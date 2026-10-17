<?php
/** Add / edit a FAQ. OWNER: B3. Vars: $mode, $row, $groups, $hints. */
$creating = $mode === 'create';
$action = $creating ? url('admin/faqs') : url('admin/faqs/' . (int) $row['id']);
$v = static fn(string $k, mixed $d = ''): string => (string) old($k, $row[$k] ?? $d);
$active = (string) old('is_active', (int) ($row['is_active'] ?? 1) === 1 ? '1' : '0') === '1';
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', ['title' => $creating ? 'Add a question' : 'Edit question', 'subtitle' => 'Keep answers short and plain. A blank line starts a new paragraph.']) ?>
<form class="card pe-narrow-wide" method="post" action="<?= e($action) ?>" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card__body">
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'question', 'label' => 'Question', 'value' => $v('question'), 'required' => true, 'maxlength' => 300, 'class' => 'field--full', 'attrs' => ['data-maxlength-counter' => true]]) ?>
            <?= partial('partials/form-field', ['name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'value' => $v('answer'), 'required' => true, 'rows' => 8, 'class' => 'field--full', 'help' => 'Plain text. Web addresses are not turned into links here.']) ?>
            <div class="field">
                <label class="field__label" for="f-group_key">Group</label>
                <input class="input" type="text" id="f-group_key" name="group_key" list="faq-groups" maxlength="50" required value="<?= e($v('group_key', 'donate')) ?>" aria-describedby="f-group-help"<?= has_error('group_key') ? ' aria-invalid="true"' : '' ?>>
                <datalist id="faq-groups"><?php foreach ($groups as $g): ?><option value="<?= e($g) ?>"><?php endforeach; ?></datalist>
                <div class="field__help" id="f-group-help">Where the question appears: "home" = home page FAQ; "donate" = Donate page (and the home page when "home" and "general" are empty); "general" = home page FAQ when "home" is empty; "membership" = Membership page.</div>
                <?php if (has_error('group_key')): ?><div class="field__error" role="alert"><?= e(error('group_key')) ?></div><?php endif; ?>
            </div>
            <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Position', 'type' => 'number', 'value' => $v('sort_order', '0'), 'min' => 0, 'step' => 1, 'help' => 'Lower numbers come first. You can also drag questions into order on the list.']) ?>
            <div class="field pe-switches">
                <label class="switch"><input type="checkbox" name="is_active" value="1"<?= $active ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Show on the website</span></label>
            </div>
        </div>
    </div>
    <div class="card__footer">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $creating ? 'Add question' : 'Save changes' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/faqs', ['group' => $v('group_key', 'donate')])) ?>">Cancel</a>
    </div>
</form>
</div>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script><?php $this->end(); ?>
