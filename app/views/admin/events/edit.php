<?php
/** Admin > Events > Edit. OWNER: B8. Vars: $event (row), $stats (registration counts). */
$id = (int) $event['id'];
$actions = [];
if ((int) $event['is_active'] === 1) {
    $actions[] = ['label' => 'View event page', 'url' => url('events/' . $event['slug']), 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'];
}
if (can('inbox.manage')) {
    $actions[] = ['label' => 'Registrations', 'url' => url('admin/events/' . $id . '/registrations'), 'class' => 'btn--outline', 'icon' => 'users'];
}
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Edit event', 'subtitle' => $event['title'], 'actions' => $actions]) ?>
    <?= partial('admin/events/_form', ['event' => $event, 'stats' => $stats, 'action' => url('admin/events/' . $id), 'isEdit' => true]) ?>
    <?php if (can('content.delete')): ?>
        <form id="delete-form" method="post" action="<?= e(url('admin/events/' . $id . '/delete')) ?>" data-confirm="<?= e('Delete "' . $event['title'] . '"?' . ($stats['total'] > 0 ? ' Its ' . (int) $stats['total'] . ' registration(s) will be deleted too.' : '') . ' This cannot be undone.') ?>">
            <?= csrf_field() ?>
        </form>
    <?php endif; ?>
</div>
