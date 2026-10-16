<?php
/** Add / edit an impact figure. OWNER: B3. Vars: $mode, $row, $groups, $icons. */
$creating = $mode === 'create';
$action = $creating ? url('admin/stats') : url('admin/stats/' . (int) $row['id']);
$v = static fn(string $k, mixed $d = ''): string => (string) old($k, $row[$k] ?? $d);
$active = (string) old('is_active', (int) ($row['is_active'] ?? 1) === 1 ? '1' : '0') === '1';
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', ['title' => $creating ? 'Add an impact figure' : 'Edit impact figure', 'subtitle' => 'A number with a short label, shown with a count-up animation on the website.']) ?>
<form class="card pe-narrow-wide" method="post" action="<?= e($action) ?>" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card__body">
        <div class="pe-stat-preview" aria-live="polite">
            <span class="pe-stat-preview__value" data-stat-preview-value></span>
            <span class="pe-stat-preview__label" data-stat-preview-label></span>
        </div>
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'label', 'label' => 'Label', 'value' => $v('label'), 'required' => true, 'maxlength' => 120, 'help' => 'For example "Youth Trained". Shown under the number.', 'attrs' => ['data-stat-label' => true]]) ?>
            <?= partial('partials/form-field', ['name' => 'value_number', 'label' => 'Number', 'type' => 'number', 'value' => $v('value_number'), 'required' => true, 'min' => 0, 'step' => 1, 'help' => 'A whole number, without commas.', 'attrs' => ['data-stat-number' => true, 'inputmode' => 'numeric']]) ?>
            <?= partial('partials/form-field', ['name' => 'prefix', 'label' => 'Before the number (optional)', 'value' => $v('prefix'), 'maxlength' => 10, 'help' => 'For example UGX', 'attrs' => ['data-stat-prefix' => true]]) ?>
            <?= partial('partials/form-field', ['name' => 'suffix', 'label' => 'After the number (optional)', 'value' => $v('suffix'), 'maxlength' => 10, 'help' => 'For example + or %', 'attrs' => ['data-stat-suffix' => true]]) ?>
            <?= partial('partials/form-field', ['name' => 'description', 'label' => 'Short description (optional)', 'value' => $v('description'), 'maxlength' => 255, 'class' => 'field--full', 'help' => 'Extra explanation where the website has room for it.']) ?>
            <div class="field">
                <label class="field__label" for="f-group_key">Group</label>
                <input class="input" type="text" id="f-group_key" name="group_key" list="stat-groups" maxlength="50" required value="<?= e($v('group_key', 'home')) ?>" aria-describedby="f-group-help"<?= has_error('group_key') ? ' aria-invalid="true"' : '' ?>>
                <datalist id="stat-groups"><?php foreach ($groups as $g): ?><option value="<?= e($g) ?>"><?php endforeach; ?></datalist>
                <div class="field__help" id="f-group-help">Where the figure appears. "home" is the home page.</div>
                <?php if (has_error('group_key')): ?><div class="field__error" role="alert"><?= e(error('group_key')) ?></div><?php endif; ?>
            </div>
            <?= partial('partials/form-field', ['name' => 'icon', 'label' => 'Icon (optional)', 'type' => 'select', 'value' => $v('icon'), 'options' => array_combine($icons, array_map('label_from', array_map(static fn($i) => str_replace('-', '_', $i), $icons))), 'empty' => 'No icon']) ?>
            <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Position', 'type' => 'number', 'value' => $v('sort_order', '0'), 'min' => 0, 'step' => 1, 'help' => 'Lower numbers come first. You can also drag them into order on the list.']) ?>
            <div class="field pe-switches">
                <label class="switch"><input type="checkbox" name="is_active" value="1"<?= $active ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Show on the website</span></label>
            </div>
        </div>
    </div>
    <div class="card__footer">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $creating ? 'Add figure' : 'Save changes' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/stats', ['group' => $v('group_key', 'home')])) ?>">Cancel</a>
    </div>
</form>
</div>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script><?php $this->end(); ?>
