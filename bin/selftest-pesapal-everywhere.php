<?php
declare(strict_types=1);

/**
 * Pesapal at EVERY payment point, as the SECOND option (docs/PESAPAL.md, section 8): the "Offer Pesapal everywhere" switch, where Pesapal is visible and why not, the order set in
 * Admin > Payment options, the "Mobile money only" warning, membership and project payments through Pesapal (mock transport: no keys, no internet) and the abilities.
 * Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-pesapal-everywhere.php
 * Needs the local web server for the admin checks. Puts every setting back and removes its rows (@selftest-pe.invalid).
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
use App\Services\Payments\PaymentOptions;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\PesapalPayments;
use App\Services\Pesapal\PesapalPlaces;

echo "-- pesapal everywhere --\n";

// ---- settings snapshot (everything this test touches is put back at the end) ----------------------------------------------------------------
$keys = ['donate_method', 'shop_pay_pesapal_enabled', 'shop_open', 'pesapal_place_project', 'pesapal_place_membership', 'pesapal_place_academy_signup', 'pesapal_place_academy_cert',
    'pay_opt_online', 'pay_opt_link', 'pay_opt_manual', 'pay_opt_other', 'pay_rank_mobile', 'pay_rank_online', 'daraza_api_project_on', 'daraza_api_membership_on', 'daraza_api_donation_on',
    'academy_signup_fee', 'academy_signup_fee_currency', 'academy_cert_fee', 'academy_cert_fee_currency', 'academy_signup_fee_enabled'];
$snap = [];
foreach ($keys as $k) {
    $row = Database::one('SELECT setting_value FROM settings WHERE setting_key = ?', [$k]);
    $snap[$k] = $row === null ? null : (string) $row['setting_value'];
}
$restore = static function () use ($snap): void {
    foreach ($snap as $k => $v) {
        if ($v === null) {
            Database::execute('DELETE FROM settings WHERE setting_key = ?', [$k]);
        } else {
            Database::execute('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)', [$k, $v]);
        }
    }
    Settings::flush();
    PesapalPlaces::forgetCache();
};
$envKey = getenv('PESAPAL_CONSUMER_KEY');
$envSecret = getenv('PESAPAL_CONSUMER_SECRET');
$envMode = getenv('PESAPAL_ENV');
$envDaraza = getenv('DARAZA_API_KEY');
$clean = static function (): void {
    Database::execute("DELETE FROM pesapal_payments WHERE payer_email LIKE '%@selftest-pe.invalid'");
    Database::execute("DELETE FROM member_payments WHERE member_id IN (SELECT id FROM members WHERE email LIKE '%@selftest-pe.invalid')");
    Database::execute("DELETE FROM members WHERE email LIKE '%@selftest-pe.invalid'");
    Database::execute("DELETE FROM donations WHERE donor_email LIKE '%@selftest-pe.invalid'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-pe-%@example.test'");
};
$clean();

// ---- a pretend Pesapal -------------------------------------------------------------------------------------------------
$mock = ['calls' => [], 'status' => []];
PesapalClient::$transport = static function (string $method, string $url, array $headers, ?string $body) use (&$mock): array {
    $path = (string) parse_url($url, PHP_URL_PATH);
    $mock['calls'][] = [$method, $path, $body !== null ? json_decode($body, true) : null];
    if (str_ends_with($path, '/api/Auth/RequestToken')) {
        return ['http' => 200, 'raw' => json_encode(['token' => 'TKN', 'expiryDate' => '2099-01-01T00:00:00Z', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/URLSetup/RegisterIPN')) {
        return ['http' => 200, 'raw' => json_encode(['ipn_id' => 'aaaaaaaa-1111-2222-3333-bbbbbbbbbbbb', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/Transactions/SubmitOrderRequest')) {
        $id = (string) ($mock['calls'][array_key_last($mock['calls'])][2]['id'] ?? '');
        return ['http' => 200, 'raw' => json_encode(['order_tracking_id' => 'trk-' . strtolower(substr(md5($id), 0, 24)), 'merchant_reference' => $id, 'redirect_url' => 'https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index?OrderTrackingId=x', 'status' => '200'])];
    }
    if (str_ends_with($path, '/api/Transactions/GetTransactionStatus')) {
        return ['http' => 200, 'raw' => json_encode($mock['status'])];
    }
    return ['http' => 404, 'raw' => ''];
};
$answer = static function (int $code, int|float $amount, string $currency, string $reference) use (&$mock): void {
    $mock['status'] = ['status_code' => $code, 'payment_status_description' => ['Invalid', 'Completed', 'Failed', 'Reversed'][$code] ?? 'Pending', 'amount' => $amount, 'currency' => $currency,
        'merchant_reference' => $reference, 'payment_method' => 'Visa', 'confirmation_code' => 'CONF' . random_int(1000, 9999)];
};

// ---- the places, with no keys ---------------------------------------------------------------------------------------------------
putenv('PESAPAL_CONSUMER_KEY');
putenv('PESAPAL_CONSUMER_SECRET');
putenv('PESAPAL_ENV=sandbox');
putenv('DARAZA_API_KEY=selftest-daraza');
foreach (['pesapal_place_project', 'pesapal_place_membership', 'pesapal_place_academy_signup', 'pesapal_place_academy_cert', 'pay_opt_online', 'pay_opt_link', 'pay_opt_manual', 'pay_opt_other', 'pay_rank_mobile', 'pay_rank_online'] as $k) {
    Database::execute('DELETE FROM settings WHERE setting_key = ?', [$k]);
}
Settings::setMany(['donate_method' => 'link', 'shop_pay_pesapal_enabled' => '0', 'shop_open' => '1']);
PesapalPlaces::forgetCache();
check('six payment points are known', array_keys(PesapalPlaces::PLACES) === ['donate', 'project', 'shop', 'membership', 'academy_signup', 'academy_cert']);
check('a fresh install: donate, projects, shop and membership are OFF; the two Academy places (always offered before) stay on', !PesapalPlaces::on('donate') && !PesapalPlaces::on('project') && !PesapalPlaces::on('shop') && !PesapalPlaces::on('membership') && PesapalPlaces::on('academy_signup') && PesapalPlaces::on('academy_cert'));
check('no keys: Pesapal is visible nowhere and the problem says the keys are missing', array_filter(array_keys(PesapalPlaces::PLACES), static fn(string $p): bool => PesapalPlaces::visible($p)) === [] && str_contains(implode(' ', PesapalPlaces::problems('membership')), 'keys'));

// ---- the everywhere switch --------------------------------------------------------------------------------------------------------
putenv('PESAPAL_CONSUMER_KEY=selftest-key');
putenv('PESAPAL_CONSUMER_SECRET=selftest-secret');
Settings::setMany(['academy_signup_fee' => '20000', 'academy_signup_fee_currency' => 'UGX', 'academy_cert_fee' => '10000', 'academy_cert_fee_currency' => 'UGX', 'academy_signup_fee_enabled' => '1',
    'daraza_api_project_on' => '1', 'daraza_api_membership_on' => '1', 'daraza_api_donation_on' => '1']);
PesapalPlaces::forgetCache();
check('keys but every place off: donate, projects, shop and membership are hidden ("the place is switched off")', !PesapalPlaces::visible('donate') && !PesapalPlaces::visible('project') && !PesapalPlaces::visible('shop') && !PesapalPlaces::visible('membership')
    && str_contains(implode(' ', PesapalPlaces::problems('project')), 'switched off'));
$firstBefore = [Settings::get('daraza_api_project_on'), Settings::get('daraza_api_membership_on'), Settings::get('daraza_api_donation_on'), Settings::get('pay_opt_online', '1'), Settings::get('pay_opt_link', '1'), Settings::get('pay_opt_manual', '1')];
PesapalPlaces::enableEverywhere(true);
PesapalPlaces::forgetCache();
$vis = array_map(static fn(string $p): bool => PesapalPlaces::visible($p), array_keys(PesapalPlaces::PLACES));
check('the everywhere switch ticks every place and every place is visible (keys, tables, switch, fees set)', !in_array(false, $vis, true), json_encode(array_combine(array_keys(PesapalPlaces::PLACES), $vis)));
check('the everywhere switch does not touch the first option (mobile money switches) nor the other ways', $firstBefore === [Settings::get('daraza_api_project_on'), Settings::get('daraza_api_membership_on'), Settings::get('daraza_api_donation_on'), Settings::get('pay_opt_online', '1'), Settings::get('pay_opt_link', '1'), Settings::get('pay_opt_manual', '1')]
    && \App\Services\DarazaPay\DarazaPayments::enabled('membership') && \App\Services\DarazaPay\DarazaPayments::enabled('project'));
Settings::set('donate_method', 'pesapal');
PesapalPlaces::enableEverywhere(true);
check('"Pesapal only" for donations is kept (only "link" is turned back to "both")', Settings::get('donate_method') === 'pesapal');
Settings::set('donate_method', 'link');
PesapalPlaces::enableEverywhere(true);
check('a donations place that was off becomes "both"', Settings::get('donate_method') === 'both' && PesapalPlaces::on('donate'));

// each place on its own: switched off = hidden, others unaffected
foreach (['project', 'membership', 'academy_signup', 'academy_cert'] as $p) {
    Settings::set('pesapal_place_' . $p, '0');
    PesapalPlaces::forgetCache();
    $others = array_filter(array_keys(PesapalPlaces::PLACES), static fn(string $o): bool => $o !== $p && !PesapalPlaces::visible($o));
    check("{$p}: switched off it is hidden, the other places stay visible", !PesapalPlaces::visible($p) && !PesapalPayments::offered($p) && $others === []);
    Settings::set('pesapal_place_' . $p, '1');
}
PesapalPlaces::forgetCache();
Settings::set('shop_open', '0');
check('shop closed: Pesapal is hidden at checkout with that reason', !PesapalPlaces::visible('shop') && str_contains(implode(' ', PesapalPlaces::problems('shop')), 'closed'));
Settings::set('shop_open', '1');
Settings::set('academy_signup_fee', '0');
PesapalPlaces::forgetCache();
check('Academy sign-up fee 0: nothing to pay, so Pesapal is not shown there', !PesapalPlaces::visible('academy_signup') && PesapalPlaces::visible('academy_cert'));
Settings::set('academy_signup_fee', '20000');
PesapalPlaces::forgetCache();

// ---- the order set in Admin > Payment options, on the pages that mix the two ways (fund a project) ------------------------------------------------
$fundMethods = static function (): array {
    $m = new ReflectionMethod(\App\Controllers\Web\FundController::class, 'methods');
    $m->setAccessible(true);
    return array_keys($m->invoke(null));
};
$ord = $fundMethods();
check('Fund a project: mobile money first, Pesapal second, then the manual ways (default order)', array_slice($ord, 0, 2) === ['mobile_now', 'pesapal_now'] && in_array('bank', $ord, true), json_encode($ord));
Settings::setMany(['pay_rank_online' => '1', 'pay_rank_mobile' => '2']);
$ord = $fundMethods();
check('Fund a project: the order set in Payment options is respected (Pesapal moved first)', array_slice($ord, 0, 2) === ['pesapal_now', 'mobile_now'] && PaymentOptions::ranked()[0] === 'online', json_encode($ord));
Settings::set('pesapal_place_project', '0');
PesapalPlaces::forgetCache();
$ord = $fundMethods();
check('Fund a project: with Pesapal off only the first option and the earlier ways remain (nothing else changed)', in_array('mobile_now', $ord, true) && !in_array('pesapal_now', $ord, true) && in_array('bank', $ord, true), json_encode($ord));
Settings::set('pesapal_place_project', '1');
Database::execute("DELETE FROM settings WHERE setting_key IN ('pay_rank_mobile', 'pay_rank_online')");
Settings::flush();
check('default ranks: mobile money is the first way and Pesapal ("online") the second', PaymentOptions::ranked()[0] === 'mobile' && PaymentOptions::ranked()[1] === 'online');

// ---- "Mobile money only" hides Pesapal and says so ------------------------------------------------------------------------------------------------
PaymentOptions::save(['online' => false, 'link' => false, 'manual' => false, 'other' => false]);
PesapalPlaces::forgetCache();
$hidden = array_filter(array_keys(PesapalPlaces::PLACES), static fn(string $p): bool => PesapalPlaces::visible($p));
check('"Mobile money only": Pesapal is hidden at every point and the reason names Payment options', $hidden === [] && PesapalPlaces::onlineWayOff() && PesapalPlaces::mobileOnly() && str_contains(implode(' ', PesapalPlaces::problems('donate')), 'Mobile money only') && !PesapalPayments::ready());
check('"Mobile money only": the first option (mobile money) is still offered', \App\Services\DarazaPay\DarazaPayments::enabled('membership'));
$ord = $fundMethods();
check('"Mobile money only": Fund a project offers only the first option', $ord === ['mobile_now'] || (in_array('mobile_now', $ord, true) && !in_array('pesapal_now', $ord, true)), json_encode($ord));
PaymentOptions::save(['online' => false, 'link' => true, 'manual' => true, 'other' => true]);
check('only the online way off is not "Mobile money only" but still hides Pesapal', PesapalPlaces::onlineWayOff() && !PesapalPlaces::mobileOnly());
PaymentOptions::save(['online' => true, 'link' => true, 'manual' => true, 'other' => true]);
PesapalPlaces::forgetCache();

// ---- membership through Pesapal ---------------------------------------------------------------------------------------------------------------------------
$typeId = (int) Database::value('SELECT id FROM membership_types ORDER BY id LIMIT 1', [], 0);
$mkMember = static function (string $tag) use ($typeId): int {
    return Database::insert('members', ['application_ref' => 'LAF-APP-ZZPE-' . $tag, 'type_id' => $typeId, 'type_name' => 'Selftest', 'status' => 'approved', 'full_name' => 'Selftest Member ' . $tag, 'date_of_birth' => '1990-01-01',
        'email' => 'm-' . $tag . '@selftest-pe.invalid', 'phone' => '+256700000009', 'fee_amount' => '50000.00', 'fee_currency' => 'UGX', 'fee_period' => 'annual']);
};
$m1 = $mkMember('1');
$sum = \App\Services\Membership\MembershipService::paymentSummary($m1);
check('membership: the member owes the fee (balance 50,000 UGX)', (int) $sum['balance'] === 50000, json_encode([$sum['balance'], $sum['currency']]));
check('membership: "membership" is a Pesapal purpose and the ledger accepts it', in_array('membership', PesapalPayments::PURPOSES, true) && in_array('project', PesapalPayments::PURPOSES, true) && PesapalPlaces::purposeReady('membership'));
$r = PesapalPayments::begin('membership', $m1, 5000000, 'UGX', 'Membership fee', ['name' => 'Selftest Member 1', 'email' => 'm-1@selftest-pe.invalid', 'phone' => '+256700000009']);
$row = $r['ok'] ? PesapalPayments::byReference((string) $r['reference']) : null;
check('membership: a payment page link is returned and the ledger row is pending (reference LAFM...)', $r['ok'] === true && $row !== null && $row['purpose'] === 'membership' && $row['status'] === 'pending' && str_starts_with((string) $r['reference'], 'LAFM' . $m1 . '-'), json_encode($r));
$trk = (string) $row['order_tracking_id'];
$ref = (string) $row['merchant_reference'];
$answer(0, 0, 'UGX', $ref);
check('membership: still waiting at Pesapal -> pending, nothing recorded', PesapalPayments::settle($trk)['state'] === 'pending' && (int) Database::value('SELECT COUNT(*) FROM member_payments WHERE member_id = ?', [$m1], 0) === 0);
$answer(1, 49999, 'UGX', $ref);
check('membership: paid less than asked -> review, nothing recorded', PesapalPayments::settle($trk)['state'] === 'review' && (int) Database::value('SELECT COUNT(*) FROM member_payments WHERE member_id = ?', [$m1], 0) === 0);
$r = PesapalPayments::begin('membership', $m1, 5000000, 'UGX', 'Membership fee', ['name' => 'Selftest Member 1', 'email' => 'm-1@selftest-pe.invalid']);
$row = PesapalPayments::byReference((string) $r['reference']);
$trk = (string) $row['order_tracking_id'];
$ref = (string) $row['merchant_reference'];
$answer(1, 50000, 'UGX', 'LAFM999-OTHER');
check('membership: Pesapal answers for another reference -> review, nothing recorded', PesapalPayments::settle($trk)['state'] === 'review' && (int) Database::value('SELECT COUNT(*) FROM member_payments WHERE member_id = ?', [$m1], 0) === 0);
$answer(1, 50000, 'USD', $ref);
check('membership: wrong currency -> review', PesapalPayments::settle($trk)['state'] === 'review');
$answer(1, 50000, 'UGX', $ref);
$s = PesapalPayments::settle($trk);
$pay = Database::one('SELECT * FROM member_payments WHERE member_id = ?', [$m1]);
check('membership: completed -> paid, one "received" member payment (method pesapal, our reference, a receipt number)', $s['state'] === 'paid' && $pay !== null && $pay['status'] === 'received' && $pay['method'] === 'pesapal' && $pay['reference'] === $ref
    && (float) $pay['amount'] === 50000.0 && !empty($pay['receipt_number']), json_encode($pay));
$sum = \App\Services\Membership\MembershipService::paymentSummary($m1);
check('membership: the balance is cleared', (float) $sum['balance'] <= 0 && $sum['status'] === 'paid', json_encode([$sum['balance'], $sum['status']]));
PesapalPayments::settle($trk);
PesapalPayments::settle($trk);
check('membership: the same confirmation again (return page + IPN) records nothing twice (idempotent)', (int) Database::value('SELECT COUNT(*) FROM member_payments WHERE member_id = ?', [$m1], 0) === 1
    && (int) Database::value("SELECT COUNT(*) FROM pesapal_payments WHERE ref_id = ? AND purpose = 'membership' AND status = 'paid'", [$m1], 0) === 1);
check('membership: the audit trail has the Pesapal start and success lines', (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'success' AND category = 'membership' AND channel = 'online'", [$ref], 0) === 1
    && (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'started'", [$ref], 0) >= 1);
check('membership: the activity log says it was paid through Pesapal', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'membership.pesapal' AND entity_id = ?", [(string) $m1], 0) >= 1);
$before = (int) Database::value('SELECT COUNT(*) FROM member_payments WHERE member_id = ?', [$m1], 0);
\App\Services\Membership\MembershipService::recordPayment($m1, ['amount' => '1', 'currency' => 'UGX', 'status' => 'pending', 'method' => 'pesapal', 'purpose' => 'membership']);
check('membership: the manual payment form still works and "pesapal" is a valid method', (int) Database::value('SELECT COUNT(*) FROM member_payments WHERE member_id = ?', [$m1], 0) === $before + 1);
$bad = PesapalPayments::begin('membership', 0, 5000000, 'UGX', 'x', []);
check('membership: a payment without a member is refused', $bad['ok'] === false);
$mFail = $mkMember('2');
$r = PesapalPayments::begin('membership', $mFail, 5000000, 'UGX', 'Membership fee', ['email' => 'm-2@selftest-pe.invalid']);
$row = PesapalPayments::byReference((string) $r['reference']);
$answer(2, 0, 'UGX', (string) $row['merchant_reference']);
check('membership: a failed Pesapal payment records no money', PesapalPayments::settle((string) $row['order_tracking_id'])['state'] === 'failed' && (int) Database::value('SELECT COUNT(*) FROM member_payments WHERE member_id = ?', [$mFail], 0) === 0);

// ---- a gift to a project through Pesapal -------------------------------------------------------------------------------------------------------------------
$dp = \App\Models\Donation::create(['donor_name' => 'SELFTEST Project Donor', 'donor_email' => 'project@selftest-pe.invalid', 'amount' => '75000.00', 'currency' => 'UGX', 'method' => 'pesapal', 'status' => 'pledged', 'source' => 'pledge_form',
    'reference' => 'PROJECT:selftest|x']);
$r = PesapalPayments::begin('project', $dp, 7500000, 'UGX', 'Gift to a project', ['name' => 'SELFTEST Project Donor', 'email' => 'project@selftest-pe.invalid']);
$row = $r['ok'] ? PesapalPayments::byReference((string) $r['reference']) : null;
check('project: a payment page link is returned (reference LAFP...)', $r['ok'] === true && $row !== null && $row['purpose'] === 'project' && str_starts_with((string) $r['reference'], 'LAFP' . $dp . '-'));
$answer(1, 75000, 'UGX', (string) $row['merchant_reference']);
$s = PesapalPayments::settle((string) $row['order_tracking_id']);
$don = Database::one('SELECT * FROM donations WHERE id = ?', [$dp]);
check('project: completed -> the donation is received with a receipt number, method pesapal', $s['state'] === 'paid' && $don['status'] === 'received' && $don['method'] === 'pesapal' && str_starts_with((string) $don['receipt_no'], 'RCT-'), json_encode($don));
PesapalPayments::settle((string) $row['order_tracking_id']);
check('project: the same confirmation again changes nothing', Database::value('SELECT receipt_no FROM donations WHERE id = ?', [$dp]) === $don['receipt_no']);
$dp2 = \App\Models\Donation::create(['donor_name' => 'SELFTEST Project Donor', 'donor_email' => 'project2@selftest-pe.invalid', 'amount' => '5000.00', 'currency' => 'UGX', 'method' => 'pesapal', 'status' => 'pledged', 'source' => 'pledge_form']);
$r = PesapalPayments::begin('project', $dp2, 500000, 'UGX', 'Gift', ['email' => 'project2@selftest-pe.invalid']);
$row = PesapalPayments::byReference((string) $r['reference']);
$answer(2, 0, 'UGX', (string) $row['merchant_reference']);
PesapalPayments::settle((string) $row['order_tracking_id']);
check('project: a failed payment cancels the pledge, never "received"', Database::value('SELECT status FROM donations WHERE id = ?', [$dp2]) === 'cancelled');

// ---- the page after the Pesapal screen: a gift that did NOT go through offers the foundation's bank account (the card), and nothing else does ---------------------
// The controller runs here with the pretend Pesapal; the view is also rendered directly. Never while the payment is pending, under review or unconfirmed: a second way to pay next to
// "we are waiting for your payment" invites paying twice.
PaymentOptions::save(['manual' => true]);
$landing = static function (string $tracking): string {
    $req = new \App\Core\Request(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/pay/pesapal/return?OrderTrackingId=' . $tracking, 'HTTP_HOST' => 'localhost', 'SCRIPT_NAME' => '/index.php'], ['OrderTrackingId' => $tracking], [], []);
    return (new \App\Controllers\Web\PesapalController($req))->returned($req)->body();
};
$gift = static function (string $tag, string $purpose, string $reference): array {
    $id = \App\Models\Donation::create(['donor_name' => 'SELFTEST Landing ' . $tag, 'donor_email' => $tag . '@selftest-pe.invalid', 'amount' => '5000.00', 'currency' => 'UGX', 'method' => 'pesapal', 'status' => 'pledged', 'source' => 'pledge_form', 'reference' => $reference]);
    $r = PesapalPayments::begin($purpose, $id, 500000, 'UGX', 'Gift', ['name' => 'SELFTEST', 'email' => $tag . '@selftest-pe.invalid']);
    return ['id' => $id, 'row' => PesapalPayments::byReference((string) $r['reference'])];
};
$cardOn = static fn(string $html): bool => str_contains($html, 'data-bankcard');
$g = $gift('land-fail', 'project', 'PROJECT:digital-inclusion|item:laptopx3');
$answer(2, 0, 'UGX', (string) $g['row']['merchant_reference']);
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: a FAILED project gift shows the bank card with the account and the project name as the reference (never a DON- number: the pledge was cancelled)', str_contains($html, 'The payment did not go through') && $cardOn($html) && str_contains($html, '4090008000229') && str_contains($html, '<span class="bankcard__value" translate="no">Digital Inclusion</span>') && !str_contains($html, 'DON-') && str_contains($html, 'Nothing was charged'));
$g = $gift('land-rev', 'project', 'PROJECT:green-horizons|any');
$answer(3, 0, 'UGX', (string) $g['row']['merchant_reference']);
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: a REVERSED project gift shows the card too, without "Nothing was charged"', str_contains($html, 'The payment was reversed') && $cardOn($html) && str_contains($html, '<span class="bankcard__value" translate="no">Green Horizons</span>') && !str_contains($html, 'Nothing was charged. If you prefer'));
$g = $gift('land-don', 'donation', '');
$answer(2, 0, 'UGX', (string) $g['row']['merchant_reference']);
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: a failed plain DONATION shows the card with the account and no reference row', $cardOn($html) && str_contains($html, '4090008000229') && !str_contains($html, 'Payment reference'));
PaymentOptions::save(['manual' => false]);
$g = $gift('land-off', 'project', 'PROJECT:digital-inclusion|any');
$answer(2, 0, 'UGX', (string) $g['row']['merchant_reference']);
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: with "Bank and manual mobile-money details" switched off the failed gift shows no account', str_contains($html, 'The payment did not go through') && !$cardOn($html) && !str_contains($html, '4090008000229'));
PaymentOptions::save(['manual' => true]);
$g = $gift('land-pend', 'project', 'PROJECT:digital-inclusion|any');
$answer(0, 0, 'UGX', (string) $g['row']['merchant_reference']);
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: a PENDING gift shows no card (do not pay twice)', str_contains($html, 'We are waiting for your payment') && !$cardOn($html));
$mock['status'] = [];
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: when Pesapal cannot be reached (verify_failed) there is no card', str_contains($html, 'We could not confirm the payment yet') && !$cardOn($html));
$answer(1, 1, 'UGX', (string) $g['row']['merchant_reference']);
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: a payment that Pesapal confirms for the wrong amount goes to review and shows no card', str_contains($html, 'We are checking your payment') && !$cardOn($html));
$g = $gift('land-paid', 'project', 'PROJECT:digital-inclusion|any');
$answer(1, 5000, 'UGX', (string) $g['row']['merchant_reference']);
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: a PAID gift shows the thank-you and no card', str_contains($html, 'Thank you for your gift!') && !$cardOn($html) && !str_contains($html, '4090008000229'));
$g = $gift('land-cert', 'academy_cert', '');
$answer(2, 0, 'UGX', (string) $g['row']['merchant_reference']);
$html = $landing((string) $g['row']['order_tracking_id']);
check('landing page: an Academy certificate payment that failed is unchanged: no bank card', str_contains($html, 'The payment did not go through') && !$cardOn($html));
$viewRow = ['purpose' => 'project', 'ref_id' => 1, 'merchant_reference' => 'LAFP1-TEST'];
$render = static fn(string $state, ?string $bankRef): string => \App\Core\View::render('web/pay/pesapal-result', ['state' => $state, 'row' => $viewRow] + ($bankRef === null ? [] : ['bankRef' => $bankRef]), null);
check('view: failed and reversed with a bankRef show the card with the reference; the other states never do, even when a bankRef is passed', $cardOn($render('failed', 'Digital Inclusion')) && $cardOn($render('reversed', 'Digital Inclusion')) && str_contains($render('failed', 'Digital Inclusion'), 'Digital Inclusion</span>')
    && !$cardOn($render('failed', null)) && !array_filter(['paid', 'pending', 'review', 'verify_failed', 'unknown'], static fn(string $s): bool => $cardOn($render($s, 'Digital Inclusion'))));

// ---- the pages (web server) --------------------------------------------------------------------------------------------------------------------------
$base = 'http://localhost/' . basename(dirname(__DIR__));
$http = static function (string $method, string $path, array $form = [], string $jar = '') use ($base): array {
    $ch = curl_init($base . $path);
    $opt = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => false];
    if ($jar !== '') {
        $opt[CURLOPT_COOKIEJAR] = $jar;
        $opt[CURLOPT_COOKIEFILE] = $jar;
    }
    curl_setopt_array($ch, $opt);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs), 'head' => substr($raw, 0, $hs)];
};
if (($http('GET', '/donate')['code'] ?? 0) !== 200) {
    echo "  (web server not reachable: the page checks are skipped)\n";
} else {
    $r = $http('POST', '/pay/pesapal/ipn', ['OrderTrackingId' => 'trk-doesnotexist00000000', 'OrderMerchantReference' => 'LAFM1-NOPE']);
    $j = json_decode($r['body'], true);
    check('IPN: an unknown reference/tracking id (a forged notification) is refused with status 500 and changes nothing', $r['code'] === 500 && is_array($j) && $j['status'] === 500);
    $before = (int) Database::value("SELECT COUNT(*) FROM member_payments WHERE method = 'pesapal'", [], 0);
    $r = $http('GET', '/pay/pesapal/ipn?OrderTrackingId=' . $trk . '&OrderMerchantReference=LAFM1-FORGED&OrderNotificationType=IPNCHANGE');
    check('IPN: a notification is never believed: the answer comes from asking Pesapal (this web server has no keys, so nothing is recorded)', (int) Database::value("SELECT COUNT(*) FROM member_payments WHERE method = 'pesapal'", [], 0) === $before);
    $r = $http('POST', '/membership/me/' . str_repeat('a', 64) . '/pay-pesapal', ['x' => '1']);
    check('membership: the Pesapal pay address needs the CSRF token and a real private link (never a 200)', in_array($r['code'], [400, 403, 404, 419], true), (string) $r['code']);

    // admin: signed in as an administrator and as an editor
    $pw = 'Pesapal everywhere pass 77';
    $login = static function (string $email, string $role) use ($http, $pw): string {
        \App\Models\User::create(['name' => 'ZZ PE ' . $role, 'email' => $email, 'password_hash' => \App\Core\Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
        Database::execute('DELETE FROM rate_limits');
        $jar = tempnam(sys_get_temp_dir(), 'pej');
        $tok = static fn(string $h): string => preg_match('/name="_token" value="([^"]+)"/', $h, $m) === 1 ? $m[1] : '';
        $page = $http('GET', '/admin/login', [], $jar);
        $http('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar);
        return $jar;
    };
    $tok = static fn(string $h): string => preg_match('/name="_token" value="([^"]+)"/', $h, $m) === 1 ? $m[1] : '';
    $adminJar = $login('zz-pe-admin@example.test', 'admin');
    Settings::setMany(['pesapal_place_project' => '0', 'pesapal_place_membership' => '0', 'donate_method' => 'link', 'shop_pay_pesapal_enabled' => '0']);
    $page = $http('GET', '/admin/pesapal', [], $adminJar);
    check('admin: Admin > Pesapal shows the "Offer Pesapal everywhere (as the second option)" button and a row per payment point', $page['code'] === 200 && str_contains($page['body'], 'Offer Pesapal everywhere (as the second option)')
        && str_contains($page['body'], 'Fund a project') && str_contains($page['body'], 'Membership fee') && str_contains($page['body'], 'Academy certificate fee') && str_contains($page['body'], 'Hidden because'), (string) $page['code']);
    $r = $http('POST', '/admin/pesapal/everywhere', ['_token' => $tok($page['body'])], $adminJar);
    Settings::flush();
    check('admin: pressing the button ticks every place at once', in_array($r['code'], [302, 303], true) && PesapalPlaces::on('donate') && PesapalPlaces::on('project') && PesapalPlaces::on('shop') && PesapalPlaces::on('membership') && PesapalPlaces::on('academy_signup') && PesapalPlaces::on('academy_cert'), (string) $r['code']);
    check('admin: it is written in the activity log', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'pesapal.everywhere' AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)", [], 0) >= 1);
    $r = $http('POST', '/admin/pesapal/everywhere', [], $adminJar);
    check('admin: without the CSRF token the button does nothing (403)', $r['code'] === 403, (string) $r['code']);
    $page = $http('GET', '/admin/pesapal', [], $adminJar);
    check('admin: with the online way on there is no "Mobile money only" warning', !str_contains($page['body'], 'Mobile money only') || !str_contains($page['body'], 'is on, so Pesapal is hidden everywhere'));
    PaymentOptions::save(['online' => false, 'link' => false, 'manual' => false, 'other' => false]);
    $page = $http('GET', '/admin/pesapal', [], $adminJar);
    check('admin: "Mobile money only" shows a warning with a link to Payment options and a button to turn the online way back on', str_contains($page['body'], '"Mobile money only" is on, so Pesapal is hidden everywhere') && str_contains($page['body'], 'admin/daraza/api') && str_contains($page['body'], 'admin/pesapal/online-on'));
    check('admin: the warning does not change anything by itself (the online way is still off)', !PaymentOptions::online());
    $r = $http('POST', '/admin/pesapal/online-on', ['_token' => $tok($page['body'])], $adminJar);
    Settings::flush();
    check('admin: the button switches ONLY the online way back on (links, manual details and pay on delivery stay off)', in_array($r['code'], [302, 303], true) && PaymentOptions::online() && !PaymentOptions::link() && !PaymentOptions::manual() && !PaymentOptions::on('other'), (string) $r['code']);
    PaymentOptions::save(['online' => true, 'link' => true, 'manual' => true, 'other' => true]);
    @unlink($adminJar);

    // an editor can open the page (inbox.manage) but not press the site-wide switches
    $editorJar = $login('zz-pe-editor@example.test', 'editor');
    Settings::setMany(['pesapal_place_project' => '0']);
    $page = $http('GET', '/admin/pesapal', [], $editorJar);
    check('abilities: an editor sees the page but not the everywhere button', $page['code'] === 200 && !str_contains($page['body'], 'admin/pesapal/everywhere'), (string) $page['code']);
    $r = $http('POST', '/admin/pesapal/everywhere', ['_token' => $tok($page['body'])], $editorJar);
    Settings::flush();
    check('abilities: an editor pressing the everywhere switch is refused (403) and nothing changes', $r['code'] === 403 && !PesapalPlaces::on('project'), (string) $r['code']);
    $r = $http('POST', '/admin/pesapal/online-on', ['_token' => $tok($page['body'])], $editorJar);
    check('abilities: an editor cannot switch the online way back on either (403)', $r['code'] === 403, (string) $r['code']);
    @unlink($editorJar);
    $r = $http('POST', '/admin/pesapal/everywhere', ['x' => '1']);
    check('abilities: signed out, the switch is refused (redirect to login or 403)', in_array($r['code'], [301, 302, 303, 401, 403], true), (string) $r['code']);
}

// ---- clean up ---------------------------------------------------------------------------------------------------------------------------------------
$clean();
Database::execute("DELETE FROM payment_events WHERE reference LIKE 'LAFM%' OR reference LIKE 'LAFP%'");
Database::execute("DELETE FROM pesapal_payments WHERE merchant_reference LIKE 'LAFM%' OR merchant_reference LIKE 'LAFP%'");
Database::execute('DELETE FROM rate_limits');
$restore();
PesapalClient::$transport = null;
PesapalClient::resetToken();
putenv($envKey === false ? 'PESAPAL_CONSUMER_KEY' : 'PESAPAL_CONSUMER_KEY=' . $envKey);
putenv($envSecret === false ? 'PESAPAL_CONSUMER_SECRET' : 'PESAPAL_CONSUMER_SECRET=' . $envSecret);
putenv($envMode === false ? 'PESAPAL_ENV' : 'PESAPAL_ENV=' . $envMode);
putenv($envDaraza === false ? 'DARAZA_API_KEY' : 'DARAZA_API_KEY=' . $envDaraza);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL OK\n" : "\n{$fail} FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
