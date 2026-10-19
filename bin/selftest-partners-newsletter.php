<?php
/**
 * Partner-logo pipeline + PDF newsletter checks, included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe -d extension=gd bin/selftest-partners-newsletter.php
 * The GD-dependent checks run only when the gd extension is loaded (XAMPP ships php_gd.dll but leaves it off: use -d extension=gd, or enable it
 * in php.ini); the "no GD" and "no WebP" paths are always tested by simulating the missing capability.
 * Covers: ImageOptimizer (trim, fit, 2x, WebP + PNG, EXIF rotation, metadata, rejects: SVG / HTML / polyglot / truncated / oversize pixels / oversize
 * bytes, memory-safe size, tiny image, no GD, no WebP), Uploader::logo name rules, PartnerLogos (columns, view, release, shared files, backfill),
 * PdfValidator + Uploader::pdf (valid, renamed, truncated, oversize, double extension, scripts, encrypted, header not at byte 0), NewsletterPdf +
 * NewsletterService (kind, readiness, attach, publish, live status, remove rules, duplicate copy, delete release), the public PDF routes over HTTP
 * (visibility, headers, counter, unpublish at once), registry, menu, schema and cache/CSP headers.
 * Everything it creates is removed again (partners zz-selftest-*, newsletters zz-selftest-pdf-*, files below uploads/zz-selftest and storage/private/newsletters).
 */
declare(strict_types=1);

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit("Refusing to run outside APP_ENV=local.\n");
    }
    $fail = 0;
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
    $standalone = true;
}

use App\Core\Database;
use App\Core\ImageOptimizer;
use App\Core\Nav;
use App\Core\PageRegistry;
use App\Core\PdfValidator;
use App\Core\SitePages;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\Newsletter;
use App\Models\Partner;
use App\Services\ImageCleanup;
use App\Services\Newsletter\NewsletterPdf;
use App\Services\Newsletter\NewsletterService;
use App\Services\PartnerLogos;

echo "Partner logos and PDF newsletters\n";

$pnGd = function_exists('imagecreatetruecolor');
$pnTmp = sys_get_temp_dir() . '/lumina-pn-' . bin2hex(random_bytes(4));
@mkdir($pnTmp, 0777, true);
$pnRel = 'zz-selftest/' . bin2hex(random_bytes(3));           // below uploads/
$pnPartnerIds = [];
$pnIssueIds = [];
$pnSwitchedPage = false;
$pnPageBefore = null;

/** Run $fn; returns the UploadException message or null when it did not throw. */
$pnThrows = static function (callable $fn): ?string {
    try {
        $fn();
        return null;
    } catch (UploadException $e) {
        return $e->getMessage();
    }
};
/** A PNG/JPEG/... file written to the temp dir; returns its path. */
$pnWrite = static function (string $name, string $bytes) use ($pnTmp): string {
    $p = $pnTmp . '/' . $name;
    file_put_contents($p, $bytes);
    return $p;
};
/** Insert a PNG ancillary chunk (tEXt) before IEND. */
$pnPngChunk = static function (string $png, string $type, string $data): string {
    $chunk = pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
    return substr($png, 0, -12) . $chunk . substr($png, -12);
};
$pnPngBytes = static function (\GdImage $im): string {
    ob_start();
    imagepng($im);
    return (string) ob_get_clean();
};
$pnJpegBytes = static function (\GdImage $im): string {
    ob_start();
    imagejpeg($im, null, 92);
    return (string) ob_get_clean();
};
$pnAbs = static fn(string $rel): string => UPLOAD_PATH . '/' . $rel;
$pnSize = static function (string $rel) use ($pnAbs): array {
    $i = @getimagesize($pnAbs($rel));
    return $i === false ? [0, 0] : [(int) $i[0], (int) $i[1]];
};
$pnPdf = static fn(string $extra = ''): string => "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] {$extra}>>\nendobj\ntrailer\n<< /Root 1 0 R /Size 4 >>\nstartxref\n0\n%%EOF\n";

try {
    // ------------------------------------------------------------------ capabilities
    $caps = ImageOptimizer::capabilities();
    echo '  info GD ' . ($caps['gd'] ? 'loaded' : 'NOT loaded (GD checks skipped; run with -d extension=gd)') . ', WebP ' . ($caps['webp'] ? 'yes' : 'no') . ', EXIF ' . ($caps['exif'] ? 'yes' : 'no') . PHP_EOL;
    ImageOptimizer::forceCapabilities(['gd' => false]);
    check('capabilities(): a forced "no GD" switches every decoder off', ImageOptimizer::capabilities()['gd'] === false && ImageOptimizer::capabilities()['png'] === false && ImageOptimizer::capabilityNotice() !== null);
    ImageOptimizer::forceCapabilities(null);

    // ------------------------------------------------------------------ rejects (do not need GD)
    $svg = $pnWrite('a.png', '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><script>alert(1)</script></svg>');
    check('reject: SVG disguised as .png', $pnThrows(fn() => ImageOptimizer::logo($svg)) !== null);
    $html = $pnWrite('b.png', '<!doctype html><html><body><script>alert(1)</script></body></html>');
    check('reject: HTML disguised as .png', $pnThrows(fn() => ImageOptimizer::logo($html)) !== null);
    $empty = $pnWrite('c.png', '');
    check('reject: empty file', $pnThrows(fn() => ImageOptimizer::logo($empty)) !== null);
    $fakeGif = $pnWrite('d.gif', 'GIF89a' . str_repeat("\x00", 40));
    check('reject: a GIF header with no picture behind it', $pnThrows(fn() => ImageOptimizer::logo($fakeGif)) !== null);
    $big = $pnWrite('e.png', "\x89PNG\r\n\x1a\n" . str_repeat('A', ImageOptimizer::MAX_BYTES + 10));
    $m = $pnThrows(fn() => ImageOptimizer::logo($big));
    check('reject: more than 5 MB', $m !== null && str_contains($m, 'too large'), (string) $m);
    // a real PNG header that claims 7000 x 7000 px (header only: nothing is decoded, so no memory is needed)
    $ihdr = pack('N', 7000) . pack('N', 7000) . "\x08\x06\x00\x00\x00";
    $png7k = "\x89PNG\r\n\x1a\n" . pack('N', 13) . 'IHDR' . $ihdr . pack('N', crc32('IHDR' . $ihdr)) . pack('N', 0) . 'IEND' . pack('N', crc32('IEND'));
    $m = $pnThrows(fn() => ImageOptimizer::inspect($pnWrite('f.png', $png7k)));
    check('reject: more than 6000 x 6000 px (read from the header, never decoded)', $m !== null && str_contains($m, '6000'), (string) $m);

    // ------------------------------------------------------------------ the pipeline (GD)
    if ($pnGd) {
        // 1) transparent PNG, 300 px empty margins, red disc + blue bar
        $im = imagecreatetruecolor(1600, 800);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, (int) imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);
        imagefilledellipse($im, 600, 400, 300, 300, (int) imagecolorallocate($im, 255, 0, 0));
        imagefilledrectangle($im, 800, 330, 1250, 470, (int) imagecolorallocate($im, 0, 0, 255));
        $f1 = $pnWrite('logo1.png', $pnPngBytes($im));
        $t0 = microtime(true);
        $r = ImageOptimizer::logo($f1, ['dir' => $pnRel . '/a']);
        $ms = (int) round((microtime(true) - $t0) * 1000);
        check('logo 1600x800 transparent: optimised, trimmed, 1x + 2x + WebP pair', $r['optimised'] === true && in_array('trimmed', $r['warnings'], true) && $r['logo_2x'] !== null && ($r['logo_webp'] !== null) === ImageOptimizer::capabilities()['webp'], json_encode($r['warnings']));
        [$w1, $h1] = $pnSize($r['logo']);
        [$w2, $h2] = $pnSize((string) $r['logo_2x']);
        check('1x fits inside 480 x 240 and equals logo_width / logo_height', $w1 <= 480 && $h1 <= 240 && $w1 === $r['logo_width'] && $h1 === $r['logo_height'], "$w1 x $h1");
        check('2x is exactly twice the 1x picture (srcset "1x, 2x" never changes the shown size)', $w2 === 2 * $w1 && $h2 === 2 * $h1 && $w2 <= 960 && $h2 <= 480, "$w2 x $h2");
        check('trimmed content is about 800 x 300 (disc + bar): 1x = half of it + 6 px padding, never upscaled', abs($w1 - (400 + 12)) <= 3 && abs($h1 - (150 + 12)) <= 3 && $w2 <= 802 + 24 + 2, "$w1 x $h1 / $w2 x $h2");
        $o = imagecreatefrompng($pnAbs($r['logo']));
        $corner = (int) imagecolorat($o, 1, 1);
        check('transparency kept: padding corner is fully transparent', (($corner >> 24) & 0x7F) === 127);
        $dark = 0;
        for ($y = 0; $y < imagesy($o); $y++) {
            for ($x = 0; $x < imagesx($o); $x++) {
                $c = (int) imagecolorat($o, $x, $y);
                $a = ($c >> 24) & 0x7F;
                if ($a > 0 && $a < 127 && ((($c >> 16) & 255) < 120) && (($c & 255) < 120)) {
                    $dark++;
                }
            }
        }
        check('no dark halo on the anti-aliased edges of a cut-out logo', $dark === 0, "$dark dark edge pixels");
        check('result lists every file written, all inside uploads/, random names', count($r['files']) === (ImageOptimizer::capabilities()['webp'] ? 4 : 2) && !array_filter($r['files'], static fn($p) => !is_file(UPLOAD_PATH . '/' . $p)) && preg_match('~-?[0-9a-f]{24}(-2x)?\.(png|webp)$~', $r['logo']) === 1);
        check('speed: a 1600x800 logo takes under 3 s', $ms < 3000, "$ms ms");

        // 2) white-background JPG with margins
        $jp = imagecreatetruecolor(1200, 600);
        imagefill($jp, 0, 0, (int) imagecolorallocate($jp, 255, 255, 255));
        imagefilledrectangle($jp, 200, 150, 1000, 450, (int) imagecolorallocate($jp, 20, 90, 60));
        $f2 = $pnWrite('logo2.jpg', $pnJpegBytes($jp));
        $r2 = ImageOptimizer::logo($f2, ['dir' => $pnRel . '/b']);
        [$jw, $jh] = $pnSize($r2['logo']);
        check('white-background JPG: near-white margins trimmed, flagged opaque', in_array('trimmed', $r2['warnings'], true) && in_array('opaque_background', $r2['warnings'], true) && abs($jw - (400 + 12)) <= 5 && abs($jh - (150 + 12)) <= 5, "$jw x $jh " . json_encode($r2['warnings']));
        $r2b = ImageOptimizer::logo($f2, ['dir' => $pnRel . '/b2', 'trim' => false]);
        [$nw] = $pnSize($r2b['logo']);
        check('trim can be switched off (setting partners_logo_trim)', !in_array('trimmed', $r2b['warnings'], true) && $nw > $jw, "$nw vs $jw");

        // 2b) JPEG logo on "paper white" / cream / light grey: the flat background becomes transparent (no tinted rectangle), margins are trimmed
        foreach (['F4F4F0' => [244, 244, 240], 'cream F7F3EC' => [247, 243, 236], 'grey EDEDED' => [237, 237, 237]] as $bgName => $bgRgb) {
            $jt = imagecreatetruecolor(1200, 600);
            imagefill($jt, 0, 0, (int) imagecolorallocate($jt, ...$bgRgb));
            imagefilledrectangle($jt, 200, 150, 1000, 450, (int) imagecolorallocate($jt, 20, 90, 60));
            $rt = ImageOptimizer::logo($pnWrite('tint.jpg', $pnJpegBytes($jt)), ['dir' => $pnRel . '/b3']);
            $ci = imagecreatefrompng(UPLOAD_PATH . '/' . $rt['logo']);
            [$tw, $th] = $pnSize($rt['logo']);
            check("off-white JPG background ({$bgName}): made transparent, trimmed, no tinted rectangle left", in_array('background_removed', $rt['warnings'], true) && in_array('trimmed', $rt['warnings'], true) && ((imagecolorat($ci, 1, 1) >> 24) & 0x7F) === 127 && abs($tw - (400 + 12)) <= 6 && abs($th - (150 + 12)) <= 6, "$tw x $th " . json_encode($rt['warnings']));
        }
        $jd = imagecreatetruecolor(600, 300);
        imagefill($jd, 0, 0, (int) imagecolorallocate($jd, 20, 40, 100));
        imagefilledrectangle($jd, 100, 80, 500, 220, (int) imagecolorallocate($jd, 240, 240, 236));
        $rd = ImageOptimizer::logo($pnWrite('badge.jpg', $pnJpegBytes($jd)), ['dir' => $pnRel . '/b3']);
        $di = imagecreatefrompng(UPLOAD_PATH . '/' . $rd['logo']);
        check('a dark or coloured badge background is left alone (only flat LIGHT backgrounds are removed)', !in_array('background_removed', $rd['warnings'], true) && ((imagecolorat($di, 8, 8) >> 24) & 0x7F) === 0);

        // 3) a white logo on a transparent background is NOT mistaken for empty space
        $wl = imagecreatetruecolor(400, 200);
        imagealphablending($wl, false);
        imagesavealpha($wl, true);
        imagefill($wl, 0, 0, (int) imagecolorallocatealpha($wl, 255, 255, 255, 127));
        imagealphablending($wl, true);
        imagefilledrectangle($wl, 100, 60, 300, 140, (int) imagecolorallocate($wl, 255, 255, 255));
        $r3 = ImageOptimizer::logo($pnWrite('logo3.png', $pnPngBytes($wl)), ['dir' => $pnRel . '/c']);
        [$ww, $wh] = $pnSize($r3['logo']);
        check('white logo on transparent: kept (trimmed to 201 x 81 + padding, not mistaken for empty space)', abs($ww - 213) <= 1 && abs($wh - 93) <= 1, "$ww x $wh");

        // 4) tiny image: never upscaled, no 2x, warned
        $ti = imagecreatetruecolor(60, 30);
        imagefill($ti, 0, 0, (int) imagecolorallocate($ti, 200, 30, 30));
        $r4 = ImageOptimizer::logo($pnWrite('logo4.png', $pnPngBytes($ti)), ['dir' => $pnRel . '/d']);
        [$tw, $th] = $pnSize($r4['logo']);
        check('tiny 60x30: kept at its own size (+ padding), no 2x, low-resolution warning', $tw === 72 && $th === 42 && $r4['logo_2x'] === null && in_array('low_resolution', $r4['warnings'], true), "$tw x $th " . json_encode($r4['warnings']));

        // 5) EXIF rotation (orientation 6 = rotate 90 clockwise): a 100 x 40 picture, red on the LEFT, becomes 40 x 100 with red on TOP
        $ex = imagecreatetruecolor(100, 40);
        imagefill($ex, 0, 0, (int) imagecolorallocate($ex, 0, 0, 255));
        imagefilledrectangle($ex, 0, 0, 49, 39, (int) imagecolorallocate($ex, 255, 0, 0));
        $jpg = $pnJpegBytes($ex);
        $tiff = "MM\x00\x2A\x00\x00\x00\x08" . "\x00\x01" . "\x01\x12\x00\x03\x00\x00\x00\x01\x00\x06\x00\x00" . "\x00\x00\x00\x00";
        $app1 = "Exif\x00\x00" . $tiff;
        $jpgExif = substr($jpg, 0, 2) . "\xFF\xE1" . pack('n', strlen($app1) + 2) . $app1 . substr($jpg, 2);
        $r5 = ImageOptimizer::logo($pnWrite('logo5.jpg', $jpgExif), ['dir' => $pnRel . '/e', 'trim' => false]);
        [$rw, $rh] = $pnSize($r5['logo']);
        $ro = imagecreatefrompng($pnAbs($r5['logo']));
        $top = (int) imagecolorat($ro, (int) ($rw / 2), 6 + 5);
        $bottom = (int) imagecolorat($ro, (int) ($rw / 2), $rh - 6 - 5);
        check('EXIF orientation 6 honoured: taller than wide, red on top, blue at the bottom', $rh > $rw && (($top >> 16) & 255) > 200 && ($bottom & 255) > 200, "$rw x $rh");

        // 6) metadata is gone (comment and camera text in the source never reach the output)
        $jc = substr($jpg, 0, 2) . "\xFF\xFE" . pack('n', 2 + 10) . 'SecTestCam' . substr($jpg, 2);
        $r6 = ImageOptimizer::logo($pnWrite('logo6.jpg', $jc), ['dir' => $pnRel . '/f']);
        $leak = false;
        foreach ($r6['files'] as $p) {
            $leak = $leak || str_contains((string) file_get_contents($pnAbs($p)), 'SecTestCam');
        }
        check('metadata (COM / EXIF text) does not survive the re-encoding', str_contains($jc, 'SecTestCam') && !$leak);

        // 7) polyglot: a valid PNG carrying PHP code in a text chunk is refused outright
        $small = imagecreatetruecolor(20, 20);
        $poly = $pnPngChunk($pnPngBytes($small), 'tEXt', "Comment\x00<?php system(\$_GET['c']); ?>");
        $m = $pnThrows(fn() => ImageOptimizer::logo($pnWrite('poly.png', $poly)));
        check('reject: valid PNG with hidden <?php code (polyglot)', $m !== null && str_contains($m, 'hidden'), (string) $m);
        $truncated = substr($pnPngBytes($im), 0, 3000);
        check('reject: truncated PNG', $pnThrows(fn() => ImageOptimizer::logo($pnWrite('trunc.png', $truncated))) !== null);

        // 8) a blank picture
        $blank = imagecreatetruecolor(300, 200);
        imagefill($blank, 0, 0, (int) imagecolorallocate($blank, 255, 255, 255));
        $m = $pnThrows(fn() => ImageOptimizer::logo($pnWrite('blank.png', $pnPngBytes($blank)), ['dir' => $pnRel . '/g']));
        check('reject: an all-white picture ("looks blank")', $m !== null && str_contains($m, 'blank'), (string) $m);

        // 9) a large valid image stays quick and inside memory
        $lg = imagecreatetruecolor(3000, 1500);
        imagefill($lg, 0, 0, (int) imagecolorallocate($lg, 255, 255, 255));
        imagefilledrectangle($lg, 300, 200, 2700, 1300, (int) imagecolorallocate($lg, 10, 40, 120));
        imagestring($lg, 5, 400, 300, 'LUMINA', (int) imagecolorallocate($lg, 255, 255, 255));
        $t0 = microtime(true);
        $r9 = ImageOptimizer::logo($pnWrite('big.png', $pnPngBytes($lg)), ['dir' => $pnRel . '/h']);
        $ms = (int) round((microtime(true) - $t0) * 1000);
        [$bw, $bh] = $pnSize($r9['logo']);
        check('3000 x 1500 logo: processed in under 5 s, fitted (2400 x 1100 content -> 468 x 214 inside the box)', $ms < 5000 && $bw <= 480 && $bh <= 240 && $r9['logo_2x'] !== null, "$ms ms, $bw x $bh");

        // 10) an opaque photographic logo gets a JPEG fallback when the PNG would be heavy
        $ph = imagecreatetruecolor(1000, 500);
        for ($px = 0; $px < 1000; $px += 4) {
            for ($py = 0; $py < 500; $py += 4) {
                imagefilledrectangle($ph, $px, $py, $px + 3, $py + 3, (int) imagecolorallocate($ph, random_int(0, 255), random_int(0, 255), random_int(0, 255)));
            }
        }
        $r10 = ImageOptimizer::logo($pnWrite('noise.png', $pnPngBytes($ph)), ['dir' => $pnRel . '/i']);
        check('opaque noisy picture: the heavy PNG fallback is replaced by a JPEG', str_ends_with($r10['logo'], '.jpg') && (str_ends_with((string) $r10['logo_2x'], '.jpg') || $r10['logo_2x'] === null), (string) $r10['logo']);

        // 11) simulated host WITHOUT WebP: PNG only, flagged
        ImageOptimizer::forceCapabilities(['webp' => false]);
        $r11 = ImageOptimizer::logo($f1, ['dir' => $pnRel . '/j']);
        ImageOptimizer::forceCapabilities(null);
        check('no WebP support: PNG pair only, "no_webp" flagged', $r11['logo_webp'] === null && $r11['logo_webp_2x'] === null && in_array('no_webp', $r11['warnings'], true) && is_file($pnAbs($r11['logo'])) && $r11['optimised'] === true);

        // 12) a failed step leaves no orphan files (GD fails on a decodable-looking but broken body)
        $before = count(glob($pnAbs($pnRel . '/k') . '/*') ?: []);
        $m = $pnThrows(fn() => ImageOptimizer::logo($pnWrite('trunc2.png', substr($pnPngBytes($lg), 0, 9000)), ['dir' => $pnRel . '/k']));
        check('a broken picture is refused and writes nothing', $m !== null && count(glob($pnAbs($pnRel . '/k') . '/*') ?: []) === $before);
    }

    // ------------------------------------------------------------------ no GD at all (simulated; the picture itself is made with GD when available)
    if ($pnGd) {
        $withCom = substr($jpg, 0, 2) . "��" . pack("n", 12) . "SecTestCam" . substr($jpg, 2);
        ImageOptimizer::forceCapabilities(['gd' => false]);
        $rn = ImageOptimizer::logo($pnWrite('nogd.jpg', $withCom), ['dir' => $pnRel . '/nogd']);
        ImageOptimizer::forceCapabilities(null);
        $stored0 = (string) file_get_contents($pnAbs($rn['logo']));
        // bytes appended after the picture (a short-tag payload is not caught by the php-tag filter) must not reach the stored file: JPEG is cut at its EOI, GIF at its trailer
        ob_start();
        imagegif($im);
        $gifBytes = (string) ob_get_clean();
        $payloadTail = '<?= system($_GET[0]) ?>';
        foreach (['jpg' => $jpg, 'gif' => $gifBytes] as $ext => $pic) {
            ImageOptimizer::forceCapabilities(['gd' => false]);
            $rp = ImageOptimizer::logo($pnWrite('tail.' . $ext, $pic . $payloadTail), ['dir' => $pnRel . '/nogd']);
            ImageOptimizer::forceCapabilities(null);
            $storedP = (string) file_get_contents($pnAbs($rp['logo']));
            check("no GD: bytes after the end of a {$ext} are not stored (hidden payload removed, picture still decodes)", !str_contains($storedP, 'system') && !str_contains($storedP, '<?') && @imagecreatefromstring($storedP) !== false);
        }
        check('ImageScrubber: a truncated JPEG / GIF is refused, not stored half-cleaned', \App\Core\ImageScrubber::scrub(substr($jpg, 0, -30), 'image/jpeg') === null && \App\Core\ImageScrubber::scrub(substr($gifBytes, 0, -3), 'image/gif') === null);
        check('no GD: the validated original is stored, metadata removed, flagged with a notice', $rn['optimised'] === false && $rn['notice'] !== null && str_contains($withCom, 'SecTestCam') && !str_contains($stored0, 'SecTestCam') && $rn['logo_width'] === 100 && $rn['logo_2x'] === null && $rn['logo_webp'] === null, json_encode([$rn['optimised'], $rn['notice']]));
    }
    ImageOptimizer::forceCapabilities(['gd' => false]);
    check('no GD: the rejects still apply (SVG, oversize pixels)', $pnThrows(fn() => ImageOptimizer::logo($svg)) !== null && $pnThrows(fn() => ImageOptimizer::inspect($pnWrite('f2.png', $png7k))) !== null);
    ImageOptimizer::forceCapabilities(null);

    // ------------------------------------------------------------------ Uploader::logo (client file name rules) and PartnerLogos
    if ($pnGd) {
        $okPng = $pnWrite('up.png', $pnPngBytes($im));
        $file = static fn(string $name, string $tmp): array => ['name' => $name, 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => (int) filesize($tmp), 'type' => 'image/png'];
        foreach (['logo.svg', 'logo.php.png', 'logo.png.php', 'logo.html', 'logo', 'logo.pdf', 'logo.png.exe'] as $badName) {
            check("Uploader::logo refuses the file name \"{$badName}\"", $pnThrows(fn() => Uploader::logo($file($badName, $okPng))) !== null);
        }
        $up = PartnerLogos::store($file('My Logo.PNG', $okPng));
        $cols = $up['columns'];
        check('PartnerLogos::store(): columns for the table (logo, 2x, webp, size, optimised flag)', str_starts_with($cols['logo'], 'partners/') && $cols['logo_2x'] !== null && $cols['logo_optimised'] === 1 && $cols['logo_width'] > 0 && $cols['logo_height'] > 0, json_encode($cols));

        $pnPartnerIds[] = $idA = Partner::create(['name' => 'zz-selftest A & <b>Co</b>', 'partner_type' => 'partner', 'is_active' => 1, 'sort_order' => 9001] + $cols);
        $rowA = Partner::find($idA);
        $v = PartnerLogos::view($rowA);
        check('PartnerLogos::view(): src, srcset "1x, 2x", WebP srcset, width and height for no layout shift', $v['has'] && $v['srcset'] !== '' && str_contains($v['srcset'], ' 2x') && $v['width'] === (int) $rowA['logo_width'] && $v['height'] === (int) $rowA['logo_height'] && ($v['webp_srcset'] !== '') === ($rowA['logo_webp'] !== null));
        $html = \App\Core\View::partial('partials/partner-logo-img', ['partner' => $rowA]);
        check('partials/partner-logo-img: <picture>, WebP source, escaped alt = partner name, width / height, lazy', str_contains($html, '<picture') && str_contains($html, 'loading="lazy"') && str_contains($html, 'alt="zz-selftest A &amp; &lt;b&gt;Co&lt;/b&gt;"') && str_contains($html, 'width="' . $rowA['logo_width'] . '"') && (!ImageOptimizer::capabilities()['webp'] || str_contains($html, 'type="image/webp"')), $html);
        check('partial prints nothing for a partner without a logo', trim(\App\Core\View::partial('partials/partner-logo-img', ['partner' => ['name' => 'x', 'logo' => null]])) === '');
        check('ImageCleanup knows the new logo columns (a 2x file still used is never released)', ImageCleanup::isReferenced((string) $rowA['logo_2x']) === true);

        // shared files: a second row that points at the same logo keeps the files alive
        $pnPartnerIds[] = $idB = Partner::create(['name' => 'zz-selftest B', 'partner_type' => 'funder', 'is_active' => 1, 'sort_order' => 9002] + $cols);
        Partner::update($idA, PartnerLogos::emptyColumns());
        $removed = PartnerLogos::release($rowA);
        check('release(): files shared with another partner are kept', $removed === 0 && is_file($pnAbs($cols['logo'])));
        $rowB = Partner::find($idB);
        Partner::delete($idB);
        $pnPartnerIds = array_values(array_diff($pnPartnerIds, [$idB]));
        $removed = PartnerLogos::release($rowB);
        check('release(): when nothing else uses them the whole logo set is deleted', $removed >= 2 && !is_file($pnAbs($cols['logo'])) && !is_file($pnAbs((string) $cols['logo_2x'])), "removed $removed");

        // backfill of a logo uploaded before the pipeline existed
        $legacyRel = 'zz-selftest/legacy/' . bin2hex(random_bytes(6)) . '.png';
        @mkdir(dirname($pnAbs($legacyRel)), 0777, true);
        copy($okPng, $pnAbs($legacyRel));
        $pnPartnerIds[] = $idL = Partner::create(['name' => 'zz-selftest legacy', 'partner_type' => 'donor', 'is_active' => 1, 'sort_order' => 9003, 'logo' => $legacyRel]);
        check('legacy logo counts as pending', PartnerLogos::pendingCount() >= 1);
        $pendBefore = PartnerLogos::pendingCount();
        $missBefore = PartnerLogos::missingCount();
        $pnPartnerIds[] = $idG = Partner::create(['name' => 'zz-selftest gone', 'partner_type' => 'donor', 'is_active' => 1, 'sort_order' => 9004, 'logo' => 'zz-selftest/gone/' . bin2hex(random_bytes(4)) . '.png']);
        check('a partner whose logo file is missing is NOT "pending" (the banner can clear) but is counted as missing', PartnerLogos::pendingCount() === $pendBefore && PartnerLogos::missingCount() === $missBefore + 1 && PartnerLogos::view(Partner::find($idG) ?? [])['missing'] === true && PartnerLogos::view(Partner::find($idL) ?? [])['missing'] === false);
        $bf = PartnerLogos::optimiseExisting(100, $idL - 1);
        $rowL = Partner::find($idL);
        check('optimiseExisting(): the legacy logo gets its optimised set, the old original is released', $bf['done'] >= 1 && (int) $rowL['logo_optimised'] === 1 && $rowL['logo'] !== $legacyRel && !is_file($pnAbs($legacyRel)) && $rowL['logo_2x'] !== null, json_encode($bf));
        check('optimiseExisting() is repeatable (nothing left to do for that row)', PartnerLogos::optimiseExisting(100, $idL - 1)['done'] === 0);
        ImageOptimizer::forceCapabilities(['gd' => false]);
        $none = PartnerLogos::optimiseExisting(5);
        ImageOptimizer::forceCapabilities(null);
        check('optimiseExisting() without GD touches nothing and says why', $none['done'] === 0 && $none['notice'] !== null);
    }

    // ------------------------------------------------------------------ PDF validation
    $pdfOk = $pnWrite('ok.pdf', $pnPdf());
    $meta = PdfValidator::inspect($pdfOk, 25 * 1048576);
    check('PDF: a valid minimal PDF passes (version, page count)', $meta['version'] === '1.4' && $meta['pages'] === 1, json_encode($meta));
    $pdfCases = [
        'plain text renamed .pdf'         => 'Hello, this is not a PDF at all.',
        'HTML renamed .pdf'               => '<!doctype html><html><body>hi</body></html>',
        'junk before the %PDF header'     => "<html></html>\n" . $pnPdf(),
        'ZIP renamed .pdf'                => "PK\x03\x04" . str_repeat("\x00", 60),
        'truncated (no %%EOF)'            => substr($pnPdf(), 0, strlen($pnPdf()) - 30),
        'password protected (/Encrypt)'   => str_replace('/Size 4', '/Size 4 /Encrypt 9 0 R', $pnPdf()),
        'embedded JavaScript'             => $pnPdf('/AA << /O << /S /JavaScript /JS (app.alert(1)) >> >> '),
        'launch action'                   => $pnPdf('/AA << /O << /S /Launch /F (cmd.exe) >> >> '),
        'JavaScript with a #xx name escape' => $pnPdf('/AA << /O << /S /Java#53cript /JS (app.alert(1)) >> >> '),
        'fully escaped JavaScript name'   => $pnPdf('/AA << /O << /S /#4Aava#53cript >> >> '),
        'Launch with a #xx name escape'   => $pnPdf('/AA << /O << /S /Lau#6Ech /F (cmd.exe) >> >> '),
        'SubmitForm action'               => $pnPdf('/AA << /O << /S /SubmitForm /F (x) >> >> '),
        'script hidden past 6 MB'         => $pnPdf() . str_repeat('A', 7 * 1048576) . "\n" . substr($pnPdf('/AA << /S /JavaScript >> '), 8),
        'launch past 6 MB, escaped'       => $pnPdf() . str_repeat('A', 7 * 1048576) . "\n" . substr($pnPdf('/AA << /S /Lau#6Ech >> '), 8),
        'script across a 1 MB chunk border' => $pnPdf() . str_repeat('A', 1048576 - strlen($pnPdf()) - 5) . '/Java#53cript ' . str_repeat('B', 100) . "\n%%EOF\n",
        'password protected past 6 MB'    => $pnPdf() . str_repeat('A', 7 * 1048576) . "\n/Encrypt 9 0 R\n%%EOF\n",
    ];
    foreach ($pdfCases as $label => $bytes) {
        check("PDF reject: {$label}", $pnThrows(fn() => PdfValidator::inspect($pnWrite('x.pdf', $bytes), 25 * 1048576)) !== null);
    }
    $bigClean = $pnWrite('big.pdf', $pnPdf() . str_repeat("q\x01#\x02#41 #zz\n", 600000) . $pnPdf());   // about 8 MB of binary-ish bytes with stray "#" signs and 2 pages
    $bigMeta = PdfValidator::inspect($bigClean, 25 * 1048576);
    check('PDF: a large clean PDF (over 6 MB, stray # bytes) passes and both pages are counted', $bigMeta['pages'] === 2 && $bigMeta['size'] > 6 * 1048576, json_encode($bigMeta));
    @unlink($bigClean);
    $m = $pnThrows(fn() => PdfValidator::inspect($pdfOk, 100));
    check('PDF reject: above the size limit (message names both sizes)', $m !== null && str_contains($m, 'too large'), (string) $m);
    check('PDF reject: empty file', $pnThrows(fn() => PdfValidator::inspect($pnWrite('empty.pdf', ''), 1000)) !== null);

    $pf = static fn(string $name, string $tmp): array => ['name' => $name, 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => (int) filesize($tmp), 'type' => 'application/pdf'];
    foreach (['report.pdf.php', 'a.php.pdf', 'a.pdf.html', 'a.pdf.exe', 'a.html.pdf', 'newsletter', 'a.PDF.svg'] as $badName) {
        check("Uploader::pdf refuses the file name \"{$badName}\"", $pnThrows(fn() => Uploader::pdf($pf($badName, $pdfOk))) !== null);
    }
    $stored = Uploader::pdf($pf('Issue 3 (Oct) 2026.PDF', $pdfOk));
    check('Uploader::pdf(): stored below storage/private/newsletters/YYYY/MM with a random name, clean display name, size, mime', preg_match('~^newsletters/\d{4}/\d{2}/[0-9a-f]{32}\.pdf$~', $stored['path']) === 1 && $stored['name'] === 'Issue 3 (Oct) 2026.PDF' && $stored['size'] === filesize($pdfOk) && $stored['mime'] === 'application/pdf' && Uploader::privateExists($stored['path']));
    check('the stored PDF is NOT below the web root uploads/ folder', !str_contains(Uploader::privatePath($stored['path']), str_replace('\\', '/', UPLOAD_PATH)) && str_contains(str_replace('\\', '/', Uploader::privatePath($stored['path'])), '/storage/private/'));
    $uni = Uploader::pdf($pf("Lettre \u{00E9}t\u{00E9} 2026.pdf", $pdfOk));
    $uni2 = Uploader::pdf($pf("Lumina\u{2019}s Newsletter \u{2013} Oct.pdf", $pdfOk));
    Uploader::deletePrivate($uni['path']);
    Uploader::deletePrivate($uni2['path']);
    check('PDF display names keep accents and curly punctuation ("Lettre été 2026.pdf", "Lumina’s Newsletter – Oct.pdf")', $uni['name'] === "Lettre \u{00E9}t\u{00E9} 2026.pdf" && $uni2['name'] === "Lumina\u{2019}s Newsletter \u{2013} Oct.pdf", $uni['name'] . ' | ' . $uni2['name']);
    $long = Uploader::pdfDisplayName(str_repeat("\u{00E9}", 300) . '.pdf');
    check('PDF display names: control characters, quotes, slashes and path parts are neutralised; a long name is cut BEFORE ".pdf" (190 characters in all)', mb_strlen($long) === 190 && str_ends_with($long, '.pdf') && Uploader::pdfDisplayName("a\"b\c/d\te\n.pdf") === 'd_e_.pdf' && Uploader::pdfDisplayName('...pdf') === 'newsletter.pdf' && Uploader::pdfDisplayName("bad\xFF.pdf") !== '');
    $copy = Uploader::copyPrivate($stored['path']);
    check('Uploader::copyPrivate(): a separate copy with its own random name', $copy !== null && $copy !== $stored['path'] && Uploader::privateExists($copy));
    Uploader::deletePrivate($copy);
    Uploader::deletePrivate($stored['path']);
    check('Uploader::deletePrivate(): files are removed; traversal paths are refused', !Uploader::privateExists($stored['path']) && !Uploader::privateExists('../../app/bootstrap.php') && Uploader::pdfMaxBytes() > 0);

    // ------------------------------------------------------------------ newsletter service: kind, PDF, live status
    $nl = NewsletterService::create(['title' => 'zz-selftest-pdf one', 'kind' => 'pdf', 'summary' => 'A PDF issue'], null);
    check('create(): kind "pdf" is stored; the default kind is "online"', $nl['ok'] && $nl['newsletter']['kind'] === 'pdf');
    $nid = (int) $nl['newsletter']['id'];
    $pnIssueIds[] = $nid;
    $on = NewsletterService::create(['title' => 'zz-selftest-pdf online'], null);
    $pnIssueIds[] = (int) $on['newsletter']['id'];
    check('create(): no kind given means online', $on['ok'] && $on['newsletter']['kind'] === 'online');
    check('create(): an unknown kind is refused', !NewsletterService::create(['title' => 'zz-selftest-pdf x', 'kind' => 'video'], null)['ok']);
    $bad = NewsletterService::save($nid, ['kind' => 'online'], (int) $nl['newsletter']['version'], null);
    check('save(): the format cannot be changed after creation', !$bad['ok'] && isset($bad['errors']['kind']));

    $pub = NewsletterService::publish($nid);
    check('publish(): a PDF issue without a PDF is refused ("pdf" is the missing field)', !$pub['ok'] && isset($pub['errors']['pdf']), json_encode($pub));
    $live = NewsletterService::liveStatus(Newsletter::find($nid) ?? []);
    check('liveStatus(): draft is "not visible" and lists why', $live['state'] === 'draft' && $live['visible'] === false && str_contains($live['label'], 'not visible') && count($live['reasons']) >= 2 && str_ends_with($live['url'], '/newsletter/' . $nl['newsletter']['slug']));
    $badUp = NewsletterPdf::attach($nid, $pf('x.pdf', $pnWrite('junk.pdf', 'nope')), null);
    check('attach(): a non-PDF is refused with a readable message and nothing is stored', !$badUp['ok'] && isset($badUp['errors']['pdf']) && !NewsletterPdf::has(Newsletter::find($nid) ?? []));
    $up = NewsletterPdf::attach($nid, $pf('Oct 2026.pdf', $pdfOk), null);
    $row = Newsletter::find($nid) ?? [];
    check('attach(): path, display name, size and upload time are stored', $up['ok'] && NewsletterPdf::exists($row) && $row['pdf_name'] === 'Oct 2026.pdf' && (int) $row['pdf_size'] === filesize($pdfOk) && $row['pdf_updated_at'] !== null);
    $firstPath = (string) $row['pdf_path'];
    $pub = NewsletterService::publish($nid);
    check('publish(): the PDF issue goes live once it has a PDF', $pub['ok'] && NewsletterService::isVisible(Newsletter::find($nid) ?? []));
    $row = Newsletter::find($nid) ?? [];
    $live = NewsletterService::liveStatus($row);
    check('liveStatus(): "Live on the website", visible, latest issue, public address', $live['state'] === 'live' && $live['visible'] && $live['label'] === 'Live on the website' && $live['tone'] === 'success');
    $info = NewsletterPdf::info($row);
    check('info(): file name, human size and routes for the buttons', $info['has'] && $info['exists'] && $info['filename'] === 'Oct 2026.pdf' && $info['size_human'] !== '' && str_ends_with($info['download_url'], '/download') && str_ends_with($info['view_url'], '/pdf'));
    // the JSON API must not advertise a PDF whose file is gone (its url / view_url would answer 404)
    $apiRef = new \ReflectionMethod(\App\Controllers\Web\NewsletterController::class, 'apiPdf');
    $apiCtl = (new \ReflectionClass(\App\Controllers\Web\NewsletterController::class))->newInstanceWithoutConstructor();
    $ghost = $row;
    $ghost['pdf_path'] = 'newsletters/2026/10/' . str_repeat('0', 32) . '.pdf';
    check('API: pdf object present when the file exists, null when the file is gone (full rows)', is_array($apiRef->invoke($apiCtl, $row)) && $apiRef->invoke($apiCtl, $ghost) === null);
    check('API: list rows (LIST_COLUMNS carry pdf_path) are checked against the disk as well', is_array($apiRef->invoke($apiCtl, Database::all('SELECT ' . NewsletterService::LIST_COLUMNS . ' FROM newsletters n WHERE n.id = ?', [$nid])[0])) && $apiRef->invoke($apiCtl, ['pdf_path' => $ghost['pdf_path'], 'has_pdf' => 1, 'slug' => 'x', 'pdf_size' => 1]) === null);

    // public routes over HTTP
    $http = static function (string $path, string $method = 'GET') use ($pnTmp): array {
        $ctx = stream_context_create(['http' => ['method' => $method, 'timeout' => 10, 'ignore_errors' => true]]);
        $body = @file_get_contents('http://localhost/' . basename(BASE_PATH) . $path, false, $ctx);
        $h = $http_response_header ?? [];
        $status = isset($h[0]) && preg_match('~ (\d{3}) ~', $h[0], $m) ? (int) $m[1] : 0;
        $headers = [];
        foreach ($h as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $headers[strtolower(trim($k))] = trim($v);
            }
        }
        return [$status, $headers, (string) $body];
    };
    [$st] = $http('/');
    if ($st === 200) {
        $slug = (string) $row['slug'];
        [$s1, $h1, $b1] = $http('/newsletter/' . $slug . '/pdf');
        check('GET /newsletter/{slug}/pdf: 200 application/pdf, inline, nosniff, private no-store, noindex, the exact bytes', $s1 === 200 && ($h1['content-type'] ?? '') === 'application/pdf' && str_starts_with($h1['content-disposition'] ?? '', 'inline') && ($h1['x-content-type-options'] ?? '') === 'nosniff' && str_contains($h1['cache-control'] ?? '', 'no-store') && str_contains($h1['x-robots-tag'] ?? '', 'noindex') && $b1 === file_get_contents($pdfOk), "$s1 " . json_encode($h1));
        check('the PDF response may be framed by the site itself (X-Frame-Options SAMEORIGIN)', ($h1['x-frame-options'] ?? '') === 'SAMEORIGIN');
        [$s2, $h2] = $http('/newsletter/' . $slug . '/download');
        $after = (int) Database::value('SELECT pdf_downloads FROM newsletters WHERE id = ?', [$nid], 0);
        check('GET /newsletter/{slug}/download: attachment with the original file name, counter +1', $s2 === 200 && str_starts_with($h2['content-disposition'] ?? '', 'attachment') && str_contains($h2['content-disposition'] ?? '', 'Oct 2026.pdf') && $after === 1, "$s2 downloads=$after");
        $http('/newsletter/' . $slug . '/pdf');
        check('viewing inline does not count as a download', (int) Database::value('SELECT pdf_downloads FROM newsletters WHERE id = ?', [$nid], 0) === 1);
        [$s3, , $b3] = $http('/newsletter/' . $slug);
        check('GET /newsletter/{slug}: the PDF issue page renders (200)', $s3 === 200 && str_contains($b3, 'zz-selftest-pdf one'), (string) $s3);
        [$s4, $h4] = $http('/newsletter/' . $slug);
        check('HTML pages: private, max-age=0, must-revalidate (no stale copy after an admin change)', ($h4['cache-control'] ?? '') === 'private, max-age=0, must-revalidate');
        check('CSP lets the page embed its own PDF viewer (frame-src \'self\') and still forbids <object>', str_contains($h4['content-security-policy'] ?? '', "frame-src 'self' ") && str_contains($h4['content-security-policy'] ?? '', "object-src 'none'"));
        [, $hs] = $http('/sitemap.xml');
        check('sitemap.xml is revalidated on every request', ($hs['cache-control'] ?? '') === 'public, max-age=0, must-revalidate');
        [$sa] = $http('/newsletter/archive');
        check('/newsletter/archive still works', $sa === 200);
    } else {
        echo "  info web server not reachable on http://localhost/: HTTP checks skipped\n";
        $slug = (string) $row['slug'];
    }

    // replace the PDF: new random file, old one gone, counter restarts
    $pdf2 = $pnWrite('second.pdf', $pnPdf('/Rotate 0 '));
    $swap = NewsletterPdf::attach($nid, $pf('Second.pdf', $pdf2), null);
    $row2 = Newsletter::find($nid) ?? [];
    check('replacing the PDF: a new file at once, the old file is deleted, the counter restarts', $swap['ok'] && $row2['pdf_path'] !== $firstPath && !Uploader::privateExists($firstPath) && Uploader::privateExists((string) $row2['pdf_path']) && (int) $row2['pdf_downloads'] === 0);
    if ($st === 200) {
        [, , $b5] = $http('/newsletter/' . $slug . '/pdf');
        check('after the swap visitors get the NEW file immediately', $b5 === file_get_contents($pdf2));
    }
    $rm = NewsletterPdf::remove($nid);
    check('remove(): refused while a PDF issue is live (it would be empty)', !$rm['ok'] && NewsletterPdf::has(Newsletter::find($nid) ?? []));
    $dup = NewsletterService::duplicate($nid);
    $pnIssueIds[] = $did = (int) $dup['newsletter']['id'];
    $drow = Newsletter::find($did) ?? [];
    check('duplicate(): kind kept, its own COPY of the PDF, a draft', $dup['ok'] && $drow['kind'] === 'pdf' && $drow['pdf_path'] !== $row2['pdf_path'] && Uploader::privateExists((string) $drow['pdf_path']) && $drow['status'] === 'draft');

    $un = NewsletterService::unpublish($nid);
    if ($st === 200) {
        [$s6] = $http('/newsletter/' . $slug . '/pdf');
        [$s7] = $http('/newsletter/' . $slug . '/download');
        [$s8] = $http('/newsletter/' . $slug);
        check('unpublish: the PDF routes and the page are 404 at once', $un['ok'] && $s6 === 404 && $s7 === 404 && $s8 === 404, "$s6 $s7 $s8");
    }
    $live = NewsletterService::liveStatus(Newsletter::find($nid) ?? []);
    check('liveStatus(): "Unpublished — not visible" once it was live', $live['state'] === 'unpublished' && !$live['visible'] && str_starts_with($live['label'], 'Unpublished'));
    $rm = NewsletterPdf::remove($nid);
    check('remove(): allowed for a draft; file deleted; the issue is incomplete again', $rm['ok'] && !NewsletterPdf::has(Newsletter::find($nid) ?? []) && !Uploader::privateExists((string) $row2['pdf_path']) && !NewsletterService::publish($nid)['ok']);

    // schedule / archive states and the list annotation
    NewsletterPdf::attach($nid, $pf('Again.pdf', $pdf2), null);
    $future = date('Y-m-d\TH:i', time() + 86400);
    $sc = NewsletterService::schedule($nid, $future);
    $live = NewsletterService::liveStatus(Newsletter::find($nid) ?? []);
    check('liveStatus(): "Scheduled for ..." with the date, not visible yet', $sc['ok'] && $live['state'] === 'scheduled' && str_starts_with($live['label'], 'Scheduled for ') && !$live['visible']);
    $ann = NewsletterService::annotate(Database::all('SELECT ' . NewsletterService::LIST_COLUMNS . ' FROM newsletters n WHERE n.id IN (' . implode(',', array_map('intval', $pnIssueIds)) . ')'));
    $byId = array_column($ann, null, 'id');
    check('annotate(): every list row gets a "live" box; incomplete drafts say what is missing', isset($byId[$nid]['live']) && isset($byId[$did]['live']) && $byId[(int) $on['newsletter']['id']]['live']['state'] === 'draft' && array_filter($byId[(int) $on['newsletter']['id']]['live']['reasons'], static fn($r) => str_contains($r, 'Missing')) !== [], json_encode($byId[(int) $on['newsletter']['id']]['live']['reasons'] ?? []));
    $noFalsePdf = static fn(array $r): bool => array_filter($r['live']['reasons'], static fn($x) => str_contains($x, 'Upload the PDF')) === [];
    check('annotate(): a scheduled and a draft PDF issue that HAS its file are not reported as missing the PDF', $byId[$nid]['live']['state'] === 'scheduled' && $byId[$did]['live']['state'] === 'draft' && $noFalsePdf($byId[$nid]) && $noFalsePdf($byId[$did]), json_encode([$byId[$nid]['live']['reasons'], $byId[$did]['live']['reasons']]));
    check('list rows carry kind, has_pdf, pdf_size', $byId[$nid]['kind'] === 'pdf' && (int) $byId[$nid]['has_pdf'] === 1 && (int) $byId[$nid]['pdf_size'] > 0 && (int) $byId[(int) $on['newsletter']['id']]['has_pdf'] === 0);
    NewsletterService::unpublish($nid);
    NewsletterService::archiveIssue($nid);
    $live = NewsletterService::liveStatus(Newsletter::find($nid) ?? []);
    check('liveStatus(): archived', $live['state'] === 'archived' && str_starts_with($live['label'], 'Archived'));

    // an ONLINE issue with an attached PDF: complete without the PDF, the PDF is an extra
    $oid = (int) $on['newsletter']['id'];
    $withText = NewsletterService::save($oid, ['content' => ['version' => 1, 'featured' => ['heading' => 'Hello', 'body_html' => '<p>Text</p>'], 'blocks' => []]], (int) $on['newsletter']['version'], null);
    NewsletterPdf::attach($oid, $pf('Extra.pdf', $pdf2), null);
    $po = NewsletterService::publish($oid);
    check('online issue + attached PDF: publishes (the PDF is optional) and can lose the PDF while live', $withText['ok'] && $po['ok'] && NewsletterPdf::remove($oid)['ok']);
    NewsletterService::unpublish($oid);

    // the Newsletter page switched off: everything is "not visible", with the reason
    $pnPageBefore = Database::one('SELECT is_published FROM pages WHERE slug = ?', ['newsletter']);
    if ($pnPageBefore !== null) {
        NewsletterService::publish($oid);
        Database::execute('UPDATE pages SET is_published = 0 WHERE slug = ?', ['newsletter']);
        $pnSwitchedPage = true;
        SitePages::flush();
        $live = NewsletterService::liveStatus(Newsletter::find($oid) ?? []);
        check('page switched off: a published issue is reported as NOT visible, with the reason', $live['state'] === 'live' && $live['visible'] === false && str_contains($live['label'], 'switched off') && array_filter($live['reasons'], static fn($r) => str_contains($r, 'switched off')) !== []);
        Database::execute('UPDATE pages SET is_published = ? WHERE slug = ?', [(int) $pnPageBefore['is_published'], 'newsletter']);
        $pnSwitchedPage = false;
        SitePages::flush();
        NewsletterService::unpublish($oid);
    }

    // delete releases the file; a duplicate's own copy survives the original's deletion
    $dpath = (string) ($drow['pdf_path'] ?? '');
    $origPath = (string) (Newsletter::find($nid)['pdf_path'] ?? '');
    NewsletterService::delete($nid);
    $pnIssueIds = array_values(array_diff($pnIssueIds, [$nid]));
    check('delete(): the issue\'s PDF file is deleted, the duplicate\'s copy is untouched', ($origPath === '' || !Uploader::privateExists($origPath)) && Uploader::privateExists($dpath));
    NewsletterService::delete($did);
    check('delete(): the duplicate\'s PDF is deleted with it', !Uploader::privateExists($dpath));
    $pnIssueIds = array_values(array_diff($pnIssueIds, [$did]));

    // ------------------------------------------------------------------ schema, registry, menu
    $cols = array_column(Database::all('SHOW COLUMNS FROM partners'), 'Field');
    check('schema: partners has logo_2x, logo_webp, logo_webp_2x, logo_width, logo_height, logo_optimised', !array_diff(['logo_2x', 'logo_webp', 'logo_webp_2x', 'logo_width', 'logo_height', 'logo_optimised'], $cols));
    $ncols = array_column(Database::all('SHOW COLUMNS FROM newsletters'), 'Field');
    check('schema: newsletters has kind, pdf_path, pdf_name, pdf_size, pdf_downloads, pdf_updated_at', !array_diff(['kind', 'pdf_path', 'pdf_name', 'pdf_size', 'pdf_downloads', 'pdf_updated_at'], $ncols));
    $defaultKind = Database::one("SHOW COLUMNS FROM newsletters LIKE 'kind'");
    check('schema: kind defaults to online (every existing issue stays an online issue)', $defaultKind !== null && $defaultKind['Default'] === 'online');
    check('registry: About has the "partners" section (toggle, heading, intro, limit, link to /partners)', PageRegistry::field('about', 'partners.title') !== null && PageRegistry::field('about', 'partners.all_cta_label') !== null && (string) \App\Core\Content::get('about', 'partners.all_cta_url') === '/partners');
    check('registry: Home "newsletter" teaser, Blog "newsletter" CTA, Newsletter "pdf" labels and "empty.when"', PageRegistry::field('home', 'newsletter.title') !== null && PageRegistry::field('blog', 'newsletter.button_url') !== null && PageRegistry::field('newsletter', 'pdf.download_label') !== null && PageRegistry::field('newsletter', 'empty.when') !== null && PageRegistry::field('newsletter', 'email.download_pdf') !== null);
    check('settings: newsletter_pdf_max_mb (25), partners_logo_hover_grey, partners_logo_trim exist with defaults', (string) setting('newsletter_pdf_max_mb') === '25' && (string) setting('partners_logo_hover_grey') === '1' && (string) setting('partners_logo_trim') === '1');
    SitePages::flush();
    Nav::flush();
    $blog = null;
    foreach (Nav::header() as $it) {
        if ($it['slug'] === 'blog') {
            $blog = $it;
        }
    }
    check('menu: Newsletter is a child of Blog in the header menu', $blog !== null && in_array('newsletter', array_column($blog['children'], 'slug'), true), json_encode(array_column($blog['children'] ?? [], 'slug')));
    $about = null;
    foreach (Nav::header() as $it) {
        if ($it['slug'] === 'about') {
            $about = $it;
        }
    }
    check('menu: Partners is still a child of About', $about !== null && in_array('partners', array_column($about['children'], 'slug'), true));
    check('menu: Newsletter is still in the footer menu', in_array('newsletter', array_column(Nav::footer(), 'slug'), true));
    check('seed: the one-time menu upgrade marker exists (a later "hide" in Admin > Menus is never undone)', (int) Database::value("SELECT COUNT(*) FROM settings WHERE setting_key = 'migration_newsletter_nav_v1'", [], 0) === 1);
} finally {
    ImageOptimizer::forceCapabilities(null);
    if ($pnSwitchedPage && $pnPageBefore !== null) {
        Database::execute('UPDATE pages SET is_published = ? WHERE slug = ?', [(int) $pnPageBefore['is_published'], 'newsletter']);
        SitePages::flush();
    }
    foreach ($pnIssueIds as $iid) {
        $r = Newsletter::find($iid);
        if ($r !== null) {
            NewsletterService::delete($iid);
        }
    }
    Database::execute("DELETE FROM newsletters WHERE slug LIKE 'zz-selftest-pdf%'");
    foreach ($pnPartnerIds as $pid) {
        $r = Partner::find($pid);
        if ($r !== null) {
            Partner::delete($pid);
            PartnerLogos::release($r);
        }
    }
    Database::execute("DELETE FROM partners WHERE name LIKE 'zz-selftest%'");
    $rm = static function (string $dir) use (&$rm): void {
        foreach (glob($dir . '/*') ?: [] as $p) {
            is_dir($p) ? $rm($p) : @unlink($p);
        }
        @rmdir($dir);
    };
    $rm($pnTmp);
    $rm(UPLOAD_PATH . '/zz-selftest');
    foreach (glob(STORAGE_PATH . '/private/newsletters/*/*/*.pdf') ?: [] as $leftover) {
        if (filemtime($leftover) > time() - 600 && !Database::value('SELECT COUNT(*) FROM newsletters WHERE pdf_path = ?', ['newsletters/' . substr(str_replace('\\', '/', $leftover), strlen(str_replace('\\', '/', STORAGE_PATH . '/private/newsletters/')))], 0)) {
            @unlink($leftover);        // only files created minutes ago by this run and not used by any issue
        }
    }
}

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
