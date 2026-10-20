<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

/**
 * Draws a CardLayout as a PNG with GD (only when GD is available: check CardPng::available()). It is a PREVIEW of the PDF, drawn from the SAME
 * primitive list, so the layout is identical; the PDF stays the authoritative output.
 *
 * Quality: the card is drawn at 2 x the output size and scaled down once, which smooths the edges of rounded rectangles. Text uses the bundled
 * Liberation fonts (metric-compatible with Helvetica and Times, so lines have the same width as in the PDF; assets/fonts/card/, SIL OFL licence in
 * the same folder) through FreeType. Fallback: when FreeType or a font file is missing the text is drawn with GD's built-in bitmap fonts, which are
 * fixed in size and look plain (documented limitation; the line positions and the alignment still follow the layout). Characters WinAnsi lacks are
 * drawn transliterated, exactly as in the PDF.
 *
 * Limits: the corner radius of clipped pictures is ignored (the card's pictures are square), and alpha in the logo is composited over white.
 */
final class CardPng
{
    public const SCALE = 2.0;            // output pixels per point (1440 x 828)
    private const SS = 2;                // supersampling factor
    private const FONTS = [
        'helvetica'      => 'LiberationSans-Regular.ttf',
        'helvetica-bold' => 'LiberationSans-Bold.ttf',
        'times-bold'     => 'LiberationSerif-Bold.ttf',
        'times-italic'   => 'LiberationSerif-Italic.ttf',
    ];

    public static function available(): bool
    {
        return function_exists('imagecreatetruecolor') && function_exists('imagepng');
    }

    /** True when text can be drawn with the bundled TrueType fonts (FreeType + the font files). */
    public static function hasTrueType(): bool
    {
        if (!function_exists('imagettftext') || !function_exists('imagettfbbox')) {
            return false;
        }
        foreach (self::FONTS as $file) {
            if (!is_file(self::fontPath($file))) {
                return false;
            }
        }
        return true;
    }

    private static function fontPath(string $file): string
    {
        return (defined('ASSET_PATH') ? ASSET_PATH : dirname(__DIR__, 4) . '/assets') . '/fonts/card/' . $file;
    }

    /**
     * @param array{w:float,h:float,ops:list<array<string,mixed>>} $layout
     * @param array<string,array{bytes:string,mime:string}|null> $images
     * @return string|null PNG bytes, or null when GD is missing or the drawing failed
     */
    public static function render(array $layout, array $images = [], ?float $scale = null): ?string
    {
        if (!self::available()) {
            return null;
        }
        $scale ??= self::SCALE;
        $ttf = self::hasTrueType();
        $k = $scale * ($ttf ? self::SS : 1);                      // pixels per point while drawing (no supersampling for the bitmap-font fallback)
        $w = (int) round($layout['w'] * $k);
        $h = (int) round($layout['h'] * $k);
        if ($w * $h > 40000000) {
            return null;
        }
        $im = imagecreatetruecolor($w, $h);
        if ($im === false) {
            return null;
        }
        imagealphablending($im, true);
        imageantialias($im, true);
        foreach ($layout['ops'] as $op) {
            switch ($op['t']) {
                case 'rect':
                    self::roundRect($im, $op['x'] * $k, $op['y'] * $k, ($op['x'] + $op['w']) * $k, ($op['y'] + $op['h']) * $k, $op['r'] * $k, self::color($im, (string) $op['fill']));
                    break;
                case 'line':
                    self::line($im, $op, $k);
                    break;
                case 'text':
                    self::text($im, $op, $k, $ttf);
                    break;
                case 'qr':
                    $g = CardLayout::qrGeometry($op);
                    $col = self::color($im, (string) $op['dark']);
                    foreach (QrEncoder::runs($op['modules']) as [$r, $c, $len]) {
                        imagefilledrectangle($im, (int) floor(($g['x'] + $c * $g['module']) * $k), (int) floor(($g['y'] + $r * $g['module']) * $k),
                            (int) ceil(($g['x'] + ($c + $len) * $g['module']) * $k) - 1, (int) ceil(($g['y'] + ($r + 1) * $g['module']) * $k) - 1, $col);
                    }
                    break;
                case 'image':
                    self::image($im, $op, $images[$op['key']] ?? null, $k);
                    break;
            }
        }
        // one smoothing step down to the output size
        $out = imagecreatetruecolor((int) round($layout['w'] * $scale), (int) round($layout['h'] * $scale));
        if ($out === false) {
            imagedestroy($im);
            return null;
        }
        imagecopyresampled($out, $im, 0, 0, 0, 0, imagesx($out), imagesy($out), $w, $h);
        imagedestroy($im);
        ob_start();
        $ok = imagepng($out, null, 6);
        $bytes = (string) ob_get_clean();
        imagedestroy($out);
        return $ok && $bytes !== '' ? $bytes : null;
    }

    private static function color(\GdImage $im, string $hex): int
    {
        $h = ltrim($hex, '#');
        return (int) imagecolorallocate($im, (int) hexdec(substr($h, 0, 2)), (int) hexdec(substr($h, 2, 2)), (int) hexdec(substr($h, 4, 2)));
    }

    private static function roundRect(\GdImage $im, float $x1, float $y1, float $x2, float $y2, float $r, int $col): void
    {
        $x1 = (int) round($x1);
        $y1 = (int) round($y1);
        $x2 = (int) round($x2) - 1;
        $y2 = (int) round($y2) - 1;
        $r = (int) round(max(0.0, min($r, ($x2 - $x1 + 1) / 2, ($y2 - $y1 + 1) / 2)));
        if ($r < 1) {
            imagefilledrectangle($im, $x1, $y1, $x2, $y2, $col);
            return;
        }
        imagefilledrectangle($im, $x1 + $r, $y1, $x2 - $r, $y2, $col);
        imagefilledrectangle($im, $x1, $y1 + $r, $x2, $y2 - $r, $col);
        $d = $r * 2;
        imagefilledellipse($im, $x1 + $r, $y1 + $r, $d, $d, $col);
        imagefilledellipse($im, $x2 - $r, $y1 + $r, $d, $d, $col);
        imagefilledellipse($im, $x1 + $r, $y2 - $r, $d, $d, $col);
        imagefilledellipse($im, $x2 - $r, $y2 - $r, $d, $d, $col);
    }

    /** @param array<string,mixed> $op */
    private static function line(\GdImage $im, array $op, float $k): void
    {
        $col = self::color($im, (string) $op['color']);
        $x = (int) round($op['x1'] * $k);
        $thick = max(1, (int) round($op['width'] * $k));
        $dash = array_values((array) $op['dash']);
        $dashed = $dash !== [];
        $on = max(1, (int) round((float) ($dash[0] ?? 0) * $k));
        $off = max(1, (int) round((float) ($dash[1] ?? 0) * $k));
        $y1 = (int) round($op['y1'] * $k);
        $y2 = (int) round($op['y2'] * $k);
        if (!$dashed) {
            imagefilledrectangle($im, $x, $y1, $x + $thick - 1, $y2, $col);
            return;
        }
        for ($y = $y1; $y < $y2; $y += $on + $off) {
            imagefilledrectangle($im, $x, $y, $x + $thick - 1, min($y2, $y + $on - 1), $col);
        }
    }

    /** @param array<string,mixed> $op */
    private static function text(\GdImage $im, array $op, float $k, bool $ttf): void
    {
        $utf8 = (string) mb_convert_encoding(PdfWriter::toWinAnsi((string) $op['text']), 'UTF-8', 'Windows-1252');
        if ($utf8 === '') {
            return;
        }
        $width = PdfWriter::textWidth((string) $op['text'], (string) $op['font'], (float) $op['size']);
        $left = (float) $op['x'] - ($op['align'] === 'c' ? $width / 2 : ($op['align'] === 'r' ? $width : 0.0));
        $col = self::color($im, (string) $op['color']);
        $px = (int) round($left * $k);
        $py = (int) round($op['y'] * $k);
        if ($ttf) {
            // GD measures the size in points at 96 dpi: a font whose em is N canvas pixels needs N * 72 / 96 points (checked in bin/selftest-membership-card.php)
            @imagettftext($im, (float) $op['size'] * $k * 0.75, 0, $px, $py, $col, self::fontPath(self::FONTS[(string) $op['font']]), $utf8);
            return;
        }
        $font = $op['size'] >= 14 ? 5 : ($op['size'] >= 9.5 ? 4 : 3);
        $fh = imagefontheight($font);
        $fw = imagefontwidth($font) * strlen($utf8);
        // bitmap fonts cannot scale: draw at their own size, centred on the layout position
        $x = $op['align'] === 'c' ? (int) round($op['x'] * $k - $fw / 2) : ($op['align'] === 'r' ? (int) round($op['x'] * $k - $fw) : $px);
        imagestring($im, $font, $x, (int) round($py - $fh * 0.8), $utf8, $col);
    }

    /** @param array<string,mixed> $op @param array{bytes:string,mime:string}|null $img */
    private static function image(\GdImage $im, array $op, ?array $img, float $k): void
    {
        if ($img === null || ($img['bytes'] ?? '') === '') {
            return;
        }
        $src = @imagecreatefromstring($img['bytes']);
        if (!$src instanceof \GdImage) {
            return;
        }
        $iw = imagesx($src);
        $ih = imagesy($src);
        $x = (float) $op['x'];
        $y = (float) $op['y'];
        $w = (float) $op['w'];
        $h = (float) $op['h'];
        if (!empty($op['cover'])) {
            [$dx, $dy, $dw, $dh] = CardLayout::cover($iw, $ih, $x, $y, $w, $h);
            // the part of the source that lies inside the box
            $sx = ($x - $dx) / $dw * $iw;
            $sy = ($y - $dy) / $dh * $ih;
            $sw = $w / $dw * $iw;
            $sh = $h / $dh * $ih;
            imagecopyresampled($im, $src, (int) round($x * $k), (int) round($y * $k), (int) round($sx), (int) round($sy), (int) round($w * $k), (int) round($h * $k), max(1, (int) round($sw)), max(1, (int) round($sh)));
        } else {
            $scale = min($w / $iw, $h / $ih);
            $dw = $iw * $scale;
            $dh = $ih * $scale;
            imagecopyresampled($im, $src, (int) round(($x + ($w - $dw) / 2) * $k), (int) round(($y + ($h - $dh) / 2) * $k), 0, 0, (int) round($dw * $k), (int) round($dh * $k), $iw, $ih);
        }
        imagedestroy($src);
    }
}
