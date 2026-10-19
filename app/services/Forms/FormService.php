<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\Config;
use App\Core\Database;
use App\Core\Pagination;
use App\Core\Slug;
use App\Models\Form;

/**
 * FORMS: create, load, save (with optimistic locking), status transitions, duplicate, delete, availability, lists, job integration.
 * Contract: docs/FORMS.md section 4. Every method returns plain arrays; nothing here writes to the audit log (controllers do) or outputs HTML.
 *
 * A HYDRATED form is the `forms` row plus:  def (normalised definition), cfg (normalised settings); the raw JSON columns `definition` / `settings` are removed.
 * All integers are cast; opens_at / closes_at / published_at are 'Y-m-d H:i:s' strings in Africa/Kampala (EAT) or null.
 *
 * STATUS MACHINE (transition()):
 *     draft ──publish──▶ published ──close──▶ closed ──reopen──▶ published
 *       ▲                    │                  │
 *       └────unpublish───────┘                  │
 *     any (not archived) ──archive──▶ archived ──restore──▶ draft
 *   publish / reopen are refused while publishChecklist() has errors. Editing a form is allowed in every status (answers are stored with a snapshot).
 */
final class FormService
{
    public const TZ = 'Africa/Kampala';
    public const ACTIONS = ['publish' => 'published', 'unpublish' => 'draft', 'close' => 'closed', 'reopen' => 'published', 'archive' => 'archived', 'restore' => 'draft'];

    /** Slugs a form may not use besides config reserved_slugs. */
    private const EXTRA_RESERVED = ['thanks', 'embed', 'print', 'preview', 'submit', 'form', 'forms', 'list', 'index', 'api', 'static'];

    private static ?\DateTimeZone $tz = null;

    // ------------------------------------------------------------------ time

    public static function tz(): \DateTimeZone
    {
        return self::$tz ??= new \DateTimeZone(self::TZ);
    }

    /** Now in EAT. */
    public static function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', self::tz());
    }

    /**
     * Parse a date-time typed in an <input type="datetime-local"> ("2026-10-19T14:30") or stored ("2026-10-19 14:30:00") as EAT; '' / null -> null.
     * Returns 'Y-m-d H:i:s' or false when it is not a valid date-time.
     */
    public static function parseLocal(mixed $v): string|false|null
    {
        if ($v === null) {
            return null;
        }
        if (!is_string($v)) {
            return false;
        }
        $v = trim(str_replace('T', ' ', $v));
        if ($v === '') {
            return null;
        }
        foreach (['!Y-m-d H:i:s', '!Y-m-d H:i', '!Y-m-d'] as $fmt) {
            $d = \DateTimeImmutable::createFromFormat($fmt, $v, self::tz());
            if ($d !== false && $d->format(substr($fmt, 1)) === $v) {
                $y = (int) $d->format('Y');
                return $y >= 2000 && $y <= 2100 ? $d->format('Y-m-d H:i:s') : false;
            }
        }
        return false;
    }

    // ------------------------------------------------------------------ hydrate / find

    /** @param array<string,mixed> $row raw `forms` row @return array<string,mixed> */
    public static function hydrate(array $row): array
    {
        $def = FormDefinition::decode(isset($row['definition']) ? (string) $row['definition'] : null);
        $cfg = FormSettings::decode(isset($row['settings']) ? (string) $row['settings'] : null);
        unset($row['definition'], $row['settings']);
        foreach (['id', 'version', 'submissions_count', 'listed', 'created_by', 'updated_by'] as $k) {
            if (array_key_exists($k, $row) && $row[$k] !== null) {
                $row[$k] = (int) $row[$k];
            }
        }
        $row['max_submissions'] = isset($row['max_submissions']) && $row['max_submissions'] !== null ? (int) $row['max_submissions'] : null;
        $row['def'] = $def;
        $row['cfg'] = $cfg;
        return $row;
    }

    public static function find(int $id): ?array
    {
        $row = Form::find($id);
        return $row === null ? null : self::hydrate($row);
    }

    public static function findBySlug(string $slug): ?array
    {
        if (preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) !== 1 || strlen($slug) > 120) {
            return null;
        }
        $row = Form::findBy('slug', $slug);
        return $row === null ? null : self::hydrate($row);
    }

    // ------------------------------------------------------------------ slugs

    /** Why this slug cannot be used (null = fine). */
    public static function slugProblem(string $slug, ?int $ignoreId = null): ?string
    {
        if ($slug === '') {
            return 'Enter the web address of the form.';
        }
        if (strlen($slug) > 100 || preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) !== 1) {
            return 'Use lower-case letters, digits and single hyphens only (at most 100 characters), for example job-application.';
        }
        if (in_array($slug, self::EXTRA_RESERVED, true) || Slug::isReserved($slug)) {
            return '"' . $slug . '" is a reserved word. Please choose another address.';
        }
        $taken = $ignoreId === null
            ? Database::value('SELECT COUNT(*) FROM forms WHERE slug = ?', [$slug], 0)
            : Database::value('SELECT COUNT(*) FROM forms WHERE slug = ? AND id <> ?', [$slug, $ignoreId], 0);
        return (int) $taken > 0 ? 'Another form already uses this address.' : null;
    }

    /** A free, valid slug built from $text (adds -2, -3 ... and avoids reserved words). */
    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Slug::make($text, 80);
        if ($base === '') {
            $base = 'untitled-form';
        }
        if (in_array($base, self::EXTRA_RESERVED, true) || Slug::isReserved($base)) {
            $base .= '-form';
        }
        $slug = $base;
        for ($i = 2; self::slugProblem($slug, $ignoreId) !== null && $i < 500; $i++) {
            $slug = substr($base, 0, 90) . '-' . $i;
        }
        return $slug;
    }

    // ------------------------------------------------------------------ create / duplicate / delete

    /**
     * Create a draft form.
     * $input: title* (<= 190), source ('blank' | a FormTemplates key | 'duplicate:<form id>'), description, slug.
     * @return array{ok:bool,form?:array<string,mixed>,errors?:array<string,string>}
     */
    public static function create(array $input, ?int $userId = null): array
    {
        $errors = [];
        $source = (string) ($input['source'] ?? 'blank');
        $template = null;
        $copy = null;
        if ($source === 'blank' || $source === '') {
            $def = FormDefinition::blank();
            $cfg = FormSettings::defaults('general');
        } elseif (str_starts_with($source, 'duplicate:')) {
            $copy = self::find((int) substr($source, 10));
            if ($copy === null) {
                return ['ok' => false, 'errors' => ['source' => 'The form to copy no longer exists.']];
            }
            $def = $copy['def'];
            $cfg = $copy['cfg'];
        } else {
            $template = FormTemplates::instantiate($source);
            if ($template === null) {
                return ['ok' => false, 'errors' => ['source' => 'Unknown template.']];
            }
            $def = $template['definition'];
            $cfg = $template['settings'];
        }
        $title = FormDefinition::plain($input['title'] ?? ($template['title'] ?? ($copy !== null ? 'Copy of ' . $copy['title'] : '')), (int) config('form_builder.max_title', 190));
        if ($title === '') {
            $errors['title'] = 'Give the form a name.';
        }
        $slugIn = trim((string) ($input['slug'] ?? ''));
        if ($slugIn !== '') {
            $p = self::slugProblem($slugIn);
            if ($p !== null) {
                $errors['slug'] = $p;
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        $days = 0;
        try {
            $days = max(0, (int) setting('forms_retention_default_days', '0'));
        } catch (\Throwable) {
        }
        if ($days > 0 && (int) ($cfg['retention_days'] ?? 0) === 0 && $copy === null) {
            $cfg['retention_days'] = min($days, (int) config('form_builder.retention_max_days', 3650));
        }
        $id = Database::insert('forms', [
            'slug'            => $slugIn !== '' ? $slugIn : self::uniqueSlug($title),
            'title'           => $title,
            'description'     => FormDefinition::plain($input['description'] ?? ($template['description'] ?? ($copy['description'] ?? '')), 600, true) ?: null,
            'status'          => 'draft',
            'definition'      => FormDefinition::encode($def),
            'settings'        => FormSettings::encode($cfg),
            'version'         => 1,
            'listed'          => 0,
            'max_submissions' => null,
            'template_key'    => $template['key'] ?? ($copy['template_key'] ?? null),
            'created_by'      => $userId,
            'updated_by'      => $userId,
        ]);
        return ['ok' => true, 'form' => self::find($id)];
    }

    /** Duplicate a form as a new DRAFT ("Copy of ..."), without its submissions. @return array{ok:bool,form?:array,errors?:array} */
    public static function duplicate(int $id, ?int $userId = null): array
    {
        $src = self::find($id);
        if ($src === null) {
            return ['ok' => false, 'errors' => ['form' => 'That form no longer exists.']];
        }
        return self::create(['source' => 'duplicate:' . $id, 'title' => mb_substr('Copy of ' . $src['title'], 0, 190)], $userId);
    }

    /**
     * Delete a form with ALL its submissions and uploaded files (jobs that used it fall back to the built-in application form: FK ON DELETE SET NULL).
     * @return array{ok:bool,submissions:int,files:int}
     */
    public static function delete(int $id): array
    {
        $form = Form::find($id);
        if ($form === null) {
            return ['ok' => false, 'submissions' => 0, 'files' => 0];
        }
        $files = Database::all('SELECT ff.stored_path FROM form_files ff JOIN form_submissions s ON s.id = ff.submission_id WHERE s.form_id = ?', [$id]);
        $subs = (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ?', [$id], 0);
        Database::execute('DELETE FROM forms WHERE id = ?', [$id]);          // cascades to form_submissions and form_files
        $n = FormFileStore::purgeRows($files);
        SubmissionService::flush();
        return ['ok' => true, 'submissions' => $subs, 'files' => $n];
    }

    // ------------------------------------------------------------------ save

    /**
     * Save a form (builder "Save" and autosave). Optimistic locking: $expectedVersion must be the version the editor loaded.
     *
     * $input may contain (any subset): title, description, slug, listed, opens_at, closes_at, max_submissions, definition (array|JSON), settings (array|JSON).
     * $opts: autosave (bool, only recorded in the result).
     *
     * @return array{ok:bool,changed?:bool,form?:array,errors?:array<string,string>,warnings?:list<string>,checklist?:list<array>,conflict?:bool,not_found?:bool,not_ready?:bool,current?:array}
     *   conflict  : somebody saved a newer version -> `form` is the newest one (the editor offers reload / keep mine)
     *   errors    : path => message (422): title, slug, opens_at, closes_at, max_submissions, definition / pages.N..., settings paths. NOTHING is saved when there are errors.
     *   not_ready : the form is live (published) and the new content would break the publish checklist; `errors` holds the blocking items by code.
     */
    public static function save(int $id, array $input, int $expectedVersion, ?int $userId = null, array $opts = []): array
    {
        $row = Form::find($id);
        if ($row === null) {
            return ['ok' => false, 'not_found' => true];
        }
        if ((int) $row['version'] !== $expectedVersion) {
            return ['ok' => false, 'conflict' => true, 'form' => self::hydrate($row), 'current' => ['version' => (int) $row['version'], 'updated_at' => (string) $row['updated_at'], 'updated_by' => $row['updated_by'] !== null ? (int) $row['updated_by'] : null]];
        }
        $cur = self::hydrate($row);
        $errors = [];
        $warnings = [];
        $set = [];

        if (array_key_exists('title', $input)) {
            $t = FormDefinition::plain($input['title'], (int) config('form_builder.max_title', 190));
            if ($t === '') {
                $errors['title'] = 'Give the form a name.';
            } else {
                $set['title'] = $t;
            }
        }
        if (array_key_exists('description', $input)) {
            $set['description'] = FormDefinition::plain($input['description'], 600, true) ?: null;
        }
        if (array_key_exists('slug', $input)) {
            $slug = strtolower(trim((string) $input['slug']));
            if ($slug !== (string) $cur['slug']) {
                $p = self::slugProblem($slug, $id);
                if ($p !== null) {
                    $errors['slug'] = $p;
                } else {
                    $set['slug'] = $slug;
                    if ($cur['status'] === 'published') {
                        $warnings[] = 'The web address changed: links to the old address no longer work.';
                    }
                }
            }
        }
        if (array_key_exists('listed', $input)) {
            $set['listed'] = in_array($input['listed'], [true, 1, '1', 'true', 'on'], true) ? 1 : 0;
        }
        foreach (['opens_at', 'closes_at'] as $k) {
            if (array_key_exists($k, $input)) {
                $p = self::parseLocal($input[$k]);
                if ($p === false) {
                    $errors[$k] = 'Enter a valid date and time.';
                } else {
                    $set[$k] = $p;
                }
            }
        }
        if (array_key_exists('max_submissions', $input)) {
            $m = $input['max_submissions'];
            if ($m === null || $m === '' || $m === false) {
                $set['max_submissions'] = null;
            } elseif ((is_int($m) || (is_string($m) && ctype_digit(trim($m)))) && (int) $m >= 1 && (int) $m <= 1000000) {
                $set['max_submissions'] = (int) $m;
            } else {
                $errors['max_submissions'] = 'Enter a whole number of 1 or more, or leave it empty for no limit.';
            }
        }
        $opens = array_key_exists('opens_at', $set) ? $set['opens_at'] : ($cur['opens_at'] ?? null);
        $closes = array_key_exists('closes_at', $set) ? $set['closes_at'] : ($cur['closes_at'] ?? null);
        if (!isset($errors['opens_at']) && !isset($errors['closes_at']) && $opens !== null && $closes !== null && (string) $closes <= (string) $opens) {
            $errors['closes_at'] = 'The closing date must be after the opening date.';
        }

        $def = $cur['def'];
        if (array_key_exists('definition', $input)) {
            $n = FormDefinition::normalise($input['definition']);
            if (!$n['ok']) {
                $errors += $n['errors'];
            } else {
                $def = $n['definition'];
                $warnings = array_merge($warnings, $n['warnings']);
                $set['definition'] = FormDefinition::encode($def);
            }
        }
        $cfg = $cur['cfg'];
        if (array_key_exists('settings', $input)) {
            $n = FormSettings::normalise($input['settings'], (string) ($cur['cfg']['kind'] ?? 'general'));
            foreach ($n['errors'] as $path => $msg) {
                $errors['settings.' . $path] = $msg;
            }
            if ($n['errors'] === []) {
                $cfg = $n['settings'];
                $set['settings'] = FormSettings::encode($cfg);
                // Destructive / privacy-relevant settings need the matching ability (callers pass `abilities`; trusted internal callers omit it).
                if (isset($opts['abilities']) && is_array($opts['abilities'])) {
                    $denied = self::deniedSettings((array) $cur['cfg'], $cfg, $opts['abilities']);
                    if ($denied !== []) {
                        return ['ok' => false, 'forbidden' => true, 'errors' => $denied, 'warnings' => $warnings];
                    }
                }
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // anything that really changed?
        $changed = false;
        foreach ($set as $k => $v) {
            if ((string) ($row[$k] ?? '') !== (string) ($v ?? '')) {
                $changed = true;
            }
        }
        $meta = ['slug' => $set['slug'] ?? $cur['slug'], 'opens_at' => $opens, 'closes_at' => $closes, 'max_submissions' => array_key_exists('max_submissions', $set) ? $set['max_submissions'] : $cur['max_submissions']];
        $checklist = FormDefinition::publishChecklist($def, $cfg, $meta);
        if (!$changed) {
            return ['ok' => true, 'changed' => false, 'form' => $cur, 'warnings' => $warnings, 'checklist' => $checklist];
        }
        // a LIVE form must stay complete
        if ($cur['status'] === 'published') {
            $blocking = array_values(array_filter($checklist, static fn(array $c): bool => $c['level'] === 'error'));
            if ($blocking !== []) {
                $errs = [];
                foreach ($blocking as $b) {
                    $errs[$b['code'] . ($b['field'] ? '.' . $b['field'] : '')] = $b['message'];
                }
                return ['ok' => false, 'not_ready' => true, 'errors' => $errs, 'warnings' => $warnings, 'checklist' => $checklist];
            }
        }
        $set['updated_by'] = $userId;
        $assign = [];
        $params = [];
        foreach ($set as $col => $val) {
            $assign[] = Database::ident($col) . ' = ?';
            $params[] = $val;
        }
        $assign[] = '`version` = `version` + 1';
        $params[] = $id;
        $params[] = $expectedVersion;
        try {
            $affected = Database::execute('UPDATE forms SET ' . implode(', ', $assign) . ' WHERE id = ? AND version = ?', $params);
        } catch (\PDOException $e) {
            if ((string) $e->getCode() === '23000') {                 // unique slug raced
                return ['ok' => false, 'errors' => ['slug' => 'Another form already uses this address.'], 'warnings' => $warnings];
            }
            throw $e;
        }
        if ($affected === 0) {
            $fresh = Form::find($id);
            return $fresh === null ? ['ok' => false, 'not_found' => true] : ['ok' => false, 'conflict' => true, 'form' => self::hydrate($fresh), 'current' => ['version' => (int) $fresh['version'], 'updated_at' => (string) $fresh['updated_at'], 'updated_by' => $fresh['updated_by'] !== null ? (int) $fresh['updated_by'] : null]];
        }
        return ['ok' => true, 'changed' => true, 'form' => self::find($id), 'warnings' => $warnings, 'checklist' => $checklist];
    }

    /**
     * Settings a role may not change without a second ability (a forms.manage-only role must not reach answers or erase them):
     *  - retention_days: setting or LOWERING the number deletes submissions permanently  -> needs forms.delete (raising it or switching it off is free);
     *  - notify.include_answers / the notify recipients while answers are e-mailed        -> needs forms.submissions.
     * @param array<string,mixed> $old  current settings   @param array<string,mixed> $new  normalised new settings
     * @param array{can_delete?:bool,can_submissions?:bool} $abilities
     * @return array<string,string> path => message (empty = allowed)
     */
    public static function deniedSettings(array $old, array $new, array $abilities): array
    {
        $out = [];
        $oldDays = (int) ($old['retention_days'] ?? 0);
        $newDays = (int) ($new['retention_days'] ?? 0);
        if ($newDays > 0 && ($oldDays === 0 || $newDays < $oldDays) && empty($abilities['can_delete'])) {
            $out['settings.retention_days'] = 'Setting or shortening the retention period deletes answers permanently, so it needs the "Delete forms and answers" permission.';
        }
        $oldAns = !empty($old['notify']['include_answers']);
        $newAns = !empty($new['notify']['include_answers']);
        $oldTo = array_values((array) ($old['notify']['emails'] ?? []));
        $newTo = array_values((array) ($new['notify']['emails'] ?? []));
        sort($oldTo);
        sort($newTo);
        if (empty($abilities['can_submissions'])) {
            if ($newAns && !$oldAns) {
                $out['settings.notify.include_answers'] = 'E-mailing the answers needs the permission to read form answers.';
            } elseif (($oldAns || $newAns) && $oldTo !== $newTo) {
                $out['settings.notify.emails'] = 'While answers are e-mailed, changing the recipients needs the permission to read form answers.';
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------ status

    /** The checklist of a hydrated form (definition + settings + dates). @return list<array{level:string,code:string,message:string,page:?string,field:?string}> */
    public static function checklist(array $form): array
    {
        return FormDefinition::publishChecklist((array) $form['def'], (array) $form['cfg'], [
            'slug' => $form['slug'], 'opens_at' => $form['opens_at'] ?? null, 'closes_at' => $form['closes_at'] ?? null, 'max_submissions' => $form['max_submissions'] ?? null,
        ]);
    }

    /**
     * Change the status: publish | unpublish | close | reopen | archive | restore (see the machine in the class comment).
     * @return array{ok:bool,form?:array,error?:string,errors?:array<string,string>,checklist?:list<array>,conflict?:bool,not_found?:bool}
     */
    public static function transition(int $id, string $action, ?int $expectedVersion = null, ?int $userId = null): array
    {
        $row = Form::find($id);
        if ($row === null) {
            return ['ok' => false, 'not_found' => true];
        }
        if (!isset(self::ACTIONS[$action])) {
            return ['ok' => false, 'error' => 'Unknown action.'];
        }
        if ($expectedVersion !== null && (int) $row['version'] !== $expectedVersion) {
            return ['ok' => false, 'conflict' => true, 'form' => self::hydrate($row)];
        }
        $from = (string) $row['status'];
        $allowed = match ($action) {
            'publish'   => ['draft'],
            'unpublish' => ['published', 'closed'],
            'close'     => ['published'],
            'reopen'    => ['closed'],
            'archive'   => ['draft', 'published', 'closed'],
            'restore'   => ['archived'],
        };
        if (!in_array($from, $allowed, true)) {
            return ['ok' => false, 'error' => 'This form is ' . $from . ': it cannot be changed that way.'];
        }
        $form = self::hydrate($row);
        $to = self::ACTIONS[$action];
        $checklist = [];
        if ($to === 'published') {
            $checklist = self::checklist($form);
            $blocking = array_values(array_filter($checklist, static fn(array $c): bool => $c['level'] === 'error'));
            if ($blocking !== []) {
                $errs = [];
                foreach ($blocking as $b) {
                    $errs[$b['code'] . ($b['field'] ? '.' . $b['field'] : '')] = $b['message'];
                }
                return ['ok' => false, 'error' => 'Fix the items below before the form can go live.', 'errors' => $errs, 'checklist' => $checklist];
            }
        }
        $data = ['status' => $to, 'updated_by' => $userId];
        if ($to === 'published' && empty($row['published_at'])) {
            $data['published_at'] = self::now()->format('Y-m-d H:i:s');
        }
        $assign = [];
        $params = [];
        foreach ($data as $c => $v) {
            $assign[] = Database::ident($c) . ' = ?';
            $params[] = $v;
        }
        $assign[] = '`version` = `version` + 1';
        $params[] = $id;
        $params[] = (int) $row['version'];
        if (Database::execute('UPDATE forms SET ' . implode(', ', $assign) . ' WHERE id = ? AND version = ?', $params) === 0) {
            $fresh = Form::find($id);
            return ['ok' => false, 'conflict' => true, 'form' => $fresh !== null ? self::hydrate($fresh) : null];
        }
        return ['ok' => true, 'form' => self::find($id), 'checklist' => $checklist];
    }

    // ------------------------------------------------------------------ availability

    /**
     * Can people send this form right now?
     *
     * @param array<string,mixed> $form   hydrated form
     * @param ?int $taken                 submissions already counted against max_submissions (default: forms.submissions_count)
     * @return array{state:string,can_submit:bool,visible:bool,opens_at:?string,closes_at:?string,remaining:?int}
     *   state : open | draft | archived | not_open (published, before opens_at) | closed (status closed or past closes_at) | full (max_submissions reached)
     *   visible: false = answer 404 (draft, archived); true = show the page (with a friendly notice unless state is open)
     */
    public static function availability(array $form, ?\DateTimeImmutable $now = null, ?int $taken = null): array
    {
        $now ??= self::now();
        $stamp = $now->format('Y-m-d H:i:s');
        $opens = !empty($form['opens_at']) ? (string) $form['opens_at'] : null;
        $closes = !empty($form['closes_at']) ? (string) $form['closes_at'] : null;
        $max = isset($form['max_submissions']) && $form['max_submissions'] !== null ? (int) $form['max_submissions'] : null;
        $taken ??= (int) ($form['submissions_count'] ?? 0);
        $remaining = $max !== null ? max(0, $max - $taken) : null;
        $state = 'open';
        $status = (string) ($form['status'] ?? 'draft');
        if ($status === 'draft') {
            $state = 'draft';
        } elseif ($status === 'archived') {
            $state = 'archived';
        } elseif ($status === 'closed') {
            $state = 'closed';
        } elseif ($opens !== null && $stamp < $opens) {
            $state = 'not_open';
        } elseif ($closes !== null && $stamp >= $closes) {
            $state = 'closed';
        } elseif ($max !== null && $taken >= $max) {
            $state = 'full';
        }
        return [
            'state'      => $state,
            'can_submit' => $state === 'open',
            'visible'    => !in_array($state, ['draft', 'archived'], true),
            'opens_at'   => $opens,
            'closes_at'  => $closes,
            'remaining'  => $remaining,
        ];
    }

    // ------------------------------------------------------------------ lists

    /**
     * Admin list. $filters: status (draft|published|closed|archived; '' = all except archived unless `with_archived`), q (title / slug), listed (1).
     * Every item carries `unread` (submissions with status new) and `availability`.
     */
    public static function adminList(array $filters, int $page, int $perPage): Pagination
    {
        $w = ['1 = 1'];
        $p = [];
        $status = (string) ($filters['status'] ?? '');
        if (in_array($status, Form::STATUSES, true)) {
            $w[] = 'f.status = ?';
            $p[] = $status;
        } elseif (empty($filters['with_archived'])) {
            $w[] = "f.status <> 'archived'";
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . addcslashes(mb_substr($q, 0, 100), '\\%_') . '%';
            $w[] = '(f.title LIKE ? OR f.slug LIKE ?)';
            array_push($p, $like, $like);
        }
        if (!empty($filters['listed'])) {
            $w[] = 'f.listed = 1';
        }
        $where = implode(' AND ', $w);
        $total = (int) Database::value("SELECT COUNT(*) FROM forms f WHERE {$where}", $p, 0);
        $pg = Pagination::make($total, $page, $perPage);
        $rows = Database::all(
            "SELECT f.*, (SELECT COUNT(*) FROM form_submissions s WHERE s.form_id = f.id AND s.status = 'new') AS unread FROM forms f WHERE {$where} ORDER BY f.updated_at DESC, f.id DESC LIMIT ? OFFSET ?",
            array_merge($p, [$pg->perPage, $pg->offset])
        );
        $pg->items = array_map(static function (array $r): array {
            $unread = (int) $r['unread'];
            $h = self::hydrate($r);
            $h['unread'] = $unread;
            $h['availability'] = self::availability($h);
            return $h;
        }, $rows);
        return $pg;
    }

    /** Count per status for the filter tabs: ['all' => n (without archived), 'draft' => ..., 'published' => ..., 'closed' => ..., 'archived' => ...]. @return array<string,int> */
    public static function statusCounts(): array
    {
        $out = ['draft' => 0, 'published' => 0, 'closed' => 0, 'archived' => 0];
        foreach (Database::all('SELECT status, COUNT(*) AS n FROM forms GROUP BY status') as $r) {
            $out[(string) $r['status']] = (int) $r['n'];
        }
        $out['all'] = $out['draft'] + $out['published'] + $out['closed'];
        return $out;
    }

    /** Published, listed forms for the public /forms page (title, slug, description, availability), newest first. @return list<array<string,mixed>> */
    public static function directory(): array
    {
        $rows = Database::all("SELECT * FROM forms WHERE status = 'published' AND listed = 1 ORDER BY title ASC LIMIT 100");
        $out = [];
        foreach ($rows as $r) {
            $h = self::hydrate($r);
            $h['availability'] = self::availability($h);
            if ($h['availability']['visible']) {
                $out[] = $h;
            }
        }
        return $out;
    }

    /** Is the public /forms directory switched on (Site settings > Forms)? */
    public static function directoryEnabled(): bool
    {
        try {
            return (string) setting('forms_directory_enabled', '0') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    // ------------------------------------------------------------------ careers integration

    /**
     * The custom application form of a job, but ONLY when people can use it right now (published, open, not full). Otherwise null, and the job page
     * shows the built-in application form as before ("falls back gracefully"). @param array<string,mixed> $job row of `jobs`
     */
    public static function jobForm(array $job): ?array
    {
        $fid = (int) ($job['form_id'] ?? 0);
        if ($fid < 1) {
            return null;
        }
        try {
            $form = self::find($fid);
        } catch (\Throwable) {
            return null;
        }
        return $form !== null && self::availability($form)['can_submit'] ? $form : null;
    }

    /** Link of the Apply button of a job that uses a custom form: /forms/{slug}?job={job slug}. */
    public static function jobApplyUrl(array $job, array $form): string
    {
        return url('forms/' . $form['slug'], ['job' => (string) $job['slug']]);
    }

    /**
     * Verify the `job` context of a request: the job must exist, be active, not past its closing date AND point to THIS form (jobs.form_id).
     * Anything else is refused, so nobody can attach an arbitrary label to a submission.
     *
     * @return ?array{type:string,ref:string,label:string,ok:bool,reason:?string}  null when no job was asked for; ok=false (+ reason closed|unknown) for an invalid one
     */
    public static function resolveContext(array $form, ?string $jobSlug): ?array
    {
        $jobSlug = trim((string) $jobSlug);
        if ($jobSlug === '') {
            return null;
        }
        $bad = ['type' => 'job', 'ref' => '', 'label' => '', 'ok' => false, 'reason' => 'unknown'];
        if (preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $jobSlug) !== 1 || strlen($jobSlug) > 190) {
            return $bad;
        }
        try {
            $job = Database::one('SELECT slug, title, is_active, closing_date, form_id FROM jobs WHERE slug = ? LIMIT 1', [$jobSlug]);
        } catch (\Throwable) {
            return $bad;
        }
        if ($job === null || (int) $job['is_active'] !== 1 || (int) ($job['form_id'] ?? 0) !== (int) $form['id']) {
            return $bad;
        }
        $closing = (string) ($job['closing_date'] ?? '');
        if ($closing !== '' && $closing !== '0000-00-00' && $closing < self::now()->format('Y-m-d')) {
            return ['type' => 'job', 'ref' => (string) $job['slug'], 'label' => (string) $job['title'], 'ok' => false, 'reason' => 'closed'];
        }
        return ['type' => 'job', 'ref' => (string) $job['slug'], 'label' => (string) $job['title'], 'ok' => true, 'reason' => null];
    }

    /** The jobs a form can be attached to, for the selector in the job admin form: id => title (active jobs). Forms: id => title (draft and published, not archived). */
    public static function formChoices(): array
    {
        $out = [];
        try {
            foreach (Database::all("SELECT id, title, status FROM forms WHERE status <> 'archived' ORDER BY title ASC LIMIT 300") as $r) {
                $out[(int) $r['id']] = (string) $r['title'] . ($r['status'] === 'published' ? '' : ' (' . $r['status'] . ')');
            }
        } catch (\Throwable) {
        }
        return $out;
    }

    // ------------------------------------------------------------------ embeds, client config, test submit

    /** Ways to put the form on a page. @return array{link:string,shortcode:string,button_html:string} */
    public static function embedCode(array $form): array
    {
        $link = abs_url('forms/' . $form['slug']);
        $title = htmlspecialchars((string) $form['title'], ENT_QUOTES, 'UTF-8');
        return [
            'link'        => $link,
            'shortcode'   => '[form slug="' . $form['slug'] . '"]',
            'button_html' => '<a class="btn btn--primary" href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">' . $title . '</a>',
        ];
    }

    /** Data for assets/js/forms.js (rendered by the public view as a data attribute or a JSON script block). Nothing private is in it. @return array<string,mixed> */
    public static function clientConfig(array $form): array
    {
        $cfg = (array) $form['cfg'];
        return [
            'slug'     => (string) $form['slug'],
            'version'  => (int) $form['version'],
            'layout'   => (string) ($cfg['theme']['layout'] ?? 'stepped'),
            'progress' => !empty($cfg['theme']['progress']),
            'other'    => FormDefinition::OTHER,
            'pages'    => $form['def']['pages'],
            'patterns' => FormDefinition::clientPatterns(),
            'draftKey' => 'lumina-form:' . $form['slug'] . ':v' . $form['version'],
        ];
    }

    /**
     * TEST SUBMIT (builder): runs the full validation and builds the snapshot a submission would have, but stores NOTHING and sends NO e-mail.
     * Uploaded files are inspected (so rule problems show) and then ignored.
     * @return array{ok:bool,errors:array<string,string>,visible:array<string,bool>,pages_visible:array<string,bool>,answers:list<array<string,mixed>>,error_pages:list<string>}
     */
    public static function testSubmit(array $form, array $input, array $files = []): array
    {
        $res = FormValidator::validate((array) $form['def'], $input, $files, ['settings' => $form['cfg']]);
        $answers = $res['ok'] ? SubmissionService::buildSnapshot((array) $form['def'], $res) : [];
        return ['ok' => $res['ok'], 'errors' => $res['errors'], 'visible' => $res['visible'], 'pages_visible' => $res['pages_visible'], 'answers' => $answers, 'error_pages' => $res['error_pages']];
    }

    /** Is the Forms module's schema present (tables imported)? */
    public static function available(): bool
    {
        try {
            return Database::tableExists('forms') && Database::tableExists('form_submissions') && Database::tableExists('form_files');
        } catch (\Throwable) {
            return false;
        }
    }

    /** Numbers for the dashboard card and the sidebar badge. @return array{new:int,forms:int,published:int,week:int} */
    public static function summary(): array
    {
        try {
            return [
                'new'       => SubmissionService::newCount(),
                'forms'     => (int) Database::value("SELECT COUNT(*) FROM forms WHERE status <> 'archived'", [], 0),
                'published' => (int) Database::value("SELECT COUNT(*) FROM forms WHERE status = 'published'", [], 0),
                'week'      => (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE created_at >= ?", [self::now()->modify('-7 days')->format('Y-m-d H:i:s')], 0),
            ];
        } catch (\Throwable) {
            return ['new' => 0, 'forms' => 0, 'published' => 0, 'week' => 0];
        }
    }
}
