<?php
declare(strict_types=1);

namespace App\Services\Security;

use App\Core\Env;
use App\Core\Request;
use App\Core\Session;
use App\Core\Settings;

/**
 * THE LOCK ON THE ADMIN PAGES: after N idle minutes (Admin > Security, default 15) the screen locks. The person stays signed in but must type their password again to carry on
 * (the page they were on is kept). Every admin request goes through enforce() (the 'auth' middleware). Background requests (JSON, XMLHttpRequest) do not count as activity, so a page
 * that refreshes itself cannot keep an unattended screen open. .env AUTH_LOCK_MINUTES overrides the setting (0 = off).
 */
final class AdminLock
{
    public static function minutes(): int
    {
        $e = Env::get('AUTH_LOCK_MINUTES');
        $m = $e !== null && trim($e) !== '' && ctype_digit(trim($e)) ? (int) trim($e) : (int) Settings::get('security_lock_minutes', '15');
        return max(0, min(240, $m));
    }

    public static function isLocked(): bool
    {
        return Session::get('_locked') !== null;
    }

    public static function lock(): void
    {
        Session::put('_locked', 1);
    }

    public static function unlock(): void
    {
        Session::forget('_locked');
        Session::put('_act', time());
    }

    /** Called for each admin request. @return bool true = the lock screen must be shown instead of the page */
    public static function enforce(Request $request): bool
    {
        $m = self::minutes();
        if ($m < 1) {
            return false;
        }
        if (self::isLocked()) {
            return true;
        }
        $now = time();
        $last = (int) Session::get('_act', $now);          // a session that began before this feature simply starts counting now
        if ($now - $last > $m * 60) {
            self::lock();
            return true;
        }
        if (!self::background($request)) {
            Session::put('_act', $now);
        }
        return false;
    }

    private static function background(Request $request): bool
    {
        return $request->expectsJson() || strtolower((string) $request->header('X-Requested-With', '')) === 'xmlhttprequest';
    }
}
