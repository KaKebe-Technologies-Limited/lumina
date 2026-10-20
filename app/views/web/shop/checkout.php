<?php
/**
 * CHECKOUT. OWNER: STOREFRONT. Vars: $cart (CartService::summary), $methods (PaymentMethods::available), $selected (key or ''), $pickup (address, hours), $delivery, $open.
 * Fields: name email phone delivery address note consent payment + the honeypot and the CSRF token (the route verifies both). Everything is checked again on the server.
 * shop.js: shows the address only for delivery, switches the button text for an online payment and moves focus to the error summary.
 */
use App\Services\Shop\CartService;
use App\Services\Shop\Money;
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\PricingService;
use App\Services\Shop\ShopSettings;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/shop.js')) ?>" defer></script>
<?php $this->end();
$q = $cart['quote'];
$t = $cart['texts'];
$buyLines = CartService::purchasable($cart['lines']);
$quotes = [];
foreach (ShopSettings::deliveryMethods() as $dm) {          // totals for each way of getting the order, so the summary follows the choice without a reload
    $dq = PricingService::quote($buyLines, $dm);
    $quotes[$dm] = ['fee' => (string) PricingService::texts($dq)['fee_label'], 'total' => (string) PricingService::texts($dq)['total_text']];
}
$deliveryMethods = ShopSettings::deliveryMethods();
$deliveryNow = (string) old('delivery', $delivery);
if (!in_array($deliveryNow, $deliveryMethods, true)) {
    $deliveryNow = $deliveryMethods[0] ?? 'pickup';
}
$errs = errors();
$areas = ShopSettings::str('shop_delivery_areas');
$feeText = (string) $t['fee_label'];
$consentOld = (bool) old('consent', false);
$consentLabel = e((string) content('shop', 'checkout.consent_label'));
$consentLabel = str_replace(
    ['{terms_link}', '{privacy_link}'],
    ['<a href="' . e(url('shop/terms')) . '#terms" target="_blank" rel="noopener">' . e(content('shop', 'checkout.terms_link')) . '</a>', '<a href="' . e(url('privacy')) . '" target="_blank" rel="noopener">' . e(content('shop', 'checkout.privacy_link')) . '</a>'],
    $consentLabel
);
$labels = ['name' => 'name_label', 'email' => 'email_label', 'phone' => 'phone_label', 'address' => 'address_label', 'note' => 'note_label', 'consent' => 'consent_label', 'payment' => 'payment_title', 'delivery' => 'delivery_title'];
$fieldIds = ['name' => 'f-name', 'email' => 'f-email', 'phone' => 'f-phone', 'address' => 'f-address', 'note' => 'f-note', 'consent' => 'f-consent', 'payment' => 'pay-group', 'delivery' => 'delivery-group'];
$payLabelKey = 'place_label';
foreach ($methods as $m) {
    if ($m['key'] === $selected && $m['key'] === 'flutterwave') {
        $payLabelKey = 'place_pay_label';
    }
}
?>
<?= partial('partials/page-hero', ['title' => (string) content('shop', 'checkout.title'), 'eyebrow' => (string) content('shop', 'checkout.eyebrow'), 'lead' => (string) content('shop', 'checkout.lead'), 'sheet' => 'white']) ?>

<section class="section sh-checkout" data-no-justify aria-label="<?= e(strip_highlight((string) content('shop', 'checkout.title'))) ?>">
    <div class="container">
        <?php if (!$open): ?>
            <div class="sh-closed" role="status"><span class="sh-closed__icon"><?= icon('clock', 'icon--lg') ?></span><div class="sh-closed__text"><strong class="sh-closed__title"><?= e(content('shop', 'closed.title')) ?></strong><p><?= e(ShopSettings::closedMessage()) ?></p></div></div>
        <?php endif; ?>
        <div class="sh-checkout__layout">
            <?php if ($methods === []): ?>
                <?php // nothing can be paid with right now: tell the buyer at once instead of after a whole form (no fields, no consent box, no "secure payment" claim) ?>
                <div class="sh-checkout__form">
                    <p class="sh-alert sh-alert--info" role="status"><?= icon('info', 'icon--sm') ?> <span><?= e(content('shop', 'checkout.no_methods')) ?></span></p>
                    <div class="sh-checkout__actions"><a class="btn btn--ghost" href="<?= e(url('shop/cart')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('shop', 'checkout.back_label')) ?></a></div>
                </div>
            <?php else: ?>
            <form class="sh-checkout__form" method="post" action="<?= e(url('shop/checkout')) ?>" novalidate data-shop-checkout>
                <?= csrf_field() ?><?= honeypot_field() ?>
                <?php if ($errs !== []): ?>
                    <div class="sh-alert sh-alert--error" role="alert" tabindex="-1" data-shop-errors>
                        <strong class="sh-alert__title"><?= e(content('shop', 'checkout.error_summary')) ?></strong>
                        <ul>
                            <?php foreach ($errs as $field => $m): $msg = is_array($m) ? (string) ($m[0] ?? '') : (string) $m; ?>
                                <li><a href="#<?= e($fieldIds[$field] ?? 'f-' . $field) ?>"><?= e($msg) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <fieldset class="sh-step">
                    <legend class="sh-step__title"><span class="sh-step__num" aria-hidden="true">1</span><?= e(content('shop', 'checkout.details_title')) ?></legend>
                    <div class="sh-step__grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'label' => (string) content('shop', 'checkout.name_label'), 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150, 'class' => 'sh-step__full']) ?>
                        <?= partial('partials/form-field', ['name' => 'email', 'label' => (string) content('shop', 'checkout.email_label'), 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 160, 'help' => (string) content('shop', 'checkout.email_help')]) ?>
                        <?= partial('partials/form-field', ['name' => 'phone', 'label' => (string) content('shop', 'checkout.phone_label'), 'type' => 'tel', 'required' => true, 'autocomplete' => 'tel', 'maxlength' => 30, 'help' => (string) content('shop', 'checkout.phone_help')]) ?>
                    </div>
                </fieldset>

                <fieldset class="sh-step" id="delivery-group" tabindex="-1">
                    <legend class="sh-step__title"><span class="sh-step__num" aria-hidden="true">2</span><?= e(content('shop', 'checkout.delivery_title')) ?></legend>
                    <div class="sh-choices">
                        <?php foreach ($deliveryMethods as $m): ?>
                            <label class="sh-pick<?= $deliveryNow === $m ? ' is-on' : '' ?>">
                                <input type="radio" name="delivery" value="<?= e($m) ?>"<?= $deliveryNow === $m ? ' checked' : '' ?> data-shop-delivery data-fee="<?= e($quotes[$m]['fee'] ?? '') ?>" data-total="<?= e($quotes[$m]['total'] ?? '') ?>">
                                <span class="sh-pick__icon"><?= icon($m === 'pickup' ? 'map-pin' : 'package') ?></span>
                                <span class="sh-pick__text">
                                    <strong><?= e(content('shop', 'checkout.delivery_' . $m)) ?></strong>
                                    <?php if ($m === 'pickup' && ($pickup['address'] !== '' || $pickup['hours'] !== '')): ?>
                                        <span class="sh-pick__sub"><?= e(trim(preg_replace('/\s+/', ' ', str_replace(['{address}', '{hours}'], [$pickup['address'], $pickup['hours']], (string) content('shop', 'checkout.pickup_note'))))) ?></span>
                                    <?php elseif ($m === 'delivery'): ?>
                                        <span class="sh-pick__sub"><?= e(trim(preg_replace('/\s+/', ' ', str_replace(['{fee}', '{areas}'], [Money::format(ShopSettings::money('shop_delivery_fee'), ShopSettings::currency()), $areas], (string) content('shop', 'checkout.delivery_note'))))) ?></span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (in_array('delivery', $deliveryMethods, true)): ?>
                        <div class="sh-address" data-shop-address>   <?php /* always visible without JavaScript; shop.js hides it while "pick up" is chosen */ ?>
                            <?= partial('partials/form-field', ['name' => 'address', 'label' => (string) content('shop', 'checkout.address_label'), 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400, 'autocomplete' => 'street-address', 'help' => (string) content('shop', 'checkout.address_help')]) ?>
                        </div>
                    <?php endif; ?>
                    <?= partial('partials/form-field', ['name' => 'note', 'label' => (string) content('shop', 'checkout.note_label'), 'type' => 'textarea', 'rows' => 2, 'maxlength' => 500, 'help' => (string) content('shop', 'checkout.note_help'), 'class' => 'sh-note-field']) ?>
                </fieldset>

                <fieldset class="sh-step" id="pay-group" tabindex="-1">
                    <legend class="sh-step__title"><span class="sh-step__num" aria-hidden="true">3</span><?= e(content('shop', 'checkout.payment_title')) ?></legend>
                    <div class="sh-choices sh-choices--pay">
                        <?php foreach ($methods as $m): $mid = 'pay-' . $m['key']; ?>
                            <div class="sh-pay<?= $selected === $m['key'] ? ' is-on' : '' ?>">
                                <label class="sh-pick" for="<?= e($mid) ?>">
                                    <input type="radio" id="<?= e($mid) ?>" name="payment" value="<?= e($m['key']) ?>"<?= $selected === $m['key'] ? ' checked' : '' ?> data-shop-pay="<?= e($m['key']) ?>">
                                    <span class="sh-pick__icon"><?= icon($m['key'] === 'flutterwave' ? 'shield' : ($m['key'] === 'bank' ? 'banknote' : ($m['key'] === 'momo' ? 'smartphone' : ($m['key'] === 'book' ? 'calendar' : 'hand-heart')))) ?></span>
                                    <span class="sh-pick__text"><strong><?= e($m['label']) ?></strong><?php if ($m['instructions'] !== ''): ?><span class="sh-pick__sub"><?= e($m['instructions']) ?></span><?php endif; ?></span>
                                </label>
                                <?php $payDetails = in_array($m['key'], ['momo', 'bank'], true) ? PaymentMethods::details($m['key']) : []; ?>
                                <?php if ($payDetails !== []): // the instructions say "send the total to the numbers below": show them here too, not only on the order page ?>
                                    <dl class="sh-paydetails sh-paydetails--checkout">
                                        <?php foreach ($payDetails as $d): ?>
                                            <div><dt><?= e($d['label']) ?><?= isset($d['name']) && $d['name'] !== '' ? ' <span>(' . e($d['name']) . ')</span>' : '' ?></dt><dd><?= e($d['number'] ?? $d['value'] ?? '') ?></dd></div>
                                        <?php endforeach; ?>
                                    </dl>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (error('payment') && $methods !== []): ?><div class="field__error" role="alert"><?= e(error('payment')) ?></div><?php endif; ?>
                </fieldset>

                <div class="field sh-consent<?= error('consent') ? ' field--error' : '' ?>">
                    <label class="check" for="f-consent">
                        <input type="checkbox" id="f-consent" name="consent" value="1"<?= $consentOld ? ' checked' : '' ?> required<?= error('consent') ? ' aria-invalid="true" aria-describedby="f-consent-error"' : '' ?>>
                        <span><?= $consentLabel ?> <span class="field__req" aria-hidden="true">*</span></span>
                    </label>
                    <?php if (error('consent')): ?><div class="field__error" id="f-consent-error" role="alert"><?= e(error('consent')) ?></div><?php endif; ?>
                </div>

                <div class="sh-checkout__actions">
                    <a class="btn btn--ghost" href="<?= e(url('shop/cart')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('shop', 'checkout.back_label')) ?></a>
                    <?php if ($methods !== [] && $open): ?>
                        <button class="btn btn--cta btn--caps btn--lg" type="submit" data-shop-submit data-label="<?= e(content('shop', 'checkout.place_label')) ?>" data-label-pay="<?= e(content('shop', 'checkout.place_pay_label')) ?>"><?= icon('lock', 'icon--sm') ?> <span data-shop-submit-text><?= e(content('shop', $payLabelKey)) ?></span></button>
                    <?php endif; ?>
                </div>
                <p class="sh-secure"><?= icon('shield', 'icon--sm') ?> <span><?= e(content('shop', 'checkout.secure_note')) ?></span></p>
            </form>
            <?php endif; ?>

            <aside class="sh-summary" aria-labelledby="sum-h">
                <h2 class="sh-summary__title" id="sum-h"><?= e(content('shop', 'checkout.summary_title')) ?></h2>
                <ul class="sh-mini">
                    <?php foreach ($buyLines as $l): ?>
                        <li class="sh-mini__item">
                            <span class="sh-mini__media"><?= partial('partials/shop-image', ['img' => $l['image'], 'sizes' => '56px', 'class' => 'sh-mini__img', 'alt' => '']) ?><span class="sh-mini__qty" aria-hidden="true"><?= (int) $l['qty'] ?></span></span>
                            <span class="sh-mini__info"><span class="sh-mini__name"><span class="visually-hidden"><?= (int) $l['qty'] ?> &times; </span><?= e($l['name']) ?></span><?php if ($l['options_text'] !== ''): ?><span class="sh-mini__opts"><?= e($l['options_text']) ?></span><?php endif; ?></span>
                            <span class="sh-mini__price"><?= e($l['line_text']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <dl class="sh-totals">
                    <div><dt><?= e(content('shop', 'cart.subtotal_label')) ?></dt><dd><?= e($t['subtotal_text']) ?></dd></div>
                    <div><dt><?= e(content('shop', 'cart.delivery_label')) ?></dt><dd data-shop-fee><?= e($quotes[$deliveryNow]['fee'] ?? $feeText) ?></dd></div>
                    <div class="sh-totals__total"><dt><?= e(content('shop', 'cart.total_label')) ?></dt><dd data-shop-total><?= e($quotes[$deliveryNow]['total'] ?? $t['total_text']) ?></dd></div>
                </dl>
                <p class="sh-summary__hint"><a href="<?= e(url('shop/cart')) ?>"><?= e(content('shop', 'checkout.back_label')) ?></a></p>
            </aside>
        </div>
    </div>
</section>
