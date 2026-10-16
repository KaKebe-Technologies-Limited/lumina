<?php
/**
 * Registry entry: EVENTS (/events, /events/{slug}) - OWNED BY B7 "org-public".
 * The events themselves (title, date, place, description, photo, capacity, registration settings) live in the `events` table
 * (admin: Events). Visitors can register on the website when registration is enabled for an event (table `event_registrations`).
 * This file holds the copy around them. The intro wording comes from the live Blog/Updates page; no event is invented.
 *
 * Placeholders usable in the texts that mention them: {n} (number), {name}, {event}, {email}, {date}, {location}, {url}, {site}, {details}.
 */
return [
    'slug'  => 'events',
    'title' => 'Events',
    'route' => '/events',
    'help'  => 'Upcoming and past events with on-site registration and add-to-calendar. Create the events under Events in the admin menu; the headings, form labels and messages are edited here.',
    'nav'   => [
        'label' => 'Events', 'order' => 61, 'header' => true, 'footer' => true, 'parent' => 'activities',
    ],
    'seo'   => [
        'title'       => 'Events',
        'description' => 'Workshops, events and initiatives for community empowerment by Lumina Africa Foundation in Gulu City, Northern Uganda.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner', 'help' => 'The title area at the top of the page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Latest Updates', 'maxlength' => 80],
                ['key' => 'title', 'label' => 'Page heading', 'type' => 'text', 'default' => 'Events', 'maxlength' => 120],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'Stay informed about our workshops, events, and initiatives for community empowerment.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg', 'alt' => '',
                    'help' => 'Decorative background behind the title (covered by a dark veil so the text stays readable). Clear it to get the plain navy banner.'],
            ],
        ],
        [
            'key' => 'upcoming', 'label' => 'Upcoming events', 'help' => 'The list of events that have not ended yet.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Join us', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Upcoming Events', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
                ['key' => 'none_title', 'label' => 'Heading when there is no upcoming event', 'type' => 'text', 'default' => 'No upcoming events at the moment', 'maxlength' => 120],
                ['key' => 'none_text', 'label' => 'Text when there is no upcoming event', 'type' => 'textarea', 'rows' => 2, 'default' => 'Please check back soon for new events and announcements.'],
            ],
        ],
        [
            'key' => 'past', 'label' => 'Past events', 'toggle' => true, 'help' => 'Events that have already taken place (newest first). Shown only when there are some.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => '', 'maxlength' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Past Events', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Intro (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
            ],
        ],
        [
            'key' => 'labels', 'label' => 'Card labels', 'help' => 'Texts used on the event cards.',
            'fields' => [
                ['key' => 'card_cta', 'label' => 'Link on an upcoming event card', 'type' => 'text', 'default' => 'View details', 'maxlength' => 40],
                ['key' => 'card_cta_past', 'label' => 'Link on a past event card', 'type' => 'text', 'default' => 'View event', 'maxlength' => 40],
                ['key' => 'past_badge', 'label' => 'Small badge on a past event photo', 'type' => 'text', 'default' => 'Past event', 'maxlength' => 40, 'help' => 'Leave empty to hide the badge.'],
            ],
        ],
        [
            'key' => 'detail', 'label' => 'Single event page', 'help' => 'Labels on the page of one event.',
            'fields' => [
                ['key' => 'back', 'label' => 'Link: back to the list', 'type' => 'text', 'default' => 'All events', 'maxlength' => 40],
                ['key' => 'about', 'label' => 'Heading above the description', 'type' => 'text', 'default' => 'About this event', 'maxlength' => 80],
                ['key' => 'when', 'label' => 'Label: date and time', 'type' => 'text', 'default' => 'Date and time', 'maxlength' => 40],
                ['key' => 'where', 'label' => 'Label: location', 'type' => 'text', 'default' => 'Location', 'maxlength' => 40],
                ['key' => 'calendar', 'label' => 'Button: add to calendar', 'type' => 'text', 'default' => 'Add to calendar', 'maxlength' => 40,
                    'help' => 'Downloads an .ics file that opens in Google, Apple and Outlook calendars.'],
                ['key' => 'ended', 'label' => 'Notice for an event that has already taken place', 'type' => 'text', 'default' => 'This event has ended.', 'maxlength' => 120],
                ['key' => 'places_left', 'label' => 'Remaining places', 'type' => 'text', 'default' => '{n} places left', 'maxlength' => 60, 'help' => '{n} is replaced by the number of free places (shown only when the event has a capacity).'],
                ['key' => 'more_title', 'label' => 'Heading above other upcoming events', 'type' => 'text', 'default' => 'More upcoming events', 'maxlength' => 80],
            ],
        ],
        [
            'key' => 'register', 'label' => 'Registration form', 'help' => 'The sign-up form on an event page (shown when registration is enabled for that event).',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Register for this event', 'maxlength' => 120],
                ['key' => 'intro', 'label' => 'Text above the form (optional)', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
                ['key' => 'name_label', 'label' => 'Field: full name', 'type' => 'text', 'default' => 'Full name', 'maxlength' => 60],
                ['key' => 'email_label', 'label' => 'Field: email address', 'type' => 'text', 'default' => 'Email address', 'maxlength' => 60],
                ['key' => 'phone_label', 'label' => 'Field: phone number', 'type' => 'text', 'default' => 'Phone number (optional)', 'maxlength' => 60],
                ['key' => 'organisation_label', 'label' => 'Field: organisation', 'type' => 'text', 'default' => 'Organisation (optional)', 'maxlength' => 60],
                ['key' => 'notes_label', 'label' => 'Field: notes', 'type' => 'text', 'default' => 'Anything we should know? (optional)', 'maxlength' => 80],
                ['key' => 'submit', 'label' => 'Button: send the registration', 'type' => 'text', 'default' => 'Register', 'maxlength' => 40],
                ['key' => 'sending', 'label' => 'Button text while sending', 'type' => 'text', 'default' => 'Sending...', 'maxlength' => 40],
                ['key' => 'external_label', 'label' => 'Button: external registration link', 'type' => 'text', 'default' => 'Register online', 'maxlength' => 60,
                    'help' => 'Used when the event has its own registration link (set in Events).'],
                ['key' => 'full_title', 'label' => 'Heading when all places are taken', 'type' => 'text', 'default' => 'Registration is full', 'maxlength' => 120],
                ['key' => 'full_text', 'label' => 'Text when all places are taken', 'type' => 'textarea', 'rows' => 2, 'default' => 'All places for this event have been taken.'],
                ['key' => 'closed_text', 'label' => 'Text when registration is closed (the event is over)', 'type' => 'textarea', 'rows' => 2, 'default' => 'Registration for this event is closed.'],
                ['key' => 'success_title', 'label' => 'Confirmation heading', 'type' => 'text', 'default' => 'Thank you for registering', 'maxlength' => 120],
                ['key' => 'success_text', 'label' => 'Confirmation text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Thank you, {name}. Your registration for {event} has been received.',
                    'help' => 'Placeholders: {name}, {event}.'],
                ['key' => 'success_email', 'label' => 'Confirmation: email notice', 'type' => 'text', 'default' => 'A confirmation email is on its way to {email}.', 'maxlength' => 160,
                    'help' => 'Shown only when the confirmation email could be sent. Placeholder: {email}.'],
            ],
        ],
        [
            'key' => 'messages', 'label' => 'Form error messages', 'help' => 'Short messages shown next to a field that needs attention.',
            'fields' => [
                ['key' => 'name_required', 'label' => 'Name is missing', 'type' => 'text', 'default' => 'Please enter your full name.', 'maxlength' => 160],
                ['key' => 'email_required', 'label' => 'Email is missing', 'type' => 'text', 'default' => 'Please enter your email address.', 'maxlength' => 160],
                ['key' => 'email_invalid', 'label' => 'Email is not valid', 'type' => 'text', 'default' => 'Please enter a valid email address.', 'maxlength' => 160],
                ['key' => 'phone_invalid', 'label' => 'Phone number is not valid', 'type' => 'text', 'default' => 'Please enter a valid phone number.', 'maxlength' => 160],
                ['key' => 'too_long', 'label' => 'An entry is too long', 'type' => 'text', 'default' => 'This entry is too long.', 'maxlength' => 160],
                ['key' => 'full', 'label' => 'Event is full', 'type' => 'text', 'default' => 'Sorry, all places for this event have been taken.', 'maxlength' => 200],
                ['key' => 'closed', 'label' => 'Registration is closed', 'type' => 'text', 'default' => 'Registration for this event is closed.', 'maxlength' => 200],
            ],
        ],
        [
            'key' => 'email', 'label' => 'Confirmation email to the person who registers', 'help' => 'Plain-text email sent after a successful registration.',
            'fields' => [
                ['key' => 'subject', 'label' => 'Subject', 'type' => 'text', 'default' => 'You are registered: {event}', 'maxlength' => 160, 'help' => 'Placeholders: {event}, {site}.'],
                ['key' => 'name_fallback', 'label' => 'Word used instead of the name', 'type' => 'text', 'default' => 'there', 'maxlength' => 40, 'help' => 'Used in "Hello {name}" when the name typed by the visitor contains no usable letters. The visitor\'s name is shortened and cleaned (no links) before it is put in this email.'],
                ['key' => 'body', 'label' => 'Message', 'type' => 'textarea', 'rows' => 9, 'maxlength' => 2000,
                    'default' => "Hello {name},\n\nThank you for registering for {event}.\n\n{details}\n\nMore information: {url}\n\n{site}",
                    'help' => 'Placeholders: {name}, {event}, {details} (date and location lines), {url} (event page), {site}.'],
            ],
        ],
        [
            'key' => 'empty', 'label' => 'Message shown while there are no events', 'help' => 'Visitors see this until the first event is added.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Content coming soon', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our events will be announced here soon. In the meantime, see what we have been doing in our community.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'View Our Activities', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/activities'],
                ['key' => 'cta2_label', 'label' => 'Second button: text (optional)', 'type' => 'text', 'default' => 'Subscribe for Updates', 'maxlength' => 60],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '/blog#subscribe'],
            ],
        ],
    ],
];
