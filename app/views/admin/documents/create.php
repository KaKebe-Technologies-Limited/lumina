<?php
/** Admin > Documents > Upload. OWNER: B8. Vars: $doc (defaults), $categories, $maxBytes. */
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', ['title' => 'Upload document', 'subtitle' => 'Add a report or download to the public Reports & Downloads page.']) ?>
    <?= partial('admin/documents/_form', ['doc' => $doc, 'categories' => $categories, 'maxBytes' => $maxBytes, 'action' => url('admin/documents'), 'isEdit' => false]) ?>
</div>
