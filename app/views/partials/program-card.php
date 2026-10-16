<?php
/**
 * Card for a programs row. Vars: $program (DB row), $cta (link label; default content('global','labels.read_more')).
 * Links to /programs/{slug}.
 */
$href = url('programs/' . $program['slug']);
$cta = $cta ?? (string) content('global', 'labels.read_more');
$img = img_url($program['image'] ?? '');
?>
<article class="card" data-reveal>
    <?php if ($img !== ''): ?>
        <a class="card__media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($img) ?>" alt="" loading="lazy"></a>
    <?php endif; ?>
    <div class="card__body">
        <?php if (!empty($program['tagline'])): ?><div class="card__meta"><span><?= e($program['tagline']) ?></span></div><?php endif; ?>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($program['title']) ?></a></h3>
        <?php if (!empty($program['summary'])): ?><p class="card__text"><?= e($program['summary']) ?></p><?php endif; ?>
        <div class="card__footer"><a class="link-arrow" href="<?= e($href) ?>"><?= e($cta) ?><span class="visually-hidden">: <?= e($program['title']) ?></span> <?= icon('arrow-right', 'icon--arrow') ?></a></div>
    </div>
</article>
