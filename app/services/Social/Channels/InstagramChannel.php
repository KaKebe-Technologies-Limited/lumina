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
 * INSTAGRAM (Business / Creator account linked to a Facebook Page) via the Graph API content-publishing flow (docs/SOCIAL.md section 4.2).
 * OWNER: CHANNELS agent builds post() and test(); the metadata is the architect's contract.
 *
 * To build (SocialHttp::api('instagram', ...), same host and token as Facebook unless the optional `token` field is set):
 *   test():  GET /{ver}/{account_id}?fields=id,username -> ok, detail = "@username".
 *   post():  Instagram needs a PUBLIC IMAGE URL: payload->imageUrl (square 1080x1080 JPEG from SocialCard::publicUrl). No image -> failed('Instagram needs a share image ...', 'no_image').
 *            1) POST /{ver}/{account_id}/media  form: image_url, caption  -> {"id":"<creation_id>"}   (harmless to repeat; not recorded as a post)
 *            2) optionally poll GET /{ver}/{creation_id}?fields=status_code until FINISHED (max ~6 s; ERROR/EXPIRED -> failed)
 *            3) POST /{ver}/{account_id}/media_publish  form: creation_id  -> {"id":"<media_id>"}
 *            then GET /{ver}/{media_id}?fields=permalink -> remote_url (best effort).
 *            Step 3 is NOT idempotent: an ambiguous timeout there is review (put the creation_id in the review result so a person can find it).
 *   Captions are plain text; Instagram does not make links clickable (templates say "link in bio"). Limit 2200 characters, at most 30 hashtags.
 *   Test post: caption "Test post from {site} - ignore" with the same image flow (the admin is warned it is a real post).
 */
final class InstagramChannel extends AbstractChannel
{
    protected const META = [
        'id' => 'instagram', 'label' => 'Instagram', 'icon' => 'instagram', 'limit' => 2200, 'soft' => 1500, 'links' => 'none', 'uses_image' => true, 'image_variant' => 'square',
        'short' => 'Posts the square share image with the caption ("link in bio") to your Instagram account.',
        'needs' => 'An Instagram Business or Creator account that is linked to your Facebook Page, and the same Meta developer app as Facebook.',
        'setup_url' => 'https://developers.facebook.com/docs/instagram-platform/content-publishing',
        'steps' => [
            'In the Instagram app switch the account to a Professional account (Business or Creator): Settings > Account type and tools.',
            'Link it to your Facebook Page: Facebook Page > Settings > Linked accounts > Instagram.',
            'Use the same Meta app as for Facebook and add the permissions instagram_basic, instagram_content_publish and pages_show_list when you create the token.',
            'Find the Instagram account ID: in the Graph API Explorer call /{your Page ID}?fields=instagram_business_account and copy the "id" inside it.',
            'Paste the ID below. Leave the token box empty to use the Facebook Page token (the normal case). Press "Test connection".',
        ],
        'permissions' => 'instagram_basic, instagram_content_publish, pages_show_list (+ pages_read_engagement). Meta may require app review for these before the public can see posts. Not tried with the real service yet.',
        'notes' => [
            'Instagram cannot show clickable links in a caption: the default template says "Link in bio". Put the newsletter link in your Instagram bio.',
            'The picture must be a public JPEG between 4:5 and 1.91:1. The site creates a 1080x1080 square image and hosts it at a private, unguessable address. Without the GD image library the cover photo is used as it is and may be rejected.',
            'Instagram limits how many posts an account can publish through the API per day.',
        ],
        'fields' => [
            ['key' => 'account_id', 'label' => 'Instagram business account ID', 'type' => 'text', 'required' => true, 'pattern' => '/^\d{5,25}$/', 'placeholder' => '17841400000000000', 'maxlength' => 25,
             'help' => 'The number from instagram_business_account (not the @name).'],
            ['key' => 'token', 'label' => 'Access token (optional)', 'type' => 'secret', 'required' => false, 'env' => 'SOCIAL_IG_TOKEN', 'pattern' => '/^[A-Za-z0-9_\-.|]{20,700}$/', 'maxlength' => 700,
             'help' => 'Leave empty to use the Facebook Page token. Only needed when Instagram uses a different token.'],
        ],
        'built' => true,
    ];

    /** The token Instagram uses: its own, else the Facebook Page token. @param array<string,string> $config */
    private function token(array $config): string
    {
        $own = SocialConfig::secretStatus('instagram', 'token');
        if ($own['set'] && !$own['readable']) {
            return '';                                  // its own token is stored but unreadable: never silently switch to another account's token
        }
        $t = trim((string) ($config['token'] ?? ''));
        return $t !== '' ? $t : trim((string) (SocialConfig::secret('facebook', 'page_token') ?? ''));
    }

    public function test(array $config): array
    {
        return ChannelSupport::guardTest('Instagram', function () use ($config): array {
            $id = trim((string) ($config['account_id'] ?? ''));
            if ($id === '') {
                return $this->bad('The Instagram business account ID is not set.');
            }
            $token = $this->token($config);
            if ($token === '') {
                return $this->bad('No access token: enter one here, or set the Facebook Page token (Instagram uses it when this box is empty).');
            }
            $r = ChannelSupport::graph('instagram', 'GET', $id, $token, ['fields' => 'id,username']);
            if (!$r['ok']) {
                return ChannelSupport::testFailure(ChannelSupport::graphResult('instagram', $r));
            }
            if ((string) ($r['json']['id'] ?? '') !== $id) {
                return $this->bad('Instagram answered with a different account than ' . $id . '. Check the ID.');
            }
            $user = '@' . mb_substr(SocialHttp::redact((string) ($r['json']['username'] ?? 'account')), 0, 40);
            return $this->ok('Connected to the Instagram account ' . $user . '. (Publishing also needs instagram_content_publish and a public image address.)', $user);
        });
    }

    public function post(array $config, SocialPayload $payload): SocialResult
    {
        return ChannelSupport::guardPost('Instagram', function () use ($config, $payload): SocialResult {
            $account = trim((string) ($config['account_id'] ?? ''));
            $token = $this->token($config);
            if ($account === '' || $token === '') {
                return SocialResult::failed('Instagram is not fully set up (account ID and a token).', 'not_configured');
            }
            $image = (string) ($payload->imageUrl ?? '');
            if ($image === '' || preg_match('~^https://[^\s]+$~i', $image) !== 1) {
                if ($payload->test) {
                    return SocialResult::failed('An Instagram test post needs a picture: it uses the share picture of your newest PUBLISHED newsletter issue, and that address must be https (set APP_URL to the https address of the site). Publish an issue first, then send the test post.', 'no_image');
                }
                return SocialResult::failed('Instagram needs a public https share image and none is available for this issue (the picture must be a public JPEG).', 'no_image');
            }
            if ($payload->test) {
                $caption = 'Test post from ' . ($payload->siteName !== '' ? $payload->siteName : 'the website') . ' - ignore (' . date('H:i:s') . ')';
            } else {
                $caption = trim($payload->caption);
                if ($caption === '') {
                    return SocialResult::failed('The Instagram caption is empty.', 'empty_caption');
                }
                if (mb_strlen($caption) > 2200) {
                    return SocialResult::failed('The Instagram caption is longer than 2200 characters.', 'too_long');
                }
                if (preg_match_all('/#[\p{L}\p{N}_]+/u', $caption) > 30) {
                    return SocialResult::failed('Instagram allows at most 30 hashtags in a caption.', 'too_many_hashtags');
                }
            }
            $dl = $payload->deadline;

            // (1) the container: harmless to repeat, so a failure here is a plain "try again", never a "needs review"
            $c = ChannelSupport::graph('instagram', 'POST', $account . '/media', $token, [], ['image_url' => $image, 'caption' => $caption], $dl);
            if (!$c['ok']) {
                $res = ChannelSupport::graphResult('instagram', $c);
                return $res->state === SocialResult::REVIEW ? SocialResult::transient('Instagram did not answer while the picture was being prepared; nothing was published. It will be tried again.', null, 'container_timeout') : $res;
            }
            $creation = is_array($c['json']) ? (string) ($c['json']['id'] ?? '') : '';
            if (preg_match('/^[A-Za-z0-9_-]{1,100}$/', $creation) !== 1) {
                return SocialResult::failed('Instagram did not return a media container id.', 'bad_response');
            }

            // (2) wait (about 6 s) until Instagram says the picture is ready; an unreadable status check falls through to the publish step, which refuses an unready one (code 9007)
            $until = microtime(true) + 6.0;
            while (true) {
                $s = ChannelSupport::graph('instagram', 'GET', $creation, $token, ['fields' => 'status_code'], [], $dl);
                if ($s['ok'] && is_array($s['json'])) {
                    $code = (string) ($s['json']['status_code'] ?? '');
                    if ($code === 'FINISHED' || $code === 'PUBLISHED') {
                        break;
                    }
                    if ($code === 'ERROR' || $code === 'EXPIRED') {
                        return SocialResult::failed('Instagram could not process the picture (status ' . $code . '). It must be a public JPEG between 4:5 and 1.91:1.', 'image_rejected');
                    }
                } elseif (!$s['ok'] && SocialHttp::isAuthFailure('instagram', $s)) {
                    return SocialHttp::toResult('instagram', $s);
                } else {
                    break;
                }
                if (microtime(true) + 1.0 > $until || ($dl !== null && microtime(true) + 2.5 > $dl)) {
                    return SocialResult::transient('Instagram is still preparing the picture; it will be tried again shortly.', 30, 'not_ready');
                }
                usleep(1000000);
            }
            if ($dl !== null && microtime(true) + 1.5 > $dl) {
                return SocialResult::transient('There was no time left to publish; the picture is ready and it will be tried again shortly.', 30, 'budget');
            }

            // (3) publish: NOT idempotent; an ambiguous answer is a "review" that carries the container id so a person can find it
            $p = ChannelSupport::graph('instagram', 'POST', $account . '/media_publish', $token, [], ['creation_id' => $creation], $dl);
            if (!$p['ok']) {
                $res = ChannelSupport::graphResult('instagram', $p);
                return $res->state === SocialResult::REVIEW ? SocialResult::review($res->error, $res->code, $creation) : $res;
            }
            $media = is_array($p['json']) ? (string) ($p['json']['id'] ?? '') : '';
            if (preg_match('/^[A-Za-z0-9_-]{1,100}$/', $media) !== 1) {
                return SocialResult::review('Instagram accepted the publish request but did not return a post id. Check the account, then mark it posted or post again.', 'no_id', $creation);
            }
            $url = null;
            if ($dl === null || microtime(true) + 1.5 < $dl) {
                $pl = ChannelSupport::graph('instagram', 'GET', $media, $token, ['fields' => 'permalink'], [], $dl);
                $link = $pl['ok'] && is_array($pl['json']) ? (string) ($pl['json']['permalink'] ?? '') : '';
                $url = preg_match('~^https://(www\.)?instagram\.com/[A-Za-z0-9_./-]+$~', $link) === 1 ? $link : null;
            }
            return SocialResult::posted($media, $url);
        });
    }
}
