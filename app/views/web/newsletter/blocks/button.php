<?php
/** Button block. Vars: $block (button: label, url, style primary|outline), $issue, $context. OWNER: WEB agent. */
use App\Services\Newsletter\NewsletterRenderer as R;

$preview = $context === 'preview';
$b = $block['button'];
$href = R::href($b['url'], $context);
if (($b['label'] === '' || $href === '') && !$preview) {
    return;
}
$external = $href !== '' && R::isExternal($b['url']);
?>
<p class="nl-block nl-block--button"<?= $preview ? ' data-nl-block="' . e($block['id']) . '" tabindex="0"' : '' ?> data-reveal>
    <?php if ($b['label'] !== '' && $href !== ''): ?>
        <a class="btn <?= $b['style'] === 'outline' ? 'btn--outline' : 'btn--cta' ?> btn--caps" href="<?= e($href) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($b['label']) ?> <?= icon($external ? 'external-link' : 'arrow-right', 'icon--sm') ?></a>
    <?php endif; ?>
</p>
