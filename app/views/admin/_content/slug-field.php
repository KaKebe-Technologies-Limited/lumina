<?php
/**
 * Web-address (slug) input with a live preview of the public URL. OWNER: B4. JS: admin.js [data-slug-source] fills the field from the title
 * until it is edited by hand; admin-content.js [data-slug-preview] shows the resulting address.
 * Vars: $value (current slug), $source (CSS selector of the title input, e.g. '#f-title'), $prefix (public path before the slug, e.g. 'programs/'),
 *       $prefixFrom (optional CSS selector of a select whose option data-prefix gives the prefix, used by posts), $isNew, $id (default f-slug).
 */
$id = $id ?? 'f-slug';
$err = error('slug');
$value = (string) ($value ?? '');
?>
<div class="field field--full<?= $err ? ' field--error' : '' ?>">
    <label class="field__label" for="<?= e($id) ?>">Web address (slug)</label>
    <input class="input input--code" type="text" id="<?= e($id) ?>" name="slug" value="<?= e($value) ?>" maxlength="120" autocomplete="off" spellcheck="false"
           data-slug-source="<?= e($source ?? '#f-title') ?>" aria-describedby="<?= e($id) ?>-help"<?= $err ? ' aria-invalid="true"' : '' ?>>
    <div class="field__help" id="<?= e($id) ?>-help">
        <span data-slug-preview data-base="<?= e(url('')) ?>" data-origin="<?= e(origin()) ?>" data-prefix="<?= e($prefix ?? '') ?>"<?= !empty($prefixFrom) ? ' data-prefix-from="' . e($prefixFrom) . '"' : '' ?> data-slug-input="#<?= e($id) ?>">
            Public address: <code><?= e(abs_url(($prefix ?? '') . ($value !== '' ? $value : 'your-title'))) ?></code>
        </span><br>
        <?= !empty($isNew) ? 'Created from the title automatically; you can edit it.' : 'Changing the address of a published page breaks links that already point to it.' ?>
    </div>
    <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
</div>
