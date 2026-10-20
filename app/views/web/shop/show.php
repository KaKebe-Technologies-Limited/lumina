<?php
/**
 * PRODUCT page. OWNER: STOREFRONT. Vars: $product (ProductService::hydrate row), $related, $open.
 * The add-to-cart form posts to shop/cart/add and works without JavaScript; shop.js adds the gallery, the quantity buttons and the "added" message.
 * Option choices, stock, price and the limits come from ProductService / ShopSettings; every word from content('shop', ...).
 */
use App\Services\Shop\Money;
use App\Services\Shop\ProductService;
use App\Services\Shop\ShopSettings;
use App\Services\Web\ShareLinks;

$p = $product;
$maxQty = ShopSettings::maxQty();
$stock = $p['stock'];
$images = $p['images'];
$isSample = in_array('sample', $p['badges'], true);
$soldOut = $stock['state'] === 'out';
if ($stock['tracked'] && $stock['qty'] !== null) {
    $maxQty = max(1, min($maxQty, (int) $stock['qty']));
}
$absUrl = abs_url('shop/' . $p['slug']);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/shop.js')) ?>" defer></script>
<?php $this->end();

// schema.org Product for search engines: never for a SAMPLE (its price is a placeholder)
if (!$isSample) {
    $dec = Money::decimals($p['currency']);
    $priceNum = number_format($p['unit_minor'] / 100, $dec, '.', '');
    $offer = [
        '@type'         => 'Offer',
        'url'           => $absUrl,
        'priceCurrency' => $p['currency'],
        'price'         => $priceNum,
        'availability'  => $soldOut ? 'https://schema.org/OutOfStock' : ($stock['state'] === 'low' ? 'https://schema.org/LimitedAvailability' : 'https://schema.org/InStock'),
    ];
    if ($p['on_sale'] && $p['sale_ends']) {
        $offer['priceValidUntil'] = substr((string) $p['sale_ends'], 0, 10);
    }
    $ld = array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => (string) $p['name'],
        'description' => excerpt((string) ($p['summary'] ?: ($p['description'] ?? '')), 300),
        'sku'         => (string) ($p['sku'] ?? ''),
        'image'       => array_values(array_filter(array_map(static fn(array $i): string => abs_url_for_image((string) $i['path']), $images))),
        'offers'      => $offer,
    ], static fn($v): bool => $v !== '' && $v !== []);
    $this->start('head');
    echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . "</script>\n";
    $this->end();
}
$statusKey = $soldOut ? 'out_of_stock' : ($stock['state'] === 'low' ? 'low_stock' : ($stock['tracked'] ? 'in_stock' : 'made_to_order'));
$statusText = str_replace('{n}', (string) ($stock['qty'] ?? ''), (string) content('shop', 'product.' . $statusKey));
$badgeSale = $p['on_sale'] ? ($p['percent_off'] > 0 ? str_replace('{percent}', (string) $p['percent_off'], (string) content('shop', 'catalogue.percent_off')) : (string) content('shop', 'catalogue.badge_sale')) : '';
$groups = $p['options'];
$share = ShareLinks::for($absUrl, (string) $p['name']);
?>
<section class="section sh-product" aria-labelledby="sh-title">
    <div class="container">
        <div class="sh-product__top">
            <?= partial('partials/breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]) ?>
        </div>

        <div class="sh-product__layout" data-no-justify>
            <div class="sh-gallery" data-shop-gallery role="group" aria-label="<?= e(content('shop', 'product.gallery_label')) ?>">
                <?php if ($images === []): ?>
                    <div class="sh-gallery__stage sh-gallery__stage--empty" aria-hidden="true"><?= icon('shopping-bag', 'icon--xl') ?></div>
                <?php else: ?>
                    <div class="sh-gallery__stage">
                        <div class="sh-gallery__frame" data-shop-stage>
                            <?= partial('partials/shop-image', ['img' => $images[0], 'sizes' => '(min-width: 992px) 46vw, 100vw', 'class' => 'sh-gallery__img', 'lazy' => false, 'attrs' => ['data-shop-main' => true]]) ?>
                        </div>
                        <?php if ($badgeSale !== '' || $soldOut): ?><span class="sh-badge sh-badge--<?= $soldOut ? 'dark' : 'sale' ?> sh-gallery__badge"><?= e($soldOut ? content('shop', 'catalogue.badge_sold_out') : $badgeSale) ?></span><?php endif; ?>
                        <button class="sh-gallery__zoom" type="button" data-shop-zoom hidden aria-label="<?= e(content('shop', 'product.zoom_label')) ?>"><?= icon('search', 'icon--sm') ?></button>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <ul class="sh-gallery__thumbs">
                            <?php foreach ($images as $i => $img): $full = $img['src2x'] !== '' ? $img['src2x'] : $img['src']; ?>
                                <li>
                                    <a class="sh-gallery__thumb<?= $i === 0 ? ' is-current' : '' ?>" href="<?= e($full) ?>" data-shop-thumb data-lightbox="shop-product" data-caption="<?= e($img['alt']) ?>"
                                       data-src="<?= e($img['src']) ?>" data-srcset="<?= e($img['srcset']) ?>" data-webp="<?= e($img['webp_srcset']) ?>" data-alt="<?= e($img['alt']) ?>"
                                       aria-label="<?= e(str_replace('{n}', (string) ($i + 1), (string) content('shop', 'product.thumb_label'))) ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>>
                                        <?= partial('partials/shop-image', ['img' => $img, 'sizes' => '84px', 'class' => 'sh-gallery__thumb-img', 'alt' => '']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <a class="sh-gallery__single" href="<?= e($images[0]['src2x'] !== '' ? $images[0]['src2x'] : $images[0]['src']) ?>" data-lightbox="shop-product" data-caption="<?= e($images[0]['alt']) ?>" data-shop-thumb data-shop-single hidden tabindex="-1" aria-hidden="true"></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="sh-buy">
                <?php if (!empty($p['category']['name'])): ?><a class="sh-buy__cat" href="<?= e(url('shop/category/' . $p['category']['slug'])) ?>"><?= e($p['category']['name']) ?></a><?php endif; ?>
                <h1 class="sh-buy__title" id="sh-title"><?= e($p['name']) ?></h1>
                <p class="sh-price sh-price--lg">
                    <span class="sh-price__now"><?= e($p['unit_text']) ?></span>
                    <?php if ($p['on_sale']): ?><s class="sh-price__was"><span class="visually-hidden"><?= e(content('shop', 'catalogue.was_label')) ?> </span><?= e($p['price_text']) ?></s><span class="sh-badge sh-badge--sale"><?= e($badgeSale) ?></span><?php endif; ?>
                </p>
                <p class="sh-stock sh-stock--<?= e($stock['state']) ?>"><span class="sh-stock__dot" aria-hidden="true"></span><?= e($statusText) ?></p>
                <?php if (trim((string) ($p['summary'] ?? '')) !== ''): ?><p class="sh-buy__summary"><?= e($p['summary']) ?></p><?php endif; ?>
                <?php if ($isSample): ?><p class="sh-note"><?= icon('info', 'icon--sm') ?> <span><?= e(content('shop', 'product.sample_note')) ?></span></p><?php endif; ?>

                <?php if (!$open): ?>
                    <div class="sh-closed sh-closed--inline" role="status"><span class="sh-closed__icon"><?= icon('clock') ?></span><div class="sh-closed__text"><strong class="sh-closed__title"><?= e(content('shop', 'closed.title')) ?></strong><p><?= e(ShopSettings::closedMessage()) ?></p></div></div>
                <?php elseif ($p['can_buy']): ?>
                    <form class="sh-form" method="post" action="<?= e(url('shop/cart/add')) ?>" novalidate data-shop-add data-shop-product data-name="<?= e($p['name']) ?>" data-max="<?= (int) $maxQty ?>" data-msg-option="<?= e(content('shop', 'product.option_required')) ?>" data-msg-and="<?= e(content('shop', 'product.option_and')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                        <div class="sh-alert sh-alert--error" role="alert" id="sh-opt-error" data-shop-error hidden></div>
                        <?php foreach ($groups as $g): $gid = 'opt-' . $g['key']; ?>
                            <fieldset class="sh-opt" data-opt-group="<?= e($g['key']) ?>" data-opt-label="<?= e($g['label']) ?>">
                                <legend class="sh-opt__legend"><?= e($g['label']) ?><?= $g['required'] ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></legend>
                                <div class="sh-opt__values">
                                    <?php foreach ($g['values'] as $vi => $v): $gone = $stock['tracked'] && $v['stock'] !== null && $v['stock'] <= 0; $vid = $gid . '-' . $vi; ?>
                                        <span class="sh-opt__item<?= $gone ? ' is-gone' : '' ?>">
                                            <input class="sh-opt__input" type="radio" id="<?= e($vid) ?>" name="opt[<?= e($g['key']) ?>]" value="<?= e($v['value']) ?>"<?= $g['required'] && $vi === 0 ? ' required' : '' ?><?= $gone ? ' disabled' : '' ?>>
                                            <label class="sh-opt__label" for="<?= e($vid) ?>"><?php if (!empty($v['swatch'])): ?><span class="sh-opt__swatch" style="background:<?= e($v['swatch']) ?>" aria-hidden="true"></span><?php endif; ?><?= e($v['label']) ?><?php if ($gone): ?><span class="sh-opt__gone"> (<?= e(content('shop', 'product.option_sold_out')) ?>)</span><?php endif; ?></label>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>

                        <div class="sh-qty-row">
                            <label class="sh-qty__label" for="sh-qty"><?= e(content('shop', 'product.qty_label')) ?></label>
                            <div class="sh-qty" data-shop-qty data-max="<?= (int) $maxQty ?>">
                                <button class="sh-qty__btn" type="button" data-qty-minus hidden aria-label="<?= e(content('shop', 'product.qty_less')) ?>"><?= icon('minus', 'icon--sm') ?></button>
                                <input class="sh-qty__input" id="sh-qty" type="number" name="qty" value="1" min="1" max="<?= (int) $maxQty ?>" inputmode="numeric" autocomplete="off">
                                <button class="sh-qty__btn" type="button" data-qty-plus hidden aria-label="<?= e(content('shop', 'product.qty_more')) ?>"><?= icon('plus', 'icon--sm') ?></button>
                            </div>
                        </div>

                        <div class="sh-buy__buttons">
                            <button class="btn btn--cta btn--caps btn--lg sh-buy__add" type="submit" name="mode" value="cart"><?= icon('shopping-bag', 'icon--sm') ?> <?= e(content('shop', 'product.add_label')) ?></button>
                            <?php if ($p['can_book']): ?><button class="btn btn--outline btn--caps btn--lg" type="submit" name="mode" value="book"><?= e(content('shop', 'product.book_label')) ?></button><?php endif; ?>
                        </div>
                        <?php if ($p['can_book']): ?><p class="sh-buy__help"><?= e(content('shop', 'product.book_help')) ?></p><?php endif; ?>
                        <div class="sh-added" role="status" aria-live="polite" data-shop-added hidden>
                            <?= icon('check-circle', 'icon--sm') ?> <span data-shop-added-text></span>
                            <a class="btn btn--primary btn--sm btn--caps" href="<?= e(url('shop/cart')) ?>"><?= e(content('shop', 'catalogue.view_cart_label')) ?></a>
                        </div>
                    </form>
                <?php endif; ?>

                <dl class="sh-facts">
                    <?php if ((string) ($p['sku'] ?? '') !== ''): ?><div><dt><?= e(content('shop', 'product.sku_label')) ?></dt><dd><?= e($p['sku']) ?></dd></div><?php endif; ?>
                </dl>

                <div class="share sh-share" data-shop-share>
                    <h2 class="share__title"><?= e(content('shop', 'product.share_title')) ?></h2>
                    <ul class="share__list">
                        <?php foreach ($share as $s): ?>
                            <li><a class="share__link share__link--<?= e($s['key']) ?>" href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($s['label']) ?>"><?= icon($s['icon']) ?></a></li>
                        <?php endforeach; ?>
                        <li><button class="share__link share__copy" type="button" data-shop-copy="<?= e($absUrl) ?>" data-copied="<?= e(content('shop', 'product.share_copied')) ?>" hidden><?= icon('copy') ?> <span data-copy-text><?= e(content('shop', 'product.share_copy')) ?></span></button></li>
                    </ul>
                    <p class="visually-hidden" role="status" aria-live="polite" data-copy-status></p>
                </div>
            </div>
        </div>

        <?php $hasDesc = trim((string) ($p['description'] ?? '')) !== ''; $hasDetails = $p['details_list'] !== []; ?>
        <?php if ($hasDesc || $hasDetails): ?>
            <div class="sh-about<?= $hasDesc && $hasDetails ? ' sh-about--two' : '' ?>">
                <?php if ($hasDesc): ?>
                    <div class="sh-about__main">
                        <h2 class="sh-about__title"><?= e(content('shop', 'product.description_title')) ?></h2>
                        <div class="prose"><?= rich((string) $p['description']) ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($hasDetails): ?>
                    <div class="sh-about__side">
                        <h2 class="sh-about__title"><?= e(content('shop', 'product.details_title')) ?></h2>
                        <ul class="sh-checklist"><?php foreach ($p['details_list'] as $d): ?><li><?= icon('check', 'icon--sm') ?><span><?= e($d) ?></span></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($related !== []): ?>
    <section class="section section--tint sh-related" aria-labelledby="sh-related-title">
        <div class="container">
            <?= partial('partials/section-head', ['title' => (string) content('shop', 'product.related_title'), 'id' => 'sh-related-title']) ?>
            <div class="sh-grid">
                <?php foreach ($related as $r): ?><?= partial('partials/shop-card', ['p' => $r, 'open' => $open]) ?><?php endforeach; ?>
            </div>
            <p class="sh-related__back"><a class="btn btn--outline btn--caps" href="<?= e(url('shop')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('shop', 'product.back_label')) ?></a></p>
        </div>
    </section>
<?php endif; ?>
