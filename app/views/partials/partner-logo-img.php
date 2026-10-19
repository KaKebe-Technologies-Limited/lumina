<?php
/**
 * ONE partner logo as an optimised, layout-shift-free picture. Used by the About section, /partners, the footer strip and the admin cards,
 * so they all show a logo identically (contract: docs/PARTNERS_NEWSLETTER.md section 6).
 *
 * Vars: $partner (partners row)*, $loading ('lazy' default | 'eager'), $class (extra classes on the <img>), $sizes (optional sizes attr).
 * Prints nothing when the partner has no picture or its file is gone (the caller shows the name instead: check PartnerLogos::view($partner)['has'] and ['missing'] first).
 * Markup: <picture> with a WebP <source> (+2x) when the pipeline made one, and an <img> with the PNG/JPEG fallback (+2x srcset). The width and
 * height attributes are the 1x size, so the browser reserves the box before the picture arrives. Alt text = the partner's name.
 */
$plv = \App\Services\PartnerLogos::view($partner ?? []);
if (!$plv['has'] || !empty($plv['missing'])) {
    return;
}
$plLoading = ($loading ?? 'lazy') === 'eager' ? 'eager' : 'lazy';
$plSizes = trim((string) ($sizes ?? ''));
?>
<picture class="partner-logo-pic"><?php if ($plv['webp'] !== ''): ?><source type="image/webp" srcset="<?= e($plv['webp_srcset']) ?>"<?= $plSizes !== '' ? ' sizes="' . e($plSizes) . '"' : '' ?>><?php endif; ?><img class="partner-logo-img<?= !empty($class) ? ' ' . e((string) $class) : '' ?>" src="<?= e($plv['src']) ?>"<?= $plv['srcset'] !== '' ? ' srcset="' . e($plv['srcset']) . '"' : '' ?><?= $plSizes !== '' ? ' sizes="' . e($plSizes) . '"' : '' ?><?= $plv['width'] !== null && $plv['height'] !== null ? ' width="' . $plv['width'] . '" height="' . $plv['height'] . '"' : '' ?> alt="<?= e($plv['alt']) ?>" loading="<?= $plLoading ?>" decoding="async"></picture>
