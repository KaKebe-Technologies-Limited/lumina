<?php
/**
 * Registry entry: SITE-WIDE copy used by the shared layout and partials (footer, donate band, common button labels).
 * OWNED BY THE ARCHITECT (foundation) - agents must not edit this file. It is a "virtual" page: it appears in the admin
 * Pages list as "Site-wide content", has no public URL, is never in menus or the sitemap, and is always published.
 *
 * Read it in views with content('global', 'footer.links_title') etc. (the shared partials already do).
 */
return [
    'slug'    => 'global',
    'title'   => 'Site-wide content (header, footer, shared buttons)',
    'virtual' => true,
    'help'    => 'Text that appears on every page: footer headings, newsletter box, the donate call-to-action band and common button labels.',
    'nav'     => ['label' => 'Site-wide content', 'order' => 9000],
    'seo'     => ['title' => 'Lumina Africa Foundation'],
    'sections' => [
        [
            'key' => 'footer', 'label' => 'Footer', 'help' => 'Headings and text in the footer on every page.',
            'fields' => [
                ['key' => 'links_title', 'label' => 'Quick links heading', 'type' => 'text', 'default' => 'Quick links'],
                ['key' => 'contact_title', 'label' => 'Contact heading', 'type' => 'text', 'default' => 'Get in touch'],
                ['key' => 'social_title', 'label' => 'Social media heading', 'type' => 'text', 'default' => 'Follow us'],
                ['key' => 'newsletter_title', 'label' => 'Newsletter heading', 'type' => 'text', 'default' => 'Stay updated'],
                ['key' => 'newsletter_text', 'label' => 'Newsletter text', 'type' => 'textarea', 'default' => 'Receive news about our programmes and impact.'],
                ['key' => 'newsletter_placeholder', 'label' => 'Newsletter email placeholder', 'type' => 'text', 'default' => 'Email address'],
                ['key' => 'newsletter_button', 'label' => 'Newsletter button label', 'type' => 'text', 'default' => 'Subscribe'],
                ['key' => 'reg_label', 'label' => 'Registration number label', 'type' => 'text', 'default' => 'Reg. No:', 'help' => 'Shown before the registration number from Settings. Leave empty to hide the number.'],
                ['key' => 'copyright', 'label' => 'Copyright line', 'type' => 'text', 'default' => '© {year} Lumina Africa Foundation. All Rights Reserved.', 'help' => '{year} is replaced by the current year.'],
                ['key' => 'back_to_top', 'label' => 'Back-to-top button label (screen readers)', 'type' => 'text', 'default' => 'Back to top'],
            ],
        ],
        [
            'key' => 'cta', 'label' => 'Donate call-to-action band', 'toggle' => true,
            'help' => 'The navy "support us" band shown near the bottom of many pages.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Help us bridge the digital divide for youth in Northern Uganda'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'Your support helps vulnerable young people gain digital skills, internships and pathways to income.'],
                ['key' => 'primary_label', 'label' => 'Main button label', 'type' => 'text', 'default' => 'Donate to Support Lumina Africa Foundation'],
                ['key' => 'primary_url', 'label' => 'Main button link', 'type' => 'url', 'default' => '', 'help' => 'Leave empty to use the online donation link from Settings.'],
                ['key' => 'secondary_label', 'label' => 'Second button label', 'type' => 'text', 'default' => 'View Available Programs'],
                ['key' => 'secondary_url', 'label' => 'Second button link', 'type' => 'url', 'default' => '/programs'],
            ],
        ],
        [
            'key' => 'labels', 'label' => 'Common button & link labels', 'help' => 'Used by the blog, news, press, program, project, event and activity cards ("Read more"), by the breadcrumbs ("Home") and by screen readers. Buttons that have their own wording (stories, reports, events) are edited on their own pages.',
            'fields' => [
                ['key' => 'submenu_toggle', 'label' => 'Screen-reader name of the "open sub-menu" arrow button', 'type' => 'text', 'default' => 'Show {label} pages', 'help' => '{label} is replaced by the menu item name. Only read aloud by screen readers.'],
                ['key' => 'read_more', 'label' => '"Read more"', 'type' => 'text', 'default' => 'Read more'],
                ['key' => 'home_breadcrumb', 'label' => 'Breadcrumb label for the home page', 'type' => 'text', 'default' => 'Home'],
            ],
        ],
    ],
];
