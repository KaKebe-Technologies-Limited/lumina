<?php
/**
 * Invitee page /meeting/{token} (Web\MeetingController::show). Vars: $data (MeetingRenderer::pageData: state, title, vars, rows, agenda_html, join_url,
 * ics_url, start_iso, end_iso, cancel_reason, rsvp{allowed,current,at,options}), $token, $suggest (answer the e-mail button named, or ''),
 * $saved (the stored answer when the visitor was just redirected after saving, or ''), $error (bool: the answer value was wrong).
 * Every word comes from the registry page "site_meetings" (Admin > Pages > Meetings); the meeting's own words (title, organiser, agenda) from the meeting.
 * Only THIS invitee's data is on the page: no other attendee is ever shown. meetings.js (in <head>, deferred so it runs before main.js) switches the
 * analytics script off here (the address contains the personal link) and adds "in your time zone".
 */

use App\Services\Meetings\MeetingRenderer as R;

$this->start('head'); ?>
<script src="<?= e(asset('js/meetings.js')) ?>" defer></script>
<?php $this->end();

$state = (string) $data['state'];
$active = $state === 'active';
$vars = (array) $data['vars'];
$rsvp = (array) $data['rsvp'];
$answers = (array) $rsvp['options'];                       // accepted | tentative | declined => button text
$current = (string) $rsvp['current'];                      // pending | accepted | tentative | declined
$hasAnswer = isset($answers[$current]);
$rows = (array) $data['rows'];
if ($active) {
    $rows = array_values(array_filter($rows, static fn(array $r): bool => $r['key'] !== 'when'));   // the "when" block above shows it bigger
}
$intro = $state === 'cancelled' ? '' : trim(R::fill(R::tf('page.intro'), $vars));   // a cancelled meeting does not say "invited you"
$savedText = $saved !== '' ? R::tf('rsvp.saved_' . $saved) : '';
$icons = ['accepted' => 'check-circle', 'tentative' => 'help-circle', 'declined' => 'x'];
?>
<?= partial('partials/page-hero', ['title' => (string) $data['title'], 'eyebrow' => R::tf('page.eyebrow'), 'lead' => $intro, 'sheet' => 'tint']) ?>

<section class="section section--tint meeting-page meeting-page--<?= e($state) ?>" data-meeting-page>
    <div class="container">
        <?php if (!$active): ?>
            <div class="meeting-page__solo">
                <div class="meeting-state meeting-state--<?= e($state) ?>" role="status">
                    <span class="meeting-state__icon"><?= icon($state === 'cancelled' ? 'alert-triangle' : 'check-circle', 'icon--lg') ?></span>
                    <div class="meeting-state__body">
                        <h2 class="meeting-state__title"><?= e(R::tf($state === 'cancelled' ? 'page.cancelled_title' : 'page.ended_title')) ?></h2>
                        <p><?= e(R::tf($state === 'cancelled' ? 'page.cancelled_text' : 'page.ended_text')) ?></p>
                        <?php if ($state === 'cancelled' && (string) $data['cancel_reason'] !== ''): ?>
                            <p class="meeting-state__reason"><strong><?= e(R::tf('page.reason_label')) ?>:</strong> <?= e((string) $data['cancel_reason']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="meeting-card">
                    <?= partial('partials/meeting-details', ['rows' => $rows, 'class' => 'meeting-details--page']) ?>
                    <p class="meeting-note"><?= e(R::tf('page.time_note')) ?></p>
                </div>
                <p class="meeting-page__home"><a class="btn btn--primary btn--caps" href="<?= e(url('')) ?>"><?= e(R::tf('page.home_label')) ?></a></p>
            </div>
        <?php else: ?>
            <div class="meeting-page__grid">
                <?php /* the actions come FIRST in the reading order (phones: join before the small print); from lg they sit in the right-hand column */ ?>
                <div class="meeting-page__side">
                    <div class="meeting-card meeting-card--join">
                        <div class="meeting-when">
                            <span class="meeting-when__icon" aria-hidden="true"><?= icon('calendar', 'icon--lg') ?></span>
                            <div class="meeting-when__text">
                                <p class="meeting-when__label"><?= e(R::tf('page.when_label')) ?></p>
                                <p class="meeting-when__date"><time datetime="<?= e((string) $data['start_iso']) ?>" data-meeting-start="<?= e((string) $data['start_iso']) ?>" data-meeting-end="<?= e((string) $data['end_iso']) ?>"><?= e((string) $vars['date']) ?></time></p>
                                <p class="meeting-when__time"><?= e((string) $vars['time']) ?></p>
                                <p class="meeting-when__local" data-meeting-local hidden><span class="meeting-when__local-label"><?= e(R::tf('page.local_label')) ?></span> <span data-meeting-local-text></span></p>
                            </div>
                        </div>
                        <?php if ($data['join_url'] !== null): ?>
                            <a class="btn btn--cta btn--lg btn--block meeting-join__button" href="<?= e((string) $data['join_url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('video') ?> <?= e(R::tf('page.join_button')) ?></a>
                            <?php if (R::tf('page.join_hint') !== ''): ?><p class="meeting-note meeting-join__hint"><?= e(R::tf('page.join_hint')) ?></p><?php endif; ?>
                        <?php endif; ?>
                        <?php if ($data['ics_url'] !== null): ?>
                            <a class="btn btn--outline btn--block meeting-join__calendar" href="<?= e((string) $data['ics_url']) ?>"><?= icon('calendar', 'icon--sm') ?> <?= e(R::tf('page.calendar_button')) ?></a>
                        <?php endif; ?>
                    </div>

                    <?php if ($rsvp['allowed'] || $hasAnswer): ?>
                        <section class="meeting-card meeting-answer" id="answer" aria-labelledby="answer-title" data-meeting-answer<?= $suggest !== '' ? ' data-suggest="' . e($suggest) . '"' : '' ?>>
                            <h2 class="meeting-answer__title" id="answer-title"><?= e(R::tf('rsvp.title')) ?></h2>
                            <div class="meeting-answer__status" role="status" aria-live="polite" tabindex="-1" data-meeting-status>
                                <?php if ($savedText !== ''): ?>
                                    <p class="meeting-alert meeting-alert--ok"><?= icon('check-circle', 'icon--sm') ?> <span><?= e($savedText) ?></span></p>
                                <?php elseif ($error): ?>
                                    <p class="meeting-alert meeting-alert--error" role="alert"><?= icon('alert-circle', 'icon--sm') ?> <span><?= e(R::tf('rsvp.error_text')) ?></span></p>
                                <?php endif; ?>
                            </div>
                            <p class="meeting-answer__current">
                                <span class="meeting-answer__current-label"><?= e(R::tf('rsvp.current_label')) ?>:</span>
                                <strong><?= e($hasAnswer ? (string) $answers[$current] : R::tf('rsvp.none_yet')) ?></strong>
                            </p>
                            <?php if ($rsvp['allowed']): ?>
                                <form class="meeting-answer__form" method="post" action="<?= e(url('meeting/' . $token)) ?>#answer" novalidate>
                                    <?= csrf_field() ?>
                                    <?php foreach ($answers as $value => $label):
                                        $isCurrent = $value === $current;
                                        $isSuggested = $suggest !== '' ? $value === $suggest : false;
                                        $cls = $isSuggested ? 'btn--cta' : ($isCurrent ? 'btn--primary' : 'btn--outline'); ?>
                                        <button class="btn <?= $cls ?> btn--block meeting-answer__button<?= $isCurrent ? ' is-current' : '' ?><?= $isSuggested ? ' is-suggested' : '' ?>" type="submit" name="rsvp" value="<?= e($value) ?>" data-answer="<?= e($value) ?>">
                                            <?= icon($icons[$value] ?? 'check', 'icon--sm') ?> <span><?= e((string) $label) ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </form>
                                <?php if (R::tf('rsvp.change_hint') !== ''): ?><p class="meeting-note"><?= e(R::tf('rsvp.change_hint')) ?></p><?php endif; ?>
                            <?php else: ?>
                                <p class="meeting-note"><?= e(R::tf('rsvp.closed_text')) ?></p>
                            <?php endif; ?>
                        </section>
                    <?php else: ?>
                        <p class="meeting-note meeting-note--card"><?= e(R::tf('rsvp.closed_text')) ?></p>
                    <?php endif; ?>
                </div>

                <div class="meeting-page__main">
                    <?php if ($rows): ?>
                        <div class="meeting-card">
                            <?= partial('partials/meeting-details', ['rows' => $rows, 'class' => 'meeting-details--page']) ?>
                            <p class="meeting-note"><?= e(R::tf('page.time_note')) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ((string) $data['agenda_html'] !== ''): ?>
                        <section class="meeting-card meeting-agenda" aria-labelledby="agenda-title">
                            <h2 class="meeting-agenda__title" id="agenda-title"><?= e(R::tf('page.agenda_label')) ?></h2>
                            <div class="prose"><?= $data['agenda_html'] ?></div>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
