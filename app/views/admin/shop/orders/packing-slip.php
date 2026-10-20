<?php
/**
 * Admin > Shop > packing slip (print it and put it in the parcel). OWNER: ADMIN. Vars: $order, $items. Prices are left off on purpose: it is for the person packing.
 * The admin chrome is hidden when printing (scss/admin/_shop.scss).
 */
use App\Services\Shop\ShopSettings;

$o = $order;
$site = (string) setting('site_name', 'Lumina Africa Foundation');
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-shop.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="ash ash-doc">
    <?= partial('partials/admin/page-header', ['title' => 'Packing slip ' . $o['order_number'], 'actions' => [
        ['label' => 'Print', 'url' => '#print', 'class' => 'btn--primary', 'icon' => 'printer'],
        ['label' => 'Back to the order', 'url' => url('admin/shop/orders/' . (int) $o['id']), 'class' => 'btn--ghost', 'icon' => 'arrow-left'],
    ]]) ?>
    <article class="ash-paper">
        <header class="ash-paper__head">
            <div><p class="ash-paper__site"><?= e($site) ?></p><h2 class="ash-paper__title">Packing slip</h2></div>
            <div class="ash-paper__num"><span>Order</span><strong><?= e($o['order_number']) ?></strong><span><?= e(format_date($o['created_at'], 'j F Y')) ?></span></div>
        </header>
        <div class="ash-paper__cols">
            <div>
                <h3>Customer</h3>
                <p><strong><?= e($o['customer_name']) ?></strong><br><?= e($o['customer_phone']) ?><br><?= e($o['customer_email']) ?></p>
            </div>
            <div>
                <h3><?= $o['delivery_method'] === 'delivery' ? 'Deliver to' : 'Collect from' ?></h3>
                <p><?= $o['delivery_method'] === 'delivery' ? nl2br(e((string) $o['delivery_address'])) : nl2br(e(ShopSettings::pickup()['address'])) ?></p>
            </div>
        </div>
        <table class="ash-paper__table">
            <thead><tr><th scope="col" class="tick"><span class="visually-hidden">Packed</span></th><th scope="col">Item</th><th scope="col" class="num">Qty</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td class="tick"><span class="ash-box" aria-hidden="true"></span></td>
                    <td><strong><?= e($it['name']) ?></strong><?php if ((string) ($it['options_text'] ?? '') !== ''): ?><br><?= e($it['options_text']) ?><?php endif; ?><?php if ((string) ($it['sku'] ?? '') !== ''): ?><br><small>Code <?= e($it['sku']) ?></small><?php endif; ?></td>
                    <td class="num"><strong><?= (int) $it['qty'] ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (trim((string) ($o['customer_note'] ?? '')) !== ''): ?><div class="ash-paper__note"><h3>Note from the customer</h3><p><?= nl2br(e((string) $o['customer_note'])) ?></p></div><?php endif; ?>
        <p class="ash-paper__foot">Payment: <?= e(\App\Services\Shop\OrderService::paymentStatusLabel((string) $o['payment_status'])) ?>. Thank you for supporting <?= e($site) ?>.</p>
    </article>
</div>
