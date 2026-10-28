<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

/**
 * A small, dependency-free PDF 1.4 writer for the membership card (and any other one-page document). Pure PHP, no extension needed
 * (zlib is used for Flate when present, otherwise streams are stored plain).
 *
 *   $pdf = new PdfWriter(['title' => 'Membership card', 'author' => 'Lumina Africa Foundation']);
 *   $pdf->addPage(720, 414);                                    // points (1/72 inch)
 *   $pdf->fill('#0F1B33'); $pdf->roundRect(28, 66, 149, 52, 5, 'f');
 *   $pdf->text(40, 102, 'MEMBER SINCE', 'helvetica-bold', 8, '#6A7389');
 *   $img = $pdf->addJpeg($jpegBytes); $pdf->image($img, 28, 26, 64, 64);
 *   $bytes = $pdf->output();
 *
 * COORDINATES: the origin is the TOP-LEFT corner of the page, y grows DOWNWARD (like the screen and like CardLayout). The writer flips
 * y itself, so callers never think in PDF's bottom-left system. For text, y is the BASELINE.
 *
 * FONTS: the standard Type1 core fonts (never embedded, every viewer has them): helvetica, helvetica-bold, times-roman, times-bold,
 * times-italic, all with WinAnsiEncoding. Text goes in as UTF-8 and is converted to Windows-1252; characters outside it are
 * transliterated (accents dropped, African letters such as the eng, open-o and epsilon mapped to n, o, e) or become "?". Widths come from the
 * Adobe AFM tables (CoreFontMetrics), so textWidth() is exact and layout can fit text before it is drawn (no kerning is applied).
 * The writer never emits a raw control character inside a string: ( ) \ and every byte outside 32..126 are escaped.
 *
 * IMAGES: JPEG (DCT, embedded untouched: gray, RGB, CMYK with Adobe inversion handled), PNG (8-bit and lower, grey / RGB / palette /
 * grey+alpha / RGBA, tRNS; alpha becomes an /SMask; interlaced PNG is refused). Both return a resource name for image().
 *
 * OUTPUT: header, objects, a cross-reference table with exact byte offsets (20 bytes per entry), trailer with /Size /Root /Info /ID.
 */
final class PdfWriter
{
    /** key => [BaseFont, resource name] */
    public const FONTS = [
        'helvetica'      => ['Helvetica', 'F1'],
        'helvetica-bold' => ['Helvetica-Bold', 'F2'],
        'times-bold'     => ['Times-Bold', 'F3'],
        'times-italic'   => ['Times-Italic', 'F4'],
        'times-roman'    => ['Times-Roman', 'F5'],
    ];
    /** Quarter-circle Bezier constant. */
    private const KAPPA = 0.5522847498;
    private const MAX_IMAGE_PIXELS = 6000000;
    /** PNGs with transparency are decoded in PHP (about 1.4 s per megapixel): larger ones are left out of the card (GD converts them when it exists). */
    private const MAX_DECODE_PIXELS = 1200000;

    /** @var array<string,string> */
    private array $info;
    /** @var array<int,array{w:float,h:float,content:string,images:array<string,true>}> */
    private array $pages = [];
    private int $cur = -1;
    /** @var array<string,array<string,mixed>> resource name => image description */
    private array $images = [];
    private bool $compress;
    /** @var array<string,string> */
    private static array $translit = [];

    /** @param array{title?:string,author?:string,subject?:string,creator?:string,keywords?:string,date?:int,compress?:bool} $info */
    public function __construct(array $info = [])
    {
        $this->info = [
            'title'    => (string) ($info['title'] ?? ''),
            'author'   => (string) ($info['author'] ?? ''),
            'subject'  => (string) ($info['subject'] ?? ''),
            'keywords' => (string) ($info['keywords'] ?? ''),
            'creator'  => (string) ($info['creator'] ?? 'Lumina membership card'),
            'date'     => (string) (int) ($info['date'] ?? time()),
        ];
        $this->compress = (bool) ($info['compress'] ?? true) && function_exists('gzcompress');
    }

    // ------------------------------------------------------------------------------------------------ pages and state

    public function addPage(float $width, float $height): void
    {
        if ($width < 1 || $height < 1 || $width > 14400 || $height > 14400) {
            throw new \InvalidArgumentException('Page size out of range.');
        }
        $this->pages[] = ['w' => $width, 'h' => $height, 'content' => '', 'images' => []];
        $this->cur = count($this->pages) - 1;
    }

    public function pageHeight(): float
    {
        return $this->page()['h'];
    }

    public function pageWidth(): float
    {
        return $this->page()['w'];
    }

    /** Save the graphics state (q). */
    public function save(): void
    {
        $this->raw('q');
    }

    /** Restore the graphics state (Q). */
    public function restore(): void
    {
        $this->raw('Q');
    }

    /** Fill colour for the next shapes and text: "#rgb" or "#rrggbb". */
    public function fill(string $hex): void
    {
        $this->raw($this->rgb($hex) . ' rg');
    }

    public function stroke(string $hex): void
    {
        $this->raw($this->rgb($hex) . ' RG');
    }

    public function lineWidth(float $w): void
    {
        $this->raw(self::n($w) . ' w');
    }

    /** @param list<float> $pattern [] = solid, [2,3] = 2 on 3 off */
    public function dash(array $pattern, float $phase = 0.0): void
    {
        $this->raw('[' . implode(' ', array_map([self::class, 'n'], $pattern)) . '] ' . self::n($phase) . ' d');
    }

    // ------------------------------------------------------------------------------------------------ shapes

    /** @param string $mode f = fill, s = stroke, b = both, n = no paint (path only, e.g. for clipping) */
    public function rect(float $x, float $y, float $w, float $h, string $mode = 'f'): void
    {
        $this->raw(self::n($x) . ' ' . self::n($this->py($y + $h)) . ' ' . self::n($w) . ' ' . self::n($h) . ' re ' . $this->paint($mode));
    }

    /** Rounded rectangle (corners are quarter-circle Beziers). The radius is clamped to half of the shorter side. */
    public function roundRect(float $x, float $y, float $w, float $h, float $r, string $mode = 'f'): void
    {
        $this->raw($this->roundPath($x, $y, $w, $h, $r) . $this->paint($mode));
    }

    /** Clip everything drawn until restore() to a (rounded) rectangle. Wrap in save() / restore(). */
    public function clipRoundRect(float $x, float $y, float $w, float $h, float $r = 0.0): void
    {
        $this->raw($r > 0 ? $this->roundPath($x, $y, $w, $h, $r) . 'W n' : self::n($x) . ' ' . self::n($this->py($y + $h)) . ' ' . self::n($w) . ' ' . self::n($h) . ' re W n');
    }

    public function line(float $x1, float $y1, float $x2, float $y2): void
    {
        $this->raw(self::n($x1) . ' ' . self::n($this->py($y1)) . ' m ' . self::n($x2) . ' ' . self::n($this->py($y2)) . ' l S');
    }

    private function roundPath(float $x, float $y, float $w, float $h, float $r): string
    {
        $r = max(0.0, min($r, $w / 2, $h / 2));
        $b = $this->py($y + $h);                       // bottom edge in PDF space
        $t = $b + $h;                                  // top edge
        if ($r < 0.01) {
            return self::n($x) . ' ' . self::n($b) . ' ' . self::n($w) . ' ' . self::n($h) . ' re ';
        }
        $k = $r * self::KAPPA;
        $x2 = $x + $w;
        $o = [];
        $o[] = self::n($x + $r) . ' ' . self::n($b) . ' m';
        $o[] = self::n($x2 - $r) . ' ' . self::n($b) . ' l';
        $o[] = self::n($x2 - $r + $k) . ' ' . self::n($b) . ' ' . self::n($x2) . ' ' . self::n($b + $r - $k) . ' ' . self::n($x2) . ' ' . self::n($b + $r) . ' c';
        $o[] = self::n($x2) . ' ' . self::n($t - $r) . ' l';
        $o[] = self::n($x2) . ' ' . self::n($t - $r + $k) . ' ' . self::n($x2 - $r + $k) . ' ' . self::n($t) . ' ' . self::n($x2 - $r) . ' ' . self::n($t) . ' c';
        $o[] = self::n($x + $r) . ' ' . self::n($t) . ' l';
        $o[] = self::n($x + $r - $k) . ' ' . self::n($t) . ' ' . self::n($x) . ' ' . self::n($t - $r + $k) . ' ' . self::n($x) . ' ' . self::n($t - $r) . ' c';
        $o[] = self::n($x) . ' ' . self::n($b + $r) . ' l';
        $o[] = self::n($x) . ' ' . self::n($b + $r - $k) . ' ' . self::n($x + $r - $k) . ' ' . self::n($b) . ' ' . self::n($x + $r) . ' ' . self::n($b) . ' c';
        $o[] = 'h ';
        return implode(' ', $o);
    }

    private function paint(string $mode): string
    {
        return match ($mode) {
            's' => 'S', 'b' => 'B', 'n' => 'n', default => 'f',
        };
    }

    // ------------------------------------------------------------------------------------------------ text

    /**
     * Draw one line of text. $y is the BASELINE. $align: l (x = left edge), c (x = centre), r (x = right edge). Returns the width used (points).
     * The caller fits text before calling (CardLayout does); this method never wraps or shrinks.
     */
    public function text(float $x, float $y, string $utf8, string $font, float $size, ?string $color = null, string $align = 'l'): float
    {
        if (!isset(self::FONTS[$font])) {
            throw new \InvalidArgumentException('Unknown font ' . $font);
        }
        $bytes = self::toWinAnsi($utf8);
        $w = self::widthOfBytes($bytes, $font, $size);
        if ($bytes === '') {
            return 0.0;
        }
        $left = $align === 'c' ? $x - $w / 2 : ($align === 'r' ? $x - $w : $x);
        $this->page();
        $op = '';
        if ($color !== null) {
            $op .= $this->rgb($color) . " rg\n";
        }
        $op .= 'BT /' . self::FONTS[$font][1] . ' ' . self::n($size) . ' Tf ' . self::n($left) . ' ' . self::n($this->py($y)) . ' Td (' . self::escape($bytes) . ') Tj ET';
        $this->raw($op);
        return $w;
    }

    /** Width in points of $utf8 set in $font at $size (exact, from the AFM tables). */
    public static function textWidth(string $utf8, string $font, float $size): float
    {
        return self::widthOfBytes(self::toWinAnsi($utf8), $font, $size);
    }

    /** @param string $bytes Windows-1252 bytes */
    private static function widthOfBytes(string $bytes, string $font, float $size): float
    {
        $table = CoreFontMetrics::WIDTHS[$font] ?? null;
        if ($table === null) {
            throw new \InvalidArgumentException('Unknown font ' . $font);
        }
        $sum = 0;
        $n = strlen($bytes);
        for ($i = 0; $i < $n; $i++) {
            $code = ord($bytes[$i]);
            $sum += $table[$code - 32] ?? 0;
        }
        return $sum * $size / 1000;
    }

    /** Escape a byte string for a PDF literal string: backslash, parentheses and every byte outside 32..126 (octal). */
    public static function escape(string $bytes): string
    {
        $out = '';
        $n = strlen($bytes);
        for ($i = 0; $i < $n; $i++) {
            $c = $bytes[$i];
            $o = ord($c);
            if ($c === '\\' || $c === '(' || $c === ')') {
                $out .= '\\' . $c;
            } elseif ($o < 32 || $o > 126) {
                $out .= sprintf('\\%03o', $o);
            } else {
                $out .= $c;
            }
        }
        return $out;
    }

    /**
     * UTF-8 -> Windows-1252 bytes for the core fonts. Control and invisible formatting characters are dropped; characters that WinAnsi has are kept; a letter
     * followed by a combining mark (the "decomposed" form many phone keyboards produce: e + U+0301) is composed when WinAnsi has the accented letter, else the
     * plain letter is printed and the mark dropped; other letters are transliterated to the nearest plain letters (see fallbackChar(): African, Nordic, Vietnamese
     * and Latin Extended letters), anything else (Arabic, Cyrillic, CJK, emoji) becomes "?".
     */
    public static function toWinAnsi(string $utf8): string
    {
        if ($utf8 === '') {
            return '';
        }
        if (!mb_check_encoding($utf8, 'UTF-8')) {
            $utf8 = (string) mb_convert_encoding($utf8, 'UTF-8', 'UTF-8');
        }
        if (class_exists('Normalizer')) {
            $nfc = \Normalizer::normalize($utf8, \Normalizer::FORM_C);
            if (is_string($nfc)) {
                $utf8 = $nfc;
            }
        }
        static $special = [
            0x20AC => 0x80, 0x201A => 0x82, 0x0192 => 0x83, 0x201E => 0x84, 0x2026 => 0x85, 0x2020 => 0x86, 0x2021 => 0x87, 0x02C6 => 0x88,
            0x2030 => 0x89, 0x0160 => 0x8A, 0x2039 => 0x8B, 0x0152 => 0x8C, 0x017D => 0x8E, 0x2018 => 0x91, 0x2019 => 0x92, 0x201C => 0x93,
            0x201D => 0x94, 0x2022 => 0x95, 0x2013 => 0x96, 0x2014 => 0x97, 0x02DC => 0x98, 0x2122 => 0x99, 0x0161 => 0x9A, 0x203A => 0x9B,
            0x0153 => 0x9C, 0x017E => 0x9E, 0x0178 => 0x9F,
        ];
        $cps = [];
        foreach (preg_split('//u', $utf8, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            $cp = mb_ord($ch, 'UTF-8');
            if ($cp !== false) {
                $cps[] = $cp;
            }
        }
        $out = '';
        $count = count($cps);
        for ($i = 0; $i < $count; $i++) {
            $cp = $cps[$i];
            if ($cp >= 0x300 && $cp <= 0x36F) {
                continue;                                  // a combining mark with nothing to attach to (or left over after the letter): dropped
            }
            if ($i + 1 < $count && $cps[$i + 1] >= 0x300 && $cps[$i + 1] <= 0x36F) {
                $cp = self::compose($cp, $cps[$i + 1]) ?? $cp;     // e + acute -> é; the marks themselves are dropped by the rule above
            }
            if ($cp < 32 || ($cp >= 0x7F && $cp <= 0x9F) || ($cp >= 0x200B && $cp <= 0x200F) || $cp === 0x2028 || $cp === 0x2029 || $cp === 0xFEFF || ($cp >= 0x202A && $cp <= 0x202E) || ($cp >= 0x2060 && $cp <= 0x2064) || ($cp >= 0x2066 && $cp <= 0x2069)) {
                if ($cp === 9 || $cp === 10 || $cp === 13) {
                    $out .= ' ';
                }
                continue;                                  // control and invisible formatting characters never reach the PDF
            }
            if ($cp === 0xA0) {
                $out .= ' ';
            } elseif ($cp < 0x80 || ($cp >= 0xA1 && $cp <= 0xFF)) {
                $out .= chr($cp);
            } elseif (isset($special[$cp])) {
                $out .= chr($special[$cp]);
            } else {
                $out .= self::fallbackChar($cp);
            }
        }
        return $out;
    }

    /** The precomposed letter for a base letter + combining mark that WinAnsi carries (e + U+0301 -> é), else null. */
    private static function compose(int $base, int $mark): ?int
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            $pairs = [
                0x300 => 'AÀ EÈ IÌ OÒ UÙ aà eè iì oò uù',
                0x301 => 'AÁ EÉ IÍ OÓ UÚ YÝ aá eé ií oó uú yý',
                0x302 => 'AÂ EÊ IÎ OÔ UÛ aâ eê iî oô uû',
                0x303 => 'AÃ NÑ OÕ aã nñ oõ',
                0x308 => 'AÄ EË IÏ OÖ UÜ aä eë iï oö uü yÿ',
                0x30A => 'AÅ aå',
                0x30C => 'SŠ sš ZŽ zž',
                0x327 => 'CÇ cç',
            ];
            foreach ($pairs as $mk => $list) {
                foreach (explode(' ', $list) as $pair) {
                    $chars = preg_split('//u', $pair, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    if (count($chars) === 2) {
                        $map[$mk][(int) mb_ord($chars[0], 'UTF-8')] = (int) mb_ord($chars[1], 'UTF-8');
                    }
                }
            }
        }
        return $map[$mark][$base] ?? null;
    }

    /** One WinAnsi byte string (usually one letter) for a code point WinAnsi lacks: the nearest plain letter(s), else "?". */
    private static function fallbackChar(int $cp): string
    {
        if (self::$translit === []) {
            // code point (4 hex digits) + the plain letter(s); generated from the Unicode decompositions of Latin Extended-A / -B / Additional (Vietnamese, Yoruba dotted
            // letters ...) plus the African letters that have none: eng -> n, open o -> o, epsilon -> e, schwa -> e, hooked b / d / k, barred i, and so on
        $table = '' .
            '0100A0101a0102A0103a0104A0105a0106C0107c0108C0109c010AC010Bc010CC010Dc010ED010Fd0110D0111d0112E0113e' .
            '0114E0115e0116E0117e0118E0119e011AE011Be011CG011Dg011EG011Fg0120G0121g0122G0123g0124H0125h0126H0127h' .
            '0128I0129i012AI012Bi012CI012Di012EI012Fi0130I0131i0132IJ0133ij0134J0135j0136K0137k0138k0139L013Al013' .
            'BL013Cl013DL013El0141L0142l0143N0144n0145N0146n0147N0148n0149n014AN014Bn014CO014Do014EO014Fo0150O015' .
            '1o0154R0155r0156R0157r0158R0159r015AS015Bs015CS015Ds015ES015Fs0162T0163t0164T0165t0166T0167t0168U016' .
            '9u016AU016Bu016CU016Du016EU016Fu0170U0171u0172U0173u0174W0175w0176Y0177y0179Z017Az017BZ017Cz017Fs018' .
            '1B0182B0183b0186O0187C0188c0189D018AD018BD018Cd018EE018FE0190E0191F0193G0194G0195h0196I0197I0198K019' .
            '9k019CW019DN019En019FO01A0O01A1o01A2O01A3o01A4P01A5p01A6R01A7S01A8s01ABt01ACT01ADt01AET01AFU01B0u01B' .
            '2V01B3Y01B4y01B5Z01B6z01B7Z01B8Z01B9z01C4DZ01C5Dz01C6dz01C7LJ01C8Lj01C9lj01CANJ01CBNj01CCnj01CDA01CE' .
            'a01CFI01D0i01D1O01D2o01D3U01D4u01D5U01D6u01D7U01D8u01D9U01DAu01DBU01DCu01DDe01DEA01DFa01E0A01E1a01E6' .
            'G01E7g01E8K01E9k01EAO01EBo01ECO01EDo01F0j01F1DZ01F2Dz01F3dz01F4G01F5g01F8N01F9n01FAA01FBa0200A0201a0' .
            '202A0203a0204E0205e0206E0207e0208I0209i020AI020Bi020CO020Do020EO020Fo0210R0211r0212R0213r0214U0215u0' .
            '216U0217u0218S0219s021AT021Bt021EH021Fh0226A0227a0228E0229e022AO022Bo022CO022Do022EO022Fo0230O0231o0' .
            '232Y0233y0253b0254o0256d0257d0259e025Be0260g0263g0268i0269i0272n0275o028Bv0292z1D2CA1D2EB1D30D1D31E1' .
            'D33G1D34H1D35I1D36J1D37K1D38L1D39M1D3AN1D3CO1D3EP1D3FR1D40T1D41U1D42W1D43a1D47b1D48d1D49e1D4Dg1D4Fk1' .
            'D50m1D52o1D56p1D57t1D58u1D5Bv1D62i1D63r1D64u1D65v1E00A1E01a1E02B1E03b1E04B1E05b1E06B1E07b1E08C1E09c1' .
            'E0AD1E0Bd1E0CD1E0Dd1E0ED1E0Fd1E10D1E11d1E12D1E13d1E14E1E15e1E16E1E17e1E18E1E19e1E1AE1E1Be1E1CE1E1De1' .
            'E1EF1E1Ff1E20G1E21g1E22H1E23h1E24H1E25h1E26H1E27h1E28H1E29h1E2AH1E2Bh1E2CI1E2Di1E2EI1E2Fi1E30K1E31k1' .
            'E32K1E33k1E34K1E35k1E36L1E37l1E38L1E39l1E3AL1E3Bl1E3CL1E3Dl1E3EM1E3Fm1E40M1E41m1E42M1E43m1E44N1E45n1' .
            'E46N1E47n1E48N1E49n1E4AN1E4Bn1E4CO1E4Do1E4EO1E4Fo1E50O1E51o1E52O1E53o1E54P1E55p1E56P1E57p1E58R1E59r1' .
            'E5AR1E5Br1E5CR1E5Dr1E5ER1E5Fr1E60S1E61s1E62S1E63s1E64S1E65s1E66S1E67s1E68S1E69s1E6AT1E6Bt1E6CT1E6Dt1' .
            'E6ET1E6Ft1E70T1E71t1E72U1E73u1E74U1E75u1E76U1E77u1E78U1E79u1E7AU1E7Bu1E7CV1E7Dv1E7EV1E7Fv1E80W1E81w1' .
            'E82W1E83w1E84W1E85w1E86W1E87w1E88W1E89w1E8AX1E8Bx1E8CX1E8Dx1E8EY1E8Fy1E90Z1E91z1E92Z1E93z1E94Z1E95z1' .
            'E96h1E97t1E98w1E99y1E9Bs1E9ESS1EA0A1EA1a1EA2A1EA3a1EA4A1EA5a1EA6A1EA7a1EA8A1EA9a1EAAA1EABa1EACA1EADa' .
            '1EAEA1EAFa1EB0A1EB1a1EB2A1EB3a1EB4A1EB5a1EB6A1EB7a1EB8E1EB9e1EBAE1EBBe1EBCE1EBDe1EBEE1EBFe1EC0E1EC1e' .
            '1EC2E1EC3e1EC4E1EC5e1EC6E1EC7e1EC8I1EC9i1ECAI1ECBi1ECCO1ECDo1ECEO1ECFo1ED0O1ED1o1ED2O1ED3o1ED4O1ED5o' .
            '1ED6O1ED7o1ED8O1ED9o1EDAO1EDBo1EDCO1EDDo1EDEO1EDFo1EE0O1EE1o1EE2O1EE3o1EE4U1EE5u1EE6U1EE7u1EE8U1EE9u' .
            '1EEAU1EEBu1EECU1EEDu1EEEU1EEFu1EF0U1EF1u1EF2Y1EF3y1EF4Y1EF5y1EF6Y1EF7y1EF8Y1EF9y';
            if (preg_match_all('/(\d[0-9A-F]{3})([A-Za-z]+)/', $table, $mm, PREG_SET_ORDER) > 0) {
                foreach ($mm as $hit) {
                    self::$translit[(string) hexdec($hit[1])] = $hit[2];
                }
            }
            foreach ([0x2BC => "'", 0x2B9 => "'", 0x2032 => "'", 0x2033 => '"', 0x2212 => '-', 0x2010 => '-', 0x2011 => '-', 0x2012 => '-', 0x2015 => '-',
                0x2002 => ' ', 0x2003 => ' ', 0x2009 => ' ', 0x00B4 => "'", 0x2024 => '.', 0x00A0 => ' ', 0x2019 => "'", 0x02BB => "'", 0x02BD => "'", 0x2035 => "'"] as $p => $r) {
                self::$translit[(string) $p] = $r;
            }
        }
        return self::$translit[(string) $cp] ?? '?';
    }

    // ------------------------------------------------------------------------------------------------ images

    /**
     * Register a JPEG (kept byte for byte, DCTDecode). Returns the resource name for image().
     * @throws \InvalidArgumentException when the data is not a JPEG this writer can place
     */
    public function addJpeg(string $bytes): string
    {
        $info = self::jpegInfo($bytes);
        $name = 'Im' . (count($this->images) + 1);
        $cs = [1 => '/DeviceGray', 3 => '/DeviceRGB', 4 => '/DeviceCMYK'][$info['channels']];
        $this->images[$name] = [
            'dict' => '/Type /XObject /Subtype /Image /Width ' . $info['w'] . ' /Height ' . $info['h'] . ' /ColorSpace ' . $cs . ' /BitsPerComponent 8 /Filter /DCTDecode'
                . ($info['channels'] === 4 && $info['adobe'] ? ' /Decode [1 0 1 0 1 0 1 0]' : ''),
            'data' => $bytes, 'smask' => null,
        ];
        return $name;
    }

    /**
     * Register a PNG. Greyscale, RGB, palette (with tRNS), greyscale+alpha and RGBA at 8 bits (and 1/2/4-bit grey and palette); alpha is
     * written as a soft mask. 16-bit and interlaced PNGs are refused.
     * @throws \InvalidArgumentException
     */
    public function addPng(string $bytes): string
    {
        $png = self::pngParse($bytes);
        $name = 'Im' . (count($this->images) + 1);
        $w = $png['w'];
        $h = $png['h'];
        $type = $png['type'];
        $depth = $png['depth'];
        $hasTrns = $png['trns'] !== null;
        $smask = null;
        if ($type === 4 || $type === 6 || $hasTrns) {
            // alpha: decode the pixels ourselves and split colour and alpha
            [$color, $alpha, $channels] = self::pngSplitAlpha($png);
            $dict = '/Type /XObject /Subtype /Image /Width ' . $w . ' /Height ' . $h . ' /BitsPerComponent 8 /ColorSpace '
                . ($channels === 1 ? '/DeviceGray' : '/DeviceRGB');
            $smask = ['dict' => '/Type /XObject /Subtype /Image /Width ' . $w . ' /Height ' . $h . ' /ColorSpace /DeviceGray /BitsPerComponent 8', 'data' => $alpha, 'raw' => true];
            $this->images[$name] = ['dict' => $dict, 'data' => $color, 'raw' => true, 'smask' => $smask];
            return $name;
        }
        $channels = $type === 2 ? 3 : 1;
        $cs = match (true) {
            $type === 3 => '[/Indexed /DeviceRGB ' . (strlen($png['plte']) / 3 - 1) . ' <' . bin2hex($png['plte']) . '>]',
            $channels === 3 => '/DeviceRGB',
            default => '/DeviceGray',
        };
        $this->images[$name] = [
            'dict' => '/Type /XObject /Subtype /Image /Width ' . $w . ' /Height ' . $h . ' /BitsPerComponent ' . $depth . ' /ColorSpace ' . $cs
                . ' /Filter /FlateDecode /DecodeParms << /Predictor 15 /Colors ' . ($type === 3 ? 1 : $channels) . ' /BitsPerComponent ' . $depth . ' /Columns ' . $w . ' >>',
            'data' => $png['idat'], 'smask' => null,
        ];
        return $name;
    }

    /** Place a registered image: its box is x, y (top-left), w, h in points. */
    public function image(string $name, float $x, float $y, float $w, float $h): void
    {
        if (!isset($this->images[$name])) {
            throw new \InvalidArgumentException('Unknown image ' . $name);
        }
        $this->page();
        $this->pages[$this->cur]['images'][$name] = true;
        $this->raw('q ' . self::n($w) . ' 0 0 ' . self::n($h) . ' ' . self::n($x) . ' ' . self::n($this->py($y + $h)) . ' cm /' . $name . ' Do Q');
    }

    /** Pixel size of a registered image. @return array{0:int,1:int} */
    public function imageSize(string $name): array
    {
        if (!isset($this->images[$name])) {
            throw new \InvalidArgumentException('Unknown image ' . $name);
        }
        preg_match('~/Width (\d+) /Height (\d+)~', (string) $this->images[$name]['dict'], $m);
        return [(int) $m[1], (int) $m[2]];
    }

    /**
     * Can this writer embed these picture bytes? Returns ['mime' => 'image/jpeg'|'image/png', 'w' => int, 'h' => int] or null (WebP, GIF, interlaced or
     * 16-bit PNG, progressive-free CMYK oddities, damaged files). Nothing is registered.
     * @return array{mime:string,w:int,h:int}|null
     */
    public static function imageInfo(string $bytes): ?array
    {
        try {
            if (str_starts_with($bytes, "\xFF\xD8")) {
                $i = self::jpegInfo($bytes);
                return ['mime' => 'image/jpeg', 'w' => $i['w'], 'h' => $i['h']];
            }
            if (str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
                $p = self::pngParse($bytes);
                if (($p['type'] === 4 || $p['type'] === 6 || $p['trns'] !== null) && $p['w'] * $p['h'] > self::MAX_DECODE_PIXELS) {
                    return null;                     // alpha has to be decoded in PHP (slow, memory hungry): only small pictures
                }
                return ['mime' => 'image/png', 'w' => $p['w'], 'h' => $p['h']];
            }
        } catch (\InvalidArgumentException) {
            return null;
        }
        return null;
    }

    /** @return array{w:int,h:int,channels:int,adobe:bool} */
    public static function jpegInfo(string $d): array
    {
        $n = strlen($d);
        if ($n < 4 || !str_starts_with($d, "\xFF\xD8")) {
            throw new \InvalidArgumentException('Not a JPEG.');
        }
        $p = 2;
        $adobe = false;
        while ($p + 4 <= $n) {
            if ($d[$p] !== "\xFF") {
                $p++;
                continue;
            }
            $m = ord($d[$p + 1]);
            if ($m === 0xFF) {
                $p++;
                continue;
            }
            if ($m === 0xD8 || ($m >= 0xD0 && $m <= 0xD7) || $m === 0x01) {
                $p += 2;
                continue;
            }
            $len = (ord($d[$p + 2]) << 8) | ord($d[$p + 3]);
            if ($m === 0xEE && $len >= 14 && substr($d, $p + 4, 5) === 'Adobe') {
                $adobe = true;
            }
            if (in_array($m, [0xC0, 0xC1, 0xC2, 0xC3, 0xC5, 0xC6, 0xC7, 0xC9, 0xCA, 0xCB, 0xCD, 0xCE, 0xCF], true)) {
                if (in_array($m, [0xC3, 0xC5, 0xC6, 0xC7, 0xC9, 0xCA, 0xCB, 0xCD, 0xCE, 0xCF], true) || ord($d[$p + 4]) !== 8) {
                    throw new \InvalidArgumentException('Unsupported JPEG type.');      // lossless / arithmetic / non-8-bit
                }
                $h = (ord($d[$p + 5]) << 8) | ord($d[$p + 6]);
                $w = (ord($d[$p + 7]) << 8) | ord($d[$p + 8]);
                $c = ord($d[$p + 9]);
                if (!in_array($c, [1, 3, 4], true) || $w < 1 || $h < 1 || $w * $h > self::MAX_IMAGE_PIXELS * 4) {
                    throw new \InvalidArgumentException('Unsupported JPEG size or colour.');
                }
                return ['w' => $w, 'h' => $h, 'channels' => $c, 'adobe' => $adobe];
            }
            $p += 2 + $len;
        }
        throw new \InvalidArgumentException('JPEG has no frame header.');
    }

    /** @return array{w:int,h:int,depth:int,type:int,plte:string,trns:?string,idat:string} */
    private static function pngParse(string $d): array
    {
        if (!str_starts_with($d, "\x89PNG\r\n\x1a\n")) {
            throw new \InvalidArgumentException('Not a PNG.');
        }
        $p = 8;
        $n = strlen($d);
        $ihdr = null;
        $plte = '';
        $trns = null;
        $idat = '';
        while ($p + 12 <= $n) {
            $len = unpack('N', substr($d, $p, 4))[1];
            $type = substr($d, $p + 4, 4);
            if ($len > $n) {
                break;
            }
            $body = substr($d, $p + 8, $len);
            if ($type === 'IHDR') {
                $ihdr = $body;
            } elseif ($type === 'PLTE') {
                $plte = $body;
            } elseif ($type === 'tRNS') {
                $trns = $body;
            } elseif ($type === 'IDAT') {
                $idat .= $body;
            } elseif ($type === 'IEND') {
                break;
            }
            $p += 12 + $len;
        }
        if ($ihdr === null || strlen($ihdr) < 13 || $idat === '') {
            throw new \InvalidArgumentException('Damaged PNG.');
        }
        $h = unpack('Nw/Nh/Cdepth/Ctype/Ccomp/Cfilter/Cinterlace', $ihdr);
        if ($h['interlace'] !== 0 || $h['comp'] !== 0 || $h['filter'] !== 0) {
            throw new \InvalidArgumentException('Interlaced PNG is not supported.');
        }
        if ($h['depth'] === 16 || !in_array($h['depth'], [1, 2, 4, 8], true) || $h['w'] < 1 || $h['h'] < 1 || $h['w'] * $h['h'] > self::MAX_IMAGE_PIXELS) {
            throw new \InvalidArgumentException('Unsupported PNG size or bit depth.');
        }
        if (($h['type'] === 3 && ($plte === '' || strlen($plte) % 3 !== 0)) || !in_array($h['type'], [0, 2, 3, 4, 6], true)) {
            throw new \InvalidArgumentException('Unsupported PNG colour type.');
        }
        if (($h['type'] === 2 || $h['type'] === 4 || $h['type'] === 6) && $h['depth'] !== 8) {
            throw new \InvalidArgumentException('Unsupported PNG bit depth.');
        }
        return ['w' => $h['w'], 'h' => $h['h'], 'depth' => $h['depth'], 'type' => $h['type'], 'plte' => $plte, 'trns' => $trns, 'idat' => $idat];
    }

    /**
     * Decode the pixels, undo the PNG row filters, and split into colour bytes and 8-bit alpha.
     * @param array{w:int,h:int,depth:int,type:int,plte:string,trns:?string,idat:string} $png
     * @return array{0:string,1:string,2:int} [colour data (rows of channels, 8-bit), alpha data, colour channels (1 or 3)]
     */
    private static function pngSplitAlpha(array $png): array
    {
        $w = $png['w'];
        $h = $png['h'];
        $depth = $png['depth'];
        $type = $png['type'];
        if ($w * $h > self::MAX_DECODE_PIXELS) {
            throw new \InvalidArgumentException('PNG with transparency is too large to decode here.');
        }
        $bitsPerPixel = $depth * ([0 => 1, 2 => 3, 3 => 1, 4 => 2, 6 => 4][$type]);
        $bpp = max(1, intdiv($bitsPerPixel, 8));
        $rowBytes = intdiv($w * $bitsPerPixel + 7, 8);
        // the decoded size is known from the header: never inflate more than that (a decompression bomb would exhaust the memory)
        $raw = @gzuncompress($png['idat'], ($rowBytes + 1) * $h);
        if ($raw === false) {
            throw new \InvalidArgumentException('Damaged PNG data.');
        }
        if (strlen($raw) < ($rowBytes + 1) * $h) {
            throw new \InvalidArgumentException('Truncated PNG data.');
        }
        $prev = array_fill(0, $rowBytes, 0);
        $colour = [];
        $alpha = [];
        $trns = $png['trns'];
        $pal = $png['plte'];
        $palAlpha = [];
        if ($type === 3 && $trns !== null) {
            $palAlpha = array_values(unpack('C*', $trns) ?: []);
        }
        $grey = null;
        $rgbKey = null;
        if ($type === 0 && $trns !== null && strlen($trns) >= 2) {
            $grey = unpack("n", $trns)[1];
        } elseif ($type === 2 && $trns !== null && strlen($trns) >= 6) {
            $k = array_values(unpack('n3', $trns));
            $rgbKey = [$k[0] & 0xFF, $k[1] & 0xFF, $k[2] & 0xFF];
        }
        $channels = ($type === 2 || $type === 6 || $type === 3) ? 3 : 1;
        $pos = 0;
        for ($y = 0; $y < $h; $y++) {
            $f = ord($raw[$pos]);
            $line = array_values(unpack('C*', substr($raw, $pos + 1, $rowBytes)) ?: []);
            $pos += $rowBytes + 1;
            if ($f !== 0) {
                for ($i = 0; $i < $rowBytes; $i++) {
                    $a = $i >= $bpp ? $line[$i - $bpp] : 0;
                    $b = $prev[$i];
                    $c = $i >= $bpp ? $prev[$i - $bpp] : 0;
                    switch ($f) {
                        case 1: $line[$i] = ($line[$i] + $a) & 0xFF; break;
                        case 2: $line[$i] = ($line[$i] + $b) & 0xFF; break;
                        case 3: $line[$i] = ($line[$i] + (($a + $b) >> 1)) & 0xFF; break;
                        case 4:
                            $pa = abs($b - $c);
                            $pb = abs($a - $c);
                            $pc = abs($a + $b - 2 * $c);
                            $pr = ($pa <= $pb && $pa <= $pc) ? $a : ($pb <= $pc ? $b : $c);
                            $line[$i] = ($line[$i] + $pr) & 0xFF;
                            break;
                        default: throw new \InvalidArgumentException('Damaged PNG filter.');
                    }
                }
            }
            $prev = $line;
            $rc = '';
            $ra = '';
            if ($type === 6) {
                for ($x = 0; $x < $w; $x++) {
                    $o = $x * 4;
                    $rc .= chr($line[$o]) . chr($line[$o + 1]) . chr($line[$o + 2]);
                    $ra .= chr($line[$o + 3]);
                }
            } elseif ($type === 4) {
                for ($x = 0; $x < $w; $x++) {
                    $rc .= chr($line[$x * 2]);
                    $ra .= chr($line[$x * 2 + 1]);
                }
            } elseif ($type === 2) {
                for ($x = 0; $x < $w; $x++) {
                    $o = $x * 3;
                    $rc .= chr($line[$o]) . chr($line[$o + 1]) . chr($line[$o + 2]);
                    $ra .= ($rgbKey !== null && $line[$o] === $rgbKey[0] && $line[$o + 1] === $rgbKey[1] && $line[$o + 2] === $rgbKey[2]) ? "\x00" : "\xFF";
                }
            } else {                                            // 0 (grey) and 3 (palette), 1..8 bits per pixel
                $maxv = (1 << $depth) - 1;
                for ($x = 0; $x < $w; $x++) {
                    $bit = $x * $depth;
                    $v = ($line[$bit >> 3] >> (8 - $depth - ($bit & 7))) & $maxv;
                    if ($type === 3) {
                        $rc .= str_pad(substr($pal, $v * 3, 3), 3, "\0");
                        $ra .= chr($palAlpha[$v] ?? 255);
                    } else {
                        $g8 = $depth === 8 ? $v : (int) round($v * 255 / $maxv);
                        $rc .= chr($g8);
                        $ra .= ($grey !== null && $v === ($grey & $maxv)) ? "\x00" : "\xFF";
                    }
                }
            }
            $colour[] = $rc;
            $alpha[] = $ra;
        }
        return [implode('', $colour), implode('', $alpha), $channels];
    }

    // ------------------------------------------------------------------------------------------------ output

    /** The finished PDF file as a binary string. */
    public function output(): string
    {
        if ($this->pages === []) {
            throw new \LogicException('The PDF has no page.');
        }
        /** @var array<int,string> $objs id => complete "N 0 obj ... endobj\n" text */
        $objs = [];
        $next = 4;                                          // 1 = catalog, 2 = pages, 3 = info
        $fontIds = [];
        foreach (self::FONTS as $key => [$base, $res]) {
            $fontIds[$res] = $next++;
            $objs[$fontIds[$res]] = $this->obj($fontIds[$res], '<< /Type /Font /Subtype /Type1 /BaseFont /' . $base . ' /Encoding /WinAnsiEncoding >>');
        }
        $imageIds = [];
        foreach ($this->images as $name => $img) {
            $smaskId = null;
            if ($img['smask'] !== null) {
                $smaskId = $next++;
                $objs[$smaskId] = $this->streamObj($smaskId, (string) $img['smask']['dict'], (string) $img['smask']['data'], $this->compress);
            }
            $imageIds[$name] = $next++;
            $dict = (string) $img['dict'] . ($smaskId !== null ? ' /SMask ' . $smaskId . ' 0 R' : '');
            $objs[$imageIds[$name]] = $this->streamObj($imageIds[$name], $dict, (string) $img['data'], !empty($img['raw']) && $this->compress);
        }
        $pageIds = [];
        foreach ($this->pages as $i => $pg) {
            $contentId = $next++;
            $pageId = $next++;
            $pageIds[] = $pageId;
            $stream = $pg['content'];
            $objs[$contentId] = $this->streamObj($contentId, '', $stream, $this->compress);
            $xo = '';
            foreach (array_keys($pg['images']) as $name) {
                $xo .= '/' . $name . ' ' . $imageIds[$name] . ' 0 R ';
            }
            $fonts = '';
            foreach ($fontIds as $res => $id) {
                $fonts .= '/' . $res . ' ' . $id . ' 0 R ';
            }
            $objs[$pageId] = $this->obj($pageId, '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::n($pg['w']) . ' ' . self::n($pg['h']) . '] /Contents ' . $contentId . ' 0 R '
                . '/Resources << /Font << ' . $fonts . '>> ' . ($xo !== '' ? '/XObject << ' . $xo . '>> ' : '') . '/ProcSet [/PDF /Text /ImageB /ImageC] >> >>');
        }
        $objs[1] = $this->obj(1, '<< /Type /Catalog /Pages 2 0 R >>');
        $objs[2] = $this->obj(2, '<< /Type /Pages /Kids [' . implode(' ', array_map(static fn(int $id): string => $id . ' 0 R', $pageIds)) . '] /Count ' . count($pageIds) . ' >>');
        $info = '<<';
        foreach (['title' => 'Title', 'author' => 'Author', 'subject' => 'Subject', 'keywords' => 'Keywords', 'creator' => 'Creator'] as $k => $label) {
            if ($this->info[$k] !== '') {
                $info .= ' /' . $label . ' ' . self::textString($this->info[$k]);
            }
        }
        $info .= ' /Producer (Lumina PdfWriter) /CreationDate (' . gmdate("\D:YmdHis", (int) $this->info['date']) . "+00'00') >>";
        $objs[3] = $this->obj(3, $info);
        ksort($objs);

        $out = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($objs as $id => $text) {
            $offsets[$id] = strlen($out);
            $out .= $text;
        }
        $size = max(array_keys($objs)) + 1;
        $xref = strlen($out);
        $out .= "xref\n0 {$size}\n0000000000 65535 f \n";
        for ($id = 1; $id < $size; $id++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $idHex = md5($out . $this->info['title'] . $this->info['date']);
        $out .= "trailer\n<< /Size {$size} /Root 1 0 R /Info 3 0 R /ID [<{$idHex}> <{$idHex}>] >>\nstartxref\n{$xref}\n%%EOF\n";
        return $out;
    }

    private function obj(int $id, string $body): string
    {
        return $id . " 0 obj\n" . $body . "\nendobj\n";
    }

    /** A stream object. $compress: deflate $data here (skipped when the dictionary already names a /Filter, e.g. DCTDecode, or when zlib is missing). */
    private function streamObj(int $id, string $dict, string $data, bool $compress): string
    {
        $filter = '';
        if ($compress && function_exists("gzcompress") && !str_contains($dict, "/Filter")) {
            $z = gzcompress($data, 6);
            if ($z !== false && strlen($z) < strlen($data)) {
                $data = $z;
                $filter = " /Filter /FlateDecode";
            }
        }
        return $id . " 0 obj\n<< " . trim($dict . $filter . " /Length " . strlen($data)) . " >>\nstream\n" . $data . "\nendstream\nendobj\n";
    }
    /** A PDF text string: a plain literal for ASCII, UTF-16BE hex (with BOM) otherwise. */
    public static function textString(string $s): string
    {
        $s = (string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $s);
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = (string) mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        }
        if (preg_match('/^[\x20-\x7E]*$/D', $s) === 1) {
            return '(' . self::escape($s) . ')';
        }
        return '<FEFF' . strtoupper(bin2hex((string) mb_convert_encoding($s, 'UTF-16BE', 'UTF-8'))) . '>';
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** @return array{w:float,h:float,content:string,images:array<string,true>} */
    private function page(): array
    {
        if ($this->cur < 0) {
            throw new \LogicException('Call addPage() first.');
        }
        return $this->pages[$this->cur];
    }

    /** Append raw PDF operators (PDF coordinates, origin bottom-left): used for artwork copied from a design, e.g. the Academy certificate seal. */
    public function rawOps(string $ops): void
    {
        $this->raw($ops);
    }

    private function raw(string $op): void
    {
        $this->page();
        $this->pages[$this->cur]['content'] .= $op . "\n";
    }

    /** PDF y (bottom-left origin) for a top-left y. */
    private function py(float $y): float
    {
        return $this->pages[$this->cur]['h'] - $y;
    }

    /** "0.058824 0.105882 0.2" from "#0F1B33"; anything unparsable becomes black. */
    private function rgb(string $hex): string
    {
        $h = ltrim(trim($hex), '#');
        if (preg_match('/^[0-9a-fA-F]{3}$/D', $h) === 1) {
            $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }
        if (preg_match('/^[0-9a-fA-F]{6}$/D', $h) !== 1) {
            $h = '000000';
        }
        return self::n(hexdec(substr($h, 0, 2)) / 255) . ' ' . self::n(hexdec(substr($h, 2, 2)) / 255) . ' ' . self::n(hexdec(substr($h, 4, 2)) / 255);
    }

    /** A number for a content stream: at most 3 decimals, no exponent, never locale-dependent. */
    public static function n(float|int $v): string
    {
        $s = rtrim(rtrim(sprintf('%.3F', (float) $v), '0'), '.');
        return $s === '' || $s === '-0' ? '0' : $s;
    }
}
