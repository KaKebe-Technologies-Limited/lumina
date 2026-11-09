<?php
declare(strict_types=1);

/**
 * OFFICIAL LETTERS: validation, recipient parsing, the PDF, sending (each / one group / hidden), no double sending, and the admin pages.
 * Run alone:   /c/xampp/php/php.exe bin/selftest-letters.php        Needs the local web server and APP_ENV=local (mail driver "file" writes .eml files).
 */

use App\Core\Database;
use App\Services\Letters\LetterPdf;
use App\Services\Letters\LetterService;

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit('Refusing to run outside APP_ENV=local.' . PHP_EOL);
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

echo "-- official letters --\n";
$clean = static function (): void {
    if (LetterService::ready()) {
        Database::execute("DELETE r FROM letter_recipients r JOIN letters l ON l.id = r.letter_id WHERE l.subject LIKE 'ZZ test%'");
        Database::execute("DELETE s FROM letter_sends s JOIN letters l ON l.id = s.letter_id WHERE l.subject LIKE 'ZZ test%'");
        Database::execute("DELETE FROM letters WHERE subject LIKE 'ZZ test%'");
        Database::execute("DELETE FROM letter_contacts WHERE email LIKE 'zz-lt-%@example.test'");
    }
    Database::execute("DELETE FROM users WHERE email = 'zz-lt-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
check('the letter tables exist (created on first use)', LetterService::install());
$clean();

// ---- validation
$bad = LetterService::validate(['letter_date' => '', 'subject' => '', 'body' => 'x', 'from_name' => '']);
check('validation: date, subject, body and sender are required', isset($bad['errors']['letter_date'], $bad['errors']['subject'], $bad['errors']['body'], $bad['errors']['from_name']));
$ok = LetterService::validate(['letter_date' => '2026-11-12', 'subject' => 'ZZ test subject', 'body' => "First paragraph of the letter.\n\n- one\n- two", 'from_name' => 'Jane Doe']);
check('validation: addressee, reference, office and signature are optional', $ok['errors'] === [] && $ok['data']['sig_mode'] === 'blank' && $ok['data']['to_name'] === '', json_encode($ok['errors']));
check('validation: the image signature needs a signatory with a picture', isset(LetterService::validate(['letter_date' => '2026-11-12', 'subject' => 's', 'body' => 'long enough body', 'from_name' => 'A', 'sig_mode' => 'image', 'signatory_id' => 999999])['errors']['signatory_id']));

// ---- recipients
$p = LetterService::parseList("mary@example.org\nJohn Okello <john@example.org>; Grace Auma, grace@example.org, Acme Ltd\nnot-an-email\nMARY@example.org\na@example.org,b@example.org");
$emails = array_column($p['rows'], 'email');
check('parse: plain, "Name <email>" and "Name, email, org" forms, duplicates removed, a joined list split', $emails === ['mary@example.org', 'john@example.org', 'grace@example.org', 'a@example.org', 'b@example.org'], json_encode($emails));
check('parse: names and organisation are kept; invalid lines are reported', $p['rows'][1]['name'] === 'John Okello' && $p['rows'][2]['org'] === 'Acme Ltd' && in_array('not-an-email', $p['bad'], true));
LetterService::addContact('Zz Lt One', 'zz-lt-1@example.test', 'Org', '', 'Partners');
$cid = (int) Database::value("SELECT id FROM letter_contacts WHERE email = 'zz-lt-1@example.test'");
$r = LetterService::recipients("zz-lt-1@example.test\nx@example.test", [$cid]);
check('recipients: a typed address and the same contact are not doubled', count($r['rows']) === 2, json_encode($r['rows']));

// ---- the PDF
$data = $ok['data'] + ['auto_ref' => true];
$id = LetterService::save(null, $data, null);
$letter = LetterService::find($id);
check('save: the reference is numbered automatically', (bool) preg_match('~^[A-Z]+/' . date('Y') . '/\d{3}$~', (string) $letter['ref_no']), (string) $letter['ref_no']);
$pdf = LetterPdf::build(array_merge($letter, ['to_name' => 'Dr. Test Person', 'to_org' => 'Acme Ltd']), ['compress' => false]);
check('pdf: it is a PDF', str_starts_with($pdf, '%PDF') && str_contains($pdf, '%%EOF'));
check('pdf: it holds the RE: line, the body, the addressee and the sender', str_contains($pdf, 'ZZ TEST SUBJECT') && str_contains($pdf, 'First paragraph') && str_contains($pdf, 'Dr. Test Person') && str_contains($pdf, 'Jane Doe'));
$long = LetterPdf::build(array_merge($letter, ['body' => implode("\n\n", array_fill(0, 60, 'A long paragraph that keeps the letter going onto a second page. ' . str_repeat('word ', 30)))]), ['compress' => false]);
check('pdf: a long letter continues on more pages', substr_count($long, '/Type /Page ') + substr_count($long, '/Type/Page ') >= 2 || str_contains($long, 'Page 2 of'));

// ---- sending
$dir = dirname(__DIR__) . '/storage/mail';
$count = static fn(): int => count(glob($dir . '/*.eml') ?: []);
$b = $count();
$res = LetterService::send($letter, [['name' => 'Ann', 'email' => 'zz-lt-a@example.test'], ['name' => '', 'email' => 'zz-lt-b@example.test']], ['mode' => 'each', 'personalise' => true], null);
check('send each: two e-mails, both logged as sent', $res['sent'] === 2 && $res['failed'] === 0 && $count() === $b + 2, json_encode($res));
$latest = glob($dir . '/*.eml') ?: [];
usort($latest, static fn($a, $c) => filemtime($c) <=> filemtime($a));
$eml = (string) file_get_contents($latest[0] ?? '');
check('send each: the e-mail carries the PDF attachment', str_contains($eml, 'application/pdf') && str_contains($eml, '.pdf'));
$again = LetterService::send(LetterService::find($id), [['name' => 'Ann', 'email' => 'zz-lt-a@example.test'], ['name' => '', 'email' => 'zz-lt-c@example.test']], ['mode' => 'each'], null);
check('send: an address that already has the letter is skipped, the new one is sent', $again['sent'] === 1 && $again['skipped'] === 1, json_encode($again));
check('the letter is now marked sent', (string) LetterService::find($id)['status'] === 'sent');
$b = $count();
$one = LetterService::send(LetterService::find($id), [['name' => '', 'email' => 'zz-lt-d@example.test'], ['name' => '', 'email' => 'zz-lt-e@example.test']], ['mode' => 'one', 'hide' => true], null);
$latest = glob($dir . '/*.eml') ?: [];
usort($latest, static fn($a, $c) => filemtime($c) <=> filemtime($a));
$eml = (string) file_get_contents($latest[0] ?? '');
check('send one (hidden): a single e-mail for the group', $one['sent'] === 2 && $count() === $b + 1, json_encode($one));
check('send one (hidden): the addresses are in the hidden list, not in To', str_contains($eml, 'X-Envelope-Bcc') && str_contains($eml, 'zz-lt-d@example.test') && !preg_match('/^To:.*zz-lt-d@/mi', $eml));
$b = $count();
LetterService::send(LetterService::find($id), [['name' => '', 'email' => 'zz-lt-f@example.test'], ['name' => '', 'email' => 'zz-lt-g@example.test']], ['mode' => 'one', 'hide' => false], null);
$latest = glob($dir . '/*.eml') ?: [];
usort($latest, static fn($a, $c) => filemtime($c) <=> filemtime($a));
$eml = (string) file_get_contents($latest[0] ?? '');
check('send one (visible): both addresses are in To', (bool) preg_match('/^To:.*zz-lt-f@example.test.*zz-lt-g@example.test/mi', $eml));

// ---- the admin pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $path, ?array $form = null, ?string $j = null) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 90, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => false]);
    if ($form !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $type = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs), 'type' => $type, 'head' => substr($raw, 0, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $mm) === 1 ? $mm[1] : '';
$apw = 'Letters admin pass 71';
\App\Models\User::create(['name' => 'ZZ Lt Admin', 'email' => 'zz-lt-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$ja = tempnam(sys_get_temp_dir(), 'ltj');
$lp = $req('/admin/login', null, $ja);
$req('/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-lt-admin@example.test', 'password' => $apw], $ja);
$ix = $req('/admin/letters', null, $ja);
check('admin: the list opens and the sidebar has "Official letters"', $ix['code'] === 200 && str_contains($ix['body'], 'ZZ test subject') && str_contains($ix['body'], 'admin/letters'), (string) $ix['code']);
$nw = $req('/admin/letters/new', null, $ja);
check('admin: the form opens with the optional fields', $nw['code'] === 200 && str_contains($nw['body'], 'name="subject"') && str_contains($nw['body'], 'name="to_address"') && str_contains($nw['body'], 'name="sig_mode"'));
$sv = $req('/admin/letters', ['_token' => $tok($nw['body']), 'letter_date' => '2026-11-12', 'subject' => 'ZZ test via form', 'body' => 'Body of the letter made through the form.', 'from_name' => 'Form Sender', 'sig_mode' => 'typed', 'auto_ref' => '1', 'preview' => '1'], $ja);
$newId = (int) Database::value("SELECT id FROM letters WHERE subject = 'ZZ test via form'");
check('admin: saving with "preview" stores the letter and goes to its preview', $newId > 0 && $sv['code'] === 302 && str_contains($sv['head'], '/preview'), (string) $sv['code']);
$pv = $req('/admin/letters/' . $newId . '/preview', null, $ja);
check('admin: the preview page frames the PDF', $pv['code'] === 200 && str_contains($pv['body'], 'preview.pdf'));
$f = $req('/admin/letters/' . $newId . '/preview.pdf', null, $ja);
check('admin: the PDF is served inline as application/pdf', $f['code'] === 200 && str_contains($f['type'], 'application/pdf') && str_starts_with($f['body'], '%PDF'), $f['type']);
$sf = $req('/admin/letters/' . $newId . '/send', null, $ja);
check('admin: the send page opens', $sf['code'] === 200 && str_contains($sf['body'], 'name="typed"') && str_contains($sf['body'], 'value="one"'));
$rv = $req('/admin/letters/' . $newId . '/review', ['_token' => $tok($sf['body']), 'typed' => "Pat Lee <zz-lt-p@example.test>\nzz-lt-q@example.test", 'mode' => 'each', 'subject' => 'S', 'message' => 'M'], $ja);
check('admin: the check page lists the addresses and sends nothing yet', $rv['code'] === 200 && str_contains($rv['body'], 'zz-lt-p@example.test') && Database::value('SELECT COUNT(*) FROM letter_recipients WHERE letter_id = ?', [$newId]) == 0);
preg_match('/name="token" value="([^"]+)"/', $rv['body'], $tm);
$b = $count();
$go = $req('/admin/letters/' . $newId . '/go', ['_token' => $tok($rv['body']), 'token' => html_entity_decode($tm[1] ?? '')], $ja);
check('admin: "Send now" sends one e-mail for each address', $go['code'] === 302 && $count() === $b + 2, (string) $go['code'] . ' ' . ($count() - $b));
$anon = $req('/admin/letters', null, tempnam(sys_get_temp_dir(), 'ltn'));
check('admin: a visitor who is not signed in is sent to the sign-in page', $anon['code'] === 302);

@unlink($ja);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
