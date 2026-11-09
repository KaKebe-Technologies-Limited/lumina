<?php
declare(strict_types=1);

/**
 * LAF Academy: the SIGN-IN GUIDE e-mailed to every approved learner: a clear numbered procedure with the learner's own e-mail address and registration number, sent a batch at a time
 * (the mail account has a daily limit), never twice to the same learner, and stopped when the mail server refuses. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-signin-guide.php
 * Needs the local web server (local mail is in "file" mode: messages are written to storage/mail). Removes its rows (zz-sg-*) and puts the settings back.
 */

use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Models\User;
use App\Services\Academy\AcademyMail;
use App\Services\Academy\LearnerMessages;

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

echo "-- academy: sign-in guide for approved learners --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM academy_messages WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-sg-%')");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-sg-%'");
    Database::execute("DELETE FROM users WHERE email = 'zz-sg-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$mailDir = STORAGE_PATH . '/mail';
$emlNow = static fn(): array => glob($mailDir . '/*.eml') ?: [];
$before = $emlNow();
$drop = static function () use ($emlNow, $before): void {
    foreach (array_diff($emlNow(), $before) as $f) {
        @unlink($f);
    }
};

$approvedBefore = count(LearnerMessages::approvedLearners());
$mk = static function (string $tag, array $extra = []): int {
    return (int) Database::insert('academy_learners', $extra + [
        'reg_no' => 'LAF-AC-SG-' . strtoupper($tag), 'name' => 'Zz Guide ' . $tag, 'email' => 'zz-sg-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'),
        'email_verified_at' => date('Y-m-d H:i:s'), 'approved_at' => date('Y-m-d H:i:s'), 'is_active' => 1,
    ]);
};
$ids = [];
foreach (['a', 'b', 'c', 'd', 'e'] as $t) {
    $ids[$t] = $mk($t, $t === 'd' ? ['google_sub' => '1234567890'] : []);
}
// one learner who signed in before
Database::execute('UPDATE academy_learners SET last_login_at = NOW() WHERE id = ?', [$ids['e']]);

check('the approved list carries the registration number and the Google marker', ($row = LearnerMessages::approvedLearners()[0] ?? null) !== null && array_key_exists('reg_no', $row) && array_key_exists('google_sub', $row));
$pending = LearnerMessages::signInPending();
check('five new approved learners are waiting for the guide', count($pending) === $approvedBefore + 5, (string) count($pending));

// ---- the e-mail itself
$aEmail = (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$ids['a']]);
$dEmail = (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$ids['d']]);
check('the guide for a normal learner is sent', AcademyMail::signInGuide($aEmail));
check('the guide for a Google learner is sent', AcademyMail::signInGuide($dEmail));
$new = array_values(array_diff($emlNow(), $before));
sort($new);
$texts = array_map(static fn(string $f): string => (string) file_get_contents($f), $new);
$mailA = '';
$mailD = '';
foreach ($texts as $t) {
    $decoded = quoted_printable_decode($t);
    $b64 = '';
    if (preg_match('~Content-Transfer-Encoding:\s*base64\R\R([A-Za-z0-9+/=\r\n]+)~', $t, $m) === 1) {
        $b64 = (string) base64_decode(preg_replace('/\s+/', '', $m[1]) ?: '', true);
    }
    $all = $t . "\n" . $decoded . "\n" . $b64;
    if (str_contains($all, 'zz-sg-a@example.test')) {
        $mailA = $all;
    }
    if (str_contains($all, 'zz-sg-d@example.test')) {
        $mailD = $all;
    }
}
check('the e-mail has a numbered procedure (Step 1 to Step 4)', str_contains($mailA, 'Step 1.') && str_contains($mailA, 'Step 2.') && str_contains($mailA, 'Step 3.') && str_contains($mailA, 'Step 4.'));
check('the e-mail names the sign-in page, THEIR e-mail address and THEIR registration number', str_contains($mailA, 'academy/login') && str_contains($mailA, 'zz-sg-a@example.test') && str_contains($mailA, 'LAF-AC-SG-A'));
check('the e-mail explains the forgotten-password route', str_contains($mailA, 'Forgot your password') && str_contains($mailA, 'academy/forgot'));
check('a learner who joined with Google is told to use "Continue with Google" (no password step)', str_contains($mailD, 'Continue with Google') && !str_contains($mailD, 'Step 4.'));
$drop();

// ---- the batch sender
$r1 = LearnerMessages::sendSignInGuides(null, '', false, 2);
check('batch 1: only the cap is sent, the rest wait', $r1['sent'] === 2 && $r1['remaining'] === $r1['total'] - 2 && $r1['stopped'] === null, json_encode($r1));
$marked = (int) Database::value("SELECT COUNT(DISTINCT learner_id) FROM academy_messages WHERE sender = 'admin' AND body LIKE ? AND learner_id IN (" . implode(',', $ids) . ')', [LearnerMessages::SIGNIN_MARK . '%'], 0);
check('batch 1: each learner written to has the portal copy that starts with the fixed words', $marked === 2, (string) $marked);
$pendingAfter = LearnerMessages::signInPending();
check('batch 1: those two are no longer waiting', count($pendingAfter) === $r1['total'] - 2);
$r2 = LearnerMessages::sendSignInGuides(null, '', false, 1000);
check('batch 2: everybody else is sent, nobody is written to twice', $r2['sent'] === $r1['total'] - 2 && $r2['remaining'] === 0, json_encode($r2));
check('after that nobody is waiting', LearnerMessages::signInPending() === [] && LearnerMessages::sendSignInGuides(null, '', false, 100)['total'] === 0);
$perLearner = (int) Database::value("SELECT MAX(c) FROM (SELECT COUNT(*) c FROM academy_messages WHERE sender = 'admin' AND body LIKE ? GROUP BY learner_id) t", [LearnerMessages::SIGNIN_MARK . '%'], 0);
check('every learner has exactly one copy', $perLearner === 1, (string) $perLearner);
$again = LearnerMessages::sendSignInGuides(null, '', true, 3);
check('"send again" ignores the record and sends once more (up to the cap)', $again['sent'] === 3 && $again['total'] >= 5);
$drop();

// the "never signed in" group leaves out the learner who signed in before
Database::execute("DELETE FROM academy_messages WHERE learner_id IN (" . implode(',', $ids) . ')');
$never = array_column(LearnerMessages::signInPending('no'), 'id');
check('"never signed in" leaves out the learner who signed in before', !in_array($ids['e'], array_map('intval', $never), true) && in_array($ids['a'], array_map('intval', $never), true));

// ---- the mail server refuses: stop after three failures, nobody is marked done
Database::execute("DELETE FROM academy_messages WHERE learner_id IN (" . implode(',', $ids) . ')');
$oldDriver = Config::get('mail.driver');
$oldSmtp = Config::get('mail.smtp');
Config::set('mail.driver', 'smtp');
Config::set('mail.smtp', ['host' => '127.0.0.1', 'port' => 1, 'username' => 'x', 'password' => 'y', 'encryption' => 'none', 'timeout' => 2, 'cafile' => '']);
$bad = LearnerMessages::sendSignInGuides(null, '', false, 100, 25);
Config::set('mail.driver', $oldDriver);
Config::set('mail.smtp', $oldSmtp);
check('a refusing mail server stops the run after three failures with the reason', $bad['sent'] === 0 && $bad['failed'] === 3 && $bad['stopped'] !== null && $bad['stopped'] !== 'time', json_encode($bad));
check('nobody is marked done after a failed run (they are tried again next time)', count(LearnerMessages::signInPending()) === $approvedBefore + 5);

// ---- the admin button
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string $j = null) use ($base): array {
    $j ??= tempnam(sys_get_temp_dir(), 'sgj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 90, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => true]);
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
$apw = 'Guide admin pass 71';
User::create(['name' => 'ZZ Sg Admin', 'email' => 'zz-sg-admin@example.test', 'password_hash' => Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$ja = tempnam(sys_get_temp_dir(), 'sga');
$lp = $req('GET', '/admin/login', [], $ja);
$req('POST', '/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-sg-admin@example.test', 'password' => $apw], $ja);
$pg = $req('GET', '/admin/academy/learners', [], $ja);
check('admin: the learners page has the "Send the sign-in guide" panel with the numbered steps and the batch note', $pg['code'] === 200 && str_contains($pg['body'], 'Send the sign-in guide to all approved learners') && str_contains($pg['body'], 'Forgot your password?') && str_contains($pg['body'], 'at a time') && str_contains($pg['body'], 'learners-signin-guides'), (string) $pg['code']);
$before2 = $emlNow();
$post = $req('POST', '/admin/academy/learners-signin-guides', ['_token' => $tok($pg['body']), 'login' => ''], $ja);
$sentNow = count(array_diff($emlNow(), $before2));
check('admin: pressing the button e-mails the guides and the page reports it', $post['code'] === 200 && $sentNow === $approvedBefore + 5 && (str_contains($post['body'], 'sign-in guide was sent to') || str_contains($post['body'], 'still waiting')), $sentNow . ' / ' . $post['code']);
$pg2 = $req('GET', '/admin/academy/learners', [], $ja);
$post2 = $req('POST', '/admin/academy/learners-signin-guides', ['_token' => $tok($pg2['body']), 'login' => ''], $ja);
check('admin: pressing it again finds nobody waiting and says so', $post2['code'] === 200 && str_contains($post2['body'], 'already been sent'));
$edTok = $tok($pg2['body']);
check('admin: the sign-in guide is recorded in the activity log', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'academy.learner.signin_guides' AND created_at >= (NOW() - INTERVAL 10 MINUTE)", [], 0) >= 2);
$drop();
@unlink($ja);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
