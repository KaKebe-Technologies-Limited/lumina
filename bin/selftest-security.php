<?php
declare(strict_types=1);

/**
 * SECURITY: two-step sign-in by e-mail, the screen lock, the extra sign-in throttle, the browser binding, safe error messages, and that secrets never reach a browser.
 * Real HTTP as throw-away administrators; mail goes to files (APP_ENV=local); every setting touched is put back.
 * Run alone: /c/xampp/php/php.exe bin/selftest-security.php      Needs MySQL and Apache.
 */

use App\Core\Auth;
use App\Core\Database;
use App\Core\Settings;
use App\Models\User;
use App\Services\Security\TwoFactor;
use App\Services\Shop\SecretStore;

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

echo "-- security --\n";
$snapshot = Database::all("SELECT * FROM settings WHERE setting_key LIKE 'security\\_%' OR setting_key LIKE '%secret%'");
$restore = static function () use ($snapshot): void {
    Database::execute("DELETE FROM settings WHERE setting_key LIKE 'security\\_%' OR setting_key LIKE '%secret%'");
    foreach ($snapshot as $row) {
        Database::insert('settings', $row);
    }
    Settings::flush();
};
$cleanup = static function (): void {
    Database::execute("DELETE FROM admin_login_codes WHERE user_id IN (SELECT id FROM users WHERE email LIKE 'zz-sec-%')");
    Database::execute("DELETE FROM admin_trusted_devices WHERE user_id IN (SELECT id FROM users WHERE email LIKE 'zz-sec-%')");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-sec-%'");
    Database::execute("DELETE FROM login_attempts WHERE email LIKE 'zz-sec-%'");
    Database::execute("DELETE FROM audit_log WHERE user_name LIKE '%zz-sec-%' OR summary LIKE '%zz-sec-%'");
    Database::execute('DELETE FROM rate_limits');
};
TwoFactor::install();
$cleanup();
Settings::setMany(['security_2fa' => '0', 'security_lock_minutes' => '15', 'security_trust_days' => '30']);
$mailDir = STORAGE_PATH . '/mail';
$mailsAtStart = glob($mailDir . '/*.eml') ?: [];

$base = 'http://localhost/' . basename(dirname(__DIR__));
$ua = 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0.0.0 Safari/537.36';
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null, array $headers = [], string $agent = '') use ($base, $ua): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'sec');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT => $agent !== '' ? $agent : $ua, CURLOPT_HTTPHEADER => $headers]);
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
$post = static function (string $path, array $form, ?string &$jar, string $from = '/admin/login', string $agent = '') use ($req, $tok): array {
    $pg = $req('GET', $from, [], $jar, [], $agent);
    return $req('POST', $path, $form + ['_token' => $tok($pg['body'])], $jar, [], $agent);
};
$mk = static fn(string $email, string $role, string $pw): int => (int) User::create(['name' => 'ZZ Sec ' . $role, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$pwA = 'Admin sec pass 61!';
$pwE = 'Editor sec pass 63!';
$idA = $mk('zz-sec-admin@example.test', 'admin', $pwA);
$idE = $mk('zz-sec-editor@example.test', 'editor', $pwE);
$loginA = static fn(?string &$jar, array $extra = [], string $agent = ''): array => $post('/admin/login', ['email' => 'zz-sec-admin@example.test', 'password' => $pwA] + $extra, $jar, '/admin/login', $agent);
$loginE = static fn(?string &$jar): array => $post('/admin/login', ['email' => 'zz-sec-editor@example.test', 'password' => $pwE], $jar);
$newMails = static function () use ($mailDir, &$seenMails): array {
    $out = [];
    foreach (array_diff(glob($mailDir . '/*.eml') ?: [], $seenMails) as $f) {
        $raw = (string) file_get_contents($f);
        $t = $raw;
        if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n([A-Za-z0-9+\/=\r\n]+)/', $raw, $mm)) {
            foreach ($mm[1] as $b64) {
                $t .= "\n" . (string) base64_decode(preg_replace('/\s+/', '', $b64) ?? '');
            }
        }
        $out[$f] = $t;
        $seenMails[] = $f;
    }
    return $out;
};
$seenMails = $mailsAtStart;
$codeFrom = static function (array $mails): ?string {
    foreach ($mails as $t) {
        if (str_contains($t, 'sign-in code') && preg_match('/\n\s{2,}(\d{6})\s*\n/', $t, $m) === 1) {
            return $m[1];
        }
    }
    return null;
};
$ok302 = static fn(array $r): bool => in_array($r['code'], [302, 303], true);

if (!$ok302($loginA($jProbe))) {
    check('http: the test administrator can sign in (the web server must be running)', false);
    $restore();
    $cleanup();
    if (!empty($standalone)) {
        exit(1);
    }
    return;
}

echo "-- no two-step: nothing changes --\n";
$j0 = null;
$r = $loginA($j0);
check('with two-step off the password alone signs in, as before', $ok302($r) && !str_contains($r['loc'], 'verify') && $req('GET', '/admin', [], $j0)['code'] === 200);
$sec = $req('GET', '/admin/security', [], $j0);
check('Admin > Security opens for an administrator: checklist, two-step card and settings', $sec['code'] === 200 && str_contains($sec['body'], 'Safety checklist') && str_contains($sec['body'], 'Two-step sign-in by e-mail') && str_contains($sec['body'], 'Screen lock') && str_contains($sec['body'], 'Two-step sign-in is off'));
$jE = null;
$loginE($jE);
check('an editor (no settings.manage) cannot open or use the Security page', $req('GET', '/admin/security', [], $jE)['code'] === 403 && $req('POST', '/admin/security/2fa-on', ['_token' => 'x'], $jE)['code'] === 403);

echo "-- turning two-step on needs a real test code --\n";
$newMails();
$wrong = $post('/admin/security/2fa-on', ['code' => '000000'], $j0, '/admin/security');
Settings::flush();
check('a wrong test code does not turn it on', $ok302($wrong) && (string) Settings::get('security_2fa', '0') === '0');
$post('/admin/security/2fa-test', [], $j0, '/admin/security');
$testCode = $codeFrom($newMails());
check('"Send a test code" e-mails a 6-digit code to the administrator\'s own address', $testCode !== null && strlen($testCode) === 6);
$post('/admin/security/2fa-on', ['code' => (string) $testCode], $j0, '/admin/security');
Settings::flush();
check('the right test code turns two-step sign-in on', (string) Settings::get('security_2fa', '0') === '1' && TwoFactor::enabled());
check('the page now says it is on', str_contains($req('GET', '/admin/security', [], $j0)['body'], 'Two-step sign-in by e-mail is on'));

echo "-- the two-step sign-in --\n";
$newMails();
$j1 = null;
$r = $loginA($j1);
check('right password -> sent to the code page, and NOT signed in yet', $ok302($r) && str_contains($r['loc'], 'admin/verify') && $req('GET', '/admin', [], $j1)['code'] !== 200 && str_contains($req('GET', '/admin', [], $j1)['loc'], 'admin/login'));
$mails = $newMails();
$code1 = $codeFrom($mails);
check('a 6-digit code was e-mailed to the account, with the time and address of the attempt', $code1 !== null && count($mails) === 1 && str_contains(implode('', $mails), 'zz-sec-admin@example.test') && str_contains(implode('', $mails), 'someone knows your password'));
check('the code page shows only a masked address', str_contains($req('GET', '/admin/verify', [], $j1)['body'], 'z***') || str_contains($req('GET', '/admin/verify', [], $j1)['body'], 'z*'));
$bad = $post('/admin/verify', ['code' => '000000'], $j1, '/admin/verify');
check('a wrong code does not sign in', $ok302($bad) && str_contains($bad['loc'], 'admin/verify') && str_contains($req('GET', '/admin', [], $j1)['loc'], 'admin/login'));
$good = $post('/admin/verify', ['code' => (string) $code1], $j1, '/admin/verify');
check('the right code signs in and lands on the admin', $ok302($good) && $req('GET', '/admin', [], $j1)['code'] === 200);
$j2 = null;
$loginA($j2);
$mails = $newMails();
$code2 = $codeFrom($mails);
$reuse = $post('/admin/verify', ['code' => (string) $code1], $j2, '/admin/verify');
check('an old code (already used, or replaced by a newer one) never works', $code2 !== null && $code2 !== $code1 && str_contains($req('GET', '/admin', [], $j2)['loc'], 'admin/login'));
$rs = $post('/admin/verify/resend', [], $j2, '/admin/verify');
check('asking for another code at once is refused (45 s apart) and sends nothing', $ok302($rs) && $newMails() === []);
$fin = $post('/admin/verify', ['code' => (string) $code2], $j2, '/admin/verify');
check('the newest code works', $ok302($fin) && $req('GET', '/admin', [], $j2)['code'] === 200);

echo "-- limits on the code --\n";
$j3 = null;
$loginA($j3);
$code3 = $codeFrom($newMails());
for ($i = 0; $i < 5; $i++) {
    $post('/admin/verify', ['code' => '111111'], $j3, '/admin/verify');
}
$late = $post('/admin/verify', ['code' => (string) $code3], $j3, '/admin/verify');
check('five wrong codes cancel the sign-in: even the right code is then useless', str_contains($req('GET', '/admin', [], $j3)['loc'], 'admin/login') && !str_contains((string) $late['loc'], 'admin/verify?') );
$j4 = null;
$loginA($j4);
$code4 = $codeFrom($newMails());
Database::execute("UPDATE admin_login_codes SET expires_at = (NOW() - INTERVAL 1 MINUTE) WHERE user_id = ? AND used_at IS NULL", [$idA]);
$post('/admin/verify', ['code' => (string) $code4], $j4, '/admin/verify');
check('an expired code (10 minutes) is refused', str_contains($req('GET', '/admin', [], $j4)['loc'], 'admin/login'));
check('codes are stored as a keyed hash, never as the code', (int) Database::value("SELECT COUNT(*) FROM admin_login_codes WHERE code_hash = ?", [(string) $code4], 0) === 0 && (int) Database::value("SELECT COUNT(*) FROM admin_login_codes WHERE user_id = ? AND CHAR_LENGTH(code_hash) = 64", [$idA], 0) > 0);

echo "-- remember this browser --\n";
$j5 = null;
$loginA($j5);
$code5 = $codeFrom($newMails());
$post('/admin/verify', ['code' => (string) $code5, 'trust' => '1'], $j5, '/admin/verify');
check('"remember this browser" stores only the hash of a random token and sets an HttpOnly cookie', (int) Database::value('SELECT COUNT(*) FROM admin_trusted_devices WHERE user_id = ?', [$idA], 0) === 1 && str_contains((string) file_get_contents($j5), 'lm_td') && str_contains((string) file_get_contents($j5), '#HttpOnly_'));
$post('/admin/logout', [], $j5, '/admin');
$newMails();
$again = $loginA($j5);
check('a remembered browser skips the code (and no code is e-mailed)', $ok302($again) && !str_contains($again['loc'], 'verify') && $newMails() === [] && $req('GET', '/admin', [], $j5)['code'] === 200);
$post('/admin/security/devices-forget', [], $j5, '/admin/security');
$post('/admin/logout', [], $j5, '/admin');
$after = $loginA($j5);
check('after "Forget all remembered browsers" the code is asked again', $ok302($after) && str_contains($after['loc'], 'admin/verify'));
$newMails();

echo "-- turning it off needs the password --\n";
$j6 = null;
$loginA($j6);
$post('/admin/verify', ['code' => (string) $codeFrom($newMails())], $j6, '/admin/verify');
$post('/admin/security/2fa-off', ['password' => 'not the password'], $j6, '/admin/security');
Settings::flush();
check('a wrong password does not turn it off', (string) Settings::get('security_2fa', '0') === '1');

echo "-- the screen lock --\n";
$sessFile = static function (string $jar): ?string {
    if (preg_match('/\t(lumina_session|[A-Za-z0-9_]*sess[A-Za-z0-9_]*|PHPSESSID)\t([A-Za-z0-9,-]+)/', (string) file_get_contents($jar), $m) === 1) {
        $f = STORAGE_PATH . '/sessions/sess_' . $m[2];
        return is_file($f) ? $f : null;
    }
    return null;
};
$ageSession = static function (string $jar, int $secondsAgo) use ($sessFile): bool {
    $f = $sessFile($jar);
    if ($f === null) {
        return false;
    }
    $data = (string) file_get_contents($f);
    $n = preg_replace('/_act\|i:\d+;/', '_act|i:' . (time() - $secondsAgo) . ';', $data, 1, $count);
    if ($count !== 1) {
        return false;
    }
    return file_put_contents($f, $n) !== false;
};
check('a signed-in session has its activity stamp', $sessFile($j6) !== null && preg_match('/_act\|i:\d+;/', (string) file_get_contents((string) $sessFile($j6))) === 1);
$ageSession($j6, 3600);
$locked = $req('GET', '/admin/pages', [], $j6);
check('after the idle time the page is NOT shown: the lock screen appears instead', str_contains($locked['loc'], 'admin/locked') && !str_contains($locked['body'], 'page-header'));
$lp = $req('GET', '/admin/locked', [], $j6);
check('the lock screen asks for the password and offers to sign out', $lp['code'] === 200 && str_contains($lp['body'], 'Screen locked') && str_contains($lp['body'], 'type="password"') && str_contains($lp['body'], 'Sign out instead') && str_contains($lp['head'], 'no-store'));
check('while locked every admin page redirects to the lock screen (also a POST)', str_contains($req('GET', '/admin', [], $j6)['loc'], 'admin/locked') && $req('POST', '/admin/security/settings', ['_token' => 'x'], $j6)['code'] !== 200);
$wp = $post('/admin/locked', ['password' => 'wrong password'], $j6, '/admin/locked');
check('a wrong password keeps it locked', str_contains($req('GET', '/admin', [], $j6)['loc'], 'admin/locked'));
$up = $post('/admin/locked', ['password' => $pwA], $j6, '/admin/locked');
check('the right password unlocks and returns to the admin (still signed in, no code asked)', $ok302($up) && $req('GET', '/admin', [], $j6)['code'] === 200 && $req('GET', '/admin/pages', [], $j6)['code'] === 200);
Settings::setMany(['security_lock_minutes' => '0']);
$ageSession($j6, 7200);
check('with the lock switched off (0) an idle session is not locked', $req('GET', '/admin/pages', [], $j6)['code'] === 200 || !str_contains($req('GET', '/admin/pages', [], $j6)['loc'], 'locked'));
Settings::setMany(['security_lock_minutes' => '15']);

echo "-- sign-in limits --\n";
Database::execute("DELETE FROM login_attempts WHERE email = 'zz-sec-editor@example.test'");
for ($i = 0; $i < 32; $i++) {
    Database::insert('login_attempts', ['email' => 'zz-sec-editor@example.test', 'ip' => '203.0.113.' . (10 + $i), 'success' => 0]);
}
$j7 = null;
$spread = $loginE($j7);
check('one account guessed from many addresses is locked for everybody, even with the right password', str_contains($req('GET', '/admin', [], $j7)['loc'], 'admin/login') && $ok302($spread));
Database::execute("DELETE FROM login_attempts WHERE email = 'zz-sec-editor@example.test'");
$j8 = null;
$pgU = $req('GET', '/admin/login', [], $j8);
$unknown = $req('POST', '/admin/login', ['_token' => $tok($pgU['body']), 'email' => 'zz-sec-nobody@example.test', 'password' => 'whatever 123'], $j8);
$flashUnknown = $req('GET', '/admin/login', [], $j8)['body'];
$j9 = null;
$pgW = $req('GET', '/admin/login', [], $j9);
$req('POST', '/admin/login', ['_token' => $tok($pgW['body']), 'email' => 'zz-sec-admin@example.test', 'password' => 'wrong wrong 123'], $j9);
$flashWrong = $req('GET', '/admin/login', [], $j9)['body'];
check('an unknown address and a wrong password give the SAME message (no hint which accounts exist)', str_contains($flashUnknown, 'Invalid email or password') && str_contains($flashWrong, 'Invalid email or password'));
Database::execute("DELETE FROM login_attempts WHERE email LIKE 'zz-sec-%'");
Database::execute('DELETE FROM rate_limits');

echo "-- a stolen session cookie --\n";
Settings::setMany(['security_2fa' => '0']);
$j10 = null;
$loginA($j10);
check('the browser that signed in keeps working, also after a version update', $req('GET', '/admin', [], $j10)['code'] === 200 && $req('GET', '/admin', [], $j10, [], 'Mozilla/5.0 (Windows NT 10.0) Chrome/121.0.9.9 Safari/537.36')['code'] === 200);
$stolen = $req('GET', '/admin', [], $j10, [], 'curl/8.4.0');
check('the same cookie replayed from another kind of browser is refused', str_contains($stolen['loc'], 'admin/login'));

echo "-- safe error messages and secrets --\n";
$nf = $req('GET', '/this-page-does-not-exist-' . bin2hex(random_bytes(3)));
$leak = static fn(string $b): bool => preg_match('/Stack trace|\.php:\d+|C:\\\\|xampp|PDOException|SQLSTATE|Uncaught|#\d+ /', $b) === 1;
check('a missing page shows a friendly message and no file names, code or database words', $nf['code'] === 404 && !$leak($nf['body']));
$j11 = null;
$pg = $req('GET', '/admin/login', [], $j11);
$nocsrf = $req('POST', '/admin/login', ['email' => 'a@b.test', 'password' => 'x'], $j11);
check('a form posted without its security token gets a short refusal, not a technical page', in_array($nocsrf['code'], [403, 419], true) && !$leak($nocsrf['body']));
$hdr = $req('GET', '/');
check('every page carries the protective headers (CSP, no sniffing, no framing, referrer policy)', str_contains($hdr['head'], 'Content-Security-Policy') && stripos($hdr['head'], 'X-Content-Type-Options: nosniff') !== false && stripos($hdr['head'], 'X-Frame-Options') !== false && stripos($hdr['head'], 'Referrer-Policy') !== false && stripos($hdr['head'], 'X-Powered-By') === false);
$blocked = true;
$what = [];
foreach (['/.env', '/.env.example', '/app/config/app.php', '/storage/app.key', '/storage/logs/', '/database/schema.sql', '/bin/setup.php', '/composer.json', '/.git/config', '/app/bootstrap.php'] as $p) {
    $c = $req('GET', $p)['code'];
    if ($c === 200 || $c === 500) {
        $blocked = false;
        $what[] = $p . '=' . $c;
    }
}
check('the settings file, source code, database files, logs and keys cannot be downloaded', $blocked, implode(', ', $what));

$canary = 'CANARY-SECRET-' . bin2hex(random_bytes(6));
SecretStore::put('flw_secret_key', $canary);
Settings::setMany(['social_secret_telegram_bot_token' => 'enc:v1:' . base64_encode($canary), 'connection_smtp_password_canary' => $canary]);
$j12 = null;
$loginA($j12);
$leaked = [];
foreach (['/admin/shop/settings/payments', '/admin/social/channels', '/admin/settings', '/admin/connections', '/admin/daraza/api', '/admin/pesapal', '/admin/security', '/admin'] as $p) {
    $pgx = $req('GET', $p, [], $j12);
    if (str_contains($pgx['body'], $canary) || str_contains($pgx['body'], base64_encode($canary))) {
        $leaked[] = $p;
    }
}
check('a secret saved in the settings never appears in any admin page (they are write-only)', $leaked === [], implode(', ', $leaked));
$pub = $req('GET', '/')['body'] . $req('GET', '/donate')['body'] . $req('GET', '/fund-a-project/digital-inclusion')['body'];
$assets = '';
foreach (glob(dirname(__DIR__) . '/assets/js/*.js') ?: [] as $f) {
    $assets .= (string) file_get_contents($f);
}
check('no key, token or "Api-Key" header appears in the public pages or in any script a browser downloads', !str_contains($pub . $assets, $canary) && !preg_match('/FLWSECK-|Api-Key\s|consumer_secret|DARAZA_API_KEY|PESAPAL_CONSUMER|ANTHROPIC_API_KEY|sk-ant-/i', $pub . $assets));
check('the bank-card script exists and, like every script, holds no account value (the account lives only in the settings and their built-in defaults)', is_file(dirname(__DIR__) . '/assets/js/bank-card.js') && !str_contains($assets, '4090008000229') && !str_contains($assets, 'Pearl Bank'));
SecretStore::forget('flw_secret_key');

$restore();
$cleanup();
foreach (array_diff(glob($mailDir . '/*.eml') ?: [], $mailsAtStart) as $f) {
    if (str_contains((string) file_get_contents($f), 'zz-sec-')) {
        @unlink($f);
    }
}
check('cleanup: no zz-sec accounts, codes or remembered browsers are left and the settings are back as they were', (int) Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-sec-%'", [], 0) === 0 && (int) Database::value('SELECT COUNT(*) FROM admin_trusted_devices WHERE user_id IN (' . $idA . ',' . $idE . ')', [], 0) === 0);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
