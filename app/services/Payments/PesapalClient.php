<?php
declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Config;
use App\Core\Env;
use App\Core\Log;
use App\Core\Settings;

/**
 * Hardened HTTP client for the PESAPAL API v3 (hosted checkout). Ported from the ObiFunds / ChamaFunds Pesapal scripts (includes/pesapal_functions.php) so the same
 * merchant account and the same proven calls are used here; the differences are on purpose: the host is FIXED, TLS is verified, no redirect is followed, answers
 * are size-capped, the keys never reach a log or an error message, and the redirect link is checked before a visitor is sent to it.
 *
 * Endpoints used (base https://pay.pesapal.com/v3, or https://cybqa.pesapal.com/pesapalv3 in sandbox):
 *   POST /api/Auth/RequestToken                    {consumer_key, consumer_secret}  ->  {token, expiryDate}                       (bearer token, about 5 minutes)
 *   GET  /api/URLSetup/GetIpnList                  ->  [{url, ipn_id, ...}]
 *   POST /api/URLSetup/RegisterIPN                 {url, ipn_notification_type:"GET"}  ->  {ipn_id}
 *   POST /api/Transactions/SubmitOrderRequest      {id (our reference), currency, amount, description, callback_url, notification_id, billing_address{}}  ->  {order_tracking_id, redirect_url}
 *   GET  /api/Transactions/GetTransactionStatus?orderTrackingId=...  ->  {payment_status_description, status_code, amount, currency, merchant_reference, ...}
 *
 * Configuration (.env only, never the database or the admin): PESAPAL_ENV (live | sandbox), PESAPAL_CONSUMER_KEY, PESAPAL_CONSUMER_SECRET, optional PESAPAL_IPN_ID
 * (empty = register the Lumina notification URL once and remember its id), optional PESAPAL_PUBLIC_URL (an https address Pesapal can reach, for example an ngrok
 * tunnel, used for the return and notification addresses while the site runs on localhost).
 * DEV ONLY: when APP_ENV=local, PESAPAL_DEV_BASE_URL=http://127.0.0.1:PORT sends every call to the local mock (bin/dev/mock-pesapal.php). Ignored everywhere else.
 * Errors are short codes: no_key | invalid_key | rejected | unavailable | bad_response | no_ipn.
 */
final class PesapalClient
{
    private const LIVE_BASE = 'https://pay.pesapal.com/v3';
    private const SANDBOX_BASE = 'https://cybqa.pesapal.com/pesapalv3';
    private const MAX_BODY = 1048576;

    // ------------------------------------------------------------------------------------------------ configuration

    public static function mode(): string
    {
        return strtolower(trim((string) Env::get('PESAPAL_ENV', 'live'))) === 'sandbox' ? 'sandbox' : 'live';
    }

    public static function configured(): bool
    {
        return self::key() !== '' && self::secret() !== '';
    }

    private static function key(): string
    {
        return trim((string) Env::get('PESAPAL_CONSUMER_KEY', ''));
    }

    private static function secret(): string
    {
        return trim((string) Env::get('PESAPAL_CONSUMER_SECRET', ''));
    }

    /** The dev override, validated, or null. @return ?string like "http://127.0.0.1:8099" */
    public static function devBase(): ?string
    {
        if (Config::get('app.env') !== 'local') {
            return null;
        }
        $raw = trim((string) Env::get('PESAPAL_DEV_BASE_URL', ''));
        if ($raw === '') {
            return null;
        }
        $p = parse_url($raw);
        if ($p === false || !in_array($p['scheme'] ?? '', ['http', 'https'], true) || !in_array(strtolower((string) ($p['host'] ?? '')), ['127.0.0.1', 'localhost', '[::1]', '::1'], true)
            || isset($p['user']) || isset($p['pass']) || (($p['path'] ?? '') !== '' && ($p['path'] ?? '') !== '/') || isset($p['query'])) {
            return null;
        }
        return $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . (int) $p['port'] : '');
    }

    public static function usingDevBase(): bool
    {
        return self::devBase() !== null;
    }

    public static function baseUrl(): string
    {
        $dev = self::devBase();
        if ($dev !== null) {
            return $dev;
        }
        return self::mode() === 'sandbox' ? self::SANDBOX_BASE : self::LIVE_BASE;
    }

    /**
     * The public https address Pesapal's servers and the paying browser use to come back to this site: PESAPAL_PUBLIC_URL when set (a tunnel while developing),
     * otherwise the site's own address. @return string without a trailing slash
     */
    public static function publicBase(): string
    {
        $raw = rtrim(trim((string) Env::get('PESAPAL_PUBLIC_URL', '')), '/');
        if ($raw !== '' && preg_match('~^https://[A-Za-z0-9.-]+(:\d{2,5})?(/[A-Za-z0-9._~/-]*)?$~', $raw) === 1) {
            return $raw;
        }
        return rtrim(abs_url(''), '/');
    }

    // ------------------------------------------------------------------------------------------------ API calls

    /** A bearer token (cached for this request and, for a few minutes, across requests). @return array{ok:bool,token?:string,error?:string} */
    public static function token(): array
    {
        static $cache = null;
        if (is_array($cache) && $cache['until'] > time()) {
            return ['ok' => true, 'token' => $cache['token']];
        }
        if (!self::configured()) {
            return ['ok' => false, 'error' => 'no_key'];
        }
        $r = self::request('POST', '/api/Auth/RequestToken', ['consumer_key' => self::key(), 'consumer_secret' => self::secret()], null);
        if ($r['error'] !== null) {
            return ['ok' => false, 'error' => $r['error']];
        }
        $tok = $r['json']['token'] ?? null;
        if (!is_string($tok) || $tok === '' || !empty($r['json']['error']['code'] ?? null)) {
            return ['ok' => false, 'error' => !empty($r['json']['error']) ? 'invalid_key' : 'bad_response'];
        }
        $cache = ['token' => $tok, 'until' => time() + 240];
        return ['ok' => true, 'token' => $tok];
    }

    /** "Test connection": ask Pesapal for a token. Never reveals the keys. @return array{ok:bool,state:string,message:string,mode:string,using_mock:bool} */
    public static function testConnection(): array
    {
        $mock = self::usingDevBase();
        if (!self::configured()) {
            return ['ok' => false, 'state' => 'no_key', 'message' => 'PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET are not set in the .env file.', 'mode' => self::mode(), 'using_mock' => $mock];
        }
        $t = self::token();
        $msg = [
            'invalid_key' => 'Pesapal rejected the consumer key and secret. Check that they belong to the ' . self::mode() . ' environment.',
            'unavailable' => 'Pesapal could not be reached from this server. Check the internet connection and try again.',
            'bad_response' => 'Pesapal answered in an unexpected way. Try again in a minute.',
            'rejected' => 'Pesapal refused the request.',
        ];
        return ['ok' => $t['ok'], 'state' => $t['ok'] ? 'ok' : (string) $t['error'], 'message' => $t['ok'] ? 'Pesapal accepted the keys (' . self::mode() . ').' : ($msg[$t['error']] ?? 'The test did not work.'), 'mode' => self::mode(), 'using_mock' => $mock];
    }

    /**
     * The id of this site's notification (IPN) address on the Pesapal account: PESAPAL_IPN_ID from the .env when set, else the id remembered for the current address
     * and mode, else it is looked up in the account's list, else registered (once). @return array{ok:bool,ipn_id?:string,error?:string}
     */
    public static function ensureIpnId(string $ipnUrl): array
    {
        $fixed = trim((string) Env::get('PESAPAL_IPN_ID', ''));
        if ($fixed !== '') {
            return ['ok' => true, 'ipn_id' => $fixed];
        }
        $scope = self::mode() . '|' . (self::usingDevBase() ? 'mock|' : '') . $ipnUrl;
        $cachedId = (string) Settings::get('pesapal_ipn_id', '');
        if ($cachedId !== '' && hash_equals((string) Settings::get('pesapal_ipn_scope', ''), $scope) && self::validId($cachedId)) {
            return ['ok' => true, 'ipn_id' => $cachedId];
        }
        $t = self::token();
        if (!$t['ok']) {
            return ['ok' => false, 'error' => (string) $t['error']];
        }
        $list = self::request('GET', '/api/URLSetup/GetIpnList', null, $t['token']);
        if ($list['error'] === null && is_array($list['json'])) {
            foreach ($list['json'] as $row) {
                if (is_array($row) && ($row['url'] ?? '') === $ipnUrl && is_string($row['ipn_id'] ?? null) && self::validId($row['ipn_id']) && strtoupper((string) ($row['ipn_status_decription'] ?? 'ACTIVE')) !== 'INACTIVE') {
                    self::rememberIpn($row['ipn_id'], $scope);
                    return ['ok' => true, 'ipn_id' => $row['ipn_id']];
                }
            }
        }
        $reg = self::request('POST', '/api/URLSetup/RegisterIPN', ['url' => $ipnUrl, 'ipn_notification_type' => 'GET'], $t['token']);
        $id = $reg['json']['ipn_id'] ?? null;
        if ($reg['error'] === null && is_string($id) && self::validId($id)) {
            self::rememberIpn($id, $scope);
            return ['ok' => true, 'ipn_id' => $id];
        }
        Log::warning('Pesapal RegisterIPN failed', ['http' => $reg['http'], 'error' => $reg['error']]);
        return ['ok' => false, 'error' => $reg['error'] ?? 'no_ipn'];
    }

    /**
     * Create an order and get the hosted-checkout address.
     * @param array{id:string,amount:float,currency:string,description:string,callback_url:string,notification_id:string,billing:array{name:string,email:string,phone:string}} $in
     * @return array{ok:bool,tracking_id?:string,link?:string,error?:string,http?:int}
     */
    public static function submitOrder(array $in): array
    {
        $t = self::token();
        if (!$t['ok']) {
            return ['ok' => false, 'error' => (string) $t['error']];
        }
        $name = trim($in['billing']['name']);
        $parts = $name !== '' ? explode(' ', $name, 2) : [];
        $billing = array_filter([
            'email_address' => $in['billing']['email'] !== '' ? $in['billing']['email'] : null,
            'phone_number'  => $in['billing']['phone'] !== '' ? $in['billing']['phone'] : null,
            'country_code'  => 'UG',
            'first_name'    => $parts[0] ?? 'Learner',
            'last_name'     => isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : 'LAF Academy',
        ], static fn($v): bool => $v !== null && $v !== '');
        $r = self::request('POST', '/api/Transactions/SubmitOrderRequest', [
            'id'              => $in['id'],
            'currency'        => strtoupper($in['currency']),
            'amount'          => round($in['amount'], 2),
            'description'     => mb_substr($in['description'], 0, 100),
            'callback_url'    => $in['callback_url'],
            'notification_id' => $in['notification_id'],
            'billing_address' => $billing,
        ], $t['token']);
        if ($r['error'] !== null) {
            return ['ok' => false, 'error' => $r['error'], 'http' => $r['http']];
        }
        $tracking = $r['json']['order_tracking_id'] ?? null;
        $link = (string) ($r['json']['redirect_url'] ?? '');
        if (!is_string($tracking) || !self::validId($tracking) || !empty($r['json']['error']['code'] ?? null) || !self::safeLink($link)) {
            return ['ok' => false, 'error' => !empty($r['json']['error']) ? 'rejected' : 'bad_response', 'http' => $r['http']];
        }
        return ['ok' => true, 'tracking_id' => $tracking, 'link' => $link, 'http' => $r['http']];
    }

    /**
     * Ask Pesapal for the real status of an order (the only thing a payment is ever believed on).
     * status: completed | failed | invalid (not paid / abandoned) | pending
     * @return array{ok:bool,status:string,amount:?float,currency:string,merchant_ref:string,method:string,confirmation:string,description:string,error?:string}
     */
    public static function status(string $trackingId): array
    {
        $none = static fn(string $err): array => ['ok' => false, 'status' => '', 'amount' => null, 'currency' => '', 'merchant_ref' => '', 'method' => '', 'confirmation' => '', 'description' => '', 'error' => $err];
        if (!self::validId($trackingId)) {
            return $none('rejected');
        }
        $t = self::token();
        if (!$t['ok']) {
            return $none((string) $t['error']);
        }
        $r = self::request('GET', '/api/Transactions/GetTransactionStatus?orderTrackingId=' . rawurlencode($trackingId), null, $t['token']);
        if ($r['error'] !== null && $r['error'] !== 'rejected') {
            return $none($r['error']);
        }
        $b = $r['json'];
        if (!is_array($b) || $r['http'] !== 200 || (!array_key_exists('status_code', $b) && !array_key_exists('payment_status_description', $b))) {
            return $none($r['error'] ?? 'bad_response');
        }
        // status_code: 0 INVALID, 1 COMPLETED, 2 FAILED, 3 REVERSED
        $code = isset($b['status_code']) && is_numeric($b['status_code']) ? (int) $b['status_code'] : -1;
        $desc = strtolower(trim((string) ($b['payment_status_description'] ?? '')));
        $status = 'pending';
        if ($code === 1 || $desc === 'completed') {
            $status = 'completed';
        } elseif ($code === 2 || $code === 3 || $desc === 'failed' || $desc === 'reversed') {
            $status = 'failed';
        } elseif ($code === 0 || $desc === 'invalid' || $desc === '') {
            $status = 'invalid';
        }
        return [
            'ok'           => true,
            'status'       => $status,
            'amount'       => isset($b['amount']) && is_numeric($b['amount']) ? (float) $b['amount'] : null,
            'currency'     => strtoupper(is_string($b['currency'] ?? null) ? $b['currency'] : ''),
            'merchant_ref' => is_string($b['merchant_reference'] ?? null) ? $b['merchant_reference'] : '',
            'method'       => mb_substr(is_string($b['payment_method'] ?? null) ? trim($b['payment_method']) : '', 0, 60),
            'confirmation' => mb_substr(is_string($b['confirmation_code'] ?? null) ? trim($b['confirmation_code']) : '', 0, 80),
            'description'  => mb_substr(is_string($b['description'] ?? null) ? trim($b['description']) : '', 0, 200),
        ];
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** Pesapal ids (order tracking id, IPN id) are GUIDs; anything else is refused before it can become part of a URL. */
    public static function validId(string $id): bool
    {
        return preg_match('/^[A-Za-z0-9-]{8,64}$/', $id) === 1;
    }

    private static function rememberIpn(string $id, string $scope): void
    {
        try {
            Settings::setMany(['pesapal_ipn_id' => $id, 'pesapal_ipn_scope' => $scope]);
        } catch (\Throwable) {
            // not remembered: it is looked up in the account list next time
        }
    }

    /** A redirect target we accept: https on pesapal.com (or, in dev mode, the same origin as the mock). */
    private static function safeLink(string $link): bool
    {
        $p = parse_url($link);
        if ($p === false || empty($p['host']) || isset($p['user']) || isset($p['pass'])) {
            return false;
        }
        $dev = self::devBase();
        if ($dev !== null) {
            $d = parse_url($dev);
            return ($p['scheme'] ?? '') === $d['scheme'] && strtolower($p['host']) === strtolower((string) $d['host']) && ($p['port'] ?? null) === ($d['port'] ?? null);
        }
        return ($p['scheme'] ?? '') === 'https' && preg_match('/(^|\.)pesapal\.com$/i', (string) $p['host']) === 1;
    }

    /**
     * One request. @param ?array<string,mixed> $body JSON body for POST
     * @return array{http:int,json:?array<mixed>,error:?string}  error: null on a 2xx answer with JSON; rejected for other JSON 4xx; invalid_key for 401/403; unavailable; bad_response
     */
    private static function request(string $method, string $path, ?array $body, ?string $token): array
    {
        $url = self::baseUrl() . $path;
        $devMode = self::usingDevBase();
        $headers = ['Accept: application/json', 'Content-Type: application/json', 'User-Agent: LuminaAcademy/1.0'];
        if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        $payload = $body !== null ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
        $raw = '';
        $http = 0;
        if (!function_exists('curl_init') || ($ch = curl_init($url)) === false) {
            return ['http' => 0, 'json' => null, 'error' => 'unavailable'];
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST   => $method,
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_MAXREDIRS       => 0,
            CURLOPT_CONNECTTIMEOUT  => 8,
            CURLOPT_TIMEOUT         => 25,
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_SSL_VERIFYHOST  => 2,
            CURLOPT_PROTOCOLS       => $devMode ? (CURLPROTO_HTTP | CURLPROTO_HTTPS) : CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_NOSIGNAL        => true,
            CURLOPT_WRITEFUNCTION   => static function ($c, string $chunk) use (&$raw): int {
                $raw .= $chunk;
                return strlen($raw) > self::MAX_BODY ? 0 : strlen($chunk);      // abort an oversized answer
            },
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $ok = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($ok === false || $errno !== 0 || $http === 0) {
            Log::warning('Pesapal request failed', ['path' => strtok($path, '?'), 'curl_errno' => $errno]);
            return ['http' => $http, 'json' => null, 'error' => 'unavailable'];
        }
        if ($http === 401 || $http === 403) {
            return ['http' => $http, 'json' => null, 'error' => 'invalid_key'];
        }
        if ($http >= 500) {
            return ['http' => $http, 'json' => null, 'error' => 'unavailable'];
        }
        $json = json_decode($raw, true, 16, JSON_BIGINT_AS_STRING);
        if (!is_array($json)) {
            return ['http' => $http, 'json' => null, 'error' => 'bad_response'];
        }
        return ['http' => $http, 'json' => $json, 'error' => $http >= 400 ? 'rejected' : null];
    }
}
