<?php
/**
 * Status tabs with counts (admin inbox lists). Vars: $tabs (list of ['key','label','count']), $current (active key),
 * $base (admin path such as 'admin/messages'), $keep (assoc list of query parameters to preserve, e.g. the search term).
 */
$keep = array_filter((array) ($keep ?? []), static fn($v): bool => $v !== null && $v !== '');
?>
<nav class="tabs inbox-tabs" aria-label="Filter by status">
    <?php foreach ($tabs as $t): $on = $t['key'] === $current; ?>
        <a class="tabs__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url($base, $keep + ($t['key'] === 'all' ? [] : ['status' => $t['key']]))) ?>"<?= $on ? ' aria-current="page"' : '' ?>>
            <?= e($t['label']) ?> <span class="inbox-count"><?= (int) $t['count'] ?></span>
        </a>
    <?php endforeach; ?>
</nav>
