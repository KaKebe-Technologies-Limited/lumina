<?php
/** Opportunities list. Vars from Content\CrudController::index() plus $categories. */
$th = static fn(string $label, string $key, string $class = ''): string => partial('admin/_content/th-sort', [
    'label' => $label, 'key' => $key, 'sort' => $sort, 'dir' => $dir, 'defaultSort' => $defaultSort, 'listUrl' => $listUrl, 'class' => $class,
]);
$waiting = 0;
foreach ($tabs as $t) {
    if ($t['key'] === 'review') {
        $waiting = (int) $t['count'];
    }
}
$today = date('Y-m-d');
?>
<?php $this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', [
    'title' => 'Opportunities',
    'subtitle' => 'Scholarships, fellowships, internships, grants and more for young people. Add your own, or let the website read them from other opportunity websites.',
    'actions' => [
        ['label' => 'Add an opportunity', 'url' => url('admin/opportunities/create'), 'icon' => 'plus'],
        ['label' => 'Sources (other websites)', 'url' => url('admin/opportunity-sources'), 'icon' => 'globe', 'class' => 'btn--outline'],
    ],
]) ?>

<?php if ($waiting > 0): ?>
    <form class="alert alert--info" method="post" action="<?= e(url('admin/opportunities/approve-all')) ?>">
        <?= csrf_field() ?>
        <strong><?= (int) $waiting ?> <?= $waiting === 1 ? 'opportunity is' : 'opportunities are' ?> waiting for your approval.</strong>
        Open the "Waiting for approval" tab to read them, or
        <button class="btn btn--outline btn--sm" type="submit">Approve all</button>
    </form>
<?php endif; ?>

<?= partial('admin/_content/tabs', ['tabs' => $tabs, 'listUrl' => $listUrl]) ?>
<?= partial('admin/_content/filters', [
    'path' => $c['path'], 'q' => $q, 'placeholder' => 'Search by title, organisation or place', 'perPage' => $perPage, 'hasFilters' => $hasFilters, 'total' => $total, 'keep' => $keep,
    'selects' => [['name' => 'category', 'label' => 'Type', 'options' => $categories, 'value' => $filters['category'] ?? '', 'empty' => 'Any type']],
]) ?>

<?php if ($rows === []): ?>
    <?= partial('admin/_content/empty', ['c' => $c, 'hasFilters' => $hasFilters, 'createUrl' => url('admin/opportunities/create'), 'icon' => 'award', 'createLabel' => 'Add the first opportunity',
        'hint' => 'Opportunities you add, and the ones read from other websites, appear on the Opportunities page.']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--stack">
            <caption class="visually-hidden">Opportunities</caption>
            <thead>
            <tr>
                <?= $th('Opportunity', 'title') ?>
                <th scope="col">Type</th>
                <?= $th('Closing date', 'deadline') ?>
                <th scope="col">Source</th>
                <th scope="col">Status</th>
                <th scope="col">Visible</th>
                <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r):
                $closed = $r['deadline'] !== null && $r['deadline'] < $today;
                $left = $r['days_left'] === null ? null : (int) $r['days_left'];
                if (!empty($r['needs_review'])) { $st = ['review', 'Waiting for approval', 'warning']; }
                elseif (empty($r['is_active'])) { $st = ['hidden', 'Hidden', 'neutral']; }
                elseif ($closed) { $st = ['closed', 'Closed', 'danger']; }
                elseif ($left !== null && $left <= 7) { $st = ['closing', 'Closing soon', 'warning']; }
                else { $st = ['open', 'Open', 'success']; }
            ?>
                <tr>
                    <td class="cell-main" data-label="Opportunity">
                        <span class="cell-title"><a href="<?= e(url('admin/opportunities/' . (int) $r['id'] . '/edit')) ?>"><?= e($r['title']) ?></a><?php if (!empty($r['is_featured'])): ?> <?= icon('star', 'icon--sm') ?><span class="visually-hidden">Featured</span><?php endif; ?></span>
                        <span class="cell-sub"><?= e(trim(($r['organisation'] ?: '') . ($r['organisation'] && $r['location'] ? ' · ' : '') . ($r['location'] ?: ''), ' ·') ?: '/opportunities/' . $r['slug']) ?></span>
                    </td>
                    <td data-label="Type"><span class="badge badge--outline"><?= e(\App\Models\Opportunity::categoryLabel($r['category'])) ?></span></td>
                    <td data-label="Closing date">
                        <?php if ($r['deadline']): ?>
                            <span class="nowrap"><?= e(format_date($r['deadline'], 'j M Y')) ?></span>
                            <span class="cell-sub"><?= $closed ? e(abs((int) $left) . ' day' . (abs((int) $left) === 1 ? '' : 's') . ' ago') : ($left === 0 ? 'today' : e($left . ' day' . ($left === 1 ? '' : 's') . ' left')) ?></span>
                        <?php else: ?><span class="text-muted">No deadline</span><?php endif; ?>
                    </td>
                    <td data-label="Source"><?= $r['source'] === 'feed' ? e((string) $r['source_name']) : 'Added by us' ?></td>
                    <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $st[0], 'label' => $st[1], 'tone' => $st[2]]) ?></td>
                    <td data-label="Visible"><?= partial('admin/_content/active-toggle', ['c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'onLabel' => 'Shown', 'offLabel' => 'Hidden']) ?></td>
                    <td class="col-actions">
                        <?php if (!empty($r['needs_review'])): ?>
                            <form method="post" action="<?= e(url('admin/opportunities/' . (int) $r['id'] . '/approve')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="return" value="<?= e($returnQs) ?>">
                                <button class="btn btn--outline btn--sm" type="submit">Approve<span class="visually-hidden">: <?= e($r['title']) ?></span></button>
                            </form>
                        <?php endif; ?>
                        <?= partial('admin/_content/row-actions', [
                            'c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'canReorder' => false,
                            'publicUrl' => (!empty($r['is_active']) && empty($r['needs_review'])) ? url('opportunities/' . $r['slug']) : null,
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => $c['path']]) ?>
<?php endif; ?>
