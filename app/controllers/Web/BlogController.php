<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Models\GalleryImage;
use App\Models\Post;
use App\Services\Web\ShareLinks;

/**
 * GET /blog (paginated list of PUBLISHED posts of type "blog" whose published_at is not in the future, optional ?category=)
 * GET /blog/{slug} (post: sanitised body, share links, related posts, older/newer navigation, newsletter box).
 * Copy around the posts comes from the "blog" registry page; the posts come from the posts table (admin: Content > Posts).
 */
final class BlogController extends Controller
{
    private const PER_PAGE = 9;
    private const PUBLISHED = "post_type = 'blog' AND status = 'published' AND published_at IS NOT NULL AND published_at <= NOW()";

    public function index(Request $request): Response
    {
        $category = mb_substr($request->string('category'), 0, 80);
        $conds = ['post_type' => 'blog', 'status' => 'published', '@raw' => ['published_at IS NOT NULL AND published_at <= NOW()', []]];
        if ($category !== '') {
            $conds['category'] = $category;
        }
        $pagination = Post::paginate($request->page(), self::PER_PAGE, $conds, 'published_at DESC, id DESC');

        $categories = Database::all(
            'SELECT category, COUNT(*) AS total FROM posts WHERE ' . self::PUBLISHED . " AND category IS NOT NULL AND category <> '' GROUP BY category ORDER BY category"
        );

        $gallery = [];
        if (section_on('blog', 'gallery')) {
            try {
                $gallery = GalleryImage::where(['is_active' => 1], 'sort_order ASC, id DESC', 6);
            } catch (\Throwable) {
                $gallery = [];
            }
        }

        return $this->renderPage('blog', 'web/blog/index', [
            'pagination' => $pagination,
            'posts'      => $pagination->items,
            'categories' => $categories,
            'category'   => $category,
            'gallery'    => $gallery,
        ], [
            'bodyClass'   => 'is-blog-index',
            'breadcrumbs' => [[$this->navLabel(), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        if (!SitePages::isPublished('blog')) {
            throw new HttpException(404, 'Not found');
        }
        $post = Post::first(['slug' => $slug, 'post_type' => 'blog', 'status' => 'published', '@raw' => ['published_at IS NOT NULL AND published_at <= NOW()', []]]);
        if ($post === null) {
            throw new HttpException(404, 'Not found');
        }

        $when = (string) $post['published_at'];
        $older = Database::one(
            'SELECT slug, title FROM posts WHERE ' . self::PUBLISHED . ' AND (published_at < ? OR (published_at = ? AND id < ?)) ORDER BY published_at DESC, id DESC LIMIT 1',
            [$when, $when, (int) $post['id']]
        );
        $newer = Database::one(
            'SELECT slug, title FROM posts WHERE ' . self::PUBLISHED . ' AND (published_at > ? OR (published_at = ? AND id > ?)) ORDER BY published_at ASC, id ASC LIMIT 1',
            [$when, $when, (int) $post['id']]
        );
        $cat = trim((string) ($post['category'] ?? ''));
        $related = $cat !== ''
            ? Database::all(
                'SELECT * FROM posts WHERE ' . self::PUBLISHED . ' AND id <> ? ORDER BY (category = ?) DESC, published_at DESC, id DESC LIMIT 3',
                [(int) $post['id'], $cat]
            )
            : Database::all(
                'SELECT * FROM posts WHERE ' . self::PUBLISHED . ' AND id <> ? ORDER BY published_at DESC, id DESC LIMIT 3',
                [(int) $post['id']]
            );

        $description = trim((string) ($post['seo_description'] ?? ''));
        if ($description === '') {
            $description = trim((string) ($post['excerpt'] ?? ''));
        }
        if ($description === '') {
            $description = excerpt((string) $post['body'], 175);
        }
        $title = trim((string) ($post['seo_title'] ?? '')) !== '' ? (string) $post['seo_title'] : (string) $post['title'];
        $absUrl = abs_url('blog/' . $post['slug']);

        return $this->renderPage('blog', 'web/blog/show', [
            'post'    => $post,
            'older'   => $older,
            'newer'   => $newer,
            'related' => $related,
            'absUrl'  => $absUrl,
            'share'   => ShareLinks::for($absUrl, (string) $post['title']),
        ], [
            'bodyClass'   => 'is-post',
            'meta'        => [
                'title'       => $title,
                'description' => $description,
                'og_image'    => (string) ($post['cover_image'] ?? ''),
                'og_type'     => 'article',
                'canonical'   => '/blog/' . $post['slug'],
            ],
            'breadcrumbs' => [[$this->navLabel(), url('blog')], [(string) $post['title'], null]],
        ]);
    }

    private function navLabel(): string
    {
        return (string) (SitePages::get('blog')['nav_label'] ?? 'Blog');
    }
}
