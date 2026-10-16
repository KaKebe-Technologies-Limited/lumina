<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Content\CrudController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\Post;

/**
 * Admin for `posts` (/admin/posts): blog posts, news items and press coverage (?type=blog|news|press).
 * The database knows two statuses (draft | published); "scheduled" is a published post whose published_at is still in the future,
 * which is exactly what the public queries (published_at <= NOW()) already honour. OWNER: B4. Engine: Content\CrudController.
 */
final class PostController extends CrudController
{
    public const TYPE_LABELS = ['blog' => 'Blog', 'news' => 'News', 'press' => 'Press'];
    public const STATE_LABELS = ['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled'];

    protected function config(): array
    {
        return [
            'entity' => 'post', 'table' => 'posts', 'singular' => 'post', 'plural' => 'posts', 'title' => 'Posts',
            'path' => 'admin/posts', 'views' => 'admin/posts', 'nav' => 'posts', 'has_active' => false, 'tab_param' => 'type',
            'search' => ['title', 'excerpt', 'category', 'author_name', 'slug', 'source_name'],
            'sorts' => [
                'date'    => 'COALESCE(t.published_at, t.created_at) {dir}, t.id {dir}',
                'title'   => 't.title {dir}, t.id ASC',
                'type'    => 't.post_type {dir}, COALESCE(t.published_at, t.created_at) DESC',
                'updated' => 't.updated_at {dir}, t.id {dir}',
            ],
            'default_sort' => ['date', 'desc'],
            'order_sql' => 'id ASC',
            'reorderable' => false,
            'public' => fn(array $r): ?string => $this->publicPath($r),
        ];
    }

    /** Public path of a live post (null while draft / scheduled). */
    private function publicPath(array $r): ?string
    {
        if (self::state($r) !== 'published') {
            return null;
        }
        if ($r['post_type'] === 'press' && !empty($r['external_url'])) {
            return null;                                   // press coverage links out
        }
        return $r['post_type'] . '/' . $r['slug'];
    }

    /** draft | scheduled | published for a posts row (needs published_at). */
    public static function state(array $r): string
    {
        if (($r['status'] ?? 'draft') !== 'published') {
            return 'draft';
        }
        $at = (string) ($r['published_at'] ?? '');
        return ($at !== '' && $at > date('Y-m-d H:i:s')) ? 'scheduled' : 'published';
    }

    protected function richColumns(): array
    {
        return ['body'];
    }

    protected function imageFields(): array
    {
        return ['cover_image' => 'cover_alt'];
    }

    protected function defaults(): array
    {
        $type = request()?->string('type') ?? '';
        $user = Auth::user();
        return [
            'post_type' => isset(self::TYPE_LABELS[$type]) ? $type : 'blog', 'status' => 'draft', 'published_at' => null, 'is_featured' => 0,
            'author_name' => (string) ($user['name'] ?? ''), 'category' => '', 'sort_order' => 0,
        ];
    }

    protected function tabs(): array
    {
        $tabs = ['all' => ['label' => 'All types', 'sql' => null, 'params' => []]];
        foreach (self::TYPE_LABELS as $k => $label) {
            $tabs[$k] = ['label' => $label, 'sql' => 't.post_type = ?', 'params' => [$k]];
        }
        return $tabs;
    }

    protected function applyFilters(Request $request, array &$where, array &$params): array
    {
        $state = $request->string('state');
        if ($state === 'draft') {
            $where[] = "t.status = 'draft'";
        } elseif ($state === 'published') {
            $where[] = "t.status = 'published' AND t.published_at <= NOW()";
        } elseif ($state === 'scheduled') {
            $where[] = "t.status = 'published' AND t.published_at > NOW()";
        } else {
            return [];
        }
        return ['state' => $state];
    }

    protected function listSelect(): string
    {
        return "t.id, t.post_type, t.slug, t.title, t.excerpt, t.cover_image, t.cover_alt, t.category, t.status, t.published_at, t.author_name, t.external_url, t.source_name, t.is_featured, t.created_at, t.updated_at,
                (t.status = 'published' AND t.published_at > NOW()) AS is_scheduled,
                (t.status = 'published' AND t.published_at <= NOW()) AS is_live";
    }

    protected function listData(): array
    {
        return ['typeLabels' => self::TYPE_LABELS, 'stateLabels' => self::STATE_LABELS, 'typeNow' => $this->request->string('type')];
    }

    protected function formData(?array $row): array
    {
        $cats = array_column(Database::all("SELECT DISTINCT category FROM posts WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC LIMIT 200"), 'category');
        return [
            'typeLabels'  => self::TYPE_LABELS,
            'stateLabels' => self::STATE_LABELS,
            'categories'  => $cats,
            'state'       => $row === null ? 'draft' : self::state($row),
            'previewUrl'  => $row === null ? null : url('admin/posts/' . (int) $row['id'] . '/preview'),
        ];
    }

    protected function rules(?array $row): array
    {
        return [
            'rules' => [
                'post_type'       => 'required|in:' . implode(',', Post::TYPES),
                'title'           => 'required|max:220',
                'slug'            => 'nullable|max:190',
                'excerpt'         => 'nullable|max:500',
                'body'            => 'nullable|max:400000',
                'category'        => 'nullable|max:80',
                'status'          => 'required|in:draft,published,scheduled',
                'published_at'    => 'nullable|datetime',
                'author_name'     => 'nullable|max:120',
                'source_name'     => 'nullable|max:190',
                'external_url'    => 'nullable|url|max:500',
                'is_featured'     => 'nullable|boolean',
                'seo_title'       => 'nullable|max:190',
                'seo_description' => 'nullable|max:300',
            ],
            'labels' => ['post_type' => 'Type', 'title' => 'Title', 'published_at' => 'Publish date', 'author_name' => 'Author', 'source_name' => 'Source',
                'external_url' => 'Link to the original', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description', 'status' => 'Status'],
            'messages' => [
                'post_type.in' => 'Please choose blog, news or press.',
                'status.in' => 'Please choose draft, published or scheduled.',
                'published_at.datetime' => 'Please enter a valid date and time.',
                'external_url.url' => 'Please enter the full address, starting with https://',
            ],
        ];
    }

    protected function payload(Request $request, ?array $row, array $valid): array
    {
        $type = (string) $valid['post_type'];
        $status = (string) $valid['status'];
        $title = (string) $this->text($valid['title']);
        $body = $this->html($request->post('body'));
        $externalUrl = ($valid['external_url'] ?? '') !== '' ? (string) $valid['external_url'] : null;
        $now = (string) Database::value('SELECT NOW()', [], date('Y-m-d H:i:s'));

        // publish date: typed value (datetime-local) -> 'Y-m-d H:i:s'
        $typed = null;
        $raw = trim((string) ($valid['published_at'] ?? ''));
        if ($raw !== '') {
            $typed = str_replace('T', ' ', $raw);
            if (strlen($typed) === 16) {
                $typed .= ':00';
            }
        }

        if ($status !== 'draft') {
            if ($type === 'press') {
                if ($externalUrl === null && $body === null) {
                    $this->fail(['external_url' => 'For press coverage, add the link to the original article or write the text here.']);
                }
            } elseif ($body === null) {
                $this->fail(['body' => 'Write the text of the post before publishing it, or save it as a draft.']);
            }
        }

        if ($status === 'scheduled') {
            if ($typed === null || $typed <= $now) {
                $this->fail(['published_at' => 'Choose a date and time in the future for a scheduled post.']);
            }
            $publishedAt = $typed;
        } elseif ($status === 'published') {
            if ($typed !== null) {
                $publishedAt = $typed;
            } else {
                $old = (string) ($row['published_at'] ?? '');
                $publishedAt = ($old !== '' && $old <= $now) ? $old : $now;
            }
        } else {
            $publishedAt = $typed ?? ($row['published_at'] ?? null);
        }

        $user = Auth::user();
        $author = $this->text($valid['author_name']);
        if ($row === null && $author === null) {
            $author = (string) ($user['name'] ?? '') ?: null;
        }

        $data = [
            'post_type'       => $type,
            'slug'            => $this->slugFor($request, $row, $title),
            'title'           => $title,
            'excerpt'         => $this->plain($valid['excerpt']),
            'body'            => $body,
            'category'        => $this->text($valid['category']),
            'status'          => $status === 'draft' ? 'draft' : 'published',
            'published_at'    => $publishedAt,
            'author_name'     => $author,
            'source_name'     => $type === 'press' ? $this->text($valid['source_name']) : null,
            'external_url'    => $type === 'press' ? $externalUrl : null,
            'is_featured'     => $request->bool('is_featured') ? 1 : 0,
            'seo_title'       => $this->text($valid['seo_title']),
            'seo_description' => $this->text($valid['seo_description']),
        ];
        if ($row === null) {
            $data['author_id'] = Auth::id();
        } elseif (empty($row['author_id'])) {
            $data['author_id'] = Auth::id();
        }
        return $data;
    }

    protected function saved(string $mode, int $id, array $row, ?array $before): void
    {
        $after = self::state($row + ['published_at' => $row['published_at'] ?? null]);
        $was = $before === null ? 'draft' : self::state($before);
        if ($after !== $was || $mode === 'create') {
            if ($after === 'published') {
                $this->audit('post.publish', 'post', $id, 'Published post "' . ($row['title'] ?? '') . '"');
            } elseif ($after === 'scheduled') {
                $this->audit('post.schedule', 'post', $id, 'Scheduled post "' . ($row['title'] ?? '') . '" for ' . ($row['published_at'] ?? ''));
            } elseif ($mode === 'update') {
                $this->audit('post.unpublish', 'post', $id, 'Moved post "' . ($row['title'] ?? '') . '" back to draft');
            }
        }
    }

    /** Quick publish / unpublish from the list (POST {id}/toggle). */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = $this->find($id);
        $state = self::state($row);
        $now = (string) Database::value('SELECT NOW()', [], date('Y-m-d H:i:s'));
        if ($state === 'draft') {
            if (($row['post_type'] ?? '') === 'press' ? (empty($row['external_url']) && empty($row['body'])) : empty($row['body'])) {
                Flash::error('"' . $row['title'] . '" has no text yet. Open it and write the post before publishing.');
                return Response::redirect($this->returnUrl($request, 'admin/posts'));
            }
            $old = (string) ($row['published_at'] ?? '');
            Database::update('posts', ['status' => 'published', 'published_at' => ($old !== '' && $old <= $now) ? $old : $now], 'id = ?', [$id]);
            $this->audit('post.publish', 'post', $id, 'Published post "' . $row['title'] . '"');
            $msg = '"' . $row['title'] . '" is now published.';
            $live = true;
        } else {
            Database::update('posts', ['status' => 'draft'], 'id = ?', [$id]);
            $this->audit('post.unpublish', 'post', $id, 'Moved post "' . $row['title'] . '" back to draft');
            $msg = '"' . $row['title'] . '" is back to draft and no longer visible on the website.';
            $live = false;
        }
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'id' => $id, 'live' => $live]);
        }
        Flash::success($msg);
        return Response::redirect($this->returnUrl($request, 'admin/posts'));
    }

    /** GET {id}/preview - shows a draft / scheduled / live post inside the public layout (never indexed). */
    public function preview(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = $this->find($id);
        $state = self::state($row);
        $typeLabel = self::TYPE_LABELS[$row['post_type']] ?? 'Post';
        return $this->render('admin/posts/preview', [
            'post' => $row, 'state' => $state, 'stateLabel' => self::STATE_LABELS[$state], 'typeLabel' => $typeLabel,
            'editUrl' => url('admin/posts/' . $id . '/edit'),
        ], [
            'layout' => 'layouts/main',
            'title' => 'Preview: ' . $row['title'],
            'meta' => ['title' => 'Preview: ' . $row['title'], 'description' => '', 'robots' => 'noindex,nofollow'],
            'breadcrumbs' => [[$typeLabel, null], [(string) $row['title'], null]],
            'bodyClass' => 'page-post-preview',
        ]);
    }
}
