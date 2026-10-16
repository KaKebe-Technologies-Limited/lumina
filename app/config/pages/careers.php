<?php
/**
 * Registry entry: CAREERS (job list, job page labels, general expression-of-interest form). OWNER: B2 "web-content".
 * Wording: https://www.luminaafricafoundation.com/careers (see docs/live-snapshot/careers.txt). Vacancies themselves are
 * written in the admin under Content > Jobs; applications arrive under Inbox > Applications.
 */
return [
    'slug'  => 'careers',
    'title' => 'Careers',
    'route' => '/careers',
    'help'  => 'The Careers page: introduction, job openings (written under Content > Jobs), why join us, recruitment steps, volunteering, FAQs and the application form wording.',
    'nav'   => ['label' => 'Careers', 'order' => 51, 'header' => true, 'footer' => true, 'parent' => 'involvement'],
    'seo'   => [
        'title'       => 'Join Our Team - Career Opportunities',
        'description' => 'Careers at Lumina Africa Foundation: volunteer, intern or work with us to light the path to opportunity for youth, women and marginalized communities in Northern Uganda.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Careers at Lumina Africa Foundation', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 5, 'default' => 'At Lumina Africa Foundation, we believe in people. Our impact is driven by passionate individuals who dedicate their time, talent, and energy to transforming lives. Whether you are a volunteer, intern, or professional, joining our team means becoming part of a movement that is lighting the path to opportunity for youth, women, and marginalized communities in Northern Uganda and beyond.'],
                ['key' => 'cta1_label', 'label' => 'First button: text', 'type' => 'text', 'default' => 'Job Openings'],
                ['key' => 'cta1_url', 'label' => 'First button: link', 'type' => 'url', 'default' => '#openings'],
                ['key' => 'cta2_label', 'label' => 'Second button: text', 'type' => 'text', 'default' => 'Volunteer Opportunities'],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '#volunteer'],
            ],
        ],
        [
            'key' => 'openings', 'label' => 'Job openings', 'help' => 'Vacancies are listed automatically from Content > Jobs. When there are none, the "no openings" text and the general application form are shown.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Job Openings'],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'Occasionally, we seek talented individuals to enhance our expanding team. If you have a passion for social impact and a commitment to excellence, we would be thrilled to collaborate with you.'],
                ['key' => 'available_label', 'label' => 'Heading above the vacancies', 'type' => 'text', 'default' => 'Available Positions:'],
                ['key' => 'none_text', 'label' => 'When there are no vacancies', 'type' => 'text', 'default' => 'Currently, there are no openings.'],
                ['key' => 'none_help', 'label' => 'When there are no vacancies: extra line', 'type' => 'textarea', 'rows' => 2, 'default' => 'You can still tell us about yourself using the form below, and we will keep your details in mind for future openings.'],
                ['key' => 'none_button_label', 'label' => 'When there are no vacancies: button', 'type' => 'text', 'default' => 'Join Our Talent Network'],
                ['key' => 'none_button_url', 'label' => 'When there are no vacancies: button link', 'type' => 'url', 'default' => '#apply'],
                ['key' => 'card_button', 'label' => 'Vacancy card: button', 'type' => 'text', 'default' => 'View details and apply'],
                ['key' => 'closing_label', 'label' => 'Vacancy card: closing date label', 'type' => 'text', 'default' => 'Closing date'],
            ],
        ],
        [
            'key' => 'why', 'label' => 'Why Join Us?', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Why Join Us?'],
                ['key' => 'items', 'label' => 'Reasons', 'type' => 'repeater', 'item_label' => 'text', 'add_label' => 'Add a reason', 'max_rows' => 10,
                    'fields' => [['key' => 'text', 'label' => 'Text', 'type' => 'textarea']],
                    'default' => [
                        ['text' => 'Be part of a purpose-driven organization focused on health, education, digital innovation, leadership, and sustainable development.'],
                        ['text' => 'Gain valuable experience working directly with communities and partners.'],
                        ['text' => 'Receive mentorship and growth opportunities while contributing to real impact.'],
                        ['text' => 'Join a team that values integrity, collaboration, and innovation.'],
                    ]],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg', 'alt' => 'A trainer pointing at a screen while young people work at computers in a training room'],
            ],
        ],
        [
            'key' => 'process', 'label' => 'Our Recruitment Process', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our Recruitment Process'],
                ['key' => 'steps', 'label' => 'Steps', 'type' => 'repeater', 'item_label' => 'text', 'add_label' => 'Add a step', 'max_rows' => 10,
                    'fields' => [['key' => 'text', 'label' => 'Step', 'type' => 'textarea']],
                    'default' => [
                        ['text' => 'Submit your application via our online form.'],
                        ['text' => 'Applications are reviewed by the HR & Programs team.'],
                        ['text' => 'Shortlisted candidates are contacted for interviews or assessments.'],
                        ['text' => 'Successful candidates are onboarded and supported through training and orientation.'],
                    ]],
            ],
        ],
        [
            'key' => 'volunteer', 'label' => 'Volunteer opportunities', 'toggle' => true,
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Opportunities Available'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Volunteer Opportunities'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Volunteers are at the heart of what we do. By donating your time and skills, you help us extend our reach and maximize our impact.'],
                ['key' => 'roles_title', 'label' => 'Roles heading', 'type' => 'text', 'default' => 'Roles include:'],
                ['key' => 'roles', 'label' => 'Roles', 'type' => 'repeater', 'item_label' => 'text', 'add_label' => 'Add a role', 'max_rows' => 12,
                    'fields' => [['key' => 'text', 'label' => 'Role', 'type' => 'text']],
                    'default' => [
                        ['text' => 'Community mobilization'],
                        ['text' => 'Program support (health, education, ICT, environment)'],
                        ['text' => 'Media & communications'],
                        ['text' => 'Fundraising & partnerships'],
                    ]],
                ['key' => 'button_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Apply here'],
                ['key' => 'button_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '#apply', 'help' => '"#apply" jumps to the application form on this page. The foundation’s earlier Google Form (https://docs.google.com/forms/d/e/1FAIpQLScSOUCFnTsaAG6c35kO-isA1snRH-OlXxQPBdQerKofpjggHQ/viewform) can be pasted here instead.'],
            ],
        ],
        [
            'key' => 'equal', 'label' => 'Equal Opportunity Statement', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Equal Opportunity Statement'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Lumina Africa Foundation is an equal opportunity organization. We celebrate diversity and are committed to creating an inclusive environment for all staff, volunteers, and partners.'],
            ],
        ],
        [
            'key' => 'faq', 'label' => 'Career Page FAQs', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Career Page FAQs'],
                ['key' => 'items', 'label' => 'Questions and answers', 'type' => 'repeater', 'item_label' => 'question', 'add_label' => 'Add a question', 'max_rows' => 20,
                    'fields' => [
                        ['key' => 'question', 'label' => 'Question', 'type' => 'text'],
                        ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea'],
                    ],
                    'default' => [
                        ['question' => 'How to apply for jobs?', 'answer' => 'Visit our career page and submit your application online.'],
                        ['question' => 'What updates will I receive?', 'answer' => 'You will receive notifications about new job openings and HR activities.'],
                        ['question' => 'Can I update my application?', 'answer' => 'Yes. Reach our HR team through the contact form on our website and tell us what you would like to change.'],
                        ['question' => 'What is the application process?', 'answer' => 'The process includes submitting your resume and completing an online form.'],
                        ['question' => 'Who can I contact for help?', 'answer' => 'You can reach our HR team via the contact form on our website.'],
                    ]],
            ],
        ],
        [
            'key' => 'apply', 'label' => 'General application (Join Our Talent Network)',
            'help' => 'The form used when there is no specific vacancy (an expression of interest). Its field labels are in the "Application form wording" section below.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Join Our Talent Network'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Stay updated on job openings and HR news.'],
                ['key' => 'message_help', 'label' => 'Message box: help text', 'type' => 'text', 'default' => 'Tell us about yourself and how you would like to be involved.'],
            ],
        ],
        [
            'key' => 'cta', 'label' => 'Get Involved Today (closing band)', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Get Involved Today'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Your skills, passion, and dedication can change lives. Join us and be part of the journey to empower communities and inspire the next generation.'],
                ['key' => 'quote', 'label' => 'Closing line', 'type' => 'textarea', 'rows' => 2, 'default' => 'At Lumina Africa Foundation, this is more than a career. It’s a calling to serve, inspire, and transform.'],
            ],
        ],
        [
            'key' => 'types', 'label' => 'Names of the types of work', 'help' => 'Shown on the job cards and job pages.',
            'fields' => [
                ['key' => 'full_time', 'label' => 'Full time', 'type' => 'text', 'default' => 'Full time', 'maxlength' => 40],
                ['key' => 'part_time', 'label' => 'Part time', 'type' => 'text', 'default' => 'Part time', 'maxlength' => 40],
                ['key' => 'contract', 'label' => 'Contract', 'type' => 'text', 'default' => 'Contract', 'maxlength' => 40],
                ['key' => 'internship', 'label' => 'Internship', 'type' => 'text', 'default' => 'Internship', 'maxlength' => 40],
                ['key' => 'volunteer', 'label' => 'Volunteer', 'type' => 'text', 'default' => 'Volunteer', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'job', 'label' => 'Single job page: labels',
            'fields' => [
                ['key' => 'back_label', 'label' => 'Link back to the list', 'type' => 'text', 'default' => 'Back to job openings'],
                ['key' => 'type_label', 'label' => 'Employment type label', 'type' => 'text', 'default' => 'Type'],
                ['key' => 'department_label', 'label' => 'Department label', 'type' => 'text', 'default' => 'Department'],
                ['key' => 'location_label', 'label' => 'Location label', 'type' => 'text', 'default' => 'Location'],
                ['key' => 'closing_label', 'label' => 'Closing date label', 'type' => 'text', 'default' => 'Closing date'],
                ['key' => 'description_title', 'label' => 'Description heading', 'type' => 'text', 'default' => 'About the role'],
                ['key' => 'requirements_title', 'label' => 'Requirements heading', 'type' => 'text', 'default' => 'Requirements'],
                ['key' => 'how_title', 'label' => 'How-to-apply heading', 'type' => 'text', 'default' => 'How to apply'],
                ['key' => 'email_label', 'label' => 'Apply-by-email line', 'type' => 'text', 'default' => 'You can also send your application by email to'],
                ['key' => 'apply_title', 'label' => 'Application form heading', 'type' => 'text', 'default' => 'Apply for this position'],
                ['key' => 'closed_text', 'label' => 'Closed vacancy notice', 'type' => 'text', 'default' => 'Applications for this position are now closed.'],
                ['key' => 'closed_button', 'label' => 'Closed vacancy: button', 'type' => 'text', 'default' => 'See other openings'],
            ],
        ],
        [
            'key' => 'form', 'label' => 'Application form wording', 'help' => 'Labels, help text and messages for the application forms (job page and talent network).',
            'fields' => [
                ['key' => 'name_label', 'label' => 'Name: label', 'type' => 'text', 'default' => 'Your name'],
                ['key' => 'email_label', 'label' => 'Email: label', 'type' => 'text', 'default' => 'Your email'],
                ['key' => 'phone_label', 'label' => 'Phone: label', 'type' => 'text', 'default' => 'Contact (phone number)'],
                ['key' => 'message_label', 'label' => 'Message / cover letter: label', 'type' => 'text', 'default' => 'Message'],
                ['key' => 'cv_label', 'label' => 'CV: label', 'type' => 'text', 'default' => 'CV / resume (optional)'],
                ['key' => 'cv_help', 'label' => 'CV: help text', 'type' => 'text', 'default' => 'PDF, DOC or DOCX, up to 5 MB.'],
                ['key' => 'cv_error_size', 'label' => 'CV: file too large (shown before sending)', 'type' => 'text', 'default' => 'That file is larger than 5 MB. Please choose a smaller file.'],
                ['key' => 'cv_error_type', 'label' => 'CV: wrong file type (shown before sending)', 'type' => 'text', 'default' => 'Please choose a PDF, DOC or DOCX file.'],
                ['key' => 'linkedin_label', 'label' => 'LinkedIn: label', 'type' => 'text', 'default' => 'LinkedIn profile (optional)'],
                ['key' => 'consent_label', 'label' => 'Consent tick box', 'type' => 'text', 'default' => 'I agree that Lumina Africa Foundation may store the details I submit in order to consider my application.'],
                ['key' => 'privacy_link_label', 'label' => 'Link to the privacy notice', 'type' => 'text', 'default' => 'Read our Privacy Policy'],
                ['key' => 'submit', 'label' => 'Submit button', 'type' => 'text', 'default' => 'Submit'],
                ['key' => 'sending', 'label' => 'Submit button: text while sending', 'type' => 'text', 'default' => 'Sending…'],
                ['key' => 'submit_job', 'label' => 'Submit button (specific job)', 'type' => 'text', 'default' => 'Submit application'],
                ['key' => 'success', 'label' => 'Success message (general application)', 'type' => 'textarea', 'rows' => 2, 'default' => 'Thank you. Your details have been received and our HR & Programs team will be in touch if a suitable opportunity arises.'],
                ['key' => 'success_job', 'label' => 'Success message (specific job)', 'type' => 'textarea', 'rows' => 2, 'default' => 'Thank you. Your application has been received. Our HR & Programs team will review it and contact shortlisted candidates.'],
                ['key' => 'closed_error', 'label' => 'Error: vacancy already closed', 'type' => 'text', 'default' => 'Sorry, applications for this position have closed.'],
                ['key' => 'err_required', 'label' => 'Error: required field', 'type' => 'text', 'default' => 'Please fill in this field.'],
                ['key' => 'err_email', 'label' => 'Error: invalid email', 'type' => 'text', 'default' => 'Please enter a valid email address.'],
                ['key' => 'err_phone', 'label' => 'Error: invalid phone', 'type' => 'text', 'default' => 'Please enter a valid phone number.'],
                ['key' => 'err_max', 'label' => 'Error: text too long', 'type' => 'text', 'default' => 'That is too long. Please shorten it.'],
                ['key' => 'err_url', 'label' => 'Error: invalid web address', 'type' => 'text', 'default' => 'Please enter a full web address starting with https://'],
                ['key' => 'err_consent', 'label' => 'Error: consent not ticked', 'type' => 'text', 'default' => 'Please tick the box to confirm that you agree.'],
                ['key' => 'notify_subject', 'label' => 'Notification email subject (to the foundation)', 'type' => 'text', 'default' => 'New job application', 'help' => 'Emailed to the notification address in Settings. The vacancy title is added after it.'],
                ['key' => 'notify_subject_general', 'label' => 'Notification email subject (general application)', 'type' => 'text', 'default' => 'New talent network submission'],
            ],
        ],
    ],
];
