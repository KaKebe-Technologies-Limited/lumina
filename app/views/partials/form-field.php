<?php
/**
 * One form field with label, help and validation error (works in BOTH bundles).
 * Vars: $name (required), $label (required), $type ('text' default | email | tel | url | number | date | datetime-local | password |
 *       textarea | select | checkbox | hidden), $value (default: old($name)), $required, $help, $placeholder, $options (select: value => label),
 *       $rows (textarea), $autocomplete, $maxlength, $class (extra field wrapper class), $attrs (extra input attributes array),
 *       $id (default f-<name>), $empty (select: label of the empty first option), $min/$max/$step.
 *   <?= partial('partials/form-field', ['name' => 'email', 'label' => content('contact', 'form.email_label'), 'type' => 'email', 'required' => true]) ?>
 */
$type = $type ?? 'text';
$id = $id ?? 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $name);
$value = array_key_exists('value', get_defined_vars()) ? $value : old((string) $name, '');
$err = error((string) $name);
$describedBy = trim(($err ? $id . '-error ' : '') . (!empty($help) ? $id . '-help' : ''));
$common = [
    'id'               => $id,
    'name'             => $name,
    'required'         => !empty($required) ? true : null,
    'aria-invalid'     => $err ? 'true' : null,
    'aria-describedby' => $describedBy !== '' ? $describedBy : null,
    'autocomplete'     => $autocomplete ?? null,
    'maxlength'        => $maxlength ?? null,
    'placeholder'      => $placeholder ?? null,
];
if ($type === 'hidden') {
    echo '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
    return;
}
?>
<div class="field<?= $err ? ' field--error' : '' ?><?= !empty($class) ? ' ' . e($class) : '' ?>">
    <?php if ($type === 'checkbox'): ?>
        <label class="check" for="<?= e($id) ?>">
            <input type="checkbox" value="1"<?= attrs($common + (array) ($attrs ?? [])) ?><?= $value ? ' checked' : '' ?>>
            <span><?= e($label) ?><?= !empty($required) ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></span>
        </label>
    <?php else: ?>
        <label class="field__label" for="<?= e($id) ?>"><?= e($label) ?><?= !empty($required) ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></label>
        <?php if ($type === 'textarea'): ?>
            <textarea class="textarea" rows="<?= (int) ($rows ?? 5) ?>"<?= attrs($common + (array) ($attrs ?? [])) ?>><?= e($value) ?></textarea>
        <?php elseif ($type === 'select'): ?>
            <select class="select"<?= attrs($common + (array) ($attrs ?? [])) ?>>
                <?php if (isset($empty)): ?><option value=""><?= e($empty) ?></option><?php endif; ?>
                <?php foreach ((array) ($options ?? []) as $ov => $ol): ?>
                    <option value="<?= e($ov) ?>"<?= (string) $ov === (string) $value ? ' selected' : '' ?>><?= e($ol) ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input class="input" type="<?= e($type) ?>"<?= attrs($common + ['min' => $min ?? null, 'max' => $max ?? null, 'step' => $step ?? null] + (array) ($attrs ?? [])) ?><?= $type === 'password' ? '' : ' value="' . e($value) . '"' ?>>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($help)): ?><div class="field__help" id="<?= e($id) ?>-help"><?= e($help) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="field__error" id="<?= e($id) ?>-error" role="alert"><?= e($err) ?></div><?php endif; ?>
</div>
