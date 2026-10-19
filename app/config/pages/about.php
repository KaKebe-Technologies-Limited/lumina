<?php
/**
 * Registry entry: ABOUT PAGE  (OWNER: B1 "web-core"; redesigned by B-INNER-A).
 * Default wording = the live About page (docs/live-snapshot/about.txt). The live site shows the registration number as GSD/R 1234 here
 * and GSD/R 1545 on the home page: both pages now read {reg_number} from Settings so there is ONE value to confirm.
 * The live page also repeats vision/mission in two slightly different versions; the first (under "Who We Are") is used here and the
 * owner should confirm the official wording.
 * The board of directors is NOT typed here: it is shown from Admin > Team (group "Board") as soon as members are added.
 * The figures band reads the stats table (Admin > Stats); nothing numeric is typed on this page.
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 */
$hlHelp = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'about',
    'title' => 'About Us',
    'route' => '/about',
    'help'  => 'Who we are: story, figures, founder quote, vision and mission, core values, governance (board shown from Admin > Team) and location.',
    'nav'   => ['label' => 'About', 'order' => 20, 'header' => true, 'footer' => true],
    'seo'   => [
        'title'       => 'Preparing Communities for Change',
        'description' => 'Lumina Africa Foundation is a registered community-based non-profit in Gulu City, Northern Uganda. Learn about our story, vision, mission, core values and governance.',
        'og_image'    => 'assets/img/site/photo-05.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner', 'help' => 'Page heading and the opening paragraph.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Who We Are'],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'About Lumina [[Africa Foundation]]', 'maxlength' => 140, 'help' => $hlHelp],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3,
                    'default' => 'Lumina Africa Foundation is a registered community-based, non-profit organisation headquartered in Gulu City, Northern Uganda, Reg. No: {reg_number}.',
                    'help' => '{reg_number} is filled from Settings > Organisation > Registration number.'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg',
                    'alt' => 'Young people seated in a training room, one of them typing on a laptop',
                    'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'story', 'label' => 'Our story', 'toggle' => true, 'help' => 'The foundation\'s background, with one or two photos.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the text', 'type' => 'text', 'default' => 'Our story'],
                ['key' => 'title', 'label' => 'Heading (optional)', 'type' => 'text', 'default' => '', 'help' => $hlHelp],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>We were established by a team of young African changemakers committed to addressing one of the region’s most urgent challenges, youth unemployment driven by digital exclusion in a post-conflict environment.</p><p>Northern Uganda endured over two decades of armed conflict that displaced approximately 1.8 million people and disrupted education systems for an entire generation. Many young people today continue to face the long-term consequences of interrupted schooling, limited certification, and restricted access to technology.</p><p>As the world rapidly evolves through digital transformation, the gap between those with access to digital skills and those without continues to widen.</p>'],
                ['key' => 'emphasis', 'label' => 'Highlighted line', 'type' => 'text', 'default' => 'Lumina Africa Foundation exists to [[close that gap]].', 'help' => $hlHelp],
                ['key' => 'image', 'label' => 'Photo (front, tilted)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg',
                    'alt' => 'A trainer points at a screen while young people wearing headphones work at computers'],
                ['key' => 'image2', 'label' => 'Second photo (behind, optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg',
                    'alt' => 'Young people seated in a training room, one of them typing on a laptop'],
            ],
        ],
        [
            'key' => 'impact', 'label' => 'Figures band', 'toggle' => true, 'default_visible' => true,
            'help' => 'The deep navy card with the foundation\'s figures. The numbers come from Admin > Stats (never typed here) and the band disappears while there are none.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the card (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading above the card (optional)', 'type' => 'text', 'default' => '', 'help' => $hlHelp],
            ],
        ],
        [
            'key' => 'quote', 'label' => 'Founder quote', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the quote (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'text', 'label' => 'Quote', 'type' => 'textarea', 'rows' => 5, 'default' => 'Northern Uganda lost years to conflict, but we will not lose our future to digital exclusion. Every young person, regardless of certificates or background, deserves the skills to participate in today’s economy.'],
                ['key' => 'author', 'label' => 'Who said it', 'type' => 'text', 'default' => 'Moses Komakech, Founder'],
                ['key' => 'image', 'label' => 'Portrait (optional)', 'type' => 'image', 'default' => 'assets/img/site/about/founder-portrait.jpg',
                    'alt' => 'Portrait of Moses Komakech, founder of Lumina Africa Foundation, in a blue suit'],
                ['key' => 'cta_label', 'label' => 'Button: text (optional)', 'type' => 'text', 'default' => 'Join Us'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
        [
            'key' => 'vision_mission', 'label' => 'Vision & Mission', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Section heading', 'type' => 'text', 'default' => 'Who We Are', 'help' => $hlHelp],
                ['key' => 'vision_icon', 'label' => 'Vision: icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'compass'],
                ['key' => 'vision_title', 'label' => 'Vision: heading', 'type' => 'text', 'default' => 'Vision'],
                ['key' => 'vision_text', 'label' => 'Vision: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'An inclusive digital future for Northern Uganda where all young people and women are equipped, connected, and empowered to thrive in the global digital economy.'],
                ['key' => 'mission_icon', 'label' => 'Mission: icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'target'],
                ['key' => 'mission_title', 'label' => 'Mission: heading', 'type' => 'text', 'default' => 'Mission'],
                ['key' => 'mission_text', 'label' => 'Mission: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'We aim to strengthen access to learning, improve practical competencies, build confidence and help young people prepare for further education, employment, entrepreneurship and leadership.'],
            ],
        ],
        [
            'key' => 'banner', 'label' => 'Vision & Mission banner (second wording)', 'toggle' => true, 'default_visible' => false,
            'help' => 'The live About page repeats the vision and mission in a navy banner with slightly different wording from the "Who We Are" block above. It is hidden by default so visitors do not see two versions; switch it on, or edit it, once you have chosen the official wording.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the cards', 'type' => 'text', 'default' => 'About Lumina Africa Foundation'],
                ['key' => 'vision_title', 'label' => 'Vision: heading', 'type' => 'text', 'default' => 'Our Vision'],
                ['key' => 'vision_text', 'label' => 'Vision: text', 'type' => 'textarea', 'rows' => 3, 'default' => 'A Northern Uganda where vulnerable youth thrive through digital skills, decent work, and equal opportunity.'],
                ['key' => 'mission_title', 'label' => 'Mission: heading', 'type' => 'text', 'default' => 'Our Mission'],
                ['key' => 'mission_text', 'label' => 'Mission: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'We aim to strengthen access to learning, improve practical competencies, build confidence and help young people prepare for further education, employment, entrepreneurship and leadership.'],
            ],
        ],
        [
            'key' => 'values', 'label' => 'Core values', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Core [[Values]]', 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'items', 'label' => 'Values', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a value', 'max_rows' => 12,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Short text (optional)', 'type' => 'textarea'],
                    ],
                    'default' => [
                        ['icon' => 'handshake', 'title' => 'Equity & Inclusion', 'text' => ''],
                        ['icon' => 'shield', 'title' => 'Integrity & Transparency', 'text' => ''],
                        ['icon' => 'users', 'title' => 'Community-Driven Action', 'text' => ''],
                        ['icon' => 'lightbulb', 'title' => 'Innovation & Sustainability', 'text' => ''],
                        ['icon' => 'globe', 'title' => 'Collaboration & Partnership', 'text' => ''],
                    ],
                ],
            ],
        ],
        [
            'key' => 'governance', 'label' => 'Governance', 'toggle' => true,
            'help' => 'The board members themselves are managed in Admin > Team (group "Board of Directors"); they appear here automatically.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Governance', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'The Foundation is governed by a Board of Directors who provide oversight and strategic direction, supported by program coordinators and volunteers.'],
                ['key' => 'board_title', 'label' => 'Board heading (shown when board members exist)', 'type' => 'text', 'default' => 'Meet Our Board of Directors'],
                ['key' => 'team_cta_label', 'label' => 'Link to the team page: text (shown when the team page has people)', 'type' => 'text', 'default' => 'Meet the whole team'],
                ['key' => 'team_cta_url', 'label' => 'Link to the team page: link', 'type' => 'url', 'default' => '/team'],
            ],
        ],
        [
            'key' => 'location', 'label' => 'Location', 'toggle' => true,
            'help' => 'The address and map come from Settings > Contact details.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Location', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Located in Pece-Laroo Division in Gulu City Council, Lumina Africa Foundation is a growing community-based organisation. Our primary geographic focus is Northern Uganda.'],
                ['key' => 'address_label', 'label' => 'Label before the address', 'type' => 'text', 'default' => 'Address'],
                ['key' => 'map_title', 'label' => 'Map: description for screen readers', 'type' => 'text', 'default' => 'Map showing the location of Lumina Africa Foundation'],
            ],
        ],
        [
            'key' => 'partners', 'label' => 'Our partners', 'toggle' => true, 'default_visible' => true,
            'help' => 'A tidy grid of the partners\' logos at the bottom of the page. The partners themselves (logo, name, website, order, show / hide) are managed in Admin > Partners and appear here at once. The whole section hides itself while there is no active partner.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => '', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Partners', 'maxlength' => 120, 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'We collaborate with NGOs, businesses, academic institutions, and community groups that believe in sustainable development and social impact.'],
                ['key' => 'limit', 'label' => 'Most logos to show here', 'type' => 'number', 'default' => 24, 'min' => 4, 'max' => 60,
                    'help' => 'If you have more partners than this, the rest are on the Partners page (see the link below).'],
                ['key' => 'all_cta_label', 'label' => 'Link to the Partners page: text', 'type' => 'text', 'default' => 'See all partners', 'maxlength' => 60,
                    'help' => 'Empty the text to hide the link.'],
                ['key' => 'all_cta_url', 'label' => 'Link to the Partners page: link', 'type' => 'url', 'default' => '/partners'],
            ],
        ],
    ],
];
