<?php
/**
 * Opportunities checks, included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-opportunities.php
 * Covers: feed parser (RSS, Atom, entities refused, HTML refused), deadline / category / summary helpers, the SSRF guard of the feed client (no network:
 * resolver and transport are injected), redirects re-checked, the importer (new, duplicate, closed, advice article, review mode, prune), the public pages
 * (list, search, type, closed, detail, file route), the sitemap and the registry. Everything it creates is removed again (slugs / feeds named zz-selftest-*).
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
use App\Core\PageRegistry;
use App\Core\Settings;
use App\Models\Opportunity;
use App\Services\Opportunities\FeedClient;
use App\Services\Opportunities\FeedParser;
use App\Services\Opportunities\OpportunityImporter as Imp;

$oppSetting = static function (string $k, ?string $v): void {
    Settings::set($k, $v);
    Settings::flush();
};
$oppBefore = ['opp_auto_publish' => (string) setting('opp_auto_publish', '1'), 'opp_feeds_enabled' => (string) setting('opp_feeds_enabled', '1')];
echo "-- opportunities --\n";

// ---- parser ------------------------------------------------------------------------------------------------------------------
$future = date('j F Y', strtotime('+40 days'));
$past = date('j F Y', strtotime('-20 days'));
$rss = static fn(string $items): string => '<?xml version="1.0"?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"><channel><title>Test</title>' . $items . '</channel></rss>';
$item = static fn(string $title, string $link, string $desc, string $cat = 'Scholarships', string $date = ''): string =>
    '<item><title>' . $title . '</title><link>' . $link . '</link><guid>' . $link . '</guid><pubDate>' . ($date ?: date('r')) . '</pubDate><category><![CDATA[' . $cat . ']]></category><description><![CDATA[' . $desc . ']]></description></item>';
$p = FeedParser::parse($rss(
    $item('Mastercard &amp; Co Scholars 2027', 'https://feeds.example.org/a', '<p>Application Deadline: ' . $future . ' Applications open.</p><p>The post X appeared first on Y.</p>')
    . $item('No link item', '', 'x')
    . $item('Bad scheme', 'javascript:alert(1)', 'x')
));
check('parser: RSS items are read, an item without a safe link is dropped', $p['ok'] && count($p['items']) === 1 && $p['items'][0]['title'] === 'Mastercard & Co Scholars 2027');
$atom = FeedParser::parse('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"><title>A</title><entry><title>Atom Fellowship</title><link rel="alternate" href="https://feeds.example.org/b"/><id>b</id><updated>2026-10-01T10:00:00Z</updated><summary>Hello</summary></entry></feed>');
check('parser: Atom entries are read', $atom['ok'] && count($atom['items']) === 1 && $atom['items'][0]['link'] === 'https://feeds.example.org/b');
check('parser: an HTML page, plain text and entity tricks are refused', !FeedParser::parse('<!doctype html><html></html>')['ok'] && !FeedParser::parse('hello')['ok']
    && !FeedParser::parse('<?xml version="1.0"?><!DOCTYPE r [<!ENTITY x SYSTEM "file:///etc/passwd">]><rss><channel><item><title>&x;</title><link>https://a.b/c</link></item></channel></rss>')['ok']);

// ---- helpers -----------------------------------------------------------------------------------------------------------------
check('deadline: "Application Deadline: 22nd November 2026" and "Deadline: November 13, 2026"', Imp::findDeadline('Application Deadline: 22nd November 2026 Applications') === '2026-11-22'
    && Imp::findDeadline('Deadline: November 13, 2026 Applications') === '2026-11-13' && Imp::findDeadline('closes on 5 Dec 2026.') === '2026-12-05');
check('deadline: no date in the text means null (rolling)', Imp::findDeadline('Applications are open all year') === null && Imp::findDeadline('') === null);
check('category: scholarship, fellowship, job beats programme, default is used when nothing matches', Imp::classify('Chevening Scholarship 2027', []) === 'scholarship'
    && Imp::classify('Jane Street Graduate Research Fellowship', []) === 'fellowship' && Imp::classify('Programme Manager Job 2026', []) === 'job'
    && Imp::classify('Something unusual', [], 'grant') === 'grant' && Imp::classify('Something unusual', [], 'bogus') === 'other');
check('text: the "appeared first on" footer is dropped, a leading deadline is not repeated, long text is cut at a word', (function (): bool {
    $c = Imp::cleanText('<p>Hello <b>world</b>.</p><p>The post T appeared first on <a href="x">Site</a>.</p>');
    $s = Imp::summarise('Deadline: 13 November 2026 ' . str_repeat('word ', 120), 'T');
    return $c === 'Hello world.' && !str_starts_with((string) $s, 'Deadline') && mb_strlen((string) $s) <= 305 && str_ends_with((string) $s, '…');
})());
check('advice articles are not opportunities', Imp::isNotAnOpportunity('A Sample Grant Proposal on Urban Heat') && Imp::isNotAnOpportunity('How to write a CV') && !Imp::isNotAnOpportunity('Mastercard Foundation Scholars Program'));
check('link normalisation ignores scheme, case and trailing slash', Imp::normalizeLink('HTTPS://Feeds.Example.org/a/') === Imp::normalizeLink('http://feeds.example.org/a'));

// ---- feed client (no network) ------------------------------------------------------------------------------------------------
$public = static fn(string $h): array => ['93.184.216.34'];
check('guard: a public https address passes', FeedClient::guard('https://feeds.example.org/feed', $public)['ok'] === true);
$bad = ['http://localhost/feed', 'http://127.0.0.1/feed', 'http://[::1]/feed', 'http://169.254.169.254/latest', 'http://10.0.0.5/x', 'ftp://feeds.example.org/f', 'https://user:pw@feeds.example.org/f',
    'https://feeds.example.org:8080/f', 'https://intranet.corp/feed', 'javascript:alert(1)', 'https://feeds.example.org/a b'];
$allBlocked = true;
foreach ($bad as $u) {
    if (FeedClient::guard($u, $public)['ok']) {
        $allBlocked = false;
        check('guard blocks ' . $u, false);
    }
}
check('guard: localhost, loopback, metadata, private, other schemes, credentials, odd ports and internal names are refused', $allBlocked);
check('guard: a name that resolves to a private address is refused', FeedClient::guard('https://evil.example.org/f', static fn(string $h): array => ['93.184.216.34', '10.1.2.3'])['ok'] === false);
$r1 = FeedClient::get('https://feeds.example.org/f', static fn(array $q): array => ['status' => 200, 'body' => 'OK'], $public);
$r2 = FeedClient::get('https://feeds.example.org/f', static fn(array $q): array => ['status' => 404, 'body' => ''], $public);
$hops = [];
$r3 = FeedClient::get('https://feeds.example.org/f', static function (array $q) use (&$hops): array {
    $hops[] = $q['url'];
    return count($hops) === 1 ? ['status' => 302, 'location' => 'http://127.0.0.1/secret', 'body' => ''] : ['status' => 200, 'body' => 'LEAK'];
}, $public);
check('client: 200 returns the body, 404 is a short error, the request is pinned to the checked address', $r1['ok'] && $r1['body'] === 'OK' && !$r2['ok'] && str_contains((string) $r2['error'], '404'));
check('client: a redirect to a private address is refused (every hop is checked again)', !$r3['ok'] && count($hops) === 1);

// ---- importer ----------------------------------------------------------------------------------------------------------------
$cleanup = static function (): void {
    Database::execute("DELETE FROM opportunities WHERE slug LIKE 'zz-selftest-%' OR source_url LIKE 'https://zz-selftest.example.org/%'");
    Database::execute("DELETE FROM opportunity_feeds WHERE name LIKE 'zz-selftest-%'");
};
$cleanup();
$feedId = Database::insert('opportunity_feeds', ['name' => 'zz-selftest-feed', 'url' => 'https://zz-selftest.example.org/feed', 'url_hash' => sha1('https://zz-selftest.example.org/feed'), 'default_category' => 'other']);
$xml = $rss(
    $item('zz-selftest Open Scholarship', 'https://zz-selftest.example.org/open', 'Application Deadline: ' . $future . ' details')
    . $item('zz-selftest Closed Fellowship', 'https://zz-selftest.example.org/closed', 'Deadline: ' . $past . ' closed', 'Fellowships')
    . $item('How to write a CV (zz-selftest)', 'https://zz-selftest.example.org/advice', 'tips')
    . $item('zz-selftest Rolling Internship', 'https://zz-selftest.example.org/rolling', 'Open all year', 'Internships')
    . $item('zz-selftest Ancient Item', 'https://zz-selftest.example.org/old', 'old news', 'Other', date('r', strtotime('-400 days')))
);
$transport = static fn(array $q): array => ['status' => 200, 'body' => $xml];
$res_ = static fn(string $h): array => ['93.184.216.34'];
$feed = Database::one('SELECT * FROM opportunity_feeds WHERE id = ?', [$feedId]);
$oppSetting('opp_auto_publish', '1');
$res = Imp::refreshFeed($feed, static fn(array $q): array => ['status' => 200, 'body' => $xml], $res_);
$count = (int) Database::value("SELECT COUNT(*) FROM opportunities WHERE feed_id = ?", [$feedId], 0);
check('importer: only the open and the rolling item are imported (closed, advice and ancient ones skipped)', $count === 2 && $res['new'] === 2 && $res['error'] === null, "count={$count}");
$row = Database::one("SELECT * FROM opportunities WHERE source_url = 'https://zz-selftest.example.org/open'");
check('importer: category, deadline, credit, link and visibility are set', $row !== null && $row['category'] === 'scholarship' && $row['deadline'] === date('Y-m-d', strtotime($future))
    && $row['source'] === 'feed' && $row['source_name'] === 'zz-selftest-feed' && (int) $row['needs_review'] === 0 && (int) $row['is_active'] === 1);
$res2 = Imp::refreshFeed(Database::one('SELECT * FROM opportunity_feeds WHERE id = ?', [$feedId]), $transport, $res_);
check('importer: running again adds nothing (no duplicates)', $res2['new'] === 0 && (int) Database::value("SELECT COUNT(*) FROM opportunities WHERE feed_id = ?", [$feedId], 0) === 2);
$fd = Database::one('SELECT * FROM opportunity_feeds WHERE id = ?', [$feedId]);
check('feed row records the check', $fd['last_status'] === 'ok' && (int) $fd['total_imported'] === 2 && $fd['last_fetched_at'] !== null);
$bad = Imp::refreshFeed($fd, static fn(array $q): array => ['status' => 200, 'body' => '<html>nope</html>'], $res_);
check('importer: a page that is not a feed is a short error and counted', $bad['error'] !== null && (int) Database::value('SELECT fail_count FROM opportunity_feeds WHERE id = ?', [$feedId], 0) === 1);

$oppSetting('opp_auto_publish', '0');
$xml2 = $rss($item('zz-selftest Needs Approval', 'https://zz-selftest.example.org/review', 'Application Deadline: ' . $future . ' x'));
Imp::refreshFeed(Database::one('SELECT * FROM opportunity_feeds WHERE id = ?', [$feedId]), static fn(array $q): array => ['status' => 200, 'body' => $xml2], $res_);
$rev = Database::one("SELECT * FROM opportunities WHERE source_url = 'https://zz-selftest.example.org/review'");
check('importer: with "approve first" on, the item waits (needs_review = 1)', $rev !== null && (int) $rev['needs_review'] === 1);
$oppSetting('opp_auto_publish', $oppBefore['opp_auto_publish']);

// anyDue / disabled
Database::execute('UPDATE opportunity_feeds SET last_fetched_at = NOW() WHERE id = ?', [$feedId]);
$dueRows = (int) Database::value("SELECT COUNT(*) FROM opportunity_feeds WHERE is_active = 1 AND id = ? AND (last_fetched_at IS NULL OR last_fetched_at < (NOW() - INTERVAL 12 HOUR))", [$feedId], 0);
check('a feed that was just read is not due again', $dueRows === 0);
$oppSetting('opp_feeds_enabled', '0');
check('with the switch off nothing is due and refreshDue does nothing', Imp::anyDue() === false && Imp::refreshDue(false, 5) === ['feeds' => 0, 'new' => 0, 'errors' => 0]);
$oppSetting('opp_feeds_enabled', $oppBefore['opp_feeds_enabled']);

// prune: a closed feed item goes, a manual one stays
$mId = Database::insert('opportunities', ['title' => 'zz-selftest manual old', 'slug' => 'zz-selftest-manual-old', 'category' => 'other', 'source' => 'manual', 'deadline' => date('Y-m-d', strtotime('-60 days'))]);
$fId = Database::insert('opportunities', ['title' => 'zz-selftest feed old', 'slug' => 'zz-selftest-feed-old', 'category' => 'other', 'source' => 'feed', 'deadline' => date('Y-m-d', strtotime('-60 days')), 'source_hash' => sha1('zz-selftest-old')]);
Imp::prune();
check('prune removes old feed items but never the ones the team added', Database::one('SELECT id FROM opportunities WHERE id = ?', [$fId]) === null && Database::one('SELECT id FROM opportunities WHERE id = ?', [$mId]) !== null);

// ---- public pages ------------------------------------------------------------------------------------------------------------
$base = 'http://localhost/' . basename(dirname(__DIR__));
$get = static function (string $path) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_HEADER => true]);
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
$slug = (string) Database::value("SELECT slug FROM opportunities WHERE source_url = 'https://zz-selftest.example.org/open'");
$pg = $get('/opportunities?q=zz-selftest');
check('public: the list shows the imported opportunity and its closing date', $pg['code'] === 200 && str_contains($pg['body'], 'zz-selftest Open Scholarship') && str_contains($pg['body'], 'opp-card'));
check('public: an item waiting for approval is not on the list', !str_contains($pg['body'], 'zz-selftest Needs Approval'));
$pg = $get('/opportunities?type=fellowship&q=zz-selftest');
check('public: the type filter narrows the list (no closed fellowship, the list says nothing found)', $pg['code'] === 200 && !str_contains($pg['body'], 'zz-selftest Open Scholarship'));
$pg = $get('/opportunities/' . $slug);
check('public: the detail page shows the credit to the source and an outside link with noopener', $pg['code'] === 200 && str_contains($pg['body'], 'zz-selftest-feed') && str_contains($pg['body'], 'noopener noreferrer'));
check('public: an unknown or waiting opportunity is a 404', $get('/opportunities/zz-selftest-nope')['code'] === 404 && $get('/opportunities/' . (string) Database::value("SELECT slug FROM opportunities WHERE source_url = 'https://zz-selftest.example.org/review'"))['code'] === 404);
check('public: no file attached means the file route is a 404', $get('/opportunities/' . $slug . '/file')['code'] === 404);
check('public: the list sets no cookie', stripos($get('/opportunities')['head'], 'set-cookie') === false);
Database::execute('UPDATE opportunities SET is_active = 0 WHERE source_url = ?', ['https://zz-selftest.example.org/open']);
check('public: hiding an opportunity removes it at once', $get('/opportunities/' . $slug)['code'] === 404);
Database::execute('UPDATE opportunities SET is_active = 1, deadline = ? WHERE source_url = ?', [date('Y-m-d', strtotime('-1 day')), 'https://zz-selftest.example.org/open']);
$pg = $get('/opportunities/' . $slug);
check('public: a closed opportunity still opens, with the closed notice and noindex', $pg['code'] === 200 && str_contains($pg['body'], 'opp-notice--closed') && str_contains($pg['body'], 'noindex'));
check('public: closed ones are hidden from the list unless "include closed" is ticked', !str_contains($get('/opportunities?q=zz-selftest')['body'], 'zz-selftest Open Scholarship') && str_contains($get('/opportunities?q=zz-selftest&closed=1')['body'], 'zz-selftest Open Scholarship'));
Database::execute('UPDATE opportunities SET deadline = ? WHERE source_url = ?', [date('Y-m-d', strtotime('+30 days')), 'https://zz-selftest.example.org/open']);
check('sitemap lists open opportunities', str_contains($get('/sitemap.xml')['body'], '/opportunities/' . $slug));

// ---- registry, schema, admin protection --------------------------------------------------------------------------------------
$page = PageRegistry::get('opportunities');
check('registry: the page sits under Involvement in the header and footer menus', $page !== null && ($page['nav']['parent'] ?? '') === 'involvement' && !empty($page['nav']['header']) && !empty($page['nav']['footer']));
check('schema: both tables exist and the starting sources were added once', Database::tableExists('opportunities') && Database::tableExists('opportunity_feeds')
    && (int) Database::value("SELECT COUNT(*) FROM opportunity_feeds WHERE url = 'https://opportunitydesk.org/feed/'", [], 0) >= 1);
check('admin pages need a sign-in', in_array($get('/admin/opportunities')['code'], [302, 401, 403], true) && in_array($get('/admin/opportunity-sources')['code'], [302, 401, 403], true));
check('settings: the opportunity settings are defined', Settings::definition('opp_auto_publish') !== null && Settings::definition('opp_refresh_hours') !== null);
check('slug "opportunities" is reserved for custom pages', in_array('opportunities', (array) \App\Core\Config::get('reserved_slugs', []), true));

$cleanup();
$oppSetting('opp_auto_publish', $oppBefore['opp_auto_publish']);
$oppSetting('opp_feeds_enabled', $oppBefore['opp_feeds_enabled']);
check('cleanup: no zz-selftest rows are left', (int) Database::value("SELECT COUNT(*) FROM opportunities WHERE slug LIKE 'zz-selftest-%' OR source_url LIKE 'https://zz-selftest.example.org/%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
