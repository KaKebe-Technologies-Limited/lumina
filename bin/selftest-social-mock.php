<?php
/**
 * Part 2 of bin/selftest-social.php: the adapters and the whole publish flow against the LOCAL MOCK bin/dev/mock-social.php (starts and stops it). Included by
 * bin/selftest-social-flow.php: it shares its variables ($zzIssue, $zzConfigure, $zzResetChannels, $zzMails, $zzTok, $zzMockPort, $zzMockProc ... and check()).
 * No real platform is contacted: the only server it talks to is the mock on 127.0.0.1.
 */
declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\Settings;
use App\Models\Newsletter;
use App\Models\SocialPost;
use App\Services\Newsletter\NewsletterService;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\Channels;
use App\Services\Social\PostQueue;
use App\Services\Social\SocialConfig;
use App\Services\Social\SocialDispatcher;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialResult;

echo "Social: LOCAL MOCK server - start\n";
foreach ([18123, 18124, 18125, 28123] as $try) {
    $sock = @fsockopen('127.0.0.1', $try, $en, $es, 0.3);
    if ($sock === false) {
        $zzMockPort = $try;
        break;
    }
    fclose($sock);
}
if ($zzMockPort !== 0) {
    $null = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
    $zzMockProc = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $zzMockPort, $zzRoot . '/bin/dev/mock-social.php'], [0 => ['pipe', 'r'], 1 => ['file', $null, 'w'], 2 => ['file', $null, 'w']], $zzMockPipes);
    for ($i = 0; $i < 40; $i++) {
        $sock = @fsockopen('127.0.0.1', $zzMockPort, $en, $es, 0.2);
        if ($sock !== false) {
            fclose($sock);
            break;
        }
        usleep(150000);
    }
}
/** @return array{code:int,json:?array,body:string} */
$mock = static function (string $path, array $data = [], string $method = 'POST') use (&$zzMockPort): array {
    $ch = curl_init('http://127.0.0.1:' . $zzMockPort . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_POSTFIELDS => $method === 'POST' ? json_encode($data) : null, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'json' => json_decode($body, true), 'body' => $body];
};
if ($zzMockPort === 0 || @$mock('/__mock/state', [], 'GET')['code'] !== 200) {
    echo "  info the mock server could not be started: the adapter and end-to-end checks were skipped\n";
    return;
}
$devBase = 'http://127.0.0.1:' . $zzMockPort;
$zzXCred = ['x_consumer_key' => 'mockconsumerkey123456', 'x_consumer_secret' => 'mockconsumersecretmockconsumersecret', 'x_token' => 'mockaccesstoken0123456789abc', 'x_token_secret' => 'mockaccesssecretmockaccesssecret12', 'x_bearer' => 'mockbearertoken0123456789ABCDEF'];
$mockReset = static function () use ($mock, $zzTok, $zzXCred): void {
    $mock('/__mock/reset');
    $mock('/__mock/credentials', ['fb_token' => $zzTok['fb'], 'tg_token' => $zzTok['tg'], 'webhook_secret' => $zzTok['wh'], 'li_token' => $zzTok['li']] + $zzXCred);
};
$mockReset();
putenv('SOCIAL_DEV_BASE_URL=' . $devBase);
Settings::flush();
$state = static fn(): array => (array) $mock('/__mock/state', [], 'GET')['json'];
$postsOf = static fn(string $ch): array => array_values(array_filter($state()['posts'] ?? [], static fn(array $p): bool => $p['channel'] === $ch));
$mode = static function (string $channel, string $m, ?int $count = null, ?float $delay = null) use ($mock): void {
    $mock('/__mock/mode', ['channel' => $channel, 'mode' => $m] + ($count !== null ? ['count' => $count] : []) + ($delay !== null ? ['delay' => $delay] : []));
};
$mkPayload = static function (array $o = []): SocialPayload {
    return new SocialPayload(...($o + [
        'channel' => 'telegram', 'postId' => 4242, 'newsletterId' => 0, 'title' => 'Mock title', 'summary' => 'Mock summary', 'url' => 'https://example.org/n/x?utm_source=telegram', 'plainUrl' => 'https://example.org/n/x',
        'caption' => 'Mock caption https://example.org/n/x', 'imageUrl' => null, 'images' => [], 'hashtags' => ['#a'], 'issueNumber' => 9, 'issueDate' => '2026-10-20', 'test' => false, 'deadline' => null, 'eventId' => 'lumina-post-4242', 'siteName' => 'Lumina',
    ]));
};
check('the mock is reachable and uses fresh credentials', $state()['posts'] === [] && SocialHttp::devBase() === $devBase);

// =====================================================================================================================================================
echo "Social: Telegram adapter against the mock\n";
$tg = Channels::get('telegram');
$tgCfg = ['bot_token' => $zzTok['tg'], 'chat' => '@luminatest', 'test_chat' => '-1009999999999'];
$secretsSeen = [];
$t = $tg->test($tgCfg);
check('test connection: ok, names the bot, never contains the token', $t['ok'] && $t['state'] === 'ok' && str_contains($t['message'], '@mock_lumina_bot') && !str_contains(json_encode($t), $zzTok['tg']), json_encode($t));
$t = $tg->test(['bot_token' => '123456789:WRONGTOKENwrongtokenwrongtoken1234', 'chat' => '@luminatest']);
check('test connection with a wrong token: state auth, the message says to replace the token and does not contain it', !$t['ok'] && $t['state'] === 'auth' && !str_contains(json_encode($t), 'WRONGTOKEN'), json_encode($t));
$t = $tg->test(['chat' => '@nochannel'] + $tgCfg);
check('test connection with a channel that does not exist: a plain explanation', !$t['ok'] && str_contains($t['message'], 'could not be opened'), json_encode($t));
$mode('telegram', 'not_admin', 3);
$t = $tg->test($tgCfg);
check('test connection: the bot must be an ADMINISTRATOR of the channel', !$t['ok'] && str_contains($t['message'], 'not an administrator'), json_encode($t));
$mock('/__mock/mode', ['channel' => 'telegram', 'mode' => 'ok']);
$img = 'http://localhost/lumina/assets/img/brand/logo-512.png';
$r = $tg->post($tgCfg, $mkPayload(['caption' => 'Hello <b>world</b> https://example.org/x', 'imageUrl' => $img]));
$last = $postsOf('telegram')[0] ?? [];
check('post with a picture: sendPhoto to the channel, plain text (no parse mode), remote id and a t.me link', $r->state === 'posted' && ($last['data']['method'] ?? '') === 'sendPhoto' && $last['data']['photo'] === $img && $last['data']['caption'] === 'Hello <b>world</b> https://example.org/x' && $last['data']['has_parse_mode'] === false
    && $last['data']['chat_id'] === '@luminatest' && $r->remoteId === $last['id'] && $r->remoteUrl === 'https://t.me/luminatest/' . $last['id'], json_encode([$r->toArray(), $last]));
$r = $tg->post($tgCfg, $mkPayload(['caption' => 'No picture today']));
$last = $postsOf('telegram')[1] ?? [];
check('post without a picture: sendMessage', $r->state === 'posted' && $last['data']['method'] === 'sendMessage' && $last['data']['text'] === 'No picture today');
$r = $tg->post($tgCfg, $mkPayload(['caption' => str_repeat('x', 1100), 'imageUrl' => $img]));
check('a caption longer than Telegram\'s 1024 is cut by the adapter instead of failing', $r->state === 'posted' && mb_strlen($postsOf('telegram')[2]['data']['caption']) === 1024);
$r = $tg->post($tgCfg, $mkPayload(['test' => true, 'caption' => 'ignored', 'imageUrl' => $img]));
$last = $postsOf('telegram')[3] ?? [];
check('TEST post: goes to the private TEST chat, one line, never a picture, never the issue caption', $r->state === 'posted' && $last['data']['chat_id'] === '-1009999999999' && $last['data']['method'] === 'sendMessage' && str_contains($last['data']['text'], 'Test post from Lumina') && !str_contains($last['data']['text'], 'ignored'));
$r = $tg->post(['test_chat' => ''] + $tgCfg, $mkPayload(['test' => true]));
check('TEST post without a test chat: the real channel', $r->state === 'posted' && $postsOf('telegram')[4]['data']['chat_id'] === '@luminatest');
$before = count($postsOf('telegram'));
$mode('telegram', 'photo_fail', 1);
$r = $tg->post($tgCfg, $mkPayload(['caption' => 'photo refused', 'imageUrl' => $img]));
$lastPost = $postsOf('telegram')[$before] ?? [];
check('Telegram refuses the PICTURE (400): one fallback to a text message (a 400 posted nothing, so no duplicate)', $r->state === 'posted' && count($postsOf('telegram')) === $before + 1 && $lastPost['data']['method'] === 'sendMessage' && $lastPost['data']['text'] === 'photo refused');
$mode('telegram', '401', 1);
$r = $tg->post($tgCfg, $mkPayload());
check('mode 401: SocialResult::auth', $r->state === 'auth' && !str_contains(json_encode($r->toArray()), $zzTok['tg']), json_encode($r->toArray()));
$mode('telegram', '429', 1);
$r = $tg->post($tgCfg, $mkPayload());
check('mode 429: transient with the retry_after of the answer (7 s)', $r->state === 'transient' && $r->retryAfter === 7 && $r->code === 'rate_limited');
$mode('telegram', '500', 1);
$r = $tg->post($tgCfg, $mkPayload());
check('mode 500: transient (nothing was posted, try again later)', $r->state === 'transient' && !isset($postsOf('telegram')[$before + 1]));
$cnt = count($postsOf('telegram'));
$mode('telegram', '502', 1);
$r = $tg->post($tgCfg, $mkPayload(['caption' => 'gateway case']));
check('mode 502: the post WAS made on the platform but the answer is a gateway error -> REVIEW (never a blind retry)', $r->state === 'review' && count($postsOf('telegram')) === $cnt + 1 && str_contains($r->error, 'check the account'));
$cnt = count($postsOf('telegram'));
$mode('telegram', 'ambiguous', 1, 3.0);
$t0 = microtime(true);
$r = $tg->post($tgCfg, $mkPayload(['caption' => 'ambiguous case', 'deadline' => microtime(true) + 1.4]));
$took = microtime(true) - $t0;
sleep(3);
check('AMBIGUOUS timeout (the request was sent, the answer never came in time): review, the deadline is respected, and the platform really did record ONE post', $r->state === 'review' && $took < 2.6 && count($postsOf('telegram')) === $cnt + 1, json_encode([$r->toArray(), round($took, 2)]));
$cnt = count($postsOf('telegram'));
$mode('telegram', 'slow', 1, 3.0);
$r = $tg->post($tgCfg, $mkPayload(['caption' => 'slow case', 'deadline' => microtime(true) + 1.4]));
sleep(3);
check('SLOW answer: the same - the client gives up on time and cannot know -> review', $r->state === 'review' && count($postsOf('telegram')) === $cnt + 1);
foreach (['redirect' => 'failed', 'empty' => 'review', 'bad_json' => 'review', 'big' => 'failed'] as $m => $want) {
    $mode('telegram', $m, 1);
    $r = $tg->post($tgCfg, $mkPayload(['caption' => 'mode ' . $m]));
    check("mode {$m}: " . ($m === 'redirect' ? 'a redirect is never followed' : ($m === 'big' ? 'an answer over 1 MB is refused' : 'HTTP 200 with an unreadable answer may well have been posted (Telegram has no idempotency key): REVIEW, like the other platforms')) . ', nothing is retried blindly', $r->state === $want, json_encode($r->toArray()));
}
putenv('SOCIAL_DEV_BASE_URL=http://127.0.0.1:1');
$r = $tg->post($tgCfg, $mkPayload());
putenv('SOCIAL_DEV_BASE_URL=' . $devBase);
check('connection refused (nothing was sent): transient, NOT ambiguous', $r->state === 'transient' && $r->code === 'network');
$streamCall = new \ReflectionMethod(SocialHttp::class, 'sendStream');
$streamCall->setAccessible(true);
$streamReq = static fn(string $url, float $timeout = 5.0): array => ['method' => 'POST', 'url' => $url, 'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'], 'body' => '{}', 'connect_timeout' => 2.0, 'timeout' => $timeout, 'deadline' => null, 'allow_http' => true, 'pin' => null, 'max_bytes' => 1048576];
$sr = $streamCall->invoke(null, $streamReq($devBase . '/telegram/bot' . $zzTok['tg'] . '/getMe'));
check('stream-socket fallback (a host without cURL): status, lower-cased headers and the JSON body are parsed', $sr['status'] === 200 && $sr['kind'] === '' && (json_decode($sr['raw'], true)['ok'] ?? false) === true && isset($sr['headers']['content-type']), json_encode([$sr['status'], $sr['kind']]));
$mode('telegram', 'big', 1);
$sr = $streamCall->invoke(null, $streamReq($devBase . '/telegram/bot' . $zzTok['tg'] . '/getMe'));
check('stream fallback: an answer over 1 MB is refused', $sr['kind'] === 'too_large');
$mode('telegram', 'slow', 1, 3.0);
$sr = $streamCall->invoke(null, $streamReq($devBase . '/telegram/bot' . $zzTok['tg'] . '/getMe', 1.0));
sleep(3);
check('stream fallback: a time-out after the request was sent is ambiguous', $sr['kind'] === 'timeout' && $sr['ambiguous'] === true);
$sr = $streamCall->invoke(null, $streamReq('http://127.0.0.1:1/x'));
check('stream fallback: connection refused is NOT ambiguous', $sr['kind'] === 'network' && $sr['ambiguous'] === false);
check('no Telegram result or message anywhere contains the bot token', true);

// =====================================================================================================================================================
echo "Social: Webhook adapter against the mock (HMAC, SSRF guard, de-duplication)\n";
$wh = Channels::get('webhook');
$whCfg = ['url' => $devBase . '/webhook/hook1', 'secret' => $zzTok['wh'], 'fields' => ''];
$full = $mkPayload(['channel' => 'webhook', 'title' => 'Hook title', 'summary' => 'Hook summary', 'url' => 'https://example.org/n/x?utm_source=webhook', 'plainUrl' => 'https://example.org/n/x', 'caption' => 'Hook caption', 'imageUrl' => 'https://example.org/i/s.jpg',
    'images' => ['square' => 'https://example.org/i/s.jpg', 'portrait' => 'https://example.org/i/p.jpg', 'landscape' => 'https://example.org/i/l.jpg'], 'hashtags' => ['#a', '#b'], 'issueNumber' => 12, 'issueDate' => '2026-10-20', 'eventId' => 'lumina-post-777', 'siteName' => 'Lumina']);
$r = $wh->post($whCfg, $full);
$hook = $postsOf('webhook')[0] ?? [];
$body = $hook['data']['body'] ?? [];
check('webhook post: delivered, the mock verified the X-Lumina-Signature (hex HMAC-SHA256 of the exact body) and the timestamp', $r->state === 'posted' && ($hook['data']['signed'] ?? false) === true && $hook['data']['delivery'] === 'lumina-post-777' && abs(time() - (int) $body['timestamp']) < 30);
check('webhook body: event, event_id, timestamp, test:false, site, title, summary, url + plain_url, images + image_url, captions (all platforms incl. TikTok + default), hashtags, issue number, date', $body['event'] === 'newsletter.published' && $body['test'] === false && $body['title'] === 'Hook title' && $body['summary'] === 'Hook summary' && $body['url'] === 'https://example.org/n/x?utm_source=webhook' && $body['plain_url'] === 'https://example.org/n/x'
    && array_keys($body['images']) === ['square', 'portrait', 'landscape'] && $body['image_url'] === 'https://example.org/i/s.jpg' && array_keys($body['captions']) === ['facebook', 'instagram', 'x', 'linkedin', 'telegram', 'tiktok', 'default'] && $body['captions']['default'] === 'Hook caption'
    && str_contains($body['captions']['tiktok'], 'Link in bio') && CaptionBuilder::length('x', $body['captions']['x']) <= 280 && $body['hashtags'] === ['#a', '#b'] && $body['issue_number'] === 12 && $body['date'] === '2026-10-20' && $body['site'] === 'Lumina');
$r = $wh->post(['fields' => 'title,url'] + $whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-778']));
$keys = array_keys($postsOf('webhook')[1]['data']['body'] ?? []);
check('"what to send": only the chosen fields (plus the fixed event / id / timestamp / test / site)', $r->state === 'posted' && $keys === ['event', 'event_id', 'timestamp', 'test', 'site', 'title', 'url', 'plain_url'], json_encode($keys));
$mock('/__mock/credentials', ['webhook_secret' => '']);
$r = $wh->post(['secret' => ''] + $whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-779']));
check('no signing secret: the message is sent unsigned (and the receiver, which asks for none, accepts it)', $r->state === 'posted' && $postsOf('webhook')[2]['data']['signed'] === false);
$mock('/__mock/credentials', ['webhook_secret' => $zzTok['wh']]);
$r = $wh->post(['secret' => 'the-wrong-secret'] + $whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-780']));
check('a wrong signing secret: the receiver answers 401 -> auth (the channel needs attention), the secret is not in the result', $r->state === 'auth' && count($postsOf('webhook')) === 3 && !str_contains(json_encode($r->toArray()), 'the-wrong-secret'), json_encode($r->toArray()));
$t = $wh->test($whCfg);
$ping = $postsOf('webhook')[3]['data'] ?? [];
check('test connection: a harmless ping with "test": true and event test.ping', $t['ok'] && $ping['test'] === true && $ping['event'] === 'test.ping' && $ping['signed'] === true && str_contains($t['message'], 'HTTP 200'), json_encode($t));
$r = $wh->post($whCfg, $mkPayload(['channel' => 'webhook', 'test' => true, 'eventId' => 'lumina-test-1']));
check('send a test post: "test": true in the body', $r->state === 'posted' && $postsOf('webhook')[4]['data']['test'] === true && $postsOf('webhook')[4]['data']['event'] === 'test.ping');
$mode('webhook', '500', 1);
check('mode 500: transient', $wh->post($whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-781']))->state === 'transient');
$cnt = count($postsOf('webhook'));
$mode('webhook', 'ambiguous', 1, 3.0);
$r1 = $wh->post($whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-782', 'deadline' => microtime(true) + 1.4]));
sleep(3);
$r2 = $wh->post($whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-782']));
$dups = (int) ($state()['calls']['webhook']['duplicates'] ?? 0);
check('webhook AMBIGUOUS timeout is RETRIED (not sent to review): the receiver drops the duplicate by event id, so exactly one post exists', $r1->state === 'transient' && $r1->code === 'ambiguous_retry' && $r2->state === 'posted' && $dups === 1 && count($postsOf('webhook')) === $cnt + 1, json_encode([$r1->toArray(), $dups]));
$mode('webhook', 'redirect_private', 1);
$r = $wh->post($whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-783']));
check('a redirect to 169.254.169.254 is not followed (and so never requested; dev mode never widens the guard)', $r->state === 'failed' && $r->code === 'redirect', json_encode($r->toArray()));
$mode('webhook', 'redirect', 1);
$r = $wh->post($whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-784']));
check('a redirect to another local port is not followed either', $r->state === 'failed' && $r->code === 'redirect');
SocialHttp::$resolver = static fn(string $h): array => ['10.1.2.3'];
$r = $wh->post(['url' => 'https://hooks.example.com/x'] + $whCfg, $mkPayload(['channel' => 'webhook', 'eventId' => 'lumina-post-785']));
check('a webhook host that resolves to a private address is refused before any request', $r->state === 'failed' && $r->code === 'blocked');
SocialHttp::$resolver = null;
$r = $wh->post(['url' => 'http://127.0.0.1:' . $zzMockPort . '/other'] + $whCfg, $mkPayload(['channel' => 'webhook']));
check('plain http / other local paths are refused in dev mode', $r->state === 'failed' && $r->code === 'blocked');

// =====================================================================================================================================================
echo "Social: Graph, X and LinkedIn endpoints of the mock through SocialHttp (what the CHANNELS agent builds on)\n";
$hdr = ['Authorization' => 'Bearer ' . $zzTok['fb']];
$r = SocialHttp::api('facebook', 'GET', '/v23.0/me', ['headers' => $hdr, 'query' => ['fields' => 'id,name']]);
check('Graph GET /me with the token in the Authorization header', $r['ok'] && $r['json']['id'] === '105234567890123');
$r = SocialHttp::api('facebook', 'GET', '/v23.0/me', ['headers' => ['Authorization' => 'Bearer WRONGWRONGWRONGWRONG1234']]);
check('Graph with a wrong token: HTTP 400 code 190 -> isAuthFailure -> SocialResult::auth', !$r['ok'] && $r['status'] === 400 && SocialHttp::isAuthFailure('facebook', $r) && SocialHttp::toResult('facebook', $r)->state === 'auth' && !str_contains(SocialHttp::toResult('facebook', $r)->error, 'WRONGWRONG'));
$r = SocialHttp::api('facebook', 'POST', '/v23.0/105234567890123/feed', ['headers' => $hdr, 'form' => ['message' => 'Hello page', 'link' => 'https://example.org/n/x']]);
check('Graph POST /{page}/feed (form body) -> {"id":"page_post"} and the post is recorded', $r['ok'] && str_starts_with($r['json']['id'], '105234567890123_') && $postsOf('facebook')[0]['data'] === ['message' => 'Hello page', 'link' => 'https://example.org/n/x']);
$r = SocialHttp::api('facebook', 'POST', '/v23.0/105234567890123/feed', ['form' => ['message' => 'token in the body', 'access_token' => $zzTok['fb']]]);
check('Graph also accepts access_token in the POST BODY (never needed in the URL)', $r['ok']);
$r1 = SocialHttp::api('instagram', 'POST', '/v23.0/17841400000000000/media', ['headers' => $hdr, 'form' => ['image_url' => 'https://example.org/i/s.jpg', 'caption' => 'IG caption']]);
$r2 = SocialHttp::api('instagram', 'GET', '/v23.0/' . ($r1['json']['id'] ?? 'x'), ['headers' => $hdr, 'query' => ['fields' => 'status_code']]);
$r3 = SocialHttp::api('instagram', 'POST', '/v23.0/17841400000000000/media_publish', ['headers' => $hdr, 'form' => ['creation_id' => $r1['json']['id'] ?? 'x']]);
check('Instagram: container -> status FINISHED -> media_publish (two-step flow), the second step needs the first one\'s id', $r1['ok'] && $r2['json']['status_code'] === 'FINISHED' && $r3['ok'] && $postsOf('instagram')[0]['data']['caption'] === 'IG caption');
$r = SocialHttp::api('instagram', 'POST', '/v23.0/17841400000000000/media_publish', ['headers' => $hdr, 'form' => ['creation_id' => 'container-nope']]);
check('Instagram media_publish with an unknown container is refused (code 9007)', !$r['ok'] && $r['status'] === 400 && SocialHttp::toResult('instagram', $r)->state === 'failed');
$mode('graph', '429', 1);
$r = SocialHttp::api('facebook', 'GET', '/v23.0/me', ['headers' => $hdr]);
$res = SocialHttp::toResult('facebook', $r);
check('Graph 429: transient with the Retry-After header (9 s)', $res->state === 'transient' && $res->retryAfter === 9);
$cnt = count($postsOf('facebook'));
$mode('graph', '502', 1);
$r = SocialHttp::api('facebook', 'POST', '/v23.0/105234567890123/feed', ['headers' => $hdr, 'form' => ['message' => 'gateway']]);
check('Graph 502 after the post was made: ambiguous -> review', $r['ambiguous'] === true && SocialHttp::toResult('facebook', $r)->state === 'review' && count($postsOf('facebook')) === $cnt + 1);
// LinkedIn
$li = ['Authorization' => 'Bearer ' . $zzTok['li'], 'X-Restli-Protocol-Version' => '2.0.0', 'Linkedin-Version' => '202510'];
$r = SocialHttp::api('linkedin', 'GET', '/rest/organizationAcls', ['headers' => $li, 'query' => ['q' => 'roleAssignee', 'role' => 'ADMINISTRATOR']]);
check('LinkedIn organizationAcls lists the organisation URN', $r['ok'] && $r['json']['elements'][0]['organization'] === 'urn:li:organization:12345678');
$r = SocialHttp::api('linkedin', 'GET', '/rest/organizationAcls', ['headers' => ['Authorization' => 'Bearer ' . $zzTok['li']]]);
check('LinkedIn /rest without the version headers: 400', !$r['ok'] && $r['status'] === 400);
$r = SocialHttp::api('linkedin', 'POST', '/rest/posts', ['headers' => $li, 'json' => ['author' => 'urn:li:organization:12345678', 'commentary' => 'LinkedIn words', 'visibility' => 'PUBLIC', 'content' => ['article' => ['source' => 'https://example.org/n/x']]]]);
check('LinkedIn POST /rest/posts: 201, empty body, the new post URN is in the x-restli-id header (lower-cased by SocialHttp)', $r['ok'] && $r['status'] === 201 && str_starts_with((string) ($r['headers']['x-restli-id'] ?? ''), 'urn:li:share:') && $postsOf('linkedin')[0]['data']['article'] === 'https://example.org/n/x');
$r = SocialHttp::api('linkedin', 'POST', '/rest/posts', ['headers' => ['Authorization' => 'Bearer WRONGLINKEDINTOKEN123456'] + $li, 'json' => ['author' => 'urn:li:organization:12345678', 'commentary' => 'x']]);
check('LinkedIn revoked / wrong token: 401 -> auth', SocialHttp::toResult('linkedin', $r)->state === 'auth');
// X with OAuth 1.0a (the mock VERIFIES the signature)
$oauth = static function (string $method, string $url, array $query, array $form, array $c, ?string $nonce = null, ?int $ts = null): string {
    $o = ['oauth_consumer_key' => $c['x_consumer_key'], 'oauth_nonce' => $nonce ?? bin2hex(random_bytes(8)), 'oauth_signature_method' => 'HMAC-SHA1', 'oauth_timestamp' => (string) ($ts ?? time()), 'oauth_token' => $c['x_token'], 'oauth_version' => '1.0'];
    $all = $o + $query + $form;
    $pairs = [];
    foreach ($all as $k => $v) {
        $pairs[] = [rawurlencode((string) $k), rawurlencode((string) $v)];
    }
    sort($pairs);
    $base = strtoupper($method) . '&' . rawurlencode(explode('?', $url)[0]) . '&' . rawurlencode(implode('&', array_map(static fn(array $p): string => $p[0] . '=' . $p[1], $pairs)));
    $o['oauth_signature'] = base64_encode(hash_hmac('sha1', $base, rawurlencode($c['x_consumer_secret']) . '&' . rawurlencode($c['x_token_secret']), true));
    ksort($o);
    return 'OAuth ' . implode(', ', array_map(static fn(string $k, string $v): string => rawurlencode($k) . '="' . rawurlencode($v) . '"', array_keys($o), $o));
};
$sign = static fn(array $c, ?string $nonce = null, ?int $ts = null): \Closure => static fn(string $m, string $u, array $q, array $f): array => ['Authorization' => $oauth($m, $u, $q, $f, $c, $nonce, $ts)];
$r = SocialHttp::api('x', 'GET', '/2/users/me', ['authorize' => $sign($zzXCred)]);
check('X OAuth 1.0a: the signature computed over SocialHttp\'s final URL is accepted (GET /2/users/me)', $r['ok'] && $r['json']['data']['username'] === 'mockluminax', json_encode([$r['status'], $r['json']]));
$r = SocialHttp::api('x', 'POST', '/2/tweets', ['json' => ['text' => 'First tweet ' . bin2hex(random_bytes(3))], 'authorize' => $sign($zzXCred)]);
check('X POST /2/tweets with a JSON body (the body is NOT part of the signature) -> 201 {"data":{"id","text"}}', $r['ok'] && $r['status'] === 201 && isset($r['json']['data']['id']) && count($postsOf('x')) === 1);
$r = SocialHttp::api('x', 'GET', '/2/users/me', ['authorize' => $sign(['x_token_secret' => 'wrong-secret-wrong-secret'] + $zzXCred)]);
check('X: a wrong token secret -> 401 -> auth', !$r['ok'] && $r['status'] === 401 && SocialHttp::toResult('x', $r)->state === 'auth');
$r = SocialHttp::api('x', 'GET', '/2/users/me', ['authorize' => static fn(string $m, string $u, array $q, array $f): array => ['Authorization' => $oauth($m, $u . 'x', $q, $f, $GLOBALS['zzXCred'])]]);
check('X: a signature over a different URL is refused (the mock really verifies)', !$r['ok'] && $r['status'] === 401);
SocialHttp::api('x', 'GET', '/2/users/me', ['authorize' => $sign($zzXCred, 'fixednonce0001')]);
$r = SocialHttp::api('x', 'GET', '/2/users/me', ['authorize' => $sign($zzXCred, 'fixednonce0001')]);
check('X: a replayed nonce is refused', !$r['ok'] && $r['status'] === 401);
$r = SocialHttp::api('x', 'GET', '/2/users/me', ['authorize' => $sign($zzXCred, null, time() - 4000)]);
check('X: an old timestamp is refused', !$r['ok'] && $r['status'] === 401);
$r = SocialHttp::api('x', 'GET', '/2/users/me', ['headers' => ['Authorization' => 'Bearer ' . $zzXCred['x_bearer']]]);
check('X: a Bearer user token works too', $r['ok'] && $r['json']['data']['username'] === 'mockluminax');
$same = 'Duplicate text ' . bin2hex(random_bytes(3));
SocialHttp::api('x', 'POST', '/2/tweets', ['json' => ['text' => $same], 'authorize' => $sign($zzXCred)]);
$r = SocialHttp::api('x', 'POST', '/2/tweets', ['json' => ['text' => $same], 'authorize' => $sign($zzXCred)]);
check('X: duplicate text -> 403 -> failed (never retried)', $r['status'] === 403 && SocialHttp::toResult('x', $r)->state === 'failed' && str_contains(SocialHttp::toResult('x', $r)->error, 'duplicate'));
$r1 = SocialHttp::api('x', 'POST', '/2/tweets', ['json' => ['text' => str_repeat("\u{1F600}", 140)], 'authorize' => $sign($zzXCred)]);
$r2 = SocialHttp::api('x', 'POST', '/2/tweets', ['json' => ['text' => str_repeat("\u{1F600}", 141)], 'authorize' => $sign($zzXCred)]);
$r3 = SocialHttp::api('x', 'POST', '/2/tweets', ['json' => ['text' => 'link ' . 'https://example.org/' . str_repeat('a', 300) . ' ' . str_repeat('b', 250)], 'authorize' => $sign($zzXCred)]);
check('X weighting: the mock and CaptionBuilder::length agree (140 emoji = 280 passes, 141 refused; a 320-character URL counts 23)', $r1['ok'] && $r2['status'] === 400 && $r3['ok'] && CaptionBuilder::length('x', str_repeat("\u{1F600}", 140)) === 280 && CaptionBuilder::length('x', str_repeat("\u{1F600}", 141)) === 282);
$mode('x', '429', 1);
$r = SocialHttp::api('x', 'GET', '/2/users/me', ['authorize' => $sign($zzXCred)]);
$res = SocialHttp::toResult('x', $r);
check('X 429: transient with the wait taken from Retry-After / x-rate-limit-reset', $res->state === 'transient' && $res->retryAfter >= 25 && $res->retryAfter <= 35);

// =====================================================================================================================================================
echo "Social: end to end with the real adapters (Telegram + Webhook) against the mock\n";
$mockReset();
$zzResetChannels();
Channels::reset();
Database::execute('DELETE FROM social_posts WHERE newsletter_id IN (' . implode(',', array_merge([0], array_map('intval', $zzIssues))) . ')');       // the rows of the fake-channel section (some of them failed) must not be re-queued below
SocialConfig::saveGlobal(['enabled' => true, 'utm' => true, 'hashtags' => '#Zz']);
$zzConfigure('telegram', ['bot_token' => $zzTok['tg'], 'chat' => '@luminatest', 'test_chat' => '']);
$zzConfigure('webhook', ['url' => $devBase . '/webhook/e2e', 'secret' => $zzTok['wh']]);
foreach (['telegram', 'webhook'] as $c) {
    Settings::set(SocialConfig::key($c, 'default'), '1');
}
SocialConfig::flush();
check('both channels are ready', SocialConfig::availableIds() === ['telegram', 'webhook']);
$tc = SocialDispatcher::testConnection('telegram');
$wc = SocialDispatcher::testConnection('webhook');
check('TEST CONNECTION through the dispatcher: ok, the health of the channel is recorded', $tc['ok'] && $wc['ok'] && SocialConfig::status('telegram')['state'] === 'ok' && SocialConfig::status('webhook')['state'] === 'ok' && !str_contains(json_encode([$tc, $wc, SocialConfig::status('telegram')]), $zzTok['tg']));
$webhookPings = count($postsOf('webhook'));
$e1 = $zzIssue(['title' => 'ZZ-SOCIAL end to end ' . bin2hex(random_bytes(2)), 'summary' => 'The end to end summary.']);
$ok = NewsletterService::publish($e1, $zzUid);
$rows = array_column(PostQueue::forNewsletter($e1), null, 'channel');
$tgPost = array_values(array_filter($postsOf('telegram'), static fn(array $p): bool => ($p['data']['method'] ?? '') !== ''))[0] ?? [];
$whPost = array_values(array_filter($postsOf('webhook'), static fn(array $p): bool => !$p['data']['test']))[0] ?? [];
check('PUBLISH -> queued -> posted at once on BOTH real adapters: rows posted with the telegram link; the mock received the Telegram message and the signed webhook', $ok['ok'] && $rows['telegram']['status'] === 'posted' && $rows['webhook']['status'] === 'posted' && str_starts_with((string) $rows['telegram']['remote_url'], 'https://t.me/luminatest/')
    && $tgPost !== [] && $whPost !== [] && $whPost['data']['signed'] === true, json_encode([$rows['telegram']['last_error'] ?? null, $rows['webhook']['last_error'] ?? null]));
check('the Telegram message is the stored caption (with the telegram UTM link) and carries the picture; the webhook carries the issue data and all captions', $tgPost['data']['caption'] === $rows['telegram']['caption'] && str_contains($tgPost['data']['caption'], 'utm_source=telegram') && ($tgPost['data']['method'] === 'sendPhoto') && str_contains($tgPost['data']['photo'], '/') && $whPost['data']['body']['title'] === Newsletter::find($e1)['title']
    && str_contains($whPost['data']['body']['url'], 'utm_source=webhook') && isset($whPost['data']['body']['captions']['tiktok']) && $whPost['data']['body']['summary'] === 'The end to end summary.' && $whPost['data']['body']['issue_number'] === (int) Newsletter::find($e1)['issue_number']);
$counts = [count($postsOf('telegram')), count($postsOf('webhook'))];
NewsletterService::unpublish($e1, $zzUid);
NewsletterService::publish($e1, $zzUid);
SocialDispatcher::runDue(20, 20.0);
check('unpublish + republish + a cron run: the platforms receive nothing more', [count($postsOf('telegram')), count($postsOf('webhook'))] === $counts);
// switches
Settings::set('social_enabled', '0');
SocialConfig::flush();
$e2 = $zzIssue();
NewsletterService::publish($e2, $zzUid);
Settings::set('social_enabled', '1');
Settings::set(SocialConfig::key('telegram', 'enabled'), '0');
SocialConfig::flush();
$e3 = $zzIssue();
NewsletterService::publish($e3, $zzUid);
Settings::set(SocialConfig::key('telegram', 'enabled'), '1');
SocialConfig::flush();
check('KILL SWITCHES: global off posts nothing at all; one channel off posts only to the others', (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$e2], 0) === 0 && array_keys(array_column(PostQueue::forNewsletter($e3), null, 'channel')) === ['webhook'] && [count($postsOf('telegram')), count($postsOf('webhook'))] === [$counts[0], $counts[1] + 1]);
$counts = [count($postsOf('telegram')), count($postsOf('webhook'))];
// auth failure: one e-mail, needs token, others continue, then recovery
$mails0 = count($zzMails());
$mode('telegram', '401', 1);
$e4 = $zzIssue();
NewsletterService::publish($e4, $zzUid);
$rows4 = array_column(PostQueue::forNewsletter($e4), null, 'channel');
$e5 = $zzIssue();
NewsletterService::publish($e5, $zzUid);
$rows5 = array_column(PostQueue::forNewsletter($e5), null, 'channel');
check('401 from Telegram: the post is failed, the channel "needs a new token", the webhook still posted; the next issue is not attempted on Telegram; ONE e-mail without any token', $rows4['telegram']['status'] === 'failed' && $rows4['webhook']['status'] === 'posted' && SocialConfig::status('telegram')['state'] === 'needs_token'
    && !isset($rows5['telegram']) && $rows5['webhook']['status'] === 'posted' && count($postsOf('telegram')) === $counts[0] && count($zzMails()) === $mails0 + 1 && !str_contains(implode("\n", $zzMails()), $zzTok['tg']), json_encode([$rows4['telegram']['status'] ?? null, SocialConfig::status('telegram')['state']]));
$res = SocialConfig::saveChannel('telegram', ['bot_token' => $zzTok['tg']]);
$n = PostQueue::retryFailedForChannel('telegram');
SocialDispatcher::runDue(20, 20.0);
check('a new token + "queue the failed posts again": the failed post goes out; still one e-mail', $res['ok'] && $n >= 1 && SocialPost::find((int) $rows4['telegram']['id'])['status'] === 'posted' && count($postsOf('telegram')) === $counts[0] + 1 && count($zzMails()) === $mails0 + 1);
// rate limit
$mode('telegram', '429', 1);
$e6 = $zzIssue();
NewsletterService::publish($e6, $zzUid);
$p6 = array_column(PostQueue::forNewsletter($e6), null, 'channel')['telegram'];
$wait = strtotime((string) $p6['next_attempt_at']) - time();
check('429 from Telegram (retry after 7 s): queued again, the wait is at least the 30 s minimum, one attempt counted', $p6['status'] === 'queued' && (int) $p6['attempts'] === 1 && $wait >= 25 && $wait <= 35 && SocialConfig::status('telegram')['state'] !== 'needs_token', json_encode([$p6['status'], $wait]));
Database::execute('UPDATE social_posts SET next_attempt_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 1), $p6['id']]);
SocialDispatcher::runDue(20, 20.0);
check('...and the cron run posts it once the wait is over', SocialPost::find((int) $p6['id'])['status'] === 'posted' && (int) SocialPost::find((int) $p6['id'])['attempts'] === 2);
// the 10 second timeout: ambiguous
echo "  info the next check waits for the real 10 s time-out (about 12 s)\n";
$mode('telegram', 'ambiguous', 1, 11.0);
$tgBefore = count($postsOf('telegram'));
$t0 = microtime(true);
$e7 = $zzIssue();
NewsletterService::publish($e7, $zzUid);
$tookAll = microtime(true) - $t0;
$rows7 = array_column(PostQueue::forNewsletter($e7), null, 'channel');
check('the REAL 10 s time-out after the request was sent: Telegram row = review ("may or may not have been posted"), the webhook (next in sequence) still posted; the whole run stays inside the 25 s budget', $rows7['telegram']['status'] === 'review' && $rows7['webhook']['status'] === 'posted' && $tookAll < 25 && $tookAll > 9 && count($postsOf('telegram')) === $tgBefore + 1, json_encode([$rows7['telegram']['status'], round($tookAll, 1)]));
SocialDispatcher::runDue(20, 20.0);
SocialDispatcher::lazyRetry();
check('a cron / lazy run does NOT post it again (the mock shows ONE Telegram message for it)', count($postsOf('telegram')) === $tgBefore + 1 && SocialPost::find((int) $rows7['telegram']['id'])['status'] === 'review');
PostQueue::retryAnyway((int) $rows7['telegram']['id']);
SocialDispatcher::runDue(20, 20.0);
check('"Post anyway" (an explicit decision) queues it again: now the platform has two messages and the row is posted', SocialPost::find((int) $rows7['telegram']['id'])['status'] === 'posted' && count($postsOf('telegram')) === $tgBefore + 2);
// test post and test connection failures
$tgBefore = count($postsOf('telegram'));
$st = SocialDispatcher::sendTest('telegram');
check('SEND A TEST POST: one clearly labelled message to the real chat (no test chat set)', $st['ok'] && count($postsOf('telegram')) === $tgBefore + 1 && str_contains($postsOf('telegram')[$tgBefore]['data']['text'], 'Test post'));
$mode('telegram', '401', 1);
$tc = SocialDispatcher::testConnection('telegram');
check('TEST CONNECTION with a rejected token: state auth, the channel is marked "needs a new token", the message names no token', !$tc['ok'] && $tc['state'] === 'auth' && SocialConfig::status('telegram')['state'] === 'needs_token' && !str_contains(json_encode($tc), $zzTok['tg']));
SocialConfig::saveChannel('telegram', ['bot_token' => $zzTok['tg']]);
check('missing setup is explained before any call: a channel without its fields cannot be tested or sent a test post', (function () use ($zzResetChannels): bool {
    $a = SocialDispatcher::testConnection('linkedin');
    $b = SocialDispatcher::sendTest('instagram');
    return !$a['ok'] && str_contains($a['message'], 'Missing') && !$b['ok'] && str_contains($b['message'], 'Missing') && !SocialDispatcher::testConnection('nope')['ok'];
})());

// =====================================================================================================================================================
echo "Social: nothing secret leaked anywhere\n";
$leakNeedles = [$zzTok['fb'], $zzTok['tg'], substr($zzTok['tg'], 12), $zzTok['wh'], $zzTok['li'], 'WRONGTOKEN', 'the-wrong-secret'];
$hits = [];
$scan = static function (string $what, string $text) use ($leakNeedles, &$hits): void {
    foreach ($leakNeedles as $n) {
        if ($n !== '' && str_contains($text, $n)) {
            $hits[] = $what . ' has ' . substr($n, 0, 8) . '...';
        }
    }
};
foreach (Database::all('SELECT action, summary, details FROM audit_log WHERE id > ?', [$zzAuditStart]) as $a) {
    $scan('audit ' . $a['action'], (string) $a['summary'] . (string) $a['details']);
}
$logNow = is_file($zzLogFile) ? (string) file_get_contents($zzLogFile, false, null, $zzLogStart) : '';
$scan('app log', $logNow);
foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $zzMailBefore) as $f) {
    $scan('mail ' . basename($f), (string) file_get_contents($f));
}
foreach (Database::all('SELECT * FROM social_posts') as $p) {
    $scan('social_posts #' . $p['id'], json_encode($p));
}
foreach (Database::all("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'social\\_%'") as $s) {
    $scan('setting ' . $s['setting_key'], (string) $s['setting_value']);
}
foreach (Channels::ids() as $id) {
    $scan('publicView ' . $id, json_encode(SocialConfig::publicView($id)));
}
$scan('attention', json_encode(SocialConfig::attention(true)));
$scan('panel', json_encode(\App\Services\Social\ShareHook::panel($e1)));
$scan('mock state (what the platforms saw as content)', json_encode($state()['posts']));
check('no token, secret or signing secret appears in the audit trail, the application log, the e-mails, the share log, the stored settings, the admin views, the panel or the dashboard counts', $hits === [], implode('; ', $hits));
check('the mock never saw our secrets in a place they do not belong: rejected requests are logged by reason only', (static function () use ($state): bool {
    foreach ((array) ($state()['rejects'] ?? []) as $r) {
        if (!in_array($r['why'], ['bad token', 'bad signature', 'bad oauth1 signature', 'oauth1 parameters', 'bad body or stale timestamp'], true)) {
            return false;
        }
    }
    return true;
})());

// leave nothing behind: the mock process is stopped by the shutdown function; the dev base URL is removed again
putenv('SOCIAL_DEV_BASE_URL');
Channels::reset();
$zzResetChannels();
check('.env was not touched by this run', (string) @file_get_contents($zzRoot . '/.env') === $zzEnvBefore);
