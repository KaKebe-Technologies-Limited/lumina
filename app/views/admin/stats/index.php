<?php
/** Impact figures list with re-ordering. OWNER: B3. Vars: $groups, $group, $rows, $hint, $canDelete. */
$fmt = static fn(array $r): string => $r['prefix'] . number_format((int) $r['value_number']) . $r['suffix'];
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', [
    'title'    => 'Impact figures',
    'subtitle' => 'The headline numbers shown on the website, such as "150+ Youth Trained". Only update them with figures you can back up.',
    'actions'  => [['label' => 'Add a figure', 'url' => url('admin/stats/create', ['group' => $group]), 'icon' => 'plus']],
]) ?>

<nav class="tabs" aria-label="Figure groups">
    <?php foreach ($groups as $g): ?>
        <a class="tabs__item<?= $g === $group ? ' is-active' : '' ?>" href="<?= e(url('admin/stats', ['group' => $g])) ?>"<?= $g === $group ? ' aria-current="page"' : '' ?>><?= e($g === 'home' ? 'Home page' : label_from($g)) ?></a>
    <?php endforeach; ?>
</nav>
<p class="pe-section__help"><?= e($hint) ?></p>

<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', [
        'title' => 'No figures in this group yet', 'text' => 'Add the first one, for example a number of young people trained or programmes running.',
        'icon' => 'bar-chart', 'actions' => [['label' => 'Add a figure', 'url' => url('admin/stats/create', ['group' => $group])]],
    ]) ?>
<?php else: ?>
    <form method="post" action="<?= e(url('admin/stats/reorder')) ?>" data-unsaved-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="group" value="<?= e($group) ?>">
        <div class="reorder-list" data-reorder>
            <?php foreach ($rows as $r): ?>
                <div class="reorder-item<?= (int) $r['is_active'] === 1 ? '' : ' is-unpublished' ?>" data-reorder-item>
                    <span class="reorder-item__handle" data-drag-handle aria-hidden="true" title="Drag to change the order"><?= icon('grip') ?></span>
                    <input type="hidden" name="ids[]" value="<?= (int) $r['id'] ?>">
                    <span class="pe-stat-value"><?= e($fmt($r)) ?></span>
                    <div class="reorder-item__body">
                        <strong><?= e($r['label']) ?></strong>
                        <?php if ($r['description']): ?><span class="cell-sub"><?= e($r['description']) ?></span><?php endif; ?>
                    </div>
                    <?= partial('partials/admin/status-badge', ['status' => (int) $r['is_active'] === 1 ? 'active' : 'hidden', 'label' => (int) $r['is_active'] === 1 ? 'Shown' : 'Hidden']) ?>
                    <span class="reorder-item__tools">
                        <button class="repeater__tool" type="button" data-move-up aria-label="Move up"><?= icon('arrow-up', 'icon--sm') ?></button>
                        <button class="repeater__tool" type="button" data-move-down aria-label="Move down"><?= icon('arrow-down', 'icon--sm') ?></button>
                        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/stats/' . (int) $r['id'] . '/edit')) ?>"><?= icon('edit', 'icon--sm') ?> Edit</a>
                        <button class="btn btn--ghost btn--sm" type="submit" formaction="<?= e(url('admin/stats/' . (int) $r['id'] . '/toggle')) ?>"><?= (int) $r['is_active'] === 1 ? 'Hide' : 'Show' ?></button>
                        <?php if ($canDelete): ?><button class="btn btn--ghost btn--sm pe-danger" type="submit" formaction="<?= e(url('admin/stats/' . (int) $r['id'] . '/delete')) ?>" data-confirm="Delete &quot;<?= e($r['label']) ?>&quot; permanently?" aria-label="Delete <?= e($r['label']) ?>"><?= icon('trash', 'icon--sm') ?></button><?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions pe-actions">
            <span class="pe-actions__state" data-pe-state role="status" aria-live="polite">Drag or use the arrows to re-order, then save the order.</span>
            <span class="pe-actions__buttons"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save order</button></span>
        </div>
    </form>
<?php endif; ?>
</div>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script><?php $this->end(); ?>
