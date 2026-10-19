<?php
/**
 * ADMIN LAYOUT. FOUNDATION-OWNED. Receives $content, $pageTitle (render() 'title' option), $navKey (render() 'nav' option),
 * $breadcrumbs (list of [label, url|null] shown after "Dashboard"). Sections: head, scripts, actions (extra topbar buttons).
 * The sidebar is built from app/config/admin_nav/*.php fragments (App\Core\AdminNav) - never edit this file to add a menu item.
 */
use App\Core\AdminNav;

$user = current_user();
$siteName = (string) setting('site_name', 'Lumina Africa Foundation');
$title = (string) ($pageTitle ?? ($meta['title'] ?? 'Admin'));
$groups = AdminNav::groups($navKey ?? null);
$logoUrl = logo_url(38);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | Admin | <?= e($siteName) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="base-url" content="<?= e(url('')) ?>">
    <link rel="icon" href="<?= e(img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'))) ?>" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700&amp;family=Poppins:wght@500;600;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/admin.min.css')) ?>">
    <script src="<?= e(asset('js/boot.js')) ?>"></script>
    <?= $this->section('head') ?>
</head>
<body class="admin">
<a class="skip-link" href="#main">Skip to content</a>
<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin navigation">
        <a class="admin-sidebar__brand" href="<?= e(url('admin')) ?>">
            <img src="<?= e($logoUrl) ?>" alt="" width="38" height="38">
            <span><strong><?= e($siteName) ?></strong><small>Admin</small></span>
        </a>
        <nav class="admin-sidebar__nav" aria-label="Admin sections">
            <?php foreach ($groups as $g): ?>
                <div class="admin-nav__group">
                    <?php if ($g['label'] !== ''): ?><div class="admin-nav__heading"><?= e($g['label']) ?></div><?php endif; ?>
                    <ul class="admin-nav__list">
                        <?php foreach ($g['items'] as $it): ?>
                            <li>
                                <a class="admin-nav__link<?= $it['active'] ? ' is-active' : '' ?>" href="<?= e($it['url']) ?>"<?= $it['active'] ? ' aria-current="page"' : '' ?>>
                                    <?= icon($it['icon']) ?>
                                    <span><?= e($it['label']) ?></span>
                                    <?php if ($it['badge'] !== null): ?><span class="admin-nav__badge"><?= e($it['badge']) ?></span><?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar__user">
            <strong><?= e($user['name'] ?? '') ?></strong>
            <span><?= e($user['role'] ?? '') ?></span>
            <form method="post" action="<?= e(url('admin/logout')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn--outline-light btn--sm btn--block" type="submit"><?= icon('log-out', 'icon--sm') ?> Sign out</button>
            </form>
        </div>
    </aside>
    <div class="admin-backdrop" data-sidebar-toggle></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-topbar__toggle" type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-label="Menu"><?= icon('menu') ?></button>
            <span class="admin-topbar__title"><?= e($title) ?></span>
            <span class="admin-topbar__spacer"></span>
            <div class="admin-topbar__actions">
                <?= $this->section('actions') ?>
                <a class="btn btn--outline btn--sm" href="<?= e(url('')) ?>" target="_blank" rel="noopener"><?= icon('external-link', 'icon--sm') ?> View site</a>
            </div>
        </header>

        <main id="main" class="admin-content" tabindex="-1">
            <?php if (\App\Core\Maintenance::isOn() && can('settings.manage')): ?>
                <div class="mt-banner" role="status"><?= icon('clock', 'icon--sm') ?> <span><strong>Your website is switched OFF</strong> — visitors see the maintenance page.</span> <a class="btn btn--sm btn--outline" href="<?= e(url('admin/maintenance')) ?>">Turn it back on</a></div>
            <?php endif; ?>
            <?php if (!empty($breadcrumbs)): ?>
                <?= partial('partials/breadcrumbs', ['breadcrumbs' => array_merge([['Dashboard', url('admin')]], $breadcrumbs), 'home' => false]) ?>
            <?php endif; ?>
            <?= partial('partials/flash') ?>
            <?= $content ?>
        </main>
        <footer class="admin-footer"><?= e($siteName) ?> admin</footer>
    </div>
</div>
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
<?= $this->section('scripts') ?>
</body>
</html>
