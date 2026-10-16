<?php
/**
 * EVENTS (/events) - OWNED BY B7 "org-public".
 * Vars: $upcoming (event rows, soonest first), $past (event rows of the current page, newest first), $pagination (for $past), $any (bool).
 * Every visible string is read through content('events', ...) so the owner can edit it in the admin (Pages > Events).
 */
$heroImg = content_img('events', 'hero.image');
$empty = content_btn('events', 'empty.cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('events', 'hero.eyebrow'),
    'title'   => content('events', 'hero.title'),
    'lead'    => content('events', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
]) ?>

<?php if (!$any): ?>
    <section class="section" id="events-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('events', 'empty.title'),
                'text'    => content('events', 'empty.text'),
                'icon'    => 'calendar',
                'actions' => $empty['has'] ? [['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php section_begin('events', 'upcoming', ['labelledby' => 'events-upcoming-title']); ?>
        <div class="section__header">
            <h2 id="events-upcoming-title"><?= e(content('events', 'upcoming.title')) ?></h2>
            <?php if (trim((string) content('events', 'upcoming.intro')) !== ''): ?><p class="lead"><?= e(content('events', 'upcoming.intro')) ?></p><?php endif; ?>
        </div>
        <?php if ($upcoming): ?>
            <div class="event-list">
                <?php foreach ($upcoming as $event): ?>
                    <?= partial('partials/event-card', ['event' => $event, 'past' => false, 'cta' => content('events', 'labels.card_cta')]) ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?= partial('partials/empty-state', [
                'title' => content('events', 'upcoming.none_title'),
                'text'  => content('events', 'upcoming.none_text'),
                'icon'  => 'calendar',
            ]) ?>
        <?php endif; ?>
    <?php section_end(); ?>

    <?php if ($past): ?>
        <?php section_begin('events', 'past', ['class' => 'section--tint', 'labelledby' => 'events-past-title']); ?>
            <div class="section__header">
                <h2 id="events-past-title"><?= e(content('events', 'past.title')) ?></h2>
                <?php if (trim((string) content('events', 'past.intro')) !== ''): ?><p class="lead"><?= e(content('events', 'past.intro')) ?></p><?php endif; ?>
            </div>
            <div class="event-list">
                <?php foreach ($past as $event): ?>
                    <?= partial('partials/event-card', ['event' => $event, 'past' => true, 'cta' => content('events', 'labels.card_cta_past')]) ?>
                <?php endforeach; ?>
            </div>
            <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
        <?php section_end(); ?>
    <?php endif; ?>
<?php endif; ?>
