<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

/**
 * THE ONE LAYOUT of the membership card: turns the data array of CardData into a flat list of drawing primitives in points (720 x 414, origin
 * top-left, y downward, text y = baseline). CardPdf draws the list as vector graphics, CardPng draws the very same list with GD, so the two
 * outputs cannot drift. All text is MEASURED here with the exact core-font widths (PdfWriter::textWidth) and shrunk, wrapped or shortened to
 * fit, so no renderer ever has to decide anything.
 *
 * Geometry follows docs/design-reference/membership-card-reference.pdf block by block (numbers read from the owner's PDF): a white panel of
 * 520 pt on the left; a navy panel of 200 pt on the right with a 9 pt accent bar on top; logo 64 pt at (28,26); the photo block 116 pt at
 * (28,130); three info tiles 52 pt high at y 296 between x 28 and 492; footer lines baseline 370 / 383 / 396; panel items centred on x 620.
 *
 * Primitives (array keys):
 *   rect   x y w h r fill                      rounded rectangle (r = 0: square)
 *   line   x1 y1 x2 y2 color width dash        dash = [on, off] or []
 *   text   x y text font size color align      font: helvetica | helvetica-bold | times-bold | times-italic; align l | c | r (x = left | centre | right)
 *   image  key x y w h r cover                 key: logo | photo; cover = fill the box and crop (top biased), r = corner radius of the clip
 *   qr     x y size quiet modules dark         modules = list of rows of bool; `quiet` modules of white around it are INSIDE size
 *
 * build() returns ['w','h','ops','meta'] where meta lists what was adjusted (shrunk / wrapped / shortened fields, qr version) for tests and the designer.
 */
final class CardLayout
{
    public const W = 720.0;
    public const H = 414.0;
    public const PANEL_X = 520.0;
    /** Left white panel content edges. */
    public const LEFT = 28.0;
    public const RIGHT = 492.0;
    public const CX = 620.0;                       // centre of the navy panel
    public const PHOTO_BIAS = 0.2;                 // how far from the top a tall photo is cropped (0 = keep the top, 0.5 = centre)
    private const ELLIPSIS = "\u{2026}";
    /**
     * The reference was typeset in Carlito (a narrow humanist sans); the PDF core font Helvetica is about 11% taller and 17% wider at the same
     * size. Every sans size in this file is the REFERENCE size; it is multiplied by the factor of its font (regular 0.91, bold 0.86) so the words take the same space and keep the same
     * hierarchy. (Serif lines use Times, which is metric-identical to the reference's Liberation Serif: no scaling.)
     */
    public const SANS_SCALE = ['helvetica' => 0.91, 'helvetica-bold' => 0.86];

    /**
     * @param array<string,mixed> $d CardData::build() output
     * @param array{logo?:bool} $opts logo = false when there is no logo picture (the text moves to the left edge)
     * @return array{w:float,h:float,ops:list<array<string,mixed>>,meta:array<string,mixed>}
     */
    public static function build(array $d, array $opts = []): array
    {
        $c = (array) $d['colors'];
        $navy = $c['navy'];
        $accent = $c['accent'];
        $label = $c['label'];
        $muted = $c['muted'];
        $ops = [];
        $meta = ['fitted' => [], 'qr_version' => null, 'photo' => false];
        $hasLogo = ($opts['logo'] ?? true) !== false;

        // ------------------------------------------------------------------ backgrounds and the navy panel
        $ops[] = ['t' => 'rect', 'x' => 0.0, 'y' => 0.0, 'w' => self::W, 'h' => self::H, 'r' => 0.0, 'fill' => '#FFFFFF'];
        $ops[] = ['t' => 'rect', 'x' => self::PANEL_X, 'y' => 0.0, 'w' => self::W - self::PANEL_X, 'h' => self::H, 'r' => 0.0, 'fill' => $navy];
        $ops[] = ['t' => 'rect', 'x' => self::PANEL_X, 'y' => 0.0, 'w' => self::W - self::PANEL_X, 'h' => 9.0, 'r' => 0.0, 'fill' => $accent];
        $ops[] = ['t' => 'line', 'x1' => self::PANEL_X, 'y1' => 0.0, 'x2' => self::PANEL_X, 'y2' => self::H, 'color' => '#8F9AB5', 'width' => 0.8, 'dash' => [2.0, 3.0]];

        // ------------------------------------------------------------------ navy panel: number, status, QR, validity, panel text
        $pw = 160.0;
        self::centred($ops, $meta, 'number_label', (string) $d['number_label'], 'helvetica-bold', 8.5, 6.5, $pw, 40.0, $muted);
        self::centred($ops, $meta, 'number', (string) $d['number'], 'helvetica-bold', 21.0, 12.0, $pw, 64.0, '#FFFFFF');
        $ops[] = ['t' => 'rect', 'x' => 546.0, 'y' => 78.0, 'w' => 148.0, 'h' => 24.0, 'r' => 3.0, 'fill' => (string) $d['status_color']];
        self::centred($ops, $meta, 'status', (string) $d['status_label'], 'helvetica-bold', 11.5, 7.5, 134.0, 94.5, '#FFFFFF');

        $shift = 0.0;                                            // the validity block moves up when there is no QR code
        if (!empty($d['show_qr']) && ($qr = self::qr((string) $d['verify_url'])) !== null) {
            $ops[] = ['t' => 'rect', 'x' => 558.0, 'y' => 106.0, 'w' => 124.0, 'h' => 118.0, 'r' => 4.0, 'fill' => '#FFFFFF'];
            $ops[] = ['t' => 'qr', 'x' => 570.0, 'y' => 115.0, 'size' => 100.0, 'quiet' => 2, 'modules' => $qr['modules'], 'dark' => $navy];
            $meta['qr_version'] = $qr['version'];
            self::centred($ops, $meta, 'scan', (string) $d['scan_text'], 'helvetica', 8.5, 6.5, $pw + 10, 238.0, $muted);
        } else {
            $shift = -58.0;
        }
        self::centred($ops, $meta, 'valid_label', (string) $d['valid_label'], 'helvetica-bold', 8.5, 6.5, $pw, 270.0 + $shift, $muted);
        self::centred($ops, $meta, 'valid_text', (string) $d['valid_text'], 'helvetica-bold', 17.0, 10.0, $pw, 291.0 + $shift, '#FFFFFF');
        if ((string) $d['renews_text'] !== '') {
            self::centred($ops, $meta, 'renews', (string) $d['renews_text'], 'helvetica', 9.0, 6.5, $pw, 307.0 + $shift, $muted);
        }
        $panelLines = (array) $d['panel'];
        $first = 392.0 - 12.0 * (count($panelLines) - 1);
        foreach ($panelLines as $i => $line) {
            self::centred($ops, $meta, 'panel_' . $i, (string) $line, 'helvetica', 8.5, 6.0, 172.0, $first + 12.0 * $i, $muted);
        }

        // ------------------------------------------------------------------ white panel: header
        $topLeft = $hasLogo ? 102.0 : self::LEFT;
        if ($hasLogo) {
            $ops[] = ['t' => 'image', 'key' => 'logo', 'x' => self::LEFT, 'y' => 26.0, 'w' => 64.0, 'h' => 64.0, 'r' => 0.0, 'cover' => false];
        }
        $cardLabel = self::fit((string) $d['label'], 'helvetica-bold', 9.5, 7.0, 190.0);
        $typeLine = self::fit((string) $d['type'], 'helvetica-bold', 10.5, 7.5, 230.0);
        if ($cardLabel['text'] !== '') {
            $ops[] = ['t' => 'text', 'x' => self::RIGHT, 'y' => 44.0, 'text' => $cardLabel['text'], 'font' => 'helvetica-bold', 'size' => $cardLabel['size'], 'color' => $label, 'align' => 'r'];
        }
        if ($typeLine['text'] !== '') {
            $ops[] = ['t' => 'text', 'x' => self::RIGHT, 'y' => 59.0, 'text' => $typeLine['text'], 'font' => 'helvetica-bold', 'size' => $typeLine['size'], 'color' => $accent, 'align' => 'r'];
            self::note($meta, 'type', (string) $d['type'], $typeLine);
        }
        $rightEdge = self::RIGHT - max($cardLabel['width'], $typeLine['width']);
        $orgMax = max(60.0, min(self::RIGHT - $topLeft, $rightEdge - 14.0 - $topLeft));
        $org = self::fit((string) $d['org'], 'helvetica-bold', 12.0, 8.0, $orgMax);
        $tag = self::fit((string) $d['tagline'], 'times-italic', 11.0, 7.5, $orgMax);
        if ($org['text'] !== '') {
            $ops[] = ['t' => 'text', 'x' => $topLeft, 'y' => 52.0, 'text' => $org['text'], 'font' => 'helvetica-bold', 'size' => $org['size'], 'color' => $navy, 'align' => 'l'];
            self::note($meta, 'org', (string) $d['org'], $org);
        }
        if ($tag['text'] !== '') {
            $ops[] = ['t' => 'text', 'x' => $topLeft, 'y' => 67.0, 'text' => $tag['text'], 'font' => 'times-italic', 'size' => $tag['size'], 'color' => $c['gold'], 'align' => 'l'];
            self::note($meta, 'tagline', (string) $d['tagline'], $tag);
        }

        // ------------------------------------------------------------------ photo block and the name block
        $nameX = self::LEFT;
        if (!empty($d['show_photo'])) {
            $nameX = 164.0;
            $ops[] = ['t' => 'rect', 'x' => self::LEFT, 'y' => 130.0, 'w' => 116.0, 'h' => 116.0, 'r' => 0.0, 'fill' => $accent];
            if (is_array($d['photo'] ?? null)) {
                $ops[] = ['t' => 'image', 'key' => 'photo', 'x' => self::LEFT, 'y' => 130.0, 'w' => 116.0, 'h' => 116.0, 'r' => 0.0, 'cover' => true];
                $meta['photo'] = true;
            } else {
                $ini = self::fit((string) $d['initials'], 'helvetica-bold', 46.0, 22.0, 100.0);
                $ops[] = ['t' => 'text', 'x' => self::LEFT + 58.0, 'y' => round(188.0 + 0.36 * $ini['size'] + 1.0, 2), 'text' => $ini['text'], 'font' => 'helvetica-bold', 'size' => $ini['size'], 'color' => '#FFFFFF', 'align' => 'c'];
            }
        }
        $nameMax = self::RIGHT - $nameX;
        if ((string) $d['name_label'] !== '') {
            $ops[] = ['t' => 'text', 'x' => $nameX, 'y' => 138.0, 'text' => (string) $d['name_label'], 'font' => 'helvetica-bold', 'size' => self::eff('helvetica-bold', 9.0), 'color' => $label, 'align' => 'l'];
        }
        $nameLines = self::nameLines((string) $d['name'], $nameMax);
        $baseline = count($nameLines['lines']) === 1 ? 162.0 : 156.0;
        foreach ($nameLines['lines'] as $i => $ln) {
            $ops[] = ['t' => 'text', 'x' => $nameX, 'y' => $baseline, 'text' => $ln, 'font' => 'times-bold', 'size' => $nameLines['size'], 'color' => $navy, 'align' => 'l'];
            if ($i < count($nameLines['lines']) - 1) {
                $baseline += $nameLines['size'] + 3.0;
            }
        }
        $meta['fitted']['name'] = ['size' => $nameLines['size'], 'lines' => count($nameLines['lines']), 'shortened' => $nameLines['shortened']];
        $underlineY = $baseline + 11.0;
        $ops[] = ['t' => 'rect', 'x' => $nameX, 'y' => $underlineY, 'w' => 40.0, 'h' => 3.0, 'r' => 0.0, 'fill' => $accent];
        $loc = self::fit((string) $d['location'], 'helvetica-bold', 13.0, 8.5, $nameMax);
        if ($loc['text'] !== '') {
            if ((string) $d['from_label'] !== '') {
                $ops[] = ['t' => 'text', 'x' => $nameX, 'y' => $underlineY + 25.0, 'text' => (string) $d['from_label'], 'font' => 'helvetica-bold', 'size' => self::eff('helvetica-bold', 8.5), 'color' => $label, 'align' => 'l'];
            }
            $ops[] = ['t' => 'text', 'x' => $nameX, 'y' => $underlineY + 39.0, 'text' => $loc['text'], 'font' => 'helvetica-bold', 'size' => $loc['size'], 'color' => $navy, 'align' => 'l'];
            self::note($meta, 'location', (string) $d['location'], $loc);
        }

        // ------------------------------------------------------------------ tiles
        $tiles = array_values((array) $d['tiles']);
        $n = count($tiles);
        if ($n > 0) {
            $gap = 8.0;
            $tw = (self::RIGHT - self::LEFT - $gap * ($n - 1)) / $n;
            foreach ($tiles as $i => $t) {
                $x = self::LEFT + $i * ($tw + $gap);
                $ops[] = ['t' => 'rect', 'x' => round($x, 3), 'y' => 296.0, 'w' => round($tw, 3), 'h' => 52.0, 'r' => 5.0, 'fill' => $c['tile']];
                $lab = self::fit((string) $t['label'], 'helvetica-bold', 8.0, 6.0, $tw - 24.0);
                $val = self::fit((string) $t['value'], 'helvetica-bold', 12.5, 7.5, $tw - 24.0);
                if ($lab['text'] !== '') {
                    $ops[] = ['t' => 'text', 'x' => round($x + 12.0, 3), 'y' => 312.0, 'text' => $lab['text'], 'font' => 'helvetica-bold', 'size' => $lab['size'], 'color' => $label, 'align' => 'l'];
                }
                if ($val['text'] !== '') {
                    $ops[] = ['t' => 'text', 'x' => round($x + 12.0, 3), 'y' => 334.0, 'text' => $val['text'], 'font' => 'helvetica-bold', 'size' => $val['size'], 'color' => $navy, 'align' => 'l'];
                    self::note($meta, 'tile_' . $t['key'], (string) $t['value'], $val);
                }
            }
        }

        // ------------------------------------------------------------------ footer lines (+ the sample warning)
        $lines = array_map(static fn(string $l): array => ['text' => $l, 'font' => 'helvetica', 'color' => $label], (array) $d['footer']);
        if (!empty($d['sample'])) {
            $lines[] = ['text' => (string) $d['sample_text'], 'font' => 'helvetica-bold', 'color' => '#C0392B'];
        }
        $lines = array_slice($lines, -4);
        $start = 396.0 - 13.0 * (count($lines) - 1);
        foreach ($lines as $i => $ln) {
            $f = self::fit($ln['text'], $ln['font'], 8.5, 6.5, self::RIGHT - self::LEFT);
            $ops[] = ['t' => 'text', 'x' => self::LEFT, 'y' => $start + 13.0 * $i, 'text' => $f['text'], 'font' => $ln['font'], 'size' => $f['size'], 'color' => $ln['color'], 'align' => 'l'];
            self::note($meta, 'footer_' . $i, $ln['text'], $f);
        }

        return ['w' => self::W, 'h' => self::H, 'ops' => $ops, 'meta' => $meta];
    }

    // ------------------------------------------------------------------------------------------------ text fitting

    /**
     * Fit one line into $maxW: the font size shrinks from $size down to $min in half-point steps; if it still does not fit, the text is cut with an
     * ellipsis at the minimum size. Always returns text that is <= $maxW wide (an empty text stays empty).
     * @return array{text:string,size:float,width:float,shortened:bool}
     */
    public static function fit(string $text, string $font, float $size, float $min, float $maxW): array
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return ['text' => '', 'size' => $size, 'width' => 0.0, 'shortened' => false];
        }
        for ($s = $size; $s >= $min - 0.001; $s -= 0.5) {
            $e = self::eff($font, $s);
            $w = PdfWriter::textWidth($text, $font, $e);
            if ($w <= $maxW) {
                return ['text' => $text, 'size' => $e, 'width' => $w, 'shortened' => false];
            }
        }
        $min = self::eff($font, $min);
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        while (count($chars) > 1) {
            array_pop($chars);
            $cut = rtrim(implode('', $chars)) . self::ELLIPSIS;
            $w = PdfWriter::textWidth($cut, $font, $min);
            if ($w <= $maxW) {
                return ['text' => $cut, 'size' => $min, 'width' => $w, 'shortened' => true];
            }
        }
        return ['text' => self::ELLIPSIS, 'size' => $min, 'width' => PdfWriter::textWidth(self::ELLIPSIS, $font, $min), 'shortened' => true];
    }

    /** The size actually used for a reference size: sans lines are scaled (SANS_SCALE), serif lines are not. */
    public static function eff(string $font, float $size): float
    {
        return round($size * (self::SANS_SCALE[$font] ?? 1.0), 2);
    }

    /**
     * The member's name in large serif type: one line from 30 pt down to 20 pt; if that does not fit, two lines (split at the space that balances them)
     * from 22 pt down to 14 pt; the last resort is a shortened second line. 60-character names therefore never overflow.
     * @return array{lines:list<string>,size:float,shortened:bool}
     */
    public static function nameLines(string $name, float $maxW): array
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));
        if ($name === '') {
            return ['lines' => [''], 'size' => 30.0, 'shortened' => false];
        }
        for ($s = 30.0; $s >= 20.0; $s -= 0.5) {
            if (PdfWriter::textWidth($name, 'times-bold', $s) <= $maxW) {
                return ['lines' => [$name], 'size' => $s, 'shortened' => false];
            }
        }
        $words = explode(' ', $name);
        for ($s = 22.0; $s >= 14.0; $s -= 0.5) {
            $best = null;
            $bestDiff = PHP_FLOAT_MAX;
            for ($i = 1; $i < count($words); $i++) {
                $a = implode(' ', array_slice($words, 0, $i));
                $b = implode(' ', array_slice($words, $i));
                $wa = PdfWriter::textWidth($a, 'times-bold', $s);
                $wb = PdfWriter::textWidth($b, 'times-bold', $s);
                if ($wa <= $maxW && $wb <= $maxW && abs($wa - $wb) < $bestDiff) {
                    $best = [$a, $b];
                    $bestDiff = abs($wa - $wb);
                }
            }
            if ($best !== null) {
                return ['lines' => $best, 'size' => $s, 'shortened' => false];
            }
        }
        // one very long word, or a name that needs more than two lines at 14 pt: break near the middle and shorten the rest
        $half = intdiv(count($words), 2);
        $a = count($words) > 1 ? implode(' ', array_slice($words, 0, max(1, $half))) : $name;
        $b = count($words) > 1 ? implode(' ', array_slice($words, max(1, $half))) : '';
        $fa = self::fit($a, 'times-bold', 16.0, 14.0, $maxW);
        $lines = [$fa['text']];
        $short = $fa['shortened'];
        if ($b !== '' || $fa['shortened']) {
            $fb = self::fit($b !== '' ? $b : $a, 'times-bold', 16.0, 14.0, $maxW);
            if ($b !== '') {
                $lines[] = $fb['text'];
                $short = $short || $fb['shortened'];
            }
        }
        return ['lines' => $lines, 'size' => 14.0, 'shortened' => $short];
    }

    /** Fit and emit a centred text op on the navy panel. */
    private static function centred(array &$ops, array &$meta, string $name, string $text, string $font, float $size, float $min, float $maxW, float $y, string $color): void
    {
        $f = self::fit($text, $font, $size, $min, $maxW);
        if ($f['text'] === '') {
            return;
        }
        $ops[] = ['t' => 'text', 'x' => self::CX, 'y' => $y, 'text' => $f['text'], 'font' => $font, 'size' => $f['size'], 'color' => $color, 'align' => 'c'];
        self::note($meta, $name, $text, $f);
    }

    /** Remember fields that did not fit at their design size. @param array{text:string,size:float,width:float,shortened:bool} $f */
    private static function note(array &$meta, string $name, string $original, array $f): void
    {
        $meta['fitted'][$name] = ['size' => $f['size'], 'shortened' => $f['shortened']];
    }

    // ------------------------------------------------------------------------------------------------ QR and images

    /** The QR matrix for the verification URL: level M, falling back to L for a very long link; null when it cannot be encoded. @return array{version:int,modules:list<list<bool>>}|null */
    private static function qr(string $url): ?array
    {
        if ($url === '') {
            return null;
        }
        foreach (['M', 'L'] as $level) {
            try {
                $q = QrEncoder::encode($url, $level);
                return ['version' => $q['version'], 'modules' => $q['modules']];
            } catch (\InvalidArgumentException) {
                // too long at this level: try the next
            }
        }
        return null;
    }

    /**
     * Where to draw a picture of $iw x $ih pixels so it COVERS the box ($x,$y,$w,$h) and is cropped by the box: [x, y, w, h] of the whole picture.
     * Horizontal crop is centred; vertical crop keeps the top (faces sit in the upper part of a portrait): $topBias 0 = top edge kept, 0.5 = centred.
     * @return array{0:float,1:float,2:float,3:float}
     */
    public static function cover(int $iw, int $ih, float $x, float $y, float $w, float $h, float $topBias = self::PHOTO_BIAS): array
    {
        if ($iw < 1 || $ih < 1) {
            return [$x, $y, $w, $h];
        }
        $scale = max($w / $iw, $h / $ih);
        $dw = $iw * $scale;
        $dh = $ih * $scale;
        return [$x - ($dw - $w) / 2, $y - ($dh - $h) * $topBias, $dw, $dh];
    }

    /**
     * Geometry of a qr primitive: the module size and the top-left of the dark area, for both renderers.
     * @param array<string,mixed> $op @return array{module:float,x:float,y:float,n:int}
     */
    public static function qrGeometry(array $op): array
    {
        $n = count($op['modules']);
        $total = $n + 2 * (int) $op['quiet'];
        $module = (float) $op['size'] / $total;
        return ['module' => $module, 'x' => (float) $op['x'] + $module * (int) $op['quiet'], 'y' => (float) $op['y'] + $module * (int) $op['quiet'], 'n' => $n];
    }
}
