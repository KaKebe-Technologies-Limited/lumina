<?php
/**
 * Registry entry: GALLERY (albums filter + lightbox). OWNER: B2 "web-content".
 * Wording: the "Gallery" block of https://www.luminaafricafoundation.com/blog. Pictures are managed in the admin under Content > Gallery.
 */
return [
    'slug'  => 'gallery',
    'title' => 'Gallery',
    'route' => '/gallery',
    'help'  => 'The photo gallery page. Add and arrange the pictures themselves under Content > Gallery. The menu entry appears automatically once there is at least one picture.',
    'nav'   => [
        'label' => 'Gallery', 'order' => 62, 'header' => true, 'footer' => true, 'parent' => 'activities',
    ],
    'seo'   => [
        'title'       => 'Gallery',
        'description' => 'Explore our latest events and initiatives in pictures: community activities, highlights from recent gatherings and behind-the-scenes moments.',
        'og_image'    => 'assets/img/site/photo-06.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Gallery', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'Explore our latest events and initiatives in pictures.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg', 'alt' => '',
                    'help' => 'Decorative background behind the title (covered by a dark veil so the text stays readable). Clear it to get the plain navy banner.'],
            ],
        ],
        [
            'key' => 'intro', 'label' => 'Introduction', 'toggle' => true, 'help' => 'A short paragraph above the pictures.',
            'fields' => [
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 5, 'default' => 'Join us as we showcase a variety of community activities, highlights from recent gatherings, and behind-the-scenes moments that capture the essence of our organization. Whether it’s a memorable workshop, a lively festival, or a significant collaboration, we invite you to immerse yourself in these visual stories that reflect our commitment and vibrancy.'],
            ],
        ],
        [
            'key' => 'grid', 'label' => 'Pictures and albums', 'help' => 'Labels for the album filter and the "no pictures yet" message.',
            'fields' => [
                ['key' => 'filter_label', 'label' => 'Album filter: accessible label', 'type' => 'text', 'default' => 'Filter by album', 'help' => 'Read out by screen readers; not shown on screen.'],
                ['key' => 'all_label', 'label' => 'Album filter: "all pictures" button', 'type' => 'text', 'default' => 'All pictures'],
                ['key' => 'open_label', 'label' => 'Picture link: accessible label', 'type' => 'text', 'default' => 'View larger picture'],
                ['key' => 'empty_title', 'label' => 'No pictures yet: heading', 'type' => 'text', 'default' => 'Pictures are coming soon'],
                ['key' => 'empty_text', 'label' => 'No pictures yet: text', 'type' => 'textarea', 'rows' => 3, 'default' => 'We are preparing our gallery. Please check back soon to see our latest events and initiatives in pictures.'],
                ['key' => 'empty_cta_label', 'label' => 'No pictures yet: button text (optional)', 'type' => 'text', 'default' => 'View Our Activities'],
                ['key' => 'empty_cta_url', 'label' => 'No pictures yet: button link', 'type' => 'url', 'default' => '/activities'],
            ],
        ],
    ],
];
