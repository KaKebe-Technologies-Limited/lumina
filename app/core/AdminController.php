<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Base class for every ADMIN controller (app/controllers/Admin/*). Routes must sit inside the 'auth' middleware group
 * (see routes/admin_*.php). Adds the admin layout, anti-cache/noindex headers, ability checks and audit helpers.
 *
 *   class PostController extends AdminController {
 *       public function index(Request $request): Response {
 *           $this->authorize('content.manage');
 *           return $this->render('admin/posts/index', ['rows' => ...], [
 *               'title' => 'Posts',                     // <title> + topbar heading
 *               'nav'   => 'posts',                     // active sidebar item key (see config/admin_nav/<area>.php)
 *               'breadcrumbs' => [['Posts', null]],     // after the implicit "Dashboard"
 *           ]);
 *       }
 *       public function store(Request $request): Response {
 *           $data = $this->validate(['title' => 'required|max:200']);
 *           $id = Post::create([...]);
 *           $this->audit('post.create', 'post', $id, 'Created post "' . $data['title'] . '"');
 *           Flash::success('Post created.');
 *           return $this->redirect('admin/posts');
 *       }
 *   }
 */
abstract class AdminController extends Controller
{
    protected string $layout = 'layouts/admin';

    protected function render(string $view, array $data = [], array $opts = []): Response
    {
        $opts += ['meta' => []];
        $opts['meta'] = ['title' => (string) ($opts['title'] ?? 'Admin'), 'robots' => 'noindex,nofollow'] + (array) $opts['meta'];
        $response = parent::render($view, $data, $opts);
        return $response->withHeaders([
            'Cache-Control' => 'no-store, max-age=0',
            'X-Robots-Tag'  => 'noindex, nofollow',
        ]);
    }

    /** Abort with 403 unless the current user holds the ability (see config app.abilities). */
    protected function authorize(string ...$abilities): void
    {
        foreach ($abilities as $a) {
            if (!can($a)) {
                throw new HttpException(403, 'Forbidden');
            }
        }
    }

    protected function user(): array
    {
        return Auth::user() ?? throw new HttpException(401, 'Unauthenticated');
    }

    protected function userId(): ?int
    {
        return Auth::id();
    }

    protected function audit(string $action, ?string $entityType = null, int|string|null $entityId = null, string $summary = '', array $details = []): void
    {
        AuditLog::record($action, $entityType, $entityId, $summary, $details);
    }

    /** Whitelisted page size for admin tables (?per_page=10|20|50|100). */
    protected function perPage(): int
    {
        $n = $this->request->int('per_page', (int) Config::get('pagination.admin_per_page', 20));
        return in_array($n, [10, 20, 50, 100], true) ? $n : (int) Config::get('pagination.admin_per_page', 20);
    }
}
