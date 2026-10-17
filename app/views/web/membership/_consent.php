<?php
/**
 * One tick box of the consent group (with an optional link inside the label).
 * Vars: $name* (input name), $labelHtml* (SAFE html: the caller has escaped the text and built the links), $required, $condRequired (switched on
 * by membership.js, like the guardian block).
 */
$id = 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $name);
$err = error((string) $name);
$oldValue = old((string) $name, '');
$checked = is_scalar($oldValue) && in_array(strtolower((string) $oldValue), ['1', 'on', 'yes', 'true'], true);
?>
<div class="field field--full mb-consent<?= $err !== null ? ' field--error' : '' ?>">
    <label class="check" for="<?= e($id) ?>">
        <input type="checkbox" id="<?= e($id) ?>" name="<?= e($name) ?>" value="1"<?= !empty($required) ? ' required' : '' ?><?= $checked ? ' checked' : '' ?><?= $err !== null ? ' aria-invalid="true" aria-describedby="' . e($id) . '-error"' : '' ?>>
        <span><?= $labelHtml ?><?php if (!empty($required) || !empty($condRequired)): ?> <span class="field__req" aria-hidden="true" data-req-mark<?= !empty($required) ? '' : ' hidden' ?>>*</span><?php endif; ?></span>
    </label>
    <?php if ($err !== null): ?><div class="field__error" id="<?= e($id) ?>-error"><?= e($err) ?></div><?php endif; ?>
</div>
