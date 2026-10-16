<?php
/**
 * One-button POST form used inside table rows (toggle, move, delete, status) - OWNER: B8.
 * CSRF + the page we came from ("_back") are added automatically; data-confirm triggers admin.js' confirm dialog.
 * Vars: $action (url), $label (accessible name + tooltip), $icon (icon name), $text (optional visible text), $class (button
 *       classes, default 'btn--ghost btn--sm'), $fields (extra hidden name => value), $confirm (message), $disabled (bool).
 */
$req = request();
$class = $class ?? 'btn--ghost btn--sm';
?>
<form class="inline-form" method="post" action="<?= e($action) ?>"<?= !empty($confirm) ? ' data-confirm="' . e($confirm) . '"' : '' ?>>
    <?= csrf_field() ?>
    <input type="hidden" name="_back" value="<?= e($req ? $req->uri() : '') ?>">
    <?php foreach (($fields ?? []) as $fn => $fv): ?><input type="hidden" name="<?= e($fn) ?>" value="<?= e($fv) ?>"><?php endforeach; ?>
    <button class="btn <?= e($class) ?>" type="submit" title="<?= e($label) ?>"<?= !empty($disabled) ? ' disabled' : '' ?>>
        <?php if (!empty($icon)): ?><?= icon($icon, 'icon--sm') ?><?php endif; ?>
        <span<?= !empty($text) ? '' : ' class="visually-hidden"' ?>><?= e(!empty($text) ? $text : $label) ?></span>
    </button>
</form>
