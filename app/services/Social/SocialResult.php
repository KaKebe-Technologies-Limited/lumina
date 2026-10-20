<?php
declare(strict_types=1);

namespace App\Services\Social;

/**
 * What a channel adapter answers after ONE attempt to post (ChannelInterface::post). The dispatcher turns it into a queue transition (PostQueue):
 *
 *   state      meaning                                                              queue status afterwards
 *   posted     the platform accepted the post (remoteId / remoteUrl when it said)   posted
 *   transient  nothing was posted and trying again later may work (429, 5xx,        queued with exponential backoff (60 s, 5 min, 15 min, 1 h, 3 h),
 *              connection refused BEFORE the request was sent, timeout while        "failed" after 5 attempts; `retryAfter` (seconds) overrides the
 *              connecting)                                                          next delay when the platform sent one
 *   failed     refused for a reason a retry will not fix (bad caption, wrong page   failed (an admin may edit the settings and press Retry)
 *              id, image rejected, adapter not built)
 *   auth       the credentials were rejected / expired                              failed + the channel is marked "needs a new token", the admin is
 *                                                                                  told once (dashboard + e-mail)
 *   review     AMBIGUOUS: the request may or may not have been executed (timeout     review (never retried by itself: a person looks at the real
 *              after sending, connection dropped after sending, HTTP 502 / 504)      account and chooses "Mark as posted", "Retry anyway" or "Skip")
 *
 * `error` is a SHORT human text that never contains a token (SocialHttp::redact is applied by the constructors). `code` is a machine word.
 */
final class SocialResult
{
    public const POSTED = 'posted';
    public const TRANSIENT = 'transient';
    public const FAILED = 'failed';
    public const AUTH = 'auth';
    public const REVIEW = 'review';

    private function __construct(
        public readonly string $state,
        public readonly ?string $remoteId = null,
        public readonly ?string $remoteUrl = null,
        public readonly string $error = '',
        public readonly string $code = '',
        public readonly ?int $retryAfter = null,
    ) {
    }

    public static function posted(?string $remoteId = null, ?string $remoteUrl = null): self
    {
        return new self(self::POSTED, self::clipId($remoteId), $remoteUrl !== null ? mb_substr($remoteUrl, 0, 255) : null);
    }

    public static function transient(string $error, ?int $retryAfter = null, string $code = 'unavailable'): self
    {
        return new self(self::TRANSIENT, null, null, self::clean($error), $code, $retryAfter !== null ? max(1, min(86400, $retryAfter)) : null);
    }

    public static function failed(string $error, string $code = 'rejected'): self
    {
        return new self(self::FAILED, null, null, self::clean($error), $code);
    }

    public static function auth(string $error, string $code = 'auth'): self
    {
        return new self(self::AUTH, null, null, self::clean($error), $code);
    }

    /** Ambiguous outcome. $remoteId may carry an id the platform already gave for a first step (Instagram container) so a person can find it. */
    public static function review(string $error, string $code = 'ambiguous', ?string $remoteId = null): self
    {
        return new self(self::REVIEW, self::clipId($remoteId), null, self::clean($error), $code);
    }

    public function isPosted(): bool
    {
        return $this->state === self::POSTED;
    }

    /** @return array<string,mixed> safe to put in JSON / logs */
    public function toArray(): array
    {
        return ['state' => $this->state, 'remote_id' => $this->remoteId, 'remote_url' => $this->remoteUrl, 'error' => $this->error, 'code' => $this->code, 'retry_after' => $this->retryAfter];
    }

    private static function clean(string $error): string
    {
        return mb_substr(SocialHttp::redact(trim((string) preg_replace('/\s+/u', ' ', $error))), 0, 240);
    }

    private static function clipId(?string $id): ?string
    {
        return $id === null || $id === '' ? null : mb_substr($id, 0, 190);
    }
}
