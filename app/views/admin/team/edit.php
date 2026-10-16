<?php
/** Admin > Team members > Edit. OWNER: B8. Vars: $member (row), $groups. */
$id = (int) $member['id'];
$actions = [];
if ((int) $member['is_active'] === 1) {
    $actions[] = ['label' => 'View team page', 'url' => url('team'), 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'];
}
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Edit team member', 'subtitle' => $member['name'], 'actions' => $actions]) ?>
    <?= partial('admin/team/_form', ['member' => $member, 'groups' => $groups, 'action' => url('admin/team/' . $id), 'isEdit' => true]) ?>
    <?php if (can('content.delete')): ?>
        <form id="delete-form" method="post" action="<?= e(url('admin/team/' . $id . '/delete')) ?>" data-confirm="<?= e('Delete ' . $member['name'] . ' and their photo? This cannot be undone.') ?>">
            <?= csrf_field() ?>
        </form>
    <?php endif; ?>
</div>
