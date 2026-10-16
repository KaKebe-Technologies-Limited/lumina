<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Content;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\Slug;
use App\Core\UploadException;
use App\Services\ImageCleanup;

/**
 * Shared admin CRUD engine for the content areas (programs, projects, posts, activities, jobs, gallery). OWNER: B4.
 *
 * A concrete controller only describes itself (config()) and the entity specifics (rules(), payload(), defaults()...);
 * the list (search / filters / tabs / sorting / pagination), create, edit, delete, activate toggle and manual re-ordering are
 * implemented once here, with CSRF (router), ability checks, audit log, safe image replacement and PRG redirects.
 *
 * config() keys:
 *   entity (audit prefix, e.g. 'program'), table, singular / plural (lower-case nouns for messages), title (admin heading, 'Programs'),
 *   path ('admin/programs'), views ('admin/programs'), nav (sidebar key), title_col ('title'), slug_col ('slug'),
 *   search (columns searched by ?q=), sorts (key => SQL with {dir}), default_sort ([key, 'asc'|'desc']),
 *   order_sql (canonical manual order, used by move()), reorderable (bool), has_active (bool, default true),
 *   public (callable(array $row): ?string  - public path of a row or null when it is not publicly visible).
 */
abstract class CrudController extends AdminController
{
    /** @return array<string,mixed> */
    abstract protected function config(): array;

    /**
     * Validation spec for create/update.
     * @return array{rules:array<string,string>,messages?:array<string,string>,labels?:array<string,string>}
     */
    abstract protected function rules(?array $row): array;

    /**
     * Turn validated input into DB columns (everything except the image columns, which the base class resolves).
     * @param array<string,mixed> $valid validated, trimmed values for the keys in rules()
     * @return array<string,mixed>
     */
    abstract protected function payload(Request $request, ?array $row, array $valid): array;

    /** Values for a brand-new record. @return array<string,mixed> */
    protected function defaults(): array
    {
        return ['is_active' => 1, 'sort_order' => $this->nextSortOrder()];
    }

    /** Image columns: column => alt-text column (or '' when there is none). @return array<string,string> */
    protected function imageFields(): array
    {
        return [];
    }

    /** Image columns that may not end up empty (NOT NULL columns). @return list<string> */
    protected function requiredImages(): array
    {
        return [];
    }

    /** Rich-text columns whose embedded uploads (pictures added with the editor) are cleaned up when removed / the row is deleted. @return list<string> */
    protected function richColumns(): array
    {
        return [];
    }

    /** @return list<string> upload paths ("2026/10/abc.jpg") embedded in the rich-text columns of a row */
    protected function embeddedImages(array $row): array
    {
        $found = [];
        foreach ($this->richColumns() as $col) {
            if (!empty($row[$col]) && preg_match_all('~/uploads/(\d{4}/\d{2}/[A-Za-z0-9_\-]+\.(?:jpe?g|png|gif|webp))~i', (string) $row[$col], $m)) {
                array_push($found, ...$m[1]);
            }
        }
        return array_values(array_unique($found));
    }

    /** Human label of a row for flash messages and the audit trail. */
    protected function rowLabel(array $row): string
    {
        $c = $this->config();
        return (string) ($row[$c['title_col'] ?? 'title'] ?? '');
    }

    /** Extra variables for the form view (select options etc.). @return array<string,mixed> */
    protected function formData(?array $row): array
    {
        return [];
    }

    /** Extra variables for the index view. @return array<string,mixed> */
    protected function listData(): array
    {
        return [];
    }

    /**
     * Apply entity specific filters (?type=, ?program= ...) to the list query.
     * @param list<string> $where
     * @param list<mixed>  $params
     * @return array<string,string> the active filters (name => value) for the view
     */
    protected function applyFilters(Request $request, array &$where, array &$params): array
    {
        return [];
    }

    /** Status tabs: key => ['label' => .., 'sql' => string|null (WHERE fragment on alias t), 'params' => []]. Key 'all' = no condition. */
    protected function tabs(): array
    {
        if (($this->config()['has_active'] ?? true) !== true) {
            return [];
        }
        return [
            'all'      => ['label' => 'All', 'sql' => null, 'params' => []],
            'active'   => ['label' => 'Active', 'sql' => 't.is_active = 1', 'params' => []],
            'inactive' => ['label' => 'Inactive', 'sql' => 't.is_active = 0', 'params' => []],
        ];
    }

    /** SELECT list / JOIN for the index query (alias of the main table is "t"). */
    protected function listSelect(): string
    {
        return 't.*';
    }

    protected function listJoin(): string
    {
        return '';
    }

    /** Hook after a successful create ('create') / update ('update'). */
    protected function saved(string $mode, int $id, array $row, ?array $before): void
    {
    }

    // ------------------------------------------------------------------ list

    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $c = $this->config();
        $where = [];
        $params = [];

        $q = mb_substr($request->string('q'), 0, 120);
        if ($q !== '' && !empty($c['search'])) {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $parts = [];
            foreach ((array) $c['search'] as $col) {
                $parts[] = 't.' . Database::ident((string) $col) . ' LIKE ?';
                $params[] = $like;
            }
            $where[] = '(' . implode(' OR ', $parts) . ')';
        }

        $filters = $this->applyFilters($request, $where, $params);

        $tabs = $this->tabs();
        $tabParam = (string) ($c['tab_param'] ?? 'status');
        $tabKey = $request->string($tabParam);
        if (!isset($tabs[$tabKey])) {
            $tabKey = 'all';
        }
        if (!empty($tabs[$tabKey]['sql'])) {
            $where[] = '(' . $tabs[$tabKey]['sql'] . ')';
            foreach ((array) ($tabs[$tabKey]['params'] ?? []) as $p) {
                $params[] = $p;
            }
        }

        [$defaultSort, $defaultDir] = (array) ($c['default_sort'] ?? ['order', 'asc']);
        $sorts = (array) $c['sorts'];
        $sort = $request->string('sort');
        if (!isset($sorts[$sort])) {
            $sort = (string) $defaultSort;
        }
        $dirParam = strtolower($request->string('dir'));
        $dir = in_array($dirParam, ['asc', 'desc'], true) ? $dirParam : (string) $defaultDir;
        $orderSql = str_replace('{dir}', strtoupper($dir), (string) $sorts[$sort]);

        $from = ' FROM ' . Database::ident((string) $c['table']) . ' t ' . $this->listJoin();
        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $total = (int) Database::value('SELECT COUNT(*)' . $from . $whereSql, $params, 0);
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        $pagination->items = Database::all(
            'SELECT ' . $this->listSelect() . $from . $whereSql . ' ORDER BY ' . $orderSql . ' LIMIT ? OFFSET ?',
            array_merge($params, [$pagination->perPage, $pagination->offset])
        );

        // tab counts (whole table, independent of search/filters)
        $tabList = [];
        foreach ($tabs as $key => $tab) {
            $sql = $tab['sql'] ?? null;
            $count = (int) Database::value(
                'SELECT COUNT(*)' . $from . ($sql ? ' WHERE ' . $sql : ''),
                (array) ($tab['params'] ?? []),
                0
            );
            $tabList[] = ['key' => $key, 'label' => (string) $tab['label'], 'count' => $count, 'active' => $key === $tabKey];
        }

        $isDefaultView = $q === '' && $filters === [] && $tabKey === 'all' && $sort === $defaultSort && $dir === $defaultDir;
        $current = array_filter(
            ['q' => $q, $tabParam => $tabKey === 'all' ? null : $tabKey, 'sort' => $sort === $defaultSort ? null : $sort,
             'dir' => ($sort === $defaultSort && $dir === $defaultDir) ? null : $dir, 'per_page' => $request->has('per_page') ? $this->perPage() : null, 'page' => $pagination->page > 1 ? $pagination->page : null]
            + $filters,
            static fn($v) => $v !== null && $v !== ''
        );
        $path = (string) $c['path'];
        $listUrl = static function (array $over = []) use ($current, $path): string {
            $base = $current;
            if (!array_key_exists('page', $over)) {
                unset($base['page']);                // any change of filter / sort / tab starts again on page 1
            }
            return url($path, array_filter(array_merge($base, $over), static fn($v) => $v !== null && $v !== ''));
        };

        return $this->render($c['views'] . '/index', [
            'c'           => $c,
            'pagination'  => $pagination,
            'rows'        => $pagination->items,
            'q'           => $q,
            'filters'     => $filters,
            'tabs'        => $tabList,
            'sort'        => $sort,
            'dir'         => $dir,
            'defaultSort' => $defaultSort,
            'canReorder'  => !empty($c['reorderable']) && (!empty($c['reorder_filtered']) ? ($sort === $defaultSort && $dir === $defaultDir) : $isDefaultView),
            'perPage'     => $pagination->perPage,
            'returnQs'    => http_build_query($current),
            'keep'        => array_intersect_key($current, array_flip([$tabParam, 'sort', 'dir'])),
            'tabParam'    => $tabParam,
            'listUrl'     => $listUrl,
            'hasFilters'  => !$isDefaultView,
            'total'       => $total,
        ] + $this->listData(), ['title' => (string) $c['title'], 'nav' => (string) $c['nav'], 'breadcrumbs' => [[(string) $c['title'], null]]]);
    }

    // ------------------------------------------------------------------ create / edit

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        return $this->form(null);
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        return $this->form($this->find($id));
    }

    protected function form(?array $row): Response
    {
        $c = $this->config();
        $isNew = $row === null;
        $pub = (!$isNew && isset($c['public']) && is_callable($c['public'])) ? $c['public']($row) : null;
        $data = [
            'c'         => $c,
            'row'       => $row ?? $this->defaults(),
            'isNew'     => $isNew,
            'id'        => $row['id'] ?? null,
            'action'    => url($isNew ? (string) $c['path'] : $c['path'] . '/' . $row['id']),
            'listUrl'   => url((string) $c['path']),
            'publicUrl' => $pub ? url($pub) : null,
        ] + $this->formData($row);
        $label = ucfirst((string) $c['singular']);
        $heading = $isNew ? 'New ' . $c['singular'] : 'Edit ' . $c['singular'];
        return $this->render($c['views'] . '/form', $data, [
            'title'       => $heading,
            'nav'         => (string) $c['nav'],
            'breadcrumbs' => [[(string) $c['title'], url((string) $c['path'])], [$isNew ? 'New ' . $label : mb_strimwidth($this->rowLabel($row), 0, 48, '…'), null]],
        ]);
    }

    // ------------------------------------------------------------------ store / update

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $c = $this->config();
        [$data, $media] = $this->gather($request, null);
        try {
            $id = Database::insert((string) $c['table'], $data);
        } catch (\Throwable $e) {
            ImageCleanup::releaseMany($media['uploaded']);
            throw $e;
        }
        $this->saved('create', $id, $data, null);
        $title = $this->rowLabel($data);
        $this->audit($c['entity'] . '.create', (string) $c['entity'], $id, 'Created ' . $c['singular'] . ' "' . $title . '"');
        Flash::success(ucfirst((string) $c['singular']) . ' "' . $title . '" was created.');
        return $this->afterSave($request, $id);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $c = $this->config();
        $row = $this->find($id);
        [$data, $media] = $this->gather($request, $row);
        try {
            Database::update((string) $c['table'], $data, 'id = ?', [$id]);
        } catch (\Throwable $e) {
            ImageCleanup::releaseMany($media['uploaded']);
            throw $e;
        }
        ImageCleanup::releaseMany(array_merge($media['release'], array_diff($this->embeddedImages($row), $this->embeddedImages($data))));
        $this->saved('update', $id, $data, $row);
        $changed = [];
        foreach ($data as $k => $v) {
            if ((string) ($row[$k] ?? '') !== (string) ($v ?? '')) {
                $changed[] = $k;
            }
        }
        $title = $this->rowLabel($data + $row);
        $this->audit($c['entity'] . '.update', (string) $c['entity'], $id, 'Edited ' . $c['singular'] . ' "' . $title . '"', ['fields' => $changed]);
        Flash::success(ucfirst((string) $c['singular']) . ' "' . $title . '" was saved.');
        return $this->afterSave($request, $id);
    }

    /** PRG: "Save" stays on the edit screen, "Save and close" returns to the list. */
    protected function afterSave(Request $request, int $id): Response
    {
        $c = $this->config();
        if ($request->string('after') === 'list') {
            return Response::redirect($this->returnUrl($request, (string) $c['path']));
        }
        return $this->redirect($c['path'] . '/' . $id . '/edit');
    }

    /**
     * Validate + build the DB payload, resolve image fields (without deleting anything yet).
     * @return array{0:array<string,mixed>,1:array{uploaded:list<string>,release:list<string>}}
     */
    protected function gather(Request $request, ?array $row): array
    {
        $spec = $this->rules($row);
        $valid = $this->validate($spec['rules'], (array) ($spec['messages'] ?? []), (array) ($spec['labels'] ?? []));
        $data = $this->payload($request, $row, $valid);

        $uploaded = [];
        $release = [];
        foreach ($this->imageFields() as $col => $altCol) {
            try {
                $r = ImageCleanup::resolve($request, $col, $row[$col] ?? null);
            } catch (UploadException $e) {
                ImageCleanup::releaseMany($uploaded);
                $this->fail([$col => $e->getMessage()]);
            }
            if ($r['value'] === null && in_array($col, $this->requiredImages(), true)) {
                ImageCleanup::releaseMany($uploaded);
                $this->fail([$col => 'Please choose a picture. An existing picture can only be replaced, not removed.']);
            }
            $data[$col] = $r['value'];
            if ($altCol !== '') {
                $alt = mb_substr(Sanitizer::text($request->string($altCol)), 0, 250);
                $data[$altCol] = ($r['value'] !== null && $alt !== '') ? $alt : null;
            }
            if ($r['uploaded'] !== null) {
                $uploaded[] = $r['uploaded'];
            }
            if ($row !== null && $r['changed'] && !empty($row[$col])) {
                $release[] = (string) $row[$col];
            }
        }
        return [$data, ['uploaded' => $uploaded, 'release' => $release]];
    }

    // ------------------------------------------------------------------ delete / toggle / move

    public function delete(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $c = $this->config();
        $row = $this->find($id);
        Database::execute('DELETE FROM ' . Database::ident((string) $c['table']) . ' WHERE id = ?', [$id]);
        $paths = [];
        foreach (array_keys($this->imageFields()) as $col) {
            if (!empty($row[$col])) {
                $paths[] = (string) $row[$col];
            }
        }
        ImageCleanup::releaseMany(array_merge($paths, $this->embeddedImages($row)));
        $title = $this->rowLabel($row);
        $this->audit($c['entity'] . '.delete', (string) $c['entity'], $id, 'Deleted ' . $c['singular'] . ' "' . $title . '"');
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'id' => $id]);
        }
        Flash::success(ucfirst((string) $c['singular']) . ' "' . $title . '" was deleted.');
        return Response::redirect($this->returnUrl($request, (string) $c['path']));
    }

    /** POST {id}/toggle: flip is_active (shown / hidden on the public site). */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $c = $this->config();
        $row = $this->find($id);
        $new = empty($row['is_active']) ? 1 : 0;
        Database::update((string) $c['table'], ['is_active' => $new], 'id = ?', [$id]);
        $title = $this->rowLabel($row);
        $this->audit($c['entity'] . ($new ? '.activate' : '.deactivate'), (string) $c['entity'], $id, ($new ? 'Activated ' : 'Deactivated ') . $c['singular'] . ' "' . $title . '"');
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'id' => $id, 'active' => (bool) $new]);
        }
        Flash::success('"' . $title . '" is now ' . ($new ? 'active (visible on the website).' : 'inactive (hidden from the website).'));
        return Response::redirect($this->returnUrl($request, (string) $c['path']));
    }

    /** POST {id}/move (direction=up|down): swap with the neighbour in the canonical manual order and renumber 10,20,30... */
    public function move(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $c = $this->config();
        $this->find($id);
        $dir = $request->string('direction') === 'up' ? -1 : 1;
        $table = Database::ident((string) $c['table']);
        Database::transaction(function () use ($c, $table, $id, $dir): void {
            $ids = array_map('intval', array_column(Database::all('SELECT id FROM ' . $table . ' ORDER BY ' . $c['order_sql']), 'id'));
            $pos = array_search($id, $ids, true);
            if ($pos === false) {
                return;
            }
            $swap = $pos + $dir;
            if (isset($ids[$swap])) {
                [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
            }
            $n = 10;
            foreach ($ids as $rowId) {
                Database::execute('UPDATE ' . $table . ' SET sort_order = ? WHERE id = ? AND sort_order <> ?', [$n, $rowId, $n]);
                $n += 10;
            }
        });
        $this->audit($c['entity'] . '.reorder', (string) $c['entity'], $id, 'Changed the order of ' . $c['plural']);
        if ($request->expectsJson()) {
            return $this->json(['ok' => true]);
        }
        return Response::redirect($this->returnUrl($request, (string) $c['path']));
    }

    // ------------------------------------------------------------------ helpers shared with the concrete controllers

    protected function find(int $id): array
    {
        $c = $this->config();
        return Database::one('SELECT * FROM ' . Database::ident((string) $c['table']) . ' WHERE id = ? LIMIT 1', [$id])
            ?? throw new HttpException(404, 'Not found');
    }

    protected function nextSortOrder(): int
    {
        $c = $this->config();
        return (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM ' . Database::ident((string) $c['table']), [], 10);
    }

    /** Where to go after a POST: the list with the filters it was opened with (hidden "return" = query string). */
    protected function returnUrl(Request $request, string $fallbackPath): string
    {
        $qs = $request->string('return');
        if ($qs !== '' && preg_match('/^[A-Za-z0-9_%=&.+,\[\]\-]{1,400}$/', $qs) === 1) {
            return url($fallbackPath) . '?' . $qs;
        }
        return url($fallbackPath);
    }

    /**
     * Slug for a row: typed value (normalised) or generated from the title; unique within the table (and optional scope).
     * @param array<string,mixed> $scope extra equality conditions that define uniqueness
     */
    protected function slugFor(Request $request, ?array $row, string $title, array $scope = []): string
    {
        $c = $this->config();
        $col = (string) ($c['slug_col'] ?? 'slug');
        $typed = Slug::make($request->string('slug'));
        if ($typed === '') {
            if ($row !== null && !empty($row[$col])) {
                return (string) $row[$col];
            }
            return Slug::unique((string) $c['table'], $title, null, $col, $scope, (string) $c['entity']);
        }
        $sql = 'SELECT COUNT(*) FROM ' . Database::ident((string) $c['table']) . ' WHERE ' . Database::ident($col) . ' = ?';
        $bind = [$typed];
        if ($row !== null) {
            $sql .= ' AND id <> ?';
            $bind[] = (int) $row['id'];
        }
        foreach ($scope as $k => $v) {
            $sql .= ' AND ' . Database::ident((string) $k) . ' = ?';
            $bind[] = $v;
        }
        if ((int) Database::value($sql, $bind, 0) > 0) {
            $this->fail([$col => 'Another ' . $c['singular'] . ' already uses this web address. Please choose a different one.']);
        }
        return $typed;
    }

    /** Plain single-line text (tags stripped) or null when empty. */
    protected function text(mixed $v): ?string
    {
        $s = Sanitizer::text(is_scalar($v) ? (string) $v : '');
        return $s === '' ? null : $s;
    }

    /** Plain multi-line text (tags stripped, line breaks kept) or null when empty. */
    protected function plain(mixed $v): ?string
    {
        $s = str_replace(["\r\n", "\r"], "\n", is_scalar($v) ? (string) $v : '');
        $s = trim(strip_tags($s));
        $s = (string) preg_replace("/\n{3,}/", "\n\n", $s);
        return $s === '' ? null : $s;
    }

    /** Sanitised rich text or null when empty. */
    protected function html(mixed $v): ?string
    {
        $s = Sanitizer::html(is_scalar($v) ? (string) $v : '');
        return $s === '' ? null : $s;
    }

    protected function dateOrNull(mixed $v): ?string
    {
        $s = is_scalar($v) ? trim((string) $v) : '';
        return $s === '' ? null : $s;
    }

    /** Rules shared by nearly every entity (seo, flags, order). @return array<string,string> */
    protected function commonRules(): array
    {
        return [
            'seo_title'       => 'nullable|max:190',
            'seo_description' => 'nullable|max:300',
            'sort_order'      => 'nullable|integer|between:-100000,100000',
            'is_active'       => 'nullable|boolean',
            'is_featured'     => 'nullable|boolean',
        ];
    }
}
