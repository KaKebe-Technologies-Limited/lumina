<?php
/**
 * E-MAIL BEHAVIOUR TEST of the forms module (docs/FORMS.md section 10), against the FILE driver (storage/mail) and the throw-away MOCK SMTP server.
 *
 *   /c/xampp/php/php.exe bin/dev/test-forms-mailing.php [--port=2611] [--keep]
 *
 * File driver: the staff notification is a LINK by default and carries no answers; "include answers" lists them; one separate message per staff address;
 * Reply-To is the submitter; the confirmation goes ONLY to the address typed in the chosen e-mail question, with {placeholders} filled and web links removed from
 * visitor values; header-injection attempts stay in the body; the confirmation cannot be used as a relay (3 per address per hour, per-form cap); the staff
 * notification is capped per form per hour; disabled / no recipient do nothing; nothing here throws.
 * Mock SMTP: the same mails travel over a real SMTP conversation: one envelope per recipient, a rejected recipient (550) does not stop the others and never
 * loses the submission, a dead mail server only makes the send report "failed", the received MIME is multipart/alternative with an escaped HTML part, and
 * nobody ever sees another recipient's address.
 * Development only: it creates the form "zz-mailtest forms", sends to *@mailtest.invalid, and removes forms, submissions, rate-limit rows, the mails it wrote
 * to storage/mail and the mock output (storage/mock-smtp/forms-mail-test). The .env is not touched (the mailer is pointed at the mock in-process).
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
use App\Services\Forms\FormDefinition;
use App\Services\Forms\FormMailer;
use App\Services\Forms\FormService;
use App\Services\Forms\FormText;
use App\Services\Forms\FormValidator;
use App\Services\Forms\SubmissionService;

if (Config::get('app.env') !== 'local') {
    exit("Refusing to run outside APP_ENV=local.\n");
}
$opt = [];
foreach (array_slice($argv, 1) as $a) {
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $m) === 1) {
        $opt[$m[1]] = $m[2] ?? '1';
    }
}
$PORT = (int) ($opt['port'] ?? 2611);
$OUT = STORAGE_PATH . '/mock-smtp/forms-mail-test';
$PHP = PHP_BINARY;

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
function rrmdir(string $d): void
{
    if (!is_dir($d)) {
        return;
    }
    foreach (scandir($d) ?: [] as $f) {
        if ($f !== '.' && $f !== '..') {
            is_dir("$d/$f") ? rrmdir("$d/$f") : @unlink("$d/$f");
        }
    }
    @rmdir($d);
}
/** Decode a stored .eml: the raw text plus every base64 part decoded (bodies may be base64). */
function eml(string $file): string
{
    $raw = (string) file_get_contents($file);
    $out = $raw;
    if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--|\z)/s', $raw, $m)) {
        foreach ($m[1] as $b) {
            $out .= "\n" . base64_decode((string) preg_replace('/\s+/', '', $b));
        }
    }
    if (preg_match_all('/Content-Transfer-Encoding: quoted-printable\r?\n\r?\n(.*?)(?=\r?\n--|\z)/s', $raw, $m)) {
        foreach ($m[1] as $b) {
            $out .= "\n" . quoted_printable_decode($b);
        }
    }
    return $out;
}
function header_of(string $mail, string $name): string
{
    $head = explode("\r\n\r\n", str_replace("\n", "\r\n", str_replace("\r\n", "\n", $mail)), 2)[0];
    $head = preg_replace('/\r\n[ \t]+/', ' ', $head);
    return preg_match('/^' . preg_quote($name, '/') . ':\s*(.*)$/mi', (string) $head, $m) === 1 ? trim($m[1]) : '';
}
/** The decoded body of the text/html part of a message. */
function html_part(string $mail): string
{
    if (preg_match('/Content-Type: text\/html[^
]*?
((?:[A-Za-z-]+:[^
]*?
)*)?
(.*?)(?=?
--|\z)/s', $mail, $m) !== 1) {
        return '';
    }
    $body = $m[2];
    if (stripos($m[1], 'base64') !== false) {
        return (string) base64_decode((string) preg_replace('/\s+/', '', $body));
    }
    if (stripos($m[1], 'quoted-printable') !== false) {
        return quoted_printable_decode($body);
    }
    return $body;
}
function pointMailerAt(int $port, int $timeout = 3): void
{
    Config::set('mail.driver', 'smtp');
    Config::set('mail.smtp', ['host' => '127.0.0.1', 'port' => $port, 'username' => '', 'password' => '', 'encryption' => 'none', 'timeout' => $timeout, 'cafile' => '']);
    Mailer::closeConnection();
}

$cfgBefore = [Config::get('mail.driver'), Config::get('mail.smtp'), Config::get('form_builder.confirm_per_address'), Config::get('form_builder.confirm_per_form'), Config::get('form_builder.notify_per_form')];
$mailDir = STORAGE_PATH . '/mail';
$mailsBefore = glob($mailDir . '/*.eml') ?: [];
$formIds = [];
$procs = [];
$rlKeys = [];
$cleanup = static function () use (&$formIds, &$procs, &$rlKeys, $cfgBefore, $mailsBefore, $mailDir, $OUT, $opt): void {
    foreach ($procs as $p) {
        if (is_resource($p)) {
            @proc_terminate($p);
            @proc_close($p);
        }
    }
    Config::set('mail.driver', $cfgBefore[0]);
    Config::set('mail.smtp', $cfgBefore[1]);
    Config::set('form_builder.confirm_per_address', $cfgBefore[2]);
    Config::set('form_builder.confirm_per_form', $cfgBefore[3]);
    Config::set('form_builder.notify_per_form', $cfgBefore[4]);
    Mailer::closeConnection();
    foreach ($formIds as $id) {
        FormService::delete($id);
        RateLimit::clear('forms:notify:' . $id);
        RateLimit::clear('forms:confirm:form:' . $id);
    }
    foreach ($rlKeys as $k) {
        RateLimit::clear($k);
    }
    foreach (glob($mailDir . '/*.eml') ?: [] as $f) {
        if (!in_array($f, $mailsBefore, true)) {
            @unlink($f);
        }
    }
    if (!isset($opt['keep'])) {
        rrmdir($OUT);
    }
};
register_shutdown_function($cleanup);

// ---------------------------------------------------------------------------------------------------------------------------------------------- set-up
section('set-up');
$r = FormService::create(['source' => 'contact_enquiry', 'title' => 'zz-mailtest forms', 'slug' => 'zz-mailtest-forms']);
$form = $r['form'];
$formIds[] = $fid = (int) $form['id'];
$ids = [];
foreach (FormDefinition::fields($form['def']) as $f) {
    $ids[$f['key']] = $f['id'];
}
check('test form created from the Contact template with name / e-mail / message questions', isset($ids['full_name'], $ids['email'], $ids['message']), json_encode(array_keys($ids)));

$configure = static function (array $cfgPatch) use ($fid, $ids): array {
    $f = FormService::find($fid);
    $cfg = array_replace_recursive($f['cfg'], $cfgPatch);
    if (isset($cfgPatch['notify']['emails'])) {
        $cfg['notify']['emails'] = $cfgPatch['notify']['emails'];          // a list is replaced, not merged
    }
    $cfg['confirmation']['email_field'] = $ids['email'];
    $res = FormService::save($fid, ['settings' => $cfg], (int) $f['version'], null);
    if (empty($res['ok'])) {
        echo "  (configure refused: " . json_encode($res['errors'] ?? $res) . ")\n";
    }
    Database::execute("UPDATE forms SET status = 'published' WHERE id = ?", [$fid]);
    return FormService::find($fid);
};
$store = static function (array $form, array $over = []) use ($ids): array {
    $in = array_replace(['f_' . $ids['full_name'] => 'Wanjiru Kamau', 'f_' . $ids['email'] => 'visitor@mailtest.invalid', 'f_' . $ids['message'] => 'Please call me about the SECRETPLAN programme.', 'form_consent' => 'yes'], $over);
    foreach (FormDefinition::inputFields($form['def']) as $f) {
        if ($f['type'] === 'consent' && !isset($in['f_' . $f['id']])) {
            $in['f_' . $f['id']] = 'yes';
        }
        if ($f['type'] === 'text' && !isset($in['f_' . $f['id']])) {
            $in['f_' . $f['id']] = 'Hello';
        }
        if (in_array($f['type'], ['select', 'radio'], true) && !isset($in['f_' . $f['id']]) && !empty($f['options'])) {
            $in['f_' . $f['id']] = $f['options'][0]['value'];
        }
    }
    $res = FormValidator::validate($form['def'], $in, [], ['settings' => $form['cfg'], 'inspect_files' => false]);
    if (!$res['ok']) {
        throw new RuntimeException('validation failed: ' . json_encode($res['errors']));
    }
    $out = SubmissionService::store($form, $res, [], ['ip' => '127.0.0.1', 'user_agent' => 'zz-mailtest']);
    if (empty($out['ok'])) {
        throw new RuntimeException('store failed: ' . json_encode($out));
    }
    return $out;
};
$newMails = static function () use ($mailDir, &$seen): array {
    $now = glob($mailDir . '/*.eml') ?: [];
    $new = array_values(array_diff($now, $seen ?? []));
    $seen = $now;
    return array_map('eml', $new);
};
$seen = glob($mailDir . '/*.eml') ?: [];

Config::set('mail.driver', 'file');
Mailer::closeConnection();

// ----------------------------------------------------------------------------------------------------------------------------------- file driver: staff
section('file driver: staff notification');
$form = $configure(['notify' => ['enabled' => true, 'emails' => ['boss@mailtest.invalid', 'hr@mailtest.invalid'], 'include_answers' => false], 'confirmation' => ['enabled' => false]]);
$o = $store($form);
$sid = (int) $o['id'];
$newMails();
$res = FormMailer::notifyStaff($form, $sid, $o['snapshot'], $o['email']);
$mails = $newMails();
check('two staff addresses -> two separate messages, sent = 2', $res['sent'] === 2 && count($mails) === 2, json_encode($res) . ' ' . count($mails));
$to = array_map(static fn(string $m): string => header_of($m, 'To'), $mails);
sort($to);
check('each message is addressed to ONE staff member only (nobody sees the other address)', $to === ['boss@mailtest.invalid', 'hr@mailtest.invalid'] && !str_contains($mails[0], 'hr@mailtest.invalid') || !str_contains($mails[0], 'boss@mailtest.invalid'), implode(' | ', $to));
check('default = LINK ONLY: the message has the admin link to the submission', str_contains($mails[0], '/admin/forms/' . $fid . '/submissions/' . $sid));
check('default = LINK ONLY: no answers, no name, no e-mail address, no message text in any part of the message', !preg_match('/Wanjiru|SECRETPLAN|visitor@mailtest/', implode("\n", array_map(static fn(string $m): string => preg_replace('/^Reply-To:.*$/mi', '', $m), $mails))));
check('...and it carries the privacy note', str_contains($mails[0], FormText::get('email.staff_privacy_note')));
check('subject comes from the registry with {form_title} filled', header_of($mails[0], 'Subject') === 'New submission: zz-mailtest forms', header_of($mails[0], 'Subject'));
check('Reply-To is the submitter (so staff can answer from their mail program)', header_of($mails[0], 'Reply-To') === 'visitor@mailtest.invalid', header_of($mails[0], 'Reply-To'));
check('the message has a plain-text and an HTML part', str_contains($mails[0], 'text/plain') && str_contains($mails[0], 'text/html'));

section('file driver: staff notification WITH answers (opt-in)');
$form = $configure(['notify' => ['include_answers' => true]]);
$o2 = $store($form, ['f_' . $ids['full_name'] => 'Odhiambo <b>Otieno</b>', 'f_' . $ids['message'] => "Line one\nSECRETPLAN & <script>alert(1)</script>"]);
$newMails();
$res = FormMailer::notifyStaff($form, (int) $o2['id'], $o2['snapshot'], $o2['email']);
$mails = $newMails();
$all = implode("\n", $mails);
check('include_answers: the labels and the answers are in the message', $res['sent'] === 2 && str_contains($all, FormText::get('email.staff_answers_heading')) && str_contains($all, 'SECRETPLAN') && str_contains($all, 'Odhiambo'), json_encode($res));
check('...and the privacy sentence is not printed (answers are in the mail on purpose)', !str_contains($all, FormText::get('email.staff_privacy_note')));
check('...the HTML part is escaped (no raw <script> or <b>)', implode('', array_map('html_part', $mails)) !== '' && !str_contains(implode('', array_map('html_part', $mails)), '<script>alert') && !str_contains(implode('', array_map('html_part', $mails)), '<b>Otieno'));
check('...header injection impossible: no Bcc / Cc header was created from the answers', header_of($mails[0], 'Bcc') === '' && header_of($mails[0], 'Cc') === '');

section('file driver: disabled / no recipient / rate limit');
$form = $configure(['notify' => ['enabled' => false]]);
$newMails();
$res = FormMailer::notifyStaff($form, $sid, $o['snapshot'], $o['email']);
check('notifications switched off: nothing sent', $res['sent'] === 0 && $res['skipped'] === 'disabled' && $newMails() === []);
$form = $configure(['notify' => ['enabled' => true, 'emails' => [], 'include_answers' => false]]);
$rec = \App\Services\Forms\FormSettings::staffRecipients($form['cfg']);
$res = FormMailer::notifyStaff($form, $sid, $o['snapshot'], $o['email']);
$mails = $newMails();
check('no staff list: falls back to the Site settings list, or skips ("no_recipient") when there is none; never throws', $rec === [] ? ($res['skipped'] === 'no_recipient' && $mails === []) : ($res['sent'] === count($rec)), json_encode([$rec, $res]));
$form = $configure(['notify' => ['enabled' => true, 'emails' => ['boss@mailtest.invalid'], 'include_answers' => false]]);
Config::set('form_builder.notify_per_form', [2, 3600]);
RateLimit::clear('forms:notify:' . $fid);
$r1 = FormMailer::notifyStaff($form, $sid, $o['snapshot'], null);
$r2 = FormMailer::notifyStaff($form, $sid, $o['snapshot'], null);
$r3 = FormMailer::notifyStaff($form, $sid, $o['snapshot'], null);
$newMails();
check('staff notifications are capped per form per hour (3rd is "rate_limited")', $r1['sent'] === 1 && $r2['sent'] === 1 && $r3['sent'] === 0 && $r3['skipped'] === 'rate_limited', json_encode([$r1, $r2, $r3]));
Config::set('form_builder.notify_per_form', $cfgBefore[4]);
RateLimit::clear('forms:notify:' . $fid);

// ------------------------------------------------------------------------------------------------------------- file driver: confirmation to the submitter
section('file driver: confirmation to the submitter');
$form = $configure(['notify' => ['enabled' => false], 'confirmation' => ['enabled' => true, 'subject' => 'Thanks {full_name} [{reference}]', 'body' => "Dear {full_name},\nwe got: {message}\nRef {reference} on {date} - {site}. Unknown {nothing_here}."]]);
$o3 = $store($form, ['f_' . $ids['full_name'] => 'Zawadi Mwangi', 'f_' . $ids['email'] => 'zawadi@mailtest.invalid', 'f_' . $ids['message'] => 'See https://evil.example/phish and www.evil.example now']);
$rlKeys[] = 'forms:confirm:addr:' . hash('sha256', 'zawadi@mailtest.invalid');
RateLimit::clear('forms:confirm:addr:' . hash('sha256', 'zawadi@mailtest.invalid'));
$newMails();
$res = FormMailer::confirmSubmitter($form, (int) $o3['id'], $o3['snapshot'], $o3['email']);
$mails = $newMails();
check('one confirmation, addressed ONLY to the typed address', $res['sent'] === true && count($mails) === 1 && header_of($mails[0], 'To') === 'zawadi@mailtest.invalid' && !preg_match('/boss@|hr@/', $mails[0]), json_encode($res));
$ref = SubmissionService::reference($fid, (int) $o3['id']);
check('subject placeholders {full_name} and {reference} are filled', header_of($mails[0], 'Subject') === 'Thanks Zawadi Mwangi [' . $ref . ']', header_of($mails[0], 'Subject'));
check('body placeholders filled; unknown placeholder left as typed', str_contains($mails[0], 'Dear Zawadi Mwangi,') && str_contains($mails[0], 'Ref ' . $ref) && str_contains($mails[0], '{nothing_here}'));
check('web links typed by the visitor are removed from the inserted value (no phishing relay)', !str_contains($mails[0], 'evil.example') && str_contains($mails[0], 'See'));
check('the confirmation does NOT leak the staff-only link', !str_contains($mails[0], '/admin/'));

$form = $configure(['confirmation' => ['subject' => '', 'body' => '']]);
RateLimit::clear('forms:confirm:addr:' . hash('sha256', 'zawadi@mailtest.invalid'));
$newMails();
$res = FormMailer::confirmSubmitter($form, (int) $o3['id'], $o3['snapshot'], $o3['email']);
$mails = $newMails();
check('empty subject / body = the editable registry defaults', $res['sent'] === true && header_of($mails[0], 'Subject') === 'We received your submission: zz-mailtest forms' && str_contains($mails[0], $ref), header_of($mails[0], 'Subject'));

section('file driver: the confirmation cannot be used as a relay');
foreach (["a@mailtest.invalid\r\nBcc: victim@mailtest.invalid", "a@mailtest.invalid,victim@mailtest.invalid", 'not-an-address', '', "<victim@mailtest.invalid>", "victim@mailtest.invalid\nCc: x@mailtest.invalid"] as $bad) {
    $r = FormMailer::confirmSubmitter($form, (int) $o3['id'], $o3['snapshot'], $bad);
    check('hostile / invalid address ' . json_encode(substr($bad, 0, 30)) . ': nothing sent', $r['sent'] === false && $newMails() === [], json_encode($r));
}
$addr = 'relay@mailtest.invalid';
$rlKeys[] = 'forms:confirm:addr:' . hash('sha256', $addr);
RateLimit::clear('forms:confirm:addr:' . hash('sha256', $addr));
$sent = 0;
for ($i = 0; $i < 6; $i++) {
    $sent += FormMailer::confirmSubmitter($form, (int) $o3['id'], $o3['snapshot'], $addr)['sent'] ? 1 : 0;
}
$last = FormMailer::confirmSubmitter($form, (int) $o3['id'], $o3['snapshot'], $addr);
$newMails();
check('same address: at most 3 confirmations per hour, then "rate_limited"', $sent === 3 && $last['skipped'] === 'rate_limited', "sent=$sent " . json_encode($last));
Config::set('form_builder.confirm_per_form', [2, 3600]);
RateLimit::clear('forms:confirm:form:' . $fid);
$sent = 0;
foreach (['x1', 'x2', 'x3', 'x4'] as $x) {
    $rlKeys[] = 'forms:confirm:addr:' . hash('sha256', $x . '@mailtest.invalid');
    RateLimit::clear('forms:confirm:addr:' . hash('sha256', $x . '@mailtest.invalid'));
    $sent += FormMailer::confirmSubmitter($form, (int) $o3['id'], $o3['snapshot'], $x . '@mailtest.invalid')['sent'] ? 1 : 0;
}
$newMails();
check('per-form cap: only the first 2 different addresses are served in the window', $sent === 2, "sent=$sent");
Config::set('form_builder.confirm_per_form', $cfgBefore[3]);
RateLimit::clear('forms:confirm:form:' . $fid);
$form = $configure(['confirmation' => ['enabled' => false]]);
$r = FormMailer::confirmSubmitter($form, (int) $o3['id'], $o3['snapshot'], $o3['email']);
check('confirmation switched off: nothing sent', $r['sent'] === false && $r['skipped'] === 'disabled' && $newMails() === []);

section('header injection through the answers');
$form = $configure(['notify' => ['enabled' => true, 'emails' => ['boss@mailtest.invalid'], 'include_answers' => true], 'confirmation' => ['enabled' => true, 'subject' => 'Hi {full_name}', 'body' => 'Dear {full_name}']]);
$snap = $o3['snapshot'];
$snap[0]['text'] = "Eve\r\nBcc: victim@mailtest.invalid\r\nSubject: pwned";
RateLimit::clear('forms:notify:' . $fid);
RateLimit::clear('forms:confirm:addr:' . hash('sha256', 'zawadi@mailtest.invalid'));
$newMails();
FormMailer::notifyStaff($form, (int) $o3['id'], $snap, 'zawadi@mailtest.invalid');
FormMailer::confirmSubmitter($form, (int) $o3['id'], $snap, 'zawadi@mailtest.invalid');
$mails = $newMails();
$hdrBlock = static fn(string $m): string => explode("

", str_replace("
", "
", str_replace("
", "
", $m)), 2)[0];
$bad = array_filter($mails, static fn(string $m): bool => header_of($m, 'Bcc') !== '' || preg_match_all('/^Subject:/mi', $hdrBlock($m)) !== 1 || preg_match_all('/^To:/mi', $hdrBlock($m)) !== 1);
check('a newline in an answer cannot add a Bcc / Subject header to the staff mail or the confirmation', count($mails) === 2 && $bad === [], count($mails) . ' mails');
check('...the confirmation subject stays on one line', !str_contains(header_of($mails[1] ?? '', 'Subject'), "\n"));

// --------------------------------------------------------------------------------------------------------------------------------------- mock SMTP
section('mock SMTP server');
rrmdir($OUT);
@mkdir($OUT, 0775, true);
$cmd = [$PHP, dirname(__DIR__) . '/dev/mock-smtp.php', '--port=' . $PORT, '--out=' . $OUT, '--quiet'];
$proc = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2));
$procs[] = $proc;
$up = false;
for ($i = 0; $i < 40 && !$up; $i++) {
    $s = @fsockopen('127.0.0.1', $PORT, $en, $es, 0.2);
    if ($s) {
        fclose($s);
        $up = true;
    } else {
        usleep(150000);
    }
}
check('mock SMTP server is listening on 127.0.0.1:' . $PORT, $up);
if ($up) {
    pointMailerAt($PORT, 3);
    $form = $configure(['notify' => ['enabled' => true, 'emails' => ['boss@mailtest.invalid', 'reject5@mailtest.invalid', 'hr@mailtest.invalid'], 'include_answers' => false], 'confirmation' => ['enabled' => true, 'subject' => '', 'body' => '']]);
    $o4 = $store($form, ['f_' . $ids['full_name'] => 'Chidi <i>Okafor</i>', 'f_' . $ids['email'] => 'chidi@mailtest.invalid']);
    $rlKeys[] = 'forms:confirm:addr:' . hash('sha256', 'chidi@mailtest.invalid');
    RateLimit::clear('forms:confirm:addr:' . hash('sha256', 'chidi@mailtest.invalid'));
    RateLimit::clear('forms:notify:' . $fid);
    $res = FormMailer::notifyStaff($form, (int) $o4['id'], $o4['snapshot'], $o4['email']);
    $conf = FormMailer::confirmSubmitter($form, (int) $o4['id'], $o4['snapshot'], $o4['email']);
    Mailer::closeConnection();
    $files = glob($OUT . '/*.eml') ?: [];
    $texts = array_map('eml', $files);
    check('staff: 3 recipients, one is rejected with 550 -> the other two still got their mail (sent = 2)', $res['sent'] === 2, json_encode($res));
    check('confirmation delivered over SMTP too', $conf['sent'] === true, json_encode($conf));
    check('mock server holds 3 messages (2 staff + 1 confirmation)', count($texts) === 3, (string) count($texts));
    $rcpt = [];
    foreach ($texts as $t) {
        $rcpt[] = preg_match('/^X-Mock-Rcpt-To:\s*(.*)$/mi', $t, $m) === 1 ? trim($m[1]) : '?';
    }
    sort($rcpt);
    check('envelopes: one recipient per message, nobody else in it', count(array_unique($rcpt)) === 3 && !preg_match('/reject5/', implode(',', $rcpt)), implode(' | ', $rcpt));
    $staffMail = null;
    foreach ($texts as $t) {
        if (str_contains($t, '/admin/forms/' . $fid . '/submissions/')) {
            $staffMail = $t;
            break;
        }
    }
    check('MIME: multipart/alternative with text/plain and text/html; staff mail has the link and not the answers', $staffMail !== null && str_contains($staffMail, 'multipart/alternative') && str_contains($staffMail, 'text/plain') && str_contains($staffMail, 'text/html') && !str_contains($staffMail, 'Chidi') && !str_contains($staffMail, 'SECRETPLAN'));
    check('From is the site address and Reply-To the submitter on the staff mail', $staffMail !== null && header_of($staffMail, 'From') !== '' && header_of($staffMail, 'Reply-To') === 'chidi@mailtest.invalid', $staffMail !== null ? header_of($staffMail, 'Reply-To') : '');
    $confMail = null;
    foreach ($texts as $t) {
        if (str_contains($t, 'chidi@mailtest.invalid') && str_contains(header_of($t, 'Subject'), 'We received')) {
            $confMail = $t;
        }
    }
    check('confirmation: HTML part has the visitor name ESCAPED (no raw <i> tag), plain part has the reference', $confMail !== null && !str_contains(preg_replace('/^Content-Type:.*$/mi', '', $confMail), '<i>Okafor') && str_contains($confMail, 'F' . $fid . '-' . $o4['id']));
    check('the submission itself is stored whatever happened to the mail', (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE id = ?', [(int) $o4['id']], 0) === 1);

    // the mail server goes away
    @proc_terminate($proc);
    @proc_close($proc);
    $procs = [];
    usleep(400000);
    Mailer::closeConnection();
    RateLimit::clear('forms:notify:' . $fid);
    RateLimit::clear('forms:confirm:addr:' . hash('sha256', 'chidi@mailtest.invalid'));
    $t0 = microtime(true);
    $threw = false;
    try {
        $res = FormMailer::notifyStaff($form, (int) $o4['id'], $o4['snapshot'], $o4['email']);
        $conf = FormMailer::confirmSubmitter($form, (int) $o4['id'], $o4['snapshot'], $o4['email']);
    } catch (\Throwable) {
        $threw = true;
        $res = $conf = [];
    }
    check('mail server down: nothing throws, the staff mail reports failed (0 sent), the confirmation reports failed', !$threw && ($res['sent'] ?? -1) === 0 && ($res['skipped'] ?? '') === 'failed' && ($conf['sent'] ?? true) === false, json_encode([$res, $conf]));
    check('mail server down: the request is not held up for long (< 12 s for 4 attempts)', (microtime(true) - $t0) < 12, round(microtime(true) - $t0, 1) . ' s');
    $log = (string) @file_get_contents(STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log');
    $tail = substr($log, -6000);
    check('the application log has no addresses and no answers from these mails', !preg_match('/chidi@mailtest|zawadi@mailtest|boss@mailtest|visitor@mailtest|SECRETPLAN/', $tail));
}

echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
exit($fail === 0 ? 0 : 1);
