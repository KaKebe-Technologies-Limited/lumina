<?php
/** Admin > Team members > Add. OWNER: B8. Vars: $member (defaults), $groups. */
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Add team member', 'subtitle' => 'New people appear on the public Our Team page once they are active.']) ?>
    <?= partial('admin/team/_form', ['member' => $member, 'groups' => $groups, 'action' => url('admin/team'), 'isEdit' => false]) ?>
</div>
