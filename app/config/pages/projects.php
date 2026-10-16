<?php
/**
 * Registry entry: PROJECTS PAGE (list at /projects, detail at /projects/{slug}).
 *
 * CONTENT SOURCE: docs/content-source/planned-programs.md (owner-supplied wording, used verbatim).
 * The "projects" are the 27 PROPOSED AREAS OF INTERVENTION of the three planned program pillars (Admin > Projects, status "planned").
 * They are plans: nothing here may be read as confirmation that an activity is funded, operational or delivering results.
 * This file holds the copy AROUND them (heading, filter labels, status labels, disclaimer, call to action, empty states).
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 *
 * Sections hero, list, filters, disclaimer, cta, detail = the current page. Section approach = LEGACY key of the previous site (describes
 * delivery), kept so no saved edit or older view breaks and HIDDEN by default.
 */
$hlHelp = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'projects',
    'title' => 'Projects',
    'route' => '/projects',
    'help'  => 'Our planned projects: the proposed areas of intervention of the three planned program pillars. Individual projects are edited in Admin > Projects (set the program pillar and the status there).',
    'nav'   => ['label' => 'Projects', 'order' => 40, 'header' => true, 'footer' => true],
    'seo'   => [
        'title'       => 'Our Planned Projects',
        'description' => 'Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Our planned projects'],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Our planned [[interventions]]', 'maxlength' => 140, 'help' => $hlHelp],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Partner With Us'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement', 'help' => 'Leave empty to use the online donation link from Settings > Donations.'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg',
                    'alt' => 'Young people seated at desks in a lecture hall',
                    'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'list', 'label' => 'Projects list', 'help' => 'The projects themselves come from Admin > Projects.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the statement (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'lead', 'label' => 'Statement above the list', 'type' => 'text', 'default' => 'Proposed areas of [[intervention]]', 'help' => $hlHelp],
                ['key' => 'card_cta', 'label' => 'Link text on each project', 'type' => 'text', 'default' => 'Read more'],
                ['key' => 'status_planned', 'label' => 'Status label: planned', 'type' => 'text', 'default' => 'Planned'],
                ['key' => 'status_ongoing', 'label' => 'Status label: ongoing', 'type' => 'text', 'default' => 'Ongoing'],
                ['key' => 'status_completed', 'label' => 'Status label: completed', 'type' => 'text', 'default' => 'Completed'],
                ['key' => 'empty_title', 'label' => 'Message when there are no projects yet: heading', 'type' => 'text', 'default' => 'Our planned projects are being published'],
                ['key' => 'empty_text', 'label' => 'Message when there are no projects yet: text', 'type' => 'textarea', 'default' => 'Please check back soon, or get in touch to learn how you can take part.'],
                ['key' => 'empty_cta_label', 'label' => 'Message when there are no projects yet: button text', 'type' => 'text', 'default' => 'Contact Lumina Africa Foundation'],
                ['key' => 'empty_cta_url', 'label' => 'Message when there are no projects yet: button link', 'type' => 'url', 'default' => '/contact'],
                ['key' => 'filter_empty', 'label' => 'Message when the chosen pillar has no projects', 'type' => 'text', 'default' => 'No proposed areas of intervention have been published for this pillar yet.'],
            ],
        ],
        [
            'key' => 'filters', 'label' => 'Filter by program pillar', 'toggle' => true, 'default_visible' => true,
            'help' => 'Buttons that narrow the list to one program pillar. Match a label to its pillar by the program link (slug).',
            'fields' => [
                ['key' => 'aria_label', 'label' => 'Accessible name of the filter group (not shown)', 'type' => 'text', 'default' => 'Filter by program pillar'],
                ['key' => 'all', 'label' => 'Label of the "show everything" button', 'type' => 'text', 'default' => 'All'],
                ['key' => 'pillars', 'label' => 'Pillar buttons', 'type' => 'repeater', 'item_label' => 'label', 'add_label' => 'Add a pillar button', 'max_rows' => 8,
                    'fields' => [
                        ['key' => 'slug', 'label' => 'Program link (slug of the program in Admin > Programs)', 'type' => 'text', 'maxlength' => 160],
                        ['key' => 'label', 'label' => 'Button text', 'type' => 'text', 'maxlength' => 160],
                    ],
                    'default' => [
                        ['slug' => 'health-community-wellbeing', 'label' => 'Health & Community Wellbeing'],
                        ['slug' => 'education-skills-youth-development', 'label' => 'Education, Skills & Youth Development'],
                        ['slug' => 'technology-digital-inclusion-innovation', 'label' => 'Technology, Digital Inclusion & Innovation'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'disclaimer', 'label' => 'Responsible-development notice',
            'help' => 'Always shown with the list. It says these are planned areas, not confirmed or funded activities; keep it.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our commitment to [[responsible development]]', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Notice', 'type' => 'textarea', 'rows' => 4, 'default' => 'The initiatives described on this page represent our planned program areas and proposed interventions. They should not be interpreted as confirmation that every activity is currently funded, operational or already delivering results.'],
            ],
        ],
        [
            'key' => 'cta', 'label' => 'Call to action (partnerships)', 'toggle' => true, 'default_visible' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Partnerships that can turn plans into [[action]]', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Together, we can help turn these plans into practical opportunities for the communities we serve.'],
                ['key' => 'cta1_label', 'label' => 'Button 1: text', 'type' => 'text', 'default' => 'Discuss a Partnership'],
                ['key' => 'cta1_url', 'label' => 'Button 1: link', 'type' => 'url', 'default' => '/involvement'],
                ['key' => 'cta2_label', 'label' => 'Button 2: text', 'type' => 'text', 'default' => 'Contact Lumina Africa Foundation'],
                ['key' => 'cta2_url', 'label' => 'Button 2: link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'detail', 'label' => 'Single project page', 'help' => 'Labels used on every /projects/{project} page.',
            'fields' => [
                ['key' => 'back_label', 'label' => 'Link back to the list', 'type' => 'text', 'default' => 'Our planned projects'],
                ['key' => 'program_label', 'label' => 'Label before the program name', 'type' => 'text', 'default' => 'Program'],
                ['key' => 'status_label', 'label' => 'Label before the status', 'type' => 'text', 'default' => 'Status'],
                ['key' => 'location_label', 'label' => 'Label before the location', 'type' => 'text', 'default' => 'Location'],
                ['key' => 'dates_label', 'label' => 'Label before the dates', 'type' => 'text', 'default' => 'Dates'],
                ['key' => 'more_title', 'label' => 'Heading above other projects', 'type' => 'text', 'default' => 'Proposed areas of intervention'],
                ['key' => 'join_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Partner With Us'],
                ['key' => 'join_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
        // ------------------------------------------------------------------ LEGACY section (kept for compatibility, see the file header)
        [
            'key' => 'approach', 'label' => 'Legacy: Our Approach', 'toggle' => true, 'default_visible' => false,
            'help' => 'Legacy section from the previous website, hidden by default because it describes delivery. Only switch it on when it is true for the planned projects.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our [[Approach]]', 'help' => $hlHelp],
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
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg', 'alt' => 'A trainer points at a screen while young people wearing headphones work at computers'],
                ['key' => 'image2', 'label' => 'Second photo (behind, optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => 'Young people seated at desks in a lecture hall'],
            ],
        ],
    ],
];
