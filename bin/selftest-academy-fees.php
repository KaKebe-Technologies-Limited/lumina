<?php
declare(strict_types=1);

/**
 * Academy: the sign-up fee and the certificate fee, paid through Pesapal (mock transport: nothing leaves the computer). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-fees.php
 * Needs the local web server for the admin page checks. Removes its own rows (zz-fee-*) and puts the settings back.
 */

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

use App\Core\Auth;
use App\Core\Database;
use App\Core\Settings;
use App\Models\User;
use App\Services\Academy\AcademyFees;
use App\Services\Academy\CertificateService;
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerRegistry;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\PesapalPayments;

echo "-- academy fees (Pesapal) --\n";

// ---- a pretend Pesapal ------------------------------------------------------------------------------------------------------------
$mock = ['status' => []];
PesapalClient::$transport = static function (string $method, string $url, array $headers, ?string $body) use (&$mock): array {
    $path = (string) parse_url($url, PHP_URL_PATH);
    $json = $body !== null ? json_decode($body, true) : [];
    if (str_ends_with($path, '/api/Auth/RequestToken')) {
        return ['http' => 200, 'raw' => json_encode(['token' => 'TKN', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/URLSetup/RegisterIPN')) {
        return ['http' => 200, 'raw' => json_encode(['ipn_id' => 'aaaaaaaa-1111-2222-3333-cccccccccccc', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/Transactions/SubmitOrderRequest')) {
        $id = (string) ($json['id'] ?? '');
        $mock['last'] = $json;
        return ['http' => 200, 'raw' => json_encode(['order_tracking_id' => 'trk-' . strtolower(substr(md5($id), 0, 24)), 'merchant_reference' => $id, 'redirect_url' => 'https://cybqa.pesapal.com/pesapaliframe/x', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/Transactions/GetTransactionStatus')) {
        return ['http' => 200, 'raw' => json_encode($mock['status'])];
    }
    return ['http' => 404, 'raw' => ''];
};
$answer = static function (string $trk, string $ref, int $amount, string $cur = 'UGX', int $code = 1) use (&$mock): void {
    $mock['status'] = ['status_code' => $code, 'payment_status_description' => 'Completed', 'amount' => $amount, 'currency' => $cur, 'merchant_reference' => $ref, 'payment_method' => 'MtnUganda', 'confirmation_code' => 'C' . random_int(1000, 9999)];
};

$keys = [getenv('PESAPAL_CONSUMER_KEY'), getenv('PESAPAL_CONSUMER_SECRET'), getenv('PESAPAL_ENV')];
$before = [];
foreach (['academy_signup_fee', 'academy_signup_fee_currency', 'academy_cert_fee', 'academy_cert_fee_currency', 'academy_cert_approval', 'academy_signup_mode'] as $k) {
    $before[$k] = Settings::isCustomised($k) ? Settings::get($k) : null;
}
$clean = static function (): void {
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Fee%'");
    Database::execute("DELETE FROM pesapal_payments WHERE payer_email LIKE 'zz-fee-%@example.test'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-fee-%@example.test'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-fee-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
check('the fee columns exist in the database', AcademyFees::ready());

// ---- when a fee is charged ----------------------------------------------------------------------------------------------------------
putenv('PESAPAL_CONSUMER_KEY');
putenv('PESAPAL_CONSUMER_SECRET');
Settings::setMany(['academy_signup_fee' => '20000', 'academy_signup_fee_currency' => 'UGX', 'academy_cert_fee' => '15000', 'academy_cert_fee_currency' => 'UGX']);
check('no Pesapal keys: the fees are set but nobody is charged (no one is blocked)', AcademyFees::configured('signup') !== null && AcademyFees::signup() === null && AcademyFees::cert() === null);
putenv('PESAPAL_CONSUMER_KEY=selftest-key');
putenv('PESAPAL_CONSUMER_SECRET=selftest-secret');
putenv('PESAPAL_ENV=sandbox');
check('with the keys: sign-up fee UGX 20,000 and certificate fee UGX 15,000 are charged', AcademyFees::signup()['minor'] === 2000000 && AcademyFees::signup()['display'] === 'UGX 20,000' && AcademyFees::cert()['display'] === 'UGX 15,000', json_encode(AcademyFees::signup()));
Settings::setMany(['academy_signup_fee' => '0']);
check('an amount of 0 means free', AcademyFees::signup() === null && AcademyFees::configured('signup') === null);
Settings::setMany(['academy_signup_fee' => 'abc', 'academy_signup_fee_currency' => 'XXX']);
check('rubbish in the setting never charges anything', AcademyFees::signup() === null);
Settings::setMany(['academy_signup_fee' => '20000', 'academy_signup_fee_currency' => 'UGX']);

// ---- sign-up fee ----------------------------------------------------------------------------------------------------------------------
Settings::set('academy_signup_mode', 'approval');
$pw = 'Fee test pass 12';
$reg = LearnerAuth::register(['name' => 'ZZ Fee One', 'email' => 'zz-fee-one@example.test', 'password' => $pw, 'phone' => '+256700000011', 'country' => 'Uganda', 'whatsapp' => '+256700000011'], '10.9.9.1');
check('sign-up with a fee: the account is created with the fee DUE', $reg['ok'] === true && ($reg['learner']['fee_status'] ?? '') === 'due', json_encode($reg['errors'] ?? []));
$lid = (int) ($reg['learner']['id'] ?? 0);
$login = LearnerAuth::login('zz-fee-one@example.test', $pw, '10.9.9.1');
check('login with the right password but an unpaid fee: refused as "fee due", never signed in', $login['ok'] === false && !empty($login['fee_due']) && !empty($login['unverified']));
LearnerAuth::signIn((array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$lid]));
LearnerAuth::flush();
check('even a session of an unpaid account does not count as signed in', LearnerAuth::current() === null);
check('the administrator cannot approve an account whose fee is unpaid', LearnerRegistry::approve($lid, null) === false && Database::value('SELECT email_verified_at FROM academy_learners WHERE id = ?', [$lid]) === null);
$begin = AcademyFees::beginSignup($lid);
$prow = $begin['ok'] ? PesapalPayments::byReference((string) ($begin['reference'] ?? '')) : null;
// begin() returns the link; find the pending row by learner
$prow = Database::one("SELECT * FROM pesapal_payments WHERE purpose = 'academy_signup' AND ref_id = ? ORDER BY id DESC LIMIT 1", [$lid]);
check('paying: Pesapal is asked for the fee amount, the description names the fee', $begin['ok'] === true && $prow !== null && (int) $prow['amount_minor'] === 2000000 && str_contains((string) ($mock['last']['description'] ?? ''), 'sign-up fee') && (float) ($mock['last']['amount'] ?? 0) === 20000.0);
$answer((string) $prow['order_tracking_id'], (string) $prow['merchant_reference'], 20000);
$s = PesapalPayments::settle((string) $prow['order_tracking_id']);
$row = (array) Database::one('SELECT fee_status, fee_paid_at, email_verified_at FROM academy_learners WHERE id = ?', [$lid]);
check('Pesapal confirms: the fee is paid and the account is APPROVED automatically', $s['state'] === 'paid' && $row['fee_status'] === 'paid' && $row['fee_paid_at'] !== null && $row['email_verified_at'] !== null);
$again = PesapalPayments::settle((string) $prow['order_tracking_id']);
check('a repeated notification changes nothing', $again['state'] === 'paid' && Database::value('SELECT fee_paid_at FROM academy_learners WHERE id = ?', [$lid]) === $row['fee_paid_at']);
$login = LearnerAuth::login('zz-fee-one@example.test', $pw, '10.9.9.1');
check('after paying, the learner can sign in straight away', $login['ok'] === true);
check('the automatic approval is written to the audit log', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'academy.learner.auto_approved' AND entity_id = ?", [(string) $lid], 0) === 1);
check('a second payment attempt for a paid fee is refused', AcademyFees::beginSignup($lid)['error'] === 'not_due');

// with the switch off, Pesapal payment only marks the fee paid: the normal approval follows
Settings::set('academy_fee_auto_approve', '0');
$reg4 = LearnerAuth::register(['name' => 'ZZ Fee Four', 'email' => 'zz-fee-four@example.test', 'password' => $pw, 'phone' => '+256700000014', 'country' => 'Uganda', 'whatsapp' => '+256700000014'], '10.9.9.4');
$l4 = (int) $reg4['learner']['id'];
AcademyFees::beginSignup($l4);
$p4 = (array) Database::one("SELECT * FROM pesapal_payments WHERE purpose = 'academy_signup' AND ref_id = ? ORDER BY id DESC LIMIT 1", [$l4]);
$answer((string) $p4['order_tracking_id'], (string) $p4['merchant_reference'], 20000);
PesapalPayments::settle((string) $p4['order_tracking_id']);
check('switch off: the fee is paid but the account still waits for the administrator', Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$l4]) === 'paid' && Database::value('SELECT email_verified_at FROM academy_learners WHERE id = ?', [$l4]) === null && LearnerRegistry::approve($l4, null) === true);
Settings::reset('academy_fee_auto_approve');

// a short payment is never accepted
$reg2 = LearnerAuth::register(['name' => 'ZZ Fee Two', 'email' => 'zz-fee-two@example.test', 'password' => $pw, 'phone' => '+256700000012', 'country' => 'Uganda', 'whatsapp' => '+256700000012'], '10.9.9.2');
$l2 = (int) $reg2['learner']['id'];
AcademyFees::beginSignup($l2);
$p2 = (array) Database::one("SELECT * FROM pesapal_payments WHERE purpose = 'academy_signup' AND ref_id = ? ORDER BY id DESC LIMIT 1", [$l2]);
$answer((string) $p2['order_tracking_id'], (string) $p2['merchant_reference'], 5000);
check('a payment smaller than the fee is held for review and the fee stays due', PesapalPayments::settle((string) $p2['order_tracking_id'])['state'] === 'review' && Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$l2]) === 'due');
check('the administrator can waive the sign-up fee; then the account can be approved', AcademyFees::waiveSignup($l2) === true && Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$l2]) === 'waived' && LearnerRegistry::approve($l2, null) === true);
check('waiving twice does nothing', AcademyFees::waiveSignup($l2) === false);

Settings::set('academy_signup_fee', '0');
$reg3 = LearnerAuth::register(['name' => 'ZZ Fee Three', 'email' => 'zz-fee-three@example.test', 'password' => $pw, 'phone' => '+256700000013', 'country' => 'Uganda', 'whatsapp' => '+256700000013'], '10.9.9.3');
check('with the fee switched off a new learner is not charged', ($reg3['learner']['fee_status'] ?? '') === 'none');
check('learners from before the fee existed are never charged (status none)', Database::value("SELECT COUNT(*) FROM academy_learners WHERE fee_status <> 'none' AND email NOT LIKE 'zz-fee-%'", [], 0) === 0);

// ---- certificate fee ----------------------------------------------------------------------------------------------------------------------
$course = Database::one('SELECT * FROM academy_courses ORDER BY id LIMIT 1');
if ($course === null) {
    check('a course exists for the certificate tests', false, 'no course in the database');
} else {
    $learner = (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$lid]);
    $learner['name'] = 'ZZ Fee One';
    Settings::set('academy_cert_approval', '1');
    Settings::set('academy_cert_auto_release', '0');
    $cert = CertificateService::issue($learner, (array) $course, 80.0, 60);
    check('exam certificate with a fee: issued with the fee DUE and not available', $cert['fee_status'] === 'due' && $cert['approved_at'] === null && CertificateService::activeFor($lid, (int) $course['id']) === null);
    check('it shows in the waiting list of the learner', count(CertificateService::pendingForLearner($lid)) === 1);
    check('the administrator cannot approve it while the fee is unpaid (without waiving)', CertificateService::approve((int) $cert['id'], null, false)['ok'] === false && !empty(CertificateService::approve((int) $cert['id'], null, false)['fee']));
    check('another learner cannot pay for it', AcademyFees::beginCert((int) $cert['id'], $l2)['ok'] === false);
    $pay = AcademyFees::beginCert((int) $cert['id'], $lid);
    $cp = (array) Database::one("SELECT * FROM pesapal_payments WHERE purpose = 'academy_cert' AND ref_id = ? ORDER BY id DESC LIMIT 1", [(int) $cert['id']]);
    check('paying the certificate fee: Pesapal is asked for UGX 15,000', $pay['ok'] === true && (int) $cp['amount_minor'] === 1500000 && str_contains((string) ($mock['last']['description'] ?? ''), 'certificate fee'));
    $answer((string) $cp['order_tracking_id'], (string) $cp['merchant_reference'], 15000);
    $s = PesapalPayments::settle((string) $cp['order_tracking_id']);
    $after = (array) Database::one('SELECT fee_status, approved_at FROM academy_certificates WHERE id = ?', [(int) $cert['id']]);
    check('paid, approval required and the automatic release switched off: the certificate waits for the administrator', $s['state'] === 'paid' && $after['fee_status'] === 'paid' && $after['approved_at'] === null);
    check('the administrator approves it now and the learner can use it', CertificateService::approve((int) $cert['id'], null, false)['ok'] === true && CertificateService::activeFor($lid, (int) $course['id']) !== null);

    // approval required but the automatic release is on (the default): the confirmed payment releases the certificate at once
    Database::execute('DELETE FROM academy_certificates WHERE id = ?', [(int) $cert['id']]);
    Settings::reset('academy_cert_auto_release');
    $certA = CertificateService::issue($learner, (array) $course, 85.0, 60);
    AcademyFees::beginCert((int) $certA['id'], $lid);
    $cpA = (array) Database::one("SELECT * FROM pesapal_payments WHERE purpose = 'academy_cert' AND ref_id = ? ORDER BY id DESC LIMIT 1", [(int) $certA['id']]);
    $answer((string) $cpA['order_tracking_id'], (string) $cpA['merchant_reference'], 15000);
    PesapalPayments::settle((string) $cpA['order_tracking_id']);
    check('approval required, release on: paying the fee releases the certificate to the learner immediately', CertificateService::activeFor($lid, (int) $course['id']) !== null && Database::value('SELECT approved_at FROM academy_certificates WHERE id = ?', [(int) $certA['id']]) !== null);
    check('the automatic release is written to the audit log', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'academy.certificate.auto_released' AND entity_id = ?", [(string) $certA['id']], 0) === 1);

    // approval not required: payment makes it available at once
    Database::execute('DELETE FROM academy_certificates WHERE id = ?', [(int) $certA['id']]);
    Settings::set('academy_cert_approval', '0');
    $cert2 = CertificateService::issue($learner, (array) $course, 90.0, 60);
    AcademyFees::beginCert((int) $cert2['id'], $lid);
    $cp2 = (array) Database::one("SELECT * FROM pesapal_payments WHERE purpose = 'academy_cert' AND ref_id = ? ORDER BY id DESC LIMIT 1", [(int) $cert2['id']]);
    $answer((string) $cp2['order_tracking_id'], (string) $cp2['merchant_reference'], 15000);
    PesapalPayments::settle((string) $cp2['order_tracking_id']);
    check('approval not required: paying the fee makes the certificate available at once', CertificateService::activeFor($lid, (int) $course['id']) !== null);

    // waive
    Database::execute('DELETE FROM academy_certificates WHERE learner_id = ?', [$lid]);
    $cert3 = CertificateService::issue($learner, (array) $course, 70.0, 60);
    check('the administrator can waive the certificate fee and approve in one go', CertificateService::approve((int) $cert3['id'], null, false, true)['ok'] === true && Database::value('SELECT fee_status FROM academy_certificates WHERE id = ?', [(int) $cert3['id']]) === 'waived' && CertificateService::activeFor($lid, (int) $course['id']) !== null);

    // fee off: nothing is held
    Database::execute('DELETE FROM academy_certificates WHERE learner_id = ?', [$lid]);
    Settings::set('academy_cert_fee', '0');
    Settings::set('academy_cert_approval', '1');
    $cert4 = CertificateService::issue($learner, (array) $course, 70.0, 60);
    check('with the certificate fee switched off nothing is held for payment', $cert4['fee_status'] === 'none');
    $hand = CertificateService::issue(array_merge($learner, ['id' => $l2]), (array) $course, null, 0, true);
    check('a certificate an administrator issues by hand is never charged', $hand['fee_status'] === 'none' && $hand['approved_at'] !== null);
}

// ---- admin page ---------------------------------------------------------------------------------------------------------------------------
$base = 'http://localhost/' . basename(dirname(__DIR__));
$probe = @file_get_contents($base . '/academy');
if ($probe === false) {
    check('web server reachable for the admin checks', false, 'start Apache');
} else {
    $jar = tempnam(sys_get_temp_dir(), 'feej');
    $req = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
        $ch = curl_init($base . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 30, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        }
        $raw = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return ['code' => $code, 'body' => substr($raw, $hs)];
    };
    $tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
    $r = $req('POST', '/academy/fee/pay', []);
    check('the public "pay the sign-up fee" button needs the CSRF token (403)', $r['code'] === 403, (string) $r['code']);
    $apw = 'Fee admin pass 33';
    User::create(['name' => 'ZZ Fee Admin', 'email' => 'zz-fee-admin@example.test', 'password_hash' => Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
    Database::execute('DELETE FROM rate_limits');
    $page = $req('GET', '/admin/login');
    $req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-fee-admin@example.test', 'password' => $apw]);
    $r = $req('GET', '/admin/pesapal');
    check('admin: the Pesapal page has the "LAF Academy fees" card', $r['code'] === 200 && str_contains($r['body'], 'LAF Academy fees') && str_contains($r['body'], 'signup_amount') && str_contains($r['body'], 'cert_amount'), (string) $r['code']);
    $adm = ['_token' => $tok($r['body'])];
    $req('POST', '/admin/pesapal/fees', $adm + ['signup_amount' => '25,000', 'signup_currency' => 'UGX', 'cert_amount' => '10', 'cert_currency' => 'USD']);
    Settings::flush();
    check('admin: saving the fees stores amounts and currencies (25,000 UGX and 10 USD)', Settings::get('academy_signup_fee') === '25000' && Settings::get('academy_signup_fee_currency') === 'UGX' && Settings::get('academy_cert_fee') === '10' && Settings::get('academy_cert_fee_currency') === 'USD');
    $r = $req('GET', '/admin/pesapal');
    $req('POST', '/admin/pesapal/fees', ['_token' => $tok($r['body']), 'signup_amount' => 'twenty', 'signup_currency' => 'UGX', 'cert_amount' => '1', 'cert_currency' => 'USD']);
    Settings::flush();
    check('admin: a wrong amount is refused and nothing changes', Settings::get('academy_signup_fee') === '25000');
    $r = $req('GET', '/admin/pesapal');
    $req('POST', '/admin/pesapal/fees', ['_token' => $tok($r['body']), 'signup_amount' => '5', 'signup_currency' => 'XXX', 'cert_amount' => '1', 'cert_currency' => 'USD']);
    Settings::flush();
    check('admin: an unknown currency is refused', Settings::get('academy_signup_fee') === '25000');
    $lp = $req('GET', '/admin/academy/learners/' . $lid);
    check('admin: a learner page opens (200)', $lp['code'] === 200, (string) $lp['code']);
    @unlink($jar);
}

// ---- clean up -----------------------------------------------------------------------------------------------------------------------------
$clean();
foreach ($before as $k => $v) {
    if ($v === null) {
        Settings::reset($k);
    } else {
        Settings::set($k, (string) $v);
    }
}
PesapalClient::$transport = null;
PesapalClient::resetToken();
putenv($keys[0] === false ? 'PESAPAL_CONSUMER_KEY' : 'PESAPAL_CONSUMER_KEY=' . $keys[0]);
putenv($keys[1] === false ? 'PESAPAL_CONSUMER_SECRET' : 'PESAPAL_CONSUMER_SECRET=' . $keys[1]);
putenv($keys[2] === false ? 'PESAPAL_ENV' : 'PESAPAL_ENV=' . $keys[2]);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL OK\n" : "\n{$fail} FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
