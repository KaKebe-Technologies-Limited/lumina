<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Hardened session wrapper (started once by App::run()).
 *
 *   Session::put('k', 'v'); Session::get('k'); Session::has('k'); Session::forget('k'); Session::pull('k');
 *   Session::regenerate();   // call on privilege change (Auth::login does)
 *   Session::flash('k','v'); // available on the NEXT request only; Session::getFlash('k')
 *
 * LAZY: reading never starts a session; the first write does (see start()). Anonymous GETs set no cookie.
 */
final class Session
{
    private static bool $started = false;

    /**
     * Called by App::run() for every request. The session is LAZY: it is only really started when the browser already
     * sent a session cookie whose server-side file exists, or later when something writes to it (Session::put(),
     * flash(), regenerate() ...). Anonymous page views, robots.txt, sitemap.xml, 404s and bots therefore create no
     * cookie and no session file. Pass $force = true to start unconditionally.
     */
    public static function start(bool $force = false): void
    {
        if (self::$started || PHP_SAPI === 'cli') {
            return;
        }
        if (!$force && !self::hasLiveCookie()) {
            return;
        }
        self::boot();
    }

    private static function name(): string
    {
        return (string) Config::get('session.name', 'lumina_session');
    }

    private static function dir(): string
    {
        return STORAGE_PATH . '/sessions';
    }

    /** True when the request carries a well-formed session cookie whose session file still exists. */
    private static function hasLiveCookie(): bool
    {
        $req = Request::current();
        $id = $req ? $req->cookie(self::name()) : ($_COOKIE[self::name()] ?? null);
        if (!is_string($id) || !preg_match('/^[A-Za-z0-9,-]{22,256}$/', $id)) {
            return false;
        }
        $dir = self::dir();
        if (is_dir($dir) && is_writable($dir)) {
            return is_file($dir . '/sess_' . $id);
        }
        return true;   // custom save path unavailable: let PHP decide
    }

    /** Start the session now if it is not running yet (used by every write). */
    private static function ensure(): void
    {
        if (!self::$started && PHP_SAPI !== 'cli') {
            self::boot();
        }
    }

    private static function boot(): void
    {
        if (self::$started || PHP_SAPI === 'cli') {
            return;
        }
        $req = Request::current();
        $secure = $req ? $req->isSecure() : false;
        $base = $req ? $req->basePath() : '';

        $dir = self::dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            session_save_path($dir);
        }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_maxlifetime', (string) max(7200, (int) Config::get('session.idle', 7200)));
        // the save path is custom, so distro cron cleaners never see it: have PHP purge it itself (about 1% of starts)
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');
        ini_set('session.sid_length', '48');
        session_cache_limiter('');          // Response sends its own Cache-Control; no 1981 Expires / no-store from PHP
        session_name(self::name());
        session_set_cookie_params([
            'lifetime' => (int) Config::get('session.lifetime', 0),
            'path'     => $base === '' ? '/' : $base . '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
        self::$started = true;

        // age out flash data: values flashed on the previous request become readable now, then expire
        $_SESSION['_flash_old'] = $_SESSION['_flash_new'] ?? [];
        $_SESSION['_flash_new'] = [];

        // idle timeout for logged-in users
        $idle = (int) Config::get('session.idle', 7200);
        if (isset($_SESSION['_last_seen']) && $idle > 0 && (time() - (int) $_SESSION['_last_seen']) > $idle && isset($_SESSION['user_id'])) {
            self::destroy();
            self::boot();
            return;
        }
        $_SESSION['_last_seen'] = time();
    }

    /** Reads only make sense when a session is running (the CLI uses the plain $_SESSION array). */
    private static function active(): bool
    {
        return self::$started || PHP_SAPI === 'cli';
    }

    /** True once the session is running (a cookie was sent or something wrote to it). */
    public static function isStarted(): bool
    {
        return self::$started;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::active()) {
            return $default;
        }
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        self::ensure();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return self::active() && isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        if (!self::active()) {
            return;
        }
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        if (!self::active()) {
            return $default;
        }
        $v = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $v;
    }

    /** Store a value readable only during the next request. */
    public static function flash(string $key, mixed $value): void
    {
        self::ensure();
        $_SESSION['_flash_new'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        if (!self::active()) {
            return $default;
        }
        return $_SESSION['_flash_old'][$key] ?? $_SESSION['_flash_new'][$key] ?? $default;
    }

    public static function regenerate(): void
    {
        self::ensure();
        if (self::$started) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (!self::$started) {
            return;
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'domain' => $p['domain'],
                'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
        }
        session_destroy();
        self::$started = false;
    }
}
