<?php
/**
 * SHARE IMAGES, SHARE KIT and the public share row (docs/SOCIAL.md section 8). Run alone:
 *     /c/xampp/php/php.exe bin/selftest-social-images.php                       (XAMPP's CLI has no GD: the fallback paths are tested)
 *     /c/xampp/php/php.exe -d extension=gd bin/selftest-social-images.php       (the drawing paths are tested too)
 * or include it from bin/selftest.php (it shares check() and $fail).
 *
 * Covers: the text clean-up (emoji, CJK, control characters, bidi tricks), the five varied issues (very long title, an unbreakable word, accents + emoji, no cover, tall and wide
 * covers, PDF edition) in all three formats (valid JPEG, exact size), tokens (256 bits, only for PUBLIC issues, stable across re-renders, re-render when the source changed), resolve()
 * (unpublished, deleted file, tampered path), the "no GD" fallback, the public image route over HTTP (headers, 304, HEAD, 404s, no cookie), the admin preview / download routes (ability,
 * attachment name, any status) with throw-away accounts, and the public "Share this issue" row (plain links, no third-party script). Everything it creates is removed again
 * (issues "ZZ-IMGS ...", covers, rendered files, users, roles); no real platform is contacted.
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

use App\Core\Auth;
use App\Core\Database;
use App\Core\Roles;
use App\Core\Settings;
use App\Models\Newsletter;
use App\Models\User;
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterService;
use App\Services\Social\SocialCard;
use App\Services\Social\SocialConfig;

echo "Social images: share pictures, Share kit, public share row\n";
$zi = new stdClass();
$zi->ids = [];
$zi->dirs = [];
$zi->users = [];
$zi->roles = [];
$zi->settings = [];
$zi->tag = bin2hex(random_bytes(3));
$zi->coverDir = 'zzimgs-' . $zi->tag;
$zi->gd = SocialCard::available();
echo '  (GD with FreeType + bundled fonts: ' . ($zi->gd ? 'yes' : 'NO - the fallback paths are tested') . ")\n";

$ziCleanup = static function () use ($zi): void {
    foreach ($zi->ids as $id) {
        try {
            Database::execute('DELETE FROM newsletters WHERE id = ?', [$id]);
        } catch (\Throwable) {
        }
        $dir = SocialCard::baseDir() . '/' . $id;
        foreach (glob($dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        foreach (glob($dir . '/.tmp-*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($dir);
    }
    $zi->ids = [];
    foreach (glob(UPLOAD_PATH . '/' . $zi->coverDir . '/*') ?: [] as $f) {
        @unlink($f);
    }
    @rmdir(UPLOAD_PATH . '/' . $zi->coverDir);
    foreach ($zi->users as $id) {
        Database::execute('DELETE FROM users WHERE id = ?', [$id]);
    }
    foreach ($zi->roles as $slug) {
        Database::execute('DELETE FROM roles WHERE slug = ?', [$slug]);
    }
    $zi->users = [];
    $zi->roles = [];
    foreach ($zi->settings as $k => $v) {
        $v === null ? Database::execute('DELETE FROM settings WHERE setting_key = ?', [$k]) : Database::execute('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [$v, $k]);
    }
    $zi->settings = [];
    SocialCard::$forceUnavailable = null;
    Roles::flush();
    Settings::flush();
};
register_shutdown_function($ziCleanup);

/** a cover image of a given size (GD only; else null) */
$ziCover = static function (int $w, int $h, string $name) use ($zi): ?string {
    if (!function_exists('imagecreatetruecolor')) {
        return null;
    }
    @mkdir(UPLOAD_PATH . '/' . $zi->coverDir, 0755, true);
    $im = imagecreatetruecolor($w, $h);
    for ($y = 0; $y < $h; $y += 8) {
        for ($x = 0; $x < $w; $x += 8) {
            imagefilledrectangle($im, $x, $y, $x + 7, $y + 7, imagecolorallocate($im, (int) (128 + 100 * sin($x / 70)), (int) (128 + 90 * sin($y / 90)), (int) (128 + 100 * cos(($x + $y) / 120))));
        }
    }
    imagejpeg($im, UPLOAD_PATH . '/' . $zi->coverDir . '/' . $name, 85);
    imagedestroy($im);
    return $zi->coverDir . '/' . $name;
};
$ziNumber = 80000 + random_int(0, 9000);
/** a (draft) issue with the given fields; the title / cover are set by UPDATE (create() validates cover files and caps what we want to exceed) */
$ziIssue = static function (array $f = []) use ($zi, &$ziNumber): int {
    $c = NewsletterContent::emptyContent();
    $c['featured']['heading'] = 'ZZ images featured';
    $c['featured']['body_html'] = '<p>Body of the zz images test issue.</p>';
    $r = NewsletterService::create(['title' => 'ZZ-IMGS ' . $zi->tag . ' ' . count($zi->ids), 'summary' => 's', 'issue_date' => '2026-10-20', 'content' => $c, 'issue_number' => ++$ziNumber]);
    if (!$r['ok']) {
        throw new \RuntimeException('could not create the test issue: ' . json_encode($r));
    }
    $id = (int) $r['newsletter']['id'];
    $zi->ids[] = $id;
    Database::run('UPDATE newsletters SET title = ?, summary = ?, cover_image = ?, kind = ?, issue_number = ? WHERE id = ?', [mb_substr($f['title'] ?? 'ZZ-IMGS plain', 0, 190), $f['summary'] ?? 'A short summary.', $f['cover'] ?? null, $f['kind'] ?? 'online', array_key_exists('n', $f) ? $f['n'] : (int) $r['newsletter']['issue_number'], $id]);
    return $id;
};
$ziJpeg = static function (?string $bytes, int $w, int $h): bool {
    if ($bytes === null || strlen($bytes) < 2000 || substr($bytes, 0, 2) !== "\xFF\xD8") {
        return false;
    }
    $i = @getimagesizefromstring($bytes);
    return is_array($i) && $i[0] === $w && $i[1] === $h && $i['mime'] === 'image/jpeg';
};
$sizes = ['square' => [1080, 1080], 'portrait' => [1080, 1920], 'landscape' => [1200, 630]];

// ----------------------------------------------------------------------------------------------------------------------------------- text clean-up
check('clean(): accents and typographic punctuation are kept', SocialCard::clean('Café Müller’s “Naïve” Žižek – Łódź') === 'Café Müller’s “Naïve” Žižek – Łódź');
check('clean(): emoji, CJK and symbols the fonts lack are dropped (never drawn as boxes), white space folded', SocialCard::clean("Hello \u{1F30D} world \u{1F389}  \u{4F60}\u{597D}\n\u{2600}done") === 'Hello world done');
check('clean(): control characters, zero-width characters and bidi overrides never reach the picture', SocialCard::clean("A\x00B\x07C\u{200B}D\u{202E}E\u{2066}F") === 'ABCDEF');
check('clean(): tags are stripped, invalid UTF-8 repaired, an all-emoji title becomes empty', SocialCard::clean('<b>Bold</b> <script>x</script>') !== '' && !str_contains(SocialCard::clean('<b>Bold</b> <script>x</script>'), '<') && SocialCard::clean("\u{1F600}\u{1F600}") === '' && is_string(SocialCard::clean("bad \xC3\x28 utf8")));

// ----------------------------------------------------------------------------------------------------------------------------------- designer
$ziKeys = ['social_card_bg', 'social_card_accent', 'social_card_tagline', 'social_card_show_logo'];
foreach ($ziKeys as $k) {
    $row = Database::one('SELECT setting_value FROM settings WHERE setting_key = ?', [$k]);
    $zi->settings[$k] = $row === null ? null : (string) $row['setting_value'];
}
/** change a setting WITHOUT touching its "owner edited" marker (created_at), so the seed's pristine-only refresh is not disturbed */
$ziSet = static function (string $k, string $v): void {
    if (Database::one('SELECT id FROM settings WHERE setting_key = ?', [$k]) === null) {
        Database::execute('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)', [$k, $v]);
    } else {
        Database::execute('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [$v, $k]);
    }
    SocialConfig::flush();
};
$ziSet('social_card_bg', 'not-a-colour');
check('designer: an invalid colour falls back to the brand default', SocialCard::designer()['bg'] === '#2C3950');
$ziSet('social_card_bg', '#112233');
$ziSet('social_card_tagline', 'Custom <b>tagline</b>');
$d = SocialCard::designer();
check('designer: a valid colour is used (upper-cased); a tagline is taken as typed (cleaned when drawn)', $d['bg'] === '#112233' && str_contains($d['tagline'], 'Custom'));
$ziSet('social_card_bg', $zi->settings['social_card_bg'] ?? '#2C3950');
$ziSet('social_card_tagline', $zi->settings['social_card_tagline'] ?? '');

// ----------------------------------------------------------------------------------------------------------------------------------- five varied issues, three formats
$tall = $ziCover(800, 2000, 'tall.jpg');
$wide = $ziCover(3000, 800, 'wide.jpg');
$photo = $ziCover(1600, 1000, 'photo.jpg');
$cases = [
    'normal photo cover' => ['title' => 'Igniting Light in Karamoja: how 400 girls returned to school after the dry season', 'summary' => 'Our January newsletter covers the school re-entry campaign, new solar labs and a thank-you to every donor.', 'cover' => $photo],
    'very long title, an unbreakable word, no cover' => ['title' => 'Supercalifragilisticexpialidocious-extraordinarily-long-title-without-any-spaces-at-all and then a really very extremely long sentence that keeps on going and going to test the shrink to fit and the ellipsis behaviour of the block', 'summary' => str_repeat('A very long summary sentence that must be cut. ', 12), 'cover' => null],
    'accents + emoji + CJK, tall cover, no number' => ['title' => 'Café Müller’s “Naïve” Žižek & Łódź — 🌍 Celebrating 🎉 Ünïcödé! 你好 ☀', 'summary' => 'Accents ÀÉÎÕÜ ñ ç, emoji 😀🔥 and CJK 日本語.', 'cover' => $tall, 'n' => null],
    'wide cover, empty summary' => ['title' => 'Wide cover banner issue', 'summary' => '', 'cover' => $wide],
    'PDF edition, no cover' => ['title' => 'Annual Report 2025 (PDF edition)', 'summary' => 'Download the full annual report.', 'cover' => null, 'kind' => 'pdf'],
    'only emoji as title' => ['title' => '🎉🎉🎉', 'summary' => '', 'cover' => null],
];
$ziRows = [];
foreach ($cases as $label => $f) {
    $id = $ziIssue($f);
    $row = Newsletter::find($id) ?? [];
    $ziRows[$label] = $row;
    if (!$zi->gd) {
        continue;
    }
    $all = true;
    $why = '';
    foreach ($sizes as $v => [$w, $h]) {
        $t0 = microtime(true);
        $b = SocialCard::draw($row, $v);
        if (!$ziJpeg($b, $w, $h)) {
            $all = false;
            $why .= " {$v}:" . ($b === null ? 'null' : 'bad');
        }
        if (microtime(true) - $t0 > 8.0) {
            $all = false;
            $why .= " {$v}:slow";
        }
    }
    check("draw: {$label}: square 1080x1080, portrait 1080x1920 and landscape 1200x630 are valid JPEGs, each in under 8 s", $all, $why);
}
if (!$zi->gd) {
    check('without GD: draw() returns null and never throws, ensure() returns null', SocialCard::draw($ziRows['PDF edition, no cover'], 'square') === null && SocialCard::ensure((int) $ziRows['PDF edition, no cover']['id'], 'square') === null);
}
$ziBadVariant = SocialCard::draw($ziRows['PDF edition, no cover'], 'huge');
check('an unknown variant is refused (null), not drawn', $ziBadVariant === null && SocialCard::ensure((int) $ziRows['PDF edition, no cover']['id'], '../etc') === null);

// ----------------------------------------------------------------------------------------------------------------------------------- drafts, publishing, tokens, re-render
$draft = (int) $ziRows['normal photo cover']['id'];
check('a draft never gets a token or a stored image (ensure() = null, no row); publicUrl() falls back to the cover photo', SocialCard::ensure($draft, 'square') === null && SocialCard::count($draft) === 0 && str_contains((string) SocialCard::publicUrl($draft, 'square'), $zi->gd ? $zi->coverDir : 'logo'));
check('adminImage(): a draft still gets a picture for the Share kit (drawn on the fly, or the cover without GD), never stored', (function () use ($zi, $draft): bool {
    $im = SocialCard::adminImage(Newsletter::find($draft) ?? [], 'portrait');
    return $im !== null && strlen($im['bytes']) > 1000 && str_starts_with($im['mime'], 'image/') && $im['drawn'] === $zi->gd && SocialCard::count($draft) === 0;
})());
check('adminImage(): an unknown variant is null', SocialCard::adminImage(Newsletter::find($draft) ?? [], 'x') === null);
$r = NewsletterService::publish($draft, null);
check('the issue is published (no social channel is configured, so nothing else happens)', $r['ok'] && (Newsletter::find($draft)['status'] ?? '') === 'published');
$made = [];
foreach ($sizes as $v => $_) {
    $made[$v] = SocialCard::ensure($draft, $v);
}
if ($zi->gd) {
    check('ensure(): a public issue gets a token (64 hex = 256 bits) and a stored file per format, all different', count(array_filter($made, static fn($m) => is_array($m) && preg_match('/^[a-f0-9]{64}$/', $m['token']) === 1)) === 3 && count(array_unique(array_column($made, 'token'))) === 3 && SocialCard::count($draft) === 3);
    check('the stored files live OUTSIDE the web root under storage/private/social/<id>/ and the address is the token route', is_file(SocialCard::baseDir() . '/' . $draft . '/square.jpg') && str_contains($made['square']['url'], '/social/image/' . $made['square']['token'] . '.jpg') && str_starts_with(realpath(SocialCard::baseDir()) ?: '', realpath(STORAGE_PATH) ?: '#'));
    check('the stored picture is a valid JPEG of the right size', $ziJpeg((string) file_get_contents(SocialCard::baseDir() . '/' . $draft . '/portrait.jpg'), 1080, 1920));
    $mt = filemtime(SocialCard::baseDir() . '/' . $draft . '/square.jpg');
    $again = SocialCard::ensure($draft, 'square');
    check('ensure() again changes nothing (same token, the file is not redrawn)', $again !== null && $again['token'] === $made['square']['token'] && filemtime(SocialCard::baseDir() . '/' . $draft . '/square.jpg') === $mt);
    $h1 = (string) Database::value('SELECT src_hash FROM social_images WHERE token = ?', [$made['square']['token']], '');
    Database::run('UPDATE newsletters SET title = ? WHERE id = ?', ['ZZ-IMGS a changed title', $draft]);
    $re = SocialCard::ensure($draft, 'square');
    $h2 = (string) Database::value('SELECT src_hash FROM social_images WHERE token = ?', [$made['square']['token']], '');
    check('a changed title redraws the picture but KEEPS its token (the address a platform already holds stays valid)', $re !== null && $re['token'] === $made['square']['token'] && $h1 !== $h2 && $h1 !== '' && SocialCard::count($draft) === 3);
    $h3 = (string) Database::value('SELECT src_hash FROM social_images WHERE token = ?', [$made['square']['token']], '');
    $ziSet('social_card_accent', '#FF00AA');
    SocialCard::ensure($draft, 'square');
    check('a changed designer colour redraws too', (string) Database::value('SELECT src_hash FROM social_images WHERE token = ?', [$made['square']['token']], '') !== $h3);
    $ziSet('social_card_accent', $zi->settings['social_card_accent'] ?? '#D6A151');
    $other = $ziIssue(['title' => 'ZZ-IMGS second', 'cover' => null]);
    NewsletterService::publish($other, null);
    $om = SocialCard::ensure($other, 'square');
    check('another issue gets its own token', $om !== null && $om['token'] !== $made['square']['token']);
    check('publicUrl() of a public issue is the token address (not the cover)', SocialCard::publicUrl($draft, 'square') === $made['square']['url'] && str_contains((string) SocialCard::publicUrl($draft, 'landscape'), '/social/image/'));

    // resolve()
    $tok = $made['square']['token'];
    $res = SocialCard::resolve($tok);
    check('resolve(): file, mime image/jpeg, mtime and a strong ETag for a public issue', is_array($res) && is_file($res['file']) && $res['mime'] === 'image/jpeg' && $res['mtime'] > 0 && preg_match('/^"[a-f0-9]{32}"$/', $res['etag']) === 1);
    check('resolve(): unknown token, wrong length, upper case, traversal, empty = null', SocialCard::resolve(str_repeat('a', 64)) === null && SocialCard::resolve('abc') === null && SocialCard::resolve(strtoupper($tok)) === null && SocialCard::resolve('../' . substr($tok, 3)) === null && SocialCard::resolve('') === null);
    Database::run("UPDATE newsletters SET status = 'draft' WHERE id = ?", [$draft]);
    check('resolve(): once the issue is unpublished the token answers null (404) at once', SocialCard::resolve($tok) === null);
    Database::run("UPDATE newsletters SET status = 'published' WHERE id = ?", [$draft]);
    check('resolve(): published again, the same token works again', SocialCard::resolve($tok) !== null);
    Database::run("UPDATE newsletters SET status = 'archived' WHERE id = ?", [$draft]);
    check('resolve(): an archived issue is not served', SocialCard::resolve($tok) === null);
    Database::run("UPDATE newsletters SET status = 'published' WHERE id = ?", [$draft]);
    @unlink(SocialCard::baseDir() . '/' . $draft . '/square.jpg');
    $res2 = SocialCard::resolve($tok);
    check('resolve(): a lost file is redrawn on demand and the token stays', $res2 !== null && is_file($res2['file']));
    Database::run('UPDATE social_images SET path = ? WHERE token = ?', ['../../../.env', $tok]);
    check('resolve(): a tampered path in the table never leaves the store (null)', SocialCard::resolve($tok) === null);
    Database::run('UPDATE social_images SET path = ? WHERE token = ?', [$draft . '/square.jpg', $tok]);
    check('resolve(): with the path repaired it works again', SocialCard::resolve($tok) !== null);
    Database::run("UPDATE newsletters SET status = 'scheduled', publish_at = DATE_ADD(NOW(), INTERVAL 1 DAY) WHERE id = ?", [$draft]);
    check('resolve(): a scheduled issue whose time has not come is not served', SocialCard::resolve($tok) === null);
    Database::run("UPDATE newsletters SET status = 'published', publish_at = NULL WHERE id = ?", [$draft]);
    Database::execute('DELETE FROM newsletters WHERE id = ?', [$other]);
    check('deleting an issue removes its image rows (cascade)', SocialCard::count($other) === 0);

    // no GD (simulated)
    SocialCard::$forceUnavailable = true;
    check('no GD (simulated): available() false, ensure() null, publicUrl() = the cover photo, adminImage() = the cover file as it is', SocialCard::available() === false && SocialCard::ensure($draft, 'square') === null && str_contains((string) SocialCard::publicUrl($draft, 'portrait') ?: '', $zi->coverDir ?: 'x') && (function () use ($draft): bool {
        $im = SocialCard::adminImage(Newsletter::find($draft) ?? [], 'square');
        return $im !== null && $im['drawn'] === false && $im['ext'] === 'jpg';
    })());
    $nc = $ziIssue(['title' => 'ZZ-IMGS no cover', 'cover' => null]);
    $im = SocialCard::adminImage(Newsletter::find($nc) ?? [], 'square');
    check('no GD (simulated) and no cover: the bundled logo is offered', $im !== null && $im['drawn'] === false && in_array($im['ext'], ['png', 'jpg', 'webp'], true) && str_contains((string) SocialCard::fallbackUrl(Newsletter::find($nc) ?? []), 'logo'));
    SocialCard::$forceUnavailable = null;
}
$nc2 = $ziIssue(['title' => 'ZZ-IMGS fallback', 'cover' => null]);
$fb = SocialCard::fallbackUrl(Newsletter::find($nc2) ?? []);
check('fallbackUrl(): an issue without cover gets the absolute logo address', is_string($fb) && preg_match('~^https?://~', $fb) === 1 && str_contains($fb, 'logo'));
check('fallbackFile(): remote or traversal cover paths are never read (the logo is used)', (function () use ($nc2): bool {
    foreach (['https://evil.example/x.jpg', '../../.env', '/etc/passwd', "a\0b.jpg", 'assets/../../.env'] as $p) {
        $row = (Newsletter::find($nc2) ?? []) + [];
        $row['cover_image'] = $p;
        $f = SocialCard::fallbackFile($row);
        if ($f === null || !str_contains($f['file'], 'logo')) {
            return false;
        }
    }
    return true;
})());

// ----------------------------------------------------------------------------------------------------------------------------------- HTTP: public route, admin routes, public share row
$ziBase = 'http://localhost/' . basename(dirname(__DIR__)) . '/';
$ziHttp = static function (string $method, string $path, array $o = []) use ($ziBase): array {
    $ch = curl_init(str_starts_with($path, 'http') ? $path : $ziBase . ltrim($path, '/'));
    $hdr = [];
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $o['headers'] ?? [],
        CURLOPT_HEADERFUNCTION => static function ($c, string $line) use (&$hdr): int {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $hdr[strtolower(trim($k))][] = trim($v);
            }
            return strlen($line);
        }]);
    if ($method === 'HEAD') {
        curl_setopt($ch, CURLOPT_NOBODY, true);
    }
    if (isset($o['jar'])) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $o['jar']);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $o['jar']);
    }
    if (isset($o['post'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($o['post']));
    }
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'headers' => $hdr];
};
$ziSite = function_exists('curl_init') && ($ziHttp('GET', 'newsletter')['code'] ?? 0) === 200;
if (!$ziSite) {
    echo "  (skipped: the site does not answer at {$ziBase})\n";
} else {
    $slug = (string) Database::value('SELECT slug FROM newsletters WHERE id = ?', [$draft], '');
    $page = $ziHttp('GET', 'newsletter/' . $slug);
    check('public issue page: the "Share this issue" row has Facebook, X, LinkedIn, WhatsApp, Telegram, e-mail links and a copy button, all plain links', $page['code'] === 200 && substr_count($page['body'], 'class="nl-share__link nl-share__link--') === 6 && str_contains($page['body'], 'nl-share__copy') && str_contains($page['body'], 'https://www.facebook.com/sharer/sharer.php?u=') && str_contains($page['body'], 'https://t.me/share/url?url=') && str_contains($page['body'], 'mailto:?subject='));
    check('public share row: external links open in a new tab with rel noopener noreferrer; the e-mail link does not; no third-party script is loaded', preg_match_all('~<a class="nl-share__link[^>]*target="_blank" rel="noopener noreferrer"~', $page['body']) === 5 && !preg_match('~<script[^>]+src="https?://(?!localhost)[^"]*(facebook|twitter|linkedin|addthis|sharethis|platform)~i', $page['body']));
    check('public share row: the issue title and address are URL-encoded inside the links', str_contains($page['body'], rawurlencode($ziBase . 'newsletter/' . $slug)) || str_contains($page['body'], rawurlencode('/newsletter/' . $slug)));
    $draftPage = $ziHttp('GET', 'newsletter/' . (string) Database::value('SELECT slug FROM newsletters WHERE id = ?', [(int) $ziRows['wide cover, empty summary']['id']], ''));
    check('a draft issue page is 404 (the share row is never shown for it)', $draftPage['code'] === 404);

    if ($zi->gd && isset($made['square']['token'])) {
        $tok = $made['square']['token'];
        $img = $ziHttp('GET', 'social/image/' . $tok . '.jpg');
        $h = $img['headers'];
        check('public image route: 200 image/jpeg with the right bytes', $img['code'] === 200 && ($h['content-type'][0] ?? '') === 'image/jpeg' && $ziJpeg($img['body'], 1080, 1080));
        check('public image route: nosniff, Cache-Control public max-age=3600, ETag, Last-Modified, noindex, and NO cookie', ($h['x-content-type-options'][0] ?? '') === 'nosniff' && ($h['cache-control'][0] ?? '') === 'public, max-age=3600' && isset($h['etag']) && isset($h['last-modified']) && !isset($h['set-cookie']) && str_contains($h['x-robots-tag'][0] ?? '', 'noindex'));
        $etag = $h['etag'][0] ?? '';
        check('public image route: If-None-Match answers 304 with no body; a different ETag gets the picture', $ziHttp('GET', 'social/image/' . $tok . '.jpg', ['headers' => ['If-None-Match: ' . $etag]])['code'] === 304 && $ziHttp('GET', 'social/image/' . $tok . '.jpg', ['headers' => ['If-None-Match: "nope"']])['code'] === 200);
        $head = $ziHttp('HEAD', 'social/image/' . $tok . '.jpg');
        check('public image route: HEAD gives the headers and Content-Length', $head['code'] === 200 && (int) ($head['headers']['content-length'][0] ?? 0) === strlen($img['body']));
        check('public image route: unknown token, short token, traversal and a directory all answer 404 (no listing)', $ziHttp('GET', 'social/image/' . str_repeat('b', 64) . '.jpg')['code'] === 404 && $ziHttp('GET', 'social/image/abc.jpg')['code'] === 404 && $ziHttp('GET', 'social/image/..%2F..%2F.env.jpg')['code'] === 404 && $ziHttp('GET', 'social/image/')['code'] === 404 && $ziHttp('GET', 'social/image')['code'] === 404);
        check('the private store is not reachable by file path (403/404)', in_array($ziHttp('GET', 'storage/private/social/' . $draft . '/portrait.jpg')['code'], [403, 404], true) && in_array($ziHttp('GET', 'storage/private/social/')['code'], [403, 404], true));
        Database::run("UPDATE newsletters SET status = 'draft' WHERE id = ?", [$draft]);
        check('public image route: the moment the issue is unpublished the picture is 404', $ziHttp('GET', 'social/image/' . $tok . '.jpg')['code'] === 404);
        Database::run("UPDATE newsletters SET status = 'published' WHERE id = ?", [$draft]);
    }

    // READABILITY on a LIGHT cover (the audit's failing case: gold on light grey was 1.4:1, the kicker 1.05:1, white 2.6:1): every fixed text zone reaches 4.5:1 (measured on the pixels
    // around the glyphs: the darker half of the zone, its 90th percentile)
    if ($zi->gd) {
        $lightRel = 'zz-imgs-light-' . $zi->tag . '.jpg';
        $lim = imagecreatetruecolor(508, 658);
        imagefill($lim, 0, 0, imagecolorallocate($lim, 226, 228, 230));
        imagefilledellipse($lim, 380, 120, 300, 300, imagecolorallocate($lim, 245, 245, 246));
        imagejpeg($lim, UPLOAD_PATH . '/' . $lightRel, 92);
        imagedestroy($lim);
        $lightRow = ['cover_image' => $lightRel, 'title' => 'A readable title on a light photograph', 'issue_number' => 904] + (array) \App\Models\Newsletter::find($draft);
        $bytes = \App\Services\Social\SocialCard::draw($lightRow, 'square');
        @unlink(UPLOAD_PATH . '/' . $lightRel);
        $lumOf = static function (int $r, int $g, int $b): float {
            $f = static fn(int $v): float => ($v / 255) <= 0.03928 ? ($v / 255) / 12.92 : ((($v / 255) + 0.055) / 1.055) ** 2.4;
            return 0.2126 * $f($r) + 0.7152 * $f($g) + 0.0722 * $f($b);
        };
        $im = is_string($bytes) ? imagecreatefromstring($bytes) : false;
        $ratios = [];
        if ($im !== false) {
            $zones = ['issue label (gold)' => [720, 84, 1010, 122, [0xD6, 0xA1, 0x51]], 'date (white)' => [720, 130, 1010, 170, [255, 255, 255]], 'address (gold)' => [76, 975, 260, 1010, [0xD6, 0xA1, 0x51]]];
            foreach ($zones as $name => [$x0, $y0, $x1, $y1, $col]) {
                $ls = [];
                for ($y = $y0; $y <= $y1; $y += 2) {
                    for ($x = $x0; $x <= $x1; $x += 2) {
                        $p = imagecolorat($im, $x, $y);
                        $ls[] = $lumOf(($p >> 16) & 255, ($p >> 8) & 255, $p & 255);
                    }
                }
                sort($ls);
                $half = array_slice($ls, 0, intdiv(count($ls), 2));
                $bg = $half[(int) floor(count($half) * 0.9) - 1];
                $lt = $lumOf($col[0], $col[1], $col[2]);
                $ratios[$name] = round((max($lt, $bg) + 0.05) / (min($lt, $bg) + 0.05), 2);
            }
            imagedestroy($im);
        }
        check('a LIGHT cover: the issue label, the date and the address reach 4.5:1 (a dark veil is laid only where the photo is too light)', $ratios !== [] && min($ratios) >= 4.4, json_encode($ratios));
    }

    // admin routes with throw-away accounts
    $ziAcc = ['share' => ['zz-imgs-share', ['dashboard.view', 'social.share']], 'both' => ['zz-imgs-both', ['dashboard.view', 'social.share', 'newsletter.manage']], 'none' => ['zz-imgs-none', ['dashboard.view', 'newsletter.manage']]];
    $ziPw = [];
    $ziJar = [];
    foreach ($ziAcc as $k => [$role, $abilities]) {
        Database::insert('roles', ['slug' => $role, 'name' => 'ZZ ' . $k, 'description' => '', 'abilities' => json_encode($abilities), 'is_system' => 0]);
        $zi->roles[] = $role;
        $ziPw[$k] = bin2hex(random_bytes(9)) . 'Aa1';
        $zi->users[] = User::create(['name' => 'ZZ imgs ' . $k, 'email' => 'zz-imgs-' . $k . '@example.test', 'password_hash' => Auth::hashPassword($ziPw[$k]), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
    }
    Roles::flush();
    Database::execute('DELETE FROM rate_limits');
    $field = static fn(string $html, string $n): string => preg_match('/name="' . preg_quote($n, '/') . '" value="([^"]*)"/', $html, $m) === 1 ? html_entity_decode($m[1]) : '';
    foreach (array_keys($ziAcc) as $k) {
        $jar = sys_get_temp_dir() . '/zz-imgs-' . $zi->tag . '-' . $k . '.txt';
        $lp = $ziHttp('GET', 'admin/login', ['jar' => $jar]);
        $ziHttp('POST', 'admin/login', ['jar' => $jar, 'post' => ['_token' => $field($lp['body'], '_token'), 'email' => 'zz-imgs-' . $k . '@example.test', 'password' => ($ziPw[$k])]]);
        $ziJar[$k] = $jar;
    }
    register_shutdown_function(static function () use (&$ziJar): void {
        foreach ($ziJar as $j) {
            @unlink($j);
        }
    });
    $anonKit = $ziHttp('GET', 'admin/social/kit/' . $draft);
    $anonPrev = $ziHttp('GET', 'admin/social/preview/' . $draft . '/square');
    $anonDl = $ziHttp('GET', 'admin/social/kit/' . $draft . '/download/square');
    check('not signed in: the kit, the preview and the download go to the login page', $anonKit['code'] === 302 && $anonPrev['code'] === 302 && $anonDl['code'] === 302 && str_contains($anonPrev['headers']['location'][0] ?? '', 'admin/login'));
    check('without social.share: kit, preview and download are 403', $ziHttp('GET', 'admin/social/kit/' . $draft, ['jar' => $ziJar['none']])['code'] === 403 && $ziHttp('GET', 'admin/social/preview/' . $draft . '/square', ['jar' => $ziJar['none']])['code'] === 403 && $ziHttp('GET', 'admin/social/kit/' . $draft . '/download/square', ['jar' => $ziJar['none']])['code'] === 403);
    $kit = $ziHttp('GET', 'admin/social/kit/' . $draft, ['jar' => $ziJar['share']]);
    $ziServerGd = !str_contains($kit['body'], 'NO square, portrait or landscape');           // the web server's PHP may differ from the CLI one: without GD the kit offers ONE picture and says so
    $ziPics = $ziServerGd ? 3 : 1;
    check('with social.share: the kit opens with ' . $ziPics . ' picture' . ($ziPics === 1 ? ' (this web server has no GD: the cover photo, said plainly)' : 's') . ', Download buttons, a caption per platform incl. TikTok, the link and hashtags', $kit['code'] === 200 && substr_count($kit['body'], 'class="skit-pic ') === $ziPics && substr_count($kit['body'], 'class="btn btn--primary btn--sm" href="') >= $ziPics && str_contains($kit['body'], 'id="skit-cap-tiktok"') && str_contains($kit['body'], 'id="skit-cap-instagram"') && str_contains($kit['body'], 'id="skit-url"') && str_contains($kit['body'], 'share-kit.js'));
    check('the kit: no secret, no token field of a channel, no inline script', !preg_match('~(page_token|bot_token|api_secret|access_secret)~i', $kit['body']) && !preg_match('~<script(?![^>]*\bsrc=)[^>]*>~i', $kit['body']) && !preg_match('~\son(click|load|error)=~i', $kit['body']));
    foreach (['square', 'portrait', 'landscape'] as $v) {
        $p = $ziHttp('GET', 'admin/social/preview/' . $draft . '/' . $v, ['jar' => $ziJar['share']]);
        $dl = $ziHttp('GET', 'admin/social/kit/' . $draft . '/download/' . $v, ['jar' => $ziJar['share']]);
        $issueNo = (int) Database::value('SELECT issue_number FROM newsletters WHERE id = ?', [$draft], 0);
        check("preview + download of the {$v} picture: image, inline vs attachment, name lumina-issue-<n>-{$v}.<ext>, never cached", $p['code'] === 200 && $dl['code'] === 200 && str_starts_with($p['headers']['content-type'][0] ?? '', 'image/') && str_starts_with($p['headers']['content-disposition'][0] ?? '', 'inline') && str_starts_with($dl['headers']['content-disposition'][0] ?? '', 'attachment') && preg_match('~filename="lumina-issue-' . $issueNo . '-' . $v . '\.(jpg|png|webp)"~', $dl['headers']['content-disposition'][0] ?? '') === 1 && str_contains($dl['headers']['cache-control'][0] ?? '', 'no-store'));
    }
    $dr = (int) $ziRows['wide cover, empty summary']['id'];
    check('a DRAFT issue still gets its kit, preview and download for somebody who may edit newsletters too (social.share + newsletter.manage)', $ziHttp('GET', 'admin/social/kit/' . $dr, ['jar' => $ziJar['both']])['code'] === 200 && $ziHttp('GET', 'admin/social/preview/' . $dr . '/portrait', ['jar' => $ziJar['both']])['code'] === 200 && $ziHttp('GET', 'admin/social/kit/' . $dr . '/download/square', ['jar' => $ziJar['both']])['code'] === 200);
    check('...but social.share ALONE gets a 404 for a draft (no unpublished title, summary or picture)', $ziHttp('GET', 'admin/social/kit/' . $dr, ['jar' => $ziJar['share']])['code'] === 404 && $ziHttp('GET', 'admin/social/preview/' . $dr . '/portrait', ['jar' => $ziJar['share']])['code'] === 404 && $ziHttp('GET', 'admin/social/kit/' . $dr . '/download/square', ['jar' => $ziJar['share']])['code'] === 404);
    check('a draft kit says the links only work once public and offers no public picture address', str_contains($ziHttp('GET', 'admin/social/kit/' . $dr, ['jar' => $ziJar['both']])['body'], 'not public yet') && !str_contains($ziHttp('GET', 'admin/social/kit/' . $dr, ['jar' => $ziJar['both']])['body'], 'data-skit-copy-value'));
    check('unknown variant, unknown issue: 404', $ziHttp('GET', 'admin/social/preview/' . $draft . '/bogus', ['jar' => $ziJar['share']])['code'] === 404 && $ziHttp('GET', 'admin/social/kit/' . $draft . '/download/bogus', ['jar' => $ziJar['share']])['code'] === 404 && $ziHttp('GET', 'admin/social/kit/99999999', ['jar' => $ziJar['share']])['code'] === 404 && $ziHttp('GET', 'admin/social/preview/99999999/square', ['jar' => $ziJar['share']])['code'] === 404);
    check('the preview / download routes are not reachable with a POST (no state change)', in_array($ziHttp('POST', 'admin/social/preview/' . $draft . '/square', ['jar' => $ziJar['share']])['code'], [404, 405, 419, 403], true));
}

$ziCleanup();
check('cleanup: no test issue, cover or rendered file is left', (int) Database::value('SELECT COUNT(*) FROM newsletters WHERE title LIKE ?', ['ZZ-IMGS%'], 0) === 0 && !is_dir(UPLOAD_PATH . '/' . $zi->coverDir));

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
