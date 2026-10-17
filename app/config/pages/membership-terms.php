<?php
/**
 * Registry entry: MEMBERSHIP TERMS (page /membership/terms, linked from the registration form's "I agree" tick box).
 * OWNER: architect. The default text below is a neutral PLACEHOLDER written for the foundation to replace with its own terms: it makes
 * no legal claim and states only how the registration process works. Edit it in Admin > Pages > Membership terms.
 * FLAGGED FOR OWNER / LEGAL REVIEW before membership opens (see docs/MEMBERSHIP.md).
 */
return [
    'slug'  => 'membership-terms',
    'title' => 'Membership terms',
    'route' => '/membership/terms',
    'help'  => 'The terms that applicants agree to when they register. The text below is a PLACEHOLDER: replace it with the foundation\'s own membership terms (ideally reviewed by a legal adviser) before opening registration.',
    'nav'   => ['label' => 'Membership terms', 'order' => 53, 'header' => false, 'footer' => false, 'parent' => null],
    'seo'   => [
        'title'       => 'Membership Terms',
        'description' => 'The terms of membership of Lumina Africa Foundation.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Membership'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Membership Terms', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'Please read these terms before you register as a member.'],
            ],
        ],
        [
            'key' => 'terms', 'label' => 'The terms', 'help' => 'Rich text. Replace the placeholder with the foundation\'s real membership terms.',
            'fields' => [
                ['key' => 'updated_label', 'label' => 'Last-updated label', 'type' => 'text', 'default' => 'Last updated'],
                ['key' => 'updated', 'label' => 'Last-updated date', 'type' => 'text', 'default' => '', 'help' => 'For example "1 November 2026". Leave empty to hide the line.'],
                ['key' => 'body', 'label' => 'Terms', 'type' => 'richtext',
                    'default' => '<p><strong>Placeholder text.</strong> The foundation will replace this page with its own membership terms.</p>'
                        . '<ul>'
                        . '<li>Membership applications are reviewed by the foundation. An application is not a guarantee of membership.</li>'
                        . '<li>Any membership fee for the type you choose is shown before you apply and is confirmed when your application is reviewed.</li>'
                        . '<li>People under 18 need the details and consent of a parent or guardian.</li>'
                        . '<li>The personal information you give is used as described in the privacy notice.</li>'
                        . '</ul>'],
                ['key' => 'privacy_label', 'label' => 'Link to the privacy notice: text', 'type' => 'text', 'default' => 'Read our privacy notice'],
                ['key' => 'back_label', 'label' => 'Button back to the form: text', 'type' => 'text', 'default' => 'Back to registration'],
            ],
        ],
    ],
];
