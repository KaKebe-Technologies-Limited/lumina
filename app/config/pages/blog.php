<?php
/**
 * Registry entry: BLOG (listing + post template labels). OWNER: B2 "web-content".
 * Wording: https://www.luminaafricafoundation.com/blog ("Latest Updates", "Subscribe for Updates", "Gallery", "Contact Us").
 * The posts themselves are written in the admin under Content > Posts (type: blog). This page holds the copy AROUND them.
 */
return [
    'slug'  => 'blog',
    'title' => 'Blog',
    'route' => '/blog',
    'help'  => 'The Blog page: heading, intro, subscribe box, gallery teaser and the labels used on every post. Write the posts themselves under Content > Posts.',
    'nav'   => ['label' => 'Blog', 'order' => 70, 'header' => true, 'footer' => true, 'parent' => null],
    'seo'   => [
        'title'       => 'Latest News & Events Updates',
        'description' => 'Stay informed about our workshops, events, and initiatives for community empowerment.',
        'og_image'    => 'assets/img/site/photo-04.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner', 'help' => 'The heading at the top of the Blog page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Latest Updates', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'Stay informed about our workshops, events, and initiatives for community empowerment.'],
            ],
        ],
        [
            'key' => 'list', 'label' => 'Post list', 'help' => 'Labels around the list of posts and what visitors see when there are no posts yet.',
            'fields' => [
                ['key' => 'sr_title', 'label' => 'Heading for screen readers (not shown on screen)', 'type' => 'text', 'default' => 'All posts', 'help' => 'Keeps a correct heading order above the post cards.'],
                ['key' => 'filter_label', 'label' => 'Category filter: accessible label', 'type' => 'text', 'default' => 'Filter by category', 'help' => 'Read out by screen readers; not shown on screen.'],
                ['key' => 'all_label', 'label' => 'Category filter: "all posts" button', 'type' => 'text', 'default' => 'All'],
                ['key' => 'empty_title', 'label' => 'No posts yet: heading', 'type' => 'text', 'default' => 'No updates yet'],
                ['key' => 'empty_text', 'label' => 'No posts yet: text', 'type' => 'textarea', 'rows' => 3, 'default' => 'We have not published any updates yet. Please check back soon, or subscribe below to be told when we do.'],
                ['key' => 'empty_category_text', 'label' => 'No posts in a category: text', 'type' => 'textarea', 'rows' => 2, 'default' => 'There are no updates in this category yet.'],
                ['key' => 'empty_button_label', 'label' => 'No posts in a category: button to see all', 'type' => 'text', 'default' => 'See all updates'],
            ],
        ],
        [
            'key' => 'subscribe', 'label' => 'Subscribe box', 'toggle' => true, 'help' => 'Newsletter sign-up shown under the list of posts.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Subscribe for Updates'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4, 'default' => 'Stay informed about our latest events and news. We are continuously updating our offerings to ensure that you are always in the loop. Don’t miss out on the exciting activities and announcements that we have in store for you!'],
                ['key' => 'placeholder', 'label' => 'Email box: placeholder', 'type' => 'text', 'default' => 'Your Email Address'],
                ['key' => 'button', 'label' => 'Button label', 'type' => 'text', 'default' => 'Subscribe for Updates'],
            ],
        ],
        [
            'key' => 'gallery', 'label' => 'Gallery teaser', 'toggle' => true,
            'help' => 'A small strip of recent gallery pictures. It is hidden automatically until pictures have been added to the Gallery.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Gallery'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 5, 'default' => 'Explore our latest events and initiatives in pictures. Join us as we showcase a variety of community activities, highlights from recent gatherings, and behind-the-scenes moments that capture the essence of our organization. Whether it’s a memorable workshop, a lively festival, or a significant collaboration, we invite you to immerse yourself in these visual stories that reflect our commitment and vibrancy.'],
                ['key' => 'button_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'View the gallery'],
                ['key' => 'button_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/gallery'],
            ],
        ],
        [
            'key' => 'contact', 'label' => 'Contact teaser', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Contact Us'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Get in touch for inquiries about our news, events, and blog updates.'],
                ['key' => 'button_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Contact Us'],
                ['key' => 'button_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'post', 'label' => 'Single post page: labels', 'help' => 'Small pieces of text shown on every individual post.',
            'fields' => [
                ['key' => 'back_label', 'label' => 'Link back to the list', 'type' => 'text', 'default' => 'Back to all updates'],
                ['key' => 'by_label', 'label' => 'Author prefix', 'type' => 'text', 'default' => 'By'],
                ['key' => 'share_label', 'label' => 'Share heading', 'type' => 'text', 'default' => 'Share this update'],
                ['key' => 'share_on', 'label' => 'Share link: accessible label', 'type' => 'text', 'default' => 'Share on {network}', 'help' => '{network} is replaced by Facebook, X, LinkedIn or WhatsApp.'],
                ['key' => 'copy_label', 'label' => 'Copy-link button', 'type' => 'text', 'default' => 'Copy link'],
                ['key' => 'copied_label', 'label' => 'Copy-link button: after copying', 'type' => 'text', 'default' => 'Link copied'],
                ['key' => 'older_label', 'label' => 'Older post link', 'type' => 'text', 'default' => 'Older update'],
                ['key' => 'newer_label', 'label' => 'Newer post link', 'type' => 'text', 'default' => 'Newer update'],
                ['key' => 'related_title', 'label' => 'Related posts heading', 'type' => 'text', 'default' => 'More updates'],
            ],
        ],
    ],
];
