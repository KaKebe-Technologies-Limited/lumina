<?php
/**
 * PODCAST / LIVE BROADCAST meetings, included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-podcast.php
 * Covers: the new platforms (podcast, YouTube / Facebook / TikTok / Instagram live), a link is required, the meeting is forced public with its link, the calendar entry
 * is filed under "Podcast / live broadcast" with a "Watch live" button on the public calendar, a non-broadcast meeting is unchanged, and a finished broadcast loses its button.
 * Removes its own rows (zz-pod-*).
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
use App\Models\CalendarEntry;
use App\Models\Meeting;
use App\Services\Meetings\MeetingService as MS;

echo "-- podcast / live broadcast --\n";
check('meetings are available', MS::available());
foreach (['podcast', 'youtube_live', 'facebook_live', 'tiktok_live', 'instagram_live'] as $p) {
    check("platform $p exists and is a broadcast", isset(Meeting::PLATFORMS[$p]) && in_array($p, Meeting::BROADCAST, true) && isset(\App\Controllers\Admin\MeetingsController::PLATFORM_HELP[$p]));
}
check('every platform has help for the form', array_diff(array_keys(Meeting::PLATFORMS), array_keys(\App\Controllers\Admin\MeetingsController::PLATFORM_HELP)) === []);

$date = date('Y-m-d', strtotime('+10 days'));
$mk = static fn(string $title, string $platform, string $url, array $extra = []): array => MS::create([
    'title' => $title, 'date' => $date, 'start_time' => '15:00', 'duration' => '60', 'platform' => $platform, 'join_url' => $url,
    'organiser_name' => 'Zz Pod', 'organiser_email' => 'zz-pod@example.test', 'visibility' => 'private',
] + $extra, null);

$ids = [];
$r = $mk('zz-pod Tiktok talk', 'tiktok_live', 'https://www.tiktok.com/@luminaafrica/live');
check('create: a TikTok live is accepted', $r['ok'], json_encode($r['errors'] ?? []));
$id = (int) ($r['id'] ?? 0);
$ids[] = $id;
$m = Meeting::find($id);
check('it is forced public with the link shown', $m !== null && $m['visibility'] === 'public' && (int) $m['show_join_link_public'] === 1);
$bad = $mk('zz-pod bad', 'youtube_live', 'http://insecure.example.test/x');
check('an insecure link is refused', !$bad['ok'] && isset($bad['errors']['join_url']));
$none = $mk('zz-pod nolink', 'facebook_live', '');
$nid = (int) ($none['id'] ?? 0);
$ids[] = $nid;
$sch = $nid > 0 ? MS::schedule($nid, null) : ['ok' => true];
check('a broadcast without a link cannot be scheduled', !$sch['ok'] && isset($sch['errors']['join_url']), json_encode($sch['errors'] ?? []));

$sch = MS::schedule($id, null);
check('schedule: a broadcast with a link can be scheduled', $sch['ok'], json_encode($sch['errors'] ?? []));
$m = (array) Meeting::find($id);
$e = CalendarEntry::find((int) $m['calendar_entry_id']);
$type = Database::one('SELECT slug, name, show_on_public FROM calendar_types WHERE id = ?', [(int) ($e['type_id'] ?? 0)]);
check('calendar: public, filed under Podcast / live broadcast, with the Watch live button', $e !== null && (int) $e['is_public'] === 1 && (int) $e['is_active'] === 1
    && $type !== null && $type['slug'] === 'podcast' && (int) $type['show_on_public'] === 1 && $e['link_url'] === 'https://www.tiktok.com/@luminaafrica/live' && $e['link_label'] === 'Watch live');

$base = 'http://localhost/' . basename(dirname(__DIR__));
$ch = curl_init($base . '/calendar/' . $e['slug'] . '?date=' . $date);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 40]);
$html = (string) curl_exec($ch);
check('public calendar page of the entry shows the title and the Watch live button', str_contains($html, 'zz-pod Tiktok talk') && str_contains($html, 'Watch live') && str_contains($html, 'tiktok.com/@luminaafrica/live'));

$ch = curl_init($base . '/calendar?view=podcast');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 40]);
$tab = (string) curl_exec($ch);
check('calendar: the independent Podcasts & live tab lists the broadcast with Watch live', str_contains($tab, 'zz-pod Tiktok talk') && str_contains($tab, 'cal-podcast__watch') && str_contains($tab, 'tiktok.com/@luminaafrica/live') && str_contains($tab, 'Podcasts'));
$ch = curl_init($base . '/calendar');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 40]);
check('calendar: the tab is offered on the normal calendar page', str_contains((string) curl_exec($ch), 'data-cal-tab="podcast"'));

$z = $mk('zz-pod Zoom meet', 'zoom', 'https://zoom.us/j/123456789');
$zid = (int) ($z['id'] ?? 0);
$ids[] = $zid;
$zm = (array) Meeting::find($zid);
$ze = CalendarEntry::find((int) $zm['calendar_entry_id']);
check('a Zoom meeting is unchanged: stays private, "Meeting / outreach"', $zm['visibility'] === 'private' && (int) $zm['show_join_link_public'] === 0
    && ($zt = Database::one('SELECT slug FROM calendar_types WHERE id = ?', [(int) $ze['type_id']])) !== null && $zt['slug'] === 'meeting');

Database::execute("UPDATE meetings SET end_at = ?, start_at = ? WHERE id = ?", [date('Y-m-d H:i:s', strtotime('-1 hour')), date('Y-m-d H:i:s', strtotime('-2 hours')), $id]);
MS::completeDue();
$e = CalendarEntry::find((int) $m['calendar_entry_id']);
check('a finished broadcast loses its Watch live button', $e !== null && trim((string) $e['link_url']) === '');

foreach ($ids as $i) {
    if ($i > 0 && ($row = Meeting::find($i)) !== null) {
        Database::execute('DELETE FROM calendar_entries WHERE id = ?', [(int) $row['calendar_entry_id']]);
    }
}
Database::execute("DELETE FROM calendar_types WHERE slug = 'podcast' AND NOT EXISTS (SELECT 1 FROM calendar_entries WHERE type_id = calendar_types.id)");
\App\Services\Calendar\CalendarService::flush();
check('cleanup: no test meetings left', (int) Database::value("SELECT COUNT(*) FROM meetings WHERE title LIKE 'zz-pod%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
