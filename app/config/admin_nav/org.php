<?php
/**
 * Admin sidebar fragment: ORGANISATION. OWNER: B8 "org-admin" (team, partners, documents, events, stories).
 * Group order 30 (core=10, content=20, org=30, inbox=40). Item keys equal the 'nav' option the controllers pass to render().
 */
return [
    'key'   => 'org',
    'label' => 'Organisation',
    'order' => 30,
    'items' => [
        ['key' => 'team',      'label' => 'Team',            'url' => 'admin/team',      'icon' => 'users',     'can' => 'content.manage', 'order' => 10, 'badge' => null],
        ['key' => 'partners',  'label' => 'Partners',        'url' => 'admin/partners',  'icon' => 'building',  'can' => 'content.manage', 'order' => 20, 'badge' => null],
        ['key' => 'documents', 'label' => 'Documents',       'url' => 'admin/documents', 'icon' => 'file-text', 'can' => 'content.manage', 'order' => 30, 'badge' => null],
        ['key' => 'events',    'label' => 'Events',          'url' => 'admin/events',    'icon' => 'calendar',  'can' => 'content.manage', 'order' => 40, 'badge' => null],
        ['key' => 'stories',   'label' => 'Impact stories',  'url' => 'admin/stories',   'icon' => 'quote',     'can' => 'content.manage', 'order' => 50, 'badge' => null],
    ],
];
