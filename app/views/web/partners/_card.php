<?php
/**
 * One partner card on /partners - OWNED BY B7 "org-public", logo markup reworked by the PARTNERS agent (docs/PARTNERS_NEWSLETTER.md section 6).
 * Vars: $partner (partners row; website already filtered to http(s) by PartnersController), $visitLabel (link text).
 * The logo is the shared optimised picture (partials/partner-logo-img: WebP + PNG, 1x/2x, width + height, lazy) inside the same fixed-height box as
 * the About page and the footer; alt text = the organisation's name. Without a logo the card shows the first letter on a navy tile (decorative).
 * Links to other sites open in a new tab with rel="noopener noreferrer". The logo is also clickable (mouse users), but hidden from keyboard and
 * screen readers because the name / "Visit website" link right next to it already goes to the same address.
 */
$p = $partner;
$name = (string) $p['name'];
$pcView = \App\Services\PartnerLogos::view($p);
$hasLogo = $pcView['has'] && empty($pcView['missing']);          // a logo file that is gone shows the initial, never a broken picture
$site = (string) ($p['website'] ?? '');
$desc = trim((string) ($p['description'] ?? ''));
?>
<article class="partner-card" data-reveal>
    <div class="partner-card__logo plogo">
        <?php if ($hasLogo): ?>
            <?php if ($site !== ''): ?><a class="partner-card__logo-link" href="<?= e($site) ?>" target="_blank" rel="noopener noreferrer" tabindex="-1" aria-hidden="true"><?php endif; ?>
            <?= partial('partials/partner-logo-img', ['partner' => $p]) ?>
            <?php if ($site !== ''): ?></a><?php endif; ?>
        <?php else: ?>
            <span class="partner-card__initial" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
        <?php endif; ?>
    </div>
    <div class="partner-card__body">
        <h3 class="partner-card__name"><?php if ($site !== '' && trim($visitLabel) === ''): ?><a href="<?= e($site) ?>" target="_blank" rel="noopener noreferrer"><?= e($name) ?></a><?php else: ?><?= e($name) ?><?php endif; ?></h3>
        <?php if ($desc !== ''): ?><div class="partner-card__desc"><?= nl2p($desc) ?></div><?php endif; ?>
        <?php if ($site !== '' && trim($visitLabel) !== ''): ?>
            <a class="link-arrow partner-card__link" href="<?= e($site) ?>" target="_blank" rel="noopener noreferrer"><?= e($visitLabel) ?><span class="visually-hidden">: <?= e($name) ?></span> <?= icon('external-link', 'icon--arrow') ?></a>
        <?php endif; ?>
    </div>
</article>
