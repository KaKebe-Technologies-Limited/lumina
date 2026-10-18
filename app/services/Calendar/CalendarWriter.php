<?php
declare(strict_types=1);

namespace App\Services\Calendar;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Icons;
use App\Core\Sanitizer;
use App\Core\Slug;
use App\Models\CalendarEntry;
use App\Models\CalendarOverride;
use App\Models\CalendarType;

/**
 * WRITE side of the calendar. EVERY change to entries, overrides and types goes through this class (admin forms, quick add, CSV import,
 * duplicate, bulk actions), so validation, sanitising, slugs, audit entries and the "pristine row" rule of the seed live in one place.
 * Controllers only translate the request into arrays, check the ability (role) and show the result.
 *
 * Result convention: ['ok' => bool, 'errors' => array<field,message>, ...]. Audit entries carry ids and counts only.
 */
final class CalendarWriter
{
    /** Repeat types only administrators may choose (ability calendar.advanced); editors keep what an entry already has. */
    public const ADVANCED = ['nth_weekday', 'easter', 'islamic'];
    /** What the one-line "Repeat" menu of the quick-add form offers. */
    public const SIMPLE_REPEAT = ['none', 'daily', 'weekly', 'monthly', 'yearly'];
    public const MAX_DESCRIPTION = 6000;
    /** Shown when someone tries to change a calendar entry that a meeting owns (docs/MEETINGS.md): the meeting is the source of truth. */
    public const MEETING_OWNED = 'This entry belongs to a meeting. Change it under Meetings so the invitations stay correct.';

    /** CSV columns (export, template and import use exactly these, in this order). */
    public const CSV_COLUMNS = [
        'title', 'type', 'start_date', 'end_date', 'all_day', 'start_time', 'end_time', 'location', 'repeat', 'repeat_until', 'public',
        'description', 'link_url', 'source_label', 'source_url',
    ];

    // ------------------------------------------------------------------ entries

    /** Does a meeting own this calendar entry? (False while the meetings table does not exist yet.) */
    public static function ownedByMeeting(int $entryId): bool
    {
        static $has = null;
        $has ??= Database::tableExists('meetings');
        return $has && (int) Database::value('SELECT COUNT(*) FROM meetings WHERE calendar_entry_id = ?', [$entryId], 0) > 0;
    }

    /**
     * INSERT a row whose slug is UNIQUE. Two editors saving the same title at the same moment can both pick the same free slug: the loser gets
     * a duplicate-key error (SQLSTATE 23000), so it picks the next free slug and tries again (a few times) instead of failing with a 500.
     * @param callable(array):int $create
     * @param array<string,mixed> $row must hold 'slug' and the text the slug comes from ($titleKey)
     */
    private static function createUnique(callable $create, string $table, array $row, int $slugLen, string $fallback, string $titleKey = 'title'): int
    {
        for ($try = 0; ; $try++) {
            try {
                return $create($row);
            } catch (\PDOException $e) {
                if ((string) $e->getCode() !== '23000' || $try >= 7) {
                    throw $e;
                }
                $row['slug'] = Slug::unique($table, Slug::make((string) ($row[$titleKey] ?? $fallback), $slugLen), null, 'slug', [], $fallback);
                if ($try >= 2) {                                   // many saves at once keep choosing the same "next" slug: spread them out
                    $row['slug'] = mb_substr((string) $row['slug'], 0, $slugLen - 5) . '-' . random_int(10, 999);
                }
            }
        }
    }

    /**
     * Validate and normalise the input of the entry form (also used by quick add, duplicate and the CSV import).
     *
     * Input keys (strings from a form are fine): title*, type_id* (or type = slug), description (HTML), location, all_day, start_date*,
     * end_date, start_time, end_time, recurrence, recurrence_data (array or JSON), repeat_until, program_id, project_id, event_id,
     * link_url, link_label, color, source_label, source_url, is_public, is_active, needs_confirmation, slug.
     *
     * @param array|null $existing the stored row when editing (slug uniqueness, unchanged advanced rules, system flag)
     * @param array{allow_advanced?:bool} $opts allow_advanced = the user holds calendar.advanced
     * @return array{ok:bool,errors:array<string,string>,row:array<string,mixed>}
     */
    public static function validateEntry(array $in, ?array $existing = null, array $opts = []): array
    {
        $errors = [];
        $row = [];
        $str = static fn(mixed $v): string => is_scalar($v) ? trim((string) $v) : '';
        $bool = static fn(mixed $v, bool $default): bool => $v === null || $v === '' ? $default : in_array($v, [1, '1', true, 'true', 'on', 'yes'], true);

        // title
        $title = Sanitizer::text($str($in['title'] ?? ''));
        if ($title === '') {
            $errors['title'] = 'Enter a title.';
        } elseif (mb_strlen($title) > 190) {
            $errors['title'] = 'The title can have at most 190 characters.';
        }
        $row['title'] = $title;

        // type
        $type = null;
        if (isset($in['type_id']) && $str($in['type_id']) !== '') {
            $type = CalendarService::typeById((int) $in['type_id']);
        } elseif (isset($in['type']) && $str($in['type']) !== '') {
            $type = CalendarService::typeBySlug(strtolower($str($in['type'])));
        }
        if ($type === null) {
            $errors['type_id'] = 'Choose a type.';
        } elseif ($type['slug'] === CalendarType::SLUG_EVENT) {
            $errors['type_id'] = 'Events are added under Events; they appear in the calendar automatically.';
        }
        $row['type_id'] = $type['id'] ?? 0;

        // description (small, sanitised HTML)
        $desc = Sanitizer::html($str($in['description'] ?? ''));
        if (mb_strlen($desc) > self::MAX_DESCRIPTION) {
            $errors['description'] = 'The description is too long (at most ' . self::MAX_DESCRIPTION . ' characters).';
        }
        $row['description'] = trim(strip_tags($desc, '<img>')) === '' ? null : $desc;

        $loc = Sanitizer::text($str($in['location'] ?? ''));
        if (mb_strlen($loc) > 255) {
            $errors['location'] = 'The location can have at most 255 characters.';
        }
        $row['location'] = $loc !== '' ? $loc : null;

        // when: times
        $startTime = CivilDate::time($str($in['start_time'] ?? ''));
        $endTime = CivilDate::time($str($in['end_time'] ?? ''));
        if ($str($in['start_time'] ?? '') !== '' && $startTime === null) {
            $errors['start_time'] = 'Enter the start time as HH:MM (24 hours).';
        }
        if ($str($in['end_time'] ?? '') !== '' && $endTime === null) {
            $errors['end_time'] = 'Enter the end time as HH:MM (24 hours).';
        }
        $allDay = $bool($in['all_day'] ?? null, $startTime === null);
        if ($allDay) {
            $startTime = $endTime = null;
        } elseif ($startTime === null && !isset($errors['start_time'])) {
            $errors['start_time'] = 'Enter a start time, or tick "All day".';
        }

        // repeat rule + dates
        $norm = RecurrenceRules::normalise([
            'recurrence' => $str($in['recurrence'] ?? 'none') ?: 'none', 'recurrence_data' => $in['recurrence_data'] ?? null,
            'start_date' => $str($in['start_date'] ?? ''), 'end_date' => $str($in['end_date'] ?? ''), 'repeat_until' => $str($in['repeat_until'] ?? ''),
        ]);
        foreach ($norm['errors'] as $f => $m) {
            $errors[$f] = $m;
        }
        if (!$allDay && $startTime !== null && $endTime !== null && ($norm['end_date'] === null) && $endTime <= $startTime && !isset($errors['end_time'])) {
            $errors['end_time'] = 'The end time must be after the start time. For something that ends after midnight, set the last day to the next day.';
        }
        // only administrators choose the advanced repeat types; an editor may keep what the entry already has
        $advancedUsed = in_array($norm['recurrence'], self::ADVANCED, true) || (($norm['data']['mode'] ?? '') === 'nth_weekday');
        if ($advancedUsed && empty($opts['allow_advanced'])) {
            $same = $existing !== null && (string) $existing['recurrence'] === $norm['recurrence']
                && RecurrenceRules::decode($existing['recurrence_data'] ?? null) === RecurrenceRules::decode($norm['json']);
            if (!$same) {
                $errors['recurrence'] = 'Only an administrator can use this repeat type.';
            }
        }
        $row += [
            'all_day' => $allDay ? 1 : 0, 'start_date' => $norm['start_date'], 'end_date' => $norm['end_date'],
            'start_time' => $startTime !== null ? $startTime . ':00' : null, 'end_time' => $endTime !== null ? $endTime . ':00' : null,
            'recurrence' => $norm['recurrence'], 'recurrence_data' => $norm['json'], 'repeat_until' => $norm['repeat_until'],
        ];

        // links to programmes, projects, events
        foreach (['program_id' => 'programs', 'project_id' => 'projects', 'event_id' => 'events'] as $f => $table) {
            $v = $str($in[$f] ?? '');
            if ($v === '' || $v === '0') {
                $row[$f] = null;
            } elseif (!ctype_digit($v) || Database::value('SELECT COUNT(*) FROM ' . Database::ident($table) . ' WHERE id = ?', [(int) $v], 0) < 1) {
                $errors[$f] = 'That item does not exist.';
                $row[$f] = null;
            } else {
                $row[$f] = (int) $v;
            }
        }

        // external link and source (http / https only)
        foreach (['link_url' => 'link_url', 'source_url' => 'source_url'] as $f => $col) {
            $u = $str($in[$f] ?? '');
            if ($u === '') {
                $row[$col] = null;
            } elseif (strlen($u) > 500 || !preg_match('~^https?://[^\s]+$~i', $u) || filter_var($u, FILTER_VALIDATE_URL) === false) {
                $errors[$f] = 'Enter a full web address starting with http:// or https://.';
                $row[$col] = null;
            } else {
                $row[$col] = $u;
            }
        }
        $label = Sanitizer::text($str($in['link_label'] ?? ''));
        $row['link_label'] = ($row['link_url'] !== null && $label !== '') ? mb_substr($label, 0, 120) : null;
        $src = Sanitizer::text($str($in['source_label'] ?? ''));
        if (mb_strlen($src) > 190) {
            $errors['source_label'] = 'The source can have at most 190 characters.';
        }
        $row['source_label'] = $src !== '' ? $src : null;

        $color = $str($in['color'] ?? '');
        if ($color === '') {
            $row['color'] = null;
        } elseif (ColorContrast::normalise($color) === null) {
            $errors['color'] = 'Enter a colour such as #2f6f7a.';
            $row['color'] = null;
        } else {
            $row['color'] = ColorContrast::normalise($color);
        }

        $row['is_public'] = $bool($in['is_public'] ?? null, true) ? 1 : 0;
        $row['is_active'] = $bool($in['is_active'] ?? null, true) ? 1 : 0;
        $row['needs_confirmation'] = $bool($in['needs_confirmation'] ?? null, (bool) ($existing['needs_confirmation'] ?? false)) ? 1 : 0;

        // slug: kept while editing unless asked; unique; never a /calendar/ route word
        $slugIn = $str($in['slug'] ?? '');
        if ($existing !== null && $slugIn === '') {
            $row['slug'] = (string) $existing['slug'];
        } else {
            $base = $slugIn !== '' ? Slug::make($slugIn, 150) : Slug::make($title !== '' ? $title : 'entry', 150);
            if (in_array($base, CalendarEntry::RESERVED_SLUGS, true)) {
                $base .= '-entry';
            }
            $row['slug'] = Slug::unique('calendar_entries', $base, isset($existing['id']) ? (int) $existing['id'] : null, 'slug', [], 'entry');
        }

        return ['ok' => $errors === [], 'errors' => $errors, 'row' => $row];
    }

    /**
     * Create ($id = null) or update an entry. Overrides are never touched by an update.
     * @return array{ok:bool,errors:array<string,string>,id?:int,entry?:array<string,mixed>}
     */
    public static function saveEntry(?int $id, array $in, ?int $userId, array $opts = []): array
    {
        $existing = $id !== null ? CalendarEntry::find($id) : null;
        if ($id !== null && $existing === null) {
            return ['ok' => false, 'errors' => ['_' => 'That entry no longer exists.']];
        }
        if ($id !== null && empty($opts['meeting']) && self::ownedByMeeting($id)) {
            return ['ok' => false, 'errors' => ['_' => self::MEETING_OWNED]];
        }
        $v = self::validateEntry($in, $existing, $opts);
        if (!$v['ok']) {
            return ['ok' => false, 'errors' => $v['errors']];
        }
        $row = $v['row'];
        $row['updated_by'] = $userId;
        if ($existing === null) {
            $row['created_by'] = $userId;
            $row['is_system'] = 0;
            $id = self::createUnique(static fn(array $r): int => CalendarEntry::create($r), 'calendar_entries', $row, 150, 'entry');
            AuditLog::record('calendar.create', 'calendar_entry', $id, 'Added a calendar entry');
        } else {
            CalendarEntry::update((int) $existing['id'], $row);
            $id = (int) $existing['id'];
            AuditLog::record('calendar.update', 'calendar_entry', $id, 'Edited a calendar entry');
        }
        CalendarService::flush();
        return ['ok' => true, 'errors' => [], 'id' => $id, 'entry' => CalendarEntry::find($id)];
    }

    /**
     * QUICK ADD: the three-field path. Input: title*, date* (or start_date), date_end, type (slug or id; default: the first visible non-event type),
     * all_day / start_time / end_time, location, repeat (none|daily|weekly|monthly|yearly), until, public (default on).
     * Weekly repeats on the weekday of the date, monthly on its day of the month, yearly on its month and day.
     */
    public static function quickAdd(array $in, ?int $userId): array
    {
        $in = array_filter($in, 'is_scalar');                 // an array value is treated as missing, never cast
        $date = is_scalar($in['date'] ?? null) ? trim((string) $in['date']) : trim((string) ($in['start_date'] ?? ''));
        $repeat = strtolower(trim((string) ($in['repeat'] ?? 'none')));
        $errors = [];
        if (!in_array($repeat, self::SIMPLE_REPEAT, true)) {
            $errors['repeat'] = 'Choose how often it repeats.';
            $repeat = 'none';
        }
        $type = $in['type_id'] ?? $in['type'] ?? '';
        if ($type === '' || $type === null) {
            foreach (CalendarService::types() as $t) {
                if ($t['is_active'] && $t['slug'] !== CalendarType::SLUG_EVENT) {
                    $type = $t['id'];
                    break;
                }
            }
        }
        $input = [
            'title' => $in['title'] ?? '', 'start_date' => $date, 'end_date' => $in['date_end'] ?? $in['end_date'] ?? '',
            'type_id' => is_numeric($type) ? $type : '', 'type' => is_numeric($type) ? '' : $type,
            'all_day' => $in['all_day'] ?? (trim((string) ($in['start_time'] ?? '')) === '' ? '1' : '0'),
            'start_time' => $in['start_time'] ?? '', 'end_time' => $in['end_time'] ?? '', 'location' => $in['location'] ?? '',
            'recurrence' => $repeat, 'recurrence_data' => [], 'repeat_until' => $in['until'] ?? $in['repeat_until'] ?? '',
            'is_public' => $in['public'] ?? $in['is_public'] ?? '1', 'is_active' => '1',
        ];
        if ($errors !== []) {                                      // a rejected repeat saves nothing (the other fields are still checked for the message)
            $v = self::validateEntry($input);
            return ['ok' => false, 'errors' => $errors + $v['errors']];
        }
        return self::saveEntry(null, $input, $userId);
    }

    /** Copy an entry (not its overrides) as a normal, non-system entry titled "... (copy)". @return array{ok:bool,id?:int,error?:string} */
    public static function duplicate(int $id, ?int $userId): array
    {
        $e = CalendarEntry::find($id);
        if ($e === null) {
            return ['ok' => false, 'error' => 'That entry no longer exists.'];
        }
        if (self::ownedByMeeting($id)) {
            return ['ok' => false, 'error' => self::MEETING_OWNED];
        }
        $copy = $e;
        unset($copy['id'], $copy['created_at'], $copy['updated_at']);
        $copy['title'] = mb_substr((string) $e['title'], 0, 180) . ' (copy)';
        $copy['slug'] = Slug::unique('calendar_entries', Slug::make($copy['title'], 150), null, 'slug', [], 'entry');
        $copy['is_system'] = 0;
        $copy['created_by'] = $userId;
        $copy['updated_by'] = $userId;
        $new = self::createUnique(static fn(array $r): int => CalendarEntry::create($r), 'calendar_entries', $copy, 150, 'entry');
        AuditLog::record('calendar.duplicate', 'calendar_entry', $new, 'Duplicated calendar entry #' . $id);
        CalendarService::flush();
        return ['ok' => true, 'id' => $new];
    }

    /** Delete one entry (its overrides go with it). System entries cannot be deleted: hide them instead. @return array{ok:bool,error?:string} */
    public static function delete(int $id): array
    {
        $e = CalendarEntry::find($id);
        if ($e === null) {
            return ['ok' => false, 'error' => 'That entry no longer exists.'];
        }
        if (self::ownedByMeeting($id)) {
            return ['ok' => false, 'error' => self::MEETING_OWNED];
        }
        if ((int) $e['is_system'] === 1) {
            return ['ok' => false, 'error' => 'Seeded holidays and international days cannot be deleted. Hide them instead (set them to inactive).'];
        }
        CalendarEntry::delete($id);
        AuditLog::record('calendar.delete', 'calendar_entry', $id, 'Deleted a calendar entry');
        CalendarService::flush();
        return ['ok' => true];
    }

    /**
     * Bulk activate / deactivate / delete (ids only). Returns the number of rows changed; system entries are skipped by 'delete'.
     * @param 'activate'|'deactivate'|'delete' $action
     * @param list<int> $ids
     * @return array{ok:bool,changed:int,skipped:int}
     */
    public static function bulk(string $action, array $ids, ?int $userId): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $i): bool => $i > 0)));
        if ($ids === [] || !in_array($action, ['activate', 'deactivate', 'delete'], true) || count($ids) > 500) {
            return ['ok' => false, 'changed' => 0, 'skipped' => count($ids)];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        // entries owned by a meeting are never changed here (the meeting is the source of truth; they count as "skipped")
        $notMeeting = Database::tableExists('meetings') ? ' AND id NOT IN (SELECT calendar_entry_id FROM meetings)' : '';
        if ($action === 'delete') {
            $changed = Database::execute("DELETE FROM calendar_entries WHERE is_system = 0 AND id IN ({$in}){$notMeeting}", $ids);
        } else {
            $changed = Database::execute("UPDATE calendar_entries SET is_active = ?, updated_by = ? WHERE id IN ({$in}){$notMeeting}", [$action === 'activate' ? 1 : 0, $userId, ...$ids]);
        }
        AuditLog::record('calendar.bulk_' . $action, 'calendar_entry', null, ucfirst($action) . ' ' . $changed . ' calendar entries', ['ids' => $ids]);
        CalendarService::flush();
        return ['ok' => true, 'changed' => $changed, 'skipped' => count($ids) - $changed];
    }

    // ------------------------------------------------------------------ overrides: ONE occurrence

    /**
     * Change one occurrence of an entry without touching the series.
     *
     * $in['action']:
     *   move     new_date* (or new_start_time to change only the time), new_end_time, note, confirmed (default 0)
     *   cancel   note
     *   confirm  new_date (empty = the predicted day is right), note   -> confirmed = 1; clears "date to be confirmed" for this occurrence
     *   restore  un-cancel
     *   reset    delete the override (back to the rule)
     * $occurrenceDate is the day the RULE produces (the occurrence_date of the occurrence), NOT its moved day.
     *
     * @return array{ok:bool,errors:array<string,string>,override?:?array<string,mixed>}
     */
    public static function saveOverride(int $entryId, string $occurrenceDate, array $in, ?int $userId): array
    {
        $entry = CalendarEntry::find($entryId);
        if ($entry === null) {
            return ['ok' => false, 'errors' => ['_' => 'That entry no longer exists.']];
        }
        if (self::ownedByMeeting($entryId)) {
            return ['ok' => false, 'errors' => ['_' => self::MEETING_OWNED]];
        }
        $in = array_filter($in, 'is_scalar');                 // an array value is treated as missing, never cast
        if (!CivilDate::valid($occurrenceDate) || RecurrenceRules::starts($entry, $occurrenceDate, $occurrenceDate) !== [$occurrenceDate]) {
            return ['ok' => false, 'errors' => ['occurrence_date' => 'That date is not an occurrence of this entry.']];
        }
        $action = (string) ($in['action'] ?? '');
        $current = Database::one('SELECT * FROM calendar_overrides WHERE entry_id = ? AND occurrence_date = ?', [$entryId, $occurrenceDate]);
        if ($action === 'reset') {
            if ($current !== null) {
                CalendarOverride::delete((int) $current['id']);
                AuditLog::record('calendar.override_reset', 'calendar_entry', $entryId, 'Reset one occurrence', ['occurrence' => $occurrenceDate]);
                CalendarService::flush();
            }
            return ['ok' => true, 'errors' => [], 'override' => null];
        }
        if (!in_array($action, ['move', 'cancel', 'confirm', 'restore'], true)) {
            return ['ok' => false, 'errors' => ['action' => 'Unknown action.']];
        }

        $errors = [];
        $data = $current !== null ? [
            'new_date' => $current['new_date'], 'new_start_time' => $current['new_start_time'], 'new_end_time' => $current['new_end_time'],
            'cancelled' => (int) $current['cancelled'], 'confirmed' => (int) $current['confirmed'], 'note' => $current['note'],
        ] : ['new_date' => null, 'new_start_time' => null, 'new_end_time' => null, 'cancelled' => 0, 'confirmed' => 0, 'note' => null];

        $newDate = trim((string) ($in['new_date'] ?? ''));
        if ($newDate !== '' && !CivilDate::valid($newDate)) {
            $errors['new_date'] = 'Enter a valid date (YYYY-MM-DD).';
        }
        $note = Sanitizer::text(trim((string) ($in['note'] ?? '')));
        if (mb_strlen($note) > 300) {
            $errors['note'] = 'The note can have at most 300 characters.';
        }
        if (array_key_exists('note', $in)) {
            $data['note'] = $note !== '' ? $note : null;
        }

        if ($action === 'move') {
            $nst = trim((string) ($in['new_start_time'] ?? ''));
            $net = trim((string) ($in['new_end_time'] ?? ''));
            if ($nst !== '' && CivilDate::time($nst) === null) {
                $errors['new_start_time'] = 'Enter the time as HH:MM (24 hours).';
            }
            if ($net !== '' && CivilDate::time($net) === null) {
                $errors['new_end_time'] = 'Enter the time as HH:MM (24 hours).';
            }
            if ($net !== '' && $nst === '') {
                $errors['new_start_time'] = 'Enter a start time as well.';
            }
            // a one-day occurrence cannot end before it starts (a longer one may: its end time belongs to the last day)
            if ($nst !== '' && $net !== '' && !isset($errors['new_start_time']) && !isset($errors['new_end_time']) && RecurrenceRules::duration($entry) === 0
                && CivilDate::time($nst) !== null && CivilDate::time($net) !== null && CivilDate::time($net) <= CivilDate::time($nst)) {
                $errors['new_end_time'] = 'The end time must be after the start time. For something that ends after midnight, give the entry an end date on the next day.';
            }
            if ($newDate === '' && $nst === '' && $errors === []) {
                $errors['new_date'] = 'Choose the new date, or a new time.';
            }
            if ($errors === []) {
                $data['new_date'] = ($newDate !== '' && $newDate !== $occurrenceDate) ? $newDate : null;
                $data['new_start_time'] = $nst !== '' ? CivilDate::time($nst) . ':00' : null;
                $data['new_end_time'] = $net !== '' ? CivilDate::time($net) . ':00' : null;
                $data['cancelled'] = 0;
                $data['confirmed'] = in_array($in['confirmed'] ?? '0', [1, '1', true, 'on'], true) ? 1 : $data['confirmed'];
            }
        } elseif ($action === 'cancel') {
            $data['cancelled'] = 1;
        } elseif ($action === 'restore') {
            $data['cancelled'] = 0;
        } else {   // confirm
            if ($errors === []) {
                $data['confirmed'] = 1;
                $data['cancelled'] = 0;
                if ($newDate !== '') {
                    $data['new_date'] = $newDate !== $occurrenceDate ? $newDate : null;
                }
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $useless = $data['new_date'] === null && $data['new_start_time'] === null && !$data['cancelled'] && !$data['confirmed'] && $data['note'] === null;
        if ($useless) {
            if ($current !== null) {
                CalendarOverride::delete((int) $current['id']);
            }
            CalendarService::flush();
            return ['ok' => true, 'errors' => [], 'override' => null];
        }
        $data['updated_by'] = $userId;
        if ($current === null) {
            try {
                $oid = CalendarOverride::create($data + ['entry_id' => $entryId, 'occurrence_date' => $occurrenceDate]);
            } catch (\PDOException $e) {
                if ((string) $e->getCode() !== '23000') {
                    throw $e;
                }
                // another editor saved the same occurrence a moment ago: the unique key refused the second row, so update that one (last write wins)
                $row = Database::one('SELECT id FROM calendar_overrides WHERE entry_id = ? AND occurrence_date = ?', [$entryId, $occurrenceDate]);
                if ($row === null) {
                    throw $e;
                }
                $oid = (int) $row['id'];
                CalendarOverride::update($oid, $data);
            }
        } else {
            $oid = (int) $current['id'];
            CalendarOverride::update($oid, $data);
        }
        AuditLog::record('calendar.override_' . $action, 'calendar_entry', $entryId, ucfirst($action) . ' one occurrence', ['occurrence' => $occurrenceDate]);
        CalendarService::flush();
        return ['ok' => true, 'errors' => [], 'override' => CalendarOverride::find($oid)];
    }

    /** Delete an override by id, only when it belongs to $entryId (IDOR guard). */
    public static function deleteOverride(int $entryId, int $overrideId): bool
    {
        $n = Database::execute('DELETE FROM calendar_overrides WHERE id = ? AND entry_id = ?', [$overrideId, $entryId]);
        if ($n > 0) {
            AuditLog::record('calendar.override_reset', 'calendar_entry', $entryId, 'Reset one occurrence');
            CalendarService::flush();
        }
        return $n > 0;
    }

    // ------------------------------------------------------------------ types

    /**
     * Create or update a type. Input: name*, color*, icon, sort_order, is_active, show_on_public, slug (create only).
     * @return array{ok:bool,errors:array<string,string>,id?:int,pair?:array<string,mixed>}
     */
    public static function saveType(?int $id, array $in, ?int $userId = null): array
    {
        $existing = $id !== null ? CalendarType::find($id) : null;
        if ($id !== null && $existing === null) {
            return ['ok' => false, 'errors' => ['_' => 'That type no longer exists.']];
        }
        $errors = [];
        $in = array_filter($in, 'is_scalar');                 // an array value is treated as missing, never cast
        $name = Sanitizer::text(trim((string) ($in['name'] ?? '')));
        if ($name === '' || mb_strlen($name) > 80) {
            $errors['name'] = 'Enter a name (at most 80 characters).';
        }
        $color = ColorContrast::normalise($in['color'] ?? null);
        if ($color === null) {
            $errors['color'] = 'Enter a colour such as #2f6f7a.';
        }
        $icon = trim((string) ($in['icon'] ?? 'calendar'));
        if (!in_array($icon, Icons::names(), true)) {
            $errors['icon'] = 'Choose an icon from the list.';
        }
        $sort = trim((string) ($in['sort_order'] ?? '100'));
        if (!preg_match('/^-?\d{1,5}$/', $sort)) {
            $errors['sort_order'] = 'Enter a whole number.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        $flag = static fn(string $k, bool $d): int => (array_key_exists($k, $in) ? in_array($in[$k], [1, '1', true, 'on'], true) : $d) ? 1 : 0;
        $row = [
            'name' => $name, 'color' => $color, 'icon' => $icon, 'sort_order' => (int) $sort,
            'is_active' => $flag('is_active', true), 'show_on_public' => $flag('show_on_public', true),
        ];
        if ($existing === null) {
            $row['slug'] = Slug::unique('calendar_types', Slug::make($name, 50), null, 'slug', [], 'type');
            $row['is_system'] = 0;
            $id = self::createUnique(static fn(array $r): int => CalendarType::create($r), 'calendar_types', $row, 50, 'type', 'name');
            AuditLog::record('calendar.type_create', 'calendar_type', $id, 'Added a calendar type');
        } else {
            CalendarType::update((int) $existing['id'], $row);
            $id = (int) $existing['id'];
            AuditLog::record('calendar.type_update', 'calendar_type', $id, 'Edited a calendar type');
        }
        CalendarService::flush();
        return ['ok' => true, 'errors' => [], 'id' => $id, 'pair' => ColorContrast::pair($color)];
    }

    /** Delete a type that no entry uses and that is not a system type. @return array{ok:bool,error?:string} */
    public static function deleteType(int $id): array
    {
        $t = CalendarType::find($id);
        if ($t === null) {
            return ['ok' => false, 'error' => 'That type no longer exists.'];
        }
        if ((int) $t['is_system'] === 1 || in_array($t['slug'], ['programme', 'international-day', 'meeting', 'deadline', 'other'], true)) {
            return ['ok' => false, 'error' => 'This type is part of the calendar and cannot be deleted. Hide it instead.'];
        }
        if ((int) Database::value('SELECT COUNT(*) FROM calendar_entries WHERE type_id = ?', [$id], 0) > 0) {
            return ['ok' => false, 'error' => 'Entries still use this type. Move them to another type first, or hide the type.'];
        }
        CalendarType::delete($id);
        AuditLog::record('calendar.type_delete', 'calendar_type', $id, 'Deleted a calendar type');
        CalendarService::flush();
        return ['ok' => true];
    }

    // ------------------------------------------------------------------ CSV mapping (import / export / template)

    /**
     * An entry row as the flat CSV record (keys = CSV_COLUMNS). Pass the output through CsvExport (formula-safe).
     * @param array<int,array<string,mixed>> $typesById
     */
    public static function csvRecord(array $entry, array $typesById): array
    {
        return [
            'title' => $entry['title'], 'type' => $typesById[(int) $entry['type_id']]['slug'] ?? '', 'start_date' => $entry['start_date'],
            'end_date' => $entry['end_date'] ?? '', 'all_day' => (int) $entry['all_day'] === 1 ? 'yes' : 'no',
            'start_time' => $entry['start_time'] ? substr((string) $entry['start_time'], 0, 5) : '', 'end_time' => $entry['end_time'] ? substr((string) $entry['end_time'], 0, 5) : '',
            'location' => $entry['location'] ?? '',
            'repeat' => (string) $entry['recurrence'],
            'repeat_until' => $entry['repeat_until'] ?? '', 'public' => (int) $entry['is_public'] === 1 ? 'yes' : 'no',
            'description' => IcsBuilder::plainText((string) ($entry['description'] ?? '')), 'link_url' => $entry['link_url'] ?? '',
            'source_label' => $entry['source_label'] ?? '', 'source_url' => $entry['source_url'] ?? '',
        ];
    }

    /**
     * Map one CSV record (keys = CSV_COLUMNS, any case) to validateEntry() input. Only the simple repeats are accepted from CSV
     * (none, daily, weekly, monthly, yearly); the description is plain text (escaped into paragraphs).
     * @return array<string,mixed>
     */
    public static function inputFromCsv(array $rec): array
    {
        $rec = array_change_key_case(array_map(static fn($v): string => is_scalar($v) ? trim((string) $v) : '', $rec));
        $yes = static fn(string $v): bool => in_array(strtolower($v), ['1', 'yes', 'y', 'true', 'on'], true);
        $desc = $rec['description'] ?? '';
        return [
            'title' => $rec['title'] ?? '', 'type' => strtolower($rec['type'] ?? ''), 'start_date' => $rec['start_date'] ?? '', 'end_date' => $rec['end_date'] ?? '',
            'all_day' => ($rec['all_day'] ?? '') === '' ? (($rec['start_time'] ?? '') === '' ? '1' : '0') : ($yes($rec['all_day']) ? '1' : '0'),
            'start_time' => $rec['start_time'] ?? '', 'end_time' => $rec['end_time'] ?? '', 'location' => $rec['location'] ?? '',
            'recurrence' => ($rec['repeat'] ?? '') === '' ? 'none' : strtolower($rec['repeat']), 'recurrence_data' => [],
            'repeat_until' => $rec['repeat_until'] ?? '', 'is_public' => ($rec['public'] ?? '') === '' ? '1' : ($yes($rec['public']) ? '1' : '0'),
            'description' => $desc === '' ? '' : '<p>' . nl2br(htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'), false) . '</p>',
            'link_url' => $rec['link_url'] ?? '', 'source_label' => $rec['source_label'] ?? '', 'source_url' => $rec['source_url'] ?? '', 'is_active' => '1',
        ];
    }
}
