<?php
/**
 * Activities listing. Vars: $pagination, $activities (rows). Copy: content('activities', ...) - admin: Pages > Activities.
 */
$aboutOn = section_on('activities', 'about');
$gallery = array_values(array_filter(content_list('activities', 'about.gallery'), static fn(array $g): bool => trim((string) ($g['image'] ?? '')) !== ''));
?>
<?= partial('partials/page-hero', [
    'title'   => content('activities', 'hero.title'),
    'eyebrow' => content('activities', 'hero.eyebrow'),
    'lead'    => content('activities', 'hero.lead'),
]) ?>

<?php section_begin('activities', 'about', ['id' => 'about', 'labelledby' => 'about-title']); $img = content_img('activities', 'about.image'); ?>
    <div class="split split--start<?= $img['has'] ? '' : ' split--single' ?>">
        <div>
            <h2 id="about-title"><?= e(content('activities', 'about.title')) ?></h2>
            <div class="prose mt-4"><?= content_html('activities', 'about.body') ?></div>
        </div>
        <?php if ($img['has']): ?>
            <div class="split__media"><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div>
        <?php endif; ?>
    </div>

    <?php if ($gallery): ?>
        <div class="activity-gallery">
            <?php if (trim((string) content('activities', 'about.gallery_title')) !== ''): ?>
                <h3 class="activity-gallery__title"><?= e(content('activities', 'about.gallery_title')) ?></h3>
            <?php endif; ?>
            <ul class="gallery-grid">
                <?php foreach ($gallery as $g): $src = img_url($g['image']); ?>
                    <li>
                        <a class="gallery-grid__item" href="<?= e($src) ?>" data-lightbox="activities-story" data-caption="<?= e($g['caption'] ?? '') ?>">
                            <img src="<?= e($src) ?>" alt="<?= e($g['image_alt'] !== '' ? $g['image_alt'] : ($g['caption'] ?? '')) ?>" loading="lazy">
                            <?php if (trim((string) ($g['caption'] ?? '')) !== ''): ?><span class="gallery-grid__caption"><?= e($g['caption']) ?></span><?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* the list section is always rendered: with no activities yet it shows the editable empty-state wording */ ?>
    <?php section_begin('activities', 'list', ['class' => $aboutOn ? 'section--tint' : '', 'id' => 'activities-list', 'labelledby' => 'activities-list-title']); ?>
        <div class="section__header">
            <h2 id="activities-list-title"><?= e(content('activities', 'list.title')) ?></h2>
            <?php if (trim((string) content('activities', 'list.intro')) !== ''): ?><p class="lead"><?= e(content('activities', 'list.intro')) ?></p><?php endif; ?>
        </div>
        <?php if ($activities): ?>
            <div class="grid grid--3 grid--gap-lg">
                <?php foreach ($activities as $a): ?><?= partial('web/activities/_card', ['activity' => $a]) ?><?php endforeach; ?>
            </div>
            <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => 'activities']) ?>
        <?php else: ?>
            <?= partial('partials/empty-state', [
                'title' => content('activities', 'list.empty_title'),
                'text'  => content('activities', 'list.empty_text'),
                'icon'  => 'activity',
            ]) ?>
        <?php endif; ?>
    <?php section_end(); ?>

<?php section_begin('activities', 'subscribe', ['class' => '', 'id' => 'subscribe']); ?>
    <?= partial('web/blog/_subscribe', [
        'id'          => 'activities-subscribe',
        'title'       => content('activities', 'subscribe.title'),
        'text'        => content('activities', 'subscribe.text'),
        'placeholder' => content('activities', 'subscribe.placeholder'),
        'button'      => content('activities', 'subscribe.button'),
        'source'      => 'activities',
    ]) ?>
<?php section_end(); ?>

<?php section_begin('activities', 'contact', ['class' => 'section--tint', 'container' => 'container--narrow', 'id' => 'contact-teaser']); $btn = content_btn('activities', 'contact.button'); ?>
    <div class="teaser text-center">
        <h2><?= e(content('activities', 'contact.title')) ?></h2>
        <p class="lead mt-3"><?= e(content('activities', 'contact.text')) ?></p>
        <?php if ($btn['has']): ?><p class="mt-5"><a class="btn btn--primary btn--lg" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a></p><?php endif; ?>
    </div>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
