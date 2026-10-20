<?php
/**
 * One shop picture as <picture> (WebP + JPEG, 1x / 2x with width descriptors). OWNER: STOREFRONT. Vars: $img (ProductImages::view row), $sizes (sizes attribute),
 * $class (class of the <img>), $lazy (default true; false for the first screen), $alt (override; default the picture's own alt text), $attrs (extra attributes array).
 * Prints nothing without a picture.
 */
if (empty($img) || empty($img['src'])) {
    return;
}
$shLazy = ($lazy ?? true) !== false;
$shSizes = (string) ($sizes ?? '100vw');
$shW = (int) ($img['width'] ?? 0);
$shH = (int) ($img['height'] ?? 0);
?>
<picture>
    <?php if (!empty($img['webp_srcset'])): ?><source type="image/webp" srcset="<?= e($img['webp_srcset']) ?>" sizes="<?= e($shSizes) ?>"><?php elseif (!empty($img['webp'])): ?><source type="image/webp" srcset="<?= e($img['webp']) ?>"><?php endif; ?>
    <img<?= attrs([
        'class'    => !empty($class) ? (string) $class : null,
        'src'      => (string) $img['src'],
        'srcset'   => !empty($img['srcset']) ? (string) $img['srcset'] : null,
        'sizes'    => !empty($img['srcset']) ? $shSizes : null,
        'alt'      => (string) ($alt ?? $img['alt'] ?? ''),
        'width'    => $shW > 0 ? $shW : null,
        'height'   => $shH > 0 ? $shH : null,
        'loading'  => $shLazy ? 'lazy' : null,
        'fetchpriority' => $shLazy ? null : 'high',
        'decoding' => 'async',
    ] + (array) ($attrs ?? [])) ?>>
</picture>
