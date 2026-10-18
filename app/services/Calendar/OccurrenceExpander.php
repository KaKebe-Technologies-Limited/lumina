<?php
declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * Turns calendar entries (+ their per-occurrence overrides) and events rows into concrete OCCURRENCES inside a date range.
 * Pure PHP, no database: CalendarService loads the rows (a constant number of queries per range) and calls this class.
 *
 * OCCURRENCE (array, the one shape every consumer uses - month grid, agenda, JSON feed, ICS, admin lists):
 *   key               'e{entry_id}:{occurrence_date}' | 'v{event_id}:{start_date}'      stable id of one occurrence
 *   source            'entry' | 'event'
 *   entry_id / event_id   int|null
 *   type_id           int|null
 *   title, slug, description (sanitised HTML), location
 *   all_day           bool
 *   start_date / end_date    'YYYY-MM-DD' (end_date is INCLUSIVE, equal to start_date for a one-day occurrence)
 *   start_time / end_time    'HH:MM' | null (Africa/Kampala; null for all-day)
 *   occurrence_date   'YYYY-MM-DD' the day the RULE produced (differs from start_date when the occurrence was moved); the override key
 *   recurring         bool
 *   moved             bool  an override changed its date or time
 *   cancelled         bool  (only returned with opts include_cancelled)
 *   confirmed         bool  an admin confirmed the date (override.confirmed = 1)
 *   needs_confirmation bool the entry is flagged "date to be confirmed" and no admin confirmed this occurrence yet
 *   note              string|null  admin note of the override
 *   override_id       int|null
 *   color             string|null  per-entry colour override (the type colour is added by CalendarService)
 *   program_id, project_id, link_url, link_label, source_label, source_url   (null for events except link_url)
 *   is_public, is_active, is_system   bool
 */
final class OccurrenceExpander
{
    public const MAX_TOTAL = 6000;
    /** rough byte budget of one expansion (descriptions are repeated in every occurrence of a feed): a guard, not a feature */
    public const MAX_BYTES = 4000000;

    /** True when the last expandEntries() call stopped early (MAX_TOTAL or MAX_BYTES): the result is incomplete. */
    public static bool $truncated = false;

    /**
     * @param list<array<string,mixed>> $entries      calendar_entries rows
     * @param array<int,list<array<string,mixed>>> $overrides  entry_id => calendar_overrides rows
     * @param array{include_cancelled?:bool,max?:int} $opts
     * @return list<array<string,mixed>> unsorted
     */
    public static function expandEntries(array $entries, array $overrides, string $from, string $to, array $opts = []): array
    {
        $out = [];
        self::$truncated = false;
        $bytes = 0;
        $max = (int) ($opts['max'] ?? self::MAX_TOTAL);
        $includeCancelled = !empty($opts['include_cancelled']);
        if (!CivilDate::valid($from) || !CivilDate::valid($to) || $from > $to) {
            return [];
        }
        foreach ($entries as $entry) {
            $id = (int) ($entry['id'] ?? 0);
            $dur = RecurrenceRules::duration($entry);
            $byDate = [];
            foreach ($overrides[$id] ?? [] as $ov) {
                if (!empty($ov['occurrence_date'])) {
                    $byDate[(string) $ov['occurrence_date']] = $ov;
                }
            }
            $seen = [];
            // 1. dates produced by the rule whose span touches the range
            foreach (RecurrenceRules::starts($entry, CivilDate::addDays($from, -$dur), $to) as $d) {
                $seen[$d] = true;
                $occ = self::build($entry, $d, $byDate[$d] ?? null, $dur);
                if ($occ['cancelled'] && !$includeCancelled) {
                    continue;
                }
                if (self::touches($occ, $from, $to)) {
                    $out[] = $occ;
                    $bytes += 400 + strlen($occ['description']);
                }
            }
            // 2. overrides that MOVED an occurrence from outside the window into the range (and are still produced by the rule)
            foreach ($byDate as $d => $ov) {
                if (isset($seen[$d]) || empty($ov['new_date']) || (!empty($ov['cancelled']) && !$includeCancelled)) {
                    continue;
                }
                if (RecurrenceRules::starts($entry, (string) $d, (string) $d) !== [(string) $d]) {
                    continue;                                   // stale override: the rule no longer produces that date
                }
                $occ = self::build($entry, (string) $d, $ov, $dur);
                if (self::touches($occ, $from, $to)) {
                    $out[] = $occ;
                }
            }
            if (count($out) >= $max || $bytes > self::MAX_BYTES) {
                self::$truncated = true;
                return array_slice($out, 0, $max);
            }
        }
        return $out;
    }

    /** One occurrence of an entry with its override applied. */
    public static function build(array $entry, string $occurrenceDate, ?array $ov, ?int $dur = null): array
    {
        $dur ??= RecurrenceRules::duration($entry);
        $cancelled = $ov !== null && !empty($ov['cancelled']);
        $newDate = ($ov !== null && !empty($ov['new_date']) && CivilDate::valid((string) $ov['new_date'])) ? (string) $ov['new_date'] : null;
        $start = $newDate ?? $occurrenceDate;
        $end = $dur > 0 ? CivilDate::addDays($start, $dur) : $start;
        $allDay = !empty($entry['all_day']);
        $startTime = $allDay ? null : CivilDate::time($entry['start_time'] ?? null);
        $endTime = $allDay ? null : CivilDate::time($entry['end_time'] ?? null);
        $newStart = $ov !== null ? CivilDate::time($ov['new_start_time'] ?? null) : null;
        if ($newStart !== null) {
            $allDay = false;
            $startTime = $newStart;
            $endTime = CivilDate::time($ov['new_end_time'] ?? null);
        }
        if ($startTime === null) {
            $allDay = true;
            $endTime = null;
        }
        $confirmed = $ov !== null && !empty($ov['confirmed']);
        $flagged = !empty($entry['needs_confirmation']);
        $id = (int) ($entry['id'] ?? 0);
        return [
            'key'                => 'e' . $id . ':' . $occurrenceDate,
            'source'             => 'entry',
            'entry_id'           => $id,
            'event_id'           => null,
            'type_id'            => isset($entry['type_id']) ? (int) $entry['type_id'] : null,
            'title'              => (string) ($entry['title'] ?? ''),
            'slug'               => (string) ($entry['slug'] ?? ''),
            'description'        => (string) ($entry['description'] ?? ''),
            'location'           => (string) ($entry['location'] ?? ''),
            'all_day'            => $allDay,
            'start_date'         => $start,
            'end_date'           => $end,
            'start_time'         => $startTime,
            'end_time'           => $endTime,
            'occurrence_date'    => $occurrenceDate,
            'recurring'          => (string) ($entry['recurrence'] ?? 'none') !== 'none',
            'moved'              => $newDate !== null || $newStart !== null,
            'cancelled'          => $cancelled,
            'confirmed'          => $confirmed,
            'needs_confirmation' => $flagged && !$confirmed,
            'note'               => $ov !== null && ($ov['note'] ?? '') !== '' ? (string) $ov['note'] : null,
            'override_id'        => $ov !== null ? (int) ($ov['id'] ?? 0) : null,
            'color'              => ($entry['color'] ?? '') !== '' ? (string) $entry['color'] : null,
            'program_id'         => isset($entry['program_id']) ? (int) $entry['program_id'] : null,
            'project_id'         => isset($entry['project_id']) ? (int) $entry['project_id'] : null,
            'link_url'           => ($entry['link_url'] ?? '') !== '' ? (string) $entry['link_url'] : null,
            // a meeting's entry stores a token, not words: the wording stays editable in Admin > Pages > Meetings (MeetingService::JOIN_LABEL_TOKEN)
            'link_label'         => ($entry['link_label'] ?? '') !== '' ? ((string) $entry['link_label'] === \App\Services\Meetings\MeetingService::JOIN_LABEL_TOKEN ? \App\Services\Meetings\MeetingService::calendarJoinLabel() : (string) $entry['link_label']) : null,
            'source_label'       => ($entry['source_label'] ?? '') !== '' ? (string) $entry['source_label'] : null,
            'source_url'         => ($entry['source_url'] ?? '') !== '' ? (string) $entry['source_url'] : null,
            'is_public'          => !empty($entry['is_public']),
            'is_active'          => !empty($entry['is_active']),
            'is_system'          => !empty($entry['is_system']),
        ];
    }

    /**
     * An `events` table row as an occurrence. start_at / end_at are DATETIMEs in Africa/Kampala.
     * Midnight start with no end (or an end at midnight / 23:59) is shown as an all-day event.
     */
    public static function fromEvent(array $row): ?array
    {
        $startAt = (string) ($row['start_at'] ?? '');
        if (!preg_match('/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})/', $startAt, $m) || !CivilDate::valid($m[1])) {
            return null;
        }
        $sd = $m[1];
        $st = $m[2];
        $ed = $sd;
        $et = null;
        $endAt = (string) ($row['end_at'] ?? '');
        if ($endAt !== '' && preg_match('/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})/', $endAt, $e) && CivilDate::valid($e[1]) && $e[1] >= $sd) {
            $ed = $e[1];
            $et = $e[2];
            if ($et === '00:00' && $ed > $sd) {          // ends exactly at midnight: it ended the day before (a 24 h event is one day, not two)
                $ed = CivilDate::addDays($ed, -1);
                $et = null;
            }
        }
        $allDay = $st === '00:00' && ($et === null || $et === '00:00' || $et === '23:59');
        $id = (int) ($row['id'] ?? 0);
        return [
            'key' => 'v' . $id . ':' . $sd, 'source' => 'event', 'entry_id' => null, 'event_id' => $id, 'type_id' => null,
            'title' => (string) ($row['title'] ?? ''), 'slug' => (string) ($row['slug'] ?? ''),
            'description' => (string) ($row['summary'] ?? ''), 'location' => (string) ($row['location'] ?? ''),
            'all_day' => $allDay, 'start_date' => $sd, 'end_date' => $ed,
            'start_time' => $allDay ? null : $st, 'end_time' => $allDay ? null : ($et === $st ? null : $et),
            'occurrence_date' => $sd, 'recurring' => false, 'moved' => false, 'cancelled' => false, 'confirmed' => true,
            'needs_confirmation' => false, 'note' => null, 'override_id' => null, 'color' => null,
            'program_id' => null, 'project_id' => null, 'link_url' => null, 'link_label' => null, 'source_label' => null, 'source_url' => null,
            'is_public' => true, 'is_active' => !empty($row['is_active']), 'is_system' => false,
        ];
    }

    /** True when the occurrence overlaps [$from, $to]. */
    public static function touches(array $occ, string $from, string $to): bool
    {
        return $occ['start_date'] <= $to && $occ['end_date'] >= $from;
    }

    /** Sort: start date, all-day first, start time, type order, title. Expects 'type_sort' (added by CalendarService) but works without. */
    public static function sort(array &$occ): void
    {
        usort($occ, static function (array $a, array $b): int {
            return [$a['start_date'], $a['all_day'] ? 0 : 1, (string) $a['start_time'], (int) ($a['type_sort'] ?? 0), mb_strtolower((string) $a['title']), (string) $a['key']]
                <=> [$b['start_date'], $b['all_day'] ? 0 : 1, (string) $b['start_time'], (int) ($b['type_sort'] ?? 0), mb_strtolower((string) $b['title']), (string) $b['key']];
        });
    }
}
