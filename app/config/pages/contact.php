<?php
/**
 * Registry entry: CONTACT. OWNER: B2 "web-content".
 * Wording: https://www.luminaafricafoundation.com/contact (see docs/live-snapshot/contact.txt).
 * Postal address, emails, main phone numbers, WhatsApp and social links come from Settings (Organisation-wide details);
 * only the surrounding copy, opening hours and the extra phone number shown on the live page live here.
 */
return [
    'slug'  => 'contact',
    'title' => 'Contact',
    'route' => '/contact',
    'help'  => 'The Contact page: heading, the contact form wording, office details, map and closing line. Address, emails, phone numbers, WhatsApp and social links are changed in Settings.',
    'nav'   => ['label' => 'Contact', 'order' => 90, 'header' => true, 'footer' => true, 'parent' => null],
    'seo'   => [
        'title'       => 'Contact Lumina Africa Foundation Today',
        'description' => 'Get in touch with Lumina Africa Foundation in Gulu City, Northern Uganda, to partner with us, volunteer, or learn more about our programs.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Contact Us', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 4, 'default' => 'We would love to hear from you! Whether you want to partner with us, volunteer, or learn more about our programs, our team is ready to connect with you.'],
            ],
        ],
        [
            'key' => 'form', 'label' => 'Contact form', 'help' => 'Headings, field labels, button and messages of the contact form. Messages are saved in Inbox > Messages and emailed to the notification address in Settings.',
            'fields' => [
                ['key' => 'title', 'label' => 'Form heading', 'type' => 'text', 'default' => 'Send us a message'],
                ['key' => 'intro', 'label' => 'Form intro', 'type' => 'textarea', 'rows' => 2, 'default' => ''],
                ['key' => 'name_label', 'label' => 'Name: label', 'type' => 'text', 'default' => 'Your Name'],
                ['key' => 'email_label', 'label' => 'Email: label', 'type' => 'text', 'default' => 'Your Email Address'],
                ['key' => 'phone_label', 'label' => 'Phone: label', 'type' => 'text', 'default' => 'Phone Number'],
                ['key' => 'subject_label', 'label' => 'Subject: label', 'type' => 'text', 'default' => 'Subject'],
                ['key' => 'message_label', 'label' => 'Message: label', 'type' => 'text', 'default' => 'Your Message'],
                ['key' => 'submit', 'label' => 'Submit button', 'type' => 'text', 'default' => 'Send Message'],
                ['key' => 'sending', 'label' => 'Submit button: text while sending', 'type' => 'text', 'default' => 'Sending…'],
                ['key' => 'privacy_note', 'label' => 'Privacy note under the button', 'type' => 'text', 'default' => 'We use your details only to reply to your message.'],
                ['key' => 'privacy_link_label', 'label' => 'Privacy note: link text', 'type' => 'text', 'default' => 'Read our Privacy Policy'],
                ['key' => 'success', 'label' => 'Success message', 'type' => 'textarea', 'rows' => 2, 'default' => 'Thank you for contacting us. Your message has been received and our team will get back to you soon.'],
                ['key' => 'err_required', 'label' => 'Error: required field', 'type' => 'text', 'default' => 'Please fill in this field.'],
                ['key' => 'err_email', 'label' => 'Error: invalid email', 'type' => 'text', 'default' => 'Please enter a valid email address.'],
                ['key' => 'err_phone', 'label' => 'Error: invalid phone', 'type' => 'text', 'default' => 'Please enter a valid phone number.'],
                ['key' => 'err_max', 'label' => 'Error: text too long', 'type' => 'text', 'default' => 'That is too long. Please shorten it.'],
                ['key' => 'notify_subject', 'label' => 'Notification email subject (to the foundation)', 'type' => 'text', 'default' => 'New contact message', 'help' => 'Emailed to the notification address in Settings.'],
            ],
        ],
        [
            'key' => 'details', 'label' => 'Office and contact details', 'toggle' => true,
            'help' => 'The address, emails, phone numbers, WhatsApp and social links are shown automatically from Settings.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Contact Us'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Visit us at Lumina Africa Foundation in Gulu City, Uganda, for inquiries.'],
                ['key' => 'office_title', 'label' => 'Office heading', 'type' => 'text', 'default' => 'Our Office'],
                ['key' => 'email_title', 'label' => 'Email heading', 'type' => 'text', 'default' => 'Email'],
                ['key' => 'phone_title', 'label' => 'Phone heading', 'type' => 'text', 'default' => 'Phone'],
                ['key' => 'extra_phone', 'label' => 'Extra phone number (this page only)', 'type' => 'text', 'default' => '+256 741 819 287', 'help' => 'Shown after the phone numbers from Settings. Leave empty to hide.'],
                ['key' => 'hours_title', 'label' => 'Opening hours heading', 'type' => 'text', 'default' => 'Hours'],
                ['key' => 'hours', 'label' => 'Opening hours', 'type' => 'text', 'default' => 'Mon-Fri, 9am-5pm', 'help' => 'Leave empty to hide.'],
                ['key' => 'whatsapp_label', 'label' => 'WhatsApp button', 'type' => 'text', 'default' => 'Chat on WhatsApp'],
                ['key' => 'social_title', 'label' => 'Social media heading', 'type' => 'text', 'default' => 'Follow us'],
            ],
        ],
        [
            'key' => 'map', 'label' => 'Map', 'toggle' => true,
            'help' => 'The map appears when a Google Maps embed link is set in Settings. Without it, a link to the address on Google Maps is shown instead.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Find us'],
                ['key' => 'iframe_title', 'label' => 'Map: accessible title', 'type' => 'text', 'default' => 'Map showing the location of our office'],
                ['key' => 'fallback_text', 'label' => 'Text when there is no embedded map', 'type' => 'textarea', 'rows' => 2, 'default' => 'Find our office on the map.'],
                ['key' => 'link_label', 'label' => 'Google Maps link', 'type' => 'text', 'default' => 'Open in Google Maps'],
            ],
        ],
        [
            'key' => 'closing', 'label' => 'Closing line', 'toggle' => true,
            'fields' => [
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'At Lumina Africa Foundation, we believe in the power of collaboration. Reach out today — together, we can light the path to opportunity.'],
            ],
        ],
    ],
];
