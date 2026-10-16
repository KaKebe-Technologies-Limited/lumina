<?php
/**
 * Rich-text field. Renders <textarea data-richtext name="<name>"> which assets/js/admin-editor.js (B4) upgrades to the
 * contenteditable editor. The server ALWAYS sanitises: Sanitizer::html($request->post('<name>')).
 * Vars: $name (required), $label, $value (default old($name)), $help, $required, $rows (default 12), $id, $placeholder.
 * Pages that use it must load the editor: in the view call  <?php $this->start('scripts'); ?><script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script><?php $this->end(); ?>
 */
$id = $id ?? 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $name);
$value = array_key_exists('value', get_defined_vars()) ? (string) $value : (string) old((string) $name, '');
$err = error((string) $name);
?>
<div class="field<?= $err ? ' field--error' : '' ?>">
    <?php if (!empty($label)): ?><label class="field__label" for="<?= e($id) ?>"><?= e($label) ?><?= !empty($required) ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></label><?php endif; ?>
    <textarea class="textarea textarea--tall" id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= (int) ($rows ?? 12) ?>" data-richtext<?= !empty($placeholder) ? ' data-placeholder="' . e($placeholder) . '"' : '' ?><?= !empty($required) ? ' required' : '' ?>><?= e($value) ?></textarea>
    <?php if (!empty($help)): ?><div class="field__help"><?= e($help) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
</div>
