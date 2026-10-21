<?php
/**
 * Registry entry: LAF ACADEMY (the free ICT learning portal: home / course catalogue, course pages, sign-up and sign-in screens, learner dashboard, the lesson
 * player, quizzes, virtual classes and certificates). The courses, lessons, quizzes, classes and learners are managed in Admin > LAF Academy; this page holds the
 * wording around them. Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 */
$hl = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'academy',
    'title' => 'LAF Academy',
    'route' => '/academy',
    'help'  => 'The LAF Academy home page and the words used on its sign-up, sign-in and learner screens. Courses, lessons, quizzes, virtual classes and learners are managed under LAF Academy in the admin menu.',
    'nav'   => ['label' => 'LAF Academy', 'order' => 45, 'header' => true, 'footer' => true],
    'seo'   => [
        'title'       => 'LAF Academy - Free ICT Skills Courses and Certificates',
        'description' => 'Learn computer basics, Microsoft Word, Excel, PowerPoint, email, digital marketing, online safety and more with free online courses, virtual classes and certificates from Lumina Africa Foundation.',
        'og_image'    => 'assets/img/site/photo-05.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Lumina Africa Foundation'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'LAF [[Academy]]: learn ICT skills for free', 'maxlength' => 140, 'help' => $hl],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 4, 'default' => 'Free, practical courses in computer basics, Microsoft Office, email, digital marketing, online safety and more. Learn at your own pace, join live virtual classes with facilitators, test yourself and earn a certificate.'],
                ['key' => 'cta1_label', 'label' => 'First button: text', 'type' => 'text', 'default' => 'Create a free account'],
                ['key' => 'cta1_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '/academy/register'],
                ['key' => 'cta2_label', 'label' => 'Second button: text', 'type' => 'text', 'default' => 'Browse the courses'],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '#courses'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-05.jpg',
                    'alt' => 'Young people seated in a training room, one of them typing on a laptop', 'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'steps', 'label' => 'How it works', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'How the Academy [[works]]', 'help' => $hl],
                ['key' => 'items', 'label' => 'Steps', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a step', 'max_rows' => 6,
                    'fields' => [['key' => 'title', 'label' => 'Title', 'type' => 'text'], ['key' => 'text', 'label' => 'Text', 'type' => 'textarea']],
                    'default' => [
                        ['title' => 'Create your account', 'text' => 'Sign up with your e-mail address and confirm it. It is free.'],
                        ['title' => 'Pick a course', 'text' => 'Start with Computer Basics or choose the skill you need: Word, Excel, email, marketing and more.'],
                        ['title' => 'Learn and practise', 'text' => 'Read the lessons, follow the practice activities and join virtual classes with a facilitator.'],
                        ['title' => 'Test and get certified', 'text' => 'Take the quizzes, pass the final exam and download your certificate.'],
                    ]],
            ],
        ],
        [
            'key' => 'courses', 'label' => 'Course list',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => 'Free courses'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Choose your [[course]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'Every course has short lessons with practice activities, a quiz after each module and a final exam. Pass the final exam to earn your certificate.'],
                ['key' => 'search_label', 'label' => 'Search box: label', 'type' => 'text', 'default' => 'Search courses'],
                ['key' => 'search_placeholder', 'label' => 'Search box: hint', 'type' => 'text', 'default' => 'For example: Word, email, marketing'],
                ['key' => 'category_label', 'label' => 'Topic menu: label', 'type' => 'text', 'default' => 'Topic'],
                ['key' => 'all_topics', 'label' => 'Topic menu: first choice', 'type' => 'text', 'default' => 'All topics'],
                ['key' => 'level_label', 'label' => 'Level menu: label', 'type' => 'text', 'default' => 'Level'],
                ['key' => 'all_levels', 'label' => 'Level menu: first choice', 'type' => 'text', 'default' => 'All levels'],
                ['key' => 'apply_filter', 'label' => 'Search button', 'type' => 'text', 'default' => 'Search'],
                ['key' => 'reset', 'label' => 'Clear button', 'type' => 'text', 'default' => 'Clear'],
                ['key' => 'card_button', 'label' => 'Course card: button', 'type' => 'text', 'default' => 'View course'],
                ['key' => 'empty_title', 'label' => 'When there is no course: headline', 'type' => 'text', 'default' => 'No courses found'],
                ['key' => 'empty_text', 'label' => 'When there is no course: text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Try a different word or topic, or check again soon. New courses are added regularly.'],
            ],
        ],
        [
            'key' => 'partners', 'label' => 'Courses from other platforms',
            'help' => 'A curated list of free courses on other learning platforms (Coursera, edX, Google, Microsoft Learn, Harvard CS50 ...). The list itself is managed under LAF Academy > Partner courses.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Home page: small heading', 'type' => 'text', 'default' => 'Keep going'],
                ['key' => 'title', 'label' => 'Home page: heading', 'type' => 'text', 'default' => 'More free courses from [[trusted platforms]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Home page: intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Beyond our own courses, we gathered free learning from universities, Google, Microsoft and other platforms, from beginner to advanced.'],
                ['key' => 'button', 'label' => 'Home page: button', 'type' => 'text', 'default' => 'See all partner courses'],
                ['key' => 'page_title', 'label' => 'Partner courses page: heading', 'type' => 'text', 'default' => 'Free courses from [[other platforms]]', 'help' => $hl],
                ['key' => 'page_lead', 'label' => 'Partner courses page: intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'A hand-picked list of free courses from well-known learning platforms, grouped by topic and level. Start with a beginner course, then move up.'],
                ['key' => 'disclaimer', 'label' => 'Partner courses page: note', 'type' => 'textarea', 'rows' => 3, 'default' => 'These courses are run by their own providers on their own websites. Lumina Africa Foundation does not run them and cannot promise their content, prices or certificates: free access and certificate rules are set by each provider and can change. Never pay anyone to give you access to a free course.'],
                ['key' => 'open_button', 'label' => 'Course card: button', 'type' => 'text', 'default' => 'Open the course'],
                ['key' => 'empty_title', 'label' => 'When nothing matches: headline', 'type' => 'text', 'default' => 'No course found'],
                ['key' => 'empty_text', 'label' => 'When nothing matches: text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Try a different word, topic or level.'],
            ],
        ],
        [
            'key' => 'why', 'label' => 'Why learn here', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Skills that open [[doors]]', 'help' => $hl],
                ['key' => 'items', 'label' => 'Reasons', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a reason', 'max_rows' => 8,
                    'fields' => [['key' => 'title', 'label' => 'Title', 'type' => 'text'], ['key' => 'text', 'label' => 'Text', 'type' => 'textarea']],
                    'default' => [
                        ['title' => 'Free for everyone', 'text' => 'No fees. All you need is a phone or computer and an internet connection.'],
                        ['title' => 'Made for beginners', 'text' => 'Plain language, short lessons and practice activities that start from zero.'],
                        ['title' => 'Learn your way', 'text' => 'Study when it suits you, on a phone or computer, and pick up where you stopped.'],
                        ['title' => 'Live support', 'text' => 'Join virtual classes with facilitators and ask questions in real time.'],
                        ['title' => 'Certificates that count', 'text' => 'Pass the final exam and download a certificate with a number anyone can verify online.'],
                        ['title' => 'Work-ready skills', 'text' => 'Office tools, email, digital marketing and online safety: the skills employers and customers ask for.'],
                    ]],
            ],
        ],
        [
            'key' => 'classes', 'label' => 'Virtual classes teaser', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Live [[virtual classes]]', 'help' => $hl],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Join a facilitator online to practise together and ask questions. Sign in to see the join link.'],
                ['key' => 'button', 'label' => 'Button', 'type' => 'text', 'default' => 'See all classes'],
            ],
        ],
        [
            'key' => 'cta', 'label' => 'Closing band', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Ready to [[start learning]]?', 'help' => $hl],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Create your free account in a minute and begin with your first lesson today.'],
                ['key' => 'button_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Create a free account'],
                ['key' => 'button_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/academy/register'],
            ],
        ],
        [
            'key' => 'auth', 'label' => 'Sign-up and sign-in screens',
            'fields' => [
                ['key' => 'register_title', 'label' => 'Sign-up: heading', 'type' => 'text', 'default' => 'Create your free account'],
                ['key' => 'register_intro', 'label' => 'Sign-up: intro', 'type' => 'textarea', 'rows' => 2, 'default' => 'Your name is printed on your certificates, so please write it as it should appear.'],
                ['key' => 'register_closed', 'label' => 'Sign-up: message when sign-ups are switched off', 'type' => 'text', 'default' => 'New sign-ups are closed for now. If you already have an account, please sign in.'],
                ['key' => 'consent', 'label' => 'Sign-up: agreement text', 'type' => 'text', 'default' => 'I agree that my name and e-mail address are kept to run my learning account and to issue my certificates.'],
                ['key' => 'login_title', 'label' => 'Sign-in: heading', 'type' => 'text', 'default' => 'Sign in to your learning account'],
                ['key' => 'login_intro', 'label' => 'Sign-in: intro', 'type' => 'text', 'default' => 'Welcome back. Continue where you stopped.'],
                ['key' => 'check_email_title', 'label' => 'After sign-up: heading', 'type' => 'text', 'default' => 'Check your e-mail'],
                ['key' => 'check_email_text', 'label' => 'After sign-up: text', 'type' => 'textarea', 'rows' => 3, 'default' => 'We sent a link to confirm your address. Open it to activate your account, then sign in. If nothing arrives within a few minutes, look in your spam folder.'],
            ],
        ],
        [
            'key' => 'learn', 'label' => 'Learner screens',
            'fields' => [
                ['key' => 'dashboard_title', 'label' => 'Dashboard: heading', 'type' => 'text', 'default' => 'My learning'],
                ['key' => 'dashboard_empty', 'label' => 'Dashboard: when no course is started', 'type' => 'text', 'default' => 'You have not started a course yet. Choose one to begin.'],
                ['key' => 'pass_note', 'label' => 'Final exam: note on the start screen', 'type' => 'text', 'default' => 'You can take the final exam when you have finished every lesson. Read each question carefully.'],
                ['key' => 'certificate_note', 'label' => 'Certificate page: note', 'type' => 'text', 'default' => 'This page confirms that the certificate below was issued by the LAF Academy.'],
            ],
        ],
    ],
];
