<?php
/**
 * PRINTABLE RECEIPT. OWNER: STOREFRONT. Same variables as order.php. The site chrome is hidden when printing (scss/pages/_shop.scss, body class is-shop-receipt).
 */
use App\Services\Shop\Money;
use App\Services\Shop\ShopSettings;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/shop.js')) ?>" defer></script>
<?php $this->end();
$o = $order;
$cur = (string) $o['currency'];
$pickup = ShopSettings::pickup();
$site = (string) setting('site_name', 'Lumina Africa Foundation');
?>
<section class="section sh-receipt-wrap">
    <div class="container container--narrow">
        <article class="sh-receipt">
            <header class="sh-receipt__head">
                <img class="sh-receipt__logo" src="<?= e(logo_url(80)) ?>" alt="" width="64" height="64">
                <div>
                    <p class="sh-receipt__site"><?= e($site) ?></p>
                    <h1 class="sh-receipt__title"><?= e(content('shop', 'order.receipt_title')) ?> <?= e($o['order_number']) ?></h1>
                </div>
            </header>
            <dl class="sh-receipt__meta">
                <div><dt><?= e(content('shop', 'order.date_label')) ?></dt><dd><?= e(format_date($o['created_at'], 'j F Y')) ?></dd></div>
                <div><dt><?= e(content('shop', 'order.status_label')) ?></dt><dd><?= e($status) ?></dd></div>
                <div><dt><?= e(content('shop', 'order.payment_label')) ?></dt><dd><?= e($paystatus) ?></dd></div>
                <div><dt><?= e(content('shop', 'order.method_label')) ?></dt><dd><?= e($pay['label']) ?></dd></div>
            </dl>
            <table class="sh-receipt__table">
                <thead><tr><th scope="col"><?= e(content('shop', 'cart.th_item')) ?></th><th scope="col" class="num"><?= e(content('shop', 'cart.th_qty')) ?></th><th scope="col" class="num"><?= e(content('shop', 'cart.th_total')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr><td><?= e($it['name']) ?><?= (string) ($it['options_text'] ?? '') !== '' ? '<br><small>' . e($it['options_text']) . '</small>' : '' ?></td><td class="num"><?= (int) $it['qty'] ?></td><td class="num"><?= e(Money::format(Money::fromDb($it['line_total']), $cur)) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="2"><?= e(content('shop', 'order.subtotal_label')) ?></td><td class="num"><?= e($text['subtotal']) ?></td></tr>
                    <tr><td colspan="2"><?= e(content('shop', 'order.delivery_label')) ?></td><td class="num"><?= e($o['delivery_method'] === 'pickup' ? content('shop', 'cart.delivery_none') : ((float) $o['delivery_fee'] <= 0 ? content('shop', 'cart.delivery_free') : $text['delivery'])) ?></td></tr>
                    <tr class="total"><td colspan="2"><?= e(content('shop', 'order.total_label')) ?></td><td class="num"><?= e($text['total']) ?></td></tr>
                </tfoot>
            </table>
            <div class="sh-receipt__cols">
                <div>
                    <h2><?= e(content('shop', 'order.customer_title')) ?></h2>
                    <p><?= e($o['customer_name']) ?><br><?= e($o['customer_email']) ?><br><?= e($o['customer_phone']) ?></p>
                </div>
                <div>
                    <h2><?= e(content('shop', $o['delivery_method'] === 'delivery' ? 'order.deliver_title' : 'order.pickup_title')) ?></h2>
                    <p><?= $o['delivery_method'] === 'delivery' ? nl2br(e((string) $o['delivery_address'])) : nl2br(e($pickup['address'])) . ($pickup['hours'] !== '' ? '<br>' . e($pickup['hours']) : '') ?></p>
                </div>
            </div>
            <p class="sh-receipt__foot"><?= e(content('shop', 'order.contact_text')) ?></p>
        </article>
        <p class="sh-receipt__actions">
            <button class="btn btn--primary btn--caps" type="button" data-shop-print hidden><?= icon('file-text', 'icon--sm') ?> <?= e(content('shop', 'order.print_label')) ?></button>
            <a class="btn btn--outline btn--caps" href="<?= e(url('shop/order/' . $o['token'])) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('shop', 'order.continue_label')) ?></a>
        </p>
    </div>
</section>
