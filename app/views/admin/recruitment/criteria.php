<?php
/** Admin > Recruitment criteria. Vars: $ready, $scope, $scopes, $kpis (all, incl. switched off), $threshold. */
?>
<?= partial('partials/admin/page-header', ['title' => 'Recruitment criteria', 'subtitle' => 'The KPIs used to judge ambassadors, job applicants and volunteers. Criteria marked "from the form" are scored by the system from what the applicant wrote; the others are scored by the judge. Each is scored 0 to 5; the weights turn the scores into a percentage. A judge can switch any criterion off for one applicant.', 'actions' => [['label' => 'Back', 'url' => url($scopes[$scope]['list']), 'icon' => 'arrow-left', 'class' => 'btn--ghost']]]) ?>
<?php if (!$ready): ?>
    <div class="alert alert--warning" role="status">The recruitment tables could not be created automatically. Import <code>database/updates/2026-11-recruitment.sql</code> in phpMyAdmin, then reload this page.</div>
<?php else: ?>
<nav class="rc-tabs" aria-label="Role">
    <?php foreach ($scopes as $k => $c): ?><a class="btn btn--sm <?= $k === $scope ? 'btn--primary' : 'btn--outline' ?>" href="<?= e(url('admin/recruitment/criteria', ['scope' => $k])) ?>"><?= e($c['title']) ?></a><?php endforeach; ?>
</nav>
<div class="ad-letter-grid">
    <section class="card"><div class="card__body">
        <h2 class="card__title">Criteria for: <?= e($scopes[$scope]['title']) ?></h2>
        <?php foreach ($kpis as $k): ?>
            <form method="post" action="<?= e(url('admin/recruitment/criteria')) ?>" class="rc-kpi-row"><?= csrf_field() ?>
                <input type="hidden" name="scope" value="<?= e($scope) ?>"><input type="hidden" name="id" value="<?= (int) $k['id'] ?>">
                <input class="input" name="label" value="<?= e($k['label']) ?>" maxlength="190" aria-label="Criterion"><?php if (!empty($k['auto_key'])): ?><span class="rc-tag" title="The system scores this from the application form; the judge can change the points or switch it off for an applicant">from the form</span><?php endif; ?>
                <label class="rc-kpi-row__w">Weight <input class="input" type="number" name="weight" min="1" max="10" value="<?= (int) $k['weight'] ?>"></label>
                <label class="rc-kpi-row__w">Order <input class="input" type="number" name="sort_order" value="<?= (int) $k['sort_order'] ?>"></label>
                <label class="check"><input type="checkbox" name="is_active" value="1" <?= $k['is_active'] ? 'checked' : '' ?>> Used</label>
                <button class="btn btn--outline btn--sm" type="submit">Save</button>
            </form>
            <form method="post" action="<?= e(url('admin/recruitment/criteria/' . (int) $k['id'] . '/delete')) ?>" class="inline-form rc-kpi-del" data-confirm="Delete this criterion and the scores given for it?"><?= csrf_field() ?>
                <input type="hidden" name="scope" value="<?= e($scope) ?>"><button class="btn btn--ghost btn--sm" type="submit">Delete</button></form>
        <?php endforeach; ?>
        <h3 class="ad-letter-sub">Add a criterion</h3>
        <form method="post" action="<?= e(url('admin/recruitment/criteria')) ?>" class="rc-kpi-row"><?= csrf_field() ?>
            <input type="hidden" name="scope" value="<?= e($scope) ?>"><input type="hidden" name="id" value="0">
            <input class="input" name="label" maxlength="190" placeholder="For example: Knowledge of our programmes" aria-label="New criterion" required>
            <label class="rc-kpi-row__w">Weight <input class="input" type="number" name="weight" min="1" max="10" value="2"></label>
            <input type="hidden" name="sort_order" value="900">
            <button class="btn btn--primary btn--sm" type="submit">Add</button>
        </form>
    </div></section>
    <section class="card"><div class="card__body">
        <h2 class="card__title">Interview level</h2>
        <p class="text-muted">When the weighted score reaches this percentage (with every criterion scored), the applicant is invited for an interview and e-mailed. It applies to ambassadors, jobs and volunteers.</p>
        <form method="post" action="<?= e(url('admin/recruitment/criteria/threshold')) ?>" class="rc-kpi-row"><?= csrf_field() ?>
            <input type="hidden" name="scope" value="<?= e($scope) ?>">
            <label class="rc-kpi-row__w">Percent <input class="input" type="number" name="pct" min="1" max="100" value="<?= (int) $threshold ?>"></label>
            <button class="btn btn--primary btn--sm" type="submit">Save</button>
        </form>
        <p class="text-muted">How the score is worked out: each criterion's points (0 to 5) are multiplied by its weight; the total is divided by the best possible total.</p>
    </div></section>
</div>
<?php endif; ?>
