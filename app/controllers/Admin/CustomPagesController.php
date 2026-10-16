<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Support\MediaCleaner;
use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Nav;
use App\Core\PageRegistry;
use App\Core\Request;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\SitePages;
use App\Core\Slug;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Page;

/**
 * CUSTOM pages created by the owner (pages.type = 'custom', served at /{slug} by the catch-all in routes/zz_custom_pages.php).
 * OWNER: B3 admin-core.
 *   GET  admin/pages/create          form
 *   POST admin/pages                 store
 *   GET  admin/pages/{slug}/edit     form (PagesController delegates here for custom pages)
 *   POST admin/pages/{slug}          update (same delegation)
 *   POST admin/pages/{slug}/delete   delete (ability pages.delete, custom pages only - built-in pages can never be deleted)
 *   POST admin/pages/{slug}/duplicate  copy as an unpublished draft
 */
final class CustomPagesController extends AdminController
{
    public const TEMPLATE_LABELS = [
        'default' => 'Standard - a comfortable reading column',
        'wide'    => 'Wide - the content uses the full page width',
        'landing' => 'Landing page - big hero image, wide sections',
    ];

    /** Public paths that exist as real routes but are not registry pages (a custom page with the same address would never be reached). */
    private const EXTRA_RESERVED = ['press', 'unsubscribe', 'sitemap.xml', 'robots.txt', 'reports', 'download'];

    public function create(Request $request): Response
    {
        $this->authorize('pages.edit');
        return $this->form('create', [
            'title' => '', 'slug' => '', 'subtitle' => '', 'body' => '', 'template' => 'default', 'hero_image' => '', 'hero_image_alt' => '',
            'seo_title' => '', 'seo_description' => '', 'og_image' => '', 'is_published' => 0, 'show_in_nav' => 0, 'show_in_footer' => 0,
            'nav_label' => '', 'nav_order' => $this->nextOrder(), 'parent_slug' => null,
        ]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('pages.edit');
        $title = $request->string('title');
        $slugInput = strtolower($request->string('slug'));
        $errors = $this->validateFields($request, null);
        $slug = $slugInput !== '' ? $slugInput : Slug::make($title);
        if ($slug === '') {
            $slug = 'page';
        }
        if (($problem = $this->slugProblem($slug, null)) !== null) {
            if ($slugInput === '' && !isset($errors['title'])) {
                $slug = Slug::unique('pages', $title, null, 'slug', [], 'page');     // auto slug collided: pick a free one
                $problem = $this->slugProblem($slug, null);
            }
            if ($problem !== null) {
                $errors['slug'] = $problem;
            }
        }
        $parent = can('menus.manage') ? $request->string('parent_slug') : '';
        if ($parent !== '' && ($p = $this->parentProblem(null, $parent)) !== null) {
            $errors['parent_slug'] = $p;
        }
        if ($errors !== []) {
            $this->fail($errors, 'admin/pages/create');
        }

        $data = $this->collect($request, null);
        $data += ['slug' => $slug, 'type' => 'custom', 'updated_by' => Auth::id()];
        $data['nav_order'] = can('menus.manage') && $request->string('nav_order') !== '' ? $request->int('nav_order') : $this->nextOrder();
        $data['parent_slug'] = $parent !== '' ? $parent : null;
        $data['show_in_nav'] = can('menus.manage') && $request->string('show_in_nav') === '1' ? 1 : 0;
        $data['show_in_footer'] = can('menus.manage') && $request->string('show_in_footer') === '1' ? 1 : 0;
        Page::create($data);
        SitePages::flush();
        Nav::flush();

        $this->audit('page.create', 'page', $slug, 'Created page "' . $data['title'] . '"');
        Flash::success('Page "' . $data['title'] . '" created' . ($data['is_published'] ? ' and published.' : ' as a draft. Switch "Published" on when it is ready for visitors.'));
        return $this->redirect('admin/pages/' . $slug . '/edit');
    }

    public function edit(Request $request, string $slug): Response
    {
        $this->authorize('pages.edit');
        $row = Page::bySlug($slug);
        if (!$row || $row['type'] !== 'custom') {
            $this->abort(404, 'Page not found');
        }
        return $this->form('edit', $row);
    }

    public function update(Request $request, string $slug): Response
    {
        $this->authorize('pages.edit');
        $row = Page::bySlug($slug);
        if (!$row || $row['type'] !== 'custom') {
            $this->abort(404, 'Page not found');
        }
        $back = 'admin/pages/' . $slug . '/edit';
        $newSlug = strtolower($request->string('slug'));
        if ($newSlug === '') {
            $newSlug = $slug;
        }
        $errors = $this->validateFields($request, (int) $row['id']);
        if ($newSlug !== $slug && ($problem = $this->slugProblem($newSlug, (int) $row['id'])) !== null) {
            $errors['slug'] = $problem;
        }
        $parent = null;
        if (can('menus.manage') && $request->has('parent_slug')) {
            $parent = $request->string('parent_slug');
            if ($parent !== '' && ($p = $this->parentProblem($slug, $parent)) !== null) {
                $errors['parent_slug'] = $p;
            }
        }
        if ($errors !== []) {
            $this->fail($errors, $back);
        }

        $bodyBefore = MediaCleaner::pathsIn((string) ($row['body'] ?? ''));
        $data = $this->collect($request, $row);
        $data['slug'] = $newSlug;
        $data['updated_by'] = Auth::id();
        if (can('menus.manage')) {
            foreach (['show_in_nav', 'show_in_footer'] as $col) {
                if ($request->has($col)) {
                    $data[$col] = $request->string($col) === '1' ? 1 : 0;
                }
            }
            if ($parent !== null) {
                $data['parent_slug'] = $parent !== '' ? $parent : null;
            }
            if ($request->string('nav_order') !== '') {
                $data['nav_order'] = $request->int('nav_order');
            }
        }
        Database::transaction(function () use ($data, $row): void {
            Page::update((int) $row['id'], $data);
            Database::execute('UPDATE pages SET updated_at = NOW() WHERE id = ?', [(int) $row['id']]);
        });
        SitePages::flush();
        Nav::flush();
        MediaCleaner::purge(array_diff($bodyBefore, MediaCleaner::pathsIn((string) $data['body'])));

        $changed = [];
        foreach ($data as $k => $v) {
            if ($k !== 'updated_by' && (string) ($row[$k] ?? '') !== (string) ($v ?? '')) {
                $changed[] = $k;
            }
        }
        $this->audit('page.update', 'page', $newSlug, 'Edited page "' . $data['title'] . '"', ['fields' => $changed]);
        Flash::success('Saved "' . $data['title'] . '".');
        if (!$data['is_published'] && (int) $row['is_published'] === 1) {
            Flash::warning('This page is now unpublished: visitors see a "not found" page and it is hidden from the menus.');
        }
        if ($request->string('after') === 'view' && $data['is_published']) {
            return Response::redirect(url($newSlug));
        }
        return $this->redirect('admin/pages/' . $newSlug . '/edit');
    }

    public function delete(Request $request, string $slug): Response
    {
        $this->authorize('pages.delete');
        $row = Page::bySlug($slug);
        if (!$row || $row['type'] !== 'custom') {
            $this->abort(404, 'Only custom pages can be deleted.');
        }
        Database::transaction(function () use ($row): void {
            Page::delete((int) $row['id']);
        });
        foreach ([(string) ($row['hero_image'] ?? ''), (string) ($row['og_image'] ?? '')] as $img) {
            MediaCleaner::purge([$img]);
        }
        MediaCleaner::purge(MediaCleaner::pathsIn((string) ($row['body'] ?? '')));
        SitePages::flush();
        Nav::flush();
        $this->audit('page.delete', 'page', $slug, 'Deleted page "' . $row['title'] . '"');
        Flash::success('Page "' . $row['title'] . '" was deleted.');
        return $this->redirect('admin/pages');
    }

    public function duplicate(Request $request, string $slug): Response
    {
        $this->authorize('pages.edit');
        $row = Page::bySlug($slug);
        if (!$row || $row['type'] !== 'custom') {
            $this->abort(404, 'Only custom pages can be duplicated.');
        }
        $title = 'Copy of ' . $row['title'];
        $copy = [
            'slug'           => Slug::unique('pages', $slug . '-copy', null, 'slug', [], 'page'),
            'title'          => mb_substr($title, 0, 190),
            'type'           => 'custom',
            'subtitle'       => $row['subtitle'], 'body' => $row['body'], 'template' => $row['template'],
            'hero_image'     => $row['hero_image'], 'hero_image_alt' => $row['hero_image_alt'],
            'seo_title'      => $row['seo_title'], 'seo_description' => $row['seo_description'], 'og_image' => $row['og_image'],
            'is_published'   => 0, 'show_in_nav' => 0, 'show_in_footer' => 0,
            'nav_label'      => '', 'nav_order' => $this->nextOrder(), 'parent_slug' => null, 'updated_by' => Auth::id(),
        ];
        Page::create($copy);
        SitePages::flush();
        $this->audit('page.create', 'page', $copy['slug'], 'Duplicated page "' . $row['title'] . '"');
        Flash::success('A draft copy was created. Change its title and address before publishing.');
        return $this->redirect('admin/pages/' . $copy['slug'] . '/edit');
    }

    // ------------------------------------------------------------------ internals

    /** @param array<string,mixed> $row */
    private function form(string $mode, array $row): Response
    {
        $slug = (string) ($row['slug'] ?? '');
        $parents = [];
        foreach (SitePages::all() as $p) {
            if ($p['slug'] !== $slug && !$p['virtual'] && $p['parent_slug'] === null) {
                $parents[$p['slug']] = $p['nav_label'];
            }
        }
        $hasChildren = false;
        if ($slug !== '') {
            foreach (SitePages::all() as $p) {
                if ($p['parent_slug'] === $slug) {
                    $hasChildren = true;
                    break;
                }
            }
        }
        $title = $mode === 'create' ? 'New page' : 'Edit ' . $row['title'];
        return $this->render('admin/pages/custom_form', [
            'mode'        => $mode,
            'row'         => $row,
            'templates'   => self::TEMPLATE_LABELS,
            'parents'     => $parents,
            'hasChildren' => $hasChildren,
            'canMenus'    => can('menus.manage'),
            'canDelete'   => can('pages.delete'),
            'links'       => Support\PageForm::linkChoices(),
            'editorJs'    => is_file(ASSET_PATH . '/js/admin-editor.js'),
        ], [
            'title'       => $title,
            'nav'         => 'pages',
            'breadcrumbs' => [['Pages', url('admin/pages')], [$mode === 'create' ? 'New page' : (string) $row['title'], null]],
        ]);
    }

    /** @return array<string,string> */
    private function validateFields(Request $request, ?int $ignoreId): array
    {
        $v = Validator::make($request->all(), [
            'title'           => 'required|max:190',
            'subtitle'        => 'nullable|max:255',
            'template'        => 'required|in:' . implode(',', Page::TEMPLATES),
            'body'            => 'nullable|max:400000',
            'hero_image_alt'  => 'nullable|max:250',
            'seo_title'       => 'nullable|max:190',
            'seo_description' => 'nullable|max:300',
            'nav_label'       => 'nullable|max:80',
            'nav_order'       => 'nullable|integer|between:0,9999',
        ], [], [
            'title' => 'Title', 'subtitle' => 'Subtitle', 'template' => 'Layout', 'body' => 'Page text', 'hero_image_alt' => 'Image description',
            'seo_title' => 'SEO title', 'seo_description' => 'SEO description', 'nav_label' => 'Menu label', 'nav_order' => 'Menu order',
        ]);
        return $v->errors();
    }

    /**
     * Normalised column values from the request (images resolved/uploaded).
     * @param array<string,mixed>|null $row existing row when editing
     * @return array<string,mixed>
     */
    private function collect(Request $request, ?array $row): array
    {
        $current = static fn(string $k): string => (string) ($row[$k] ?? '');
        try {
            $hero = Uploader::resolveImageField($request, 'hero_image', $current('hero_image'));
            $og = Uploader::resolveImageField($request, 'og_image', $current('og_image'));
        } catch (UploadException $e) {
            $this->fail(['hero_image' => $e->getMessage()], $row ? 'admin/pages/' . $row['slug'] . '/edit' : 'admin/pages/create');
        }
        // when editing, a field that was not submitted keeps its stored value
        $pick = static fn(string $k): string => $request->has($k) ? $request->string($k) : $current($k);
        $template = $pick('template');
        return [
            'title'           => $request->string('title'),
            'subtitle'        => $pick('subtitle'),
            'body'            => $request->has('body') || $row === null ? Sanitizer::html((string) $request->post('body', '')) : $current('body'),
            'template'        => in_array($template, Page::TEMPLATES, true) ? $template : 'default',
            'hero_image'      => $hero ?? '',
            'hero_image_alt'  => Sanitizer::text($pick('hero_image_alt')),
            'seo_title'       => $pick('seo_title'),
            'seo_description' => $pick('seo_description'),
            'og_image'        => $og ?? '',
            'is_published'    => $request->has('is_published') ? ($request->string('is_published') === '1' ? 1 : 0) : (int) ($row['is_published'] ?? 0),
            'nav_label'       => $pick('nav_label'),
        ];
    }

    private function nextOrder(): int
    {
        $max = (int) Database::value('SELECT COALESCE(MAX(nav_order), 0) FROM pages WHERE nav_order < 1000', [], 0);
        return min(990, $max + 10);
    }

    private function slugProblem(string $slug, ?int $ignoreId): ?string
    {
        if (strlen($slug) > 120 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return 'The address may only contain lowercase letters, numbers and hyphens (for example "our-story").';
        }
        if (Slug::isReserved($slug) || in_array($slug, self::EXTRA_RESERVED, true)) {
            return 'This address is reserved by the website. Please choose another.';
        }
        if (PageRegistry::has($slug)) {
            return 'A built-in page already uses this address.';
        }
        foreach (PageRegistry::all() as $p) {
            if (!$p['virtual'] && trim((string) $p['route'], '/') === $slug) {
                return 'A built-in page already uses this address.';
            }
        }
        $sql = 'SELECT COUNT(*) FROM pages WHERE slug = ?';
        $params = [$slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        if ((int) Database::value($sql, $params, 0) > 0) {
            return 'Another page already uses this address.';
        }
        return null;
    }

    private function parentProblem(?string $slug, string $parent): ?string
    {
        $all = SitePages::all();
        if ($slug !== null && $parent === $slug) {
            return 'A page cannot be its own parent.';
        }
        if (!isset($all[$parent]) || $all[$parent]['virtual']) {
            return 'Choose an existing page as the parent.';
        }
        if ($all[$parent]['parent_slug'] !== null) {
            return 'The parent cannot itself be a sub-page (menus have one level of dropdown).';
        }
        if ($slug !== null) {
            foreach ($all as $p) {
                if ($p['parent_slug'] === $slug) {
                    return 'This page has sub-pages of its own, so it cannot become a sub-page.';
                }
            }
        }
        return null;
    }
}
