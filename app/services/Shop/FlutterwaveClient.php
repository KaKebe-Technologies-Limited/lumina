<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Config;
use App\Core\Env;
use App\Core\Log;

/**
 * Hardened HTTP client for the FLUTTERWAVE v3 API (hosted checkout, "Standard"). Written against the documented API; tested ONLY against the local
 * mock bin/dev/mock-flutterwave.php - it has not been run against the real service (that needs the owner's merchant account: try it in TEST mode first).
 *
 * Endpoints used (base https://api.flutterwave.com):
 *   POST /v3/payments                                      create a hosted payment: tx_ref, amount, currency, redirect_url, payment_options, meta, customer{email,phonenumber,name},
 *                                                          customizations{title,description,logo}  ->  {"status":"success","data":{"link":"https://checkout.flutterwave.com/v3/hosted/pay/..."}}
 *   GET  /v3/transactions/verify_by_reference?tx_ref=...   ->  {"status":"success","data":{"id":123,"tx_ref":"..","amount":35000,"currency":"UGX","status":"successful",...}}
 *   GET  /v3/transactions/{id}/verify                      same shape, by Flutterwave transaction id
 *   header  Authorization: Bearer <secret key>
 *
 * SAFETY: the host is FIXED (api.flutterwave.com, https only, TLS verified, no redirects followed, 8 s connect / 20 s total timeout, 1 MB response cap);
 * nothing a visitor typed ever becomes part of a URL except an url-encoded tx_ref or digits. The secret key is read from SecretStore at the moment of
 * the call, goes only into the Authorization header and is never logged, returned or put in an error message. Errors are mapped to short codes:
 *   no_key | invalid_key (401/403) | rejected (other 4xx) | unavailable (network, timeout, 5xx) | bad_response (not JSON / unexpected shape)
 * DEV ONLY: when APP_ENV=local the base URL may be overridden with FLW_DEV_BASE_URL=http://127.0.0.1:PORT (127.0.0.1, localhost or [::1] only).
 * It is ignored in every other environment, so a production server can never be pointed anywhere else.
 */
final class FlutterwaveClient
{
    public const HOST = 'api.flutterwave.com';
    private const MAX_BODY = 1048576;

    /** Where requests go: https://api.flutterwave.com, or (APP_ENV=local only) the validated FLW_DEV_BASE_URL. */
    public static function baseUrl(): string
    {
        $dev = self::devBase();
        return $dev ?? 'https://' . self::HOST;
    }

    /** The dev override, validated, or null. @return ?string like "http://127.0.0.1:8099" */
    public static function devBase(): ?string
    {
        if (Config::get('app.env') !== 'local') {
            return null;
        }
        $raw = trim((string) Env::get('FLW_DEV_BASE_URL', ''));
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

    /** True when a request would go to the local mock instead of Flutterwave. */
    public static function usingDevBase(): bool
    {
        return self::devBase() !== null;
    }

    public static function keyMode(string $secret): string
    {
        if (str_contains($secret, 'FLWSECK_TEST')) {
            return 'test';
        }
        return str_starts_with($secret, 'FLWSECK-') ? 'live' : 'unknown';
    }

    // ------------------------------------------------------------------------------------------------ API calls

    /**
     * Create the hosted payment page.
     * @param array{tx_ref:string,amount_minor:int,currency:string,redirect_url:string,customer:array{name:string,email:string,phone:string},title?:string,description?:string,logo?:string,options?:list<string>,meta?:array<string,string>} $in
     * @return array{ok:bool,link?:string,error?:string,http?:int}
     */
    public static function createPayment(array $in): array
    {
        $secret = SecretStore::get('flw_secret_key');
        if ($secret === null) {
            return ['ok' => false, 'error' => 'no_key'];
        }
        $body = [
            'tx_ref'          => (string) $in['tx_ref'],
            'amount'          => Money::gateway((int) $in['amount_minor'], (string) $in['currency']),
            'currency'        => strtoupper((string) $in['currency']),
            'redirect_url'    => (string) $in['redirect_url'],
            'payment_options' => implode(',', $in['options'] ?? PaymentMethods::flwOptions()),
            'meta'            => (object) ($in['meta'] ?? []),
            'customer'        => [
                'email'       => (string) $in['customer']['email'],
                'phonenumber' => (string) $in['customer']['phone'],
                'name'        => (string) $in['customer']['name'],
            ],
            'customizations'  => array_filter([
                'title'       => mb_substr((string) ($in['title'] ?? ''), 0, 60),
                'description' => mb_substr((string) ($in['description'] ?? ''), 0, 120),
                'logo'        => (string) ($in['logo'] ?? ''),
            ], static fn(string $v): bool => $v !== ''),
        ];
        $r = self::request('POST', '/v3/payments', $secret, $body);
        if ($r['error'] !== null) {
            return ['ok' => false, 'error' => $r['error'], 'http' => $r['http']];
        }
        $link = (string) ($r['json']['data']['link'] ?? '');
        if (($r['json']['status'] ?? '') !== 'success' || !self::safeLink($link)) {
            return ['ok' => false, 'error' => $r['http'] >= 400 ? 'rejected' : 'bad_response', 'http' => $r['http']];
        }
        return ['ok' => true, 'link' => $link, 'http' => $r['http']];
    }

    /** @return array{ok:bool,status:string,tx_ref:string,id:?string,amount_minor:?int,currency:string,error?:string} see normalise() */
    public static function verifyByReference(string $txRef): array
    {
        $secret = SecretStore::get('flw_secret_key');
        if ($secret === null) {
            return self::none('no_key');
        }
        if (preg_match('/^[A-Za-z0-9._-]{3,120}$/', $txRef) !== 1) {
            return self::none('rejected');
        }
        return self::verifyCall('/v3/transactions/verify_by_reference?tx_ref=' . rawurlencode($txRef), $secret);
    }

    /** @return array{ok:bool,status:string,tx_ref:string,id:?string,amount_minor:?int,currency:string,error?:string} */
    public static function verifyById(string $id): array
    {
        $secret = SecretStore::get('flw_secret_key');
        if ($secret === null) {
            return self::none('no_key');
        }
        if (preg_match('/^[0-9]{1,20}$/', $id) !== 1) {
            return self::none('rejected');
        }
        return self::verifyCall('/v3/transactions/' . $id . '/verify', $secret);
    }

    /**
     * "Test connection": an authenticated, side-effect-free call (verify a reference that does not exist). A valid key gets a normal JSON answer
     * ("no transaction found"); a wrong key gets 401. Never reveals the key.
     * @return array{ok:bool,state:string,message:string,mode:string,using_mock:bool}  state: ok | no_key | invalid_key | unavailable | unexpected
     */
    public static function testConnection(): array
    {
        $secret = SecretStore::get('flw_secret_key');
        $mode = $secret === null ? 'unknown' : self::keyMode($secret);
        $mock = self::usingDevBase();
        if ($secret === null) {
            return ['ok' => false, 'state' => 'no_key', 'message' => 'No secret key is set yet.', 'mode' => $mode, 'using_mock' => $mock];
        }
        $r = self::request('GET', '/v3/transactions/verify_by_reference?tx_ref=' . rawurlencode('lumina-connection-test'), $secret, null);
        PaymentService::recordEvent('connection_test', null, null, $r['error'] ?? 'ok');
        $msg = [
            'invalid_key' => 'Flutterwave rejected the secret key. Copy it again from the Flutterwave dashboard (Settings > API keys) and make sure it matches the mode (test or live).',
            'unavailable' => 'Flutterwave could not be reached from this server. Check the internet connection of the server and try again.',
            'bad_response' => 'Flutterwave answered in an unexpected way. Try again in a minute.',
        ];
        if ($r['error'] === null || $r['error'] === 'rejected') {
            return ['ok' => true, 'state' => 'ok', 'message' => 'Flutterwave accepted the secret key.', 'mode' => $mode, 'using_mock' => $mock];
        }
        return ['ok' => false, 'state' => $r['error'] === 'invalid_key' ? 'invalid_key' : ($r['error'] === 'unavailable' ? 'unavailable' : 'unexpected'),
            'message' => $msg[$r['error']] ?? 'The test did not work.', 'mode' => $mode, 'using_mock' => $mock];
    }

    // ------------------------------------------------------------------------------------------------ webhook helpers

    /** Does the `verif-hash` header equal the secret hash the owner set? Constant-time; false when no hash is configured. */
    public static function webhookAuthentic(?string $headerValue): bool
    {
        $known = SecretStore::get('flw_secret_hash');
        return $known !== null && SecretStore::equals($known, trim((string) $headerValue));
    }

    /**
     * Pull the one thing we use out of a webhook body: the tx_ref (format-checked). Amounts, status and everything else in the body are IGNORED - the
     * payment is verified with the server-to-server call.
     * @return ?string tx_ref or null
     */
    public static function webhookTxRef(mixed $payload): ?string
    {
        if (!is_array($payload)) {
            return null;
        }
        $ref = $payload['data']['tx_ref'] ?? ($payload['txRef'] ?? null);
        return is_string($ref) && preg_match('/^[A-Za-z0-9._-]{3,120}$/', $ref) === 1 ? $ref : null;
    }

    /** Flutterwave API JSON -> the fields settleGateway() needs. @return array{ok:bool,status:string,tx_ref:string,id:?string,amount_minor:?int,currency:string} */
    public static function normalise(mixed $json): array
    {
        $data = is_array($json) ? ($json['data'] ?? null) : null;
        if (!is_array($json) || ($json['status'] ?? '') !== 'success' || !is_array($data)) {
            return ['ok' => false, 'status' => '', 'tx_ref' => '', 'id' => null, 'amount_minor' => null, 'currency' => ''];
        }
        $id = isset($data['id']) && (is_int($data['id']) || (is_string($data['id']) && ctype_digit($data['id']))) ? (string) $data['id'] : null;
        return [
            'ok'           => true,
            'status'       => strtolower((string) ($data['status'] ?? '')),
            'tx_ref'       => is_string($data['tx_ref'] ?? null) ? (string) $data['tx_ref'] : '',
            'id'           => $id,
            'amount_minor' => Money::fromGateway($data['amount'] ?? null),
            'currency'     => strtoupper((string) ($data['currency'] ?? '')),
        ];
    }

    // ------------------------------------------------------------------------------------------------ transport

    /** @return array{ok:bool,status:string,tx_ref:string,id:?string,amount_minor:?int,currency:string,error?:string} */
    private static function verifyCall(string $path, string $secret): array
    {
        $r = self::request('GET', $path, $secret, null);
        if ($r['error'] !== null && $r['error'] !== 'rejected') {
            return self::none($r['error']);
        }
        $n = self::normalise($r['json']);
        if (!$n['ok']) {
            return self::none($r['error'] ?? 'bad_response') ;
        }
        return $n;
    }

    /** @return array{ok:bool,status:string,tx_ref:string,id:?string,amount_minor:?int,currency:string,error:string} */
    private static function none(string $error): array
    {
        return ['ok' => false, 'status' => '', 'tx_ref' => '', 'id' => null, 'amount_minor' => null, 'currency' => '', 'error' => $error];
    }

    /** A redirect target we accept: https on flutterwave.com (or, in dev mode, the same origin as the mock). */
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
        return ($p['scheme'] ?? '') === 'https' && preg_match('/(^|\.)flutterwave\.com$/i', (string) $p['host']) === 1;
    }

    /**
     * One request. @param ?array<string,mixed> $body JSON body for POST
     * @return array{http:int,json:?array<string,mixed>,error:?string}  error: null on a 2xx answer with JSON; rejected for other JSON 4xx; invalid_key for 401/403; unavailable; bad_response
     */
    private static function request(string $method, string $path, string $secret, ?array $body): array
    {
        $url = self::baseUrl() . $path;
        $devMode = self::usingDevBase();
        $headers = ['Authorization: Bearer ' . $secret, 'Accept: application/json', 'User-Agent: LuminaShop/1.0'];
        $payload = null;
        if ($body !== null) {
            $payload = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Type: application/json';
        }
        $http = 0;
        $raw = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return ['http' => 0, 'json' => null, 'error' => 'unavailable'];
            }
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS      => 0,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS      => $devMode ? (CURLPROTO_HTTP | CURLPROTO_HTTPS) : CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_NOSIGNAL       => true,
                CURLOPT_WRITEFUNCTION  => static function ($c, string $chunk) use (&$raw): int {
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
                Log::warning('Flutterwave request failed', ['path' => strtok($path, '?'), 'curl_errno' => $errno]);
                return ['http' => $http, 'json' => null, 'error' => 'unavailable'];
            }
        } else {
            $ctx = stream_context_create([
                'http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'content' => $payload ?? '', 'timeout' => 20, 'ignore_errors' => true, 'follow_location' => 0, 'max_redirects' => 0],
                'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true, 'allow_self_signed' => false],
            ]);
            $fh = @fopen($url, 'rb', false, $ctx);
            if ($fh === false) {
                Log::warning('Flutterwave request failed (stream)', ['path' => strtok($path, '?')]);
                return ['http' => 0, 'json' => null, 'error' => 'unavailable'];
            }
            $meta = stream_get_meta_data($fh);
            foreach ((array) ($meta['wrapper_data'] ?? []) as $line) {
                if (is_string($line) && preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m) === 1) {
                    $http = (int) $m[1];
                }
            }
            $raw = (string) stream_get_contents($fh, self::MAX_BODY + 1);
            fclose($fh);
            if ($http === 0 || strlen($raw) > self::MAX_BODY) {
                return ['http' => $http, 'json' => null, 'error' => 'unavailable'];
            }
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
