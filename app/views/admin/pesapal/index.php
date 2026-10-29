<?php
/** Admin > Pesapal payments. Vars: $hasKeys, $mode, $payments, $ipnUrl, $returnUrl. */
use App\Services\Shop\Money;

$tones = ['pending' => 'warning', 'paid' => 'success', 'failed' => 'danger', 'reversed' => 'danger', 'review' => 'warning'];
?>
<?= partial('partials/admin/page-header', ['title' => 'Pesapal payments', 'subtitle' => 'Card, mobile money and bank payments for donations and shop orders.']) ?>

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
                    <td><?php if ($p['purpose'] === 'donation'): ?><a href="<?= e(url('admin/donations/' . (int) $p['ref_id'] . '/edit')) ?>">Donation #<?= (int) $p['ref_id'] ?></a><?php else: ?>Shop order #<?= (int) $p['ref_id'] ?><?php endif; ?></td>
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
