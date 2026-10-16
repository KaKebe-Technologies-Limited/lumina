<?php
/** Admin > Documents > Edit. OWNER: B8. Vars: $doc (row + file_ok), $categories, $maxBytes. */
$id = (int) $doc['id'];
$actions = [];
if ((int) $doc['is_active'] === 1 && (int) $doc['is_public'] === 1) {
    $actions[] = ['label' => 'View reports page', 'url' => url('reports'), 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'];
}
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Edit document', 'subtitle' => $doc['title'], 'actions' => $actions]) ?>
    <?= partial('admin/documents/_form', ['doc' => $doc, 'categories' => $categories, 'maxBytes' => $maxBytes, 'action' => url('admin/documents/' . $id), 'isEdit' => true]) ?>
    <?php if (can('content.delete')): ?>
        <form id="delete-form" method="post" action="<?= e(url('admin/documents/' . $id . '/delete')) ?>" data-confirm="<?= e('Delete "' . $doc['title'] . '" and its file? This cannot be undone.') ?>">
            <?= csrf_field() ?>
        </form>
    <?php endif; ?>
</div>
