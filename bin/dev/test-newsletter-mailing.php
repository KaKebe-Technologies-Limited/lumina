<?php
/**
 * END-TO-END TEST of the newsletter e-mailing side (queue, batch sender, retries, pause / resume / cancel, unsubscribe mid-campaign, restart, concurrency,
 * cron script, message format) against the throw-away mock SMTP server. Development only; it creates and removes its own data:
 * issues "zz-mailtest-*", subscribers "*@mailtest.invalid", mock output in storage/mock-smtp/mailing-test, matching audit entries.
 *
 *   /c/xampp/php/php.exe bin/dev/test-newsletter-mailing.php        (needs the local Apache for the one-click unsubscribe check: http://localhost/lumina/)
 *
 * Nothing leaves the machine: the test points the mailer at 127.0.0.1 (mock server started by this script). OWNER: MAILING agent.
 * The same file is used as its own child process (--child=...) to prove behaviour across separate PHP processes.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Mailer;
use App\Models\Newsletter;
use App\Services\Newsletter\NewsletterContent as NC;
use App\Services\Newsletter\NewsletterMailer as NM;
use App\Services\Newsletter\NewsletterService as NS;

if (Config::get('app.env') !== 'local') {
    exit("Refusing to run outside APP_ENV=local.\n");
}

$opt = [];
foreach (array_slice($argv, 1) as $a) {
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $m) === 1) {
        $opt[$m[1]] = $m[2] ?? '1';
    }
}
$PORT = (int) ($opt['port'] ?? 2597);
$OUT = STORAGE_PATH . '/mock-smtp/mailing-test';
$PHP = PHP_BINARY;
$SELF = __FILE__;

function pointMailerAt(int $port, int $timeout = 3): void
{
    Config::set('mail.driver', 'smtp');
    Config::set('mail.smtp', ['host' => '127.0.0.1', 'port' => $port, 'username' => '', 'password' => '', 'encryption' => 'none', 'timeout' => $timeout, 'cafile' => '']);
    Mailer::closeConnection();
}

// ====================================================================================================================== child mode
if (isset($opt['child'])) {
    pointMailerAt($PORT, (int) ($opt['timeout'] ?? 3));
    NM::$retryDelay = (int) ($opt['delay'] ?? 0);
    $mode = (string) $opt['child'];
    if ($mode === 'queue') {
        $r = NM::queue((int) $opt['issue'], (int) $opt['count']);
        echo json_encode(['ok' => $r['ok'], 'code' => $r['code'] ?? null]), "\n";
    } elseif ($mode === 'batchloop') {
        $sum = ['batches' => 0, 'busy' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0, 'status' => ''];
        for ($i = 0; $i < 400; $i++) {
            $r = NM::processBatch((int) $opt['issue'], ['limit' => (int) ($opt['limit'] ?? 10)]);
            if (!$r['ok']) {
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
            foreach (['sent', 'failed', 'skipped'] as $k) {
                $sum[$k] += $r[$k];
            }
            $sum['status'] = $r['status'];
            if ($r['idle'] || $r['done'] || $r['halted'] !== null || $r['processed'] === 0) {
                break;
            }
            if (isset($opt['stop-after']) && $sum['batches'] >= (int) $opt['stop-after']) {
                break;
            }
        }
        echo json_encode($sum), "\n";
    } elseif ($mode === 'cron') {
        $argv = ['newsletter-send.php', '--verbose'];
        require dirname(__DIR__) . '/newsletter-send.php';
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
function child(string $mode, array $args = []): string
{
    global $PHP, $SELF, $PORT;
    $cmd = [$PHP, $SELF, '--child=' . $mode, '--port=' . $PORT];
    foreach ($args as $k => $v) {
        $cmd[] = '--' . $k . '=' . $v;
    }
    [$p, $pipes] = startProc($cmd);
    return endProc($p, $pipes);
}
function childrenParallel(string $mode, array $args, int $n): array
{
    global $PHP, $SELF, $PORT;
    $procs = [];
    for ($i = 0; $i < $n; $i++) {
        $cmd = [$PHP, $SELF, '--child=' . $mode, '--port=' . $PORT];
        foreach ($args as $k => $v) {
            $cmd[] = '--' . $k . '=' . $v;
        }
        $procs[] = startProc($cmd);
    }
    return array_map(static fn(array $pp): string => endProc($pp[0], $pp[1]), $procs);
}

function startMock(int $port, string $out): array
{
    global $PHP;
    if (!is_dir($out)) {
        mkdir($out, 0775, true);
    }
    [$p, $pipes] = startProc([$PHP, dirname(__DIR__) . '/dev/mock-smtp.php', '--port=' . $port, '--out=' . $out, '--quiet']);
    for ($i = 0; $i < 60; $i++) {
        $s = @fsockopen('127.0.0.1', $port, $en, $es, 0.2);
        if ($s) {
            fclose($s);
            return [$p, $pipes];
        }
        usleep(100000);
    }
    throw new RuntimeException('mock SMTP server did not start');
}
function stopMock(array $mock, string $out): void
{
    @file_put_contents($out . '/STOP', '1');
    $s = @fsockopen('127.0.0.1', (int) $GLOBALS['PORT'], $en, $es, 0.5);      // wake its accept loop
    if ($s) {
        fclose($s);
    }
    usleep(1500000);
    @proc_terminate($mock[0]);
    @fclose($mock[1][1]);
    @fclose($mock[1][2]);
    @proc_close($mock[0]);
}

/** Unfolded header map + decoded text / html parts of a mock .eml. @return array{h:array<string,string>,text:string,html:string,rcpt:string} */
function readEml(string $file): array
{
    $raw = (string) file_get_contents($file);
    [$head, $body] = array_pad(preg_split("/\r?\n\r?\n/", $raw, 2) ?: [], 2, '');
    $head = (string) preg_replace("/\r?\n[ \t]+/", ' ', $head);
    $h = [];
    foreach (preg_split("/\r?\n/", $head) ?: [] as $line) {
        if (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $h[strtolower(trim($k))] = trim($v);
        }
    }
    $text = '';
    $html = '';
    if (preg_match_all('~Content-Type: text/(plain|html); charset=UTF-8\r?\nContent-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--lumina_)~s', $body, $m, PREG_SET_ORDER)) {
        foreach ($m as $part) {
            $dec = (string) base64_decode(preg_replace('/\s+/', '', $part[2]) ?? '', true);
            if ($part[1] === 'plain') {
                $text = $dec;
            } else {
                $html = $dec;
            }
        }
    }
    return ['h' => $h, 'text' => $text, 'html' => $html, 'rcpt' => strtolower(trim((string) ($h['x-mock-rcpt-to'] ?? ''), '<>'))];
}
/** recipient => number of messages, for messages whose decoded subject contains $needle. @return array<string,int> */
function mailsBySubject(string $dir, string $needle): array
{
    $out = [];
    foreach (glob($dir . '/*.eml') ?: [] as $f) {
        $e = readEml($f);
        $subj = (string) iconv_mime_decode($e['h']['subject'] ?? '', ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        if (str_contains($subj, $needle)) {
            $out[$e['rcpt']] = ($out[$e['rcpt']] ?? 0) + 1;
        }
    }
    return $out;
}
function filesBySubject(string $dir, string $needle): array
{
    $out = [];
    foreach (glob($dir . '/*.eml') ?: [] as $f) {
        $e = readEml($f);
        if (str_contains((string) iconv_mime_decode($e['h']['subject'] ?? '', ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8'), $needle)) {
            $out[] = $f;
        }
    }
    return $out;
}

function addSubscriber(string $email, string $status = 'subscribed'): array
{
    $token = bin2hex(random_bytes(20));
    $id = Database::insert('subscribers', ['email' => strtolower($email), 'name' => 'MailTest', 'status' => $status, 'token' => $token, 'source' => 'mailtest', 'subscribed_at' => date('Y-m-d H:i:s')]);
    return ['id' => $id, 'email' => strtolower($email), 'token' => $token];
}
function makeIssue(string $slugKey, string $title, bool $publish = true): int
{
    $content = NC::emptyContent();
    $content['featured'] = ['heading' => 'Building Pathways', 'body_html' => '<p>Lumina Africa Foundation is developing a portfolio of community-centred programs. Read &amp; share.</p><ul><li>One</li><li>Two</li></ul>',
        'image' => ['path' => 'assets/img/site/photo-03.jpg', 'alt' => 'Learners in a classroom', 'caption' => 'A classroom'], 'link' => ['label' => 'Explore Our Planned Programs', 'url' => '/programs']];
    $a = NC::newBlock('article');
    $a['heading'] = 'Visit to Wii Got Quarry Site';
    $a['body_html'] = '<p>Second block &mdash; with <a href="/about">a link</a>.</p>';
    $a['link'] = ['label' => 'Read more', 'url' => '/blog'];
    $q = NC::newBlock('quote');
    $q['body_html'] = '<p>A quote.</p>';
    $q['cite'] = 'Someone, Role';
    $b = NC::newBlock('button');
    $b['button'] = ['label' => 'Partner With Us', 'url' => '/involvement', 'style' => 'primary'];
    $content['blocks'] = [$a, $q, $b, NC::newBlock('divider')];
    $res = NS::create(['title' => $title, 'summary' => 'A summary line.', 'content' => $content], null);
    if (!$res['ok']) {
        throw new RuntimeException('create failed: ' . json_encode($res['errors'] ?? []));
    }
    $id = (int) $res['newsletter']['id'];
    Database::execute('UPDATE newsletters SET slug = ? WHERE id = ?', ['zz-mailtest-' . $slugKey, $id]);
    if ($publish) {
        $p = NS::publish($id, null);
        if (!$p['ok']) {
            throw new RuntimeException('publish failed: ' . json_encode($p));
        }
    }
    return $id;
}
function counts(int $id): array
{
    return NM::counts($id);
}
function row(int $id): array
{
    return Newsletter::find($id) ?? [];
}
function runToEnd(int $id, int $limit = 25): array
{
    $sum = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'batches' => 0, 'last' => []];
    for ($i = 0; $i < 300; $i++) {
        $r = NM::processBatch($id, ['limit' => $limit]);
        if (!$r['ok'] || $r['busy'] || $r['idle']) {
            $sum['last'] = $r;
            break;
        }
        $sum['batches']++;
        foreach (['sent', 'failed', 'skipped'] as $k) {
            $sum[$k] += $r[$k];
        }
        $sum['last'] = $r;
        if ($r['done'] || $r['halted'] !== null || $r['processed'] === 0) {
            break;
        }
    }
    return $sum;
}

$lockName = 'lumina_newsletter_sender_' . substr(md5((string) Config::get('db.name', '') . '@' . (string) Config::get('db.host', '')), 0, 8);

// ====================================================================================================================== setup
echo "Newsletter e-mailing (mock SMTP on 127.0.0.1:{$PORT})\n";
if (is_dir($OUT)) {
    foreach (glob($OUT . '/*') ?: [] as $f) {
        @unlink($f);
    }
}
$auditBefore = (int) Database::value('SELECT COALESCE(MAX(id), 0) FROM audit_log', [], 0);
$logFile = STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log';
$logSizeBefore = is_file($logFile) ? filesize($logFile) : 0;
$issueIds = [];
$mock = startMock($PORT, $OUT);
$cleanup = static function () use (&$issueIds, $OUT, &$mock, $auditBefore): void {
    try {
        stopMock($mock, $OUT);
    } catch (\Throwable) {
    }
    if ($issueIds !== []) {
        $in = implode(',', array_map('intval', $issueIds));
        Database::execute("DELETE FROM audit_log WHERE id > ? AND entity_type = 'newsletter' AND entity_id IN ({$in})", [$auditBefore]);
        Database::execute("DELETE FROM newsletters WHERE id IN ({$in})");
    }
    Database::execute("DELETE FROM newsletters WHERE slug LIKE 'zz-mailtest-%'");
    Database::execute("DELETE FROM subscribers WHERE email LIKE '%@mailtest.invalid'");
    Database::execute("DELETE FROM rate_limits WHERE rl_key IN (?)", [sha1('nl-test-addr:0')]);
    foreach (glob($OUT . '/*') ?: [] as $f) {
        @unlink($f);
    }
    @rmdir($OUT);
};
register_shutdown_function($cleanup);

pointMailerAt($PORT, 3);
NM::$retryDelay = 0;
$others = (int) Database::value("SELECT COUNT(*) FROM subscribers WHERE status = 'subscribed'", [], 0);
$subs = [];
for ($i = 1; $i <= 120; $i++) {
    $subs[] = addSubscriber(sprintf('mt-%04d@mailtest.invalid', $i));
}
$rej5 = [addSubscriber('reject5-a@mailtest.invalid'), addSubscriber('reject5-b@mailtest.invalid')];
$rej4 = addSubscriber('reject4-a@mailtest.invalid');
$mineSubscribed = 123;
$total = $others + $mineSubscribed;
// addresses already in the table that are not valid e-mail addresses (e.g. XSS probes of other tests): the sender must FAIL them cleanly, never mail them
$oi = count(array_filter(Database::all("SELECT email FROM subscribers WHERE status = 'subscribed' AND email NOT LIKE '%@mailtest.invalid'"), static fn(array $r): bool => !Mailer::validEmail((string) $r['email'])));
$F3 = 3 + $oi;       // failures expected in a full campaign: reject5 x2 + reject4 + the invalid ones

// ====================================================================================================================== A. message format + settings + test send
section('A. Message format, settings, test send');
$A = makeIssue('a', 'zz-mailtest A - Ünïcode & <b>tags</b>');
$issueIds[] = $A;
$rowA = row($A);
check('issue A created and published', NS::isLive($rowA) && $rowA['email_status'] === 'none' && (int) $rowA['email_enabled'] === 0, json_encode([$rowA['status'], $rowA['email_enabled']]));
check('e-mail is OFF by default', (int) $rowA['email_enabled'] === 0);

$st = NM::status($rowA);
check('status: cannot start while switched off (reason given)', $st['can_start'] === false && str_contains((string) $st['cannot_start_reason'], 'Switch on'));
check('status: recipients = all subscribed addresses', $st['recipients'] === $total && $st['subscribed'] === $total, "{$st['recipients']} vs {$total}");

$tpl = NM::template($rowA, false, true);
$h = $tpl['html'];
$hPersonal = str_replace('LUMINA_UNSUBSCRIBE_URL_PLACEHOLDER', 'https://example.org/unsubscribe/' . str_repeat('a', 40), $h);
check('html: complete document with preheader and view-in-browser', str_starts_with(ltrim($h), '<!doctype html>') && str_contains($h, 'View in browser') && str_contains($h, 'display:none'));
check('html: placeholder for the unsubscribe link is present before personalising', str_contains($h, 'LUMINA_UNSUBSCRIBE_URL_PLACEHOLDER') && str_contains($tpl['text'], 'LUMINA_UNSUBSCRIBE_URL_PLACEHOLDER'));
check('html: no script, no on* handlers, no style sheets', !preg_match('~<script|\son[a-z]+\s*=|<link\s|<style~i', $h));
check('html: every href/src is absolute (http, https, mailto, tel)', preg_match_all('~\b(?:href|src)="(?!https?://|mailto:|tel:)[^"]*"~i', $hPersonal, $bad) === 0, json_encode($bad[0] ?? []));
check('html: table layout with inline CSS and web-safe serif', str_contains($h, '<table') && str_contains($h, 'style="') && str_contains($h, 'Georgia'));
check('html: title is escaped (no raw <b>)', !str_contains($h, '<b>tags</b>'));
$addr = trim((string) setting('city_country', ''));
check('html: organisation address in the footer', $addr === '' || str_contains($h, e($addr)), $addr);
check('text part: has the content and the browser link', str_contains($tpl['text'], 'Visit to Wii Got Quarry Site') && str_contains($tpl['text'], 'View in browser'));
check('subject is the issue title (no prefix setting)', str_contains($tpl['subject'], 'zz-mailtest A'));
$testTpl = NM::template($rowA, true, false);
check('test e-mail: [TEST] prefix, test notice, no unsubscribe link', str_starts_with($testTpl['subject'], '[TEST] ') && str_contains($testTpl['html'], 'This is a test e-mail') && !str_contains($testTpl['html'], 'LUMINA_UNSUBSCRIBE'));

// settings validation + sanitising
$bad = NM::saveSettings($A, ['email_subject' => str_repeat('x', 191)]);
check('settings: subject over 190 characters is refused', !$bad['ok'] && $bad['code'] === 'validation');
$bad = NM::saveSettings($A, ['email_preheader' => str_repeat('x', 151)]);
check('settings: preview line over 150 characters is refused', !$bad['ok'] && $bad['code'] === 'validation');
$bad = NM::saveSettings($A, ['email_subject' => ['a']]);
check('settings: an array is refused', !$bad['ok'] && $bad['code'] === 'validation');
$inj = NM::saveSettings($A, ['email_subject' => "News\r\nBcc: evil@example.org\n<b>bold</b>", 'email_enabled' => '1', 'email_preheader' => "Line1\nLine2"]);
$rowA = row($A);
check('settings: CR/LF and tags removed from the subject (no header injection)', $inj['ok'] && !str_contains((string) $rowA['email_subject'], "\n") && !str_contains((string) $rowA['email_subject'], '<') && $rowA['email_subject'] === 'News Bcc: evil@example.org bold', (string) $rowA['email_subject']);
check('settings: enabled saved, preheader one line', (int) $rowA['email_enabled'] === 1 && $rowA['email_preheader'] === 'Line1 Line2');
$updatedBefore = $rowA['updated_at'];
$same = NM::saveSettings($A, ['email_subject' => 'zz-mailtest A subject – Ünï']);
check('settings: subject saved', $same['ok'] && row($A)['email_subject'] === 'zz-mailtest A subject – Ünï');
check('settings: saving e-mail settings does not touch the issue version', (int) row($A)['version'] === (int) $rowA['version']);

// test send
$testRcpt = [$subs[0]['email'], 'someone-else@mailtest.invalid'];
$res = NM::sendTest(row($A), $testRcpt, 0);
check('test send: both addresses accepted', count($res) === 2 && $res[0]['ok'] && $res[1]['ok'], json_encode($res));
$testMails = filesBySubject($OUT, '[TEST] zz-mailtest A subject');
check('test send: two [TEST] messages in the mock', count($testMails) === 2, (string) count($testMails));
$byRcpt = [];
foreach ($testMails as $f) {
    $e = readEml($f);
    $byRcpt[$e['rcpt']] = $e;
}
$subMail = $byRcpt[$subs[0]['email']] ?? null;
$strangerMail = $byRcpt['someone-else@mailtest.invalid'] ?? null;
check('test send: a subscriber gets their real unsubscribe link and headers', $subMail !== null && str_contains($subMail['html'], '/unsubscribe/' . $subs[0]['token']) && isset($subMail['h']['list-unsubscribe-post']));
check('test send: a non-subscriber gets no unsubscribe link/headers', $strangerMail !== null && !str_contains($strangerMail['html'], '/unsubscribe/') && !isset($strangerMail['h']['list-unsubscribe']));
check('test send: nothing recorded, campaign untouched', counts($A)['total'] === 0 && row($A)['email_status'] === 'none');
$parse = NM::parseAddresses("a@x.org, b@x.org\nc@x.org;d@x.org e@x.org f@x.org A@x.org");
check('parse: at most five distinct addresses, duplicates ignored, too_many flagged', count($parse['emails']) === 5 && $parse['too_many'] === true);
$parse = NM::parseAddresses("ok@x.org, not-an-address, bad@@x.org, x@y.org\r\nBcc: z@x.org");
check('parse: invalid and injected tokens are counted, not sent', $parse['invalid'] >= 3 && in_array('ok@x.org', $parse['emails'], true), json_encode($parse));

// ====================================================================================================================== B. preconditions
section('B. Queue preconditions');
$draft = makeIssue('draft', 'zz-mailtest draft', false);
$issueIds[] = $draft;
NM::saveSettings($draft, ['email_enabled' => '1']);
$r = NM::queue($draft, NM::recipientCount($draft));
check('queue refuses a DRAFT issue', !$r['ok'] && $r['code'] === 'not_live');
$off = makeIssue('off', 'zz-mailtest switched off', true);
$issueIds[] = $off;
$r = NM::queue($off, NM::recipientCount($off));
check('queue refuses when e-mailing is switched off', !$r['ok'] && $r['code'] === 'not_enabled');
$r = NM::queue($A, $total + 5);
check('queue refuses a wrong confirm count and changes nothing', !$r['ok'] && $r['code'] === 'count_changed' && $r['recipients'] === $total && counts($A)['total'] === 0 && row($A)['email_status'] === 'none');
$r = NM::queue($A, $total - 1);
check('queue refuses a confirm count that is too low', !$r['ok'] && $r['code'] === 'count_changed');
Config::set('mail.smtp.host', '');
$r = NM::queue($A, $total);
check('queue refuses when the SMTP host is not configured', !$r['ok'] && $r['code'] === 'transport');
pointMailerAt($PORT, 3);
// scheduled in the future is not live
$sched = makeIssue('sched', 'zz-mailtest scheduled', false);
$issueIds[] = $sched;
NS::schedule($sched, date('Y-m-d H:i', time() + 3600), null);
NM::saveSettings($sched, ['email_enabled' => '1']);
$r = NM::queue($sched, NM::recipientCount($sched));
check('queue refuses a scheduled issue that is not live yet', !$r['ok'] && $r['code'] === 'not_live');
$r = NM::processBatch($draft);
check('a batch on a campaign that does not exist does nothing', $r['ok'] && $r['idle'] === true && $r['processed'] === 0);
check('retry/pause/cancel on a campaign that does not exist are refused', !NM::pause($A)['ok'] && !NM::cancel($A)['ok'] && !NM::resume($A)['ok'] && !NM::retryFailed($A)['ok']);

// ====================================================================================================================== C. full campaign A
section('C. Full campaign with 120+ subscribers, rejections, retries');
// duplicate start: two PARALLEL processes + a later call
$parallel = childrenParallel('queue', ['issue' => $A, 'count' => $total], 3);
$oks = 0;
foreach ($parallel as $line) {
    $j = json_decode($line, true);
    if (is_array($j) && $j['ok']) {
        $oks++;
    }
}
check('3 parallel start requests: exactly ONE succeeds', $oks === 1, json_encode($parallel));
$r = NM::queue($A, $total);
check('a later start request is refused (already started)', !$r['ok'] && $r['code'] === 'conflict');
$c = counts($A);
check('deliveries = every subscribed address, once', $c['total'] === $total && $c['queued'] === $total, json_encode($c));
check('state is queued and counters are synced', row($A)['email_status'] === 'queued' && (int) row($A)['email_total'] === $total);
$dups = (int) Database::value('SELECT COUNT(*) FROM (SELECT email FROM newsletter_deliveries WHERE newsletter_id = ? GROUP BY email HAVING COUNT(*) > 1) x', [$A], 0);
check('no duplicate rows for an address', $dups === 0);
Database::execute("INSERT IGNORE INTO newsletter_deliveries (newsletter_id, subscriber_id, email, status) SELECT ?, id, email, 'queued' FROM subscribers WHERE status = 'subscribed'", [$A]);
check('the unique key makes a second INSERT IGNORE add nothing', counts($A)['total'] === $total);
$edit = NM::saveSettings($A, ['email_subject' => 'changed']);
check('settings are locked while the campaign runs', !$edit['ok'] && $edit['code'] === 'conflict');
$unpub = NS::unpublish($A, null);
check('the issue cannot be unpublished while the campaign runs', !$unpub['ok'], json_encode($unpub));

$t0 = microtime(true);
$first = NM::processBatch($A, ['limit' => 30]);
check('first batch: 30 handled, state sending', $first['processed'] === 30 && $first['status'] === 'sending' && $first['busy'] === false, json_encode($first));
check('first batch sets started_at', row($A)['email_started_at'] !== null);
$sum = runToEnd($A, 25);
$c = counts($A);
$rowA = row($A);
check('campaign finishes: status sent, completed_at set', $rowA['email_status'] === 'sent' && $rowA['email_completed_at'] !== null && $c['queued'] === 0, json_encode([$rowA['email_status'], $c]));
check('2 permanent rejections (550) failed after ONE attempt each', (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND email LIKE 'reject5-%' AND status = 'failed' AND attempts = 1", [$A], 0) === 2);
$r4 = Database::one("SELECT status, attempts, last_error FROM newsletter_deliveries WHERE newsletter_id = ? AND email LIKE 'reject4-%'", [$A]);
check('temporary failure (451) retried, failed after 3 attempts', $r4 !== null && $r4['status'] === 'failed' && (int) $r4['attempts'] === 3, json_encode($r4));
check('error text is short, no address, no password', $r4 !== null && !str_contains((string) $r4['last_error'], '@') && strlen((string) $r4['last_error']) <= 255 && str_contains((string) $r4['last_error'], '451'), (string) ($r4['last_error'] ?? ''));
check('counters: sent + failed = total (3 rejected + ' . $oi . ' invalid address(es) failed)', $c['failed'] === $F3 && $c['sent'] === $total - $F3 && (int) $rowA['email_sent'] === $c['sent'] && (int) $rowA['email_failed'] === $F3, json_encode($c));
check('an invalid address fails at once with a clear reason (never sent)', $oi === 0 || (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'failed' AND attempts = 1 AND last_error LIKE '%valid%'", [$A], 0) >= $oi);
// the first subscriber also got a [TEST] mail, so real campaign mails are told apart by their subject (no "[TEST]")
$real = [];
$realFiles = [];
foreach (filesBySubject($OUT, 'zz-mailtest A subject') as $f) {
    $e = readEml($f);
    if (!str_starts_with((string) ($e['h']['subject'] ?? ''), '[TEST]') && !str_contains((string) iconv_mime_decode($e['h']['subject'] ?? '', ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8'), '[TEST]')) {
        $real[$e['rcpt']] = ($real[$e['rcpt']] ?? 0) + 1;
        $realFiles[$e['rcpt']] = $f;
    }
}
check('mock received exactly one message per sent address', count($real) === $c['sent'] && max($real) === 1, count($real) . ' vs ' . $c['sent'] . ' max ' . (max($real) ?: 0));
check('rejected addresses received nothing', !isset($real['reject5-a@mailtest.invalid']) && !isset($real['reject4-a@mailtest.invalid']));
$sentRows = array_column(Database::all("SELECT email FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'sent'", [$A]), 'email');
$missing = array_diff($sentRows, array_keys($real));
check("every 'sent' row has a message in the mock", $missing === [], json_encode(array_slice($missing, 0, 3)));
// inspect a real message
$sample = readEml($realFiles[$subs[41]['email']]);
$tok = $subs[41]['token'];
$h = $sample['h'];
check('message: From / Reply-To / Date / Message-ID / MIME', isset($h['from'], $h['reply-to'], $h['date'], $h['message-id']) && str_contains($h['content-type'] ?? '', 'multipart/alternative') && ($h['mime-version'] ?? '') === '1.0', json_encode(array_keys($h)));
check('message: one recipient in To (no Bcc/Cc leak)', ($h['to'] ?? '') === $subs[41]['email'] && !isset($h['cc']) && !isset($h['bcc']), (string) ($h['to'] ?? ''));
check('message: UTF-8 subject encoded (RFC 2047), decodes to the subject', str_contains($h['subject'] ?? '', '=?UTF-8?B?') && str_contains((string) iconv_mime_decode($h['subject'], 0, 'UTF-8'), 'zz-mailtest A subject'), $h['subject'] ?? '');
check('message: List-Unsubscribe has the https link with THIS recipient token + mailto', str_contains($h['list-unsubscribe'] ?? '', '/unsubscribe/' . $tok . '>') && str_contains($h['list-unsubscribe'] ?? '', '<mailto:') && !str_contains($h['list-unsubscribe'] ?? '', "\n"), $h['list-unsubscribe'] ?? '');
check('message: List-Unsubscribe-Post one-click header', ($h['list-unsubscribe-post'] ?? '') === 'List-Unsubscribe=One-Click');
check('message: html and text both contain the recipient unsubscribe link, no placeholder', str_contains($sample['html'], '/unsubscribe/' . $tok) && str_contains($sample['text'], '/unsubscribe/' . $tok) && !str_contains($sample['html'] . $sample['text'], 'LUMINA_UNSUBSCRIBE'));
check('message: no other recipient token leaked into it', !str_contains($sample['html'], $subs[40]['token']) && !str_contains($sample['html'], $subs[42]['token']));
check('message: html is under 102 KB (Gmail clipping)', strlen($sample['html']) < 102400, (string) strlen($sample['html']));
$dupAfter = (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'sent'", [$A], 0);
// final = no re-queue
$r = NM::queue($A, 1);
check("'sent' is final: the issue can never be queued again", !$r['ok'] && $r['code'] === 'conflict');
$r = NM::processBatch($A);
check('a batch after the end does nothing', $r['idle'] === true && counts($A)['sent'] === $dupAfter);
check('settings are locked after sending', !NM::saveSettings($A, ['email_subject' => 'x'])['ok']);

// retry failed
$r = NM::retryFailed($A);
check('retry: the failed deliveries go back to the queue, campaign sending again', $r['ok'] && $r['requeued'] === $F3 && row($A)['email_status'] === 'sending' && counts($A)['queued'] === $F3, json_encode([$r['ok'], $r['requeued'] ?? null]));
check('retry: attempts reset', (int) Database::value("SELECT MAX(attempts) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'queued'", [$A], 0) === 0);
$sum2 = runToEnd($A, 25);
check('retry: still rejected addresses fail again, campaign sent again', row($A)['email_status'] === 'sent' && counts($A)['failed'] === $F3 && counts($A)['sent'] === $total - $F3);
check('retry sent nobody twice', count(array_filter(mailsBySubject($OUT, 'zz-mailtest A subject'), static fn($n) => $n > 1)) === 1, 'only the first subscriber (test + real) may have 2');
check('retry with nothing failed is refused', (function () use ($A) {
    Database::execute("UPDATE newsletter_deliveries SET status = 'skipped', last_error = 'x' WHERE newsletter_id = ? AND status = 'failed'", [$A]);
    return !NM::retryFailed($A)['ok'];
})());

// ====================================================================================================================== D. unsubscribe mid-campaign, pause/resume, restart
section('D. Unsubscribe mid-campaign, pause / resume, restart in a new process');
$B = makeIssue('b', 'zz-mailtest B');
$issueIds[] = $B;
NM::saveSettings($B, ['email_enabled' => '1']);
$totalB = NM::recipientCount($B);
$r = NM::queue($B, $totalB);
check('campaign B queued', $r['ok'] && counts($B)['total'] === $totalB, json_encode($r['code'] ?? 'ok'));
$r = NM::processBatch($B, ['limit' => 20]);
check('B: 20 handled in the first batch', $r['processed'] === 20 && $r['sent'] === 20 - $oi, json_encode([$r['sent'], $r['processed']]));
// pick three recipients that are still waiting and unsubscribe them: one via the real one-click POST, one via a plain DB change, one deleted
$waiting = Database::all("SELECT d.email, s.id sid, s.token FROM newsletter_deliveries d JOIN subscribers s ON s.id = d.subscriber_id WHERE d.newsletter_id = ? AND d.status = 'queued' AND d.email LIKE 'mt-%' ORDER BY d.id DESC LIMIT 3", [$B]);
check('B: found three waiting recipients to unsubscribe', count($waiting) === 3);
$ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => 'List-Unsubscribe=One-Click', 'ignore_errors' => true, 'timeout' => 10]]);
$body = @file_get_contents('http://localhost/' . basename(BASE_PATH) . '/unsubscribe/' . $waiting[0]['token'], false, $ctx);
$code = (int) (preg_match('~HTTP/\S+ (\d{3})~', $http_response_header[0] ?? '', $m) ? $m[1] : 0);
check('B: one-click POST (RFC 8058) to the link of the e-mail answers 200 OK', $code === 200 && trim((string) $body) === 'OK', "{$code} " . substr((string) $body, 0, 40));
Database::execute("UPDATE subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?", [(int) $waiting[1]['sid']]);
Database::execute('DELETE FROM subscribers WHERE id = ?', [(int) $waiting[2]['sid']]);
check('B: unsubscribed immediately by the one-click route', Database::value('SELECT status FROM subscribers WHERE id = ?', [(int) $waiting[0]['sid']]) === 'unsubscribed');
$pause = NM::pause($B);
check('B: pause works', $pause['ok'] && row($B)['email_status'] === 'paused');
$r = NM::processBatch($B);
check('B: a paused campaign sends nothing (idle)', $r['idle'] === true && $r['processed'] === 0);
check('B: pause twice is refused', !NM::pause($B)['ok']);
$res = NM::resume($B);
check('B: resume works', $res['ok'] && row($B)['email_status'] === 'sending');
// restart: finish in a brand new PHP process (stop after 2 batches), then finish in another one
$out1 = child('batchloop', ['issue' => $B, 'limit' => 15, 'stop-after' => 2]);
$j1 = json_decode($out1, true);
check('B: new process continues the campaign (restart)', is_array($j1) && $j1['batches'] === 2 && $j1['sent'] > 0, $out1);
$out2 = child('batchloop', ['issue' => $B, 'limit' => 15]);
$j2 = json_decode($out2, true);
check('B: another new process finishes it', is_array($j2) && $j2['status'] === 'sent', $out2);
$cB = counts($B);
check('B: the 3 unsubscribed / removed people were SKIPPED, not mailed', $cB['skipped'] === 3 && (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'skipped' AND last_error IN (?, ?)", [$B, NM::REASON_UNSUBSCRIBED, NM::REASON_REMOVED], 0) === 3, json_encode($cB));
$mailsB = mailsBySubject($OUT, 'zz-mailtest B');
check('B: none of the 3 got a message', !isset($mailsB[$waiting[0]['email']]) && !isset($mailsB[$waiting[1]['email']]) && !isset($mailsB[$waiting[2]['email']]));
check('B: one message per sent address across 3 processes', max($mailsB ?: [0]) === 1 && count($mailsB) === $cB['sent'], count($mailsB) . ' vs ' . $cB['sent']);
check('B: finished with sent status', row($B)['email_status'] === 'sent');

// ====================================================================================================================== E. cancel and send to the remaining
section('E. Cancel, then send to the remaining only');
$C = makeIssue('c', 'zz-mailtest C');
$issueIds[] = $C;
NM::saveSettings($C, ['email_enabled' => '1']);
$totalC = NM::recipientCount($C);
NM::queue($C, $totalC);
NM::processBatch($C, ['limit' => 25]);
$cancel = NM::cancel($C);
$cc = counts($C);
check('C: cancel works, waiting addresses skipped (reason Cancelled), sent ones stay', $cancel['ok'] && row($C)['email_status'] === 'cancelled' && $cc['queued'] === 0 && $cc['skipped'] > 0 && $cc['sent'] > 0, json_encode($cc));
check('C: cancel sets completed_at and syncs counters', row($C)['email_completed_at'] !== null && (int) row($C)['email_sent'] === $cc['sent']);
check('C: a cancelled campaign sends nothing', NM::processBatch($C)['idle'] === true);
$sentSoFar = $cc['sent'];
$remaining = NM::recipientCount($C);
check('C: recipient count after cancel = those not yet handled', $remaining === $totalC - $sentSoFar - $cc['failed'], "{$remaining} vs " . ($totalC - $sentSoFar - $cc['failed']));
$r = NM::queue($C, $remaining + 1);
check('C: re-queue needs the exact remaining count', !$r['ok'] && $r['code'] === 'count_changed');
$r = NM::queue($C, $remaining);
check('C: re-queue after cancel works', $r['ok'] && row($C)['email_status'] === 'queued' && counts($C)['queued'] === $remaining, json_encode($r['code'] ?? 'ok'));
runToEnd($C, 40);
$mailsC = mailsBySubject($OUT, 'zz-mailtest C');
check('C: everybody received it exactly once (nobody twice after cancel + restart)', max($mailsC ?: [0]) === 1 && counts($C)['sent'] === count($mailsC), counts($C)['sent'] . ' vs ' . count($mailsC));
// a new subscriber joins after a campaign ends: sent is final, they get nothing
$late = addSubscriber('late@mailtest.invalid');
check('C: a late subscriber changes nothing for a finished campaign', !NM::queue($C, NM::recipientCount($C))['ok']);

// ====================================================================================================================== F. mail server down, slow, dropped connections
section('F. Mail server problems');
$F = makeIssue('f', 'zz-mailtest F');
$issueIds[] = $F;
NM::saveSettings($F, ['email_enabled' => '1']);
NM::queue($F, NM::recipientCount($F));
Config::set('mail.smtp.port', $PORT + 1);       // nothing listens here
Mailer::closeConnection();
$r = NM::processBatch($F, ['limit' => 30]);
check('F: server down -> after 5 temporary failures in a row the campaign PAUSES (halted mail_server)', $r['halted'] === 'mail_server' && $r['status'] === 'paused' && $r['retried'] === 5 && $r['processed'] === 5 + $oi, json_encode([$r['halted'], $r['status'], $r['processed']]));
check('F: nothing was sent and nothing valid was marked failed; the 5 attempts used are recorded', counts($F)['failed'] === $oi && counts($F)['sent'] === 0 && (int) Database::value("SELECT MAX(attempts) FROM newsletter_deliveries WHERE newsletter_id = ?", [$F], 0) === 1);
check('F: no secrets or addresses in the stored errors', (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND last_error LIKE '%@%'", [$F], 0) === 0);
$r = NM::processBatch($F);
check('F: a paused campaign stays paused while the server is down', $r['idle'] === true);
pointMailerAt($PORT, 3);
check('F: resume after the server is back', NM::resume($F)['ok']);
runToEnd($F, 30);
$cF = counts($F);
check('F: finished sent; everybody else mailed once, the 3 rejections failed', row($F)['email_status'] === 'sent' && $cF['queued'] === 0 && $cF['failed'] === $F3 && max(mailsBySubject($OUT, 'zz-mailtest F') ?: [0]) === 1, json_encode($cF));
check('F: the 5 addresses that met the outage were mailed on the second try', (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'sent' AND attempts = 2", [$F], 0) === 5);

// the outage guard must also work with a SMALL batch size (the streak is counted from the table, not per batch)
$L = makeIssue('l', 'zz-mailtest L');
$issueIds[] = $L;
NM::saveSettings($L, ['email_enabled' => '1']);
NM::queue($L, NM::recipientCount($L));
Config::set('mail.smtp.port', $PORT + 1);
Mailer::closeConnection();
$halted = null;
for ($i = 0; $i < 12 && $halted === null; $i++) {
    $r = NM::processBatch($L, ['limit' => 2]);
    $halted = $r['halted'];
}
$burned = (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND attempts > 0", [$L], 0);
check('F: with batches of 2 the outage still pauses the campaign after about 5 failures, not after hundreds', $halted === 'mail_server' && $burned <= 7 + $oi, "halted={$halted} burned={$burned}");
pointMailerAt($PORT, 3);
NM::cancel($L);

// one target recipient at a time, so the mock server (one connection at a time) cannot confuse the timing
$only = static function (int $issue, string $like): void {
    Database::execute("UPDATE newsletter_deliveries SET status = 'skipped', last_error = 'test' WHERE newsletter_id = ? AND email NOT LIKE ?", [$issue, $like]);
};
addSubscriber('slow-a@mailtest.invalid');
addSubscriber('dropconn-a@mailtest.invalid');
$J = makeIssue('j', 'zz-mailtest J');
$issueIds[] = $J;
NM::saveSettings($J, ['email_enabled' => '1']);
NM::queue($J, NM::recipientCount($J));
$only($J, 'slow-%');
$t0 = microtime(true);
$r = NM::processBatch($J, ['limit' => 5]);
$secs = microtime(true) - $t0;
$row = Database::one("SELECT status, attempts, last_error FROM newsletter_deliveries WHERE newsletter_id = ? AND email LIKE 'slow-%'", [$J]);
check('F: a stalling mail server hits the timeout (3 s), the row is kept for a retry', $r['retried'] === 1 && $row['status'] === 'queued' && (int) $row['attempts'] === 1 && $secs < 7, round($secs, 1) . 's ' . json_encode($row));
check('F: the timeout reason is stored without an address', (string) $row['last_error'] !== '' && !str_contains((string) $row['last_error'], '@'), (string) $row['last_error']);
Database::execute("UPDATE newsletter_deliveries SET attempts = 2 WHERE newsletter_id = ? AND email LIKE 'slow-%'", [$J]);
sleep(8);       // let the mock finish its stall
$r = NM::processBatch($J, ['limit' => 5]);
$row = Database::one("SELECT status, attempts FROM newsletter_deliveries WHERE newsletter_id = ? AND email LIKE 'slow-%'", [$J]);
check('F: the third failed attempt -> failed for good, campaign finished', $row['status'] === 'failed' && (int) $row['attempts'] === 3 && $r['failed'] === 1 && row($J)['email_status'] === 'sent', json_encode([$row, row($J)['email_status']]));
$K = makeIssue('k', 'zz-mailtest K');
$issueIds[] = $K;
NM::saveSettings($K, ['email_enabled' => '1']);
NM::queue($K, NM::recipientCount($K));
$only($K, 'dropconn-%');
$r = NM::processBatch($K, ['limit' => 5]);
$row = Database::one("SELECT status, attempts FROM newsletter_deliveries WHERE newsletter_id = ? AND email LIKE 'dropconn-%'", [$K]);
check('F: a connection dropped by the server is a temporary failure, kept for a retry', $r['retried'] === 1 && $row['status'] === 'queued' && (int) $row['attempts'] === 1, json_encode([$r['retried'], $row]));
sleep(1);
Database::execute("DELETE FROM subscribers WHERE email IN ('slow-a@mailtest.invalid', 'dropconn-a@mailtest.invalid')");

// ====================================================================================================================== G. concurrency
section('G. Concurrent senders');
$G = makeIssue('g', 'zz-mailtest G');
$issueIds[] = $G;
NM::saveSettings($G, ['email_enabled' => '1']);
NM::queue($G, NM::recipientCount($G));
$outs = childrenParallel('batchloop', ['issue' => $G, 'limit' => 12], 5);
$js = array_map(static fn($o) => json_decode($o, true), $outs);
check('G: 5 parallel sender processes all ran without crashing', count(array_filter($js, 'is_array')) === 5, json_encode($outs));
$cG = counts($G);
if ($cG['queued'] > 0) {
    runToEnd($G, 30);
    $cG = counts($G);
}
$mailsG = mailsBySubject($OUT, 'zz-mailtest G');
check('G: no address got two messages although 5 senders ran at once', max($mailsG ?: [0]) === 1, json_encode(array_slice(array_filter($mailsG, static fn($n) => $n > 1), 0, 3)));
check('G: messages in the mock = sent rows', count($mailsG) === $cG['sent'] && $cG['sent'] === $cG['total'] - $cG['failed'] - $cG['skipped'], count($mailsG) . ' / ' . json_encode($cG));
check('G: the lock was released (a new sender can take it)', (int) Database::value('SELECT IS_FREE_LOCK(?)', [$lockName], 0) === 1);

// ====================================================================================================================== H. cron script
section('H. Cron script bin/newsletter-send.php');
$H = makeIssue('h', 'zz-mailtest H');
$issueIds[] = $H;
NM::saveSettings($H, ['email_enabled' => '1']);
NM::queue($H, NM::recipientCount($H));
NM::pause($H);
$o = child('cron');
check('cron: ignores a paused campaign', counts($H)['sent'] === 0 && row($H)['email_status'] === 'paused', $o);
NM::resume($H);
$o = child('cron', ['timeout' => 3]);
check('cron: finishes the campaign in one run and prints a one-line count summary', row($H)['email_status'] === 'sent' && str_contains($o, 'campaigns=') && !str_contains($o, '@'), $o);
check('cron: sent everybody exactly once', max(mailsBySubject($OUT, 'zz-mailtest H') ?: [0]) === 1 && counts($H)['failed'] === $F3, json_encode(counts($H)));
$o = child('cron');
check('cron: nothing to do -> prints that it has nothing to continue (verbose)', str_contains($o, 'No newsletter campaign to continue'), $o);
// lock held by someone else
$lockConn = Database::connect((string) Config::get('db.name'));
$lockConn->query("SELECT GET_LOCK('" . $lockName . "', 0)");
$I = makeIssue('i', 'zz-mailtest I');
$issueIds[] = $I;
NM::saveSettings($I, ['email_enabled' => '1']);
NM::queue($I, NM::recipientCount($I));
$r = NM::processBatch($I);
check('lock held elsewhere -> batch answers busy and sends nothing', $r['busy'] === true && counts($I)['sent'] === 0, json_encode($r['busy']));
$o = child('cron');
check('lock held elsewhere -> cron exits quietly without sending', counts($I)['sent'] === 0, $o);
$lockConn->query("SELECT RELEASE_LOCK('" . $lockName . "')");
$lockConn = null;
$r = NM::processBatch($I, ['limit' => 5]);
check('lock released -> sending works again', $r['busy'] === false && $r['processed'] === 5);
NM::cancel($I);

// web cannot run the cron script
$web = @file_get_contents('http://localhost/' . basename(BASE_PATH) . '/bin/newsletter-send.php');
check('cron script is not reachable over HTTP', $web === false || trim((string) $web) === '' || str_contains((string) $web, '404') || str_contains((string) $web, 'Not found'));

// ====================================================================================================================== J. after review
section('J. Mail server SET-UP problems pause the campaign, the issue is frozen while it runs, preconditions');
// a mock that wants a login, and one with a tiny size limit
$loginPort = $PORT + 2;
$sizePort = $PORT + 3;
$loginOut = $OUT . '-login';
$sizeOut = $OUT . '-size';
$startExtra = static function (int $port, string $dir, array $extra) use ($PHP): array {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    foreach (glob($dir . '/*') ?: [] as $f) {
        @unlink($f);
    }
    [$p, $pipes] = startProc(array_merge([$PHP, dirname(__DIR__) . '/dev/mock-smtp.php', '--port=' . $port, '--out=' . $dir, '--quiet'], $extra));
    for ($i = 0; $i < 60; $i++) {
        $s = @fsockopen('127.0.0.1', $port, $en, $es, 0.2);
        if ($s) {
            fclose($s);
            break;
        }
        usleep(100000);
    }
    return [$p, $pipes];
};
$stopExtra = static function (array $m, int $port, string $dir): void {
    @file_put_contents($dir . '/STOP', '1');
    $s = @fsockopen('127.0.0.1', $port, $en, $es, 0.5);
    if ($s) {
        fclose($s);
    }
    usleep(800000);
    @proc_terminate($m[0]);
    @proc_close($m[0]);
    foreach (glob($dir . '/*') ?: [] as $f) {
        @unlink($f);
    }
    @rmdir($dir);
};
$smtpTo = static function (int $port, string $user, string $pass): void {
    Config::set('mail.smtp', ['host' => '127.0.0.1', 'port' => $port, 'username' => $user, 'password' => $pass, 'encryption' => 'none', 'timeout' => 3, 'cafile' => '']);
    Mailer::closeConnection();
};
$mockLogin = $startExtra($loginPort, $loginOut, ['--user=u', '--pass=right-secret']);
$J = makeIssue('jx', 'zz-mailtest JX');
$issueIds[] = $J;
NM::saveSettings($J, ['email_enabled' => '1']);
$jCount = NM::recipientCount($J);
check('J: queued for everybody subscribed', NM::queue($J, $jCount)['ok'] === true && counts($J)['queued'] === $jCount && $jCount > 20);
// 1. the wrong password
$smtpTo($loginPort, 'u', 'wrong-secret');
$r = NM::processBatch($J, ['limit' => 200]);
$cJ = counts($J);
check('J: a wrong SMTP login stops the campaign at the FIRST message (paused, halted mail_server) instead of failing the whole list',
    $r['halted'] === 'mail_server' && $r['status'] === 'paused' && $r['processed'] === 0 && $r['sent'] === 0 && $r['failed'] === 0 && $cJ['failed'] === 0 && $cJ['queued'] === $jCount, json_encode([$r['halted'], $r['status'], $r['processed'], $cJ]));
check('J: the reason is shown (login), without the password; the recipients are untouched (attempts 0)', str_contains((string) ($r['halt_reason'] ?? ''), 'login') && !str_contains(json_encode($r), 'wrong-secret')
    && (int) Database::value('SELECT MAX(attempts) FROM newsletter_deliveries WHERE newsletter_id = ?', [$J], 0) === 0);
check('J: the issue is not marked "sent" and not completed', row($J)['email_status'] === 'paused' && row($J)['email_completed_at'] === null);
// 2. no login at all
$smtpTo($loginPort, '', '');
check('J: resume', NM::resume($J)['ok']);
$r = NM::processBatch($J, ['limit' => 200]);
check('J: a server that wants a login but gets none also pauses the campaign (it would fail for everybody)', $r['halted'] === 'mail_server' && $r['status'] === 'paused' && counts($J)['failed'] === 0 && counts($J)['sent'] === 0, json_encode([$r['halted'], counts($J)]));
// 3. the right login: all delivered
$smtpTo($loginPort, 'u', 'right-secret');
NM::resume($J);
runToEnd($J, 50);
check('J: after the settings are fixed and the campaign resumed, everybody is mailed exactly once (only the reject5 addresses of the earlier sections fail)', row($J)['email_status'] === 'sent' && counts($J)['sent'] === $jCount - $F3 && counts($J)['failed'] === $F3, json_encode(counts($J)));
$stopExtra($mockLogin, $loginPort, $loginOut);

// 4. a message larger than the server accepts
$mockSize = $startExtra($sizePort, $sizeOut, ['--max-size=3000']);
$K = makeIssue('kx', 'zz-mailtest KX');
$issueIds[] = $K;
NM::saveSettings($K, ['email_enabled' => '1']);
NM::queue($K, NM::recipientCount($K));
$smtpTo($sizePort, '', '');
$r = NM::processBatch($K, ['limit' => 200]);
check('J: a message above the mail server size limit pauses the campaign too (nobody is marked failed)', $r['halted'] === 'mail_server' && $r['status'] === 'paused' && counts($K)['failed'] === 0 && counts($K)['sent'] === 0 && str_contains((string) ($r['halt_reason'] ?? ''), 'larger'), json_encode([$r['halted'], $r['halt_reason'] ?? null]));
NM::cancel($K);
$stopExtra($mockSize, $sizePort, $sizeOut);
pointMailerAt($PORT, 3);

// 5. the issue is frozen while the campaign runs (title and content feed every batch)
$M = makeIssue('mx', 'zz-mailtest MX');
$issueIds[] = $M;
NM::saveSettings($M, ['email_enabled' => '1']);
NM::queue($M, NM::recipientCount($M));
NM::processBatch($M, ['limit' => 5]);
$vM = (int) row($M)['version'];
$edit = NS::save($M, ['title' => 'CHANGED mid-campaign', 'content' => ['featured' => ['heading' => 'CHANGED mid-campaign']]], $vM, null);
check('J: a title / content change in the middle of a campaign is refused (every subscriber gets the same message)', !$edit['ok'] && !empty($edit['locked']) && row($M)['title'] === 'zz-mailtest MX');
NM::pause($M);
check('J: ... also while paused', !empty(NS::save($M, ['summary' => 'x'], $vM, null)['locked']));
NM::cancel($M);
check('J: after cancelling the campaign the issue can be edited again', NS::save($M, ['summary' => 'edited after cancel'], $vM, null)['ok'] === true);

// 6. preconditions: complete issue, Newsletter page switched on
$N = makeIssue('nx', 'zz-mailtest NX');
$issueIds[] = $N;
NM::saveSettings($N, ['email_enabled' => '1']);
$goodContent = (string) row($N)['content'];
Database::execute('UPDATE newsletters SET content = ? WHERE id = ?', [NC::encode(NC::emptyContent()), $N]);        // an incomplete live issue (cannot be produced through save() any more)
$qr = NM::queue($N, NM::recipientCount($N));
$st = NM::status(row($N));
check('J: an incomplete (empty) live issue cannot be e-mailed', $qr['ok'] === false && ($qr['code'] ?? '') === 'incomplete' && $st['can_start'] === false && str_contains((string) $st['cannot_start_reason'], 'not complete'), json_encode([$qr['code'] ?? null, $st['cannot_start_reason']]));
Database::execute('UPDATE newsletters SET content = ? WHERE id = ?', [$goodContent, $N]);
Database::execute("UPDATE pages SET is_published = 0 WHERE slug = 'newsletter'");
\App\Core\SitePages::flush();
$qr = NM::queue($N, NM::recipientCount($N));
$st = NM::status(row($N));
Database::execute("UPDATE pages SET is_published = 1 WHERE slug = 'newsletter'");
\App\Core\SitePages::flush();
check('J: with the Newsletter page switched off ("View in browser" would be a 404) the campaign cannot start', $qr['ok'] === false && ($qr['code'] ?? '') === 'page_unpublished' && $st['can_start'] === false, json_encode([$qr['code'] ?? null, $st['cannot_start_reason']]));
$qr = NM::queue($N, NM::recipientCount($N));
check('J: ... and starts once the page is on again', $qr['ok'] === true);
NM::cancel($N);

// ====================================================================================================================== I. privacy
section('I. Privacy: no addresses in audit entries or the log');
$ids = implode(',', array_map('intval', $issueIds));
$audit = Database::all("SELECT action, summary, details FROM audit_log WHERE id > ? AND entity_type = 'newsletter' AND entity_id IN ({$ids})", [$auditBefore]);
$leak = array_filter($audit, static fn($a) => str_contains((string) $a['summary'] . (string) $a['details'], '@') || stripos((string) $a['summary'] . (string) $a['details'], 'mailtest') !== false);
check('audit entries (' . count($audit) . ') contain no e-mail address', $leak === [], json_encode(array_slice($leak, 0, 2)));
$logNew = is_file($logFile) ? (string) file_get_contents($logFile, false, null, $logSizeBefore) : '';
check('log lines written meanwhile contain no mailtest address', !str_contains($logNew, '@mailtest.invalid'), substr($logNew, 0, 200));
check('log lines contain no password-ish data (the advice text may name the setting MAIL_PASSWORD; never a value, never the AUTH exchange)', !preg_match('/MAIL_PASSWORD\s*=|AUTH PLAIN|AUTH LOGIN|right-secret|wrong-secret/i', $logNew));
$errs = Database::all('SELECT last_error FROM newsletter_deliveries WHERE last_error IS NOT NULL');
$bad = array_filter($errs, static fn($e) => str_contains((string) $e['last_error'], '@'));
check('no stored delivery error contains an address', $bad === []);

// ====================================================================================================================== done
echo PHP_EOL . ($fail === 0 ? 'ALL MAILING CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
exit($fail === 0 ? 0 : 1);
