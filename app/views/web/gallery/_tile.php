<?php
/**
 * One gallery picture as a <li> with a lightbox link (assets/js/main.js opens a[data-lightbox] groups; without JavaScript the link
 * opens the picture itself). Vars: $img (gallery_images row), $group (lightbox group name), $variant ('masonry' | 'strip').
 * width / height come from the file header when the picture is stored on this server, so the layout does not jump while it loads.
 * The picture keeps its own proportions in the masonry layout, so faces are never cropped there.
 */
$src = img_url((string) ($img['image'] ?? ''));
if ($src === '') {
    return;
}
$caption = trim((string) ($img['caption'] ?? ''));
$alt = trim((string) ($img['alt_text'] ?? '')) !== '' ? (string) $img['alt_text'] : $caption;
$variant = ($variant ?? 'masonry') === 'strip' ? 'strip' : 'masonry';

$w = 0;
$h = 0;
$stored = (string) ($img['image'] ?? '');
if (!preg_match('~^https?://~i', $stored) && !str_contains($stored, '..')) {
    $file = str_starts_with($stored, 'assets/') ? BASE_PATH . '/' . $stored : UPLOAD_PATH . '/' . ltrim($stored, '/');
    if (is_file($file)) {
        $size = @getimagesize($file);
        if (is_array($size) && $size[0] > 0 && $size[1] > 0) {
            [$w, $h] = [(int) $size[0], (int) $size[1]];
        }
    }
}
?>
<li class="gallery-tile gallery-tile--<?= e($variant) ?>" data-reveal>
    <a class="gallery-tile__link" href="<?= e($src) ?>" data-lightbox="<?= e($group ?? 'gallery') ?>" data-caption="<?= e($caption) ?>" data-viewer-label="<?= e((string) content('global', 'labels.image_viewer')) ?>" data-close-label="<?= e((string) content('global', 'labels.close')) ?>" data-prev-label="<?= e((string) content('global', 'labels.prev_image')) ?>" data-next-label="<?= e((string) content('global', 'labels.next_image')) ?>"<?= trim($alt) === '' ? ' aria-label="' . e(content('gallery', 'grid.open_label')) . '"' : '' ?>>
        <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" loading="lazy" decoding="async"<?= $w > 0 ? ' width="' . $w . '" height="' . $h . '"' : '' ?>>
        <?php if ($caption !== ''): ?><span class="gallery-tile__caption"><?= e($caption) ?></span><?php endif; ?>
    </a>
</li>
