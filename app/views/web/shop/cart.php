<?php
/**
 * CART. OWNER: STOREFRONT. Vars: $cart = CartService::summary() (lines, quote, texts, units, has_problems, all_bookable, open, delivery, prefer).
 * Works without JavaScript (Update cart button, plain POSTs). shop.js submits quantity changes in the background, swaps in the fresh cart and says "Cart updated".
 */
use App\Services\Shop\ShopSettings;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/shop.js')) ?>" defer></script>
<?php $this->end();
$lines = $cart['lines'];
$q = $cart['quote'];
$t = $cart['texts'];
$maxQty = ShopSettings::maxQty();
$methods = ShopSettings::deliveryMethods();
$feeText = (string) $t['fee_label'];
$hasBuyable = false;
foreach ($lines as $l) {
    if ($l['problem'] === null || $l['problem'] === 'reduced') {
        $hasBuyable = true;
    }
}
?>
<?= partial('partials/page-hero', ['title' => (string) content('shop', 'cart.title'), 'eyebrow' => (string) content('shop', 'cart.eyebrow'), 'sheet' => 'white']) ?>

<section class="section sh-cart" data-no-justify aria-label="<?= e(strip_highlight((string) content('shop', 'cart.title'))) ?>">
    <div class="container" data-shop-cart data-updated="<?= e(content('shop', 'cart.updated_live')) ?>">
        <div class="visually-hidden" role="status" aria-live="polite" data-shop-live></div>
        <?php if ($lines === []): ?>
            <?= partial('partials/empty-state', ['title' => (string) content('shop', 'cart.empty_title'), 'icon' => 'shopping-cart', 'text' => (string) content('shop', 'cart.empty_text'),
                'actions' => [['label' => (string) content('shop', 'cart.empty_cta'), 'url' => url('shop'), 'class' => 'btn--cta btn--caps']]]) ?>
        <?php else: ?>
            <div class="sh-cart__layout">
                <div class="sh-cart__main">
                    <form method="post" action="<?= e(url('shop/cart/update')) ?>" data-shop-cart-form>
                        <?= csrf_field() ?>
                        <ul class="sh-lines">
                            <?php foreach ($lines as $l): ?>
                                <?php if ($l['problem'] === 'removed' || $l['problem'] === 'out_of_stock'): ?>
                                    <li class="sh-line sh-line--problem">
                                        <span class="sh-line__alert"><?= icon('alert-circle', 'icon--sm') ?></span>
                                        <p class="sh-line__msg"><?= e(str_replace('{name}', $l['name'] !== '' ? $l['name'] : content('shop', 'cart.item_fallback'), (string) content('shop', $l['problem'] === 'removed' ? 'cart.notice_removed' : 'cart.notice_out'))) ?></p>
                                        <button class="btn btn--ghost btn--sm" type="submit" formaction="<?= e(url('shop/cart/remove')) ?>" name="line" value="<?= e($l['key']) ?>"><?= e(content('shop', 'cart.remove_label')) ?></button>
                                    </li>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <?php $lineMax = $l['max'] === null ? $maxQty : min($maxQty, (int) $l['max']); ?>
                                <li class="sh-line" data-line="<?= e($l['key']) ?>">
                                    <a class="sh-line__media" href="<?= e($l['url']) ?>" tabindex="-1" aria-hidden="true">
                                        <?= partial('partials/shop-image', ['img' => $l['image'], 'sizes' => '96px', 'class' => 'sh-line__img', 'alt' => '']) ?>
                                    </a>
                                    <div class="sh-line__info">
                                        <a class="sh-line__name" href="<?= e($l['url']) ?>"><?= e($l['name']) ?></a>
                                        <?php if ($l['options_text'] !== ''): ?><span class="sh-line__opts"><?= e($l['options_text']) ?></span><?php endif; ?>
                                        <span class="sh-line__unit"><?= e($l['unit_text']) ?></span>
                                        <?php if ($l['problem'] === 'reduced'): ?><span class="sh-line__note"><?= icon('info', 'icon--sm') ?> <?= e(str_replace(['{name}', '{n}'], [$l['name'], (string) $l['qty']], (string) content('shop', 'cart.notice_reduced'))) ?></span><?php endif; ?>
                                    </div>
                                    <div class="sh-line__qty">
                                        <div class="sh-qty" data-shop-qty data-max="<?= (int) $lineMax ?>">
                                            <button class="sh-qty__btn" type="button" data-qty-minus hidden aria-label="<?= e(content('shop', 'product.qty_less')) ?>"><?= icon('minus', 'icon--sm') ?></button>
                                            <label class="visually-hidden" for="q-<?= e($l['key']) ?>"><?= e($l['name']) ?>: <?= e(content('shop', 'cart.th_qty')) ?></label>
                                            <input class="sh-qty__input" id="q-<?= e($l['key']) ?>" type="number" name="qty[<?= e($l['key']) ?>]" value="<?= (int) $l['qty'] ?>" min="0" max="<?= (int) $lineMax ?>" inputmode="numeric" autocomplete="off" data-shop-cart-qty>
                                            <button class="sh-qty__btn" type="button" data-qty-plus hidden aria-label="<?= e(content('shop', 'product.qty_more')) ?>"><?= icon('plus', 'icon--sm') ?></button>
                                        </div>
                                    </div>
                                    <p class="sh-line__total"><span class="visually-hidden"><?= e(content('shop', 'cart.th_total')) ?>: </span><?= e($l['line_text']) ?></p>
                                    <button class="sh-line__remove" type="submit" formaction="<?= e(url('shop/cart/remove')) ?>" name="line" value="<?= e($l['key']) ?>" aria-label="<?= e(str_replace('{name}', $l['name'], (string) content('shop', 'cart.remove_aria'))) ?>"><?= icon('trash', 'icon--sm') ?><span class="sh-line__remove-text"><?= e(content('shop', 'cart.remove_label')) ?></span></button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="sh-cart__update<?= $cart['has_problems'] ? '' : ' sh-nojs' ?>"><button class="btn btn--outline btn--sm" type="submit"><?= e(content('shop', 'cart.update_label')) ?></button></p>
                    </form>
                    <p class="sh-cart__continue"><a class="link-arrow" href="<?= e(url('shop')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('shop', 'cart.continue_label')) ?></a></p>
                </div>

                <aside class="sh-summary" aria-labelledby="sh-sum-title">
                    <h2 class="sh-summary__title" id="sh-sum-title"><?= e(content('shop', 'checkout.summary_title')) ?></h2>

                    <?php if (count($methods) > 1): ?>
                        <form class="sh-delivery" method="post" action="<?= e(url('shop/cart/delivery')) ?>" data-shop-delivery-form>
                            <?= csrf_field() ?>
                            <fieldset>
                                <legend class="sh-summary__sub"><?= e(content('shop', 'cart.delivery_title')) ?></legend>
                                <?php foreach ($methods as $m): ?>
                                    <label class="sh-choice"><input type="radio" name="delivery" value="<?= e($m) ?>"<?= $cart['delivery'] === $m ? ' checked' : '' ?>> <span><?= e(content('shop', 'checkout.delivery_' . $m)) ?></span></label>
                                <?php endforeach; ?>
                            </fieldset>
                            <button class="btn btn--outline btn--sm sh-nojs" type="submit"><?= e(content('shop', 'cart.update_label')) ?></button>
                        </form>
                    <?php endif; ?>

                    <dl class="sh-totals">
                        <div><dt><?= e(content('shop', 'cart.subtotal_label')) ?></dt><dd><?= e($t['subtotal_text']) ?></dd></div>
                        <div><dt><?= e(content('shop', 'cart.delivery_label')) ?></dt><dd><?= e($feeText) ?></dd></div>
                        <div class="sh-totals__total"><dt><?= e(content('shop', 'cart.total_label')) ?></dt><dd><?= e($t['total_text']) ?></dd></div>
                    </dl>
                    <?php if ($q['free_gap'] !== null && $q['free_gap'] > 0 && $q['delivery_method'] === 'delivery'): ?><p class="sh-summary__hint"><?= icon('package', 'icon--sm') ?> <?= e(str_replace('{amount}', (string) $t['free_gap_text'], (string) content('shop', 'cart.free_gap'))) ?></p><?php endif; ?>

                    <?php if (!$cart['open']): ?>
                        <div class="sh-closed sh-closed--inline" role="status"><span class="sh-closed__icon"><?= icon('clock') ?></span><div class="sh-closed__text"><strong class="sh-closed__title"><?= e(content('shop', 'closed.title')) ?></strong><p><?= e(ShopSettings::closedMessage()) ?></p></div></div>
                    <?php elseif ($cart['has_problems']): ?>
                        <p class="sh-summary__hint sh-summary__hint--warn" role="status"><?= icon('alert-circle', 'icon--sm') ?> <?= e(content('shop', 'checkout.err_stock')) ?></p>
                    <?php elseif ($hasBuyable): ?>
                        <a class="btn btn--cta btn--caps btn--lg btn--block" href="<?= e(url('shop/checkout')) ?>"><?= e(content('shop', 'cart.checkout_label')) ?></a>
                    <?php endif; ?>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>
