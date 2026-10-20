<?php
declare(strict_types=1);

namespace App\Services\Social\Channels;

use App\Services\Social\AbstractChannel;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialResult;

/**
 * TELEGRAM CHANNEL via the Bot API (docs/SOCIAL.md section 4.5). REFERENCE IMPLEMENTATION by the architect (the CHANNELS agent keeps it working and extends its
 * mock-server conformance tests; it must not change the behaviour described here).
 *
 *   test():  getMe (the bot) -> getChat(chat) (the channel exists and the bot can see it) -> getChatMember(chat, bot id): for a CHANNEL the bot must be an administrator.
 *            The optional test chat is checked with getChat too. Read-only.
 *   post():  with an image: sendPhoto {chat_id, photo: <public image url>, caption} (caption cut at Telegram's 1024); without: sendMessage {chat_id, text}.
 *            PLAIN TEXT (no parse_mode), so nothing in a title can be read as markup; Telegram turns links into links by itself. A sendPhoto that Telegram refuses
 *            for the PICTURE (400 about the photo / url) falls back once to a text message (a 400 means nothing was posted, so this cannot duplicate).
 *            remote_id = message_id, remote_url = https://t.me/<username>/<id> (public channel) or https://t.me/c/<internal id>/<id>.
 *            Test post: a one-line sendMessage to the TEST chat when one is configured, else to the real chat. Never an image.
 *   The bot token is part of the URL path (Telegram's design): SocialHttp never logs or returns URLs, and every error text goes through redact().
 *   429: retry_after from the answer. Ambiguous timeout -> review (no idempotency key).
 */
final class TelegramChannel extends AbstractChannel
{
    protected const META = [
        'id' => 'telegram', 'label' => 'Telegram channel', 'icon' => 'telegram', 'limit' => 1024, 'soft' => 800, 'links' => 'clickable', 'uses_image' => true, 'image_variant' => 'square',
        'test_post' => 'test_chat',
        'short' => 'Posts the share image with the caption and link to your Telegram channel.',
        'needs' => 'A Telegram bot (made with @BotFather) that is an administrator of your channel.',
        'setup_url' => 'https://core.telegram.org/bots#how-do-i-create-a-bot',
        'steps' => [
            'In Telegram, open a chat with @BotFather, send /newbot and follow the questions. It gives you the bot token (looks like 123456789:ABC...). Keep it secret.',
            'Open your channel > Administrators > Add administrator, find your bot and give it the right to "Post messages".',
            'Enter the channel as @channelname (public channel) or as its number (private channel, it starts with -100).',
            'Optional but recommended: make a small private channel or chat for tests, add the bot there too, and enter it as the test chat: "Send a test post" then goes there and never to your public channel.',
            'Paste the token and the channel below and press "Test connection".',
        ],
        'permissions' => 'The bot must be an administrator of the channel with "Post messages".',
        'notes' => ['Telegram needs no app review. Captions longer than 1024 characters are not allowed with a picture.'],
        'fields' => [
            ['key' => 'bot_token', 'label' => 'Bot token', 'type' => 'secret', 'required' => true, 'env' => 'SOCIAL_TG_BOT_TOKEN', 'pattern' => '/^\d{5,}:[A-Za-z0-9_\-]{20,80}$/', 'maxlength' => 120,
             'help' => 'From @BotFather. Stored encrypted; never shown again.'],
            ['key' => 'chat', 'label' => 'Channel', 'type' => 'text', 'required' => true, 'pattern' => '/^(@[A-Za-z][A-Za-z0-9_]{4,31}|-?\d{5,20})$/', 'placeholder' => '@luminaafrica', 'maxlength' => 40,
             'help' => '@name of a public channel, or the number of a private one.'],
            ['key' => 'test_chat', 'label' => 'Test chat (optional)', 'type' => 'text', 'required' => false, 'pattern' => '/^(@[A-Za-z][A-Za-z0-9_]{4,31}|-?\d{5,20})$/', 'placeholder' => '-1001234567890', 'maxlength' => 40,
             'help' => 'A private chat or channel where "Send a test post" goes. Empty = the real channel.'],
        ],
    ];

    public function test(array $config): array
    {
        $token = trim((string) ($config['bot_token'] ?? ''));
        if ($token === '') {
            return $this->bad('The bot token is not set.');
        }
        $me = $this->call($token, 'getMe', []);
        if (!$me['ok'] || ($me['json']['ok'] ?? false) !== true) {
            [$state, $msg] = SocialHttp::describeFailure('telegram', $me);
            return $this->bad($msg, $state);
        }
        $bot = $me['json']['result'] ?? [];
        $botName = '@' . (string) ($bot['username'] ?? 'bot');
        $botId = (int) ($bot['id'] ?? 0);
        $chats = array_filter([(string) ($config['chat'] ?? ''), (string) ($config['test_chat'] ?? '')], static fn(string $c): bool => $c !== '');
        if ($chats === []) {
            return $this->bad('The bot token works (' . $botName . '), but no channel is entered yet.');
        }
        $title = '';
        foreach ($chats as $i => $chat) {
            $g = $this->call($token, 'getChat', ['chat_id' => $chat]);
            if (!$g['ok'] || ($g['json']['ok'] ?? false) !== true) {
                [, $msg] = SocialHttp::describeFailure('telegram', $g);
                return $this->bad(($i === 0 ? 'The channel' : 'The test chat') . ' could not be opened: ' . $msg . ' Check the name and that the bot was added.');
            }
            $info = $g['json']['result'] ?? [];
            $title = $title !== '' ? $title : (string) ($info['title'] ?? $info['username'] ?? '');
            if (($info['type'] ?? '') === 'channel' && $botId > 0) {
                $m = $this->call($token, 'getChatMember', ['chat_id' => $chat, 'user_id' => $botId]);
                $status = (string) ($m['json']['result']['status'] ?? '');
                if (!$m['ok'] || !in_array($status, ['administrator', 'creator'], true)) {
                    return $this->bad($botName . ' is not an administrator of ' . ($i === 0 ? 'the channel' : 'the test chat') . '. Add the bot as an administrator with the right to post messages.');
                }
            }
        }
        return $this->ok('Connected as ' . $botName . ($title !== '' ? ' to "' . $title . '"' : '') . '.', $botName);
    }

    public function post(array $config, SocialPayload $payload): SocialResult
    {
        $token = trim((string) ($config['bot_token'] ?? ''));
        $chat = trim((string) (($payload->test && trim((string) ($config['test_chat'] ?? '')) !== '') ? $config['test_chat'] : ($config['chat'] ?? '')));
        if ($token === '' || $chat === '') {
            return SocialResult::failed('Telegram is not fully set up (bot token and channel).', 'not_configured');
        }
        $opts = ['deadline' => $payload->deadline];
        if ($payload->test) {
            $r = $this->call($token, 'sendMessage', ['chat_id' => $chat, 'text' => 'Test post from ' . ($payload->siteName !== '' ? $payload->siteName : 'the website') . ': checking that posting works. Please ignore.', 'disable_web_page_preview' => true], $opts);
        } elseif ($payload->imageUrl !== null && $payload->imageUrl !== '') {
            $r = $this->call($token, 'sendPhoto', ['chat_id' => $chat, 'photo' => $payload->imageUrl, 'caption' => mb_substr($payload->caption, 0, 1024)], $opts);
            if (!$r['ok'] && (int) $r['status'] === 400 && preg_match('/photo|http url|wrong|image|file/i', SocialHttp::apiMessage($r)) === 1) {
                $r = $this->call($token, 'sendMessage', ['chat_id' => $chat, 'text' => mb_substr($payload->caption, 0, 4096)], $opts);
            }
        } else {
            $r = $this->call($token, 'sendMessage', ['chat_id' => $chat, 'text' => mb_substr($payload->caption, 0, 4096)], $opts);
        }
        if ($r['ok'] && ($r['json']['ok'] ?? false) === true) {
            $res = $r['json']['result'] ?? [];
            $mid = (string) ($res['message_id'] ?? '');
            return SocialResult::posted($mid !== '' ? $mid : null, $mid !== '' ? $this->messageUrl($res, $mid) : null);
        }
        if ((int) $r['status'] === 429) {
            $wait = (int) ($r['json']['parameters']['retry_after'] ?? 0);
            return SocialResult::transient('Telegram asks us to slow down (rate limit).', $wait > 0 ? $wait : ($r['retry_after'] ?? null), 'rate_limited');
        }
        if ($r['ok']) {
            // HTTP 2xx but no readable {"ok":true,...}: the message may well have been sent (Telegram has no idempotency key), so a person decides, as for every other platform
            return SocialResult::review('Telegram answered in an unexpected way. The message may or may not have been posted: check the channel.', 'bad_response');
        }
        return SocialHttp::toResult('telegram', $r);
    }

    /** @param array<string,mixed> $result Telegram's `result` object of a sent message */
    private function messageUrl(array $result, string $messageId): ?string
    {
        $chat = (array) ($result['chat'] ?? []);
        $user = (string) ($chat['username'] ?? '');
        if ($user !== '' && preg_match('/^[A-Za-z][A-Za-z0-9_]{3,31}$/', $user) === 1) {
            return 'https://t.me/' . $user . '/' . $messageId;
        }
        $id = (string) ($chat['id'] ?? '');
        if (preg_match('/^-100(\d{5,15})$/', $id, $m) === 1) {
            return 'https://t.me/c/' . $m[1] . '/' . $messageId;
        }
        return null;
    }

    /**
     * @param array<string,mixed> $body
     * @param array<string,mixed> $opts
     * @return array<string,mixed>
     */
    private function call(string $token, string $method, array $body, array $opts = []): array
    {
        return SocialHttp::api('telegram', 'POST', '/bot' . $token . '/' . $method, $opts + ['json' => $body]);
    }
}
