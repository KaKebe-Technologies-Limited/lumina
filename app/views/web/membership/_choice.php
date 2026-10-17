<?php
/**
 * A group of radio buttons (one answer) or check boxes (several answers) of the membership form, as a fieldset with a legend.
 * Vars: $name* (radio: column name; checkbox: the array name without brackets, e.g. interests), $label*, $options* (key => label),
 *       $type ('radio' default | 'checkbox'), $selected (key | list of keys; default: old input), $required (radio), $help,
 *       $layout ('chips' default: pills | 'cards': a grid of bordered rows), $class (extra wrapper classes).
 * The FIRST input gets the id f-<name> so the error summary can link to it. Output is escaped here.
 */
$type = ($type ?? 'radio') === 'checkbox' ? 'checkbox' : 'radio';
$base = 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $name);
$selected = array_key_exists('selected', get_defined_vars()) ? $selected : old((string) $name, $type === 'checkbox' ? [] : '');
$chosen = array_map('strval', is_array($selected) ? array_filter($selected, 'is_scalar') : [(string) $selected]);
$err = error((string) $name);
$help = trim((string) ($help ?? ''));
$ids = ($err !== null ? $base . '-error ' : '') . ($help !== '' ? $base . '-help' : '');
$layout = ($layout ?? 'chips') === 'cards' ? 'cards' : 'chips';
$i = 0;
?>
<div class="field field--full mb-choice<?= $err !== null ? ' field--error' : '' ?><?= !empty($class) ? ' ' . e($class) : '' ?>">
    <fieldset class="mb-fieldset"<?= trim($ids) !== '' ? ' aria-describedby="' . e(trim($ids)) . '"' : '' ?>>
        <legend class="field__label"><?= e($label) ?><?php if (!empty($required)): ?> <span class="field__req" aria-hidden="true">*</span><?php endif; ?></legend>
        <div class="mb-opts mb-opts--<?= e($layout) ?>">
            <?php foreach ((array) $options as $key => $text): $key = (string) $key; $oid = $i === 0 ? $base : $base . '-' . ($i + 1); $i++; ?>
                <label class="mb-opt" for="<?= e($oid) ?>">
                    <input type="<?= $type ?>" id="<?= e($oid) ?>" name="<?= e($name) ?><?= $type === 'checkbox' ? '[]' : '' ?>" value="<?= e($key) ?>"<?= in_array($key, $chosen, true) ? ' checked' : '' ?><?= !empty($required) && $i === 1 ? ' required' : '' ?><?= $err !== null && $i === 1 ? ' aria-invalid="true"' : '' ?>>
                    <span><?= e($text) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>
    <?php if ($help !== ''): ?><div class="field__help" id="<?= e($base) ?>-help"><?= e($help) ?></div><?php endif; ?>
    <?php if ($err !== null): ?><div class="field__error" id="<?= e($base) ?>-error"><?= e($err) ?></div><?php endif; ?>
</div>
