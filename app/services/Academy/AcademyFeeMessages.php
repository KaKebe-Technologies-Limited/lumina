<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Settings;

/**
 * The wording the Academy sends or shows about fees, EDITABLE by the administrator (Admin > LAF Academy > Fees & payments).
 * Each message has a built-in default; a saved text replaces it, an empty one brings the default back. {placeholders} are filled in when it is used:
 *   cert_pay    portal message + e-mail asking learners to pay for a waiting certificate (CertificatePayReminders)
 *   signup_pay  reminder about an unpaid sign-up fee (AcademyFees::reminderText: portal message, e-mail, WhatsApp)
 *   cert_portal the notice on the learner's Certificates page next to a certificate that waits for its fee (the pay buttons and payment details follow it)
 * Unknown {words} are left as typed, so a typing mistake is visible rather than silently removed.
 */
final class AcademyFeeMessages
{
    public const MAX = 1500;

    private const PH = [
        'first_name' => 'The learner\'s first name',
        'fee' => 'The fee, for example UGX 20,000 (empty when no fee is set)',
        'courses' => 'The course(s) of the certificate(s)',
        'sign_in_link' => 'The Academy sign-in link',
        'how_to_pay' => 'A ready sentence on how to pay (online, or contact the team), chosen from what is switched on',
        'pay_details' => 'Your mobile-money and bank details (empty when none are set)',
    ];

    /** @return array<string,array{setting:string,title:string,where:string,default:string,placeholders:list<string>,rows:int}> */
    public static function all(): array
    {
        return [
            'cert_pay' => [
                'setting' => 'academy_msg_cert_pay', 'title' => 'Certificate payment reminder', 'rows' => 7,
                'where' => 'Sent as a portal message and an e-mail when you press "Remind all to pay" or "Remind to pay" on the Certificates page.',
                'default' => 'Hello {first_name}, this is the LAF Academy team. Good news: your certificate for {courses} is ready. Please pay the certificate fee ({fee}) so that we can approve and release it. {how_to_pay} {pay_details}',
                'placeholders' => ['first_name', 'courses', 'fee', 'how_to_pay', 'pay_details', 'sign_in_link'],
            ],
            'signup_pay' => [
                'setting' => 'academy_msg_signup_pay', 'title' => 'Sign-up fee reminder', 'rows' => 7,
                'where' => 'Sent to learners whose sign-up fee is not paid (the Remind buttons on Learners): portal message, e-mail and WhatsApp.',
                'default' => 'Hello {first_name}, this is the LAF Academy team. Your sign-up fee ({fee}) is not paid yet, so your account cannot be approved. {how_to_pay} After paying, sign in at {sign_in_link} and use "I have paid" to send us the transaction ID. Your account is approved automatically as soon as the payment is confirmed.',
                'placeholders' => ['first_name', 'fee', 'how_to_pay', 'pay_details', 'sign_in_link'],
            ],
            'cert_portal' => [
                'setting' => 'academy_msg_cert_portal', 'title' => 'Notice on the learner\'s Certificates page', 'rows' => 3,
                'where' => 'Shown to a learner next to a certificate that waits for its fee. Your payment details and the pay buttons appear right below it.',
                'default' => 'Your certificate is ready once the certificate fee of {fee} is paid. Choose how to pay below.',
                'placeholders' => ['fee', 'courses'],
            ],
        ];
    }

    /** @return array<string,string> placeholder => what it stands for, for one message */
    public static function placeholders(string $key): array
    {
        return array_intersect_key(self::PH, array_flip(self::all()[$key]['placeholders'] ?? []));
    }

    public static function defaultText(string $key): string
    {
        return self::all()[$key]['default'] ?? '';
    }

    public static function isCustom(string $key): bool
    {
        return trim((string) Settings::get(self::all()[$key]['setting'] ?? '', '')) !== '';
    }

    /** The text in use: the saved one, or the built-in default. */
    public static function template(string $key): string
    {
        $t = trim((string) Settings::get(self::all()[$key]['setting'] ?? '', ''));
        return $t !== '' ? $t : self::defaultText($key);
    }

    /** Fill the placeholders of the text in use. @param array<string,string> $vars */
    public static function render(string $key, array $vars): string
    {
        return self::fill(self::template($key), $vars);
    }

    /** @param array<string,string> $vars */
    public static function fill(string $text, array $vars): string
    {
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{' . $k . '}'] = $v;
        }
        $out = strtr($text, $map);
        $out = (string) preg_replace('/\s*\(\s*\)/u', '', $out);            // "fee ()" when no fee is set
        $out = (string) preg_replace('/[ \t]{2,}/u', ' ', $out);
        return trim((string) preg_replace('/ +([,.;:!?])/u', '$1', $out));
    }

    /** Example values for the preview and the tests. @return array<string,string> */
    public static function sample(): array
    {
        $fee = AcademyFees::configured('cert') ?? AcademyFees::configured('signup');
        $manual = AcademyFees::manual();
        return [
            'first_name' => 'Amina', 'fee' => $fee['display'] ?? 'UGX 20,000', 'courses' => '"Microsoft Word: Documents from Zero"', 'sign_in_link' => abs_url('academy/login'),
            'how_to_pay' => self::howToPay('cert', false), 'pay_details' => self::payDetails($manual) ?: 'Mobile money: 0772 000 000 (Lumina Africa Foundation).',
        ];
    }

    /** One sentence on how the learner can pay, from what is switched on. */
    public static function howToPay(string $kind, bool $many): string
    {
        $one = $many ? 'Each certificate is' : 'Your certificate is';
        if ($kind === 'cert') {
            return AcademyFees::cert() !== null
                ? 'Sign in at ' . abs_url('academy/login') . ', open My certificates and pay: ' . strtolower($one) . ' released as soon as the payment is confirmed.'
                : 'Reply to this message or contact the LAF Academy team to pay: ' . strtolower($one) . ' released as soon as the payment is confirmed.';
        }
        $ways = [];
        $m = AcademyFees::manual();
        if ($m !== null) {
            $ways[] = trim(($m['mobile'] !== '' ? 'mobile money: ' . preg_replace('/\s*\R\s*/', ', ', $m['mobile']) : '') . ($m['bank'] !== '' ? ($m['mobile'] !== '' ? '; ' : '') . 'bank: ' . preg_replace('/\s*\R\s*/', ', ', $m['bank']) : ''));
        }
        if (\App\Services\Pesapal\PesapalPayments::ready() || \App\Services\Daraza::enabled('signup') || AcademyFees::mobileAvailable('signup')) {
            $ways[] = 'online on the waiting page';
        }
        return $ways !== [] ? 'You can pay by ' . implode(', or ', $ways) . '.' : '';
    }

    /** @param ?array{mobile:string,bank:string} $m */
    public static function payDetails(?array $m): string
    {
        if ($m === null) {
            return '';
        }
        return trim(($m['mobile'] !== '' ? 'Mobile money: ' . preg_replace('/\s*\R\s*/', ', ', $m['mobile']) . '. ' : '') . ($m['bank'] !== '' ? 'Bank: ' . preg_replace('/\s*\R\s*/', ', ', $m['bank']) . '.' : ''));
    }

    /** @return ?string an error to show the administrator, or null when the text may be saved */
    public static function check(string $text): ?string
    {
        $t = trim($text);
        if ($t === '') {
            return null;                                                   // empty = use the built-in wording
        }
        if (mb_strlen($t) < 15) {
            return 'The message is too short. Write at least a sentence, or leave it empty to use the built-in wording.';
        }
        if (mb_strlen($t) > self::MAX) {
            return 'The message is too long (at most ' . self::MAX . ' characters).';
        }
        return null;
    }
}
