<?php
/**
 * Admin > Meeting requests > one request: what was asked, "Schedule this meeting" (the prefilled schedule form), decline with an optional message, private notes, status, delete.
 * OWNER: ADMIN agent. Vars: $r (request row), $meeting (the scheduled meeting or null), $canDelete, $today, $erased.
 */
use App\Controllers\Admin\MeetingRequestsController as C;
use App\Services\Calendar\CivilDate;
use App\Services\Meetings\MeetingRequestService as RS;
use App\Services\Meetings\MeetingService as MS;
use App\Services\Meetings\MeetingTime;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-meetings.js')) ?>" defer></script>
<?php $this->end();

$rid = (int) $r['id'];
$status = (string) $r['status'];
$canSchedule = !$erased && $meeting === null;
$whenText = !empty($r['preferred_date']) ? CivilDate::fmt((string) $r['preferred_date'], 'l j F Y') : 'No date given';
$phone = trim((string) ($r['phone'] ?? ''));
$telHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';
$actions = [['label' => 'All requests', 'url' => url('admin/meeting-requests'), 'class' => 'btn--outline', 'icon' => 'arrow-left']];
if ($canSchedule) {
    array_unshift($actions, ['label' => 'Schedule this meeting', 'url' => url('admin/meetings/create', ['request' => $rid]), 'icon' => 'calendar']);
}
?>
<div class="meetings-admin ma-page ma-page--request">
    <?= partial('partials/admin/page-header', ['title' => 'Request from ' . (string) $r['name'], 'subtitle' => 'Received ' . format_datetime((string) $r['created_at'], 'j F Y, H:i') . '. Nothing is booked until you schedule it.', 'actions' => $actions]) ?>
    <p class="ma-lead"><?= partial('partials/admin/status-badge', ['status' => $status, 'label' => RS::STATUS_LABELS[$status] ?? $status, 'tone' => RS::STATUS_TONES[$status] ?? 'neutral']) ?>
        <span class="ma-lead__platform"><?= e((string) $r['topic']) ?></span></p>

    <?php if ($meeting): ?>
        <div class="alert alert--success" role="status"><?= icon('check-circle') ?><div class="alert__body">
            <strong class="alert__title">A meeting was scheduled from this request.</strong>
            <p><a href="<?= e(MS::adminUrl((int) $meeting['id'])) ?>"><?= e((string) $meeting['title']) ?></a> &middot; <?= e(MeetingTime::rangeLabel((string) $meeting['start_at'], (string) $meeting['end_at'])) ?> &middot; <?= e(MS::STATUS_LABELS[$meeting['status']] ?? (string) $meeting['status']) ?></p>
        </div></div>
    <?php elseif ($erased): ?>
        <div class="alert alert--info" role="note"><?= icon('shield') ?><div class="alert__body">This person's details were erased on request, so the request can no longer be scheduled or answered.</div></div>
    <?php endif; ?>

    <div class="ma-layout">
        <div class="ma-layout__main">
            <section class="card" aria-labelledby="ma-r-msg">
                <div class="card__header"><h2 class="card__title" id="ma-r-msg">What they wrote</h2></div>
                <div class="card__body">
                    <blockquote class="ma-quote"><?= nl2br(e((string) $r['message'])) ?></blockquote>
                    <dl class="ma-facts-list ma-facts-list--cols">
                        <div><dt>Topic</dt><dd><?= e((string) $r['topic']) ?></dd></div>
                        <div><dt>Preferred day</dt><dd><?= e($whenText) ?></dd></div>
                        <div><dt>Time of day</dt><dd><?= e(C::TIME_LABELS[$r['preferred_time'] ?? 'any'] ?? 'Any time of day') ?> <span class="text-muted">(East Africa Time)</span></dd></div>
                        <div><dt>Length</dt><dd><?= !empty($r['duration_minutes']) ? (int) $r['duration_minutes'] . ' minutes' : 'Not said' ?></dd></div>
                        <div><dt>Type of meeting</dt><dd><?= !empty($r['platform_pref']) ? e(C::PREF_LABELS[$r['platform_pref']] ?? '') : 'Not said' ?></dd></div>
                    </dl>
                </div>
            </section>

            <section class="card" aria-labelledby="ma-r-notes">
                <div class="card__header"><h2 class="card__title" id="ma-r-notes">Private notes</h2></div>
                <div class="card__body">
                    <form method="post" action="<?= e(url('admin/meeting-requests/' . $rid . '/status')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="<?= e($status) ?>">
                        <div class="field"><label class="field__label" for="r-notes">Only the team sees these</label>
                            <textarea class="textarea" id="r-notes" name="notes" rows="4" maxlength="3000"><?= e((string) ($r['admin_notes'] ?? '')) ?></textarea></div>
                        <button class="btn btn--outline" type="submit"><?= icon('save', 'icon--sm') ?> Save notes</button>
                    </form>
                </div>
            </section>
        </div>

        <aside class="ma-layout__side" aria-label="Request details and actions">
            <section class="card" aria-labelledby="ma-r-who">
                <div class="card__header"><h2 class="card__title" id="ma-r-who">Who</h2></div>
                <div class="card__body">
                    <dl class="ma-facts-list">
                        <div><dt>Name</dt><dd><?= e((string) $r['name']) ?></dd></div>
                        <div><dt>E-mail</dt><dd><?= $erased ? 'Erased' : '<a href="mailto:' . e((string) $r['email']) . '">' . e((string) $r['email']) . '</a>' ?></dd></div>
                        <?php if ($phone !== ''): ?><div><dt>Phone</dt><dd><a href="<?= e($telHref) ?>"><?= e($phone) ?></a></dd></div><?php endif; ?>
                        <?php if (!empty($r['organisation'])): ?><div><dt>Organisation</dt><dd><?= e((string) $r['organisation']) ?></dd></div><?php endif; ?>
                        <div><dt>Agreed</dt><dd><?= !empty($r['consent_at']) ? 'Yes, on ' . e(format_datetime((string) $r['consent_at'], 'j M Y')) : 'No record' ?></dd></div>
                    </dl>
                    <p class="field__help">The person agreed that the team may use these details to answer this request.</p>
                </div>
            </section>

            <section class="card" aria-labelledby="ma-r-do">
                <div class="card__header"><h2 class="card__title" id="ma-r-do">What now?</h2></div>
                <div class="card__body ma-manage">
                    <?php if ($canSchedule): ?>
                        <a class="btn btn--primary" href="<?= e(url('admin/meetings/create', ['request' => $rid])) ?>"><?= icon('calendar', 'icon--sm') ?> Schedule this meeting</a>
                        <p class="field__help">Opens the schedule form with the person, the topic and their preferred day and time already filled in. Sending the invitation is your reply, and the request is then marked as approved.</p>
                    <?php endif; ?>
                    <?php if ($status !== 'declined' && !$erased): ?>
                        <button class="btn btn--outline" type="button" data-ma-open="ma-decline"><?= icon('x', 'icon--sm') ?> Decline...</button>
                    <?php endif; ?>
                    <?php if ($status !== 'closed'): ?>
                        <form class="inline-form" method="post" action="<?= e(url('admin/meeting-requests/' . $rid . '/status')) ?>"><?= csrf_field() ?>
                            <input type="hidden" name="status" value="closed">
                            <button class="btn btn--ghost" type="submit"><?= icon('check', 'icon--sm') ?> Mark as closed</button></form>
                    <?php endif; ?>
                    <?php if ($status !== 'new'): ?>
                        <form class="inline-form" method="post" action="<?= e(url('admin/meeting-requests/' . $rid . '/status')) ?>"><?= csrf_field() ?>
                            <input type="hidden" name="status" value="new">
                            <button class="btn btn--ghost" type="submit"><?= icon('refresh', 'icon--sm') ?> Mark as new again</button></form>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                        <form class="inline-form" method="post" action="<?= e(url('admin/meeting-requests/' . $rid . '/delete')) ?>" data-confirm="Delete this request for good? The person's details are removed. This cannot be undone."><?= csrf_field() ?>
                            <button class="btn btn--ghost ma-danger" type="submit"><?= icon('trash', 'icon--sm') ?> Delete this request</button></form>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>
</div>

<?php if ($status !== 'declined' && !$erased): ?>
<dialog class="ma-dialog" id="ma-decline" aria-labelledby="ma-decline-title">
    <form method="post" action="<?= e(url('admin/meeting-requests/' . $rid . '/status')) ?>" class="ma-dialog__form">
        <?= csrf_field() ?>
        <input type="hidden" name="status" value="declined">
        <h2 class="ma-dialog__title" id="ma-decline-title">Decline this request?</h2>
        <div class="ma-dialog__body">
            <p>The request is marked as declined. You can also send <?= e((string) $r['name']) ?> a short message so they are not left waiting.</p>
            <label class="check"><input type="checkbox" name="notify" value="1" checked data-ma-notify><span>E-mail <?= e((string) $r['name']) ?> that the request was declined</span></label>
            <div class="field" data-ma-notify-note><label class="field__label" for="r-note">Your message <span class="field__opt">(optional, added to the e-mail)</span></label>
                <textarea class="textarea" id="r-note" name="note" rows="4" maxlength="1500" placeholder="For example: Thank you for your interest. We cannot meet this month, please write to us again in January."></textarea></div>
        </div>
        <div class="ma-dialog__actions">
            <button type="button" class="btn btn--outline" data-ma-close autofocus>Not now</button>
            <button type="submit" class="btn btn--danger">Decline the request</button>
        </div>
    </form>
</dialog>
<?php endif; ?>
