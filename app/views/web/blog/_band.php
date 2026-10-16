<?php
/**
 * Navy call-to-action band (rounded deep panel) for the bottom of the inner pages (B-INNER-B).
 * Vars: $title* ([[highlight]] allowed), $text, $eyebrow, $actions (list of ['label','url','external'?]); the first action is the orange pill,
 * the others are outline pills. Nothing is printed without a title. Wrap it in section_begin(..., ['bleed' => true, 'class' => 'section--band-wrap']).
 */
$title = trim((string) ($title ?? ''));
if ($title === '') {
    return;
}
$text = trim((string) ($text ?? ''));
$eyebrow = trim((string) ($eyebrow ?? ''));
$actions = array_values(array_filter((array) ($actions ?? []), static fn($a): bool => !empty($a['label']) && !empty($a['url'])));
?>
<div class="cta-wrap">
    <div class="container">
        <div class="cta cta--inner" data-reveal>
            <?php if ($eyebrow !== ''): ?><span class="eyebrow eyebrow--center"><?= e($eyebrow) ?></span><?php endif; ?>
            <h2 class="cta__title"><?= highlight($title) ?></h2>
            <?php if ($text !== ''): ?><p class="cta__text"><?= e($text) ?></p><?php endif; ?>
            <?php if ($actions): ?>
                <div class="cta__actions">
                    <?php foreach ($actions as $i => $a): ?>
                        <a class="btn <?= $i === 0 ? 'btn--cta' : 'btn--outline-light' ?> btn--caps btn--lg" href="<?= e($a['url']) ?>"<?= !empty($a['external']) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($a['label']) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
