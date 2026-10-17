<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Table `newsletter_deliveries` = one row per (issue, address) of an e-mail campaign. UNIQUE(newsletter_id, email) is what guarantees an issue
 * is never sent twice to the same address. Admin only: the addresses are personal data, never log them. Written by the mailing queue only.
 */
final class NewsletterDelivery extends Model
{
    protected static string $table = 'newsletter_deliveries';
    protected static string $order = 'id ASC';

    public const STATUSES = ['queued', 'sent', 'failed', 'skipped'];
    public const MAX_ATTEMPTS = 3;

    /**
     * Erasure: take a person out of every campaign record when their subscriber row is deleted (or their data is erased). The rows stay, so the
     * counters and the "never twice" guard are unchanged, but the address is replaced by a unique placeholder and the error text is cleared.
     * A row still waiting to be sent becomes "skipped". Call it BEFORE deleting the subscriber (the foreign key would otherwise lose the link).
     *
     * @param list<int|string>    $subscriberIds
     * @param list<string>        $emails
     * @return int rows scrubbed
     */
    public static function erase(array $subscriberIds = [], array $emails = []): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $subscriberIds), static fn(int $i): bool => $i > 0)));
        $emails = array_values(array_unique(array_filter(array_map(static fn($e): string => strtolower(trim((string) $e)), $emails), static fn(string $e): bool => $e !== '')));
        $where = [];
        $params = [];
        if ($ids !== []) {
            $where[] = 'subscriber_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
            array_push($params, ...$ids);
        }
        if ($emails !== []) {
            $where[] = 'email IN (' . implode(',', array_fill(0, count($emails), '?')) . ')';
            array_push($params, ...$emails);
        }
        if ($where === []) {
            return 0;
        }
        $cond = '(' . implode(' OR ', $where) . ") AND email NOT LIKE 'erased-d%@invalid.invalid'";
        $issues = array_map('intval', array_column(Database::all("SELECT DISTINCT newsletter_id FROM newsletter_deliveries WHERE {$cond}", $params), 'newsletter_id'));
        if ($issues === []) {
            return 0;
        }
        Database::execute("UPDATE newsletter_deliveries SET status = 'skipped', last_error = 'No longer on the list' WHERE status = 'queued' AND {$cond}", $params);
        $n = Database::execute("UPDATE newsletter_deliveries SET email = CONCAT('erased-d', id, '@invalid.invalid'), subscriber_id = NULL, last_error = IF(status = 'skipped', last_error, NULL) WHERE {$cond}", $params);
        foreach ($issues as $issueId) {
            Database::execute(
                "UPDATE newsletters n SET n.email_total = (SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = n.id),
                        n.email_sent = (SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = n.id AND status = 'sent'),
                        n.email_failed = (SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = n.id AND status = 'failed'), n.updated_at = n.updated_at
                  WHERE n.id = ? AND n.email_status <> 'none'",
                [$issueId]
            );
        }
        return $n;
    }
}
