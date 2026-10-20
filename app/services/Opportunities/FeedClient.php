<?php
declare(strict_types=1);

namespace App\Services\Opportunities;

use App\Services\Social\SocialHttp;

/**
 * Downloads one RSS/Atom feed for the Opportunities module. The address is typed by an administrator, so it gets the same care as a webhook:
 * http/https only, no user name or password, ports 80/443 only, a real host name (no `localhost`, no numeric IP spellings) whose EVERY address is public,
 * the request is PINNED to the address that was checked (no second DNS lookup), at most 3 redirects and each one is checked again, 15 s total,
 * answer capped at 3 MB. Returns ['ok' => bool, 'body' => string, 'error' => ?string]; the error text is short and safe to show.
 * Tests can pass $transport (fn(array $request): array{status:int,body:string,location?:string}) to avoid the network, and $resolver for DNS.
 */
final class FeedClient
{
    public const MAX_BODY = 3145728;
    private const BAD_SUFFIXES = ['localhost', 'local', 'localdomain', 'internal', 'intranet', 'lan', 'home', 'corp', 'private', 'test', 'example', 'invalid', 'onion', 'arpa'];

    /** @return array{ok:bool,host?:string,port?:int,ips?:list<string>,url?:string,error?:string} */
    public static function guard(string $url, ?\Closure $resolver = null): array
    {
        $url = trim($url);
        if ($url === '' || strlen($url) > 500 || preg_match('/[\x00-\x20\x7f]/', $url) === 1) {
            return ['ok' => false, 'error' => 'That web address is not valid.'];
        }
        $p = parse_url($url);
        if ($p === false || !isset($p['host']) || !in_array(strtolower((string) ($p['scheme'] ?? '')), ['http', 'https'], true)) {
            return ['ok' => false, 'error' => 'The address must start with http:// or https://'];
        }
        if (isset($p['user']) || isset($p['pass'])) {
            return ['ok' => false, 'error' => 'The address must not contain a user name or password.'];
        }
        $https = strtolower((string) $p['scheme']) === 'https';
        $port = (int) ($p['port'] ?? ($https ? 443 : 80));
        if (!in_array($port, [80, 443], true)) {
            return ['ok' => false, 'error' => 'Only the normal web ports (80 and 443) are allowed.'];
        }
        $host = strtolower(trim((string) $p['host'], '[]'));
        $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
        if (!$isIp) {
            if (preg_match('/^(?=.{1,253}$)([a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $host) !== 1) {
                return ['ok' => false, 'error' => 'That is not a real website name.'];
            }
            $last = substr($host, (int) strrpos($host, '.') + 1);
            if (in_array($last, self::BAD_SUFFIXES, true)) {
                return ['ok' => false, 'error' => 'That address is not a public website.'];
            }
        }
        $ips = $isIp ? [$host] : SocialHttp::resolve($host, $resolver);
        if ($ips === []) {
            return ['ok' => false, 'error' => 'The website name could not be found.'];
        }
        foreach ($ips as $ip) {
            if (!SocialHttp::isPublicIp($ip)) {
                return ['ok' => false, 'error' => 'That address is not a public website.'];
            }
        }
        $clean = ($https ? 'https' : 'http') . '://' . $host . ($port !== ($https ? 443 : 80) ? ':' . $port : '') . ($p['path'] ?? '/') . (isset($p['query']) ? '?' . $p['query'] : '');
        return ['ok' => true, 'host' => $host, 'port' => $port, 'ips' => array_values($ips), 'url' => $clean];
    }

    /** @return array{ok:bool,body:string,error:?string} */
    public static function get(string $url, ?\Closure $transport = null, ?\Closure $resolver = null): array
    {
        $current = $url;
        for ($hop = 0; $hop <= 3; $hop++) {
            $g = self::guard($current, $resolver);
            if (!$g['ok']) {
                return ['ok' => false, 'body' => '', 'error' => (string) $g['error']];
            }
            $req = ['url' => $g['url'], 'host' => $g['host'], 'port' => $g['port'], 'ip' => $g['ips'][0]];
            $r = $transport !== null ? $transport($req) : self::curl($req);
            if (isset($r['error'])) {
                return ['ok' => false, 'body' => '', 'error' => (string) $r['error']];
            }
            $status = (int) ($r['status'] ?? 0);
            if ($status >= 300 && $status < 400 && !empty($r['location'])) {
                $current = self::absolutize($g['url'], (string) $r['location']);
                continue;
            }
            if ($status < 200 || $status >= 300) {
                return ['ok' => false, 'body' => '', 'error' => 'The website answered with error ' . $status . '.'];
            }
            return ['ok' => true, 'body' => (string) $r['body'], 'error' => null];
        }
        return ['ok' => false, 'body' => '', 'error' => 'The website redirected too many times.'];
    }

    /** @return array{status?:int,body?:string,location?:string,error?:string} */
    private static function curl(array $req): array
    {
        if (!function_exists('curl_init')) {
            return ['error' => 'This server cannot download feeds (the PHP cURL extension is missing).'];
        }
        $ip = (string) $req['ip'];
        $resolve = $req['host'] . ':' . $req['port'] . ':' . (str_contains($ip, ':') ? '[' . $ip . ']' : $ip);
        $raw = '';
        $tooLarge = false;
        $location = '';
        $ch = curl_init($req['url']);
        curl_setopt_array($ch, [
            CURLOPT_RESOLVE        => [$resolve],
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_ENCODING       => '',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; LuminaAfricaFoundationBot/1.0; +' . abs_url('') . ')',
            CURLOPT_HTTPHEADER     => ['Accept: application/rss+xml, application/atom+xml, application/xml;q=0.9, text/xml;q=0.8, */*;q=0.5'],
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_HEADERFUNCTION => static function ($c, string $line) use (&$location): int {
                if (stripos($line, 'location:') === 0) {
                    $location = trim(substr($line, 9));
                }
                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION  => static function ($c, string $chunk) use (&$raw, &$tooLarge): int {
                if (strlen($raw) + strlen($chunk) > self::MAX_BODY) {
                    $tooLarge = true;
                    return 0;
                }
                $raw .= $chunk;
                return strlen($chunk);
            },
        ]);
        curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($tooLarge) {
            return ['error' => 'The feed is too large.'];
        }
        if ($errno !== 0 && $status === 0) {
            if ($errno === CURLE_OPERATION_TIMEDOUT) {
                return ['error' => 'The website took too long to answer.'];
            }
            if (in_array($errno, [35, 51, 58, 60, 77], true)) {
                return ['error' => 'The security certificate of the website could not be verified.'];
            }
            return ['error' => 'The website could not be reached.'];
        }
        return ['status' => $status, 'body' => $raw, 'location' => $location];
    }

    private static function absolutize(string $base, string $loc): string
    {
        if (preg_match('~^https?://~i', $loc) === 1) {
            return $loc;
        }
        $p = parse_url($base);
        $origin = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
        if (str_starts_with($loc, '/')) {
            return $origin . $loc;
        }
        $dir = rtrim(dirname((string) ($p['path'] ?? '/')), '/');
        return $origin . $dir . '/' . $loc;
    }
}
