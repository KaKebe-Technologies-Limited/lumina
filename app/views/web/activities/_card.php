<?php
/** Activity card. Vars: $activity (row), $cta (link label). */
$href = url('activities/' . $activity['slug']);
$img = img_url($activity['image'] ?? '');
$cta = $cta ?? (string) content('global', 'labels.read_more');
?>
<article class="card activity-card" data-reveal>
    <?php if ($img !== ''): ?>
        <a class="card__media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($img) ?>" alt="<?= e($activity['image_alt'] ?? '') ?>" loading="lazy"></a>
    <?php endif; ?>
    <div class="card__body">
        <div class="card__meta">
            <?php if (!empty($activity['activity_date'])): ?><span class="card__meta-item"><?= icon('calendar', 'icon--sm') ?> <time datetime="<?= e(format_date($activity['activity_date'], 'Y-m-d')) ?>"><?= e(format_date($activity['activity_date'])) ?></time></span><?php endif; ?>
            <?php if (!empty($activity['location'])): ?><span class="card__meta-item"><?= icon('map-pin', 'icon--sm') ?> <?= e($activity['location']) ?></span><?php endif; ?>
        </div>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($activity['title']) ?></a></h3>
        <?php if (!empty($activity['summary'])): ?><p class="card__text"><?= e($activity['summary']) ?></p><?php endif; ?>
        <div class="card__footer"><a class="link-arrow" href="<?= e($href) ?>"><?= e($cta) ?><span class="visually-hidden">: <?= e($activity['title']) ?></span> <?= icon('arrow-right', 'icon--arrow') ?></a></div>
    </div>
</article>
