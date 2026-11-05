<?php
declare(strict_types=1);

/**
 * LAF Academy: following and subscribing to our social media pages is REQUIRED to create an account (Settings > LAF Academy), and asked after a Google sign-up.
 * Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-academy-follow.php
 * Needs the local web server. Removes its rows (zz-fw-*) and puts the settings back.
 */

use App\Core\Database;
use App\Core\Settings;
use App\Services\Academy\LearnerAuth;

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

echo "-- academy: follow our pages --\n";
$keys = ['academy_register_follow_required', 'academy_register_photo_required', 'academy_signup_mode', 'academy_registration_open', 'academy_signup_fee', 'academy_enabled'];
$before = [];
foreach ($keys as $k) {
    $before[$k] = Settings::isCustomised($k) ? Settings::get($k) : null;
}
$clean = static function (): void {
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-fw-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
Settings::set('academy_enabled', '1');
Settings::set('academy_registration_open', '1');
Settings::set('academy_signup_mode', 'approval');
Settings::set('academy_register_photo_required', '0');
Settings::reset('academy_signup_fee');
Settings::reset('academy_register_follow_required');
Settings::flush();

$links = social_links('all');
check('the social media pages are set (Admin > Social media) and the follow rule is on by default', $links !== [] && LearnerAuth::followRequired(), count($links) . ' pages');
Settings::set('academy_register_follow_required', '0');
Settings::flush();
check('switched off, the rule is off', !LearnerAuth::followRequired());
Settings::set('academy_register_follow_required', '1');
Settings::flush();

$base = 'http://localhost/' . basename(dirname(__DIR__));
$jar = tempnam(sys_get_temp_dir(), 'fwj');
$req = static function (string $method, string $path, array $form = [], ?string $j = null) use ($base, $jar): array {
    $j ??= $jar;
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'body' => substr($raw, $hs), 'loc' => preg_match('/^Location:\s*(.+)$/mi', $head, $m) === 1 ? trim($m[1]) : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$hp = static function (): string {
    $ts = (string) (time() - 30);
    return $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
};
$register = static function (string $tag, bool $follow) use ($req, $tok, $hp): array {
    $page = $req('GET', '/academy/register');
    $form = ['_token' => $tok($page['body']), '_hp_ts' => $hp(), 'name' => 'Zed Fw ' . $tag, 'email' => 'zz-fw-' . $tag . '@example.test', 'password' => 'Sentence-for-test-77',
        'phone' => '+256700' . random_int(100000, 999999), 'country' => 'Uganda', 'whatsapp' => '+256700' . random_int(100000, 999999), 'agree' => '1'];
    if ($follow) {
        $form['follow'] = '1';
    }
    return $req('POST', '/academy/register', $form);
};
$exists = static fn(string $tag): bool => Database::value('SELECT 1 FROM academy_learners WHERE email = ?', ['zz-fw-' . $tag . '@example.test']) !== null;

$page = $req('GET', '/academy/register');
$allLinks = true;
foreach ($links as $l) {
    $allLinks = $allLinks && str_contains($page['body'], $l['url']);
}
check('the sign-up form shows every social media page as a link, opening in a new tab, and the required tick', $page['code'] === 200 && $allLinks && str_contains($page['body'], 'Follow and subscribe to our pages') && str_contains($page['body'], 'name="follow"') && str_contains($page['body'], 'noopener'), (string) $page['code']);

$r = $register('no', false);
check('without the tick the sign-up is refused (back to the form) and NO account is made', $r['code'] === 302 && str_contains($r['loc'], 'academy/register') && !$exists('no'), $r['code'] . ' ' . $r['loc']);
$f = $req('GET', '/academy/register');
check('...and the form explains what to do', str_contains($f['body'], 'follow and subscribe to our pages, then tick'));
$r = $register('yes', true);
check('with the tick the account is created', $exists('yes'), $r['code'] . ' ' . $r['loc']);

Settings::set('academy_register_follow_required', '0');
Settings::flush();
$page = $req('GET', '/academy/register');
check('switched off: no follow panel on the form and an account can be made without the tick', !str_contains($page['body'], 'name="follow"') && $register('off', false)['code'] === 302 && $exists('off'));
Settings::set('academy_register_follow_required', '1');
Settings::flush();

// ---- the step after a Google sign-up: a signed-in learner opens /academy/follow
$j2 = tempnam(sys_get_temp_dir(), 'fw2');
$pg = $req('GET', '/academy/follow', [], $j2);
check('the follow step needs a signed-in learner (others go to the sign-in page)', $pg['code'] === 302 && str_contains($pg['loc'], 'academy/login'), $pg['code'] . ' ' . $pg['loc']);
Database::execute("UPDATE academy_learners SET email_verified_at = NOW(), approved_at = NOW() WHERE email = 'zz-fw-yes@example.test'");
$lp = $req('GET', '/academy/login', [], $j2);
$req('POST', '/academy/login', ['_token' => $tok($lp['body']), '_hp_ts' => $hp(), 'email' => 'zz-fw-yes@example.test', 'password' => 'Sentence-for-test-77'], $j2);
$pg = $req('GET', '/academy/follow', [], $j2);
check('signed in: the follow step shows the pages and the tick', $pg['code'] === 200 && str_contains($pg['body'], 'One last step') && str_contains($pg['body'], 'name="follow"'), (string) $pg['code']);
$bad = $req('POST', '/academy/follow', ['_token' => $tok($pg['body'])], $j2);
check('the follow step is not left without the tick', $bad['code'] === 302 && str_contains($bad['loc'], 'academy/follow'), $bad['code'] . ' ' . $bad['loc']);
$pg = $req('GET', '/academy/follow', [], $j2);
$good = $req('POST', '/academy/follow', ['_token' => $tok($pg['body']), 'follow' => '1'], $j2);
check('with the tick the learner goes on (to the dashboard)', $good['code'] === 302 && str_contains($good['loc'], 'academy/dashboard'), $good['code'] . ' ' . $good['loc']);
@unlink($j2);
@unlink($jar);

$clean();
foreach ($before as $k => $v) {
    $v === null ? Settings::reset($k) : Settings::set($k, (string) $v);
}
Settings::flush();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
