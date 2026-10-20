<?php
/**
 * Admin > Shop > Shop settings. OWNER: PAYMENTS. Vars: $defs (ShopSettings::DEFS), $readiness (list of ok/text/link), $defaults (registry wording of the three policy texts), $currency, $orgAddress.
 * Field names are the setting keys (ShopSettingsController::saveSettings validates every one). The payment methods and the gateway keys live on the Payments screen.
 */
use App\Services\Shop\Money;
use App\Services\Shop\ShopSettings;

$dec = Money::decimals($currency);
$v = static fn(string $k): string => (string) old($k, ShopSettings::str($k));
$on = static fn(string $k): bool => (string) old($k, ShopSettings::bool($k) ? '1' : '0') === '1';
$money = static function (string $k): string {
    $p = old($k, null);
    if ($p !== null) { return (string) $p; }
    $m = ShopSettings::money($k);
    return $m === 0 ? '0' : Money::format($m, ShopSettings::currency(), false);
};
$switch = static function (string $k, string $label, string $help = '') use ($on): string {
    return '<div><input type="hidden" name="' . e($k) . '" value="0"><label class="switch"><input type="checkbox" name="' . e($k) . '" value="1"' . ($on($k) ? ' checked' : '') . '><span class="switch__track" aria-hidden="true"></span><span class="switch__text">' . e($label) . '</span></label>'
        . ($help !== '' ? '<p class="form-note">' . e($help) . '</p>' : '') . '</div>';
};
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-shop-payments.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="ash ash-settings">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Shop settings',
        'subtitle' => 'Open or close the shop, say how customers get their order, and who is told about new orders.',
        'actions'  => [['label' => 'Payments', 'url' => url('admin/shop/payments'), 'class' => 'btn--outline', 'icon' => 'banknote'], ['label' => 'View the shop', 'url' => url('shop'), 'class' => 'btn--ghost', 'icon' => 'external-link', 'target' => '_blank']],
    ]) ?>

    <section class="card ash-ready" aria-labelledby="rd-title">
        <div class="card__header"><h2 class="card__title" id="rd-title">Before you open the shop</h2></div>
        <div class="card__body">
            <ul class="ash-checks">
                <?php foreach ($readiness as $r): ?>
                    <li class="<?= $r['ok'] ? 'is-ok' : 'is-todo' ?>"><?= icon($r['ok'] ? 'check-circle' : 'alert-circle', 'icon--sm') ?> <span><?= e($r['text']) ?><?php if (!$r['ok'] && !empty($r['link'])): ?> <a href="<?= e(url($r['link'])) ?>">Open</a><?php endif; ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <form method="post" action="<?= e(url('admin/shop/settings')) ?>" novalidate data-unsaved-guard>
        <?= csrf_field() ?>
        <button class="visually-hidden" type="submit" tabindex="-1" aria-hidden="true">Save</button>
        <div class="card-grid card-grid--main-aside">
            <div>
                <section class="card" aria-labelledby="st-open">
                    <div class="card__header"><h2 class="card__title" id="st-open">Is the shop open?</h2></div>
                    <div class="card__body">
                        <?= $switch('shop_open', 'The shop takes orders', 'When off, visitors can still look at the products but cannot add to the cart or check out. They see the message below.') ?>
                        <?= partial('partials/form-field', ['name' => 'shop_closed_message', 'label' => 'Message shown while the shop is closed', 'type' => 'textarea', 'rows' => 3, 'value' => $v('shop_closed_message'), 'maxlength' => 500, 'help' => 'Empty = the wording of the Shop page (Admin > Pages > Shop).']) ?>
                    </div>
                </section>

                <section class="card" aria-labelledby="st-deliv">
                    <div class="card__header"><h2 class="card__title" id="st-deliv">Collection and delivery</h2></div>
                    <div class="card__body">
                        <?php if (error('shop_pickup_enabled')): ?><div class="field__error" role="alert"><?= e(error('shop_pickup_enabled')) ?></div><?php endif; ?>
                        <?= $switch('shop_pickup_enabled', 'Customers can collect their order', 'They pick it up from the address below.') ?>
                        <div class="form-grid">
                            <?= partial('partials/form-field', ['name' => 'shop_pickup_address', 'label' => 'Collection address', 'type' => 'textarea', 'rows' => 2, 'value' => $v('shop_pickup_address'), 'maxlength' => 300, 'help' => $orgAddress !== '' ? 'Empty = the organisation address from Site settings (' . $orgAddress . ').' : 'Where customers collect their order. Empty = the organisation address from Site settings.']) ?>
                            <?= partial('partials/form-field', ['name' => 'shop_pickup_hours', 'label' => 'Collection hours', 'type' => 'textarea', 'rows' => 2, 'value' => $v('shop_pickup_hours'), 'maxlength' => 200, 'help' => 'For example: Monday to Friday, 9am to 5pm.']) ?>
                        </div>
                        <hr class="ash-sep">
                        <?= $switch('shop_delivery_enabled', 'We deliver', 'Customers can ask for delivery and give an address. You arrange the delivery yourself.') ?>
                        <div class="form-grid">
                            <?= partial('partials/form-field', ['name' => 'shop_delivery_fee', 'label' => 'Delivery fee (' . $currency . ')', 'value' => $money('shop_delivery_fee'), 'attrs' => ['inputmode' => 'decimal'], 'autocomplete' => 'off', 'help' => '0 = no fee.']) ?>
                            <?= partial('partials/form-field', ['name' => 'shop_free_delivery_over', 'label' => 'Free delivery for orders of at least', 'value' => $money('shop_free_delivery_over'), 'attrs' => ['inputmode' => 'decimal'], 'autocomplete' => 'off', 'help' => '0 = delivery is never free.']) ?>
                            <?= partial('partials/form-field', ['name' => 'shop_delivery_areas', 'label' => 'Where you deliver', 'type' => 'textarea', 'rows' => 2, 'value' => $v('shop_delivery_areas'), 'maxlength' => 500, 'class' => 'field--full', 'help' => 'Shown at checkout. For example: Gulu town and nearby trading centres.']) ?>
                        </div>
                    </div>
                </section>

                <section class="card" aria-labelledby="st-orders">
                    <div class="card__header"><h2 class="card__title" id="st-orders">Orders and e-mails</h2></div>
                    <div class="card__body">
                        <div class="form-grid">
                            <?= partial('partials/form-field', ['name' => 'shop_notify_emails', 'label' => 'Who is told about new orders', 'value' => $v('shop_notify_emails'), 'class' => 'field--full', 'maxlength' => 400, 'help' => 'One or more e-mail addresses, separated by commas. Empty = the organisation notification address. The e-mails never contain card or account details.']) ?>
                            <?= partial('partials/form-field', ['name' => 'shop_order_prefix', 'label' => 'Order number prefix', 'value' => $v('shop_order_prefix'), 'maxlength' => 8, 'help' => '2 to 8 letters or digits. Order numbers look like LAF-261020-AB12.']) ?>
                            <?= partial('partials/form-field', ['name' => 'shop_max_qty', 'label' => 'Most of one item per order', 'type' => 'number', 'value' => $v('shop_max_qty'), 'min' => 1, 'max' => 99, 'step' => 1]) ?>
                        </div>
                        <?= $switch('shop_auto_confirm_paid', 'Confirm an order automatically once it is paid', 'The customer gets a confirmation e-mail the moment an online payment is verified or you mark a payment received.') ?>
                    </div>
                </section>

                <section class="card" aria-labelledby="st-hold">
                    <div class="card__header"><h2 class="card__title" id="st-hold">Keeping stock for unpaid orders</h2></div>
                    <div class="card__body">
                        <p class="ash-hint">When someone orders, the items are set aside so nobody else buys them. If the order is still unpaid after the time below, it is cancelled and the items go back on sale. Orders that need a person to check a payment are never cancelled by themselves.</p>
                        <div class="form-grid form-grid--3">
                            <?= partial('partials/form-field', ['name' => 'shop_hold_days', 'label' => 'Bookings and pay on pickup (days)', 'type' => 'number', 'value' => $v('shop_hold_days'), 'min' => 1, 'max' => 60, 'step' => 1]) ?>
                            <?= partial('partials/form-field', ['name' => 'shop_manual_hold_days', 'label' => 'Mobile money and bank (days)', 'type' => 'number', 'value' => $v('shop_manual_hold_days'), 'min' => 1, 'max' => 60, 'step' => 1]) ?>
                            <?= partial('partials/form-field', ['name' => 'shop_gateway_hold_hours', 'label' => 'Online payment (hours)', 'type' => 'number', 'value' => $v('shop_gateway_hold_hours'), 'min' => 1, 'max' => 168, 'step' => 1]) ?>
                        </div>
                        <?= $switch('shop_restock_on_cancel', 'Put the items back on sale when a PAID order is cancelled', 'An unpaid order that is cancelled always returns its items.') ?>
                        <?= $switch('shop_restock_on_refund', 'Put the items back on sale when an order is refunded', 'Leave off if returned goods may be damaged: you can add the stock back by hand in Products.') ?>
                    </div>
                </section>

                <section class="card" aria-labelledby="st-texts">
                    <div class="card__header"><h2 class="card__title" id="st-texts">Terms, delivery and returns</h2></div>
                    <div class="card__body">
                        <div class="alert alert--warning" role="note"><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title">The ready-made wording is only a draft.</strong> Read it and change it so it is true for the foundation. Ideally ask a legal adviser to check it before the shop opens. Leave a box empty to use the draft.</div></div>
                        <?php foreach (['shop_terms_text' => ['terms', 'Terms of sale'], 'shop_returns_text' => ['returns', 'Returns and exchanges'], 'shop_delivery_text' => ['delivery', 'Delivery and collection']] as $key => [$d, $label]): ?>
                            <?= partial('partials/form-field', ['name' => $key, 'label' => $label, 'type' => 'textarea', 'rows' => 7, 'value' => $v($key), 'maxlength' => 8000, 'placeholder' => (string) $defaults[$d], 'help' => 'Empty = the draft wording shown in grey. Blank lines start a new paragraph.']) ?>
                        <?php endforeach; ?>
                        <p class="ash-hint">Customers read these at <a href="<?= e(url('shop/terms')) ?>" target="_blank" rel="noopener"><?= e(url('shop/terms')) ?></a>, linked from the checkout.</p>
                    </div>
                </section>
            </div>

            <div class="ash-aside">
                <section class="card" aria-labelledby="st-help">
                    <div class="card__header"><h2 class="card__title" id="st-help">Good to know</h2></div>
                    <div class="card__body ash-help">
                        <p><strong>Currency:</strong> <?= e($currency) ?> (change it in Payments).</p>
                        <p><strong>Prices</strong> are set per product in Products.</p>
                        <p><strong>Wording</strong> of buttons, headings and e-mails: Admin &gt; Pages &gt; Shop.</p>
                        <p><strong>Live cron:</strong> ask your hosting to run <code>bin/shop-maintenance.php</code> every 15 to 30 minutes so old unpaid orders are released and missed payment messages are caught up.</p>
                    </div>
                </section>
            </div>
        </div>
        <div class="form-actions"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save shop settings</button></div>
    </form>
</div>
