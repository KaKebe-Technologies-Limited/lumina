<?php
/**
 * Registry entry (VIRTUAL, no URL): NEWSLETTER messages and the unsubscribe page. OWNER: B2 "web-content".
 * The sign-up boxes themselves live on their pages (footer = Site-wide content, Blog, Activities); this holds the messages
 * visitors see after submitting them and the one-click unsubscribe page reached from an email link.
 */
return [
    'slug'    => 'site_newsletter',
    'title'   => 'Newsletter messages & unsubscribe page',
    'virtual' => true,
    'help'    => 'The messages shown after someone subscribes, and the wording of the unsubscribe page.',
    'nav'     => ['label' => 'Newsletter messages', 'order' => 9020],
    'seo'     => ['title' => 'Newsletter'],
    'sections' => [
        [
            'key' => 'messages', 'label' => 'Messages after subscribing',
            'fields' => [
                ['key' => 'success', 'label' => 'Subscribed successfully', 'type' => 'textarea', 'rows' => 2, 'default' => 'Thank you for subscribing. You will receive our latest news and updates.'],
                ['key' => 'invalid', 'label' => 'Invalid email address', 'type' => 'textarea', 'rows' => 2, 'default' => 'Please enter a valid email address and try again.'],
            ],
        ],
        [
            'key' => 'unsubscribe', 'label' => 'Unsubscribe page',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Unsubscribe'],
                ['key' => 'text', 'label' => 'Question', 'type' => 'textarea', 'rows' => 3, 'default' => 'Do you want to stop receiving our news and updates? You can change your mind at any time.'],
                ['key' => 'button', 'label' => 'Confirm button', 'type' => 'text', 'default' => 'Yes, unsubscribe me'],
                ['key' => 'done_title', 'label' => 'After unsubscribing: heading', 'type' => 'text', 'default' => 'You have been unsubscribed'],
                ['key' => 'done_text', 'label' => 'After unsubscribing: text', 'type' => 'textarea', 'rows' => 3, 'default' => 'You will not receive our news and updates any more. We are sorry to see you go.'],
                ['key' => 'resubscribe_button', 'label' => 'Subscribe-again button', 'type' => 'text', 'default' => 'I changed my mind, subscribe me again'],
                ['key' => 'back_title', 'label' => 'After subscribing again: heading', 'type' => 'text', 'default' => 'Welcome back'],
                ['key' => 'back_text', 'label' => 'After subscribing again: text', 'type' => 'textarea', 'rows' => 3, 'default' => 'You are subscribed again and will receive our news and updates.'],
                ['key' => 'home_label', 'label' => 'Home button', 'type' => 'text', 'default' => 'Back to the home page'],
            ],
        ],
    ],
];
