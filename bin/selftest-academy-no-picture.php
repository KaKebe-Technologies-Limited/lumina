<?php
declare(strict_types=1);

/**
 * LAF Academy: remind the approved learners who have no profile picture, with a deadline, and switch them off after it. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-no-picture.php
 * Needs the local web server. Removes its rows (zz-np-*) and puts the setting back.
 */

use App\Core\Database;
use App\Core\Settings;
use App\Services\Academy\LearnerMessages;
use App\Services\Academy\LearnerPortrait;

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

echo "-- academy: learners without a picture --\n";
$wasDeadline = Settings::isCustomised(LearnerPortrait::DEADLINE_KEY) ? (string) Settings::get(LearnerPortrait::DEADLINE_KEY) : null;
$clean = static function (): void {
    Database::execute("DELETE FROM academy_messages WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-np-%')");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-np-%'");
    Database::execute("DELETE FROM users WHERE email = 'zz-np-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
Settings::reset(LearnerPortrait::DEADLINE_KEY);
Settings::flush();

$now = date('Y-m-d H:i:s');
$hash = password_hash('x', PASSWORD_DEFAULT);
$mk = static fn(string $tag, array $extra): int => (int) Database::insert('academy_learners', ['name' => 'Zed Np ' . $tag, 'email' => 'zz-np-' . $tag . '@example.test', 'password_hash' => $hash] + $extra + ['is_active' => 1]);
$noPic = $mk('nopic', ['email_verified_at' => $now]);
$hasPic = $mk('haspic', ['email_verified_at' => $now, 'photo_path' => 'learners/zz-np-test.jpg']);
$waiting = $mk('waiting', []);
$off = $mk('off', ['email_verified_at' => $now, 'is_active' => 0]);

$ids = array_map('intval', array_column(LearnerPortrait::withoutPicture(), 'id'));
check('withoutPicture: the approved learner without a picture is listed; the ones with a picture, waiting or switched off are not', in_array($noPic, $ids, true) && !in_array($hasPic, $ids, true) && !in_array($waiting, $ids, true) && !in_array($off, $ids, true));
check('no deadline yet: not passed, nothing can be switched off', LearnerPortrait::deadline() === '' && !LearnerPortrait::deadlinePassed() && LearnerPortrait::switchOffWithoutPicture(null, false) === 0);
check('the default reminder uses {name} and {deadline} and warns about switching off', str_contains(LearnerPortrait::defaultReminder(), '{name}') && str_contains(LearnerPortrait::defaultReminder(), '{deadline}') && str_contains(LearnerPortrait::defaultReminder(), 'switched off'));

$r = LearnerPortrait::remindWithoutPicture(null, 'Hello {name}, add your picture before {deadline} please.', false, 7);
check('the reminder starts a 7-day deadline and reaches at least the learner without a picture', $r['sent'] >= 1 && $r['deadline'] === date('Y-m-d', strtotime('+7 day')) && LearnerPortrait::deadline() === $r['deadline']);
$body = (string) Database::value("SELECT body FROM academy_messages WHERE learner_id = ? AND sender = 'admin' ORDER BY id DESC LIMIT 1", [$noPic], '');
check('the message has the first name and the deadline date filled in', str_contains($body, 'Hello Zed,') && str_contains($body, date('j M Y', strtotime('+7 day'))), $body);
check('nobody with a picture, waiting or switched off was messaged', (int) Database::value('SELECT COUNT(*) FROM academy_messages WHERE learner_id IN (?, ?, ?)', [$hasPic, $waiting, $off], 0) === 0);
check('before the deadline nothing is switched off', !LearnerPortrait::deadlinePassed() && LearnerPortrait::switchOffWithoutPicture(null, false) === 0 && (int) Database::value('SELECT is_active FROM academy_learners WHERE id = ?', [$noPic], 0) === 1);

Settings::set(LearnerPortrait::DEADLINE_KEY, date('Y-m-d', strtotime('-1 day')));
Settings::flush();
check('a deadline in the past counts as passed', LearnerPortrait::deadlinePassed());
$n = LearnerPortrait::switchOffWithoutPicture(null, false);
check('after the deadline the learner without a picture is switched off; the one with a picture stays on', $n >= 1 && (int) Database::value('SELECT is_active FROM academy_learners WHERE id = ?', [$noPic], 1) === 0 && (int) Database::value('SELECT is_active FROM academy_learners WHERE id = ?', [$hasPic], 0) === 1);
$note = (string) Database::value("SELECT body FROM academy_messages WHERE learner_id = ? AND sender = 'admin' ORDER BY id DESC LIMIT 1", [$noPic], '');
check('the switched-off learner is told why and how to get the account back', str_contains($note, 'switched off') && str_contains($note, 'academy/contact'), $note);

// ---- the admin page
$base = 'http://localhost/' . basename(dirname(__DIR__));
$apw = 'Np admin pass 88';
Database::execute("UPDATE academy_learners SET is_active = 1 WHERE id = ?", [$noPic]);
Settings::set(LearnerPortrait::DEADLINE_KEY, date('Y-m-d', strtotime('+3 day')));
Settings::flush();
\App\Models\User::create(['name' => 'ZZ Np Admin', 'email' => 'zz-np-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$jar = tempnam(sys_get_temp_dir(), 'npj');
$req = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
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
    return ['code' => $code, 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$page = $req('GET', '/admin/login');
$req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-np-admin@example.test', 'password' => $apw]);
$r = $req('GET', '/admin/academy/learners?q=zz-np-');
check('admin: the Learners page shows the "no profile picture" panel with the deadline and a disabled switch-off button', $r['code'] === 200 && str_contains($r['body'], 'no profile picture.') && str_contains($r['body'], 'Current deadline') && str_contains($r['body'], 'Available after'), (string) $r['code']);
$r = $req('GET', '/admin/academy/learners?status=nopicture&q=zz-np-');
check('admin: the filter "Approved, no picture" lists the learner without a picture only', $r['code'] === 200 && str_contains($r['body'], 'zz-np-nopic@') && !str_contains($r['body'], 'zz-np-haspic@') && str_contains($r['body'], 'No picture'), (string) $r['code']);
$r = $req('GET', '/admin/academy/learners');
$req('POST', '/admin/academy/learners-switch-off-no-picture', ['_token' => $tok($r['body'])]);
check('admin: pressing switch-off before the deadline changes nothing', (int) Database::value('SELECT is_active FROM academy_learners WHERE id = ?', [$noPic], 0) === 1);
$r = $req('GET', '/admin/academy/learners');
$req('POST', '/admin/academy/learners-remind-no-picture', ['_token' => $tok($r['body']), 'message' => 'Hello {name}, please add your picture before {deadline}.', 'days' => '14']);
check('admin: the remind button sends and sets a 14-day deadline', LearnerPortrait::deadline() === date('Y-m-d', strtotime('+14 day')));
$rep = \App\Services\Reports\ReportCatalog::all()['academy-no-picture'] ?? null;
$res = $rep !== null ? \App\Services\Reports\ReportCatalog::run($rep, ['from' => null, 'to' => null], 200) : ['error' => 'missing', 'rows' => []];
check('report "Approved learners without a picture" runs and lists the learner', $res['error'] === null && in_array('zz-np-nopic@example.test', array_column($res['rows'], 'email'), true));
@unlink($jar);

Database::execute("DELETE FROM audit_log WHERE user_name = 'ZZ Np Admin'");
$clean();
$wasDeadline === null ? Settings::reset(LearnerPortrait::DEADLINE_KEY) : Settings::set(LearnerPortrait::DEADLINE_KEY, $wasDeadline);
Settings::flush();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
