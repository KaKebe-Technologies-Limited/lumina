<?php
/**
 * Registry entry: HOME PAGE  (OWNER: B1 "web-core").
 * Every heading, paragraph, button (label + link), image (+ alt text), repeating list, section toggle and the SEO defaults of the
 * home page are declared here, so the owner can edit all of it in Admin > Pages > Home without touching code.
 * DEFAULT WORDING = the live site https://www.luminaafricafoundation.com/ (docs/live-snapshot/home.txt).
 *
 * Tokens usable in text fields: {reg_number} {site_name} {founder_name} {city} (filled from Settings; see HomeSupport::fill()).
 * Data that is NOT copy lives elsewhere: statistics (Admin > Stats), project cards (Admin > Projects), contact details (Settings).
 */
return [
    'slug'  => 'home',
    'title' => 'Home',
    'route' => '/',
    'help'  => 'The landing page: hero, who we are, the challenge, why digital inclusion matters, vision and mission, statistics, projects, founder story, impact, background and a contact teaser.',
    'nav'   => ['label' => 'Home', 'order' => 10, 'header' => true, 'footer' => false],
    'seo'   => [
        'title'       => 'Empowering Marginalized Communities in Uganda',
        'description' => 'Lumina Africa Foundation: a community-based non-profit in Gulu City, Northern Uganda, bridging the digital divide for vulnerable youth through digital skills and enterprise.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        // ------------------------------------------------------------------ hero
        [
            'key' => 'hero', 'label' => 'Hero banner', 'help' => 'The first thing visitors see: heading, intro, two buttons and a photo.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Welcome to the Lumina Africa Foundation', 'maxlength' => 120],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Bridging the Digital Divide for Vulnerable Youth in Northern Uganda', 'maxlength' => 160],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 4,
                    'default' => 'Lumina Africa Foundation is a registered community-based, non-profit organisation headquartered in Gulu City, Northern Uganda, Reg. No: {reg_number}. We work to reduce youth unemployment and economic exclusion by bridging the digital divide affecting vulnerable young people in Northern Uganda.',
                    'help' => '{reg_number} is filled from Settings > Organisation > Registration number. Remove the "Reg. No" part if you do not want it shown.'],
                ['key' => 'cta1_label', 'label' => 'First button: text', 'type' => 'text', 'default' => 'Explore our Programs'],
                ['key' => 'cta1_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '/programs'],
                ['key' => 'cta2_label', 'label' => 'Second button: text', 'type' => 'text', 'default' => 'Support Our Mission'],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '/involvement'],
                ['key' => 'image', 'label' => 'Hero photo (portrait)', 'type' => 'image', 'default' => 'assets/img/site/photo-hero-portrait.jpg',
                    'alt' => 'Pupils in blue school uniforms seated along rows of desks with laptops in a classroom',
                    'help' => 'Portrait photos (4:5) work best. The bottom strip of the photo is cropped on purpose; the default photo carries a third-party banner there, so replace it with the foundation\'s own photo when you can.'],
                ['key' => 'show_location', 'label' => 'Show the location badge on the photo', 'type' => 'boolean', 'default' => true, 'help' => 'Prints the city/country from Settings > Contact details.'],
            ],
        ],
        // ------------------------------------------------------------------ who we are
        [
            'key' => 'who_we_are', 'label' => 'Who We Are', 'toggle' => true, 'help' => 'Short introduction to the foundation, with a photo.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Who We Are'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Lumina Africa Foundation is a community based nonprofit organization headquartered in Gulu City, Northern Uganda. Founded by young African changemakers, the organization works to expand digital opportunity for vulnerable youth in post conflict communities.</p><p>We focus on closing the digital gap by providing practical digital skills training, access to technology, mentorship, and employment pathways that help young people participate meaningfully in the modern economy.</p><p>Our work is grounded in the belief that digital inclusion is essential for sustainable development and economic resilience.</p>'],
                ['key' => 'cta_label', 'label' => 'Button: text (optional)', 'type' => 'text', 'default' => 'Learn more'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/about'],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg',
                    'alt' => 'A trainer points at a screen while young people wearing headphones work at computers in a training room'],
            ],
        ],
        // ------------------------------------------------------------------ challenge
        [
            'key' => 'challenge', 'label' => 'The Challenge We Address', 'toggle' => true, 'help' => 'The problem the foundation exists to solve.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'The Challenge We Address'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Northern Uganda experienced over two decades of armed conflict that displaced millions of people and severely disrupted education systems and economic activity.</p><p>Many young people today continue to face the long term consequences of that conflict.</p><p>Access to computers, internet connectivity, and digital training opportunities remains limited, particularly for youth from vulnerable backgrounds or those who did not complete formal education.</p><p>As the global economy becomes increasingly digital, the absence of these skills risks leaving an entire generation behind.</p><p>Lumina Africa Foundation exists to ensure that young people in Northern Uganda are not excluded from the opportunities created by the digital age.</p>'],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg',
                    'alt' => 'Young people seated in the audience of an event'],
            ],
        ],
        // ------------------------------------------------------------------ why digital inclusion matters
        [
            'key' => 'why_digital', 'label' => 'Why Digital Inclusion Matters', 'toggle' => true, 'help' => 'Long-form explanation with an optional video (shown as a click-to-play poster; nothing is loaded from YouTube until the visitor presses play).',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Why Digital Inclusion Matters in Northern Uganda'],
                ['key' => 'lead', 'label' => 'Opening paragraph', 'type' => 'textarea', 'rows' => 3, 'default' => 'Northern Uganda is a region of resilience, but it is also a region that continues to rebuild after more than two decades of conflict that disrupted education systems, livelihoods, and access to opportunity.'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>For many young people, those years meant growing up in displacement camps, experiencing interrupted schooling, and having limited exposure to technology that is now essential for participation in today’s economy.</p><p>While the world has rapidly advanced through digital innovation, many communities in Northern Uganda still face significant barriers to accessing technology, digital skills training, and reliable internet connectivity.</p><p>As governments, businesses, and institutions increasingly rely on digital systems, the absence of these skills risks excluding vulnerable youth from employment opportunities and economic participation.</p><blockquote>Digital inclusion is therefore not only a technological issue, it is a development priority.</blockquote><p>By expanding access to digital skills, tools, and opportunities, we enable young people to connect with education, employment pathways, entrepreneurship, and information that can transform their futures.</p>'],
                ['key' => 'closing', 'label' => 'Closing statement (highlighted)', 'type' => 'textarea', 'rows' => 3, 'default' => "Lumina Africa Foundation works to ensure that young people in Northern Uganda are not left behind by the digital revolution.\n\nInstead, they become active participants in shaping the region’s future."],
                ['key' => 'video_url', 'label' => 'Video: YouTube link (optional)', 'type' => 'url', 'default' => 'https://www.youtube.com/watch?v=DpptGd72fwc', 'help' => 'A youtube.com or youtu.be link. Leave empty to show the photo below instead.'],
                ['key' => 'video_title', 'label' => 'Video: title', 'type' => 'text', 'default' => 'Driving Digital Inclusion in Africa | Orange’s Vision at Africa Tech Festival 2024', 'help' => 'Shown on the poster and read by screen readers.'],
                ['key' => 'video_play', 'label' => 'Video: play button text', 'type' => 'text', 'default' => 'Play video'],
                ['key' => 'video_watch', 'label' => 'Video: "watch on YouTube" link text', 'type' => 'text', 'default' => 'Watch on YouTube'],
                ['key' => 'image', 'label' => 'Photo (used when there is no video)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg',
                    'alt' => 'Young people seated at desks in a lecture hall'],
            ],
        ],
        // ------------------------------------------------------------------ vision & mission
        [
            'key' => 'vision_mission', 'label' => 'Vision & Mission', 'toggle' => true, 'help' => 'Navy band with the vision and the mission.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the cards', 'type' => 'text', 'default' => 'About Lumina Africa Foundation'],
                ['key' => 'vision_title', 'label' => 'Vision: heading', 'type' => 'text', 'default' => 'Our Vision'],
                ['key' => 'vision_text', 'label' => 'Vision: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'An inclusive digital future for Northern Uganda where all young people and women are equipped, connected, and empowered to thrive in the global digital economy.'],
                ['key' => 'mission_title', 'label' => 'Mission: heading', 'type' => 'text', 'default' => 'Our Mission'],
                ['key' => 'mission_text', 'label' => 'Mission: text', 'type' => 'textarea', 'rows' => 4, 'default' => 'To equip young people in Northern Uganda with relevant digital skills, expand access to internet and technology, and connect them to opportunities in the digital economy, enabling sustainable livelihoods and inclusive growth'],
            ],
        ],
        // ------------------------------------------------------------------ stats
        [
            'key' => 'stats', 'label' => 'Statistics', 'toggle' => true,
            'help' => 'Number counters under the vision and mission. The numbers come from Admin > Stats; the list below is only used until you add statistics there.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'items', 'label' => 'Fallback statistics (used only while Admin > Stats is empty)', 'type' => 'repeater', 'item_label' => 'label', 'add_label' => 'Add a statistic', 'max_rows' => 6,
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
        // ------------------------------------------------------------------ projects
        [
            'key' => 'projects', 'label' => 'Empowerment Projects', 'toggle' => true,
            'help' => 'Project cards. They come from Admin > Projects (featured ones first); the cards below are only used until you add projects there.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Empowerment Projects'],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'text', 'default' => 'Discover our initiatives empowering youth and communities.'],
                ['key' => 'limit', 'label' => 'How many projects to show', 'type' => 'number', 'default' => 4, 'min' => 1, 'max' => 12],
                ['key' => 'card_cta', 'label' => 'Card link text', 'type' => 'text', 'default' => 'Learn more'],
                ['key' => 'cta_label', 'label' => 'Button below the cards: text (optional)', 'type' => 'text', 'default' => 'View all projects'],
                ['key' => 'cta_url', 'label' => 'Button below the cards: link', 'type' => 'url', 'default' => '/projects'],
                ['key' => 'items', 'label' => 'Fallback cards (used only while Admin > Projects is empty)', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a project card', 'max_rows' => 8,
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'subtitle', 'label' => 'Small heading', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url'],
                        ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                    ],
                    'default' => [
                        ['title' => 'Digital Pathways for Youth', 'subtitle' => 'Bridging the Digital Divide', 'text' => 'Digital Pathways for Youth is our flagship program designed to equip vulnerable young people in Northern Uganda with practical digital skills and structured pathways to economic opportunity.', 'url' => '/projects', 'image' => '', 'image_alt' => ''],
                        ['title' => 'Digital Employability & Internship Linkages', 'subtitle' => 'From Skills to Work', 'text' => 'Training without opportunity is incomplete. The Digital Employability & Internship Linkages Program connects trained youth to real income pathways.', 'url' => '/projects', 'image' => '', 'image_alt' => ''],
                        ['title' => 'Inclusive Digital Access for Out-of-School Youth', 'subtitle' => 'No Youth Left Behind', 'text' => 'Northern Uganda’s conflict history disrupted education for many young people. This program ensures that youth without formal academic certificates are not excluded from digital opportunity.', 'url' => '/projects', 'image' => '', 'image_alt' => ''],
                        ['title' => 'Youth Digital Entrepreneurship & Micro-Income Development', 'subtitle' => 'Building Self-Reliance', 'text' => 'This program supports youth to transition from skills acquisition to income generation through small digital enterprises.', 'url' => '/projects', 'image' => '', 'image_alt' => ''],
                    ],
                ],
            ],
        ],
        // ------------------------------------------------------------------ founder story
        [
            'key' => 'founder', 'label' => 'Founder Story', 'toggle' => true, 'help' => 'The founder\'s story with a portrait.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Founder Story'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'From Experience to Purpose'],
                ['key' => 'body', 'label' => 'Story', 'type' => 'richtext', 'default' => '<p>Lumina Africa Foundation was founded on a simple but urgent realization, that too many young people in Northern Uganda are being left behind in a world that is rapidly becoming digital.</p><p>Growing up in a region that experienced more than two decades of conflict, our founder, Moses Komakech, witnessed firsthand how war disrupted education, limited opportunity, and left many young people struggling to rebuild their futures. Entire communities were displaced, schools were interrupted, and access to technology was almost nonexistent for many families.</p><p>Even as peace returned and communities began to rebuild, a new challenge emerged. The world was evolving through technology, yet many young people in Northern Uganda still lacked access to computers, digital skills, and opportunities to participate in the modern economy.</p><p>For Moses, this gap was not just about technology. It was about opportunity, dignity, and the future of an entire generation.</p><p>Motivated by the belief that every young person deserves the tools to succeed, he founded Lumina Africa Foundation to bridge the digital divide and create pathways for youth to learn, work, and lead in the digital age.</p><p>Today, Lumina Africa Foundation works with communities, institutions, and partners to expand access to digital skills training, employment opportunities, and leadership development for vulnerable youth.</p><p>At its core, the foundation represents a commitment to ensuring that the young people of Northern Uganda are not defined by the challenges of the past, but empowered to shape the opportunities of the future.</p>'],
                ['key' => 'cta_label', 'label' => 'Button: text (optional)', 'type' => 'text', 'default' => 'See our story'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => 'https://www.linkedin.com/company/lumina-africa-foundation/'],
                ['key' => 'image', 'label' => 'Portrait', 'type' => 'image', 'default' => 'assets/img/site/photo-founder-tall.jpg',
                    'alt' => 'Portrait of Moses Komakech, founder of Lumina Africa Foundation, in a blue suit'],
                ['key' => 'caption', 'label' => 'Caption under the portrait (optional)', 'type' => 'text', 'default' => 'Moses Komakech, Founder'],
            ],
        ],
        // ------------------------------------------------------------------ impact
        [
            'key' => 'impact', 'label' => 'Our Impact', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Impact'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>At Lumina Africa Foundation, our work focuses on creating real and measurable change for young people in Northern Uganda. Through digital skills training, mentorship, and opportunity pathways, we are building a future where youth can participate fully in the digital economy.</p><p>Our programs are designed to expand digital access, strengthen employability, and empower young people with the skills needed to thrive in a rapidly evolving world.</p>'],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg',
                    'alt' => 'Young people seated in a training room, one of them typing on a laptop'],
            ],
        ],
        // ------------------------------------------------------------------ background & rationale
        [
            'key' => 'background', 'label' => 'Background and Rationale', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Background and Rationale'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Northern Uganda’s recovery story is inseparable from its past. For over two decades, the Lord’s Resistance Army insurgency displaced an estimated 1.8 million people across the Acholi sub region. Entire communities lived in internally displaced persons camps for nearly 20 years. Education systems collapsed, livelihoods were disrupted, and institutional capacity weakened. A generation of young people grew up with interrupted schooling, trauma exposure, and limited pathways into formal employment.</p><p>Although stability has returned, the structural effects remain visible. Many youth lack UCE, UACE, or tertiary completion certificates, not because of limited ability, but because conflict interrupted their education. This has resulted in layered exclusion, limited qualifications, restricted access to formal jobs, lower earning potential, and heightened economic vulnerability.</p><p>At the same time, Uganda’s economy is rapidly digitising. Access to government services, banking, commerce, and recruitment increasingly requires basic digital literacy. Yet in Northern Uganda, limited access to computers, weak connectivity, and minimal structured digital skills training have deepened the digital divide. For post conflict youth, digital exclusion risks becoming a second wave of marginalisation.</p><p>Lumina Africa Foundation was born at this intersection of post conflict recovery and digital inequality. The organisation exists to provide inclusive, practical digital skills that do not require prior certification. By equipping youth with competencies in typing, digital documentation, basic office tools, data collection, and entry level digital entrepreneurship, Lumina creates accessible entry points into income generation.</p>'],
                ['key' => 'closing', 'label' => 'Closing statement (highlighted)', 'type' => 'textarea', 'rows' => 4, 'default' => 'Small digital opportunities can unlock income. Income restores confidence. Confidence rebuilds dignity. Lumina Africa Foundation was established to ensure that Northern Uganda’s youth are not left behind in a digitising economy, but positioned to participate, compete, and rebuild their futures with resilience and purpose.'],
            ],
        ],
        // ------------------------------------------------------------------ contact teaser
        [
            'key' => 'contact', 'label' => 'Contact teaser', 'toggle' => true,
            'help' => 'A short "get in touch" band. The contact details come from Settings > Contact details; the button leads to the Contact page.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Contact Us'],
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
