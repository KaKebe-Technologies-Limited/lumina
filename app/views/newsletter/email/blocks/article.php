<?php
/** E-mail: article block. Vars: $block, $issue, $context ('email'). Returns a table row. */
use App\Services\Newsletter\NewsletterRenderer as R;

$serif = "Georgia,'Times New Roman',Times,serif";
$sans = "'DM Sans',Arial,Helvetica,sans-serif";
$wrap = 'word-break:break-word;overflow-wrap:anywhere;word-wrap:break-word;';    // a pasted URL or a very long word must wrap, not stretch the 600px card
$img = $block['image'];
$body = R::richHtml($block['body_html'], 'email');
$link = $block['link'];
$href = R::href($link['url'], 'email');
$linkLabel = $link['label'] !== '' ? $link['label'] : (string) content('newsletter', 'issue.read_more');
?>
<tr><td style="padding:24px 32px 8px 32px;font-family:<?= $sans ?>;font-size:16px;line-height:1.65;color:#1D1E20;<?= $wrap ?>">
    <?php if ($img['path'] !== ''): ?>
        <img src="<?= e(R::imageUrl($img, 'email')) ?>" width="536" alt="<?= e($img['alt']) ?>" style="display:block;border:0;width:100%;max-width:536px;height:auto;border-radius:12px;margin:0 0 8px 0;">
        <?php if ($img['caption'] !== ''): ?><p style="margin:0 0 12px 0;font-size:13px;line-height:1.5;color:#5A6470;"><?= e($img['caption']) ?></p><?php endif; ?>
    <?php endif; ?>
    <?php if ($block['heading'] !== ''): ?><h2 style="margin:8px 0 10px 0;font-family:<?= $serif ?>;font-size:21px;line-height:1.3;color:#2C3950;font-weight:bold;<?= $wrap ?>"><?= e($block['heading']) ?></h2><?php endif; ?>
    <?= $body ?>
    <?php if ($href !== ''): ?>
        <p style="margin:0 0 8px 0;"><a href="<?= e($href) ?>" style="color:#A64B0C;font-weight:bold;text-decoration:underline;<?= $wrap ?>"><?= e($linkLabel) ?> &rarr;</a></p>
    <?php endif; ?>
</td></tr>
