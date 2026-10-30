<?php
/** Admin > Visitors. Vars: $f (period, country, page), $periods, $summary, $pages, $countries, $daily, $recent, $referrers, $devices. */
use App\Services\Analytics\Countries;

$qs = static fn(array $over = []): string => url('admin/visitors', array_filter(array_merge(['period' => $f['period'], 'country' => $f['country'], 'page' => $f['page']], $over), static fn($v): bool => $v !== '' && $v !== null));
$maxPage = max(1, ...array_map(static fn(array $r): int => $r['views'], $pages ?: [['views' => 1]]));
$maxCountry = max(1, ...array_map(static fn(array $r): int => $r['views'], $countries ?: [['views' => 1]]));
$maxDay = max(1, ...array_map(static fn(array $r): int => $r['views'], $daily ?: [['views' => 1]]));
$devTotal = max(1, array_sum($devices));
$where = array_filter([$f['country'] !== '' ? ($f['country'] === 'unknown' ? 'unknown country' : Countries::name($f['country'])) : '', $f['page'] !== '' ? 'pages containing "' . $f['page'] . '"' : '']);
?>
<?= partial('partials/admin/page-header', ['title' => 'Visitors', 'subtitle' => 'Who opens the public website: filter by country and see the pages visited most. Staff, robots and visitors who send "Do Not Track" are not counted.', 'actions' => [['label' => 'Download CSV', 'url' => url('admin/visitors/export', array_filter(['period' => $f['period'], 'country' => $f['country'], 'page' => $f['page']])), 'icon' => 'download', 'class' => 'btn--outline']]]) ?>

<form class="filters" method="get" action="<?= e(url('admin/visitors')) ?>" role="search">
    <div class="field"><label class="field__label" for="v-period">Period</label><select class="select" id="v-period" name="period"><?php foreach ($periods as $k => $l): ?><option value="<?= e((string) $k) ?>"<?= (string) $f['period'] === (string) $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="v-country">Country</label><select class="select" id="v-country" name="country"><option value="">All countries</option><?php foreach ($countries as $c): ?><option value="<?= e($c['code'] ?? 'unknown') ?>"<?= $f['country'] === ($c['code'] ?? 'unknown') ? ' selected' : '' ?>><?= e($c['name']) ?> (<?= number_format($c['views']) ?>)</option><?php endforeach; ?></select></div>
    <div class="field field--grow"><label class="field__label" for="v-page">Page</label><input class="input" id="v-page" type="search" name="page" value="<?= e($f['page']) ?>" placeholder="Part of the address, e.g. academy or /about" maxlength="80"></div>
    <button class="btn btn--outline" type="submit">Filter</button>
    <?php if ($where !== []): ?><a class="btn btn--ghost" href="<?= e(url('admin/visitors', ['period' => $f['period']])) ?>">Clear filters</a><?php endif; ?>
</form>
<?php if ($where !== []): ?><p class="text-muted">Showing: <strong><?= e(implode(' and ', $where)) ?></strong>, <?= e(strtolower($periods[$f['period']])) ?>.</p><?php endif; ?>

<div class="vis-cards">
    <div class="card vis-card"><div class="card__body"><span class="vis-card__n"><?= number_format($summary['views']) ?></span><span class="vis-card__l">Page views</span></div></div>
    <div class="card vis-card"><div class="card__body"><span class="vis-card__n"><?= number_format($summary['visitors']) ?></span><span class="vis-card__l">Visitors <small>(counted once a day)</small></span></div></div>
    <div class="card vis-card"><div class="card__body"><span class="vis-card__n"><?= number_format($summary['countries']) ?></span><span class="vis-card__l">Countries</span></div></div>
    <div class="card vis-card"><div class="card__body"><span class="vis-card__n"><?= number_format($summary['pages']) ?></span><span class="vis-card__l">Different pages</span></div></div>
</div>

<?php if ($summary['views'] === 0): ?>
    <?= partial('partials/empty-state', ['title' => 'No visits recorded yet', 'icon' => 'globe', 'text' => 'Visits are counted from now on, as people open pages of the public website. Come back after the site has had some visitors, or widen the period and clear the filters.']) ?>
<?php else: ?>
    <div class="card"><div class="card__header"><h2 class="card__title">Visits per day</h2></div><div class="card__body">
        <div class="vis-days" role="img" aria-label="Page views per day">
            <?php foreach ($daily as $d): ?><span class="vis-days__bar" style="height: <?= max(2, (int) round($d['views'] * 100 / $maxDay)) ?>%" title="<?= e(date('j M', strtotime($d['day']))) ?>: <?= (int) $d['views'] ?>"></span><?php endforeach; ?>
        </div>
    </div></div>

    <div class="vis-grid">
        <div class="card"><div class="card__header"><h2 class="card__title">Most visited pages</h2></div><div class="card__body">
            <?php if ($pages === []): ?><p class="text-muted">No page matches.</p><?php else: ?>
            <div class="table-wrap"><table class="table vis-table">
                <thead><tr><th>Page</th><th class="vis-num">Views</th><th class="vis-num">Visitors</th></tr></thead>
                <tbody><?php foreach ($pages as $r): ?>
                    <tr><td><a href="<?= e(url(ltrim($r['path'], '/'))) ?>" target="_blank" rel="noopener"><?= e($r['path'] === '/' ? 'Home page' : $r['path']) ?></a>
                        <span class="vis-bar"><span style="width: <?= max(2, (int) round($r['views'] * 100 / $maxPage)) ?>%"></span></span></td>
                        <td class="vis-num"><?= number_format($r['views']) ?></td><td class="vis-num"><?= number_format($r['visitors']) ?></td></tr>
                <?php endforeach; ?></tbody>
            </table></div><?php endif; ?>
        </div></div>

        <div class="card"><div class="card__header"><h2 class="card__title">Visitors by country</h2></div><div class="card__body">
            <div class="table-wrap"><table class="table vis-table">
                <thead><tr><th>Country</th><th class="vis-num">Views</th><th class="vis-num">Visitors</th></tr></thead>
                <tbody><?php foreach ($countries as $r): $code = $r['code'] ?? 'unknown'; ?>
                    <tr<?= $f['country'] === $code ? ' class="is-active"' : '' ?>><td><a href="<?= e($qs(['country' => $code])) ?>"><?= e($r['name']) ?></a>
                        <span class="vis-bar"><span style="width: <?= max(2, (int) round($r['views'] * 100 / $maxCountry)) ?>%"></span></span></td>
                        <td class="vis-num"><?= number_format($r['views']) ?></td><td class="vis-num"><?= number_format($r['visitors']) ?></td></tr>
                <?php endforeach; ?></tbody>
            </table></div>
            <p class="text-muted vis-note">Click a country to see the pages visited from there.</p>
        </div></div>
    </div>

    <div class="vis-grid">
        <div class="card"><div class="card__header"><h2 class="card__title">Devices</h2></div><div class="card__body">
            <?php foreach (['desktop' => 'Computer', 'mobile' => 'Phone', 'tablet' => 'Tablet'] as $k => $l): $pct = (int) round($devices[$k] * 100 / $devTotal); ?>
                <p class="vis-dev"><span><?= e($l) ?></span><span class="vis-bar"><span style="width: <?= max($devices[$k] > 0 ? 2 : 0, $pct) ?>%"></span></span><strong><?= $pct ?>%</strong></p>
            <?php endforeach; ?>
            <h3 class="vis-sub">Came from</h3>
            <?php if ($referrers === []): ?><p class="text-muted">Mostly direct visits: no other website sent visitors in this period.</p><?php else: ?>
                <ul class="vis-list"><?php foreach ($referrers as $r): ?><li><span><?= e($r['host']) ?></span><strong><?= number_format($r['views']) ?></strong></li><?php endforeach; ?></ul>
            <?php endif; ?>
        </div></div>

        <div class="card"><div class="card__header"><h2 class="card__title">Latest visits</h2></div><div class="card__body">
            <div class="table-wrap"><table class="table vis-table">
                <thead><tr><th>Time</th><th>Country</th><th>Page</th><th>Device</th></tr></thead>
                <tbody><?php foreach ($recent as $r): ?>
                    <tr><td><?= e(format_datetime((string) $r['visited_at'], 'j M, H:i')) ?></td><td><?= e($r['country_name']) ?></td><td><?= e($r['path'] === '/' ? 'Home page' : $r['path']) ?></td><td><?= e(['desktop' => 'Computer', 'mobile' => 'Phone', 'tablet' => 'Tablet'][$r['device']] ?? $r['device']) ?></td></tr>
                <?php endforeach; ?></tbody>
            </table></div>
        </div></div>
    </div>
<?php endif; ?>
<p class="text-muted vis-note">Privacy: no IP address and no cookie is stored. Each visit keeps the page, the country, the device and the website the visitor came from, plus a one-day anonymous code used only to count visitors. The country comes from the visitor's network, or from the free ipwho.is lookup done once per visitor after the page was shown. Visits are kept for 400 days.</p>
