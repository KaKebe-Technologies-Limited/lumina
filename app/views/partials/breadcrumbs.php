<?php
/**
 * Breadcrumb trail. Vars: $breadcrumbs = list of [label, url|null] (last item usually url null = current page),
 * $home (bool, default true: prepend the Home link). Renders nothing for an empty list.
 */
$crumbs = $breadcrumbs ?? [];
if ($crumbs === []) {
    return;
}
if (($home ?? true) !== false) {
    array_unshift($crumbs, [(string) content('global', 'labels.home_breadcrumb'), url('')]);
}
$last = count($crumbs) - 1;
?>
<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol>
        <?php foreach ($crumbs as $i => [$label, $href]): ?>
            <li><?php if ($href !== null && $i !== $last): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><span aria-current="page"><?= e($label) ?></span><?php endif; ?></li>
        <?php endforeach; ?>
    </ol>
</nav>
