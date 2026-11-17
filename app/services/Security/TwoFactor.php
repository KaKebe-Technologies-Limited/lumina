<?php
declare(strict_types=1);

namespace App\Services\Security;

use App\Core\AuditLog;
use App\Core\Config;
use App\Core\Database;
use App\Core\Env;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Session;
use App\Core\Settings;
use App\Models\User;

/**
 * TWO-FACTOR SIGN-IN BY E-MAIL for the admin area. After the password is right, a 6-digit code is e-mailed to the account's address; only the right code completes the sign-in.
 *
 *   enabled()                 the switch (Admin > Security). The .env line AUTH_2FA=off forces it OFF (emergency way back in if the mail server is down), AUTH_2FA=on forces it ON.
 *   startLogin($user,$req)    the password was right: make a code, e-mail it, remember in the SESSION who is waiting (nobody is signed in yet). Fails CLOSED: no mail, no sign-in.
 *   verify($code)             right code + not expired + fewer than 5 wrong tries -> the user to sign in. A code works once and only for 10 minutes.
 *   trust() / isTrusted()     "remember this browser for N days" (cookie holds a random token, only its hash is stored): that browser skips the code until it expires or is forgotten.
 *
 * Codes are stored as a keyed hash (never the code), every wrong try and every send is counted, and nothing here reveals whether an account exists. Tables are created on first use.
 */
final class TwoFactor
{
    public const CODE_MINUTES = 10;
    public const MAX_TRIES = 5;
    public const RESEND_SECONDS = 45;
    private const SESSION = '_2fa';
    private const COOKIE = 'lm_td';

    private static ?bool $ready = null;

    public static function install(): bool
    {
        if (self::$ready !== null) {
            return self::$ready;
        }
        try {
            if (Database::tableExists('admin_login_codes') && Database::tableExists('admin_trusted_devices')) {
                return self::$ready = true;
            }
            Database::execute("CREATE TABLE IF NOT EXISTS `admin_login_codes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `user_id` INT UNSIGNED NOT NULL, `code_hash` CHAR(64) NOT NULL, `purpose` VARCHAR(10) NOT NULL DEFAULT 'login', `tries` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `ip` VARCHAR(45) NOT NULL DEFAULT '', `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `expires_at` DATETIME NOT NULL, `used_at` DATETIME NULL,
  PRIMARY KEY (`id`), KEY `ix_alc_user` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            Database::execute("CREATE TABLE IF NOT EXISTS `admin_trusted_devices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `user_id` INT UNSIGNED NOT NULL, `token_hash` CHAR(64) NOT NULL, `label` VARCHAR(120) NOT NULL DEFAULT '', `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `last_used_at` DATETIME NULL, `expires_at` DATETIME NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `uq_atd_token` (`token_hash`), KEY `ix_atd_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return self::$ready = true;
        } catch (\Throwable $e) {
            Log::warning('TwoFactor tables could not be created: ' . $e::class);
            return self::$ready = false;
        }
    }

    // ------------------------------------------------------------------------------------------------ the switch and the settings

    /** @return 'on'|'off'|null the .env override, if any */
    public static function forced(): ?string
    {
        $v = strtolower(trim((string) Env::get('AUTH_2FA', '')));
        return in_array($v, ['on', 'off'], true) ? $v : null;
    }

    public static function enabled(): bool
    {
        $f = self::forced();
        if ($f !== null) {
            return $f === 'on';
        }
        return (string) Settings::get('security_2fa', '0') === '1' && self::install();
    }

    /** Days a browser may be remembered (0 = the option is not offered). */
    public static function trustDays(): int
    {
        return max(0, min(90, (int) Settings::get('security_trust_days', '30')));
    }

    // ------------------------------------------------------------------------------------------------ the code

    /** @return array{ok:bool,error?:string} */
    public static function sendCode(array $user, string $ip, string $purpose = 'login'): array
    {
        if (!self::install()) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        $uid = (int) $user['id'];
        if (!RateLimit::attempt('2fa-send:' . $uid, 8, 3600)) {                    // a stranger cannot flood someone's inbox with codes
            return ['ok' => false, 'error' => 'too_many'];
        }
        $code = (string) random_int(100000, 999999);
        Database::execute('UPDATE admin_login_codes SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL', [$uid]);
        Database::insert('admin_login_codes', [
            'user_id' => $uid, 'code_hash' => self::hash($code), 'purpose' => $purpose, 'ip' => mb_substr($ip, 0, 45), 'expires_at' => date('Y-m-d H:i:s', time() + self::CODE_MINUTES * 60),
        ]);
        $site = (string) setting('site_name', 'Lumina Africa Foundation');
        $text = 'Your ' . $site . " sign-in code is:\n\n    " . $code . "\n\n"
            . 'It works for ' . self::CODE_MINUTES . " minutes and only once. Type it on the sign-in page.\n\n"
            . 'The sign-in was started on ' . date('j M Y, H:i') . ' from the address ' . $ip . ".\n"
            . "If this was not you, someone knows your password: do not share this code, and change your password straight away.\n";
        try {
            $ok = Mailer::send((string) $user['email'], 'Your sign-in code: ' . $code, $text);
        } catch (\Throwable $e) {
            Log::warning('2FA code e-mail failed: ' . $e::class);
            $ok = false;
        }
        return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'mail'];
    }

    /** @return bool true when this is the right, unused, unexpired code of the user (it is then used up) */
    public static function checkCode(int $userId, string $code, string $purpose = 'login'): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';
        if (preg_match('/^\d{6}$/', $code) !== 1 || !self::install()) {
            return false;
        }
        $row = Database::one("SELECT * FROM admin_login_codes WHERE user_id = ? AND purpose = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1", [$userId, $purpose]);
        if ($row === null) {
            return false;
        }
        if ((int) $row['tries'] >= self::MAX_TRIES) {
            return false;
        }
        if (!hash_equals((string) $row['code_hash'], self::hash($code))) {
            Database::execute('UPDATE admin_login_codes SET tries = tries + 1 WHERE id = ?', [(int) $row['id']]);
            return false;
        }
        return Database::execute('UPDATE admin_login_codes SET used_at = NOW() WHERE id = ? AND used_at IS NULL', [(int) $row['id']]) > 0;
    }

    private static function hash(string $code): string
    {
        return hash_hmac('sha256', $code, Config::appKey());
    }

    // ------------------------------------------------------------------------------------------------ the sign-in in two steps

    /** The password was right. Sends the code and parks the person in the session (they are NOT signed in). @return array{ok:bool,error?:string} */
    public static function startLogin(array $user, Request $request): array
    {
        $r = self::sendCode($user, $request->ip());
        if (!$r['ok']) {
            return $r;
        }
        Session::regenerate();
        Session::put(self::SESSION, ['uid' => (int) $user['id'], 'at' => time(), 'sent' => time(), 'fails' => 0]);
        return ['ok' => true];
    }

    /** @return ?array{uid:int,at:int,sent:int,fails:int} */
    public static function pending(): ?array
    {
        $p = Session::get(self::SESSION);
        if (!is_array($p) || !isset($p['uid'], $p['at']) || time() - (int) $p['at'] > 900) {       // the step must be finished within 15 minutes
            if ($p !== null) {
                Session::forget(self::SESSION);
            }
            return null;
        }
        return $p;
    }

    public static function cancel(): void
    {
        Session::forget(self::SESSION);
    }

    /** @return array{ok:bool,user?:array<string,mixed>,error?:string,locked?:bool} */
    public static function verify(string $code, Request $request): array
    {
        $p = self::pending();
        if ($p === null) {
            return ['ok' => false, 'error' => 'expired'];
        }
        if (!RateLimit::attempt('2fa-try:' . $request->ip(), 30, 900)) {
            return ['ok' => false, 'error' => 'too_many'];
        }
        $user = User::find((int) $p['uid']);
        if ($user === null || !(int) $user['is_active']) {
            self::cancel();
            return ['ok' => false, 'error' => 'expired'];
        }
        if (self::checkCode((int) $user['id'], $code)) {
            self::cancel();
            return ['ok' => true, 'user' => $user];
        }
        $p['fails'] = (int) $p['fails'] + 1;
        if ($p['fails'] >= self::MAX_TRIES) {                                  // five wrong codes: start again from the password
            self::cancel();
            Database::execute('UPDATE admin_login_codes SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL', [(int) $user['id']]);
            AuditLog::record('auth.2fa_failed', 'user', (int) $user['id'], 'Five wrong sign-in codes: the sign-in was cancelled');
            return ['ok' => false, 'error' => 'locked', 'locked' => true];
        }
        Session::put(self::SESSION, $p);
        return ['ok' => false, 'error' => 'wrong'];
    }

    /** A new code for the person waiting (at most one per 45 seconds). @return array{ok:bool,error?:string,wait?:int} */
    public static function resend(Request $request): array
    {
        $p = self::pending();
        if ($p === null) {
            return ['ok' => false, 'error' => 'expired'];
        }
        $wait = self::RESEND_SECONDS - (time() - (int) $p['sent']);
        if ($wait > 0) {
            return ['ok' => false, 'error' => 'wait', 'wait' => $wait];
        }
        $user = User::find((int) $p['uid']);
        if ($user === null) {
            self::cancel();
            return ['ok' => false, 'error' => 'expired'];
        }
        $r = self::sendCode($user, $request->ip());
        if ($r['ok']) {
            $p['sent'] = time();
            $p['fails'] = 0;
            Session::put(self::SESSION, $p);
        }
        return $r;
    }

    /** The masked address shown on the code page: a***@example.org */
    public static function maskedEmail(string $email): string
    {
        [$a, $d] = array_pad(explode('@', $email, 2), 2, '');
        return ($a !== '' ? $a[0] : '') . str_repeat('*', max(2, min(6, strlen($a) - 1))) . '@' . $d;
    }

    // ------------------------------------------------------------------------------------------------ "remember this browser"

    public static function trust(array $user, Request $request): void
    {
        $days = self::trustDays();
        if ($days < 1 || !self::install()) {
            return;
        }
        $token = bin2hex(random_bytes(32));
        $label = mb_substr((string) preg_replace('/\s+/', ' ', (string) $request->userAgent()), 0, 120);
        Database::insert('admin_trusted_devices', ['user_id' => (int) $user['id'], 'token_hash' => hash('sha256', $token), 'label' => $label, 'ip' => mb_substr($request->ip(), 0, 45), 'expires_at' => date('Y-m-d H:i:s', time() + $days * 86400)]);
        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            setcookie(self::COOKIE, (int) $user['id'] . '.' . $token, ['expires' => time() + $days * 86400, 'path' => '/', 'secure' => $request->isSecure(), 'httponly' => true, 'samesite' => 'Lax']);
        }
    }

    public static function isTrusted(array $user, Request $request): bool
    {
        $raw = (string) $request->cookie(self::COOKIE, '');
        if ($raw === '' || !self::install() || preg_match('/^(\d+)\.([a-f0-9]{64})$/', $raw, $m) !== 1 || (int) $m[1] !== (int) $user['id']) {
            return false;
        }
        $row = Database::one('SELECT id FROM admin_trusted_devices WHERE token_hash = ? AND user_id = ? AND expires_at > NOW()', [hash('sha256', $m[2]), (int) $user['id']]);
        if ($row === null) {
            return false;
        }
        Database::execute('UPDATE admin_trusted_devices SET last_used_at = NOW() WHERE id = ?', [(int) $row['id']]);
        return true;
    }

    /** Forget every remembered browser of one account, or of everybody (null). @return int how many were removed */
    public static function forgetDevices(?int $userId = null): int
    {
        if (!self::install()) {
            return 0;
        }
        return $userId === null ? Database::execute('DELETE FROM admin_trusted_devices') : Database::execute('DELETE FROM admin_trusted_devices WHERE user_id = ?', [$userId]);
    }

    public static function deviceCount(): int
    {
        return self::install() ? (int) Database::value('SELECT COUNT(*) FROM admin_trusted_devices WHERE expires_at > NOW()', [], 0) : 0;
    }
}
