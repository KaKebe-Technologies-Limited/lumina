<?php
/**
 * Impact story card. Vars: $story (stories row - only pass rows with consent_confirmed = 1, e.g. Story::published()),
 * $cta (link label; default content('stories','list.cta')). Links to /stories/{slug}.
 */
if (empty($story['consent_confirmed'])) {
    return;                     // never render a story without confirmed consent
}
$href = url('stories/' . $story['slug']);
$cta = $cta ?? (string) content('stories', 'list.cta');
$img = img_url($story['image'] ?? '');
?>
<article class="story-card" data-reveal>
    <?php if ($img !== ''): ?>
        <div class="story-card__media"><img src="<?= e($img) ?>" alt="<?= e($story['image_alt'] ?? '') ?>" loading="lazy"></div>
    <?php endif; ?>
    <div class="story-card__body">
        <h3 class="story-card__title"><a href="<?= e($href) ?>"><?= e($story['title']) ?></a></h3>
        <?php if (!empty($story['summary'])): ?><p class="story-card__text"><?= e($story['summary']) ?></p><?php endif; ?>
        <?php if (!empty($story['person_name'])): ?>
            <p class="story-card__person"><?= e($story['person_name']) ?>
                <?php if (!empty($story['person_role']) || !empty($story['location'])): ?><small><?= e(implode(' · ', array_filter([$story['person_role'] ?? '', $story['location'] ?? '']))) ?></small><?php endif; ?>
            </p>
        <?php endif; ?>
        <span class="link-arrow"><?= e($cta) ?> <?= icon('arrow-right', 'icon--arrow') ?></span>
    </div>
</article>
