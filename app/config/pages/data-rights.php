<?php
/**
 * Registry entry: DATA RIGHTS REQUEST (page /privacy/request). A person asks to see, correct, delete or stop the use of their personal data, or withdraws consent.
 * The request is saved in Admin > Messages (kind "dsr") with a reference number and a response target date, and an acknowledgement is e-mailed.
 * DRAFT FRAMEWORK: the foundation must confirm the wording (ideally with a legal adviser). The response time is a TARGET, not a legal promise.
 */
return [
    'slug'  => 'data-rights',
    'title' => 'Data rights request',
    'route' => '/privacy/request',
    'help'  => 'The form people use to ask about their personal data (see it, correct it, delete it, stop its use, withdraw consent). Requests land in Admin > Messages and in Admin > Data protection with a reference number. The response time below is a target, not a legal promise.',
    'nav'   => ['label' => 'Your data rights', 'order' => 201, 'header' => false, 'footer' => true, 'parent' => null],
    'seo'   => [
        'title'       => 'Your data rights',
        'description' => 'Ask Lumina Africa Foundation to show, correct or delete your personal data, or to stop using it.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Privacy'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Your data rights', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'It is your information. You can ask us what we hold about you, ask us to correct or delete it, or ask us to stop using it.'],
            ],
        ],
        [
            'key' => 'form', 'label' => 'The request form',
            'fields' => [
                ['key' => 'intro', 'label' => 'Text above the form', 'type' => 'richtext', 'default' => '<p>Fill in the form and we will write back to you. We may ask you to prove who you are before we show or change anything, so that nobody else can see your information.</p><p>You do not have to use this form. You can also write to us at {email_link}. The form is simply the quickest way, and it gives you a reference number.</p>'],
                ['key' => 'response_days', 'label' => 'Response target (days)', 'type' => 'number', 'default' => '30', 'min' => 1, 'max' => 90, 'help' => 'We aim to answer within this many days. It is a target, not a legal promise. Between 1 and 90.'],
                ['key' => 'response_note', 'label' => 'Response-time note', 'type' => 'text', 'default' => 'We aim to answer within {days} days. This is our target, not a legal promise: if we need more time or need to check who you are, we will tell you.'],
                ['key' => 'name_label', 'label' => 'Name: label', 'type' => 'text', 'default' => 'Your full name'],
                ['key' => 'email_label', 'label' => 'Email: label', 'type' => 'text', 'default' => 'Your email address (we reply here)'],
                ['key' => 'phone_label', 'label' => 'Phone: label', 'type' => 'text', 'default' => 'Phone number (optional)'],
                ['key' => 'type_label', 'label' => 'Request type: label', 'type' => 'text', 'default' => 'What would you like to do?'],
                ['key' => 'details_label', 'label' => 'Details: label', 'type' => 'text', 'default' => 'Tell us which service or form you used, and what you need'],
                ['key' => 'details_help', 'label' => 'Details: help', 'type' => 'text', 'default' => 'For example "I joined the Academy in March with this email" or "please remove me from the newsletter". Please do not send passwords or ID numbers.'],
                ['key' => 'submit', 'label' => 'Submit button', 'type' => 'text', 'default' => 'Send my request'],
                ['key' => 'why_note', 'label' => 'Why we ask (note under the button)', 'type' => 'text', 'default' => 'We ask for your name and email only so that we can find your records, check it is really you, and reply. We keep the request and our answer as a record that we dealt with it.'],
                ['key' => 'success_title', 'label' => 'After sending: heading', 'type' => 'text', 'default' => 'We have your request'],
                ['key' => 'success', 'label' => 'After sending: text ({reference} and {date} are filled in)', 'type' => 'textarea', 'rows' => 3, 'default' => 'Your reference is {reference}. We aim to answer by {date}. We have e-mailed you a confirmation; if it does not arrive, check your spam folder and keep this reference.'],
                ['key' => 'complaint_note', 'label' => 'Note about complaints', 'type' => 'richtext', 'default' => '<p>If you are not happy with how we handled your request, you can complain to the Personal Data Protection Office (PDPO) in Uganda, which supervises the Data Protection and Privacy Act, 2019.</p>'],
            ],
        ],
    ],
];
