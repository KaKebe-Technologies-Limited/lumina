<?php
/**
 * PARTNERS (/partners) - OWNED BY B7 "org-public".
 * Vars: $groups (partner|funder|donor|collaborator => list of partners rows, website already filtered to http(s)), $total (int).
 * Every visible string is read through content('partners', ...) so the owner can edit it in the admin (Pages > Partners).
 */
$heroImg = content_img('partners', 'hero.image');
$visitLabel = (string) content('partners', 'labels.visit');
$empty = content_btn('partners', 'empty.cta');
$join = content_btn('partners', 'join.cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('partners', 'hero.eyebrow'),
    'title'   => content('partners', 'hero.title'),
    'lead'    => content('partners', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
]) ?>

<?php if ($total === 0): ?>
    <section class="section" id="partners-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('partners', 'empty.title'),
                'text'    => content('partners', 'empty.text'),
                'icon'    => 'globe',
                'actions' => $empty['has'] ? [['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php $shown = 0; foreach (['partner', 'funder', 'donor', 'collaborator'] as $type):
        if (empty($groups[$type])) { continue; }
        $shown++;
        $headingId = 'partners-' . $type . '-title';
        section_begin('partners', $type, ['class' => $shown % 2 === 0 ? 'section--tint' : '', 'labelledby' => $headingId]);
    ?>
        <div class="section__header">
            <h2 id="<?= e($headingId) ?>"><?= e(content('partners', $type . '.title')) ?></h2>
            <?php if (trim((string) content('partners', $type . '.intro')) !== ''): ?>
                <p class="lead"><?= e(content('partners', $type . '.intro')) ?></p>
            <?php endif; ?>
        </div>
        <div class="partner-cards">
            <?php foreach ($groups[$type] as $partner): ?>
                <?= partial('web/partners/_card', ['partner' => $partner, 'visitLabel' => $visitLabel]) ?>
            <?php endforeach; ?>
        </div>
    <?php section_end(); endforeach; ?>

    <?php section_begin('partners', 'join', ['class' => 'section--navy', 'container' => 'container--narrow']); ?>
        <div class="partners-join text-center">
            <h2><?= e(content('partners', 'join.title')) ?></h2>
            <?php if (trim((string) content('partners', 'join.text')) !== ''): ?><p class="lead mt-3"><?= e(content('partners', 'join.text')) ?></p><?php endif; ?>
            <?php if ($join['has']): ?>
                <p class="mt-5"><a class="btn btn--accent btn--lg" href="<?= e($join['url']) ?>"<?= $join['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($join['label']) ?></a></p>
            <?php endif; ?>
        </div>
    <?php section_end(); ?>
<?php endif; ?>
