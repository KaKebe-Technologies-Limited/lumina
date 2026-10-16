<?php
/**
 * List filter bar (GET form). OWNER: B4.
 * Vars: $path (e.g. 'admin/programs'), $q (current search), $placeholder, $selects (list of ['name','label','options' (value=>label),'value','empty']),
 *       $perPage, $keep (hidden inputs to preserve: name => value, e.g. status/sort/dir), $hasFilters (show Reset), $total (result count).
 */
$selects = $selects ?? [];
$keep = array_filter((array) ($keep ?? []), static fn($v) => $v !== null && $v !== '');
?>
<form class="filters" method="get" action="<?= e(url($path)) ?>" role="search" aria-label="Search and filter">
    <div class="field field--grow">
        <label class="field__label" for="f-q">Search</label>
        <div class="search-input">
            <?= icon('search', 'icon--sm') ?>
            <input class="input" type="search" id="f-q" name="q" value="<?= e($q ?? '') ?>" placeholder="<?= e($placeholder ?? 'Search...') ?>" maxlength="120" autocomplete="off">
        </div>
    </div>
    <?php foreach ($selects as $s): $sid = 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $s['name']); ?>
        <div class="field">
            <label class="field__label" for="<?= e($sid) ?>"><?= e($s['label']) ?></label>
            <select class="select" id="<?= e($sid) ?>" name="<?= e($s['name']) ?>" data-autosubmit>
                <option value=""><?= e($s['empty'] ?? 'Any') ?></option>
                <?php foreach ((array) $s['options'] as $ov => $ol): ?>
                    <option value="<?= e($ov) ?>"<?= (string) $ov === (string) ($s['value'] ?? '') ? ' selected' : '' ?>><?= e($ol) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endforeach; ?>
    <div class="field">
        <label class="field__label" for="f-per-page">Per page</label>
        <select class="select" id="f-per-page" name="per_page" data-autosubmit>
            <?php foreach (($perPageOptions ?? [10, 20, 50, 100]) as $n): ?>
                <option value="<?= $n ?>"<?= (int) ($perPage ?? 20) === $n ? ' selected' : '' ?>><?= $n ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php foreach ($keep as $k => $v): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endforeach; ?>
    <div class="filters__actions">
        <button class="btn btn--primary" type="submit"><?= icon('search', 'icon--sm') ?> Search</button>
        <?php if (!empty($hasFilters)): ?><a class="btn btn--ghost" href="<?= e(url($path)) ?>">Reset</a><?php endif; ?>
    </div>
</form>
<?php if (isset($total)): ?><p class="result-count" role="status"><?= (int) $total ?> <?= (int) $total === 1 ? 'result' : 'results' ?><?= !empty($hasFilters) ? ' match your search' : '' ?></p><?php endif; ?>
