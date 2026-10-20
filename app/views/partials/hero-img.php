<?php
/**
 * Background photo of a banner / hero (page-hero, programs-hero, hero-photo, band). Sends a <picture> with small WebP copies when the photo has them
 * (see responsive_sources() and bin/build-hero-images.py), otherwise the plain <img> as before.
 * Vars: $src, $alt, $class (css class of the <img>), $focus (CSS object-position, optional), $priority ('high' = the first thing on the page, default) | 'lazy'.
 */
$hiPrio = ($priority ?? 'high') === 'lazy' ? 'lazy' : 'high';
$hiSet = responsive_sources((string) $src);
$hiAttrs = ' class="' . e($class) . '" src="' . e($src) . '" alt="' . e($alt ?? '') . '" decoding="async"'
    . ($hiPrio === 'high' ? ' fetchpriority="high"' : ' loading="lazy"')
    . (!empty($focus) ? ' style="object-position:' . e($focus) . '"' : '');
if ($hiSet !== null): ?>
<picture><source type="image/webp" srcset="<?= e($hiSet['srcset']) ?>" sizes="100vw"><img<?= $hiAttrs ?>></picture>
<?php else: ?>
<img<?= $hiAttrs ?>>
<?php endif; ?>
