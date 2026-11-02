<?php
/** Admin > Reports > one report. Vars: $report, $r (range), $periods, $res (rows, total, sums, truncated, error), $canExport, $groupLabel, $siblings. */
$qs = array_filter(['period' => $r['period'], 'from' => $r['fromDate'], 'to' => $r['toDate']], static fn($v): bool => $v !== '');
$actions = [['label' => 'All reports', 'url' => url('admin/reports'), 'icon' => 'bar-chart', 'class' => 'btn--outline']];
if ($canExport && $res['error'] === null && $res['rows'] !== []) {
    $actions[] = ['label' => 'Download CSV', 'url' => url('admin/reports/' . $report['key'] . '/export', $qs), 'icon' => 'download'];
}
$cols = $report['cols'];
$money = static fn(string $c): bool => in_array($c, ['amount', 'total', 'revenue', 'spent', 'value', 'price', 'annual_amount'], true);
$fmt = static function (string $c, mixed $v): string {
    if ($v === null || $v === '') {
        return '';
    }
    if (is_numeric($v) && !preg_match('/^\d{4}-\d{2}/', (string) $v) && !in_array($c, ['hour', 'year', 'issue_date', 'reg_no', 'code', 'order_number', 'phone'], true)) {
        return (float) $v == (int) (float) $v ? number_format((float) $v) : number_format((float) $v, 2);
    }
    return (string) $v;
};
?>
<?= partial('partials/admin/page-header', ['title' => $report['title'], 'subtitle' => $groupLabel . ': ' . $report['desc'], 'actions' => $actions]) ?>

<?php if ($report['range']): ?>
<form class="filters" method="get" action="<?= e(url('admin/reports/' . $report['key'])) ?>" role="search">
    <div class="field"><label class="field__label" for="rp-period">Period</label><select class="select" id="rp-period" name="period"><?php foreach ($periods as $k => $l): ?><option value="<?= e((string) $k) ?>"<?= $r['period'] === (string) $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="rp-from">From</label><input class="input" id="rp-from" type="date" name="from" value="<?= e($r['fromDate']) ?>"></div>
    <div class="field"><label class="field__label" for="rp-to">To</label><input class="input" id="rp-to" type="date" name="to" value="<?= e($r['toDate']) ?>"></div>
    <button class="btn btn--outline" type="submit">Show</button>
</form>
<p class="text-muted">From and To are used when the period is "Custom dates".</p>
<?php else: ?>
<p class="text-muted">This report shows the situation <strong>now</strong>, so it has no date range.</p>
<?php endif; ?>

<?php if ($res['error'] !== null): ?>
    <div class="alert alert--warning" role="status"><?= e($res['error']) ?></div>
<?php elseif ($res['rows'] === []): ?>
    <?= partial('partials/empty-state', ['title' => 'Nothing to report', 'icon' => 'bar-chart', 'text' => $report['range'] ? 'There is nothing for these dates. Try a longer period.' : 'There is nothing to show right now.']) ?>
<?php else: ?>
    <p class="result-count"><?= number_format($res['total']) ?> row<?= $res['total'] === 1 ? '' : 's' ?><?= $res['truncated'] ? ' (the first 500 are shown here; the CSV has them all)' : '' ?>
        <?php foreach ($res['sums'] as $c => $v): ?> &middot; <strong><?= e($cols[$c] ?? $c) ?>: <?= $v == (int) $v ? number_format($v) : number_format($v, 2) ?></strong><?php endforeach; ?></p>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden"><?= e($report['title']) ?></caption>
        <thead><tr><?php foreach ($cols as $c => $label): ?><th scope="col"<?= isset($res['sums'][$c]) || $money($c) ? ' class="text-right"' : '' ?>><?= e($label) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
        <?php foreach ($res['rows'] as $row): ?>
            <tr><?php foreach ($cols as $c => $label): ?><td data-label="<?= e($label) ?>"<?= isset($res['sums'][$c]) || $money($c) ? ' class="text-right"' : '' ?>><?= e($fmt($c, $row[$c] ?? '')) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php endif; ?>

<?php if ($siblings !== []): ?>
    <div class="card" style="margin-top:1.5rem"><div class="card__header"><h2 class="card__title">More reports: <?= e($groupLabel) ?></h2></div><div class="card__body">
        <ul style="margin:0;padding-left:1.1rem;display:grid;gap:.35rem"><?php foreach ($siblings as $s): ?><li><a href="<?= e(url('admin/reports/' . $s['key'], $qs)) ?>"><?= e($s['title']) ?></a></li><?php endforeach; ?></ul>
    </div></div>
<?php endif; ?>
