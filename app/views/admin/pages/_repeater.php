<?php
/**
 * Repeater (list of cards / values / steps / stats...). OWNER: B3. Behaviour: assets/js/admin.js [data-repeater] (add, remove, up/down,
 * drag) + admin-pages.js (row titles, empty state, richtext/image fields in new rows).
 * Vars: $field (repeater definition), $name (blocks[section.field]), $rows (current rows), $id.
 * Inputs: <name>[<i>][<sub>]  and, for image sub-fields, <name>[<i>][<sub>_alt]. Indexes are renumbered by the JS in DOM order.
 */
$max = (int) ($field['max_rows'] ?? 50);
$min = (int) ($field['min_rows'] ?? 0);
$rows = array_values((array) $rows);
$addLabel = (string) ($field['add_label'] ?? 'Add an item');
$count = count($rows);
// A repeater with every row removed posts no inputs at all, which would look like "field not on the form" and silently keep the old
// items. This marker is always posted, so PageForm::collect() knows the (now empty) list really was on the form.
$repeaterKey = preg_match('/^blocks\[(.+)\]$/', (string) $name, $rk) === 1 ? $rk[1] : '';
?>
<div class="repeater pe-repeater" data-repeater data-repeater-prefix="<?= e($name) ?>" data-max-rows="<?= $max ?>" data-min-rows="<?= $min ?>" id="<?= e($id) ?>">
    <?php if ($repeaterKey !== ''): ?><input type="hidden" name="repeaters[]" value="<?= e($repeaterKey) ?>"><?php endif; ?>
    <div class="repeater__list" data-repeater-list><?php foreach ($rows as $i => $row): ?><?= partial('admin/pages/_row', ['field' => $field, 'name' => $name, 'index' => $i, 'row' => (array) $row]) ?><?php endforeach; ?></div>
    <p class="pe-repeater__empty"<?= $count > 0 ? ' hidden' : '' ?> data-pe-empty>Nothing here yet. <?= e($addLabel) ?> with the button below.</p>
    <template data-repeater-template><?= partial('admin/pages/_row', ['field' => $field, 'name' => $name, 'index' => '__INDEX__', 'row' => []]) ?></template>
    <div class="repeater__footer">
        <button class="btn btn--outline btn--sm" type="button" data-repeater-add><?= icon('plus', 'icon--sm') ?> <?= e($addLabel) ?></button>
        <span class="field__help"><?= $max > 0 && $max < 50 ? 'Up to ' . $max . ' items. ' : '' ?>Drag the handle or use the arrows to change the order.</span>
    </div>
</div>
