<?php
/**
 * Admin sidebar fragment: ADMINISTRATION (site settings, users, activity log, my profile). OWNER: B3 "admin-core".
 * Sits after the Content / Organisation / Inbox groups (order 50). Items are hidden for roles without the ability.
 */
return [
    'key'   => 'administration',
    'label' => 'Administration',
    'order' => 50,
    'items' => [
        ['key' => 'maintenance', 'label' => 'Website on / off', 'url' => 'admin/maintenance', 'icon' => 'clock', 'can' => 'settings.manage', 'order' => 5],
        ['key' => 'settings', 'label' => 'Site settings', 'url' => 'admin/settings', 'icon' => 'settings', 'can' => 'settings.manage', 'order' => 10],
        ['key' => 'users', 'label' => 'Users', 'url' => 'admin/users', 'icon' => 'users', 'can' => 'users.manage', 'order' => 20],
        ['key' => 'audit', 'label' => 'Activity log', 'url' => 'admin/audit-log', 'icon' => 'history', 'can' => 'audit.view', 'order' => 30],
        ['key' => 'profile', 'label' => 'My profile', 'url' => 'admin/profile', 'icon' => 'user', 'can' => 'dashboard.view', 'order' => 40],
    ],
];
