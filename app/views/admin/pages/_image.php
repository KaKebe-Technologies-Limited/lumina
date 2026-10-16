<?php
/**
 * Image picker for the Pages editor (own markup: the shared partials/admin/image-field cannot be used with bracketed input names,
 * because its "<name>_remove" / "<name>_file" siblings collapse onto "<name>" once PHP parses "blocks[x]_remove").
 * Behaviour: assets/js/pages/admin-pages.js ([data-pe-image]): AJAX upload through LuminaAdmin.uploadImage(), live preview, remove.
 * Vars: $name (path input), $altName (alt text input, '' = none), $value (stored path), $alt, $id, $label, $help (optional).
 * Works inside repeater rows too (event delegation, so rows added later are covered).
 */
$id = $id ?? $name;
$preview = (string) $value !== '' ? img_url((string) $value) : '';
$altName = $altName ?? '';
?>
<div class="pe-image" data-pe-image data-max-bytes="<?= (int) config('upload.max_image_bytes', 5242880) ?>">
    <div class="pe-image__preview<?= $preview === '' ? ' is-empty' : '' ?>" data-pe-image-preview>
        <img src="<?= e($preview) ?>" alt="" loading="lazy">
        <span class="pe-image__placeholder"><?= icon('image', 'icon--lg') ?><span>No image</span></span>
    </div>
    <div class="pe-image__controls">
        <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>" data-pe-image-path>
        <div class="pe-image__buttons">
            <label class="btn btn--outline btn--sm pe-image__pick" for="<?= e($id) ?>">
                <?= icon('upload', 'icon--sm') ?> <span data-pe-image-label><?= $preview === '' ? 'Choose an image' : 'Replace image' ?></span>
                <input class="pe-image__file" type="file" id="<?= e($id) ?>" accept="image/jpeg,image/png,image/gif,image/webp" data-pe-image-input>
            </label>
            <button class="btn btn--ghost btn--sm" type="button" data-pe-image-clear<?= $preview === '' ? ' hidden' : '' ?>><?= icon('trash', 'icon--sm') ?> Remove</button>
        </div>
        <div class="pe-image__status" data-pe-image-status role="status" aria-live="polite"></div>
        <?php if ($altName !== ''): ?>
            <div class="pe-image__alt">
                <label class="field__label" for="<?= e($id) ?>-alt">Image description (alt text)</label>
                <input class="input" type="text" id="<?= e($id) ?>-alt" name="<?= e($altName) ?>" maxlength="250" value="<?= e($alt) ?>" placeholder="Describe what the picture shows, for people who cannot see it" data-pe-image-alt>
            </div>
        <?php endif; ?>
    </div>
</div>
