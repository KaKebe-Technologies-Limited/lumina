<?php
/**
 * Newsletter editor: picture picker (upload with progress, preview, description for screen readers, optional caption).
 * Behaviour: assets/js/admin-newsletter.js ([data-image]). It is used for the cover, the featured article, article blocks and image blocks.
 * Vars: $idp (unique id prefix, may contain __ID__ inside a <template>), $label, $help, $caption (bool), $altMax (int, default 300), $altHelp.
 */
$idp = (string) ($idp ?? 'img');
$altMax = (int) ($altMax ?? 300);
?>
<div class="nl-image" data-image data-alt-max="<?= $altMax ?>">
    <span class="field__label" id="<?= e($idp) ?>-label"><?= e($label ?? 'Picture') ?></span>
    <div class="nl-image__row">
        <div class="nl-image__preview is-empty" data-image-preview>
            <img src="" alt="" hidden>
            <span class="nl-image__placeholder" aria-hidden="true"><?= icon('image', 'icon--lg') ?></span>
        </div>
        <div class="nl-image__side">
            <div class="nl-image__buttons">
                <label class="btn btn--outline btn--sm nl-image__pick" for="<?= e($idp) ?>-file"><?= icon('upload', 'icon--sm') ?> <span data-image-pick-label>Choose a picture</span></label>
                <input class="nl-image__file" type="file" id="<?= e($idp) ?>-file" accept="image/jpeg,image/png,image/gif,image/webp" data-image-file aria-describedby="<?= e($idp) ?>-help">
                <button type="button" class="btn btn--ghost btn--sm" data-image-remove hidden><?= icon('trash', 'icon--sm') ?> Remove picture</button>
            </div>
            <div class="nl-progress" data-image-progress hidden>
                <progress max="100" value="0" aria-label="Upload progress"></progress>
                <span data-image-progress-text role="status"></span>
            </div>
            <p class="field__help" id="<?= e($idp) ?>-help"><?= e($help ?? 'JPG, PNG, GIF or WebP, up to 5 MB. You can also drop a picture on this box.') ?></p>
            <p class="field__error" data-image-error role="alert" hidden></p>
        </div>
    </div>
    <input type="hidden" data-image-path>
    <div class="nl-image__meta" data-image-meta hidden>
        <div class="field">
            <label class="field__label" for="<?= e($idp) ?>-alt">Describe the picture</label>
            <input class="input" type="text" id="<?= e($idp) ?>-alt" maxlength="<?= $altMax ?>" autocomplete="off" data-image-alt aria-describedby="<?= e($idp) ?>-alt-help">
            <p class="field__help" id="<?= e($idp) ?>-alt-help"><?= e($altHelp ?? 'A short description for people who cannot see the picture, and for search engines. For example: "Students learning to use laptops".') ?></p>
        </div>
        <?php if (!empty($caption)): ?>
            <div class="field">
                <label class="field__label" for="<?= e($idp) ?>-caption">Caption (optional)</label>
                <input class="input" type="text" id="<?= e($idp) ?>-caption" maxlength="300" autocomplete="off" data-image-caption>
                <p class="field__help">Shown in small text under the picture.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
