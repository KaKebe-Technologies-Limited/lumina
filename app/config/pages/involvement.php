<?php
/**
 * Registry entry: INVOLVEMENT PAGE  (OWNER: B1 "web-core"; redesigned by B-INNER-A).
 * Default wording = the live Involvement page (docs/live-snapshot/involvement.txt). The live site sent people to two Google Forms
 * ("Partner With Us", "Volunteer With Us"); this rebuild has its own form (section "form") that stores requests in Admin > Involvement
 * and emails the notification address from Settings. The buttons below point at that form with the type pre-selected.
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 */
$hlHelp = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'involvement',
    'title' => 'Involvement',
    'route' => '/involvement',
    'help'  => 'How to volunteer, partner or advocate, the sign-up form and the FAQ.',
    'nav'   => ['label' => 'Involvement', 'order' => 50, 'header' => true, 'footer' => true],
    'seo'   => [
        'title'       => 'Get Involved in Our Mission',
        'description' => 'Volunteer, partner or advocate with Lumina Africa Foundation and help transform lives through digital inclusion, education and youth empowerment in Northern Uganda.',
        'og_image'    => 'assets/img/site/photo-04.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Join us in creating lasting change across Northern Uganda.'],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Get Involved with Lumina [[Africa Foundation]]', 'maxlength' => 140, 'help' => $hlHelp],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 4, 'default' => 'Whether you choose to volunteer, partner, or advocate, your contribution makes a real difference in transforming lives through healthcare access, educational empowerment, and digital inclusion.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Partner With Us'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement?type=partner#join', 'help' => '"/involvement?type=partner#join" opens the sign-up form below with "Partner" selected.'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg',
                    'alt' => 'Young people seated in the audience of an event',
                    'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'how', 'label' => 'How to get involved', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'How to Get [[Involved]]', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 5, 'default' => 'Join us in making a positive impact together. Our efforts can create a ripple effect in the community, inspiring others to take action, spreading awareness, and fostering a culture of collaboration and support. Together, we can achieve meaningful change and contribute to a brighter future for everyone.'],
                ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg', 'alt' => 'Young people seated in the audience of an event'],
                ['key' => 'image2', 'label' => 'Second photo (behind, optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => 'Young people seated at desks in a lecture hall'],
            ],
        ],
        [
            'key' => 'ways', 'label' => 'Ways to take part', 'toggle' => true, 'help' => 'The cards: volunteer, partner, advocate (or anything else you add).',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the cards (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading above the cards (optional)', 'type' => 'text', 'default' => '', 'help' => $hlHelp],
                ['key' => 'items', 'label' => 'Cards', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a card', 'max_rows' => 8,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options() + ['bell' => 'Megaphone / bell', 'layers' => 'Layers'], 'default' => 'heart'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'tagline', 'label' => 'Short line', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4],
                        ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text'],
                        ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url'],
                    ],
                    'default' => [
                        ['icon' => 'users', 'title' => 'Volunteer With Us', 'tagline' => 'Give your time, skills, and passion to impact lives directly.',
                            'text' => 'As a volunteer, you become part of a movement driving hope in healthcare, education, and innovation. From mentoring young people to supporting outreach programs, your effort helps build a brighter future.',
                            'cta_label' => 'Volunteer With Us', 'cta_url' => '/involvement?type=volunteer#join'],
                        ['icon' => 'globe', 'title' => 'Partner With Us', 'tagline' => 'Together we can achieve more.',
                            'text' => 'We welcome collaborations with organizations, businesses, and institutions that share our vision. By partnering with Lumina Africa Foundation, you join us in developing sustainable programs that create long-term community impact.',
                            'cta_label' => 'Partner With Us', 'cta_url' => '/involvement?type=partner#join'],
                        ['icon' => 'bell', 'title' => 'Advocate for Change', 'tagline' => 'Use your voice to inspire action.',
                            'text' => 'Not everyone can volunteer on the ground, but you can still make an impact by advocating for healthcare access, digital inclusion, and youth empowerment. Share our stories, raise awareness, and help us amplify the voices of those who need it most.',
                            'cta_label' => 'Join Us', 'cta_url' => '/involvement?type=advocate#join'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'ambassadors', 'label' => 'Ambassadors call (banner)', 'toggle' => true,
            'help' => 'A banner that invites visitors to become ambassadors and leads to the Ambassadors page. Hidden while that page is not available.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'New: Ambassadors'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Represent Lumina Africa Foundation from [[anywhere in the world]]', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Ambassadors raise funds, lobby for the foundation and rally computers, materials and support from their own networks. Tell us what you can raise or give each year, in money or in kind.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Become an ambassador'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/involvement/ambassadors'],
            ],
        ],
        [
            'key' => 'form', 'label' => 'Sign-up form', 'help' => 'Heading, labels and messages of the "Join us" form. Requests are stored in Admin > Involvement and emailed to the notification address in Settings.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Join Us [[Today]]', 'help' => $hlHelp],
                ['key' => 'subtitle', 'label' => 'Sub-heading', 'type' => 'text', 'default' => 'Your support fuels transformation.'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4, 'default' => 'Becoming part of Lumina Africa Foundation means standing for hope, opportunity, and change. Whether you give your time, resources, or your voice, you are building the Africa we all dream of.'],
                ['key' => 'name_label', 'label' => 'Field: name', 'type' => 'text', 'default' => 'Your Name'],
                ['key' => 'email_label', 'label' => 'Field: email', 'type' => 'text', 'default' => 'Your Email Address'],
                ['key' => 'phone_label', 'label' => 'Field: phone', 'type' => 'text', 'default' => 'Phone Number'],
                ['key' => 'type_label', 'label' => 'Field: how would you like to take part', 'type' => 'text', 'default' => 'I would like to'],
                ['key' => 'type_volunteer', 'label' => 'Choice: volunteer', 'type' => 'text', 'default' => 'Volunteer'],
                ['key' => 'type_partner', 'label' => 'Choice: partner', 'type' => 'text', 'default' => 'Partner'],
                ['key' => 'type_advocate', 'label' => 'Choice: advocate', 'type' => 'text', 'default' => 'Advocate'],
                ['key' => 'org_label', 'label' => 'Field: organisation', 'type' => 'text', 'default' => 'Organisation'],
                ['key' => 'org_help', 'label' => 'Help under the organisation field', 'type' => 'text', 'default' => 'If you are partnering as an organisation, business or institution.'],
                ['key' => 'message_label', 'label' => 'Field: message', 'type' => 'text', 'default' => 'Your Message'],
                ['key' => 'required_note', 'label' => 'Note about required fields', 'type' => 'text', 'default' => 'Fields marked * are required.'],
                ['key' => 'submit_label', 'label' => 'Submit button text', 'type' => 'text', 'default' => 'Join Us'],
                ['key' => 'sending_label', 'label' => 'Submit button text while sending', 'type' => 'text', 'default' => 'Sending...'],
                ['key' => 'success', 'label' => 'Message after a successful submission', 'type' => 'textarea', 'rows' => 2, 'default' => 'Thank you for getting involved. We will be in touch soon.'],
                ['key' => 'error_title', 'label' => 'Error summary: heading (shown above the form when something needs fixing)', 'type' => 'text', 'default' => 'Please check the following before sending'],
                ['key' => 'err_required', 'label' => 'Error: required field is empty', 'type' => 'text', 'default' => 'Please fill in this field.'],
                ['key' => 'err_email', 'label' => 'Error: invalid email', 'type' => 'text', 'default' => 'Please enter a valid email address.'],
                ['key' => 'err_phone', 'label' => 'Error: invalid phone number', 'type' => 'text', 'default' => 'Please enter a valid phone number.'],
                ['key' => 'err_max', 'label' => 'Error: text too long', 'type' => 'text', 'default' => 'This is too long. Please shorten it.'],
                ['key' => 'err_choice', 'label' => 'Error: invalid choice', 'type' => 'text', 'default' => 'Please choose one of the options.'],
            ],
        ],
        [
            'key' => 'faq', 'label' => 'Frequently Asked Questions', 'toggle' => true,
            'help' => 'The questions and answers shown on the involvement page, next to a photo.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Frequently Asked [[Questions]]', 'help' => $hlHelp],
                ['key' => 'image', 'label' => 'Photo beside the questions (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg', 'alt' => 'A trainer points at a screen while young people wearing headphones work at computers'],
                ['key' => 'items', 'label' => 'Questions', 'type' => 'repeater', 'item_label' => 'question', 'add_label' => 'Add a question', 'max_rows' => 20,
                    'fields' => [
                        ['key' => 'question', 'label' => 'Question', 'type' => 'text', 'maxlength' => 300],
                        ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 4],
                    ],
                    'default' => [
                        ['question' => 'What does “getting involved” mean?', 'answer' => 'It means taking part in our mission through volunteering, partnerships, or advocacy. Every role is valuable in advancing healthcare, education, and innovation in Northern Uganda.'],
                        ['question' => 'Who can volunteer?', 'answer' => 'Anyone passionate about making a difference. Whether you’re a student, professional, or retiree, your time and skills can contribute meaningfully.'],
                        ['question' => 'How long is a volunteer commitment?', 'answer' => 'It depends on your availability. Some roles are short-term (events and campaigns) while others may require longer involvement.'],
                        ['question' => 'What kind of organizations can partner with you?', 'answer' => 'We collaborate with NGOs, businesses, academic institutions, and community groups that believe in sustainable development and social impact.'],
                        ['question' => 'Can I advocate if I live outside Uganda?', 'answer' => 'Yes. Advocacy is borderless. You can support by sharing our work online, raising awareness, and connecting us with networks that align with our cause.'],
                    ],
                ],
            ],
        ],
    ],
];
