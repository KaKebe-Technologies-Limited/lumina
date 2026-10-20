<?php
/**
 * THROWAWAY LOCAL MOCK OF THE FLUTTERWAVE v3 API (development and tests only). It lets you try the whole online-payment flow of the shop WITHOUT a merchant
 * account. It is NOT Flutterwave: it only imitates the few documented endpoints the shop uses, so a green test here proves our code, not the real service.
 * Live payments must first be tried in Flutterwave TEST mode with Flutterwave's test cards / numbers.
 *
 *   /c/xampp/php/php.exe -S 127.0.0.1:8099 bin/dev/mock-flutterwave.php         (leave it running in its own terminal; Ctrl+C stops it)
 *
 * and in .env (honoured ONLY when APP_ENV=local):   FLW_DEV_BASE_URL=http://127.0.0.1:8099   FLW_SECRET_KEY=FLWSECK_TEST-mock   FLW_SECRET_HASH=mock-hash
 * It refuses to answer requests that do not come from 127.0.0.1 / ::1.
 *
 * Imitated endpoints (Authorization: Bearer <MOCK secret> required, 401 otherwise):
 *   POST /v3/payments                                 -> {"status":"success","message":"Hosted Link","data":{"link":"http://127.0.0.1:PORT/hosted/pay/<tx_ref>"}}
 *   GET  /v3/transactions/verify_by_reference?tx_ref= -> {"status":"success","data":{id,tx_ref,flw_ref,amount,currency,charged_amount,status}} | 400 "No transaction was found..."
 *   GET  /v3/transactions/{id}/verify                 -> same by id
 *   GET  /hosted/pay/<tx_ref>                         -> a tiny page with Pay / Fail / Cancel links that send the browser back to redirect_url (as Flutterwave does)
 * Control endpoints (any caller on localhost; no auth):
 *   POST /__mock/reset                                forget everything
 *   POST /__mock/config   {secret, fail: "" | "500" | "bad_json" | "slow"}   which secret is valid / break the next verify calls
 *   POST /__mock/outcome  {tx_ref, status: successful|failed|pending|cancelled, amount?, currency?, tx_ref_in_response?}   decide what verify will say
 *   POST /__mock/webhook  {url, hash, tx_ref, event?}   send a webhook to the shop with the header verif-hash (returns the shop's HTTP status)
 *   GET  /__mock/state                                what the mock remembers (for tests)
 * State lives in one JSON file in the system temp folder (flock-protected), so the built-in server's separate requests share it.
 */
declare(strict_types=1);

$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Local use only.');
}

$stateFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lumina-mock-flutterwave-' . (int) ($_SERVER['SERVER_PORT'] ?? 0) . '.json';

/** @return array<string,mixed> */
function load_state(string $file): array
{
    $s = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    return is_array($s) ? $s + ['secret' => 'FLWSECK_TEST-mock-secret', 'fail' => '', 'next_id' => 700000, 'tx' => []] : ['secret' => 'FLWSECK_TEST-mock-secret', 'fail' => '', 'next_id' => 700000, 'tx' => []];
}

function with_state(string $file, callable $fn): mixed
{
    $fh = fopen($file . '.lock', 'c');
    flock($fh, LOCK_EX);
    $state = load_state($file);
    $result = $fn($state);
    file_put_contents($file, json_encode($state));
    flock($fh, LOCK_UN);
    fclose($fh);
    return $result;
}

function out(int $code, mixed $body): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo is_string($body) ? $body : json_encode($body);
    exit;
}

function bearer(): string
{
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    return preg_match('/^Bearer\s+(.+)$/i', $h, $m) === 1 ? trim($m[1]) : '';
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$raw = (string) file_get_contents('php://input');
$in = json_decode($raw, true);
$in = is_array($in) ? $in : $_POST;

// ------------------------------------------------------------------------------------------------ control endpoints
if (str_starts_with($path, '/__mock/')) {
    switch ($path) {
        case '/__mock/reset':
            @unlink($stateFile);
            out(200, ['ok' => true]);
        case '/__mock/config':
            with_state($stateFile, static function (array &$s) use ($in): void {
                if (isset($in['secret'])) {
                    $s['secret'] = (string) $in['secret'];
                }
                if (isset($in['fail'])) {
                    $s['fail'] = (string) $in['fail'];
                }
            });
            out(200, ['ok' => true]);
        case '/__mock/outcome':
            $ref = (string) ($in['tx_ref'] ?? '');
            with_state($stateFile, static function (array &$s) use ($in, $ref): void {
                $t = $s['tx'][$ref] ?? ['id' => $s['next_id']++, 'tx_ref' => $ref, 'amount' => 0, 'currency' => 'UGX', 'status' => 'pending', 'redirect_url' => ''];
                foreach (['status', 'amount', 'currency', 'tx_ref_in_response'] as $k) {
                    if (array_key_exists($k, $in)) {
                        $t[$k] = $in[$k];
                    }
                }
                $s['tx'][$ref] = $t;
            });
            out(200, ['ok' => true]);
        case '/__mock/state':
            out(200, with_state($stateFile, static fn(array &$s): array => $s));
        case '/__mock/webhook':
            $url = (string) ($in['url'] ?? '');
            $p = parse_url($url);
            if ($p === false || !in_array($p['host'] ?? '', ['127.0.0.1', 'localhost'], true)) {
                out(400, ['ok' => false, 'error' => 'webhook target must be localhost']);
            }
            $body = json_encode(['event' => (string) ($in['event'] ?? 'charge.completed'), 'data' => ['id' => 1, 'tx_ref' => (string) ($in['tx_ref'] ?? ''), 'amount' => 1, 'currency' => 'UGX', 'status' => 'successful']]);
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'verif-hash: ' . (string) ($in['hash'] ?? '')]]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            out(200, ['ok' => true, 'status' => $code]);
    }
    out(404, ['ok' => false]);
}

// ------------------------------------------------------------------------------------------------ the hosted page (browser demo)
if (preg_match('#^/hosted/pay/([A-Za-z0-9._-]{3,120})$#', $path, $m) === 1) {
    $ref = $m[1];
    $act = (string) ($_GET['do'] ?? '');
    $t = with_state($stateFile, static function (array &$s) use ($ref, $act): ?array {
        $t = $s['tx'][$ref] ?? null;
        if ($t !== null && in_array($act, ['pay', 'fail', 'cancel'], true)) {
            $t['status'] = $act === 'pay' ? 'successful' : ($act === 'fail' ? 'failed' : 'pending');
            $s['tx'][$ref] = $t;
        }
        return $t;
    });
    if ($t === null) {
        http_response_code(404);
        exit('Unknown payment.');
    }
    if ($act !== '') {
        $q = http_build_query(['status' => $act === 'pay' ? 'successful' : ($act === 'fail' ? 'failed' : 'cancelled'), 'tx_ref' => $ref, 'transaction_id' => (string) $t['id']]);
        header('Location: ' . $t['redirect_url'] . (str_contains((string) $t['redirect_url'], '?') ? '&' : '?') . $q);
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><meta charset="utf-8"><title>Mock Flutterwave</title><body style="font-family:sans-serif;max-width:30rem;margin:3rem auto"><h1>Mock payment page</h1><p>This is a LOCAL MOCK, not Flutterwave.</p>'
        . '<p>Reference: ' . $h($ref) . '<br>Amount: ' . $h((string) $t['amount']) . ' ' . $h((string) $t['currency']) . '</p>'
        . '<p><a href="?do=pay">Pay (successful)</a> | <a href="?do=fail">Fail</a> | <a href="?do=cancel">Cancel</a></p></body>';
    exit;
}

// ------------------------------------------------------------------------------------------------ the API
$state = with_state($stateFile, static fn(array &$s): array => $s);
if (str_starts_with($path, '/v3/')) {
    if (bearer() !== $state['secret']) {
        out(401, ['status' => 'error', 'message' => 'Invalid authorization key', 'data' => null]);
    }
    if ($state['fail'] === '500' && str_contains($path, '/transactions/')) {
        out(500, ['status' => 'error', 'message' => 'Internal error']);
    }
    if ($state['fail'] === 'bad_json' && str_contains($path, '/transactions/')) {
        http_response_code(200);
        exit('<html>not json</html>');
    }
    if ($state['fail'] === 'slow' && str_contains($path, '/transactions/')) {
        sleep(30);
    }
}

if ($method === 'POST' && $path === '/v3/payments') {
    $ref = (string) ($in['tx_ref'] ?? '');
    if (preg_match('/^[A-Za-z0-9._-]{3,120}$/', $ref) !== 1 || !isset($in['amount'], $in['currency'], $in['redirect_url']) || !is_array($in['customer'] ?? null) || empty($in['customer']['email'])) {
        out(400, ['status' => 'error', 'message' => 'Invalid request', 'data' => null]);
    }
    $port = (int) ($_SERVER['SERVER_PORT'] ?? 0);
    with_state($stateFile, static function (array &$s) use ($in, $ref): void {
        $s['tx'][$ref] = ($s['tx'][$ref] ?? []) + ['id' => $s['next_id']++, 'tx_ref' => $ref, 'status' => 'pending'];
        $s['tx'][$ref]['amount'] = $in['amount'];
        $s['tx'][$ref]['currency'] = (string) $in['currency'];
        $s['tx'][$ref]['redirect_url'] = (string) $in['redirect_url'];
        $s['tx'][$ref]['payment_options'] = (string) ($in['payment_options'] ?? '');
        $s['tx'][$ref]['meta'] = $in['meta'] ?? null;
    });
    out(200, ['status' => 'success', 'message' => 'Hosted Link', 'data' => ['link' => 'http://127.0.0.1:' . $port . '/hosted/pay/' . $ref]]);
}

$tx = null;
if ($method === 'GET' && $path === '/v3/transactions/verify_by_reference') {
    $tx = $state['tx'][(string) ($_GET['tx_ref'] ?? '')] ?? null;
} elseif ($method === 'GET' && preg_match('#^/v3/transactions/(\d+)/verify$#', $path, $m) === 1) {
    foreach ($state['tx'] as $t) {
        if ((string) $t['id'] === $m[1]) {
            $tx = $t;
        }
    }
} else {
    out(404, ['status' => 'error', 'message' => 'Not found', 'data' => null]);
}
if ($tx === null) {
    out(400, ['status' => 'error', 'message' => 'No transaction was found for this reference', 'data' => null]);
}
out(200, ['status' => 'success', 'message' => 'Transaction fetched successfully', 'data' => [
    'id' => (int) $tx['id'], 'tx_ref' => (string) ($tx['tx_ref_in_response'] ?? $tx['tx_ref']), 'flw_ref' => 'MOCK/' . $tx['id'], 'amount' => $tx['amount'], 'currency' => (string) $tx['currency'],
    'charged_amount' => $tx['amount'], 'status' => (string) $tx['status'], 'payment_type' => 'card',
]]);
