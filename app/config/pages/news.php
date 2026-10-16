<?php
/**
 * Registry entry: NEWS & PRESS (/news, /news/{slug}, /press, /press/{slug}) - OWNED BY B7 "org-public".
 * One registry page covers BOTH listings: "News" (the foundation's own announcements, posts of type "news") and "Press"
 * (media coverage, posts of type "press", which can link out to the original article). The articles are written under
 * Posts in the admin menu. Blog posts have their own page (/blog).
 * Intro wording comes from the live Blog/Updates page. No article, headline or media outlet is invented.
 */
return [
    'slug'  => 'news',
    'title' => 'News & Press',
    'route' => '/news',
    'help'  => 'News announcements (/news) and press coverage (/press). Write the articles under Posts in the admin menu (choose the type News or Press); the headings and texts on both pages are edited here.',
    'nav'   => [
        'label' => 'News', 'order' => 71, 'header' => true, 'footer' => true, 'parent' => 'blog',
    ],
    'seo'   => [
        'title'       => 'News',
        'description' => 'The latest news and announcements from Lumina Africa Foundation, a registered community-based non-profit organisation in Gulu City, Northern Uganda.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'News page banner', 'help' => 'The title area at the top of the News page (/news).',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Latest Updates', 'maxlength' => 80],
                ['key' => 'title', 'label' => 'Page heading', 'type' => 'text', 'default' => 'News', 'maxlength' => 120],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'Stay informed about our latest events and news. We are continuously updating our offerings to ensure that you are always in the loop.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => '',
                    'help' => 'Decorative background behind the title (covered by a dark veil so the text stays readable). Clear it to get the plain navy banner.'],
            ],
        ],
        [
            'key' => 'list', 'label' => 'News list', 'help' => 'The grid of news articles.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading (optional)', 'type' => 'text', 'default' => '', 'maxlength' => 120],
                ['key' => 'sr_title', 'label' => 'Heading for screen readers (not shown on screen)', 'type' => 'text', 'default' => 'All articles', 'maxlength' => 80, 'help' => 'Used when the heading above is empty, so the page keeps a correct heading order.'],
                ['key' => 'cta', 'label' => 'Link on an article card', 'type' => 'text', 'default' => 'Read more', 'maxlength' => 40],
                ['key' => 'featured_label', 'label' => 'Small label on the newest article (large card)', 'type' => 'text', 'default' => 'Latest', 'maxlength' => 40, 'help' => 'Leave empty to hide the label. Used on the News and Press lists.'],
                ['key' => 'filter_label', 'label' => 'Category filter name (read by screen readers)', 'type' => 'text', 'default' => 'Filter by category', 'maxlength' => 80],
                ['key' => 'all_label', 'label' => 'Category filter: show everything', 'type' => 'text', 'default' => 'All', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'press_link', 'label' => 'News page: link to the Press page', 'toggle' => true,
            'help' => 'A band at the bottom of the News page pointing to press coverage. It is shown only when press articles exist.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'In the press', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'See how our work is covered in the media.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'View press coverage', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/press'],
            ],
        ],
        [
            'key' => 'press_hero', 'label' => 'Press page banner', 'help' => 'The title area at the top of the Press page (/press), plus its search-engine text.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Media', 'maxlength' => 80],
                ['key' => 'title', 'label' => 'Page heading', 'type' => 'text', 'default' => 'Press', 'maxlength' => 120],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'Media coverage and press mentions of Lumina Africa Foundation.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg', 'alt' => '',
                    'help' => 'Decorative background behind the title (covered by a dark veil so the text stays readable). Clear it to get the plain navy banner.'],
                ['key' => 'seo_title', 'label' => 'Search-engine title', 'type' => 'text', 'default' => 'Press', 'maxlength' => 70],
                ['key' => 'seo_description', 'label' => 'Search-engine description', 'type' => 'textarea', 'rows' => 2, 'maxlength' => 300,
                    'default' => 'Media coverage and press mentions of Lumina Africa Foundation, a registered community-based non-profit organisation in Gulu City, Northern Uganda.'],
                ['key' => 'cta', 'label' => 'Link on a press card', 'type' => 'text', 'default' => 'Read more', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'news_link', 'label' => 'Press page: link to the News page', 'toggle' => true,
            'help' => 'A band at the bottom of the Press page pointing to our own news. It is shown only when news articles exist.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'News from Lumina Africa Foundation', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Read our own announcements and updates.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'View news', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/news'],
            ],
        ],
        [
            'key' => 'article', 'label' => 'Single article page', 'help' => 'Labels on the page of one news or press article.',
            'fields' => [
                ['key' => 'back_news', 'label' => 'Link: back to the news list', 'type' => 'text', 'default' => 'All news', 'maxlength' => 40],
                ['key' => 'back_press', 'label' => 'Link: back to the press list', 'type' => 'text', 'default' => 'All press', 'maxlength' => 40],
                ['key' => 'published', 'label' => 'Label before the publication date', 'type' => 'text', 'default' => 'Published', 'maxlength' => 40],
                ['key' => 'by', 'label' => 'Label before the author', 'type' => 'text', 'default' => 'By', 'maxlength' => 40],
                ['key' => 'source', 'label' => 'Label before the media outlet (press)', 'type' => 'text', 'default' => 'Source', 'maxlength' => 40],
                ['key' => 'external_cta', 'label' => 'Button: read the original article (press)', 'type' => 'text', 'default' => 'Read the full article', 'maxlength' => 60],
                ['key' => 'more_news', 'label' => 'Heading above other news', 'type' => 'text', 'default' => 'More news', 'maxlength' => 80],
                ['key' => 'more_press', 'label' => 'Heading above other press', 'type' => 'text', 'default' => 'More press coverage', 'maxlength' => 80],
            ],
        ],
        [
            'key' => 'empty_news', 'label' => 'Message shown while there is no news', 'help' => 'Visitors to /news see this until the first news article is published.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Content coming soon', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our latest news and announcements will appear here soon. In the meantime, read our blog and see our activities.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Visit Our Blog', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/blog'],
                ['key' => 'cta2_label', 'label' => 'Second button: text (optional)', 'type' => 'text', 'default' => 'View Our Activities', 'maxlength' => 60],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '/activities'],
            ],
        ],
        [
            'key' => 'empty_press', 'label' => 'Message shown while there is no press coverage', 'help' => 'Visitors to /press see this until the first press article is published.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Content coming soon', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Press coverage will be listed here soon. In the meantime, read our blog and see our activities.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Visit Our Blog', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/blog'],
                ['key' => 'cta2_label', 'label' => 'Second button: text (optional)', 'type' => 'text', 'default' => 'View Our Activities', 'maxlength' => 60],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '/activities'],
            ],
        ],
    ],
];
