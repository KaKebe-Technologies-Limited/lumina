<?php
/** Admin > Partners > Edit. OWNER: B8. Vars: $partner (row), $types. */
$id = (int) $partner['id'];
$actions = [];
if ((int) $partner['is_active'] === 1) {
    $actions[] = ['label' => 'View partners page', 'url' => url('partners'), 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'];
}
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Edit partner', 'subtitle' => $partner['name'], 'actions' => $actions]) ?>
    <?= partial('admin/partners/_form', ['partner' => $partner, 'types' => $types, 'action' => url('admin/partners/' . $id), 'isEdit' => true]) ?>
    <?php if (can('content.delete')): ?>
        <form id="delete-form" method="post" action="<?= e(url('admin/partners/' . $id . '/delete')) ?>" data-confirm="<?= e('Delete ' . $partner['name'] . ' and its logo? This cannot be undone.') ?>">
            <?= csrf_field() ?>
        </form>
    <?php endif; ?>
</div>
