<?php
/**
 * Program STAGE (planned / ongoing / completed) changed from Admin > Programs, included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-program-status.php
 * Covers: the stage buttons on the list, the one-click change (saved, audited, flash), refusal of an unknown stage and of a user without the content ability, and that
 * the PUBLIC program page, the Programs page and the home page show the new stage at once. Removes its own rows (zz-ps-*).
 */
declare(strict_types=1);

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit("Refusing to run outside APP_ENV=local.\n");
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
use App\Models\Program;
use App\Models\User;

echo "-- program stage --\n";
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'psj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 40, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs), 'head' => substr($raw, 0, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$login = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    return in_array($req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar)['code'], [302, 303], true);
};
$clean = static function (): void {
    Database::execute("DELETE FROM programs WHERE slug LIKE 'zz-ps-%'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-ps-%@example.test'");
    Database::execute("DELETE FROM audit_log WHERE action = 'program.status' AND summary LIKE '%zz-ps%'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$mk = static fn(string $name, string $email, string $role, string $pw): int => (int) User::create(['name' => $name, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$mk('ZZ PS Admin', 'zz-ps-admin@example.test', 'admin', 'Admin test pass 61');
$mk('ZZ PS Teacher', 'zz-ps-teacher@example.test', 'facilitator', 'Teacher test pass 62');
$pid = Database::insert('programs', ['slug' => 'zz-ps-program', 'title' => 'zz-ps Test program', 'tagline' => 'A test', 'summary' => 'A test program.', 'status' => 'planned', 'is_active' => 1, 'sort_order' => -50]);
$jA = $jT = null;
check('admin and a teaching-only user can sign in', $login('zz-ps-admin@example.test', 'Admin test pass 61', $jA) && $login('zz-ps-teacher@example.test', 'Teacher test pass 62', $jT));

$list = $req('GET', '/admin/programs?q=zz-ps', [], $jA);
check('the list shows the three stage buttons for the program, with the current one marked', $list['code'] === 200 && str_contains($list['body'], 'stage-switch') && str_contains($list['body'], 'name="status" value="ongoing"')
    && str_contains($list['body'], 'stage-switch__btn--planned is-current'));
$post = static function (string $path, array $form, ?string &$jar, string $from) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    return $req('POST', $path, $form + ['_token' => $tok($page['body'])], $jar);
};
$r = $post('/admin/programs/' . $pid . '/status', ['status' => 'ongoing', 'return' => 'q=zz-ps'], $jA, '/admin/programs?q=zz-ps');
check('one click saves the new stage and returns to the same list', in_array($r['code'], [302, 303], true) && str_contains($r['head'], 'q=zz-ps') && Database::value('SELECT status FROM programs WHERE id = ?', [$pid]) === 'ongoing');
$label = Program::statusLabels()['ongoing'];
$pub = $req('GET', '/programs/zz-ps-program');
check('the public program page shows the new stage at once', $pub['code'] === 200 && str_contains($pub['body'], $label), $label);
$idx = $req('GET', '/programs');
check('the Programs page card shows it too', $idx['code'] === 200 && str_contains($idx['body'], 'zz-ps Test program') && str_contains($idx['body'], $label));
$post('/admin/programs/' . $pid . '/status', ['status' => 'completed'], $jA, '/admin/programs');
$done = Program::statusLabels()['completed'];
check('completed is reflected on the public page, and the previous label is gone', str_contains($req('GET', '/programs/zz-ps-program')['body'], $done) && !str_contains($req('GET', '/programs/zz-ps-program')['body'], 'Planned program area'));
check('the change is written to the audit trail', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'program.status' AND summary LIKE '%zz-ps Test program%'", [], 0) >= 2);
$post('/admin/programs/' . $pid . '/status', ['status' => 'bogus'], $jA, '/admin/programs');
check('an unknown stage is refused and nothing changes', Database::value('SELECT status FROM programs WHERE id = ?', [$pid]) === 'completed');
$t = $post('/admin/programs/' . $pid . '/status', ['status' => 'planned'], $jT, '/admin/academy');
check('a user without the content ability cannot change it (403) and nothing changes', $t['code'] === 403 && Database::value('SELECT status FROM programs WHERE id = ?', [$pid]) === 'completed');
$anon = $req('POST', '/admin/programs/' . $pid . '/status', ['status' => 'planned']);
check('an anonymous request is turned away', $anon['code'] !== 200 && Database::value('SELECT status FROM programs WHERE id = ?', [$pid]) === 'completed');
$post('/admin/programs/' . $pid . '/status', ['status' => 'planned'], $jA, '/admin/programs');
check('it can go back to planned', Database::value('SELECT status FROM programs WHERE id = ?', [$pid]) === 'planned');
$post('/admin/programs/' . $pid . '/status', ['status' => 'ongoing'], $jA, '/admin/programs');
check('the home page shows the stage of a highlighted program', str_contains($req('GET', '/')['body'], 'zz-ps Test program') ? str_contains($req('GET', '/')['body'], $label) : true);

$clean();
check('cleanup: no zz-ps rows are left', Database::value("SELECT COUNT(*) FROM programs WHERE slug LIKE 'zz-ps-%'", [], 0) === 0 && Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-ps-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
