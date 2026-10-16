<?php
/**
 * Registry entry: DONATE PAGE  (OWNER: B1 "web-core").
 * Default wording = the live Donate page (docs/live-snapshot/donate.txt). NOTE FOR THE OWNER: parts of the live wording (school supplies,
 * community health outreach, children and families) describe a wider scope than the digital-skills mission described elsewhere on the
 * site. They are kept as the faithful default; review them in Admin > Pages > Donate.
 * Account details (bank, mobile money, PayPal) and the online link are NOT copy: they come from Settings > Donations and are simply
 * hidden while empty. Pledges are stored in Admin > Donations with status "pledged" (a record of intent, never a payment).
 */
return [
    'slug'  => 'donate',
    'title' => 'Donate',
    'route' => '/donate',
    'help'  => 'Ways to give (details from Settings > Donations), what donations support, the pledge form and the FAQ.',
    'nav'   => ['label' => 'Donate', 'order' => 95, 'header' => true, 'footer' => true, 'button' => true],
    'seo'   => [
        'title'       => 'Support Our Life-Changing Programs',
        'description' => 'Support Lumina Africa Foundation: your donation helps vulnerable youth and families in Northern Uganda gain digital skills and opportunity. Every contribution counts.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Support Us', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'default' => 'Your donations empower children and families through life-changing programs. Every contribution counts!'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Donate to Support Lumina Africa Foundation'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '', 'help' => 'Leave empty to use the online donation link from Settings > Donations (currently the GoFundMe campaign).'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => '', 'help' => 'A wide photo darkened behind the heading. Leave empty for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'ways', 'label' => 'Ways to give', 'help' => 'Only the methods that have details in Settings > Donations are shown.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Ways to give'],
                ['key' => 'online_title', 'label' => 'Online donation: heading', 'type' => 'text', 'default' => 'Donate online'],
                ['key' => 'bank_title', 'label' => 'Bank transfer: heading', 'type' => 'text', 'default' => 'Bank transfer'],
                ['key' => 'mobile_title', 'label' => 'Mobile money: heading', 'type' => 'text', 'default' => 'Mobile money'],
                ['key' => 'paypal_title', 'label' => 'PayPal: heading', 'type' => 'text', 'default' => 'PayPal'],
                ['key' => 'paypal_text', 'label' => 'PayPal: text before the email address', 'type' => 'text', 'default' => 'Send your gift via PayPal to'],
                ['key' => 'copy_label', 'label' => 'Copy button text', 'type' => 'text', 'default' => 'Copy'],
                ['key' => 'copied_label', 'label' => 'Copy button text after copying', 'type' => 'text', 'default' => 'Copied'],
                ['key' => 'empty_text', 'label' => 'Message when no method is set up yet', 'type' => 'textarea', 'default' => 'Our giving details will be published here soon. Please get in touch and we will gladly help you give.'],
                ['key' => 'empty_cta_label', 'label' => 'Message when no method is set up yet: button text', 'type' => 'text', 'default' => 'Contact Us'],
                ['key' => 'empty_cta_url', 'label' => 'Message when no method is set up yet: button link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'support', 'label' => 'What your support does', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Support Us'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 6, 'default' => 'Join us in making a significant difference for families in our community and beyond. Together, we can build a supportive environment that encourages growth, resilience, and hope for every family. Your involvement is essential in helping us achieve our goals and create lasting change. Let\'s work hand in hand to transform the lives of those who need it most.'],
                ['key' => 'items', 'label' => 'Cards', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a card', 'max_rows' => 8,
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4],
                        ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image'],
                    ],
                    'default' => [
                        ['title' => 'School Supplies', 'text' => 'Provide essential supplies for children in need, including food, clothing, educational materials, and hygiene products, to ensure they have a better chance at growing up healthy and successful in their lives.', 'image' => '', 'image_alt' => ''],
                        ['title' => 'Digital Literacy', 'text' => 'Empower youth through essential training programs.', 'image' => '', 'image_alt' => ''],
                    ],
                ],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Donate to Support Lumina Africa Foundation'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '', 'help' => 'Leave empty to use the online donation link from Settings > Donations.'],
            ],
        ],
        [
            'key' => 'pledge', 'label' => 'Pledge form', 'toggle' => true,
            'help' => 'A form that records an intention to give (Admin > Donations, status "pledged"). It does not take payment.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Make a pledge'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Tell us how you would like to give and we will follow up with you.'],
                ['key' => 'note', 'label' => 'Notice (this is a record of intent, not a payment)', 'type' => 'textarea', 'rows' => 3, 'default' => 'This form records your intention to give. It is a pledge, not a payment: no money is taken here. We will contact you with the details to complete your gift.'],
                ['key' => 'name_label', 'label' => 'Field: name', 'type' => 'text', 'default' => 'Your name'],
                ['key' => 'email_label', 'label' => 'Field: email', 'type' => 'text', 'default' => 'Email address'],
                ['key' => 'phone_label', 'label' => 'Field: phone', 'type' => 'text', 'default' => 'Phone number'],
                ['key' => 'amount_label', 'label' => 'Field: amount', 'type' => 'text', 'default' => 'Amount'],
                ['key' => 'currency_label', 'label' => 'Field: currency', 'type' => 'text', 'default' => 'Currency'],
                ['key' => 'method_label', 'label' => 'Field: how would you like to give', 'type' => 'text', 'default' => 'How would you like to give?'],
                ['key' => 'method_bank', 'label' => 'Choice: bank transfer', 'type' => 'text', 'default' => 'Bank transfer'],
                ['key' => 'method_mobile_money', 'label' => 'Choice: mobile money', 'type' => 'text', 'default' => 'Mobile money'],
                ['key' => 'method_paypal', 'label' => 'Choice: PayPal', 'type' => 'text', 'default' => 'PayPal'],
                ['key' => 'method_other', 'label' => 'Choice: other', 'type' => 'text', 'default' => 'Another way'],
                ['key' => 'message_label', 'label' => 'Field: message (optional)', 'type' => 'text', 'default' => 'Message (optional)'],
                ['key' => 'anonymous_label', 'label' => 'Checkbox: anonymous', 'type' => 'text', 'default' => 'I would like my gift to stay anonymous'],
                ['key' => 'required_note', 'label' => 'Note about required fields', 'type' => 'text', 'default' => 'Fields marked * are required.'],
                ['key' => 'submit_label', 'label' => 'Submit button text', 'type' => 'text', 'default' => 'Submit my pledge'],
                ['key' => 'sending_label', 'label' => 'Submit button text while sending', 'type' => 'text', 'default' => 'Sending...'],
                ['key' => 'success', 'label' => 'Message after a successful pledge', 'type' => 'textarea', 'rows' => 2, 'default' => 'Thank you. Your pledge has been recorded (no payment has been taken). We will be in touch with the details.'],
                ['key' => 'err_required', 'label' => 'Error: required field is empty', 'type' => 'text', 'default' => 'Please fill in this field.'],
                ['key' => 'err_email', 'label' => 'Error: invalid email', 'type' => 'text', 'default' => 'Please enter a valid email address.'],
                ['key' => 'err_phone', 'label' => 'Error: invalid phone number', 'type' => 'text', 'default' => 'Please enter a valid phone number.'],
                ['key' => 'err_max', 'label' => 'Error: text too long', 'type' => 'text', 'default' => 'This is too long. Please shorten it.'],
                ['key' => 'err_choice', 'label' => 'Error: invalid choice', 'type' => 'text', 'default' => 'Please choose one of the options.'],
                ['key' => 'err_number', 'label' => 'Error: invalid amount', 'type' => 'text', 'default' => 'Please enter an amount greater than zero.'],
                ['key' => 'err_amount_range', 'label' => 'Error: amount outside the allowed range', 'type' => 'text', 'default' => 'Please enter an amount between 1 and 1,000,000,000.'],
                ['key' => 'err_amount_format', 'label' => 'Error: amount typed in an unclear way', 'type' => 'text', 'default' => 'Please type the amount with digits only, using a point for cents (for example 1500 or 1500.50).'],
            ],
        ],
        [
            'key' => 'faq', 'label' => 'Frequently Asked Questions', 'toggle' => true,
            'help' => 'Questions come from Admin > FAQs (group "donate"); the list below is only used while that group has no active questions. The three live-site questions that promise mobile money / PayPal / Stripe payments, monthly giving and school-supply funding are left out until the foundation confirms them.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Frequently Asked Questions'],
                ['key' => 'items', 'label' => 'Fallback questions (used only while Admin > FAQs has none for "donate")', 'type' => 'repeater', 'item_label' => 'question', 'add_label' => 'Add a question', 'max_rows' => 20,
                    'fields' => [
                        ['key' => 'question', 'label' => 'Question', 'type' => 'text', 'maxlength' => 300],
                        ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 4],
                    ],
                    'default' => [
                        ['question' => 'Is my donation tax-deductible?', 'answer' => 'Please check with your local tax authority regarding the deductibility of donations made to our organization.'],
                        ['question' => 'Who can I contact?', 'answer' => 'You can contact us through our website for any inquiries or assistance regarding donations.'],
                        ['question' => 'What is the impact of my donation?', 'answer' => 'Every contribution directly helps us provide essential resources and programs to children, youth, and families in need.'],
                    ],
                ],
            ],
        ],
    ],
];
