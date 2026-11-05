<?php
/**
 * "Follow and subscribe to our pages": one button per social media page (Admin > Social media) and, when it is required, the tick that must be set to go on.
 * Vars: $required (bool, default false). The tick is named "follow".
 */
$required = !empty($required);
$links = social_links('all');
if ($links === []) {
    return;
}
?>
<div class="ac-follow field<?= error('follow') ? ' field--error' : '' ?>" role="group" aria-labelledby="ac-follow-title">
    <p class="ac-follow__title" id="ac-follow-title"><?= icon('heart', 'icon--sm') ?> Follow and subscribe to our pages<?= $required ? ' <abbr title="required" aria-label="required">*</abbr>' : '' ?></p>
    <p class="ac-follow__text">Stay close to Lumina Africa Foundation and the LAF Academy: news, new courses, classes and opportunities are shared on our pages. Open each one and follow or subscribe<?= $required ? ', then come back and tick the box' : '' ?>.</p>
    <ul class="ac-follow__links">
        <?php foreach ($links as $l): ?>
            <li><a class="btn btn--outline btn--sm" href="<?= e($l['url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon((string) $l['icon'], 'icon--sm') ?> <?= e($l['label']) ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($required): ?>
        <label class="ac-follow__check"><input type="checkbox" name="follow" value="1" required aria-required="true"<?= old('follow') ? ' checked' : '' ?>> I follow and subscribe to the pages above</label>
    <?php endif; ?>
    <?php if (error('follow')): ?><div class="field__error" role="alert"><?= e((string) error('follow')) ?></div><?php endif; ?>
</div>
