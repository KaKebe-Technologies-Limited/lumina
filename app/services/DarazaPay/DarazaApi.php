<?php
declare(strict_types=1);

namespace App\Services\DarazaPay;

use App\Core\Env;
use App\Core\Log;

/**
 * Small client for the DARAZA API (mobile-money collections and payouts in Uganda). Written against Daraza's published OpenAPI description (https://daraza.net/api/schema/).
 *
 *   POST /api/request_to_pay/   collect money from a phone: method 1 = MTN, 2 = Airtel (3 Visa, 4 MasterCard ...), amount in UGX (500 to 5,000,000), phone +256... or 07..., note <= 150 characters.
 *                               Airtel no longer needs an authorisation code. THE CALL IS HELD OPEN until the payer approves or refuses on their phone: it can take up to 5 MINUTES.
 *   POST /api/remit/            send money to a phone (same fields, no authorisation code).
 *   POST /api/get_request_status/   {request_id, request_type: "Request_to_Pay" | "Remittance"} -> details.is_successful
 *   GET  /api/app_wallet/balance/   the balance of the app wallet
 * While testing, the two transaction endpoints live under /api/sandbox/ (DARAZA_ENV=sandbox, the default). Authorization header: "Api-Key <key>".
 *
 * The key lives ONLY in the server .env (DARAZA_API_KEY, DARAZA_ENV=sandbox|live); it is never stored in the database, logged or shown.
 * Every answer is turned into: ok, state (success | error | auth | unknown), message, request_id. "unknown" means the call did not come back in time (the payer may still approve):
 * nothing is assumed, the payment is checked again with status().
 */
final class DarazaApi
{
    public const BASE = 'https://daraza.net';
    /** The call can be held open for up to five minutes by Daraza; a little more here. */
    public const TIMEOUT = 330;

    /** TEST HOOK: callable(string $method, string $url, array $headers, ?string $body): array{http:int,raw:string}. Used only by the self-tests. */
    public static $transport = null;

    public static function mode(): string
    {
        return strtolower(trim((string) Env::get('DARAZA_ENV', 'sandbox'))) === 'live' ? 'live' : 'sandbox';
    }

    public static function hasKey(): bool
    {
        return trim((string) Env::get('DARAZA_API_KEY', '')) !== '';
    }

    /** MTN prefixes 077 076 078 039, Airtel 070 074 075 020. @return int|null 1 = MTN, 2 = Airtel */
    public static function networkOf(string $phone): ?int
    {
        $local = self::local($phone);
        if ($local === null) {
            return null;
        }
        $p = substr($local, 0, 3);
        if (in_array($p, ['077', '078', '076', '039'], true)) {
            return 1;
        }
        return in_array($p, ['070', '075', '074', '020'], true) ? 2 : null;
    }

    /** "0772123456" from "+256772123456", "256772123456", "0772 123 456"; null when it is not a Ugandan mobile number. */
    public static function local(string $phone): ?string
    {
        $d = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        if (preg_match('/^(?:\+?256)(\d{9})$/', $d, $m) === 1) {
            return '0' . $m[1];
        }
        return preg_match('/^0\d{9}$/', $d) === 1 ? $d : null;
    }

    /** "+256772123456" or null. */
    public static function e164(string $phone): ?string
    {
        $l = self::local($phone);
        return $l === null ? null : '+256' . substr($l, 1);
    }

    /** @return array{ok:bool,state:string,message:string,request_id:string,http:int} */
    public static function requestToPay(int $method, int $amountUgx, string $phone, string $note): array
    {
        return self::transaction('request_to_pay', $method, $amountUgx, $phone, $note);
    }

    /** @return array{ok:bool,state:string,message:string,request_id:string,http:int} */
    public static function remit(int $method, int $amountUgx, string $phone, string $note): array
    {
        return self::transaction('remit', $method, $amountUgx, $phone, $note);
    }

    /** @return array{ok:bool,state:string,message:string,request_id:string,http:int} */
    private static function transaction(string $op, int $method, int $amountUgx, string $phone, string $note): array
    {
        $e164 = self::e164($phone);
        if (!self::hasKey()) {
            return self::fail('auth', 'Mobile-money payments are not connected yet.');
        }
        if ($e164 === null || !in_array($method, [1, 2], true) || $amountUgx < 500 || $amountUgx > 5000000) {
            return self::fail('error', 'The phone number, the network or the amount is not valid.');
        }
        $path = self::mode() === 'live' ? '/api/' . $op . '/' : '/api/sandbox/' . $op . '/';
        $r = self::call('POST', $path, ['method' => $method, 'amount' => $amountUgx, 'phone' => $e164, 'note' => mb_substr(trim($note), 0, 150)]);
        return self::interpret($r);
    }

    /** @return array{ok:bool,state:string,message:string,request_id:string,http:int,successful:?bool,amount:string} */
    public static function status(string $requestId, string $type): array
    {
        if (!self::hasKey()) {
            return self::fail('auth', 'Mobile-money payments are not connected yet.') + ['successful' => null, 'amount' => ''];
        }
        $r = self::call('POST', '/api/get_request_status/', ['request_id' => $requestId, 'request_type' => $type === 'Remittance' ? 'Remittance' : 'Request_to_Pay']);
        $d = is_array($r['json']['details'] ?? null) ? $r['json']['details'] : [];
        $ok = $r['http'] === 200 && ($r['json']['code'] ?? '') === 'Success';
        return ['ok' => $ok, 'state' => $r['http'] === 0 ? 'unknown' : ($ok ? 'success' : ($r['http'] === 401 || $r['http'] === 403 ? 'auth' : 'error')),
            'message' => $ok ? (string) ($d['reference'] ?? 'ok') : self::reason($r), 'request_id' => $requestId, 'http' => $r['http'],
            'successful' => $ok ? (bool) ($d['is_successful'] ?? false) : null, 'amount' => (string) ($d['amount'] ?? '')];
    }

    /** @return array{ok:bool,balance:string,error:string} the app wallet balance, e.g. "500000 UGX" */
    public static function balance(): array
    {
        if (!self::hasKey()) {
            return ['ok' => false, 'balance' => '', 'error' => 'No key: add DARAZA_API_KEY to the .env file.'];
        }
        $r = self::call('GET', '/api/app_wallet/balance/', null, 40);
        if ($r['http'] === 200 && ($r['json']['code'] ?? '') === 'Success') {
            return ['ok' => true, 'balance' => (string) ($r['json']['details']['balance'] ?? ''), 'error' => ''];
        }
        return ['ok' => false, 'balance' => '', 'error' => self::reason($r)];
    }

    /** @return array{http:int,json:?array<string,mixed>,raw:string} */
    private static function call(string $method, string $path, ?array $body, int $timeout = self::TIMEOUT): array
    {
        $url = self::BASE . $path;
        $headers = ['Authorization: Api-Key ' . trim((string) Env::get('DARAZA_API_KEY', '')), 'Content-Type: application/json', 'Accept: application/json'];
        $payload = $body === null ? null : (string) json_encode($body);
        @set_time_limit($timeout + 30);
        if (self::$transport !== null) {
            $t = (self::$transport)($method, $url, $headers, $payload);
            return ['http' => (int) $t['http'], 'json' => self::decode((string) $t['raw']), 'raw' => (string) $t['raw']];
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_MAXFILESIZE => 1048576,
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $raw = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($raw === false) {
            Log::warning('Daraza: no answer (' . curl_errno($ch) . ') for ' . $path);
        }
        curl_close($ch);
        return ['http' => $raw === false ? 0 : $http, 'json' => $raw === false ? null : self::decode((string) $raw), 'raw' => $raw === false ? '' : (string) $raw];
    }

    /** @return ?array<string,mixed> */
    private static function decode(string $raw): ?array
    {
        $j = json_decode($raw, true);
        return is_array($j) ? $j : null;
    }

    /** @param array{http:int,json:?array<string,mixed>,raw:string} $r @return array{ok:bool,state:string,message:string,request_id:string,http:int} */
    private static function interpret(array $r): array
    {
        $j = $r['json'] ?? [];
        $rid = (string) ($j['request_id'] ?? ($j['response_details']['request_id'] ?? ''));
        if ($r['http'] === 0) {
            return ['ok' => false, 'state' => 'unknown', 'message' => 'Daraza did not answer in time. The payer may still approve: check the payment again in a minute.', 'request_id' => '', 'http' => 0];
        }
        if (in_array($r['http'], [200, 201], true) && ($j['code'] ?? '') === 'Success') {
            return ['ok' => true, 'state' => 'success', 'message' => (string) ($j['message'] ?? 'Success'), 'request_id' => $rid, 'http' => $r['http']];
        }
        if (in_array($r['http'], [401, 403, 404], true)) {
            return ['ok' => false, 'state' => 'auth', 'message' => 'Daraza refused the key: check DARAZA_API_KEY and that the app is active.', 'request_id' => $rid, 'http' => $r['http']];
        }
        return ['ok' => false, 'state' => 'error', 'message' => self::reason($r), 'request_id' => $rid, 'http' => $r['http']];
    }

    /** A short human reason from an error answer. @param array{http:int,json:?array<string,mixed>,raw:string} $r */
    private static function reason(array $r): string
    {
        if ($r['http'] === 0) {
            return 'Daraza did not answer.';
        }
        $d = $r['json']['details'] ?? ($r['json']['message'] ?? null);
        if (is_array($d)) {
            $first = reset($d);
            $d = is_array($first) ? (string) reset($first) : (string) $first;
        }
        $d = trim((string) $d);
        return mb_substr($d !== '' ? $d : 'Daraza answered with an error (' . $r['http'] . ').', 0, 200);
    }

    /** @return array{ok:bool,state:string,message:string,request_id:string,http:int} */
    private static function fail(string $state, string $message): array
    {
        return ['ok' => false, 'state' => $state, 'message' => $message, 'request_id' => '', 'http' => 0];
    }
}
