<?php
declare(strict_types=1);

namespace App\Services\Social;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Models\Newsletter;
use App\Models\SocialPost;
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterService;

/**
 * THE integration point between the newsletter module and the social-media module (docs/SOCIAL.md section 5). Everything the newsletter code calls is here, and
 * everything is wrapped so that it CAN NEVER BREAK PUBLISHING: a missing table, a broken setting, a thrown error are logged and swallowed.
 *
 *   NewsletterService::transition(... 'published')       -> ShareHook::onWentLive($id, 'publish',  $userId, $firstTime)
 *   NewsletterService::resolveScheduled() (lazy go-live)  -> ShareHook::onWentLive($id, 'schedule', null,    $firstTime)
 *
 * WHAT HAPPENS WHEN AN ISSUE BECOMES PUBLIC (onWentLive):
 *   1. the global switch is on, the issue really is public, and it is the FIRST time it goes public ($firstTime: published_at was empty before) - an issue that was
 *      public before the module existed is never shared by surprise; use "Share now";
 *   2. for every channel that is configured, switched on and healthy (SocialConfig::availableIds) the admin's saved choice decides (newsletters.share_choices; when the
 *      panel was never opened the channel's "share by default" setting does) and a social_posts row is created ONCE (PostQueue::enqueue; a channel that already has ANY
 *      row for this issue is left alone, so unpublish + republish never posts again);
 *   3. the dispatcher is scheduled to run right AFTER the response was sent (SocialDispatcher::afterResponse): the admin's publish request is never delayed, and the
 *      posts that do not go through are retried by bin/social-dispatch.php (cron) and by the lazy retry on admin page loads.
 * Nothing is ever posted for a draft, an unpublished or an archived issue: the dispatcher re-checks that the issue is public at the moment of every attempt.
 */
final class ShareHook
{
    public const VARIANT_BY_CHANNEL = ['instagram' => 'square', 'telegram' => 'square', 'webhook' => 'square'];

    // ------------------------------------------------------------------------------------------------ who may touch an issue

    /**
     * May the signed-in user see and change the share data of this issue? An issue that is PUBLIC: anybody with social.share (its title is public anyway). A draft, scheduled,
     * unpublished or archived issue: only somebody who may also edit newsletters (newsletter.manage), so social.share alone never opens unpublished content (title, summary,
     * pictures) or writes captions onto it. The controllers answer 404 otherwise.
     * @param ?array<string,mixed> $row a newsletters row
     */
    public static function mayUse(?array $row): bool
    {
        return $row !== null && (NewsletterService::isLive($row) || can('newsletter.manage'));
    }

    // ------------------------------------------------------------------------------------------------ go-live hook

    /**
     * Called when an issue has just become public. Never throws.
     * @param bool $firstTime false when the issue was already public once (published_at was set): then nothing is queued automatically
     */
    public static function onWentLive(int $newsletterId, string $source = 'publish', ?int $userId = null, bool $firstTime = true): void
    {
        try {
            if (!$firstTime || !SocialConfig::globalOn()) {
                return;
            }
            $row = Newsletter::find($newsletterId);
            if ($row === null || !NewsletterService::isLive($row)) {
                return;
            }
            $created = 0;
            $version = max(1, (int) ($row['share_version'] ?? 1));
            foreach (self::plan($row) as $channel => $caption) {
                if (PostQueue::enqueue($newsletterId, $channel, $version, $caption, $userId)['created']) {
                    $created++;
                }
            }
            if ($created > 0) {
                AuditLog::record('social.share.queued', 'newsletter', $newsletterId, 'Queued the issue for social media', ['source' => $source, 'count' => $created]);
                SocialDispatcher::afterResponse($newsletterId);
            }
        } catch (\Throwable $e) {
            Log::warning('Social: share hook failed (publishing was not affected) - ' . SocialHttp::redact($e->getMessage()));
        }
    }

    /**
     * The channels that would be queued at go-live and their captions.
     * @param array<string,mixed> $row a newsletters row
     * @return array<string,string> channel => caption
     */
    public static function plan(array $row): array
    {
        $id = (int) $row['id'];
        $issue = self::issueForShare($row);
        $saved = self::decodeChoices($row['share_choices'] ?? null);
        $out = [];
        foreach (SocialConfig::availableIds() as $ch) {
            if (PostQueue::hasAny($id, $ch)) {
                continue;
            }
            $s = $saved[$ch] ?? null;
            $on = $s !== null ? !empty($s['on']) : SocialConfig::defaultOn($ch);
            if (!$on) {
                continue;
            }
            $out[$ch] = self::effectiveCaption($ch, $issue, $s);
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ issue data and captions

    /**
     * What the caption templates need to know about an issue.
     * @param array<string,mixed> $row a newsletters row (with `content` for the summary fallback)
     * @return array{id:int,title:string,summary:string,url:string,issue_number:?int,issue_date:string,site:string,kind:string,cover:string}
     */
    public static function issueForShare(array $row): array
    {
        $summary = trim((string) ($row['summary'] ?? ''));
        if ($summary === '' && isset($row['content']) && ($row['kind'] ?? 'online') !== 'pdf') {
            $summary = NewsletterContent::plainText(NewsletterContent::decode((string) $row['content']), 200);
        }
        return [
            'id'           => (int) $row['id'],
            'title'        => trim((string) $row['title']),
            'summary'      => $summary,
            'url'          => NewsletterService::url($row, true),
            'issue_number' => isset($row['issue_number']) && $row['issue_number'] !== null ? (int) $row['issue_number'] : null,
            'issue_date'   => (string) ($row['issue_date'] ?? ''),
            'site'         => (string) setting('site_name', ''),
            'kind'         => (string) ($row['kind'] ?? 'online'),
            'cover'        => (string) ($row['cover_image'] ?? ''),
        ];
    }

    /**
     * The saved share choices of an issue.
     * @return ?array<string,array{on:bool,caption:string}> null when the panel was never saved
     */
    public static function decodeChoices(?string $json): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }
        $d = json_decode($json, true);
        if (!is_array($d)) {
            return null;
        }
        $out = [];
        foreach ($d as $ch => $c) {
            if (is_string($ch) && Channels::has($ch) && is_array($c)) {
                $out[$ch] = ['on' => !empty($c['on']), 'caption' => is_string($c['caption'] ?? null) ? (string) $c['caption'] : ''];
            }
        }
        return $out;
    }

    /**
     * The caption that goes out: what the admin typed when it is valid, else the template for the CURRENT title and summary.
     * @param ?array{on:bool,caption:string} $saved
     * @param array<string,mixed> $issue see issueForShare()
     */
    public static function effectiveCaption(string $channel, array $issue, ?array $saved): string
    {
        $typed = trim((string) ($saved['caption'] ?? ''));
        if ($typed !== '') {
            $v = CaptionBuilder::validate($channel, $typed);
            if ($v['ok']) {
                return $v['text'];
            }
        }
        $text = CaptionBuilder::build($channel, $issue)['text'];
        return $text !== '' ? $text : CaptionBuilder::sanitize((string) $issue['title'], false);
    }

    // ------------------------------------------------------------------------------------------------ the share panel (editor + publish confirmation)

    /**
     * Everything the "Share on social media" panel shows for an issue (JSON-ready).
     *
     * channels[]: id label icon, on (ticked), caption (what will be sent), edited (differs from the template), template (the template's caption), length, limit, soft, over,
     *   links (clickable | none | attached), uses_image, notes, status {state: none | queued | posting | posted | failed | review | skipped, label, remote_url, error, post_id,
     *   attempts, posted_at, share_version}, can_share_now, can_post_again, can_retry.
     * Only channels that are configured, switched on and healthy are in `channels` (or have a row for this issue); the others are listed in `hidden` with the reason.
     *
     * @return array<string,mixed>
     */
    public static function panel(int $newsletterId): array
    {
        $row = Newsletter::find($newsletterId);
        if ($row === null) {
            return ['ok' => false, 'error' => 'That issue no longer exists.'];
        }
        $issue = self::issueForShare($row);
        $saved = self::decodeChoices($row['share_choices'] ?? null);
        $posts = PostQueue::latestByChannel($newsletterId);
        $live = NewsletterService::isLive($row);
        $channels = [];
        $hidden = [];
        $warnings = [];
        foreach (Channels::ids() as $id) {
            $meta = Channels::get($id)->meta();
            $ready = SocialConfig::ready($id);
            $post = $posts[$id] ?? null;
            if (!$ready['ready'] && $post === null) {
                $hidden[] = ['id' => $id, 'label' => (string) $meta['label'], 'icon' => (string) $meta['icon'], 'state' => $ready['state'], 'reason' => (string) ($ready['reasons'][0] ?? '')];
                continue;
            }
            $s = $saved[$id] ?? null;
            $built = CaptionBuilder::build($id, $issue);
            $typed = trim((string) ($s['caption'] ?? ''));
            $caption = $typed !== '' ? CaptionBuilder::validate($id, $typed)['text'] : $built['text'];
            // a channel that already has a post: while it is queued, being posted or posted, the caption is FROZEN in the row (editing the panel would change nothing), so the
            // panel shows that real text read-only; after a failure / skip / review the saved choice is what a retry will send, so it stays editable
            $locked = $post !== null && in_array((string) $post['status'], ['queued', 'posting', 'posted'], true);
            if ($locked) {
                $caption = (string) $post['caption'];
            }
            $v = CaptionBuilder::validate($id, $caption);
            $on = $post !== null ? true : ($ready['ready'] && ($s !== null ? !empty($s['on']) : SocialConfig::defaultOn($id)));
            $status = self::postStatus($post);
            $wait = $post !== null ? self::againWait($post) : 0;
            $canAgain = $live && $ready['ready'] && $post !== null && $post['status'] === 'posted';
            $channels[] = [
                'id' => $id, 'label' => (string) $meta['label'], 'icon' => (string) $meta['icon'], 'available' => $ready['ready'], 'reason' => (string) ($ready['reasons'][0] ?? ''),
                'on' => $on, 'caption' => $caption, 'edited' => !$locked && $typed !== '' && $caption !== $built['text'], 'template' => $built['text'],
                'locked' => $locked, 'has_post' => $post !== null,
                'length' => $v['length'], 'limit' => $v['limit'], 'soft' => (int) $meta['soft'], 'over' => $locked ? false : $v['over'], 'counting' => (string) $meta['count'],
                'links' => (string) $meta['links'], 'uses_image' => (bool) $meta['uses_image'], 'notes' => (array) $meta['notes'],
                'status' => $status,
                'can_share_now' => $live && $ready['ready'] && $post === null,
                'can_post_again' => $canAgain && $wait === 0,
                'again_wait' => $canAgain ? $wait : 0,
                'again_note' => ($meta['repeat_text'] ?? '') === 'rejected' ? Channels::label($id) . ' refuses a post with exactly the same text as an earlier one: change the caption (a small change is enough) before you post again.' : '',
                'can_retry' => $post !== null && in_array($post['status'], ['failed', 'skipped'], true),
            ];
            if ($on && $meta['uses_image'] && SocialCard::publicUrl($newsletterId, (string) ($meta['image_variant'] ?? 'square')) === null) {
                $warnings[] = $meta['label'] . ' needs a picture: add a cover photo to the issue.';
            }
        }
        if (!NewsletterService::pageVisible()) {
            $warnings[] = 'The Newsletter page is switched off (Admin > Pages), so the links in the posts would show "page not found".';
        }
        foreach (SocialConfig::expiryWarnings() as $w) {
            $warnings[] = $w['label'] . ($w['days_left'] < 0 ? ' token expired on ' : ' token expires on ') . $w['date'] . ': paste a new one in Admin > Social media > Channels.';
        }
        $anyPost = false;
        foreach ($channels as $c) {
            if (($c['status']['state'] ?? 'none') !== 'none') {
                $anyPost = true;
            }
        }
        return [
            'ok'            => true,
            'issue'         => ['id' => $newsletterId, 'title' => $issue['title'], 'url' => $issue['url'], 'status' => (string) $row['status'], 'is_live' => $live, 'kind' => $issue['kind'], 'scheduled_for' => $row['publish_at'] ?? null,
                                'scheduled_label' => self::dateLabel($row['publish_at'] ?? null), 'share_version' => (int) ($row['share_version'] ?? 1)],
            'global_on'     => SocialConfig::globalOn(),
            'configured'    => $channels !== [],
            'channels'      => $channels,
            'hidden'        => $hidden,
            'image'         => ['variant' => 'square', 'thumb_url' => SocialCard::adminUrl($newsletterId, 'square'), 'has_cover' => $issue['cover'] !== ''],
            'warnings'      => array_values(array_unique($warnings)),
            'note'          => $live
                ? ($anyPost
                    ? 'This issue is public. The result of each channel is shown below. "Share now" posts the channels that have no post yet; "Post again" makes a second post. Posts that were already made are not deleted if the issue is deleted.'
                    : 'This issue is already public. Tick the channels and press "Share now" to post it. Posts that were already made are not deleted if the issue is deleted.')
                : 'Nothing is posted until the issue is public. When you publish it (or when its scheduled time comes) it is posted to the ticked channels within a minute; if a post fails it is retried automatically and you can see the result here. Deleting the issue later does not delete posts that were already made.',
            'kit_url'       => url('admin/social/kit/' . $newsletterId),
        ];
    }

    /**
     * Save the admin's choices WITH the issue (they are used when it goes public). Does not change the issue's version or its updated_at.
     *
     * @param array{channels?:array<string,array{on?:mixed,caption?:mixed}>} $input
     * @return array{ok:bool,errors:array<string,string>,panel?:array<string,mixed>}
     */
    public static function saveChoices(int $newsletterId, array $input, ?int $userId = null): array
    {
        $row = Newsletter::find($newsletterId);
        if ($row === null) {
            return ['ok' => false, 'errors' => ['_' => 'That issue no longer exists.']];
        }
        $issue = self::issueForShare($row);
        $store = self::decodeChoices($row['share_choices'] ?? null) ?? [];
        $errors = [];
        $in = is_array($input['channels'] ?? null) ? $input['channels'] : [];
        foreach ($in as $ch => $c) {
            if (!is_string($ch) || !Channels::has($ch) || !is_array($c)) {
                $errors[is_string($ch) ? $ch : '_'] = 'Unknown channel.';
                continue;
            }
            $on = in_array($c['on'] ?? false, [true, 1, '1', 'true', 'on', 'yes'], true);
            $caption = is_string($c['caption'] ?? null) ? (string) $c['caption'] : '';
            if ($on && !PostQueue::hasAny($newsletterId, $ch)) {
                $ready = SocialConfig::ready($ch);
                if (!$ready['ready']) {
                    $why = trim((string) ($ready['reasons'][0] ?? ''));
                    $errors[$ch] = Channels::label($ch) . ' cannot be ticked: ' . ($why !== '' ? rtrim($why, '.') . '.' : 'it is not set up or is switched off.');
                    continue;
                }
            }
            $keep = '';
            if (trim($caption) !== '') {
                $v = CaptionBuilder::validate($ch, $caption);
                if (!$v['ok']) {
                    $errors[$ch] = $v['error'];
                    continue;
                }
                $keep = $v['text'] === CaptionBuilder::build($ch, $issue)['text'] ? '' : $v['text'];      // the untouched template is not stored: it follows later edits of the title
            }
            $store[$ch] = ['on' => $on, 'caption' => $keep];
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        $json = json_encode($store, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Database::execute('UPDATE newsletters SET share_choices = ?, updated_at = updated_at WHERE id = ?', [$json === false ? null : $json, $newsletterId]);
        AuditLog::record('social.share.choices', 'newsletter', $newsletterId, 'Saved the social media choices of an issue', ['channels' => array_keys($in)]);
        return ['ok' => true, 'errors' => [], 'panel' => self::panel($newsletterId)];
    }

    // ------------------------------------------------------------------------------------------------ explicit actions

    /**
     * "Share now": queue the channels that were NEVER posted for this (already public) issue and dispatch. Never reposts.
     * @param list<string> $channels
     * @return array{ok:bool,queued:list<string>,errors:array<string,string>}
     */
    public static function shareNow(int $newsletterId, array $channels, ?int $userId = null): array
    {
        $row = Newsletter::find($newsletterId);
        if ($row === null || !NewsletterService::isLive($row)) {
            return ['ok' => false, 'queued' => [], 'errors' => ['_' => 'The issue is not public yet. It is shared automatically when it goes live.']];
        }
        $issue = self::issueForShare($row);
        $saved = self::decodeChoices($row['share_choices'] ?? null) ?? [];
        $version = max(1, (int) ($row['share_version'] ?? 1));
        $queued = [];
        $errors = [];
        foreach (array_unique($channels) as $ch) {
            if (!is_string($ch) || !Channels::has($ch)) {
                $errors[(string) $ch] = 'Unknown channel.';
            } elseif (!SocialConfig::ready($ch)['ready']) {
                $errors[$ch] = Channels::label($ch) . ' is not set up or is switched off.';
            } elseif (PostQueue::hasAny($newsletterId, $ch)) {
                $errors[$ch] = Channels::label($ch) . ' already has a post for this issue. Use "Post again" if you really want a second one.';
            } elseif (PostQueue::enqueue($newsletterId, $ch, $version, self::effectiveCaption($ch, $issue, $saved[$ch] ?? null), $userId)['created']) {
                $queued[] = $ch;
            }
        }
        if ($queued !== []) {
            AuditLog::record('social.share.now', 'newsletter', $newsletterId, 'Shared an issue on social media', ['channels' => $queued]);
            SocialDispatcher::afterResponse($newsletterId);
        }
        return ['ok' => $errors === [], 'queued' => $queued, 'errors' => $errors];
    }

    /** Seconds after the last post of a channel during which "Post again" is refused (a double click, a second tab, a retry of the request). */
    public const AGAIN_COOLDOWN = 120;

    /** Seconds left of the "Post again" cool-down of a channel's newest post (0 = may post again). @param array<string,mixed> $post */
    public static function againWait(array $post): int
    {
        $ref = 0;
        foreach (['created_at', 'posted_at', 'last_attempt_at'] as $k) {
            $t = isset($post[$k]) && $post[$k] !== null ? strtotime((string) $post[$k]) : false;
            if ($t !== false) {
                $ref = max($ref, $t);
            }
        }
        return $ref > 0 ? max(0, self::AGAIN_COOLDOWN - (time() - $ref)) : 0;
    }

    /**
     * "Post again": an explicit second post of an issue that already has one on the channel. Raises share_version (the only way a second row can exist).
     *
     * IDEMPOTENT: the whole decision is made under a named lock (lumina_social_again_<id>), the request may carry the share_version the admin saw ($expectedVersion: another
     * tab or a double click that already raised it is refused), the raise is a compare-and-swap (`... WHERE share_version = <seen>`, one row or nothing), and a channel whose
     * newest post is younger than AGAIN_COOLDOWN seconds is refused. A platform that rejects identical text (X, meta `repeat_text`) needs an edited caption.
     *
     * @param list<string> $channels
     * @return array{ok:bool,queued:list<string>,errors:array<string,string>,share_version?:int}
     */
    public static function postAgain(int $newsletterId, array $channels, ?int $userId = null, ?int $expectedVersion = null): array
    {
        $lock = 'lumina_social_again_' . $newsletterId;
        if ((int) Database::value('SELECT GET_LOCK(?, 5)', [$lock], 0) !== 1) {
            return ['ok' => false, 'queued' => [], 'errors' => ['_' => 'Another "Post again" for this issue is running right now. Wait a moment, reload the panel and check the result before trying again.']];
        }
        try {
            return self::postAgainLocked($newsletterId, $channels, $userId, $expectedVersion);
        } finally {
            Database::value('SELECT RELEASE_LOCK(?)', [$lock]);
        }
    }

    /** @param list<string> $channels @return array{ok:bool,queued:list<string>,errors:array<string,string>,share_version?:int} */
    private static function postAgainLocked(int $newsletterId, array $channels, ?int $userId, ?int $expectedVersion): array
    {
        $row = Newsletter::find($newsletterId);                                     // read INSIDE the lock: what the previous request left behind
        if ($row === null || !NewsletterService::isLive($row)) {
            return ['ok' => false, 'queued' => [], 'errors' => ['_' => 'The issue is not public.']];
        }
        $current = max(1, (int) ($row['share_version'] ?? 1));
        if ($expectedVersion !== null && $expectedVersion !== $current) {
            return ['ok' => false, 'queued' => [], 'errors' => ['_' => 'This issue was already posted again (in another tab or by a second click). Nothing was posted a second time: reload the panel to see the result.']];
        }
        $issue = self::issueForShare($row);
        $saved = self::decodeChoices($row['share_choices'] ?? null) ?? [];
        $posts = PostQueue::latestByChannel($newsletterId);
        $valid = [];
        $captions = [];
        $errors = [];
        foreach (array_unique($channels) as $ch) {
            if (!is_string($ch) || !Channels::has($ch)) {
                $errors[(string) $ch] = 'Unknown channel.';
            } elseif (!SocialConfig::ready($ch)['ready']) {
                $errors[$ch] = Channels::label($ch) . ' is not set up or is switched off.';
            } elseif (!isset($posts[$ch])) {
                $errors[$ch] = Channels::label($ch) . ' has no post for this issue yet: use "Share now".';
            } elseif (!in_array($posts[$ch]['status'], ['posted', 'skipped'], true)) {
                $errors[$ch] = Channels::label($ch) . ' has a post that is still ' . strtolower(SocialPost::STATUS_LABELS[$posts[$ch]['status']] ?? $posts[$ch]['status']) . '. Deal with it first.';
            } elseif (($wait = self::againWait($posts[$ch])) > 0) {
                $errors[$ch] = Channels::label($ch) . ' was posted a moment ago. Posting it again straight away would most likely be a double click: wait ' . $wait . ' second' . ($wait === 1 ? '' : 's') . ' and check the account first.';
            } else {
                $caption = self::effectiveCaption($ch, $issue, $saved[$ch] ?? null);
                if ((Channels::get($ch)->meta()['repeat_text'] ?? '') === 'rejected' && trim($caption) === trim((string) $posts[$ch]['caption'])) {
                    $errors[$ch] = Channels::label($ch) . ' refuses a post with exactly the same text as an earlier one. Change the caption (a small change is enough) and try again.';
                    continue;
                }
                $valid[] = $ch;
                $captions[$ch] = $caption;
            }
        }
        if ($valid === []) {
            return ['ok' => false, 'queued' => [], 'errors' => $errors];
        }
        // compare-and-swap: only the request that still sees the version it read may raise it
        $won = Database::execute('UPDATE newsletters SET share_version = share_version + 1, updated_at = updated_at WHERE id = ? AND share_version = ?', [$newsletterId, $current]);
        if ($won !== 1) {
            return ['ok' => false, 'queued' => [], 'errors' => ['_' => 'This issue was posted again by somebody else a moment ago. Nothing was posted a second time: reload the panel.']];
        }
        $version = $current + 1;
        $queued = [];
        foreach ($valid as $ch) {
            if (PostQueue::enqueue($newsletterId, $ch, $version, $captions[$ch], $userId)['created']) {
                $queued[] = $ch;
            }
        }
        AuditLog::record('social.share.again', 'newsletter', $newsletterId, 'Posted an issue on social media again', ['channels' => $queued, 'share_version' => $version]);
        if ($queued !== []) {
            SocialDispatcher::afterResponse($newsletterId);
        }
        return ['ok' => $errors === [], 'queued' => $queued, 'errors' => $errors, 'share_version' => $version];
    }

    /** "23 Oct 2026, 21:29 (Kampala)" from a stored application-time-zone date; '' when there is none. */
    public static function dateLabel(?string $dt): string
    {
        $t = $dt !== null && trim($dt) !== '' ? strtotime($dt) : false;
        if ($t === false) {
            return '';
        }
        $zone = date_default_timezone_get();
        $city = str_contains($zone, '/') ? str_replace('_', ' ', substr($zone, (int) strrpos($zone, '/') + 1)) : $zone;
        return date('j M Y, H:i', $t) . ' (' . $city . ')';
    }

    // ------------------------------------------------------------------------------------------------ payloads and summaries

    /**
     * The payload an adapter posts for a queue row.
     * @param array<string,mixed> $post a social_posts row
     * @param array<string,mixed> $newsletter a newsletters row
     */
    public static function buildPayload(array $post, array $newsletter, ?float $deadline = null): SocialPayload
    {
        $channel = (string) $post['channel'];
        $issue = self::issueForShare($newsletter);
        return self::payload($channel, $issue, (string) $post['caption'], (int) $post['id'], false, $deadline, 'lumina-post-' . (int) $post['id']);
    }

    /**
     * The payload of a "Send a test post" (no queue row): a short clearly labelled caption; uses the newest public issue for the links and pictures when there is one.
     */
    public static function testPayload(string $channel, ?float $deadline = null): SocialPayload
    {
        $row = Database::one("SELECT * FROM newsletters WHERE status = 'published' ORDER BY issue_date DESC, id DESC LIMIT 1");
        $site = (string) setting('site_name', '');
        $issue = $row !== null ? self::issueForShare($row) : ['id' => 0, 'title' => 'Test', 'summary' => '', 'url' => abs_url('newsletter'), 'issue_number' => null, 'issue_date' => '', 'site' => $site, 'kind' => 'online', 'cover' => ''];
        $caption = 'Test post from ' . ($site !== '' ? $site : 'the website') . ': checking that posting works. Please ignore.';
        return self::payload($channel, $issue, $caption, 0, true, $deadline, 'lumina-test-' . bin2hex(random_bytes(6)));
    }

    /** @param array<string,mixed> $issue */
    private static function payload(string $channel, array $issue, string $caption, int $postId, bool $test, ?float $deadline, string $eventId): SocialPayload
    {
        $meta = Channels::get($channel)->meta();
        $images = [];
        $id = (int) $issue['id'];
        if ($id > 0 && ($meta['uses_image'] || $channel === 'webhook')) {
            $want = $channel === 'webhook' ? SocialCard::VARIANTS : [(string) ($meta['image_variant'] ?? 'square')];
            foreach ($want as $variant) {
                $u = SocialCard::publicUrl($id, $variant);
                if ($u !== null) {
                    $images[$variant] = $u;
                }
            }
        }
        $variant = (string) ($meta['image_variant'] ?? 'square');
        return new SocialPayload(
            $channel, $postId, $id, (string) $issue['title'], (string) $issue['summary'], CaptionBuilder::utmUrl((string) $issue['url'], $channel), (string) $issue['url'], $caption,
            $images[$variant] ?? ($images !== [] ? reset($images) : null), $images, CaptionBuilder::hashtagList(SocialConfig::str('social_hashtags'), 10),
            $issue['issue_number'] !== null ? (int) $issue['issue_number'] : null, (string) $issue['issue_date'], $test, $deadline, $eventId, (string) $issue['site']
        );
    }

    /**
     * One post row as the panel and the status poll show it.
     * @param ?array<string,mixed> $post
     * @return array<string,mixed>
     */
    public static function postStatus(?array $post): array
    {
        if ($post === null) {
            return ['state' => 'none', 'label' => 'Not shared', 'tone' => 'neutral', 'remote_url' => null, 'error' => '', 'post_id' => 0, 'attempts' => 0, 'posted_at' => null, 'share_version' => 0, 'next_attempt_at' => null];
        }
        $s = (string) $post['status'];
        $label = match ($s) {
            'posted' => !empty($post['remote_url']) ? 'Posted - view post' : 'Posted',
            'failed' => 'Failed - retry',
            'review' => 'Needs review',
            'queued' => 'Queued',
            'posting' => 'Posting now',
            default  => SocialPost::STATUS_LABELS[$s] ?? $s,
        };
        return [
            'state' => $s, 'label' => $label, 'tone' => SocialPost::STATUS_TONES[$s] ?? 'neutral', 'remote_url' => $post['remote_url'] !== null ? (string) $post['remote_url'] : null,
            'error' => (string) ($post['last_error'] ?? ''), 'post_id' => (int) $post['id'], 'attempts' => (int) $post['attempts'], 'posted_at' => $post['posted_at'] ?? null,
            'share_version' => (int) $post['share_version'], 'next_attempt_at' => $post['next_attempt_at'] ?? null,
        ];
    }

    /**
     * Lightweight summary for the newsletter JSON item and the issues list. Never throws.
     * @return array{any:bool,available:list<string>,channels:array<string,string>}
     */
    public static function summary(int $newsletterId): array
    {
        try {
            $by = [];
            foreach (PostQueue::latestByChannel($newsletterId) as $ch => $p) {
                $by[$ch] = (string) $p['status'];
            }
            return ['any' => $by !== [], 'available' => SocialConfig::availableIds(), 'channels' => $by];
        } catch (\Throwable) {
            return ['any' => false, 'available' => [], 'channels' => []];
        }
    }
}
