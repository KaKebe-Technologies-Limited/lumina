<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Route registration + dispatch. Route files (app/routes/*.php) get a $router variable in scope.
 *
 *   $router->get('/about', [AboutController::class, 'index']);
 *   $router->get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
 *   $router->post('/contact', [ContactController::class, 'send'])->middleware('throttle:5,60');
 *   $router->get('/hello', function (Request $request) { return 'hi'; });
 *   $router->get('/x', 'App\Controllers\Web\XController@index');   // "Class@method" string also accepted
 *
 *   $router->group(['prefix' => 'admin', 'middleware' => ['auth']], function (Router $router) {
 *       $router->get('/posts', [PostController::class, 'index']);                  // /admin/posts
 *       $router->group(['middleware' => ['role:admin']], function (Router $router) { ... });
 *   });
 *
 * Handlers are called as $controller->method(Request $request, ...$routeParamsInPathOrder).
 * `{id}` params are cast to int. Handlers may return Response | string (HTML) | array (JSON) | null.
 *
 * Matching: static routes beat dynamic ones regardless of the file they were registered in.
 * CSRF is verified AUTOMATICALLY for every POST/PUT/PATCH/DELETE (see dispatch()).
 */
final class Router
{
    /** @var Route[] */
    private array $routes = [];
    /** @var array<int,array{prefix:string,middleware:string[]}> */
    private array $stack = [];
    /** @var array<string,Route> */
    private static array $named = [];
    private mixed $fallback = null;

    public static function registerName(string $name, Route $route): void
    {
        self::$named[$name] = $route;
    }

    /** Path (no base path) for a named route. Use route() helper for a full URL. */
    public static function path(string $name, array $params = []): string
    {
        if (!isset(self::$named[$name])) {
            throw new \InvalidArgumentException("Unknown route name: {$name}");
        }
        return self::$named[$name]->build($params);
    }

    public function get(string $path, mixed $handler): Route
    {
        return $this->add(['GET', 'HEAD'], $path, $handler);
    }

    public function post(string $path, mixed $handler): Route
    {
        return $this->add(['POST'], $path, $handler);
    }

    public function put(string $path, mixed $handler): Route
    {
        return $this->add(['PUT'], $path, $handler);
    }

    public function patch(string $path, mixed $handler): Route
    {
        return $this->add(['PATCH'], $path, $handler);
    }

    public function delete(string $path, mixed $handler): Route
    {
        return $this->add(['DELETE'], $path, $handler);
    }

    public function any(string $path, mixed $handler): Route
    {
        return $this->add(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'], $path, $handler);
    }

    /** @param string[] $methods */
    public function match(array $methods, string $path, mixed $handler): Route
    {
        return $this->add(array_map('strtoupper', $methods), $path, $handler);
    }

    /**
     * Register the catch-all used when NO route matches a GET/HEAD request (routes/zz_custom_pages.php).
     * The handler receives (Request $request) and MUST throw HttpException(404) if it has nothing to show.
     */
    public function fallback(mixed $handler): void
    {
        $this->fallback = $handler;
    }

    /** @param array{prefix?:string,middleware?:string|string[]} $attrs */
    public function group(array $attrs, callable $callback): void
    {
        $this->stack[] = [
            'prefix'     => trim((string) ($attrs['prefix'] ?? ''), '/'),
            'middleware' => (array) ($attrs['middleware'] ?? []),
        ];
        try {
            $callback($this);
        } finally {
            array_pop($this->stack);
        }
    }

    private function add(array $methods, string $path, mixed $handler): Route
    {
        $prefix = [];
        $middleware = [];
        foreach ($this->stack as $g) {
            if ($g['prefix'] !== '') {
                $prefix[] = $g['prefix'];
            }
            $middleware = array_merge($middleware, $g['middleware']);
        }
        $full = '/' . trim(implode('/', $prefix) . '/' . trim($path, '/'), '/');
        $route = new Route($methods, $full, $handler, $middleware);
        $this->routes[] = $route;
        return $route;
    }

    /** @return Route[] */
    public function routes(): array
    {
        return $this->routes;
    }

    public function dispatch(Request $request): Response
    {
        $path = $request->path();
        $method = $request->method() === 'HEAD' ? 'GET' : $request->method();

        // static routes before dynamic ones, otherwise in registration order (stable)
        $ordered = $this->routes;
        usort($ordered, static fn(Route $a, Route $b) => $a->specificity() <=> $b->specificity());

        $allowed = [];
        $matched = null;
        $params = [];
        foreach ($ordered as $route) {
            $p = $route->match($path);
            if ($p === null) {
                continue;
            }
            if (in_array($method, $route->methods, true) || ($method === 'GET' && in_array('HEAD', $route->methods, true))) {
                $matched = $route;
                $params = $p;
                break;
            }
            $allowed = array_merge($allowed, $route->methods);
        }

        if ($matched === null && $this->fallback !== null && $method === 'GET') {
            // catch-all (custom CMS pages). The handler must throw HttpException(404) when it has nothing to show.
            $fb = $this->fallback;
            $core = fn(Request $request): Response => $this->toResponse($this->invoke($fb, $request, []));
            return $this->toResponse(Middleware::run([], $request, $core));
        }

        if ($matched === null) {
            if ($allowed !== []) {
                throw new HttpException(405, 'Method not allowed', ['Allow' => implode(', ', array_unique($allowed))]);
            }
            throw new HttpException(404, 'Not found');
        }

        foreach ($params as $k => $v) {
            if ($k === 'id' || (isset(Route::PATTERNS[$k]) && Route::PATTERNS[$k] === '\d+')) {
                $params[$k] = (int) $v;
            }
        }
        $request->setParams(array_map('strval', $params));

        $middleware = $matched->middleware;
        if ($request->isUnsafe() && !in_array('csrf', $middleware, true)) {
            array_unshift($middleware, 'csrf');          // global CSRF protection
        }

        $core = function (Request $request) use ($matched, $params): Response {
            return $this->toResponse($this->invoke($matched->handler, $request, array_values($params)));
        };

        return $this->toResponse(Middleware::run($middleware, $request, $core));
    }

    private function invoke(mixed $handler, Request $request, array $args): mixed
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $handler = [$class, $method];
        }
        if (is_array($handler) && is_string($handler[0])) {
            if (!class_exists($handler[0])) {
                throw new \RuntimeException("Controller not found: {$handler[0]}");
            }
            $handler = [new $handler[0]($request), $handler[1]];
        }
        if (!is_callable($handler)) {
            throw new \RuntimeException('Route handler is not callable.');
        }
        return $handler($request, ...$args);
    }

    private function toResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }
        if (is_array($result)) {
            return Response::json($result);
        }
        if (is_string($result)) {
            return Response::html($result);
        }
        return Response::html('');
    }
}
