<?php
/**
 * Impact-story card (B-INNER-B; used on the Stories pages instead of partials/story-card).
 * Vars: $story (stories row - ONLY rows with consent_confirmed = 1, e.g. Story::published(); the card prints nothing otherwise), $cta (link label).
 * With a photo: photo on top and a round quote badge on its edge. Without a photo: a pale tint card with a big quote mark (no placeholder picture).
 * Links to /stories/{slug}.
 */
if (empty($story['consent_confirmed'])) {
    return;                     // never render a story without confirmed consent
}
$href = url('stories/' . $story['slug']);
$cta = $cta ?? (string) content('stories', 'list.cta');
$img = img_url($story['image'] ?? '');
$name = trim((string) ($story['person_name'] ?? ''));
$meta = implode(' · ', array_filter([trim((string) ($story['person_role'] ?? '')), trim((string) ($story['location'] ?? ''))]));
$initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '';
?>
<article class="card story-tile<?= $img === '' ? ' story-tile--text' : '' ?>" data-reveal>
    <?php if ($img !== ''): ?>
        <a class="card__media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($img) ?>" alt="" loading="lazy" decoding="async"></a>
    <?php endif; ?>
    <div class="card__body<?= $img !== '' ? ' card__body--chip' : '' ?>">
        <?php if ($img !== ''): ?>
            <span class="card__chip" aria-hidden="true"><?= icon('quote', 'icon--lg') ?></span>
        <?php else: ?>
            <span class="story-tile__mark" aria-hidden="true"><?= icon('quote') ?></span>
        <?php endif; ?>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($story['title']) ?></a></h3>
        <?php if (trim((string) ($story['summary'] ?? '')) !== ''): ?><p class="card__text"><?= e($story['summary']) ?></p><?php endif; ?>
        <?php if ($name !== ''): ?>
            <p class="story-tile__person">
                <span class="story-tile__avatar" aria-hidden="true"><?= e($initial) ?></span>
                <span><span class="story-tile__name"><?= e($name) ?></span><?php if ($meta !== ''): ?><span class="story-tile__role"><?= e($meta) ?></span><?php endif; ?></span>
            </p>
        <?php endif; ?>
        <div class="card__footer"><span class="link-arrow"><?= e($cta) ?> <?= icon('arrow-right', 'icon--arrow') ?></span></div>
    </div>
</article>
