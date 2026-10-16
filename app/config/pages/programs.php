<?php
/**
 * Registry entry: PROGRAMS PAGE (list at /programs, detail at /programs/{slug}).
 *
 * CONTENT SOURCE: docs/content-source/planned-programs.md (owner-supplied wording, used verbatim). Every default below is that wording.
 * These programs are PLANNED program areas: nothing on this page may be read as confirmation that an activity is funded, operational or
 * delivering results (see the "commitment" section). Do not add claims of delivery here.
 *
 * The three pillars themselves (title, planned focus, summary, body, photo, status) are rows of `programs` (Admin > Programs);
 * their 27 "proposed areas of intervention" are rows of `projects` (Admin > Projects). This file holds the copy AROUND them.
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 *
 * Sections hero, pillars, cross_cutting, approach, audience, partnerships, commitment, labels, detail = the planned-programs page.
 * (The sections of the previous site that described delivery - overview, accountability, impact, how_we_work, faq - were removed.)
 */
$hlHelp = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'programs',
    'title' => 'Programs',
    'route' => '/programs',
    'help'  => 'Our planned programs: the three program pillars, cross-cutting priorities, implementation approach, who we intend to serve, partnerships and our commitment to responsible development. The pillars are edited in Admin > Programs and their proposed areas of intervention in Admin > Projects.',
    'nav'   => ['label' => 'Programs', 'order' => 30, 'header' => true, 'footer' => true],
    'seo'   => [
        'title'       => 'Our Planned Programs',
        'description' => 'Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner',
            'help' => 'The page heading is "title" + "title2" (two display lines). The three paragraphs and the vision sentence sit under it.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Our planned programs'],
                ['key' => 'title', 'label' => 'Main heading (first line)', 'type' => 'text', 'default' => 'Building [[Pathways to Opportunity]].', 'maxlength' => 140, 'help' => $hlHelp],
                ['key' => 'title2', 'label' => 'Main heading (second line)', 'type' => 'text', 'default' => 'Preparing Communities for Change.', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro paragraph 1', 'type' => 'textarea', 'rows' => 3, 'default' => 'Lumina Africa Foundation is developing a portfolio of community-centred programs designed to expand opportunities for underserved communities, particularly young people in Northern Uganda.'],
                ['key' => 'lead2', 'label' => 'Intro paragraph 2', 'type' => 'textarea', 'rows' => 4, 'default' => 'Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation. Through these areas, we aspire to promote access to reliable information, strengthen practical skills, encourage innovation and help communities build the knowledge and capacity needed for sustainable development.'],
                ['key' => 'lead3', 'label' => 'Intro paragraph 3', 'type' => 'textarea', 'rows' => 3, 'default' => 'As a growing community-based organisation, we are working towards establishing the partnerships, resources and implementation frameworks necessary to translate these plans into meaningful community impact.'],
                ['key' => 'vision', 'label' => 'Vision sentence', 'type' => 'textarea', 'rows' => 2, 'default' => 'Our vision is to turn potential into opportunity by creating pathways for individuals and communities to learn, grow, innovate and thrive.'],
                ['key' => 'cta1_label', 'label' => 'Button 1: text', 'type' => 'text', 'default' => 'Partner With Us'],
                ['key' => 'cta1_url', 'label' => 'Button 1: link', 'type' => 'url', 'default' => '/involvement'],
                ['key' => 'cta2_label', 'label' => 'Button 2: text', 'type' => 'text', 'default' => 'Explore Our Planned Programs'],
                ['key' => 'cta2_url', 'label' => 'Button 2: link', 'type' => 'url', 'default' => '#pillars'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg',
                    'alt' => 'A trainer points at a screen while young people wearing headphones work at computers',
                    'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'pillars', 'label' => 'Our three planned program pillars', 'help' => 'Heading above the three pillar cards (the pillars come from Admin > Programs).',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our three planned program [[pillars]]', 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro under the heading (optional)', 'type' => 'textarea', 'rows' => 3, 'default' => ''],
                ['key' => 'card_cta', 'label' => 'Pillar card button text', 'type' => 'text', 'default' => 'View pillar'],
                ['key' => 'empty_title', 'label' => 'Message when there are no programs yet: heading', 'type' => 'text', 'default' => 'Our planned programs are being published'],
                ['key' => 'empty_text', 'label' => 'Message when there are no programs yet: text', 'type' => 'textarea', 'default' => 'Please check back soon, or get in touch to learn how you can take part.'],
                ['key' => 'empty_cta_label', 'label' => 'Message when there are no programs yet: button text', 'type' => 'text', 'default' => 'Contact Lumina Africa Foundation'],
                ['key' => 'empty_cta_url', 'label' => 'Message when there are no programs yet: button link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'cross_cutting', 'label' => 'Our cross-cutting priorities', 'toggle' => true, 'default_visible' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our cross-cutting [[priorities]]', 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'These priorities are intended to guide the design and future implementation of our programs across all three pillars.'],
                ['key' => 'items', 'label' => 'Priorities', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a priority', 'max_rows' => 12,
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'maxlength' => 160],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3],
                    ],
                    'default' => [
                        ['title' => 'Youth Empowerment', 'text' => 'We intend to place young people at the centre of opportunity creation, supporting their development as professionals, entrepreneurs, innovators, leaders and active community participants.'],
                        ['title' => 'Employability & Economic Empowerment', 'text' => 'We plan to connect learning with practical skills, career guidance, workplace exposure, entrepreneurship and pathways towards sustainable livelihoods.'],
                        ['title' => 'Entrepreneurship & Innovation', 'text' => 'We aim to encourage creativity, initiative and problem-solving while exploring opportunities for young people to develop ideas that respond to social and economic challenges.'],
                        ['title' => 'Gender Equality & Young Women\'s Empowerment', 'text' => 'We intend to promote equitable access to education, technology, mentorship, leadership and economic opportunities for girls and young women.'],
                        ['title' => 'Inclusion & Equal Opportunity', 'text' => 'We seek to develop approaches that consider the needs of underserved groups, including vulnerable children, out-of-school youth, young people with limited resources and persons with disabilities.'],
                        ['title' => 'Leadership, Mentorship & Civic Participation', 'text' => 'We aspire to support young people in developing the knowledge, confidence and responsibility needed to contribute positively to their communities.'],
                        ['title' => 'Community Resilience & Sustainable Development', 'text' => 'We intend to prioritise locally informed solutions that respond to community needs, strengthen local capacity and support sustainable development.'],
                        ['title' => 'Partnerships & Collaboration', 'text' => 'We aim to establish meaningful partnerships with government institutions, civil society organisations, schools, universities, healthcare providers, businesses, technology companies and development partners.'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'approach', 'label' => 'Our proposed implementation approach', 'toggle' => true, 'default_visible' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our proposed implementation [[approach]]', 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'We intend to use a community-centred approach to move from identifying needs to designing, delivering and evaluating interventions.'],
                ['key' => 'items', 'label' => 'Steps', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a step', 'max_rows' => 10,
                    'fields' => [
                        ['key' => 'number', 'label' => 'Number', 'type' => 'text', 'maxlength' => 8],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'maxlength' => 160],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3],
                    ],
                    'default' => [
                        ['number' => '01', 'title' => 'Listen & Assess', 'text' => 'Engage communities, young people and relevant stakeholders to understand local needs, barriers and opportunities.'],
                        ['number' => '02', 'title' => 'Design & Collaborate', 'text' => 'Develop context-appropriate program proposals with clear objectives, activities, budgets, responsibilities and expected results.'],
                        ['number' => '03', 'title' => 'Mobilise Resources & Partnerships', 'text' => 'Seek funding, technical expertise, equipment, institutional support and implementation partnerships needed to deliver planned activities.'],
                        ['number' => '04', 'title' => 'Implement & Support', 'text' => 'Subject to resources and partnerships, deliver relevant training, mentorship, awareness activities, practical learning and community initiatives.'],
                        ['number' => '05', 'title' => 'Monitor, Learn & Improve', 'text' => 'Establish appropriate monitoring and evaluation systems to assess implementation, document progress and identify areas for improvement.'],
                        ['number' => '06', 'title' => 'Strengthen Sustainability', 'text' => 'Explore opportunities to build local ownership, develop institutional relationships and expand interventions where evidence, capacity and resources support further implementation.'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'audience', 'label' => 'Who we intend to serve', 'toggle' => true, 'default_visible' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Who we intend to [[serve]]', 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Our planned programs are being designed with the needs of the following groups in mind:'],
                ['key' => 'items', 'label' => 'Groups', 'type' => 'repeater', 'item_label' => 'text', 'add_label' => 'Add a group', 'max_rows' => 14,
                    'fields' => [['key' => 'text', 'label' => 'Group', 'type' => 'text', 'maxlength' => 300]],
                    'default' => [
                        ['text' => 'Children and vulnerable learners facing barriers to education.'],
                        ['text' => 'Young people aged 15–35 seeking skills, mentorship and development opportunities.'],
                        ['text' => 'Out-of-school youth and young people with limited access to digital learning.'],
                        ['text' => 'Unemployed and underemployed young people seeking pathways to work.'],
                        ['text' => 'Girls and young women pursuing education, leadership and economic participation.'],
                        ['text' => 'Persons with disabilities and other underserved groups.'],
                        ['text' => 'Community members who may benefit from health awareness and educational initiatives.'],
                        ['text' => 'Schools, community organisations and local institutions interested in collaborative development.'],
                    ],
                ],
                ['key' => 'geographic', 'label' => 'Geographic focus paragraph', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our primary geographic focus is Northern Uganda. Specific implementation locations will be determined by community needs, organisational capacity, available resources and partnerships.'],
            ],
        ],
        [
            'key' => 'partnerships', 'label' => 'Partnerships that can turn plans into action', 'toggle' => true, 'default_visible' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Partnerships that can turn plans into [[action]]', 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro sentence 1', 'type' => 'textarea', 'rows' => 2, 'default' => 'We recognise that translating planned programs into meaningful outcomes requires collaboration, resources and shared commitment.'],
                ['key' => 'list_intro', 'label' => 'Sentence introducing the list', 'type' => 'textarea', 'rows' => 2, 'default' => 'Lumina Africa Foundation welcomes conversations with organisations and individuals interested in helping develop our proposed interventions through:'],
                ['key' => 'items', 'label' => 'Ways to help', 'type' => 'repeater', 'item_label' => 'text', 'add_label' => 'Add a way to help', 'max_rows' => 14,
                    'fields' => [['key' => 'text', 'label' => 'Way to help', 'type' => 'text', 'maxlength' => 300]],
                    'default' => [
                        ['text' => 'Program funding and grants.'],
                        ['text' => 'Computers, digital equipment and learning resources.'],
                        ['text' => 'Technical expertise and professional mentorship.'],
                        ['text' => 'Health education partnerships and qualified service providers.'],
                        ['text' => 'Training facilities and educational support.'],
                        ['text' => 'Internship placements and workplace exposure.'],
                        ['text' => 'Research, monitoring and evaluation.'],
                        ['text' => 'Innovation, entrepreneurship and youth development initiatives.'],
                        ['text' => 'Institutional collaboration and community outreach.'],
                    ],
                ],
                ['key' => 'closing', 'label' => 'Closing sentence 1', 'type' => 'textarea', 'rows' => 2, 'default' => 'We are particularly interested in working with partners who share our commitment to inclusion, youth empowerment, education, community wellbeing and sustainable development.'],
                ['key' => 'closing2', 'label' => 'Closing sentence 2', 'type' => 'textarea', 'rows' => 2, 'default' => 'Together, we can help turn these plans into practical opportunities for the communities we serve.'],
                ['key' => 'cta1_label', 'label' => 'Button 1: text', 'type' => 'text', 'default' => 'Discuss a Partnership'],
                ['key' => 'cta1_url', 'label' => 'Button 1: link', 'type' => 'url', 'default' => '/involvement'],
                ['key' => 'cta2_label', 'label' => 'Button 2: text', 'type' => 'text', 'default' => 'Contact Lumina Africa Foundation'],
                ['key' => 'cta2_url', 'label' => 'Button 2: link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'commitment', 'label' => 'Our commitment to responsible development',
            'help' => 'Always shown. The second paragraph explains that the programs are planned; keep it.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our commitment to [[responsible development]]', 'help' => $hlHelp],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>As we build our organisation, we are committed to developing programs around genuine community needs, realistic implementation plans, responsible resource management and measurable objectives.</p><p>The initiatives described on this page represent our planned program areas and proposed interventions. They should not be interpreted as confirmation that every activity is currently funded, operational or already delivering results.</p><p>As programs are developed and implemented, we intend to communicate their status, objectives, progress and verified outcomes transparently.</p>'],
                ['key' => 'signoff_name', 'label' => 'Sign-off: name', 'type' => 'text', 'default' => 'Lumina Africa Foundation'],
                ['key' => 'signoff_tagline', 'label' => 'Sign-off: tagline', 'type' => 'text', 'default' => 'Igniting Light. Inspiring Change.'],
            ],
        ],
        [
            'key' => 'labels', 'label' => 'Labels (status, headings used on cards and pillar pages)',
            'help' => 'Short labels shown on the program cards and pages.',
            'fields' => [
                ['key' => 'status_label', 'label' => 'Label before the status', 'type' => 'text', 'default' => 'Status'],
                ['key' => 'status_planned', 'label' => 'Status label: planned', 'type' => 'text', 'default' => 'Planned program area'],
                ['key' => 'status_ongoing', 'label' => 'Status label: ongoing', 'type' => 'text', 'default' => 'Ongoing'],
                ['key' => 'status_completed', 'label' => 'Status label: completed', 'type' => 'text', 'default' => 'Completed'],
                ['key' => 'planned_focus', 'label' => 'Label before the planned focus sentence', 'type' => 'text', 'default' => 'Planned focus'],
                ['key' => 'interventions_heading', 'label' => 'Heading above the proposed areas of intervention', 'type' => 'text', 'default' => 'Proposed areas of intervention'],
                ['key' => 'view_pillar', 'label' => 'Link text: open a pillar', 'type' => 'text', 'default' => 'View pillar'],
                ['key' => 'view_interventions', 'label' => 'Link text: see a pillar\'s proposed areas of intervention', 'type' => 'text', 'default' => 'View interventions'],
                ['key' => 'interventions_empty', 'label' => 'Message when a pillar has no areas of intervention yet', 'type' => 'text', 'default' => 'No proposed areas of intervention have been published for this pillar yet.'],
            ],
        ],
        [
            'key' => 'detail', 'label' => 'Single program (pillar) page', 'help' => 'Labels used on every /programs/{program} page.',
            'fields' => [
                ['key' => 'back_label', 'label' => 'Link back to the list', 'type' => 'text', 'default' => 'Our planned programs'],
                ['key' => 'related_title', 'label' => 'Heading above the pillar\'s projects', 'type' => 'text', 'default' => 'Proposed areas of intervention'],
                ['key' => 'related_cta', 'label' => 'Related project link text', 'type' => 'text', 'default' => 'View project'],
                ['key' => 'more_title', 'label' => 'Heading above other programs', 'type' => 'text', 'default' => 'Our three planned program pillars'],
                ['key' => 'join_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Partner With Us'],
                ['key' => 'join_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
    ],
];
