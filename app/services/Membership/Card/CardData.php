<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipService;

/**
 * Turns a member (or the fictional sample) plus the card settings into the plain DATA ARRAY that CardLayout draws. Everything that decides what
 * the card SAYS is here (status, valid-until wording, fee and period text, payment wording, initials, location line, footer lines, tile choice);
 * CardLayout only decides WHERE it goes. Contract and key list: docs/MEMBERSHIP_CARD.md.
 *
 *   $raw  = CardData::raw($memberRow, ['verify_token' => $t, 'valid_until' => '2027-10-19', 'issued' => '2026-10-20'])   // member row -> neutral input
 *   $data = CardData::build($raw, CardSettings::resolve())                                                                 // pure: no database
 *   $data = CardData::sample(CardSettings::resolve($unsavedForm))                                                          // the watermarked fictional card
 *
 * build() is a pure function of its two arguments, so tests (and the designer preview) can feed it anything. Every string it returns is already
 * cleaned of control characters and cut to a sane length; CardLayout still fits it to the available width.
 */
final class CardData
{
    public const SAMPLE_TEXT = "SAMPLE CARD \u{2013} fictional member, not valid for verification";
    /** Token shown in the QR code of the sample card: it can never exist in the database, so the verification page says NOT VALID. */
    public const SAMPLE_TOKEN = 'samplecardnotvalid000000000000';

    /** The words below are the DEFAULTS of the designer settings membership_card_fee_* / _period_* / _adverb_* (CardSettings::DEFS): the card reads the settings, these are only the fallback. */
    private const PERIOD_ADVERB = ['annual' => 'annually', 'monthly' => 'monthly', 'quarterly' => 'quarterly'];
    private const PERIOD_LABEL = ['annual' => 'ANNUAL', 'monthly' => 'MONTHLY', 'quarterly' => 'QUARTERLY', 'lifetime' => 'LIFETIME', 'one_time' => 'ONE-TIME'];
    /** period => settings key of the word after the fee amount ("UGX 50,000 / year"). */
    private const FEE_SUFFIX = ['annual' => 'membership_card_fee_per_year', 'monthly' => 'membership_card_fee_per_month', 'quarterly' => 'membership_card_fee_per_quarter',
        'lifetime' => 'membership_card_fee_for_life', 'one_time' => 'membership_card_fee_one_off'];
    /** Status key => [settings key of its wording, colour key]. */
    private const STATUS = [
        'active'    => ['membership_card_status_active', 'green'],
        'expired'   => ['membership_card_status_expired', 'expired'],
        'suspended' => ['membership_card_status_suspended', 'red'],
        'revoked'   => ['membership_card_status_revoked', 'red'],
    ];
    /** Colours of the statuses that are not the designer's green. */
    public const STATUS_COLORS = ['expired' => '#6B7280', 'red' => '#C0392B'];

    // ------------------------------------------------------------------------------------------------ sources

    /**
     * The neutral input of build() for a real member row.
     * $ctx: verify_token (the card's token; without it the QR is empty), valid_until (Y-m-d|null; default = the member's expiry), issued (Y-m-d; default today),
     * status (force a status key), card_status (members.card_status: 'revoked' makes the card read NOT VALID), photo (false = leave the photo out).
     * @param array<string,mixed> $member @param array<string,mixed> $ctx @return array<string,mixed>
     */
    public static function raw(array $member, array $ctx = []): array
    {
        $summary = MembershipService::paymentSummary($member);
        $payState = match ($summary['status']) {
            'paid' => $summary['waived'] > 0 && $summary['paid'] <= 0 ? 'waived' : 'paid',
            'free', 'tbd' => $summary['status'],
            'partial' => 'partial',
            default => 'pending',
        };
        $photo = ($ctx['photo'] ?? true) === false ? null : MemberFiles::photoForCard($member);
        return [
            'name'         => (string) ($member['full_name'] ?? ''),
            'type_name'    => (string) ($member['type_name'] ?? ''),
            'number'       => (string) ($member['membership_number'] ?? ''),
            'status'       => (string) ($ctx['status'] ?? self::state($member, (string) ($ctx['card_status'] ?? $member['card_status'] ?? 'none'))),
            'since'        => !empty($member['start_date']) ? (string) $member['start_date'] : null,
            // the LAST valid day (the day before the expiry date, like the reference: 20 Oct 2026 + 1 year = valid until 19 Oct 2027); $ctx['valid_until'] is an expiry DATE
            'valid_until'  => MembershipService::lastValidDay(array_key_exists('valid_until', $ctx) ? ($ctx['valid_until'] === null ? null : (string) $ctx['valid_until']) : (!empty($member['expiry_date']) ? (string) $member['expiry_date'] : null)),
            'issued'       => (string) ($ctx['issued'] ?? date('Y-m-d')),
            'period'       => (string) ($member['fee_period'] ?? 'annual'),
            'fee_amount'   => $member['fee_amount'] ?? null,
            'joining_fee'  => $member['joining_fee'] ?? null,
            'currency'     => (string) ($member['fee_currency'] ?? 'UGX'),
            'pay_state'    => $payState,
            'city'         => (string) ($member['city'] ?? ''),
            'district'     => (string) ($member['region'] ?? ''),
            'country'      => (string) ($member['country'] ?? ''),
            'photo'        => $photo,
            'verify_url'   => isset($ctx['verify_token']) && $ctx['verify_token'] !== '' ? abs_url('membership/verify/' . $ctx['verify_token']) : '',
            'sample'       => false,
        ];
    }

    /**
     * What the issued card SAYS, to keep with the member (members.card_snapshot): the neutral input of build() without the picture, the live status and the
     * link. A card is a snapshot: a later edit of the name, photo or location needs a re-issue to show up on the card (docs/MEMBERSHIP_CARD.md).
     * @param array<string,mixed> $raw @return array<string,mixed>
     */
    public static function snapshotOf(array $raw): array
    {
        unset($raw['photo'], $raw['status'], $raw['verify_url'], $raw['sample']);
        return $raw;
    }

    /**
     * The neutral input of build() for an ISSUED card: the snapshot taken at issue, the card's own photo copy, and the LIVE status (active / expired / suspended /
     * revoked) and verification link. Null when the member has no snapshot (a card issued before snapshots existed): callers fall back to raw().
     * @param array<string,mixed> $member @return array<string,mixed>|null
     */
    public static function rawFromSnapshot(array $member): ?array
    {
        $snap = json_decode((string) ($member['card_snapshot'] ?? ''), true);
        if (!is_array($snap) || $snap === []) {
            return null;
        }
        $photo = !empty($member['card_photo_path']) ? MemberFiles::photoForCard(['photo_path' => $member['card_photo_path']]) : null;
        $token = (string) ($member['card_verify_token'] ?? '');
        return $snap + [
            'status'     => self::state($member, (string) ($member['card_status'] ?? 'none')),
            'verify_url' => $token !== '' ? abs_url('membership/verify/' . $token) : '',
            'photo'      => $photo,
            'sample'     => false,
        ];
    }

    /** The fictional sample: Amina Akello, LAF-IND-26-0001, the figures of the owner's reference. */
    public static function sampleRaw(): array
    {
        return [
            'name' => 'Amina Akello', 'type_name' => 'Individual Member', 'number' => 'LAF-IND-26-0001', 'status' => 'active',
            'since' => '2026-10-20', 'valid_until' => '2027-10-19', 'issued' => '2026-10-20', 'period' => 'annual',
            'fee_amount' => '50000.00', 'joining_fee' => '50000.00', 'currency' => 'UGX', 'pay_state' => 'paid',
            'city' => 'Gulu City', 'district' => 'Gulu', 'country' => 'Uganda', 'photo' => null,
            'verify_url' => abs_url('membership/verify/' . self::SAMPLE_TOKEN), 'sample' => true,
        ];
    }

    /** @param array<string,string> $settings resolved card settings @return array<string,mixed> */
    public static function sample(array $settings): array
    {
        return self::build(self::sampleRaw(), $settings);
    }

    /**
     * The live state of a card: active | expired | suspended | revoked, from the member row (and the card status). Used for the PDF pill and by
     * the verification page, so they can never disagree.
     * @param array<string,mixed> $member
     */
    public static function state(array $member, string $cardStatus = 'none'): string
    {
        $status = (string) ($member['status'] ?? '');
        if ($cardStatus === 'revoked' || !empty($member['anonymised_at']) || in_array($status, ['pending', 'rejected', 'cancelled'], true) || $status === '') {
            return 'revoked';
        }
        if ($status === 'suspended') {
            return 'suspended';
        }
        $expiry = (string) ($member['expiry_date'] ?? '');
        if ($status === 'expired' || ($expiry !== '' && $expiry <= date('Y-m-d'))) {
            return 'expired';
        }
        return 'active';
    }

    // ------------------------------------------------------------------------------------------------ the pure builder

    /**
     * @param array<string,mixed> $raw see raw() @param array<string,string> $s resolved settings (CardSettings::resolve())
     * @return array<string,mixed>
     */
    public static function build(array $raw, array $s): array
    {
        $line = static fn(mixed $v, int $max = 200): string => CardSettings::cleanLine(is_scalar($v) ? (string) $v : '', $max);
        $colors = CardSettings::colors($s);
        $status = isset(self::STATUS[(string) ($raw['status'] ?? '')]) ? (string) $raw['status'] : 'revoked';
        [$statusKey, $statusColorKey] = self::STATUS[$status];
        $statusColor = $statusColorKey === 'green' ? $colors['green'] : self::STATUS_COLORS[$statusColorKey];
        $period = in_array($raw['period'] ?? '', ['annual', 'monthly', 'quarterly', 'lifetime', 'one_time'], true) ? (string) $raw['period'] : 'annual';
        $currency = strtoupper($line($raw['currency'] ?? 'UGX', 3)) ?: 'UGX';
        $name = $line($raw['name'] ?? '', 150);
        $valid = self::date($raw['valid_until'] ?? null);
        $expires = $valid !== '' && isset(self::PERIOD_ADVERB[$period]);
        $district = $line($raw['district'] ?? '', 120);
        $city = $line($raw['city'] ?? '', 120);
        $country = $line($raw['country'] ?? '', 80);

        // ---- support contact: the card's own setting, else the Site settings
        $phone = $line($s['membership_card_support_phone'] ?? '', 30);
        $email = $line($s['membership_card_support_email'] ?? '', 190);
        if ($phone === '') {
            $phone = self::localPhone(self::siteSetting('phone_1'));
        }
        if ($email === '') {
            $email = $line(self::siteSetting('email_info') ?: self::siteSetting('email_primary'), 190);
        }

        // ---- values reused by tiles and footer
        $since = self::date($raw['since'] ?? null);
        $feeValue = self::feeValue($raw['fee_amount'] ?? null, $currency, $period, $s);
        $payValue = match ((string) ($raw['pay_state'] ?? 'pending')) {
            'paid' => $s['membership_card_pay_paid'] ?? 'Confirmed',
            'waived' => $s['membership_card_pay_waived'] ?? 'Waived',
            'free' => $s['membership_card_pay_free'] ?? 'No fee',
            'partial' => $s['membership_card_pay_partial'] ?? 'Part paid',
            'tbd' => $s['membership_card_pay_tbd'] ?? 'Fee not set',
            default => $s['membership_card_pay_pending'] ?? 'Pending',
        };
        $joiningAmount = $raw['joining_fee'] ?? null;
        $joining = '';
        if ($joiningAmount !== null && $joiningAmount !== '' && (float) $joiningAmount > 0) {
            // a waived fee must not read "paid": the card is shown to third parties
            $joinKey = match ((string) ($raw['pay_state'] ?? '')) {
                'paid' => 'membership_card_joining_paid', 'waived' => 'membership_card_joining_waived', default => 'membership_card_joining_due',
            };
            $joinDefault = ['membership_card_joining_paid' => 'Joining fee {amount} paid', 'membership_card_joining_waived' => 'Joining fee {amount} waived', 'membership_card_joining_due' => 'Joining fee {amount} due'][$joinKey];
            $joining = $line(str_replace('{amount}', MembershipService::formatMoney($joiningAmount, $currency), (string) ($s[$joinKey] ?? $joinDefault)), 80);
        }
        $issued = self::date($raw['issued'] ?? null) ?: date('j M Y');
        $vars = [
            '{joining}' => $joining, '{issued}' => $issued, '{phone}' => $phone, '{email}' => $email, '{number}' => $line($raw['number'] ?? '', 40),
            '{name}' => $name, '{since}' => $since, '{valid_until}' => $valid,
        ];

        // ---- tiles
        $tileValue = [
            'member_since'  => [$s['membership_card_label_member_since'] ?? 'MEMBER SINCE', $since !== '' ? $since : "\u{2013}"],
            'payment'       => [$s['membership_card_label_payment'] ?? 'PAYMENT', (string) $payValue],
            'fee'           => [strtr((string) ($s['membership_card_label_fee'] ?? '{period} MEMBERSHIP'), ['{period}' => $line($s['membership_card_period_' . $period] ?? self::PERIOD_LABEL[$period], 16)]), $feeValue],
            'valid_until'   => [$s['membership_card_label_valid_until'] ?? 'VALID UNTIL', $valid !== '' ? $valid : ($s['membership_card_no_expiry_text'] ?? 'No expiry')],
            'member_number' => [$s['membership_card_label_number'] ?? 'MEMBER NUMBER', $line($raw['number'] ?? '', 40)],
            'type'          => [$s['membership_card_label_type'] ?? 'MEMBERSHIP TYPE', $line($raw['type_name'] ?? '', 80)],
            'district'      => [$s['membership_card_label_district'] ?? 'DISTRICT', $district !== '' ? $district : $city],
        ];
        $tiles = [];
        foreach (['membership_card_tile_1', 'membership_card_tile_2', 'membership_card_tile_3'] as $key) {
            $choice = (string) ($s[$key] ?? 'none');
            if (isset($tileValue[$choice])) {
                $tiles[] = ['key' => $choice, 'label' => $line($tileValue[$choice][0], 40), 'value' => $line($tileValue[$choice][1], 80)];
            }
        }

        // ---- footer lines and panel text
        $footer = [];
        foreach (['membership_card_footer_1', 'membership_card_footer_2', 'membership_card_footer_3'] as $key) {
            $t = self::template((string) ($s[$key] ?? ''), $vars);
            if ($t !== '') {
                $footer[] = $t;
            }
        }
        $panel = array_values(array_filter(array_map(static fn(string $l): string => CardSettings::cleanLine($l, 80), explode("\n", (string) ($s['membership_card_panel_text'] ?? ''))), static fn(string $l): bool => $l !== ''));

        return [
            'org'          => $line($s['membership_card_org_name'] ?? '', 60),
            'tagline'      => $line($s['membership_card_tagline'] ?? '', 70),
            'label'        => $line($s['membership_card_label'] ?? '', 30),
            'type'         => mb_strtoupper($line($raw['type_name'] ?? '', 80)),
            'name'         => $name,
            'initials'     => self::initials($name),
            'name_label'   => $line($s['membership_card_label_member'] ?? '', 24),
            'from_label'   => $line($s['membership_card_label_from'] ?? '', 24),
            'location'     => self::location((string) ($s['membership_card_location_format'] ?? '{city}, {country}'), $city, $district, $country),
            'number'       => $line($raw['number'] ?? '', 40),
            'number_label' => $line($s['membership_card_label_number'] ?? '', 30),
            'status'       => $status,
            'status_label' => $line($s[$statusKey] ?? '', 24),
            'status_color' => $statusColor,
            'valid_label'  => $line($s['membership_card_label_valid_until'] ?? '', 30),
            'valid_text'   => $valid !== '' ? $valid : $line($s['membership_card_no_expiry_text'] ?? 'No expiry', 30),
            'renews_text'  => $expires ? self::template((string) ($s['membership_card_renews_text'] ?? ''), ['{period}' => $line($s['membership_card_adverb_' . $period] ?? self::PERIOD_ADVERB[$period], 16)]) : '',
            'scan_text'    => $line($s['membership_card_scan_text'] ?? '', 40),
            'tiles'        => $tiles,
            'footer'       => $footer,
            'panel'        => array_slice($panel, 0, 2),
            'sample'       => !empty($raw['sample']),
            'sample_text'  => self::SAMPLE_TEXT,
            'show_photo'   => ($s['membership_card_show_photo'] ?? '1') === '1',
            'show_qr'      => ($s['membership_card_show_qr'] ?? '1') === '1' && is_string($raw['verify_url'] ?? null) && ($raw['verify_url'] ?? '') !== '',
            'photo'        => is_array($raw['photo'] ?? null) ? $raw['photo'] : null,
            'verify_url'   => is_string($raw['verify_url'] ?? null) ? (string) $raw['verify_url'] : '',
            'colors'       => $colors,
            'title'        => 'Membership card ' . $line($raw['number'] ?? '', 40),
        ];
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** "20 Oct 2026" from Y-m-d; '' for null / invalid. */
    public static function date(mixed $ymd): string
    {
        if (!is_string($ymd) || preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $ymd, $m) !== 1 || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return '';
        }
        return (int) $m[3] . ' ' . ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][(int) $m[2] - 1] . ' ' . $m[1];
    }

    /**
     * Does the card have to print "?" for something in this text (letters the core fonts cannot draw: Arabic, Cyrillic, Chinese, emoji ...)? The name on the
     * verification page, in the e-mail and on the private page is the real one, so staff are told when the printed card differs. $text = name or place.
     */
    public static function hasUnprintable(string $text): bool
    {
        $clean = CardSettings::cleanLine($text, 200);
        return $clean !== '' && substr_count(PdfWriter::toWinAnsi($clean), '?') > substr_count($clean, '?');
    }

    /**
     * One or two capital letters for the photo placeholder: first letter of the first and the last word ("Amina Akello" -> "AA"), the first two letters of a single
     * word. For the CARD ($printableOnly) only words it can print are used ("王小明 Wang" -> "WA"); a name with none gives "M" (for member) and not a row of question
     * marks. Web pages pass false: a browser can show any letters.
     */
    public static function initials(string $name, bool $printableOnly = true): string
    {
        $words = array_values(array_filter(preg_split('/[\s\-]+/u', $name) ?: [], static fn(string $w): bool => preg_match('/\p{L}/u', $w) === 1 && (!$printableOnly || !self::hasUnprintable($w))));
        if ($words === []) {
            return preg_match('/\p{L}/u', $name) === 1 ? 'M' : '?';
        }
        $first = static function (string $w): string {
            $w = (string) preg_replace('/^[^\p{L}]+/u', '', $w);
            return mb_strtoupper(mb_substr($w, 0, 1));
        };
        if (count($words) === 1) {
            $w = (string) preg_replace('/[^\p{L}]/u', '', $words[0]);
            return mb_strtoupper(mb_substr($w, 0, 2));
        }
        return $first($words[0]) . $first($words[count($words) - 1]);
    }

    /** "Gulu City, Uganda" from a format with {city} {district} {country}; empty pieces and their commas disappear. */
    public static function location(string $format, string $city, string $district, string $country): string
    {
        $s = strtr($format, ['{city}' => $city, '{district}' => $district, '{country}' => $country]);
        $s = (string) preg_replace('/\{[a-z_]+\}/', '', $s);
        $s = (string) preg_replace('/(\s*,\s*){2,}/', ', ', $s);
        return CardSettings::cleanLine(trim($s, " ,"), 120);
    }

    /**
     * "UGX 50,000 / year", "UGX 50,000 (one-off)", "Free" or "To be announced". The words come from the designer settings ($s = resolved settings; without
     * them the built-in defaults, which are the same words).
     * @param array<string,string> $s
     */
    public static function feeValue(mixed $amount, string $currency, string $period, array $s = []): string
    {
        $word = static fn(string $key, string $fallback): string => CardSettings::cleanLine((string) ($s[$key] ?? $fallback), 24);
        if ($amount === null || $amount === '') {
            return $word('membership_card_fee_tbd', 'To be announced');
        }
        if ((float) $amount <= 0) {
            return $word('membership_card_fee_free', 'Free');
        }
        $money = MembershipService::formatMoney($amount, $currency);
        $key = self::FEE_SUFFIX[$period] ?? 'membership_card_fee_one_off';
        $suffix = $word($key, ['membership_card_fee_per_year' => '/ year', 'membership_card_fee_per_month' => '/ month', 'membership_card_fee_per_quarter' => '/ quarter',
            'membership_card_fee_for_life' => '(for life)', 'membership_card_fee_one_off' => '(one-off)'][$key]);
        return $suffix === '' ? $money : $money . ' ' . $suffix;
    }

    /**
     * Fill {placeholders} (lower case) from $vars, drop unknown ones, then tidy the " · " separators so a missing piece leaves no dangling dot
     * ("{joining} · Issued {issued}" with no joining fee reads "Issued 20 Oct 2026").
     * @param array<string,string> $vars
     */
    public static function template(string $text, array $vars): string
    {
        $s = strtr($text, $vars);
        $s = (string) preg_replace('/\{[a-z_]+\}/', '', $s);
        $parts = array_values(array_filter(array_map(static fn(string $p): string => trim($p), explode("\u{00B7}", $s)), static fn(string $p): bool => $p !== ''));
        return CardSettings::cleanLine(implode(' ' . "\u{00B7}" . ' ', $parts), 200);
    }

    /** "+256-779-712-990" -> "0779 712 990" (Uganda numbers written the local way); anything else keeps its digits with spaces for dashes. */
    public static function localPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (preg_match('/^256(\d{9})$/', $digits, $m) === 1) {
            return '0' . substr($m[1], 0, 3) . ' ' . substr($m[1], 3, 3) . ' ' . substr($m[1], 6, 3);
        }
        return CardSettings::cleanLine(str_replace('-', ' ', $phone), 30);
    }

    private static function siteSetting(string $key): string
    {
        try {
            return trim((string) setting($key, ''));
        } catch (\Throwable) {
            return '';
        }
    }
}
