<?php
/**
 * Registry entry (VIRTUAL, no URL): SHARED LABELS for pages the owner creates in the admin (Pages > Add a page). OWNER: B2 "web-content".
 * The title, body, hero photo and SEO of each custom page are edited on that page itself; this holds the small labels
 * that every custom page shares.
 */
return [
    'slug'    => 'site_custom',
    'title'   => 'Labels for pages you create',
    'virtual' => true,
    'help'    => 'Small pieces of text that appear on every page you create yourself (for example "In this section").',
    'nav'     => ['label' => 'Created-page labels', 'order' => 9030],
    'seo'     => ['title' => 'Lumina Africa Foundation'],
    'sections' => [
        [
            'key' => 'labels', 'label' => 'Labels',
            'fields' => [
                ['key' => 'children_title', 'label' => 'Heading above the list of sub-pages', 'type' => 'text', 'default' => 'In this section'],
                ['key' => 'updated_label', 'label' => 'Last-updated label', 'type' => 'text', 'default' => 'Last updated'],
                ['key' => 'show_updated', 'label' => 'Show the last-updated date', 'type' => 'boolean', 'default' => false],
            ],
        ],
    ],
];
