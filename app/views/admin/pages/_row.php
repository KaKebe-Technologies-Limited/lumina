<?php
/**
 * One row of a repeater (also rendered with $index = '__INDEX__' inside the <template>). OWNER: B3.
 * Vars: $field (repeater definition), $name (prefix blocks[section.field]), $index (int | '__INDEX__'), $row (values).
 */
$subs = (array) $field['fields'];
$titleKey = (string) ($field['item_label'] ?? '');
if ($titleKey === '' || !isset($subs[$titleKey])) {
    $titleKey = '';
    foreach ($subs as $sk => $sf) {
        if (in_array($sf['type'], ['text', 'textarea'], true)) {
            $titleKey = (string) $sk;
            break;
        }
    }
}
$rowTitle = $titleKey !== '' ? trim((string) ($row[$titleKey] ?? '')) : '';
$isTemplate = $index === '__INDEX__';
?>
<div class="repeater__row pe-row" data-repeater-row>
    <div class="repeater__row-head">
        <span class="repeater__handle" data-repeater-handle title="Drag to change the order" aria-hidden="true"><?= icon('grip', 'icon--sm') ?></span>
        <span class="repeater__row-title"><span class="pe-row__num" data-repeater-index><?= $isTemplate ? '#' : (int) $index + 1 ?></span><span class="pe-row__title" data-pe-row-title data-pe-row-fallback="New item"><?= e($rowTitle !== '' ? $rowTitle : 'New item') ?></span></span>
        <span class="repeater__tools">
            <button class="repeater__tool" type="button" data-repeater-up aria-label="Move this item up"><?= icon('arrow-up', 'icon--sm') ?></button>
            <button class="repeater__tool" type="button" data-repeater-down aria-label="Move this item down"><?= icon('arrow-down', 'icon--sm') ?></button>
            <button class="repeater__tool repeater__tool--danger" type="button" data-repeater-remove aria-label="Remove this item"><?= icon('trash', 'icon--sm') ?></button>
        </span>
    </div>
    <div class="repeater__fields">
        <?php foreach ($subs as $sk => $sf):
            $sname = $name . '[' . $index . '][' . $sk . ']';
            $sval = $row[$sk] ?? ($isTemplate || $row === [] ? ($sf['default'] ?? '') : '');
            if ($sf['type'] === 'boolean') {
                $sval = in_array($sval, [true, 1, '1'], true);
            }
            $salt = (string) ($row[$sk . '_alt'] ?? ($row === [] ? ($sf['alt'] ?? '') : ''));
            $err = error($sname);
            $full = in_array($sf['type'], ['textarea', 'richtext', 'image'], true);
            ?>
            <div class="field<?= $full ? ' field--full' : '' ?><?= $err ? ' field--error' : '' ?>">
                <?php if ($sf['type'] !== 'boolean'): ?><label class="field__label" for="<?= e($sname) ?>"><?= e($sf['label']) ?></label><?php endif; ?>
                <?= partial('admin/pages/_control', [
                    'field' => $sf, 'name' => $sname, 'id' => $sname, 'value' => $sval, 'alt' => $salt, 'altName' => $name . '[' . $index . '][' . $sk . '_alt]',
                    'rowSource' => $sk === $titleKey, 'invalid' => (bool) $err, 'compact' => true,
                ]) ?>
                <?php if (($sf['help'] ?? '') !== ''): ?><div class="field__help"><?= e($sf['help']) ?></div><?php endif; ?>
                <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
