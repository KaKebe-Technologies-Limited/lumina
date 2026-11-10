<?php
declare(strict_types=1);

/**
 * PAYMENTS DASHBOARD: one list of every payment (mobile money, Pesapal, manual), the totals, filters, CSV and Send money.
 * Run alone: /c/xampp/php/php.exe bin/selftest-payments-dashboard.php   (needs the local web server and APP_ENV=local)
 */

use App\Core\Database;
use App\Services\DarazaPay\DarazaApi;
use App\Services\DarazaPay\DarazaPayments;
use App\Services\Payments\PaymentsDashboard;

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
    Database::execute("DELETE FROM daraza_payments WHERE reference LIKE 'ZZPD-%' OR note = 'Zz prize'");
    Database::execute("DELETE FROM pesapal_payments WHERE merchant_reference LIKE 'ZZPD-%'");
    Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-pd-%@example.test'");
    Database::execute("DELETE FROM users WHERE email = 'zz-pd-admin@example.test'");
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
check('admin: the dashboard shows the totals, how people paid, Send money and the list', $pg['code'] === 200 && str_contains($pg['body'], 'Money in') && str_contains($pg['body'], 'UGX 95,000') && str_contains($pg['body'], 'Send money to mobile money')
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

@unlink($ja);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
