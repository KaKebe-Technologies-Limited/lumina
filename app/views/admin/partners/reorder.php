<?php
/**
 * Admin > Partners > Reorder (drag handle or arrow buttons; behaviour = assets/js/admin.js [data-reorder]). OWNER: B8.
 * Vars: $partners (rows in current order), $types. The submitted ids[] order (DOM order) becomes sort_order 10, 20, ...
 */
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Reorder partners',
        'subtitle' => 'Drag a partner by the handle, or use the arrows, then save.',
        'actions'  => [['label' => 'Back to the list', 'url' => url('admin/partners'), 'class' => 'btn--outline', 'icon' => 'arrow-left']],
    ]) ?>

    <?php if ($partners === []): ?>
        <?= partial('partials/empty-state', [
            'title'   => 'Nothing to reorder yet',
            'text'    => 'Add partners first, then come back to arrange them.',
            'icon'    => 'building',
            'actions' => [['label' => 'Add a partner', 'url' => url('admin/partners/create')]],
        ]) ?>
    <?php else: ?>
        <form method="post" action="<?= e(url('admin/partners/reorder')) ?>" data-unsaved-guard>
            <?= csrf_field() ?>
            <section class="card" aria-label="Partners in display order">
                <div class="card__body">
                    <div class="reorder-list" data-reorder>
                        <?php foreach ($partners as $p):
                            $logo = img_url((string) ($p['logo'] ?? ''));
                        ?>
                            <div class="reorder-item" data-reorder-item>
                                <input type="hidden" name="ids[]" value="<?= (int) $p['id'] ?>">
                                <span class="reorder-item__handle" data-drag-handle title="Drag to reorder"><?= icon('grip') ?><span class="visually-hidden">Drag handle</span></span>
                                <?php if ($logo !== ''): ?>
                                    <img class="org-logo" src="<?= e($logo) ?>" alt="" width="64" height="44" loading="lazy">
                                <?php else: ?>
                                    <span class="org-logo org-logo--empty" aria-hidden="true"><?= icon('building') ?></span>
                                <?php endif; ?>
                                <span class="reorder-item__body">
                                    <strong><?= e($p['name']) ?></strong>
                                    <span class="cell-sub"><?= e($types[$p['partner_type']] ?? label_from($p['partner_type'])) ?></span>
                                </span>
                                <?php if ((int) $p['is_active'] !== 1): ?><span class="badge badge--neutral">Hidden</span><?php endif; ?>
                                <span class="reorder-item__tools">
                                    <button class="btn btn--ghost btn--sm btn--icon" type="button" data-move-up aria-label="Move <?= e($p['name']) ?> up"><?= icon('arrow-up', 'icon--sm') ?></button>
                                    <button class="btn btn--ghost btn--sm btn--icon" type="button" data-move-down aria-label="Move <?= e($p['name']) ?> down"><?= icon('arrow-down', 'icon--sm') ?></button>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <div class="form-actions">
                <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save order</button>
                <a class="btn btn--ghost" href="<?= e(url('admin/partners')) ?>">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>
