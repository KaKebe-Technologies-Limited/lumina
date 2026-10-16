<?php
/**
 * Sticky save bar at the bottom of an edit form. OWNER: B4.
 * Vars: $listUrl, $isNew, $publicUrl (optional: "View on website" link), $previewUrl (optional: opens a preview in a new tab), $extraButtons (html-free list of ['label','name','value','class']).
 */
?>
<div class="form-actions">
    <button class="btn btn--primary" type="submit" name="after" value="stay"><?= icon('save', 'icon--sm') ?> <?= !empty($isNew) ? 'Create' : 'Save changes' ?></button>
    <button class="btn btn--outline" type="submit" name="after" value="list">Save and close</button>
    <?php foreach ((array) ($extraButtons ?? []) as $b): ?>
        <button class="btn <?= e($b['class'] ?? 'btn--outline') ?>" type="submit" name="<?= e($b['name']) ?>" value="<?= e($b['value']) ?>"><?= e($b['label']) ?></button>
    <?php endforeach; ?>
    <a class="btn btn--ghost" href="<?= e($listUrl) ?>">Cancel</a>
    <?php if (!empty($previewUrl)): ?><a class="btn btn--ghost" href="<?= e($previewUrl) ?>" target="_blank" rel="noopener"><?= icon('eye', 'icon--sm') ?> Preview</a><?php endif; ?>
    <?php if (!empty($publicUrl)): ?><a class="btn btn--ghost" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener"><?= icon('external-link', 'icon--sm') ?> View on website</a><?php endif; ?>
    <span class="required-note">Fields marked <span aria-hidden="true">*</span><span class="visually-hidden">with an asterisk</span> are required.</span>
</div>
