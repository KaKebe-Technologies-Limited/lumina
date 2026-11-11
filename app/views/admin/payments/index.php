<?php
/**
 * Admin > Payments: every payment on the website in one place, plus "Send money".
 * Vars: $f (filters), $sum (summary), $rows, $pagination, $ready, $hasKey, $mode, $recipients, $prefill.
 */
use App\Services\Payments\PaymentsDashboard as PD;

$this->start('scripts'); ?><script src="<?= e(asset('js/payments-admin.js')) ?>" defer></script><?php $this->end();
$m = static fn(int $minor): string => number_format((int) round($minor / 100));
$net = [1 => 'MTN', 2 => 'Airtel'];
$tone = ['paid' => 'success', 'pending' => 'warning', 'failed' => 'danger'];
$chanIcon = ['mobile' => 'phone', 'online' => 'globe', 'manual' => 'edit'];
$base = ['range' => $f['range'], 'channel' => $f['channel'], 'category' => $f['category'] ?? '', 'dir' => $f['dir'], 'status' => $f['status'], 'q' => $f['q']];
$qs = static fn(array $over): string => http_build_query(array_filter(array_merge($base, $over), static fn($v) => $v !== '' && $v !== null));
$maxDay = max(1, ...array_map(static fn(array $d): int => $d['amt'], $sum['days']));
$maxWhat = max(1, ...array_map(static fn(array $d): int => $d['amt'], $sum['by_what'] ?: [['amt' => 1]]));
$totalIn = max(1, array_sum($sum['by_channel']));
$rangeLabel = strtolower(PD::RANGES[$f['range']]);
?>
<?= partial('partials/admin/page-header', ['title' => 'Payments', 'subtitle' => 'Every payment on the website in one place: mobile money, card, bank and cash. Send money to any MTN or Airtel number.',
    'actions' => [['label' => 'Download (CSV)', 'url' => url('admin/payments/export?' . $qs([])), 'icon' => 'download', 'class' => 'btn--outline'], ['label' => 'Audit trail', 'url' => url('admin/payments/audit'), 'icon' => 'list', 'class' => 'btn--outline'], ['label' => 'Payment options', 'url' => url('admin/daraza/api'), 'icon' => 'phone', 'class' => 'btn--ghost']]]) ?>
<?php if (!$ready): ?><div class="alert alert--warning" role="status">The payments table could not be created automatically. Ask your developer to check the database permissions.</div><?php endif; ?>

<nav class="pd-range" aria-label="Period">
    <?php foreach (PD::RANGES as $k => $label): ?><a class="pd-range__item<?= $f['range'] === (string) $k ? ' is-active' : '' ?>" href="<?= e(url('admin/payments?' . $qs(['range' => $k, 'page' => null]))) ?>"<?= $f['range'] === (string) $k ? ' aria-current="true"' : '' ?>><?= e($label) ?></a><?php endforeach; ?>
</nav>

<div class="pd-kpis">
    <div class="pd-kpi pd-kpi--in"><span class="pd-kpi__icon"><?= icon('arrow-down', 'icon--sm') ?></span><span class="pd-kpi__label">Money in</span><strong class="pd-kpi__num">UGX <?= e($m($sum['in'])) ?></strong><span class="pd-kpi__sub"><?= (int) $sum['count_in'] ?> payments, <?= e($rangeLabel) ?></span></div>
    <div class="pd-kpi pd-kpi--out"><span class="pd-kpi__icon"><?= icon('arrow-up', 'icon--sm') ?></span><span class="pd-kpi__label">Money sent out</span><strong class="pd-kpi__num">UGX <?= e($m($sum['out'])) ?></strong><span class="pd-kpi__sub"><?= (int) $sum['count_out'] ?> transfers</span></div>
    <div class="pd-kpi"><span class="pd-kpi__icon"><?= icon('trending-up', 'icon--sm') ?></span><span class="pd-kpi__label">Net</span><strong class="pd-kpi__num">UGX <?= e($m($sum['net'])) ?></strong><span class="pd-kpi__sub">in minus out</span></div>
    <div class="pd-kpi pd-kpi--wait"><span class="pd-kpi__icon"><?= icon('refresh', 'icon--sm') ?></span><span class="pd-kpi__label">Need attention</span><strong class="pd-kpi__num"><?= (int) $sum['waiting'] ?> waiting <small>· <?= (int) $sum['failed'] ?> failed</small></strong>
        <span class="pd-kpi__sub"><a href="<?= e(url('admin/payments?' . $qs(['status' => 'pending', 'page' => null]))) ?>">Waiting</a> (last 3 days) · <a href="<?= e(url('admin/payments?' . $qs(['status' => 'failed', 'page' => null]))) ?>">Failed</a></span></div>
</div>
<?php if ($sum['other']): ?><p class="text-muted pd-other">Also received in other currencies (not added above): <?php foreach ($sum['other'] as $cur => $amt): ?><strong><?= e($cur) ?> <?= e($m($amt)) ?></strong> <?php endforeach; ?></p><?php endif; ?>

<div class="ad-letter-grid">
    <section class="card"><div class="card__body">
        <h2 class="card__title">Money in, last 14 days</h2>
        <div class="pd-bars" role="img" aria-label="Money received per day for the last 14 days">
            <?php foreach ($sum['days'] as $d): $h = (int) round($d['amt'] / $maxDay * 100); ?>
                <div class="pd-bars__col" title="<?= e(format_date($d['day'], 'j M') . ': UGX ' . $m($d['amt'])) ?>"><span class="pd-bars__bar" style="height:<?= $d['amt'] > 0 ? max(4, $h) : 2 ?>%"></span><span class="pd-bars__day"><?= e(date('j', strtotime($d['day']))) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div></section>
    <section class="card"><div class="card__body">
        <h2 class="card__title">How people paid (<?= e($rangeLabel) ?>)</h2>
        <?php foreach (PD::CHANNELS as $k => $label): $pc = (int) round($sum['by_channel'][$k] / $totalIn * 100); ?>
            <a class="pd-share" href="<?= e(url('admin/payments?' . $qs(['channel' => $f['channel'] === $k ? '' : $k, 'page' => null]))) ?>">
                <span class="pd-share__head"><span><?= icon($chanIcon[$k], 'icon--sm') ?> <?= e($label) ?></span><strong>UGX <?= e($m($sum['by_channel'][$k])) ?></strong></span>
                <span class="pd-share__track"><span class="pd-share__fill pd-share__fill--<?= e($k) ?>" style="width:<?= $pc ?>%"></span></span>
            </a>
        <?php endforeach; ?>
        <?php if ($sum['by_what']): ?><h3 class="ad-letter-sub">What it paid for</h3>
            <?php foreach ($sum['by_what'] as $w): ?><div class="pd-what"><span><?= e($w['what']) ?> <small class="text-muted">(<?= (int) $w['n'] ?>)</small></span><strong>UGX <?= e($m($w['amt'])) ?></strong><span class="pd-what__track"><span style="width:<?= (int) round($w['amt'] / $maxWhat * 100) ?>%"></span></span></div><?php endforeach; ?>
        <?php endif; ?>
    </div></section>
</div>

<section class="card pd-send" id="send"><div class="card__body">
    <div class="pd-send__head">
        <div><h2 class="card__title"><?= icon('send', 'icon--sm') ?> Send money to mobile money</h2>
            <p class="text-muted">Pay out from the Daraza app wallet to any MTN or Airtel number: a refund, a prize, a stipend or a supplier. UGX 500 to 5,000,000. <?= $mode === 'live' ? '<strong>This is real money.</strong>' : '<span class="badge badge--outline">Sandbox: no real money</span>' ?></p></div>
        <form method="post" action="<?= e(url('admin/payments/balance')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit" <?= $hasKey ? '' : 'disabled' ?>><?= icon('refresh', 'icon--sm') ?> Check wallet balance</button></form>
    </div>
    <?php if (!$hasKey): ?><p class="opp-notice opp-notice--closed" role="status">Add DARAZA_API_KEY to the server .env file first (see Payment options).</p><?php endif; ?>
    <?php if ($recipients): ?><p class="pd-recent"><span class="text-muted">Send again to:</span>
        <?php foreach ($recipients as $r): ?><a class="pd-chip" href="<?= e(url('admin/payments?' . $qs(['to' => $r['phone'], 'network' => $r['method']]) . '#send')) ?>"><?= e(($net[$r['method']] ?? '') . ' ' . $r['phone']) ?></a><?php endforeach; ?></p><?php endif; ?>
    <form method="post" action="<?= e(url('admin/payments/payout')) ?>" class="pd-send__form" data-pd-send><?= csrf_field() ?>
        <div class="field"><label class="field__label" for="pd-phone">Phone number</label>
            <input class="input" id="pd-phone" name="phone" type="tel" inputmode="tel" placeholder="0772 123 456" value="<?= e($prefill['phone']) ?>" required data-pd-phone>
            <div class="field__help" data-pd-net>Any MTN or Airtel number. The network is detected from the number.</div></div>
        <div class="field"><label class="field__label" for="pd-net">Network</label>
            <select class="select" id="pd-net" name="method" data-pd-method><option value="0">Detect from the number</option><option value="1" <?= $prefill['method'] === 1 ? 'selected' : '' ?>>MTN Mobile Money</option><option value="2" <?= $prefill['method'] === 2 ? 'selected' : '' ?>>Airtel Money</option></select></div>
        <div class="field"><label class="field__label" for="pd-amount">Amount (UGX)</label>
            <input class="input" id="pd-amount" name="amount" type="number" min="500" max="5000000" step="100" required data-pd-amount>
            <div class="pd-quick"><?php foreach ([5000, 10000, 20000, 50000, 100000, 500000] as $q): ?><button class="pd-chip" type="button" data-pd-fill="<?= (int) $q ?>"><?= e(number_format($q)) ?></button><?php endforeach; ?></div></div>
        <div class="field"><label class="field__label" for="pd-note">Reason (the receiver may see it)</label>
            <input class="input" id="pd-note" name="note" maxlength="150" placeholder="Refund, prize, stipend..."></div>
        <div class="pd-send__go">
            <label class="check"><input type="checkbox" name="confirm" value="1" required> <span data-pd-sum>Yes, send this money now</span></label>
            <button class="btn btn--cta" type="submit" <?= $hasKey ? '' : 'disabled' ?>><?= icon('send', 'icon--sm') ?> Send money</button>
        </div>
    </form>
</div></section>

<section class="card"><div class="card__body">
    <h2 class="card__title">All payments</h2>
    <form method="get" action="<?= e(url('admin/payments')) ?>" class="pd-filters">
        <input type="hidden" name="range" value="<?= e($f['range']) ?>">
        <label class="pd-filters__q"><span class="visually-hidden">Search</span><input class="input" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Search name, phone, reference..."></label>
        <label><span class="visually-hidden">Direction</span><select class="select" name="dir"><option value="">In and out</option><option value="in"<?= $f['dir'] === 'in' ? ' selected' : '' ?>>Money in</option><option value="out"<?= $f['dir'] === 'out' ? ' selected' : '' ?>>Money out</option></select></label>
        <label><span class="visually-hidden">Category</span><select class="select" name="category"><option value="">All categories</option><?php foreach (\App\Services\Payments\PaymentEvents::CATEGORIES as $k => $l): ?><option value="<?= e($k) ?>"<?= ($f['category'] ?? '') === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <label><span class="visually-hidden">Channel</span><select class="select" name="channel"><option value="">All channels</option><?php foreach (PD::CHANNELS as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['channel'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <label><span class="visually-hidden">Status</span><select class="select" name="status"><option value="">Any status</option><?php foreach (PD::STATUSES as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['status'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <button class="btn btn--primary btn--sm" type="submit"><?= icon('search', 'icon--sm') ?> Filter</button>
        <?php if ($f['q'] !== '' || $f['dir'] !== '' || $f['channel'] !== '' || $f['status'] !== '' || ($f['category'] ?? '') !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/payments?range=' . $f['range'])) ?>">Clear</a><?php endif; ?>
    </form>
    <?php $unfinished = array_filter($rows, static fn(array $r): bool => $r['dir'] === 'in' && \App\Services\Payments\PaymentReminders::actionable((string) $r['src'], (string) $r['status'])); ?>
    <?php if ($unfinished): ?>
        <form method="post" action="<?= e(url('admin/payments/remind-all')) ?>" class="pd-remindall" data-confirm="E-mail a reminder to every payer with an unfinished payment in this list (at most 50, one per payment per day)?"><?= csrf_field() ?>
            <?php foreach (['range' => $f['range'], 'channel' => $f['channel'], 'category' => $f['category'] ?? '', 'q' => $f['q']] as $kk => $kv): ?><input type="hidden" name="<?= e($kk) ?>" value="<?= e((string) $kv) ?>"><?php endforeach; ?>
            <span class="text-muted"><?= count($unfinished) ?> unfinished payment<?= count($unfinished) === 1 ? '' : 's' ?> on this page.</span>
            <button class="btn btn--outline btn--sm" type="submit"><?= icon('mail', 'icon--sm') ?> Remind all unfinished (current list)</button>
        </form>
    <?php endif; ?>
    <?php if (!$rows): ?><p class="text-muted">No payments match. Try a longer period or clear the filters.</p><?php else: ?>
    <div class="table-wrap"><table class="table table--compact">
        <caption class="visually-hidden">Payments</caption>
        <thead><tr><th scope="col">When</th><th scope="col">What</th><th scope="col">Who</th><th scope="col">Channel</th><th scope="col" class="num">Amount</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $out = $r['dir'] === 'out'; ?>
            <tr>
                <td class="nowrap" data-label="When"><?= e(format_datetime($r['at'], 'j M, H:i')) ?></td>
                <td data-label="What"><strong><?= e($r['what']) ?></strong><span class="cell-sub"><?= e($r['ref']) ?></span></td>
                <td data-label="Who"><?= e($r['who'] !== '' ? $r['who'] : '—') ?><span class="cell-sub"><?= e(trim((string) $r['detail'])) ?></span></td>
                <td data-label="Channel"><span class="pd-chan pd-chan--<?= e($r['channel']) ?>"><?= icon($chanIcon[$r['channel']] ?? 'phone', 'icon--sm') ?> <?= e(['mobile' => 'Mobile money', 'online' => 'Card / online', 'manual' => 'Bank / cash'][$r['channel']] ?? $r['channel']) ?></span></td>
                <td class="num nowrap pd-amt<?= $out ? ' pd-amt--out' : '' ?>" data-label="Amount"><?= $out ? '−' : '+' ?> <?= e($r['cur']) ?> <?= e($m((int) $r['amt'])) ?></td>
                <td data-label="Status"><?php $stale = $r['status'] === 'pending' && strtotime((string) $r['at']) < time() - 600; ?><span class="badge badge--<?= e($tone[$r['status']] ?? 'neutral') ?>"><?= e($stale ? 'Not confirmed' : (PD::STATUSES[$r['status']] ?? $r['status'])) ?></span><?php if ($stale && $r['src'] === 'daraza'): ?><span class="cell-sub">Check the Daraza wallet, then press Resolve</span><?php endif; ?></td>
                <td><?php if ($r['dir'] === 'in' && \App\Services\Payments\PaymentReminders::actionable((string) $r['src'], (string) $r['status'])): $keep = ['range' => $f['range'], 'channel' => $f['channel'], 'category' => $f['category'] ?? '', 'status' => $f['status'], 'dir' => $f['dir'], 'q' => $f['q']]; ?>
                    <details class="pd-fix"><summary class="btn btn--outline btn--sm">Resolve</summary>
                        <div class="pd-fix__box">
                            <?php if ($r['src'] === 'daraza'): ?>
                                <p class="text-muted">If the payer approved but it still shows waiting, Daraza's answer was lost on the way. Press <strong>Check again</strong>; if that cannot tell, look in your Daraza wallet or statement.</p>
                                <form method="post" action="<?= e(url('admin/payments/' . (int) $r['id'] . '/check')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Check again</button></form>
                                <form method="post" action="<?= e(url('admin/payments/' . (int) $r['id'] . '/confirm')) ?>" class="inline-form" data-confirm="Confirm only if you can see this money in the Daraza wallet or statement. The order or receipt will be completed. Continue?"><?= csrf_field() ?><input class="input" name="note" maxlength="120" placeholder="Note (optional)"><button class="btn btn--primary btn--sm" type="submit">Money received: confirm</button></form>
                                <form method="post" action="<?= e(url('admin/payments/' . (int) $r['id'] . '/close')) ?>" class="inline-form" data-confirm="Close this payment as not received?"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Not received: close</button></form>
                            <?php else: ?>
                                <p class="text-muted">This card payment was started but not completed.</p>
                            <?php endif; ?>
                            <form method="post" action="<?= e(url('admin/payments/' . e((string) $r['src']) . '/' . (int) $r['id'] . '/remind')) ?>" class="inline-form"><?= csrf_field() ?><?php foreach ($keep as $kk => $kv): ?><input type="hidden" name="<?= e($kk) ?>" value="<?= e((string) $kv) ?>"><?php endforeach; ?><button class="btn btn--outline btn--sm" type="submit"><?= icon('mail', 'icon--sm') ?> Remind the payer by e-mail</button></form>
                            <form method="post" action="<?= e(url('admin/payments/' . e((string) $r['src']) . '/' . (int) $r['id'] . '/delete')) ?>" class="inline-form" data-confirm="Delete this unfinished payment record? It cannot be undone. A paid payment is never deleted."><?php /* the audit trail keeps a note */ ?><?= csrf_field() ?><?php foreach ($keep as $kk => $kv): ?><input type="hidden" name="<?= e($kk) ?>" value="<?= e((string) $kv) ?>"><?php endforeach; ?><button class="btn btn--ghost btn--sm pd-fix__del" type="submit"><?= icon('trash', 'icon--sm') ?> Delete the record</button></form>
                        </div></details>
                <?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => 'admin/payments']) ?>
    <?php endif; ?>
</div></section>
