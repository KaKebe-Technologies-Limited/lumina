<?php
/**
 * Registry entry: IMPACT STORIES (/stories, /stories/{slug}) - OWNED BY B7 "org-public".
 * The stories live in the `stories` table (admin: Stories). A story is only ever shown publicly when the owner has ticked
 * "consent confirmed" for it - the website enforces this in code, whatever the page settings are.
 * This file holds the copy around the stories. The intro wording is taken from the live Home page ("Background and Rationale").
 * No story, person or quote is invented: the list is empty until the owner publishes real, consented stories.
 */
return [
    'slug'  => 'stories',
    'title' => 'Impact Stories',
    'route' => '/stories',
    'help'  => 'Stories of the people and communities we work with. Write the stories under Stories in the admin menu (a story needs "consent confirmed" before it appears); the headings and texts on this page are edited here.',
    'nav'   => [
        'label' => 'Impact Stories', 'order' => 72, 'header' => true, 'footer' => true, 'parent' => 'blog',
    ],
    'seo'   => [
        'title'       => 'Impact Stories',
        'description' => 'Stories from Lumina Africa Foundation about digital skills, income and dignity for young people in Northern Uganda.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner', 'help' => 'The title area at the top of the page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Our Impact', 'maxlength' => 80],
                ['key' => 'title', 'label' => 'Page heading', 'type' => 'text', 'default' => 'Impact Stories', 'maxlength' => 120],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'Small digital opportunities can unlock income. Income restores confidence. Confidence rebuilds dignity.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '',
                    'help' => 'Decorative background behind the title. Leave empty for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'list', 'label' => 'Story list', 'help' => 'The grid of stories.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading (optional)', 'type' => 'text', 'default' => '', 'maxlength' => 120],
                ['key' => 'sr_title', 'label' => 'Heading for screen readers (not shown on screen)', 'type' => 'text', 'default' => 'All stories', 'maxlength' => 80, 'help' => 'Used when the heading above is empty, so the page keeps a correct heading order.'],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
                ['key' => 'cta', 'label' => 'Link on a story card', 'type' => 'text', 'default' => 'Read the story', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'consent', 'label' => 'Consent note', 'toggle' => true,
            'help' => 'A reassurance shown under the list. It is true by design: the website only shows stories marked "consent confirmed".',
            'fields' => [
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Every story on this page is shared with the consent of the people it features.'],
            ],
        ],
        [
            'key' => 'detail', 'label' => 'Single story page', 'help' => 'Labels on the page of one story.',
            'fields' => [
                ['key' => 'back', 'label' => 'Link: back to the list', 'type' => 'text', 'default' => 'All stories', 'maxlength' => 40],
                ['key' => 'more_title', 'label' => 'Heading above other stories', 'type' => 'text', 'default' => 'More stories', 'maxlength' => 80],
                ['key' => 'published', 'label' => 'Label before the publication date', 'type' => 'text', 'default' => 'Published', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'join', 'label' => 'Get involved call-to-action', 'toggle' => true,
            'help' => 'A band below the stories inviting visitors to take part.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Join Us Today', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Your support fuels transformation. Whether you give your time, resources, or your voice, you are building the Africa we all dream of.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Get Involved', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
        [
            'key' => 'empty', 'label' => 'Message shown while there are no stories', 'help' => 'Visitors see this until the first consented story is published.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Content coming soon', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our impact stories will be shared here soon. In the meantime, learn about the programs that make them possible.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'View Available Programs', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/programs'],
            ],
        ],
    ],
];
