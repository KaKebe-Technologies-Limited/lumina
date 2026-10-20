<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

use App\Core\AuditLog;
use App\Core\Settings;

/**
 * Typed access, defaults, validation and saving of the MEMBERSHIP CARD settings (settings table, group "Membership card", keys `membership_card_*`
 * plus `membership_photo_required`, `membership_proof_retention_days`). The definitions below are the single source of truth for the defaults:
 * database/seed.sql seeds exactly these keys (a selftest compares them) and the defaults REPRODUCE THE OWNER'S REFERENCE CARD
 * (docs/design-reference/membership-card-reference.pdf). Like the shop settings they are deliberately NOT in config/settings_schema.php: the generic
 * Site settings screen never shows them, the only editor is Admin > Membership > Membership card (the card designer).
 *
 *   CardSettings::get('membership_card_tagline')      string (stored value, else the default)
 *   CardSettings::bool('membership_card_show_qr')     bool
 *   CardSettings::resolve(['membership_card_navy' => '#112233'])   every key resolved, with $override applied on top (live preview of unsaved edits)
 *   $r = CardSettings::sanitise($_POST)               ['values' => clean strings, 'errors' => [key => message]]   (hostile input is cut down, never trusted)
 *   $r = CardSettings::save($_POST, $userId)          validates, stores only what differs from the default, audits key NAMES only
 *   CardSettings::warnings($values)                   contrast warnings (list of ['key','message','ratio'])
 *
 * Types: str:N (one line, N characters), text:N (several lines), color (#rrggbb), bool, enum:a,b,c, int:min:max, image (path below uploads/), format (member number
 * format), email, phone.
 */
final class CardSettings
{
    /** The tile contents an admin can choose for each of the three tiles. */
    public const TILE_OPTIONS = [
        'member_since' => 'Member since',
        'payment'      => 'Payment status',
        'fee'          => 'Fee and period',
        'valid_until'  => 'Valid until',
        'member_number' => 'Member number',
        'type'         => 'Membership type',
        'district'     => 'District',
        'none'         => 'Leave this tile out',
    ];
    public const TRIGGERS = ['paid_approved' => 'When approved AND the balance is cleared (paid or waived), whichever happens last', 'approved' => 'As soon as the member is approved', 'manual' => 'Never automatically: staff issue each card'];
    /** The member-number placeholders; {SEQ} may carry a width: {SEQ:5}. */
    public const NUMBER_PLACEHOLDERS = ['{PREFIX}', '{TYPE}', '{YY}', '{YYYY}', '{SEQ}'];
    public const DEFAULT_NUMBER_FORMAT = '{PREFIX}-{TYPE}-{YY}-{SEQ}';
    public const NUMBER_MAX_LENGTH = 40;

    /** key => [default, type, label]. Order = order on the designer screen. */
    public const DEFS = [
        // ---- words on the card
        'membership_card_org_name'     => ['LUMINA AFRICA FOUNDATION', 'str:60', 'Organisation name line'],
        'membership_card_tagline'      => ['Igniting Light. Inspiring Change.', 'str:70', 'Tagline'],
        'membership_card_label'        => ['MEMBERSHIP CARD', 'str:30', 'Card label (top right)'],
        'membership_card_logo'         => ['', 'image', 'Logo (empty = the bundled Lumina seal)'],
        // ---- colours
        'membership_card_navy'         => ['#0F1B33', 'color', 'Side panel colour (navy)'],
        'membership_card_accent'       => ['#E0642C', 'color', 'Accent colour (orange)'],
        'membership_card_gold'         => ['#C9973F', 'color', 'Tagline colour (gold)'],
        'membership_card_green'        => ['#22A35A', 'color', 'Active status colour (green)'],
        'membership_card_tile'         => ['#F4F6FA', 'color', 'Info tile background'],
        'membership_card_label_color'  => ['#6A7389', 'color', 'Small label colour (grey)'],
        'membership_card_muted'        => ['#B9C2DA', 'color', 'Small text on the side panel'],
        // ---- which blocks to show
        'membership_card_show_photo'   => ['1', 'bool', 'Show the photo block'],
        'membership_card_show_qr'      => ['1', 'bool', 'Show the QR code (verification link)'],
        'membership_card_tile_1'       => ['member_since', 'enum:' . 'member_since,payment,fee,valid_until,member_number,type,district,none', 'Tile 1'],
        'membership_card_tile_2'       => ['payment', 'enum:' . 'member_since,payment,fee,valid_until,member_number,type,district,none', 'Tile 2'],
        'membership_card_tile_3'       => ['fee', 'enum:' . 'member_since,payment,fee,valid_until,member_number,type,district,none', 'Tile 3'],
        // ---- labels
        'membership_card_label_member'        => ['MEMBER', 'str:24', 'Label above the name'],
        'membership_card_label_from'          => ['FROM', 'str:24', 'Label above the location'],
        'membership_card_label_number'        => ['MEMBER NUMBER', 'str:30', 'Label above the member number'],
        'membership_card_label_valid_until'   => ['VALID UNTIL', 'str:30', 'Label above the expiry date'],
        'membership_card_label_member_since'  => ['MEMBER SINCE', 'str:24', 'Tile label: member since'],
        'membership_card_label_payment'       => ['PAYMENT', 'str:24', 'Tile label: payment'],
        'membership_card_label_fee'           => ['{period} MEMBERSHIP', 'str:30', 'Tile label: fee ({period} = ANNUAL, MONTHLY ...)'],
        'membership_card_label_type'          => ['MEMBERSHIP TYPE', 'str:24', 'Tile label: type'],
        'membership_card_label_district'      => ['DISTRICT', 'str:24', 'Tile label: district'],
        'membership_card_scan_text'           => ['Scan to verify membership', 'str:40', 'Text under the QR code'],
        'membership_card_renews_text'         => ['Renews {period}', 'str:40', 'Text under the expiry date ({period} = annually, monthly ...)'],
        'membership_card_no_expiry_text'      => ['No expiry', 'str:30', 'Expiry wording for lifetime / one-time memberships'],
        'membership_card_status_active'       => ['ACTIVE MEMBER', 'str:24', 'Status pill: active'],
        'membership_card_status_expired'      => ['EXPIRED', 'str:24', 'Status pill: expired'],
        'membership_card_status_suspended'    => ['SUSPENDED', 'str:24', 'Status pill: suspended'],
        'membership_card_status_revoked'      => ['NOT VALID', 'str:24', 'Status pill: revoked'],
        'membership_card_pay_paid'            => ['Confirmed', 'str:24', 'Payment tile: balance cleared'],
        'membership_card_pay_waived'          => ['Waived', 'str:24', 'Payment tile: fee waived'],
        'membership_card_pay_pending'         => ['Pending', 'str:24', 'Payment tile: still to pay'],
        'membership_card_pay_free'            => ['No fee', 'str:24', 'Payment tile: free membership'],
        'membership_card_pay_partial'         => ['Part paid', 'str:24', 'Payment tile: part paid'],
        'membership_card_pay_tbd'             => ['Fee not set', 'str:24', 'Payment tile: no fee set yet'],
        'membership_card_fee_free'            => ['Free', 'str:24', 'Fee tile: free membership'],
        'membership_card_fee_tbd'             => ['To be announced', 'str:24', 'Fee tile: no fee set yet'],
        'membership_card_fee_per_year'        => ['/ year', 'str:16', 'Fee tile: after the amount of a yearly fee'],
        'membership_card_fee_per_month'       => ['/ month', 'str:16', 'Fee tile: after the amount of a monthly fee'],
        'membership_card_fee_per_quarter'     => ['/ quarter', 'str:16', 'Fee tile: after the amount of a quarterly fee'],
        'membership_card_fee_for_life'        => ['(for life)', 'str:16', 'Fee tile: after the amount of a lifetime fee'],
        'membership_card_fee_one_off'         => ['(one-off)', 'str:16', 'Fee tile: after the amount of a one-time fee'],
        'membership_card_period_annual'       => ['ANNUAL', 'str:16', 'Period word: annual'],
        'membership_card_period_monthly'      => ['MONTHLY', 'str:16', 'Period word: monthly'],
        'membership_card_period_quarterly'    => ['QUARTERLY', 'str:16', 'Period word: quarterly'],
        'membership_card_period_lifetime'     => ['LIFETIME', 'str:16', 'Period word: lifetime'],
        'membership_card_period_one_time'     => ['ONE-TIME', 'str:16', 'Period word: one-time'],
        'membership_card_adverb_annual'       => ['annually', 'str:16', 'Renewal word: annually'],
        'membership_card_adverb_monthly'      => ['monthly', 'str:16', 'Renewal word: monthly'],
        'membership_card_adverb_quarterly'    => ['quarterly', 'str:16', 'Renewal word: quarterly'],
        'membership_card_joining_paid'        => ['Joining fee {amount} paid', 'str:60', 'Footer {joining}: joining fee paid'],
        'membership_card_joining_due'         => ['Joining fee {amount} due', 'str:60', 'Footer {joining}: joining fee still to pay'],
        'membership_card_joining_waived'      => ['Joining fee {amount} waived', 'str:60', 'Footer {joining}: joining fee waived'],
        'membership_card_location_format'     => ['{city}, {country}', 'str:60', 'Location line ({city}, {district}, {country})'],
        'membership_card_panel_text'          => ["Present this card for member check-in\nLumina Africa Foundation \u{00B7} Gulu, Uganda", 'text:160', 'Text at the bottom of the side panel (two lines)'],
        // ---- footer lines (grey, bottom left)
        'membership_card_footer_1'            => ["{joining} \u{00B7} Issued {issued}", 'str:120', 'Footer line 1'],
        'membership_card_footer_2'            => ["Support: {phone} \u{00B7} {email}", 'str:120', 'Footer line 2'],
        'membership_card_footer_3'            => ['', 'str:120', 'Footer line 3 (extra, optional)'],
        'membership_card_support_phone'       => ['', 'str:30', 'Support phone (empty = the phone in Site settings)'],
        'membership_card_support_email'       => ['', 'email', 'Support e-mail (empty = the info e-mail in Site settings)'],
        // ---- numbering
        'membership_card_number_format'       => [self::DEFAULT_NUMBER_FORMAT, 'format', 'Member number format'],
        // ---- behaviour
        'membership_card_trigger'             => ['paid_approved', 'enum:paid_approved,approved,manual', 'When is a card issued and e-mailed automatically'],
        'membership_card_reissue_renewal'     => ['1', 'bool', 'Issue a new card (new valid-until date) when a renewal is paid'],
        'membership_card_require_photo'       => ['0', 'bool', 'Photo required before the card is issued'],
        'membership_photo_required'           => ['1', 'bool', 'Photo required on the registration form'],
        'membership_proof_retention_days'     => ['90', 'int:0:3650', 'Delete photo and proof this many days after a rejection or cancellation (0 = never)'],
        // ---- the card e-mail
        'membership_card_email_subject'       => ['Your {site} membership card ({number})', 'str:150', 'Card e-mail subject'],
        'membership_card_email_body'          => ["Hello {first_name},\n\nYour membership is confirmed and your membership card is attached to this message as a PDF. Keep it on your phone or print it.\n\nName: {name}\nMember number: {number}\nType: {type}\nValid until: {valid_until}\n\nHow to use your card: show it (printed or on your phone) when you check in at our events and meetings. Anyone can scan the QR code on it to confirm that your membership is genuine and current.\n\n{photo_note}Your private membership page (download your card again, upload a photo): {dashboard_url}\n\nQuestions? Reply to this e-mail or write to {contact_email}.\n\nThank you for standing with {site}.", 'text:3000', 'Card e-mail text'],
    ];

    /** Placeholders of the card e-mail subject and body. */
    public const EMAIL_PLACEHOLDERS = ['{name}', '{first_name}', '{site}', '{number}', '{type}', '{valid_until}', '{start_date}', '{dashboard_url}', '{contact_email}', '{support_phone}', '{photo_note}'];
    public const COLOR_KEYS = ['membership_card_navy', 'membership_card_accent', 'membership_card_gold', 'membership_card_green', 'membership_card_tile', 'membership_card_label_color', 'membership_card_muted'];

    // ------------------------------------------------------------------------------------------------ reading

    /** The stored value of a key, else its default; '' for an unknown key. Never throws (no database = the defaults). */
    public static function get(string $key): string
    {
        $def = self::DEFS[$key] ?? null;
        if ($def === null) {
            return '';
        }
        try {
            return (string) Settings::get($key, $def[0]);
        } catch (\Throwable) {
            return (string) $def[0];
        }
    }

    public static function bool(string $key): bool
    {
        return self::get($key) === '1';
    }

    public static function int(string $key): int
    {
        return (int) self::get($key);
    }

    public static function default(string $key): string
    {
        return (string) (self::DEFS[$key][0] ?? '');
    }

    /** @return array<string,string> key => default */
    public static function defaults(): array
    {
        return array_map(static fn(array $d): string => (string) $d[0], self::DEFS);
    }

    /**
     * Every key resolved (stored value or default) with $override applied on top. Values in $override are sanitised first, so a preview built from
     * a posted form can never inject anything; an invalid override value falls back to the stored one.
     * @param array<string,mixed> $override @return array<string,string>
     */
    public static function resolve(array $override = []): array
    {
        $out = [];
        foreach (self::DEFS as $key => $_) {
            $out[$key] = self::get($key);
        }
        if ($override !== []) {
            $clean = self::sanitise($override, false);
            foreach ($clean['values'] as $k => $v) {
                if (!isset($clean['errors'][$k])) {
                    $out[$k] = $v;
                }
            }
        }
        return $out;
    }

    /** The seven colours of a resolved settings array, each guaranteed to be a valid #rrggbb. @param array<string,string> $values @return array<string,string> short name => hex */
    public static function colors(array $values): array
    {
        $out = [];
        foreach (self::COLOR_KEYS as $key) {
            $hex = self::normaliseColor((string) ($values[$key] ?? ''));
            $out[str_replace(['membership_card_', '_color'], '', $key)] = $hex ?? self::default($key);
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ validation

    /**
     * Clean and validate designer input. Only keys present in $input are returned (checkboxes: when $form is true a missing bool means "off",
     * because an unticked HTML checkbox posts nothing). Strings lose control and zero-width characters and are cut to the length of their type;
     * colours must be #rgb / #rrggbb; enums must be a listed value; the number format is checked by numberFormatError(); an upload path must look like an uploads/ path.
     * @param array<string,mixed> $input
     * @return array{values:array<string,string>,errors:array<string,string>}
     */
    public static function sanitise(array $input, bool $form = true): array
    {
        $values = [];
        $errors = [];
        foreach (self::DEFS as $key => [$default, $type, $label]) {
            $present = array_key_exists($key, $input);
            if (!$present && !($form && $type === 'bool')) {
                continue;
            }
            $raw = $present ? $input[$key] : '0';
            if (!is_scalar($raw) && $raw !== null) {
                $errors[$key] = $label . ': enter a single value.';
                $values[$key] = (string) $default;
                continue;
            }
            $raw = (string) $raw;
            [$kind, $a, $b] = array_pad(explode(':', $type, 3), 3, '');
            switch ($kind) {
                case 'bool':
                    $values[$key] = in_array(strtolower(trim($raw)), ['1', 'on', 'yes', 'true'], true) ? '1' : '0';
                    break;
                case 'color':
                    $c = self::normaliseColor($raw);
                    if ($c === null) {
                        $errors[$key] = $label . ': use a colour such as #0F1B33.';
                        $values[$key] = (string) $default;
                    } else {
                        $values[$key] = $c;
                    }
                    break;
                case 'enum':
                    $opts = explode(',', (string) $a . ($b !== '' ? ':' . $b : ''));
                    $v = trim($raw);
                    if (!in_array($v, $opts, true)) {
                        $errors[$key] = $label . ': choose one of the listed options.';
                        $values[$key] = (string) $default;
                    } else {
                        $values[$key] = $v;
                    }
                    break;
                case 'int':
                    $v = trim($raw);
                    if (preg_match('/^\d{1,6}$/D', $v) !== 1 || (int) $v < (int) $a || (int) $v > (int) $b) {
                        $errors[$key] = $label . ': enter a whole number from ' . $a . ' to ' . $b . '.';
                        $values[$key] = (string) $default;
                    } else {
                        $values[$key] = (string) (int) $v;
                    }
                    break;
                case 'text':
                    $values[$key] = self::cleanText($raw, (int) $a);
                    break;
                case 'email':
                    $v = self::cleanLine($raw, 190);
                    if ($v !== '' && (filter_var($v, FILTER_VALIDATE_EMAIL) === false || preg_match('/[\s<>(),;:"\\\\]/', $v) === 1)) {
                        $errors[$key] = $label . ': enter a valid e-mail address.';
                        $values[$key] = (string) $default;
                    } else {
                        $values[$key] = $v;
                    }
                    break;
                case 'image':
                    $v = trim($raw);
                    if ($v !== '' && (preg_match('~^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*/[A-Za-z0-9_\-]+\.(png|jpg|jpeg|webp|gif)$~D', $v) !== 1 || str_contains($v, '..'))) {
                        $errors[$key] = $label . ': that is not a stored picture.';
                        $values[$key] = (string) $default;
                    } else {
                        $values[$key] = $v;
                    }
                    break;
                case 'format':
                    $v = strtoupper(self::cleanLine($raw, 60));
                    $err = self::numberFormatError($v);
                    if ($err !== null) {
                        $errors[$key] = $err;
                        $values[$key] = (string) $default;
                    } else {
                        $values[$key] = $v;
                    }
                    break;
                default:                                               // str
                    $v = self::cleanLine($raw, (int) $a);
                    $values[$key] = $v;
                    if ($v === '' && !in_array($key, self::MAY_BE_EMPTY, true)) {
                        $errors[$key] = $label . ': this cannot be empty.';
                        $values[$key] = (string) $default;
                    }
            }
        }
        return ['values' => $values, 'errors' => $errors];
    }

    /** Text keys that may be saved empty (empty = use the Site settings, hide the line, ...). */
    private const MAY_BE_EMPTY = ['membership_card_footer_1', 'membership_card_footer_2', 'membership_card_footer_3', 'membership_card_support_phone',
        'membership_card_support_email', 'membership_card_logo', 'membership_card_label_member', 'membership_card_label_from', 'membership_card_label',
        'membership_card_scan_text', 'membership_card_renews_text', 'membership_card_tagline', 'membership_card_location_format'];

    /**
     * Validate and store. Only values that differ from the default are kept in the settings table (saving the default removes the override).
     * The audit entry lists the changed key NAMES only.
     * @param array<string,mixed> $input @return array{ok:bool,errors:array<string,string>,values:array<string,string>,changed:list<string>}
     */
    public static function save(array $input, ?int $userId = null): array
    {
        $r = self::sanitise($input, true);
        if ($r['errors'] !== []) {
            return ['ok' => false, 'errors' => $r['errors'], 'values' => $r['values'], 'changed' => []];
        }
        $changed = [];
        $write = [];
        $reset = [];
        foreach ($r['values'] as $key => $value) {
            if ($value === self::get($key)) {
                continue;
            }
            $changed[] = $key;
            if ($value === self::default($key)) {
                $reset[] = $key;
            } else {
                $write[$key] = $value;
            }
        }
        if ($write !== []) {
            Settings::setMany($write);
        }
        foreach ($reset as $k) {
            Settings::reset($k);
        }
        if ($changed !== []) {
            AuditLog::record('membership_card.settings', 'settings', null, 'Changed the membership card settings', ['keys' => $changed, 'user_id' => $userId]);
        }
        return ['ok' => true, 'errors' => [], 'values' => $r['values'], 'changed' => $changed];
    }

    // ------------------------------------------------------------------------------------------------ text helpers

    /** One line: control, zero-width and bidi-override characters removed, whitespace collapsed, cut to $max characters. */
    public static function cleanLine(string $s, int $max): string
    {
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = (string) mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        }
        $s = (string) preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2064}\x{2066}-\x{2069}\x{FEFF}]+/u', '', $s);
        $s = (string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $s);
        $s = trim((string) preg_replace('/\s+/u', ' ', $s));
        return mb_substr($s, 0, max(0, $max));
    }

    /** Several lines (at most 6): same cleaning, line breaks kept. */
    public static function cleanText(string $s, int $max): string
    {
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = (string) mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        }
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = (string) preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2064}\x{2066}-\x{2069}\x{FEFF}]+/u', '', $s);
        $s = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $s);
        $lines = array_map(static fn(string $l): string => trim((string) preg_replace('/[^\S\n]+/u', ' ', $l)), explode("\n", $s));
        $s = trim(implode("\n", array_slice($lines, 0, max(6, intdiv($max, 40)))));   // short fields keep 6 lines; the 3000-character e-mail message keeps up to 75
        $s = (string) preg_replace("/\n{3,}/", "\n\n", $s);
        return mb_substr($s, 0, max(0, $max));
    }

    /** "#0f1b33" / "0F1B33" / "#abc" -> "#0F1B33"; null when it is not a colour. */
    public static function normaliseColor(string $raw): ?string
    {
        $h = ltrim(trim($raw), '#');
        if (preg_match('/^[0-9a-fA-F]{3}$/D', $h) === 1) {
            $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }
        return preg_match('/^[0-9a-fA-F]{6}$/D', $h) === 1 ? '#' . strtoupper($h) : null;
    }

    // ------------------------------------------------------------------------------------------------ contrast

    /** WCAG relative luminance of a #rrggbb colour. */
    public static function luminance(string $hex): float
    {
        $h = ltrim($hex, '#');
        $c = [hexdec(substr($h, 0, 2)) / 255, hexdec(substr($h, 2, 2)) / 255, hexdec(substr($h, 4, 2)) / 255];
        $c = array_map(static fn(float $v): float => $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, $c);
        return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
    }

    /** WCAG contrast ratio of two colours (1 to 21). */
    public static function contrast(string $a, string $b): float
    {
        $la = self::luminance($a);
        $lb = self::luminance($b);
        [$hi, $lo] = $la >= $lb ? [$la, $lb] : [$lb, $la];
        return ($hi + 0.05) / ($lo + 0.05);
    }

    /**
     * Contrast checks of the colours that carry text on the card. WCAG AA: small text needs 4.5:1, only the big initials block 3:1. The owner's REFERENCE
     * colours fall short on a few small texts (gold tagline 2.6, accent type line 3.5, white on the green pill 3.3, tile labels 4.4): they stay the default (it is
     * the owner's design) and are reported honestly here.
     * @param array<string,string> $values resolved settings @return list<array{key:string,message:string,ratio:float}>
     */
    public static function warnings(array $values): array
    {
        $c = self::colors($values);
        $checks = [
            ['membership_card_navy', 'White text on the side panel (member number, expiry date)', '#FFFFFF', $c['navy'], 4.5],
            ['membership_card_muted', 'Small text on the side panel', $c['muted'], $c['navy'], 4.5],
            ['membership_card_green', 'White status text on the active pill', '#FFFFFF', $c['green'], 4.5],
            ['membership_card_accent', 'White initials on the photo block', '#FFFFFF', $c['accent'], 3.0],
            ['membership_card_accent', 'Membership type text on the white panel', $c['accent'], '#FFFFFF', 4.5],
            ['membership_card_gold', 'Tagline on the white panel', $c['gold'], '#FFFFFF', 4.5],
            ['membership_card_label_color', 'Small labels on the white panel', $c['label'], '#FFFFFF', 4.5],
            ['membership_card_label_color', 'Small labels on the info tiles', $c['label'], $c['tile'], 4.5],
            ['membership_card_tile', 'Navy text on the info tiles', $c['navy'], $c['tile'], 7.0],
        ];
        $out = [];
        foreach ($checks as [$key, $what, $fg, $bg, $min]) {
            $ratio = self::contrast($fg, $bg);
            if ($ratio < $min) {
                $out[] = ['key' => $key, 'message' => $what . ' is hard to read (contrast ' . number_format($ratio, 1) . ':1, at least ' . number_format($min, 1) . ':1 is recommended).', 'ratio' => round($ratio, 2)];
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ member number format

    /**
     * Why a member-number format is not acceptable, or null. Rules: only the placeholders {PREFIX} {TYPE} {YY} {YYYY} {SEQ} / {SEQ:3..8} and the
     * characters A-Z 0-9 and "-" (so a number is safe in links, file names and CSV); {SEQ} exactly once and LAST (the running number is the part
     * that counts up inside the text before it); at most 40 characters even with the longest prefix, type code and year.
     */
    public static function numberFormatError(string $format): ?string
    {
        $format = trim($format);
        if ($format === '') {
            return 'Enter a number format such as {PREFIX}-{TYPE}-{YY}-{SEQ}.';
        }
        if (strlen($format) > 60) {
            return 'The number format is too long.';
        }
        if (preg_match('/\{SEQ(?::[3-8])?\}$/D', $format) !== 1) {
            return 'The number format must end with {SEQ} (the running number), for example {PREFIX}-{TYPE}-{YY}-{SEQ}.';
        }
        if (preg_match_all('/\{SEQ(?::[3-8])?\}/', $format) !== 1) {
            return 'Use {SEQ} only once.';
        }
        $rest = (string) preg_replace('/\{(PREFIX|TYPE|YY|YYYY)\}|\{SEQ(?::[3-8])?\}/', '', $format);
        if (str_contains($rest, '{') || str_contains($rest, '}')) {
            return 'Only {PREFIX}, {TYPE}, {YY}, {YYYY} and {SEQ} may be used inside braces.';
        }
        if (preg_match('/^[A-Z0-9-]*$/D', $rest) !== 1) {
            return 'Besides the placeholders, use only capital letters, digits and "-".';
        }
        if (strlen(self::expandNumber($format, str_repeat('P', 10), str_repeat('T', 8), 2099, 99999999)) > self::NUMBER_MAX_LENGTH) {
            return 'The longest possible number would be more than ' . self::NUMBER_MAX_LENGTH . ' characters. Shorten the format.';
        }
        if (str_starts_with($format, '-') || str_contains($format, '--')) {
            return 'Do not start the format with "-" or write "--".';
        }
        if (preg_match('/\{(?:TYPE|YY|YYYY)\}\{SEQ/', $format) === 1) {
            // without a separator the series of one membership type could swallow the numbers of another (codes CDA and CDA1: "CDA" + 10001 is also "CDA1" + 0001)
            return 'Put a "-" between {TYPE}, {YY} or {YYYY} and {SEQ}, for example {PREFIX}-{TYPE}-{YY}-{SEQ}.';
        }
        return null;
    }

    /**
     * The text of a member number for given parts. {SEQ} is padded to 4 digits ({SEQ:6} to 6).
     */
    public static function expandNumber(string $format, string $prefix, string $typeCode, int $year, int $seq): string
    {
        return (string) preg_replace_callback('/\{(PREFIX|TYPE|YYYY|YY|SEQ(?::[3-8])?)\}/', static function (array $m) use ($prefix, $typeCode, $year, $seq): string {
            $t = $m[1];
            if ($t === 'PREFIX') {
                return $prefix;
            }
            if ($t === 'TYPE') {
                return $typeCode;
            }
            if ($t === 'YYYY') {
                return (string) $year;
            }
            if ($t === 'YY') {
                return substr((string) $year, -2);
            }
            $pad = str_contains($t, ':') ? (int) substr($t, 4) : 4;
            return str_pad((string) $seq, $pad, '0', STR_PAD_LEFT);
        }, $format);
    }

    /**
     * The fixed text before the running number, and the digit width, for one member type and year: ['LAF-IND-26-', 4]. The next number of that
     * series is this text plus (highest running number found + 1). Because {SEQ} is last, every number of a series starts with the same text.
     * @return array{0:string,1:int}
     */
    public static function numberSeries(string $format, string $prefix, string $typeCode, int $year): array
    {
        $pos = strrpos($format, '{SEQ');
        $head = $pos === false ? $format : substr($format, 0, $pos);
        $pad = preg_match('/\{SEQ:([3-8])\}$/D', $format, $m) === 1 ? (int) $m[1] : 4;
        return [self::expandNumber($head, $prefix, $typeCode, $year, 0), $pad];
    }
}
