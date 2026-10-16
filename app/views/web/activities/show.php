<?php
/** Single activity. Vars: $activity (row), $more (rows), $absUrl, $share. Labels: content('activities', 'detail.*'). */
$img = img_url($activity['image'] ?? '');
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/blog.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/page-hero', [
    'title'   => $activity['title'],
    'eyebrow' => '',
    'lead'    => (string) ($activity['summary'] ?? ''),
]) ?>

<article class="section activity">
    <div class="container container--narrow">
        <?php if (!empty($activity['activity_date']) || !empty($activity['location'])): ?>
            <dl class="activity__facts">
                <?php if (!empty($activity['activity_date'])): ?>
                    <div><dt><?= icon('calendar', 'icon--sm') ?> <?= e(content('activities', 'detail.date_label')) ?></dt><dd><time datetime="<?= e(format_date($activity['activity_date'], 'Y-m-d')) ?>"><?= e(format_date($activity['activity_date'])) ?></time></dd></div>
                <?php endif; ?>
                <?php if (!empty($activity['location'])): ?>
                    <div><dt><?= icon('map-pin', 'icon--sm') ?> <?= e(content('activities', 'detail.location_label')) ?></dt><dd><?= e($activity['location']) ?></dd></div>
                <?php endif; ?>
            </dl>
        <?php endif; ?>

        <?php if ($img !== ''): ?>
            <figure class="post__cover">
                <img src="<?= e($img) ?>" alt="<?= e($activity['image_alt'] ?? '') ?>" width="1200" height="750" fetchpriority="high">
            </figure>
        <?php endif; ?>

        <div class="prose post__body"><?= rich($activity['body'] ?? '') ?></div>

        <?= partial('web/blog/_share', [
            'share'       => $share,
            'absUrl'      => $absUrl,
            'heading'     => content('activities', 'detail.share_label'),
            'shareOn'     => content('activities', 'detail.share_on'),
            'copyLabel'   => content('activities', 'detail.copy_label'),
            'copiedLabel' => content('activities', 'detail.copied_label'),
        ]) ?>

        <p class="post__back"><a class="link-arrow" href="<?= e(url('activities')) ?>"><?= icon('arrow-left', 'icon--arrow') ?> <?= e(content('activities', 'detail.back_label')) ?></a></p>
    </div>
</article>

<?php if ($more): ?>
    <section class="section section--tint" aria-labelledby="more-title">
        <div class="container">
            <div class="section__header"><h2 id="more-title"><?= e(content('activities', 'detail.more_title')) ?></h2></div>
            <div class="grid grid--3 grid--gap-lg">
                <?php foreach ($more as $a): ?><?= partial('web/activities/_card', ['activity' => $a]) ?><?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php section_begin('activities', 'subscribe', ['id' => 'subscribe']); ?>
    <?= partial('web/blog/_subscribe', [
        'id'          => 'activity-subscribe',
        'title'       => content('activities', 'subscribe.title'),
        'text'        => content('activities', 'subscribe.text'),
        'placeholder' => content('activities', 'subscribe.placeholder'),
        'button'      => content('activities', 'subscribe.button'),
        'source'      => 'activity',
    ]) ?>
<?php section_end(); ?>
