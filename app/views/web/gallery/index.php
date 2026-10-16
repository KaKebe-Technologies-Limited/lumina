<?php
/** Gallery. Vars: $pagination, $images (rows), $albums (album,total), $album (active). Copy: content('gallery', ...). */
?>
<?= partial('partials/page-hero', [
    'title'   => content('gallery', 'hero.title'),
    'eyebrow' => content('gallery', 'hero.eyebrow'),
    'lead'    => content('gallery', 'hero.lead'),
]) ?>

<?php section_begin('gallery', 'intro', ['class' => 'section--sm', 'container' => 'container--narrow']); ?>
    <p class="lead text-center"><?= e(content('gallery', 'intro.text')) ?></p>
<?php section_end(); ?>

<?php section_begin('gallery', 'grid', ['id' => 'pictures', 'class' => 'section--flush-top']); ?>
    <?php if (count($albums) > 0): ?>
        <nav class="pills mb-5" aria-label="<?= e(content('gallery', 'grid.filter_label')) ?>">
            <a class="pills__item<?= $album === '' ? ' is-active' : '' ?>" href="<?= e(url('gallery')) ?>"<?= $album === '' ? ' aria-current="page"' : '' ?>><?= e(content('gallery', 'grid.all_label')) ?></a>
            <?php foreach ($albums as $a): $on = $album === (string) $a['album']; ?>
                <a class="pills__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('gallery', ['album' => $a['album']])) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($a['album']) ?></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($images): ?>
        <ul class="gallery-grid">
            <?php foreach ($images as $img): $src = img_url($img['image']); if ($src === '') { continue; } $alt = trim((string) ($img['alt_text'] ?? '')) !== '' ? (string) $img['alt_text'] : (string) ($img['caption'] ?? ''); ?>
                <li>
                    <a class="gallery-grid__item" href="<?= e($src) ?>" data-lightbox="gallery" data-caption="<?= e($img['caption'] ?? '') ?>"<?= trim($alt) === '' ? ' aria-label="' . e(content('gallery', 'grid.open_label')) . '"' : '' ?>>
                        <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" loading="lazy">
                        <?php if (trim((string) ($img['caption'] ?? '')) !== ''): ?><span class="gallery-grid__caption"><?= e($img['caption']) ?></span><?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => 'gallery']) ?>
    <?php else: ?>
        <?= partial('partials/empty-state', [
            'title' => content('gallery', 'grid.empty_title'),
            'text'  => content('gallery', 'grid.empty_text'),
            'icon'  => 'camera',
        ]) ?>
    <?php endif; ?>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
