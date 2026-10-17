<?php
declare(strict_types=1);

namespace App\Services\Membership;

use App\Core\Session;

/**
 * One-time form tokens for the staff forms that must not run twice (record a payment, start a new term): the page prints a token, the
 * handler accepts it ONCE. A double click, a re-sent request or the Back button then cannot create a second receipt or a second term.
 * The tokens live in the staff member's own session (PHP serialises requests of one session, so two clicks cannot both consume it).
 *
 *   <input type="hidden" name="pay[_once]" value="<?= e(OnceToken::issue('pay:' . $id)) ?>">
 *   if (!OnceToken::consume('pay:' . $id, (string) ($pay['_once'] ?? ''))) { ...already submitted... }
 */
final class OnceToken
{
    private const KEY = '_member_once';
    private const MAX_PER_SCOPE = 25;
    private const LIFETIME = 7200;

    /** A fresh token for a scope (for example "pay:12"). */
    public static function issue(string $scope): string
    {
        $token = bin2hex(random_bytes(16));
        $all = (array) Session::get(self::KEY, []);
        $mine = array_filter((array) ($all[$scope] ?? []), static fn($t): bool => (int) $t > time() - self::LIFETIME);
        $mine[$token] = time();
        $all[$scope] = array_slice($mine, -self::MAX_PER_SCOPE, null, true);
        Session::put(self::KEY, $all);
        return $token;
    }

    /** True exactly once per issued token. */
    public static function consume(string $scope, string $token): bool
    {
        if (preg_match('/^[a-f0-9]{32}$/', $token) !== 1) {
            return false;
        }
        $all = (array) Session::get(self::KEY, []);
        $issued = $all[$scope][$token] ?? null;
        if ($issued === null || (int) $issued < time() - self::LIFETIME) {
            return false;
        }
        unset($all[$scope][$token]);
        Session::put(self::KEY, $all);
        return true;
    }
}
