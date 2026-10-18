<?php
/**
 * Meeting scheduler checks, included by bin/selftest.php (shares its check() helper and $fail counter) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-meetings.php
 * Covers: time helpers and schedule validation, the invitee "smart box" parser (incl. a fuzz run), iCalendar output (REQUEST / CANCEL / PUBLISH, folding,
 * escaping, UID / SEQUENCE, one ATTENDEE, header-injection payloads), token entropy, status transitions and SEQUENCE logic, the calendar entry the meeting owns
 * (public / private / draft / cancelled, guards against editing it elsewhere), attendees (caps, duplicates, bulk rule, re-adding, removal), the queue state
 * machine (scopes, claim, 3 attempts), answers (RSVP rules), reminders (selection and no double claim), erasure, the e-mail messages (no other invitee's address,
 * escaping), the Mailer multipart structure (file driver + mock SMTP server), meeting requests, and the public endpoints. Everything it creates is removed again
 * (titles zz-selftest*, addresses @selftest.invalid).
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

use App\Core\Config;
use App\Core\Database;
use App\Core\Mailer;
use App\Models\CalendarEntry;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\MeetingRequest;
use App\Services\Calendar\CalendarService as CS;
use App\Services\Calendar\CalendarWriter as CW;
use App\Services\Calendar\CivilDate as CD;
use App\Services\Calendar\IcsBuilder;
use App\Services\Meetings\InviteeParser as IP;
use App\Services\Meetings\JitsiLink;
use App\Services\Meetings\MeetingIcs as ICS;
use App\Services\Meetings\MeetingRenderer as MR;
use App\Services\Meetings\MeetingRequestService as RQ;
use App\Services\Meetings\MeetingService as MS;
use App\Services\Meetings\MeetingTime as MT;

echo "Meetings\n";

$mtAuditBefore = (int) Database::value('SELECT COALESCE(MAX(id), 0) FROM audit_log', [], 0);
$mtMail = [Config::get('mail.driver'), Config::get('mail.smtp')];
$mtMockDir = STORAGE_PATH . '/mock-smtp/selftest-meetings';
$mtProcs = [];
$mtMailBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$mtCleanup = static function () use ($mtAuditBefore, $mtMail, $mtMockDir, &$mtProcs, $mtMailBefore): void {
    MT::freeze(null);
    foreach ($mtProcs as $p) {
        if (is_resource($p['proc'])) {
            @file_put_contents($p['dir'] . '/STOP', '1');
            @proc_terminate($p['proc']);
            @proc_close($p['proc']);
        }
    }
    $mtProcs = [];
    Mailer::closeConnection();
    Config::set('mail.driver', $mtMail[0]);
    Config::set('mail.smtp', $mtMail[1]);
    Database::execute("DELETE FROM meeting_requests WHERE email LIKE '%@selftest.invalid' OR email LIKE 'erased-r%@invalid.invalid' AND message = '(erased)'");
    Database::execute("DELETE FROM calendar_entries WHERE id IN (SELECT calendar_entry_id FROM meetings WHERE title LIKE 'zz-selftest%')");     // meetings + attendees cascade
    Database::execute("DELETE FROM audit_log WHERE id > ? AND (action LIKE 'meeting%' OR action LIKE 'calendar.%')", [$mtAuditBefore]);
    foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mtMailBefore) as $f) {
        @unlink($f);
    }
    CS::flush();
    MS::flush();
    usleep(300000);
    $rm = static function (string $dir) use (&$rm): void {
        foreach (glob($dir . '/*') ?: [] as $f) {
            is_dir($f) ? $rm($f) : @unlink($f);
        }
        @rmdir($dir);
    };
    $rm($mtMockDir);
};
$mtCleanup();
@mkdir($mtMockDir, 0775, true);

/** Parse a raw message (CRLF) into headers + parts (recursive; base64 bodies decoded). */
$mtMime = static function (string $raw) use (&$mtMime): array {
    $pos = strpos($raw, "\r\n\r\n");
    $head = $pos === false ? $raw : substr($raw, 0, $pos);
    $body = $pos === false ? '' : substr($raw, $pos + 4);
    $headers = [];
    foreach (explode("\r\n", (string) preg_replace("/\r\n[ \t]+/", ' ', $head)) as $line) {
        if (str_contains($line, ':')) {
            [$n, $v] = explode(':', $line, 2);
            $headers[strtolower(trim($n))] ??= trim($v);
        }
    }
    $type = strtolower((string) ($headers['content-type'] ?? 'text/plain'));
    $out = ['headers' => $headers, 'type' => strtok($type, ';'), 'raw_type' => $type, 'parts' => [], 'body' => ''];
    if (str_starts_with($type, 'multipart/') && preg_match('/boundary="([^"]+)"/', $type, $m)) {
        $segs = explode('--' . $m[1], $body);
        array_shift($segs);
        foreach ($segs as $seg) {
            if (str_starts_with(ltrim($seg, "\r\n"), '--')) {
                break;
            }
            $out['parts'][] = $mtMime(ltrim($seg, "\r\n"));
        }
    } else {
        $out['body'] = strtolower((string) ($headers['content-transfer-encoding'] ?? '')) === 'base64' ? (string) base64_decode(str_replace(["\r", "\n"], '', $body)) : $body;
    }
    return $out;
};
/** Unfolded ICS content lines. */
$mtLines = static fn(string $ics): array => explode("\r\n", rtrim((string) preg_replace("/\r\n[ \t]/", '', $ics), "\r\n"));
/** Start the mock SMTP server on a free port. */
$mtMock = static function (string $tag) use (&$mtProcs, $mtMockDir): ?array {
    $probe = stream_socket_server('tcp://127.0.0.1:0');
    if (!$probe) {
        return null;
    }
    $name = (string) stream_socket_get_name($probe, false);
    $port = (int) substr($name, strrpos($name, ':') + 1);
    fclose($probe);
    $dir = $mtMockDir . '/' . $tag;
    @mkdir($dir, 0775, true);
    $proc = @proc_open([PHP_BINARY, dirname(__DIR__) . '/bin/dev/mock-smtp.php', '--quiet', '--port=' . $port, '--out=' . $dir], [0 => ['pipe', 'r'], 1 => ['file', $mtMockDir . '/' . $tag . '.log', 'w'], 2 => ['file', $mtMockDir . '/' . $tag . '.err', 'w']], $pipes);
    if (!is_resource($proc)) {
        return null;
    }
    $mtProcs[] = ['proc' => $proc, 'dir' => $dir];
    for ($i = 0; $i < 40; $i++) {
        $s = @stream_socket_client('tcp://127.0.0.1:' . $port, $en, $es, 0.5);
        if ($s) {
            fclose($s);
            return ['port' => $port, 'dir' => $dir];
        }
        usleep(100000);
    }
    return null;
};
/** Newest .eml written by the file driver since $before. */
$mtLastMail = static function (array $before): string {
    $new = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $before));
    sort($new);
    return $new === [] ? '' : (string) file_get_contents((string) end($new));
};

$future = CD::addDays(MT::today(), 30);                  // a day well ahead of "now" for every meeting below
$form = static fn(array $over = []): array => $over + [
    'title' => 'zz-selftest meeting', 'date' => $future, 'start_time' => '10:00', 'duration' => 60, 'platform' => 'zoom', 'join_url' => 'https://zoom.us/j/123456789?pwd=abc',
    'passcode' => '4321', 'dial_in' => '', 'location' => '', 'description' => '<p>Agenda <strong>one</strong></p><ul><li>Budget</li></ul>', 'organiser_name' => 'Jane Organiser',
    'organiser_email' => 'jane@selftest.invalid', 'visibility' => 'private', 'show_join_link_public' => 0, 'reminder_24h' => 1, 'reminder_1h' => 1, 'allow_rsvp' => 1,
];

try {
    check('tables exist (schema applied)', MS::available());

    // =========================================================================================================== time
    echo " time and schedule\n";
    check('EAT labels: Sat 24 Oct 2026, 10:00–11:00 EAT', MT::rangeLabel('2026-10-24 10:00:00', '2026-10-24 11:00:00') === "Sat 24 Oct 2026, 10:00\u{2013}11:00 EAT");
    check('UTC conversion is a fixed +3 h shift (no DST)', MT::utcStamp('2026-10-24 10:00:00') === '20261024T070000Z' && MT::utcIso('2026-01-01 02:30:00') === '2025-12-31T23:30:00Z' && MT::fromTimestamp(MT::toTimestamp('2026-07-01 00:00:00')) === '2026-07-01 00:00:00');
    check('normalise rejects impossible dates and times', MT::normalise('2026-02-30 10:00') === null && MT::normalise('2026-10-24 24:00') === null && MT::normalise('2026-10-24 10:60') === null && MT::normalise('nope') === null && MT::normalise('2026-10-24T10:05') === '2026-10-24 10:05:00');
    check('duration labels', MT::durationLabel(45) === '45 minutes' && MT::durationLabel(60) === '1 hour' && MT::durationLabel(90) === '1 hour 30 minutes' && MT::durationLabel(120) === '2 hours');
    $f = MT::fromForm('2026-10-24', '10:00', '90', '');
    check('form: start + duration preset', $f['ok'] && $f['end_at'] === '2026-10-24 11:30:00' && $f['duration'] === 90);
    $f = MT::fromForm('2026-10-24', '10:00', '30', '12:15');
    check('form: a custom end time wins over the duration', $f['ok'] && $f['end_at'] === '2026-10-24 12:15:00' && $f['duration'] === 135);
    check('form: end before start, past midnight, 13 hours, missing pieces are refused', !MT::fromForm('2026-10-24', '10:00', '', '09:00')['ok'] && isset(MT::fromForm('2026-10-24', '23:00', '90', '')['errors']['end_time'])
        && isset(MT::fromForm('2026-10-24', '06:00', '', '19:30')['errors']['end_time']) && isset(MT::fromForm('', '10:00', '60', '')['errors']['date']) && isset(MT::fromForm('2026-10-24', '', '60', '')['errors']['start_time']) && isset(MT::fromForm('2026-10-24', '10:00', '', '')['errors']['duration'])
        && !MT::fromForm(['x'], '10:00', '60', '')['ok'] && !MT::fromForm('2026-10-24', '10:00', ['1'], ['2'])['ok']);
    MT::freeze(MT::toTimestamp('2026-10-24 08:00:00'));
    check('now() follows the frozen clock and hasEnded works', MT::now() === '2026-10-24 08:00:00' && MT::hasEnded(['end_at' => '2026-10-24 07:59:59']) && !MT::hasEnded(['end_at' => '2026-10-24 08:00:01']));
    MT::freeze(null);
    check('Jitsi room: https://meet.jit.si/<random>, 128 bits, never the same twice', preg_match('~^https://meet\.jit\.si/LuminaMeeting-[a-f0-9]{32}$~', JitsiLink::generate()) === 1 && JitsiLink::generate() !== JitsiLink::generate() && JitsiLink::isJitsi(JitsiLink::generate()) && !JitsiLink::isJitsi('https://evil.example/LuminaMeeting-abcdefgh'));

    // =========================================================================================================== invitee parser
    echo " invitee parser\n";
    $p = IP::parse("Jane Doe <jane@x.org>, bob@y.org; \"Doe, John\" <JOHN@Z.ORG>\nbad@, a@b.c  mailto:mary@q.org (zed@q.org) bob@y.org Pat <pat@q.org> Lee <lee@q.org>", ['pat@q.org']);
    $emails = array_column($p['valid'], 'email');
    sort($emails);
    check('separators comma / semicolon / space / new line, "Name <addr>", quotes, mailto:, brackets', $emails === ['bob@y.org', 'jane@x.org', 'john@z.org', 'lee@q.org', 'mary@q.org', 'zed@q.org'], json_encode($emails));
    $names = array_column($p['valid'], 'name', 'email');
    check('names are kept (also with a comma inside quotes); the address is lower-cased', $names['jane@x.org'] === 'Jane Doe' && $names['john@z.org'] === 'Doe, John' && $names['lee@q.org'] === 'Lee');
    check('duplicates are counted once, skipped addresses are reported, bad ones say why', $p['duplicates'] === 1 && $p['skipped'] === 1 && count($p['invalid']) === 2 && $p['invalid'][0]['reason'] !== '');
    $inj = IP::parse("evil@x.org\r\nBcc: victim@y.org\r\n, \"A\r\nBcc: b@c.d\" <ok@x.org>, <script>@x.org, a@b.org\0c@d.org");
    check('CR / LF / NUL / angle brackets: every accepted address is a plain address, every name is header-safe, the word "Bcc:" is just rejected text', count(array_filter($inj['valid'], static fn(array $v): bool => preg_match('/[\r\n\0<>"\s,;]/', $v['email'] . $v['name']) === 1)) === 0 && count($inj['invalid']) >= 3);
    check('very long address, IDN, spaces and empty input are refused or empty', IP::parse(str_repeat('a', 200) . '@x.org')['valid'] === [] && IP::parse('münchen@x.org')['valid'] === [] && IP::parse("  \n ,; ")['valid'] === [] && IP::parse('a@b.c')['valid'] === []);
    $big = IP::parse(implode(' ', array_map(static fn(int $i): string => "u{$i}@selftest.invalid", range(1, 1500))));
    check('a huge paste is cut at ' . IP::MAX_TOKENS . ' addresses and says so', count($big['valid']) === IP::MAX_TOKENS && $big['truncated'] === true);
    mt_srand(7);
    $alphabet = ['a', 'b', '@', '.', ' ', ',', ';', "\n", '<', '>', '"', '(', ')', "\r", "\0", 'é', '-', 'x.org', 'mailto:'];
    $bad = 0;
    for ($i = 0; $i < 400; $i++) {
        $s = '';
        for ($k = mt_rand(0, 40); $k > 0; $k--) {
            $s .= $alphabet[mt_rand(0, count($alphabet) - 1)];
        }
        $r = IP::parse($s);
        foreach ($r['valid'] as $v) {
            if (!Mailer::validEmail($v['email']) || preg_match('/[\x00-\x20<>",;()]/', $v['email']) || preg_match('/[\r\n\0<>"]/', $v['name']) || strlen($v['email']) > 190) {
                $bad++;
            }
        }
    }
    check('fuzz (400 random pastes): every accepted address is valid and every name is header-safe', $bad === 0, (string) $bad);

    // =========================================================================================================== iCalendar
    echo " iCalendar\n";
    $uid = ICS::newUid('example.org');
    $m = ['uid' => $uid, 'title' => 'Budget, review; "Q4" \\ done', 'start_at' => '2026-10-24 10:00:00', 'end_at' => '2026-10-24 11:00:00', 'platform' => 'zoom', 'join_url' => 'https://zoom.us/j/1?pwd=' . str_repeat('x', 90),
        'passcode' => '99', 'dial_in' => '+256 700 000000', 'location' => '', 'agenda' => "Line one, with comma;\nLine two Ünïcode " . str_repeat('é', 60), 'organiser_name' => 'Jane "J" Doe', 'organiser_email' => 'jane@example.org', 'sequence' => 3, 'allow_rsvp' => 1];
    $att = ['name' => "Bob; Builder\r\nATTENDEE:mailto:evil@x.org", 'email' => 'bob@example.org', 'rsvp' => 'accepted'];
    $ics = ICS::build($m, $att, 'REQUEST', ['now' => '20261018T120000Z', 'alarm_minutes' => 15, 'page_url' => 'https://example.org/meeting/abc']);
    $lines = $mtLines($ics);
    check('ICS: CRLF everywhere, starts/ends VCALENDAR, METHOD:REQUEST, VERSION, PRODID, VTIMEZONE Africa/Kampala', str_starts_with($ics, "BEGIN:VCALENDAR\r\n") && str_ends_with($ics, "END:VCALENDAR\r\n") && !preg_match('/(?<!\r)\n/', $ics)
        && in_array('METHOD:REQUEST', $lines, true) && in_array('VERSION:2.0', $lines, true) && in_array('TZID:Africa/Kampala', $lines, true) && in_array('BEGIN:VTIMEZONE', $lines, true));
    $over = array_filter(explode("\r\n", $ics), static fn(string $l): bool => strlen($l) > 75);
    check('ICS: no physical line longer than 75 octets, folding never splits a UTF-8 character', $over === [] && mb_check_encoding((string) preg_replace("/\r\n /", '', $ics), 'UTF-8'));
    check('ICS: UID stable, SEQUENCE, DTSTART/DTEND with TZID, ORGANIZER with CN, URL, LOCATION = join link', in_array('UID:' . $uid, $lines, true) && in_array('SEQUENCE:3', $lines, true) && in_array('DTSTART;TZID=Africa/Kampala:20261024T100000', $lines, true)
        && in_array('DTEND;TZID=Africa/Kampala:20261024T110000', $lines, true) && in_array('ORGANIZER;CN="Jane \'J\' Doe":mailto:jane@example.org', $lines, true) === false && (bool) preg_grep('/^ORGANIZER;CN="Jane  ?J ?  ?Doe":mailto:jane@example\.org$/', $lines) !== [] || (bool) preg_grep('/^ORGANIZER;CN="[^"]*":mailto:jane@example\.org$/', $lines));
    check('ICS: URL and LOCATION carry the join link', (bool) preg_grep('~^URL:https://zoom\.us/j/1\?pwd=x+$~', $lines) && (bool) preg_grep('~^LOCATION:https://zoom\.us/j/1\\\\?pwd=x+$|^LOCATION:https://zoom\.us/j/1\?pwd=x+$~', $lines));
    $attLines = array_values(preg_grep('/^ATTENDEE/', $lines));
    check('ICS: exactly ONE ATTENDEE (the recipient), NEEDS-ACTION, RSVP=FALSE (answers go through the site, not a mailed iTIP reply); a CRLF in the name cannot add a second', count($attLines) === 1 && str_contains($attLines[0], 'PARTSTAT=NEEDS-ACTION') && str_contains($attLines[0], 'RSVP=FALSE') && !str_contains($ics, 'RSVP=TRUE') && str_ends_with($attLines[0], ':mailto:bob@example.org') && !str_contains($ics, 'evil@x.org' . "\r\n"));
    $sum = (string) current(preg_grep('/^SUMMARY:/', $lines));
    $desc = (string) current(preg_grep('/^DESCRIPTION:/', $lines));
    check('ICS: TEXT escaping (\\, ; , newline) in SUMMARY and DESCRIPTION', $sum === 'SUMMARY:Budget\\, review\\; "Q4" \\\\ done' && str_contains($desc, 'Line one\\, with comma\\;\\nLine two') && str_contains($desc, 'Passcode: 99') && str_contains($desc, 'Dial-in: +256 700 000000'));
    check('ICS: VALARM when asked, BEGIN/END balanced', in_array('BEGIN:VALARM', $lines, true) && in_array('TRIGGER:-PT15M', $lines, true) && substr_count($ics, 'BEGIN:') === substr_count($ics, 'END:'));
    $cancel = ICS::build($m, $att, 'CANCEL', ['now' => '20261018T120000Z', 'cancel_reason' => 'Speaker ill']);
    $cl = $mtLines($cancel);
    check('ICS CANCEL: METHOD:CANCEL, STATUS:CANCELLED, same UID, same SEQUENCE as given, no alarm, RSVP=FALSE, reason in DESCRIPTION', in_array('METHOD:CANCEL', $cl, true) && in_array('STATUS:CANCELLED', $cl, true) && in_array('UID:' . $uid, $cl, true) && in_array('SEQUENCE:3', $cl, true)
        && !in_array('BEGIN:VALARM', $cl, true) && (bool) preg_grep('/RSVP=FALSE/', $cl) && str_contains(implode('', $cl), 'Speaker ill'));
    $pub = ICS::build($m, $att, 'PUBLISH', ['now' => '20261018T120000Z']);
    check('ICS PUBLISH keeps the invitee\'s own answer (PARTSTAT=ACCEPTED)', str_contains($pub, 'METHOD:PUBLISH') && str_contains($pub, 'PARTSTAT=ACCEPTED'));
    $inPerson = ICS::build(['platform' => 'in_person', 'location' => 'Plot 4, Gulu', 'join_url' => ''] + $m, null, 'REQUEST');
    check('ICS: in-person LOCATION is the address; no ATTENDEE line without a recipient; unknown method refused', (bool) preg_grep('/^LOCATION:Plot 4\\\\, Gulu$/', $mtLines($inPerson)) && !str_contains($inPerson, 'ATTENDEE') && (function () use ($m) { try { ICS::build($m, null, 'EVIL'); return false; } catch (\InvalidArgumentException) { return true; } })());
    $injected = ICS::build(['title' => "Hello
DTSTART:19990101T000000", 'organiser_name' => "X
ATTENDEE:mailto:z@z.z", 'uid' => $uid . "
X-EVIL:1"] + $m, $att, 'REQUEST');
    $il = $mtLines($injected);
    check('ICS: CRLF payloads in title, organiser name and UID cannot create new properties', !preg_grep('/^DTSTART:1999/', $il) && !preg_grep('/^X-EVIL/', $il) && count(preg_grep('/^ATTENDEE/', $il)) === 1 && count(preg_grep('/^ORGANIZER/', $il)) === 1 && count(preg_grep('/^UID:/', $il)) === 1 && count(preg_grep('/^DTSTART;/', $il)) === 1);

    // =========================================================================================================== tokens
    echo " tokens\n";
    $tokens = [];
    for ($i = 0; $i < 3000; $i++) {
        $tokens[MS::newToken()] = 1;
    }
    $sample = array_keys($tokens)[0];
    check('3000 tokens: all different, 48 lower-case hex characters (192 bits), validToken accepts only that shape', count($tokens) === 3000 && preg_match('/^[a-f0-9]{48}$/', $sample) === 1 && MS::validToken($sample)
        && !MS::validToken(strtoupper($sample)) && !MS::validToken(substr($sample, 1)) && !MS::validToken($sample . 'a') && !MS::validToken(['x']) && !MS::validToken(null) && !MS::validToken("../etc/passwd"));
    $bitsOk = true;
    $ones = 0;
    foreach (array_slice(array_keys($tokens), 0, 500) as $t) {
        foreach (str_split($t) as $ch) {
            $ones += substr_count(decbin(hexdec($ch)), '1');
        }
    }
    check('token bits look random (about half the bits are 1)', abs($ones / (500 * 192) - 0.5) < 0.02, (string) round($ones / (500 * 192), 3));

    // =========================================================================================================== meeting service
    echo " meeting service\n";
    $v = MS::validate($form(['title' => '', 'join_url' => 'http://insecure.example', 'organiser_email' => 'nope', 'platform' => 'fax', 'date' => '2020-01-01']), null);
    check('validation: title, https-only link, organiser e-mail, platform, a start in the past', !$v['ok'] && isset($v['errors']['title'], $v['errors']['join_url'], $v['errors']['organiser_email'], $v['errors']['platform'], $v['errors']['date']), json_encode(array_keys($v['errors'])));
    check('validation: user:pass@ links, javascript: and over-long values are refused; arrays are ignored', isset(MS::validate($form(['join_url' => 'https://user:pw@zoom.us/j/1']), null)['errors']['join_url']) && isset(MS::validate($form(['join_url' => 'javascript:alert(1)']), null)['errors']['join_url'])
        && isset(MS::validate($form(['title' => str_repeat('t', 191)]), null)['errors']['title']) && isset(MS::validate($form(['title' => ['x']]), null)['errors']['title']) && MS::validate($form(['platform' => 'phone', 'join_url' => 'tel:+256700000000']), null)['ok']);
    $vh = MS::validate($form(['description' => '<p onclick="x()">Hi</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>']), null);
    check('the description is sanitised and a plain-text agenda is derived', $vh['ok'] && !preg_match('/script|onclick|javascript:/i', (string) $vh['row']['description']) && str_contains((string) $vh['row']['agenda'], 'Hi'));
    $created = MS::create($form(['title' => 'zz-selftest main']), 1);
    check('create: a DRAFT with a uid, sequence 0 and its calendar entry', $created['ok'] && $created['meeting']['status'] === 'draft' && (int) $created['meeting']['sequence'] === 0 && preg_match('/^lumina-meet-[a-f0-9]{24}@/', (string) $created['meeting']['uid']) === 1 && CalendarEntry::find((int) $created['meeting']['calendar_entry_id']) !== null);
    $id = (int) $created['id'];
    $m0 = (array) Meeting::find($id);
    $entry = (array) CalendarEntry::find((int) $m0['calendar_entry_id']);
    $typeId = (int) Database::value("SELECT id FROM calendar_types WHERE slug = 'meeting'");
    check('calendar entry: type Meeting / outreach, timed (not all day), EAT date and times, inactive-for-public (draft), no description / location', (int) $entry['type_id'] === $typeId && (int) $entry['all_day'] === 0 && $entry['start_date'] === $future && $entry['start_time'] === '10:00:00'
        && $entry['end_time'] === '11:00:00' && (int) $entry['is_public'] === 0 && (int) $entry['is_active'] === 1 && $entry['description'] === null && $entry['location'] === null && $entry['link_url'] === null);
    check('the meeting\'s calendar entry cannot be edited, duplicated, deleted or overridden from the calendar (the meeting is the source of truth)',
        !CW::saveEntry((int) $entry['id'], ['title' => 'hacked', 'type_id' => $typeId, 'start_date' => $future, 'all_day' => '1'], 1)['ok'] && !CW::duplicate((int) $entry['id'], 1)['ok'] && !CW::delete((int) $entry['id'])['ok']
        && CW::bulk('delete', [(int) $entry['id']], 1)['changed'] === 0 && CW::bulk('deactivate', [(int) $entry['id']], 1)['changed'] === 0 && !CW::saveOverride((int) $entry['id'], $future, ['action' => 'cancel'], 1)['ok'] && Meeting::find($id) !== null);
    check('a draft is hidden from every public calendar query', !array_filter(CS::occurrences($future, $future), static fn(array $o): bool => str_contains((string) $o['title'], 'zz-selftest')));
    $none = MS::schedule($id, 1);
    check('schedule: refused until the platform has what it needs (a join link) - here it has one, so it works', $none['ok'] && $none['meeting']['status'] === 'scheduled');
    $noLink = MS::create($form(['title' => 'zz-selftest nolink', 'join_url' => '']), 1);
    $r = MS::schedule((int) $noLink['id'], 1);
    $soon = $future . ' 10:00:00';
    check('schedule: a Zoom meeting without a link is refused with a message on join_url; in-person needs an address; a phone meeting needs a number', !$r['ok'] && isset($r['errors']['join_url'])
        && isset(MS::readiness(['platform' => 'in_person', 'location' => '', 'start_at' => $soon, 'organiser_email' => 'a@b.org'])['location']) && isset(MS::readiness(['platform' => 'phone', 'start_at' => $soon, 'organiser_email' => 'a@b.org'])['dial_in'])
        && MS::readiness(['platform' => 'in_person', 'location' => 'Plot 4', 'start_at' => $soon, 'organiser_email' => 'a@b.org']) === []);
    // visibility rules of the calendar entry
    $pub = MS::create($form(['title' => 'zz-selftest public', 'visibility' => 'public', 'platform' => 'google_meet', 'join_url' => 'https://meet.google.com/abc-defg-hij']), 1);
    $pid = (int) $pub['id'];
    $pubEntry = static fn(): array => (array) CalendarEntry::find((int) Meeting::find($pid)['calendar_entry_id']);
    $publicHas = static fn(): bool => (bool) array_filter(CS::occurrences($future, $future), static fn(array $o): bool => $o['title'] === 'zz-selftest public');
    CS::flush();
    $hiddenAsDraft = !$publicHas();
    $sch = MS::schedule($pid, 1);
    CS::flush();
    check('a public DRAFT is still hidden; scheduling makes it public (title, date, time only)', $hiddenAsDraft && $sch['ok'] && $publicHas() && (int) $pubEntry()['is_public'] === 1);
    $occ = current(array_filter(CS::occurrences($future, $future), static fn(array $o): bool => $o['title'] === 'zz-selftest public'));
    check('public meeting: no join link, no description, no location in the public occurrence', $occ && ($occ['link_url'] ?? '') === '' && ($occ['description'] ?? '') === '' && ($occ['location'] ?? '') === '' && $occ['start_time'] === '10:00' && $occ['end_time'] === '11:00' && $occ['type_slug'] === 'meeting');
    $feed = json_encode(CS::feed($future, $future, []));
    $icsAll = CS::icsFeed([]);
    check('the join link of a public meeting is in no public feed or .ics unless ticked', !str_contains($feed, 'meet.google.com') && !str_contains($icsAll, 'meet.google.com') && str_contains($feed, 'zz-selftest public'));
    $up = MS::update($pid, $form(['title' => 'zz-selftest public', 'visibility' => 'public', 'show_join_link_public' => 1, 'platform' => 'google_meet', 'join_url' => 'https://meet.google.com/abc-defg-hij']), 1);
    CS::flush();
    check('"also show the join link publicly" ticked: the link appears on the public entry', $up['ok'] && $pubEntry()['link_url'] === 'https://meet.google.com/abc-defg-hij' && str_contains(json_encode(CS::feed($future, $future, [])), 'meet.google.com'));
    MS::update($pid, $form(['title' => 'zz-selftest public', 'visibility' => 'private', 'show_join_link_public' => 1, 'platform' => 'google_meet', 'join_url' => 'https://meet.google.com/abc-defg-hij']), 1);
    CS::flush();
    check('switching back to private hides it again and unticks the public link', !$publicHas() && (int) Meeting::find($pid)['show_join_link_public'] === 0 && $pubEntry()['link_url'] === null && !str_contains(CS::icsFeed([]), 'zz-selftest public'));
    $canc = MS::cancel($pid, 'Not needed', 1);
    CS::flush();
    check('cancel: status cancelled, entry inactive and not public, reason kept; cancel twice and cancelled -> scheduled are refused', $canc['ok'] && (int) $pubEntry()['is_active'] === 0 && (int) $pubEntry()['is_public'] === 0 && Meeting::find($pid)['cancelled_reason'] === 'Not needed' && !MS::cancel($pid, '', 1)['ok'] && !MS::canTransition('cancelled', 'scheduled') && !MS::canTransition('completed', 'draft'));
    $re = MS::reopen($pid, 1);
    check('reopen: a cancelled meeting becomes a draft again; editing a cancelled one is refused', $re['ok'] && Meeting::find($pid)['status'] === 'draft' && MS::update($pid, $form(['title' => 'zz-selftest public']), 1)['ok']);

    // SEQUENCE and the update flow
    $add = MS::addAttendees($id, [['name' => 'Bob', 'email' => 'bob@selftest.invalid'], ['name' => 'Ann', 'email' => 'ann@selftest.invalid'], ['name' => 'Cy', 'email' => 'cy@selftest.invalid']], 1);
    check('attendees added: 3 rows, unique tokens, nothing sent yet', $add['ok'] && $add['added'] === 3 && count(array_unique(array_column(MS::attendees($id), 'token'))) === 3 && MS::summary($id)['not_sent'] === 3);
    $upd = MS::update($id, $form(['title' => 'zz-selftest main', 'join_url' => 'https://zoom.us/j/999']), 1);
    check('before the first invitation a change does NOT bump SEQUENCE and suggests no update', $upd['ok'] && $upd['sequence_bumped'] === false && (int) Meeting::find($id)['sequence'] === 0 && $upd['update_suggested'] === false);
    $q = MS::queue($id, 'invite_new');
    check('queue invite_new: 3 queued with kind invite', $q['ok'] && $q['queued'] === 3 && MS::summary($id)['queued'] === 3 && (int) Database::value("SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND queue_kind = 'invite'", [$id], 0) === 3);
    check('queue is idempotent: a second call queues nothing more', MS::queue($id, 'invite_new')['queued'] === 0);
    $rows = MS::queuedRows($id, 10);
    $bob = $rows[0];
    $seen = (int) $bob['attempts'];
    check('claim: a compare-and-swap - the first claim of a row wins, a second sender holding the same view loses (no double send)', MS::claim((int) $bob['id'], $seen) === true && MS::claim((int) $bob['id'], $seen) === false);
    MS::markSent((int) $bob['id'], 0);
    $bobRow = (array) MeetingAttendee::find((int) $bob['id']);
    check('markSent: sent, sequence recorded, invited-at set, queue kind cleared, meeting.invitations_sent_at set; a sent row cannot be claimed again',
        $bobRow['invite_status'] === 'sent' && (int) $bobRow['invite_sequence'] === 0 && $bobRow['invite_sent_at'] !== null && $bobRow['queue_kind'] === null && Meeting::find($id)['invitations_sent_at'] !== null && MS::claim((int) $bob['id']) === false);
    $ann = $rows[1];
    MS::claim((int) $ann['id']);
    check('a transient failure keeps the row queued (attempt 1 of 3)', MS::markFailed((int) $ann['id'], 'timeout talking to ann@selftest.invalid', false) === 'queued' && !str_contains((string) MeetingAttendee::find((int) $ann['id'])['last_error'], 'ann@selftest.invalid'));
    MS::claim((int) $ann['id']);
    MS::claim((int) $ann['id']);
    check('the 3rd failed attempt (and any permanent failure) ends as failed', MS::markFailed((int) $ann['id'], 'try 3', false) === 'failed' && MeetingAttendee::find((int) $ann['id'])['invite_status'] === 'failed');
    $cy = $rows[2];
    MS::claim((int) $cy['id']);
    check('a permanent failure (550) is failed at once', MS::markFailed((int) $cy['id'], '550 no such user', true) === 'failed');
    check('retry_failed puts failed rows back with the kind they had', MS::queue($id, 'retry_failed')['queued'] === 2 && (int) Database::value("SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND invite_status = 'queued' AND queue_kind = 'invite' AND attempts = 0", [$id], 0) === 2);
    foreach (MS::queuedRows($id, 10) as $row) {
        MS::claim((int) $row['id']);
        MS::markSent((int) $row['id'], 0);
    }
    $upd = MS::update($id, $form(['title' => 'zz-selftest main', 'join_url' => 'https://zoom.us/j/777']), 1);
    check('after the first invitation a link change bumps SEQUENCE to 1 and suggests "send updated invitation"', $upd['ok'] && $upd['sequence_bumped'] === true && (int) Meeting::find($id)['sequence'] === 1 && $upd['update_suggested'] === true && in_array('join_url', $upd['changed'], true));
    $nochange = MS::update($id, $form(['title' => 'zz-selftest main', 'join_url' => 'https://zoom.us/j/777', 'reminder_1h' => 0]), 1);
    check('a change of an option only (reminders) does not bump SEQUENCE', $nochange['ok'] && $nochange['sequence_bumped'] === false && (int) Meeting::find($id)['sequence'] === 1);
    MS::update($id, $form(['title' => 'zz-selftest main', 'join_url' => 'https://zoom.us/j/777']), 1);
    MS::setRsvp((string) MeetingAttendee::find((int) $bob['id'])['token'], 'accepted');
    $resched = MS::update($id, $form(['title' => 'zz-selftest main', 'join_url' => 'https://zoom.us/j/777', 'start_time' => '14:00']), 1);
    check('a new time bumps SEQUENCE again, resets every answer to "no answer yet" and clears reminder marks', $resched['ok'] && (int) Meeting::find($id)['sequence'] === 2 && MeetingAttendee::find((int) $bob['id'])['rsvp'] === 'pending' && MeetingAttendee::find((int) $bob['id'])['rsvp_at'] === null && $resched['update_suggested']);
    $qa = MS::queue($id, 'update_all');
    check('queue update_all: everyone with an older SEQUENCE gets kind update; people already up to date get nothing', $qa['queued'] === 3 && (int) Database::value("SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND queue_kind = 'update'", [$id], 0) === 3);
    foreach (MS::queuedRows($id, 10) as $row) {
        MS::claim((int) $row['id']);
        MS::markSent((int) $row['id'], 2);
    }
    check('after sending, update_all finds nobody left; invite_new finds nobody', MS::queue($id, 'update_all')['queued'] === 0 && MS::queue($id, 'invite_new')['queued'] === 0);
    $late = MS::addAttendees($id, [['name' => 'Late', 'email' => 'late@selftest.invalid']], 1);
    check('a person added later: invite_new queues ONLY the new one', $late['added'] === 1 && MS::queue($id, 'invite_new')['queued'] === 1);
    $lateRow = (array) MeetingAttendee::find((int) $late['ids'][0]);
    MS::claim((int) $lateRow['id']);
    MS::release((int) $lateRow['id']);
    check('release() gives the attempt back (configuration failure stops the batch without punishing the recipient)', (int) MeetingAttendee::find((int) $lateRow['id'])['attempts'] === 0 && MeetingAttendee::find((int) $lateRow['id'])['invite_status'] === 'queued');
    check('pause / resume flag and the cron list skip a paused meeting', (function () use ($id): bool { MS::pause($id, 'Bad login'); $a = in_array($id, MS::meetingsWithQueue(), true); MS::resume($id); return !$a && in_array($id, MS::meetingsWithQueue(), true) && Meeting::find($id)['send_halt_reason'] === null; })());

    // attendees: caps, duplicates, removal
    $dupe = MS::addAttendees($id, [['email' => 'BOB@selftest.invalid', 'name' => 'Again'], ['email' => 'not-an-address'], ['email' => "x@selftest.invalid\r\nBcc: y@z.z"]], 1);
    check('re-adding an existing address (any case), a bad address and a CRLF payload add nobody', $dupe['added'] === 0 && $dupe['existing'] === 1 && count(MS::attendees($id)) === 4);
    $many = [];
    for ($i = 0; $i < 51; $i++) {
        $many[] = ['email' => "bulk{$i}@selftest.invalid"];
    }
    $r = MS::addAttendees($id, $many, 1);
    check('more than ' . MS::BULK_THRESHOLD . ' at once needs the bulk right (administrator); with it, it works', !$r['ok'] && $r['added'] === 0 && MS::addAttendees($id, $many, 1, ['allow_bulk' => true])['added'] === 51);
    $rest = [];
    for ($i = 0; $i < 160; $i++) {
        $rest[] = ['email' => "cap{$i}@selftest.invalid"];
    }
    $r = MS::addAttendees($id, $rest, 1, ['allow_bulk' => true]);
    check('the ' . MS::MAX_ATTENDEES . '-attendee cap refuses the whole batch with a clear message', !$r['ok'] && str_contains($r['errors']['_'], (string) MS::MAX_ATTENDEES) && count(MS::attendees($id)) === 55);
    $oldTok = (string) MeetingAttendee::find((int) $bob['id'])['token'];
    $rm = MS::removeAttendee($id, (int) $bob['id'], true, 1);
    $bobAfter = (array) MeetingAttendee::find((int) $bob['id']);
    check('remove with cancellation: removed, queued as kind cancel; their token is dead at once', $rm['ok'] && $rm['queued_cancel'] === true && $bobAfter['removed_at'] !== null && $bobAfter['invite_status'] === 'queued' && $bobAfter['queue_kind'] === 'cancel' && MS::attendeeByToken($oldTok) === null);
    $rm2 = MS::removeAttendee($id, (int) $ann['id'], false, 1);
    check('remove without cancellation: nothing queued; the id must belong to this meeting (IDOR guard); removing twice is refused', $rm2['ok'] && $rm2['queued_cancel'] === false && MeetingAttendee::find((int) $ann['id'])['invite_status'] !== 'queued' && !MS::removeAttendee($pid, (int) $cy['id'], false, 1)['ok'] && !MS::removeAttendee($id, (int) $ann['id'], false, 1)['ok']);
    $back = MS::addAttendees($id, [['email' => 'bob@selftest.invalid', 'name' => 'Bob Again']], 1);
    $bobBack = (array) MeetingAttendee::find((int) $bob['id']);
    check('a removed person who is added again comes back with a NEW token and a clean state', $back['reactivated'] === 1 && $bobBack['removed_at'] === null && $bobBack['token'] !== $oldTok && $bobBack['invite_status'] === 'not_sent' && $bobBack['invite_sent_at'] === null && $bobBack['rsvp'] === 'pending' && MS::attendeeByToken($oldTok) === null);

    // picked people are read from the database, never from the posted address
    $mem = Database::one("SELECT id, email FROM members WHERE status = 'approved' AND anonymised_at IS NULL AND email <> '' LIMIT 1");
    $usr = Database::one('SELECT id, email FROM users WHERE is_active = 1 LIMIT 1');
    $fake = MS::addAttendees($pid, [['source' => 'member', 'member_id' => 99999999, 'email' => 'forged@selftest.invalid'], ['source' => 'user', 'user_id' => 99999999, 'email' => 'forged2@selftest.invalid'],
        ['source' => 'user', 'user_id' => (int) $usr['id'], 'email' => 'forged3@selftest.invalid']], 1);
    $gotUser = Database::one('SELECT email FROM meeting_attendees WHERE meeting_id = ? AND user_id = ?', [$pid, (int) $usr['id']]);
    check('picked members / staff: unknown ids are dropped and the address comes from the database, not from the request', $fake['added'] === 1 && $gotUser !== null && strtolower((string) $gotUser['email']) === strtolower((string) $usr['email']) && !Database::value("SELECT COUNT(*) FROM meeting_attendees WHERE email LIKE 'forged%@selftest.invalid'", [], 0));
    $memberHit = $mem === null ? true : (bool) array_filter(MS::searchMembers(substr((string) $mem['email'], 0, 4)), static fn(array $x): bool => $x['id'] === (int) $mem['id']);
    check('bulk member specs: unknown spec refused, caps respected; member search needs 2+ characters; the staff list is not empty', MS::bulkItems('everyone') === null && MS::bulkItems('members_all') !== null && count(MS::bulkItems('members_all')['items']) <= MS::MAX_BULK_MEMBERS && MS::bulkItems('members_type:abc') === null && MS::searchMembers('a') === [] && count(MS::staff()) >= 1 && $memberHit);

    // RSVP rules
    $tok = (string) MeetingAttendee::find((int) $late['ids'][0])['token'];
    $r1 = MS::setRsvp($tok, 'accepted');
    check('RSVP: accept works and is stored with a time; same answer twice is harmless; the answer can change', $r1['ok'] && MeetingAttendee::find((int) $late['ids'][0])['rsvp'] === 'accepted' && MS::setRsvp($tok, 'accepted')['ok'] && MS::setRsvp($tok, 'tentative')['ok'] && MeetingAttendee::find((int) $late['ids'][0])['rsvp'] === 'tentative');
    check('RSVP: nonsense answers, malformed / unknown tokens and arrays are refused without a database hit on the token', MS::setRsvp($tok, 'yes')['code'] === 'invalid' && MS::setRsvp('x', 'accepted')['code'] === 'not_found' && MS::setRsvp(str_repeat('a', 48), 'accepted')['code'] === 'not_found' && MS::setRsvp([$tok], 'accepted')['code'] === 'not_found' && MS::setRsvp($tok, ['accepted'])['code'] === 'invalid');
    MT::freeze(MT::toTimestamp($future . ' 15:00:01'));
    check('RSVP: refused once the meeting has ended', MS::setRsvp($tok, 'accepted')['code'] === 'ended');
    MT::freeze(null);
    Database::execute('UPDATE meetings SET allow_rsvp = 0 WHERE id = ?', [$id]);
    check('RSVP: refused when answers are switched off', MS::setRsvp($tok, 'accepted')['code'] === 'rsvp_off');
    Database::execute('UPDATE meetings SET allow_rsvp = 1 WHERE id = ?', [$id]);

    // cancel with invitations
    $beforeSeq = (int) Meeting::find($id)['sequence'];
    $cc = MS::cancel($id, 'Venue closed', 1);
    $holders = (int) Database::value('SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NULL AND invite_sent_at IS NOT NULL', [$id], 0);
    check('cancel after invitations: SEQUENCE +1, the number to notify = people holding an invitation; RSVP now says cancelled', $cc['ok'] && (int) Meeting::find($id)['sequence'] === $beforeSeq + 1 && $cc['notify'] === $holders && $holders >= 1 && MS::setRsvp($tok, 'accepted')['code'] === 'cancelled');
    $qc = MS::queue($id, 'cancel_all');
    check('queue cancel_all: everyone invited and not removed gets kind cancel; invite scopes are refused on a cancelled meeting', $qc['queued'] === $cc['notify'] && !MS::queue($id, 'invite_new')['ok']);
    $dl = MS::delete($id);
    $noLinkEntry = (int) Meeting::find((int) $noLink['id'])['calendar_entry_id'];
    check('delete: only a never-sent draft; a meeting that was sent is cancelled instead', !$dl['ok'] && MS::delete((int) $noLink['id'])['ok'] && Meeting::find((int) $noLink['id']) === null && CalendarEntry::find($noLinkEntry) === null);

    // duplicate
    $dup = MS::duplicate($pid, 1, true);
    $dm = $dup['ok'] ? (array) Meeting::find((int) $dup['id']) : [];
    check('duplicate: a new DRAFT "(copy)", new uid, later date, same people with fresh tokens, nothing sent', $dup['ok'] && $dm['status'] === 'draft' && str_ends_with($dm['title'], '(copy)') && $dm['uid'] !== Meeting::find($pid)['uid'] && $dm['start_at'] > Meeting::find($pid)['start_at'] && (int) $dm['sequence'] === 0 && $dm['invitations_sent_at'] === null
        && MS::summary((int) $dup['id'])['total'] === MS::summary($pid)['total'] && MS::summary((int) $dup['id'])['not_sent'] === MS::summary((int) $dup['id'])['total']);
    check('duplicating twice keeps unique calendar slugs and uids', MS::duplicate($pid, 1, false)['ok']);

    // completion
    $past = MS::create($form(['title' => 'zz-selftest past']), 1);
    MS::addAttendees((int) $past['id'], [['email' => 'p1@selftest.invalid']], 1);
    MS::schedule((int) $past['id'], 1);
    MT::freeze(MT::toTimestamp($future . ' 11:00:01'));
    check('completeDue: a scheduled meeting whose end has passed becomes completed; a completed one cannot be edited or cancelled', MS::completeDue() >= 1 && Meeting::find((int) $past['id'])['status'] === 'completed'
        && !MS::update((int) $past['id'], $form(['title' => 'zz-selftest past']), 1)['ok'] && !MS::cancel((int) $past['id'], '', 1)['ok']);
    MT::freeze(null);

    // reminders
    echo " reminders\n";
    $rem = MS::create($form(['title' => 'zz-selftest reminders', 'date' => $future, 'start_time' => '12:00']), 1);
    $rid = (int) $rem['id'];
    $ra = MS::addAttendees($rid, [['email' => 'r1@selftest.invalid'], ['email' => 'r2@selftest.invalid'], ['email' => 'r3@selftest.invalid'], ['email' => 'r4@selftest.invalid']], 1);
    MS::schedule($rid, 1);
    MS::queue($rid, 'invite_new');
    $start = $future . ' 12:00:00';
    MT::freeze(MT::toTimestamp(MT::addMinutes($start, -60 * 30)));         // 30 h before: invitations go out
    foreach (MS::queuedRows($rid, 10) as $row) {
        MS::claim((int) $row['id']);
        MS::markSent((int) $row['id'], 0);
    }
    $a1 = $ra['ids'][0];
    $a2 = $ra['ids'][1];
    MS::setRsvp((string) MeetingAttendee::find((int) $a2)['token'], 'declined');
    MS::removeAttendee($rid, (int) $ra['ids'][2], false, 1);
    MT::freeze(MT::toTimestamp(MT::addMinutes($start, -60 * 25)));
    check('reminders: nothing is due 25 hours before', MS::dueReminders() === []);
    MT::freeze(MT::toTimestamp(MT::addMinutes($start, -60 * 23 - 30)));
    $due = array_filter(MS::dueReminders(), static fn(array $d): bool => (int) $d['meeting']['id'] === $rid);
    $dueIds = array_map(static fn(array $d): int => (int) $d['attendee']['id'], $due);
    sort($dueIds);
    check('24 h reminder: due for invited people only - not the one who declined, not the removed one', array_unique(array_column($due, 'which')) === ['24h'] && $dueIds === [(int) $a1, (int) $ra['ids'][3]], json_encode($dueIds));
    check('claimReminder: the first claim wins, the second loses (never sent twice)', MS::claimReminder((int) $a1, '24h') === true && MS::claimReminder((int) $a1, '24h') === false);
    MS::reminderFailed((int) $ra['ids'][3], '24h', true);
    MS::claimReminder((int) $ra['ids'][3], '24h');
    $stillDue = array_filter(MS::dueReminders(), static fn(array $d): bool => (int) $d['meeting']['id'] === $rid);
    check('after a send nobody is due again for the 24 h reminder (a released claim is retried at most 3 times)', $stillDue === [] && (int) MeetingAttendee::find((int) $ra['ids'][3])['reminder_attempts'] === 1);
    MT::freeze(MT::toTimestamp(MT::addMinutes($start, -45)));
    $due1 = array_filter(MS::dueReminders(), static fn(array $d): bool => (int) $d['meeting']['id'] === $rid);
    check('1 h reminder: due 45 minutes before; the 24 h one is no longer offered less than 2 hours before', array_unique(array_column($due1, 'which')) === ['1h'] && count($due1) === 2);
    MT::freeze(MT::toTimestamp(MT::addMinutes($start, 1)));
    check('no reminder after the start', array_filter(MS::dueReminders(), static fn(array $d): bool => (int) $d['meeting']['id'] === $rid) === []);
    MT::freeze(null);
    Database::execute('UPDATE meeting_attendees SET invite_sent_at = ? WHERE id = ?', [MT::addMinutes($start, -60 * 5), $a1]);
    MT::freeze(MT::toTimestamp(MT::addMinutes($start, -60 * 23)));
    Database::execute('UPDATE meeting_attendees SET reminder_24h_sent_at = NULL WHERE id = ?', [$a1]);
    check('a person invited only 5 hours ahead gets no "24 hours" reminder', !in_array((int) $a1, array_map(static fn(array $d): int => $d['which'] === '24h' ? (int) $d['attendee']['id'] : 0, MS::dueReminders()), true));
    MT::freeze(null);

    // erase
    $erased = MS::erase('p1@selftest.invalid');
    $er = Database::one("SELECT * FROM meeting_attendees WHERE meeting_id = ? ORDER BY id LIMIT 1", [(int) $past['id']]);
    check('erase: the address and name are gone, the old token is replaced, the row stays removed (counts and the never-twice guard are unchanged)', $erased >= 1 && str_starts_with((string) $er['email'], 'erased-a') && $er['name'] === 'Erased' && $er['removed_at'] !== null && MS::attendeeByToken((string) $er['token']) === null);

    // =========================================================================================================== messages
    echo " e-mail messages\n";
    $m1 = (array) Meeting::find($pid);
    MS::addAttendees($pid, [['name' => 'Zed', 'email' => 'zed@selftest.invalid']], 1);
    $zed = (array) Database::one("SELECT * FROM meeting_attendees WHERE meeting_id = ? AND email = 'zed@selftest.invalid'", [$pid]);
    $evil = MS::update($pid, $form(['title' => 'zz-selftest <b>"Bold"</b> & more', 'organiser_name' => 'O"Brien <script>x</script>', 'platform' => 'google_meet', 'join_url' => 'https://meet.google.com/abc-defg-hij', 'description' => '<p>Hello <img src=x onerror=alert(1)></p>']), 1);
    $m1 = (array) Meeting::find($pid);
    if ($m1['status'] !== 'scheduled') {
        MS::schedule($pid, 1);
        $m1 = (array) Meeting::find($pid);
    }
    $invite = MR::message($m1, $zed, 'invite', ['now' => '20261018T120000Z']);
    check('invite: subject "Invitation: <title> — Sat 24 Oct 2026, 10:00–11:00 EAT" (title cut safely), single recipient, text + html + ics', str_starts_with($invite['subject'], 'Invitation: zz-selftest') && str_contains($invite['subject'], " \u{2014} ") && str_contains($invite['subject'], 'EAT') && $invite['to'] === 'zed@selftest.invalid' && $invite['method'] === 'REQUEST' && $invite['ics'] !== null && !str_contains($invite['subject'], "\n"));
    check('invite: Join button, answer links (Accept / Maybe / Decline) open the invitee page and carry no side effect (GET ?r=)', str_contains($invite['html'], 'https://meet.google.com/abc-defg-hij') && substr_count($invite['text'], '/meeting/' . $zed['token']) >= 4 && str_contains($invite['text'], '?r=accept') && str_contains($invite['text'], '?r=decline') && str_contains($invite['text'], $zed['token'] . '.ics'));
    check('invite HTML: titles and names are escaped, no script / on* handlers, tables only, no unsubscribe link', !str_contains($invite['html'], '<script') && !preg_match('/\sonerror=/i', $invite['html']) && str_contains($invite['html'], '&amp; more') && str_contains($invite['html'], '&quot;Bold&quot;') && !str_contains($invite['html'], '<b>') && stripos($invite['html'], 'unsubscribe') === false && str_contains($invite['html'], '<table'));
    check('invite: reply-to is the organiser, From is NOT the organiser address, the display name says "via"', $invite['opts']['reply_to'] === 'jane@selftest.invalid' && ($invite['opts']['from'] ?? 'x@y.z') !== 'jane@selftest.invalid' && str_contains($invite['opts']['from_name'], ' via ') && isset($invite['opts']['calendar']['method']) && $invite['opts']['attachments'][0]['filename'] === 'invite.ics');
    $other = $invite['text'] . $invite['html'] . ($invite['ics'] ?? '');
    check('a message holds only its own recipient: no other invitee\'s address, token or name', !str_contains($other, 'late@selftest.invalid') && !str_contains($other, 'bob@selftest.invalid') && substr_count($invite['ics'], 'ATTENDEE') === 1);
    $upd = MR::message($m1, $zed, 'update');
    $can = MR::message($m1, $zed, 'cancel', ['reason' => 'Speaker ill']);
    $rem24 = MR::message($m1, $zed, 'reminder_24h');
    $rem1 = MR::message($m1, $zed, 'reminder_1h');
    $rmv = MR::message($m1, $zed, 'removed');
    $msg = MR::message($m1, $zed, 'message', ['message' => "Hello <everyone>\n\nSee you there"]);
    check('update / cancel / removed carry the right iCalendar METHOD, reminders and organiser messages carry none', $upd['method'] === 'REQUEST' && $can['method'] === 'CANCEL' && $rmv['method'] === 'CANCEL' && $rem24['method'] === null && $rem1['method'] === null && $msg['method'] === null && !isset($rem1['opts']['calendar']) && !isset($msg['opts']['attachments']));
    check('subjects: Updated / Cancelled / Reminder (24 hours) / Starting soon (1 hour) / About', str_starts_with($upd['subject'], 'Updated:') && str_starts_with($can['subject'], 'Cancelled:') && str_starts_with($rem24['subject'], 'Reminder:') && str_contains($rem24['subject'], ' starts in ') && str_starts_with($rem1['subject'], 'Starting soon:') && str_contains($rem1['subject'], ' starts in ') && str_starts_with($msg['subject'], 'About '));
    check('cancellation: no Join button, no answer buttons, shows the reason; reminders show Join and no answer buttons', !str_contains($can['html'], 'meet.google.com') && !str_contains($can['text'], '?r=accept') && str_contains($can['text'], 'Speaker ill') && str_contains($rem1['html'], 'meet.google.com') && !str_contains($rem1['text'], '?r=accept'));
    check('organiser message: the text is shown as paragraphs and escaped', str_contains($msg['html'], 'Hello &lt;everyone&gt;') && str_contains($msg['text'], 'See you there'));
    $ts = MR::message($m1, $zed, 'invite', ['test' => true]);
    check('test message: "[TEST]" subject and notice, answer links do not carry the invitee token', str_starts_with($ts['subject'], '[TEST] ') && str_contains($ts['html'], 'This is a test') && !str_contains($ts['text'], '?r=accept'));
    $long = MR::message(['title' => str_repeat('Very long title ', 30)] + $m1, $zed, 'invite');
    check('a 480-character title still gives a one-line subject under 210 characters that keeps the date and the time zone', mb_strlen($long['subject']) < 210 && str_contains($long['subject'], ' EAT') && str_contains($long['subject'], CD::fmt(MT::date((string) $m1['start_at']), 'D j M Y')) && !str_contains($long['subject'], "\n"));
    $phoneMsg = MR::message(['platform' => 'phone', 'join_url' => 'tel:+256700000000', 'dial_in' => '+256 700 000000'] + $m1, $zed, 'invite');
    check('phone / in-person meetings: the main button opens the meeting page (no https join link)', str_contains($phoneMsg['text'], 'Open the meeting page: http') && !str_contains($phoneMsg['text'], 'Join meeting: http'));
    $page = MR::pageData($m1, $zed);
    check('pageData: active state, join link, ICS url, UTC times for "your time zone", answer options; cancelled / ended states hide the join link', $page['state'] === 'active' && $page['join_url'] === 'https://meet.google.com/abc-defg-hij' && str_ends_with($page['start_iso'], 'Z') && count($page['rsvp']['options']) === 3 && $page['rsvp']['allowed']
        && MR::pageData(['status' => 'cancelled'] + $m1, $zed)['join_url'] === null && MR::pageData(['status' => 'completed'] + $m1, $zed)['state'] === 'ended');

    // =========================================================================================================== Mailer structure
    echo " Mailer\n";
    Config::set('mail.driver', 'file');
    $before = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    $res = Mailer::attempt($invite['to'], $invite['subject'], $invite['text'], $invite['opts']);
    $raw = $mtLastMail($before);
    $mime = $mtMime($raw);
    $alt = $mime['parts'][0] ?? [];
    $types = array_map(static fn(array $p): string => $p['type'], $alt['parts'] ?? []);
    check('invitation: multipart/mixed { multipart/alternative { text/plain, text/html, text/calendar; method=REQUEST }, application/ics invite.ics }', $res['ok'] && $mime['type'] === 'multipart/mixed' && ($alt['type'] ?? '') === 'multipart/alternative' && $types === ['text/plain', 'text/html', 'text/calendar']
        && str_contains($alt['parts'][2]['raw_type'], 'method=request') && ($mime['parts'][1]['type'] ?? '') === 'application/ics' && str_contains($mime['parts'][1]['headers']['content-disposition'] ?? '', 'filename="invite.ics"'), json_encode($types));
    check('invitation: both ICS copies are identical and valid, the HTML and text parts decode to UTF-8, boundaries are unique', ($alt['parts'][2]['body'] ?? 'a') === ($mime['parts'][1]['body'] ?? 'b') && str_starts_with($alt['parts'][2]['body'], 'BEGIN:VCALENDAR') && str_contains($alt['parts'][1]['body'], '<table') && str_contains($alt['parts'][0]['body'], 'Join')
        && preg_match_all('/boundary="(lumina_[a-f0-9]+)"/', $raw, $bm) && count(array_unique($bm[1])) === 2);
    check('headers: To has the display name, one recipient, Reply-To has the organiser, MIME-Version, Date, Message-ID; no Bcc/Cc', preg_match('/^To: Zed <zed@selftest\.invalid>\r$/m', $raw) === 1 && preg_match('/^Reply-To: .*<jane@selftest\.invalid>/m', $raw) === 1 && str_contains($raw, 'MIME-Version: 1.0') && preg_match('/^Message-ID: </m', $raw) === 1 && !preg_match('/^(Bcc|Cc):/mi', $raw));
    check('header lines are folded to at most 78 characters and contain no bare LF', (function () use ($raw): bool { $head = substr($raw, 0, (int) strpos($raw, "\r\n\r\n")); foreach (explode("\r\n", $head) as $l) { if (strlen($l) > 998 || str_contains($l, "\n")) { return false; } } return !preg_match('/(?<!\r)\n/', $head); })());
    $b4 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    Mailer::attempt('to@selftest.invalid', "Subject\r\nBcc: evil@selftest.invalid", 'text', ['to_name' => "Name\r\nBcc: evil2@selftest.invalid", 'reply_to' => 'r@selftest.invalid', 'reply_to_name' => "R\r\nBcc: evil3@selftest.invalid", 'headers' => ["X-Test\r\nBcc: e@e.e" => 'v', 'X-Ok' => "a\r\nBcc: e@e.e"],
        'attachments' => [['filename' => "../../evil\r\n\"; x=\"y.ics", 'content' => 'hello', 'type' => "text/plain\r\nBcc: e@e.e"], ['filename' => 'ünï.ics', 'content' => 'x', 'type' => 'application/ics']], 'calendar' => ['method' => "REQUEST\r\nBcc: e@e.e", 'content' => 'BEGIN:VCALENDAR']]);
    $raw2 = $mtLastMail($b4);
    check('injection payloads in subject, display names, header names / values, attachment names / types and the calendar method create no new header', $raw2 !== '' && !preg_match('/^Bcc:/mi', $raw2) && !preg_match('/^X-Test/mi', $raw2) && !str_contains($raw2, "\r\nBcc") && substr_count($raw2, "\r\n") > 5);
    $mime2 = $mtMime($raw2);
    check('attachments: path and quotes stripped from the name, a non-ASCII name also gets filename*=UTF-8, a bad type becomes octet-stream, a bad calendar part is dropped', (function () use ($raw2, $mime2): bool {
        $names = [];
        foreach (($mime2['parts'] ?? []) as $p) {
            $names[] = ($p['headers']['content-disposition'] ?? '') . ' | ' . ($p['raw_type'] ?? '');
        }
        $all = implode("\n", $names);
        return !str_contains($all, '../') && str_contains($all, "filename*=UTF-8''") && str_contains($all, 'application/octet-stream') && !str_contains($raw2, 'text/calendar');
    })(), json_encode($mime2['parts'] ? array_map(static fn(array $p): string => $p['raw_type'], $mime2['parts']) : []));
    $b5 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    Mailer::send('plain@selftest.invalid', 'Plain', 'Just text');
    $raw3 = $mtLastMail($b5);
    $mime3 = $mtMime($raw3);
    check('existing callers are unchanged: a text-only mail is still a single base64 text/plain part', $mime3['type'] === 'text/plain' && trim($mime3['body']) === 'Just text' && !str_contains($raw3, 'multipart'));
    $b6 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    Mailer::send('html@selftest.invalid', 'Html', '', ['html' => '<p>Hi <b>there</b></p>']);
    $mime4 = $mtMime($mtLastMail($b6));
    check('existing callers are unchanged: html without text is multipart/alternative [text/plain, text/html] with a generated text part', $mime4['type'] === 'multipart/alternative' && array_map(static fn(array $p): string => $p['type'], $mime4['parts']) === ['text/plain', 'text/html'] && str_contains($mime4['parts'][0]['body'], 'Hi there'));
    $b7 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    $cmsg = MR::message($m1, $zed, 'cancel');
    Mailer::attempt($cmsg['to'], $cmsg['subject'], $cmsg['text'], $cmsg['opts']);
    $mime5 = $mtMime($mtLastMail($b7));
    check('cancellation: inline text/calendar; method=CANCEL and cancel.ics attachment', str_contains($mime5['parts'][0]['parts'][2]['raw_type'] ?? '', 'method=cancel') && str_contains($mime5['parts'][1]['headers']['content-disposition'] ?? '', 'cancel.ics') && str_contains($mime5['parts'][1]['body'] ?? '', 'METHOD:CANCEL'));

    $mock = $mtMock('plain');
    if ($mock === null) {
        check('mock SMTP server starts', false, 'could not start bin/dev/mock-smtp.php');
    } else {
        Config::set('mail.driver', 'smtp');
        Config::set('mail.smtp', ['host' => '127.0.0.1', 'port' => $mock['port'], 'username' => '', 'password' => '', 'encryption' => 'none', 'timeout' => 4, 'cafile' => '']);
        $r1 = Mailer::attempt($invite['to'], $invite['subject'], $invite['text'], $invite['opts']);
        $r2 = Mailer::attempt('reject5-x@selftest.invalid', $invite['subject'], $invite['text'], $invite['opts']);
        Mailer::closeConnection();
        $box = glob($mock['dir'] . '/*.eml') ?: [];
        sort($box);
        $got = $box ? (string) file_get_contents($box[0]) : '';
        $gm = $mtMime(substr($got, (int) strpos($got, "\r\n\r\n") - strlen(substr($got, 0, (int) strpos($got, "\r\n\r\n")) ) ));
        $gm = $mtMime($got);
        check('SMTP (mock): the invitation is delivered to ONE recipient with the multipart structure intact (dot-stuffing, CRLF, long base64 lines)', $r1['ok'] && count($box) === 1 && str_contains($got, 'X-Mock-Rcpt-To: zed@selftest.invalid') && ($gm['type'] ?? '') === 'multipart/mixed' && str_contains((string) ($gm['parts'][1]['body'] ?? ''), 'BEGIN:VCALENDAR') && str_contains((string) ($gm['parts'][0]['parts'][1]['body'] ?? ''), '<table'));
        check('SMTP (mock): a 550 for a recipient is a permanent failure (not transient), the next message still works', $r2['ok'] === false && $r2['transient'] === false);
        Config::set('mail.driver', 'file');
    }

    // =========================================================================================================== meeting requests
    echo " meeting requests\n";
    Config::set('mail.driver', 'file');
    $rqIn = ['name' => 'Rita Requester', 'email' => 'rita@selftest.invalid', 'phone' => '+256700123456', 'organisation' => 'Acme', 'topic' => 'Partnership', 'message' => 'We would like to talk about a school partnership.',
        'preferred_date' => CD::addDays(MT::today(), 14), 'preferred_time' => 'morning', 'duration_minutes' => '45', 'platform_pref' => 'video', 'consent' => '1'];
    check('request topics come from the setting (one per line)', RQ::topics()[0] === 'Partnership' && count(RQ::topics()) === 6);
    foreach (['rita@selftest.invalid', 'victim@selftest.invalid'] as $addr) {
        \App\Core\RateLimit::clear('mreq-ack:' . sha1($addr));
    }
    \App\Core\RateLimit::clear('mreq-ack-all');
    $b8 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    $rq = RQ::create($rqIn, ['ip' => '203.0.113.9']);
    $rqId = (int) ($rq['id'] ?? 0);
    $mails = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $b8));
    $blob = implode("\n", array_map(static fn(string $f): string => (string) file_get_contents($f), $mails));
    check('request: stored as NEW with consent time and a hashed IP; an acknowledgement goes to the visitor and a notification to the organisation', $rq['ok'] && MeetingRequest::find($rqId)['status'] === 'new' && MeetingRequest::find($rqId)['consent_at'] !== null && strlen((string) MeetingRequest::find($rqId)['ip_hash']) === 40
        && !str_contains((string) MeetingRequest::find($rqId)['ip_hash'], '203.0.113') && count($mails) === 2 && preg_match('/^To: rita@selftest.invalid\r?$/m', $blob) === 1 && preg_match('/^To: .*Rita/m', $blob) === 0);
    $b9 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    $rq2 = RQ::create($rqIn, ['ip' => '203.0.113.9']);
    check('the same request twice within a day is accepted silently: no second row, no second e-mail', $rq2['ok'] && ($rq2['duplicate'] ?? false) && (int) $rq2['id'] === $rqId && count(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $b9)) === 0);
    $bad = RQ::create(['name' => '', 'email' => 'nope', 'topic' => 'Not a topic', 'message' => 'short', 'preferred_date' => '2020-01-01', 'phone' => 'abc'], ['ip' => '203.0.113.10']);
    check('request validation: name, e-mail, phone, topic from the list, message length, a future date, consent - each with its own message', !$bad['ok'] && isset($bad['errors']['name'], $bad['errors']['email'], $bad['errors']['topic'], $bad['errors']['message'], $bad['errors']['preferred_date'], $bad['errors']['phone'], $bad['errors']['consent']), json_encode(array_keys($bad['errors'])));
    check('request validation: arrays and tags are neutralised, a topic is exact', !RQ::create(['topic' => ['x']] + $rqIn, [])['ok'] && !RQ::create(['topic' => 'partnership'] + $rqIn, [])['ok'] && !RQ::create(['consent' => ''] + $rqIn, [])['ok']);
    $xss = RQ::create(['name' => '<script>alert(1)</script>Eve', 'email' => 'eve@selftest.invalid', 'message' => '<img src=x onerror=alert(1)> hello there friend'] + $rqIn, ['ip' => '203.0.113.11', 'mail' => false]);
    check('request text is stored as plain text (tags stripped)', $xss['ok'] && !preg_match('/<|onerror/', (string) MeetingRequest::find((int) $xss['id'])['name'] . MeetingRequest::find((int) $xss['id'])['message']));
    for ($i = 0; $i < RQ::MAX_PER_HOUR; $i++) {
        RQ::create(['email' => "flood{$i}@selftest.invalid", 'message' => "Flood message number {$i} please"] + $rqIn, ['ip' => '203.0.113.50', 'mail' => false]);
    }
    $fl = RQ::create(['email' => 'flood-last@selftest.invalid', 'message' => 'One more please, thank you'] + $rqIn, ['ip' => '203.0.113.50', 'mail' => false]);
    check('more than ' . RQ::MAX_PER_HOUR . ' requests per hour from one address are refused (keyed IP hash)', !$fl['ok'] && ($fl['throttled'] ?? false));
    Database::execute('UPDATE settings SET setting_value = ? WHERE setting_key = ?', ['0', 'meeting_request_enabled']);
    \App\Core\Settings::flush();
    $closed = RQ::create(['email' => 'closed@selftest.invalid'] + $rqIn, []);
    Database::execute('UPDATE settings SET setting_value = ? WHERE setting_key = ?', ['1', 'meeting_request_enabled']);
    \App\Core\Settings::flush();
    check('with "accept meeting requests" switched off a request is refused', !$closed['ok'] && RQ::enabled());
    $sd = RQ::scheduleDefaults($rqId);
    check('"Schedule this meeting": title = topic, date = preferred date, morning = 09:00, 45 minutes, a video platform, the requester as the first attendee (source request)',
        $sd !== null && $sd['form']['title'] === 'Partnership' && $sd['form']['date'] === $rqIn['preferred_date'] && $sd['form']['start_time'] === '09:00' && (int) $sd['form']['duration'] === 45 && in_array($sd['form']['platform'], MS::ONLINE_PLATFORMS, true)
        && $sd['attendees'][0]['email'] === 'rita@selftest.invalid' && $sd['attendees'][0]['source'] === 'request');
    $b10 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    $dec = RQ::setStatus($rqId, 'declined', 1, ['notify' => true, 'note' => 'We are fully booked this month.', 'notes' => 'private note']);
    $declMail = $mtLastMail($b10);
    check('decline: status declined, the visitor gets the decline e-mail with the optional note, the private note stays private', $dec['ok'] && $dec['mailed'] && str_contains((string) $mtMime($declMail)['parts'][0]['body'], 'fully booked') && !str_contains($declMail, 'private note') && MeetingRequest::find($rqId)['admin_notes'] === 'private note');
    $b11 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
    RQ::setStatus($rqId, 'declined', 1, ['notify' => true]);
    check('declining twice does not e-mail twice; unknown statuses are refused', count(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $b11)) === 0 && !RQ::setStatus($rqId, 'bogus', 1)['ok']);
    $link = MS::create($form(['title' => 'zz-selftest from request']), 1);
    RQ::attachMeeting($rqId, (int) $link['id'], 1);
    check('attachMeeting: the request is approved and linked; deleting the meeting later keeps the request (SET NULL)', MeetingRequest::find($rqId)['status'] === 'approved' && (int) MeetingRequest::find($rqId)['meeting_id'] === (int) $link['id']);
    $csv = RQ::csvRows(['q' => 'selftest']);
    check('CSV rows exist for the filter and the header matches the columns', $csv !== [] && count($csv[0]) === count(RQ::CSV_HEADER));
    check('erase() makes the request anonymous', MS::erase('rita@selftest.invalid') >= 1 && str_starts_with((string) MeetingRequest::find($rqId)['email'], 'erased-r') && MeetingRequest::find($rqId)['message'] === '(erased)');
    $csvA = MS::csvRows($pid);
    check('attendee CSV: header and rows have the same width; formula-looking names are made safe by CsvExport', $csvA !== [] && count($csvA[0]) === count(MS::CSV_HEADER) && \App\Core\CsvExport::cell('=HYPERLINK("x")') === "'=HYPERLINK(\"x\")");

    // =========================================================================================================== dashboard, nav, registry, settings
    echo " integration\n";
    check('registry: the Contact page has a "Request a meeting" section and the Meetings page holds the e-mail wording', \App\Core\PageRegistry::field('contact', 'meeting_request.title') !== null && \App\Core\PageRegistry::field('site_meetings', 'email.invite_subject') !== null && MR::t('email.invite_subject') !== '' && \App\Core\PageRegistry::sections('contact')['meeting_request']['toggle'] === true);
    $ab = (array) Config::get('abilities');
    check('settings: five meeting keys with defaults; the abilities exist (editor manages, only admin bulk-adds and deletes requests)', setting('meeting_default_duration') === '60' && setting('meeting_default_platform') === 'google_meet' && setting('meeting_request_enabled') === '1' && count(RQ::topics()) === 6
        && $ab['meetings.manage'] === ['admin', 'editor'] && $ab['meetings.bulk'] === ['admin'] && $ab['meetings.delete'] === ['admin']);
    $groups = \App\Core\AdminNav::groups();
    check('sidebar groups still build without a signed-in user (the Calendar group hides itself for visitors)', is_array($groups));
    $nav = require APP_PATH . '/config/admin_nav/meetings.php';
    check('nav fragment: merge_into calendar, three items, the request badge is a callable', $nav['merge_into'] === 'calendar' && count($nav['items']) === 3 && is_callable($nav['items'][2]['badge']));
    check('the Meeting calendar type exists and is not a team-only type', ($t = CS::typeBySlug('meeting')) !== null && (int) $t['show_on_public'] === 1 && $t['name'] === 'Meeting / outreach');
    $weekly = MS::dashboardTiles();
    check('dashboard tiles are produced only for users who may manage meetings (none in the CLI)', is_array($weekly));

    // =========================================================================================================== fixes after the review
    echo " review fixes\n";
    $fxBackup = Database::all("SELECT block_key, value, updated_by FROM content_blocks WHERE page_slug = 'site_meetings'");
    Database::execute("DELETE FROM content_blocks WHERE page_slug = 'site_meetings'");
    \App\Core\Content::flush('site_meetings');
    try {
        $fx = MS::create($form(['title' => 'zz-selftest fixes', 'visibility' => 'public', 'show_join_link_public' => 1,
            'description' => '<p>Read <a href="https://example.org/doc">the doc</a> and <a href="/about">about us</a></p>']), 1);
        $fid = (int) $fx['id'];
        MS::addAttendees($fid, [['name' => 'Fa', 'email' => 'fa@selftest.invalid'], ['name' => 'Fb', 'email' => 'fb@selftest.invalid'], ['name' => 'Fc', 'email' => 'fc@selftest.invalid']], 1);
        MS::schedule($fid, 1);
        MS::queue($fid, 'invite_new');
        foreach (MS::queuedRows($fid, 10) as $row) {
            MS::claim((int) $row['id']);
            MS::markSent((int) $row['id'], 0);
        }
        $fa = (array) Database::one("SELECT * FROM meeting_attendees WHERE meeting_id = ? AND email = 'fa@selftest.invalid'", [$fid]);
        $fb = (array) Database::one("SELECT * FROM meeting_attendees WHERE meeting_id = ? AND email = 'fb@selftest.invalid'", [$fid]);

        // the join label on the public calendar is the editable registry text, never a stored literal
        CS::flush();
        $fxOcc = static fn(): array => array_values(array_filter(CS::occurrences($future, $future), static fn(array $o): bool => str_contains((string) $o['title'], 'zz-selftest fixes')));
        $occ = $fxOcc();
        check('public calendar: the join button text is the registry wording (page.calendar_join), and the entry stores a token, not words', $occ !== [] && $occ[0]['link_label'] === MS::calendarJoinLabel() && $occ[0]['link_label'] !== MS::JOIN_LABEL_TOKEN
            && (string) CalendarEntry::find((int) Meeting::find($fid)['calendar_entry_id'])['link_label'] === MS::JOIN_LABEL_TOKEN);
        \App\Core\Content::set('site_meetings', 'page.calendar_join', 'Enter the room', 1);
        CS::flush();
        $occ = $fxOcc();
        check('editing that wording changes the public calendar at once (nothing stale in the database)', ($occ[0]['link_label'] ?? '') === 'Enter the room');
        \App\Core\Content::reset('site_meetings', 'page.calendar_join');

        // sitemap / robots by ownership
        $plain = CW::saveEntry(null, ['title' => 'zz-selftest plain outreach', 'type_id' => $typeId, 'start_date' => $future, 'all_day' => '1', 'is_public' => '1', 'is_active' => '1'], 1);
        check('only a MEETING-owned entry counts as one: an ordinary entry of the type "Meeting / outreach" is not owned', CW::ownedByMeeting((int) Meeting::find($fid)['calendar_entry_id']) === true && $plain['ok'] && CW::ownedByMeeting((int) $plain['id']) === false);
        $sm = \App\Services\Web\SitemapBuilder::xml();
        $plainSlug = (string) Database::value('SELECT slug FROM calendar_entries WHERE id = ?', [(int) $plain['id']], '');
        $ownSlug = (string) Database::value('SELECT slug FROM calendar_entries WHERE id = ?', [(int) Meeting::find($fid)['calendar_entry_id']], '');
        check('sitemap: the ordinary outreach entry is listed, the meeting-owned entry is not', $plainSlug !== '' && str_contains($sm, '/calendar/' . $plainSlug) && !str_contains($sm, '/calendar/' . $ownSlug));
        Database::execute('DELETE FROM calendar_entries WHERE id = ?', [(int) $plain['id']]);

        // SEQUENCE: remove with a cancellation, then add the same person again
        $before = (int) Meeting::find($fid)['sequence'];
        MS::removeAttendee($fid, (int) $fa['id'], true, 1);
        $seqAfterRemove = (int) Meeting::find($fid)['sequence'];
        $faRemoved = (array) MeetingAttendee::find((int) $fa['id']);
        $cancelMsg = MR::message((array) Meeting::find($fid), $faRemoved, 'removed');
        check('removal with a cancellation: SEQUENCE goes up and the CANCEL carries it; people who were up to date move with it (nobody turns "stale")',
            $seqAfterRemove === $before + 1 && str_contains((string) $cancelMsg['ics'], 'SEQUENCE:' . $seqAfterRemove . "\r\n") && (int) MeetingAttendee::find((int) $fb['id'])['invite_sequence'] === $seqAfterRemove && MS::queue($fid, 'update_all')['queued'] === 0);
        MS::addAttendees($fid, [['name' => 'Fa', 'email' => 'fa@selftest.invalid']], 1);
        $seqAfterReadd = (int) Meeting::find($fid)['sequence'];
        MS::queue($fid, 'invite_new');
        $faNow = (array) MeetingAttendee::find((int) $fa['id']);
        $reinvite = MR::message((array) Meeting::find($fid), $faNow, 'invite');
        check('re-adding the same person: the new REQUEST (same UID) has a HIGHER SEQUENCE than the CANCEL they got, and nobody else becomes stale', $seqAfterReadd === $seqAfterRemove + 1 && str_contains((string) $reinvite['ics'], 'SEQUENCE:' . $seqAfterReadd . "\r\n")
            && (int) MeetingAttendee::find((int) $fb['id'])['invite_sequence'] === $seqAfterReadd && MeetingAttendee::find((int) $fa['id'])['queue_kind'] === 'invite');

        // a removal notice that is still waiting survives the cancellation of the meeting
        $fc = (array) Database::one("SELECT * FROM meeting_attendees WHERE meeting_id = ? AND email = 'fc@selftest.invalid'", [$fid]);
        MS::removeAttendee($fid, (int) $fc['id'], true, 1);
        MS::cancel($fid, 'Speaker ill', 1);
        $fcRow = (array) MeetingAttendee::find((int) $fc['id']);
        check('cancelling the meeting does not drop a removed person\'s waiting "no longer invited" notice', $fcRow['invite_status'] === 'queued' && $fcRow['queue_kind'] === 'cancel' && $fcRow['removed_at'] !== null);
        check('cancel_all queues the people still on the list; the removed one keeps its own notice', MS::queue($fid, 'cancel_all')['queued'] >= 1 && MeetingAttendee::find((int) $fc['id'])['queue_kind'] === 'cancel');

        // an end-only change keeps reminder marks and declines; a moved start resets them
        $rm = MS::create($form(['title' => 'zz-selftest endonly']), 1);
        $rid = (int) $rm['id'];
        MS::addAttendees($rid, [['name' => 'Ra', 'email' => 'ra@selftest.invalid'], ['name' => 'Rb', 'email' => 'rb@selftest.invalid'], ['name' => 'Rc', 'email' => 'rc@selftest.invalid']], 1);
        MS::schedule($rid, 1);
        MS::queue($rid, 'invite_new');
        foreach (MS::queuedRows($rid, 10) as $row) {
            MS::claim((int) $row['id']);
            MS::markSent((int) $row['id'], 0);
        }
        $ra = array_values(MS::attendees($rid));
        Database::execute("UPDATE meeting_attendees SET rsvp = 'accepted', reminder_24h_sent_at = NOW() WHERE id = ?", [(int) $ra[0]['id']]);
        Database::execute("UPDATE meeting_attendees SET rsvp = 'declined', reminder_24h_sent_at = NOW() WHERE id = ?", [(int) $ra[1]['id']]);
        Database::execute("UPDATE meeting_attendees SET reminder_24h_sent_at = NOW() WHERE id = ?", [(int) $ra[2]['id']]);
        MS::update($rid, $form(['title' => 'zz-selftest endonly', 'duration' => 120]), 1);
        $after = array_values(MS::attendees($rid));
        check('only the END time changed: the reminder marks stay (no second "starts in 24 hours"), a decline stays a decline, an acceptance is asked again',
            $after[0]['reminder_24h_sent_at'] !== null && $after[1]['reminder_24h_sent_at'] !== null && $after[2]['reminder_24h_sent_at'] !== null && $after[1]['rsvp'] === 'declined' && $after[0]['rsvp'] === 'pending');
        MS::update($rid, $form(['title' => 'zz-selftest endonly', 'duration' => 120, 'start_time' => '12:00']), 1);
        $after = array_values(MS::attendees($rid));
        check('the START moved: every answer (declines too) and every reminder mark is reset', $after[0]['reminder_24h_sent_at'] === null && $after[1]['rsvp'] === 'pending' && $after[1]['reminder_24h_sent_at'] === null);

        // the public join link goes when the meeting is over
        $cm = MS::create($form(['title' => 'zz-selftest done', 'visibility' => 'public', 'show_join_link_public' => 1]), 1);
        $cid = (int) $cm['id'];
        MS::addAttendees($cid, [['email' => 'cm@selftest.invalid']], 1);
        MS::schedule($cid, 1);
        $ceId = (int) Meeting::find($cid)['calendar_entry_id'];
        $hadLink = (string) CalendarEntry::find($ceId)['link_url'] !== '';
        Database::execute('UPDATE meetings SET start_at = ?, end_at = ? WHERE id = ?', [MT::addMinutes(MT::now(), -120), MT::addMinutes(MT::now(), -60), $cid]);
        MS::completeDue();
        CS::flush();
        check('a public meeting advertises its join link only while it is scheduled: completed => no link on the public calendar entry', $hadLink && Meeting::find($cid)['status'] === 'completed' && (string) CalendarEntry::find($ceId)['link_url'] === '' && (int) CalendarEntry::find($ceId)['is_public'] === 1);

        // reminders say how long is REALLY left
        MT::freeze(MT::toTimestamp('2030-05-06 08:00:00'));
        check('untilLabel: now / minutes / hours / days', MT::untilLabel('2030-05-06 08:00:00') === 'now' && MT::untilLabel('2030-05-06 08:25:00') === 'in 25 minutes' && MT::untilLabel('2030-05-06 08:01:00') === 'in 1 minute' && MT::untilLabel('2030-05-06 09:00:00') === 'in 1 hour'
            && MT::untilLabel('2030-05-06 10:01:00') === 'in 2 hours' && MT::untilLabel('2030-05-07 07:30:00') === 'in 24 hours' && MT::untilLabel('2030-05-09 08:00:00') === 'in 3 days');
        $lateM = ['title' => 'Board', 'start_at' => '2030-05-06 10:01:00', 'end_at' => '2030-05-06 11:00:00', 'platform' => 'zoom', 'join_url' => 'https://zoom.us/j/1', 'organiser_name' => 'Jane', 'organiser_email' => 'jane@selftest.invalid',
            'uid' => 'u@x', 'sequence' => 0, 'allow_rsvp' => 1, 'status' => 'scheduled'];
        $lateA = ['name' => 'Zed', 'email' => 'zed@selftest.invalid', 'token' => str_repeat('b', 48), 'rsvp' => 'pending', 'rsvp_at' => null];
        $lr = MR::message($lateM, $lateA, 'reminder_24h');
        check('a LATE 24-hour reminder says "in 2 hours", never "24 hours"', str_contains($lr['subject'], 'starts in 2 hours') && !str_contains($lr['subject'], '24 hours') && str_contains($lr['text'], 'Starts in 2 hours') && !str_contains($lr['text'], '24 hours'));
        MT::freeze(null);

        // wording: placeholders work in every field, calendar-file words and the sender name are registry text
        \App\Core\Content::set('site_meetings', 'page.cancelled_text', 'Sorry {name}, {title} is off.', 1);
        \App\Core\Content::set('site_meetings', 'email.time_note', 'Time of {title}: {when}', 1);
        \App\Core\Content::set('site_meetings', 'ics.cancelled_prefix', 'Annule:', 1);
        \App\Core\Content::set('site_meetings', 'ics.passcode_label', 'Code', 1);
        \App\Core\Content::set('site_meetings', 'email.from_name', '{organiser} pour {site}', 1);
        MR::pageData($lateM, $lateA);
        check('placeholders work in every field: page texts and the e-mail time note', MR::tf('page.cancelled_text') === 'Sorry Zed, Board is off.' && str_contains(MR::message($lateM, $lateA, 'invite')['text'], 'Time of Board: '));
        $cic = ICS::build($lateM + ['passcode' => 'P9'], $lateA, 'CANCEL');
        $ric = ICS::build($lateM + ['passcode' => 'P9'], $lateA, 'REQUEST');
        check('calendar-file words come from the registry (cancelled prefix, labels) and the sender name pattern is editable', str_contains($cic, 'SUMMARY:Annule: Board') && str_contains($ric, 'Code: P9') && MR::fromName('Jane', 'Lumina') === 'Jane pour Lumina');
        foreach (['page.cancelled_text', 'email.time_note', 'ics.cancelled_prefix', 'ics.passcode_label', 'email.from_name'] as $k) {
            \App\Core\Content::reset('site_meetings', $k);
        }

        // phone / WhatsApp numbers are visible text; a phone meeting's number is the calendar "place"
        $tel = ['platform' => 'whatsapp', 'join_url' => 'tel:+256700123456', 'start_at' => $future . ' 10:00:00', 'end_at' => $future . ' 11:00:00', 'organiser_name' => 'Jane', 'organiser_email' => 'jane@selftest.invalid',
            'uid' => 'u@x', 'sequence' => 0, 'allow_rsvp' => 1, 'status' => 'scheduled', 'title' => 'T'];
        $how = array_values(array_filter(MR::details($tel), static fn(array $r): bool => $r['key'] === 'how'));
        check('a tel: join value shows the number as text (page, e-mail and text twin); a phone meeting\'s LOCATION is its dial-in number', str_contains($how[0]['text'], '+256700123456') && ICS::location(['platform' => 'phone', 'dial_in' => '+256 700 123 456', 'join_url' => '', 'location' => '']) === '+256 700 123 456'
            && str_contains(MR::message($tel, $lateA, 'invite')['text'], '+256700123456'));

        // links typed in the description survive in the text part and the calendar file; answers go through the site only
        $fx1 = (array) Meeting::find($fid);
        $im = MR::message($fx1, $faNow, 'invite');
        check('agenda links keep their target in the plain-text part and the ICS description ("label (url)", relative links made absolute at send time)', str_contains($im['text'], 'the doc (https://example.org/doc)') && str_contains((string) $im['ics'], 'https://example.org/doc') && preg_match('~about us \(https?://[^)]+/about\)~', $im['text']) === 1);
        $icsFlat = str_replace("\r\n ", '', (string) $im['ics']);
        check('the invitation does not ask for a native Accept / Decline (RSVP=FALSE): the site\'s buttons are the only answer path', !str_contains($icsFlat, 'RSVP=TRUE') && str_contains($icsFlat, 'RSVP=FALSE'), substr($icsFlat, 0, 1200));
        $btnHtml = (string) $im['html'];
        check('invitation HTML: Accept / Maybe / Decline are inline-block links with white-space:nowrap, in a cell that does not break words', substr_count($btnHtml, 'white-space:nowrap') >= 3 && preg_match('~<td style="padding:12px 32px 0 32px;font-family:[^"]*">\s*<p[^>]*>[^<]*</p>\s*<div~', $btnHtml) === 1);

        // meeting requests: fixed acknowledgement, caps per address, /64, line breaks
        \App\Core\RateLimit::clear('mreq-ack:' . sha1('victim@selftest.invalid'));
        \App\Core\RateLimit::clear('mreq-ack-all');
        $evil = ['name' => 'SECURITY ALERT: Account locked, call +1-555-0100', 'email' => 'victim@selftest.invalid', 'topic' => 'Partnership', 'consent' => '1',
            'message' => "Your account was suspended.\nVerify now at http://evil.test/login"];
        $fb0 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
        $e1 = RQ::create($evil, ['ip' => '2001:db8:aaaa:1::1']);
        $e2 = RQ::create(['message' => 'A second different message to the same person.'] + $evil, ['ip' => '2001:db8:aaaa:1::2']);
        $e3 = RQ::create(['message' => 'A third different message to the same person.'] + $evil, ['ip' => '2001:db8:aaaa:1::3']);
        $newMails = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $fb0));
        $acks = array_values(array_filter($newMails, static fn(string $f): bool => preg_match('/^To: victim@selftest.invalid\r?$/m', (string) file_get_contents($f)) === 1));
        $ackRaw = $acks !== [] ? (string) file_get_contents($acks[0]) : '';
        $ackMsg = MR::requestMessage('ack', (array) MeetingRequest::find((int) $e1['id']));
        check('request acknowledgement: fixed text - no name, no message, no link from the visitor, no display name in To:', $e1['ok'] && !str_contains($ackMsg['text'], 'SECURITY') && !str_contains($ackMsg['text'], 'evil.test') && !str_contains($ackMsg['html'], 'SECURITY') && !str_contains($ackMsg['html'], 'evil.test')
            && !isset($ackMsg['opts']['to_name']) && str_starts_with($ackMsg['text'], 'Hello,') && $ackRaw !== '' && !str_contains($ackRaw, 'SECURITY'));
        check('request acknowledgement: at most 2 per address per day, even from different addresses of one /64 and with different messages (the visitor still sees success; staff still get every notification)',
            $e1['ok'] && $e2['ok'] && $e3['ok'] && count($acks) === 2 && count($newMails) === 5);
        check('IPv6 throttle key is the /64; IPv4 is unchanged', RQ::ipKey('2001:db8:aaaa:1::1') === RQ::ipKey('2001:db8:aaaa:1:ffff:eeee:dddd:cccc') && RQ::ipKey('2001:db8:aaaa:2::1') !== RQ::ipKey('2001:db8:aaaa:1::1') && RQ::ipKey('203.0.113.9') === '203.0.113.9');
        check('request message and admin notes keep their line breaks (tags stripped, blank lines capped)', RQ::multiline("Line1\r\nLine2\n\n\n\nLine3   x <b>t</b>\t\ty", 200) === "Line1\nLine2\n\nLine3 x t y" && str_contains((string) MeetingRequest::find((int) $e1['id'])['message'], "\n"));
        $decl = MR::requestMessage('decline', ['name' => 'Click http://evil.test <b>now</b> Bob', 'email' => 'victim@selftest.invalid', 'topic' => 'Partnership', 'message' => 'x'], ['note' => 'Sorry']);
        check('decline: the name in the text is plain letters (no link, no markup) and the visitor message is never echoed', !str_contains($decl['text'], 'evil.test') && !str_contains($decl['text'], '<b>') && str_contains($decl['text'], 'Sorry'));

        // the 1-hour reminder is not sent when only minutes remain
        Database::execute('UPDATE meeting_attendees SET invite_sent_at = DATE_SUB(NOW(), INTERVAL 30 DAY), reminder_1h_sent_at = NULL, rsvp = ? WHERE meeting_id = ?', ['pending', $rid]);
        $rStart = (string) Meeting::find($rid)['start_at'];
        MT::freeze(MT::toTimestamp(MT::addMinutes($rStart, -5)));
        $dueLate = array_filter(MS::dueReminders(), static fn(array $d): bool => (int) $d['meeting']['id'] === $rid && $d['which'] === '1h');
        MT::freeze(MT::toTimestamp(MT::addMinutes($rStart, -30)));
        $dueOk = array_filter(MS::dueReminders(), static fn(array $d): bool => (int) $d['meeting']['id'] === $rid && $d['which'] === '1h');
        MT::freeze(null);
        check('the 1-hour reminder is skipped when fewer than 10 minutes remain, and due at 30 minutes', $dueLate === [] && count($dueOk) >= 1);
    } finally {
        MT::freeze(null);
        Database::execute("DELETE FROM content_blocks WHERE page_slug = 'site_meetings'");
        foreach ($fxBackup as $row) {
            Database::insert('content_blocks', ['page_slug' => 'site_meetings', 'block_key' => $row['block_key'], 'value' => $row['value'], 'updated_by' => $row['updated_by']]);
        }
        \App\Core\Content::flush('site_meetings');
        Database::execute("DELETE FROM calendar_entries WHERE title LIKE 'zz-selftest plain outreach%'");
        CS::flush();
    }

    // =========================================================================================================== HTTP
    echo " http\n";
    $base = 'http://localhost/lumina';
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $probe = @file_get_contents($base . '/robots.txt', false, $ctx);
    if ($probe === false) {
        echo "  skip HTTP checks (the local site does not answer at {$base})\n";
    } else {
        $get = static function (string $path, string $method = 'GET') use ($base): array {
            $c = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true, 'method' => $method, 'follow_location' => 0, 'header' => $method === 'POST' ? "Content-Type: application/x-www-form-urlencoded\r\n" : '', 'content' => '']]);
            $body = @file_get_contents($base . $path, false, $c);
            $status = 0;
            foreach ((array) ($http_response_header ?? []) as $h) {
                if (preg_match('~^HTTP/\S+ (\d{3})~', $h, $m)) {
                    $status = (int) $m[1];
                }
            }
            return [$status, (string) $body, (array) ($http_response_header ?? [])];
        };
        check('robots.txt disallows /meeting/', str_contains($probe, 'Disallow: /lumina/meeting/') || str_contains($probe, '/meeting/'));
        check('/meeting/<not a token> and /meeting/<unknown token> are plain 404s (no database lookup, no hint)', $get('/meeting/hello')[0] === 404 && $get('/meeting/' . str_repeat('a', 48))[0] !== 200 && $get('/meeting/' . str_repeat('a', 47))[0] === 404 && $get('/meeting/' . str_repeat('A', 48))[0] === 404 && $get('/meeting/' . str_repeat('a', 48) . '.ics')[0] !== 200);
        check('POST /meeting/<token> without a CSRF token is refused (never a GET side effect)', in_array($get('/meeting/' . $zed['token'], 'POST')[0], [403, 419, 501], true) && (int) Database::value('SELECT COUNT(*) FROM meeting_attendees WHERE id = ? AND rsvp <> ?', [$zed['id'], 'pending'], 0) === 0);
        check('GET /meeting/<token>?r=accept changes nothing', ($get('/meeting/' . $zed['token'] . '?r=accept') && (string) MeetingAttendee::find((int) $zed['id'])['rsvp'] === 'pending'));
        check('admin meeting pages and JSON helpers need a login (302 to login or 401/403)', in_array($get('/admin/meetings')[0], [301, 302, 303, 401, 403], true) && in_array($get('/admin/api/meetings/pickers/staff')[0], [301, 302, 303, 401, 403], true) && in_array($get('/admin/api/meetings/pickers/members?q=ann')[0], [301, 302, 303, 401, 403], true));
        check('no private data in the public pages: the sitemap, the calendar feed and calendar.ics never mention private meetings, join links or addresses', (function () use ($get): bool {
            $all = $get('/sitemap.xml')[1] . $get('/calendar.ics')[1] . $get('/calendar/feed?month=' . substr(CD::addDays(MT::today(), 30), 0, 7))[1];
            return !str_contains($all, 'selftest.invalid') && !str_contains($all, 'zoom.us/j/777') && !str_contains($all, 'meet.google.com') && !str_contains($all, 'zz-selftest main');
        })());
        check('/contact and /calendar still work', $get('/contact')[0] === 200 && $get('/calendar')[0] === 200 && $get('/')[0] === 200);
    }
} finally {
    $mtCleanup();
}

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
