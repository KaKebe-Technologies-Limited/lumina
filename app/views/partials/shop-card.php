<?php
/**
 * Product card of the catalogue, the related products and the home strips. OWNER: STOREFRONT. Vars: $p (ProductService::hydrate row), $open (shop takes orders),
 * $priority (first screen: the picture is not lazy), $heading (h2|h3, default h3). Every word comes from content('shop', 'catalogue.*').
 * Quick "Add to cart" is offered only for a product without option groups (a size or colour must be chosen on the product page); the form works without JavaScript.
 */
use App\Services\Shop\ShopSettings;

$shOpen = $open ?? ShopSettings::isOpen();
$shBadges = [];
if (in_array('sold_out', $p['badges'], true)) {
    $shBadges[] = ['dark', (string) content('shop', 'catalogue.badge_sold_out')];
} else {
    if ($p['on_sale']) {
        $shBadges[] = ['sale', $p['percent_off'] > 0 ? str_replace('{percent}', (string) $p['percent_off'], (string) content('shop', 'catalogue.percent_off')) : (string) content('shop', 'catalogue.badge_sale')];
    }
    if (in_array('low_stock', $p['badges'], true)) {
        $shBadges[] = ['low', (string) content('shop', 'catalogue.badge_low')];
    }
}
if (in_array('sample', $p['badges'], true)) {
    $shBadges[] = ['sample', (string) content('shop', 'catalogue.badge_sample')];
}
$shTag = ($heading ?? 'h3') === 'h2' ? 'h2' : 'h3';
$shQuick = $shOpen && $p['can_buy'] && $p['options'] === [];
$shHover = $p['hover'] ?? null;
?>
<article class="sh-card<?= in_array('sold_out', $p['badges'], true) ? ' is-soldout' : '' ?>">
    <a class="sh-card__media<?= $shHover ? ' has-hover' : '' ?>" href="<?= e($p['url']) ?>" tabindex="-1" aria-hidden="true">
        <?= partial('partials/shop-image', ['img' => $p['cover'], 'sizes' => '(min-width: 992px) 25vw, (min-width: 576px) 33vw, 50vw', 'class' => 'sh-card__img', 'lazy' => empty($priority), 'alt' => '']) ?>
        <?php if ($shHover): ?><?= partial('partials/shop-image', ['img' => $shHover, 'sizes' => '(min-width: 992px) 25vw, (min-width: 576px) 33vw, 50vw', 'class' => 'sh-card__img sh-card__img--hover', 'alt' => '']) ?><?php endif; ?>
    </a>
    <?php if ($shBadges !== []): ?>
        <ul class="sh-card__badges">
            <?php foreach ($shBadges as [$tone, $text]): ?><li class="sh-badge sh-badge--<?= e($tone) ?>"><?= e($text) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <div class="sh-card__body">
        <?php if (!empty($p['category']['name'])): ?><span class="sh-card__cat"><?= e($p['category']['name']) ?></span><?php endif; ?>
        <<?= $shTag ?> class="sh-card__title"><a href="<?= e($p['url']) ?>"><?= e($p['name']) ?></a></<?= $shTag ?>>
        <p class="sh-price">
            <?php if ($p['on_sale']): ?>
                <span class="sh-price__now"><?= e($p['unit_text']) ?></span>
                <s class="sh-price__was"><span class="visually-hidden"><?= e(content('shop', 'catalogue.was_label')) ?> </span><?= e($p['price_text']) ?></s>
            <?php else: ?>
                <span class="sh-price__now"><?= e($p['unit_text']) ?></span>
            <?php endif; ?>
        </p>
        <div class="sh-card__actions">
            <?php if ($shQuick): ?>
                <form method="post" action="<?= e(url('shop/cart/add')) ?>" data-shop-add data-name="<?= e($p['name']) ?>" data-added-label="<?= e(content('shop', 'catalogue.added_short')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                    <input type="hidden" name="qty" value="1">
                    <button class="btn btn--primary btn--sm btn--caps sh-card__btn" type="submit" name="mode" value="cart"><?= icon('shopping-bag', 'icon--sm') ?> <span><?= e(content('shop', 'catalogue.add_label')) ?></span><span class="visually-hidden"> <?= e($p['name']) ?></span></button>
                </form>
            <?php else: ?>
                <a class="btn btn--outline btn--sm btn--caps sh-card__btn" href="<?= e($p['url']) ?>"><span><?= e(content('shop', !$p['can_buy'] || !$shOpen ? 'catalogue.view_label' : ($p['options'] !== [] ? 'catalogue.options_label' : 'catalogue.view_label'))) ?></span><span class="visually-hidden"> <?= e($p['name']) ?></span></a>
            <?php endif; ?>
        </div>
    </div>
</article>
