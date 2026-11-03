<?php
declare(strict_types=1);

/**
 * LAF Academy: the registration number warning screen, the "Track my application" page and sign-in with the registration number.
 * Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-academy-track.php
 * Needs the local web server. Removes its rows (zz-trk-*) and puts the settings back.
 */

use App\Core\Database;
use App\Core\Settings;
use App\Services\Academy\LearnerRegistry;

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

echo "-- academy: registration number, tracking and sign-in --\n";
$keys = ['academy_register_photo_required', 'academy_signup_mode', 'academy_registration_open', 'academy_signup_fee', 'academy_enabled'];
$before = [];
foreach ($keys as $k) {
    $before[$k] = Settings::isCustomised($k) ? Settings::get($k) : null;
}
$clean = static function (): void {
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-trk-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
Settings::set('academy_enabled', '1');
Settings::set('academy_registration_open', '1');
Settings::set('academy_register_photo_required', '0');
Settings::reset('academy_signup_fee');
Settings::set('academy_signup_mode', 'approval');
Settings::flush();

// ---- the number
check('normaliseNumber: case, spaces and missing dashes are forgiven', LearnerRegistry::normaliseNumber(' laf-ac-2026-000123 ') === 'LAF-AC-2026-000123' && LearnerRegistry::normaliseNumber('LAFAC2026000123') === 'LAF-AC-2026-000123' && LearnerRegistry::normaliseNumber('LAF AC 2026 000123') === 'LAF-AC-2026-000123');
check('normaliseNumber: rubbish and e-mail addresses are not numbers', LearnerRegistry::normaliseNumber('hello') === '' && LearnerRegistry::normaliseNumber('a@b.co') === '' && LearnerRegistry::normaliseNumber('LAF-AC-26-1') === '');

$now = date('Y-m-d H:i:s');
$hash = password_hash('Track-pass-77', PASSWORD_DEFAULT);
$mk = static function (string $tag, array $extra) use ($hash): array {
    $id = (int) Database::insert('academy_learners', ['name' => 'Zed Trk ' . $tag, 'email' => 'zz-trk-' . $tag . '@example.test', 'password_hash' => $hash, 'phone' => '+256700000000', 'country' => 'Uganda', 'whatsapp' => '256700000000'] + $extra + ['is_active' => 1]);
    return [$id, LearnerRegistry::assign($id)];
};
[$okId, $okNo] = $mk('ok', ['email_verified_at' => $now, 'approved_at' => $now]);
[$waitId, $waitNo] = $mk('wait', []);
[$offId, $offNo] = $mk('off', ['email_verified_at' => $now, 'is_active' => 0]);
[$dueId, $dueNo] = $mk('due', ['fee_status' => 'due']);
check('the learners got registration numbers', preg_match('/^LAF-AC-\d{4}-\d{6}$/', $okNo) === 1 && $waitNo !== '' && $offNo !== '' && $dueNo !== '');

$t = LearnerRegistry::track($okNo);
check('track: an approved, active account', $t['found'] && $t['headline'] === 'Approved and active' && $t['tone'] === 'good' && $t['can_sign_in'] === true, json_encode($t['headline']));
$t = LearnerRegistry::track(strtolower($waitNo));
check('track: an account waiting for approval (the number is read in lower case too)', $t['found'] && $t['headline'] === 'Waiting for approval' && $t['can_sign_in'] === false);
check('track: a switched-off account', LearnerRegistry::track($offNo)['headline'] === 'Switched off');
check('track: an account with an unpaid sign-up fee', LearnerRegistry::track($dueNo)['headline'] === 'Waiting for the sign-up fee');
check('track: a number that does not exist, and rubbish, are "not found"', !LearnerRegistry::track('LAF-AC-2026-999999')['found'] && !LearnerRegistry::track('zzz')['found']);
check('track: nothing personal is returned (no name, e-mail or phone)', !str_contains(json_encode(LearnerRegistry::track($okNo)), 'Zed') && !str_contains(json_encode(LearnerRegistry::track($okNo)), 'zz-trk') && !str_contains(json_encode(LearnerRegistry::track($okNo)), '256700000000'));

// ---- the pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
$jar = tempnam(sys_get_temp_dir(), 'trj');
$req = static function (string $method, string $path, array $form = [], ?string $cookieJar = null) use ($base, $jar): array {
    $j = $cookieJar ?? $jar;
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
$postForm = static function (string $path, array $form, string $from) use ($req, $tok, $hp): array {
    $page = $req('GET', $from);
    return $req('POST', $path, $form + ['_token' => $tok($page['body']), '_hp_ts' => $hp()]);
};

$r = $req('GET', '/academy/track');
check('track page: opens (200) with the number field', $r['code'] === 200 && str_contains($r['body'], 'name="number"') && str_contains($r['body'], 'Track my application'), (string) $r['code']);
$r = $postForm('/academy/track', ['number' => $okNo], '/academy/track');
check('track page: an approved number shows "Approved and active" and a Sign in button', $r['code'] === 200 && str_contains($r['body'], 'Approved and active') && str_contains($r['body'], 'academy/login'), (string) $r['code']);
check('track page: no name or e-mail is shown', !str_contains($r['body'], 'Zed Trk') && !str_contains($r['body'], 'zz-trk-ok'));
$r = $postForm('/academy/track', ['number' => $waitNo], '/academy/track');
check('track page: a waiting number shows "Waiting for approval"', str_contains($r['body'], 'Waiting for approval'));
$r = $postForm('/academy/track', ['number' => 'LAF-AC-2026-999999'], '/academy/track');
check('track page: an unknown number shows "No account found"', str_contains($r['body'], 'No account found'));
$r = $postForm('/academy/track', ['number' => 'not a number'], '/academy/track');
check('track page: rubbish is refused with a hint about the format', $r['code'] === 302 && str_contains($r['loc'], 'academy/track'), $r['code'] . ' ' . $r['loc']);

// ---- sign in with the registration number
$j2 = tempnam(sys_get_temp_dir(), 'tr2');
$login = static function (string $id, string $pw) use ($req, $tok, $hp, $j2): array {
    $page = $req('GET', '/academy/login', [], $j2);
    return $req('POST', '/academy/login', ['_token' => $tok($page['body']), '_hp_ts' => $hp(), 'email' => $id, 'password' => $pw], $j2);
};
$page = $req('GET', '/academy/login', [], $j2);
check('login page: asks for an e-mail address or a registration number and links to the tracker', str_contains($page['body'], 'registration number') && str_contains($page['body'], 'academy/track'));
$r = $login($okNo, 'Track-pass-77');
check('login: the registration number works instead of the e-mail address', $r['code'] === 302 && !str_contains($r['loc'], 'academy/login'), $r['code'] . ' ' . $r['loc']);
$r = $req('GET', '/academy/dashboard', [], $j2);
check('login: ...and the learner is signed in (dashboard opens)', $r['code'] === 200, (string) $r['code']);
@unlink($j2);
$j3 = tempnam(sys_get_temp_dir(), 'tr3');
$page = $req('GET', '/academy/login', [], $j3);
$r = $req('POST', '/academy/login', ['_token' => $tok($page['body']), '_hp_ts' => $hp(), 'email' => strtolower($okNo), 'password' => 'wrong-password-1'], $j3);
check('login: a wrong password with the number is refused', $r['code'] !== 302 || str_contains($r['loc'], 'academy/login'), $r['code'] . ' ' . $r['loc']);
@unlink($j3);

// ---- the warning screen after sign-up
$jr = tempnam(sys_get_temp_dir(), 'trr');
$post = static function (string $tag) use ($req, $tok, $hp, &$jr): array {
    $page = $req('GET', '/academy/register', [], $jr);
    return $req('POST', '/academy/register', ['_token' => $tok($page['body']), '_hp_ts' => $hp(), 'name' => 'Zed Trk ' . $tag, 'email' => 'zz-trk-' . $tag . '@example.test', 'password' => 'Sentence-for-test-77',
        'phone' => '+256700' . random_int(100000, 999999), 'country' => 'Uganda', 'whatsapp' => '+256700' . random_int(100000, 999999), 'agree' => '1'], $jr);
};
$r = $post('new1');
$row = Database::one("SELECT reg_no FROM academy_learners WHERE email = 'zz-trk-new1@example.test'");
check('sign-up (approval mode): the account has a registration number', $row !== null && preg_match('/^LAF-AC-\d{4}-\d{6}$/', (string) $row['reg_no']) === 1 && str_contains($r['loc'], 'academy/pending'), $r['code'] . ' ' . $r['loc']);
$pg = $req('GET', '/academy/pending', [], $jr);
check('the "application received" page shows the WARNING with the number, the copy button and the tracker link', str_contains($pg['body'], 'Important: copy and keep this number') && str_contains($pg['body'], (string) $row['reg_no']) && str_contains($pg['body'], 'data-copy-regno') && str_contains($pg['body'], 'academy/track'));

Settings::set('academy_signup_mode', 'open');
Settings::flush();
$jr2 = $jr;
@unlink($jr);
$jr = tempnam(sys_get_temp_dir(), 'trr');
$r = $post('new2');
$row2 = Database::one("SELECT reg_no FROM academy_learners WHERE email = 'zz-trk-new2@example.test'");
check('sign-up (open mode): goes to the warning screen first', $row2 !== null && str_contains($r['loc'], 'academy/registered'), $r['code'] . ' ' . $r['loc']);
$pg = $req('GET', '/academy/registered', [], $jr);
check('the warning screen shows the number, the copy button, the "I have saved it" tick and Continue', $pg['code'] === 200 && str_contains($pg['body'], (string) $row2['reg_no']) && str_contains($pg['body'], 'data-copy-regno') && str_contains($pg['body'], 'data-regno-saved') && str_contains($pg['body'], 'academy/registered/continue'), (string) $pg['code']);
$go = $req('GET', '/academy/registered/continue', [], $jr);
check('Continue leads on to the learner dashboard', $go['code'] === 302 && str_contains($go['loc'], 'academy/dashboard'), $go['code'] . ' ' . $go['loc']);
$again = $req('GET', '/academy/registered', [], $jr);
check('the warning screen cannot be opened again without a new sign-up (back to the Academy page)', $again['code'] === 302);
$cold = $req('GET', '/academy/registered', [], tempnam(sys_get_temp_dir(), 'trc'));
check('a visitor with no sign-up cannot open the warning screen', $cold['code'] === 302);
@unlink($jr);
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
