<?php
declare(strict_types=1);

/**
 * "Pay with Daraza": the payment link set in the admin, offered on the Donate page and buttons, as a shop payment method and for the two Academy fees. Daraza has no
 * automatic confirmation: staff confirm. Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-daraza.php
 * Needs the local web server. Removes its rows (zz-dz-*) and puts the settings back.
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
use App\Services\Daraza;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\PaymentService;

echo "-- pay with daraza --\n";

$keys = ['daraza_label', 'daraza_url', 'daraza_donate_url', 'daraza_shop_url', 'daraza_signup_url', 'daraza_cert_url', 'daraza_donate_on', 'daraza_signup_on', 'daraza_cert_on',
    'shop_pay_daraza_enabled', 'academy_signup_fee', 'academy_signup_fee_currency', 'academy_cert_fee', 'academy_cert_fee_currency', 'academy_cert_approval', 'academy_signup_mode', 'donate_method', 'academy_register_photo_required', 'academy_register_follow_required'];
$before = [];
foreach ($keys as $k) {
    $before[$k] = Settings::isCustomised($k) ? Settings::get($k) : null;
}
$envKeys = [getenv('PESAPAL_CONSUMER_KEY'), getenv('PESAPAL_CONSUMER_SECRET')];
putenv('PESAPAL_CONSUMER_KEY');
putenv('PESAPAL_CONSUMER_SECRET');
$clean = static function (): void {
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Dz%'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-dz-%@example.test'");
    Database::execute("DELETE FROM shop_order_log WHERE order_id IN (SELECT id FROM shop_orders WHERE customer_email LIKE 'zz-dz-%@example.test')");
    Database::execute("DELETE FROM shop_payments WHERE order_id IN (SELECT id FROM shop_orders WHERE customer_email LIKE 'zz-dz-%@example.test')");
    Database::execute("DELETE FROM shop_orders WHERE customer_email LIKE 'zz-dz-%@example.test'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-dz-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
Settings::setMany(['academy_register_photo_required' => '0', 'academy_register_follow_required' => '0', 'daraza_label' => '', 'daraza_url' => '', 'daraza_donate_url' => '', 'daraza_shop_url' => '', 'daraza_signup_url' => '', 'daraza_cert_url' => '', 'daraza_donate_on' => '0', 'daraza_signup_on' => '0', 'daraza_cert_on' => '0', 'shop_pay_daraza_enabled' => '0']);

// ---- the link ---------------------------------------------------------------------------------------------------------------------
$good = 'https://paylink.daraza.net/auths/pay/a9fd8f56-75e3-4547-8d2a-f3130b0ee0aa';
check('links: https on daraza.net (and its sub-domains) are accepted', Daraza::validUrl($good) && Daraza::validUrl('https://daraza.net/pay/1') && Daraza::validUrl('https://pay.daraza.net/x?a=1'));
check('links: other hosts, look-alikes, plain http, credentials and script links are refused', !Daraza::validUrl('http://paylink.daraza.net/x') && !Daraza::validUrl('https://paylink.daraza.net.evil.test/x') && !Daraza::validUrl('https://evil.test/paylink.daraza.net')
    && !Daraza::validUrl('https://user:pw@paylink.daraza.net/x') && !Daraza::validUrl('javascript:alert(1)') && !Daraza::validUrl('') && !Daraza::validUrl('https://notdaraza.net/x'));
check('label: "Pay with Daraza" by default, editable, markup removed', Daraza::label() === 'Pay with Daraza' && (function () {
    Settings::set('daraza_label', '<b>Give via</b> Daraza');
    $ok = Daraza::label() === 'Give via Daraza';
    Settings::set('daraza_label', '');
    return $ok;
})());
Settings::set('daraza_url', $good);
check('url: the main link is used by every place', Daraza::url('donate') === $good && Daraza::url('shop') === $good && Daraza::url('signup') === $good && Daraza::url('cert') === $good && Daraza::url('nope') === '');
Settings::set('daraza_signup_url', 'https://paylink.daraza.net/auths/pay/signup-only');
check('url: a place can have a link of its own', Daraza::url('signup') === 'https://paylink.daraza.net/auths/pay/signup-only' && Daraza::url('cert') === $good);
Settings::set('daraza_signup_url', 'https://evil.test/steal');
check('url: a bad stored link is ignored (falls back to the main link), never used', Daraza::url('signup') === $good);
Settings::set('daraza_signup_url', '');
Settings::set('daraza_url', '');
check('enabled: needs the switch AND a link', !Daraza::enabled('donate') && (function () use ($good) {
    Settings::set('daraza_donate_on', '1');
    $none = !Daraza::enabled('donate');
    Settings::set('daraza_url', $good);
    return $none && Daraza::enabled('donate');
})());

// ---- shop ----------------------------------------------------------------------------------------------------------------------------
Settings::setMany(['shop_pay_daraza_enabled' => '1', 'daraza_url' => '']);
check('shop: the method exists, is manual, and is NOT ready without a link (the admin is told what is missing)', in_array('daraza', PaymentMethods::KEYS, true) && PaymentMethods::KINDS['daraza'] === 'manual' && !PaymentMethods::ready('daraza') && PaymentMethods::problems('daraza') !== []);
Settings::set('daraza_url', $good);
check('shop: with a link and switched on it is offered at checkout, with the label "Pay with Daraza"', PaymentMethods::ready('daraza') && PaymentMethods::isAvailable('daraza', false) && PaymentMethods::label('daraza') === 'Pay with Daraza');
$oid = Database::insert('shop_orders', ['order_number' => 'ZZDZ-' . strtoupper(bin2hex(random_bytes(3))), 'token' => bin2hex(random_bytes(20)), 'customer_name' => 'ZZ Dz Buyer', 'customer_email' => 'zz-dz-buyer@example.test', 'customer_phone' => '+256700000031',
    'payment_method' => 'daraza', 'status' => 'pending', 'payment_status' => 'unpaid', 'total' => '50000.00', 'subtotal' => '50000.00', 'currency' => 'UGX']);
$info = OrderService::paymentInfo((array) OrderService::find($oid));
check('shop: the order page gets the Daraza link and may take the customer\'s transaction ID', $info['method'] === 'daraza' && $info['daraza_url'] === $good && $info['can_enter_reference'] === true && $info['can_pay_online'] === false);
$ref = PaymentService::recordCustomerReference($oid, 'DZ123456789');
check('shop: the customer\'s Daraza transaction ID is stored, the payment is "pending" (never paid by the customer)', $ref['ok'] === true && OrderService::find($oid)['payment_status'] === 'pending');
$paid = PaymentService::markPaid($oid, ['method' => 'daraza', 'reference' => 'DZ123456789', 'amount' => 5000000, 'currency' => 'UGX', 'source' => 'staff']);
check('shop: staff mark the order paid after checking Daraza', $paid['ok'] === true && OrderService::find($oid)['payment_status'] === 'paid');
check('shop: the hold time of a Daraza order is the manual one', strlen(OrderService::holdUntil('daraza')) === 19 && OrderService::holdUntil('daraza') === OrderService::holdUntil('momo'));

// ---- donate ---------------------------------------------------------------------------------------------------------------------------
$base = 'http://localhost/' . basename(dirname(__DIR__));
$get = static function (string $path) use ($base): string {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => false]);
    $b = (string) curl_exec($ch);
    curl_close($ch);
    return $b;
};
Settings::setMany(['daraza_donate_on' => '1', 'donate_method' => 'link']);
check('donate buttons: with Daraza on, every Donate button leads to the "Give online" section of the Donate page', donate_online_url() === abs_url('donate') . '#give-online');
$page = $get('/donate');
check('donate page: the section shows the Daraza button (new tab) and no Pesapal form without keys', str_contains($page, 'id="give-online"') && str_contains($page, $good) && str_contains($page, 'Pay with Daraza') && !str_contains($page, 'action="' . $base . '/donate/online"')
    && preg_match('/<a[^>]*href="' . preg_quote($good, '/') . '"[^>]*target="_blank"/', $page) === 1);
check('donate page: the other online link is kept as a small link when the choice keeps it', preg_match('/Or give through our other online link/', $page) === 1 || trim((string) Settings::get('donate_url', '')) === '');
Settings::set('donate_method', 'pesapal');
check('donate page: with Pesapal only (no keys here) the Daraza button still works', str_contains($get('/donate'), $good));
Settings::set('daraza_donate_on', '0');
check('donate buttons: with Daraza off they go back to the old behaviour (link, no Daraza button)', !str_contains($get('/donate'), $good) && !Daraza::enabled('donate'));
Settings::set('donate_method', (string) ($before['donate_method'] ?? 'both'));

// ---- Academy fees (Pesapal keys absent: only Daraza) ----------------------------------------------------------------------------------
Settings::setMany(['academy_signup_fee' => '20000', 'academy_signup_fee_currency' => 'UGX', 'academy_cert_fee' => '15000', 'academy_cert_fee_currency' => 'UGX', 'daraza_signup_on' => '0', 'daraza_cert_on' => '0', 'daraza_api_academy_signup_on' => '0', 'daraza_api_academy_cert_on' => '0', 'academy_signup_mode' => 'approval', 'academy_cert_approval' => '1']);
check('academy: no Pesapal keys and Daraza off: the fees are not charged (nobody is blocked)', AcademyFees::signup() === null && AcademyFees::cert() === null);
Settings::setMany(['daraza_signup_on' => '1', 'daraza_cert_on' => '1']);
check('academy: Daraza alone is enough to charge the fees', AcademyFees::signup()['display'] === 'UGX 20,000' && AcademyFees::cert()['display'] === 'UGX 15,000' && AcademyFees::daraza('signup')['url'] === $good && AcademyFees::daraza('cert')['label'] === 'Pay with Daraza');
$pw = 'Daraza test pass 18';
$reg = LearnerAuth::register(['name' => 'ZZ Dz One', 'email' => 'zz-dz-one@example.test', 'password' => $pw, 'phone' => '+256700000041', 'country' => 'Uganda', 'whatsapp' => '+256700000041'], '10.8.8.1');
$lid = (int) ($reg['learner']['id'] ?? 0);
check('academy: a new learner owes the sign-up fee and cannot sign in until it is settled', ($reg['learner']['fee_status'] ?? '') === 'due' && LearnerAuth::login('zz-dz-one@example.test', $pw, '10.8.8.1')['ok'] === false);
check('academy: an administrator marks the Daraza payment as paid; then the account follows the normal approval', AcademyFees::markPaidByAdmin('signup', $lid) === true && Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$lid]) === 'paid' && AcademyFees::markPaidByAdmin('signup', $lid) === false);
$course = Database::one('SELECT * FROM academy_courses ORDER BY id LIMIT 1');
if ($course !== null) {
    $learner = (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$lid]);
    $learner['name'] = 'ZZ Dz One';
    $cert = CertificateService::issue($learner, (array) $course, 80.0, 60);
    check('academy: an exam certificate is held for the fee; the administrator marks it paid, then approves it', $cert['fee_status'] === 'due' && CertificateService::approve((int) $cert['id'], null, false)['ok'] === false
        && AcademyFees::markPaidByAdmin('cert', (int) $cert['id']) === true && CertificateService::approve((int) $cert['id'], null, false)['ok'] === true && CertificateService::activeFor($lid, (int) $course['id']) !== null);
}

// ---- the public pages and the admin page --------------------------------------------------------------------------------------------------
$jar = tempnam(sys_get_temp_dir(), 'dzj');
$req = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
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
    return ['code' => $code, 'body' => substr($raw, $hs), 'loc' => preg_match('/^location:\s*(\S+)/mi', substr($raw, 0, $hs), $m) === 1 ? $m[1] : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$rg = $req('GET', '/academy/register');
check('sign-up page: names the fee and how to pay it (Daraza here)', str_contains($rg['body'], 'Sign-up fee') && str_contains($rg['body'], 'Pay with Daraza') && str_contains($rg['body'], 'Create my account and pay the fee'));
preg_match('/name="_hp_ts" value="([^"]+)"/', $rg['body'], $ts);
sleep(6);
$post = $req('POST', '/academy/register', ['_token' => $tok($rg['body']), '_hp_ts' => $ts[1] ?? '', 'name' => 'ZZ Dz Web', 'email' => 'zz-dz-web@example.test', 'password' => 'Daraza web pass 4321x', 'country' => 'Uganda', 'phone' => '+256 700000042', 'whatsapp' => '+256 700000042', 'agree' => '1']);
$pend = $req('GET', '/academy/pending');
check('sign-up: the new learner lands on the waiting page with the Daraza button and no Pesapal button (no keys)', $post['code'] === 302 && str_ends_with($post['loc'], '/academy/pending') && str_contains($pend['body'], 'Sign-up fee: UGX 20,000') && str_contains($pend['body'], $good)
    && str_contains($pend['body'], 'Pay with Daraza') && !str_contains($pend['body'], 'with Pesapal'), $post['code'] . ' ' . $post['loc']);

User::create(['name' => 'ZZ Dz Admin', 'email' => 'zz-dz-admin@example.test', 'password_hash' => Auth::hashPassword('Daraza admin pass 66'), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$jar2 = tempnam(sys_get_temp_dir(), 'dzj2');
$jar = $jar2;
$pg = $req('GET', '/admin/login');
$req('POST', '/admin/login', ['_token' => $tok($pg['body']), 'email' => 'zz-dz-admin@example.test', 'password' => 'Daraza admin pass 66']);
$web = (int) Database::value("SELECT id FROM academy_learners WHERE email = 'zz-dz-web@example.test'", [], 0);
$lp = $req('GET', '/admin/academy/learners/' . $web);
check('admin: the learner page has "Mark the fee as paid" next to "Waive the fee"', $lp['code'] === 200 && str_contains($lp['body'], 'Mark the fee as paid') && str_contains($lp['body'], 'Waive the fee'), (string) $lp['code']);
$mk = $req('POST', '/admin/academy/learners/' . $web . '/mark-fee-paid', ['_token' => $tok($lp['body'])]);
check('admin: pressing it marks the fee paid', in_array($mk['code'], [302, 303], true) && Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$web]) === 'paid');
$dz = $req('GET', '/admin/daraza');
check('admin: the Pay with Daraza page opens with the link, the button text and the four places', $dz['code'] === 200 && str_contains($dz['body'], 'Main payment link') && str_contains($dz['body'], 'name="on_donate"') && str_contains($dz['body'], 'name="on_shop"') && str_contains($dz['body'], 'name="on_signup"') && str_contains($dz['body'], 'name="on_cert"'), (string) $dz['code']);
$new = 'https://paylink.daraza.net/auths/pay/new-link-123';
$sv = $req('POST', '/admin/daraza', ['_token' => $tok($dz['body']), 'label' => 'Give with Daraza', 'url' => $new, 'donate_url' => '', 'shop_url' => '', 'signup_url' => '', 'cert_url' => '', 'on_donate' => '1', 'on_shop' => '1']);
Settings::flush();
check('admin: saving stores the link, the button text and the ticks (and unticked places go off)', in_array($sv['code'], [302, 303], true) && Settings::get('daraza_url') === $new && Settings::get('daraza_label') === 'Give with Daraza' && Settings::get('daraza_donate_on') === '1'
    && Settings::get('shop_pay_daraza_enabled') === '1' && Settings::get('daraza_signup_on') === '0' && Settings::get('daraza_cert_on') === '0');
$dz = $req('GET', '/admin/daraza');
$bad = $req('POST', '/admin/daraza', ['_token' => $tok($dz['body']), 'label' => 'X', 'url' => 'https://evil.test/pay', 'on_donate' => '1']);
Settings::flush();
check('admin: a link that is not on daraza.net is refused and nothing changes', Settings::get('daraza_url') === $new && Settings::get('daraza_label') === 'Give with Daraza');
$nc = $req('POST', '/admin/daraza', ['url' => $new]);
check('admin: saving needs the CSRF token (403)', $nc['code'] === 403, (string) $nc['code']);
$sp = $req('GET', '/admin/shop/payments');
check('admin: Shop > Payments lists "Pay with Daraza" among the ways to pay', $sp['code'] === 200 && str_contains($sp['body'], 'Give with Daraza') || str_contains($sp['body'], 'Pay with Daraza'), (string) $sp['code']);
@unlink($jar2);
@unlink($jar);

// ---- clean up ----------------------------------------------------------------------------------------------------------------------------
$clean();
foreach ($before as $k => $v) {
    if ($v === null) {
        Settings::reset($k);
    } else {
        Settings::set($k, (string) $v);
    }
}
putenv($envKeys[0] === false ? 'PESAPAL_CONSUMER_KEY' : 'PESAPAL_CONSUMER_KEY=' . $envKeys[0]);
putenv($envKeys[1] === false ? 'PESAPAL_CONSUMER_SECRET' : 'PESAPAL_CONSUMER_SECRET=' . $envKeys[1]);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL OK\n" : "\n{$fail} FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
