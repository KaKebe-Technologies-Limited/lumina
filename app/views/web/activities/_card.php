<?php
/**
 * Activity card. Vars: $activity (row), $cta (link label).
 * Photo on top with a round date badge on its edge; without a photo a navy tile with an icon is shown (decorative).
 */
$href = url('activities/' . $activity['slug']);
$img = img_url($activity['image'] ?? '');
$cta = $cta ?? (string) content('global', 'labels.read_more');
$when = to_datetime($activity['activity_date'] ?? null);
?>
<article class="card activity-card" data-reveal>
    <a class="card__media<?= $img === '' ? ' card__media--ph' : '' ?>" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($img !== ''): ?>
            <img src="<?= e($img) ?>" alt="<?= e($activity['image_alt'] ?? '') ?>" loading="lazy" decoding="async">
        <?php else: ?>
            <span class="card__ph"><?= icon('activity', 'icon--xl') ?></span>
        <?php endif; ?>
    </a>
    <div class="card__body card__body--chip">
        <?php if ($when): ?>
            <span class="card__chip card__chip--date" aria-hidden="true">
                <span class="card__chip-day"><?= e($when->format('j')) ?></span>
                <span class="card__chip-month"><?= e($when->format('M')) ?></span>
            </span>
        <?php else: ?>
            <span class="card__chip" aria-hidden="true"><?= icon('activity', 'icon--lg') ?></span>
        <?php endif; ?>
        <div class="card__meta">
            <?php if ($when): ?><span class="card__meta-item"><?= icon('calendar', 'icon--sm') ?> <time datetime="<?= e($when->format('Y-m-d')) ?>"><?= e(format_date($activity['activity_date'])) ?></time></span><?php endif; ?>
            <?php if (!empty($activity['location'])): ?><span class="card__meta-item"><?= icon('map-pin', 'icon--sm') ?> <?= e($activity['location']) ?></span><?php endif; ?>
        </div>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($activity['title']) ?></a></h3>
        <?php if (!empty($activity['summary'])): ?><p class="card__text"><?= e($activity['summary']) ?></p><?php endif; ?>
        <div class="card__footer"><a class="btn btn--cta btn--caps btn--sm" href="<?= e($href) ?>"><?= e($cta) ?><span class="visually-hidden">: <?= e($activity['title']) ?></span></a></div>
    </div>
</article>
