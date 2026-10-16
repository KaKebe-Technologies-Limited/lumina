<?php
/**
 * Programs list. OWNER: B4. Vars from Content\CrudController::index(): $c $rows $pagination $q $filters $tabs $sort $dir $defaultSort $canReorder
 * $perPage $returnQs $listUrl $hasFilters $total.
 */
$th = static fn(string $label, string $key, string $class = ''): string => partial('admin/_content/th-sort', [
    'label' => $label, 'key' => $key, 'sort' => $sort, 'dir' => $dir, 'defaultSort' => $defaultSort, 'listUrl' => $listUrl, 'class' => $class,
]);
?>
<?php $this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', [
    'title' => 'Programs',
    'subtitle' => 'The programs shown on the Programs page and the home page. Switch one off to hide it without deleting it.',
    'actions' => [['label' => 'New program', 'url' => url('admin/programs/create'), 'icon' => 'plus']],
]) ?>

<?= partial('admin/_content/tabs', ['tabs' => $tabs, 'listUrl' => $listUrl]) ?>
<?= partial('admin/_content/filters', [
    'path' => $c['path'], 'q' => $q, 'placeholder' => 'Search by title, tagline or text', 'perPage' => $perPage, 'hasFilters' => $hasFilters, 'total' => $total,
    'keep' => $keep,
]) ?>

<?php if ($rows === []): ?>
    <?= partial('admin/_content/empty', ['c' => $c, 'hasFilters' => $hasFilters, 'createUrl' => url('admin/programs/create'), 'icon' => 'layers']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--stack">
            <caption class="visually-hidden">Programs</caption>
            <thead>
            <tr>
                <th scope="col" class="col-narrow"><span class="visually-hidden">Picture</span></th>
                <?= $th('Program', 'title') ?>
                <?= $th('Order', 'order', 'col-num') ?>
                <th scope="col">Status</th>
                <?= $th('Updated', 'updated') ?>
                <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="col-narrow" data-label="Picture">
                        <?php if (!empty($r['image'])): ?><img class="cell-thumb" src="<?= e(img_url($r['image'])) ?>" alt="" loading="lazy"><?php else: ?><span class="cell-thumb cell-thumb--empty" aria-hidden="true"><?= icon('image', 'icon--sm') ?></span><?php endif; ?>
                    </td>
                    <td class="cell-main" data-label="Program">
                        <span class="cell-title"><a href="<?= e(url('admin/programs/' . (int) $r['id'] . '/edit')) ?>"><?= e($r['title']) ?></a>
                            <?php if (!empty($r['is_featured'])): ?><span class="badge badge--gold">Featured</span><?php endif; ?></span>
                        <span class="cell-sub"><?= e($r['tagline'] ?: '/programs/' . $r['slug']) ?></span>
                    </td>
                    <td class="col-num" data-label="Order"><?= (int) $r['sort_order'] ?></td>
                    <td data-label="Status"><?= partial('admin/_content/active-toggle', ['c' => $c, 'row' => $r, 'returnQs' => $returnQs]) ?></td>
                    <td data-label="Updated"><span class="nowrap"><?= e(format_date($r['updated_at'], 'j M Y')) ?></span></td>
                    <td class="col-actions">
                        <?= partial('admin/_content/row-actions', [
                            'c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'canReorder' => $canReorder,
                            'publicUrl' => !empty($r['is_active']) ? url('programs/' . $r['slug']) : null,
                            'deleteWarning' => 'Projects linked to it are kept but unlinked. ',
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
