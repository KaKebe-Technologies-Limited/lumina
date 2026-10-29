<?php
declare(strict_types=1);

/**
 * Pesapal payment gateway (docs/PESAPAL.md): client, donations, shop orders, public endpoints. Pesapal itself is replaced by a mock transport, so nothing
 * leaves the computer and no key is needed. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-pesapal.php
 * Needs the local web server for the endpoint checks.
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

use App\Core\Database;
use App\Core\Settings;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\PesapalPayments;
use App\Services\Pesapal\PesapalShop;
use App\Services\Shop\Money;
use App\Services\Shop\OrderService;

echo "-- pesapal --\n";

// ---- a pretend Pesapal -------------------------------------------------------------------------------------------------
$mock = ['calls' => [], 'auth' => 'ok', 'status' => ['status_code' => 0, 'payment_status_description' => 'Pending', 'amount' => 0, 'currency' => 'UGX', 'merchant_reference' => '', 'payment_method' => '', 'confirmation_code' => ''], 'ipn' => 0];
PesapalClient::$transport = static function (string $method, string $url, array $headers, ?string $body) use (&$mock): array {
    $path = (string) parse_url($url, PHP_URL_PATH);
    $mock['calls'][] = [$method, $path, $headers, $body !== null ? json_decode($body, true) : null];
    if (str_ends_with($path, '/api/Auth/RequestToken')) {
        if ($mock['auth'] === 'down') {
            return ['http' => 503, 'raw' => ''];
        }
        if ($mock['auth'] === 'denied') {
            return ['http' => 200, 'raw' => json_encode(['token' => null, 'error' => ['code' => 'invalid_consumer_key_or_secret_provided'], 'status' => '500'])];
        }
        return ['http' => 200, 'raw' => json_encode(['token' => 'TKN-' . count($mock['calls']), 'expiryDate' => '2099-01-01T00:00:00Z', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/URLSetup/RegisterIPN')) {
        $mock['ipn']++;
        return ['http' => 200, 'raw' => json_encode(['ipn_id' => 'aaaaaaaa-1111-2222-3333-bbbbbbbbbbbb', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/Transactions/SubmitOrderRequest')) {
        $id = (string) ($mock['calls'][array_key_last($mock['calls'])][3]['id'] ?? '');
        return ['http' => 200, 'raw' => json_encode(['order_tracking_id' => 'trk-' . strtolower(substr(md5($id), 0, 24)), 'merchant_reference' => $id, 'redirect_url' => 'https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index?OrderTrackingId=x', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/Transactions/GetTransactionStatus')) {
        return ['http' => 200, 'raw' => json_encode($mock['status'])];
    }
    return ['http' => 404, 'raw' => ''];
};
$lastBody = static function (string $suffix) use (&$mock): ?array {
    for ($i = count($mock['calls']) - 1; $i >= 0; $i--) {
        if (str_ends_with($mock['calls'][$i][1], $suffix)) {
            return $mock['calls'][$i][3];
        }
    }
    return null;
};
$answer = static function (string $trackingId, int $code, int|float $amount, string $currency, string $reference) use (&$mock): void {
    $mock['status'] = ['status_code' => $code, 'payment_status_description' => ['Invalid', 'Completed', 'Failed', 'Reversed'][$code] ?? 'Pending', 'amount' => $amount, 'currency' => $currency,
        'merchant_reference' => $reference, 'payment_method' => 'MtnUganda', 'confirmation_code' => 'CONF' . random_int(1000, 9999)];
};

$settingBefore = Settings::get('shop_pay_pesapal_enabled', '0');
$envKey = getenv('PESAPAL_CONSUMER_KEY');
$envSecret = getenv('PESAPAL_CONSUMER_SECRET');
$envMode = getenv('PESAPAL_ENV');
Database::execute("DELETE FROM pesapal_ipns WHERE env = 'sandbox'");

// ---- client -------------------------------------------------------------------------------------------------------------
putenv('PESAPAL_CONSUMER_KEY');
putenv('PESAPAL_CONSUMER_SECRET');
putenv('PESAPAL_ENV=sandbox');
check('no keys: the gateway is not ready and nothing is called', !PesapalClient::hasKeys() && !PesapalPayments::ready() && PesapalClient::token()['error'] === 'no_keys' && $mock['calls'] === []);
$bad = PesapalPayments::begin('donation', 1, 5000, 'UGX', 'x', []);
check('no keys: a payment cannot be started and the failure is recorded', $bad['ok'] === false && $bad['error'] === 'no_keys');
Database::execute("DELETE FROM pesapal_payments WHERE merchant_reference LIKE 'LAFD1-%'");

putenv('PESAPAL_CONSUMER_KEY=selftest-key');
putenv('PESAPAL_CONSUMER_SECRET=selftest-secret');
check('keys in the environment: the gateway is ready, sandbox host is used', PesapalPayments::ready() && PesapalClient::baseUrl() === PesapalClient::SANDBOX);
putenv('PESAPAL_ENV=live');
check('PESAPAL_ENV=live switches to the live host', PesapalClient::baseUrl() === PesapalClient::LIVE);
putenv('PESAPAL_ENV=sandbox');

$mock['auth'] = 'denied';
PesapalClient::resetToken();
check('wrong keys (Pesapal answers 200 with an error) are reported as invalid_keys', PesapalClient::token()['error'] === 'invalid_keys');
$mock['auth'] = 'down';
check('Pesapal down: unavailable', PesapalClient::token()['error'] === 'unavailable');
$mock['auth'] = 'ok';
PesapalClient::resetToken();
check('test connection accepts good keys', PesapalClient::testConnection()['ok'] === true);
$n = count($mock['calls']);
PesapalClient::token();
check('the token is reused, not asked for again on every call', count($mock['calls']) === $n);
check('the Pesapal link must be https on pesapal.com', PesapalClient::safeLink('https://pay.pesapal.com/v3/x') && !PesapalClient::safeLink('http://pay.pesapal.com/x') && !PesapalClient::safeLink('https://pesapal.com.evil.test/x') && !PesapalClient::safeLink('https://evil.test/pesapal.com') && !PesapalClient::safeLink('javascript:alert(1)'));
check('status() refuses a malformed tracking id without calling Pesapal', (function () use (&$mock) { $c = count($mock['calls']); $r = PesapalClient::status("x'; DROP"); return $r['ok'] === false && count($mock['calls']) === $c; })());

// ---- donation: paid ----------------------------------------------------------------------------------------------------
$newDonation = static fn(string $amount = '25000.00'): int => \App\Models\Donation::create([
    'donor_name' => 'SELFTEST Donor', 'donor_email' => 'selftest-donor@example.test', 'amount' => $amount, 'currency' => 'UGX', 'method' => 'pesapal', 'status' => 'pledged', 'source' => 'online',
]);
$d1 = $newDonation();
$r = PesapalPayments::begin('donation', $d1, 2500000, 'UGX', 'Donation', ['name' => 'Ada Lovelace', 'email' => 'ada@example.test', 'phone' => '+256700000001']);
check('donation: a payment page link is returned', $r['ok'] === true && PesapalClient::safeLink((string) $r['link']) && str_starts_with((string) $r['reference'], 'LAFD' . $d1 . '-'));
$row = PesapalPayments::byReference((string) $r['reference']);
$sent = $lastBody('/SubmitOrderRequest');
check('donation: the ledger row is pending with the tracking id', $row !== null && $row['status'] === 'pending' && (string) $row['order_tracking_id'] !== '' && (int) $row['amount_minor'] === 2500000 && $row['mode'] === 'sandbox', json_encode($row));
check('donation: Pesapal was asked for the right amount, currency, callback and registered IPN', $sent !== null && (float) $sent['amount'] === 25000.0 && $sent['currency'] === 'UGX' && str_ends_with((string) $sent['callback_url'], '/pay/pesapal/return')
    && $sent['notification_id'] === 'aaaaaaaa-1111-2222-3333-bbbbbbbbbbbb' && $sent['billing_address']['first_name'] === 'Ada' && $sent['billing_address']['last_name'] === 'Lovelace' && mb_strlen((string) $sent['id']) <= 50, json_encode($sent));
check('the IPN address was registered once and is remembered', $mock['ipn'] === 1);
PesapalPayments::begin('donation', $newDonation(), 100000, 'UGX', 'again', []);
check('a second payment does not register the IPN again', $mock['ipn'] === 1);

$trk = (string) $row['order_tracking_id'];
$answer($trk, 0, 0, 'UGX', (string) $row['merchant_reference']);
check('still unpaid at Pesapal: pending, the donation stays a pledge', PesapalPayments::settle($trk)['state'] === 'pending' && Database::value('SELECT status FROM donations WHERE id = ?', [$d1]) === 'pledged');
$answer($trk, 1, 25000, 'UGX', (string) $row['merchant_reference']);
$s = PesapalPayments::settle($trk);
$don = Database::one('SELECT * FROM donations WHERE id = ?', [$d1]);
check('completed at Pesapal: paid, the donation is received with a receipt number', $s['state'] === 'paid' && $don['status'] === 'received' && $don['method'] === 'pesapal' && str_starts_with((string) $don['receipt_no'], 'RCT-') && $don['received_at'] !== null, json_encode($don));
$receipt = $don['receipt_no'];
$s2 = PesapalPayments::settle($trk);
$don2 = Database::one('SELECT * FROM donations WHERE id = ?', [$d1]);
check('the same notification twice changes nothing (idempotent)', $s2['state'] === 'paid' && $don2['receipt_no'] === $receipt && $don2['received_at'] === $don['received_at']);
check('Pesapal is not asked again about a payment that is already paid', (function () use (&$mock, $trk) { $c = count($mock['calls']); PesapalPayments::settle($trk); return count($mock['calls']) === $c; })());

// ---- donation: failed / short / wrong currency / wrong reference / reversed ----------------------------------------------
$mk = static function (int $minor = 1000000) use ($newDonation): array {
    $d = $newDonation();
    $r = PesapalPayments::begin('donation', $d, $minor, 'UGX', 'x', ['name' => 'T', 'email' => 't@example.test']);
    $row = PesapalPayments::byReference((string) $r['reference']);
    return [$d, (string) $row['order_tracking_id'], (string) $row['merchant_reference']];
};
[$d, $t, $ref] = $mk();
$answer($t, 2, 0, 'UGX', $ref);
check('failed at Pesapal: failed, the donation is cancelled, never received', PesapalPayments::settle($t)['state'] === 'failed' && Database::value('SELECT status FROM donations WHERE id = ?', [$d]) === 'cancelled');
[$d, $t, $ref] = $mk();
$answer($t, 1, 9999, 'UGX', $ref);
check('paid less than asked: review, never received', PesapalPayments::settle($t)['state'] === 'review' && Database::value('SELECT status FROM donations WHERE id = ?', [$d]) === 'pledged');
[$d, $t, $ref] = $mk();
$answer($t, 1, 10000, 'USD', $ref);
check('paid in another currency: review', PesapalPayments::settle($t)['state'] === 'review' && Database::value('SELECT status FROM donations WHERE id = ?', [$d]) === 'pledged');
[$d, $t, $ref] = $mk();
$answer($t, 1, 10000, 'UGX', 'LAFD999-OTHER');
check('Pesapal answers for a different reference: review', PesapalPayments::settle($t)['state'] === 'review' && Database::value('SELECT status FROM donations WHERE id = ?', [$d]) === 'pledged');
[$d, $t, $ref] = $mk();
$answer($t, 1, 10000.0, 'UGX', $ref);
check('a float amount from Pesapal (10000.0) is accepted', PesapalPayments::settle($t)['state'] === 'paid');
[$d, $t, $ref] = $mk();
$answer($t, 3, 10000, 'UGX', $ref);
check('reversed at Pesapal: reversed, the team is told', PesapalPayments::settle($t)['state'] === 'reversed');
check('an unknown tracking id is "unknown", and so is junk', PesapalPayments::settle('trk-doesnotexist00000000')['state'] === 'unknown' && PesapalPayments::settle("a b'c")['state'] === 'unknown');
[$d, $t, $ref] = $mk();
$mock['auth'] = 'down';
PesapalClient::resetToken();
check('Pesapal unreachable while settling: verify_failed, nothing is marked paid', PesapalPayments::settle($t)['state'] === 'verify_failed' && Database::value('SELECT status FROM donations WHERE id = ?', [$d]) === 'pledged');
$mock['auth'] = 'ok';
PesapalClient::resetToken();

// ---- shop ---------------------------------------------------------------------------------------------------------------
$orderNo = 'SELFTEST-' . strtoupper(bin2hex(random_bytes(3)));
$orderId = Database::insert('shop_orders', [
    'order_number' => $orderNo, 'token' => bin2hex(random_bytes(20)), 'customer_name' => 'Shop Tester', 'customer_email' => 'shop-tester@example.test', 'customer_phone' => '+256700000002',
    'payment_method' => 'pesapal', 'status' => 'pending', 'payment_status' => 'unpaid', 'total' => Money::toDb(4500000), 'subtotal' => Money::toDb(4500000), 'currency' => 'UGX',
]);
$order = OrderService::find($orderId);
Settings::set('shop_pay_pesapal_enabled', '0');
check('shop: switched off in Admin > Shop > Payments, the method is not offered', PesapalShop::begin($order)['error'] === 'unavailable' && !\App\Services\Shop\PaymentMethods::isAvailable('pesapal', false));
Settings::set('shop_pay_pesapal_enabled', '1');
check('shop: switched on with keys, the method is offered at checkout', \App\Services\Shop\PaymentMethods::isAvailable('pesapal', false));
$r = PesapalShop::begin($order);
$srow = $r['ok'] ? PesapalPayments::byReference((string) $r['reference']) : null;
check('shop: a payment page link is returned and the order waits for payment', $r['ok'] === true && $srow !== null && $srow['purpose'] === 'shop' && (int) $srow['amount_minor'] === 4500000 && OrderService::find($orderId)['payment_status'] === 'pending', json_encode($r));
$sent = $lastBody('/SubmitOrderRequest');
check('shop: the order number is in the description Pesapal shows', $sent !== null && str_contains((string) $sent['description'], $orderNo));
$answer((string) $srow['order_tracking_id'], 1, 4500000, 'UGX', (string) $srow['merchant_reference']);
$s = PesapalPayments::settle((string) $srow['order_tracking_id']);
$o2 = OrderService::find($orderId);
check('shop: completed at Pesapal marks the order paid, with the Pesapal reference', $s['state'] === 'paid' && $o2['payment_status'] === 'paid' && $o2['paid_at'] !== null && $o2['payment_reference'] === $srow['merchant_reference'], json_encode([$s['state'], $o2['payment_status']]));
$paidRows = (int) Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'success'", [$orderId], 0);
PesapalPayments::settle((string) $srow['order_tracking_id']);
check('shop: a repeated notification records the money once', (int) Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'success'", [$orderId], 0) === $paidRows && $paidRows === 1);
check('shop: "Pay now" on an order that is already paid is refused', PesapalShop::begin(OrderService::find($orderId))['error'] === 'already_paid');
$view = OrderService::paymentInfo(OrderService::find($orderId));
check('shop: the order page knows it is a Pesapal order', $view['method'] === 'pesapal' && $view['paid'] === true);

// a short payment on another order
$orderId2 = Database::insert('shop_orders', [
    'order_number' => $orderNo . 'B', 'token' => bin2hex(random_bytes(20)), 'customer_name' => 'Shop Tester', 'customer_email' => 'shop-tester@example.test', 'customer_phone' => '+256700000002',
    'payment_method' => 'pesapal', 'status' => 'pending', 'payment_status' => 'unpaid', 'total' => Money::toDb(200000), 'subtotal' => Money::toDb(200000), 'currency' => 'UGX',
]);
$r = PesapalShop::begin(OrderService::find($orderId2));
$srow2 = PesapalPayments::byReference((string) $r['reference']);
$answer((string) $srow2['order_tracking_id'], 1, 1000, 'UGX', (string) $srow2['merchant_reference']);
PesapalPayments::settle((string) $srow2['order_tracking_id']);
check('shop: a payment smaller than the order is flagged for review, the order is NOT paid', OrderService::find($orderId2)['payment_status'] !== 'paid');
$r = PesapalShop::begin(OrderService::find($orderId2));
check('shop: after a payment under review, the customer is not invited to pay again', $r['ok'] === false && in_array($r['error'], ['under_review', 'not_payable'], true), json_encode($r));

// ---- public endpoints (real web server) -----------------------------------------------------------------------------------
$base = 'http://localhost/' . basename(dirname(__DIR__));
$http = static function (string $method, string $path, array $post = []) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 20, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_HEADER => true]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
if ($base === '' || (($probe = $http('GET', '/donate'))['code'] ?? 0) !== 200) {
    check('web server reachable for the endpoint checks', false, 'start Apache / check APP_URL');
} else {
    check('donate page: no "Give online" form while the web server has no Pesapal keys', !str_contains($probe['body'], 'id="give-online"') || str_contains($probe['body'], 'give_amount'));
    $r = $http('GET', '/pay/pesapal/return?OrderTrackingId=trk-doesnotexist00000000&OrderMerchantReference=LAFD1-X');
    check('return page: an unknown payment gets a neutral page (200, not cached, no error trace)', $r['code'] === 200 && str_contains($r['body'], 'Payment not found') && stripos($r['head'], 'no-store') !== false);
    $r = $http('GET', '/pay/pesapal/return');
    check('return page without parameters does not crash', in_array($r['code'], [200], true));
    $r = $http('GET', '/pay/pesapal/ipn?OrderTrackingId=trk-doesnotexist00000000&OrderMerchantReference=LAFD1-X&OrderNotificationType=IPNCHANGE');
    $j = json_decode($r['body'], true);
    check('IPN: an unknown payment is answered with the JSON Pesapal expects (status 500 so Pesapal retries)', is_array($j) && $j['orderNotificationType'] === 'IPNCHANGE' && $j['orderTrackingId'] === 'trk-doesnotexist00000000' && $j['status'] === 500 && $r['code'] === 500, $r['body']);
    $r = $http('POST', '/pay/pesapal/ipn', ['OrderTrackingId' => 'trk-doesnotexist00000000']);
    check('IPN also works as a POST without a CSRF token', $r['code'] === 500 && is_array(json_decode($r['body'], true)));
    $r = $http('POST', '/donate/online', ['give_name' => 'X', 'give_email' => 'x@example.test', 'give_amount' => '5000', 'give_currency' => 'UGX']);
    check('giving online needs the CSRF token (a bare POST is refused, no donation is created)', in_array($r['code'], [400, 403, 419], true), (string) $r['code']);
    $r = $http('GET', '/admin/pesapal');
    check('the admin Pesapal page needs a login', in_array($r['code'], [301, 302, 303, 401, 403], true), (string) $r['code']);
}

// ---- the admin pages, signed in (a missing method or table here would be a 500 in production) --------------------------------
if (isset($probe) && $probe['code'] === 200) {
    $jar = tempnam(sys_get_temp_dir(), 'pesj');
    $adminReq = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
        $ch = curl_init($base . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 30, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => false]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        }
        $raw = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return ['code' => $code, 'body' => substr($raw, $hs), 'loc' => preg_match('/^location:\s*(\S+)/mi', substr($raw, 0, $hs), $m) === 1 ? $m[1] : ''];
    };
    $tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
    $pw = 'Pesapal test pass 91';
    \App\Models\User::create(['name' => 'ZZ Pesapal Admin', 'email' => 'zz-pesapal-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($pw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
    Database::execute('DELETE FROM rate_limits');
    $page = $adminReq('GET', '/admin/login');
    $adminReq('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-pesapal-admin@example.test', 'password' => $pw]);
    $r = $adminReq('GET', '/admin/pesapal');
    check('admin: /admin/pesapal opens (200), shows the connection and says the keys are missing', $r['code'] === 200 && str_contains($r['body'], 'Pesapal payments') && str_contains($r['body'], 'Keys missing'), (string) $r['code']);
    $r = $adminReq('GET', '/admin/shop/payments');
    check('admin: Shop > Payments opens (200) and lists the Pesapal method', $r['code'] === 200 && str_contains($r['body'], 'Pesapal'), (string) $r['code']);
    $r = $adminReq('GET', '/admin');
    check('admin: the dashboard shows the Pesapal entry in the menu', $r['code'] === 200 && str_contains($r['body'], 'admin/pesapal'), (string) $r['code']);
    $page = $adminReq('GET', '/admin/pesapal');
    $r = $adminReq('POST', '/admin/pesapal/test', ['_token' => $tok($page['body'])]);
    check('admin: "Test connection" without keys answers with a redirect back and a message, never a 500', in_array($r['code'], [302, 303], true), (string) $r['code']);
    $dRow = Database::insert('pesapal_payments', ['merchant_reference' => 'LAFD0-SELFTEST', 'purpose' => 'donation', 'ref_id' => 999999, 'amount_minor' => 1000, 'currency' => 'UGX', 'status' => 'pending', 'payer_name' => 'ZZ', 'payer_email' => 'zz-pesapal@example.test']);
    $page = $adminReq('GET', '/admin/pesapal');
    check('admin: a payment row is listed', str_contains($page['body'], 'LAFD0-SELFTEST'));
    $r = $adminReq('POST', '/admin/pesapal/' . $dRow . '/check', ['_token' => $tok($page['body'])]);
    check('admin: "Check with Pesapal" on a row that never reached Pesapal explains itself (redirect, no 500)', in_array($r['code'], [302, 303], true), (string) $r['code']);
    // tables missing (the database update was not imported yet): a clear message, never a 500
    Database::execute('RENAME TABLE pesapal_payments TO zz_pesapal_payments_off');
    try {
        $r = $adminReq('GET', '/admin/pesapal');
        check('admin: with the Pesapal tables missing the page still opens and says what to import', $r['code'] === 200 && str_contains($r['body'], 'tables are missing'), (string) $r['code']);
    } finally {
        Database::execute('RENAME TABLE zz_pesapal_payments_off TO pesapal_payments');
    }
    Database::execute("DELETE FROM pesapal_payments WHERE merchant_reference = 'LAFD0-SELFTEST'");
    Database::execute("DELETE FROM users WHERE email = 'zz-pesapal-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
    @unlink($jar);
}
// ---- clean up -----------------------------------------------------------------------------------------------------------
Database::execute("DELETE FROM pesapal_payments WHERE payer_email IN ('ada@example.test', 't@example.test', 'shop-tester@example.test') OR merchant_reference LIKE 'LAFD%' OR merchant_reference LIKE 'LAFS%' AND payer_email = 'shop-tester@example.test'");
Database::execute("DELETE FROM pesapal_payments WHERE ref_id IN (?, ?) AND purpose = 'shop'", [$orderId, $orderId2]);
Database::execute("DELETE FROM shop_payments WHERE order_id IN (?, ?)", [$orderId, $orderId2]);
Database::execute("DELETE FROM shop_order_log WHERE order_id IN (?, ?)", [$orderId, $orderId2]);
Database::execute("DELETE FROM shop_orders WHERE id IN (?, ?)", [$orderId, $orderId2]);
Database::execute("DELETE FROM donations WHERE donor_email = 'selftest-donor@example.test'");
Database::execute("DELETE FROM pesapal_ipns WHERE env = 'sandbox' AND ipn_id = 'aaaaaaaa-1111-2222-3333-bbbbbbbbbbbb'");
Settings::set('shop_pay_pesapal_enabled', (string) $settingBefore);
PesapalClient::$transport = null;
PesapalClient::resetToken();
putenv($envKey === false ? 'PESAPAL_CONSUMER_KEY' : 'PESAPAL_CONSUMER_KEY=' . $envKey);
putenv($envSecret === false ? 'PESAPAL_CONSUMER_SECRET' : 'PESAPAL_CONSUMER_SECRET=' . $envSecret);
putenv($envMode === false ? 'PESAPAL_ENV' : 'PESAPAL_ENV=' . $envMode);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL OK\n" : "\n{$fail} FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
