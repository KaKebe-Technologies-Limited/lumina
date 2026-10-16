<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Post;

/**
 * GET /news, /news/{slug}, /press, /press/{slug} - posts of type "news" and "press" (blog posts are /blog, owned by B2).
 * OWNED BY B7 "org-public". Both listings use the registry page "news" (app/config/pages/news.php); /press has its own banner copy
 * (section press_hero) and its own SEO text, and is hidden together with the page when the owner unpublishes "News & Press".
 *
 * Only status = 'published' posts whose published_at has passed are read. A slug that belongs to another post type is redirected
 * (301) to its own URL instead of being shown under the wrong section.
 */
final class NewsController extends OrgPublicController
{
    private const PER_PAGE = 9;

    public function index(Request $request): Response
    {
        return $this->listing($request, 'news');
    }

    public function press(Request $request): Response
    {
        return $this->listing($request, 'press');
    }

    public function show(Request $request, string $slug): Response
    {
        return $this->article('news', $slug);
    }

    public function pressShow(Request $request, string $slug): Response
    {
        return $this->article('press', $slug);
    }

    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    private function visible(string $type): array
    {
        return ['post_type' => $type, 'status' => 'published', '@raw' => ['published_at <= NOW()', []]];
    }

    private function listing(Request $request, string $type): Response
    {
        $conds = $this->visible($type);

        $categories = [];
        foreach (Database::all(
            "SELECT DISTINCT category FROM posts WHERE post_type = ? AND status = 'published' AND published_at <= NOW() AND category IS NOT NULL AND category <> '' ORDER BY category ASC",
            [$type]
        ) as $row) {
            $categories[] = (string) $row['category'];
        }
        $category = $request->string('category');
        if ($category !== '' && !in_array($category, $categories, true)) {
            $category = '';
        }
        if ($category !== '') {
            $conds['category'] = $category;
        }

        $pagination = Post::paginate($request->page(), self::PER_PAGE, $conds, 'published_at DESC, id DESC');
        $otherType = $type === 'news' ? 'press' : 'news';
        $otherCount = Post::count($this->visible($otherType));

        $opts = [
            'breadcrumbs' => $type === 'news'
                ? $this->crumbs('news')
                : $this->crumbs('news', [[(string) content('news', 'press_hero.title'), null]]),
        ];
        if ($type === 'press') {
            $opts['meta'] = [
                'title'       => (string) content('news', 'press_hero.seo_title'),
                'description' => (string) content('news', 'press_hero.seo_description'),
                'canonical'   => 'press',
            ];
        }

        return $this->renderPage('news', 'web/news/index', [
            'type'       => $type,
            'posts'      => $pagination->items,
            'pagination' => $pagination,
            'categories' => $categories,
            'category'   => $category,
            'otherCount' => $otherCount,
        ], $opts);
    }

    private function article(string $type, string $slug): Response
    {
        $post = Post::publishedBySlug($slug);
        if ($post === null) {
            throw new HttpException(404);
        }
        if ((string) $post['post_type'] !== $type) {
            return Response::redirect(url((string) $post['post_type'] . '/' . $post['slug']), 301);
        }

        $others = Post::where($this->visible($type) + ['id !=' => (int) $post['id']], 'published_at DESC, id DESC', 3);

        $excerpt = trim((string) ($post['excerpt'] ?? ''));
        $tail = [[(string) $post['title'], null]];
        if ($type === 'press') {
            array_unshift($tail, [(string) content('news', 'press_hero.title'), url('press')]);
        }

        return $this->renderPage('news', 'web/news/show', [
            'type'      => $type,
            'post'      => $post,
            'others'    => $others,
            'external'  => $this->safeUrl($post['external_url'] ?? ''),
        ], [
            'meta' => [
                'title'       => trim((string) ($post['seo_title'] ?? '')) !== '' ? (string) $post['seo_title'] : (string) $post['title'],
                'description' => trim((string) ($post['seo_description'] ?? '')) !== '' ? (string) $post['seo_description'] : ($excerpt !== '' ? $excerpt : excerpt((string) ($post['body'] ?? ''), 175)),
                'og_image'    => (string) ($post['cover_image'] ?? ''),
                'og_type'     => 'article',
            ],
            'breadcrumbs' => $this->crumbs('news', $tail),
            'bodyClass'   => 'is-detail',
        ]);
    }
}
