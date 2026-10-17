<?php
/** E-mail: quote block. Vars: $block, $issue, $context ('email'). Returns a table row. */
use App\Services\Newsletter\NewsletterRenderer as R;

$serif = "Georgia,'Times New Roman',Times,serif";
$sans = "'DM Sans',Arial,Helvetica,sans-serif";
$wrap = 'word-break:break-word;overflow-wrap:anywhere;word-wrap:break-word;';    // a pasted URL or a very long word must wrap, not stretch the 600px card
$body = R::richHtml($block['body_html'], 'email');
if ($body === '') {
    return;
}
?>
<tr><td style="padding:20px 32px 4px 32px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#EEF1F6;border-radius:12px;"><tr><td style="padding:20px 24px;border-left:4px solid #D6A151;border-radius:12px;font-family:<?= $serif ?>;font-size:19px;line-height:1.55;color:#2C3950;font-style:italic;<?= $wrap ?>">
        <?= str_replace('<p style="margin:0 0 16px 0;', '<p style="margin:0 0 8px 0;', $body) ?>
        <?php if ($block['cite'] !== ''): ?><p style="margin:8px 0 0 0;font-family:<?= $sans ?>;font-size:13px;line-height:1.4;font-style:normal;font-weight:bold;color:#5A6470;">&mdash; <?= e($block['cite']) ?></p><?php endif; ?>
    </td></tr></table>
</td></tr>
