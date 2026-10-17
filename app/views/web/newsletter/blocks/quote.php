<?php
/** Quote block (pull quote). Vars: $block (body_html, cite), $issue, $context. OWNER: WEB agent. */
use App\Services\Newsletter\NewsletterRenderer as R;

$preview = $context === 'preview';
$body = R::richHtml($block['body_html'], $context);
if ($body === '' && !$preview) {
    return;
}
?>
<figure class="nl-block nl-block--quote"<?= $preview ? ' data-nl-block="' . e($block['id']) . '" tabindex="0"' : '' ?> data-reveal>
    <span class="nl-quote__mark" aria-hidden="true"><?= icon('quote', 'icon--lg') ?></span>
    <blockquote class="nl-quote"><?= $body ?></blockquote>
    <?php if ($block['cite'] !== ''): ?><figcaption class="nl-quote__cite"><?= e($block['cite']) ?></figcaption><?php endif; ?>
</figure>
