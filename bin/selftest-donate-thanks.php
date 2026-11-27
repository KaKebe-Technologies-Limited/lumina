<?php
declare(strict_types=1);

/**
 * DONATE PAGE + PLEDGE ("show interest") FLOW with the foundation's official bank account: /donate shows the account (hidden while "Bank and manual mobile-money details" is off),
 * a pledge redirects to GET /donate/thanks which shows the thank-you, "no money has been taken yet", the account with the reference DON-<id> and the amount, the mobile-money details
 * that are set, and what happens next; the page is private, noindex, survives a refresh, expires and is empty for a visitor without a pledge. The donation row gets the reference
 * DON-<id>; the staff e-mail names it; the donor is NOT e-mailed automatically. Needs MySQL and Apache. Rows zz-dt-* and the settings are put back.
 * Run alone: /c/xampp/php/php.exe bin/selftest-donate-thanks.php   (the app is loaded from app/bootstrap.php)
 */

use App\Core\Database;
use App\Core\Settings;
use App\Services\Payments\PaymentOptions;

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

echo "-- donate page, pledge and thank-you page --\n";
$keys = ['official_bank_account_name', 'official_bank_account_number', 'official_bank_name', 'official_bank_branch', 'official_bank_swift', 'official_bank_note', 'donate_bank_details', 'donate_mobile_money', 'donate_paypal_email', 'pay_opt_manual',
    'daraza_donate_on', 'daraza_url', 'daraza_donate_url', 'donate_method', 'pay_opt_link'];
$in = implode(',', array_fill(0, count($keys), '?'));
$snapshot = Database::all('SELECT * FROM settings WHERE setting_key IN (' . $in . ')', $keys);
$restore = static function () use ($keys, $in, $snapshot): void {
    Database::execute('DELETE FROM settings WHERE setting_key IN (' . $in . ')', $keys);
    foreach ($snapshot as $row) {
        Database::insert('settings', $row);
    }
    Settings::flush();
};
$cleanup = static function (): void {
    Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-dt-%'");
    Database::execute('DELETE FROM rate_limits');
};
$cleanup();
$mailDir = STORAGE_PATH . '/mail';
$mails = static fn(): array => glob($mailDir . '/*.eml') ?: [];
$mailsAtStart = $mails();
$mailText = static function (array $before) use ($mailDir): string {
    $t = '';
    foreach (array_diff(glob($mailDir . '/*.eml') ?: [], $before) as $f) {
        $raw = (string) file_get_contents($f);
        $t .= $raw . "\n";
        if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n([A-Za-z0-9+\/=\r\n]+)/', $raw, $mm)) {
            foreach ($mm[1] as $b64) {
                $t .= (string) base64_decode(preg_replace('/\s+/', '', $b64) ?? '') . "\n";
            }
        }
        if (preg_match('/quoted-printable/i', $raw)) {
            $t .= quoted_printable_decode($raw) . "\n";
        }
    }
    return $t;
};

$base = 'http://localhost/' . basename(dirname(__DIR__));
$basePath = (string) parse_url($base, PHP_URL_PATH);
$req = static function (string $method, string $path, array $form, string $jar) use ($base): array {
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
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'head' => $head, 'body' => substr($raw, $hs), 'loc' => preg_match('/^Location:\s*(.+)$/mi', $head, $m) === 1 ? trim($m[1]) : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$hp = static function (): string {
    $ts = (string) (time() - 30);
    return $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
};
$newJar = static fn(): string => (string) tempnam(sys_get_temp_dir(), 'dtj');
$pledge = static function (string $jar, array $over = []) use ($req, $tok, $hp): array {
    $page = $req('GET', '/donate', [], $jar);
    return $req('POST', '/donate/pledge', $over + [
        '_token' => $tok($page['body']), '_hp_ts' => $hp(), 'donor_name' => 'Zed Thanks Donor', 'donor_email' => 'zz-dt-a@example.test', 'donor_phone' => '',
        'method' => 'bank', 'amount' => '150000', 'currency' => 'UGX', 'message' => 'Happy to help',
    ], $jar);
};
$row = static fn(string $email): ?array => Database::one('SELECT * FROM donations WHERE donor_email = ? ORDER BY id DESC LIMIT 1', [$email]);
$noStore = static fn(array $r): bool => preg_match('/^Cache-Control:\s*private, no-store\s*$/mi', $r['head']) === 1;

try {
    // a known state: the built-in account, one mobile-money line, no extra bank text
    Database::execute('DELETE FROM settings WHERE setting_key IN (' . $in . ')', $keys);
    Settings::flush();
    Settings::setMany(['donate_bank_details' => '', 'donate_mobile_money' => 'MTN: 0772 000 111 (zz-dt)', 'donate_paypal_email' => '']);

    // ---- /donate
    $jar = $newJar();
    $d = $req('GET', '/donate', [], $jar);
    check('/donate answers 200 and shows the official account: name, number, bank, branch and the note', $d['code'] === 200 && str_contains($d['body'], 'Lumina Africa Foundation') && str_contains($d['body'], '4090008000229') && str_contains($d['body'], 'Pearl Bank') && str_contains($d['body'], 'Gulu') && str_contains($d['body'], 'confirm the account name before sending'), (string) $d['code']);
    check('/donate: the card sits in the Ways to give grid (id ways) and has copy buttons and its script; the old free-text bank box is gone', str_contains($d['body'], 'id="ways"') && str_contains($d['body'], 'data-bankcard-copy="4090008000229"') && str_contains($d['body'], 'js/bank-card.js') && !str_contains($d['body'], 'id="donate-bank"') && str_contains($d['body'], 'donate-way--account'));
    check('/donate: the mobile-money card and the donate.js copy buttons are still there', str_contains($d['body'], 'MTN: 0772 000 111 (zz-dt)') && str_contains($d['body'], 'js/pages/donate.js') && str_contains($d['body'], 'data-copy="MTN: 0772 000 111 (zz-dt)"'));
    check('/donate: the pledge form and its privacy note are untouched', str_contains($d['body'], 'action="' . $basePath . '/donate/pledge"') && str_contains($d['body'], 'id="pledge"') && str_contains($d['body'], 'name="donor_email"'));
    check('/donate: the account number is not written into the page script (it lives in the settings and the button)', !str_contains((string) file_get_contents(dirname(__DIR__) . '/assets/js/pages/donate.js'), '4090008000229'));
    Settings::setMany(['donate_bank_details' => 'Use your full name as the reference.']);
    $d2 = $req('GET', '/donate', [], $jar);
    check('/donate: extra bank instructions are printed under the account (not as a second bank box)', str_contains($d2['body'], 'Use your full name as the reference.') && substr_count($d2['body'], '4090008000229') === substr_count($d['body'], '4090008000229'));
    Settings::setMany(['donate_bank_details' => "Bank: Pearl Bank\nAccount Number: 4090008000229"]);
    $d3 = $req('GET', '/donate', [], $jar);
    check('/donate: free text that repeats the official account number is not shown a second time', !str_contains($d3['body'], 'Bank: Pearl Bank') && substr_count($d3['body'], '4090008000229') === substr_count($d['body'], '4090008000229'));
    Settings::setMany(['donate_bank_details' => '']);

    // ---- a pledge
    $mailsBefore = $mails();
    $r = $pledge($jar);
    $id = (int) ($row('zz-dt-a@example.test')['id'] ?? 0);
    $don = $row('zz-dt-a@example.test');
    check('a pledge redirects (302) to /donate/thanks', $r['code'] === 302 && preg_match('~/donate/thanks$~', $r['loc']) === 1, $r['code'] . ' ' . $r['loc']);
    check('the pledge is recorded (pledged, pledge_form) and its reference is DON-<id>', $don !== null && $don['status'] === 'pledged' && $don['source'] === 'pledge_form' && (float) $don['amount'] === 150000.0 && (string) $don['reference'] === 'DON-' . $id, json_encode($don));
    $staff = $mailText($mailsBefore);
    check('the e-mail to the foundation names the reference given to the donor', str_contains($staff, 'Payment reference shown to the donor: DON-' . $id) && str_contains($staff, 'zz-dt-a@example.test'), substr($staff, 0, 300));
    check('the donor is NOT e-mailed automatically (only the foundation is told)', substr_count($staff, 'Subject: ') === 1 && !preg_match('/^To:.*zz-dt-a@example\.test/mi', $staff));

    $t = $req('GET', '/donate/thanks', [], $jar);
    check('/donate/thanks (same visitor) answers 200', $t['code'] === 200, (string) $t['code']);
    check('the thank-you names the donor, states that no money has been taken yet and shows the amount pledged', str_contains($t['body'], 'Thank you, Zed') && str_contains($t['body'], 'No money has been taken yet') && str_contains($t['body'], 'UGX 150,000'), substr(strip_tags($t['body']), 0, 200));
    check('it shows the official account and the reference DON-' . $id . ' with copy buttons', str_contains($t['body'], '4090008000229') && str_contains($t['body'], 'Pearl Bank') && str_contains($t['body'], 'Gulu') && str_contains($t['body'], 'Payment reference') && str_contains($t['body'], 'DON-' . $id) && str_contains($t['body'], 'data-bankcard-copy="DON-' . $id . '"') && str_contains($t['body'], 'Amount to send'));
    check('it shows the mobile-money details that are set, and "What happens next" with a link back to /donate', str_contains($t['body'], 'MTN: 0772 000 111 (zz-dt)') && str_contains($t['body'], 'What happens next') && str_contains($t['body'], 'href="' . $basePath . '/donate"') && str_contains($t['body'], 'Back to the Donate page'));
    check('"What happens next" lists the steps from the page registry (send the gift, share the proof of payment, receive a receipt)', str_contains($t['body'], 'class="donate-next"') && substr_count($t['body'], '<li>Send your gift whenever') === 1 && str_contains($t['body'], 'Share your proof of payment with us') && str_contains($t['body'], 'send you a receipt'));
    check('it is not indexed and not cached (noindex meta, Cache-Control: private, no-store)', preg_match('/<meta[^>]+name="robots"[^>]+noindex/i', $t['body']) === 1 && $noStore($t), $t['head']);
    check('it does not show the pledge form again (nothing to submit twice)', !preg_match('~<form[^>]+action="[^"]*/donate/(pledge|online|mobile-money)~', $t['body']));
    $t2 = $req('GET', '/donate/thanks', [], $jar);
    check('a refresh still shows the page and the account (the session is read, not consumed)', $t2['code'] === 200 && str_contains($t2['body'], 'DON-' . $id) && str_contains($t2['body'], '4090008000229'));
    $fresh = $req('GET', '/donate/thanks', [], $newJar());
    check('a visitor without a recent pledge is sent to /donate (302)', $fresh['code'] === 302 && preg_match('~/donate$~', $fresh['loc']) === 1, $fresh['code'] . ' ' . $fresh['loc']);

    // ---- it expires after an hour
    $sid = '';
    foreach (file($jar, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = ltrim($line, '#');
        $p = explode("\t", $line);
        if (count($p) >= 7 && $p[5] === (string) \App\Core\Config::get('session.name', 'lumina_session')) {
            $sid = trim($p[6]);
        }
    }
    $sf = STORAGE_PATH . '/sessions/sess_' . $sid;
    if ($sid !== '' && is_file($sf)) {
        $orig = (string) file_get_contents($sf);
        $aged = (string) preg_replace('/("at";i:)\d+/', '${1}' . (time() - 7300), $orig, -1, $n);
        file_put_contents($sf, $aged);
        $old = $req('GET', '/donate/thanks', [], $jar);
        check('after an hour the thank-you page no longer opens (302 to /donate)', $n === 1 && $old['code'] === 302 && preg_match('~/donate$~', $old['loc']) === 1, $n . ' ' . $old['code'] . ' ' . $old['loc']);
    } else {
        check('the session file of the test visitor was found (needed for the expiry check)', false, $sid . ' ' . $sf);
    }

    // ---- a pledge in another currency gets the "outside Uganda" note; a bad pledge goes back to the form
    $jar2 = $newJar();
    $r = $pledge($jar2, ['donor_email' => 'zz-dt-b@example.test', 'amount' => '75', 'currency' => 'USD']);
    $t = $req('GET', '/donate/thanks', [], $jar2);
    check('a pledge in USD: thanks page shows USD 75.00 and the note about giving from outside Uganda', $r['code'] === 302 && str_contains($t['body'], 'USD 75.00') && str_contains($t['body'], 'outside Uganda'), $r['code'] . ' ' . $t['code']);
    $jar3 = $newJar();
    $bad = $pledge($jar3, ['donor_email' => 'zz-dt-bad', 'donor_name' => '']);
    $after = $req('GET', '/donate/thanks', [], $jar3);
    check('an invalid pledge goes back to /donate#pledge, records nothing and opens no thank-you page', $bad['code'] === 302 && str_contains($bad['loc'], '/donate#pledge') && (int) Database::value("SELECT COUNT(*) FROM donations WHERE donor_email = 'zz-dt-bad'", [], 0) === 0 && $after['code'] === 302 && preg_match('~/donate$~', $after['loc']) === 1, $bad['code'] . ' ' . $bad['loc']);

    // ---- with an online way to give switched on, /donate points to the account and the thank-you page offers "Give online now"
    Settings::setMany(['daraza_donate_on' => '1', 'daraza_url' => 'https://paylink.daraza.net/zz-dt', 'donate_method' => 'link', 'pay_opt_link' => '1']);
    $don = $req('GET', '/donate', [], $jar2);
    $thx = $req('GET', '/donate/thanks', [], $jar2);
    check('with a give-online way on: /donate says "Prefer a bank transfer? See our bank account" (a link to the card), the thank-you page offers "Give online now"', str_contains($don['body'], 'Prefer a bank transfer?') && str_contains($don['body'], 'href="#donate-account"') && str_contains($thx['body'], 'Give online now') && str_contains($thx['body'], $basePath . '/donate#give-online'));
    PaymentOptions::save(['manual' => false]);
    $donOff = $req('GET', '/donate', [], $jar2);
    check('...and the "See our bank account" line is gone while the manual-details switch is off (the give-online section stays)', !str_contains($donOff['body'], 'Prefer a bank transfer?') && str_contains($donOff['body'], 'id="give-online"'));
    PaymentOptions::save(['manual' => true]);
    Settings::setMany(['daraza_donate_on' => '0']);

    // ---- the switch in Admin > Payment options
    PaymentOptions::save(['manual' => false]);
    $off = $req('GET', '/donate', [], $jar2);
    check('switch off: /donate still answers 200 but shows no bank account (no number, no card, no link to it)', $off['code'] === 200 && !str_contains($off['body'], '4090008000229') && !str_contains($off['body'], 'bankcard') && !str_contains($off['body'], 'id="donate-account"') && !str_contains($off['body'], 'See our bank account'), (string) $off['code']);
    $offT = $req('GET', '/donate/thanks', [], $jar2);
    check('switch off: the thank-you page (same visitor) has no account and no mobile-money number, and says that the foundation will make contact', $offT['code'] === 200 && !str_contains($offT['body'], '4090008000229') && !str_contains($offT['body'], 'MTN: 0772 000 111') && str_contains($offT['body'], 'No money has been taken yet') && str_contains($offT['body'], 'We will contact you'), (string) $offT['code']);
    PaymentOptions::save(['manual' => true]);
    $onT = $req('GET', '/donate/thanks', [], $jar2);
    check('switch back on: the account is back on the thank-you page', str_contains($onT['body'], '4090008000229'));
} finally {
    $restore();
    $cleanup();
    foreach (array_diff($mails(), $mailsAtStart) as $f) {
        if (str_contains((string) file_get_contents($f), 'zz-dt-')) {
            @unlink($f);
        }
    }
}
check('cleanup: no zz-dt rows are left and the settings are back as they were', (int) Database::value("SELECT COUNT(*) FROM donations WHERE donor_email LIKE 'zz-dt-%'", [], 0) === 0 && (int) Database::value('SELECT COUNT(*) FROM settings WHERE setting_key IN (' . $in . ')', $keys, 0) === count($snapshot));

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
