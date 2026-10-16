<?php
/**
 * Card for a projects row. Vars: $project (DB row), $cta (link label; default content('global','labels.read_more')),
 * $statusLabels (optional map status => label for the badge; the badge is hidden when not given).
 * Links to /projects/{slug}.
 */
$href = url('projects/' . $project['slug']);
$cta = $cta ?? (string) content('global', 'labels.read_more');
$img = img_url($project['image'] ?? '');
$statusBadge = ['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$project['status'] ?? ''] ?? 'neutral';
?>
<article class="card" data-reveal>
    <?php if ($img !== ''): ?>
        <a class="card__media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true">
            <img src="<?= e($img) ?>" alt="" loading="lazy">
            <?php if (!empty($statusLabels[$project['status'] ?? ''])): ?><span class="card__badge badge badge--<?= e($statusBadge) ?>"><?= e($statusLabels[$project['status']]) ?></span><?php endif; ?>
        </a>
    <?php endif; ?>
    <div class="card__body">
        <?php if (!empty($project['tagline']) || !empty($project['location'])): ?>
            <div class="card__meta">
                <?php if (!empty($project['tagline'])): ?><span><?= e($project['tagline']) ?></span><?php endif; ?>
                <?php if (!empty($project['location'])): ?><span><?= icon('map-pin', 'icon--sm') ?> <?= e($project['location']) ?></span><?php endif; ?>
            </div>
        <?php endif; ?>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($project['title']) ?></a></h3>
        <?php if (!empty($project['summary'])): ?><p class="card__text"><?= e($project['summary']) ?></p><?php endif; ?>
        <div class="card__footer"><a class="link-arrow" href="<?= e($href) ?>"><?= e($cta) ?><span class="visually-hidden">: <?= e($project['title']) ?></span> <?= icon('arrow-right', 'icon--arrow') ?></a></div>
    </div>
</article>
