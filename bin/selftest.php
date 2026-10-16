<?php
/**
 * Foundation smoke test (CLI):  /c/xampp/php/php.exe bin/selftest.php
 * Exercises Sanitizer, Validator, Slug, CsvExport, Mailer, Honeypot, RateLimit, the page registry, Content blocks (save / default /
 * reset / section toggle / repeater), SitePages + Nav (publish / unpublish / custom page) against the local database.
 * It cleans up after itself. Exit code 1 if any check fails. Safe to run any time (local database only).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Content;
use App\Core\Csrf;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Honeypot;
use App\Core\ImageScrubber;
use App\Core\Mailer;
use App\Core\Nav;
use App\Core\PageRegistry;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Sanitizer;
use App\Core\SitePages;
use App\Core\Slug;
use App\Core\Validator;
use App\Models\Page;

if (Config::get('app.env') !== 'local') {
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

echo "Sanitizer\n";
$dirty = '<p onclick="x()">Hi <script>alert(1)</script><a href="javascript:alert(1)">bad</a> <a href="https://example.org/x">ok</a></p><iframe src="//evil"></iframe><img src="//evil/x.png" alt="e"><img src="/uploads/2026/01/a.jpg" alt="a" style="x:y"><h1>T</h1><div>d</div><b>b</b>';
$clean = Sanitizer::html($dirty);
check('strips script/iframe/on*/javascript:', !preg_match('/script|iframe|onclick|javascript:|style=|\/\/evil/i', $clean), $clean);
check('keeps allowed markup', str_contains($clean, '<strong>b</strong>') && str_contains($clean, '<h2>T</h2>') && str_contains($clean, 'rel="noopener noreferrer"') && str_contains($clean, 'src="/uploads/2026/01/a.jpg"'), $clean);
check('idempotent', Sanitizer::html($clean) === $clean);
check('url(): rejects data:/javascript:', Sanitizer::url('data:text/html,x') === '' && Sanitizer::url("java\nscript:alert(1)") === '' && Sanitizer::url('//evil.com') === '');
check('url(): accepts http/mailto/tel/path', Sanitizer::url('https://a.org') !== '' && Sanitizer::url('mailto:a@b.org') !== '' && Sanitizer::url('/about') === '/about' && Sanitizer::url('#x') === '#x');

echo "Validator\n";
$v = Validator::make(['email' => 'nope', 'name' => '', 'type' => 'x', 'url' => 'ftp://x', 'n' => '5'], [
    'email' => 'required|email', 'name' => 'required|max:5', 'type' => 'required|in:a,b', 'url' => 'nullable|url', 'n' => 'numeric|min:10', 'opt' => 'nullable|email',
]);
check('collects first error per field', $v->fails() && isset($v->errors()['email'], $v->errors()['name'], $v->errors()['type'], $v->errors()['url'], $v->errors()['n']) && !isset($v->errors()['opt']), json_encode($v->errors()));
check('passes valid input', Validator::make(['email' => 'a@b.org', 'd' => '2026-02-28'], ['email' => 'required|email', 'd' => 'date'])->passes());
check('rejects impossible date', Validator::make(['d' => '2026-02-30'], ['d' => 'date'])->fails());
$arr = Validator::make(['name' => ['x']], ['name' => 'required|max:10']);
check('array posted for a scalar field is a validation error, not "Array"', $arr->fails() && isset($arr->errors()['name']));

echo "Request (client IP, host, encoding, CSRF)\n";
$proxied = static function (array $s, array $trusted): string {
    Config::set('trusted_proxies', $trusted);
    return (new Request($s + ['REQUEST_URI' => '/', 'REQUEST_METHOD' => 'GET'], [], [], []))->ip();
};
check('X-Forwarded-For ignored unless the peer is a trusted proxy', $proxied(['REMOTE_ADDR' => '203.0.113.5', 'HTTP_X_FORWARDED_FOR' => '1.2.3.4'], ['127.0.0.1']) === '203.0.113.5');
check('client IP = first untrusted hop from the right (spoofed left entry ignored)', $proxied(['REMOTE_ADDR' => '127.0.0.1', 'HTTP_X_FORWARDED_FOR' => '10.9.8.1, 203.0.113.7'], ['127.0.0.1']) === '203.0.113.7');
check('trusted proxies may be CIDR ranges', $proxied(['REMOTE_ADDR' => '10.5.5.5', 'HTTP_X_FORWARDED_FOR' => '198.51.100.9'], ['10.0.0.0/8']) === '198.51.100.9');
Config::set('trusted_proxies', []);
check('malformed Host header is replaced', (new Request(['HTTP_HOST' => "evil.com\r\nX: y", 'REQUEST_URI' => '/'], [], [], []))->host() === 'localhost');
$bad = new Request(['REQUEST_URI' => '/'], ['q' => "a\xEDb"], ['name' => ["x\xFF"]], []);
check('invalid UTF-8 in input is repaired', mb_check_encoding((string) $bad->query('q'), 'UTF-8') && mb_check_encoding((string) $bad->post('name')[0], 'UTF-8'));
$tok = Csrf::token();
check('CSRF token verifies; a forged one does not', Csrf::verify(new Request(['REQUEST_URI' => '/contact', 'REQUEST_METHOD' => 'POST'], [], ['_token' => $tok], []))
    && !Csrf::verify(new Request(['REQUEST_URI' => '/contact', 'REQUEST_METHOD' => 'POST'], [], ['_token' => 's.' . time() . '.' . str_repeat('0', 64)], [])));

echo "Uploads (metadata scrubber)\n";
$jpeg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/4QCsRXhpZgAATU0AKgAAAAgAAwEPAAIAAAALAAAAMgESAAMAAAABAAYAAIglAAQAAAABAAAAPgAAAABTZWNUZXN0Q2FtAAAABAABAAIAAAACTgAAAAACAAUAAAADAAAAdAADAAIAAAACRQAAAAAEAAUAAAADAAAAjAAAAAAAAAACAAAAAQAAAC4AAAABAAAAAAAAAAEAAAAgAAAAAQAAABEAAAABAAAAAAAAAAH/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAAeACgDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwCOiiivmj7AKKKKACiiigAooooAKKKKACiiigD/2Q==', true);   // 40x30 JPEG with Make=SecTestCam, GPS position and Orientation=6
$scrubbed = $jpeg === false ? null : ImageScrubber::scrub($jpeg, 'image/jpeg');
check('JPEG: camera + GPS data removed, orientation kept, still a valid image', $scrubbed !== null && str_contains($jpeg, 'SecTestCam') && !str_contains($scrubbed, 'SecTestCam')
    && strlen($scrubbed) < strlen($jpeg) && (@getimagesizefromstring($scrubbed)[0] ?? 0) === 40 && str_contains($scrubbed, 'Exif'));
check('non-images are refused by the scrubber', ImageScrubber::scrub('<?php echo 1;', 'image/jpeg') === null && ImageScrubber::scrub('GIF89a', 'image/png') === null);
echo '  info ' . (extension_loaded('gd') ? 'GD loaded: photos are resized and re-encoded' : 'GD not loaded: photos are cleaned by ImageScrubber (install GD on the live server for resizing)') . '; exif extension ' . (extension_loaded('exif') ? 'loaded' : 'missing') . PHP_EOL;

echo "Slug / CSV / Mail\n";
check('Slug::make', Slug::make('Digital Pathways & Youth!') === 'digital-pathways-youth', Slug::make('Digital Pathways & Youth!'));
check('Slug reserved', Slug::isReserved('admin') && !Slug::isReserved('our-story'));
check('CSV formula injection', CsvExport::cell('=1+1') === "'=1+1" && CsvExport::cell('-2') === "'-2" && CsvExport::cell('@x') === "'@x" && CsvExport::cell('plain') === 'plain');
check('CSV phone numbers keep no apostrophe', CsvExport::cell('+256700999888') === ' +256700999888' && CsvExport::cell('+cmd|calc') === "'+cmd|calc");
$before = count(glob(STORAGE_PATH . '/mail/*.eml') ?: []);
$sent = Mailer::send('to@example.org', "Hello\r\nBcc: evil@example.org", 'Body', ['reply_to' => "a@b.org\r\nBcc: x@y.z"]);
$files = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
check('Mailer writes .eml in local env', $sent && count($files) === $before + 1);
if ($files) {
    $latest = end($files);
    $raw = (string) file_get_contents($latest);
    check('Mailer is header-injection safe', !preg_match('/^Bcc:/mi', $raw), $raw);
    @unlink($latest);
}

echo "Honeypot / RateLimit\n";
$req = new Request(['REQUEST_URI' => '/x', 'REQUEST_METHOD' => 'POST', 'SCRIPT_NAME' => '/lumina/index.php'], [], ['website' => '', '_hp_ts' => (time() - 10) . '.' . hash_hmac('sha256', (string) (time() - 10), Config::appKey())], []);
check('Honeypot passes a slow human', Honeypot::passes($req));
$bot = new Request(['REQUEST_URI' => '/x', 'REQUEST_METHOD' => 'POST', 'SCRIPT_NAME' => '/lumina/index.php'], [], ['website' => 'http://spam', '_hp_ts' => (time() - 10) . '.bad'], []);
check('Honeypot rejects a bot', !Honeypot::passes($bot));
$k = 'selftest:' . bin2hex(random_bytes(3));
check('RateLimit allows then blocks', RateLimit::attempt($k, 2, 60) && RateLimit::attempt($k, 2, 60) && !RateLimit::attempt($k, 2, 60));
RateLimit::clear($k);

echo "Page registry / content blocks\n";
check('registry loads home + global', PageRegistry::has('home') && PageRegistry::has('global'));
check('global is virtual (not in nav)', (SitePages::get('global')['virtual'] ?? false) === true);
SitePages::sync();
Content::resetPage('home');
check('default value without override', Content::get('home', 'hero.title') === PageRegistry::field('home', 'hero.title')['default']);
Content::saveMany('home', ['hero.title' => '<b>Custom</b> title', 'hero.image.alt' => 'New alt', 'who_we_are._visible' => '0', 'hero.cta1_url' => 'javascript:alert(1)']);
check('override stored (tags stripped)', Content::get('home', 'hero.title') === 'Custom title', (string) Content::get('home', 'hero.title'));
check('bad url dropped to empty', Content::get('home', 'hero.cta1_url') === '');
check('image alt override', Content::image('home', 'hero.image')['alt'] === 'New alt');
check('section toggle hides', Content::sectionOn('home', 'who_we_are') === false && Content::sectionOn('home', 'challenge') === true);
$rows = [['title' => 'A', 'subtitle' => 's', 'text' => 't', 'url' => '/x', 'image' => ''], ['title' => '', 'subtitle' => '', 'text' => '', 'url' => '', 'image' => '']];
Content::saveMany('home', ['projects.items' => $rows]);
$list = Content::list('home', 'projects.items');
check('repeater saved, empty row dropped', count($list) === 1 && $list[0]['title'] === 'A', json_encode($list));
Content::reset('home', 'hero.title');
check('reset restores default', Content::get('home', 'hero.title') === PageRegistry::field('home', 'hero.title')['default'] && !Content::isOverridden('home', 'hero.title'));
Content::saveMany('home', ['hero.title' => PageRegistry::field('home', 'hero.title')['default']]);
check('saving the default stores nothing', !Content::isOverridden('home', 'hero.title'));
Content::resetPage('home');

echo "SitePages / Nav\n";
Database::execute("UPDATE pages SET is_published = 0 WHERE slug = 'home'");
SitePages::flush();
check('unpublished page is not published', !SitePages::isPublished('home'));
Database::execute("UPDATE pages SET is_published = 1 WHERE slug = 'home'");
SitePages::flush();
Database::insert('pages', ['slug' => 'selftest-page', 'title' => 'Selftest', 'type' => 'custom', 'body' => '<p>x</p>', 'is_published' => 1, 'show_in_nav' => 1, 'nav_label' => 'Selftest nav', 'nav_order' => 5]);
SitePages::flush();
Nav::flush();
$labels = array_column(Nav::header(), 'label');
check('custom page appears in header nav', in_array('Selftest nav', $labels, true), json_encode($labels));
Database::execute("UPDATE pages SET show_in_nav = 0 WHERE slug = 'selftest-page'");
SitePages::flush();
Nav::flush();
check('hidden page disappears', !in_array('Selftest nav', array_column(Nav::header(), 'label'), true));
Page::deleteWhere(['slug' => 'selftest-page']);
SitePages::flush();
Nav::flush();
$meta = page_meta('home');
check('page_meta fields', $meta['title'] !== '' && str_starts_with($meta['canonical'], 'http') && $meta['robots'] === 'index,follow');

echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
exit($fail === 0 ? 0 : 1);
