<?php
/**
 * Admin > Shop > one order. OWNER: ADMIN. Vars: $order, $items, $ledger, $log, $next (statuses allowed from here), $balance (minor units still to pay), $methods (PaymentMethods::all()),
 * $refundable (minor units held for a CANCELLED order that staff can record as refunded), $open_attempt (an online try that may still turn into money).
 * Every button posts to ShopOrdersController (status, confirm, payment, payment-status, recheck, note, resend, delete); the money and status rules are the services'.
 * Contains the buyer's personal data: ability shop.orders.
 */
use App\Services\Shop\Money;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\ShopSettings;

$o = $order;
$id = (int) $o['id'];
$cur = (string) $o['currency'];
$base = url('admin/shop/orders/' . $id);
$m = static fn($v): string => Money::format(Money::fromDb($v), $cur);
$isPaid = $o['payment_status'] === 'paid';
$closed = in_array($o['status'], ['cancelled', 'refunded'], true);
$booking = $o['payment_method'] === 'book';
$online = $o['payment_method'] === 'flutterwave';
$manual = in_array($o['payment_method'], ['momo', 'bank'], true);
$canRecord = !in_array($o['payment_status'], ['paid', 'refunded'], true) && !$closed;
$phoneHref = 'tel:' . preg_replace('/[^0-9+]/', '', (string) $o['customer_phone']);
$waNumber = preg_replace('/[^0-9]/', '', (string) $o['customer_phone']);
$statusNames = ['confirmed' => 'Confirm the order', 'processing' => 'Being prepared', 'ready' => 'Ready for pickup', 'shipped' => 'Sent out for delivery', 'completed' => 'Completed (handed over)', 'cancelled' => 'Cancel the order', 'refunded' => 'Refunded', 'pending' => 'Back to new'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-shop.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="ash ash-order">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Order ' . $o['order_number'],
        'subtitle' => 'Placed ' . format_datetime($o['created_at']) . ' by ' . $o['customer_name'] . '.',
        'actions'  => [
            ['label' => 'Packing slip', 'url' => $base . '/packing-slip', 'class' => 'btn--outline', 'icon' => 'printer', 'target' => '_blank'],
            ['label' => 'Receipt', 'url' => $base . '/receipt', 'class' => 'btn--outline', 'icon' => 'file-text', 'target' => '_blank'],
            ['label' => 'All orders', 'url' => url('admin/shop/orders'), 'class' => 'btn--ghost', 'icon' => 'arrow-left'],
        ],
    ]) ?>

    <div class="ash-order__badges">
        <?= partial('partials/admin/shop-badge', ['kind' => 'order', 'value' => $o['status']]) ?>
        <?= partial('partials/admin/shop-badge', ['kind' => 'payment', 'value' => $o['payment_status']]) ?>
        <span class="badge badge--outline"><?= e(PaymentMethods::label((string) $o['payment_method'])) ?></span>
        <?php if (!$isPaid && !$closed && $o['status'] === 'pending' && !empty($o['hold_until'])): ?><span class="ash-order__hold"><?= icon('clock', 'icon--sm') ?> Stock is held until <?= e(format_datetime($o['hold_until'])) ?></span><?php endif; ?>
    </div>

    <?php if ($o['payment_status'] === 'review'): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title">This payment needs a person to look at it.</strong>
            Money arrived that does not match the order (a different amount or currency, or a payment for a cancelled order), or the gateway could not confirm it. Check it in your Flutterwave dashboard, mobile-money or bank statement, then <?= $closed ? 'record the refund once you have returned the money, or mark it as failed if nothing was received' : 'record it as paid or mark it as failed' ?> below.</div></div>
    <?php endif; ?>

    <div class="card-grid card-grid--main-aside">
        <div>
            <?php // ------------------------------------------------------------------------------------ next step ?>
            <section class="card" aria-labelledby="o-status">
                <div class="card__header"><h2 class="card__title" id="o-status">Order progress</h2></div>
                <div class="card__body">
                    <?php if ($booking && $o['status'] === 'pending'): ?>
                        <form method="post" action="<?= e($base . '/confirm') ?>" class="ash-inline">
                            <?= csrf_field() ?>
                            <p class="ash-hint">This is a booking. Confirm it to keep the items reserved for the customer (the hold stops expiring) and send them a confirmation e-mail.</p>
                            <button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Confirm the booking</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($next === []): ?>
                        <p class="ash-hint">This order is <?= e(strtolower(OrderService::statusLabel((string) $o['status']))) ?>: nothing more can be changed in its progress.</p>
                    <?php else: ?>
                        <form method="post" action="<?= e($base . '/status') ?>" class="ash-status-form" data-confirm-unpaid>
                            <?= csrf_field() ?>
                            <div class="form-grid">
                                <div class="field">
                                    <label class="field__label" for="o-next">Move the order to</label>
                                    <select class="select" id="o-next" name="status" data-status-select>
                                        <?php foreach ($next as $s): ?><option value="<?= e($s) ?>"><?= e($statusNames[$s] ?? OrderService::statusLabel($s)) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label class="field__label" for="o-note">Short note (optional)</label>
                                    <input class="input" id="o-note" name="note" maxlength="300" placeholder="Only your team sees this">
                                </div>
                            </div>
                            <?php if (!$isPaid && in_array('completed', $next, true)): ?>
                                <label class="check ash-check" data-unpaid-only hidden><input type="checkbox" name="allow_unpaid" value="1"> <span>This order needs no payment (free or waived), so it can be completed unpaid</span></label>
                            <?php endif; ?>
                            <p class="ash-hint" data-status-hint>The customer is e-mailed when the order is confirmed, ready, sent out, completed, cancelled or refunded. Cancelling returns the reserved stock to the shop.</p>
                            <button class="btn btn--primary" type="submit">Save the new status</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>

            <?php // ------------------------------------------------------------------------------------ items ?>
            <section class="card" aria-labelledby="o-items">
                <div class="card__header"><h2 class="card__title" id="o-items">Items</h2></div>
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead><tr><th>Item</th><th class="col-num">Price</th><th class="col-num">Qty</th><th class="col-num">Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td data-label="Item">
                                    <span class="ash-item">
                                        <?php if (!empty($it['image'])): ?><img class="ash-item__img" src="<?= e(img_url((string) $it['image'])) ?>" alt="" width="44" height="44" loading="lazy"><?php endif; ?>
                                        <span><span class="cell-title"><?= e($it['name']) ?></span><?php if ((string) ($it['options_text'] ?? '') !== ''): ?><span class="cell-sub"><?= e($it['options_text']) ?></span><?php endif; ?><?php if ((string) ($it['sku'] ?? '') !== ''): ?><span class="cell-sub">Code <?= e($it['sku']) ?></span><?php endif; ?></span>
                                    </span>
                                </td>
                                <td class="col-num" data-label="Price"><?= e($m($it['unit_price'])) ?></td>
                                <td class="col-num" data-label="Qty"><?= (int) $it['qty'] ?></td>
                                <td class="col-num" data-label="Total"><?= e($m($it['line_total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card__body">
                    <dl class="ash-totals">
                        <div><dt>Subtotal</dt><dd><?= e($m($o['subtotal'])) ?></dd></div>
                        <div><dt>Delivery</dt><dd><?= e($o['delivery_method'] === 'pickup' ? 'Collect (no charge)' : $m($o['delivery_fee'])) ?></dd></div>
                        <div class="ash-totals__total"><dt>Total</dt><dd><?= e($m($o['total'])) ?></dd></div>
                        <div><dt>Still to be paid</dt><dd><?= e(Money::format((int) $balance, $cur)) ?></dd></div>
                    </dl>
                </div>
            </section>

            <?php // ------------------------------------------------------------------------------------ payments ?>
            <section class="card" aria-labelledby="o-pay">
                <div class="card__header"><h2 class="card__title" id="o-pay">Payments</h2></div>
                <?php if ($ledger === []): ?>
                    <div class="card__body"><p class="ash-hint">No payment has been recorded for this order yet.</p></div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table table--stack">
                            <thead><tr><th>When</th><th>Method</th><th>Reference</th><th class="col-num">Amount</th><th>Result</th></tr></thead>
                            <tbody>
                            <?php foreach ($ledger as $p): ?>
                                <tr>
                                    <td data-label="When"><?= e(format_datetime($p['created_at'])) ?><span class="cell-sub">by <?= e($p['source']) ?></span></td>
                                    <td data-label="Method"><?= e(PaymentMethods::label((string) $p['method'])) ?></td>
                                    <td data-label="Reference"><span class="ash-ref"><?= e((string) ($p['reference'] ?? '')) ?></span></td>
                                    <td class="col-num" data-label="Amount"><?= e($m($p['amount'])) ?></td>
                                    <td data-label="Result"><?= partial('partials/admin/shop-badge', ['kind' => 'payment', 'value' => in_array($p['status'], ['success', 'duplicate'], true) ? 'paid' : ($p['status'] === 'pending' ? 'pending' : ($p['status'] === 'review' ? 'review' : (in_array($p['status'], ['refunded', 'superseded'], true) ? 'refunded' : 'failed'))), 'text' => ucfirst((string) $p['status'])]) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($canRecord): ?>
                    <div class="card__body ash-pay">
                        <?php if ($manual): ?>
                            <div class="ash-check-list">
                                <strong>Before you mark a mobile-money or bank payment as paid</strong>
                                <ol>
                                    <li>Open your <?= e($o['payment_method'] === 'momo' ? 'mobile-money' : 'bank') ?> statement and find the payment<?= (string) ($o['payment_reference'] ?? '') !== '' ? ' with the reference the customer sent: <b>' . e($o['payment_reference']) . '</b>' : '' ?>.</li>
                                    <li>Check the amount is at least <b><?= e(Money::format((int) $balance, $cur)) ?></b> and that it is in <?= e($cur) ?>.</li>
                                    <li>Check the same reference has not been used for another order.</li>
                                </ol>
                            </div>
                        <?php elseif ($online): ?>
                            <p class="ash-hint">Online payments are confirmed automatically by Flutterwave. If the customer says they paid but the order is still unpaid, ask the gateway again:</p>
                            <form method="post" action="<?= e($base . '/recheck') ?>" class="ash-inline"><?= csrf_field() ?><button class="btn btn--outline" type="submit"><?= icon('refresh', 'icon--sm') ?> Re-check with Flutterwave</button></form>
                        <?php endif; ?>
                        <form method="post" action="<?= e($base . '/payment') ?>" class="ash-pay__form">
                            <?= csrf_field() ?>
                            <h3 class="ash-subtitle">Record a payment received</h3>
                            <div class="form-grid form-grid--3">
                                <div class="field">
                                    <label class="field__label" for="p-method">Paid by</label>
                                    <select class="select" id="p-method" name="via">
                                        <?php foreach ($methods as $mm): ?><option value="<?= e($mm['key']) ?>"<?= $mm['key'] === $o['payment_method'] ? ' selected' : '' ?>><?= e($mm['label']) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label class="field__label" for="p-ref">Reference or transaction ID</label>
                                    <input class="input" id="p-ref" name="reference" maxlength="80" value="<?= e((string) ($o['payment_reference'] ?? '')) ?>" autocomplete="off">
                                </div>
                                <div class="field">
                                    <label class="field__label" for="p-amount">Amount received (<?= e($cur) ?>)</label>
                                    <input class="input" id="p-amount" name="amount" inputmode="decimal" placeholder="<?= e(Money::format((int) $balance, $cur, false)) ?>" autocomplete="off">
                                    <div class="field__help">Leave empty to record the full amount still to be paid.</div>
                                </div>
                            </div>
                            <button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Mark as paid</button>
                        </form>
                        <form method="post" action="<?= e($base . '/payment-status') ?>" class="ash-pay__form ash-pay__form--status">
                            <?= csrf_field() ?>
                            <h3 class="ash-subtitle">Or set the payment result by hand</h3>
                            <div class="form-grid">
                                <div class="field">
                                    <label class="field__label" for="ps-status">Payment is</label>
                                    <select class="select" id="ps-status" name="payment_status">
                                        <?php foreach (['unpaid' => 'Not paid yet', 'pending' => 'Waiting for confirmation', 'failed' => 'Failed', 'review' => 'Needs checking'] as $k => $l): if ($k === $o['payment_status']) { continue; } ?>
                                            <option value="<?= e($k) ?>"><?= e($l) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field"><label class="field__label" for="ps-note">Short note (optional)</label><input class="input" id="ps-note" name="note" maxlength="300"></div>
                            </div>
                            <button class="btn btn--outline" type="submit">Save</button>
                        </form>
                    </div>
                <?php elseif ($o['status'] === 'cancelled' && $o['payment_status'] !== 'refunded' && ($refundable > 0 || $open_attempt || $o['payment_status'] === 'review')): ?>
                    <?php // a cancelled order can still hold money (paid before the cancel, part paid, or a payment that arrived after the cancel): staff must be able to see it and close it ?>
                    <div class="card__body ash-pay">
                        <?php if ($refundable > 0): ?>
                            <p class="ash-hint"><?= icon('alert-triangle', 'icon--sm') ?> This order is cancelled but <b><?= e(Money::format((int) $refundable, $cur)) ?></b> is recorded for it. Give the money back from your gateway, mobile-money or bank account, then record the refund here so your records match.</p>
                            <form method="post" action="<?= e($base . '/payment-status') ?>" class="ash-pay__form ash-pay__form--status">
                                <?= csrf_field() ?>
                                <input type="hidden" name="payment_status" value="refunded">
                                <div class="form-grid">
                                    <div class="field"><label class="field__label" for="rf-note">Short note (optional)</label><input class="input" id="rf-note" name="note" maxlength="300" placeholder="For example: sent back by mobile money on 21 Oct"></div>
                                </div>
                                <button class="btn btn--primary" type="submit" data-confirm="Record that <?= e(Money::format((int) $refundable, $cur)) ?> was returned to the customer?"><?= icon('check', 'icon--sm') ?> Record the refund</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($online && $open_attempt): ?>
                            <p class="ash-hint">An online payment try of this order is still open at the gateway. If the customer says they paid, ask the gateway again:</p>
                            <form method="post" action="<?= e($base . '/recheck') ?>" class="ash-inline"><?= csrf_field() ?><button class="btn btn--outline" type="submit"><?= icon('refresh', 'icon--sm') ?> Re-check with Flutterwave</button></form>
                        <?php endif; ?>
                        <?php if ($o['payment_status'] === 'review' && $refundable <= 0): ?>
                            <form method="post" action="<?= e($base . '/payment-status') ?>" class="ash-pay__form ash-pay__form--status">
                                <?= csrf_field() ?>
                                <h3 class="ash-subtitle">No money was received?</h3>
                                <input type="hidden" name="payment_status" value="failed">
                                <div class="form-grid"><div class="field"><label class="field__label" for="rv-note">Short note (optional)</label><input class="input" id="rv-note" name="note" maxlength="300"></div></div>
                                <button class="btn btn--outline" type="submit">Mark the payment as failed</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php elseif ($isPaid): ?>
                    <div class="card__body"><p class="ash-hint"><?= icon('check-circle', 'icon--sm') ?> Paid<?= !empty($o['paid_at']) ? ' on ' . e(format_datetime($o['paid_at'])) : '' ?>.<?= in_array('refunded', $next, true) ? ' Refunding the order (in the progress box above) records the refund of the whole amount; the money itself is returned from your gateway, mobile-money or bank account.' : '' ?></p></div>
                <?php endif; ?>
            </section>

            <?php // ------------------------------------------------------------------------------------ timeline ?>
            <section class="card" aria-labelledby="o-log">
                <div class="card__header"><h2 class="card__title" id="o-log">History and notes</h2></div>
                <div class="card__body">
                    <form method="post" action="<?= e($base . '/note') ?>" class="ash-note-form">
                        <?= csrf_field() ?>
                        <label class="field__label" for="o-newnote">Add a note for your team</label>
                        <div class="input-group"><input class="input" id="o-newnote" name="note" maxlength="500" placeholder="For example: called the customer, size swapped"><button class="btn btn--outline" type="submit">Add note</button></div>
                    </form>
                    <ol class="ash-timeline">
                        <?php foreach ($log as $l): ?>
                            <li><time datetime="<?= e((string) $l['created_at']) ?>"><?= e(format_datetime($l['created_at'])) ?></time><span><?= e($l['message']) ?></span></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </section>
        </div>

        <aside class="ash-order__aside">
            <section class="card" aria-labelledby="o-who">
                <div class="card__header"><h2 class="card__title" id="o-who">Customer</h2></div>
                <div class="card__body">
                    <p class="ash-who"><strong><?= e($o['customer_name']) ?></strong></p>
                    <ul class="ash-contact">
                        <li><?= icon('mail', 'icon--sm') ?> <a href="mailto:<?= e($o['customer_email']) ?>"><?= e($o['customer_email']) ?></a></li>
                        <li><?= icon('phone', 'icon--sm') ?> <a href="<?= e($phoneHref) ?>"><?= e($o['customer_phone']) ?></a></li>
                        <?php if ($waNumber !== ''): ?><li><?= icon('whatsapp', 'icon--sm') ?> <a href="https://wa.me/<?= e($waNumber) ?>" target="_blank" rel="noopener">WhatsApp</a></li><?php endif; ?>
                    </ul>
                    <h3 class="ash-subtitle"><?= $o['delivery_method'] === 'delivery' ? 'Deliver to' : 'Collects from' ?></h3>
                    <p class="ash-address"><?php if ($o['delivery_method'] === 'delivery'): ?><?= nl2br(e((string) $o['delivery_address'])) ?><?php else: ?><?= nl2br(e(ShopSettings::pickup()['address'])) ?><?php endif; ?></p>
                    <?php if (trim((string) ($o['customer_note'] ?? '')) !== ''): ?>
                        <h3 class="ash-subtitle">Note from the customer</h3>
                        <p class="ash-address"><?= nl2br(e((string) $o['customer_note'])) ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card" aria-labelledby="o-more">
                <div class="card__header"><h2 class="card__title" id="o-more">Actions</h2></div>
                <div class="card__body ash-actions">
                    <form method="post" action="<?= e($base . '/resend') ?>"><?= csrf_field() ?><button class="btn btn--outline btn--block" type="submit"><?= icon('send', 'icon--sm') ?> E-mail the customer again</button></form>
                    <?php if (can('shop.delete')): ?>
                        <form method="post" action="<?= e($base . '/delete') ?>" data-confirm="Delete order <?= e($o['order_number']) ?> for good? Its history and payment records are removed too. Cancel it instead if you only want to stop it."><?= csrf_field() ?><button class="btn btn--ghost btn--block ash-danger" type="submit"><?= icon('trash', 'icon--sm') ?> Delete this order</button></form>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card" aria-labelledby="o-meta">
                <div class="card__header"><h2 class="card__title" id="o-meta">Details</h2></div>
                <div class="card__body">
                    <dl class="ash-meta">
                        <div><dt>Order number</dt><dd><?= e($o['order_number']) ?></dd></div>
                        <div><dt>Delivery</dt><dd><?= e($o['delivery_method'] === 'delivery' ? 'Delivery' : 'Collect in person') ?></dd></div>
                        <?php if ((string) ($o['payment_reference'] ?? '') !== ''): ?><div><dt>Customer's reference</dt><dd><?= e($o['payment_reference']) ?></dd></div><?php endif; ?>
                        <?php if ($online && (int) ($o['gateway_attempts'] ?? 0) > 0): ?><div><dt>Online payment tries</dt><dd><?= (int) $o['gateway_attempts'] ?></dd></div><?php endif; ?>
                        <?php if (!empty($o['consent_at'])): ?><div><dt>Terms accepted</dt><dd><?= e(format_datetime($o['consent_at'])) ?></dd></div><?php endif; ?>
                    </dl>
                </div>
            </section>
        </aside>
    </div>
</div>
