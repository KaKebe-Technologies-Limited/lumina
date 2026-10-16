<?php
/** Search box for the filter forms (OWNER: B8). Vars: $q (current term), $placeholder. */
?>
<div class="field field--grow">
    <label class="field__label" for="q">Search</label>
    <div class="search-input">
        <?= icon('search', 'icon--sm') ?>
        <input class="input" type="search" id="q" name="q" value="<?= e($q ?? '') ?>" placeholder="<?= e($placeholder ?? 'Search') ?>" maxlength="100" autocomplete="off">
    </div>
</div>
