<?php
declare(strict_types=1);

namespace App\Services\Social\Channels;

use App\Services\Social\AbstractChannel;
use App\Services\Social\ChannelSupport;
use App\Services\Social\SocialConfig;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialResult;

/**
 * LINKEDIN ORGANISATION (Company Page) posts via the LinkedIn REST API (docs/SOCIAL.md section 4.4). OWNER: CHANNELS agent builds post() and test(); the
 * metadata is the architect's contract.
 *
 * To build (SocialHttp::api('linkedin', ..., host api.linkedin.com):
 *   headers: Authorization: Bearer <access_token>, X-Restli-Protocol-Version: 2.0.0, Linkedin-Version: <SocialConfig::str('social_li_version'), YYYYMM>.
 *   test():  GET /rest/organizationAcls?q=roleAssignee&role=ADMINISTRATOR  (or /v2/organizationAcls) -> 200 and the organisation URN is among the elements -> ok.
 *            200 without the URN -> error "this token's member is not an administrator of that page". 401 -> auth.
 *   post():  POST /rest/posts  json {author: org_urn, commentary: caption, visibility: "PUBLIC", distribution: {feedDistribution: "MAIN_FEED", targetEntities: [],
 *            thirdPartyDistributionChannels: []}, content: {article: {source: payload->plainUrl, title: payload->title, description: summary}}, lifecycleState: "PUBLISHED",
 *            isReshareDisabledByAuthor: false}  -> 201 with the new post URN in the `x-restli-id` response header: remote_id = that URN,
 *            remote_url = "https://www.linkedin.com/feed/update/{urn}". (The legacy /v2/ugcPosts endpoint is an acceptable fallback.)
 *            LinkedIn "little text" format: the characters ( ) [ ] { } < > @ | ~ _ * # \ in commentary must be escaped with a backslash.
 *   Token: valid for about 60 days (refresh tokens exist only for LinkedIn partner programmes): the admin types the expiry date; 14 days before it the dashboard and an
 *   e-mail remind the admin (SocialConfig::expiryWarnings). 401 or "REVOKED_ACCESS_TOKEN" -> auth.
 *   Ambiguous timeout -> review.
 */
final class LinkedInChannel extends AbstractChannel
{
    protected const META = [
        'id' => 'linkedin', 'label' => 'LinkedIn Page', 'icon' => 'linkedin', 'limit' => 3000, 'soft' => 700, 'links' => 'clickable', 'uses_image' => false, 'image_variant' => null,
        'short' => 'Posts the summary and a link to the issue on your LinkedIn Company Page.',
        'needs' => 'A LinkedIn Company Page that you administer and a LinkedIn developer app with the "Community Management API" product (LinkedIn reviews and approves this).',
        'setup_url' => 'https://www.linkedin.com/developers/apps',
        'steps' => [
            'Open linkedin.com/developers, create an app and connect it to your Company Page.',
            'In the app, open the Products tab and request "Community Management API" (posting for an organisation needs the permission w_organization_social; LinkedIn reviews the request, it can take days).',
            'Create an access token with the scopes w_organization_social and r_organization_social (Auth tab > OAuth 2.0 tools, or the token generator). Write down the expiry date: it lasts about 60 days.',
            'Find the organisation number in the address of your Page admin view (linkedin.com/company/<number>/admin) and enter it as the organisation URN (urn:li:organization:<number>, or just the number).',
            'Paste the token and its expiry date below. Press "Test connection". You will be reminded 14 days before it expires; then create a new token and replace it.',
        ],
        'permissions' => 'w_organization_social (post) and r_organization_social (read). Only administrators of the Page can post as the Page. Not tried with the real service yet.',
        'notes' => ['LinkedIn does not renew these tokens by itself: when it expires, posting stops until a new token is pasted here.'],
        'fields' => [
            ['key' => 'org_urn', 'label' => 'Organisation URN', 'type' => 'text', 'required' => true, 'pattern' => '/^(urn:li:organization:)?\d{3,20}$/', 'placeholder' => 'urn:li:organization:12345678', 'maxlength' => 60,
             'help' => 'urn:li:organization:12345678 (a bare number is fine).'],
            ['key' => 'access_token', 'label' => 'Access token', 'type' => 'secret', 'required' => true, 'env' => 'SOCIAL_LI_ACCESS_TOKEN', 'pattern' => '/^[A-Za-z0-9_\-.~+\/=]{20,2000}$/', 'maxlength' => 2000],
            ['key' => 'expires', 'label' => 'Token expiry date', 'type' => 'date', 'required' => false, 'placeholder' => '2026-12-31', 'maxlength' => 10,
             'help' => 'The day the token stops working (about 60 days after you created it). We remind you 14 days before.'],
        ],
        'token_expiry' => 'expires',
        'built' => true,
    ];

    public function normaliseConfig(array $config): array
    {
        $urn = trim((string) ($config['org_urn'] ?? ''));
        if ($urn !== '' && ctype_digit($urn)) {
            $config['org_urn'] = 'urn:li:organization:' . $urn;
        }
        return $config;
    }

    public function test(array $config): array
    {
        return ChannelSupport::guardTest('LinkedIn', function () use ($config): array {
            $config = $this->normaliseConfig($config);
            $urn = trim((string) ($config['org_urn'] ?? ''));
            if ($urn === '' || trim((string) ($config['access_token'] ?? '')) === '') {
                return $this->bad('The organisation URN and the access token must both be set.');
            }
            if (ChannelSupport::datePassed(trim((string) ($config['expires'] ?? '')))) {
                return $this->bad('The token expiry date has passed: create a new token on LinkedIn and replace it here.', 'auth');
            }
            $r = $this->call($config, 'GET', '/rest/organizationAcls', ['query' => ['q' => 'roleAssignee', 'role' => 'ADMINISTRATOR', 'count' => 100]]);
            if (!$r['ok']) {
                return ChannelSupport::testFailure(ChannelSupport::linkedinResult($r));
            }
            $els = is_array($r['json']) && is_array($r['json']['elements'] ?? null) ? $r['json']['elements'] : null;
            if ($els === null) {
                return $this->bad('LinkedIn answered in an unexpected way.');
            }
            foreach ($els as $el) {
                if (is_array($el) && (string) ($el['organization'] ?? $el['organizationalTarget'] ?? '') === $urn) {
                    return $this->ok('Connected: the token\'s member administers ' . $urn . '. (Posting needs w_organization_social.)', $urn);
                }
            }
            return $this->bad('The token works, but its member is not an administrator of ' . $urn . '. Check the organisation number, or use a token of a Page administrator.');
        });
    }

    public function post(array $config, SocialPayload $payload): SocialResult
    {
        return ChannelSupport::guardPost('LinkedIn', function () use ($config, $payload): SocialResult {
            $config = $this->normaliseConfig($config);
            $urn = trim((string) ($config['org_urn'] ?? ''));
            if (preg_match('/^urn:li:organization:\d{3,20}$/', $urn) !== 1 || trim((string) ($config['access_token'] ?? '')) === '') {
                return SocialResult::failed('LinkedIn is not fully set up (organisation URN and access token).', 'not_configured');
            }
            if (ChannelSupport::datePassed(trim((string) ($config['expires'] ?? '')))) {
                return SocialResult::auth('The LinkedIn token expiry date has passed: create a new token and replace it.', 'expired');
            }
            if ($payload->test) {
                $text = 'Test post from ' . ($payload->siteName !== '' ? $payload->siteName : 'the website') . ' - ignore (' . date('H:i:s') . ')';
                $article = null;
            } else {
                $text = trim($payload->caption);
                if ($text === '') {
                    return SocialResult::failed('The LinkedIn text is empty.', 'empty_caption');
                }
                if (mb_strlen($text) > 3000) {
                    return SocialResult::failed('The LinkedIn text is longer than 3000 characters.', 'too_long');
                }
                $article = preg_match('~^https://[^\s]+$~i', $payload->plainUrl) === 1 ? [
                    'source' => $payload->plainUrl,
                    'title' => mb_substr($payload->title !== '' ? $payload->title : ($payload->siteName !== '' ? $payload->siteName : 'Newsletter'), 0, 400),
                    'description' => mb_substr($payload->summary, 0, 4000),
                ] : null;
            }
            $commentary = ChannelSupport::linkedinEscape($text);
            $body = [
                'author' => $urn,
                'commentary' => $commentary,
                'visibility' => 'PUBLIC',
                'distribution' => ['feedDistribution' => 'MAIN_FEED', 'targetEntities' => [], 'thirdPartyDistributionChannels' => []],
                'lifecycleState' => 'PUBLISHED',
                'isReshareDisabledByAuthor' => false,
            ];
            if ($article !== null) {
                $body['content'] = ['article' => $article];
            }
            $r = $this->call($config, 'POST', '/rest/posts', ['json' => $body, 'deadline' => $payload->deadline]);
            if (!$r['ok'] && in_array((int) $r['status'], [404, 426], true) && (string) ($r['kind'] ?? '') === '') {
                // the versioned endpoint is not available to this app / version (nothing is created by a 404 / 426): the legacy endpoint is the documented fallback
                $legacy = $this->call($config, 'POST', '/v2/ugcPosts', ['json' => $this->legacyBody($urn, $commentary, $article), 'deadline' => $payload->deadline], false);
                if ($legacy['ok'] || (int) $r['status'] !== 426) {
                    $r = $legacy;
                }
            }
            if (!$r['ok']) {
                return ChannelSupport::linkedinResult($r);
            }
            $id = trim((string) ($r['headers']['x-restli-id'] ?? ''));
            if ($id === '' && is_array($r['json'])) {
                $id = trim((string) ($r['json']['id'] ?? ''));
            }
            if ($id === '' || mb_strlen($id) > 190) {
                return SocialResult::review('LinkedIn accepted the request but did not return a post id. Check the Page, then mark it posted or post again.', 'no_id');
            }
            $url = preg_match('/^urn:li:(share|ugcPost|activity):\d{1,30}$/', $id) === 1 ? 'https://www.linkedin.com/feed/update/' . $id : null;
            return SocialResult::posted($id, $url);
        });
    }

    /**
     * @param array<string,string> $config
     * @param array<string,mixed> $opts
     * @return array<string,mixed>
     */
    private function call(array $config, string $method, string $path, array $opts = [], bool $versioned = true): array
    {
        $h = ['Authorization' => 'Bearer ' . trim((string) $config['access_token']), 'X-Restli-Protocol-Version' => '2.0.0'];
        if ($versioned) {
            $ver = trim(SocialConfig::str('social_li_version'));
            $h['Linkedin-Version'] = preg_match('/^20\d{4}$/', $ver) === 1 ? $ver : '202510';
        }
        $opts['headers'] = $h;
        return SocialHttp::api('linkedin', $method, $path, $opts);
    }

    /**
     * The legacy UGC Posts body.
     * @param ?array{source:string,title:string,description:string} $article
     * @return array<string,mixed>
     */
    private function legacyBody(string $urn, string $commentary, ?array $article): array
    {
        $content = ['shareCommentary' => ['text' => $commentary], 'shareMediaCategory' => $article !== null ? 'ARTICLE' : 'NONE'];
        if ($article !== null) {
            $content['media'] = [['status' => 'READY', 'originalUrl' => $article['source'], 'title' => ['text' => $article['title']], 'description' => ['text' => $article['description']]]];
        }
        return [
            'author' => $urn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => ['com.linkedin.ugc.ShareContent' => $content],
            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'],
        ];
    }
}
