<?php
/**
 * Sticky site header + off-canvas mobile drawer (rendered by layouts/main.php). No variables.
 * Menu: Nav::header() (pages table), Donate pill(s): Nav::buttons(). Behaviour: assets/js/main.js ([data-site-header] [data-nav-toggle]
 * [data-site-nav] [data-nav-close] [data-nav-backdrop] [data-submenu-toggle]).
 */

use App\Core\Nav;

$hdName = (string) setting('site_name', 'Lumina Africa Foundation');
$hdTag = (string) setting('tagline', '');
$hdButtons = Nav::buttons();
$hdPhone = trim((string) setting('phone_1', ''));
$hdEmail = trim((string) setting('email_primary', ''));
$hdMenu = (string) content('global', 'labels.menu_open');
$hdClose = (string) content('global', 'labels.menu_close');
// shop: a small cart link, shown only once something is in the cart (CartService::units() reads the session only if it already exists; it never creates one)
$hdShopOn = \App\Core\SitePages::isPublished('shop');
$hdCartUnits = $hdShopOn ? \App\Services\Shop\CartService::units() : 0;
?>
<header class="site-header" data-site-header>
    <div class="site-header__bar">
        <a class="brand" href="<?= e(url('')) ?>">
            <img class="brand__logo" src="<?= e(logo_url(60)) ?>" alt="" width="58" height="58">
            <span class="brand__text">
                <span class="brand__name"><?= e($hdName) ?></span>
                <?php if ($hdTag !== ''): ?><span class="brand__tag"><?= e($hdTag) ?></span><?php endif; ?>
            </span>
        </a>

        <nav id="site-nav" class="site-nav" data-site-nav aria-label="Main">
            <div class="site-nav__head">
                <span class="site-nav__title"><?= e((string) content('global', 'labels.drawer_title')) ?></span>
                <button class="site-nav__close" type="button" data-nav-close aria-label="<?= e($hdClose) ?>"><?= icon('x') ?></button>
            </div>
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
            <div class="site-nav__foot">
                <?php foreach ($hdButtons as $btn): ?>
                    <a class="btn btn--cta btn--caps btn--block site-nav__foot-cta" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a>
                    <?php if (!empty($btn['children'])): ?>
                        <ul class="site-nav__foot-links" aria-label="<?= e($btn['label']) ?>">
                            <?php foreach ($btn['children'] as $kid): ?><li><a href="<?= e($kid['url']) ?>"<?= $kid['active'] ? ' aria-current="page"' : '' ?>><?= e($kid['label']) ?></a></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($hdPhone !== '' || $hdEmail !== ''): ?>
                    <div class="site-nav__contact">
                        <?php if ($hdPhone !== ''): ?><a href="<?= e(phone_href($hdPhone)) ?>"><?= icon('phone') ?><?= e($hdPhone) ?></a><?php endif; ?>
                        <?php if ($hdEmail !== ''): ?><a href="mailto:<?= e($hdEmail) ?>"><?= icon('mail') ?><?= e($hdEmail) ?></a><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </nav>

        <div class="site-header__actions">
            <?php if ($hdShopOn): ?>
                <a class="site-header__cart" href="<?= e(url('shop/cart')) ?>" data-shop-cart-link data-aria="<?= e(content('shop', 'cart.header_aria')) ?>" data-aria-one="<?= e(content('shop', 'cart.header_aria_one')) ?>" aria-label="<?= e(str_replace('{n}', (string) $hdCartUnits, (string) content('shop', $hdCartUnits === 1 ? 'cart.header_aria_one' : 'cart.header_aria'))) ?>"<?= $hdCartUnits > 0 ? '' : ' hidden' ?>><?= icon('shopping-bag') ?><span class="site-header__cart-count" data-shop-count><?= (int) $hdCartUnits ?></span></a>
            <?php endif; ?>
            <?php foreach ($hdButtons as $btn): ?>
                <?php if (!empty($btn['children'])): ?>
                    <?php /* a button entry that has child pages (e.g. Shop under Donate): the pill opens a small list on hover / keyboard focus; the drawer lists them too */ ?>
                    <div class="site-header__cta-group">
                        <a class="btn btn--primary btn--caps site-header__cta" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a>
                        <ul class="site-header__cta-sub" aria-label="<?= e($btn['label']) ?>">
                            <?php foreach ($btn['children'] as $kid): ?><li><a href="<?= e($kid['url']) ?>"<?= $kid['active'] ? ' aria-current="page"' : '' ?>><?= e($kid['label']) ?></a></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn btn--primary btn--caps site-header__cta" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
            <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-nav" aria-label="<?= e($hdMenu) ?>">
                <span class="nav-toggle__bars"></span>
            </button>
        </div>
    </div>
    <div class="nav-backdrop" data-nav-backdrop hidden></div>
</header>
