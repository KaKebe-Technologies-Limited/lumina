<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\AuditLog;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CalendarWriter;

/**
 * Admin > Calendar > Import / export (CSV). Contract: docs/CALENDAR.md. OWNER: ADMIN agent.
 *
 * EXPORT (formula-safe through CsvExport) and the TEMPLATE come from the architect. The IMPORT is a two-step flow:
 *   1. "Check the file" (dry_run=1): the file is read and every row is validated EXACTLY as the entry form validates it
 *      (CalendarWriter::validateEntry through inputFromCsv); nothing is saved. The page lists each row as ready / problem / already there.
 *   2. "Import" (commit=1): the checked text travels back in a hidden field (base64; nothing is stored on the server in between), is validated
 *      AGAIN row by row and only the valid rows are saved through CalendarWriter::saveEntry, in one transaction, so audit entries and
 *      sanitising are identical to the forms. Nothing posted in step 1 is trusted in step 2.
 * Limits: 500 rows, 1 MB, UTF-8 (a Windows-1252 file from Excel is converted), comma / semicolon / tab separated. Import creates NON-system entries
 * with simple repeats only (none, daily, weekly, monthly, yearly): allow_advanced is always false. Descriptions are plain text.
 */
final class CalendarTransferController extends AdminController
{
    public const MAX_ROWS = 500;
    public const MAX_BYTES = 1048576;
    public const MAX_CELL = 6000;

    /** Friendly names of the columns, used in the error report. */
    private const LABELS = [
        'title' => 'Title', 'type' => 'Type', 'start_date' => 'Start date', 'end_date' => 'End date', 'all_day' => 'All day', 'start_time' => 'Start time',
        'end_time' => 'End time', 'location' => 'Location', 'repeat' => 'Repeat', 'repeat_until' => 'Repeat until', 'public' => 'Public', 'description' => 'Description',
        'link_url' => 'Link', 'source_label' => 'Source', 'source_url' => 'Source link',
    ];
    /** writer field => CSV column */
    private const FIELD_TO_COLUMN = [
        'title' => 'title', 'type_id' => 'type', 'start_date' => 'start_date', 'end_date' => 'end_date', 'start_time' => 'start_time', 'end_time' => 'end_time',
        'location' => 'location', 'recurrence' => 'repeat', 'recurrence_data' => 'repeat', 'repeat_until' => 'repeat_until', 'link_url' => 'link_url',
        'description' => 'description', 'source_label' => 'source_label', 'source_url' => 'source_url', 'color' => 'title', 'slug' => 'title',
    ];
    /** other spellings of the column names (after lower-casing and replacing every run of other characters by _) */
    private const COLUMN_ALIASES = [
        'name' => 'title', 'event' => 'title', 'date' => 'start_date', 'start' => 'start_date', 'startdate' => 'start_date', 'from' => 'start_date', 'begin' => 'start_date',
        'enddate' => 'end_date', 'end' => 'end_date', 'to' => 'end_date', 'allday' => 'all_day', 'starttime' => 'start_time', 'endtime' => 'end_time',
        'category' => 'type', 'place' => 'location', 'venue' => 'location', 'repeats' => 'repeat', 'recurrence' => 'repeat', 'frequency' => 'repeat',
        'until' => 'repeat_until', 'repeatuntil' => 'repeat_until', 'is_public' => 'public', 'visible' => 'public', 'notes' => 'description', 'details' => 'description',
        'link' => 'link_url', 'url' => 'link_url', 'linkurl' => 'link_url', 'source' => 'source_label', 'sourcelabel' => 'source_label', 'sourceurl' => 'source_url',
    ];
    private const REPEAT_ALIASES = [
        '' => 'none', 'no' => 'none', 'none' => 'none', 'never' => 'none', 'once' => 'none', 'does not repeat' => 'none', 'do not repeat' => 'none', 'one-off' => 'none',
        'daily' => 'daily', 'every day' => 'daily', 'day' => 'daily', 'weekly' => 'weekly', 'every week' => 'weekly', 'week' => 'weekly',
        'monthly' => 'monthly', 'every month' => 'monthly', 'month' => 'monthly', 'yearly' => 'yearly', 'annually' => 'yearly', 'annual' => 'yearly',
        'every year' => 'yearly', 'year' => 'yearly',
    ];

    /** GET /admin/calendar/import - the page: upload form, instructions, template and export links. */
    public function form(Request $request): Response
    {
        $this->authorize('calendar.manage');
        return $this->page(null);
    }

    /** POST /admin/calendar/import - dry_run=1 checks the file and shows what would happen, commit=1 (payload from the check) saves. */
    public function run(Request $request): Response
    {
        $this->authorize('calendar.manage');
        if (!CalendarService::available()) {
            $this->abort(404);
        }
        $commit = $request->string('commit') === '1';
        $src = $this->source($request, $commit);
        if (isset($src['error'])) {
            return $this->problem($src['error']);
        }
        $parsed = $this->parse($src['text']);
        if (isset($parsed['error'])) {
            return $this->problem($parsed['error']);
        }
        $result = $this->check($parsed['rows']);
        $result['notes'] = $parsed['notes'];
        $result['converted'] = $src['converted'] ?? false;
        $result['filename'] = $src['name'] ?? '';

        if (!$commit) {
            if ($request->expectsJson()) {
                return $this->json(['ok' => true, 'dry_run' => true] + $this->summaryJson($result));
            }
            $result['payload'] = $result['summary']['valid'] > 0 ? base64_encode($src['text']) : '';
            return $this->page($result);
        }

        // ---- commit: only the rows that are valid NOW; a row that fails here is reported, never half-saved
        @set_time_limit(120);
        $userId = $this->userId();
        $created = 0;
        $failed = 0;
        Database::transaction(static function () use ($result, $userId, &$created, &$failed): void {
            foreach ($result['rows'] as $r) {
                if ($r['status'] !== 'ok') {
                    continue;
                }
                $res = CalendarWriter::saveEntry(null, $r['input'], $userId, ['allow_advanced' => false]);
                $res['ok'] ? $created++ : $failed++;
            }
        });
        $skipped = $result['summary']['total'] - $created;
        if ($created > 0) {
            AuditLog::record('calendar.import', 'calendar_entry', null, 'Imported ' . $created . ' calendar entries from a CSV file', ['rows' => $result['summary']['total'], 'created' => $created, 'skipped' => $skipped]);
        }
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'dry_run' => false, 'created' => $created, 'skipped' => $skipped, 'failed' => $failed]);
        }
        if ($created === 0) {
            Flash::warning('Nothing was imported: none of the rows was ready to import.');
            return $this->redirect('admin/calendar/import');
        }
        Flash::success($created . ($created === 1 ? ' date was' : ' dates were') . ' added to the calendar.' . ($skipped > 0 ? ' ' . $skipped . ($skipped === 1 ? ' row was' : ' rows were') . ' skipped (problem or already in the calendar).' : ''));
        return $this->redirect('admin/calendar?view=list&source=own&sort=updated');
    }

    /** GET /admin/calendar/export.csv - every entry (system ones included), plain-text descriptions. */
    public function export(Request $request): Response
    {
        $this->authorize('calendar.manage');
        if (!CalendarService::available()) {
            $this->abort(404);
        }
        $types = [];
        foreach (CalendarService::types() as $t) {
            $types[$t['id']] = $t;
        }
        $rows = (static function () use ($types): \Generator {
            foreach (Database::all('SELECT * FROM calendar_entries ORDER BY start_date ASC, id ASC LIMIT 5000') as $e) {
                $rec = CalendarWriter::csvRecord($e, $types);
                yield array_map(static fn(string $c) => $rec[$c] ?? '', CalendarWriter::CSV_COLUMNS);
            }
        })();
        AuditLog::record('calendar.export', 'calendar_entry', null, 'Exported the calendar as CSV');
        return CsvExport::response('lumina-calendar-' . date('Y-m-d') . '.csv', CalendarWriter::CSV_COLUMNS, $rows);
    }

    /** GET /admin/calendar/import-template.csv - the header row and two example rows (clearly examples). */
    public function template(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $rows = [
            ['Example: volunteer orientation', 'meeting', '2030-01-15', '', 'no', '10:00', '12:00', 'Gulu City', 'none', '', 'yes', 'Replace this row with your own dates.', '', '', ''],
            ['Example: monthly planning call', 'meeting', '2030-01-05', '', 'no', '15:00', '16:00', '', 'monthly', '2030-12-31', 'no', '', '', '', ''],
        ];
        return CsvExport::response('lumina-calendar-import-template.csv', CalendarWriter::CSV_COLUMNS, $rows);
    }

    // ------------------------------------------------------------------ reading the file

    /** @param array<string,mixed>|null $result */
    private function page(?array $result): Response
    {
        $own = (int) Database::value('SELECT COUNT(*) FROM calendar_entries WHERE is_system = 0', [], 0);
        $all = (int) Database::value('SELECT COUNT(*) FROM calendar_entries', [], 0);
        return $this->render('admin/calendar/import', [
            'available' => CalendarService::available(), 'columns' => CalendarWriter::CSV_COLUMNS, 'labels' => self::LABELS, 'result' => $result,
            'maxRows' => self::MAX_ROWS, 'maxBytes' => self::MAX_BYTES, 'types' => $this->importTypes(), 'ownCount' => $own, 'allCount' => $all,
        ], ['title' => 'Import / export', 'nav' => 'calendar_import', 'breadcrumbs' => [['Calendar & schedule', url('admin/calendar')], ['Import / export', null]]]);
    }

    private function problem(string $message): Response
    {
        if ($this->request->expectsJson()) {
            return $this->json(['ok' => false, 'errors' => ['csv' => $message]], 422);
        }
        Flash::error($message);
        return $this->redirect('admin/calendar/import');
    }

    /**
     * The CSV text: the uploaded file (check step) or the payload the check step handed back (import step).
     * @return array{text:string,name?:string,converted?:bool}|array{error:string}
     */
    private function source(Request $request, bool $commit): array
    {
        if ($commit && is_string($request->post('payload')) && $request->post('payload') !== '') {
            $raw = base64_decode((string) $request->post('payload'), true);
            if ($raw === false || strlen($raw) > self::MAX_BYTES) {
                return ['error' => 'The checked file could not be read back. Please choose the file and check it again.'];
            }
            return $this->normaliseText($raw);
        }
        $f = $request->file('csv');
        if ($f === null) {
            return ['error' => 'Choose a CSV file first.'];
        }
        $code = (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'That file is too large. The limit is ' . human_filesize(self::MAX_BYTES) . '.'];
        }
        if ($code !== UPLOAD_ERR_OK || !is_string($f['tmp_name'] ?? null) || !is_uploaded_file($f['tmp_name'])) {
            return ['error' => 'The file could not be uploaded. Please try again.'];
        }
        $name = (string) ($f['name'] ?? '');
        if (!preg_match('/\.(csv|txt)$/i', $name)) {
            return ['error' => 'Upload a .csv file. In Excel or Google Sheets use File > Download / Save as > CSV.'];
        }
        if ((int) ($f['size'] ?? 0) > self::MAX_BYTES) {
            return ['error' => 'That file is too large. The limit is ' . human_filesize(self::MAX_BYTES) . '.'];
        }
        $raw = file_get_contents($f['tmp_name'], false, null, 0, self::MAX_BYTES + 1);
        if ($raw === false || strlen($raw) > self::MAX_BYTES) {
            return ['error' => 'That file is too large. The limit is ' . human_filesize(self::MAX_BYTES) . '.'];
        }
        $out = $this->normaliseText($raw);
        if (!isset($out['error'])) {
            $out['name'] = mb_substr(preg_replace('/[^\p{L}\p{N}._ -]+/u', '', $name) ?? '', 0, 80);
        }
        return $out;
    }

    /** @return array{text:string,converted:bool}|array{error:string} UTF-8 text without a BOM and with Unix line breaks */
    private function normaliseText(string $raw): array
    {
        if (str_contains($raw, "\0")) {
            return ['error' => 'That does not look like a CSV text file. Save the spreadsheet as "CSV (comma delimited)" and upload that.'];
        }
        $raw = (string) preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $converted = false;
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');       // Excel on Windows "CSV" without UTF-8
            $converted = true;
        }
        return ['text' => str_replace(["\r\n", "\r"], "\n", $raw), 'converted' => $converted];
    }

    /**
     * Header + rows. Blank rows are skipped but still counted, so "row 7" is row 7 of the spreadsheet.
     * @return array{rows:list<array{n:int,rec:array<string,string>}>,notes:list<string>}|array{error:string}
     */
    private function parse(string $text): array
    {
        $first = (string) strtok($text, "\n");
        $delim = ',';
        $best = substr_count($first, ',');
        foreach ([';' => ';', "\t" => "\t"] as $d) {
            if (substr_count($first, $d) > $best) {
                $best = substr_count($first, $d);
                $delim = $d;
            }
        }
        $h = fopen('php://temp', 'r+');
        if ($h === false) {
            return ['error' => 'The file could not be read.'];
        }
        fwrite($h, $text);
        rewind($h);
        $head = fgetcsv($h, 0, $delim, '"', '');
        if (!is_array($head) || $head === [null]) {
            fclose($h);
            return ['error' => 'The file is empty. The first row must hold the column names (see the template).'];
        }
        $cols = [];
        $unknown = [];
        foreach ($head as $i => $c) {
            $key = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '_', (string) $c), '_'));
            $key = self::COLUMN_ALIASES[$key] ?? self::COLUMN_ALIASES[str_replace('_', '', $key)] ?? $key;
            if (in_array($key, CalendarWriter::CSV_COLUMNS, true) && !in_array($key, $cols, true)) {
                $cols[$i] = $key;
            } elseif (trim((string) $c) !== '') {
                $unknown[] = mb_substr(trim((string) $c), 0, 40);
            }
        }
        if (!in_array('title', $cols, true) || !in_array('start_date', $cols, true)) {
            fclose($h);
            return ['error' => 'The first row must be a header row with at least the columns "title" and "start_date". Download the template to see the layout.'];
        }
        $rows = [];
        $n = 1;
        while (($cells = fgetcsv($h, 0, $delim, '"', '')) !== false) {
            $n++;
            if ($cells === [null] || trim(implode('', array_map(static fn($c): string => (string) $c, $cells))) === '') {
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                fclose($h);
                return ['error' => 'The file has more than ' . self::MAX_ROWS . ' rows. Split it into smaller files and import them one by one.'];
            }
            $rec = array_fill_keys(CalendarWriter::CSV_COLUMNS, '');
            foreach ($cols as $i => $key) {
                $rec[$key] = $this->cell($cells[$i] ?? '');
            }
            $rows[] = ['n' => $n, 'rec' => $rec];
        }
        fclose($h);
        $notes = [];
        if ($unknown !== []) {
            $notes[] = 'These columns are not used and were ignored: ' . implode(', ', array_slice($unknown, 0, 8)) . '.';
        }
        if ($rows === []) {
            return ['error' => 'The file has a header row but no dates. Add one row per date under the header.'];
        }
        return ['rows' => $rows, 'notes' => $notes];
    }

    /** One cell: control characters out, the apostrophe our own export puts in front of "=", "+", "-", "@" removed, capped. */
    private function cell(mixed $v): string
    {
        $s = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', (string) $v);
        $s = trim($s);
        if (preg_match("/^'[=+\\-@]/", $s) === 1) {
            $s = substr($s, 1);
        }
        return mb_substr($s, 0, self::MAX_CELL);
    }

    // ------------------------------------------------------------------ checking the rows

    /** @return list<array<string,mixed>> active, non-event types an imported row may use */
    private function importTypes(): array
    {
        return array_values(array_filter(CalendarService::types(), static fn(array $t): bool => $t['is_active'] && $t['slug'] !== CalendarService::SLUG_EVENT));
    }

    /**
     * Validate every row with the entry form's own rules. A row is "ok" (would be saved), "error" (the messages say what to fix) or
     * "duplicate" (a dated entry with the same title, day and repeat is already in the calendar or earlier in this file).
     * @param list<array{n:int,rec:array<string,string>}> $rows
     * @return array{rows:list<array<string,mixed>>,summary:array{total:int,valid:int,errors:int,duplicates:int}}
     */
    private function check(array $rows): array
    {
        $types = $this->importTypes();
        $bySlug = [];
        $byName = [];
        foreach ($types as $t) {
            $bySlug[$t['slug']] = $t;
            $byName[mb_strtolower((string) $t['name'])] = $t;
        }
        $seen = [];
        foreach (Database::all('SELECT title, start_date, recurrence FROM calendar_entries LIMIT 20000') as $e) {
            $seen[mb_strtolower((string) $e['title']) . '|' . $e['start_date'] . '|' . $e['recurrence']] = true;
        }
        $out = [];
        $sum = ['total' => 0, 'valid' => 0, 'errors' => 0, 'duplicates' => 0];
        foreach ($rows as $r) {
            $rec = $r['rec'];
            $messages = [];
            $repeat = mb_strtolower($rec['repeat']);
            $rec['repeat'] = self::REPEAT_ALIASES[$repeat] ?? $repeat;
            $typeKey = mb_strtolower($rec['type']);
            $type = $bySlug[$typeKey] ?? $byName[$typeKey] ?? null;
            if ($type === null) {
                $messages['type'] = $rec['type'] === ''
                    ? 'Choose a type. Use one of: ' . implode(', ', array_column($types, 'slug')) . '.'
                    : '"' . mb_substr($rec['type'], 0, 40) . '" is not a type. Use one of: ' . implode(', ', array_column($types, 'slug')) . '.';
            }
            $rec['type'] = $type['slug'] ?? '';
            if (!in_array($rec['repeat'], CalendarWriter::SIMPLE_REPEAT, true)) {
                $messages['repeat'] = in_array($rec['repeat'], CalendarWriter::ADVANCED, true)
                    ? 'This kind of repeat can only be set up in the editor by an administrator. Use none, daily, weekly, monthly or yearly here.'
                    : 'Repeat must be none, daily, weekly, monthly or yearly.';
                $rec['repeat'] = 'none';
            }
            $input = CalendarWriter::inputFromCsv($rec);
            $v = CalendarWriter::validateEntry($input, null, ['allow_advanced' => false]);
            foreach ($v['errors'] as $field => $msg) {
                $col = self::FIELD_TO_COLUMN[$field] ?? 'title';
                $messages[$col] ??= $msg;
            }
            $key = mb_strtolower($rec['title']) . '|' . $v['row']['start_date'] . '|' . $v['row']['recurrence'];
            $status = 'ok';
            if ($messages !== []) {
                $status = 'error';
            } elseif (isset($seen[$key])) {
                $status = 'duplicate';
            }
            if ($status === 'ok') {
                $seen[$key] = true;
            }
            $sum['total']++;
            $sum[$status === 'ok' ? 'valid' : ($status === 'error' ? 'errors' : 'duplicates')]++;
            $out[] = [
                'n' => $r['n'], 'status' => $status, 'title' => $rec['title'], 'type_name' => $type['name'] ?? '', 'start_date' => $rec['start_date'], 'end_date' => $rec['end_date'],
                'time' => $rec['start_time'] . ($rec['end_time'] !== '' ? ' to ' . $rec['end_time'] : ''), 'repeat' => $rec['repeat'], 'messages' => $messages,
                'input' => $status === 'ok' ? $input : [],
            ];
        }
        return ['rows' => $out, 'summary' => $sum];
    }

    /** @param array<string,mixed> $result */
    private function summaryJson(array $result): array
    {
        return [
            'summary' => $result['summary'],
            'rows' => array_map(static fn(array $r): array => ['row' => $r['n'], 'status' => $r['status'], 'title' => $r['title'], 'messages' => $r['messages']], $result['rows']),
        ];
    }
}
