<?php
/**
 * One field of the membership registration form: label, input, hint, counter, error. Visible copy comes from the caller (registry).
 * Vars: $name* (input name = column name), $label*, $type ('text' | email | tel | date | textarea | select), $value (default: old input),
 *       $required, $condRequired (a conditional field: the required marker is printed hidden and switched on by membership.js),
 *       $help, $options (select: key => label), $optionAttrs (select: key => [attr => value]), $empty (label of the empty first option),
 *       $rows, $maxlength, $minlength, $autocomplete, $inputmode, $class (extra wrapper classes), $attrs (extra input attributes),
 *       $counter (show "n / max" under a textarea), $after (SAFE html printed under the input, e.g. the age line), $id (default f-<name>),
 *       $describedby (extra ids for aria-describedby), $summaryLabel (the name the error summary uses when the label alone is ambiguous).
 * Everything printed is escaped here; $after must be escaped by the caller.
 */
$id = (string) ($id ?? 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $name));
$type = (string) ($type ?? 'text');
$value = array_key_exists('value', get_defined_vars()) ? $value : old((string) $name, '');
$value = is_scalar($value) ? (string) $value : '';
$err = error((string) $name);
$help = trim((string) ($help ?? ''));
$ids = [];
if ($err !== null) {
    $ids[] = $id . '-error';
}
if ($help !== '') {
    $ids[] = $id . '-help';
}
foreach ((array) ($describedby ?? []) as $extra) {
    $ids[] = (string) $extra;
}
$input = [
    'id'               => $id,
    'name'             => $name,
    'required'         => !empty($required) ? true : null,
    'aria-invalid'     => $err !== null ? 'true' : null,
    'aria-describedby' => $ids ? implode(' ', $ids) : null,
    'autocomplete'     => $autocomplete ?? null,
    'inputmode'        => $inputmode ?? null,
    'maxlength'        => $maxlength ?? null,
    'minlength'        => $minlength ?? null,
] + (array) ($attrs ?? []);
$mark = !empty($required) || !empty($condRequired);
?>
<div class="field<?= $err !== null ? ' field--error' : '' ?><?= !empty($class) ? ' ' . e($class) : '' ?>"<?= !empty($summaryLabel) ? ' data-summary-label="' . e($summaryLabel) . '"' : '' ?>>
    <label class="field__label" for="<?= e($id) ?>"><?= e($label) ?><?php if ($mark): ?> <span class="field__req" aria-hidden="true" data-req-mark<?= !empty($required) ? '' : ' hidden' ?>>*</span><?php endif; ?></label>
    <?php if ($type === 'textarea'): ?>
        <textarea class="textarea" rows="<?= (int) ($rows ?? 5) ?>"<?= attrs($input) ?>><?= e($value) ?></textarea>
    <?php elseif ($type === 'select'): ?>
        <select class="select"<?= attrs($input) ?>>
            <?php if (isset($empty)): ?><option value=""><?= e($empty) ?></option><?php endif; ?>
            <?php foreach ((array) ($options ?? []) as $ov => $ol): ?>
                <option value="<?= e($ov) ?>"<?= (string) $ov === $value ? ' selected' : '' ?><?= attrs((array) (($optionAttrs ?? [])[$ov] ?? [])) ?>><?= e($ol) ?></option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <input class="input" type="<?= e($type) ?>"<?= attrs($input) ?> value="<?= e($value) ?>">
    <?php endif; ?>
    <?php if ($help !== '' || !empty($counter)): ?>
        <div class="field__meta">
            <?php if ($help !== ''): ?><div class="field__help" id="<?= e($id) ?>-help"><?= e($help) ?></div><?php endif; ?>
            <?php if (!empty($counter) && !empty($maxlength)): ?><div class="mb-count" data-count-for="<?= e($id) ?>" data-max="<?= (int) $maxlength ?>" aria-hidden="true"><?= mb_strlen($value) ?> / <?= (int) $maxlength ?></div><?php endif; ?>
        </div>
    <?php endif; ?>
    <?= (string) ($after ?? '') ?>
    <?php if ($err !== null): ?><div class="field__error" id="<?= e($id) ?>-error"><?= e($err) ?></div><?php endif; ?>
</div>
