<?php
declare(strict_types=1);

namespace App\Services\Social;

/**
 * OAuth 1.0a request signing (RFC 5849), HMAC-SHA1, for the X (Twitter) API in "user context" (docs/SOCIAL.md section 4.3).
 *
 * Only what the site needs: a signed Authorization header for a request whose body is JSON (a JSON body is NOT part of the signature base string) or a
 * form-urlencoded body (its fields are). Duplicate parameter names are kept (RFC 5849 3.4.1.3.2). The class holds the four credentials only for the length of one
 * request and never returns, logs or throws them: the only output is the finished header (which carries the oauth_token and the signature, as the protocol requires).
 *
 * Verified against RFC 5849 section 3.4.1 (base string of the specification's own example) and the worked example of X's documentation "Creating a signature"
 * (bin/selftest-social-channels.php).
 */
final class OAuth1Signer
{
    public function __construct(
        #[\SensitiveParameter] private readonly string $consumerKey,
        #[\SensitiveParameter] private readonly string $consumerSecret,
        #[\SensitiveParameter] private readonly string $token,
        #[\SensitiveParameter] private readonly string $tokenSecret,
    ) {
    }

    /** RFC 3986 percent-encoding (RFC 5849 3.6): everything except A-Z a-z 0-9 - . _ ~ */
    public static function encode(string $value): string
    {
        return rawurlencode($value);
    }

    /**
     * The "base string URI" (RFC 5849 3.4.1.2): lower-case scheme and host, the port only when it is not the default of the scheme, no query, no fragment.
     */
    public static function baseUri(string $url): string
    {
        $p = parse_url($url);
        if ($p === false || !isset($p['scheme'], $p['host'])) {
            return '';
        }
        $scheme = strtolower((string) $p['scheme']);
        $host = strtolower((string) $p['host']);
        if (str_contains($host, ':') && !str_starts_with($host, '[')) {
            $host = '[' . $host . ']';
        }
        $port = isset($p['port']) ? (int) $p['port'] : null;
        if ($port !== null && !(($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443))) {
            $host .= ':' . $port;
        }
        $path = (string) ($p['path'] ?? '');
        return $scheme . '://' . $host . ($path === '' ? '/' : $path);
    }

    /**
     * Split a query string or an application/x-www-form-urlencoded body into DECODED name/value pairs, keeping duplicates and order.
     * @return list<array{0:string,1:string}>
     */
    public static function parsePairs(string $encoded): array
    {
        $pairs = [];
        foreach (explode('&', $encoded) as $part) {
            if ($part === '') {
                continue;
            }
            $kv = explode('=', $part, 2);
            $pairs[] = [urldecode($kv[0]), urldecode($kv[1] ?? '')];
        }
        return $pairs;
    }

    /**
     * The signature base string (RFC 5849 3.4.1): METHOD & encoded base URI & encoded normalised parameters.
     * @param list<array{0:string,1:string}> $pairs every request parameter (query, form body) AND the oauth_* protocol parameters except oauth_signature
     */
    public static function baseString(string $method, string $url, array $pairs): string
    {
        $enc = [];
        foreach ($pairs as [$k, $v]) {
            $enc[] = [self::encode((string) $k), self::encode((string) $v)];
        }
        usort($enc, static fn(array $a, array $b): int => strcmp($a[0], $b[0]) ?: strcmp($a[1], $b[1]));   // by encoded name, then encoded value (byte order)
        $norm = implode('&', array_map(static fn(array $p): string => $p[0] . '=' . $p[1], $enc));
        return strtoupper($method) . '&' . self::encode(self::baseUri($url)) . '&' . self::encode($norm);
    }

    /** base64(HMAC-SHA1(base string, "consumer secret&token secret")), both secrets percent-encoded (RFC 5849 3.4.2). */
    public static function hmacSha1(string $baseString, string $consumerSecret, string $tokenSecret): string
    {
        return base64_encode(hash_hmac('sha1', $baseString, self::encode($consumerSecret) . '&' . self::encode($tokenSecret), true));
    }

    /**
     * The protocol parameters (with oauth_signature) for one request.
     *
     * @param string                         $url   the EXACT final address, query included (the signature covers its query parameters)
     * @param array<string,scalar>|list<array{0:string,1:string}> $form fields of a form-urlencoded body ([] for a JSON body)
     * @return array<string,string>
     */
    public function parameters(string $method, string $url, array $form = [], ?string $nonce = null, ?int $timestamp = null): array
    {
        $oauth = [
            'oauth_consumer_key'     => $this->consumerKey,
            'oauth_nonce'            => $nonce ?? bin2hex(random_bytes(16)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => (string) ($timestamp ?? time()),
            'oauth_token'            => $this->token,
            'oauth_version'          => '1.0',
        ];
        $pairs = [];
        $query = parse_url($url, PHP_URL_QUERY);
        if (is_string($query) && $query !== '') {
            array_push($pairs, ...self::parsePairs($query));
        }
        foreach ($form as $k => $v) {
            if (is_array($v) && count($v) === 2 && array_is_list($v)) {
                $pairs[] = [(string) $v[0], (string) $v[1]];      // already a name/value pair (duplicates allowed)
            } elseif (is_scalar($v)) {
                $pairs[] = [(string) $k, (string) $v];
            }
        }
        foreach ($oauth as $k => $v) {
            $pairs[] = [$k, $v];
        }
        $oauth['oauth_signature'] = self::hmacSha1(self::baseString($method, $url, $pairs), $this->consumerSecret, $this->tokenSecret);
        return $oauth;
    }

    /**
     * The value of the Authorization header: `OAuth oauth_consumer_key="..", ...` (parameters sorted by name, values percent-encoded).
     * @param array<string,scalar>|list<array{0:string,1:string}> $form
     */
    public function header(string $method, string $url, array $form = [], ?string $nonce = null, ?int $timestamp = null): string
    {
        $p = $this->parameters($method, $url, $form, $nonce, $timestamp);
        ksort($p);
        $parts = [];
        foreach ($p as $k => $v) {
            $parts[] = self::encode($k) . '="' . self::encode($v) . '"';
        }
        return 'OAuth ' . implode(', ', $parts);
    }

    /**
     * Adapter for SocialHttp::api()'s `authorize` option: callable(method, url, query, form): array<string,string> of extra headers.
     * The query is part of $url already, so it is read from there.
     */
    public function authorizer(): \Closure
    {
        return fn(string $method, string $url, array $query, array $form): array => ['Authorization' => $this->header($method, $url, $form)];
    }

    /** Nothing secret may leak through a dump or an exception trace. */
    public function __debugInfo(): array
    {
        return ['signer' => 'OAuth1Signer'];
    }
}
