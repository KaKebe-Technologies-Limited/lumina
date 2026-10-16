<?php
/**
 * Activities listing. Vars: $pagination, $activities (rows). Copy: content('activities', ...) - admin: Pages > Activities.
 * Layout: photo page-hero -> featured story (split with a framed photo when there is one) + photo strip -> activity cards -> newsletter panel
 * -> contact band -> donate band.
 */
$aboutOn = section_on('activities', 'about');
$heroImg = content_img('activities', 'hero.image');
$gallery = array_values(array_filter(content_list('activities', 'about.gallery'), static fn(array $g): bool => trim((string) ($g['image'] ?? '')) !== ''));
$contactBtn = content_btn('activities', 'contact.button');
?>
<?= partial('partials/page-hero', [
    'title'   => content('activities', 'hero.title'),
    'eyebrow' => content('activities', 'hero.eyebrow'),
    'lead'    => content('activities', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 35%'] : null,
]) ?>

<?php section_begin('activities', 'about', ['id' => 'about', 'labelledby' => 'about-title']); $img = content_img('activities', 'about.image'); ?>
    <div class="split split--start story-split<?= $img['has'] ? '' : ' split--single' ?>">
        <div data-reveal>
            <?= partial('partials/section-head', [
                'eyebrow' => content('activities', 'about.eyebrow'),
                'title'   => content('activities', 'about.title'),
                'id'      => 'about-title',
            ]) ?>
            <div class="prose story-split__body"><?= content_html('activities', 'about.body') ?></div>
        </div>
        <?php if ($img['has']): ?>
            <figure class="split__media split__media--frame split__media--portrait"><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy" decoding="async"></figure>
        <?php endif; ?>
    </div>

    <?php if ($gallery): ?>
        <div class="activity-gallery">
            <?php if (trim((string) content('activities', 'about.gallery_title')) !== ''): ?>
                <h3 class="activity-gallery__title"><?= e(content('activities', 'about.gallery_title')) ?></h3>
            <?php endif; ?>
            <ul class="gallery-strip">
                <?php foreach ($gallery as $g): ?>
                    <?= partial('web/gallery/_tile', [
                        'img'     => ['image' => $g['image'], 'alt_text' => (string) ($g['image_alt'] ?? ''), 'caption' => (string) ($g['caption'] ?? '')],
                        'group'   => 'activities-story',
                        'variant' => 'strip',
                    ]) ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* the list section is always rendered: with no activities yet it shows the editable empty-state wording */ ?>
<?php section_begin('activities', 'list', ['class' => $aboutOn ? 'section--tint' : 'section--list', 'id' => 'activities-list', 'labelledby' => 'activities-list-title']); ?>
    <?= partial('partials/section-head', [
        'eyebrow' => content('activities', 'list.eyebrow'),
        'title'   => content('activities', 'list.title'),
        'lead'    => content('activities', 'list.intro'),
        'id'      => 'activities-list-title',
        'align'   => 'center',
    ]) ?>
    <?php if ($activities): ?>
        <div class="grid grid--3 grid--gap-lg post-grid post-grid--n<?= min(count($activities), 3) ?>">
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

<?php section_begin('activities', 'subscribe', ['class' => 'section--panel', 'id' => 'subscribe']); ?>
    <?= partial('web/blog/_subscribe', [
        'id'          => 'activities-subscribe',
        'eyebrow'     => content('activities', 'subscribe.eyebrow'),
        'title'       => content('activities', 'subscribe.title'),
        'text'        => content('activities', 'subscribe.text'),
        'placeholder' => content('activities', 'subscribe.placeholder'),
        'button'      => content('activities', 'subscribe.button'),
        'source'      => 'activities',
    ]) ?>
<?php section_end(); ?>

<?php section_begin('activities', 'contact', ['class' => 'section--tint section--teaser', 'container' => 'container--narrow', 'id' => 'contact-teaser']); ?>
    <div class="teaser text-center" data-reveal>
        <span class="teaser__icon"><?= icon('message-circle', 'icon--lg') ?></span>
        <h2><?= e(content('activities', 'contact.title')) ?></h2>
        <p class="lead mt-3"><?= e(content('activities', 'contact.text')) ?></p>
        <?php if ($contactBtn['has']): ?><p class="mt-5"><a class="btn btn--primary btn--caps btn--lg" href="<?= e($contactBtn['url']) ?>"><?= e($contactBtn['label']) ?></a></p><?php endif; ?>
    </div>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
