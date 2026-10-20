<?php
/**
 * Admin > Shop > receipt (print or save as PDF for a customer who asks). OWNER: ADMIN. Vars: $order, $items, $ledger.
 */
use App\Services\Shop\Money;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;

$o = $order;
$cur = (string) $o['currency'];
$m = static fn($v): string => Money::format(Money::fromDb($v), $cur);
$site = (string) setting('site_name', 'Lumina Africa Foundation');
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-shop.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="ash ash-doc">
    <?= partial('partials/admin/page-header', ['title' => 'Receipt ' . $o['order_number'], 'actions' => [
        ['label' => 'Print', 'url' => '#print', 'class' => 'btn--primary', 'icon' => 'printer'],
        ['label' => 'Back to the order', 'url' => url('admin/shop/orders/' . (int) $o['id']), 'class' => 'btn--ghost', 'icon' => 'arrow-left'],
    ]]) ?>
    <article class="ash-paper">
        <header class="ash-paper__head">
            <div><p class="ash-paper__site"><?= e($site) ?></p><h2 class="ash-paper__title">Receipt</h2></div>
            <div class="ash-paper__num"><span>Order</span><strong><?= e($o['order_number']) ?></strong><span><?= e(format_date($o['created_at'], 'j F Y')) ?></span></div>
        </header>
        <div class="ash-paper__cols">
            <div><h3>Customer</h3><p><strong><?= e($o['customer_name']) ?></strong><br><?= e($o['customer_email']) ?><br><?= e($o['customer_phone']) ?></p></div>
            <div><h3>Status</h3><p>Order: <?= e(OrderService::statusLabel((string) $o['status'])) ?><br>Payment: <?= e(OrderService::paymentStatusLabel((string) $o['payment_status'])) ?><?= !empty($o['paid_at']) ? '<br>Paid on ' . e(format_date($o['paid_at'], 'j F Y')) : '' ?></p></div>
        </div>
        <table class="ash-paper__table">
            <thead><tr><th scope="col">Item</th><th scope="col" class="num">Price</th><th scope="col" class="num">Qty</th><th scope="col" class="num">Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr><td><strong><?= e($it['name']) ?></strong><?= (string) ($it['options_text'] ?? '') !== '' ? '<br>' . e($it['options_text']) : '' ?></td><td class="num"><?= e($m($it['unit_price'])) ?></td><td class="num"><?= (int) $it['qty'] ?></td><td class="num"><?= e($m($it['line_total'])) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="3">Subtotal</td><td class="num"><?= e($m($o['subtotal'])) ?></td></tr>
                <tr><td colspan="3">Delivery</td><td class="num"><?= e($o['delivery_method'] === 'pickup' ? 'No charge' : $m($o['delivery_fee'])) ?></td></tr>
                <tr class="total"><td colspan="3">Total</td><td class="num"><?= e($m($o['total'])) ?></td></tr>
            </tfoot>
        </table>
        <?php $paid = array_filter($ledger, static fn(array $p): bool => $p['status'] === 'success'); ?>
        <?php if ($paid !== []): ?>
            <h3 class="ash-paper__h3">Payments received</h3>
            <table class="ash-paper__table">
                <tbody>
                <?php foreach ($paid as $p): ?>
                    <tr><td><?= e(format_datetime($p['created_at'])) ?></td><td><?= e(PaymentMethods::label((string) $p['method'])) ?></td><td><?= e((string) ($p['reference'] ?? '')) ?></td><td class="num"><?= e($m($p['amount'])) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <p class="ash-paper__foot">Thank you for supporting <?= e($site) ?>.</p>
    </article>
</div>
