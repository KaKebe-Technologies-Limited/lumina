<?php $this->start('scripts'); ?><script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script><?php $this->end(); ?>
<?php
/** Admin > Academy > Payments. Vars: $f, $periods, $types, $statuses, $events, $summary, $daily, $ready, $signupFee, $certFee. */
use App\Services\Academy\PaymentsReport;

$maxDay = max(1, ...array_map(static fn(array $d): int => $d['n'], $daily ?: [['n' => 1]]));
$q = array_filter(['period' => $f['period'], 'type' => $f['type'], 'status' => $f['status']], static fn($v): bool => $v !== 'all');
$statusLabel = ['received' => ['Received', 'success'], 'pending' => ['Started, not finished', 'neutral'], 'reported' => ['Waiting for you', 'warning'], 'failed' => ['Failed', 'danger']];
?>
<?= partial('partials/admin/page-header', ['title' => 'Academy payments', 'subtitle' => 'Sign-up and certificate fees as they come in. This page refreshes itself every 30 seconds.', 'actions' => [['label' => 'Download CSV', 'url' => url('admin/academy/payments-export', $q), 'icon' => 'download', 'class' => 'btn--outline']]]) ?>

<?php if (!$ready): ?>
    <div class="alert alert--info" role="status"><strong>No Academy fee is set up yet.</strong> Set the sign-up fee and the certificate fee in Admin > Pesapal payments > LAF Academy fees. Payments then appear here.</div>
<?php endif; ?>

<form class="filters" method="get" action="<?= e(url('admin/academy/payments')) ?>" role="search">
    <div class="field"><label class="field__label" for="p-period">Period</label><select class="select" id="p-period" name="period"><?php foreach ($periods as $k => $l): ?><option value="<?= e((string) $k) ?>"<?= (string) $f['period'] === (string) $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="p-type">Fee</label><select class="select" id="p-type" name="type"><?php foreach ($types as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['type'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="p-status">Status</label><select class="select" id="p-status" name="status"><?php foreach ($statuses as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['status'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <button class="btn btn--outline" type="submit">Filter</button>
</form>

<div data-pay-live data-pay-latest="<?= e((string) ($events[0]['key'] ?? '')) ?>">
    <div class="vis-cards pay-cards">
        <div class="card vis-card pay-card pay-card--ok"><div class="card__body">
            <span class="vis-card__n"><?php if ($summary['amounts'] === []): ?>0<?php else: foreach ($summary['amounts'] as $cur => $minor): ?><span class="pay-amount"><?= e(PaymentsReport::money((int) $minor, (string) $cur)) ?></span><?php endforeach; endif; ?></span>
            <span class="vis-card__l">Received <small>(<?= (int) $summary['received'] ?> payment<?= $summary['received'] === 1 ? '' : 's' ?><?= $summary['estimated'] ? '; payments you confirmed by hand are counted at the current fee' : '' ?>)</small></span>
        </div></div>
        <div class="card vis-card pay-card<?= $summary['reported'] > 0 ? ' pay-card--warn' : '' ?>"><div class="card__body">
            <span class="vis-card__n"><?= (int) $summary['reported'] ?></span>
            <span class="vis-card__l">Reported by learners, waiting for you to check the money</span>
        </div></div>
        <div class="card vis-card pay-card"><div class="card__body">
            <span class="vis-card__n"><?= (int) $summary['owed_signup'] ?></span>
            <span class="vis-card__l">Sign-up fees not paid yet<?= $summary['owed_signup_display'] !== '' ? ' <small>(' . e($summary['owed_signup_display']) . ' expected)</small>' : '' ?></span>
        </div></div>
        <div class="card vis-card pay-card"><div class="card__body">
            <span class="vis-card__n"><?= (int) $summary['owed_cert'] ?></span>
            <span class="vis-card__l">Certificates waiting for their fee<?= $summary['owed_cert_display'] !== '' ? ' <small>(' . e($summary['owed_cert_display']) . ' expected)</small>' : '' ?></span>
        </div></div>
    </div>
    <p class="text-muted pay-sub"><?= (int) $summary['pending'] ?> payment<?= $summary['pending'] === 1 ? '' : 's' ?> started and not finished, <?= (int) $summary['failed'] ?> failed, <?= (int) $summary['waived'] ?> sign-up fee<?= $summary['waived'] === 1 ? '' : 's' ?> waived by you.</p>

    <div class="card"><div class="card__header"><h2 class="card__title">Payments received per day</h2></div><div class="card__body">
        <div class="vis-days" role="img" aria-label="Payments received per day">
            <?php foreach ($daily as $d): ?><span class="vis-days__bar" style="height: <?= max(2, (int) round($d['n'] * 100 / $maxDay)) ?>%" title="<?= e(date('j M', strtotime($d['day']))) ?>: <?= (int) $d['n'] ?>"></span><?php endforeach; ?>
        </div>
    </div></div>

    <div class="card"><div class="card__header"><h2 class="card__title">Latest payments</h2></div><div class="card__body">
        <?php if ($events === []): ?>
            <?= partial('partials/empty-state', ['title' => 'No payment matches', 'icon' => 'credit-card', 'text' => 'Payments appear here the moment they are made or reported. Widen the period or change the filters.']) ?>
        <?php else: ?>
            <div class="table-wrap"><table class="table vis-table pay-table">
                <thead><tr><th>Time</th><th>Learner</th><th>Fee</th><th class="vis-num">Amount</th><th>How</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($events as $e): [$sl, $st] = $statusLabel[$e['status']] ?? [$e['status'], 'neutral']; ?>
                    <tr data-pay-key="<?= e($e['key']) ?>">
                        <td class="nowrap"><?= e(format_datetime($e['at'], 'j M, H:i')) ?></td>
                        <td><?php if ($e['learner_id'] > 0): ?><a href="<?= e(url('admin/academy/learners/' . (int) $e['learner_id'])) ?>"><?= e($e['learner'] ?: 'Learner') ?></a><?php else: ?><?= e($e['learner'] ?: 'Unknown') ?><?php endif; ?></td>
                        <td><?= $e['type'] === 'cert' ? 'Certificate' : 'Sign-up' ?><?= $e['item'] !== '' ? '<span class="cell-sub">' . e($e['item']) . '</span>' : '' ?></td>
                        <td class="vis-num"><?= $e['minor'] > 0 ? e(PaymentsReport::money((int) $e['minor'], (string) $e['currency'])) . ($e['estimated'] ? ' <abbr title="Estimated: the current fee. The amount typed by hand is not recorded.">~</abbr>' : '') : '-' ?></td>
                        <td><?= e($e['method']) ?><?= $e['reference'] !== '' ? '<span class="cell-sub">Ref: ' . e($e['reference']) . '</span>' : '' ?></td>
                        <td><span class="badge badge--<?= e($st) ?>"><?= e($sl) ?></span></td>
                        <td><?php if ($e['mark'] > 0): ?><form method="post" action="<?= e(url('admin/academy/learners/' . (int) $e['mark'] . '/mark-fee-paid')) ?>" class="inline-form" data-confirm="Mark the sign-up fee of <?= e($e['learner']) ?> as paid? Do this only after you checked the money."><?= csrf_field() ?><input type="hidden" name="back" value="payments"><button class="btn btn--primary btn--sm" type="submit">Mark as paid</button></form><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
    </div></div>
</div>
<p class="text-muted vis-note" data-pay-clock>Updated <?= e(date('H:i:s')) ?>. Payments with Pesapal are recorded the moment Pesapal confirms them. Mobile-money and bank payments appear as "Waiting for you" when the learner reports them, and as "Received" once you mark them as paid. Amounts marked ~ are estimates (the current fee).</p>
