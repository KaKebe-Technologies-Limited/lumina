<?php
declare(strict_types=1);
/**
 * Charts of the LAF Academy dashboards, drawn with plain HTML and CSS (no script, no library): used by the public page /academy/impact, the block on /academy and
 * Admin > LAF Academy > Statistics. Styles: scss/components/_academy-dash.scss (.ad-*, loaded by the site and by the admin).
 * Vars: $part = 'kpis' | 'topics' | 'countries' | 'months' | 'funnel', and the data that part needs:
 *   kpis: $overview (AcademyStats::overview())      topics: $topics (byTopic()), $limit (rows, default all)      countries: $countries (byCountry())
 *   months: $months (byMonth())      funnel: $funnel (funnel()).      $empty = the sentence shown when there is nothing to draw yet.
 */
use App\Services\Academy\AcademyStats;

$part = (string) ($part ?? '');
$adEmpty = (string) ($empty ?? 'Nothing to show yet. The numbers appear as learners join.');
$pal = AcademyStats::PALETTE;

if ($part === 'kpis'):
    $o = (array) ($overview ?? []);
    $tiles = [
        ['users', (int) ($o['learners'] ?? 0), 'Learners'],
        ['globe', (int) ($o['countries'] ?? 0), 'Countries'],
        ['layers', (int) ($o['courses'] ?? 0), 'Courses'],
        ['book-open', (int) ($o['lessons'] ?? 0), 'Lessons'],
        ['check-circle', (int) ($o['enrollments'] ?? 0), 'Course enrolments'],
        ['award', (int) ($o['certificates'] ?? 0), 'Certificates issued'],
    ]; ?>
    <ul class="ad-kpis">
        <?php foreach ($tiles as $t): ?>
            <li class="ad-kpi"><span class="ad-kpi__icon"><?= icon($t[0]) ?></span><b data-count-to="<?= (int) $t[1] ?>"><?= number_format((int) $t[1]) ?></b><span class="ad-kpi__label"><?= e($t[2]) ?></span></li>
        <?php endforeach; ?>
    </ul>
<?php elseif ($part === 'topics'):
    $rows = array_values(array_filter((array) ($topics ?? []), static fn(array $t): bool => $t['learners'] > 0 || $t['courses'] > 0));
    $useLearners = array_sum(array_column($rows, 'learners')) > 0;
    $metric = $useLearners ? 'learners' : 'courses';
    $max = max(1, ...array_map(static fn(array $t): int => (int) $t[$metric], $rows ?: [['learners' => 1, 'courses' => 1]]));
    if (isset($limit)) {
        $rows = array_slice($rows, 0, (int) $limit);
    }
    if (!$rows): ?><p class="ad-empty"><?= e($adEmpty) ?></p><?php else: ?>
    <ol class="ad-bars">
        <?php foreach ($rows as $i => $t): $w = round((int) $t[$metric] * 100 / $max, 1); ?>
            <li class="ad-bar">
                <span class="ad-bar__label"><?= icon((string) $t['icon'], 'icon--sm') ?> <?= e($t['label']) ?></span>
                <span class="ad-bar__track" aria-hidden="true"><span class="ad-bar__fill" style="--w:<?= $w ?>%;--c:<?= e($pal[$i % count($pal)]) ?>"></span></span>
                <span class="ad-bar__value"><b><?= number_format((int) $t['learners']) ?></b> <?= $t['learners'] === 1 ? 'learner' : 'learners' ?><span> &middot; <?= (int) $t['courses'] ?> <?= $t['courses'] === 1 ? 'course' : 'courses' ?></span></span>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
<?php elseif ($part === 'countries'):
    $c = (array) ($countries ?? ['rows' => [], 'total' => 0, 'countries' => 0]);
    $rows = (array) $c['rows'];
    if (!$rows): ?><p class="ad-empty"><?= e($adEmpty) ?></p><?php else:
        $stops = [];
        $acc = 0.0;
        foreach ($rows as $i => $r) {
            $from = $acc;
            $acc += (float) $r['pct'];
            $stops[] = $pal[$i % count($pal)] . ' ' . round($from, 2) . '% ' . round(min(100, $acc), 2) . '%';
        } ?>
    <div class="ad-donut-wrap">
        <div class="ad-donut" style="background:conic-gradient(<?= e(implode(', ', $stops)) ?>)" role="img" aria-label="Learners by country: <?= e(implode(', ', array_map(static fn(array $r): string => $r['country'] . ' ' . $r['pct'] . '%', $rows))) ?>">
            <span class="ad-donut__hole"><b><?= number_format((int) $c['countries']) ?></b><span><?= (int) $c['countries'] === 1 ? 'country' : 'countries' ?></span></span>
        </div>
        <ul class="ad-legend">
            <?php foreach ($rows as $i => $r): ?>
                <li><span class="ad-legend__dot" style="--c:<?= e($pal[$i % count($pal)]) ?>"></span><span class="ad-legend__name"><?= e($r['country']) ?></span><span class="ad-legend__n"><b><?= number_format((int) $r['n']) ?></b> <span>(<?= rtrim(rtrim(number_format((float) $r['pct'], 1), '0'), '.') ?>%)</span></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
<?php elseif ($part === 'months'):
    $m = (array) ($months ?? []);
    $max = max(1, ...array_map(static fn(array $x): int => max((int) $x['signups'], (int) $x['certificates']), $m ?: [['signups' => 1, 'certificates' => 1]]));
    if (array_sum(array_column($m, 'signups')) + array_sum(array_column($m, 'certificates')) === 0): ?><p class="ad-empty"><?= e($adEmpty) ?></p><?php else: ?>
    <div class="ad-cols" role="img" aria-label="New learners and certificates per month">
        <?php foreach ($m as $x): ?>
            <div class="ad-col">
                <span class="ad-col__bars">
                    <span class="ad-col__bar ad-col__bar--a" style="--h:<?= round((int) $x['signups'] * 100 / $max, 1) ?>%" title="<?= e($x['ym']) ?>: <?= (int) $x['signups'] ?> new learners"><i><?= (int) $x['signups'] ?></i></span>
                    <span class="ad-col__bar ad-col__bar--b" style="--h:<?= round((int) $x['certificates'] * 100 / $max, 1) ?>%" title="<?= e($x['ym']) ?>: <?= (int) $x['certificates'] ?> certificates"><i><?= (int) $x['certificates'] ?></i></span>
                </span>
                <span class="ad-col__label"><?= e($x['label']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="ad-key"><span class="ad-key__dot ad-key__dot--a"></span> New learners <span class="ad-key__dot ad-key__dot--b"></span> Certificates issued</p>
    <?php endif; ?>
<?php elseif ($part === 'funnel'):
    $f = (array) ($funnel ?? []);
    if (($f[0]['n'] ?? 0) === 0): ?><p class="ad-empty"><?= e($adEmpty) ?></p><?php else: ?>
    <ol class="ad-funnel">
        <?php foreach ($f as $i => $s): ?>
            <li class="ad-funnel__step"><span class="ad-funnel__bar" style="--w:<?= max(4, (float) $s['pct']) ?>%;--c:<?= e($pal[min($i, 3) % count($pal)]) ?>"><span><?= e($s['label']) ?></span></span><span class="ad-funnel__n"><b><?= number_format((int) $s['n']) ?></b> <span>(<?= rtrim(rtrim(number_format((float) $s['pct'], 1), '0'), '.') ?>%)</span></span></li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
<?php endif; ?>
