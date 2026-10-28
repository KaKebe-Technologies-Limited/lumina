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
use App\Core\SiteClock;
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
check('CSV formula injection', CsvExport::cell('=1+1') === "'=1+1" && CsvExport::cell('-2+3') === "'-2+3" && CsvExport::cell('-cmd') === "'-cmd" && CsvExport::cell('@x') === "'@x" && CsvExport::cell('plain') === 'plain');
check('CSV plain numbers stay numbers (a negative balance is not a formula)', CsvExport::cell('-17111.00') === '-17111.00' && CsvExport::cell('-2') === '-2' && CsvExport::cell('1234.50') === '1234.50' && CsvExport::cell('-1e5') === "'-1e5");
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
Content::saveMany('home', ['hero.title' => '<b>Custom</b> title', 'hero.image.alt' => 'New alt', 'background._visible' => '0', 'hero.cta1_url' => 'javascript:alert(1)']);
check('override stored (tags stripped)', Content::get('home', 'hero.title') === 'Custom title', (string) Content::get('home', 'hero.title'));
check('bad url dropped to empty', Content::get('home', 'hero.cta1_url') === '');
check('image alt override', Content::image('home', 'hero.image')['alt'] === 'New alt');
check('section toggle hides', Content::sectionOn('home', 'background') === false && Content::sectionOn('home', 'challenge') === true);
$rows = [['title' => 'A', 'subtitle' => 's', 'text' => 't', 'url' => '/x', 'image' => ''], ['title' => '', 'subtitle' => '', 'text' => '', 'url' => '', 'image' => '']];
Content::saveMany('home', ['projects.items' => $rows]);
$list = Content::list('home', 'projects.items');
check('repeater saved, empty row dropped', count($list) === 1 && $list[0]['title'] === 'A', json_encode($list));
Content::reset('home', 'hero.title');
check('reset restores default', Content::get('home', 'hero.title') === PageRegistry::field('home', 'hero.title')['default'] && !Content::isOverridden('home', 'hero.title'));
Content::saveMany('home', ['hero.title' => PageRegistry::field('home', 'hero.title')['default']]);
check('saving the default stores nothing', !Content::isOverridden('home', 'hero.title'));
Content::resetPage('home');

echo "Top-bar clock (SiteClock)\n";
$globalBackup = Database::all("SELECT block_key, value, updated_by FROM content_blocks WHERE page_slug = 'global'");   // the owner's saved site-wide copy is put back at the end
Content::resetPage('global');
check('timezone: valid names kept, case fixed', SiteClock::resolveTimezone('Europe/London') === 'Europe/London' && SiteClock::resolveTimezone('  africa/nairobi ') === 'Africa/Nairobi');
check('timezone: invalid / empty / abbreviations fall back to Africa/Kampala', SiteClock::resolveTimezone('Mars/Base') === 'Africa/Kampala' && SiteClock::resolveTimezone('') === 'Africa/Kampala' && SiteClock::resolveTimezone('EAT') === 'Africa/Kampala' && SiteClock::resolveTimezone('UTC+3') === 'Africa/Kampala' && SiteClock::resolveTimezone("Europe/London\n<script>") === 'Africa/Kampala');
check('timezone: the admin save path (Content::sanitise filter) validates too', Content::sanitise(PageRegistry::field('global', 'clock.timezone'), 'Nope/Zone') === 'Africa/Kampala' && Content::sanitise(PageRegistry::field('global', 'clock.timezone'), 'europe/paris') === 'Europe/Paris');
$def = SiteClock::normalise([]);
$utc = new DateTimeImmutable('2026-10-16 11:35:09', new DateTimeZone('UTC'));
$c = SiteClock::build($def, $utc);
check('default format: "Fri, 16 Oct 2026 · 14:35:09 EAT"', SiteClock::text($c) === 'Fri, 16 Oct 2026 · 14:35:09 EAT' && $c['iso'] === '2026-10-16T14:35:09+03:00' && $c['label'] === 'Uganda time' && $c['offset'] === 180, SiteClock::text($c));
check('build() carries the server instant (epoch) for organisation-time keeping', $c['epoch'] === $utc->getTimestamp() && SiteClock::build(['timezone' => 'Europe/London'] + $def, $utc)['epoch'] === $utc->getTimestamp(), (string) $c['epoch']);
$lonSummer = SiteClock::build(['timezone' => 'Europe/London'] + $def, new DateTimeImmutable('2026-07-01 12:00:00', new DateTimeZone('UTC')));
$lonWinter = SiteClock::build(['timezone' => 'Europe/London'] + $def, new DateTimeImmutable('2026-12-01 12:00:00', new DateTimeZone('UTC')));
check('DST Europe/London: BST +01:00 in July, GMT +00:00 in December', $lonSummer['abbr'] === 'BST' && $lonSummer['time'] === '13:00:00' && $lonSummer['offset'] === 60 && $lonWinter['abbr'] === 'GMT' && $lonWinter['time'] === '12:00:00' && $lonWinter['offset'] === 0, $lonSummer['abbr'] . ' ' . $lonWinter['abbr']);
$lonSwitch = SiteClock::build(['timezone' => 'Europe/London'] + $def, new DateTimeImmutable('2026-03-29 00:59:59', new DateTimeZone('UTC')));
$lonAfter = SiteClock::build(['timezone' => 'Europe/London'] + $def, new DateTimeImmutable('2026-03-29 01:00:00', new DateTimeZone('UTC')));
check('DST switch instant (29 Mar 2026 01:00 UTC): 00:59:59 GMT then 02:00:00 BST', $lonSwitch['time'] === '00:59:59' && $lonSwitch['abbr'] === 'GMT' && $lonAfter['time'] === '02:00:00' && $lonAfter['abbr'] === 'BST', $lonSwitch['time'] . ' / ' . $lonAfter['time']);
$kJan = SiteClock::build($def, new DateTimeImmutable('2026-01-15 21:30:00', new DateTimeZone('UTC')));
$kJul = SiteClock::build($def, new DateTimeImmutable('2026-07-15 21:30:00', new DateTimeZone('UTC')));
check('Africa/Kampala has no DST (+03:00 EAT all year) and the date rolls over at local midnight', $kJan['abbr'] === 'EAT' && $kJul['abbr'] === 'EAT' && $kJan['offset'] === 180 && $kJul['offset'] === 180 && $kJan['time'] === '00:30:00' && $kJan['date'] === 'Fri, 16 Jan' && $kJul['date'] === 'Thu, 16 Jul', $kJan['date'] . ' ' . $kJan['time']);
$odd = SiteClock::build(['timezone' => 'America/Sao_Paulo'] + $def, $utc);
$kol = SiteClock::build(['timezone' => 'Asia/Kolkata'] + $def, $utc);
check('zones without a letter abbreviation read "GMT-3" (like Intl); half-hour zones keep the minutes', $odd['abbr'] === 'GMT-3' && $odd['offset'] === -180 && $odd['time'] === '08:35:09' && $kol['offset'] === 330 && $kol['time'] === '17:05:09', $odd['abbr'] . ' ' . $odd['time'] . ' / ' . $kol['abbr']);
$h12 = SiteClock::build(['hour12' => true] + $def, $utc);
$h12am = SiteClock::build(['hour12' => true] + $def, new DateTimeImmutable('2026-10-16 21:05:07', new DateTimeZone('UTC')));
check('12-hour: "02:35:09 PM" and midnight hour "12:05:07 AM"', $h12['time'] === '02:35:09 PM' && $h12am['time'] === '12:05:07 AM', $h12['time'] . ' / ' . $h12am['time']);
$noSec = SiteClock::build(['show_seconds' => false] + $def, $utc);
$noSec12 = SiteClock::build(['show_seconds' => false, 'hour12' => true] + $def, $utc);
check('show_seconds off: "14:35" and "02:35 PM"', $noSec['time'] === '14:35' && $noSec['seconds'] === false && $noSec12['time'] === '02:35 PM', $noSec['time'] . ' / ' . $noSec12['time']);
$noDate = SiteClock::build(['show_date' => false] + $def, $utc);
check('show_date off: text is just the time and zone', SiteClock::text($noDate) === '14:35:09 EAT' && $noDate['show_date'] === false, SiteClock::text($noDate));
$vis = SiteClock::build(['mode' => 'visitor', 'label' => 'Uganda time'] + $def, $utc);
check('visitor mode drops the label; unknown mode means organisation time', $vis['label'] === '' && $vis['mode'] === 'visitor' && SiteClock::build(['mode' => 'x'] + $def, $utc)['mode'] === 'org' && SiteClock::build(['label' => ''] + $def, $utc)['label'] === '');
Content::resetPage('global');
check('settings() reads the registry defaults (on, organisation time, Africa/Kampala, 24h, seconds, date)', SiteClock::settings() === ['enabled' => true, 'mode' => 'org', 'timezone' => 'Africa/Kampala', 'hour12' => false, 'show_seconds' => true, 'show_date' => true, 'label' => 'Uganda time', 'aria_label' => 'Current date and time'], json_encode(SiteClock::settings()));
Content::saveMany('global', ['clock.enabled' => '0']);
check('clock.enabled off: forView() returns null (the partial prints no clock)', SiteClock::forView() === null);
Content::resetPage('global');
check('clock.enabled back on after reset', SiteClock::forView() !== null);
Content::saveMany('global', ['clock.timezone' => 'Europe/Paris', 'clock.label' => '<b>Paris</b> "time"', 'clock.mode' => 'bogus']);
$sv = SiteClock::settings();
check('stored settings are sanitised (tags stripped, bad mode -> org)', $sv['timezone'] === 'Europe/Paris' && $sv['label'] === 'Paris "time"' && $sv['mode'] === 'org', json_encode($sv));
Content::resetPage('global');
$html = partial('partials/topbar');
check('markup: role=timer, aria-live=off, data attributes and <time datetime>', str_contains($html, 'role="timer"') && str_contains($html, 'aria-live="off"') && str_contains($html, 'data-clock') && str_contains($html, 'data-tz="Africa/Kampala"') && str_contains($html, 'data-mode="org"') && str_contains($html, 'data-hour12="0"') && str_contains($html, 'data-seconds="1"') && str_contains($html, 'data-date="1"') && preg_match('/<time class="clock__time" datetime="\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+03:00"/', $html) === 1);
Content::saveMany('global', ['clock.label' => '"><img src=x onerror=alert(1)>', 'clock.aria_label' => '" onmouseover="x']);
$html = partial('partials/topbar');
check('markup: label and aria-label are escaped (no injected tag or attribute)', !str_contains($html, '<img') && !str_contains($html, '" onmouseover="') && str_contains($html, 'aria-label="&quot; onmouseover=&quot;x"'), substr($html, (int) strpos($html, 'role="timer"'), 200));
Content::resetPage('global');
Content::resetPage('global');
foreach ($globalBackup as $row) {
    Database::insert('content_blocks', ['page_slug' => 'global', 'block_key' => $row['block_key'], 'value' => $row['value'], 'updated_by' => $row['updated_by']]);
}
Content::flush('global');

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

require __DIR__ . '/selftest-roles.php';          // roles and permissions: built-in and custom roles, admin-only abilities never assignable (removes its own test data)
require __DIR__ . '/selftest-membership.php';     // membership service checks (creates and removes its own test data)
require __DIR__ . '/selftest-newsletter.php';     // newsletter schema / service / renderer / SMTP driver checks (starts and stops the mock SMTP server, removes its own test data)
require __DIR__ . '/selftest-calendar.php';       // calendar date engine, recurrence rules, overrides, iCal output, seed, visibility, writer, HTTP endpoints (removes its own test data)
require __DIR__ . '/selftest-meetings.php';       // meeting scheduler: times, invitee parser, iCalendar, service rules, queue, reminders, messages, Mailer multipart (file driver + mock SMTP), requests (removes its own test data)
require __DIR__ . '/selftest-partners-ui.php';           // partner tile / grid / strip markup, admin card, About "Our partners" section (rolled-back transaction, removes its own test data)
require __DIR__ . '/selftest-newsletter-live.php';      // admin change -> public site at once: real HTTP requests to http://localhost/<folder>/ (the HTTP part skips itself when the site does not answer), PDF e-mail, live status
require __DIR__ . '/selftest-partners-newsletter.php';   // partner-logo pipeline, PDF newsletters, live status, public PDF routes, menu, schema (removes its own test data; GD checks need -d extension=gd)
require __DIR__ . '/selftest-forms.php';               // forms: definition schema + fuzz, patterns, condition truth table, validator, files, templates, service, submissions, race, export, mail, retention, spam traps, abilities + the HTTP flow (removes its own test data)
require __DIR__ . '/selftest-membership-card.php';  // membership card: PDF writer + QR encoder (spec vectors, 80 independent matrices), settings, layout fitting, numbers per type and year, issue triggers, idempotence (parallel processes), tokens, files (polyglots, PDFs), erasure, e-mail, scratch-database migration, HTTP flows of the member page, the verification page and the admin (removes its own test data; GD checks need -d extension=gd)
require __DIR__ . '/selftest-membership-staff.php';          // membership card, STAFF side: proof / photo / card panels, trigger rules, bulk issue, filters, abilities (custom roles), erasure, audit (real HTTP; skips itself when the site does not answer; removes its own test data)
require __DIR__ . '/selftest-membership-card-designer.php';  // membership card DESIGNER: tabs, themes, contrast, preview scenarios, e-mail, HTTP flow (throw-away administrator; restores every setting it touches)
require __DIR__ . '/selftest-membership-card-fixes.php';    // membership card, regression checks for the review findings: strict PDF scan, PNG bomb, retention and erasure, renewal race, letters, wording, number format, e-mail colours (removes its own test data)
require __DIR__ . '/selftest-shop.php';                 // shop: money, seed / abilities, SecretStore, products, cart, orders (stock race), status + payment machines, Flutterwave against the LOCAL MOCK, CSV, e-mails, pictures (needs -d extension=gd), HTTP flow + admin abilities (removes its own test data, restores the shop settings)
require __DIR__ . '/selftest-social.php';               // social media: SSRF-guarded HTTP client, caption builder, secrets, post queue, dispatcher + ShareHook (fake channels), the LOCAL MOCK (Telegram, webhook, Graph, X OAuth1, LinkedIn), HTTP abilities (removes its own test data, restores the social settings)
require __DIR__ . '/selftest-opportunities.php';        // opportunities: feed parser, deadline / category / summary helpers, SSRF-guarded feed client, importer (duplicates, closed, review mode, prune), public pages, sitemap (removes its own test data)
require __DIR__ . '/selftest-hero.php';                 // hero / banner images: responsive WebP copies, <picture> partial, one well-formed hero image on every banner page
require __DIR__ . '/selftest-academy.php';               // LAF Academy: quiz text format, starter courses + installer, learner accounts (sign-up, e-mailed confirmation, sign-in, reset), enrolling, lessons, quizzes and grading, final exam, certificates (PDF, check page), virtual classes, access control (removes its own test data)
require __DIR__ . '/selftest-academy-admin.php';         // LAF Academy admin: courses, curriculum, lessons + handouts, quiz editor, classes, learners, certificates, facilitator accounts and the facilitator limits (removes its own test data)
require __DIR__ . '/selftest-academy-challenges.php';     // LAF Academy: volunteer facilitator applications (files, review, approval creates the account) and challenges (quiz, innovation, hackathon: applying, timed quiz, judges, automatic ranking, winners, certificates, winners of the month) (removes its own test data)
require __DIR__ . '/selftest-program-status.php';        // Programs: the stage (planned / ongoing / completed) changed in one click from the admin list and shown at once on the public pages (removes its own test data)
require __DIR__ . '/selftest-ambassadors.php';           // Ambassadors page: regions and countries, the sign-up form and every validation rule, storing and e-mailing an application, the public list of approved ambassadors, admin review / export / delete and access (removes its own test data)
require __DIR__ . '/selftest-document-signatures.php';    // Documents: policy pack importer, signatories + signature pictures, applying signatures, the signed PDF (original untouched plus approval page), public download of the signed copy (removes its own test data)
require __DIR__ . '/selftest-podcast.php';                   // Meetings: podcast / live broadcast platforms (TikTok, Facebook, YouTube, Instagram, any link): forced public with the link, "Podcast / live broadcast" calendar type, Watch live button, gone when finished (removes its own test data)
require __DIR__ . '/selftest-admin-reset.php';          // Admin: forgot-password link (signed token, expiry, one use, lockout cleared; removes its own test user)
require __DIR__ . '/selftest-academy-certificates.php';     // Academy certificates: new PDF design, approval by an administrator (waiting / approve / approve all / e-mail), setting off, by hand (removes its own test data)
require __DIR__ . '/selftest-academy-code.php';             // Academy: 6-digit confirmation code (e-mail + WhatsApp number rules), tries and expiry (removes its own test data)
require __DIR__ . '/selftest-academy-mail-portrait.php';   // Academy: administrator sends confirmation e-mails (delivery record, bulk, confirm all) + learner portrait (private by default, consent, public strip; removes its own test data)

echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
exit($fail === 0 ? 0 : 1);
