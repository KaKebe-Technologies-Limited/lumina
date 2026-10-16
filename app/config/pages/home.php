<?php
/**
 * Registry entry: HOME PAGE.
 * Every heading, paragraph, button (label + link), image (+ alt text), repeating list, section toggle and the SEO defaults of the
 * home page are declared here, so the owner can edit all of it in Admin > Pages > Home without touching code.
 * DEFAULT WORDING = the live site https://www.luminaafricafoundation.com/ (docs/live-snapshot/home.txt). Where a heading had to be
 * written for the new layout it is built only from live wording (hero, programs, vision, mission, involvement page).
 *
 * Sections, in the order they appear on the page (each has an on/off switch):
 *   hero > help (How we help) > who_we_are > challenge > volunteer (+ stats card) > vision_mission (+ founder quote, photo mosaic)
 *   > projects (featured programs & projects carousel) > donate_panel > stories > faq > why_digital > founder > impact > background > contact
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
    'help'  => 'The landing page: hero with video, how we help, who we are, volunteer band with statistics, vision and mission, featured programs, donation panel, impact stories, FAQ, and the longer story sections.',
    'nav'   => ['label' => 'Home', 'order' => 10, 'header' => true, 'footer' => false],
    'seo'   => [
        'title'       => 'Empowering Marginalized Communities in Uganda',
        'description' => 'Lumina Africa Foundation: a community-based non-profit in Gulu City, Northern Uganda, bridging the digital divide for vulnerable youth through digital skills and enterprise.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        // ------------------------------------------------------------------ 1. hero
        [
            'key' => 'hero', 'label' => '1. Hero banner', 'toggle' => true, 'default_visible' => true,
            'help' => 'Full-width photo with the main heading, two buttons and a video button. If you hide it, the heading is kept for screen readers and search engines.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Welcome to the Lumina Africa Foundation', 'maxlength' => 120],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Bridging the Digital Divide for [[Vulnerable Youth]] in Northern Uganda', 'maxlength' => 160, 'help' => $hl],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 4,
                    'default' => 'Lumina Africa Foundation is a registered community-based, non-profit organisation headquartered in Gulu City, Northern Uganda, Reg. No: {reg_number}. We work to reduce youth unemployment and economic exclusion by bridging the digital divide affecting vulnerable young people in Northern Uganda.',
                    'help' => '{reg_number} is filled from Settings > Organisation > Registration number. Remove the "Reg. No" part if you do not want it shown.'],
                ['key' => 'cta1_label', 'label' => 'First button (orange): text', 'type' => 'text', 'default' => 'Explore our Programs'],
                ['key' => 'cta1_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '/programs'],
                ['key' => 'cta2_label', 'label' => 'Extra button (glass): text (optional)', 'type' => 'text', 'default' => '', 'help' => 'Leave empty for the cleaner two-button hero (orange button + video button).'],
                ['key' => 'cta2_url', 'label' => 'Extra button: link', 'type' => 'url', 'default' => '/involvement'],
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
        // ------------------------------------------------------------------ 2. how we help
        [
            'key' => 'help', 'label' => '2. How we help (feature cards + tall photo)', 'toggle' => true, 'default_visible' => true,
            'help' => 'Outlined cards for the three planned program pillars, with a tall portrait photo beside them.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Our planned programs'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Building [[Pathways to Opportunity]]', 'help' => $hl],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation.'],
                ['key' => 'items', 'label' => 'Cards', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a card', 'max_rows' => 4,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'One-line description', 'type' => 'textarea', 'rows' => 2],
                        ['key' => 'url', 'label' => 'Link (optional)', 'type' => 'url'],
                    ],
                    'default' => [
                        ['icon' => 'heart', 'title' => 'Health & Community Wellbeing', 'text' => 'Promoting health awareness and supporting healthier communities.', 'url' => '/programs/health-community-wellbeing'],
                        ['icon' => 'graduation-cap', 'title' => 'Education, Skills & Youth Development', 'text' => 'Expanding learning opportunities and preparing young people for the future.', 'url' => '/programs/education-skills-youth-development'],
                        ['icon' => 'laptop', 'title' => 'Technology, Digital Inclusion & Innovation', 'text' => 'Expanding access to technology and preparing young people for the digital economy.', 'url' => '/programs/technology-digital-inclusion-innovation'],
                    ]],
                ['key' => 'image', 'label' => 'Tall photo (portrait)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg',
                    'alt' => 'Young people seated in the audience of an event, some smiling',
                    'help' => 'Shown in a 4:5 portrait frame with very round corners. Keep the faces near the centre of the photo.'],
            ],
        ],
        // ------------------------------------------------------------------ 3. who we are (photo stack)
        [
            'key' => 'who_we_are', 'label' => '3. Who We Are (tilted photos + text)', 'toggle' => true, 'default_visible' => true,
            'help' => 'Short introduction to the foundation, with up to three overlapping photos.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Who We Are'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Expanding digital opportunity for [[vulnerable youth]]', 'help' => $hl],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Lumina Africa Foundation is a community based nonprofit organization headquartered in Gulu City, Northern Uganda. Founded by young African changemakers, the organization works to expand digital opportunity for vulnerable youth in post conflict communities.</p><p>We focus on closing the digital gap by providing practical digital skills training, access to technology, mentorship, and employment pathways that help young people participate meaningfully in the modern economy.</p><p>Our work is grounded in the belief that digital inclusion is essential for sustainable development and economic resilience.</p>'],
                ['key' => 'cta_label', 'label' => 'Button: text (optional)', 'type' => 'text', 'default' => 'Learn more'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/about'],
                ['key' => 'image', 'label' => 'Front photo', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg',
                    'alt' => 'A trainer points at a screen while young people wearing headphones work at computers in a training room'],
                ['key' => 'image2', 'label' => 'Second photo (behind, optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg',
                    'alt' => 'Young people seated at desks in a lecture hall'],
                ['key' => 'image3', 'label' => 'Third photo (furthest back, optional)', 'type' => 'image', 'default' => '', 'alt' => ''],
            ],
        ],
        // ------------------------------------------------------------------ 3b. challenge
        [
            'key' => 'challenge', 'label' => '3b. The Challenge We Address', 'toggle' => true, 'default_visible' => true,
            'help' => 'The problem the foundation exists to solve. Sits directly under "Who We Are" on the same cream background.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'The Challenge We Address', 'help' => $hl],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Northern Uganda experienced over two decades of armed conflict that displaced millions of people and severely disrupted education systems and economic activity.</p><p>Many young people today continue to face the long term consequences of that conflict.</p><p>Access to computers, internet connectivity, and digital training opportunities remains limited, particularly for youth from vulnerable backgrounds or those who did not complete formal education.</p><p>As the global economy becomes increasingly digital, the absence of these skills risks leaving an entire generation behind.</p><p>Lumina Africa Foundation exists to ensure that young people in Northern Uganda are not excluded from the opportunities created by the digital age.</p>'],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '', 'help' => 'Leave empty for a text-only card. When a photo is added it appears beside the text.'],
            ],
        ],
        // ------------------------------------------------------------------ 4. volunteer band + statistics card
        [
            'key' => 'volunteer', 'label' => '4. Volunteer band (photo + buttons)', 'toggle' => true, 'default_visible' => true,
            'help' => 'Full-width photo with a centred invitation. The statistics card (next section) overlaps its bottom edge.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Volunteer With Us'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Give your time, skills, and passion to [[impact lives]] directly', 'help' => $hl],
                ['key' => 'text', 'label' => 'Text (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
                ['key' => 'cta_label', 'label' => 'First button (orange): text', 'type' => 'text', 'default' => 'Volunteer With Us'],
                ['key' => 'cta_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '/involvement?type=volunteer#join'],
                ['key' => 'cta2_label', 'label' => 'Second button (white): text (optional)', 'type' => 'text', 'default' => 'Partner With Us'],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '/involvement?type=partner#join'],
                ['key' => 'video_url', 'label' => 'Video: YouTube link (optional)', 'type' => 'url', 'default' => '', 'help' => 'Adds a small white "play" button. Leave empty for none.'],
                ['key' => 'video_label', 'label' => 'Video: button text', 'type' => 'text', 'default' => 'Watch video'],
                ['key' => 'video_title', 'label' => 'Video: title', 'type' => 'text', 'default' => ''],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg',
                    'alt' => 'Young people seated in a training room, one of them typing on a laptop',
                    'help' => 'Darkened behind the text, so people far from the centre are fine; a plain navy band is shown when empty.'],
            ],
        ],
        [
            'key' => 'stats', 'label' => '4b. Statistics card', 'toggle' => true, 'default_visible' => true,
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
        // ------------------------------------------------------------------ 5. vision & mission
        [
            'key' => 'vision_mission', 'label' => '5. Vision & Mission (cards, founder quote, photo row)', 'toggle' => true, 'default_visible' => true,
            'help' => 'Photo collage on the left; heading, vision and mission cards, icon rows and a founder quote on the right; a row of gallery photos underneath.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'About Lumina Africa Foundation'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our [[vision]] and mission', 'help' => $hl],
                ['key' => 'vision_title', 'label' => 'Vision: heading', 'type' => 'text', 'default' => 'Our Vision'],
                ['key' => 'vision_text', 'label' => 'Vision: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'An inclusive digital future for Northern Uganda where all young people and women are equipped, connected, and empowered to thrive in the global digital economy.'],
                ['key' => 'mission_title', 'label' => 'Mission: heading', 'type' => 'text', 'default' => 'Our Mission'],
                ['key' => 'mission_text', 'label' => 'Mission: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'To equip young people in Northern Uganda with relevant digital skills, expand access to internet and technology, and connect them to opportunities in the digital economy, enabling sustainable livelihoods and inclusive growth'],
                ['key' => 'rows', 'label' => 'Icon rows (optional)', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a row', 'max_rows' => 4,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options(), 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2],
                    ],
                    'default' => [
                        ['icon' => 'laptop', 'title' => 'Relevant digital skills', 'text' => 'Equipping young people in Northern Uganda with relevant digital skills.'],
                        ['icon' => 'wifi', 'title' => 'Internet and technology', 'text' => 'Expanding access to internet and technology.'],
                        ['icon' => 'briefcase', 'title' => 'Digital economy opportunities', 'text' => 'Connecting young people to opportunities that enable sustainable livelihoods and inclusive growth.'],
                    ]],
                ['key' => 'image1', 'label' => 'Collage: front photo', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => 'Young people seated at desks in a lecture hall'],
                ['key' => 'image2', 'label' => 'Collage: photo behind (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg', 'alt' => 'Young people seated in a training room, one of them typing on a laptop'],
                ['key' => 'show_mosaic', 'label' => 'Show the row of gallery photos underneath', 'type' => 'boolean', 'default' => true, 'help' => 'Uses the active photos of Admin > Gallery. It is hidden automatically while fewer than three photos remain that the projects carousel does not already show.'],
            ],
        ],
        // ------------------------------------------------------------------ 6. featured programs & projects
        [
            'key' => 'projects', 'label' => '6. Featured programs & projects (carousel)', 'toggle' => true, 'default_visible' => true,
            'help' => 'A swipeable row of cards taken from Admin > Programs (and any extra, separate projects from Admin > Projects). The cards below are only used while both lists are empty.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Our planned programs'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our three planned program [[pillars]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'text', 'default' => 'Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation.'],
                ['key' => 'limit', 'label' => 'How many cards to show', 'type' => 'number', 'default' => 3, 'min' => 1, 'max' => 12],
                ['key' => 'card_cta', 'label' => 'Card button text', 'type' => 'text', 'default' => 'Learn more'],
                ['key' => 'cta_label', 'label' => 'Button below the cards: text (optional)', 'type' => 'text', 'default' => 'Explore Our Planned Programs'],
                ['key' => 'cta_url', 'label' => 'Button below the cards: link', 'type' => 'url', 'default' => '/programs'],
                ['key' => 'items', 'label' => 'Fallback cards (used only while Admin > Programs and Projects are empty)', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a card', 'max_rows' => 8,
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'subtitle', 'label' => 'Small heading', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url'],
                        ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                    ],
                    'default' => [
                        ['title' => 'Health & Community Wellbeing', 'subtitle' => 'Planned program area', 'text' => 'Promoting health awareness and supporting healthier communities.', 'url' => '/programs/health-community-wellbeing', 'image' => '', 'image_alt' => ''],
                        ['title' => 'Education, Skills & Youth Development', 'subtitle' => 'Planned program area', 'text' => 'Expanding learning opportunities and preparing young people for the future.', 'url' => '/programs/education-skills-youth-development', 'image' => '', 'image_alt' => ''],
                        ['title' => 'Technology, Digital Inclusion & Innovation', 'subtitle' => 'Planned program area', 'text' => 'Expanding access to technology and preparing young people for the digital economy.', 'url' => '/programs/technology-digital-inclusion-innovation', 'image' => '', 'image_alt' => ''],
                    ],
                ],
            ],
        ],
        // ------------------------------------------------------------------ 7. donation panel
        [
            'key' => 'donate_panel', 'label' => '7. Donation panel', 'toggle' => true, 'default_visible' => true,
            'help' => 'The deep navy "make a gift" panel with amount buttons. It does NOT take payments: amounts go to the Donate page (pledge form) and the second button opens the online donation link from Settings. Its wording, amounts, currency and photos are edited once under Site-wide content > Donation panel; fill in the fields below only to say something different on the home page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading (leave empty to use the site-wide wording)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading (leave empty to use the site-wide wording)', 'type' => 'text', 'default' => '', 'help' => $hl],
                ['key' => 'text', 'label' => 'Text (leave empty to use the site-wide wording)', 'type' => 'textarea', 'rows' => 3, 'default' => ''],
                ['key' => 'image1', 'label' => 'Collage photo (front)', 'type' => 'image', 'default' => 'assets/img/site/photo-hero-wide.jpg',
                    'alt' => 'Pupils in blue school uniforms seated along rows of desks with laptops in a classroom',
                    'help' => 'The home page shows its own photo here so the projects carousel just above does not repeat the same pictures. Remove it (and the next one) to use the site-wide collage photos instead.'],
                ['key' => 'image2', 'label' => 'Collage photo (behind, optional)', 'type' => 'image', 'default' => '', 'alt' => ''],
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
        // ------------------------------------------------------------------ 9. FAQ
        [
            'key' => 'faq', 'label' => '9. FAQ (photo + questions)', 'toggle' => true, 'default_visible' => true,
            'help' => 'Questions come from Admin > FAQs: group "home" if you create one, otherwise "general", otherwise the Donate questions. Hidden when there are none.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'FAQ'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Frequently Asked [[Questions]]', 'help' => $hl],
                ['key' => 'lead', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg', 'alt' => 'Young people seated in the audience of an event, some smiling', 'help' => 'Leave empty for a centred list without a photo.'],
            ],
        ],
        // ------------------------------------------------------------------ 10a. why digital inclusion matters
        [
            'key' => 'why_digital', 'label' => '10a. Why Digital Inclusion Matters', 'toggle' => true, 'default_visible' => true,
            'help' => 'Long-form explanation in two columns, with a highlighted closing statement.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Why Digital Inclusion Matters in Northern Uganda', 'help' => $hl],
                ['key' => 'lead', 'label' => 'Opening paragraph', 'type' => 'textarea', 'rows' => 3, 'default' => 'Northern Uganda is a region of resilience, but it is also a region that continues to rebuild after more than two decades of conflict that disrupted education systems, livelihoods, and access to opportunity.'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>For many young people, those years meant growing up in displacement camps, experiencing interrupted schooling, and having limited exposure to technology that is now essential for participation in today’s economy.</p><p>While the world has rapidly advanced through digital innovation, many communities in Northern Uganda still face significant barriers to accessing technology, digital skills training, and reliable internet connectivity.</p><p>As governments, businesses, and institutions increasingly rely on digital systems, the absence of these skills risks excluding vulnerable youth from employment opportunities and economic participation.</p><blockquote>Digital inclusion is therefore not only a technological issue, it is a development priority.</blockquote><p>By expanding access to digital skills, tools, and opportunities, we enable young people to connect with education, employment pathways, entrepreneurship, and information that can transform their futures.</p>'],
                ['key' => 'closing', 'label' => 'Closing statement (highlighted)', 'type' => 'textarea', 'rows' => 3, 'default' => "Lumina Africa Foundation works to ensure that young people in Northern Uganda are not left behind by the digital revolution.\n\nInstead, they become active participants in shaping the region’s future."],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '', 'help' => 'Leave empty for the two-column text layout. A photo turns it into text + photo.'],
            ],
        ],
        // ------------------------------------------------------------------ 10b. founder story
        [
            'key' => 'founder', 'label' => '10b. Founder Story', 'toggle' => true, 'default_visible' => true,
            'help' => 'The founder\'s story with a portrait. The quote shown in the Vision & Mission section is taken from here.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Founder Story'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'From Experience to Purpose', 'help' => $hl],
                ['key' => 'body', 'label' => 'Story', 'type' => 'richtext', 'default' => '<p>Lumina Africa Foundation was founded on a simple but urgent realization, that too many young people in Northern Uganda are being left behind in a world that is rapidly becoming digital.</p><p>Growing up in a region that experienced more than two decades of conflict, our founder, Moses Komakech, witnessed firsthand how war disrupted education, limited opportunity, and left many young people struggling to rebuild their futures. Entire communities were displaced, schools were interrupted, and access to technology was almost nonexistent for many families.</p><p>Even as peace returned and communities began to rebuild, a new challenge emerged. The world was evolving through technology, yet many young people in Northern Uganda still lacked access to computers, digital skills, and opportunities to participate in the modern economy.</p><p>For Moses, this gap was not just about technology. It was about opportunity, dignity, and the future of an entire generation.</p><p>Motivated by the belief that every young person deserves the tools to succeed, he founded Lumina Africa Foundation to bridge the digital divide and create pathways for youth to learn, work, and lead in the digital age.</p><p>Today, Lumina Africa Foundation works with communities, institutions, and partners to expand access to digital skills training, employment opportunities, and leadership development for vulnerable youth.</p><p>At its core, the foundation represents a commitment to ensuring that the young people of Northern Uganda are not defined by the challenges of the past, but empowered to shape the opportunities of the future.</p>'],
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
            'key' => 'impact', 'label' => '10c. Our Impact', 'toggle' => true, 'default_visible' => true,
            'help' => 'A short statement on a deep navy band.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Impact', 'help' => $hl],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>At Lumina Africa Foundation, our work focuses on creating real and measurable change for young people in Northern Uganda. Through digital skills training, mentorship, and opportunity pathways, we are building a future where youth can participate fully in the digital economy.</p><p>Our programs are designed to expand digital access, strengthen employability, and empower young people with the skills needed to thrive in a rapidly evolving world.</p>'],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '', 'help' => 'Leave empty for centred text. A photo turns it into text + photo.'],
            ],
        ],
        // ------------------------------------------------------------------ 10d. background & rationale
        [
            'key' => 'background', 'label' => '10d. Background and Rationale', 'toggle' => true, 'default_visible' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Background and Rationale', 'help' => $hl],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Northern Uganda’s recovery story is inseparable from its past. For over two decades, the Lord’s Resistance Army insurgency displaced an estimated 1.8 million people across the Acholi sub region. Entire communities lived in internally displaced persons camps for nearly 20 years. Education systems collapsed, livelihoods were disrupted, and institutional capacity weakened. A generation of young people grew up with interrupted schooling, trauma exposure, and limited pathways into formal employment.</p><p>Although stability has returned, the structural effects remain visible. Many youth lack UCE, UACE, or tertiary completion certificates, not because of limited ability, but because conflict interrupted their education. This has resulted in layered exclusion, limited qualifications, restricted access to formal jobs, lower earning potential, and heightened economic vulnerability.</p><p>At the same time, Uganda’s economy is rapidly digitising. Access to government services, banking, commerce, and recruitment increasingly requires basic digital literacy. Yet in Northern Uganda, limited access to computers, weak connectivity, and minimal structured digital skills training have deepened the digital divide. For post conflict youth, digital exclusion risks becoming a second wave of marginalisation.</p><p>Lumina Africa Foundation was born at this intersection of post conflict recovery and digital inequality. The organisation exists to provide inclusive, practical digital skills that do not require prior certification. By equipping youth with competencies in typing, digital documentation, basic office tools, data collection, and entry level digital entrepreneurship, Lumina creates accessible entry points into income generation.</p>'],
                ['key' => 'closing', 'label' => 'Closing statement (highlighted)', 'type' => 'textarea', 'rows' => 4, 'default' => 'Small digital opportunities can unlock income. Income restores confidence. Confidence rebuilds dignity. Lumina Africa Foundation was established to ensure that Northern Uganda’s youth are not left behind in a digitising economy, but positioned to participate, compete, and rebuild their futures with resilience and purpose.'],
            ],
        ],
        // ------------------------------------------------------------------ 10e. contact teaser
        [
            'key' => 'contact', 'label' => '10e. Contact teaser', 'toggle' => true, 'default_visible' => true,
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
    ],
];
