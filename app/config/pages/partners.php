<?php
/**
 * Registry entry: PARTNERS (/partners) - OWNED BY B7 "org-public".
 * The organisations themselves (name, logo, website, description, type) live in the `partners` table (admin: Partners).
 * This file holds the copy around them. Wording comes from the live Involvement page ("Partner With Us", the partner FAQ).
 * No partner, funder or donor is named here: the owner adds the real ones in the admin.
 */
return [
    'slug'  => 'partners',
    'title' => 'Partners',
    'route' => '/partners',
    'help'  => 'Partners, funders, donors and collaborators. The organisations are managed under Partners in the admin menu; the headings and texts on this page are edited here.',
    'nav'   => [
        'label' => 'Partners', 'order' => 22, 'header' => true, 'footer' => true, 'parent' => 'about',
    ],
    'seo'   => [
        'title'       => 'Our Partners',
        'description' => 'Organisations that work with Lumina Africa Foundation to develop sustainable programs for vulnerable youth in Northern Uganda.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner', 'help' => 'The title area at the top of the page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Together we can achieve more', 'maxlength' => 80],
                ['key' => 'title', 'label' => 'Page heading', 'type' => 'text', 'default' => 'Our Partners', 'maxlength' => 120],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'We collaborate with NGOs, businesses, academic institutions, and community groups that believe in sustainable development and social impact.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '',
                    'help' => 'Decorative background behind the title. Leave empty for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'partner', 'label' => 'Group: Partners', 'toggle' => true,
            'help' => 'Shown only when at least one active organisation of the type "Partner" exists.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Partners', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'funder', 'label' => 'Group: Funders', 'toggle' => true,
            'help' => 'Shown only when at least one active organisation of the type "Funder" exists.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Funders', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'donor', 'label' => 'Group: Donors', 'toggle' => true,
            'help' => 'Shown only when at least one active organisation of the type "Donor" exists.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Donors', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'collaborator', 'label' => 'Group: Collaborators', 'toggle' => true,
            'help' => 'Shown only when at least one active organisation of the type "Collaborator" exists.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Collaborators', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'labels', 'label' => 'Link labels', 'help' => 'Small texts used on the partner cards.',
            'fields' => [
                ['key' => 'visit', 'label' => 'Link: visit the partner\'s website', 'type' => 'text', 'default' => 'Visit website', 'maxlength' => 60],
            ],
        ],
        [
            'key' => 'join', 'label' => 'Become a partner call-to-action', 'toggle' => true,
            'help' => 'A band below the list inviting organisations to get in touch.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Partner With Us', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4,
                    'default' => 'We welcome collaborations with organizations, businesses, and institutions that share our vision. By partnering with Lumina Africa Foundation, you join us in developing sustainable programs that create long-term community impact.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Partner With Us', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
        [
            'key' => 'empty', 'label' => 'Message shown while the partner list is empty', 'help' => 'Visitors see this until the first partner is added.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Content coming soon', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'The organisations we work with will be listed here soon. If your organisation would like to collaborate with us, we would love to hear from you.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Partner With Us', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
    ],
];
