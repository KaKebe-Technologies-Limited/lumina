<?php
/**
 * Registry entry: REPORTS & DOWNLOADS (/reports) - OWNED BY B7 "org-public".
 * The files themselves live in the `documents` table (admin: Documents; the file is stored privately and streamed through
 * /reports/download/{id}, which counts the download). Only documents flagged "public" and "active" are listed.
 * This file holds the copy around them: headings, filter labels, category names, empty and "no match" messages, SEO.
 * No report title or figure is invented: the list is empty until the owner uploads real documents.
 */
return [
    'slug'  => 'reports',
    'title' => 'Reports & Downloads',
    'route' => '/reports',
    'help'  => 'Annual reports, financial statements, policies and other downloads. Upload the files under Documents in the admin menu; the headings, filter labels and category names are edited here.',
    'nav'   => [
        'label' => 'Reports & Downloads', 'order' => 23, 'header' => true, 'footer' => true, 'parent' => 'about',
    ],
    'seo'   => [
        'title'       => 'Reports & Downloads',
        'description' => 'Download reports, policies and other publications from Lumina Africa Foundation, a registered community-based non-profit organisation in Gulu City, Northern Uganda.',
        'og_image'    => '',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner', 'help' => 'The title area at the top of the page.',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Integrity & Transparency', 'maxlength' => 80],
                ['key' => 'title', 'label' => 'Page heading', 'type' => 'text', 'default' => 'Reports & Downloads', 'maxlength' => 120],
                ['key' => 'lead', 'label' => 'Intro paragraph', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'Browse and download our reports, policies and other publications.'],
                ['key' => 'image', 'label' => 'Banner background photo (optional)', 'type' => 'image', 'default' => '', 'alt' => '',
                    'help' => 'Decorative background behind the title. Leave empty for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'filters', 'label' => 'Filter form', 'help' => 'Labels of the category / year filter above the list. The filter is shown when there is more than one category or year to choose from.',
            'fields' => [
                ['key' => 'legend', 'label' => 'Filter group name (read by screen readers)', 'type' => 'text', 'default' => 'Filter documents', 'maxlength' => 80],
                ['key' => 'category_label', 'label' => 'Category selector label', 'type' => 'text', 'default' => 'Category', 'maxlength' => 40],
                ['key' => 'year_label', 'label' => 'Year selector label', 'type' => 'text', 'default' => 'Year', 'maxlength' => 40],
                ['key' => 'all_categories', 'label' => 'Option: every category', 'type' => 'text', 'default' => 'All categories', 'maxlength' => 40],
                ['key' => 'all_years', 'label' => 'Option: every year', 'type' => 'text', 'default' => 'All years', 'maxlength' => 40],
                ['key' => 'apply', 'label' => 'Button: apply the filter', 'type' => 'text', 'default' => 'Apply filters', 'maxlength' => 40],
                ['key' => 'reset', 'label' => 'Link: clear the filter', 'type' => 'text', 'default' => 'Reset', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'categories', 'label' => 'Category names', 'help' => 'How each document category is called on the website (the category itself is chosen in Documents).',
            'fields' => [
                ['key' => 'annual_report', 'label' => 'Annual reports', 'type' => 'text', 'default' => 'Annual reports', 'maxlength' => 60],
                ['key' => 'financial', 'label' => 'Financial statements', 'type' => 'text', 'default' => 'Financial statements', 'maxlength' => 60],
                ['key' => 'policy', 'label' => 'Policies', 'type' => 'text', 'default' => 'Policies', 'maxlength' => 60],
                ['key' => 'brief', 'label' => 'Briefs', 'type' => 'text', 'default' => 'Briefs', 'maxlength' => 60],
                ['key' => 'newsletter', 'label' => 'Newsletters', 'type' => 'text', 'default' => 'Newsletters', 'maxlength' => 60],
                ['key' => 'other', 'label' => 'Other', 'type' => 'text', 'default' => 'Other', 'maxlength' => 60],
            ],
        ],
        [
            'key' => 'list', 'label' => 'Document list', 'help' => 'Heading, counters and button text used in the list of documents.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Available documents', 'maxlength' => 120],
                ['key' => 'count_one', 'label' => 'Result count: one document', 'type' => 'text', 'default' => '1 document', 'maxlength' => 60],
                ['key' => 'count_many', 'label' => 'Result count: several documents', 'type' => 'text', 'default' => '{n} documents', 'maxlength' => 60, 'help' => '{n} is replaced by the number.'],
                ['key' => 'download', 'label' => 'Button: download a document', 'type' => 'text', 'default' => 'Download', 'maxlength' => 40],
                ['key' => 'published', 'label' => 'Label before the publication date', 'type' => 'text', 'default' => 'Published', 'maxlength' => 40],
            ],
        ],
        [
            'key' => 'no_results', 'label' => 'Message when the filter finds nothing', 'help' => 'Shown when documents exist but none matches the chosen category / year.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'No documents match your selection', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Try a different category or year.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Show all documents', 'maxlength' => 60],
            ],
        ],
        [
            'key' => 'contact', 'label' => 'Need something else? band', 'toggle' => true,
            'help' => 'A short band below the list pointing visitors to the contact page.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Looking for something else?', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Get in touch and we will help you find the document you need.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Contact Us', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
        [
            'key' => 'empty', 'label' => 'Message shown while there are no documents', 'help' => 'Visitors see this until the first public document is uploaded.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Content coming soon', 'maxlength' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'default' => 'Our reports and downloads will be published here soon. If you need a document in the meantime, please get in touch.'],
                ['key' => 'cta_label', 'label' => 'Button: text', 'type' => 'text', 'default' => 'Contact Us', 'maxlength' => 60],
                ['key' => 'cta_url', 'label' => 'Button: link', 'type' => 'url', 'default' => '/contact'],
            ],
        ],
    ],
];
