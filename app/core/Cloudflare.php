<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Cloudflare awareness (docs/CLOUDFLARE.md). Everything here is inert until Cloudflare really sits in front of the site AND the owner sets
 * CLOUDFLARE_PROXY=1 in the server .env: without that line no Cloudflare address is trusted and every header below is ignored.
 *
 * WHY the address list matters: a visitor's own browser can send "CF-Connecting-IP: 1.2.3.4" to the origin directly. The header is only
 * believed when the TCP peer (REMOTE_ADDR) is a published Cloudflare address, which cannot be faked over a real connection.
 *
 * The ranges are Cloudflare's published lists https://www.cloudflare.com/ips-v4 and /ips-v6, copied on 2026-11-26. They change rarely; if a
 * visitor ever looks like a Cloudflare address, add the new range to CLOUDFLARE_EXTRA_RANGES in .env (comma list) without waiting for a release.
 */
final class Cloudflare
{
    public const RANGES_DATE = '2026-11-26';

    public const IPV4 = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20',
        '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
    ];
    public const IPV6 = [
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];

    /**
     * Areas that must never sit in a shared cache (Cloudflare, a browser-company proxy ...): sign-in, the admin, the learner / member / shop / payment
     * areas and the JSON API. The application answers every page below these prefixes with "private, no-store"; the same list is the one the owner
     * puts in the Cloudflare "bypass cache" rule (docs/CLOUDFLARE.md section 7).
     */
    public const NO_STORE_PREFIXES = [
        '/admin', '/academy', '/membership', '/shop', '/donate', '/pay', '/api', '/forms', '/fund-a-project', '/track-application',
        '/ambassador', '/install', '/privacy/request', '/meeting', '/contact/meeting-request', '/bono',
    ];

    public static function noStorePath(string $path): bool
    {
        $path = '/' . ltrim($path, '/');
        foreach (self::NO_STORE_PREFIXES as $p) {
            if ($path === $p || str_starts_with($path, $p . '/')) {
                return true;
            }
        }
        return false;
    }

    /** CLOUDFLARE_PROXY=1 in the .env: the site is behind Cloudflare, so the published ranges are trusted proxies. */
    public static function proxyEnabled(): bool
    {
        return (bool) Config::get('cloudflare.proxy', false);
    }

    /** All Cloudflare ranges (built-in + CLOUDFLARE_EXTRA_RANGES), whether or not they are trusted. @return list<string> */
    public static function ranges(): array
    {
        return array_values(array_unique(array_merge(self::IPV4, self::IPV6, (array) Config::get('cloudflare.extra_ranges', []))));
    }

    /** True when $ip belongs to Cloudflare's published ranges (a fact about the address; says nothing about trust). */
    public static function isCloudflareIp(string $ip): bool
    {
        return Request::ipInList($ip, self::ranges());
    }

    /** The proxies whose forwarding headers are believed: TRUSTED_PROXIES plus, only with CLOUDFLARE_PROXY=1, Cloudflare. @return list<string> */
    public static function trustedList(): array
    {
        $list = array_values((array) Config::get('trusted_proxies', []));
        return self::proxyEnabled() ? array_merge($list, self::ranges()) : $list;
    }

    /**
     * Read-only facts about one request for Admin > Security. Never contains a secret.
     * @return array{ray:bool,peer:string,peer_is_cf:bool,trusted:bool,visitor:string,https:bool,country:string,proxy_enabled:bool,via_cf:bool}
     */
    public static function inspect(Request $request, array $server): array
    {
        $peer = (string) ($server['REMOTE_ADDR'] ?? '');
        $isCf = $peer !== '' && self::isCloudflareIp($peer);
        $ray = isset($server['HTTP_CF_RAY']) && preg_match('/^[0-9a-f]{8,20}(-[A-Z]{3})?$/', (string) $server['HTTP_CF_RAY']) === 1;
        $country = strtoupper(trim((string) ($server['HTTP_CF_IPCOUNTRY'] ?? '')));
        return [
            'ray' => $ray, 'peer' => $peer, 'peer_is_cf' => $isCf, 'trusted' => self::proxyEnabled() && $request->viaCloudflare(),
            'visitor' => $request->ip(), 'https' => $request->isSecure(),
            'country' => preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : '',
            'proxy_enabled' => self::proxyEnabled(), 'via_cf' => $request->viaCloudflare(),
        ];
    }
}
