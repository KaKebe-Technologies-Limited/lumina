<?php
/**
 * Registry entry: PRIVACY POLICY. OWNER: B2 "web-content".
 *
 * *** DRAFT FOR OWNER REVIEW *** The live website has no privacy notice. This text is a sensible starting point for a Ugandan
 * non-profit and describes ONLY the personal data this website really collects (contact form, newsletter, job and volunteer
 * applications, involvement requests, donation pledges, event registrations, spam-protection data). It must be read and
 * approved by the foundation (ideally with a legal adviser, under the Data Protection and Privacy Act, 2019) before launch.
 * Tokens {site_name} {email} {email_link} {phone} {phone_link} {address} {year} are replaced with the current Settings values.
 */
return [
    'slug'  => 'privacy',
    'title' => 'Privacy Policy',
    'route' => '/privacy',
    'help'  => 'DRAFT FOR REVIEW: please have this privacy notice checked by the foundation (and a legal adviser) before launch. Each block below is one numbered section of the notice. You can use {site_name}, {email_link}, {phone_link} and {address}: they are filled in from Settings. The last section, "Membership registration", is a DRAFT kept in its own block below: confirm it, and the data-retention period it mentions, with the foundation and a legal adviser.',
    'nav'   => ['label' => 'Privacy Policy', 'order' => 200, 'header' => false, 'footer' => true, 'parent' => null],
    'seo'   => [
        'title'       => 'Privacy Policy',
        'description' => 'How Lumina Africa Foundation collects, uses and protects the personal information you share through this website.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Privacy Policy', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'How {site_name} collects, uses and protects the personal information you share with us through this website.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '',
                    'help' => 'Decorative background behind the title. Leave empty for the plain navy banner (recommended for a legal page).'],
            ],
        ],
        [
            'key' => 'notice', 'label' => 'The privacy notice', 'help' => 'Each row is a numbered section with its own heading. Rows can be added, removed and reordered.',
            'fields' => [
                ['key' => 'updated_label', 'label' => 'Last-updated label', 'type' => 'text', 'default' => 'Last updated'],
                ['key' => 'updated', 'label' => 'Last-updated date', 'type' => 'text', 'default' => '', 'help' => 'For example "1 November 2026". Leave empty to hide the line. Update it whenever the notice changes.'],
                ['key' => 'contents_title', 'label' => 'Table of contents heading', 'type' => 'text', 'default' => 'On this page'],
                ['key' => 'items', 'label' => 'Sections', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a section', 'max_rows' => 25,
                    'fields' => [
                        ['key' => 'title', 'label' => 'Heading', 'type' => 'text'],
                        ['key' => 'body', 'label' => 'Text', 'type' => 'richtext'],
                    ],
                    'default' => [
                        [
                            'title' => 'Who we are',
                            'body'  => '<p>{site_name} is a registered community-based non-profit organisation based in {address}. We are responsible for the personal information collected through this website.</p><p>If you have any question about this notice or about your personal information, contact us at {email_link}.</p>',
                        ],
                        [
                            'title' => 'The information we collect',
                            'body'  => '<p>We collect only what you choose to send us through the forms on this website:</p><ul>'
                                . '<li><strong>Contact form:</strong> your name, email address, phone number (optional), subject (optional) and message.</li>'
                                . '<li><strong>Newsletter:</strong> your email address and the page you signed up from.</li>'
                                . '<li><strong>Job and volunteer applications:</strong> your name, email address, phone number, message or cover letter, LinkedIn address (optional) and CV or resume (optional).</li>'
                                . '<li><strong>Getting involved:</strong> your name, email address, phone number, the way you would like to help, your organisation (if any) and your message.</li>'
                                . '<li><strong>Donation pledges:</strong> your name, email address, phone number, the amount you intend to give, your message and whether you wish to remain anonymous.</li>'
                                . '<li><strong>Event registrations:</strong> your name, email address, phone number and organisation.</li>'
                                . '<li><strong>Membership registration:</strong> see the section &ldquo;Membership registration&rdquo; below.</li>'
                                . '</ul><p>When you submit a form we also record your IP address and browser type. We use this only to keep the website secure and to prevent spam and abuse. To limit abuse of busy pages (forms, the calendar feed and calendar downloads) the site also keeps a scrambled, keyed hash of your IP address for a short time; it cannot be turned back into the address.</p>',
                        ],
                        [
                            'title' => 'How we use your information',
                            'body'  => '<ul>'
                                . '<li>to reply to your messages and requests;</li>'
                                . '<li>to send you our news and updates, only if you subscribed to them;</li>'
                                . '<li>to consider your job, internship or volunteer application;</li>'
                                . '<li>to organise our programmes, projects and events, and to keep a record of pledges and donations;</li>'
                                . '<li>to protect the website and prevent spam; and</li>'
                                . '<li>to meet our legal and reporting obligations.</li>'
                                . '</ul><p>We do not sell your personal information and we do not use it for automated decision-making.</p>',
                        ],
                        [
                            'title' => 'Our legal basis',
                            'body'  => '<p>We handle personal data in line with the Data Protection and Privacy Act, 2019 of Uganda. We rely mainly on your consent, which you give when you submit a form or tick a box, and on our legitimate interest in running the organisation and keeping this website secure. You can withdraw your consent at any time.</p>',
                        ],
                        [
                            'title' => 'Cookies and analytics',
                            'body'  => '<p>This website uses a small number of essential cookies, for example to keep forms secure and to let our staff sign in. They do not track you across other websites.</p><p>We may use Google Analytics to understand how visitors use the site. Where it is used, IP addresses are anonymised and the information is used only to improve our website.</p>',
                        ],
                        [
                            'title' => 'Who we share it with',
                            'body'  => '<p>Your information is seen only by the staff and volunteers who need it to do their work. We also use trusted service providers, such as our website host and email provider, who handle it on our behalf. We share personal information with others only when the law requires it or when you ask us to.</p><p>This website links to other services such as GoFundMe, social media platforms and Google Maps. Those services have their own privacy notices, and we are not responsible for how they use your information.</p>',
                        ],
                        [
                            'title' => 'How long we keep it',
                            'body'  => '<p>We keep personal information only for as long as it is needed for the purpose it was collected for, or as the law requires. When it is no longer needed we delete or anonymise it.</p>',
                        ],
                        [
                            'title' => 'How we keep it safe',
                            'body'  => '<p>CVs and other private files are stored outside the public part of the website and can be opened only by authorised staff who are signed in. Our forms have protection against spam and automated abuse. No online system is completely secure, so please do not send us sensitive information that we have not asked for.</p>',
                        ],
                        [
                            'title' => 'Children and young people',
                            'body'  => '<p>Our programmes serve young people. We do not knowingly collect personal information from anyone under 18 through this website without the consent of a parent or guardian. We publish photographs and stories of young people only with proper consent.</p>',
                        ],
                        [
                            'title' => 'Your rights',
                            'body'  => '<p>You have the right to ask us for a copy of the personal information we hold about you, to have it corrected or deleted, to object to how we use it, and to withdraw your consent. Every newsletter we send has an unsubscribe link.</p><p>To use any of these rights, contact us at {email_link}. If you are not satisfied with our response, you may complain to the Personal Data Protection Office in Uganda.</p>',
                        ],
                        [
                            'title' => 'Changes to this notice',
                            'body'  => '<p>We may update this notice from time to time. The latest version is always on this page.</p>',
                        ],
                        [
                            'title' => 'Contact us',
                            'body'  => '<p>{site_name}<br>{address}<br>Email: {email_link}<br>Phone: {phone_link}</p>',
                        ],
                    ]],
            ],
        ],
        [
            'key' => 'membership', 'label' => 'Membership registration (section added after the notice)', 'toggle' => true,
            'help' => 'DRAFT: this section is shown as the last numbered section of the notice, below the sections above, and stays here even if you rewrite the list above. Confirm it, and the data-retention period it mentions, with the foundation and a legal adviser. Switch it off only if membership registration is not used.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Membership registration', 'maxlength' => 140],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p><strong>Draft for the foundation and its legal adviser to confirm.</strong></p>'
                    . '<p>If you apply to become a member, we collect: your full name, gender, date of birth, nationality, email address, phone and WhatsApp numbers, country, district or region, sub-county or city, and village or address; your occupation or status, level of education and organisation or school; the areas you are interested in, your skills and availability, how you heard about us and why you want to join; the name, phone number and relationship of an emergency contact; your preferred payment method and any payment reference you give us; and your consent choices. If you are under 18 we also collect the name, phone number and relationship of your parent or guardian. We ask for a national ID or passport number only if the foundation has switched that question on.</p>'
                    . '<p>We use this information to review your application, to contact you, to manage your membership, to record membership fees and issue receipts, and, only if you ticked the box, to send you news and updates. Only authorised staff who are signed in can see membership records; they are never published on this website.</p>'
                    . '<p>We keep membership records for as long as you are a member and afterwards only for as long as the foundation needs them for the purposes above or the law requires. [The foundation will confirm the exact retention period here.]</p>'
                    . '<p>To see, correct or delete your membership information, or to withdraw your consent, contact us at {email_link}.</p>'],
            ],
        ],
        [
            'key' => 'shop', 'label' => 'Shop orders (section added after the notice)', 'toggle' => true,
            'help' => 'DRAFT: this section is shown as the last numbered section of the notice, after the membership section. It describes the personal data collected when someone buys from the shop. Confirm it, and the retention period it mentions, with the foundation and a legal adviser. Switch it off only if the shop is not used.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Shop orders', 'maxlength' => 140],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p><strong>Draft for the foundation and its legal adviser to confirm.</strong></p>'
                    . '<p>If you buy something from our shop, we collect your name, email address and phone number, your delivery address if you ask for delivery, any note you write to us, the items you order and the amount, how you chose to pay, and any payment reference you give us. We also keep a scrambled, keyed hash of your IP address and the time you agreed to our terms, to prevent abuse.</p>'
                    . '<p>Card and mobile-money payments made online are processed by our payment partner, Flutterwave, on its own secure page. We never see or store your card number: we only receive confirmation that a payment was made, its amount and its reference. When you pay online, your name, email address and phone number are passed to Flutterwave so that it can process the payment.</p>'
                    . '<p>We use this information to prepare and deliver your order, to confirm and record your payment, to contact you about your order, and to keep our accounts. Only authorised staff who are signed in can see orders; they are never published on this website. Your order page can only be opened with the private link we send you.</p>'
                    . '<p>We keep order records for as long as we need them for our accounts and the law requires. [The foundation will confirm the exact retention period here.]</p>'
                    . '<p>To see, correct or delete your order information, contact us at {email_link}.</p>'],
            ],
        ],
    ],
];
