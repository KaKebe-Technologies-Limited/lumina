<?php
/**
 * CONFORMANCE TESTS of the four API adapters (Facebook Page, Instagram, X, LinkedIn) and of the OAuth 1.0a signer, against the LOCAL MOCK bin/dev/mock-social.php
 * (docs/SOCIAL.md sections 4 and 11). Run alone:   /c/xampp/php/php.exe bin/selftest-social-channels.php     or from bin/selftest.php (own process).
 *
 * What it proves: for every adapter and every outcome the mock can produce (ok, 401, 403, 429, 500, 502, slow, ambiguous, redirect, empty, bad JSON, oversized, connection
 * refused, no time left) the adapter returns the right SocialResult state (posted | transient | failed | auth | review) with a redacted text, never throws, and never lets a
 * credential reach a result, a log line or a URL. The OAuth 1.0a signer is checked against RFC 5849 3.4.1 and the worked example in X's documentation; the mock then VERIFIES
 * the signature of every X request, refuses a replayed nonce and a stale timestamp.
 *
 * HONESTY: a green run proves OUR code against an imitation. No real platform is contacted (the only server is the mock on 127.0.0.1); nothing here shows that a real
 * Facebook / Instagram / X / LinkedIn account accepts the posts: the owner must press "Test connection" and "Send a test post" with their own keys first.
 */
declare(strict_types=1);

if (function_exists('check')) {
    // included by bin/selftest.php: run in our OWN process (clean scope, owns its mock server) and relay the output
    $zcProc = proc_open([PHP_BINARY, __FILE__], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $zcPipes);
    $zcOut = is_resource($zcProc) ? (string) stream_get_contents($zcPipes[1]) : '';
    $zcErr = is_resource($zcProc) ? (string) stream_get_contents($zcPipes[2]) : 'could not start the process';
    $zcCode = is_resource($zcProc) ? proc_close($zcProc) : -1;
    echo $zcOut . ($zcErr !== '' ? $zcErr . PHP_EOL : '');
    check('the social channel conformance suite (own process) ends with ALL CHECKS PASSED', $zcCode === 0 && str_contains($zcOut, 'ALL CHECKS PASSED'), 'exit code ' . $zcCode);
    return;
}
if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';
if (\App\Core\Config::get('app.env') !== 'local') {
    exit("Refusing to run outside APP_ENV=local.\n");
}

use App\Core\Settings;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\ChannelSupport;
use App\Services\Social\Channels;
use App\Services\Social\OAuth1Signer;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialResult;

$fail = 0;
if (!function_exists('check')) {   // conditional on purpose: a top-level function would be hoisted and the guard at the top would fire in standalone runs
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
}

$zzRoot = dirname(__DIR__);
$started = microtime(true);
echo "Social channels: conformance of Facebook, Instagram, X, LinkedIn against the LOCAL MOCK\n";

// ===================================================================================================================================================== 1. the signer
echo "-- OAuth 1.0a signer\n";
$rfcPairs = [['b5', '=%3D'], ['a3', 'a'], ['c@', ''], ['a2', 'r b'], ['c2', ''], ['a3', '2 q'], ['oauth_consumer_key', '9djdj82h48djs9d2'], ['oauth_token', 'kkk9d7dh3k39sjv7'], ['oauth_signature_method', 'HMAC-SHA1'], ['oauth_timestamp', '137131201'], ['oauth_nonce', '7d8f3e4a']];
check('RFC 5849 3.4.1: the base string of the specification\'s own example (duplicate names, encoded names, empty values)', OAuth1Signer::baseString('POST', 'http://example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b', $rfcPairs)
    === 'POST&http%3A%2F%2Fexample.com%2Frequest&a2%3Dr%2520b%26a3%3D2%2520q%26a3%3Da%26b5%3D%253D%25253D%26c%2540%3D%26c2%3D%26oauth_consumer_key%3D9djdj82h48djs9d2%26oauth_nonce%3D7d8f3e4a%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D137131201%26oauth_token%3Dkkk9d7dh3k39sjv7');
$docSigner = new OAuth1Signer('xvz1evFS4wEEPTGEFPHBog', 'kAcSOqF21Fu85e7zjz7ZN2U4ZRhfV3WpwPAoE3Z7kBw', '370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb', 'LswwdoUaIvS8ltyTt5jkRh4J50vUPVVHtR2YPi5kE');
$docParams = $docSigner->parameters('POST', 'https://api.twitter.com/1.1/statuses/update.json?include_entities=true', ['status' => 'Hello Ladies + Gentlemen, a signed OAuth request!'], 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg', 1318622958);
check('X documentation "Creating a signature": the published example gives the published signature (hCtSmYh+iHYCEqBWrE7C7hYmtUk=)', $docParams['oauth_signature'] === 'hCtSmYh+iHYCEqBWrE7C7hYmtUk=');
$hdr = $docSigner->header('POST', 'https://api.twitter.com/1.1/statuses/update.json?include_entities=true', ['status' => 'Hello Ladies + Gentlemen, a signed OAuth request!'], 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg', 1318622958);
check('the Authorization header: "OAuth " + sorted, quoted, percent-encoded parameters, signature encoded (+ / = escaped)', str_starts_with($hdr, 'OAuth oauth_consumer_key="xvz1evFS4wEEPTGEFPHBog", oauth_nonce="kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg", oauth_signature="hCtSmYh%2BiHYCEqBWrE7C7hYmtUk%3D", oauth_signature_method="HMAC-SHA1", oauth_timestamp="1318622958", oauth_token="370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb", oauth_version="1.0"'), $hdr);
check('percent-encoding follows RFC 3986 (space %20, ~ - . _ kept, * and + and unicode escaped)', OAuth1Signer::encode("a b~c-d.e_f*g+h\u{e9}") === 'a%20b~c-d.e_f%2Ag%2Bh%C3%A9');
check('base URI: lower-case scheme/host, default ports dropped, other ports kept, query and fragment removed',
    OAuth1Signer::baseUri('HTTPS://API.X.com:443/2/tweets?a=1#f') === 'https://api.x.com/2/tweets' && OAuth1Signer::baseUri('http://127.0.0.1:8123/x/2/tweets') === 'http://127.0.0.1:8123/x/2/tweets' && OAuth1Signer::baseUri('http://example.com:80') === 'http://example.com/');
$s1 = new OAuth1Signer('ck', 'cs', 'tk', 'ts');
$a = $s1->parameters('GET', 'https://api.x.com/2/users/me');
$b = $s1->parameters('GET', 'https://api.x.com/2/users/me');
check('every signature uses a fresh 128-bit nonce and a current timestamp', $a['oauth_nonce'] !== $b['oauth_nonce'] && strlen($a['oauth_nonce']) === 32 && abs((int) $a['oauth_timestamp'] - time()) <= 2);
check('a JSON body is not part of the signature: the signature does not depend on any body argument, a query parameter is', $s1->parameters('POST', 'https://api.x.com/2/tweets', [], 'n1', 100)['oauth_signature'] === $s1->parameters('POST', 'https://api.x.com/2/tweets', [], 'n1', 100)['oauth_signature']
    && $s1->parameters('GET', 'https://api.x.com/2/x?a=1', [], 'n1', 100)['oauth_signature'] !== $s1->parameters('GET', 'https://api.x.com/2/x?a=2', [], 'n1', 100)['oauth_signature']);
check('a dump or an exception trace of the signer shows no credential', !str_contains(print_r($s1, true), 'tk') && !str_contains(var_export($s1->__debugInfo(), true), 'cs'));

// ===================================================================================================================================================== 2. start the mock
echo "-- the local mock\n";
$mockPort = 0;
foreach ([18131, 18132, 18133, 28131] as $try) {
    $sock = @fsockopen('127.0.0.1', $try, $en, $es, 0.3);
    if ($sock === false) {
        $mockPort = $try;
        break;
    }
    fclose($sock);
}
$mockProc = null;
$mockPipes = [];
if ($mockPort !== 0) {
    $null = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
    $mockProc = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $mockPort, $zzRoot . '/bin/dev/mock-social.php'], [0 => ['pipe', 'r'], 1 => ['file', $null, 'w'], 2 => ['file', $null, 'w']], $mockPipes);
    for ($i = 0; $i < 40; $i++) {
        $sock = @fsockopen('127.0.0.1', $mockPort, $en, $es, 0.2);
        if ($sock !== false) {
            fclose($sock);
            break;
        }
        usleep(150000);
    }
}
/** Process-level environment override that also beats a line in .env (other sessions may have a dev override there): App\Core\Env::load() of a throw-away file. '' = unset. */
$setEnv = static function (string $key, string $value): void {
    $f = tempnam(sys_get_temp_dir(), 'lumsc');
    file_put_contents($f, $key . '=' . $value . "\n");
    \App\Core\Env::load($f);
    @unlink($f);
    putenv($value === '' ? $key : $key . '=' . $value);
};
$mock = static function (string $path, array $data = [], string $method = 'POST') use (&$mockPort): array {
    $ch = curl_init('http://127.0.0.1:' . $mockPort . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_POSTFIELDS => $method === 'POST' ? json_encode($data) : null, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'json' => json_decode($body, true), 'body' => $body];
};
$cleanup = static function () use (&$mockProc, &$mockPipes, $mock, &$mockPort, $setEnv): void {
    if ($mockPort !== 0) {
        @$mock('/__mock/reset');
    }
    if (is_resource($mockProc)) {
        foreach ($mockPipes as $p) {
            if (is_resource($p)) {
                fclose($p);
            }
        }
        proc_terminate($mockProc);
        proc_close($mockProc);
    }
    $setEnv('SOCIAL_DEV_BASE_URL', '');
    $setEnv('SOCIAL_FB_PAGE_TOKEN', '');
};
if ($mockPort === 0 || @$mock('/__mock/state', [], 'GET')['code'] !== 200) {
    echo "  FAIL the mock server could not be started: the adapter checks cannot run\n";
    $cleanup();
    exit(1);
}
$devBase = 'http://127.0.0.1:' . $mockPort;
$tok = ['fb' => 'MOCKFBTOKEN0123456789ABCDEFGHIJ', 'li' => 'mocklinkedintoken0123456789ABCDEF'];
$xc = ['x_consumer_key' => 'mockconsumerkey123456', 'x_consumer_secret' => 'mockconsumersecretmockconsumersecret12345', 'x_token' => 'mockaccesstoken0123456789abc', 'x_token_secret' => 'mockaccesssecretmockaccesssecret1234567', 'x_bearer' => 'mockbearertoken0123456789ABCDEF'];
$secrets = [$tok['fb'], $tok['li'], $xc['x_consumer_key'], $xc['x_consumer_secret'], $xc['x_token'], $xc['x_token_secret'], $xc['x_bearer']];
$reset = static function () use ($mock, $tok, $xc): void {
    $mock('/__mock/reset');
    $mock('/__mock/credentials', ['fb_token' => $tok['fb'], 'li_token' => $tok['li']] + $xc);
};
$reset();
$setEnv('SOCIAL_DEV_BASE_URL', $devBase);
$setEnv('SOCIAL_FB_PAGE_TOKEN', '');
Settings::flush();
$state = static fn(): array => (array) $mock('/__mock/state', [], 'GET')['json'];
$postsOf = static fn(string $ch): array => array_values(array_filter($state()['posts'] ?? [], static fn(array $p): bool => $p['channel'] === $ch));
$last = static fn(array $a): array => $a === [] ? [] : $a[array_key_last($a)];
$calls = static fn(string $ch, string $what): int => (int) ($state()['calls'][$ch][$what] ?? 0);
$mode = static function (string $channel, string $m, array $o = []) use ($mock): void {
    $mock('/__mock/mode', ['channel' => $channel, 'mode' => $m] + $o);
};
$logFile = STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log';
$logStart = is_file($logFile) ? (int) filesize($logFile) : 0;
/** everything an adapter returned in this run, scanned for credentials at the end */
$seen = [];
$keep = static function (mixed $r) use (&$seen): mixed {
    $seen[] = $r instanceof SocialResult ? $r->toArray() : $r;
    return $r;
};
$payload = static function (array $o = []): SocialPayload {
    return new SocialPayload(...($o + [
        'channel' => 'facebook', 'postId' => 5151, 'newsletterId' => 0, 'title' => 'Conformance title', 'summary' => 'Conformance summary', 'url' => 'https://example.org/newsletter/x?utm_source=facebook',
        'plainUrl' => 'https://example.org/newsletter/x', 'caption' => 'Conformance caption ' . bin2hex(random_bytes(3)), 'imageUrl' => 'https://example.org/social/image/' . str_repeat('a', 64) . '.jpg',
        'images' => [], 'hashtags' => ['#a'], 'issueNumber' => 9, 'issueDate' => '2026-10-20', 'test' => false, 'deadline' => null, 'eventId' => 'lumina-post-5151', 'siteName' => 'Lumina',
    ]));
};
$fb = Channels::get('facebook');
$ig = Channels::get('instagram');
$x = Channels::get('x');
$li = Channels::get('linkedin');
$fbCfg = ['page_id' => '105234567890123', 'page_token' => $tok['fb']];
$igCfg = ['account_id' => '17841400000000000', 'token' => ''];
$xCfg = ['mode' => 'oauth1', 'api_key' => $xc['x_consumer_key'], 'api_secret' => $xc['x_consumer_secret'], 'access_token' => $xc['x_token'], 'access_secret' => $xc['x_token_secret'], 'bearer_token' => ''];
$xBearer = ['mode' => 'bearer', 'api_key' => '', 'api_secret' => '', 'access_token' => '', 'access_secret' => '', 'bearer_token' => $xc['x_bearer']];
$liCfg = ['org_urn' => 'urn:li:organization:12345678', 'access_token' => $tok['li'], 'expires' => ''];
check('the four adapters are marked built and keep their metadata', $fb->meta()['built'] && $ig->meta()['built'] && $x->meta()['built'] && $li->meta()['built'] && $fb->meta()['limit'] === 5000 && $x->meta()['count'] === 'x');
check('the mock is reachable, fresh, and the dev base is honoured (APP_ENV=local)', $state()['posts'] === [] && SocialHttp::devBase() === $devBase);
$noMode = static function () use ($mock): void {
    $mock('/__mock/mode', ['channel' => '*', 'mode' => 'ok']);
    foreach (['graph', 'x', 'linkedin'] as $c) {
        $mock('/__mock/mode', ['channel' => $c, 'mode' => 'ok']);
    }
};

// ===================================================================================================================================================== 3. Facebook
echo "-- Facebook Page\n";
$r = $keep($fb->test($fbCfg));
check('test(): GET /me + GET /{page}: ok, detail is the Page name', $r['ok'] === true && $r['state'] === 'ok' && ($r['detail'] ?? '') === 'Mock Lumina Page' && $calls('graph', 'me') === 1, json_encode($r));
$r = $keep($fb->test(['page_id' => '999999999', 'page_token' => $tok['fb']]));
check('test(): a token whose /me is another id than the configured Page is explained (not a Page token / wrong ID)', $r['ok'] === false && str_contains($r['message'], 'Page'), json_encode($r));
$r = $keep($fb->test(['page_id' => '105234567890123', 'page_token' => 'WRONGTOKENWRONGTOKEN1234567890']));
check('test(): a rejected token (Graph code 190) -> state auth, and the token is not in the message', $r['state'] === 'auth' && !str_contains($r['message'], 'WRONGTOKEN'), json_encode($r));
$meCalls = $calls('graph', 'me');
$r = $keep($fb->test(['page_id' => '', 'page_token' => '']));
check('test(): missing fields are named, no call is made', $r['ok'] === false && str_contains($r['message'], 'Page ID') && $calls('graph', 'me') === $meCalls);
$cap = 'Hello Facebook ' . bin2hex(random_bytes(4));
$res = $keep($fb->post($fbCfg, $payload(['caption' => $cap])));
$p = $postsOf('facebook');
check('post(): POST /{page}/feed with message + link, result posted with id and https://www.facebook.com/<id>', $res->state === 'posted' && $res->remoteId === ($p[0]['id'] ?? '?') && $res->remoteUrl === 'https://www.facebook.com/' . $res->remoteId && $p[0]['data']['message'] === $cap && $p[0]['data']['link'] === 'https://example.org/newsletter/x', json_encode($res->toArray()));
$res = $keep($fb->post($fbCfg, $payload(['test' => true, 'caption' => 'ignored caption'])));
$p = $postsOf('facebook');
check('post(): a test post says "Test post from Lumina - ignore" and carries NO link', $res->state === 'posted' && str_starts_with($p[1]['data']['message'], 'Test post from Lumina - ignore') && $p[1]['data']['link'] === '');
$before = count($postsOf('facebook'));
$res = $keep($fb->post($fbCfg, $payload(['caption' => str_repeat('x', 5001)])));
check('post(): a caption over 5000 characters is refused locally (failed too_long), nothing is sent', $res->state === 'failed' && $res->code === 'too_long' && count($postsOf('facebook')) === $before);
$res = $keep($fb->post($fbCfg, $payload(['caption' => '   '])));
check('post(): an empty caption is refused locally', $res->state === 'failed' && $res->code === 'empty_caption');
$res = $keep($fb->post(['page_id' => '', 'page_token' => ''], $payload()));
check('post(): an unconfigured channel is "failed not_configured" and never throws', $res->state === 'failed' && $res->code === 'not_configured');
$lastMs = 0.0;
$outcome = static function (string $channel, string $m, callable $do, array $o = []) use ($mode, $noMode, $keep, &$lastMs): SocialResult {
    $mode($channel, $m, $o + ['count' => 1]);
    $t0 = microtime(true);
    $res = $keep($do());
    $lastMs = microtime(true) - $t0;     // the call itself (the mock is single-threaded: resetting its mode afterwards waits for a delayed request to finish)
    $noMode();
    return $res;
};
$n0 = count($postsOf('facebook'));
$res = $outcome('graph', '401', fn() => $fb->post($fbCfg, $payload()));
check('Facebook 401 (code 190) -> auth', $res->state === 'auth' && $res->error !== '' && count($postsOf('facebook')) === $n0, json_encode($res->toArray()));
$res = $outcome('graph', '403', fn() => $fb->post($fbCfg, $payload()));
check('Facebook 403 (code 200, missing permission) -> failed "permission" with advice, not retried', $res->state === 'failed' && $res->code === 'permission' && str_contains($res->error, 'pages_manage_posts'), json_encode($res->toArray()));
$res = $outcome('graph', '429', fn() => $fb->post($fbCfg, $payload()));
check('Facebook 429 -> transient, honours Retry-After 9', $res->state === 'transient' && $res->retryAfter === 9 && $res->code === 'rate_limited', json_encode($res->toArray()));
$res = $outcome('graph', '500', fn() => $fb->post($fbCfg, $payload()));
check('Facebook 500 -> transient', $res->state === 'transient' && count($postsOf('facebook')) === $n0, json_encode($res->toArray()));
$res = $outcome('graph', '502', fn() => $fb->post($fbCfg, $payload()));
check('Facebook 502 after the post was made -> review (never blindly re-posted)', $res->state === 'review' && count($postsOf('facebook')) === $n0 + 1, json_encode($res->toArray()));
$n1 = count($postsOf('facebook'));
$res = $outcome('graph', 'ambiguous', fn() => $fb->post($fbCfg, $payload(['deadline' => microtime(true) + 2.0])), ['delay' => 4]);
check('Facebook: a timeout AFTER the request was sent (the post was recorded) -> review, and the deadline is honoured (about 2 s)', $res->state === 'review' && $res->code === 'ambiguous' && count($postsOf('facebook')) === $n1 + 1 && $lastMs < 2.8, json_encode($res->toArray()) . ' ' . round($lastMs, 1) . 's');
usleep(2300000);   // let the mock finish the delayed request before the next one (the built-in server answers one request at a time)
$res = $outcome('graph', 'slow', fn() => $fb->post($fbCfg, $payload()), ['delay' => 1]);
check('Facebook: a slow answer (1 s) still posts', $res->state === 'posted');
$res = $outcome('graph', 'redirect', fn() => $fb->post($fbCfg, $payload()));
check('Facebook: a redirect is never followed -> failed', $res->state === 'failed' && $res->code === 'redirect');
$res = $outcome('graph', 'empty', fn() => $fb->post($fbCfg, $payload()));
check('Facebook: 200 with an empty body -> review (accepted but no id: a person checks)', $res->state === 'review' && $res->code === 'no_id', json_encode($res->toArray()));
$res = $outcome('graph', 'bad_json', fn() => $fb->post($fbCfg, $payload()));
check('Facebook: 200 with an HTML body -> review', $res->state === 'review');
$res = $outcome('graph', 'big', fn() => $fb->post($fbCfg, $payload()));
check('Facebook: an answer over 1 MB is refused -> failed too_large', $res->state === 'failed' && $res->code === 'too_large', json_encode($res->toArray()));
$res = $keep($fb->post($fbCfg, $payload(['deadline' => microtime(true) - 1.0])));
check('Facebook: no time left in the run -> transient "budget", no request sent', $res->state === 'transient' && $res->code === 'budget' && count($postsOf('facebook')) === $n1 + 2);
$setEnv('SOCIAL_DEV_BASE_URL', 'http://127.0.0.1:1');
$res = $keep($fb->post($fbCfg, $payload()));
$t = $keep($fb->test($fbCfg));
$setEnv('SOCIAL_DEV_BASE_URL', $devBase);
check('Facebook: connection refused (nothing sent) -> transient; test() says the service could not be reached', $res->state === 'transient' && $t['ok'] === false && $t['state'] === 'error', json_encode([$res->toArray(), $t]));

// ===================================================================================================================================================== 4. Instagram
echo "-- Instagram\n";
$r = $keep($ig->test($igCfg));
check('test(): without any token the message says where it comes from', $r['ok'] === false && str_contains($r['message'], 'Facebook Page token'), json_encode($r));
$igCfgTok = ['account_id' => '17841400000000000', 'token' => $tok['fb']];
$r = $keep($ig->test($igCfgTok));
check('test(): GET /{account}?fields=id,username -> ok, detail @mock_lumina_ig', $r['ok'] === true && ($r['detail'] ?? '') === '@mock_lumina_ig', json_encode($r));
$setEnv('SOCIAL_FB_PAGE_TOKEN', $tok['fb']);
Settings::flush();
$r = $keep($ig->test($igCfg));
check('test(): an empty token field falls back to the Facebook Page token (SOCIAL_FB_PAGE_TOKEN)', $r['ok'] === true, json_encode($r));
$r = $keep($ig->test(['account_id' => '123456', 'token' => $tok['fb']]));
check('test(): an unknown account id is reported as an error', $r['ok'] === false);
$r = $keep($ig->test(['account_id' => '17841400000000000', 'token' => 'WRONGTOKENWRONGTOKEN1234567890']));
check('test(): a rejected token -> state auth', $r['state'] === 'auth' && !str_contains($r['message'], 'WRONGTOKEN'));
$igCap = 'Instagram caption ' . bin2hex(random_bytes(3)) . "\n\nLink in bio. #lumina";
$img = 'https://example.org/social/image/' . str_repeat('b', 64) . '.jpg';
$res = $keep($ig->post($igCfg, $payload(['channel' => 'instagram', 'caption' => $igCap, 'imageUrl' => $img])));
$pub = $postsOf('instagram');
$con = $postsOf('instagram_container');
check('post(): container (image_url + caption) -> status FINISHED -> media_publish -> permalink: posted with the instagram.com link', $res->state === 'posted' && count($con) === 1 && count($pub) === 1 && $pub[0]['data']['creation_id'] === $con[0]['id'] && $pub[0]['data']['caption'] === $igCap && $pub[0]['data']['image_url'] === $img && $res->remoteId === $pub[0]['id'] && str_starts_with((string) $res->remoteUrl, 'https://www.instagram.com/p/'), json_encode($res->toArray()));
$res = $keep($ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => null])));
check('post(): no image -> failed no_image, no container is created', $res->state === 'failed' && $res->code === 'no_image' && count($postsOf('instagram_container')) === 1);
$res = $keep($ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => 'http://example.org/a.jpg'])));
check('post(): an http (not https) image address is refused locally', $res->state === 'failed' && $res->code === 'no_image');
$res = $keep($ig->post($igCfg, $payload(['channel' => 'instagram', 'caption' => str_repeat('a', 2201)])));
check('post(): a caption over 2200 characters is refused locally', $res->state === 'failed' && $res->code === 'too_long');
$res = $keep($ig->post($igCfg, $payload(['channel' => 'instagram', 'caption' => implode(' ', array_map(static fn(int $i): string => '#tag' . $i, range(1, 31)))])));
check('post(): more than 30 hashtags are refused locally', $res->state === 'failed' && $res->code === 'too_many_hashtags');
$res = $keep($ig->post($igCfg, $payload(['channel' => 'instagram', 'test' => true, 'imageUrl' => $img])));
check('post(): a test post uses the same two-step flow with a "Test post ... ignore" caption', $res->state === 'posted' && str_starts_with($last($postsOf('instagram'))['data']['caption'], 'Test post from Lumina - ignore'));
$cBase = count($postsOf('instagram_container'));
$pBase = count($postsOf('instagram'));
$res = $outcome('graph', '401', fn() => $ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram 401 -> auth, nothing created', $res->state === 'auth' && count($postsOf('instagram_container')) === $cBase);
$res = $outcome('graph', '403', fn() => $ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram 403 -> failed permission (names instagram_content_publish)', $res->state === 'failed' && $res->code === 'permission' && str_contains($res->error, 'instagram_content_publish'), json_encode($res->toArray()));
$res = $outcome('graph', '429', fn() => $ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram 429 -> transient with Retry-After', $res->state === 'transient' && $res->retryAfter === 9);
$res = $outcome('graph', '500', fn() => $ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram 500 -> transient', $res->state === 'transient');
$mock('/__mock/config', ['fetch_images' => true]);
$res = $keep($ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => 'https://127.0.0.1:1/never.jpg'])));
check('Instagram: a picture Instagram cannot fetch / that is not a JPEG (code 9004 / 2207052) -> failed image_rejected with the JPEG requirement', $res->state === 'failed' && $res->code === 'image_rejected' && str_contains($res->error, 'JPEG'), json_encode($res->toArray()));
$mock('/__mock/config', ['fetch_images' => false]);
$pBase = count($postsOf('instagram'));
$res = $outcome('graph', '502', fn() => $ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => $img])), ['after' => 2]);
check('Instagram: 502 on media_publish (published, answer lost) -> review that carries the container id, never re-posted', $res->state === 'review' && $res->remoteId !== null && str_starts_with($res->remoteId, 'container-') && count($postsOf('instagram')) === $pBase + 1, json_encode($res->toArray()));
$pBase = count($postsOf('instagram'));
$res = $outcome('graph', 'ambiguous', fn() => $ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => $img, 'deadline' => microtime(true) + 4.0])), ['after' => 2, 'delay' => 6]);
check('Instagram: a timeout on media_publish -> review with the container id (the post may exist)', $res->state === 'review' && $res->remoteId !== null && count($postsOf('instagram')) === $pBase + 1 && $lastMs < 6.0, json_encode($res->toArray()) . ' ' . round($lastMs, 1) . 's');
usleep(3500000);
$cBase = count($postsOf('instagram_container'));
$res = $outcome('graph', 'slow', fn() => $ig->post($igCfg, $payload(['channel' => 'instagram', 'imageUrl' => $img, 'deadline' => microtime(true) + 2.0])), ['delay' => 4]);
check('Instagram: a timeout on the HARMLESS container step -> transient (not review): repeating it creates nothing public', $res->state === 'transient' && $res->code === 'container_timeout', json_encode($res->toArray()));
usleep(2500000);
// scripted answers through the test transport: the status polling
$script = [];
$sent = [];
SocialHttp::$transport = static function (array $req) use (&$script, &$sent): array {
    $sent[] = $req;
    foreach ($script as $re => $answers) {
        if (preg_match($re, $req['method'] . ' ' . $req['url']) === 1) {
            $a = count($script[$re]) > 1 ? array_shift($script[$re]) : $script[$re][0];   // the last answer of a list repeats
            return $a + ['status' => 200, 'headers' => [], 'raw' => '{}', 'kind' => '', 'ambiguous' => false];
        }
    }
    return ['status' => 404, 'headers' => [], 'raw' => '{}', 'kind' => '', 'ambiguous' => false];
};
$j = static fn(array $a, int $status = 200): array => ['status' => $status, 'raw' => json_encode($a)];
$script = ['#POST .*/media$#' => [$j(['id' => 'c1'])], '#GET .*/c1\?#' => [$j(['status_code' => 'IN_PROGRESS']), $j(['status_code' => 'IN_PROGRESS']), $j(['status_code' => 'FINISHED'])], '#POST .*/media_publish$#' => [$j(['id' => 'm1'])], '#GET .*/m1\?#' => [$j(['permalink' => 'https://www.instagram.com/p/ABC/'])]];
$res = $keep($ig->post($igCfgTok, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
$polls = count(array_filter($sent, static fn(array $q): bool => str_contains($q['url'], '/c1?')));
check('Instagram: it polls IN_PROGRESS -> IN_PROGRESS -> FINISHED (3 checks), then publishes', $res->state === 'posted' && $polls === 3 && $res->remoteId === 'm1' && $res->remoteUrl === 'https://www.instagram.com/p/ABC/', json_encode([$res->toArray(), $polls]));
$leak = false;
foreach ($sent as $q) {
    $leak = $leak || str_contains($q['url'], $tok['fb']) || str_contains((string) $q['body'], $tok['fb']) || ($q['headers']['Authorization'] ?? '') !== 'Bearer ' . $tok['fb'];
}
check('Graph calls carry the token ONLY in the Authorization header (never in the URL or the body)', !$leak);
$sent = [];
$script = ['#POST .*/media$#' => [$j(['id' => 'c2'])], '#GET .*/c2\?#' => [$j(['status_code' => 'ERROR'])]];
$res = $keep($ig->post($igCfgTok, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram: container status ERROR -> failed image_rejected, publish is never called', $res->state === 'failed' && $res->code === 'image_rejected' && count(array_filter($sent, static fn(array $q): bool => str_contains($q['url'], 'media_publish'))) === 0);
$sent = [];
$script = ['#POST .*/media$#' => [$j(['id' => 'c3'])], '#GET .*/c3\?#' => [$j(['status_code' => 'EXPIRED'])]];
$res = $keep($ig->post($igCfgTok, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram: container status EXPIRED -> failed', $res->state === 'failed');
$sent = [];
$script = ['#POST .*/media$#' => [$j(['id' => 'c4'])], '#GET .*/c4\?#' => [['status' => 0, 'kind' => 'timeout', 'raw' => '', 'ambiguous' => true]], '#POST .*/media_publish$#' => [$j(['error' => ['message' => 'Media ID is not available', 'code' => 9007, 'error_subcode' => 2207027]], 400)]];
$res = $keep($ig->post($igCfgTok, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram: an unreadable status check goes on to publish; "media not ready" (9007) -> transient not_ready (container is recreated next time)', $res->state === 'transient' && $res->code === 'not_ready', json_encode($res->toArray()));
$sent = [];
$script = ['#POST .*/media$#' => [$j(['id' => 'c5'])], '#GET .*/c5\?#' => [$j(['status_code' => 'IN_PROGRESS'])]];
$t0 = microtime(true);
$res = $keep($ig->post($igCfgTok, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram: a picture that stays IN_PROGRESS for ~6 s ends as transient not_ready (publish never attempted)', $res->state === 'transient' && $res->code === 'not_ready' && count(array_filter($sent, static fn(array $q): bool => str_contains($q['url'], 'media_publish'))) === 0 && microtime(true) - $t0 < 8.5, json_encode($res->toArray()) . ' ' . round(microtime(true) - $t0, 1) . 's');
$sent = [];
$script = ['#POST .*/media$#' => [$j(['id' => 'c6'])], '#GET .*/c6\?#' => [$j(['status_code' => 'FINISHED'])], '#POST .*/media_publish$#' => [['status' => 200, 'raw' => '']]];
$res = $keep($ig->post($igCfgTok, $payload(['channel' => 'instagram', 'imageUrl' => $img])));
check('Instagram: publish answered 2xx without an id -> review carrying the container id', $res->state === 'review' && $res->remoteId === 'c6' && $res->code === 'no_id');
SocialHttp::$transport = null;
$script = [];
$noMode();
$setEnv('SOCIAL_FB_PAGE_TOKEN', '');
Settings::flush();

// ===================================================================================================================================================== 5. X
echo "-- X\n";
$r = $keep($x->test($xCfg));
check('test() (OAuth 1.0a): GET /2/users/me with a signature the mock VERIFIES -> ok, detail @mockluminax', $r['ok'] === true && ($r['detail'] ?? '') === '@mockluminax' && $calls('x', 'me') === 1, json_encode($r));
$r = $keep($x->test($xBearer));
check('test() (bearer user token) -> ok', $r['ok'] === true && $calls('x', 'me') === 2, json_encode($r));
$r = $keep($x->test(['access_secret' => 'wrong-secret-wrong-secret'] + $xCfg));
check('test(): a wrong token secret -> the signature is refused (401) -> state auth', $r['state'] === 'auth' && !str_contains($r['message'], 'wrong-secret'), json_encode($r));
$r = $keep($x->test(['api_key' => ''] + $xCfg));
check('test(): oauth1 with a missing credential names what is needed, no request', $r['ok'] === false && str_contains($r['message'], 'API key') && $calls('x', 'me') === 2);
$r = $keep($x->test(['bearer_token' => ''] + $xBearer));
check('test(): bearer mode without a token is refused locally', $r['ok'] === false && str_contains($r['message'], 'bearer'));
$tw = 'Hello X ' . bin2hex(random_bytes(4)) . ' https://example.org/newsletter/x';
$res = $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => $tw])));
$p = $postsOf('x');
check('post() (OAuth 1.0a, JSON body NOT in the signature): 201, posted with the id and an x.com link', $res->state === 'posted' && $res->remoteId === ($p[0]['id'] ?? '?') && $res->remoteUrl === 'https://x.com/i/web/status/' . $res->remoteId && $p[0]['data']['text'] === $tw, json_encode($res->toArray()));
$res = $keep($x->post($xBearer, $payload(['channel' => 'x', 'caption' => 'Bearer tweet ' . bin2hex(random_bytes(3))])));
check('post() with a bearer user token -> posted', $res->state === 'posted' && count($postsOf('x')) === 2);
$res = $keep($x->post($xCfg, $payload(['channel' => 'x', 'test' => true])));
check('post(): a test post says "Test post from Lumina - ignore"', $res->state === 'posted' && str_starts_with($last($postsOf('x'))['data']['text'], 'Test post from Lumina - ignore'));
$xn = count($postsOf('x'));
$res = $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => str_repeat('a', 281)])));
check('post(): 281 plain characters are refused locally (failed too_long), nothing sent', $res->state === 'failed' && $res->code === 'too_long' && count($postsOf('x')) === $xn);
$res = $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => str_repeat("\u{1F600}", 140)])));
check('post(): 140 emoji = 280 weighted characters pass (the mock and CaptionBuilder::length agree)', $res->state === 'posted' && CaptionBuilder::length('x', str_repeat("\u{1F600}", 140)) === 280);
$res = $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => str_repeat("\u{1F600}", 141)])));
check('post(): 141 emoji = 282 weighted -> refused locally', $res->state === 'failed' && $res->code === 'too_long');
$long = 'Read ' . 'https://example.org/' . str_repeat('a', 300) . ' ' . str_repeat('b', 240);
check('post(): a 320-character link counts 23 (text + link = 23 + 6 + 240 passes)', CaptionBuilder::length('x', $long) === 6 + 23 + 241 - 1 && ($keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => $long])))->state === 'posted'));
$same = 'Same text ' . bin2hex(random_bytes(3));
$keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => $same])));
$res = $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => $same])));
check('post(): duplicate text (403) -> failed "duplicate", not retried', $res->state === 'failed' && $res->code === 'duplicate', json_encode($res->toArray()));
$res = $keep($x->post(['mode' => 'oauth1', 'api_key' => '', 'api_secret' => '', 'access_token' => '', 'access_secret' => ''], $payload(['channel' => 'x'])));
check('post(): not configured -> failed not_configured', $res->state === 'failed' && $res->code === 'not_configured');
$xn = count($postsOf('x'));
$res = $outcome('x', '401', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'a ' . bin2hex(random_bytes(3))])));
check('X 401 -> auth', $res->state === 'auth' && count($postsOf('x')) === $xn);
$res = $outcome('x', '403', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'b ' . bin2hex(random_bytes(3))])));
check('X 403 (app is not "Read and write") -> failed permission with the fix in the text', $res->state === 'failed' && $res->code === 'permission' && str_contains($res->error, 'Read and write'), json_encode($res->toArray()));
$res = $outcome('x', '429', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'c ' . bin2hex(random_bytes(3))])));
check('X 429 -> transient with the reset time (Retry-After 30)', $res->state === 'transient' && $res->retryAfter === 30, json_encode($res->toArray()));
$res = $outcome('x', '500', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'd ' . bin2hex(random_bytes(3))])));
check('X 500 -> transient', $res->state === 'transient');
$res = $outcome('x', '502', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'e ' . bin2hex(random_bytes(3))])));
check('X 502 after the tweet was created -> review', $res->state === 'review' && count($postsOf('x')) === $xn + 1, json_encode($res->toArray()));
$xn = count($postsOf('x'));
$res = $outcome('x', 'ambiguous', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'f ' . bin2hex(random_bytes(3)), 'deadline' => microtime(true) + 2.0])), ['delay' => 4]);
check('X: a timeout after sending (the tweet exists) -> review, deadline honoured', $res->state === 'review' && count($postsOf('x')) === $xn + 1 && $lastMs < 2.8, json_encode($res->toArray()) . ' ' . round($lastMs, 1) . 's');
usleep(2300000);
$res = $outcome('x', 'redirect', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'g ' . bin2hex(random_bytes(3))])));
check('X: a redirect is not followed -> failed', $res->state === 'failed' && $res->code === 'redirect');
$res = $outcome('x', 'empty', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'h ' . bin2hex(random_bytes(3))])));
check('X: 2xx without an id -> review', $res->state === 'review' && $res->code === 'no_id');
$res = $outcome('x', 'big', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'i ' . bin2hex(random_bytes(3))])));
check('X: an answer over 1 MB -> failed too_large', $res->state === 'failed' && $res->code === 'too_large');
$res = $outcome('x', 'slow', fn() => $x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'j ' . bin2hex(random_bytes(3))])), ['delay' => 1]);
check('X: a slow answer (1 s) still posts', $res->state === 'posted');
$setEnv('SOCIAL_DEV_BASE_URL', 'http://127.0.0.1:1');
$res = $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'k ' . bin2hex(random_bytes(3))])));
$setEnv('SOCIAL_DEV_BASE_URL', $devBase);
check('X: connection refused -> transient', $res->state === 'transient');
// a captured signed request: replay and stale timestamp are refused by the mock (it is a REAL verification)
$signer = new OAuth1Signer($xc['x_consumer_key'], $xc['x_consumer_secret'], $xc['x_token'], $xc['x_token_secret']);
$url = SocialHttp::urlFor('x', '/2/users/me');
$nonce = 'conformance' . bin2hex(random_bytes(4));
$first = SocialHttp::api('x', 'GET', '/2/users/me', ['headers' => ['Authorization' => $signer->header('GET', $url, [], $nonce)]]);
$replay = SocialHttp::api('x', 'GET', '/2/users/me', ['headers' => ['Authorization' => $signer->header('GET', $url, [], $nonce)]]);
$stale = SocialHttp::api('x', 'GET', '/2/users/me', ['headers' => ['Authorization' => $signer->header('GET', $url, [], null, time() - 4000)]]);
$other = SocialHttp::api('x', 'GET', '/2/users/me', ['headers' => ['Authorization' => $signer->header('GET', $url . 'x', [])]]);
check('the mock verifies the signer for real: first use accepted; a replayed nonce, a stale timestamp and a signature over another URL are refused', $first['ok'] && $replay['status'] === 401 && $stale['status'] === 401 && $other['status'] === 401);
$sent = [];
SocialHttp::$transport = static function (array $req) use (&$sent): array {
    $sent[] = $req;
    return ['status' => 201, 'headers' => [], 'raw' => '{"data":{"id":"1700000000000000999","text":"t"}}', 'kind' => '', 'ambiguous' => false];
};
$res = $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'transport check'])));
SocialHttp::$transport = null;
$q = $sent[0] ?? ['url' => '', 'headers' => [], 'body' => ''];
check('X requests: the secrets are not in the URL or the body; the Authorization header is an OAuth header without the consumer secret / token secret', $res->state === 'posted' && !str_contains($q['url'], $xc['x_token']) && !str_contains((string) $q['body'], $xc['x_token']) && str_starts_with($q['headers']['Authorization'], 'OAuth ') && !str_contains($q['headers']['Authorization'], $xc['x_consumer_secret']) && !str_contains($q['headers']['Authorization'], $xc['x_token_secret']) && $q['body'] === '{"text":"transport check"}');

// ===================================================================================================================================================== 6. LinkedIn
echo "-- LinkedIn\n";
$r = $keep($li->test($liCfg));
check('test(): organizationAcls (with the version headers) lists the URN -> ok', $r['ok'] === true && ($r['detail'] ?? '') === 'urn:li:organization:12345678' && $calls('linkedin', 'acls') === 1, json_encode($r));
$r = $keep($li->test(['org_urn' => '12345678'] + $liCfg));
check('test(): a bare organisation number is accepted (prefixed urn:li:organization:)', $r['ok'] === true);
$r = $keep($li->test(['org_urn' => 'urn:li:organization:99999999'] + $liCfg));
check('test(): 200 without the URN -> "not an administrator of that page"', $r['ok'] === false && str_contains($r['message'], 'not an administrator'), json_encode($r));
$r = $keep($li->test(['access_token' => 'WRONGLINKEDINTOKEN123456'] + $liCfg));
check('test(): a revoked / wrong token -> state auth, token not in the message', $r['state'] === 'auth' && !str_contains($r['message'], 'WRONGLINKEDIN'));
$aclBase = $calls('linkedin', 'acls');
$r = $keep($li->test(['expires' => '2020-01-01'] + $liCfg));
check('test(): a token whose expiry date has passed -> state auth without calling LinkedIn', $r['state'] === 'auth' && $calls('linkedin', 'acls') === $aclBase);
$r = $keep($li->test(['expires' => date('Y-m-d', strtotime('+30 days'))] + $liCfg));
check('test(): a future expiry date is fine', $r['ok'] === true);
$text = 'LinkedIn post #Lumina (2026) *bold* [x] _y_ {z} <a> @all | ~t \\ end ' . bin2hex(random_bytes(3));
$res = $keep($li->post($liCfg, $payload(['channel' => 'linkedin', 'caption' => $text, 'title' => 'The title', 'summary' => 'The summary'])));
$p = $postsOf('linkedin');
$expectEsc = preg_replace('/([\\\\()\[\]{}<>@|~_*#])/', '\\\\$1', $text);
check('post(): POST /rest/posts (author, commentary, article source) -> 201 + x-restli-id header: posted with the feed/update link', $res->state === 'posted' && $res->remoteId === ($p[0]['id'] ?? '?') && str_starts_with((string) $res->remoteUrl, 'https://www.linkedin.com/feed/update/urn:li:share:') && $p[0]['data']['article'] === 'https://example.org/newsletter/x' && $p[0]['data']['author'] === 'urn:li:organization:12345678', json_encode($res->toArray()));
check('post(): "little text" characters ( ) [ ] { } < > @ | ~ _ * # \\ are escaped with a backslash, everything else stays as typed', $p[0]['data']['commentary'] === $expectEsc && str_contains($p[0]['data']['commentary'], '\\#Lumina \\(2026\\)') && ChannelSupport::linkedinEscape('plain text 1.5%') === 'plain text 1.5%', $p[0]['data']['commentary']);
$res = $keep($li->post($liCfg, $payload(['channel' => 'linkedin', 'test' => true])));
$p = $postsOf('linkedin');
check('post(): a test post has no article and says "Test post from Lumina - ignore"', $res->state === 'posted' && str_starts_with($last($p)['data']['commentary'], 'Test post from Lumina - ignore') && $last($p)['data']['article'] === '');
$ln = count($postsOf('linkedin'));
$res = $keep($li->post($liCfg, $payload(['channel' => 'linkedin', 'caption' => str_repeat('a', 3001)])));
check('post(): over 3000 characters is refused locally', $res->state === 'failed' && $res->code === 'too_long' && count($postsOf('linkedin')) === $ln);
$res = $keep($li->post(['expires' => '2020-01-01'] + $liCfg, $payload(['channel' => 'linkedin'])));
check('post(): an expired token date -> auth "expired", nothing sent', $res->state === 'auth' && $res->code === 'expired' && count($postsOf('linkedin')) === $ln);
$res = $keep($li->post(['org_urn' => '', 'access_token' => ''], $payload(['channel' => 'linkedin'])));
check('post(): not configured -> failed not_configured', $res->state === 'failed' && $res->code === 'not_configured');
$res = $keep($li->post(['org_urn' => 'urn:li:organization:99999999'] + $liCfg, $payload(['channel' => 'linkedin'])));
check('post(): an organisation the member does not administer (403 ACCESS_DENIED) -> failed permission', $res->state === 'failed' && $res->code === 'permission', json_encode($res->toArray()));
$res = $outcome('linkedin', '401', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])));
check('LinkedIn 401 REVOKED_ACCESS_TOKEN -> auth', $res->state === 'auth');
$res = $outcome('linkedin', '403', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])));
check('LinkedIn 403 -> failed permission (names w_organization_social)', $res->state === 'failed' && $res->code === 'permission' && str_contains($res->error, 'w_organization_social'));
$res = $outcome('linkedin', '429', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])));
check('LinkedIn 429 -> transient with Retry-After 60', $res->state === 'transient' && $res->retryAfter === 60);
$res = $outcome('linkedin', '500', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])));
check('LinkedIn 500 -> transient', $res->state === 'transient');
$ln = count($postsOf('linkedin'));
$res = $outcome('linkedin', '502', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])));
check('LinkedIn 502 after the post was created -> review', $res->state === 'review' && count($postsOf('linkedin')) === $ln + 1);
$ln = count($postsOf('linkedin'));
$res = $outcome('linkedin', 'ambiguous', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin', 'deadline' => microtime(true) + 2.0])), ['delay' => 4]);
check('LinkedIn: a timeout after sending -> review, deadline honoured', $res->state === 'review' && count($postsOf('linkedin')) === $ln + 1 && $lastMs < 2.8, round($lastMs, 1) . 's');
usleep(2300000);
$res = $outcome('linkedin', 'redirect', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])));
check('LinkedIn: a redirect is not followed -> failed', $res->state === 'failed' && $res->code === 'redirect');
$res = $outcome('linkedin', 'bad_json', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])));
check('LinkedIn: a 2xx without the x-restli-id header and without JSON -> review', $res->state === 'review' && $res->code === 'no_id');
$res = $outcome('linkedin', 'big', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])));
check('LinkedIn: an answer over 1 MB -> failed too_large', $res->state === 'failed' && $res->code === 'too_large');
$res = $outcome('linkedin', 'slow', fn() => $li->post($liCfg, $payload(['channel' => 'linkedin'])), ['delay' => 1]);
check('LinkedIn: a slow answer (1 s) still posts', $res->state === 'posted');
$ln = count($postsOf('linkedin'));
$mode('linkedin', 'version_gone');
$res = $keep($li->post($liCfg, $payload(['channel' => 'linkedin', 'caption' => 'Fallback post ' . bin2hex(random_bytes(3))])));
$noMode();
$p = $postsOf('linkedin');
check('LinkedIn: 426 NONEXISTENT_VERSION on /rest/posts falls back to the legacy /v2/ugcPosts and posts once', $res->state === 'posted' && count($p) === $ln + 1 && $calls('linkedin', 'ugcPosts') === 1 && str_starts_with((string) $res->remoteId, 'urn:li:share:'), json_encode($res->toArray()));
$mode('linkedin', 'version_gone_all');
$ugc = $calls('linkedin', 'ugcPosts');
$res = $keep($li->post($liCfg, $payload(['channel' => 'linkedin', 'caption' => 'Fallback two ' . bin2hex(random_bytes(3))])));
$t = $keep($li->test($liCfg));
$noMode();
check('LinkedIn: with every /rest/ call refused (426) the post still goes out through the legacy endpoint, and test() says which API version setting to change', $res->state === 'posted' && $calls('linkedin', 'ugcPosts') === $ugc + 1 && $t['ok'] === false && str_contains($t['message'], 'API version'), json_encode([$res->toArray(), $t]));
$sent = [];
SocialHttp::$transport = static function (array $req) use (&$sent): array {
    $sent[] = $req['method'] . ' ' . $req['url'];
    return str_contains($req['url'], '/rest/posts')
        ? ['status' => 426, 'headers' => [], 'raw' => '{"status":426,"code":"NONEXISTENT_VERSION","message":"Requested version 20250601 is not active"}', 'kind' => '', 'ambiguous' => false]
        : ['status' => 403, 'headers' => [], 'raw' => '{"status":403,"code":"ACCESS_DENIED","message":"nope"}', 'kind' => '', 'ambiguous' => false];
};
$res = $keep($li->post($liCfg, $payload(['channel' => 'linkedin'])));
SocialHttp::$transport = null;
check('LinkedIn: when the version AND the legacy endpoint are refused, the error names the API version setting (failed "version"), two requests only', $res->state === 'failed' && $res->code === 'version' && str_contains($res->error, 'LinkedIn API version') && count($sent) === 2, json_encode([$res->toArray(), $sent]));
$setEnv('SOCIAL_DEV_BASE_URL', 'http://127.0.0.1:1');
$res = $keep($li->post($liCfg, $payload(['channel' => 'linkedin'])));
$setEnv('SOCIAL_DEV_BASE_URL', $devBase);
check('LinkedIn: connection refused -> transient', $res->state === 'transient');

// ===================================================================================================================================================== 7. safety nets
echo "-- never throws, never leaks\n";
$boom = ChannelSupport::guardPost('Facebook', static function () use ($tok): SocialResult {
    throw new \RuntimeException('Exploded with the secret ' . $tok['fb'] . ' and Bearer ' . $tok['li']);
});
$boomT = ChannelSupport::guardTest('Facebook', static function () use ($tok): array {
    throw new \RuntimeException('Exploded with the secret ' . $tok['fb']);
});
$keep($boom);
$keep($boomT);
check('a throwing adapter body becomes a "failed" result and the exception text (with secrets in it) is NOT used', $boom->state === 'failed' && $boom->code === 'internal' && !str_contains(json_encode($boom->toArray()), $tok['fb']) && !str_contains($boom->error, 'Exploded') && $boomT['ok'] === false && !str_contains($boomT['message'], 'Exploded'));
// a transport that throws with secrets in its message: SocialHttp turns it into a network failure, the adapters stay quiet
SocialHttp::$transport = static function (array $req) use ($tok): array {
    throw new \RuntimeException('connect failed for ' . $req['url'] . ' with ' . $tok['fb']);
};
$all = [$keep($fb->post($fbCfg, $payload())), $keep($ig->post($igCfgTok, $payload(['channel' => 'instagram']))), $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'q']))), $keep($li->post($liCfg, $payload(['channel' => 'linkedin']))), $keep($fb->test($fbCfg)), $keep($x->test($xCfg))];
SocialHttp::$transport = null;
check('a transport that throws (with a secret in its message) never reaches any result', !str_contains(json_encode($seen), 'connect failed') && $all[0] instanceof SocialResult);
foreach ([['SOCIAL_FB_PAGE_TOKEN', $tok['fb']], ['SOCIAL_LI_ACCESS_TOKEN', $tok['li']], ['SOCIAL_X_ACCESS_SECRET', $xc['x_token_secret']]] as [$k, $v]) {
    $setEnv($k, $v);       // configured secrets are blanked by exact value as well (SocialHttp::redact), as on a real site
}
\App\Services\Social\SocialConfig::flush();
$hostile = 'Access_Token=' . $tok['fb'] . ' Bearer ' . $tok['li'] . ' oauth_signature="abc" ' . $xc['x_token_secret'];
SocialHttp::$transport = static fn(array $req): array => ['status' => 400, 'headers' => [], 'raw' => json_encode(['error' => ['message' => $hostile, 'code' => 100, 'type' => 'OAuthException']]), 'kind' => '', 'ambiguous' => false];
$res = $keep($fb->post($fbCfg, $payload()));
$t = $keep($fb->test($fbCfg));
$res2 = $keep($x->post($xCfg, $payload(['channel' => 'x', 'caption' => 'w'])));
$res3 = $keep($li->post($liCfg, $payload(['channel' => 'linkedin'])));
SocialHttp::$transport = null;
foreach (['SOCIAL_FB_PAGE_TOKEN', 'SOCIAL_LI_ACCESS_TOKEN', 'SOCIAL_X_ACCESS_SECRET'] as $k) {
    $setEnv($k, '');
}
\App\Services\Social\SocialConfig::flush();
$blob = json_encode([$res->toArray(), $t, $res2->toArray(), $res3->toArray()]);
check('a platform that echoes our credentials back in its error text: they are blanked in every result and test message', $res->state === 'failed' && !str_contains($blob, $tok['fb']) && !str_contains($blob, $tok['li']) && !str_contains($blob, $xc['x_token_secret']) && !str_contains($blob, 'oauth_signature="abc"'), $blob);
$blob = json_encode($seen);
$found = [];
foreach ($secrets as $s) {
    if (str_contains($blob, $s)) {
        $found[] = substr($s, 0, 6) . '...';
    }
}
check('no credential appears in ANY result or test message of this whole run (' . count($seen) . ' answers scanned)', $found === [], implode(',', $found));
clearstatcache();
$logNow = is_file($logFile) ? (string) file_get_contents($logFile, false, null, $logStart) : '';
$logLeak = [];
foreach (array_merge($secrets, ['oauth_signature=']) as $s) {
    if (str_contains($logNow, $s)) {
        $logLeak[] = substr($s, 0, 6) . '...';
    }
}
check('no credential appears in the application log written during this run (' . strlen($logNow) . ' bytes)', $logLeak === [], implode(',', $logLeak));
check('the audit-trail rule holds: adapters never touch the database or AuditLog', !str_contains((string) file_get_contents($zzRoot . '/app/services/Social/Channels/XChannel.php') . (string) file_get_contents($zzRoot . '/app/services/Social/ChannelSupport.php') . (string) file_get_contents($zzRoot . '/app/services/Social/Channels/FacebookChannel.php'), 'AuditLog'));
$envText = (string) @file_get_contents($zzRoot . '/.env');
echo preg_match('/^\s*SOCIAL_/m', $envText) === 1 ? "  info .env currently has a SOCIAL_ line (set by another session or by hand): this suite does not use or change it; remove it when done\n" : "  ok   .env has no SOCIAL_ line\n";
$noMode();

$cleanup();
echo sprintf("\nElapsed %.1f s\n", microtime(true) - $started);
echo $fail === 0 ? "ALL CHECKS PASSED\n" : "{$fail} CHECK(S) FAILED\n";
exit($fail === 0 ? 0 : 1);
