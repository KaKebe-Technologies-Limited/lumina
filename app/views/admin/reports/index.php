<?php /** Admin > Reports (the hub). Vars: $groups (group => list of reports), $meta (group => [label, icon]), $total. */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Reports', 'subtitle' => $total . ' reports across the whole website. Pick one, choose the dates, read it on screen or download it as a spreadsheet (CSV). You see the reports of the areas your role can use.']) ?>
<?php if ($groups === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No report for your role', 'icon' => 'bar-chart', 'text' => 'Ask an administrator to give your role access to an area of the website.']) ?>
<?php endif; ?>
<?php foreach ($groups as $g => $list): [$label, $icon] = $meta[$g] ?? [$g, 'bar-chart']; ?>
    <section class="card" style="margin-bottom:1rem" aria-labelledby="rg-<?= e($g) ?>">
        <div class="card__header"><h2 class="card__title" id="rg-<?= e($g) ?>"><?= icon($icon, 'icon--sm') ?> <?= e($label) ?> <small class="text-muted">(<?= count($list) ?>)</small></h2></div>
        <div class="card__body">
            <ul style="list-style:none;margin:0;padding:0;display:grid;gap:.75rem;grid-template-columns:repeat(auto-fill,minmax(17rem,1fr))">
                <?php foreach ($list as $rep): ?>
                    <li style="border:1px solid var(--border);border-radius:12px;padding:.85rem 1rem;background:#fff">
                        <a href="<?= e(url('admin/reports/' . $rep['key'])) ?>"><strong><?= e($rep['title']) ?></strong></a>
                        <div class="text-muted" style="font-size:.9rem;margin-top:.25rem"><?= e($rep['desc']) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endforeach; ?>
