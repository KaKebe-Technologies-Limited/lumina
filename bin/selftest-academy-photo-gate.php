<?php
declare(strict_types=1);

/**
 * LAF Academy: a PICTURE is needed to use the Academy, also for learners who signed up with Google (the Google sign-up never shows the form that asks for it). A Google account, or an
 * account made since pictures became required, without a picture is kept on "My profile > My picture" until one is saved; older accounts are not locked out (the reminder and deadline
 * in Admin > Learners deal with them); the Settings switch turns the rule off. Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-academy-photo-gate.php
 * Needs the local web server. Removes its rows (zz-pg-*) and puts the settings back.
 */

use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Core\Settings;
use App\Services\Academy\LearnerPortrait;

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (Config::get('app.env') !== 'local') {
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

echo "-- academy: picture needed, also with Google --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-pg-%'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$oldPhoto = Settings::get('academy_register_photo_required', null);
$oldFrom = Settings::get('academy_photo_gate_from', null);
Settings::set('academy_register_photo_required', '1');
Settings::set('academy_enabled', '1');
Settings::reset('academy_photo_gate_from');
Settings::flush();
check('pictures can be stored here (the columns exist)', LearnerPortrait::ready());

$mk = static function (string $tag, array $extra = []): int {
    return (int) Database::insert('academy_learners', $extra + [
        'reg_no' => 'LAF-AC-PG-' . strtoupper($tag), 'name' => 'Zz Pg ' . $tag, 'email' => 'zz-pg-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'),
        'email_verified_at' => date('Y-m-d H:i:s'), 'approved_at' => date('Y-m-d H:i:s'), 'is_active' => 1, 'phone' => '+256700123456', 'country' => 'Uganda', 'whatsapp' => '+256700123456',
    ]);
};
$g = $mk('g', ['google_sub' => 'g-123456']);                                   // Google account, no picture
$n = $mk('n');                                                                  // new account, no picture
$o = $mk('o', ['created_at' => '2026-10-01 09:00:00']);                         // account from before pictures were required, no picture
$p = $mk('p', ['photo_path' => 'zz/zz-pg.jpg']);                                // new account WITH a picture
$og = $mk('og', ['google_sub' => 'g-654321', 'created_at' => '2026-10-01 09:00:00']);   // an older Google account, no picture

check('rule: a Google account without a picture must add one', LearnerPortrait::mustAdd($g));
check('rule: an older Google account without a picture must add one too', LearnerPortrait::mustAdd($og));
check('rule: a new account without a picture must add one', LearnerPortrait::mustAdd($n));
check('rule: an account from before pictures were required is not locked out', !LearnerPortrait::mustAdd($o));
check('rule: an account that has a picture is fine', !LearnerPortrait::mustAdd($p));

// ---- the pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string $j = null) use ($base): array {
    $j ??= tempnam(sys_get_temp_dir(), 'pgj');
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
    return $ts . '.' . hash_hmac('sha256', $ts, Config::appKey());
};
$login = static function (string $email) use ($req, $tok, $hp): string {
    $j = tempnam(sys_get_temp_dir(), 'pgl');
    $p = $req('GET', '/academy/login', [], $j);
    $req('POST', '/academy/login', ['_token' => $tok($p['body']), '_hp_ts' => $hp(), 'email' => $email, 'password' => 'Secret-12345'], $j);
    return $j;
};
$screens = ['/academy/dashboard', '/academy/portal/courses', '/academy/portal/certificates', '/academy/portal/messages'];
$jars = [];
foreach (['g', 'n', 'o', 'p'] as $t) {
    $jars[$t] = $login('zz-pg-' . $t . '@example.test');
}

foreach (['g' => 'a Google account', 'n' => 'a new account'] as $t => $label) {
    $all = true;
    $detail = '';
    foreach ($screens as $s) {
        $r = $req('GET', $s, [], $jars[$t]);
        $ok = $r['code'] === 302 && str_contains($r['loc'], 'academy/account') && str_contains($r['loc'], 'picture');
        $all = $all && $ok;
        $detail .= $s . '=' . $r['code'] . ' ';
    }
    check('gate: ' . $label . ' without a picture is sent to "My picture" from every portal screen', $all, $detail);
}
$acc = $req('GET', '/academy/account', [], $jars['g']);
check('gate: the profile page itself opens and says why a picture is needed (also with Google)', $acc['code'] === 200 && str_contains($acc['body'], 'Add your picture to continue') && str_contains($acc['body'], 'id="picture"'), (string) $acc['code']);
$out = $req('POST', '/academy/logout', ['_token' => $tok($acc['body'])], $jars['g']);
check('gate: the learner can still sign out', in_array($out['code'], [302, 303, 200], true));

$old = $req('GET', '/academy/dashboard', [], $jars['o']);
check('no gate: an older account without a picture still opens the portal', $old['code'] === 200, (string) $old['code']);
$has = $req('GET', '/academy/dashboard', [], $jars['p']);
check('no gate: an account with a picture opens the portal', $has['code'] === 200, (string) $has['code']);

// ---- a picture saved: the gate opens
$jg = $login('zz-pg-g@example.test');
Database::execute("UPDATE academy_learners SET photo_path = 'zz/zz-pg.jpg' WHERE id = ?", [$g]);
$open = $req('GET', '/academy/dashboard', [], $jg);
check('once a picture is saved the Google learner reaches the portal', $open['code'] === 200, (string) $open['code']);
Database::execute('UPDATE academy_learners SET photo_path = NULL WHERE id = ?', [$g]);

// ---- the setting turns the rule off
Settings::set('academy_register_photo_required', '0');
Settings::flush();
$jg2 = $login('zz-pg-g@example.test');
$off = $req('GET', '/academy/dashboard', [], $jg2);
check('setting off: nobody is kept on the picture step', $off['code'] === 200, (string) $off['code']);
Settings::set('academy_register_photo_required', '1');
Settings::flush();

foreach ($jars as $f) {
    @unlink($f);
}
@unlink($jg);
@unlink($jg2);
$clean();
$oldPhoto === null ? Settings::reset('academy_register_photo_required') : Settings::set('academy_register_photo_required', (string) $oldPhoto);
$oldFrom === null ? Settings::reset('academy_photo_gate_from') : Settings::set('academy_photo_gate_from', (string) $oldFrom);
Settings::flush();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
