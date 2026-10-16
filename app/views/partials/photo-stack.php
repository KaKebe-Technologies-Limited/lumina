<?php
/**
 * Stack of 1-3 rounded photos; the FIRST image is the front one (tilted -6deg), the others peek out behind it.
 * Vars: $images = list of content_img() results or ['src','alt'] arrays (entries without src are skipped), $class (extra classes).
 *   <?= partial('partials/photo-stack', ['images' => [content_img('home','gift.image'), content_img('home','gift.image2'), content_img('home','gift.image3')]]) ?>
 */
$psImages = [];
foreach ((array) ($images ?? []) as $im) {
    if (!empty($im['src']) && (!array_key_exists('has', $im) || $im['has'])) {
        $psImages[] = $im;
    }
}
$psImages = array_reverse(array_slice($psImages, 0, 3));       // CSS expects back ... front
if (!$psImages) {
    return;
}
?>
<div class="photo-stack<?= !empty($class) ? ' ' . e($class) : '' ?>" data-reveal>
    <?php foreach ($psImages as $im): ?>
        <figure class="photo-stack__item"><img src="<?= e($im['src']) ?>" alt="<?= e($im['alt'] ?? '') ?>" loading="lazy" decoding="async"></figure>
    <?php endforeach; ?>
</div>
