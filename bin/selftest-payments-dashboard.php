<?php
declare(strict_types=1);

/**
 * PAYMENTS DASHBOARD: one list of every payment (mobile money, Pesapal, manual), the totals, filters, CSV and Send money.
 * Run alone: /c/xampp/php/php.exe bin/selftest-payments-dashboard.php   (needs the local web server and APP_ENV=local)
 */

use App\Core\Database;
use App\Services\DarazaPay\DarazaApi;
use App\Services\DarazaPay\DarazaPayments;
use App\Services\Payments\PaymentEvents;
use App\Services\Payments\PaymentThanks;
use App\Services\Payments\PaymentsDashboard;
use App\Core\Settings;

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

echo "-- payments dashboard --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM daraza_payments WHERE reference LIKE 'ZZPD-%' OR note = 'Zz prize' OR payer_email LIKE 'zz-pd-%@example.test'");
    Database::execute("DELETE FROM pesapal_payments WHERE merchant_reference LIKE 'ZZPD-%'");
    Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-pd-%@example.test'");
    Database::execute("DELETE FROM users WHERE email = 'zz-pd-admin@example.test'");
    Database::execute("DELETE FROM payment_events WHERE who LIKE '%zz-pd-%' OR reference LIKE 'ZZPD-%' OR detail LIKE '%ZZPD%' OR who LIKE 'Zz Pd%'");
    Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-pd-%@example.test'");
    DarazaApi::$transport = null;
    putenv('DARAZA_API_KEY');
    putenv('DARAZA_ENV');
};
$clean();
DarazaPayments::install();
$now = date('Y-m-d H:i:s');
$old = date('Y-m-d H:i:s', strtotime('-200 days'));
Database::insert('daraza_payments', ['reference' => 'ZZPD-IN1', 'direction' => 'in', 'purpose' => 'shop', 'ref_id' => 1, 'amount_minor' => 5000000, 'currency' => 'UGX', 'amount_ugx' => 50000, 'phone' => '+256772000111', 'method' => 1, 'status' => 'paid', 'mode' => 'sandbox', 'payer_name' => 'Zz Payer', 'paid_at' => $now]);
Database::insert('daraza_payments', ['reference' => 'ZZPD-IN2', 'direction' => 'in', 'purpose' => 'donation', 'ref_id' => 1, 'amount_minor' => 1000000, 'currency' => 'UGX', 'amount_ugx' => 10000, 'phone' => '+256702000111', 'method' => 2, 'status' => 'collecting', 'mode' => 'sandbox', 'payer_name' => 'Zz Waiting']);
Database::insert('daraza_payments', ['reference' => 'ZZPD-OUT1', 'direction' => 'out', 'purpose' => 'payout', 'ref_id' => 0, 'amount_ugx' => 20000, 'currency' => 'UGX', 'phone' => '+256772000222', 'method' => 1, 'status' => 'paid', 'mode' => 'sandbox', 'note' => 'Zz refund', 'paid_at' => $now]);
Database::insert('daraza_payments', ['reference' => 'ZZPD-IN3', 'direction' => 'in', 'purpose' => 'shop', 'ref_id' => 2, 'amount_minor' => 7000000, 'currency' => 'UGX', 'amount_ugx' => 70000, 'phone' => '+256772000333', 'method' => 1, 'status' => 'paid', 'mode' => 'sandbox', 'payer_name' => 'Zz Old', 'paid_at' => $old, 'created_at' => $old]);
Database::insert('pesapal_payments', ['merchant_reference' => 'ZZPD-PP1', 'purpose' => 'donation', 'ref_id' => 1, 'amount_minor' => 3000000, 'currency' => 'UGX', 'status' => 'paid', 'mode' => 'sandbox', 'payer_name' => 'Zz Card', 'paid_at' => $now]);
Database::insert('pesapal_payments', ['merchant_reference' => 'ZZPD-PP2', 'purpose' => 'donation', 'ref_id' => 2, 'amount_minor' => 100000, 'currency' => 'USD', 'status' => 'paid', 'mode' => 'sandbox', 'payer_name' => 'Zz Dollar', 'paid_at' => $now]);
Database::execute("INSERT INTO donations (donor_name, donor_email, amount, currency, method, status, source, received_at) VALUES ('Zz Bank','zz-pd-1@example.test',15000,'UGX','bank','received','manual','{$now}'), ('Zz Dup','zz-pd-2@example.test',99999,'UGX','mobile_money','received','pledge_form','{$now}'), ('Zz Pledge','zz-pd-3@example.test',88888,'UGX','bank','pledged','pledge_form',NULL)");

$s = PaymentsDashboard::summary('30');
check('money in (30 days): mobile 50,000 + card 30,000 + bank donation 15,000 = UGX 95,000 (old, waiting, pledge and duplicate are not counted)', $s['in'] === 9500000, (string) $s['in']);
check('money out: the payout of 20,000; net 75,000', $s['out'] === 2000000 && $s['net'] === 7500000, $s['out'] . '/' . $s['net']);
check('by channel: mobile 50,000, online 30,000, manual 15,000', $s['by_channel'] === ['mobile' => 5000000, 'online' => 3000000, 'manual' => 1500000], json_encode($s['by_channel']));
check('a dollar payment is listed apart (not added to UGX)', ($s['other']['USD'] ?? 0) === 100000);
check('the waiting count includes the collecting payment', $s['waiting'] >= 1);
check('the all-time range includes the old payment (UGX 165,000)', PaymentsDashboard::summary('all')['in'] === 16500000);
check('the 14-day bars have 14 days and today holds the money', count($s['days']) === 14 && $s['days'][13]['amt'] === 9500000);

$all = PaymentsDashboard::rows(['range' => 'all', 'q' => 'ZZPD'], 50, 0);
check('the list shows each payment once: 4 mobile-money + 2 card', $all['total'] === 6 && count($all['rows']) === 6, (string) $all['total']);
check('filter: money out only', PaymentsDashboard::rows(['range' => '30', 'dir' => 'out', 'q' => 'ZZPD'], 50, 0)['total'] === 1);
check('filter: waiting only', array_column(PaymentsDashboard::rows(['range' => '30', 'status' => 'pending', 'q' => 'ZZPD'], 50, 0)['rows'], 'ref') === ['ZZPD-IN2']);
check('filter: card channel', PaymentsDashboard::rows(['range' => '30', 'channel' => 'online', 'q' => 'ZZPD'], 50, 0)['total'] === 2);
check('search finds the manual donation by the donor name', PaymentsDashboard::rows(['range' => 'all', 'q' => 'Zz Bank'], 50, 0)['total'] === 1);
check('the list says what it paid for and labels a payout as money sent', in_array('Shop orders', array_column($all['rows'], 'what'), true) && in_array('Payout (money sent)', array_column($all['rows'], 'what'), true));
check('recent recipients lists the payout number', in_array('+256772000222', array_column(PaymentsDashboard::recentRecipients(), 'phone'), true));

// ---- the payout through the service (a fake Daraza answers)
putenv('DARAZA_API_KEY=zz-test-key');
putenv('DARAZA_ENV=sandbox');
$sent = [];
DarazaApi::$transport = static function (string $method, string $url, array $headers, ?string $body) use (&$sent): array {
    $sent[] = ['url' => $url, 'body' => $body === null ? null : json_decode($body, true)];
    return ['http' => 201, 'raw' => json_encode(['code' => 'Success', 'message' => 'Sent', 'request_id' => 'REQ-PD-1'])];
};
$pr = DarazaPayments::payout(null, '0752 555 666', 0, 12000, 'Zz prize');
check('send money: an Airtel number is detected, UGX 12,000 goes out and shows in the dashboard', $pr['ok'] && end($sent)['body']['method'] === 2 && (int) end($sent)['body']['amount'] === 12000
    && PaymentsDashboard::rows(['range' => '30', 'dir' => 'out', 'q' => 'Zz prize'], 10, 0)['total'] === 1, json_encode($pr['message'] ?? ''));
DarazaApi::$transport = null;
putenv('DARAZA_API_KEY');

// ---- the Daraza Pay collections shown on the main dashboard
$dz = PaymentsDashboard::darazaCollections('all', 50);
$zrows = array_values(array_filter($dz['rows'], static fn(array $r): bool => str_starts_with((string) $r['reference'], 'ZZPD-')));
check('daraza collections: only mobile-money money IN is listed (the payout is not), with who paid, country and category', count($zrows) === 3 && !in_array('ZZPD-OUT1', array_column($zrows, 'reference'), true));
$byRef = array_column($zrows, null, 'reference');
check('daraza collections: the country comes from the phone (+256 = Uganda) and the category from the purpose', ($byRef['ZZPD-IN1']['country'] ?? '') === 'Uganda' && ($byRef['ZZPD-IN1']['category'] ?? '') === 'shop' && ($byRef['ZZPD-IN2']['category'] ?? '') === 'donations' && ($byRef['ZZPD-IN1']['who'] ?? '') === 'Zz Payer');
check('daraza collections: the totals split paid, waiting and failed, and MTN from Airtel', $dz['paid'] >= 2 && $dz['waiting'] >= 1 && $dz['paid_ugx'] >= 120000 && $dz['mtn'] >= 120000);
check('country of a number: Uganda, Kenya, Tanzania; nothing for an empty number', PaymentsDashboard::countryOfPhone('+256772123456') === 'Uganda' && PaymentsDashboard::countryOfPhone('254712345678') === 'Kenya' && PaymentsDashboard::countryOfPhone('+255 712 345 678') === 'Tanzania' && PaymentsDashboard::countryOfPhone('') === '');

// ---- the audit trail and the thank-you e-mails
PaymentEvents::install();
$prevSwitch = Settings::get('daraza_api_donation_on', '');
Settings::setMany(['daraza_api_donation_on' => '1']);
Settings::flush();
putenv('DARAZA_API_KEY=zz-test-key');
putenv('DARAZA_ENV=sandbox');
$mailDir = STORAGE_PATH . '/mail';
$mailBefore = glob($mailDir . '/*.eml') ?: [];
$mailText = static function (array $before) use ($mailDir): string {
    $new = array_diff(glob($mailDir . '/*.eml') ?: [], $before);
    $t = '';
    foreach ($new as $f) {
        $raw = (string) file_get_contents($f);
        $t .= $raw . "\n";
        if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n([A-Za-z0-9+\/=\r\n]+)/', $raw, $mm)) {
            foreach ($mm[1] as $b64) {
                $t .= (string) base64_decode(preg_replace('/\s+/', '', $b64) ?? '') . "\n";
            }
        }
    }
    return $t;
};
$answer = ['http' => 201, 'raw' => json_encode(['code' => 'Success', 'message' => 'Payment Successful', 'request_id' => 'REQ-PD-T1'])];
DarazaApi::$transport = static function (string $method, string $url, array $headers, ?string $body) use (&$answer): array {
    return $answer;
};
Database::execute("INSERT INTO donations (donor_name, donor_email, amount, currency, method, status, source) VALUES ('Zz Pd Giver','zz-pd-t1@example.test',20000,'UGX','mobile_money','pledged','online')");
$did = (int) Database::value('SELECT LAST_INSERT_ID()');
$b = DarazaPayments::begin('donation', $did, 2000000, 'UGX', ['name' => 'Zz Pd Giver', 'email' => 'zz-pd-t1@example.test']);
$ref = (string) $b['reference'];
$ev = static fn(string $e): int => (int) Database::value('SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = ?', [$ref, $e], 0);
check('audit: opening a payment page is written down as "started"', $ev('started') === 1);
DarazaPayments::collect($ref, '0772123456', 0);
check('audit: the request to the phone and the success are written down (the phone number is hidden)', $ev('requested') === 1 && $ev('success') === 1 && !str_contains((string) Database::value("SELECT detail FROM payment_events WHERE reference = ? AND event = 'requested'", [$ref], ''), '772123456'));
check('audit: the thank-you is recorded as sent exactly once', $ev('thanks') === 1);
$mailTxt = $mailText($mailBefore);
check('thank-you: the payer gets an e-mail that thanks them, with the amount and the reference', str_contains($mailTxt, 'zz-pd-t1@example.test') && str_contains($mailTxt, 'Thank you') && str_contains($mailTxt, 'UGX 20,000') && str_contains($mailTxt, $ref));
$count = count(glob($mailDir . '/*.eml') ?: []);
PaymentThanks::send('donations', 'mobile', 'zz-pd-t1@example.test', 'Zz Pd Giver', 2000000, 'UGX', $ref);
check('thank-you: a second call for the same payment sends nothing (no duplicate e-mail)', count(glob($mailDir . '/*.eml') ?: []) === $count);

// an unsuccessful attempt
Database::execute("INSERT INTO donations (donor_name, donor_email, amount, currency, method, status, source) VALUES ('Zz Pd Fail','zz-pd-t2@example.test',10000,'UGX','mobile_money','pledged','online')");
$did2 = (int) Database::value('SELECT LAST_INSERT_ID()');
$b2 = DarazaPayments::begin('donation', $did2, 1000000, 'UGX', ['name' => 'Zz Pd Fail', 'email' => 'zz-pd-t2@example.test']);
$ref2 = (string) $b2['reference'];
$answer = ['http' => 200, 'raw' => json_encode(['code' => 'Error', 'message' => 'Insufficient funds', 'details' => 'Insufficient funds'])];
$mailBefore2 = glob($mailDir . '/*.eml') ?: [];
DarazaPayments::collect($ref2, '0702000999', 0);
check('audit: a refused payment is written down as "failed" with the reason, and no thank-you is sent', (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'failed'", [$ref2], 0) === 1
    && (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'thanks'", [$ref2], 0) === 0 && !str_contains($mailText($mailBefore2), 'zz-pd-t2@example.test'));
DarazaApi::$transport = null;

// staff record a bank donation and a project gift
Database::execute("INSERT INTO donations (donor_name, donor_email, amount, currency, method, status, source, reference, received_at, receipt_no) VALUES ('Zz Pd Bank','zz-pd-t3@example.test',45000,'UGX','bank','received','manual','PROJECT:digital-inclusion|package:year1',NOW(),'RCT-ZZPD-1')");
$mailBefore3 = glob($mailDir . '/*.eml') ?: [];
PaymentThanks::donationReceived((int) Database::value('SELECT LAST_INSERT_ID()'));
check('audit: a gift recorded by staff is written down under "Projects" and the donor is thanked, with the receipt number', (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = 'RCT-ZZPD-1' AND event = 'success' AND category = 'projects' AND channel = 'manual'", [], 0) === 1 && str_contains($mailText($mailBefore3), 'RCT-ZZPD-1'));
$cnt = PaymentEvents::counts('30');
check('audit: the counts show attempts, successes and failures', $cnt['started'] >= 2 && $cnt['success'] >= 2 && $cnt['failed'] >= 1 && $cnt['rate'] > 0 && $cnt['rate'] < 100, json_encode($cnt));
check('audit: filter by group and category', PaymentEvents::rows(['range' => 'all', 'group' => 'failure', 'q' => 'Zz Pd'], 20, 0)['total'] >= 1 && PaymentEvents::rows(['range' => 'all', 'group' => 'success', 'category' => 'projects', 'q' => 'RCT-ZZPD'], 20, 0)['total'] === 1);
$cat = PaymentsDashboard::summary('30');
check('categories: donations, projects and shop are totalled apart (the project gift of 45,000 is in "projects")', ($cat['by_cat']['projects']['amt'] ?? 0) >= 4500000 && ($cat['by_cat']['donations']['amt'] ?? 0) >= 2000000);
check('categories: the payments list can be filtered by category', PaymentsDashboard::rows(['range' => '30', 'category' => 'projects', 'q' => 'RCT-ZZPD'], 10, 0)['total'] === 1);
// ---- a payment that stayed "waiting" although the payer paid: staff confirm it, or close it
Settings::setMany(['daraza_api_donation_on' => '1']);
Settings::flush();
putenv('DARAZA_API_KEY=zz-test-key');
Database::execute("INSERT INTO donations (donor_name, donor_email, amount, currency, method, status, source) VALUES ('Zz Pd Lost','zz-pd-lost@example.test',5000,'UGX','mobile_money','pledged','online')");
$lostDon = (int) Database::value('SELECT LAST_INSERT_ID()');
$lost = DarazaPayments::begin('donation', $lostDon, 500000, 'UGX', ['name' => 'Zz Pd Lost', 'email' => 'zz-pd-lost@example.test']);
$lostRow = DarazaPayments::byReference((string) $lost['reference']);
Database::execute("UPDATE daraza_payments SET status = 'unknown', message = 'Daraza did not answer in time' WHERE id = ?", [(int) $lostRow['id']]);
$lostCheck = DarazaPayments::check((int) $lostRow['id']);
check('lost answer: "check again" says plainly that Daraza gave no request number, so it cannot look it up', $lostCheck['state'] === 'unknown' && str_contains($lostCheck['message'], 'request number'));
$mailBeforeL = glob($mailDir . '/*.eml') ?: [];
$cf = DarazaPayments::confirmManually((int) $lostRow['id'], null, 'seen in the wallet');
$after = DarazaPayments::byReference((string) $lost['reference']);
check('lost answer: staff can confirm it: it becomes paid, the donation is received, and the payer is thanked', $cf['ok'] && $after['status'] === 'paid' && Database::value('SELECT status FROM donations WHERE id = ?', [$lostDon]) === 'received' && str_contains($mailText($mailBeforeL), 'zz-pd-lost@example.test'));
check('lost answer: the audit trail says it was confirmed by staff', str_contains((string) Database::value("SELECT detail FROM payment_events WHERE reference = ? AND event = 'success'", [(string) $lost['reference']], ''), 'Confirmed by staff'));
check('lost answer: confirming twice changes nothing', DarazaPayments::confirmManually((int) $lostRow['id'], null)['ok'] && (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'success'", [(string) $lost['reference']], 0) === 1);
Database::execute("INSERT INTO donations (donor_name, donor_email, amount, currency, method, status, source) VALUES ('Zz Pd Never','zz-pd-never@example.test',7000,'UGX','mobile_money','pledged','online')");
$nev = DarazaPayments::begin('donation', (int) Database::value('SELECT LAST_INSERT_ID()'), 700000, 'UGX', ['name' => 'Zz Pd Never', 'email' => 'zz-pd-never@example.test']);
$nevRow = DarazaPayments::byReference((string) $nev['reference']);
$cl = DarazaPayments::closeAsNotReceived((int) $nevRow['id'], null);
check('lost answer: staff can close a payment that never arrived (it stops showing as waiting, no thank-you)', $cl['ok'] && DarazaPayments::byReference((string) $nev['reference'])['status'] === 'failed' && (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'thanks'", [(string) $nev['reference']], 0) === 0);
Settings::setMany(['daraza_api_donation_on' => (string) $prevSwitch]);
Settings::flush();
putenv('DARAZA_API_KEY');

Settings::setMany(['daraza_api_donation_on' => (string) $prevSwitch]);
Settings::flush();
putenv('DARAZA_API_KEY');

// ---- the pages
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
$pw = 'Payments dash pass 83';
\App\Models\User::create(['name' => 'ZZ PD Admin', 'email' => 'zz-pd-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($pw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$ja = tempnam(sys_get_temp_dir(), 'pda');
check('the page needs a sign-in', $req('/admin/payments', null, tempnam(sys_get_temp_dir(), 'pdx'))['code'] === 302);
$lp = $req('/admin/login', null, $ja);
$req('/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-pd-admin@example.test', 'password' => $pw], $ja);
$pg = $req('/admin/payments?range=30', null, $ja);
check('admin: the dashboard shows the totals, how people paid, Send money and the list', $pg['code'] === 200 && str_contains($pg['body'], 'Money in') && str_contains($pg['body'], 'UGX 165,000') && str_contains($pg['body'], 'Send money to mobile money')
    && str_contains($pg['body'], 'How people paid') && str_contains($pg['body'], 'Zz Payer') && str_contains($pg['body'], 'Shop orders'), (string) $pg['code']);
$out = $req('/admin/payments?range=30&dir=out&q=ZZPD', null, $ja);
check('admin: filters work in the address (money out only)', str_contains($out['body'], 'ZZPD-OUT1') && !str_contains($out['body'], 'ZZPD-IN1'));
check('admin: "send again" pre-fills the number', str_contains($req('/admin/payments?to=0772000222&network=1', null, $ja)['body'], 'value="0772000222"'));
$ex = $req('/admin/payments/export?range=30&q=ZZPD', null, $ja);
check('admin: the CSV download has the rows', $ex['code'] === 200 && str_contains($ex['body'], 'ZZPD-IN1') && str_contains($ex['body'], 'Money out'));
$pg2 = $req('/admin/payments', null, $ja);
$po = $req('/admin/payments/payout', ['_token' => $tok($pg2['body']), 'phone' => '0772000999', 'method' => '0', 'amount' => '5000'], $ja);
check('admin: sending money without ticking the box does nothing (back to the dashboard)', $po['code'] === 302 && (int) Database::value("SELECT COUNT(*) FROM daraza_payments WHERE direction = 'out' AND phone = '+256772000999'", [], 0) === 0);
check('the Payment options page still works', $req('/admin/daraza/api', null, $ja)['code'] === 200);
$au = $req('/admin/payments/audit?range=30&q=Zz+Pd', null, $ja);
check('admin: the audit trail page lists the steps (started, successful, unsuccessful) with a filter', $au['code'] === 200 && str_contains($au['body'], 'Payment started') && str_contains($au['body'], 'Successful') && str_contains($au['body'], 'Unsuccessful') && str_contains($au['body'], 'Zz Pd Giver'), (string) $au['code']);
$dh = $req('/admin?range=30', null, $ja);
check('admin: the dashboard shows the money rings, the activity feed and the round attention tiles', $dh['code'] === 200 && str_contains($dh['body'], 'Money collected') && str_contains($dh['body'], 'dash-ring') && str_contains($dh['body'], 'Latest payment activity') && str_contains($dh['body'], 'Needs your attention') && str_contains($dh['body'], 'class="orb'), (string) $dh['code']);
check('admin: the dashboard lists the Daraza Pay collections with who paid, category, country and time', str_contains($dh['body'], 'Daraza Pay collections') && str_contains($dh['body'], 'Zz Payer') && str_contains($dh['body'], 'Uganda') && str_contains($dh['body'], 'data-dz-group="paid"'));
check('admin: the payments page can be filtered by category in the address', $req('/admin/payments?range=30&category=projects&q=RCT-ZZPD', null, $ja)['code'] === 200);

@unlink($ja);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
