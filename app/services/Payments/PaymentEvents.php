<?php
declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Database;
use App\Core\Log;

/**
 * THE PAYMENT AUDIT TRAIL: one line for every step of every payment on the website, for any way of paying and any purpose.
 *   started    a payment page / attempt was opened for a payer
 *   requested  the payment request was sent (to the phone, to the gateway)
 *   success    the money arrived
 *   failed     it did not go through (declined, cancelled, refused, expired)
 *   unknown    no answer yet: somebody must check
 *   reversed   the gateway took the money back
 *   review     something did not add up: a person must look at it
 *   thanks     the thank-you e-mail was sent to the payer
 * It never stores a PIN, a key, a card number or a full phone number. The table is created on first use. Writing here never breaks a payment (every error is swallowed and logged).
 */
final class PaymentEvents
{
    /** event => group shown to the admin: attempt | success | failure | notice */
    public const EVENTS = [
        'started' => 'attempt', 'requested' => 'attempt', 'unknown' => 'attempt',
        'success' => 'success',
        'failed' => 'failure', 'reversed' => 'failure', 'review' => 'failure',
        'thanks' => 'notice', 'reminded' => 'notice', 'deleted' => 'notice',
    ];
    public const EVENT_LABELS = ['started' => 'Payment started', 'requested' => 'Request sent', 'success' => 'Successful', 'failed' => 'Unsuccessful', 'unknown' => 'Waiting for an answer', 'reversed' => 'Reversed', 'review' => 'Needs a look', 'thanks' => 'Thank-you e-mail sent', 'reminded' => 'Reminder sent', 'deleted' => 'Record deleted'];
    public const GROUPS = ['attempt' => 'Attempts', 'success' => 'Successful', 'failure' => 'Unsuccessful'];
    public const CATEGORIES = ['membership' => 'Membership', 'shop' => 'Shop', 'donations' => 'Donations', 'projects' => 'Projects', 'academy' => 'Academy fees', 'payout' => 'Payouts', 'other' => 'Other'];

    private static ?bool $ready = null;

    public static function install(): bool
    {
        if (self::$ready !== null) {
            return self::$ready;
        }
        try {
            // a SELECT first: CREATE TABLE (even IF NOT EXISTS) commits the open transaction, and payments are written inside transactions
            if (Database::tableExists('payment_events')) {
                return self::$ready = true;
            }
            Database::execute("CREATE TABLE IF NOT EXISTS `payment_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `event` VARCHAR(12) NOT NULL, `channel` VARCHAR(10) NOT NULL DEFAULT 'manual',
  `category` VARCHAR(12) NOT NULL DEFAULT 'other', `reference` VARCHAR(80) NOT NULL DEFAULT '', `who` VARCHAR(190) NOT NULL DEFAULT '', `amount_minor` BIGINT NULL, `currency` CHAR(3) NOT NULL DEFAULT 'UGX',
  `detail` VARCHAR(250) NOT NULL DEFAULT '', `actor` INT UNSIGNED NULL, PRIMARY KEY (`id`), KEY `ix_pe_created` (`created_at`), KEY `ix_pe_ref` (`reference`, `event`), KEY `ix_pe_event` (`event`, `created_at`),
  KEY `ix_pe_cat` (`category`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return self::$ready = true;
        } catch (\Throwable) {
            return self::$ready = false;
        }
    }

    /** Category of a payment purpose (the purposes of the gateway ledgers). */
    public static function category(string $purpose): string
    {
        return ['donation' => 'donations', 'project' => 'projects', 'shop' => 'shop', 'membership' => 'membership', 'academy_signup' => 'academy', 'academy_cert' => 'academy', 'payout' => 'payout'][$purpose] ?? 'other';
    }

    /** Write one line. $who is a name or an e-mail (never a phone number in full). Never throws. */
    public static function log(string $event, string $channel, string $category, string $reference, ?int $amountMinor = null, string $currency = 'UGX', string $who = '', string $detail = '', ?int $actor = null): void
    {
        if (!isset(self::EVENTS[$event]) || !self::install()) {
            return;
        }
        try {
            Database::insert('payment_events', [
                'event' => $event, 'channel' => in_array($channel, ['mobile', 'online', 'manual'], true) ? $channel : 'manual',
                'category' => isset(self::CATEGORIES[$category]) ? $category : 'other', 'reference' => mb_substr($reference, 0, 80), 'who' => mb_substr(trim($who), 0, 190),
                'amount_minor' => $amountMinor, 'currency' => strtoupper(substr($currency, 0, 3)) ?: 'UGX', 'detail' => mb_substr(trim($detail), 0, 250), 'actor' => $actor,
            ]);
        } catch (\Throwable $e) {
            Log::warning('payment_events write failed: ' . $e::class);
        }
    }

    /** A phone number with its middle hidden: +256772***456. */
    public static function maskPhone(?string $phone): string
    {
        $p = preg_replace('/\s+/', '', (string) $phone) ?? '';
        return strlen($p) >= 8 ? substr($p, 0, 7) . '***' . substr($p, -3) : '';
    }

    /** @return array{0:string,1:list<mixed>} */
    private static function where(array $f): array
    {
        $w = [];
        $v = [];
        $since = PaymentsDashboard::since((string) ($f['range'] ?? 'all'));
        if ($since !== null) {
            $w[] = 'created_at >= ?';
            $v[] = $since;
        }
        $group = (string) ($f['group'] ?? '');
        if (isset(self::GROUPS[$group])) {
            $in = array_keys(array_filter(self::EVENTS, static fn(string $g): bool => $g === $group));
            $w[] = 'event IN (' . implode(',', array_fill(0, count($in), '?')) . ')';
            array_push($v, ...$in);
        }
        if (isset(self::CATEGORIES[(string) ($f['category'] ?? '')])) {
            $w[] = 'category = ?';
            $v[] = $f['category'];
        }
        if (isset(['mobile' => 1, 'online' => 1, 'manual' => 1][(string) ($f['channel'] ?? '')])) {
            $w[] = 'channel = ?';
            $v[] = $f['channel'];
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 60)) . '%';
            $w[] = '(reference LIKE ? OR who LIKE ? OR detail LIKE ?)';
            array_push($v, $like, $like, $like);
        }
        return [$w === [] ? '' : ' WHERE ' . implode(' AND ', $w), $v];
    }

    /** @return array{rows:list<array<string,mixed>>,total:int} */
    public static function rows(array $f, int $limit, int $offset = 0): array
    {
        if (!self::install()) {
            return ['rows' => [], 'total' => 0];
        }
        [$where, $v] = self::where($f);
        $total = (int) Database::value('SELECT COUNT(*) FROM payment_events' . $where, $v, 0);
        $rows = Database::all('SELECT * FROM payment_events' . $where . ' ORDER BY id DESC LIMIT ' . max(1, min(500, $limit)) . ' OFFSET ' . max(0, $offset), $v);
        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * Counts for a range: attempts started, successes, failures, and the success rate (successes of the attempts that finished).
     * @return array{started:int,success:int,failed:int,waiting:int,rate:int}
     */
    public static function counts(string $range): array
    {
        $out = ['started' => 0, 'success' => 0, 'failed' => 0, 'waiting' => 0, 'rate' => 0];
        if (!self::install()) {
            return $out;
        }
        $since = PaymentsDashboard::since($range);
        $w = $since === null ? '' : ' WHERE created_at >= ?';
        foreach (Database::all('SELECT event, COUNT(*) n FROM payment_events' . $w . ' GROUP BY event', $since === null ? [] : [$since]) as $r) {
            $n = (int) $r['n'];
            match ($r['event']) {
                'started' => $out['started'] += $n,
                'success' => $out['success'] += $n,
                'failed', 'reversed', 'review' => $out['failed'] += $n,
                default => null,
            };
        }
        $done = $out['success'] + $out['failed'];
        $out['waiting'] = max(0, $out['started'] - $done);
        $out['rate'] = $done > 0 ? (int) round($out['success'] / $done * 100) : 0;
        return $out;
    }

    /** Has this event already been written for this reference? (used so a thank-you is sent once) */
    public static function seen(string $event, string $reference): bool
    {
        return $reference !== '' && self::install() && Database::value('SELECT 1 FROM payment_events WHERE event = ? AND reference = ? LIMIT 1', [$event, mb_substr($reference, 0, 80)], null) !== null;
    }
}
