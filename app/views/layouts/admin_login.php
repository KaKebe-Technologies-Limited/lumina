<?php
/** ADMIN LOGIN LAYOUT (login, forced password change). FOUNDATION-OWNED. Receives $content, $pageTitle. */
$siteName = (string) setting('site_name', 'Lumina Africa Foundation');
$logoUrl = logo_url(84);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Sign in') ?> | <?= e($siteName) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="icon" href="<?= e(img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'))) ?>" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&amp;family=Poppins:wght@500;600;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/admin.min.css')) ?>">
    <script src="<?= e(asset('js/boot.js')) ?>"></script>
</head>
<body class="admin admin-login">
<main class="auth" id="main">
    <div class="auth__card">
        <div class="auth__brand">
            <img src="<?= e($logoUrl) ?>" alt="" width="84" height="84">
            <strong><?= e($siteName) ?></strong>
        </div>
        <?= partial('partials/flash') ?>
        <?= $content ?>
    </div>
</main>
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
