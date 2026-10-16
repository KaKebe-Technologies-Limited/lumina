<?php
/**
 * Registry entry: PROJECTS PAGE (list at /projects, detail at /projects/{slug})  (OWNER: B1 "web-core").
 * The projects themselves (title, tagline, summary, body, image, status) are managed in Admin > Projects; this file holds the copy AROUND them.
 * Default wording = the live Projects page (docs/live-snapshot/projects.txt).
 */
return [
    'slug'  => 'projects',
    'title' => 'Projects',
    'route' => '/projects',
    'help'  => 'Our projects list and our approach. Individual projects are edited in Admin > Projects.',
    'nav'   => ['label' => 'Projects', 'order' => 40, 'header' => true, 'footer' => true],
    'seo'   => [
        'title'       => 'Empowering Communities Through Innovative Projects',
        'description' => 'Inclusive digital empowerment projects closing the digital access gap in Northern Uganda and opening pathways to income, decent work and self-reliance for youth.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Our Projects', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 5, 'default' => 'Lumina Africa Foundation implements structured and inclusive digital empowerment initiatives aimed at closing the digital access gap in Northern Uganda. Through practical, market-relevant skills training and employment linkages, our projects create tangible pathways to income generation, decent work, and long-term self-reliance for vulnerable youth.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Donate to Support Lumina Africa Foundation'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '', 'help' => 'Leave empty to use the online donation link from Settings > Donations.'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => '', 'help' => 'A wide photo darkened behind the heading. Leave empty for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'list', 'label' => 'Projects list', 'help' => 'The projects themselves come from Admin > Projects.',
            'fields' => [
                ['key' => 'lead', 'label' => 'Statement above the list', 'type' => 'text', 'default' => 'We focus on translating digital access into real economic opportunity.'],
                ['key' => 'card_cta', 'label' => 'Link text on each project', 'type' => 'text', 'default' => 'Read more'],
                ['key' => 'status_planned', 'label' => 'Status label: planned', 'type' => 'text', 'default' => 'Planned'],
                ['key' => 'status_ongoing', 'label' => 'Status label: ongoing', 'type' => 'text', 'default' => 'Ongoing'],
                ['key' => 'status_completed', 'label' => 'Status label: completed', 'type' => 'text', 'default' => 'Completed'],
                ['key' => 'empty_title', 'label' => 'Message when there are no projects yet: heading', 'type' => 'text', 'default' => 'Our projects are being published'],
                ['key' => 'empty_text', 'label' => 'Message when there are no projects yet: text', 'type' => 'textarea', 'default' => 'Please check back soon, or get in touch to learn how you can take part.'],
                ['key' => 'empty_cta_label', 'label' => 'Message when there are no projects yet: button text', 'type' => 'text', 'default' => 'Contact Us'],
                ['key' => 'empty_cta_url', 'label' => 'Message when there are no projects yet: button link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'approach', 'label' => 'Our Approach', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Approach'],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'text', 'default' => 'Across all programs, Lumina Africa Foundation operates with:'],
                ['key' => 'items', 'label' => 'Practices', 'type' => 'repeater', 'item_label' => 'text', 'add_label' => 'Add a practice', 'max_rows' => 12,
                    'fields' => [['key' => 'text', 'label' => 'Practice', 'type' => 'text', 'maxlength' => 200]],
                    'default' => [
                        ['text' => 'Structured monitoring and evaluation systems'],
                        ['text' => 'Clear governance and financial accountability'],
                        ['text' => 'Community-based implementation'],
                        ['text' => 'Partnerships with institutions and private sector actors'],
                    ],
                ],
                ['key' => 'closing', 'label' => 'Closing statement', 'type' => 'textarea', 'rows' => 3, 'default' => 'We believe digital skills are not a luxury. They are a pathway to recovery, employment, and long-term resilience in post-conflict Northern Uganda.'],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => 'Young people seated at desks in a lecture hall'],
            ],
        ],
        [
            'key' => 'detail', 'label' => 'Single project page', 'help' => 'Labels used on every /projects/{project} page.',
            'fields' => [
                ['key' => 'back_label', 'label' => 'Link back to the list', 'type' => 'text', 'default' => 'All projects'],
                ['key' => 'program_label', 'label' => 'Label before the program name', 'type' => 'text', 'default' => 'Program'],
                ['key' => 'status_label', 'label' => 'Label before the status', 'type' => 'text', 'default' => 'Status'],
                ['key' => 'location_label', 'label' => 'Label before the location', 'type' => 'text', 'default' => 'Location'],
                ['key' => 'dates_label', 'label' => 'Label before the dates', 'type' => 'text', 'default' => 'Dates'],
                ['key' => 'more_title', 'label' => 'Heading above other projects', 'type' => 'text', 'default' => 'More projects'],
                ['key' => 'join_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Get Involved'],
                ['key' => 'join_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
    ],
];
