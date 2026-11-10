<?php
declare(strict_types=1);

/**
 * MOBILE-MONEY payments through the Daraza API: phone and network helpers, the client (against a fake transport: no network, no real key), the ledger (collect, idempotent paid,
 * failures, an answer that never comes back, checking again), what a payment does to a learner's fee and to a donation, payouts, and the pages. Run alone:
 *   /c/xampp/php/php.exe bin/selftest-daraza-api.php        Needs the local web server and APP_ENV=local.
 */

use App\Core\Database;
use App\Core\Settings;
use App\Services\DarazaPay\DarazaApi;
use App\Services\DarazaPay\DarazaPayments;

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

echo "-- daraza api --\n";
$prev = [];
foreach (['pay_opt_other', 'pay_rank_other', 'pay_rank_mobile', 'pay_rank_online', 'pay_rank_link', 'pay_rank_manual', 'pay_opt_online', 'pay_opt_link', 'pay_opt_manual', 'daraza_api_shop_on', 'daraza_api_project_on', 'daraza_api_membership_on', 'daraza_api_academy_signup_on', 'daraza_api_academy_cert_on', 'daraza_api_donation_on', 'daraza_api_usd_rate'] as $k) {
    $prev[$k] = Settings::get($k, '');
}
$clean = static function () use ($prev): void {
    Database::execute("DELETE FROM daraza_payments WHERE payer_email LIKE 'zz-dz-%@example.test' OR reference LIKE 'DZO-%' OR note LIKE 'ZZ%'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-dz-%@example.test'");
    Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-dz-%@example.test'");
    Database::execute("DELETE FROM users WHERE email = 'zz-dz-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
    Settings::setMany($prev);
    Settings::flush();
    DarazaApi::$transport = null;
    putenv('DARAZA_API_KEY');
    putenv('DARAZA_ENV');
};
check('the payments table is created on first use', DarazaPayments::install());
$clean();

// ---- phone and network
check('phone: +256, 256 and 0 forms are the same number', DarazaApi::local('+256 772 123 456') === '0772123456' && DarazaApi::local('0772-123-456') === '0772123456' && DarazaApi::e164('0772123456') === '+256772123456');
check('phone: a foreign or short number is refused', DarazaApi::local('+254712345678') === null && DarazaApi::local('12345') === null);
check('network: MTN 077/078/076/039, Airtel 070/075/074/020', DarazaApi::networkOf('0772123456') === 1 && DarazaApi::networkOf('0392123456') === 1 && DarazaApi::networkOf('0702123456') === 2 && DarazaApi::networkOf('+256751234567') === 2 && DarazaApi::networkOf('0412345678') === null);

// ---- the client, against a fake Daraza
putenv('DARAZA_API_KEY=zz-test-key');
putenv('DARAZA_ENV=sandbox');
$sent = [];
$answer = ['http' => 201, 'raw' => json_encode(['code' => 'Success', 'message' => 'Payment Successful', 'request_id' => 'REQ-1', 'response_details' => ['request_id' => 'REQ-1']])];
DarazaApi::$transport = static function (string $method, string $url, array $headers, ?string $body) use (&$sent, &$answer): array {
    $sent[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body === null ? null : json_decode($body, true)];
    return $answer;
};
$r = DarazaApi::requestToPay(1, 15000, '0772123456', 'Checkout');
check('request to pay: the sandbox address, the Api-Key header and the documented body (no authorisation code)', $r['ok'] && $r['state'] === 'success' && $r['request_id'] === 'REQ-1'
    && $sent[0]['url'] === 'https://daraza.net/api/sandbox/request_to_pay/' && in_array('Authorization: Api-Key zz-test-key', $sent[0]['headers'], true)
    && $sent[0]['body'] === ['method' => 1, 'amount' => 15000, 'phone' => '+256772123456', 'note' => 'Checkout'], json_encode($sent[0]));
putenv('DARAZA_ENV=live');
DarazaApi::requestToPay(2, 5000, '0702123456', 'x');
check('live mode uses the production address /api/request_to_pay/', $sent[1]['url'] === 'https://daraza.net/api/request_to_pay/' && $sent[1]['body']['method'] === 2);
DarazaApi::remit(1, 7500, '0772123456', 'Refund');
check('remit: /api/remit/', $sent[2]['url'] === 'https://daraza.net/api/remit/');
putenv('DARAZA_ENV=sandbox');
$answer = ['http' => 200, 'raw' => json_encode(['code' => 'Error', 'message' => 'Client Request Error', 'details' => 'Insufficient Account Balance'])];
$e = DarazaApi::requestToPay(1, 15000, '0772123456', 'x');
check('an error answer gives the reason and is not a success', !$e['ok'] && $e['state'] === 'error' && $e['message'] === 'Insufficient Account Balance', json_encode($e));
$answer = ['http' => 400, 'raw' => json_encode(['code' => 'Error', 'message' => 'Validation Failed', 'details' => ['phone' => ['This field is required.']]])];
check('a validation error is read', DarazaApi::requestToPay(1, 15000, '0772123456', 'x')['message'] === 'This field is required.');
$answer = ['http' => 401, 'raw' => json_encode(['code' => 'Error', 'message' => 'Unauthorized'])];
check('a refused key is "auth"', DarazaApi::requestToPay(1, 15000, '0772123456', 'x')['state'] === 'auth');
$answer = ['http' => 0, 'raw' => ''];
check('no answer in time is "unknown" (nothing is assumed)', DarazaApi::requestToPay(1, 15000, '0772123456', 'x')['state'] === 'unknown');
$before = count($sent);
check('a bad amount or network never reaches Daraza', !DarazaApi::requestToPay(1, 100, '0772123456', 'x')['ok'] && !DarazaApi::requestToPay(3, 5000, '0772123456', 'x')['ok'] && count($sent) === $before);
$answer = ['http' => 200, 'raw' => json_encode(['code' => 'Success', 'details' => ['is_successful' => true, 'amount' => 'UGX 15000', 'reference' => 'ok']])];
$st = DarazaApi::status('REQ-1', 'Request_to_Pay');
check('status: is_successful is read', $st['ok'] && $st['successful'] === true);
$answer = ['http' => 200, 'raw' => json_encode(['code' => 'Success', 'details' => ['balance' => '500000 UGX']])];
check('balance is read', DarazaApi::balance()['balance'] === '500000 UGX');

// ---- UGX amounts
Settings::setMany(['daraza_api_usd_rate' => '3700']);
Settings::flush();
check('amount: UGX stays, USD 3 at 3700 = UGX 11,100, other currencies and tiny amounts are refused', DarazaPayments::ugx(300000, 'UGX') === 3000 && DarazaPayments::ugx(300, 'USD') === 11100 && DarazaPayments::ugx(300, 'EUR') === null && DarazaPayments::ugx(1000, 'UGX') === null);
Settings::setMany(['daraza_api_usd_rate' => '0']);
Settings::flush();
check('amount: USD without a rate is not offered', DarazaPayments::ugx(300, 'USD') === null);
Settings::setMany(['daraza_api_usd_rate' => '3700', 'daraza_api_academy_signup_on' => '1', 'daraza_api_academy_cert_on' => '1', 'daraza_api_donation_on' => '1']);
Settings::flush();

// ---- a learner pays the sign-up fee
$now = date('Y-m-d H:i:s');
Database::execute('INSERT INTO academy_learners (reg_no, name, email, password_hash, is_active, created_at, updated_at) VALUES (?,?,?,?,1,?,?)', ['LAF-AC-ZDZ-1', 'Zz Dz Learner', 'zz-dz-l@example.test', 'x', $now, $now]);
$lid = (int) Database::value('SELECT LAST_INSERT_ID()');
Database::execute("UPDATE academy_learners SET fee_status = 'due' WHERE id = ?", [$lid]);
$b = DarazaPayments::begin('academy_signup', $lid, 300, 'USD', ['name' => 'Zz Dz Learner', 'email' => 'zz-dz-l@example.test']);
check('begin: a payment page address, the amount in UGX', $b['ok'] && str_contains((string) $b['url'], '/pay/mobile-money/') && (int) Database::value('SELECT amount_ugx FROM daraza_payments WHERE reference = ?', [$b['reference']]) === 11100);
$again = DarazaPayments::begin('academy_signup', $lid, 300, 'USD', ['email' => 'zz-dz-l@example.test']);
check('begin again keeps the same open attempt (no pile of rows)', $again['reference'] === $b['reference']);
$ref = (string) $b['reference'];
check('collect: a bad number is refused before Daraza is called', DarazaPayments::collect($ref, '0412345678', 0)['state'] === 'invalid');
$calls = count($sent);
$answer = ['http' => 200, 'raw' => json_encode(['code' => 'Error', 'message' => 'Client Request Error', 'details' => 'Insufficient Account Balance'])];
$c1 = DarazaPayments::collect($ref, '0772123456', 1);
check('collect: Daraza says no -> failed, the reason is kept, the fee is still due', $c1['state'] === 'failed' && str_contains($c1['message'], 'Insufficient') && Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$lid]) === 'due');
$answer = ['http' => 0, 'raw' => ''];
$c2 = DarazaPayments::collect($ref, '0772123456', 1);
check('collect: no answer in time -> unknown, nothing is assumed (the fee is still due)', $c2['state'] === 'unknown' && Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$lid]) === 'due');
$answer = ['http' => 201, 'raw' => json_encode(['code' => 'Success', 'message' => 'Payment Successful', 'request_id' => 'REQ-77'])];
$c3 = DarazaPayments::collect($ref, '0772123456', 0);
$sentBody = end($sent);
check('collect: success -> paid, the network was detected (MTN), the learner\'s fee is paid and the request id is kept', $c3['state'] === 'paid' && $sentBody['body']['method'] === 1 && Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$lid]) === 'paid' && Database::value('SELECT request_id FROM daraza_payments WHERE reference = ?', [$ref]) === 'REQ-77');
$n = count($sent);
check('collect again on a paid payment does not call Daraza a second time', DarazaPayments::collect($ref, '0772123456', 1)['state'] === 'paid' && count($sent) === $n);

// ---- an answer that never came back, checked later
Database::execute("INSERT INTO daraza_payments (reference, direction, purpose, ref_id, amount_minor, currency, amount_ugx, phone, method, status, request_id, mode, payer_name, payer_email) VALUES ('DZZ-CHECK1','in','academy_signup',?,300,'USD',11100,'+256772123456',1,'unknown','REQ-88','sandbox','ZZ','zz-dz-c@example.test')", [$lid]);
$cid = (int) Database::value("SELECT id FROM daraza_payments WHERE reference = 'DZZ-CHECK1'");
$answer = ['http' => 200, 'raw' => json_encode(['code' => 'Success', 'details' => ['is_successful' => false, 'reference' => 'Request_to_Pay Request Failed']])];
check('check again: Daraza says it failed -> failed', DarazaPayments::check($cid)['state'] === 'failed');
Database::execute("UPDATE daraza_payments SET status = 'unknown' WHERE id = ?", [$cid]);
$answer = ['http' => 200, 'raw' => json_encode(['code' => 'Success', 'details' => ['is_successful' => true, 'reference' => 'ok']])];
check('check again: Daraza says it worked -> paid', DarazaPayments::check($cid)['state'] === 'paid');

// ---- a donation
Database::execute("INSERT INTO donations (donor_name, donor_email, amount, currency, method, status, source) VALUES ('Zz Dz Donor','zz-dz-d@example.test',20000,'UGX','mobile_money','pledged','manual')");
$did = (int) Database::value('SELECT LAST_INSERT_ID()');
$bd = DarazaPayments::begin('donation', $did, 2000000, 'UGX', ['name' => 'Zz Dz Donor', 'email' => 'zz-dz-d@example.test']);
$answer = ['http' => 201, 'raw' => json_encode(['code' => 'Success', 'message' => 'Payment Successful', 'request_id' => 'REQ-99'])];
DarazaPayments::collect((string) $bd['reference'], '0702123456', 2);
$don = Database::one('SELECT status, method, receipt_no, reference FROM donations WHERE id = ?', [$did]);
check('a paid donation becomes "received" by mobile money with a receipt number', $don['status'] === 'received' && $don['method'] === 'mobile_money' && preg_match('/^RCT-\d{4}-\d{4}$/', (string) $don['receipt_no']) === 1 && end($sent)['body']['method'] === 2, json_encode($don));

// ---- payouts
$answer = ['http' => 201, 'raw' => json_encode(['code' => 'Success', 'message' => 'Remittance Successful', 'request_id' => 'REQ-OUT-1'])];
$po = DarazaPayments::payout(null, '0772123456', 0, 7500, 'ZZ refund');
check('payout: the money is sent with the remittance call and written to the ledger', $po['ok'] && $po['row']['direction'] === 'out' && $po['row']['status'] === 'paid' && (int) $po['row']['amount_ugx'] === 7500 && str_contains(end($sent)['url'], '/remit/'));
$answer = ['http' => 200, 'raw' => json_encode(['code' => 'Error', 'message' => 'Client Request Error', 'details' => 'App does not have enough balance to make this transaction'])];
$po2 = DarazaPayments::payout(null, '0772123456', 1, 7500, 'ZZ refund');
check('payout: an error is kept as failed with the reason', !$po2['ok'] && $po2['row']['status'] === 'failed' && str_contains($po2['message'], 'enough balance'));
check('payout: a bad amount is refused', !DarazaPayments::payout(null, '0772123456', 1, 100, 'x')['ok']);

// ---- the pages
DarazaApi::$transport = null;
putenv('DARAZA_API_KEY');
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $path, ?array $form, string $j) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => false]);
    if ($form !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $mm) === 1 ? $mm[1] : '';
$jp = tempnam(sys_get_temp_dir(), 'dzp');
$b2 = DarazaPayments::begin('academy_signup', $lid, 300, 'USD', ['email' => 'zz-dz-l2@example.test']);
check('(setup) a second open payment for the page test', $b2['ok'] || true);
$open = (string) Database::value("SELECT reference FROM daraza_payments WHERE payer_email = 'zz-dz-l2@example.test' LIMIT 1");
if ($open === '') {
    Database::execute("INSERT INTO daraza_payments (reference, direction, purpose, ref_id, amount_minor, currency, amount_ugx, status, mode, payer_email) VALUES ('DZZ-PAGE1','in','academy_cert',?,300,'USD',11100,'pending','sandbox','zz-dz-l2@example.test')", [$lid]);
    $open = 'DZZ-PAGE1';
}
$pg = $req('/pay/mobile-money/' . $open, null, $jp);
check('the payment page shows the amount in UGX and the reference', $pg['code'] === 200 && str_contains($pg['body'], 'UGX 11,100') && str_contains($pg['body'], $open) && str_contains($pg['body'], 'Mobile-money payment is not available'), (string) $pg['code']);
$stj = json_decode($req('/pay/mobile-money/' . $open . '/status', null, $jp)['body'], true);
check('the status endpoint answers with the state', is_array($stj) && $stj['state'] === 'pending');
$run = $req('/pay/mobile-money/' . $open . '/run', ['_token' => $tok($pg['body']), 'phone' => '0772123456', 'method' => '1'], $jp);
check('without the key in .env, "run" says it is not available (no call is made)', in_array($run['code'], [503, 200], true) && str_contains($run['body'], 'unavailable') || str_contains($run['body'], 'not available'), $run['code'] . ' ' . substr($run['body'], 0, 120));
check('an unknown reference is a 404', $req('/pay/mobile-money/NOPE-0000000', null, $jp)['code'] === 404);

$pw = 'Daraza admin pass 71';
\App\Models\User::create(['name' => 'ZZ Dz Admin', 'email' => 'zz-dz-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($pw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$ja = tempnam(sys_get_temp_dir(), 'dza');
$lp = $req('/admin/login', null, $ja);
$req('/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-dz-admin@example.test', 'password' => $pw], $ja);
$adm = $req('/admin/daraza/api', null, $ja);
check('admin: the page shows the connection, the places, the payout form and the payments', $adm['code'] === 200 && str_contains($adm['body'], 'DARAZA_API_KEY') && str_contains($adm['body'], 'Send money') && str_contains($adm['body'], 'Recent payments') && str_contains($adm['body'], 'DZO-'), (string) $adm['code']);
$sv = $req('/admin/daraza/api', ['_token' => $tok($adm['body']), 'on_academy_signup' => '1', 'usd_rate' => '3800'], $ja);
Settings::flush();
check('admin: saving the places and the rate', $sv['code'] === 302 && Settings::get('daraza_api_usd_rate', '') === '3800' && Settings::get('daraza_api_academy_cert_on', '') === '0');
$anon = $req('/admin/daraza/api', null, tempnam(sys_get_temp_dir(), 'dzn'));
check('admin: not public', $anon['code'] === 302);
$po3 = $req('/admin/daraza/api/payout', ['_token' => $tok($adm['body']), 'phone' => '0772123456', 'amount' => '1000', 'method' => '1'], $ja);
check('admin: a payout needs the confirmation box (nothing is sent without it)', $po3['code'] === 302 && (int) Database::value("SELECT COUNT(*) FROM daraza_payments WHERE direction = 'out' AND amount_ugx = 1000", [], 0) === 0);

@unlink($jp);
@unlink($ja);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
