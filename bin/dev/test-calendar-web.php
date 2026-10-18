<?php
/**
 * HTTP checks of the PUBLIC calendar (WEB part of docs/CALENDAR.md): pages, filters, JSON feed, iCal files, detail pages, the Contact block, the sitemap.
 * Read-only: it only GETs the seeded holidays and international days (it creates nothing). Run with the local site up:
 *
 *   /c/xampp/php/php.exe bin/dev/test-calendar-web.php [http://localhost/lumina]
 *
 * The iCal checks use the RFC 5545 checker of bin/dev/ics-validate.php. Exit code 1 when a check fails.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require __DIR__ . '/ics-validate.php';

$base = rtrim($argv[1] ?? 'http://localhost/lumina', '/');
$fail = 0;
function chk(string $name, bool $ok, string $extra = ''): void
{
    global $fail;
    echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
    $fail += $ok ? 0 : 1;
}
/** @return array{0:int,1:array<string,string>,2:string} status, lower-case headers, body */
function get(string $path, array $headers = []): array
{
    global $base;
    $ctx = stream_context_create(['http' => ['timeout' => 30, 'ignore_errors' => true, 'header' => implode("\r\n", $headers)]]);
    $body = @file_get_contents($base . $path, false, $ctx);
    $status = 0;
    $h = [];
    foreach ($http_response_header ?? [] as $line) {
        if (preg_match('~^HTTP/\S+ (\d{3})~', $line, $m)) {
            $status = (int) $m[1];
        } elseif (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $h[strtolower(trim($k))] = trim($v);
        }
    }
    return [$status, $h, (string) $body];
}
$pills = static fn(string $html): array => (preg_match_all('/class="cal-pill [^"]*" href="[^"]*" data-cal-key="[^"]*" data-cal-date="[^"]*" data-cal-type="([^"]*)"/', $html, $m) ? $m[1] : []);

echo "Calendar (public)\n pages\n";
[$st, $h, $page] = get('/calendar?month=2026-04');
chk('GET /calendar answers 200 with one h1 and a 7-column grid', $st === 200 && substr_count($page, '<h1') === 1 && substr_count($page, 'class="cal-grid__dow"') === 7);
chk('April 2026 lists Good Friday (3 April) and Easter Monday (6 April)', str_contains($page, 'data-cal-date="2026-04-03"') && str_contains($page, 'Good Friday') && str_contains($page, 'data-cal-date="2026-04-06"') && str_contains($page, 'Easter Monday'));
[, , $dec] = get('/calendar?month=2026-12');
chk('December 2026 lists Christmas Day and Boxing Day', str_contains($dec, 'Christmas Day') && str_contains($dec, 'Boxing Day'));
[, , $mar] = get('/calendar?month=2027-03');
chk('Eid al-Fitr 2027 says "Date to be confirmed"', str_contains($mar, 'Eid al-Fitr') && str_contains($mar, 'Date to be confirmed'));
chk('canonical of another month names the month; thin months stay indexable only with real entries', preg_match('~rel="canonical" href="[^"]*calendar\?month=2027-03"~', $mar) === 1);
[, , $far] = get('/calendar?month=2199-12');
chk('a month 170 years away does not crash and the page is still a month', str_contains($far, 'December 2199'));
[$sb, , ] = get('/calendar?month=2200-12');
[$sc, , ] = get('/calendar?month=abc&view=zzz&types[]=x&y=99999&m=0');
chk('out-of-range or malformed parameters fall back to the current month (200, no error)', $sb === 200 && $sc === 200);
echo " filters\n";
[, , $only] = get('/calendar?month=2026-04&types=uganda-holiday');
$kinds = array_unique($pills($only));
chk('types=uganda-holiday shows only public holidays and says noindex', $kinds === ['uganda-holiday'] && str_contains($only, 'content="noindex,follow"'), json_encode($kinds));
[, , $none] = get('/calendar?month=2026-04&types=none');
chk('types=none shows no entries and the explanation', $pills($none) === [] && str_contains($none, 'No dates match the selected types'));
[, , $all] = get('/calendar?month=2026-04&types=all');
chk('types=all shows every type', count($pills($all)) >= count($pills($only)) && substr_count($all, 'data-cal-type-toggle checked') >= 2);
[, , $list] = get('/calendar?month=2026-04&view=list');
chk('list view groups by date with anchors', str_contains($list, 'id="d-2026-04-03"') && str_contains($list, 'class="cal-agenda"'));
[, , $up] = get('/calendar?view=upcoming');
chk('coming-up view answers with a list or the empty state', str_contains($up, 'cal-upcoming') || str_contains($up, 'empty-state'));

echo " JSON feed\n";
[$st, $h, $body] = get('/calendar/feed?month=2026-04');
$j = json_decode($body, true);
chk('feed: 200, JSON, cacheable (max-age=300) with an ETag', $st === 200 && is_array($j) && ($h['cache-control'] ?? '') === 'public, max-age=300' && isset($h['etag']));
chk('feed: shape (range, capped, timezone, week_start, types, count, items)', is_array($j) && !array_diff(['range', 'capped', 'timezone', 'week_start', 'types', 'count', 'items'], array_keys($j)));
$bad = false;
foreach ($j['items'] ?? [] as $it) {
    foreach (['created_by', 'updated_by', 'email', 'is_active', 'is_public', 'program_id', 'project_id'] as $k) {
        $bad = $bad || array_key_exists($k, $it);
    }
}
chk('feed: no staff ids, no flags, no personal data in the items', !$bad && ($j['count'] ?? 0) > 0);
[$s304] = get('/calendar/feed?month=2026-04', ['If-None-Match: ' . ($h['etag'] ?? '')]);
chk('feed: If-None-Match answers 304', $s304 === 304);
[$s400] = get('/calendar/feed?start=2026-13-01&end=2026-12-01');
[$s400b] = get('/calendar/feed?month=nope');
[$s400c] = get('/calendar/feed');
chk('feed: bad dates / month / nothing answer 400', $s400 === 400 && $s400b === 400 && $s400c === 400);
[, , $wide] = get('/calendar/feed?start=2026-01-01&end=2030-12-31');
$w = json_decode($wide, true);
chk('feed: a 5-year range is capped at 400 days', ($w['capped'] ?? false) === true && ($w['range']['end'] ?? '') === '2027-02-04', json_encode($w['range'] ?? null));
[, , $typed] = get('/calendar/feed?month=2026-04&types=international-day');
$t = json_decode($typed, true);
chk('feed: ?types= filters', ($t['count'] ?? 0) > 0 && count(array_unique(array_column($t['items'] ?? [], 'type'))) === 1);

echo " iCal\n";
foreach (['/calendar.ics', '/calendar.ics?type=uganda-holiday', '/calendar.ics?type=international-day,uganda-holiday'] as $p) {
    [$st, $h, $ics] = get($p);
    $r = ics_check($ics);
    chk("{$p}: text/calendar, attachment, valid RFC 5545 ({$r['events']} events)", $st === 200 && str_starts_with($h['content-type'] ?? '', 'text/calendar') && str_contains($h['content-disposition'] ?? '', 'attachment') && $r['errors'] === [] && $r['events'] > 0, implode(' | ', array_slice($r['errors'], 0, 2)));
}
$xmas = null;
foreach (($j2 = json_decode(get('/calendar/feed?month=2026-12')[2], true))['items'] ?? [] as $it) {
    if ($it['title'] === 'Christmas Day') {
        $xmas = $it;
    }
}
chk('feed item of Christmas Day has an ics_url and a detail url', $xmas !== null && str_contains($xmas['ics_url'] ?? '', '.ics?date=2026-12-25') && str_contains($xmas['url'] ?? '', '/calendar/christmas-day'));
if ($xmas !== null) {
    [$st, $h, $one] = get(preg_replace('~^' . preg_quote((string) parse_url($base, PHP_URL_PATH), '~') . '~', '', (string) $xmas['ics_url']));
    $r = ics_check($one);
    chk('entry .ics (one occurrence): valid, all-day, exclusive DTEND 20261226', $st === 200 && $r['errors'] === [] && str_contains($one, "DTSTART;VALUE=DATE:20261225\r\n") && str_contains($one, "DTEND;VALUE=DATE:20261226\r\n"), implode(' | ', $r['errors']));
}
[$s404a] = get('/calendar/999999.ics');
[$s404b] = get('/calendar/abc.ics');
chk('unknown entry .ics answers 404', $s404a === 404 && $s404b === 404);

echo " detail pages\n";
[$st, , $d] = get('/calendar/christmas-day');
chk('/calendar/christmas-day: holiday badge, add-to-calendar link, no Event JSON-LD', $st === 200 && str_contains($d, 'Public holiday') && str_contains($d, '.ics?date=') && !str_contains($d, '"@type":"Event"'));
[$st, $h, $nf] = get('/calendar/this-does-not-exist');
chk('unknown date: friendly 404, noindex', $st === 404 && str_contains($nf, 'content="noindex,follow"'));
[$st, , $ff] = get('/calendar/feed?month=2026-04');
chk('/calendar/feed is the feed, never an entry page', $st === 200 && str_starts_with(ltrim($ff), '{'));

echo " Contact page block\n";
[$st, , $c] = get('/contact');
chk('/contact shows the compact calendar, the next dates and the full-calendar link', $st === 200 && str_contains($c, 'data-cal-embed') && str_contains($c, 'cal-upcoming') && str_contains($c, 'href="' . (string) parse_url($base, PHP_URL_PATH) . '/calendar"'));
[, , $c2] = get('/contact?cal=2027-03');
chk('/contact?cal=2027-03 works without JavaScript', str_contains($c2, 'March 2027') && str_contains($c2, 'Eid al-Fitr'));
[, , $c3] = get('/contact?cal=garbage');
chk('/contact?cal=garbage falls back to the current month', str_contains($c3, 'data-cal-embed'));

echo " sitemap\n";
[, , $sm] = get('/sitemap.xml');
chk('sitemap lists /calendar and public entry pages', str_contains($sm, '/calendar</loc>') && str_contains($sm, '/calendar/christmas-day</loc>'));

echo $fail === 0 ? "ALL CALENDAR WEB CHECKS PASSED\n" : "CALENDAR WEB CHECKS FAILED: {$fail}\n";
exit($fail === 0 ? 0 : 1);
