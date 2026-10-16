<?php
/** Admin > Events > Add. OWNER: B8. Vars: $event (defaults). */
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Add event', 'subtitle' => 'New events appear on the public Events page once they are active.']) ?>
    <?= partial('admin/events/_form', ['event' => $event, 'action' => url('admin/events'), 'isEdit' => false]) ?>
</div>
