<?php
/** "Showing 1-20 of 53" + pagination for the organisation lists (OWNER: B8). Vars: $pager (App\Core\Pagination). */
if (empty($pager) || $pager->total === 0) {
    return;
}
?>
<div class="org-footer">
    <p class="result-count">Showing <?= (int) $pager->from() ?>&ndash;<?= (int) $pager->to() ?> of <?= (int) $pager->total ?></p>
    <?= partial('partials/pagination', ['pagination' => $pager]) ?>
</div>
