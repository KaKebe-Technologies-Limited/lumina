<?php
declare(strict_types=1);

namespace App\Services\Calendar;

use App\Core\Database;
use App\Core\Request;
use App\Core\Sanitizer;
use Throwable;

/**
 * READ side of the calendar: queries, month grid, agenda, upcoming list, JSON feed, iCal feeds, dates-to-confirm, previews.
 * (Write side: CalendarWriter.) Static, pure application logic over the date engine; docs/CALENDAR.md has every signature and shape.
 *
 * Performance contract: a range query runs a CONSTANT number of SQL statements (types are cached per request, then one for entries,
 * one for their overrides, one for events), whatever the number of entries or days. No query inside a loop.
 */
final class CalendarService
{
    /** longest range occurrences() will expand (about 5 years); the public JSON feed is capped lower */
    public const MAX_INTERNAL_DAYS = 1900;
    public const FEED_MAX_DAYS = 400;
    /** most items the public JSON feed returns (a month grid holds well under 200) */
    public const FEED_MAX_ITEMS = 1500;
    /** most events in /calendar.ics; the holidays and international days are always kept, the rest is cut chronologically */
    public const ICS_MAX_ITEMS = 3000;
    /** months a visitor may navigate to (public page, Contact block); the grid of 1900 / 2200 would reach outside the supported years */
    public const MIN_MONTH = '1901-01';
    public const MAX_MONTH = '2199-12';
    public const SLUG_EVENT = 'event';
    public const SLUG_HOLIDAY = 'uganda-holiday';

    /** @var list<array<string,mixed>>|null */
    private static ?array $types = null;
    private static ?bool $available = null;
    private static ?int $pending = null;

    // ------------------------------------------------------------------ availability and settings

    /** False until the three tables exist (a live database imported before the code upload must not 500 on /contact). */
    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Database::tableExists('calendar_entries') && Database::tableExists('calendar_types') && Database::tableExists('calendar_overrides');
            } catch (Throwable) {
                self::$available = false;
            }
        }
        return self::$available;
    }

    /** Forget per-request caches (tests, and after the admin changed types or entries in the same request). */
    public static function flush(): void
    {
        self::$types = null;
        self::$available = null;
        self::$pending = null;
    }

    /** ISO weekday the grid starts on: 1 (Monday, default), 7 (Sunday) or 6 (Saturday). Setting calendar_week_start. */
    public static function weekStart(): int
    {
        return match (strtolower(trim((string) setting('calendar_week_start', 'monday')))) {
            'sunday', '7', '0' => 7,
            'saturday', '6' => 6,
            default => 1,
        };
    }

    /** Type slugs pre-selected in the filters (setting calendar_default_types, comma separated; empty = all public types). */
    public static function defaultTypeSlugs(): array
    {
        $out = [];
        foreach (explode(',', (string) setting('calendar_default_types', '')) as $s) {
            $s = trim(strtolower($s));
            if ($s !== '' && preg_match('/^[a-z0-9-]{1,60}$/', $s)) {
                $out[$s] = $s;
            }
        }
        return array_values($out);
    }

    /** The contact-page block is on (setting AND registry section switch AND the calendar page is published). */
    public static function showOnContact(): bool
    {
        return self::available()
            && (string) setting('calendar_show_on_contact', '1') !== '0'
            && section_on('contact', 'calendar')
            && \App\Core\SitePages::isPublished('calendar');
    }

    // ------------------------------------------------------------------ types

    /**
     * Every type, ordered, with its accessible colour set under 'pair'. Cached per request.
     * @return list<array<string,mixed>>
     */
    public static function types(): array
    {
        if (self::$types === null) {
            self::$types = [];
            if (self::available()) {
                foreach (Database::all('SELECT * FROM calendar_types ORDER BY sort_order ASC, id ASC') as $t) {
                    $t['id'] = (int) $t['id'];
                    $t['sort_order'] = (int) $t['sort_order'];
                    foreach (['is_active', 'show_on_public', 'is_system'] as $f) {
                        $t[$f] = (int) $t[$f] === 1;
                    }
                    $t['pair'] = ColorContrast::pair((string) $t['color']);
                    self::$types[] = $t;
                }
            }
        }
        return self::$types;
    }

    /** Types visitors see (active and show_on_public). @return list<array<string,mixed>> */
    public static function publicTypes(): array
    {
        return array_values(array_filter(self::types(), static fn(array $t): bool => $t['is_active'] && $t['show_on_public']));
    }

    public static function typeById(int $id): ?array
    {
        foreach (self::types() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }
        return null;
    }

    public static function typeBySlug(string $slug): ?array
    {
        foreach (self::types() as $t) {
            if ($t['slug'] === $slug) {
                return $t;
            }
        }
        return null;
    }

    // ------------------------------------------------------------------ occurrences

    /**
     * Every occurrence touching [$from, $to] (inclusive, 'YYYY-MM-DD'), sorted, decorated with the type and the colour pair.
     *
     * $filters (all optional):
     *   types               list of type slugs (unknown slugs ignored)
     *   public              bool, default TRUE: only is_active + is_public entries of visible types, no inactive events.
     *                       FALSE (admin): everything, flagged through is_public / is_active
     *   include_cancelled   bool (admin): keep occurrences an override cancelled (cancelled = true)
     *   include_inactive    bool (admin, with public=false; default true there): inactive entries and events
     *   q                   text searched in title and location
     *   entry_id            only this entry
     *   only_unconfirmed    bool: only occurrences that still need confirmation
     *   source              'entry' | 'event'
     *
     * @return list<array<string,mixed>>
     */
    public static function occurrences(string $from, string $to, array $filters = []): array
    {
        if (!self::available() || !CivilDate::valid($from) || !CivilDate::valid($to) || $from > $to) {
            return [];
        }
        $to = min($to, CivilDate::addDays($from, self::MAX_INTERNAL_DAYS));
        $public = ($filters['public'] ?? true) !== false;
        $includeInactive = !$public && ($filters['include_inactive'] ?? true) !== false;

        $visible = [];
        $bySlug = [];
        foreach (self::types() as $t) {
            if (!$public || ($t['is_active'] && $t['show_on_public'])) {
                $visible[$t['id']] = $t;
                $bySlug[$t['slug']] = $t['id'];
            }
        }
        if (!empty($filters['types']) && is_array($filters['types'])) {
            $want = [];
            foreach ($filters['types'] as $slug) {
                if (is_string($slug) && isset($bySlug[$slug])) {
                    $want[$bySlug[$slug]] = true;
                }
            }
            $visible = array_intersect_key($visible, $want);
        }
        if ($visible === []) {
            return [];
        }

        $source = (string) ($filters['source'] ?? '');
        $q = trim((string) ($filters['q'] ?? ''));
        $like = $q === '' ? null : '%' . strtr(mb_substr($q, 0, 100), ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']) . '%';
        $out = [];

        // ---- 1. entries (one query) + their overrides (one query)
        $eventTypeId = $bySlug[self::SLUG_EVENT] ?? null;
        $entryTypeIds = array_keys(array_diff_key($visible, $eventTypeId !== null ? [$eventTypeId => true] : []));
        if ($source !== 'event' && ($entryTypeIds !== [] || $eventTypeId !== null)) {
            $typeIds = array_keys($visible);
            $sql = 'SELECT e.* FROM calendar_entries e WHERE e.type_id IN (' . implode(',', array_fill(0, count($typeIds), '?')) . ')';
            $params = $typeIds;
            if ($public) {
                $sql .= ' AND e.is_active = 1 AND e.is_public = 1';
            } elseif (!$includeInactive) {
                $sql .= ' AND e.is_active = 1';
            }
            $sql .= " AND ((e.start_date <= ? AND (e.recurrence <> 'none' OR COALESCE(e.end_date, e.start_date) >= ?) AND (e.repeat_until IS NULL OR e.repeat_until >= ?))"
                . ' OR EXISTS (SELECT 1 FROM calendar_overrides o WHERE o.entry_id = e.id AND o.new_date BETWEEN ? AND ?))';
            // repeat_until is the last day an occurrence may START, and a multi-day occurrence runs up to MAX_DURATION_DAYS past it
            array_push($params, $to, $from, CivilDate::addDays($from, -RecurrenceRules::MAX_DURATION_DAYS), CivilDate::addDays($from, -RecurrenceRules::MAX_DURATION_DAYS), $to);
            if (!empty($filters['entry_id'])) {
                $sql .= ' AND e.id = ?';
                $params[] = (int) $filters['entry_id'];
            }
            if (!empty($filters['only_unconfirmed'])) {
                $sql .= ' AND e.needs_confirmation = 1';
            }
            if ($like !== null) {
                $sql .= " AND (e.title LIKE ? ESCAPE '\\\\' OR e.location LIKE ? ESCAPE '\\\\')";
                array_push($params, $like, $like);
            }
            // seeded holidays and international days first: if a limit ever cuts the list, the owner's own entries go, never the holidays
            $sql .= ' ORDER BY e.is_system DESC, e.start_date ASC, e.id ASC LIMIT 2000';
            $entries = Database::all($sql, $params);
            if ($entries !== []) {
                $overrides = self::overridesFor(array_map(static fn(array $e): int => (int) $e['id'], $entries), $from, $to);
                $occ = OccurrenceExpander::expandEntries($entries, $overrides, $from, $to, ['include_cancelled' => !empty($filters['include_cancelled'])]);
                if (OccurrenceExpander::$truncated) {
                    \App\Core\Log::warning('Calendar range ' . $from . ' to ' . $to . ' was cut at the occurrence limit; check for entries that repeat too densely.');
                }
                foreach ($occ as $o) {
                    $t = $visible[(int) $o['type_id']] ?? null;
                    if ($t !== null) {
                        $out[] = self::decorate($o, $t);
                    }
                }
            }
        }

        // ---- 2. events (one query) as occurrences of the "event" type; nothing is copied into calendar_entries
        if ($eventTypeId !== null && isset($visible[$eventTypeId]) && $source !== 'entry' && empty($filters['only_unconfirmed']) && empty($filters['entry_id'])) {
            $sql = 'SELECT id, slug, title, summary, start_at, end_at, location, is_active FROM events WHERE start_at <= ? AND COALESCE(end_at, start_at) >= ?';
            $params = [$to . ' 23:59:59', $from . ' 00:00:00'];
            if (!$includeInactive) {
                $sql .= ' AND is_active = 1';
            }
            if ($like !== null) {
                $sql .= " AND (title LIKE ? ESCAPE '\\\\' OR location LIKE ? ESCAPE '\\\\')";
                array_push($params, $like, $like);
            }
            $sql .= ' ORDER BY start_at ASC LIMIT 1000';
            foreach (Database::all($sql, $params) as $row) {
                $o = OccurrenceExpander::fromEvent($row);
                if ($o !== null && OccurrenceExpander::touches($o, $from, $to)) {
                    $out[] = self::decorate($o, $visible[$eventTypeId]);
                }
            }
        }

        OccurrenceExpander::sort($out);
        return $out;
    }

    /**
     * Overrides of the given entries relevant to a range, grouped by entry id (one query).
     * @param list<int> $entryIds
     * @return array<int,list<array<string,mixed>>>
     */
    public static function overridesFor(array $entryIds, string $from, string $to): array
    {
        if ($entryIds === []) {
            return [];
        }
        $lo = CivilDate::addDays($from, -(RecurrenceRules::MAX_DURATION_DAYS + 4));
        $sql = 'SELECT * FROM calendar_overrides WHERE entry_id IN (' . implode(',', array_fill(0, count($entryIds), '?')) . ')'
            . ' AND ((occurrence_date BETWEEN ? AND ?) OR (new_date BETWEEN ? AND ?))';
        $params = [...array_values($entryIds), $lo, $to, $lo, $to];
        $by = [];
        foreach (Database::all($sql, $params) as $r) {
            $by[(int) $r['entry_id']][] = $r;
        }
        return $by;
    }

    /** Add type data, the final colour and its accessible pair, URLs and the holiday flag to a raw occurrence. */
    public static function decorate(array $o, array $type): array
    {
        $color = ColorContrast::normalise($o['color'] ?? null) ?? (string) $type['color'];
        $o['type_slug'] = (string) $type['slug'];
        $o['type_name'] = (string) $type['name'];
        $o['type_icon'] = (string) $type['icon'];
        $o['type_sort'] = (int) $type['sort_order'];
        $o['type_id'] = (int) $type['id'];
        $o['color'] = $color;
        $o['pair'] = ColorContrast::pair($color);
        $o['is_holiday'] = $type['slug'] === self::SLUG_HOLIDAY;
        $o['is_event'] = $o['source'] === 'event';
        $o['url'] = self::occurrenceUrl($o);
        $o['ics_url'] = self::icsUrl($o);
        return $o;
    }

    /** Relative page URL of an occurrence: /calendar/{slug}[?date=...] or /events/{slug}. */
    public static function occurrenceUrl(array $o): string
    {
        if (($o['source'] ?? '') === 'event') {
            return url('events/' . $o['slug']);
        }
        return url('calendar/' . $o['slug'], !empty($o['recurring']) ? ['date' => $o['occurrence_date']] : []);
    }

    /** Relative "add to my calendar" URL: /calendar/{id}.ics?date=... (events use their own /events/{slug}/calendar.ics). */
    public static function icsUrl(array $o): string
    {
        if (($o['source'] ?? '') === 'event') {
            return url('events/' . $o['slug'] . '/calendar.ics');
        }
        return url('calendar/' . (int) $o['entry_id'] . '.ics', ['date' => $o['occurrence_date']]);
    }

    // ------------------------------------------------------------------ month grid, agenda, upcoming

    /**
     * Month grid for the server-rendered page, the contact embed and the admin month view.
     *
     * @param string $month 'YYYY-MM' (invalid values fall back to the current month)
     * @param array $filters see occurrences()
     * @param array{week_start?:int} $opts
     * @return array{
     *   month:string,year:int,month_number:int,label:string,prev:string,next:string,is_current:bool,is_far_future:bool,
     *   week_start:int,weekdays:list<array{iso:int,short:string,long:string}>,from:string,to:string,today:string,
     *   weeks:list<list<array<string,mixed>>>,total:int,custom_total:int,items:list<array<string,mixed>>
     * } each day cell: date, day, in_month, is_today, is_weekend, weekday, label, items (all occurrences covering the day), count, has_holiday
     */
    public static function monthGrid(string $month, array $filters = [], array $opts = []): array
    {
        $today = CivilDate::today();
        if (!CivilDate::validMonth($month)) {
            $month = substr($today, 0, 7);
        }
        $ws = (int) ($opts['week_start'] ?? self::weekStart());
        if (!in_array($ws, [1, 6, 7], true)) {
            $ws = 1;
        }
        $first = $month . '-01';
        $last = CivilDate::monthEnd($month);
        $gridStart = CivilDate::weekStartOf($first, $ws);
        $gridEnd = CivilDate::addDays(CivilDate::weekStartOf($last, $ws), 6);

        $items = self::occurrences($gridStart, $gridEnd, $filters);
        $byDay = [];
        foreach ($items as $o) {
            $a = max($o['start_date'], $gridStart);
            $b = min($o['end_date'], $gridEnd);
            for ($d = CivilDate::days($a), $end = CivilDate::days($b); $d <= $end; $d++) {
                $byDay[CivilDate::ymd($d)][] = $o;
            }
        }

        $weekdays = [];
        for ($i = 0; $i < 7; $i++) {
            $iso = (($ws - 1 + $i) % 7) + 1;
            $weekdays[] = ['iso' => $iso, 'short' => substr(CivilDate::WEEKDAYS[$iso], 0, 3), 'long' => CivilDate::WEEKDAYS[$iso]];
        }
        $weeks = [];
        $inMonth = 0;
        for ($d = CivilDate::days($gridStart), $end = CivilDate::days($gridEnd), $i = 0; $d <= $end; $d++, $i++) {
            $ymd = CivilDate::ymd($d);
            $wd = CivilDate::weekdayOfDays($d);
            $list = $byDay[$ymd] ?? [];
            $cell = [
                'date' => $ymd, 'day' => (int) substr($ymd, 8, 2), 'in_month' => substr($ymd, 0, 7) === $month, 'is_today' => $ymd === $today,
                'is_weekend' => $wd >= 6, 'weekday' => $wd, 'label' => CivilDate::fmt($ymd, 'l j F Y'), 'items' => $list, 'count' => count($list),
                'has_holiday' => (bool) array_filter($list, static fn(array $o): bool => $o['is_holiday']),
            ];
            $weeks[intdiv($i, 7)][] = $cell;
        }
        $monthItems = array_values(array_filter($items, static fn(array $o): bool => $o['start_date'] <= $last && $o['end_date'] >= $first));
        $todayMonth = substr($today, 0, 7);
        return [
            'month' => $month, 'year' => (int) substr($month, 0, 4), 'month_number' => (int) substr($month, 5, 2),
            'label' => CivilDate::MONTHS[(int) substr($month, 5, 2)] . ' ' . substr($month, 0, 4),
            'prev' => CivilDate::addMonths($month, -1), 'next' => CivilDate::addMonths($month, 1),
            'is_current' => $month === $todayMonth, 'is_far_future' => $month > CivilDate::addMonths($todayMonth, 24),
            'week_start' => $ws, 'weekdays' => $weekdays, 'from' => $gridStart, 'to' => $gridEnd, 'today' => $today,
            'weeks' => $weeks, 'total' => count($monthItems), 'custom_total' => count(array_filter($monthItems, static fn(array $o): bool => !$o['is_system'])), 'items' => $items,
        ];
    }

    /**
     * Occurrences grouped by day for the agenda list (an occurrence that started before $from is listed on $from).
     * @return list<array{date:string,label:string,is_today:bool,items:list<array<string,mixed>>}>
     */
    public static function agenda(string $from, string $to, array $filters = []): array
    {
        return self::groupByDay(self::occurrences($from, $to, $filters), $from, $to);
    }

    /**
     * Group already loaded occurrences by day for the range [$from, $to] (an occurrence that started before $from is listed on $from).
     * @param list<array<string,mixed>> $items
     * @return list<array{date:string,label:string,is_today:bool,items:list<array<string,mixed>>}>
     */
    public static function groupByDay(array $items, string $from, string $to): array
    {
        $today = CivilDate::today();
        $days = [];
        foreach ($items as $o) {
            if (!OccurrenceExpander::touches($o, $from, $to)) {
                continue;
            }
            $d = max($o['start_date'], $from);
            $days[$d][] = $o;
        }
        ksort($days);
        $out = [];
        foreach ($days as $d => $list) {
            $out[] = ['date' => (string) $d, 'label' => CivilDate::fmt((string) $d, 'l j F Y'), 'is_today' => $d === $today, 'items' => $list];
        }
        return $out;
    }

    /**
     * The next $n occurrences that have not ended yet (today's and running multi-day ones included).
     * @return list<array<string,mixed>>
     */
    public static function upcoming(int $n, array $filters = [], ?string $from = null): array
    {
        $n = max(1, min(50, $n));
        $from = ($from !== null && CivilDate::valid($from)) ? $from : CivilDate::today();
        $found = [];
        for ($w = 0; $w < 8 && count($found) < $n; $w++) {
            $a = CivilDate::addDays($from, $w * 120);
            $b = CivilDate::addDays($a, 119);
            foreach (self::occurrences($a, $b, $filters) as $o) {
                $found[$o['key']] = $o;
            }
        }
        $list = array_values($found);
        OccurrenceExpander::sort($list);
        return array_slice($list, 0, $n);
    }

    // ------------------------------------------------------------------ public JSON feed

    /**
     * Visitor-safe representation of an occurrence (no ids of staff, no private fields, nothing about visitors).
     * @return array<string,mixed>
     */
    public static function publicItem(array $o): array
    {
        $desc = (string) ($o['description'] ?? '');
        $html = ($o['source'] ?? '') === 'event'
            ? ($desc === '' ? '' : '<p>' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '</p>')
            : Sanitizer::rich($desc);
        return [
            'key' => $o['key'], 'kind' => $o['source'], 'entry_id' => $o['entry_id'], 'event_id' => $o['event_id'],
            'title' => $o['title'], 'type' => $o['type_slug'], 'type_name' => $o['type_name'], 'icon' => $o['type_icon'],
            'color' => $o['pair']['solid'], 'on_color' => $o['pair']['on_solid'], 'tint' => $o['pair']['tint'], 'text_color' => $o['pair']['text'],
            'start_date' => $o['start_date'], 'end_date' => $o['end_date'], 'all_day' => $o['all_day'],
            'start_time' => $o['start_time'], 'end_time' => $o['end_time'], 'location' => $o['location'] !== '' ? $o['location'] : null,
            'summary' => IcsBuilder::plainText($desc) === '' ? null : self::clip(IcsBuilder::plainText($desc), 240),
            'description_html' => $html !== '' ? $html : null,
            'is_holiday' => $o['is_holiday'], 'needs_confirmation' => $o['needs_confirmation'], 'confirmed' => $o['confirmed'], 'moved' => $o['moved'],
            'note' => $o['note'], 'source_label' => $o['source_label'], 'source_url' => $o['source_url'],
            'link_url' => $o['link_url'], 'link_label' => $o['link_label'], 'url' => $o['url'], 'ics_url' => $o['ics_url'],
        ];
    }

    /**
     * Body of GET /calendar/feed. The range is capped at FEED_MAX_DAYS; `errors` explain a rejected parameter.
     * @return array{range:array{start:string,end:string},capped:bool,timezone:string,week_start:int,types:list<array<string,mixed>>,count:int,items:list<array<string,mixed>>}
     */
    public static function feed(string $from, string $to, array $filters = []): array
    {
        $cap = CivilDate::addDays($from, self::FEED_MAX_DAYS - 1);
        $capped = $to > $cap;
        $to = min($to, $cap);
        $filters['public'] = true;
        $occ = self::occurrences($from, $to, $filters);
        if (count($occ) > self::FEED_MAX_ITEMS) {                    // a runaway entry must not turn the public feed into megabytes
            $occ = array_slice($occ, 0, self::FEED_MAX_ITEMS);
            $capped = true;
        }
        $items = array_map([self::class, 'publicItem'], $occ);
        return [
            'range' => ['start' => $from, 'end' => $to], 'capped' => $capped, 'timezone' => CivilDate::TZ, 'week_start' => self::weekStart(),
            'types' => array_map(static fn(array $t): array => [
                'slug' => $t['slug'], 'name' => $t['name'], 'icon' => $t['icon'], 'color' => $t['pair']['solid'], 'on_color' => $t['pair']['on_solid'],
                'tint' => $t['pair']['tint'], 'text_color' => $t['pair']['text'],
            ], self::publicTypes()),
            'count' => count($items), 'items' => $items,
        ];
    }

    // ------------------------------------------------------------------ entries (detail pages, next dates)

    /** A PUBLIC entry by slug (active, public, visible type) or null. */
    public static function publicEntryBySlug(string $slug): ?array
    {
        if (!self::available() || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return null;
        }
        return Database::one(
            'SELECT e.* FROM calendar_entries e JOIN calendar_types t ON t.id = e.type_id'
            . ' WHERE e.slug = ? AND e.is_active = 1 AND e.is_public = 1 AND t.is_active = 1 AND t.show_on_public = 1 LIMIT 1',
            [$slug]
        );
    }

    /** A PUBLIC entry by id (same rule). */
    public static function publicEntryById(int $id): ?array
    {
        if (!self::available() || $id < 1) {
            return null;
        }
        return Database::one(
            'SELECT e.* FROM calendar_entries e JOIN calendar_types t ON t.id = e.type_id'
            . ' WHERE e.id = ? AND e.is_active = 1 AND e.is_public = 1 AND t.is_active = 1 AND t.show_on_public = 1 LIMIT 1',
            [$id]
        );
    }

    /**
     * Occurrences of ONE entry in a range (public flags are not re-checked: pass an entry you already loaded).
     * @return list<array<string,mixed>>
     */
    public static function occurrencesOfEntry(array $entry, string $from, string $to, bool $includeCancelled = false): array
    {
        $type = self::typeById((int) $entry['type_id']);
        if ($type === null || !CivilDate::valid($from) || !CivilDate::valid($to) || $from > $to) {
            return [];
        }
        $to = min($to, CivilDate::addDays($from, self::MAX_INTERNAL_DAYS));
        $id = (int) $entry['id'];
        $ov = $id > 0 ? self::overridesFor([$id], $from, $to) : [];
        $occ = OccurrenceExpander::expandEntries([$entry], $ov, $from, $to, ['include_cancelled' => $includeCancelled]);
        $out = array_map(static fn(array $o): array => self::decorate($o, $type), $occ);
        OccurrenceExpander::sort($out);
        return $out;
    }

    /**
     * The next $n occurrences of one entry starting from $from (default today), searching up to about 13 years ahead.
     * @return list<array<string,mixed>>
     */
    public static function nextOccurrences(array $entry, int $n = 5, ?string $from = null, bool $includeCancelled = false): array
    {
        $from = ($from !== null && CivilDate::valid($from)) ? $from : CivilDate::today();
        $found = [];
        $none = ((string) ($entry['recurrence'] ?? 'none')) === 'none';
        for ($w = 0; $w < 12 && count($found) < $n; $w++) {
            $a = CivilDate::addDays($from, $w * 400);
            foreach (self::occurrencesOfEntry($entry, $a, CivilDate::addDays($a, 399), $includeCancelled) as $o) {
                $found[$o['key']] = $o;
            }
            if ($none) {
                break;
            }
        }
        $list = array_values($found);
        OccurrenceExpander::sort($list);
        return array_slice($list, 0, $n);
    }

    /**
     * Live preview for the editor: the next dates of an UNSAVED schedule, computed by the same engine.
     * $input keys as the entry form (start_date, end_date, recurrence, recurrence_data, repeat_until). $entryId includes that entry's overrides.
     * @return array{ok:bool,errors:array<string,string>,description:string,dates:list<array{date:string,end_date:string,label:string,weekday:string}>}
     */
    public static function previewOccurrences(array $input, int $n = 5, ?int $entryId = null, ?string $from = null): array
    {
        $norm = RecurrenceRules::normalise($input);
        if (!$norm['ok'] || $norm['start_date'] === null) {
            return ['ok' => false, 'errors' => $norm['errors'], 'description' => '', 'dates' => []];
        }
        $row = [
            'id' => $entryId ?? 0, 'type_id' => 0, 'start_date' => $norm['start_date'], 'end_date' => $norm['end_date'],
            'recurrence' => $norm['recurrence'], 'recurrence_data' => $norm['json'], 'repeat_until' => $norm['repeat_until'],
        ];
        $from = ($from !== null && CivilDate::valid($from)) ? $from : ($norm['recurrence'] === 'none' ? $norm['start_date'] : CivilDate::today());
        $overrides = $entryId ? self::overridesFor([$entryId], $from, CivilDate::addDays($from, 4800)) : [];
        $dates = [];
        for ($w = 0; $w < 12 && count($dates) < $n; $w++) {
            $a = CivilDate::addDays($from, $w * 400);
            $occ = OccurrenceExpander::expandEntries([$row], $overrides, $a, CivilDate::addDays($a, 399));
            foreach ($occ as $o) {
                $dates[$o['key']] = $o;
            }
            if ($norm['recurrence'] === 'none') {
                break;
            }
        }
        $list = array_values($dates);
        OccurrenceExpander::sort($list);
        $out = [];
        foreach (array_slice($list, 0, $n) as $o) {
            $label = CivilDate::fmt($o['start_date'], 'D j M Y');
            if ($o['end_date'] !== $o['start_date']) {
                $label .= ' to ' . CivilDate::fmt($o['end_date'], 'D j M Y');
            }
            $out[] = ['date' => $o['start_date'], 'end_date' => $o['end_date'], 'label' => $label, 'weekday' => CivilDate::WEEKDAYS[CivilDate::weekday($o['start_date'])]];
        }
        return ['ok' => true, 'errors' => [], 'description' => RecurrenceRules::describe($row), 'dates' => $out];
    }

    // ------------------------------------------------------------------ dates to confirm, holidays tab

    /**
     * Occurrences within the next $withinDays days that still carry "date to be confirmed" (admin; includes entries that are hidden).
     * @return list<array<string,mixed>>
     */
    public static function datesToConfirm(int $withinDays = 60): array
    {
        if (!self::available()) {
            return [];
        }
        $today = CivilDate::today();
        $list = self::occurrences($today, CivilDate::addDays($today, max(1, $withinDays)), ['public' => false, 'include_inactive' => false, 'only_unconfirmed' => true]);
        return array_values(array_filter($list, static fn(array $o): bool => $o['needs_confirmation']));
    }

    /** Number of dates to confirm in the next 60 days (sidebar badge; cached per request, 0 on any error). */
    public static function pendingConfirmationCount(): int
    {
        if (self::$pending === null) {
            try {
                self::$pending = count(self::datesToConfirm(60));
            } catch (Throwable) {
                self::$pending = 0;
            }
        }
        return self::$pending;
    }

    /** Dashboard tile (null when the user may not manage the calendar). */
    public static function dashboardTile(): ?array
    {
        if (!can('calendar.manage') || !self::available()) {
            return null;
        }
        $n = self::pendingConfirmationCount();
        return ['icon' => 'calendar', 'value' => number_format($n), 'label' => 'Dates to confirm', 'sub' => 'within the next 60 days', 'url' => 'admin/calendar/holidays', 'alert' => $n > 0];
    }

    /**
     * The "Holidays" tab: every system entry of a type (default: Uganda public holidays) with its next occurrence.
     * @return list<array{entry:array<string,mixed>,type:array<string,mixed>,next:?array<string,mixed>,unconfirmed:bool,days_until:?int}>
     */
    public static function systemEntries(string $typeSlug = self::SLUG_HOLIDAY): array
    {
        $type = self::typeBySlug($typeSlug);
        if ($type === null) {
            return [];
        }
        $entries = Database::all('SELECT * FROM calendar_entries WHERE type_id = ? AND is_system = 1 ORDER BY title ASC', [$type['id']]);
        if ($entries === []) {
            return [];
        }
        $today = CivilDate::today();
        $horizon = CivilDate::addDays($today, 800);
        $ov = self::overridesFor(array_map(static fn(array $e): int => (int) $e['id'], $entries), $today, $horizon);
        $rows = [];
        foreach ($entries as $e) {
            $occ = OccurrenceExpander::expandEntries([$e], $ov, $today, $horizon);
            $next = null;
            if ($occ !== []) {
                OccurrenceExpander::sort($occ);
                $next = self::decorate($occ[0], $type);
            }
            $rows[] = [
                'entry' => $e, 'type' => $type, 'next' => $next, 'unconfirmed' => $next !== null && $next['needs_confirmation'],
                'days_until' => $next !== null ? max(0, CivilDate::diffDays($today, $next['start_date'])) : null,
            ];
        }
        usort($rows, static fn(array $a, array $b): int => [$a['next']['start_date'] ?? '9999', $a['entry']['title']] <=> [$b['next']['start_date'] ?? '9999', $b['entry']['title']]);
        return $rows;
    }

    // ------------------------------------------------------------------ embed for the Contact page

    /**
     * Data of the compact calendar on /contact, or NULL when it is switched off or unavailable.
     * @return array{month:string,grid:array<string,mixed>,upcoming:list<array<string,mixed>>,types:list<array<string,mixed>>,calendar_url:string,page_published:bool}|null
     */
    public static function embedData(?string $month = null): ?array
    {
        try {
            if (!self::showOnContact()) {
                return null;
            }
            $filters = ['types' => self::defaultTypeSlugs()];
            $count = (int) content('contact', 'calendar.upcoming_count');
            // the same span as the /calendar page: the grid of 1900-01 or 2200-12 would reach outside the supported years
            $month = ($month !== null && CivilDate::validMonth($month) && $month >= self::MIN_MONTH && $month <= self::MAX_MONTH) ? $month : substr(CivilDate::today(), 0, 7);
            return [
                'month' => $month,
                'grid' => self::monthGrid($month, $filters),
                'upcoming' => self::upcoming($count > 0 ? $count : 6, $filters),
                'types' => self::publicTypes(),
                'calendar_url' => url('calendar'),
                'page_published' => true,
            ];
        } catch (Throwable $e) {
            \App\Core\Log::error('Calendar embed failed: ' . $e->getMessage());
            return null;
        }
    }

    // ------------------------------------------------------------------ iCalendar

    /** Host used in UIDs: the APP_URL host, else the request host, else the default. */
    public static function icsHost(): string
    {
        $app = (string) \App\Core\Config::get('app.url', '');
        $h = $app !== '' ? (string) parse_url($app, PHP_URL_HOST) : '';
        if ($h === '') {
            try {
                $h = Request::current()?->host() ?? '';
            } catch (Throwable) {
                $h = '';
            }
        }
        return $h !== '' ? $h : IcsBuilder::DEFAULT_HOST;
    }

    /** Add the absolute page URL used by the ICS URL property. */
    private static function withAbsoluteUrl(array $o): array
    {
        try {
            $rel = $o['url'];
            $o['abs_url'] = preg_match('~^https?://~i', $rel) ? $rel : origin() . $rel;
        } catch (Throwable) {
            $o['abs_url'] = '';
        }
        return $o;
    }

    /**
     * /calendar.ics: public occurrences from 1 January of last year to 31 December of next year plus one (about four years).
     * @param array $filters see occurrences() (types)
     */
    public static function icsFeed(array $filters = [], ?string $now = null): string
    {
        $y = (int) substr(CivilDate::today(), 0, 4);
        $occ = self::occurrences(($y - 1) . '-01-01', ($y + 2) . '-12-31', ['public' => true] + $filters);
        if (count($occ) > self::ICS_MAX_ITEMS) {
            // never silently drop the holidays and international days: keep every system occurrence, fill the rest with the earliest others
            $keep = [];
            $rest = [];
            foreach ($occ as $o) {
                if (!empty($o['is_system'])) {
                    $keep[] = $o;
                } else {
                    $rest[] = $o;
                }
            }
            $occ = array_merge($keep, array_slice($rest, 0, max(0, self::ICS_MAX_ITEMS - count($keep))));
            OccurrenceExpander::sort($occ);
            $occ = array_slice($occ, 0, self::ICS_MAX_ITEMS);
            \App\Core\Log::warning('Calendar feed (.ics) was cut at ' . self::ICS_MAX_ITEMS . ' events; check for entries that repeat too densely.');
        }
        return IcsBuilder::calendar(array_map([self::class, 'withAbsoluteUrl'], $occ), self::icsOptions(self::icsCalendarName(), $now));
    }

    /** /calendar/{id}.ics: one occurrence ($date) or the next occurrence of a public entry; '' when nothing is found. */
    public static function icsEntry(array $entry, ?string $date = null, ?string $now = null): string
    {
        if ($date !== null && CivilDate::valid($date)) {
            $occ = self::occurrencesOfEntry($entry, CivilDate::addDays($date, -RecurrenceRules::MAX_DURATION_DAYS), CivilDate::addDays($date, 1));
            $hit = self::occurrenceOn($occ, $date);
            $occ = $hit === null ? [] : [$hit];
        } else {
            $occ = self::nextOccurrences($entry, 1);
        }
        if ($occ === []) {
            return '';
        }
        return IcsBuilder::calendar(array_map([self::class, 'withAbsoluteUrl'], array_slice($occ, 0, 1)), self::icsOptions((string) $entry['title'], $now));
    }

    /**
     * The occurrence a ?date= link means: the one whose rule date (occurrence_date) is that day, else the first one whose span covers it.
     * (Overlapping or moved series can have several that cover the day; the link always carries the occurrence_date.)
     * @param list<array<string,mixed>> $occ
     */
    public static function occurrenceOn(array $occ, string $date): ?array
    {
        foreach ($occ as $o) {
            if ($o['occurrence_date'] === $date) {
                return $o;
            }
        }
        foreach ($occ as $o) {
            if ($o['start_date'] <= $date && $o['end_date'] >= $date) {
                return $o;
            }
        }
        return null;
    }

    /** Calendar name from the registry (calendar > ics.calendar_name, {site} = organisation name). */
    public static function icsCalendarName(): string
    {
        $name = trim((string) content('calendar', 'ics.calendar_name'));
        return str_replace('{site}', (string) setting('site_name', 'Lumina Africa Foundation'), $name !== '' ? $name : '{site} calendar');
    }

    /** @return array<string,mixed> options for IcsBuilder::calendar() */
    private static function icsOptions(string $name, ?string $now): array
    {
        $opts = ['name' => $name, 'host' => self::icsHost(), 'labels' => [
            'tbc' => trim((string) content('calendar', 'ics.tbc_note')) ?: 'Date to be confirmed.',
            'source' => trim((string) content('calendar', 'ics.source_prefix')) ?: 'Source:',
        ]];
        if ($now !== null) {
            $opts['now'] = $now;
        }
        return $opts;
    }

    // ------------------------------------------------------------------ small helpers

    /** "Tue 3 Mar" style label of an occurrence's days: '3 March 2026' or '3 to 5 March 2026'. */
    public static function dateLabel(array $o, string $joiner = 'to'): string
    {
        $s = $o['start_date'];
        $e = $o['end_date'];
        if ($s === $e) {
            return CivilDate::fmt($s, 'l j F Y');
        }
        return CivilDate::fmt($s, 'j F Y') . ' ' . $joiner . ' ' . CivilDate::fmt($e, 'j F Y');
    }

    /** "09:00 to 11:30" / "09:00" / '' for all-day. East Africa Time is implied (the page says so once). */
    public static function timeLabel(array $o, string $joiner = 'to'): string
    {
        if (!empty($o['all_day']) || empty($o['start_time'])) {
            return '';
        }
        return $o['start_time'] . (!empty($o['end_time']) ? ' ' . $joiner . ' ' . $o['end_time'] : '');
    }

    private static function clip(string $s, int $max): string
    {
        $s = trim((string) preg_replace('/\s+/u', ' ', $s));
        return mb_strlen($s) <= $max ? $s : rtrim(mb_substr($s, 0, $max - 1)) . "\u{2026}";
    }
}
