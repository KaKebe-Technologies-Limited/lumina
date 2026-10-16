<?php
/**
 * On/off switch that always posts a value (hidden 0 + checkbox 1), so old() input restores correctly after a validation error. OWNER: B4.
 * Vars: $name, $label, $checked (bool), $help (optional), $id.
 */
$id = $id ?? 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $name);
?>
<div class="field">
    <input type="hidden" name="<?= e($name) ?>" value="0">
    <label class="switch" for="<?= e($id) ?>">
        <input type="checkbox" id="<?= e($id) ?>" name="<?= e($name) ?>" value="1"<?= !empty($checked) ? ' checked' : '' ?><?= !empty($help) ? ' aria-describedby="' . e($id) . '-help"' : '' ?>>
        <span class="switch__track" aria-hidden="true"></span>
        <span class="switch__text"><?= e($label) ?></span>
    </label>
    <?php if (!empty($help)): ?><div class="field__help" id="<?= e($id) ?>-help"><?= e($help) ?></div><?php endif; ?>
</div>
