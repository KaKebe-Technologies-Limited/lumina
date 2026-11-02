<?php
declare(strict_types=1);

/**
 * Admin > Academy > Learners: "Send a reminder to all approved learners". Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-remind-approved.php
 * Removes its rows (zz-remind-*).
 */

use App\Core\Database;
use App\Services\Academy\LearnerMessages;

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

echo "-- remind all approved learners --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM academy_messages WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-remind-%')");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-remind-%'");
};
$clean();
$now = date('Y-m-d H:i:s');
$hash = password_hash('x', PASSWORD_DEFAULT);
$mk = static fn(string $tag, array $extra): int => (int) Database::insert('academy_learners', ['name' => 'Zed ' . $tag, 'email' => 'zz-remind-' . $tag . '@example.test', 'password_hash' => $hash] + $extra + ['is_active' => 1]);
$okId = $mk('approved', ['email_verified_at' => $now]);
$waitId = $mk('waiting', []);
$offId = $mk('off', ['email_verified_at' => $now, 'is_active' => 0]);
$inId = $mk('signedin', ['email_verified_at' => $now, 'last_login_at' => $now]);

$ids = array_map('intval', array_column(LearnerMessages::approvedLearners(), 'id'));
check('approvedLearners: the approved one is listed, the waiting and the deactivated ones are not', in_array($okId, $ids, true) && !in_array($waitId, $ids, true) && !in_array($offId, $ids, true));
check('the default reminder uses {name} and holds the sign-in link', str_contains(LearnerMessages::defaultReminder(), '{name}') && str_contains(LearnerMessages::defaultReminder(), 'academy/login'));

$n = LearnerMessages::remindApproved(null, 'Hello {name}, keep learning with the LAF Academy today.', false);
check('remindApproved: at least the approved learner was written to', $n >= 1, (string) $n);
$body = (string) Database::value("SELECT body FROM academy_messages WHERE learner_id = ? AND sender = 'admin' ORDER BY id DESC LIMIT 1", [$okId], '');
check('the message reached the approved learner with the first name filled in', str_contains($body, 'Hello Zed,'), $body);
check('nothing was written to the waiting or the deactivated learner', (int) Database::value('SELECT COUNT(*) FROM academy_messages WHERE learner_id IN (?, ?)', [$waitId, $offId], 0) === 0);

$yes = array_map('intval', array_column(LearnerMessages::approvedLearners('yes'), 'id'));
$no = array_map('intval', array_column(LearnerMessages::approvedLearners('no'), 'id'));
check('sign-in filter: "yes" has the learner who signed in and not the one who never did', in_array($inId, $yes, true) && !in_array($okId, $yes, true));
check('sign-in filter: "no" has the one who never signed in and not the one who did', in_array($okId, $no, true) && !in_array($inId, $no, true));
check('LearnerRegistry::loginSql: any / yes / no, and junk means any', \App\Services\Academy\LearnerRegistry::loginSql('') === null && str_contains((string) \App\Services\Academy\LearnerRegistry::loginSql('yes'), 'NOT NULL') && str_contains((string) \App\Services\Academy\LearnerRegistry::loginSql('no'), 'IS NULL') && \App\Services\Academy\LearnerRegistry::loginMode('x') === '');
$inbox = array_map('intval', array_column(LearnerMessages::inbox(300, null, 'yes'), 'id'));
check('messages inbox honours the sign-in filter (the never-signed-in learner is not in "yes")', !in_array($okId, $inbox, true));
$f = \App\Services\Academy\PaymentsReport::filters('30', 'all', 'all', 'yes');
check('payments filters carry the sign-in choice', $f['login'] === 'yes' && \App\Services\Academy\PaymentsReport::filters('30', 'all', 'all', 'zzz')['login'] === '');
// ---- the pages (needs the local web server): admin signs in, every Academy report takes ?login=yes|no
$base = 'http://localhost/' . basename(dirname(__DIR__));
Database::execute("DELETE FROM users WHERE email = 'zz-remind-admin@example.test'");
$apw = 'Remind admin pass 44';
\App\Models\User::create(['name' => 'ZZ Remind Admin', 'email' => 'zz-remind-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$jar = tempnam(sys_get_temp_dir(), 'rmj');
$req = static function (string $path, array $form = []) use ($base, $jar): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 40, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($form !== []) {
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
$page = $req('/admin/login');
$req('/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-remind-admin@example.test', 'password' => $apw]);
$r = $req('/admin/academy/learners?q=zz-remind-&login=yes');
check('learners page, Sign-in "Has signed in": 200, shows the filter and only the signed-in test learner', $r['code'] === 200 && str_contains($r['body'], 'name="login"') && str_contains($r['body'], 'zz-remind-signedin@') && !str_contains($r['body'], 'zz-remind-approved@'), (string) $r['code']);
$r = $req('/admin/academy/learners?q=zz-remind-&login=no');
check('learners page, Sign-in "Never signed in": the others, not the signed-in one', $r['code'] === 200 && str_contains($r['body'], 'zz-remind-approved@') && !str_contains($r['body'], 'zz-remind-signedin@'), (string) $r['code']);
$r = $req('/admin/academy/learners-export?q=zz-remind-&login=yes');
check('learners CSV export follows the filter', $r['code'] === 200 && str_contains($r['body'], 'zz-remind-signedin@') && !str_contains($r['body'], 'zz-remind-approved@'), (string) $r['code']);
foreach (['certificates', 'payments', 'messages'] as $pg) {
    foreach (['yes', 'no'] as $m) {
        $r = $req('/admin/academy/' . $pg . '?login=' . $m);
        check('/admin/academy/' . $pg . '?login=' . $m . ' opens (200) with the Sign-in filter', $r['code'] === 200 && str_contains($r['body'], 'name="login"'), (string) $r['code']);
    }
}
$r = $req('/admin/academy/payments-export?login=yes');
check('payments CSV export accepts the filter', $r['code'] === 200, (string) $r['code']);
@unlink($jar);
Database::execute("DELETE FROM users WHERE email = 'zz-remind-admin@example.test'");
$clean();
if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
