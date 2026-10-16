<?php
/**
 * Logo grid. Vars: $partners (list of partners rows), $showType (bool, default false), $typeLabels (map partner_type => label).
 * Logos are shown at their natural aspect; the alt text is the organisation name.
 */
if (empty($partners)) {
    return;
}
?>
<div class="partner-grid">
    <?php foreach ($partners as $p):
        $logo = img_url($p['logo'] ?? '');
        $tag = !empty($p['website']) ? 'a' : 'div';
    ?>
        <<?= $tag ?> class="partner-logo"<?= $tag === 'a' ? ' href="' . e($p['website']) . '" target="_blank" rel="noopener noreferrer"' : '' ?> data-reveal>
            <?php if ($logo !== ''): ?>
                <img src="<?= e($logo) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
            <?php else: ?>
                <span class="partner-logo__name"><?= e($p['name']) ?></span>
            <?php endif; ?>
            <?php if (!empty($showType) && !empty($typeLabels[$p['partner_type']] ?? '')): ?><span class="partner-logo__type"><?= e($typeLabels[$p['partner_type']]) ?></span><?php endif; ?>
        </<?= $tag ?>>
    <?php endforeach; ?>
</div>
