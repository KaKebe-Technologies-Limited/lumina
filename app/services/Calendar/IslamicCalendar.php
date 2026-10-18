<?php
declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * The ARITHMETICAL ("tabular") civil Islamic calendar (30-year cycle, epoch Friday 16 July 622 Julian = JDN 1948440).
 *
 * IMPORTANT: this is a mathematical approximation. Real Islamic months start with the sighting of the new moon and, in Uganda, with the
 * announcement of the Uganda Muslim Supreme Council / the government, so the real day of Eid al-Fitr or Eid al-Adha can differ from the
 * tabular day by one or two days. Every date produced here is therefore only a PREDICTION: the calendar flags it "date to be confirmed"
 * until an admin confirms it (calendar_overrides.confirmed = 1). Never present it as certain.
 */
final class IslamicCalendar
{
    /** Julian day number of 1970-01-01 (noon). */
    private const JDN_UNIX_EPOCH = 2440588;
    /** Julian day number of 1 Muharram 1 AH (civil / Friday epoch). */
    private const JDN_EPOCH = 1948440;

    /** key => [hijri month, hijri day, label] */
    public const HOLIDAYS = [
        'eid_al_fitr'      => [10, 1, 'Eid al-Fitr (1 Shawwal)'],
        'eid_al_adha'      => [12, 10, 'Eid al-Adha (10 Dhu al-Hijjah)'],
        'islamic_new_year' => [1, 1, 'Islamic New Year (1 Muharram)'],
        'mawlid'           => [3, 12, 'Mawlid (12 Rabi al-Awwal)'],
    ];

    public static function isLeap(int $hy): bool
    {
        return ((11 * $hy + 14) % 30) < 11;
    }

    public static function daysInMonth(int $hy, int $hm): int
    {
        if ($hm % 2 === 1) {
            return 30;
        }
        return ($hm === 12 && self::isLeap($hy)) ? 30 : 29;
    }

    private static function toJdn(int $hy, int $hm, int $hd): int
    {
        return $hd + (int) ceil(29.5 * ($hm - 1)) + ($hy - 1) * 354 + intdiv(3 + 11 * $hy, 30) + self::JDN_EPOCH - 1;
    }

    /** Hijri date to 'YYYY-MM-DD' (Gregorian). */
    public static function toGregorian(int $hy, int $hm, int $hd): string
    {
        return CivilDate::ymd(self::toJdn($hy, $hm, $hd) - self::JDN_UNIX_EPOCH);
    }

    /** @return array{0:int,1:int,2:int} [hijri year, month, day] of a Gregorian 'YYYY-MM-DD' */
    public static function fromGregorian(string $ymd): array
    {
        $jdn = CivilDate::days($ymd) + self::JDN_UNIX_EPOCH;
        $hy = intdiv(30 * ($jdn - self::JDN_EPOCH) + 10646, 10631);
        $hm = min(12, (int) ceil(($jdn - (29 + self::toJdn($hy, 1, 1))) / 29.5) + 1);
        $hd = $jdn - self::toJdn($hy, $hm, 1) + 1;
        return [$hy, $hm, $hd];
    }

    /**
     * Gregorian dates in [$from, $to] (inclusive) on which the named Islamic day falls, ascending. One Gregorian year can hold the same
     * Islamic date twice (the Islamic year is 354/355 days), so every Hijri year touching the range is tried.
     *
     * @return list<string>
     */
    public static function datesInRange(string $key, string $from, string $to): array
    {
        if (!isset(self::HOLIDAYS[$key])) {
            return [];
        }
        [$hm, $hd] = self::HOLIDAYS[$key];
        $y1 = (int) substr($from, 0, 4);
        $y2 = (int) substr($to, 0, 4);
        $hyFrom = (int) floor(($y1 - 622) * 1.030684) - 1;
        $hyTo = (int) ceil(($y2 - 622) * 1.030684) + 2;
        $out = [];
        for ($hy = max(1, $hyFrom); $hy <= $hyTo; $hy++) {
            $date = self::toGregorian($hy, $hm, $hd);
            if ($date >= $from && $date <= $to) {
                $out[] = $date;
            }
        }
        sort($out);
        return $out;
    }

    public static function knownKey(string $key): bool
    {
        return isset(self::HOLIDAYS[$key]);
    }
}
