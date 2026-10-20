<?php
/**
 * NEWSLETTER E-MAIL (HTML). MAILING agent owns this file and newsletter/email/blocks/*. Rendered by NewsletterRenderer::emailHtml().
 * Rules for mail clients: table layout, inline CSS only, web-safe fonts (Georgia for headings), absolute URLs, no scripts, images with
 * explicit width, a hidden preheader, a "view in browser" link and a per-recipient unsubscribe link in the footer.
 *
 * Vars: $issue (row + content_data), $content, $subject, $preheader, $browser_url, $unsubscribe_url ('' = no link), $is_test, $label,
 * $org_name, $org_address, $footer_text, $reason, $logo_url, $labels (follow, view_in_browser, read_online, unsubscribe, test_notice),
 * $featuredHtml, $blocksHtml, $pdf (null, or for a PDF issue: download_url, view_url, size_text, label - two buttons, the file is NOT attached). Brand colours: navy #2C3950, gold #D6A151, cta #B85A14, ink #1D1E20, soft ink #5A6470, cream #F7F3EC.
 */
use App\Services\Newsletter\NewsletterRenderer as R;

$pdf = $pdf ?? null;
$serif = "Georgia,'Times New Roman',Times,serif";
$sans = "'DM Sans',Arial,Helvetica,sans-serif";
$wrap = 'word-break:break-word;overflow-wrap:anywhere;word-wrap:break-word;';    // a pasted URL or a very long word must wrap, not stretch the 600px card
$cover = (string) ($issue['cover_image'] ?? '');
$coverSize = R::imageSize($cover);
$summary = trim((string) ($issue['summary'] ?? ''));
?>
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="color-scheme" content="light">
<title><?= e($subject) ?></title>
</head>
<body style="margin:0;padding:0;background:#F7F3EC;">
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;"><?= e($preheader) ?>&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F7F3EC;">
<tr><td align="center" style="padding:24px 12px;">
    <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;table-layout:fixed;">
        <?php if ($is_test): ?>
        <tr><td style="padding:0 0 12px 0;font-family:<?= $sans ?>;font-size:13px;line-height:1.5;color:#7a1f12;text-align:center;<?= $wrap ?>"><strong><?= e($labels['test_notice']) ?></strong></td></tr>
        <?php endif; ?>
        <tr><td align="center" style="padding:0 0 12px 0;font-family:<?= $sans ?>;font-size:12px;line-height:1.5;color:#5A6470;<?= $wrap ?>">
            <a href="<?= e($browser_url) ?>" style="color:#5A6470;text-decoration:underline;"><?= e($labels['view_in_browser']) ?></a>
        </td></tr>
        <tr><td style="background:#ffffff;border-radius:16px;overflow:hidden;<?= $wrap ?>">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed;">
                <tr><td style="background:#2C3950;padding:20px 32px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                        <?php if ($logo_url !== ''): ?><td style="padding:0 12px 0 0;vertical-align:middle;"><img src="<?= e($logo_url) ?>" width="40" height="40" alt="" style="display:block;border:0;width:40px;height:40px;"></td><?php endif; ?>
                        <td style="vertical-align:middle;font-family:<?= $serif ?>;font-size:20px;line-height:1.3;color:#ffffff;<?= $wrap ?>"><?= e($org_name) ?></td>
                    </tr></table>
                </td></tr>
                <tr><td style="padding:32px 32px 8px 32px;font-family:<?= $sans ?>;color:#1D1E20;<?= $wrap ?>">
                    <?php if ($label !== ''): ?><p style="margin:0 0 8px 0;font-size:12px;line-height:1.4;letter-spacing:1.5px;text-transform:uppercase;color:#8A5A06;font-weight:bold;"><?= e($label) ?></p><?php endif; ?>
                    <h1 style="margin:0 0 12px 0;font-family:<?= $serif ?>;font-size:30px;line-height:1.2;color:#2C3950;font-weight:bold;<?= $wrap ?>"><?= e((string) $issue['title']) ?></h1>
                    <?php if ($summary !== ''): ?><p style="margin:0 0 8px 0;font-size:17px;line-height:1.6;color:#5A6470;"><?= e($summary) ?></p><?php endif; ?>
                </td></tr>
                <?php if ($cover !== ''): ?>
                <tr><td style="padding:16px 32px 0 32px;">
                    <img src="<?= e(R::imageUrl(['path' => $cover], 'email')) ?>" width="536" alt="<?= e((string) ($issue['cover_alt'] ?? '')) ?>" style="display:block;border:0;width:100%;max-width:536px;height:auto;border-radius:12px;">
                </td></tr>
                <?php endif; ?>
                <?= $featuredHtml ?>
                <?= $blocksHtml ?>
                <?php if ($pdf !== null): ?>
                <tr><td align="center" style="padding:16px 32px 0 32px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" bgcolor="#B85A14" style="border-radius:999px;background:#B85A14;">
                        <a href="<?= e($pdf['download_url']) ?>" style="display:inline-block;padding:13px 28px;font-family:<?= $sans ?>;font-size:15px;line-height:1.2;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:999px;max-width:100%;<?= $wrap ?>"><?= e($pdf['label']) ?></a>
                    </td></tr></table>
                    <?php if ($pdf['size_text'] !== ''): ?><p style="margin:8px 0 0 0;font-family:<?= $sans ?>;font-size:12px;line-height:1.5;color:#5A6470;<?= $wrap ?>"><?= e($pdf['size_text']) ?></p><?php endif; ?>
                </td></tr>
                <tr><td align="center" style="padding:16px 32px 32px 32px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="border-radius:999px;border:2px solid #2C3950;">
                        <a href="<?= e($browser_url) ?>" style="display:inline-block;padding:11px 26px;font-family:<?= $sans ?>;font-size:15px;line-height:1.2;font-weight:bold;color:#2C3950;text-decoration:none;border-radius:999px;max-width:100%;<?= $wrap ?>"><?= e($labels['read_online']) ?></a>
                    </td></tr></table>
                </td></tr>
                <?php else: ?>
                <tr><td align="center" style="padding:16px 32px 32px 32px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" bgcolor="#B85A14" style="border-radius:999px;background:#B85A14;">
                        <a href="<?= e($browser_url) ?>" style="display:inline-block;padding:13px 28px;font-family:<?= $sans ?>;font-size:15px;line-height:1.2;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:999px;max-width:100%;<?= $wrap ?>"><?= e($labels['read_online']) ?></a>
                    </td></tr></table>
                </td></tr>
                <?php endif; ?>
            </table>
        </td></tr>
        <?php $nlSocial = function_exists('social_links') ? social_links('newsletter') : []; ?>
        <?php if ($nlSocial): ?>
        <tr><td align="center" style="padding:20px 24px 0 24px;font-family:<?= $sans ?>;font-size:13px;line-height:1.8;color:#5A6470;<?= $wrap ?>">
            <?php if (trim((string) ($labels['follow'] ?? '')) !== ''): ?><span style="color:#2C3950;font-weight:bold;"><?= e($labels['follow']) ?></span><?php endif; ?>
            <?php foreach ($nlSocial as $i => $s): ?><?= $i > 0 ? ' &middot; ' : ' ' ?><a href="<?= e($s['url']) ?>" style="color:#2C3950;text-decoration:underline;"><?= e($s['label']) ?></a><?php endforeach; ?>
        </td></tr>
        <?php endif; ?>
        <tr><td align="center" style="padding:20px 24px 8px 24px;font-family:<?= $sans ?>;font-size:12px;line-height:1.6;color:#5A6470;<?= $wrap ?>">
            <?php if ($footer_text !== ''): ?><p style="margin:0 0 10px 0;"><?= nl2br(e($footer_text)) ?></p><?php endif; ?>
            <p style="margin:0 0 10px 0;"><?= e($reason) ?></p>
            <?php if ($org_address !== ''): ?><p style="margin:0 0 10px 0;"><?= e($org_name) ?> &middot; <?= e($org_address) ?></p><?php endif; ?>
            <?php if ($unsubscribe_url !== ''): ?><p style="margin:0;"><a href="<?= e($unsubscribe_url) ?>" style="color:#5A6470;text-decoration:underline;"><?= e($labels['unsubscribe']) ?></a></p>
            <?php elseif ($is_test): ?><p style="margin:0;color:#5A6470;"><?= e($labels['unsubscribe']) ?></p><?php endif; ?>
        </td></tr>
    </table>
    <!--[if mso]></td></tr></table><![endif]-->
</td></tr>
</table>
</body>
</html>
