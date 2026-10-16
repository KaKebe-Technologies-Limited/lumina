<?php
/** Activities list. OWNER: B4. Vars from Content\CrudController::index(). */
$th = static fn(string $label, string $key, string $class = ''): string => partial('admin/_content/th-sort', [
    'label' => $label, 'key' => $key, 'sort' => $sort, 'dir' => $dir, 'defaultSort' => $defaultSort, 'listUrl' => $listUrl, 'class' => $class,
]);
?>
<?php $this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', [
    'title' => 'Activities',
    'subtitle' => 'Things the foundation has done or is doing: workshops, outreach, trainings. They appear on the Activities page in this order (same display order: newest first).',
    'actions' => [['label' => 'New activity', 'url' => url('admin/activities/create'), 'icon' => 'plus']],
]) ?>

<?= partial('admin/_content/tabs', ['tabs' => $tabs, 'listUrl' => $listUrl]) ?>
<?= partial('admin/_content/filters', [
    'path' => $c['path'], 'q' => $q, 'placeholder' => 'Search by title, place or text', 'perPage' => $perPage, 'hasFilters' => $hasFilters, 'total' => $total, 'keep' => $keep,
]) ?>

<?php if ($rows === []): ?>
    <?= partial('admin/_content/empty', ['c' => $c, 'hasFilters' => $hasFilters, 'createUrl' => url('admin/activities/create'), 'icon' => 'activity']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--stack">
            <caption class="visually-hidden">Activities</caption>
            <thead>
            <tr>
                <th scope="col" class="col-narrow"><span class="visually-hidden">Picture</span></th>
                <?= $th('Activity', 'title') ?>
                <?= $th('Date', 'date') ?>
                <th scope="col">Place</th>
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
                    <td class="cell-main" data-label="Activity">
                        <span class="cell-title"><a href="<?= e(url('admin/activities/' . (int) $r['id'] . '/edit')) ?>"><?= e($r['title']) ?></a></span>
                        <span class="cell-sub"><?= e(excerpt((string) $r['summary'], 90) ?: '/activities/' . $r['slug']) ?></span>
                    </td>
                    <td data-label="Date"><span class="nowrap"><?= $r['activity_date'] ? e(format_date($r['activity_date'], 'j M Y')) : '<span class="text-muted">No date</span>' ?></span></td>
                    <td data-label="Place"><?= $r['location'] ? e($r['location']) : '<span class="text-muted">-</span>' ?></td>
                    <td class="col-num" data-label="Order"><?= (int) $r['sort_order'] ?></td>
                    <td data-label="On website"><?= partial('admin/_content/active-toggle', ['c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'onLabel' => 'Shown', 'offLabel' => 'Hidden']) ?></td>
                    <td class="col-actions">
                        <?= partial('admin/_content/row-actions', [
                            'c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'canReorder' => $canReorder,
                            'publicUrl' => !empty($r['is_active']) ? url('activities/' . $r['slug']) : null,
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
