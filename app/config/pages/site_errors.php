<?php
/**
 * Registry entry (VIRTUAL, no URL): ERROR PAGES (404, 403, 405, 419, 429, 500 and the maintenance page). OWNER: B2 "web-content".
 * Appears in the admin Pages list as "Error pages & maintenance message". Default wording matches the built-in messages.
 */
return [
    'slug'    => 'site_errors',
    'title'   => 'Error pages & maintenance message',
    'virtual' => true,
    'help'    => 'The wording of the "page not found", "access denied", "something went wrong" and maintenance pages.',
    'nav'     => ['label' => 'Error pages', 'order' => 9010],
    'seo'     => ['title' => 'Lumina Africa Foundation'],
    'sections' => [
        [
            'key' => 'e404', 'label' => 'Page not found (404)',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Page not found'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'We could not find the page you were looking for. It may have moved or no longer exists.'],
                ['key' => 'links_title', 'label' => 'Suggested links heading', 'type' => 'text', 'default' => 'You might be looking for'],
            ],
        ],
        [
            'key' => 'e403', 'label' => 'Access denied (403)',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Access denied'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'You do not have permission to view this page.'],
            ],
        ],
        [
            'key' => 'e405', 'label' => 'Action not available (405)',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Method not allowed'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'That action is not available here.'],
            ],
        ],
        [
            'key' => 'e419', 'label' => 'Page expired (form session timed out)',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Page expired'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Your session timed out or the form was submitted from an old page. Please go back, refresh and try again.'],
            ],
        ],
        [
            'key' => 'e429', 'label' => 'Too many requests (429)',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Too many requests'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'You have made too many requests in a short time. Please wait a moment and try again.'],
            ],
        ],
        [
            'key' => 'e500', 'label' => 'Something went wrong (500)',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Something went wrong'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'An unexpected error occurred on our side. Please try again shortly.'],
            ],
        ],
        [
            'key' => 'maintenance', 'label' => 'Maintenance page (503)', 'help' => 'Shown to visitors while the site is switched to maintenance mode (APP_MAINTENANCE in the .env file). Signed-in staff and the admin area keep working.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Back soon'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'The website is undergoing scheduled maintenance. Please check back shortly.'],
                ['key' => 'contact_title', 'label' => 'Contact heading', 'type' => 'text', 'default' => 'Need to reach us?'],
            ],
        ],
        [
            'key' => 'form', 'label' => 'Messages shared by every form',
            'help' => 'Wording visitors see when a form has mistakes or a file cannot be accepted. The words in {curly brackets} are filled in automatically.',
            'fields' => [
                ['key' => 'validation', 'label' => 'Banner above a form that has mistakes', 'type' => 'text', 'default' => 'Please correct the highlighted fields and try again.'],
                ['key' => 'honeypot_ok', 'label' => 'Message shown to automated spam submissions', 'type' => 'text', 'default' => 'Thank you. Your submission has been received.', 'help' => 'Real visitors never see this. Keep it friendly and neutral.'],
                ['key' => 'too_large', 'label' => 'Files are too big for the server (total upload)', 'type' => 'text', 'default' => 'The files you sent are larger than this server accepts (limit: {max}). Please choose smaller files and try again.'],
                ['key' => 'upload_type', 'label' => 'File type not allowed', 'type' => 'text', 'default' => 'That file type is not allowed. Allowed: {types}.'],
                ['key' => 'upload_too_large', 'label' => 'File too large', 'type' => 'text', 'default' => 'That file is too large (maximum {max}).'],
                ['key' => 'upload_invalid', 'label' => 'File does not match its type', 'type' => 'text', 'default' => 'That file does not look like a valid .{ext} document.'],
                ['key' => 'upload_server_limit', 'label' => 'File larger than the server allows', 'type' => 'text', 'default' => 'That file is larger than the server allows.'],
                ['key' => 'image_unprocessable', 'label' => 'Picture could not be processed', 'type' => 'text', 'default' => 'That image could not be processed. Please re-save it from a photo editor and try again.'],
            ],
        ],
        [
            'key' => 'common', 'label' => 'Buttons and labels shared by all error pages',
            'fields' => [
                ['key' => 'home_label', 'label' => 'Home button', 'type' => 'text', 'default' => 'Back to the home page'],
                ['key' => 'back_label', 'label' => 'Go-back button', 'type' => 'text', 'default' => 'Go back'],
                ['key' => 'contact_label', 'label' => 'Contact button', 'type' => 'text', 'default' => 'Contact us'],
                ['key' => 'contact_url', 'label' => 'Contact button: link', 'type' => 'url', 'default' => '/contact'],
                ['key' => 'code_label', 'label' => 'Error code label (screen readers)', 'type' => 'text', 'default' => 'Error code'],
            ],
        ],
    ],
];
