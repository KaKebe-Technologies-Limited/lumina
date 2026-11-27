<?php
declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Settings;

/**
 * The foundation's OFFICIAL BANK ACCOUNT: the one account donors and shop customers can pay into by bank transfer. It is shown on the Donate page (and after a pledge), on
 * every project gift page, on the shop order page of an unpaid order, in the pledge reminder and order e-mails, and on the gateway pages of a gift that did not go through.
 *
 * ONE SOURCE OF TRUTH. The values are the settings official_bank_* (Admin > Settings > Donations, and the "Your account details" box on Admin > Donations). Their DEFAULTS live
 * only in app/config/settings_schema.php: Settings::get() returns an explicitly saved empty string instead of a default, so value() falls back to the schema default whenever the saved
 * value is empty. That makes the account appear right after a deploy with no database import, and an administrator who saves empty boxes can never blank it.
 *
 * Visibility: the site-wide switch Admin > Payment options > "Bank and manual mobile-money details" (PaymentOptions::manual()) hides the card everywhere on screen. visible() is
 * the only place that rule is applied for the card; text() (for e-mails that a person at the foundation sends by hand) does not check it.
 *
 * The labels "Account Name", "Account Number", "Bank", "Branch" are used in the plain-text lines of e-mails: the pledge reminder tests read them.
 */
final class OfficialAccount
{
    /** field => [setting key, label]; the ORDER is the order on the card (as on the foundation's bank card). */
    public const FIELDS = [
        'name'   => ['official_bank_account_name', 'Account Name'],
        'number' => ['official_bank_account_number', 'Account Number'],
        'bank'   => ['official_bank_name', 'Bank'],
        'branch' => ['official_bank_branch', 'Branch'],
        'swift'  => ['official_bank_swift', 'SWIFT / BIC'],
    ];
    public const NOTE_KEY = 'official_bank_note';
    /** The older free-text box: now only EXTRA instructions printed under the official account. */
    public const EXTRA_KEY = 'donate_bank_details';

    /** One field: the saved value, else the built-in default (also when a saved value is empty). */
    public static function value(string $field): string
    {
        $key = self::FIELDS[$field][0] ?? '';
        return $key === '' ? '' : self::setting($key);
    }

    public static function note(): string
    {
        return self::setting(self::NOTE_KEY);
    }

    /** @return array{name:string,number:string,bank:string,branch:string,swift:string} */
    public static function get(): array
    {
        $out = [];
        foreach (self::FIELDS as $field => $_) {
            $out[$field] = self::value($field);
        }
        return $out;
    }

    /**
     * The rows of the card, only the filled ones, in card order. 'copy' is what the copy button puts on the clipboard (the account number without spaces).
     * @return list<array{field:string,label:string,value:string,copy:string}>
     */
    public static function rows(): array
    {
        $rows = [];
        foreach (self::FIELDS as $field => [$_, $label]) {
            $v = self::value($field);
            if ($v !== '') {
                $rows[] = ['field' => $field, 'label' => $label, 'value' => $v, 'copy' => $field === 'number' ? (string) preg_replace('/\s+/', '', $v) : $v];
            }
        }
        return $rows;
    }

    /** Show the account on screen? Yes while the site-wide manual-details switch is on and there is an account number. */
    public static function visible(): bool
    {
        return PaymentOptions::manual() && self::value('number') !== '';
    }

    /** "Account Name: Lumina Africa Foundation" ... one line per filled row. @return list<string> */
    public static function lines(): array
    {
        return array_map(static fn(array $r): string => $r['label'] . ': ' . $r['value'], self::rows());
    }

    /**
     * Extra free-text instructions (setting donate_bank_details). Empty when it already contains the official account number, so an administrator who typed the same account there
     * (the old box invited that) does not show it twice.
     */
    public static function extra(): string
    {
        $t = trim((string) Settings::get(self::EXTRA_KEY, ''));
        if ($t === '' || self::containsNumber($t, self::value('number'))) {
            return '';
        }
        return $t;
    }

    /** Where to send the proof of payment, in one sentence ('' when the site has no contact detail). */
    public static function proofLine(): string
    {
        $bits = [];
        $phone = trim((string) setting('phone_1', ''));
        if ($phone !== '') {
            $bits[] = $phone . ' (call or WhatsApp)';
        }
        $mail = trim((string) setting('email_primary', ''));
        if ($mail !== '') {
            $bits[] = $mail;
        }
        return $bits === [] ? '' : 'Send your proof of payment to ' . implode(' or ', $bits) . '.';
    }

    /**
     * The account as plain text for an e-mail or a free-text instruction: the filled lines, the reference to quote, the extra instructions and the note. It does NOT check visible():
     * an e-mail is sent by a person at the foundation (or follows an order the customer placed), not shown to every visitor.
     */
    public static function text(string $reference = '', bool $withExtra = true): string
    {
        $parts = [implode("\n", self::lines())];
        if ($reference !== '') {
            $parts[] = 'Payment reference: ' . $reference;
        }
        $extra = $withExtra ? self::extra() : '';
        if ($extra !== '') {
            $parts[] = $extra;
        }
        $note = self::note();
        if ($note !== '') {
            $parts[] = $note;
        }
        $proof = self::proofLine();
        if ($proof !== '') {
            $parts[] = $proof;
        }
        return implode("\n\n", array_filter($parts, static fn(string $p): bool => trim($p) !== ''));
    }

    /** Is every field still the built-in value? (the admin box says so) */
    public static function isBuiltIn(): bool
    {
        foreach (array_merge(array_column(self::FIELDS, 0), [self::NOTE_KEY]) as $key) {
            if (self::setting($key) !== trim((string) (Settings::definition($key)['default'] ?? ''))) {
                return false;
            }
        }
        return true;
    }

    /** The reference a donor writes on a bank transfer for a donation record: it matches the receipt and Admin > Payments (DON-12). */
    public static function donationReference(int $donationId): string
    {
        return 'DON-' . $donationId;
    }

    /** Does a free text already contain the account number, however it is spaced or punctuated ("4090 0080 00229", "409-0008-000229")? Digits are compared when the number has enough of them. */
    private static function containsNumber(string $text, string $number): bool
    {
        if ($number === '') {
            return false;
        }
        $digits = (string) preg_replace('/\D+/', '', $number);
        if (strlen($digits) >= 6) {
            return str_contains((string) preg_replace('/\D+/', '', $text), $digits);
        }
        return str_contains((string) preg_replace('/\s+/', '', $text), (string) preg_replace('/\s+/', '', $number));
    }

    private static function setting(string $key): string
    {
        $v = trim((string) Settings::get($key, ''));
        return $v !== '' ? $v : trim((string) (Settings::definition($key)['default'] ?? ''));
    }
}
