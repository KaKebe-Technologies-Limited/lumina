<?php
declare(strict_types=1);

namespace App\Services\Newsletter;

/**
 * The COVER PICTURE of a newsletter PDF, made automatically from the uploaded PDF.
 *  1. Imagick with a PDF delegate (Ghostscript) draws page 1 exactly as it looks: used when the server has it.
 *  2. Otherwise the biggest photo placed in the file (a JPEG inside the PDF, which is what Canva, Word and InDesign exports hold) is taken as it is.
 * Returns JPEG bytes, or null when the PDF holds no usable picture (the page then shows the branded cover tile).
 */
final class PdfCover
{
    private const MAX_READ = 40 * 1048576;

    public static function fromFile(string $pdfPath): ?string
    {
        if (!is_file($pdfPath) || (int) filesize($pdfPath) > self::MAX_READ) {
            return null;
        }
        return self::viaImagick($pdfPath) ?? self::largestJpeg((string) file_get_contents($pdfPath));
    }

    private static function viaImagick(string $pdfPath): ?string
    {
        if (!class_exists(\Imagick::class)) {
            return null;
        }
        try {
            $im = new \Imagick();
            $im->setResolution(110, 110);
            $im->readImage($pdfPath . '[0]');
            $im->setImageBackgroundColor('white');
            $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $im->setImageFormat('jpeg');
            $im->setImageCompressionQuality(86);
            $im->thumbnailImage(900, 0);
            $blob = (string) $im->getImageBlob();
            $im->clear();
            return str_starts_with($blob, "\xFF\xD8") ? $blob : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** The first sizeable colour/grey JPEG of the file that is not an obvious strip or icon (the cover photo comes first in nearly every export). */
    public static function largestJpeg(string $pdf): ?string
    {
        $best = null;
        $bestScore = 0;
        $pos = 0;
        $seen = 0;
        while (($s = strpos($pdf, 'stream', $pos)) !== false && $seen < 400) {
            $pos = $s + 6;
            if ($s >= 3 && substr($pdf, $s - 3, 3) === 'end') {
                continue;
            }
            $dictStart = strrpos(substr($pdf, max(0, $s - 1500), 1500), ' obj');
            $dict = $dictStart === false ? substr($pdf, max(0, $s - 600), 600) : substr($pdf, max(0, $s - 1500) + $dictStart, 1500 - $dictStart);
            if (!str_contains($dict, '/Image') || !str_contains($dict, 'DCTDecode')) {
                continue;
            }
            $seen++;
            $start = $s + 6;
            if (substr($pdf, $start, 2) === "\r\n") {
                $start += 2;
            } elseif (($pdf[$start] ?? '') === "\n" || ($pdf[$start] ?? '') === "\r") {
                $start += 1;
            }
            $end = strpos($pdf, 'endstream', $start);
            if ($end === false) {
                continue;
            }
            $data = rtrim(substr($pdf, $start, $end - $start), "\r\n");
            if (str_contains($dict, 'ASCII85Decode')) {                                    // ReportLab and some other tools wrap the JPEG in ASCII85
                $data = self::ascii85($data);
            }
            if (!str_starts_with($data, "\xFF\xD8") || strlen($data) < 8000) {
                continue;
            }
            $info = self::jpegInfo($data);
            if ($info === null || $info['comps'] === 4 || $info['w'] < 400 || $info['h'] < 400) {
                continue;
            }
            $ratio = $info['w'] / $info['h'];
            if ($ratio > 3 || $ratio < 0.33) {
                continue;
            }
            $score = $info['w'] * $info['h'];
            // the first big picture wins unless a later one is more than twice as large (a full-page cover beats a small inset)
            if ($best === null || $score > $bestScore * 2) {
                $best = $data;
                $bestScore = $score;
            }
            if ($best !== null && $seen >= 12) {
                break;
            }
        }
        return $best;
    }

    private static function ascii85(string $in): string
    {
        $in = preg_replace('~\s+~', '', $in) ?? '';
        if (str_starts_with($in, '<~')) {
            $in = substr($in, 2);
        }
        $end = strpos($in, '~>');
        if ($end !== false) {
            $in = substr($in, 0, $end);
        }
        $out = [];
        $tuple = 0;
        $count = 0;
        $len = strlen($in);
        for ($i = 0; $i < $len; $i++) {
            $c = $in[$i];
            if ($c === 'z' && $count === 0) {
                $out[] = "\0\0\0\0";
                continue;
            }
            $v = ord($c) - 33;
            if ($v < 0 || $v > 84) {
                return '';
            }
            $tuple = $tuple * 85 + $v;
            if (++$count === 5) {
                $out[] = pack('N', $tuple & 0xFFFFFFFF);
                $tuple = 0;
                $count = 0;
            }
        }
        if ($count > 1) {
            for ($k = $count; $k < 5; $k++) {
                $tuple = $tuple * 85 + 84;
            }
            $out[] = substr(pack('N', $tuple & 0xFFFFFFFF), 0, $count - 1);
        }
        return implode('', $out);
    }

    /** @return array{w:int,h:int,comps:int}|null */
    private static function jpegInfo(string $d): ?array
    {
        $len = strlen($d);
        $i = 2;
        while ($i + 9 < $len) {
            if ($d[$i] !== "\xFF") {
                $i++;
                continue;
            }
            $m = ord($d[$i + 1]);
            if ($m === 0xFF) {
                $i++;
                continue;
            }
            if ($m >= 0xC0 && $m <= 0xCF && !in_array($m, [0xC4, 0xC8, 0xCC], true)) {
                return ['h' => (ord($d[$i + 5]) << 8) | ord($d[$i + 6]), 'w' => (ord($d[$i + 7]) << 8) | ord($d[$i + 8]), 'comps' => ord($d[$i + 9])];
            }
            if ($m === 0xD8 || ($m >= 0xD0 && $m <= 0xD7) || $m === 0x01) {
                $i += 2;
                continue;
            }
            $i += 2 + ((ord($d[$i + 2]) << 8) | ord($d[$i + 3]));
        }
        return null;
    }
}
