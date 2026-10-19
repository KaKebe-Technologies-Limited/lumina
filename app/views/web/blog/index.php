<?php
/**
 * Blog listing. Vars: $pagination (items = posts), $posts, $categories (category,total), $category (active filter), $gallery (rows).
 * Every heading / label comes from content('blog', ...) - editable in the admin (Pages > Blog).
 * Layout: photo page-hero -> category pills -> newest post as a large feature card + grid -> gallery teaser (cream) -> newsletter panel
 * -> contact band -> donate band.
 */
$heroImg = content_img('blog', 'hero.image');
$rest = $posts;
$feature = null;
if ($rest && (int) $pagination->page === 1) {
    $feature = array_shift($rest);
}
$galleryBtn = content_btn('blog', 'gallery.button');
?>
<?= partial('partials/page-hero', [
    'title'   => content('blog', 'hero.title'),
    'eyebrow' => content('blog', 'hero.eyebrow'),
    'lead'    => content('blog', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 50%'] : null,
]) ?>

<?php section_begin('blog', 'list', ['id' => 'posts', 'class' => 'section--list']); ?>
    <?php if (count($categories) > 1): ?>
        <nav class="pills pills--center" aria-label="<?= e(content('blog', 'list.filter_label')) ?>">
            <a class="pills__item<?= $category === '' ? ' is-active' : '' ?>" href="<?= e(url('blog')) ?>"<?= $category === '' ? ' aria-current="page"' : '' ?>><?= e(content('blog', 'list.all_label')) ?></a>
            <?php foreach ($categories as $c): $on = $category === (string) $c['category']; ?>
                <a class="pills__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('blog', ['category' => $c['category']])) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($c['category']) ?> <span class="pills__count"><?= (int) $c['total'] ?></span></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($posts): ?>
        <h2 class="visually-hidden"><?= e(content('blog', 'list.sr_title')) ?></h2>
        <?php if ($feature): ?>
            <?= partial('web/blog/_card', ['post' => $feature, 'featured' => true, 'badge' => content('blog', 'list.featured_label'), 'cta' => content('global', 'labels.read_more')]) ?>
        <?php endif; ?>
        <?php if ($rest): ?>
            <div class="grid grid--3 grid--gap-lg post-grid post-grid--n<?= min(count($rest), 3) ?>">
                <?php foreach ($rest as $post): ?>
                    <?= partial('web/blog/_card', ['post' => $post]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => 'blog']) ?>
    <?php elseif ($category !== ''): ?>
        <?= partial('partials/empty-state', [
            'title'   => $category,
            'text'    => content('blog', 'list.empty_category_text'),
            'icon'    => 'newspaper',
            'actions' => [['label' => content('blog', 'list.empty_button_label'), 'url' => url('blog'), 'class' => 'btn--cta btn--caps']],
        ]) ?>
    <?php else: ?>
        <?= partial('partials/empty-state', [
            'title' => content('blog', 'list.empty_title'),
            'text'  => content('blog', 'list.empty_text'),
            'icon'  => 'newspaper',
        ]) ?>
    <?php endif; ?>
<?php section_end(); ?>

<?php if ($gallery): ?>
    <?php section_begin('blog', 'gallery', ['id' => 'gallery-preview', 'class' => 'section--tint', 'labelledby' => 'gallery-preview-title']); ?>
        <?= partial('partials/section-head', [
            'title'   => content('blog', 'gallery.title'),
            'lead'    => content('blog', 'gallery.text'),
            'eyebrow' => content('blog', 'gallery.eyebrow'),
            'id'      => 'gallery-preview-title',
            'align'   => 'center',
        ]) ?>
        <ul class="gallery-strip">
            <?php foreach ($gallery as $img): ?>
                <?= partial('web/gallery/_tile', ['img' => $img, 'group' => 'blog-gallery', 'variant' => 'strip']) ?>
            <?php endforeach; ?>
        </ul>
        <?php if ($galleryBtn['has']): ?>
            <div class="section__footer"><a class="btn btn--outline btn--caps" href="<?= e($galleryBtn['url']) ?>"><?= e($galleryBtn['label']) ?> <?= icon('arrow-right', 'icon--sm') ?></a></div>
        <?php endif; ?>
    <?php section_end(); ?>
<?php endif; ?>

<?= partial('web/newsletter/_blog-cta') ?>

<?php section_begin('blog', 'subscribe', ['class' => 'section--panel', 'id' => 'subscribe']); ?>
    <?= partial('web/blog/_subscribe', [
        'id'          => 'blog-subscribe',
        'eyebrow'     => content('blog', 'subscribe.eyebrow'),
        'title'       => content('blog', 'subscribe.title'),
        'text'        => content('blog', 'subscribe.text'),
        'placeholder' => content('blog', 'subscribe.placeholder'),
        'button'      => content('blog', 'subscribe.button'),
        'source'      => 'blog',
    ]) ?>
<?php section_end(); ?>

<?php section_begin('blog', 'contact', ['class' => 'section--tint section--teaser', 'container' => 'container--narrow', 'id' => 'contact-teaser']); $btn = content_btn('blog', 'contact.button'); ?>
    <div class="teaser text-center" data-reveal>
        <span class="teaser__icon"><?= icon('message-circle', 'icon--lg') ?></span>
        <h2><?= e(content('blog', 'contact.title')) ?></h2>
        <p class="lead mt-3"><?= e(content('blog', 'contact.text')) ?></p>
        <?php if ($btn['has']): ?><p class="mt-5"><a class="btn btn--primary btn--caps btn--lg" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a></p><?php endif; ?>
    </div>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
