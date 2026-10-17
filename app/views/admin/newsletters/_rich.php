<?php
/**
 * Newsletter editor: one rich-text field (assets/js/admin-editor.js upgrades the textarea; the server sanitises the result).
 * Vars: $idp (unique id, may contain __ID__), $label, $help, $placeholder, $f (data-f name, default body_html).
 */
$idp = (string) ($idp ?? 'rt');
?>
<div class="field nl-rich" data-rich-field>
    <label class="field__label" for="<?= e($idp) ?>"><?= e($label ?? 'Text') ?></label>
    <textarea class="textarea" id="<?= e($idp) ?>" rows="7" data-richtext data-f="<?= e($f ?? 'body_html') ?>" data-placeholder="<?= e($placeholder ?? 'Write here...') ?>" aria-describedby="<?= e($idp) ?>-help"></textarea>
    <p class="field__help" id="<?= e($idp) ?>-help"><?= e($help ?? 'Short paragraphs read best. Use the buttons above the box for bold, lists and links.') ?></p>
</div>
