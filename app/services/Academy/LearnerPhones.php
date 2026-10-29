<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Services\Web\Countries;

/**
 * Phone and WhatsApp numbers of learners, with the country code of the learner's OWN country.
 *
 *   international()  what a person typed + the dialling code of the country they chose -> digits with the country code ("0772 123 456" in Uganda -> 256772123456)
 *   repair()         one-time clean-up of older accounts: numbers saved before the sign-up knew the country get the code of the country on the account
 *
 * Rules (the same everywhere): "+..." and "00..." are already international; a number starting with 0 loses the 0 and takes the country code; a number that already starts with
 * the country code and is long enough is kept; any other digits get the country code in front. Unknown country: the site default (WHATSAPP_COUNTRY_CODE, else 256).
 */
final class LearnerPhones
{
    /** @return string digits with the country code (8 to 15 digits), or null when it cannot be a phone number */
    public static function international(string $input, ?string $dial = null): ?string
    {
        $raw = trim($input);
        $d = (string) preg_replace('/\D+/', '', $raw);
        if ($d === '') {
            return null;
        }
        if (str_starts_with($raw, '+')) {
            return self::fit($d);
        }
        if (str_starts_with($d, '00')) {
            return self::fit(substr($d, 2));
        }
        $dial = $dial !== null && $dial !== '' ? $dial : self::fallbackDial();
        if (str_starts_with($d, '0')) {
            return self::fit($dial . ltrim($d, '0'));
        }
        if (str_starts_with($d, $dial) && strlen($d) >= strlen($dial) + 7) {
            return self::fit($d);
        }
        return self::fit($dial . $d);
    }

    /**
     * A number as the REPORTS show it: with the country code of the learner's country ("+256772123456"), whatever way it was saved. Numbers that already start with + are
     * shown as saved. With an unknown country and no + the number is shown as it is: a report never guesses a country.
     */
    public static function display(?string $phone, ?string $country): string
    {
        $p = trim((string) $phone);
        if ($p === '') {
            return '';
        }
        if (str_starts_with($p, '+')) {
            return $p;
        }
        $dial = trim((string) $country) === '' ? null : Countries::dial((string) $country);
        if ($dial === null && !str_starts_with((string) preg_replace('/\D+/', '', $p), '00')) {
            return $p;
        }
        $intl = self::international($p, $dial);
        return $intl === null ? $p : '+' . $intl;
    }

    /** "+256" for a country, '' when we do not know it. */
    public static function dialCode(?string $country): string
    {
        $d = trim((string) $country) === '' ? null : Countries::dial((string) $country);
        return $d === null ? '' : '+' . $d;
    }

    /** The code used when the learner's country is unknown: WHATSAPP_COUNTRY_CODE from .env, else 256 (Uganda). */
    public static function fallbackDial(): string
    {
        $cc = (string) preg_replace('/\D+/', '', (string) \App\Core\Env::get('WHATSAPP_COUNTRY_CODE', ''));
        return $cc !== '' ? $cc : '256';
    }

    private static function fit(string $digits): ?string
    {
        return strlen($digits) >= 8 && strlen($digits) <= 15 ? $digits : null;
    }

    /**
     * Give older accounts the country code of their own country.
     *   phone:    "0803 123 456" (Nigeria) -> "+234803123456"; numbers that already start with + are left alone
     *   whatsapp: only the mistake the old sign-up made: a local number that was given +256 whatever the country. It is put right ONLY when the account's own phone
     *             shows the same local number (so a Ugandan living abroad who really has a +256 WhatsApp number is never changed).
     * Safe to run again: a number that already has its code is never touched twice. $emailLike limits it to some accounts (the tests use it).
     * @return array{checked:int,phones:int,whatsapp:int,unknown_country:array<string,int>,examples:list<array{string,string}>}
     */
    public static function repair(bool $apply, string $emailLike = '%'): array
    {
        $rows = Database::all('SELECT id, phone, whatsapp, country FROM academy_learners WHERE email LIKE ? ORDER BY id', [$emailLike]);
        $out = ['checked' => count($rows), 'phones' => 0, 'whatsapp' => 0, 'unknown_country' => [], 'examples' => []];
        foreach ($rows as $r) {
            $country = trim((string) ($r['country'] ?? ''));
            $dial = $country === '' ? null : Countries::dial($country);
            if ($dial === null) {
                if ($country !== '') {
                    $out['unknown_country'][$country] = ($out['unknown_country'][$country] ?? 0) + 1;
                }
                continue;
            }
            $set = [];
            $phone = trim((string) ($r['phone'] ?? ''));
            $phoneDigits = (string) preg_replace('/\D+/', '', $phone);
            if ($phone !== '' && !str_starts_with($phone, '+')) {
                $intl = self::international($phone, $dial);
                if ($intl !== null && '+' . $intl !== $phone) {
                    $set['phone'] = '+' . $intl;
                }
            }
            $wa = (string) preg_replace('/\D+/', '', (string) ($r['whatsapp'] ?? ''));
            if ($wa !== '' && $dial !== '256' && str_starts_with($wa, '256') && str_starts_with($phoneDigits, '0') && !str_starts_with($phone, '+')
                && $wa === '256' . ltrim($phoneDigits, '0')) {
                $fixed = self::fit($dial . ltrim($phoneDigits, '0'));
                if ($fixed !== null) {
                    $set['whatsapp'] = $fixed;
                }
            }
            if ($set === []) {
                continue;
            }
            if (isset($set['phone'])) {
                $out['phones']++;
            }
            if (isset($set['whatsapp'])) {
                $out['whatsapp']++;
            }
            if (count($out['examples']) < 6) {
                $out['examples'][] = [$country . ': ' . $phone . ($wa !== '' ? ' / ' . $wa : ''), implode(' / ', array_values($set))];
            }
            if ($apply) {
                Database::update('academy_learners', $set, 'id = ?', [(int) $r['id']]);
            }
        }
        ksort($out['unknown_country']);
        return $out;
    }
}
