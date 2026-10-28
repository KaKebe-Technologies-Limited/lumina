<?php
declare(strict_types=1);

/**
 * Academy: registration numbers, administrator approval, the notice about new accounts and the dashboard tile. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-registry.php
 * Needs the local web server (the sign-up form and the admin screens are used over HTTP). Removes its own rows (zz-lre-*).
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
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerRegistry;

$clean = static function (): void {
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lre-%'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-lre-%'");
    Database::execute("DELETE FROM audit_log WHERE summary LIKE '%zz-lre-%' OR summary LIKE '%Zed Lre%'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();

check('registry: the migration added reg_no, approved_at and approved_by', LearnerRegistry::ready());
check('registry: the number is LAF-AC-year-six digits', LearnerRegistry::format(123, '2026-10-28 10:00:00') === 'LAF-AC-2026-000123');

// ------------------------------------------------------------------------------------------------------------- the sign-up form (over HTTP)
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'lrj');
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
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$post = static function (string $path, array $form, ?string &$jar, string $from) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    $ts = (string) (time() - 30);
    $hp = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
    return $req('POST', $path, $form + ['_token' => $tok($page['body']), '_hp_ts' => $hp], $jar);
};
$jL = null;
$jP = null;
$pw = 'Sentence-for-test-77';
$reg = $post('/academy/register', ['name' => 'Zed Lre One', 'email' => 'zz-lre-one@example.test', 'password' => $pw, 'agree' => '1'], $jL, '/academy/register');
$one = Database::one("SELECT * FROM academy_learners WHERE email = 'zz-lre-one@example.test'");
check('sign-up: the account is created at once and the learner is sent to the "check your e-mail" page', $one !== null && in_array($reg['code'], [302, 303], true) && str_contains($reg['head'], 'check-email'));
check('sign-up: a registration number is generated automatically', $one !== null && preg_match('/^LAF-AC-\d{4}-\d{6}$/', (string) $one['reg_no']) === 1 && str_ends_with((string) $one['reg_no'], str_pad((string) $one['id'], 6, '0', STR_PAD_LEFT)));
$page = $req('GET', '/academy/check-email', [], $jL);
check('sign-up: the page shows the registration number and says approval does not need the e-mail', $one !== null && str_contains($page['body'], (string) $one['reg_no']) && str_contains($page['body'], 'approves your account'));
$login = $post('/academy/login', ['email' => 'zz-lre-one@example.test', 'password' => $pw], $jL, '/academy/login');
$flash = $req('GET', '/academy/login', [], $jL)['body'] . $login['body'];
check('sign-in: before approval the learner is told the account is waiting for approval', !str_contains($login['head'], 'academy/dashboard') && str_contains($flash, 'waiting for approval'));
$log = Database::one("SELECT * FROM audit_log WHERE action = 'academy.learner.register' AND summary LIKE '%Zed Lre One%' ORDER BY id DESC LIMIT 1");
check('notice: the new account is written to the activity log (shown on the dashboard)', $log !== null && str_contains((string) $log['summary'], (string) $one['reg_no']) && str_contains((string) $log['summary'], 'waiting for approval'));

// ------------------------------------------------------------------------------------------------------------- the administrator
$pwA = 'Admin lre pass 91';
\App\Models\User::create(['name' => 'ZZ Lre Admin', 'email' => 'zz-lre-admin@example.test', 'password_hash' => Auth::hashPassword($pwA), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
$jA = null;
$page = $req('GET', '/admin/login', [], $jA);
$r = $req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-lre-admin@example.test', 'password' => $pwA], $jA);
check('admin: the test administrator can sign in', in_array($r['code'], [302, 303], true));
$dash = $req('GET', '/admin', [], $jA)['body'];
check('dashboard: the tile "Academy learners waiting for approval" shows the waiting learners', str_contains($dash, 'Academy learners waiting for approval') && str_contains($dash, 'new this week'));
$list = $req('GET', '/admin/academy/learners?q=zz-lre', [], $jA)['body'];
check('list: shows the registration number, "Awaiting approval" and an Approve button on the row', str_contains($list, (string) $one['reg_no']) && str_contains($list, 'Awaiting approval') && str_contains($list, '/approve"'));
$bySearch = $req('GET', '/admin/academy/learners?q=' . urlencode((string) $one['reg_no']), [], $jA)['body'];
check('list: a learner can be found by registration number', str_contains($bySearch, 'zz-lre-one@'));
$fil = $req('GET', '/admin/academy/learners?status=unverified&q=zz-lre', [], $jA)['body'];
check('list: the "Awaiting approval" filter lists them', str_contains($fil, 'zz-lre-one@'));
$ap = $post('/admin/academy/learners/' . (int) $one['id'] . '/approve', ['back' => 'list'], $jA, '/admin/academy/learners?q=zz-lre');
$after = Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) $one['id']]);
$adm = (int) Database::value("SELECT id FROM users WHERE email = 'zz-lre-admin@example.test'", [], 0);
check('approve: the learner is approved with the time and the administrator recorded, no e-mail needed', $after['email_verified_at'] !== null && $after['approved_at'] !== null && (int) $after['approved_by'] === $adm);
$jL2 = null;
$login = $post('/academy/login', ['email' => 'zz-lre-one@example.test', 'password' => $pw], $jL2, '/academy/login');
check('approve: the approved learner can sign in and reach the dashboard', in_array($login['code'], [302, 303], true) && str_contains($login['head'], 'academy'));
check('approve: it is in the activity log', Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'academy.learner.approve' AND summary LIKE '%zz-lre-one@%'", [], 0) > 0);

// approve all
$ids = [];
foreach (['two', 'three'] as $t) {
    $ids[$t] = (int) Database::insert('academy_learners', ['name' => 'Zed Lre ' . $t, 'email' => 'zz-lre-' . $t . '@example.test', 'password_hash' => Auth::hashPassword($pw), 'email_verified_at' => null, 'is_active' => 1]);
}
$off = (int) Database::insert('academy_learners', ['name' => 'Zed Lre off', 'email' => 'zz-lre-off@example.test', 'password_hash' => Auth::hashPassword($pw), 'email_verified_at' => null, 'is_active' => 0]);
check('registry: a learner made any other way gets a number when asked', LearnerRegistry::assign($ids['two']) === LearnerRegistry::format($ids['two'], (string) Database::value('SELECT created_at FROM academy_learners WHERE id = ?', [$ids['two']])));
$ap = $post('/admin/academy/learners-confirm-all', [], $jA, '/admin/academy/learners');
$waitingAfter = (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-lre-%' AND is_active = 1 AND email_verified_at IS NULL", [], 0);
check('approve all: every waiting switched-on learner is approved, a switched-off one is left alone', $waitingAfter === 0 && Database::value('SELECT email_verified_at FROM academy_learners WHERE id = ?', [$off]) === null && Database::value('SELECT approved_at FROM academy_learners WHERE id = ?', [$ids['three']]) !== null);

// ------------------------------------------------------------------------------------------------------------- picture or selfie at sign-up
$regPage = $req('GET', '/academy/register', [], $jP);
check('picture: the sign-up form can send a file and offers the picture field with the selfie button', str_contains($regPage['body'], 'enctype="multipart/form-data"') && str_contains($regPage['body'], 'name="photo"') && str_contains($regPage['body'], 'data-selfie-open') && str_contains($regPage['body'], 'academy.js'));
check('picture: the browser may use the camera on this site (Permissions-Policy camera=(self))', str_contains($regPage['head'], 'camera=(self)'));
$postFile = static function (string $email, string $name, string $tmp, string $type, ?string &$jar) use ($base, $tok, $req): array {
    $page = $req('GET', '/academy/register', [], $jar);
    $ts = (string) (time() - 30);
    $hp = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
    $ch = curl_init($base . '/academy/register');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['_token' => $tok($page['body']), '_hp_ts' => $hp, 'name' => $name, 'email' => $email, 'password' => 'Sentence-for-test-77', 'agree' => '1', 'photo' => new CURLFile($tmp, $type, 'me.jpg')]]);
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'raw' => $raw];
};
$img = sys_get_temp_dir() . '/zz-lre-' . bin2hex(random_bytes(4)) . '.jpg';
copy(dirname(__DIR__) . '/assets/img/site/photo-02.jpg', $img);
$jP1 = null;
$r1 = $postFile('zz-lre-pic@example.test', 'Zed Lre Pic', $img, 'image/jpeg', $jP1);
$pic = Database::one("SELECT * FROM academy_learners WHERE email = 'zz-lre-pic@example.test'");
$abs = $pic !== null && (string) ($pic['photo_path'] ?? '') !== '' ? STORAGE_PATH . '/private/' . $pic['photo_path'] : '';
check('picture: a picture attached at sign-up is saved with the new account, privately (no consent to show it publicly)', in_array($r1['code'], [302, 303], true) && $abs !== '' && is_file($abs) && $pic['photo_consent_at'] === null && preg_match('/^LAF-AC-/', (string) $pic['reg_no']) === 1);
$bad = sys_get_temp_dir() . '/zz-lre-' . bin2hex(random_bytes(4)) . '.jpg';
file_put_contents($bad, 'this is not a picture');
$jP2 = null;
$r2 = $postFile('zz-lre-bad@example.test', 'Zed Lre Bad', $bad, 'image/jpeg', $jP2);
$badRow = Database::one("SELECT * FROM academy_learners WHERE email = 'zz-lre-bad@example.test'");
check('picture: a file that is not a picture never stops the sign-up (the account exists, no picture, the learner is told)', $badRow !== null && in_array($r2['code'], [302, 303], true) && ($badRow['photo_path'] ?? null) === null && str_contains($req('GET', '/academy/check-email', [], $jP2)['body'], 'picture could not be saved'));
$list = $req('GET', '/admin/academy/learners?q=zz-lre-pic', [], $jA)['body'];
check('picture: the administrator sees the portrait in the learner list', str_contains($list, '/photo"'));
@unlink($img);
@unlink($bad);
if ($abs !== '') {
    @unlink($abs);
}

// ------------------------------------------------------------------------------------------------------------- export + other doors
$csv = $req('GET', '/admin/academy/learners-export?q=zz-lre', [], $jA)['body'];
check('export: the learner list has a Reg no column with the numbers', str_contains($csv, 'Reg no') && str_contains($csv, (string) $one['reg_no']));
$google = LearnerAuth::registrationOpen() ? \App\Services\Academy\GoogleSignIn::account(['email' => 'zz-lre-google@example.test', 'sub' => 'zz-lre-sub', 'name' => 'Zed Lre Google']) : ['ok' => true, 'created' => false, 'learner' => ['reg_no' => 'x']];
check('google: an account made with Google gets a number too, is active at once and is announced', $google['ok'] && preg_match('/^LAF-AC-\d{4}-\d{6}$/', (string) ($google['learner']['reg_no'] ?? '')) === 1
    && Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'academy.learner.register' AND summary LIKE '%Zed Lre Google%'", [], 0) > 0);
$noAdmin = $req('GET', '/admin/academy/learners', [], $jL2);
check('access: a learner cannot open the admin learner list', in_array($noAdmin['code'], [302, 303, 401, 403], true));

$clean();
if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : "{$fail} check(s) FAILED.") . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
