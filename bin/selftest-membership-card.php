<?php
/**
 * MEMBERSHIP CARD checks, included by bin/selftest.php (shares its check() helper and $fail counter). Contract: docs/MEMBERSHIP_CARD.md.
 * Part 1 (this file): the pure engines. PdfWriter (structure, xref offsets, stream lengths, escaping fuzz, fonts, images), QrEncoder (ISO example, format and
 * version tables, capacities, 80 vectors from an independent implementation), CardSettings (defaults = seed, hostile input, number formats, contrast), CardData and
 * CardLayout (long / accented names, blocks switched off), CardPdf / CardPng, schema + migration on a scratch database.
 * Part 2 (selftest-membership-card-flow.php): numbering, the issue triggers, idempotency, tokens, files, erasure, e-mail, the HTTP flows.
 * Creates and removes its own data. Everything GD-related is checked when GD is loaded (run with  php -d extension=gd bin/selftest.php) and skipped otherwise.
 */
declare(strict_types=1);

use App\Core\Database;
use App\Core\Settings;
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\CardLayout;
use App\Services\Membership\Card\CardPdf;
use App\Services\Membership\Card\CardPng;
use App\Services\Membership\Card\CardRenderer;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\Card\PdfWriter;
use App\Services\Membership\Card\QrEncoder;

echo "Membership card: PDF writer\n";
$mcGd = extension_loaded('gd');
echo '  info ' . ($mcGd ? 'GD loaded: PNG preview and photo processing are checked' : 'GD not loaded: the no-GD paths are checked (run with -d extension=gd for the GD paths)') . PHP_EOL;

/** Structural validation of a PDF produced by PdfWriter: header, xref offsets, stream lengths, trailer. @return list<string> problems */
function mc_pdf_problems(string $pdf): array
{
    $p = [];
    if (!str_starts_with($pdf, "%PDF-1.4\n")) {
        $p[] = 'header';
    }
    if (!str_ends_with($pdf, "%%EOF\n")) {
        $p[] = 'EOF marker';
    }
    if (preg_match('/startxref\n(\d+)\n%%EOF\n$/', $pdf, $m) !== 1) {
        return array_merge($p, ['startxref']);
    }
    $xrefPos = (int) $m[1];
    if (substr($pdf, $xrefPos, 5) !== "xref\n") {
        return array_merge($p, ['startxref does not point at "xref"']);
    }
    if (preg_match('/xref\n0 (\d+)\n/', $pdf, $m, 0, $xrefPos) !== 1) {
        return array_merge($p, ['xref subsection']);
    }
    $count = (int) $m[1];
    $tablePos = $xrefPos + strlen($m[0]);
    if (substr($pdf, $tablePos, 20) !== "0000000000 65535 f \n") {
        $p[] = 'free entry';
    }
    for ($i = 1; $i < $count; $i++) {
        $entry = substr($pdf, $tablePos + 20 * $i, 20);
        if (preg_match('/^(\d{10}) 00000 n \n$/', $entry, $e) !== 1) {
            $p[] = "entry $i is not 20 bytes";
            continue;
        }
        if (substr($pdf, (int) $e[1], strlen($i . ' 0 obj')) !== $i . ' 0 obj') {
            $p[] = "object $i is not at offset " . (int) $e[1];
        }
    }
    if (preg_match('/trailer\n<< \/Size (\d+) \/Root (\d+) 0 R \/Info (\d+) 0 R/', $pdf, $m) !== 1 || (int) $m[1] !== $count) {
        $p[] = 'trailer /Size';
    }
    // every stream: /Length equals the bytes between "stream\n" and "\nendstream"
    $pos = 0;
    while (($s = strpos($pdf, "stream\n", $pos)) !== false) {
        if ($s >= 3 && substr($pdf, $s - 3, 3) === 'end') {
            $pos = $s + 7;
            continue;
        }
        $headStart = (int) strrpos(substr($pdf, 0, $s), "obj\n");
        $head = substr($pdf, $headStart, $s - $headStart);
        $e = strpos($pdf, "\nendstream", $s + 7);
        if ($e === false || preg_match('/\/Length (\d+)/', $head, $lm) !== 1 || (int) $lm[1] !== $e - ($s + 7)) {
            $p[] = 'stream length at ' . $s;
        }
        $pos = ($e === false ? strlen($pdf) : $e) + 10;
    }
    $objs = preg_match_all('/^\d+ 0 obj\n/m', $pdf);
    if ($objs !== $count - 1) {
        $p[] = "object count $objs vs xref " . ($count - 1);
    }
    return $p;
}

/** All (text) strings of the page content stream of a one-page PDF written by PdfWriter, as WinAnsi byte strings. @return list<string> */
function mc_pdf_strings(string $pdf): array
{
    if (preg_match('/<< (?:\/Filter \/FlateDecode )?\/Length \d+ >>\nstream\n(.*?)\nendstream/s', $pdf, $m) !== 1) {
        return [];
    }
    $c = str_contains(substr($pdf, strpos($pdf, $m[0]) ?: 0, 80), 'FlateDecode') ? (string) gzuncompress($m[1]) : $m[1];
    $out = [];
    if (preg_match_all('/\((?:[^()\\\\]|\\\\.)*\) Tj/s', $c, $mm)) {
        foreach ($mm[0] as $tj) {
            $inner = substr($tj, 1, -4);
            $out[] = (string) preg_replace_callback('/\\\\([0-7]{3}|.)/s', static fn(array $x): string => strlen($x[1]) === 3 ? chr((int) octdec($x[1])) : $x[1], $inner);
        }
    }
    return $out;
}

/** A PNG file built in pure PHP (no GD): colour type 0 grey, 2 RGB, 3 palette (+ tRNS), 4 grey+alpha, 6 RGBA, 8 bit unless told. */
function mc_png(int $w, int $h, int $type = 2, int $depth = 8, bool $interlace = false, bool $trns = false): string
{
    $chan = [0 => 1, 2 => 3, 3 => 1, 4 => 2, 6 => 4][$type];
    $rowBytes = intdiv($w * $chan * $depth + 7, 8);
    $raw = '';
    for ($y = 0; $y < $h; $y++) {
        $raw .= "\x00";                                              // filter 0 (None)
        for ($b = 0; $b < $rowBytes; $b++) {
            $raw .= chr(($y * 7 + $b * 3 + ($y > $h / 2 ? 90 : 20)) & 0xFF);
        }
    }
    $chunk = static fn(string $t, string $d): string => pack('N', strlen($d)) . $t . $d . pack('N', crc32($t . $d));
    $png = "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', $w, $h, $depth, $type, 0, 0, $interlace ? 1 : 0));
    if ($type === 3) {
        $png .= $chunk('PLTE', str_repeat("\x10\x80\xF0\xE0\x64\x2C", 128));
        if ($trns) {
            $png .= $chunk('tRNS', str_repeat("\xFF\x80", 128));
        }
    }
    return $png . $chunk('IDAT', (string) gzcompress($raw, 6)) . $chunk('IEND', '');
}

// ---------------------------------------------------------------------------------------------------- PdfWriter
$w = new PdfWriter(['title' => 'Membership card LAF-IND-26-0001', 'author' => 'Lumina Africa Foundation', 'date' => 1792000000]);
$w->addPage(720, 414);
$w->fill('#0F1B33');
$w->roundRect(28, 66, 149.333, 52, 5, 'f');
$w->text(40, 102, 'MEMBER SINCE', 'helvetica-bold', 8, '#6A7389');
$w->text(360, 80, 'Centred (ok)', 'times-italic', 12, '#C9973F', 'c');
$w->text(692, 80, 'Right', 'helvetica', 12, '#FFFFFF', 'r');
$w->save();
$w->clipRoundRect(28, 130, 116, 116, 6);
$w->stroke('#8F9AB5');
$w->lineWidth(0.8);
$w->dash([2, 3]);
$w->line(520, 0, 520, 414);
$w->restore();
$pdf = $w->output();
$problems = mc_pdf_problems($pdf);
check('PDF: header, cross-reference offsets (20 bytes each), object count, trailer and every stream length are exact', $problems === [], implode('; ', $problems));
check('PDF: document info (Title, Author, Creator, Producer, CreationDate) and a /ID in the trailer', str_contains($pdf, '/Title (Membership card LAF-IND-26-0001)') && str_contains($pdf, '/Author (Lumina Africa Foundation)')
    && str_contains($pdf, '/Producer (Lumina PdfWriter)') && preg_match("/\\/CreationDate \\(D:\\d{14}\\+00'00'\\)/", $pdf) === 1 && str_contains($pdf, '/ID [<'));
check('PDF: page size 720 x 414 and the five core fonts with WinAnsiEncoding (nothing embedded)', str_contains($pdf, '/MediaBox [0 0 720 414]') && substr_count($pdf, '/Encoding /WinAnsiEncoding') === 5
    && !str_contains($pdf, '/FontFile'));
check('PDF: the y axis is flipped (a top-left origin API): text at y=102 on a 414 pt page lands at 312', str_contains((function () use ($pdf): string {
    preg_match('/<< (?:\/Filter \/FlateDecode )?\/Length \d+ >>\nstream\n(.*?)\nendstream/s', $pdf, $m);
    return str_contains($m[0], 'FlateDecode') ? (string) gzuncompress($m[1]) : $m[1];
})(), '40 312 Td (MEMBER SINCE) Tj'));
check('PDF: output is deterministic for the same input', $pdf === (function (): string {
    $w2 = new PdfWriter(['title' => 'Membership card LAF-IND-26-0001', 'author' => 'Lumina Africa Foundation', 'date' => 1792000000]);
    $w2->addPage(720, 414);
    $w2->fill('#0F1B33');
    $w2->roundRect(28, 66, 149.333, 52, 5, 'f');
    $w2->text(40, 102, 'MEMBER SINCE', 'helvetica-bold', 8, '#6A7389');
    $w2->text(360, 80, 'Centred (ok)', 'times-italic', 12, '#C9973F', 'c');
    $w2->text(692, 80, 'Right', 'helvetica', 12, '#FFFFFF', 'r');
    $w2->save();
    $w2->clipRoundRect(28, 130, 116, 116, 6);
    $w2->stroke('#8F9AB5');
    $w2->lineWidth(0.8);
    $w2->dash([2, 3]);
    $w2->line(520, 0, 520, 414);
    $w2->restore();
    return $w2->output();
})());

// rounded rectangle = 4 Bezier corners; radius clamped to half of the short side; r=0 is a plain rectangle
$w = new PdfWriter(['compress' => false]);
$w->addPage(100, 100);
$w->roundRect(10, 10, 50, 20, 100, 'f');
$w->roundRect(10, 40, 50, 20, 0, 'f');
$w->roundRect(10, 70, 50, 20, 4, 's');
$plain = $w->output();
check('PDF: roundRect draws 4 curves per rounded rectangle (radius clamped to half the short side), a plain "re" for radius 0, S for a stroked one; no negative coordinates', preg_match_all('/\\sc\\s/', $plain) === 8
    && substr_count($plain, ' re f') === 1 && str_contains($plain, 'h S') && !preg_match('/-\\d/', substr($plain, (int) strpos($plain, 'stream'))) && mc_pdf_problems($plain) === [], (string) preg_match_all('/\\sc\\s/', $plain));
check('PDF: numbers are locale independent and short (no exponent, no trailing zeros)', PdfWriter::n(1.5) === '1.5' && PdfWriter::n(2.0) === '2' && PdfWriter::n(-0.0004) === '0' && PdfWriter::n(1234.56789) === '1234.568'
    && PdfWriter::n(0.1 + 0.2) === '0.3');
check('PDF: colours: #rgb, #rrggbb, junk falls back to black', str_contains((function (): string {
    $c = new PdfWriter(['compress' => false]);
    $c->addPage(10, 10);
    $c->fill('#f00');
    $c->fill('nonsense');
    $c->stroke('#00FF00');
    $o = $c->output();
    return $o;
})(), "1 0 0 rg\n0 0 0 rg\n0 1 0 RG"));

// widths: exact AFM values (units of 1/1000 em)
check('fonts: Helvetica / Helvetica-Bold / Times-Bold / Times-Italic widths come from the AFM tables', PdfWriter::textWidth('A', 'helvetica', 1000) === 667.0 && PdfWriter::textWidth('i', 'helvetica', 1000) === 222.0
    && PdfWriter::textWidth('A', 'helvetica-bold', 1000) === 722.0 && PdfWriter::textWidth('A', 'times-bold', 1000) === 722.0 && PdfWriter::textWidth('A', 'times-italic', 1000) === 611.0
    && PdfWriter::textWidth('AB', 'helvetica', 10) === (667 + 667) / 100, (string) PdfWriter::textWidth('A', 'helvetica', 1000));
check('fonts: accented letters have the width of their base letter (e-acute 556 in Helvetica), euro and en dash are WinAnsi codes', PdfWriter::textWidth('é', 'helvetica', 1000) === 556.0
    && PdfWriter::toWinAnsi('é€–') === "\xE9\x80\x96" && PdfWriter::textWidth('€', 'helvetica', 1000) === 556.0 && PdfWriter::textWidth('–', 'helvetica', 1000) === 556.0);
check('fonts: African letters are transliterated (eng -> n, open o -> o, epsilon -> e, modifier apostrophe -> \'), unknown characters become "?", Latin Extended letters lose their marks',
    PdfWriter::toWinAnsi('Ŋeŋ ɔ ɛ ʼ') === "Nen o e '" && PdfWriter::toWinAnsi('日本') === '??' && PdfWriter::toWinAnsi('Łukasz Ćwiek') === 'Lukasz Cwiek' && PdfWriter::toWinAnsi('Žižek') === "\x8Ei\x9Eek");
check('fonts: control, zero-width, bidi-override and BOM characters never reach the PDF; tabs and line breaks become spaces', PdfWriter::toWinAnsi("a\x00b\x07c\u{200B}d\u{202E}e\u{FEFF}f") === 'abcdef'
    && PdfWriter::toWinAnsi("a\tb\nc\r") === 'a b c ' && PdfWriter::toWinAnsi("bad \xFF\xFE utf8") !== '');

// escaping fuzz: hostile strings must come back out of the content stream unchanged (as WinAnsi), whatever they contain
$hostile = ['(', ')', '\\', '))) Tj ET q 0 0 0 rg 0 0 720 414 re f Q BT (', '\\) Tj', "back\\slash (and) (nested (parens))", "(unbalanced", 'a\\', "\x00\x01\x1f\x7f", '%PDF-1.4 endstream endobj', '/JavaScript << /S /JavaScript >>', "<</Type/Page>>", '☃ emoji 😀 ok', "line\nbreak", str_repeat('(', 300), str_repeat('\\', 301)];
mt_srand(42);
for ($i = 0; $i < 60; $i++) {
    $s = '';
    for ($j = 0, $n = mt_rand(1, 40); $j < $n; $j++) {
        $s .= chr(mt_rand(0, 255));
    }
    $hostile[] = $s;
}
$fuzzOk = true;
$fuzzWhy = '';
foreach ($hostile as $i => $h) {
    $w = new PdfWriter(['compress' => $i % 2 === 0]);
    $w->addPage(200, 50);
    $w->text(5, 20, $h, 'helvetica', 10, '#000000');
    $out = $w->output();
    $got = mc_pdf_strings($out);
    $want = PdfWriter::toWinAnsi($h);
    $ctrl = $got !== [] && preg_match('/[\x00-\x1F\x7F]/', $got[0]) === 1;
    if (mc_pdf_problems($out) !== [] || ($want !== '' && ($got === [] || $got[0] !== $want)) || $ctrl) {
        $fuzzOk = false;
        $fuzzWhy = 'case ' . $i . ' ' . bin2hex(substr($h, 0, 12)) . ' -> ' . json_encode(mc_pdf_problems($out)) . ' ' . bin2hex($got[0] ?? '') . ' vs ' . bin2hex($want);
        break;
    }
}
check('PDF text escaping: ' . count($hostile) . ' hostile / random strings (parentheses, backslashes, control bytes, PDF operators, invalid UTF-8) survive exactly and never break the file', $fuzzOk, $fuzzWhy);
check('PDF strings: escape() octal-escapes everything outside 32..126 and the three special characters', PdfWriter::escape("a(b)c\\d\x00\xE9") === 'a\\(b\\)c\\\\d\\000\\351');
check('PDF text strings (document info): ASCII is literal, anything else is UTF-16BE hex with a BOM, control characters are dropped', PdfWriter::textString('Plain (title)') === '(Plain \\(title\\))'
    && PdfWriter::textString("Café") === '<FEFF00430061006600E9>' && !str_contains(PdfWriter::textString("a\x00b\nc"), "\x00"));

// images: JPEG kept byte for byte, PNG with and without alpha, palette with tRNS, 1-bit grey; interlaced and 16-bit refused
$jpeg = (string) file_get_contents(ASSET_PATH . '/img/brand/card-logo.jpg');
$w = new PdfWriter();
$w->addPage(100, 100);
$ji = $w->addJpeg($jpeg);
$w->image($ji, 10, 10, 64, 64);
$out = $w->output();
check('images: a JPEG is embedded untouched (DCTDecode, 384 x 384, /DeviceRGB) and the file stays valid', str_contains($out, $jpeg) && str_contains($out, '/Filter /DCTDecode') && str_contains($out, '/Width 384 /Height 384')
    && mc_pdf_problems($out) === [], implode(';', mc_pdf_problems($out)));
$bad = 0;
foreach (['', 'GIF89a', "\xFF\xD8", "\xFF\xD8\xFF\xE0\x00\x10JFIF", 'not an image at all'] as $junk) {
    try {
        (new PdfWriter())->addJpeg($junk);
    } catch (\InvalidArgumentException) {
        $bad++;
    }
}
check('images: junk is refused with an exception, not embedded', $bad === 5);
$pngCases = ['RGB' => mc_png(40, 30, 2), 'grey' => mc_png(40, 30, 0), 'RGBA' => mc_png(40, 30, 6), 'grey+alpha' => mc_png(40, 30, 4), 'palette' => mc_png(40, 30, 3), 'palette+tRNS' => mc_png(40, 30, 3, 8, false, true),
    '1-bit grey' => mc_png(40, 30, 0, 1)];
$pngOk = true;
$pngWhy = '';
foreach ($pngCases as $name => $bytes) {
    $w = new PdfWriter();
    $w->addPage(100, 100);
    try {
        $w->image($w->addPng($bytes), 10, 10, 40, 30);
        $out = $w->output();
        $alpha = in_array($name, ['RGBA', 'grey+alpha', 'palette+tRNS'], true);
        if (mc_pdf_problems($out) !== [] || str_contains($out, '/SMask') !== $alpha) {
            $pngOk = false;
            $pngWhy = $name . ': ' . implode(';', mc_pdf_problems($out)) . (str_contains($out, '/SMask') ? ' has' : ' no') . ' SMask';
        }
    } catch (\Throwable $e) {
        $pngOk = false;
        $pngWhy = $name . ': ' . $e->getMessage();
    }
}
check('images: PNG grey / RGB / RGBA / grey+alpha / palette / palette+tRNS / 1-bit all embed; alpha becomes a soft mask; files stay valid', $pngOk, $pngWhy);
$refused = 0;
foreach ([mc_png(20, 20, 2, 8, true), mc_png(20, 20, 2, 16)] as $bad) {
    try {
        (new PdfWriter())->addPng($bad);
    } catch (\InvalidArgumentException) {
        $refused++;
    }
}
check('images: an interlaced and a 16-bit PNG are refused (CardRenderer converts them with GD when it can)', $refused === 2 && PdfWriter::imageInfo(mc_png(20, 20, 2, 8, true)) === null && PdfWriter::imageInfo($jpeg)['w'] === 384);
$info = PdfWriter::imageInfo(mc_png(33, 21, 6));
check('images: imageInfo() reads type and size without registering anything', $info === ['mime' => 'image/png', 'w' => 33, 'h' => 21] && PdfWriter::imageInfo('GIF89a....') === null);

// ---------------------------------------------------------------------------------------------------- QR encoder
echo "Membership card: QR encoder\n";
$iso = [0x10, 0x20, 0x0C, 0x56, 0x61, 0x80, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11];
check('QR: Reed-Solomon of the ISO 18004 worked example (01234567, version 1-M): A5 24 D4 C1 ED 36 C7 87 2C 55', QrEncoder::eccCodewords($iso, 10) === [0xA5, 0x24, 0xD4, 0xC1, 0xED, 0x36, 0xC7, 0x87, 0x2C, 0x55]);
check('QR: byte-mode data codewords: mode 0100, 8-bit count, bytes, terminator, pad bytes EC 11 (version 1-M, "AB")', QrEncoder::dataCodewords('AB', 1, 'M') === array_merge([0x40, 0x24, 0x14, 0x20], [0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11]));
check('QR: format information tables (L,0)=111011111000100 (M,0)=101010000010010 (M,5)=100000011001110 (Q,0)=011010101011111 (H,0)=001011010001001',
    sprintf('%015b', QrEncoder::formatBits('L', 0)) === '111011111000100' && sprintf('%015b', QrEncoder::formatBits('M', 0)) === '101010000010010'
    && sprintf('%015b', QrEncoder::formatBits('M', 5)) === '100000011001110' && sprintf('%015b', QrEncoder::formatBits('Q', 0)) === '011010101011111' && sprintf('%015b', QrEncoder::formatBits('H', 0)) === '001011010001001');
check('QR: version information (BCH 18,6): v7 = 000111110010010100, v8 = 001000010110111100, v9 = 001001101010011001, v10 = 001010010011010011',
    sprintf('%018b', QrEncoder::versionBits(7)) === '000111110010010100' && sprintf('%018b', QrEncoder::versionBits(8)) === '001000010110111100'
    && sprintf('%018b', QrEncoder::versionBits(9)) === '001001101010011001' && sprintf('%018b', QrEncoder::versionBits(10)) === '001010010011010011');
$caps = ['L' => [17, 32, 53, 78, 106, 134, 154, 192, 230, 271], 'M' => [14, 26, 42, 62, 84, 106, 122, 152, 180, 213], 'Q' => [11, 20, 32, 46, 60, 74, 86, 108, 130, 151], 'H' => [7, 14, 24, 34, 44, 58, 64, 84, 98, 119]];
$capOk = true;
foreach ($caps as $lvl => $list) {
    foreach ($list as $i => $cap) {
        $capOk = $capOk && QrEncoder::byteCapacity($i + 1, $lvl) === $cap;
    }
}
check('QR: byte capacity of versions 1-10 at L / M / Q / H matches the standard (v1: 17/14/11/7 ... v10: 271/213/151/119)', $capOk);
$mcLcg = static function (int $n, int $seed): string {
    $x = $seed;
    $s = '';
    for ($i = 0; $i < $n; $i++) {
        $x = ($x * 1103515245 + 12345) & 0x7fffffff;
        $s .= chr(32 + (($x >> 16) % 95));
    }
    return $s;
};
$vectors = require __DIR__ . '/selftest-data/qr-vectors.php';
$vecBad = [];
foreach ($vectors as [$len, $seed, $lvl, $mask, $version, $sha]) {
    $q = QrEncoder::encode($mcLcg($len, $seed), $lvl, 1, $mask);
    if ($q['version'] !== $version || sha1(QrEncoder::toText($q['modules'])) !== $sha) {
        $vecBad[] = "{$lvl}{$version}m{$mask}/{$len}";
    }
}
check('QR: ' . count($vectors) . ' matrices (versions 1-10, levels L M Q H, smallest and largest payload of each, all eight masks) equal those of an independent implementation', $vecBad === [], implode(' ', array_slice($vecBad, 0, 8)));
$q = QrEncoder::encode('https://luminaafricafoundation.com/membership/verify/3f9a1c0d5e7b42a8916d0c4e7a2b5f18', 'M');
$n = $q['size'];
$m = $q['modules'];
$finder = static function (array $m, int $r0, int $c0): bool {
    for ($r = 0; $r < 7; $r++) {
        for ($c = 0; $c < 7; $c++) {
            $want = $r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);
            if ($m[$r0 + $r][$c0 + $c] !== $want) {
                return false;
            }
        }
    }
    return true;
};
$timing = true;
for ($i = 8; $i < $n - 8; $i++) {
    $timing = $timing && $m[6][$i] === ($i % 2 === 0) && $m[$i][6] === ($i % 2 === 0);
}
check('QR: a verification URL gets version 6 at level M (41 x 41); three finder patterns, timing patterns, the always-dark module', $q['version'] === 6 && $n === 41 && $finder($m, 0, 0) && $finder($m, 0, $n - 7) && $finder($m, $n - 7, 0) && $timing && $m[$n - 8][8] === true);
$pen = [];
for ($mask = 0; $mask < 8; $mask++) {
    $pen[$mask] = QrEncoder::encode('https://example.org/membership/verify/abcdef0123456789abcdef0123456789', 'M', 1, $mask)['penalty'];
}
$chosen = QrEncoder::encode('https://example.org/membership/verify/abcdef0123456789abcdef0123456789', 'M');
check('QR: the mask with the lowest penalty score is chosen (ties: the lower number)', $chosen['penalty'] === min($pen) && $chosen['mask'] === array_search(min($pen), $pen, true), json_encode($pen));
$reconstructed = array_fill(0, $n, array_fill(0, $n, false));
foreach (QrEncoder::runs($m) as [$r, $c, $len]) {
    for ($k = 0; $k < $len; $k++) {
        $reconstructed[$r][$c + $k] = true;
    }
}
check('QR: the horizontal runs used for vector drawing cover exactly the dark modules', $reconstructed === $m);
$thrown = 0;
foreach ([[str_repeat('x', 214), 'M'], ['x', 'Z'], [str_repeat('x', 272), 'L']] as [$text, $lvl]) {
    try {
        QrEncoder::encode($text, $lvl);
    } catch (\InvalidArgumentException) {
        $thrown++;
    }
}
check('QR: too long (214 bytes at M, 272 at L) and an unknown level are refused; 213 bytes at M still fit version 10', $thrown === 3 && QrEncoder::encode(str_repeat('x', 213), 'M')['version'] === 10
    && QrEncoder::encode(str_repeat('x', 14), 'M')['version'] === 1 && QrEncoder::encode(str_repeat('x', 15), 'M')['version'] === 2 && QrEncoder::encode('x', 'M', 5)['version'] === 5);
check('QR: encoding is deterministic', QrEncoder::encode('same', 'Q') === QrEncoder::encode('same', 'Q'));

// ---------------------------------------------------------------------------------------------------- CardSettings
echo "Membership card: settings (defaults, validation, number format, contrast)\n";
$defs = CardSettings::DEFS;
$seed = (string) file_get_contents(dirname(__DIR__) . '/database/seed.sql');
$seedMissing = [];
$seedDiff = [];
foreach ($defs as $key => [$default]) {
    if (!str_contains($seed, "('" . $key . "'")) {
        $seedMissing[] = $key;
        continue;
    }
    $row = Database::one('SELECT setting_value, created_at, updated_at FROM settings WHERE setting_key = ?', [$key]);
    if ($row !== null && $row['created_at'] === $row['updated_at'] && (string) $row['setting_value'] !== (string) $default) {
        $seedDiff[] = $key;
    }
}
check('settings: database/seed.sql seeds every key of CardSettings::DEFS (' . count($defs) . ') and the seeded values equal the defaults', $seedMissing === [] && $seedDiff === [], implode(',', array_merge($seedMissing, $seedDiff)));
check('settings: the defaults reproduce the reference design (navy #0F1B33, orange #E0642C, gold #C9973F, green #22A35A, tile #F4F6FA, MEMBERSHIP CARD, ACTIVE MEMBER, 3 tiles)',
    $defs['membership_card_navy'][0] === '#0F1B33' && $defs['membership_card_accent'][0] === '#E0642C' && $defs['membership_card_gold'][0] === '#C9973F' && $defs['membership_card_green'][0] === '#22A35A'
    && $defs['membership_card_tile'][0] === '#F4F6FA' && $defs['membership_card_label'][0] === 'MEMBERSHIP CARD' && $defs['membership_card_status_active'][0] === 'ACTIVE MEMBER'
    && [$defs['membership_card_tile_1'][0], $defs['membership_card_tile_2'][0], $defs['membership_card_tile_3'][0]] === ['member_since', 'payment', 'fee']
    && $defs['membership_card_number_format'][0] === '{PREFIX}-{TYPE}-{YY}-{SEQ}' && $defs['membership_card_trigger'][0] === 'paid_approved' && $defs['membership_photo_required'][0] === '1');
check('settings: they are NOT in the generic Site settings schema (the designer is the only editor)', array_diff_key($defs, Settings::definitions()) === $defs);
// the owner's REFERENCE colours do not reach 4.5:1 on four small texts (gold tagline 2.6, accent type line 3.5, white on the green pill 3.3, tile labels 4.4): they stay the default
// (it is the owner's design) and the designer REPORTS them instead of calling them good
$refWarn = array_map(static fn(array $w): string => $w['key'], CardSettings::warnings(CardSettings::resolve()));
sort($refWarn);
check('settings: the defaults pass their own validation; the contrast report names exactly the four reference colours below 4.5:1 (green pill, accent type line, gold tagline, tile labels)',
    CardSettings::sanitise(CardSettings::defaults(), true)['errors'] === [] && $refWarn === ['membership_card_accent', 'membership_card_gold', 'membership_card_green', 'membership_card_label_color'], json_encode($refWarn));
$hostile = CardSettings::sanitise([
    'membership_card_navy' => 'red', 'membership_card_accent' => "#12345\n", 'membership_card_gold' => ['#ffffff'], 'membership_card_green' => 'url(javascript:alert(1))', 'membership_card_tile' => '#abc',
    'membership_card_tile_1' => 'photo', 'membership_card_tile_2' => '../../etc/passwd', 'membership_card_trigger' => 'always',
    'membership_card_org_name' => "<script>alert(1)</script>\r\n" . str_repeat('x', 400) . "\u{202E}\u{200B}\x00", 'membership_card_footer_1' => '{evil} ((( \\ ' . str_repeat('A', 500),
    'membership_card_logo' => '../../app/config/app.php', 'membership_card_support_email' => 'a@b.c, d@e.f', 'membership_card_number_format' => '{PREFIX}/{SEQ}', 'membership_proof_retention_days' => '-5',
    'membership_card_label' => '', 'unknown_key' => 'x',
], true);
$hv = $hostile['values'];
check('settings: hostile colours are rejected and replaced by the default ("red", newline, array, url(...)); "#abc" is expanded to #AABBCC', isset($hostile['errors']['membership_card_navy'], $hostile['errors']['membership_card_accent'],
    $hostile['errors']['membership_card_gold'], $hostile['errors']['membership_card_green']) && $hv['membership_card_navy'] === '#0F1B33' && $hv['membership_card_tile'] === '#AABBCC');
check('settings: unknown enum values, a path traversal in the logo, several e-mail addresses, a bad number format and a negative day count are refused', isset($hostile['errors']['membership_card_tile_1'], $hostile['errors']['membership_card_tile_2'],
    $hostile['errors']['membership_card_trigger'], $hostile['errors']['membership_card_logo'], $hostile['errors']['membership_card_support_email'], $hostile['errors']['membership_card_number_format'],
    $hostile['errors']['membership_proof_retention_days']));
check('settings: text is cut to its length, loses control / zero-width / bidi characters; markup is kept as TEXT (the card and e-mail escape it); unknown keys are ignored',
    mb_strlen($hv['membership_card_org_name']) <= 60 && !preg_match('/[\x00-\x1F\x{202E}\x{200B}]/u', $hv['membership_card_org_name']) && mb_strlen($hv['membership_card_footer_1']) <= 120 && !isset($hv['unknown_key'])
    && isset($hostile['errors']['membership_card_label']) === false);
$unchecked = CardSettings::sanitise(['membership_card_show_qr' => '1'], true)['values'];
check('settings: an unticked box (missing from the post) means off when the form is saved, but is left alone when only some keys are given', $unchecked['membership_card_show_photo'] === '0' && $unchecked['membership_card_show_qr'] === '1'
    && !isset(CardSettings::sanitise(['membership_card_org_name' => 'X'], false)['values']['membership_card_show_qr']));
$okFmt = ['{PREFIX}-{TYPE}-{YY}-{SEQ}', '{PREFIX}-{YYYY}-{SEQ}', '{PREFIX}{SEQ}', 'M-{TYPE}-{SEQ:6}', '{PREFIX}-{SEQ:3}', 'LAF-ZZ-{YY}-{SEQ}'];
$badFmt = ['', '{PREFIX}-{TYPE}', '{PREFIX}-{SEQ}-{TYPE}', '{SEQ}{SEQ}', '{PREFIX}-{SEQ:2}', '{PREFIX}-{SEQ:9}', '{PREFIX}/{YY}/{SEQ}', '{PREFIX}-{FOO}-{SEQ}', '{PREFIX}_{SEQ}', '-{SEQ}', '{PREFIX}--{SEQ}', 'laf-{SEQ}',
    '{PREFIX}-{TYPE}-{YYYY}-{YYYY}-{YYYY}-{YYYY}-{SEQ:8}', "{PREFIX}\n{SEQ}", '{PREFIX} {SEQ}', '<b>{SEQ}'];
check('number format: valid formats accepted (' . count($okFmt) . ')', array_filter($okFmt, static fn(string $f): bool => CardSettings::numberFormatError($f) !== null) === []);
check('number format: invalid ones refused (' . count($badFmt) . ': no {SEQ}, {SEQ} not last, twice, bad width, slash / space / underscore, unknown placeholder, lower case, too long when expanded)',
    array_filter($badFmt, static fn(string $f): bool => CardSettings::numberFormatError($f) === null) === [], json_encode(array_filter($badFmt, static fn(string $f): bool => CardSettings::numberFormatError($f) === null)));
check('number format: expandNumber / numberSeries produce LAF-IND-26-0001 and the series head LAF-IND-26-',
    CardSettings::expandNumber('{PREFIX}-{TYPE}-{YY}-{SEQ}', 'LAF', 'IND', 2026, 1) === 'LAF-IND-26-0001' && CardSettings::expandNumber('{PREFIX}-{YYYY}-{SEQ:6}', 'LAF', 'IND', 2026, 42) === 'LAF-2026-000042'
    && CardSettings::numberSeries('{PREFIX}-{TYPE}-{YY}-{SEQ}', 'LAF', 'IND', 2026) === ['LAF-IND-26-', 4] && CardSettings::numberSeries('{PREFIX}{SEQ:5}', 'LAF', 'IND', 2026) === ['LAF', 5]);
check('contrast: black on white 21:1, white on white 1:1, WCAG ratios', abs(CardSettings::contrast('#000000', '#FFFFFF') - 21.0) < 0.001 && abs(CardSettings::contrast('#FFFFFF', '#FFFFFF') - 1.0) < 0.001
    && abs(CardSettings::contrast('#767676', '#FFFFFF') - 4.54) < 0.02);
$lowKeys = static fn(array $over): array => array_column(CardSettings::warnings(CardSettings::resolve($over)), 'key');
$baseLow = $lowKeys([]);
check('contrast: a pale side panel and a dark tile colour each raise a NEW warning, a pale tagline changes nothing new (it was already reported)', in_array('membership_card_navy', array_diff($lowKeys(['membership_card_navy' => '#CCCCCC']), $baseLow), true)
    && in_array('membership_card_tile', array_diff($lowKeys(['membership_card_tile' => '#222222']), $baseLow), true) && array_diff($lowKeys(['membership_card_gold' => '#FFFFAA']), $baseLow) === []
    && in_array('membership_card_gold', $lowKeys(['membership_card_gold' => '#FFFFAA']), true), json_encode([$lowKeys(['membership_card_tile' => '#222222']), $lowKeys(['membership_card_gold' => '#FFFFAA'])]));
check('settings: resolve() applies unsaved values on top, ignores invalid ones and never writes anything', CardSettings::resolve(['membership_card_org_name' => 'Preview Org'])['membership_card_org_name'] === 'Preview Org'
    && CardSettings::resolve(['membership_card_navy' => 'garbage'])['membership_card_navy'] === CardSettings::get('membership_card_navy'));

// ---------------------------------------------------------------------------------------------------- CardData and CardLayout
echo "Membership card: data and layout\n";
$s = CardSettings::defaults();
$d = CardData::sample($s);
check('data: the sample is Amina Akello, LAF-IND-26-0001, INDIVIDUAL MEMBER, 20 Oct 2026 - 19 Oct 2027, UGX 50,000 / year, Gulu City, Uganda, AA',
    $d['name'] === 'Amina Akello' && $d['number'] === 'LAF-IND-26-0001' && $d['type'] === 'INDIVIDUAL MEMBER' && $d['valid_text'] === '19 Oct 2027' && $d['tiles'][0]['value'] === '20 Oct 2026' && $d['tiles'][1]['value'] === 'Confirmed'
    && $d['tiles'][2]['value'] === 'UGX 50,000 / year' && $d['tiles'][2]['label'] === 'ANNUAL MEMBERSHIP' && $d['location'] === 'Gulu City, Uganda' && $d['initials'] === 'AA' && $d['renews_text'] === 'Renews annually'
    && $d['status_label'] === 'ACTIVE MEMBER' && $d['footer'][0] === "Joining fee UGX 50,000 paid \u{00B7} Issued 20 Oct 2026" && str_starts_with($d['footer'][1], 'Support: ') && $d['sample'] === true, json_encode([$d['footer'], $d['tiles']]));
check('data: sample footer shows the support phone in local form (0779 712 990) and the info e-mail from Site settings', str_contains($d['footer'][1], '0779 712 990') && str_contains($d['footer'][1], 'info@luminaafricafoundation.com'), $d['footer'][1]);
check('data: "Valid until" is the LAST valid day, the day before the expiry date (start 20 Oct 2026 + 1 year = 19 Oct 2027, like the reference); year ends, leap days, no expiry', \App\Services\Membership\MembershipService::lastValidDay('2027-10-20') === '2027-10-19'
    && \App\Services\Membership\MembershipService::lastValidDay('2028-03-01') === '2028-02-29' && \App\Services\Membership\MembershipService::lastValidDay('2027-01-01') === '2026-12-31'
    && \App\Services\Membership\MembershipService::lastValidDay(null) === null && \App\Services\Membership\MembershipService::lastValidDay('') === null && \App\Services\Membership\MembershipService::lastValidDay('junk') === null
    && CardData::raw(['id' => 0, 'full_name' => 'X', 'expiry_date' => '2027-10-20', 'fee_period' => 'annual', 'fee_currency' => 'UGX'], [])['valid_until'] === '2027-10-19');
check('data: initials (first + last word, apostrophes and hyphens, one word, no letters)', CardData::initials('Amina Akello') === 'AA' && CardData::initials("Okello-Lalam N'dyanabo Ocitti") === 'OO' && CardData::initials('Madonna') === 'MA'
    && CardData::initials('   ') === '?' && CardData::initials('Élodie Ŋeŋ') === 'ÉŊ' && CardData::initials('123 456') === '?');
check('data: location format drops empty pieces and their commas; fee wording per period; dates; Uganda phone numbers', CardData::location('{city}, {district}, {country}', '', 'Gulu', 'Uganda') === 'Gulu, Uganda'
    && CardData::location('{city}, {country}', 'Gulu City', '', '') === 'Gulu City' && CardData::feeValue('50000.00', 'UGX', 'monthly') === 'UGX 50,000 / month' && CardData::feeValue('0.00', 'UGX', 'annual') === 'Free'
    && CardData::feeValue(null, 'UGX', 'annual') === 'To be announced' && CardData::feeValue('1500.5', 'USD', 'lifetime') === 'USD 1,500.50 (for life)' && CardData::date('2026-02-30') === '' && CardData::date('2026-10-05') === '5 Oct 2026'
    && CardData::localPhone('+256-779-712-990') === '0779 712 990' && CardData::localPhone('+44 20 7946 0958') === '+44 20 7946 0958');
check('data: footer templates: a missing joining fee leaves no dangling dot; unknown placeholders vanish', CardData::template('{joining} · Issued {issued}', ['{joining}' => '', '{issued}' => '1 Jan 2026']) === 'Issued 1 Jan 2026'
    && CardData::template('A · {x} · B', []) === "A \u{00B7} B" && CardData::template('{a}{b}', ['{a}' => '', '{b}' => '']) === '');
$noExpiry = CardData::build(array_merge(CardData::sampleRaw(), ['valid_until' => null, 'period' => 'lifetime']), $s);
$exp = CardData::build(array_merge(CardData::sampleRaw(), ['status' => 'expired']), $s);
$sus = CardData::build(array_merge(CardData::sampleRaw(), ['status' => 'suspended']), $s);
$rev = CardData::build(array_merge(CardData::sampleRaw(), ['status' => 'revoked']), $s);
check('data: a lifetime membership prints "No expiry" and no renewal line; statuses: EXPIRED (grey), SUSPENDED and NOT VALID (red)', $noExpiry['valid_text'] === 'No expiry' && $noExpiry['renews_text'] === '' && $noExpiry['tiles'][2]['label'] === 'LIFETIME MEMBERSHIP'
    && $exp['status_label'] === 'EXPIRED' && $exp['status_color'] === '#6B7280' && $sus['status_label'] === 'SUSPENDED' && $sus['status_color'] === '#C0392B' && $rev['status_label'] === 'NOT VALID' && $rev['status_color'] === '#C0392B');
check('data: state() = revoked for pending / rejected / cancelled / erased / revoked card, suspended, expired (status or past expiry), else active', CardData::state(['status' => 'approved', 'expiry_date' => date('Y-m-d', strtotime('+10 days'))]) === 'active'
    && CardData::state(['status' => 'approved', 'expiry_date' => date('Y-m-d')]) === 'expired' && CardData::state(['status' => 'approved', 'expiry_date' => null]) === 'active' && CardData::state(['status' => 'expired']) === 'expired'
    && CardData::state(['status' => 'suspended']) === 'suspended' && CardData::state(['status' => 'pending']) === 'revoked' && CardData::state(['status' => 'approved'], 'revoked') === 'revoked'
    && CardData::state(['status' => 'approved', 'anonymised_at' => '2026-01-01 00:00:00']) === 'revoked');

$mcOps = static fn(array $layout, string $t): array => array_values(array_filter($layout['ops'], static fn(array $o): bool => $o['t'] === $t));
$mcBounds = static function (array $layout): array {
    $bad = [];
    foreach ($layout['ops'] as $o) {
        if ($o['t'] !== 'text') {
            continue;
        }
        $wd = PdfWriter::textWidth($o['text'], $o['font'], $o['size']);
        $left = $o['align'] === 'c' ? $o['x'] - $wd / 2 : ($o['align'] === 'r' ? $o['x'] - $wd : $o['x']);
        $right = $left + $wd;
        $onPanel = $o['x'] >= 600 && $o['align'] === 'c';
        $min = $onPanel ? 524.0 : 28.0;
        $max = $onPanel ? 716.0 : ($o['align'] === 'c' && $o['x'] < 200 ? 144.0 : 492.0);
        if ($left < $min - 0.01 || $right > $max + 0.01) {
            $bad[] = $o['text'] . ' [' . round($left, 1) . '..' . round($right, 1) . ']';
        }
    }
    return $bad;
};
check('layout: the sample\'s text all stays inside its panel (left text 28..492, side-panel text 524..716)', $mcBounds(CardLayout::build($d)) === [], implode('; ', $mcBounds(CardLayout::build($d))));
$names = [
    'Amina Akello', 'Ssekitoleko-Nakimuli Nalubega Namukasa Bukenya Lutaaya', "Ocitti-Lalam Okello O'Brien-Müller Łukasz Žižek", 'Wàńgá Ŋeŋ Ɔkɛ Atim', "Nakiyingi Namukasa Bridget Akello Ocitti Lalam Okello Atim Odongo Opio",
    str_repeat('A', 60), str_repeat('W', 60), 'Li', 'X', str_repeat('Ssekandi ', 8), 'Pneumonoultramicroscopicsilicovolcanoconiosis-Pneumonoultramicroscopicsilicovolcanoconiosis', "O'Brien (the Third) \"Jr\" \\ / <b>",
];
$layoutBad = [];
foreach ($names as $name) {
    $raw = array_merge(CardData::sampleRaw(), ['name' => $name, 'type_name' => 'Organisation / Partner Member of the Northern Uganda Youth Network', 'city' => 'Kampala Central Division Nakawa Municipality', 'country' => 'United Republic of Tanzania',
        'number' => 'LAF-ORG-26-0001234']);
    $dd = CardData::build($raw, $s);
    $lay = CardLayout::build($dd);
    $bad = $mcBounds($lay);
    $nameOps = array_values(array_filter($lay['ops'], static fn(array $o): bool => $o['t'] === 'text' && $o['font'] === 'times-bold'));
    $lines = count($nameOps);
    if ($bad !== [] || $lines < 1 || $lines > 2) {
        $layoutBad[] = mb_substr($name, 0, 20) . ': ' . implode(',', $bad) . " lines=$lines";
    }
    if (mc_pdf_problems(CardPdf::render($lay, [])) !== []) {
        $layoutBad[] = 'pdf ' . mb_substr($name, 0, 20);
    }
}
check('layout: ' . count($names) . ' names (60 characters, accents, African letters, apostrophes, one letter, markup, an unbreakable word) never overflow: one or two lines, shrunk or shortened, valid PDF', $layoutBad === [], implode(' | ', $layoutBad));
$short = CardLayout::fit(str_repeat('M', 100), 'helvetica-bold', 12.5, 7.5, 100.0);
check('layout: fit() shrinks first, then cuts with an ellipsis; the result is never wider than the box; empty text stays empty', $short['shortened'] === true && $short['width'] <= 100.0 && str_ends_with($short['text'], "\u{2026}")
    && CardLayout::fit('Hi', 'helvetica', 10, 6, 100.0)['size'] === 9.1 && CardLayout::fit('', 'helvetica', 10, 6, 100.0)['text'] === '');
$tilesOff = CardData::build(CardData::sampleRaw(), CardSettings::resolve(['membership_card_tile_2' => 'none', 'membership_card_tile_3' => 'district', 'membership_card_show_photo' => '0', 'membership_card_show_qr' => '0', 'membership_card_footer_3' => 'Extra line']));
$l2 = CardLayout::build($tilesOff, ['logo' => false]);
$tileRects = array_values(array_filter($l2['ops'], static fn(array $o): bool => $o['t'] === 'rect' && $o['h'] === 52.0));
$texts = array_column($mcOps($l2, 'text'), 'text');
check('layout: blocks can be switched off: two tiles share the width, no photo block, no QR code, no logo (text moves left), an extra footer line appears, everything still inside', count($tileRects) === 2
    && abs($tileRects[0]['w'] - 228.0) < 0.01 && $mcOps($l2, 'qr') === [] && $mcOps($l2, 'image') === [] && !in_array('AA', $texts, true) && in_array('Extra line', $texts, true) && in_array('Gulu', $texts, true) && $mcBounds($l2) === []
    && (array_values(array_filter($mcOps($l2, 'text'), static fn(array $o): bool => $o['text'] === 'LUMINA AFRICA FOUNDATION'))[0]['x'] ?? 0) === 28.0, json_encode($texts, JSON_INVALID_UTF8_SUBSTITUTE));
$withPhoto = CardLayout::build(array_merge($d, ['photo' => ['bytes' => 'x', 'mime' => 'image/jpeg']]));
$imgOps = $mcOps($withPhoto, 'image');
check('layout: with a photo the image replaces the initials; a tall picture is cropped to a square keeping the top (cover)', count($imgOps) === 2 && $imgOps[1]['key'] === 'photo' && $imgOps[1]['cover'] === true
    && !in_array('AA', array_column($mcOps($withPhoto, 'text'), 'text'), true) && CardLayout::cover(600, 800, 28, 130, 116, 116) === [28.0, 130 - (154.666666666667 - 116) * 0.2, 116.0, 154.66666666666666]
    || (function () use ($withPhoto, $mcOps): bool {
        [$x, $y, $w, $h] = CardLayout::cover(600, 800, 28, 130, 116, 116);
        return abs($w - 116) < 0.001 && abs($h - 154.667) < 0.01 && abs($y - (130 - 38.667 * 0.2)) < 0.01 && abs($x - 28) < 0.001 && count($mcOps($withPhoto, 'image')) === 2;
    })());
$qrOp = $mcOps(CardLayout::build($d), 'qr')[0];
$geo = CardLayout::qrGeometry($qrOp);
check('layout: the QR code is drawn at 100 pt inside the white box with a 2-module quiet zone; the module size is exact', $qrOp['size'] === 100.0 && abs($geo['module'] * ($geo['n'] + 4) - 100.0) < 1e-9 && $qrOp['x'] >= 558 && $qrOp['x'] + 100 <= 682
    && $geo['module'] >= 2.0, (string) $geo['module']);

// ---------------------------------------------------------------------------------------------------- the real renderers
echo "Membership card: PDF and PNG output\n";
$pdfSample = CardRenderer::samplePdf();
check('card PDF: the sample is a valid one-page 720 x 414 vector PDF with real text, the logo and the sample watermark, under 120 KB', mc_pdf_problems($pdfSample) === [] && str_contains($pdfSample, '/MediaBox [0 0 720 414]')
    && count(array_filter(mc_pdf_strings($pdfSample), static fn(string $x): bool => str_contains($x, 'SAMPLE CARD'))) === 1 && str_contains($pdfSample, '/Filter /DCTDecode') && strlen($pdfSample) < 120000, (string) strlen($pdfSample));
$strings = mc_pdf_strings($pdfSample);
check('card PDF: the text on it is the text of the reference (member number, name, status, tiles, valid until, footer)', in_array('LAF-IND-26-0001', $strings, true) && in_array('Amina Akello', $strings, true) && in_array('ACTIVE MEMBER', $strings, true)
    && in_array('UGX 50,000 / year', $strings, true) && in_array('19 Oct 2027', $strings, true) && in_array('Scan to verify membership', $strings, true) && in_array('AA', $strings, true), (string) json_encode($strings, JSON_INVALID_UTF8_SUBSTITUTE));
$qrScan = array_filter($mcOps(CardLayout::build(CardData::sample($s)), 'qr'), static fn(array $o): bool => true)[0];
check('card PDF: the QR code encodes the verification URL of the sample (a token that cannot exist in the database)', str_contains(abs_url('membership/verify/' . CardData::SAMPLE_TOKEN), 'membership/verify/samplecardnotvalid')
    && count(QrEncoder::runs($qrScan['modules'])) > 100 && QrEncoder::encode(abs_url('membership/verify/' . CardData::SAMPLE_TOKEN), 'M')['modules'] === $qrScan['modules']);
$over = CardRenderer::samplePdf(['membership_card_org_name' => 'Preview (Org) \\ Name', 'membership_card_navy' => '#112244']);
check('card PDF: unsaved designer values are applied to the preview (organisation name, colour) and hostile ones cannot break the file', mc_pdf_problems($over) === [] && in_array('Preview (Org) \\ Name', mc_pdf_strings($over), true)
    && str_contains((string) gzuncompress((string) (preg_match('/<< \/Filter \/FlateDecode \/Length \d+ >>\nstream\n(.*?)\nendstream/s', $over, $mm) ? $mm[1] : '')), '0.067 0.133 0.267 rg'));
$plainPdf = CardPdf::render(CardLayout::build($d), ['logo' => null, 'photo' => null], ['compress' => false]);
check('card PDF: without compression (no zlib) the file is still valid; a missing logo simply leaves the logo out', mc_pdf_problems($plainPdf) === [] && !str_contains($plainPdf, '/DCTDecode'));
if ($mcGd) {
    $png = CardRenderer::samplePng();
    $im = $png !== null ? @imagecreatefromstring($png) : false;
    check('card PNG (GD): the preview is a 1440 x 828 PNG of the same layout', $png !== null && str_starts_with($png, "\x89PNG") && $im !== false && imagesx($im) === 1440 && imagesy($im) === 828);
    $navyPx = $im ? imagecolorat($im, 1300, 400) : 0;
    $accentPx = $im ? imagecolorat($im, 150, 450) : 0;
    $tilePx = $im ? imagecolorat($im, 100, 640) : 0;
    check('card PNG (GD): the colours land where the reference has them (navy panel, accent photo block, light tile)', sprintf('%06X', $navyPx & 0xFFFFFF) === '0F1B33' && sprintf('%06X', $accentPx & 0xFFFFFF) === 'E0642C' && sprintf('%06X', $tilePx & 0xFFFFFF) === 'F4F6FA',
        sprintf('%06X %06X %06X', $navyPx & 0xFFFFFF, $accentPx & 0xFFFFFF, $tilePx & 0xFFFFFF));
    if (CardPng::hasTrueType()) {
        $bbox = imagettfbbox(100 * 0.75, 0, ASSET_PATH . '/fonts/card/LiberationSans-Regular.ttf', 'LAF-IND-26-0001 Support: 0779 712 990');
        $ttfWidth = $bbox[2] - $bbox[0];
        $afm = PdfWriter::textWidth('LAF-IND-26-0001 Support: 0779 712 990', 'helvetica', 100);
        check('card PNG (GD): the bundled Liberation fonts measure like the AFM tables (within 1.5%), so the PNG lines are as wide as in the PDF and GD sizes are points at 96 dpi (px x 0.75)', abs($ttfWidth - $afm) / $afm < 0.015, "$ttfWidth vs $afm");
    }
    $tallJpeg = (function (): string {
        $i = imagecreatetruecolor(600, 800);
        imagefill($i, 0, 0, (int) imagecolorallocate($i, 40, 90, 200));
        imagefilledellipse($i, 300, 250, 180, 220, (int) imagecolorallocate($i, 230, 190, 160));
        ob_start();
        imagejpeg($i, null, 85);
        return (string) ob_get_clean();
    })();
    $dPhoto = CardData::build(array_merge(CardData::sampleRaw(), ['photo' => ['bytes' => $tallJpeg, 'mime' => 'image/jpeg']]), $s);
    $pdfPhoto = CardRenderer::pdf($dPhoto, $s);
    check('card PDF (GD): a photo is embedded (2 images), cropped by a clip path to the 116 pt square, valid file', mc_pdf_problems($pdfPhoto) === [] && substr_count($pdfPhoto, '/Subtype /Image') === 2 && str_contains((string) gzuncompress((string) (preg_match('/<< \/Filter \/FlateDecode \/Length \d+ >>\nstream\n(.*?)\nendstream/s', $pdfPhoto, $mm2) ? $mm2[1] : '')), 'W n'));
    $webp = function_exists('imagewebp') ? (function (): string {
        $i = imagecreatetruecolor(400, 400);
        imagefill($i, 0, 0, (int) imagecolorallocate($i, 10, 120, 60));
        ob_start();
        imagewebp($i, null, 80);
        return (string) ob_get_clean();
    })() : '';
    if ($webp !== '') {
        $conv = CardRenderer::usable($webp);
        check('card renderer (GD): a WebP picture is converted to a JPEG the PDF can embed; a PNG with alpha is kept as PNG with a soft mask', $conv !== null && $conv['mime'] === 'image/jpeg' && CardRenderer::usable(mc_png(40, 40, 6))['mime'] === 'image/png');
    }
    $interlacedConv = CardRenderer::usable(mc_png(40, 40, 2, 8, true));
    check('card renderer (GD): an interlaced PNG is converted too (GD reads it)', $interlacedConv === null || $interlacedConv['mime'] === 'image/jpeg');
} else {
    check('card PNG (no GD): the preview is simply unavailable (null), never an error', CardRenderer::samplePng() === null && !CardPng::available());
    check('card renderer (no GD): a WebP / interlaced picture cannot be converted and is left out; PNG and JPEG are used as they are', CardRenderer::usable("RIFF\x00\x00\x00\x00WEBPVP8 ") === null && CardRenderer::usable(mc_png(40, 40, 2, 8, true)) === null
        && CardRenderer::usable(mc_png(40, 40, 6))['mime'] === 'image/png');
    $dPng = CardData::build(array_merge(CardData::sampleRaw(), ['photo' => ['bytes' => mc_png(320, 400, 2), 'mime' => 'image/png']]), $s);
    $pdfPng = CardRenderer::pdf($dPng, $s);
    check('card PDF (no GD): a PNG photo is embedded straight from the file (pure-PHP PNG reader), cropped by a clip path, valid file', mc_pdf_problems($pdfPng) === [] && substr_count($pdfPng, '/Subtype /Image') === 2);
}
check('card PDF: every card state renders (active, expired, suspended, revoked) and a card without QR still validates', (function () use ($s): bool {
    foreach (['active', 'expired', 'suspended', 'revoked'] as $st) {
        if (mc_pdf_problems(CardRenderer::pdf(CardData::build(array_merge(CardData::sampleRaw(), ['status' => $st]), $s), $s)) !== []) {
            return false;
        }
    }
    return mc_pdf_problems(CardRenderer::pdf(CardData::build(CardData::sampleRaw(), CardSettings::resolve(['membership_card_show_qr' => '0'])), CardSettings::resolve())) === [];
})());

require __DIR__ . '/selftest-membership-card-flow.php';
