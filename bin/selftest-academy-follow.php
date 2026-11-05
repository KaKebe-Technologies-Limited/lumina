<?php
declare(strict_types=1);

/**
 * LAF Academy: following and subscribing to our social media pages is REQUIRED to create an account (Settings > LAF Academy): every page has to be opened from the form (tracked
 * links), the usernames are asked, the tick unlocks, and the team verifies the learner (Admin > Academy > Social follow check). Also the follow step after a Google sign-up.
 * Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-academy-follow.php
 * Needs the local web server and database/updates/2026-11-academy-social-follow.sql. Removes its rows (zz-fw-*) and puts the settings back.
 */

use App\Core\Database;
use App\Core\Settings;
use App\Services\Academy\AcademySocial;
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
    Database::execute("DELETE FROM academy_social WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-fw-%@example.test')");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-fw-%@example.test'");
    Database::execute("DELETE FROM users WHERE email = 'zz-fw-admin@example.test'");
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

$pages = AcademySocial::pages();
check('the social media pages are set (Admin > Social media), the table exists and the rule is on by default', $pages !== [] && AcademySocial::ready() && LearnerAuth::followRequired(), count($pages) . ' pages');
check('usernames are asked for the platforms that have a page (Instagram, TikTok and X are required)', array_intersect(['instagram', 'tiktok', 'x'], AcademySocial::fields()) !== [], implode(',', AcademySocial::fields()));

// ---- the username reader
check('normaliseHandle: @name, a profile link and plain names all give the bare username', AcademySocial::normaliseHandle('@Zed_Fw') === 'Zed_Fw' && AcademySocial::normaliseHandle('https://www.instagram.com/zed.fw/?hl=en') === 'zed.fw' && AcademySocial::normaliseHandle('https://www.tiktok.com/@zedfw') === 'zedfw' && AcademySocial::normaliseHandle('x.com/zedfw') === 'zedfw' && AcademySocial::normaliseHandle('  zedfw ') === 'zedfw');
check('normaliseHandle: rubbish gives nothing', AcademySocial::normaliseHandle('') === '' && AcademySocial::normaliseHandle('a') === '' && AcademySocial::normaliseHandle('two words') === '' && AcademySocial::normaliseHandle('@@@') === '');
check('profileUrl: the link an administrator opens to look the learner up', AcademySocial::profileUrl('instagram', 'zed.fw') === 'https://www.instagram.com/zed.fw/' && AcademySocial::profileUrl('tiktok', 'zedfw') === 'https://www.tiktok.com/@zedfw' && AcademySocial::profileUrl('facebook', 'x') === '');

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
$handlesOk = ['handle_instagram' => '@zed.fw', 'handle_tiktok' => 'https://www.tiktok.com/@zedfw', 'handle_x' => 'zedfw', 'handle_facebook' => 'zedfw.fb'];
$openAll = static function (string $j) use ($req, $pages): void {
    foreach (array_keys($pages) as $k) {
        $req('GET', '/academy/go/' . $k, [], $j);
    }
};
$register = static function (string $tag, bool $open, bool $follow, array $handles, string $j) use ($req, $tok, $hp, $openAll): array {
    $page = $req('GET', '/academy/register', [], $j);
    if ($open) {
        $openAll($j);
    }
    $form = ['_token' => $tok($page['body']), '_hp_ts' => $hp(), 'name' => 'Zed Fw ' . $tag, 'email' => 'zz-fw-' . $tag . '@example.test', 'password' => 'Sentence-for-test-77',
        'phone' => '+256700' . random_int(100000, 999999), 'country' => 'Uganda', 'whatsapp' => '+256700' . random_int(100000, 999999), 'agree' => '1'] + $handles;
    if ($follow) {
        $form['follow'] = '1';
    }
    return $req('POST', '/academy/register', $form, $j);
};
$exists = static fn(string $tag): bool => Database::value('SELECT 1 FROM academy_learners WHERE email = ?', ['zz-fw-' . $tag . '@example.test']) !== null;
$newJar = static fn(): string => tempnam(sys_get_temp_dir(), 'fwn');

// ---- tracked links
$go = $req('GET', '/academy/go/' . array_key_first($pages));
check('a page link goes through /academy/go/{page} and sends the visitor on to the real page', $go['code'] === 302 && $go['loc'] === $pages[array_key_first($pages)]['url'], $go['code'] . ' ' . $go['loc']);
check('an unknown page is a 404 (no open redirect)', $req('GET', '/academy/go/not-a-page')['code'] === 404 && $req('GET', '/academy/go/' . rawurlencode('https://evil.example'))['code'] === 404);

$j = $newJar();
$form = $req('GET', '/academy/register', [], $j);
$allGo = true;
foreach (array_keys($pages) as $k) {
    $allGo = $allGo && str_contains($form['body'], '/academy/go/' . $k);
}
check('the sign-up form shows every page as a tracked link, the username fields and the tick', $form['code'] === 200 && $allGo && str_contains($form['body'], 'name="handle_instagram"') && str_contains($form['body'], 'name="follow"') && str_contains($form['body'], 'data-follow'), (string) $form['code']);
$panel = (string) preg_replace('/^.*?data-follow(?:\s|>).*?(<ul class="ac-follow__links">.*?<\/ul>).*$/s', '$1', $form['body']);
check('inside the follow panel the links are the tracked ones, not the real page addresses', str_contains($panel, '/academy/go/') && !str_contains($panel, 'href="' . $pages[array_key_first($pages)]['url'] . '"'));

$r = $register('closed', false, true, $handlesOk, $newJar());
check('ticking WITHOUT having opened the pages is refused and NO account is made', $r['code'] === 302 && str_contains($r['loc'], 'academy/register') && !$exists('closed'), $r['code'] . ' ' . $r['loc']);
$r = $register('nohandle', true, true, [], $newJar());
check('pages opened and ticked but NO usernames: refused and no account', $r['code'] === 302 && !$exists('nohandle'));
$r = $register('badhandle', true, true, ['handle_instagram' => 'not valid!', 'handle_tiktok' => 'zedfw', 'handle_x' => 'zedfw'], $newJar());
check('a username that cannot be one is refused and no account is made', $r['code'] === 302 && !$exists('badhandle'));
$r = $register('notick', true, false, $handlesOk, $newJar());
check('pages opened, usernames given but the tick missing: refused and no account', $r['code'] === 302 && !$exists('notick'));
$r = $register('yes', true, true, $handlesOk, $newJar());
check('pages opened + usernames + tick: the account is created', $exists('yes'), $r['code'] . ' ' . $r['loc']);

$id = (int) Database::value("SELECT id FROM academy_learners WHERE email = 'zz-fw-yes@example.test'", [], 0);
$row = Database::one('SELECT handles, opened, verified_at FROM academy_social WHERE learner_id = ?', [$id]);
$h = json_decode((string) ($row['handles'] ?? ''), true) ?: [];
check('the usernames are kept clean (no @, no link) with the pages opened, not verified yet', ($h['instagram'] ?? '') === 'zed.fw' && ($h['tiktok'] ?? '') === 'zedfw' && ($h['x'] ?? '') === 'zedfw' && $row !== null && $row['verified_at'] === null && count(json_decode((string) $row['opened'], true) ?: []) === count($pages), json_encode($row));

// ---- switched off
Settings::set('academy_register_follow_required', '0');
Settings::flush();
$j = $newJar();
$page = $req('GET', '/academy/register', [], $j);
check('switched off: no tick and no username fields on the form, an account can be made without them', !str_contains($page['body'], 'name="follow"') && !str_contains($page['body'], 'name="handle_instagram"') && $register('off', false, false, [], $j)['code'] === 302 && $exists('off'));
Settings::set('academy_register_follow_required', '1');
Settings::flush();

// ---- the follow step after a Google sign-up
$j2 = $newJar();
$pg = $req('GET', '/academy/follow', [], $j2);
check('the follow step needs a signed-in learner (others go to the sign-in page)', $pg['code'] === 302 && str_contains($pg['loc'], 'academy/login'), $pg['code'] . ' ' . $pg['loc']);
Database::execute("UPDATE academy_learners SET email_verified_at = NOW(), approved_at = NOW() WHERE email = 'zz-fw-yes@example.test'");
$lp = $req('GET', '/academy/login', [], $j2);
$req('POST', '/academy/login', ['_token' => $tok($lp['body']), '_hp_ts' => $hp(), 'email' => 'zz-fw-yes@example.test', 'password' => 'Sentence-for-test-77'], $j2);
$pg = $req('GET', '/academy/follow', [], $j2);
check('signed in: the follow step shows the pages, the usernames and the tick', $pg['code'] === 200 && str_contains($pg['body'], 'One last step') && str_contains($pg['body'], 'name="follow"') && str_contains($pg['body'], 'name="handle_x"'), (string) $pg['code']);
$bad = $req('POST', '/academy/follow', ['_token' => $tok($pg['body']), 'follow' => '1'] + $handlesOk, $j2);
check('the follow step is refused while the pages were not opened', $bad['code'] === 302 && str_contains($bad['loc'], 'academy/follow'), $bad['code'] . ' ' . $bad['loc']);
$openAll($j2);
$pg = $req('GET', '/academy/follow', [], $j2);
$good = $req('POST', '/academy/follow', ['_token' => $tok($pg['body']), 'follow' => '1'] + ['handle_instagram' => 'zed.fw2', 'handle_tiktok' => 'zedfw2', 'handle_x' => 'zedfw2'], $j2);
check('pages opened + usernames + tick: the learner goes on (to the dashboard) and the usernames are saved', $good['code'] === 302 && str_contains($good['loc'], 'academy/dashboard') && (string) Database::value('SELECT handles FROM academy_social WHERE learner_id = ?', [$id], '') !== '' && str_contains((string) Database::value('SELECT handles FROM academy_social WHERE learner_id = ?', [$id], ''), 'zed.fw2'), $good['code'] . ' ' . $good['loc']);

// ---- the admin check
$apw = 'Follow admin pass 71';
\App\Models\User::create(['name' => 'ZZ Fw Admin', 'email' => 'zz-fw-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$ja = $newJar();
$lp = $req('GET', '/admin/login', [], $ja);
$req('POST', '/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-fw-admin@example.test', 'password' => $apw], $ja);
$r = $req('GET', '/admin/academy/social?q=zz-fw', [], $ja);
check('admin: the Social follow check lists the learner with a link to the Instagram profile and "To check"', $r['code'] === 200 && str_contains($r['body'], 'Zed Fw yes') && str_contains($r['body'], 'instagram.com/zed.fw2') && str_contains($r['body'], 'To check'), (string) $r['code']);
$req('POST', '/admin/academy/social/' . $id . '/verify', ['_token' => $tok($r['body'])], $ja);
check('admin: "Mark verified" sets the mark', Database::value('SELECT verified_at FROM academy_social WHERE learner_id = ?', [$id]) !== null);
$r = $req('GET', '/admin/academy/social?status=verified&q=zz-fw', [], $ja);
check('admin: the Verified filter shows the learner', str_contains($r['body'], 'Zed Fw yes') && str_contains($r['body'], 'Remove mark'));
$req('POST', '/admin/academy/social/' . $id . '/unverify', ['_token' => $tok($r['body'])], $ja);
check('admin: "Remove mark" clears it', Database::value('SELECT verified_at FROM academy_social WHERE learner_id = ?', [$id]) === null);
$rep = \App\Services\Reports\ReportCatalog::all()['academy-social-follow'] ?? null;
$res = $rep !== null ? \App\Services\Reports\ReportCatalog::run($rep, ['from' => null, 'to' => null], 200) : ['error' => 'missing', 'rows' => []];
check('the report "Social follow check" runs and lists the usernames', $res['error'] === null && in_array('zedfw2', array_column($res['rows'], 'tiktok'), true), (string) ($res['error'] ?? ''));

foreach ([$jar, $j, $j2, $ja] as $f) {
    @unlink($f);
}
Database::execute("DELETE FROM audit_log WHERE user_name = 'ZZ Fw Admin'");
$clean();
foreach ($before as $k => $v) {
    $v === null ? Settings::reset($k) : Settings::set($k, (string) $v);
}
Settings::flush();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
