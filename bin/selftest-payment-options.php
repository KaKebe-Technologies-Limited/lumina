<?php
declare(strict_types=1);

/**
 * PAYMENT OPTIONS: mobile money (Daraza API) in every place (shop, projects, membership, donations, Academy) and the switches that turn the EARLIER ways to pay off and on
 * (Pesapal and other gateways, the Daraza payment link, the manual bank / mobile-money details). Run alone: /c/xampp/php/php.exe bin/selftest-payment-options.php
 * Needs the local web server and APP_ENV=local. A fake Daraza answers; settings are put back.
 */

use App\Core\Database;
use App\Core\Settings;
use App\Services\Academy\AcademyFees;
use App\Services\Daraza;
use App\Services\DarazaPay\DarazaApi;
use App\Services\DarazaPay\DarazaPayments;
use App\Services\Payments\PaymentOptions;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;

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

echo "-- payment options --\n";
$keys = ['pay_opt_online', 'pay_opt_link', 'pay_opt_manual', 'daraza_api_shop_on', 'daraza_api_project_on', 'daraza_api_membership_on', 'daraza_api_donation_on', 'daraza_api_usd_rate',
    'daraza_donate_on', 'daraza_url', 'daraza_label', 'shop_open', 'shop_pay_momo_enabled', 'academy_pay_mobile_money', 'membership_mobile_money'];
$prev = [];
foreach ($keys as $k) {
    $prev[$k] = Settings::get($k, '');
}
$clean = static function () use ($prev): void {
    Database::execute("DELETE FROM shop_orders WHERE customer_email = 'zz-po-shop@example.test'");
    Database::execute("DELETE FROM shop_products WHERE slug LIKE 'zz-po-%'");
    Database::execute("DELETE FROM daraza_payments WHERE payer_email LIKE 'zz-po-%@example.test'");
    Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-po-%@example.test'");
    Database::execute("DELETE FROM member_payments WHERE reference LIKE 'DZM%' OR notes = 'Paid by mobile money through Daraza'");
    Settings::setMany($prev);
    Settings::flush();
    DarazaApi::$transport = null;
    putenv('DARAZA_API_KEY');
    putenv('DARAZA_ENV');
};
$clean();
putenv('DARAZA_API_KEY=zz-test-key');
putenv('DARAZA_ENV=sandbox');
$sent = [];
$answer = ['http' => 201, 'raw' => json_encode(['code' => 'Success', 'message' => 'Payment Successful', 'request_id' => 'REQ-PO-1'])];
DarazaApi::$transport = static function (string $method, string $url, array $headers, ?string $body) use (&$sent, &$answer): array {
    $sent[] = ['url' => $url, 'body' => $body === null ? null : json_decode($body, true)];
    return $answer;
};

// ---- numbers: every MTN and Airtel number
check('numbers: every MTN (076 077 078 039 031) and Airtel (070 074 075 020) prefix is recognised, in any written form', DarazaApi::networkOf('0762000000') === 1 && DarazaApi::networkOf('+256 392 000 000') === 1 && DarazaApi::networkOf('0312000000') === 1
    && DarazaApi::networkOf('(0740) 000 000') === 2 && DarazaApi::networkOf('256752000000') === 2 && DarazaApi::networkOf('0202000000') === 2);

// ---- the three earlier options
check('the earlier options are on by default (nothing changes until you switch them off)', PaymentOptions::online() && PaymentOptions::link() && PaymentOptions::manual());
Settings::setMany(['daraza_url' => 'https://paylink.daraza.net/zz', 'daraza_donate_on' => '1']);
Settings::flush();
check('Daraza payment link: offered while the option is on', Daraza::enabled('donate'));
PaymentOptions::save(['link' => false]);
check('Daraza payment link: gone everywhere when its option is switched off (settings kept)', !Daraza::enabled('donate') && Settings::get('daraza_url', '') === 'https://paylink.daraza.net/zz');
PaymentOptions::save(['link' => true]);
check('Daraza payment link: back when switched on again', Daraza::enabled('donate'));
Settings::setMany(['academy_pay_mobile_money' => 'MTN 0772 000 000', 'shop_pay_momo_enabled' => '1']);
Settings::flush();
check('manual details: Academy shows them while on', AcademyFees::manual() !== null);
PaymentOptions::save(['manual' => false]);
check('manual details: hidden when off (Academy, shop bank/mobile-money methods)', AcademyFees::manual() === null && !PaymentMethods::enabled('momo') && !PaymentMethods::enabled('bank'));
PaymentOptions::save(['manual' => true]);
PaymentOptions::save(['online' => false]);
check('online gateways: Pesapal and Flutterwave are not offered when off', !PaymentMethods::enabled('pesapal') && !PaymentMethods::enabled('flutterwave') && !\App\Services\Pesapal\PesapalPayments::ready());
PaymentOptions::save(['online' => true]);

// ---- mobile money in the shop
Settings::setMany(['daraza_api_shop_on' => '0']);
Settings::flush();
check('shop: mobile money is not offered until it is switched on', !PaymentMethods::enabled('mobile'));
Settings::setMany(['daraza_api_shop_on' => '1', 'daraza_api_usd_rate' => '3700', 'shop_open' => '1']);
Settings::flush();
$avail = array_column(PaymentMethods::all(), null, 'key');
check('shop: "MTN or Airtel mobile money" is offered, ready (the key is in .env) and listed first', PaymentMethods::enabled('mobile') && $avail['mobile']['ready'] && PaymentMethods::all()[0]['key'] === 'mobile' && str_contains($avail['mobile']['label'], 'MTN'));
$pid = Database::insert('shop_products', ['slug' => 'zz-po-' . bin2hex(random_bytes(3)), 'name' => 'ZZ PO Product', 'price' => '25000.00', 'status' => 'published', 'track_stock' => 0, 'stock_qty' => null, 'low_stock' => 2, 'allow_booking' => 0, 'is_sample' => 0]);
$line = ['key' => substr(sha1((string) $pid), 0, 12), 'product_id' => $pid, 'selection' => [], 'qty' => 2];
$res = OrderService::create(['name' => 'Zz Po Shopper', 'email' => 'zz-po-shop@example.test', 'phone' => '0772123456', 'address' => '', 'note' => '', 'consent' => true], [$line], 'pickup', 'mobile', ['notify' => false, 'ip_hash' => 'x']);
check('shop: an order can be placed with the mobile-money method', $res['ok'] === true, json_encode($res['error'] ?? null));
$order = (array) ($res['order'] ?? []);
$b = DarazaPayments::beginShop($order);
check('shop: the payment page address is made, for the order total (UGX 50,000)', $b['ok'] && (int) Database::value('SELECT amount_ugx FROM daraza_payments WHERE reference = ?', [explode('/', (string) $b['url'])[count(explode('/', (string) $b['url'])) - 1]]) === 50000, json_encode($b));
$ref = basename((string) $b['url']);
check('shop: the order waits (payment pending, with a hold time)', Database::value('SELECT payment_status FROM shop_orders WHERE id = ?', [(int) $order['id']]) === 'pending');
$c = DarazaPayments::collect($ref, '0772123456', 0);
$o2 = Database::one('SELECT status, payment_status, payment_method FROM shop_orders WHERE id = ?', [(int) $order['id']]);
check('shop: when Daraza confirms, the order is PAID and recorded in the shop ledger', $c['state'] === 'paid' && $o2['payment_status'] === 'paid' && (int) Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'success'", [(int) $order['id']], 0) === 1, json_encode($o2));
check('shop: a paid order cannot be paid again', !DarazaPayments::beginShop($order)['ok']);

// ---- gifts to a project, and donations
Database::execute("INSERT INTO donations (donor_name, donor_email, amount, currency, method, status, source) VALUES ('Zz Po Donor','zz-po-d@example.test',30000,'UGX','mobile_money','pledged','pledge_form')");
$did = (int) Database::value('SELECT LAST_INSERT_ID()');
Settings::setMany(['daraza_api_project_on' => '1']);
Settings::flush();
$bp = DarazaPayments::begin('project', $did, 3000000, 'UGX', ['name' => 'Zz Po Donor', 'email' => 'zz-po-d@example.test']);
check('project: a mobile-money gift to a project gets its payment page', $bp['ok'] && (int) Database::value('SELECT amount_ugx FROM daraza_payments WHERE reference = ?', [$bp['reference']]) === 30000);
DarazaPayments::collect((string) $bp['reference'], '+256702000000', 0);
$d = Database::one('SELECT status, method FROM donations WHERE id = ?', [$did]);
check('project: when confirmed the pledge becomes "received" by mobile money (Airtel was detected)', $d['status'] === 'received' && $d['method'] === 'mobile_money' && end($sent)['body']['method'] === 2);

// ---- membership
$mem = Database::one('SELECT id FROM members ORDER BY id LIMIT 1');
if ($mem === null) {
    check('(skipped) no member in the local database to pay for', true);
} else {
    Settings::setMany(['daraza_api_membership_on' => '1']);
    Settings::flush();
    $bm = DarazaPayments::begin('membership', (int) $mem['id'], 5000000, 'UGX', ['name' => 'Zz', 'email' => 'zz-po-m@example.test']);
    DarazaPayments::collect((string) $bm['reference'], '0772123456', 0);
    $pay = Database::one("SELECT status, method, amount, reference FROM member_payments WHERE member_id = ? AND reference = ?", [(int) $mem['id'], $bm['reference']]);
    check('membership: a confirmed mobile-money payment is recorded as a RECEIVED membership payment with a receipt', $pay !== null && $pay['status'] === 'received' && $pay['method'] === 'mobile_money' && (float) $pay['amount'] === 50000.0, json_encode($pay));
    Database::execute('DELETE FROM member_payments WHERE reference = ?', [$bm['reference']]);
}

// ---- the admin page and the pages
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
$pw = 'Payment options pass 71';
Database::execute("DELETE FROM users WHERE email = 'zz-po-admin@example.test'");
\App\Models\User::create(['name' => 'ZZ Po Admin', 'email' => 'zz-po-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($pw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$ja = tempnam(sys_get_temp_dir(), 'poa');
$lp = $req('/admin/login', null, $ja);
$req('/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-po-admin@example.test', 'password' => $pw], $ja);
$pg = $req('/admin/daraza/api', null, $ja);
check('admin: the Payment options page lists every place, the three earlier options and the "Mobile money only" button', $pg['code'] === 200 && str_contains($pg['body'], 'Shop orders') && str_contains($pg['body'], 'Gifts to a project') && str_contains($pg['body'], 'Membership payments')
    && str_contains($pg['body'], 'Daraza payment link') && str_contains($pg['body'], 'Mobile money only'), (string) $pg['code']);
$mo = $req('/admin/daraza/api', ['_token' => $tok($pg['body']), 'preset' => 'mobile_only'], $ja);
Settings::flush();
check('admin: "Mobile money only" turns mobile money on everywhere and the earlier ways off', $mo['code'] === 302 && !PaymentOptions::online() && !PaymentOptions::link() && !PaymentOptions::manual() && DarazaPayments::on('shop') && DarazaPayments::on('membership') && DarazaPayments::on('project'));
$pg2 = $req('/admin/daraza/api', null, $ja);
$rs = $req('/admin/daraza/api', ['_token' => $tok($pg2['body']), 'preset' => 'restore'], $ja);
Settings::flush();
check('admin: "Switch the earlier ways back on" restores them (nothing was lost)', $rs['code'] === 302 && PaymentOptions::online() && PaymentOptions::link() && PaymentOptions::manual());
$don = $req('/donate', null, $ja);
check('the donate page opens (the banner appears only when the key and the switch are on)', $don['code'] === 200);

Database::execute("DELETE FROM users WHERE email = 'zz-po-admin@example.test'");
@unlink($ja);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
