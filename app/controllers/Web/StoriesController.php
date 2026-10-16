<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Story;

/**
 * GET /stories and GET /stories/{slug}.
 * OWNED BY B7 "org-public". Copy comes from the registry page "stories" (app/config/pages/stories.php).
 *
 * CONSENT RULE: only rows that are active AND consent_confirmed = 1 AND already published are ever read by this controller.
 * A story without confirmed consent is a plain 404 - it is never shown, listed, or exposed through the detail URL.
 */
final class StoriesController extends OrgPublicController
{
    private const PER_PAGE = 9;

    /** The one place that defines what "publishable" means for this page. */
    private function visible(): array
    {
        return ['is_active' => 1, 'consent_confirmed' => 1, '@raw' => ['published_at IS NULL OR published_at <= NOW()', []]];
    }

    public function index(Request $request): Response
    {
        $pagination = Story::paginate($request->page(), self::PER_PAGE, $this->visible(), 'published_at DESC, id DESC');

        return $this->renderPage('stories', 'web/stories/index', [
            'stories'    => $pagination->items,
            'pagination' => $pagination,
        ], [
            'breadcrumbs' => $this->crumbs('stories'),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $story = Story::first(['slug' => $slug] + $this->visible());
        if ($story === null || (int) $story['consent_confirmed'] !== 1) {
            throw new HttpException(404);
        }

        $others = Story::where($this->visible() + ['id !=' => (int) $story['id']], 'published_at DESC, id DESC', 3);

        $summary = trim((string) ($story['summary'] ?? ''));
        return $this->renderPage('stories', 'web/stories/show', [
            'story'  => $story,
            'others' => $others,
        ], [
            'meta' => [
                'title'       => trim((string) ($story['seo_title'] ?? '')) !== '' ? (string) $story['seo_title'] : (string) $story['title'],
                'description' => trim((string) ($story['seo_description'] ?? '')) !== '' ? (string) $story['seo_description'] : ($summary !== '' ? $summary : excerpt((string) ($story['body'] ?? ''), 175)),
                'og_image'    => (string) ($story['image'] ?? ''),
                'og_type'     => 'article',
            ],
            'breadcrumbs' => $this->crumbs('stories', [[(string) $story['title'], null]]),
            'bodyClass'   => 'is-detail',
        ]);
    }
}
