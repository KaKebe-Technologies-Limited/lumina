<?php
/** Empty list state. Vars: $c (config), $hasFilters, $createUrl, $icon, $createLabel (default "Add ..."), $hint. OWNER: B4. */
if (!empty($hasFilters)) {
    echo partial('partials/empty-state', [
        'icon' => 'search', 'title' => 'Nothing matches your search',
        'text' => 'Try a different word, or clear the filters to see every ' . $c['singular'] . '.',
        'actions' => [['label' => 'Clear search and filters', 'url' => url($c['path']), 'class' => 'btn--outline']],
    ]);
    return;
}
echo partial('partials/empty-state', [
    'icon' => $icon ?? 'inbox', 'title' => 'No ' . $c['plural'] . ' yet',
    'text' => $hint ?? ('When you add a ' . $c['singular'] . ' it will be listed here and shown on the website.'),
    'actions' => !empty($createUrl) ? [['label' => $createLabel ?? ('Add the first ' . $c['singular']), 'url' => $createUrl]] : [],
]);
