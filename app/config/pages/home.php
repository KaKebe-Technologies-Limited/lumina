<?php
/**
 * Registry entry: HOME PAGE.
 * Every heading, paragraph, button (label + link), image (+ alt text), repeating list, section toggle and the SEO defaults of the
 * home page are declared here, so the owner can edit all of it in Admin > Pages > Home without touching code.
 * DEFAULT WORDING = the live site https://www.luminaafricafoundation.com/ (docs/live-snapshot/home.txt). Where a heading had to be
 * written for the new layout it is built only from live wording (hero, programs, vision, mission, involvement page).
 *
 * Sections, in the order they appear on the page (each has an on/off switch). The home page is a page of HIGHLIGHTS that link to the detail pages:
 *   hero > quick links > statistics > program highlights > projects in focus > LAF Academy > opportunities > get involved > about us (short)
 *   > impact stories > newsletter > contact.   The long narrative sections at the end (challenge, why digital inclusion, founder story, commitment, background)
 *   are shown on the About page.
 *
 * Text fields accept [[double brackets]] around ONE phrase to give it the hand-drawn gold/orange underline (headings only).
 * Tokens usable in text fields: {reg_number} {site_name} {founder_name} {city} (filled from Settings; see HomeSupport::fill()).
 * Data that is NOT copy lives elsewhere: statistics (Admin > Stats), programs/projects (Admin > Programs / Projects), impact stories,
 * FAQs, gallery photos, contact details (Settings). Those blocks hide themselves when they have no rows.
 */

$hl = 'Put [[double brackets]] around the words that should get the hand-drawn underline.';

return [
    'slug'  => 'home',
    'title' => 'Home',
    'route' => '/',
    'help'  => 'The landing page of highlights: hero with video, quick links, statistics, programs and projects in focus, the LAF Academy, opportunities, ways to get involved, a short About block, impact stories and contact. The longer story sections are shown on the About page.',
    'nav'   => ['label' => 'Home', 'order' => 10, 'header' => true, 'footer' => false],
    'seo'   => [
        'title'       => 'Building Pathways to Opportunity',
        'description' => 'Lumina Africa Foundation: a community-based non-profit in Gulu City, Northern Uganda, developing community-centred programs for underserved communities.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        // ------------------------------------------------------------------ 1. hero
        [
            'key' => 'hero', 'label' => '1. Hero banner', 'toggle' => true, 'default_visible' => true,
            'help' => 'Full-width photo with the main heading, two buttons and a video button. If you hide it, the heading is kept for screen readers and search engines.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Welcome to the Lumina Africa Foundation', 'maxlength' => 120],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Building Pathways to Opportunity. Preparing [[Communities]] for Change.', 'maxlength' => 160, 'help' => $hl],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 4,
                    'default' => 'Lumina Africa Foundation is a registered community-based, non-profit organisation headquartered in Gulu City, Northern Uganda, Reg. No: {reg_number}. We are developing a portfolio of community-centred programs designed to expand opportunities for underserved communities, particularly young people in Northern Uganda.',
                    'help' => '{reg_number} is filled from Settings > Organisation > Registration number. Remove the "Reg. No" part if you do not want it shown.'],
                ['key' => 'cta1_label', 'label' => 'First button (orange): text', 'type' => 'text', 'default' => 'Explore Our Programs'],
                ['key' => 'cta1_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '/programs'],
                ['key' => 'cta2_label', 'label' => 'Extra button (glass): text (optional)', 'type' => 'text', 'default' => 'Learn free at the Academy', 'help' => 'Leave empty for the cleaner two-button hero (orange button + video button).'],
                ['key' => 'cta2_url', 'label' => 'Extra button: link', 'type' => 'url', 'default' => '/academy'],
                ['key' => 'video_url', 'label' => 'Video: YouTube link', 'type' => 'url', 'default' => 'https://www.youtube.com/watch?v=DpptGd72fwc', 'help' => 'A youtube.com or youtu.be link. The video button opens a pop-up player; nothing is loaded from YouTube until a visitor presses it. Leave empty to hide the video button.'],
                ['key' => 'video_label', 'label' => 'Video: button text', 'type' => 'text', 'default' => 'Watch video'],
                ['key' => 'video_title', 'label' => 'Video: title', 'type' => 'text', 'default' => 'Driving Digital Inclusion in Africa | Orange’s Vision at Africa Tech Festival 2024', 'help' => 'Shown above the player and read by screen readers.'],
                ['key' => 'image', 'label' => 'Hero photo', 'type' => 'image', 'default' => 'assets/img/site/photo-hero-wide.jpg',
                    'alt' => 'Pupils in blue school uniforms seated along rows of desks with laptops in a classroom',
                    'help' => 'A wide photo (at least 1600 px) works best; the left side sits under a dark gradient so the heading stays readable. The default photo was supplied without a licence note and originally carried a third-party banner along its bottom edge (framed out of this crop): replace it with the foundation\'s own photograph when you can.'],
                ['key' => 'image_focus', 'label' => 'Which part of the photo to keep when it is cropped', 'type' => 'select', 'default' => '80% 80%',
                    'options' => ['80% 15%' => 'Top', '80% 45%' => 'Upper middle', '80% 65%' => 'Centre', '80% 80%' => 'Lower middle', '80% 100%' => 'Bottom'],
                    'help' => 'Keep faces inside the visible part: choose where the people are in your photo.'],
            ],
        ],
        // ------------------------------------------------------------------ 2. quick links (right under the hero)
        [
            'key' => 'quick', 'label' => '2. Quick links (four tiles under the hero)', 'toggle' => true, 'default_visible' => true,
            'help' => 'Four large tiles that send visitors to the most useful pages: the Academy, opportunities, programs and ways to get involved.',
            'fields' => [
                ['key' => 'items', 'label' => 'Tiles', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a tile', 'max_rows' => 4,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'One line', 'type' => 'textarea', 'rows' => 2],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url'],
                    ],
                    'default' => [
                        ['icon' => 'book', 'title' => 'LAF Academy', 'text' => 'Free ICT courses, quizzes, challenges and certificates.', 'url' => '/academy'],
                        ['icon' => 'award', 'title' => 'Opportunities', 'text' => 'Scholarships, fellowships, jobs and grants for young people.', 'url' => '/opportunities'],
                        ['icon' => 'target', 'title' => 'Our programs', 'text' => 'Climate, health, education, technology and community development.', 'url' => '/programs'],
                        ['icon' => 'heart', 'title' => 'Get involved', 'text' => 'Volunteer, partner with us or support the work.', 'url' => '/involvement'],
                    ]],
            ],
        ],
        [
            'key' => 'stats', 'label' => '2b. Statistics card (right under the quick links)', 'toggle' => true, 'default_visible' => true,
            'help' => 'The deep navy card with big numbers. The numbers come from Admin > Stats (up to four are shown); the list below is only used until you add statistics there. Nothing is shown when there are none.',
            'fields' => [
                ['key' => 'items', 'label' => 'Fallback statistics (used only while Admin > Stats is empty)', 'type' => 'repeater', 'item_label' => 'label', 'add_label' => 'Add a statistic', 'max_rows' => 4,
                    'fields' => [
                        ['key' => 'value', 'label' => 'Number', 'type' => 'number', 'min' => 0],
                        ['key' => 'prefix', 'label' => 'Before the number (e.g. UGX)', 'type' => 'text', 'maxlength' => 10],
                        ['key' => 'suffix', 'label' => 'After the number (e.g. +)', 'type' => 'text', 'maxlength' => 10],
                        ['key' => 'label', 'label' => 'Label under the number', 'type' => 'text', 'maxlength' => 120],
                    ],
                    'default' => [
                        ['value' => 150, 'prefix' => '', 'suffix' => '+', 'label' => 'Youth Trained'],
                        ['value' => 5, 'prefix' => '', 'suffix' => '', 'label' => 'Active Programs'],
                    ],
                ],
            ],
        ],
        // ------------------------------------------------------------------ 2c. the four focus areas (the foundation's programme template)
        [
            'key' => 'focus', 'label' => '2c. Our four focus areas (the programme ribbons)', 'toggle' => true, 'default_visible' => true,
            'help' => 'The four focus areas of the foundation as numbered colour ribbons (Health, Education, Technology & Innovation, Empowered Communities). Colours follow the order of the rows: red, green, gold, purple. Add a link to send visitors to the matching programs.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => 'What we do'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our four [[focus areas]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => 'Everything we do serves one of four goals, so that every community can grow stronger.'],
                ['key' => 'label', 'label' => 'Word on each ribbon', 'type' => 'text', 'default' => 'Focus area'],
                ['key' => 'items', 'label' => 'Focus areas', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a focus area', 'max_rows' => 6,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'One line', 'type' => 'textarea', 'rows' => 2],
                        ['key' => 'url', 'label' => 'Link (optional)', 'type' => 'url'],
                    ],
                    'default' => [
                        ['icon' => 'heart', 'title' => 'Health', 'text' => 'Healthier people, stronger communities.', 'url' => '/programs'],
                        ['icon' => 'book-open', 'title' => 'Education', 'text' => 'Brighter minds, bigger opportunities.', 'url' => '/programs'],
                        ['icon' => 'lightbulb', 'title' => 'Technology & Innovation', 'text' => 'Digital skills for a brighter tomorrow.', 'url' => '/academy'],
                        ['icon' => 'users', 'title' => 'Empowered Communities', 'text' => 'Inclusive, resilient and sustainable Africa.', 'url' => '/programs'],
                    ]],
                ['key' => 'tagline', 'label' => 'Line under the ribbons (optional)', 'type' => 'text', 'default' => 'Igniting Light. Inspiring Change.'],
            ],
        ],
        // ------------------------------------------------------------------ 3. what we do (program highlights)  [the cards come from Admin > Programs]
        [
            'key' => 'programs', 'label' => '3. What we do (program highlights)', 'toggle' => true, 'default_visible' => true,
            'help' => 'A grid of the active programs from Admin > Programs: photo, one line and a link to each program page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'What we do'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our program [[highlights]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Community-centred programs that open pathways to learning, work and resilience for young people and communities in Northern Uganda.'],
                ['key' => 'limit', 'label' => 'How many programs to show', 'type' => 'number', 'default' => 6, 'min' => 1, 'max' => 12],
                ['key' => 'card_cta', 'label' => 'Card button text', 'type' => 'text', 'default' => 'Learn more'],
                ['key' => 'cta_label', 'label' => 'Button below the cards: text (optional)', 'type' => 'text', 'default' => 'See all programs'],
                ['key' => 'cta_url', 'label' => 'Button below the cards: link', 'type' => 'url', 'default' => '/programs'],
            ],
        ],
        // ------------------------------------------------------------------ 3b. projects in focus
        [
            'key' => 'projects', 'label' => '3b. Projects in focus', 'toggle' => true, 'default_visible' => true,
            'help' => 'The featured projects from Admin > Projects (a project that mirrors a program is shown with the programs, not here).',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Projects in focus'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Where our plans take [[shape]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Specific projects that turn each program into practical activities for young people and communities.'],
                ['key' => 'limit', 'label' => 'How many projects to show', 'type' => 'number', 'default' => 6, 'min' => 1, 'max' => 8, 'help' => 'Taken from every program in turn, so each program is represented.'],
                ['key' => 'card_cta', 'label' => 'Link text on each project', 'type' => 'text', 'default' => 'Learn more'],
                ['key' => 'cta_label', 'label' => 'Button below the projects: text (optional)', 'type' => 'text', 'default' => 'See all projects'],
                ['key' => 'cta_url', 'label' => 'Button below the cards: link', 'type' => 'url', 'default' => '/projects'],
                ['key' => 'items', 'label' => 'Fallback cards (used only while Admin > Programs and Projects are empty)', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a card', 'max_rows' => 8,
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'subtitle', 'label' => 'Small heading', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url'],
                        ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                    ],
                    'default' => [],
                ],
            ],
        ],
        // ------------------------------------------------------------------ 4. LAF Academy highlight
        [
            'key' => 'academy', 'label' => '4. LAF Academy highlight', 'toggle' => true, 'default_visible' => true,
            'help' => 'A band that introduces the free learning portal, with live numbers and shortcuts to its main pages. Hidden while the Academy is switched off.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'LAF Academy'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Learn ICT skills for [[free]]', 'help' => $hl],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Free online courses from computer basics to cloud, data and cybersecurity. Join live virtual classes, take part in quiz challenges and hackathons, and earn certificates you can share with employers.'],
                ['key' => 'cta_label', 'label' => 'First button (orange): text', 'type' => 'text', 'default' => 'Start learning free'],
                ['key' => 'cta_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '/academy'],
                ['key' => 'cta2_label', 'label' => 'Second button (white): text (optional)', 'type' => 'text', 'default' => 'Join a challenge'],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '/academy/challenges'],
                ['key' => 'links', 'label' => 'Shortcut chips', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a shortcut', 'max_rows' => 6,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url'],
                    ],
                    'default' => [
                        ['icon' => 'book', 'title' => 'All free courses', 'url' => '/academy#courses'],
                        ['icon' => 'target', 'title' => 'Challenges and hackathons', 'url' => '/academy/challenges'],
                        ['icon' => 'award', 'title' => 'Winners of the month', 'url' => '/academy/winners'],
                        ['icon' => 'users', 'title' => 'Become a volunteer facilitator', 'url' => '/academy/facilitate'],
                    ]],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg',
                    'alt' => 'Young people seated in a training room, one of them typing on a laptop', 'help' => 'Darkened behind the text.'],
            ],
        ],
        // ------------------------------------------------------------------ 5. opportunities teaser
        [
            'key' => 'opportunities', 'label' => '5. Opportunities for young people', 'toggle' => true, 'default_visible' => true,
            'help' => 'The newest open opportunities from Admin > Opportunities (scholarships, fellowships, jobs ...). Hidden while there are none.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Opportunities'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Opportunities for [[young people]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Scholarships, fellowships, internships, grants and jobs we found for you. Check the deadline and apply early.'],
                ['key' => 'limit', 'label' => 'How many to show', 'type' => 'number', 'default' => 3, 'min' => 1, 'max' => 6],
                ['key' => 'cta_label', 'label' => 'Button below the list: text', 'type' => 'text', 'default' => 'See all opportunities'],
                ['key' => 'cta_url', 'label' => 'Button below the list: link', 'type' => 'url', 'default' => '/opportunities'],
            ],
        ],
        // ------------------------------------------------------------------ 6. get involved
        [
            'key' => 'involved', 'label' => '6. Get involved (three cards)', 'toggle' => true, 'default_visible' => true,
            'help' => 'Three cards that invite visitors to volunteer, partner or donate.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Get involved'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Together, we can turn plans into [[practical opportunities]]', 'help' => $hl],
                ['key' => 'items', 'label' => 'Cards', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a card', 'max_rows' => 4,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3],
                        ['key' => 'button', 'label' => 'Button text', 'type' => 'text'],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url'],
                    ],
                    'default' => [
                        ['icon' => 'users', 'title' => 'Volunteer', 'text' => 'Give your time and skills to mentor, teach, organise or support community activities.', 'button' => 'Volunteer with us', 'url' => '/involvement?type=volunteer#join'],
                        ['icon' => 'globe', 'title' => 'Partner', 'text' => 'Schools, businesses and organisations: work with us to reach more young people.', 'button' => 'Partner with us', 'url' => '/involvement?type=partner#join'],
                        ['icon' => 'heart', 'title' => 'Donate', 'text' => 'Support learning, mentorship and digital skills for young people in Northern Uganda.', 'button' => 'Give a gift', 'url' => '/donate'],
                    ]],
            ],
        ],
        // ------------------------------------------------------------------ 5. vision & mission
        [
            'key' => 'vision_mission', 'label' => '7. About us block (vision, mission and founder quote, with links to the About page)', 'toggle' => true, 'default_visible' => true,
            'help' => 'A short introduction to who we are: photo collage, the vision and mission cards, the founder quote and two buttons that lead to the About and Team pages. The longer story is on the About page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'About Lumina Africa Foundation'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our [[vision]] and mission', 'help' => $hl],
                ['key' => 'vision_title', 'label' => 'Vision: heading', 'type' => 'text', 'default' => 'Our Vision'],
                ['key' => 'vision_text', 'label' => 'Vision: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'An inclusive digital future for Northern Uganda where all young people and women are equipped, connected, and empowered to thrive in the global digital economy.'],
                ['key' => 'mission_title', 'label' => 'Mission: heading', 'type' => 'text', 'default' => 'Our Mission'],
                ['key' => 'mission_text', 'label' => 'Mission: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'We aim to strengthen access to learning, improve practical competencies, build confidence and help young people prepare for further education, employment, entrepreneurship and leadership.'],
                ['key' => 'image1', 'label' => 'Collage: front photo', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => 'Young people seated at desks in a lecture hall'],
                ['key' => 'image2', 'label' => 'Collage: photo behind (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg', 'alt' => 'Young people seated in a training room, one of them typing on a laptop'],
                ['key' => 'cta_label', 'label' => 'First button: text', 'type' => 'text', 'default' => 'Read our story'],
                ['key' => 'cta_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '/about'],
                ['key' => 'cta2_label', 'label' => 'Second button: text (optional)', 'type' => 'text', 'default' => 'Meet the team'],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '/team'],
            ],
        ],
        // ------------------------------------------------------------------ 8. impact stories
        [
            'key' => 'stories', 'label' => '8. Impact stories', 'toggle' => true, 'default_visible' => true,
            'help' => 'Stories from Admin > Impact stories that are active AND have the consent box ticked. The whole section stays hidden until at least one exists.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Our Impact'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Impact [[Stories]]', 'help' => $hl],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Every story is shared with the consent of the people it features.'],
                ['key' => 'cta_label', 'label' => 'Button: text (optional)', 'type' => 'text', 'default' => 'Read more stories'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/stories'],
            ],
        ],
        // ------------------------------------------------------------------ 8b. latest newsletter
        [
            'key' => 'newsletter', 'label' => '9. Latest newsletter', 'toggle' => true, 'default_visible' => true,
            'help' => 'A card for the newest published newsletter issue (title, number and date, summary, cover picture). It is filled in by itself from Admin > Newsletter and stays hidden until an issue is published; only the words around it are edited here.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Newsletter'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Latest [[Newsletter]]', 'help' => $hl],
                ['key' => 'lead', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => 'Our latest news and updates.'],
                ['key' => 'cta_label', 'label' => 'Button on the card: text', 'type' => 'text', 'default' => 'Read this issue', 'help' => 'The same button opens the PDF issues too.'],
                ['key' => 'archive_cta_label', 'label' => 'Link to all issues: text (optional)', 'type' => 'text', 'default' => 'All issues'],
                ['key' => 'archive_cta_url', 'label' => 'Link to all issues: link', 'type' => 'url', 'default' => '/newsletter/archive'],
                ['key' => 'badge', 'label' => 'Small label on the card', 'type' => 'text', 'default' => 'Latest issue'],
            ],
        ],
        // ------------------------------------------------------------------ 10e. contact teaser
        [
            'key' => 'contact', 'label' => '10. Contact teaser', 'toggle' => true, 'default_visible' => true,
            'help' => 'A short "get in touch" card. The contact details come from Settings > Contact details; the button leads to the Contact page. (The newsletter form is in the footer.)',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Contact Us', 'help' => $hl],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Get in touch to empower youth and communities.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Submit Your Inquiry'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/contact'],
                ['key' => 'email_label', 'label' => 'Label: email', 'type' => 'text', 'default' => 'Email'],
                ['key' => 'phone_label', 'label' => 'Label: phone', 'type' => 'text', 'default' => 'Tel'],
                ['key' => 'whatsapp_label', 'label' => 'Label: WhatsApp', 'type' => 'text', 'default' => 'WhatsApp'],
                ['key' => 'location_label', 'label' => 'Label: location', 'type' => 'text', 'default' => 'Location'],
            ],
        ],
        // ------------------------------------------------------------------ 3b. challenge
        [
            'key' => 'challenge', 'label' => '10a. The Challenge We Address (shown on the About page)', 'toggle' => true, 'default_visible' => true,
            'help' => 'The problem the foundation exists to solve. Sits directly under "Who We Are" on the same cream background.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'The Challenge We Address', 'help' => $hl],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Northern Uganda experienced over two decades of armed conflict that displaced millions of people and severely disrupted education systems and economic activity.</p><p>Many young people today continue to face the long term consequences of that conflict.</p><p>Access to computers, internet connectivity, and digital training opportunities remains limited, particularly for youth from vulnerable backgrounds or those who did not complete formal education.</p><p>As the global economy becomes increasingly digital, the absence of these skills risks leaving an entire generation behind.</p><p>Lumina Africa Foundation exists to ensure that young people in Northern Uganda are not excluded from the opportunities created by the digital age.</p>'],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '', 'help' => 'Leave empty for a text-only card. When a photo is added it appears beside the text.'],
            ],
        ],
        // ------------------------------------------------------------------ 10a. why digital inclusion matters
        [
            'key' => 'why_digital', 'label' => '10b. Why Digital Inclusion Matters (shown on the About page)', 'toggle' => true, 'default_visible' => true,
            'help' => 'Long-form explanation in two columns, with a highlighted closing statement.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Why Digital Inclusion Matters in Northern Uganda', 'help' => $hl],
                ['key' => 'lead', 'label' => 'Opening paragraph', 'type' => 'textarea', 'rows' => 3, 'default' => 'Northern Uganda is a region of resilience, but it is also a region that continues to rebuild after more than two decades of conflict that disrupted education systems, livelihoods, and access to opportunity.'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>For many young people, those years meant growing up in displacement camps, experiencing interrupted schooling, and having limited exposure to technology that is now essential for participation in today’s economy.</p><p>While the world has rapidly advanced through digital innovation, many communities in Northern Uganda still face significant barriers to accessing technology, digital skills training, and reliable internet connectivity.</p><p>As governments, businesses, and institutions increasingly rely on digital systems, the absence of these skills risks excluding vulnerable youth from employment opportunities and economic participation.</p><blockquote>Digital inclusion is therefore not only a technological issue, it is a development priority.</blockquote><p>Technology can open pathways to education, employment, entrepreneurship and innovation.</p>'],
                ['key' => 'closing', 'label' => 'Closing statement (highlighted)', 'type' => 'textarea', 'rows' => 3, 'default' => "Our vision is to turn potential into opportunity by creating pathways for individuals and communities to learn, grow, innovate and thrive."],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '', 'help' => 'Leave empty for the two-column text layout. A photo turns it into text + photo.'],
            ],
        ],
        // ------------------------------------------------------------------ 10b. founder story
        [
            'key' => 'founder', 'label' => '10c. Founder Story (shown on the About page; its quote also appears in the home page About block)', 'toggle' => true, 'default_visible' => true,
            'help' => 'The founder\'s story with a portrait. The quote shown in the Vision & Mission section is taken from here.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Founder Story'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'From Experience to Purpose', 'help' => $hl],
                ['key' => 'body', 'label' => 'Story', 'type' => 'richtext', 'default' => '<p>Lumina Africa Foundation was founded on a simple but urgent realization, that too many young people in Northern Uganda are being left behind in a world that is rapidly becoming digital.</p><p>Growing up in a region that experienced more than two decades of conflict, our founder, Moses Komakech, witnessed firsthand how war disrupted education, limited opportunity, and left many young people struggling to rebuild their futures. Entire communities were displaced, schools were interrupted, and access to technology was almost nonexistent for many families.</p><p>Even as peace returned and communities began to rebuild, a new challenge emerged. The world was evolving through technology, yet many young people in Northern Uganda still lacked access to computers, digital skills, and opportunities to participate in the modern economy.</p><p>For Moses, this gap was not just about technology. It was about opportunity, dignity, and the future of an entire generation.</p><p>Motivated by the belief that every young person deserves the tools to succeed, he founded Lumina Africa Foundation to bridge the digital divide and create pathways for youth to learn, work, and lead in the digital age.</p><p>Today, Lumina Africa Foundation is a growing community-based organisation, working towards establishing the partnerships, resources and implementation frameworks necessary to translate its plans into meaningful community impact.</p><p>At its core, the foundation represents a commitment to ensuring that the young people of Northern Uganda are not defined by the challenges of the past, but empowered to shape the opportunities of the future.</p>'],
                ['key' => 'cta_label', 'label' => 'Button: text (optional)', 'type' => 'text', 'default' => 'See our story'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => 'https://www.linkedin.com/company/lumina-africa-foundation/'],
                ['key' => 'image', 'label' => 'Portrait', 'type' => 'image', 'default' => 'assets/img/site/photo-founder-tall.jpg',
                    'alt' => 'Portrait of Moses Komakech, founder of Lumina Africa Foundation, in a blue suit'],
                ['key' => 'caption', 'label' => 'Caption under the portrait (optional)', 'type' => 'text', 'default' => 'Moses Komakech, Founder'],
                ['key' => 'quote', 'label' => 'Quote card in "Vision & Mission": text', 'type' => 'textarea', 'rows' => 3,
                    'default' => 'Lumina Africa Foundation was founded on a simple but urgent realization, that too many young people in Northern Uganda are being left behind in a world that is rapidly becoming digital.',
                    'help' => 'Use a sentence from the founder\'s story or a quotation the founder has approved. Leave empty to hide the card. The default is an exact sentence from the live founder story (which is written in the third person, so the card is labelled "from the founder story" rather than "said by").'],
                ['key' => 'quote_role', 'label' => 'Quote card: small line under the name', 'type' => 'text', 'default' => 'From the founder story', 'maxlength' => 80],
            ],
        ],
        // ------------------------------------------------------------------ 10c. impact
        [
            'key' => 'impact', 'label' => '10d. Our Commitment to Responsible Development (shown on the About page)', 'toggle' => true, 'default_visible' => true,
            'help' => 'A short statement on a deep navy band.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Commitment to Responsible Development', 'help' => $hl],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>The initiatives described on this site represent our planned program areas and proposed interventions. They should not be interpreted as confirmation that every activity is currently funded, operational or already delivering results.</p><p>As programs are developed and implemented, we intend to communicate their status, objectives, progress and verified outcomes transparently.</p>'],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '', 'help' => 'Leave empty for centred text. A photo turns it into text + photo.'],
            ],
        ],
        // ------------------------------------------------------------------ 10d. background & rationale
        [
            'key' => 'background', 'label' => '10e. Background and Rationale (shown on the About page)', 'toggle' => true, 'default_visible' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Background and Rationale', 'help' => $hl],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Northern Uganda’s recovery story is inseparable from its past. For over two decades, the Lord’s Resistance Army insurgency displaced an estimated 1.8 million people across the Acholi sub region. Entire communities lived in internally displaced persons camps for nearly 20 years. Education systems collapsed, livelihoods were disrupted, and institutional capacity weakened. A generation of young people grew up with interrupted schooling, trauma exposure, and limited pathways into formal employment.</p><p>Although stability has returned, the structural effects remain visible. Many youth lack UCE, UACE, or tertiary completion certificates, not because of limited ability, but because conflict interrupted their education. This has resulted in layered exclusion, limited qualifications, restricted access to formal jobs, lower earning potential, and heightened economic vulnerability.</p><p>At the same time, Uganda’s economy is rapidly digitising. Access to government services, banking, commerce, and recruitment increasingly requires basic digital literacy. Yet in Northern Uganda, limited access to computers, weak connectivity, and minimal structured digital skills training have deepened the digital divide. For post conflict youth, digital exclusion risks becoming a second wave of marginalisation.</p><p>Lumina Africa Foundation was born at this intersection of post conflict recovery and digital inequality. Lumina Africa Foundation is planning foundational computer training covering computer use, office productivity tools, internet literacy and digital communication.</p>'],
                ['key' => 'closing', 'label' => 'Closing statement (highlighted)', 'type' => 'textarea', 'rows' => 4, 'default' => 'Small digital opportunities can unlock income. Income restores confidence. Confidence rebuilds dignity. Lumina Africa Foundation was established to ensure that Northern Uganda’s youth are not left behind in a digitising economy, but positioned to participate, compete, and rebuild their futures with resilience and purpose.'],
            ],
        ],
    ],
];
