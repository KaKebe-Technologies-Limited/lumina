<?php
declare(strict_types=1);

namespace App\Core;

/**
 * CSRF protection. Router verifies automatically for POST/PUT/PATCH/DELETE.
 * Every <form method="post"> MUST include <?= csrf_field() ?>. AJAX: send header X-CSRF-TOKEN (see <meta name="csrf-token">).
 */
final class Csrf
{
    public const FIELD = '_token';
    /** Anonymous (cookie-less) visitors get a signed, time-limited token instead of a session. */
    private const STATELESS_MAX_AGE = 21600;   // 6 h

    /**
     * Session token when a session is running or the request is for /admin; otherwise a stateless HMAC token so
     * anonymous page views never create a session cookie or file. Public forms are still guarded by honeypot,
     * timing and throttling; admin forms always need the real session token.
     */
    public static function token(): string
    {
        if (self::needsSession()) {
            $t = Session::get('_csrf');
            if (!is_string($t) || strlen($t) < 32) {
                $t = bin2hex(random_bytes(32));
                Session::put('_csrf', $t);
            }
            return $t;
        }
        $ts = (string) time();
        return 's.' . $ts . '.' . self::sign($ts);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD . '" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify(Request $request): bool
    {
        $sent = $request->post(self::FIELD) ?? $request->header('X-CSRF-TOKEN');
        if (!is_string($sent) || $sent === '') {
            return false;
        }
        $real = Session::get('_csrf');
        if (is_string($real) && $real !== '' && hash_equals($real, $sent)) {
            return true;
        }
        // stateless token: public (non-admin) forms only
        if ($request->pathIs('/admin') || !str_starts_with($sent, 's.')) {
            return false;
        }
        $parts = explode('.', $sent);
        if (count($parts) !== 3 || !ctype_digit($parts[1])) {
            return false;
        }
        $age = time() - (int) $parts[1];
        return $age >= -60 && $age <= self::STATELESS_MAX_AGE && hash_equals(self::sign($parts[1]), $parts[2]);
    }

    private static function needsSession(): bool
    {
        if (Session::isStarted()) {
            return true;
        }
        $req = Request::current();
        return $req === null || $req->pathIs('/admin');
    }

    private static function sign(string $ts): string
    {
        return hash_hmac('sha256', 'csrf|' . $ts, Config::appKey());
    }
}
