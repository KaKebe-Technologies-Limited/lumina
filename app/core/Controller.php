<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Base class for PUBLIC controllers (app/controllers/Web/*). Admin controllers extend App\Core\AdminController.
 * The router instantiates controllers as `new X($request)` and calls `$controller->method(Request $request, ...$routeParams)`.
 *
 *   class AboutController extends Controller {
 *       public function index(Request $request): Response {
 *           return $this->renderPage('about', 'web/about/index', ['team' => TeamMember::where(['is_active' => 1])]);
 *       }
 *       public function show(Request $request, string $slug): Response {          // detail page with its own SEO
 *           $post = Post::findBy('slug', $slug) ?? throw new HttpException(404);
 *           return $this->renderPage('blog', 'web/blog/show', ['post' => $post], [
 *               'meta'        => ['title' => $post['title'], 'description' => $post['excerpt'], 'og_image' => $post['cover_image'], 'og_type' => 'article'],
 *               'breadcrumbs' => [['Blog', url('blog')], [$post['title'], null]],
 *           ]);
 *       }
 *   }
 *
 * render()/renderPage() $opts: layout (default 'layouts/main'), status, meta (array merged over page_meta), breadcrumbs
 *   (list of [label, url|null]), bodyClass, page (slug; renderPage sets it), title (admin topbar title), nav (admin sidebar key).
 * Views receive: $request, $page (slug|null), $meta, $breadcrumbs, $bodyClass, plus your $data.
 */
abstract class Controller
{
    protected string $layout = 'layouts/main';

    public function __construct(protected Request $request)
    {
    }

    /** Render a view inside the layout (no published check, no page meta). */
    protected function render(string $view, array $data = [], array $opts = []): Response
    {
        $layout = array_key_exists('layout', $opts) ? $opts['layout'] : $this->layout;
        $page = $opts['page'] ?? null;
        $meta = page_meta($page ?? '', (array) ($opts['meta'] ?? []));
        $shared = [
            'request'     => $this->request,
            'page'        => $page,
            'meta'        => $meta,
            'breadcrumbs' => (array) ($opts['breadcrumbs'] ?? []),
            'bodyClass'   => (string) ($opts['bodyClass'] ?? ''),
            'pageTitle'   => $opts['title'] ?? null,
            'navKey'      => $opts['nav'] ?? null,
        ];
        View::share($shared);
        $html = View::render($view, $data, $layout);
        return Response::html($html, (int) ($opts['status'] ?? 200));
    }

    /**
     * Render a CMS-managed public page: 404 when the page is unpublished, SEO from page_meta($slug) (+ overrides in $opts['meta']),
     * and views can call content($slug, 'section.field') etc.
     */
    protected function renderPage(string $slug, string $view, array $data = [], array $opts = []): Response
    {
        if (!SitePages::isPublished($slug)) {
            throw new HttpException(404, 'Not found');
        }
        $opts['page'] = $slug;
        $data += ['pageSlug' => $slug];
        return $this->render($view, $data, $opts);
    }

    protected function redirect(string $path, int $status = 302): Response
    {
        return Response::redirect(preg_match('~^https?://~i', $path) ? $path : url($path), $status);
    }

    /** Redirect to the (same-origin) referer, else $fallback path. */
    protected function back(string $fallback = ''): Response
    {
        $ref = $this->request->referer();
        if ($ref !== null && strtolower((string) parse_url($ref, PHP_URL_HOST)) === strtolower(explode(':', $this->request->host())[0])) {
            return Response::redirect($ref);
        }
        return Response::redirect(url($fallback));
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function abort(int $status, string $message = ''): never
    {
        throw new HttpException($status, $message);
    }

    /**
     * Validate request input (POST + query). On failure throws ValidationException: App::run() flashes errors + old input
     * and redirects back (or returns 422 JSON for AJAX). On success returns the validated (trimmed) values.
     * @param array<string,string|array> $rules
     */
    protected function validate(array $rules, array $messages = [], array $labels = [], ?string $redirectTo = null): array
    {
        $input = $this->request->all();
        $v = Validator::make($input, $rules, $messages, $labels);
        if ($v->fails()) {
            throw new ValidationException($v->errors(), self::scrubOld($input), $redirectTo);
        }
        return $v->validated();
    }

    /** Throw a validation failure manually: $this->fail(['email' => 'Already registered.']). */
    protected function fail(array $errors, ?string $redirectTo = null): never
    {
        throw new ValidationException($errors, self::scrubOld($this->request->all()), $redirectTo);
    }

    public static function scrubOld(array $input): array
    {
        $drop = ['_token', '_method', '_hp_ts', (string) Config::get('forms.honeypot_field', 'website')];
        foreach ($input as $k => $v) {
            if (in_array($k, $drop, true) || str_contains((string) $k, 'password')) {
                unset($input[$k]);
            }
        }
        return $input;
    }
}
