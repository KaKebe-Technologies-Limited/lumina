<?php
/**
 * Per-row action buttons: move up/down (when the list is in manual order), edit, view on site, delete. OWNER: B4.
 * Vars: $c (controller config), $row, $returnQs, $canReorder (bool), $publicUrl (absolute href or null), $viewLabel ('View on website'),
 *       $extra (optional list of ['label','url','icon'] extra links), $deleteWarning (optional extra sentence for the confirm box).
 */
$id = (int) $row['id'];
$title = (string) ($row[$c['title_col'] ?? 'title'] ?? '');
$path = (string) $c['path'];
?>
<div class="table__actions">
    <?php if (!empty($canReorder)): ?>
        <?php foreach (['up' => ['arrow-up', 'Move up'], 'down' => ['arrow-down', 'Move down']] as $dirKey => [$ic, $lab]): ?>
            <form method="post" action="<?= e(url($path . '/' . $id . '/move')) ?>" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="direction" value="<?= e($dirKey) ?>">
                <input type="hidden" name="return" value="<?= e($returnQs ?? '') ?>">
                <button class="btn btn--ghost btn--icon" type="submit" title="<?= e($lab) ?>" aria-label="<?= e($lab . ': ' . $title) ?>"><?= icon($ic, 'icon--sm') ?></button>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
    <a class="btn btn--ghost btn--icon" href="<?= e(url($path . '/' . $id . '/edit')) ?>" title="Edit" aria-label="<?= e('Edit: ' . $title) ?>"><?= icon('edit', 'icon--sm') ?></a>
    <?php foreach ((array) ($extra ?? []) as $x): ?>
        <a class="btn btn--ghost btn--icon" href="<?= e($x['url']) ?>" title="<?= e($x['label']) ?>" aria-label="<?= e($x['label'] . ': ' . $title) ?>"<?= !empty($x['blank']) ? ' target="_blank" rel="noopener"' : '' ?>><?= icon($x['icon'] ?? 'eye', 'icon--sm') ?></a>
    <?php endforeach; ?>
    <?php if (!empty($publicUrl)): ?>
        <a class="btn btn--ghost btn--icon" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener" title="<?= e($viewLabel ?? 'View on website') ?>" aria-label="<?= e(($viewLabel ?? 'View on website') . ': ' . $title) ?>"><?= icon('external-link', 'icon--sm') ?></a>
    <?php endif; ?>
    <?php if (can('content.delete')): ?>
        <form method="post" action="<?= e(url($path . '/' . $id . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete "' . $title . '"? ' . ($deleteWarning ?? '') . 'This cannot be undone.') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="return" value="<?= e($returnQs ?? '') ?>">
            <button class="btn btn--ghost btn--icon btn-danger-hover" type="submit" title="Delete" aria-label="<?= e('Delete: ' . $title) ?>"><?= icon('trash', 'icon--sm') ?></button>
        </form>
    <?php endif; ?>
</div>
