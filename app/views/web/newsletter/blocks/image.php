<?php
/** Image block. Vars: $block (image: path, alt, caption), $issue, $context. OWNER: WEB agent. */
use App\Services\Newsletter\NewsletterRenderer as R;

$preview = $context === 'preview';
$img = $block['image'];
if ($img['path'] === '' && !$preview) {
    return;
}
$size = R::imageSize($img['path']);
?>
<figure class="nl-block nl-block--image nl-figure"<?= $preview ? ' data-nl-block="' . e($block['id']) . '" tabindex="0"' : '' ?> data-reveal>
    <?php if ($img['path'] !== ''): ?>
        <img src="<?= e(R::imageUrl($img, $context)) ?>" alt="<?= e($img['alt']) ?>"<?= $size ? ' width="' . $size[0] . '" height="' . $size[1] . '"' : '' ?> loading="lazy" decoding="async">
        <?php if ($img['caption'] !== ''): ?><figcaption><?= e($img['caption']) ?></figcaption><?php endif; ?>
    <?php endif; ?>
</figure>
