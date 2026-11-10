<?php
/**
 * Admin > Payments > Audit trail: every step of every payment (attempts, successful, unsuccessful). Vars: $f (filters), $rows, $pagination, $counts.
 */
use App\Services\Payments\PaymentEvents as PE;
use App\Services\Payments\PaymentsDashboard as PD;

$tone = ['attempt' => 'warning', 'success' => 'success', 'failure' => 'danger', 'notice' => 'info'];
$icon = ['started' => 'clock', 'requested' => 'send', 'unknown' => 'clock', 'success' => 'check-circle', 'failed' => 'alert-circle', 'reversed' => 'alert-circle', 'review' => 'alert-triangle', 'thanks' => 'mail'];
$base = ['range' => $f['range'], 'group' => $f['group'], 'category' => $f['category'], 'channel' => $f['channel'], 'q' => $f['q']];
$qs = static fn(array $over): string => http_build_query(array_filter(array_merge($base, $over), static fn($v) => $v !== '' && $v !== null));
$money = static fn(?int $minor, string $cur): string => $minor === null ? '' : $cur . ' ' . number_format((int) round($minor / 100));
?>
<?= partial('partials/admin/page-header', ['title' => 'Payment audit trail', 'subtitle' => 'Every step of every payment: attempts, successful and unsuccessful. Nothing here can be changed or deleted from this page.',
    'actions' => [['label' => 'Payments', 'url' => url('admin/payments'), 'icon' => 'bar-chart', 'class' => 'btn--outline']]]) ?>

<nav class="pd-range" aria-label="Period">
    <?php foreach (PD::RANGES as $k => $label): ?><a class="pd-range__item<?= $f['range'] === (string) $k ? ' is-active' : '' ?>" href="<?= e(url('admin/payments/audit?' . $qs(['range' => $k, 'page' => null]))) ?>"<?= $f['range'] === (string) $k ? ' aria-current="true"' : '' ?>><?= e($label) ?></a><?php endforeach; ?>
</nav>

<div class="pd-kpis">
    <a class="pd-kpi" href="<?= e(url('admin/payments/audit?' . $qs(['group' => 'attempt', 'page' => null]))) ?>"><span class="pd-kpi__icon"><?= icon('clock', 'icon--sm') ?></span><span class="pd-kpi__label">Attempts</span><strong class="pd-kpi__num"><?= (int) $counts['started'] ?></strong><span class="pd-kpi__sub"><?= (int) $counts['waiting'] ?> still waiting</span></a>
    <a class="pd-kpi pd-kpi--in" href="<?= e(url('admin/payments/audit?' . $qs(['group' => 'success', 'page' => null]))) ?>"><span class="pd-kpi__icon"><?= icon('check-circle', 'icon--sm') ?></span><span class="pd-kpi__label">Successful</span><strong class="pd-kpi__num"><?= (int) $counts['success'] ?></strong><span class="pd-kpi__sub"><?= (int) $counts['rate'] ?>% of finished attempts</span></a>
    <a class="pd-kpi pd-kpi--out" href="<?= e(url('admin/payments/audit?' . $qs(['group' => 'failure', 'page' => null]))) ?>"><span class="pd-kpi__icon"><?= icon('alert-circle', 'icon--sm') ?></span><span class="pd-kpi__label">Unsuccessful</span><strong class="pd-kpi__num"><?= (int) $counts['failed'] ?></strong><span class="pd-kpi__sub">failed, reversed or flagged</span></a>
</div>

<section class="card"><div class="card__body">
    <form method="get" action="<?= e(url('admin/payments/audit')) ?>" class="pd-filters">
        <input type="hidden" name="range" value="<?= e($f['range']) ?>">
        <label class="pd-filters__q"><span class="visually-hidden">Search</span><input class="input" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Search reference, name, detail..."></label>
        <label><span class="visually-hidden">Result</span><select class="select" name="group"><option value="">All steps</option><?php foreach (PE::GROUPS as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['group'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <label><span class="visually-hidden">Category</span><select class="select" name="category"><option value="">All categories</option><?php foreach (PE::CATEGORIES as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['category'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <label><span class="visually-hidden">Channel</span><select class="select" name="channel"><option value="">All channels</option><?php foreach (['mobile' => 'Mobile money', 'online' => 'Card / online', 'manual' => 'Bank / cash'] as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['channel'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <button class="btn btn--primary btn--sm" type="submit"><?= icon('search', 'icon--sm') ?> Filter</button>
        <?php if ($f['q'] !== '' || $f['group'] !== '' || $f['category'] !== '' || $f['channel'] !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/payments/audit?range=' . $f['range'])) ?>">Clear</a><?php endif; ?>
    </form>
    <?php if (!$rows): ?><p class="text-muted">No payment steps match. Steps appear here as soon as someone starts, completes or fails a payment.</p><?php else: ?>
    <div class="table-wrap"><table class="table table--compact">
        <caption class="visually-hidden">Payment audit trail</caption>
        <thead><tr><th scope="col">When</th><th scope="col">Step</th><th scope="col">Category</th><th scope="col">Who</th><th scope="col">Details</th><th scope="col" class="num">Amount</th><th scope="col">Reference</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $g = PE::EVENTS[$r['event']] ?? 'notice'; ?>
            <tr>
                <td class="nowrap" data-label="When"><?= e(format_datetime($r['created_at'], 'j M Y, H:i:s')) ?></td>
                <td data-label="Step"><span class="badge badge--<?= e($tone[$g] ?? 'neutral') ?>"><?= icon($icon[$r['event']] ?? 'info', 'icon--sm') ?> <?= e(PE::EVENT_LABELS[$r['event']] ?? $r['event']) ?></span></td>
                <td data-label="Category"><?= e(PE::CATEGORIES[$r['category']] ?? $r['category']) ?><span class="cell-sub"><?= e(['mobile' => 'Mobile money', 'online' => 'Card / online', 'manual' => 'Bank / cash'][$r['channel']] ?? $r['channel']) ?></span></td>
                <td data-label="Who"><?= e($r['who'] !== '' ? $r['who'] : '—') ?></td>
                <td data-label="Details"><?= e($r['detail']) ?></td>
                <td class="num nowrap" data-label="Amount"><?= e($money($r['amount_minor'] === null ? null : (int) $r['amount_minor'], (string) $r['currency'])) ?></td>
                <td class="nowrap" data-label="Reference"><code><?= e($r['reference']) ?></code></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => 'admin/payments/audit']) ?>
    <?php endif; ?>
</div></section>
