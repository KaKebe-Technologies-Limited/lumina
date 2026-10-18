<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Models\CalendarEntry;
use App\Models\CalendarType;
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CalendarWriter;
use App\Services\Calendar\CivilDate;
use App\Services\Calendar\IslamicCalendar;
use App\Services\Calendar\OccurrenceExpander;
use App\Services\Calendar\RecurrenceRules;

/**
 * Admin > Calendar: manager (month and list), holidays tab, the entry editor, one-occurrence changes, bulk actions and the settings panel.
 * Contract: docs/CALENDAR.md. OWNER: ADMIN agent. Every change goes through CalendarWriter (validation, sanitising, audit, "system entries are
 * never deleted"); this class only turns a request into the writer's input, checks the ability and shows the result.
 *
 * Abilities: calendar.manage (routes and here), calendar.advanced (the advanced repeat types: enforced by CalendarWriter::validateEntry through
 * the allow_advanced option, and the selector is only shown to those who hold it), calendar.delete (BULK delete), settings.manage (settings panel).
 *
 * Form conventions: the repeat settings are posted as friendly fields (rule_interval, rule_weekdays[], rule_monthly_mode, rule_nth, rule_weekday,
 * rule_month, rule_easter, rule_easter_custom, rule_islamic) and mapped to recurrence_data in ONE place (ruleFromForm), which the live-preview
 * endpoint uses too, so the preview and the saved entry can never disagree.
 */
final class CalendarController extends AdminController
{
    /** most entries the list view loads before filtering stops being useful (the next date of each is computed in PHP) */
    public const LIST_MAX = 1500;
    public const SORTS = ['next' => 'Soonest date first', 'title' => 'Title A to Z', 'updated' => 'Recently changed'];
    public const STATUSES = ['' => 'Any', 'active' => 'Shown on the website', 'private' => 'Team only (not public)', 'hidden' => 'Hidden'];
    public const SOURCES = ['' => 'Any', 'own' => 'Added by our team', 'system' => 'Holidays and days that came with the site'];

    // ------------------------------------------------------------------ manager

    /** GET /admin/calendar  (?view=month|list, ?month=YYYY-MM, ?day=, ?date= prefill of the quick-add form, list filters) */
    public function index(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $available = CalendarService::available();
        $view = $request->string('view') === 'list' ? 'list' : 'month';
        $data = [
            'available' => $available, 'view' => $view, 'types' => CalendarService::types(), 'addTypes' => $this->addableTypes(),
            'canDelete' => can('calendar.delete'), 'canSettings' => can('settings.manage'), 'today' => CivilDate::today(),
            'month' => null, 'selectedDay' => '',
        ];
        if ($available) {
            $data = ($view === 'list' ? $this->listData($request) : $this->monthData($request)) + $data;
        }
        $data['quick'] = $this->quickDefaults($request, (string) ($data['grid']['month'] ?? ''), (string) ($data['selectedDay'] ?? ''));
        return $this->render('admin/calendar/index', $data, [
            'title' => 'Calendar & schedule', 'nav' => 'calendar', 'breadcrumbs' => [['Calendar & schedule', null]],
        ]);
    }

    /** @return array<string,mixed> */
    private function monthData(Request $request): array
    {
        $month = $this->monthParam($request);
        $slug = $request->string('type');
        $typeFilter = ($slug !== '' && CalendarService::typeBySlug($slug) !== null) ? $slug : '';
        $grid = CalendarService::monthGrid($month, ['public' => false, 'include_cancelled' => true] + ($typeFilter !== '' ? ['types' => [$typeFilter]] : []));
        $first = $grid['month'] . '-01';
        $last = CivilDate::monthEnd($grid['month']);
        $day = $request->string('day');
        $selected = (CivilDate::valid($day) && $day >= $grid['from'] && $day <= $grid['to']) ? $day : '';
        $selItems = [];
        if ($selected !== '') {
            foreach ($grid['weeks'] as $week) {
                foreach ($week as $cell) {
                    if ($cell['date'] === $selected) {
                        $selItems = $cell['items'];
                    }
                }
            }
        }
        $inMonth = array_values(array_filter($grid['items'], static fn(array $o): bool => $o['start_date'] <= $last && $o['end_date'] >= $first));
        $thisYear = (int) substr(CivilDate::today(), 0, 4);
        $years = range(min($thisYear - 2, $grid['year']), max($thisYear + 8, $grid['year']));
        return [
            'grid' => $grid, 'typeFilter' => $typeFilter, 'selectedDay' => $selected, 'selectedItems' => $selItems,
            'agenda' => CalendarService::groupByDay($inMonth, $first, $last), 'years' => $years,
        ];
    }

    /** ?month=YYYY-MM, or the ?m=&y= pair of the "go to" form; the current month otherwise. */
    private function monthParam(Request $request): string
    {
        $m = $request->string('month');
        if (!CivilDate::validMonth($m)) {
            $mm = $request->string('m');
            $yy = $request->string('y');
            $m = (ctype_digit($mm) && ctype_digit($yy)) ? sprintf('%04d-%02d', (int) $yy, (int) $mm) : '';
        }
        return self::safeMonth($m);
    }

    /**
     * A month the grid can draw: the engine handles 1900..2200 and the grid reaches into the neighbouring months, so the edges are left out
     * (a bad or extreme value falls back to the current month instead of an error page).
     */
    public static function safeMonth(string $m): string
    {
        return (CivilDate::validMonth($m) && $m >= '1901-01' && $m <= '2199-12') ? $m : substr(CivilDate::today(), 0, 7);
    }

    /** @return array{date:string,type:string} defaults of the quick-add form (old input wins inside the view) */
    private function quickDefaults(Request $request, string $month, string $selectedDay): array
    {
        $today = CivilDate::today();
        $date = $request->string('date');
        if (!CivilDate::valid($date)) {
            $date = $selectedDay !== '' ? $selectedDay : (($month === '' || $month === substr($today, 0, 7)) ? $today : $month . '-01');
        }
        $addable = array_column($this->addableTypes(), 'slug');
        $type = $request->string('type');
        if (!in_array($type, $addable, true)) {
            $type = in_array('programme', $addable, true) ? 'programme' : (string) ($addable[0] ?? '');
        }
        return ['date' => $date, 'type' => $type];
    }

    /** Active types an entry can be given (events are read from the Events screen, never stored here). */
    private function addableTypes(?int $keepId = null): array
    {
        return array_values(array_filter(
            CalendarService::types(),
            static fn(array $t): bool => $t['slug'] !== CalendarType::SLUG_EVENT && ($t['is_active'] || $t['id'] === $keepId)
        ));
    }

    /**
     * The list view: entries (not occurrences) with search, filters, the next date of each (PHP, no query per row), sorting and paging.
     * @return array<string,mixed>
     */
    private function listData(Request $request): array
    {
        $f = [
            'q' => mb_substr($request->string('q'), 0, 100), 'type' => $request->string('type'), 'year' => $request->string('year'),
            'status' => $request->string('status'), 'source' => $request->string('source'), 'confirm' => $request->string('confirm') === '1' ? '1' : '',
            'sort' => $request->string('sort'),
        ];
        $type = $f['type'] !== '' ? CalendarService::typeBySlug($f['type']) : null;
        $f['type'] = $type !== null ? $type['slug'] : '';
        $f['year'] = (preg_match('/^\d{4}$/', $f['year']) && (int) $f['year'] >= 1990 && (int) $f['year'] <= 2200) ? $f['year'] : '';
        $f['status'] = array_key_exists($f['status'], self::STATUSES) ? $f['status'] : '';
        $f['source'] = array_key_exists($f['source'], self::SOURCES) ? $f['source'] : '';
        $f['sort'] = array_key_exists($f['sort'], self::SORTS) ? $f['sort'] : 'next';

        $sql = 'SELECT * FROM calendar_entries WHERE 1 = 1';
        $params = [];
        if ($type !== null) {
            $sql .= ' AND type_id = ?';
            $params[] = $type['id'];
        }
        if ($f['q'] !== '') {
            $like = '%' . strtr($f['q'], ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']) . '%';
            $sql .= " AND (title LIKE ? ESCAPE '\\\\' OR location LIKE ? ESCAPE '\\\\' OR source_label LIKE ? ESCAPE '\\\\')";
            array_push($params, $like, $like, $like);
        }
        // "shown on the website" also needs a type that visitors can see (active and not team-only)
        $visibleTypes = array_map(static fn(array $t): int => (int) $t['id'], CalendarService::publicTypes());
        $inVisible = $visibleTypes === [] ? '0 = 1' : 'type_id IN (' . implode(',', array_fill(0, count($visibleTypes), '?')) . ')';
        if ($f['status'] === 'active') {
            $sql .= ' AND is_active = 1 AND is_public = 1 AND ' . $inVisible;
            array_push($params, ...$visibleTypes);
        } elseif ($f['status'] === 'private') {
            $sql .= ' AND is_active = 1 AND (is_public = 0 OR NOT (' . $inVisible . '))';
            array_push($params, ...$visibleTypes);
        } elseif ($f['status'] === 'hidden') {
            $sql .= ' AND is_active = 0';
        }
        $sql .= match ($f['source']) {
            'system' => ' AND is_system = 1',
            'own' => ' AND is_system = 0',
            default => '',
        };
        if ($f['confirm'] === '1') {
            $sql .= ' AND needs_confirmation = 1';
        }
        if ($f['year'] !== '') {
            // the series touches that year: it starts before the year ends and has not ended before the year starts
            $sql .= " AND start_date <= ? AND ((recurrence <> 'none' AND (repeat_until IS NULL OR repeat_until >= ?)) OR COALESCE(end_date, start_date) >= ?)";
            array_push($params, $f['year'] . '-12-31', $f['year'] . '-01-01', $f['year'] . '-01-01');
        }
        $sql .= ' ORDER BY id ASC LIMIT ' . self::LIST_MAX;
        $entries = Database::all($sql, $params);

        $typesById = [];
        foreach (CalendarService::types() as $t) {
            $typesById[$t['id']] = $t;
        }
        $next = $this->nextDates($entries, $f['year'] !== '' ? (int) $f['year'] : null);
        $rows = [];
        foreach ($entries as $e) {
            $rows[] = [
                'entry' => $e, 'type' => $typesById[(int) $e['type_id']] ?? null, 'next' => $next[(int) $e['id']] ?? null,
                'rule' => RecurrenceRules::describe($e),
            ];
        }
        $today = CivilDate::today();
        // sort keys are built once; "soonest first" = entries with a date from today on (ascending), then the ones that are over (latest first)
        $invert = static fn(string $d): string => str_pad((string) (99999999 - (int) str_replace('-', '', $d)), 8, '0', STR_PAD_LEFT);
        foreach ($rows as $i => $r) {
            $n = $r['next'];
            $title = mb_strtolower((string) $r['entry']['title']);
            $rows[$i]['key'] = match ($f['sort']) {
                'title' => [$title, ''],
                'updated' => [$invert(substr((string) $r['entry']['updated_at'], 0, 10)) . substr((string) $r['entry']['updated_at'], 11), $title],
                default => $n !== null && $n['end_date'] >= $today ? ['0' . $n['start_date'], $title] : ['1' . $invert((string) ($n['start_date'] ?? $r['entry']['start_date'])), $title],
            };
        }
        usort($rows, static fn(array $a, array $b): int => $a['key'] <=> $b['key']);

        $pagination = Pagination::make(count($rows), $request->page(), $this->perPage());
        $pagination->items = array_slice($rows, $pagination->offset, $pagination->perPage);
        $thisYear = (int) substr($today, 0, 4);
        $years = range($thisYear - 1, $thisYear + 6);
        if ($f['year'] !== '' && !in_array((int) $f['year'], $years, true)) {
            $years[] = (int) $f['year'];
            sort($years);
        }
        $counts = Database::one(
            'SELECT COUNT(*) AS total, COALESCE(SUM(is_system = 0), 0) AS own, COALESCE(SUM(is_active = 0), 0) AS hidden, COALESCE(SUM(needs_confirmation = 1), 0) AS confirm FROM calendar_entries'
        ) ?: [];
        return ['f' => $f, 'pagination' => $pagination, 'years' => $years, 'counts' => $counts, 'filtered' => array_filter([$f['q'], $f['type'], $f['year'], $f['status'], $f['source'], $f['confirm']]) !== []];
    }

    /**
     * The first date of each entry that is not cancelled: from today (or inside the chosen year). PHP only: ONE query for the overrides.
     * @param list<array<string,mixed>> $entries
     * @return array<int,array<string,mixed>|null> entry id => occurrence
     */
    private function nextDates(array $entries, ?int $year): array
    {
        if ($entries === []) {
            return [];
        }
        $today = CivilDate::today();
        [$from, $to] = $year !== null ? [$year . '-01-01', $year . '-12-31'] : [$today, CivilDate::addDays($today, 800)];
        $near = $year !== null ? $to : CivilDate::addDays($from, 45);
        $ov = CalendarService::overridesFor(array_map(static fn(array $e): int => (int) $e['id'], $entries), $from, $to);
        $out = [];
        foreach ($entries as $e) {
            $found = null;
            foreach ([[$from, $near], [CivilDate::addDays($near, 1), $to]] as [$a, $b]) {
                if ($found !== null || $a > $b) {
                    continue;
                }
                $occ = OccurrenceExpander::expandEntries([$e], $ov, $a, $b, ['include_cancelled' => false, 'max' => 400]);
                if ($occ !== []) {
                    OccurrenceExpander::sort($occ);
                    $found = $occ[0];
                }
            }
            $out[(int) $e['id']] = $found;
        }
        return $out;
    }

    // ------------------------------------------------------------------ holidays tab

    /** GET /admin/calendar/holidays  (Uganda public holidays with the "Confirm date" workflow, and the international days) */
    public function holidays(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $available = CalendarService::available();
        // holidays the team added itself (extra days the government declared): not part of the seeded list, but they belong on this screen
        $extra = [];
        $holidayType = $available ? CalendarService::typeBySlug(CalendarService::SLUG_HOLIDAY) : null;
        if ($holidayType !== null) {
            $rows = Database::all('SELECT * FROM calendar_entries WHERE type_id = ? AND is_system = 0 ORDER BY start_date DESC, id DESC LIMIT 100', [$holidayType['id']]);
            $next = $this->nextDates($rows, null);
            foreach ($rows as $r) {
                $extra[] = ['entry' => $r, 'next' => $next[(int) $r['id']] ?? null];
            }
        }
        return $this->render('admin/calendar/holidays', [
            'available' => $available, 'extra' => $extra,
            'holidays' => $available ? CalendarService::systemEntries(CalendarService::SLUG_HOLIDAY) : [],
            'days' => $available ? CalendarService::systemEntries('international-day') : [],
            'toConfirm' => $available ? CalendarService::datesToConfirm(60) : [],
            'today' => CivilDate::today(),
            'islamic' => IslamicCalendar::HOLIDAYS,
        ], ['title' => 'Holidays & days', 'nav' => 'calendar_holidays', 'breadcrumbs' => [['Calendar & schedule', url('admin/calendar')], ['Holidays & days', null]]]);
    }

    // ------------------------------------------------------------------ the entry editor

    /** GET /admin/calendar/create  (?date=YYYY-MM-DD and ?type=slug prefill) */
    public function create(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $date = $request->string('date');
        $date = CivilDate::valid($date) ? $date : CivilDate::today();
        $type = CalendarService::typeBySlug($request->string('type'));
        $addable = $this->addableTypes();
        $typeId = ($type !== null && $type['slug'] !== CalendarType::SLUG_EVENT && $type['is_active']) ? $type['id'] : (int) ($addable[0]['id'] ?? 0);
        foreach ($addable as $t) {
            if ($type === null && $t['slug'] === 'programme') {
                $typeId = $t['id'];
            }
        }
        return $this->form([
            'id' => null, 'type_id' => $typeId, 'title' => '', 'slug' => '', 'description' => '', 'location' => '', 'all_day' => 1, 'start_date' => $date, 'end_date' => null,
            'start_time' => null, 'end_time' => null, 'recurrence' => 'none', 'recurrence_data' => null, 'repeat_until' => null, 'program_id' => null, 'project_id' => null,
            'event_id' => null, 'link_url' => null, 'link_label' => null, 'color' => null, 'source_label' => null, 'source_url' => null, 'is_system' => 0, 'is_public' => 1,
            'is_active' => 1, 'needs_confirmation' => 0,
        ], true);
    }

    /** GET /admin/calendar/{id}/edit */
    public function edit(Request $request, int $id): Response
    {
        $this->authorize('calendar.manage');
        return $this->form(CalendarEntry::findOrFail($id), false);
    }

    /** @param array<string,mixed> $entry */
    private function form(array $entry, bool $isNew): Response
    {
        $id = $isNew ? null : (int) $entry['id'];
        $canAdvanced = can('calendar.advanced');
        $rule = RecurrenceRules::decode($entry['recurrence_data'] ?? null);
        $advancedUsed = self::isAdvanced($entry);
        $start = CivilDate::valid((string) $entry['start_date']) ? (string) $entry['start_date'] : CivilDate::today();
        $sp = CivilDate::parse($start) ?? [2026, 1, 1];
        $ruleValues = [
            'interval' => (int) ($rule['interval'] ?? 1),
            'weekdays' => array_map('intval', is_array($rule['weekdays'] ?? null) ? $rule['weekdays'] : [CivilDate::weekday($start)]),
            'monthly_mode' => (string) ($rule['mode'] ?? 'day'),
            'nth' => (int) ($rule['nth'] ?? (int) ceil($sp[2] / 7)),
            'weekday' => (int) ($rule['weekday'] ?? CivilDate::weekday($start)),
            'month' => (int) ($rule['month'] ?? $sp[1]),
            'easter_offset' => (int) ($rule['easter_offset'] ?? -2),
            'islamic' => (string) ($rule['islamic'] ?? 'eid_al_fitr'),
        ];
        $preview = CalendarService::previewOccurrences([
            'start_date' => $entry['start_date'], 'end_date' => $entry['end_date'] ?? '', 'recurrence' => $entry['recurrence'],
            'recurrence_data' => $entry['recurrence_data'], 'repeat_until' => $entry['repeat_until'] ?? '',
        ], 5, $id);

        $occurrences = [];
        $overrides = [];
        if (!$isNew) {
            if ((string) $entry['recurrence'] !== 'none') {
                $occurrences = CalendarService::nextOccurrences($entry, 8, null, true);
            }
            $overrides = Database::all('SELECT * FROM calendar_overrides WHERE entry_id = ? ORDER BY occurrence_date DESC, id DESC LIMIT 100', [$id]);
        }
        $title = $isNew ? 'Add a date' : (string) $entry['title'];
        return $this->render('admin/calendar/form', [
            'entry' => $entry, 'isNew' => $isNew, 'types' => $this->addableTypes($isNew ? null : (int) $entry['type_id']), 'type' => CalendarService::typeById((int) $entry['type_id']),
            'canAdvanced' => $canAdvanced, 'advancedUsed' => $advancedUsed, 'locked' => !$isNew && !$canAdvanced && $advancedUsed,
            'rule' => $ruleValues, 'ruleText' => RecurrenceRules::describe($entry), 'preview' => $preview,
            'occurrences' => $occurrences, 'overrides' => $overrides, 'islamic' => IslamicCalendar::HOLIDAYS,
            'programs' => Database::all('SELECT id, title FROM programs ORDER BY sort_order ASC, title ASC LIMIT 200'),
            'projects' => Database::all('SELECT id, title FROM projects ORDER BY sort_order ASC, title ASC LIMIT 200'),
            'events' => Database::all('SELECT id, title, start_at FROM events ORDER BY start_at DESC LIMIT 200'),
            'today' => CivilDate::today(), 'canDelete' => !$isNew && (int) $entry['is_system'] !== 1,
        ], [
            'title' => $isNew ? 'Add a date' : 'Edit: ' . $title, 'nav' => 'calendar',
            'breadcrumbs' => [['Calendar & schedule', url('admin/calendar')], [$isNew ? 'Add a date' : mb_strimwidth($title, 0, 60, '...'), null]],
        ]);
    }

    /** POST /admin/calendar - the full form. Advanced repeat types need calendar.advanced (checked by the writer). */
    public function store(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $res = CalendarWriter::saveEntry(null, $this->entryInput($request->post() ?: [], null), $this->userId(), ['allow_advanced' => can('calendar.advanced')]);
        if (!$res['ok']) {
            return $this->failed($res['errors'], 'admin/calendar/create');
        }
        $e = $res['entry'] ?? [];
        $start = (string) ($e['start_date'] ?? CivilDate::today());
        return $this->done($res, '"' . (string) ($e['title'] ?? 'The entry') . '" was added to the calendar.', 'admin/calendar?month=' . substr($start, 0, 7) . '&day=' . $start . '#day-panel');
    }

    /** POST /admin/calendar/{id} */
    public function update(Request $request, int $id): Response
    {
        $this->authorize('calendar.manage');
        $existing = CalendarEntry::findOrFail($id);
        $res = CalendarWriter::saveEntry($id, $this->entryInput($request->post() ?: [], $existing), $this->userId(), ['allow_advanced' => can('calendar.advanced')]);
        if (!$res['ok']) {
            return $this->failed($res['errors'], 'admin/calendar/' . $id . '/edit');
        }
        return $this->done($res, 'Your changes were saved.', 'admin/calendar/' . $id . '/edit');
    }

    /**
     * The form as the writer expects it: the friendly repeat fields become recurrence_data, a seeded entry keeps its type, an editor keeps an
     * advanced rule exactly as it is, an Islamic holiday always stays "date to be confirmed" (the date is a prediction).
     * @param array<string,mixed> $in
     * @param array<string,mixed>|null $existing
     * @return array<string,mixed>
     */
    private function entryInput(array $in, ?array $existing): array
    {
        $rule = self::ruleFromForm($in);
        $in['recurrence'] = $rule['recurrence'];
        $in['recurrence_data'] = $rule['recurrence_data'];
        unset($in['slug'], $in['type']);
        if ($existing !== null) {
            if ((int) $existing['is_system'] === 1) {
                $in['type_id'] = $existing['type_id'];
            }
            if (!can('calendar.advanced') && self::isAdvanced($existing)) {
                foreach (['recurrence', 'recurrence_data', 'start_date', 'end_date', 'repeat_until'] as $k) {
                    $in[$k] = $existing[$k] ?? '';
                }
            }
        }
        if ($in['recurrence'] === 'islamic') {
            $in['needs_confirmation'] = '1';
        }
        return $in;
    }

    /** An entry whose repeat type only an administrator may choose. */
    public static function isAdvanced(array $entry): bool
    {
        $data = RecurrenceRules::decode($entry['recurrence_data'] ?? null);
        return in_array((string) ($entry['recurrence'] ?? 'none'), CalendarWriter::ADVANCED, true) || (($data['mode'] ?? '') === 'nth_weekday');
    }

    /**
     * Friendly repeat fields to the writer's recurrence + recurrence_data. Only the keys that belong to the chosen repeat type are sent (the
     * writer rejects unknown keys), and empty values are left out so the engine's defaults apply. Callers that already send recurrence_data
     * (and no rule_* field) are passed through unchanged. Shared with CalendarApiController::preview.
     * @param array<string,mixed> $in
     * @return array{recurrence:string,recurrence_data:mixed}
     */
    public static function ruleFromForm(array $in): array
    {
        $rec = is_scalar($in['recurrence'] ?? null) ? trim((string) $in['recurrence']) : 'none';
        $rec = $rec === '' ? 'none' : $rec;
        $hasRuleFields = false;
        foreach (array_keys($in) as $k) {
            if (str_starts_with((string) $k, 'rule_')) {
                $hasRuleFields = true;
                break;
            }
        }
        if (!$hasRuleFields && array_key_exists('recurrence_data', $in)) {
            return ['recurrence' => $rec, 'recurrence_data' => $in['recurrence_data']];
        }
        $str = static fn(string $k): string => is_scalar($in[$k] ?? null) ? trim((string) $in[$k]) : '';
        $data = [];
        if (in_array($rec, ['daily', 'weekly', 'monthly', 'yearly', 'nth_weekday'], true) && $str('rule_interval') !== '') {
            $data['interval'] = $str('rule_interval');
        }
        switch ($rec) {
            case 'weekly':
                $days = $in['rule_weekdays'] ?? [];
                if (is_array($days)) {
                    $days = array_values(array_unique(array_filter(array_map(static fn($d) => is_scalar($d) ? trim((string) $d) : '', $days), static fn(string $d): bool => $d !== '')));
                    if ($days !== []) {
                        $data['weekdays'] = $days;
                    }
                }
                break;
            case 'monthly':
                $mode = $str('rule_monthly_mode');
                if ($mode !== '' && $mode !== 'day') {
                    $data['mode'] = $mode;
                }
                if ($mode === 'nth_weekday') {
                    foreach (['nth' => 'rule_nth', 'weekday' => 'rule_weekday'] as $key => $field) {
                        if ($str($field) !== '') {
                            $data[$key] = $str($field);
                        }
                    }
                }
                break;
            case 'nth_weekday':
                foreach (['month' => 'rule_month', 'weekday' => 'rule_weekday', 'nth' => 'rule_nth'] as $key => $field) {
                    if ($str($field) !== '') {
                        $data[$key] = $str($field);
                    }
                }
                break;
            case 'easter':
                $preset = $str('rule_easter');
                $offset = $preset === 'custom' ? $str('rule_easter_custom') : $preset;
                if ($offset !== '') {
                    $data['easter_offset'] = $offset;
                }
                break;
            case 'islamic':
                if ($str('rule_islamic') !== '') {
                    $data['islamic'] = $str('rule_islamic');
                }
                break;
        }
        return ['recurrence' => $rec, 'recurrence_data' => $data];
    }

    // ------------------------------------------------------------------ quick add, delete, duplicate, toggle, bulk

    /** POST /admin/calendar/quick-add - title, date, (date_end, all_day, start_time, end_time), type, location, repeat, until, public. */
    public function quickAdd(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $post = array_filter($request->post() ?: [], 'is_scalar');
        $back = CivilDate::validMonth((string) ($post['return_month'] ?? '')) ? 'admin/calendar?month=' . $post['return_month'] : 'admin/calendar';
        $res = CalendarWriter::quickAdd($post, $this->userId());
        if (!$res['ok']) {
            return $this->failed($this->quickErrors($res['errors'] ?? []), $back);
        }
        $e = $res['entry'] ?? [];
        $month = substr((string) ($e['start_date'] ?? CivilDate::today()), 0, 7);
        return $this->done($res, '"' . (string) ($e['title'] ?? 'The date') . '" was added to the calendar.', 'admin/calendar?month=' . $month . '&day=' . (string) ($e['start_date'] ?? '') . '#day-panel');
    }

    /** The quick form names two fields differently from the writer's entry fields: show the messages next to the right inputs. */
    private function quickErrors(array $errors): array
    {
        $map = ['start_date' => 'date', 'end_date' => 'date_end', 'repeat_until' => 'until', 'type_id' => 'type', 'recurrence' => 'repeat', 'recurrence_data' => 'repeat'];
        $out = [];
        foreach ($errors as $k => $m) {
            $out[$map[$k] ?? $k] = $m;
        }
        return $out;
    }

    /** POST /admin/calendar/{id}/delete - system entries cannot be deleted (hide them). */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('calendar.manage');
        $e = CalendarEntry::find($id);
        $res = CalendarWriter::delete($id);
        if (!$res['ok']) {
            return $this->failed(['_' => $res['error'] ?? 'Could not delete.'], 'admin/calendar/' . $id . '/edit');
        }
        return $this->done($res, '"' . (string) ($e['title'] ?? 'The entry') . '" was deleted.', 'admin/calendar?view=list');
    }

    /** POST /admin/calendar/{id}/duplicate */
    public function duplicate(Request $request, int $id): Response
    {
        $this->authorize('calendar.manage');
        $res = CalendarWriter::duplicate($id, $this->userId());
        if (!$res['ok']) {
            return $this->failed(['_' => $res['error'] ?? 'Could not copy.'], 'admin/calendar');
        }
        return $this->done($res, 'A copy was created. Change its title and dates, then save.', 'admin/calendar/' . (int) $res['id'] . '/edit');
    }

    /** POST /admin/calendar/{id}/toggle - show / hide (this is how a seeded holiday is hidden). */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('calendar.manage');
        $e = CalendarEntry::findOrFail($id);
        $on = (int) $e['is_active'] !== 1;
        $res = CalendarWriter::bulk($on ? 'activate' : 'deactivate', [$id], $this->userId());
        if ($this->request->expectsJson()) {
            return $this->json(['ok' => true, 'active' => $on] + array_intersect_key($res, ['changed' => 1]));
        }
        Flash::success($on ? '"' . $e['title'] . '" is shown on the calendar again.' : '"' . $e['title'] . '" is hidden from the calendar. You can show it again at any time.');
        return $this->back('admin/calendar');
    }

    /** POST /admin/calendar/bulk - action = activate|deactivate|delete, ids[]. Delete needs calendar.delete. */
    public function bulk(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $action = $request->string('action');
        if ($action === 'delete') {
            $this->authorize('calendar.delete');
        }
        $ids = $request->post('ids', []);
        $res = CalendarWriter::bulk($action, is_array($ids) ? $ids : [], $this->userId());
        if (!$res['ok']) {
            if ($this->request->expectsJson()) {
                return $this->json(['ok' => false, 'errors' => ['_' => 'Choose at least one entry and an action.']], 422);
            }
            Flash::error('Tick at least one entry and choose what to do with it.');
            return $this->back('admin/calendar');
        }
        $n = (int) $res['changed'];
        $what = $n . ' ' . ($n === 1 ? 'entry' : 'entries');
        $msg = match ($action) {
            'activate' => $what . ' will show on the calendar.',
            'deactivate' => $what . ' hidden from the calendar.',
            default => $what . ' deleted.',
        };
        if ($action === 'delete' && $res['skipped'] > 0) {
            $msg .= ' The holidays and days that came with the site cannot be deleted, so they were kept (hide them instead).';
        }
        if ($this->request->expectsJson()) {
            return $this->json(['ok' => true] + array_intersect_key($res, array_flip(['changed', 'skipped'])));
        }
        Flash::success($msg);
        return $this->back('admin/calendar');
    }

    // ------------------------------------------------------------------ one occurrence

    /**
     * POST /admin/calendar/{id}/occurrence - occurrence_date* (the day the RULE produces), action* = move|cancel|confirm|restore|reset,
     * new_date, new_start_time, new_end_time, note. "Confirm date" of Eid al-Fitr / Eid al-Adha = action confirm (+ new_date if the announced
     * day differs from the predicted one). return = holidays | edit (default).
     */
    public function occurrence(Request $request, int $id): Response
    {
        $this->authorize('calendar.manage');
        CalendarEntry::findOrFail($id);
        $post = array_filter($request->post() ?: [], 'is_scalar');       // an array value (action[]=) is dropped, never cast to a string
        $toHolidays =(string) ($post['return'] ?? '') === 'holidays';
        $redirect = $toHolidays ? 'admin/calendar/holidays' : 'admin/calendar/' . $id . '/edit';
        $action = (string) ($post['action'] ?? '');
        if ($action === 'move' && ($post['confirmed'] ?? '') === '' && $toHolidays) {
            $post['confirmed'] = '1';                        // a date the government announced on the holidays tab is a confirmed date
        }
        $res = CalendarWriter::saveOverride($id, (string) ($post['occurrence_date'] ?? ''), $post, $this->userId());
        if (!$res['ok']) {
            return $this->failed($res['errors'], $redirect);
        }
        $msg = match ($action) {
            'confirm' => 'The date is confirmed. Visitors no longer see "Date to be confirmed" for it.',
            'move' => 'That date was moved.',
            'cancel' => 'That date was cancelled. Visitors will not see it.',
            'restore' => 'That date is back on the calendar.',
            'reset' => 'That date is back to its usual rule.',
            default => 'The date was updated.',
        };
        return $this->done($res, $msg, $redirect . ($toHolidays ? '#upcoming' : '#dates'));
    }

    /** POST /admin/calendar/{id}/occurrence/{oid}/delete - back to the rule for that occurrence (the override must belong to the entry). */
    public function occurrenceDelete(Request $request, int $id, int|string $oid): Response
    {
        $this->authorize('calendar.manage');
        CalendarEntry::findOrFail($id);
        $ok = CalendarWriter::deleteOverride($id, (int) $oid);          // the router only casts {id}; {oid} arrives as a string
        if (!$ok) {
            return $this->failed(['_' => 'That change no longer exists.'], 'admin/calendar/' . $id . '/edit');
        }
        return $this->done(['ok' => true], 'The change was removed. That date follows the usual rule again.', 'admin/calendar/' . $id . '/edit#dates');
    }

    // ------------------------------------------------------------------ settings panel (settings.manage)

    /** GET /admin/calendar/settings */
    public function settings(Request $request): Response
    {
        $this->authorize('settings.manage');
        return $this->render('admin/calendar/settings', [
            'weekStart' => (string) setting('calendar_week_start', 'monday'), 'showOnContact' => (string) setting('calendar_show_on_contact', '1') !== '0',
            'selected' => CalendarService::defaultTypeSlugs(), 'types' => CalendarService::publicTypes(), 'available' => CalendarService::available(),
            'weekOptions' => (array) (Settings::definition('calendar_week_start')['options'] ?? ['monday' => 'Monday', 'sunday' => 'Sunday', 'saturday' => 'Saturday']),
        ], ['title' => 'Calendar settings', 'nav' => 'calendar', 'breadcrumbs' => [['Calendar & schedule', url('admin/calendar')], ['Settings', null]]]);
    }

    /** POST /admin/calendar/settings - week start, contact block, default types (stored like Admin > Settings: the default removes the override). */
    public function saveSettings(Request $request): Response
    {
        $this->authorize('settings.manage');
        $post = $request->post() ?: [];
        $errors = [];
        $week = strtolower(trim(is_scalar($post['calendar_week_start'] ?? null) ? (string) $post['calendar_week_start'] : 'monday'));
        if (!in_array($week, ['monday', 'sunday', 'saturday'], true)) {
            $errors['calendar_week_start'] = 'Choose Monday, Sunday or Saturday.';
        }
        $public = array_column(CalendarService::publicTypes(), 'slug');
        $picked = $post['calendar_types'] ?? [];
        $picked = is_array($picked) ? array_values(array_intersect($public, array_map(static fn($s): string => is_scalar($s) ? (string) $s : '', $picked))) : [];
        if ($picked === []) {
            $errors['calendar_types'] = 'Tick at least one type.';
        }
        if ($errors !== []) {
            return $this->failed($errors, 'admin/calendar/settings');
        }
        $values = [
            'calendar_week_start' => $week,
            'calendar_show_on_contact' => ($post['calendar_show_on_contact'] ?? '0') === '1' ? '1' : '0',
            'calendar_default_types' => array_diff($public, $picked) === [] ? '' : implode(',', $picked),
        ];
        $save = [];
        $changed = [];
        foreach ($values as $key => $value) {
            $default = (string) (Settings::definition($key)['default'] ?? '');
            $current = (string) Settings::get($key, $default);
            if ($value === $current) {
                continue;
            }
            $changed[] = $key;
            if ($value === $default) {
                Settings::reset($key);
            } else {
                $save[$key] = $value;
            }
        }
        if ($save !== []) {
            Settings::setMany($save);
        }
        Settings::flush();
        CalendarService::flush();
        AuditLog::record('calendar.settings', 'settings', null, $changed === [] ? 'Saved calendar settings (no changes)' : 'Updated calendar settings: ' . implode(', ', $changed), ['keys' => $changed]);
        return $this->done(['ok' => true], $changed === [] ? 'Nothing changed.' : 'The calendar settings were saved.', 'admin/calendar/settings');
    }

    // ------------------------------------------------------------------ helpers

    /** @param array<string,string> $errors */
    private function failed(array $errors, string $redirect): Response
    {
        if ($this->request->expectsJson()) {
            return $this->json(['ok' => false, 'errors' => $errors], 422);
        }
        $this->fail($errors === [] ? ['_' => 'Please check the form.'] : $errors, $redirect);
    }

    private function done(array $res, string $message, string $redirect): Response
    {
        if ($this->request->expectsJson()) {
            return $this->json(['ok' => true] + array_intersect_key($res, array_flip(['id', 'changed'])));
        }
        Flash::success($message);
        return $this->redirect($redirect);
    }
}
