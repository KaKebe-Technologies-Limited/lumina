<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Fixed-window rate limiter backed by the `rate_limits` table (works across PHP processes).
 *
 *   if (!RateLimit::attempt('contact:' . $request->ip(), 5, 600)) { throw new HttpException(429); }
 *   RateLimit::tooMany($key, 5)       // read-only check
 *   RateLimit::hit($key, 600)         // count one hit (returns hits so far)
 *   RateLimit::retryAfter($key)       // seconds until the window resets
 *   RateLimit::clear($key)
 *
 * Fails OPEN (allows the request) if the database is unavailable, so a DB hiccup never blocks visitors.
 */
final class RateLimit
{
    /**
     * The stored key: a KEYED hash (HMAC with the application key), so the visitor address inside a throttle key cannot be recovered by
     * trying every address (a plain sha1 of a short, guessable string can). 160 bits fit the CHAR(40) column.
     */
    private static function k(string $key): string
    {
        return substr(hash_hmac('sha256', $key, Config::appKey()), 0, 40);
    }

    /** Count a hit and return true while the caller is still within $max hits per $decay seconds. */
    public static function attempt(string $key, int $max, int $decay): bool
    {
        try {
            return self::hit($key, $decay) <= $max;
        } catch (\Throwable) {
            return true;
        }
    }

    public static function hit(string $key, int $decay): int
    {
        $k = self::k($key);
        $now = time();
        if (random_int(1, 20) === 1) {
            Database::execute('DELETE FROM rate_limits WHERE expires_at < ?', [$now]);
        }
        // ONE atomic statement (no read-then-write race): a new or expired window restarts at 1, otherwise the counter is incremented.
        // `hits` is assigned before `expires_at` so the IF() still sees the old expiry.
        Database::execute(
            'INSERT INTO rate_limits (rl_key, hits, expires_at) VALUES (?, (@rl_hits := 1), ?) '
            . 'ON DUPLICATE KEY UPDATE hits = (@rl_hits := IF(expires_at < ?, 1, hits + 1)), expires_at = IF(expires_at < ?, VALUES(expires_at), expires_at)',
            [$k, $now + $decay, $now, $now]
        );
        // the user variable is private to this connection and was set by the statement above: concurrent requests each read their own number
        return max(1, (int) Database::value('SELECT @rl_hits', [], 1));
    }

    public static function tooMany(string $key, int $max): bool
    {
        try {
            $row = Database::one('SELECT hits, expires_at FROM rate_limits WHERE rl_key = ?', [self::k($key)]);
            return $row !== null && (int) $row['expires_at'] >= time() && (int) $row['hits'] >= $max;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function retryAfter(string $key): int
    {
        try {
            $exp = (int) Database::value('SELECT expires_at FROM rate_limits WHERE rl_key = ?', [self::k($key)], 0);
            return max(1, $exp - time());
        } catch (\Throwable) {
            return 60;
        }
    }

    public static function clear(string $key): void
    {
        try {
            Database::execute('DELETE FROM rate_limits WHERE rl_key = ?', [self::k($key)]);
        } catch (\Throwable) {
        }
    }
}
