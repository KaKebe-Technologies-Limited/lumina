<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

/**
 * Draws a CardLayout as a one-page vector PDF (720 x 414 pt) with PdfWriter. The PDF is the authoritative card: text is real text in core fonts,
 * shapes are vectors, the QR code is vector squares, the photo and logo are embedded pictures.
 *
 *   $pdf = CardPdf::render($layout, ['logo' => ['bytes' => $b, 'mime' => 'image/jpeg'], 'photo' => [...]], ['title' => 'Membership card LAF-IND-26-0001', 'author' => 'Lumina Africa Foundation']);
 */
final class CardPdf
{
    /**
     * @param array{w:float,h:float,ops:list<array<string,mixed>>} $layout
     * @param array<string,array{bytes:string,mime:string}|null> $images picture bytes by layout key (logo, photo); a missing or unusable picture is simply not drawn
     * @param array{title?:string,author?:string,subject?:string,date?:int,compress?:bool} $info
     */
    public static function render(array $layout, array $images = [], array $info = []): string
    {
        $pdf = new PdfWriter($info + ['title' => 'Membership card', 'subject' => 'Membership card']);
        $pdf->addPage((float) $layout['w'], (float) $layout['h']);
        $names = [];
        foreach ($layout['ops'] as $op) {
            switch ($op['t']) {
                case 'rect':
                    $pdf->fill((string) $op['fill']);
                    $pdf->roundRect((float) $op['x'], (float) $op['y'], (float) $op['w'], (float) $op['h'], (float) $op['r'], 'f');
                    break;
                case 'line':
                    $pdf->save();
                    $pdf->stroke((string) $op['color']);
                    $pdf->lineWidth((float) $op['width']);
                    $pdf->dash((array) $op['dash']);
                    $pdf->line((float) $op['x1'], (float) $op['y1'], (float) $op['x2'], (float) $op['y2']);
                    $pdf->restore();
                    break;
                case 'text':
                    $pdf->text((float) $op['x'], (float) $op['y'], (string) $op['text'], (string) $op['font'], (float) $op['size'], (string) $op['color'], (string) $op['align']);
                    break;
                case 'qr':
                    $g = CardLayout::qrGeometry($op);
                    $pdf->fill((string) $op['dark']);
                    foreach (QrEncoder::runs($op['modules']) as [$r, $c, $len]) {
                        // a hair of overlap so adjacent rows never show a seam in a viewer's anti-aliasing
                        $pdf->rect($g['x'] + $c * $g['module'] - 0.01, $g['y'] + $r * $g['module'] - 0.01, $len * $g['module'] + 0.02, $g['module'] + 0.02, 'f');
                    }
                    break;
                case 'image':
                    self::image($pdf, $op, $images[$op['key']] ?? null, $names);
                    break;
            }
        }
        return $pdf->output();
    }

    /** @param array<string,mixed> $op @param array{bytes:string,mime:string}|null $img @param array<string,string> $names resource names already registered */
    private static function image(PdfWriter $pdf, array $op, ?array $img, array &$names): void
    {
        if ($img === null || ($img['bytes'] ?? '') === '') {
            return;
        }
        $key = (string) $op['key'];
        try {
            if (!isset($names[$key])) {
                $names[$key] = $img['mime'] === 'image/png' ? $pdf->addPng($img['bytes']) : $pdf->addJpeg($img['bytes']);
            }
        } catch (\InvalidArgumentException) {
            return;                                      // an unusable picture is left out (CardRenderer checks beforehand, this is the last guard)
        }
        [$iw, $ih] = $pdf->imageSize($names[$key]);
        $x = (float) $op['x'];
        $y = (float) $op['y'];
        $w = (float) $op['w'];
        $h = (float) $op['h'];
        if (!empty($op['cover'])) {
            [$dx, $dy, $dw, $dh] = CardLayout::cover($iw, $ih, $x, $y, $w, $h);
            $pdf->save();
            $pdf->clipRoundRect($x, $y, $w, $h, (float) $op['r']);
            $pdf->image($names[$key], $dx, $dy, $dw, $dh);
            $pdf->restore();
            return;
        }
        $scale = min($w / $iw, $h / $ih);                // contain: keep the whole picture, centred in its box
        $dw = $iw * $scale;
        $dh = $ih * $scale;
        $pdf->image($names[$key], $x + ($w - $dw) / 2, $y + ($h - $dh) / 2, $dw, $dh);
    }
}
