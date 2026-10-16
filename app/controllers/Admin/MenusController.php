<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Nav;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;

/**
 * Header / footer MENUS (ability menus.manage = admins). OWNER: B3 admin-core.
 *   GET  admin/menus    every page in menu order with label, header/footer switches and parent (dropdown) select
 *   POST admin/menus    saves nav_order (10,20,30... in the posted order), nav_label, show_in_nav, show_in_footer, parent_slug
 * Header and footer share ONE order (pages.nav_order); the header shows the entries flagged "header", the footer those flagged "footer".
 */
final class MenusController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('menus.manage');
        SitePages::sync();
        $items = [];
        foreach (SitePages::all() as $p) {
            if ($p['virtual']) {
                continue;
            }
            $p['menu_hidden'] = $p['requires'] !== null && !SitePages::requirementMet($p['requires']);
            $items[] = $p;
        }
        usort($items, static fn(array $a, array $b): int => [$a['nav_order'], $a['nav_label']] <=> [$b['nav_order'], $b['nav_label']]);

        // show a sub-page directly below its parent so the structure is obvious
        $byParent = [];
        $top = [];
        foreach ($items as $it) {
            if ($it['parent_slug'] !== null && isset(SitePages::all()[$it['parent_slug']])) {
                $byParent[$it['parent_slug']][] = $it;
            } else {
                $top[] = $it;
            }
        }
        $ordered = [];
        foreach ($top as $it) {
            $ordered[] = $it;
            foreach ($byParent[$it['slug']] ?? [] as $child) {
                $ordered[] = $child;
            }
        }

        return $this->render('admin/menus/index', [
            'items' => $ordered,
        ], ['title' => 'Menus', 'nav' => 'menus', 'breadcrumbs' => [['Menus', null]]]);
    }

    public function update(Request $request): Response
    {
        $this->authorize('menus.manage');
        SitePages::sync();
        $all = SitePages::all();
        $order = $request->post('order', []);
        $menu = $request->post('menu', []);
        $order = is_array($order) ? array_values(array_filter($order, 'is_string')) : [];
        $menu = is_array($menu) ? $menu : [];

        $slugs = [];
        foreach ($order as $slug) {
            if (isset($all[$slug]) && !$all[$slug]['virtual'] && !in_array($slug, $slugs, true)) {
                $slugs[] = $slug;
            }
        }
        if ($slugs === []) {
            $this->fail(['_form' => 'Nothing to save: the menu list was empty.'], 'admin/menus');
        }

        $rows = [];
        $errors = [];
        foreach ($slugs as $slug) {
            $m = isset($menu[$slug]) && is_array($menu[$slug]) ? $menu[$slug] : [];
            $label = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) ($m['label'] ?? ''))));
            if (mb_strlen($label) > 80) {
                $errors["menu[{$slug}][label]"] = 'The label can have at most 80 characters.';
            }
            if ($label === '') {
                $label = (string) $all[$slug]['title'];
            }
            $parent = trim((string) ($m['parent'] ?? ''));
            $rows[$slug] = [
                'label'  => $label,
                'header' => (string) ($m['header'] ?? '0') === '1',
                'footer' => (string) ($m['footer'] ?? '0') === '1',
                'parent' => $parent !== '' ? $parent : null,
            ];
        }
        // parent rules: one level only, parent must be a listed page, never itself
        foreach ($rows as $slug => $r) {
            $parent = $r['parent'];
            if ($parent === null) {
                continue;
            }
            $key = "menu[{$slug}][parent]";
            if ($parent === $slug) {
                $errors[$key] = '"' . $r['label'] . '" cannot be its own parent.';
            } elseif (!isset($rows[$parent])) {
                $errors[$key] = 'Choose an existing page as the parent of "' . $r['label'] . '".';
            } elseif ($rows[$parent]['parent'] !== null) {
                $errors[$key] = '"' . $r['label'] . '": the parent "' . $rows[$parent]['label'] . '" is itself a sub-page. Dropdowns have one level only.';
            }
        }
        if ($errors !== []) {
            $this->fail($errors, 'admin/menus');
        }

        Database::transaction(function () use ($slugs, $rows): void {
            $pos = 10;
            foreach ($slugs as $slug) {
                $r = $rows[$slug];
                Database::update('pages', [
                    'nav_order'      => $pos,
                    'nav_label'      => $r['label'],
                    'show_in_nav'    => $r['header'] ? 1 : 0,
                    'show_in_footer' => $r['footer'] ? 1 : 0,
                    'parent_slug'    => $r['parent'],
                    'updated_by'     => Auth::id(),
                ], 'slug = ?', [$slug]);
                $pos += 10;
            }
        });
        SitePages::flush();
        Nav::flush();
        $this->audit('menu.update', 'menu', null, 'Updated the header and footer menus', ['pages' => count($slugs)]);
        Flash::success('Menus saved. The header and footer on the website are updated.');
        return $this->redirect('admin/menus');
    }
}
