<?php
/**
 * Projects list. OWNER: B4. Vars from Content\CrudController::index() plus $programs (id => title) and $statuses.
 */
$th = static fn(string $label, string $key, string $class = ''): string => partial('admin/_content/th-sort', [
    'label' => $label, 'key' => $key, 'sort' => $sort, 'dir' => $dir, 'defaultSort' => $defaultSort, 'listUrl' => $listUrl, 'class' => $class,
]);
$statusTone = ['ongoing' => 'success', 'planned' => 'info', 'completed' => 'neutral'];
?>
<?php $this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', [
    'title' => 'Projects',
    'subtitle' => 'Projects appear on the Projects page. A project can belong to one of your programs.',
    'actions' => [['label' => 'New project', 'url' => url('admin/projects/create'), 'icon' => 'plus']],
]) ?>

<?= partial('admin/_content/tabs', ['tabs' => $tabs, 'listUrl' => $listUrl]) ?>
<?= partial('admin/_content/filters', [
    'path' => $c['path'], 'q' => $q, 'placeholder' => 'Search by title, location or text', 'perPage' => $perPage, 'hasFilters' => $hasFilters, 'total' => $total, 'keep' => $keep,
    'selects' => [
        ['name' => 'project_status', 'label' => 'Stage', 'options' => $statuses, 'value' => $filters['project_status'] ?? '', 'empty' => 'Any stage'],
        ['name' => 'program', 'label' => 'Program', 'options' => ['none' => 'No program'] + $programs, 'value' => $filters['program'] ?? '', 'empty' => 'Any program'],
    ],
]) ?>

<?php if ($rows === []): ?>
    <?= partial('admin/_content/empty', ['c' => $c, 'hasFilters' => $hasFilters, 'createUrl' => url('admin/projects/create'), 'icon' => 'folder']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--stack">
            <caption class="visually-hidden">Projects</caption>
            <thead>
            <tr>
                <th scope="col" class="col-narrow"><span class="visually-hidden">Picture</span></th>
                <?= $th('Project', 'title') ?>
                <?= $th('Program', 'program') ?>
                <?= $th('Stage', 'status') ?>
                <?= $th('Order', 'order', 'col-num') ?>
                <th scope="col">On website</th>
                <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="col-narrow" data-label="Picture">
                        <?php if (!empty($r['image'])): ?><img class="cell-thumb" src="<?= e(img_url($r['image'])) ?>" alt="" loading="lazy"><?php else: ?><span class="cell-thumb cell-thumb--empty" aria-hidden="true"><?= icon('image', 'icon--sm') ?></span><?php endif; ?>
                    </td>
                    <td class="cell-main" data-label="Project">
                        <span class="cell-title"><a href="<?= e(url('admin/projects/' . (int) $r['id'] . '/edit')) ?>"><?= e($r['title']) ?></a>
                            <?php if (!empty($r['is_featured'])): ?><span class="badge badge--gold">Featured</span><?php endif; ?></span>
                        <span class="cell-sub"><?= e($r['location'] ?: '/projects/' . $r['slug']) ?></span>
                    </td>
                    <td data-label="Program"><?= $r['program_title'] !== null ? e($r['program_title']) : '<span class="text-muted">None</span>' ?></td>
                    <td data-label="Stage"><?= partial('partials/admin/status-badge', ['status' => $r['status'], 'tone' => $statusTone[$r['status']] ?? 'neutral', 'label' => $statuses[$r['status']] ?? $r['status']]) ?></td>
                    <td class="col-num" data-label="Order"><?= (int) $r['sort_order'] ?></td>
                    <td data-label="On website"><?= partial('admin/_content/active-toggle', ['c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'onLabel' => 'Shown', 'offLabel' => 'Hidden']) ?></td>
                    <td class="col-actions">
                        <?= partial('admin/_content/row-actions', [
                            'c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'canReorder' => $canReorder,
                            'publicUrl' => !empty($r['is_active']) ? url('projects/' . $r['slug']) : null,
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
