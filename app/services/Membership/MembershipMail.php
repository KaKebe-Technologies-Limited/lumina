<?php
declare(strict_types=1);

namespace App\Services\Membership;

use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Models\Member;
use App\Models\MemberPayment;

/**
 * Membership e-mails, built from the editable templates in Pages > Membership > E-mail templates (registry section "emails").
 * Plain text, header-injection safe (Mailer strips CR/LF), the applicant's name is reduced to a safe form because the message goes to an
 * address nobody has proven to own, and public sends are capped per address and per hour (the visitor still sees the normal success screen).
 * OWNER: architect (frozen). Nothing here logs personal data (ids / references only).
 *
 * Templates: ack (applicant after registering), staff (organisation), approved, rejected, payment.
 */
final class MembershipMail
{
    public const TEMPLATES = ['ack', 'staff', 'approved', 'rejected', 'payment'];

    /** Acknowledgement to the applicant. Returns true when handed to the mailer (or skipped by the send cap). */
    public static function applicationReceived(array $member): bool
    {
        if (!RateLimit::attempt('member-mail:' . sha1(strtolower((string) $member['email'])), 2, 86400) || !RateLimit::attempt('member-mail-all', 120, 3600)) {
            Log::warning('Membership e-mail skipped (send cap reached)', ['member' => $member['id'] ?? null]);
            return true;
        }
        return self::send('ack', $member);
    }

    /** Notification to the organisation (setting notify_email). Reply-to = the applicant. */
    public static function staffNotice(array $member): bool
    {
        try {
            [$subject, $body] = self::render('staff', $member);
            return Mailer::notify($subject, $body, (string) $member['email']);
        } catch (\Throwable $e) {
            Log::error('Membership staff notification failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function approved(array $member): bool
    {
        return self::send('approved', $member);
    }

    public static function rejected(array $member): bool
    {
        return self::send('rejected', $member);
    }

    /** E-mail confirming a received payment (uses the receipt number of the payment row). */
    public static function paymentReceived(array $member, array $payment): bool
    {
        return self::send('payment', $member, ['payment' => $payment]);
    }

    /**
     * Send one template to the member's address (staff "resend e-mail" uses this). $extra may carry 'payment' (row) for the payment template.
     * Never throws; returns false when the mailer refuses.
     */
    public static function send(string $template, array $member, array $extra = []): bool
    {
        if (!in_array($template, self::TEMPLATES, true) || $template === 'staff') {
            return false;
        }
        try {
            [$subject, $body] = self::render($template, $member, $extra);
            $contact = MembershipService::contactEmail();
            return Mailer::send((string) $member['email'], $subject, $body, $contact !== '' ? ['reply_to' => $contact] : []);
        } catch (\Throwable $e) {
            Log::error('Membership e-mail failed: ' . $e->getMessage(), ['template' => $template, 'member' => $member['id'] ?? null]);
            return false;
        }
    }

    /** @return array{0:string,1:string} [subject, body] with every placeholder filled in. */
    public static function render(string $template, array $member, array $extra = []): array
    {
        $vars = self::vars($member, $extra);
        $subject = strtr(MembershipService::copy('emails.' . $template . '_subject'), $vars);
        $body = strtr(MembershipService::copy('emails.' . $template . '_body'), $vars);
        return [trim((string) preg_replace('/\s+/', ' ', $subject)), trim((string) preg_replace("/\n{3,}/", "\n\n", $body))];
    }

    /** @return array<string,string> placeholder => value */
    public static function vars(array $member, array $extra = []): array
    {
        $payment = (array) ($extra['payment'] ?? []);
        $instructions = MembershipService::paymentInstructions();
        $name = (string) ($member['full_name'] ?? '');
        $first = self::safeName($name);
        $contact = MembershipService::contactEmail();
        $joining = MembershipService::joiningFeeLabel($member);
        $fee = MembershipService::memberFeeLabel($member);
        $amount = isset($payment['amount']) ? MembershipService::formatMoney($payment['amount'], (string) ($payment['currency'] ?? $member['fee_currency'])) : '';
        $methodList = MembershipService::options('payment_methods');
        $method = (string) ($payment['method'] ?? '');
        return [
            '{name}'                 => self::safeName($name, true),
            '{first_name}'           => $first,
            '{site}'                 => (string) setting('site_name', ''),
            '{ref}'                  => (string) ($member['application_ref'] ?? ''),
            '{type}'                 => (string) ($member['type_name'] ?? ''),
            '{fee}'                  => $fee . ($joining !== '' ? ' (' . $joining . ')' : ''),
            '{joining_fee}'          => $joining,
            '{payment_instructions}' => $instructions !== '' ? $instructions : MembershipService::copy('confirmation.payment_none'),
            '{contact_email}'        => $contact,
            '{url}'                  => abs_url('admin/members/' . (int) ($member['id'] ?? 0)),
            '{status_url}'           => isset($member['application_ref']) ? MembershipService::confirmationUrl((string) $member['application_ref']) : '',
            '{membership_number}'    => (string) ($member['membership_number'] ?? ''),
            '{start_date}'           => !empty($member['start_date']) ? format_date($member['start_date']) : '',
            '{expiry_date}'          => !empty($member['expiry_date']) ? format_date($member['expiry_date']) : MembershipService::copy('emails.no_expiry', 'No expiry'),
            '{renewal_due}'          => !empty($member['expiry_date']) ? format_date($member['expiry_date']) : MembershipService::copy('emails.no_renewal', 'None, this membership does not expire'),
            '{receipt}'              => (string) ($payment['receipt_number'] ?? ''),
            '{amount}'               => $amount,
            '{method}'               => $method !== '' ? ($methodList[$method] ?? label_from($method)) : '',
        ];
    }

    /**
     * A name that is safe inside a message sent to a third party's inbox: letters, digits and a few punctuation marks only (no links, no
     * line breaks), shortened. $full = whole name, otherwise the first word only. Empty -> the editable fallback word.
     */
    private static function safeName(string $name, bool $full = false): string
    {
        $n = (string) preg_replace('~(?:https?://|www\.)\S*~iu', ' ', $name);
        $n = (string) preg_replace('/[^\p{L}\p{M}\p{N} \'.-]+/u', ' ', $n);
        $n = trim((string) preg_replace('/\s+/u', ' ', $n));
        if (!$full) {
            $n = explode(' ', $n)[0] ?? '';
        }
        $n = mb_substr($n, 0, 40);
        if (preg_match('/\p{L}/u', $n) !== 1) {
            return MembershipService::copy('emails.name_fallback', 'there');
        }
        return $n;
    }
}
