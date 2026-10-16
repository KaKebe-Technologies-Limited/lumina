<?php
/**
 * Project card for the home page (B1). Vars: $card (['title','tagline','summary','href','image','image_alt']), $n (1-based position), $cta (link text).
 * Works for rows of the `projects` table and for the editable fallback cards in the registry.
 */
$img = img_url((string) ($card['image'] ?? ''));
$href = (string) ($card['href'] ?? '');
?>
<article class="card card--horizontal home-project" data-reveal>
    <div class="card__media home-project__media">
        <?php if ($img !== ''): ?>
            <img src="<?= e($img) ?>" alt="<?= e($card['image_alt'] ?? '') ?>" loading="lazy">
        <?php else: ?>
            <span class="home-project__num" aria-hidden="true"><?= str_pad((string) (int) ($n ?? 1), 2, '0', STR_PAD_LEFT) ?></span>
        <?php endif; ?>
    </div>
    <div class="card__body">
        <?php if (trim((string) ($card['tagline'] ?? '')) !== ''): ?><div class="card__meta"><span><?= e($card['tagline']) ?></span></div><?php endif; ?>
        <h3 class="card__title"><?php if ($href !== ''): ?><a class="card__stretch" href="<?= e($href) ?>"><?= e($card['title']) ?></a><?php else: ?><?= e($card['title']) ?><?php endif; ?></h3>
        <?php if (trim((string) ($card['summary'] ?? '')) !== ''): ?><p class="card__text"><?= e($card['summary']) ?></p><?php endif; ?>
        <?php if ($href !== '' && trim((string) ($cta ?? '')) !== ''): ?>
            <div class="card__footer"><span class="link-arrow"><?= e($cta) ?> <?= icon('arrow-right', 'icon--arrow') ?></span></div>
        <?php endif; ?>
    </div>
</article>
