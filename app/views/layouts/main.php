<?php
/**
 * PUBLIC LAYOUT. FOUNDATION-OWNED. Receives: $content (page HTML), $meta (page_meta array), $page (slug|null),
 * $breadcrumbs, $bodyClass. Sections a view may define: head (extra <head> markup), scripts (extra <script> tags).
 */
use App\Core\Nav;

$siteName = (string) setting('site_name', 'Lumina Africa Foundation');
$logoUrl = logo_url(60);          // light copy of the logo for the 60 px header / 72 px footer spots
$faviconUrl = img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'));
$tagline = (string) setting('tagline', '');
$gaId = trim((string) setting('analytics_id', ''));

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
    <link rel="apple-touch-icon" href="<?= e(asset('img/brand/logo-192.png')) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,400&amp;family=Poppins:wght@500;600;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/main.min.css')) ?>">
    <script src="<?= e(asset('js/boot.js')) ?>"></script>
    <?= $this->section('head') ?>
    <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <script type="application/ld+json"><?= json_encode($ldSite, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php if ($ldCrumbs !== null): ?><script type="application/ld+json"><?= json_encode($ldCrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script><?php endif; ?>
</head>
<body class="<?= e(trim('has-sticky-header ' . ($page ? 'page-' . $page : '') . ' ' . $bodyClass)) ?>"<?= $gaId !== '' ? ' data-ga-id="' . e($gaId) . '"' : '' ?>>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" data-site-header>
    <div class="site-header__bar">
        <a class="brand" href="<?= e(url('')) ?>">
            <img class="brand__logo" src="<?= e($logoUrl) ?>" alt="" width="60" height="60">
            <span class="brand__text">
                <span class="brand__name"><?= e($siteName) ?></span>
                <?php if ($tagline !== ''): ?><span class="brand__tag"><?= e($tagline) ?></span><?php endif; ?>
            </span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-nav" aria-label="Menu">
            <span class="nav-toggle__bars"></span>
        </button>

        <nav id="site-nav" class="site-nav" data-site-nav aria-label="Main">
            <ul class="site-nav__list">
                <?php foreach (Nav::header() as $item): $hasKids = $item['children'] !== []; ?>
                    <li class="site-nav__item<?= $hasKids ? ' has-children' : '' ?><?= $item['active'] ? ' is-active' : '' ?>">
                        <a class="site-nav__link" href="<?= e($item['url']) ?>"<?= $item['active'] && !$hasKids ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
                        <?php if ($hasKids): ?>
                            <button class="site-nav__sub-toggle" type="button" data-submenu-toggle aria-expanded="false" aria-label="<?= e(str_replace('{label}', (string) $item['label'], (string) content('global', 'labels.submenu_toggle'))) ?>"><?= icon('chevron-down') ?></button>
                            <ul class="site-nav__sub">
                                <?php foreach ($item['children'] as $child): ?>
                                    <li<?= $child['active'] ? ' class="is-active"' : '' ?>><a href="<?= e($child['url']) ?>"<?= $child['active'] ? ' aria-current="page"' : '' ?>><?= e($child['label']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php foreach (Nav::buttons() as $btn): ?>
                <a class="btn btn--accent site-nav__cta" href="<?= e($btn['url']) ?>"><?= icon('heart') ?> <?= e($btn['label']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<?= partial('partials/flash') ?>

<main id="main" class="site-main" tabindex="-1">
    <?= $content ?>
</main>

<?php
$year = date('Y');
$copyright = str_replace('{year}', $year, (string) content('global', 'footer.copyright'));
$regNo = trim((string) setting('reg_number', ''));
$phones = array_filter([(string) setting('phone_1', ''), (string) setting('phone_2', '')]);
$emails = array_filter([(string) setting('email_primary', ''), (string) setting('email_info', '')]);
$wa = whatsapp_url();
$addressLine = implode(', ', array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))]));
?>
<footer class="site-footer">
    <div class="site-footer__grid">
        <div class="site-footer__brand">
            <img class="site-footer__logo" src="<?= e($logoUrl) ?>" alt="" width="72" height="72" loading="lazy">
            <span class="site-footer__name"><?= e($siteName) ?></span>
            <?php if ($tagline !== ''): ?><span class="site-footer__tagline"><?= e($tagline) ?></span><?php endif; ?>
            <?php if ($regNo !== '' && trim((string) content('global', 'footer.reg_label')) !== ''): ?>
                <span class="site-footer__reg"><?= e(content('global', 'footer.reg_label')) ?> <?= e($regNo) ?></span>
            <?php endif; ?>
            <?php if (social_links()): ?>
                <div>
                    <h2 class="visually-hidden"><?= e(content('global', 'footer.social_title')) ?></h2>
                    <ul class="social-list">
                        <?php foreach (social_links() as $s): ?>
                            <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($s['label']) ?>"><?= icon($s['icon']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <nav aria-label="<?= e(content('global', 'footer.links_title')) ?>">
            <h2 class="site-footer__title"><?= e(content('global', 'footer.links_title')) ?></h2>
            <ul class="site-footer__links">
                <?php foreach (Nav::footer() as $f): ?>
                    <li><a href="<?= e($f['url']) ?>"><?= e($f['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div>
            <h2 class="site-footer__title"><?= e(content('global', 'footer.contact_title')) ?></h2>
            <ul class="site-footer__contact">
                <?php if ($addressLine !== ''): ?>
                    <li><?= icon('map-pin') ?><span><?= e($addressLine) ?></span></li>
                <?php endif; ?>
                <?php foreach ($emails as $em): ?>
                    <li><?= icon('mail') ?><a href="mailto:<?= e($em) ?>"><?= e($em) ?></a></li>
                <?php endforeach; ?>
                <?php foreach ($phones as $ph): ?>
                    <li><?= icon('phone') ?><a href="<?= e(phone_href($ph)) ?>"><?= e($ph) ?></a></li>
                <?php endforeach; ?>
                <?php if ($wa !== ''): ?>
                    <li><?= icon('whatsapp') ?><a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="newsletter">
            <h2 class="site-footer__title"><?= e(content('global', 'footer.newsletter_title')) ?></h2>
            <p><?= e(content('global', 'footer.newsletter_text')) ?></p>
            <form class="newsletter__form" method="post" action="<?= e(url('subscribe')) ?>">
                <?= csrf_field() ?>
                <?= honeypot_field() ?>
                <input type="hidden" name="source" value="footer">
                <label class="visually-hidden" for="newsletter-email"><?= e(content('global', 'footer.newsletter_placeholder')) ?></label>
                <input class="input" id="newsletter-email" type="email" name="email" autocomplete="email" required maxlength="190" placeholder="<?= e(content('global', 'footer.newsletter_placeholder')) ?>">
                <button class="btn btn--accent" type="submit"><?= e(content('global', 'footer.newsletter_button')) ?></button>
            </form>
        </div>
    </div>
    <div class="site-footer__bottom">
        <div>
            <span><?= e($copyright) ?></span>
            <?php if (trim((string) setting('footer_credit', '')) !== ''): ?><span><?= e(setting('footer_credit')) ?></span><?php endif; ?>
        </div>
    </div>
</footer>

<button class="back-to-top" type="button" data-back-to-top aria-label="<?= e(content('global', 'footer.back_to_top')) ?>"><?= icon('arrow-up') ?></button>

<script src="<?= e(asset('js/main.js')) ?>" defer></script>
<?= $this->section('scripts') ?>
</body>
</html>
