<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Support\MediaCleaner;
use App\Controllers\Admin\Support\PageForm;
use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Content;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Nav;
use App\Core\PageRegistry;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Page;

/**
 * Admin PAGES EDITOR (centrepiece of the CMS requirement). OWNER: B3 admin-core.
 *   GET  admin/pages                       list of every page (registry + custom + site-wide content)
 *   GET  admin/pages/{slug}/edit           sectioned editor (registry page) - custom pages are delegated to CustomPagesController
 *   POST admin/pages/{slug}                save content blocks, section visibility, resets, SEO, menu entry, publish flag
 *   POST admin/pages/{slug}/publish        quick publish / unpublish from the list
 *   POST admin/pages/{slug}/reset          drop EVERY saved edit of a registry page (back to the code defaults)
 * Form contract: docs/ARCHITECTURE.md section 8.7 (see Support\PageForm for the field names).
 */
final class PagesController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('pages.edit');
        SitePages::sync();
        $all = SitePages::all();
        $summary = PageForm::editSummary();

        $registry = [];
        $custom = [];
        $virtual = [];
        foreach ($all as $p) {
            $p['edits'] = $summary[$p['slug']]['n'] ?? 0;
            $p['edited_at'] = $summary[$p['slug']]['at'] ?? '';
            $p['menu_hidden'] = $p['requires'] !== null && !SitePages::requirementMet($p['requires']);
            if ($p['virtual']) {
                $virtual[] = $p;
            } elseif ($p['registry']) {
                $registry[] = $p;
            } else {
                $custom[] = $p;
            }
        }
        $by = static fn(array $a, array $b): int => [$a['nav_order'], $a['title']] <=> [$b['nav_order'], $b['title']];
        usort($registry, $by);
        usort($custom, static fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));

        return $this->render('admin/pages/index', [
            'registry' => $registry,
            'custom'   => $custom,
            'virtual'  => $virtual,
        ], ['title' => 'Pages', 'nav' => 'pages', 'breadcrumbs' => [['Pages', null]]]);
    }

    public function edit(Request $request, string $slug): Response
    {
        $this->authorize('pages.edit');
        SitePages::sync();
        $page = SitePages::get($slug) ?? $this->abort(404, 'Page not found');
        if (!$page['registry']) {
            return (new CustomPagesController($this->request))->edit($request, $slug);
        }
        $reg = PageRegistry::get($slug);
        $row = Page::bySlug($slug) ?? [];

        $parents = [];
        foreach (SitePages::all() as $p) {
            if ($p['slug'] !== $slug && !$p['virtual'] && $p['parent_slug'] === null) {
                $parents[$p['slug']] = $p['nav_label'];
            }
        }
        $hasChildren = false;
        foreach (SitePages::all() as $p) {
            if ($p['parent_slug'] === $slug) {
                $hasChildren = true;
                break;
            }
        }

        return $this->render('admin/pages/edit', [
            'slug'        => $slug,
            'pg'          => $page,
            'reg'         => $reg,
            'row'         => $row,
            'sections'    => PageForm::sections($slug),
            'links'       => PageForm::linkChoices(),
            'last'        => PageForm::lastEdit($slug),
            'stamp'       => PageForm::stamp($slug),
            'parents'     => $parents,
            'hasChildren' => $hasChildren,
            'canMenus'    => can('menus.manage'),
            'canReset'    => can('pages.delete'),
            'editorJs'    => is_file(ASSET_PATH . '/js/admin-editor.js'),
        ], [
            'title'       => 'Edit ' . $page['title'],
            'nav'         => 'pages',
            'breadcrumbs' => [['Pages', url('admin/pages')], [$page['title'], null]],
        ]);
    }

    public function update(Request $request, string $slug): Response
    {
        $this->authorize('pages.edit');
        $page = SitePages::get($slug) ?? $this->abort(404, 'Page not found');
        if (!$page['registry']) {
            return (new CustomPagesController($this->request))->update($request, $slug);
        }
        $back = 'admin/pages/' . $slug . '/edit';
        $post = $request->post();
        $post = is_array($post) ? $post : [];

        // Large pages may be posted as one JSON field (assets/js/pages/admin-pages.js) to stay below PHP's max_input_vars.
        if (isset($post['blocks_json']) && is_string($post['blocks_json'])) {
            $decoded = json_decode($post['blocks_json'], true);
            $post['blocks'] = is_array($decoded) ? $decoded : [];
            unset($post['blocks_json']);
        }
        if (($post['_end'] ?? '') !== '1') {
            $this->fail(['_form' => 'Nothing was saved: the form was larger than the server accepts (PHP max_input_vars = '
                . (int) ini_get('max_input_vars') . '). Please enable JavaScript and try again, or ask your developer to raise that limit.'], $back);
        }

        // Somebody else saved this page after the editor was opened?
        $loaded = (int) ($post['_loaded'] ?? 0);
        if ($loaded > 0 && PageForm::stamp($slug) > $loaded && ($post['_overwrite'] ?? '') !== '1') {
            $this->fail(['_form' => 'This page was changed by someone else after you opened it. Your edits are still in the form below: '
                . 'review them and press Save again to overwrite the other changes, or reload to start from the latest version.'], $back);
        }

        // ---- page-level fields
        $v = Validator::make($request->all(), [
            'title'           => 'required|max:190',
            'nav_label'       => 'nullable|max:80',
            'seo_title'       => 'nullable|max:190',
            'seo_description' => 'nullable|max:300',
            'nav_order'       => 'nullable|integer|between:0,9999',
        ], [], ['title' => 'Page title', 'nav_label' => 'Menu label', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description', 'nav_order' => 'Menu order']);
        $errors = $v->errors();

        $collected = PageForm::collect($slug, $post);
        $errors += $collected['errors'];

        $canMenus = can('menus.manage');
        $parent = null;
        if ($canMenus && $request->has('parent_slug')) {
            $parent = $request->string('parent_slug');
            if ($parent !== '') {
                $problem = $this->parentProblem($slug, $parent);
                if ($problem !== null) {
                    $errors['parent_slug'] = $problem;
                }
            }
        }
        if ($errors !== []) {
            $this->fail($errors, $back);
        }

        // ---- save
        $row = Page::bySlug($slug) ?? [];
        $uploadsBefore = MediaCleaner::forPage($slug);
        // only fields that were actually submitted are changed (a partial POST never wipes the rest)
        $data = ['title' => $request->string('title'), 'updated_by' => Auth::id()];
        foreach (['nav_label', 'seo_title', 'seo_description'] as $col) {
            if ($request->has($col)) {
                $data[$col] = $request->string($col);
            }
        }
        try {
            $og = Uploader::resolveImageField($request, 'og_image', (string) ($row['og_image'] ?? ''));
        } catch (UploadException $e) {
            $this->fail(['og_image' => $e->getMessage()], $back);
        }
        $data['og_image'] = $og ?? '';

        $wasPublished = (bool) $page['is_published'];
        if (!$page['virtual'] && $request->has('is_published')) {
            $data['is_published'] = $slug === 'home' ? 1 : ($request->string('is_published') === '1' ? 1 : 0);
        }
        if ($canMenus && !$page['virtual']) {
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

        $changed = [];
        Database::transaction(function () use ($slug, $collected, $data, &$changed): void {
            $changed = Content::saveMany($slug, $collected['input'], Auth::id());
            foreach ($collected['resets'] as $key) {
                Content::reset($slug, $key);
            }
            Database::update('pages', $data, 'slug = ?', [$slug]);
            Database::execute('UPDATE pages SET updated_at = NOW() WHERE slug = ?', [$slug]);
        });
        Content::flush($slug);
        SitePages::flush();
        Nav::flush();
        MediaCleaner::purge(array_diff($uploadsBefore, MediaCleaner::forPage($slug)));

        $pageChanged = [];
        foreach (['title', 'nav_label', 'seo_title', 'seo_description', 'is_published', 'show_in_nav', 'show_in_footer', 'parent_slug', 'nav_order'] as $k) {
            if (array_key_exists($k, $data) && (string) ($row[$k] ?? '') !== (string) ($data[$k] ?? '')) {
                $pageChanged[] = $k;
            }
        }
        if ((string) ($row['og_image'] ?? '') !== (string) $data['og_image']) {
            $pageChanged[] = 'og_image';
        }
        $this->audit('page.update', 'page', $slug, 'Edited page "' . $data['title'] . '"', [
            'fields' => array_slice($changed, 0, 80),
            'reset'  => array_slice($collected['resets'], 0, 80),
            'page'   => $pageChanged,
        ]);

        $n = count($changed) + count($collected['resets']) + count($pageChanged);
        Flash::success($n === 0 ? 'No changes to save: everything is already up to date.' : 'Saved "' . $data['title'] . '" (' . $n . ($n === 1 ? ' change' : ' changes') . ').');
        if (isset($data['is_published']) && !$data['is_published'] && $wasPublished) {
            Flash::warning('This page is now unpublished: visitors see a "not found" page and it is hidden from the menus.');
        }
        if (($post['after'] ?? '') === 'view' && !$page['virtual']) {
            $fresh = SitePages::get($slug);
            return Response::redirect((string) ($fresh['url'] ?? url('')));
        }
        return $this->redirect($back);
    }

    /** POST admin/pages/{slug}/publish (publish=1|0) - quick toggle from the list. */
    public function publish(Request $request, string $slug): Response
    {
        $this->authorize('pages.edit');
        SitePages::sync();
        $page = SitePages::get($slug) ?? $this->abort(404, 'Page not found');
        if ($page['virtual'] || $slug === 'home') {
            Flash::error($slug === 'home' ? 'The home page cannot be unpublished.' : 'This entry is not a page.');
            return $this->redirect('admin/pages');
        }
        $on = $request->string('publish') === '1';
        Database::update('pages', ['is_published' => $on ? 1 : 0, 'updated_by' => Auth::id()], 'slug = ?', [$slug]);
        Database::execute('UPDATE pages SET updated_at = NOW() WHERE slug = ?', [$slug]);
        SitePages::flush();
        Nav::flush();
        $this->audit($on ? 'page.publish' : 'page.unpublish', 'page', $slug, ($on ? 'Published' : 'Unpublished') . ' page "' . $page['title'] . '"');
        Flash::success('"' . $page['title'] . '" is now ' . ($on ? 'published.' : 'unpublished. Visitors see a "not found" page and it is hidden from the menus.'));
        return $this->back('admin/pages');
    }

    /** POST admin/pages/{slug}/reset - remove every saved edit of a registry page. */
    public function reset(Request $request, string $slug): Response
    {
        $this->authorize('pages.delete');
        $page = SitePages::get($slug) ?? $this->abort(404, 'Page not found');
        if (!$page['registry']) {
            $this->abort(404, 'Only built-in pages can be reset.');
        }
        $uploads = MediaCleaner::forPage($slug);
        Content::resetPage($slug);
        MediaCleaner::purge($uploads);
        SitePages::flush();
        Nav::flush();
        $this->audit('page.reset', 'page', $slug, 'Reset page "' . $page['title'] . '" to its default content');
        Flash::success('"' . $page['title'] . '" now shows its original content again. (Title, search-engine text and menu settings were not changed.)');
        return $this->redirect('admin/pages/' . $slug . '/edit');
    }

    /** Why a page cannot be placed under $parent in the header menu, or null when it can. */
    private function parentProblem(string $slug, string $parent): ?string
    {
        $all = SitePages::all();
        if ($parent === $slug) {
            return 'A page cannot be its own parent.';
        }
        if (!isset($all[$parent]) || $all[$parent]['virtual']) {
            return 'Choose an existing page as the parent.';
        }
        if ($all[$parent]['parent_slug'] !== null) {
            return 'The parent cannot itself be a sub-page (menus have one level of dropdown).';
        }
        foreach ($all as $p) {
            if ($p['parent_slug'] === $slug) {
                return 'This page has sub-pages of its own, so it cannot become a sub-page.';
            }
        }
        return null;
    }
}
