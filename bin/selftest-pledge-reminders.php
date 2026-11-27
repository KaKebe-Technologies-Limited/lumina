<?php
declare(strict_types=1);

/**
 * PLEDGE REMINDERS (Admin > Donations: "Remind" and "Send thankful reminders to all"): a warm e-mail to a donor whose pledge is not received yet, with the foundation's
 * account details. Needs MySQL and Apache (real HTTP as a throw-away administrator and editor). Mail goes to files; settings are put back.
 * Run alone: /c/xampp/php/php.exe bin/selftest-pledge-reminders.php
 */

use App\Core\Auth;
use App\Core\Database;
use App\Core\Settings;
use App\Models\User;
use App\Services\Payments\PaymentEvents;
use App\Services\Payments\PledgeReminders;

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

echo "-- pledge reminders --\n";
$keys = ['donate_bank_details', 'donate_mobile_money', 'donate_paypal_email', 'donation_reminder_message', 'pay_opt_manual',
    'official_bank_account_name', 'official_bank_account_number', 'official_bank_name', 'official_bank_branch', 'official_bank_swift', 'official_bank_note'];
$in = implode(',', array_fill(0, count($keys), '?'));
$snapshot = Database::all('SELECT * FROM settings WHERE setting_key IN (' . $in . ')', $keys);
$restore = static function () use ($keys, $in, $snapshot): void {
    Database::execute('DELETE FROM settings WHERE setting_key IN (' . $in . ')', $keys);
    foreach ($snapshot as $row) {
        Database::insert('settings', $row);
    }
    Settings::flush();
};
$cleanup = static function (): void {
    Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-pr-%'");
    Database::execute("DELETE FROM payment_events WHERE reference LIKE 'DON-%' AND who LIKE 'ZZ Pr%'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-pr-%'");
    Database::execute("DELETE FROM audit_log WHERE user_name LIKE '%zz-pr-%'");
    Database::execute('DELETE FROM rate_limits');
};
$cleanup();
PaymentEvents::install();
$mailDir = STORAGE_PATH . '/mail';
$mailText = static function (array $before) use ($mailDir): string {
    $t = '';
    foreach (array_diff(glob($mailDir . '/*.eml') ?: [], $before) as $f) {
        $raw = (string) file_get_contents($f);
        $t .= $raw . "\n";
        if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n([A-Za-z0-9+\/=\r\n]+)/', $raw, $mm)) {
            foreach ($mm[1] as $b64) {
                $t .= (string) base64_decode(preg_replace('/\s+/', '', $b64) ?? '') . "\n";
            }
        }
        if (preg_match('/quoted-printable/i', $raw)) {
            $t .= quoted_printable_decode($raw) . "\n";
        }
    }
    return $t;
};
$mails = static fn(): array => glob($mailDir . '/*.eml') ?: [];
$mailsAtStart = $mails();
$mk = static fn(string $tag, string $status = 'pledged', ?string $amount = '20.00', string $cur = 'USD', string $email = ''): int => (int) Database::insert('donations', [
    'donor_name' => 'ZZ Pr ' . $tag . ' Donor', 'donor_email' => $email !== '' ? $email : 'zz-pr-' . $tag . '@example.test', 'amount' => $amount, 'currency' => $cur, 'method' => 'bank', 'status' => $status, 'source' => 'pledge_form',
]);

// every box empty, saved empty on purpose (what a settings form with blank boxes stores): the foundation's built-in official account is still the account
Settings::setMany(['donate_bank_details' => '', 'donate_mobile_money' => '', 'donate_paypal_email' => '', 'donation_reminder_message' => '', 'pay_opt_manual' => '1',
    'official_bank_account_name' => '', 'official_bank_account_number' => '', 'official_bank_name' => '', 'official_bank_branch' => '', 'official_bank_swift' => '', 'official_bank_note' => '']);
$a0 = $mk('a0');
$det = PledgeReminders::paymentDetails('DON-' . $a0);
check('every box empty: the payment details are the official account (name, number, bank, branch), the reference to quote, the note and where to send the proof',
    str_starts_with($det, "Bank transfer\n") && str_contains($det, 'Account Name: Lumina Africa Foundation') && str_contains($det, 'Account Number: 4090008000229') && str_contains($det, 'Bank: Pearl Bank') && str_contains($det, 'Branch: Gulu')
    && str_contains($det, 'Payment reference: DON-' . $a0) && str_contains($det, 'proof of payment'), $det);
check('no reference given: no "Payment reference" line (the details are still complete)', !str_contains(PledgeReminders::paymentDetails(), 'Payment reference') && str_contains(PledgeReminders::paymentDetails(), '4090008000229'));
\App\Services\Payments\PaymentOptions::save(['manual' => false]);
check('manual details switched off on screen: the e-mail a staff member sends still lists the account (the switch hides it from visitors only)', str_contains(PledgeReminders::paymentDetails('DON-1'), '4090008000229') && !\App\Services\Payments\OfficialAccount::visible());
$before = $mails();
$r = PledgeReminders::remind($a0, null);
$text0 = $mailText($before);
check('nothing typed in any box, switch off: the reminder is NOT refused for missing bank details; it is sent with the official account and DON-' . $a0, $r['ok'] && count($mails()) === count($before) + 1 && str_contains($text0, '4090008000229') && str_contains($text0, 'Pearl Bank') && str_contains($text0, 'DON-' . $a0), json_encode($r));
\App\Services\Payments\PaymentOptions::save(['manual' => true]);
$pr = (int) Database::insert('donations', ['donor_name' => 'ZZ Pr proj Donor', 'donor_email' => 'zz-pr-proj@example.test', 'amount' => '500000', 'currency' => 'UGX', 'method' => 'bank', 'status' => 'pledged', 'source' => 'pledge_form', 'reference' => 'PROJECT:digital-inclusion|item:laptopx1']);
$short = (string) (\App\Services\Fund\FundProjects::find('digital-inclusion')['short'] ?? '');
check('a gift to a project is quoted as "<project short name> DON-<id>" and its PROJECT: reference is left alone', $short !== '' && PledgeReminders::reference((array) Database::one('SELECT * FROM donations WHERE id = ?', [$pr])) === $short . ' DON-' . $pr
    && str_contains(PledgeReminders::message((array) Database::one('SELECT * FROM donations WHERE id = ?', [$pr])), 'Payment reference: ' . $short . ' DON-' . $pr) && (string) Database::value('SELECT reference FROM donations WHERE id = ?', [$pr], '') === 'PROJECT:digital-inclusion|item:laptopx1');

$a = $mk('a');
Settings::setMany(['donate_bank_details' => "Bank: Pearl Bank\nAccount Name: Lumina Africa Foundation\nAccount Number: 4090008000229", 'donate_mobile_money' => 'MTN: 0772 000 111']);
$before = $mails();
$r = PledgeReminders::remind($a, null);
$text = $mailText($before);
check('reminder is sent for a waiting pledge', $r['ok'] && count($mails()) === count($before) + 1, json_encode($r));
check('the e-mail is thankful, names the donor, the amount and why gifts matter', str_contains($text, 'Dear ZZ,') && str_contains($text, 'Thank you') && str_contains($text, 'USD 20') && str_contains($text, 'generous pledge') && str_contains($text, 'Northern Uganda') && str_contains($text, 'makes a real difference'), substr($text, 0, 400));
check('the e-mail carries the account details (bank, account name and number, mobile money) and the donate link', str_contains($text, 'Pearl Bank') && str_contains($text, 'Account Name: Lumina Africa Foundation') && str_contains($text, '4090008000229') && str_contains($text, 'MTN: 0772 000 111') && str_contains($text, 'donate'));
$msgA = PledgeReminders::message((array) Database::one('SELECT * FROM donations WHERE id = ?', [$a]));
check('the account is told ONCE although the old free-text box repeats it, with Gulu, the reference DON-' . $a . ' and the proof-of-payment sentence', substr_count($msgA, '4090008000229') === 1 && str_contains($msgA, 'Gulu') && str_contains($msgA, 'Payment reference: DON-' . $a) && str_contains($msgA, 'proof of payment')
    && str_contains($text, 'Gulu') && str_contains($text, 'DON-' . $a), (string) substr_count($msgA, '4090008000229'));
Settings::setMany(['donate_bank_details' => 'Please write your full name too.']);
check('free text that does not hold the official number is kept as an addition under the account', str_contains(PledgeReminders::paymentDetails('DON-' . $a), "Payment reference: DON-{$a}\n\nPlease write your full name too.") && substr_count(PledgeReminders::paymentDetails(), '4090008000229') === 1);
Settings::setMany(['donate_bank_details' => "Bank: Pearl Bank\nAccount Name: Lumina Africa Foundation\nAccount Number: 4090008000229"]);
check('the e-mail asks for the transaction number and leaves no unfilled {placeholder}', str_contains($text, 'transaction number') && preg_match('/\{[a-z_]+\}/', $text) !== 1);
check('the reminder is written to the payment audit trail', (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'reminded'", ['DON-' . $a], 0) === 1);
check('a second reminder within 24 hours is refused (no spam)', !PledgeReminders::remind($a, null)['ok'] && count($mails()) === count($before) + 1);

$rec = $mk('b', 'received');
$none = $mk('c', 'pledged', '20.00', 'USD', 'zz-pr-bad email');
$noAmt = $mk('d', 'pledged', null);
check('a received donation, a pledge with a bad e-mail and a pledge without an amount are never reminded', !PledgeReminders::remind($rec, null)['ok'] && !PledgeReminders::remind($none, null)['ok'] && !PledgeReminders::remind($noAmt, null)['ok']);
$ids = array_map(static fn(array $d): int => (int) $d['id'], PledgeReminders::pending(500));
check('the waiting list contains only pledged donations with a usable e-mail and an amount', in_array($a, $ids, true) && !in_array($rec, $ids, true) && !in_array($none, $ids, true) && !in_array($noAmt, $ids, true));

Settings::setMany(['donation_reminder_message' => "Hi {first_name}, thanks for {amount} on {pledge_date}. Send to:\n{payment_details}"]);
$e = $mk('e');
$msg = PledgeReminders::message((array) Database::one('SELECT * FROM donations WHERE id = ?', [$e]));
check('a customised wording is used, with every placeholder filled', str_starts_with($msg, 'Hi ZZ, thanks for USD 20.00 on ') && str_contains($msg, 'Pearl Bank') && preg_match('/\{[a-z_]+\}/', $msg) !== 1, $msg);
Settings::setMany(['donation_reminder_message' => '']);

// ------------------------------------------------------------------------------------------------------------- over HTTP
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'prj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 90, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$post = static function (string $path, array $form, ?string &$jar) use ($req, $tok): array {
    $page = $req('GET', '/admin/donations', [], $jar);
    return $req('POST', $path, $form + ['_token' => $tok($page['body'])], $jar);
};
$mkU = static fn(string $email, string $role, string $pw): int => (int) User::create(['name' => 'ZZ Pr ' . $role, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$pwA = 'Admin pr pass 51';
$pwE = 'Editor pr pass 53';
$mkU('zz-pr-admin@example.test', 'admin', $pwA);
$mkU('zz-pr-editor@example.test', 'editor', $pwE);
$login = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    return in_array($req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar)['code'], [302, 303], true);
};
$jA = $jE = null;
if (!$login('zz-pr-admin@example.test', $pwA, $jA) || !$login('zz-pr-editor@example.test', $pwE, $jE)) {
    check('http: the test administrator and editor can sign in (the web server must be running)', false);
} else {
    $page = $req('GET', '/admin/donations?q=zz-pr-', [], $jA);
    check('http: the list shows "Send thankful reminders to all" and a Remind button on a waiting pledge, none on a received one', $page['code'] === 200 && str_contains($page['body'], 'Send thankful reminders to all') && str_contains($page['body'], '/donations/' . $e . '/remind') && !str_contains($page['body'], '/donations/' . $rec . '/remind'));
    $before = $mails();
    $one = $post('/admin/donations/' . $e . '/remind', [], $jA);
    check('http: "Remind" on one pledge sends one e-mail with the account details and returns to the list', in_array($one['code'], [302, 303], true) && count($mails()) === count($before) + 1 && str_contains($mailText($before), '4090008000229'));
    $before = $mails();
    $all = $post('/admin/donations/remind-all', [], $jA);
    $sentTo = $mailText($before);
    $aAgain = Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'reminded'", ['DON-' . $a], 0);
    check('http: "Send thankful reminders to all" mails the waiting pledges that were not reminded today and skips the ones that were', in_array($all['code'], [302, 303], true) && (int) $aAgain === 1 && (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'reminded'", ['DON-' . $e], 0) === 1);
    $pg = $req('GET', '/admin/donations', [], $jA);
    check('http: the Donations page shows the "Your account details" box to an administrator', str_contains($pg['body'], 'Your account details') && str_contains($pg['body'], 'name="bank"') && str_contains($pg['body'], 'Pearl Bank'));
    check('http: the box edits the five official fields and the note (name, number, bank, branch, SWIFT) with the current values, and the old box is now "Extra bank instructions"',
        preg_match('/id="acc-oname"[^>]*name="official_name"[^>]*value="Lumina Africa Foundation"/', $pg['body']) === 1 && preg_match('/id="acc-onumber"[^>]*name="official_number"[^>]*value="4090008000229"/', $pg['body']) === 1
        && preg_match('/name="official_bank"[^>]*value="Pearl Bank"/', $pg['body']) === 1 && preg_match('/name="official_branch"[^>]*value="Gulu"/', $pg['body']) === 1 && str_contains($pg['body'], 'name="official_swift"') && str_contains($pg['body'], 'name="official_note"')
        && str_contains($pg['body'], 'Extra bank instructions') && str_contains($pg['body'], 'Shown to donors'));
    \App\Services\Payments\PaymentOptions::save(['manual' => false]);
    $pgOff = $req('GET', '/admin/donations', [], $jA);
    \App\Services\Payments\PaymentOptions::save(['manual' => true]);
    check('http: while "Bank and manual mobile-money details" is off the box says the account is hidden from visitors', str_contains($pgOff['body'], 'Hidden (switched off)'));
    $sv = $post('/admin/donations/account-details', ['bank' => "Bank: Test Bank\nAccount Number: 111222", 'mobile' => 'MTN: 0772 999 000', 'paypal' => ''], $jA);
    Settings::flush();
    check('http: saving the account details writes the same settings as Site settings > Donations (a form without official boxes leaves the official account alone)', in_array($sv['code'], [302, 303], true) && str_contains((string) Settings::get('donate_bank_details', ''), 'Test Bank') && (string) Settings::get('donate_mobile_money', '') === 'MTN: 0772 999 000'
        && \App\Services\Payments\OfficialAccount::value('number') === '4090008000229');
    $bp = $post('/admin/donations/account-details', ['bank' => 'Changed', 'mobile' => '', 'paypal' => 'not an email'], $jA);
    Settings::flush();
    check('http: a PayPal value that is not an e-mail address is refused and nothing changes', str_contains((string) Settings::get('donate_bank_details', ''), 'Test Bank'));

    // ---- the official account: valid change, refusals, and "empty = built-in"
    $OA = \App\Services\Payments\OfficialAccount::class;
    $full = ['bank' => 'Pay in person too', 'mobile' => 'MTN: 0772 999 000', 'paypal' => '', 'official_name' => 'Lumina Africa Foundation Test', 'official_number' => '1234 5678 9012', 'official_bank' => 'Zed Test Bank', 'official_branch' => 'Kampala', 'official_swift' => 'abcd ug ka', 'official_note' => 'Zed note'];
    $ok = $post('/admin/donations/account-details', $full, $jA);
    Settings::flush();
    check('http: a valid official account is saved in the official_bank_* settings (the SWIFT without spaces, in capitals)', in_array($ok['code'], [302, 303], true) && $OA::value('name') === 'Lumina Africa Foundation Test' && $OA::value('number') === '1234 5678 9012'
        && (string) Settings::get('official_bank_name', '') === 'Zed Test Bank' && $OA::value('branch') === 'Kampala' && (string) Settings::get('official_bank_swift', '') === 'ABCDUGKA' && $OA::note() === 'Zed note' && (string) Settings::get('donate_bank_details', '') === 'Pay in person too');
    $aud = (string) Database::value("SELECT summary FROM audit_log WHERE action = 'donation.account_details' AND user_name LIKE '%zz-pr-admin%' ORDER BY id DESC LIMIT 1", [], '');
    check('http: the audit line says which official fields changed and only the last four digits of the number', str_contains($aud, 'ends in 9012') && str_contains($aud, 'number') && !str_contains($aud, '1234') && !str_contains($aud, '5678'), $aud);
    $refused = [
        'a number with symbols' => ['official_number' => '12#34'],
        'a number that is too long' => ['official_number' => str_repeat('1', 41)],
        'a SWIFT code of 5 letters' => ['official_swift' => 'ABCDE'],
        'an account name over 80 characters' => ['official_name' => str_repeat('N', 81)],
        'a note over 300 characters' => ['official_note' => str_repeat('n', 301)],
    ];
    foreach ($refused as $what => $bad) {
        $res = $post('/admin/donations/account-details', ['official_bank' => 'Should Not Be Saved', 'bank' => 'x', 'mobile' => '', 'paypal' => ''] + $bad, $jA);
        Settings::flush();
        check('http: ' . $what . ' is refused and NOTHING in the same form is saved', in_array($res['code'], [302, 303], true) && (string) Settings::get('official_bank_name', '') === 'Zed Test Bank' && $OA::value('number') === '1234 5678 9012' && (string) Settings::get('official_bank_swift', '') === 'ABCDUGKA' && (string) Settings::get('donate_bank_details', '') === 'Pay in person too' && $OA::note() === 'Zed note');
    }
    $reset = $post('/admin/donations/account-details', ['bank' => '', 'mobile' => '', 'paypal' => '', 'official_name' => '', 'official_number' => '', 'official_bank' => 'Pearl Bank', 'official_branch' => '', 'official_swift' => '', 'official_note' => ''], $jA);
    Settings::flush();
    check('http: empty boxes (or a box equal to the built-in value) go back to the built-in account: the saved rows are removed, so the account can never be blanked', in_array($reset['code'], [302, 303], true) && $OA::get() === ['name' => 'Lumina Africa Foundation', 'number' => '4090008000229', 'bank' => 'Pearl Bank', 'branch' => 'Gulu', 'swift' => '']
        && !Settings::isCustomised('official_bank_account_number') && !Settings::isCustomised('official_bank_name') && !Settings::isCustomised('official_bank_swift') && !Settings::isCustomised('official_bank_note') && $OA::isBuiltIn());
    check('http: an editor cannot change the account details (administrators only)', $req('POST', '/admin/donations/account-details', ['_token' => 'x', 'bank' => 'x', 'official_number' => '1'], $jE)['code'] === 403 && $OA::value('number') === '4090008000229');
    check('http: an editor (no inbox.manage) is refused',$req('POST', '/admin/donations/' . $a . '/remind', ['_token' => 'x'], $jE)['code'] === 403 && $req('POST', '/admin/donations/remind-all', ['_token' => 'x'], $jE)['code'] === 403);
}

$restore();
$cleanup();
foreach (array_diff($mails(), $mailsAtStart) as $f) {
    if (str_contains((string) file_get_contents($f), 'zz-pr-')) {
        @unlink($f);
    }
}
check('cleanup: no zz-pr rows are left and the settings are back as they were', (int) Database::value("SELECT COUNT(*) FROM donations WHERE donor_email LIKE 'zz-pr-%'", [], 0) === 0 && (int) Database::value('SELECT COUNT(*) FROM settings WHERE setting_key IN (' . $in . ')', $keys, 0) === count($snapshot));

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
