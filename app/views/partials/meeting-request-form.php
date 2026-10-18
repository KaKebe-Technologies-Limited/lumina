<?php
/**
 * "Request a meeting" section of the Contact page (rendered by web/contact/index.php, between the contact form and the calendar). Anchor #meeting-request.
 * Prints NOTHING unless ALL of these hold: the meeting tables exist and the setting "Accept meeting requests" is on (MeetingRequestService::enabled()),
 * the registry switch contact > meeting_request is on (section_begin / section_on), and the contact page is published (we are on it).
 *
 * Copy: registry page "contact", section "meeting_request" (Admin > Pages > Contact); the topics: Settings > Meetings. Posts to /contact/meeting-request
 * (Web\MeetingRequestController). Works without JavaScript. The inputs are prefixed mr_ and use their own flash keys (meeting_request_errors / _old /
 * _sent), so the main contact form never shows this form's errors. Nothing is booked: staff reply from Admin > Calendar > Meeting requests.
 */

use App\Core\Session;
use App\Models\MeetingRequest;
use App\Services\Calendar\CivilDate;
use App\Services\Meetings\MeetingRequestService;
use App\Services\Meetings\MeetingTime;

if (!MeetingRequestService::enabled()) {
    return;
}
$c = static fn(string $key): string => (string) content('contact', 'meeting_request.' . $key);

$sent = Session::getFlash('meeting_request_sent');
$errors = Session::getFlash('meeting_request_errors', []);
$errors = is_array($errors) ? $errors : [];
$old = Session::getFlash('meeting_request_old', []);
$old = is_array($old) ? $old : [];
$val = static fn(string $k, string $default = ''): string => isset($old[$k]) && is_string($old[$k]) ? $old[$k] : $default;
$err = static fn(string $k): ?string => isset($errors[$k]) && $errors[$k] !== '' ? (string) $errors[$k] : null;

// the choices
$topics = array_combine(MeetingRequestService::topics(), MeetingRequestService::topics());
$windows = MeetingRequestService::windows();
$platforms = ['' => $c('platform_none')] + MeetingRequestService::platformPrefs();
$unit = $c('duration_unit');
$durations = [];
foreach (MeetingRequest::DURATIONS as $d) {
    $durations[(string) $d] = $d . ($unit !== '' ? ' ' . $unit : '');
}
$tomorrow = CivilDate::addDays(MeetingTime::today(), 1);
$latest = CivilDate::addDays(MeetingTime::today(), 366);

// ids + labels for the error summary links
$fields = [
    'mr_name' => ['mr-name', $c('name_label')], 'mr_email' => ['mr-email', $c('email_label')], 'mr_phone' => ['mr-phone', $c('phone_label')],
    'mr_organisation' => ['mr-organisation', $c('organisation_label')], 'mr_topic' => ['mr-topic', $c('topic_label')], 'mr_message' => ['mr-message', $c('message_label')],
    'mr_date' => ['mr-date', $c('date_label')], 'mr_time' => ['mr-time', $c('window_label')], 'mr_duration' => ['mr-duration', $c('duration_label')],
    'mr_platform' => ['mr-platform', $c('platform_label')], 'mr_consent' => ['mr-consent', ''],
];
$field = static fn(array $vars): string => partial('web/meetings/_field', $vars);

section_begin('contact', 'meeting_request', ['class' => 'meeting-request section--cool', 'labelledby' => 'meeting-request-title']);
?>
<div class="meeting-request__layout">
    <div class="meeting-request__intro">
        <?= partial('partials/section-head', ['eyebrow' => $c('eyebrow'), 'title' => $c('title'), 'lead' => $c('intro'), 'id' => 'meeting-request-title', 'class' => 'meeting-request__head']) ?>
        <span class="meeting-request__badge" aria-hidden="true"><?= icon('video', 'icon--xl') ?></span>
    </div>

    <div class="meeting-request__panel">
        <?php if ($sent): ?>
            <div class="alert alert--success meeting-request__done" role="status" tabindex="-1" data-meeting-request-done>
                <?= icon('check-circle') ?>
                <div class="alert__body">
                    <strong class="alert__title"><?= e($c('success_title')) ?></strong>
                    <p><?= e($c('success_text')) ?></p>
                </div>
            </div>
        <?php else: ?>
            <form class="form-card ia-form meeting-request__form" method="post" action="<?= e(url('contact/meeting-request')) ?>#meeting-request" aria-labelledby="meeting-request-title" novalidate>
                <?php if ($errors): ?>
                    <?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/form-summary.js')) ?>" defer></script><?php $this->end(); ?>
                    <div class="ia-error-summary" id="meeting-request-errors" role="alert" tabindex="-1" aria-labelledby="meeting-request-errors-title" data-error-summary>
                        <h3 class="ia-error-summary__title" id="meeting-request-errors-title"><?= icon('alert-circle') ?> <span><?= e($c('error_title')) ?></span></h3>
                        <ul>
                            <?php foreach ($errors as $key => $message): $key = (string) $key; $meta = $fields[$key] ?? null; ?>
                                <li>
                                    <?php if ($meta !== null): ?>
                                        <a href="#<?= e($meta[0]) ?>" data-error-link><?= $meta[1] !== '' ? e($meta[1]) . (preg_match('/[?.!:]$/u', $meta[1]) === 1 ? ' ' : ': ') : '' ?><?= e((string) $message) ?></a>
                                    <?php else: ?>
                                        <?= e((string) $message) ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?= csrf_field() ?>
                <?= honeypot_field() ?>
                <div class="form-grid">
                    <?= $field(['name' => 'mr_name', 'id' => 'mr-name', 'label' => $c('name_label'), 'value' => $val('mr_name'), 'error' => $err('mr_name'), 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                    <?= $field(['name' => 'mr_email', 'id' => 'mr-email', 'label' => $c('email_label'), 'type' => 'email', 'value' => $val('mr_email'), 'error' => $err('mr_email'), 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                    <?= $field(['name' => 'mr_phone', 'id' => 'mr-phone', 'label' => $c('phone_label'), 'type' => 'tel', 'value' => $val('mr_phone'), 'error' => $err('mr_phone'), 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                    <?= $field(['name' => 'mr_organisation', 'id' => 'mr-organisation', 'label' => $c('organisation_label'), 'value' => $val('mr_organisation'), 'error' => $err('mr_organisation'), 'autocomplete' => 'organization', 'maxlength' => 190]) ?>
                    <?= $field(['name' => 'mr_topic', 'id' => 'mr-topic', 'label' => $c('topic_label'), 'type' => 'select', 'options' => $topics, 'empty' => $c('topic_placeholder'), 'value' => $val('mr_topic'), 'error' => $err('mr_topic'), 'required' => true, 'class' => 'field--full']) ?>
                    <?= $field(['name' => 'mr_message', 'id' => 'mr-message', 'label' => $c('message_label'), 'type' => 'textarea', 'rows' => 5, 'value' => $val('mr_message'), 'error' => $err('mr_message'), 'required' => true, 'maxlength' => 3000, 'class' => 'field--full']) ?>
                    <?= $field(['name' => 'mr_date', 'id' => 'mr-date', 'label' => $c('date_label'), 'type' => 'date', 'value' => $val('mr_date'), 'error' => $err('mr_date'), 'min' => $tomorrow, 'max' => $latest, 'class' => 'field--pair']) ?>
                    <?= $field(['name' => 'mr_time', 'id' => 'mr-time', 'label' => $c('window_label'), 'type' => 'select', 'options' => $windows, 'value' => $val('mr_time', 'any'), 'error' => $err('mr_time'), 'class' => 'field--pair']) ?>
                    <?= $field(['name' => 'mr_duration', 'id' => 'mr-duration', 'label' => $c('duration_label'), 'type' => 'select', 'options' => $durations, 'value' => $val('mr_duration', '30'), 'error' => $err('mr_duration'), 'class' => 'field--pair']) ?>
                    <?= $field(['name' => 'mr_platform', 'id' => 'mr-platform', 'label' => $c('platform_label'), 'type' => 'select', 'options' => $platforms, 'value' => $val('mr_platform'), 'error' => $err('mr_platform'), 'class' => 'field--pair']) ?>
                    <?= $field(['name' => 'mr_consent', 'id' => 'mr-consent', 'label' => $c('consent_label'), 'type' => 'checkbox', 'value' => $val('mr_consent'), 'error' => $err('mr_consent'), 'required' => true, 'class' => 'field--full']) ?>
                </div>
                <div class="form-actions">
                    <button class="btn btn--cta btn--caps btn--lg" type="submit" data-loading-text="<?= e($c('sending')) ?>"><?= e($c('submit')) ?></button>
                </div>
                <?php if (trim($c('privacy_note')) !== ''): ?>
                    <p class="form-note mt-4"><?= e($c('privacy_note')) ?>
                        <?php if (trim((string) content('contact', 'form.privacy_link_label')) !== ''): ?><a href="<?= e(url('privacy')) ?>"><?= e(content('contact', 'form.privacy_link_label')) ?></a><?php endif; ?></p>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php section_end(); ?>
