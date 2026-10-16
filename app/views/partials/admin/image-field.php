<?php
/**
 * Image picker with live preview. Uploads happen immediately via AJAX (POST admin/media/upload, handled by assets/js/admin.js)
 * and the stored path lands in the hidden input "<name>". Works without JS too: the file input "<name>_file" is posted
 * multipart and picked up by Uploader::resolveImageField($request, '<name>', $current) in the controller.
 *
 * Vars: $name (required, e.g. 'image'), $label, $value (current stored path), $alt_name (optional text input name, e.g. 'image_alt'),
 *       $alt_value, $help, $required, $max_bytes (default 5 MB), $id.
 * Controller (create/update):   $data['image'] = Uploader::resolveImageField($request, 'image', $row['image'] ?? null);
 *                               $data['image_alt'] = $request->string('image_alt');
 */
$id = $id ?? 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $name);
$value = array_key_exists('value', get_defined_vars()) ? (string) $value : (string) old((string) $name, '');
$preview = $value !== '' ? img_url($value) : '';
$err = error((string) $name) ?? (isset($alt_name) ? error((string) $alt_name) : null);
?>
<div class="field<?= $err ? ' field--error' : '' ?>">
    <?php if (!empty($label)): ?><span class="field__label" id="<?= e($id) ?>-label"><?= e($label) ?><?= !empty($required) ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></span><?php endif; ?>
    <div class="image-field" data-image-field data-max-bytes="<?= (int) ($max_bytes ?? 5242880) ?>">
        <div class="image-field__preview<?= $preview === '' ? ' is-empty' : '' ?>" data-image-preview>
            <img src="<?= e($preview) ?>" alt="">
            <span class="image-field__placeholder"><?= icon('image', 'icon--lg') ?></span>
        </div>
        <div class="image-field__controls">
            <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>" data-image-path data-current-url="<?= e($preview) ?>">
            <input type="hidden" name="<?= e($name) ?>_remove" value="" data-image-remove>
            <input class="image-field__file" id="<?= e($id) ?>" type="file" name="<?= e($name) ?>_file" accept="image/jpeg,image/png,image/gif,image/webp" data-image-input aria-labelledby="<?= e($id) ?>-label">
            <?php if (isset($alt_name)): ?>
                <label class="visually-hidden" for="<?= e($id) ?>-alt">Image description (alt text)</label>
                <input class="input" id="<?= e($id) ?>-alt" type="text" name="<?= e($alt_name) ?>" maxlength="250" value="<?= e(array_key_exists('alt_value', get_defined_vars()) ? $alt_value : old((string) $alt_name, '')) ?>" placeholder="Describe the image (alt text, for screen readers)">
            <?php endif; ?>
            <div class="cluster">
                <button class="btn btn--ghost btn--sm" type="button" data-image-clear><?= icon('trash', 'icon--sm') ?> Remove image</button>
                <span class="field__help" data-image-status role="status"></span>
            </div>
        </div>
    </div>
    <?php if (!empty($help)): ?><div class="field__help"><?= e($help) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
</div>
