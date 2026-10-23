<?php
declare(strict_types=1);

/** Admin "forgot password" checks, included by bin/selftest.php or run alone: /c/xampp/php/php.exe bin/selftest-admin-reset.php. Removes its own test user. */

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
use App\Core\RateLimit;
use App\Models\User;
use App\Services\AdminPasswordReset;

$email = 'zz-selftest-reset@example.test';
Database::execute('DELETE FROM users WHERE email = ?', [$email]);
$uid = (int) User::create(['name' => 'ZZ Reset Test', 'email' => $email, 'password_hash' => Auth::hashPassword('Old-Password-1234'), 'role' => 'editor', 'is_active' => 1, 'must_change_password' => 1]);
$user = User::find($uid);

$tok = AdminPasswordReset::token($user);
check('admin reset: a fresh link is valid for its account', (int) (AdminPasswordReset::userForToken($tok)['id'] ?? 0) === $uid);
check('admin reset: an expired link is refused', AdminPasswordReset::userForToken(AdminPasswordReset::token($user, time() - 5)) === null);
$parts = explode('_', $tok);
check('admin reset: a changed signature, id or expiry is refused',
    AdminPasswordReset::userForToken($parts[0] . '_' . $parts[1] . '_' . strrev($parts[2])) === null
    && AdminPasswordReset::userForToken(($uid + 1) . '_' . $parts[1] . '_' . $parts[2]) === null
    && AdminPasswordReset::userForToken($parts[0] . '_' . ((int) $parts[1] + 600) . '_' . $parts[2]) === null
    && AdminPasswordReset::userForToken('garbage') === null && AdminPasswordReset::userForToken('') === null);

$weak = AdminPasswordReset::reset($tok, 'short1');
check('admin reset: a weak password is refused and the link still works', !$weak['ok'] && AdminPasswordReset::userForToken($tok) !== null);

Database::insert('login_attempts', ['email' => $email, 'ip' => '203.0.113.9', 'success' => 0]);
$done = AdminPasswordReset::reset($tok, 'New-Password-5678');
$after = User::find($uid);
check('admin reset: the new password works and the old one does not', $done['ok'] && Auth::verifyPassword('New-Password-5678', (string) $after['password_hash']) && !Auth::verifyPassword('Old-Password-1234', (string) $after['password_hash']));
check('admin reset: the temporary-password flag is cleared', (int) $after['must_change_password'] === 0);
check('admin reset: the link works only once', AdminPasswordReset::userForToken($tok) === null && !AdminPasswordReset::reset($tok, 'Another-Password-9')['ok']);
check('admin reset: earlier failed sign-ins no longer lock the account out', (int) Database::value('SELECT COUNT(*) FROM login_attempts WHERE email = ?', [$email]) === 0);

User::update($uid, ['is_active' => 0]);
check('admin reset: a disabled account cannot use a link', AdminPasswordReset::userForToken(AdminPasswordReset::token(User::find($uid))) === null);
User::update($uid, ['is_active' => 1]);

// requests: same quiet outcome for unknown, malformed and known addresses; only the known one writes a mail file
$ip = '203.0.113.77';
RateLimit::clear('admin-reset-ip:' . $ip);
RateLimit::clear('admin-reset:' . sha1($email));
$dir = dirname(__DIR__) . '/storage/mail';
$before = is_dir($dir) ? count(glob($dir . '/*')) : 0;
AdminPasswordReset::request('nobody-zz@example.test', $ip);
AdminPasswordReset::request('not an address', $ip);
$mid = is_dir($dir) ? count(glob($dir . '/*')) : 0;
AdminPasswordReset::request($email, $ip);
$end = is_dir($dir) ? count(glob($dir . '/*')) : 0;
check('admin reset: unknown and malformed addresses send nothing', $mid === $before);
check('admin reset: a known address gets exactly one e-mail', \App\Core\Mailer::driver() !== 'file' || $end === $mid + 1);
RateLimit::clear('admin-reset-ip:' . $ip);
RateLimit::clear('admin-reset:' . sha1($email));

Database::execute('DELETE FROM users WHERE email = ?', [$email]);
Database::execute("DELETE FROM audit_log WHERE action LIKE 'auth.reset_%' AND entity_id = ?", [$uid]);
check('admin reset: cleanup removed the test user', User::findBy('email', $email) === null);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
