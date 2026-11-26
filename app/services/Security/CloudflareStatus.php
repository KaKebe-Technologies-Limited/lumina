<?php
declare(strict_types=1);

namespace App\Services\Security;

use App\Core\Cloudflare;
use App\Core\Settings;

/**
 * Admin > Security > Cloudflare: the owner's checklist (ticks stored as settings) and the cache-header check.
 * No secret ever passes through here. The cache check fetches the site's OWN public pages without a cookie and reports what a shared cache
 * (Cloudflare) would be told: sensitive areas must say "no-store"; nothing may say "public" / "s-maxage".
 */
final class CloudflareStatus
{
    /** key => [title, plain-English hint]. The ticks are the owner's own record; nothing is verified automatically. */
    public const STEPS = [
        'account'    => ['Cloudflare account created', 'Free plan is enough.'],
        'dns_copied' => ['Every existing DNS record copied, mail records set to "DNS only" (grey cloud)', 'Check MX, SPF, DKIM and DMARC records twice.'],
        'nameservers' => ['Name servers changed at Hostinger to the two Cloudflare names', 'Wait until Cloudflare says "Active".'],
        'ssl_strict' => ['SSL/TLS mode = Full (strict), Always Use HTTPS on', 'Never "Flexible": it causes a redirect loop.'],
        'env_proxy'  => ['CLOUDFLARE_PROXY=1 added to the server .env file', 'Makes the site see each visitor\'s real address.'],
        'cache_rule' => ['Cache bypass rule for the admin, academy, membership, shop, donate, pay and api areas', 'Section 7 of docs/CLOUDFLARE.md.'],
        'waf'        => ['Managed WAF rules, Bot Fight Mode and Browser Integrity Check on', 'Security > WAF and Security > Bots.'],
        'rate_rules' => ['Rate limiting rule for the sign-in and form addresses', 'Section 8 of docs/CLOUDFLARE.md.'],
        'webhooks'   => ['Payment notification addresses (/pay/pesapal/ipn, /shop/webhook/*) are skipped by the bot rules', 'Otherwise payments may stay "pending".'],
        'rocket_off' => ['Rocket Loader and Auto Minify are off', 'They can break the site\'s scripts.'],
        'hsts'       => ['HSTS decided (only after everything works over HTTPS for a week)', 'Do not tick "preload" lightly.'],
        'notify'     => ['Cloudflare notifications set up to your e-mail', 'Notifications > Add.'],
        'privacy'    => ['Privacy page mentions Cloudflare (it sees visitors\' IP addresses)', 'See docs/CLOUDFLARE.md section 14.'],
    ];

    /** area => [path, sensitive?] used by the cache check */
    public const PROBES = [
        ['/', false], ['/admin/login', true], ['/academy/login', true], ['/membership/register', true], ['/donate', true], ['/shop', true],
        ['/api/newsletters', false], ['/contact', false],
    ];

    /** @var ?\Closure(string):array<string,mixed> test hook: url => ['status'=>int,'headers'=>array lower-case name => value] or ['error'=>string] */
    public static ?\Closure $transport = null;

    /** @return array<string,bool> */
    public static function ticks(): array
    {
        $raw = json_decode((string) Settings::get('security_cf_steps', ''), true);
        $out = [];
        foreach (array_keys(self::STEPS) as $k) {
            $out[$k] = is_array($raw) && !empty($raw[$k]);
        }
        return $out;
    }

    /** @param list<string> $done keys the owner ticked */
    public static function saveTicks(array $done): void
    {
        $save = [];
        foreach (array_keys(self::STEPS) as $k) {
            $save[$k] = in_array($k, $done, true);
        }
        Settings::setMany(['security_cf_steps' => (string) json_encode($save)]);
    }

    /** The last stored cache check, or null. @return ?array{at:string,rows:list<array<string,mixed>>} */
    public static function lastCheck(): ?array
    {
        $raw = json_decode((string) Settings::get('security_cf_cache', ''), true);
        return is_array($raw) && isset($raw['rows']) ? $raw : null;
    }

    /** Fetch every probe page from $baseUrl and judge its headers; stored when $store. @return list<array<string,mixed>> */
    public static function runCacheCheck(string $baseUrl, bool $store = true): array
    {
        $rows = [];
        foreach (self::PROBES as [$path, $sensitive]) {
            $res = self::fetch(rtrim($baseUrl, '/') . $path);
            $rows[] = ['path' => $path, 'sensitive' => $sensitive] + self::evaluate($res, $sensitive);
        }
        if ($store) {
            Settings::setMany(['security_cf_cache' => (string) json_encode(['at' => date('Y-m-d H:i'), 'rows' => $rows])]);
        }
        return $rows;
    }

    /**
     * Judge one answer. status: ok | warn | bad | unknown.
     * @param array<string,mixed> $res
     * @return array{status:string,cache_control:string,cf_cache:string,note:string,http:int}
     */
    public static function evaluate(array $res, bool $sensitive): array
    {
        if (isset($res['error'])) {
            return ['status' => 'unknown', 'cache_control' => '', 'cf_cache' => '', 'note' => 'Could not fetch this page (' . preg_replace('/[^a-z ]/i', '', (string) $res['error']) . ').', 'http' => 0];
        }
        $h = (array) ($res['headers'] ?? []);
        $cc = strtolower(trim((string) ($h['cache-control'] ?? '')));
        $cf = strtolower(trim((string) ($h['cf-cache-status'] ?? '')));
        $http = (int) ($res['status'] ?? 0);
        $base = ['cache_control' => mb_substr($cc, 0, 120), 'cf_cache' => mb_substr($cf, 0, 30), 'http' => $http];
        if (preg_match('/(^|[ ,])(public|s-maxage)/', $cc) === 1) {
            if ($sensitive) {
                return ['status' => 'bad', 'note' => 'A shared cache is allowed to store this page.'] + $base;
            }
            // public data that must be re-checked on every request (the newsletter list) is fine; anything kept for a while is only a warning
            if (!str_contains($cc, 'max-age=0') || str_contains($cc, 's-maxage')) {
                return ['status' => 'warn', 'note' => 'Public data that a cache may keep for a while (fine for public pages).'] + $base;
            }
        }
        if (in_array($cf, ['hit', 'stale', 'revalidated', 'updating'], true)) {
            return ['status' => $sensitive ? 'bad' : 'warn', 'note' => 'Cloudflare served this page from its cache.'] + $base;
        }
        if ($sensitive && !str_contains($cc, 'no-store')) {
            return ['status' => 'bad', 'note' => 'Expected "no-store" for a private area.'] + $base;
        }
        if ($cc === '') {
            return ['status' => 'warn', 'note' => 'No Cache-Control header sent.'] + $base;
        }
        if (!$sensitive && !str_contains($cc, 'private') && !str_contains($cc, 'no-store') && !str_contains($cc, 'max-age=0')) {
            return ['status' => 'warn', 'note' => 'This page may be kept by a shared cache.'] + $base;
        }
        return ['status' => 'ok', 'note' => $sensitive ? 'Never stored.' : 'Revalidated every time.'] + $base;
    }

    /** @return array<string,mixed> */
    private static function fetch(string $url): array
    {
        if (self::$transport !== null) {
            try {
                return (array) (self::$transport)($url);
            } catch (\Throwable) {
                return ['error' => 'failed'];
            }
        }
        if (!function_exists('curl_init')) {
            return ['error' => 'curl missing'];
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_USERAGENT => 'LuminaCacheCheck/1', CURLOPT_SSL_VERIFYPEER => true, CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS]);
        $raw = curl_exec($ch);
        $size = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($raw === false || $status === 0) {
            return ['error' => 'no answer'];
        }
        $headers = [];
        foreach (explode("\n", substr((string) $raw, 0, $size)) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $headers[strtolower(trim($k))] = trim($v);
            }
        }
        return ['status' => $status, 'headers' => $headers];
    }

    /** Facts about THIS request for the card (never a secret). @return array<string,mixed> */
    public static function request(\App\Core\Request $request): array
    {
        $i = Cloudflare::inspect($request, $_SERVER);
        $i['ranges_date'] = Cloudflare::RANGES_DATE;
        $i['through'] = $i['ray'] && ($i['peer_is_cf'] || $i['via_cf']);   // CF-Ray present AND a published Cloudflare address is in the trusted chain
        return $i;
    }
}
