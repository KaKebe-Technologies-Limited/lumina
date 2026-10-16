<?php
/**
 * Bulk-action bar. The checkboxes live in the table (name="ids[]" form="<formId>"), so row buttons can be real forms.
 * Vars: $formId, $action (form URL), $actions (value => label), $danger (values that ask for confirmation), $noun (e.g. 'messages').
 * JS: assets/js/pages/admin-inbox.js ([data-bulk-form], [data-select-all], [data-select-row]).
 */
$noun = $noun ?? 'items';
?>
<form id="<?= e($formId) ?>" class="inbox-bulk" method="post" action="<?= e($action) ?>" data-bulk-form data-bulk-danger="<?= e(implode(',', (array) ($danger ?? []))) ?>" data-bulk-noun="<?= e($noun) ?>">
    <?= csrf_field() ?>
    <label class="visually-hidden" for="<?= e($formId) ?>-action">Action for the selected <?= e($noun) ?></label>
    <select class="select" id="<?= e($formId) ?>-action" name="action">
        <option value="">Bulk action...</option>
        <?php foreach ($actions as $value => $label): ?>
            <option value="<?= e($value) ?>"><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn--outline btn--sm" type="submit">Apply</button>
    <span class="inbox-bulk__count" data-selected-count role="status" aria-live="polite"></span>
</form>
