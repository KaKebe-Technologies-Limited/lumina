<?php
/**
 * Registry entry: PRIVACY POLICY. OWNER: B2 "web-content".
 *
 * *** DRAFT FRAMEWORK, NOT LEGAL ADVICE *** Written for the foundation to check with a legal adviser (Data Protection and Privacy Act, 2019 of Uganda) and
 * to keep consistent with its own policy LAF-DP-001. It describes the personal data this website really collects (see docs/DATA-INVENTORY.md).
 * Tokens {site_name} {email} {email_link} {phone} {phone_link} {address} {year} are replaced with the current Settings values.
 * Structure: "in brief" box -> numbered sections (each has a fixed anchor) -> a table of what we collect per activity (inserted after the "collect"
 * section; each row has an anchor "collect-<key>" that the "Why we ask for this" notes next to the forms link to) -> notes shown beside forms.
 * Retention periods in the table are stated as AIMS. The ones taken from LAF-DP-001 are: member records (membership + 3 years), unsuccessful
 * job candidates (12 months), financial records (7 years).
 */

use App\Services\Privacy\Notices;

$noteFields = [
    ['key' => 'heading', 'label' => 'Note heading', 'type' => 'text', 'default' => 'Why we ask for this'],
    ['key' => 'link_label', 'label' => 'Note link text', 'type' => 'text', 'default' => 'How we use your information'],
    ['key' => 'newsletter_tick', 'label' => 'Newsletter tick box wording', 'type' => 'text', 'default' => 'I agree to receive the newsletter by e-mail. I can unsubscribe at any time.'],
    ['key' => 'newsletter_link', 'label' => 'Newsletter tick box link text', 'type' => 'text', 'default' => 'Why we ask'],
];
foreach (Notices::ITEMS as $nk => $nv) {
    $noteFields[] = ['key' => $nk, 'label' => $nv[0], 'type' => 'textarea', 'rows' => 3, 'default' => $nv[1]];
}

return [
    'slug'  => 'privacy',
    'title' => 'Privacy Policy',
    'route' => '/privacy',
    'help'  => 'DRAFT FRAMEWORK, NOT LEGAL ADVICE: please have this policy checked by the foundation and a legal adviser before relying on it, and keep it consistent with the Data Protection and Privacy Policy (LAF-DP-001). Tokens {site_name}, {email_link}, {phone_link} and {address} are filled in from Settings. The people-facing summary is the "In brief" box; the table of what is collected, why and for how long is under "Table: what we collect". Update the "last updated" date whenever you change anything. See Admin > Data protection for the checklist.',
    'nav'   => ['label' => 'Privacy Policy', 'order' => 200, 'header' => false, 'footer' => true, 'parent' => null],
    'seo'   => [
        'title'       => 'Privacy Policy',
        'description' => 'What personal information Lumina Africa Foundation collects, why, how long we keep it, who sees it, and your rights.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Your privacy'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Privacy Policy', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'What personal information {site_name} collects on this website, why we ask for it, who sees it, how long we keep it, and what you can ask us to do about it.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '',
                    'help' => 'Decorative background behind the title. Leave empty for the plain navy banner (recommended for a legal page).'],
            ],
        ],
        [
            'key' => 'brief', 'label' => 'In brief (summary box at the top)', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'In brief'],
                ['key' => 'points', 'label' => 'Points (one per line)', 'type' => 'textarea', 'rows' => 8, 'default' => "We only ask for the information we need, and we tell you why when we ask.\nWe never sell your information, and we never give it to advertisers.\nOnly the people who need it to do their work can see it.\nPayment companies, e-mail and hosting services handle some of it for us. They may only use it for us.\nWe keep it only as long as we need it, then delete it or make it anonymous.\nYou can ask to see it, correct it, delete it or stop us using it. Use the form on the \"Your data rights\" page.\nIf you are under 18, we need a parent or guardian to agree before we keep your details."],
                ['key' => 'cta_label', 'label' => 'Button text', 'type' => 'text', 'default' => 'Ask about my data'],
            ],
        ],
        [
            'key' => 'notice', 'label' => 'The numbered sections', 'help' => 'Each row is a numbered section. The "Link name" becomes the address of the section (for example privacy#rights): keep it short, in lower case, with no spaces. Rows can be added, removed and reordered. The table of what we collect is placed after the section whose link name is "collect".',
            'fields' => [
                ['key' => 'updated_label', 'label' => 'Last-updated label', 'type' => 'text', 'default' => 'Last updated'],
                ['key' => 'updated', 'label' => 'Last-updated date', 'type' => 'text', 'default' => '23 November 2026', 'help' => 'For example "1 November 2026". Update it whenever the policy changes. Leave empty to hide the line.'],
                ['key' => 'contents_title', 'label' => 'Table of contents heading', 'type' => 'text', 'default' => 'On this page'],
                ['key' => 'items', 'label' => 'Sections', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a section', 'max_rows' => 25,
                    'fields' => [
                        ['key' => 'title', 'label' => 'Heading', 'type' => 'text'],
                        ['key' => 'anchor', 'label' => 'Link name (letters, no spaces)', 'type' => 'text'],
                        ['key' => 'body', 'label' => 'Text', 'type' => 'richtext'],
                    ],
                    'default' => [
                        [
                            'title' => 'Who we are and how to reach us',
                            'anchor' => 'who',
                            'body'  => '<p>{site_name} is a youth-led, community-based non-profit organisation based in {address}. We decide why and how the personal information described here is used. In law, that makes us the &ldquo;data controller&rdquo;.</p>'
                                . '<p>Our <strong>Data Protection Focal Person</strong> looks after data protection for us, keeps our records, deals with your requests and is your contact point. You can reach them through the Executive Director&rsquo;s office:</p>'
                                . '<p>{site_name}<br>Email: {email_link}<br>Phone: {phone_link}<br>{reg_line}</p>'
                                . '<p>This page follows our own Data Protection and Privacy Policy and the Data Protection and Privacy Act, 2019 of Uganda. If a word on this page is hard, write to us and we will explain it.</p>',
                        ],
                        [
                            'title' => 'What we collect and why',
                            'anchor' => 'collect',
                            'body'  => '<p>We collect only what you choose to send us through this website, plus a little technical information that keeps the website safe. The table below goes through each activity one by one: what we ask for, why, our legal reason, who receives it and how long we aim to keep it.</p>'
                                . '<p>When you send a form we also record your IP address (a number that identifies your internet connection) and your browser type. We use them to stop spam and misuse and to investigate problems. Where a form is used a lot, the site keeps a scrambled, keyed hash of your IP address for a short time to limit abuse; it cannot be turned back into your address.</p>',
                        ],
                        [
                            'title' => 'How we use your information, and what we never do',
                            'anchor' => 'use',
                            'body'  => '<p>We use your information to do the thing you asked for: reply to you, register you, take your gift, send your order, look at your application, run your course. We also use it to keep our accounts and records, to protect this website and to meet the law.</p>'
                                . '<ul><li>We <strong>never sell</strong> your information or rent it out.</li><li>We <strong>never give it to advertisers</strong>.</li><li>We do not use it to make automatic decisions about you.</li><li>We send you news, newsletters or fundraising messages <strong>only if you agreed</strong>, and every message tells you how to stop.</li><li>We do not use your information for a new purpose without asking you first.</li></ul>',
                        ],
                        [
                            'title' => 'Who we share it with',
                            'anchor' => 'sharing',
                            'body'  => '<p>Inside {site_name}, your information is seen only by the staff and volunteers who need it for their work, and only after they sign in with their own account.</p>'
                                . '<p>Some companies handle information for us. They are called &ldquo;processors&rdquo; and may use it only to do their job for us:</p>'
                                . '<ul><li><strong>Payment providers</strong>: Pesapal, Flutterwave, PayPal and mobile-money services take your payment on their own secure page. They receive your name, contact details and the amount. We never see or store your card or mobile-money PIN.</li>'
                                . '<li><strong>E-mail and messaging services</strong> that deliver our e-mails (and WhatsApp messages, where you chose them).</li>'
                                . '<li><strong>Our web host</strong> (Hostinger), where the website and its database live.</li>'
                                . '<li><strong>Google</strong>, if you choose to sign in to the Academy with Google, or if you choose a different language (see Cookies below).</li>'
                                . '<li><strong>Our AI service provider</strong>, if you ask Bono, the Academy assistant, a question.</li></ul>'
                                . '<p>We may also have to give information to the police, a court or a regulator when the law says we must. Some of these companies keep their computers outside Uganda. We use them only when they protect information properly or when you have agreed. We never publish personal information without your consent.</p>',
                        ],
                        [
                            'title' => 'How long we keep it, and deleting it',
                            'anchor' => 'retention',
                            'body'  => '<p>We keep personal information only as long as we need it for the reason we collected it, or as long as the law or a donor requires. The table above gives our aim for each activity. These are targets we work to, not promises that cannot change.</p>'
                                . '<p>When the time is up, we delete the information or remove the parts that identify you. A record that a legal or financial obligation applies (for example a donation receipt) may have to be kept longer. If you ask us to delete your information, we do so unless we must keep it by law, or for an open complaint or safeguarding case; we will tell you if that applies.</p>',
                        ],
                        [
                            'title' => 'How we keep it safe',
                            'anchor' => 'security',
                            'body'  => '<ul><li>Private files such as CVs, proofs of payment, photos and certificates are kept away from the public part of the website and can be opened only by signed-in staff who are allowed to.</li>'
                                . '<li>Staff accounts have their own passwords, can be protected with a code sent by e-mail, and lock themselves when left alone.</li>'
                                . '<li>Our forms are protected against spam and fake requests, and the connection to this website is encrypted (https).</li>'
                                . '<li>We check who can see what, and we keep a log of sign-ins and important changes.</li>'
                                . '<li>If something goes wrong with your information in a way that could harm you, we will tell you and the Personal Data Protection Office as the law requires.</li></ul>'
                                . '<p>No online system is completely safe. Please do not send us passwords or private details we have not asked for.</p>',
                        ],
                        [
                            'title' => 'Children and young people',
                            'anchor' => 'children',
                            'body'  => '<p>Many people we serve are young. We take extra care with information about anyone <strong>under 18</strong>.</p>'
                                . '<ul><li>If you are under 18, please ask a parent or guardian to agree before you apply, register or send us your details. For membership and the Academy we ask for a parent or guardian&rsquo;s details when the person is under 18.</li>'
                                . '<li>We ask only for what we need, we do not publish a child&rsquo;s full name, school or home area, and we show photos and stories of young people only with written consent from a parent or guardian and the young person&rsquo;s own agreement.</li>'
                                . '<li>Consent is never a condition for getting help, unless we truly need the information to give that help.</li>'
                                . '<li>A parent or guardian can ask to see, correct or delete a child&rsquo;s information at any time, using the form on the &ldquo;Your data rights&rdquo; page.</li></ul>'
                                . '<p>Learners in the LAF Academy are asked to confirm that they are old enough to register, or that a parent or guardian agrees. If we find that we hold information about a child without the right consent, we will delete it.</p>',
                        ],
                        [
                            'title' => 'Cookies and similar technologies',
                            'anchor' => 'cookies',
                            'body'  => '<p>A cookie is a tiny file a website saves in your browser. This website uses only the ones it needs to work. They do not follow you around the internet and we do not use them for advertising.</p>'
                                . '<ul><li><strong>Session cookie</strong>: remembers that you are signed in (Academy learners, members, staff) or keeps a form safe from fake requests. It is removed when you close the browser or after a while.</li>'
                                . '<li><strong>Sign-in and security cookies</strong>: let our staff and members sign in, remember a browser you chose to trust for the e-mail code, and lock an unattended admin screen.</li>'
                                . '<li><strong>Language cookie</strong> (&ldquo;googtrans&rdquo;): saved only if you pick a different language. It is set by you choosing, and lasts up to a year.</li>'
                                . '<li><strong>Learner-portal cookie</strong> (&ldquo;laf_portal&rdquo;): remembers that this browser has an Academy account so we can show a link to your portal. It lasts about 13 months.</li>'
                                . '<li><strong>Stored in your browser</strong> (not sent to us): small settings such as an Academy exam answer being typed, a reminder you have dismissed, or the offline copy of the site if you install it as an app.</li></ul>'
                                . '{analytics_note}'
                                . '<p><strong>Other companies&rsquo; services.</strong> Our pages load fonts from Google Fonts, and the About and Contact pages may show a Google map. When your browser loads them, Google can see your IP address. We also ask a free country-lookup service (ipwho.is) which country an IP address is in, to count visits by country; we do not store the IP address.</p>'
                                . '<p>You can block or delete cookies in your browser settings. The website will still work, but you may not be able to sign in or send some forms.</p>',
                        ],
                        [
                            'title' => 'Your rights',
                            'anchor' => 'rights',
                            'body'  => '<p>The Data Protection and Privacy Act, 2019 gives you rights over your personal information. You can ask us to:</p>'
                                . '<ul><li><strong>Tell you</strong> whether we hold information about you and give you a copy (<em>access</em>);</li>'
                                . '<li><strong>Correct</strong> anything that is wrong or incomplete;</li>'
                                . '<li><strong>Delete</strong> information we no longer need;</li>'
                                . '<li><strong>Stop</strong> using it for a purpose you object to, such as messages (<em>objection</em>);</li>'
                                . '<li><strong>Take back your consent</strong> at any time, for example the newsletter or a photo. Newsletters also have an &ldquo;unsubscribe&rdquo; link.</li></ul>'
                                . '<p><strong>How to ask:</strong> use the form on the <a href="{request_url}">Your data rights</a> page, or e-mail {email_link}. You get a reference number and an e-mail to confirm we have it. It costs nothing. We may need to check that you are the person the information is about (or their parent or guardian) before we show or change anything.</p>'
                                . '<p><strong>How fast:</strong> we aim to answer within {days} days. That is our target, not a legal promise; if we need longer we will tell you why.</p>'
                                . '<p><strong>If you are not happy:</strong> you may complain to the Personal Data Protection Office (PDPO) in Uganda, which supervises the Act. We hope you will talk to us first, but you do not have to.</p>',
                        ],
                        [
                            'title' => 'Changes to this policy',
                            'anchor' => 'changes',
                            'body'  => '<p>We may update this policy, for example when we add a service or the law changes. The newest version is always on this page and its &ldquo;last updated&rdquo; date is shown at the top. If we change something important about how we use your information, we will put a notice on the website, and where we can, e-mail the people it affects.</p>',
                        ],
                    ]],
            ],
        ],
        [
            'key' => 'collect', 'label' => 'Table: what we collect', 'help' => 'One row per activity: what is collected, why, our reason in law, who receives it and how long we aim to keep it. The "Link name" must keep its "collect-" start, because the notes next to the forms link to these rows. Retention figures are aims; the ones for members (membership + 3 years), unsuccessful job candidates (12 months) and financial records (7 years) come from the foundation policy LAF-DP-001. Confirm the rest in Admin > Data protection.',
            'fields' => [
                ['key' => 'cols', 'label' => 'Column headings (five, one per line: Data, Why, Legal basis, Who receives it, How long)', 'type' => 'textarea', 'rows' => 5, 'default' => "What we collect\nWhy we need it\nOur legal reason\nWho receives it\nHow long we aim to keep it"],
                ['key' => 'rows', 'label' => 'Rows', 'type' => 'repeater', 'item_label' => 'activity', 'add_label' => 'Add an activity', 'max_rows' => 30,
                    'fields' => [
                        ['key' => 'activity', 'label' => 'Activity', 'type' => 'text'],
                        ['key' => 'anchor', 'label' => 'Link name (start with collect-)', 'type' => 'text'],
                        ['key' => 'data', 'label' => 'What we collect', 'type' => 'textarea', 'rows' => 3],
                        ['key' => 'purpose', 'label' => 'Why we need it', 'type' => 'textarea', 'rows' => 3],
                        ['key' => 'basis', 'label' => 'Our legal reason', 'type' => 'text'],
                        ['key' => 'who', 'label' => 'Who receives it', 'type' => 'textarea', 'rows' => 2],
                        ['key' => 'keep', 'label' => 'How long we aim to keep it', 'type' => 'text'],
                    ],
                    'default' => [
                        ['activity' => 'Contact form and meeting requests', 'anchor' => 'collect-contact',
                            'data' => 'Name, e-mail, phone (if you give it), subject, your message; for a meeting also the date, time and who is coming.',
                            'purpose' => 'To reply to you and arrange the meeting.', 'basis' => 'Your consent; our legitimate interest in answering enquiries.',
                            'who' => 'Our staff who answer messages; our e-mail service.', 'keep' => 'About 2 years after the last message.'],
                        ['activity' => 'Newsletter', 'anchor' => 'collect-newsletter',
                            'data' => 'E-mail address (and name if you give it), the page you signed up on, the date you agreed.',
                            'purpose' => 'To send you our newsletter and news.', 'basis' => 'Your consent (you can take it back at any time).',
                            'who' => 'Our staff who send newsletters; our e-mail service.', 'keep' => 'Until you unsubscribe. We then keep only a note that you opted out, so we do not e-mail you again.'],
                        ['activity' => 'Donations and pledges', 'anchor' => 'collect-donate',
                            'data' => 'Name, e-mail, phone, amount, message, whether you want to stay anonymous, payment reference and status (never your card number or PIN).',
                            'purpose' => 'To take and record your gift, thank you, send a receipt and keep our accounts.', 'basis' => 'Your agreement to give; our legal duty to keep accounts.',
                            'who' => 'Our finance staff; the payment provider you choose (Pesapal, Flutterwave, PayPal or mobile money).', 'keep' => '7 years, as our financial records.'],
                        ['activity' => 'Shop orders and delivery', 'anchor' => 'collect-shop',
                            'data' => 'Name, e-mail, phone, delivery address, items, amount, payment method and reference, the time you agreed to our terms.',
                            'purpose' => 'To prepare and deliver your order, confirm payment and keep our accounts.', 'basis' => 'To carry out your order (contract); our legal duty to keep accounts.',
                            'who' => 'Our shop staff; the payment provider; a courier if you ask for delivery.', 'keep' => '7 years, as our financial records.'],
                        ['activity' => 'Event registration', 'anchor' => 'collect-events',
                            'data' => 'Name, e-mail, phone, organisation.',
                            'purpose' => 'To register you, send event information and count who came.', 'basis' => 'Your consent; our legitimate interest in running events.',
                            'who' => 'Our events team; our e-mail service.', 'keep' => 'About 2 years after the event.'],
                        ['activity' => 'Jobs, volunteering and internships', 'anchor' => 'collect-careers',
                            'data' => 'Name, e-mail, phone, your message or cover letter, LinkedIn address, your CV and, where asked, a photo; a tracking number so you can follow your application.',
                            'purpose' => 'To consider your application and tell you the result.', 'basis' => 'Your consent; steps you ask us to take before you join.',
                            'who' => 'The people who review applications.', 'keep' => 'Unsuccessful applicants: 12 months. Volunteers: while you volunteer and about 3 years after.'],
                        ['activity' => 'Ambassador applications', 'anchor' => 'collect-ambassador',
                            'data' => 'Name, contact details, motivation, portrait photo, social links; profile text if you become an ambassador.',
                            'purpose' => 'To consider your application and, if accepted, to show your profile.', 'basis' => 'Your consent (separate for your photo and public profile).',
                            'who' => 'The people who review applications; the public, only for an approved profile.', 'keep' => 'Unsuccessful applicants: 12 months. Ambassadors: while active and about 3 years after.'],
                        ['activity' => 'Getting involved (partners and supporters)', 'anchor' => 'collect-involvement',
                            'data' => 'Name, e-mail, phone, how you want to help, your organisation, your message.',
                            'purpose' => 'To reply and arrange how you can help.', 'basis' => 'Your consent.',
                            'who' => 'Our programme and partnership staff.', 'keep' => 'About 2 years after the last contact.'],
                        ['activity' => 'Membership', 'anchor' => 'collect-membership',
                            'data' => 'Name, gender, date of birth, nationality, contact details, address, occupation and education, interests, an emergency contact, payment reference and proof, a photo for your card, a parent or guardian if you are under 18, your consent choices.',
                            'purpose' => 'To review your application, run your membership, issue your card and receipts, and contact you.', 'basis' => 'Membership (contract); your consent for the photo and for news.',
                            'who' => 'Our membership staff. A public check page shows only your name, type, number, join date and whether you are valid.', 'keep' => 'While you are a member and 3 years after.'],
                        ['activity' => 'LAF Academy learners', 'anchor' => 'collect-academy',
                            'data' => 'Name, e-mail, phone or WhatsApp, country, date of birth or age group, a picture of you, registration number, course and quiz results, exam records (including the times you left the exam page), certificates, messages with us; Google account name and e-mail if you sign in with Google.',
                            'purpose' => 'To run your account, courses and exams, check that exams are fair, issue and verify certificates and stay in touch.', 'basis' => 'Your registration (contract); our legitimate interest in fair exams; your consent for WhatsApp messages.',
                            'who' => 'Academy staff and facilitators; Google (sign-in); our messaging and e-mail services; the public, only a certificate check page.', 'keep' => 'While your account is open and about 3 years after it ends. Certificate records stay so a certificate can still be verified.'],
                        ['activity' => 'Bono, the Academy assistant', 'anchor' => 'collect-bono',
                            'data' => 'The questions you type to Bono and the answers.',
                            'purpose' => 'To answer your question.', 'basis' => 'Your consent when you use it.',
                            'who' => 'Our AI service provider.', 'keep' => 'Questions are not kept with your name; please do not type private details.'],
                        ['activity' => 'Academy facilitator applications', 'anchor' => 'collect-facilitator',
                            'data' => 'Name, contact details, experience, CV, photo.',
                            'purpose' => 'To consider your application to teach.', 'basis' => 'Your consent.',
                            'who' => 'Academy staff who review applications.', 'keep' => 'Unsuccessful applicants: 12 months.'],
                        ['activity' => 'Fund a project', 'anchor' => 'collect-fund',
                            'data' => 'Name, contact details, the project and amount you pledge or propose, your message.',
                            'purpose' => 'To follow up your pledge or proposal.', 'basis' => 'Your consent.',
                            'who' => 'Our fundraising and finance staff.', 'keep' => 'Pledges that become gifts: 7 years. Others: about 2 years.'],
                        ['activity' => 'Other forms on this website', 'anchor' => 'collect-forms',
                            'data' => 'What you type into the form, and any file you attach.',
                            'purpose' => 'The purpose written on that form.', 'basis' => 'Your consent.',
                            'who' => 'The people who deal with that form.', 'keep' => 'As stated on the form; otherwise about 2 years.'],
                        ['activity' => 'Staff and administrator accounts, and security log', 'anchor' => 'collect-staff',
                            'data' => 'Name, e-mail, role, password (stored scrambled), sign-in times, IP address, browser, e-mail codes.',
                            'purpose' => 'To let staff sign in safely and to investigate misuse.', 'basis' => 'Contract with staff; our legitimate interest in security.',
                            'who' => 'Administrators.', 'keep' => 'Sign-in log: about 12 months. Accounts: while the person works with us.'],
                        ['activity' => 'Website visits and technical logs', 'anchor' => 'collect-visits',
                            'data' => 'Pages visited, country (worked out from the connection), browser type, errors. Our own visit counter does not store your IP address.',
                            'purpose' => 'To see which pages are useful, keep the site working and fix errors.', 'basis' => 'Our legitimate interest.',
                            'who' => 'Our administrators; our web host.', 'keep' => 'About 12 months.'],
                    ]],
            ],
        ],
        [
            'key' => 'analytics', 'label' => 'Cookies: Google Analytics sentence (shown only when a Google Analytics ID is set in Settings)',
            'help' => 'This text appears in the Cookies section ONLY while a Google Analytics measurement ID is saved in Settings. Google Analytics sets cookies, so if you use it, keep this text and think about asking visitors first. Leave the Settings ID empty to use no tracking at all.',
            'fields' => [
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p><strong>Visit statistics (Google Analytics)</strong>: this website currently uses Google Analytics to count visits. It sets cookies (named like &ldquo;_ga&rdquo;) to tell visitors apart, and your IP address is shortened before it is used. We use the figures only to improve the website. You can refuse by blocking these cookies in your browser or by using a browser add-on that blocks Google Analytics.</p>'],
            ],
        ],
        [
            'key' => 'who', 'label' => 'Contact details used in the policy',
            'fields' => [
                ['key' => 'reg_line', 'label' => 'Registration line (use {reg_number} for the number in Settings; leave empty to hide)', 'type' => 'text', 'default' => 'Registration number: {reg_number}'],
            ],
        ],
        [
            'key' => 'notes', 'label' => 'Notes next to forms ("Why we ask for this")', 'help' => 'The short notes shown beside each form that collects personal data. Each links to the matching row of the table above.',
            'fields' => $noteFields,
        ],
        [
            'key' => 'membership', 'label' => 'More about membership (section added after the numbered sections)', 'toggle' => true,
            'help' => 'This section is shown as the last numbered sections of the policy and stays here even if you rewrite the list above. Switch it off if membership registration is not used.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'More about membership', 'maxlength' => 140],
                ['key' => 'anchor', 'label' => 'Link name', 'type' => 'text', 'default' => 'membership'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>If you add a photo and a proof of payment to your membership application, we keep those pictures too. Your photo is printed on your membership card, which carries a QR code: scanning it opens a page that shows only your name, membership type and number, the date you joined and whether your membership is valid. It never shows your photo or contact details. Photos and proofs of payment are stored privately, only authorised staff can open them, and they are deleted if you ask us to erase your data.</p>'
                    . '<p>We use your membership information to review your application, to contact you, to manage your membership, to record membership fees and issue receipts, and, only if you ticked the box, to send you news and updates. We keep membership records while you are a member and for 3 years after. We ask for a national ID or passport number only if the foundation has switched that question on.</p>'
                    . '<p>To see, correct or delete your membership information, or to withdraw your consent, use the <a href="{request_url}">Your data rights</a> page or e-mail {email_link}.</p>'],
            ],
        ],
        [
            'key' => 'shop', 'label' => 'More about shop orders (section added after the numbered sections)', 'toggle' => true,
            'help' => 'Shown after the membership section. Switch it off if the shop is not used.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'More about shop orders', 'maxlength' => 140],
                ['key' => 'anchor', 'label' => 'Link name', 'type' => 'text', 'default' => 'shop'],
                ['key' => 'body', 'label' => 'Text', 'type' => 'richtext', 'default' => '<p>Card and mobile-money payments made online are processed by our payment partner on its own secure page. We never see or store your card number: we only receive confirmation that a payment was made, its amount and its reference. When you pay online, your name, e-mail address and phone number are passed to the payment partner so that it can process the payment.</p>'
                    . '<p>We use this information to prepare and deliver your order, to confirm and record your payment, to contact you about your order and to keep our accounts. Only authorised staff who are signed in can see orders. Your order page can be opened only with the private link we send you. We keep order records for 7 years because they are financial records.</p>'
                    . '<p>To see, correct or delete your order information, use the <a href="{request_url}">Your data rights</a> page or e-mail {email_link}.</p>'],
            ],
        ],
    ],
];
