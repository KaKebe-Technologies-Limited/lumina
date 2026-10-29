<?php
declare(strict_types=1);

namespace App\Services\Pesapal;

use App\Core\Database;
use App\Core\Env;
use App\Core\Log;

/**
 * Small, hardened client for the PESAPAL API 3.0 (hosted checkout: card, mobile money, bank). Written against the documented API.
 *
 *   POST /api/Auth/RequestToken                      consumer_key + consumer_secret -> bearer token (cached ~4 minutes in this process)
 *   POST /api/URLSetup/RegisterIPN                   url + ipn_notification_type -> ipn_id (registered once per environment + URL, kept in pesapal_ipns)
 *   POST /api/Transactions/SubmitOrderRequest        merchant reference, amount, currency, callback_url, notification_id, billing_address -> order_tracking_id + redirect_url
 *   GET  /api/Transactions/GetTransactionStatus?orderTrackingId=...   -> status_code 0 invalid | 1 completed | 2 failed | 3 reversed, amount, currency, ...
 *
 * KEYS live ONLY in the server .env (PESAPAL_CONSUMER_KEY, PESAPAL_CONSUMER_SECRET, PESAPAL_ENV=live|sandbox); they are never stored in the database,
 * logged or shown. The host is fixed per environment (https only, TLS verified, no redirects, short timeouts, 1 MB cap). Errors are short codes:
 * no_keys | invalid_keys | rejected | unavailable | bad_response.
 */
final class PesapalClient
{
    public const LIVE = 'https://pay.pesapal.com/v3';
    public const SANDBOX = 'https://cybqa.pesapal.com/pesapalv3';
    private const MAX_BODY = 1048576;

    /** TEST HOOK: callable(string $method, string $url, array $headers, ?string $body): array{http:int,raw:string}. Used only by the self-tests. */
    public static $transport = null;

    private static ?array $token = null;

    public static function mode(): string
    {
        return strtolower(trim((string) Env::get('PESAPAL_ENV', 'sandbox'))) === 'live' ? 'live' : 'sandbox';
    }

    public static function baseUrl(): string
    {
        return self::mode() === 'live' ? self::LIVE : self::SANDBOX;
    }

    public static function hasKeys(): bool
    {
        return trim((string) Env::get('PESAPAL_CONSUMER_KEY', '')) !== '' && trim((string) Env::get('PESAPAL_CONSUMER_SECRET', '')) !== '';
    }

    public static function resetToken(): void
    {
        self::$token = null;
    }

    /** @return array{ok:bool,token?:string,error?:string} */
    public static function token(): array
    {
        if (!self::hasKeys()) {
            return ['ok' => false, 'error' => 'no_keys'];
        }
        $mode = self::mode();
        if (self::$token !== null && self::$token['mode'] === $mode && self::$token['exp'] > time()) {
            return ['ok' => true, 'token' => self::$token['value']];
        }
        $r = self::call('POST', '/api/Auth/RequestToken', null, [
            'consumer_key'    => trim((string) Env::get('PESAPAL_CONSUMER_KEY', '')),
            'consumer_secret' => trim((string) Env::get('PESAPAL_CONSUMER_SECRET', '')),
        ]);
        if ($r['error'] !== null && !($r['error'] === 'rejected' && is_array($r['json']))) {
            return ['ok' => false, 'error' => $r['error']];
        }
        $tok = (string) ($r['json']['token'] ?? '');
        if ($tok === '') {
            // Pesapal answers a wrong key / secret with an "error" object
            return ['ok' => false, 'error' => isset($r['json']['error']) ? 'invalid_keys' : 'bad_response'];
        }
        self::$token = ['mode' => $mode, 'value' => $tok, 'exp' => time() + 240];
        return ['ok' => true, 'token' => $tok];
    }

    /** The IPN id for our notification URL in this environment (registered once, then remembered). @return array{ok:bool,ipn_id?:string,error?:string} */
    public static function ipnId(string $url): array
    {
        $mode = self::mode();
        $row = Database::one('SELECT ipn_id FROM pesapal_ipns WHERE env = ? AND url = ? LIMIT 1', [$mode, $url]);
        if ($row !== null && (string) $row['ipn_id'] !== '') {
            return ['ok' => true, 'ipn_id' => (string) $row['ipn_id']];
        }
        $t = self::token();
        if (!$t['ok']) {
            return ['ok' => false, 'error' => (string) $t['error']];
        }
        $r = self::call('POST', '/api/URLSetup/RegisterIPN', $t['token'], ['url' => $url, 'ipn_notification_type' => 'GET']);
        if ($r['error'] !== null) {
            return ['ok' => false, 'error' => $r['error']];
        }
        $id = (string) ($r['json']['ipn_id'] ?? '');
        if ($id === '' || preg_match('/^[A-Za-z0-9-]{8,80}$/', $id) !== 1) {
            return ['ok' => false, 'error' => 'bad_response'];
        }
        Database::execute('INSERT INTO pesapal_ipns (env, url, ipn_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE ipn_id = VALUES(ipn_id)', [$mode, $url, $id]);
        return ['ok' => true, 'ipn_id' => $id];
    }

    /**
     * @param array{reference:string,amount:string,currency:string,description:string,callback_url:string,ipn_url:string,email:string,phone:string,first_name:string,last_name:string} $in
     * @return array{ok:bool,tracking_id?:string,redirect_url?:string,error?:string}
     */
    public static function submitOrder(array $in): array
    {
        $ipn = self::ipnId($in['ipn_url']);
        if (!$ipn['ok']) {
            return ['ok' => false, 'error' => (string) $ipn['error']];
        }
        $t = self::token();
        if (!$t['ok']) {
            return ['ok' => false, 'error' => (string) $t['error']];
        }
        $r = self::call('POST', '/api/Transactions/SubmitOrderRequest', $t['token'], [
            'id'              => $in['reference'],
            'currency'        => strtoupper($in['currency']),
            'amount'          => (float) $in['amount'],
            'description'     => mb_substr($in['description'], 0, 100),
            'callback_url'    => $in['callback_url'],
            'notification_id' => $ipn['ipn_id'],
            'billing_address' => array_filter([
                'email_address' => $in['email'],
                'phone_number'  => $in['phone'],
                'first_name'    => $in['first_name'],
                'last_name'     => $in['last_name'],
            ], static fn(string $v): bool => $v !== ''),
        ]);
        if ($r['error'] !== null && !($r['error'] === 'rejected' && is_array($r['json']))) {
            return ['ok' => false, 'error' => $r['error']];
        }
        $tracking = (string) ($r['json']['order_tracking_id'] ?? '');
        $redirect = (string) ($r['json']['redirect_url'] ?? '');
        if ($tracking === '' || !self::safeLink($redirect)) {
            return ['ok' => false, 'error' => isset($r['json']['error']) ? 'rejected' : 'bad_response'];
        }
        return ['ok' => true, 'tracking_id' => $tracking, 'redirect_url' => $redirect];
    }

    /**
     * The authoritative state of a payment (server to server; nothing in a browser URL is trusted).
     * @return array{ok:bool,code:int,status:string,amount:?float,currency:string,reference:string,method:string,confirmation:string,error?:string}
     */
    public static function status(string $trackingId): array
    {
        $none = ['ok' => false, 'code' => 0, 'status' => '', 'amount' => null, 'currency' => '', 'reference' => '', 'method' => '', 'confirmation' => ''];
        if (preg_match('/^[A-Za-z0-9-]{8,80}$/', $trackingId) !== 1) {
            return $none + ['error' => 'rejected'];
        }
        $t = self::token();
        if (!$t['ok']) {
            return $none + ['error' => (string) $t['error']];
        }
        $r = self::call('GET', '/api/Transactions/GetTransactionStatus?orderTrackingId=' . rawurlencode($trackingId), $t['token'], null);
        if ($r['error'] !== null) {
            return $none + ['error' => $r['error']];
        }
        $j = (array) $r['json'];
        if (!isset($j['status_code']) || !is_numeric($j['status_code'])) {
            return $none + ['error' => 'bad_response'];
        }
        return [
            'ok'           => true,
            'code'         => (int) $j['status_code'],
            'status'       => mb_substr((string) ($j['payment_status_description'] ?? ''), 0, 40),
            'amount'       => isset($j['amount']) && is_numeric($j['amount']) ? (float) $j['amount'] : null,
            'currency'     => strtoupper(mb_substr((string) ($j['currency'] ?? ''), 0, 3)),
            'reference'    => mb_substr((string) ($j['merchant_reference'] ?? ''), 0, 60),
            'method'       => mb_substr((string) ($j['payment_method'] ?? ''), 0, 60),
            'confirmation' => mb_substr((string) ($j['confirmation_code'] ?? ''), 0, 60),
        ];
    }

    /** "Test connection": asks for a token only. @return array{ok:bool,message:string,mode:string} */
    public static function testConnection(): array
    {
        $mode = self::mode();
        self::resetToken();
        $t = self::token();
        $msg = [
            'no_keys'      => 'The Pesapal keys are not in the server .env file yet (PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET).',
            'invalid_keys' => 'Pesapal refused the keys. Copy them again from your Pesapal dashboard and check they match the mode (sandbox keys only work with PESAPAL_ENV=sandbox, live keys with PESAPAL_ENV=live).',
            'unavailable'  => 'Pesapal could not be reached from this server. Try again in a minute.',
        ];
        if ($t['ok']) {
            return ['ok' => true, 'message' => 'Pesapal accepted the keys (' . $mode . ' mode).', 'mode' => $mode];
        }
        return ['ok' => false, 'message' => $msg[(string) $t['error']] ?? 'Pesapal answered in an unexpected way.', 'mode' => $mode];
    }

    public static function safeLink(string $url): bool
    {
        $p = parse_url($url);
        return $p !== false && ($p['scheme'] ?? '') === 'https' && preg_match('/(^|\.)pesapal\.com$/i', (string) ($p['host'] ?? '')) === 1 && !isset($p['user']);
    }

    // ------------------------------------------------------------------------------------------------ transport

    /** @return array{http:int,json:?array<string,mixed>,error:?string} */
    private static function call(string $method, string $path, ?string $token, ?array $body): array
    {
        $url = self::baseUrl() . $path;
        $headers = ['Accept: application/json', 'Content-Type: application/json', 'User-Agent: LuminaPay/1.0'];
        if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        $payload = $body === null ? null : json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (is_callable(self::$transport)) {
            $res = (self::$transport)($method, $url, $headers, $payload);
            $http = (int) ($res['http'] ?? 0);
            $raw = (string) ($res['raw'] ?? '');
        } else {
            [$http, $raw] = self::curl($method, $url, $headers, $payload, $path);
        }
        if ($http === 0 || $http >= 500) {
            return ['http' => $http, 'json' => null, 'error' => 'unavailable'];
        }
        if ($http === 401 || $http === 403) {
            return ['http' => $http, 'json' => null, 'error' => 'invalid_keys'];
        }
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            return ['http' => $http, 'json' => null, 'error' => $http >= 400 ? 'rejected' : 'bad_response'];
        }
        return ['http' => $http, 'json' => $json, 'error' => $http >= 400 ? 'rejected' : null];
    }

    /** @return array{0:int,1:string} */
    private static function curl(string $method, string $url, array $headers, ?string $payload, string $path): array
    {
        if (!function_exists('curl_init') || ($ch = curl_init($url)) === false) {
            return [0, ''];
        }
        $raw = '';
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST   => $method,
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_CONNECTTIMEOUT  => 8,
            CURLOPT_TIMEOUT         => 25,
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_SSL_VERIFYHOST  => 2,
            CURLOPT_PROTOCOLS       => CURLPROTO_HTTPS,
            CURLOPT_NOSIGNAL        => true,
            CURLOPT_WRITEFUNCTION   => static function ($c, string $chunk) use (&$raw): int {
                $raw .= $chunk;
                return strlen($raw) > self::MAX_BODY ? 0 : strlen($chunk);
            },
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $ok = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($ok === false || $errno !== 0) {
            Log::warning('Pesapal request failed', ['path' => strtok($path, '?'), 'curl_errno' => $errno]);
            return [0, ''];
        }
        return [$http, $raw];
    }
}
