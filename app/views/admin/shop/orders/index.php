<?php
/**
 * Admin > Shop > Orders. OWNER: ADMIN. Vars: $page (OrderService::search result), $filters. Ability shop.orders; the CSV buttons need shop.export.
 * Orders contain personal data of buyers: only what a person needs to find and open an order is listed here.
 */
use App\Services\Shop\Money;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;
use App\Models\ShopOrder;

$pager = \App\Core\Pagination::make((int) $page['total'], (int) $page['page'], (int) $page['perPage']);
$active = array_filter($filters, static fn($v): bool => $v !== '' && $v !== false && $v !== null);
$qs = http_build_query(array_diff_key($active, ['method' => 1]) + (isset($active['method']) ? ['paid_by' => $active['method']] : []));
$actions = [];
if (can('shop.export')) {
    $actions[] = ['label' => 'Download orders (CSV)', 'url' => url('admin/shop/orders/export') . '?kind=orders' . ($qs !== '' ? '&' . $qs : ''), 'class' => 'btn--outline', 'icon' => 'download'];
    $actions[] = ['label' => 'Download items (CSV)', 'url' => url('admin/shop/orders/export') . '?kind=items' . ($qs !== '' ? '&' . $qs : ''), 'class' => 'btn--outline', 'icon' => 'download'];
}
?>
<div class="ash">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Shop orders',
        'subtitle' => 'Orders contain the personal details of buyers. Open an order to confirm it, record a payment or mark it ready.',
        'actions'  => $actions,
    ]) ?>

    <form class="filters" method="get" action="<?= e(url('admin/shop/orders')) ?>" role="search">
        <div class="field field--grow">
            <label class="field__label" for="of-q">Search</label>
            <input class="input" id="of-q" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Order number, name, e-mail or phone" maxlength="80">
        </div>
        <div class="field">
            <label class="field__label" for="of-status">Order status</label>
            <select class="select" id="of-status" name="status" data-autosubmit>
                <option value="">Any</option>
                <?php foreach (ShopOrder::STATUSES as $s): ?><option value="<?= e($s) ?>"<?= $filters['status'] === $s ? ' selected' : '' ?>><?= e(OrderService::statusLabel($s)) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label class="field__label" for="of-pay">Payment</label>
            <select class="select" id="of-pay" name="payment_status" data-autosubmit>
                <option value="">Any</option>
                <?php foreach (ShopOrder::PAYMENT_STATUSES as $s): ?><option value="<?= e($s) ?>"<?= $filters['payment_status'] === $s ? ' selected' : '' ?>><?= e(OrderService::paymentStatusLabel($s)) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label class="field__label" for="of-method">Paid by</label>
            <select class="select" id="of-method" name="paid_by" data-autosubmit>
                <option value="">Any</option>
                <?php foreach (PaymentMethods::KEYS as $k): ?><option value="<?= e($k) ?>"<?= $filters['method'] === $k ? ' selected' : '' ?>><?= e(PaymentMethods::label($k)) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="of-from">From</label><input class="input" id="of-from" type="date" name="from" value="<?= e($filters['from']) ?>"></div>
        <div class="field"><label class="field__label" for="of-to">To</label><input class="input" id="of-to" type="date" name="to" value="<?= e($filters['to']) ?>"></div>
        <div class="field">
            <label class="check ash-check"><input type="checkbox" name="needs_action" value="1"<?= $filters['needs_action'] ? ' checked' : '' ?> data-autosubmit-check> <span>Needs attention</span></label>
        </div>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit">Filter</button>
            <?php if ($active !== []): ?><a class="btn btn--ghost" href="<?= e(url('admin/shop/orders')) ?>">Clear</a><?php endif; ?>
        </div>
    </form>

    <p class="result-count"><?= (int) $page['total'] ?> order<?= (int) $page['total'] === 1 ? '' : 's' ?><?= $active !== [] ? ' match your filters' : '' ?>.</p>

    <?php if ($page['items'] === []): ?>
        <?= partial('partials/empty-state', ['title' => $active !== [] ? 'No orders match' : 'No orders yet', 'icon' => 'shopping-bag',
            'text' => $active !== [] ? 'Try other filters.' : 'When a visitor places an order it appears here and you get an e-mail.']) ?>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table--stack">
                <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th class="col-num">Total</th><th class="col-actions"><span class="visually-hidden">Open</span></th></tr></thead>
                <tbody>
                <?php foreach ($page['items'] as $o): $needs = $o['status'] === 'pending' || $o['payment_status'] === 'review'; ?>
                    <tr<?= $needs ? ' class="is-unread"' : '' ?>>
                        <td data-label="Order">
                            <a class="cell-title" href="<?= e(url('admin/shop/orders/' . (int) $o['id'])) ?>"><?= e($o['order_number']) ?></a>
                            <span class="cell-sub"><?= e(format_datetime($o['created_at'])) ?> &middot; <?= (int) $o['units'] ?> item<?= (int) $o['units'] === 1 ? '' : 's' ?></span>
                        </td>
                        <td data-label="Customer"><span class="cell-title"><?= e($o['customer_name']) ?></span><span class="cell-sub"><?= e($o['customer_phone']) ?></span></td>
                        <td data-label="Status"><?= partial('partials/admin/shop-badge', ['kind' => 'order', 'value' => $o['status']]) ?></td>
                        <td data-label="Payment"><?= partial('partials/admin/shop-badge', ['kind' => 'payment', 'value' => $o['payment_status']]) ?><span class="cell-sub"><?= e(PaymentMethods::label((string) $o['payment_method'])) ?></span></td>
                        <td class="col-num" data-label="Total"><?= e(Money::format(Money::fromDb($o['total']), (string) $o['currency'])) ?></td>
                        <td class="col-actions"><a class="btn btn--outline btn--sm" href="<?= e(url('admin/shop/orders/' . (int) $o['id'])) ?>">Open<span class="visually-hidden"> order <?= e($o['order_number']) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pager]) ?>
    <?php endif; ?>
</div>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/admin-shop.js')) ?>" defer></script><?php $this->end(); ?>
