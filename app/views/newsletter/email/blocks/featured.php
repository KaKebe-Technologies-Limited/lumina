<?php
/** E-mail: featured article. Vars: $featured (heading, body_html, image, link), $issue, $context ('email'). Returns table rows (inserted into the layout's table). */
use App\Services\Newsletter\NewsletterRenderer as R;

$serif = "Georgia,'Times New Roman',Times,serif";
$sans = "'DM Sans',Arial,Helvetica,sans-serif";
$wrap = 'word-break:break-word;overflow-wrap:anywhere;word-wrap:break-word;';    // a pasted URL or a very long word must wrap, not stretch the 600px card
$img = $featured['image'];
$body = R::richHtml($featured['body_html'], 'email');
$link = $featured['link'];
$href = R::href($link['url'], 'email');
$linkLabel = $link['label'] !== '' ? $link['label'] : (string) content('newsletter', 'issue.read_more');
?>
<tr><td style="padding:24px 32px 8px 32px;font-family:<?= $sans ?>;font-size:16px;line-height:1.65;color:#1D1E20;<?= $wrap ?>">
    <?php if ($img['path'] !== ''): ?>
        <img src="<?= e(R::imageUrl($img, 'email')) ?>" width="536" alt="<?= e($img['alt']) ?>" style="display:block;border:0;width:100%;max-width:536px;height:auto;border-radius:12px;margin:0 0 8px 0;">
        <?php if ($img['caption'] !== ''): ?><p style="margin:0 0 16px 0;font-size:13px;line-height:1.5;color:#5A6470;"><?= e($img['caption']) ?></p><?php endif; ?>
    <?php endif; ?>
    <p style="margin:12px 0 6px 0;font-size:12px;line-height:1.4;letter-spacing:1.5px;text-transform:uppercase;color:#8A5A06;font-weight:bold;"><?= e(content('newsletter', 'issue.featured_label')) ?></p>
    <?php if ($featured['heading'] !== ''): ?><h2 style="margin:0 0 12px 0;font-family:<?= $serif ?>;font-size:24px;line-height:1.3;color:#2C3950;font-weight:bold;<?= $wrap ?>"><?= e($featured['heading']) ?></h2><?php endif; ?>
    <?= $body ?>
    <?php if ($href !== ''): ?>
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 0 0;"><tr><td align="center" bgcolor="#B85A14" style="border-radius:999px;background:#B85A14;">
            <a href="<?= e($href) ?>" style="display:inline-block;padding:12px 26px;font-family:<?= $sans ?>;font-size:15px;line-height:1.2;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:999px;max-width:100%;<?= $wrap ?>"><?= e($linkLabel) ?></a>
        </td></tr></table>
    <?php endif; ?>
</td></tr>
