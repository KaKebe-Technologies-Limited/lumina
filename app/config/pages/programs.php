<?php
/**
 * Registry entry: PROGRAMS PAGE (list at /programs, detail at /programs/{slug})  (OWNER: B1 "web-core"; redesigned by B-INNER-A).
 * The programs themselves (title, summary, body, image) are managed in Admin > Programs; this file holds the copy AROUND them.
 * The "Impact Indicators" of each programme belong in that programme's own text (Admin > Programs > body).
 * Default wording = the live Programs page (docs/live-snapshot/programs.txt).
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 */
$hlHelp = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'programs',
    'title' => 'Programs',
    'route' => '/programs',
    'help'  => 'Our programs list, accountability framework, impact, how we work and FAQ. Individual programs are edited in Admin > Programs.',
    'nav'   => ['label' => 'Programs', 'order' => 30, 'header' => true, 'footer' => true],
    'seo'   => [
        'title'       => 'Empowering Youth and Communities in Northern Uganda',
        'description' => 'Structured, inclusive programs that bridge the digital divide and create real pathways to employment and self-reliance for vulnerable youth in Northern Uganda.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Our [[Programs]]', 'maxlength' => 140, 'help' => $hlHelp],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 4, 'default' => 'Lumina Africa Foundation delivers structured, inclusive programs designed to bridge the digital divide and create real pathways to employment and self-reliance for vulnerable youth in Northern Uganda.'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg',
                    'alt' => 'A trainer points at a screen while young people wearing headphones work at computers',
                    'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'overview', 'label' => 'Programs overview', 'help' => 'The grid of programs (from Admin > Programs).',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Programs [[Overview]]', 'help' => $hlHelp],
                ['key' => 'subtitle', 'label' => 'Sub-heading', 'type' => 'text', 'default' => 'Bridging the Digital Divide for Vulnerable Youth in Northern Uganda'],
                ['key' => 'card_cta', 'label' => 'Card button text', 'type' => 'text', 'default' => 'See Program Details'],
                ['key' => 'empty_title', 'label' => 'Message when there are no programs yet: heading', 'type' => 'text', 'default' => 'Our programs are being published'],
                ['key' => 'empty_text', 'label' => 'Message when there are no programs yet: text', 'type' => 'textarea', 'default' => 'Please check back soon, or get in touch to learn how you can take part.'],
                ['key' => 'empty_cta_label', 'label' => 'Message when there are no programs yet: button text', 'type' => 'text', 'default' => 'Contact Us'],
                ['key' => 'empty_cta_url', 'label' => 'Message when there are no programs yet: button link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'accountability', 'label' => 'Accountability Framework', 'toggle' => true, 'help' => 'Shown on the programs page and on every program page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Accountability [[Framework]]', 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'text', 'default' => 'Across all programs, Lumina Africa Foundation applies:'],
                ['key' => 'items', 'label' => 'Practices', 'type' => 'repeater', 'item_label' => 'text', 'add_label' => 'Add a practice', 'max_rows' => 12,
                    'fields' => [['key' => 'text', 'label' => 'Practice', 'type' => 'text', 'maxlength' => 200]],
                    'default' => [
                        ['text' => 'Baseline and endline assessments'],
                        ['text' => 'Attendance and training hour tracking'],
                        ['text' => 'Structured mentorship documentation'],
                        ['text' => 'Post-program income and employment tracking'],
                        ['text' => 'Quarterly internal performance reviews'],
                    ],
                ],
                ['key' => 'closing', 'label' => 'Closing statement', 'type' => 'text', 'default' => 'We believe impact must be measurable, transparent, and continuously improved.'],
            ],
        ],
        [
            'key' => 'impact', 'label' => 'Our Measurable Impact', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Measurable [[Impact]]', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4, 'default' => 'Lumina Africa Foundation is committed to results-driven implementation. We track progress, measure outcomes, and continuously refine our programs to ensure real economic impact for vulnerable youth in Northern Uganda.'],
                ['key' => 'show_stats', 'label' => 'Show the figures card under the text', 'type' => 'boolean', 'default' => true, 'help' => 'The numbers come from Admin > Stats; the card disappears while there are none.'],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => 'Young people seated at desks in a lecture hall'],
                ['key' => 'image2', 'label' => 'Second photo (behind, optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg', 'alt' => 'Young people seated in a training room, one of them typing on a laptop'],
            ],
        ],
        [
            'key' => 'how_we_work', 'label' => 'How We Work', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'How We [[Work]]', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 5, 'default' => 'Our approach is rooted in community participation, accountability, and collaboration. Every project is designed with the community, not for the community, ensuring solutions are sustainable and owned by the people we serve. By collaborating with partners and local leaders, we maximize our impact and create sustainable systems.'],
                ['key' => 'items', 'label' => 'Principles', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a principle', 'max_rows' => 8,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                    ],
                    'default' => [
                        ['icon' => 'users', 'title' => 'Community-Driven', 'text' => 'Projects are co-created with local leaders, youth, and women.'],
                        ['icon' => 'handshake', 'title' => 'Partnerships', 'text' => 'We collaborate with government, NGOs, schools, and private sector partners.'],
                        ['icon' => 'shield', 'title' => 'Accountability', 'text' => 'Regular monitoring, reporting, and community feedback ensure transparency and measurable impact.'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'faq', 'label' => 'Programs FAQ', 'toggle' => true,
            'help' => 'The questions and answers shown on the programs page, next to a photo.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Programs [[FAQ]]', 'help' => $hlHelp],
                ['key' => 'image', 'label' => 'Photo beside the questions (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg', 'alt' => 'Young people seated in the audience of an event'],
                ['key' => 'items', 'label' => 'Questions', 'type' => 'repeater', 'item_label' => 'question', 'add_label' => 'Add a question', 'max_rows' => 20,
                    'fields' => [
                        ['key' => 'question', 'label' => 'Question', 'type' => 'text', 'maxlength' => 300],
                        ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 4],
                    ],
                    'default' => [
                        ['question' => 'What programs do you offer?', 'answer' => 'We offer programs in education, health, technology, and entrepreneurship aimed at youth empowerment and community development.'],
                        ['question' => 'How can I get involved?', 'answer' => 'You can get involved by volunteering, participating in our programs, or supporting our initiatives through donations.'],
                        ['question' => 'Who can participate?', 'answer' => 'Our programs are open to all young people and marginalized community members seeking personal growth and empowerment.'],
                        ['question' => 'Do you provide mentorship?', 'answer' => 'Yes, we offer mentorship programs specifically designed for youth and women to foster personal and professional growth.'],
                        ['question' => 'What is your mission?', 'answer' => 'Our mission is to reduce youth unemployment in Northern Uganda by equipping vulnerable young people with market-relevant digital and employable skills, providing psychosocial support, and creating pathways to decent work and sustainable livelihoods.'],
                        ['question' => 'Where are you located?', 'answer' => 'We are based in northern Uganda, serving local communities and focusing on sustainable development initiatives.'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'detail', 'label' => 'Single program page', 'help' => 'Labels used on every /programs/{program} page.',
            'fields' => [
                ['key' => 'back_label', 'label' => 'Link back to the list', 'type' => 'text', 'default' => 'All programs'],
                ['key' => 'related_title', 'label' => 'Heading above related projects', 'type' => 'text', 'default' => 'Projects under this program'],
                ['key' => 'related_cta', 'label' => 'Related project link text', 'type' => 'text', 'default' => 'View project'],
                ['key' => 'more_title', 'label' => 'Heading above other programs', 'type' => 'text', 'default' => 'More programs'],
                ['key' => 'join_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Get Involved'],
                ['key' => 'join_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
    ],
];
