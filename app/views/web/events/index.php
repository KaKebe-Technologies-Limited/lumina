<?php
/**
 * EVENTS (/events) - B-INNER-B restyle of the B7 view.
 * Vars: $upcoming (event rows, soonest first), $past (event rows of the current page, newest first), $pagination (for $past), $any (bool).
 * Every visible string is read through content('events', ...) so the owner can edit it in the admin (Pages > Events).
 */
$heroImg = content_img('events', 'hero.image');
$empty = content_btn('events', 'empty.cta');
$empty2 = content_btn('events', 'empty.cta2');
$emptyActions = [];
if ($empty['has']) {
    $emptyActions[] = ['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--cta btn--caps'];
}
if ($empty2['has']) {
    $emptyActions[] = ['label' => $empty2['label'], 'url' => $empty2['url'], 'class' => 'btn--outline btn--caps'];
}
$pastBadge = (string) content('events', 'labels.past_badge');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('events', 'hero.eyebrow'),
    'title'   => content('events', 'hero.title'),
    'lead'    => content('events', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 45%'] : null,
    'sheet'   => $any ? 'white' : 'tint',
]) ?>

<?php if (!$any): ?>
    <section class="section section--tint" id="events-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('events', 'empty.title'),
                'text'    => content('events', 'empty.text'),
                'icon'    => 'calendar',
                'actions' => $emptyActions,
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php section_begin('events', 'upcoming', ['labelledby' => 'events-upcoming-title']); ?>
        <?= partial('partials/section-head', [
            'eyebrow' => content('events', 'upcoming.eyebrow'),
            'title'   => content('events', 'upcoming.title'),
            'lead'    => content('events', 'upcoming.intro'),
            'id'      => 'events-upcoming-title',
            'align'   => 'center',
        ]) ?>
        <?php if ($upcoming): ?>
            <div class="grid grid--3 grid--gap-lg event-list post-grid post-grid--n<?= min(count($upcoming), 3) ?>">
                <?php foreach ($upcoming as $event): ?>
                    <?= partial('web/events/_card', ['event' => $event, 'past' => false, 'cta' => content('events', 'labels.card_cta')]) ?>
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
            <?= partial('partials/section-head', [
                'eyebrow' => content('events', 'past.eyebrow'),
                'title'   => content('events', 'past.title'),
                'lead'    => content('events', 'past.intro'),
                'id'      => 'events-past-title',
                'align'   => 'center',
            ]) ?>
            <div class="grid grid--3 grid--gap-lg event-list post-grid post-grid--n<?= min(count($past), 3) ?>">
                <?php foreach ($past as $event): ?>
                    <?= partial('web/events/_card', ['event' => $event, 'past' => true, 'cta' => content('events', 'labels.card_cta_past'), 'pastLabel' => $pastBadge]) ?>
                <?php endforeach; ?>
            </div>
            <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
        <?php section_end(); ?>
    <?php endif; ?>
<?php endif; ?>

<?= partial('partials/cta-donate') ?>
