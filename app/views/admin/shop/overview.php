<?php
/**
 * Admin > Shop overview. OWNER: ADMIN. Vars: $summary (ShopStats::summary, null without shop.orders), $low (ShopStats::lowStock), $recent, $by_day, $money (may see money figures).
 * Figures are the services' (ShopStats): this view only lays them out. Nothing here is invented: a new shop shows zeros and says so.
 */
use App\Services\Shop\Money;
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\ShopSettings;

$cur = $summary['currency'] ?? ShopSettings::currency();
$open = ShopSettings::isOpen();
$samples = (int) \App\Core\Database::value("SELECT COUNT(*) FROM shop_products WHERE is_sample = 1", [], 0);
$ready = PaymentMethods::available(true);
$from = (string) ($from ?? '');
$to = (string) ($to ?? '');
$maxDay = 1;
foreach ($by_day as $d) { $maxDay = max($maxDay, (int) $d['orders']); }
$totalDayOrders = array_sum(array_column($by_day, 'orders'));
$actions = [];
if (can('shop.manage')) { $actions[] = ['label' => 'Add a product', 'url' => url('admin/shop/products/create'), 'icon' => 'plus']; }
$actions[] = ['label' => 'View the shop', 'url' => url('shop'), 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-shop.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="ash">
    <?= partial('partials/admin/page-header', ['title' => 'Shop', 'subtitle' => 'Orders, sales and what is running low.', 'actions' => $actions]) ?>

    <?php if (!$open): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title">The shop is closed to visitors.</strong>
            They can look at the products but cannot order. <?= can('shop.settings') ? 'Open it in <a href="' . e(url('admin/shop/settings')) . '">Shop settings</a> once the products, delivery and payments are ready.' : 'Ask an administrator to open it when everything is ready.' ?></div></div>
    <?php endif; ?>
    <?php if ($samples > 0): ?>
        <div class="alert alert--info" role="status"><?= icon('info') ?><div class="alert__body"><strong class="alert__title">The shop still has <?= (int) $samples ?> sample product<?= $samples === 1 ? '' : 's' ?>.</strong>
            Their names, prices and pictures are placeholders to show how the shop looks. <?= can('shop.manage') ? '<a href="' . e(url('admin/shop/products')) . '">Edit or delete them</a> and add your real items.' : 'An administrator needs to replace them.' ?></div></div>
    <?php endif; ?>
    <?php if ($open && $ready === []): ?>
        <div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><strong class="alert__title">No payment method is ready.</strong> Customers cannot check out. <?= can('shop.settings') ? '<a href="' . e(url('admin/shop/payments')) . '">Set up the payment methods</a>.' : '' ?></div></div>
    <?php endif; ?>

    <?php if ($summary !== null): ?>
        <form class="filters ash-period" method="get" action="<?= e(url('admin/shop')) ?>">
            <div class="field"><label class="field__label" for="sp-from">From</label><input class="input" id="sp-from" type="date" name="from" value="<?= e($from) ?>"></div>
            <div class="field"><label class="field__label" for="sp-to">To</label><input class="input" id="sp-to" type="date" name="to" value="<?= e($to) ?>"></div>
            <div class="filters__actions"><button class="btn btn--primary" type="submit">Show</button><?php if ($from !== '' || $to !== ''): ?><a class="btn btn--ghost" href="<?= e(url('admin/shop')) ?>">All time</a><?php endif; ?></div>
            <p class="ash-hint ash-period__note">Figures cover <?= $from !== '' || $to !== '' ? 'the dates you chose' : 'every order so far' ?>. "Paid" counts orders whose payment was received; cancelled and refunded orders are left out.</p>
        </form>

        <div class="stat-grid">
            <a class="stat-tile" href="<?= e(url('admin/shop/orders')) ?>"><span class="stat-tile__icon"><?= icon('shopping-bag') ?></span><span><span class="stat-tile__value"><?= (int) $summary['orders'] ?></span><span class="stat-tile__label">Orders (<?= (int) $summary['units'] ?> items)</span></span></a>
            <a class="stat-tile" href="<?= e(url('admin/shop/orders?payment_status=paid')) ?>"><span class="stat-tile__icon"><?= icon('banknote') ?></span><span><span class="stat-tile__value ash-money"><?= e(Money::format((int) $summary['revenue'], $cur)) ?></span><span class="stat-tile__label">Paid so far</span></span></a>
            <a class="stat-tile" href="<?= e(url('admin/shop/orders?payment_status=unpaid')) ?>"><span class="stat-tile__icon"><?= icon('clock') ?></span><span><span class="stat-tile__value ash-money"><?= e(Money::format((int) $summary['outstanding'], $cur)) ?></span><span class="stat-tile__label">Still to be paid</span></span></a>
            <a class="stat-tile<?= (int) $summary['new_orders'] > 0 ? ' is-alert' : '' ?>" href="<?= e(url('admin/shop/orders?needs_action=1')) ?>"><span class="stat-tile__icon"><?= icon('alert-circle') ?></span><span><span class="stat-tile__value"><?= (int) $summary['new_orders'] ?></span><span class="stat-tile__label">Need attention<?= (int) $summary['review'] > 0 ? ' (' . (int) $summary['review'] . ' payment' . ((int) $summary['review'] === 1 ? '' : 's') . ' to check)' : '' ?></span></span></a>
        </div>

        <div class="card-grid">
            <section class="card" aria-labelledby="ov-days">
                <div class="card__header"><h2 class="card__title" id="ov-days">Orders in the last 30 days</h2><span class="result-count"><?= (int) $totalDayOrders ?> order<?= $totalDayOrders === 1 ? '' : 's' ?></span></div>
                <div class="card__body">
                    <?php if ($totalDayOrders === 0): ?>
                        <p class="ash-hint">No orders in the last 30 days.</p>
                    <?php else: ?>
                        <div class="ash-bars" role="img" aria-label="Orders per day over the last 30 days: <?= (int) $totalDayOrders ?> in total">
                            <?php foreach ($by_day as $d): $h = (int) $d['orders'] > 0 ? max(6, (int) round(((int) $d['orders'] / $maxDay) * 100)) : 2; ?>
                                <span class="ash-bars__col" title="<?= e(format_date($d['date'], 'j M')) ?>: <?= (int) $d['orders'] ?> order<?= (int) $d['orders'] === 1 ? '' : 's' ?>"><span class="ash-bars__bar<?= (int) $d['orders'] > 0 ? ' has-value' : '' ?>" style="height:<?= $h ?>%"></span></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="ash-bars__axis" aria-hidden="true"><span><?= e(format_date($by_day[0]['date'], 'j M')) ?></span><span><?= e(format_date(end($by_day)['date'], 'j M')) ?></span></div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card" aria-labelledby="ov-method">
                <div class="card__header"><h2 class="card__title" id="ov-method">Money received, by way of paying</h2></div>
                <div class="card__body">
                    <?php if ($summary['revenue_by_method'] === []): ?>
                        <p class="ash-hint">No payment has been received yet.</p>
                    <?php else: ?>
                        <ul class="ash-rows">
                            <?php foreach ($summary['revenue_by_method'] as $r): ?>
                                <li><span><?= e(PaymentMethods::label((string) $r['method'])) ?> <small>(<?= (int) $r['orders'] ?> order<?= (int) $r['orders'] === 1 ? '' : 's' ?>)</small></span><strong><?= e(Money::format((int) $r['amount'], $cur)) ?></strong></li>
                            <?php endforeach; ?>
                            <li class="ash-rows__total"><span>Average order</span><strong><?= e(Money::format((int) $summary['average'], $cur)) ?></strong></li>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="card-grid">
            <section class="card" aria-labelledby="ov-best">
                <div class="card__header"><h2 class="card__title" id="ov-best">Best sellers</h2></div>
                <div class="card__body">
                    <?php if ($summary['units_by_product'] === []): ?>
                        <p class="ash-hint">Nothing sold yet.</p>
                    <?php else: ?>
                        <ol class="ash-rows ash-rows--numbered">
                            <?php foreach ($summary['units_by_product'] as $r): ?>
                                <li><span><?= e($r['name']) ?></span><span><strong><?= (int) $r['units'] ?></strong> sold &middot; <?= e(Money::format((int) $r['amount'], $cur)) ?></span></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card" aria-labelledby="ov-low">
                <div class="card__header"><h2 class="card__title" id="ov-low">Running low</h2></div>
                <div class="card__body">
                    <?php if ($low === []): ?>
                        <p class="ash-hint"><?= icon('check-circle', 'icon--sm') ?> Nothing is running low.</p>
                    <?php else: ?>
                        <ul class="ash-rows">
                            <?php foreach ($low as $l): ?>
                                <li><span><?php if (can('shop.manage')): ?><a href="<?= e(url('admin/shop/products/' . (int) $l['id'] . '/edit')) ?>"><?= e($l['name']) ?></a><?php else: ?><?= e($l['name']) ?><?php endif; ?><?= $l['status'] === 'draft' ? ' <small>(draft)</small>' : '' ?></span><span class="badge badge--<?= $l['out'] ? 'danger' : 'warning' ?>"><?= $l['out'] ? 'Sold out' : (int) $l['qty'] . ' left' ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <section class="card" aria-labelledby="ov-recent">
            <div class="card__header"><h2 class="card__title" id="ov-recent">Newest orders</h2><a class="btn btn--outline btn--sm" href="<?= e(url('admin/shop/orders')) ?>">All orders</a></div>
            <?php if ($recent === []): ?>
                <div class="card__body"><p class="ash-hint">No orders yet. When a visitor orders, it shows here and you get an e-mail.</p></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead><tr><th>Order</th><th>Status</th><th>Payment</th><th class="col-num">Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent as $o): ?>
                            <tr>
                                <td data-label="Order"><a class="cell-title" href="<?= e(url('admin/shop/orders/' . (int) $o['id'])) ?>"><?= e($o['order_number']) ?></a><span class="cell-sub"><?= e(format_datetime($o['created_at'])) ?></span></td>
                                <td data-label="Status"><?= partial('partials/admin/shop-badge', ['kind' => 'order', 'value' => $o['status']]) ?></td>
                                <td data-label="Payment"><?= partial('partials/admin/shop-badge', ['kind' => 'payment', 'value' => $o['payment_status']]) ?></td>
                                <td class="col-num" data-label="Total"><?= e(Money::format(Money::fromDb($o['total']), (string) $o['currency'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="card" aria-labelledby="ov-low2"><div class="card__header"><h2 class="card__title" id="ov-low2">Running low</h2></div><div class="card__body">
            <?php if ($low === []): ?><p class="ash-hint">Nothing is running low.</p><?php else: ?><ul class="ash-rows"><?php foreach ($low as $l): ?><li><span><a href="<?= e(url('admin/shop/products/' . (int) $l['id'] . '/edit')) ?>"><?= e($l['name']) ?></a></span><span class="badge badge--<?= $l['out'] ? 'danger' : 'warning' ?>"><?= $l['out'] ? 'Sold out' : (int) $l['qty'] . ' left' ?></span></li><?php endforeach; ?></ul><?php endif; ?>
        </div></section>
    <?php endif; ?>
</div>
