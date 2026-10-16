<?php
/**
 * EVENT DETAIL (/events/{slug}) - OWNED BY B7 "org-public".
 * Vars: $event (events row), $start / $end (DateTimeImmutable|null), $over (bool: the event has ended), $capacity (0 = unlimited),
 * $taken, $left (int|null free places), $full, $canRegister, $externalUrl (validated http(s) registration link or ''),
 * $registered (array|null: this visitor just registered - name/email/mailed), $others (other upcoming events).
 * Every label and message comes from content('events', ...) (admin: Pages > Events); the event data itself comes from the Events admin.
 */
$img = img_url($event['image'] ?? '');
$summary = trim((string) ($event['summary'] ?? ''));
$body = trim((string) ($event['body'] ?? ''));
$location = trim((string) ($event['location'] ?? ''));
$slug = (string) $event['slug'];

$dateText = $start ? $start->format('l, j F Y') : '';
$timeText = '';
if ($start) {
    $timeText = $start->format('H:i');
    if ($end) {
        $timeText .= $end->format('Y-m-d') === $start->format('Y-m-d')
            ? ' - ' . $end->format('H:i')
            : ' - ' . $end->format('l, j F Y, H:i');
    }
}

$enabled = !empty($event['registration_enabled']);
$showRegister = $enabled || $externalUrl !== '';
$placesLeft = ($left !== null && !$over && !$full)
    ? str_replace('{n}', (string) $left, (string) content('events', 'detail.places_left')) : '';
$calendarUrl = url('events/' . $slug . '/calendar.ics');

$ld = array_filter([
    '@context'    => 'https://schema.org',
    '@type'       => 'Event',
    'name'        => (string) $event['title'],
    'startDate'   => $start ? $start->format('c') : null,
    'endDate'     => $end ? $end->format('c') : null,
    'description' => $summary !== '' ? $summary : excerpt($body, 300),
    'image'       => $img !== '' ? abs_url_for_image((string) $event['image']) : null,
    'url'         => abs_url('events/' . $slug),
    'eventStatus' => 'https://schema.org/EventScheduled',
    'eventAttendanceMode' => $location !== '' ? 'https://schema.org/OfflineEventAttendanceMode' : null,
    'location'    => $location !== '' ? ['@type' => 'Place', 'name' => $location, 'address' => $location] : null,
    'organizer'   => ['@type' => 'Organization', 'name' => (string) setting('site_name', ''), 'url' => abs_url('')],
], static fn($v) => $v !== null && $v !== '');
?>
<?php $this->start('head'); ?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php $this->end(); ?>

<?= partial('partials/page-hero', [
    'eyebrow' => $dateText,
    'title'   => $event['title'],
    'lead'    => $summary,
]) ?>

<section class="section" id="event">
    <div class="container">
        <p class="event-back"><a class="link-arrow" href="<?= e(url('events')) ?>"><?= icon('arrow-left', 'icon--arrow') ?> <?= e(content('events', 'detail.back')) ?></a></p>

        <div class="event-detail">
            <div class="event-detail__main">
                <?php if ($img !== ''): ?>
                    <figure class="event-detail__media"><img src="<?= e($img) ?>" alt="<?= e($event['image_alt'] ?? '') ?>" fetchpriority="high"></figure>
                <?php endif; ?>

                <?php if ($body !== ''): ?>
                    <h2 class="event-detail__heading"><?= e(content('events', 'detail.about')) ?></h2>
                    <div class="prose"><?= rich($body) ?></div>
                <?php endif; ?>

                <?php if ($showRegister): ?>
                    <section class="event-register" id="register" aria-labelledby="register-title">
                        <h2 id="register-title"><?= e(content('events', 'register.title')) ?></h2>

                        <?php if ($registered): ?>
                            <?php
                            $vars = ['{name}' => (string) $registered['name'], '{event}' => (string) $event['title'], '{email}' => (string) $registered['email']];
                            ?>
                            <div class="alert alert--success event-register__done" role="status" tabindex="-1" data-focus-on-load>
                                <?= icon('check-circle') ?>
                                <div class="alert__body">
                                    <strong class="alert__title"><?= e(content('events', 'register.success_title')) ?></strong>
                                    <p><?= e(strtr((string) content('events', 'register.success_text'), $vars)) ?></p>
                                    <?php if (!empty($registered['mailed']) && trim((string) content('events', 'register.success_email')) !== ''): ?>
                                        <p><?= e(strtr((string) content('events', 'register.success_email'), $vars)) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($over): ?>
                            <p class="alert alert--info"><?= icon('info') ?><span class="alert__body"><?= e(content('events', 'register.closed_text')) ?></span></p>
                        <?php elseif ($enabled && $full): ?>
                            <div class="alert alert--warning">
                                <?= icon('alert-triangle') ?>
                                <div class="alert__body">
                                    <strong class="alert__title"><?= e(content('events', 'register.full_title')) ?></strong>
                                    <p><?= e(content('events', 'register.full_text')) ?></p>
                                </div>
                            </div>
                        <?php elseif ($enabled): ?>
                            <?php if (trim((string) content('events', 'register.intro')) !== ''): ?><p class="event-register__intro"><?= e(content('events', 'register.intro')) ?></p><?php endif; ?>
                            <form class="form-card event-register__form" method="post" action="<?= e(url('events/' . $slug . '/register')) ?>" novalidate>
                                <?= csrf_field() ?>
                                <?= honeypot_field() ?>
                                <?php if (has_error('form')): ?>
                                    <div class="alert alert--error" role="alert" tabindex="-1" data-focus-on-load><?= icon('alert-circle') ?><span class="alert__body"><?= e(error('form')) ?></span></div>
                                <?php endif; ?>
                                <div class="form-grid">
                                    <?= partial('partials/form-field', ['name' => 'name', 'label' => content('events', 'register.name_label'), 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                                    <?= partial('partials/form-field', ['name' => 'email', 'label' => content('events', 'register.email_label'), 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                                    <?= partial('partials/form-field', ['name' => 'phone', 'label' => content('events', 'register.phone_label'), 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                                    <?= partial('partials/form-field', ['name' => 'organisation', 'label' => content('events', 'register.organisation_label'), 'autocomplete' => 'organization', 'maxlength' => 190]) ?>
                                    <?= partial('partials/form-field', ['name' => 'notes', 'label' => content('events', 'register.notes_label'), 'type' => 'textarea', 'rows' => 4, 'maxlength' => 1000, 'class' => 'field--full']) ?>
                                </div>
                                <div class="form-actions">
                                    <button class="btn btn--primary btn--lg" type="submit" data-loading-text="<?= e(content('events', 'register.sending')) ?>"><?= e(content('events', 'register.submit')) ?></button>
                                    <?php if ($placesLeft !== ''): ?><span class="form-note"><?= e($placesLeft) ?></span><?php endif; ?>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if ($externalUrl !== '' && !$over): ?>
                            <p class="event-register__external"><a class="btn btn--accent" href="<?= e($externalUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e(content('events', 'register.external_label')) ?> <?= icon('external-link', 'icon--sm') ?></a></p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="event-detail__aside">
                <div class="event-facts">
                    <?php if ($over): ?><p class="badge badge--neutral"><?= e(content('events', 'detail.ended')) ?></p><?php endif; ?>
                    <dl class="event-facts__list">
                        <?php if ($start): ?>
                            <div class="event-facts__item">
                                <dt><?= icon('calendar', 'icon--sm') ?> <?= e(content('events', 'detail.when')) ?></dt>
                                <dd>
                                    <time datetime="<?= e($start->format('c')) ?>"><?= e($dateText) ?></time>
                                    <span class="event-facts__time"><?= e($timeText) ?></span>
                                </dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($location !== ''): ?>
                            <div class="event-facts__item">
                                <dt><?= icon('map-pin', 'icon--sm') ?> <?= e(content('events', 'detail.where')) ?></dt>
                                <dd><?= e($location) ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <?php if ($placesLeft !== ''): ?><p class="event-facts__places"><?= icon('users', 'icon--sm') ?> <?= e($placesLeft) ?></p><?php endif; ?>
                    <?php if ($start && trim((string) content('events', 'detail.calendar')) !== ''): ?>
                        <a class="btn btn--outline btn--block" href="<?= e($calendarUrl) ?>" download><?= icon('calendar', 'icon--sm') ?> <?= e(content('events', 'detail.calendar')) ?></a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php if ($others): ?>
    <section class="section section--tint" id="more-events" aria-labelledby="more-events-title">
        <div class="container">
            <div class="section__header"><h2 id="more-events-title"><?= e(content('events', 'detail.more_title')) ?></h2></div>
            <div class="event-list">
                <?php foreach ($others as $other): ?>
                    <?= partial('partials/event-card', ['event' => $other, 'past' => false, 'cta' => content('events', 'labels.card_cta')]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php $this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/events.js')) ?>" defer></script>
<?php $this->end(); ?>
