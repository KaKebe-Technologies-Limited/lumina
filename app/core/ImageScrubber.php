<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pure-PHP removal of privacy-sensitive metadata (EXIF / GPS location, camera serial, XMP, IPTC, text comments) from uploaded
 * images WITHOUT needing GD. Used by Uploader whenever the image is stored as-is (no GD, GD decode failure, huge image).
 *
 *   $clean = ImageScrubber::scrub($bytes, 'image/jpeg');    // string, or null when the file is not a well-formed image
 *
 * JPEG: APP1 (Exif/XMP), APP13 (IPTC), COM and APP3-APP12/APP15 are dropped; JFIF, ICC profile and Adobe colour markers are kept.
 *       When the camera stored a rotation flag, a minimal Exif block holding ONLY the orientation is written back so the
 *       picture does not turn sideways. PNG: eXIf, tEXt, zTXt, iTXt and tIME chunks are dropped.
 *       WebP: EXIF and XMP chunks are dropped (and the VP8X flags cleared). GIF: comments and XMP/unknown application extensions are dropped.
 *       JPEG and GIF are cut at the end marker found by walking the file structure (EOI / trailer), so bytes appended after the picture (a hidden
 *       payload, `<?= ... ?>`, an old copy of a cropped picture) never reach the stored file; PNG stops at IEND, WebP at the RIFF size.
 */
final class ImageScrubber
{
    public static function scrub(string $bytes, string $mime): ?string
    {
        return match ($mime) {
            'image/jpeg' => self::jpeg($bytes),
            'image/png'  => self::png($bytes),
            'image/webp' => self::webp($bytes),
            'image/gif'  => self::gif($bytes),
            default      => null,
        };
    }

    // ------------------------------------------------------------------ JPEG

    private static function jpeg(string $d): ?string
    {
        $len = strlen($d);
        if ($len < 4 || !str_starts_with($d, "\xFF\xD8")) {
            return null;
        }
        $kept = [];                // header segments that stay (raw bytes, marker included), in file order
        $orientation = 1;
        $tail = null;              // start-of-scan segment + entropy data + EOI, copied untouched
        $i = 2;
        while ($i + 2 <= $len) {
            if ($d[$i] !== "\xFF") {
                return null;
            }
            $m = ord($d[$i + 1]);
            if ($m === 0xFF) {                       // fill byte
                $i++;
                continue;
            }
            if ($m === 0xD8 || $m === 0x01 || ($m >= 0xD0 && $m <= 0xD7)) {    // markers without a length field
                $i += 2;
                continue;
            }
            if ($m === 0xD9 || $i + 4 > $len) {      // EOI before any scan, or truncated
                return null;
            }
            $segLen = (ord($d[$i + 2]) << 8) | ord($d[$i + 3]);
            if ($segLen < 2 || $i + 2 + $segLen > $len) {
                return null;
            }
            if ($m === 0xDA) {
                $tail = self::jpegScans($d, $i);       // every scan up to the EOI that ends the picture; anything after it is dropped
                break;
            }
            $seg = substr($d, $i, 2 + $segLen);
            if ($m === 0xE1) {                                       // APP1: Exif or XMP
                if (substr($seg, 4, 6) === "Exif\0\0") {
                    $orientation = self::tiffOrientation(substr($seg, 10));
                }
            } elseif ($m === 0xED || $m === 0xFE || ($m >= 0xE3 && $m <= 0xEC) || $m === 0xEF) {
                // APP13 (IPTC), comments, APP3..APP12 (vendor / meta blocks), APP15: dropped
            } else {
                $kept[] = [$m, $seg];                                // APP0 JFIF, APP2 ICC, APP14 Adobe, tables, frame header ...
            }
            $i += 2 + $segLen;
        }
        if ($tail === null) {
            return null;
        }
        $out = "\xFF\xD8";
        $exifWritten = $orientation <= 1 || $orientation > 8;
        foreach ($kept as [$m, $seg]) {
            $out .= $seg;
            if (!$exifWritten && $m === 0xE0) {                      // right after the JFIF header, like cameras do
                $out .= self::orientationOnlyExif($orientation);
                $exifWritten = true;
            }
        }
        if (!$exifWritten) {
            $out = "\xFF\xD8" . self::orientationOnlyExif($orientation) . substr($out, 2);
        }
        return $out . $tail;
    }

    /**
     * The scan part of a JPEG, from the first SOS marker to the EOI that ends the picture, walked by marker structure (not by searching for the
     * last FFD9). Comments and APPn segments between scans are dropped; bytes after the EOI (a hidden payload, an appended thumbnail, the old
     * data of a cropped picture) are never copied. Null when the scans are truncated or malformed.
     */
    private static function jpegScans(string $d, int $p): ?string
    {
        $len = strlen($d);
        $out = '';
        while (true) {
            if ($p + 2 > $len || $d[$p] !== "\xFF") {
                return null;
            }
            $m = ord($d[$p + 1]);
            if ($m === 0xFF) {                                       // fill byte
                $p++;
                continue;
            }
            if ($m === 0xD9) {                                       // EOI: the picture ends here
                return $out . "\xFF\xD9";
            }
            if ($m === 0xD8 || $m === 0x01 || ($m >= 0xD0 && $m <= 0xD7)) {
                $out .= substr($d, $p, 2);
                $p += 2;
                continue;
            }
            if ($p + 4 > $len) {
                return null;
            }
            $segLen = (ord($d[$p + 2]) << 8) | ord($d[$p + 3]);
            if ($segLen < 2 || $p + 2 + $segLen > $len) {
                return null;
            }
            if ($m !== 0xFE && !($m >= 0xE1 && $m <= 0xEF && $m !== 0xE2 && $m !== 0xEE)) {     // keep tables, scan headers, ICC (APP2), Adobe (APP14)
                $out .= substr($d, $p, 2 + $segLen);
            }
            $p += 2 + $segLen;
            if ($m === 0xDA) {                                       // entropy-coded data runs to the next real marker
                $q = $p;
                while (true) {
                    $pos = strpos($d, "\xFF", $q);
                    if ($pos === false || $pos + 1 >= $len) {
                        return null;
                    }
                    $b = ord($d[$pos + 1]);
                    if ($b === 0x00 || ($b >= 0xD0 && $b <= 0xD7)) {  // stuffed byte or restart marker
                        $q = $pos + 2;
                    } elseif ($b === 0xFF) {
                        $q = $pos + 1;
                    } else {
                        break;
                    }
                }
                $out .= substr($d, $p, $pos - $p);
                $p = $pos;
            }
        }
    }

    // ------------------------------------------------------------------ GIF

    /**
     * Rebuilds a GIF from its blocks up to the trailer (0x3B): comments, plain-text and unknown application extensions (XMP) are dropped, and
     * so is everything after the trailer. Null when the blocks are truncated or malformed.
     */
    private static function gif(string $d): ?string
    {
        $len = strlen($d);
        if ($len < 14 || (!str_starts_with($d, 'GIF87a') && !str_starts_with($d, 'GIF89a'))) {
            return null;
        }
        $flags = ord($d[10]);
        $p = 13 + (($flags & 0x80) ? 3 * (1 << (($flags & 7) + 1)) : 0);
        if ($p > $len) {
            return null;
        }
        $out = substr($d, 0, $p);
        while ($p < $len) {
            $b = $d[$p];
            if ($b === "\x3B") {
                return $out . "\x3B";
            }
            if ($b === "\x21") {                                     // extension: label + sub-blocks ending with a zero-length block
                if ($p + 2 >= $len) {
                    return null;
                }
                $label = ord($d[$p + 1]);
                $q = $p + 2;
                $appId = $label === 0xFF ? substr($d, $q + 1, ord($d[$q])) : '';
                $q = self::gifSubBlocks($d, $q);
                if ($q === null) {
                    return null;
                }
                if ($label === 0xF9 || ($label === 0xFF && (str_starts_with($appId, 'NETSCAPE') || str_starts_with($appId, 'ANIMEXTS')))) {
                    $out .= substr($d, $p, $q - $p);                 // graphic control (transparency, timing) and the loop count stay
                }
                $p = $q;
                continue;
            }
            if ($b === "\x2C") {                                     // image: descriptor, optional local colour table, LZW size, data blocks
                if ($p + 10 > $len) {
                    return null;
                }
                $f = ord($d[$p + 9]);
                $q = $p + 10 + (($f & 0x80) ? 3 * (1 << (($f & 7) + 1)) : 0) + 1;
                $q = self::gifSubBlocks($d, $q);
                if ($q === null) {
                    return null;
                }
                $out .= substr($d, $p, $q - $p);
                $p = $q;
                continue;
            }
            return null;
        }
        return null;
    }

    /** Position just after the sub-block chain that starts at $q (each block is a length byte + data; a zero length ends it), or null. */
    private static function gifSubBlocks(string $d, int $q): ?int
    {
        $len = strlen($d);
        while (true) {
            if ($q >= $len) {
                return null;
            }
            $n = ord($d[$q]);
            $q++;
            if ($n === 0) {
                return $q;
            }
            $q += $n;
            if ($q > $len) {
                return null;
            }
        }
    }

    /** Orientation (1-8) from a TIFF block (the Exif payload after "Exif\0\0"); 1 when absent or unreadable. */
    private static function tiffOrientation(string $t): int
    {
        if (strlen($t) < 14) {
            return 1;
        }
        $le = str_starts_with($t, 'II');
        if (!$le && !str_starts_with($t, 'MM')) {
            return 1;
        }
        $u16 = static fn(int $o): int => $o + 2 > strlen($t) ? 0 : (int) unpack($le ? 'v' : 'n', $t, $o)[1];
        $u32 = static fn(int $o): int => $o + 4 > strlen($t) ? 0 : (int) unpack($le ? 'V' : 'N', $t, $o)[1];
        if ($u16(2) !== 42) {
            return 1;
        }
        $ifd = $u32(4);
        $n = $u16($ifd);
        if ($ifd < 8 || $n < 1 || $n > 200) {
            return 1;
        }
        for ($k = 0; $k < $n; $k++) {
            $e = $ifd + 2 + $k * 12;
            if ($e + 12 > strlen($t)) {
                break;
            }
            if ($u16($e) === 0x0112) {
                $v = $u16($e + 8);
                return ($v >= 1 && $v <= 8) ? $v : 1;
            }
        }
        return 1;
    }

    /** APP1 segment that contains nothing but the orientation tag (big-endian TIFF, one IFD entry). */
    private static function orientationOnlyExif(int $orientation): string
    {
        $tiff = "MM\x00\x2A\x00\x00\x00\x08"             // header, IFD0 at offset 8
            . "\x00\x01"                                  // one entry
            . "\x01\x12\x00\x03\x00\x00\x00\x01" . pack('n', $orientation) . "\x00\x00"   // Orientation, SHORT, count 1
            . "\x00\x00\x00\x00";                         // no next IFD
        $payload = "Exif\0\0" . $tiff;
        return "\xFF\xE1" . pack('n', strlen($payload) + 2) . $payload;
    }

    // ------------------------------------------------------------------ PNG

    private static function png(string $d): ?string
    {
        $len = strlen($d);
        if ($len < 20 || !str_starts_with($d, "\x89PNG\r\n\x1A\n")) {
            return null;
        }
        $out = substr($d, 0, 8);
        $i = 8;
        $sawEnd = false;
        while ($i + 12 <= $len) {
            $cl = (int) unpack('N', $d, $i)[1];
            $type = substr($d, $i + 4, 4);
            $total = 12 + $cl;
            if ($cl < 0 || $cl > 0x7FFFFFFF || $i + $total > $len) {
                return null;
            }
            if (!in_array($type, ['eXIf', 'tEXt', 'zTXt', 'iTXt', 'tIME'], true)) {
                $out .= substr($d, $i, $total);
            }
            $i += $total;
            if ($type === 'IEND') {
                $sawEnd = true;
                break;
            }
        }
        return $sawEnd ? $out : null;
    }

    // ------------------------------------------------------------------ WebP

    private static function webp(string $d): ?string
    {
        $len = strlen($d);
        if ($len < 20 || !str_starts_with($d, 'RIFF') || substr($d, 8, 4) !== 'WEBP') {
            return null;
        }
        $end = min($len, 8 + (int) unpack('V', $d, 4)[1]);
        $body = '';
        $i = 12;
        while ($i + 8 <= $end) {
            $type = substr($d, $i, 4);
            $size = (int) unpack('V', $d, $i + 4)[1];
            $padded = $size + ($size & 1);
            if ($i + 8 + $size > $end) {
                return null;
            }
            if ($type === 'EXIF' || $type === 'XMP ') {
                $i += 8 + $padded;
                continue;
            }
            $chunk = substr($d, $i, 8 + $padded);
            if ($type === 'VP8X' && $size >= 10) {
                $chunk[8] = chr(ord($chunk[8]) & ~0x0C);       // clear the "has EXIF" and "has XMP" flags
            }
            $body .= $chunk;
            $i += 8 + $padded;
        }
        if ($body === '') {
            return null;
        }
        return 'RIFF' . pack('V', 4 + strlen($body)) . 'WEBP' . $body;
    }
}
