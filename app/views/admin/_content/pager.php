<?php
/** "Showing 1-20 of 53" + page links. Vars: $pagination, $path (e.g. 'admin/programs'). OWNER: B4. */
if ($pagination->total === 0) {
    return;
}
?>
<div class="list-footer">
    <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
    <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => $path]) ?>
</div>
