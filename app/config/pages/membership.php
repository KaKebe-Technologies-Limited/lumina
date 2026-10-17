<?php
/**
 * Registry entry: MEMBERSHIP PAGE + REGISTRATION FORM + CONFIRMATION + E-MAIL TEMPLATES.
 * OWNER: architect (frozen for the web/admin agents; they only READ these keys with content() / content_list()). Contract: docs/MEMBERSHIP.md.
 *
 * Nothing here is invented: where the owner must decide (benefits, eligibility, terms, fees) the defaults are neutral, and the
 * only descriptive sentences are the owner's own wording from the existing site (Involvement and planned-programs copy).
 * FEES are NOT page copy: they live in Admin > Membership types (table membership_types).
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 *
 * Placeholders usable in the e-mail templates ({...}, filled in by MembershipMail):
 *   {name} {first_name} {site} {ref} {type} {fee} {joining_fee} {payment_instructions} {contact_email} {url} {status_url}
 *   {membership_number} {start_date} {expiry_date} {renewal_due} {receipt} {amount} {method}
 */
return (static function (): array {
    $hl = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
    $text = static fn(string $key, string $label, string $default, array $extra = []): array => ['key' => $key, 'label' => $label, 'type' => 'text', 'default' => $default] + $extra;
    $area = static fn(string $key, string $label, string $default, int $rows = 3, array $extra = []): array => ['key' => $key, 'label' => $label, 'type' => 'textarea', 'rows' => $rows, 'default' => $default] + $extra;
    // a labelled form field: "<name>_label" and "<name>_help"
    $field = static function (string $name, string $what, string $label, string $help = '') use ($text): array {
        return [
            $text($name . '_label', 'Field: ' . $what, $label),
            $text($name . '_help', 'Hint under: ' . $what . ' (optional)', $help),
        ];
    };
    // an editable list of choices: key (stored in the database - do not change once members use it) + label (what visitors read)
    $options = static fn(string $key, string $label, array $rows, string $help = ''): array => [
        'key' => $key, 'label' => $label, 'type' => 'repeater', 'item_label' => 'label', 'add_label' => 'Add a choice', 'max_rows' => 30,
        'help' => trim($help . ' "Short code" is stored with each application: lower case letters, numbers and underscores, and do not change it once people have used it. Leave it empty on a new choice and a code is made from its label. The code "other" asks the applicant to type their own answer.'),
        'fields' => [
            ['key' => 'key', 'label' => 'Short code', 'type' => 'text', 'maxlength' => 40],
            ['key' => 'label', 'label' => 'Label shown to visitors', 'type' => 'text', 'maxlength' => 120],
        ],
        'default' => $rows,
    ];

    return [
        'slug'  => 'membership',
        'title' => 'Membership',
        'route' => '/membership',
        'help'  => 'Membership page, registration form wording, confirmation page and e-mail templates. The FEES are set in Admin > Membership types, the on/off switch and payment instructions in Site settings > Membership, the terms text in Pages > Membership terms.',
        'nav'   => ['label' => 'Membership', 'order' => 52, 'header' => true, 'footer' => true, 'requires' => ['table' => 'membership_types']],   // hidden while the membership tables are not imported yet
        'seo'   => [
            'title'       => 'Become a Member',
            'description' => 'Register to become a member of Lumina Africa Foundation. Choose a membership type, submit your details and we will review your application.',
            'og_image'    => 'assets/img/site/photo-04.jpg',
        ],
        'sections' => [
            // ------------------------------------------------------------------------------------------ the /membership page
            [
                'key' => 'hero', 'label' => 'Page banner',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title', 'Join Lumina Africa Foundation'),
                    $text('title', 'Main heading', 'Become a [[Member]]', ['maxlength' => 140, 'help' => $hl]),
                    $area('lead', 'Intro paragraph', 'Members are people and organisations who would love to be part of Lumina Africa Foundation. Choose a membership type and register online.', 4),
                    $text('cta_label', 'Button: text', 'Register now'),
                    ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/membership/register'],
                    ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg',
                        'alt' => 'Young people seated in the audience of an event', 'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
                ],
            ],
            [
                'key' => 'why', 'label' => 'Why join', 'toggle' => true,
                'help' => 'ADD YOUR MEMBERSHIP BENEFITS HERE. The rows below reuse the foundation\'s existing wording about volunteering, mentorship and partnership; replace or extend them with the real benefits of membership once the foundation has decided them.',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title (optional)', ''),
                    $text('title', 'Heading', 'Why Become a [[Member]]', ['help' => $hl]),
                    $area('intro', 'Intro (optional)', '', 3),
                    ['key' => 'items', 'label' => 'Reasons / benefits', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a reason', 'max_rows' => 12,
                        'fields' => [
                            ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options() + ['bell' => 'Megaphone / bell', 'layers' => 'Layers'], 'default' => 'heart'],
                            ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                            ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4],
                        ],
                        'default' => [
                            ['icon' => 'users', 'title' => 'Volunteer', 'text' => 'Give your time, skills, and passion to impact lives directly.'],
                            ['icon' => 'book-open', 'title' => 'Mentor young people', 'text' => 'Build a network of professionals, educators and experienced individuals who can guide young people in their personal and professional development.'],
                            ['icon' => 'globe', 'title' => 'Partner and advocate', 'text' => 'Together we can achieve more. Use your voice to inspire action.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'eligibility', 'label' => 'Who can join', 'toggle' => true,
                'help' => 'ADD YOUR ELIGIBILITY CRITERIA HERE. Each membership type also has its own "Who it is for" note (Admin > Membership types).',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title (optional)', ''),
                    $text('title', 'Heading', 'Who Can [[Join]]', ['help' => $hl]),
                    $area('text', 'Text', "Each membership type below says who it is for.\n\nPeople under 18 can apply with the details and consent of a parent or guardian.\n\nEvery application is reviewed before membership is confirmed.", 6),
                ],
            ],
            [
                'key' => 'types', 'label' => 'Membership types and fees', 'help' => 'The cards are built from Admin > Membership types, so the fee shown is always the CURRENT fee. This section only holds the surrounding words.',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title (optional)', ''),
                    $text('title', 'Heading', 'Membership [[Types]]', ['help' => $hl]),
                    $area('intro', 'Intro (optional)', '', 3),
                    $text('fee_tba', 'Wording when the fee is not set yet', 'Fee to be announced'),
                    $text('free', 'Wording when the fee is zero', 'Free'),
                    $text('joining_fee', 'Joining fee line ({amount} is replaced)', 'Joining fee: {amount}'),
                    $text('eligibility_label', 'Heading of the "who it is for" note', 'Who it is for'),
                    $text('apply_label', 'Button on each card', 'Apply for this membership'),
                    $text('full_label', 'Wording when a type has no places left', 'No places left'),
                    $text('period_one_time', 'Billing period: one-off', 'one-off'),
                    $text('period_monthly', 'Billing period: monthly', 'per month'),
                    $text('period_quarterly', 'Billing period: quarterly', 'per quarter'),
                    $text('period_annual', 'Billing period: yearly', 'per year'),
                    $text('period_lifetime', 'Billing period: lifetime', 'for life'),
                    $text('empty_title', 'Title when there is no membership type yet', 'Membership types are coming soon'),
                    $text('empty_text', 'Text when there is no membership type yet', 'Please check back soon or contact us.'),
                    $text('full_title', 'Title when every membership type has no places left', 'All membership places are taken'),
                    $text('full_text', 'Text when every membership type has no places left', 'We are not able to take new members in these types right now. Please check back later or contact us.'),
                ],
            ],
            [
                'key' => 'how', 'label' => 'How it works', 'toggle' => true,
                'fields' => [
                    $text('eyebrow', 'Small heading above the title (optional)', ''),
                    $text('title', 'Heading', 'How It [[Works]]', ['help' => $hl]),
                    ['key' => 'steps', 'label' => 'Steps', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a step', 'max_rows' => 8,
                        'fields' => [
                            ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                            ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3],
                        ],
                        'default' => [
                            ['title' => 'Apply online', 'text' => 'Fill in the registration form with your details and choose a membership type.'],
                            ['title' => 'We review your application', 'text' => 'Our team checks your application and may contact you if something is missing.'],
                            ['title' => 'Pay the membership fee', 'text' => 'If your membership type has a fee, pay it using the payment instructions we give you.'],
                            ['title' => 'Receive your membership number', 'text' => 'When your application is approved we confirm your membership and give you a membership number.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'payment', 'label' => 'How to pay', 'toggle' => true, 'help' => 'The payment details themselves are set in Site settings > Membership ("How to pay the membership fee"); while empty, the bank and mobile-money details of the Donations tab are used. The section is hidden while there are no details at all.',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title (optional)', ''),
                    $text('title', 'Heading', 'How to [[Pay]]', ['help' => $hl]),
                    $area('intro', 'Text above the details', 'Use these details to pay the fee of your membership type. Keep your application reference: you may be asked for it.', 3),
                ],
            ],
            [
                'key' => 'faq', 'label' => 'Frequently Asked Questions', 'toggle' => true,
                'help' => 'Questions can be added in Admin > FAQs (group "membership") or here. The section stays hidden while there are no questions.',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title (optional)', ''),
                    $text('title', 'Heading', 'Frequently Asked [[Questions]]', ['help' => $hl]),
                    ['key' => 'items', 'label' => 'Questions (used while the FAQs screen has none for "membership")', 'type' => 'repeater', 'item_label' => 'question', 'add_label' => 'Add a question', 'max_rows' => 20,
                        'fields' => [
                            ['key' => 'question', 'label' => 'Question', 'type' => 'text', 'maxlength' => 300],
                            ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 4],
                        ],
                        'default' => [],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'label' => 'Call to action at the bottom', 'toggle' => true,
                'fields' => [
                    $text('title', 'Heading', 'Ready to join us?'),
                    $area('text', 'Text', 'Register online. It only takes a few minutes.', 2),
                    $text('button_label', 'Button: text', 'Register now'),
                    ['key' => 'button_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/membership/register'],
                ],
            ],
            [
                'key' => 'closed', 'label' => 'Notice when membership is closed', 'help' => 'Shown instead of the form when "Accept new membership applications" is switched off in Site settings > Membership.',
                'fields' => [
                    $text('lead', 'Intro in the page banner while registration is closed', 'Membership registration is not open at the moment.'),
                    $text('title', 'Heading', 'Membership registration is closed'),
                    $area('text', 'Text', 'We are not accepting new membership applications at the moment. Please check back soon or contact us if you have a question.', 3),
                    $text('button_label', 'Button: text', 'Contact us'),
                    ['key' => 'button_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/contact'],
                ],
            ],

            // ------------------------------------------------------------------------------------------ /membership/register
            [
                'key' => 'register', 'label' => 'Registration page banner',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title', 'Membership'),
                    $text('title', 'Main heading', 'Membership [[Registration]]', ['maxlength' => 140, 'help' => $hl]),
                    $area('lead', 'Intro paragraph', 'Tell us about yourself and choose the membership type you would like.', 3),
                    ['key' => 'breadcrumb', 'label' => 'Breadcrumb label', 'type' => 'text', 'default' => 'Register'],
                ],
            ],
            [
                'key' => 'form', 'label' => 'Registration form: general wording', 'help' => 'Headings of the form groups, buttons and validation messages.',
                'fields' => [
                    $text('required_note', 'Note about required fields', 'Fields marked * are required.'),
                    $text('choose', 'First option of every drop-down', 'Choose an option'),
                    $text('sections_label', 'Screen-reader name of the list of form sections', 'Form sections'),
                    $text('legend_type', 'Group heading: membership type', 'Membership type'),
                    $text('legend_personal', 'Group heading: personal details', 'About you'),
                    $text('legend_contact', 'Group heading: contact details', 'Contact details'),
                    $text('legend_livelihood', 'Group heading: occupation and education', 'Occupation and education'),
                    $text('legend_participation', 'Group heading: participation', 'How you would like to take part'),
                    $text('legend_guardian', 'Group heading: parent or guardian', 'Parent or guardian'),
                    $text('legend_emergency', 'Group heading: emergency contact', 'Emergency contact'),
                    $text('legend_payment', 'Group heading: payment', 'Payment'),
                    $text('legend_consent', 'Group heading: consent and declaration', 'Consent and declaration'),
                    $text('fee_line', 'Line showing the fee of the chosen type ({fee} is replaced)', '{fee}'),
                    $text('age_line', 'Line showing the age ({age} is replaced)', 'Age: {age}'),
                    $area('minor_note', 'Note shown when the applicant is under 18', 'You are under 18, so we need the details of a parent or guardian and their consent.', 2),
                    $area('payment_note', 'Note in the payment group', 'You do not need to pay now. If you have already paid, add the payment reference so we can match it. Do not upload or type card or bank PINs.', 3),
                    $text('submit_label', 'Submit button text', 'Submit application'),
                    $text('sending_label', 'Submit button text while sending', 'Sending...'),
                    $text('error_title', 'Error summary: heading', 'Please check the following before sending'),
                    $text('err_required', 'Error: required field is empty', 'Please fill in this field.'),
                    $text('err_email', 'Error: invalid email', 'Please enter a valid email address.'),
                    $text('err_phone', 'Error: invalid phone number', 'Please enter a valid phone number.'),
                    $text('err_max', 'Error: text too long', 'This is too long. Please shorten it.'),
                    $text('err_min', 'Error: text too short', 'Please write a little more.'),
                    $text('err_choice', 'Error: invalid choice', 'Please choose one of the options.'),
                    $text('err_date', 'Error: invalid date', 'Please enter a valid date.'),
                    $text('err_dob', 'Error: date of birth in the future or too long ago', 'Please check the date of birth.'),
                    $text('err_accept', 'Error: a required tick box is not ticked', 'Please tick this box to continue.'),
                    $text('err_type_full', 'Error: the chosen type has no places left', 'This membership type has no places left. Please choose another.'),
                    $text('err_closed', 'Message when someone posts while membership is closed', 'Membership registration is closed at the moment.'),
                    $area('duplicate', 'Message when the e-mail already has an open application (does not reveal anything else)', 'Thank you. We already have your details and will be in touch. If you need help, please contact us.', 3),
                ],
            ],
            [
                'key' => 'fields', 'label' => 'Registration form: field labels and hints', 'help' => 'Rename any label or add a hint. Hints are optional.',
                'fields' => array_merge(
                    $field('type', 'membership type', 'Membership type', ''),
                    $field('name', 'full name', 'Full name', ''),
                    $field('gender', 'gender', 'Gender', ''),
                    $field('dob', 'date of birth', 'Date of birth', 'We use this to check whether a parent or guardian needs to consent.'),
                    $field('nationality', 'nationality', 'Nationality', ''),
                    $field('id_number', 'ID or passport number', 'National ID or passport number', ''),
                    $field('email', 'email', 'Email address', ''),
                    $field('phone', 'phone', 'Phone number', 'Include the country code, for example +256...'),
                    $field('whatsapp', 'WhatsApp', 'WhatsApp number (optional)', ''),
                    $field('country', 'country', 'Country', ''),
                    $field('region', 'district or region', 'District or region', ''),
                    $field('city', 'sub-county or city', 'Sub-county or city', ''),
                    $field('address', 'village or address', 'Village or address (optional)', ''),
                    $field('occupation', 'occupation or status', 'Occupation or status', ''),
                    $field('occupation_other', 'occupation (when "other")', 'Please tell us more', ''),
                    $field('education', 'education level', 'Highest level of education', ''),
                    $field('organisation', 'organisation or school', 'Organisation or school (optional)', ''),
                    $field('interests', 'areas of interest', 'Areas you are interested in', 'Tick all that apply.'),
                    $field('skills', 'skills and experience', 'Skills and experience (optional)', 'Tell us about skills or experience you could share.'),
                    $field('availability', 'availability', 'Availability', ''),
                    $field('heard', 'how did you hear about us', 'How did you hear about us?', ''),
                    $field('heard_other', 'how you heard (when "other")', 'Please tell us where', ''),
                    $field('motivation', 'why do you want to join', 'Why would you like to join?', 'A few sentences are enough.'),
                    $field('guardian_name', 'parent or guardian name', 'Parent or guardian: full name', ''),
                    $field('guardian_phone', 'parent or guardian phone', 'Parent or guardian: phone number', ''),
                    $field('guardian_relationship', 'parent or guardian relationship', 'Relationship to you', 'For example mother, father, guardian.'),
                    $field('emergency_name', 'emergency contact name', 'Emergency contact: full name', ''),
                    $field('emergency_phone', 'emergency contact phone', 'Emergency contact: phone number', ''),
                    $field('emergency_relationship', 'emergency contact relationship', 'Relationship to you', 'For example sister, friend, employer.'),
                    $field('payment_method', 'preferred payment method', 'Preferred payment method', ''),
                    $field('payment_reference', 'payment reference', 'Payment reference (optional)', 'Only if you have already paid: for example the mobile-money or bank transaction ID.'),
                ),
            ],
            [
                'key' => 'consent', 'label' => 'Registration form: consent and declaration', 'help' => 'In the first two labels, {terms_link} and {privacy_link} become links to the terms page and the privacy notice.',
                'fields' => [
                    $text('terms_label', 'Tick box: membership terms', 'I have read and agree to the {terms_link}.', ['maxlength' => 300]),
                    $text('terms_link_text', 'Link text: membership terms', 'membership terms'),
                    $text('privacy_label', 'Tick box: privacy and data processing', 'I have read the {privacy_link} and agree that my personal data may be processed to handle my membership.', ['maxlength' => 300]),
                    $text('privacy_link_text', 'Link text: privacy notice', 'privacy notice'),
                    $text('updates_label', 'Tick box: news and updates (optional)', 'Send me news and updates by email (optional).'),
                    $text('declaration_label', 'Tick box: declaration', 'I declare that the information I have given is true and complete, and that I have permission to give my emergency contact\'s details.', ['maxlength' => 300]),
                    $text('guardian_consent_label', 'Tick box: parent or guardian consent (under 18)', 'My parent or guardian knows about this application and consents to it.', ['maxlength' => 300]),
                ],
            ],
            [
                'key' => 'options', 'label' => 'Registration form: choices', 'help' => 'The drop-down and tick-box choices. Interest areas come from your three program pillars automatically; the list below adds extra ones.',
                'fields' => [
                    $text('gender_female', 'Gender: female', 'Female'),
                    $text('gender_male', 'Gender: male', 'Male'),
                    $text('gender_undisclosed', 'Gender: prefer not to say', 'Prefer not to say'),
                    $options('occupation', 'Occupation or status', [
                        ['key' => 'student', 'label' => 'Student'],
                        ['key' => 'employed', 'label' => 'Employed'],
                        ['key' => 'self_employed', 'label' => 'Self-employed'],
                        ['key' => 'unemployed', 'label' => 'Unemployed'],
                        ['key' => 'other', 'label' => 'Other'],
                    ]),
                    $options('education', 'Highest level of education', [
                        ['key' => 'none', 'label' => 'No formal education'],
                        ['key' => 'primary', 'label' => 'Primary'],
                        ['key' => 'secondary', 'label' => 'Secondary'],
                        ['key' => 'vocational', 'label' => 'Vocational or technical'],
                        ['key' => 'diploma', 'label' => 'Diploma'],
                        ['key' => 'bachelor', 'label' => 'Bachelor\'s degree'],
                        ['key' => 'postgraduate', 'label' => 'Postgraduate'],
                    ]),
                    $options('interests_extra', 'Extra areas of interest (after the program pillars)', [
                        ['key' => 'volunteering', 'label' => 'Volunteering'],
                        ['key' => 'mentorship', 'label' => 'Mentorship'],
                        ['key' => 'partnerships', 'label' => 'Partnerships and advocacy'],
                    ]),
                    $options('availability', 'Availability', [
                        ['key' => 'weekdays', 'label' => 'Weekdays'],
                        ['key' => 'weekends', 'label' => 'Weekends'],
                        ['key' => 'evenings', 'label' => 'Evenings'],
                        ['key' => 'flexible', 'label' => 'Flexible'],
                    ]),
                    $options('heard', 'How did you hear about us?', [
                        ['key' => 'website', 'label' => 'Our website'],
                        ['key' => 'social_media', 'label' => 'Social media'],
                        ['key' => 'friend_family', 'label' => 'Friend or family'],
                        ['key' => 'event', 'label' => 'Event or community meeting'],
                        ['key' => 'partner', 'label' => 'Partner organisation'],
                        ['key' => 'other', 'label' => 'Other'],
                    ]),
                    $options('payment_methods', 'Preferred payment methods', [
                        ['key' => 'mobile_money', 'label' => 'Mobile money'],
                        ['key' => 'bank_transfer', 'label' => 'Bank transfer'],
                        ['key' => 'cash_office', 'label' => 'Cash at the office'],
                        ['key' => 'other', 'label' => 'Other'],
                    ], 'These are also the payment methods staff can pick when they record a payment.'),
                ],
            ],

            // ------------------------------------------------------------------------------------------ /membership/confirmation
            [
                'key' => 'confirmation', 'label' => 'Confirmation page (after registering)',
                'fields' => [
                    $text('title', 'Heading', 'Application [[received]]', ['help' => $hl]),
                    $area('lead', 'Text', 'Thank you, {first_name}. Your membership application has been received and will be reviewed.', 3),
                    $text('ref_label', 'Label of the application reference', 'Your application reference'),
                    $text('type_label', 'Label of the membership type', 'Membership type'),
                    $text('fee_label', 'Label of the fee', 'Fee'),
                    $text('payment_title', 'Heading above the payment instructions', 'How to pay'),
                    $area('payment_none', 'Text when there are no payment instructions yet', 'We will share the payment details with you when we review your application.', 2),
                    $text('next_title', 'Heading of the next steps', 'What happens next'),
                    $area('next_text', 'Next steps', "We review your application and contact you by email or phone.\n\nIf your membership type has a fee, pay it using the payment details we give you.\n\nWhen your application is approved you receive your membership number.", 6),
                    $area('keep_note', 'Note about keeping the reference', 'Please keep your application reference. Quote it if you contact us.', 2),
                    $text('home_label', 'Button: text', 'Back to the membership page'),
                    $text('invalid_title', 'Heading when the link is not valid or has expired', 'This confirmation link is not valid'),
                    $area('invalid_text', 'Text when the link is not valid or has expired', 'The link may have expired. If you applied recently, please contact us and quote your application reference.', 3),
                ],
            ],

            // ------------------------------------------------------------------------------------------ e-mail templates
            [
                'key' => 'emails', 'label' => 'E-mail templates', 'help' => 'Plain-text e-mails. You can use these placeholders: {name} {first_name} {site} {ref} {type} {fee} {joining_fee} {payment_instructions} {contact_email} {url} {status_url} {membership_number} {start_date} {expiry_date} {renewal_due} {receipt} {amount} {method}. Do not put passwords or private data in templates.',
                'fields' => [
                    $text('ack_subject', 'To the applicant after registering: subject', 'We received your membership application ({ref})'),
                    $area('ack_body', 'To the applicant after registering: message', "Hello {first_name},\n\nThank you for applying to become a member of {site}. We have received your application.\n\nApplication reference: {ref}\nMembership type: {type}\nFee: {fee}\n\n{payment_instructions}\n\nWe will review your application and contact you. If you have a question, reply to this email or write to {contact_email}.\n\n{site}", 14),
                    $text('staff_subject', 'To the organisation (notification address): subject', 'New membership application {ref}'),
                    $area('staff_body', 'To the organisation: message', "A new membership application was submitted.\n\nReference: {ref}\nName: {name}\nMembership type: {type}\nFee: {fee}\n\nReview it in the admin area: {url}", 8),
                    $text('approved_subject', 'To the member when approved: subject', 'Your membership is confirmed ({membership_number})'),
                    $area('approved_body', 'To the member when approved: message', "Hello {first_name},\n\nYour membership of {site} has been approved.\n\nMembership number: {membership_number}\nMembership type: {type}\nStart date: {start_date}\nRenewal due: {renewal_due}\n\nIf you have a question, write to {contact_email}.\n\n{site}", 12),
                    $text('rejected_subject', 'To the applicant when not approved: subject', 'About your membership application ({ref})'),
                    $area('rejected_body', 'To the applicant when not approved: message', "Hello {first_name},\n\nThank you for your interest in {site}. We are not able to approve your membership application ({ref}) at this time.\n\nIf you have a question, write to {contact_email}.\n\n{site}", 8),
                    $text('payment_subject', 'To the member when a payment is recorded: subject', 'We received your payment ({receipt})'),
                    $area('payment_body', 'To the member when a payment is recorded: message', "Hello {first_name},\n\nWe have recorded your payment of {amount} ({method}) for your membership of {site}.\n\nReceipt number: {receipt}\nMembership number: {membership_number}\n\nThank you.\n\n{site}", 10),
                    $text('name_fallback', 'Word used when a name cannot be shown safely', 'there'),
                    $text('no_expiry', 'Wording for {expiry_date} when the membership does not expire', 'No expiry'),
                    $text('no_renewal', 'Wording for {renewal_due} when the membership does not expire', 'None, this membership does not expire'),
                ],
            ],
        ],
    ];
})();
