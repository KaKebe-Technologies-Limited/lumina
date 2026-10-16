<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\PageRegistry;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Core\Slug;

/**
 * Router fallback (routes/zz_custom_pages.php): serves owner-created pages (pages.type = 'custom') at /{slug}.
 * 404 when the slug is unknown, reserved, a registry page, nested (a/b) or unpublished. SEO via page_meta(); the template
 * (default | wide | landing) is chosen in the admin. Body is sanitised rich text (Sanitizer on save AND on output).
 */
final class CustomPageController extends Controller
{
    public function show(Request $request): Response
    {
        $slug = trim($request->path(), '/');
        if ($slug === '' || str_contains($slug, '/') || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
            || Slug::isReserved($slug) || in_array($slug, (array) Config::get('reserved_slugs', []), true) || PageRegistry::has($slug)) {
            throw new HttpException(404, 'Not found');
        }

        $row = Database::one("SELECT * FROM pages WHERE slug = ? AND type = 'custom' AND is_published = 1 LIMIT 1", [$slug]);
        if ($row === null) {
            throw new HttpException(404, 'Not found');
        }
        $template = in_array((string) $row['template'], ['default', 'wide', 'landing'], true) ? (string) $row['template'] : 'default';

        // sub-pages: published custom pages that name this page as their parent
        $children = Database::all(
            "SELECT slug, title, nav_label, subtitle FROM pages WHERE type = 'custom' AND is_published = 1 AND parent_slug = ? ORDER BY nav_order ASC, title ASC",
            [$slug]
        );

        // breadcrumb trail: parent (when it is a published page we know) > this page
        $crumbs = [];
        $parent = $row['parent_slug'] !== null && $row['parent_slug'] !== '' ? SitePages::get((string) $row['parent_slug']) : null;
        if ($parent !== null && $parent['is_published'] && !$parent['virtual'] && $parent['url'] !== '') {
            $crumbs[] = [(string) $parent['nav_label'], $parent['url']];
        }
        $crumbs[] = [(string) $row['title'], null];

        $meta = ['canonical' => '/' . $slug];
        if (trim((string) ($row['og_image'] ?? '')) === '' && trim((string) ($row['hero_image'] ?? '')) !== '') {
            $meta['og_image'] = (string) $row['hero_image'];        // share the hero photo when no dedicated social image was set
        }
        if (trim((string) ($row['seo_description'] ?? '')) === '') {
            $meta['description'] = trim((string) ($row['subtitle'] ?? '')) !== '' ? (string) $row['subtitle'] : excerpt((string) ($row['body'] ?? ''), 175);
        }

        return $this->render('web/custom/show', [
            'row'      => $row,
            'template' => $template,
            'children' => $children,
        ], [
            'page'        => $slug,
            'bodyClass'   => 'is-custom template-' . $template,
            'meta'        => $meta,
            'breadcrumbs' => $crumbs,
        ]);
    }
}
