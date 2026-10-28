<?php
declare(strict_types=1);

/**
 * Academy: the 6-digit confirmation code (e-mail + optional WhatsApp). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-code.php
 * The WhatsApp message itself needs Meta credentials and is NOT sent here: the tests cover the number rules, the "not set up" behaviour and the code logic.
 * Removes its own rows (zz-lcd-*).
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
use App\Core\RateLimit;
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\VerificationCode;
use App\Services\Academy\WhatsAppSender;

Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lcd-%'");
Database::execute('DELETE FROM rate_limits');
$mk = static fn(string $tag): int => (int) Database::insert('academy_learners', [
    'name' => 'Zed Lcd ' . $tag, 'email' => 'zz-lcd-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'), 'email_verified_at' => null, 'is_active' => 1,
]);
$ip = '203.0.113.50';

check('code: the columns exist after the schema migration', VerificationCode::ready());

// ------------------------------------------------------------------------------------------------------------- WhatsApp numbers
$cc = (string) preg_replace('/\D+/', '', (string) (\App\Core\Env::get('WHATSAPP_COUNTRY_CODE', '') ?? ''));
check('whatsapp: +, spaces, dashes and 00 are accepted', WhatsAppSender::normalise('+256 772-123 456') === '256772123456' && WhatsAppSender::normalise('00256772123456') === '256772123456');
check('whatsapp: text, too short and too long numbers are refused', WhatsAppSender::normalise('abc') === null && WhatsAppSender::normalise('12345') === null && WhatsAppSender::normalise('+1234567890123456') === null);
check('whatsapp: a local number starting with 0 needs a default country code', $cc === '' ? WhatsAppSender::normalise('0772123456') === null : WhatsAppSender::normalise('0772123456') === $cc . '772123456');
check('whatsapp: without the credentials in .env the option is switched off and sending says so', !WhatsAppSender::enabled() && WhatsAppSender::sendCode('256772123456', '123456')['ok'] === false);
$off = VerificationCode::sendWhatsApp('zz-lcd-none@example.test', '+256772123456', $ip);
check('whatsapp: asking for a code while it is not set up gives a clear message', $off['ok'] === false && str_contains($off['shown'], 'not available'));

// ------------------------------------------------------------------------------------------------------------- the code
$a = $mk('a');
$code = (string) VerificationCode::issue($a);
check('code: a code has six digits and only its hash is stored', preg_match('/^\d{6}$/', $code) === 1 && !str_contains((string) Database::value('SELECT code_hash FROM academy_learners WHERE id = ?', [$a]), $code));
$bad = VerificationCode::check('zz-lcd-a@example.test', $code === '000000' ? '111111' : '000000', $ip);
check('code: a wrong code is refused with a generic message and counted', $bad['ok'] === false && (int) Database::value('SELECT code_attempts FROM academy_learners WHERE id = ?', [$a]) === 1);
check('code: an unknown address gets the same generic message', VerificationCode::check('zz-lcd-nobody@example.test', $code, $ip)['error'] === $bad['error']);
$ok = VerificationCode::check('ZZ-LCD-A@example.test ', ' ' . substr($code, 0, 3) . ' ' . substr($code, 3), $ip);
$row = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$a]);
check('code: the right code (spaces and capitals ignored) confirms the account', $ok['ok'] === true && $row['email_verified_at'] !== null && $row['code_hash'] === null);
check('code: it cannot be used twice', VerificationCode::check('zz-lcd-a@example.test', $code, $ip)['ok'] === false);

$b = $mk('b');
$c1 = (string) VerificationCode::issue($b);
$wrong = $c1 === '999999' ? '123456' : '999999';
for ($i = 0; $i < VerificationCode::MAX_TRIES; $i++) {
    VerificationCode::check('zz-lcd-b@example.test', $wrong, $ip);
}
$locked = VerificationCode::check('zz-lcd-b@example.test', $c1, $ip);
check('code: after 5 wrong tries even the right code is refused until a new one is requested', $locked['ok'] === false && str_contains((string) $locked['error'], 'new one'));
$c2 = (string) VerificationCode::issue($b);
check('code: a new code resets the tries and works', VerificationCode::check('zz-lcd-b@example.test', $c2, $ip)['ok'] === true);

$e = $mk('e');
$c3 = (string) VerificationCode::issue($e);
Database::update('academy_learners', ['code_expires_at' => date('Y-m-d H:i:s', time() - 60)], 'id = ?', [$e]);
check('code: an expired code is refused', VerificationCode::check('zz-lcd-e@example.test', $c3, $ip)['ok'] === false);
Database::update('academy_learners', ['is_active' => 0, 'code_expires_at' => date('Y-m-d H:i:s', time() + 3600)], 'id = ?', [$e]);
check('code: a switched-off account cannot confirm', VerificationCode::check('zz-lcd-e@example.test', $c3, $ip)['ok'] === false);

$f = $mk('f');
Database::execute('DELETE FROM rate_limits');
$many = true;
for ($i = 0; $i < 16; $i++) {
    $r = VerificationCode::check('zz-lcd-f@example.test', '000000', '203.0.113.' . (60 + $i));
    $many = $many && $r['ok'] === false;
}
check('code: trying one address from many places is stopped by the per-address limit', $many && str_contains((string) VerificationCode::check('zz-lcd-f@example.test', '000000', '203.0.113.99')['error'], 'Too many'));
Database::execute('DELETE FROM rate_limits');

// the confirmation e-mail carries the code (and the link)
$g = $mk('g');
$mailsBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$sent = LearnerAuth::sendConfirmation($g);
$new = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore));
$text = '';
if ($new !== []) {
    $raw = (string) file_get_contents($new[0]);
    $text = $raw;
    if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--)/s', $raw, $parts) > 0) {
        foreach ($parts[1] as $p) {
            $text .= "\n" . (string) base64_decode(preg_replace('/\s+/', '', $p));
        }
    }
}
check('e-mail: the confirmation e-mail contains a 6-digit code and the link', $sent['ok'] && preg_match('/confirmation code: (\d{6})/', $text, $m) === 1 && str_contains($text, '/academy/verify/') && str_contains($text, 'academy/check-email'));
check('e-mail: the code in the e-mail is the one that works', isset($m[1]) && VerificationCode::check('zz-lcd-g@example.test', $m[1], $ip)['ok'] === true);
foreach ($new as $nf) {
    @unlink($nf);
}

// ------------------------------------------------------------------------------------------------------------- over HTTP
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'lcj');
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
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m2) === 1 ? $m2[1] : '';
$post = static function (string $path, array $form, ?string &$jar, string $from) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    $ts = (string) (time() - 30);
    return $req('POST', $path, $form + ['_token' => $tok($page['body']), '_hp_ts' => $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey())], $jar);
};
$h = $mk('h');
$ch1 = (string) VerificationCode::issue($h);
$j = null;
$page = $req('GET', '/academy/check-email', [], $j);
check('http: the "check your e-mail" page asks for the 6-digit code and hides WhatsApp while it is not set up', $page['code'] === 200 && str_contains($page['body'], 'Type your 6-digit code') && !str_contains($page['body'], 'Get the code on WhatsApp'));
$reg = $req('GET', '/academy/register', [], $j);
check('http: the sign-up form has no WhatsApp field while it is not set up', $reg['code'] === 200 && !str_contains($reg['body'], 'name="whatsapp"'));
$wrongPost = $post('/academy/verify/code', ['email' => 'zz-lcd-h@example.test', 'code' => $ch1 === '123456' ? '654321' : '123456'], $j, '/academy/check-email');
check('http: a wrong code goes back to the page with an error', in_array($wrongPost['code'], [302, 303], true) && str_contains($wrongPost['head'], 'check-email'));
$okPost = $post('/academy/verify/code', ['email' => 'zz-lcd-h@example.test', 'code' => $ch1], $j, '/academy/check-email');
check('http: the right code signs the learner in and opens the dashboard', in_array($okPost['code'], [302, 303], true) && str_contains($okPost['head'], 'academy/dashboard') && $req('GET', '/academy/dashboard', [], $j)['code'] === 200);
check('http: the WhatsApp route answers "not available" instead of failing', in_array($post('/academy/verify/whatsapp', ['email' => 'zz-lcd-h@example.test', 'whatsapp' => '+256772123456'], $j, '/academy/check-email')['code'], [302, 303], true));

Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lcd-%'");
Database::execute('DELETE FROM rate_limits');
check('cleanup: no zz-lcd rows are left', (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-lcd-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
