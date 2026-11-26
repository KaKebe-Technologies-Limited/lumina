<?php
declare(strict_types=1);

/**
 * CLOUDFLARE readiness: the real visitor address behind Cloudflare (and header spoofing), CIDR ranges, HSTS only on real HTTPS,
 * cache headers on private pages, protected paths, optional Turnstile (fake transport), and the Admin > Security Cloudflare card.
 * Nothing here talks to Cloudflare. Every setting touched is put back; the throw-away users are removed.
 * Run alone: /c/xampp/php/php.exe bin/selftest-cloudflare.php      The HTTP parts need MySQL and Apache.
 */

use App\Core\Auth;
use App\Core\Cloudflare;
use App\Core\Config;
use App\Core\Database;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Models\User;
use App\Services\Security\CloudflareStatus;
use App\Services\Security\Turnstile;

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (Config::get('app.env') !== 'local') {
        exit('Refusing to run outside APP_ENV=local.' . PHP_EOL);
    }
    $fail = 0;
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
    $standalone = true;
}

echo "-- cloudflare --\n";
$savedProxy = Config::get('cloudflare.proxy');
$savedExtra = Config::get('cloudflare.extra_ranges');
$savedTrusted = Config::get('trusted_proxies');
$savedTs = Config::get('turnstile');
$savedServer = $_SERVER;

$cf = static function (array $server, bool $proxy, array $trusted = [], array $extra = []): array {
    Config::set('cloudflare.proxy', $proxy);
    Config::set('cloudflare.extra_ranges', $extra);
    Config::set('trusted_proxies', $trusted);
    $r = new Request($server + ['REQUEST_URI' => '/', 'REQUEST_METHOD' => 'GET'], [], [], []);
    return ['ip' => $r->ip(), 'cf' => $r->viaCloudflare(), 'https' => $r->isSecure(), 'req' => $r];
};

echo "-- real visitor address --\n";
$edge = '104.16.5.5';
$r = $cf(['REMOTE_ADDR' => $edge, 'HTTP_CF_CONNECTING_IP' => '198.51.100.7'], true);
check('behind Cloudflare (CLOUDFLARE_PROXY=1) the visitor is CF-Connecting-IP', $r['ip'] === '198.51.100.7' && $r['cf'] === true);
$r = $cf(['REMOTE_ADDR' => $edge, 'HTTP_CF_CONNECTING_IP' => '198.51.100.7'], false);
check('without CLOUDFLARE_PROXY nothing changes: the header is ignored', $r['ip'] === $edge && $r['cf'] === false);
$r = $cf(['REMOTE_ADDR' => '203.0.113.9', 'HTTP_CF_CONNECTING_IP' => '1.2.3.4', 'HTTP_X_FORWARDED_FOR' => '1.2.3.4', 'HTTP_CF_VISITOR' => '{"scheme":"https"}', 'HTTP_X_FORWARDED_PROTO' => 'https'], true);
check('SPOOF: a visitor who calls the server directly cannot choose its address (CF-Connecting-IP / X-Forwarded-For ignored)', $r['ip'] === '203.0.113.9' && $r['cf'] === false);
check('SPOOF: ... nor make the request look like HTTPS (CF-Visitor / X-Forwarded-Proto ignored)', $r['https'] === false);
$r = $cf(['REMOTE_ADDR' => '203.0.113.9', 'HTTP_CF_CONNECTING_IP' => '1.2.3.4'], false);
check('SPOOF: same with CLOUDFLARE_PROXY off', $r['ip'] === '203.0.113.9');
$r = $cf(['REMOTE_ADDR' => '2606:4700::6810:1', 'HTTP_CF_CONNECTING_IP' => '2001:db8::5'], true);
check('IPv6 Cloudflare edge and an IPv6 visitor', $r['ip'] === '2001:db8::5' && $r['cf']);
$r = $cf(['REMOTE_ADDR' => '2001:db8:ffff::1', 'HTTP_CF_CONNECTING_IP' => '9.9.9.9'], true);
check('SPOOF over IPv6: a non-Cloudflare IPv6 peer is not believed', $r['ip'] === '2001:db8:ffff::1');
$r = $cf(['REMOTE_ADDR' => $edge, 'HTTP_CF_CONNECTING_IP' => 'not-an-ip', 'HTTP_X_FORWARDED_FOR' => '198.51.100.8'], true);
check('a garbage CF-Connecting-IP is ignored; the forwarded chain still names the visitor', $r['ip'] === '198.51.100.8');
$r = $cf(['REMOTE_ADDR' => $edge, 'HTTP_CF_CONNECTING_IP' => '999.1.1.1'], true);
check('invalid header and no chain: the Cloudflare edge address, never junk', $r['ip'] === $edge);
// two proxies: Hostinger's own front (10.0.0.0/8 stands in) AFTER Cloudflare
$r = $cf(['REMOTE_ADDR' => '10.1.1.1', 'HTTP_X_FORWARDED_FOR' => '198.51.100.7, 172.69.0.5', 'HTTP_CF_CONNECTING_IP' => '198.51.100.7'], true, ['10.0.0.0/8']);
check('chain Cloudflare -> Hostinger proxy -> site: the visitor is found', $r['ip'] === '198.51.100.7' && $r['cf']);
$r = $cf(['REMOTE_ADDR' => '10.1.1.1', 'HTTP_X_FORWARDED_FOR' => '6.6.6.6, 198.51.100.7, 172.69.0.5'], true, ['10.0.0.0/8']);
check('chain without CF-Connecting-IP: walked from the right, a spoofed left entry is ignored', $r['ip'] === '198.51.100.7');
$r = $cf(['REMOTE_ADDR' => '10.1.1.1', 'HTTP_X_FORWARDED_FOR' => '9.9.9.9', 'HTTP_CF_CONNECTING_IP' => '1.2.3.4'], true, ['10.0.0.0/8']);
check('SPOOF through the Hostinger proxy with no Cloudflare in the chain: CF-Connecting-IP ignored', $r['ip'] === '9.9.9.9' && $r['cf'] === false);
$r = $cf(['REMOTE_ADDR' => '10.1.1.1', 'HTTP_X_FORWARDED_FOR' => '172.69.0.5, 5.5.5.5', 'HTTP_CF_CONNECTING_IP' => '1.1.1.1'], true, ['10.0.0.0/8']);
check('SPOOF: a fake Cloudflare address typed into X-Forwarded-For does not make a visitor trusted', $r['ip'] === '5.5.5.5' && $r['cf'] === false);
$r = $cf(['REMOTE_ADDR' => '10.1.1.1', 'HTTP_X_FORWARDED_FOR' => '203.0.113.50'], false, ['10.0.0.0/8']);
check('only the Hostinger proxy (TRUSTED_PROXIES), no Cloudflare: works as before', $r['ip'] === '203.0.113.50' && !$r['cf']);
$r = $cf(['REMOTE_ADDR' => '198.18.4.4', 'HTTP_CF_CONNECTING_IP' => '198.51.100.9'], true, [], ['198.18.0.0/15']);
check('CLOUDFLARE_EXTRA_RANGES adds a range', $r['ip'] === '198.51.100.9');
$r = $cf(['REMOTE_ADDR' => '198.18.4.4', 'HTTP_CF_CONNECTING_IP' => '198.51.100.9'], true);
check('... and without it that address is not trusted', $r['ip'] === '198.18.4.4');

echo "-- CIDR ranges --\n";
Config::set('cloudflare.extra_ranges', []);
$in = static fn(string $ip): bool => Cloudflare::isCloudflareIp($ip);
check('range edges: 104.16.0.0 and 104.23.255.255 inside, 104.15.255.255 and 104.28.0.0 outside', $in('104.16.0.0') && $in('104.23.255.255') && $in('104.24.0.1') && $in('104.27.255.255') && !$in('104.15.255.255') && !$in('104.28.0.0'));
check('range edges: 172.64.0.0 and 172.71.255.255 inside, 172.63.255.255 and 172.72.0.0 outside', $in('172.64.0.0') && $in('172.71.255.255') && !$in('172.63.255.255') && !$in('172.72.0.0'));
check('range edges: /15 162.158.0.0 - 162.159.255.255', $in('162.158.0.0') && $in('162.159.255.255') && !$in('162.160.0.0') && !$in('162.157.255.255'));
check('range edges: /22 131.0.72.0 - 131.0.75.255 and /17 198.41.128.0 - 198.41.255.255', $in('131.0.72.0') && $in('131.0.75.255') && !$in('131.0.76.0') && $in('198.41.200.1') && !$in('198.41.127.255'));
check('IPv6: /32, /29 (2a06:98c0 - 2a06:98c7) and outside', $in('2606:4700:1::1') && $in('2a06:98c7:ffff::1') && !$in('2a06:98c8::1') && $in('2c0f:f248::1') && !$in('2c0f:f249::1'));
check('private, loopback and ordinary addresses are not Cloudflare', !$in('127.0.0.1') && !$in('10.0.0.1') && !$in('8.8.8.8') && !$in('::1') && !$in('') && !$in('junk'));
check('the built-in list is dated and complete (15 IPv4 + 7 IPv6)', count(Cloudflare::IPV4) === 15 && count(Cloudflare::IPV6) === 7 && preg_match('/^\d{4}-\d{2}-\d{2}$/', Cloudflare::RANGES_DATE) === 1);

echo "-- HTTPS and HSTS --\n";
$hdrs = static function (array $server, bool $proxy, string $path = '/about', ?Response $resp = null) use ($cf): array {
    $r = $cf($server + ['REQUEST_URI' => $path], $proxy);
    $_SERVER = $server + ['REQUEST_URI' => $path, 'REQUEST_METHOD' => 'GET'];
    Request::capture();
    $resp ??= Response::html('x');
    $m = new ReflectionMethod($resp, 'securityHeaders');
    $m->setAccessible(true);
    return (array) $m->invoke($resp);
};
$h = $hdrs(['REMOTE_ADDR' => $edge, 'HTTP_CF_VISITOR' => '{"scheme":"https"}', 'HTTP_CF_CONNECTING_IP' => '198.51.100.7'], true);
check('behind Cloudflare, CF-Visitor https => HSTS is sent (Flexible origin hop included)', isset($h['Strict-Transport-Security']));
$h = $hdrs(['REMOTE_ADDR' => $edge, 'HTTP_CF_VISITOR' => '{"scheme":"http"}', 'HTTP_X_FORWARDED_PROTO' => 'http'], true);
check('a visitor on plain http gets NO HSTS (never set over http)', !isset($h['Strict-Transport-Security']));
$h = $hdrs(['REMOTE_ADDR' => $edge, 'HTTP_X_FORWARDED_PROTO' => 'https'], true);
check('X-Forwarded-Proto https from a trusted Cloudflare peer => HSTS', isset($h['Strict-Transport-Security']));
$h = $hdrs(['REMOTE_ADDR' => '203.0.113.9', 'HTTP_X_FORWARDED_PROTO' => 'https', 'HTTP_CF_VISITOR' => '{"scheme":"https"}'], true);
check('headers claiming https from a stranger are ignored: no HSTS', !isset($h['Strict-Transport-Security']));
$cfx = $cf(['REMOTE_ADDR' => '10.1.1.1', 'HTTP_X_FORWARDED_PROTO' => 'https'], false, ['10.0.0.0/8']);
check('Hostinger-style proxy (TRUSTED_PROXIES) with X-Forwarded-Proto https is recognised as HTTPS', $cfx['https'] === true);
$h = $hdrs(['REMOTE_ADDR' => '127.0.0.1', 'HTTPS' => 'on'], false);
check('plain HTTPS at the origin => HSTS 180 days with includeSubDomains, never preload', ($h['Strict-Transport-Security'] ?? '') === 'max-age=15552000; includeSubDomains');
$h = $hdrs(['REMOTE_ADDR' => '127.0.0.1'], false);
check('plain http at the origin => no HSTS', !isset($h['Strict-Transport-Security']));
check('existing protections are still sent (nosniff, framing, referrer, permissions, CSP)',
    ($h['X-Content-Type-Options'] ?? '') === 'nosniff' && ($h['X-Frame-Options'] ?? '') === 'SAMEORIGIN' && isset($h['Referrer-Policy'], $h['Permissions-Policy'])
    && str_contains((string) ($h['Content-Security-Policy'] ?? ''), "object-src 'none'") && str_contains((string) $h['Content-Security-Policy'], "frame-ancestors 'self'"));
check('the CSP does not mention Cloudflare unless a Turnstile widget is on the page', !str_contains((string) $h['Content-Security-Policy'], 'cloudflare'));

echo "-- cache headers on private pages --\n";
foreach (['/admin/login', '/admin/security', '/academy/account', '/academy/portal/courses', '/membership/me/abc', '/shop/cart', '/shop/checkout', '/donate', '/donate/online', '/pay/mobile-money/X1', '/api/newsletters', '/forms/x', '/track-application'] as $p) {
    $h = $hdrs(['REMOTE_ADDR' => '127.0.0.1'], false, $p);
    check("HTML at {$p} is private, no-store", ($h['Cache-Control'] ?? '') === 'private, no-store');
}
$h = $hdrs(['REMOTE_ADDR' => '127.0.0.1'], false, '/about');
check('a public page is private + revalidated (not shared-cacheable)', ($h['Cache-Control'] ?? '') === 'private, max-age=0, must-revalidate');
$h = $hdrs(['REMOTE_ADDR' => '127.0.0.1'], false, '/academy/login', Response::redirect('/x'));
check('a temporary redirect is never stored (private, no-store)', ($h['Cache-Control'] ?? '') === 'private, no-store');
check('JSON answers keep their own no-store', (Response::json([])->headers()['Cache-Control'] ?? '') === 'no-store');
check('adminlike prefix does not over-match (/administration is public, /admin/x private)', !Cloudflare::noStorePath('/administration') && Cloudflare::noStorePath('/admin/x') && Cloudflare::noStorePath('/admin') && !Cloudflare::noStorePath('/'));
foreach (Cloudflare::NO_STORE_PREFIXES as $pre) {
    $found = false;
    foreach (glob(dirname(__DIR__) . '/app/routes/*.php') ?: [] as $rf) {
        if (str_contains((string) file_get_contents($rf), "'" . $pre) || str_contains((string) file_get_contents($rf), "'/" . ltrim($pre, '/') . "/") || str_contains((string) file_get_contents($rf), "'prefix' => '" . trim($pre, '/') . "'")) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        check("the no-store prefix {$pre} belongs to a real route", false);
    }
}
$_SERVER = $savedServer;
Request::capture();

echo "-- cache-header judge --\n";
$j = static fn(string $cc, bool $sens, string $cfc = ''): string => CloudflareStatus::evaluate(['status' => 200, 'headers' => ['cache-control' => $cc] + ($cfc !== '' ? ['cf-cache-status' => $cfc] : [])], $sens)['status'];
check('private area + no-store = ok', $j('private, no-store', true) === 'ok');
check('private area + public = bad', $j('public, max-age=600', true) === 'bad' && $j('s-maxage=60', true) === 'bad');
check('private area with only "private, max-age=0" = bad (must say no-store)', $j('private, max-age=0, must-revalidate', true) === 'bad');
check('private area served from Cloudflare cache (HIT) = bad', $j('private, no-store', true, 'HIT') === 'bad');
check('public page: private/revalidate = ok, none = warn, HIT = warn', $j('private, max-age=0, must-revalidate', false) === 'ok' && $j('', false) === 'warn' && $j('private', false, 'HIT') === 'warn');
check('an unreachable page is "unknown", never a false ok', CloudflareStatus::evaluate(['error' => 'timeout'], true)['status'] === 'unknown');
CloudflareStatus::$transport = static fn(string $u): array => ['status' => 200, 'headers' => ['cache-control' => str_contains($u, '/shop') ? 'public, max-age=300' : 'private, no-store']];
$rows = CloudflareStatus::runCacheCheck('http://example.test', false);
CloudflareStatus::$transport = null;
check('the page check flags exactly the page that is cacheable', count(array_filter($rows, static fn($r) => $r['status'] === 'bad')) === 1 && array_values(array_filter($rows, static fn($r) => $r['status'] === 'bad'))[0]['path'] === '/shop');

echo "-- Turnstile (fake transport, no network) --\n";
$secret = 'test-secret-value-9X7Q-NEVER-SHOW';
$sitekey = '0x4AAAAAAATESTKEY';
$logFile = STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log';
$logBefore = is_file($logFile) ? (int) filesize($logFile) : 0;
$tsReq = static fn(string $token = 'tok-123'): Request => new Request(['REMOTE_ADDR' => '203.0.113.20', 'REQUEST_URI' => '/contact', 'REQUEST_METHOD' => 'POST'], [], $token === '' ? [] : [Turnstile::FIELD => $token], []);
putenv('TURNSTILE_SECRET_KEY');
Config::set('turnstile', ['site_key' => '', 'enabled' => true, 'fail' => 'open']);
check('off by default: no widget, every submission passes (nothing changes for visitors)', !Turnstile::enabled() && Turnstile::widget() === '' && Turnstile::verify($tsReq('')) === true && !Turnstile::rendered());
Config::set('turnstile', ['site_key' => $sitekey, 'enabled' => true, 'fail' => 'open']);
check('a site key without the secret is still off', !Turnstile::enabled());
putenv('TURNSTILE_SECRET_KEY=' . $secret);
check('both keys => on', Turnstile::enabled() === true);
Config::set('turnstile.enabled', false);
check('TURNSTILE_ENABLED=0 switches it off', !Turnstile::enabled() && Turnstile::verify($tsReq('')) === true);
Config::set('turnstile.enabled', true);
$sent = [];
Turnstile::$transport = static function (array $q) use (&$sent): array {
    $sent[] = $q;
    return ['status' => 200, 'body' => '{"success":true}'];
};
$w = Turnstile::widget('contact');
check('the widget carries the SITE key and the Cloudflare script, never the secret', str_contains($w, $sitekey) && str_contains($w, 'challenges.cloudflare.com/turnstile/v0/api.js') && !str_contains($w, $secret) && str_contains($w, 'data-action="contact"'));
check('rendering the widget flags the page for the CSP', Turnstile::rendered() === true);
$_SERVER = ['REMOTE_ADDR' => '127.0.0.1', 'REQUEST_URI' => '/contact', 'REQUEST_METHOD' => 'GET'];
Request::capture();
$m = new ReflectionMethod(Response::class, 'securityHeaders');
$m->setAccessible(true);
$csp = (string) ($m->invoke(Response::html('x'))['Content-Security-Policy'] ?? '');
check('that page (only) may load Cloudflare in script-src, frame-src and connect-src', substr_count($csp, 'https://challenges.cloudflare.com') === 3 && str_contains($csp, "default-src 'self'") && str_contains($csp, "object-src 'none'"));
Turnstile::resetRendered();
$csp = (string) ($m->invoke(Response::html('x'))['Content-Security-Policy'] ?? '');
check('a page without the widget keeps the strict policy', !str_contains($csp, 'cloudflare'));
$_SERVER = $savedServer;
Request::capture();
check('success: verify passes and Cloudflare receives the secret, token and visitor address', Turnstile::verify($tsReq()) === true && $sent[0]['fields']['secret'] === $secret && $sent[0]['fields']['response'] === 'tok-123' && $sent[0]['fields']['remoteip'] === '203.0.113.20' && $sent[0]['url'] === Turnstile::VERIFY_URL);
$n = count($sent);
check('no token: refused without calling Cloudflare', Turnstile::verify($tsReq('')) === false && count($sent) === $n);
check('an oversized token is refused without calling Cloudflare', Turnstile::verify($tsReq(str_repeat('a', 3000))) === false && count($sent) === $n);
$arrReq = new Request(['REMOTE_ADDR' => '203.0.113.20', 'REQUEST_URI' => '/contact', 'REQUEST_METHOD' => 'POST'], [], [Turnstile::FIELD => ['x']], []);
check('an array posted as the token is refused', Turnstile::verify($arrReq) === false);
Turnstile::$transport = static fn(array $q): array => ['status' => 200, 'body' => '{"success":false,"error-codes":["invalid-input-response"]}'];
check('failure: a bad token is refused (fail-open does NOT excuse a real "no")', Turnstile::verify($tsReq()) === false);
Turnstile::$transport = static fn(array $q): array => ['error' => 'timeout'];
check('timeout, fail-open (default): the form still works', Turnstile::verify($tsReq()) === true);
Config::set('turnstile.fail', 'closed');
check('timeout, fail-closed: the form is refused', Turnstile::verify($tsReq()) === false);
Turnstile::$transport = static fn(array $q): array => ['status' => 503, 'body' => ''];
check('HTTP 503, fail-closed: refused', Turnstile::verify($tsReq()) === false);
Turnstile::$transport = static function (array $q): array {
    throw new RuntimeException('boom');
};
check('an exception in the transport counts as unreachable (closed: refused)', Turnstile::verify($tsReq()) === false);
Config::set('turnstile.fail', 'open');
check('... and open: passes', Turnstile::verify($tsReq()) === true);
Turnstile::$transport = static fn(array $q): array => ['status' => 200, 'body' => 'not json'];
check('unreadable answer, fail-open: passes; fail-closed: refused', Turnstile::verify($tsReq()) === true && (static function () use ($tsReq) { Config::set('turnstile.fail', 'closed'); $r = Turnstile::verify($tsReq()); Config::set('turnstile.fail', 'open'); return $r === false; })());
Turnstile::$transport = static fn(array $q): array => ['status' => 200, 'body' => '{"success":false,"error-codes":["internal-error"]}'];
check("Cloudflare's own internal-error follows the fail setting (open: passes)", Turnstile::verify($tsReq()) === true);
Config::set('turnstile.fail', 'closed');
Turnstile::$transport = static fn(array $q): array => ['status' => 200, 'body' => '{"success":true}'];
$mw = static fn(Request $q): mixed => Middleware::run(['turnstile'], $q, static fn() => 'passed');
$out = $mw($tsReq());
check("the 'turnstile' middleware lets a verified post through", $out === 'passed');
$out = $mw($tsReq(''));
check('... and bounces a post without a valid check back with a message (a redirect, nothing stored)', $out instanceof Response && $out->status() === 302);
$getReq = new Request(['REMOTE_ADDR' => '203.0.113.20', 'REQUEST_URI' => '/contact', 'REQUEST_METHOD' => 'GET'], [], [], []);
check('a GET is never checked', Middleware::run(['turnstile'], $getReq, static fn() => 'passed') === 'passed');
Config::set('turnstile.fail', 'open');
$st = Turnstile::status();
check('status() for the admin card holds no key, not even a fragment', !str_contains(json_encode($st), $secret) && !str_contains(json_encode($st), $sitekey) && $st['secret_set'] === true);
$logAfter = is_file($logFile) ? (string) file_get_contents($logFile, false, null, $logBefore) : '';
check('the secret and the token never reached the log', !str_contains($logAfter, $secret) && !str_contains($logAfter, 'tok-123'));
check('the failures that fail open WERE logged (so the owner can see them)', str_contains($logAfter, 'Turnstile could not be reached'));
Turnstile::$transport = null;
putenv('TURNSTILE_SECRET_KEY');
Config::set('turnstile', $savedTs);
Turnstile::resetRendered();
foreach (['app/views/admin/auth/login.php', 'app/views/web/contact/index.php', 'app/views/web/academy/login.php', 'app/views/web/academy/register.php', 'app/views/web/membership/register.php', 'app/views/partials/newsletter-row.php', 'app/views/web/blog/_subscribe.php', 'app/views/web/donate/index.php', 'app/views/web/ambassadors/index.php', 'app/views/web/involvement/index.php', 'app/views/web/careers/_apply_form.php', 'app/views/web/legal/data-rights.php'] as $v) {
    check("the form in {$v} carries the widget call", str_contains((string) file_get_contents(dirname(__DIR__) . '/' . $v), 'turnstile_widget('));
}
$routes = (string) file_get_contents(dirname(__DIR__) . '/app/routes/web_core.php') . (string) file_get_contents(dirname(__DIR__) . '/app/routes/web_content.php') . (string) file_get_contents(dirname(__DIR__) . '/app/routes/web_academy.php') . (string) file_get_contents(dirname(__DIR__) . '/app/routes/web_membership.php') . (string) file_get_contents(dirname(__DIR__) . '/app/routes/admin_core.php');
check('the matching routes carry the middleware: 13 public forms after the honeypot + the admin sign-in', substr_count($routes, "'turnstile'") === 14 && substr_count($routes, "'honeypot', 'turnstile'") === 13);

echo "-- web server: protected paths and live headers --\n";
$base = 'http://localhost/' . basename(dirname(__DIR__));
$get = static function (string $path, array $headers = [], string $jar = '') use ($base): array {
    $ch = curl_init($base . $path);
    $o = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => false, CURLOPT_USERAGENT => 'Mozilla/5.0 Chrome/120 Safari/537.36', CURLOPT_HTTPHEADER => $headers];
    if ($jar !== '') {
        $o += [CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar];
    }
    curl_setopt_array($ch, $o);
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
$home = $get('/');
if ($home['code'] === 0) {
    check('http: the web server is running (start Apache to run the HTTP checks)', false);
} else {
    $bad = [];
    foreach (['/.env', '/.env.production', '/.env.example', '/.git/config', '/.git/HEAD', '/.github/', '/.htaccess', '/app/config/app.php', '/app/bootstrap.php', '/app/', '/storage/app.key', '/storage/logs/', '/storage/upload-ready/lumina-site.zip', '/database/schema.sql', '/database/', '/bin/selftest.php', '/bin/', '/docs/SECURITY.md', '/docs/CLOUDFLARE.md', '/docs/', '/scss/main.scss', '/tools/', '/vendor/', '/composer.json', '/backup.sql', '/dump.sql', '/db.sql.gz', '/site-backup.zip', '/index.php.bak', '/index.php.old', '/.env.bak', '/id_rsa.key', '/cert.pem', '/README.md', '/CONTRIBUTING.md', '/assets/.htaccess', '/uploads/.htaccess', '/APP/config/app.php', '/%2e%2e/app/bootstrap.php', '/assets/../app/bootstrap.php'] as $p) {
        $c = $get($p)['code'];
        if (!in_array($c, [403, 404], true)) {
            $bad[] = $p . '=' . $c;
        }
    }
    check('internal folders, secrets, source, backups and dumps are never served (403/404)', $bad === [], implode(' ', $bad));
    $body = $get('/.env')['body'];
    check('... and the refusal page shows no setting from .env', !preg_match('/DB_PASS|APP_KEY=|PASSWORD=/', $body));
    $login = $get('/admin/login');
    check('live: /admin/login answers with Cache-Control no-store', $login['code'] === 200 && preg_match('/^Cache-Control:\s*(private, )?no-store/mi', $login['head']) === 1);
    $acad = $get('/academy/login');
    check('live: /academy/login answers with Cache-Control private, no-store', preg_match('/^Cache-Control:\s*private, no-store/mi', $acad['head']) === 1);
    $don = $get('/donate');
    check('live: /donate answers with Cache-Control private, no-store', preg_match('/^Cache-Control:\s*private, no-store/mi', $don['head']) === 1);
    check('live: no Set-Cookie travels with a cacheable header (no "public" anywhere)', !preg_match('/^Cache-Control:.*\b(public|s-maxage)/mi', $login['head'] . $acad['head'] . $don['head'] . $home['head']));
    check('live: no Strict-Transport-Security over plain http', !str_contains(strtolower($login['head']), 'strict-transport-security'));
    $sp = $get('/admin/login', ['CF-Connecting-IP: 1.2.3.4', 'X-Forwarded-For: 1.2.3.4', 'CF-Visitor: {"scheme":"https"}', 'X-Forwarded-Proto: https']);
    check('live: spoofed Cloudflare headers from a direct connection do not switch HTTPS/HSTS on', $sp['code'] === 200 && !str_contains(strtolower($sp['head']), 'strict-transport-security'));
    check('live: CSP of the sign-in page has no Cloudflare allowance while Turnstile is off', !str_contains($login['head'], 'challenges.cloudflare.com'));
    check('live: the home page still sends nosniff, framing, referrer and permissions headers', preg_match('/X-Content-Type-Options:\s*nosniff/i', $home['head']) === 1 && preg_match('/X-Frame-Options:\s*SAMEORIGIN/i', $home['head']) === 1 && stripos($home['head'], 'Referrer-Policy') !== false && stripos($home['head'], 'Permissions-Policy') !== false);

    echo "-- Admin > Security: the Cloudflare card --\n";
    $snap = Database::all("SELECT * FROM settings WHERE setting_key LIKE 'security\\_cf\\_%'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-cf-%'");
    Database::execute('DELETE FROM rate_limits');
    $mk = static fn(string $email, string $role, string $pw): int => (int) User::create(['name' => 'ZZ CF ' . $role, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
    $pwA = 'Admin cf pass 71!';
    $pwE = 'Editor cf pass 73!';
    $mk('zz-cf-admin@example.test', 'admin', $pwA);
    $mk('zz-cf-editor@example.test', 'editor', $pwE);
    $wasTwoFactor = Settings::get('security_2fa', '0');
    Settings::setMany(['security_2fa' => '0']);
    $send = static function (string $method, string $path, array $form, string $jar) use ($base): array {
        $ch = curl_init($base . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_USERAGENT => 'Mozilla/5.0 Chrome/120 Safari/537.36']);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        }
        $raw = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
    };
    $tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
    $signIn = static function (string $email, string $pw) use ($send, $tok): string {
        $jar = (string) tempnam(sys_get_temp_dir(), 'cfj');
        $pg = $send('GET', '/admin/login', [], $jar);
        $send('POST', '/admin/login', ['_token' => $tok($pg['body']), 'email' => $email, 'password' => $pw], $jar);
        return $jar;
    };
    $jA = $signIn('zz-cf-admin@example.test', $pwA);
    $jE = $signIn('zz-cf-editor@example.test', $pwE);
    $jN = (string) tempnam(sys_get_temp_dir(), 'cfj');
    $page = $send('GET', '/admin/security', [], $jA);
    check('an administrator sees the Cloudflare card with the request facts, the checklist and the cache check', $page['code'] === 200 && str_contains($page['body'], 'Cloudflare protection') && str_contains($page['body'], 'Visitor address seen by the site') && str_contains($page['body'], 'Your set-up checklist') && str_contains($page['body'], 'Check the cache headers now'));
    check('a direct (non-Cloudflare) request is reported as "not in front yet" with the plain explanation', str_contains($page['body'], 'Not in front yet') && str_contains($page['body'], 'did not arrive through Cloudflare'));
    check('the card shows Turnstile as optional/off and no key text', str_contains($page['body'], 'Turnstile bot check: off') && !str_contains($page['body'], 'TURNSTILE_SECRET_KEY=') && !preg_match('/0x4AAAA/', $page['body']));
    check('an editor (no settings.manage) cannot open the page nor use the Cloudflare buttons', $send('GET', '/admin/security', [], $jE)['code'] === 403 && $send('POST', '/admin/security/cloudflare-check', ['_token' => 'x'], $jE)['code'] === 403 && $send('POST', '/admin/security/cloudflare-steps', ['_token' => 'x'], $jE)['code'] === 403);
    $anon = $send('GET', '/admin/security', [], $jN);
    check('a signed-out visitor is sent to the sign-in page, not shown the card', in_array($anon['code'], [302, 303], true) && str_contains($anon['head'], 'admin/login') && !str_contains($anon['body'], 'Cloudflare protection'));
    check('a signed-out POST to the card is refused', !in_array($send('POST', '/admin/security/cloudflare-steps', ['_token' => 'x', 'steps' => ['account']], $jN)['code'], [200], true));
    $r = $send('POST', '/admin/security/cloudflare-steps', ['_token' => $tok($page['body']), 'steps' => ['account', 'waf', 'bogus-key']], $jA);
    Settings::flush();
    $ticks = CloudflareStatus::ticks();
    check('ticks are saved as settings; unknown keys are ignored', in_array($r['code'], [302, 303], true) && $ticks['account'] === true && $ticks['waf'] === true && $ticks['hsts'] === false && !isset($ticks['bogus-key']));
    $page = $send('GET', '/admin/security', [], $jA);
    check('the ticks show on the page (2 of ' . count(CloudflareStatus::STEPS) . ')', str_contains($page['body'], '(2 of ' . count(CloudflareStatus::STEPS) . ')'));
    $r = $send('POST', '/admin/security/cloudflare-check', ['_token' => $tok($page['body'])], $jA);
    Settings::flush();
    $last = CloudflareStatus::lastCheck();
    check('the cache check runs from the admin page and keeps its result', in_array($r['code'], [302, 303], true) && is_array($last) && count($last['rows']) === count(CloudflareStatus::PROBES));
    $page = $send('GET', '/admin/security', [], $jA);
    $badRows = array_filter((array) ($last['rows'] ?? []), static fn($x) => $x['status'] === 'bad');
    check('the real private pages are judged correctly (no "bad" row on this site)', $badRows === [], json_encode(array_values($badRows)));
    check('the result table is on the page', str_contains($page['body'], 'Cache-Control sent') && str_contains($page['body'], '/admin/login'));
    $secretHit = false;
    foreach ([$page['body'], $home['body']] as $b) {
        if (preg_match('/(APP_KEY|DB_PASS|MAIL_PASSWORD|TURNSTILE_SECRET_KEY)\s*=\s*\S+/', $b)) {
            $secretHit = true;
        }
    }
    check('no secret appears on the page', !$secretHit);
    // clean up
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-cf-%'");
    Database::execute("DELETE FROM audit_log WHERE user_name LIKE '%ZZ CF%' OR user_name LIKE '%zz-cf-%'");
    Database::execute("DELETE FROM login_attempts WHERE email LIKE 'zz-cf-%'");
    Database::execute('DELETE FROM rate_limits');
    Database::execute("DELETE FROM settings WHERE setting_key LIKE 'security\\_cf\\_%'");
    foreach ($snap as $row) {
        Database::insert('settings', $row);
    }
    Settings::setMany(['security_2fa' => (string) $wasTwoFactor]);
    Settings::flush();
}

Config::set('cloudflare.proxy', $savedProxy);
Config::set('cloudflare.extra_ranges', $savedExtra);
Config::set('trusted_proxies', $savedTrusted);
$_SERVER = $savedServer;

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
