<?php
declare(strict_types=1);

/**
 * Academy certificate payments through Pesapal (docs/PESAPAL.md): PesapalClient + CertificatePayments against the local mock bin/dev/mock-pesapal.php (this test starts
 * and stops it), the payment gate in front of the certificate, settlement rules (amount / currency / reference / duplicates), the return and notification endpoints,
 * and the public routes over real HTTP. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-payments.php
 * It never talks to the real Pesapal and never touches the real keys in .env (they are overridden in memory for this process). Removes its own rows (zz-pay-*).
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
use App\Core\Config;
use App\Core\Database;
use App\Core\Env;
use App\Core\Settings;
use App\Services\Academy\CertificatePayments;
use App\Services\Academy\CertificateService;
use App\Services\Academy\CourseService;
use App\Services\Payments\PesapalClient;

echo "Academy payments (Pesapal)\n";
$zzRoot = dirname(__DIR__);

$cleanup = static function (): void {
    Database::execute("DELETE FROM academy_payments WHERE learner_email LIKE 'zz-pay-%'");
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Pay%'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-pay-%'");
    Database::execute("DELETE FROM academy_courses WHERE slug LIKE 'zz-pay-%'");
    Database::execute('DELETE FROM rate_limits');
};
$cleanup();

// ---- environment: override the Pesapal variables in memory (Env::get prefers what .env loaded) and remember the settings we touch
$envRef = new ReflectionProperty(Env::class, 'vars');
$envRef->setAccessible(true);
$envBackup = $envRef->getValue();
$envSet = static function (string $k, ?string $v) use ($envRef): void {
    $a = $envRef->getValue();
    if ($v === null) {
        unset($a[$k]);
    } else {
        $a[$k] = $v;
    }
    $envRef->setValue(null, $a);
};
$settingBackup = [];
foreach (['academy_cert_fee', 'academy_cert_approval', 'pesapal_ipn_id', 'pesapal_ipn_scope'] as $k) {
    $settingBackup[$k] = Settings::isCustomised($k) ? (string) Settings::get($k) : null;
}
$restoreSettings = static function () use ($settingBackup): void {
    foreach ($settingBackup as $k => $v) {
        $v === null ? Settings::reset($k) : Settings::set($k, $v);
    }
    Settings::flush();
};

// ---- the mock
$srv = @stream_socket_server('tcp://127.0.0.1:0', $en, $es);
$mockPort = 0;
if ($srv !== false) {
    $mockPort = (int) substr((string) strrchr((string) stream_socket_get_name($srv, false), ':'), 1);
    fclose($srv);
}
$mockProc = null;
$mockPipes = [];
if ($mockPort !== 0) {
    $null = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
    $mockProc = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $mockPort, $zzRoot . '/bin/dev/mock-pesapal.php'], [0 => ['pipe', 'r'], 1 => ['file', $null, 'w'], 2 => ['file', $null, 'w']], $mockPipes);
    for ($i = 0; $i < 40; $i++) {
        $sock = @fsockopen('127.0.0.1', $mockPort, $en, $es, 0.2);
        if ($sock !== false) {
            fclose($sock);
            break;
        }
        usleep(150000);
    }
}
$mock = static function (string $path, array $data = [], string $method = 'POST') use (&$mockPort): array {
    $ch = curl_init('http://127.0.0.1:' . $mockPort . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_POSTFIELDS => $method === 'POST' ? json_encode($data) : null, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'json' => json_decode($body, true), 'body' => $body];
};
$mockUp = $mockPort !== 0 && @$mock('/__mock/state', [], 'GET')['code'] === 200;

if (!$mockUp) {
    echo "  info the mock server could not be started: the Pesapal checks were skipped\n";
} else {
    $mock('/__mock/reset');
    $envSet('PESAPAL_DEV_BASE_URL', 'http://127.0.0.1:' . $mockPort);
    $envSet('PESAPAL_CONSUMER_KEY', 'mock-key');
    $envSet('PESAPAL_CONSUMER_SECRET', 'mock-secret');
    $envSet('PESAPAL_ENV', 'live');
    $envSet('PESAPAL_IPN_ID', '');
    $envSet('PESAPAL_PUBLIC_URL', '');
    Settings::reset('pesapal_ipn_id');
    Settings::reset('pesapal_ipn_scope');
    Settings::set('academy_cert_fee', '15000');
    Settings::set('academy_cert_approval', '1');
    Settings::flush();

    // ================================================================================================ client
    check('client: the dev base URL is honoured on localhost while APP_ENV=local', PesapalClient::usingDevBase() && PesapalClient::baseUrl() === 'http://127.0.0.1:' . $mockPort);
    Config::set('app.env', 'production');
    $prodBase = PesapalClient::baseUrl();
    Config::set('app.env', 'local');
    check('client: the override is IGNORED outside APP_ENV=local (production talks to pay.pesapal.com over https)', $prodBase === 'https://pay.pesapal.com/v3', $prodBase);
    $envSet('PESAPAL_ENV', 'sandbox');
    Config::set('app.env', 'production');
    $sandboxBase = PesapalClient::baseUrl();
    Config::set('app.env', 'local');
    $envSet('PESAPAL_ENV', 'live');
    check('client: sandbox mode uses the Pesapal sandbox host', $sandboxBase === 'https://cybqa.pesapal.com/pesapalv3', $sandboxBase);
    $bad = 0;
    foreach (['http://evil.example', 'http://127.0.0.1.evil.example', 'http://user:pw@127.0.0.1:1', 'http://127.0.0.1:99/path', 'ftp://127.0.0.1', 'file:///etc/passwd', 'http://localhost.evil.com'] as $u) {
        $envSet('PESAPAL_DEV_BASE_URL', $u);
        $bad += PesapalClient::devBase() !== null ? 1 : 0;
    }
    $envSet('PESAPAL_DEV_BASE_URL', 'http://127.0.0.1:' . $mockPort);
    check('client: hostile dev base URLs are all refused (other hosts, userinfo, paths, other schemes)', $bad === 0);
    $t = PesapalClient::testConnection();
    check('client: "test connection" with valid keys is ok and says nothing secret', $t['ok'] && $t['state'] === 'ok' && !str_contains(json_encode($t), 'mock-secret'));
    Settings::flush();
    $mock('/__mock/config', ['secret' => 'rotated']);
    check('client: the token is cached for a few minutes, so a key rotated at Pesapal is noticed only by a fresh process (no needless calls)', PesapalClient::token()['ok'] === true);
    $mock('/__mock/config', ['secret' => 'mock-secret']);
    check('client: Pesapal ids are format-checked before they reach a URL', PesapalClient::validId('3fa85f64-5717-4562-b3fc-2c963f66afa6') && !PesapalClient::validId('../../etc') && !PesapalClient::validId('a b c d e f g h') && !PesapalClient::validId(''));
    $none = PesapalClient::status('not valid!');
    check('client: a malformed tracking id is refused without a call', $none['ok'] === false && ($none['error'] ?? '') === 'rejected');
    $ipn1 = PesapalClient::ensureIpnId('http://localhost/academy/pay/ipn');
    $ipn2 = PesapalClient::ensureIpnId('http://localhost/academy/pay/ipn');
    $state = $mock('/__mock/state', [], 'GET')['json'];
    check('client: the notification address is registered ONCE and remembered', $ipn1['ok'] && $ipn2['ok'] && $ipn1['ipn_id'] === $ipn2['ipn_id'] && count($state['ipns']) === 1);
    $ipn3 = PesapalClient::ensureIpnId('http://localhost/other/academy/pay/ipn');
    check('client: another address (another site / folder) gets its own registration', $ipn3['ok'] && $ipn3['ipn_id'] !== $ipn1['ipn_id']);
    Settings::reset('pesapal_ipn_id');
    Settings::reset('pesapal_ipn_scope');
    $ipn4 = PesapalClient::ensureIpnId('http://localhost/academy/pay/ipn');
    check('client: with the remembered id lost, the account list is searched before registering again', $ipn4['ok'] && $ipn4['ipn_id'] === $ipn1['ipn_id'] && count($mock('/__mock/state', [], 'GET')['json']['ipns']) === 2);
    Settings::reset('pesapal_ipn_id');
    Settings::reset('pesapal_ipn_scope');

    // ================================================================================================ fixtures
    $mkCourse = static function (string $tag, ?int $fee = null): array {
        $id = Database::insert('academy_courses', ['slug' => 'zz-pay-' . $tag, 'title' => 'ZZ Pay course ' . $tag, 'status' => 'published', 'certificate_enabled' => 1, 'certificate_fee' => $fee]);
        $mid = Database::insert('academy_modules', ['course_id' => $id, 'title' => 'Module', 'sort_order' => 1]);
        Database::insert('academy_lessons', ['course_id' => $id, 'module_id' => $mid, 'title' => 'Lesson', 'sort_order' => 1]);
        return (array) CourseService::find($id);
    };
    $mkLearner = static fn(string $tag): array => (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) Database::insert('academy_learners', [
        'name' => 'ZZ Pay ' . $tag, 'email' => 'zz-pay-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'), 'email_verified_at' => date('Y-m-d H:i:s'), 'is_active' => 1, 'phone' => '0771234567',
    ])]);
    /** enrol and finish every lesson (what the learner does before the certificate step) */
    $finish = static function (array $l, array $c): void {
        CourseService::enroll((int) $l['id'], (int) $c['id']);
        foreach (Database::all('SELECT * FROM academy_lessons WHERE course_id = ?', [(int) $c['id']]) as $lesson) {
            CourseService::completeLesson((int) $l['id'], $lesson);
        }
        CourseService::completion((int) $l['id'], (int) $c['id']);
    };
    $course = $mkCourse('main');
    $freeCourse = $mkCourse('free', 0);
    $ownFee = $mkCourse('own', 5000);
    $outcome = static fn(string $tracking, array $o) => $mock('/__mock/outcome', ['tracking' => $tracking] + $o);
    $certCount = static fn(array $l): int => (int) Database::value('SELECT COUNT(*) FROM academy_certificates WHERE learner_id = ?', [(int) $l['id']], 0);

    // ================================================================================================ fee rules
    check('fee: the site-wide fee applies to a course without its own', CertificatePayments::fee($course) === 15000);
    check('fee: a course can have its own fee, and 0 makes it free', CertificatePayments::fee($ownFee) === 5000 && CertificatePayments::fee($freeCourse) === 0);
    $a = $mkLearner('a');
    $finish($a, $course);
    check('fee: required for a learner who has not paid, not for a free course', CertificatePayments::required((int) $a['id'], $course) && !CertificatePayments::required((int) $a['id'], $freeCourse));
    check('fee: the payments table is there', CertificatePayments::ready());

    // ================================================================================================ the gate
    $req = CertificateService::request($a, $course);
    check('gate: asking for the certificate without paying is refused and nothing is filed', $req['ok'] === false && !empty($req['payment_required']) && $certCount($a) === 0);
    Settings::set('academy_cert_approval', '0');
    Settings::flush();
    $cc = CourseService::checkCompletion((int) $a['id'], (int) $course['id']);
    check('gate: with approval switched OFF the certificate is still not issued before the fee (no bypass)', is_array($cc) && !empty($cc['payment_required']) && $certCount($a) === 0);
    Settings::set('academy_cert_approval', '1');
    Settings::flush();
    $cc = CourseService::checkCompletion((int) $a['id'], (int) $course['id']);
    check('gate: with approval ON nothing is filed either', is_array($cc) && !empty($cc['payment_required']) && $certCount($a) === 0);
    $lF = $mkLearner('free');
    $finish($lF, $freeCourse);
    $reqF = CertificateService::request($lF, $freeCourse);
    check('gate: a free course (fee 0) goes straight to the normal certificate flow', $reqF['ok'] === true && $certCount($lF) === 1);
    $notDone = $mkLearner('notdone');
    CourseService::enroll((int) $notDone['id'], (int) $course['id']);
    check('begin: a learner who has not finished the course cannot start a payment', CertificatePayments::begin($notDone, $course)['error'] === 'not_eligible');
    check('begin: a free course has nothing to pay', CertificatePayments::begin($lF, $freeCourse)['error'] === 'not_required');

    // ================================================================================================ begin
    $b = CertificatePayments::begin($a, $course);
    $row = $b['ok'] ? $b['payment'] : [];
    $order = $b['ok'] ? ($mock('/__mock/state', [], 'GET')['json']['orders'][$row['tracking_id']] ?? []) : [];
    check('begin: a pending row is created with the fee from OUR settings, in UGX, with Pesapal\'s tracking id', $b['ok'] && $row['status'] === 'pending' && (float) $row['amount'] === 15000.0 && $row['currency'] === 'UGX' && PesapalClient::validId((string) $row['tracking_id']) && CertificatePayments::validRef((string) $row['merchant_ref']));
    check('begin: Pesapal got our reference, the amount and a callback address tied to this reference', $order !== [] && $order['merchant_reference'] === $row['merchant_ref'] && (float) $order['amount'] === 15000.0 && str_contains($order['callback_url'], '/academy/pay/return/' . CertificatePayments::returnKey((string) $row['merchant_ref'])));
    check('begin: the learner is sent to the Pesapal page (the mock), nowhere else', $b['ok'] && str_starts_with((string) $b['link'], 'http://127.0.0.1:' . $mockPort . '/pay/'));
    check('begin: the row keeps a snapshot of the learner and the course', $row['learner_email'] === $a['email'] && $row['course_title'] === $course['title']);
    check('begin: the certificate is still locked while the payment is only pending', CertificatePayments::required((int) $a['id'], $course) && $certCount($a) === 0);

    // ================================================================================================ coming back
    $ref = (string) $row['merchant_ref'];
    $key = CertificatePayments::returnKey($ref);
    $trk = (string) $row['tracking_id'];
    check('return: no key / wrong key / bad reference is "unbound" and changes nothing', CertificatePayments::handleReturn(['OrderMerchantReference' => $ref], '')['state'] === 'unbound'
        && CertificatePayments::handleReturn(['OrderMerchantReference' => $ref], str_repeat('a', 32))['state'] === 'unbound'
        && CertificatePayments::handleReturn(['OrderMerchantReference' => 'LAF-NOTHEX'], $key)['state'] === 'unbound' && CertificatePayments::find((int) $row['id'])['status'] === 'pending');
    check('return: a key for ANOTHER reference does not unlock this one', CertificatePayments::handleReturn(['OrderMerchantReference' => $ref], CertificatePayments::returnKey('LAF-000000000000'))['state'] === 'unbound');
    $unknownRef = 'LAF-' . strtoupper(bin2hex(random_bytes(6)));
    check('return: a well-formed but unknown reference is "unknown"', CertificatePayments::handleReturn(['OrderMerchantReference' => $unknownRef], CertificatePayments::returnKey($unknownRef))['state'] === 'unknown');
    $r1 = CertificatePayments::handleReturn(['OrderMerchantReference' => $ref, 'OrderTrackingId' => $trk, 'status' => 'successful', 'amount' => '15000'], $key);
    check('return: the learner is back but Pesapal has not confirmed yet: pending (a claim in the query changes nothing)', $r1['state'] === 'pending' && CertificatePayments::required((int) $a['id'], $course) && $certCount($a) === 0);
    // pay on the (mock) Pesapal page by hand: the confirmation lives at Pesapal
    $outcome($trk, ['status' => 'completed']);
    $mock('/__mock/config', ['fail' => '500']);
    $rDown = CertificatePayments::handleReturn(['OrderMerchantReference' => $ref], $key);
    $mock('/__mock/config', ['fail' => '']);
    check('return: when Pesapal cannot be asked, nothing is decided (verify_failed), the learner is not told "paid"', $rDown['state'] === 'verify_failed' && CertificatePayments::required((int) $a['id'], $course));
    $r2 = CertificatePayments::handleReturn(['OrderMerchantReference' => $ref], $key);
    $paidRow = (array) CertificatePayments::find((int) $row['id']);
    check('return: confirmed by Pesapal: the row is paid, with the method and the confirmation code', $r2['state'] === 'paid' && $paidRow['status'] === 'paid' && $paidRow['method'] === 'MtnUganda' && str_starts_with((string) $paidRow['confirmation_code'], 'MOCK') && $paidRow['paid_at'] !== null);
    check('return: paying files the certificate request (it waits for the Academy team: approval is on)', $certCount($a) === 1 && CertificateService::pendingFor((int) $a['id'], (int) $course['id']) !== null);
    check('return: the learner no longer has to pay, and a receipt was e-mailed once', !CertificatePayments::required((int) $a['id'], $course) && $paidRow['receipt_emailed_at'] !== null);
    CertificatePayments::handleReturn(['OrderMerchantReference' => $ref], $key);
    CertificatePayments::handleIpn(['OrderMerchantReference' => $ref, 'OrderTrackingId' => $trk]);
    check('return/ipn again: idempotent (still one paid row, one certificate)', (int) Database::value("SELECT COUNT(*) FROM academy_payments WHERE learner_id = ? AND status = 'paid'", [(int) $a['id']], 0) === 1 && $certCount($a) === 1);
    check('begin: a learner who already paid cannot pay twice', CertificatePayments::begin($a, $course)['error'] === 'already_paid');

    // ================================================================================================ notification (IPN)
    $ipnRes = $mock('/__mock/ipn', ['tracking' => $trk, 'url' => 'http://localhost/' . basename($zzRoot) . '/academy/pay/ipn']);        // the CLI does not know the sub-folder: say where the registered address really is
    $ipnBody = json_decode((string) ($ipnRes['json']['body'] ?? ''), true);
    check('ipn: over real HTTP the site answers 200 with the JSON Pesapal expects (and the mock reached the registered address)', ($ipnRes['json']['http'] ?? 0) === 200 && is_array($ipnBody) && ($ipnBody['status'] ?? 0) === 200 && ($ipnBody['orderMerchantReference'] ?? '') === $ref,
        (string) ($ipnRes['body'] ?? ''));
    $unk = CertificatePayments::handleIpn(['OrderMerchantReference' => $unknownRef, 'OrderTrackingId' => '3fa85f64-5717-4562-b3fc-2c963f66afa6']);
    check('ipn: an unknown reference is acknowledged and ignored (200, nothing created)', $unk['http'] === 200 && Database::value('SELECT COUNT(*) FROM academy_payments WHERE merchant_ref = ?', [$unknownRef], 0) === 0);
    $junk = CertificatePayments::handleIpn(['OrderMerchantReference' => ['x'], 'OrderTrackingId' => str_repeat('z', 500), 'OrderNotificationType' => "<script>"]);
    check('ipn: arrays and junk in the query never break it and are never echoed', $junk['http'] === 200 && $junk['body']['orderMerchantReference'] === '' && $junk['body']['orderTrackingId'] === '' && $junk['body']['orderNotificationType'] === 'IPNCHANGE');

    // ================================================================================================ mismatches go to review, never a silent pass
    $startFor = static function (string $tag) use ($mkLearner, $finish, $course, &$mock): array {
        $l = $mkLearner($tag);
        $finish($l, $course);
        $b = CertificatePayments::begin($l, $course);
        return [$l, $b['payment'] ?? [], $b];
    };
    [$lU, $pU] = $startFor('under');
    $outcome((string) $pU['tracking_id'], ['status' => 'completed', 'amount' => 100]);
    $resU = CertificatePayments::handleIpn(['OrderMerchantReference' => $pU['merchant_ref'], 'OrderTrackingId' => $pU['tracking_id']]);
    $rowU = (array) CertificatePayments::find((int) $pU['id']);
    check('settle: Pesapal says 100 was paid but 15000 was due: needs review, the certificate stays locked', $resU['http'] === 200 && $rowU['status'] === 'review' && str_contains((string) $rowU['note'], 'Amount mismatch') && CertificatePayments::required((int) $lU['id'], $course) && $certCount($lU) === 0);
    [$lC, $pC] = $startFor('currency');
    $outcome((string) $pC['tracking_id'], ['status' => 'completed', 'currency' => 'USD']);
    CertificatePayments::handleIpn(['OrderMerchantReference' => $pC['merchant_ref'], 'OrderTrackingId' => $pC['tracking_id']]);
    check('settle: a different currency needs review', CertificatePayments::find((int) $pC['id'])['status'] === 'review' && CertificatePayments::required((int) $lC['id'], $course));
    [$lR, $pR] = $startFor('reference');
    $outcome((string) $pR['tracking_id'], ['status' => 'completed', 'merchant_reference' => 'LAF-SOMEONEELSE']);
    CertificatePayments::handleIpn(['OrderMerchantReference' => $pR['merchant_ref'], 'OrderTrackingId' => $pR['tracking_id']]);
    check('settle: Pesapal reporting another reference than ours needs review', CertificatePayments::find((int) $pR['id'])['status'] === 'review' && CertificatePayments::required((int) $lR['id'], $course));
    [$lO, $pO] = $startFor('over');
    $outcome((string) $pO['tracking_id'], ['status' => 'completed', 'amount' => 20000]);
    CertificatePayments::handleIpn(['OrderMerchantReference' => $pO['merchant_ref'], 'OrderTrackingId' => $pO['tracking_id']]);
    check('settle: paying MORE than the fee is fine', CertificatePayments::find((int) $pO['id'])['status'] === 'paid' && !CertificatePayments::required((int) $lO['id'], $course));
    // an administrator looks at the amount mismatch and accepts it
    check('review: an administrator can accept a payment that needed review: it counts and the request is filed', CertificatePayments::accept((int) $pU['id'], null) && !CertificatePayments::required((int) $lU['id'], $course) && $certCount($lU) === 1);
    check('review: only a payment that needs review can be accepted', !CertificatePayments::accept((int) $pU['id'], null) && !CertificatePayments::accept((int) $row['id'], null));
    // a second payment for a course that is already paid
    $dup = CertificatePayments::begin($a, $course);
    check('duplicate: a second order cannot even be opened for a paid course', $dup['ok'] === false && $dup['error'] === 'already_paid');
    $lD = $mkLearner('dup');
    $finish($lD, $course);
    $d1 = CertificatePayments::begin($lD, $course)['payment'];
    Database::execute("UPDATE academy_payments SET status = 'failed' WHERE id = ?", [(int) $d1['id']]);        // the learner tried again after a "failure"...
    $d2 = CertificatePayments::begin($lD, $course)['payment'];
    $outcome((string) $d2['tracking_id'], ['status' => 'completed']);
    $outcome((string) $d1['tracking_id'], ['status' => 'completed']);                                           // ...but the first try was in fact paid too
    CertificatePayments::handleIpn(['OrderMerchantReference' => $d2['merchant_ref'], 'OrderTrackingId' => $d2['tracking_id']]);
    CertificatePayments::settle((array) CertificatePayments::find((int) $d1['id']), PesapalClient::status((string) $d1['tracking_id']), 'test');
    $dRows = Database::all('SELECT status, note FROM academy_payments WHERE learner_id = ? ORDER BY id', [(int) $lD['id']]);
    check('duplicate: the second money-in for the same course is put in review (to refund), not counted twice', $dRows[0]['status'] === 'review' && str_contains((string) $dRows[0]['note'], 'Duplicate') && $dRows[1]['status'] === 'paid' && $certCount($lD) === 1);

    // ================================================================================================ failures, abandonment, outages
    [$lF2, $pF] = $startFor('failed');
    $outcome((string) $pF['tracking_id'], ['status' => 'failed']);
    $rf = CertificatePayments::handleReturn(['OrderMerchantReference' => $pF['merchant_ref']], CertificatePayments::returnKey((string) $pF['merchant_ref']));
    check('failed: a declined payment is "failed", the certificate stays locked, and the learner can try again', $rf['state'] === 'failed' && CertificatePayments::required((int) $lF2['id'], $course) && (CertificatePayments::begin($lF2, $course)['ok'] ?? false));
    [$lS, $pS] = $startFor('stale');
    Database::execute('UPDATE academy_payments SET created_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 3 * 86400), (int) $pS['id']]);
    check('stale: a payment Pesapal never confirmed is closed after two days', CertificatePayments::recheck((array) CertificatePayments::find((int) $pS['id']), 'test') === 'failed');
    [$lP, $pP] = $startFor('pendingip');
    $mock('/__mock/config', ['fail' => '500']);
    $down = CertificatePayments::handleIpn(['OrderMerchantReference' => $pP['merchant_ref'], 'OrderTrackingId' => $pP['tracking_id']]);
    $mock('/__mock/config', ['fail' => 'bad_json']);
    $junkAnswer = CertificatePayments::recheck((array) CertificatePayments::find((int) $pP['id']), 'test');
    $mock('/__mock/config', ['fail' => '']);
    check('outage: when Pesapal cannot be asked the notification answers 500 (Pesapal retries) and a garbage answer decides nothing', $down['http'] === 500 && $junkAnswer === 'verify_failed' && CertificatePayments::find((int) $pP['id'])['status'] === 'pending');
    $swept = CertificatePayments::recheckPending(50);
    check('cron: the sweep re-asks Pesapal about pending payments', $swept >= 1);
    // the query string never chooses the order that is asked about
    [$lQ, $pQ] = $startFor('query');
    $rq = CertificatePayments::handleReturn(['OrderMerchantReference' => $pQ['merchant_ref'], 'OrderTrackingId' => $trk], CertificatePayments::returnKey((string) $pQ['merchant_ref']));
    check('return: a tracking id of somebody else\'s PAID order in the query does not pay this row', $rq['state'] === 'pending' && CertificatePayments::required((int) $lQ['id'], $course));
    // waive
    $lW = $mkLearner('waive');
    $finish($lW, $course);
    CertificatePayments::waive($lW, $course, 'Scholarship', null);
    $reqW = CertificateService::request($lW, $course);
    check('waive: an administrator can excuse the fee; the certificate flow then works', $reqW['ok'] === true && $certCount($lW) === 1 && !CertificatePayments::required((int) $lW['id'], $course));
    // approval OFF: paying issues the certificate at once
    Settings::set('academy_cert_approval', '0');
    Settings::flush();
    $lN = $mkLearner('noappr');
    $finish($lN, $course);
    $pN = CertificatePayments::begin($lN, $course)['payment'];
    $outcome((string) $pN['tracking_id'], ['status' => 'completed']);
    CertificatePayments::handleIpn(['OrderMerchantReference' => $pN['merchant_ref'], 'OrderTrackingId' => $pN['tracking_id']]);
    $cN = CertificateService::activeFor((int) $lN['id'], (int) $course['id']);
    check('approval off: once paid, the certificate is issued and available at once', $cN !== null && CertificateService::isApproved($cN));
    Settings::set('academy_cert_approval', '1');
    Settings::flush();
    // no keys: fail closed
    $envSet('PESAPAL_CONSUMER_KEY', '');
    $lK = $mkLearner('nokey');
    $finish($lK, $course);
    $nk = CertificatePayments::begin($lK, $course);
    check('no keys: payment is unavailable AND the certificate stays locked (fail closed)', $nk['ok'] === false && $nk['error'] === 'no_key' && CertificatePayments::required((int) $lK['id'], $course) && !CertificatePayments::available());
    $envSet('PESAPAL_CONSUMER_KEY', 'mock-key');
    check('safety: no secret appears in the payment rows or the mock\'s record of what we sent', !str_contains(json_encode(Database::all("SELECT * FROM academy_payments WHERE learner_email LIKE 'zz-pay-%'")), 'mock-secret'));
}

// ================================================================================================ routes over real HTTP (no Pesapal needed)
$base = 'http://localhost/' . basename($zzRoot) . '/';
$http = static function (string $method, string $path) use ($base): array {
    $ch = curl_init($base . ltrim($path, '/'));
    curl_setopt_array($ch, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 3]);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => $raw === false ? '' : substr((string) $raw, 0, $hs), 'body' => $raw === false ? '' : substr((string) $raw, $hs)];
};
if ($http('GET', 'academy')['code'] !== 200) {
    echo "  info the site does not answer on {$base}: the HTTP checks were skipped\n";
} else {
    $co = $http('GET', 'academy/courses/zz-nothing/checkout');
    check('http: the checkout page needs a signed-in learner (302 to the sign-in page)', $co['code'] === 302 && str_contains(strtolower($co['head']), 'academy/login'), (string) $co['code']);
    $ref = 'LAF-' . strtoupper(bin2hex(random_bytes(6)));
    $r1 = $http('GET', 'academy/pay/return/' . str_repeat('a', 32) . '?OrderMerchantReference=' . $ref . '&OrderTrackingId=3fa85f64-5717-4562-b3fc-2c963f66afa6');
    $r2 = $http('GET', 'academy/pay/return/' . str_repeat('b', 32) . '?OrderMerchantReference=LAF-111111111111');
    $r3 = $http('GET', 'academy/pay/return');
    $norm = static fn(string $h): string => (string) preg_replace(['/(name="(?:_token|_hp_ts)" value=")[^"]*/', '/s\.\d+\.[a-f0-9]{20,}/', '/(data-clock-hms>)[^<]*/', '/data-epoch="\d+"/', '/(data-clock-date>)[^<]*/', '/(<time class="clock__time" datetime=")[^"]*/'], ['$1', 'T', '$1', 'data-epoch="E"', '$1', '$1'], $h);
    check('http: the return page without the right key is the same neutral 200 for a real-looking and an invented reference (no redirect, no cache)', $r1['code'] === 200 && $r2['code'] === 200 && $r3['code'] === 200
        && !str_contains(strtolower($r1['head']), 'location:') && str_contains(strtolower($r1['head']), 'no-store') && $norm($r1['body']) === $norm($r2['body']), $r1['code'] . '/' . $r2['code'] . '/' . $r3['code']);
    $i1 = $http('GET', 'academy/pay/ipn');
    $j = json_decode($i1['body'], true);
    check('http: the notification address answers 200 with Pesapal\'s JSON even with nothing in the query (GET needs no CSRF token)', $i1['code'] === 200 && is_array($j) && ($j['status'] ?? 0) === 200 && str_contains(strtolower($i1['head']), 'application/json'));
    $i2 = $http('POST', 'academy/pay/ipn?OrderMerchantReference=' . $ref . '&OrderTrackingId=3fa85f64-5717-4562-b3fc-2c963f66afa6');
    check('http: a POST notification is accepted too (CSRF-exempt, authenticated by asking Pesapal)', $i2['code'] === 200);
    $ad = $http('GET', 'admin/academy/payments');
    check('http: the admin payments page needs a sign-in', $ad['code'] === 302 && str_contains(strtolower($ad['head']), 'admin/login'), (string) $ad['code']);
    $adp = $http('POST', 'admin/academy/payments/waive');
    check('http: an unauthenticated waive is refused (redirect or 4xx, never 200)', $adp['code'] !== 200, (string) $adp['code']);
}

// ================================================================================================ clean up
$restoreSettings();
$envRef->setValue(null, $envBackup);
$cleanup();
if (isset($mockProc) && is_resource($mockProc)) {
    foreach ($mockPipes as $p) {
        if (is_resource($p)) {
            fclose($p);
        }
    }
    proc_terminate($mockProc);
    proc_close($mockProc);
    @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lumina-mock-pesapal-' . $mockPort . '.json');
    @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lumina-mock-pesapal-' . $mockPort . '.json.lock');
}
check('clean-up: no test learner, course or payment is left behind', (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-pay-%'", [], 0) === 0 && (int) Database::value("SELECT COUNT(*) FROM academy_payments WHERE learner_email LIKE 'zz-pay-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
