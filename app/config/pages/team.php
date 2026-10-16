<?php
/**
 * Registry entry: TEAM (/team) - OWNED BY B7 "org-public".
 * The people themselves (names, roles, photos, bios, links) live in the `team_members` table (admin: Team). This file holds
 * the COPY AROUND them: headings, intros, button labels, empty state and SEO. Wording is taken from the live About page
 * ("Governance" / "Meet our Board of Directors") and the Involvement page ("Volunteer With Us"); nothing about individual
 * people is invented - the owner adds the real team in the admin.
 */
return [
    'slug'  => 'team',
    'title' => 'Our Team',
    'route' => '/team',
    'help'  => 'Board of directors, staff, advisors and volunteers. The people are managed under Team in the admin menu; the headings and texts on this page are edited here.',
    'nav'   => [
        'label' => 'Our Team', 'order' => 21, 'header' => true, 'footer' => true, 'parent' => 'about',
    ],
    'seo'   => [
        'title'       => 'Our Team',
        'description' => 'Meet the people behind Lumina Africa Foundation, a registered community-based non-profit organisation in Gulu City, Northern Uganda.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner', 'help' => 'The title area at the top of the page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Governance', 'maxlength' => 80],
                ['key' => 'title', 'label' => 'Page heading', 'type' => 'text', 'default' => 'Our Team', 'maxlength' => 120],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'The Foundation is governed by a Board of Directors who provide oversight and strategic direction, supported by program coordinators and volunteers.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg', 'alt' => '',
                    'help' => 'Decorative background behind the title (covered by a dark veil so the text stays readable). Clear it to get the plain navy banner.'],
            ],
        ],
        [
            'key' => 'board', 'label' => 'Board of Directors', 'toggle' => true,
            'help' => 'Shown only when at least one active member of the group "Board of Directors" exists (Team > add a member).',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => '', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Meet Our Board of Directors', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'staff', 'label' => 'Staff', 'toggle' => true,
            'help' => 'Shown only when at least one active member of the group "Staff" exists.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => '', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Staff', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'advisors', 'label' => 'Advisors', 'toggle' => true,
            'help' => 'Shown only when at least one active member of the group "Advisors" exists.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => '', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Advisors', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'volunteers', 'label' => 'Volunteers', 'toggle' => true,
            'help' => 'Shown only when at least one active member of the group "Volunteers" exists.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => '', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Volunteers', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'labels', 'label' => 'Button and link labels', 'help' => 'Small texts used on the member cards and the biography pop-up.',
            'fields' => [
                ['key' => 'read_bio', 'label' => 'Button: read the full biography', 'type' => 'text', 'default' => 'Read bio', 'maxlength' => 40],
                ['key' => 'close', 'label' => 'Pop-up close button (read by screen readers)', 'type' => 'text', 'default' => 'Close', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'join', 'label' => 'Volunteer call-to-action', 'toggle' => true,
            'help' => 'A short band below the team inviting visitors to get involved.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Volunteer With Us', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Give your time, skills, and passion to impact lives directly.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Get Involved', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement'],
            ],
        ],
        [
            'key' => 'empty', 'label' => 'Message shown while the team list is empty', 'help' => 'Visitors see this until the first team member is added.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Content coming soon', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our team profiles will appear here soon. In the meantime, learn more about who we are and what we do.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'About Us', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/about'],
                ['key' => 'cta2_label', 'label' => 'Second button: text (optional)', 'type' => 'text', 'default' => 'Contact Us', 'maxlength' => 60],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
    ],
];
