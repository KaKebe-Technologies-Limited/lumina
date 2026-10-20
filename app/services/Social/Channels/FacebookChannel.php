<?php
declare(strict_types=1);

namespace App\Services\Social\Channels;

use App\Services\Social\AbstractChannel;
use App\Services\Social\ChannelSupport;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialResult;

/**
 * FACEBOOK PAGE via the Graph API (docs/SOCIAL.md section 4.1). OWNER: CHANNELS agent builds post() and test(); the metadata below is the architect's contract
 * (fields, limits, steps) and is frozen.
 *
 * To build (all through SocialHttp::api('facebook', ...), host graph.facebook.com, version = SocialConfig::str('social_graph_version'), e.g. "/v23.0/me"):
 *   test():  GET /{ver}/me?fields=id,name  -> ok when it answers 200 (detail = the Page name); then GET /{ver}/{page_id}?fields=id,name must answer the same id.
 *            A Page access token's /me IS the Page. Failure -> SocialHttp::describeFailure('facebook', $r).
 *   post():  POST /{ver}/{page_id}/feed  form: message = caption, link = payload->plainUrl (the Page shows a link card from the issue's Open Graph tags)
 *            -> {"id":"{page_id}_{post_id}"}; remote_id = that id, remote_url = "https://www.facebook.com/{id}".
 *            Test post (payload->test): message "Test post from {site} - ignore" WITHOUT link.
 *   Token in the Authorization header ("Bearer"), never in the URL. Any non-2xx -> SocialHttp::toResult('facebook', $r) (code 190 / 102 / 463 -> auth).
 *   Ambiguous timeout -> review (meta 'ambiguous' = review): Graph has no idempotency key.
 */
final class FacebookChannel extends AbstractChannel
{
    protected const META = [
        'id' => 'facebook', 'label' => 'Facebook Page', 'icon' => 'facebook', 'limit' => 5000, 'soft' => 600, 'links' => 'attached', 'uses_image' => false, 'image_variant' => null,
        'short' => 'Posts the issue title, summary and a link card to your Facebook Page.',
        'needs' => 'A Facebook Page that you manage, and a Meta developer app (free).',
        'setup_url' => 'https://developers.facebook.com/apps/',
        'steps' => [
            'Open developers.facebook.com, sign in with the Facebook account that manages the Page and create an app (type "Business").',
            'Open the Graph API Explorer, choose your app, press "Get Page Access Token", tick pages_manage_posts, pages_read_engagement and pages_show_list and select your Page.',
            'Make the token long-lived: in the Access Token Debugger press "Extend access token" for the USER token, then call /me/accounts with it. The Page token that comes back does not expire.',
            'Copy the Page ID (it is the "id" next to your Page in /me/accounts, and also shown under Page > About > Page transparency) and the Page access token into the boxes below.',
            'Press "Test connection", then "Send a test post" (it posts a short test message to the Page: delete it afterwards).',
        ],
        'permissions' => 'pages_manage_posts, pages_read_engagement, pages_show_list. While the Meta app is in Development mode only people with a role on the app can see what it posts; to post for the public the app may have to go Live (Meta can ask for a business verification or app review). Not tried with the real service yet.',
        'notes' => ['A link card is attached from the issue page, so the default template has no link in the text.'],
        'fields' => [
            ['key' => 'page_id', 'label' => 'Facebook Page ID', 'type' => 'text', 'required' => true, 'pattern' => '/^\d{5,25}$/', 'placeholder' => '105234567890123', 'maxlength' => 25,
             'help' => 'The long number of your Page (not its name).'],
            ['key' => 'page_token', 'label' => 'Page access token', 'type' => 'secret', 'required' => true, 'env' => 'SOCIAL_FB_PAGE_TOKEN', 'pattern' => '/^[A-Za-z0-9_\-.|]{20,700}$/', 'maxlength' => 700,
             'help' => 'A long-lived PAGE token (not a user token). Stored encrypted; never shown again.'],
        ],
        'built' => true,
    ];

    public function test(array $config): array
    {
        return ChannelSupport::guardTest('Facebook', function () use ($config): array {
            $pageId = trim((string) ($config['page_id'] ?? ''));
            $token = trim((string) ($config['page_token'] ?? ''));
            if ($pageId === '') {
                return $this->bad('The Facebook Page ID is not set.');
            }
            if ($token === '') {
                return $this->bad('The Page access token is not set.');
            }
            $me = ChannelSupport::graph('facebook', 'GET', 'me', $token, ['fields' => 'id,name']);
            if (!$me['ok']) {
                return ChannelSupport::testFailure(ChannelSupport::graphResult('facebook', $me));
            }
            $meId = (string) ($me['json']['id'] ?? '');
            if ($meId === '') {
                return $this->bad('Facebook answered in an unexpected way.');
            }
            if ($meId !== $pageId) {
                // a USER token's /me is the person; the Page token that /me/accounts returns is what is needed
                $page = ChannelSupport::graph('facebook', 'GET', $pageId, $token, ['fields' => 'id,name']);
                if (!$page['ok'] || (string) ($page['json']['id'] ?? '') !== $pageId) {
                    return $this->bad('This token belongs to "' . $this->name($me) . '", not to the Page with ID ' . $pageId . '. Use the PAGE access token that /me/accounts returns for your Page, and check the Page ID.');
                }
                return $this->bad('This looks like a personal (user) token, not a Page token: posting needs the Page access token from /me/accounts.');
            }
            return $this->ok('Connected to the Facebook Page "' . $this->name($me) . '". (Posting also needs pages_manage_posts and, for the public to see posts, a Live Meta app.)', $this->name($me));
        });
    }

    public function post(array $config, SocialPayload $payload): SocialResult
    {
        return ChannelSupport::guardPost('Facebook', function () use ($config, $payload): SocialResult {
            $pageId = trim((string) ($config['page_id'] ?? ''));
            $token = trim((string) ($config['page_token'] ?? ''));
            if ($pageId === '' || $token === '') {
                return SocialResult::failed('Facebook is not fully set up (Page ID and Page access token).', 'not_configured');
            }
            if ($payload->test) {
                $form = ['message' => 'Test post from ' . ($payload->siteName !== '' ? $payload->siteName : 'the website') . ' - ignore (' . date('H:i:s') . ')'];
            } else {
                $message = trim($payload->caption);
                if ($message === '') {
                    return SocialResult::failed('The Facebook caption is empty.', 'empty_caption');
                }
                if (mb_strlen($message) > 5000) {
                    return SocialResult::failed('The Facebook caption is longer than 5000 characters.', 'too_long');
                }
                $form = ['message' => $message];
                if (preg_match('~^https://[^\s]+$~i', $payload->plainUrl) === 1) {
                    $form['link'] = $payload->plainUrl;   // the Page builds a link card from the issue's Open Graph tags
                }
            }
            $r = ChannelSupport::graph('facebook', 'POST', $pageId . '/feed', $token, [], $form, $payload->deadline);
            if (!$r['ok']) {
                return ChannelSupport::graphResult('facebook', $r);
            }
            $id = is_array($r['json']) ? (string) ($r['json']['id'] ?? '') : '';
            if (preg_match('/^[0-9]+(_[0-9]+)?$/', $id) !== 1) {
                // accepted (2xx) but no readable post id: the post may exist, so it is never posted again by itself
                return SocialResult::review('Facebook accepted the request but did not return a post id. Check the Page, then mark it posted or post again.', 'no_id');
            }
            return SocialResult::posted($id, 'https://www.facebook.com/' . $id);
        });
    }

    /** @param array<string,mixed> $answer */
    private function name(array $answer): string
    {
        $n = is_array($answer['json'] ?? null) ? (string) ($answer['json']['name'] ?? '') : '';
        return $n !== '' ? mb_substr(SocialHttp::redact($n), 0, 80) : 'your Page';
    }
}
