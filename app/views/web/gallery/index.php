<?php
/**
 * Gallery. Vars: $pagination, $images (rows), $albums (album,total), $album (active). Copy: content('gallery', ...).
 * Layout: photo page-hero -> intro -> album pills -> masonry of rounded pictures (each keeps its own proportions, so faces are never cropped)
 * that open in the lightbox of assets/js/main.js -> donate band.
 */
$heroImg = content_img('gallery', 'hero.image');
$emptyBtn = content_btn('gallery', 'grid.empty_cta');
?>
<?= partial('partials/page-hero', [
    'title'   => content('gallery', 'hero.title'),
    'eyebrow' => content('gallery', 'hero.eyebrow'),
    'lead'    => content('gallery', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 45%'] : null,
    'sheet'   => $images ? 'white' : 'tint',
]) ?>

<?php section_begin('gallery', 'grid', ['id' => 'pictures', 'class' => $images ? 'section--list' : 'section--tint', 'labelledby' => 'gallery-intro-title']); ?>
    <?php if (section_on('gallery', 'intro') && trim((string) content('gallery', 'intro.text')) !== ''): ?>
        <div class="section-head section-head--center">
            <div class="section-head__text">
                <h2 class="visually-hidden" id="gallery-intro-title"><?= e(content('gallery', 'hero.title')) ?></h2>
                <p class="section-head__lead"><?= e(content('gallery', 'intro.text')) ?></p>
            </div>
        </div>
    <?php else: ?>
        <h2 class="visually-hidden" id="gallery-intro-title"><?= e(content('gallery', 'hero.title')) ?></h2>
    <?php endif; ?>

    <?php if (count($albums) > 0): ?>
        <nav class="pills pills--center" aria-label="<?= e(content('gallery', 'grid.filter_label')) ?>">
            <a class="pills__item<?= $album === '' ? ' is-active' : '' ?>" href="<?= e(url('gallery')) ?>"<?= $album === '' ? ' aria-current="page"' : '' ?>><?= e(content('gallery', 'grid.all_label')) ?></a>
            <?php foreach ($albums as $a): $on = $album === (string) $a['album']; ?>
                <a class="pills__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('gallery', ['album' => $a['album']])) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($a['album']) ?> <span class="pills__count"><?= (int) $a['total'] ?></span></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($images): ?>
        <ul class="gallery-masonry">
            <?php foreach ($images as $img): ?>
                <?= partial('web/gallery/_tile', ['img' => $img, 'group' => 'gallery', 'variant' => 'masonry']) ?>
            <?php endforeach; ?>
        </ul>
        <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => 'gallery']) ?>
    <?php else: ?>
        <?= partial('partials/empty-state', [
            'title'   => content('gallery', 'grid.empty_title'),
            'text'    => content('gallery', 'grid.empty_text'),
            'icon'    => 'camera',
            'actions' => $emptyBtn['has'] ? [['label' => $emptyBtn['label'], 'url' => $emptyBtn['url'], 'class' => 'btn--cta btn--caps']] : [],
        ]) ?>
    <?php endif; ?>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
