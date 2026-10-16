<?php
/**
 * Tail of every organisation filter form (OWNER: B8): rows-per-page select, the current sort (kept while filtering) and the
 * Filter / Reset buttons. Vars: $pager (Pagination), $sort, $dir, $filtered (bool), $resetUrl, $keep (optional extra hidden fields name => value).
 */
?>
<div class="field field--small">
    <label class="field__label" for="per_page">Per page</label>
    <select class="select" id="per_page" name="per_page" data-autosubmit>
        <?php foreach ([10, 20, 50, 100] as $n): ?>
            <option value="<?= $n ?>"<?= (int) $pager->perPage === $n ? ' selected' : '' ?>><?= $n ?></option>
        <?php endforeach; ?>
    </select>
</div>
<input type="hidden" name="sort" value="<?= e($sort) ?>">
<input type="hidden" name="dir" value="<?= e($dir) ?>">
<?php foreach (($keep ?? []) as $kn => $kv): if ($kv === '') { continue; } ?>
    <input type="hidden" name="<?= e($kn) ?>" value="<?= e($kv) ?>">
<?php endforeach; ?>
<div class="filters__actions">
    <button class="btn btn--primary btn--sm" type="submit">Apply filters</button>
    <?php if (!empty($filtered)): ?><a class="btn btn--ghost btn--sm" href="<?= e($resetUrl) ?>">Reset</a><?php endif; ?>
</div>
