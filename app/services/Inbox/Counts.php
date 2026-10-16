<?php
declare(strict_types=1);

namespace App\Services\Inbox;

use App\Core\Database;

/**
 * "Needs attention" counters of the admin inbox, fetched with ONE query and memoised for the request.
 * Used by the sidebar badges (config/admin_nav/inbox.php) and available to the dashboard:
 *
 *   \App\Services\Inbox\Counts::get('messages')        // unread contact messages (status "new")
 *   \App\Services\Inbox\Counts::all()                  // ['messages'=>n, 'involvement'=>n, 'applications'=>n, 'donations'=>n]
 *
 * Keys: messages (new messages), involvement (new requests), applications (new applications), donations (pledged, not yet received).
 */
final class Counts
{
    /** @var array<string,int>|null */
    private static ?array $cache = null;

    /** @return array<string,int> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        try {
            $row = Database::one(
                "SELECT
                    (SELECT COUNT(*) FROM contact_messages WHERE status = 'new') AS messages,
                    (SELECT COUNT(*) FROM involvement_requests WHERE status = 'new') AS involvement,
                    (SELECT COUNT(*) FROM applications WHERE status = 'new') AS applications,
                    (SELECT COUNT(*) FROM donations WHERE status = 'pledged') AS donations"
            ) ?? [];
        } catch (\Throwable) {
            $row = [];
        }
        return self::$cache = [
            'messages'     => (int) ($row['messages'] ?? 0),
            'involvement'  => (int) ($row['involvement'] ?? 0),
            'applications' => (int) ($row['applications'] ?? 0),
            'donations'    => (int) ($row['donations'] ?? 0),
        ];
    }

    public static function get(string $key): int
    {
        return self::all()[$key] ?? 0;
    }

    /** Forget the memoised numbers (after changing statuses within the same request). */
    public static function flush(): void
    {
        self::$cache = null;
    }
}
