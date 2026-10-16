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
 *       WebP: EXIF and XMP chunks are dropped (and the VP8X flags cleared). GIF has no location metadata and is returned as is.
 */
final class ImageScrubber
{
    public static function scrub(string $bytes, string $mime): ?string
    {
        return match ($mime) {
            'image/jpeg' => self::jpeg($bytes),
            'image/png'  => self::png($bytes),
            'image/webp' => self::webp($bytes),
            'image/gif'  => str_starts_with($bytes, 'GIF8') ? $bytes : null,
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
                $tail = substr($d, $i);
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
