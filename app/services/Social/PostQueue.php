<?php
declare(strict_types=1);

namespace App\Services\Social;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Pagination;
use App\Models\Newsletter;
use App\Models\SocialPost;
use App\Services\Newsletter\NewsletterService;

/**
 * The social post queue and its state machine (table social_posts; docs/SOCIAL.md section 5). All methods are static and never throw for a business problem.
 *
 *   queued ──claim──► posting ──posted──► posted
 *      ▲                 ├──transient──► queued (next_attempt_at = now + backoff 60 s / 5 min / 15 min / 1 h / 3 h) … after the 5th attempt ► failed
 *      │                 ├──failed / auth──► failed
 *      │                 └──ambiguous──► review
 *   retry (admin) ◄── failed | skipped | review ──skip (admin)──► skipped        review ──"mark as posted" (admin)──► posted
 *
 * IDEMPOTENCY: UNIQUE (newsletter_id, channel, share_version) makes enqueue() safe to call any number of times (INSERT IGNORE). claim() is ONE conditional UPDATE
 * (`WHERE status = 'queued' AND due`): of two processes (the request that just published, the cron job, the lazy retry) exactly one wins. The attempt is RECORDED
 * (status posting, attempts + 1, last_attempt_at) BEFORE the platform is called, so a crash can never lead to an unnoticed second post: a `posting` row older than
 * ten minutes is moved to `review` (or back to `queued` for channels that de-duplicate, i.e. the webhook) by reclaimStuck().
 */
final class PostQueue
{
    public const MAX_ATTEMPTS = 5;
    /** seconds to wait after attempt 1, 2, 3, 4 (the 5th failure ends in `failed`) */
    public const BACKOFF = [60, 300, 900, 3600, 10800];
    public const STUCK_SECONDS = 600;

    /** now in the application time zone, 'Y-m-d H:i:s' (what is stored and compared; never the database server's NOW()) */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    // ------------------------------------------------------------------------------------------------ creating rows

    /**
     * Queue one post. Creates the row ONCE per (issue, channel, share version).
     * @return array{created:bool,id:int}  created=false: a row already exists for that key (nothing changed); id is the existing row (0 when the input is not valid)
     */
    public static function enqueue(int $newsletterId, string $channel, int $shareVersion, string $caption, ?int $userId = null): array
    {
        if (!Channels::has($channel) || $newsletterId < 1 || $shareVersion < 1) {
            return ['created' => false, 'id' => 0];
        }
        $caption = CaptionBuilder::sanitize($caption, true);
        $n = Database::execute(
            "INSERT IGNORE INTO social_posts (newsletter_id, channel, share_version, status, caption, created_by) VALUES (?, ?, ?, 'queued', ?, ?)",
            [$newsletterId, $channel, $shareVersion, $caption, $userId]
        );
        if ($n === 1) {
            return ['created' => true, 'id' => Database::lastId()];
        }
        $id = (int) Database::value('SELECT id FROM social_posts WHERE newsletter_id = ? AND channel = ? AND share_version = ?', [$newsletterId, $channel, $shareVersion], 0);
        return ['created' => false, 'id' => $id];
    }

    /** Has this issue ever been queued for the channel (any share version)? */
    public static function hasAny(int $newsletterId, string $channel): bool
    {
        return (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ? AND channel = ?', [$newsletterId, $channel], 0) > 0;
    }

    // ------------------------------------------------------------------------------------------------ attempts

    /**
     * Take a queued, due row for posting. ONE conditional UPDATE: returns the row (now `posting`, attempts + 1, last_attempt_at recorded) or null when somebody else
     * has it, it is not due yet or it is not queued any more.
     * @return ?array<string,mixed>
     */
    public static function claim(int $id): ?array
    {
        $now = self::now();
        $n = Database::execute(
            "UPDATE social_posts SET status = 'posting', attempts = attempts + 1, last_attempt_at = ?, next_attempt_at = NULL WHERE id = ? AND status = 'queued' AND (next_attempt_at IS NULL OR next_attempt_at <= ?)",
            [$now, $id, $now]
        );
        return $n === 1 ? SocialPost::find($id) : null;
    }

    /** Ids of queued rows that are due, oldest first. @return list<int> */
    public static function due(int $limit = 20, ?int $newsletterId = null): array
    {
        $now = self::now();
        $sql = "SELECT id FROM social_posts WHERE status = 'queued' AND (next_attempt_at IS NULL OR next_attempt_at <= ?)";
        $params = [$now];
        if ($newsletterId !== null) {
            $sql .= ' AND newsletter_id = ?';
            $params[] = $newsletterId;
        }
        $sql .= ' ORDER BY id ASC LIMIT ' . max(1, min(200, $limit));
        return array_map(static fn(array $r): int => (int) $r['id'], Database::all($sql, $params));
    }

    /** Give a claimed row back WITHOUT counting the attempt (no time was left in this run): queued again after $seconds. */
    public static function release(int $id, int $seconds = 30): bool
    {
        return Database::execute(
            "UPDATE social_posts SET status = 'queued', attempts = GREATEST(attempts - 1, 0), next_attempt_at = ? WHERE id = ? AND status = 'posting'",
            [date('Y-m-d H:i:s', time() + max(0, $seconds)), $id]
        ) === 1;
    }

    public static function markPosted(int $id, ?string $remoteId, ?string $remoteUrl): bool
    {
        return Database::execute(
            "UPDATE social_posts SET status = 'posted', posted_at = ?, remote_id = ?, remote_url = ?, last_error = NULL, next_attempt_at = NULL WHERE id = ? AND status = 'posting'",
            [self::now(), $remoteId, $remoteUrl, $id]
        ) === 1;
    }

    /**
     * Nothing was posted and a retry may work: back to `queued` with the backoff, or `failed` after the fifth attempt.
     * @return string the status the row ended up in (queued | failed | '' when the row was not posting)
     */
    public static function markTransient(int $id, string $error, ?int $retryAfter = null): string
    {
        $row = SocialPost::find($id);
        if ($row === null || $row['status'] !== 'posting') {
            return '';
        }
        $attempts = (int) $row['attempts'];
        if ($attempts >= self::MAX_ATTEMPTS) {
            self::markFailed($id, 'Gave up after ' . self::MAX_ATTEMPTS . ' tries. Last problem: ' . $error);
            return 'failed';
        }
        $wait = $retryAfter !== null && $retryAfter > 0 ? min(86400, max(30, $retryAfter)) : self::BACKOFF[min($attempts, count(self::BACKOFF)) - 1];
        Database::execute(
            "UPDATE social_posts SET status = 'queued', next_attempt_at = ?, last_error = ? WHERE id = ? AND status = 'posting'",
            [date('Y-m-d H:i:s', time() + $wait), self::err($error), $id]
        );
        return 'queued';
    }

    /** @param ?string $code 'auth' when the platform rejected the access token / key (only those rows are re-queued after a new token is pasted); null for every other reason */
    public static function markFailed(int $id, string $error, ?string $code = null): bool
    {
        return Database::execute("UPDATE social_posts SET status = 'failed', last_error = ?, fail_code = ?, next_attempt_at = NULL WHERE id = ? AND status IN ('posting','queued')", [self::err($error), $code, $id]) === 1;
    }

    /** Ambiguous outcome: a person must look at the real account. */
    public static function markReview(int $id, string $error, ?string $remoteId = null): bool
    {
        return Database::execute(
            "UPDATE social_posts SET status = 'review', last_error = ?, remote_id = COALESCE(?, remote_id), next_attempt_at = NULL WHERE id = ? AND status IN ('posting','queued')",
            [self::err($error), $remoteId, $id]
        ) === 1;
    }

    public static function markSkipped(int $id, string $reason): bool
    {
        return Database::execute("UPDATE social_posts SET status = 'skipped', last_error = ?, next_attempt_at = NULL WHERE id = ? AND status IN ('posting','queued','failed','review')", [self::err($reason), $id]) === 1;
    }

    /**
     * Rows stuck in `posting` (the process died): ambiguous for channels that cannot de-duplicate -> `review`; the webhook (de-duplicated by event id) -> `queued`.
     * @return int rows moved
     */
    public static function reclaimStuck(): int
    {
        $cut = date('Y-m-d H:i:s', time() - self::STUCK_SECONDS);
        $moved = 0;
        foreach (Database::all("SELECT id, channel FROM social_posts WHERE status = 'posting' AND last_attempt_at < ?", [$cut]) as $r) {
            $retry = Channels::has((string) $r['channel']) && Channels::get((string) $r['channel'])->meta()['ambiguous'] === 'retry';
            $n = $retry
                ? Database::execute("UPDATE social_posts SET status = 'queued', next_attempt_at = NULL, last_error = ? WHERE id = ? AND status = 'posting' AND last_attempt_at < ?", ['The previous attempt did not finish; trying again.', (int) $r['id'], $cut])
                : Database::execute("UPDATE social_posts SET status = 'review', last_error = ? WHERE id = ? AND status = 'posting' AND last_attempt_at < ?", ['The attempt did not finish (the server stopped while posting). It may or may not have been posted: check the account.', (int) $r['id'], $cut]);
            $moved += $n;
        }
        return $moved;
    }

    // ------------------------------------------------------------------------------------------------ admin actions

    /**
     * "Retry": failed or skipped -> queued again (attempts start from 0). A `review` row is retried with retryAnyway() (it may duplicate a post).
     * @return array{ok:bool,error?:string}
     */
    public static function retry(int $id, ?int $userId = null): array
    {
        $row = SocialPost::find($id);
        if ($row === null) {
            return ['ok' => false, 'error' => 'That post no longer exists.'];
        }
        if (!in_array($row['status'], ['failed', 'skipped'], true)) {
            return ['ok' => false, 'error' => $row['status'] === 'review' ? 'This post needs a decision first: check the account, then choose "Mark as posted" or "Post anyway".' : 'Only a failed or skipped post can be retried.'];
        }
        return self::requeue($row, 'retry');
    }

    /** "Post anyway" on a `review` row: the admin looked and it is NOT on the platform, or accepts a possible duplicate. */
    public static function retryAnyway(int $id, ?int $userId = null): array
    {
        $row = SocialPost::find($id);
        if ($row === null || $row['status'] !== 'review') {
            return ['ok' => false, 'error' => 'That post is not waiting for a decision.'];
        }
        return self::requeue($row, 'retry_anyway');
    }

    public static function skip(int $id, ?int $userId = null): array
    {
        $row = SocialPost::find($id);
        if ($row === null) {
            return ['ok' => false, 'error' => 'That post no longer exists.'];
        }
        if (!in_array($row['status'], ['queued', 'failed', 'review'], true)) {
            return ['ok' => false, 'error' => 'Only a waiting, failed or unclear post can be skipped.'];
        }
        self::markSkipped($id, 'Skipped by an administrator.');
        AuditLog::record('social.post.skip', 'social_post', $id, 'Skipped a social post', ['channel' => $row['channel'], 'newsletter' => (int) $row['newsletter_id']]);
        return ['ok' => true];
    }

    /** "Mark as posted": the admin saw the post on the platform (an ambiguous or failed attempt that actually worked). $remoteUrl optional (https only). */
    public static function markPostedByAdmin(int $id, ?string $remoteUrl = null, ?int $userId = null): array
    {
        $row = SocialPost::find($id);
        if ($row === null) {
            return ['ok' => false, 'error' => 'That post no longer exists.'];
        }
        if (!in_array($row['status'], ['review', 'failed'], true)) {
            return ['ok' => false, 'error' => 'Only a post that needs review or failed can be marked as posted.'];
        }
        $remoteUrl = trim((string) $remoteUrl);
        if ($remoteUrl !== '' && (preg_match('~^https://[^\s<>"\']{4,240}$~i', $remoteUrl) !== 1)) {
            return ['ok' => false, 'error' => 'The link must be a full https:// address (or leave it empty).'];
        }
        Database::execute(
            "UPDATE social_posts SET status = 'posted', posted_at = ?, remote_url = ?, last_error = ?, next_attempt_at = NULL WHERE id = ? AND status IN ('review','failed')",
            [self::now(), $remoteUrl !== '' ? $remoteUrl : $row['remote_url'], 'Marked as posted by an administrator.', $id]
        );
        AuditLog::record('social.post.mark_posted', 'social_post', $id, 'Marked a social post as posted', ['channel' => $row['channel'], 'newsletter' => (int) $row['newsletter_id']]);
        return ['ok' => true];
    }

    /** How far back a new token brings failed posts back to the queue (hours). Older issues were most likely handled by hand. */
    public const AUTH_RETRY_HOURS = 48;

    /**
     * After the admin pasted a new token / address: ONLY the posts that failed because the old credentials were rejected (fail_code 'auth'), made in the last 48 hours,
     * for issues that are still public, go back to the queue. Posts that failed for any other reason (a refused picture, a duplicate, a permission problem, "gave up after
     * 5 tries") and old issues the admin may have posted by hand stay as they are: each has its own Retry button.
     * @return array{count:int,issues:list<string>} issues = the titles that were queued again (for the message to the admin)
     */
    public static function retryAuthFailures(string $channel): array
    {
        $out = ['count' => 0, 'issues' => []];
        $since = date('Y-m-d H:i:s', time() - self::AUTH_RETRY_HOURS * 3600);
        foreach (Database::all("SELECT sp.id, sp.newsletter_id, n.title FROM social_posts sp INNER JOIN newsletters n ON n.id = sp.newsletter_id WHERE sp.channel = ? AND sp.status = 'failed' AND sp.fail_code = 'auth' AND sp.created_at >= ? ORDER BY sp.id", [$channel, $since]) as $r) {
            $news = Newsletter::find((int) $r['newsletter_id']);
            if ($news === null || !NewsletterService::isLive($news)) {
                continue;
            }
            $row = SocialPost::find((int) $r['id']);
            $n = $row === null ? 0 : Database::execute("UPDATE social_posts SET status = 'queued', attempts = 0, next_attempt_at = NULL, last_error = NULL, fail_code = NULL, caption = ? WHERE id = ? AND status = 'failed' AND fail_code = 'auth'", [self::freshCaption($row), (int) $r['id']]);
            if ($n === 1) {
                $out['count']++;
                $t = trim((string) $r['title']);
                $out['issues'][] = $t !== '' ? $t : 'Issue #' . (int) $r['newsletter_id'];
            }
        }
        return $out;
    }

    /** Same as retryAuthFailures() but only the number (kept for callers that do not need the titles). */
    public static function retryFailedForChannel(string $channel): int
    {
        return self::retryAuthFailures($channel)['count'];
    }

    /**
     * Queue a failed / skipped / review row again. The caption is resolved AGAIN from the issue's saved choices (what the admin edited in the share panel after the failure)
     * or the template of the current title, so an edit made after a failure is what goes out.
     * @param array<string,mixed> $row
     */
    private static function requeue(array $row, string $why): array
    {
        $n = Database::execute("UPDATE social_posts SET status = 'queued', attempts = 0, next_attempt_at = NULL, last_error = NULL, fail_code = NULL, caption = ? WHERE id = ? AND status = ?", [self::freshCaption($row), (int) $row['id'], $row['status']]);
        if ($n !== 1) {
            return ['ok' => false, 'error' => 'Somebody else just changed this post. Reload the list.'];
        }
        AuditLog::record('social.post.' . $why, 'social_post', (int) $row['id'], 'Queued a social post again', ['channel' => $row['channel'], 'newsletter' => (int) $row['newsletter_id']]);
        return ['ok' => true];
    }

    /**
     * The caption a re-queued post will send: resolved AGAIN from the issue's saved choices (what the admin typed in the share panel since) or the template for the current title;
     * the stored text when that cannot be resolved.
     * @param array<string,mixed> $row a social_posts row
     */
    private static function freshCaption(array $row): string
    {
        try {
            $news = Newsletter::find((int) $row['newsletter_id']);
            if ($news !== null) {
                $channel = (string) $row['channel'];
                $saved = ShareHook::decodeChoices($news['share_choices'] ?? null);
                $fresh = ShareHook::effectiveCaption($channel, ShareHook::issueForShare($news), $saved[$channel] ?? null);
                if (trim($fresh) !== '') {
                    return CaptionBuilder::sanitize($fresh, true);
                }
            }
        } catch (\Throwable) {
            // keep the stored caption
        }
        return (string) $row['caption'];
    }

    // ------------------------------------------------------------------------------------------------ reading

    /** @return list<array<string,mixed>> every row of an issue, channel order then newest share version first */
    public static function forNewsletter(int $newsletterId): array
    {
        $rows = Database::all('SELECT * FROM social_posts WHERE newsletter_id = ? ORDER BY share_version DESC, id DESC', [$newsletterId]);
        $order = array_flip(Channels::ids());
        usort($rows, static fn(array $a, array $b): int => [(int) $b['share_version'], $order[$a['channel']] ?? 99] <=> [(int) $a['share_version'], $order[$b['channel']] ?? 99]);
        return $rows;
    }

    /**
     * The newest row of every channel for an issue (what the share panel shows).
     * @return array<string,array<string,mixed>> channel => row
     */
    public static function latestByChannel(int $newsletterId): array
    {
        $out = [];
        foreach (Database::all('SELECT * FROM social_posts WHERE newsletter_id = ? ORDER BY share_version ASC, id ASC', [$newsletterId]) as $r) {
            $out[(string) $r['channel']] = $r;
        }
        return $out;
    }

    /** @return array<string,int> status => count (all rows) */
    public static function counts(): array
    {
        $out = array_fill_keys(SocialPost::STATUSES, 0);
        foreach (Database::all('SELECT status, COUNT(*) AS c FROM social_posts GROUP BY status') as $r) {
            $out[(string) $r['status']] = (int) $r['c'];
        }
        return $out;
    }

    /**
     * The share log (newest first), joined to the issue title.
     * @param array{channel?:string,status?:string,newsletter_id?:int|string} $filters
     */
    public static function log(array $filters, int $page, int $perPage = 25): Pagination
    {
        $w = [];
        $p = [];
        $ch = (string) ($filters['channel'] ?? '');
        if ($ch !== '' && Channels::has($ch)) {
            $w[] = 'sp.channel = ?';
            $p[] = $ch;
        }
        $st = (string) ($filters['status'] ?? '');
        if ($st !== '' && in_array($st, SocialPost::STATUSES, true)) {
            $w[] = 'sp.status = ?';
            $p[] = $st;
        }
        $nid = (int) ($filters['newsletter_id'] ?? 0);
        if ($nid > 0) {
            $w[] = 'sp.newsletter_id = ?';
            $p[] = $nid;
        }
        $where = $w === [] ? '' : 'WHERE ' . implode(' AND ', $w);
        $total = (int) Database::value("SELECT COUNT(*) FROM social_posts sp {$where}", $p, 0);
        $pg = Pagination::make($total, $page, $perPage);
        $pg->items = Database::all(
            "SELECT sp.id, sp.newsletter_id, sp.channel, sp.share_version, sp.status, sp.attempts, sp.next_attempt_at, sp.last_attempt_at, sp.remote_id, sp.remote_url, sp.last_error, sp.posted_at, sp.created_at, sp.caption, n.title AS newsletter_title, n.issue_number FROM social_posts sp LEFT JOIN newsletters n ON n.id = sp.newsletter_id {$where} ORDER BY sp.id DESC LIMIT ? OFFSET ?",
            array_merge($p, [$pg->perPage, $pg->offset])
        );
        return $pg;
    }

    private static function err(string $error): string
    {
        return mb_substr(SocialHttp::redact(trim((string) preg_replace('/\s+/u', ' ', $error))), 0, 255);
    }
}
