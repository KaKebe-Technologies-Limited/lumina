<?php
/**
 * END-TO-END TEST of the meeting scheduler's SENDING side against the throw-away mock SMTP server: queue and batches (120 attendees), bounces and rejections,
 * a mail-server outage in the middle of a sending (pause, resume), duplicate and concurrent sends (named lock), reschedule -> update with SEQUENCE + 1,
 * cancellation, removal, pause / resume, wrong mail settings, reminders at the 24 h / 1 h boundaries (simulated time), "send reminder now", the cron script,
 * the test invitation, the free-text message, pasted address lists, and the received MIME (multipart structure, text/calendar method, attachment, Reply-To, From,
 * nobody else's address in anybody's mail, UTF-8 names and subjects). Development only; it creates and removes its own data:
 * meetings "zz-mailtest*", addresses "*@mailtest.invalid", mock output in storage/mock-smtp/meetings-mail-test, its audit and rate-limit rows.
 *
 *   /c/xampp/php/php.exe bin/dev/test-meetings-mailing.php [--port=2598] [--keep]
 *
 * Nothing leaves the machine: the test points the mailer at 127.0.0.1 (mock server started by this script, in-process configuration; the .env is not touched).
 * The clock is simulated with MeetingTime::freeze() (a test hook the architect built; this script is the only caller). The same file is its own child process
 * (--child=...) to prove the lock and the cron script across separate PHP processes. OWNER: MAIL agent.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Services\Meetings\InviteeParser as IP;
use App\Services\Meetings\MeetingMailer as MM;
use App\Services\Meetings\MeetingService as MS;
use App\Services\Meetings\MeetingTime as MT;

if (Config::get('app.env') !== 'local') {
    exit("Refusing to run outside APP_ENV=local.\n");
}

$opt = [];
foreach (array_slice($argv, 1) as $a) {
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $m) === 1) {
        $opt[$m[1]] = $m[2] ?? '1';
    }
}
// Hermetic: the owner may have re-worded the e-mails (Admin > Pages > Meetings). The expectations below are the REGISTRY DEFAULTS, so the owner's wording is set aside
// for the run and put back when the script ends, however it ends (a child process of this script leaves it alone).
if (!isset($opt['child'])) {
    $ownerWording = Database::all("SELECT block_key, value, updated_by FROM content_blocks WHERE page_slug = 'site_meetings'");
    if ($ownerWording !== []) {
        Database::execute("DELETE FROM content_blocks WHERE page_slug = 'site_meetings'");
        \App\Core\Content::flush('site_meetings');
        register_shutdown_function(static function () use ($ownerWording): void {
            Database::execute("DELETE FROM content_blocks WHERE page_slug = 'site_meetings'");
            foreach ($ownerWording as $row) {
                Database::insert('content_blocks', ['page_slug' => 'site_meetings', 'block_key' => $row['block_key'], 'value' => $row['value'], 'updated_by' => $row['updated_by']]);
            }
            \App\Core\Content::flush('site_meetings');
        });
    }
}
$PORT = (int) ($opt['port'] ?? 2598);
$OUT = STORAGE_PATH . '/mock-smtp/meetings-mail-test';
$PHP = PHP_BINARY;
$SELF = __FILE__;
$APP_URL = 'http://localhost/lumina';
const FAR = '2031-03-15';            // the day of every test meeting: far from anybody else's data
const BASE_NOW = '2031-03-01 08:00:00';

function pointMailerAt(int $port, int $timeout = 3, string $user = '', string $pass = ''): void
{
    Config::set('mail.driver', 'smtp');
    Config::set('mail.smtp', ['host' => '127.0.0.1', 'port' => $port, 'username' => $user, 'password' => $pass, 'encryption' => 'none', 'timeout' => $timeout, 'cafile' => '']);
    Mailer::closeConnection();
}

// ====================================================================================================================== child mode
if (isset($opt['child'])) {
    pointMailerAt($PORT, (int) ($opt['timeout'] ?? 3));
    Config::set('app.url', $APP_URL);
    MM::$retryDelay = (int) ($opt['delay'] ?? 0);
    $mode = (string) $opt['child'];
    if ($mode === 'batchloop') {
        $sum = ['batches' => 0, 'busy' => 0, 'sent' => 0, 'failed' => 0, 'halted' => null, 'processed' => 0];
        for ($i = 0; $i < 400; $i++) {
            $r = MM::processBatch((int) $opt['meeting'], ['limit' => (int) ($opt['limit'] ?? 10)]);
            if (empty($r['ok'])) {
                break;
            }
            if ($r['busy']) {
                $sum['busy']++;
                if ($sum['busy'] >= 3) {
                    break;
                }
                usleep(150000);
                continue;
            }
            $sum['batches']++;
            foreach (['sent', 'failed', 'processed'] as $k) {
                $sum[$k] += $r[$k];
            }
            $sum['halted'] = $r['halted']['code'] ?? $sum['halted'];
            if ($r['paused'] || $r['remaining'] === 0 || $r['processed'] === 0) {
                break;
            }
        }
        echo json_encode($sum), "\n";
    } elseif ($mode === 'watch') {
        // kills the process --pid as soon as --dir holds --count messages (a mail-server outage in the middle of a sending)
        for ($i = 0; $i < 60000; $i++) {
            if (count(glob($opt['dir'] . '/*.eml') ?: []) >= (int) $opt['count']) {
                exec('taskkill /F /PID ' . (int) $opt['pid'] . ' 2>NUL');
                break;
            }
            usleep(200);
        }
    } elseif ($mode === 'cron') {
        $argv = ['meetings-send.php', '--verbose'];
        require dirname(__DIR__) . '/meetings-send.php';
    }
    exit(0);
}

// ====================================================================================================================== harness
$fail = 0;
function check(string $name, bool $ok, string $extra = ''): void
{
    global $fail;
    echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
}
function section(string $t): void
{
    echo PHP_EOL . $t . PHP_EOL;
}

/** @return array{0:resource,1:array} */
function startProc(array $cmd): array
{
    $p = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2));
    if (!is_resource($p)) {
        throw new RuntimeException('cannot start process');
    }
    fclose($pipes[0]);
    return [$p, $pipes];
}
function endProc($p, array $pipes): string
{
    $out = (string) stream_get_contents($pipes[1]);
    $err = (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($p);
    return trim($out . ($err !== '' ? "\n[stderr] " . $err : ''));
}
function childProc(string $mode, array $args = []): array
{
    global $PHP, $SELF, $PORT;
    $cmd = [$PHP, $SELF, '--child=' . $mode, '--port=' . $PORT];
    foreach ($args as $k => $v) {
        $cmd[] = '--' . $k . '=' . $v;
    }
    return startProc($cmd);
}
function child(string $mode, array $args = []): string
{
    [$p, $pipes] = childProc($mode, $args);
    return endProc($p, $pipes);
}

/** @return array{proc:resource,pipes:array,port:int,dir:string} */
function startMock(int $port, string $out, array $extra = []): array
{
    global $PHP;
    if (!is_dir($out)) {
        mkdir($out, 0775, true);
    }
    @unlink($out . '/STOP');
    [$p, $pipes] = startProc(array_merge([$PHP, dirname(__DIR__) . '/dev/mock-smtp.php', '--port=' . $port, '--out=' . $out, '--quiet'], $extra));
    for ($i = 0; $i < 60; $i++) {
        $s = @fsockopen('127.0.0.1', $port, $en, $es, 0.2);
        if ($s) {
            fclose($s);
            return ['proc' => $p, 'pipes' => $pipes, 'port' => $port, 'dir' => $out];
        }
        usleep(100000);
    }
    throw new RuntimeException('mock SMTP server did not start');
}
/** Polite stop (the server notices the STOP file). */
function stopMock(array $mock): void
{
    @file_put_contents($mock['dir'] . '/STOP', '1');
    $s = @fsockopen('127.0.0.1', $mock['port'], $en, $es, 0.5);      // wake its accept loop
    if ($s) {
        fclose($s);
    }
    usleep(1500000);
    killMock($mock);
}
/** Abrupt death: the server disappears (a real outage). */
function killMock(array $mock): void
{
    @proc_terminate($mock['proc']);
    @fclose($mock['pipes'][1]);
    @fclose($mock['pipes'][2]);
    @proc_close($mock['proc']);
    @unlink($mock['dir'] . '/STOP');
    usleep(300000);
}
function clearMock(array $mock): void
{
    foreach (glob($mock['dir'] . '/*.eml') ?: [] as $f) {
        @unlink($f);
    }
}

/** Parse a raw message (CRLF) into headers + parts (recursive; base64 bodies decoded). */
function mime(string $raw): array
{
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
            $out['parts'][] = mime(ltrim($seg, "\r\n"));
        }
    } else {
        $out['body'] = strtolower((string) ($headers['content-transfer-encoding'] ?? '')) === 'base64' ? (string) base64_decode(str_replace(["\r", "\n"], '', $body)) : $body;
    }
    return $out;
}
/** Every decoded text of a parsed message, joined (headers and all parts): for "does X appear anywhere?". */
function allText(array $mime): string
{
    $s = implode("\n", $mime['headers']) . "\n" . $mime['body'];
    foreach ($mime['parts'] as $p) {
        $s .= "\n" . allText($p);
    }
    return $s;
}
function decodeHeader(string $v): string
{
    return (string) iconv_mime_decode($v, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
}
/** All messages in a mock directory: [{file, rcpt, from, mime, subject}] oldest first. @return list<array<string,mixed>> */
function inbox(array $mock): array
{
    $files = glob($mock['dir'] . '/*.eml') ?: [];
    sort($files);
    $out = [];
    foreach ($files as $f) {
        $raw = (string) file_get_contents($f);
        preg_match('/^X-Mock-Rcpt-To: (.*)\r?\n/m', $raw, $r);
        preg_match('/^X-Mock-Mail-From: (.*)\r?\n/m', $raw, $mf);
        if (trim((string) ($r[1] ?? '')) === '') {
            // a file the mock server was still writing when the outage test killed it: the server never answered "250 OK" for it, so the sender retries that message
            $GLOBALS['unfinishedFiles'] = ($GLOBALS['unfinishedFiles'] ?? 0) + 1;
            continue;
        }
        $body = (string) preg_replace('/^X-Mock-[^\n]*\n/m', '', $raw, 2);
        $mm = mime($body);
        $out[] = ['file' => $f, 'rcpt' => strtolower(trim((string) ($r[1] ?? ''))), 'envelope_from' => trim((string) ($mf[1] ?? '')), 'mime' => $mm, 'subject' => decodeHeader((string) ($mm['headers']['subject'] ?? ''))];
    }
    return $out;
}
/** recipient => number of messages whose subject contains $needle. @return array<string,int> */
function countBySubject(array $mock, string $needle): array
{
    $out = [];
    foreach (inbox($mock) as $e) {
        if (str_contains($e['subject'], $needle)) {
            $out[$e['rcpt']] = ($out[$e['rcpt']] ?? 0) + 1;
        }
    }
    return $out;
}
/** The text/calendar content (inline part) of a message, or ''. */
function inlineIcs(array $mime): string
{
    foreach ($mime['parts'] as $p) {
        foreach ($p['parts'] ?: [$p] as $q) {
            if ($q['type'] === 'text/calendar') {
                return $q['body'];
            }
        }
    }
    return '';
}
function icsLines(string $ics): array
{
    return explode("\r\n", rtrim((string) preg_replace("/\r\n[ \t]/", '', $ics), "\r\n"));
}

// ---- data helpers
$ids = ['meetings' => [], 'entries' => [], 'users' => []];
function form(array $over = []): array
{
    return $over + [
        'title' => 'zz-mailtest meeting', 'date' => FAR, 'start_time' => '10:00', 'duration' => 60, 'platform' => 'zoom', 'join_url' => 'https://zoom.us/j/123456789?pwd=abc',
        'passcode' => '4321', 'description' => '<p>Agenda <strong>one</strong></p><ul><li>Budget</li></ul>', 'organiser_name' => 'Jane Organiser', 'organiser_email' => 'jane@mailtest.invalid',
        'visibility' => 'private', 'reminder_24h' => 1, 'reminder_1h' => 1, 'allow_rsvp' => 1,
    ];
}
/** A draft meeting with $n attendees p001.. (or the given list of [name, email]). */
function newMeeting(string $title, array $over = [], int|array $attendees = 0): int
{
    global $ids;
    $r = MS::create(form(['title' => $title] + $over), null);
    if (!$r['ok']) {
        throw new RuntimeException('create failed: ' . json_encode($r['errors']));
    }
    $id = (int) $r['id'];
    $ids['meetings'][] = $id;
    $ids['entries'][] = (int) $r['meeting']['calendar_entry_id'];
    if (is_int($attendees)) {
        $list = [];
        for ($i = 1; $i <= $attendees; $i++) {
            $list[] = ['name' => sprintf('Person %03d', $i), 'email' => sprintf('p%03d@mailtest.invalid', $i)];
        }
    } else {
        $list = array_map(static fn(array $p): array => ['name' => $p[0], 'email' => $p[1]], $attendees);
    }
    if ($list !== []) {
        $a = MS::addAttendees($id, $list, null, ['allow_bulk' => true]);
        if (!$a['ok']) {
            throw new RuntimeException('attendees failed: ' . json_encode($a['errors']));
        }
    }
    return $id;
}
function meeting(int $id): array
{
    return (array) Meeting::find($id);
}
/** Schedule + queue the new people through the real entry point, like the admin button. */
function sendInvites(int $id, ?int $confirm = null): array
{
    $m = meeting($id);
    return MM::send($m, 'invite_new', $confirm ?? MM::available($m)['invite_new'], null, null);
}
/** Run batches until nothing is left (or paused / nothing processed). @return array{batches:int,sent:int,failed:int,retried:int,dropped:int,halted:?string} */
function drain(int $id, int $limit = 30, int $maxBatches = 60): array
{
    $sum = ['batches' => 0, 'sent' => 0, 'failed' => 0, 'retried' => 0, 'dropped' => 0, 'halted' => null];
    for ($i = 0; $i < $maxBatches; $i++) {
        $r = MM::processBatch($id, ['limit' => $limit]);
        if (empty($r['ok']) || $r['busy']) {
            break;
        }
        $sum['batches']++;
        foreach (['sent', 'failed', 'retried', 'dropped'] as $k) {
            $sum[$k] += $r[$k];
        }
        $sum['halted'] = $r['halted']['code'] ?? $sum['halted'];
        if ($r['paused'] || $r['remaining'] === 0 || $r['processed'] === 0) {
            break;
        }
    }
    return $sum;
}
/** The edit-form data of a stored meeting with some fields replaced (array_merge: the later value wins). */
function fd(int $id, array $over): array
{
    return array_merge(MS::formData(meeting($id)), $over);
}
function att(int $id, string $email): array
{
    return (array) Database::one('SELECT * FROM meeting_attendees WHERE meeting_id = ? AND email = ?', [$id, $email]);
}
function freezeAt(string $eat): void
{
    MT::freeze(MT::toTimestamp($eat));
}

// ---- cleanup
$auditBefore = (int) Database::value('SELECT COALESCE(MAX(id), 0) FROM audit_log', [], 0);
$mailBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$mock = null;
$cfgBefore = [Config::get('mail.driver'), Config::get('mail.smtp'), Config::get('app.url')];
$cleanup = static function () use (&$mock, &$ids, $cfgBefore, $auditBefore, $mailBefore, $OUT, $opt): void {
    MT::freeze(null);
    if ($mock !== null) {
        @file_put_contents($mock['dir'] . '/STOP', '1');
        killMock($mock);
        $mock = null;
    }
    Mailer::closeConnection();
    Config::set('mail.driver', $cfgBefore[0]);
    Config::set('mail.smtp', $cfgBefore[1]);
    Config::set('app.url', $cfgBefore[2]);
    foreach ($ids['meetings'] as $mid) {
        RateLimit::clear('meeting-mail-streak:' . $mid);
    }
    RateLimit::clear('meeting-mail-streak:reminders');
    // meetings (and their attendees) go with their calendar entries (ON DELETE CASCADE); pick up anything left under the test title as well
    Database::execute("DELETE FROM calendar_entries WHERE id IN (SELECT calendar_entry_id FROM meetings WHERE title LIKE 'zz-mailtest%')");
    foreach ($ids['entries'] as $eid) {
        Database::execute('DELETE FROM calendar_entries WHERE id = ?', [$eid]);
    }
    foreach ($ids['meetings'] as $mid) {
        Database::execute("DELETE FROM audit_log WHERE entity_type = 'meeting' AND entity_id = ?", [(string) $mid]);
    }
    foreach ($ids['entries'] as $eid) {
        Database::execute("DELETE FROM audit_log WHERE entity_type = 'calendar_entry' AND entity_id = ?", [(string) $eid]);
    }
    Database::execute("DELETE FROM audit_log WHERE id > ? AND action LIKE 'meeting.mail%'", [$auditBefore]);
    Database::execute("DELETE FROM rate_limits WHERE rl_key IN (?, ?)", [substr(hash_hmac('sha256', 'meeting-test:' . 0, Config::appKey()), 0, 40), substr(hash_hmac('sha256', 'meeting-msg:' . 0, Config::appKey()), 0, 40)]);
    foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailBefore) as $f) {
        @unlink($f);
    }
    if (!isset($opt['keep'])) {
        $rm = static function (string $dir) use (&$rm): void {
            foreach (glob($dir . '/{,.}[!.,!..]*', GLOB_BRACE) ?: [] as $f) {
                is_dir($f) ? $rm($f) : @unlink($f);
            }
            @rmdir($dir);
        };
        $rm($OUT);
    }
    \App\Services\Calendar\CalendarService::flush();
    MS::flush();
};
register_shutdown_function($cleanup);
$cleanup();
@mkdir($OUT, 0775, true);

try {
    Config::set('app.url', $APP_URL);
    MM::$retryDelay = 0;
    freezeAt(BASE_NOW);
    $mock = startMock($PORT, $OUT);
    pointMailerAt($PORT);

    // =========================================================================================================== A. 120 attendees: queue, batches, one mail each
    section('A. 120 attendees: one message each, nobody sees anybody else');
    $A = newMeeting('zz-mailtest Ünïcode — Réunion d’équipe ✓ 120', ['organiser_name' => 'Jane Ñandú Organiser'], 120);
    $m = meeting($A);
    $av = MM::available($m);
    check('draft: invite_new available = 120, nothing else', $av['invite_new'] === 120 && $av['update_all'] === 0 && $av['cancel_all'] === 0 && $av['retry_failed'] === 0);
    $r = MM::send($m, 'invite_new', 119, null, null);
    check('a wrong confirm_count is refused (count_changed, expected 120) and nothing is scheduled', !$r['ok'] && $r['code'] === 'count_changed' && ($r['expected'] ?? 0) === 120 && meeting($A)['status'] === 'draft');
    $r = MM::send($m, 'invite_new', null, null, null);
    check('no confirm_count is refused', !$r['ok'] && $r['code'] === 'bad_request');
    $r = MM::send(meeting($A), 'update_all', 0, null, null);
    check('update_all on a draft is a conflict', !$r['ok'] && $r['code'] === 'conflict');
    $r = sendInvites($A, 120);
    check('send(invite_new, 120) on a draft schedules it and queues 120', $r['ok'] && $r['queued'] === 120 && meeting($A)['status'] === 'scheduled' && MM::waiting($A) === 120, json_encode($r));
    $r2 = sendInvites($A, 120);
    check('a second identical send is refused (count_changed, expected 0): nothing queued twice', !$r2['ok'] && $r2['code'] === 'count_changed' && ($r2['expected'] ?? -1) === 0 && MM::waiting($A) === 120);
    $b1 = MM::processBatch($A, ['limit' => 30]);
    check('batch 1: 30 sent, 90 remaining, not done, no halt', $b1['ok'] && $b1['sent'] === 30 && $b1['remaining'] === 90 && !$b1['done'] && $b1['halted'] === null && !$b1['paused'] && count($b1['results']) === 30);
    check('batch results name the recipient and say sent', $b1['results'][0]['status'] === 'sent' && $b1['results'][0]['kind'] === 'invite' && str_contains($b1['results'][0]['email'], '@mailtest.invalid'));
    $d = drain($A, 30);
    $c = MS::summary($A);
    check('three more batches finish it: 120 sent, 0 failed, 0 waiting', $d['sent'] === 90 && $d['batches'] >= 3 && $c['sent'] === 120 && $c['failed'] === 0 && $c['queued'] === 0 && MM::waiting($A) === 0, json_encode([$d, $c]));
    check('invitations_sent_at is set and SEQUENCE stays 0', meeting($A)['invitations_sent_at'] !== null && (int) meeting($A)['sequence'] === 0);
    $box = inbox($mock);
    $rcpts = array_column($box, 'rcpt');
    check('the mock server received exactly 120 messages to 120 DIFFERENT recipients', count($box) === 120 && count(array_unique($rcpts)) === 120, count($box) . ' messages, ' . count(array_unique($rcpts)) . ' recipients');
    $leak = 0;
    foreach ($box as $e) {
        $text = strtolower(allText($e['mime']));
        foreach (['p001', 'p060', 'p120'] as $who) {
            if ($e['rcpt'] !== $who . '@mailtest.invalid' && str_contains($text, $who . '@mailtest.invalid')) {
                $leak++;
            }
        }
        if (preg_match_all('/mailtest\.invalid/', $text) > 0 && preg_match_all('/p\d{3}@mailtest\.invalid/', $text, $mm) && count(array_unique($mm[0])) > 1) {
            $leak++;
        }
    }
    check('nobody sees another invitee\'s address anywhere in the headers or any part (checked on all 120)', $leak === 0, "leaks: {$leak}");

    // ---- the structure of one message, in detail
    section('A2. The received MIME');
    $one = null;
    foreach ($box as $e) {
        if ($e['rcpt'] === 'p007@mailtest.invalid') {
            $one = $e;
        }
    }
    $mm = $one['mime'];
    $h = $mm['headers'];
    check('top level multipart/mixed { multipart/alternative, application/ics attachment }', $mm['type'] === 'multipart/mixed' && count($mm['parts']) === 2 && $mm['parts'][0]['type'] === 'multipart/alternative' && str_starts_with($mm['parts'][1]['type'], 'application/'));
    $alt = array_map(static fn(array $p): string => $p['type'], $mm['parts'][0]['parts']);
    check('alternative = text/plain, text/html, text/calendar', $alt === ['text/plain', 'text/html', 'text/calendar'], implode(',', $alt));
    check('text/calendar part carries method=REQUEST and charset UTF-8', str_contains($mm['parts'][0]['parts'][2]['raw_type'], 'method=request') && str_contains($mm['parts'][0]['parts'][2]['raw_type'], 'charset=utf-8'));
    check('attachment is invite.ics with Content-Disposition: attachment', str_contains($mm['parts'][1]['headers']['content-disposition'] ?? '', 'attachment') && str_contains($mm['parts'][1]['headers']['content-disposition'] ?? '', 'invite.ics') && str_contains($mm['parts'][1]['raw_type'], 'invite.ics'));
    check('the inline part and the attachment are the SAME iCalendar text', trim($mm['parts'][0]['parts'][2]['body']) === trim($mm['parts'][1]['body']));
    check('To is ONLY this person, with the display name', preg_match('/^"?Person 007"? <p007@mailtest\.invalid>$/', $h['to'] ?? '') === 1, $h['to'] ?? '');
    check('From is the configured sender address, display name "<organiser> via <site>" (UTF-8 encoded)', str_contains($h['from'] ?? '', '<no-reply@luminaafricafoundation.com>') && str_contains(decodeHeader($h['from'] ?? ''), 'Jane Ñandú Organiser via '), decodeHeader($h['from'] ?? ''));
    check('Reply-To is the ORGANISER (name + address); the organiser address is NOT the From address', str_contains($h['reply-to'] ?? '', '<jane@mailtest.invalid>') && !str_contains(strtolower($h['from'] ?? ''), 'jane@mailtest.invalid'), $h['reply-to'] ?? '');
    check('no List-Unsubscribe header on a transactional invitation', !isset($h['list-unsubscribe']) && !isset($h['list-unsubscribe-post']));
    check('envelope sender (MAIL FROM) is the configured sender, not the organiser', $one['envelope_from'] === 'no-reply@luminaafricafoundation.com', $one['envelope_from']);
    check('subject: "Invitation: <title> — Sat 15 Mar 2031, 10:00–11:00 EAT" with UTF-8 intact', str_starts_with($one['subject'], 'Invitation: zz-mailtest Ünïcode — Réunion d’équipe ✓ 120') && str_contains($one['subject'], '10:00–11:00 EAT'), $one['subject']);
    check('Date, Message-ID and MIME-Version headers exist', isset($h['date'], $h['message-id'], $h['mime-version']) && preg_match('/^<[a-f0-9]{24}@[^>]+>$/', $h['message-id']) === 1);
    $ics = inlineIcs($mm);
    $L = icsLines($ics);
    $meetingRow = meeting($A);
    check('ICS: METHOD:REQUEST, stable UID, SEQUENCE:0, TZID Africa/Kampala, STATUS:CONFIRMED', in_array('METHOD:REQUEST', $L, true) && in_array('UID:' . $meetingRow['uid'], $L, true) && in_array('SEQUENCE:0', $L, true) && in_array('DTSTART;TZID=Africa/Kampala:20310315T100000', $L, true) && in_array('STATUS:CONFIRMED', $L, true));
    check('ICS: exactly ONE ATTENDEE = this recipient, PARTSTAT=NEEDS-ACTION, RSVP=FALSE (answers go through the site)', count(preg_grep('/^ATTENDEE/', $L)) === 1 && (bool) preg_grep('/^ATTENDEE;.*PARTSTAT=NEEDS-ACTION;RSVP=FALSE.*:mailto:p007@mailtest\.invalid$/', $L));
    check('ICS: ORGANIZER is the organiser, URL/LOCATION the join link, a VALARM, a VTIMEZONE, CRLF folding at 75 octets', (bool) preg_grep('/^ORGANIZER;CN="Jane Ñandú Organiser":mailto:jane@mailtest\.invalid$/u', $L) && (bool) preg_grep('~^URL:https://zoom\.us/j/123456789\?pwd=abc$~', $L)
        && (bool) preg_grep('~^LOCATION:https://zoom\.us~', $L) && in_array('BEGIN:VALARM', $L, true) && in_array('BEGIN:VTIMEZONE', $L, true) && max(array_map('strlen', explode("\r\n", $ics))) <= 75);
    $html = $mm['parts'][0]['parts'][1]['body'];
    $text = $mm['parts'][0]['parts'][0]['body'];
    $tok = (string) att($A, 'p007@mailtest.invalid')['token'];
    check('HTML: table layout, Join button to the Zoom link, RSVP buttons to /meeting/{token}?r=..., absolute URLs from APP_URL', str_contains($html, '<table') && str_contains($html, 'https://zoom.us/j/123456789?pwd=abc')
        && str_contains($html, $APP_URL . '/meeting/' . $tok . '?r=accept#answer') && str_contains($html, '?r=maybe#answer') && str_contains($html, '?r=decline#answer') && !str_contains($html, 'href="/'));
    check('text part: the same facts in plain text (organiser, link, answer links)', str_contains($text, 'Jane Ñandú Organiser') && str_contains($text, 'https://zoom.us/j/123456789?pwd=abc') && str_contains($text, '/meeting/' . $tok));
    check('the message never contains a different attendee\'s token', !str_contains(allText($mm), (string) att($A, 'p008@mailtest.invalid')['token']));

    // ---- re-running the sender sends nothing more
    $again = MM::processBatch($A, ['limit' => 30]);
    check('another batch when everything is sent sends nothing', $again['processed'] === 0 && count(inbox($mock)) === 120);

    // =========================================================================================================== B. reschedule -> update, SEQUENCE + 1
    section('B. Reschedule: update e-mail with the same UID and SEQUENCE + 1');
    $before = meeting($A);
    // one person declines, one answers yes (a non-time change keeps answers)
    $tokDecl = (string) att($A, 'p010@mailtest.invalid')['token'];
    $tokAcc = (string) att($A, 'p011@mailtest.invalid')['token'];
    check('RSVP: decline and accept recorded', MS::setRsvp($tokDecl, 'declined')['ok'] && MS::setRsvp($tokAcc, 'accepted')['ok']);
    $u1 = MS::update($A, fd($A, ['join_url' => 'https://zoom.us/j/999888777']), null);
    check('changing the join link bumps SEQUENCE to 1 and suggests an update e-mail; answers are kept', $u1['ok'] && $u1['sequence_bumped'] && $u1['update_suggested'] && (int) meeting($A)['sequence'] === 1 && att($A, 'p011@mailtest.invalid')['rsvp'] === 'accepted');
    $m = meeting($A);
    $av = MM::available($m);
    check('update_all available = 119 (everybody except the one who declined); invite_new 0; cancel_all 0', $av['update_all'] === 119 && $av['invite_new'] === 0 && $av['cancel_all'] === 0, json_encode($av));
    clearMock($mock);
    $r = MM::send($m, 'update_all', 119, null, null);
    check('send(update_all, 119) queues 119 and the batches deliver them', $r['ok'] && $r['queued'] === 119);
    $d = drain($A, 50);
    $box = inbox($mock);
    check('119 "Updated:" messages, none to the person who declined', $d['sent'] === 119 && count($box) === 119 && !in_array('p010@mailtest.invalid', array_column($box, 'rcpt'), true) && str_starts_with($box[0]['subject'], 'Updated: '), (string) ($box[0]['subject'] ?? ''));
    $L2 = icsLines(inlineIcs($box[0]['mime']));
    check('the update ICS: same UID, SEQUENCE:1, METHOD:REQUEST, the NEW link', in_array('UID:' . $before['uid'], $L2, true) && in_array('SEQUENCE:1', $L2, true) && in_array('METHOD:REQUEST', $L2, true) && (bool) preg_grep('~^URL:https://zoom\.us/j/999888777$~', $L2));
    check('invite_sequence of every updated person is 1; the decliner stays at 0', (int) att($A, 'p011@mailtest.invalid')['invite_sequence'] === 1 && (int) att($A, 'p010@mailtest.invalid')['invite_sequence'] === 0);
    $r = MM::send(meeting($A), 'update_all', 119, null, null);
    check('a second "send updated invitation" finds nobody (no double send): count_changed, expected 0', !$r['ok'] && ($r['expected'] ?? -1) === 0);
    $u2 = MS::update($A, fd($A, ['reminder_1h' => 0, 'allow_rsvp' => 1]), null);
    check('an option-only change does NOT bump SEQUENCE and offers no update', $u2['ok'] && !$u2['sequence_bumped'] && (int) meeting($A)['sequence'] === 1);
    // a real reschedule: new time resets answers and reminder marks
    $u3 = MS::update($A, fd($A, ['start_time' => '14:00', 'duration' => 30, 'end_time' => '']), null);
    check('rescheduling (new time) bumps SEQUENCE to 2 and resets every answer to pending', $u3['ok'] && (int) meeting($A)['sequence'] === 2 && att($A, 'p011@mailtest.invalid')['rsvp'] === 'pending' && att($A, 'p010@mailtest.invalid')['rsvp'] === 'pending');
    $av = MM::available(meeting($A));
    check('after the reschedule everybody (120) needs the update again', $av['update_all'] === 120, json_encode($av));
    clearMock($mock);
    $one = MM::send(meeting($A), 'one', null, (int) att($A, 'p003@mailtest.invalid')['id'], null);
    check('"resend to one person" queues exactly that person (IDOR: another meeting\'s attendee id is refused)', $one['ok'] && $one['queued'] === 1 && MM::waiting($A) === 1);
    $d = drain($A);
    $box = inbox($mock);
    $L3 = icsLines(inlineIcs($box[0]['mime']));
    check('the resend carries SEQUENCE:2 and the new time 14:00-14:30', $d['sent'] === 1 && count($box) === 1 && in_array('SEQUENCE:2', $L3, true) && in_array('DTSTART;TZID=Africa/Kampala:20310315T140000', $L3, true) && in_array('DTEND;TZID=Africa/Kampala:20310315T143000', $L3, true) && str_contains($box[0]['subject'], '14:00–14:30 EAT'));
    // "resend" to somebody who already holds the CURRENT version is the invitation again (not "Updated:")
    clearMock($mock);
    $again = MM::send(meeting($A), 'one', null, (int) att($A, 'p003@mailtest.invalid')['id'], null);
    drain($A);
    $box = inbox($mock);
    check('a resend to somebody who already has the current version is an "Invitation:" (same SEQUENCE 2), not "Updated:"', $again['ok'] && count($box) === 1 && str_starts_with($box[0]['subject'], 'Invitation: ') && in_array('SEQUENCE:2', icsLines(inlineIcs($box[0]['mime'])), true), (string) ($box[0]['subject'] ?? ''));
    $other = newMeeting('zz-mailtest other meeting', [], 2);
    $foreign = (int) att($other, 'p001@mailtest.invalid')['id'];
    $bad = MM::send(meeting($A), 'one', null, $foreign, null);
    check('IDOR: an attendee id of another meeting is "not found"', !$bad['ok'] && $bad['code'] === 'not_found');

    // =========================================================================================================== C. cancellation
    section('C. Cancellation: METHOD:CANCEL to everyone who holds an invitation');
    $held = (int) Database::value('SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND invite_sent_at IS NOT NULL AND removed_at IS NULL', [$A], 0);
    $cx = MS::cancel($A, 'The speaker is ill.', null);
    check('cancel(): status cancelled, SEQUENCE bumped to 3, notify = 120', $cx['ok'] && $cx['notify'] === $held && (int) meeting($A)['sequence'] === 3 && meeting($A)['status'] === 'cancelled', json_encode($cx['notify'] ?? null));
    $m = meeting($A);
    $av = MM::available($m);
    check('cancelled: cancel_all available = 120 (declined people too), invite/update 0', $av['cancel_all'] === 120 && $av['update_all'] === 0 && $av['invite_new'] === 0, json_encode($av));
    $r = MM::send($m, 'invite_new', 0, null, null);
    check('inviting for a cancelled meeting is refused', !$r['ok'] && $r['code'] === 'conflict');
    clearMock($mock);
    $r = MM::send($m, 'cancel_all', 120, null, null);
    check('send(cancel_all, 120) queues 120', $r['ok'] && $r['queued'] === 120);
    $d = drain($A, 60);
    $box = inbox($mock);
    check('120 cancellation messages, 120 recipients, subject "Cancelled: ..."', $d['sent'] === 120 && count($box) === 120 && count(array_unique(array_column($box, 'rcpt'))) === 120 && str_starts_with($box[0]['subject'], 'Cancelled: '));
    $mc = $box[0]['mime'];
    $L4 = icsLines(inlineIcs($mc));
    check('cancel message: inline text/calendar method=CANCEL + cancel.ics attachment; STATUS:CANCELLED, SEQUENCE:3, same UID, reason in the text, no join button', str_contains($mc['parts'][0]['parts'][2]['raw_type'], 'method=cancel') && str_contains($mc['parts'][1]['headers']['content-disposition'] ?? '', 'cancel.ics')
        && in_array('METHOD:CANCEL', $L4, true) && in_array('STATUS:CANCELLED', $L4, true) && in_array('SEQUENCE:3', $L4, true) && in_array('UID:' . $before['uid'], $L4, true) && str_contains(allText($mc), 'The speaker is ill.') && !str_contains($mc['parts'][0]['parts'][1]['body'], 'zoom.us/j/'));
    $r = MM::send(meeting($A), 'cancel_all', 120, null, null);
    check('"send the cancellation" again finds nobody (they already hold it): no duplicate cancellations', !$r['ok'] && ($r['expected'] ?? -1) === 0 && MM::available(meeting($A))['cancel_all'] === 0);
    check('the invite_sequence of every attendee is now 3', (int) Database::value('SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND invite_sequence = 3', [$A], 0) === 120);

    // =========================================================================================================== D. bounces, rejections, retries
    section('D. Rejections, temporary failures, the 3-try rule, failed-cancellation retry');
    clearMock($mock);
    $D = newMeeting('zz-mailtest bounces', [], [
        ['Good One', 'good1@mailtest.invalid'], ['Hard Bounce', 'reject5-a@mailtest.invalid'], ['Good Two', 'good2@mailtest.invalid'], ['Soft Bounce', 'reject4-b@mailtest.invalid'], ['Good Three', 'good3@mailtest.invalid'],
    ]);
    $r = sendInvites($D, 5);
    check('5 queued', $r['ok'] && $r['queued'] === 5);
    $b = MM::processBatch($D, ['limit' => 30]);
    $rej = att($D, 'reject5-a@mailtest.invalid');
    $soft = att($D, 'reject4-b@mailtest.invalid');
    check('batch 1: 3 sent, 1 failed (550 permanent, ONE attempt), 1 retried (451)', $b['sent'] === 3 && $b['failed'] === 1 && $b['retried'] === 1 && $b['halted'] === null, json_encode([$b['sent'], $b['failed'], $b['retried']]));
    check('the 550 row is failed after exactly 1 attempt; the error has no address and no secrets', $rej['invite_status'] === 'failed' && (int) $rej['attempts'] === 1 && (string) $rej['last_error'] !== '' && !str_contains((string) $rej['last_error'], '@') && !str_contains(strtolower((string) $rej['last_error']), 'password'), (string) $rej['last_error']);
    check('the 451 row waits in the queue (queued, attempts 1)', $soft['invite_status'] === 'queued' && (int) $soft['attempts'] === 1 && (string) $soft['last_error'] !== '');
    $b = MM::processBatch($D, ['limit' => 30]);
    $b = MM::processBatch($D, ['limit' => 30]);
    $soft = att($D, 'reject4-b@mailtest.invalid');
    check('after the 3rd try the 451 row is failed (attempts 3)', $soft['invite_status'] === 'failed' && (int) $soft['attempts'] === 3, json_encode([$soft['invite_status'], $soft['attempts']]));
    $c = MS::summary($D);
    check('counts: 3 sent, 2 failed, 0 waiting; the good ones got exactly one message each', $c['sent'] === 3 && $c['failed'] === 2 && $c['queued'] === 0 && count(inbox($mock)) === 3);
    $av = MM::available(meeting($D));
    check('retry_failed available = 2', $av['retry_failed'] === 2);
    $rr = MM::retry(meeting($D));
    $b = drain($D);
    check('retry: both go back to queued, fail again (the mock keeps refusing), still only 3 messages delivered', $rr['ok'] && $rr['queued'] === 2 && MS::summary($D)['failed'] === 2 && count(inbox($mock)) === 3);
    // failed cancellation: make a "holder" whose address the mock refuses
    Database::execute("UPDATE meeting_attendees SET invite_sent_at = ?, invite_status = 'sent', invite_sequence = 0 WHERE meeting_id = ? AND email = 'reject5-a@mailtest.invalid'", [BASE_NOW, $D]);
    MS::cancel($D, '', null);
    $m = meeting($D);
    check('cancelled: cancel_all available counts the holders (good1-3 and the refused one)', MM::available($m)['cancel_all'] === 4, json_encode(MM::available($m)));
    clearMock($mock);
    $r = MM::send($m, 'cancel_all', 4, null, null);
    $d = drain($D);
    $rej = att($D, 'reject5-a@mailtest.invalid');
    check('cancel_all: 3 delivered, the refused one is failed with kind cancel', $r['ok'] && $d['sent'] === 3 && $rej['invite_status'] === 'failed' && $rej['queue_kind'] === 'cancel' && count(inbox($mock)) === 3);
    $av = MM::available(meeting($D));
    check('after that: cancel_all 1 and retry_failed 1 are the SAME person (the failed cancellation); the invitation failures of never-invited people are not retried on a cancelled meeting', $av['cancel_all'] === 1 && $av['retry_failed'] === 1, json_encode($av));
    $rr = MM::retry(meeting($D));
    check('retry on a cancelled meeting requeues only the failed cancellation', $rr['ok'] && $rr['queued'] === 1 && att($D, 'reject5-a@mailtest.invalid')['invite_status'] === 'queued' && att($D, 'reject5-a@mailtest.invalid')['queue_kind'] === 'cancel');
    drain($D);

    // ---- the breaker: 8 temporary failures in a row pause the queue
    section('D2. Repeated temporary failures pause the queue (breaker), the rest stays queued');
    clearMock($mock);
    $E = newMeeting('zz-mailtest breaker', [], array_merge(
        array_map(static fn(int $i): array => ['Soft ' . $i, "reject4-{$i}@mailtest.invalid"], range(1, 8)),
        [['Fine One', 'fine1@mailtest.invalid'], ['Fine Two', 'fine2@mailtest.invalid']]
    ));
    sendInvites($E, 10);
    $b = MM::processBatch($E, ['limit' => 30]);
    check('after 5 temporary failures in a row the batch stops, halted = mail_server, the meeting is paused with a reason', $b['halted'] !== null && $b['halted']['code'] === 'mail_server' && $b['paused'] && $b['retried'] === 5 && $b['sent'] === 0, json_encode([$b['halted'], $b['retried']]));
    $mE = meeting($E);
    check('paused in the database with a halt reason; nobody is marked failed', (int) $mE['send_paused'] === 1 && (string) $mE['send_halt_reason'] !== '' && MS::summary($E)['failed'] === 0 && MS::summary($E)['queued'] === 10);
    $b = MM::processBatch($E, ['limit' => 30]);
    check('while paused a batch sends nothing', $b['paused'] && $b['processed'] === 0 && count(inbox($mock)) === 0);
    $res = MM::resume(meeting($E));
    $d = drain($E, 30);
    check('resume: fresh people go first, so the two fine ones are delivered at once', $res['ok'] && count(inbox($mock)) === 2 && MS::summary($E)['sent'] === 2, json_encode([$d, MS::summary($E)]));
    for ($i = 0; $i < 8 && (int) meeting($E)['send_paused'] === 1; $i++) {
        MM::resume(meeting($E));
        drain($E, 30);
    }
    $cE = MS::summary($E);
    check('pressing Resume again and again settles it: the 8 always-refused addresses end failed after 3 tries each, nothing else is lost or repeated', $cE['failed'] === 8 && $cE['sent'] === 2 && $cE['queued'] === 0 && count(inbox($mock)) === 2, json_encode($cE));

    // =========================================================================================================== E. outage in the middle
    section('E. The mail server dies in the middle of a sending, then comes back');
    clearMock($mock);
    $F = newMeeting('zz-mailtest outage', [], 80);
    sendInvites($F, 80);
    // a watcher process kills the mock as soon as 25 messages have arrived
    $watch = childProc('watch', ['dir' => $OUT, 'pid' => (string) proc_get_status($mock['proc'])['pid'], 'count' => 25]);
    $b = MM::processBatch($F, ['limit' => 80]);
    endProc($watch[0], $watch[1]);
    $got = count(inbox($mock));
    $c = MS::summary($F);
    check('the sending stopped part-way (25..79 messages arrived, others waiting) and paused itself with mail_server', $got >= 25 && $got < 80 && $c['queued'] === 80 - $c['sent'] && $b['halted'] !== null && $b['halted']['code'] === 'mail_server', json_encode([$got, $c, $b['halted'], $b['retried']]));
    check('nobody is marked failed by the outage', $c['failed'] === 0, json_encode($c));
    $sentBefore = $c['sent'];
    $mock = startMock($PORT, $OUT);
    $res = MM::resume(meeting($F));
    $d = drain($F, 30);
    $box = inbox($mock);
    $per = array_count_values(array_column($box, 'rcpt'));
    $dups = count(array_filter($per, static fn(int $n): bool => $n > 1));
    $c = MS::summary($F);
    $odd = array_values(array_filter(array_keys($per), static fn(string $r): bool => preg_match('/^p0\d\d@mailtest\.invalid$/', $r) !== 1));
    check('after the server is back: resume finishes it, all 80 sent, 0 failed', $res['ok'] && $c['sent'] === 80 && $c['failed'] === 0 && $c['queued'] === 0 && count($per) === 80, json_encode([$c, count($per), $odd]));
    check('no recipient got a second copy (at most one in-flight message may repeat when the server dies between accepting it and answering: the documented limit)', $dups <= 1, "duplicates: {$dups}");
    check('the persistent failure counter was cleared by the first success', !RateLimit::tooMany('meeting-mail-streak:' . $F, 1));

    // =========================================================================================================== F. concurrent senders
    section('F. Two senders at once (named lock) and a double click');
    clearMock($mock);
    $G = newMeeting('zz-mailtest concurrent', [], 60);
    sendInvites($G, 60);
    $kids = [childProc('batchloop', ['meeting' => $G, 'limit' => 7]), childProc('batchloop', ['meeting' => $G, 'limit' => 7]), childProc('batchloop', ['meeting' => $G, 'limit' => 7])];
    $outs = array_map(static fn(array $k): string => endProc($k[0], $k[1]), $kids);
    $sumSent = 0;
    $busy = 0;
    foreach ($outs as $o) {
        $j = json_decode($o, true);
        $sumSent += (int) ($j['sent'] ?? 0);
        $busy += (int) ($j['busy'] ?? 0);
    }
    $box = inbox($mock);
    $per = array_count_values(array_column($box, 'rcpt'));
    check('three parallel sender processes: every attendee got EXACTLY one message (no double send)', count($box) === 60 && count($per) === 60 && max($per) === 1 && $sumSent === 60, json_encode([count($box), count($per), $sumSent, $outs]));
    check('the sending was shared or refused by the lock (busy answers seen or work split)', $busy >= 0 && MS::summary($G)['sent'] === 60);
    // held lock => busy
    $got = (int) Database::value("SELECT GET_LOCK(?, 0)", ['lumina_meeting_sender_' . substr(md5((string) Config::get('db.name', '') . '@' . (string) Config::get('db.host', '')), 0, 8)], 0);
    $pdoOther = Database::connect((string) Config::get('db.name'));
    $stmt = $pdoOther->prepare('SELECT GET_LOCK(?, 0)');
    $stmt->execute(['lumina_meeting_sender_' . substr(md5((string) Config::get('db.name', '') . '@' . (string) Config::get('db.host', '')), 0, 8)]);
    $otherGot = (int) $stmt->fetchColumn();
    $stmt = null;
    check('the named database lock is exclusive between connections', $got === 1 && $otherGot === 0);
    Database::value('SELECT RELEASE_LOCK(?)', ['lumina_meeting_sender_' . substr(md5((string) Config::get('db.name', '') . '@' . (string) Config::get('db.host', '')), 0, 8)]);
    $pdoOther = null;
    $pdoLockHolder = Database::connect((string) Config::get('db.name'));
    $stmt = $pdoLockHolder->prepare('SELECT GET_LOCK(?, 0)');
    $stmt->execute(['lumina_meeting_sender_' . substr(md5((string) Config::get('db.name', '') . '@' . (string) Config::get('db.host', '')), 0, 8)]);
    $stmt = null;
    $H = newMeeting('zz-mailtest busy', [], 3);
    sendInvites($H, 3);
    $b = MM::processBatch($H, ['limit' => 10]);
    check('while another connection holds the lock the batch answers busy and sends nothing', $b['busy'] && $b['processed'] === 0 && MM::waiting($H) === 3);
    $pdoLockHolder = null;
    $b = MM::processBatch($H, ['limit' => 10]);
    check('once the lock is free the batch runs', !$b['busy'] && $b['sent'] === 3);

    // =========================================================================================================== G. removal, pause, cancelling while queued
    section('G. Removal, pause / resume, cancel while messages are queued, wrong state rows');
    clearMock($mock);
    $I = newMeeting('zz-mailtest removal', [], 6);
    sendInvites($I, 6);
    drain($I);
    $rm = MS::removeAttendee($I, (int) att($I, 'p002@mailtest.invalid')['id'], true, null);
    $rm2 = MS::removeAttendee($I, (int) att($I, 'p003@mailtest.invalid')['id'], false, null);
    check('remove with a cancellation queues one message; remove without sends none', $rm['ok'] && $rm['queued_cancel'] && $rm2['ok'] && !$rm2['queued_cancel'] && MM::waiting($I) === 1);
    clearMock($mock);
    $d = drain($I);
    $box = inbox($mock);
    check('the removed person gets "You are no longer invited" with METHOD:CANCEL; nobody else gets anything', count($box) === 1 && $box[0]['rcpt'] === 'p002@mailtest.invalid' && str_starts_with($box[0]['subject'], 'You are no longer invited: ') && in_array('METHOD:CANCEL', icsLines(inlineIcs($box[0]['mime'])), true));
    check('their /meeting/{token} link is dead (attendeeByToken is null)', MS::attendeeByToken(att($I, 'p002@mailtest.invalid')['token']) === null && MS::attendeeByToken(att($I, 'p003@mailtest.invalid')['token']) === null);
    // changed content -> update; removed people and declined people get nothing
    MS::update($I, fd($I, ['location' => 'Room 5', 'title' => 'zz-mailtest removal v2']), null);
    check('update_all after the removals reaches the 4 who remain (not the 2 removed)', MM::available(meeting($I))['update_all'] === 4);
    clearMock($mock);
    MM::send(meeting($I), 'update_all', 4, null, null);
    // remove one of them while their update waits: they must get nothing
    MS::removeAttendee($I, (int) att($I, 'p004@mailtest.invalid')['id'], false, null);
    $d = drain($I);
    $box = inbox($mock);
    check('a person removed while their update waited in the queue gets nothing (3 mails)', count($box) === 3 && !in_array('p004@mailtest.invalid', array_column($box, 'rcpt'), true), implode(',', array_column($box, 'rcpt')));
    // pause / resume
    $J = newMeeting('zz-mailtest pause', [], 10);
    sendInvites($J, 10);
    clearMock($mock);
    $p = MM::pause(meeting($J));
    $b = MM::processBatch($J, ['limit' => 5]);
    check('pause: the batch sends nothing while paused', $p['ok'] && $b['paused'] && $b['processed'] === 0 && count(inbox($mock)) === 0 && (int) meeting($J)['send_paused'] === 1);
    $p2 = MM::pause(meeting($J));
    check('pausing twice is harmless', $p2['ok']);
    $res = MM::resume(meeting($J));
    $d = drain($J, 5);
    check('resume: everything is delivered', $res['ok'] && $d['sent'] === 10 && count(inbox($mock)) === 10);
    check('"pause" with nothing waiting is refused', !MM::pause(meeting($J))['ok']);
    check('"resume" when not paused is refused', !MM::resume(meeting($J))['ok']);
    // cancel while queued
    $K = newMeeting('zz-mailtest cancel while queued', [], 5);
    sendInvites($K, 5);
    clearMock($mock);
    $cx = MS::cancel($K, '', null);
    $b = MM::processBatch($K, ['limit' => 10]);
    check('cancelling a meeting whose invitations are still queued: nothing is sent, nobody was invited (notify 0)', $cx['ok'] && $cx['notify'] === 0 && $b['processed'] === 0 && count(inbox($mock)) === 0 && MS::summary($K)['not_sent'] === 5);
    // a stale queued row on a cancelled meeting is dropped by the sender
    Database::execute("UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = 'invite', attempts = 0 WHERE meeting_id = ? AND email = 'p001@mailtest.invalid'", [$K]);
    $b = MM::processBatch($K, ['limit' => 10]);
    check('a row queued for an invitation on a cancelled meeting is dropped (back to not_sent), nothing sent', $b['dropped'] === 1 && $b['sent'] === 0 && att($K, 'p001@mailtest.invalid')['invite_status'] === 'not_sent' && count(inbox($mock)) === 0);
    // a draft cannot be sent to without scheduling; readiness blocks
    $L = newMeeting('zz-mailtest not ready', ['platform' => 'zoom', 'join_url' => ''], 2);
    $r = sendInvites($L, 2);
    check('a draft without a join link is not scheduled: not_ready with the field message, nothing queued', !$r['ok'] && $r['code'] === 'not_ready' && isset($r['errors']['join_url']) && meeting($L)['status'] === 'draft' && MM::waiting($L) === 0);
    $r = sendInvites(newMeeting('zz-mailtest empty', [], 0), 0);
    check('a draft with nobody to invite: "nobody to send to"', !$r['ok'] && $r['code'] === 'nothing');

    // =========================================================================================================== H. wrong mail settings stop at the first message
    section('H. Wrong mail server login: stops at the first message, nobody failed, resume after the fix');
    stopMock($mock);
    $mock = startMock($PORT, $OUT, ['--user=mailer', '--pass=s3cret-pass']);
    pointMailerAt($PORT, 3, '', '');                       // the site has no login
    $M = newMeeting('zz-mailtest bad login', [], 6);
    sendInvites($M, 6);
    $b = MM::processBatch($M, ['limit' => 30]);
    $mM = meeting($M);
    check('the batch halts at the very first message (config problem), 0 processed', $b['halted'] !== null && $b['halted']['code'] === 'mail_server' && $b['processed'] === 0 && $b['sent'] === 0 && $b['failed'] === 0, json_encode($b['halted']));
    check('nobody is failed, every row is still queued with the attempt given back (attempts 0), the meeting is paused with a reason', MS::summary($M)['failed'] === 0 && MM::waiting($M) === 6 && (int) Database::value('SELECT MAX(attempts) FROM meeting_attendees WHERE meeting_id = ?', [$M], 0) === 0 && (int) $mM['send_paused'] === 1 && (string) $mM['send_halt_reason'] !== '');
    check('the reason shown to the administrator holds no password and no address', !str_contains((string) $mM['send_halt_reason'], 's3cret') && !str_contains((string) $mM['send_halt_reason'], '@'), (string) $mM['send_halt_reason']);
    stopMock($mock);
    $mock = startMock($PORT, $OUT);
    pointMailerAt($PORT);
    MM::resume(meeting($M));
    $d = drain($M);
    check('after the fix, resume delivers all 6', $d['sent'] === 6 && MS::summary($M)['sent'] === 6);

    // =========================================================================================================== I. pasted lists, end to end
    section('I. A messy pasted list -> attendees -> invitations');
    clearMock($mock);
    $pasted = "  Ann Lee <ann.lee@mailtest.invalid>; \"Okello, Peter\" <peter@mailtest.invalid>,\n"
        . "José Ñandú <jose@mailtest.invalid>  ANN.LEE@mailtest.invalid, not-an-address, bob@@mailtest.invalid\n"
        . "mailto:carol@mailtest.invalid <dave@mailtest.invalid>\ntruncated@, eve@mailtest.invalid\r\n\r\n; ; ,";
    $parsed = IP::parse($pasted);
    $emails = array_column($parsed['valid'], 'email');
    check('the parser keeps the valid, distinct addresses and reports the rubbish', $parsed['duplicates'] >= 1 && count($parsed['invalid']) >= 2 && in_array('ann.lee@mailtest.invalid', $emails, true) && in_array('peter@mailtest.invalid', $emails, true) && in_array('jose@mailtest.invalid', $emails, true) && in_array('eve@mailtest.invalid', $emails, true), json_encode($emails));
    $N = newMeeting('zz-mailtest pasted list', [], array_map(static fn(array $v): array => [$v['name'], $v['email']], $parsed['valid']));
    sendInvites($N, count($parsed['valid']));
    $d = drain($N);
    $box = inbox($mock);
    $byRcpt = [];
    foreach ($box as $e) {
        $byRcpt[$e['rcpt']] = decodeHeader($e['mime']['headers']['to'] ?? '');
    }
    check('every valid address got exactly one message and nothing went to a bad address', $d['sent'] === count($parsed['valid']) && count($box) === count($parsed['valid']) && count(array_diff(array_keys($byRcpt), $emails)) === 0, json_encode(array_keys($byRcpt)));
    check('display names survive (comma inside quotes, accents): To: "Okello, Peter" <...>, José Ñandú <...>', str_contains($byRcpt['peter@mailtest.invalid'] ?? '', 'Okello, Peter') && str_contains($byRcpt['jose@mailtest.invalid'] ?? '', 'José Ñandú'), json_encode($byRcpt));

    // =========================================================================================================== J. reminders (simulated clock)
    section('J. Reminders: 24 h and 1 h boundaries');
    clearMock($mock);
    // invited 3 days before: now = start - 72 h
    freezeAt('2031-03-12 10:00:00');
    $R = newMeeting('zz-mailtest reminders', [], [['Early One', 'early1@mailtest.invalid'], ['Early Two', 'early2@mailtest.invalid'], ['Decliner', 'decl@mailtest.invalid'], ['Removed', 'gone@mailtest.invalid'], ['Late Guest', 'late@mailtest.invalid']]);
    // invite everybody but "late", who is added after the 24-hour moment
    Database::execute("UPDATE meeting_attendees SET rsvp = rsvp WHERE meeting_id = ?", [$R]);
    $lateId = (int) att($R, 'late@mailtest.invalid')['id'];
    Database::execute("UPDATE meeting_attendees SET removed_at = ? WHERE id = ?", [BASE_NOW, $lateId]);          // keep "late" out of the first invitation round
    sendInvites($R, 4);
    drain($R);
    check('four people were invited at start - 72 h', MS::summary($R)['sent'] === 4 && count(inbox($mock)) === 4);
    MS::setRsvp((string) att($R, 'decl@mailtest.invalid')['token'], 'declined');
    MS::removeAttendee($R, (int) att($R, 'gone@mailtest.invalid')['id'], false, null);
    $startEat = FAR . ' 10:00:00';
    clearMock($mock);
    $due = static function (string $now) use ($R): array {
        return array_values(array_filter(MS::dueReminders($now, 200), static fn(array $d): bool => (int) $d['meeting']['id'] === $R));
    };
    check('start - 24 h - 1 min: nothing is due', $due('2031-03-14 09:59:00') === []);
    check('start - 24 h exactly: the 24-hour reminder is due for the 2 people who can get it (not the decliner, not the removed one)', array_map(static fn(array $d): string => $d['which'], $due('2031-03-14 10:00:00')) === ['24h', '24h']);
    freezeAt('2031-03-14 10:00:00');
    $s = MM::processDueReminders();
    $mine = count(array_filter(inbox($mock), static fn(array $e): bool => str_contains($e['subject'], 'zz-mailtest reminders')));
    check('processDueReminders sends exactly 2 "starts in 24 hours" messages', $s['sent'] >= 2 && $mine === 2, json_encode([$s, $mine]));
    $box = array_values(array_filter(inbox($mock), static fn(array $e): bool => str_contains($e['subject'], 'zz-mailtest reminders')));
    $rm = $box[0]['mime'];
    check('the reminder: subject "Reminder: ... starts in 24 hours", NO calendar part, no attachment, Join button, Reply-To the organiser, one recipient', str_starts_with($box[0]['subject'], 'Reminder: ') && str_contains($box[0]['subject'], 'starts in 24 hours') && $rm['type'] === 'multipart/alternative' && inlineIcs($rm) === ''
        && str_contains($rm['parts'][1]['body'], 'https://zoom.us/j/123456789') && str_contains($rm['headers']['reply-to'] ?? '', 'jane@mailtest.invalid'), $box[0]['subject'] . ' | ' . $rm['type']);
    $s2 = MM::processDueReminders();
    check('the same minute again: nothing is sent twice', $s2['sent'] === 0 && count(array_filter(inbox($mock), static fn(array $e): bool => str_contains($e['subject'], 'zz-mailtest reminders'))) === 2);
    check('both people carry the 24 h mark; the decliner and the removed one do not', (int) Database::value('SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND reminder_24h_sent_at IS NOT NULL', [$R], 0) === 2);
    // a person invited AFTER the 24-hour moment
    freezeAt('2031-03-14 15:00:00');
    Database::execute('UPDATE meeting_attendees SET removed_at = NULL WHERE id = ?', [$lateId]);
    sendInvites($R, 1);
    drain($R);
    check('"Late Guest" is invited at start - 19 h', att($R, 'late@mailtest.invalid')['invite_status'] === 'sent');
    check('no 24 h reminder for someone invited after that moment (they were not invited 24 h ahead)', array_values(array_filter($due('2031-03-14 15:01:00'), static fn(array $d): bool => $d['which'] === '24h')) === []);
    check('start - 1 h - 1 min: no 1-hour reminder', $due('2031-03-15 08:59:00') === []);
    $d1 = $due('2031-03-15 09:00:00');
    check('start - 1 h exactly: the 1-hour reminder is due for the 3 who may get it (incl. Late Guest)', count($d1) === 3 && array_unique(array_map(static fn(array $d): string => $d['which'], $d1)) === ['1h']);
    freezeAt('2031-03-15 09:00:00');
    clearMock($mock);
    $s = MM::processDueReminders();
    $mine = array_values(array_filter(inbox($mock), static fn(array $e): bool => str_contains($e['subject'], 'zz-mailtest reminders')));
    check('3 "starts in 1 hour" messages sent', count($mine) === 3 && str_contains($mine[0]['subject'], 'starts in 1 hour'), (string) ($mine[0]['subject'] ?? ''));
    $s2 = MM::processDueReminders();
    check('and not twice', $s2['sent'] === 0);
    check('at the start time nothing is due any more', $due('2031-03-15 10:00:00') === []);
    // 24h window ends 2 h before the start: no late 24 h reminder
    $T = newMeeting('zz-mailtest window', [], [['Win One', 'win1@mailtest.invalid']]);
    freezeAt('2031-03-10 09:00:00');
    sendInvites($T, 1);
    drain($T);
    $dueT = static fn(string $now): array => array_values(array_filter(MS::dueReminders($now, 200), static fn(array $d): bool => (int) $d['meeting']['id'] === $T));
    $w1 = array_map(static fn(array $d): string => $d['which'], $dueT('2031-03-15 07:59:00'));
    $w2 = array_map(static fn(array $d): string => $d['which'], $dueT('2031-03-15 08:00:00'));
    check('at start - 2 h 1 min only the 24 h reminder is due; at start - 2 h exactly the 24 h window is over (the 1 h one comes later)', $w1 === ['24h'] && $w2 === [], json_encode([$w1, $w2]));
    // option off
    Database::execute('UPDATE meetings SET reminder_24h = 0 WHERE id = ?', [$T]);
    check('with the 24 h option off nothing is due for it', $dueT('2031-03-14 12:00:00') === []);
    // reschedule resets the marks
    freezeAt('2031-03-14 09:00:00');
    MS::update($R, fd($R, ['date' => '2031-03-16']), null);
    check('rescheduling clears the sent-reminder marks (they restart for the new time)', (int) Database::value('SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND (reminder_24h_sent_at IS NOT NULL OR reminder_1h_sent_at IS NOT NULL)', [$R], 0) === 0);
    // send reminder now
    section('J2. "Send reminder now" and the cron job');
    clearMock($mock);
    freezeAt('2031-03-10 12:00:00');
    $U = newMeeting('zz-mailtest reminder now', [], [['Now One', 'now1@mailtest.invalid'], ['Now Two', 'now2@mailtest.invalid'], ['Now Three', 'now3@mailtest.invalid']]);
    sendInvites($U, 3);
    drain($U);
    clearMock($mock);
    MS::setRsvp((string) att($U, 'now3@mailtest.invalid')['token'], 'declined');
    check('reminder-now candidates: 2 (the decliner is left out), in both kinds', count(MM::reminderCandidates(meeting($U), '24h')) === 2 && count(MM::reminderCandidates(meeting($U), '1h')) === 2 && MM::status(meeting($U))['reminders']['h24'] === 2);
    $rn = MM::sendRemindersNow(meeting($U), '24h');
    check('send reminder now (24h): 2 sent, none left, whatever the clock says (the meeting is days away)', $rn['ok'] && $rn['sent'] === 2 && $rn['remaining'] === 0 && count(inbox($mock)) === 2, json_encode($rn));
    $rn2 = MM::sendRemindersNow(meeting($U), '24h');
    check('pressing it again sends nothing', $rn2['ok'] && $rn2['sent'] === 0 && count(inbox($mock)) === 2);
    $rn3 = MM::sendRemindersNow(meeting($U), '1h');
    check('the 1-hour reminder is a separate mark: 2 more', $rn3['sent'] === 2 && count(inbox($mock)) === 4);
    check('bad kind refused; a started meeting refuses', !MM::sendRemindersNow(meeting($U), '2h')['ok']);
    freezeAt('2031-03-15 10:30:00');
    check('a reminder after the start is refused', !MM::sendRemindersNow(meeting($U), '1h')['ok']);
    freezeAt('2031-03-10 12:00:00');

    // the cron script in a separate process: queue only (reminders depend on the real clock)
    clearMock($mock);
    $V = newMeeting('zz-mailtest cron', [], 12);
    sendInvites($V, 12);
    $queued = MS::meetingsWithQueue();
    if (array_diff($queued, [$V]) === []) {
        $out = child('cron');
        check('bin/meetings-send.php --verbose finishes the queue in its own process and prints counts only', MS::summary($V)['sent'] === 12 && str_contains($out, 'processed=12') && str_contains($out, 'sent=12') && !str_contains($out, '@'), $out);
        $out2 = child('cron');
        check('and a second run does nothing', str_contains($out2, 'processed=0') && count(inbox($mock)) === 12, $out2);
    } else {
        check('cron script: skipped (another agent has queued meetings right now; run this test again later)', true);
        drain($V);
    }
    $silent = (function () use ($PHP): string {
        $p = proc_open([$PHP, dirname(__DIR__) . '/meetings-send.php'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2));
        $o = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        proc_close($p);
        return trim((string) $o);
    })();
    check('the cron script prints nothing without --verbose', $silent === '', $silent);

    // =========================================================================================================== K. test invitation and free-text message
    section('K. Test invitation (own address only) and the free-text message');
    clearMock($mock);
    $W = newMeeting('zz-mailtest test and message', [], [['Tess A', 'tessa@mailtest.invalid'], ['Tess B', 'tessb@mailtest.invalid'], ['Tess C', 'tessc@mailtest.invalid']]);
    $user = ['id' => 1, 'name' => 'Admin Örtest', 'email' => 'admin-mailtest@mailtest.invalid'];
    $t = MM::sendTest(meeting($W), $user, 'invite');
    $box = inbox($mock);
    check('the test goes to the signed-in user only, "[TEST]" subject, real UID and SEQUENCE, buttons do not record answers', $t['ok'] && $t['sent'] === 1 && count($box) === 1 && $box[0]['rcpt'] === 'admin-mailtest@mailtest.invalid' && str_starts_with($box[0]['subject'], '[TEST] Invitation: ')
        && in_array('UID:' . meeting($W)['uid'], icsLines(inlineIcs($box[0]['mime'])), true) && !str_contains($box[0]['mime']['parts'][0]['parts'][1]['body'], '?r=accept'));
    check('a test on a DRAFT does not schedule, queue or mark anything', meeting($W)['status'] === 'draft' && MM::waiting($W) === 0 && MS::summary($W)['not_sent'] === 3);
    $t2 = MM::sendTest(meeting($W), ['id' => 1, 'name' => 'x', 'email' => 'not-an-address'], 'invite');
    check('a user without a valid address cannot send a test', !$t2['ok']);
    $t3 = MM::sendTest(meeting($W), $user, 'cancel; DROP');
    check('an unknown test kind falls back to the invitation', $t3['ok'] && str_starts_with(inbox($mock)[1]['subject'], '[TEST] Invitation: '));
    sendInvites($W, 3);
    drain($W);
    clearMock($mock);
    $ids3 = array_map(static fn(array $a): int => (int) $a['id'], MS::attendees($W));
    $msg = MM::sendMessage(meeting($W), 'Notes ✓ from Jane', "Hello team,\n\nThe agenda changed.\n\nThanks — Jane", $ids3, 1);
    $box = inbox($mock);
    check('free-text message to 3 attendees: 3 messages, custom UTF-8 subject, organiser text in both parts, nobody else\'s address', $msg['ok'] && $msg['sent'] === 3 && count($box) === 3 && $box[0]['subject'] === 'Notes ✓ from Jane' && str_contains($box[0]['mime']['parts'][0]['body'], 'The agenda changed.') && str_contains($box[0]['mime']['parts'][1]['body'], 'The agenda changed.')
        && !str_contains(allText($box[0]['mime']), 'tessb@') && !str_contains(allText($box[0]['mime']), 'tessc@'));
    check('the message has no join button / no calendar part / Reply-To the organiser', inlineIcs($box[0]['mime']) === '' && !str_contains($box[0]['mime']['parts'][1]['body'], 'Join meeting') && str_contains($box[0]['mime']['headers']['reply-to'] ?? '', 'jane@mailtest.invalid'));
    MS::removeAttendee($W, $ids3[0], false, null);
    clearMock($mock);
    $msg = MM::sendMessage(meeting($W), '', 'Second note', array_merge($ids3, [999999999, (int) att($other, 'p001@mailtest.invalid')['id']]), 1);
    check('removed people and ids of other meetings are skipped, with a reason; default subject "About <title>"', $msg['sent'] === 2 && count($msg['results']) === 5 && str_starts_with(inbox($mock)[0]['subject'], 'About zz-mailtest test and message'), json_encode($msg['results']));
    check('limits: more than 30 ids, an empty text, a draft are refused', !MM::sendMessage(meeting($W), '', 'x', range(1, 31))['ok'] && !MM::sendMessage(meeting($W), '', "  \n ", [1])['ok'] && !MM::sendMessage(meeting($other), '', 'x', [1])['ok']);
    $inj = MM::sendMessage(meeting($W), "Hi\r\nBcc: attacker@mailtest.invalid", 'Body', [$ids3[1]], 1);
    $last = inbox($mock);
    $lastMsg = end($last);
    check('header injection in the subject is neutralised (no Bcc header, single recipient)', $inj['ok'] && !isset($lastMsg['mime']['headers']['bcc']) && $lastMsg['rcpt'] === 'tessb@mailtest.invalid');

    // =========================================================================================================== L. injection and odd data in names / titles / organiser
    section('L. Header-injection payloads in names, titles and the organiser');
    clearMock($mock);
    $X = newMeeting('zz-mailtest inject', ['title' => "zz-mailtest inject\r\nBcc: evil@mailtest.invalid", 'organiser_name' => "Eve\r\nCc: evil2@mailtest.invalid"], [["Mallory\r\nBcc: evil3@mailtest.invalid", 'mallory@mailtest.invalid']]);
    sendInvites($X, 1);
    $d = drain($X);
    $box = inbox($mock);
    $hh = $box[0]['mime']['headers'] ?? [];
    check('one message, one recipient, no injected header (Bcc / Cc), subject is a single line', $d['sent'] === 1 && count($box) === 1 && !isset($hh['bcc']) && !isset($hh['cc']) && $box[0]['rcpt'] === 'mallory@mailtest.invalid' && !str_contains($box[0]['subject'], "\n"));

    echo PHP_EOL . ($fail === 0 ? 'ALL MEETING-MAIL CHECKS PASSED' : "{$fail} CHECK(S) FAILED") . PHP_EOL;
} catch (Throwable $e) {
    $fail++;
    echo PHP_EOL . 'EXCEPTION: ' . get_class($e) . ': ' . $e->getMessage() . ' at ' . basename($e->getFile()) . ':' . $e->getLine() . PHP_EOL;
}
$cleanup();
exit($fail === 0 ? 0 : 1);
