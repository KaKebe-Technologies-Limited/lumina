<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\SitePages;

/**
 * Shared base of the organisation-public controllers (Team, Partners, Reports, Events, Stories, News).
 * OWNED BY B7 "org-public". It only holds tiny helpers so that the six controllers stay short; it registers no routes.
 */
abstract class OrgPublicController extends Controller
{
    /**
     * Breadcrumb trail for a registry page: [parent page (when the menu nests it, e.g. About > Our Team)] > the page itself,
     * followed by $tail (detail pages: [[title, null]]). Labels come from the editable menu labels, never from literals.
     *
     * @param array<int,array{0:string,1:?string}> $tail
     * @return array<int,array{0:string,1:?string}>
     */
    protected function crumbs(string $slug, array $tail = []): array
    {
        $crumbs = [];
        $page = SitePages::get($slug);
        $parentSlug = $page['parent_slug'] ?? null;
        if (is_string($parentSlug) && $parentSlug !== '' && $parentSlug !== $slug) {
            $parent = SitePages::get($parentSlug);
            if ($parent !== null && $parent['is_published'] && empty($parent['virtual']) && (string) $parent['url'] !== '') {
                $crumbs[] = [(string) $parent['nav_label'], (string) $parent['url']];
            }
        }
        $label = (string) ($page['nav_label'] ?? $page['title'] ?? ucfirst($slug));
        $crumbs[] = [$label, $tail !== [] ? (string) ($page['url'] ?? url($slug)) : null];
        return array_merge($crumbs, $tail);
    }

    /** Only http(s) links are ever printed from database columns (website, social, external article URLs). */
    protected function safeUrl(?string $value): string
    {
        $v = trim((string) $value);
        return preg_match('~^https?://[^\s"\'<>]+$~i', $v) === 1 ? $v : '';
    }
}
