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

$ids = array_map('intval', array_column(LearnerMessages::approvedLearners(), 'id'));
check('approvedLearners: the approved one is listed, the waiting and the deactivated ones are not', in_array($okId, $ids, true) && !in_array($waitId, $ids, true) && !in_array($offId, $ids, true));
check('the default reminder uses {name} and holds the sign-in link', str_contains(LearnerMessages::defaultReminder(), '{name}') && str_contains(LearnerMessages::defaultReminder(), 'academy/login'));

$n = LearnerMessages::remindApproved(null, 'Hello {name}, keep learning with the LAF Academy today.', false);
check('remindApproved: at least the approved learner was written to', $n >= 1, (string) $n);
$body = (string) Database::value("SELECT body FROM academy_messages WHERE learner_id = ? AND sender = 'admin' ORDER BY id DESC LIMIT 1", [$okId], '');
check('the message reached the approved learner with the first name filled in', str_contains($body, 'Hello Zed,'), $body);
check('nothing was written to the waiting or the deactivated learner', (int) Database::value('SELECT COUNT(*) FROM academy_messages WHERE learner_id IN (?, ?)', [$waitId, $offId], 0) === 0);

$clean();
if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
