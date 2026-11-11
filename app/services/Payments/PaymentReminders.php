<?php
declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Services\DarazaPay\DarazaPayments;
use App\Services\Shop\Money;

/**
 * Admin > Payments: what can be done with a payment that was never completed (waiting, not confirmed, or failed):
 *   remind  - e-mail the payer a kind reminder with a link to finish paying (at most one reminder per payment per day)
 *   delete  - remove the record of a payment that never arrived (never a paid one; a waiting one only after an hour, so a late approval is not lost)
 * Works for the two gateway ledgers: mobile money (daraza) and card / online (pesapal). Every action is written to the audit trail and the activity log.
 */
final class PaymentReminders
{
    private const TABLES = ['daraza' => 'daraza_payments', 'pesapal' => 'pesapal_payments'];

    /** @return ?array<string,mixed> normalised: id, src, ref, purpose, email, name, minor, currency, status, created, paid */
    private static function row(string $src, int $id): ?array
    {
        if (!isset(self::TABLES[$src])) {
            return null;
        }
        $r = Database::one('SELECT * FROM ' . self::TABLES[$src] . ' WHERE id = ?', [$id]);
        if ($r === null || ($src === 'daraza' && $r['direction'] !== 'in')) {
            return null;
        }
        return [
            'id' => (int) $r['id'], 'src' => $src, 'ref' => (string) ($src === 'daraza' ? $r['reference'] : $r['merchant_reference']), 'purpose' => (string) $r['purpose'], 'ref_id' => (int) $r['ref_id'],
            'email' => trim((string) $r['payer_email']), 'name' => trim((string) $r['payer_name']), 'minor' => (int) $r['amount_minor'], 'currency' => (string) $r['currency'],
            'status' => (string) $r['status'], 'created' => (string) $r['created_at'], 'paid' => $r['status'] === 'paid',
        ];
    }

    /** Where the payer can finish paying. */
    public static function link(array $p): string
    {
        if ($p['src'] === 'daraza' && DarazaPayments::enabled($p['purpose'])) {
            return abs_url('pay/mobile-money/' . $p['ref']);
        }
        switch ($p['purpose']) {
            case 'academy_signup':
                return abs_url('academy/login');
            case 'academy_cert':
                return abs_url('academy/portal/certificates');
            case 'shop':
                $o = \App\Services\Shop\OrderService::find((int) $p['ref_id']);
                return $o !== null ? abs_url('shop/order/' . $o['token']) : abs_url('shop');
            case 'membership':
                return abs_url('membership');
            default:
                return abs_url('donate') . '#give-online';
        }
    }

    /** Can this payment be reminded or deleted at all? (an unfinished incoming payment of a gateway) */
    public static function actionable(string $src, string $status): bool
    {
        return isset(self::TABLES[$src]) && in_array($status, ['pending', 'failed'], true);
    }

    /** @return array{ok:bool,message:string} */
    public static function remind(string $src, int $id, ?int $adminId): array
    {
        $p = self::row($src, $id);
        if ($p === null) {
            return ['ok' => false, 'message' => 'Payment not found.'];
        }
        if ($p['paid']) {
            return ['ok' => false, 'message' => 'This payment is already paid: nothing to remind.'];
        }
        if (!Mailer::validEmail($p['email'])) {
            return ['ok' => false, 'message' => 'This payer left no valid e-mail address, so no reminder can be sent.'];
        }
        PaymentEvents::install();
        if ((int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'reminded' AND created_at > (NOW() - INTERVAL 1 DAY)", [mb_substr($p['ref'], 0, 80)], 0) > 0) {
            return ['ok' => false, 'message' => 'A reminder for this payment was already sent in the last 24 hours.'];
        }
        $site = (string) setting('site_name', 'Lumina Africa Foundation');
        $first = trim((string) strtok($p['name'], ' '));
        $what = DarazaPayments::PURPOSES[$p['purpose']] ?? 'your payment';
        $text = 'Dear ' . ($first !== '' ? $first : 'friend') . ",\n\n"
            . 'Our records show that your payment of ' . Money::format($p['minor'], $p['currency']) . ' (' . $what . ', reference ' . $p['ref'] . ") was not completed.\n\n"
            . "You can finish it here whenever you are ready:\n" . self::link($p) . "\n\n"
            . "If you have already paid, or you no longer wish to, please ignore this message or reply and tell us.\n\n"
            . "With thanks,\n" . $site . "\n";
        try {
            $ok = Mailer::send($p['email'], 'A reminder about your payment: ' . $p['ref'], $text);
        } catch (\Throwable $e) {
            Log::warning('Payment reminder failed: ' . $e::class);
            $ok = false;
        }
        if (!$ok) {
            return ['ok' => false, 'message' => 'The reminder e-mail could not be sent. Try again in a moment.'];
        }
        PaymentEvents::log('reminded', $src === 'daraza' ? 'mobile' : 'online', PaymentEvents::category($p['purpose']), $p['ref'], $p['minor'], $p['currency'], $p['name'] !== '' ? $p['name'] : $p['email'], 'Reminder e-mailed to the payer', $adminId);
        AuditLog::record('payment.remind', 'payment', $p['ref'], 'Sent a payment reminder (' . $p['ref'] . ')');
        return ['ok' => true, 'message' => 'Reminder sent to ' . $p['email'] . '.'];
    }

    /** @return array{ok:bool,message:string} */
    public static function delete(string $src, int $id, ?int $adminId): array
    {
        $p = self::row($src, $id);
        if ($p === null) {
            return ['ok' => false, 'message' => 'Payment not found.'];
        }
        if ($p['paid']) {
            return ['ok' => false, 'message' => 'A paid payment is never deleted: it is part of your accounts.'];
        }
        if ($p['status'] !== 'failed' && strtotime($p['created']) > time() - 3600) {
            return ['ok' => false, 'message' => 'This payment was started less than an hour ago and the payer may still approve it. Try again later.'];
        }
        Database::execute('DELETE FROM ' . self::TABLES[$src] . " WHERE id = ? AND status <> 'paid'", [$id]);
        PaymentEvents::log('deleted', $src === 'daraza' ? 'mobile' : 'online', PaymentEvents::category($p['purpose']), $p['ref'], $p['minor'], $p['currency'], $p['name'] !== '' ? $p['name'] : $p['email'], 'Unfinished payment record deleted by staff (it was ' . $p['status'] . ')', $adminId);
        AuditLog::record('payment.delete', 'payment', $p['ref'], 'Deleted an unfinished payment record (' . $p['ref'] . ', ' . $p['status'] . ')');
        return ['ok' => true, 'message' => 'The unfinished payment record was deleted. The audit trail keeps a note of it.'];
    }
}
