<?php
/** E-mail: button block (bulletproof table button). Vars: $block, $issue, $context ('email'). Returns a table row. */
use App\Services\Newsletter\NewsletterRenderer as R;

$sans = "'DM Sans',Arial,Helvetica,sans-serif";
$wrap = 'word-break:break-word;overflow-wrap:anywhere;word-wrap:break-word;';    // a pasted URL or a very long word must wrap, not stretch the 600px card
$b = $block['button'];
$href = R::href($b['url'], 'email');
if ($b['label'] === '' || $href === '') {
    return;
}
$outline = $b['style'] === 'outline';
?>
<tr><td style="padding:20px 32px 4px 32px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
        <td align="center" bgcolor="<?= $outline ? '#ffffff' : '#D6A151' ?>" style="border-radius:999px;background:<?= $outline ? '#ffffff' : '#D6A151' ?>;<?= $outline ? 'border:2px solid #2C3950;' : '' ?><?= $wrap ?>">
            <a href="<?= e($href) ?>" style="display:inline-block;padding:12px 26px;font-family:<?= $sans ?>;font-size:15px;line-height:1.2;font-weight:bold;color:<?= $outline ? '#2C3950' : '#172036' ?>;text-decoration:none;border-radius:999px;max-width:100%;<?= $wrap ?>"><?= e($b['label']) ?></a>
        </td>
    </tr></table>
</td></tr>
