<?php
/**
 * Blog listing. Vars: $pagination (items = posts), $posts, $categories (category,total), $category (active filter), $gallery (rows).
 * Every heading / label comes from content('blog', ...) - editable in the admin (Pages > Blog).
 */
?>
<?= partial('partials/page-hero', [
    'title'   => content('blog', 'hero.title'),
    'eyebrow' => content('blog', 'hero.eyebrow'),
    'lead'    => content('blog', 'hero.lead'),
]) ?>

<?php section_begin('blog', 'list', ['id' => 'posts']); ?>
    <?php if (count($categories) > 1): ?>
        <nav class="pills mb-5" aria-label="<?= e(content('blog', 'list.filter_label')) ?>">
            <a class="pills__item<?= $category === '' ? ' is-active' : '' ?>" href="<?= e(url('blog')) ?>"<?= $category === '' ? ' aria-current="page"' : '' ?>><?= e(content('blog', 'list.all_label')) ?></a>
            <?php foreach ($categories as $c): $on = $category === (string) $c['category']; ?>
                <a class="pills__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('blog', ['category' => $c['category']])) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($c['category']) ?></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($posts): ?>
        <h2 class="visually-hidden"><?= e(content('blog', 'list.sr_title')) ?></h2>
        <div class="grid grid--3 grid--gap-lg">
            <?php foreach ($posts as $post): ?>
                <?= partial('partials/post-card', ['post' => $post]) ?>
            <?php endforeach; ?>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => 'blog']) ?>
    <?php elseif ($category !== ''): ?>
        <?= partial('partials/empty-state', [
            'title'   => $category,
            'text'    => content('blog', 'list.empty_category_text'),
            'icon'    => 'newspaper',
            'actions' => [['label' => content('blog', 'list.empty_button_label'), 'url' => url('blog')]],
        ]) ?>
    <?php else: ?>
        <?= partial('partials/empty-state', [
            'title' => content('blog', 'list.empty_title'),
            'text'  => content('blog', 'list.empty_text'),
            'icon'  => 'newspaper',
        ]) ?>
    <?php endif; ?>
<?php section_end(); ?>

<?php section_begin('blog', 'subscribe', ['class' => 'section--tint', 'id' => 'subscribe']); ?>
    <?= partial('web/blog/_subscribe', [
        'id'          => 'blog-subscribe',
        'title'       => content('blog', 'subscribe.title'),
        'text'        => content('blog', 'subscribe.text'),
        'placeholder' => content('blog', 'subscribe.placeholder'),
        'button'      => content('blog', 'subscribe.button'),
        'source'      => 'blog',
    ]) ?>
<?php section_end(); ?>

<?php if ($gallery): ?>
    <?php section_begin('blog', 'gallery', ['id' => 'gallery-preview']); $btn = content_btn('blog', 'gallery.button'); ?>
        <div class="section__header section__header--center">
            <h2><?= e(content('blog', 'gallery.title')) ?></h2>
            <p class="lead"><?= e(content('blog', 'gallery.text')) ?></p>
        </div>
        <ul class="gallery-grid gallery-grid--strip">
            <?php foreach ($gallery as $img): $src = img_url($img['image']); if ($src === '') { continue; } $alt = trim((string) ($img['alt_text'] ?? '')) !== '' ? (string) $img['alt_text'] : (string) ($img['caption'] ?? ''); ?>
                <li>
                    <a class="gallery-grid__item" href="<?= e($src) ?>" data-lightbox="blog-gallery" data-caption="<?= e($img['caption'] ?? '') ?>"<?= trim($alt) === '' ? ' aria-label="' . e(content('gallery', 'grid.open_label')) . '"' : '' ?>>
                        <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" loading="lazy">
                        <?php if (!empty($img['caption'])): ?><span class="gallery-grid__caption"><?= e($img['caption']) ?></span><?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($btn['has']): ?>
            <div class="section__footer"><a class="btn btn--outline" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a></div>
        <?php endif; ?>
    <?php section_end(); ?>
<?php endif; ?>

<?php section_begin('blog', 'contact', ['class' => 'section--tint', 'container' => 'container--narrow', 'id' => 'contact-teaser']); $btn = content_btn('blog', 'contact.button'); ?>
    <div class="teaser text-center">
        <h2><?= e(content('blog', 'contact.title')) ?></h2>
        <p class="lead mt-3"><?= e(content('blog', 'contact.text')) ?></p>
        <?php if ($btn['has']): ?><p class="mt-5"><a class="btn btn--primary btn--lg" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a></p><?php endif; ?>
    </div>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
