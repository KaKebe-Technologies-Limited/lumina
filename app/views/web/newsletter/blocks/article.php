<?php
/** Article block. Vars: $block (heading, body_html, image, link), $issue, $context. OWNER: WEB agent. With a picture it is a two-column row from md (alternating sides). */
use App\Services\Newsletter\NewsletterRenderer as R;

$preview = $context === 'preview';
$img = $block['image'];
$hasImg = $img['path'] !== '';
$size = R::imageSize($img['path']);
$body = R::richHtml($block['body_html'], $context);
$link = $block['link'];
$href = R::href($link['url'], $context);
$linkLabel = $link['label'] !== '' ? $link['label'] : (string) content('newsletter', 'issue.read_more');
$external = $href !== '' && R::isExternal($link['url']);
?>
<section class="nl-block nl-block--article<?= $hasImg ? ' nl-block--has-image' : '' ?>"<?= $preview ? ' data-nl-block="' . e($block['id']) . '" tabindex="0"' : '' ?> data-reveal>
    <?php if ($hasImg): ?>
        <figure class="nl-figure">
            <img src="<?= e(R::imageUrl($img, $context)) ?>" alt="<?= e($img['alt']) ?>"<?= $size ? ' width="' . $size[0] . '" height="' . $size[1] . '"' : '' ?> loading="lazy" decoding="async">
            <?php if ($img['caption'] !== ''): ?><figcaption><?= e($img['caption']) ?></figcaption><?php endif; ?>
        </figure>
    <?php endif; ?>
    <div class="nl-block__body">
        <?php if ($block['heading'] !== ''): ?><h2 class="nl-block__title"><?= e($block['heading']) ?></h2><?php endif; ?>
        <?php if ($body !== ''): ?><div class="prose nl-prose"><?= $body ?></div><?php endif; ?>
        <?php if ($href !== ''): ?>
            <p class="nl-block__link"><a class="link-arrow" href="<?= e($href) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($linkLabel) ?> <?= icon($external ? 'external-link' : 'arrow-right', 'icon--sm') ?></a></p>
        <?php endif; ?>
    </div>
</section>
