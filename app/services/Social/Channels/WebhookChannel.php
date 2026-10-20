<?php
declare(strict_types=1);

namespace App\Services\Social\Channels;

use App\Services\Social\AbstractChannel;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\SocialConfig;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialResult;

/**
 * WEBHOOK (Zapier / Make / IFTTT / Buffer ...) - the route for TikTok and every platform without a direct channel (docs/SOCIAL.md section 4.6).
 * REFERENCE IMPLEMENTATION by the architect (the CHANNELS agent keeps it working and extends the conformance tests).
 *
 *   The URL is typed by an admin: SocialHttp::webhook() applies the full SSRF guard (https only, port 443 / 8443, no user info, DNS to public addresses only,
 *   pinned connection, redirects re-checked, max 2).
 *   POST JSON, headers: Content-Type: application/json, X-Lumina-Event (newsletter.published | test.ping), X-Lumina-Delivery (the event id), X-Lumina-Timestamp (unix seconds),
 *   X-Lumina-Signature = hex HMAC-SHA256 of the EXACT request body with the signing secret (only when a secret is set).
 *   Body: {"event","event_id","timestamp" (inside the signed body, so receivers can reject old messages),"test":bool,"site", and the chosen fields:
 *   title, summary, url + plain_url, images {square,portrait,landscape} + image_url, captions {facebook,instagram,x,linkedin,telegram,tiktok,default},
 *   hashtags [..], issue_number, date}. Empty field setting = all of them.
 *   Success = any 2xx. Retrying is safe (the receiver can drop duplicates by event_id), so an ambiguous timeout is retried, not sent to review.
 */
final class WebhookChannel extends AbstractChannel
{
    public const FIELD_OPTIONS = ['title' => 'Title', 'summary' => 'Summary', 'url' => 'Issue link', 'images' => 'Share image links', 'captions' => 'Ready-made captions (all platforms, incl. TikTok)', 'hashtags' => 'Hashtags', 'issue_number' => 'Issue number', 'date' => 'Issue date'];

    protected const META = [
        'id' => 'webhook', 'label' => 'Webhook (TikTok and others)', 'icon' => 'link', 'limit' => 5000, 'soft' => 1000, 'links' => 'clickable', 'uses_image' => true, 'image_variant' => 'square',
        'ambiguous' => 'retry', 'test_post' => 'webhook_flag',
        'short' => 'Sends the issue (title, summary, link, image links, ready captions) to Zapier, Make, IFTTT or Buffer, which can post it to TikTok and anywhere else.',
        'needs' => 'A free account with an automation tool (Zapier, Make, IFTTT) or a scheduling tool such as Buffer that gives you a webhook address.',
        'setup_url' => 'https://zapier.com/apps/webhooks/integrations',
        'steps' => [
            'In Zapier choose the trigger "Webhooks by Zapier > Catch Hook" (Make: "Webhooks > Custom webhook"; IFTTT: "Webhooks"). Copy the https address it gives you.',
            'Paste that address below. Optional: type a signing secret; every message then carries an X-Lumina-Signature header (HMAC-SHA256 of the body) that the receiving tool can check.',
            'Press "Test connection": a harmless test message is sent ("test": true) so the tool can learn the fields.',
            'In the automation add the actions you want (a TikTok or Buffer post, a message to a team chat, a Google Sheet row ...). The ready captions and image links are in the message.',
        ],
        'permissions' => 'None here. The permissions belong to the automation tool\'s own connections.',
        'notes' => [
            'TikTok has NO direct channel on purpose: its Content Posting API only takes video (or photo posts from apps that TikTok has audited), apps that are not audited can only create private drafts, and it cannot carry a text + link newsletter. Use this webhook with an automation tool, or post the Share kit by hand: it takes a minute.',
            'Only https addresses on the public internet are accepted (port 443 or 8443); private and local addresses are refused.',
        ],
        'fields' => [
            ['key' => 'url', 'label' => 'Webhook address', 'type' => 'secret', 'required' => true, 'legacy' => true, 'env' => 'SOCIAL_WEBHOOK_URL', 'placeholder' => 'https://hooks.zapier.com/hooks/catch/123456/abcdef/', 'maxlength' => 1000,
             'help' => 'An https address from your automation tool (like https://hooks.zapier.com/...). Treat it like a password: anyone who has it can send fake events into your automations. Stored encrypted; never shown again.'],
            ['key' => 'secret', 'label' => 'Signing secret (optional)', 'type' => 'secret', 'required' => false, 'env' => 'SOCIAL_WEBHOOK_SECRET', 'pattern' => '/^[\x21-\x7e]{8,200}$/', 'maxlength' => 200,
             'help' => 'If set, every message is signed (X-Lumina-Signature). Stored encrypted; never shown again.'],
            ['key' => 'fields', 'label' => 'What to send', 'type' => 'multi', 'required' => false, 'options' => self::FIELD_OPTIONS, 'default' => '', 'maxlength' => 200,
             'help' => 'Leave all unticked to send everything.'],
        ],
    ];

    public function validate(array $input): array
    {
        $errors = parent::validate($input);
        $url = trim((string) ($input['url'] ?? ''));
        if ($url !== '' && !isset($errors['url'])) {
            $g = SocialHttp::guardWebhookUrl($url);
            if (!$g['ok']) {
                $errors['url'] = (string) $g['error'];
            }
        }
        return $errors;
    }

    /**
     * FAIL CLOSED: a signing secret that is stored but cannot be read (the APP_KEY changed) must stop the channel, never turn into an UNSIGNED message.
     * @return ?string the reason, or null when it is fine
     */
    private function unreadableSecret(): ?string
    {
        $st = SocialConfig::secretStatus('webhook', 'secret');
        return $st['set'] && !$st['readable'] ? 'The stored signing secret cannot be read (the application key changed). Nothing was sent: enter the signing secret again (or remove it) in Admin > Social media > Channels.' : null;
    }

    public function test(array $config): array
    {
        $url = trim((string) ($config['url'] ?? ''));
        if ($url === '') {
            return $this->bad('The webhook address is not set.');
        }
        if (($why = $this->unreadableSecret()) !== null) {
            return $this->bad($why, 'auth');
        }
        $data = [
            'event' => 'test.ping', 'event_id' => 'lumina-test-' . bin2hex(random_bytes(6)), 'timestamp' => time(), 'test' => true, 'site' => (string) setting('site_name', ''),
            'title' => 'Connection test', 'summary' => 'This is a test message from the website. Nothing was published.', 'url' => abs_url(''), 'plain_url' => abs_url(''),
        ];
        $r = $this->send($config, $data, null);
        if ($r['ok']) {
            return $this->ok('The webhook answered with HTTP ' . (int) $r['status'] . '. A test message ("test": true) was sent.', 'HTTP ' . (int) $r['status']);
        }
        [$state, $msg] = SocialHttp::describeFailure('webhook', $r);
        return $this->bad($msg, $state);
    }

    public function post(array $config, SocialPayload $payload): SocialResult
    {
        if (trim((string) ($config['url'] ?? '')) === '') {
            return SocialResult::failed('The webhook address is not set.', 'not_configured');
        }
        if (($why = $this->unreadableSecret()) !== null) {
            return SocialResult::auth($why, 'secret_unreadable');
        }
        $r = $this->send($config, $this->body($config, $payload), $payload);
        if ($r['ok']) {
            $id = is_array($r['json'] ?? null) && isset($r['json']['id']) && is_scalar($r['json']['id']) ? (string) $r['json']['id'] : null;
            return SocialResult::posted($id, null);
        }
        $res = SocialHttp::toResult('webhook', $r);
        if ($res->state === SocialResult::REVIEW) {
            return SocialResult::transient($res->error, null, 'ambiguous_retry');          // the receiver de-duplicates by event_id: retrying is safe
        }
        return $res;
    }

    /** @return array<string,mixed> the JSON object that is sent (also used by the tests and the mock) */
    public function body(array $config, SocialPayload $p): array
    {
        $chosen = array_values(array_filter(array_map('trim', explode(',', (string) ($config['fields'] ?? '')))));
        $all = $chosen === [];
        $on = static fn(string $f): bool => $all || in_array($f, $chosen, true);
        $data = ['event' => $p->test ? 'test.ping' : 'newsletter.published', 'event_id' => $p->eventId !== '' ? $p->eventId : 'lumina-post-' . $p->postId, 'timestamp' => time(), 'test' => $p->test, 'site' => $p->siteName];
        if ($on('title')) {
            $data['title'] = $p->title;
        }
        if ($on('summary')) {
            $data['summary'] = $p->summary;
        }
        if ($on('url')) {
            $data['url'] = $p->url;
            $data['plain_url'] = $p->plainUrl;
        }
        if ($on('images')) {
            $data['images'] = (object) $p->images;
            $data['image_url'] = $p->imageUrl;
        }
        if ($on('captions')) {
            $issue = ['title' => $p->title, 'summary' => $p->summary, 'url' => $p->plainUrl, 'issue_number' => $p->issueNumber, 'issue_date' => $p->issueDate, 'site' => $p->siteName];
            $caps = [];
            foreach (['facebook', 'instagram', 'x', 'linkedin', 'telegram', 'tiktok'] as $ch) {
                $caps[$ch] = CaptionBuilder::build($ch, $issue)['text'];
            }
            $caps['default'] = $p->caption;
            $data['captions'] = $caps;
        }
        if ($on('hashtags')) {
            $data['hashtags'] = $p->hashtags;
        }
        if ($on('issue_number')) {
            $data['issue_number'] = $p->issueNumber;
        }
        if ($on('date')) {
            $data['date'] = $p->issueDate;
        }
        return $data;
    }

    /**
     * @param array<string,string> $config
     * @param array<string,mixed> $data
     * @return array<string,mixed> SocialHttp result
     */
    private function send(array $config, array $data, ?SocialPayload $p): array
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        $body = $json === false ? '{}' : $json;
        $headers = ['Content-Type' => 'application/json', 'X-Lumina-Event' => (string) $data['event'], 'X-Lumina-Delivery' => (string) $data['event_id'], 'X-Lumina-Timestamp' => (string) $data['timestamp']];
        $secret = trim((string) ($config['secret'] ?? ''));
        if ($secret !== '') {
            $headers['X-Lumina-Signature'] = SocialHttp::sign($body, $secret);
        }
        return SocialHttp::webhook((string) $config['url'], $body, $headers, ['deadline' => $p?->deadline]);
    }
}
