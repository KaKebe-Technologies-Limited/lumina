<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Web entry: App::run() is called by index.php.
 *   capture request -> start session -> load app/routes/*.php (alphabetical, $router in scope) -> dispatch -> send.
 * HttpException / ValidationException / any Throwable are converted to responses here (friendly error pages;
 * details only when APP_DEBUG=1).
 */
final class App
{
    public static function run(): void
    {
        $request = Request::capture();
        try {
            self::guardHost($request);
            Session::start();      // lazy: only really starts when a session cookie exists (see Session)
            $router = new Router();
            self::loadRoutes($router);
            self::maintenanceGate($request);
            $response = self::oversizedPost($request) ?? $router->dispatch($request);
        } catch (ValidationException $e) {
            $response = self::validationResponse($e, $request);
        } catch (\Throwable $e) {
            $response = ErrorHandler::response($e, $request);
        }
        $response->send();
    }

    /**
     * Never trust the Host header outside local development: URLs in e-mails, canonical links and the sitemap are built from it.
     * Production needs APP_URL (the pinned origin); a request for any other host (not APP_URL / ALLOWED_HOSTS) gets 400.
     */
    private static function guardHost(Request $request): void
    {
        if ((string) Config::get('app.env', 'production') === 'local') {
            // local development: with APP_URL empty the request host IS the site address, so it must at least look like a dev host
            if ((string) Config::get('app.url', '') === '' && !$request->hostAllowed()) {
                throw new HttpException(400, 'Bad request');
            }
            return;
        }
        if ((string) Config::get('app.url', '') === '') {
            throw new \RuntimeException('APP_URL is not set. Set APP_URL=https://your-domain in .env (required when APP_ENV is not "local").');
        }
        if (!$request->hostAllowed()) {
            throw new HttpException(400, 'Bad request');
        }
    }

    /**
     * A POST bigger than post_max_size reaches PHP as an EMPTY $_POST/$_FILES, which would look like an expired CSRF token
     * ("Page expired") and throw away the whole form. Detect it and say what really happened.
     */
    private static function oversizedPost(Request $request): ?Response
    {
        if (!$request->isPost() || $_POST !== [] || $_FILES !== []) {
            return null;
        }
        $len = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $limit = self::iniBytes((string) ini_get('post_max_size'));
        if ($len <= 0 || $limit <= 0 || $len <= $limit) {
            return null;
        }
        while (ob_get_level() > 0) {          // drop PHP's own "POST Content-Length exceeds the limit" warning text
            @ob_end_clean();
        }
        $human = $limit >= 1048576 ? round($limit / 1048576, 1) . ' MB' : round($limit / 1024) . ' KB';
        $default = 'The files you sent are larger than this server accepts (limit: {max}). Please choose smaller files and try again.';
        try {
            $msg = content('site_errors', 'form.too_large', $default);
            $msg = is_string($msg) && trim($msg) !== '' ? trim($msg) : $default;
        } catch (\Throwable) {
            $msg = $default;
        }
        $msg = str_replace('{max}', $human, $msg);
        if ($request->expectsJson()) {
            return Response::json(['message' => $msg], 413);
        }
        Flash::error($msg);
        $ref = $request->referer();
        $same = $ref !== null && strtolower((string) parse_url($ref, PHP_URL_HOST)) === strtolower(explode(':', $request->host())[0]);
        return Response::redirect($same ? (string) $ref : url(ltrim($request->path(), '/')));
    }

    private static function iniBytes(string $v): int
    {
        $v = trim($v);
        if ($v === '' || $v === '0') {
            return 0;
        }
        $n = (int) $v;
        return $n * match (strtolower(substr($v, -1))) { 'g' => 1073741824, 'm' => 1048576, 'k' => 1024, default => 1 };
    }

    private static function loadRoutes(Router $router): void
    {
        $files = glob(APP_PATH . '/routes/*.php') ?: [];
        sort($files, SORT_STRING);
        $load = static function (string $file) use ($router): void {
            require $file;
        };
        foreach ($files as $file) {
            $load($file);
        }
    }

    /** APP_MAINTENANCE=1: everything except /admin (and logged-in staff) gets a 503. */
    private static function maintenanceGate(Request $request): void
    {
        if (!(bool) Config::get('app.maintenance', false) || $request->pathIs('/admin') || Auth::check()) {
            return;
        }
        throw new HttpException(503, 'Maintenance', ['Retry-After' => '3600']);
    }

    /** Banner above a form with mistakes (editable: Admin > Pages > Error pages > Form messages). */
    private static function validationBanner(): string
    {
        $default = 'Please correct the highlighted fields and try again.';
        try {
            $m = content('site_errors', 'form.validation', $default);
            return is_string($m) && trim($m) !== '' ? trim($m) : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    private static function validationResponse(ValidationException $e, Request $request): Response
    {
        if ($request->expectsJson()) {
            return Response::json(['message' => 'Validation failed', 'errors' => $e->errors], 422);
        }
        FormState::flash($e->errors, $e->old);
        Flash::error(self::validationBanner());
        $to = $e->redirectTo;
        if ($to === null || $to === '') {
            $ref = $request->referer();
            $same = $ref !== null && strtolower((string) parse_url($ref, PHP_URL_HOST)) === strtolower(explode(':', $request->host())[0]);
            return Response::redirect($same ? (string) $ref : url(ltrim($request->path(), '/')));
        }
        return Response::redirect(preg_match('~^https?://~i', $to) ? $to : url($to));
    }
}
