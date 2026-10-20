<?php
declare(strict_types=1);

namespace App\Services\Social;

use App\Core\Config;
use App\Core\Env;
use App\Core\Log;

/**
 * THE ONE hardened HTTP client of the social-media module. Every outbound request of every channel goes through here (docs/SOCIAL.md section 6).
 *
 * PLATFORM APIs (`api()`): HTTPS only, FIXED hosts per channel (API_HOSTS: graph.facebook.com, api.x.com / api.twitter.com, api.linkedin.com, api.telegram.org),
 *   TLS verification always ON, no redirect is ever followed (a 3xx is an error), the path is validated, timeouts (connect 4 s, total 10 s, both clamped to the
 *   caller's overall `deadline`), the answer is capped at 1 MB, secrets travel in headers or the body (Telegram needs the bot token IN the path: the URL is never
 *   logged and never returned). Error text goes through redact() so a token cannot leak into the queue, the log, the audit trail, a page or an e-mail.
 *
 * WEBHOOK (`webhook()`): the URL is typed by an admin, so it gets a full SSRF guard (guardWebhookUrl): https only, port 443 / 8443, no user name or password,
 *   a real host name (no `localhost`, no numeric / octal / hex / short IP spellings, no internal suffixes) or a canonical public IP literal, DNS resolved for IPv4
 *   AND IPv6 and EVERY address must be public (private, loopback, link-local, CGNAT, documentation, multicast, reserved, IPv4-mapped / NAT64 / 6to4 / Teredo are
 *   refused), the request is PINNED to the address that was checked (no DNS re-resolution between check and connect), at most 2 redirects and each hop is checked
 *   again from scratch. Signed with X-Lumina-Signature (hex HMAC-SHA256 of the body) and X-Lumina-Timestamp.
 *
 * DEV ONLY: with APP_ENV=local, SOCIAL_DEV_BASE_URL=http://127.0.0.1:PORT (127.0.0.1 / localhost / [::1], no path) sends the platform calls to the local mock
 *   (bin/dev/mock-social.php) under /graph, /x, /linkedin, /telegram, and lets a webhook URL under <base>/webhook through the SSRF guard. Ignored in every
 *   other environment, so a live server can never be pointed anywhere else.
 *
 * Result of api() / webhook():
 *   ok (2xx) | status | json (decoded body or null) | raw (the body, up to 1 MB; callers must not log it) | headers (lower-case names) | error (safe short text or null)
 *   | kind ('' | blocked | timeout | network | tls | too_large | redirect | budget) | ambiguous (the request may have been executed: timeout or drop AFTER the
 *   request was sent, HTTP 502 / 504) | retry_after (seconds or null) | ms
 * Tests can inject $resolver (host => list of IPs) and $transport (request array => response array) so no network is needed.
 */
final class SocialHttp
{
    public const MAX_BODY = 1048576;
    public const CONNECT_TIMEOUT = 4;
    public const TOTAL_TIMEOUT = 10;
    public const MAX_REDIRECTS = 2;
    public const WEBHOOK_PORTS = [443, 8443];

    /** channel => the only hosts it may talk to */
    public const API_HOSTS = [
        'facebook'  => ['graph.facebook.com'],
        'instagram' => ['graph.facebook.com'],
        'x'         => ['api.x.com', 'api.twitter.com'],
        'linkedin'  => ['api.linkedin.com'],
        'telegram'  => ['api.telegram.org'],
    ];
    /** channel => path prefix on the local mock (dev override only) */
    private const DEV_PREFIX = ['facebook' => 'graph', 'instagram' => 'graph', 'x' => 'x', 'linkedin' => 'linkedin', 'telegram' => 'telegram'];
    /** host suffixes that are never public */
    private const BAD_SUFFIXES = ['localhost', 'local', 'localdomain', 'internal', 'intranet', 'lan', 'home', 'corp', 'private', 'test', 'example', 'invalid', 'onion', 'arpa'];
    private const V4_BLOCKS = [
        '0.0.0.0/8', '10.0.0.0/8', '100.64.0.0/10', '127.0.0.0/8', '169.254.0.0/16', '172.16.0.0/12', '192.0.0.0/24', '192.0.2.0/24', '192.88.99.0/24',
        '192.168.0.0/16', '198.18.0.0/15', '198.51.100.0/24', '203.0.113.0/24', '224.0.0.0/4', '240.0.0.0/4',
    ];
    /** inside 2000::/3 but still not routable on the public internet */
    private const V6_BLOCKS = ['2001::/32', '2001:2::/48', '2001:10::/28', '2001:20::/28', '2001:db8::/32', '2002::/16', '3fff::/20'];

    /** @var ?\Closure(string):list<string> test hook: host => IP addresses (replaces DNS) */
    public static ?\Closure $resolver = null;
    /** @var ?\Closure(array<string,mixed>):array<string,mixed> test hook: replaces the network; receives the request array of send() and returns its answer */
    public static ?\Closure $transport = null;

    // ------------------------------------------------------------------------------------------------ dev override

    /** The validated SOCIAL_DEV_BASE_URL ("http://127.0.0.1:8123") or null. Honoured ONLY when APP_ENV=local. */
    public static function devBase(): ?string
    {
        if (Config::get('app.env') !== 'local') {
            return null;
        }
        $raw = trim((string) Env::get('SOCIAL_DEV_BASE_URL', ''));
        if ($raw === '') {
            return null;
        }
        $p = parse_url($raw);
        if ($p === false || !in_array($p['scheme'] ?? '', ['http', 'https'], true) || !in_array(strtolower((string) ($p['host'] ?? '')), ['127.0.0.1', 'localhost', '[::1]', '::1'], true)
            || isset($p['user']) || isset($p['pass']) || (($p['path'] ?? '') !== '' && ($p['path'] ?? '') !== '/') || isset($p['query']) || isset($p['fragment'])) {
            return null;
        }
        return $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . (int) $p['port'] : '');
    }

    public static function usingDevBase(): bool
    {
        return self::devBase() !== null;
    }

    // ------------------------------------------------------------------------------------------------ platform API

    /**
     * One call to a platform API on its fixed host.
     *
     * @param string $channel facebook | instagram | x | linkedin | telegram
     * @param string $path    absolute path WITHOUT query ("/v23.0/me"); validated
     * @param array{query?:array<string,scalar>,headers?:array<string,string>,authorize?:callable,json?:array<mixed>,form?:array<string,scalar>,body?:string,content_type?:string,host?:string,connect_timeout?:float,timeout?:float,deadline?:?float} $opts
     * @return array<string,mixed> see the class comment
     */
    public static function api(string $channel, string $method, string $path, array $opts = []): array
    {
        $hosts = self::API_HOSTS[$channel] ?? null;
        if ($hosts === null) {
            return self::fail('blocked', 'Unknown channel.');
        }
        $host = (string) ($opts['host'] ?? $hosts[0]);
        if (!in_array($host, $hosts, true)) {
            return self::fail('blocked', 'That host is not allowed for this channel.');
        }
        if (preg_match('#^/[A-Za-z0-9._~!$&\'()*+,;=:@%/-]{1,500}$#', $path) !== 1 || str_contains($path, '//') || str_contains($path, '..')) {
            return self::fail('blocked', 'Illegal request path.');
        }
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST'], true)) {
            return self::fail('blocked', 'Illegal request method.');
        }
        $dev = self::devBase();
        $url = self::urlFor($channel, $path, (array) ($opts['query'] ?? []), $host);
        if (isset($opts['authorize']) && is_callable($opts['authorize'])) {
            // e.g. OAuth 1.0a: the signature must be computed over the EXACT url that is requested (the dev base included), so it is computed here, right before sending
            $extra = (array) $opts['authorize']($method, $url, (array) ($opts['query'] ?? []), (array) ($opts['form'] ?? []));
            $opts['headers'] = array_merge((array) ($opts['headers'] ?? []), array_filter($extra, 'is_string'));
        }
        $req = self::buildRequest($method, $url, $opts, $dev !== null);
        if (isset($req['error'])) {
            return self::fail('blocked', (string) $req['error']);
        }
        $r = self::execute($req);
        if ($r['status'] >= 300 && $r['status'] < 400) {
            $r['kind'] = 'redirect';
            $r['error'] = 'The service answered with a redirect, which is not followed.';
            $r['ok'] = false;
        }
        return $r;
    }

    /**
     * The exact address api() will request for a channel and path (the dev base included): "https://graph.facebook.com/v23.0/me?fields=id". Adapters that sign a request
     * (X, OAuth 1.0a) need it; most use the `authorize` option of api() instead, a callable(method, url, query, form): array<string,string> of extra headers that is called
     * with this same address right before the request is sent.
     * @param array<string,scalar> $query
     */
    public static function urlFor(string $channel, string $path, array $query = [], ?string $host = null): string
    {
        $hosts = self::API_HOSTS[$channel] ?? [];
        $host = $host !== null && in_array($host, $hosts, true) ? $host : ($hosts[0] ?? '');
        $dev = self::devBase();
        $url = ($dev !== null ? $dev . '/' . (self::DEV_PREFIX[$channel] ?? $channel) : 'https://' . $host) . $path;
        return $query === [] ? $url : $url . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    // ------------------------------------------------------------------------------------------------ webhook (SSRF guarded)

    /**
     * POST $body to an admin-supplied URL. $headers are extra request headers (Content-Type is application/json unless given). The signature headers are NOT added
     * here: call sign() and pass them in (WebhookChannel does).
     *
     * @param array<string,string> $headers
     * @param array{connect_timeout?:float,timeout?:float,deadline?:?float} $opts
     * @return array<string,mixed>
     */
    public static function webhook(string $url, string $body, array $headers = [], array $opts = []): array
    {
        $current = $url;
        for ($hop = 0; ; $hop++) {
            $g = self::guardWebhookUrl($current);
            if (!$g['ok']) {
                return self::fail('blocked', (string) $g['error']);
            }
            $headers += ['Content-Type' => 'application/json'];
            $req = self::buildRequest('POST', (string) $g['url'], ['headers' => $headers, 'body' => $body, 'content_type' => $headers['Content-Type'], 'connect_timeout' => $opts['connect_timeout'] ?? null,
                'timeout' => $opts['timeout'] ?? null, 'deadline' => $opts['deadline'] ?? null], (bool) $g['dev']);
            if (isset($req['error'])) {
                return self::fail('blocked', (string) $req['error']);
            }
            $literal = filter_var(trim((string) $g['host'], '[]'), FILTER_VALIDATE_IP) !== false;
            $req['pin'] = ($g['dev'] || $literal) ? null : ($g['ips'][0] ?? null);        // connect to the address that was checked (no second DNS lookup)
            $r = self::execute($req);
            if (!in_array($r['status'], [301, 302, 303, 307, 308], true)) {
                return $r;
            }
            if (!in_array($r['status'], [307, 308], true)) {
                // 301 / 302 / 303: a browser would turn the POST into a GET, and repeating a signed POST somewhere else is never right: the admin pastes the final address
                $r['kind'] = 'redirect';
                $r['ok'] = false;
                $r['error'] = 'The webhook address answered with a redirect (HTTP ' . (int) $r['status'] . '), which is not followed. Paste the final address from your automation tool.';
                return $r;
            }
            if ($hop >= self::MAX_REDIRECTS) {
                $r['kind'] = 'redirect';
                $r['ok'] = false;
                $r['error'] = 'Too many redirects.';
                return $r;
            }
            $loc = (string) ($r['headers']['location'] ?? '');
            if ($loc === '' || strlen($loc) > 2000) {
                $r['kind'] = 'redirect';
                $r['ok'] = false;
                $r['error'] = 'The address answered with an empty redirect.';
                return $r;
            }
            $next = self::absolutize($current, $loc);
            $a = parse_url($url);
            $b = parse_url($next);
            $same = is_array($a) && is_array($b) && strtolower((string) ($a['host'] ?? '')) === strtolower((string) ($b['host'] ?? '')) && (int) ($a['port'] ?? 443) === (int) ($b['port'] ?? 443);
            if (!$same) {
                // 307 / 308 keep the method and the body, so the signed message would reach a host the admin never entered: only the SAME host may be followed
                $r['kind'] = 'redirect';
                $r['ok'] = false;
                $r['error'] = 'The webhook address redirects to a different host, which is not followed (the message is signed for the address you entered). Paste the final address.';
                return $r;
            }
            $current = $next;                                       // same host: checked again from scratch on the next loop
        }
    }

    /**
     * The SSRF guard for a webhook address. Pure (DNS only): nothing is sent.
     *
     * @return array{ok:bool,error?:string,host?:string,port?:int,ips?:list<string>,url?:string,dev?:bool}
     */
    public static function guardWebhookUrl(string $url, ?\Closure $resolver = null): array
    {
        $deny = static fn(string $why): array => ['ok' => false, 'error' => $why];
        if (str_contains($url, "\0")) {
            return $deny('The address contains illegal characters.');
        }
        $url = trim($url);
        if ($url === '' || strlen($url) > 2000 || preg_match('/[\x00-\x20\x7f]/', $url) === 1 || preg_match('/[^\x21-\x7e]/', $url) === 1) {
            return $deny('The address is empty, too long or contains illegal characters. Use plain ASCII (encode special characters).');
        }
        $dev = self::devBase();
        if ($dev !== null && str_starts_with($url, $dev . '/webhook') && preg_match('~^[a-z]+://[^/@]+/webhook(?:[/?][^#]*)?$~i', $url) === 1) {
            return ['ok' => true, 'host' => (string) parse_url($url, PHP_URL_HOST), 'port' => (int) (parse_url($url, PHP_URL_PORT) ?: 80), 'ips' => [], 'url' => $url, 'dev' => true];
        }
        $p = parse_url($url);
        if ($p === false || !isset($p['scheme'], $p['host'])) {
            return $deny('That is not a valid address.');
        }
        if (strtolower($p['scheme']) !== 'https') {
            return $deny('Only https:// addresses are allowed.');
        }
        if (isset($p['user']) || isset($p['pass']) || str_contains(explode('?', explode('#', $url)[0])[0], '@')) {
            return $deny('Addresses with a user name or password are not allowed.');
        }
        $host = strtolower((string) $p['host']);
        if (str_ends_with($host, '.') && !str_ends_with($host, '..')) {
            $host = substr($host, 0, -1);
        }
        $ips = [];
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $lit = substr($host, 1, -1);
            if (str_contains($lit, '%') || filter_var($lit, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                return $deny('That IPv6 address is not valid.');
            }
            $ips = [$lit];
            $display = '[' . $lit . ']';
        } elseif (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $ips = [$host];
            $display = $host;
        } else {
            if (preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $host) !== 1) {
                return $deny('The host name is not valid. Use a normal public domain name (not localhost, not a short or numeric IP spelling).');
            }
            $tld = substr($host, (int) strrpos($host, '.') + 1);
            if (in_array($tld, self::BAD_SUFFIXES, true) || $host === 'metadata.google.internal') {
                return $deny('Internal and local host names are not allowed.');
            }
            $display = $host;
        }
        $port = isset($p['port']) ? (int) $p['port'] : 443;
        if (!in_array($port, self::WEBHOOK_PORTS, true)) {
            return $deny('Only port 443 or 8443 is allowed.');
        }
        if ($ips === []) {
            $ips = self::resolve($host, $resolver);
            if ($ips === []) {
                return $deny('The address could not be found (DNS lookup failed).');
            }
        }
        foreach ($ips as $ip) {
            if (!self::isPublicIp($ip)) {
                return $deny('The address points to a private, local or reserved network and is not allowed.');
            }
        }
        $path = (string) ($p['path'] ?? '');
        $clean = 'https://' . $display . ($port !== 443 ? ':' . $port : '') . ($path === '' ? '/' : $path) . (isset($p['query']) ? '?' . $p['query'] : '');
        return ['ok' => true, 'host' => $host, 'port' => $port, 'ips' => array_values($ips), 'url' => $clean, 'dev' => false];
    }

    /** Is this IP address routable on the public internet (and therefore allowed as a webhook target)? */
    public static function isPublicIp(string $ip): bool
    {
        $bin = @inet_pton($ip);
        if ($bin === false) {
            return false;
        }
        if (strlen($bin) === 4) {
            foreach (self::V4_BLOCKS as $cidr) {
                if (self::inCidr($bin, $cidr)) {
                    return false;
                }
            }
            return true;
        }
        if (!self::inCidr($bin, '2000::/3')) {            // allow-list for IPv6: global unicast only (loopback, link-local, unique-local, multicast, mapped, NAT64 ... all fall outside)
            return false;
        }
        foreach (self::V6_BLOCKS as $cidr) {
            if (self::inCidr($bin, $cidr)) {
                return false;
            }
        }
        return true;
    }

    /** @return list<string> every A and AAAA address of the host */
    public static function resolve(string $host, ?\Closure $resolver = null): array
    {
        $fn = $resolver ?? self::$resolver;
        if ($fn !== null) {
            return array_values(array_filter((array) $fn($host), 'is_string'));
        }
        $out = [];
        if (function_exists('dns_get_record')) {
            foreach ([DNS_A => 'ip', DNS_AAAA => 'ipv6'] as $type => $key) {
                $recs = @dns_get_record($host, $type);
                foreach (is_array($recs) ? $recs : [] as $rec) {
                    if (isset($rec[$key]) && is_string($rec[$key])) {
                        $out[] = $rec[$key];
                    }
                }
            }
        }
        if ($out === []) {
            $v4 = @gethostbynamel($host);
            foreach (is_array($v4) ? $v4 : [] as $ip) {
                $out[] = $ip;
            }
        }
        return array_values(array_unique($out));
    }

    /** Hex HMAC-SHA256 of the body with the secret: the value of X-Lumina-Signature. */
    public static function sign(string $body, string $secret): string
    {
        return hash_hmac('sha256', $body, $secret);
    }

    // ------------------------------------------------------------------------------------------------ error mapping (never contains a token)

    /**
     * Remove everything that looks like a credential from a text, plus every exact secret currently configured. Used for every error text that is stored or shown.
     * @param list<string> $extra additional strings to blank out
     */
    public static function redact(string $text, array $extra = []): string
    {
        foreach (array_merge($extra, self::knownSecrets()) as $s) {
            if (is_string($s) && strlen($s) >= 6) {
                $text = str_replace([$s, rawurlencode($s), urlencode($s), base64_encode($s)], '[hidden]', $text);
            }
        }
        // OAuth 1.0a header: oauth_consumer_key="..", oauth_nonce=".." ... (everything after the word OAuth); Bearer / Basic: the credential after the word
        $text = (string) preg_replace('/\bOAuth\s+(?:[A-Za-z_]+="[^"]*"\s*,?\s*)+/', 'OAuth [hidden] ', $text);
        $text = (string) preg_replace('/\b(Bearer|Basic)\s+[A-Za-z0-9._~+\/=-]{6,}/i', '$1 [hidden]', $text);
        $text = (string) preg_replace('/\b(access_token|oauth_token|oauth_signature|oauth_consumer_key|oauth_nonce|api_key|api_secret|client_secret|bearer_token|token|secret|signature)(["\']?\s*[=:]\s*["\']?)(?!\[hidden\])[^&\s"\',;]+/i', '$1$2[hidden]', $text);
        $text = (string) preg_replace('/bot\d{5,}:[A-Za-z0-9_-]{10,}/i', 'bot[hidden]', $text);
        $text = (string) preg_replace('/(?<![A-Za-z0-9_:-])\d{6,}:[A-Za-z0-9_-]{20,}(?![A-Za-z0-9_-])/', '[hidden]', $text);          // a Telegram bot token on its own
        $text = (string) preg_replace('/(?<![A-Za-z0-9_-])[A-Za-z0-9_\-]{36,}(?![A-Za-z0-9_-])/', '[hidden]', $text);
        return $text;
    }

    /** @return list<string> the plaintext secrets currently configured (so redact() can blank them even when they appear in odd places) */
    private static function knownSecrets(): array
    {
        try {
            return SocialConfig::secretValues();
        } catch (\Throwable) {
            return [];
        }
    }

    /** The error message a platform put in its JSON answer, redacted and clipped (graph / x / linkedin / telegram shapes). */
    public static function apiMessage(array $r): string
    {
        $j = $r['json'] ?? null;
        $msg = '';
        if (is_array($j)) {
            foreach ([['error', 'message'], ['errors', 0, 'message'], ['detail'], ['title'], ['description'], ['message'], ['error_description'], ['error']] as $path) {
                $v = $j;
                foreach ($path as $k) {
                    $v = is_array($v) && array_key_exists($k, $v) ? $v[$k] : null;
                }
                if (is_string($v) && trim($v) !== '') {
                    $msg = $v;
                    break;
                }
            }
        }
        return mb_substr(self::redact(trim((string) preg_replace('/\s+/u', ' ', $msg))), 0, 200);
    }

    /** Is this answer "your credentials are not (or no longer) valid" for the channel? */
    public static function isAuthFailure(string $channel, array $r): bool
    {
        $status = (int) ($r['status'] ?? 0);
        if ($status === 401) {
            return true;
        }
        $j = $r['json'] ?? null;
        if (in_array($channel, ['facebook', 'instagram'], true) && is_array($j)) {
            $code = (int) ($j['error']['code'] ?? 0);
            return in_array($code, [190, 102, 463, 467, 460], true);
        }
        return false;
    }

    /**
     * Turn a FAILED api()/webhook() answer into the SocialResult the dispatcher understands. Adapters call it for every non-2xx answer.
     * 401 / expired token -> auth; 429 -> transient (+Retry-After); timeouts and drops AFTER the request was sent, 502 and 504 -> review (ambiguous);
     * connect errors and 500 / 503 -> transient; other 4xx -> failed with the platform's own (redacted) message.
     */
    public static function toResult(string $channel, array $r): SocialResult
    {
        $kind = (string) ($r['kind'] ?? '');
        $status = (int) ($r['status'] ?? 0);
        if ($kind === 'blocked') {
            return SocialResult::failed((string) ($r['error'] ?? 'The address is not allowed.'), 'blocked');
        }
        if ($kind === 'budget') {
            return SocialResult::transient('There was no time left in this run; it will be tried again shortly.', 30, 'budget');
        }
        if ($kind === 'redirect' || $kind === 'too_large') {
            return SocialResult::failed((string) ($r['error'] ?? 'Unexpected answer.'), $kind);
        }
        if ($kind === 'timeout' || $kind === 'network' || $kind === 'tls') {
            if (!empty($r['ambiguous'])) {
                return SocialResult::review((string) ($r['error'] ?? 'The service did not answer in time.') . ' It may or may not have been posted: check the account.', 'ambiguous');
            }
            return SocialResult::transient((string) ($r['error'] ?? 'Could not reach the service.'), null, $kind);
        }
        if (self::isAuthFailure($channel, $r)) {
            return SocialResult::auth(self::apiMessage($r) !== '' ? self::apiMessage($r) : 'The access token was rejected or has expired.');
        }
        if ($status === 429) {
            return SocialResult::transient('The platform is limiting how fast we post (rate limit).', $r['retry_after'] ?? null, 'rate_limited');
        }
        if ($status === 502 || $status === 504) {
            return SocialResult::review('The platform answered with a gateway error (HTTP ' . $status . '). It may or may not have been posted: check the account.', 'ambiguous');
        }
        if ($status >= 500) {
            return SocialResult::transient('The platform had a problem (HTTP ' . $status . ').', $r['retry_after'] ?? null, 'server_error');
        }
        $msg = self::apiMessage($r);
        return SocialResult::failed('The platform refused the post (HTTP ' . $status . ')' . ($msg !== '' ? ': ' . $msg : '.'), 'rejected');
    }

    /**
     * For TEST CONNECTION: a failed answer -> [state, message] for the admin.
     * @return array{0:string,1:string}
     */
    public static function describeFailure(string $channel, array $r): array
    {
        $res = self::toResult($channel, $r);
        if ($res->state === SocialResult::AUTH) {
            return ['auth', 'The platform rejected the access token (' . ($res->error !== '' ? $res->error : 'invalid or expired') . '). Create a new token and replace it here.'];
        }
        if ($res->state === SocialResult::FAILED || $res->state === SocialResult::REVIEW) {
            return ['error', $res->error];
        }
        return ['error', $res->error !== '' ? $res->error : 'The service could not be reached from this server.'];
    }

    // ------------------------------------------------------------------------------------------------ transport

    /**
     * @param array<string,mixed> $opts
     * @return array<string,mixed> request array for send() or ['error' => text]
     */
    private static function buildRequest(string $method, string $url, array $opts, bool $allowHttp): array
    {
        $headers = ['Accept' => 'application/json', 'User-Agent' => 'LuminaSocial/1.0'];
        foreach ((array) ($opts['headers'] ?? []) as $k => $v) {
            if (!is_string($k) || preg_match('/^[A-Za-z0-9-]{1,60}$/', $k) !== 1 || !is_scalar($v) || preg_match('/[\r\n\x00]/', (string) $v) === 1) {
                return ['error' => 'Illegal header.'];
            }
            $headers[$k] = (string) $v;
        }
        $body = null;
        if (isset($opts['json'])) {
            $body = json_encode($opts['json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            $headers['Content-Type'] = 'application/json';
        } elseif (isset($opts['form'])) {
            $body = http_build_query((array) $opts['form'], '', '&', PHP_QUERY_RFC3986);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        } elseif (isset($opts['body'])) {
            $body = (string) $opts['body'];
            if (!empty($opts['content_type']) && preg_match('/^[A-Za-z0-9\/+.;= -]{3,100}$/', (string) $opts['content_type']) === 1) {
                $headers['Content-Type'] = (string) $opts['content_type'];
            }
        }
        if ($body === false) {
            return ['error' => 'The request body could not be encoded.'];
        }
        $connect = min(8.0, max(0.5, (float) ($opts['connect_timeout'] ?? self::CONNECT_TIMEOUT)));
        $total = min(25.0, max(1.0, (float) ($opts['timeout'] ?? self::TOTAL_TIMEOUT)));
        return [
            'method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body, 'connect_timeout' => $connect, 'timeout' => $total,
            'deadline' => isset($opts['deadline']) ? (float) $opts['deadline'] : null, 'allow_http' => $allowHttp, 'pin' => null, 'max_bytes' => self::MAX_BODY,
        ];
    }

    /**
     * Send one request and normalise the answer.
     * @param array<string,mixed> $req
     * @return array<string,mixed>
     */
    private static function execute(array $req): array
    {
        $t0 = microtime(true);
        if ($req['deadline'] !== null) {
            $left = (float) $req['deadline'] - $t0;
            if ($left < 0.8) {
                return self::fail('budget', 'The time budget of this run is used up.');
            }
            $req['timeout'] = min((float) $req['timeout'], $left);
            $req['connect_timeout'] = min((float) $req['connect_timeout'], max(0.5, $left - 0.3));
        }
        try {
            $t = self::$transport !== null ? (self::$transport)($req) : self::send($req);
        } catch (\Throwable $e) {
            $t = ['status' => 0, 'headers' => [], 'raw' => '', 'kind' => 'network', 'ambiguous' => false, 'detail' => 'transport'];
        }
        $status = (int) ($t['status'] ?? 0);
        $headers = (array) ($t['headers'] ?? []);
        $raw = (string) ($t['raw'] ?? '');
        $kind = (string) ($t['kind'] ?? '');
        $json = null;
        if ($raw !== '' && $kind === '' && ($raw[0] === '{' || $raw[0] === '[')) {
            $d = json_decode($raw, true, 32);
            $json = is_array($d) ? $d : null;
        }
        $retry = null;
        if (isset($headers['retry-after']) && ctype_digit(trim((string) $headers['retry-after']))) {
            $retry = (int) trim((string) $headers['retry-after']);
        } elseif (isset($headers['x-rate-limit-reset']) && ctype_digit(trim((string) $headers['x-rate-limit-reset']))) {
            $retry = max(1, (int) trim((string) $headers['x-rate-limit-reset']) - time());
        }
        $r = [
            'ok' => $kind === '' && $status >= 200 && $status < 300, 'status' => $status, 'json' => $json, 'raw' => $raw, 'headers' => $headers, 'error' => null, 'kind' => $kind,
            'ambiguous' => (bool) ($t['ambiguous'] ?? false) || ($kind === '' && ($status === 502 || $status === 504)), 'retry_after' => $retry, 'ms' => (int) round((microtime(true) - $t0) * 1000),
        ];
        if (!$r['ok']) {
            $r['error'] = self::describe($r);
            self::logFailure($req, $r);
        }
        return $r;
    }

    /** Short safe text for a failed answer. */
    private static function describe(array $r): string
    {
        return match ((string) $r['kind']) {
            'timeout'   => 'The service did not answer in time.',
            'network'   => 'Could not connect to the service.',
            'tls'       => 'The secure connection failed (certificate or TLS problem on this server).',
            'too_large' => 'The answer was larger than allowed.',
            ''          => 'The service answered with HTTP ' . (int) $r['status'] . (self::apiMessage($r) !== '' ? ': ' . self::apiMessage($r) : '.'),
            default     => 'The request could not be completed.',
        };
    }

    /** Log WITHOUT the URL (the Telegram token is part of its path), headers or body. */
    private static function logFailure(array $req, array $r): void
    {
        $host = (string) parse_url((string) $req['url'], PHP_URL_HOST);
        Log::warning('Social request failed', ['host' => $host, 'method' => $req['method'], 'status' => $r['status'], 'kind' => $r['kind'], 'ms' => $r['ms']]);
    }

    /** @return array<string,mixed> */
    private static function fail(string $kind, string $error): array
    {
        return ['ok' => false, 'status' => 0, 'json' => null, 'raw' => '', 'headers' => [], 'error' => $error, 'kind' => $kind, 'ambiguous' => false, 'retry_after' => null, 'ms' => 0];
    }

    /**
     * The real network. cURL when available, otherwise a small HTTP/1.1 client on stream sockets. Both verify TLS, follow no redirect, cap the answer and
     * report whether the request may already have reached the server (ambiguous).
     * @param array<string,mixed> $req
     * @return array{status:int,headers:array<string,string>,raw:string,kind:string,ambiguous:bool,detail:string}
     */
    private static function send(array $req): array
    {
        $scheme = strtolower((string) parse_url((string) $req['url'], PHP_URL_SCHEME));
        if ($scheme !== 'https' && !($scheme === 'http' && $req['allow_http'])) {
            return ['status' => 0, 'headers' => [], 'raw' => '', 'kind' => 'network', 'ambiguous' => false, 'detail' => 'scheme'];
        }
        return function_exists('curl_init') ? self::sendCurl($req) : self::sendStream($req);
    }

    /** @param array<string,mixed> $req */
    private static function sendCurl(array $req): array
    {
        $ch = curl_init((string) $req['url']);
        if ($ch === false) {
            return ['status' => 0, 'headers' => [], 'raw' => '', 'kind' => 'network', 'ambiguous' => false, 'detail' => 'init'];
        }
        $raw = '';
        $headers = [];
        $tooLarge = false;
        $hdr = [];
        foreach ((array) $req['headers'] as $k => $v) {
            $hdr[] = $k . ': ' . $v;
        }
        $opts = [
            CURLOPT_CUSTOMREQUEST   => (string) $req['method'],
            CURLOPT_HTTPHEADER      => $hdr,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_MAXREDIRS       => 0,
            CURLOPT_CONNECTTIMEOUT_MS => (int) round((float) $req['connect_timeout'] * 1000),
            CURLOPT_TIMEOUT_MS      => (int) round((float) $req['timeout'] * 1000),
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_SSL_VERIFYHOST  => 2,
            CURLOPT_PROTOCOLS       => $req['allow_http'] ? (CURLPROTO_HTTP | CURLPROTO_HTTPS) : CURLPROTO_HTTPS,
            CURLOPT_NOSIGNAL        => true,
            CURLOPT_ENCODING        => '',
            CURLOPT_HEADERFUNCTION  => static function ($c, string $line) use (&$headers): int {
                $p = strpos($line, ':');
                if ($p !== false && $p > 0) {
                    $headers[strtolower(trim(substr($line, 0, $p)))] = trim(substr($line, $p + 1));
                }
                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION   => static function ($c, string $chunk) use (&$raw, &$tooLarge, $req): int {
                $raw .= $chunk;
                if (strlen($raw) > (int) $req['max_bytes']) {
                    $tooLarge = true;
                    return 0;                                          // abort an oversized answer
                }
                return strlen($chunk);
            },
        ];
        if ($req['body'] !== null) {
            $opts[CURLOPT_POSTFIELDS] = (string) $req['body'];
        }
        if (!empty($req['pin'])) {
            $u = parse_url((string) $req['url']);
            $opts[CURLOPT_RESOLVE] = [strtolower((string) $u['host']) . ':' . (int) ($u['port'] ?? 443) . ':' . $req['pin']];
        }
        $ca = trim((string) Env::get('SOCIAL_CAFILE', ''));
        if ($ca !== '' && is_file($ca)) {
            $opts[CURLOPT_CAINFO] = $ca;
        }
        curl_setopt_array($ch, $opts);
        $ok = curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $connected = (float) curl_getinfo($ch, CURLINFO_CONNECT_TIME) > 0;
        $secured = (float) curl_getinfo($ch, CURLINFO_APPCONNECT_TIME) > 0;
        curl_close($ch);
        $ssl = str_starts_with((string) $req['url'], 'https://');
        $reached = $connected && (!$ssl || $secured);
        if ($tooLarge) {
            return ['status' => $status, 'headers' => $headers, 'raw' => '', 'kind' => 'too_large', 'ambiguous' => $reached, 'detail' => 'cap'];
        }
        if ($ok === false || $errno !== 0) {
            $kind = match (true) {
                $errno === 28                                   => 'timeout',
                in_array($errno, [35, 51, 58, 59, 60, 64, 66, 77, 80, 82, 83, 90, 91], true) => 'tls',
                default                                         => 'network',
            };
            $sent = $reached && in_array($errno, [28, 52, 55, 56], true);          // timed out, empty reply, send / recv failure: the request may have been executed
            return ['status' => $status, 'headers' => $headers, 'raw' => '', 'kind' => $kind, 'ambiguous' => $sent, 'detail' => 'curl' . $errno];
        }
        if ($status === 0) {
            return ['status' => 0, 'headers' => $headers, 'raw' => '', 'kind' => 'network', 'ambiguous' => $reached, 'detail' => 'nostatus'];
        }
        return ['status' => $status, 'headers' => $headers, 'raw' => $raw, 'kind' => '', 'ambiguous' => false, 'detail' => ''];
    }

    /** @param array<string,mixed> $req */
    private static function sendStream(array $req): array
    {
        $u = parse_url((string) $req['url']);
        $https = strtolower((string) ($u['scheme'] ?? '')) === 'https';
        $host = (string) ($u['host'] ?? '');
        $port = (int) ($u['port'] ?? ($https ? 443 : 80));
        $target = $req['pin'] ?? $host;
        if (is_string($target) && str_contains($target, ':') && !str_starts_with($target, '[')) {
            $target = '[' . $target . ']';
        }
        $ssl = ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => trim($host, '[]'), 'SNI_enabled' => true, 'SNI_server_name' => trim($host, '[]'), 'allow_self_signed' => false];
        $ca = trim((string) Env::get('SOCIAL_CAFILE', ''));
        if ($ca !== '' && is_file($ca)) {
            $ssl['cafile'] = $ca;
        }
        $ctx = stream_context_create(['ssl' => $ssl]);
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client(($https ? 'ssl' : 'tcp') . '://' . $target . ':' . $port, $errno, $errstr, (float) $req['connect_timeout'], STREAM_CLIENT_CONNECT, $ctx);
        if ($fp === false) {
            $tls = $https && (stripos($errstr, 'certificate') !== false || stripos($errstr, 'ssl') !== false || stripos($errstr, 'crypto') !== false);
            return ['status' => 0, 'headers' => [], 'raw' => '', 'kind' => $tls ? 'tls' : 'network', 'ambiguous' => false, 'detail' => 'connect'];
        }
        $start = microtime(true);
        $limit = (float) $req['timeout'];
        $path = ($u['path'] ?? '/') === '' ? '/' : (string) ($u['path'] ?? '/');
        if (isset($u['query'])) {
            $path .= '?' . $u['query'];
        }
        $lines = [(string) $req['method'] . ' ' . $path . ' HTTP/1.1', 'Host: ' . $host . (isset($u['port']) && $u['port'] !== ($https ? 443 : 80) ? ':' . $u['port'] : ''), 'Connection: close', 'Accept-Encoding: identity'];
        foreach ((array) $req['headers'] as $k => $v) {
            $lines[] = $k . ': ' . $v;
        }
        $body = $req['body'] !== null ? (string) $req['body'] : '';
        if ($req['body'] !== null || (string) $req['method'] === 'POST') {
            $lines[] = 'Content-Length: ' . strlen($body);
        }
        $msg = implode("\r\n", $lines) . "\r\n\r\n" . $body;
        stream_set_timeout($fp, max(1, (int) ceil($limit)));
        $written = 0;
        while ($written < strlen($msg)) {
            $n = @fwrite($fp, substr($msg, $written));
            if ($n === false || $n === 0 || microtime(true) - $start > $limit) {
                fclose($fp);
                return ['status' => 0, 'headers' => [], 'raw' => '', 'kind' => 'timeout', 'ambiguous' => $written > 0, 'detail' => 'write'];
            }
            $written += $n;
        }
        $buf = '';
        $max = (int) $req['max_bytes'] + 65536;
        while (!feof($fp)) {
            $chunk = @fread($fp, 8192);
            $meta = stream_get_meta_data($fp);
            if ($chunk === false || (!empty($meta['timed_out'])) || microtime(true) - $start > $limit) {
                fclose($fp);
                return ['status' => 0, 'headers' => [], 'raw' => '', 'kind' => 'timeout', 'ambiguous' => true, 'detail' => 'read'];
            }
            $buf .= $chunk;
            if (strlen($buf) > $max) {
                fclose($fp);
                return ['status' => 0, 'headers' => [], 'raw' => '', 'kind' => 'too_large', 'ambiguous' => true, 'detail' => 'cap'];
            }
        }
        fclose($fp);
        $split = strpos($buf, "\r\n\r\n");
        if ($split === false || preg_match('#^HTTP/\d(?:\.\d)?\s+(\d{3})#', $buf, $m) !== 1) {
            return ['status' => 0, 'headers' => [], 'raw' => '', 'kind' => 'network', 'ambiguous' => true, 'detail' => 'parse'];
        }
        $headers = [];
        foreach (explode("\r\n", substr($buf, 0, $split)) as $i => $line) {
            $p = strpos($line, ':');
            if ($i > 0 && $p !== false && $p > 0) {
                $headers[strtolower(trim(substr($line, 0, $p)))] = trim(substr($line, $p + 1));
            }
        }
        $raw = substr($buf, $split + 4);
        if (stripos((string) ($headers['transfer-encoding'] ?? ''), 'chunked') !== false) {
            $raw = self::dechunk($raw);
        }
        if (strlen($raw) > (int) $req['max_bytes']) {
            return ['status' => (int) $m[1], 'headers' => $headers, 'raw' => '', 'kind' => 'too_large', 'ambiguous' => true, 'detail' => 'cap'];
        }
        return ['status' => (int) $m[1], 'headers' => $headers, 'raw' => $raw, 'kind' => '', 'ambiguous' => false, 'detail' => ''];
    }

    private static function dechunk(string $data): string
    {
        $out = '';
        $pos = 0;
        $len = strlen($data);
        while ($pos < $len) {
            $eol = strpos($data, "\r\n", $pos);
            if ($eol === false) {
                break;
            }
            $size = hexdec(trim(explode(';', substr($data, $pos, $eol - $pos))[0]));
            if ($size <= 0) {
                break;
            }
            $out .= substr($data, $eol + 2, (int) $size);
            $pos = $eol + 2 + (int) $size + 2;
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** Resolve a redirect Location against the URL it came from. */
    private static function absolutize(string $base, string $loc): string
    {
        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $loc) === 1) {
            return $loc;
        }
        $p = parse_url($base);
        $origin = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
        if (str_starts_with($loc, '//')) {
            return ($p['scheme'] ?? 'https') . ':' . $loc;
        }
        if (str_starts_with($loc, '/')) {
            return $origin . $loc;
        }
        $dir = rtrim(substr((string) ($p['path'] ?? '/'), 0, (int) strrpos((string) ($p['path'] ?? '/'), '/') + 1), '/');
        return $origin . $dir . '/' . $loc;
    }

    /** Is the binary address inside the CIDR block? */
    private static function inCidr(string $bin, string $cidr): bool
    {
        [$net, $bits] = explode('/', $cidr);
        $nb = inet_pton($net);
        if ($nb === false || strlen($nb) !== strlen($bin)) {
            return false;
        }
        $bits = (int) $bits;
        $bytes = intdiv($bits, 8);
        if ($bytes > 0 && substr($bin, 0, $bytes) !== substr($nb, 0, $bytes)) {
            return false;
        }
        $rem = $bits % 8;
        if ($rem === 0) {
            return true;
        }
        $mask = (0xFF << (8 - $rem)) & 0xFF;
        return (ord($bin[$bytes]) & $mask) === (ord($nb[$bytes]) & $mask);
    }
}
