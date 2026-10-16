<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Named middleware registry. A middleware is callable(Request $request, callable $next, string ...$args): Response.
 * Reference them in route definitions by name, with optional comma separated arguments after a colon:
 *
 *   'csrf'            verify CSRF token (applied automatically to every POST/PUT/PATCH/DELETE)
 *   'auth'            must be logged in (admin area); redirects to /admin/login; forces password change if flagged
 *   'guest'           only for logged-out users (login page)
 *   'role:admin'      logged-in user must have one of the listed roles: 'role:admin' or 'role:admin,editor'
 *   'can:settings.manage'  logged-in user must hold the ability (config 'abilities' in app/config/app.php)
 *   'throttle:5,60'   at most 5 requests per 60 seconds per IP per path (429 afterwards). Defaults 10,60.
 *   'page.published:about'  404 unless the page 'about' is published (pages.is_published; a page with no row counts as published)
 *   'honeypot'        public form spam trap: if Honeypot::passes() fails the request is silently accepted-and-dropped
 */
final class Middleware
{
    /** @var array<string,callable> */
    private static array $registry = [];
    private static bool $booted = false;

    public static function register(string $name, callable $fn): void
    {
        self::$registry[$name] = $fn;
    }

    /** @param string[] $names */
    public static function run(array $names, Request $request, callable $core): mixed
    {
        self::boot();
        $next = $core;
        foreach (array_reverse($names) as $spec) {
            $name = $spec;
            $args = [];
            if (str_contains($spec, ':')) {
                [$name, $argStr] = explode(':', $spec, 2);
                $args = array_map('trim', explode(',', $argStr));
            }
            if (!isset(self::$registry[$name])) {
                throw new \RuntimeException("Unknown middleware: {$name}");
            }
            $next = self::wrap(self::$registry[$name], $next, $args);
        }
        return $next($request);
    }

    private static function wrap(callable $mw, callable $next, array $args): \Closure
    {
        return static fn(Request $request) => $mw($request, $next, ...$args);
    }

    /** Editable shared form wording (Admin > Pages > Error pages > Form messages) with a built-in fallback. */
    private static function formMessage(string $key, string $default): string
    {
        try {
            $v = content('site_errors', 'form.' . $key, $default);
            return is_string($v) && trim($v) !== '' ? trim($v) : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    private static function boot(): void
    {
        if (self::$booted) {
            return;
        }
        self::$booted = true;

        self::register('csrf', static function (Request $request, callable $next) {
            if ($request->isUnsafe() && !Csrf::verify($request)) {
                throw new HttpException(419, 'Page expired');
            }
            return $next($request);
        });

        self::register('auth', static function (Request $request, callable $next) {
            if (!Auth::check()) {
                if ($request->expectsJson()) {
                    return Response::json(['error' => 'Unauthenticated'], 401);
                }
                if ($request->isGet()) {
                    Session::put('intended', $request->uri());
                }
                Flash::info('Please sign in to continue.');
                return Response::redirect(url('admin/login'));
            }
            if (Auth::mustChangePassword()
                && !$request->pathIs('/admin/profile/password')
                && !$request->pathIs('/admin/logout')) {
                Flash::warning('Please set a new password before continuing.');
                return Response::redirect(url('admin/profile/password'));
            }
            return $next($request);
        });

        self::register('guest', static function (Request $request, callable $next) {
            if (Auth::check()) {
                return Response::redirect(url('admin'));
            }
            return $next($request);
        });

        self::register('role', static function (Request $request, callable $next, string ...$roles) {
            if (!Auth::check()) {
                return Response::redirect(url('admin/login'));
            }
            if (!Auth::hasRole(...$roles)) {
                throw new HttpException(403, 'Forbidden');
            }
            return $next($request);
        });

        self::register('can', static function (Request $request, callable $next, string ...$abilities) {
            foreach ($abilities as $a) {
                if (!can($a)) {
                    throw new HttpException(403, 'Forbidden');
                }
            }
            return $next($request);
        });

        self::register('page.published', static function (Request $request, callable $next, string $slug = '') {
            if ($slug !== '' && !SitePages::isPublished($slug)) {
                throw new HttpException(404, 'Not found');
            }
            return $next($request);
        });

        self::register('honeypot', static function (Request $request, callable $next) {
            if ($request->isPost() && !Honeypot::passes($request)) {
                Log::info('honeypot rejected a submission', ['path' => $request->path(), 'ip' => $request->ip()]);
                Flash::success(self::formMessage('honeypot_ok', 'Thank you. Your submission has been received.'));
                return Response::redirect(Honeypot::safeBackUrl($request));
            }
            return $next($request);
        });

        self::register('throttle', static function (Request $request, callable $next, string $max = '10', string $decay = '60') {
            $key = 'throttle:' . $request->ip() . ':' . $request->method() . ':' . $request->path();
            if (!RateLimit::attempt($key, (int) $max, (int) $decay)) {
                throw new HttpException(429, 'Too many requests', ['Retry-After' => (string) RateLimit::retryAfter($key)]);
            }
            return $next($request);
        });
    }
}
