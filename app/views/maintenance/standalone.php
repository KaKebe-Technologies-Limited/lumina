<?php
/**
 * Visitor "website is closed for maintenance" page: standalone HTML (no site layout, no menus), so it works even if parts of the site are down.
 * Vars: $siteName, $tagline, $title, $message, $backAt (unix|null), $backText, $contact (array|[]), $socials (list), $logo, $preview (bool).
 */
$ic = static function (string $name): string {
    $p = [
        'mail'  => '<path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1z"/><path d="m3.5 7.5 8.5 6 8.5-6"/>',
        'phone' => '<path d="M5 4h3l2 5-2.5 1.5a11 11 0 0 0 6 6L15 14l5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
        'chat'  => '<path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1h-7l-5 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1z"/>',
        'pin'   => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
    ][$name] ?? '';
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p . '</svg>';
};
$c = is_array($contact ?? null) ? $contact : [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e($siteName) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="<?= e(excerpt($message !== '' ? $message : $title, 150)) ?>">
    <meta name="theme-color" content="#1f2a44">
    <link rel="icon" href="<?= e(img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'))) ?>" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&amp;family=Fraunces:wght@600;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/maintenance.css')) ?>">
</head>
<body class="mt">
<div class="mt__bg" aria-hidden="true"><span class="mt__orb mt__orb--a"></span><span class="mt__orb mt__orb--b"></span><span class="mt__orb mt__orb--c"></span><span class="mt__grid"></span></div>

<?php if (!empty($preview)): ?>
    <div class="mt__preview" role="status">Preview — this is how visitors see your website while it is switched off. <a href="<?= e(url('admin/maintenance')) ?>">Back to Website on/off</a></div>
<?php endif; ?>

<main class="mt__wrap" id="main">
    <section class="mt__card" aria-labelledby="mt-title">
        <div class="mt__logo"><span class="mt__ring" aria-hidden="true"></span><img src="<?= e($logo) ?>" alt="<?= e($siteName) ?> logo" width="120" height="120"></div>
<?php $mtNow = new DateTimeImmutable('now', new DateTimeZone('Africa/Kampala')); ?>
        <p class="mt__clock" data-mt-clock data-tz="Africa/Kampala" role="timer" aria-live="off" aria-label="Current date and time in Uganda">
            <span class="mt__clock-date" data-mt-date><?= e($mtNow->format('l, j F Y')) ?></span>
            <span class="mt__clock-time" data-mt-time><?= e($mtNow->format('H:i:s')) ?></span>
            <span class="mt__clock-tz">EAT · Uganda time</span>
        </p>
        <p class="mt__eyebrow"><?= e($siteName) ?></p>
        <h1 class="mt__title" id="mt-title"><?= e($title) ?></h1>
        <?php if ($message !== ''): ?><p class="mt__text"><?= nl2br(e($message), false) ?></p><?php endif; ?>

        <div class="mt__bar" role="presentation" aria-hidden="true"><span></span></div>
        <p class="mt__status"><span class="mt__dot" aria-hidden="true"></span> Improvements in progress</p>

        <?php if ($backAt !== null && $backAt > time()): ?>
            <div class="mt__count" data-mt-countdown="<?= (int) $backAt ?>" aria-live="off">
                <p class="mt__count-label">Expected back <strong><?= e($backText) ?></strong></p>
                <ul class="mt__units" aria-hidden="true">
                    <li><b data-u="d">--</b><span>days</span></li><li><b data-u="h">--</b><span>hours</span></li><li><b data-u="m">--</b><span>minutes</span></li><li><b data-u="s">--</b><span>seconds</span></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (array_filter($c)): ?>
            <div class="mt__contact">
                <h2 class="mt__h2">Need us in the meantime?</h2>
                <ul class="mt__list">
                    <?php if (($c['email'] ?? '') !== ''): ?><li><a href="mailto:<?= e($c['email']) ?>"><?= $ic('mail') ?><span><?= e($c['email']) ?></span></a></li><?php endif; ?>
                    <?php if (($c['phone'] ?? '') !== ''): ?><li><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $c['phone']) ?? '') ?>"><?= $ic('phone') ?><span><?= e($c['phone']) ?></span></a></li><?php endif; ?>
                    <?php if (($c['whatsapp'] ?? '') !== ''): ?><li><a href="<?= e($c['whatsapp']) ?>" rel="noopener noreferrer" target="_blank"><?= $ic('chat') ?><span>Chat on WhatsApp</span></a></li><?php endif; ?>
                    <?php if (($c['address'] ?? '') !== ''): ?><li class="mt__addr"><?= $ic('pin') ?><span><?= e($c['address']) ?></span></li><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($socials)): ?>
            <ul class="mt__social" aria-label="Follow us">
                <?php foreach ($socials as $s): ?><li><a href="<?= e($s['url']) ?>" rel="noopener noreferrer" target="_blank"><?= e($s['label']) ?></a></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($tagline !== ''): ?><p class="mt__tag"><?= e($tagline) ?></p><?php endif; ?>
    </section>
    <p class="mt__foot">&copy; <?= e(date('Y')) ?> <?= e($siteName) ?></p>
</main>
<script src="<?= e(asset('js/maintenance.js')) ?>" defer></script>
</body>
</html>
