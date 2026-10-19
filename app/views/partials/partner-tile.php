<?php
/**
 * ONE partner in a uniform logo box (About wall, footer strip). OWNER: PARTNERS agent (docs/PARTNERS_NEWSLETTER.md section 6).
 * Vars: $partner (partners row)*, $variant ('wall' default | 'strip'), $loading ('lazy' default | 'eager').
 *
 * - With a website (http/https only): the box is a link that opens in a new tab (rel noopener noreferrer). Its accessible name is the logo's
 *   alt text = the partner's name (or the name text itself when there is no logo).
 * - Without a website: a plain box, no link.
 * - Without a logo: the partner's NAME as text in the same box, so nothing looks broken.
 * The picture markup is partials/partner-logo-img (srcset 1x/2x, WebP source, width + height, lazy): the only place that prints a logo.
 */
$ptRow = (array) ($partner ?? []);
$ptName = trim((string) ($ptRow['name'] ?? ''));
if ($ptName === '') {
    return;
}
$ptVariant = in_array($variant ?? 'wall', ['wall', 'strip'], true) ? ($variant ?? 'wall') : 'wall';
$ptSite = trim((string) ($ptRow['website'] ?? ''));
if (preg_match('~^https?://[^\s"\'<>]+$~i', $ptSite) !== 1) {
    $ptSite = '';                                   // same rule as OrgPublicController::safeUrl(): never echo javascript: / data: / junk into an href
}
$ptLogo = \App\Services\PartnerLogos::view($ptRow);
$ptTag = $ptSite !== '' ? 'a' : 'div';
?>
<<?= $ptTag ?> class="plogo plogo--<?= e($ptVariant) ?>"<?= $ptTag === 'a' ? ' href="' . e($ptSite) . '" target="_blank" rel="noopener noreferrer"' : '' ?> data-partner="<?= (int) ($ptRow['id'] ?? 0) ?>">
    <?php if ($ptLogo['has'] && empty($ptLogo['missing'])): ?>
        <?= partial('partials/partner-logo-img', ['partner' => $ptRow, 'loading' => ($loading ?? 'lazy') === 'eager' ? 'eager' : 'lazy']) ?>
    <?php else: ?>
        <span class="plogo__name"><?= e($ptName) ?></span>
    <?php endif; ?>
</<?= $ptTag ?>>
