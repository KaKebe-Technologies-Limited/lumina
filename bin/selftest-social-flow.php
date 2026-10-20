<?php
/**
 * Part 1b + 2 of bin/selftest-social.php: the dispatcher and ShareHook with FAKE channels, then the adapters and the whole publish flow against the LOCAL MOCK
 * bin/dev/mock-social.php. Included by bin/selftest-social.php: it shares its variables ($zzIssue, $zzConfigure, $zzResetChannels, $zzMails, $zzTok, ... and check()).
 */
declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\Settings;
use App\Models\Newsletter;
use App\Models\SocialPost;
use App\Services\Newsletter\NewsletterService;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\Channels;
use App\Services\Social\PostQueue;
use App\Services\Social\ShareHook;
use App\Services\Social\SocialCard;
use App\Services\Social\SocialConfig;
use App\Services\Social\SocialDispatcher;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialProfiles;
use App\Services\Social\SocialResult;

// =====================================================================================================================================================
echo "Social: publish flow with FAKE channels (queue, dispatcher, ShareHook)\n";
$zzUid = (int) Database::value('SELECT id FROM users ORDER BY id LIMIT 1', [], 0);
$zzResetChannels();
Channels::reset();
SocialConfig::saveGlobal(['enabled' => true, 'utm' => true, 'hashtags' => '#LuminaAfricaFoundation #Uganda']);
$none = $zzIssue();
$r = NewsletterService::publish($none, null);
check('no channel configured: publishing works exactly as before and nothing is queued', $r['ok'] && $r['newsletter']['status'] === 'published' && (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$none], 0) === 0 && ShareHook::summary($none)['any'] === false && ShareHook::summary($none)['available'] === []);
check('the issue JSON carries the social summary', (function () use ($none): bool {
    $item = \App\Controllers\Admin\NewsletterApiController::itemFor(Newsletter::find($none) ?? [], true);
    return isset($item['social']) && $item['social']['any'] === false;
})());
$panel = ShareHook::panel($none);
check('panel with nothing configured: no channels, every channel listed as hidden with its reason, the notes say what will happen', $panel['ok'] && $panel['configured'] === false && $panel['channels'] === [] && count($panel['hidden']) === 6 && $panel['hidden'][0]['state'] === 'off' && str_contains($panel['note'], 'already public'));

// two or three working channels with FAKE adapters
$zzConfigure('facebook', ['page_id' => '105234567890123', 'page_token' => $zzTok['fb']]);
$zzConfigure('telegram', ['bot_token' => $zzTok['tg'], 'chat' => '@zzchannel']);
$zzConfigure('x', ['mode' => 'oauth1', 'api_key' => 'zzapikey1234567890', 'api_secret' => 'zzapisecret12345678901234', 'access_token' => 'zzaccesstoken1234567890123', 'access_secret' => 'zzaccesssecret12345678901234']);
$fakeOk = static fn(string $ch): \Closure => static fn(SocialPayload $p, int $n): SocialResult => SocialResult::posted($ch . '_' . $p->postId, 'https://example.org/' . $ch . '/' . $p->postId);
$fakes = [];
foreach (['facebook', 'telegram', 'x'] as $ch) {
    $fakes[$ch] = new ZzSocialFakeChannel(Channels::get($ch), $fakeOk($ch));
    Channels::override($ch, $fakes[$ch]);
}
check('ready channels: facebook, x, telegram (instagram, linkedin, webhook are not set up)', SocialConfig::availableIds() === ['facebook', 'x', 'telegram'], json_encode(SocialConfig::availableIds()) . ' ' . json_encode(array_map(static fn(string $c): array => SocialConfig::ready($c)['reasons'], ['facebook', 'telegram', 'x'])));
$i1 = $zzIssue(['summary' => 'Summary for the flow test issue number one.']);
$i1row = Newsletter::find($i1);
$panel = ShareHook::panel($i1);
$byId = array_column($panel['channels'], null, 'id');
check('panel: only configured + enabled channels appear (3); the rest are hidden with a reason', array_keys($byId) === ['facebook', 'x', 'telegram'] && count($panel['hidden']) === 3 && $panel['hidden'][0]['id'] === 'instagram' && $panel['hidden'][0]['reason'] !== '');
check('panel: each channel has a pre-filled caption from the template, the live length, limit and counting rule; all ticked by default', $byId['x']['limit'] === 280 && $byId['x']['counting'] === 'x' && $byId['x']['length'] <= 280 && str_contains($byId['x']['caption'], 'ZZ-SOCIAL') && str_contains($byId['x']['caption'], 'utm_source=x') && $byId['facebook']['on'] === true && $byId['x']['edited'] === false && $byId['telegram']['limit'] === 1024 && $byId['telegram']['uses_image'] === true);
check('panel: the status of an issue that was never shared is "none" / "Not shared"; the image thumbnail address is the admin preview route', $byId['x']['status']['state'] === 'none' && $byId['x']['status']['label'] === 'Not shared' && str_contains($panel['image']['thumb_url'], '/admin/social/preview/' . $i1 . '/square') && $panel['issue']['is_live'] === false);
$rowBefore = Database::one('SELECT version, updated_at FROM newsletters WHERE id = ?', [$i1]);
$resBad = ShareHook::saveChoices($i1, ['channels' => ['x' => ['on' => true, 'caption' => str_repeat('a', 281)], 'instagram' => ['on' => true, 'caption' => ''], 'myspace' => ['on' => true], 'facebook' => ['on' => true, 'caption' => 'ok']]]);
check('saving choices: an over-long caption, a channel that is not set up and an unknown channel are refused; nothing is saved when one is wrong', !$resBad['ok'] && isset($resBad['errors']['x'], $resBad['errors']['instagram'], $resBad['errors']['myspace']) && str_contains($resBad['errors']['x'], '1 character too long')
    && Database::value('SELECT share_choices FROM newsletters WHERE id = ?', [$i1]) === null);
$sleepSafe = static function (): void {
    usleep(1100000);
};
$sleepSafe();     // updated_at has one-second resolution: make a change visible if it happened
$resOk = ShareHook::saveChoices($i1, ['channels' => ['x' => ['on' => false, 'caption' => ''], 'facebook' => ['on' => true, 'caption' => "My own words for <b>Facebook</b>\n\n\n\nsecond paragraph"], 'telegram' => ['on' => true, 'caption' => $byId['telegram']['caption']]]]);
$rowAfter = Database::one('SELECT version, updated_at, share_choices FROM newsletters WHERE id = ?', [$i1]);
$stored = json_decode((string) $rowAfter['share_choices'], true);
check('saving choices: stored WITH the issue; the version and updated_at of the issue do not change (no editing conflict)', $resOk['ok'] && $rowAfter['version'] === $rowBefore['version'] && $rowAfter['updated_at'] === $rowBefore['updated_at'] && $stored['x'] === ['on' => false, 'caption' => ''] && $stored['facebook']['on'] === true);
check('saving choices: a typed caption is cleaned (no tags, one blank line) and kept; the untouched template is NOT stored (it follows later edits of the title)', $stored['facebook']['caption'] === "My own words for Facebook\n\nsecond paragraph" && $stored['telegram']['caption'] === '');
$byId = array_column($resOk['panel']['channels'], null, 'id');
check('the panel reads the saved choices back (x off, facebook edited)', $byId['x']['on'] === false && $byId['facebook']['edited'] === true && str_starts_with($byId['facebook']['caption'], 'My own words for Facebook'));
$ok = NewsletterService::publish($i1, $zzUid);
$rows = array_column(PostQueue::forNewsletter($i1), null, 'channel');
check('PUBLISH: a queue row per ticked channel is created once and posted at once (CLI = right after the "response"); the unticked channel gets nothing', $ok['ok'] && array_keys($rows) === ['facebook', 'telegram'], json_encode(array_keys($rows)));
check('the rows are posted, with the remote id and link the platform returned and the user who published', $rows['facebook']['status'] === 'posted' && str_starts_with((string) $rows['facebook']['remote_id'], 'facebook_') && str_starts_with((string) $rows['facebook']['remote_url'], 'https://example.org/facebook/') && (int) $rows['facebook']['attempts'] === 1 && (int) $rows['facebook']['created_by'] === $zzUid && $rows['telegram']['status'] === 'posted');
$pf = $fakes['facebook']->calls[0];
$pt = $fakes['telegram']->calls[0];
check('the facebook payload: the edited caption, the plain and the UTM link, the issue data, no test flag', count($fakes['facebook']->calls) === 1 && $pf->caption === "My own words for Facebook\n\nsecond paragraph" && str_contains($pf->url, 'utm_source=facebook&utm_medium=social&utm_campaign=newsletter') && !str_contains($pf->plainUrl, 'utm_') && $pf->title === (string) $i1row['title'] && $pf->test === false && $pf->eventId === 'lumina-post-' . $rows['facebook']['id'] && $pf->postId === (int) $rows['facebook']['id'] && count($fakes['x']->calls) === 0);
check('the telegram payload: the template caption (current title) and a public share-image address (the cover / logo fallback until the renderer exists)', str_contains($pt->caption, 'ZZ-SOCIAL') && $pt->imageUrl !== null && preg_match('~^https?://~', $pt->imageUrl) === 1 && $pt->images !== [] && $pt->deadline !== null && $pt->deadline > microtime(true) - 5);
check('the audit trail has the queue entry (ids and counts only)', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'social.share.queued' AND entity_id = ?", [(string) $i1], 0) === 1);
NewsletterService::unpublish($i1, $zzUid);
NewsletterService::publish($i1, $zzUid);
check('UNPUBLISH + REPUBLISH never posts again (one row per issue and channel; nothing new is queued)', count($fakes['facebook']->calls) === 1 && count($fakes['telegram']->calls) === 1 && (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$i1], 0) === 2);
// "Post again" is idempotent: a second request straight after a post (a double click, a second tab) is refused
$zzAge = static function (int $id): void {
    $old = date('Y-m-d H:i:s', time() - 3600);
    Database::execute('UPDATE social_posts SET created_at = ?, posted_at = IF(posted_at IS NULL, NULL, ?), last_attempt_at = IF(last_attempt_at IS NULL, NULL, ?) WHERE newsletter_id = ?', [$old, $old, $old, $id]);
};
$early = ShareHook::postAgain($i1, ['facebook']);
$earlyPanel = array_column(ShareHook::panel($i1)['channels'], null, 'id');
check('POST AGAIN straight after a post is refused (cool-down against a double click): nothing queued, share_version unchanged, and the panel says how long to wait', !$early['ok'] && $early['queued'] === [] && str_contains((string) ($early['errors']['facebook'] ?? ''), 'a moment ago') && (int) Database::value('SELECT share_version FROM newsletters WHERE id = ?', [$i1], 0) === 1
    && $earlyPanel['facebook']['can_post_again'] === false && $earlyPanel['facebook']['again_wait'] > 0 && count($fakes['facebook']->calls) === 1);
$zzAge($i1);
$panel = ShareHook::panel($i1);
$byId = array_column($panel['channels'], null, 'id');
check('panel after publishing: "Posted - view post" with the link; x can still be shared now; posted channels offer "post again"; no retry offered', $panel['issue']['is_live'] === true && $byId['facebook']['status']['label'] === 'Posted - view post' && $byId['facebook']['status']['remote_url'] !== null && $byId['x']['can_share_now'] === true && $byId['facebook']['can_post_again'] === true && $byId['facebook']['can_share_now'] === false && $byId['facebook']['can_retry'] === false);
check('panel: a channel that already has a post shows the REAL caption of the post, read-only (locked, ticked, not tickable); a channel without a post stays editable', $byId['facebook']['locked'] === true && $byId['facebook']['has_post'] === true && $byId['facebook']['on'] === true && $byId['facebook']['caption'] === (string) $rows['facebook']['caption'] && $byId['x']['locked'] === false && $byId['x']['has_post'] === false);
$sn = ShareHook::shareNow($i1, ['x', 'telegram', 'bogus']);
check('SHARE NOW: queues only the channels that were NEVER posted (x), refuses a repeat (telegram) with the reason, refuses unknown ones', $sn['queued'] === ['x'] && !$sn['ok'] && str_contains($sn['errors']['telegram'], 'Post again') && isset($sn['errors']['bogus']) && count($fakes['x']->calls) === 1 && SocialPost::find((int) Database::value("SELECT id FROM social_posts WHERE newsletter_id = ? AND channel = 'x'", [$i1], 0))['status'] === 'posted');
check('X payload: the caption fits 280 weighted characters and ends with the shared link + hashtags', CaptionBuilder::length('x', $fakes['x']->calls[0]->caption) <= 280 && str_contains($fakes['x']->calls[0]->caption, 'utm_source=x'));
$zzAge($i1);
$stale = ShareHook::postAgain($i1, ['facebook'], null, 7);
check('POST AGAIN with a version the panel did not see (another tab already raised it) is refused: nothing is posted a second time', !$stale['ok'] && $stale['queued'] === [] && isset($stale['errors']['_']) && (int) Database::value('SELECT share_version FROM newsletters WHERE id = ?', [$i1], 0) === 1 && count($fakes['facebook']->calls) === 1);
ShareHook::saveChoices($i1, ['channels' => ['x' => ['on' => false, 'caption' => 'New words for the second post on X']]]);       // X refuses identical text: a second post needs an edited caption
$pa = ShareHook::postAgain($i1, ['facebook', 'x', 'linkedin'], null, 1);
$fbRows = Database::all("SELECT share_version, status FROM social_posts WHERE newsletter_id = ? AND channel = 'facebook' ORDER BY share_version", [$i1]);
check('POST AGAIN is an explicit action: share_version rises to 2, ONE new row for each channel that had a post, the others are refused with the reason; then it is posted', $pa['share_version'] === 2 && $pa['queued'] === ['facebook', 'x'] && isset($pa['errors']['linkedin']) && count($fbRows) === 2 && $fbRows[1]['status'] === 'posted' && (int) Database::value('SELECT share_version FROM newsletters WHERE id = ?', [$i1], 0) === 2 && count($fakes['facebook']->calls) === 2 && count($fakes['x']->calls) === 2, json_encode([$pa, $fbRows]));
$again2 = ShareHook::postAgain($i1, ['facebook'], null, 2);
check('...a repeated request right after it (same version or the old one) is refused: ONE extra post only, share_version stays 2', !$again2['ok'] && $again2['queued'] === [] && (int) Database::value('SELECT share_version FROM newsletters WHERE id = ?', [$i1], 0) === 2 && !ShareHook::postAgain($i1, ['facebook'], null, 1)['ok'] && count($fakes['facebook']->calls) === 2);
$zzAge($i1);
$xDup = ShareHook::postAgain($i1, ['x'], null, 2);
check('POST AGAIN on X with the SAME text is refused with the reason (X rejects identical text): nothing queued, the version stays', !$xDup['ok'] && $xDup['queued'] === [] && str_contains((string) ($xDup['errors']['x'] ?? ''), 'exactly the same text') && (int) Database::value('SELECT share_version FROM newsletters WHERE id = ?', [$i1], 0) === 2 && count($fakes['x']->calls) === 2);
check('the panel tells the admin about X before they press the button', (function () use ($i1): bool {
    $c = array_column(ShareHook::panel($i1)['channels'], null, 'id');
    return str_contains($c['x']['again_note'], 'exactly the same text') && $c['facebook']['again_note'] === '';
})());
NewsletterService::unpublish($i1, $zzUid);
NewsletterService::publish($i1, $zzUid);
check('...and a later republish still posts nothing (share_version 2 does not re-open the other channels)', count($fakes['facebook']->calls) === 2 && count($fakes['telegram']->calls) === 1);
check('post again / share now need a PUBLIC issue', !ShareHook::postAgain($zzIssue(), ['facebook'])['ok'] && !ShareHook::shareNow($zzIssue(), ['facebook'])['ok'] && !ShareHook::shareNow(999999999, ['facebook'])['ok']);

// scheduled go-live uses the choices saved with the issue
$i2 = $zzIssue();
ShareHook::saveChoices($i2, ['channels' => ['telegram' => ['on' => true, 'caption' => 'Scheduled telegram words'], 'facebook' => ['on' => false], 'x' => ['on' => false]]]);
$sch = NewsletterService::schedule($i2, date('Y-m-d H:i', time() + 3600), $zzUid);
check('a SCHEDULED issue queues nothing until it goes live', $sch['ok'] && $sch['newsletter']['status'] === 'scheduled' && (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$i2], 0) === 0);
$panelS = ShareHook::panel($i2);
check('the panel of a scheduled issue says it is not live, and keeps the choices', $panelS['issue']['is_live'] === false && $panelS['issue']['status'] === 'scheduled' && array_column($panelS['channels'], 'on', 'id') == ['facebook' => false, 'x' => false, 'telegram' => true] && !ShareHook::shareNow($i2, ['telegram'])['ok']);
Database::execute('UPDATE newsletters SET publish_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 30), $i2]);
$n = NewsletterService::resolveScheduled(true);
$rows2 = PostQueue::forNewsletter($i2);
check('LAZY GO-LIVE (resolveScheduled): the saved choices are used - only telegram, with the caption typed earlier, posted once', $n >= 1 && count($rows2) === 1 && $rows2[0]['channel'] === 'telegram' && $rows2[0]['status'] === 'posted' && $rows2[0]['caption'] === 'Scheduled telegram words' && $rows2[0]['created_by'] === null && Newsletter::find($i2)['status'] === 'published');
NewsletterService::resolveScheduled(true);
check('running the lazy resolver again changes nothing', count(PostQueue::forNewsletter($i2)) === 1 && count($fakes['telegram']->calls) === 2);

// an issue that was public before the module (published_at set, no rows): never shared by surprise
Settings::set('social_enabled', '0');
SocialConfig::flush();
$i3 = $zzIssue();
NewsletterService::publish($i3, $zzUid);
check('the global switch OFF: publishing queues nothing', (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$i3], 0) === 0);
Settings::set('social_enabled', '1');
SocialConfig::flush();
NewsletterService::unpublish($i3, $zzUid);
NewsletterService::publish($i3, $zzUid);
check('an issue that was already public once is NOT shared by a republish (use "Share now")', (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$i3], 0) === 0 && ShareHook::shareNow($i3, ['telegram'])['queued'] === ['telegram']);

// drafts, archived issues, switched-off channels never post. From here on publishing does not post by itself (the defaults are switched off) so every row below is queued explicitly.
foreach (['facebook', 'telegram', 'x'] as $c) {
    Settings::set(SocialConfig::key($c, 'default'), '0');
}
SocialConfig::flush();
$d1 = $zzIssue();
$qd = PostQueue::enqueue($d1, 'telegram', 1, 'draft caption');
$callsBefore = count($fakes['telegram']->calls);
$att = SocialDispatcher::attempt($qd['id']);
check('a DRAFT is never posted: the attempt is skipped and the platform is not called', $att['state'] === 'skipped' && SocialPost::find($qd['id'])['status'] === 'skipped' && str_contains((string) SocialPost::find($qd['id'])['last_error'], 'not public') && count($fakes['telegram']->calls) === $callsBefore);
$d2 = $zzIssue();
NewsletterService::publish($d2, $zzUid);
NewsletterService::archiveIssue($d2, $zzUid);
$qa = PostQueue::enqueue($d2, 'telegram', 5, 'archived caption');
check('an ARCHIVED (or unpublished) issue is never posted', SocialDispatcher::attempt($qa['id'])['state'] === 'skipped' && count($fakes['telegram']->calls) === $callsBefore);
$d3 = $zzIssue();
NewsletterService::publish($d3, $zzUid);
check('with the default switched off nothing is posted at publish time', (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$d3], 0) === 0 && count($fakes['telegram']->calls) === $callsBefore);
$qk = PostQueue::enqueue($d3, 'telegram', 9, 'kill switch caption');
Settings::set(SocialConfig::key('telegram', 'enabled'), '0');
SocialConfig::flush();
$att = SocialDispatcher::attempt($qk['id']);
check('PER-CHANNEL KILL SWITCH: a queued post of a channel that was switched off is skipped (not posted), with the reason', $att['state'] === 'skipped' && str_contains((string) SocialPost::find($qk['id'])['last_error'], 'switched off') && count($fakes['telegram']->calls) === $callsBefore);
Settings::set(SocialConfig::key('telegram', 'enabled'), '1');
SocialConfig::flush();
PostQueue::retry($qk['id']);
check('...and "Retry" after switching it on again posts it', SocialDispatcher::attempt($qk['id'])['state'] === 'posted' && count($fakes['telegram']->calls) === $callsBefore + 1);
$qg = PostQueue::enqueue($d3, 'x', 9, 'global switch caption');
Settings::set('social_enabled', '0');
SocialConfig::flush();
$att = SocialDispatcher::attempt($qg['id']);
Settings::set('social_enabled', '1');
SocialConfig::flush();
check('GLOBAL KILL SWITCH: nothing is posted while it is off, whatever is queued', $att['state'] === 'skipped' && count($fakes['x']->calls) === 2 && SocialPost::find($qg['id'])['status'] === 'skipped');

// outcomes
$script = [];
$fakes['telegram']->onPost = static function (SocialPayload $p, int $n) use (&$script): SocialResult {
    $step = array_shift($script) ?? 'ok';
    return match (true) {
        $step === 'ok'        => SocialResult::posted('tg' . $n, null),
        $step === 'transient' => SocialResult::transient('The platform is busy', null, 'server_error'),
        $step === 'failed'    => SocialResult::failed('The platform refused the post'),
        $step === 'review'    => SocialResult::review('Timed out after sending'),
        $step === 'throw'     => throw new \RuntimeException('Exploded with the secret ' . $GLOBALS['zzTok']['tg']),
        $step === 'budget'    => SocialResult::transient('no time', 30, 'budget'),
        default               => SocialResult::posted('x'),
    };
};
$mk = static function (string $channel = 'telegram') use ($zzIssue, $zzUid): array {
    $id = $zzIssue();
    NewsletterService::publish($id, $zzUid);                                  // (the defaults are off: nothing is posted by the hook)
    $r = PostQueue::enqueue($id, $channel, 1, 'outcome test caption');
    return ['issue' => $id, 'post' => $r['id']];
};
$c0 = count($fakes['telegram']->calls);
$script = ['transient', 'ok'];
$t = $mk();
$callsBase = count($fakes['telegram']->calls);
$a1 = SocialDispatcher::attempt($t['post']);
$row = SocialPost::find($t['post']);
check('outcome TRANSIENT: back to the queue with the 60 s backoff, the attempt counted, the error kept short', $a1['state'] === 'transient' && $row['status'] === 'queued' && (int) $row['attempts'] === 1 && strtotime((string) $row['next_attempt_at']) - time() >= 55 && strtotime((string) $row['next_attempt_at']) - time() <= 65 && $row['last_error'] === 'The platform is busy');
check('...a run now does NOT call it again before its time; after the wait the cron run posts it (attempt 2)', (function () use ($t, $fakes, $callsBase): bool {
    $run = SocialDispatcher::runDue(50, 20.0);
    if (count($fakes['telegram']->calls) !== $callsBase + 1) {
        return false;
    }
    Database::execute('UPDATE social_posts SET next_attempt_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 1), $t['post']]);
    $run = SocialDispatcher::runDue(50, 20.0);
    $row = SocialPost::find($t['post']);
    return $row['status'] === 'posted' && (int) $row['attempts'] === 2 && count($fakes['telegram']->calls) === $callsBase + 2 && $run['posted'] >= 1;
})());
$script = ['failed'];
$t = $mk();
$callsBase = count($fakes['telegram']->calls);
SocialDispatcher::attempt($t['post']);
SocialDispatcher::runDue(50, 20.0);
$row = SocialPost::find($t['post']);
check('outcome FAILED: status failed with the reason, never retried by itself (one call only)', $row['status'] === 'failed' && $row['last_error'] === 'The platform refused the post' && count($fakes['telegram']->calls) === $callsBase + 1);
check('...an admin can Retry it (attempts reset) and it is posted', PostQueue::retry($t['post'])['ok'] && SocialPost::find($t['post'])['status'] === 'queued' && SocialDispatcher::attempt($t['post'])['state'] === 'posted');
$script = ['review'];
$t = $mk();
$callsBase = count($fakes['telegram']->calls);
SocialDispatcher::attempt($t['post']);
SocialDispatcher::runDue(50, 20.0);
check('outcome AMBIGUOUS: status review, NOT retried by the cron / lazy run (never a blind re-post)', SocialPost::find($t['post'])['status'] === 'review' && count($fakes['telegram']->calls) === $callsBase + 1);
check('...a person decides: "Post anyway" queues it again, "Mark as posted" settles it without posting', PostQueue::retryAnyway($t['post'])['ok'] && SocialDispatcher::attempt($t['post'])['state'] === 'posted' && count($fakes['telegram']->calls) === $callsBase + 2);
$script = ['throw'];
$t = $mk();
$callsBase = count($fakes['telegram']->calls);
$a1 = SocialDispatcher::attempt($t['post']);
$row = SocialPost::find($t['post']);
check('an adapter that THROWS is treated as ambiguous (review) and its message (with a secret in it) never reaches the row', $a1['state'] === 'review' && $row['status'] === 'review' && !str_contains(json_encode($row), $zzTok['tg']) && !str_contains((string) $row['last_error'], 'Exploded') && count($fakes['telegram']->calls) === $callsBase + 1);
$script = ['budget'];
$t = $mk();
$a1 = SocialDispatcher::attempt($t['post']);
$row = SocialPost::find($t['post']);
check('a run that has no time left puts the row back WITHOUT counting the attempt', $a1['state'] === 'deferred' && $row['status'] === 'queued' && (int) $row['attempts'] === 0);
// five attempts
$script = array_fill(0, 6, 'transient');
$t = $mk();
$callsBase = count($fakes['telegram']->calls);
for ($i = 1; $i <= 6; $i++) {
    Database::execute("UPDATE social_posts SET next_attempt_at = NULL WHERE id = ? AND status = 'queued'", [$t['post']]);
    SocialDispatcher::attempt($t['post']);
}
$row = SocialPost::find($t['post']);
check('FIVE attempts at most: the fifth transient failure ends in failed; a sixth try is not made', $row['status'] === 'failed' && (int) $row['attempts'] === 5 && count($fakes['telegram']->calls) === $callsBase + 5 && str_contains((string) $row['last_error'], 'Gave up after 5'));
// runDue lock
$other = \App\Core\Database::connect((string) Config::get('db.name'));
$other->query("SELECT GET_LOCK('lumina_social_dispatch', 0)")->fetchColumn();
$busy = SocialDispatcher::runDue(5, 5.0);
$other->query("SELECT RELEASE_LOCK('lumina_social_dispatch')")->fetchColumn();
check('two cron runs never overlap: while another connection holds the named lock, runDue() answers "busy" and posts nothing', $busy['busy'] === true && $busy['attempted'] === 0 && SocialDispatcher::runDue(5, 5.0)['busy'] === false);
// lazy retry
$stamp = STORAGE_PATH . '/cache/social-lazy.stamp';
@unlink($stamp);
$script = [];
$t = $mk();
$callsBase = count($fakes['telegram']->calls);
SocialDispatcher::lazyRetry();
check('lazy retry on admin page loads: runs the due posts (after the response), then only once a minute (a stamp file, no query in between)', is_file($stamp) && SocialPost::find($t['post'])['status'] === 'posted' && count($fakes['telegram']->calls) === $callsBase + 1);
$t2 = $mk();
$callsBase = count($fakes['telegram']->calls);
SocialDispatcher::lazyRetry();
check('...a second call within the minute does nothing', SocialPost::find($t2['post'])['status'] === 'queued' && count($fakes['telegram']->calls) === $callsBase);
SocialDispatcher::runDue(50, 20.0);
@unlink($stamp);

// auth failure: needs a new token, notified ONCE (publishing posts by itself again)
foreach (['facebook', 'telegram', 'x'] as $c) {
    Settings::set(SocialConfig::key($c, 'default'), '1');
}
SocialConfig::flush();
$fakes['telegram']->onPost = $fakeOk('telegram');
$fakes['facebook']->onPost = static fn(SocialPayload $p, int $n): SocialResult => SocialResult::auth('Invalid OAuth access token ' . $GLOBALS['zzTok']['fb'], 'auth');
$mailsBefore = count($zzMails());
$fbBase = count($fakes['facebook']->calls);
$a = $zzIssue();
NewsletterService::publish($a, $zzUid);
$rowA = array_column(PostQueue::forNewsletter($a), null, 'channel');
check('AUTH failure: the post is failed, the channel is marked "needs a new token", the error is redacted', $rowA['facebook']['status'] === 'failed' && SocialConfig::status('facebook')['state'] === 'needs_token' && !str_contains(json_encode($rowA['facebook']), $zzTok['fb']) && !str_contains(SocialConfig::status('facebook')['message'], $zzTok['fb']) && count($fakes['facebook']->calls) === $fbBase + 1);
$b = $zzIssue();
NewsletterService::publish($b, $zzUid);
$rowsB = array_column(PostQueue::forNewsletter($b), null, 'channel');
check('while it needs a token the channel is not offered and no other post is attempted on it; the other channels keep working', !isset($rowsB['facebook']) && $rowsB['telegram']['status'] === 'posted' && count($fakes['facebook']->calls) === $fbBase + 1 && !SocialConfig::ready('facebook')['ready']);
$mails = $zzMails();
check('the admin is e-mailed ONCE (no token in the message), the dashboard counts it', count($mails) === $mailsBefore + 1 && str_contains($mails[count($mails) - 1], 'needs a new access token') && !str_contains(implode("\n", $mails), $zzTok['fb']) && SocialConfig::attention(true)['needs_token'] === ['facebook'] && SocialConfig::attention()['total'] >= 1);
check('the panel of a new issue hides the channel and says why; the issue that already has the failed row still shows it with "Failed - retry"', (function () use ($b, $a): bool {
    $p = ShareHook::panel($b);
    $h = array_column($p['hidden'], null, 'id');
    $pa = array_column(ShareHook::panel($a)['channels'], null, 'id');
    return isset($h['facebook']) && $h['facebook']['state'] === 'needs_token' && isset($pa['facebook']) && $pa['facebook']['status']['label'] === 'Failed - retry' && $pa['facebook']['can_retry'] === true && $pa['facebook']['available'] === false;
})());
check('panel: a channel with a FAILED post keeps an editable caption (a retry sends the saved text) and the edit can be saved even though the channel needs a token right now', (function () use ($a): bool {
    $c = array_column(ShareHook::panel($a)['channels'], null, 'id')['facebook'];
    $res = ShareHook::saveChoices($a, ['channels' => ['facebook' => ['on' => true, 'caption' => 'Edited after the failure']]]);
    $c2 = array_column(ShareHook::panel($a)['channels'], null, 'id')['facebook'];
    return $c['locked'] === false && $c['has_post'] === true && $res['ok'] === true && $c2['caption'] === 'Edited after the failure';
})());
$fakes['facebook']->onPost = $fakeOk('facebook');
$res = SocialConfig::saveChannel('facebook', ['page_token' => 'ZZNEWFB' . bin2hex(random_bytes(12))]);
check('saveChannel() reports which channels were reset (the one that needed a token)', $res['ok'] && in_array('facebook', $res['reset'], true));
$requeued = PostQueue::retryFailedForChannel('facebook');
SocialDispatcher::runDue(50, 20.0);
check('a NEW token resets the status; the posts that failed because of the old one are queued again and go out; no second e-mail', $res['ok'] && SocialConfig::status('facebook')['state'] === 'ok' && $requeued >= 1 && SocialPost::find((int) $rowA['facebook']['id'])['status'] === 'posted' && count($zzMails()) === $mailsBefore + 1 && SocialConfig::attention(true)['needs_token'] === []);
check('...and the retry that followed sent the text the admin edited AFTER the failure, not the frozen old one', (function () use ($fakes): bool {
    $last = end($fakes['facebook']->calls);
    return $last instanceof SocialPayload && $last->caption === 'Edited after the failure';
})());
// a failing hook never breaks publishing
Channels::override('facebook', new ZzSocialThrowingChannel());
$e = $zzIssue();
$r = NewsletterService::publish($e, $zzUid);
check('a hook that blows up (broken adapter metadata) is logged and swallowed: the issue is published, nothing is queued', $r['ok'] && Newsletter::find($e)['status'] === 'published' && (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$e], 0) === 0);
check('...the resolver of scheduled issues survives it too', (function () use ($zzIssue, $zzUid): bool {
    $id = $zzIssue();
    NewsletterService::schedule($id, date('Y-m-d H:i', time() + 3600), $zzUid);
    Database::execute('UPDATE newsletters SET publish_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 30), $id]);
    NewsletterService::resolveScheduled(true);
    return Newsletter::find($id)['status'] === 'published';
})());
Channels::override('facebook', $fakes['facebook']);
check('panel(): unknown issue answers ok=false; summary() never throws', ShareHook::panel(999999999)['ok'] === false && ShareHook::summary(999999999) === ['any' => false, 'available' => SocialConfig::availableIds(), 'channels' => []]);
check('SocialCard stub: the public address of an issue is a http(s) address (cover / logo fallback), nothing for an unknown issue', SocialCard::publicUrl($none, 'square') !== null && preg_match('~^https?://~', (string) SocialCard::publicUrl($none, 'square')) === 1 && SocialCard::publicUrl(999999999, 'square') === null && str_contains(SocialCard::adminUrl(5, 'portrait'), '/admin/social/preview/5/portrait') && SocialCard::resolve('nothex') === null);

// the test payload and the unknown-channel guards
$tp = ShareHook::testPayload('telegram', microtime(true) + 5);
check('a TEST payload is clearly labelled and flagged test; it never carries an issue caption', $tp->test === true && str_contains($tp->caption, 'Test post') && str_starts_with($tp->eventId, 'lumina-test-') && $tp->postId === 0);
Channels::reset();
$zzResetChannels();
check('Channels registry: the six channels in order; an unknown id throws; label() knows tiktok', Channels::ids() === ['facebook', 'instagram', 'x', 'linkedin', 'telegram', 'webhook'] && Channels::label('tiktok') === 'TikTok' && (function (): bool {
    try {
        Channels::get('myspace');
        return false;
    } catch (\InvalidArgumentException) {
        return true;
    }
})());

// =====================================================================================================================================================
echo "Social: audit fixes (secrets fail closed, borrowed tokens, caption text)\n";
$zzResetChannels();
Channels::reset();
// a webhook whose stored signing secret cannot be read (the APP_KEY changed) must STOP, never send unsigned
$whSent = [];
SocialHttp::$resolver = static fn(string $h): array => ['93.184.216.34'];
SocialHttp::$transport = static function (array $req) use (&$whSent): array {
    $whSent[] = $req;
    return ['status' => 200, 'headers' => [], 'raw' => '{"id":"x"}', 'kind' => '', 'ambiguous' => false, 'detail' => ''];
};
$zzConfigure('webhook', ['url' => 'https://hooks.example.com/zzfix', 'secret' => 'zzsigningsecret123456']);
$okReady = SocialConfig::ready('webhook')['ready'];
$r0 = Channels::get('webhook')->post(SocialConfig::channelConfig('webhook'), ShareHook::testPayload('webhook', microtime(true) + 5));
$signed = count($whSent) === 1 && isset($whSent[0]['headers']['X-Lumina-Signature']);
$whSent = [];
Settings::set(SocialConfig::secretSettingKey('webhook', 'secret'), 'enc:v1:' . rtrim(strtr(base64_encode(random_bytes(40)), '+/', '-_'), '='));       // as after an APP_KEY change
SocialConfig::flush();
$rd = SocialConfig::ready('webhook');
$r1 = Channels::get('webhook')->post(SocialConfig::channelConfig('webhook'), ShareHook::testPayload('webhook', microtime(true) + 5));
$t1 = Channels::get('webhook')->test(SocialConfig::channelConfig('webhook'));
$qid = PostQueue::enqueue($zzIssue(), 'webhook', 1, 'unreadable secret probe');
NewsletterService::publish((int) Database::value('SELECT newsletter_id FROM social_posts WHERE id = ?', [$qid['id']], 0), null);
$att = SocialDispatcher::attempt($qid['id']);
check('a webhook with a signing secret that is stored but UNREADABLE is not ready (needs_token, "enter it again"), no adapter call sends anything unsigned, and the dispatcher fails the post as an auth problem (fail_code auth)',
    $okReady && $r0->isPosted() && $signed && !$rd['ready'] && $rd['state'] === 'needs_token' && str_contains($rd['reasons'][0], 'enter it again') && !in_array('webhook', SocialConfig::availableIds(), true)
    && $r1->state === 'auth' && $t1['ok'] === false && $t1['state'] === 'auth' && $whSent === [] && $att['state'] === 'auth' && SocialPost::find($qid['id'])['status'] === 'failed' && SocialPost::find($qid['id'])['fail_code'] === 'auth', json_encode([$rd, $r1->toArray(), $att, $whSent]));
check('...missingFor() names it, the dashboard counts the channel as needing a token, and a new secret makes it ready again (and queues that failed post)', (function () use ($qid): bool {
    $m = SocialConfig::missingFor('webhook');
    $att = SocialConfig::attention(true);
    $res = SocialConfig::saveChannel('webhook', ['secret' => 'zznewsigningsecret123456']);
    $q = PostQueue::retryAuthFailures('webhook');
    return str_contains(implode(' ', $m), 'stored but unreadable') && in_array('webhook', $att['needs_token'], true) === SocialConfig::enabled('webhook') && $res['ok'] && SocialConfig::ready('webhook')['ready'] && $q['count'] === 1 && SocialPost::find($qid['id'])['status'] === 'queued';
})());
$whSent = [];
PostQueue::markSkipped($qid['id'], 'test cleanup');
$whSent = [];
// the optional Instagram token: unreadable must not silently fall back to the Facebook token
$zzResetChannels();
$zzConfigure('facebook', ['page_id' => '105234567890123', 'page_token' => $zzTok['fb']]);
$zzConfigure('instagram', ['account_id' => '17841400000000000', 'token' => 'ZZIGOWNTOKEN' . bin2hex(random_bytes(10))]);
$readyIg = SocialConfig::ready('instagram')['ready'];
Settings::set(SocialConfig::secretSettingKey('instagram', 'token'), 'enc:v1:' . rtrim(strtr(base64_encode(random_bytes(40)), '+/', '-_'), '='));
SocialConfig::flush();
check('an OPTIONAL secret that cannot be read (Instagram own token) blocks the channel instead of silently using the Facebook token', $readyIg && !SocialConfig::ready('instagram')['ready'] && SocialConfig::ready('instagram')['state'] === 'needs_token' && SocialConfig::ready('facebook')['ready'] && $whSent === []);
// a replaced Facebook token also restarts Instagram, which borrows it
SocialConfig::forgetSecret('instagram', 'token');
SocialConfig::setStatus('facebook', 'needs_token', 'The platform rejected the access token');
SocialConfig::setStatus('instagram', 'needs_token', 'The platform rejected the access token');
Settings::set(SocialConfig::key('instagram', 'notified'), date('Y-m-d H:i:s'));
SocialConfig::flush();
$resFb = SocialConfig::saveChannel('facebook', ['page_token' => 'ZZNEWFB2' . bin2hex(random_bytes(12))]);
check('replacing the Facebook token restarts Instagram too when it uses that token (status reset, "notified" cleared, both reported so their failed posts can be queued again)', $resFb['ok'] && $resFb['reset'] === ['facebook', 'instagram'] && SocialConfig::status('instagram')['state'] === 'untested' && trim((string) Settings::get(SocialConfig::key('instagram', 'notified'), '')) === '' && SocialConfig::ready('instagram')['ready'], json_encode($resFb));
$zzConfigure('instagram', ['token' => 'ZZIGOWNTOKEN' . bin2hex(random_bytes(10))]);
SocialConfig::setStatus('instagram', 'needs_token', 'The platform rejected the access token');
$resFb2 = SocialConfig::saveChannel('facebook', ['page_token' => 'ZZNEWFB3' . bin2hex(random_bytes(12))]);
check('...but not when Instagram has its OWN token (it does not use the Facebook one)', $resFb2['ok'] && !in_array('instagram', $resFb2['reset'], true) && SocialConfig::status('instagram')['state'] === 'needs_token');
SocialHttp::$transport = null;
SocialHttp::$resolver = null;
$zzResetChannels();
Settings::set(SocialConfig::key('instagram', 'account_id'), '');                          // the plain fields of this block are not left behind
Settings::set(SocialConfig::key('webhook', 'fields'), '');
SocialConfig::flush();
// caption text: only real markup is removed, a "<" that is not a tag is kept
check('captions keep the text after a "<" that is not a tag ("<5%", "<3", "x<y", "<$1"), and still remove real tags, comments, scripts and decoded markup', CaptionBuilder::sanitize('Dropout rate <5% after our programme', false) === 'Dropout rate <5% after our programme' && CaptionBuilder::sanitize('I <3 Uganda', false) === 'I <3 Uganda' && CaptionBuilder::sanitize('Yield x<y rises', false) === 'Yield x<y rises' && CaptionBuilder::sanitize('Cost <$1/day', false) === 'Cost <$1/day' && CaptionBuilder::sanitize('Funding <Youth> Fund', false) === 'Funding <Youth> Fund'
    && CaptionBuilder::sanitize('<b>Bold</b> <!-- hidden -->text &lt;i&gt;x&lt;/i&gt;', false) === 'Bold text x' && CaptionBuilder::sanitize('<p class="a>b">Para</p><br/>', false) === 'Para' && CaptionBuilder::sanitize('a<script>alert(1)</script>b', false) === 'aalert(1)b' && CaptionBuilder::build('facebook', ['title' => 'Dropout rate <5% after our programme', 'summary' => 'I <3 it', 'url' => 'https://example.org/n', 'issue_number' => 1, 'issue_date' => '2026-10-01', 'site' => 'Site'])['text'] === "Dropout rate <5% after our programme\n\nI <3 it\n\n" . CaptionBuilder::hashtags(SocialConfig::str('social_hashtags'), 30));

require __DIR__ . '/selftest-social-mock.php';
