<?php
/**
 * Accessible error summary for the public forms (involvement, donate pledge, careers application, contact).
 * Prints NOTHING when the previous request produced no validation errors. Vars:
 *   $title   heading of the summary (editable copy, e.g. content('contact', 'form.error_title'))
 *   $fields  [field name => ['id' => 'html id of the input', 'label' => 'visible label']]; unknown error keys link to f-<name>
 *   $sid     unique id prefix of the summary (default 'form')
 * The block is focusable (tabindex -1, role alert); assets/js/pages/form-summary.js moves focus to it after the redirect and makes each
 * link focus its field. Without JS the links still jump to the fields.
 */
$errs = errors();
if (!$errs) {
    return;
}
$sid = preg_replace('/[^a-z0-9_-]/i', '', (string) ($sid ?? 'form')) ?: 'form';
$fields = (array) ($fields ?? []);
$this->start('scripts'); ?><script src="<?= e(asset('js/pages/form-summary.js')) ?>" defer></script><?php $this->end(); ?>
<div class="ia-error-summary" id="<?= e($sid) ?>-errors" role="alert" tabindex="-1" aria-labelledby="<?= e($sid) ?>-errors-title" data-error-summary>
    <h3 class="ia-error-summary__title" id="<?= e($sid) ?>-errors-title"><?= icon('alert-circle') ?> <span><?= e($title ?? '') ?></span></h3>
    <ul>
        <?php foreach ($errs as $field => $message):
            $field = (string) $field;
            $meta = (array) ($fields[$field] ?? []);
            $inputId = (string) ($meta['id'] ?? ('f-' . preg_replace('/[^a-z0-9_-]/i', '-', $field)));
            $label = trim((string) ($meta['label'] ?? ''));
        ?>
            <li><a href="#<?= e($inputId) ?>" data-error-link><?= $label !== '' ? e($label) . ': ' : '' ?><?= e((string) $message) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
