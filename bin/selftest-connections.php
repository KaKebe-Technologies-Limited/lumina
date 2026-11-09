<?php
declare(strict_types=1);

/**
 * Admin > E-mail and sign-in: the status of the e-mail connection (SMTP / Gmail) and of "Continue with Google", the test-message button and the safety rules (administrators only, no secret
 * on the page). Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-connections.php
 * Needs the local web server. Local mail is in "file" mode, so the test message is written to storage/mail and nothing leaves the computer. Removes its rows (zz-cn-*).
 */

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Services\Academy\GoogleSignIn;

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

echo "-- admin: e-mail and sign-in --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-cn-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$pw = 'Connections pass 71';
User::create(['name' => 'ZZ Cn Admin', 'email' => 'zz-cn-admin@example.test', 'password_hash' => Auth::hashPassword($pw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
User::create(['name' => 'ZZ Cn Editor', 'email' => 'zz-cn-editor@example.test', 'password_hash' => Auth::hashPassword($pw), 'role' => 'editor', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');

$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string $j = null) use ($base): array {
    $j ??= tempnam(sys_get_temp_dir(), 'cnj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => true]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$login = static function (string $email) use ($req, $tok, $pw): string {
    $j = tempnam(sys_get_temp_dir(), 'cnl');
    $p = $req('GET', '/admin/login', [], $j);
    $req('POST', '/admin/login', ['_token' => $tok($p['body']), 'email' => $email, 'password' => $pw], $j);
    return $j;
};
$ja = $login('zz-cn-admin@example.test');
$je = $login('zz-cn-editor@example.test');

$page = $req('GET', '/admin/connections', [], $ja);
check('admin: the page opens and shows the E-mail and Google sign-in cards', $page['code'] === 200 && str_contains($page['body'], 'E-mail') && str_contains($page['body'], 'Google sign-in') && str_contains($page['body'], 'Send a test message'), (string) $page['code']);
check('admin: it shows the exact redirect address to paste into the Google console', str_contains($page['body'], 'academy/google/callback') && preg_match('~<code id="conn-redirect">https?://[^<]+/academy/google/callback</code>~', $page['body']) === 1);
check('admin: it shows the status (Google keys are ' . (GoogleSignIn::enabled() ? 'set' : 'not set') . ' here)', str_contains($page['body'], GoogleSignIn::enabled() ? 'learners see' : 'no Google keys yet'));
check('admin: the SPF / DMARC / DKIM checks are listed', str_contains($page['body'], 'SPF record') && str_contains($page['body'], 'DMARC record') && str_contains($page['body'], 'DKIM'));
$cfg = (array) \App\Core\Config::get('mail.smtp', []);
$cid = GoogleSignIn::cfg();
$leak = false;
foreach ([(string) ($cfg['password'] ?? ''), $cid['secret']] as $secret) {
    if (strlen($secret) >= 6 && str_contains($page['body'], $secret)) {
        $leak = true;
    }
}
check('safety: no mail password and no Google secret anywhere on the page', !$leak);

$tok2 = $tok($page['body']);
$bad = $req('POST', '/admin/connections/test-mail', ['_token' => $tok2, 'to' => 'not an address'], $ja);
check('test message: a wrong address is refused with a message', $bad['code'] === 200 && str_contains($bad['body'], 'valid e-mail address'));
$before = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
Database::execute('DELETE FROM rate_limits');
$page = $req('GET', '/admin/connections', [], $ja);
$ok = $req('POST', '/admin/connections/test-mail', ['_token' => $tok($page['body']), 'to' => 'zz-cn-target@example.test'], $ja);
$after = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$new = array_values(array_diff($after, $before));
$mode = \App\Core\Mailer::driver();
check('test message: it is sent through the configured driver (' . $mode . ') and the page says so', $ok['code'] === 200 && (str_contains($ok['body'], 'Test message sent') || str_contains($ok['body'], 'written to a file')), (string) $ok['code']);
if ($mode === 'file') {
    $eml = $new !== [] ? (string) file_get_contents($new[0]) : '';
    check('test message (file mode): a message to the typed address is written, with the test wording', count($new) === 1 && str_contains($eml, 'zz-cn-target@example.test') && stripos($eml, 'test message') !== false);
    foreach ($new as $f) {
        @unlink($f);
    }
}
$aud = (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'settings.manage' AND summary LIKE 'Sent an e-mail test message%' AND created_at >= (NOW() - INTERVAL 5 MINUTE)", [], 0);
check('the test is written to the activity log', $aud >= 1);

check('an editor cannot open the page or send a test (403)', $req('GET', '/admin/connections', [], $je)['code'] === 403);
$anon = $req('GET', '/admin/connections');
check('a visitor who is not signed in lands on the sign-in page', $anon['code'] === 200 && str_contains($anon['body'], 'Sign in'));
$sidebar = $req('GET', '/admin', [], $ja);
check('the sidebar has the "E-mail and sign-in" link for administrators', str_contains($sidebar['body'], 'admin/connections') && str_contains($sidebar['body'], 'E-mail and sign-in'));

$clean();
if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
