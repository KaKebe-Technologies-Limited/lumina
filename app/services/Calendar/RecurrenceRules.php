<?php
declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * Validation, normalisation, description and expansion of the repeat rule of a calendar entry.
 * Pure PHP: no database, no DateTime, no intl. Contract: docs/CALENDAR.md section 3 (rule JSON schema).
 *
 * An entry carries `recurrence` (enum) + `recurrence_data` (JSON) + `repeat_until` (column) + `start_date` / `end_date`.
 *   start_date  first day of the first occurrence. For the rule types nth_weekday, easter and islamic it is only a LOWER BOUND
 *               (no occurrence before it).
 *   end_date    last day of a multi-day entry (NULL = one day); every occurrence has the same length.
 *   repeat_until  last day an occurrence may START. A copy in recurrence_data.until is accepted and moved here by normalise().
 *
 * Weekdays are ISO 1..7 (Monday..Sunday). `nth` is 1..5 or -1 (last).
 */
final class RecurrenceRules
{
    public const TYPES = ['none', 'daily', 'weekly', 'monthly', 'yearly', 'nth_weekday', 'easter', 'islamic'];
    public const MAX_INTERVAL = 99;
    public const MAX_DURATION_DAYS = 366;
    public const MAX_PER_ENTRY = 3000;
    public const MAX_JSON_BYTES = 1000;

    /** keys allowed in recurrence_data per type (until is accepted everywhere except 'none') */
    private const KEYS = [
        'none'        => [],
        'daily'       => ['interval', 'until'],
        'weekly'      => ['interval', 'weekdays', 'until'],
        'monthly'     => ['interval', 'mode', 'nth', 'weekday', 'until'],
        'yearly'      => ['interval', 'until'],
        'nth_weekday' => ['interval', 'month', 'weekday', 'nth', 'from_day', 'until'],
        'easter'      => ['easter_offset', 'until'],
        'islamic'     => ['islamic', 'until'],
    ];

    /** Tolerant JSON / array reader: never throws, always an array. */
    public static function decode(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }
        if (!is_string($data) || trim($data) === '') {
            return [];
        }
        $v = json_decode($data, true);
        return is_array($v) && ($v === [] || !array_is_list($v)) ? $v : [];
    }

    /** Compact JSON for the column (NULL when there is nothing to store). */
    public static function encode(array $data): ?string
    {
        return $data === [] ? null : json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Validate and normalise the schedule fields of an entry.
     *
     * @param array{recurrence?:mixed,recurrence_data?:mixed,start_date?:mixed,end_date?:mixed,repeat_until?:mixed} $in
     * @return array{ok:bool,errors:array<string,string>,recurrence:string,data:array,json:?string,repeat_until:?string,start_date:?string,end_date:?string}
     */
    public static function normalise(array $in): array
    {
        $errors = [];
        $rec = is_string($in['recurrence'] ?? null) && ($in['recurrence'] ?? '') !== '' ? (string) $in['recurrence'] : 'none';
        $start = $in['start_date'] ?? null;
        $end = $in['end_date'] ?? null;
        $until = $in['repeat_until'] ?? null;
        $raw = $in['recurrence_data'] ?? null;

        if (!in_array($rec, self::TYPES, true)) {
            $errors['recurrence'] = 'Unknown repeat type.';
            $rec = 'none';
        }
        if (!is_string($start) || !CivilDate::valid($start)) {
            $errors['start_date'] = 'Enter a valid start date (YYYY-MM-DD).';
            $start = null;
        }
        if ($end === '' || $end === null) {
            $end = null;
        } elseif (!is_string($end) || !CivilDate::valid($end)) {
            $errors['end_date'] = 'Enter a valid end date (YYYY-MM-DD).';
            $end = null;
        } elseif ($start !== null && $end < $start) {
            $errors['end_date'] = 'The end date cannot be before the start date.';
            $end = null;
        } elseif ($start !== null && $end === $start) {
            $end = null;
        } elseif ($start !== null && CivilDate::diffDays($start, $end) > self::MAX_DURATION_DAYS) {
            $errors['end_date'] = 'An entry can last at most ' . self::MAX_DURATION_DAYS . ' days.';
            $end = null;
        }
        if ($until === '' || $until === null) {
            $until = null;
        } elseif (!is_string($until) || !CivilDate::valid($until)) {
            $errors['repeat_until'] = 'Enter a valid "repeat until" date (YYYY-MM-DD).';
            $until = null;
        }

        // ---- the JSON itself
        $data = [];
        if (is_string($raw)) {
            if (strlen($raw) > self::MAX_JSON_BYTES) {
                $errors['recurrence_data'] = 'The repeat settings are too long.';
                $raw = [];
            } elseif (trim($raw) === '') {
                $raw = [];
            } else {
                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    $errors['recurrence_data'] = 'The repeat settings are not valid JSON.';
                    $decoded = [];
                }
                $raw = $decoded;
            }
        } elseif ($raw === null) {
            $raw = [];
        } elseif (!is_array($raw)) {
            $errors['recurrence_data'] = 'The repeat settings must be an object.';
            $raw = [];
        }
        if ($raw !== [] && array_is_list($raw)) {
            $errors['recurrence_data'] = 'The repeat settings must be an object, not a list.';
            $raw = [];
        }

        $allowed = self::KEYS[$rec];
        foreach (array_keys($raw) as $k) {
            if (!is_string($k) || !in_array($k, $allowed, true)) {
                $errors['recurrence_data'] = 'Unknown repeat setting "' . (is_string($k) ? mb_substr($k, 0, 30) : (string) $k) . '" for this repeat type.';
                break;
            }
        }
        if (!isset($errors['recurrence_data'])) {
            self::readKeys($rec, $raw, $start, $data, $errors);
        }

        // an occurrence must be shorter than the gap to the next one (a "daily" entry lasting a year would be 365 overlapping copies)
        if ($rec !== 'none' && $end !== null && $start !== null && !isset($errors['recurrence_data']) && $data !== []) {
            $gap = self::minGapDays($rec, $data);
            if (CivilDate::diffDays($start, $end) >= $gap) {
                $errors['end_date'] = 'This entry lasts longer than the time between repeats (' . $gap . ' day' . ($gap === 1 ? '' : 's')
                    . '). Shorten it, or repeat it less often.';
                $end = null;
            }
        }

        // ---- until: column and JSON copy -> the earlier one, stored in the column
        $dataUntil = $raw['until'] ?? null;
        if ($dataUntil !== null && $dataUntil !== '') {
            if (!is_string($dataUntil) || !CivilDate::valid($dataUntil)) {
                $errors['recurrence_data'] ??= 'The "until" date in the repeat settings is not valid.';
            } else {
                $until = ($until === null || $dataUntil < $until) ? $dataUntil : $until;
            }
        }
        if ($until !== null && $start !== null && $until < $start) {
            $errors['repeat_until'] = 'The "repeat until" date cannot be before the start date.';
            $until = null;
        }
        if ($rec === 'none') {
            $until = null;
        }

        return [
            'ok'           => $errors === [],
            'errors'       => $errors,
            'recurrence'   => $rec,
            'data'         => $data,
            'json'         => self::encode($data),
            'repeat_until' => $until,
            'start_date'   => $start,
            'end_date'     => $end,
        ];
    }

    /** Type-specific keys: range checks and defaults (taken from the start date where that is natural). */
    private static function readKeys(string $rec, array $raw, ?string $start, array &$data, array &$errors): void
    {
        $int = static function (mixed $v, int $min, int $max): ?int {
            if (is_int($v)) {
                $n = $v;
            } elseif (is_string($v) && preg_match('/^-?\d{1,6}$/', trim($v))) {
                $n = (int) trim($v);
            } else {
                return null;
            }
            return ($n >= $min && $n <= $max) ? $n : null;
        };
        $bad = static function (string $msg) use (&$errors): void {
            $errors['recurrence_data'] ??= $msg;
        };
        $sp = $start !== null ? CivilDate::parse($start) : null;
        $startWeekday = $start !== null ? CivilDate::weekday($start) : 1;
        $startNth = $sp !== null ? (int) ceil($sp[2] / 7) : 1;

        if (in_array($rec, ['daily', 'weekly', 'monthly', 'yearly', 'nth_weekday'], true)) {
            if (array_key_exists('interval', $raw)) {
                $v = $int($raw['interval'], 1, self::MAX_INTERVAL);
                if ($v === null) {
                    $bad('"Every N" must be a whole number from 1 to ' . self::MAX_INTERVAL . '.');
                } else {
                    $data['interval'] = $v;
                }
            } else {
                $data['interval'] = 1;
            }
        }

        switch ($rec) {
            case 'weekly':
                if (array_key_exists('weekdays', $raw)) {
                    $list = $raw['weekdays'];
                    $out = [];
                    if (!is_array($list) || !array_is_list($list) || $list === [] || count($list) > 7) {
                        $bad('Choose at least one weekday (1 = Monday ... 7 = Sunday).');
                    } else {
                        foreach ($list as $w) {
                            $n = $int($w, 1, 7);
                            if ($n === null) {
                                $bad('Weekdays must be numbers from 1 (Monday) to 7 (Sunday).');
                                break;
                            }
                            $out[$n] = $n;
                        }
                        ksort($out);
                        $data['weekdays'] = array_values($out);
                    }
                } else {
                    $data['weekdays'] = [$startWeekday];
                }
                break;

            case 'monthly':
                $mode = $raw['mode'] ?? 'day';
                if (!in_array($mode, ['day', 'nth_weekday'], true)) {
                    $bad('Monthly mode must be "day" or "nth_weekday".');
                    $mode = 'day';
                }
                $data['mode'] = $mode;
                if ($mode === 'nth_weekday') {
                    $nth = array_key_exists('nth', $raw) ? $int($raw['nth'], -1, 5) : $startNth;
                    $wd = array_key_exists('weekday', $raw) ? $int($raw['weekday'], 1, 7) : $startWeekday;
                    if ($nth === null || $nth === 0) {
                        $bad('"nth" must be 1 to 5 or -1 (last).');
                    } else {
                        $data['nth'] = $nth;
                    }
                    if ($wd === null) {
                        $bad('"weekday" must be 1 (Monday) to 7 (Sunday).');
                    } else {
                        $data['weekday'] = $wd;
                    }
                } elseif (array_key_exists('nth', $raw) || array_key_exists('weekday', $raw)) {
                    $bad('"nth" and "weekday" only apply to the nth-weekday monthly mode.');
                }
                break;

            case 'nth_weekday':
                $month = array_key_exists('month', $raw) ? $int($raw['month'], 1, 12) : ($sp[1] ?? null);
                $wd = array_key_exists('weekday', $raw) ? $int($raw['weekday'], 1, 7) : $startWeekday;
                $nth = array_key_exists('nth', $raw) ? $int($raw['nth'], -1, 5) : $startNth;
                if ($month === null) {
                    $bad('"month" must be 1 to 12.');
                } else {
                    $data['month'] = $month;
                }
                if ($wd === null) {
                    $bad('"weekday" must be 1 (Monday) to 7 (Sunday).');
                } else {
                    $data['weekday'] = $wd;
                }
                if ($nth === null || $nth === 0) {
                    $bad('"nth" must be 1 to 5 or -1 (last).');
                } else {
                    $data['nth'] = $nth;
                }
                // from_day: "the first <weekday> on or after this day of the month" (5 = the weekday between the 5th and the 11th, which is
                // how Safer Internet Day is set); only with nth = 1
                if (array_key_exists('from_day', $raw)) {
                    $fd = $int($raw['from_day'], 2, 22);
                    if ($fd === null || ($data['nth'] ?? 1) !== 1) {
                        $bad('"from_day" must be 2 to 22 and only applies to the 1st weekday.');
                    } else {
                        $data['from_day'] = $fd;
                    }
                }
                break;

            case 'easter':
                if (!array_key_exists('easter_offset', $raw)) {
                    $data['easter_offset'] = 0;
                } else {
                    $off = $int($raw['easter_offset'], -366, 366);
                    if ($off === null) {
                        $bad('"easter_offset" must be a whole number of days from -366 to 366.');
                    } else {
                        $data['easter_offset'] = $off;
                    }
                }
                break;

            case 'islamic':
                $key = $raw['islamic'] ?? null;
                if (!is_string($key) || !IslamicCalendar::knownKey($key)) {
                    $bad('Choose an Islamic holiday: ' . implode(', ', array_keys(IslamicCalendar::HOLIDAYS)) . '.');
                } else {
                    $data['islamic'] = $key;
                }
                break;
        }
    }

    /** The until date that applies to an entry row (column and JSON copy, earlier wins) or null. */
    public static function effectiveUntil(array $entry): ?string
    {
        $until = isset($entry['repeat_until']) && is_string($entry['repeat_until']) && CivilDate::valid($entry['repeat_until']) ? $entry['repeat_until'] : null;
        $d = self::decode($entry['recurrence_data'] ?? null)['until'] ?? null;
        if (is_string($d) && CivilDate::valid($d) && ($until === null || $d < $until)) {
            $until = $d;
        }
        return $until;
    }

    /** Length of an entry in days minus one (0 = one day). */
    public static function duration(array $entry): int
    {
        $s = $entry['start_date'] ?? null;
        $e = $entry['end_date'] ?? null;
        if (!is_string($s) || !is_string($e) || !CivilDate::valid($s) || !CivilDate::valid($e) || $e <= $s) {
            return 0;
        }
        return min(self::MAX_DURATION_DAYS, CivilDate::diffDays($s, $e));
    }

    /**
     * Rule dates (the days on which an occurrence STARTS, before any override) inside [$from, $to], ascending.
     * Invalid stored rules yield no occurrences (never an exception): the admin forms validate with normalise() before saving.
     *
     * @param array $entry row with start_date, recurrence, recurrence_data, repeat_until
     * @return list<string>
     */
    public static function starts(array $entry, string $from, string $to): array
    {
        $start = $entry['start_date'] ?? null;
        if (!is_string($start) || !CivilDate::valid($start) || !CivilDate::valid($from) || !CivilDate::valid($to) || $from > $to) {
            return [];
        }
        $rec = (string) ($entry['recurrence'] ?? 'none');
        if ($rec === 'none') {
            return ($start >= $from && $start <= $to) ? [$start] : [];
        }
        $norm = self::normalise([
            'recurrence' => $rec, 'recurrence_data' => $entry['recurrence_data'] ?? null, 'start_date' => $start,
            'repeat_until' => $entry['repeat_until'] ?? null,
        ]);
        if (!$norm['ok']) {
            return [];
        }
        $d = $norm['data'];
        $lo = max($from, $start);
        $hi = $norm['repeat_until'] !== null ? min($to, $norm['repeat_until']) : $to;
        if ($lo > $hi) {
            return [];
        }
        $loDays = CivilDate::days($lo);
        $hiDays = CivilDate::days($hi);
        $startDays = CivilDate::days($start);
        [$sy, $sm, $sd] = CivilDate::parse($start);
        $out = [];
        $n = (int) ($d['interval'] ?? 1);

        switch ($rec) {
            case 'daily':
                $k = max(0, (int) ceil(($loDays - $startDays) / $n));
                for ($day = $startDays + $k * $n; $day <= $hiDays && count($out) < self::MAX_PER_ENTRY; $day += $n) {
                    $out[] = CivilDate::ymd($day);
                }
                break;

            case 'weekly':
                $set = array_flip($d['weekdays']);
                $mondayDays = $startDays - (CivilDate::weekdayOfDays($startDays) - 1);
                for ($day = $loDays; $day <= $hiDays && count($out) < self::MAX_PER_ENTRY; $day++) {
                    if (isset($set[CivilDate::weekdayOfDays($day)]) && intdiv($day - $mondayDays, 7) % $n === 0) {
                        $out[] = CivilDate::ymd($day);
                    }
                }
                break;

            case 'monthly':
                [$ly, $lm] = CivilDate::fromDays($loDays);
                [$hy, $hm] = CivilDate::fromDays($hiDays);
                for ($i = $ly * 12 + $lm - 1; $i <= $hy * 12 + $hm - 1 && count($out) < self::MAX_PER_ENTRY; $i++) {
                    $y = intdiv($i, 12);
                    $m = $i % 12 + 1;
                    if (($i - ($sy * 12 + $sm - 1)) < 0 || ($i - ($sy * 12 + $sm - 1)) % $n !== 0) {
                        continue;
                    }
                    $date = ($d['mode'] ?? 'day') === 'nth_weekday'
                        ? CivilDate::nthWeekday($y, $m, (int) $d['weekday'], (int) $d['nth'])
                        : CivilDate::format($y, $m, min($sd, CivilDate::daysInMonth($y, $m)));
                    if ($date !== null && $date >= $lo && $date <= $hi) {
                        $out[] = $date;
                    }
                }
                break;

            case 'yearly':
                for ($y = (int) substr($lo, 0, 4); $y <= (int) substr($hi, 0, 4) && $y <= CivilDate::MAX_YEAR; $y++) {
                    if (($y - $sy) < 0 || ($y - $sy) % $n !== 0) {
                        continue;
                    }
                    $date = CivilDate::format($y, $sm, min($sd, CivilDate::daysInMonth($y, $sm)));      // 29 Feb falls on 28 Feb in common years
                    if ($date >= $lo && $date <= $hi) {
                        $out[] = $date;
                    }
                }
                break;

            case 'nth_weekday':
                for ($y = (int) substr($lo, 0, 4); $y <= (int) substr($hi, 0, 4) && $y <= CivilDate::MAX_YEAR; $y++) {
                    if (($y - $sy) < 0 || ($y - $sy) % $n !== 0) {
                        continue;
                    }
                    $date = isset($d['from_day'])
                        ? self::weekdayFrom($y, (int) $d['month'], (int) $d['weekday'], (int) $d['from_day'])
                        : CivilDate::nthWeekday($y, (int) $d['month'], (int) $d['weekday'], (int) $d['nth']);
                    if ($date !== null && $date >= $lo && $date <= $hi) {
                        $out[] = $date;
                    }
                }
                break;

            case 'easter':
                $off = (int) $d['easter_offset'];
                for ($y = (int) substr($lo, 0, 4) - 1; $y <= (int) substr($hi, 0, 4) + 1; $y++) {
                    if ($y < CivilDate::MIN_YEAR + 1 || $y > CivilDate::MAX_YEAR - 1) {
                        continue;
                    }
                    $date = Easter::offset($y, $off);
                    if ($date >= $lo && $date <= $hi) {
                        $out[] = $date;
                    }
                }
                break;

            case 'islamic':
                $out = IslamicCalendar::datesInRange((string) $d['islamic'], $lo, $hi);
                break;
        }
        sort($out);
        return array_values(array_unique($out));
    }

    /** The first $weekday (ISO) on or after day $fromDay of the month. */
    private static function weekdayFrom(int $y, int $m, int $weekday, int $fromDay): string
    {
        $first = CivilDate::format($y, $m, $fromDay);
        return CivilDate::addDays($first, ($weekday - CivilDate::weekday($first) + 7) % 7);
    }

    /**
     * Smallest number of days between two STARTS of a normalised rule (a lower bound, never too high). An occurrence that lasts this long
     * or longer would overlap the next one, so normalise() refuses it (overlapping copies multiply every feed and page).
     */
    public static function minGapDays(string $rec, array $d): int
    {
        $n = max(1, (int) ($d['interval'] ?? 1));
        switch ($rec) {
            case 'daily':
                return $n;
            case 'weekly':
                $w = array_values($d['weekdays'] ?? [1]);
                sort($w);
                $gap = 7 * $n - ((int) end($w) - (int) $w[0]);
                for ($i = 1; $i < count($w); $i++) {
                    $gap = min($gap, (int) $w[$i] - (int) $w[$i - 1]);
                }
                return max(1, $gap);
            case 'monthly':
                return 28 * $n;
            case 'yearly':
                return 365 * $n;
            case 'nth_weekday':
                return 357 * $n;
            case 'easter':
                return 300;
            case 'islamic':
                return 340;
        }
        return PHP_INT_MAX;
    }

    /**
     * Human sentence for the admin ("Every year on 8 March"). Uses the normalised rule; invalid input gives a short fallback.
     */
    public static function describe(array $entry): string
    {
        $rec = (string) ($entry['recurrence'] ?? 'none');
        if ($rec === 'none') {
            return 'Does not repeat';
        }
        $start = (string) ($entry['start_date'] ?? '');
        $norm = self::normalise([
            'recurrence' => $rec, 'recurrence_data' => $entry['recurrence_data'] ?? null, 'start_date' => $start,
            'repeat_until' => $entry['repeat_until'] ?? null,
        ]);
        if (!$norm['ok']) {
            return 'Repeat settings need attention';
        }
        $d = $norm['data'];
        $n = (int) ($d['interval'] ?? 1);
        $every = static fn(string $unit): string => $n === 1 ? 'Every ' . $unit : 'Every ' . $n . ' ' . $unit . 's';
        $ord = static fn(int $nth): string => $nth === -1 ? 'last' : ['', '1st', '2nd', '3rd', '4th', '5th'][$nth];
        $s = match ($rec) {
            'daily' => $every('day'),
            'weekly' => $every('week') . ' on ' . implode(', ', array_map(static fn(int $w): string => CivilDate::WEEKDAYS[$w], $d['weekdays'])),
            'monthly' => ($d['mode'] ?? 'day') === 'nth_weekday'
                ? $every('month') . ' on the ' . $ord((int) $d['nth']) . ' ' . CivilDate::WEEKDAYS[(int) $d['weekday']]
                : $every('month') . ' on day ' . (CivilDate::parse($start)[2] ?? '?'),
            'yearly' => $every('year') . (CivilDate::valid($start) ? ' on ' . CivilDate::fmt($start, 'j F') : ''),
            'nth_weekday' => 'Every ' . ($n === 1 ? 'year' : $n . ' years') . ' on the '
                . (isset($d['from_day'])
                    ? CivilDate::WEEKDAYS[(int) $d['weekday']] . ' falling on day ' . (int) $d['from_day'] . ' to day ' . ((int) $d['from_day'] + 6)
                    : $ord((int) $d['nth']) . ' ' . CivilDate::WEEKDAYS[(int) $d['weekday']])
                . ' of ' . CivilDate::MONTHS[(int) $d['month']],
            'easter' => self::easterName((int) $d['easter_offset']),
            'islamic' => IslamicCalendar::HOLIDAYS[$d['islamic']][2] . ' (moon sighting: date to be confirmed)',
            default => '',
        };
        if ($norm['repeat_until'] !== null) {
            $s .= ', until ' . CivilDate::fmt($norm['repeat_until'], 'j F Y');
        }
        return $s;
    }

    private static function easterName(int $off): string
    {
        return match (true) {
            $off === 0 => 'Easter Sunday every year',
            $off === -2 => 'Good Friday (2 days before Easter Sunday) every year',
            $off === 1 => 'Easter Monday (the day after Easter Sunday) every year',
            $off < 0 => abs($off) . ' day' . (abs($off) === 1 ? '' : 's') . ' before Easter Sunday every year',
            default => $off . ' day' . ($off === 1 ? '' : 's') . ' after Easter Sunday every year',
        };
    }
}
