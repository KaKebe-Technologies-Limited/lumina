<?php
/**
 * One partner card - OWNED BY B7 "org-public".
 * Vars: $partner (partners row; website already filtered to http(s) by PartnersController), $visitLabel (link text).
 * The logo's alt text is the organisation's name; without a logo the card shows the first letter on a navy tile (decorative).
 * Links to other sites open in a new tab with rel="noopener noreferrer".
 */
$p = $partner;
$name = (string) $p['name'];
$logo = img_url($p['logo'] ?? '');
$site = (string) ($p['website'] ?? '');
$desc = trim((string) ($p['description'] ?? ''));
?>
<article class="partner-card" data-reveal>
    <div class="partner-card__logo">
        <?php if ($logo !== ''): ?>
            <img src="<?= e($logo) ?>" alt="<?= e($name) ?>" loading="lazy">
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
