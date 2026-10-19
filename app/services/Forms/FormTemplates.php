<?php
declare(strict_types=1);

namespace App\Services\Forms;

/**
 * THE NINE FORM TEMPLATES (code-defined definitions; "New form" copies one into an ordinary, fully editable form). Contract: docs/FORMS.md section 11.
 *
 *   FormTemplates::keys()                 // ['job_application', 'volunteer_registration', ...]
 *   FormTemplates::listing()              // for the picker: key, title, description, icon, kind, pages, fields
 *   FormTemplates::get('job_application') // ['key','title','description','icon','kind','definition','settings']   (normalised; stable symbolic ids)
 *   FormTemplates::instantiate($key)      // the same with FRESH ids (and conditions remapped) - what create() stores
 *
 * Every template must pass FormDefinition::normalise() and have no `error` in FormDefinition::publishChecklist() (bin/selftest-forms.php proves it).
 * They contain no organisation facts: wording is neutral, and the owner edits everything in the builder.
 */
final class FormTemplates
{
    /** @var array<string,array<string,mixed>>|null */
    private static ?array $cache = null;

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /** @return list<array{key:string,title:string,description:string,icon:string,kind:string,pages:int,fields:int}> */
    public static function listing(): array
    {
        $out = [];
        foreach (self::all() as $key => $t) {
            $out[] = [
                'key'         => $key,
                'title'       => $t['title'],
                'description' => $t['description'],
                'icon'        => $t['icon'],
                'kind'        => $t['kind'],
                'pages'       => count($t['definition']['pages']),
                'fields'      => count(FormDefinition::inputFields($t['definition'])),
            ];
        }
        return $out;
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** A copy ready to be stored: fresh ids, same keys. @return array<string,mixed>|null */
    public static function instantiate(string $key): ?array
    {
        $t = self::get($key);
        if ($t === null) {
            return null;
        }
        $map = [];
        $t['definition'] = FormDefinition::reidentify($t['definition'], $map);
        $ef = (string) ($t['settings']['confirmation']['email_field'] ?? '');
        if ($ef !== '') {
            $t['settings']['confirmation']['email_field'] = $map[$ef] ?? '';
        }
        return $t;
    }

    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $out = [];
        foreach (self::raw() as $key => $t) {
            $norm = FormDefinition::normalise(['pages' => $t['pages']]);
            $set = FormSettings::normalise($t['settings'] + ['kind' => $t['kind']], $t['kind']);
            if (!$norm['ok']) {
                continue;                                   // a broken template must never take the module down (the selftest fails loudly instead)
            }
            $out[$key] = [
                'key'         => $key,
                'title'       => $t['title'],
                'description' => $t['description'],
                'icon'        => $t['icon'],
                'kind'        => $t['kind'],
                'definition'  => $norm['definition'],
                'settings'    => $set['settings'],
            ];
        }
        return self::$cache = $out;
    }

    // ------------------------------------------------------------------ the tiny DSL

    /** Symbolic id of a key: letters and digits only, at least 3 characters. */
    private static function id(string $key): string
    {
        $id = (string) preg_replace('/[^a-z0-9]/', '', strtolower($key));
        return 'f' . substr($id, 0, 20);
    }

    /**
     * A field. $o: required, help, placeholder, width, default, options (list of labels), other (allow_other), min_length, max_length, min, max, pattern,
     * types/max_mb/max_files (file), show (logic: [key, op, value] or list of those; `any` => true makes it OR).
     * @param array<string,mixed> $o
     */
    private static function f(string $type, string $key, string $label, array $o = []): array
    {
        $field = ['id' => self::id($key), 'key' => $key, 'type' => $type, 'label' => $label, 'required' => !empty($o['required']), 'width' => $o['width'] ?? 'full'];
        foreach (['help', 'placeholder', 'default', 'text'] as $k) {
            if (isset($o[$k])) {
                $field[$k] = $o[$k];
            }
        }
        if (isset($o['options'])) {
            $field['options'] = array_map(static fn($x): array => is_array($x) ? $x : ['label' => (string) $x], $o['options']);
        }
        if (!empty($o['other'])) {
            $field['allow_other'] = true;
        }
        $val = [];
        foreach (['min_length', 'max_length', 'min', 'max', 'pattern'] as $k) {
            if (isset($o[$k])) {
                $val[$k] = $o[$k];
            }
        }
        if ($val !== []) {
            $field['validation'] = $val;
        }
        if ($type === 'file') {
            $field['file'] = ['types' => $o['types'] ?? ['pdf'], 'max_mb' => $o['max_mb'] ?? 5, 'max_files' => $o['max_files'] ?? 1];
        }
        if (isset($o['show'])) {
            $field['logic'] = self::logic($o['show'], !empty($o['any']));
        }
        return $field;
    }

    /** @param array<int,mixed> $show [key, op, value] or a list of such triples */
    private static function logic(array $show, bool $any = false): array
    {
        $rules = is_array($show[0] ?? null) ? $show : [$show];
        return [
            'match' => $any ? 'any' : 'all',
            'rules' => array_map(static fn(array $r): array => ['field' => self::id((string) $r[0]), 'op' => (string) $r[1], 'value' => (string) ($r[2] ?? '')], $rules),
        ];
    }

    private static function page(string $title, array $fields, string $description = '', ?array $show = null): array
    {
        return ['id' => 'p' . substr(md5($title), 0, 6), 'title' => $title, 'description' => $description, 'fields' => $fields, 'logic' => $show !== null ? self::logic($show) : null];
    }

    private static function note(string $key, string $text): array
    {
        return ['id' => self::id($key), 'type' => 'paragraph', 'label' => '', 'text' => $text];
    }

    private static function consent(string $text): array
    {
        return self::f('consent', 'consent', $text, ['required' => true]);
    }

    /** @return array<string,array<string,mixed>> */
    private static function raw(): array
    {
        $f = [self::class, 'f'];
        $page = [self::class, 'page'];
        $yes = [['value' => 'yes', 'label' => 'Yes'], ['value' => 'no', 'label' => 'No']];

        $notifyAnswers = ['notify' => ['enabled' => true, 'emails' => [], 'include_answers' => false]];
        $confirm = static fn(string $field, string $subject, string $body): array => ['confirmation' => ['enabled' => true, 'email_field' => self::id($field), 'subject' => $subject, 'body' => $body]];

        return [
            // ------------------------------------------------------------------------------------------------ 1 job application
            'job_application' => [
                'title' => 'Job application', 'icon' => 'briefcase', 'kind' => 'job',
                'description' => 'Collect applications for a vacancy: contact details, CV upload, cover letter, availability and referees. Attach it to a job in Content > Jobs.',
                'pages' => [
                    $page('Your details', [
                        $f('text', 'full_name', 'Full name', ['required' => true, 'max_length' => 120, 'width' => 'half']),
                        $f('email', 'email', 'Email address', ['required' => true, 'width' => 'half']),
                        $f('phone', 'phone', 'Phone number', ['required' => true, 'width' => 'half', 'help' => 'For example 0700 123456 or +256 700 123456.']),
                        $f('text', 'location', 'Where do you live?', ['required' => true, 'width' => 'half', 'placeholder' => 'Town or city, country', 'max_length' => 120]),
                    ], 'Tell us how to reach you.'),
                    $page('Your application', [
                        $f('file', 'cv', 'CV or resume', ['required' => true, 'types' => ['pdf', 'doc', 'docx'], 'max_mb' => 5, 'help' => 'PDF or Word document.']),
                        $f('textarea', 'cover_letter', 'Cover letter', ['max_length' => 3000, 'help' => 'Optional. Tell us why you are a good fit.']),
                        $f('text', 'profile_link', 'LinkedIn or portfolio link', ['pattern' => 'url', 'placeholder' => 'https://', 'help' => 'Optional.']),
                        $f('select', 'availability', 'When could you start?', ['required' => true, 'options' => ['Immediately', 'Within two weeks', 'Within one month', 'More than one month']]),
                        $f('textarea', 'referees', 'Referees', ['max_length' => 1500, 'help' => 'Optional. Names and contact details of up to two people who can speak about your work.']),
                        self::consent('I confirm that the information I have given is correct, and I agree that it may be stored and used to assess my application.'),
                    ]),
                ],
                'settings' => $confirm('email', 'We received your application', "Dear {full_name},\n\nThank you for applying. We have received your application and will be in touch if you are shortlisted.\n\nKind regards,\n{site}")
                    + ['success' => ['mode' => 'message', 'message_html' => '<p>Thank you. Your application has been received. We will contact you if you are shortlisted.</p>', 'redirect_path' => '']]
                    + $notifyAnswers + ['spam' => 'standard', 'seo' => ['noindex' => true], 'theme' => ['accent' => 'orange', 'layout' => 'stepped', 'progress' => true]],
            ],

            // ------------------------------------------------------------------------------------------------ 2 volunteer
            'volunteer_registration' => [
                'title' => 'Volunteer registration', 'icon' => 'hand-heart', 'kind' => 'general',
                'description' => 'Invite people to volunteer: contact details, interests, availability and experience.',
                'pages' => [
                    $page('About you', [
                        $f('text', 'full_name', 'Full name', ['required' => true, 'max_length' => 120, 'width' => 'half']),
                        $f('email', 'email', 'Email address', ['required' => true, 'width' => 'half']),
                        $f('phone', 'phone', 'Phone number', ['required' => true, 'width' => 'half']),
                        $f('text', 'location', 'Town or district', ['required' => true, 'width' => 'half', 'max_length' => 120]),
                        $f('radio', 'age_group', 'Your age group', ['required' => true, 'options' => [['value' => 'under-18', 'label' => 'Under 18'], ['value' => '18-24', 'label' => '18 to 24'], ['value' => '25-34', 'label' => '25 to 34'], ['value' => '35-plus', 'label' => '35 or older']]]),
                        $f('text', 'guardian_name', 'Parent or guardian name', ['required' => true, 'width' => 'half', 'show' => ['age_group', 'equals', 'under-18']]),
                        $f('phone', 'guardian_phone', 'Parent or guardian phone', ['required' => true, 'width' => 'half', 'show' => ['age_group', 'equals', 'under-18']]),
                    ]),
                    $page('How you can help', [
                        $f('checkboxes', 'interests', 'What would you like to help with?', ['required' => true, 'other' => true, 'options' => ['Teaching and mentoring', 'Digital skills training', 'Events and outreach', 'Fundraising', 'Communications and media', 'Administration']]),
                        $f('checkboxes', 'availability', 'When are you available?', ['required' => true, 'options' => ['Weekdays', 'Evenings', 'Weekends']]),
                        $f('select', 'hours', 'Hours per week you can give', ['options' => ['Up to 2 hours', '3 to 5 hours', '6 to 10 hours', 'More than 10 hours']]),
                        $f('textarea', 'experience', 'Skills and experience', ['max_length' => 2000, 'help' => 'Optional.']),
                        $f('textarea', 'motivation', 'Why would you like to volunteer?', ['max_length' => 2000]),
                        self::consent('I agree that my information may be stored and used to contact me about volunteering.'),
                    ]),
                ],
                'settings' => $confirm('email', 'Thank you for volunteering', "Dear {full_name},\n\nThank you for offering your time. We have received your details and will contact you soon.\n\n{site}")
                    + ['success' => ['mode' => 'message', 'message_html' => '<p>Thank you for volunteering. We will be in touch soon.</p>', 'redirect_path' => '']] + $notifyAnswers,
            ],

            // ------------------------------------------------------------------------------------------------ 3 event rsvp
            'event_rsvp' => [
                'title' => 'Event or workshop RSVP', 'icon' => 'calendar', 'kind' => 'general',
                'description' => 'Count who is coming to an event or workshop, with guests, dietary needs and accessibility.',
                'pages' => [
                    $page('', [
                        $f('text', 'full_name', 'Full name', ['required' => true, 'max_length' => 120, 'width' => 'half']),
                        $f('email', 'email', 'Email address', ['required' => true, 'width' => 'half']),
                        $f('phone', 'phone', 'Phone number', ['width' => 'half', 'help' => 'Optional. Only used if the event changes.']),
                        $f('text', 'organisation', 'Organisation', ['width' => 'half', 'max_length' => 150, 'help' => 'Optional.']),
                        $f('radio', 'attending', 'Will you attend?', ['required' => true, 'options' => [['value' => 'yes', 'label' => 'Yes, I will attend'], ['value' => 'maybe', 'label' => 'Maybe'], ['value' => 'no', 'label' => 'No, I cannot attend']]]),
                        $f('number', 'guests', 'How many guests are you bringing?', ['min' => 0, 'max' => 10, 'default' => '0', 'width' => 'half', 'show' => ['attending', 'equals', 'yes']]),
                        $f('checkboxes', 'dietary', 'Any food requirements?', ['other' => true, 'options' => ['Vegetarian', 'Halal', 'No pork', 'Allergy'], 'show' => ['attending', 'equals', 'yes']]),
                        $f('textarea', 'access', 'Accessibility needs', ['max_length' => 1000, 'help' => 'Optional. Tell us how we can make the event easier for you.']),
                        $f('textarea', 'comments', 'Anything else?', ['max_length' => 1000]),
                    ]),
                ],
                'settings' => $confirm('email', 'Your RSVP', "Dear {full_name},\n\nThank you for letting us know. We have recorded your answer.\n\n{site}")
                    + ['one_per_email' => true, 'success' => ['mode' => 'message', 'message_html' => '<p>Thank you. Your RSVP has been recorded.</p>', 'redirect_path' => '']] + $notifyAnswers,
            ],

            // ------------------------------------------------------------------------------------------------ 4 contact
            'contact_enquiry' => [
                'title' => 'Contact or enquiry', 'icon' => 'mail', 'kind' => 'general',
                'description' => 'A simple contact form with a topic, a subject and a message.',
                'pages' => [
                    $page('', [
                        $f('text', 'full_name', 'Your name', ['required' => true, 'max_length' => 120, 'width' => 'half']),
                        $f('email', 'email', 'Email address', ['required' => true, 'width' => 'half']),
                        $f('phone', 'phone', 'Phone number', ['width' => 'half', 'help' => 'Optional.']),
                        $f('select', 'topic', 'What is your enquiry about?', ['required' => true, 'width' => 'half', 'options' => ['General enquiry', 'Programmes', 'Partnership', 'Volunteering', 'Media', 'Something else']]),
                        $f('text', 'subject', 'Subject', ['required' => true, 'max_length' => 150]),
                        $f('textarea', 'message', 'Message', ['required' => true, 'min_length' => 10, 'max_length' => 4000]),
                        self::consent('I agree that my details may be used to reply to my enquiry.'),
                    ]),
                ],
                'settings' => $confirm('email', 'We received your message', "Dear {full_name},\n\nThank you for contacting us. We have received your message and will reply as soon as we can.\n\n{site}")
                    + ['success' => ['mode' => 'message', 'message_html' => '<p>Thank you. Your message has been sent.</p>', 'redirect_path' => '']] + $notifyAnswers
                    + ['theme' => ['accent' => 'orange', 'layout' => 'single', 'progress' => false]],
            ],

            // ------------------------------------------------------------------------------------------------ 5 partnership
            'partnership_enquiry' => [
                'title' => 'Partnership enquiry', 'icon' => 'handshake', 'kind' => 'general',
                'description' => 'Organisations and donors who want to work with you: who they are, how they want to help, and a message.',
                'pages' => [
                    $page('', [
                        $f('text', 'organisation', 'Organisation name', ['required' => true, 'max_length' => 150]),
                        $f('select', 'organisation_type', 'Type of organisation', ['required' => true, 'other' => true, 'options' => ['Non-governmental organisation', 'Community-based organisation', 'Company', 'Government or public body', 'School or university', 'Faith-based organisation', 'Individual']]),
                        $f('text', 'website', 'Website', ['pattern' => 'url', 'placeholder' => 'https://', 'help' => 'Optional.']),
                        $f('text', 'contact_person', 'Contact person', ['required' => true, 'max_length' => 120, 'width' => 'half']),
                        $f('text', 'role', 'Their role', ['max_length' => 120, 'width' => 'half']),
                        $f('email', 'email', 'Email address', ['required' => true, 'width' => 'half']),
                        $f('phone', 'phone', 'Phone number', ['width' => 'half']),
                        $f('checkboxes', 'interest', 'How would you like to work with us?', ['required' => true, 'other' => true, 'options' => ['Funding or a grant', 'Working together on a programme', 'Donating goods or services', 'Skills or expert time', 'Research or learning']]),
                        $f('textarea', 'message', 'Tell us more', ['required' => true, 'min_length' => 20, 'max_length' => 4000]),
                        self::consent('I agree that this information may be stored and used to respond to our enquiry.'),
                    ]),
                ],
                'settings' => $confirm('email', 'Thank you for your interest in partnering', "Dear {contact_person},\n\nThank you for getting in touch. We have received your enquiry and will respond soon.\n\n{site}")
                    + ['success' => ['mode' => 'message', 'message_html' => '<p>Thank you. We have received your enquiry and will respond soon.</p>', 'redirect_path' => '']] + $notifyAnswers,
            ],

            // ------------------------------------------------------------------------------------------------ 6 feedback survey
            'feedback_survey' => [
                'title' => 'Feedback survey', 'icon' => 'message-circle', 'kind' => 'survey',
                'description' => 'An anonymous survey after an event or training: ratings, what worked, what to improve. Name and e-mail only if the person wants a reply.',
                'pages' => [
                    $page('', [
                        $f('rating', 'overall', 'Overall, how would you rate it?', ['required' => true, 'help' => '1 is poor, 5 is excellent.']),
                        $f('radio', 'recommend', 'Would you recommend it to a friend?', ['required' => true, 'options' => ['Definitely', 'Probably', 'Not sure', 'Probably not', 'No']]),
                        $f('checkboxes', 'liked', 'What worked well?', ['other' => true, 'options' => ['The content', 'The facilitators', 'The venue', 'The organisation', 'The materials']]),
                        $f('textarea', 'improve', 'What could we improve?', ['max_length' => 2000]),
                        $f('yes_no', 'reply', 'Would you like us to reply to you?', []),
                        $f('text', 'full_name', 'Your name', ['required' => true, 'max_length' => 120, 'width' => 'half', 'show' => ['reply', 'equals', 'yes']]),
                        $f('email', 'email', 'Email address', ['required' => true, 'width' => 'half', 'show' => ['reply', 'equals', 'yes']]),
                    ]),
                ],
                'settings' => ['success' => ['mode' => 'message', 'message_html' => '<p>Thank you for your feedback.</p>', 'redirect_path' => ''], 'notify' => ['enabled' => true, 'emails' => [], 'include_answers' => false],
                    'seo' => ['noindex' => false], 'theme' => ['accent' => 'teal', 'layout' => 'single', 'progress' => false]],
            ],

            // ------------------------------------------------------------------------------------------------ 7 scholarship
            'scholarship_application' => [
                'title' => 'Scholarship or programme application', 'icon' => 'graduation-cap', 'kind' => 'general',
                'description' => 'A three-step application: applicant details, education and a personal statement with supporting documents.',
                'pages' => [
                    $page('About the applicant', [
                        $f('text', 'full_name', 'Full name', ['required' => true, 'max_length' => 120, 'width' => 'half']),
                        $f('date', 'date_of_birth', 'Date of birth', ['required' => true, 'width' => 'half', 'max' => 'today']),
                        $f('select', 'gender', 'Gender', ['width' => 'half', 'options' => ['Female', 'Male', 'Prefer not to say']]),
                        $f('text', 'national_id', 'National ID number (NIN)', ['width' => 'half', 'pattern' => 'nin', 'help' => 'Optional. 14 characters, for example CM12345678ABCD.']),
                        $f('email', 'email', 'Email address', ['required' => true, 'width' => 'half']),
                        $f('phone', 'phone', 'Phone number', ['required' => true, 'width' => 'half']),
                        $f('text', 'district', 'District and village or town', ['required' => true, 'max_length' => 150]),
                        $f('yes_no', 'under_18', 'Are you under 18?', ['required' => true]),
                        $f('text', 'guardian_name', 'Parent or guardian name', ['required' => true, 'width' => 'half', 'show' => ['under_18', 'equals', 'yes']]),
                        $f('phone', 'guardian_phone', 'Parent or guardian phone', ['required' => true, 'width' => 'half', 'show' => ['under_18', 'equals', 'yes']]),
                    ]),
                    $page('Education', [
                        $f('select', 'education_level', 'Highest level completed', ['required' => true, 'other' => true, 'options' => ['Primary', 'Ordinary level (S4)', 'Advanced level (S6)', 'Certificate or diploma', 'Degree']]),
                        $f('text', 'school', 'School or institution', ['required' => true, 'max_length' => 150, 'width' => 'half']),
                        $f('text', 'year_completed', 'Year completed', ['pattern' => 'year', 'width' => 'half', 'help' => 'Optional.']),
                    ]),
                    $page('Your application', [
                        $f('text', 'programme', 'Scholarship or programme you are applying for', ['required' => true, 'max_length' => 150]),
                        $f('textarea', 'statement', 'Why should we select you?', ['required' => true, 'min_length' => 100, 'max_length' => 3000, 'help' => 'Tell us about yourself, your goals and what this opportunity would change for you.']),
                        $f('file', 'documents', 'Supporting documents', ['types' => ['pdf', 'jpg', 'png'], 'max_mb' => 5, 'max_files' => 3, 'help' => 'Optional. Results slips, a recommendation letter or a school report.']),
                        $f('text', 'referee', 'Referee name and contact', ['max_length' => 250, 'help' => 'Optional. Someone who knows you, such as a teacher or leader.']),
                        self::consent('I confirm that my answers are true, and I agree that my information may be stored and used to assess my application.'),
                    ]),
                ],
                'settings' => $confirm('email', 'We received your application', "Dear {full_name},\n\nThank you for applying. We have received your application and will contact you about the next steps.\n\n{site}")
                    + ['success' => ['mode' => 'message', 'message_html' => '<p>Thank you. Your application has been received.</p>', 'redirect_path' => '']] + $notifyAnswers
                    + ['seo' => ['noindex' => true]],
            ],

            // ------------------------------------------------------------------------------------------------ 8 beneficiary intake
            'beneficiary_intake' => [
                'title' => 'Beneficiary intake', 'icon' => 'users', 'kind' => 'intake',
                'description' => 'Register a person or household who needs support. Sensitive: keep it private, limit who can see the answers and set a retention period.',
                'pages' => [
                    $page('About you', [
                        self::note('privacy_note', 'The information you give here is private. Only the people who help with your case can see it.'),
                        $f('text', 'full_name', 'Full name', ['required' => true, 'max_length' => 120, 'width' => 'half']),
                        $f('date', 'date_of_birth', 'Date of birth', ['required' => true, 'width' => 'half', 'max' => 'today']),
                        $f('select', 'sex', 'Sex', ['width' => 'half', 'options' => ['Female', 'Male', 'Prefer not to say']]),
                        $f('phone', 'phone', 'Phone number', ['width' => 'half', 'help' => 'Optional. A number where we can reach you or someone close to you.']),
                        $f('text', 'location', 'District, sub-county and village', ['required' => true, 'max_length' => 200]),
                        $f('number', 'household_size', 'How many people live in your household?', ['min' => 1, 'max' => 40, 'width' => 'half']),
                        $f('yes_no', 'under_18', 'Are you under 18?', ['required' => true, 'width' => 'half']),
                        $f('text', 'guardian_name', 'Parent or guardian name', ['required' => true, 'width' => 'half', 'show' => ['under_18', 'equals', 'yes']]),
                        $f('phone', 'guardian_phone', 'Parent or guardian phone', ['width' => 'half', 'show' => ['under_18', 'equals', 'yes']]),
                    ]),
                    $page('Your needs', [
                        $f('checkboxes', 'support_needed', 'What support do you need?', ['required' => true, 'other' => true, 'options' => ['Education or school fees', 'Health care', 'Food', 'Shelter', 'Skills training or livelihood', 'Counselling or psychosocial support']]),
                        $f('textarea', 'situation', 'Tell us about your situation', ['required' => true, 'min_length' => 20, 'max_length' => 3000]),
                        $f('yes_no', 'urgent', 'Is this urgent?', []),
                        self::consent('I agree that the information I have given may be stored securely and used to assess whether we can support you.'),
                    ]),
                ],
                'settings' => ['success' => ['mode' => 'message', 'message_html' => '<p>Thank you. We have received your details and will contact you.</p>', 'redirect_path' => ''],
                    'notify' => ['enabled' => true, 'emails' => [], 'include_answers' => false], 'spam' => 'standard', 'seo' => ['noindex' => true]],
            ],

            // ------------------------------------------------------------------------------------------------ 9 training registration
            'training_registration' => [
                'title' => 'Training registration', 'icon' => 'laptop', 'kind' => 'general',
                'description' => 'Register participants for a training or course: contact details, experience level and what they need.',
                'pages' => [
                    $page('', [
                        $f('text', 'full_name', 'Full name', ['required' => true, 'max_length' => 120, 'width' => 'half']),
                        $f('email', 'email', 'Email address', ['required' => true, 'width' => 'half']),
                        $f('phone', 'phone', 'Phone number', ['required' => true, 'width' => 'half']),
                        $f('text', 'organisation', 'Organisation or school', ['max_length' => 150, 'width' => 'half', 'help' => 'Optional.']),
                        $f('text', 'training', 'Training or course you are registering for', ['required' => true, 'max_length' => 150]),
                        $f('radio', 'level', 'Your experience with the subject', ['required' => true, 'options' => ['Beginner', 'Some experience', 'Experienced']]),
                        $f('yes_no', 'own_device', 'Will you bring your own laptop or tablet?', []),
                        $f('textarea', 'needs', 'Anything we should know to support you?', ['max_length' => 1000, 'help' => 'Optional. For example accessibility needs.']),
                        self::consent('I agree that my details may be stored and used to organise the training.'),
                    ]),
                ],
                'settings' => $confirm('email', 'Your training registration', "Dear {full_name},\n\nThank you for registering. We have received your details and will send more information soon.\n\n{site}")
                    + ['one_per_email' => false, 'success' => ['mode' => 'message', 'message_html' => '<p>Thank you for registering. We will send you more information soon.</p>', 'redirect_path' => '']] + $notifyAnswers,
            ],
        ];
    }
}
