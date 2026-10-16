<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Global error/exception handling.
 *   - PHP warnings/notices become ErrorException when APP_DEBUG=1 (so bugs surface); in production they are only logged.
 *   - Uncaught exceptions render a FRIENDLY page (never stack traces unless APP_DEBUG=1) and are logged to storage/logs.
 *   - Error page lookup for public requests:  web/errors/<status>  ->  web/errors/error  ->  built-in plain HTML (ErrorHandler::fallbackHtml). 503 tries web/errors/maintenance first.
 *     Admin requests (/admin...) and JSON requests get a compact admin/JSON error.
 *   View variables for web/errors/*: $status (int), $title, $message, $debug (null | array with 'message','file','line','trace').
 */
final class ErrorHandler
{
    private static bool $debug = false;

    public static function register(bool $debug): void
    {
        self::$debug = $debug;
        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');

        set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
            if (!(error_reporting() & $no)) {
                return false;
            }
            if (in_array($no, [E_DEPRECATED, E_USER_DEPRECATED], true)) {
                Log::warning("PHP deprecated: {$str} in {$file}:{$line}");
                return true;
            }
            if (self::$debug) {
                throw new \ErrorException($str, 0, $no, $file, $line);
            }
            Log::warning("PHP error [{$no}]: {$str} in {$file}:{$line}");
            return true;
        });

        set_exception_handler(static function (\Throwable $e): void {
            Log::error(get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            if (PHP_SAPI === 'cli') {
                fwrite(STDERR, 'Uncaught ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL);
                exit(1);
            }
            self::response($e, Request::current())->send();
        });

        register_shutdown_function(static function (): void {
            $err = error_get_last();
            if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true) && PHP_SAPI !== 'cli') {
                Log::error('Fatal: ' . $err['message'], ['file' => $err['file'], 'line' => $err['line']]);
                if (!headers_sent()) {
                    while (ob_get_level() > 0) {
                        @ob_end_clean();
                    }
                    http_response_code(500);
                    header('Content-Type: text/html; charset=utf-8');
                    echo self::fallbackHtml(500, 'Something went wrong', 'An unexpected error occurred. Please try again shortly.', self::$debug ? $err['message'] . ' in ' . $err['file'] . ':' . $err['line'] : null);
                }
            }
        });
    }

    public static function isDebug(): bool
    {
        return self::$debug;
    }

    /** Convert any Throwable to a Response. */
    public static function response(\Throwable $e, ?Request $request): Response
    {
        $status = 500;
        $headers = [];
        if ($e instanceof HttpException) {
            $status = $e->status;
            $headers = $e->headers;
        } else {
            Log::error(get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'uri' => $request?->uri()]);
        }

        [$title, $message] = self::copy($status);
        $debug = null;
        if (self::$debug && $status >= 500) {
            $debug = ['message' => get_class($e) . ': ' . $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()];
        }

        if ($request !== null && $request->expectsJson()) {
            return Response::json(['error' => $title, 'status' => $status] + ($debug ? ['debug' => $debug] : []), $status === 419 ? 403 : $status)->withHeaders($headers);
        }

        try {
            $isAdmin = $request !== null && $request->pathIs('/admin');
            $html = null;
            if (!$isAdmin) {
                $candidates = $status === 503
                    ? ['web/errors/maintenance', 'web/errors/503', 'web/errors/error']
                    : ['web/errors/' . $status, 'web/errors/error'];
                foreach ($candidates as $view) {
                    if (View::exists($view)) {
                        View::share(['request' => $request, 'page' => null, 'meta' => page_meta('', ['title' => $title, 'robots' => 'noindex,nofollow']), 'breadcrumbs' => [], 'bodyClass' => 'is-error', 'pageTitle' => $title, 'navKey' => null]);
                        $html = View::render($view, ['status' => $status, 'title' => $title, 'message' => $message, 'debug' => $debug], 'layouts/main');
                        break;
                    }
                }
            }
            if ($html === null) {
                $html = self::fallbackHtml($status, $title, $message, $debug ? $debug['message'] . "\n" . $debug['file'] . ':' . $debug['line'] . "\n\n" . $debug['trace'] : null);
            }
        } catch (\Throwable $inner) {
            Log::error('Error page failed: ' . $inner->getMessage());
            $html = self::fallbackHtml($status, $title, $message, $debug ? $debug['message'] : null);
        }
        // 419 (expired CSRF token) is not a standard status and Apache turns it into a 500, so it is SENT as 403 (views still get $status = 419).
        return Response::html($html, $status === 419 ? 403 : $status)->withHeaders($headers + ['Cache-Control' => 'no-store']);
    }

    /** @return array{0:string,1:string} */
    public static function copy(int $status): array
    {
        return match ($status) {
            403 => ['Access denied', 'You do not have permission to view this page.'],
            404 => ['Page not found', 'We could not find the page you were looking for. It may have moved or no longer exists.'],
            405 => ['Method not allowed', 'That action is not available here.'],
            419 => ['Page expired', 'Your session timed out or the form was submitted from an old page. Please go back, refresh and try again.'],
            429 => ['Too many requests', 'You have made too many requests in a short time. Please wait a moment and try again.'],
            503 => ['Back soon', 'The website is undergoing scheduled maintenance. Please check back shortly.'],
            default => ['Something went wrong', 'An unexpected error occurred on our side. Please try again shortly.'],
        };
    }

    /** Self-contained HTML (no layout, no DB) used when no themed error view exists or rendering fails. */
    public static function fallbackHtml(int $status, string $title, string $message, ?string $debug = null): string
    {
        $css = '';
        try {
            $css = '<link rel="stylesheet" href="' . htmlspecialchars(asset('css/main.min.css'), ENT_QUOTES, 'UTF-8') . '">';
        } catch (\Throwable) {
        }
        $home = htmlspecialchars(function_exists('url') ? url('') : '/', ENT_QUOTES, 'UTF-8');
        $dbg = $debug ? '<pre style="text-align:left;white-space:pre-wrap;background:#f7f3ec;padding:1rem;border-radius:8px;overflow:auto;font-size:12px">' . htmlspecialchars($debug, ENT_QUOTES, 'UTF-8') . '</pre>' : '';
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<meta name="robots" content="noindex"><title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>' . $css
            . '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#2c3950;color:#fff;font-family:system-ui,-apple-system,Segoe UI,sans-serif;text-align:center}'
            . 'main{max-width:560px;padding:2rem}h1{color:#d6a151;font-size:3.5rem;margin:0}h2{margin:.25rem 0 1rem}p{opacity:.9;line-height:1.6}'
            . 'a{display:inline-block;margin-top:1rem;padding:.75rem 1.5rem;border-radius:999px;background:#e07341;color:#172036;font-weight:700;text-decoration:none}</style></head>'
            . '<body><main><h1>' . $status . '</h1><h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<a href="' . $home . '">Back to the home page</a>' . $dbg . '</main></body></html>';
    }
}
