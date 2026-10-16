<?php
/**
 * Sortable table header cell. OWNER: B4.
 * Vars: $label, $key (sort key from the controller's config), $sort, $dir, $defaultSort, $listUrl (callable), $class (optional).
 */
$on = $sort === $key;
$next = ($on && $dir === 'asc') ? 'desc' : 'asc';
$aria = $on ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
$over = ['sort' => $key === $defaultSort ? null : $key, 'dir' => ($key === $defaultSort && $next === 'asc') ? null : $next];
?>
<th scope="col"<?= !empty($class) ? ' class="' . e($class) . '"' : '' ?> aria-sort="<?= e($aria) ?>">
    <a class="th-sort<?= $on ? ' is-sorted' : '' ?>" href="<?= e($listUrl($over)) ?>"><?= e($label) ?><?= $on ? icon($dir === 'asc' ? 'arrow-up' : 'arrow-down', 'icon--sm') : '' ?></a>
</th>
