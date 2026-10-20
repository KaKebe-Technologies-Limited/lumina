<?php
/**
 * Heading of one designer tab. Vars: $key (tab key), $tab (label, icon, help), $reset (true = show the "back to the reference" button).
 * The button belongs to the separate form #mc-reset (form="mc-reset"), so Enter in a field never triggers it.
 */
$reset = $reset ?? true;
?>
<div class="card__header mc-panel__head">
    <div class="mc-panel__titles">
        <h2 class="card__title" id="mc-heading-<?= e($key) ?>" tabindex="-1"><?= e($tab['label']) ?></h2>
        <p class="mc-panel__help"><?= e($tab['help']) ?></p>
    </div>
    <?php if ($reset): ?>
        <button class="btn btn--ghost btn--sm mc-panel__reset" type="submit" form="mc-reset" name="reset" value="<?= e($key) ?>"
                data-confirm="Put every setting on the &quot;<?= e($tab['label']) ?>&quot; tab back to the reference design? Your changes on this tab are replaced.">
            <?= icon('refresh', 'icon--sm') ?> Back to the reference
        </button>
    <?php endif; ?>
</div>
