<?php
/**
 * PUBLIC LAYOUT. FOUNDATION-OWNED. Receives: $content (page HTML), $meta (page_meta array), $page (slug|null),
 * $breadcrumbs, $bodyClass. Sections a view may define: head (extra <head> markup), scripts (extra <script> tags).
 */
use App\Core\Nav;

$siteName = (string) setting('site_name', 'Lumina Africa Foundation');
$faviconUrl = img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'));
$tagline = (string) setting('tagline', '');
$gaId = analytics_id();

$sameAs = array_values(array_map(static fn(array $s): string => $s['url'], social_links()));
$ld = array_filter([
    '@context'    => 'https://schema.org',
    '@type'       => 'NGO',
    'name'        => $siteName,
    'url'         => abs_url(''),
    'logo'        => abs_url_for_image((string) setting('logo_path', '')),
    'description' => (string) setting('meta_description', ''),
    'slogan'      => $tagline,
    'email'       => (string) setting('email_primary', ''),
    'telephone'   => (string) setting('phone_1', ''),
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => (string) setting('address', ''),
        'addressLocality' => (string) setting('city_country', ''),
        'addressCountry'  => 'UG',
    ],
    'sameAs'      => $sameAs,
    'founder'     => (string) setting('founder_name', '') !== '' ? ['@type' => 'Person', 'name' => (string) setting('founder_name')] : null,
]);

$ldSite = ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $siteName, 'url' => abs_url('')];
$ldCrumbs = null;
if (!empty($breadcrumbs) && is_array($breadcrumbs)) {
    $items = [];
    $trail = $breadcrumbs;
    array_unshift($trail, [(string) content('global', 'labels.home_breadcrumb'), url('')]);      // same Home first item as partials/breadcrumbs
    foreach (array_values($trail) as $i => $c) {
        $label = trim((string) ($c[0] ?? $c['label'] ?? ''));
        $href = (string) ($c[1] ?? $c['url'] ?? '');
        if ($label === '') {
            continue;
        }
        $abs = $href === '' ? (string) $meta['canonical'] : (preg_match('~^https?://~i', $href) ? $href : origin() . (str_starts_with($href, '/') ? '' : '/') . $href);
        $items[] = ['@type' => 'ListItem', 'position' => count($items) + 1, 'name' => $label, 'item' => $abs];
    }
    if (count($items) >= 2) {
        $ldCrumbs = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($meta['full_title']) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <meta name="robots" content="<?= e($meta['robots']) ?>">
    <link rel="canonical" href="<?= e($meta['canonical']) ?>">
    <meta name="theme-color" content="#2C3950">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="base-url" content="<?= e(url('')) ?>">

    <meta property="og:type" content="<?= e($meta['og_type']) ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:locale" content="en_GB">
    <meta property="og:title" content="<?= e($meta['title']) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:url" content="<?= e($meta['canonical']) ?>">
    <?php if ($meta['og_image'] !== ''): ?><meta property="og:image" content="<?= e($meta['og_image']) ?>"><meta property="og:image:alt" content="<?= e($siteName) ?>"><?php endif; ?>

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($meta['title']) ?>">
    <meta name="twitter:description" content="<?= e($meta['description']) ?>">
    <?php if ($meta['og_image'] !== ''): ?><meta name="twitter:image" content="<?= e($meta['og_image']) ?>"><?php endif; ?>

    <?php if ($faviconUrl !== ''): ?><link rel="icon" href="<?= e($faviconUrl) ?>" sizes="32x32"><?php endif; ?>
    <link rel="apple-touch-icon" href="<?= e(asset('img/pwa/apple-touch-icon.png')) ?>">
<?php if ((string) setting('pwa_enabled', '1') === '1'): ?>
    <link rel="manifest" href="<?= e(url('manifest.webmanifest')) ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="<?= e((string) setting('pwa_short_name', 'Lumina')) ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&amp;family=Fraunces:wght@500;600;700&amp;family=Poppins:wght@500;600;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/main.min.css')) ?>">
    <script src="<?= e(asset('js/boot.js')) ?>"></script>
    <?= $this->section('head') ?>
    <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <script type="application/ld+json"><?= json_encode($ldSite, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php if ($ldCrumbs !== null): ?><script type="application/ld+json"><?= json_encode($ldCrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script><?php endif; ?>
</head>
<body class="<?= e(trim('has-sticky-header ' . ($page ? 'page-' . $page : '') . ' ' . $bodyClass)) ?>"<?= $gaId !== '' ? ' data-ga-id="' . e($gaId) . '"' : '' ?>>
<a class="skip-link" href="#main">Skip to content</a>

<?= partial('partials/topbar') ?>
<?= partial('partials/site-header') ?>

<?= partial('partials/flash') ?>

<main id="main" class="site-main" tabindex="-1">
    <?= $content ?>
</main>

<?= partial('partials/site-footer') ?>

<button class="back-to-top" type="button" data-back-to-top aria-label="<?= e(content('global', 'footer.back_to_top')) ?>"><?= icon('arrow-up') ?></button>

<?= partial('partials/pwa-install') ?>
<script src="<?= e(asset('js/main.js')) ?>" defer></script>
<script src="<?= e(asset('js/pwa.js')) ?>" defer></script>
<?= $this->section('scripts') ?>
</body>
</html>
