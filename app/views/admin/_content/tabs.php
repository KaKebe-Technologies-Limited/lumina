<?php
/**
 * Status tabs above a list (All / Active / Inactive ...). OWNER: B4.
 * Vars: $tabs (list of ['key','label','count','active']), $listUrl (callable(array $overrides): string), $label (aria-label, default 'Filter by status').
 */
if (!isset($tabs) || count($tabs) < 2) {
    return;
}
?>
<nav class="tabs content-tabs" aria-label="<?= e($label ?? 'Filter by status') ?>">
    <?php foreach ($tabs as $t): ?>
        <a class="tabs__item<?= $t['active'] ? ' is-active' : '' ?>" href="<?= e($listUrl([$param ?? 'status' => $t['key'] === 'all' ? null : $t['key']])) ?>"<?= $t['active'] ? ' aria-current="page"' : '' ?>>
            <?= e($t['label']) ?> <span class="content-tabs__count"><?= (int) $t['count'] ?></span>
        </a>
    <?php endforeach; ?>
</nav>
