<?php
declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * Western (Gregorian) Easter Sunday by the Meeus/Jones/Butcher algorithm (valid for every Gregorian year). No ext-calendar needed.
 * Good Friday = offset -2, Easter Sunday 0, Easter Monday +1, Ascension +39, Pentecost +49.
 */
final class Easter
{
    /** Easter Sunday of a Gregorian year as 'YYYY-MM-DD'. */
    public static function sunday(int $year): string
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        return CivilDate::format($year, $month, $day);
    }

    /** Easter Sunday plus $offset days. */
    public static function offset(int $year, int $offset): string
    {
        return CivilDate::addDays(self::sunday($year), $offset);
    }
}
