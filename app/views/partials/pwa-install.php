<?php
/**
 * Install-as-app UI (Admin > Settings > Install as an app). Vars: $part = 'button' (footer) or 'ui' (banner + iPhone dialog + script root).
 * Everything starts hidden; assets/js/pwa.js reveals it only where installing is possible.
 */
if ((string) setting('pwa_enabled', '1') !== '1') {
    return;
}
$label = (string) setting('pwa_install_label', 'Install the app');
if (($part ?? 'ui') === 'button') {
    if ((string) setting('pwa_install_button', '1') === '1') {
        echo '<button class="pwa-install-btn" type="button" data-pwa-install hidden>' . icon('download', 'icon--sm') . ' ' . e($label) . '</button>';
    }
    return;
}
?>
<div data-pwa data-sw="<?= e(url('sw.js')) ?>" data-scope="<?= e(base_path() . '/') ?>" hidden></div>
<?php if ((string) setting('pwa_install_banner', '0') === '1'): ?>
    <div class="pwa-banner" data-pwa-banner role="region" aria-label="<?= e($label) ?>" hidden>
        <p class="pwa-banner__text"><?= e((string) setting('pwa_banner_text', '')) ?></p>
        <button class="pwa-banner__go" type="button" data-pwa-go><?= e($label) ?></button>
        <button class="pwa-banner__x" type="button" data-pwa-dismiss>Not now</button>
    </div>
<?php endif; ?>
<dialog class="pwa-dialog" data-pwa-ios aria-label="<?= e($label) ?>">
    <p><?= e((string) setting('pwa_ios_text', '')) ?></p>
    <button type="button" data-pwa-ios-close>OK</button>
</dialog>
