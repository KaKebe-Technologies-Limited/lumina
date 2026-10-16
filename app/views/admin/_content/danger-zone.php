<?php
/**
 * Delete card shown at the bottom of an edit screen. OWNER: B4.
 * Vars: $c (controller config), $row, $warning (optional extra sentence).
 */
if (!can('content.delete') || empty($row['id'])) {
    return;
}
$title = (string) ($row[$c['title_col'] ?? 'title'] ?? '');
?>
<section class="card card--danger" aria-labelledby="danger-title">
    <div class="card__header"><h2 class="card__title" id="danger-title">Delete this <?= e($c['singular']) ?></h2></div>
    <div class="card__body">
        <p class="text-muted">Deleting removes it from the website for good<?= !empty($warning) ? '. ' . e($warning) : '' ?>. Uploaded pictures that are not used anywhere else are deleted as well. This cannot be undone.</p>
        <form method="post" action="<?= e(url($c['path'] . '/' . (int) $row['id'] . '/delete')) ?>" data-confirm="<?= e('Delete "' . $title . '"? This cannot be undone.') ?>">
            <?= csrf_field() ?>
            <button class="btn btn--danger" type="submit"><?= icon('trash', 'icon--sm') ?> Delete <?= e($c['singular']) ?></button>
        </form>
    </div>
</section>
