<?php
/**
 * THROWAWAY LOCAL MOCK OF THE PESAPAL API v3 (development and tests only). It lets you try the whole certificate-payment flow of the LAF Academy WITHOUT spending
 * real money. It is NOT Pesapal: it only imitates the few documented endpoints the site uses (same paths, same JSON field names), so a green test here proves
 * our code and our reading of the contract, not the real service. The keys and the live status of the real account are checked separately (docs/PESAPAL.md).
 *
 *   /c/xampp/php/php.exe -S 127.0.0.1:8098 bin/dev/mock-pesapal.php         (leave it running in its own terminal; Ctrl+C stops it)
 *
 * and in .env (honoured ONLY when APP_ENV=local):   PESAPAL_DEV_BASE_URL=http://127.0.0.1:8098   PESAPAL_CONSUMER_KEY=mock-key   PESAPAL_CONSUMER_SECRET=mock-secret
 * It refuses to answer requests that do not come from 127.0.0.1 / ::1.
 *
 * Imitated endpoints (Authorization: Bearer <token from RequestToken> required, 401 otherwise):
 *   POST /api/Auth/RequestToken                      {consumer_key, consumer_secret} -> {token, expiryDate, error:null, status:"200"} (wrong keys: token null + error, as Pesapal does)
 *   GET  /api/URLSetup/GetIpnList                    -> [{url, ipn_id, ...}]
 *   POST /api/URLSetup/RegisterIPN                   {url, ipn_notification_type} -> {url, ipn_id, status:"200"}
 *   POST /api/Transactions/SubmitOrderRequest        {id, currency, amount, description, callback_url, notification_id, billing_address} -> {order_tracking_id, merchant_reference, redirect_url, status:"200"}
 *   GET  /api/Transactions/GetTransactionStatus?orderTrackingId=   -> {payment_status_description, status_code, amount, currency, merchant_reference, payment_method, confirmation_code, ...}
 *   GET  /pay/<tracking>                             a tiny page with Pay / Fail links that send the browser back to callback_url with OrderTrackingId + OrderMerchantReference (as Pesapal does)
 * Control endpoints (any caller on localhost; no auth):
 *   POST /__mock/reset
 *   POST /__mock/config   {key, secret, fail: "" | "500" | "bad_json"}   which keys are valid / break the next status calls
 *   POST /__mock/outcome  {tracking, status: completed|failed|pending|invalid, amount?, currency?, merchant_reference?}   decide what GetTransactionStatus will say
 *   POST /__mock/ipn      {tracking}   call the registered IPN url of that order (GET, as Pesapal does) and return the site's HTTP status + body
 *   GET  /__mock/state
 */
declare(strict_types=1);

$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Local use only.');
}

$stateFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lumina-mock-pesapal-' . (int) ($_SERVER['SERVER_PORT'] ?? 0) . '.json';
$defaults = ['key' => 'mock-key', 'secret' => 'mock-secret', 'fail' => '', 'tokens' => [], 'ipns' => [], 'orders' => []];

/** @return array<string,mixed> */
function load_state(string $file, array $defaults): array
{
    $s = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    return is_array($s) ? $s + $defaults : $defaults;
}

function with_state(string $file, array $defaults, callable $fn): mixed
{
    $fh = fopen($file . '.lock', 'c');
    flock($fh, LOCK_EX);
    $state = load_state($file, $defaults);
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

function guid(): string
{
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}

function bearer(): string
{
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    return preg_match('/^Bearer\s+(.+)$/i', $h, $m) === 1 ? trim($m[1]) : '';
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$in = json_decode((string) file_get_contents('php://input'), true);
$in = is_array($in) ? $in : [];
$port = (int) ($_SERVER['SERVER_PORT'] ?? 8098);

// ------------------------------------------------------------------------------------------------ control
if (str_starts_with($path, '/__mock/')) {
    switch ($path) {
        case '/__mock/reset':
            with_state($stateFile, $defaults, static function (array &$s) use ($defaults): void {
                $s = $defaults;
            });
            out(200, ['ok' => true]);
        case '/__mock/config':
            with_state($stateFile, $defaults, static function (array &$s) use ($in): void {
                foreach (['key', 'secret', 'fail'] as $k) {
                    if (isset($in[$k]) && is_string($in[$k])) {
                        $s[$k] = $in[$k];
                    }
                }
            });
            out(200, ['ok' => true]);
        case '/__mock/outcome':
            $ok = with_state($stateFile, $defaults, static function (array &$s) use ($in): bool {
                $t = (string) ($in['tracking'] ?? '');
                if (!isset($s['orders'][$t])) {
                    return false;
                }
                foreach (['status', 'amount', 'currency', 'merchant_reference'] as $k) {
                    if (array_key_exists($k, $in)) {
                        $s['orders'][$t][$k === 'status' ? 'status' : 'over_' . $k] = $in[$k];
                    }
                }
                if (($in['status'] ?? '') === 'completed') {
                    $s['orders'][$t]['method'] = 'MtnUganda';
                    $s['orders'][$t]['confirmation'] = 'MOCK' . strtoupper(substr(md5($t), 0, 8));
                }
                return true;
            });
            out($ok ? 200 : 404, ['ok' => $ok]);
        case '/__mock/ipn':
            $o = load_state($stateFile, $defaults)['orders'][(string) ($in['tracking'] ?? '')] ?? null;
            $ipn = $o !== null ? (load_state($stateFile, $defaults)['ipns'][$o['notification_id']] ?? null) : null;
            if ($o === null || $ipn === null) {
                out(404, ['ok' => false]);
            }
            if (isset($in['url']) && is_string($in['url']) && preg_match('~^http://(localhost|127\.0\.0\.1)(:\d+)?/~', $in['url']) === 1) {
                $ipn = $in['url'];                          // a CLI test does not know the sub-folder the site runs in: let it say where the registered address really is
            }
            $url = $ipn . (str_contains($ipn, '?') ? '&' : '?') . http_build_query(['OrderTrackingId' => $in['tracking'], 'OrderMerchantReference' => $o['merchant_reference'], 'OrderNotificationType' => 'IPNCHANGE']);
            $ctx = stream_context_create(['http' => ['method' => 'GET', 'ignore_errors' => true, 'timeout' => 20, 'follow_location' => 0]]);
            $body = @file_get_contents($url, false, $ctx);
            $code = 0;
            foreach ((array) ($http_response_header ?? []) as $h) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m) === 1) {
                    $code = (int) $m[1];
                }
            }
            out(200, ['http' => $code, 'body' => (string) $body, 'url' => $url]);
        case '/__mock/state':
            out(200, load_state($stateFile, $defaults));
    }
    out(404, ['error' => 'unknown control endpoint']);
}

// ------------------------------------------------------------------------------------------------ the hosted page
if (preg_match('#^/pay/([A-Za-z0-9-]{8,64})$#', $path, $m) === 1) {
    $state = load_state($stateFile, $defaults);
    $o = $state['orders'][$m[1]] ?? null;
    if ($o === null) {
        http_response_code(404);
        exit('Unknown order.');
    }
    header('Content-Type: text/html; charset=utf-8');
    $e = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES);
    echo '<!doctype html><meta charset="utf-8"><title>Mock Pesapal</title><body style="font:16px Arial;max-width:520px;margin:40px auto">'
        . '<h1>Mock Pesapal checkout</h1><p><strong>THIS IS A LOCAL MOCK. No money moves.</strong></p>'
        . '<p>Order ' . $e((string) $o['merchant_reference']) . '<br>' . $e((string) $o['currency']) . ' ' . $e(number_format((float) $o['amount'], 2)) . '</p>'
        . '<p><a id="pay" href="/__finish/' . $e($m[1]) . '/completed">Pay (mobile money)</a> | <a id="fail" href="/__finish/' . $e($m[1]) . '/failed">Decline</a> | <a id="leave" href="/__finish/' . $e($m[1]) . '/pending">Leave without paying</a></p></body>';
    exit;
}
if (preg_match('#^/__finish/([A-Za-z0-9-]{8,64})/(completed|failed|pending)$#', $path, $m) === 1) {
    $cb = with_state($stateFile, $defaults, static function (array &$s) use ($m): ?string {
        if (!isset($s['orders'][$m[1]])) {
            return null;
        }
        if ($m[2] !== 'pending') {
            $s['orders'][$m[1]]['status'] = $m[2];
            $s['orders'][$m[1]]['method'] = 'MtnUganda';
            $s['orders'][$m[1]]['confirmation'] = 'MOCK' . strtoupper(substr(md5($m[1]), 0, 8));
        }
        $o = $s['orders'][$m[1]];
        return $o['callback_url'] . (str_contains($o['callback_url'], '?') ? '&' : '?') . http_build_query(['OrderTrackingId' => $m[1], 'OrderMerchantReference' => $o['merchant_reference'], 'OrderNotificationType' => 'CALLBACKURL']);
    });
    if ($cb === null) {
        http_response_code(404);
        exit('Unknown order.');
    }
    header('Location: ' . $cb);
    exit;
}

// ------------------------------------------------------------------------------------------------ the API
if ($path === '/api/Auth/RequestToken' && $method === 'POST') {
    $s = load_state($stateFile, $defaults);
    if (($in['consumer_key'] ?? '') !== $s['key'] || ($in['consumer_secret'] ?? '') !== $s['secret']) {
        out(200, ['token' => null, 'expiryDate' => null, 'error' => ['error_type' => 'authentication_error', 'code' => 'invalid_consumer_key_or_secret_provided', 'message' => 'Invalid consumer key or secret'], 'status' => '500', 'message' => 'Error']);
    }
    $tok = bin2hex(random_bytes(24));
    with_state($stateFile, $defaults, static function (array &$st) use ($tok): void {
        $st['tokens'][$tok] = time() + 300;
    });
    out(200, ['token' => $tok, 'expiryDate' => gmdate('Y-m-d\TH:i:s.000\Z', time() + 300), 'error' => null, 'status' => '200', 'message' => 'Request processed successfully']);
}

$state = load_state($stateFile, $defaults);
if (($state['tokens'][bearer()] ?? 0) < time()) {
    out(401, ['error' => ['code' => 'unauthorized']]);
}
if ($state['fail'] === '500') {
    out(500, ['error' => 'mock failure']);
}
if ($state['fail'] === 'bad_json') {
    out(200, 'not json at all');
}

if ($path === '/api/URLSetup/GetIpnList' && $method === 'GET') {
    $list = [];
    foreach ($state['ipns'] as $id => $url) {
        $list[] = ['url' => $url, 'created_by' => 'mock', 'created_date' => gmdate('c'), 'ipn_id' => $id, 'notification_type' => 0, 'ipn_notification_type_description' => 'GET', 'ipn_status' => 1, 'ipn_status_decription' => 'Active'];
    }
    out(200, $list);
}
if ($path === '/api/URLSetup/RegisterIPN' && $method === 'POST') {
    $url = (string) ($in['url'] ?? '');
    if (!preg_match('~^https?://~', $url)) {
        out(400, ['error' => ['code' => 'invalid_url']]);
    }
    $id = guid();
    with_state($stateFile, $defaults, static function (array &$s) use ($id, $url): void {
        $s['ipns'][$id] = $url;
    });
    out(200, ['url' => $url, 'created_date' => gmdate('c'), 'ipn_id' => $id, 'error' => null, 'status' => '200']);
}
if ($path === '/api/Transactions/SubmitOrderRequest' && $method === 'POST') {
    $need = ['id', 'currency', 'amount', 'description', 'callback_url', 'notification_id', 'billing_address'];
    foreach ($need as $k) {
        if (!isset($in[$k]) || $in[$k] === '' || $in[$k] === []) {
            out(200, ['order_tracking_id' => null, 'merchant_reference' => null, 'redirect_url' => null, 'error' => ['error_type' => 'validation_error', 'code' => 'missing_' . $k, 'message' => $k . ' is required'], 'status' => '500']);
        }
    }
    if (!isset($state['ipns'][$in['notification_id']]) || !is_numeric($in['amount']) || $in['amount'] <= 0 || strlen((string) $in['id']) > 50) {
        out(200, ['order_tracking_id' => null, 'redirect_url' => null, 'error' => ['error_type' => 'validation_error', 'code' => 'invalid_order', 'message' => 'Invalid notification id, amount or id'], 'status' => '500']);
    }
    $tracking = guid();
    with_state($stateFile, $defaults, static function (array &$s) use ($tracking, $in): void {
        $s['orders'][$tracking] = ['merchant_reference' => (string) $in['id'], 'amount' => (float) $in['amount'], 'currency' => (string) $in['currency'], 'callback_url' => (string) $in['callback_url'],
            'notification_id' => (string) $in['notification_id'], 'status' => 'pending', 'created' => time()];
    });
    out(200, ['order_tracking_id' => $tracking, 'merchant_reference' => (string) $in['id'], 'redirect_url' => 'http://127.0.0.1:' . $port . '/pay/' . $tracking, 'error' => null, 'status' => '200']);
}
if ($path === '/api/Transactions/GetTransactionStatus' && $method === 'GET') {
    $t = (string) ($_GET['orderTrackingId'] ?? '');
    $o = $state['orders'][$t] ?? null;
    if ($o === null) {
        out(200, ['payment_method' => null, 'amount' => 0, 'payment_status_description' => 'Invalid', 'status_code' => 0, 'merchant_reference' => null, 'currency' => null, 'error' => ['error_type' => 'validation_error', 'code' => 'order_not_found'], 'status' => '500']);
    }
    $map = ['completed' => [1, 'Completed'], 'failed' => [2, 'Failed'], 'invalid' => [0, 'Invalid'], 'pending' => [0, 'Invalid']];
    [$code, $desc] = $map[$o['status']] ?? [0, 'Invalid'];
    out(200, [
        'payment_method'             => $o['method'] ?? null,
        'amount'                     => $o['over_amount'] ?? $o['amount'],
        'created_date'               => gmdate('c', (int) $o['created']),
        'confirmation_code'          => $o['confirmation'] ?? null,
        'payment_status_description' => $desc,
        'description'                => null,
        'message'                    => 'Request processed successfully',
        'payment_account'            => $o['status'] === 'completed' ? '256771***123' : null,
        'call_back_url'              => $o['callback_url'],
        'status_code'                => $code,
        'merchant_reference'         => $o['over_merchant_reference'] ?? $o['merchant_reference'],
        'payment_status_code'        => '',
        'currency'                   => $o['over_currency'] ?? $o['currency'],
        'error'                      => ['error_type' => null, 'code' => null, 'message' => null, 'call_back_url' => null],
        'status'                     => '200',
    ]);
}
out(404, ['error' => 'unknown endpoint ' . $path]);
