<?php
/** Flash messages (one request). Used by both layouts. No variables. */
$messages = flash();
if ($messages === []) {
    return;
}
$icons = ['success' => 'check-circle', 'error' => 'alert-circle', 'warning' => 'alert-triangle', 'info' => 'info'];
?>
<div class="flash-stack" role="status" aria-live="polite">
    <?php foreach ($messages as $m): $type = in_array($m['type'], ['success', 'error', 'warning', 'info'], true) ? $m['type'] : 'info'; ?>
        <div class="alert alert--<?= e($type) ?>"<?= $type === 'error' ? ' role="alert"' : '' ?>>
            <?= icon($icons[$type]) ?>
            <div class="alert__body"><?= e($m['message']) ?></div>
            <button class="alert__close" type="button" data-dismiss aria-label="Close"><?= icon('x', 'icon--sm') ?></button>
        </div>
    <?php endforeach; ?>
</div>
