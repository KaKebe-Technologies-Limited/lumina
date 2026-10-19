<?php
/**
 * Logo drop zone (drag a picture in, or click to choose) with an instant preview in the SAME box the website uses.
 * OWNER: PARTNERS agent. Used by the quick-add bar (index.php) and by the full form (_form.php). Behaviour: assets/js/admin-partners.js [data-pa-drop].
 * Vars: $id (input id, unique on the page), $current (partners row whose logo is shown at first, or null), $hint (extra sentence, optional).
 * The file input is named "logo" (multipart). It works without JavaScript as a plain file field.
 */
use App\Services\PartnerLogos;

$dzId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($id ?? 'pa-logo'));
$dzRow = isset($current) && is_array($current) ? $current : null;
$dzHas = $dzRow !== null && PartnerLogos::view($dzRow)['has'];
?>
<div class="pa-drop" data-pa-drop>
    <input class="pa-drop__input" type="file" id="<?= e($dzId) ?>" name="logo" accept="image/png,image/jpeg,image/webp,image/gif,.png,.jpg,.jpeg,.webp,.gif" data-pa-drop-input aria-describedby="<?= e($dzId) ?>-help <?= e($dzId) ?>-status">
    <label class="pa-drop__area" for="<?= e($dzId) ?>">
        <span class="visually-hidden">Logo picture</span><?php /* the name of the control stays when the preview replaces the visible text */ ?>
        <span class="pa-drop__box plogo" data-pa-preview>
            <?php if ($dzHas): ?>
                <?= partial('partials/partner-logo-img', ['partner' => $dzRow, 'loading' => 'eager']) ?>
            <?php else: ?>
                <span class="pa-drop__empty" data-pa-drop-empty><?= icon('upload', 'icon--lg') ?><strong>Drop the logo here</strong><span>or click to choose a picture</span></span>
            <?php endif; ?>
        </span>
        <span class="pa-drop__over" aria-hidden="true">Drop it to use this picture</span>
    </label>
    <p class="pa-drop__help" id="<?= e($dzId) ?>-help">
        Use a PNG with a transparent background, at least 400 px wide.
        <span class="pa-drop__help-more">JPG, WebP or GIF up to 5 MB work too. Margins are trimmed and the size is optimised for you. Use a colour or dark logo: a white one vanishes on the light boxes.<?= !empty($hint) ? ' ' . e((string) $hint) : '' ?></span>
    </p>
    <p class="pa-drop__status" id="<?= e($dzId) ?>-status" role="status" data-pa-drop-status></p>
    <button class="btn btn--ghost btn--sm pa-drop__clear" type="button" data-pa-drop-clear hidden><?= icon('x', 'icon--sm') ?> Remove this picture</button>
</div>
