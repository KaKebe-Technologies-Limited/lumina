<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Immutable-ish view of the HTTP request. Obtain with Request::current() (or the $request argument
 * your controller method receives).
 *
 *   $request->method();            // GET|POST|PUT|PATCH|DELETE (honours POST field _method)
 *   $request->path();              // '/blog/my-post' relative to the app base path ('/' for home)
 *   $request->input('name', '');   // POST first, then query string
 *   $request->query('page', 1);    // query string only
 *   $request->post('email');       // POST body only
 *   $request->string('name');      // trimmed string ('' if missing / non-scalar)
 *   $request->int('id', 0);        // intval
 *   $request->file('cv');          // normalised upload array or null when nothing was uploaded
 *   $request->files('images');     // list of normalised uploads (multi-upload), empty array if none
 *   $request->ip();                // client IP (REMOTE_ADDR unless behind TRUSTED_PROXIES)
 */
final class Request
{
    private static ?Request $current = null;

    /** @var array<string,string> */
    private array $routeParams = [];
    private string $basePath;
    private string $path;
    private string $method;

    public function __construct(
        private array $server,
        private array $query,
        private array $post,
        private array $files,
        private array $cookies = [],
    ) {
        // Invalid UTF-8 (bots, hand-made requests) would make MySQL refuse the INSERT and give visitors a 500: repair it once, here.
        $this->query = self::scrub($this->query);
        $this->post = self::scrub($this->post);
        $this->basePath = self::detectBasePath($server);
        $uriPath = (string) (parse_url((string) ($server['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?? '/');
        $uriPath = rawurldecode($uriPath);
        if ($this->basePath !== '' && ($uriPath === $this->basePath || str_starts_with($uriPath, $this->basePath . '/'))) {
            $uriPath = substr($uriPath, strlen($this->basePath));
        }
        // collapse duplicate slashes, drop trailing slash, drop a literal "/index.php" front controller segment
        $uriPath = '/' . trim((string) preg_replace('#/{2,}#', '/', $uriPath), '/');
        if ($uriPath === '/index.php') {
            $uriPath = '/';
        }
        $this->path = $uriPath;

        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        if ($method === 'POST') {
            $spoof = strtoupper((string) ($post['_method'] ?? ($server['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')));
            if (in_array($spoof, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoof;
            }
        }
        $this->method = $method;
    }

    /** Recursively replace invalid UTF-8 sequences (and NUL bytes) in request input. */
    private static function scrub(mixed $v): mixed
    {
        if (is_array($v)) {
            $out = [];
            foreach ($v as $k => $item) {
                $out[$k] = self::scrub($item);
            }
            return $out;
        }
        if (is_string($v)) {
            if ($v !== '' && !mb_check_encoding($v, 'UTF-8')) {
                $v = mb_scrub($v, 'UTF-8');
            }
            return str_contains($v, "\0") ? str_replace("\0", '', $v) : $v;
        }
        return $v;
    }

    public static function capture(): self
    {
        return self::$current = new self($_SERVER, $_GET, self::bodyInput($_SERVER, $_POST), $_FILES, $_COOKIE);
    }

    /**
     * JSON API support (additive): a POST/PUT/PATCH/DELETE with "Content-Type: application/json" is decoded into the post bag, so controllers
     * read it with post()/input()/string()/int() like a form (nested arrays and typed values stay intact). PUT/PATCH/DELETE with a
     * urlencoded body (PHP fills $_POST only for POST) are parsed too. A body over 4 MB or invalid JSON leaves the bag untouched.
     * Nothing changes for ordinary forms.
     *
     * @param array<string,mixed> $server
     * @param array<string,mixed> $post
     * @return array<string,mixed>
     */
    public static function bodyInput(array $server, array $post): array
    {
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $post;
        }
        $type = strtolower((string) ($server['CONTENT_TYPE'] ?? $server['HTTP_CONTENT_TYPE'] ?? ''));
        $json = str_starts_with($type, 'application/json');
        $form = $method !== 'POST' && $post === [] && str_starts_with($type, 'application/x-www-form-urlencoded');
        if (!$json && !$form) {
            return $post;
        }
        $limit = 4 * 1024 * 1024;
        if ((int) ($server['CONTENT_LENGTH'] ?? 0) > $limit) {
            return $post;
        }
        $raw = (string) @file_get_contents('php://input', false, null, 0, $limit + 1);
        if ($raw === '' || strlen($raw) > $limit) {
            return $post;
        }
        if ($json) {
            $data = json_decode(mb_check_encoding($raw, 'UTF-8') ? $raw : mb_scrub($raw, 'UTF-8'), true, 64);
            return is_array($data) ? $data : $post;
        }
        parse_str($raw, $parsed);
        return $parsed;
    }

    /** The request captured by App::run(); null on the CLI. */
    public static function current(): ?self
    {
        return self::$current;
    }

    /**
     * Public URL prefix of the app: '' at a domain root, '/lumina' under XAMPP, '/any/sub/folder' elsewhere.
     * The front controller is ALWAYS <docroot>/<prefix>/index.php, so the prefix is dirname(SCRIPT_NAME).
     * An explicit APP_URL path component wins (useful behind a path-rewriting proxy).
     */
    public static function detectBasePath(array $server): string
    {
        $configured = (string) Config::get('app.url', '');
        if ($configured !== '') {
            return rtrim((string) (parse_url($configured, PHP_URL_PATH) ?? ''), '/');
        }
        $script = str_replace(chr(92), '/', (string) ($server['SCRIPT_NAME'] ?? ''));
        $dir = rtrim(str_replace(chr(92), '/', dirname($script)), '/');
        return ($dir === '.' || $dir === '/') ? '' : $dir;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isMethod(string $m): bool
    {
        return $this->method === strtoupper($m);
    }

    public function isGet(): bool
    {
        return in_array($this->method, ['GET', 'HEAD'], true);
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /** True for POST/PUT/PATCH/DELETE. */
    public function isUnsafe(): bool
    {
        return !in_array($this->method, ['GET', 'HEAD', 'OPTIONS'], true);
    }

    public function path(): string
    {
        return $this->path;
    }

    /** Request path + query string, relative to the base path. */
    public function uri(): string
    {
        $qs = (string) ($this->server['QUERY_STRING'] ?? '');
        return $this->path . ($qs !== '' ? '?' . $qs : '');
    }

    /** True if the path equals $prefix or is below it ('/admin' matches '/admin' and '/admin/posts'). */
    public function pathIs(string $prefix): bool
    {
        $prefix = '/' . trim($prefix, '/');
        return $this->path === $prefix || str_starts_with($this->path, rtrim($prefix, '/') . '/');
    }

    /**
     * Host[:port] of the request, validated. A syntactically invalid Host header (the usual injection vector) is replaced by
     * the APP_URL host, else SERVER_NAME, else 'localhost'. Whether an unknown-but-valid host is acceptable is decided by
     * App::run() (hostAllowed()).
     */
    public function host(): string
    {
        $h = strtolower(trim((string) ($this->server['HTTP_HOST'] ?? '')));
        if ($h !== '' && preg_match('/^(?:[a-z0-9](?:[a-z0-9._-]*[a-z0-9])?|\[[0-9a-f:.]+\])(?::\d{1,5})?$/', $h) === 1) {
            return $h;
        }
        $configured = (string) parse_url((string) Config::get('app.url', ''), PHP_URL_HOST);
        if ($configured !== '') {
            $port = parse_url((string) Config::get('app.url', ''), PHP_URL_PORT);
            return strtolower($configured) . ($port ? ':' . $port : '');
        }
        $sn = strtolower(trim((string) ($this->server['SERVER_NAME'] ?? '')));
        return ($sn !== '' && preg_match('/^[a-z0-9._-]+$/', $sn) === 1) ? $sn : 'localhost';
    }

    /**
     * Hosts this site answers to: the APP_URL host plus ALLOWED_HOSTS (comma list in .env). Empty list = no restriction.
     * @return string[] lower-case hosts without port
     */
    public static function allowedHosts(): array
    {
        $list = (array) Config::get('allowed_hosts', []);
        $app = strtolower((string) parse_url((string) Config::get('app.url', ''), PHP_URL_HOST));
        if ($app !== '') {
            $list[] = $app;
        }
        return array_values(array_unique(array_map('strtolower', $list)));
    }

    /**
     * False when this request's Host is not acceptable: not on the allow-list (APP_URL host + ALLOWED_HOSTS) when one exists,
     * otherwise (no allow-list: local development with an empty APP_URL) not a loopback / private-network / single-label /
     * *.localhost / *.test / *.local name.
     */
    public function hostAllowed(): bool
    {
        $h = $this->host();
        $h = str_starts_with($h, '[') ? trim(explode(']', $h)[0], '[]') : explode(':', $h)[0];
        $allowed = self::allowedHosts();
        if ($allowed !== []) {
            return in_array($h, $allowed, true);
        }
        if (filter_var($h, FILTER_VALIDATE_IP)) {
            return filter_var($h, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }
        return !str_contains($h, '.') || preg_match('/\.(localhost|test|local|localdomain|lan|internal)$/', $h) === 1;
    }

    public function isSecure(): bool
    {
        $https = $this->server['HTTPS'] ?? '';
        if ($https !== '' && strtolower((string) $https) !== 'off') {
            return true;
        }
        return $this->fromTrustedProxy() && strtolower((string) ($this->server['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    public function scheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /** scheme://host (no base path, no trailing slash). */
    public function origin(): string
    {
        return $this->scheme() . '://' . $this->host();
    }

    public function all(): array
    {
        return $this->post + $this->query;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->post : ($this->post[$key] ?? $default);
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : ($this->query[$key] ?? $default);
    }

    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->query[$key]);
    }

    /** @return array<string,mixed> only the listed keys that are present */
    public function only(array $keys): array
    {
        $out = [];
        foreach ($keys as $k) {
            if ($this->has($k)) {
                $out[$k] = $this->input($k);
            }
        }
        return $out;
    }

    /** Trimmed scalar string from input (''/default if missing or an array). */
    public function string(string $key, string $default = ''): string
    {
        $v = $this->input($key);
        return is_scalar($v) ? trim((string) $v) : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $v = $this->input($key);
        return is_scalar($v) && is_numeric($v) ? (int) $v : $default;
    }

    public function bool(string $key): bool
    {
        $v = $this->input($key);
        return is_scalar($v) && in_array(strtolower((string) $v), ['1', 'true', 'on', 'yes'], true);
    }

    /** Current ?page= (>= 1). */
    public function page(): int
    {
        return max(1, (int) ($this->query['page'] ?? 1));
    }

    /**
     * Single uploaded file as ['name','type','tmp_name','error','size'] or null if no file was chosen.
     * (Do NOT trust name/type; pass it to Uploader which sniffs content.)
     */
    public function file(string $key): ?array
    {
        $f = $this->files[$key] ?? null;
        if (!is_array($f)) {
            return null;
        }
        if (is_array($f['name'])) {       // multi-upload field: return the first real file
            $list = $this->files($key);
            return $list[0] ?? null;
        }
        return ((int) $f['error']) === UPLOAD_ERR_NO_FILE ? null : $f;
    }

    /** @return array<int,array> normalised list for <input type="file" name="images[]" multiple> */
    public function files(string $key): array
    {
        $f = $this->files[$key] ?? null;
        if (!is_array($f)) {
            return [];
        }
        $out = [];
        if (is_array($f['name'])) {
            foreach (array_keys($f['name']) as $i) {
                if ((int) $f['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $out[] = [
                    'name' => $f['name'][$i], 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i],
                    'error' => $f['error'][$i], 'size' => $f['size'][$i],
                ];
            }
        } elseif ((int) $f['error'] !== UPLOAD_ERR_NO_FILE) {
            $out[] = $f;
        }
        return $out;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($this->server[$key]) ? (string) $this->server[$key] : $default;
    }

    public function userAgent(): string
    {
        return mb_substr((string) ($this->server['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public function referer(): ?string
    {
        $r = $this->server['HTTP_REFERER'] ?? null;
        return is_string($r) && $r !== '' ? $r : null;
    }

    private function fromTrustedProxy(): bool
    {
        return self::ipInList((string) ($this->server['REMOTE_ADDR'] ?? ''), (array) Config::get('trusted_proxies', []));
    }

    /** Exact match or CIDR match (IPv4 and IPv6) of $ip against a list such as ['127.0.0.1', '10.0.0.0/8', '::1']. */
    public static function ipInList(string $ip, array $list): bool
    {
        $bin = @inet_pton($ip);
        if ($bin === false) {
            return false;
        }
        foreach ($list as $entry) {
            $entry = trim((string) $entry);
            if ($entry === '') {
                continue;
            }
            [$net, $bits] = array_pad(explode('/', $entry, 2), 2, null);
            $nb = @inet_pton($net);
            if ($nb === false || strlen($nb) !== strlen($bin)) {
                continue;
            }
            $max = strlen($nb) * 8;
            $bits = $bits === null || !ctype_digit($bits) ? $max : min($max, (int) $bits);
            $bytes = intdiv($bits, 8);
            if ($bytes > 0 && substr($bin, 0, $bytes) !== substr($nb, 0, $bytes)) {
                continue;
            }
            $rem = $bits % 8;
            if ($rem > 0) {
                $mask = (0xFF << (8 - $rem)) & 0xFF;
                if ((ord($bin[$bytes]) & $mask) !== (ord($nb[$bytes]) & $mask)) {
                    continue;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Client IP. X-Forwarded-For is only read when REMOTE_ADDR is a trusted proxy (TRUSTED_PROXIES, CIDR allowed), and the
     * chain is walked RIGHT to LEFT: every proxy appends the address it saw, so the first hop from the right that is not a
     * trusted proxy is the real client. Anything further left is client-supplied and ignored.
     */
    public function ip(): string
    {
        $ip = (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
        if ($this->fromTrustedProxy() && !empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $trusted = (array) Config::get('trusted_proxies', []);
            $hops = array_reverse(array_map('trim', explode(',', (string) $this->server['HTTP_X_FORWARDED_FOR'])));
            foreach ($hops as $hop) {
                if (!filter_var($hop, FILTER_VALIDATE_IP)) {
                    break;                       // garbage in the chain: stop trusting it
                }
                $ip = $hop;
                if (!self::ipInList($hop, $trusted)) {
                    break;
                }
            }
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    public function isAjax(): bool
    {
        return strtolower((string) ($this->server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    public function expectsJson(): bool
    {
        $accept = (string) ($this->server['HTTP_ACCEPT'] ?? '');
        return $this->isAjax() || (str_contains($accept, 'application/json') && !str_contains($accept, 'text/html'));
    }

    /** @param array<string,string> $params */
    public function setParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->routeParams;
    }
}
