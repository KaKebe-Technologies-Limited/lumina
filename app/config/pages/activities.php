<?php
/**
 * Registry entry: ACTIVITIES (listing + activity template labels). OWNER: B2 "web-content".
 * Wording: https://www.luminaafricafoundation.com/activities (see docs/live-snapshot/activities.txt).
 * Individual activities are written in the admin under Content > Activities; the "About Our Activities" story below
 * is the live site's own text and can be edited or hidden here.
 */
return [
    'slug'  => 'activities',
    'title' => 'Activities',
    'route' => '/activities',
    'help'  => 'The Activities page: heading, the featured story, the list of activities (written under Content > Activities), subscribe box and contact teaser.',
    'nav'   => ['label' => 'Activities', 'order' => 60, 'header' => true, 'footer' => true, 'parent' => null],
    'seo'   => [
        'title'       => 'Explore Our Exciting Activities and Updates',
        'description' => 'Discover all our activities and stay updated with the latest news and events from Lumina Africa Foundation.',
        'og_image'    => 'assets/img/site/photo-04.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Activities', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'Discover all our activities and stay updated with the latest news and events.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg', 'alt' => '',
                    'help' => 'Decorative background behind the title (covered by a dark veil so the text stays readable). Clear it to get the plain navy banner.'],
            ],
        ],
        [
            'key' => 'about', 'label' => 'About Our Activities (featured story)', 'toggle' => true,
            'help' => 'The featured story at the top of the page. Add photos in the gallery below, or hide the whole section once you no longer need it.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Featured story', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'About Our Activities'],
                ['key' => 'body', 'label' => 'Story', 'type' => 'richtext', 'default' => '<p>We are thrilled to share that our Director has joined the incredible team of the Delegation of the Northern Region Youth Parliament! He had the amazing opportunity to participate in a dynamic outreach event at the Wii Got Quarry Site in Gulu City, where he collaborated passionately with enthusiastic youth leaders from the EU Youth Sounding Board and the representatives of the 8th Northern Regional Youth Parliament. This exciting initiative aimed to empower and inspire our vibrant young participants!</p><p>We engaged the quarry youth in stimulating discussions about environmental sustainability and community development while providing them with essential safety gear to keep them safe and sound during all activities. Plus, in line with our commitment to fostering sustainability, the event was highlighted by the donation and planting of 20 tree seedlings! These seedlings symbolize hope for a brighter, greener future and deepen our understanding of the importance of environmental stewardship.</p><p>This incredible collective effort showcases our strong belief that young people can lead the charge for positive change in their communities, embracing active roles in environmental protection and social responsibility!</p>'],
                ['key' => 'image', 'label' => 'Photo beside the story', 'type' => 'image', 'default' => '', 'alt' => '', 'help' => 'Optional. Describe what the photo shows in the alt text.'],
                ['key' => 'gallery_title', 'label' => 'Photo gallery heading', 'type' => 'text', 'default' => 'Visit to Wii Quarry Site'],
                ['key' => 'gallery', 'label' => 'Photo gallery', 'type' => 'repeater', 'item_label' => 'caption', 'add_label' => 'Add a photo', 'max_rows' => 24,
                    'help' => 'Photos from the visit. They open in a larger viewer when clicked. Leave empty to hide the gallery.',
                    'fields' => [
                        ['key' => 'image', 'label' => 'Photo', 'type' => 'image'],
                        ['key' => 'caption', 'label' => 'Caption', 'type' => 'text'],
                    ],
                    'default' => []],
            ],
        ],
        [
            'key' => 'list', 'label' => 'List of activities', 'help' => 'Heading and labels around the activities you add under Content > Activities.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'What we do', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Latest Activities'],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'text', 'default' => ''],
                ['key' => 'empty_title', 'label' => 'No activities yet: heading', 'type' => 'text', 'default' => 'No activities to show yet'],
                ['key' => 'empty_text', 'label' => 'No activities yet: text', 'type' => 'textarea', 'rows' => 3, 'default' => 'We will share our activities here as soon as they are published. Please check back soon.'],
            ],
        ],
        [
            'key' => 'subscribe', 'label' => 'Subscribe box', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Newsletter', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Stay Updated with Activities'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Get the latest updates on our activities.'],
                ['key' => 'placeholder', 'label' => 'Email box: placeholder', 'type' => 'text', 'default' => 'Enter your email address'],
                ['key' => 'button', 'label' => 'Button label', 'type' => 'text', 'default' => 'Submit your details'],
            ],
        ],
        [
            'key' => 'contact', 'label' => 'Contact teaser', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Contact Us'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Get in touch for updates on our activities.'],
                ['key' => 'button_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Contact Us'],
                ['key' => 'button_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'detail', 'label' => 'Single activity page: labels',
            'fields' => [
                ['key' => 'back_label', 'label' => 'Link back to the list', 'type' => 'text', 'default' => 'Back to all activities'],
                ['key' => 'date_label', 'label' => 'Date label', 'type' => 'text', 'default' => 'Date'],
                ['key' => 'location_label', 'label' => 'Location label', 'type' => 'text', 'default' => 'Location'],
                ['key' => 'share_label', 'label' => 'Share heading', 'type' => 'text', 'default' => 'Share this activity'],
                ['key' => 'share_on', 'label' => 'Share link: accessible label', 'type' => 'text', 'default' => 'Share on {network}', 'help' => '{network} is replaced by Facebook, X, LinkedIn or WhatsApp.'],
                ['key' => 'copy_label', 'label' => 'Copy-link button', 'type' => 'text', 'default' => 'Copy link'],
                ['key' => 'copied_label', 'label' => 'Copy-link button: after copying', 'type' => 'text', 'default' => 'Link copied'],
                ['key' => 'more_title', 'label' => 'More activities heading', 'type' => 'text', 'default' => 'More activities'],
            ],
        ],
    ],
];
