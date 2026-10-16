<?php
/**
 * 30-day stacked column chart, dependency-free (HTML + CSS only). OWNER: B3. Var: $chart from DashboardData::chart().
 * Accessibility: the plot is described by aria-label, every column is keyboard-focusable and carries its numbers as a tooltip,
 * the legend names every series (colour is never the only cue) and the same data is available as a table.
 */
$days = $chart['days'];
$series = $chart['series'];
$max = (int) $chart['max'];
$top = 4;
foreach ([4, 6, 10, 20, 40, 60, 100, 200, 400, 600, 1000, 2000, 4000] as $step) {
    $top = $step;
    if ($max <= $step) {
        break;
    }
}
if ($max > $top) {
    $top = (int) (ceil($max / 100) * 100);
}
$grand = array_sum(array_column($series, 'total'));
$keys = array_keys($series);
$n = count($days);
$sentence = static function (array $day) use ($series): string {
    $parts = [];
    foreach ($series as $k => $s) {
        if (($day['v'][$k] ?? 0) > 0) {
            $parts[] = $day['v'][$k] . ' ' . mb_strtolower($s['label']);
        }
    }
    return $day['label'] . ': ' . ($parts === [] ? 'no activity' : implode(', ', $parts));
};
?>
<section class="card dash-chart" aria-labelledby="dash-chart-title">
    <div class="card__header">
        <div>
            <h2 class="card__title" id="dash-chart-title">Incoming activity, last 30 days</h2>
            <p class="dash-chart__sub"><?= $grand === 0 ? 'Nothing received yet.' : number_format($grand) . ' new ' . ($grand === 1 ? 'item' : 'items') . ' in total' ?></p>
        </div>
        <ul class="dash-legend" aria-label="Chart key">
            <?php foreach ($keys as $i => $k): ?>
                <li><span class="dash-swatch dash-swatch--<?= $i + 1 ?>" aria-hidden="true"></span><?= e($series[$k]['label']) ?> <strong><?= (int) $series[$k]['total'] ?></strong></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card__body">
        <div class="dash-chart__frame">
            <div class="dash-chart__y" aria-hidden="true">
                <span><?= (int) $top ?></span><span><?= (int) ($top / 2) ?></span><span>0</span>
            </div>
            <div class="dash-chart__plot" role="group" aria-label="Stacked columns, one per day for the last 30 days. <?= (int) $grand ?> items in total. The same numbers are in the table below.">
                <?php foreach ($days as $i => $day): ?>
                    <?php $h = $top > 0 ? round($day['total'] / $top * 100, 2) : 0; ?>
                    <div class="dash-bar<?= $i < 4 ? ' dash-bar--start' : '' ?><?= $i > $n - 5 ? ' dash-bar--end' : '' ?>" tabindex="0" role="img" aria-label="<?= e($sentence($day)) ?>" data-tip="<?= e($sentence($day)) ?>">
                        <span class="dash-bar__stack" style="height: <?= e((string) $h) ?>%">
                            <?php foreach ($keys as $si => $k): if (($day['v'][$k] ?? 0) < 1) { continue; } ?>
                                <span class="dash-bar__seg dash-bar__seg--<?= $si + 1 ?>" style="flex-grow: <?= (int) $day['v'][$k] ?>"></span>
                            <?php endforeach; ?>
                        </span>
                        <?php if (($n - 1 - $i) % 10 === 0): ?><span class="dash-bar__x" aria-hidden="true"><?= e(format_date($day['date'], 'j M')) ?></span><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <details class="dash-table">
            <summary>Show the numbers as a table</summary>
            <div class="table-wrap">
                <table class="table table--striped">
                    <caption class="visually-hidden">Incoming activity per day. Days with no activity are left out.</caption>
                    <thead><tr><th scope="col">Day</th><?php foreach ($keys as $k): ?><th scope="col" class="col-num"><?= e($series[$k]['label']) ?></th><?php endforeach; ?></tr></thead>
                    <tbody>
                    <?php $any = false; foreach (array_reverse($days) as $day): if ($day['total'] === 0) { continue; } $any = true; ?>
                        <tr><th scope="row"><?= e($day['label']) ?></th><?php foreach ($keys as $k): ?><td class="col-num"><?= (int) $day['v'][$k] ?></td><?php endforeach; ?></tr>
                    <?php endforeach; ?>
                    <?php if (!$any): ?><tr><td colspan="<?= count($keys) + 1 ?>">No activity in the last 30 days.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</section>
