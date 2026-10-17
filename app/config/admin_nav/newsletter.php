<?php
/**
 * Admin sidebar fragment: NEWSLETTER (issues + write a new one). Group order 37 (core=10, content=20, org=30, membership=35, inbox=40).
 * The e-mail panel is reached from each issue (admin only), so it has no sidebar item. Contract: docs/NEWSLETTER.md.
 */

return [
    'key'   => 'newsletter',
    'label' => 'Newsletter',
    'order' => 37,
    'items' => [
        ['key' => 'newsletters', 'label' => 'Issues', 'url' => 'admin/newsletters', 'icon' => 'newspaper', 'can' => 'newsletter.manage', 'order' => 10, 'badge' => null],
        ['key' => 'newsletter_create', 'label' => 'Write an issue', 'url' => 'admin/newsletters/create', 'icon' => 'plus', 'can' => 'newsletter.manage', 'order' => 20, 'badge' => null],
    ],
];
