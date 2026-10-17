<?php
/**
 * ORGANISATION-WIDE settings schema. Drives (a) the admin Settings form (built by B3) and (b) the fallbacks of setting('key').
 * Only organisation-wide facts belong here. PAGE COPY lives in content blocks (app/config/pages/*.php), NOT here.
 *
 * Field definition: label, type (text|textarea|url|email|tel|image|boolean|number|select), default, help, rules (Validator
 * string applied when the admin saves), options (select), placeholder, group is the key of the parent array.
 * Image-type values are stored paths understood by img_url() ("assets/img/..." bundled, or "YYYY/MM/x.jpg" uploads).
 */
return [
    'groups' => [
        'organisation' => [
            'label' => 'Organisation',
            'icon'  => 'building',
            'help'  => 'Name, tagline and registration shown across the site and in search results.',
            'fields' => [
                'site_name'   => ['label' => 'Organisation name', 'type' => 'text', 'default' => 'Lumina Africa Foundation', 'rules' => 'required|max:120'],
                'tagline'     => ['label' => 'Tagline', 'type' => 'text', 'default' => 'Igniting Light, Inspiring Change.', 'rules' => 'nullable|max:160'],
                'reg_number'  => ['label' => 'Registration number', 'type' => 'text', 'default' => 'GSD/R 1545', 'rules' => 'nullable|max:60',
                    'help' => 'NOTE: the live website shows two different numbers (GSD/R 1545 on the home page, GSD/R 1234 on About). Confirm the correct one with the registrar and update it here.'],
                'founder_name' => ['label' => 'Founder name', 'type' => 'text', 'default' => 'Moses Komakech', 'rules' => 'nullable|max:120', 'help' => 'Used in search-engine (schema.org) data.'],
                'footer_credit' => ['label' => 'Footer credit line', 'type' => 'text', 'default' => '', 'rules' => 'nullable|max:200', 'help' => 'Optional line under the copyright (e.g. "Website by ..."). Leave empty to hide.'],
            ],
        ],
        'contact' => [
            'label' => 'Contact details',
            'icon'  => 'phone',
            'help'  => 'Shown in the footer, the contact page and structured data.',
            'fields' => [
                'email_primary' => ['label' => 'Primary email', 'type' => 'email', 'default' => 'luminaafricafoundation@gmail.com', 'rules' => 'required|email'],
                'email_info'    => ['label' => 'Info email', 'type' => 'email', 'default' => 'info@luminaafricafoundation.com', 'rules' => 'nullable|email'],
                'phone_1'       => ['label' => 'Phone 1', 'type' => 'tel', 'default' => '+256-779-712-990', 'rules' => 'nullable|phone'],
                'phone_2'       => ['label' => 'Phone 2', 'type' => 'tel', 'default' => '+256-790-635-544', 'rules' => 'nullable|phone'],
                'whatsapp'      => ['label' => 'WhatsApp link or number', 'type' => 'text', 'default' => 'https://wa.me/256779712990', 'rules' => 'nullable|max:200'],
                'address'       => ['label' => 'Address', 'type' => 'textarea', 'default' => 'Laroo-Pece Division', 'rules' => 'nullable|max:300'],
                'city_country'  => ['label' => 'City, country', 'type' => 'text', 'default' => 'Gulu City, Northern Uganda', 'rules' => 'nullable|max:120'],
                'map_embed_url' => ['label' => 'Google Maps embed URL', 'type' => 'url', 'default' => '', 'rules' => 'nullable|url', 'help' => 'The src URL of a Google Maps "Embed a map" iframe. Leave empty to hide the map.'],
                'notify_email'  => ['label' => 'Notification email', 'type' => 'email', 'default' => 'luminaafricafoundation@gmail.com', 'rules' => 'nullable|email', 'help' => 'Where form submissions (contact, applications, pledges...) are emailed.'],
            ],
        ],
        'social' => [
            'label' => 'Social media',
            'icon'  => 'globe',
            'help'  => 'Full profile URLs. Empty fields are not shown.',
            'fields' => [
                'social_facebook'  => ['label' => 'Facebook', 'type' => 'url', 'default' => 'https://www.facebook.com/profile.php?id=61578215262565', 'rules' => 'nullable|url'],
                'social_instagram' => ['label' => 'Instagram', 'type' => 'url', 'default' => 'https://www.instagram.com/luminaafricafdn/', 'rules' => 'nullable|url'],
                'social_linkedin'  => ['label' => 'LinkedIn', 'type' => 'url', 'default' => 'https://www.linkedin.com/company/lumina-africa-foundation', 'rules' => 'nullable|url'],
                'social_x'         => ['label' => 'X (Twitter)', 'type' => 'url', 'default' => 'https://x.com/luminaafricafdn', 'rules' => 'nullable|url'],
                'social_tiktok'    => ['label' => 'TikTok', 'type' => 'url', 'default' => 'https://www.tiktok.com/@lumina.africa.fou', 'rules' => 'nullable|url'],
                'social_youtube'   => ['label' => 'YouTube', 'type' => 'url', 'default' => '', 'rules' => 'nullable|url'],
            ],
        ],
        'donations' => [
            'label' => 'Donations',
            'icon'  => 'heart',
            'help'  => 'Used by the Donate page and the donate buttons.',
            'fields' => [
                'donate_url'          => ['label' => 'Online donation link', 'type' => 'url', 'default' => 'https://gofund.me/676b7a086', 'rules' => 'nullable|url', 'help' => 'Currently a GoFundMe campaign.'],
                'donate_intro'        => ['label' => 'Donation intro text', 'type' => 'textarea', 'default' => '', 'rules' => 'nullable|max:1000'],
                'donate_bank_details' => ['label' => 'Bank transfer details', 'type' => 'textarea', 'default' => '', 'rules' => 'nullable|max:2000', 'help' => 'Leave empty until the foundation confirms its official account details.'],
                'donate_mobile_money' => ['label' => 'Mobile money details', 'type' => 'textarea', 'default' => '', 'rules' => 'nullable|max:1000', 'help' => 'Leave empty until confirmed.'],
                'donate_paypal_email' => ['label' => 'PayPal email', 'type' => 'email', 'default' => '', 'rules' => 'nullable|email'],
            ],
        ],
        'membership' => [
            'label' => 'Membership',
            'icon'  => 'user-plus',
            'help'  => 'Controls the Membership page and its registration form. The FEES themselves are set per membership type in Admin > Membership types. The membership terms text is edited in Admin > Pages > Membership terms.',
            'fields' => [
                'membership_open'                 => ['label' => 'Accept new membership applications', 'type' => 'boolean', 'default' => '0',
                    'help' => 'OFF until you switch it on: while it is off the form is replaced by a "membership is closed" notice and no personal data is collected. Tick it once you have set the fees, replaced the placeholder membership terms and reviewed the privacy notice (wording of the notice: Admin > Pages > Membership).'],
                'membership_currency'             => ['label' => 'Default currency', 'type' => 'text', 'default' => 'UGX', 'rules' => 'required|regex:/^[A-Za-z]{3}$/', 'placeholder' => 'UGX',
                    'help' => 'Three-letter code (UGX, USD, KES...). New membership types start with this currency; each type can use another one.'],
                'membership_number_prefix'        => ['label' => 'Membership number prefix', 'type' => 'text', 'default' => 'LAF', 'rules' => 'required|regex:/^[A-Za-z0-9]{2,10}$/', 'placeholder' => 'LAF',
                    'help' => 'Numbers look like LAF-2026-0001 (members), LAF-APP-2026-48213 (applications) and LAF-RCT-2026-0001 (receipts). Numbers already issued never change.'],
                'membership_payment_instructions' => ['label' => 'How to pay the membership fee', 'type' => 'textarea', 'default' => '', 'rules' => 'nullable|max:2000',
                    'help' => 'Shown to applicants after they register (bank account, mobile-money number, office address...). While empty, the bank and mobile-money details from the Donations tab are used.'],
                'membership_collect_id'           => ['label' => 'Ask for a national ID or passport number', 'type' => 'boolean', 'default' => '0',
                    'help' => 'Off by default (collect less personal data). Turn it on only if you really need it.'],
                'membership_contact_email'        => ['label' => 'Membership contact e-mail', 'type' => 'email', 'default' => '', 'rules' => 'nullable|email',
                    'help' => 'Shown to applicants and used as the reply-to address of membership e-mails. While empty, the primary e-mail from the Contact tab is used.'],
            ],
        ],
        'newsletter' => [
            'label' => 'Newsletter',
            'icon'  => 'newspaper',
            'help'  => 'How newsletter e-mails are sent. Write and publish the issues in Admin > Newsletter. The SMTP login (host, user, password) is NOT here: it lives in the .env file on the server (MAIL_DRIVER=smtp, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION) so it is never stored in the database.',
            'fields' => [
                'newsletter_from_name'     => ['label' => 'Sender name', 'type' => 'text', 'default' => '', 'rules' => 'nullable|max:120', 'help' => 'The name subscribers see. While empty, the organisation name is used.'],
                'newsletter_from_email'    => ['label' => 'Sender e-mail address', 'type' => 'email', 'default' => '', 'rules' => 'nullable|email', 'help' => 'Must be an address your mail server may send from (usually the one of the SMTP account). While empty, MAIL_FROM from the .env file is used.'],
                'newsletter_reply_to'      => ['label' => 'Reply-to address', 'type' => 'email', 'default' => '', 'rules' => 'nullable|email', 'help' => 'Where replies go. While empty, the primary e-mail from the Contact tab is used.'],
                'newsletter_footer_text'   => ['label' => 'Extra footer text', 'type' => 'textarea', 'default' => '', 'rules' => 'nullable|max:600', 'help' => 'Optional line at the bottom of every newsletter e-mail, above the organisation address and the unsubscribe link.'],
                'newsletter_batch_size'    => ['label' => 'E-mails per batch', 'type' => 'number', 'default' => '30', 'rules' => 'required|integer|between:1,200', 'help' => 'How many subscribers are e-mailed per step when you send an issue from the admin screen. Lower it if your host limits how fast you can send (many shared hosts allow only a few hundred e-mails per hour).'],
                'newsletter_default_subject_prefix' => ['label' => 'Subject prefix', 'type' => 'text', 'default' => '', 'rules' => 'nullable|max:60', 'help' => 'Put in front of the issue title in the e-mail subject, for example "Lumina Newsletter:". Leave empty for no prefix.'],
            ],
        ],
        'branding' => [
            'label' => 'Branding & SEO',
            'icon'  => 'image',
            'help'  => 'Logo, icons and default search-engine information.',
            'fields' => [
                'logo_path'        => ['label' => 'Logo', 'type' => 'image', 'default' => 'assets/img/brand/logo-512.png', 'rules' => 'nullable|max:255'],
                'favicon_path'     => ['label' => 'Favicon', 'type' => 'image', 'default' => 'assets/img/brand/favicon-32.png', 'rules' => 'nullable|max:255'],
                'og_image'         => ['label' => 'Default social sharing image', 'type' => 'image', 'default' => 'assets/img/site/photo-hero-portrait.jpg', 'rules' => 'nullable|max:255', 'help' => 'Shown when a page is shared on social media (1200x630 works best).'],
                'meta_description' => ['label' => 'Default meta description', 'type' => 'textarea', 'default' => 'Lumina Africa Foundation is a community-based non-profit in Gulu City, Northern Uganda, developing community-centred programs for underserved communities.', 'rules' => 'nullable|max:300'],
                'analytics_id'     => ['label' => 'Google Analytics measurement ID', 'type' => 'text', 'default' => '', 'rules' => 'nullable|regex:/^G-[A-Z0-9]{4,20}$/', 'placeholder' => 'G-XXXXXXXXXX', 'help' => 'Optional. Leave empty for no tracking.'],
            ],
        ],
    ],
];
