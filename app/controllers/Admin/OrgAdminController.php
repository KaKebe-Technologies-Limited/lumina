<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Content;
use App\Core\Database;
use App\Core\Pagination;

/**
 * Shared plumbing for the ORGANISATION admin screens (team, partners, documents, events, registrations, stories).
 * OWNER: B8 "org-admin". Not a route target itself; every method is protected.
 *
 * What it gives the concrete controllers (so each list behaves identically):
 *   - listState()/orderSql()/searchSql()/whereSql()/paged()  search + whitelisted sort + pagination for list screens
 *   - postedIds()/setFlag()/plural()                          bulk actions on `ids[]`
 *   - returnUrl()                                             safe "back to the list I came from" redirect (_back field)
 *   - nextSortOrder()/applyOrder()/moveWithin()               manual ordering (team, partners)
 *   - webAddress()/clean()/dbDateTime()/imageWillExist()      input normalisation
 */
abstract class OrgAdminController extends AdminController
{
    /** Base admin path of the area without leading slash, e.g. 'admin/team'. Used to validate the "_back" return address. */
    abstract protected function area(): string;

    // ------------------------------------------------------------------ list screens

    /**
     * Search term + whitelisted sort column/direction from the query string.
     * @param array<string,string[]> $sortable sort key => list of SQL expressions (trusted constants, never user input)
     * @return array{q:string,sort:string,dir:string}
     */
    protected function listState(array $sortable, string $defaultSort, string $defaultDir = 'asc'): array
    {
        $q = mb_substr($this->request->string('q'), 0, 100);
        $sort = $this->request->string('sort');
        $known = isset($sortable[$sort]);
        if (!$known) {
            $sort = $defaultSort;
        }
        $dir = strtolower($this->request->string('dir'));
        if (!$known || !in_array($dir, ['asc', 'desc'], true)) {
            $dir = $defaultDir;
        }
        return ['q' => $q, 'sort' => $sort, 'dir' => $dir];
    }

    /** @param array<string,string[]> $sortable */
    protected function orderSql(array $sortable, string $sort, string $dir): string
    {
        $dir = $dir === 'desc' ? 'DESC' : 'ASC';
        $parts = [];
        foreach ($sortable[$sort] ?? ['id'] as $expr) {
            $parts[] = $expr . ' ' . $dir;
        }
        $parts[] = 'id ' . $dir;                       // stable tie-break
        return implode(', ', $parts);
    }

    /**
     * LIKE search over trusted column expressions.
     * @param string[] $columns
     * @return array{0:string,1:array<int,string>} [sql fragment ('' when no term), params]
     */
    protected function searchSql(array $columns, string $q): array
    {
        if ($q === '' || $columns === []) {
            return ['', []];
        }
        $like = '%' . addcslashes($q, '\\%_') . '%';
        $parts = array_map(static fn(string $c): string => $c . ' LIKE ?', $columns);
        return ['(' . implode(' OR ', $parts) . ')', array_fill(0, count($columns), $like)];
    }

    /** @param string[] $parts */
    protected function whereSql(array $parts): string
    {
        return $parts === [] ? '' : ' WHERE ' . implode(' AND ', $parts);
    }

    /**
     * Count + fetch one page. $from is "table WHERE ..." (trusted SQL built from constants + placeholders).
     * @param array<int,mixed> $params
     */
    protected function paged(string $from, array $params, string $select, string $order): Pagination
    {
        $total = (int) Database::value('SELECT COUNT(*) FROM ' . $from, $params, 0);
        $pager = Pagination::make($total, $this->request->page(), $this->perPage());
        $pager->items = Database::all(
            'SELECT ' . $select . ' FROM ' . $from . ' ORDER BY ' . $order . ' LIMIT ? OFFSET ?',
            array_merge($params, [$pager->perPage, $pager->offset])
        );
        return $pager;
    }

    // ------------------------------------------------------------------ bulk + redirects

    /** @return int[] ids posted as ids[] (unique, positive, at most 500) */
    protected function postedIds(): array
    {
        $raw = $this->request->post('ids', []);
        if (!is_array($raw)) {
            return [];
        }
        $ids = [];
        foreach ($raw as $v) {
            if (is_scalar($v) && ctype_digit((string) $v) && (int) $v > 0) {
                $ids[(int) $v] = true;
            }
        }
        return array_slice(array_keys($ids), 0, 500);
    }

    /** UPDATE <table> SET <column> = ? WHERE id IN (...). Table and column are trusted constants. Returns rows changed. */
    protected function setFlag(string $table, string $column, int $value, array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        return Database::execute(
            'UPDATE ' . Database::ident($table) . ' SET ' . Database::ident($column) . ' = ? WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')',
            array_merge([$value], array_map('intval', $ids))
        );
    }

    protected function plural(int $n, string $one, string $many): string
    {
        return $n . ' ' . ($n === 1 ? $one : $many);
    }

    /**
     * Where to go after a list action: the page the admin came from (hidden "_back" = request URI, validated to stay
     * inside this area) or the area index.
     */
    protected function returnUrl(string $fallback = ''): string
    {
        $back = $this->request->string('_back');
        if ($back !== '' && $back[0] === '/' && !preg_match('~[\x00-\x1F]~', $back)) {
            $path = (string) parse_url($back, PHP_URL_PATH);
            $qs = (string) parse_url($back, PHP_URL_QUERY);
            $prefix = '/' . trim($this->area(), '/');
            if (($path === $prefix || str_starts_with($path, $prefix . '/')) && !str_contains($path, '..')) {
                return url(trim($path, '/')) . ($qs !== '' ? '?' . $qs : '');
            }
        }
        return url($fallback !== '' ? $fallback : $this->area());
    }

    // ------------------------------------------------------------------ manual ordering

    /** Next free sort_order (max + 10) inside an optional scope. Table and scope are trusted constants. */
    protected function nextSortOrder(string $table, string $where = '', array $params = []): int
    {
        return (int) Database::value(
            'SELECT COALESCE(MAX(sort_order), 0) FROM ' . Database::ident($table) . ($where !== '' ? ' WHERE ' . $where : ''),
            $params,
            0
        ) + 10;
    }

    /** Apply an explicit order: ids in the submitted order get sort_order 10, 20, 30 ... Unknown ids are ignored. Returns rows renumbered. */
    protected function applyOrder(string $table, array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return 0;
        }
        $known = array_map('intval', array_column(
            Database::all('SELECT id FROM ' . Database::ident($table) . ' WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids),
            'id'
        ));
        $valid = array_values(array_filter($ids, static fn(int $id): bool => in_array($id, $known, true)));
        Database::transaction(function () use ($table, $valid): void {
            foreach ($valid as $i => $id) {
                Database::execute('UPDATE ' . Database::ident($table) . ' SET sort_order = ? WHERE id = ?', [($i + 1) * 10, $id]);
            }
        });
        return count($valid);
    }

    /**
     * Move one row a step up/down inside its scope (e.g. its team group), renumbering the scope 10, 20, 30 ...
     * so rows that all had sort_order 0 can still be moved. Returns false when the row is already at the edge.
     */
    protected function moveWithin(string $table, int $id, string $direction, string $scopeColumn = ''): bool
    {
        $t = Database::ident($table);
        $row = Database::one('SELECT * FROM ' . $t . ' WHERE id = ?', [$id]);
        if ($row === null) {
            return false;
        }
        $where = '1 = 1';
        $params = [];
        if ($scopeColumn !== '') {
            $where = Database::ident($scopeColumn) . ' = ?';
            $params[] = $row[$scopeColumn];
        }
        $ids = array_map('intval', array_column(Database::all('SELECT id FROM ' . $t . ' WHERE ' . $where . ' ORDER BY sort_order ASC, id ASC', $params), 'id'));
        $pos = array_search($id, $ids, true);
        if ($pos === false) {
            return false;
        }
        $swap = $direction === 'up' ? $pos - 1 : $pos + 1;
        if (!isset($ids[$swap])) {
            return false;
        }
        [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
        Database::transaction(function () use ($t, $ids): void {
            foreach ($ids as $i => $rid) {
                Database::execute('UPDATE ' . $t . ' SET sort_order = ? WHERE id = ?', [($i + 1) * 10, $rid]);
            }
        });
        return true;
    }

    // ------------------------------------------------------------------ input normalisation

    /** Checkbox pair (hidden 0 + checkbox 1) -> 0|1. */
    protected function flag(string $key): int
    {
        return $this->request->bool($key) ? 1 : 0;
    }

    /**
     * Plain text from a form: control characters removed, line breaks normalised (kept only when $multiline).
     * Output is always escaped with e(), so no tag stripping is needed (and "R&D" or "<3" survive).
     */
    protected function clean(string $value, bool $multiline = false): string
    {
        // invalid UTF-8 (only possible with hand-crafted requests) is repaired first: preg_replace() with /u would return null
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = (string) preg_replace($multiline ? '/[^\P{C}\n\t]+/u' : '/\p{C}+/u', '', $value);
        return trim($value);
    }

    /**
     * A required text field must still contain something AFTER clean() (control characters only, or an empty value, are not a
     * name/title). Adds the message to $errors unless the field already has an error.
     * @param array<string,string> $errors
     */
    protected function requireText(array &$errors, string $field, string $cleaned, string $message): void
    {
        if ($cleaned === '' && !isset($errors[$field])) {
            $errors[$field] = $message;
        }
    }

    /**
     * A web address typed by an admin (website, LinkedIn, X, registration link). Returns '' when empty, the normalised
     * absolute http(s) URL, or null when it is not a usable web address. "linkedin.com/in/x" becomes "https://linkedin.com/in/x".
     * Stored as an absolute URL because the public templates print it straight into an href.
     */
    protected function webAddress(string $value, int $maxLength = 255): ?string
    {
        $v = trim($value);
        if ($v === '') {
            return '';
        }
        if (!preg_match('~^[a-z][a-z0-9+.\-]*:~i', $v)) {
            $v = 'https://' . ltrim($v, '/');
        }
        if (strlen($v) > $maxLength || !preg_match('~^https?://[^\s/$.?#][^\s]*$~i', $v)) {
            return null;
        }
        $host = parse_url($v, PHP_URL_HOST);
        if (!is_string($host) || $host === '' || (!str_contains($host, '.') && strtolower($host) !== 'localhost')) {
            return null;
        }
        return filter_var($v, FILTER_VALIDATE_URL) !== false ? $v : null;
    }

    /** "2026-11-03T14:30" / "2026-11-03 14:30[:00]" (already validated) -> "2026-11-03 14:30:00"; '' -> null. */
    protected function dbDateTime(string $value): ?string
    {
        $v = str_replace('T', ' ', trim($value));
        if ($v === '') {
            return null;
        }
        return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v) === 1 ? $v . ':00' : $v;
    }

    /**
     * Will an image-field named $name hold an image after Uploader::resolveImageField()? (mirrors its decision, but without
     * touching any file, so validation can run before anything is stored).
     */
    protected function imageWillExist(string $name, ?string $current): bool
    {
        if ($this->request->file($name . '_file') !== null) {
            return true;
        }
        if ($this->request->string($name . '_remove') === '1') {
            return false;
        }
        if ($this->request->has($name)) {
            return Content::cleanImagePath($this->request->string($name)) !== '';
        }
        return $current !== null && $current !== '';
    }
}
