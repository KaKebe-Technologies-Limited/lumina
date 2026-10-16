<?php
/**
 * One top-level field of a registry page: label, input control, help, "edited" marker and "Reset to default". OWNER: B3.
 * Vars: $fk (block key "section.field"), $entry (Content::snapshot entry: field,value,default,overridden[,alt]), $oldBlocks (array|null: input
 * of a failed save, wins over the stored value).
 * Names: blocks[<fk>] (value), blocks[<fk>.alt] (image alt), reset[<fk>] (hidden flag, only for edited fields).
 */
$f = $entry['field'];
$type = $f['type'];
$name = 'blocks[' . $fk . ']';
$id = 'f-' . trim((string) preg_replace('/[^a-z0-9]+/i', '-', $fk), '-');
$hasOld = is_array($oldBlocks ?? null) && array_key_exists($fk, $oldBlocks);
$value = $hasOld ? $oldBlocks[$fk] : $entry['value'];
if ($type === 'boolean' && $hasOld) {
    $value = (string) $value === '1';
}
if ($type === 'repeater' && $hasOld) {
    $value = is_array($value) ? array_values(array_filter($value, 'is_array')) : [];
}
$alt = is_array($oldBlocks ?? null) && array_key_exists($fk . '.alt', $oldBlocks) ? (string) $oldBlocks[$fk . '.alt'] : (string) ($entry['alt'] ?? '');
$edited = !empty($entry['overridden']);
$err = error($name);
$descId = $id . '-help';
$isBool = $type === 'boolean';
$isRepeater = $type === 'repeater';
$wide = in_array($type, ['richtext', 'repeater', 'image'], true);
$defaultText = \App\Controllers\Admin\Support\PageForm::describeDefault($f);
?>
<div class="field pe-field pe-field--<?= e($type) ?><?= $wide ? ' pe-field--wide' : '' ?><?= $edited ? ' is-edited' : '' ?><?= $err ? ' field--error' : '' ?>" data-field data-pe-field="<?= e($fk) ?>">
    <div class="field__label-row pe-field__head">
        <?php if ($isBool): ?>
            <span></span>
        <?php elseif ($type === 'image'): ?>
            <span class="field__label" id="<?= e($id) ?>-label"><?= e($f['label']) ?></span>
        <?php else: ?>
            <label class="field__label" for="<?= e($id) ?>"><?= e($f['label']) ?></label>
        <?php endif; ?>
        <span class="pe-field__tools">
            <?php if ($edited): ?>
                <span class="pe-edited" title="This differs from the original wording">Edited</span>
                <button class="field-reset" type="button" data-pe-reset aria-label="Reset &quot;<?= e($f['label']) ?>&quot; to its original value">Reset to default</button>
                <input type="hidden" name="reset[<?= e($fk) ?>]" value="" data-pe-reset-flag>
            <?php endif; ?>
        </span>
    </div>
    <div class="pe-field__control" data-pe-control>
        <?php if ($isRepeater): ?>
            <?= partial('admin/pages/_repeater', ['field' => $f, 'name' => $name, 'rows' => (array) $value, 'id' => $id]) ?>
        <?php else: ?>
            <?= partial('admin/pages/_control', [
                'field' => $f, 'name' => $name, 'id' => $id, 'value' => $value, 'alt' => $alt, 'altName' => 'blocks[' . $fk . '.alt]',
                'invalid' => (bool) $err, 'describedBy' => trim(($f['help'] !== '' ? $descId : '') . ($err ? ' ' . $id . '-error' : '')),
            ]) ?>
        <?php endif; ?>
    </div>
    <?php if ($edited): ?>
        <div class="pe-field__reset-note" data-pe-reset-note hidden>
            <span>Will return to the original (<?= e($defaultText) ?>) when you save.</span>
            <button class="btn btn--link btn--sm" type="button" data-pe-undo>Undo</button>
        </div>
    <?php endif; ?>
    <?php if ($f['help'] !== ''): ?><div class="field__help" id="<?= e($descId) ?>"><?= e($f['help']) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="field__error" id="<?= e($id) ?>-error" role="alert"><?= e($err) ?></div><?php endif; ?>
</div>
