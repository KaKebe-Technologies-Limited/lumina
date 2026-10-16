<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Admin authentication. Users live in the `users` table (roles: admin, editor).
 *
 *   if (Auth::attempt($request, $email, $password)) { ... } else { $msg = Auth::error(); }
 *   Auth::check(); Auth::user(); Auth::id(); Auth::role(); Auth::hasRole('admin'); Auth::logout();
 *   Auth::changePassword($userId, $newPlain, mustChange: false);
 *   Auth::generatePassword(16);
 *
 * Throttling: after 5 failures per IP+email (or 20 per IP) within 15 minutes, attempts are refused (login_attempts table).
 */
final class Auth
{
    /** bcrypt cost used for EVERY hash (real and dummy) so a login for an unknown e-mail costs exactly as much as a real one. */
    private const COST = 10;
    /** Valid bcrypt hash (cost 10) of a random string nobody knows; verified when the e-mail does not exist. */
    private const DUMMY_HASH = '$2y$10$mxfM3UgbRdHcGwKxChEAZuoh9yAGVeW90JdtKsbGc6GDNuoAYh4bq';
    /** Common passwords refused outright (lower-case compare). */
    private const COMMON = ['password1', 'password12', 'password123', 'password1234', 'passw0rd123', 'qwerty123', 'qwerty1234', 'qwertyuiop1',
        '1234567890a', 'abcdefghij1', 'welcome123', 'welcome1234', 'letmein123', 'admin12345', 'administrator1', 'iloveyou123', 'changeme123',
        'lumina12345', 'luminaafrica1', 'uganda12345', 'p@ssw0rd123'];

    private static ?array $user = null;
    private static bool $loaded = false;
    private static ?string $error = null;

    /** Current user row (without password_hash) or null. Re-checked against the DB once per request (is_active). */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;
        $id = Session::get('user_id');
        if (!$id || !Database::tableExists('users')) {
            return self::$user = null;
        }
        $u = User::find((int) $id);
        if (!$u || !(int) $u['is_active']) {
            Session::forget('user_id');
            return self::$user = null;
        }
        // A password change (own, or reset by an admin) signs every OTHER session of that account out: the session remembers a
        // fingerprint of the password hash it was created with.
        $fp = self::fingerprint((string) $u['password_hash']);
        $seen = Session::get('_pwv');
        if (is_string($seen) && $seen !== '' && !hash_equals($seen, $fp)) {
            Session::forget('user_id');
            Session::forget('_pwv');
            return self::$user = null;
        }
        if (!is_string($seen) || $seen === '') {
            Session::put('_pwv', $fp);        // sessions created before this check existed adopt the current fingerprint
        }
        unset($u['password_hash']);
        return self::$user = $u;
    }

    private static function fingerprint(string $hash): string
    {
        return substr(hash_hmac('sha256', $hash, Config::appKey()), 0, 24);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['id'] : null;
    }

    public static function role(): ?string
    {
        $u = self::user();
        return $u ? (string) $u['role'] : null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $r = self::role();
        return $r !== null && in_array($r, $roles, true);
    }

    public static function mustChangePassword(): bool
    {
        $u = self::user();
        return $u !== null && (int) ($u['must_change_password'] ?? 0) === 1;
    }

    /** Last failure message from attempt() (safe to display). */
    public static function error(): ?string
    {
        return self::$error;
    }

    public static function attempt(Request $request, string $email, string $password): bool
    {
        self::$error = null;
        $email = strtolower(trim($email));
        $ip = $request->ip();
        $window = (int) Config::get('auth.window', 900);

        // Reserve the attempt BEFORE the slow password check and count afterwards (our own row included), so parallel requests
        // cannot all slip under the limit while none has been recorded yet.
        $attemptId = self::record($email, $ip, false);
        if (self::isLockedOut($email, $ip)) {
            self::forgetAttempt($attemptId);
            self::$error = 'Too many failed sign-in attempts. Please wait about ' . (int) ceil($window / 60) . ' minutes and try again.';
            return false;
        }

        $user = $email !== '' ? User::findBy('email', $email) : null;
        // Always run exactly one hash verification, at the same cost as real hashes, so timing does not reveal whether the account exists.
        $hash = is_array($user) ? (string) $user['password_hash'] : self::DUMMY_HASH;
        $ok = password_verify($password, $hash) && $user !== null && (int) $user['is_active'] === 1;

        if (!$ok) {
            self::$error = 'Invalid email or password.';
            return false;
        }
        self::markSuccess($attemptId);

        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => self::COST])) {
            User::update((int) $user['id'], ['password_hash' => self::hashPassword($password)]);
            $user['password_hash'] = (string) User::find((int) $user['id'])['password_hash'];
        }
        self::login($user);
        return true;
    }

    /** Log a user row in (regenerates the session id). */
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::put('user_id', (int) $user['id']);
        if (isset($user['password_hash'])) {
            Session::put('_pwv', self::fingerprint((string) $user['password_hash']));
        } else {
            Session::forget('_pwv');
        }
        Session::put('_last_seen', time());
        Database::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $user['id']]);
        self::$loaded = false;
        self::$user = null;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$loaded = false;
        self::$user = null;
    }

    /** Force reload of the cached user (call after editing the current user's profile). */
    public static function refresh(): void
    {
        self::$loaded = false;
        self::$user = null;
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => self::COST]);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function changePassword(int $userId, string $newPlain, bool $mustChange = false): void
    {
        Database::update('users', [
            'password_hash'        => self::hashPassword($newPlain),
            'must_change_password' => $mustChange ? 1 : 0,
        ], 'id = ?', [$userId]);
        if (self::id() === $userId) {
            // keep THIS session signed in; every other session of the account now fails the fingerprint check
            $row = User::find($userId);
            self::refresh();
            if ($row) {
                Session::put('_pwv', self::fingerprint((string) $row['password_hash']));
            }
        }
    }

    /** Returns an error message if the password is too weak, else null. */
    public static function passwordProblem(string $password): ?string
    {
        $min = (int) Config::get('auth.min_password', 10);
        if (mb_strlen($password) < $min) {
            return "Password must be at least {$min} characters.";
        }
        if (strlen($password) > 72) {
            return 'Password must be at most 72 bytes long (bcrypt ignores anything beyond that).';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            return 'Password must contain at least one letter and one number.';
        }
        if (count(array_unique(mb_str_split($password))) < 5) {
            return 'Password is too repetitive. Use at least 5 different characters.';
        }
        if (in_array(strtolower($password), self::COMMON, true)) {
            return 'That password is too common. Choose something less guessable.';
        }
        $u = self::user();
        if ($u !== null && stripos($password, explode('@', (string) $u['email'])[0]) !== false && strlen(explode('@', (string) $u['email'])[0]) >= 4) {
            return 'Password must not contain your e-mail name.';
        }
        return null;
    }

    /** Random password (no ambiguous characters) guaranteed to satisfy passwordProblem(). */
    public static function generatePassword(int $length = 16): string
    {
        $letters = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';
        $symbols = '-_.!@#';
        $all = $letters . $digits . $symbols;
        $pw = [$letters[random_int(0, strlen($letters) - 1)], $digits[random_int(0, strlen($digits) - 1)]];
        while (count($pw) < max(12, $length)) {
            $pw[] = $all[random_int(0, strlen($all) - 1)];
        }
        for ($i = count($pw) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$pw[$i], $pw[$j]] = [$pw[$j], $pw[$i]];
        }
        return implode('', $pw);
    }

    /** Insert a login_attempts row (failure unless $success) and return its id (0 if logging failed). */
    private static function record(string $email, string $ip, bool $success): int
    {
        try {
            $id = Database::insert('login_attempts', ['email' => mb_substr($email, 0, 190), 'ip' => $ip, 'success' => $success ? 1 : 0]);
            if (random_int(1, 50) === 1) {
                Database::execute('DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 2 DAY)');
            }
            return (int) $id;
        } catch (\Throwable) {
            return 0;     // never block login because logging failed
        }
    }

    private static function markSuccess(int $id): void
    {
        if ($id > 0) {
            try {
                Database::execute('UPDATE login_attempts SET success = 1 WHERE id = ?', [$id]);
            } catch (\Throwable) {
            }
        }
    }

    private static function forgetAttempt(int $id): void
    {
        if ($id > 0) {
            try {
                Database::execute('DELETE FROM login_attempts WHERE id = ?', [$id]);
            } catch (\Throwable) {
            }
        }
    }

    private static function isLockedOut(string $email, string $ip): bool
    {
        $window = (int) Config::get('auth.window', 900);
        $since = date('Y-m-d H:i:s', time() - $window);
        try {
            $pair = (int) Database::value(
                'SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND ip = ? AND email = ? AND created_at >= ?',
                [$ip, $email, $since]
            );
            if ($pair > (int) Config::get('auth.max_failures', 5)) {
                return true;
            }
            $perIp = (int) Database::value('SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND ip = ? AND created_at >= ?', [$ip, $since]);
            return $perIp > (int) Config::get('auth.max_ip_failures', 20);
        } catch (\Throwable) {
            return false;
        }
    }
}
