<?php
/** Admin > Impact stories > Edit. OWNER: B8. Vars: $story (row). */
$id = (int) $story['id'];
$actions = [];
if ((int) $story['is_active'] === 1 && (int) $story['consent_confirmed'] === 1) {
    $actions[] = ['label' => 'View story', 'url' => url('stories/' . $story['slug']), 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'];
}
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Edit story', 'subtitle' => $story['title'], 'actions' => $actions]) ?>
    <?= partial('admin/stories/_form', ['story' => $story, 'action' => url('admin/stories/' . $id), 'isEdit' => true]) ?>
    <?php if (can('content.delete')): ?>
        <form id="delete-form" method="post" action="<?= e(url('admin/stories/' . $id . '/delete')) ?>" data-confirm="<?= e('Delete "' . $story['title'] . '" and its image? This cannot be undone.') ?>">
            <?= csrf_field() ?>
        </form>
    <?php endif; ?>
</div>
