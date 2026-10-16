<?php
/**
 * Mosaic row of 3-5 rounded photos (2 columns on phones). Vars: $images* = list of content_img() results or ['src','alt'] arrays
 * (entries without a photo are skipped; at most 5 are shown), $class. Renders nothing without images.
 */
$mmImages = [];
foreach ((array) ($images ?? []) as $im) {
    if (!empty($im['src']) && (!array_key_exists('has', $im) || $im['has'])) {
        $mmImages[] = $im;
    }
}
$mmImages = array_slice($mmImages, 0, 5);
if (!$mmImages) {
    return;
}
$mmCount = count($mmImages);
?>
<div class="mosaic<?= $mmCount === 5 ? ' mosaic--5' : ($mmCount === 3 ? ' mosaic--3' : '') ?><?= !empty($class) ? ' ' . e($class) : '' ?>">
    <?php foreach ($mmImages as $im): ?>
        <figure class="mosaic__item" data-reveal><img src="<?= e($im['src']) ?>" alt="<?= e($im['alt'] ?? '') ?>" loading="lazy" decoding="async"></figure>
    <?php endforeach; ?>
</div>
