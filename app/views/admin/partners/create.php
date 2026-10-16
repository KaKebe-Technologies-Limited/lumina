<?php
/** Admin > Partners > Add. OWNER: B8. Vars: $partner (defaults), $types. */
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Add partner', 'subtitle' => 'New partners appear on the public Partners page once they are active.']) ?>
    <?= partial('admin/partners/_form', ['partner' => $partner, 'types' => $types, 'action' => url('admin/partners'), 'isEdit' => false]) ?>
</div>
