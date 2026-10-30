<?php
declare(strict_types=1);

/**
 * Academy sign-up payment is ENFORCED: pay with mobile money / bank (details set in Settings > LAF Academy), Pesapal or Daraza; the applicant reports a manual payment
 * (transaction ID + optional receipt) and the account can only be approved after an administrator confirms the payment. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-fee-enforce.php
 * Needs the local web server. Removes its rows (zz-fe-*) and puts the settings back.
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
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerRegistry;

set_time_limit(0); // the sign-up form waits for the anti-bot delay several times
echo "-- academy sign-up payment enforced --\n";

$keys = ['academy_signup_fee', 'academy_signup_fee_currency', 'academy_pay_mobile_money', 'academy_pay_bank_details', 'daraza_signup_on', 'daraza_url', 'academy_signup_mode'];
$before = [];
foreach ($keys as $k) {
    $before[$k] = Settings::isCustomised($k) ? Settings::get($k) : null;
}
$envKeys = [getenv('PESAPAL_CONSUMER_KEY'), getenv('PESAPAL_CONSUMER_SECRET')];
putenv('PESAPAL_CONSUMER_KEY');
putenv('PESAPAL_CONSUMER_SECRET');
$proofFiles = [];
$clean = static function () use (&$proofFiles): void {
    foreach (Database::all("SELECT fee_proof_path FROM academy_learners WHERE email LIKE 'zz-fe-%@example.test' AND fee_proof_path IS NOT NULL") as $r) {
        @unlink(STORAGE_PATH . '/private/' . $r['fee_proof_path']);
    }
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-fe-%@example.test'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-fe-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
Settings::setMany(['academy_signup_fee' => '20000', 'academy_signup_fee_currency' => 'UGX', 'academy_pay_mobile_money' => '', 'academy_pay_bank_details' => '', 'daraza_signup_on' => '0', 'daraza_url' => '', 'academy_signup_mode' => 'approval']);

check('the new columns for a reported payment exist', AcademyFees::reportReady());
check('no way to pay set up (no keys, no Daraza, no mobile money / bank): the fee is not charged, nobody is blocked', AcademyFees::signup() === null && AcademyFees::manual() === null);
Settings::setMany(['academy_pay_mobile_money' => "MTN: 0772 222 333\nAirtel: 0752 444 555", 'academy_pay_bank_details' => "Stanbic Bank\nLumina Africa Foundation\n9030000000001"]);
check('mobile-money / bank details alone are enough to enforce the fee', AcademyFees::signup()['display'] === 'UGX 20,000' && AcademyFees::manual()['mobile'] !== '' && AcademyFees::manual()['bank'] !== '');

// ---- the applicant signs up over HTTP ---------------------------------------------------------------------------------------------------
$base = 'http://localhost/' . basename(dirname(__DIR__));
$mk = static function (): array {
    return ['jar' => tempnam(sys_get_temp_dir(), 'fej')];
};
$req = static function (array $ctx, string $method, string $path, array $form = [], ?array $file = null) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $ctx['jar'], CURLOPT_COOKIEFILE => $ctx['jar'], CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        $post = $form;
        if ($file !== null) {
            $post[$file[0]] = new CURLFile($file[1], $file[2], $file[3]);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file === null ? http_build_query($form) : $post);
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs), 'loc' => preg_match('/^location:\s*(\S+)/mi', substr($raw, 0, $hs), $m) === 1 ? $m[1] : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$learner = $mk();
$rg = $req($learner, 'GET', '/academy/register');
preg_match('/name="_hp_ts" value="([^"]+)"/', $rg['body'], $ts);
check('sign-up page: names the fee and the ways, including mobile money / bank, and says approval needs the payment', str_contains($rg['body'], 'Sign-up fee') && str_contains($rg['body'], 'Mobile money or bank transfer') && str_contains($rg['body'], 'approved only after the payment is confirmed'));
sleep(6);
$post = $req($learner, 'POST', '/academy/register', ['_token' => $tok($rg['body']), '_hp_ts' => $ts[1] ?? '', 'name' => 'ZZ Fe Payer', 'email' => 'zz-fe-payer@example.test', 'password' => 'Fee enforce pass 77x', 'country' => 'Uganda', 'phone' => '+256 700000051', 'whatsapp' => '+256 700000051', 'agree' => '1']);
$lid = (int) Database::value("SELECT id FROM academy_learners WHERE email = 'zz-fe-payer@example.test'", [], 0);
check('sign-up: the account is created with the fee DUE and the applicant lands on the waiting page', str_ends_with($post['loc'], '/academy/pending') && Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$lid]) === 'due', $post['code'] . ' ' . $post['loc']);
$pend = $req($learner, 'GET', '/academy/pending');
check('waiting page: shows the mobile-money numbers, the bank details, the exact amount and the "I have paid" form (no Pesapal button, no keys)', str_contains($pend['body'], '0772 222 333') && str_contains($pend['body'], '9030000000001') && str_contains($pend['body'], 'Pay exactly')
    && str_contains($pend['body'], 'UGX 20,000') && str_contains($pend['body'], 'academy/fee/report') && !str_contains($pend['body'], 'with Pesapal') && str_contains($pend['body'], 'approved only after the sign-up fee is paid'));
check('the applicant cannot sign in while the fee is unpaid', LearnerAuth::login('zz-fe-payer@example.test', 'Fee enforce pass 77x', '10.7.7.1')['ok'] === false && !empty(LearnerAuth::login('zz-fe-payer@example.test', 'Fee enforce pass 77x', '10.7.7.1')['fee_due']));

// ---- reporting the payment ---------------------------------------------------------------------------------------------------------------
$hp = static fn(string $h): string => preg_match('/name="_hp_ts" value="([^"]+)"/', $h, $m) === 1 ? $m[1] : '';
sleep(6);
$bad = $req($learner, 'POST', '/academy/fee/report', ['_token' => $tok($pend['body']), '_hp_ts' => $hp($pend['body']), 'reference' => 'x']);
check('a missing or silly transaction ID is refused and nothing is saved', Database::value('SELECT fee_reported_at FROM academy_learners WHERE id = ?', [$lid]) === null);
$pdf = tempnam(sys_get_temp_dir(), 'rcp') . '.pdf';
file_put_contents($pdf, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] >>\nendobj\ntrailer\n<< /Root 1 0 R /Size 4 >>\n%%EOF\n");
$pend = $req($learner, 'GET', '/academy/pending');
sleep(6);
$rep = $req($learner, 'POST', '/academy/fee/report', ['_token' => $tok($pend['body']), '_hp_ts' => $hp($pend['body']), 'reference' => 'MP123456789'], ['proof', $pdf, 'application/pdf', 'receipt.pdf']);
$row = (array) Database::one('SELECT fee_status, fee_reference, fee_reported_at, fee_proof_path, email_verified_at FROM academy_learners WHERE id = ?', [$lid]);
check('reporting: the transaction ID and the receipt are stored (privately), the fee stays DUE and the account is NOT approved', $row['fee_reference'] === 'MP123456789' && $row['fee_reported_at'] !== null && $row['fee_status'] === 'due' && $row['email_verified_at'] === null
    && ($row['fee_proof_path'] === null || is_file(STORAGE_PATH . '/private/' . $row['fee_proof_path'])), json_encode($row));
$receiptSaved = !empty($row['fee_proof_path']);
$pend = $req($learner, 'GET', '/academy/pending');
check('waiting page: now says the payment was reported and is being checked', str_contains($pend['body'], 'MP123456789') && str_contains($pend['body'], 'is checking it'));

// ---- the administrator: cannot approve before payment ----------------------------------------------------------------------------------------
User::create(['name' => 'ZZ Fe Admin', 'email' => 'zz-fe-admin@example.test', 'password_hash' => Auth::hashPassword('Fee admin pass 88'), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$admin = $mk();
$pg = $req($admin, 'GET', '/admin/login');
$req($admin, 'POST', '/admin/login', ['_token' => $tok($pg['body']), 'email' => 'zz-fe-admin@example.test', 'password' => 'Fee admin pass 88']);
$lp = $req($admin, 'GET', '/admin/academy/learners/' . $lid);
check('admin: the learner page shows "Payment reported", the transaction ID' . ($receiptSaved ? ' and the receipt link' : ''), $lp['code'] === 200 && str_contains($lp['body'], 'Payment reported') && str_contains($lp['body'], 'MP123456789') && (!$receiptSaved || str_contains($lp['body'], 'see the receipt')), (string) $lp['code']);
if ($receiptSaved) {
    $fp = $req($admin, 'GET', '/admin/academy/learners/' . $lid . '/fee-proof');
    check('admin: the receipt opens (private, not cached) for an administrator', $fp['code'] === 200 && str_starts_with($fp['body'], '%PDF') && stripos($fp['head'], 'no-store') !== false);
    $anon = $req($mk(), 'GET', '/admin/academy/learners/' . $lid . '/fee-proof');
    check('the receipt is NOT available to anyone who is not signed in as an administrator', in_array($anon['code'], [301, 302, 303, 401, 403, 404], true) && !str_starts_with($anon['body'], '%PDF'), (string) $anon['code']);
}
$ap = $req($admin, 'POST', '/admin/academy/learners/' . $lid . '/approve', ['_token' => $tok($lp['body'])]);
check('admin: pressing Approve on an unpaid account is refused', Database::value('SELECT email_verified_at FROM academy_learners WHERE id = ?', [$lid]) === null);
$lp = $req($admin, 'GET', '/admin/academy/learners/' . $lid);
$vf = $req($admin, 'POST', '/admin/academy/learners/' . $lid . '/verify', ['_token' => $tok($lp['body'])]);
check('admin: "confirm the e-mail" is refused as well while the fee is unpaid', Database::value('SELECT email_verified_at FROM academy_learners WHERE id = ?', [$lid]) === null);
$list = $req($admin, 'GET', '/admin/academy/learners');
$all = $req($admin, 'POST', '/admin/academy/learners-confirm-all', ['_token' => $tok($list['body'])]);
check('admin: "Approve all" skips the unpaid account', Database::value('SELECT email_verified_at FROM academy_learners WHERE id = ?', [$lid]) === null && LearnerRegistry::approve($lid, null) === false);
check('even a direct approval in code is refused', LearnerRegistry::approve($lid, null) === false);

// ---- payment reminders: one learner and everyone, by message or WhatsApp ---------------------------------------------------------------------------------
$countMsgs = static fn(): int => (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'admin' AND body LIKE '%sign-up fee%not paid%'", [$lid], 0);
$lp = $req($admin, 'GET', '/admin/academy/learners/' . $lid);
check('admin: the learner page has both reminder buttons (message and WhatsApp)', str_contains($lp['body'], 'Remind: message') && str_contains($lp['body'], 'Remind: WhatsApp') && str_contains($lp['body'], 'https://wa.me/256700000051?text='));
$one = $req($admin, 'POST', '/admin/academy/learners/' . $lid . '/remind-fee', ['_token' => $tok($lp['body'])]);
check('admin: the individual reminder lands in the learner\'s messages, with the amount, the mobile-money details and the sign-in link', $countMsgs() === 1
    && ($m = (string) Database::value("SELECT body FROM academy_messages WHERE learner_id = ? ORDER BY id DESC LIMIT 1", [$lid], '')) !== '' && str_contains($m, 'UGX 20,000') && str_contains($m, '0772 222 333') && str_contains($m, 'academy/login'));
$lst = $req($admin, 'GET', '/admin/academy/learners');
check('admin: the learners list offers "Remind all" and the WhatsApp list', str_contains($lst['body'], 'have not paid the sign-up fee') || str_contains($lst['body'], 'has not paid the sign-up fee'));
$bulk = $req($admin, 'POST', '/admin/academy/learners-remind-fee-all', ['_token' => $tok($lst['body'])]);
check('admin: "Remind all" sends the reminder to the learner who has not paid', $countMsgs() === 2, (string) $countMsgs());
$wl = $req($admin, 'GET', '/admin/academy/learners-fee-reminders');
check('admin: the WhatsApp list has a button with the reminder typed for the learner', $wl['code'] === 200 && str_contains($wl['body'], 'ZZ Fe Payer') && str_contains($wl['body'], 'https://wa.me/256700000051?text=') && str_contains($wl['body'], rawurlencode('UGX 20,000')));
check('a reminder is refused for an account that does not owe the fee', AcademyFees::remind(999999999, null, false) === false);

// ---- the administrator confirms the payment, then approves -----------------------------------------------------------------------------------------
$lp = $req($admin, 'GET', '/admin/academy/learners/' . $lid);
$mp = $req($admin, 'POST', '/admin/academy/learners/' . $lid . '/mark-fee-paid', ['_token' => $tok($lp['body'])]);
check('admin: marks the fee as paid after checking the money', Database::value('SELECT fee_status FROM academy_learners WHERE id = ?', [$lid]) === 'paid');
$login = LearnerAuth::login('zz-fe-payer@example.test', 'Fee enforce pass 77x', '10.7.7.2');
check('after the payment is confirmed the account is still waiting for the normal approval (cannot sign in yet)', $login['ok'] === false && empty($login['fee_due']) && !empty($login['unverified']));
$lp = $req($admin, 'GET', '/admin/academy/learners/' . $lid);
$ap = $req($admin, 'POST', '/admin/academy/learners/' . $lid . '/approve', ['_token' => $tok($lp['body'])]);
check('admin: now Approve works, and the learner can sign in', Database::value('SELECT email_verified_at FROM academy_learners WHERE id = ?', [$lid]) !== null && LearnerAuth::login('zz-fe-payer@example.test', 'Fee enforce pass 77x', '10.7.7.3')['ok'] === true);

// ---- the report is only for an account that owes the fee --------------------------------------------------------------------------------------------
check('a paid account gets no reminder', AcademyFees::remind($lid, null, false) === false && AcademyFees::reminderWhatsAppUrl((array) Database::one('SELECT name, whatsapp, phone FROM academy_learners WHERE id = ?', [$lid])) !== null);
check('a paid account cannot "report" a payment again', AcademyFees::report($lid, 'MP999999999', null)['ok'] === false);

// ---- clean up ------------------------------------------------------------------------------------------------------------------------------------------
@unlink($pdf);
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
