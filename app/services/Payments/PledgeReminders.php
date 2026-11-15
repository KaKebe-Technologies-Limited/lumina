<?php
declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Services\Shop\Money;

/**
 * Admin > Donations: a warm, thankful follow-up e-mail to a donor whose pledge is still "pledged" (not received yet).
 * It thanks them, says why their gift matters, and gives the foundation's account details (Settings > Donations: bank, mobile money, PayPal, online link).
 * The wording can be changed in Settings > Donations > "Pledge reminder e-mail"; empty = the built-in text below. At most one reminder per pledge per 24 hours
 * (the audit trail 'reminded' event is the memory), every reminder is written to the payment audit trail and the activity log.
 * Placeholders: {first_name} {amount} {pledge_date} {payment_details} {donate_link} {site_name}.
 */
final class PledgeReminders
{
    public const SETTING = 'donation_reminder_message';

    public const DEFAULT = "Dear {first_name},\n\n"
        . "Thank you, from all of us at {site_name}, for your generous pledge of {amount} on {pledge_date}. Your kindness means a great deal to us.\n\n"
        . "Gifts like yours are what keep our work going: they help young people and families in Northern Uganda learn, grow and build a better future, and they let us plan ahead with confidence. Every contribution, whatever its size, makes a real difference to the lives we serve, and we are deeply grateful that you chose to stand with us.\n\n"
        . "If you are ready to complete your gift, you can send it to:\n\n{payment_details}\n\n"
        . "{donate_link}"
        . "When you have sent it, please reply to this e-mail with the transaction number so that we can confirm your gift and send your receipt. If you have already given, please accept our heartfelt thanks and simply let us know. If your plans have changed, that is perfectly fine too: we appreciate you for having thought of us.\n\n"
        . "With gratitude,\n{site_name}";

    /** @return ?array<string,mixed> */
    private static function row(int $id): ?array
    {
        return Database::one('SELECT * FROM donations WHERE id = ?', [$id]);
    }

    /** The account details to put in the e-mail (empty string when none are set). */
    public static function paymentDetails(): string
    {
        $parts = [];
        $bank = trim((string) setting('donate_bank_details', ''));
        $mobile = trim((string) setting('donate_mobile_money', ''));
        $paypal = trim((string) setting('donate_paypal_email', ''));
        if ($bank !== '') {
            $parts[] = "Bank transfer\n" . $bank;
        }
        if ($mobile !== '') {
            $parts[] = "Mobile money\n" . $mobile;
        }
        if ($paypal !== '') {
            $parts[] = "PayPal\n" . $paypal;
        }
        return implode("\n\n", $parts);
    }

    public static function template(): string
    {
        $t = trim((string) setting(self::SETTING, ''));
        return $t !== '' ? $t : self::DEFAULT;
    }

    /** Fill the placeholders. @param array<string,string> $vars */
    public static function fill(string $text, array $vars): string
    {
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{' . $k . '}'] = $v;
        }
        return trim((string) preg_replace("/\n{3,}/", "\n\n", strtr($text, $map))) . "\n";
    }

    /** @param array<string,mixed> $d a donations row */
    public static function message(array $d): string
    {
        $first = trim((string) strtok(trim((string) ($d['donor_name'] ?? '')), ' '));
        $minor = (int) round((float) $d['amount'] * 100);
        return self::fill(self::template(), [
            'first_name' => $first !== '' ? $first : 'friend',
            'amount' => Money::format($minor, (string) $d['currency']),
            'pledge_date' => format_date((string) $d['created_at'], 'j F Y'),
            'payment_details' => self::paymentDetails(),
            'donate_link' => ((string) setting('donate_method', 'both') !== 'link' || trim((string) setting('donate_url', '')) !== '') ? 'You can also give online, by card or mobile money, at ' . abs_url('donate') . "\n\n" : '',
            'site_name' => (string) setting('site_name', 'Lumina Africa Foundation'),
        ]);
    }

    /** Pledges that can still be reminded: pledged, with an amount and a usable e-mail address. @return list<array<string,mixed>> */
    public static function pending(int $limit = 100): array
    {
        $rows = Database::all("SELECT * FROM donations WHERE status = 'pledged' AND amount IS NOT NULL AND amount > 0 AND donor_email IS NOT NULL AND donor_email <> '' ORDER BY id LIMIT " . max(1, min(500, $limit)));
        return array_values(array_filter($rows, static fn(array $d): bool => Mailer::validEmail((string) $d['donor_email'])));
    }

    public static function count(): int
    {
        return count(self::pending(500));
    }

    private static function recently(string $ref): bool
    {
        PaymentEvents::install();
        return (int) Database::value("SELECT COUNT(*) FROM payment_events WHERE reference = ? AND event = 'reminded' AND created_at > (NOW() - INTERVAL 1 DAY)", [$ref], 0) > 0;
    }

    /** @return array{ok:bool,message:string} */
    public static function remind(int $id, ?int $adminId): array
    {
        $d = self::row($id);
        if ($d === null) {
            return ['ok' => false, 'message' => 'Donation not found.'];
        }
        if ($d['status'] !== 'pledged') {
            return ['ok' => false, 'message' => 'Only a pledge that has not been received yet can be reminded.'];
        }
        if ($d['amount'] === null || (float) $d['amount'] <= 0) {
            return ['ok' => false, 'message' => 'This pledge has no amount, so there is nothing to remind about.'];
        }
        $email = trim((string) ($d['donor_email'] ?? ''));
        if (!Mailer::validEmail($email)) {
            return ['ok' => false, 'message' => 'This donor left no valid e-mail address, so no reminder can be sent.'];
        }
        if (self::paymentDetails() === '') {
            return ['ok' => false, 'message' => 'Add your account details first (Settings > Donations: bank transfer, mobile money or PayPal), so the e-mail can tell donors where to send their gift.'];
        }
        $ref = 'DON-' . $id;
        if (self::recently($ref)) {
            return ['ok' => false, 'message' => 'A reminder for this pledge was already sent in the last 24 hours.'];
        }
        $site = (string) setting('site_name', 'Lumina Africa Foundation');
        try {
            $ok = Mailer::send($email, 'Thank you for your pledge to ' . $site, self::message($d));
        } catch (\Throwable $e) {
            Log::warning('Pledge reminder failed: ' . $e::class);
            $ok = false;
        }
        if (!$ok) {
            return ['ok' => false, 'message' => 'The e-mail could not be sent. Try again in a moment (and check Settings > E-mail).'];
        }
        $cat = str_starts_with((string) $d['reference'], 'PROJECT:') ? 'projects' : 'donations';
        PaymentEvents::log('reminded', 'manual', $cat, $ref, (int) round((float) $d['amount'] * 100), (string) $d['currency'], (string) ($d['donor_name'] ?: $email), 'Thank-you and reminder e-mailed about the pledge', $adminId);
        AuditLog::record('donation.remind', 'donation', $id, 'Sent a thankful pledge reminder (' . $ref . ')');
        return ['ok' => true, 'message' => 'A thankful reminder was e-mailed to ' . $email . '.'];
    }

    /** @return array{ok:bool,sent:int,skipped:int,message:string} */
    public static function remindAll(?int $adminId): array
    {
        if (self::paymentDetails() === '') {
            return ['ok' => false, 'sent' => 0, 'skipped' => 0, 'message' => 'Add your account details first (Settings > Donations: bank transfer, mobile money or PayPal), so the e-mail can tell donors where to send their gift.'];
        }
        $sent = $skipped = 0;
        foreach (self::pending(100) as $d) {
            self::remind((int) $d['id'], $adminId)['ok'] ? $sent++ : $skipped++;
        }
        Mailer::closeConnection();
        return ['ok' => $sent > 0, 'sent' => $sent, 'skipped' => $skipped, 'message' => $sent > 0
            ? $sent . ' thankful reminder' . ($sent === 1 ? '' : 's') . ' sent.' . ($skipped > 0 ? ' ' . $skipped . ' skipped (reminded in the last 24 hours, or no usable e-mail).' : '')
            : ($skipped > 0 ? 'Nothing sent: ' . $skipped . ' pledge' . ($skipped === 1 ? ' was' : 's were') . ' reminded in the last 24 hours or could not be e-mailed.' : 'There are no pledges waiting with an e-mail address.')];
    }
}
