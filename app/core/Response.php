<?php
declare(strict_types=1);

namespace App\Core;

/**
 * HTTP response value object.
 *
 *   return Response::html($string);                // or just return a string from a controller
 *   return Response::json(['ok' => true], 201);    // or return an array
 *   return Response::redirect(url('blog'));        // 302; Response::redirect($url, 301)
 *   return Response::download($absPath, 'cv.pdf'); // auth-gated private files
 *   return Response::csv('contacts.csv', ['Name','Email'], $rows);
 *   return Response::text($body, 200, 'application/xml; charset=utf-8');  // sitemap.xml / robots.txt
 */
class Response
{
    /** @var array<string,string> */
    private array $headers = [];
    /** @var array<int,string> */
    private array $cookies = [];

    public function __construct(
        private string $body = '',
        private int $status = 200,
        array $headers = [],
        private ?\Closure $stream = null,
    ) {
        foreach ($headers as $k => $v) {
            $this->headers[$k] = $v;
        }
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($body, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function text(string $body, int $status = 200, string $contentType = 'text/plain; charset=utf-8'): self
    {
        return new self($body, $status, ['Content-Type' => $contentType]);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self(
            (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'no-store']
        );
    }

    /** Redirect. $to may be a full URL, or a path produced by url(). Newlines are stripped (header injection). */
    public static function redirect(string $to, int $status = 302): self
    {
        $to = str_replace(["\r", "\n"], '', $to);
        return new self('', $status, ['Location' => $to]);
    }

    public static function noContent(): self
    {
        return new self('', 204);
    }

    /**
     * Send a file from disk as a download (or inline). $path is an ABSOLUTE filesystem path - callers must have
     * authorised the user and must never build $path from raw user input.
     */
    public static function download(string $path, ?string $name = null, ?string $mime = null, bool $inline = false): self
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new HttpException(404);
        }
        $name = $name ?: basename($path);
        $safe = preg_replace('/[^A-Za-z0-9._ -]+/', '_', $name) ?: 'download';
        if ($mime === null) {
            $fi = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
            $mime = $fi ? (string) finfo_file($fi, $path) : 'application/octet-stream';
        }
        $r = new self('', 200, [
            'Content-Type'            => $mime,
            'Content-Length'          => (string) filesize($path),
            'Content-Disposition'     => ($inline ? 'inline' : 'attachment') . '; filename="' . $safe . '"; filename*=UTF-8\'\'' . rawurlencode($name),
            'X-Content-Type-Options'  => 'nosniff',
            'Cache-Control'           => 'private, no-store',
        ]);
        $r->stream = static function () use ($path): void {
            readfile($path);
        };
        return $r;
    }

    /**
     * Streamed CSV download (UTF-8 with BOM so Excel opens it correctly).
     * @param string[]        $header column titles
     * @param iterable<array> $rows   each row = list of scalar cells (formula-injection is neutralised)
     */
    public static function csv(string $filename, array $header, iterable $rows): self
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $filename) ?: 'export.csv';
        $r = new self('', 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $safe . '"',
            'Cache-Control'       => 'private, no-store',
        ]);
        $r->stream = static function () use ($header, $rows): void {
            CsvExport::write('php://output', $header, $rows);
        };
        return $r;
    }

    public function status(?int $code = null): int|self
    {
        if ($code === null) {
            return $this->status;
        }
        $this->status = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = str_replace(["\r", "\n"], '', $value);
        return $this;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body(?string $body = null): string|self
    {
        if ($body === null) {
            return $this->body;
        }
        $this->body = $body;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        foreach ($headers as $k => $v) {
            $this->header((string) $k, (string) $v);
        }
        return $this;
    }

    /** Baseline security headers + CSP for HTML pages. */
    private function securityHeaders(): array
    {
        $h = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options'        => 'SAMEORIGIN',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(), payment=()',
        ];
        $ct = $this->headers['Content-Type'] ?? '';
        if (!isset($this->headers['Cache-Control']) && str_starts_with($ct, 'text/html')) {
            // PHP's session cache limiter is off (see Session), so say it explicitly: admin pages are never stored; public pages
            // may sit in the back/forward cache but must be revalidated (they embed a per-request form token).
            $admin = Request::current()?->pathIs('/admin') ?? false;
            $h['Cache-Control'] = $admin ? 'private, no-store' : 'private, max-age=0, must-revalidate';
        }
        if (str_starts_with($ct, 'text/html')) {
            $gaId = trim((string) setting('analytics_id', ''));
            $analytics = $gaId !== '' ? ' https://www.googletagmanager.com' : '';
            $analyticsConnect = $gaId !== '' ? ' https://www.google-analytics.com https://region1.google-analytics.com' : '';
            // No inline <script> / on* handlers anywhere: JS lives in assets/js/*.js
            $h['Content-Security-Policy'] = "default-src 'self'; "
                . "script-src 'self'" . $analytics . "; "
                . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
                . "font-src 'self' https://fonts.gstatic.com data:; "
                . "img-src 'self' data: blob: https:; "
                . "media-src 'self' https:; "
                . "frame-src https://www.google.com https://maps.google.com https://www.youtube-nocookie.com https://www.youtube.com; "
                . "connect-src 'self'" . $analyticsConnect . "; form-action 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'";
        }
        if (Request::current()?->isSecure()) {
            $h['Strict-Transport-Security'] = 'max-age=15552000; includeSubDomains';
        }
        return $h;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->securityHeaders() as $k => $v) {
                if (!isset($this->headers[$k])) {
                    header("{$k}: {$v}");
                }
            }
            foreach ($this->headers as $k => $v) {
                header("{$k}: {$v}", true);
            }
            header_remove('X-Powered-By');
        }
        // Drop any stray buffered output (notices etc.) before sending a streamed/binary response.
        if ($this->stream !== null) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ($this->stream)();
            return;
        }
        if (Request::current()?->isMethod('HEAD')) {
            return;
        }
        echo $this->body;
    }
}
