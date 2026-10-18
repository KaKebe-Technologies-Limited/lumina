<?php
/**
 * Admin > Meetings > one meeting: status, details, the sending panel hook for the MAIL agent, the people (attendee table, add, remove, resend), reminders, an update message
 * and the actions (edit / reschedule, cancel, reopen, duplicate, delete, export). OWNER: ADMIN agent. JS: assets/js/admin-meetings.js (+ admin-meetings-send.js for the batch loop).
 * Vars: $m (meeting row), $reopened (a draft that was invited before), $attendees (not removed), $summary, $n (stale unsent failed queued invited active), $entry, $fromRequest, $detailRows, $readiness (draft: what is missing),
 *       $canBulk, $user, $autoSend, $today, $canDelete.
 */
use App\Models\Meeting;
use App\Services\Calendar\CivilDate;
use App\Services\Meetings\MeetingRenderer;
use App\Services\Meetings\MeetingService as MS;
use App\Services\Meetings\MeetingTime;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-meetings.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-meetings-send.js')) ?>" defer></script>
<?php $this->end();

$id = (int) $m['id'];
$status = (string) $m['status'];
$isDraft = $status === 'draft';
$isScheduled = $status === 'scheduled';
$isCancelled = $status === 'cancelled';
$isCompleted = $status === 'completed';
$editable = $isDraft || $isScheduled;
$when = MeetingTime::rangeLabel((string) $m['start_at'], (string) $m['end_at']);
$rel = static function (string $date) use ($today): string {
    $d = CivilDate::diffDays($today, $date);
    return $d === 0 ? 'Today' : ($d === 1 ? 'Tomorrow' : ($d > 1 && $d <= 90 ? 'In ' . $d . ' days' : ($d === -1 ? 'Yesterday' : '')));
};
$relText = $rel(MeetingTime::date((string) $m['start_at']));
$joinUrl = MeetingRenderer::joinUrl($m) ?? (preg_match('~^tel:~i', (string) $m['join_url']) === 1 ? (string) $m['join_url'] : '');
$api = url('admin/api/meetings/' . $id . '/mail');
$pageUrl = url('admin/meetings/' . $id);
$s = $summary;
$ago = static fn(?string $t): string => $t ? format_datetime($t, 'j M, H:i') : '';
$platformLabel = Meeting::PLATFORMS[$m['platform']] ?? 'Other';
$state = ['id' => $id, 'status' => $status, 'sequence' => (int) $m['sequence'], 'paused' => (int) $m['send_paused'] === 1, 'counts' => $s, 'unsent' => $n['unsent'], 'stale' => $n['stale']];
$isoStart = MeetingTime::utcIso((string) $m['start_at']);
$isoEnd = MeetingTime::utcIso((string) $m['end_at']);
$pageActions = [];
if ($editable) {
    $pageActions[] = ['label' => $isScheduled ? 'Edit or reschedule' : 'Edit the draft', 'url' => url('admin/meetings/' . $id . '/edit'), 'class' => 'btn--primary', 'icon' => 'edit'];
}
$pageActions[] = ['label' => 'All meetings', 'url' => url('admin/meetings'), 'class' => 'btn--outline', 'icon' => 'arrow-left'];
?>
<div class="meetings-admin ma-page ma-page--show" data-meeting-page data-meeting-id="<?= $id ?>" data-api="<?= e($api) ?>" data-page-url="<?= e($pageUrl) ?>" data-user-email="<?= e((string) ($user['email'] ?? '')) ?>"
     data-title="<?= e((string) $m['title']) ?>" data-when="<?= e($when) ?>" data-organiser="<?= e((string) $m['organiser_name']) ?>" data-link="<?= e($joinUrl) ?>" data-status="<?= e($status) ?>">
    <?= partial('partials/admin/page-header', ['title' => (string) $m['title'], 'actions' => $pageActions]) ?>
    <p class="ma-lead">
        <?= partial('partials/admin/status-badge', ['status' => $status, 'label' => MS::STATUS_LABELS[$status] ?? $status, 'tone' => MS::STATUS_TONES[$status] ?? 'neutral']) ?>
        <span class="ma-lead__when"><?= icon('calendar', 'icon--sm') ?> <time datetime="<?= e($isoStart) ?>" data-meeting-start="<?= e($isoStart) ?>" data-meeting-end="<?= e($isoEnd) ?>"><?= e($when) ?></time><?= $relText !== '' ? ' <span class="ma-rel">' . e($relText) . '</span>' : '' ?></span>
        <span class="ma-lead__platform"><?= e($platformLabel) ?></span>
        <?= $m['visibility'] === 'public' ? '<span class="badge badge--gold">Shown on the public calendar</span>' : '<span class="badge badge--neutral">Private</span>' ?>
    </p>

    <div class="ma-banners">
        <?php if ($isDraft): ?>
            <div class="alert alert--info" role="status"><?= icon('info') ?><div class="alert__body">
                <strong class="alert__title"><?= $reopened ? 'This meeting is a draft again.' : 'This meeting is a draft. Nobody has been told about it.' ?></strong>
                <?php if ($reopened): ?>
                    <p>It was invited or cancelled before, so people may have removed it from their calendar. Check the details, then send the invitations again.</p>
                    <p><a class="btn btn--primary btn--sm" href="<?= e(url('admin/meetings/' . $id . '/edit')) ?>"><?= icon('send', 'icon--sm') ?> Edit and send it again</a></p>
                <?php elseif ($readiness !== []): ?>
                    <p>Before the invitations can go out:</p>
                    <ul><?php foreach ($readiness as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul>
                    <p><a class="btn btn--outline btn--sm" href="<?= e(url('admin/meetings/' . $id . '/edit')) ?>">Fix this in the form</a></p>
                <?php elseif ($n['unsent'] === 0): ?>
                    <p>Add the people you want to invite (see "People" below), then send the invitations.</p>
                <?php else: ?>
                    <p><button class="btn btn--primary" type="button" data-ma-queue="invite_new" data-label="Send the invitations"><?= icon('send', 'icon--sm') ?> Send invitations to <?= (int) $n['unsent'] ?> <?= $n['unsent'] === 1 ? 'person' : 'people' ?></button></p>
                <?php endif; ?>
            </div></div>
        <?php elseif ($isCancelled): ?>
            <div class="alert alert--error" role="status"><?= icon('alert-circle') ?><div class="alert__body">
                <strong class="alert__title">This meeting was cancelled<?= $m['cancelled_at'] ? ' on ' . e(format_datetime((string) $m['cancelled_at'], 'j M Y, H:i')) : '' ?>.</strong>
                <?php if (!empty($m['cancelled_reason'])): ?><p>Reason: <?= e((string) $m['cancelled_reason']) ?></p><?php endif; ?>
                <p>It is not on the website calendar any more.</p>
                <?php if ($n['queued'] > 0): ?>
                    <p>The cancellation is still being sent to some people.</p>
                <?php elseif ($n['untold'] > 0): ?>
                    <p><?= (int) $n['untold'] ?> <?= $n['untold'] === 1 ? 'person who was' : 'people who were' ?> invited <?= $n['untold'] === 1 ? 'has' : 'have' ?> not been told it is cancelled.</p>
                    <p><button class="btn btn--primary btn--sm" type="button" data-ma-queue="cancel_all" data-label="Send the cancellation"><?= icon('send', 'icon--sm') ?> Send the cancellation to everyone who was invited</button></p>
                <?php elseif ($n['invited'] > 0): ?>
                    <p>Everyone who was invited has been told.</p>
                <?php endif; ?>
                <div class="ma-inline"><form class="inline-form" method="post" action="<?= e(url('admin/meetings/' . $id . '/reopen')) ?>" data-confirm="Reopen this meeting as a draft? You can then change it and send it again."><?= csrf_field() ?>
                    <button class="btn btn--outline btn--sm" type="submit"><?= icon('refresh', 'icon--sm') ?> Reopen as a draft</button></form></div>
            </div></div>
        <?php elseif ($isCompleted): ?>
            <div class="alert alert--info" role="status"><?= icon('check-circle') ?><div class="alert__body">This meeting has taken place. You can still see who answered. To hold it again, make a copy.</div></div>
        <?php endif; ?>

        <?php if ($isScheduled && $n['stale'] > 0): ?>
            <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body">
                <strong class="alert__title"><?= (int) $n['stale'] ?> <?= $n['stale'] === 1 ? 'person has' : 'people have' ?> the old details.</strong>
                <p>You changed the meeting after they were invited. Send the updated invitation so their calendar changes too.</p>
                <p><button class="btn btn--primary btn--sm" type="button" data-ma-queue="update_all" data-label="Send the updated invitation"><?= icon('send', 'icon--sm') ?> Send updated invitation to everyone</button></p>
            </div></div>
        <?php endif; ?>
        <?php if ($isScheduled && $n['unsent'] > 0): ?>
            <div class="alert alert--info" role="status"><?= icon('user-plus') ?><div class="alert__body">
                <strong class="alert__title"><?= (int) $n['unsent'] ?> <?= $n['unsent'] === 1 ? 'person has' : 'people have' ?> not been invited yet.</strong>
                <p><button class="btn btn--primary btn--sm" type="button" data-ma-queue="invite_new" data-label="Invite the new people"><?= icon('send', 'icon--sm') ?> Invite only the new <?= $n['unsent'] === 1 ? 'person' : 'people' ?></button></p>
            </div></div>
        <?php endif; ?>
        <?php if ($isScheduled && $n['failed'] > 0): ?>
            <div class="alert alert--error" role="status"><?= icon('alert-circle') ?><div class="alert__body">
                <strong class="alert__title"><?= (int) $n['failed'] ?> <?= $n['failed'] === 1 ? 'message' : 'messages' ?> could not be sent.</strong>
                <p>The reason is shown beside each name below. You can try again after fixing the address or the mail settings.</p>
                <p><button class="btn btn--outline btn--sm" type="button" data-ma-queue="retry_failed" data-label="Try the failed messages again"><?= icon('refresh', 'icon--sm') ?> Try again</button></p>
            </div></div>
        <?php endif; ?>
        <div class="ma-live" data-ma-live role="status" aria-live="polite"></div>
    </div>

    <div class="ma-show">
        <div class="ma-top">
        <section class="card ma-send" id="sending" aria-labelledby="ma-send-title" data-meeting-send data-api="<?= e($api) ?>" data-id="<?= $id ?>" data-auto="<?= $autoSend ? '1' : '0' ?>"
                 data-status="<?= e($status) ?>" data-state="<?= e((string) json_encode($state)) ?>">
            <div class="card__header"><h2 class="card__title" id="ma-send-title">Sending</h2>
                <?php if ((int) $m['send_paused'] === 1): ?><span class="badge badge--warning">Paused</span><?php endif; ?></div>
            <div class="card__body">
                <p class="ma-send__summary" data-ma-send-summary>
                    <strong><?= (int) $s['sent'] ?></strong> sent, <strong><?= (int) $s['queued'] ?></strong> waiting to be sent, <strong><?= (int) $s['failed'] ?></strong> failed, <strong><?= (int) $s['not_sent'] ?></strong> not invited yet.
                </p>
                <?php if ((int) $m['send_paused'] === 1 && !empty($m['send_halt_reason'])): ?><p class="alert alert--warning ma-send__halt"><?= icon('alert-triangle') ?><span class="alert__body">Sending was paused: <?= e((string) $m['send_halt_reason']) ?></span></p><?php endif; ?>
                <noscript><p class="field__help">Sending needs JavaScript. Turn it on in your browser, or ask your administrator to set up the scheduled sending job.</p></noscript>
            </div>
        </section>
        <section class="card" aria-labelledby="ma-details-title">
            <div class="card__header"><h2 class="card__title" id="ma-details-title">Meeting details</h2></div>
            <div class="card__body">
                <?= partial('partials/meeting-details', ['rows' => $detailRows, 'class' => 'ma-details', 'iso_start' => $isoStart, 'iso_end' => $isoEnd]) ?>
                <?php if ($joinUrl !== ''): ?>
                    <p class="btn-row">
                        <?php if (str_starts_with($joinUrl, 'https://')): ?><a class="btn btn--outline btn--sm" href="<?= e($joinUrl) ?>" target="_blank" rel="noopener noreferrer"><?= icon('external-link', 'icon--sm') ?> Open the link</a><?php endif; ?>
                        <button class="btn btn--ghost btn--sm" type="button" data-ma-copy="<?= e($joinUrl) ?>"><?= icon('copy', 'icon--sm') ?> Copy the join link</button>
                    </p>
                <?php endif; ?>
                <?php $desc = trim((string) ($m['description'] ?? '')); if ($desc !== ''): ?>
                    <h3 class="ma-sub">Agenda</h3>
                    <div class="prose ma-prose"><?= rich($desc) ?></div>
                <?php endif; ?>
                <ul class="ma-facts">
                    <li><?= icon('eye', 'icon--sm') ?> <?= $m['visibility'] === 'public' ? 'Shown on the public calendar as "Meeting"' . ((int) $m['show_join_link_public'] === 1 ? ', with the join link' : ', without the join link') : 'Private: only invited people can see the details' ?></li>
                    <li><?= icon('bell', 'icon--sm') ?> Reminders: <?= (int) $m['reminder_24h'] || (int) $m['reminder_1h'] ? implode(' and ', array_filter([(int) $m['reminder_24h'] ? '24 hours before' : '', (int) $m['reminder_1h'] ? '1 hour before' : ''])) : 'off' ?></li>
                    <li><?= icon('check-circle', 'icon--sm') ?> Answers (Accept / Maybe / Decline): <?= (int) $m['allow_rsvp'] ? 'on' : 'off' ?></li>
                    <?php if ($fromRequest): ?><li><?= icon('inbox', 'icon--sm') ?> <span>From the <a href="<?= e(url('admin/meeting-requests/' . (int) $fromRequest['id'])) ?>">meeting request</a> of <?= e((string) $fromRequest['name']) ?></span></li><?php endif; ?>
                    <?php if ($entry): ?><li><?= icon('calendar', 'icon--sm') ?> <a href="<?= e(url('admin/calendar', ['month' => substr((string) $entry['start_date'], 0, 7), 'day' => (string) $entry['start_date']]) . '#day-panel') ?>">See it in the calendar</a></li><?php endif; ?>
                </ul>
            </div>
        </section>
        </div>
        <section class="card" id="people" aria-labelledby="ma-people-title">
            <div class="card__header">
                <h2 class="card__title" id="ma-people-title">People <span class="ma-count"><?= (int) $s['total'] ?></span></h2>
                <div class="btn-row">
                    <?php if ($isScheduled && ($n['unsent'] + $n['failed']) > 0): ?><button class="btn btn--outline btn--sm" type="button" data-ma-queue="invite_pending" data-label="Send to everyone still pending"><?= icon('send', 'icon--sm') ?> Send to everyone still pending</button><?php endif; ?>
                    <?php if ((int) $s['total'] > 0): ?><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/meetings/' . $id . '/attendees.csv')) ?>"><?= icon('download', 'icon--sm') ?> Export CSV</a><?php endif; ?>
                </div>
            </div>
            <div class="card__body">
                <ul class="ma-stats" aria-label="Summary of answers">
                    <li class="ma-stat"><strong><?= (int) $s['total'] ?></strong><span>On the list</span></li>
                    <li class="ma-stat ma-stat--ok"><strong><?= (int) $s['accepted'] ?></strong><span>Accepted</span></li>
                    <li class="ma-stat ma-stat--maybe"><strong><?= (int) $s['tentative'] ?></strong><span>Maybe</span></li>
                    <li class="ma-stat ma-stat--no"><strong><?= (int) $s['declined'] ?></strong><span>Declined</span></li>
                    <li class="ma-stat"><strong><?= (int) $s['pending'] ?></strong><span>No answer yet</span></li>
                    <li class="ma-stat<?= (int) $s['failed'] > 0 ? ' ma-stat--no' : '' ?>"><strong><?= (int) $s['failed'] ?></strong><span>Failed to send</span></li>
                </ul>

                <?php if ($attendees === []): ?>
                    <?= partial('partials/empty-state', ['icon' => 'users', 'tag' => 'h3', 'title' => 'Nobody is invited yet', 'text' => $editable ? 'Add people below. You can type or paste e-mail addresses, or pick members and colleagues.' : 'Nobody was invited to this meeting.']) ?>
                <?php else: ?>
                    <div class="ma-tablebar">
                        <div class="search-input"><?= icon('search', 'icon--sm') ?><label class="visually-hidden" for="ma-people-filter">Find a person</label>
                            <input class="input" id="ma-people-filter" type="search" placeholder="Find a person" autocomplete="off"></div>
                        <div class="ma-filter" role="group" aria-label="Show">
                            <button class="pills__item is-active" type="button" data-ma-filter="all" aria-pressed="true">All</button>
                            <button class="pills__item" type="button" data-ma-filter="accepted" aria-pressed="false">Accepted</button>
                            <button class="pills__item" type="button" data-ma-filter="tentative" aria-pressed="false">Maybe</button>
                            <button class="pills__item" type="button" data-ma-filter="declined" aria-pressed="false">Declined</button>
                            <button class="pills__item" type="button" data-ma-filter="pending" aria-pressed="false">No answer</button>
                            <button class="pills__item" type="button" data-ma-filter="failed" aria-pressed="false">Failed</button>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table class="table table--stack ma-table" id="ma-people-table">
                            <caption class="visually-hidden">People invited to <?= e((string) $m['title']) ?></caption>
                            <thead><tr><th scope="col">Person</th><th scope="col">Invitation</th><th scope="col">Answer</th><th scope="col">Reminders</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th></tr></thead>
                            <tbody>
                            <?php foreach ($attendees as $a):
                                $aid = (int) $a['id'];
                                $inv = (string) $a['invite_status'];
                                $oldVersion = $a['invite_sent_at'] !== null && in_array($inv, ['sent', 'failed'], true) && ($a['invite_sequence'] === null || (int) $a['invite_sequence'] < (int) $m['sequence']);
                                $display = trim((string) $a['name']) !== '' ? (string) $a['name'] : (string) $a['email'];
                                ?>
                                <tr data-ma-row data-rsvp="<?= e((string) $a['rsvp']) ?>" data-invite="<?= e($inv) ?>" data-invited="<?= $a['invite_sent_at'] !== null ? '1' : '0' ?>" data-attendee-id="<?= $aid ?>" data-person="<?= e($display) ?>">
                                    <td data-label="Person"><div class="ma-cell">
                                        <span class="cell-title"><?= e($display) ?></span>
                                        <span class="cell-sub"><?= trim((string) $a['name']) !== '' ? e((string) $a['email']) . ' &middot; ' : '' ?><?= e(MS::SOURCE_LABELS[$a['source']] ?? (string) $a['source']) ?></span>
                                    </div></td>
                                    <td data-label="Invitation"><div class="ma-cell">
                                        <?= partial('partials/admin/status-badge', ['status' => $inv, 'label' => MS::INVITE_LABELS[$inv] ?? $inv, 'tone' => ['sent' => 'success', 'failed' => 'danger', 'queued' => 'info', 'not_sent' => 'neutral'][$inv] ?? 'neutral']) ?>
                                        <?php if ($inv === 'failed' && !empty($a['last_error'])): ?><span class="cell-sub ma-err"><?= e((string) $a['last_error']) ?></span>
                                        <?php elseif ($a['invite_sent_at'] !== null): ?><span class="cell-sub">First sent <?= e($ago((string) $a['invite_sent_at'])) ?></span><?php endif; ?>
                                        <?php if ($oldVersion && $isScheduled): ?><span class="cell-sub ma-warn">Has the old details</span><?php endif; ?>
                                    </div></td>
                                    <td data-label="Answer"><div class="ma-cell">
                                        <?= partial('partials/admin/status-badge', ['status' => (string) $a['rsvp'], 'label' => MS::RSVP_LABELS[$a['rsvp']] ?? (string) $a['rsvp'], 'tone' => ['accepted' => 'success', 'declined' => 'danger', 'tentative' => 'warning', 'pending' => 'neutral'][$a['rsvp']] ?? 'neutral']) ?>
                                        <?php if ($a['rsvp_at'] !== null): ?><span class="cell-sub"><?= e($ago((string) $a['rsvp_at'])) ?></span><?php endif; ?>
                                    </div></td>
                                    <td data-label="Reminders"><div class="ma-cell">
                                        <?php if (!(int) $m['reminder_24h'] && !(int) $m['reminder_1h']): ?><span class="cell-sub">Off</span>
                                        <?php else: ?>
                                            <?php if ((int) $m['reminder_24h']): ?><span class="cell-sub">24 h: <?= $a['reminder_24h_sent_at'] !== null ? 'sent ' . e($ago((string) $a['reminder_24h_sent_at'])) : 'not yet' ?></span><?php endif; ?>
                                            <?php if ((int) $m['reminder_1h']): ?><span class="cell-sub">1 h: <?= $a['reminder_1h_sent_at'] !== null ? 'sent ' . e($ago((string) $a['reminder_1h_sent_at'])) : 'not yet' ?></span><?php endif; ?>
                                        <?php endif; ?>
                                    </div></td>
                                    <td class="col-actions" data-label="">
                                        <div class="table__actions">
                                            <?php if ($isScheduled): ?>
                                                <button class="btn btn--ghost btn--sm" type="button" data-ma-queue="one" data-attendee-id="<?= $aid ?>" data-label="Send the invitation again" data-person="<?= e($display) ?>"
                                                        aria-label="<?= $a['invite_sent_at'] === null ? 'Send the invitation to' : 'Resend the invitation to' ?> <?= e($display) ?>"><?= icon('send', 'icon--sm') ?> <span class="ma-btn-text"><?= $a['invite_sent_at'] === null ? 'Send' : 'Resend' ?></span></button>
                                            <?php endif; ?>
                                            <?php if ($editable): ?>
                                                <button class="btn btn--ghost btn--sm" type="button" data-ma-remove data-action="<?= e(url('admin/meetings/' . $id . '/attendees/' . $aid . '/remove')) ?>" data-person="<?= e($display) ?>"
                                                        data-invited="<?= $a['invite_sent_at'] !== null && $isScheduled ? '1' : '0' ?>" aria-label="Remove <?= e($display) ?> from the meeting"><?= icon('x', 'icon--sm') ?> <span class="ma-btn-text">Remove</span></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php /* announced politely: how many people the filter or the "show all" button leaves visible */ ?>
                    <p class="field__help ma-filter-status" data-ma-filter-status role="status" aria-live="polite"></p>
                <?php endif; ?>

                <?php if ($editable): ?>
                    <details class="ma-add" id="add-people"<?= $attendees === [] ? ' open' : '' ?>>
                        <summary><?= icon('user-plus', 'icon--sm') ?> Add people</summary>
                        <form method="post" action="<?= e(url('admin/meetings/' . $id . '/attendees')) ?>" class="ma-add__form" data-ma-add-form>
                            <?= csrf_field() ?>
                            <?= partial('admin/meetings/_invitees', ['idp' => 'add', 'people' => [], 'groups' => [], 'existing' => [], 'canBulk' => $canBulk, 'lead' => 'Type or paste e-mail addresses', 'hint' => 'Choose the people to add, then press "Add to this meeting".']) ?>
                            <?php if ($isScheduled): ?>
                                <label class="check"><input type="checkbox" name="invite_now" value="1" checked><span>Send the invitation to the new people now <small>(people who already have it are not e-mailed again)</small></span></label>
                            <?php else: ?>
                                <p class="field__help">This is a draft, so nobody is e-mailed until you send the invitations.</p>
                            <?php endif; ?>
                            <button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Add to this meeting</button>
                        </form>
                    </details>
                <?php endif; ?>
            </div>
        </section>
        <div class="ma-tools">
        <?php if ($isScheduled): ?>
            <section class="card" id="message" aria-labelledby="ma-msg-title">
                <div class="card__header"><h2 class="card__title" id="ma-msg-title">Send an update</h2></div>
                <div class="card__body">
                    <p class="field__help">A short message to the people on the list, for example "We start 15 minutes late". It does not change the meeting. Each person gets their own e-mail.</p>
                    <form class="ma-msg" data-ma-message novalidate>
                        <div class="field"><label class="field__label" for="ma-msg-to">Send to</label>
                            <select class="select" id="ma-msg-to" data-ma-msg-to>
                                <option value="all">Everyone who has not declined</option>
                                <option value="accepted">Only people who accepted</option>
                                <option value="tentative">Only people who answered Maybe</option>
                                <option value="pending">Only people who have not answered</option>
                            </select></div>
                        <div class="field"><label class="field__label" for="ma-msg-subject">Subject <span class="field__opt">(optional)</span></label>
                            <input class="input" type="text" id="ma-msg-subject" maxlength="150" data-ma-msg-subject placeholder="About <?= e(mb_strimwidth((string) $m['title'], 0, 60, '...')) ?>"></div>
                        <div class="field"><label class="field__label" for="ma-msg-text">Your message</label>
                            <textarea class="textarea" id="ma-msg-text" rows="5" maxlength="5000" required data-ma-msg-text></textarea></div>
                        <button class="btn btn--primary" type="submit" data-ma-msg-send><?= icon('send', 'icon--sm') ?> Send the message</button>
                        <p class="ma-msg__status" data-ma-msg-status role="status" aria-live="polite"></p>
                    </form>
                </div>
            </section>

            <section class="card" id="reminders" aria-labelledby="ma-rem-title">
                <div class="card__header"><h2 class="card__title" id="ma-rem-title">Reminders</h2></div>
                <div class="card__body">
                    <p class="field__help">Reminders go out by themselves <?= (int) $m['reminder_24h'] || (int) $m['reminder_1h'] ? '(' . e(implode(' and ', array_filter([(int) $m['reminder_24h'] ? '24 hours before' : '', (int) $m['reminder_1h'] ? '1 hour before' : '']))) . ')' : '(they are switched off for this meeting)' ?> when the scheduled sending job runs. You can also send one right now to everyone who was invited and has not had it.</p>
                    <div class="btn-row">
                        <button class="btn btn--outline btn--sm" type="button" data-ma-reminder="24h">Send the 24-hour reminder now</button>
                        <button class="btn btn--outline btn--sm" type="button" data-ma-reminder="1h">Send the 1-hour reminder now</button>
                    </div>
                    <p class="ma-msg__status" data-ma-reminder-status role="status" aria-live="polite"></p>
                </div>
            </section>
        <?php endif; ?>

        <section class="card" aria-labelledby="ma-act-title">
            <div class="card__header"><h2 class="card__title" id="ma-act-title">Manage</h2></div>
            <div class="card__body ma-manage">
                <?php if ($editable): ?>
                    <button class="btn btn--ghost btn--sm" type="button" data-ma-test><?= icon('mail', 'icon--sm') ?> Send me a test invitation</button>
                    <span class="ma-actions__status" data-ma-test-status role="status" aria-live="polite"></span>
                <?php endif; ?>
                <button class="btn btn--ghost btn--sm" type="button" data-ma-open="ma-duplicate"><?= icon('copy', 'icon--sm') ?> Make a copy</button>
                <?php if ($isScheduled && $m['invitations_sent_at'] === null): ?>
                    <form class="inline-form" method="post" action="<?= e(url('admin/meetings/' . $id . '/reopen')) ?>" data-confirm="Take this meeting back to a draft?"><?= csrf_field() ?>
                        <button class="btn btn--ghost btn--sm" type="submit"><?= icon('refresh', 'icon--sm') ?> Back to a draft</button></form>
                <?php endif; ?>
                <?php if ($editable): ?>
                    <button class="btn btn--ghost btn--sm ma-danger" type="button" data-ma-open="ma-cancel"><?= icon('x', 'icon--sm') ?> Cancel this meeting</button>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                    <form class="inline-form" method="post" action="<?= e(url('admin/meetings/' . $id . '/delete')) ?>" data-confirm="Delete this draft for good? This cannot be undone."><?= csrf_field() ?>
                        <button class="btn btn--ghost btn--sm ma-danger" type="submit"><?= icon('trash', 'icon--sm') ?> Delete this draft</button></form>
                <?php elseif (!$isCancelled && !$isDraft): ?>
                    <p class="field__help">A meeting that was sent cannot be deleted. Cancel it instead, so everyone is told.</p>
                <?php endif; ?>
            </div>
        </section>
        </div>
    </div>
</div>

<?php if ($editable): ?>
<dialog class="ma-dialog" id="ma-cancel" aria-labelledby="ma-cancel-title">
    <form method="post" action="<?= e(url('admin/meetings/' . $id . '/cancel')) ?>" class="ma-dialog__form">
        <?= csrf_field() ?>
        <h2 class="ma-dialog__title" id="ma-cancel-title">Cancel this meeting?</h2>
        <div class="ma-dialog__body">
            <p>"<?= e((string) $m['title']) ?>" on <?= e($when) ?> will be cancelled and removed from the website calendar.</p>
            <div class="field"><label class="field__label" for="ma-cancel-reason">Reason <span class="field__opt">(optional, shown to the people you tell)</span></label>
                <textarea class="textarea" id="ma-cancel-reason" name="reason" rows="3" maxlength="500"></textarea></div>
            <?php if ($n['invited'] > 0): ?>
                <label class="check"><input type="checkbox" name="send_cancellation" value="1" checked><span>Tell the <?= (int) $n['invited'] ?> <?= $n['invited'] === 1 ? 'person' : 'people' ?> who got an invitation <small>(they get a cancellation that removes it from their calendar)</small></span></label>
            <?php else: ?>
                <p class="field__help">Nobody was invited yet, so nobody needs to be told.</p>
            <?php endif; ?>
        </div>
        <div class="ma-dialog__actions">
            <button type="button" class="btn btn--outline" data-ma-close autofocus>Keep the meeting</button>
            <button type="submit" class="btn btn--danger">Cancel the meeting</button>
        </div>
    </form>
</dialog>
<?php endif; ?>

<dialog class="ma-dialog" id="ma-duplicate" aria-labelledby="ma-dup-title">
    <form method="post" action="<?= e(url('admin/meetings/' . $id . '/duplicate')) ?>" class="ma-dialog__form">
        <?= csrf_field() ?>
        <h2 class="ma-dialog__title" id="ma-dup-title">Make a copy of this meeting?</h2>
        <div class="ma-dialog__body">
            <p>The copy is a new draft one week later with no answers. Nothing is sent. You choose its date and time on the next screen.</p>
            <?php if ((int) $s['total'] > 0): ?>
                <label class="check"><input type="checkbox" name="copy_attendees" value="1" checked><span>Copy the <?= (int) $s['total'] ?> <?= $s['total'] === 1 ? 'person' : 'people' ?> too</span></label>
            <?php endif; ?>
        </div>
        <div class="ma-dialog__actions">
            <button type="button" class="btn btn--outline" data-ma-close autofocus>Not now</button>
            <button type="submit" class="btn btn--primary"><?= icon('copy', 'icon--sm') ?> Make the copy</button>
        </div>
    </form>
</dialog>

<dialog class="ma-dialog" id="ma-remove" aria-labelledby="ma-remove-title">
    <form method="post" action="" class="ma-dialog__form" data-ma-remove-form>
        <?= csrf_field() ?>
        <h2 class="ma-dialog__title" id="ma-remove-title">Remove <span data-ma-fill="person"></span>?</h2>
        <div class="ma-dialog__body">
            <p>They will no longer be on the list, and their personal link to the meeting stops working.</p>
            <label class="check" data-ma-remove-notify hidden><input type="checkbox" name="send_cancellation" value="1"><span>Also e-mail them that they are no longer invited <small>(removes the invitation from their calendar)</small></span></label>
        </div>
        <div class="ma-dialog__actions">
            <button type="button" class="btn btn--outline" data-ma-close autofocus>Keep them</button>
            <button type="submit" class="btn btn--danger">Remove</button>
        </div>
    </form>
</dialog>
<?= partial('admin/meetings/_dialog') ?>
