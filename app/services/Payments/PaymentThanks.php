<?php
declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Log;
use App\Core\Mailer;
use App\Services\Shop\Money;

/**
 * THANK YOU: a warm e-mail to whoever has just paid, for any payment on the website (a donation, a gift to a project, a membership payment, an Academy fee). It is sent ONCE per payment
 * (the audit trail remembers that it was sent). The shop has its own order e-mails, so shop payments are not sent here. Never blocks or breaks a payment.
 */
final class PaymentThanks
{
    private const WHAT = [
        'donations' => 'your donation', 'projects' => 'your gift to our project', 'membership' => 'your membership payment', 'academy' => 'your LAF Academy payment', 'shop' => 'your order',
    ];

    /**
     * @param string $category donations | projects | membership | academy | shop
     * @param string $channel mobile | online | manual
     * @param string $item what it was for, in words ("Equip one complete hub", "Certificate fee"), optional
     * @param string $receipt a receipt number, optional
     */
    public static function send(string $category, string $channel, string $email, string $name, int $amountMinor, string $currency, string $reference, string $item = '', string $receipt = ''): bool
    {
        $email = trim($email);
        if ($category === 'shop' || !Mailer::validEmail($email) || $reference === '' || PaymentEvents::seen('thanks', $reference)) {
            return false;
        }
        try {
            $first = trim((string) strtok(trim($name), ' '));
            $site = (string) setting('site_name', 'Lumina Africa Foundation');
            $what = self::WHAT[$category] ?? 'your payment';
            $amount = Money::format($amountMinor, strtoupper($currency));
            $text = 'Dear ' . ($first !== '' ? $first : 'friend') . ",\n\n"
                . 'Thank you! We have received ' . $what . ' of ' . $amount . ($item !== '' ? ' (' . $item . ')' : '') . '.' . "\n\n"
                . 'Reference: ' . $reference . ($receipt !== '' ? "\nReceipt number: " . $receipt : '') . "\n"
                . 'Paid by: ' . ['mobile' => 'MTN or Airtel mobile money', 'online' => 'card / online', 'manual' => 'bank or cash'][$channel] . "\n\n"
                . self::closing($category) . "\n\n"
                . 'With gratitude,' . "\n" . $site . "\n" . abs_url('') . "\n";
            $ok = Mailer::send($email, 'Thank you for your payment: ' . $reference, $text);
            if ($ok) {
                PaymentEvents::log('thanks', $channel, $category, $reference, $amountMinor, $currency, $email, 'Thank-you e-mail sent');
            }
            return $ok;
        } catch (\Throwable $e) {
            Log::warning('Payment thank-you e-mail failed: ' . $e::class);
            return false;
        }
    }

    /** A donation a person at the foundation recorded as RECEIVED (bank, cash, PayPal, GoFundMe...): audit line + thank-you. Mobile money and Pesapal write their own. */
    public static function donationReceived(int $id): void
    {
        try {
            $d = \App\Core\Database::one('SELECT * FROM donations WHERE id = ?', [$id]);
            if ($d === null || $d['status'] !== 'received' || in_array((string) $d['method'], ['mobile_money', 'pesapal'], true) || $d['amount'] === null) {
                return;
            }
            $cat = str_starts_with((string) $d['reference'], 'PROJECT:') ? 'projects' : 'donations';
            $ref = (string) (($d['receipt_no'] ?? '') !== '' ? $d['receipt_no'] : 'DON-' . $id);
            $minor = (int) round((float) $d['amount'] * 100);
            if (!PaymentEvents::seen('success', $ref)) {
                PaymentEvents::log('success', 'manual', $cat, $ref, $minor, (string) $d['currency'], (int) $d['is_anonymous'] === 1 ? 'Anonymous' : (string) $d['donor_name'], 'Recorded by staff (' . $d['method'] . ')');
            }
            self::send($cat, 'manual', (string) ($d['donor_email'] ?? ''), (string) $d['donor_name'], $minor, (string) $d['currency'], $ref, '', (string) ($d['receipt_no'] ?? ''));
        } catch (\Throwable $e) {
            Log::warning('donationReceived hook failed: ' . $e::class);
        }
    }

    /** A membership payment recorded as RECEIVED by staff: audit line + thank-you to the member. */
    public static function memberPaymentReceived(int $paymentId): void
    {
        try {
            $p = \App\Core\Database::one('SELECT p.*, m.full_name, m.email FROM member_payments p JOIN members m ON m.id = p.member_id WHERE p.id = ?', [$paymentId]);
            if ($p === null || $p['status'] !== 'received' || in_array((string) $p['method'], ['mobile_money', 'pesapal'], true)) {
                return;
            }
            $ref = (string) (($p['receipt_number'] ?? '') !== '' ? $p['receipt_number'] : 'MEM-' . $paymentId);
            $minor = (int) round((float) $p['amount'] * 100);
            if (!PaymentEvents::seen('success', $ref)) {
                PaymentEvents::log('success', 'manual', 'membership', $ref, $minor, (string) $p['currency'], (string) $p['full_name'], 'Recorded by staff (' . $p['method'] . ')', $p['recorded_by'] !== null ? (int) $p['recorded_by'] : null);
            }
            self::send('membership', 'manual', (string) $p['email'], (string) $p['full_name'], $minor, (string) $p['currency'], $ref, '', (string) ($p['receipt_number'] ?? ''));
        } catch (\Throwable $e) {
            Log::warning('memberPaymentReceived hook failed: ' . $e::class);
        }
    }

    private static function closing(string $category): string
    {
        return match ($category) {
            'donations' => 'Your generosity helps us bring education, health and hope to communities in Northern Uganda. We will keep you informed about the difference you make.',
            'projects' => 'Your gift moves this project forward. We will report back to you on how it is used.',
            'membership' => 'Thank you for being part of our community. Your membership supports our work and gives you a voice in it.',
            'academy' => 'Thank you. If you have any questions about your learning, write to us any time.',
            default => 'If you have any question about this payment, simply reply to this e-mail.',
        };
    }
}
