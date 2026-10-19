<?php
/**
 * Admin > Forms > Summary. Vars: $form, $days, $ranges, $overview, $perDay, $tallies, $contexts, $labels, $canExport, $canManage.
 * COUNTS ONLY (no personal data). Dependency-free: the chart is plain HTML / CSS, readable without CSS or JavaScript through the table under it.
 */
$fid = (int) $form['id'];
$base = 'admin/forms/' . $fid . '/submissions';
$counts = array_column($perDay, 'count');
$max = max(1, ...$counts);
$top = $max <= 4 ? $max : ($max <= 20 ? (int) (ceil($max / 5) * 5) : (int) (ceil($max / 10) * 10));
$ticks = array_values(array_unique(array_filter([$top, $top % 2 === 0 ? (int) ($top / 2) : null, 0], static fn($v): bool => $v !== null)));
$sumRange = array_sum($counts);
$peak = array_search($max, $counts, true);
$n = count($perDay);
$every = max(1, (int) ceil($n / 6));
$by = $overview['by_status'];
$totalAll = max(1, (int) $overview['total']);
$tone = ['new' => 'warning', 'read' => 'neutral', 'starred' => 'gold', 'shortlisted' => 'info', 'accepted' => 'success', 'rejected' => 'danger', 'archived' => 'neutral', 'spam' => 'danger'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-form-submissions.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox admin-forms fs-stats">
    <?= partial('partials/admin/page-header', [
        'title' => (string) $form['title'],
        'subtitle' => 'Summary of the answers',
        'actions' => array_values(array_filter([
            ['label' => 'All answers', 'url' => url($base), 'class' => 'btn--outline', 'icon' => 'inbox'],
            $canExport ? ['label' => 'Export CSV', 'url' => url($base . '/export'), 'class' => 'btn--outline', 'icon' => 'download'] : null,
        ])),
    ]) ?>

    <div class="stat-grid">
        <a class="stat-tile" href="<?= e(url($base)) ?>"><span class="stat-tile__text"><span class="stat-tile__value"><?= (int) $overview['total'] ?></span><span class="stat-tile__label">Answers in total</span></span></a>
        <a class="stat-tile<?= (int) $overview['unread'] > 0 ? ' stat-tile--alert' : '' ?>" href="<?= e(url($base, ['status' => 'new'])) ?>"><span class="stat-tile__text"><span class="stat-tile__value"><?= (int) $overview['unread'] ?></span><span class="stat-tile__label">New, not opened yet</span></span></a>
        <div class="stat-tile"><span class="stat-tile__text"><span class="stat-tile__value"><?= (int) $overview['week'] ?></span><span class="stat-tile__label">Last 7 days</span></span></div>
        <div class="stat-tile"><span class="stat-tile__text"><span class="stat-tile__value"><?= (int) $overview['month'] ?></span><span class="stat-tile__label">Last 30 days</span></span></div>
    </div>

    <section class="card" aria-labelledby="fs-chart-title">
        <div class="card__header">
            <h2 class="card__title" id="fs-chart-title">Answers per day</h2>
            <div class="fs-range" role="group" aria-label="Period shown">
                <?php foreach ($ranges as $r): ?><a class="btn btn--sm <?= $r === $days ? 'btn--primary' : 'btn--outline' ?>" href="<?= e(url($base . '/stats', $r === 30 ? [] : ['days' => $r])) ?>"<?= $r === $days ? ' aria-current="true"' : '' ?>><?= (int) $r ?> days</a><?php endforeach; ?>
            </div>
        </div>
        <div class="card__body">
            <?php if ($sumRange === 0): ?>
                <p class="text-muted">No answers in the last <?= (int) $days ?> days.</p>
            <?php else: ?>
                <p class="fs-chart__sum"><strong><?= (int) $sumRange ?></strong> answer<?= $sumRange === 1 ? '' : 's' ?> in the last <?= (int) $days ?> days. Busiest day: <strong><?= e(format_date($perDay[$peak]['date'], 'j M')) ?></strong> (<?= (int) $max ?>).</p>
            <?php endif; ?>
            <div class="fs-chart" data-fs-chart role="img" aria-label="Bar chart of the answers per day for the last <?= (int) $days ?> days. <?= (int) $sumRange ?> in total. The table below the chart has the same numbers.">
                <div class="fs-chart__axis" aria-hidden="true">
                    <?php foreach ($ticks as $t): ?><span style="bottom: <?= e((string) round($t / $top * 100, 2)) ?>%"><?= (int) $t ?></span><?php endforeach; ?>
                </div>
                <ol class="fs-chart__bars" aria-hidden="true">
                    <?php foreach ($perDay as $i => $d): $h = $d['count'] === 0 ? 0 : max(3, round($d['count'] / $top * 100, 2)); ?>
                        <li class="fs-chart__col" data-fs-point="<?= e(format_date($d['date'], 'l j F') . ': ' . $d['count'] . ($d['count'] === 1 ? ' answer' : ' answers')) ?>">
                            <span class="fs-chart__bar<?= $d['count'] === 0 ? ' is-zero' : '' ?>" style="height: <?= e((string) $h) ?>%"></span>
                            <?php if ($i % $every === 0 || $i === $n - 1): ?><span class="fs-chart__label"><?= e(format_date($d['date'], 'j M')) ?></span><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <p class="fs-chart__readout small text-muted" data-fs-readout aria-hidden="true">Point at a bar to see its day.</p>
            <details class="fs-details">
                <summary>Show the numbers</summary>
                <div class="table-wrap">
                    <table class="table fs-numbers"><thead><tr><th scope="col">Day</th><th scope="col" class="col-num">Answers</th></tr></thead><tbody>
                    <?php foreach (array_reverse($perDay) as $d): ?><tr><td><?= e(format_date($d['date'], 'D j M Y')) ?></td><td class="col-num"><?= (int) $d['count'] ?></td></tr><?php endforeach; ?>
                    </tbody></table>
                </div>
            </details>
        </div>
    </section>

    <div class="fs-grid">
        <section class="card" aria-labelledby="fs-status-title">
            <div class="card__header"><h2 class="card__title" id="fs-status-title">By status</h2></div>
            <div class="card__body">
                <ul class="fs-bars">
                    <?php foreach ($by as $key => $cnt): $pct = (int) round($cnt / $totalAll * 100); ?>
                        <li>
                            <span class="fs-bars__label"><a href="<?= e(url($base, ['status' => $key])) ?>"><?= e($labels[$key] ?? ucfirst($key)) ?></a></span>
                            <span class="fs-bars__track" aria-hidden="true"><span class="fs-bars__fill fs-tone-<?= e($tone[$key] ?? 'neutral') ?>" style="width: <?= $cnt > 0 ? max(2, $pct) : 0 ?>%"></span></span>
                            <span class="fs-bars__num"><?= (int) $cnt ?> <span class="text-muted">(<?= $pct ?>%)</span></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

        <?php if ($contexts !== []): $cmax = max(1, ...array_column($contexts, 'n')); ?>
            <section class="card" aria-labelledby="fs-ctx-title">
                <div class="card__header"><h2 class="card__title" id="fs-ctx-title">By position</h2></div>
                <div class="card__body">
                    <ul class="fs-bars">
                        <?php foreach ($contexts as $c): ?>
                            <li>
                                <span class="fs-bars__label"><a href="<?= e(url($base, ['ref' => $c['ref']])) ?>"><?= e($c['label']) ?></a></span>
                                <span class="fs-bars__track" aria-hidden="true"><span class="fs-bars__fill" style="width: <?= max(2, (int) round($c['n'] / $cmax * 100)) ?>%"></span></span>
                                <span class="fs-bars__num"><?= (int) $c['n'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <?php if ($tallies !== []): ?>
        <h2 class="fs-section-title">Answers to the choice questions</h2>
        <p class="small text-muted">Spam is not counted. Choices removed from the form later still appear with the name they had.</p>
        <div class="fs-grid">
        <?php foreach ($tallies as $t): ?>
            <section class="card" aria-labelledby="fs-q-<?= e($t['id']) ?>">
                <div class="card__header"><h3 class="card__title" id="fs-q-<?= e($t['id']) ?>"><?= e($t['label']) ?></h3></div>
                <div class="card__body">
                    <p class="small text-muted">
                        <?= (int) $t['total'] ?> answered<?php if ($t['average'] !== null): ?>
                            &middot; average <strong><?= e(number_format((float) $t['average'], 1)) ?></strong> out of 5
                            <span class="fs-stars" aria-hidden="true"><?= str_repeat('&#9733;', (int) round((float) $t['average'])) ?><span class="fs-stars__off"><?= str_repeat('&#9733;', 5 - (int) round((float) $t['average'])) ?></span></span><?php endif; ?>
                    </p>
                    <?php if ($t['total'] === 0): ?>
                        <p class="text-muted">No one has answered this question yet.</p>
                    <?php else: ?>
                        <ul class="fs-bars">
                            <?php foreach ($t['options'] as $o): $pct = (int) round($o['share'] * 100); ?>
                                <li>
                                    <span class="fs-bars__label"><?= e($t['type'] === 'rating' ? $o['label'] . ($o['label'] === '1' ? ' star' : ' stars') : $o['label']) ?></span>
                                    <span class="fs-bars__track" aria-hidden="true"><span class="fs-bars__fill" style="width: <?= $o['count'] > 0 ? max(2, $pct) : 0 ?>%"></span></span>
                                    <span class="fs-bars__num"><?= (int) $o['count'] ?> <span class="text-muted">(<?= $pct ?>%)</span></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
        </div>
    <?php elseif ((int) $overview['total'] > 0): ?>
        <p class="small text-muted">This form has no dropdown, single-choice, multiple-choice, yes/no or rating questions, so there is nothing to total per choice.</p>
    <?php endif; ?>
</div>
