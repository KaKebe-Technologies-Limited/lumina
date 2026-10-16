<?php
/**
 * Bulk action bar for the organisation lists (OWNER: B8). It is an EMPTY form: the row checkboxes sit in the table and are tied
 * to it with form="bulk-form", so the per-row mini forms in the table never nest inside another form.
 * assets/js/pages/admin-org.js: enables "Apply" only when rows are selected + an action is chosen, shows "N selected", and
 * asks for confirmation when the chosen option carries data-bulk-confirm ("{n}" is replaced by the selection size).
 * Vars: $action (POST url), $options (list of ['value','label','confirm'?]), $noun (e.g. 'team members').
 */
$req = request();
?>
<form id="bulk-form" class="org-bulk" method="post" action="<?= e($action) ?>" data-bulk-form>
    <?= csrf_field() ?>
    <input type="hidden" name="_back" value="<?= e($req ? $req->uri() : '') ?>">
    <label class="visually-hidden" for="bulk-action">Action for the selected <?= e($noun ?? 'rows') ?></label>
    <select class="select" id="bulk-action" name="bulk_action" data-bulk-action>
        <option value="">With selected&hellip;</option>
        <?php foreach ($options as $o): ?>
            <option value="<?= e($o['value']) ?>"<?= !empty($o['confirm']) ? ' data-bulk-confirm="' . e($o['confirm']) . '"' : '' ?>><?= e($o['label']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn--outline btn--sm" type="submit" data-bulk-submit>Apply</button>
    <span class="org-bulk__count" data-bulk-count role="status" aria-live="polite"></span>
</form>
