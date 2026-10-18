<?php
/**
 * One field of the "Request a meeting" form (partials/meeting-request-form). Like partials/form-field, but the error and the old value are PASSED IN
 * (the form keeps its own flash, so it never shares error() / old() with the main contact form that has fields called name, email, phone and message).
 * Vars: $name* (input name, prefixed mr_), $id*, $label*, $type ('text' | email | tel | date | textarea | select | checkbox), $value, $error (string|null),
 *       $required, $help, $options (select: value => label), $empty (select: label of the empty first option), $rows, $maxlength, $autocomplete,
 *       $min / $max (date), $inputmode, $class (extra wrapper class). Everything printed is escaped here.
 */
$type = (string) ($type ?? 'text');
$value = is_scalar($value ?? null) ? (string) $value : '';
$error = isset($error) && $error !== '' ? (string) $error : null;
$help = trim((string) ($help ?? ''));
$ids = array_filter([$error !== null ? $id . '-error' : '', $help !== '' ? $id . '-help' : '']);
$input = [
    'id'               => $id,
    'name'             => $name,
    'required'         => !empty($required) ? true : null,
    'aria-invalid'     => $error !== null ? 'true' : null,
    'aria-describedby' => $ids ? implode(' ', $ids) : null,
    'autocomplete'     => $autocomplete ?? null,
    'inputmode'        => $inputmode ?? null,
    'maxlength'        => $maxlength ?? null,
];
?>
<div class="field<?= $error !== null ? ' field--error' : '' ?><?= !empty($class) ? ' ' . e($class) : '' ?>">
    <?php if ($type === 'checkbox'): ?>
        <label class="check" for="<?= e($id) ?>">
            <input type="checkbox" value="1"<?= attrs($input) ?><?= $value === '1' || $value === 'on' ? ' checked' : '' ?>>
            <span><?= e($label) ?><?= !empty($required) ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></span>
        </label>
    <?php else: ?>
        <label class="field__label" for="<?= e($id) ?>"><?= e($label) ?><?= !empty($required) ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></label>
        <?php if ($type === 'textarea'): ?>
            <textarea class="textarea" rows="<?= (int) ($rows ?? 5) ?>"<?= attrs($input) ?>><?= e($value) ?></textarea>
        <?php elseif ($type === 'select'): ?>
            <select class="select"<?= attrs($input) ?>>
                <?php if (isset($empty)): ?><option value=""><?= e($empty) ?></option><?php endif; ?>
                <?php foreach ((array) ($options ?? []) as $ov => $ol): ?>
                    <option value="<?= e((string) $ov) ?>"<?= (string) $ov === $value ? ' selected' : '' ?>><?= e((string) $ol) ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input class="input" type="<?= e($type) ?>"<?= attrs($input + ['min' => $min ?? null, 'max' => $max ?? null]) ?> value="<?= e($value) ?>">
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($help !== ''): ?><div class="field__help" id="<?= e($id) ?>-help"><?= e($help) ?></div><?php endif; ?>
    <?php if ($error !== null): ?><div class="field__error" id="<?= e($id) ?>-error" role="alert"><?= e($error) ?></div><?php endif; ?>
</div>
