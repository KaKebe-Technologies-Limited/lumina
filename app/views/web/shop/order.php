<?php
/**
 * ORDER PAGE / confirmation (private page, opened by the secret link). OWNER: STOREFRONT. Vars: $order, $items, $pay (OrderService::paymentInfo), $text (subtotal, delivery, total,
 * balance), $status, $paystatus. Forms: POST shop/pay/{token} (Pay now / Pay again) and POST shop/order/{token}/reference (field `reference`).
 */
use App\Services\Shop\Money;
use App\Services\Shop\ShopSettings;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/shop.js')) ?>" defer></script>
<?php $this->end();
$o = $order;
$token = (string) $o['token'];
$cur = (string) $o['currency'];
$booking = $o['payment_method'] === 'book';
$cancelled = in_array($o['status'], ['cancelled', 'refunded'], true);
$isPaid = $o['payment_status'] === 'paid';
$pickup = ShopSettings::pickup();
$steps = ['pending', 'confirmed', 'processing', $o['delivery_method'] === 'delivery' ? 'shipped' : 'ready', 'completed'];
$flow = ['pending' => 0, 'confirmed' => 1, 'processing' => 2, 'ready' => 3, 'shipped' => 3, 'completed' => 4];
$at = $flow[$o['status']] ?? -1;
$tone = $cancelled ? 'danger' : ($isPaid ? 'success' : ($o['payment_status'] === 'failed' ? 'danger' : 'warning'));
?>
<?= partial('partials/page-hero', [
    'title'   => (string) content('shop', $booking ? 'order.title_booking' : 'order.title'),
    'lead'    => str_replace(['{order_number}', '{email}'], [(string) $o['order_number'], (string) $o['customer_email']], (string) content('shop', 'order.lead')),
    'sheet'   => 'tint',
]) ?>

<section class="section section--tint sh-order" data-no-justify aria-label="<?= e($o['order_number']) ?>">
    <div class="container">
        <div class="sh-order__head">
            <div>
                <span class="sh-order__label"><?= e(content('shop', 'order.number_label')) ?></span>
                <strong class="sh-order__number"><?= e($o['order_number']) ?></strong>
                <span class="sh-order__date"><?= e(format_date($o['created_at'], 'j F Y')) ?></span>
            </div>
            <div class="sh-order__badges">
                <span class="sh-pill sh-pill--neutral"><span class="visually-hidden"><?= e(content('shop', 'order.status_label')) ?>: </span><?= e($status) ?></span>
                <span class="sh-pill sh-pill--<?= e($tone) ?>"><span class="visually-hidden"><?= e(content('shop', 'order.payment_label')) ?>: </span><?= e($paystatus) ?></span>
            </div>
        </div>

        <?php if (!$cancelled && $at >= 0): ?>
            <ol class="sh-track" aria-label="<?= e(content('shop', 'order.status_label')) ?>">
                <?php foreach ($steps as $i => $s): ?>
                    <li class="sh-track__step<?= $i < $at ? ' is-done' : '' ?><?= $i === $at ? ' is-current' : '' ?>"<?= $i === $at ? ' aria-current="step"' : '' ?>><span class="sh-track__dot" aria-hidden="true"><?= $i < $at ? icon('check', 'icon--sm') : '' ?></span><span class="sh-track__text"><?= e(content('shop', 'status.' . $s)) ?></span></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <p class="sh-keep"><?= icon('lock', 'icon--sm') ?> <span><?= e(content('shop', 'order.keep_note')) ?></span></p>

        <div class="sh-order__layout">
            <div class="sh-order__main">
                <?php if ($isPaid): ?>
                    <div class="sh-alert sh-alert--success" role="status"><?= icon('check-circle', 'icon--sm') ?> <span><?= e(content('shop', 'order.paid_note')) ?></span></div>
                <?php elseif ($cancelled): ?>
                    <div class="sh-alert sh-alert--info" role="status"><?= icon('info', 'icon--sm') ?> <span><?= e(content('shop', $o['status'] === 'refunded' ? 'order.refunded_note' : 'order.closed_note')) ?></span></div>
                <?php else: ?>
                    <section class="sh-card-box" aria-labelledby="sh-pay-title">
                        <h2 class="sh-card-box__title" id="sh-pay-title"><?= icon('banknote', 'icon--sm') ?> <?= e(content('shop', 'order.pay_title')) ?></h2>
                        <p class="sh-method"><strong><?= e($pay['label']) ?></strong></p>
                        <?php if ($pay['instructions'] !== ''): ?><p class="sh-instructions"><?= nl2br(e($pay['instructions'])) ?></p><?php endif; ?>

                        <?php if ($o['payment_status'] === 'review'): ?><p class="sh-alert sh-alert--info"><?= icon('info', 'icon--sm') ?> <span><?= e(content('shop', 'order.review_note')) ?></span></p><?php endif; ?>
                        <?php if ($o['payment_status'] === 'failed'): ?><p class="sh-alert sh-alert--error" role="status"><?= icon('alert-circle', 'icon--sm') ?> <span><?= e(content('shop', 'order.failed_note')) ?></span></p><?php endif; ?>
                        <?php if ($pay['awaiting_verification'] || ($o['payment_status'] === 'pending' && $o['payment_method'] === 'flutterwave')): ?><p class="sh-alert sh-alert--info" role="status"><?= icon('clock', 'icon--sm') ?> <span><?= e(content('shop', 'order.pending_note')) ?></span></p><?php endif; ?>

                        <?php if ($pay['details'] !== []): ?>
                            <dl class="sh-paydetails">
                                <?php foreach ($pay['details'] as $d): ?>
                                    <?php if (isset($d['number'])): ?>
                                        <div><dt><?= e($d['label']) ?><?= $d['name'] !== '' ? ' <span>(' . e($d['name']) . ')</span>' : '' ?></dt><dd><span class="sh-copyable"><?= e($d['number']) ?></span> <button class="sh-copybtn" type="button" data-shop-copy="<?= e(preg_replace('/\s+/', '', (string) $d['number'])) ?>" data-copied="<?= e(content('shop', 'order.copied')) ?>" hidden aria-label="<?= e(content('shop', 'order.copy_number')) ?>: <?= e($d['number']) ?>"><?= icon('copy', 'icon--sm') ?></button></dd></div>
                                    <?php else: ?>
                                        <div><dt><?= e($d['label']) ?></dt><dd><?= e($d['value']) ?></dd></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>

                        <?php if (in_array($o['payment_method'], ['momo', 'bank'], true)): ?>
                            <p class="sh-refline"><?= icon('info', 'icon--sm') ?> <strong><?= e(str_replace('{order_number}', (string) $o['order_number'], (string) content('shop', 'payment.reference_line'))) ?></strong></p>
                        <?php endif; ?>

                        <?php if ($pay['can_pay_online']): ?>
                            <form method="post" action="<?= e(url('shop/pay/' . $token)) ?>" class="sh-payform">
                                <?= csrf_field() ?>
                                <button class="btn btn--cta btn--caps btn--lg" type="submit"><?= icon('lock', 'icon--sm') ?> <?= e(content('shop', in_array($o['payment_status'], ['failed', 'pending'], true) ? 'order.pay_again_label' : 'order.pay_now_label')) ?> &middot; <?= e($text['balance']) ?></button>
                            </form>
                        <?php endif; ?>

                        <?php if ($pay['can_enter_reference']): ?>
                            <form method="post" action="<?= e(url('shop/order/' . $token . '/reference')) ?>" class="sh-refform" novalidate>
                                <?= csrf_field() ?><?= honeypot_field() ?>
                                <h3 class="sh-refform__title"><?= e(content('shop', 'order.ref_title')) ?></h3>
                                <div class="sh-refform__row">
                                    <?= partial('partials/form-field', ['name' => 'reference', 'label' => (string) content('shop', $o['payment_method'] === 'momo' ? 'order.ref_label_momo' : 'order.ref_label_bank'), 'required' => true, 'maxlength' => 60, 'value' => '', 'autocomplete' => 'off', 'help' => (string) content('shop', 'order.ref_help')]) ?>
                                    <button class="btn btn--primary btn--caps" type="submit"><?= e(content('shop', 'order.ref_button')) ?></button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <section class="sh-card-box" aria-labelledby="sh-items-title">
                    <h2 class="sh-card-box__title" id="sh-items-title"><?= icon('shopping-bag', 'icon--sm') ?> <?= e(content('shop', 'order.items_title')) ?></h2>
                    <ul class="sh-mini sh-mini--order">
                        <?php foreach ($items as $it): ?>
                            <li class="sh-mini__item">
                                <span class="sh-mini__media"><?php if (!empty($it['image'])): ?><img class="sh-mini__img" src="<?= e(img_url((string) $it['image'])) ?>" alt="" width="56" height="56" loading="lazy"><?php endif; ?><span class="sh-mini__qty" aria-hidden="true"><?= (int) $it['qty'] ?></span></span>
                                <span class="sh-mini__info"><span class="sh-mini__name"><span class="visually-hidden"><?= e(str_replace('{n}', (string) $it['qty'], (string) content('shop', 'order.qty_label'))) ?> </span><?= e($it['name']) ?></span><?php if ((string) ($it['options_text'] ?? '') !== ''): ?><span class="sh-mini__opts"><?= e($it['options_text']) ?></span><?php endif; ?></span>
                                <span class="sh-mini__price"><?= e(Money::format(Money::fromDb($it['line_total']), $cur)) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <dl class="sh-totals">
                        <div><dt><?= e(content('shop', 'order.subtotal_label')) ?></dt><dd><?= e($text['subtotal']) ?></dd></div>
                        <div><dt><?= e(content('shop', 'order.delivery_label')) ?></dt><dd><?= e($o['delivery_method'] === 'pickup' ? content('shop', 'cart.delivery_none') : ((float) $o['delivery_fee'] <= 0 ? content('shop', 'cart.delivery_free') : $text['delivery'])) ?></dd></div>
                        <div class="sh-totals__total"><dt><?= e(content('shop', 'order.total_label')) ?></dt><dd><?= e($text['total']) ?></dd></div>
                    </dl>
                </section>
            </div>

            <aside class="sh-order__side">
                <section class="sh-card-box" aria-labelledby="sh-who-title">
                    <h2 class="sh-card-box__title" id="sh-who-title"><?= icon('user', 'icon--sm') ?> <?= e(content('shop', 'order.customer_title')) ?></h2>
                    <p class="sh-address-text"><strong><?= e($o['customer_name']) ?></strong><br><?= e($o['customer_email']) ?><br><?= e($o['customer_phone']) ?></p>
                    <h3 class="sh-card-box__sub"><?= e(content('shop', $o['delivery_method'] === 'delivery' ? 'order.deliver_title' : 'order.pickup_title')) ?></h3>
                    <?php if ($o['delivery_method'] === 'delivery'): ?>
                        <p class="sh-address-text"><?= nl2br(e((string) $o['delivery_address'])) ?></p>
                    <?php else: ?>
                        <p class="sh-address-text"><?= nl2br(e($pickup['address'])) ?><?= $pickup['hours'] !== '' ? '<br>' . e($pickup['hours']) : '' ?></p>
                    <?php endif; ?>
                </section>
                <div class="sh-help">
                    <p><?= e(content('shop', 'order.contact_text')) ?></p>
                    <a class="btn btn--outline btn--sm btn--caps" href="<?= e(url('contact')) ?>"><?= e(content('shop', 'order.contact_label')) ?></a>
                </div>
                <div class="sh-order__links">
                    <a class="btn btn--outline btn--caps" href="<?= e(url('shop/order/' . $token . '/receipt')) ?>"><?= icon('file-text', 'icon--sm') ?> <?= e(content('shop', 'order.print_label')) ?></a>
                    <a class="link-arrow" href="<?= e(url('shop')) ?>"><?= e(content('shop', 'order.continue_label')) ?> <?= icon('arrow-right', 'icon--sm') ?></a>
                </div>
            </aside>
        </div>
    </div>
</section>
