<?php
declare(strict_types=1);

namespace App\Services\Meetings;

use App\Services\Calendar\CivilDate;

/**
 * Time helpers of the meeting scheduler. Pure PHP (no DateTime, no intl): every meeting time is a LOCAL datetime string 'Y-m-d H:i:s' in the
 * application time zone Africa/Kampala = EAT = UTC+3 with no daylight saving, so conversion to UTC is a fixed 3-hour shift.
 * Docs: docs/MEETINGS.md section 5.
 */
final class MeetingTime
{
    public const TZ = 'Africa/Kampala';
    public const LABEL = 'EAT';
    public const OFFSET = 10800;                       // seconds east of UTC
    /** Duration presets of the schedule form (minutes). A custom end time is also allowed. */
    public const DURATIONS = [15, 30, 45, 60, 90];
    public const MAX_MINUTES = 720;                    // a meeting lasts at most 12 hours and ends on the day it starts

    /** Test hook: freeze "now" (epoch seconds, UTC). */
    private static ?int $frozen = null;

    public static function freeze(?int $utcEpoch): void
    {
        self::$frozen = $utcEpoch;
    }

    public static function nowTimestamp(): int
    {
        return self::$frozen ?? time();
    }

    /** Current EAT datetime 'Y-m-d H:i:s'. */
    public static function now(): string
    {
        return self::fromTimestamp(self::nowTimestamp());
    }

    public static function today(): string
    {
        return substr(self::now(), 0, 10);
    }

    /** 'Y-m-d H:i[:s]' (also 'Y-m-dTH:i') -> 'Y-m-d H:i:s', or null when it is not a real date and time. */
    public static function normalise(mixed $dt): ?string
    {
        if (!is_string($dt) || preg_match('/^(\d{4}-\d{2}-\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/', trim($dt), $m) !== 1) {
            return null;
        }
        if (!CivilDate::valid($m[1]) || (int) $m[2] > 23 || (int) $m[3] > 59 || (int) ($m[4] ?? 0) > 59) {
            return null;
        }
        return sprintf('%s %02d:%02d:%02d', $m[1], (int) $m[2], (int) $m[3], (int) ($m[4] ?? 0));
    }

    public static function combine(string $date, string $time): ?string
    {
        return self::normalise(trim($date) . ' ' . trim($time));
    }

    /** UTC epoch of a local EAT datetime. */
    public static function toTimestamp(string $local): int
    {
        $n = self::normalise($local) ?? throw new \InvalidArgumentException('Invalid meeting time.');
        return gmmktime((int) substr($n, 11, 2), (int) substr($n, 14, 2), (int) substr($n, 17, 2), (int) substr($n, 5, 2), (int) substr($n, 8, 2), (int) substr($n, 0, 4)) - self::OFFSET;
    }

    public static function fromTimestamp(int $utcEpoch): string
    {
        return gmdate('Y-m-d H:i:s', $utcEpoch + self::OFFSET);
    }

    /** '20261024T070000Z' */
    public static function utcStamp(string $local): string
    {
        return gmdate('Ymd\THis\Z', self::toTimestamp($local));
    }

    /** '2026-10-24T07:00:00Z' (machine-readable, for <time datetime> and the browser's "your local time" hint) */
    public static function utcIso(string $local): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', self::toTimestamp($local));
    }

    public static function date(string $local): string
    {
        return substr($local, 0, 10);
    }

    /** 'HH:MM' */
    public static function time(string $local): string
    {
        return substr($local, 11, 5);
    }

    public static function addMinutes(string $local, int $minutes): string
    {
        return self::fromTimestamp(self::toTimestamp($local) + $minutes * 60);
    }

    public static function minutesBetween(string $startLocal, string $endLocal): int
    {
        return intdiv(self::toTimestamp($endLocal) - self::toTimestamp($startLocal), 60);
    }

    /** "Sat 24 Oct 2026, 10:00-11:00 EAT" (with an en dash). Used in subjects, e-mails and the admin. */
    public static function rangeLabel(string $start, string $end): string
    {
        return CivilDate::fmt(self::date($start), 'D j M Y') . ', ' . self::timeRange($start, $end);
    }

    /** "10:00-11:00 EAT" (with an en dash) */
    public static function timeRange(string $start, string $end): string
    {
        return self::time($start) . "\u{2013}" . self::time($end) . ' ' . self::LABEL;
    }

    /** "Saturday 24 October 2026" */
    public static function dateLabel(string $start): string
    {
        return CivilDate::fmt(self::date($start), 'l j F Y');
    }

    /** "1 hour 30 minutes" */
    public static function durationLabel(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        $parts = [];
        if ($h > 0) {
            $parts[] = $h . ($h === 1 ? ' hour' : ' hours');
        }
        if ($m > 0 || $h === 0) {
            $parts[] = $m . ($m === 1 ? ' minute' : ' minutes');
        }
        return implode(' ', $parts);
    }

    /** Has the meeting finished at $now (default: now)? Works on a meeting row. */
    public static function hasEnded(array $meeting, ?string $now = null): bool
    {
        return (string) ($meeting['end_at'] ?? '') !== '' && (string) $meeting['end_at'] <= ($now ?? self::now());
    }

    public static function hasStarted(array $meeting, ?string $now = null): bool
    {
        return (string) ($meeting['start_at'] ?? '') !== '' && (string) $meeting['start_at'] <= ($now ?? self::now());
    }

    /**
     * How far away a start is, in words that are TRUE at $now (default: now): "now", "in 5 minutes", "in 1 hour", "in 23 hours", "in 3 days". Used by the reminders,
     * which can be late (cron outage) or early ("Send reminder now"), so they must never promise "24 hours" when it is not.
     */
    public static function untilLabel(string $startAt, ?string $now = null): string
    {
        $mins = (int) round((self::toTimestamp($startAt) - self::toTimestamp($now ?? self::now())) / 60);
        if ($mins <= 0) {
            return 'now';
        }
        if ($mins < 60) {
            return 'in ' . $mins . ($mins === 1 ? ' minute' : ' minutes');
        }
        if ($mins < 36 * 60) {
            $h = (int) round($mins / 60);
            return 'in ' . $h . ($h === 1 ? ' hour' : ' hours');
        }
        $d = (int) round($mins / 1440);
        return 'in ' . $d . ($d === 1 ? ' day' : ' days');
    }

    /** The moment a reminder $hoursBefore the start is due (EAT datetime). */
    public static function reminderAt(string $startAt, int $hoursBefore): string
    {
        return self::addMinutes($startAt, -60 * $hoursBefore);
    }

    /**
     * Validate the schedule part of the form: date (Y-m-d), start time (HH:MM) and EITHER end time (HH:MM, wins when given) OR duration (minutes).
     * @return array{ok:bool,errors:array<string,string>,start_at:?string,end_at:?string,duration:?int}
     */
    public static function fromForm(mixed $date, mixed $startTime, mixed $duration, mixed $endTime): array
    {
        $errors = [];
        $d = is_scalar($date) ? trim((string) $date) : '';
        $st = is_scalar($startTime) ? trim((string) $startTime) : '';
        $start = null;
        if ($d === '' || !CivilDate::valid($d)) {
            $errors['date'] = 'Choose the date of the meeting.';
        } elseif ($st === '' || CivilDate::time($st) === null) {
            $errors['start_time'] = 'Enter the start time as HH:MM (24 hours, East Africa Time).';
        } else {
            $start = self::combine($d, (string) CivilDate::time($st));
        }
        $end = null;
        $et = is_scalar($endTime) ? trim((string) $endTime) : '';
        $dur = is_scalar($duration) && ctype_digit(trim((string) $duration)) ? (int) trim((string) $duration) : null;
        if ($start !== null) {
            if ($et !== '') {
                if (CivilDate::time($et) === null) {
                    $errors['end_time'] = 'Enter the end time as HH:MM (24 hours, East Africa Time).';
                } else {
                    $end = self::combine($d, (string) CivilDate::time($et));
                }
            } elseif ($dur !== null && $dur >= 5 && $dur <= self::MAX_MINUTES) {
                $end = self::addMinutes($start, $dur);
            } else {
                $errors['duration'] = 'Choose how long the meeting lasts, or enter an end time.';
            }
            if ($end !== null && !isset($errors['end_time'])) {
                if (self::date($end) !== self::date($start)) {
                    $errors['end_time'] = 'A meeting must end on the day it starts. For a longer event, use the calendar.';
                } elseif ($end <= $start) {
                    $errors['end_time'] = 'The end time must be after the start time.';
                } elseif (self::minutesBetween($start, $end) > self::MAX_MINUTES) {
                    $errors['end_time'] = 'A meeting can last at most 12 hours.';
                }
            }
        }
        $ok = $errors === [] && $start !== null && $end !== null;
        return ['ok' => $ok, 'errors' => $errors, 'start_at' => $ok ? $start : null, 'end_at' => $ok ? $end : null, 'duration' => $ok ? self::minutesBetween((string) $start, (string) $end) : null];
    }
}
