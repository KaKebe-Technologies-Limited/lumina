<?php
/** Single activity. Vars: $activity (row), $more (rows), $absUrl, $share. Labels: content('activities', 'detail.*'). */
$img = img_url($activity['image'] ?? '');
$when = to_datetime($activity['activity_date'] ?? null);
$place = trim((string) ($activity['location'] ?? ''));
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/blog.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/page-hero', [
    'title'   => $activity['title'],
    'eyebrow' => '',
    'lead'    => (string) ($activity['summary'] ?? ''),
]) ?>

<article class="section post activity">
    <div class="container container--narrow">
        <?php if ($when || $place !== ''): ?>
            <dl class="activity__facts">
                <?php if ($when): ?>
                    <div class="activity__fact"><dt><?= icon('calendar', 'icon--sm') ?> <?= e(content('activities', 'detail.date_label')) ?></dt><dd><time datetime="<?= e($when->format('Y-m-d')) ?>"><?= e(format_date($activity['activity_date'])) ?></time></dd></div>
                <?php endif; ?>
                <?php if ($place !== ''): ?>
                    <div class="activity__fact"><dt><?= icon('map-pin', 'icon--sm') ?> <?= e(content('activities', 'detail.location_label')) ?></dt><dd><?= e($place) ?></dd></div>
                <?php endif; ?>
            </dl>
        <?php endif; ?>

        <?php if ($img !== ''): ?>
            <figure class="post__cover">
                <img src="<?= e($img) ?>" alt="<?= e($activity['image_alt'] ?? '') ?>" fetchpriority="high" decoding="async">
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

        <p class="post__back"><a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('activities')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('activities', 'detail.back_label')) ?></a></p>
    </div>
</article>

<?php if ($more): ?>
    <section class="section section--tint" aria-labelledby="more-title">
        <div class="container">
            <?= partial('partials/section-head', ['title' => content('activities', 'detail.more_title'), 'id' => 'more-title', 'align' => 'center']) ?>
            <div class="grid grid--3 grid--gap-lg post-grid post-grid--n<?= min(count($more), 3) ?>">
                <?php foreach ($more as $a): ?><?= partial('web/activities/_card', ['activity' => $a]) ?><?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php section_begin('activities', 'subscribe', ['class' => 'section--panel', 'id' => 'subscribe']); ?>
    <?= partial('web/blog/_subscribe', [
        'id'          => 'activity-subscribe',
        'eyebrow'     => content('activities', 'subscribe.eyebrow'),
        'title'       => content('activities', 'subscribe.title'),
        'text'        => content('activities', 'subscribe.text'),
        'placeholder' => content('activities', 'subscribe.placeholder'),
        'button'      => content('activities', 'subscribe.button'),
        'source'      => 'activity',
    ]) ?>
<?php section_end(); ?>
