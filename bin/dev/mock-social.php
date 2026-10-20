<?php
/**
 * THROWAWAY LOCAL MOCK OF THE SOCIAL-MEDIA APIS (development and tests only). It lets you try the whole auto-share flow WITHOUT any developer account.
 * It is NOT Facebook, Instagram, X, LinkedIn, Telegram or Zapier: it imitates the few documented endpoints the site uses, so a green test here proves OUR code,
 * not the real services. Real posting must first be tried with the owner's own keys and a test post (docs/SOCIAL.md, honesty rules).
 *
 *   /c/xampp/php/php.exe -S 127.0.0.1:8123 bin/dev/mock-social.php          (leave it running in its own terminal; Ctrl+C stops it)
 *
 * and in .env (honoured ONLY when APP_ENV=local):   SOCIAL_DEV_BASE_URL=http://127.0.0.1:8123
 * (remove the line again afterwards: bin/selftest-social.php expects it to be unset). It refuses requests that do not come from 127.0.0.1 / ::1.
 * The PHP built-in server handles ONE request at a time: do not ask it to call something that calls it back.
 *
 * Imitated endpoints (path prefix = what SocialHttp adds in dev mode):
 *   /graph/{ver}/me                                   GET   -> {id,name}                                     token: Authorization Bearer / OAuth, or access_token (query or form)
 *   /graph/{ver}/{id}?fields=                         GET   -> page {id,name,instagram_business_account} | ig {id,username} | container {id,status_code} | media {permalink}
 *   /graph/{ver}/{page}/feed                          POST  form message, link             -> {"id":"PAGE_POST"}
 *   /graph/{ver}/{ig}/media                           POST  form image_url, caption        -> {"id":"container"}   (fetch_images: the mock downloads image_url and wants an image/jpeg)
 *   /graph/{ver}/{ig}/media_publish                   POST  form creation_id               -> {"id":"media"}
 *   /x/2/users/me                                     GET   -> {"data":{id,name,username}}        OAuth 1.0a (signature VERIFIED, nonce replay refused) or Bearer
 *   /x/2/tweets                                       POST  json {text}                    -> 201 {"data":{id,text}}   (280 weighted characters, URL = 23; duplicate text -> 403)
 *   /linkedin/rest/organizationAcls | /linkedin/v2/organizationAcls   GET  Bearer + X-Restli-Protocol-Version + Linkedin-Version required
 *   /linkedin/rest/posts                              POST  json {author,commentary,...}   -> 201 + header x-restli-id (urn:li:share:..), empty body
 *   /linkedin/v2/ugcPosts                             POST  json                           -> 201 {"id":"urn:li:share:.."}
 *   /telegram/bot{token}/getMe | getChat | getChatMember | sendMessage | sendPhoto     (GET or POST, JSON or form)
 *   /webhook/{anything}                               POST  JSON body; X-Lumina-Signature (hex HMAC-SHA256 of the body) verified when a secret is set, X-Lumina-Timestamp
 *                                                           and body.timestamp checked, X-Lumina-Delivery de-duplicated
 * Control endpoints (any caller on localhost; no auth):
 *   POST /__mock/reset                                forget everything (credentials back to the defaults)
 *   POST /__mock/credentials  {fb_token, ig_token, x_consumer_key, x_consumer_secret, x_token, x_token_secret, x_bearer, li_token, li_org, tg_token, webhook_secret, ...}
 *   POST /__mock/mode  {channel: graph (Facebook AND Instagram share it)|x|linkedin|telegram|webhook|*, mode, count?, delay?}   what the NEXT calls do:
 *        ok | 401 | 403 | 429 | 500 | 502 (the post IS recorded, then a gateway error) | slow (waits `delay` s, then works) | ambiguous (the post IS recorded, then it waits `delay` s: the
 *        client has timed out) | redirect | empty (200, no body) | bad_json | big (more than 1 MB) | photo_fail (Telegram refuses the photo) | not_admin (Telegram: bot is a member only)
 *        version_gone (LinkedIn: POST /rest/posts answers 426 NONEXISTENT_VERSION, /v2/ugcPosts still works) | version_gone_all (every /rest/ call answers 426)
 *        count = how many calls it applies to (default: until changed), delay default 4, after = let that many calls through first (default 0)
 *   POST /__mock/config {fetch_images: bool}
 *   GET  /__mock/state                                posts, calls, rejects (never a credential)
 * State lives in one JSON file in the system temp folder, so the separate requests of the built-in server share it.
 */
declare(strict_types=1);

$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Local use only.');
}

$stateFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lumina-mock-social-' . (int) ($_SERVER['SERVER_PORT'] ?? 0) . '.json';

function default_state(): array
{
    return [
        'cred' => [
            'fb_token' => 'MOCKFBTOKEN0123456789ABCDEFGHIJ', 'ig_token' => '', 'fb_page' => '105234567890123', 'ig_account' => '17841400000000000',
            'x_consumer_key' => 'mockconsumerkey123456', 'x_consumer_secret' => 'mockconsumersecretmockconsumersecret', 'x_token' => 'mockaccesstoken0123456789abc',
            'x_token_secret' => 'mockaccesssecretmockaccesssecret12', 'x_bearer' => 'mockbearertoken0123456789ABCDEF', 'x_user' => 'mockluminax',
            'li_token' => 'mocklinkedintoken0123456789ABCDEF', 'li_org' => 'urn:li:organization:12345678',
            'tg_token' => '123456789:MOCKTELEGRAMTOKENabcdefghijk', 'tg_bot' => 'mock_lumina_bot', 'tg_chats' => ['@luminatest' => '-1001234567890', '-1001234567890' => '-1001234567890', '-1009999999999' => '-1009999999999'],
            'webhook_secret' => 'mockwebhooksecret',
        ],
        'modes' => [], 'config' => ['fetch_images' => false], 'posts' => [], 'calls' => [], 'rejects' => [], 'nonces' => [], 'seq' => 1000, 'deliveries' => [],
    ];
}

function load_state(string $file): array
{
    $s = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    $d = default_state();
    if (!is_array($s)) {
        return $d;
    }
    $s['cred'] = array_merge($d['cred'], (array) ($s['cred'] ?? []));
    return $s + $d;
}

function save_state(string $file, array $state): void
{
    file_put_contents($file, json_encode($state), LOCK_EX);
}

function out(int $code, mixed $body = null, array $headers = []): never
{
    http_response_code($code);
    foreach ($headers as $k => $v) {
        header($k . ': ' . $v);
    }
    if (!isset($headers['Content-Type'])) {
        header('Content-Type: application/json');
    }
    if ($body !== null) {
        echo is_string($body) ? $body : json_encode($body, JSON_UNESCAPED_SLASHES);
    }
    exit;
}

function header_in(string $name): string
{
    return trim((string) ($_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $name))] ?? ''));
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$raw = (string) file_get_contents('php://input');
$ctype = strtolower(header_in('Content-Type'));
$json = str_contains($ctype, 'json') ? json_decode($raw, true) : null;
$body = is_array($json) ? $json : $_POST;
$query = $_GET;
$state = load_state($stateFile);
$cred = $state['cred'];

// ------------------------------------------------------------------------------------------------ control endpoints
if (str_starts_with($path, '/__mock/')) {
    $in = is_array($json) ? $json : $_POST;
    switch ($path) {
        case '/__mock/reset':
            @unlink($stateFile);
            out(200, ['ok' => true]);
        case '/__mock/credentials':
            foreach ($in as $k => $v) {
                if (array_key_exists($k, $state['cred']) && (is_string($v) || is_array($v))) {
                    $state['cred'][$k] = $v;
                }
            }
            save_state($stateFile, $state);
            out(200, ['ok' => true]);
        case '/__mock/mode':
            $ch = (string) ($in['channel'] ?? '*');
            $mode = (string) ($in['mode'] ?? 'ok');
            if ($mode === 'ok') {
                unset($state['modes'][$ch]);
            } else {
                $state['modes'][$ch] = ['mode' => $mode, 'left' => isset($in['count']) ? (int) $in['count'] : null, 'delay' => isset($in['delay']) ? (float) $in['delay'] : 4.0, 'after' => isset($in['after']) ? max(0, (int) $in['after']) : 0];
            }
            save_state($stateFile, $state);
            out(200, ['ok' => true]);
        case '/__mock/config':
            if (isset($in['fetch_images'])) {
                $state['config']['fetch_images'] = (bool) $in['fetch_images'];
            }
            save_state($stateFile, $state);
            out(200, ['ok' => true]);
        case '/__mock/state':
            out(200, ['posts' => $state['posts'], 'calls' => $state['calls'], 'rejects' => $state['rejects'], 'modes' => $state['modes'], 'config' => $state['config']]);
    }
    out(404, ['error' => 'unknown control endpoint']);
}

// ------------------------------------------------------------------------------------------------ helpers shared by the channels
/** @return array{0:string,1:float} mode, delay for the next call of a channel (and use one of its `count`) */
function take_mode(array &$state, string $channel): array
{
    foreach ([$channel, '*'] as $key) {
        if (isset($state['modes'][$key])) {
            $m = $state['modes'][$key];
            if ((int) ($m['after'] ?? 0) > 0) {
                // `after` = let this many calls through first (to aim a failure at the 2nd or 3rd step of a flow, e.g. Instagram's media_publish)
                $state['modes'][$key]['after'] = (int) $m['after'] - 1;
                return ['ok', 0.0];
            }
            if ($m['left'] !== null) {
                $state['modes'][$key]['left'] = $m['left'] - 1;
                if ($state['modes'][$key]['left'] <= 0) {
                    unset($state['modes'][$key]);
                }
            }
            return [(string) $m['mode'], (float) ($m['delay'] ?? 4.0)];
        }
    }
    return ['ok', 0.0];
}

function count_call(array &$state, string $channel, string $what): void
{
    $state['calls'][$channel][$what] = (int) ($state['calls'][$channel][$what] ?? 0) + 1;
}

function reject(array &$state, string $channel, string $why): void
{
    $state['rejects'][] = ['channel' => $channel, 'why' => $why, 'at' => time()];
}

function next_id(array &$state): int
{
    return ++$state['seq'];
}

/**
 * Apply a failure mode BEFORE the work. Returns the mode name when the caller must continue normally ('ok' / 'slow' / 'ambiguous' / 'photo_fail' / 'not_admin'),
 * and exits for the others. $fail(string $mode) draws the platform-shaped error for 401 / 403 / 429 / 500.
 */
function apply_mode(array &$state, string $stateFile, string $channel, callable $fail): string
{
    [$mode, $delay] = take_mode($state, $channel);
    $GLOBALS['mock_delay'] = $delay;
    if (in_array($mode, ['401', '403', '429', '500'], true)) {
        save_state($stateFile, $state);
        $fail($mode);
    }
    if ($mode === 'redirect') {
        save_state($stateFile, $state);
        out(302, '', ['Location' => 'http://127.0.0.1:1/never-followed', 'Content-Type' => 'text/plain']);
    }
    if ($mode === 'empty') {
        save_state($stateFile, $state);
        out(200, '', ['Content-Type' => 'application/json']);
    }
    if ($mode === 'bad_json') {
        save_state($stateFile, $state);
        out(200, '<html><body>not json</body></html>', ['Content-Type' => 'text/html']);
    }
    if ($mode === 'big') {
        save_state($stateFile, $state);
        out(200, '{"pad":"' . str_repeat('x', 1200000) . '"}');
    }
    if ($mode === 'slow') {
        save_state($stateFile, $state);
        usleep((int) ($delay * 1000000));
    }
    return $mode;
}

/** After the work: 'ambiguous' waits (the client gave up), '502' answers a gateway error although the work was done. */
function finish_mode(string $mode): void
{
    if ($mode === 'ambiguous') {
        usleep((int) (((float) ($GLOBALS['mock_delay'] ?? 4.0)) * 1000000));
    }
    if ($mode === '502') {
        out(502, '<html><body>Bad gateway</body></html>', ['Content-Type' => 'text/html']);
    }
}

function weighted_length(string $text): int
{
    $w = 0;
    $rest = preg_replace_callback('~https?://\S+~iu', static function () use (&$w): string {
        $w += 23;
        return '';
    }, $text) ?? $text;
    foreach (mb_str_split($rest) as $ch) {
        $cp = mb_ord($ch, 'UTF-8');
        $w += ($cp <= 4351 || ($cp >= 8192 && $cp <= 8205) || ($cp >= 8208 && $cp <= 8223) || ($cp >= 8242 && $cp <= 8247)) ? 1 : 2;
    }
    return $w;
}

// ------------------------------------------------------------------------------------------------ GRAPH API (Facebook + Instagram)
if (preg_match('#^/graph/(v\d+\.\d+)/(.+)$#', $path, $m) === 1) {
    $ch = 'graph';
    $rest = trim($m[2], '/');
    $token = '';
    if (preg_match('/^(?:Bearer|OAuth)\s+(\S+)/i', header_in('Authorization'), $am) === 1) {
        $token = $am[1];
    } elseif (isset($query['access_token'])) {
        $token = (string) $query['access_token'];
    } elseif (isset($body['access_token'])) {
        $token = (string) $body['access_token'];
    }
    $fail = static function (string $mode) use (&$state, $stateFile): never {
        if ($mode === '401') {
            out(400, ['error' => ['message' => 'Invalid OAuth access token - Cannot parse access token', 'type' => 'OAuthException', 'code' => 190, 'fbtrace_id' => 'MOCK']]);
        }
        if ($mode === '403') {
            out(403, ['error' => ['message' => '(#200) The permission(s) pages_manage_posts are not granted', 'type' => 'OAuthException', 'code' => 200, 'fbtrace_id' => 'MOCK']]);
        }
        if ($mode === '429') {
            out(429, ['error' => ['message' => '(#4) Application request limit reached', 'type' => 'OAuthException', 'code' => 4, 'fbtrace_id' => 'MOCK']], ['Retry-After' => '9']);
        }
        out(500, ['error' => ['message' => 'An unexpected error has occurred. Please retry your request later.', 'type' => 'OAuthException', 'code' => 2, 'fbtrace_id' => 'MOCK']]);
    };
    $isIg = str_starts_with($rest, $cred['ig_account'] . '/') || $rest === $cred['ig_account'];
    $valid = $token !== '' && hash_equals($isIg && $cred['ig_token'] !== '' ? $cred['ig_token'] : $cred['fb_token'], $token);
    $mode = apply_mode($state, $stateFile, $ch, $fail);
    if (!$valid) {
        reject($state, $ch, 'bad token');
        save_state($stateFile, $state);
        $fail('401');
    }
    if ($method === 'GET' && $rest === 'me') {
        count_call($state, $ch, 'me');
        save_state($stateFile, $state);
        out(200, ['id' => $cred['fb_page'], 'name' => 'Mock Lumina Page']);
    }
    if ($method === 'GET') {
        count_call($state, $ch, 'get');
        save_state($stateFile, $state);
        if ($rest === $cred['fb_page']) {
            out(200, ['id' => $cred['fb_page'], 'name' => 'Mock Lumina Page', 'instagram_business_account' => ['id' => $cred['ig_account']]]);
        }
        if ($rest === $cred['ig_account']) {
            out(200, ['id' => $cred['ig_account'], 'username' => 'mock_lumina_ig']);
        }
        if (str_starts_with($rest, 'container-')) {
            out(200, ['id' => $rest, 'status_code' => 'FINISHED']);
        }
        if (str_starts_with($rest, 'igmedia-')) {
            out(200, ['id' => $rest, 'permalink' => 'https://www.instagram.com/p/' . substr($rest, 8) . '/']);
        }
        out(400, ['error' => ['message' => 'Unsupported get request. Object with ID \'' . substr($rest, 0, 40) . '\' does not exist', 'type' => 'GraphMethodException', 'code' => 100]]);
    }
    if ($method === 'POST' && $rest === $cred['fb_page'] . '/feed') {
        $message = (string) ($body['message'] ?? '');
        $link = (string) ($body['link'] ?? '');
        if ($message === '' && $link === '') {
            save_state($stateFile, $state);
            out(400, ['error' => ['message' => '(#100) The parameter message is required', 'type' => 'OAuthException', 'code' => 100]]);
        }
        $id = $cred['fb_page'] . '_' . next_id($state);
        $state['posts'][] = ['channel' => 'facebook', 'id' => $id, 'at' => time(), 'data' => ['message' => $message, 'link' => $link]];
        count_call($state, $ch, 'feed');
        save_state($stateFile, $state);
        finish_mode($mode);
        out(200, ['id' => $id]);
    }
    if ($method === 'POST' && $rest === $cred['ig_account'] . '/media') {
        $img = (string) ($body['image_url'] ?? '');
        $cap = (string) ($body['caption'] ?? '');
        if ($img === '' || preg_match('~^https?://~i', $img) !== 1) {
            save_state($stateFile, $state);
            out(400, ['error' => ['message' => 'Invalid parameter', 'type' => 'OAuthException', 'code' => 100, 'error_user_title' => 'Media upload has failed', 'error_user_msg' => 'The image URL is missing or not valid.']]);
        }
        if (mb_strlen($cap) > 2200) {
            save_state($stateFile, $state);
            out(400, ['error' => ['message' => 'Invalid parameter', 'type' => 'OAuthException', 'code' => 100, 'error_user_msg' => 'The caption is longer than 2200 characters.']]);
        }
        if (!empty($state['config']['fetch_images'])) {
            $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
            $data = @file_get_contents($img, false, $ctx);
            $ct = '';
            foreach ($http_response_header ?? [] as $h) {
                if (stripos($h, 'content-type:') === 0) {
                    $ct = strtolower(trim(substr($h, 13)));
                }
            }
            if ($data === false || !str_starts_with($ct, 'image/jpeg')) {
                save_state($stateFile, $state);
                out(400, ['error' => ['message' => 'Only photo or video can be accepted as media type.', 'type' => 'OAuthException', 'code' => 9004, 'error_subcode' => 2207052]]);
            }
        }
        $id = 'container-' . next_id($state);
        $state['posts'][] = ['channel' => 'instagram_container', 'id' => $id, 'at' => time(), 'data' => ['image_url' => $img, 'caption' => $cap]];
        count_call($state, $ch, 'media');
        save_state($stateFile, $state);
        out(200, ['id' => $id]);
    }
    if ($method === 'POST' && $rest === $cred['ig_account'] . '/media_publish') {
        $cid = (string) ($body['creation_id'] ?? '');
        $found = null;
        foreach ($state['posts'] as $p) {
            if ($p['channel'] === 'instagram_container' && $p['id'] === $cid) {
                $found = $p;
            }
        }
        if ($found === null) {
            save_state($stateFile, $state);
            out(400, ['error' => ['message' => 'Media ID is not available', 'type' => 'OAuthException', 'code' => 9007]]);
        }
        $id = 'igmedia-' . next_id($state);
        $state['posts'][] = ['channel' => 'instagram', 'id' => $id, 'at' => time(), 'data' => ['image_url' => $found['data']['image_url'], 'caption' => $found['data']['caption'], 'creation_id' => $cid]];
        count_call($state, $ch, 'media_publish');
        save_state($stateFile, $state);
        finish_mode($mode);
        out(200, ['id' => $id]);
    }
    save_state($stateFile, $state);
    out(404, ['error' => ['message' => 'Unknown path', 'type' => 'GraphMethodException', 'code' => 803]]);
}

// ------------------------------------------------------------------------------------------------ X API v2
if (preg_match('#^/x/2/(users/me|tweets)$#', $path, $m) === 1) {
    $ch = 'x';
    $fail = static function (string $mode): never {
        if ($mode === '401') {
            out(401, ['title' => 'Unauthorized', 'type' => 'about:blank', 'status' => 401, 'detail' => 'Unauthorized']);
        }
        if ($mode === '403') {
            out(403, ['title' => 'Forbidden', 'type' => 'about:blank', 'status' => 403, 'detail' => 'Your client app is not configured with the appropriate oauth1 app permissions for this endpoint.']);
        }
        if ($mode === '429') {
            out(429, ['title' => 'Too Many Requests', 'detail' => 'Too Many Requests', 'type' => 'about:blank', 'status' => 429], ['x-rate-limit-reset' => (string) (time() + 30), 'Retry-After' => '30']);
        }
        out(500, ['title' => 'Internal Server Error', 'detail' => 'Internal Server Error', 'type' => 'about:blank', 'status' => 500]);
    };
    $mode = apply_mode($state, $stateFile, $ch, $fail);
    $auth = header_in('Authorization');
    $ok = false;
    if (preg_match('/^Bearer\s+(\S+)$/i', $auth, $am) === 1) {
        $ok = hash_equals($cred['x_bearer'], $am[1]);
    } elseif (stripos($auth, 'OAuth ') === 0) {
        $params = [];
        if (preg_match_all('/([A-Za-z0-9_]+)="([^"]*)"/', substr($auth, 6), $pm, PREG_SET_ORDER)) {
            foreach ($pm as $one) {
                $params[$one[1]] = rawurldecode($one[2]);
            }
        }
        $sig = (string) ($params['oauth_signature'] ?? '');
        unset($params['oauth_signature']);
        $fresh = isset($params['oauth_timestamp'], $params['oauth_nonce']) && abs(time() - (int) $params['oauth_timestamp']) <= 300 && !in_array($params['oauth_nonce'], $state['nonces'], true);
        if ($fresh && ($params['oauth_signature_method'] ?? '') === 'HMAC-SHA1' && ($params['oauth_consumer_key'] ?? '') === $cred['x_consumer_key'] && ($params['oauth_token'] ?? '') === $cred['x_token']) {
            $all = $params;
            foreach ($query as $k => $v) {
                $all[$k] = $v;
            }
            if (str_contains($ctype, 'x-www-form-urlencoded')) {
                foreach ($_POST as $k => $v) {
                    $all[$k] = $v;
                }
            }
            $pairs = [];
            foreach ($all as $k => $v) {
                $pairs[] = [rawurlencode((string) $k), rawurlencode((string) $v)];
            }
            sort($pairs);
            $norm = implode('&', array_map(static fn(array $p): string => $p[0] . '=' . $p[1], $pairs));
            $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
            $base = 'http://' . $host . $path;
            $baseString = strtoupper($method) . '&' . rawurlencode($base) . '&' . rawurlencode($norm);
            $key = rawurlencode($cred['x_consumer_secret']) . '&' . rawurlencode($cred['x_token_secret']);
            $expect = base64_encode(hash_hmac('sha1', $baseString, $key, true));
            $ok = hash_equals($expect, $sig);
            if ($ok) {
                $state['nonces'][] = $params['oauth_nonce'];
                $state['nonces'] = array_slice($state['nonces'], -200);
            } else {
                reject($state, $ch, 'bad oauth1 signature');
            }
        } else {
            reject($state, $ch, 'oauth1 parameters');
        }
    }
    if (!$ok) {
        save_state($stateFile, $state);
        $fail('401');
    }
    if ($m[1] === 'users/me') {
        count_call($state, $ch, 'me');
        save_state($stateFile, $state);
        out(200, ['data' => ['id' => '9001', 'name' => 'Mock Lumina', 'username' => $cred['x_user']]]);
    }
    if ($method !== 'POST' || !is_array($json)) {
        save_state($stateFile, $state);
        out(400, ['title' => 'Invalid Request', 'detail' => 'The request body must be JSON.', 'type' => 'about:blank', 'status' => 400]);
    }
    $text = (string) ($json['text'] ?? '');
    if ($text === '' || weighted_length($text) > 280) {
        save_state($stateFile, $state);
        out(400, ['title' => 'Invalid Request', 'detail' => $text === '' ? 'The text is required.' : 'Tweet text is too long (weighted length ' . weighted_length($text) . ' > 280).', 'type' => 'about:blank', 'status' => 400]);
    }
    foreach ($state['posts'] as $p) {
        if ($p['channel'] === 'x' && $p['data']['text'] === $text) {
            save_state($stateFile, $state);
            out(403, ['title' => 'Forbidden', 'detail' => 'You are not allowed to create a Tweet with duplicate content.', 'type' => 'about:blank', 'status' => 403]);
        }
    }
    $id = (string) (1700000000000000000 + next_id($state));
    $state['posts'][] = ['channel' => 'x', 'id' => $id, 'at' => time(), 'data' => ['text' => $text]];
    count_call($state, $ch, 'tweets');
    save_state($stateFile, $state);
    finish_mode($mode);
    out(201, ['data' => ['id' => $id, 'text' => $text]]);
}

// ------------------------------------------------------------------------------------------------ LINKEDIN
if (preg_match('#^/linkedin/(rest|v2)/(organizationAcls|posts|ugcPosts)$#', $path, $m) === 1) {
    $ch = 'linkedin';
    $fail = static function (string $mode): never {
        if ($mode === '401') {
            out(401, ['status' => 401, 'serviceErrorCode' => 65601, 'code' => 'REVOKED_ACCESS_TOKEN', 'message' => 'The token used in the request has been revoked by the user.']);
        }
        if ($mode === '403') {
            out(403, ['status' => 403, 'serviceErrorCode' => 100, 'code' => 'ACCESS_DENIED', 'message' => 'Not enough permissions to access: partnerApiPostsExternal.CREATE.20250601']);
        }
        if ($mode === '429') {
            out(429, ['status' => 429, 'code' => 'TOO_MANY_REQUESTS', 'message' => 'Resource level throttle APPLICATION DAY limit for calls to this resource is reached.'], ['Retry-After' => '60']);
        }
        out(500, ['status' => 500, 'message' => 'Internal Server Error']);
    };
    $mode = apply_mode($state, $stateFile, $ch, $fail);
    $valid = preg_match('/^Bearer\s+(\S+)$/i', header_in('Authorization'), $am) === 1 && hash_equals($cred['li_token'], $am[1]);
    if (!$valid) {
        reject($state, $ch, 'bad token');
        save_state($stateFile, $state);
        $fail('401');
    }
    if ($mode === 'version_gone' && $m[1] === 'rest' && $m[2] === 'posts') {
        // LinkedIn answers 426 when the Linkedin-Version header names a retired version (nothing is created); the legacy /v2/ugcPosts keeps working
        save_state($stateFile, $state);
        out(426, ['status' => 426, 'code' => 'NONEXISTENT_VERSION', 'message' => 'Requested version 20250601 is not active']);
    }
    if ($mode === 'version_gone_all' && $m[1] === 'rest') {
        save_state($stateFile, $state);
        out(426, ['status' => 426, 'code' => 'NONEXISTENT_VERSION', 'message' => 'Requested version 20250601 is not active']);
    }
    if ($m[1] === 'rest' && (header_in('X-Restli-Protocol-Version') !== '2.0.0' || preg_match('/^20\d{4}$/', header_in('Linkedin-Version')) !== 1)) {
        save_state($stateFile, $state);
        out(400, ['status' => 400, 'code' => 'VERSION_MISSING', 'message' => 'A version must be provided in the Linkedin-Version header and X-Restli-Protocol-Version must be 2.0.0.']);
    }
    if ($m[2] === 'organizationAcls') {
        count_call($state, $ch, 'acls');
        save_state($stateFile, $state);
        out(200, ['elements' => [['organization' => $cred['li_org'], 'role' => 'ADMINISTRATOR', 'state' => 'APPROVED', 'roleAssignee' => 'urn:li:person:MOCK']], 'paging' => ['start' => 0, 'count' => 10]]);
    }
    if ($method !== 'POST' || !is_array($json)) {
        save_state($stateFile, $state);
        out(400, ['status' => 400, 'message' => 'The request body must be JSON.']);
    }
    $author = (string) ($json['author'] ?? '');
    $text = (string) ($json['commentary'] ?? ($json['specificContent']['com.linkedin.ugc.ShareContent']['shareCommentary']['text'] ?? ''));
    if ($author !== $cred['li_org']) {
        save_state($stateFile, $state);
        out(403, ['status' => 403, 'code' => 'ACCESS_DENIED', 'message' => 'Unpermitted fields present in author or not an administrator of that organization.']);
    }
    if ($text === '' || mb_strlen($text) > 3000) {
        save_state($stateFile, $state);
        out(422, ['status' => 422, 'message' => 'commentary is missing or longer than 3000 characters']);
    }
    $urn = 'urn:li:share:' . next_id($state);
    $state['posts'][] = ['channel' => 'linkedin', 'id' => $urn, 'at' => time(), 'data' => ['commentary' => $text, 'article' => $json['content']['article']['source'] ?? '', 'author' => $author]];
    count_call($state, $ch, $m[2]);
    save_state($stateFile, $state);
    finish_mode($mode);
    if ($m[2] === 'posts') {
        out(201, '', ['x-restli-id' => $urn, 'Content-Type' => 'application/json']);
    }
    out(201, ['id' => $urn]);
}

// ------------------------------------------------------------------------------------------------ TELEGRAM
if (preg_match('#^/telegram/bot([^/]+)/(getMe|getChat|getChatMember|sendMessage|sendPhoto)$#', $path, $m) === 1) {
    $ch = 'telegram';
    $fail = static function (string $mode): never {
        if ($mode === '401') {
            out(401, ['ok' => false, 'error_code' => 401, 'description' => 'Unauthorized']);
        }
        if ($mode === '403') {
            out(403, ['ok' => false, 'error_code' => 403, 'description' => 'Forbidden: bot is not a member of the channel chat']);
        }
        if ($mode === '429') {
            out(429, ['ok' => false, 'error_code' => 429, 'description' => 'Too Many Requests: retry after 7', 'parameters' => ['retry_after' => 7]]);
        }
        out(500, ['ok' => false, 'error_code' => 500, 'description' => 'Internal Server Error']);
    };
    $mode = apply_mode($state, $stateFile, $ch, $fail);
    if (!hash_equals($cred['tg_token'], rawurldecode($m[1]))) {
        reject($state, $ch, 'bad token');
        save_state($stateFile, $state);
        $fail('401');
    }
    $in = array_merge($query, $body);
    $chat = (string) ($in['chat_id'] ?? '');
    $known = isset($cred['tg_chats'][$chat]);
    $username = ltrim($chat, '@');
    if ($m[2] === 'getMe') {
        count_call($state, $ch, 'getMe');
        save_state($stateFile, $state);
        out(200, ['ok' => true, 'result' => ['id' => 123456789, 'is_bot' => true, 'first_name' => 'Mock Lumina', 'username' => $cred['tg_bot']]]);
    }
    if (!$known) {
        save_state($stateFile, $state);
        out(400, ['ok' => false, 'error_code' => 400, 'description' => 'Bad Request: chat not found']);
    }
    $chatId = (string) $cred['tg_chats'][$chat];
    $chatObj = ['id' => (int) $chatId, 'title' => 'Mock Channel', 'type' => 'channel'] + (str_starts_with($chat, '@') ? ['username' => $username] : []);
    if ($m[2] === 'getChat') {
        count_call($state, $ch, 'getChat');
        save_state($stateFile, $state);
        out(200, ['ok' => true, 'result' => $chatObj]);
    }
    if ($m[2] === 'getChatMember') {
        count_call($state, $ch, 'getChatMember');
        save_state($stateFile, $state);
        out(200, ['ok' => true, 'result' => ['status' => $mode === 'not_admin' ? 'member' : 'administrator', 'user' => ['id' => (int) ($in['user_id'] ?? 0), 'is_bot' => true]]]);
    }
    if ($m[2] === 'sendMessage') {
        $text = (string) ($in['text'] ?? '');
        if ($text === '') {
            save_state($stateFile, $state);
            out(400, ['ok' => false, 'error_code' => 400, 'description' => 'Bad Request: message text is empty']);
        }
        if (mb_strlen($text) > 4096) {
            save_state($stateFile, $state);
            out(400, ['ok' => false, 'error_code' => 400, 'description' => 'Bad Request: message is too long']);
        }
        $data = ['chat_id' => $chat, 'text' => $text, 'has_parse_mode' => isset($in['parse_mode'])];
    } else {
        $photo = (string) ($in['photo'] ?? '');
        $cap = (string) ($in['caption'] ?? '');
        if ($mode === 'photo_fail' || preg_match('~^https?://~i', $photo) !== 1) {
            save_state($stateFile, $state);
            out(400, ['ok' => false, 'error_code' => 400, 'description' => 'Bad Request: wrong file identifier/HTTP URL specified']);
        }
        if (mb_strlen($cap) > 1024) {
            save_state($stateFile, $state);
            out(400, ['ok' => false, 'error_code' => 400, 'description' => 'Bad Request: message caption is too long']);
        }
        $data = ['chat_id' => $chat, 'photo' => $photo, 'caption' => $cap, 'has_parse_mode' => isset($in['parse_mode'])];
    }
    $mid = next_id($state);
    $state['posts'][] = ['channel' => 'telegram', 'id' => (string) $mid, 'at' => time(), 'data' => $data + ['method' => $m[2]]];
    count_call($state, $ch, $m[2]);
    save_state($stateFile, $state);
    finish_mode($mode);
    out(200, ['ok' => true, 'result' => ['message_id' => $mid, 'chat' => $chatObj, 'date' => time()]]);
}

// ------------------------------------------------------------------------------------------------ WEBHOOK RECEIVER
if (str_starts_with($path, '/webhook')) {
    $ch = 'webhook';
    $fail = static function (string $mode): never {
        out($mode === '401' ? 401 : ($mode === '403' ? 403 : ($mode === '429' ? 429 : 500)), ['error' => 'mock ' . $mode]);
    };
    $mode = apply_mode($state, $stateFile, $ch, $fail);
    if ($path === '/webhook/redirected') {
        out(200, ['id' => 'hook-redirected']);
    }
    if ($mode === 'redirect_private') {
        save_state($stateFile, $state);
        out(302, '', ['Location' => 'http://169.254.169.254/latest/meta-data/', 'Content-Type' => 'text/plain']);
    }
    $sig = header_in('X-Lumina-Signature');
    $secret = (string) $cred['webhook_secret'];
    $valid = $secret === '' ? null : ($sig !== '' && hash_equals(hash_hmac('sha256', $raw, $secret), $sig));
    $data = json_decode($raw, true);
    if ($valid === false) {
        reject($state, $ch, 'bad signature');
        save_state($stateFile, $state);
        out(401, ['error' => 'bad signature']);
    }
    if (!is_array($data) || abs(time() - (int) ($data['timestamp'] ?? 0)) > 300 || (int) header_in('X-Lumina-Timestamp') !== (int) ($data['timestamp'] ?? -1)) {
        reject($state, $ch, 'bad body or stale timestamp');
        save_state($stateFile, $state);
        out(400, ['error' => 'bad body or stale timestamp']);
    }
    $delivery = header_in('X-Lumina-Delivery');
    if ($delivery !== '' && in_array($delivery, $state['deliveries'], true)) {
        count_call($state, $ch, 'duplicates');
        save_state($stateFile, $state);
        out(200, ['id' => 'hook-dup', 'duplicate' => true]);
    }
    $state['deliveries'][] = $delivery;
    $id = 'hook-' . next_id($state);
    $state['posts'][] = ['channel' => 'webhook', 'id' => $id, 'at' => time(), 'data' => ['event' => $data['event'] ?? '', 'test' => (bool) ($data['test'] ?? false), 'delivery' => $delivery, 'signed' => $valid === true, 'keys' => array_keys($data), 'body' => $data]];
    count_call($state, $ch, 'received');
    save_state($stateFile, $state);
    finish_mode($mode);
    out(200, ['id' => $id]);
}

out(404, ['error' => 'mock-social: unknown path']);
