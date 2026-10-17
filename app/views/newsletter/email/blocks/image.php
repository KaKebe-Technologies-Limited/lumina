<?php
/** E-mail: image block. Vars: $block, $issue, $context ('email'). Returns a table row (nothing without a picture). */
use App\Services\Newsletter\NewsletterRenderer as R;

$sans = "'DM Sans',Arial,Helvetica,sans-serif";
$wrap = 'word-break:break-word;overflow-wrap:anywhere;word-wrap:break-word;';    // a pasted URL or a very long word must wrap, not stretch the 600px card
$img = $block['image'];
if ($img['path'] === '') {
    return;
}
?>
<tr><td style="padding:20px 32px 4px 32px;font-family:<?= $sans ?>;<?= $wrap ?>">
    <img src="<?= e(R::imageUrl($img, 'email')) ?>" width="536" alt="<?= e($img['alt']) ?>" style="display:block;border:0;width:100%;max-width:536px;height:auto;border-radius:12px;">
    <?php if ($img['caption'] !== ''): ?><p style="margin:8px 0 0 0;font-size:13px;line-height:1.5;color:#5A6470;"><?= e($img['caption']) ?></p><?php endif; ?>
</td></tr>
