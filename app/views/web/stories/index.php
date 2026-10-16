<?php
/**
 * IMPACT STORIES (/stories) - OWNED BY B7 "org-public".
 * Vars: $stories (rows of the current page - ONLY active + consent_confirmed + published, enforced by StoriesController), $pagination.
 * Every visible string is read through content('stories', ...) so the owner can edit it in the admin (Pages > Impact Stories).
 */
$heroImg = content_img('stories', 'hero.image');
$empty = content_btn('stories', 'empty.cta');
$join = content_btn('stories', 'join.cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('stories', 'hero.eyebrow'),
    'title'   => content('stories', 'hero.title'),
    'lead'    => content('stories', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
]) ?>

<?php if ($stories === []): ?>
    <section class="section" id="stories-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('stories', 'empty.title'),
                'text'    => content('stories', 'empty.text'),
                'icon'    => 'quote',
                'actions' => $empty['has'] ? [['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php section_begin('stories', 'list', ['labelledby' => trim((string) content('stories', 'list.title')) !== '' ? 'stories-title' : null]); ?>
        <?php if (trim((string) content('stories', 'list.title')) !== ''): ?>
            <div class="section__header">
                <h2 id="stories-title"><?= e(content('stories', 'list.title')) ?></h2>
                <?php if (trim((string) content('stories', 'list.intro')) !== ''): ?><p class="lead"><?= e(content('stories', 'list.intro')) ?></p><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (trim((string) content('stories', 'list.title')) === ''): ?><h2 class="visually-hidden"><?= e(content('stories', 'list.sr_title')) ?></h2><?php endif; ?>
        <div class="grid grid--3 story-grid">
            <?php foreach ($stories as $story): ?>
                <?= partial('partials/story-card', ['story' => $story, 'cta' => content('stories', 'list.cta')]) ?>
            <?php endforeach; ?>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
        <?php if (section_on('stories', 'consent') && trim((string) content('stories', 'consent.text')) !== ''): ?>
            <p class="stories-consent text-center text-muted"><?= icon('shield', 'icon--sm') ?> <?= e(content('stories', 'consent.text')) ?></p>
        <?php endif; ?>
    <?php section_end(); ?>

    <?php section_begin('stories', 'join', ['class' => 'section--navy', 'container' => 'container--narrow']); ?>
        <div class="stories-join text-center">
            <h2><?= e(content('stories', 'join.title')) ?></h2>
            <?php if (trim((string) content('stories', 'join.text')) !== ''): ?><p class="lead mt-3"><?= e(content('stories', 'join.text')) ?></p><?php endif; ?>
            <?php if ($join['has']): ?>
                <p class="mt-5"><a class="btn btn--accent btn--lg" href="<?= e($join['url']) ?>"<?= $join['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($join['label']) ?></a></p>
            <?php endif; ?>
        </div>
    <?php section_end(); ?>
<?php endif; ?>
