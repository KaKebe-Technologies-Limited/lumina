<?php
declare(strict_types=1);
/**
 * The pre-launch page (app/core/PreLaunch.php): the whole website is closed until the launch moment, so the visitor gets ONLY the launch pop-up, kept open, with its big countdown.
 * Standalone HTML (no site header, menu or footer). At zero the script reloads the page and the site opens. No variables.
 */
$siteName = (string) setting('site_name', 'Lumina Africa Foundation');
?>
<!doctype html>
<html lang="en" class="launch-open launch-gated">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Launching soon | <?= e($siteName) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#0B1B33">
    <link rel="icon" href="<?= e(img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'))) ?>" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&amp;family=Fraunces:wght@600;700;800&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/main.min.css')) ?>">
    <script src="<?= e(asset('js/boot.js')) ?>"></script>
</head>
<body class="launch-gate">
<?= partial('partials/launch-popup', ['gate' => true]) ?>
<noscript><p style="position:fixed;left:0;right:0;bottom:0;margin:0;padding:1rem;text-align:center;background:#0B1B33;color:#fff;font-family:sans-serif">The <?= e($siteName) ?> website opens at 10:00 AM (East Africa Time) on 6 November 2026.</p></noscript>
</body>
</html>
