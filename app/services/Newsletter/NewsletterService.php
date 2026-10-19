<?php
declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Core\Pagination;
use App\Core\Sanitizer;
use App\Core\SitePages;
use App\Core\Slug;
use App\Models\Newsletter;
use App\Models\NewsletterRevision;
use App\Services\ImageCleanup;

/**
 * Newsletter business rules. Everything that touches `newsletters.status`, `.version`, `.content`, the revisions or the slug goes through here.
 * Contract: docs/NEWSLETTER.md. All methods are static; none throws for an ordinary business problem (they return ok=false + messages).
 *
 * STATUS WORKFLOW (TRANSITIONS): draft -> scheduled | published | archived;  scheduled -> draft | published | archived;
 *   published -> draft (unpublish) | archived;  archived -> draft.   An issue whose e-mail campaign is queued / sending / paused cannot be
 *   unpublished, archived or deleted until the campaign is cancelled or finished.
 * VISIBILITY (the ONE public predicate, visibleSql()): status = 'published'  OR  (status = 'scheduled' AND publish_at <= now).
 *   Scheduled issues therefore appear on time without a cron job; resolveScheduled() additionally flips the stored status once.
 * OPTIMISTIC LOCKING: newsletters.version rises by one on every save that changes details or content; save() needs the version the editor
 *   loaded and answers {ok:false, conflict:true} when somebody saved in between. Status changes do not touch the version.
 */
final class NewsletterService
{
    public const TRANSITIONS = [
        'draft'     => ['scheduled', 'published', 'archived'],
        'scheduled' => ['draft', 'published', 'archived'],
        'published' => ['draft', 'archived'],
        'archived'  => ['draft'],
    ];
    public const MAX_ISSUE_NUMBER = 99999;
    /** how an issue is read: written online with the block editor, or an uploaded PDF (an online issue may ALSO carry a PDF) */
    public const KINDS = ['online', 'pdf'];
    public const KIND_LABELS = ['online' => 'Written online', 'pdf' => 'PDF'];
    public const STATUS_LABELS = ['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published', 'archived' => 'Archived'];
    public const STATUS_TONES = ['draft' => 'warning', 'scheduled' => 'info', 'published' => 'success', 'archived' => 'neutral'];
    public const EMAIL_LABELS = [
        'none' => 'Not sent', 'queued' => 'Queued', 'sending' => 'Sending', 'paused' => 'Paused', 'sent' => 'Sent', 'cancelled' => 'Cancelled',
    ];
    public const EMAIL_TONES = ['none' => 'neutral', 'queued' => 'info', 'sending' => 'info', 'paused' => 'warning', 'sent' => 'success', 'cancelled' => 'danger'];
    /** slugs that would collide with a route ("/newsletter/archive") or confuse readers */
    public const RESERVED_SLUGS = ['archive', 'latest', 'preview', 'feed', 'rss', 'api'];
    /** the public ordering of issues everywhere: newest issue date first, then newest id */
    public const PUBLIC_ORDER = 'n.issue_date DESC, n.id DESC';
    /** columns for list screens (no heavy `content`) */
    public const LIST_COLUMNS = 'n.id, n.slug, n.issue_number, n.title, n.summary, n.issue_date, n.cover_image, n.cover_alt, n.status, n.publish_at, n.published_at, n.version, n.email_enabled, n.email_status, n.email_total, n.email_sent, n.email_failed, n.updated_at, n.kind, n.pdf_path, n.pdf_name, n.pdf_size, n.pdf_downloads, (n.pdf_path IS NOT NULL AND CHAR_LENGTH(n.pdf_path) > 0) AS has_pdf';

    private static bool $resolved = false;

    // ------------------------------------------------------------------------------------------------ time

    /** Now in the application time zone, 'Y-m-d H:i:s' (what is stored and compared; never the DB server's NOW()). */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Parse what a date-time picker sends ("2026-11-03T09:30", "2026-11-03 09:30", with or without seconds) as a time in the application
     * time zone (Africa/Kampala). Returns 'Y-m-d H:i:s' or null when it is not a real date and time.
     */
    public static function parseLocalDateTime(?string $value): ?string
    {
        $value = trim((string) $value);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})(:\d{2})?$/', $value, $m) !== 1) {
            return null;
        }
        $norm = $m[1] . ' ' . $m[2] . (($m[3] ?? '') !== '' ? $m[3] : ':00');
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $norm);
        return $d !== false && $d->format('Y-m-d H:i:s') === $norm && (int) $d->format('Y') >= 2000 && (int) $d->format('Y') <= 2100 ? $norm : null;
    }

    // ------------------------------------------------------------------------------------------------ public visibility

    /**
     * The "is this issue public right now" predicate, shared by EVERY public query (page, archive, API, sitemap, prev/next).
     *
     * @return array{0:string,1:array<int,string>} [sql, params] e.g. ["(n.status = 'published' OR (...))", ['2026-10-17 09:00:00']]
     */
    public static function visibleSql(string $alias = 'n', ?string $now = null): array
    {
        $a = preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $alias) === 1 ? $alias . '.' : '';
        return [
            "({$a}status = 'published' OR ({$a}status = 'scheduled' AND {$a}publish_at IS NOT NULL AND {$a}publish_at <= ?))",
            [$now ?? self::now()],
        ];
    }

    /** The same rule for a row already in memory. */
    public static function isVisible(array $row, ?string $now = null): bool
    {
        $status = (string) ($row['status'] ?? '');
        if ($status === 'published') {
            return true;
        }
        return $status === 'scheduled' && !empty($row['publish_at']) && (string) $row['publish_at'] <= ($now ?? self::now());
    }

    /** Live = visible to the public. Only a live issue may be e-mailed. */
    public static function isLive(array $row): bool
    {
        return self::isVisible($row);
    }

    /**
     * Lazy publisher: flips scheduled issues whose time has come to 'published' (published_at = their publish_at). Idempotent, race-safe
     * (the UPDATE only matches a still-scheduled row), runs at most once per request unless $force. Returns the number flipped.
     */
    public static function resolveScheduled(bool $force = false): int
    {
        if (self::$resolved && !$force) {
            return 0;
        }
        self::$resolved = true;
        $flipped = 0;
        try {
            $now = self::now();
            foreach (Database::all("SELECT id FROM newsletters WHERE status = 'scheduled' AND publish_at IS NOT NULL AND publish_at <= ?", [$now]) as $r) {
                $n = Database::execute(
                    "UPDATE newsletters SET status = 'published', published_at = COALESCE(published_at, publish_at) WHERE id = ? AND status = 'scheduled' AND publish_at <= ?",
                    [(int) $r['id'], $now]
                );
                if ($n > 0) {
                    $flipped++;
                    AuditLog::record('newsletter.publish', 'newsletter', (int) $r['id'], 'Scheduled issue went live', ['scheduled' => true]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Newsletter: resolveScheduled failed - ' . $e->getMessage());
        }
        return $flipped;
    }

    // ------------------------------------------------------------------------------------------------ public reads

    /** The current issue: the newest PUBLIC one (by issue date). */
    public static function latestPublished(): ?array
    {
        self::resolveScheduled();
        [$sql, $params] = self::visibleSql('n');
        return Database::one("SELECT n.* FROM newsletters n WHERE {$sql} ORDER BY " . self::PUBLIC_ORDER . ' LIMIT 1', $params);
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        self::resolveScheduled();
        [$sql, $params] = self::visibleSql('n');
        return Database::one("SELECT n.* FROM newsletters n WHERE n.slug = ? AND {$sql} LIMIT 1", array_merge([$slug], $params));
    }

    public static function countPublished(): int
    {
        self::resolveScheduled();
        [$sql, $params] = self::visibleSql('n');
        return (int) Database::value("SELECT COUNT(*) FROM newsletters n WHERE {$sql}", $params, 0);
    }

    /**
     * Public archive page: newest first. Rows carry LIST_COLUMNS (no content). $excludeId leaves one issue out (e.g. the current one).
     * Throws HttpException(404) for a page beyond the last one (Pagination::make).
     */
    public static function archive(int $page, int $perPage = 12, ?int $excludeId = null): Pagination
    {
        self::resolveScheduled();
        [$sql, $params] = self::visibleSql('n');
        if ($excludeId !== null) {
            $sql .= ' AND n.id <> ?';
            $params[] = $excludeId;
        }
        $total = (int) Database::value("SELECT COUNT(*) FROM newsletters n WHERE {$sql}", $params, 0);
        $p = Pagination::make($total, $page, $perPage);
        $p->items = Database::all(
            'SELECT ' . self::LIST_COLUMNS . " FROM newsletters n WHERE {$sql} ORDER BY " . self::PUBLIC_ORDER . ' LIMIT ? OFFSET ?',
            array_merge($params, [$p->perPage, $p->offset])
        );
        return $p;
    }

    /** Neighbouring PUBLIC issues in archive order: prev = the next older one, next = the next newer one (either may be null). */
    public static function adjacent(array $issue): array
    {
        [$sql, $params] = self::visibleSql('n');
        $date = (string) $issue['issue_date'];
        $id = (int) $issue['id'];
        $cols = 'n.id, n.slug, n.kind, n.issue_number, n.title, n.issue_date, n.cover_image, n.cover_alt, n.summary';
        $prev = Database::one(
            "SELECT {$cols} FROM newsletters n WHERE {$sql} AND (n.issue_date < ? OR (n.issue_date = ? AND n.id < ?)) ORDER BY n.issue_date DESC, n.id DESC LIMIT 1",
            array_merge($params, [$date, $date, $id])
        );
        $next = Database::one(
            "SELECT {$cols} FROM newsletters n WHERE {$sql} AND (n.issue_date > ? OR (n.issue_date = ? AND n.id > ?)) ORDER BY n.issue_date ASC, n.id ASC LIMIT 1",
            array_merge($params, [$date, $date, $id])
        );
        return ['prev' => $prev, 'next' => $next];
    }

    /** Public URL of an issue ("/newsletter/{slug}"); the current issue is also at "/newsletter". */
    public static function url(array $issue, bool $absolute = false): string
    {
        return url('newsletter/' . $issue['slug'], [], $absolute);
    }

    /**
     * SEO data of an issue for the layout: title, description (summary, else a clip of the text), og_image (first image), canonical (absolute).
     * @return array{title:string,description:string,og_image:string,canonical:string,published:string,modified:string}
     */
    public static function seo(array $issue): array
    {
        $content = NewsletterContent::decode((string) ($issue['content'] ?? ''));
        $title = trim((string) ($issue['seo_title'] ?? '')) !== '' ? (string) $issue['seo_title'] : (string) $issue['title'];
        $desc = trim((string) ($issue['seo_description'] ?? ''));
        if ($desc === '') {
            $desc = trim((string) ($issue['summary'] ?? ''));
        }
        if ($desc === '') {
            $desc = NewsletterContent::plainText($content, 160);
        }
        $image = (string) ($issue['cover_image'] ?? '');
        if ($image === '') {
            $image = (string) (NewsletterContent::firstImage($content)['path'] ?? '');
        }
        return [
            'title'       => $title,
            'description' => $desc,
            'og_image'    => $image,
            'canonical'   => self::url($issue, true),
            'published'   => (string) ($issue['published_at'] ?? $issue['publish_at'] ?? $issue['issue_date'] ?? ''),
            'modified'    => (string) ($issue['updated_at'] ?? ''),
        ];
    }

    // ------------------------------------------------------------------------------------------------ admin reads

    public static function find(int $id): ?array
    {
        return Newsletter::find($id);
    }

    /**
     * Admin issues list. Filters: status (draft|scheduled|published|archived), email_status, q (title / number). Newest issue date first.
     * @param array{status?:string,email_status?:string,q?:string} $filters
     */
    public static function adminList(array $filters, int $page, int $perPage): Pagination
    {
        self::resolveScheduled();
        [$where, $params] = self::adminWhere($filters);
        $total = (int) Database::value("SELECT COUNT(*) FROM newsletters n {$where}", $params, 0);
        $p = Pagination::make($total, $page, $perPage);
        $p->items = Database::all(
            'SELECT ' . self::LIST_COLUMNS . " FROM newsletters n {$where} ORDER BY n.issue_date DESC, n.id DESC LIMIT ? OFFSET ?",
            array_merge($params, [$p->perPage, $p->offset])
        );
        return $p;
    }

    /** @return array<string,int> status => count, plus 'all' */
    public static function statusCounts(): array
    {
        $out = ['all' => 0, 'draft' => 0, 'scheduled' => 0, 'published' => 0, 'archived' => 0];
        foreach (Database::all('SELECT status, COUNT(*) AS c FROM newsletters GROUP BY status') as $r) {
            $out[(string) $r['status']] = (int) $r['c'];
            $out['all'] += (int) $r['c'];
        }
        return $out;
    }

    /** @return array{0:string,1:array<int,mixed>} */
    private static function adminWhere(array $f): array
    {
        $w = [];
        $p = [];
        $status = (string) ($f['status'] ?? '');
        if (in_array($status, Newsletter::STATUSES, true)) {
            $w[] = 'n.status = ?';
            $p[] = $status;
        }
        $email = (string) ($f['email_status'] ?? '');
        if (in_array($email, Newsletter::EMAIL_STATUSES, true)) {
            $w[] = 'n.email_status = ?';
            $p[] = $email;
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 100)) . '%';
            $w[] = '(n.title LIKE ? OR n.summary LIKE ? OR n.slug LIKE ?' . (ctype_digit($q) ? ' OR n.issue_number = ?' : '') . ')';
            array_push($p, $like, $like, $like);
            if (ctype_digit($q)) {
                $p[] = (int) $q;
            }
        }
        return [$w === [] ? '' : 'WHERE ' . implode(' AND ', $w), $p];
    }

    // ------------------------------------------------------------------------------------------------ numbering and slugs

    /** Suggested number for a new issue: highest used number + 1 (1 when none). */
    public static function nextIssueNumber(): int
    {
        return min(self::MAX_ISSUE_NUMBER, (int) Database::value('SELECT COALESCE(MAX(issue_number), 0) + 1 FROM newsletters', [], 1));
    }

    /** The issue already using this number (id, title), or null. */
    public static function issueNumberTaken(int $number, ?int $ignoreId = null): ?array
    {
        return Database::one(
            'SELECT id, title FROM newsletters WHERE issue_number = ?' . ($ignoreId !== null ? ' AND id <> ?' : '') . ' LIMIT 1',
            $ignoreId !== null ? [$number, $ignoreId] : [$number]
        );
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Slug::unique('newsletters', $title, $ignoreId, 'slug', [], 'issue');
        while (in_array($slug, self::RESERVED_SLUGS, true)) {
            $slug = Slug::unique('newsletters', $slug . '-issue', $ignoreId, 'slug', [], 'issue');
        }
        return $slug;
    }

    // ------------------------------------------------------------------------------------------------ create / save

    /**
     * Create a DRAFT. Input: title* (<= 190), issue_number (default: next), issue_date (default: today), summary, cover_image, cover_alt,
     * seo_title, seo_description, content (array|JSON; default: an empty issue), slug (default: from the title).
     *
     * @return array{ok:bool,errors?:array<string,string>,newsletter?:array,warnings?:array<string,string>}
     */
    public static function create(array $input, ?int $userId = null): array
    {
        $d = self::cleanDetails($input, null, true);
        if ($d['errors'] !== []) {
            return ['ok' => false, 'errors' => $d['errors']];
        }
        $norm = NewsletterContent::normalise($input['content'] ?? null, ['strict' => true]);
        if (!$norm['ok']) {
            return ['ok' => false, 'errors' => $norm['errors']];
        }
        $row = $d['values'];
        $row['slug'] = $d['values']['slug'] ?? self::uniqueSlug((string) $row['title']);
        $row['content'] = NewsletterContent::encode($norm['content']);
        $row['status'] = 'draft';
        $row['version'] = 1;
        $row['kind'] = $d['values']['kind'] ?? 'online';
        $row['created_by'] = $userId;
        $row['updated_by'] = $userId;
        $id = 0;
        $autoNumber = !array_key_exists('issue_number', $input) || trim((string) (is_scalar($input['issue_number']) ? $input['issue_number'] : '')) === '';
        for ($try = 0; $try < 5; $try++) {
            try {
                $id = Newsletter::create($row);
                break;
            } catch (\PDOException $e) {
                if ((string) $e->getCode() !== '23000' || $try === 4) {
                    throw $e;
                }
                if (str_contains($e->getMessage(), 'ux_newsletters_number')) {
                    if (!$autoNumber) {
                        return ['ok' => false, 'errors' => ['issue_number' => 'That issue number was just taken by another issue. Choose another one.']];
                    }
                    $next = self::nextIssueNumber();                            // two creators picked the same number at once: take the next one
                    $row['issue_number'] = self::issueNumberTaken($next) === null ? $next : null;
                } else {
                    $row['slug'] = self::uniqueSlug((string) $row['title']);    // two creators picked the same slug at once
                }
            }
        }
        self::snapshot($id, 1, (string) $row['title'], (string) $row['content'], 'save', $userId);
        self::flushRelease();
        AuditLog::record('newsletter.create', 'newsletter', $id, 'Created newsletter issue', ['issue_number' => $row['issue_number'] ?? null]);
        return ['ok' => true, 'newsletter' => Newsletter::find($id) ?? [], 'warnings' => $norm['warnings']];
    }

    /**
     * Save details and/or content with optimistic locking. Only the keys present in $input change.
     *
     * $input: title, slug (only before the issue was ever public), issue_number, issue_date, summary, cover_image, cover_alt, seo_title,
     *         seo_description, content (array|JSON string).
     * $opts:  autosave (bool) - lenient content validation (bad values cleared + warnings) and revisions of the same author within five
     *         minutes are merged.
     *
     * @return array{ok:bool,conflict?:bool,not_found?:bool,errors?:array<string,string>,warnings?:array<string,string>,changed?:bool,
     *               newsletter?:array,current?:array}
     */
    public static function save(int $id, array $input, int $expectedVersion, ?int $userId = null, array $opts = []): array
    {
        $autosave = (bool) ($opts['autosave'] ?? false);
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'not_found' => true];
        }
        if ((int) $row['version'] !== $expectedVersion) {
            return self::conflict($row);
        }
        // every subscriber of one campaign must get the same message: the e-mail is rendered from the live row on every batch
        if (in_array($row['email_status'], Newsletter::EMAIL_ACTIVE, true)) {
            return ['ok' => false, 'locked' => true, 'error' => 'The e-mail campaign of this issue is running (or paused). Cancel the campaign before changing the issue, so every subscriber receives the same message.'];
        }
        // autosave belongs to drafts: a stale editor tab must never push half-typed text into an issue that was published meanwhile
        if ($autosave && empty($opts['restore']) && !in_array((string) $row['status'], ['draft', 'archived'], true)) {
            return ['ok' => false, 'status_changed' => true, 'error' => 'This issue is no longer a draft (it was published or scheduled from another page), so nothing was saved automatically. Reload the page to continue.'];
        }

        $d = self::cleanDetails($input, $row, false);
        $errors = $d['errors'];
        $warnings = [];
        $update = $d['values'];
        $contentJson = (string) $row['content'];
        if (array_key_exists('content', $input)) {
            $norm = NewsletterContent::normalise($input['content'], ['strict' => !$autosave]);
            if (!$norm['ok']) {
                $errors += $norm['errors'];
            } else {
                $contentJson = NewsletterContent::encode($norm['content']);
                $warnings = $norm['warnings'];
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $changed = $contentJson !== (string) $row['content'];
        foreach ($update as $k => $v) {
            if ((string) ($row[$k] ?? '') !== (string) ($v ?? '')) {
                $changed = true;
            }
        }
        if (!$changed) {
            return ['ok' => true, 'changed' => false, 'newsletter' => $row, 'warnings' => $warnings];
        }
        // a public (or scheduled) issue must stay complete: readiness is checked on every save, not only when publishing
        if (in_array((string) $row['status'], ['published', 'scheduled'], true)) {
            $ready = NewsletterContent::readiness(NewsletterContent::decode($contentJson), array_merge($row, $update));
            if ($ready['blocking'] !== []) {
                return ['ok' => false, 'not_ready' => true, 'errors' => $ready['blocking']];
            }
        }
        $imagesBefore = self::imagePaths($row);

        $update['content'] = $contentJson;
        $update['updated_by'] = $userId;
        $newVersion = $expectedVersion + 1;
        $assign = [];
        $params = [];
        foreach ($update as $col => $val) {
            $assign[] = Database::ident($col) . ' = ?';
            $params[] = $val;
        }
        $assign[] = '`version` = ?';
        $params[] = $newVersion;
        $params[] = $id;
        $params[] = $expectedVersion;
        try {
            $affected = Database::transaction(static function () use ($assign, $params, $id, $newVersion, $update, $row, $userId, $autosave): int {
                $n = Database::execute('UPDATE newsletters SET ' . implode(', ', $assign) . ' WHERE id = ? AND version = ?', $params);
                if ($n === 1) {
                    self::snapshot($id, $newVersion, (string) ($update['title'] ?? $row['title']), (string) $update['content'], $autosave ? 'autosave' : 'save', $userId);
                }
                return $n;
            });
        } catch (\PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                if (str_contains($e->getMessage(), 'ux_newsletters_number')) {
                    return ['ok' => false, 'errors' => ['issue_number' => 'That issue number was just taken by another issue. Choose another one.']];
                }
                return ['ok' => false, 'errors' => ['slug' => 'That web address is already used by another issue.']];
            }
            throw $e;
        }
        if ($affected !== 1) {
            return self::conflict(Newsletter::find($id) ?? $row);
        }
        $fresh = Newsletter::find($id) ?? [];
        // pictures that left the issue (replaced, removed, cleared cover) are deleted when nothing else, revisions included, still uses them
        self::$release = array_merge(self::$release, array_diff($imagesBefore, self::imagePaths($fresh)));
        self::flushRelease();
        return ['ok' => true, 'changed' => true, 'newsletter' => $fresh, 'warnings' => $warnings];
    }

    /** @return array{ok:false,conflict:true,current:array<string,mixed>,newsletter:array} */
    private static function conflict(array $row): array
    {
        $by = '';
        if (!empty($row['updated_by'])) {
            $by = (string) Database::value('SELECT name FROM users WHERE id = ?', [(int) $row['updated_by']], '');
        }
        return [
            'ok' => false, 'conflict' => true, 'newsletter' => $row,
            'current' => ['version' => (int) $row['version'], 'updated_at' => (string) $row['updated_at'], 'updated_by' => $by],
        ];
    }

    /**
     * Validate the detail fields present in $input.
     *
     * @return array{values:array<string,mixed>,errors:array<string,string>}
     */
    private static function cleanDetails(array $in, ?array $row, bool $creating): array
    {
        $v = [];
        $e = [];
        $has = static fn(string $k): bool => array_key_exists($k, $in);
        $str = static fn(string $k): string => is_scalar($in[$k] ?? null) ? trim((string) $in[$k]) : '';

        // the format is chosen once, when the issue is created
        if ($creating) {
            $kind = $str('kind') === '' ? 'online' : strtolower($str('kind'));
            if (!in_array($kind, self::KINDS, true)) {
                $e['kind'] = 'Choose how this issue is made: written online, or an uploaded PDF.';
            } else {
                $v['kind'] = $kind;
            }
        } elseif ($has('kind') && $row !== null && strtolower($str('kind')) !== (string) ($row['kind'] ?? 'online')) {
            $e['kind'] = 'The format of an issue cannot be changed after it was created. Create a new issue in the other format instead.';
        }
        if ($has('title') || $creating) {
            $title = Sanitizer::text($str('title'));
            if ($title === '') {
                $e['title'] = 'Give the issue a title.';
            } elseif (mb_strlen($title) > 190) {
                $e['title'] = 'The title is too long (at most 190 characters).';
            }
            $v['title'] = $title;
        }
        if ($has('issue_number') || $creating) {
            $raw = $str('issue_number');
            if ($raw === '' && $creating) {
                $v['issue_number'] = self::nextIssueNumber();
            } elseif ($raw === '') {
                $v['issue_number'] = null;
            } elseif (!ctype_digit($raw) || (int) $raw < 1 || (int) $raw > 99999) {
                $e['issue_number'] = 'The issue number must be a whole number from 1 to 99999.';
            } elseif (($t = self::issueNumberTaken((int) $raw, $row['id'] ?? null)) !== null) {
                $e['issue_number'] = 'Issue No. ' . (int) $raw . ' is already used by "' . $t['title'] . '".';
            } else {
                $v['issue_number'] = (int) $raw;
            }
        }
        if ($has('issue_date') || $creating) {
            $raw = $str('issue_date');
            $date = $raw === '' && $creating ? date('Y-m-d') : $raw;
            $dt = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if ($dt === false || $dt->format('Y-m-d') !== $date || (int) $dt->format('Y') < 2000 || (int) $dt->format('Y') > 2100) {
                $e['issue_date'] = 'Choose a real issue date.';
            } else {
                $v['issue_date'] = $date;
            }
        }
        if ($has('summary')) {
            $s = Sanitizer::text($str('summary'));
            if (mb_strlen($s) > 600) {
                $e['summary'] = 'The summary is too long (at most 600 characters).';
            }
            $v['summary'] = $s === '' ? null : $s;
        }
        foreach (['cover_alt' => 190, 'seo_title' => 190, 'seo_description' => 300] as $k => $max) {
            if ($has($k)) {
                $s = Sanitizer::text($str($k));
                if (mb_strlen($s) > $max) {
                    $e[$k] = 'This is too long (at most ' . $max . ' characters).';
                }
                $v[$k] = $s === '' ? null : $s;
            }
        }
        if ($has('cover_image')) {
            $p = $str('cover_image');
            if ($p === '') {
                $v['cover_image'] = null;
            } elseif (!NewsletterContent::validImagePath($p) || !NewsletterContent::imageExists($p)) {
                $e['cover_image'] = 'That cover image could not be found. Upload it again.';
            } else {
                $v['cover_image'] = $p;
            }
        }
        if ($has('slug') && !($creating && $str('slug') === '')) {
            $slug = strtolower($str('slug'));
            $current = (string) ($row['slug'] ?? '');
            if ($slug !== $current) {
                if ($row !== null && !empty($row['published_at'])) {
                    $e['slug'] = 'The web address cannot change after the issue has been published.';
                } elseif (preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) !== 1 || strlen($slug) > 160) {
                    $e['slug'] = 'Use lower-case letters, numbers and hyphens only.';
                } elseif (in_array($slug, self::RESERVED_SLUGS, true)) {
                    $e['slug'] = 'That web address is reserved.';
                } elseif (Database::value('SELECT COUNT(*) FROM newsletters WHERE slug = ? AND id <> ?', [$slug, (int) ($row['id'] ?? 0)], 0) > 0) {
                    $e['slug'] = 'That web address is already used by another issue.';
                } else {
                    $v['slug'] = $slug;
                }
            }
        }
        return ['values' => $v, 'errors' => $e];
    }

    // ------------------------------------------------------------------------------------------------ status workflow

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /** @return string[] */
    public static function allowedTransitions(string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }

    /**
     * Move an issue to another status.
     *   $opts: user_id; publish_at (required for 'scheduled': a future time, any format parseLocalDateTime() accepts).
     * Publishing / scheduling needs the issue to be complete (NewsletterContent::readiness blocking list). A published issue whose e-mail
     * campaign is queued / sending / paused cannot leave the published state.
     *
     * @return array{ok:bool,error?:string,errors?:array<string,string>,warnings?:array<string,string>,newsletter?:array}
     */
    public static function transition(int $id, string $to, array $opts = []): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'error' => 'That issue no longer exists.'];
        }
        $from = (string) $row['status'];
        $userId = isset($opts['user_id']) ? (int) $opts['user_id'] : null;
        // a scheduled issue whose time has passed is already live: treat it as published
        if ($from === 'scheduled' && self::isVisible($row)) {
            self::resolveScheduled(true);
            $row = Newsletter::find($id) ?? $row;
            $from = (string) $row['status'];
        }
        if ($from === $to || !self::canTransition($from, $to)) {
            if ($from === $to && $to === 'scheduled') {
                return ['ok' => false, 'error' => 'This issue is already scheduled. Cancel the schedule first, then schedule it again for the new time.'];
            }
            $fromWord = strtolower(self::STATUS_LABELS[$from] ?? $from);
            return ['ok' => false, 'error' => (in_array($fromWord[0], ['a', 'e', 'i', 'o', 'u'], true) ? 'An ' : 'A ') . $fromWord . ' issue cannot be changed to ' . strtolower(self::STATUS_LABELS[$to] ?? $to) . '.'];
        }
        if (in_array($row['email_status'], Newsletter::EMAIL_ACTIVE, true) && $from === 'published') {
            return ['ok' => false, 'error' => 'The e-mail campaign of this issue is still running. Cancel it first.'];
        }

        $set = ['status' => $to, 'updated_by' => $userId];
        $warnings = [];
        if ($to === 'published' || $to === 'scheduled') {
            $ready = NewsletterContent::readiness(NewsletterContent::decode((string) $row['content']), $row);
            if ($ready['blocking'] !== []) {
                return ['ok' => false, 'error' => 'The issue is not complete yet.', 'errors' => $ready['blocking'], 'warnings' => $ready['warnings']];
            }
            $warnings = $ready['warnings'];
        }
        if ($to === 'published') {
            $set['publish_at'] = null;
            $set['published_at'] = $row['published_at'] ?: self::now();
        } elseif ($to === 'scheduled') {
            $at = self::parseLocalDateTime((string) ($opts['publish_at'] ?? ''));
            if ($at === null) {
                return ['ok' => false, 'error' => 'Choose a date and time.', 'errors' => ['publish_at' => 'Choose a date and time.']];
            }
            if ($at <= self::now()) {
                return ['ok' => false, 'error' => 'Choose a time in the future, or publish now.', 'errors' => ['publish_at' => 'Choose a time in the future, or publish now.']];
            }
            $set['publish_at'] = $at;
        } elseif ($to === 'draft') {
            $set['publish_at'] = null;
        } elseif ($to === 'archived') {
            $set['publish_at'] = null;
        }

        $n = Database::update('newsletters', $set, 'id = ? AND status = ?', [$id, $from]);
        if ($n !== 1) {
            return ['ok' => false, 'error' => 'Somebody else just changed this issue. Reload the page and try again.'];
        }
        if ($to === 'published') {
            self::snapshot($id, (int) $row['version'], (string) $row['title'], (string) $row['content'], 'publish', $userId);
            self::flushRelease();
        }
        $audit = ['published' => 'newsletter.publish', 'scheduled' => 'newsletter.schedule', 'draft' => 'newsletter.unpublish', 'archived' => 'newsletter.archive'][$to];
        AuditLog::record($audit, 'newsletter', $id, ucfirst(self::STATUS_LABELS[$to] ?? $to) . ' newsletter issue', ['from' => $from, 'to' => $to]);
        return ['ok' => true, 'newsletter' => Newsletter::find($id) ?? [], 'warnings' => $warnings];
    }

    public static function publish(int $id, ?int $userId = null): array
    {
        return self::transition($id, 'published', ['user_id' => $userId]);
    }

    public static function schedule(int $id, string $publishAt, ?int $userId = null): array
    {
        return self::transition($id, 'scheduled', ['user_id' => $userId, 'publish_at' => $publishAt]);
    }

    /** Back to draft (also cancels a schedule). */
    public static function unpublish(int $id, ?int $userId = null): array
    {
        return self::transition($id, 'draft', ['user_id' => $userId]);
    }

    public static function archiveIssue(int $id, ?int $userId = null): array
    {
        return self::transition($id, 'archived', ['user_id' => $userId]);
    }

    // ------------------------------------------------------------------------------------------------ duplicate / delete

    /** Copy an issue as a new DRAFT ("Title (copy)", next number, today's date, fresh slug, no e-mail history). */
    public static function duplicate(int $id, ?int $userId = null): array
    {
        $src = Newsletter::find($id);
        if ($src === null) {
            return ['ok' => false, 'error' => 'That issue no longer exists.'];
        }
        $title = mb_substr(trim((string) $src['title']), 0, 180) . ' (copy)';
        $res = self::create([
            'title' => $title, 'summary' => $src['summary'], 'cover_image' => $src['cover_image'], 'cover_alt' => $src['cover_alt'],
            'seo_title' => $src['seo_title'], 'seo_description' => $src['seo_description'], 'content' => (string) $src['content'],
            'kind' => (string) ($src['kind'] ?? 'online'),
        ], $userId);
        if ($res['ok']) {
            // the PDF is copied (its own file), so deleting or replacing one issue's PDF never touches the other
            $copy = NewsletterPdf::copyColumns($src);
            if ($copy !== []) {
                Database::update('newsletters', $copy, 'id = ?', [(int) $res['newsletter']['id']]);
                $res['newsletter'] = Newsletter::find((int) $res['newsletter']['id']) ?? $res['newsletter'];
            }
            AuditLog::record('newsletter.duplicate', 'newsletter', (int) $res['newsletter']['id'], 'Duplicated newsletter issue', ['source' => $id]);
        }
        return $res;
    }

    /**
     * Delete an issue with its revisions and delivery records. Refused while an e-mail campaign is queued / sending / paused.
     * (Who may delete a PUBLISHED issue is a controller decision: admin only.)
     */
    public static function delete(int $id): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'error' => 'That issue no longer exists.'];
        }
        if (in_array($row['email_status'], Newsletter::EMAIL_ACTIVE, true)) {
            return ['ok' => false, 'error' => 'The e-mail campaign of this issue is still running. Cancel it first.'];
        }
        $paths = self::imagePaths($row);
        foreach (Database::all('SELECT content FROM newsletter_revisions WHERE newsletter_id = ?', [$id]) as $r) {
            $paths = array_merge($paths, self::uploadPaths((string) $r['content']));
        }
        Newsletter::delete($id);
        self::$release = array_merge(self::$release, $paths);
        self::flushRelease();
        NewsletterPdf::releaseFile((string) ($row['pdf_path'] ?? ''), $id);
        AuditLog::record('newsletter.delete', 'newsletter', $id, 'Deleted newsletter issue', ['status' => $row['status']]);
        return ['ok' => true];
    }

    // ------------------------------------------------------------------------------------------------ revisions

    /**
     * Store a content snapshot and keep the latest NewsletterRevision::KEEP per issue. A snapshot identical to the newest one only relabels it;
     * an autosave directly after an autosave of the same author (within five minutes) replaces it, so 30 revisions cover real work.
     */
    public static function snapshot(int $newsletterId, int $version, string $title, string $contentJson, string $source = 'save', ?int $userId = null): void
    {
        try {
            $last = Database::one('SELECT id, content, source, saved_by, created_at FROM newsletter_revisions WHERE newsletter_id = ? ORDER BY id DESC LIMIT 1', [$newsletterId]);
            if ($last !== null && (string) $last['content'] === $contentJson) {
                if (in_array($source, ['publish', 'restore'], true) && $last['source'] !== $source) {
                    Database::update('newsletter_revisions', ['source' => $source, 'version' => $version, 'title' => $title], 'id = ?', [(int) $last['id']]);
                }
                return;
            }
            if ($last !== null && $source === 'autosave' && $last['source'] === 'autosave' && (int) ($last['saved_by'] ?? 0) === (int) $userId
                && strtotime((string) $last['created_at']) >= time() - 300) {
                self::$release = array_merge(self::$release, self::uploadPaths((string) $last['content']));
                Database::update('newsletter_revisions', ['version' => $version, 'title' => $title, 'content' => $contentJson, 'created_at' => self::now()], 'id = ?', [(int) $last['id']]);
                return;
            }
            Database::insert('newsletter_revisions', [
                'newsletter_id' => $newsletterId, 'version' => $version, 'title' => $title, 'content' => $contentJson,
                'source' => in_array($source, NewsletterRevision::SOURCES, true) ? $source : 'save', 'saved_by' => $userId,
            ]);
            $keep = 'SELECT id FROM (SELECT id FROM newsletter_revisions WHERE newsletter_id = ? ORDER BY id DESC LIMIT ' . (int) NewsletterRevision::KEEP . ') keep_rows';
            foreach (Database::all("SELECT content FROM newsletter_revisions WHERE newsletter_id = ? AND id NOT IN ({$keep})", [$newsletterId, $newsletterId]) as $old) {
                self::$release = array_merge(self::$release, self::uploadPaths((string) $old['content']));
            }
            Database::execute("DELETE FROM newsletter_revisions WHERE newsletter_id = ? AND id NOT IN ({$keep})", [$newsletterId, $newsletterId]);
        } catch (\Throwable $e) {
            Log::warning('Newsletter: snapshot failed - ' . $e->getMessage());
        }
    }

    /**
     * The revision list for the drawer, newest first (no content). Each row: id, version, title, source, saved_by (user id), saved_by_name, created_at, bytes.
     * @return list<array<string,mixed>>
     */
    public static function revisions(int $newsletterId): array
    {
        return Database::all(
            'SELECT r.id, r.version, r.title, r.source, r.saved_by, u.name AS saved_by_name, r.created_at, LENGTH(r.content) AS bytes
               FROM newsletter_revisions r LEFT JOIN users u ON u.id = r.saved_by
              WHERE r.newsletter_id = ? ORDER BY r.id DESC LIMIT ' . (int) NewsletterRevision::KEEP,
            [$newsletterId]
        );
    }

    /** One revision with decoded content, or null. */
    public static function revision(int $newsletterId, int $revisionId): ?array
    {
        $r = Database::one('SELECT * FROM newsletter_revisions WHERE id = ? AND newsletter_id = ?', [$revisionId, $newsletterId]);
        if ($r === null) {
            return null;
        }
        $r['content_data'] = NewsletterContent::decode((string) $r['content']);
        return $r;
    }

    /** Make a revision's content the current content (a normal save: bumps the version, optimistic locking applies). */
    public static function restoreRevision(int $newsletterId, int $revisionId, int $expectedVersion, ?int $userId = null): array
    {
        $rev = self::revision($newsletterId, $revisionId);
        if ($rev === null) {
            return ['ok' => false, 'not_found' => true, 'errors' => ['revision' => 'That version no longer exists.']];
        }
        $res = self::save($newsletterId, ['content' => $rev['content_data']], $expectedVersion, $userId, ['autosave' => true, 'restore' => true]);
        if ($res['ok']) {
            $row = Newsletter::find($newsletterId);
            if ($row !== null) {
                self::snapshot($newsletterId, (int) $row['version'], (string) $row['title'], (string) $row['content'], 'restore', $userId);
                self::flushRelease();
            }
            AuditLog::record('newsletter.restore', 'newsletter', $newsletterId, 'Restored an earlier version', ['revision' => $revisionId]);
        }
        return $res;
    }

    /**
     * "Diff-lite" between two contents (old -> new) for the revisions drawer: a short list of readable changes.
     * Each item: kind (added|removed|changed), where (human label), from, to (clipped plain text, '' when not applicable).
     *
     * @return list<array{kind:string,where:string,from:string,to:string}>
     */
    public static function diffLite(array $old, array $new): array
    {
        $old = NewsletterContent::decode($old);
        $new = NewsletterContent::decode($new);
        $out = [];
        $add = static function (string $kind, string $where, string $from = '', string $to = '') use (&$out): void {
            $out[] = ['kind' => $kind, 'where' => $where, 'from' => NewsletterContent::clip($from, 120), 'to' => NewsletterContent::clip($to, 120)];
        };
        $same = static fn(string $a, string $b): bool => trim($a) === trim($b);

        $fo = $old['featured'];
        $fn = $new['featured'];
        if (!$same($fo['heading'], $fn['heading'])) {
            $add('changed', 'Featured article heading', $fo['heading'], $fn['heading']);
        }
        $bo = NewsletterContent::htmlToText($fo['body_html'], false);
        $bn = NewsletterContent::htmlToText($fn['body_html'], false);
        if (!$same($bo, $bn)) {
            $add('changed', 'Featured article text', $bo, $bn);
        }
        if ($fo['image']['path'] !== $fn['image']['path']) {
            $add('changed', 'Featured article image');
        }
        $label = static function (array $b, int $pos): string {
            $name = ucfirst((string) $b['type']) . ' block ' . ($pos + 1);
            $hint = (string) ($b['heading'] ?? $b['button']['label'] ?? $b['cite'] ?? '');
            return $hint !== '' ? $name . ' ("' . NewsletterContent::clip($hint, 40) . '")' : $name;
        };
        $oldBy = [];
        foreach ($old['blocks'] as $i => $b) {
            $oldBy[$b['id']] = [$b, $i];
        }
        $seen = [];
        foreach ($new['blocks'] as $i => $b) {
            $seen[$b['id']] = true;
            if (!isset($oldBy[$b['id']])) {
                $add('added', $label($b, $i));
                continue;
            }
            [$ob, $oi] = $oldBy[$b['id']];
            if ($ob !== $b) {
                $ot = NewsletterContent::plainText(['blocks' => [$ob]]);
                $nt = NewsletterContent::plainText(['blocks' => [$b]]);
                $moved = $oi !== $i && $ot === $nt;
                $add('changed', $label($b, $i) . ($moved ? ' (moved)' : ''), $moved ? '' : $ot, $moved ? '' : $nt);
            } elseif ($oi !== $i) {
                $add('changed', $label($b, $i) . ' (moved)');
            }
        }
        foreach ($old['blocks'] as $i => $b) {
            if (!isset($seen[$b['id']])) {
                $add('removed', $label($b, $i));
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ uploaded pictures

    /** Uploads waiting to be released once the current operation has committed (see flushRelease()). @var list<string> */
    private static array $release = [];

    /**
     * Every uploaded picture ("YYYY/MM/file.ext") a JSON text mentions: image fields and pictures inside rich text alike.
     * @return list<string>
     */
    public static function uploadPaths(string $json): array
    {
        $json = str_replace('\\/', '/', $json);
        if (preg_match_all('~(?<![A-Za-z0-9_\-])(\d{4}/\d{2}/[A-Za-z0-9_\-]+\.(?:jpe?g|png|gif|webp))~i', $json, $m) < 1) {
            return [];
        }
        return array_values(array_unique($m[1]));
    }

    /**
     * The uploaded pictures an issue row uses: the cover plus everything in its content.
     * @return list<string>
     */
    public static function imagePaths(array $row): array
    {
        $paths = self::uploadPaths((string) ($row['content'] ?? ''));
        $cover = trim((string) ($row['cover_image'] ?? ''));
        if ($cover !== '') {
            $paths[] = $cover;
        }
        return array_values(array_unique($paths));
    }

    /**
     * Delete the pictures queued by save / delete / the revision pruning, but only those nothing references any more (ImageCleanup keeps a
     * file that the issue, a restorable revision or any other page still uses). Called AFTER the database change committed.
     */
    private static function flushRelease(): void
    {
        $paths = array_values(array_unique(self::$release));
        self::$release = [];
        if ($paths === []) {
            return;
        }
        try {
            ImageCleanup::releaseMany($paths);
        } catch (\Throwable $e) {
            Log::warning('Newsletter: releasing pictures failed - ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------------------------------------ "Is it on the website?" (admin status box + list warnings)

    /** Is the Newsletter page itself switched on? When it is not, EVERY issue answers 404 on the public site. */
    public static function pageVisible(): bool
    {
        return SitePages::isPublished('newsletter');
    }

    /**
     * Plain-language answer to "can visitors see this issue right now, and if not why not?", for the admin status box on an issue, the list
     * warnings and the API. Same rules as the public site (visibleSql / isVisible), nothing is cached.
     *
     * $opts['content'] (bool, default true): also check that the issue is complete (needs the `content` column; the list passes false for live rows).
     *
     * @return array{state:string,visible:bool,label:string,tone:string,detail:string,reasons:list<string>,url:string,preview_url:string,is_latest:bool,in_menu:bool,page_visible:bool}
     *   state: live | scheduled | draft | unpublished | archived        visible: visitors can open it right now
     *   url: the public address (always set, even when not visible, so the admin can see what it will be); preview_url: the admin preview
     */
    public static function liveStatus(array $row, array $opts = []): array
    {
        $status = (string) ($row['status'] ?? 'draft');
        $due = $status === 'scheduled' && self::isVisible($row);
        $state = match (true) {
            $status === 'published', $due => 'live',
            $status === 'scheduled'       => 'scheduled',
            $status === 'archived'        => 'archived',
            default                       => !empty($row['published_at']) ? 'unpublished' : 'draft',
        };
        $pageOn = self::pageVisible();
        $reasons = [];
        $detail = '';
        $label = '';
        $tone = 'neutral';
        $when = '';
        if (!empty($row['publish_at'])) {
            $d = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $row['publish_at']);
            $when = $d !== false ? $d->format('j M Y, H:i') : (string) $row['publish_at'];
        }
        switch ($state) {
            case 'live':
                $label = 'Live on the website';
                $tone = 'success';
                $detail = 'Visitors can read this issue now.';
                break;
            case 'scheduled':
                $label = 'Scheduled for ' . $when;
                $tone = 'info';
                $detail = 'It is not visible yet. It appears on the website by itself at that time (Uganda time); nothing else is needed.';
                $reasons[] = 'Scheduled for the future: visitors cannot see it before ' . $when . '.';
                break;
            case 'unpublished':
                $label = 'Unpublished — not visible on the website';
                $tone = 'warning';
                $detail = 'This issue was live once and has been taken down. Publish it again to put it back.';
                $reasons[] = 'Unpublished (back to draft): visitors get "page not found".';
                break;
            case 'archived':
                $label = 'Archived — not visible on the website';
                $tone = 'neutral';
                $detail = 'Archived issues are kept for you but are not shown to visitors.';
                $reasons[] = 'Archived: visitors cannot see it. Move it back to draft to edit and publish it again.';
                break;
            default:
                $label = 'Draft — not visible on the website';
                $tone = 'warning';
                $detail = 'Only you can see a draft. Click "Publish now" when it is ready.';
                $reasons[] = 'Draft: not published yet.';
        }
        if (($opts['content'] ?? true) !== false && $state !== 'archived') {
            $content = array_key_exists('content', $row) ? (string) $row['content'] : (string) Database::value('SELECT content FROM newsletters WHERE id = ?', [(int) ($row['id'] ?? 0)], '');
            $ready = NewsletterContent::readiness(NewsletterContent::decode($content), $row);
            foreach ($ready['blocking'] as $msg) {
                $reasons[] = ($state === 'live' ? 'Needs attention: ' : 'Missing before it can be published: ') . $msg;
            }
        }
        if ($state === 'live' && !$pageOn) {
            $label = 'Published — but the Newsletter page is switched off';
            $tone = 'warning';
            $detail = 'The issue is published, yet visitors still get "page not found" because the Newsletter page is switched off. Switch it on in Admin > Pages.';
        }
        if (!$pageOn) {
            $reasons[] = 'The Newsletter page is switched off (Admin > Pages): no issue can be seen by visitors.';
        }
        if (in_array((string) ($row['email_status'] ?? 'none'), Newsletter::EMAIL_ACTIVE, true)) {
            $reasons[] = 'An e-mail campaign is running for this issue, so it cannot be changed or taken down until it is cancelled.';
        }
        $visible = $state === 'live' && $pageOn;
        $latestId = 0;
        if ($visible) {
            [$sql, $params] = self::visibleSql('n');
            $latestId = (int) Database::value("SELECT n.id FROM newsletters n WHERE {$sql} ORDER BY " . self::PUBLIC_ORDER . ' LIMIT 1', $params, 0);
        }
        $nav = SitePages::get('newsletter');
        return [
            'state'        => $state,
            'visible'      => $visible,
            'label'        => $label,
            'tone'         => $tone,
            'detail'       => $detail,
            'reasons'      => array_values(array_unique($reasons)),
            'url'          => self::url($row + ['slug' => '']),
            'preview_url'  => url('admin/newsletters/' . (int) ($row['id'] ?? 0) . '/preview'),
            'is_latest'    => $visible && $latestId === (int) ($row['id'] ?? 0),
            'in_menu'      => $nav !== null && !empty($nav['show_in_nav']) && !empty($nav['is_published']),
            'page_visible' => $pageOn,
        ];
    }

    /**
     * Add a `live` key (liveStatus()) to every row of an admin list. Rows that are not live get their completeness checked too, which needs the
     * `content` column: it is fetched for those rows only, in one query.
     *
     * @param list<array<string,mixed>> $rows rows with LIST_COLUMNS
     * @return list<array<string,mixed>>
     */
    public static function annotate(array $rows): array
    {
        $needContent = [];
        foreach ($rows as $r) {
            if (in_array((string) ($r['status'] ?? ''), ['draft', 'scheduled'], true) && !self::isVisible($r)) {
                $needContent[] = (int) $r['id'];
            }
        }
        $contents = [];
        $paths = [];
        if ($needContent !== []) {
            // pdf_path is fetched here (list rows carry only has_pdf) because readiness() of a PDF issue looks at it; it is merged into a COPY of the
            // row for liveStatus() only, so pdfInfo() still treats the row as a list row
            foreach (Database::all('SELECT id, content, pdf_path FROM newsletters WHERE id IN (' . implode(',', array_fill(0, count($needContent), '?')) . ')', $needContent) as $c) {
                $contents[(int) $c['id']] = (string) $c['content'];
                $paths[(int) $c['id']] = (string) ($c['pdf_path'] ?? '');
            }
        }
        foreach ($rows as &$r) {
            $id = (int) $r['id'];
            $withContent = isset($contents[$id]);
            $r['live'] = self::liveStatus($withContent ? $r + ['content' => $contents[$id], 'pdf_path' => $paths[$id]] : $r, ['content' => $withContent]);
        }
        unset($r);
        return $rows;
    }

    // ------------------------------------------------------------------------------------------------ e-mail helpers (shared by the mailing agent)

    /** Default e-mail subject: the setting newsletter_default_subject_prefix + the issue title. */
    public static function defaultSubject(array $issue): string
    {
        return trim(trim((string) setting('newsletter_default_subject_prefix', '')) . ' ' . (string) $issue['title']);
    }
}
