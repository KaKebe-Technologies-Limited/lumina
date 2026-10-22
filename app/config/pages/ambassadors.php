<?php
/**
 * Registry entry: AMBASSADORS (page /involvement/ambassadors, a child of Involvement). A call for people anywhere in the world to become Lumina Africa Foundation
 * ambassadors: they raise funds and lobby for the foundation, and pledge a yearly amount of money and / or in-kind support (computers, materials ...).
 * The sign-up form stores applications in `ambassador_applications` (Admin > Inbox > Ambassadors) and e-mails the notification address from Settings.
 * Every heading, paragraph, card, step and FAQ below is editable in Admin > Pages > Ambassadors. The default benefits are proposals for the owner to confirm or edit:
 * nothing here claims figures or partners that do not exist.
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 */
$hl = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
$cards = static fn(string $help = ''): array => [
    'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a card', 'max_rows' => 8, 'help' => $help,
    'fields' => [
        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options() + ['bell' => 'Megaphone', 'book' => 'Book'], 'default' => 'check-circle'],
        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3],
    ],
];

return [
    'slug'  => 'ambassadors',
    'title' => 'Ambassadors',
    'route' => '/involvement/ambassadors',
    'help'  => 'The call for ambassadors: what they do, what they can pledge each year (money, computers and materials, or something else), how it works, the sign-up form and the questions people ask. Applications arrive under Inbox > Ambassadors.',
    'nav'   => ['label' => 'Ambassadors', 'order' => 56, 'header' => true, 'footer' => true, 'parent' => 'involvement', 'requires' => ['table' => 'pages', 'where' => "EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications')"]],
    'seo'   => [
        'title'       => 'Become a Lumina Ambassador',
        'description' => 'Join Lumina Africa Foundation as an ambassador from anywhere in the world: raise funds, lobby for young people in Northern Uganda and pledge money, computers or materials each year.',
        'og_image'    => 'assets/img/site/photo-04.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Involvement · Ambassadors'],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Become a Lumina [[Ambassador]]', 'maxlength' => 140, 'help' => $hl],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 4, 'default' => 'Wherever you live, you can open doors for young people in Northern Uganda. Lumina ambassadors raise funds, lobby on our behalf and rally computers, learning materials and support from their own networks.'],
                ['key' => 'cta_label', 'label' => 'First button: text', 'type' => 'text', 'default' => 'Apply to become an ambassador'],
                ['key' => 'cta_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '#apply'],
                ['key' => 'cta2_label', 'label' => 'Second button: text (optional)', 'type' => 'text', 'default' => 'How it works'],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '#how'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg', 'alt' => 'Young people seated in the audience of an event', 'help' => 'A wide photo darkened behind the heading.'],
            ],
        ],
        [
            'key' => 'facts', 'label' => 'Highlights strip (under the banner)', 'toggle' => true,
            'fields' => [
                ['key' => 'items', 'label' => 'Highlights', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a highlight', 'max_rows' => 4,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options() + ['bell' => 'Megaphone', 'book' => 'Book'], 'default' => 'check-circle'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'One line', 'type' => 'text'],
                    ],
                    'default' => [
                        ['icon' => 'globe', 'title' => 'From anywhere in the world', 'text' => 'Every region and country is welcome.'],
                        ['icon' => 'heart', 'title' => 'You set your yearly pledge', 'text' => 'Choose an amount you are comfortable with.'],
                        ['icon' => 'laptop', 'title' => 'Money or in-kind', 'text' => 'Funds, computers and learning materials all count.'],
                        ['icon' => 'users', 'title' => 'Voluntary and flexible', 'text' => 'Give the time and reach that suit you.'],
                    ]],
            ],
        ],
        [
            'key' => 'about', 'label' => 'What is an ambassador?', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'The role'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'A voice and a bridge for [[young people]]', 'help' => $hl],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>A Lumina Africa Foundation ambassador is a supporter who represents the foundation in their own community, workplace and network. Ambassadors believe that digital inclusion is a development priority and use their influence to turn that belief into practical support.</p><p>You do not need to live in Uganda. Diaspora members, professionals, students, business people, faith and community leaders and friends of Africa are all welcome. You decide how much you can raise or give each year, and what kind of support you can bring.</p>'],
                ['key' => 'image', 'label' => 'Photo (front)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => 'Young people seated at desks in a lecture hall'],
                ['key' => 'image2', 'label' => 'Photo (behind, optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg', 'alt' => 'Young people seated in a training room, one of them typing on a laptop'],
            ],
        ],
        [
            'key' => 'roles', 'label' => 'What ambassadors do', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'What ambassadors do'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Four ways to make a [[difference]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Take on one role or all of them. Every ambassador shapes the role around their own skills and contacts.'],
                ['key' => 'items'] + $cards() + ['default' => [
                    ['icon' => 'heart', 'title' => 'Raise funds', 'text' => 'Organise appeals, events, workplace giving and online campaigns, and bring in donations on behalf of the foundation.'],
                    ['icon' => 'bell', 'title' => 'Lobby for the foundation', 'text' => 'Speak to companies, institutions, donors, schools and leaders about our work and ask for their support.'],
                    ['icon' => 'laptop', 'title' => 'Mobilise equipment and materials', 'text' => 'Gather computers, laptops, books and other learning materials that young people in Northern Uganda can use.'],
                    ['icon' => 'users', 'title' => 'Open doors', 'text' => 'Introduce us to partners, volunteers and fellow supporters, and share our story in your own network.'],
                ]],
            ],
        ],
        [
            'key' => 'pledge', 'label' => 'What you can pledge each year', 'toggle' => true,
            'help' => 'The three kinds of yearly pledge on the sign-up form: money, in-kind items and anything else.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Your yearly pledge'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Tell us what you can [[raise or give]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'When you apply you say how much you can lobby for or give each year, in money, in kind, or both. A pledge is a good-faith intention that helps us plan: it is agreed with you when your application is approved.'],
                ['key' => 'items'] + $cards() + ['default' => [
                    ['icon' => 'banknote', 'title' => 'Money', 'text' => 'The amount you can raise through your appeals and contacts, or give yourself, in a year. Choose your own currency.'],
                    ['icon' => 'laptop', 'title' => 'In-kind support', 'text' => 'Computers, laptops, tablets, projectors, books, stationery and other equipment or materials you can collect or donate in a year.'],
                    ['icon' => 'star', 'title' => 'Something else', 'text' => 'Services, scholarships, software licences, free training, venues, flights or any other support you can lobby for or give. Tell us in your own words.'],
                ]],
                ['key' => 'examples_title', 'label' => 'Heading above the examples', 'type' => 'text', 'default' => 'In-kind items we can put to use'],
                ['key' => 'examples', 'label' => 'Examples (one per line)', 'type' => 'textarea', 'rows' => 8, 'default' => "Laptops and desktop computers\nTablets and smartphones\nProjectors, printers and routers\nSolar lamps and power equipment\nBooks and learning materials\nStationery and school supplies\nSoftware licences and online subscriptions\nFurniture for training spaces"],
            ],
        ],
        [
            'key' => 'steps', 'label' => 'How it works', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'How it works'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'From application to [[impact]]', 'help' => $hl],
                ['key' => 'items', 'label' => 'Steps', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a step', 'max_rows' => 6,
                    'fields' => [['key' => 'title', 'label' => 'Title', 'type' => 'text'], ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2]],
                    'default' => [
                        ['title' => 'Apply', 'text' => 'Fill in the form below: who you are, where you live and what you can pledge each year.'],
                        ['title' => 'We get in touch', 'text' => 'Our team reads your application and contacts you to talk about your ideas and your pledge.'],
                        ['title' => 'Agree your pledge', 'text' => 'Together we agree what you will raise or give and how, and we send you a short briefing.'],
                        ['title' => 'Raise, lobby and report', 'text' => 'You start representing the foundation, and we keep you updated on what your support achieves.'],
                    ]],
            ],
        ],
        [
            'key' => 'benefits', 'label' => 'What ambassadors receive', 'toggle' => true,
            'help' => 'Edit these to match what the foundation really offers its ambassadors.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'In return'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'What you get as an [[ambassador]]', 'help' => $hl],
                ['key' => 'items'] + $cards() + ['default' => [
                    ['icon' => 'award', 'title' => 'Recognition', 'text' => 'A letter of appointment from the foundation and, with your consent, a place on our ambassadors page.'],
                    ['icon' => 'book', 'title' => 'A briefing pack', 'text' => 'Clear facts about our programs and plans, so you can speak with confidence.'],
                    ['icon' => 'users', 'title' => 'A global community', 'text' => 'Meet and share ideas with other ambassadors around the world.'],
                    ['icon' => 'check-circle', 'title' => 'Updates and accountability', 'text' => 'News on how support is used, so you can report back to your own donors and friends.'],
                ]],
            ],
        ],
        [
            'key' => 'world', 'label' => 'Anywhere in the world', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Our ambassadors'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Based anywhere, working [[together]]', 'help' => $hl],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our ambassadors can be based in any country and region. Pick yours in the form: we welcome applications from every part of the world.'],
                ['key' => 'count_label', 'label' => 'Label after the number of countries (shown only once ambassadors are approved)', 'type' => 'text', 'default' => 'countries represented'],
            ],
        ],
        [
            'key' => 'directory', 'label' => 'Meet our ambassadors', 'toggle' => true,
            'help' => 'Approved ambassadors who agreed to be shown (Admin > Inbox > Ambassadors). Hidden while there are none.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Meet them'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our [[ambassadors]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Thank you to the people who represent Lumina Africa Foundation around the world.'],
            ],
        ],
        [
            'key' => 'form', 'label' => 'Application form', 'help' => 'Headings, notes and messages of the sign-up form. Applications are stored in Admin > Inbox > Ambassadors and e-mailed to the notification address.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Apply'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Apply to become an [[ambassador]]', 'help' => $hl],
                ['key' => 'subtitle', 'label' => 'Sub-heading', 'type' => 'text', 'default' => 'It takes about five minutes.'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4, 'default' => 'Tell us about yourself, where you are in the world and what you can raise or give each year. There is no obligation: we will talk with you before anything is agreed.'],
                ['key' => 'submit_label', 'label' => 'Submit button text', 'type' => 'text', 'default' => 'Send my application'],
                ['key' => 'sending_label', 'label' => 'Submit button text while sending', 'type' => 'text', 'default' => 'Sending...'],
                ['key' => 'consent_label', 'label' => 'Consent tick box', 'type' => 'text', 'default' => 'I agree that Lumina Africa Foundation may store these details and contact me about my application.'],
                ['key' => 'public_label', 'label' => 'Public listing tick box', 'type' => 'text', 'default' => 'If I am approved, you may show my name and country on the ambassadors page.'],
                ['key' => 'privacy_note', 'label' => 'Privacy note under the button', 'type' => 'text', 'default' => 'Your details are used only to process your application. See our privacy notice.'],
                ['key' => 'success', 'label' => 'Message after a successful submission', 'type' => 'textarea', 'rows' => 3, 'default' => 'Thank you for offering to become an ambassador! We have received your application and will contact you soon. A confirmation was sent to your e-mail address.'],
                ['key' => 'error_title', 'label' => 'Error summary heading', 'type' => 'text', 'default' => 'Please check the following before sending'],
            ],
        ],
        [
            'key' => 'faq', 'label' => 'Questions and answers', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Ambassador [[questions]]', 'help' => $hl],
                ['key' => 'image', 'label' => 'Photo beside the questions (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg', 'alt' => 'A trainer points at a screen while young people work at computers'],
                ['key' => 'items', 'label' => 'Questions', 'type' => 'repeater', 'item_label' => 'question', 'add_label' => 'Add a question', 'max_rows' => 20,
                    'fields' => [['key' => 'question', 'label' => 'Question', 'type' => 'text', 'maxlength' => 300], ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 4]],
                    'default' => [
                        ['question' => 'Do I have to live in Uganda?', 'answer' => 'No. Ambassadors can live in any country in the world. Many of the most useful contacts and donors are outside Uganda.'],
                        ['question' => 'Is the ambassador role paid?', 'answer' => 'No. Ambassadors are volunteers who represent the foundation. The role is about raising support and opening doors, not employment.'],
                        ['question' => 'How much do I have to raise or give?', 'answer' => 'There is no fixed amount. You tell us what you can realistically lobby for or give in a year, in money, in kind or both, and we agree it with you.'],
                        ['question' => 'Can I give computers or materials instead of money?', 'answer' => 'Yes. In-kind support is very welcome: computers, laptops, books and other equipment. Tell us what you can collect and where it is, and we will discuss how it can reach us.'],
                        ['question' => 'Can I represent a company or an organisation?', 'answer' => 'Yes. You can apply as an individual who represents your employer, your school, your congregation or your community group. Tell us about it in the form.'],
                        ['question' => 'What happens after I apply?', 'answer' => 'Our team reviews every application and contacts you by e-mail. If it is a good fit we agree your pledge and send your briefing pack.'],
                    ],
                ],
            ],
        ],
    ],
];
