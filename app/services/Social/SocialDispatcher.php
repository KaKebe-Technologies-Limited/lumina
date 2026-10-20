<?php
declare(strict_types=1);

namespace App\Services\Social;

use App\Core\App;
use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\Settings;
use App\Models\Newsletter;
use App\Services\Newsletter\NewsletterService;

/**
 * Posts the queued rows (docs/SOCIAL.md section 5). Three callers, one code path (attempt()):
 *   1. IMMEDIATELY after a publish / go-live: afterResponse($issueId) runs dispatchNewsletter() once the HTTP response has been sent (never delays or breaks publishing),
 *   2. bin/social-dispatch.php (cron, every 5 minutes): runDue(),
 *   3. the lazy retry on admin page loads (at most once a minute, after the response): lazyRetry().
 *
 * TIME BUDGETS: per channel connect 4 s and total 10 s (SocialHttp), channels in sequence, 25 s for the whole immediate run (12 s when the host cannot detach from
 * the request). Rows that did not get their turn stay queued and are picked up by the cron / lazy retry.
 * RETRIES: a transient problem puts the row back with the backoff of PostQueue (60 s, 5 min, 15 min, 1 h, 3 h; failed after 5 attempts). An ambiguous outcome is `review`,
 * never retried by itself. Concurrent runs are safe: PostQueue::claim() is one conditional UPDATE, runDue() also holds the named lock lumina_social_dispatch.
 * AUTH FAILURES (token rejected / expired): the post is `failed`, the channel is marked "needs a new token" (SocialConfig::status), no other post is attempted on it, and the
 * admin is told ONCE per episode on the dashboard (SocialConfig::attention) and by e-mail (Mailer::notify). Pasting a new token resets all of that.
 */
final class SocialDispatcher
{
    public const OVERALL_BUDGET = 25.0;
    public const LOCK = 'lumina_social_dispatch';
    public const LAZY_EVERY = 60;

    // ------------------------------------------------------------------------------------------------ immediate dispatch

    /**
     * Run dispatchNewsletter() right after the response has been sent. On a public request (a visitor's page view flipped a scheduled issue live) it only runs when the host
     * can detach from the request (PHP-FPM / LiteSpeed): otherwise the cron job picks the rows up. In the CLI it runs at once.
     */
    public static function afterResponse(int $newsletterId): void
    {
        try {
            $canDetach = function_exists('fastcgi_finish_request') || function_exists('litespeed_finish_request');
            if (PHP_SAPI !== 'cli' && !$canDetach && !Auth::check()) {
                return;
            }
            $budget = $canDetach || PHP_SAPI === 'cli' ? self::OVERALL_BUDGET : 12.0;
            App::afterResponse(static function () use ($newsletterId, $budget): void {
                SocialDispatcher::dispatchNewsletter($newsletterId, $budget);
            });
        } catch (\Throwable $e) {
            Log::warning('Social: could not schedule the dispatch - ' . SocialHttp::redact($e->getMessage()));
        }
    }

    /**
     * Try every due queued row of one issue now, channels in sequence, within $budget seconds.
     * @return array{attempted:int,posted:int,failed:int,queued:int,review:int,skipped:int,deferred:int}
     */
    public static function dispatchNewsletter(int $newsletterId, float $budget = self::OVERALL_BUDGET): array
    {
        $sum = ['attempted' => 0, 'posted' => 0, 'failed' => 0, 'queued' => 0, 'review' => 0, 'skipped' => 0, 'deferred' => 0];
        try {
            @set_time_limit((int) ceil($budget) + 20);
            $deadline = microtime(true) + $budget;
            foreach (PostQueue::due(50, $newsletterId) as $id) {
                if (microtime(true) > $deadline - 1.0) {
                    $sum['deferred']++;
                    continue;
                }
                self::tally($sum, self::attempt($id, $deadline));
            }
        } catch (\Throwable $e) {
            Log::warning('Social: dispatch failed - ' . SocialHttp::redact($e->getMessage()));
        }
        return $sum;
    }

    /**
     * One attempt for one row: claim it (recorded BEFORE the call), post, and write the outcome.
     * @return array{state:string,status:string,error:string}  state: posted | transient | failed | auth | review | skipped | busy | deferred
     */
    public static function attempt(int $postId, ?float $deadline = null): array
    {
        $row = PostQueue::claim($postId);
        if ($row === null) {
            return ['state' => 'busy', 'status' => '', 'error' => ''];
        }
        try {
            $channel = (string) $row['channel'];
            $news = Newsletter::find((int) $row['newsletter_id']);
            if ($news === null || !NewsletterService::isLive($news)) {
                PostQueue::markSkipped($postId, 'The issue is not public (draft, unpublished or archived), so nothing was posted.');
                return ['state' => 'skipped', 'status' => 'skipped', 'error' => ''];
            }
            if (!Channels::has($channel)) {
                PostQueue::markFailed($postId, 'Unknown channel.');
                return ['state' => 'failed', 'status' => 'failed', 'error' => 'Unknown channel.'];
            }
            $ready = SocialConfig::ready($channel);
            if (!$ready['ready']) {
                if (in_array($ready['state'], ['needs_token', 'expired'], true)) {
                    PostQueue::markFailed($postId, (string) $ready['reasons'][0], 'auth');
                    return ['state' => 'auth', 'status' => 'failed', 'error' => (string) $ready['reasons'][0]];
                }
                PostQueue::markSkipped($postId, 'Not posted: ' . (string) $ready['reasons'][0] . ' Press Retry after fixing it.');
                return ['state' => 'skipped', 'status' => 'skipped', 'error' => (string) $ready['reasons'][0]];
            }
            $payload = ShareHook::buildPayload($row, $news, $deadline);
            try {
                $res = Channels::get($channel)->post(SocialConfig::channelConfig($channel), $payload);
            } catch (\Throwable $e) {
                $res = SocialResult::review('Something went wrong while posting (' . (new \ReflectionClass($e))->getShortName() . '). It may or may not have been posted: check the account.', 'exception');
            }
            return self::record($postId, $channel, $res);
        } catch (\Throwable $e) {
            PostQueue::markReview($postId, 'An unexpected error stopped the attempt. It may or may not have been posted: check the account.');
            Log::warning('Social: attempt failed - ' . SocialHttp::redact($e->getMessage()));
            return ['state' => 'review', 'status' => 'review', 'error' => 'unexpected error'];
        }
    }

    /** @return array{state:string,status:string,error:string} */
    private static function record(int $postId, string $channel, SocialResult $res): array
    {
        switch ($res->state) {
            case SocialResult::POSTED:
                PostQueue::markPosted($postId, $res->remoteId, $res->remoteUrl);
                if (SocialConfig::status($channel)['state'] !== 'ok') {
                    SocialConfig::setStatus($channel, 'ok', 'The last post went through.');
                }
                if ((string) Settings::get(SocialConfig::key($channel, 'notified'), '') !== '') {
                    Settings::set(SocialConfig::key($channel, 'notified'), '');
                }
                return ['state' => 'posted', 'status' => 'posted', 'error' => ''];
            case SocialResult::AUTH:
                PostQueue::markFailed($postId, 'The platform rejected the access token. ' . $res->error, 'auth');
                SocialConfig::setStatus($channel, 'needs_token', $res->error);
                self::notifyOnce($channel);
                return ['state' => 'auth', 'status' => 'failed', 'error' => $res->error];
            case SocialResult::REVIEW:
                PostQueue::markReview($postId, $res->error, $res->remoteId);
                return ['state' => 'review', 'status' => 'review', 'error' => $res->error];
            case SocialResult::TRANSIENT:
                if ($res->code === 'budget') {
                    PostQueue::release($postId, 30);                               // no time was left: this try does not count
                    return ['state' => 'deferred', 'status' => 'queued', 'error' => ''];
                }
                $to = PostQueue::markTransient($postId, $res->error, $res->retryAfter);
                SocialConfig::setStatus($channel, 'error', $res->error);
                return ['state' => 'transient', 'status' => $to, 'error' => $res->error];
            default:
                PostQueue::markFailed($postId, $res->error !== '' ? $res->error : 'The post was refused.');
                return ['state' => 'failed', 'status' => 'failed', 'error' => $res->error];
        }
    }

    /** @param array<string,int> $sum @param array{state:string,status:string,error:string} $r */
    private static function tally(array &$sum, array $r): void
    {
        $sum['attempted']++;
        match ($r['state']) {
            'posted'    => $sum['posted']++,
            'transient' => $r['status'] === 'failed' ? $sum['failed']++ : $sum['queued']++,
            'failed', 'auth' => $sum['failed']++,
            'review'    => $sum['review']++,
            'skipped'   => $sum['skipped']++,
            'deferred'  => $sum['deferred']++,
            default     => $sum['attempted']--,
        };
    }

    // ------------------------------------------------------------------------------------------------ cron and lazy retry

    /**
     * Everything that is due: stuck rows are reclaimed, due rows are attempted (a channel that just failed transiently is not hammered again in the same run), token
     * expiry reminders are sent. Holds the named lock, so two runs never overlap.
     * @return array{busy:bool,attempted:int,posted:int,failed:int,queued:int,review:int,skipped:int,deferred:int,reclaimed:int}
     */
    public static function runDue(int $max = 20, float $budget = 240.0): array
    {
        $sum = ['busy' => false, 'attempted' => 0, 'posted' => 0, 'failed' => 0, 'queued' => 0, 'review' => 0, 'skipped' => 0, 'deferred' => 0, 'reclaimed' => 0];
        $locked = (int) Database::value('SELECT GET_LOCK(?, 0)', [self::LOCK], 0) === 1;
        if (!$locked) {
            $sum['busy'] = true;
            return $sum;
        }
        try {
            @set_time_limit((int) ceil($budget) + 20);
            $deadline = microtime(true) + $budget;
            $sum['reclaimed'] = PostQueue::reclaimStuck();
            self::remindExpiry();
            $hot = [];
            foreach (PostQueue::due($max) as $id) {
                if (microtime(true) > $deadline - 1.0) {
                    $sum['deferred']++;
                    continue;
                }
                $channel = (string) Database::value('SELECT channel FROM social_posts WHERE id = ?', [$id], '');
                if (isset($hot[$channel])) {
                    continue;                                                     // it just failed: leave the rest of that channel for the next run
                }
                $r = self::attempt($id, $deadline);
                self::tally($sum, $r);
                if (in_array($r['state'], ['transient', 'auth', 'review'], true)) {
                    $hot[$channel] = true;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Social: dispatch run failed - ' . SocialHttp::redact($e->getMessage()));
        } finally {
            Database::value('SELECT RELEASE_LOCK(?)', [self::LOCK]);
        }
        return $sum;
    }

    /** Is there anything for the retry hook to do? Cheap. */
    public static function needsWork(): bool
    {
        $now = PostQueue::now();
        $due = (int) Database::value("SELECT COUNT(*) FROM social_posts WHERE (status = 'queued' AND (next_attempt_at IS NULL OR next_attempt_at <= ?)) OR (status = 'posting' AND last_attempt_at < ?)", [$now, date('Y-m-d H:i:s', time() - PostQueue::STUCK_SECONDS)], 0);
        if ($due > 0) {
            return true;
        }
        foreach (SocialConfig::expiryWarnings() as $w) {
            if ((string) Settings::get(SocialConfig::key($w['channel'], 'expiry_notified'), '') !== $w['date']) {
                return true;
            }
        }
        return false;
    }

    /**
     * The lazy retry hook (AdminController::render calls it on every admin page): at most once a minute (a stamp file, no database read in between), only when
     * there is work, and AFTER the page was sent. Never throws.
     */
    public static function lazyRetry(): void
    {
        try {
            $file = STORAGE_PATH . '/cache/social-lazy.stamp';
            $last = @filemtime($file);
            if ($last !== false && time() - $last < self::LAZY_EVERY) {
                return;
            }
            if (!is_dir(dirname($file))) {
                @mkdir(dirname($file), 0775, true);
            }
            @touch($file);
            if (!SocialConfig::globalOn() || !self::needsWork()) {
                return;
            }
            App::afterResponse(static function (): void {
                SocialDispatcher::runDue(10, 20.0);
            });
        } catch (\Throwable $e) {
            Log::warning('Social: lazy retry failed - ' . SocialHttp::redact($e->getMessage()));
        }
    }

    // ------------------------------------------------------------------------------------------------ test buttons

    /**
     * TEST CONNECTION (read-only). Updates the channel's health (ok / needs a new token / error).
     * @return array{ok:bool,state:string,message:string,detail?:string,at:string}
     */
    public static function testConnection(string $channel): array
    {
        if (!Channels::has($channel)) {
            return ['ok' => false, 'state' => 'error', 'message' => 'Unknown channel.', 'at' => date('Y-m-d H:i:s')];
        }
        $missing = SocialConfig::missingFor($channel);
        if ($missing !== []) {
            return ['ok' => false, 'state' => 'error', 'message' => 'Finish the setup first. Missing: ' . implode(', ', $missing) . '.', 'at' => date('Y-m-d H:i:s')];
        }
        try {
            $r = Channels::get($channel)->test(SocialConfig::channelConfig($channel));
        } catch (\Throwable $e) {
            $r = ['ok' => false, 'state' => 'error', 'message' => 'The test could not be completed (' . (new \ReflectionClass($e))->getShortName() . ').'];
        }
        $r['message'] = SocialHttp::redact((string) ($r['message'] ?? ''));
        self::recordHealth($channel, (bool) ($r['ok'] ?? false), (string) ($r['state'] ?? 'error'), (string) $r['message']);
        AuditLog::record('social.channel.test', 'social_channel', $channel, 'Tested a social channel connection', ['channel' => $channel, 'ok' => (bool) ($r['ok'] ?? false)]);
        return ['ok' => (bool) ($r['ok'] ?? false), 'state' => (string) ($r['state'] ?? 'error'), 'message' => $r['message']] + (isset($r['detail']) ? ['detail' => SocialHttp::redact((string) $r['detail'])] : []) + ['at' => date('Y-m-d H:i:s')];
    }

    /**
     * SEND A TEST POST: a short, clearly labelled message to the real account (Telegram: its test chat; webhook: "test": true). The caller confirms with the admin first.
     * @return array{ok:bool,state:string,message:string,remote_url?:string,at:string}
     */
    public static function sendTest(string $channel): array
    {
        $now = date('Y-m-d H:i:s');
        if (!Channels::has($channel)) {
            return ['ok' => false, 'state' => 'error', 'message' => 'Unknown channel.', 'at' => $now];
        }
        $missing = SocialConfig::missingFor($channel);
        if ($missing !== []) {
            return ['ok' => false, 'state' => 'error', 'message' => 'Finish the setup first. Missing: ' . implode(', ', $missing) . '.', 'at' => $now];
        }
        try {
            $res = Channels::get($channel)->post(SocialConfig::channelConfig($channel), ShareHook::testPayload($channel, microtime(true) + 14.0));
        } catch (\Throwable $e) {
            $res = SocialResult::failed('The test post could not be completed (' . (new \ReflectionClass($e))->getShortName() . ').');
        }
        $ok = $res->isPosted();
        self::recordHealth($channel, $ok, $res->state === SocialResult::AUTH ? 'auth' : ($ok ? 'ok' : 'error'), $res->error);
        AuditLog::record('social.channel.send_test', 'social_channel', $channel, 'Sent a test post to a social channel', ['channel' => $channel, 'ok' => $ok]);
        return ['ok' => $ok, 'state' => $ok ? 'ok' : ($res->state === SocialResult::AUTH ? 'auth' : 'error'), 'message' => $ok ? 'The test post was sent.' : ($res->error !== '' ? $res->error : 'The test post failed.')]
            + ($ok && $res->remoteUrl !== null ? ['remote_url' => $res->remoteUrl] : []) + ['at' => $now];
    }

    private static function recordHealth(string $channel, bool $ok, string $state, string $message): void
    {
        if ($ok) {
            SocialConfig::setStatus($channel, 'ok', $message !== '' ? $message : 'Connection works.');
        } elseif ($state === 'auth') {
            SocialConfig::setStatus($channel, 'needs_token', $message);
        } else {
            SocialConfig::setStatus($channel, 'error', $message);
        }
    }

    // ------------------------------------------------------------------------------------------------ notifications (once)

    /** E-mail the organisation ONCE per episode that a channel needs a new access token. Pasting a new token starts a new episode. */
    public static function notifyOnce(string $channel): void
    {
        try {
            $key = SocialConfig::key($channel, 'notified');
            if ((string) Settings::get($key, '') !== '') {
                return;
            }
            Settings::set($key, date('Y-m-d H:i:s'));
            $label = Channels::label($channel);
            Mailer::notify(
                'Social media: ' . $label . ' needs a new access token',
                "The website could not post to {$label} because it rejected the access credentials (an access token, key or signing secret that was revoked, has expired or is wrong).\n\nNothing was posted there. To fix it: open Admin > Social media > Channels, paste the new token or key for {$label} and press Test connection. Posts that failed can then be retried from Admin > Social media > Share log.\n\n"
                . abs_url('admin/social/channels') . "\n\nYou are sent this message once; the dashboard keeps showing the warning until the token is replaced."
            );
        } catch (\Throwable $e) {
            Log::warning('Social: notification failed - ' . SocialHttp::redact($e->getMessage()));
        }
    }

    /** E-mail ONCE per expiry date when a token runs out within 14 days (or already did). */
    public static function remindExpiry(): void
    {
        try {
            foreach (SocialConfig::expiryWarnings() as $w) {
                $key = SocialConfig::key($w['channel'], 'expiry_notified');
                if ((string) Settings::get($key, '') === $w['date']) {
                    continue;
                }
                Settings::set($key, $w['date']);
                $when = $w['days_left'] < 0 ? 'expired on ' . $w['date'] : 'expires on ' . $w['date'] . ' (in ' . $w['days_left'] . ' day' . ($w['days_left'] === 1 ? '' : 's') . ')';
                Mailer::notify(
                    'Social media: the ' . $w['label'] . ' access token ' . ($w['days_left'] < 0 ? 'has expired' : 'expires soon'),
                    "The access token for {$w['label']} {$when}. Posting there stops when it does.\n\nCreate a new token (the steps are on Admin > Social media > Channels), paste it there, enter its new expiry date and press Test connection.\n\n"
                    . abs_url('admin/social/channels')
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Social: expiry reminder failed - ' . SocialHttp::redact($e->getMessage()));
        }
    }
}
