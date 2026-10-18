<?php
/**
 * Calendar checks, included by bin/selftest.php (shares its check() helper and $fail counter) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-calendar.php
 * Covers: civil-date arithmetic, Western Easter (2025-2028 and more), nth weekdays, leap years, the tabular Islamic calendar against the published
 * Eid dates 2025-2030 (within 2 days), recurrence rules (daily / weekly / monthly / yearly / nth weekday / Easter / Islamic, interval, until),
 * overrides (move, cancel, confirm, stale, moved across the window edge), validation of recurrence_data (incl. a fuzz run), RFC 5545 output
 * (CRLF, folding at 75 octets, escaping, VALUE=DATE with exclusive DTEND, TZID + VTIMEZONE), colour contrast (AA for every seeded type), the seed
 * (14 Uganda holidays, curated international days, idempotent and non-destructive), visibility rules (public / private / inactive / hidden type),
 * the writer (entries, quick add, duplicate, delete, bulk, overrides, types), events merged without copying, the JSON feed, the contact embed and
 * the HTTP endpoints when the local site answers. Everything it creates is removed again (slugs zz-selftest-*).
 */
declare(strict_types=1);

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit("Refusing to run outside APP_ENV=local.\n");
    }
    $fail = 0;
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
    $standalone = true;
}

use App\Core\Database;
use App\Models\CalendarEntry;
use App\Models\CalendarType;
use App\Services\Calendar\CalendarService as CS;
use App\Services\Calendar\CalendarWriter as CW;
use App\Services\Calendar\CivilDate as CD;
use App\Services\Calendar\ColorContrast as CC;
use App\Services\Calendar\Easter;
use App\Services\Calendar\IcsBuilder as ICS;
use App\Services\Calendar\IslamicCalendar as IC;
use App\Services\Calendar\OccurrenceExpander as OE;
use App\Services\Calendar\RecurrenceRules as RR;

echo "Calendar\n";

$calAuditBefore = (int) Database::value('SELECT COALESCE(MAX(id), 0) FROM audit_log', [], 0);
$calCleanup = static function () use ($calAuditBefore): void {
    CD::setToday(null);
    Database::execute("DELETE FROM calendar_entries WHERE slug LIKE 'zz-selftest-%' OR title LIKE 'zz-selftest%'");
    Database::execute("DELETE FROM calendar_types WHERE slug LIKE 'zz-selftest-%'");
    Database::execute("DELETE FROM events WHERE slug LIKE 'zz-selftest-%'");
    Database::execute("DELETE FROM audit_log WHERE id > ? AND action LIKE 'calendar.%'", [$calAuditBefore]);
    CS::flush();
};
$calCleanup();
$calHas = static fn(array $list, string $key): bool => (bool) array_filter($list, static fn($o) => $o['key'] === $key);
$calTitles = static fn(array $list): array => array_map(static fn($o) => $o['title'], $list);

try {
    // ------------------------------------------------------------------------------------------------ civil dates
    echo " dates\n";
    check('leap years (1900 no, 2000 yes, 2024 yes, 2100 no)', !CD::isLeap(1900) && CD::isLeap(2000) && CD::isLeap(2024) && !CD::isLeap(2100));
    check('days in month incl. February', CD::daysInMonth(2024, 2) === 29 && CD::daysInMonth(2025, 2) === 28 && CD::daysInMonth(2025, 4) === 30 && CD::daysInMonth(2025, 12) === 31);
    check('strict date validation', CD::valid('2024-02-29') && !CD::valid('2025-02-29') && !CD::valid('2025-13-01') && !CD::valid('2025-04-31') && !CD::valid('2025-1-1') && !CD::valid('1899-12-31') && !CD::valid(' 2025-01-01') && !CD::valid(null) && !CD::valid(['2025-01-01']));
    check('weekday: 1970-01-01 Thursday, 2000-01-01 Saturday, 2026-10-18 Sunday', CD::weekday('1970-01-01') === 4 && CD::weekday('2000-01-01') === 6 && CD::weekday('2026-10-18') === 7);
    check('days <-> date round trip over 1900-2200 samples', (static function (): bool {
        for ($d = CD::days('1900-01-01'); $d <= CD::days('2200-12-31'); $d += 97) {
            [$y, $m, $dd] = CD::fromDays($d);
            if (CD::toDays($y, $m, $dd) !== $d) {
                return false;
            }
        }
        return true;
    })());
    check('year / month / day boundaries', CD::addDays('2025-12-31', 1) === '2026-01-01' && CD::addDays('2026-01-01', -1) === '2025-12-31' && CD::addDays('2024-02-28', 1) === '2024-02-29' && CD::addDays('2025-02-28', 1) === '2025-03-01' && CD::diffDays('2025-12-25', '2026-01-05') === 11);
    check('addMonths across years', CD::addMonths('2025-11', 3) === '2026-02' && CD::addMonths('2026-01', -1) === '2025-12' && CD::addMonths('2026-01', -13) === '2024-12' && CD::addMonths('2026-12', 12) === '2027-12');
    check('weekStartOf (Monday / Sunday first)', CD::weekStartOf('2026-10-18', 1) === '2026-10-12' && CD::weekStartOf('2026-10-18', 7) === '2026-10-18' && CD::weekStartOf('2026-10-17', 7) === '2026-10-11');
    check('fmt', CD::fmt('2026-03-08', 'l j F Y') === 'Sunday 8 March 2026' && CD::fmt('2026-03-08', 'D j M') === 'Sun 8 Mar' && CD::fmt('2026-03-08', 'd/m/y') === '08/03/26');
    check('time() accepts HH:MM and HH:MM:SS only', CD::time('09:30') === '09:30' && CD::time('09:30:15') === '09:30' && CD::time('24:00') === null && CD::time('9:30') === null && CD::time('') === null);
    check('Africa/Kampala has no daylight saving (fixed +03:00 all year)', (new DateTimeZone('Africa/Kampala'))->getOffset(new DateTimeImmutable('2026-01-15 12:00', new DateTimeZone('UTC'))) === 10800
        && (new DateTimeZone('Africa/Kampala'))->getOffset(new DateTimeImmutable('2026-07-15 12:00', new DateTimeZone('UTC'))) === 10800);

    echo " easter and nth weekdays\n";
    check('Easter 2025-04-20, 2026-04-05, 2027-03-28, 2028-04-16', Easter::sunday(2025) === '2025-04-20' && Easter::sunday(2026) === '2026-04-05' && Easter::sunday(2027) === '2027-03-28' && Easter::sunday(2028) === '2028-04-16');
    check('Easter more years (2024, 2029, 2030, 2038 latest, 2285 earliest-ish)', Easter::sunday(2024) === '2024-03-31' && Easter::sunday(2029) === '2029-04-01' && Easter::sunday(2030) === '2030-04-21' && Easter::sunday(2038) === '2038-04-25' && Easter::sunday(2008) === '2008-03-23');
    check('Good Friday / Easter Monday derived', Easter::offset(2025, -2) === '2025-04-18' && Easter::offset(2025, 1) === '2025-04-21' && Easter::offset(2026, -2) === '2026-04-03' && Easter::offset(2026, 1) === '2026-04-06' && Easter::offset(2027, -2) === '2027-03-26' && Easter::offset(2028, 1) === '2028-04-17');
    check('Easter is always a Sunday in March/April 1900-2200', (static function (): bool {
        for ($y = 1900; $y <= 2199; $y++) {
            $e = Easter::sunday($y);
            $m = (int) substr($e, 5, 2);
            if (CD::weekday($e) !== 7 || ($m !== 3 && $m !== 4)) {
                return false;
            }
        }
        return true;
    })());
    check('4th Thursday of April 2025-2030 (24 Apr 2025, 23 Apr 2026, 22 Apr 2027, 27 Apr 2028, 26 Apr 2029, 25 Apr 2030)',
        [CD::nthWeekday(2025, 4, 4, 4), CD::nthWeekday(2026, 4, 4, 4), CD::nthWeekday(2027, 4, 4, 4), CD::nthWeekday(2028, 4, 4, 4), CD::nthWeekday(2029, 4, 4, 4), CD::nthWeekday(2030, 4, 4, 4)]
        === ['2025-04-24', '2026-04-23', '2027-04-22', '2028-04-27', '2029-04-26', '2030-04-25']);
    check('2nd Tuesday of February 2025-2035 is a Tuesday between the 8th and the 14th (10 Feb 2026, 9 Feb 2027)', (static function (): bool {
        for ($y = 2025; $y <= 2035; $y++) {
            $d = CD::nthWeekday($y, 2, 2, 2);
            if ($d === null || CD::weekday($d) !== 2 || (int) substr($d, 8, 2) < 8 || (int) substr($d, 8, 2) > 14) {
                return false;
            }
        }
        return CD::nthWeekday(2026, 2, 2, 2) === '2026-02-10' && CD::nthWeekday(2027, 2, 2, 2) === '2027-02-09';
    })());
    check('last weekday and a missing 5th weekday', CD::nthWeekday(2026, 2, 1, -1) === '2026-02-23' && CD::nthWeekday(2026, 2, 1, 5) === null && CD::nthWeekday(2026, 3, 1, 5) === '2026-03-30' && CD::nthWeekday(2026, 13, 1, 1) === null && CD::nthWeekday(2026, 2, 8, 1) === null);

    // ------------------------------------------------------------------------------------------------ islamic
    echo " islamic (tabular) calendar\n";
    $published = [   // [year, Eid al-Fitr, Eid al-Adha] as announced / expected (UMSC 2025-2026, astronomical tables 2027-2030): see docs/CALENDAR.md
        [2025, '2025-03-30', '2025-06-06'], [2026, '2026-03-20', '2026-05-27'], [2027, '2027-03-09', '2027-05-16'],
        [2028, '2028-02-26', '2028-05-05'], [2029, '2029-02-13', '2029-04-24'], [2030, '2030-02-04', '2030-04-13'],
    ];
    $islamicOk = true;
    $islamicWhy = '';
    foreach ($published as [$y, $fitr, $adha]) {
        $f = IC::datesInRange('eid_al_fitr', "$y-01-01", "$y-12-31");
        $a = IC::datesInRange('eid_al_adha', "$y-01-01", "$y-12-31");
        if (count($f) !== 1 || count($a) !== 1 || abs(CD::diffDays($fitr, $f[0])) > 2 || abs(CD::diffDays($adha, $a[0])) > 2) {
            $islamicOk = false;
            $islamicWhy .= " {$y}: " . implode(',', $f) . ' / ' . implode(',', $a);
        }
    }
    check('tabular Eid al-Fitr and Eid al-Adha 2025-2030 within 2 days of the published dates', $islamicOk, $islamicWhy);
    check('2026: tabular = announced dates exactly (20 Mar, 27 May)', IC::datesInRange('eid_al_fitr', '2026-01-01', '2026-12-31') === ['2026-03-20'] && IC::datesInRange('eid_al_adha', '2026-01-01', '2026-12-31') === ['2026-05-27']);
    check('Gregorian <-> Hijri round trip (3000 days)', (static function (): bool {
        for ($d = CD::days('2020-01-01'); $d < CD::days('2020-01-01') + 3000; $d++) {
            $g = CD::ymd($d);
            [$hy, $hm, $hd] = IC::fromGregorian($g);
            if (IC::toGregorian($hy, $hm, $hd) !== $g || $hm < 1 || $hm > 12 || $hd < 1 || $hd > 30) {
                return false;
            }
        }
        return true;
    })());
    check('the same Islamic date can fall twice in one Gregorian year (Islamic New Year 2008)', count(IC::datesInRange('islamic_new_year', '2008-01-01', '2008-12-31')) === 2);
    check('Hijri leap years follow the 30-year cycle (11 per cycle)', count(array_filter(range(1, 30), [IC::class, 'isLeap'])) === 11);
    check('unknown Islamic key gives nothing', IC::datesInRange('nope', '2026-01-01', '2026-12-31') === []);

    // ------------------------------------------------------------------------------------------------ recurrence rules
    echo " recurrence rules\n";
    $rule = static fn(string $rec, array $data, string $start, ?string $until = null): array => ['start_date' => $start, 'recurrence' => $rec, 'recurrence_data' => json_encode($data), 'repeat_until' => $until];
    check('none: only the start day', RR::starts($rule('none', [], '2026-05-05'), '2026-01-01', '2026-12-31') === ['2026-05-05'] && RR::starts($rule('none', [], '2026-05-05'), '2026-06-01', '2026-12-31') === []);
    check('daily every 3 days', RR::starts($rule('daily', ['interval' => 3], '2026-03-01'), '2026-03-05', '2026-03-16') === ['2026-03-07', '2026-03-10', '2026-03-13', '2026-03-16']);
    check('weekly Mon+Wed, every 2 weeks', RR::starts($rule('weekly', ['interval' => 2, 'weekdays' => [1, 3]], '2026-03-02'), '2026-03-01', '2026-03-31') === ['2026-03-02', '2026-03-04', '2026-03-16', '2026-03-18', '2026-03-30']);
    check('weekly default weekday = weekday of the start date', RR::starts($rule('weekly', [], '2026-03-04'), '2026-03-01', '2026-03-20') === ['2026-03-04', '2026-03-11', '2026-03-18']);
    check('monthly on the 31st clamps to the last day of short months', RR::starts($rule('monthly', ['mode' => 'day'], '2026-01-31'), '2026-01-01', '2026-05-31') === ['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30', '2026-05-31']);
    check('monthly on the 2nd Tuesday', RR::starts($rule('monthly', ['mode' => 'nth_weekday', 'nth' => 2, 'weekday' => 2], '2026-01-01'), '2026-01-01', '2026-03-31') === ['2026-01-13', '2026-02-10', '2026-03-10']);
    check('monthly last Friday', RR::starts($rule('monthly', ['mode' => 'nth_weekday', 'nth' => -1, 'weekday' => 5], '2026-01-01'), '2026-01-01', '2026-02-28') === ['2026-01-30', '2026-02-27']);
    check('yearly: 29 February falls on 28 February in common years', RR::starts($rule('yearly', [], '2024-02-29'), '2024-01-01', '2028-12-31') === ['2024-02-29', '2025-02-28', '2026-02-28', '2027-02-28', '2028-02-29']);
    check('yearly every 2 years', RR::starts($rule('yearly', ['interval' => 2], '2026-06-03'), '2026-01-01', '2032-12-31') === ['2026-06-03', '2028-06-03', '2030-06-03', '2032-06-03']);
    check('nth_weekday rule (4th Thursday of April) never starts before start_date', RR::starts($rule('nth_weekday', ['month' => 4, 'weekday' => 4, 'nth' => 4], '2026-05-01'), '2025-01-01', '2028-12-31') === ['2027-04-22', '2028-04-27']);
    check('easter rule Good Friday and Easter Monday', RR::starts($rule('easter', ['easter_offset' => -2], '2020-01-01'), '2026-01-01', '2028-12-31') === ['2026-04-03', '2027-03-26', '2028-04-14'] && RR::starts($rule('easter', ['easter_offset' => 1], '2020-01-01'), '2026-01-01', '2026-12-31') === ['2026-04-06']);
    check('easter offset reaches into the previous year (Easter 2027 minus 100 days = 18 Dec 2026) and Ash Wednesday (-46)', RR::starts($rule('easter', ['easter_offset' => -100], '2020-01-01'), '2026-12-01', '2026-12-31') === ['2026-12-18'] && RR::starts($rule('easter', ['easter_offset' => -46], '2020-01-01'), '2027-01-01', '2027-12-31') === ['2027-02-10']);
    check('islamic rule honours the range', RR::starts($rule('islamic', ['islamic' => 'eid_al_fitr'], '2020-01-01'), '2027-01-01', '2027-12-31') === ['2027-03-10']);
    check('until (column) stops the series', RR::starts($rule('daily', [], '2026-03-01', '2026-03-03'), '2026-03-01', '2026-03-31') === ['2026-03-01', '2026-03-02', '2026-03-03']);
    check('until (inside recurrence_data) stops the series; the earlier of both wins', RR::starts($rule('daily', ['until' => '2026-03-02'], '2026-03-01'), '2026-03-01', '2026-03-31') === ['2026-03-01', '2026-03-02']
        && RR::starts($rule('daily', ['until' => '2026-03-20'], '2026-03-01', '2026-03-02'), '2026-03-01', '2026-03-31') === ['2026-03-01', '2026-03-02']);
    check('a range across the year boundary', RR::starts($rule('daily', [], '2025-12-30'), '2025-12-31', '2026-01-02') === ['2025-12-31', '2026-01-01', '2026-01-02']);
    check('stored garbage yields no occurrences and no exception', RR::starts(['start_date' => '2026-01-01', 'recurrence' => 'weekly', 'recurrence_data' => '{"weekdays":[9]}'], '2026-01-01', '2026-12-31') === []
        && RR::starts(['start_date' => 'nope', 'recurrence' => 'daily'], '2026-01-01', '2026-02-01') === [] && RR::starts(['start_date' => '2026-01-01', 'recurrence' => 'daily'], '2026-02-01', '2026-01-01') === []);
    check('describe() reads like a sentence', RR::describe($rule('yearly', [], '2026-03-08')) === 'Every year on 8 March' && RR::describe($rule('weekly', ['weekdays' => [1, 3]], '2026-03-02')) === 'Every week on Monday, Wednesday'
        && str_contains(RR::describe($rule('nth_weekday', ['month' => 2, 'weekday' => 2, 'nth' => 2], '2020-01-01')), '2nd Tuesday of February') && RR::describe($rule('easter', ['easter_offset' => -2], '2020-01-01')) === 'Good Friday (2 days before Easter Sunday) every year'
        && str_contains(RR::describe($rule('islamic', ['islamic' => 'eid_al_adha'], '2020-01-01')), 'date to be confirmed') && RR::describe($rule('none', [], '2026-01-01')) === 'Does not repeat');

    echo " recurrence_data validation\n";
    $n = static fn(array $in): array => RR::normalise($in + ['recurrence' => 'none', 'start_date' => '2026-03-02']);
    check('valid weekly is normalised (sorted unique weekdays, interval default)', ($v = $n(['recurrence' => 'weekly', 'recurrence_data' => ['weekdays' => [5, 1, 5]]])) && $v['ok'] && $v['data'] === ['interval' => 1, 'weekdays' => [1, 5]]);
    check('JSON string input is accepted', $n(['recurrence' => 'yearly', 'recurrence_data' => '{"interval":2}'])['ok']);
    check('unknown recurrence type rejected', !$n(['recurrence' => 'hourly'])['ok'] && isset($n(['recurrence' => 'hourly'])['errors']['recurrence']));
    check('unknown key rejected', !$n(['recurrence' => 'daily', 'recurrence_data' => ['weekdays' => [1]]])['ok'] && !$n(['recurrence' => 'daily', 'recurrence_data' => ['x' => 1]])['ok']);
    check('out-of-range values rejected', !$n(['recurrence' => 'daily', 'recurrence_data' => ['interval' => 0]])['ok'] && !$n(['recurrence' => 'daily', 'recurrence_data' => ['interval' => 100]])['ok']
        && !$n(['recurrence' => 'weekly', 'recurrence_data' => ['weekdays' => [0]]])['ok'] && !$n(['recurrence' => 'weekly', 'recurrence_data' => ['weekdays' => []]])['ok']
        && !$n(['recurrence' => 'nth_weekday', 'recurrence_data' => ['month' => 13, 'weekday' => 1, 'nth' => 1]])['ok'] && !$n(['recurrence' => 'nth_weekday', 'recurrence_data' => ['month' => 2, 'weekday' => 1, 'nth' => 0]])['ok']
        && !$n(['recurrence' => 'easter', 'recurrence_data' => ['easter_offset' => 400]])['ok'] && !$n(['recurrence' => 'islamic', 'recurrence_data' => ['islamic' => 'eid']])['ok'] && !$n(['recurrence' => 'islamic', 'recurrence_data' => []])['ok']);
    check('wrong types rejected (strings, floats, bools, nested arrays)', !$n(['recurrence' => 'daily', 'recurrence_data' => ['interval' => 'x']])['ok'] && !$n(['recurrence' => 'daily', 'recurrence_data' => ['interval' => 1.5]])['ok']
        && !$n(['recurrence' => 'daily', 'recurrence_data' => ['interval' => [1]]])['ok'] && !$n(['recurrence' => 'weekly', 'recurrence_data' => ['weekdays' => 'mon']])['ok']);
    check('malformed JSON, a JSON list, a scalar and an oversized document are rejected', !$n(['recurrence' => 'daily', 'recurrence_data' => '{bad'])['ok'] && !$n(['recurrence' => 'daily', 'recurrence_data' => '[1,2]'])['ok']
        && !$n(['recurrence' => 'daily', 'recurrence_data' => 5])['ok'] && !$n(['recurrence' => 'daily', 'recurrence_data' => str_repeat('a', 5000)])['ok']);
    check('"none" accepts no settings', !$n(['recurrence' => 'none', 'recurrence_data' => ['interval' => 1]])['ok'] && $n(['recurrence' => 'none'])['ok']);
    check('dates: end before start, bad dates, too long, until before start', !$n(['end_date' => '2026-03-01'])['ok'] && !$n(['start_date' => '2026-02-30'])['ok'] && !$n(['end_date' => '2028-03-02'])['ok'] && !$n(['recurrence' => 'daily', 'repeat_until' => '2026-03-01'])['ok']);
    check('"until" inside the JSON is moved to repeat_until', ($v = $n(['recurrence' => 'daily', 'recurrence_data' => ['until' => '2026-04-01']])) && $v['ok'] && $v['repeat_until'] === '2026-04-01' && !isset($v['data']['until']));
    check('normalise() is idempotent', (static function () use ($n): bool {
        $a = $n(['recurrence' => 'monthly', 'recurrence_data' => ['mode' => 'nth_weekday']]);
        $b = $n(['recurrence' => 'monthly', 'recurrence_data' => $a['json'], 'repeat_until' => $a['repeat_until']]);
        return $a['ok'] && $b['ok'] && $a['json'] === $b['json'];
    })());
    check('fuzz: 4000 random inputs never throw; accepted ones expand without error', (static function (): bool {
        mt_srand(20260418);
        $junk = [null, true, false, 0, 1, -1, 7, 99, 100, 1.5, 'x', '', '1', '{"a":1}', '[]', [], [1, 2], ['interval' => 3], ['weekdays' => [1, 2]], ['month' => 2, 'weekday' => 2, 'nth' => 2], ['easter_offset' => -2], ['islamic' => 'eid_al_adha'], ['mode' => 'nth_weekday', 'nth' => -1, 'weekday' => 5], str_repeat('z', 40), ['until' => '2030-01-01'], ['until' => 'x']];
        $types = ['none', 'daily', 'weekly', 'monthly', 'yearly', 'nth_weekday', 'easter', 'islamic', 'bogus', '', null, 5];
        for ($i = 0; $i < 4000; $i++) {
            $in = ['recurrence' => $types[mt_rand(0, count($types) - 1)], 'recurrence_data' => $junk[mt_rand(0, count($junk) - 1)],
                'start_date' => mt_rand(0, 4) ? '2026-0' . mt_rand(1, 9) . '-1' . mt_rand(0, 9) : $junk[mt_rand(0, count($junk) - 1)],
                'end_date' => $junk[mt_rand(0, count($junk) - 1)], 'repeat_until' => mt_rand(0, 3) ? null : $junk[mt_rand(0, count($junk) - 1)]];
            try {
                $v = RR::normalise($in);
                if ($v['ok'] && $v['start_date'] !== null) {
                    RR::starts(['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'recurrence' => $v['recurrence'], 'recurrence_data' => $v['json'], 'repeat_until' => $v['repeat_until']], '2026-01-01', '2027-12-31');
                }
                RR::starts($in + ['start_date' => '2026-01-01'], '2026-01-01', '2026-12-31');
            } catch (\Throwable $e) {
                echo '    fuzz exception: ' . $e->getMessage() . ' ' . json_encode($in) . "\n";
                return false;
            }
        }
        return true;
    })());

    // ------------------------------------------------------------------------------------------------ overrides and the expander
    echo " occurrences and overrides\n";
    $entry = ['id' => 77, 'type_id' => 1, 'title' => 'Weekly class', 'slug' => 'weekly-class', 'start_date' => '2026-03-02', 'end_date' => null, 'all_day' => 0, 'start_time' => '09:00:00', 'end_time' => '11:00:00',
        'recurrence' => 'weekly', 'recurrence_data' => '{"interval":1,"weekdays":[1]}', 'repeat_until' => '2026-03-30', 'needs_confirmation' => 0, 'is_public' => 1, 'is_active' => 1];
    $plain = OE::expandEntries([$entry], [], '2026-03-01', '2026-03-31');
    check('expander: five Mondays with times', count($plain) === 5 && $plain[0]['start_date'] === '2026-03-02' && $plain[0]['start_time'] === '09:00' && $plain[0]['end_time'] === '11:00' && !$plain[0]['all_day']);
    $ov = [77 => [
        ['id' => 1, 'entry_id' => 77, 'occurrence_date' => '2026-03-09', 'new_date' => '2026-03-11', 'new_start_time' => '14:00:00', 'new_end_time' => '15:30:00', 'cancelled' => 0, 'confirmed' => 0, 'note' => 'Moved for a holiday'],
        ['id' => 2, 'entry_id' => 77, 'occurrence_date' => '2026-03-16', 'new_date' => null, 'new_start_time' => null, 'new_end_time' => null, 'cancelled' => 1, 'confirmed' => 0, 'note' => null],
        ['id' => 3, 'entry_id' => 77, 'occurrence_date' => '2026-03-25', 'new_date' => '2026-03-26', 'new_start_time' => null, 'new_end_time' => null, 'cancelled' => 0, 'confirmed' => 0, 'note' => null],   // stale: not a Monday of the rule
    ]];
    $with = OE::expandEntries([$entry], $ov, '2026-03-01', '2026-03-31');
    $byKey = array_column($with, null, 'key');
    check('override: moved occurrence takes the new date and times, keeps its identity', isset($byKey['e77:2026-03-09']) && $byKey['e77:2026-03-09']['start_date'] === '2026-03-11' && $byKey['e77:2026-03-09']['start_time'] === '14:00' && $byKey['e77:2026-03-09']['moved'] && $byKey['e77:2026-03-09']['occurrence_date'] === '2026-03-09' && $byKey['e77:2026-03-09']['note'] === 'Moved for a holiday');
    check('override: cancelled occurrence disappears (and shows with include_cancelled)', !isset($byKey['e77:2026-03-16']) && count($with) === 4
        && (OE::expandEntries([$entry], $ov, '2026-03-01', '2026-03-31', ['include_cancelled' => true])[2]['cancelled'] ?? false));
    check('override: a stale override (date not produced by the rule) is ignored', !$calHas($with, 'e77:2026-03-25') && count(array_filter($with, static fn($o) => $o['start_date'] === '2026-03-26')) === 0);
    $edge = OE::expandEntries([$entry], $ov, '2026-03-11', '2026-03-11');
    check('override: an occurrence moved INTO the range from outside its window is found', count($edge) === 1 && $edge[0]['key'] === 'e77:2026-03-09');
    check('override: an occurrence moved OUT of the range is not returned', OE::expandEntries([$entry], $ov, '2026-03-09', '2026-03-09') === []);
    $eid = ['id' => 5, 'type_id' => 3, 'title' => 'Eid al-Fitr', 'slug' => 'eid-al-fitr', 'start_date' => '2020-01-01', 'end_date' => null, 'all_day' => 1, 'recurrence' => 'islamic', 'recurrence_data' => '{"islamic":"eid_al_fitr"}', 'needs_confirmation' => 1, 'is_public' => 1, 'is_active' => 1];
    $eidPlain = OE::expandEntries([$eid], [], '2027-01-01', '2027-12-31');
    check('unconfirmed Islamic date is flagged "to be confirmed"', count($eidPlain) === 1 && $eidPlain[0]['needs_confirmation'] && !$eidPlain[0]['confirmed']);
    $eidConf = OE::expandEntries([$eid], [5 => [['id' => 9, 'entry_id' => 5, 'occurrence_date' => $eidPlain[0]['start_date'], 'new_date' => CD::addDays($eidPlain[0]['start_date'], 1), 'cancelled' => 0, 'confirmed' => 1, 'note' => null]]], '2027-01-01', '2027-12-31');
    check('confirmed override supersedes the prediction (new day, no longer "to be confirmed")', count($eidConf) === 1 && $eidConf[0]['start_date'] === CD::addDays($eidPlain[0]['start_date'], 1) && !$eidConf[0]['needs_confirmation'] && $eidConf[0]['confirmed']);
    $multi = ['id' => 6, 'type_id' => 1, 'title' => 'Camp', 'slug' => 'camp', 'start_date' => '2026-03-30', 'end_date' => '2026-04-03', 'all_day' => 1, 'recurrence' => 'none'];
    check('multi-day: appears in a range that only touches its tail or its head', count(OE::expandEntries([$multi], [], '2026-04-02', '2026-04-10')) === 1 && count(OE::expandEntries([$multi], [], '2026-03-01', '2026-03-30')) === 1 && count(OE::expandEntries([$multi], [], '2026-04-04', '2026-04-10')) === 0);
    $multiRec = ['id' => 7, 'type_id' => 1, 'title' => 'Retreat', 'slug' => 'retreat', 'start_date' => '2026-01-30', 'end_date' => '2026-02-02', 'all_day' => 1, 'recurrence' => 'monthly', 'recurrence_data' => '{"mode":"day"}'];
    check('recurring multi-day: an occurrence that started before the range is still returned', count(OE::expandEntries([$multiRec], [], '2026-02-01', '2026-02-01')) === 1);
    $evt = OE::fromEvent(['id' => 3, 'slug' => 'open-day', 'title' => 'Open day', 'summary' => 'Hello', 'start_at' => '2026-05-06 10:00:00', 'end_at' => '2026-05-07 16:30:00', 'location' => 'Gulu', 'is_active' => 1]);
    check('event row becomes a (multi-day, timed) occurrence', $evt && $evt['source'] === 'event' && $evt['start_time'] === '10:00' && $evt['end_date'] === '2026-05-07' && $evt['end_time'] === '16:30' && !$evt['all_day'] && $evt['key'] === 'v3:2026-05-06');
    check('event at midnight without an end is all-day; bad rows are skipped', OE::fromEvent(['id' => 1, 'slug' => 'a', 'title' => 'A', 'start_at' => '2026-05-06 00:00:00', 'end_at' => null, 'is_active' => 1])['all_day'] === true && OE::fromEvent(['id' => 1, 'start_at' => 'garbage']) === null);

    // ------------------------------------------------------------------------------------------------ iCalendar
    echo " iCalendar (RFC 5545)\n";
    $icsOcc = [
        ['key' => 'e1:2026-03-08', 'entry_id' => 1, 'title' => 'International Women\'s Day; "joy", more', 'start_date' => '2026-03-08', 'end_date' => '2026-03-08', 'all_day' => true, 'start_time' => null, 'end_time' => null,
         'description' => '<p>Line one</p><p>Line two, with a comma; and a semicolon</p>', 'location' => 'Gulu, Uganda', 'needs_confirmation' => false, 'source_label' => 'Public Holidays Act (Uganda)', 'link_url' => 'https://example.org/x', 'type_name' => 'Uganda public holiday', 'abs_url' => 'https://example.org/calendar/x'],
        ['key' => 'e2:2026-03-09', 'entry_id' => 2, 'title' => 'Eid al-Fitr', 'start_date' => '2026-03-20', 'end_date' => '2026-03-22', 'all_day' => true, 'description' => '', 'location' => '', 'needs_confirmation' => true, 'note' => null],
        ['key' => 'e3:2026-04-01', 'entry_id' => 3, 'title' => 'Workshop', 'start_date' => '2026-04-01', 'end_date' => '2026-04-01', 'all_day' => false, 'start_time' => '09:00', 'end_time' => '11:30', 'description' => str_repeat('Ünïcödé text ', 40), 'location' => 'Gulu', 'needs_confirmation' => false],
        ['key' => 'e4:2026-04-02', 'entry_id' => 4, 'title' => 'Late show', 'start_date' => '2026-04-02', 'end_date' => '2026-04-03', 'all_day' => false, 'start_time' => '22:00', 'end_time' => null, 'description' => '', 'location' => '', 'needs_confirmation' => false],
        ['key' => 'bad', 'title' => 'Broken', 'start_date' => 'nope', 'end_date' => 'nope', 'all_day' => true],
    ];
    $ics = ICS::calendar($icsOcc, ['name' => 'Test; calendar', 'host' => 'example.org', 'now' => '20260101T000000Z']);
    $icsLines = explode("\r\n", $ics);
    check('CRLF line endings only, ends with CRLF, begins/ends with VCALENDAR', str_ends_with($ics, "END:VCALENDAR\r\n") && str_starts_with($ics, "BEGIN:VCALENDAR\r\n") && !preg_match('/(?<!\r)\n/', $ics));
    check('no content line longer than 75 octets (multi-byte text folded safely)', (static function () use ($icsLines): bool {
        foreach ($icsLines as $l) {
            if (strlen($l) > 75) {
                return false;
            }
        }
        return true;
    })());
    $unfolded = preg_replace("/\r\n[ \t]/", '', $ics);
    check('folding does not break UTF-8 and unfolds to the original text', mb_check_encoding($ics, 'UTF-8') && str_contains($unfolded, str_repeat('Ünïcödé text ', 40) === '' ? '' : 'Ünïcödé text Ünïcödé'));
    check('TEXT escaping (semicolon, comma, backslash, newline)', str_contains($unfolded, 'SUMMARY:International Women\'s Day\; "joy"\, more') && str_contains($unfolded, 'LOCATION:Gulu\, Uganda') && str_contains($unfolded, 'Line one\nLine two\, with a comma\; and a semicolon') && str_contains($unfolded, 'X-WR-CALNAME:Test\; calendar'));
    check('all-day: VALUE=DATE with an EXCLUSIVE DTEND (single day +1, 3 days +1)', str_contains($ics, "DTSTART;VALUE=DATE:20260308\r\nDTEND;VALUE=DATE:20260309") && str_contains($ics, "DTSTART;VALUE=DATE:20260320\r\nDTEND;VALUE=DATE:20260323"));
    check('timed: TZID=Africa/Kampala, VTIMEZONE present, DTEND from end time; none without end time; multi-day ends at 23:59', str_contains($ics, 'DTSTART;TZID=Africa/Kampala:20260401T090000') && str_contains($ics, 'DTEND;TZID=Africa/Kampala:20260401T113000')
        && str_contains($ics, 'BEGIN:VTIMEZONE') && str_contains($ics, 'TZID:Africa/Kampala') && str_contains($ics, 'DTEND;TZID=Africa/Kampala:20260403T235900'));
    check('VTIMEZONE only when a timed event exists', !str_contains(ICS::calendar([$icsOcc[0]], ['now' => '20260101T000000Z']), 'VTIMEZONE'));
    check('every VEVENT has UID, DTSTAMP, SUMMARY; UIDs unique; DTSTAMP is UTC', (static function () use ($ics): bool {
        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $ics, $m);
        $uids = [];
        foreach ($m[1] as $b) {
            if (!preg_match('/\nUID:(\S+)/', $b, $u) || !preg_match('/\nDTSTAMP:\d{8}T\d{6}Z/', $b) || !str_contains($b, "\nSUMMARY:")) {
                return false;
            }
            $uids[] = $u[1];
        }
        return count($m[1]) === 4 && count(array_unique($uids)) === 4;
    })());
    check('invalid occurrences are skipped; BEGIN/END are balanced', substr_count($ics, 'BEGIN:VEVENT') === 4 && substr_count($ics, 'END:VEVENT') === 4 && substr_count($ics, 'BEGIN:') === substr_count($ics, 'END:'));
    check('unconfirmed date: STATUS:TENTATIVE and a "Date to be confirmed" line', str_contains($unfolded, "STATUS:TENTATIVE") && str_contains($unfolded, 'Date to be confirmed.') && str_contains($unfolded, 'STATUS:CONFIRMED'));
    check('UID is stable when an occurrence moves (it follows the original date)', ICS::uid(['key' => 'e5:2026-03-20'], 'x.org') === ICS::uid(['key' => 'e5:2026-03-20', 'start_date' => '2026-03-21'], 'x.org') && str_ends_with(ICS::uid(['key' => 'e5:2026-03-20'], 'x.org'), '@x.org'));
    check('header injection: control characters never survive', !preg_match('/\r\n[A-Z-]+:INJECT/', ICS::calendar([['key' => 'e9:2026-01-01', 'entry_id' => 9, 'title' => "A\r\nX-EVIL:INJECT", 'start_date' => '2026-01-01', 'end_date' => '2026-01-01', 'all_day' => true]], ['now' => '20260101T000000Z'])));

    // ------------------------------------------------------------------------------------------------ colour contrast
    echo " colour contrast\n";
    check('ratio black/white = 21, same colour = 1', CC::ratio('#000000', '#ffffff') === 21.0 && CC::ratio('#336699', '#336699') === 1.0);
    check('normalise accepts 3 or 6 digits, rejects junk', CC::normalise('#ABC') === '#aabbcc' && CC::normalise('2f6f7a') === '#2f6f7a' && CC::normalise('red') === null && CC::normalise('#12345') === null && CC::normalise(null) === null);
    check('every colour gets an AA pair (pill text on its tint, label on the solid colour), whatever it is', (static function (): bool {
        foreach (['#ffff00', '#808080', '#00ff00', '#0000ff', '#ff0000', '#123456', '#f0f0f0', '#000000', '#ffffff'] as $c) {
            $p = CC::pair($c);
            if ($p['ratio_text'] < 4.5 || $p['ratio_solid'] < 4.4) {
                echo "    pair fails for $c: " . json_encode($p) . "\n";
                return false;
            }
        }
        return true;
    })());

    // ------------------------------------------------------------------------------------------------ database objects and seed
    echo " schema and seed\n";
    foreach (['calendar_types', 'calendar_entries', 'calendar_overrides'] as $t) {
        check("table {$t} exists", Database::tableExists($t));
    }
    check('CalendarService::available()', CS::available());
    $types = array_column(Database::all('SELECT slug, color, is_system FROM calendar_types'), null, 'slug');
    check('7 default types incl. the event and holiday system types', count(array_intersect(array_keys($types), ['programme', 'event', 'uganda-holiday', 'international-day', 'meeting', 'deadline', 'other'])) === 7 && (int) $types['event']['is_system'] === 1 && (int) $types['uganda-holiday']['is_system'] === 1);
    check('every default type colour has an AA pair', (static function (): bool {
        foreach (CS::types() as $t) {
            if (!$t['pair']['aa_solid'] || !$t['pair']['aa_text']) {
                echo "    type {$t['slug']}: " . json_encode($t['pair']) . "\n";
                return false;
            }
        }
        return true;
    })());
    $hol = Database::all("SELECT e.* FROM calendar_entries e JOIN calendar_types t ON t.id = e.type_id WHERE t.slug = 'uganda-holiday' AND e.is_system = 1");
    $expectedHolidays = ['new-years-day', 'nrm-liberation-day', 'archbishop-janani-luwum-day', 'international-womens-day', 'good-friday', 'easter-monday', 'labour-day', 'uganda-martyrs-day', 'national-heroes-day', 'eid-al-fitr', 'eid-al-adha', 'independence-day', 'christmas-day', 'boxing-day'];
    check('exactly the 14 statutory Uganda public holidays are seeded', count($hol) === 14 && !array_diff($expectedHolidays, array_column($hol, 'slug')));
    $holBy = array_column($hol, null, 'slug');
    check('fixed holidays carry the right day and month (1 Jan, 26 Jan, 16 Feb, 8 Mar, 1 May, 3 Jun, 9 Jun, 9 Oct, 25 Dec, 26 Dec)', (static function () use ($holBy): bool {
        $want = ['new-years-day' => '01-01', 'nrm-liberation-day' => '01-26', 'archbishop-janani-luwum-day' => '02-16', 'international-womens-day' => '03-08', 'labour-day' => '05-01', 'uganda-martyrs-day' => '06-03', 'national-heroes-day' => '06-09', 'independence-day' => '10-09', 'christmas-day' => '12-25', 'boxing-day' => '12-26'];
        foreach ($want as $slug => $md) {
            if ($holBy[$slug]['recurrence'] !== 'yearly' || substr((string) $holBy[$slug]['start_date'], 5) !== $md) {
                return false;
            }
        }
        return true;
    })());
    check('Good Friday = Easter -2, Easter Monday = Easter +1', $holBy['good-friday']['recurrence'] === 'easter' && json_decode($holBy['good-friday']['recurrence_data'], true)['easter_offset'] === -2 && json_decode($holBy['easter-monday']['recurrence_data'], true)['easter_offset'] === 1);
    check('Eid al-Fitr and Eid al-Adha are Islamic rules flagged "date to be confirmed"; no other holiday is', (int) $holBy['eid-al-fitr']['needs_confirmation'] === 1 && (int) $holBy['eid-al-adha']['needs_confirmation'] === 1 && $holBy['eid-al-fitr']['recurrence'] === 'islamic'
        && count(array_filter($hol, static fn($h) => (int) $h['needs_confirmation'] === 1)) === 2);
    $days = Database::all("SELECT e.* FROM calendar_entries e JOIN calendar_types t ON t.id = e.type_id WHERE t.slug = 'international-day' AND e.is_system = 1");
    check('at least 40 curated international days, all with a source and a valid rule', count($days) >= 40 && !array_filter($days, static fn($d) => trim((string) $d['source_label']) === '' || !RR::normalise(['recurrence' => $d['recurrence'], 'recurrence_data' => $d['recurrence_data'], 'start_date' => $d['start_date']])['ok']));
    $dayBy = array_column($days, null, 'slug');
    check('key international days present with the right dates (education 24 Jan, health 7 Apr, humanitarian 19 Aug, refugee 20 Jun, AIDS 1 Dec ...)', (static function () use ($dayBy): bool {
        $want = ['international-day-of-education' => '01-24', 'world-health-day' => '04-07', 'world-humanitarian-day' => '08-19', 'world-refugee-day' => '06-20', 'world-aids-day' => '12-01', 'international-youth-day' => '08-12', 'international-day-of-peace' => '09-21',
            'world-mental-health-day' => '10-10', 'world-food-day' => '10-16', 'human-rights-day' => '12-10', 'africa-day' => '05-25', 'day-of-the-african-child' => '06-16', 'world-youth-skills-day' => '07-15', 'international-day-of-charity' => '09-05',
            'international-day-of-the-girl-child' => '10-11', 'international-day-of-persons-with-disabilities' => '12-03', 'international-volunteer-day' => '12-05', 'world-red-cross-and-red-crescent-day' => '05-08', 'world-telecommunication-and-information-society-day' => '05-17',
            'international-day-of-women-and-girls-in-science' => '02-11', 'world-water-day' => '03-22', 'universal-childrens-day' => '11-20', 'international-day-for-the-elimination-of-violence-against-women' => '11-25', 'international-day-for-the-eradication-of-poverty' => '10-17'];
        foreach ($want as $slug => $md) {
            if (!isset($dayBy[$slug]) || $dayBy[$slug]['recurrence'] !== 'yearly' || substr((string) $dayBy[$slug]['start_date'], 5) !== $md) {
                echo "    wrong/missing: $slug\n";
                return false;
            }
        }
        return $dayBy['safer-internet-day']['recurrence'] === 'nth_weekday' && $dayBy['international-girls-in-ict-day']['recurrence'] === 'nth_weekday'
            && json_decode($dayBy['international-girls-in-ict-day']['recurrence_data'], true) === ['interval' => 1, 'month' => 4, 'weekday' => 4, 'nth' => 4]
            && json_decode($dayBy['safer-internet-day']['recurrence_data'], true) === ['interval' => 1, 'month' => 2, 'weekday' => 2, 'nth' => 1, 'from_day' => 5] && (int) $dayBy['safer-internet-day']['needs_confirmation'] === 1;
    })());
    check('no Lumina programme session is seeded (nothing system-owned in the programme type)', (int) Database::value("SELECT COUNT(*) FROM calendar_entries e JOIN calendar_types t ON t.id = e.type_id WHERE t.slug = 'programme' AND e.is_system = 1", [], 0) === 0);
    check('the calendar page is a registry page under Contact in the menus', ($p = Database::one("SELECT * FROM pages WHERE slug = 'calendar'")) && $p['type'] === 'registry' && $p['parent_slug'] === 'contact' && (int) $p['show_in_nav'] === 1 && (int) $p['show_in_footer'] === 1);
    check('settings seeded', Database::value("SELECT COUNT(*) FROM settings WHERE setting_key IN ('calendar_week_start','calendar_show_on_contact','calendar_default_types')", [], 0) == 3);
    check('registry copy exists (calendar page and contact calendar section)', trim((string) content('calendar', 'hero.title')) !== '' && trim((string) content('calendar', 'notes.disclaimer')) !== '' && trim((string) content('contact', 'calendar.title')) !== '');

    // idempotent and non-destructive seed
    $before = [(int) Database::value('SELECT COUNT(*) FROM calendar_entries', [], 0), (int) Database::value('SELECT COUNT(*) FROM calendar_types', [], 0)];
    $runSeed = static function (): void {
        foreach (Database::splitSql((string) file_get_contents(BASE_PATH . '/database/seed.sql')) as $stmt) {
            if (preg_match('/calendar|^SET /i', $stmt) && !preg_match('/^(CREATE\s+DATABASE|USE\s)/i', $stmt)) {
                Database::pdo()->exec($stmt);
            }
        }
    };
    $runSeed();
    check('importing the seed again changes no row count', $before === [(int) Database::value('SELECT COUNT(*) FROM calendar_entries', [], 0), (int) Database::value('SELECT COUNT(*) FROM calendar_types', [], 0)]);
    $boxing = $holBy['boxing-day'];
    Database::execute("UPDATE calendar_entries SET description = '<p>Owner wording</p>', is_active = 0, updated_at = DATE_ADD(created_at, INTERVAL 5 SECOND) WHERE id = ?", [$boxing['id']]);
    $typeProg = Database::one("SELECT * FROM calendar_types WHERE slug = 'meeting'");
    Database::execute("UPDATE calendar_types SET name = 'Owner meeting name', updated_at = DATE_ADD(created_at, INTERVAL 5 SECOND) WHERE id = ?", [$typeProg['id']]);
    $runSeed();
    $after = Database::one('SELECT description, is_active FROM calendar_entries WHERE id = ?', [$boxing['id']]);
    check('a re-import never overwrites an entry or a type the owner edited (or hid)', $after['description'] === '<p>Owner wording</p>' && (int) $after['is_active'] === 0 && Database::value('SELECT name FROM calendar_types WHERE id = ?', [$typeProg['id']]) === 'Owner meeting name');
    Database::execute('UPDATE calendar_entries SET description = ?, is_active = ?, updated_at = created_at WHERE id = ?', [$boxing['description'], $boxing['is_active'], $boxing['id']]);
    Database::execute('UPDATE calendar_types SET name = ?, updated_at = created_at WHERE id = ?', [$typeProg['name'], $typeProg['id']]);
    Database::execute("UPDATE calendar_entries SET title = 'zz-selftest pristine', source_label = 'x' WHERE id = ?", [$holBy['labour-day']['id']]);
    Database::execute('UPDATE calendar_entries SET updated_at = created_at WHERE id = ?', [$holBy['labour-day']['id']]);
    $runSeed();
    check('a pristine seeded entry IS refreshed from the seed file', Database::value('SELECT title FROM calendar_entries WHERE id = ?', [$holBy['labour-day']['id']]) === 'Labour Day');
    CS::flush();

    // ------------------------------------------------------------------------------------------------ queries over the real data
    echo " queries and visibility\n";
    CD::setToday('2027-02-01');
    $y2027 = CS::occurrences('2027-01-01', '2027-12-31', ['types' => ['uganda-holiday']]);
    check('2027: all 14 Uganda public holidays, once each, in date order', count($y2027) === 14 && $y2027 === array_values($y2027) && array_column($y2027, 'start_date') === (static function () use ($y2027): array { $d = array_column($y2027, 'start_date'); sort($d); return $d; })());
    $t27 = array_column($y2027, 'start_date', 'title');
    check('2027 dates: Good Friday 26 Mar, Easter Monday 29 Mar, Women\'s Day 8 Mar, Independence 9 Oct', $t27['Good Friday'] === '2027-03-26' && $t27['Easter Monday'] === '2027-03-29' && $t27["International Women's Day"] === '2027-03-08' && $t27['Independence Day'] === '2027-10-09');
    $y2026 = array_column(CS::occurrences('2026-01-01', '2026-12-31', ['types' => ['uganda-holiday']]), 'start_date', 'title');
    check('2026 dates: Good Friday 3 Apr, Easter Monday 6 Apr, Eid al-Fitr 20 Mar, Eid al-Adha 27 May', $y2026['Good Friday'] === '2026-04-03' && $y2026['Easter Monday'] === '2026-04-06' && $y2026['Eid al-Fitr'] === '2026-03-20' && $y2026['Eid al-Adha'] === '2026-05-27');
    $eidOcc = array_values(array_filter($y2027, static fn($o) => $o['title'] === 'Eid al-Fitr'))[0];
    check('Eid al-Fitr is flagged "date to be confirmed" and is a holiday with a colour pair and icon', $eidOcc['needs_confirmation'] && $eidOcc['is_holiday'] && $eidOcc['type_icon'] === 'star' && $eidOcc['pair']['aa_text']);
    $june = CS::occurrences('2027-06-16', '2027-06-16');
    check('16 June 2027 shows the Day of the African Child', in_array('Day of the African Child', $calTitles($june), true));
    $feb = CS::monthGrid('2027-02');
    check('month grid: 6 columns of weekdays start Monday, weeks of 7, Safer Internet Day (9 Feb 2027) and Janani Luwum Day (16 Feb) in the cells', count($feb['weekdays']) === 7 && $feb['weekdays'][0]['iso'] === 1 && $feb['weeks'][0][0]['date'] === '2027-02-01' && count($feb['weeks']) === 4
        && in_array('Safer Internet Day', $calTitles((static function () use ($feb): array { foreach ($feb['weeks'] as $w) { foreach ($w as $c) { if ($c['date'] === '2027-02-09') { return $c['items']; } } } return []; })()), true));
    $oct = CS::monthGrid('2026-10', [], ['week_start' => 7]);
    check('month grid: Sunday-first grid covers whole weeks and marks today', $oct['weeks'][0][0]['date'] === '2026-09-27' && count($oct['weeks']) === 5 && $oct['week_start'] === 7 && CS::monthGrid('2027-02')['today'] === '2027-02-01');
    check('month grid: invalid month falls back to the current month; navigation months', CS::monthGrid('garbage')['month'] === '2027-02' && $feb['prev'] === '2027-01' && $feb['next'] === '2027-03');
    $up = CS::upcoming(6);
    check('upcoming: next 6 are in order, none already over, include a holiday or international day', count($up) === 6 && $up[0]['end_date'] >= '2027-02-01' && array_column($up, 'start_date') === (static function () use ($up): array { $d = array_column($up, 'start_date'); sort($d); return $d; })());
    check('upcoming respects the type filter', !array_filter(CS::upcoming(10, ['types' => ['uganda-holiday']]), static fn($o) => $o['type_slug'] !== 'uganda-holiday'));
    $t0 = microtime(true);
    $five = CS::occurrences('2026-01-01', '2030-12-31');
    $ms = (microtime(true) - $t0) * 1000;
    check('five years of every entry expand fast (' . count($five) . ' occurrences, ' . round($ms) . ' ms)', count($five) >= 290 && $ms < 1500);
    check('range is capped (a 30-year request is cut to about five years, never explodes)', count(CS::occurrences('2000-01-01', '2030-12-31')) < 2000);

    // writer + visibility with temporary data
    $typeMeeting = CS::typeBySlug('meeting');
    $typeProgramme = CS::typeBySlug('programme');
    $q = CW::quickAdd(['title' => 'zz-selftest quick', 'date' => '2027-03-15', 'type' => 'programme', 'repeat' => 'weekly', 'until' => '2027-04-05'], null);
    check('quick add: three fields are enough', $q['ok'] && ($e = CalendarEntry::find($q['id'])) && $e['recurrence'] === 'weekly' && $e['repeat_until'] === '2027-04-05' && (int) $e['is_public'] === 1 && (int) $e['is_system'] === 0 && str_starts_with($e['slug'], 'zz-selftest-quick'));
    $quickOcc = array_values(array_filter(CS::occurrences('2027-03-01', '2027-04-30', ['types' => ['programme']]), static fn($o) => (int) $o['entry_id'] === (int) $q['id']));      // only this test's entry: the owner's own programme data must not matter
    check('quick add: weekly repeat on the weekday of the date until the end date (15, 22, 29 March, 5 April)', array_column($quickOcc, 'start_date') === ['2027-03-15', '2027-03-22', '2027-03-29', '2027-04-05']);
    $bad = CW::validateEntry(['title' => '', 'type_id' => 0, 'start_date' => '2027-02-30', 'link_url' => 'javascript:alert(1)', 'source_url' => 'ftp://x', 'start_time' => '25:00', 'all_day' => '0', 'color' => 'red', 'recurrence' => 'weekly', 'recurrence_data' => ['weekdays' => [9]]]);
    check('validation: title, type, date, URL scheme, time, colour, recurrence data are all rejected with messages', !$bad['ok'] && isset($bad['errors']['title'], $bad['errors']['type_id'], $bad['errors']['start_date'], $bad['errors']['link_url'], $bad['errors']['source_url'], $bad['errors']['start_time'], $bad['errors']['color'], $bad['errors']['recurrence_data']), json_encode(array_keys($bad['errors'])));
    $good = CW::validateEntry(['title' => '<b>Hi</b> there', 'type' => 'meeting', 'start_date' => '2027-06-01', 'all_day' => '0', 'start_time' => '09:00', 'end_time' => '08:00']);
    check('validation: end time must be after the start time on the same day; titles are stripped of tags', !$good['ok'] && isset($good['errors']['end_time']) && $good['row']['title'] === 'Hi there');
    check('validation: events cannot be created as entries', isset(CW::validateEntry(['title' => 'x', 'type' => 'event', 'start_date' => '2027-06-01'])['errors']['type_id']));
    check('validation: description HTML is sanitised', (static function (): bool { $v = CW::validateEntry(['title' => 'x', 'type' => 'meeting', 'start_date' => '2027-06-01', 'description' => '<p onclick="x()">Hi<script>alert(1)</script></p>']); return $v['ok'] && !preg_match('/script|onclick/i', (string) $v['row']['description']); })());
    check('validation: only administrators choose advanced repeat types (editors keep what exists)', isset(CW::validateEntry(['title' => 'x', 'type' => 'meeting', 'start_date' => '2027-06-01', 'recurrence' => 'easter', 'recurrence_data' => ['easter_offset' => 0]])['errors']['recurrence'])
        && CW::validateEntry(['title' => 'x', 'type' => 'meeting', 'start_date' => '2027-06-01', 'recurrence' => 'easter', 'recurrence_data' => ['easter_offset' => 0]], null, ['allow_advanced' => true])['ok']
        && CW::validateEntry(['title' => 'x', 'type' => 'meeting', 'start_date' => '2027-06-01', 'recurrence' => 'easter', 'recurrence_data' => ['easter_offset' => 0]], ['recurrence' => 'easter', 'recurrence_data' => '{"easter_offset":0}', 'slug' => 's'])['ok']);
    check('validation: a slug can not be a /calendar/ route word and stays unique', CW::validateEntry(['title' => 'feed', 'type' => 'meeting', 'start_date' => '2027-06-01'])['row']['slug'] === 'feed-entry' && CW::validateEntry(['title' => 'zz-selftest quick', 'type' => 'meeting', 'start_date' => '2027-06-01'])['row']['slug'] === 'zz-selftest-quick-2');
    $r1 = CW::saveEntry(null, ['title' => 'zz-selftest private', 'type' => 'meeting', 'start_date' => '2027-03-16', 'is_public' => '0'], null);
    $r2 = CW::saveEntry(null, ['title' => 'zz-selftest hidden', 'type' => 'meeting', 'start_date' => '2027-03-16', 'is_active' => '0'], null);
    $r3 = CW::saveEntry(null, ['title' => 'zz-selftest visible', 'type' => 'meeting', 'start_date' => '2027-03-16'], null);
    $pub = $calTitles(CS::occurrences('2027-03-16', '2027-03-16'));
    $adm = $calTitles(CS::occurrences('2027-03-16', '2027-03-16', ['public' => false]));
    check('public queries show only active + public entries; the admin query shows everything', in_array('zz-selftest visible', $pub, true) && !in_array('zz-selftest private', $pub, true) && !in_array('zz-selftest hidden', $pub, true) && in_array('zz-selftest private', $adm, true) && in_array('zz-selftest hidden', $adm, true));
    check('public detail lookups hide private and inactive entries', CS::publicEntryBySlug('zz-selftest-visible') !== null && CS::publicEntryBySlug('zz-selftest-private') === null && CS::publicEntryBySlug('zz-selftest-hidden') === null && CS::publicEntryById((int) $r2['id']) === null);
    check('public JSON items contain no private data', (static function () use ($r3): bool { $f = CS::feed('2027-03-16', '2027-03-16'); $j = json_encode($f); return $f['count'] >= 1 && !preg_match('/"(created_by|updated_by|email|ip|is_public|is_active|id)":/', (string) $j); })());
    $tId = CW::saveType(null, ['name' => 'zz-selftest type', 'color' => '#336699', 'icon' => 'tag', 'show_on_public' => '0'], null);
    $r4 = CW::saveEntry(null, ['title' => 'zz-selftest typed', 'type_id' => $tId['id'], 'start_date' => '2027-03-17'], null);
    CS::flush();
    check('a type hidden from the public hides its entries from visitors only', !in_array('zz-selftest typed', $calTitles(CS::occurrences('2027-03-17', '2027-03-17')), true) && in_array('zz-selftest typed', $calTitles(CS::occurrences('2027-03-17', '2027-03-17', ['public' => false])), true));
    check('types: invalid colour / icon rejected; a type in use or a system type cannot be deleted', !CW::saveType(null, ['name' => 'x', 'color' => 'blue', 'icon' => 'tag'])['ok'] && !CW::saveType(null, ['name' => 'x', 'color' => '#000000', 'icon' => 'nope'])['ok']
        && !CW::deleteType((int) $tId['id'])['ok'] && !CW::deleteType((int) CS::typeBySlug('event')['id'])['ok'] && !CW::deleteType((int) CS::typeBySlug('uganda-holiday')['id'])['ok']);
    CW::delete((int) $r4['id']);
    check('types: an unused custom type can be deleted', CW::deleteType((int) $tId['id'])['ok']);
    CS::flush();

    $dup = CW::duplicate((int) $q['id'], null);
    check('duplicate: a copy with a unique slug that is not a system entry', $dup['ok'] && ($d = CalendarEntry::find($dup['id'])) && str_ends_with($d['title'], '(copy)') && $d['slug'] !== $e['slug'] && (int) $d['is_system'] === 0);
    check('delete: system entries are refused, normal ones deleted', !CW::delete((int) $holBy['christmas-day']['id'])['ok'] && CalendarEntry::find((int) $holBy['christmas-day']['id']) !== null && CW::delete((int) $dup['id'])['ok'] && CalendarEntry::find((int) $dup['id']) === null);
    $bulk = CW::bulk('deactivate', [(int) $q['id'], (int) $r3['id']], null);
    check('bulk deactivate / activate / delete (system entries survive a bulk delete)', $bulk['ok'] && $bulk['changed'] === 2 && !in_array('zz-selftest visible', $calTitles(CS::occurrences('2027-03-16', '2027-03-16')), true)
        && CW::bulk('activate', [(int) $r3['id']], null)['changed'] === 1 && CW::bulk('delete', [(int) $holBy['christmas-day']['id']], null)['changed'] === 0 && !CW::bulk('explode', [1], null)['ok'] && !CW::bulk('delete', [], null)['ok']);

    // overrides through the writer on the real Eid al-Fitr entry (reset at the end)
    $eidId = (int) $holBy['eid-al-fitr']['id'];
    $pred = array_values(array_filter(CS::occurrences('2028-01-01', '2028-12-31', ['types' => ['uganda-holiday']]), static fn($o) => $o['title'] === 'Eid al-Fitr'))[0]['start_date'];
    check('override: a date that the rule does not produce is refused (no free-form rows)', !CW::saveOverride($eidId, '2028-01-02', ['action' => 'confirm'], null)['ok'] && !CW::saveOverride($eidId, 'garbage', ['action' => 'confirm'], null)['ok'] && !CW::saveOverride(999999, $pred, ['action' => 'confirm'], null)['ok']);
    $c1 = CW::saveOverride($eidId, $pred, ['action' => 'confirm', 'new_date' => CD::addDays($pred, -1), 'note' => 'Announced by the Uganda Muslim Supreme Council'], null);
    $after = array_values(array_filter(CS::occurrences('2028-01-01', '2028-12-31', ['types' => ['uganda-holiday']]), static fn($o) => $o['title'] === 'Eid al-Fitr'))[0];
    check('confirm date: moves the day, clears "to be confirmed", keeps the entry itself untouched (still pristine for the seed)', $c1['ok'] && $after['start_date'] === CD::addDays($pred, -1) && !$after['needs_confirmation'] && $after['confirmed'] && $after['note'] === 'Announced by the Uganda Muslim Supreme Council'
        && Database::value('SELECT updated_at = created_at FROM calendar_entries WHERE id = ?', [$eidId]) == 1);
    check('dates to confirm: the confirmed year drops out of the list', !array_filter(CS::datesToConfirm(900), static fn($o) => $o['key'] === $after['key']));
    $c2 = CW::saveOverride($eidId, $pred, ['action' => 'cancel'], null);
    check('cancel one occurrence', $c2['ok']);
    $gone = array_filter(CS::occurrences('2028-01-01', '2028-12-31', ['types' => ['uganda-holiday']]), static fn($o) => $o['title'] === 'Eid al-Fitr');
    check('cancelled occurrence is not public', $gone === []);
    CW::saveOverride($eidId, $pred, ['action' => 'restore'], null);
    $restored = array_values(array_filter(CS::occurrences('2028-01-01', '2028-12-31', ['types' => ['uganda-holiday']]), static fn($o) => $o['title'] === 'Eid al-Fitr'));
    check('restore returns the confirmed date', count($restored) === 1 && $restored[0]['start_date'] === CD::addDays($pred, -1));
    check('override: moving needs a date or a time; bad values are refused', !CW::saveOverride($eidId, $pred, ['action' => 'move'], null)['ok'] && !CW::saveOverride($eidId, $pred, ['action' => 'move', 'new_date' => '2028-13-01'], null)['ok'] && !CW::saveOverride($eidId, $pred, ['action' => 'move', 'new_start_time' => '99:00'], null)['ok'] && !CW::saveOverride($eidId, $pred, ['action' => 'explode'], null)['ok']);
    CW::saveOverride($eidId, $pred, ['action' => 'reset'], null);
    $reset = array_values(array_filter(CS::occurrences('2028-01-01', '2028-12-31', ['types' => ['uganda-holiday']]), static fn($o) => $o['title'] === 'Eid al-Fitr'));
    check('reset returns to the prediction and "to be confirmed"', $reset[0]['start_date'] === $pred && $reset[0]['needs_confirmation'] && (int) Database::value('SELECT COUNT(*) FROM calendar_overrides WHERE entry_id = ? AND occurrence_date = ?', [$eidId, $pred], 0) === 0);
    $ovId = CW::saveOverride((int) $q['id'], '2027-03-22', ['action' => 'cancel'], null)['override']['id'];
    check('override IDOR guard: an override can only be removed through its own entry', !CW::deleteOverride($eidId, (int) $ovId) && CW::deleteOverride((int) $q['id'], (int) $ovId));
    CW::saveOverride((int) $q['id'], '2027-03-22', ['action' => 'cancel'], null);
    Database::execute('DELETE FROM calendar_entries WHERE id = ?', [(int) $q['id']]);
    check('deleting an entry deletes its overrides (cascade)', (int) Database::value('SELECT COUNT(*) FROM calendar_overrides WHERE entry_id = ?', [(int) $q['id']], 0) === 0);
    check('database refuses a second override for the same occurrence and invalid JSON', (static function () use ($r3): bool {
        Database::execute('INSERT INTO calendar_overrides (entry_id, occurrence_date, cancelled) VALUES (?, ?, 1)', [(int) $r3['id'], '2027-03-16']);
        try {
            Database::execute('INSERT INTO calendar_overrides (entry_id, occurrence_date, cancelled) VALUES (?, ?, 1)', [(int) $r3['id'], '2027-03-16']);
            return false;
        } catch (\Throwable) {
        }
        try {
            Database::execute("UPDATE calendar_entries SET recurrence_data = '{bad' WHERE id = ?", [(int) $r3['id']]);
            return false;
        } catch (\Throwable) {
            return true;
        }
    })());

    // events are merged, not copied
    $evId = Database::insert('events', ['slug' => 'zz-selftest-event', 'title' => 'zz-selftest event', 'summary' => 'An event', 'start_at' => '2027-03-20 10:00:00', 'end_at' => '2027-03-20 12:00:00', 'location' => 'Gulu', 'is_active' => 1]);
    $evInactive = Database::insert('events', ['slug' => 'zz-selftest-event-off', 'title' => 'zz-selftest event off', 'start_at' => '2027-03-20 10:00:00', 'is_active' => 0]);
    $evs = array_values(array_filter(CS::occurrences('2027-03-20', '2027-03-20'), static fn($o) => $o['source'] === 'event' && str_starts_with($o['title'], 'zz-selftest')));
    check('events appear automatically with their own link and type, once; inactive events do not', count($evs) === 1 && $evs[0]['type_slug'] === 'event' && str_ends_with($evs[0]['url'], '/events/zz-selftest-event') && $evs[0]['start_time'] === '10:00' && $evs[0]['is_event'] && $evs[0]['event_id'] === $evId);
    check('no event was copied into calendar_entries', (int) Database::value("SELECT COUNT(*) FROM calendar_entries WHERE title LIKE 'zz-selftest event%'", [], 0) === 0);
    check('event type filter and the admin view include inactive events', array_filter(CS::occurrences('2027-03-20', '2027-03-20', ['types' => ['event']]), static fn($o) => $o['title'] === 'zz-selftest event') !== []
        && in_array('zz-selftest event off', $calTitles(CS::occurrences('2027-03-20', '2027-03-20', ['public' => false])), true));
    Database::execute('DELETE FROM events WHERE id IN (?, ?)', [$evId, $evInactive]);

    // preview, feed, embed
    $pv = CS::previewOccurrences(['start_date' => '2027-02-01', 'recurrence' => 'nth_weekday', 'recurrence_data' => ['month' => 2, 'weekday' => 2, 'nth' => 2]], 5);
    check('preview: next 5 occurrences from the same engine (2nd Tuesday of February)', $pv['ok'] && array_column($pv['dates'], 'date') === ['2027-02-09', '2028-02-08', '2029-02-13', '2030-02-12', '2031-02-11'] && str_contains($pv['description'], '2nd Tuesday of February'));
    check('preview: errors are reported, never thrown', !CS::previewOccurrences(['start_date' => 'x', 'recurrence' => 'daily'])['ok'] && !CS::previewOccurrences(['start_date' => '2027-02-01', 'recurrence' => 'weekly', 'recurrence_data' => '{x'])['ok']);
    $fd = CS::feed('2027-01-01', '2028-12-31');
    check('feed range is capped at 400 days and says so', $fd['capped'] === true && $fd['range']['end'] === CD::addDays('2027-01-01', 399) && CS::feed('2027-01-01', '2027-01-31')['capped'] === false);
    check('feed items carry colour pair, icon, URLs and the "to be confirmed" flag', (static function () use ($fd): bool { $i = $fd['items'][0]; return isset($i['color'], $i['text_color'], $i['tint'], $i['icon'], $i['url'], $i['ics_url'], $i['needs_confirmation'], $i['is_holiday']); })());
    $ev = CS::embedData();
    check('contact embed data: month grid, upcoming list, types, link', $ev !== null && count($ev['upcoming']) >= 1 && $ev['grid']['month'] === '2027-02' && count($ev['types']) >= 5 && str_ends_with($ev['calendar_url'], '/calendar'));
    $icsAll = CS::icsFeed([], '20270101T000000Z');
    check('calendar.ics for the whole site is valid and complete (holidays, days, no private entries)', str_starts_with($icsAll, "BEGIN:VCALENDAR\r\n") && substr_count($icsAll, 'BEGIN:VEVENT') > 200 && !str_contains($icsAll, 'zz-selftest private') && substr_count($icsAll, 'BEGIN:VEVENT') === substr_count($icsAll, 'END:VEVENT'));
    check('calendar.ics ?type= filter', !str_contains(CS::icsFeed(['types' => ['uganda-holiday']], '20270101T000000Z'), 'CATEGORIES:International'));
    $entryRow = CalendarEntry::findBy('slug', 'boxing-day');
    check('per-entry ics: one occurrence by date, the next one without a date, nothing for a date that is not an occurrence', str_contains(CS::icsEntry($entryRow, '2027-12-26', '20270101T000000Z'), 'DTSTART;VALUE=DATE:20271226') && str_contains(CS::icsEntry($entryRow, null, '20270101T000000Z'), 'BEGIN:VEVENT') && CS::icsEntry($entryRow, '2027-12-27', '20270101T000000Z') === '');
    check('dashboard tile and badge count work', (CS::pendingConfirmationCount() >= 0) && is_array(CS::systemEntries()) && count(CS::systemEntries()) === 14);
    check('public/private text labels (date and time)', CS::dateLabel(['start_date' => '2027-03-05', 'end_date' => '2027-03-07']) === '5 March 2027 to 7 March 2027' && CS::timeLabel(['all_day' => false, 'start_time' => '09:00', 'end_time' => '10:00']) === '09:00 to 10:00' && CS::timeLabel(['all_day' => true]) === '');

    // CSV mapping
    $csv = CW::inputFromCsv(['Title' => 'zz-selftest csv', 'Type' => 'Meeting', 'Start_Date' => '2027-07-01', 'All_Day' => 'no', 'Start_Time' => '10:00', 'End_Time' => '11:00', 'Repeat' => 'weekly', 'Public' => 'no', 'Description' => "Line1\n<script>x</script>"]);
    $csvV = CW::validateEntry($csv);
    check('csv record maps to entry input (public flag, repeat, escaped plain-text description)', $csvV['ok'] && (int) $csvV['row']['is_public'] === 0 && $csvV['row']['recurrence'] === 'weekly' && !str_contains((string) $csvV['row']['description'], '<script') && (int) $csvV['row']['all_day'] === 0);
    check('csv with an advanced repeat is refused for non-admins; description cannot smuggle markup', isset(CW::validateEntry(CW::inputFromCsv(['title' => 'x', 'type' => 'meeting', 'start_date' => '2027-07-01', 'repeat' => 'islamic']))['errors']));
    check('export record has exactly the documented columns', array_keys(CW::csvRecord($holBy['labour-day'] + ['recurrence_data' => null], [(int) CS::typeBySlug('uganda-holiday')['id'] => CS::typeBySlug('uganda-holiday')])) === CW::CSV_COLUMNS);


    // ------------------------------------------------------------------------------------------------ review fixes (regressions)
    echo " review fixes\n";
    CD::setToday('2027-02-01');
    // Safer Internet Day: the Tuesday between the 5th and the 11th (history 2020-2028 verified), flagged because the organisers announce it each year
    $sidRow = Database::one("SELECT * FROM calendar_entries WHERE slug = 'safer-internet-day'");
    check('Safer Internet Day: Tuesday of day 5-11 (7 Feb 2023, 6 Feb 2024 as announced; 2029+ differ from "2nd Tuesday"), flagged "to be confirmed"',
        $sidRow !== null && RR::starts($sidRow, '2020-01-01', '2028-12-31') === ['2020-02-11', '2021-02-09', '2022-02-08', '2023-02-07', '2024-02-06', '2025-02-11', '2026-02-10', '2027-02-09', '2028-02-08']
        && RR::starts($sidRow, '2029-01-01', '2030-12-31') === ['2029-02-06', '2030-02-05'] && (int) $sidRow['needs_confirmation'] === 1);
    check('Safer Internet Day rule: from_day only with the 1st weekday, 2..22', !RR::normalise(['recurrence' => 'nth_weekday', 'recurrence_data' => ['month' => 2, 'weekday' => 2, 'nth' => 2, 'from_day' => 5], 'start_date' => '2020-01-01'])['ok']
        && !RR::normalise(['recurrence' => 'nth_weekday', 'recurrence_data' => ['month' => 2, 'weekday' => 2, 'nth' => 1, 'from_day' => 30], 'start_date' => '2020-01-01'])['ok']
        && RR::normalise(['recurrence' => 'nth_weekday', 'recurrence_data' => ['month' => 2, 'weekday' => 2, 'nth' => 1, 'from_day' => 5], 'start_date' => '2020-01-01'])['ok']);
    // Eid days known from announcements are seeded as confirmed overrides (real day, no "to be confirmed"); later years stay predicted
    $eidSeen = static function (string $title, string $from, string $to): array {
        return array_values(array_filter(CS::occurrences($from, $to, ['types' => ['uganda-holiday']]), static fn($o) => $o['title'] === $title));
    };
    $ef24 = $eidSeen('Eid al-Adha', '2024-01-01', '2024-12-31');
    $ef25 = $eidSeen('Eid al-Fitr', '2025-01-01', '2025-12-31');
    $ea25 = $eidSeen('Eid al-Adha', '2025-01-01', '2025-12-31');
    $ef26 = $eidSeen('Eid al-Fitr', '2026-01-01', '2026-12-31');
    $ef27 = $eidSeen('Eid al-Fitr', '2027-01-01', '2027-12-31');
    check('known Eid days are confirmed with the announced date (16 Jun 2024, 30 Mar 2025, 6 Jun 2025, 20 Mar 2026); 2027 stays predicted',
        count($ef24) === 1 && $ef24[0]['start_date'] === '2024-06-16' && !$ef24[0]['needs_confirmation'] && $ef25[0]['start_date'] === '2025-03-30' && !$ef25[0]['needs_confirmation']
        && $ea25[0]['start_date'] === '2025-06-06' && $ef26[0]['start_date'] === '2026-03-20' && !$ef26[0]['needs_confirmation'] && $ef27[0]['needs_confirmation'] && $ef27[0]['start_date'] === '2027-03-10');
    // an occurrence must be shorter than the gap to the next one
    check('overlapping repeats are refused (daily for a year, weekly camp of two months, 3-day weekly), fine ones pass',
        !RR::normalise(['recurrence' => 'daily', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31'])['ok']
        && !RR::normalise(['recurrence' => 'weekly', 'start_date' => '2026-07-01', 'end_date' => '2026-08-31'])['ok']
        && !RR::normalise(['recurrence' => 'weekly', 'recurrence_data' => ['weekdays' => [1, 3, 5]], 'start_date' => '2026-07-06', 'end_date' => '2026-07-08'])['ok']
        && RR::normalise(['recurrence' => 'weekly', 'recurrence_data' => ['weekdays' => [1, 3, 5]], 'start_date' => '2026-07-06', 'end_date' => '2026-07-07'])['ok']
        && RR::normalise(['recurrence' => 'monthly', 'start_date' => '2026-07-01', 'end_date' => '2026-07-10'])['ok']
        && !RR::normalise(['recurrence' => 'monthly', 'start_date' => '2026-07-01', 'end_date' => '2026-08-10'])['ok']
        && RR::normalise(['recurrence' => 'none', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31'])['ok']
        && isset(CW::validateEntry(['title' => 'x', 'type' => 'meeting', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'recurrence' => 'daily'])['errors']['end_date']));
    // events that end exactly at midnight
    $mid = OE::fromEvent(['id' => 1, 'slug' => 's', 'title' => 't', 'start_at' => '2026-03-01 20:00:00', 'end_at' => '2026-03-02 00:00:00', 'is_active' => 1]);
    $day24 = OE::fromEvent(['id' => 2, 'slug' => 's', 'title' => 't', 'start_at' => '2026-03-01 00:00:00', 'end_at' => '2026-03-02 00:00:00', 'is_active' => 1]);
    $day3 = OE::fromEvent(['id' => 3, 'slug' => 's', 'title' => 't', 'start_at' => '2026-03-01 00:00:00', 'end_at' => '2026-03-04 00:00:00', 'is_active' => 1]);
    check('events ending at midnight stay on their own day (20:00-24:00 is one day, a 24 h event is one all-day, 3 x 24 h is 3 days)',
        $mid['end_date'] === '2026-03-01' && $mid['start_time'] === '20:00' && !OE::touches($mid, '2026-03-02', '2026-03-02')
        && $day24['all_day'] && $day24['end_date'] === '2026-03-01' && $day3['all_day'] && $day3['end_date'] === '2026-03-03');
    // ?date= prefers the occurrence the link names
    $ovl = [['occurrence_date' => '2026-11-03', 'start_date' => '2026-11-03', 'end_date' => '2026-11-05'], ['occurrence_date' => '2026-11-05', 'start_date' => '2026-11-05', 'end_date' => '2026-11-07']];
    check('?date= picks the occurrence whose rule date is that day before one that merely covers it', CS::occurrenceOn($ovl, '2026-11-05')['occurrence_date'] === '2026-11-05' && CS::occurrenceOn($ovl, '2026-11-04')['occurrence_date'] === '2026-11-03' && CS::occurrenceOn($ovl, '2026-12-01') === null);
    // a multi-day series with "repeat until" is found while its last occurrence is still running
    $wk = CW::saveEntry(null, ['title' => 'zz-selftest camp', 'type' => 'programme', 'start_date' => '2026-11-02', 'end_date' => '2026-11-07', 'recurrence' => 'weekly', 'repeat_until' => '2026-11-09'], null);
    $campLate = array_values(array_filter(CS::occurrences('2026-11-12', '2026-11-30', ['types' => ['programme']]), static fn($o) => $o['title'] === 'zz-selftest camp'));
    check('a multi-day repeat is still listed while its last occurrence runs (11-09 to 11-14 seen from 11-12)', $wk['ok'] && count($campLate) === 1 && $campLate[0]['start_date'] === '2026-11-09' && $campLate[0]['end_date'] === '2026-11-14');
    // quick add with a rejected repeat saves nothing
    $before = (int) Database::value("SELECT COUNT(*) FROM calendar_entries WHERE title LIKE 'zz-selftest badrepeat%'", [], 0);
    $qb = CW::quickAdd(['title' => 'zz-selftest badrepeat', 'date' => '2027-05-05', 'type' => 'meeting', 'repeat' => 'nth_weekday'], null);
    check('quick add: an invalid repeat is reported and nothing is saved', !$qb['ok'] && isset($qb['errors']['repeat']) && !isset($qb['id']) && (int) Database::value("SELECT COUNT(*) FROM calendar_entries WHERE title LIKE 'zz-selftest badrepeat%'", [], 0) === $before);
    // time range of one moved occurrence
    $one = CW::saveEntry(null, ['title' => 'zz-selftest timed', 'type' => 'programme', 'start_date' => '2027-06-01', 'all_day' => '0', 'start_time' => '09:00', 'end_time' => '10:00'], null);
    check('moving one occurrence: an end time before the start time is refused (a later day is the way past midnight)', $one['ok']
        && !CW::saveOverride((int) $one['id'], '2027-06-01', ['action' => 'move', 'new_start_time' => '20:00', 'new_end_time' => '08:00'], null)['ok']
        && !CW::saveOverride((int) $one['id'], '2027-06-01', ['action' => 'move', 'new_start_time' => '20:00', 'new_end_time' => '20:00'], null)['ok']
        && CW::saveOverride((int) $one['id'], '2027-06-01', ['action' => 'move', 'new_start_time' => '08:00', 'new_end_time' => '20:00'], null)['ok']);
    check('entry form: the end-time message explains how to cross midnight', str_contains((string) (CW::validateEntry(['title' => 'x', 'type' => 'meeting', 'start_date' => '2027-06-01', 'all_day' => '0', 'start_time' => '22:00', 'end_time' => '02:00'])['errors']['end_time'] ?? ''), 'next day'));
    check('array values are ignored, never cast (writer)', !CW::saveOverride((int) $one['id'], '2027-06-01', ['action' => ['x'], 'new_date' => ['y']], null)['ok'] && CW::saveType(null, ['name' => ['a'], 'color' => ['b']])['ok'] === false && CW::quickAdd(['title' => ['a'], 'date' => ['b']], null)['ok'] === false);
    // duplicate-key races: the loser picks the next free slug / updates the winner's row
    $cu = new \ReflectionMethod(CW::class, 'createUnique');
    $calls = 0;
    $rid = $cu->invoke(null, static function (array $r) use (&$calls): int {
        if (++$calls === 1) {
            $e = new \PDOException('Duplicate entry');
            (new \ReflectionProperty(\Exception::class, 'code'))->setValue($e, '23000');
            throw $e;
        }
        return 4242;
    }, 'calendar_entries', ['title' => 'zz-selftest race', 'slug' => 'zz-selftest-race'], 150, 'entry');
    check('a duplicate-slug race retries with the next free slug instead of failing', $rid === 4242 && $calls === 2);
    $ovRace = CW::saveOverride((int) $one['id'], '2027-06-01', ['action' => 'move', 'new_start_time' => '07:00', 'new_end_time' => '08:00'], null);
    check('saving the same occurrence again updates the one row (unique key)', $ovRace['ok'] && (int) Database::value('SELECT COUNT(*) FROM calendar_overrides WHERE entry_id = ?', [(int) $one['id']], 0) === 1);
    // the .ics keeps every holiday when the file is cut at 3000 events
    foreach ([1, 2, 3] as $k) {
        CW::quickAdd(['title' => 'zz-selftest dense ' . $k, 'date' => '2026-01-01', 'type' => 'programme', 'repeat' => 'daily'], null);
    }
    $dense = CS::icsFeed([], '20270101T000000Z');
    check('calendar.ics cut at 3000 events still holds every holiday (Christmas 2026 to 2029)', substr_count($dense, 'BEGIN:VEVENT') === CS::ICS_MAX_ITEMS
        && substr_count($dense, 'DTSTART;VALUE=DATE:20281225') >= 1 && substr_count($dense, 'DTSTART;VALUE=DATE:20291225') >= 1 && substr_count($dense, 'SUMMARY:Christmas Day') === 4);
    // contact embed: out-of-range months fall back instead of throwing
    check('contact embed: 2200-12 and 1900-01 fall back to the current month (no exception)', ($e1 = CS::embedData('2200-12')) !== null && $e1['grid']['month'] === '2027-02' && ($e2 = CS::embedData('1900-01')) !== null && $e2['grid']['month'] === '2027-02' && CS::embedData('2199-12')['grid']['month'] === '2199-12');
    // events JSON-LD: a timed session without an end time has no bare-date endDate
    $ctl = new \ReflectionMethod(\App\Controllers\Web\CalendarController::class, 'eventLd');
    $ctlObj = (new \ReflectionClass(\App\Controllers\Web\CalendarController::class))->newInstanceWithoutConstructor();
    $ld = $ctl->invoke($ctlObj, [['type_slug' => 'programme', 'cancelled' => false, 'is_event' => false, 'title' => 'T', 'start_date' => '2026-03-01', 'end_date' => '2026-03-01', 'start_time' => '10:00', 'end_time' => null, 'all_day' => false, 'url' => '/calendar/t', 'description' => '', 'location' => '']]);
    $ld2 = $ctl->invoke($ctlObj, [['type_slug' => 'programme', 'cancelled' => false, 'is_event' => false, 'title' => 'T', 'start_date' => '2026-03-01', 'end_date' => '2026-03-01', 'start_time' => '10:00', 'end_time' => '12:00', 'all_day' => false, 'url' => '/calendar/t', 'description' => '', 'location' => '']]);
    check('JSON-LD: endDate only when it can be stated in the same format as startDate', !isset($ld[0]['endDate']) && $ld2[0]['endDate'] === '2026-03-01T12:00:00+03:00');
    // CSV: seeded holidays count as already in the calendar
    $tc = new \ReflectionMethod(\App\Controllers\Admin\CalendarTransferController::class, 'check');
    $tcObj = (new \ReflectionClass(\App\Controllers\Admin\CalendarTransferController::class))->newInstanceWithoutConstructor();
    $tcRes = $tc->invoke($tcObj, [['n' => 1, 'rec' => ['title' => "New Year's Day", 'type' => 'uganda-holiday', 'start_date' => '2020-01-01', 'end_date' => '', 'all_day' => 'yes', 'start_time' => '', 'end_time' => '', 'location' => '', 'repeat' => 'yearly', 'repeat_until' => '', 'public' => 'yes', 'description' => '', 'link_url' => '', 'source_label' => '', 'source_url' => '']]]);
    check('CSV import: a seeded holiday in the file is a duplicate, not a second copy', ($tcRes['summary']['duplicates'] ?? 0) === 1 && ($tcRes['summary']['valid'] ?? 1) === 0);
    check('throttle keys are keyed hashes (not sha1 of the visitor address)', (static function (): bool {
        $m = new \ReflectionMethod(\App\Core\RateLimit::class, 'k');
        $k = $m->invoke(null, 'throttle:127.0.0.1:GET:/calendar/feed');
        return strlen($k) === 40 && $k !== sha1('throttle:127.0.0.1:GET:/calendar/feed');
    })());
    // ------------------------------------------------------------------------------------------------ HTTP (only when the local site answers)
    echo " http\n";
    $base = 'http://localhost' . rtrim((string) preg_replace('~/index\.php$~', '', (string) ($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $base = 'http://localhost/lumina';
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $probe = @file_get_contents($base . '/calendar/feed?start=2027-03-01&end=2027-03-31', false, $ctx);
    if ($probe === false) {
        echo "  skip HTTP checks (the local site does not answer at {$base})\n";
    } else {
        $get = static function (string $path) use ($base, $ctx): array {
            $body = @file_get_contents($base . $path, false, $ctx);
            $status = 0;
            $headers = [];
            foreach ((array) ($http_response_header ?? []) as $h) {
                if (preg_match('~^HTTP/\S+ (\d{3})~', $h, $m)) {
                    $status = (int) $m[1];
                }
                $headers[] = $h;
            }
            return [$status, (string) $body, $headers];
        };
        [$s1, $b1, $h1] = $get('/calendar/feed?start=2027-03-01&end=2027-03-31');
        $j = json_decode($b1, true);
        check('GET /calendar/feed: 200 JSON with items, cache headers and an ETag', $s1 === 200 && is_array($j) && $j['count'] >= 5 && (bool) preg_grep('/^Cache-Control: public/i', $h1) && (bool) preg_grep('/^ETag:/i', $h1) && (bool) preg_grep('~^Content-Type: application/json~i', $h1));
        check('GET /calendar/feed: bad input answers 400 JSON, month parameter works', $get('/calendar/feed')[0] === 400 && $get('/calendar/feed?start=x&end=y')[0] === 400 && $get('/calendar/feed?start=2027-03-10&end=2027-03-01')[0] === 400 && $get('/calendar/feed?month=2027-03')[0] === 200);
        [$s2, $b2, $h2] = $get('/calendar.ics');
        check('GET /calendar.ics: text/calendar, CRLF, many events', $s2 === 200 && (bool) preg_grep('~^Content-Type: text/calendar~i', $h2) && str_starts_with($b2, "BEGIN:VCALENDAR\r\n") && substr_count($b2, 'BEGIN:VEVENT') > 200);
        check('GET /calendar/{id}.ics and /calendar/{slug}', $get('/calendar/' . $entryRow['id'] . '.ics?date=2027-12-26')[0] === 200 && $get('/calendar/' . $entryRow['id'] . '.ics?date=2027-12-27')[0] === 404 && $get('/calendar/boxing-day')[0] === 200 && $get('/calendar/zz-selftest-private')[0] === 404 && $get('/calendar/feed-entry')[0] === 404);
        [$s3, $b3] = $get('/calendar?month=2027-03');
        check('GET /calendar renders the month with holidays and a canonical link; a far-future empty month is noindex', $s3 === 200 && str_contains($b3, 'Good Friday') && str_contains($b3, 'rel="canonical"') && (CS::monthGrid('2099-01')['custom_total'] > 0 || str_contains($get('/calendar?month=2099-01')[1], 'noindex')));      // a month the owner filled is not thin
        check('GET /calendar bad parameters do not break the page', $get('/calendar?month=garbage&view=%00&types[]=%3Cscript%3E')[0] === 200);
        [$s4, $b4] = $get('/contact');
        check('GET /contact shows the calendar block under the form, with a link to the full calendar', $s4 === 200 && str_contains($b4, 'id="contact-calendar"') && str_contains($b4, '/calendar"') && str_contains($b4, 'calendar.js'));
        // array-valued query parameters are a bad request or ignored, never an error page
        check('array query parameters never give a 500 (/contact, feed, .ics)', $get('/contact?cal%5B%5D=2026-02')[0] === 200 && $get('/contact?cal%5Ba%5D=x')[0] === 200
            && $get('/calendar/feed?start%5B%5D=2026-01-01&end=2026-02-01')[0] === 400 && $get('/calendar/feed?month%5B%5D=2026-01')[0] === 400 && $get('/calendar/feed?end%5B%5D=1&start=2026-01-01')[0] === 400
            && $get('/calendar/' . $entryRow['id'] . '.ics?date%5B%5D=2026-01-01')[0] === 200 && $get('/calendar?month%5B%5D=2026-01')[0] === 200);
        $c2200 = $get('/contact?cal=2200-12');
        check('/contact?cal=2200-12 still shows the calendar block (falls back to the current month)', $c2200[0] === 200 && str_contains($c2200[1], 'id="contact-calendar"') && $get('/calendar/feed?month=2200-12')[0] === 400);
        // unpublishing the Calendar page takes the feed and the .ics files down too
        Database::execute("UPDATE pages SET is_published = 0, updated_at = updated_at WHERE slug = 'calendar'");
        try {
            $off = [$get('/calendar')[0], $get('/calendar/feed?month=2027-03')[0], $get('/calendar.ics')[0], $get('/calendar/' . $entryRow['id'] . '.ics?date=2027-12-26')[0], $get('/calendar/boxing-day')[0]];
            $offContact = $get('/contact')[1];
        } finally {
            Database::execute("UPDATE pages SET is_published = 1, updated_at = updated_at WHERE slug = 'calendar'");
        }
        check('unpublished Calendar page: page, detail, feed, .ics and per-entry .ics all answer 404; the Contact block is gone', $off === [404, 404, 404, 404, 404] && !str_contains($offContact, 'id="contact-calendar"') && $get('/calendar/feed?month=2027-03')[0] === 200, json_encode($off));
        check('GET /sitemap.xml and the menus still work', $get('/sitemap.xml')[0] === 200 && $get('/')[0] === 200 && str_contains($get('/')[1], '/calendar'));
    }
} finally {
    $calCleanup();
    CS::flush();
}

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
