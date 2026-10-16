<?php
/**
 * Admin sidebar fragment: CORE (dashboard + editing the website itself). OWNER: B3 "admin-core".
 * The second group "Administration" (settings, users, activity log, profile) is app/config/admin_nav/core_admin.php.
 * Item keys must be unique across all fragments; they equal the 'nav' option the controller passes to render().
 */
return [
    'key'   => 'core',
    'label' => '',
    'order' => 10,
    'items' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'admin', 'icon' => 'home', 'can' => 'dashboard.view', 'order' => 1],
        ['key' => 'pages', 'label' => 'Pages', 'url' => 'admin/pages', 'icon' => 'layout', 'can' => 'pages.edit', 'order' => 10],
        ['key' => 'menus', 'label' => 'Menus', 'url' => 'admin/menus', 'icon' => 'list', 'can' => 'menus.manage', 'order' => 20],
        ['key' => 'stats', 'label' => 'Impact figures', 'url' => 'admin/stats', 'icon' => 'bar-chart', 'can' => 'content.manage', 'order' => 30],
        ['key' => 'faqs', 'label' => 'FAQs', 'url' => 'admin/faqs', 'icon' => 'help-circle', 'can' => 'content.manage', 'order' => 40],
    ],
];
