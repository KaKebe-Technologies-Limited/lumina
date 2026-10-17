<?php
/**
 * The featured article of an issue (the large one): a cream panel, photo beside the text from lg. Vars: $featured (heading, body_html, image, link),
 * $issue, $context. Rendered by NewsletterRenderer::renderFeatured(); an empty featured article is never rendered. OWNER: WEB agent.
 */
use App\Services\Newsletter\NewsletterRenderer as R;

$preview = $context === 'preview';
$img = $featured['image'];
$hasImg = $img['path'] !== '';
$size = R::imageSize($img['path']);
$body = R::richHtml($featured['body_html'], $context);
$link = $featured['link'];
$href = R::href($link['url'], $context);
$linkLabel = $link['label'] !== '' ? $link['label'] : (string) content('newsletter', 'issue.read_more');
$external = $href !== '' && R::isExternal($link['url']);
?>
<section class="nl-featured<?= $hasImg ? ' nl-featured--has-image' : '' ?>"<?= $preview ? ' data-nl-block="featured" tabindex="0"' : '' ?> data-reveal<?= $featured['heading'] !== '' ? ' aria-labelledby="nl-featured-title"' : '' ?>>
    <?php if ($hasImg): ?>
        <figure class="nl-figure nl-figure--featured">
            <img src="<?= e(R::imageUrl($img, $context)) ?>" alt="<?= e($img['alt']) ?>"<?= $size ? ' width="' . $size[0] . '" height="' . $size[1] . '"' : '' ?> loading="lazy" decoding="async">
            <?php if ($img['caption'] !== ''): ?><figcaption><?= e($img['caption']) ?></figcaption><?php endif; ?>
        </figure>
    <?php endif; ?>
    <div class="nl-featured__body">
        <p class="nl-featured__label eyebrow"><?= e(content('newsletter', 'issue.featured_label')) ?></p>
        <?php if ($featured['heading'] !== ''): ?><h2 class="nl-featured__title" id="nl-featured-title"><?= e($featured['heading']) ?></h2><?php endif; ?>
        <?php if ($body !== ''): ?><div class="prose nl-prose"><?= $body ?></div><?php endif; ?>
        <?php if ($href !== ''): ?>
            <p class="nl-featured__link"><a class="btn btn--cta btn--caps" href="<?= e($href) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($linkLabel) ?> <?= icon($external ? 'external-link' : 'arrow-right', 'icon--sm') ?></a></p>
        <?php endif; ?>
    </div>
</section>
