<?php
declare(strict_types=1);

namespace App\Services\Membership;

use App\Core\AuditLog;
use App\Core\Config;
use App\Core\Database;
use App\Core\PageRegistry;
use App\Core\Slug;
use App\Core\Validator;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MembershipType;
use App\Models\Program;

/**
 * MEMBERSHIP: the single place for the business rules, shared by the public site (MembershipController) and the admin
 * (MembersController, MembershipTypesController) so they can never drift. Contract and examples: docs/MEMBERSHIP.md.
 * OWNER: architect (frozen for the web and admin agents: call it, do not copy it).
 *
 * PRIVACY: this class never writes personal data to logs; audit entries carry ids, references, statuses and field NAMES only.
 *
 * Groups: settings, options (editable lists), money and period labels, dates (age, expiry), numbers (reference, membership
 * number, receipt), confirmation links, status workflow, renewal, payments and balance, membership types, counters.
 * Applications (validation + creation) are in MembershipApplication; e-mails in MembershipMail.
 */
final class MembershipService
{
    // ------------------------------------------------------------------------------------------------ constants

    public const ADULT_AGE = 18;
    public const MOTIVATION_MIN = 20;
    /** The option key that makes the form ask for free text (occupation, how you heard about us). */
    public const OTHER_KEY = 'other';
    /** Statuses in which a person "already has an application": a second application with the same e-mail is not created. */
    public const OPEN_STATUSES = ['pending', 'approved', 'suspended'];
    /** Statuses that occupy a place of a type that has max_members. */
    public const CAPACITY_STATUSES = ['pending', 'approved'];
    public const PERIODS = ['one_time', 'monthly', 'quarterly', 'annual', 'lifetime'];
    /** Billing periods that give an expiry date (months added to the start date). one_time and lifetime never expire. */
    public const PERIOD_MONTHS = ['monthly' => 1, 'quarterly' => 3, 'annual' => 12];

    /** status => statuses it may move to by changeStatus(). expired -> approved happens only through renew(). */
    public const TRANSITIONS = [
        'pending'   => ['approved', 'rejected', 'cancelled'],
        'approved'  => ['suspended', 'expired', 'cancelled'],
        'suspended' => ['approved', 'expired', 'cancelled'],
        'expired'   => ['cancelled'],
        'rejected'  => ['pending'],
        'cancelled' => ['pending'],
    ];
    /** Moving INTO these statuses needs a reason (status_reason). */
    public const REASON_REQUIRED = ['rejected', 'suspended', 'cancelled'];

    public const STATUS_LABELS = [
        'pending' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected',
        'suspended' => 'Suspended', 'expired' => 'Expired', 'cancelled' => 'Cancelled',
    ];
    /** status => badge tone understood by partials/admin/status-badge (and .badge--<tone>) */
    public const STATUS_TONES = [
        'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger',
        'suspended' => 'orange', 'expired' => 'neutral', 'cancelled' => 'neutral',
    ];
    public const PAYMENT_STATUS_LABELS = ['received' => 'Received', 'pending' => 'Pending', 'waived' => 'Waived'];
    public const PAYMENT_TONES = ['received' => 'success', 'pending' => 'warning', 'waived' => 'info'];
    public const PURPOSE_LABELS = ['joining' => 'Joining fee', 'membership' => 'Membership fee', 'renewal' => 'Renewal', 'other' => 'Other'];
    /** Payment status of a MEMBER (not of one payment row): see paymentSummary(). */
    public const PAYMENT_SUMMARY_LABELS = ['tbd' => 'Fee not set', 'free' => 'No fee', 'unpaid' => 'Unpaid', 'partial' => 'Part paid', 'paid' => 'Paid'];
    public const PAYMENT_SUMMARY_TONES = ['tbd' => 'neutral', 'free' => 'neutral', 'unpaid' => 'warning', 'partial' => 'info', 'paid' => 'success'];
    public const PERIOD_LABELS_ADMIN = ['one_time' => 'One-time', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual', 'lifetime' => 'Lifetime'];
    /** Currencies shown without decimals. */
    private const ZERO_DECIMAL = ['UGX', 'RWF', 'BIF', 'XOF', 'XAF', 'JPY', 'KRW', 'TZS'];

    private static ?int $pending = null;

    // ------------------------------------------------------------------------------------------------ settings

    /** Is registration open? (setting membership_open, default OFF: the owner opens it after replacing the placeholder terms, fees and privacy text). */
    public static function isOpen(): bool
    {
        return (string) setting('membership_open', '0') === '1';
    }

    /** Does the form ask for a national ID / passport number? (setting membership_collect_id, default off). */
    public static function collectsId(): bool
    {
        return (string) setting('membership_collect_id', '0') === '1';
    }

    public static function defaultCurrency(): string
    {
        $c = strtoupper(trim((string) setting('membership_currency', 'UGX')));
        return preg_match('/^[A-Z]{3}$/', $c) === 1 ? $c : 'UGX';
    }

    /** Upper-case letters and digits only (2 to 10), default LAF. */
    public static function prefix(): string
    {
        $p = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', (string) setting('membership_number_prefix', 'LAF')));
        return strlen($p) >= 2 ? substr($p, 0, 10) : 'LAF';
    }

    /** Contact address for applicants: membership_contact_email, else email_primary. */
    public static function contactEmail(): string
    {
        $e = trim((string) setting('membership_contact_email', ''));
        return $e !== '' ? $e : trim((string) setting('email_primary', ''));
    }

    /** Plain-text payment instructions: membership_payment_instructions, else the donation bank + mobile-money details. '' when none. */
    public static function paymentInstructions(): string
    {
        $own = trim((string) setting('membership_payment_instructions', ''));
        if ($own !== '') {
            return $own;
        }
        $parts = array_values(array_filter([
            trim((string) setting('donate_bank_details', '')),
            trim((string) setting('donate_mobile_money', '')),
        ], static fn(string $s): bool => $s !== ''));
        return implode("\n\n", $parts);
    }

    // ------------------------------------------------------------------------------------------------ editable option lists

    /**
     * Choices for a form list as key => label. Lists: occupation, education, availability, heard, payment_methods, interests (program
     * pillars + extras), gender (fixed keys, editable labels). Falls back to the built-in defaults when the owner emptied a list.
     * Keys are lower-case [a-z0-9_] (interests: "program:<slug>" and the extra keys); the label is never empty.
     * @return array<string,string>
     */
    public static function options(string $list): array
    {
        if ($list === 'gender') {
            return [
                'female'            => self::copy('options.gender_female', 'Female'),
                'male'              => self::copy('options.gender_male', 'Male'),
                'prefer_not_to_say' => self::copy('options.gender_undisclosed', 'Prefer not to say'),
            ];
        }
        if ($list === 'interests') {
            $out = [];
            try {
                foreach (Program::active() as $p) {
                    $out['program:' . $p['slug']] = (string) $p['title'];
                }
            } catch (\Throwable) {
                // no database: extras only
            }
            return $out + self::registryList('options.interests_extra');
        }
        $map = ['occupation' => 'options.occupation', 'education' => 'options.education', 'availability' => 'options.availability',
            'heard' => 'options.heard', 'payment_methods' => 'options.payment_methods'];
        return isset($map[$list]) ? self::registryList($map[$list]) : [];
    }

    /** Label for a stored key; a key the owner removed later still shows as a readable fallback. */
    public static function optionLabel(string $list, ?string $key): string
    {
        $key = (string) $key;
        if ($key === '') {
            return '';
        }
        $all = self::options($list);
        if (isset($all[$key])) {
            return $all[$key];
        }
        $plain = str_starts_with($key, 'program:') ? substr($key, 8) : $key;
        return ucfirst(str_replace(['_', '-'], ' ', $plain));
    }

    /** @return list<string> keys stored in members.interests (JSON) */
    public static function decodeInterests(?string $json): array
    {
        $list = json_decode((string) $json, true);
        return is_array($list) ? array_values(array_filter(array_map('strval', $list), static fn(string $s): bool => $s !== '')) : [];
    }

    /** @return list<string> readable labels of the stored interests */
    public static function interestLabels(?string $json): array
    {
        return array_map(static fn(string $k): string => self::optionLabel('interests', $k), self::decodeInterests($json));
    }

    /** @return array<string,string> */
    private static function registryList(string $blockKey): array
    {
        $rows = content_list('membership', $blockKey);
        $out = self::rowsToOptions($rows);
        if ($out === []) {
            $field = PageRegistry::field('membership', $blockKey);
            $out = self::rowsToOptions((array) ($field['default'] ?? []));
        }
        return $out;
    }

    /**
     * Rows of an editable choice list -> key => label. A row with a label but no (or an unusable) "Short code" gets a code derived from its
     * label (lower case, letters and digits, other characters become "_"); a code that is already taken gets a numeric suffix, so no choice the
     * owner typed is silently dropped. Rows without a label are ignored. Stored answers keep their key, so codes already in use never change.
     * @param array<int,array<string,mixed>> $rows @return array<string,string>
     */
    private static function rowsToOptions(array $rows): array
    {
        $out = [];
        $pending = [];
        foreach ($rows as $r) {
            $label = trim((string) preg_replace('/\s+/u', ' ', (string) ($r['label'] ?? '')));
            if ($label === '') {
                continue;
            }
            $key = strtolower(trim((string) ($r['key'] ?? '')));
            if (preg_match('/^[a-z0-9_]{1,40}$/', $key) === 1 && !isset($out[$key])) {
                $out[$key] = $label;                    // a valid, unused code is kept exactly as typed
            } else {
                $pending[] = [$key, $label];            // derive after all explicit codes are known, so they never clash
            }
        }
        foreach ($pending as [$key, $label]) {
            $out[self::deriveKey($key !== '' ? $key : $label, $out)] = $label;
        }
        return $out;
    }

    /** @param array<string,string> $taken @return string a unique [a-z0-9_] code of 1 to 40 characters */
    private static function deriveKey(string $source, array $taken): string
    {
        $base = trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower((string) (function_exists('iconv') ? @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $source) : $source))), '_');
        $base = substr($base !== '' ? $base : 'choice', 0, 34);
        $key = $base;
        for ($i = 2; isset($taken[$key]); $i++) {
            $key = $base . '_' . $i;
        }
        return $key;
    }

    /** Registry copy with a built-in fallback so a missing key never prints an empty label. */
    public static function copy(string $blockKey, string $fallback = ''): string
    {
        $v = trim((string) content('membership', $blockKey, $fallback));
        return $v !== '' ? $v : $fallback;
    }

    /**
     * Short fingerprints of the membership terms and of the privacy notice as they read NOW. Stored with the consent times of an application
     * so the owner can tell later whether the text changed after a member accepted it (the texts are editable and not versioned).
     * @return array{terms:string,privacy:string}
     */
    public static function consentVersions(): array
    {
        $privacy = json_encode(content_list('privacy', 'notice.items')) . '|' . (section_on('privacy', 'membership') ? (string) content('privacy', 'membership.body', '') : '');
        return [
            'terms'   => substr(sha1((string) content('membership-terms', 'terms.body', '')), 0, 12),
            'privacy' => substr(sha1((string) $privacy), 0, 12),
        ];
    }

    // ------------------------------------------------------------------------------------------------ labels, money, periods

    /** @return array{label:string,tone:string} */
    public static function statusBadge(string $status): array
    {
        return ['label' => self::STATUS_LABELS[$status] ?? label_from($status), 'tone' => self::STATUS_TONES[$status] ?? 'neutral'];
    }

    /** @return array{label:string,tone:string} badge for one payment row */
    public static function paymentBadge(string $status): array
    {
        return ['label' => self::PAYMENT_STATUS_LABELS[$status] ?? label_from($status), 'tone' => self::PAYMENT_TONES[$status] ?? 'neutral'];
    }

    /** @return array{label:string,tone:string} badge for the member's overall payment status (paymentSummary()['status']) */
    public static function summaryBadge(string $status): array
    {
        return ['label' => self::PAYMENT_SUMMARY_LABELS[$status] ?? label_from($status), 'tone' => self::PAYMENT_SUMMARY_TONES[$status] ?? 'neutral'];
    }

    /** Public wording of a billing period ("per year"), editable in Pages > Membership. */
    public static function periodLabel(string $period): string
    {
        $fallback = ['one_time' => 'one-off', 'monthly' => 'per month', 'quarterly' => 'per quarter', 'annual' => 'per year', 'lifetime' => 'for life'];
        return self::copy('types.period_' . $period, $fallback[$period] ?? '');
    }

    /** "50,000" / "1,500.50": whole amounts of zero-decimal currencies have no decimals. */
    public static function formatAmount(float|int|string|null $amount, string $currency): string
    {
        $n = (float) $amount;
        $zero = in_array(strtoupper($currency), self::ZERO_DECIMAL, true) && abs($n - round($n)) < 0.005;
        return number_format($n, $zero ? 0 : 2);
    }

    /** "UGX 50,000" (amount must not be null). */
    public static function formatMoney(float|int|string|null $amount, string $currency): string
    {
        return trim(strtoupper($currency) . ' ' . self::formatAmount($amount, $currency));
    }

    /**
     * The fee as visitors read it: "Fee to be announced" (NULL), "Free" (0) or "UGX 50,000 per year".
     * Wording comes from Pages > Membership (types.*). $withPeriod = false drops the period.
     */
    public static function feeLabel(float|int|string|null $amount, string $currency, string $period = 'annual', bool $withPeriod = true): string
    {
        if ($amount === null || $amount === '') {
            return self::copy('types.fee_tba', 'Fee to be announced');
        }
        if ((float) $amount <= 0) {
            return self::copy('types.free', 'Free');
        }
        $label = self::formatMoney($amount, $currency);
        $p = $withPeriod ? self::periodLabel($period) : '';
        return $p !== '' ? $label . ' ' . $p : $label;
    }

    /** Current fee of a membership TYPE row. */
    public static function typeFeeLabel(array $type): string
    {
        return self::feeLabel($type['fee_amount'] ?? null, (string) ($type['currency'] ?? self::defaultCurrency()), (string) ($type['period'] ?? 'annual'));
    }

    /** Fee of a MEMBER row = the snapshot taken at application time (never the type's current fee). */
    public static function memberFeeLabel(array $member): string
    {
        return self::feeLabel($member['fee_amount'] ?? null, (string) ($member['fee_currency'] ?? self::defaultCurrency()), (string) ($member['fee_period'] ?? 'annual'));
    }

    /** "Joining fee: UGX 20,000" or '' when the member/type has none. */
    public static function joiningFeeLabel(array $row, bool $isType = false): string
    {
        $amount = $row['joining_fee'] ?? null;
        if ($amount === null || $amount === '' || (float) $amount <= 0) {
            return '';
        }
        $cur = (string) ($row[$isType ? 'currency' : 'fee_currency'] ?? self::defaultCurrency());
        return str_replace('{amount}', self::formatMoney($amount, $cur), self::copy('types.joining_fee', 'Joining fee: {amount}'));
    }

    // ------------------------------------------------------------------------------------------------ dates

    /** Whole years between a date of birth and $on (default today). Invalid dates give -1. */
    public static function ageOn(string $dob, ?string $on = null): int
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $dob);
        $ref = $on !== null ? \DateTimeImmutable::createFromFormat('!Y-m-d', $on) : new \DateTimeImmutable('today');
        if ($d === false || $ref === false || $d->format('Y-m-d') !== $dob) {
            return -1;
        }
        return $d > $ref ? -1 : (int) $d->diff($ref)->y;
    }

    public static function isMinor(string $dob, ?string $on = null): bool
    {
        $age = self::ageOn($dob, $on);
        return $age >= 0 && $age < self::ADULT_AGE;
    }

    /**
     * Expiry date of a term: start + period (monthly +1 month, quarterly +3, annual +1 year; month ends are clamped, so
     * 31 Jan + 1 month = 28/29 Feb). one_time and lifetime return null (no expiry). The membership is valid UP TO (not including)
     * this date and counts as expired on it. Returns 'Y-m-d' | null.
     */
    public static function computeExpiry(\DateTimeInterface|string $start, string $period): ?string
    {
        $months = self::PERIOD_MONTHS[$period] ?? null;
        if ($months === null) {
            return null;
        }
        $d = $start instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($start) : \DateTimeImmutable::createFromFormat('!Y-m-d', substr($start, 0, 10));
        if ($d === false) {
            return null;
        }
        $day = (int) $d->format('j');
        $first = $d->modify('first day of this month')->modify('+' . $months . ' months');
        $last = (int) $first->format('t');
        return $first->setDate((int) $first->format('Y'), (int) $first->format('n'), min($day, $last))->format('Y-m-d');
    }

    // ------------------------------------------------------------------------------------------------ numbers

    /**
     * Next free sequence number as text: nextSequence('members', 'membership_number', 'LAF-2026-', 4) -> 'LAF-2026-0001'.
     * Only a suggestion: the UNIQUE index decides, callers retry on a duplicate (see assignNumber()).
     */
    public static function nextSequence(string $table, string $column, string $prefix, int $pad): string
    {
        return $prefix . str_pad((string) (self::maxSequence($table, $column, $prefix) + 1), $pad, '0', STR_PAD_LEFT);
    }

    /** Highest sequence number in use for a prefix (0 when none). */
    private static function maxSequence(string $table, string $column, string $prefix): int
    {
        return (int) Database::value(
            'SELECT MAX(CAST(SUBSTRING(' . Database::ident($column) . ', ?) AS UNSIGNED)) FROM ' . Database::ident($table) . ' WHERE ' . Database::ident($column) . ' LIKE ?',
            [strlen($prefix) + 1, $prefix . '%'],
            0
        );
    }

    /** A random, currently unused application reference LAF-APP-YYYY-NNNNN (not guessable, reveals no volume). Insertion retries on a clash. */
    public static function generateApplicationRef(?int $year = null): string
    {
        $year ??= (int) date('Y');
        for ($i = 0; $i < 25; $i++) {
            $ref = self::prefix() . '-APP-' . $year . '-' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            if (!Member::exists(['application_ref' => $ref])) {
                return $ref;
            }
        }
        return self::nextSequence('members', 'application_ref', self::prefix() . '-APP-' . $year . '-', 6);   // practically unreachable
    }

    /** Give the member a membership number if it has none (LAF-YYYY-NNNN, sequential per year); returns the number. Collision-safe. */
    public static function assignMembershipNumber(int $memberId, ?int $year = null): string
    {
        return self::assignNumber('members', 'membership_number', $memberId, self::prefix() . '-' . ($year ?? (int) date('Y')) . '-', 4);
    }

    /** Give the payment a receipt number if it has none (LAF-RCT-YYYY-NNNN); returns the number. Collision-safe. */
    public static function assignReceiptNumber(int $paymentId, ?int $year = null): string
    {
        return self::assignNumber('member_payments', 'receipt_number', $paymentId, self::prefix() . '-RCT-' . ($year ?? (int) date('Y')) . '-', 4);
    }

    /**
     * Claim the next number of a series for a row. Safe when several requests run at once, also INSIDE a transaction: the UNIQUE index
     * decides, and after a clash the next candidate is always higher than the last one tried (a transaction that reads a frozen snapshot
     * would otherwise propose the same taken number again and again). Callers that approve members additionally hold withNumberLock().
     */
    private static function assignNumber(string $table, string $column, int $id, string $prefix, int $pad): string
    {
        $t = Database::ident($table);
        $c = Database::ident($column);
        $seq = null;
        for ($i = 0; $i < 60; $i++) {
            $existing = Database::value('SELECT ' . $c . ' FROM ' . $t . ' WHERE id = ?', [$id]);
            if ($existing !== null && $existing !== '') {
                return (string) $existing;
            }
            $fresh = self::maxSequence($table, $column, $prefix) + 1;
            $seq = $seq === null ? $fresh : max($fresh, $seq + 1);
            $number = $prefix . str_pad((string) $seq, $pad, '0', STR_PAD_LEFT);
            try {
                Database::execute('UPDATE ' . $t . ' SET ' . $c . ' = ? WHERE id = ? AND ' . $c . ' IS NULL', [$number, $id]);
            } catch (\PDOException $e) {
                if ((string) $e->getCode() === '23000') {
                    continue;                      // somebody took that number a moment ago: try a higher one
                }
                throw $e;
            }
            $now = Database::value('SELECT ' . $c . ' FROM ' . $t . ' WHERE id = ?', [$id]);
            if ($now === $number) {
                return $number;
            }
        }
        throw new \RuntimeException('Could not allocate a ' . $column);
    }

    /**
     * Run $fn while holding a named database lock (GET_LOCK, 10 seconds) that is taken BEFORE a transaction is opened, so the transaction
     * starts after every competing one has committed and sees the numbers they took. Without the lock the work still runs (the UNIQUE
     * index and assignNumber() keep it correct), it just may retry more.
     */
    private static function withNumberLock(string $name, callable $fn): mixed
    {
        $lock = 'lumina_' . $name;
        $held = false;
        try {
            $held = (int) Database::value('SELECT GET_LOCK(?, 10)', [$lock], 0) === 1;
        } catch (\Throwable) {
            // no lock service: continue without
        }
        try {
            return $fn();
        } finally {
            if ($held) {
                try {
                    Database::value('SELECT RELEASE_LOCK(?)', [$lock]);
                } catch (\Throwable) {
                    // the lock goes away with the connection
                }
            }
        }
    }

    // ------------------------------------------------------------------------------------------------ confirmation link

    /** Signed link to the confirmation page: /membership/confirmation/{ref}?t=<exp>.<sig>. Valid for 30 days, cannot be guessed from a reference. */
    public static function confirmationUrl(string $ref, int $days = 30): string
    {
        $exp = time() + $days * 86400;
        return abs_url('membership/confirmation/' . rawurlencode($ref), ['t' => $exp . '.' . self::sign($ref, $exp)]);
    }

    /** Same link as a site-relative URL (for redirects). */
    public static function confirmationPath(string $ref, int $days = 30): string
    {
        $exp = time() + $days * 86400;
        return url('membership/confirmation/' . rawurlencode($ref), ['t' => $exp . '.' . self::sign($ref, $exp)]);
    }

    public static function verifyConfirmationToken(string $ref, string $token): bool
    {
        if (preg_match('/^(\d{9,11})\.([a-f0-9]{40})$/', $token, $m) !== 1) {
            return false;
        }
        return (int) $m[1] >= time() && hash_equals(self::sign($ref, (int) $m[1]), $m[2]);
    }

    private static function sign(string $ref, int $exp): string
    {
        return substr(hash_hmac('sha256', 'member-confirm|' . $ref . '|' . $exp, Config::appKey()), 0, 40);
    }

    // ------------------------------------------------------------------------------------------------ types: capacity, fee snapshot

    /** @return array{taken:int,left:?int,full:bool} places used by pending + approved members of a type */
    public static function capacity(array $type): array
    {
        $max = $type['max_members'] ?? null;
        $taken = (int) Database::value(
            'SELECT COUNT(*) FROM members WHERE type_id = ? AND status IN (' . self::in(self::CAPACITY_STATUSES) . ')',
            [(int) $type['id']],
            0
        );
        if ($max === null || $max === '') {
            return ['taken' => $taken, 'left' => null, 'full' => false];
        }
        $left = max(0, (int) $max - $taken);
        return ['taken' => $taken, 'left' => $left, 'full' => $left === 0];
    }

    /**
     * The columns copied from a type onto a new member: later fee changes never rewrite history.
     * @return array{type_name:string,fee_amount:?string,joining_fee:?string,fee_currency:string,fee_period:string}
     */
    public static function feeSnapshot(array $type): array
    {
        return [
            'type_name'    => (string) $type['name'],
            'fee_amount'   => ($type['fee_amount'] ?? null) === null || $type['fee_amount'] === '' ? null : number_format((float) $type['fee_amount'], 2, '.', ''),
            'joining_fee'  => ($type['joining_fee'] ?? null) === null || $type['joining_fee'] === '' ? null : number_format((float) $type['joining_fee'], 2, '.', ''),
            'fee_currency' => strtoupper((string) ($type['currency'] ?? self::defaultCurrency())),
            'fee_period'   => in_array($type['period'] ?? '', self::PERIODS, true) ? (string) $type['period'] : 'annual',
        ];
    }

    // ------------------------------------------------------------------------------------------------ term dates

    /** Earliest and latest date accepted as a term date (start, expiry, payment): keeps typos and overflow out of the records. */
    public const DATE_MIN = '2000-01-01';
    public const DATE_MAX_YEARS = 30;

    /** A real calendar date (Y-m-d, no roll-over such as 2026-02-30) inside the accepted window. */
    public static function validTermDate(string $date): bool
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($d === false || $d->format('Y-m-d') !== $date) {
            return false;
        }
        return $date >= self::DATE_MIN && $date <= date('Y-m-d', strtotime('+' . self::DATE_MAX_YEARS . ' years') ?: time());
    }

    /**
     * Does the type have a free place for a member who does not hold one now? Counts the OTHER members in $statuses against max_members.
     * Call inside a transaction: the type row is locked first, so two approvals cannot both take the last place.
     */
    private static function typeHasRoom(int $typeId, int $exceptMemberId, array $statuses): bool
    {
        $type = Database::one('SELECT id, max_members FROM membership_types WHERE id = ? FOR UPDATE', [$typeId]);
        if ($type === null || $type['max_members'] === null) {
            return true;
        }
        $n = (int) Database::value('SELECT COUNT(*) FROM members WHERE type_id = ? AND id <> ? AND status IN (' . self::in($statuses) . ')', [$typeId, $exceptMemberId], 0);
        return $n < (int) $type['max_members'];
    }

    // ------------------------------------------------------------------------------------------------ status workflow

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /** @return list<string> */
    public static function allowedTransitions(string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }

    /**
     * Move a member to another status. $opts: reason (required for rejected / suspended / cancelled), user_id, start_date (Y-m-d, first
     * approval only), expiry_date ('Y-m-d' or '' = none; default computed from the fee period).
     * First approval assigns the membership number and the start + expiry dates; approved_by / approved_at are set. Capacity is checked
     * on every move INTO a place-holding status (pending, approved) from one that holds none (reopen, reinstate), and on first approval.
     * Every change is written to the audit log (ids, references and statuses only).
     * Errors: invalid_status, reason_required, reason_too_long, not_found, not_allowed, type_full, term_lapsed, bad_start, bad_expiry.
     * @return array{ok:bool,error:?string,message:string,member:?array,number_assigned:bool}
     */
    public static function changeStatus(int $memberId, string $to, array $opts = []): array
    {
        $fail = static fn(string $code, string $msg): array => ['ok' => false, 'error' => $code, 'message' => $msg, 'member' => null, 'number_assigned' => false];
        if (!in_array($to, Member::STATUSES, true)) {
            return $fail('invalid_status', 'Unknown status.');
        }
        $reason = trim((string) preg_replace('/\s+/u', ' ', (string) ($opts['reason'] ?? '')));
        if (in_array($to, self::REASON_REQUIRED, true) && $reason === '') {
            return $fail('reason_required', 'Please give a reason.');
        }
        if (mb_strlen($reason) > 500) {
            return $fail('reason_too_long', 'The reason is too long (500 characters at most).');
        }
        $userId = isset($opts['user_id']) ? (int) $opts['user_id'] : null;
        $assigned = false;
        $error = null;

        $run = function () use ($memberId, $to, $reason, $opts, $userId, &$assigned, &$error): ?array {
            return Database::transaction(function () use ($memberId, $to, $reason, $opts, $userId, &$assigned, &$error): ?array {
                $m = Database::one('SELECT * FROM members WHERE id = ? FOR UPDATE', [$memberId]);
                if ($m === null) {
                    $error = ['not_found', 'Member not found.'];
                    return null;
                }
                $from = (string) $m['status'];
                if (!self::canTransition($from, $to)) {
                    $error = ['not_allowed', 'A member who is "' . (self::STATUS_LABELS[$from] ?? $from) . '" cannot be set to "' . (self::STATUS_LABELS[$to] ?? $to) . '".'];
                    return null;
                }
                if (!empty($m['anonymised_at']) && $to === 'pending') {
                    $error = ['not_allowed', 'The personal data of this record was erased, so it cannot be reopened.'];
                    return null;
                }
                $set = ['status' => $to, 'status_reason' => in_array($to, self::REASON_REQUIRED, true) ? $reason : ($reason !== '' ? $reason : null),
                    'status_changed_at' => date('Y-m-d H:i:s'), 'status_changed_by' => $userId];

                // moving into a status that holds a place (reopen -> pending, reinstate -> approved) needs a free place, like a new applicant
                if (in_array($to, self::CAPACITY_STATUSES, true) && !in_array($from, self::CAPACITY_STATUSES, true)
                    && !self::typeHasRoom((int) $m['type_id'], $memberId, self::CAPACITY_STATUSES)) {
                    $error = ['type_full', 'This membership type has no places left.'];
                    return null;
                }
                if ($to === 'approved' && $from === 'pending') {
                    if (!self::typeHasRoom((int) $m['type_id'], $memberId, ['approved'])) {
                        $error = ['type_full', 'This membership type has no places left.'];
                        return null;
                    }
                    $start = trim((string) ($opts['start_date'] ?? ''));
                    if ($start === '') {
                        $start = date('Y-m-d');
                    } elseif (!self::validTermDate($start)) {
                        $error = ['bad_start', 'Use a real start date between ' . format_date(self::DATE_MIN) . ' and ' . self::DATE_MAX_YEARS . ' years from now.'];
                        return null;
                    }
                    $set['start_date'] = $start;
                    if (array_key_exists('expiry_date', $opts) && $opts['expiry_date'] !== null && trim((string) $opts['expiry_date']) !== '') {
                        $expiry = trim((string) $opts['expiry_date']);
                        if (!self::validTermDate($expiry) || $expiry <= $start) {
                            $error = ['bad_expiry', 'The expiry date must be a real date after the start date.'];
                            return null;
                        }
                        $set['expiry_date'] = $expiry;
                    } elseif (array_key_exists('expiry_date', $opts) && $opts['expiry_date'] !== null) {
                        $set['expiry_date'] = null;               // explicitly empty: no expiry
                    } else {
                        $set['expiry_date'] = self::computeExpiry($start, (string) $m['fee_period']);
                    }
                    $set['approved_by'] = $userId;
                    $set['approved_at'] = date('Y-m-d H:i:s');
                }
                if ($to === 'approved' && $from === 'suspended' && !empty($m['expiry_date']) && (string) $m['expiry_date'] <= date('Y-m-d')) {
                    $error = ['term_lapsed', 'The term of this membership has ended. Mark it as expired first, then renew it.'];
                    return null;
                }
                Database::update('members', $set, 'id = ?', [$memberId]);
                if ($to === 'approved' && empty($m['membership_number'])) {
                    self::assignMembershipNumber($memberId);
                    $assigned = true;
                }
                $row = Member::find($memberId);
                AuditLog::record('member.status', 'member', $memberId, 'Application ' . $m['application_ref'] . ': ' . $from . ' -> ' . $to,
                    ['ref' => $m['application_ref'], 'from' => $from, 'to' => $to, 'number' => $row['membership_number'] ?? null, 'number_assigned' => $assigned]);
                return $row;
            });
        };
        // approving hands out a membership number: serialise approvals so the transaction starts after the previous one has committed
        $member = $to === 'approved' ? self::withNumberLock('member_number', $run) : $run();

        if ($member === null) {
            return $fail($error[0] ?? 'failed', $error[1] ?? 'The status could not be changed.');
        }
        self::flush();
        return ['ok' => true, 'error' => null, 'message' => 'Status set to ' . (self::STATUS_LABELS[$to] ?? $to) . '.', 'member' => $member, 'number_assigned' => $assigned];
    }

    /**
     * Start a new term for an approved or expired member: sets the new expiry date (continuing from the current expiry when it is still in the
     * future, otherwise from today, or from $opts['start_date']), re-activates an expired member, and creates a PENDING payment row of
     * purpose "renewal" (amount = the member's fee snapshot unless $opts['amount'] is given). The balance rises until that row is received.
     * Rules: the new term must end after the current expiry and in the future; an active member's new term cannot start before the current
     * expiry (it would shorten what was paid for); an expired member comes back only if a place is free. Errors: not_found, not_allowed,
     * no_term, bad_amount, bad_start, term_overlap, term_over, type_full.
     * $opts: user_id, start_date, amount, notes.
     * @return array{ok:bool,error:?string,message:string,member:?array,payment:?array}
     */
    public static function renew(int $memberId, array $opts = []): array
    {
        $fail = static fn(string $code, string $msg): array => ['ok' => false, 'error' => $code, 'message' => $msg, 'member' => null, 'payment' => null];
        $error = null;
        $payment = null;
        $member = Database::transaction(function () use ($memberId, $opts, &$error, &$payment): ?array {
            $m = Database::one('SELECT * FROM members WHERE id = ? FOR UPDATE', [$memberId]);
            if ($m === null) {
                $error = ['not_found', 'Member not found.'];
                return null;
            }
            if (!in_array($m['status'], ['approved', 'expired'], true)) {
                $error = ['not_allowed', 'Only approved or expired members can be renewed.'];
                return null;
            }
            if (!isset(self::PERIOD_MONTHS[(string) $m['fee_period']])) {
                $error = ['no_term', 'This membership type has no renewal term (one-time or lifetime).'];
                return null;
            }
            $today = date('Y-m-d');
            $current = !empty($m['expiry_date']) ? (string) $m['expiry_date'] : null;
            $active = $m['status'] === 'approved' && $current !== null && $current > $today;     // the paid term is still running
            $start = trim((string) ($opts['start_date'] ?? ''));
            if ($start === '') {
                $start = $active ? $current : $today;
            } elseif (!self::validTermDate($start)) {
                $error = ['bad_start', 'Use a real start date between ' . format_date(self::DATE_MIN) . ' and ' . self::DATE_MAX_YEARS . ' years from now.'];
                return null;
            }
            if ($active && $start < $current) {
                $error = ['term_overlap', 'This membership is paid until ' . format_date($current) . '. The new term must start on or after that date: leave the field empty to continue from it.'];
                return null;
            }
            $end = (string) self::computeExpiry($start, (string) $m['fee_period']);
            if ($end === '' || $end <= $today || ($m['status'] === 'approved' && $current !== null && $end <= $current)) {      // an expired member's earlier term was ended on purpose
                $error = ['term_over', 'That term would end on ' . ($end !== '' ? format_date($end) : 'an invalid date') . ', which is not later than today and the current expiry date. Choose a later start date.'];
                return null;
            }
            $amountRaw = $opts['amount'] ?? null;
            $amount = $amountRaw !== null && $amountRaw !== '' ? self::parseMoney((string) $amountRaw) : ($m['fee_amount'] ?? '0');
            if ($amount === null) {
                $error = ['bad_amount', 'The renewal amount is not valid.'];
                return null;
            }
            if ($m['status'] === 'expired' && !self::typeHasRoom((int) $m['type_id'], $memberId, self::CAPACITY_STATUSES)) {
                $error = ['type_full', 'This membership type has no places left.'];
                return null;
            }
            $set = ['expiry_date' => $end];
            if ($m['status'] === 'expired') {
                $set += ['status' => 'approved', 'status_changed_at' => date('Y-m-d H:i:s'), 'status_changed_by' => $opts['user_id'] ?? null, 'status_reason' => null];
            }
            Database::update('members', $set, 'id = ?', [$memberId]);
            $paymentId = Database::insert('member_payments', [
                'member_id'   => $memberId,
                'purpose'     => 'renewal',
                'amount'      => number_format((float) $amount, 2, '.', ''),
                'currency'    => (string) $m['fee_currency'],
                'method'      => '',
                'status'      => 'pending',
                'term_start'  => $start,
                'term_end'    => $end,
                'recorded_by' => $opts['user_id'] ?? null,
                'notes'       => isset($opts['notes']) && trim((string) $opts['notes']) !== '' ? mb_substr(trim((string) $opts['notes']), 0, 500) : null,
            ]);
            $payment = MemberPayment::find($paymentId);
            AuditLog::record('member.renew', 'member', $memberId, 'Renewed ' . $m['application_ref'] . ' until ' . $end,
                ['ref' => $m['application_ref'], 'term_start' => $start, 'term_end' => $end, 'payment_id' => $paymentId, 'expiry_was' => $current]);
            return Member::find($memberId);
        });
        if ($member === null) {
            return $fail($error[0] ?? 'failed', $error[1] ?? 'The membership could not be renewed.');
        }
        return ['ok' => true, 'error' => null, 'message' => 'Membership renewed until ' . format_date($member['expiry_date']) . '.', 'member' => $member, 'payment' => $payment];
    }

    /**
     * Mark every approved member whose expiry date has been reached as "expired" (idempotent; the admin list calls it on load).
     * Returns the number of members changed.
     */
    public static function syncExpired(): int
    {
        $n = Database::execute(
            "UPDATE members SET status = 'expired', status_reason = NULL, status_changed_at = NOW(), status_changed_by = NULL
             WHERE status = 'approved' AND expiry_date IS NOT NULL AND expiry_date <= CURDATE()"
        );
        if ($n > 0) {
            AuditLog::record('member.expire', 'member', null, $n . ' membership(s) reached their expiry date', ['count' => $n]);
            self::flush();
        }
        return $n;
    }

    // ------------------------------------------------------------------------------------------------ payments

    /** '50,000' / '1 500.5' / '1500.50' -> '1500.50'; null when it is not a plain non-negative amount (max 10 digits, 2 decimals, commas only as 3-digit groups). */
    public static function parseMoney(string $raw): ?string
    {
        $s = str_replace([' ', "\u{00A0}"], '', trim($raw));
        if (str_contains($s, ',')) {
            if (preg_match('/^\d{1,3}(,\d{3})+(\.\d{1,2})?$/', $s) !== 1) {
                return null;
            }
            $s = str_replace(',', '', $s);
        }
        if (preg_match('/^\d{1,10}(\.\d{1,2})?$/', $s) !== 1) {
            return null;
        }
        return number_format((float) $s, 2, '.', '');
    }

    /** Money as integer cents (no float drift when adding up). */
    public static function cents(float|int|string|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    /**
     * Where a member stands financially. Charges = joining fee + fee of the first term (from the member's fee snapshot) + every renewal row;
     * paid = rows with status received; waived = rows with status waived; balance = charges - paid - waived (negative = credit).
     * Only rows in the member's fee currency are added up; others are listed in 'other_currencies'.
     * status: tbd (no fee set and nothing paid), free (nothing to pay), unpaid, partial, paid.
     * @return array{currency:string,initial_due:float,renewal_due:float,charges:float,paid:float,waived:float,balance:float,status:string,other_currencies:array<string,float>,payments:array<int,array<string,mixed>>}
     */
    public static function paymentSummary(int|array $member): array
    {
        $m = is_array($member) ? $member : (Member::find($member) ?? []);
        $cur = (string) ($m['fee_currency'] ?? self::defaultCurrency());
        $rows = $m === [] ? [] : MemberPayment::where(['member_id' => (int) $m['id']], 'id ASC');
        $initial = self::cents($m['joining_fee'] ?? 0) + self::cents($m['fee_amount'] ?? 0);
        $renewal = $paid = $waived = 0;
        $other = [];
        foreach ($rows as $p) {
            $c = self::cents($p['amount']);
            if (strtoupper((string) $p['currency']) !== strtoupper($cur)) {
                if ($p['status'] === 'received') {
                    $other[(string) $p['currency']] = ($other[(string) $p['currency']] ?? 0) + $c / 100;
                }
                continue;
            }
            if ($p['purpose'] === 'renewal') {
                $renewal += $c;
            }
            if ($p['status'] === 'received') {
                $paid += $c;
            } elseif ($p['status'] === 'waived') {
                $waived += $c;
            }
        }
        $charges = $initial + $renewal;
        $balance = $charges - $paid - $waived;
        if ($charges === 0 && $paid === 0 && $waived === 0) {
            $status = ($m['fee_amount'] ?? null) === null && ($m['joining_fee'] ?? null) === null ? 'tbd' : 'free';
        } elseif ($balance <= 0) {
            $status = 'paid';
        } elseif ($paid + $waived > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }
        return [
            'currency' => $cur, 'initial_due' => (float) ($initial / 100), 'renewal_due' => (float) ($renewal / 100), 'charges' => (float) ($charges / 100),
            'paid' => (float) ($paid / 100), 'waived' => (float) ($waived / 100), 'balance' => (float) ($balance / 100), 'status' => $status,
            'other_currencies' => $other, 'payments' => $rows,
        ];
    }

    /**
     * SQL expressions for lists (filter by payment status, show the balance) that follow exactly the rules of paymentSummary().
     * Use inside a query over `members <alias>`; no user input is interpolated. Keys: charges, paid, waived, balance, status.
     * @return array<string,string>
     */
    public static function paymentSql(string $alias = 'm'): array
    {
        $a = preg_replace('/[^A-Za-z0-9_]/', '', $alias) ?: 'm';
        $sum = static fn(string $cond): string => "COALESCE((SELECT SUM(p.amount) FROM member_payments p WHERE p.member_id = {$a}.id AND p.currency = {$a}.fee_currency {$cond}), 0)";
        $charges = "(COALESCE({$a}.joining_fee, 0) + COALESCE({$a}.fee_amount, 0) + " . $sum("AND p.purpose = 'renewal'") . ')';
        $paid = $sum("AND p.status = 'received'");
        $waived = $sum("AND p.status = 'waived'");
        $balance = "({$charges} - {$paid} - {$waived})";
        $status = "CASE WHEN {$charges} = 0 AND {$paid} = 0 AND {$waived} = 0 THEN IF({$a}.fee_amount IS NULL AND {$a}.joining_fee IS NULL, 'tbd', 'free') "
            . "WHEN {$balance} <= 0 THEN 'paid' WHEN {$paid} + {$waived} > 0 THEN 'partial' ELSE 'unpaid' END";
        return ['charges' => $charges, 'paid' => $paid, 'waived' => $waived, 'balance' => $balance, 'status' => $status];
    }

    /**
     * Record a payment (or a waiver / an expected payment) for a member. $input: amount*, status* (received|pending|waived), method, reference,
     * paid_at (Y-m-d or Y-m-d H:i), purpose (joining|membership|other; "renewal" rows are created only by renew()), currency (default: the
     * member's), term_start, term_end, notes, user_id. A received payment gets its receipt number at once and must be more than zero and not
     * dated in the future. Payment methods are the editable list "payment_methods".
     * @return array{ok:bool,errors:array<string,string>,payment:?array,message:string}
     */
    public static function recordPayment(int $memberId, array $input): array
    {
        $m = Member::find($memberId);
        if ($m === null) {
            return ['ok' => false, 'errors' => ['member' => 'Member not found.'], 'payment' => null, 'message' => 'Member not found.'];
        }
        [$errors, $row] = self::validatePayment($input, $m);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'payment' => null, 'message' => 'Please check the payment details.'];
        }
        $row['member_id'] = $memberId;
        $row['recorded_by'] = isset($input['user_id']) ? (int) $input['user_id'] : null;
        $id = Database::insert('member_payments', $row);
        if ($row['status'] === 'received') {
            self::assignReceiptNumber($id);
        }
        $payment = MemberPayment::find($id);
        // amounts are not personal data: the log shows what was recorded (ids and references only otherwise)
        AuditLog::record('member.payment', 'member', $memberId, 'Recorded a ' . $row['status'] . ' payment on ' . $m['application_ref'],
            ['ref' => $m['application_ref'], 'payment_id' => $id, 'status' => $row['status'], 'receipt' => $payment['receipt_number'] ?? null,
             'amount' => $row['amount'], 'currency' => $row['currency'], 'purpose' => $row['purpose']]);
        return ['ok' => true, 'errors' => [], 'payment' => $payment, 'message' => $row['status'] === 'received' ? 'Payment recorded. Receipt ' . ($payment['receipt_number'] ?? '') . '.' : 'Payment saved.'];
    }

    /** Validate a payment exactly as recordPayment() would, without saving anything. @return array<string,string> field => message (empty = fine) */
    public static function checkPayment(int $memberId, array $input): array
    {
        $m = Member::find($memberId);
        if ($m === null) {
            return ['member' => 'Member not found.'];
        }
        return self::validatePayment($input, $m)[0];
    }

    /**
     * Change a payment row (same $input keys as recordPayment). Switching it to "received" issues the receipt number; an issued receipt
     * number never changes or disappears, and the audit entry records the amount, currency, status and purpose before and after.
     * @return array{ok:bool,errors:array<string,string>,payment:?array,message:string}
     */
    public static function updatePayment(int $paymentId, array $input): array
    {
        $p = MemberPayment::find($paymentId);
        $m = $p !== null ? Member::find((int) $p['member_id']) : null;
        if ($p === null || $m === null) {
            return ['ok' => false, 'errors' => ['payment' => 'Payment not found.'], 'payment' => null, 'message' => 'Payment not found.'];
        }
        [$errors, $row] = self::validatePayment($input + ['paid_at' => $p['paid_at'], 'term_start' => $p['term_start'], 'term_end' => $p['term_end']], $m, $p);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'payment' => null, 'message' => 'Please check the payment details.'];
        }
        Database::update('member_payments', $row, 'id = ?', [$paymentId]);
        if ($row['status'] === 'received') {
            self::assignReceiptNumber($paymentId);
        }
        $new = MemberPayment::find($paymentId);
        $moneyChanged = self::cents($p['amount']) !== self::cents($new['amount']) || $p['status'] !== $new['status'] || $p['currency'] !== $new['currency'] || $p['purpose'] !== $new['purpose'];
        $issued = !empty($p['receipt_number']);
        $details = ['ref' => $m['application_ref'], 'payment_id' => $paymentId, 'status' => $new['status'], 'receipt' => $new['receipt_number']];
        if ($moneyChanged) {
            $details += ['amount_was' => $p['amount'], 'amount_now' => $new['amount'], 'currency_was' => $p['currency'], 'currency_now' => $new['currency'],
                'status_was' => $p['status'], 'status_now' => $new['status'], 'purpose_was' => $p['purpose'], 'purpose_now' => $new['purpose']];
        }
        AuditLog::record('member.payment', 'member', (int) $m['id'], 'Updated a payment on ' . $m['application_ref']
            . ($moneyChanged && $issued ? ' (receipt ' . $p['receipt_number'] . ': ' . $p['status'] . ' ' . $p['amount'] . ' ' . $p['currency'] . ' -> ' . $new['status'] . ' ' . $new['amount'] . ' ' . $new['currency'] . ')' : ''),
            $details);
        return ['ok' => true, 'errors' => [], 'payment' => $new, 'message' => $moneyChanged && $issued ? 'Payment updated. Receipt ' . $p['receipt_number'] . ' was already issued: tell the member if they hold the old one.' : 'Payment updated.'];
    }

    /** A payment that has a receipt number can never be deleted (the number stays reserved). @return array{ok:bool,message:string} */
    public static function deletePayment(int $paymentId): array
    {
        $p = MemberPayment::find($paymentId);
        if ($p === null) {
            return ['ok' => false, 'message' => 'Payment not found.'];
        }
        if (!empty($p['receipt_number'])) {
            return ['ok' => false, 'message' => 'Receipt ' . $p['receipt_number'] . ' was issued, so this payment cannot be deleted. Change its status instead.'];
        }
        $m = Member::find((int) $p['member_id']);
        MemberPayment::delete($paymentId);
        AuditLog::record('member.payment_delete', 'member', (int) $p['member_id'], 'Deleted a payment row on ' . ($m['application_ref'] ?? ''),
            ['payment_id' => $paymentId, 'amount' => $p['amount'], 'currency' => $p['currency'], 'purpose' => $p['purpose'], 'status' => $p['status']]);
        return ['ok' => true, 'message' => 'Payment deleted.'];
    }

    /** A date typed by staff as a real calendar date (no roll-over) in one of the accepted shapes, else null. */
    private static function strictDateTime(string $raw): ?\DateTimeImmutable
    {
        $raw = trim(str_replace('T', ' ', $raw));
        foreach (['Y-m-d', 'Y-m-d H:i', 'Y-m-d H:i:s'] as $format) {
            $d = \DateTimeImmutable::createFromFormat('!' . $format, $raw);
            if ($d !== false && $d->format($format) === $raw) {
                return $d;
            }
        }
        return null;
    }

    /**
     * @param array<string,mixed>|null $stored the payment row when it is being updated (its own method stays valid, and its purpose rules apply)
     * @return array{0:array<string,string>,1:array<string,mixed>} [errors, database row]
     */
    private static function validatePayment(array $input, array $member, ?array $stored = null): array
    {
        $methods = array_keys(self::options('payment_methods'));
        // a method the owner removed from the list later stays valid for the payment that already uses it (and for the member's own choice)
        $methods[] = (string) ($stored['method'] ?? $member['payment_method'] ?? '');
        $v = Validator::make($input, [
            'amount'     => 'required',
            'status'     => 'required|in:received,pending,waived',
            'purpose'    => 'nullable|in:' . implode(',', MemberPayment::PURPOSES),
            'method'     => 'nullable|max:60',
            'reference'  => 'nullable|max:120',
            'currency'   => 'nullable|regex:/^[A-Za-z]{3}$/',
            'paid_at'    => 'nullable|max:20',
            'term_start' => 'nullable|date',
            'term_end'   => 'nullable|date',
            'notes'      => 'nullable|max:500',
        ], [
            'amount.required' => 'Enter the amount.', 'status.required' => 'Choose a status.', 'status.in' => 'Choose a valid status.',
            'currency.regex' => 'Use a three-letter currency code.', 'term_start.date' => 'Use the format YYYY-MM-DD.', 'term_end.date' => 'Use the format YYYY-MM-DD.',
        ]);
        $errors = $v->errors();
        $amount = self::parseMoney(is_scalar($input['amount'] ?? null) ? (string) $input['amount'] : '');
        if (!isset($errors['amount']) && $amount === null) {
            $errors['amount'] = 'Enter a plain amount such as 50000 or 1500.50.';
        }
        $status = is_scalar($input['status'] ?? null) ? (string) $input['status'] : '';
        if (!isset($errors['amount']) && $status === 'received' && (float) $amount <= 0) {
            $errors['amount'] = 'A received payment must be more than zero. Use "Waived" to clear a charge without money.';
        }
        $method = is_scalar($input['method'] ?? null) ? trim((string) $input['method']) : '';
        if (!isset($errors['method']) && $method !== '' && !in_array($method, $methods, true)) {
            $errors['method'] = 'Choose one of the listed payment methods.';
        }
        // renewal rows are the charges added by "Start a new term": money that arrives is recorded as a membership fee, otherwise it would be counted as a second charge
        $purpose = is_scalar($input['purpose'] ?? null) && trim((string) $input['purpose']) !== '' ? trim((string) $input['purpose']) : 'membership';
        $wasRenewal = $stored !== null && ($stored['purpose'] ?? '') === 'renewal';
        if (!isset($errors['purpose'])) {
            if ($purpose === 'renewal' && !$wasRenewal) {
                $errors['purpose'] = 'Renewal charges are added by "Start a new term". Record the money you receive as "Membership fee", or mark the pending renewal row as received.';
            } elseif ($wasRenewal && $purpose !== 'renewal') {
                $errors['purpose'] = 'This row is a renewal charge, so its purpose cannot be changed. Delete it (while it is pending) if it was added by mistake.';
            }
        }
        $paidAt = is_scalar($input['paid_at'] ?? null) ? trim((string) $input['paid_at']) : '';
        $paidSql = null;
        if ($paidAt !== '') {
            $dt = self::strictDateTime($paidAt);
            if ($dt === null) {
                $errors['paid_at'] = 'Use a real date in the format YYYY-MM-DD.';
            } elseif ($dt->format('Y-m-d') < self::DATE_MIN) {
                $errors['paid_at'] = 'That date is too far in the past.';
            } elseif ($status === 'received' && $dt->format('Y-m-d') > date('Y-m-d')) {
                $errors['paid_at'] = 'A received payment cannot be dated in the future. Use "Pending" for an expected payment.';
            } elseif ($dt->format('Y-m-d') > date('Y-m-d', strtotime('+' . self::DATE_MAX_YEARS . ' years') ?: time())) {
                $errors['paid_at'] = 'That date is too far in the future.';
            } else {
                $paidSql = $dt->format('Y-m-d H:i:s');
            }
        }
        $termStart = is_scalar($input['term_start'] ?? null) ? trim((string) $input['term_start']) : '';
        $termEnd = is_scalar($input['term_end'] ?? null) ? trim((string) $input['term_end']) : '';
        foreach (['term_start' => $termStart, 'term_end' => $termEnd] as $field => $value) {
            if ($value !== '' && !isset($errors[$field]) && !self::validTermDate($value)) {
                $errors[$field] = 'Use a real date between ' . format_date(self::DATE_MIN) . ' and ' . self::DATE_MAX_YEARS . ' years from now.';
            }
        }
        if ($termStart !== '' && $termEnd !== '' && !isset($errors['term_start']) && !isset($errors['term_end']) && $termEnd < $termStart) {
            $errors['term_end'] = 'The end of the period cannot be before its start.';
        }
        if ($errors !== []) {
            return [$errors, []];
        }
        if ($status === 'received' && $paidSql === null) {
            $paidSql = date('Y-m-d H:i:s');
        }
        $str = static fn(string $k): string => is_scalar($input[$k] ?? null) ? trim((string) $input[$k]) : '';
        return [[], [
            'purpose'    => $purpose,
            'amount'     => $amount,
            'currency'   => strtoupper($str('currency') !== '' ? $str('currency') : (string) $member['fee_currency']),
            'method'     => $method,
            'reference'  => $str('reference') !== '' ? $str('reference') : null,
            'status'     => $status,
            'paid_at'    => $paidSql,
            'term_start' => $termStart !== '' ? $termStart : null,
            'term_end'   => $termEnd !== '' ? $termEnd : null,
            'notes'      => $str('notes') !== '' ? $str('notes') : null,
        ]];
    }

    /** Everything a printable receipt needs, or null when the payment has no receipt (not received). @return array{payment:array,member:array,summary:array}|null */
    public static function receiptData(int $paymentId): ?array
    {
        $p = MemberPayment::find($paymentId);
        if ($p === null || $p['status'] !== 'received' || empty($p['receipt_number'])) {
            return null;
        }
        $m = Member::find((int) $p['member_id']);
        return $m === null ? null : ['payment' => $p, 'member' => $m, 'summary' => self::paymentSummary($m)];
    }

    // ------------------------------------------------------------------------------------------------ membership types (the fee configuration)

    /** Normalise the money fields of a type form: "50,000" -> "50000.00", '' stays '' (empty = fee to be announced). */
    private static function typeMoney(mixed $raw): array
    {
        $s = trim((string) $raw);
        if ($s === '') {
            return [null, null];
        }
        $n = self::parseMoney($s);
        return $n === null ? [null, 'Enter a plain amount such as 50000 or 1500.50, or leave it empty for "fee to be announced".'] : [$n, null];
    }

    /**
     * Create ($id = null) or update a membership type from form input. Keys: name*, slug, description, fee_amount ('' = fee to be announced,
     * 0 = free), joining_fee, currency, period*, eligibility, sort_order, is_active, max_members. Changing a fee only affects NEW applications.
     * @return array{ok:bool,errors:array<string,string>,id:?int,type:?array}
     */
    public static function saveType(?int $id, array $input): array
    {
        $existing = $id !== null ? MembershipType::find($id) : null;
        if ($id !== null && $existing === null) {
            return ['ok' => false, 'errors' => ['name' => 'Membership type not found.'], 'id' => null, 'type' => null];
        }
        $v = Validator::make($input, [
            'name'        => 'required|min:2|max:150',
            'slug'        => 'nullable|max:120',
            'description' => 'nullable|max:3000',
            'eligibility' => 'nullable|max:1000',
            'currency'    => 'nullable|regex:/^[A-Za-z]{3}$/',
            'period'      => 'required|in:' . implode(',', self::PERIODS),
            'sort_order'  => 'nullable|integer|between:-100000,100000',
            'max_members' => 'nullable|integer|between:1,10000000',
        ], [
            'name.required' => 'Enter a name for the membership type.', 'period.required' => 'Choose a billing period.', 'period.in' => 'Choose a billing period.',
            'currency.regex' => 'Use a three-letter currency code such as UGX.', 'max_members.integer' => 'Enter a whole number, or leave it empty for no limit.',
            'max_members.between' => 'Enter a whole number of 1 or more, or leave it empty for no limit.',
        ], ['name' => 'Name']);
        $errors = $v->errors();
        [$fee, $feeErr] = self::typeMoney($input['fee_amount'] ?? '');
        [$join, $joinErr] = self::typeMoney($input['joining_fee'] ?? '');
        if ($feeErr !== null) {
            $errors['fee_amount'] = $feeErr;
        }
        if ($joinErr !== null) {
            $errors['joining_fee'] = $joinErr;
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'id' => null, 'type' => null];
        }
        // an edit without a slug keeps the existing one (a renamed type must not change its slug silently); a new type derives it from the name
        $slugSource = trim((string) ($input['slug'] ?? '')) !== '' ? (string) $input['slug'] : ($existing !== null ? (string) $existing['slug'] : (string) $input['name']);
        $row = [
            'slug'        => Slug::unique('membership_types', $slugSource, $id, 'slug', [], 'membership'),
            'name'        => trim((string) preg_replace('/\s+/u', ' ', (string) $input['name'])),
            'description' => trim((string) ($input['description'] ?? '')) !== '' ? trim((string) $input['description']) : null,
            'fee_amount'  => $fee,
            'joining_fee' => $join,
            'currency'    => trim((string) ($input['currency'] ?? '')) !== '' ? strtoupper(trim((string) $input['currency'])) : self::defaultCurrency(),
            'period'      => (string) $input['period'],
            'eligibility' => trim((string) ($input['eligibility'] ?? '')) !== '' ? trim((string) $input['eligibility']) : null,
            'sort_order'  => (int) ($input['sort_order'] ?? 0),
            'is_active'   => !empty($input['is_active']) && (string) $input['is_active'] !== '0' ? 1 : 0,
            'max_members' => trim((string) ($input['max_members'] ?? '')) !== '' ? (int) $input['max_members'] : null,
        ];
        if ($id === null) {
            $id = MembershipType::create($row);
            $verb = 'created';
        } else {
            MembershipType::update($id, $row);
            $verb = 'updated';
        }
        $feeChanged = $existing !== null && (($existing['fee_amount'] ?? null) !== $row['fee_amount'] || ($existing['joining_fee'] ?? null) !== $row['joining_fee']
            || $existing['currency'] !== $row['currency'] || $existing['period'] !== $row['period']);
        AuditLog::record('membership_type.' . ($verb === 'created' ? 'create' : 'update'), 'membership_type', $id,
            ucfirst($verb) . ' membership type "' . $row['name'] . '"' . ($feeChanged ? ' (fee settings changed: applies to new applications only)' : ''),
            ['fee_changed' => $feeChanged, 'active' => $row['is_active']]);
        return ['ok' => true, 'errors' => [], 'id' => $id, 'type' => MembershipType::find($id)];
    }

    /** Delete a type only when no member uses it (deactivate instead). @return array{ok:bool,message:string} */
    public static function deleteType(int $id): array
    {
        $type = MembershipType::find($id);
        if ($type === null) {
            return ['ok' => false, 'message' => 'Membership type not found.'];
        }
        $n = (int) Database::value('SELECT COUNT(*) FROM members WHERE type_id = ?', [$id], 0);
        if ($n > 0) {
            return ['ok' => false, 'message' => 'This type has ' . $n . ' member' . ($n === 1 ? '' : 's') . ' and cannot be deleted. Deactivate it instead: it then disappears from the website and the form.'];
        }
        MembershipType::delete($id);
        AuditLog::record('membership_type.delete', 'membership_type', $id, 'Deleted membership type "' . $type['name'] . '"');
        return ['ok' => true, 'message' => 'Membership type deleted.'];
    }

    // ------------------------------------------------------------------------------------------------ counters

    /** Applications waiting for review (sidebar badge, dashboard card). Memoised per request. */
    public static function pendingCount(): int
    {
        if (self::$pending === null) {
            try {
                self::$pending = (int) Database::value("SELECT COUNT(*) FROM members WHERE status = 'pending'", [], 0);
            } catch (\Throwable) {
                self::$pending = 0;
            }
        }
        return self::$pending;
    }

    /** Dashboard card (same shape as the tiles of DashboardController) or null when the user may not manage members. @return array<string,mixed>|null */
    public static function dashboardTile(): ?array
    {
        if (!can('members.manage')) {
            return null;
        }
        $n = self::pendingCount();
        return ['icon' => 'user-plus', 'value' => number_format($n), 'label' => 'Pending membership applications', 'url' => 'admin/members?status=pending', 'alert' => $n > 0];
    }

    public static function flush(): void
    {
        self::$pending = null;
    }

    /** @param list<string> $values SQL list of quoted constants (internal constants only) */
    private static function in(array $values): string
    {
        return implode(',', array_map(static fn(string $v): string => "'" . preg_replace('/[^a-z_]/', '', $v) . "'", $values));
    }
}
