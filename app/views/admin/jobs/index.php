<?php
/** Jobs (vacancies) list. OWNER: B4. Vars from Content\CrudController::index() plus $types. */
$th = static fn(string $label, string $key, string $class = ''): string => partial('admin/_content/th-sort', [
    'label' => $label, 'key' => $key, 'sort' => $sort, 'dir' => $dir, 'defaultSort' => $defaultSort, 'listUrl' => $listUrl, 'class' => $class,
]);
$today = date('Y-m-d');
?>
<?php $this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', [
    'title' => 'Jobs',
    'subtitle' => 'Vacancies for the Careers page. A vacancy closes by itself after its closing date; you do not have to remove it.',
    'actions' => [['label' => 'New vacancy', 'url' => url('admin/jobs/create'), 'icon' => 'plus']],
]) ?>

<?= partial('admin/_content/tabs', ['tabs' => $tabs, 'listUrl' => $listUrl]) ?>
<?= partial('admin/_content/filters', [
    'path' => $c['path'], 'q' => $q, 'placeholder' => 'Search by title, department or place', 'perPage' => $perPage, 'hasFilters' => $hasFilters, 'total' => $total, 'keep' => $keep,
    'selects' => [['name' => 'employment_type', 'label' => 'Type of work', 'options' => $types, 'value' => $filters['employment_type'] ?? '', 'empty' => 'Any type']],
]) ?>

<?php if ($rows === []): ?>
    <?= partial('admin/_content/empty', ['c' => $c, 'hasFilters' => $hasFilters, 'createUrl' => url('admin/jobs/create'), 'icon' => 'briefcase', 'createLabel' => 'Add the first vacancy',
        'hint' => 'Vacancies you add are listed on the Careers page until their closing date.']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--stack">
            <caption class="visually-hidden">Vacancies</caption>
            <thead>
            <tr>
                <?= $th('Vacancy', 'title') ?>
                <th scope="col">Type</th>
                <?= $th('Closing date', 'deadline') ?>
                <th scope="col">Status</th>
                <th scope="col">Visible</th>
                <?= $th('Order', 'order', 'col-num') ?>
                <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r):
                $closed = $r['closing_date'] !== null && $r['closing_date'] < $today;
                $left = $r['days_left'] === null ? null : (int) $r['days_left'];
                if (empty($r['is_active'])) { $st = ['hidden', 'Hidden', 'neutral']; }
                elseif ($closed) { $st = ['closed', 'Closed', 'danger']; }
                elseif ($left !== null && $left <= 7) { $st = ['closing', 'Closing soon', 'warning']; }
                else { $st = ['open', 'Open', 'success']; }
            ?>
                <tr>
                    <td class="cell-main" data-label="Vacancy">
                        <span class="cell-title"><a href="<?= e(url('admin/jobs/' . (int) $r['id'] . '/edit')) ?>"><?= e($r['title']) ?></a></span>
                        <span class="cell-sub"><?= e(trim(($r['department'] ?: '') . ($r['department'] && $r['location'] ? ' · ' : '') . ($r['location'] ?: ''), ' ·') ?: '/careers/' . $r['slug']) ?></span>
                    </td>
                    <td data-label="Type"><span class="badge badge--outline"><?= e($types[$r['employment_type']] ?? label_from($r['employment_type'])) ?></span></td>
                    <td data-label="Closing date">
                        <?php if ($r['closing_date']): ?>
                            <span class="nowrap"><?= e(format_date($r['closing_date'], 'j M Y')) ?></span>
                            <span class="cell-sub"><?= $closed ? e(abs((int) $left) . ' day' . (abs((int) $left) === 1 ? '' : 's') . ' ago') : ($left === 0 ? 'today' : e($left . ' day' . ($left === 1 ? '' : 's') . ' left')) ?></span>
                        <?php else: ?><span class="text-muted">No deadline</span><?php endif; ?>
                    </td>
                    <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $st[0], 'label' => $st[1], 'tone' => $st[2]]) ?></td>
                    <td data-label="Visible"><?= partial('admin/_content/active-toggle', ['c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'onLabel' => 'Shown', 'offLabel' => 'Hidden']) ?></td>
                    <td class="col-num" data-label="Order"><?= (int) $r['sort_order'] ?></td>
                    <td class="col-actions">
                        <?= partial('admin/_content/row-actions', [
                            'c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'canReorder' => $canReorder,
                            'publicUrl' => (!empty($r['is_active']) && !$closed) ? url('careers/' . $r['slug']) : null,
                            'deleteWarning' => 'Applications already received are kept. ',
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => $c['path']]) ?>
    <?php if (!$canReorder): ?><p class="form-note">The up and down arrows appear when the list is in its normal order (no search, filter or sorting).</p><?php endif; ?>
<?php endif; ?>
