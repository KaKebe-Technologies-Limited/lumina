<?php
declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;

/**
 * Server side of the top-bar clock (partials/topbar.php + the [data-clock] ticker in assets/js/main.js).
 * Pure and testable: every method takes its input as arguments (build() also takes the "now" instant), so bin/selftest.php can
 * check time zone validation, DST, 12/24 hour formats and the show_seconds / show_date switches without a browser.
 *
 * Settings live in the registry page "global", section "clock" (Admin > Pages > Site-wide content): clock.enabled, clock.mode
 * (org|visitor), clock.timezone (IANA name), clock.hour12, clock.show_seconds, clock.show_date, clock.label, clock.aria_label.
 */
final class SiteClock
{
    public const DEFAULT_TZ = 'Africa/Kampala';

    private const DEFAULTS = [
        'enabled' => true, 'mode' => 'org', 'timezone' => self::DEFAULT_TZ, 'hour12' => false,
        'show_seconds' => true, 'show_date' => true, 'label' => 'Uganda time', 'aria_label' => 'Current date and time',
    ];

    /** @var array<string,string>|null lower-cased name => canonical IANA name */
    private static ?array $zones = null;

    /**
     * A valid IANA identifier (canonical spelling) or DEFAULT_TZ. Case-insensitive, trims, rejects anything not returned by
     * DateTimeZone::listIdentifiers() (so "UTC+3", "EAT", "Mars/Base" and junk all fall back).
     */
    public static function resolveTimezone(?string $tz): string
    {
        if (self::$zones === null) {
            self::$zones = [];
            foreach (DateTimeZone::listIdentifiers() as $id) {
                self::$zones[strtolower($id)] = $id;
            }
        }
        $key = strtolower(trim((string) $tz));
        return self::$zones[$key] ?? self::DEFAULT_TZ;
    }

    /** Clock settings from the registry (typed, validated). */
    public static function settings(): array
    {
        $get = static fn (string $k) => Content::get('global', 'clock.' . $k, self::DEFAULTS[$k]);
        return self::normalise([
            'enabled' => $get('enabled'), 'mode' => $get('mode'), 'timezone' => $get('timezone'), 'hour12' => $get('hour12'),
            'show_seconds' => $get('show_seconds'), 'show_date' => $get('show_date'), 'label' => $get('label'), 'aria_label' => $get('aria_label'),
        ]);
    }

    /** Coerce raw settings to their types (invalid time zone -> DEFAULT_TZ, unknown mode -> org). */
    public static function normalise(array $raw): array
    {
        $o = $raw + self::DEFAULTS;
        return [
            'enabled'      => (bool) $o['enabled'],
            'mode'         => $o['mode'] === 'visitor' ? 'visitor' : 'org',
            'timezone'     => self::resolveTimezone(is_scalar($o['timezone']) ? (string) $o['timezone'] : ''),
            'hour12'       => (bool) $o['hour12'],
            'show_seconds' => (bool) $o['show_seconds'],
            'show_date'    => (bool) $o['show_date'],
            'label'        => trim((string) $o['label']),
            'aria_label'   => trim((string) $o['aria_label']),
        ];
    }

    /**
     * Everything the partial needs for one render.
     *
     * @param array $opts normalised settings (see settings() / normalise())
     * @return array{epoch:int,iso:string,date:string,year:string,time:string,abbr:string,offset:int,tz:string,mode:string,hour12:bool,seconds:bool,show_date:bool,label:string,aria_label:string}
     */
    public static function build(array $opts, ?DateTimeImmutable $now = null): array
    {
        $o = self::normalise($opts);
        $dt = ($now ?? new DateTimeImmutable('now'))->setTimezone(new DateTimeZone($o['timezone']));
        $time = $dt->format($o['hour12'] ? ($o['show_seconds'] ? 'h:i:s A' : 'h:i A') : ($o['show_seconds'] ? 'H:i:s' : 'H:i'));
        return [
            'epoch'      => $dt->getTimestamp(),        // the server's instant (Unix seconds): organisation mode keeps time with it, not with the visitor's device clock
            'iso'        => $dt->format('c'),
            'date'       => $dt->format('D, j M'),      // "Fri, 16 Oct" (the year is a separate span so phones can drop it)
            'year'       => $dt->format('Y'),
            'time'       => $time,
            'abbr'       => self::abbreviation($dt),
            'offset'     => intdiv($dt->getOffset(), 60),   // minutes east of UTC; lets the script tell when its own zone name is the same zone
            'tz'         => $o['timezone'],
            'mode'       => $o['mode'],
            'hour12'     => $o['hour12'],
            'seconds'    => $o['show_seconds'],
            'show_date'  => $o['show_date'],
            'label'      => $o['mode'] === 'org' ? $o['label'] : '',
            'aria_label' => $o['aria_label'],
        ];
    }

    /** "EAT", "BST", "GMT"; zones without a letter abbreviation (PHP gives "+03", "-0330") become "GMT+3" / "GMT-3:30" like Intl does. */
    public static function abbreviation(DateTimeImmutable $dt): string
    {
        $abbr = $dt->format('T');
        if ($abbr !== '' && ($abbr[0] === '+' || $abbr[0] === '-')) {
            $min = intdiv($dt->getOffset(), 60);
            $h = intdiv(abs($min), 60);
            $m = abs($min) % 60;
            return 'GMT' . ($min < 0 ? '-' : '+') . $h . ($m ? ':' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) : '');
        }
        return $abbr !== '' ? $abbr : 'UTC';
    }

    /** Plain text as the page prints it, e.g. "Fri, 16 Oct 2026 · 14:35:09 EAT" (used by tests and for the no-script fallback title). */
    public static function text(array $c): string
    {
        return ($c['show_date'] ? $c['date'] . ' ' . $c['year'] . ' · ' : '') . $c['time'] . ' ' . $c['abbr'];
    }

    /** Safe wrapper for views: never throws (a broken clock must not take the page down). */
    public static function forView(): ?array
    {
        try {
            $s = self::settings();
            return $s['enabled'] ? self::build($s) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
