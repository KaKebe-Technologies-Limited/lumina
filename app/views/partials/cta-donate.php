<?php
/**
 * Navy donate call-to-action band. Copy comes from the "Site-wide content" registry page (admin: Pages > Site-wide content).
 * Optional overrides: $title, $text, $class. Can be hidden everywhere via the 'cta' section toggle.
 *   <?= partial('partials/cta-donate') ?>
 */
if (!section_on('global', 'cta')) {
    return;
}
$title = $title ?? (string) content('global', 'cta.title');
$text = $text ?? (string) content('global', 'cta.text');
$primaryUrl = trim((string) content('global', 'cta.primary_url'));
$primaryHref = $primaryUrl !== '' ? link_url($primaryUrl) : (string) setting('donate_url', '');
$primaryLabel = trim((string) content('global', 'cta.primary_label'));
$secondary = content_btn('global', 'cta.secondary');
?>
<div class="cta-wrap<?= !empty($class) ? ' ' . e($class) : '' ?>">
    <div class="container">
        <div class="cta" data-reveal>
            <?php if ($title !== ''): ?><h2 class="cta__title"><?= e($title) ?></h2><?php endif; ?>
            <?php if ($text !== ''): ?><p class="cta__text"><?= e($text) ?></p><?php endif; ?>
            <div class="cta__actions">
                <?php if ($primaryLabel !== '' && $primaryHref !== ''): ?>
                    <a class="btn btn--accent btn--lg" href="<?= e($primaryHref) ?>"<?= is_external($primaryHref) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= icon('heart') ?> <?= e($primaryLabel) ?></a>
                <?php endif; ?>
                <?php if ($secondary['has']): ?>
                    <a class="btn btn--outline-light btn--lg" href="<?= e($secondary['url']) ?>"><?= e($secondary['label']) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
