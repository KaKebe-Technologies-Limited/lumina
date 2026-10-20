<?php
/** Offline page (standalone, no layout so the service worker can keep it). Vars: $siteName, $title, $text, $theme, $bg, $home. */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e($siteName) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="<?= e($theme) ?>">
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:1.5rem;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:<?= e($bg) ?>;color:#eef1f7;text-align:center;line-height:1.6}
        main{max-width:30rem}
        img{width:6rem;height:6rem;border-radius:50%;background:#fff;margin-bottom:1rem}
        h1{margin:0 0 .75rem;font-size:clamp(1.6rem,6vw,2.2rem)}
        p{margin:0 0 1.5rem;color:rgba(238,241,247,.88)}
        a{display:inline-block;padding:.8rem 1.5rem;border-radius:99px;background:#d6a151;color:#1b2540;font-weight:700;text-decoration:none}
        a:focus-visible{outline:3px solid #fff;outline-offset:3px}
    </style>
</head>
<body>
<main>
    <img src="<?= e(url('assets/img/pwa/icon-192.png')) ?>" alt="" width="96" height="96">
    <h1><?= e($title) ?></h1>
    <?php if (trim($text) !== ''): ?><p><?= e($text) ?></p><?php endif; ?>
    <a href="<?= e($home) ?>">Try again</a>
</main>
</body>
</html>
