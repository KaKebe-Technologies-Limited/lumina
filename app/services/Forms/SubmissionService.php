<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\Config;
use App\Core\Database;
use App\Core\Pagination;
use App\Models\FormSubmission;

/**
 * SUBMISSIONS: storing (with a SNAPSHOT of every answer), reading, searching, status workflow, notes, deleting. Contract: docs/FORMS.md sections 7 and 8.
 * All of it is PERSONAL DATA: only call the read/write methods from code that already checked `forms.submissions` (admin) or from the public submit
 * handler (store()). Nothing here writes to the audit log or to the application log (ids and counts only are logged by the controllers).
 *
 * SNAPSHOT: `form_submissions.answers` is a JSON list with one entry per VISIBLE input field, in form order:
 *   {"id":"f8k2m9xa","key":"full_name","type":"text","label":"Full name","page":"About you","value":"Jane","text":"Jane"}
 *   select/radio : value = option value (or "__other"), text = option LABEL at that time (or "Other: free text"), "other" = the free text
 *   checkboxes   : value = list of option values, text = labels joined by ", "
 *   yes_no       : value "yes"/"no", text "Yes"/"No"      rating : value "4", text "4 / 5"
 *   consent      : value "yes", text "Yes", "at" = when it was ticked (EAT)
 *   file         : value = [{"name":"cv.pdf","size":12345}], text = names (the files themselves are in form_files, matched by field_id)
 * so a submission stays readable after the form is edited or its fields are deleted.
 */
final class SubmissionService
{
    public const SORTS = ['created_at', 'id', 'status', 'submitter_email'];

    /** @var array<string,int> */
    private static array $countCache = [];

    // ------------------------------------------------------------------ snapshot

    /**
     * @param array<string,mixed> $definition normalised definition
     * @param array<string,mixed> $result     FormValidator::validate() result (ok)
     * @param array<string,list<array{name:string,size:int}>> $storedFiles fieldId => stored files ([] before the files are stored: names come from values)
     * @return list<array<string,mixed>>
     */
    public static function buildSnapshot(array $definition, array $result, array $storedFiles = []): array
    {
        $entries = [];
        $values = (array) ($result['values'] ?? []);
        $others = (array) ($result['others'] ?? []);
        foreach ((array) $definition['pages'] as $page) {
            foreach ((array) $page['fields'] as $f) {
                $id = (string) $f['id'];
                $type = (string) $f['type'];
                if (!FormDefinition::isInput($type) || empty($result['visible'][$id]) || !array_key_exists($id, $values)) {
                    continue;
                }
                $v = $values[$id];
                $entry = [
                    'id'    => $id,
                    'key'   => (string) $f['key'],
                    'type'  => $type,
                    'label' => (string) $f['label'],
                    'page'  => (string) $page['title'],
                    'value' => $v,
                    'text'  => '',
                ];
                $labelOf = static function (string $value) use ($f): string {
                    foreach ((array) ($f['options'] ?? []) as $o) {
                        if ((string) $o['value'] === $value) {
                            return (string) $o['label'];
                        }
                    }
                    return $value;
                };
                $otherLabel = (string) (($f['other_label'] ?? '') !== '' ? $f['other_label'] : FormText::get('ui.other_short'));
                switch ($type) {
                    case 'select':
                    case 'radio':
                        $s = (string) $v;
                        if ($s === FormDefinition::OTHER) {
                            $entry['other'] = (string) ($others[$id] ?? '');
                            $entry['text'] = $otherLabel . ': ' . $entry['other'];
                        } else {
                            $entry['text'] = $s === '' ? '' : $labelOf($s);
                        }
                        break;
                    case 'checkboxes':
                        $parts = [];
                        foreach ((array) $v as $x) {
                            if ((string) $x === FormDefinition::OTHER) {
                                $entry['other'] = (string) ($others[$id] ?? '');
                                $parts[] = $otherLabel . ': ' . $entry['other'];
                            } else {
                                $parts[] = $labelOf((string) $x);
                            }
                        }
                        $entry['text'] = implode(', ', $parts);
                        break;
                    case 'yes_no':
                        $entry['text'] = $v === 'yes' ? FormText::get('ui.yes') : ($v === 'no' ? FormText::get('ui.no') : '');
                        break;
                    case 'rating':
                        $entry['text'] = (string) $v === '' ? '' : $v . ' / 5';
                        break;
                    case 'consent':
                        $entry['text'] = $v === 'yes' ? FormText::get('ui.yes') : '';
                        if ($v === 'yes' && !empty($result['consent_at'])) {
                            $entry['at'] = (string) $result['consent_at'];
                        }
                        break;
                    case 'file':
                        $list = [];
                        foreach ((array) ($storedFiles[$id] ?? []) as $sf) {
                            $list[] = ['name' => (string) $sf['name'], 'size' => (int) $sf['size']];
                        }
                        if ($list === []) {
                            foreach ((array) $v as $n) {
                                $list[] = ['name' => (string) $n, 'size' => 0];
                            }
                        }
                        $entry['value'] = $list;
                        $entry['text'] = implode(', ', array_column($list, 'name'));
                        break;
                    default:
                        $entry['text'] = (string) $v;
                }
                $entries[] = $entry;
            }
        }
        return $entries;
    }

    /** Plain text of all answers (admin search). @param list<array<string,mixed>> $snapshot */
    public static function searchText(array $snapshot, ?string $contextLabel = null): string
    {
        $parts = [];
        foreach ($snapshot as $e) {
            if (($e['text'] ?? '') !== '') {
                $parts[] = (string) $e['text'];
            }
        }
        if ($contextLabel !== null && $contextLabel !== '') {
            $parts[] = $contextLabel;
        }
        return mb_substr(implode("\n", $parts), 0, 20000);
    }

    /** The address the submitter typed: the e-mail field chosen for the confirmation, else the first visible e-mail field with a value. */
    public static function submitterEmail(array $form, array $result): ?string
    {
        $def = (array) $form['def'];
        $map = FormDefinition::fieldMap($def);
        $chosen = (string) ($form['cfg']['confirmation']['email_field'] ?? '');
        $candidates = [];
        if ($chosen !== '' && ($map[$chosen]['type'] ?? '') === 'email') {
            $candidates[] = $chosen;
        }
        foreach (FormDefinition::inputFields($def) as $f) {
            if ($f['type'] === 'email') {
                $candidates[] = (string) $f['id'];
            }
        }
        foreach ($candidates as $id) {
            $v = $result['values'][$id] ?? '';
            if (empty($result['visible'][$id]) || !is_string($v) || $v === '') {
                continue;
            }
            return strtolower(substr($v, 0, 190));
        }
        return null;
    }

    /** Public reference shown to the submitter and used in e-mails: F<form id>-<submission id>. */
    public static function reference(int $formId, int $submissionId): string
    {
        return 'F' . $formId . '-' . $submissionId;
    }

    // ------------------------------------------------------------------ storing

    /**
     * Store a validated submission. Call AFTER FormValidator::validate() said ok and AFTER FormFileStore::storeAll() moved the files.
     * Everything is decided inside ONE transaction that holds a row lock on the form (SELECT ... FOR UPDATE), so two visitors can never both take the
     * last place of a form with a maximum, and the status / opening dates are the ones in force at that moment.
     *
     * @param array<string,mixed> $form        hydrated form (FormService::hydrate)
     * @param array<string,mixed> $result      FormValidator result (ok)
     * @param array<string,list<array{path:string,name:string,mime:string,size:int}>> $storedFiles fieldId => stored files
     * @param array{ip?:string,user_agent?:string,context?:?array{type:string,ref:string,label:string}} $meta
     * @return array{ok:bool,id?:int,reference?:string,error?:string,field?:?string,snapshot?:list<array<string,mixed>>,email?:?string}
     *   error: unavailable | not_open | closed | full | duplicate (field = the e-mail field id for `duplicate`)
     */
    public static function store(array $form, array $result, array $storedFiles, array $meta = []): array
    {
        $snapshot = self::buildSnapshot((array) $form['def'], $result, $storedFiles);
        $answersJson = (string) json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        $ctx = $meta['context'] ?? null;
        $email = self::submitterEmail($form, $result);
        $search = self::searchText($snapshot, $ctx['label'] ?? null);
        $emailFieldId = null;
        foreach (FormDefinition::inputFields((array) $form['def']) as $f) {
            if ($f['type'] === 'email' && ($result['values'][$f['id']] ?? '') === $email && $email !== null) {
                $emailFieldId = (string) $f['id'];
                break;
            }
        }
        $ip = (string) ($meta['ip'] ?? '');

        $out = Database::transaction(static function () use ($form, $result, $storedFiles, $meta, $ctx, $email, $emailFieldId, $answersJson, $search, $ip): array {
            $row = Database::one('SELECT * FROM forms WHERE id = ? FOR UPDATE', [(int) $form['id']]);
            if ($row === null) {
                return ['ok' => false, 'error' => 'unavailable'];
            }
            $live = FormService::hydrate($row);
            $taken = (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status <> 'spam'", [(int) $row['id']], 0);
            $avail = FormService::availability($live, null, $taken);
            if (!$avail['can_submit']) {
                return ['ok' => false, 'error' => $avail['state']];
            }
            if (!empty($live['cfg']['one_per_email']) && $email !== null) {
                $dup = (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND submitter_email = ? AND status <> 'spam'", [(int) $row['id'], $email], 0);
                if ($dup > 0) {
                    return ['ok' => false, 'error' => 'duplicate', 'field' => $emailFieldId];
                }
            }
            $id = Database::insert('form_submissions', [
                'form_id'         => (int) $row['id'],
                'form_version'    => (int) $row['version'],
                'answers'         => $answersJson,
                'search_text'     => $search,
                'submitter_email' => $email,
                'context_type'    => $ctx['type'] ?? null,
                'context_ref'     => isset($ctx['ref']) ? mb_substr((string) $ctx['ref'], 0, 190) : null,
                'context_label'   => isset($ctx['label']) ? mb_substr((string) $ctx['label'], 0, 220) : null,
                'status'          => 'new',
                'consent_at'      => $result['consent_at'] ?? null,
                'ip_hash'         => $ip !== '' ? hash_hmac('sha256', $ip, Config::appKey()) : null,
                'user_agent'      => mb_substr((string) ($meta['user_agent'] ?? ''), 0, 255) ?: null,
            ]);
            foreach ($storedFiles as $fieldId => $list) {
                foreach ($list as $sf) {
                    Database::insert('form_files', [
                        'submission_id' => $id,
                        'field_id'      => (string) $fieldId,
                        'original_name' => mb_substr((string) $sf['name'], 0, 255),
                        'stored_path'   => (string) $sf['path'],
                        'mime'          => (string) $sf['mime'],
                        'size'          => (int) $sf['size'],
                    ]);
                }
            }
            Database::execute('UPDATE forms SET submissions_count = submissions_count + 1 WHERE id = ?', [(int) $row['id']]);
            return ['ok' => true, 'id' => $id, 'reference' => self::reference((int) $row['id'], $id), 'email' => $email];
        });
        self::$countCache = [];
        if (!empty($out['ok'])) {
            $out['snapshot'] = $snapshot;
        }
        return $out;
    }

    // ------------------------------------------------------------------ reading

    /** Decode a raw row: adds `entries` (the snapshot list) and `files` (form_files rows), keeps every column. @return array<string,mixed> */
    public static function hydrate(array $row, bool $withFiles = true): array
    {
        $entries = json_decode((string) ($row['answers'] ?? '[]'), true);
        $row['entries'] = is_array($entries) ? array_values(array_filter($entries, 'is_array')) : [];
        unset($row['answers']);
        if ($withFiles) {
            $row['files'] = self::files((int) $row['id']);
        }
        return $row;
    }

    public static function find(int $id, bool $withFiles = true): ?array
    {
        $row = FormSubmission::find($id);
        return $row === null ? null : self::hydrate($row, $withFiles);
    }

    /** @return list<array<string,mixed>> form_files rows of one submission */
    public static function files(int $submissionId): array
    {
        return Database::all('SELECT * FROM form_files WHERE submission_id = ? ORDER BY id ASC', [$submissionId]);
    }

    /** One file of a submission of a form (all three ids must match: a file of another form is a 404 even with a valid id). */
    public static function file(int $formId, int $submissionId, int $fileId): ?array
    {
        return Database::one(
            'SELECT ff.* FROM form_files ff JOIN form_submissions s ON s.id = ff.submission_id WHERE ff.id = ? AND ff.submission_id = ? AND s.form_id = ? LIMIT 1',
            [$fileId, $submissionId, $formId]
        );
    }

    /**
     * Columns of the inbox table: the first five questions that are not file uploads (id, label, type). @return list<array{id:string,label:string,type:string}>
     */
    public static function listColumns(array $form, int $max = 5): array
    {
        $cols = [];
        foreach (FormDefinition::inputFields((array) $form['def']) as $f) {
            if ($f['type'] === 'file') {
                continue;
            }
            $cols[] = ['id' => (string) $f['id'], 'label' => (string) $f['label'], 'type' => (string) $f['type']];
            if (count($cols) >= $max) {
                break;
            }
        }
        return $cols;
    }

    /** The text of one answer of a hydrated submission ('' when the question was not shown / not answered). */
    public static function answerText(array $submission, string $fieldId): string
    {
        foreach ((array) ($submission['entries'] ?? []) as $e) {
            if (($e['id'] ?? '') === $fieldId) {
                return (string) ($e['text'] ?? '');
            }
        }
        return '';
    }

    /**
     * WHERE clause of the inbox filters. $filters: status (string) | statuses (list) | q (text, every word must occur in the answers, e-mail, context or notes) |
     * from / to (Y-m-d, EAT days, inclusive) | context_ref.  @return array{0:string,1:list<mixed>}
     */
    public static function where(int $formId, array $filters, string $alias = 's'): array
    {
        $w = ["{$alias}.form_id = ?"];
        $p = [$formId];
        $statuses = [];
        if (!empty($filters['statuses']) && is_array($filters['statuses'])) {
            $statuses = array_values(array_intersect(FormSubmission::STATUSES, array_map('strval', $filters['statuses'])));
        } elseif (!empty($filters['status']) && in_array($filters['status'], FormSubmission::STATUSES, true)) {
            $statuses = [(string) $filters['status']];
        }
        if ($statuses !== []) {
            $w[] = "{$alias}.status IN (" . implode(',', array_fill(0, count($statuses), '?')) . ')';
            array_push($p, ...$statuses);
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            foreach (array_slice(preg_split('/\s+/u', mb_substr($q, 0, 200)) ?: [], 0, 6) as $word) {
                $like = '%' . addcslashes($word, '\\%_') . '%';
                $w[] = "({$alias}.search_text LIKE ? OR {$alias}.submitter_email LIKE ? OR {$alias}.context_label LIKE ? OR {$alias}.notes LIKE ?)";
                array_push($p, $like, $like, $like, $like);
            }
        }
        foreach (['from' => '>=', 'to' => '<='] as $k => $op) {
            $d = (string) ($filters[$k] ?? '');
            $dt = $d !== '' ? \DateTimeImmutable::createFromFormat('!Y-m-d', $d) : false;
            if ($dt !== false && $dt->format('Y-m-d') === $d) {
                $w[] = $k === 'from' ? "{$alias}.created_at >= ?" : "{$alias}.created_at < ?";
                $p[] = $k === 'from' ? $d . ' 00:00:00' : $dt->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
            }
        }
        if (isset($filters['context_ref']) && $filters['context_ref'] !== '') {
            $w[] = "{$alias}.context_ref = ?";
            $p[] = (string) $filters['context_ref'];
        }
        return [implode(' AND ', $w), $p];
    }

    /**
     * Paginated inbox. $filters see where(); sort (SORTS) and dir (asc|desc, default desc). Items are hydrated WITHOUT files (entries decoded).
     */
    public static function listFor(int $formId, array $filters, int $page, int $perPage): Pagination
    {
        [$where, $params] = self::where($formId, $filters);
        $total = (int) Database::value("SELECT COUNT(*) FROM form_submissions s WHERE {$where}", $params, 0);
        $pg = Pagination::make($total, $page, $perPage);
        $sort = in_array($filters['sort'] ?? '', self::SORTS, true) ? (string) $filters['sort'] : 'created_at';
        $dir = strtolower((string) ($filters['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $order = $sort === 'created_at' ? "s.created_at {$dir}, s.id {$dir}" : "s.{$sort} {$dir}, s.id DESC";
        $rows = Database::all("SELECT s.* FROM form_submissions s WHERE {$where} ORDER BY {$order} LIMIT ? OFFSET ?", array_merge($params, [$pg->perPage, $pg->offset]));
        $pg->items = array_map(static fn(array $r): array => self::hydrate($r, false), $rows);
        return $pg;
    }

    /**
     * Every matching submission, oldest first, in chunks (CSV export). Yields hydrated rows WITH files.
     * @return \Generator<int,array<string,mixed>>
     */
    public static function each(int $formId, array $filters = [], int $chunk = 300): \Generator
    {
        [$where, $params] = self::where($formId, $filters);
        $last = 0;
        while (true) {
            $rows = Database::all("SELECT s.* FROM form_submissions s WHERE {$where} AND s.id > ? ORDER BY s.id ASC LIMIT ?", array_merge($params, [$last, $chunk]));
            if ($rows === []) {
                return;
            }
            foreach ($rows as $r) {
                $last = (int) $r['id'];
                yield self::hydrate($r, true);
            }
            if (count($rows) < $chunk) {
                return;
            }
        }
    }

    /**
     * The neighbours of a submission in the default inbox order (newest first): `prev` = the next NEWER one, `next` = the next OLDER one (ids or null),
     * under the same filters.  @return array{prev:?int,next:?int}
     */
    public static function neighbours(int $formId, int $id, array $filters = []): array
    {
        $cur = Database::one('SELECT created_at FROM form_submissions WHERE id = ? AND form_id = ?', [$id, $formId]);
        if ($cur === null) {
            return ['prev' => null, 'next' => null];
        }
        [$where, $params] = self::where($formId, $filters);
        $older = Database::value("SELECT s.id FROM form_submissions s WHERE {$where} AND (s.created_at < ? OR (s.created_at = ? AND s.id < ?)) ORDER BY s.created_at DESC, s.id DESC LIMIT 1", array_merge($params, [$cur['created_at'], $cur['created_at'], $id]));
        $newer = Database::value("SELECT s.id FROM form_submissions s WHERE {$where} AND (s.created_at > ? OR (s.created_at = ? AND s.id > ?)) ORDER BY s.created_at ASC, s.id ASC LIMIT 1", array_merge($params, [$cur['created_at'], $cur['created_at'], $id]));
        return ['prev' => $newer !== null ? (int) $newer : null, 'next' => $older !== null ? (int) $older : null];
    }

    /** Count per status for one form: ['new' => 3, 'read' => 1, ..., 'all' => 4]. @return array<string,int> */
    public static function statusCounts(int $formId): array
    {
        $out = array_fill_keys(FormSubmission::STATUSES, 0);
        $total = 0;
        foreach (Database::all('SELECT status, COUNT(*) AS n FROM form_submissions WHERE form_id = ? GROUP BY status', [$formId]) as $r) {
            $out[(string) $r['status']] = (int) $r['n'];
            $total += (int) $r['n'];
        }
        $out['all'] = $total;
        return $out;
    }

    /** New (unread) submissions: of one form, or of all forms. Cached per request. */
    public static function newCount(?int $formId = null): int
    {
        $key = $formId === null ? 'all' : 'f' . $formId;
        if (isset(self::$countCache[$key])) {
            return self::$countCache[$key];
        }
        try {
            $n = $formId === null
                ? (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE status = 'new'", [], 0)
                : (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status = 'new'", [$formId], 0);
        } catch (\Throwable) {
            $n = 0;                                                  // tables not imported yet
        }
        return self::$countCache[$key] = $n;
    }

    // ------------------------------------------------------------------ changing

    /** Change the status of one submission of a form. @return bool changed */
    public static function setStatus(int $formId, int $id, string $status): bool
    {
        if (!in_array($status, FormSubmission::STATUSES, true)) {
            return false;
        }
        $n = Database::execute('UPDATE form_submissions SET status = ? WHERE id = ? AND form_id = ? AND status <> ?', [$status, $id, $formId, $status]);
        self::$countCache = [];
        if ($n > 0) {
            self::recount($formId);                  // spam rows do not count against max_submissions: marking / un-marking spam moves the counter
        }
        return $n > 0;
    }

    /** First view of a NEW submission turns it into READ (never touches any other status). */
    public static function markRead(int $formId, int $id): void
    {
        Database::execute("UPDATE form_submissions SET status = 'read' WHERE id = ? AND form_id = ? AND status = 'new'", [$id, $formId]);
        self::$countCache = [];
    }

    /** @param list<int> $ids @return int rows changed */
    public static function bulkStatus(int $formId, array $ids, string $status): int
    {
        $ids = self::cleanIds($ids);
        if ($ids === [] || !in_array($status, FormSubmission::STATUSES, true)) {
            return 0;
        }
        $n = Database::execute(
            'UPDATE form_submissions SET status = ? WHERE form_id = ? AND status <> ? AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')',
            array_merge([$status, $formId, $status], $ids)
        );
        self::$countCache = [];
        if ($n > 0) {
            self::recount($formId);
        }
        return $n;
    }

    /** Replace the internal notes (plain text, at most 5000 characters). */
    public static function saveNotes(int $formId, int $id, string $notes): bool
    {
        $notes = FormDefinition::plain($notes, 5000, true);
        return Database::execute('UPDATE form_submissions SET notes = ? WHERE id = ? AND form_id = ?', [$notes !== '' ? $notes : null, $id, $formId]) >= 0;
    }

    /** Delete one submission and its files. @return bool deleted */
    public static function delete(int $formId, int $id): bool
    {
        return self::deleteMany($formId, [$id]) > 0;
    }

    /** @param list<int> $ids @return int submissions deleted */
    public static function deleteMany(int $formId, array $ids): int
    {
        $ids = self::cleanIds($ids);
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $mine = array_column(Database::all("SELECT id FROM form_submissions WHERE form_id = ? AND id IN ({$in})", array_merge([$formId], $ids)), 'id');
        if ($mine === []) {
            return 0;
        }
        $in2 = implode(',', array_fill(0, count($mine), '?'));
        $files = Database::all("SELECT stored_path FROM form_files WHERE submission_id IN ({$in2})", $mine);
        $n = Database::execute("DELETE FROM form_submissions WHERE form_id = ? AND id IN ({$in2})", array_merge([$formId], $mine));
        FormFileStore::purgeRows($files);                              // rows are gone (ON DELETE CASCADE removed form_files); now the bytes
        self::recount($formId);
        return $n;
    }

    /**
     * Re-count forms.submissions_count from the rows: the submissions that COUNT, i.e. every status except spam. This is the figure the public page, the POST
     * pre-check and store() (under its row lock) all compare with max_submissions, so staff free a place by marking junk as spam.
     */
    public static function recount(int $formId): int
    {
        Database::execute("UPDATE forms SET submissions_count = (SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status <> 'spam') WHERE id = ?", [$formId, $formId]);
        self::$countCache = [];
        return (int) Database::value('SELECT submissions_count FROM forms WHERE id = ?', [$formId], 0);
    }

    /** Remove every cached count (tests). */
    public static function flush(): void
    {
        self::$countCache = [];
    }

    /** @param array<int,mixed> $ids @return list<int> */
    private static function cleanIds(array $ids): array
    {
        $out = [];
        foreach ($ids as $i) {
            if ((is_int($i) || (is_string($i) && ctype_digit($i))) && (int) $i > 0) {
                $out[(int) $i] = (int) $i;
            }
        }
        return array_slice(array_values($out), 0, 500);
    }
}
