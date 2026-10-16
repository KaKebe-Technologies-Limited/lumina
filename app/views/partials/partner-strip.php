<?php
/**
 * "Thanks to our partners" strip. Vars: $partners (list of partners rows: name, logo, website), $title (heading, from the registry).
 * Renders NOTHING when the list is empty. Logos are shown in grey and colourise on hover; a partner without a logo shows its name.
 */
if (empty($partners)) {
    return;
}
$psTitle = trim((string) ($title ?? ''));
?>
<div class="partner-strip">
    <?php if ($psTitle !== ''): ?><h2 class="partner-strip__title"><?= e($psTitle) ?></h2><?php endif; ?>
    <ul class="partner-strip__list">
        <?php foreach ($partners as $p):
            $psLogo = img_url((string) ($p['logo'] ?? ''));
            $psUrl = trim((string) ($p['website'] ?? ''));
            $psTag = $psUrl !== '' ? 'a' : 'span';
        ?>
            <li><<?= $psTag ?> class="partner-strip__item"<?= $psTag === 'a' ? ' href="' . e($psUrl) . '" target="_blank" rel="noopener noreferrer"' : '' ?>>
                <?php if ($psLogo !== ''): ?><img src="<?= e($psLogo) ?>" alt="<?= e($p['name']) ?>" loading="lazy" decoding="async"><?php else: ?><?= e($p['name']) ?><?php endif; ?>
            </<?= $psTag ?>></li>
        <?php endforeach; ?>
    </ul>
</div>
