<?php
/**
 * Registry entry: OPPORTUNITIES (scholarships, fellowships, internships, grants ... for young people). A section of the Involvement menu.
 * The opportunities themselves are managed in the admin under Content > Opportunities (added by the team, uploaded, or read from other websites);
 * this page holds the wording around them. Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 */
$hlHelp = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'opportunities',
    'title' => 'Opportunities',
    'route' => '/opportunities',
    'help'  => 'The Opportunities page: wording above the list and on each opportunity. The opportunities themselves are added under Content > Opportunities.',
    'nav'   => ['label' => 'Opportunities', 'order' => 53, 'header' => true, 'footer' => true, 'parent' => 'involvement'],
    'seo'   => [
        'title'       => 'Opportunities for Young People - Scholarships, Fellowships and Grants',
        'description' => 'Scholarships, fellowships, internships, grants, training and competitions for young people in Uganda and across Africa, gathered by Lumina Africa Foundation.',
        'og_image'    => 'assets/img/site/photo-04.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Get involved'],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Opportunities for [[Young People]]', 'maxlength' => 140, 'help' => $hlHelp],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 4, 'default' => 'Scholarships, fellowships, internships, grants, training and competitions open to young people in Uganda and across Africa. We gather them in one place so you can find what fits you and apply before the deadline.'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg',
                    'alt' => 'Young people learning together in a training room', 'help' => 'A wide photo darkened behind the heading. Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'list', 'label' => 'The list',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title (optional)', 'type' => 'text', 'default' => ''],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Find your [[next step]]', 'help' => $hlHelp],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'default' => 'Search by keyword, choose a type, and open an opportunity to read who can apply and how. Always check the official page before you apply.'],
                ['key' => 'search_label', 'label' => 'Search box: label', 'type' => 'text', 'default' => 'Search'],
                ['key' => 'search_placeholder', 'label' => 'Search box: hint', 'type' => 'text', 'default' => 'For example: scholarship, engineering, Kenya'],
                ['key' => 'type_label', 'label' => 'Type menu: label', 'type' => 'text', 'default' => 'Type'],
                ['key' => 'all_types', 'label' => 'Type menu: first choice', 'type' => 'text', 'default' => 'All types'],
                ['key' => 'apply_filter', 'label' => 'Search button', 'type' => 'text', 'default' => 'Search'],
                ['key' => 'reset', 'label' => 'Clear button', 'type' => 'text', 'default' => 'Clear'],
                ['key' => 'show_closed', 'label' => 'Checkbox: include closed ones', 'type' => 'text', 'default' => 'Include closed'],
                ['key' => 'closes', 'label' => 'Card: closing date label', 'type' => 'text', 'default' => 'Closes'],
                ['key' => 'rolling', 'label' => 'Card: when there is no closing date', 'type' => 'text', 'default' => 'Open until filled'],
                ['key' => 'closed', 'label' => 'Card: closed label', 'type' => 'text', 'default' => 'Closed'],
                ['key' => 'card_button', 'label' => 'Card: button', 'type' => 'text', 'default' => 'View details'],
                ['key' => 'empty_title', 'label' => 'When nothing matches: headline', 'type' => 'text', 'default' => 'No opportunities found'],
                ['key' => 'empty_text', 'label' => 'When nothing matches: text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Try a different word or type, or check again soon. New opportunities are added every day.'],
                ['key' => 'credit_note', 'label' => 'Note below the list', 'type' => 'textarea', 'rows' => 2, 'default' => 'Some opportunities are gathered from other websites and link back to the original. Lumina Africa Foundation does not run them and cannot guarantee any application. Never pay to apply.'],
            ],
        ],
        [
            'key' => 'detail', 'label' => 'On each opportunity', 'help' => 'Headings and labels shown on a single opportunity.',
            'fields' => [
                ['key' => 'back', 'label' => 'Link back to the list', 'type' => 'text', 'default' => 'All opportunities'],
                ['key' => 'about_title', 'label' => 'Heading: about', 'type' => 'text', 'default' => 'About this opportunity'],
                ['key' => 'eligibility_title', 'label' => 'Heading: who can apply', 'type' => 'text', 'default' => 'Who can apply'],
                ['key' => 'benefits_title', 'label' => 'Heading: what you get', 'type' => 'text', 'default' => 'What you get'],
                ['key' => 'how_title', 'label' => 'Heading: how to apply', 'type' => 'text', 'default' => 'How to apply'],
                ['key' => 'type_label', 'label' => 'Side box: type', 'type' => 'text', 'default' => 'Type'],
                ['key' => 'by_label', 'label' => 'Side box: offered by', 'type' => 'text', 'default' => 'Offered by'],
                ['key' => 'where_label', 'label' => 'Side box: where', 'type' => 'text', 'default' => 'Where'],
                ['key' => 'deadline_label', 'label' => 'Side box: closing date', 'type' => 'text', 'default' => 'Closing date'],
                ['key' => 'apply_button', 'label' => 'Button: apply', 'type' => 'text', 'default' => 'Apply now'],
                ['key' => 'source_button', 'label' => 'Button: read the original (items from other websites)', 'type' => 'text', 'default' => 'Read the full details at the source'],
                ['key' => 'file_button', 'label' => 'Button: download the PDF', 'type' => 'text', 'default' => 'Download the PDF'],
                ['key' => 'source_panel', 'label' => 'Text above the button when only a summary is available', 'type' => 'text', 'default' => 'This is a summary. The full requirements, documents needed and the application form are on the official page.'],
                ['key' => 'closed_notice', 'label' => 'Notice when the closing date has passed', 'type' => 'text', 'default' => 'The closing date has passed. This opportunity may no longer accept applications.'],
                ['key' => 'source_credit', 'label' => 'Credit line for items from other websites', 'type' => 'text', 'default' => 'Gathered from {source}. Lumina Africa Foundation is not the organiser. Check the official page for the full and latest details.', 'help' => 'Use {source} where the website name goes.'],
                ['key' => 'warning', 'label' => 'Safety reminder', 'type' => 'text', 'default' => 'Real scholarships and fellowships never ask you to pay to apply. Be careful with anyone who does.'],
                ['key' => 'more_title', 'label' => 'Heading: more opportunities', 'type' => 'text', 'default' => 'More opportunities'],
                ['key' => 'share_label', 'label' => 'Share: heading', 'type' => 'text', 'default' => 'Share this opportunity'],
                ['key' => 'share_on', 'label' => 'Share: "Share on" (before the network name)', 'type' => 'text', 'default' => 'Share on'],
                ['key' => 'copy_label', 'label' => 'Share: copy link button', 'type' => 'text', 'default' => 'Copy link'],
                ['key' => 'copied_label', 'label' => 'Share: after copying', 'type' => 'text', 'default' => 'Link copied'],
            ],
        ],
        [
            'key' => 'cta', 'label' => 'Closing band', 'toggle' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Know an [[opportunity]] for young people?', 'help' => $hlHelp],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Organisations, schools and individuals can tell us about a scholarship, fellowship, internship or training so more young people can find it.'],
                ['key' => 'button_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Tell us about it'],
                ['key' => 'button_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
    ],
];
