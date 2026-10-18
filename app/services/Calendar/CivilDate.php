<?php
declare(strict_types=1);

namespace App\Services\Calendar;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Pure civil-date arithmetic on 'YYYY-MM-DD' strings (proleptic Gregorian). No DateTime, no time zone, no ext-intl / ext-calendar:
 * a date is converted to "days since 1970-01-01" (days_from_civil / civil_from_days), so adding days, differences, weekdays and
 * leap years are exact and DST-free. Africa/Kampala (EAT, UTC+3) has no daylight saving, which is why plain civil dates are all the
 * calendar needs; only today() looks at the clock.
 *
 * Weekdays are ISO: 1 = Monday ... 7 = Sunday.
 */
final class CivilDate
{
    public const MIN_YEAR = 1900;
    public const MAX_YEAR = 2200;
    public const TZ = 'Africa/Kampala';

    public const MONTHS = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    public const WEEKDAYS = [1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /** Fixed clock for tests (a 'Y-m-d' string); null = real clock. */
    private static ?string $fixedToday = null;

    public static function isLeap(int $y): bool
    {
        return ($y % 4 === 0 && $y % 100 !== 0) || $y % 400 === 0;
    }

    public static function daysInMonth(int $y, int $m): int
    {
        return match ($m) {
            2 => self::isLeap($y) ? 29 : 28,
            4, 6, 9, 11 => 30,
            default => 31,
        };
    }

    /** Strict 'YYYY-MM-DD' check (real calendar date, year within MIN_YEAR..MAX_YEAR). */
    public static function valid(mixed $ymd): bool
    {
        return self::parse($ymd) !== null;
    }

    /** @return array{0:int,1:int,2:int}|null [year, month, day] */
    public static function parse(mixed $ymd): ?array
    {
        if (!is_string($ymd) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
            return null;
        }
        $y = (int) $m[1];
        $mo = (int) $m[2];
        $d = (int) $m[3];
        if ($y < self::MIN_YEAR || $y > self::MAX_YEAR || $mo < 1 || $mo > 12 || $d < 1 || $d > self::daysInMonth($y, $mo)) {
            return null;
        }
        return [$y, $mo, $d];
    }

    /** 'YYYY-MM' check. */
    public static function validMonth(mixed $ym): bool
    {
        return is_string($ym) && preg_match('/^(\d{4})-(\d{2})$/', $ym, $m) === 1
            && (int) $m[1] >= self::MIN_YEAR && (int) $m[1] <= self::MAX_YEAR && (int) $m[2] >= 1 && (int) $m[2] <= 12;
    }

    /** Days since 1970-01-01 (negative before). */
    public static function toDays(int $y, int $m, int $d): int
    {
        $y -= $m <= 2 ? 1 : 0;
        $era = intdiv($y >= 0 ? $y : $y - 399, 400);
        $yoe = $y - $era * 400;
        $doy = intdiv(153 * ($m + ($m > 2 ? -3 : 9)) + 2, 5) + $d - 1;
        $doe = $yoe * 365 + intdiv($yoe, 4) - intdiv($yoe, 100) + $doy;
        return $era * 146097 + $doe - 719468;
    }

    /** @return array{0:int,1:int,2:int} */
    public static function fromDays(int $n): array
    {
        $n += 719468;
        $era = intdiv($n >= 0 ? $n : $n - 146096, 146097);
        $doe = $n - $era * 146097;
        $yoe = intdiv($doe - intdiv($doe, 1460) + intdiv($doe, 36524) - intdiv($doe, 146096), 365);
        $y = $yoe + $era * 400;
        $doy = $doe - (365 * $yoe + intdiv($yoe, 4) - intdiv($yoe, 100));
        $mp = intdiv(5 * $doy + 2, 153);
        $d = $doy - intdiv(153 * $mp + 2, 5) + 1;
        $m = $mp + ($mp < 10 ? 3 : -9);
        return [$y + ($m <= 2 ? 1 : 0), $m, $d];
    }

    public static function format(int $y, int $m, int $d): string
    {
        return sprintf('%04d-%02d-%02d', $y, $m, $d);
    }

    /** Days-since-epoch to 'YYYY-MM-DD'. */
    public static function ymd(int $days): string
    {
        [$y, $m, $d] = self::fromDays($days);
        return self::format($y, $m, $d);
    }

    /** 'YYYY-MM-DD' to days-since-epoch. @throws InvalidArgumentException on an invalid date */
    public static function days(string $ymd): int
    {
        $p = self::parse($ymd);
        if ($p === null) {
            throw new InvalidArgumentException('Invalid date: ' . $ymd);
        }
        return self::toDays($p[0], $p[1], $p[2]);
    }

    public static function addDays(string $ymd, int $n): string
    {
        return self::ymd(self::days($ymd) + $n);
    }

    /** $b - $a in days. */
    public static function diffDays(string $a, string $b): int
    {
        return self::days($b) - self::days($a);
    }

    /** ISO weekday (1 Monday ... 7 Sunday) of a days-since-epoch number (1970-01-01 was a Thursday). */
    public static function weekdayOfDays(int $days): int
    {
        return (($days % 7) + 7 + 3) % 7 + 1;
    }

    public static function weekday(string $ymd): int
    {
        return self::weekdayOfDays(self::days($ymd));
    }

    /**
     * The nth given weekday of a month. $nth = 1..5 (the 5th does not exist in every month: null) or -1 = the last one.
     * $weekday is ISO (1..7).
     */
    public static function nthWeekday(int $y, int $m, int $weekday, int $nth): ?string
    {
        if ($weekday < 1 || $weekday > 7 || $m < 1 || $m > 12) {
            return null;
        }
        $dim = self::daysInMonth($y, $m);
        if ($nth === -1) {
            $last = self::toDays($y, $m, $dim);
            $back = (self::weekdayOfDays($last) - $weekday + 7) % 7;
            return self::ymd($last - $back);
        }
        if ($nth < 1 || $nth > 5) {
            return null;
        }
        $first = self::toDays($y, $m, 1);
        $fwd = ($weekday - self::weekdayOfDays($first) + 7) % 7;
        $day = 1 + $fwd + ($nth - 1) * 7;
        return $day > $dim ? null : self::format($y, $m, $day);
    }

    /** First day of the month of $ymd or of 'YYYY-MM'. */
    public static function monthStart(string $ymOrYmd): string
    {
        return substr($ymOrYmd, 0, 7) . '-01';
    }

    public static function monthEnd(string $ymOrYmd): string
    {
        $y = (int) substr($ymOrYmd, 0, 4);
        $m = (int) substr($ymOrYmd, 5, 2);
        return self::format($y, $m, self::daysInMonth($y, $m));
    }

    /** 'YYYY-MM' shifted by $n months. */
    public static function addMonths(string $ym, int $n): string
    {
        $y = (int) substr($ym, 0, 4);
        $m = (int) substr($ym, 5, 2) - 1 + $n;
        $y += (int) floor($m / 12);
        $m = (($m % 12) + 12) % 12;
        return sprintf('%04d-%02d', $y, $m + 1);
    }

    /** First day of the week containing $ymd. $weekStart is ISO (1 Monday, 7 Sunday, 6 Saturday). */
    public static function weekStartOf(string $ymd, int $weekStart = 1): string
    {
        $back = (self::weekday($ymd) - $weekStart + 7) % 7;
        return self::addDays($ymd, -$back);
    }

    /**
     * Small date formatter. Tokens: d j (day), n m (month number), M F (short/long month name), Y y (year), l D (long/short weekday),
     * N (ISO weekday). Other characters are copied; escape a letter with a backslash.
     */
    public static function fmt(string $ymd, string $pattern = 'j F Y'): string
    {
        [$y, $m, $d] = self::parse($ymd) ?? throw new InvalidArgumentException('Invalid date: ' . $ymd);
        $wd = self::weekday($ymd);
        $out = '';
        $len = strlen($pattern);
        for ($i = 0; $i < $len; $i++) {
            $c = $pattern[$i];
            if ($c === '\\' && $i + 1 < $len) {
                $out .= $pattern[++$i];
                continue;
            }
            $out .= match ($c) {
                'd' => sprintf('%02d', $d),
                'j' => (string) $d,
                'm' => sprintf('%02d', $m),
                'n' => (string) $m,
                'M' => substr(self::MONTHS[$m], 0, 3),
                'F' => self::MONTHS[$m],
                'Y' => sprintf('%04d', $y),
                'y' => sprintf('%02d', $y % 100),
                'l' => self::WEEKDAYS[$wd],
                'D' => substr(self::WEEKDAYS[$wd], 0, 3),
                'N' => (string) $wd,
                default => $c,
            };
        }
        return $out;
    }

    /** Today in Africa/Kampala as 'YYYY-MM-DD' (honours setToday() for tests). */
    public static function today(): string
    {
        if (self::$fixedToday !== null) {
            return self::$fixedToday;
        }
        return (new DateTimeImmutable('now', new DateTimeZone(self::TZ)))->format('Y-m-d');
    }

    /** Tests only: pin "today" (null = real clock). */
    public static function setToday(?string $ymd): void
    {
        self::$fixedToday = $ymd;
    }

    /** Valid 'HH:MM' (24 h, also accepts HH:MM:SS) to 'HH:MM', else null. */
    public static function time(mixed $t): ?string
    {
        if (!is_string($t) || !preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/', trim($t), $m)) {
            return null;
        }
        return $m[1] . ':' . $m[2];
    }
}
