<?php
declare(strict_types=1);

namespace App\Services\Social;

/**
 * Everything an adapter needs to post ONE issue to ONE channel. Built by ShareHook::buildPayload(); adapters never read the database.
 *
 *   $caption   final PLAIN TEXT (already sanitised and within the channel limit; placeholders expanded, {url} carries the UTM tags when enabled)
 *   $url       absolute public address of the issue WITH utm_source=<channel>&utm_medium=social&utm_campaign=newsletter (when the UTM option is on)
 *   $plainUrl  the same address without UTM tags
 *   $imageUrl  absolute PUBLIC https address of the share image for this channel (square for Instagram / Telegram), or null when no image exists.
 *              Comes from SocialCard::publicUrl() (the unguessable /social/image/{token}.jpg route, or the cover image as a fallback).
 *   $images    variant => absolute URL for every variant that exists ('square','portrait','landscape'); the webhook sends all of them
 *   $test      true for "Send a test post": the adapter posts to a private test chat where it has one (Telegram) / sends "test": true (webhook)
 *   $deadline  absolute time (microtime(true)) by which the adapter must be done; pass it to SocialHttp as the `deadline` option
 *   $eventId   stable id of this post ("lumina-post-123"): the webhook sends it so receivers can drop duplicates; others may ignore it
 */
final class SocialPayload
{
    /**
     * @param list<string>          $hashtags
     * @param array<string,string>  $images
     */
    public function __construct(
        public readonly string $channel,
        public readonly int $postId,
        public readonly int $newsletterId,
        public readonly string $title,
        public readonly string $summary,
        public readonly string $url,
        public readonly string $plainUrl,
        public readonly string $caption,
        public readonly ?string $imageUrl = null,
        public readonly array $images = [],
        public readonly array $hashtags = [],
        public readonly ?int $issueNumber = null,
        public readonly string $issueDate = '',
        public readonly bool $test = false,
        public readonly ?float $deadline = null,
        public readonly string $eventId = '',
        public readonly string $siteName = '',
    ) {
    }
}
