<?php
/**
 * IMPACT STORIES (/stories) - B-INNER-B restyle of the B7 view.
 * Vars: $stories (rows of the current page - ONLY active + consent_confirmed + published, enforced by StoriesController), $pagination.
 * Every visible string is read through content('stories', ...) so the owner can edit it in the admin (Pages > Impact Stories).
 */
$heroImg = content_img('stories', 'hero.image');
$empty = content_btn('stories', 'empty.cta');
$empty2 = content_btn('stories', 'empty.cta2');
$join = content_btn('stories', 'join.cta');
$listTitle = trim((string) content('stories', 'list.title'));
$emptyActions = [];
if ($empty['has']) {
    $emptyActions[] = ['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--cta btn--caps'];
}
if ($empty2['has']) {
    $emptyActions[] = ['label' => $empty2['label'], 'url' => $empty2['url'], 'class' => 'btn--outline btn--caps'];
}
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('stories', 'hero.eyebrow'),
    'title'   => content('stories', 'hero.title'),
    'lead'    => content('stories', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 35%'] : null,
    'sheet'   => $stories === [] ? 'tint' : 'white',
]) ?>

<?php if ($stories === []): ?>
    <section class="section section--tint" id="stories-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('stories', 'empty.title'),
                'text'    => content('stories', 'empty.text'),
                'icon'    => 'quote',
                'actions' => $emptyActions,
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php section_begin('stories', 'list', ['class' => 'section--list', 'labelledby' => $listTitle !== '' ? 'stories-title' : null]); ?>
        <?php if ($listTitle !== ''): ?>
            <?= partial('partials/section-head', [
                'title' => $listTitle,
                'lead'  => content('stories', 'list.intro'),
                'id'    => 'stories-title',
                'align' => 'center',
            ]) ?>
        <?php else: ?>
            <h2 class="visually-hidden"><?= e(content('stories', 'list.sr_title')) ?></h2>
        <?php endif; ?>
        <div class="grid grid--3 grid--gap-lg story-grid post-grid post-grid--n<?= min(count($stories), 3) ?>">
            <?php foreach ($stories as $story): ?>
                <?= partial('web/stories/_card', ['story' => $story, 'cta' => content('stories', 'list.cta')]) ?>
            <?php endforeach; ?>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
        <?php if (section_on('stories', 'consent') && trim((string) content('stories', 'consent.text')) !== ''): ?>
            <p class="stories-consent"><?= icon('shield', 'icon--sm') ?> <span><?= e(content('stories', 'consent.text')) ?></span></p>
        <?php endif; ?>
    <?php section_end(); ?>

    <?php section_begin('stories', 'join', ['bleed' => true, 'class' => 'section--band-wrap']); ?>
        <?= partial('web/blog/_band', [
            'title'   => content('stories', 'join.title'),
            'text'    => content('stories', 'join.text'),
            'actions' => [['label' => $join['label'], 'url' => $join['url'], 'external' => $join['external']]],
        ]) ?>
    <?php section_end(); ?>
<?php endif; ?>
