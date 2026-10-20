<?php
declare(strict_types=1);

namespace App\Services\Social\Channels;

use App\Services\Social\AbstractChannel;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\ChannelSupport;
use App\Services\Social\OAuth1Signer;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialResult;

/**
 * X (TWITTER) via API v2 (docs/SOCIAL.md section 4.3). OWNER: CHANNELS agent builds post(), test() and the OAuth 1.0a signer (new file
 * app/services/Social/OAuth1Signer.php); the metadata is the architect's contract.
 *
 * To build (SocialHttp::api('x', ..., host api.x.com):
 *   auth:    mode "oauth1" (default): sign every request with OAuth 1.0a user context (HMAC-SHA1; consumer key/secret = api_key/api_secret, token/secret =
 *            access_token/access_secret) and send it in the Authorization header. JSON bodies are NOT part of the signature base string.
 *            mode "bearer": Authorization: Bearer <bearer_token> (an OAuth 2.0 USER-context access token; it expires after about two hours and this app cannot refresh it:
 *            that is why oauth1 is the recommended mode).
 *   test():  GET /2/users/me -> {"data":{"id","name","username"}}; detail = "@username". 401 -> auth.
 *   post():  POST /2/tweets  json {"text": caption}  -> 201 {"data":{"id":"1234","text":"..."}}; remote_id = id, remote_url = "https://x.com/{username or i}/status/{id}".
 *            Duplicate text answers 403 ("duplicate content"): failed, not retried. 429 -> transient (Retry-After / x-rate-limit-reset are in the SocialHttp result).
 *            Ambiguous timeout -> review (no idempotency key).
 *   Limit 280 weighted characters: every URL counts 23 (CaptionBuilder::length handles it; payload->caption already fits).
 *   Test post: text "Test post from {site} - ignore" (real tweet; the admin must confirm).
 */
final class XChannel extends AbstractChannel
{
    protected const META = [
        'id' => 'x', 'label' => 'X (Twitter)', 'icon' => 'twitter', 'limit' => 280, 'soft' => 240, 'count' => 'x', 'links' => 'clickable', 'uses_image' => false, 'image_variant' => null, 'repeat_text' => 'rejected',
        'short' => 'Posts a short text with the link to the issue on your X account.',
        'needs' => 'An X developer account with an app that has "Read and write" permission. X may require a paid API plan for posting: check the current plans.',
        'setup_url' => 'https://developer.x.com/en/portal/dashboard',
        'steps' => [
            'Open developer.x.com, sign in with your X account and create a Project and an App (check which API plan allows posting: X changes its plans).',
            'In the App settings > "User authentication settings" turn it on, choose App permissions "Read and write" and save.',
            'Open "Keys and tokens". Generate the API Key and API Key Secret, then generate the Access Token and Access Token Secret (they must say "Read and Write": if you changed the permission afterwards, generate them again).',
            'Paste the four values below (they are stored encrypted and never shown again). Press "Test connection", then "Send a test post" (a real post: delete it afterwards).',
            'Alternative (not recommended): choose "Bearer user token" and paste an OAuth 2.0 user access token. Those expire after about two hours and this site cannot refresh them.',
        ],
        'permissions' => 'App permission "Read and write" (tweet.write). Not tried with the real service yet.',
        'notes' => ['X counts every link as 23 characters, whatever its length; the counter in the share panel follows that rule.'],
        'fields' => [
            ['key' => 'mode', 'label' => 'How to sign in', 'type' => 'select', 'required' => true, 'options' => ['oauth1' => 'API key + access token (OAuth 1.0a, recommended)', 'bearer' => 'Bearer user token (OAuth 2.0, expires quickly)'], 'default' => 'oauth1'],
            ['key' => 'api_key', 'label' => 'API key', 'type' => 'secret', 'required' => true, 'when' => ['mode' => 'oauth1'], 'env' => 'SOCIAL_X_API_KEY', 'pattern' => '/^[A-Za-z0-9_\-]{10,200}$/', 'maxlength' => 200],
            ['key' => 'api_secret', 'label' => 'API key secret', 'type' => 'secret', 'required' => true, 'when' => ['mode' => 'oauth1'], 'env' => 'SOCIAL_X_API_SECRET', 'pattern' => '/^[A-Za-z0-9_\-]{20,300}$/', 'maxlength' => 300],
            ['key' => 'access_token', 'label' => 'Access token', 'type' => 'secret', 'required' => true, 'when' => ['mode' => 'oauth1'], 'env' => 'SOCIAL_X_ACCESS_TOKEN', 'pattern' => '/^[A-Za-z0-9_\-]{20,300}$/', 'maxlength' => 300],
            ['key' => 'access_secret', 'label' => 'Access token secret', 'type' => 'secret', 'required' => true, 'when' => ['mode' => 'oauth1'], 'env' => 'SOCIAL_X_ACCESS_SECRET', 'pattern' => '/^[A-Za-z0-9_\-]{20,300}$/', 'maxlength' => 300],
            ['key' => 'bearer_token', 'label' => 'Bearer user token', 'type' => 'secret', 'required' => true, 'when' => ['mode' => 'bearer'], 'env' => 'SOCIAL_X_BEARER_TOKEN', 'pattern' => '/^[A-Za-z0-9_\-.%=]{20,1000}$/', 'maxlength' => 1000],
        ],
        'built' => true,
    ];

    /** @param array<string,string> $config */
    private function signer(array $config): ?OAuth1Signer
    {
        foreach (['api_key', 'api_secret', 'access_token', 'access_secret'] as $k) {
            if (trim((string) ($config[$k] ?? '')) === '') {
                return null;
            }
        }
        return new OAuth1Signer(trim($config['api_key']), trim($config['api_secret']), trim($config['access_token']), trim($config['access_secret']));
    }

    /**
     * One X API call with the configured sign-in mode.
     * @param array<string,string> $config
     * @param array<string,mixed> $opts
     * @return ?array<string,mixed> null when the credentials of the chosen mode are incomplete
     */
    private function call(array $config, string $method, string $path, array $opts = []): ?array
    {
        if (($config['mode'] ?? 'oauth1') === 'bearer') {
            $t = trim((string) ($config['bearer_token'] ?? ''));
            if ($t === '') {
                return null;
            }
            $opts['headers'] = ['Authorization' => 'Bearer ' . $t];
        } else {
            $signer = $this->signer($config);
            if ($signer === null) {
                return null;
            }
            $opts['authorize'] = $signer->authorizer();
        }
        return SocialHttp::api('x', $method, $path, $opts);
    }

    /** @param array<string,string> $config */
    private function incomplete(array $config): string
    {
        return ($config['mode'] ?? 'oauth1') === 'bearer'
            ? 'The bearer user token is not set.'
            : 'The API key, API key secret, access token and access token secret must all be set.';
    }

    public function test(array $config): array
    {
        return ChannelSupport::guardTest('X', function () use ($config): array {
            $r = $this->call($config, 'GET', '/2/users/me');
            if ($r === null) {
                return $this->bad($this->incomplete($config));
            }
            if (!$r['ok']) {
                return ChannelSupport::testFailure(ChannelSupport::xResult($r));
            }
            $u = is_array($r['json']) && is_array($r['json']['data'] ?? null) ? (string) ($r['json']['data']['username'] ?? '') : '';
            if ($u === '') {
                return $this->bad('X answered in an unexpected way.');
            }
            $name = '@' . mb_substr(SocialHttp::redact($u), 0, 40);
            return $this->ok('Connected to X as ' . $name . '. (Posting also needs the app permission "Read and write" and an API plan that allows posting.)', $name);
        });
    }

    public function post(array $config, SocialPayload $payload): SocialResult
    {
        return ChannelSupport::guardPost('X', function () use ($config, $payload): SocialResult {
            if ($payload->test) {
                $text = 'Test post from ' . ($payload->siteName !== '' ? $payload->siteName : 'the website') . ' - ignore (' . date('H:i:s') . ')';
            } else {
                $text = trim($payload->caption);
                if ($text === '') {
                    return SocialResult::failed('The X text is empty.', 'empty_caption');
                }
                if (CaptionBuilder::length('x', $text) > 280) {
                    return SocialResult::failed('The X text is longer than 280 (weighted) characters; every link counts 23.', 'too_long');
                }
            }
            $r = $this->call($config, 'POST', '/2/tweets', ['json' => ['text' => $text], 'deadline' => $payload->deadline]);
            if ($r === null) {
                return SocialResult::failed('X is not fully set up. ' . $this->incomplete($config), 'not_configured');
            }
            if (!$r['ok']) {
                return ChannelSupport::xResult($r);
            }
            $id = is_array($r['json']) && is_array($r['json']['data'] ?? null) ? (string) ($r['json']['data']['id'] ?? '') : '';
            if (preg_match('/^\d{5,25}$/', $id) !== 1) {
                return SocialResult::review('X accepted the request but did not return a post id. Check the account, then mark it posted or post again.', 'no_id');
            }
            return SocialResult::posted($id, 'https://x.com/i/web/status/' . $id);
        });
    }
}
