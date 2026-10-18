<?php
/**
 * Admin > Meetings > Schedule a meeting / Edit meeting: the ONE-PAGE form. OWNER: ADMIN agent. Contract: docs/MEETINGS.md section 11.
 * Vars: $mode ('create' | 'edit'), $form (values), $meeting (row when editing), $platforms (MeetingsController::PLATFORM_HELP), $people (initial chips),
 *       $groups (initial "all members" groups), $existing (people already on the meeting), $requestCtx (the meeting request this is scheduled from, or null),
 *       $canBulk, $user, $today, $ownDraft (never invited: draft), $sentBefore (an invitation was already sent).
 * Posted names: see the contract (title date start_time duration end_time platform join_url passcode dial_in location description organiser_name organiser_email visibility
 * show_join_link_public reminder_24h reminder_1h allow_rsvp emails items bulk[] intent confirm_count request_id). All times are East Africa Time.
 */
use App\Models\Meeting;
use App\Services\Meetings\MeetingRenderer;
use App\Services\Meetings\MeetingService;
use App\Services\Meetings\MeetingTime;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-meetings.js')) ?>" defer></script>
<?php $this->end();

$isEdit = $mode === 'edit';
$id = $isEdit ? (int) $meeting['id'] : 0;
$val = static fn(string $k): string => (string) old($k, (string) ($form[$k] ?? ''));
$chk = static fn(string $k): bool => (string) old($k, (string) (int) ($form[$k] ?? 0)) === '1';
$platform = $val('platform');
$platform = isset($platforms[$platform]) ? $platform : 'google_meet';
$presets = [15, 30, 45, 60, 90];
$durRaw = (string) old('duration', (string) ($form['duration'] ?? 60));
$endTime = (string) old('end_time', (string) ($form['end_time'] ?? ''));
$custom = $durRaw === 'custom' || !in_array((int) $durRaw, $presets, true);
if ($custom && $endTime === '') {
    $endTime = (string) ($form['end_time'] ?? '');
}
$durPreset = $custom ? 'custom' : (string) (int) $durRaw;
$startTime = substr($val('start_time'), 0, 5);
$visibility = $val('visibility') === 'public' ? 'public' : 'private';
$minDate = ($isEdit && (string) $meeting['start_at'] <= MeetingTime::now()) ? '' : $today;
$errs = errors();
$labels = [
    'title' => 'Title', 'date' => 'Date', 'start_time' => 'Start time', 'end_time' => 'End time', 'duration' => 'Length', 'platform' => 'How people meet', 'join_url' => 'Meeting link',
    'passcode' => 'Passcode', 'dial_in' => 'Dial-in number', 'location' => 'Address', 'description' => 'Agenda', 'organiser_name' => 'Organiser name', 'organiser_email' => 'Organiser e-mail',
    'visibility' => 'Who can see it', 'emails' => 'E-mail addresses', 'invitees' => 'People', '_' => '',
];
$mailApi = $isEdit ? url('admin/api/meetings/' . $id . '/mail') : '';
$action = $isEdit ? url('admin/meetings/' . $id) : url('admin/meetings');
$platformData = [];
foreach ($platforms as $key => $p) {
    $platformData[$key] = ['label' => MeetingService::platformLabel($key), 'link' => $p['link'], 'passcode' => $p['passcode'], 'dial' => $p['dial'], 'place' => $p['place'], 'placeholder' => $p['placeholder'], 'help' => $p['help'], 'steps' => $p['steps']];
}
$ph = $platforms[$platform];
$title = $isEdit ? 'Edit: ' . (string) $meeting['title'] : 'Schedule a meeting';
$subtitle = $isEdit
    ? ($meeting['status'] === 'scheduled' ? 'Change the details here. Press "Save and send the update" so everyone who was invited gets the new details.' : 'This is a draft: nobody has been told about it yet.')
    : 'Fill in the details, add the people and send the invitations. Only the title, date, start time and organiser are needed to save a draft.';
?>
<div class="meetings-admin ma-page ma-page--form">
    <?= partial('partials/admin/page-header', ['title' => $title, 'subtitle' => $subtitle, 'actions' => [
        ['label' => $isEdit ? 'Back to the meeting' : 'All meetings', 'url' => url($isEdit ? 'admin/meetings/' . $id : 'admin/meetings'), 'class' => 'btn--outline', 'icon' => 'arrow-left'],
    ]]) ?>

    <?php if ($requestCtx): ?>
        <aside class="alert alert--info ma-reqbox" role="note" aria-label="The meeting request this is scheduled from">
            <?= icon('inbox') ?>
            <div class="alert__body">
                <strong class="alert__title">From a meeting request: <?= e($requestCtx['name']) ?><?= !empty($requestCtx['organisation']) ? ' (' . e($requestCtx['organisation']) . ')' : '' ?></strong>
                <?php
                $prefParts = array_filter([
                    !empty($requestCtx['preferred_date']) ? \App\Services\Calendar\CivilDate::fmt((string) $requestCtx['preferred_date'], 'D j M Y') : 'no date given',
                    strtolower(\App\Controllers\Admin\MeetingRequestsController::TIME_LABELS[$requestCtx['preferred_time'] ?? 'any'] ?? 'any time of day'),
                    !empty($requestCtx['duration_minutes']) ? (int) $requestCtx['duration_minutes'] . ' minutes' : '',
                    !empty($requestCtx['platform_pref']) ? strtolower(\App\Controllers\Admin\MeetingRequestsController::PREF_LABELS[$requestCtx['platform_pref']] ?? '') : '',
                ]);
                ?>
                <p>Topic: <strong><?= e($requestCtx['topic']) ?></strong>. Preferred: <?= e(implode(', ', $prefParts)) ?>.</p>
                <p class="ma-reqbox__msg"><?= nl2br(e(mb_strimwidth((string) $requestCtx['message'], 0, 600, '...'))) ?></p>
                <p>The person is already on the list below. When you send the invitation, the request is marked as approved.</p>
            </div>
        </aside>
    <?php endif; ?>

    <?php if ($isEdit && $sentBefore && $meeting['status'] === 'scheduled'): ?>
        <div class="alert alert--info" role="note"><?= icon('info') ?><div class="alert__body">Invitations for this meeting have already gone out. If you change the title, date, time, link or place, press <strong>Save and send the update</strong> so people get the new details.
            Changing the date or time also clears everyone's answers, because they accepted the old time.</div></div>
    <?php endif; ?>

    <form id="meeting-form" class="ma-form" method="post" action="<?= e($action) ?>" data-meeting-form data-unsaved-guard
          data-mode="<?= $isEdit ? ($meeting['status'] === 'scheduled' ? 'edit-scheduled' : 'edit-draft') : 'create' ?>" data-meeting-id="<?= $id ?>"
          data-api="<?= e(url('admin/api/meetings')) ?>" data-mail-api="<?= e($mailApi) ?>" data-user-email="<?= e((string) ($user['email'] ?? '')) ?>"
          data-platforms="<?= e((string) json_encode($platformData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="request_id" value="<?= $requestCtx ? (int) $requestCtx['id'] : 0 ?>">
        <input type="hidden" name="confirm_count" value="" data-ma-confirm-count>

        <?php $shown = array_diff_key($errs, ['_' => 1]); if ($shown !== [] || error('_') !== null): ?>
            <div class="alert alert--error ma-errors" role="alert" id="form-errors">
                <?= icon('alert-circle') ?>
                <div class="alert__body"><strong class="alert__title">Some details need your attention.</strong>
                    <ul>
                        <?php if (error('_') !== null): ?><li><?= e((string) error('_')) ?></li><?php endif; ?>
                        <?php foreach ($shown as $k => $m): ?><li><?= ($labels[$k] ?? '') !== '' ? '<strong>' . e($labels[$k]) . ':</strong> ' : '' ?><?= e((string) $m) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <section class="card" aria-labelledby="ma-s-when">
            <div class="card__header"><h2 class="card__title" id="ma-s-when">1. What and when</h2><span class="ma-tz" title="East Africa Time, UTC+3, the time in Kampala"><?= icon('clock', 'icon--sm') ?> All times are East Africa Time (EAT)</span></div>
            <div class="card__body">
                <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Meeting title', 'required' => true, 'maxlength' => 190, 'value' => $val('title'),
                    'placeholder' => 'For example: Board meeting, or Partnership talk with Gulu District', 'help' => 'People see this in the invitation and in their calendar.', 'attrs' => $isEdit ? [] : ['autofocus' => true]]) ?>
                <div class="form-grid ma-when-grid">
                    <?= partial('partials/form-field', ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'value' => $val('date'), 'min' => $minDate !== '' ? $minDate : null,
                        'attrs' => ['data-ma-date' => true]]) ?>
                    <?= partial('partials/form-field', ['name' => 'start_time', 'label' => 'Starts at (EAT)', 'type' => 'time', 'required' => true, 'value' => $startTime, 'attrs' => ['data-ma-start' => true],
                        'help' => 'East Africa Time, the time in Kampala.']) ?>
                </div>
                <fieldset class="ma-duration" data-ma-duration>
                    <legend class="field__label">How long?</legend>
                    <div class="ma-pills" role="radiogroup" aria-label="Length of the meeting">
                        <?php foreach ($presets as $mins): ?>
                            <label class="ma-pill"><input type="radio" name="duration" value="<?= $mins ?>"<?= $durPreset === (string) $mins ? ' checked' : '' ?> data-ma-dur><span><?= $mins ?> min</span></label>
                        <?php endforeach; ?>
                        <label class="ma-pill"><input type="radio" name="duration" value="custom"<?= $durPreset === 'custom' ? ' checked' : '' ?> data-ma-dur><span>Other (choose the end time)</span></label>
                    </div>
                    <div class="field<?= has_error('end_time') ? ' field--error' : '' ?> ma-endtime" data-ma-endwrap>
                        <label class="field__label" for="f-end_time">Ends at (EAT)</label>
                        <input class="input" type="time" id="f-end_time" name="end_time" value="<?= e(substr($endTime, 0, 5)) ?>" data-ma-end aria-describedby="f-end_time-help">
                        <div class="field__help" id="f-end_time-help">Leave empty to use the length chosen above. A meeting must end on the day it starts.</div>
                        <?php if (error('end_time')): ?><div class="field__error" role="alert"><?= e((string) error('end_time')) ?></div><?php endif; ?>
                    </div>
                    <?php if (has_error('duration')): ?><div class="field__error" role="alert"><?= e((string) error('duration')) ?></div><?php endif; ?>
                </fieldset>
                <p class="ma-when" data-ma-when role="status" aria-live="polite"></p>
            </div>
        </section>

        <section class="card" aria-labelledby="ma-s-how">
            <div class="card__header"><h2 class="card__title" id="ma-s-how">2. How will people meet?</h2></div>
            <div class="card__body">
                <fieldset class="ma-platforms">
                    <legend class="field__label">Type of meeting</legend>
                    <div class="ma-platform-grid">
                        <?php foreach (Meeting::PLATFORMS as $key => $label): $pp = $platforms[$key]; ?>
                            <label class="ma-platform"><input type="radio" name="platform" value="<?= e($key) ?>"<?= $platform === $key ? ' checked' : '' ?> data-ma-platform>
                                <span class="ma-platform__box"><?= icon($pp['icon'], 'icon--lg') ?><span class="ma-platform__name"><?= e($label) ?></span></span></label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (has_error('platform')): ?><div class="field__error" role="alert"><?= e((string) error('platform')) ?></div><?php endif; ?>
                </fieldset>

                <div class="ma-how">
                    <?php /* The one-line instruction and the steps for the chosen type sit OUTSIDE the "Meeting link" field: for Phone and In person that field is hidden,
                             and the instruction ("enter the number in Dial-in") must still be read. */ ?>
                    <div class="ma-how__help">
                        <p class="field__help" id="f-join_url-help" data-ma-help><?= e($ph['help']) ?></p>
                        <ol class="ma-steps" id="f-join_url-steps" data-ma-steps><?php foreach ($ph['steps'] as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ol>
                    </div>
                    <div class="field<?= has_error('join_url') ? ' field--error' : '' ?>" data-ma-field="link">
                        <label class="field__label" for="f-join_url">Meeting link <span class="field__req" aria-hidden="true" data-ma-req hidden>*</span></label>
                        <input class="input" type="text" inputmode="url" id="f-join_url" name="join_url" value="<?= e($val('join_url')) ?>" maxlength="500" autocomplete="off" spellcheck="false"
                               placeholder="<?= e($ph['placeholder']) ?>" data-ma-link aria-describedby="f-join_url-help f-join_url-steps<?= has_error('join_url') ? ' f-join_url-error' : '' ?>">
                        <?php if (has_error('join_url')): ?><div class="field__error" id="f-join_url-error" role="alert"><?= e((string) error('join_url')) ?></div><?php endif; ?>
                        <div class="ma-jitsi" data-ma-jitsi-box>
                            <button class="btn btn--outline btn--sm" type="button" data-ma-jitsi hidden><?= icon('globe', 'icon--sm') ?> Generate a free private video room</button>
                            <p class="ma-jitsi__note" data-ma-jitsi-note role="status" aria-live="polite" hidden></p>
                        </div>
                    </div>

                    <div class="form-grid ma-how__more">
                        <div class="field<?= has_error('passcode') ? ' field--error' : '' ?>" data-ma-field="passcode">
                            <label class="field__label" for="f-passcode">Passcode or PIN <span class="field__opt">(optional)</span></label>
                            <input class="input" type="text" id="f-passcode" name="passcode" value="<?= e($val('passcode')) ?>" maxlength="100" autocomplete="off">
                            <?php if (has_error('passcode')): ?><div class="field__error" role="alert"><?= e((string) error('passcode')) ?></div><?php endif; ?>
                        </div>
                        <div class="field<?= has_error('dial_in') ? ' field--error' : '' ?>" data-ma-field="dial">
                            <label class="field__label" for="f-dial_in" data-ma-dial-label>Dial-in phone number <span class="field__opt">(optional)</span></label>
                            <input class="input" type="text" inputmode="tel" id="f-dial_in" name="dial_in" value="<?= e($val('dial_in')) ?>" maxlength="190" placeholder="+256 700 000000" autocomplete="off">
                            <div class="field__help">For people who join by phone. Include the country code.</div>
                            <?php if (has_error('dial_in')): ?><div class="field__error" role="alert"><?= e((string) error('dial_in')) ?></div><?php endif; ?>
                        </div>
                        <div class="field field--full<?= has_error('location') ? ' field--error' : '' ?>" data-ma-field="place">
                            <label class="field__label" for="f-location">Address or place <span class="field__req" aria-hidden="true" data-ma-req hidden>*</span><span class="field__opt" data-ma-opt>(optional)</span></label>
                            <input class="input" type="text" id="f-location" name="location" value="<?= e($val('location')) ?>" maxlength="255" placeholder="For example: Lumina Africa Foundation office, Gulu City" autocomplete="off">
                            <?php if (has_error('location')): ?><div class="field__error" role="alert"><?= e((string) error('location')) ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card" aria-labelledby="ma-s-agenda">
            <div class="card__header"><h2 class="card__title" id="ma-s-agenda">3. Agenda (optional)</h2></div>
            <div class="card__body">
                <?= partial('partials/admin/richtext-field', ['name' => 'description', 'label' => 'What is this meeting about?', 'rows' => 8, 'value' => $val('description'),
                    'help' => 'Shown in the invitation and on the invitee page. A few lines or a short list is plenty.']) ?>
            </div>
        </section>

        <section class="card" aria-labelledby="ma-s-org">
            <div class="card__header"><h2 class="card__title" id="ma-s-org">4. Who is organising it?</h2></div>
            <div class="card__body">
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'organiser_name', 'label' => 'Organiser name', 'required' => true, 'maxlength' => 100, 'value' => $val('organiser_name'), 'autocomplete' => 'name']) ?>
                    <?= partial('partials/form-field', ['name' => 'organiser_email', 'label' => 'Organiser e-mail', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'value' => $val('organiser_email'),
                        'autocomplete' => 'email', 'help' => 'People reply to this address. Invitations are sent from the website\'s own address on the organiser\'s behalf.']) ?>
                </div>
            </div>
        </section>

        <section class="card" aria-labelledby="ma-s-who">
            <div class="card__header"><h2 class="card__title" id="ma-s-who">5. Who is invited?</h2></div>
            <div class="card__body">
                <?= partial('admin/meetings/_invitees', [
                    'idp' => 'inv', 'people' => $people, 'groups' => $groups, 'existing' => $existing, 'canBulk' => $canBulk,
                    'lead' => $isEdit ? 'Add more people (optional)' : 'Type or paste e-mail addresses',
                ]) ?>
                <?php if ($isEdit): ?><p class="field__help">To remove somebody who is already invited, open <a href="<?= e(url('admin/meetings/' . $id . '#people')) ?>">the meeting page</a>.</p><?php endif; ?>
            </div>
        </section>

        <section class="card" aria-labelledby="ma-s-vis">
            <div class="card__header"><h2 class="card__title" id="ma-s-vis">6. Who can see it, and reminders</h2></div>
            <div class="card__body">
                <fieldset class="ma-choices">
                    <legend class="field__label">Who can see this meeting?</legend>
                    <label class="ma-choice"><input type="radio" name="visibility" value="private"<?= $visibility === 'private' ? ' checked' : '' ?> data-ma-vis>
                        <span><strong>Private</strong> Only the people you invite. It appears on your team's calendar, but not on the website.</span></label>
                    <label class="ma-choice"><input type="radio" name="visibility" value="public"<?= $visibility === 'public' ? ' checked' : '' ?> data-ma-vis>
                        <span><strong>Show on the public calendar</strong> Visitors see "Meeting", the title, the date and the time. They never see who is invited.</span></label>
                </fieldset>
                <div class="ma-public" data-ma-public<?= $visibility === 'public' ? '' : ' hidden' ?>>
                    <input type="hidden" name="show_join_link_public" value="0">
                    <label class="check"><input type="checkbox" name="show_join_link_public" value="1"<?= $chk('show_join_link_public') ? ' checked' : '' ?> data-ma-publink><span>Also show the join link publicly</span></label>
                    <div class="alert alert--warning" role="note"><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title">Think before you tick this.</strong>
                        Anyone on the internet who sees a public link can join, including people you did not invite ("Zoom-bombing"). Leave it off unless the meeting is open to everybody; invited people always get the link in their own invitation.</div></div>
                </div>
                <div class="ma-options">
                    <p class="field__label">Reminders and answers</p>
                    <input type="hidden" name="reminder_24h" value="0">
                    <label class="check"><input type="checkbox" name="reminder_24h" value="1"<?= $chk('reminder_24h') ? ' checked' : '' ?>><span>Send a reminder 24 hours before</span></label>
                    <input type="hidden" name="reminder_1h" value="0">
                    <label class="check"><input type="checkbox" name="reminder_1h" value="1"<?= $chk('reminder_1h') ? ' checked' : '' ?>><span>Send a reminder 1 hour before</span></label>
                    <input type="hidden" name="allow_rsvp" value="0">
                    <label class="check"><input type="checkbox" name="allow_rsvp" value="1"<?= $chk('allow_rsvp') ? ' checked' : '' ?>><span>Let people answer Accept, Maybe or Decline <small>(adds buttons to the invitation)</small></span></label>
                    <p class="field__help">The meeting is added to the site calendar automatically as "Meeting / outreach". Private meetings stay hidden from visitors.</p>
                </div>
            </div>
        </section>

        <div class="form-actions ma-actions">
            <?php if ($isEdit && $meeting['status'] === 'scheduled'): ?>
                <button class="btn btn--outline" type="submit" name="intent" value="save"><?= icon('save', 'icon--sm') ?> Save changes</button>
                <button class="btn btn--primary" type="submit" name="intent" value="send" data-ma-send="update"><?= icon('send', 'icon--sm') ?> Save and send the update</button>
            <?php else: ?>
                <button class="btn btn--outline" type="submit" name="intent" value="<?= $isEdit ? 'save' : 'draft' ?>"><?= icon('save', 'icon--sm') ?> Save draft</button>
                <button class="btn btn--primary" type="submit" name="intent" value="send" data-ma-send="invite"><?= icon('send', 'icon--sm') ?> Save and send invitations</button>
            <?php endif; ?>
            <?php if ($isEdit): ?>
                <button class="btn btn--ghost" type="button" data-ma-test aria-describedby="ma-test-help"><?= icon('mail', 'icon--sm') ?> Send me a test invitation</button>
            <?php else: ?>
                <button class="btn btn--ghost" type="button" disabled aria-describedby="ma-test-help"><?= icon('mail', 'icon--sm') ?> Send me a test invitation</button>
            <?php endif; ?>
            <a class="btn btn--ghost" href="<?= e(url($isEdit ? 'admin/meetings/' . $id : 'admin/meetings')) ?>">Cancel</a>
            <span class="ma-actions__status" data-ma-test-status role="status" aria-live="polite"></span>
        </div>
        <p class="ma-actions__help" id="ma-test-help"><?= $isEdit ? 'The test invitation goes only to you and shows the last saved version.' : 'To send yourself a test invitation, save the draft first.' ?> Nothing is e-mailed to anybody until you press "<?= $isEdit && $meeting['status'] === 'scheduled' ? 'Save and send the update' : 'Save and send invitations' ?>" and confirm.</p>
    </form>
</div>
<?= partial('admin/meetings/_dialog') ?>
