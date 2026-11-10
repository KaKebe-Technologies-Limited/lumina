<?php
/** Admin > Mobile money (Daraza API). Vars: $ready, $hasKey, $mode, $places (purpose => name, on), $rate, $rows (recent ledger rows). */
$net = [1 => 'MTN', 2 => 'Airtel'];
$badge = ['paid' => 'badge--success', 'failed' => 'badge--danger', 'unknown' => 'badge--warning', 'collecting' => 'badge--warning', 'pending' => 'badge--outline'];
?>
<?= partial('partials/admin/page-header', ['title' => 'Mobile money (Daraza API)', 'subtitle' => 'Take MTN and Airtel mobile-money payments on the website and send money out, through the Daraza API.',
    'actions' => [['label' => 'Daraza payment links', 'url' => url('admin/daraza'), 'icon' => 'external-link', 'class' => 'btn--ghost']]]) ?>
<?php if (!$ready): ?><div class="alert alert--warning" role="status">The payments table could not be created automatically. Ask your developer to check the database permissions.</div><?php endif; ?>

<div class="ad-letter-grid">
    <section class="card"><div class="card__body">
        <h2 class="card__title">Connection</h2>
        <p><?= $hasKey ? '<span class="badge badge--success">API key found</span>' : '<span class="badge badge--danger">No API key</span>' ?>
            <span class="badge badge--outline"><?= $mode === 'live' ? 'LIVE: real money' : 'SANDBOX: testing, no real money' ?></span></p>
        <?php if (!$hasKey): ?>
            <p class="text-muted">Create an app in the Daraza Developer Console, copy its API key and add these two lines to the <code>.env</code> file on the server (the key is never stored in the website or shown here):</p>
            <pre class="ad-code">DARAZA_API_KEY=your-key-here
DARAZA_ENV=sandbox</pre>
            <p class="text-muted">Use <code>sandbox</code> while testing. When everything works, change it to <code>live</code>.</p>
        <?php else: ?>
            <p class="text-muted">The key is read from the server <code>.env</code>. To go live, set <code>DARAZA_ENV=live</code> there.</p>
            <form method="post" action="<?= e(url('admin/daraza/api/test')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= icon('check-circle', 'icon--sm') ?> Test the connection (shows the wallet balance)</button></form>
        <?php endif; ?>
    </div></section>

    <section class="card"><div class="card__body">
        <h2 class="card__title">Where "Pay with mobile money" appears</h2>
        <form method="post" action="<?= e(url('admin/daraza/api')) ?>"><?= csrf_field() ?>
            <?php foreach ($places as $k => $p): ?><label class="check"><input type="checkbox" name="on_<?= e($k) ?>" value="1" <?= $p['on'] ? 'checked' : '' ?>> <?= e($p['name']) ?></label><?php endforeach; ?>
            <div class="field"><label class="field__label" for="usd-rate">Uganda shillings for 1 US dollar</label>
                <input class="input" id="usd-rate" name="usd_rate" type="number" min="0" max="100000" value="<?= (int) $rate ?>" placeholder="3700">
                <div class="field__help">Mobile money is charged in UGX. A fee set in USD (for example the USD 3 certificate fee) is converted with this rate and rounded up to the next 100 UGX. Leave 0 to offer mobile money only for fees set in UGX.</div></div>
            <button class="btn btn--primary btn--sm" type="submit">Save</button>
        </form>
        <p class="text-muted">The payer approves the payment on their phone; this can take up to 5 minutes. The payment is confirmed automatically and the fee, certificate or donation is updated at once.</p>
    </div></section>
</div>

<section class="card"><div class="card__body">
    <h2 class="card__title">Send money (payout)</h2>
    <p class="text-muted">Send money from the Daraza app wallet to a phone: a refund, a prize or a payment. The wallet must have enough balance. UGX 500 to 5,000,000. <strong>This is real money in live mode.</strong></p>
    <form method="post" action="<?= e(url('admin/daraza/api/payout')) ?>" class="rc-kpi-row"><?= csrf_field() ?>
        <label class="rc-kpi-row__w">Phone <input class="input" name="phone" type="tel" placeholder="0772 123 456" required></label>
        <label class="rc-kpi-row__w">Network <select class="select" name="method"><option value="0">Detect</option><option value="1">MTN</option><option value="2">Airtel</option></select></label>
        <label class="rc-kpi-row__w">Amount (UGX) <input class="input" name="amount" type="number" min="500" max="5000000" required></label>
        <label class="rc-kpi-row__w">Reason <input class="input" name="note" maxlength="150" placeholder="Refund / prize"></label>
        <label class="check"><input type="checkbox" name="confirm" value="1"> Yes, send this money now</label>
        <button class="btn btn--primary btn--sm" type="submit" <?= $hasKey ? '' : 'disabled' ?>><?= icon('send', 'icon--sm') ?> Send</button>
    </form>
</div></section>

<section class="card"><div class="card__body">
    <h2 class="card__title">Recent payments</h2>
    <?php if (!$rows): ?><p class="text-muted">No mobile-money payment yet.</p><?php else: ?>
    <div class="table-wrap"><table class="table table--compact">
        <caption class="visually-hidden">Mobile-money payments</caption>
        <thead><tr><th scope="col">When</th><th scope="col">What</th><th scope="col">Who</th><th scope="col" class="num">UGX</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td class="nowrap"><?= e(format_datetime($r['created_at'], 'j M, H:i')) ?><span class="cell-sub"><?= e($r['mode']) ?></span></td>
                <td><?= $r['direction'] === 'out' ? '<strong>Payout</strong>' : e(\App\Services\DarazaPay\DarazaPayments::PURPOSES[$r['purpose']] ?? $r['purpose']) ?><span class="cell-sub"><?= e($r['reference']) ?></span></td>
                <td><?= e($r['direction'] === 'out' ? (string) $r['phone'] : ($r['payer_name'] !== '' ? $r['payer_name'] : (string) $r['payer_email'])) ?><span class="cell-sub"><?= e(($net[(int) $r['method']] ?? '') . ' ' . ($r['direction'] === 'in' ? (string) $r['phone'] : '')) ?></span></td>
                <td class="num"><?= e(number_format((int) $r['amount_ugx'])) ?></td>
                <td><span class="badge <?= e($badge[$r['status']] ?? 'badge--outline') ?>"><?= e($r['status']) ?></span><?php if ($r['message']): ?><span class="cell-sub"><?= e(mb_strimwidth((string) $r['message'], 0, 80, '...')) ?></span><?php endif; ?></td>
                <td><?php if (in_array($r['status'], ['pending', 'unknown', 'collecting', 'failed'], true) && $r['request_id']): ?>
                    <form method="post" action="<?= e(url('admin/daraza/api/payments/' . (int) $r['id'] . '/check')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Check again</button></form><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div></section>
