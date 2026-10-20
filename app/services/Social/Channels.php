<?php
declare(strict_types=1);

namespace App\Services\Social;

use App\Services\Social\Channels\FacebookChannel;
use App\Services\Social\Channels\InstagramChannel;
use App\Services\Social\Channels\LinkedInChannel;
use App\Services\Social\Channels\TelegramChannel;
use App\Services\Social\Channels\WebhookChannel;
use App\Services\Social\Channels\XChannel;

/**
 * Registry of the channels, in the order they are shown and posted. `facebook instagram x linkedin telegram webhook`.
 * TikTok (and anything else) has NO direct channel: it is served by the webhook (Zapier / Make / IFTTT / Buffer) and the Share kit (docs/SOCIAL.md section 1).
 * Tests replace an adapter with a fake through override() and put the real ones back with reset().
 */
final class Channels
{
    public const IDS = ['facebook', 'instagram', 'x', 'linkedin', 'telegram', 'webhook'];
    /** extra names with a caption template but no adapter: the Share kit has one for TikTok */
    public const KIT_ONLY = ['tiktok'];

    /** @var array<string,ChannelInterface> */
    private static array $instances = [];
    /** @var array<string,ChannelInterface> */
    private static array $overrides = [];

    /** @return list<string> */
    public static function ids(): array
    {
        return self::IDS;
    }

    public static function has(string $id): bool
    {
        return in_array($id, self::IDS, true);
    }

    public static function get(string $id): ChannelInterface
    {
        if (isset(self::$overrides[$id])) {
            return self::$overrides[$id];
        }
        if (!self::has($id)) {
            throw new \InvalidArgumentException('Unknown social channel.');
        }
        return self::$instances[$id] ??= match ($id) {
            'facebook'  => new FacebookChannel(),
            'instagram' => new InstagramChannel(),
            'x'         => new XChannel(),
            'linkedin'  => new LinkedInChannel(),
            'telegram'  => new TelegramChannel(),
            'webhook'   => new WebhookChannel(),
        };
    }

    /** @return array<string,ChannelInterface> id => adapter */
    public static function all(): array
    {
        $out = [];
        foreach (self::IDS as $id) {
            $out[$id] = self::get($id);
        }
        return $out;
    }

    /** @return array<string,mixed> */
    public static function meta(string $id): array
    {
        return self::get($id)->meta();
    }

    public static function label(string $id): string
    {
        return self::has($id) ? (string) self::get($id)->meta()['label'] : ($id === 'tiktok' ? 'TikTok' : ucfirst($id));
    }

    /** Test hook: use $channel instead of the real adapter for $id. */
    public static function override(string $id, ChannelInterface $channel): void
    {
        self::$overrides[$id] = $channel;
    }

    public static function reset(): void
    {
        self::$overrides = [];
        self::$instances = [];
    }
}
