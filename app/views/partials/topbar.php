<?php
/**
 * Thin gold strip above the header: address (left), e-mail + phone + quick link (right). Data comes from Settings (contact details),
 * the strip itself and its quick link from the registry page "Site-wide content" > Top bar (switch it off to hide it everywhere).
 * No variables.
 */
if (!section_on('global', 'topbar')) {
    return;
}
$tbAddress = implode(', ', array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))]));
$tbEmail = trim((string) setting('email_primary', ''));
$tbPhone = trim((string) setting('phone_1', ''));
$tbShowAddress = (bool) content('global', 'topbar.show_address') && $tbAddress !== '';
$tbShowEmail = (bool) content('global', 'topbar.show_email') && $tbEmail !== '';
$tbShowPhone = (bool) content('global', 'topbar.show_phone') && $tbPhone !== '';
$tbType = (string) content('global', 'topbar.link_type');
$tbLabel = trim((string) content('global', 'topbar.link_label'));
$tbHref = '';
$tbIcon = 'heart';
if ($tbType === 'whatsapp') {
    $tbHref = whatsapp_url();
    $tbIcon = 'whatsapp';
} elseif ($tbType === 'donate') {
    $tbHref = url('donate');
}
$tbShowLink = $tbHref !== '' && $tbLabel !== '';
if (!$tbShowAddress && !$tbShowEmail && !$tbShowPhone && !$tbShowLink) {
    return;
}
?>
<div class="topbar">
    <div class="topbar__inner">
        <div class="topbar__group">
            <?php if ($tbShowAddress): ?><span class="topbar__item topbar__item--address"><?= icon('map-pin') ?><span><?= e($tbAddress) ?></span></span><?php endif; ?>
        </div>
        <div class="topbar__group">
            <?php if ($tbShowEmail): ?><a class="topbar__item topbar__item--email" href="mailto:<?= e($tbEmail) ?>"><?= icon('mail') ?><span><?= e($tbEmail) ?></span></a><?php endif; ?>
            <?php if ($tbShowPhone): ?><a class="topbar__item topbar__item--phone" href="<?= e(phone_href($tbPhone)) ?>"><?= icon('phone') ?><span><?= e($tbPhone) ?></span></a><?php endif; ?>
            <?php if ($tbShowLink): ?><a class="topbar__item topbar__item--link" href="<?= e($tbHref) ?>"<?= is_external($tbHref) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= icon($tbIcon) ?><span><?= e($tbLabel) ?></span></a><?php endif; ?>
        </div>
    </div>
</div>
