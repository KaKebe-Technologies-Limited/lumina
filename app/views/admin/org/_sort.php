<?php
/**
 * Sortable column header link for the organisation lists (OWNER: B8). Keeps the current filters, drops ?page.
 * Vars: $label, $col (sort key), $sort (current key), $dir (current 'asc'|'desc').
 */
$isActive = ($sort ?? '') === $col;
$next = ($isActive && ($dir ?? 'asc') === 'asc') ? 'desc' : 'asc';
$query = array_merge($_GET, ['sort' => $col, 'dir' => $next]);
unset($query['page']);
$req = request();
$href = url(ltrim($req ? $req->path() : '', '/'), $query);
?>
<a class="org-sort<?= $isActive ? ' is-active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?><?php if ($isActive): ?><?= icon(($dir ?? 'asc') === 'asc' ? 'arrow-up' : 'arrow-down', 'icon--sm') ?><span class="visually-hidden">, sorted <?= ($dir ?? 'asc') === 'asc' ? 'ascending' : 'descending' ?>. Activate to reverse.</span><?php endif; ?></a>
