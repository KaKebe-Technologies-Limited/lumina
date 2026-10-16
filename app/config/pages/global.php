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
    'help'    => 'Text that appears on every page: the top bar, footer headings, newsletter box, the donation panel, the donate call-to-action band and common button labels.',
    'nav'     => ['label' => 'Site-wide content', 'order' => 9000],
    'seo'     => ['title' => 'Lumina Africa Foundation'],
    'sections' => [
        [
            'key' => 'footer', 'label' => 'Footer', 'help' => 'Headings and text in the footer on every page.',
            'fields' => [
                ['key' => 'blurb', 'label' => 'Short description under the logo', 'type' => 'textarea', 'rows' => 3, 'default' => 'Lumina Africa Foundation is a registered community-based non-profit in Gulu City, Northern Uganda, bridging the digital divide for vulnerable youth.', 'help' => 'Leave empty to hide it.'],
                ['key' => 'partners_title', 'label' => 'Partners strip heading', 'type' => 'text', 'default' => 'With thanks to our partners', 'help' => 'The strip appears only when partners have been added in Admin > Partners.'],
                ['key' => 'links_title', 'label' => 'Quick links heading', 'type' => 'text', 'default' => 'Quick links'],
                ['key' => 'show_programs', 'label' => 'Show a "programs" column', 'type' => 'boolean', 'default' => true, 'help' => 'Lists the active programs (Admin > Programs).'],
                ['key' => 'programs_title', 'label' => 'Programs column heading', 'type' => 'text', 'default' => 'Our programs'],
                ['key' => 'whatsapp_label', 'label' => 'WhatsApp link text', 'type' => 'text', 'default' => 'WhatsApp'],
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
            'key' => 'topbar', 'label' => 'Top bar (strip above the menu)', 'toggle' => true, 'default_visible' => true,
            'help' => 'A thin gold strip above the header with the address, e-mail and phone from Settings > Contact details, and one quick link.',
            'fields' => [
                ['key' => 'show_address', 'label' => 'Show the address', 'type' => 'boolean', 'default' => true],
                ['key' => 'show_email', 'label' => 'Show the main e-mail', 'type' => 'boolean', 'default' => true],
                ['key' => 'show_phone', 'label' => 'Show the first phone number', 'type' => 'boolean', 'default' => true],
                ['key' => 'link_type', 'label' => 'Quick link on the right', 'type' => 'select', 'options' => ['whatsapp' => 'Chat on WhatsApp (link from Settings)', 'donate' => 'Go to the Donate page', 'none' => 'No quick link'], 'default' => 'whatsapp'],
                ['key' => 'link_label', 'label' => 'Quick link text', 'type' => 'text', 'default' => 'Chat on WhatsApp', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'donate_panel', 'label' => 'Donation panel (amount + buttons)', 'toggle' => true, 'default_visible' => true,
            'help' => 'The deep-navy "make a gift" panel used on the home page and elsewhere. It does NOT take payments: the amount is passed to the Donate page, where the visitor records a pledge, and the second button goes to the online donation link from Settings.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Support our work'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Help us bridge the digital divide for [[youth]]', 'help' => 'Put [[double brackets]] around the words that should get the hand-drawn underline.'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Your support helps vulnerable young people gain digital skills, internships and pathways to income.'],
                ['key' => 'amount_label', 'label' => 'Amount field label', 'type' => 'text', 'default' => 'Your gift'],
                ['key' => 'amount_placeholder', 'label' => 'Amount field placeholder', 'type' => 'text', 'default' => 'Enter an amount'],
                ['key' => 'currency', 'label' => 'Currency', 'type' => 'select', 'options' => ['USD' => 'USD', 'UGX' => 'UGX', 'EUR' => 'EUR', 'GBP' => 'GBP'], 'default' => 'USD', 'help' => 'Shown beside the amount and passed to the pledge form.'],
                ['key' => 'amounts', 'label' => 'Quick amount buttons', 'type' => 'repeater', 'item_label' => 'amount', 'add_label' => 'Add an amount', 'max_rows' => 6,
                    'fields' => [['key' => 'amount', 'label' => 'Amount (number)', 'type' => 'number', 'min' => 1]],
                    'default' => [['amount' => 10], ['amount' => 25], ['amount' => 50], ['amount' => 100]],
                    'help' => 'Suggested amounts in the chosen currency. They are suggestions only, not targets or totals.'],
                ['key' => 'pledge_label', 'label' => 'Pledge button text', 'type' => 'text', 'default' => 'Pledge this amount'],
                ['key' => 'online_label', 'label' => 'Online donation button text', 'type' => 'text', 'default' => 'Donate online', 'help' => 'Goes to the online donation link in Settings > Donations. Hidden when that link is empty.'],
                ['key' => 'note', 'label' => 'Small print under the buttons', 'type' => 'textarea', 'rows' => 2, 'default' => 'A pledge records your intention on the Donate page. No payment is taken on this site.'],
                ['key' => 'image1', 'label' => 'Collage photo 1', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg', 'alt' => 'A trainer points at a screen while young people wearing headphones work at computers'],
                ['key' => 'image2', 'label' => 'Collage photo 2', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg', 'alt' => 'Young people seated in the audience of an event'],
                ['key' => 'image3', 'label' => 'Collage photo 3', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg', 'alt' => 'Students at desks in a classroom, one working on a laptop'],
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
                ['key' => 'menu_open', 'label' => 'Screen-reader name of the menu (hamburger) button', 'type' => 'text', 'default' => 'Menu'],
                ['key' => 'menu_close', 'label' => 'Screen-reader name of the "close menu" button', 'type' => 'text', 'default' => 'Close menu'],
                ['key' => 'drawer_title', 'label' => 'Title at the top of the mobile menu', 'type' => 'text', 'default' => 'Menu'],
                ['key' => 'close', 'label' => '"Close" (video window and image viewer)', 'type' => 'text', 'default' => 'Close'],
                ['key' => 'prev', 'label' => '"Previous" (carousel arrow, screen readers)', 'type' => 'text', 'default' => 'Previous'],
                ['key' => 'next', 'label' => '"Next" (carousel arrow, screen readers)', 'type' => 'text', 'default' => 'Next'],
                ['key' => 'image_viewer', 'label' => 'Screen-reader name of the picture viewer (gallery)', 'type' => 'text', 'default' => 'Image viewer'],
                ['key' => 'prev_image', 'label' => '"Previous image" (picture viewer, screen readers)', 'type' => 'text', 'default' => 'Previous image'],
                ['key' => 'next_image', 'label' => '"Next image" (picture viewer, screen readers)', 'type' => 'text', 'default' => 'Next image'],
                ['key' => 'video_fallback_title', 'label' => 'Screen-reader title of a video window that has no title of its own', 'type' => 'text', 'default' => 'Video'],
                ['key' => 'watch_video', 'label' => 'Default text of a video button', 'type' => 'text', 'default' => 'Watch video'],
                ['key' => 'watch_on_youtube', 'label' => '"Watch on YouTube" (link inside the video window)', 'type' => 'text', 'default' => 'Watch on YouTube'],
                ['key' => 'read_more', 'label' => '"Read more"', 'type' => 'text', 'default' => 'Read more'],
                ['key' => 'home_breadcrumb', 'label' => 'Breadcrumb label for the home page', 'type' => 'text', 'default' => 'Home'],
            ],
        ],
    ],
];
