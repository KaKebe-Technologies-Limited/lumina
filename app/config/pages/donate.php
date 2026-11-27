<?php
/**
 * Registry entry: DONATE PAGE  (OWNER: B1 "web-core"; redesigned by B-INNER-A).
 * Default wording = the owner's planned-programs copy (docs/content-source/planned-programs.md). The earlier live-site wording (school
 * supplies, food and clothing for children, "life-changing programs") was replaced because it contradicted the planned-programs
 * wording; the support cards now name the partnership routes of that copy. Review them in Admin > Pages > Donate.
 * Account details (the official bank account, mobile money, PayPal) and the online link are NOT copy: they come from Settings > Donations (the
 * bank account: App\Services\Payments\OfficialAccount, built in) and are simply hidden while empty / while the manual-details switch is off.
 * Pledges are stored in Admin > Donations with status "pledged" (a record of intent, never a payment); after one the donor sees /donate/thanks (section "thanks").
 * The quick-amount buttons of the pledge form are the ones of the donation panel (Site-wide content > Donation panel): suggestions only.
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 */
$hlHelp = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'donate',
    'title' => 'Donate',
    'route' => '/donate',
    'help'  => 'Ways to give (details from Settings > Donations), what donations support, the pledge form, the thank-you page after a pledge and the FAQ. The pledge form can be pre-filled with ?amount=50&currency=USD.',
    'nav'   => ['label' => 'Donate', 'order' => 95, 'header' => true, 'footer' => true, 'button' => true],
    'seo'   => [
        'title'       => 'Support Our Planned Programs',
        'description' => 'Support Lumina Africa Foundation. Together, we can help turn these plans into practical opportunities for the communities we serve.',
        'og_image'    => 'assets/img/site/photo-03.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Hero banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'default' => 'Support [[Us]]', 'maxlength' => 140, 'help' => $hlHelp],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'default' => 'Lumina Africa Foundation welcomes conversations with organisations and individuals interested in helping develop our proposed interventions.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Donate to Support Lumina Africa Foundation'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '', 'help' => 'Leave empty to use the online donation link from Settings > Donations (currently the GoFundMe campaign).'],
                ['key' => 'cta2_label', 'label' => 'Second button: text (optional)', 'type' => 'text', 'default' => 'Make a pledge', 'help' => 'Hidden while the pledge form section is switched off.'],
                ['key' => 'cta2_url', 'label' => 'Second button: link', 'type' => 'url', 'default' => '#pledge'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-03.jpg',
                    'alt' => 'A trainer points at a screen while young people wearing headphones work at computers',
                    'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'ways', 'label' => 'Ways to give', 'help' => 'The foundation\'s official bank account is shown while "Bank and manual mobile-money details" is switched on in Admin > Payment options (edit the account in Admin > Donations > "Your account details"); the other methods only when they have details in Settings > Donations.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Ways to [[give]]', 'help' => $hlHelp],
                ['key' => 'online_title', 'label' => 'Online donation: heading', 'type' => 'text', 'default' => 'Donate online'],
                ['key' => 'bank_title', 'label' => 'Bank transfer: heading', 'type' => 'text', 'default' => 'Bank transfer'],
                ['key' => 'bank_lead', 'label' => 'Bank transfer: sentence under the heading', 'type' => 'text', 'default' => 'Send your gift from any bank or banking app to this account, then share your proof of payment with us.'],
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
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Support [[Us]]', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 6, 'default' => 'We recognise that translating planned programs into meaningful outcomes requires collaboration, resources and shared commitment. Together, we can help turn these plans into practical opportunities for the communities we serve. The initiatives described on this site represent our planned program areas and proposed interventions, and they should not be interpreted as confirmation that every activity is currently funded, operational or already delivering results.'],
                ['key' => 'items', 'label' => 'Cards', 'type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add a card', 'max_rows' => 8,
                    'fields' => [
                        ['key' => 'icon', 'label' => 'Icon (used when the card has no photo)', 'type' => 'select', 'options' => icon_options(), 'default' => 'heart'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4],
                        ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image'],
                    ],
                    'default' => [
                        ['icon' => 'handshake', 'title' => 'Program Funding & Grants', 'text' => 'Lumina Africa Foundation welcomes conversations with organisations and individuals interested in helping develop our proposed interventions through program funding and grants.', 'image' => '', 'image_alt' => ''],
                        ['icon' => 'laptop', 'title' => 'Computers & Digital Equipment', 'text' => 'Lumina Africa Foundation welcomes conversations with organisations and individuals interested in helping develop our proposed interventions through computers, digital equipment and learning resources.', 'image' => '', 'image_alt' => ''],
                    ],
                ],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Donate to Support Lumina Africa Foundation'],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '', 'help' => 'Leave empty to use the online donation link from Settings > Donations.'],
            ],
        ],
        [
            'key' => 'pledge', 'label' => 'Pledge form', 'toggle' => true,
            'help' => 'A form that records an intention to give (Admin > Donations, status "pledged"). It does not take payment. Visitors arriving from the donation panel (?amount=50&currency=USD) find the amount already filled in.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Make a [[pledge]]', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Tell us how you would like to give and we will follow up with you.'],
                ['key' => 'note', 'label' => 'Notice (this is a record of intent, not a payment)', 'type' => 'textarea', 'rows' => 3, 'default' => 'This form records your intention to give. It is a pledge, not a payment: no money is taken here. After you send it, the next page shows how to complete your gift straight away.'],
                ['key' => 'chips_label', 'label' => 'Quick amounts: label (the buttons come from Site-wide content > Donation panel)', 'type' => 'text', 'default' => 'Quick amounts'],
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
                ['key' => 'success', 'label' => 'Message after a successful pledge', 'type' => 'textarea', 'rows' => 2, 'default' => 'Thank you. Your pledge has been recorded (no payment has been taken yet).'],
                ['key' => 'error_title', 'label' => 'Error summary: heading (shown above the form when something needs fixing)', 'type' => 'text', 'default' => 'Please check the following before sending'],
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
            'key' => 'thanks', 'label' => 'Thank-you page after a pledge',
            'help' => 'The page a donor sees right after sending the pledge form (/donate/thanks). It shows the foundation\'s bank account with the reference to quote, and any mobile-money or PayPal details set in Settings > Donations. Use {name} for the donor\'s first name and {amount} for the amount pledged.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Thank you, {name}!', 'maxlength' => 140],
                ['key' => 'lead', 'label' => 'Intro sentence under the heading (keep it short)', 'type' => 'text', 'default' => 'Your pledge of {amount} is recorded. No money has been taken yet.'],
                ['key' => 'pay_title', 'label' => 'Heading above the payment details', 'type' => 'text', 'default' => 'Complete your gift'],
                ['key' => 'bank_lead', 'label' => 'Bank transfer: sentence under the heading', 'type' => 'text', 'default' => 'Send your gift to this account and write the reference below on the transfer, so we can match it to your pledge.'],
                ['key' => 'foreign_note', 'label' => 'Note for a pledge in a currency other than UGX', 'type' => 'textarea', 'rows' => 2, 'default' => 'Giving from outside Uganda, or in another currency? Please write to us before you send, and we will confirm the best way to transfer your gift.'],
                ['key' => 'no_details', 'label' => 'Message when no payment details are shown', 'type' => 'textarea', 'rows' => 2, 'default' => 'We will contact you, usually within a few working days, with the details to complete your gift.'],
                ['key' => 'online_text', 'label' => 'Give-online hint (shown only when online giving is switched on)', 'type' => 'text', 'default' => 'You can also give right now by card or mobile money.'],
                ['key' => 'online_label', 'label' => 'Give-online button text', 'type' => 'text', 'default' => 'Give online now'],
                ['key' => 'next_title', 'label' => 'Heading: what happens next', 'type' => 'text', 'default' => 'What happens next'],
                ['key' => 'next_items', 'label' => 'What happens next: steps', 'type' => 'repeater', 'item_label' => 'text', 'add_label' => 'Add a step', 'max_rows' => 6,
                    'fields' => [
                        ['key' => 'text', 'label' => 'Step', 'type' => 'textarea', 'rows' => 2],
                    ],
                    'default' => [
                        ['text' => 'Send your gift whenever you are ready and quote your reference.'],
                        ['text' => 'Share your proof of payment with us by WhatsApp, phone or e-mail.'],
                        ['text' => 'We confirm your gift and send you a receipt.'],
                    ],
                ],
                ['key' => 'back_label', 'label' => 'Link back to the Donate page: text', 'type' => 'text', 'default' => 'Back to the Donate page'],
            ],
        ],
        [
            'key' => 'faq', 'label' => 'Frequently Asked Questions', 'toggle' => true,
            'help' => 'Questions come from Admin > FAQs (group "donate"); the list below is only used while that group has no active questions. The three live-site questions that promise mobile money / PayPal / Stripe payments, monthly giving and school-supply funding are left out until the foundation confirms them.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Frequently Asked [[Questions]]', 'help' => $hlHelp],
                ['key' => 'image', 'label' => 'Photo beside the questions (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-06.jpg', 'alt' => 'Young people seated at desks in a lecture hall'],
                ['key' => 'items', 'label' => 'Fallback questions (used only while Admin > FAQs has none for "donate")', 'type' => 'repeater', 'item_label' => 'question', 'add_label' => 'Add a question', 'max_rows' => 20,
                    'fields' => [
                        ['key' => 'question', 'label' => 'Question', 'type' => 'text', 'maxlength' => 300],
                        ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 4],
                    ],
                    'default' => [
                        ['question' => 'Is my donation tax-deductible?', 'answer' => 'Please check with your local tax authority regarding the deductibility of donations made to our organization.'],
                        ['question' => 'Who can I contact?', 'answer' => 'You can contact us through our website for any inquiries or assistance regarding donations.'],
                        ['question' => 'What is the impact of my donation?', 'answer' => 'Together, we can help turn these plans into practical opportunities for the communities we serve. The initiatives described on this site represent our planned program areas and proposed interventions, and they should not be interpreted as confirmation that every activity is currently funded, operational or already delivering results.'],
                    ],
                ],
            ],
        ],
    ],
];
