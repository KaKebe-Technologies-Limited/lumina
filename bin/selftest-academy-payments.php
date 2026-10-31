<?php
declare(strict_types=1);

/**
 * Admin > Academy > Payments: the events (Pesapal + confirmed by hand + reported), the figures, the filters and the CSV rows. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-payments.php
 * Removes its rows (zz-pay-*) and puts the fee settings back.
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
use App\Services\Academy\AcademyFees;
use App\Services\Academy\PaymentsReport;

echo "-- academy payments dashboard --\n";
$keys = ['academy_signup_fee', 'academy_signup_fee_currency', 'academy_cert_fee', 'academy_cert_fee_currency'];
$before = [];
foreach ($keys as $k) {
    $before[$k] = Settings::isCustomised($k) ? Settings::get($k) : null;
}
$clean = static function (): void {
    Database::execute("DELETE FROM pesapal_payments WHERE merchant_reference LIKE 'zz-pay-%'");
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Pay%'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-pay-%@example.test'");
};
$clean();
Settings::set('academy_signup_fee', '20000');
Settings::set('academy_signup_fee_currency', 'UGX');
Settings::set('academy_cert_fee', '15000');
Settings::set('academy_cert_fee_currency', 'UGX');

check('the dashboard has fees to show', AcademyFees::ready() && AcademyFees::configured('signup') !== null && AcademyFees::configured('cert') !== null);
$mk = static fn(string $n, string $status, ?string $paidAt, ?string $reportedAt = null, ?string $ref = null): int => (int) Database::insert('academy_learners', [
    'name' => 'ZZ Pay ' . $n, 'email' => 'zz-pay-' . strtolower($n) . '@example.test', 'password_hash' => 'x', 'is_active' => 1, 'fee_status' => $status, 'fee_paid_at' => $paidAt, 'fee_reported_at' => $reportedAt, 'fee_reference' => $ref,
]);
$now = date('Y-m-d H:i:s');
$old = date('Y-m-d H:i:s', strtotime('-40 days'));
$a = $mk('Ann', 'paid', $now);                                   // paid with Pesapal (row below)
$b = $mk('Ben', 'paid', $now, null, 'MP123456');                 // confirmed by hand
$c = $mk('Cy', 'due', null, $now, 'TX9988');                     // reported, waiting
$d = $mk('Di', 'due', null);                                     // owes, nothing reported
$e = $mk('Ed', 'paid', $old);                                    // confirmed by hand long ago
$f2 = $mk('Fay', 'waived', null);
Database::insert('pesapal_payments', ['merchant_reference' => 'zz-pay-1', 'order_tracking_id' => 'zz-pay-trk-1', 'purpose' => 'academy_signup', 'ref_id' => $a, 'amount_minor' => 2000000, 'currency' => 'UGX', 'status' => 'paid', 'pay_method' => 'Airtel Money', 'confirmation' => 'CONF-1', 'paid_at' => $now]);
Database::insert('pesapal_payments', ['merchant_reference' => 'zz-pay-2', 'order_tracking_id' => 'zz-pay-trk-2', 'purpose' => 'academy_signup', 'ref_id' => $d, 'amount_minor' => 2000000, 'currency' => 'UGX', 'status' => 'pending']);
$cid = (int) Database::insert('academy_certificates', ['code' => 'ZZPAY0001', 'learner_id' => $b, 'learner_name' => 'ZZ Pay Ben', 'course_title' => 'ZZ Course', 'fee_status' => 'paid', 'fee_paid_at' => $now]);

$all = PaymentsReport::events(PaymentsReport::filters('30', 'all', 'all'), 500);
$mine = array_values(array_filter($all, static fn(array $x): bool => str_starts_with((string) $x['learner'], 'ZZ Pay')));
$by = static function (array $rows, string $key): array { return array_values(array_filter($rows, static fn(array $x): bool => $x['key'] === $key)); };
check('a Pesapal payment is listed with its real amount, method and confirmation code', count($pp = array_filter($mine, static fn(array $x): bool => $x['source'] === 'pesapal' && $x['status'] === 'received')) === 1 && ($pr = array_values($pp)[0])['minor'] === 2000000 && str_contains($pr['method'], 'Airtel Money') && $pr['reference'] === 'CONF-1' && $pr['estimated'] === false);
check('a payment confirmed by hand is listed at the current fee and marked as an estimate', count($bm = array_filter($mine, static fn(array $x): bool => $x['key'] === 'ms' . $b)) === 1 && array_values($bm)[0]['minor'] === 2000000 && array_values($bm)[0]['estimated'] === true && array_values($bm)[0]['reference'] === 'MP123456');
check('the Pesapal sign-up is not counted a second time as a hand-confirmed one', $by($mine, 'ms' . $a) === []);
check('a certificate fee confirmed by hand is listed with its course and the certificate fee', count($cm = array_filter($mine, static fn(array $x): bool => $x['type'] === 'cert')) === 1 && array_values($cm)[0]['item'] === 'ZZ Course' && array_values($cm)[0]['minor'] === 1500000);
check('a reported payment waits for you and can be marked paid', count($rp = array_filter($mine, static fn(array $x): bool => $x['status'] === 'reported')) === 1 && array_values($rp)[0]['mark'] === $c && array_values($rp)[0]['reference'] === 'TX9988');
check('a payment started and not finished is listed as such', count(array_filter($mine, static fn(array $x): bool => $x['status'] === 'pending')) === 1);
check('a payment made 40 days ago is outside the 30-day period but inside "all time"', $by($mine, 'ms' . $e) === [] && $by(PaymentsReport::events(PaymentsReport::filters('all', 'all', 'all'), 500), 'ms' . $e) !== []);
check('a waived fee is not a payment', array_filter($mine, static fn(array $x): bool => $x['learner_id'] === $f2) === []);

$sum = PaymentsReport::summary($mine);
check('the figures add the received payments and count what is waiting', $sum['received'] === 3 && ($sum['amounts']['UGX'] ?? 0) === 2000000 + 2000000 + 1500000 && $sum['estimated'] === true && $sum['reported'] === 1 && $sum['pending'] === 1);
$onlyCert = PaymentsReport::events(PaymentsReport::filters('30', 'cert', 'all'), 500);
check('the "certificate fees" filter shows only certificates', $onlyCert !== [] && count(array_filter($onlyCert, static fn(array $x): bool => $x['type'] !== 'cert')) === 0);
$onlyRep = PaymentsReport::events(PaymentsReport::filters('30', 'all', 'reported'), 500);
check('the "reported" filter shows only reported payments', $onlyRep !== [] && count(array_filter($onlyRep, static fn(array $x): bool => $x['status'] !== 'reported')) === 0);
check('the daily series has one entry per day, with today counted', count(PaymentsReport::daily($mine, PaymentsReport::filters('7', 'all', 'all'))) === 7 && PaymentsReport::daily($mine, PaymentsReport::filters('7', 'all', 'all'))[6]['n'] >= 3);
check('a bad filter falls back safely', PaymentsReport::filters('x', 'y', 'z') === ['period' => '30', 'type' => 'all', 'status' => 'all']);

// ---- acknowledge the payment and welcome (portal message; the body starts with a fixed phrase that also marks "already welcomed")
$notW = array_values(array_filter(AcademyFees::paidLearners(true), static fn(array $r): bool => in_array((int) $r['id'], [$a, $b, $e], true)));
check('learners who paid and were not welcomed yet are listed', count($notW) === 3);
$wt = AcademyFees::welcomeText((array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$b]));
check('the welcome thanks for the payment, welcomes to the class and says where to ask for support', str_starts_with($wt, AcademyFees::WELCOME_MARK) && str_contains($wt, 'UGX 20,000') && str_contains($wt, 'Welcome to the class') && str_contains($wt, 'any question or need any support') && str_contains($wt, 'academy/login'));
check('"acknowledge" is refused for a learner who has not paid', AcademyFees::acknowledge($d, null, false) === false && AcademyFees::acknowledge($c, null, false) === false);
check('"acknowledge" sends the message to the learner\'s portal', AcademyFees::acknowledge($b, null, false) === true && (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'admin' AND body LIKE ?", [$b, AcademyFees::WELCOME_MARK . '%'], 0) === 1);
check('a welcomed learner leaves the "not welcomed" list', count(array_filter(AcademyFees::paidLearners(true), static fn(array $r): bool => (int) $r['id'] === $b)) === 0 && count(array_filter(AcademyFees::paidLearners(false), static fn(array $r): bool => (int) $r['id'] === $b && (int) $r['welcomed'] === 1)) === 1);
$sentAll = AcademyFees::acknowledgeAll(null, false);
check('"welcome all" reaches the others who paid, once each', $sentAll >= 2 && (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'admin' AND body LIKE ?", [$a, AcademyFees::WELCOME_MARK . '%'], 0) === 1 && AcademyFees::acknowledgeAll(null, false) === 0);
check('the WhatsApp welcome link carries the typed message', str_contains((string) AcademyFees::welcomeWhatsAppUrl(['name' => 'ZZ Pay Ann', 'phone' => '+256 700 000 000', 'whatsapp' => '+256700000000']), 'https://wa.me/256700000000?text='));
Database::execute("DELETE FROM academy_messages WHERE learner_id IN (?, ?, ?)", [$a, $b, $e]);

$clean();
foreach ($keys as $k) {
    if ($before[$k] === null) {
        Database::execute('DELETE FROM settings WHERE setting_key = ?', [$k]);
    } else {
        Settings::set($k, (string) $before[$k]);
    }
}
check('cleanup: no test payment rows are left', (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-pay-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
