<?php
/** Admin > Impact stories > Add. OWNER: B8. Vars: $story (defaults). */
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Add story', 'subtitle' => 'Save it as a draft first; it can only be published once consent is confirmed.']) ?>
    <?= partial('admin/stories/_form', ['story' => $story, 'action' => url('admin/stories'), 'isEdit' => false]) ?>
</div>
