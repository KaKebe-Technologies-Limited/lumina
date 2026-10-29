<?php
/** Admin > Pesapal payments. Vars: $fees, $feeCurrencies, $feesReady, $canFees (Academy fees), $donationsOn, $shopOn, $shopOpen, $canShop (where Pesapal is offered), $tables (database tables present), $hasKeys, $mode, $payments, $ipnUrl, $returnUrl. */
use App\Services\Shop\Money;

$tones = ['pending' => 'warning', 'paid' => 'success', 'failed' => 'danger', 'reversed' => 'danger', 'review' => 'warning'];
?>
<?= partial('partials/admin/page-header', ['title' => 'Pesapal payments', 'subtitle' => 'Card, mobile money and bank payments for donations and shop orders.']) ?>

<?php if (!$tables): ?>
<div class="alert alert--error" role="alert"><strong>The Pesapal database tables are missing.</strong> In phpMyAdmin, select the live database, open <b>Import</b> and import the file <code>database/updates/2026-10-pesapal.sql</code> (it only adds two tables and is safe to repeat). Then reload this page.</div>
<?php endif; ?>
<section class="card">
    <div class="card__header"><h2 class="card__title">Connection</h2></div>
    <div class="card__body">
        <p>
            <?php if ($hasKeys): ?><span class="badge badge--success badge--dot">Keys found in .env</span><?php else: ?><span class="badge badge--warning badge--dot">Keys missing</span><?php endif; ?>
            <span class="badge badge--<?= $mode === 'live' ? 'success' : 'neutral' ?>"><?= $mode === 'live' ? 'LIVE: real money' : 'SANDBOX: practice only' ?></span>
        </p>
        <?php if (!$hasKeys): ?>
            <p>Add these three lines to the <code>.env</code> file on the server (never send them in chat or e-mail), then press <strong>Test connection</strong>:</p>
            <pre>PESAPAL_ENV=sandbox
PESAPAL_CONSUMER_KEY=your key from the Pesapal dashboard
PESAPAL_CONSUMER_SECRET=your secret from the Pesapal dashboard</pre>
            <p>Use <code>sandbox</code> with your sandbox keys while testing. When Pesapal has verified your business and you have your live keys, change it to <code>PESAPAL_ENV=live</code>.</p>
        <?php else: ?>
            <form method="post" action="<?= e(url('admin/pesapal/test')) ?>"><?= csrf_field() ?><button class="btn btn--primary" type="submit">Test connection</button></form>
        <?php endif; ?>
        <p class="text-muted">Pesapal reports payments to <code><?= e($ipnUrl) ?></code> (registered automatically on the first payment). Customers return to <code><?= e($returnUrl) ?></code>.
            Online giving appears on the Donate page, and "Pesapal" appears at the shop checkout once it is switched on under Shop &gt; Payments.</p>
    </div>
</section>

<section class="card" style="margin-top:1.5rem">
    <div class="card__header"><h2 class="card__title">Where Pesapal is offered on the website</h2></div>
    <div class="card__body">
        <form method="post" action="<?= e(url('admin/pesapal/options')) ?>">
            <?= csrf_field() ?>
            <p class="text-muted">Tick the places where visitors can pay with Pesapal, then press <strong>Save</strong>. A place only shows Pesapal when its box is ticked <em>and</em> the keys are in <code>.env</code>.</p>
            <div style="display:grid;gap:1rem;margin:1rem 0">
                <label class="check" style="display:flex;gap:.75rem;align-items:flex-start">
                    <input type="checkbox" name="donations" value="1"<?= $donationsOn ? ' checked' : '' ?>>
                    <span><strong>Donate page: "Give online" form</strong><br>
                        <span class="text-muted">Donors pay by card or mobile money on <a href="<?= e(url('donate')) ?>" target="_blank" rel="noopener">/donate</a>. Paid gifts become "Received" donations with a receipt number.</span><br>
                        <?php if ($donationsOn && $hasKeys): ?><span class="badge badge--success badge--dot">Visible to visitors</span>
                        <?php elseif ($donationsOn): ?><span class="badge badge--warning badge--dot">Ticked, but hidden: the keys are missing</span>
                        <?php else: ?><span class="badge badge--neutral badge--dot">Off: the form is hidden</span><?php endif; ?></span>
                </label>
                <label class="check" style="display:flex;gap:.75rem;align-items:flex-start">
                    <input type="checkbox" name="shop" value="1"<?= $shopOn ? ' checked' : '' ?><?= $canShop ? '' : ' disabled' ?>>
                    <span><strong>Shop checkout: "Card or mobile money (Pesapal)"</strong><br>
                        <span class="text-muted">Customers can choose Pesapal when they pay for an order at <a href="<?= e(url('shop/checkout')) ?>" target="_blank" rel="noopener">/shop</a>. Paid orders are marked paid automatically.<?= $canShop ? '' : ' (Only a main administrator can change this one.)' ?></span><br>
                        <?php if ($shopOn && $hasKeys && $shopOpen): ?><span class="badge badge--success badge--dot">Visible at checkout</span>
                        <?php elseif ($shopOn && !$hasKeys): ?><span class="badge badge--warning badge--dot">Ticked, but hidden: the keys are missing</span>
                        <?php elseif ($shopOn && !$shopOpen): ?><span class="badge badge--warning badge--dot">Ticked, but the shop is closed: turn on "The shop takes orders" in <a href="<?= e(url('admin/shop/settings')) ?>">Shop settings</a></span>
                        <?php else: ?><span class="badge badge--neutral badge--dot">Off at checkout</span><?php endif; ?></span>
                </label>
            </div>
            <button class="btn btn--primary" type="submit">Save</button>
        </form>
    </div>
</section>

<section class="card" style="margin-top:1.5rem">
    <div class="card__header"><h2 class="card__title">LAF Academy fees</h2></div>
    <div class="card__body">
        <?php if (!$feesReady): ?>
            <div class="alert alert--error" role="alert"><strong>The Academy fee columns are missing in the database.</strong> In phpMyAdmin import <code>database/updates/2026-10-academy-fees.sql</code> (safe to repeat), then reload this page. Until then no fee is charged.</div>
        <?php endif; ?>
        <form method="post" action="<?= e(url('admin/pesapal/fees')) ?>">
            <?= csrf_field() ?>
            <p class="text-muted">Set what learners pay through Pesapal. <strong>Leave 0 for free.</strong> A fee is only charged while Pesapal is connected (keys in <code>.env</code>). Accounts and certificates that exist already are never charged.</p>
            <?php foreach ([
                'signup' => ['Sign-up fee', 'Paid once by a new learner right after creating the account. The account cannot be used until it is paid (or you waive it on the learner\'s page).'],
                'cert'   => ['Certificate fee', 'Paid by a learner to receive a certificate earned by passing a final exam (the certificate is held until it is paid, and still needs your approval if that setting is on).'],
            ] as $kind => [$label, $help]): $f = $fees[$kind]; ?>
                <div style="display:grid;gap:.25rem;margin:1rem 0">
                    <strong><?= e($label) ?></strong>
                    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
                        <div class="field" style="margin:0"><label class="field__label" for="fee-<?= e($kind) ?>">Amount</label><input class="input" id="fee-<?= e($kind) ?>" name="<?= e($kind) ?>_amount" inputmode="decimal" value="<?= e($f['amount']) ?>" maxlength="15"<?= $canFees ? '' : ' disabled' ?>></div>
                        <div class="field" style="margin:0"><label class="field__label" for="feec-<?= e($kind) ?>">Currency</label><select class="select" id="feec-<?= e($kind) ?>" name="<?= e($kind) ?>_currency"<?= $canFees ? '' : ' disabled' ?>>
                            <?php foreach ($feeCurrencies as $code): ?><option value="<?= e($code) ?>"<?= $f['currency'] === $code ? ' selected' : '' ?>><?= e($code) ?></option><?php endforeach; ?></select></div>
                        <div>
                            <?php if ($f['on'] && $feesReady && $hasKeys): ?><span class="badge badge--success badge--dot">Charged: <?= e($f['display']) ?></span>
                            <?php elseif ($f['on']): ?><span class="badge badge--warning badge--dot">Set, but not charged yet: <?= !$hasKeys ? 'the Pesapal keys are missing' : 'database update needed' ?></span>
                            <?php else: ?><span class="badge badge--neutral badge--dot">Free</span><?php endif; ?>
                        </div>
                    </div>
                    <span class="text-muted"><?= e($help) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if ($canFees): ?><button class="btn btn--primary" type="submit">Save the fees</button><?php else: ?><p class="text-muted">Only an administrator who manages the Academy can change the fees.</p><?php endif; ?>
        </form>
    </div>
</section>

<section class="card" style="margin-top:1.5rem">
    <div class="card__header"><h2 class="card__title">Payments</h2></div>
    <div class="card__body">
        <?php if ($payments === []): ?>
            <p class="text-muted">No Pesapal payment yet.</p>
        <?php else: ?>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Date</th><th>Reference</th><th>For</th><th>Payer</th><th>Amount</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= e(format_datetime((string) $p['created_at'], 'j M Y, H:i')) ?></td>
                    <td><code><?= e($p['merchant_reference']) ?></code><?= $p['mode'] === 'sandbox' ? ' <span class="badge badge--neutral">sandbox</span>' : '' ?></td>
                    <td><?php if ($p['purpose'] === 'donation'): ?><a href="<?= e(url('admin/donations/' . (int) $p['ref_id'] . '/edit')) ?>">Donation #<?= (int) $p['ref_id'] ?></a><?php elseif ($p['purpose'] === 'academy_signup'): ?><a href="<?= e(url('admin/academy/learners/' . (int) $p['ref_id'])) ?>">Academy sign-up fee (learner #<?= (int) $p['ref_id'] ?>)</a><?php elseif ($p['purpose'] === 'academy_cert'): ?><a href="<?= e(url('admin/academy/certificates')) ?>">Academy certificate fee (#<?= (int) $p['ref_id'] ?>)</a><?php else: ?>Shop order #<?= (int) $p['ref_id'] ?><?php endif; ?></td>
                    <td><?= e($p['payer_name']) ?><br><small class="text-muted"><?= e($p['payer_email']) ?></small></td>
                    <td><?= e(Money::format((int) $p['amount_minor'], (string) $p['currency'])) ?></td>
                    <td><span class="badge badge--<?= e($tones[$p['status']] ?? 'neutral') ?>"><?= e(ucfirst((string) $p['status'])) ?></span><?= $p['last_error'] ? '<br><small class="text-muted">' . e($p['last_error']) . '</small>' : '' ?></td>
                    <td><?php if (in_array($p['status'], ['pending', 'review'], true) && (string) $p['order_tracking_id'] !== ''): ?>
                        <form method="post" action="<?= e(url('admin/pesapal/' . (int) $p['id'] . '/check')) ?>"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Check with Pesapal</button></form>
                    <?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</section>
