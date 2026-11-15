<?php
declare(strict_types=1);

/**
 * ACADEMY FEES & PAYMENTS PAGE (Admin > LAF Academy > Fees & payments): the fees, the payment details and the editable messages, and that the website and the portal
 * read exactly what the page saves. Needs MySQL and Apache (real HTTP as a throw-away administrator and editor); every setting it touches is put back.
 * Run alone: /c/xampp/php/php.exe bin/selftest-academy-fees-page.php
 */

use App\Core\Auth;
use App\Core\Database;
use App\Core\Settings;
use App\Models\User;
use App\Services\Academy\AcademyFeeMessages;
use App\Services\Academy\AcademyFees;
use App\Services\Academy\CertificatePayReminders;

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

echo "-- academy fees page --\n";
$keys = ['academy_signup_fee', 'academy_signup_fee_currency', 'academy_cert_fee', 'academy_cert_fee_currency', 'academy_signup_fee_enabled', 'academy_fee_auto_approve', 'academy_cert_auto_release',
    'academy_pay_mobile_money', 'academy_pay_bank_details', 'pay_opt_manual', 'academy_msg_cert_pay', 'academy_msg_signup_pay', 'academy_msg_cert_portal'];
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
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-faf-%'");
    Database::execute('DELETE FROM rate_limits');
};
$cleanup();
$mailsBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];

// ------------------------------------------------------------------------------------------------------------- the message engine
Database::execute('DELETE FROM settings WHERE setting_key IN (' . $in . ')', $keys);
Settings::flush();
$r = AcademyFeeMessages::fill('Hello {first_name}, pay ({fee}) now . {nope}', ['first_name' => 'Amina', 'fee' => '']);
check('messages: placeholders are filled, an empty fee leaves no "()" or stray spaces, an unknown {word} stays visible', $r === 'Hello Amina, pay now. {nope}', $r);
check('messages: with nothing saved the built-in wording is used and is not "customised"', !AcademyFeeMessages::isCustom('cert_pay') && AcademyFeeMessages::template('cert_pay') === AcademyFeeMessages::defaultText('cert_pay'));
check('messages: every built-in text uses only placeholders it declares', (static function (): bool {
    foreach (AcademyFeeMessages::all() as $k => $m) {
        preg_match_all('/\{([a-z_]+)\}/', $m['default'], $found);
        if (array_diff($found[1], $m['placeholders']) !== []) {
            return false;
        }
    }
    return true;
})());
check('messages: check() accepts empty and normal text, refuses a few words and a very long text', AcademyFeeMessages::check('') === null && AcademyFeeMessages::check('Hello {first_name}, please pay the fee.') === null && AcademyFeeMessages::check('Pay') !== null && AcademyFeeMessages::check(str_repeat('a', 1501)) !== null);

Settings::setMany(['academy_cert_fee' => '15000', 'academy_cert_fee_currency' => 'UGX', 'academy_signup_fee' => '20000', 'academy_signup_fee_currency' => 'UGX', 'academy_pay_mobile_money' => "MTN: 0772 000 111\nName: Lumina", 'academy_pay_bank_details' => 'Stanbic 0001', 'pay_opt_manual' => '1']);
$def = CertificatePayReminders::text('Amina Test', ['Microsoft Word']);
check('default certificate reminder: says the certificate is ready, names the course and the fee, and carries the payment details', str_contains($def, 'Hello Amina,') && str_contains($def, 'is ready') && str_contains($def, '"Microsoft Word"') && str_contains($def, 'UGX 15,000') && str_contains($def, 'MTN: 0772 000 111, Name: Lumina') && str_contains($def, 'Stanbic 0001'), $def);
$def2 = AcademyFees::reminderText(['name' => 'Amina Test']);
check('default sign-up reminder: names the fee, how to pay and the sign-in link', str_contains($def2, 'sign-up fee (UGX 20,000)') && str_contains($def2, 'You can pay by mobile money') && str_contains($def2, 'academy/login'), $def2);
Settings::setMany(['academy_msg_cert_pay' => 'Dear {first_name}: your {courses} certificate awaits. Fee {fee}. Details: {pay_details}', 'academy_msg_signup_pay' => 'Join us {first_name}! Fee {fee}.', 'academy_msg_cert_portal' => 'Almost there! Pay {fee} for {courses}.']);
check('customised wording is what the reminders now send', CertificatePayReminders::text('Amina Test', ['Excel']) === 'Dear Amina: your "Excel" certificate awaits. Fee UGX 15,000. Details: Mobile money: MTN: 0772 000 111, Name: Lumina. Bank: Stanbic 0001.' && AcademyFees::reminderText(['name' => 'Amina Test']) === 'Join us Amina! Fee UGX 20,000.', CertificatePayReminders::text('Amina Test', ['Excel']));
check('customised wording is what the learner portal shows (the notice next to a certificate waiting for its fee)', AcademyFeeMessages::render('cert_portal', ['fee' => 'UGX 15,000', 'courses' => 'Excel']) === 'Almost there! Pay UGX 15,000 for Excel.');
Database::execute("DELETE FROM settings WHERE setting_key IN ('academy_msg_cert_pay','academy_msg_signup_pay','academy_msg_cert_portal')");
Settings::flush();

// ------------------------------------------------------------------------------------------------------------- over HTTP
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'faf');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
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
    $page = $req('GET', '/admin/academy/fees', [], $jar);
    return $req('POST', $path, $form + ['_token' => $tok($page['body'])], $jar);
};
$mk = static fn(string $email, string $role, string $pw): int => (int) User::create(['name' => 'ZZ Faf ' . $role, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$pwA = 'Admin faf pass 71';
$pwE = 'Editor faf pass 73';
$mk('zz-faf-admin@example.test', 'admin', $pwA);
$mk('zz-faf-editor@example.test', 'editor', $pwE);
$login = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    return in_array($req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar)['code'], [302, 303], true);
};
$jA = $jE = null;
if (!$login('zz-faf-admin@example.test', $pwA, $jA) || !$login('zz-faf-editor@example.test', $pwE, $jE)) {
    check('http: the test administrator and editor can sign in (the web server must be running)', false);
} else {
    $page = $req('GET', '/admin/academy/fees', [], $jA);
    check('http: the page opens for an administrator with its four sections and the menu entry', $page['code'] === 200 && str_contains($page['body'], 'id="fees"') && str_contains($page['body'], 'id="details"') && str_contains($page['body'], 'id="messages"') && str_contains($page['body'], 'Fees &amp; payments'), (string) $page['code']);
    check('http: an editor (no academy.manage) is refused', $req('GET', '/admin/academy/fees', [], $jE)['code'] === 403 && $req('POST', '/admin/academy/fees/fees', ['_token' => 'x'], $jE)['code'] === 403);

    $bad = $post('/admin/academy/fees/fees', ['signup_amount' => '12abc', 'signup_currency' => 'UGX', 'cert_amount' => '5000', 'cert_currency' => 'UGX'], $jA);
    check('fees: a non-numeric amount is refused and nothing is saved', in_array($bad['code'], [302, 303], true) && (string) Settings::get('academy_signup_fee', '') !== '12abc' && (string) Settings::get('academy_cert_fee', '') !== '5000');
    $badCur = $post('/admin/academy/fees/fees', ['signup_amount' => '1000', 'signup_currency' => 'XXX', 'cert_amount' => '5000', 'cert_currency' => 'UGX'], $jA);
    check('fees: a currency outside the list is refused', in_array($badCur['code'], [302, 303], true) && (string) Settings::get('academy_cert_fee', '') !== '5000');
    $ok = $post('/admin/academy/fees/fees', ['signup_on' => '1', 'signup_amount' => '25,000', 'signup_currency' => 'UGX', 'cert_amount' => '7500', 'cert_currency' => 'USD', 'auto_approve' => '1'], $jA);
    Settings::flush();
    check('fees: saved to the same settings the website reads (amounts, currencies, switches); a box left unticked saves as off', in_array($ok['code'], [302, 303], true) && (string) Settings::get('academy_signup_fee', '') === '25000' && Settings::get('academy_cert_fee_currency', '') === 'USD'
        && AcademyFees::configured('signup')['minor'] === 2500000 && AcademyFees::configured('cert')['currency'] === 'USD' && AcademyFees::signupEnabled() && (string) Settings::get('academy_cert_auto_release', '1') === '0' && (string) Settings::get('academy_fee_auto_approve', '0') === '1');
    $off = $post('/admin/academy/fees/fees', ['signup_amount' => '25000', 'signup_currency' => 'UGX', 'cert_amount' => '0', 'cert_currency' => 'UGX'], $jA);
    Settings::flush();
    check('fees: switching the sign-up fee off makes the website treat sign-up as free, and a certificate fee of 0 means free', !AcademyFees::signupEnabled() && AcademyFees::configured('signup') === null && AcademyFees::configured('cert') === null);

    $det = $post('/admin/academy/fees/details', ['mobile' => "MTN: 0772 111 222\nAirtel: 0700 111 222", 'bank' => 'Stanbic Bank, 0001234', 'manual_on' => '1'], $jA);
    Settings::flush();
    $m = AcademyFees::manual();
    check('details: saved, switched on, and returned by AcademyFees::manual() (what the portal shows learners)', in_array($det['code'], [302, 303], true) && $m !== null && str_contains($m['mobile'], 'Airtel: 0700 111 222') && $m['bank'] === 'Stanbic Bank, 0001234');
    $page = $req('GET', '/admin/academy/fees', [], $jA);
    check('details: the page shows the saved details in the boxes', str_contains($page['body'], 'Airtel: 0700 111 222') && str_contains($page['body'], 'Stanbic Bank, 0001234'));
    $post('/admin/academy/fees/details', ['mobile' => 'MTN: 0772 111 222', 'bank' => ''], $jA);
    Settings::flush();
    check('details: without the "Show these details" tick the details are saved but switched off (learners see none)', AcademyFees::manual() === null && (string) Settings::get('academy_pay_mobile_money', '') === 'MTN: 0772 111 222');
    $long = $post('/admin/academy/fees/details', ['mobile' => str_repeat('x', 1001)], $jA);
    Settings::flush();
    check('details: a box over 1000 characters is refused', (string) Settings::get('academy_pay_mobile_money', '') === 'MTN: 0772 111 222');

    $msg = $post('/admin/academy/fees/messages', ['msg_cert_pay' => 'Dear {first_name}, your certificate is waiting: pay {fee}. {how_to_pay}', 'msg_signup_pay' => '', 'msg_cert_portal' => ''], $jA);
    Settings::flush();
    check('messages: a customised text is saved and used; empty boxes stay on the built-in wording', in_array($msg['code'], [302, 303], true) && AcademyFeeMessages::isCustom('cert_pay') && !AcademyFeeMessages::isCustom('signup_pay') && str_starts_with(CertificatePayReminders::text('Amina T', ['X']), 'Dear Amina, your certificate is waiting'));
    $page = $req('GET', '/admin/academy/fees', [], $jA);
    check('messages: the page shows the customised text, the "Customised" badge and the placeholder buttons', str_contains($page['body'], 'your certificate is waiting: pay {fee}') && str_contains($page['body'], 'Customised') && str_contains($page['body'], 'data-af-insert="{pay_details}"'));
    $short = $post('/admin/academy/fees/messages', ['msg_cert_pay' => 'Pay', 'msg_signup_pay' => '', 'msg_cert_portal' => ''], $jA);
    Settings::flush();
    check('messages: a text that is too short is refused and the earlier one is kept', AcademyFeeMessages::isCustom('cert_pay') && str_contains(AcademyFeeMessages::template('cert_pay'), 'your certificate is waiting'));
    $post('/admin/academy/fees/messages', ['reset_cert_pay' => '1', 'msg_cert_pay' => 'ignored text that is long enough', 'msg_signup_pay' => '', 'msg_cert_portal' => ''], $jA);
    Settings::flush();
    check('messages: "Use the built-in wording" brings the default back', !AcademyFeeMessages::isCustom('cert_pay'));
    $same = $post('/admin/academy/fees/messages', ['msg_cert_pay' => AcademyFeeMessages::defaultText('cert_pay'), 'msg_signup_pay' => '', 'msg_cert_portal' => ''], $jA);
    Settings::flush();
    check('messages: saving the built-in text unchanged is stored as "not customised" (a later improvement of the default still reaches this site)', !AcademyFeeMessages::isCustom('cert_pay'));
    check('http: an unknown section is not routed', $req('POST', '/admin/academy/fees/other', ['_token' => $tok($req('GET', '/admin/academy/fees', [], $jA)['body'])], $jA)['code'] === 404);
}

$restore();
$cleanup();
foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore) as $f) {
    @unlink($f);
}
check('cleanup: the settings are back as they were and no zz-faf users are left', (int) Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-faf-%'", [], 0) === 0 && (int) Database::value('SELECT COUNT(*) FROM settings WHERE setting_key IN (' . $in . ')', $keys, 0) === count($snapshot));

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
