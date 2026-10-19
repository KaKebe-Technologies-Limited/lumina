<?php
/**
 * Registry entry: NEWSLETTER (public page /newsletter, archive, issue pages and the wording of the e-mail edition).
 * OWNER: architect (frozen for the agents; they only READ these keys with content()). Contract: docs/NEWSLETTER.md.
 *
 * The ISSUES themselves (title, articles, pictures) are written in Admin > Newsletter; this file holds only the words AROUND them:
 * hero, labels, subscribe box, empty states, archive labels, share and navigation labels, and the fixed words of the e-mail edition.
 * Nothing here is invented: where the foundation must decide something, the default is neutral.
 * Heading fields accept [[double brackets]] around the words that get the hand-drawn underline.
 *
 * Placeholders: {number} in issue.label, {date} in issue.label, {site} in email.reason.
 */
return (static function (): array {
    $hl = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
    $text = static fn(string $key, string $label, string $default, array $extra = []): array => ['key' => $key, 'label' => $label, 'type' => 'text', 'default' => $default] + $extra;
    $area = static fn(string $key, string $label, string $default, int $rows = 3, array $extra = []): array => ['key' => $key, 'label' => $label, 'type' => 'textarea', 'rows' => $rows, 'default' => $default] + $extra;

    return [
        'slug'  => 'newsletter',
        'title' => 'Newsletter',
        'route' => '/newsletter',
        'help'  => 'The wording around the newsletter: page banner, labels, subscribe box, archive, share buttons and the fixed words of the e-mail edition. Write and publish the issues themselves in Admin > Newsletter.',
        // Blog > Newsletter in the header menu (a dropdown child of Blog) and in the footer. The owner can move or hide it in Admin > Menus.
        'nav'   => ['label' => 'Newsletter', 'order' => 73, 'header' => true, 'footer' => true, 'parent' => 'blog', 'requires' => ['table' => 'newsletters']],   // hidden while the newsletter tables are not imported yet
        'seo'   => [
            'title'       => 'Newsletter',
            'description' => 'Read the latest newsletter from Lumina Africa Foundation and browse past issues.',
            'og_image'    => 'assets/img/site/photo-04.jpg',
        ],
        'sections' => [
            [
                'key' => 'hero', 'label' => 'Page banner (shown only while no issue is published yet)',
                'help' => 'Once an issue is published, the page shows that issue (its own title and summary) instead of this banner. These three fields only matter until then.',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title (only while no issue is published)', 'Newsletter'),
                    $text('title', 'Heading (only while no issue is published)', 'Our [[Newsletter]]', ['maxlength' => 140, 'help' => $hl . ' When an issue is published its own title is the page heading.']),
                    $area('lead', 'Intro paragraph (only while no issue is published)', 'Our latest news and updates.', 3),
                ],
            ],
            [
                'key' => 'issue', 'label' => 'Issue labels',
                'fields' => [
                    $text('label', 'Issue line', 'Issue No. {number} · {date}', ['help' => 'Shown under the title. {number} is the issue number and {date} the issue date. When an issue has no number only the date is shown.']),
                    $text('featured_label', 'Label above the featured article', 'Featured'),
                    $text('read_more', 'Link text for an article link without its own text', 'Read more'),
                    $text('latest_badge', 'Badge on the newest issue', 'Latest issue'),
                    $text('contents_label', 'Accessible name of the issue content', 'Newsletter content'),
                ],
            ],
            [
                'key' => 'subscribe', 'label' => 'Subscribe box', 'toggle' => true,
                'fields' => [
                    $text('eyebrow', 'Small heading above the title', 'Newsletter'),
                    $text('title', 'Heading', 'Get our news and updates', ['maxlength' => 140, 'help' => $hl]),
                    $area('text', 'Text', 'Subscribe to receive our latest news and updates by e-mail.', 3),
                    $text('placeholder', 'E-mail field placeholder', 'Email address'),
                    $text('button', 'Button', 'Subscribe'),
                    $area('privacy_note', 'Note under the form', 'We use your e-mail address only to send you our news and updates. Every e-mail has a link to unsubscribe in one click.', 3),
                ],
            ],
            [
                'key' => 'pager', 'label' => 'Previous / next issue',
                'fields' => [
                    $text('prev', 'Previous issue', 'Previous issue'),
                    $text('next', 'Next issue', 'Next issue'),
                    $text('archive_link', 'Link to the archive', 'All issues'),
                    $text('latest_link', 'Link back to the newest issue', 'Back to the latest issue'),
                ],
            ],
            [
                'key' => 'share', 'label' => 'Share buttons', 'toggle' => true,
                'fields' => [
                    $text('title', 'Heading', 'Share this issue'),
                    $text('on', 'Name of each share link for screen readers', 'Share on {network}', ['help' => '{network} is filled in with Facebook, X, LinkedIn or WhatsApp.']),
                    $text('copy', 'Copy-link button', 'Copy link'),
                    $text('copied', 'Message after copying', 'Link copied'),
                ],
            ],
            [
                'key' => 'pdf', 'label' => 'PDF issues',
                'help' => 'Words used when an issue is an uploaded PDF (or has a PDF attached): the badge on the archive card, the buttons, the viewer. Write the PDF issues themselves in Admin > Newsletter > Upload a PDF.',
                'fields' => [
                    $text('badge', 'Badge on a PDF issue (archive and home card)', 'PDF', ['maxlength' => 20]),
                    $text('download_label', 'Button: download the PDF', 'Download PDF', ['maxlength' => 40]),
                    $text('open_label', 'Link: read the PDF in a new tab', 'Read in a new tab', ['maxlength' => 60]),
                    $text('size_label', 'File size line', 'PDF · {size}', ['maxlength' => 40, 'help' => '{size} is filled in, for example "2.4 MB".']),
                    $text('downloads_label', 'Download counter line', '{count} downloads', ['maxlength' => 40, 'help' => '{count} is filled in. Shown only while the next switch is on.']),
                    $text('downloads_label_one', 'Download counter line when it is exactly one', '{count} download', ['maxlength' => 40, 'help' => 'Used instead of the line above when the PDF was downloaded once. Empty it to use the line above as written. The line above is never changed automatically.']),
                    ['key' => 'show_downloads', 'label' => 'Show how many times the PDF was downloaded', 'type' => 'boolean', 'default' => false],
                    $text('viewer_title', 'Name of the embedded viewer for screen readers', 'PDF of the newsletter: {title}', ['help' => '{title} is the issue title.']),
                    $area('fallback', 'Text when the browser cannot show a PDF inside the page', 'Your browser cannot show this PDF inside the page. Use the buttons to download it or open it in a new tab.', 3),
                    $text('summary_label', 'Heading above the summary of a PDF issue (optional)', '', ['maxlength' => 60]),
                    $text('attached_label', 'Link on an online issue that also has a PDF', 'Download this issue as PDF', ['maxlength' => 60]),
                    $text('card_fallback', 'Text on the cover-less card of a PDF issue', 'Newsletter', ['maxlength' => 40, 'help' => 'Shown on the branded picture used when an issue has no cover image.']),
                ],
            ],
            [
                'key' => 'empty', 'label' => 'When no issue is published yet',
                'fields' => [
                    $text('title', 'Heading', 'No issue has been published yet'),
                    $area('when', 'When the first issue will appear', 'The first issue will appear here as soon as it is published.', 2, ['help' => 'Edit this to say when readers can expect it, for example "Our first issue comes out in March."']),
                    $area('text', 'Text', 'Subscribe below to receive our latest news and updates.', 3),
                    $text('archive_title', 'Archive: heading when there are no issues', 'No issues to show yet'),
                    $area('archive_text', 'Archive: text when there are no issues', 'Past issues will appear here once they are published.', 3),
                ],
            ],
            [
                'key' => 'archive', 'label' => 'Archive page',
                'fields' => [
                    $text('eyebrow', 'Small heading above the title', 'Newsletter'),
                    $text('title', 'Heading', 'Newsletter [[archive]]', ['maxlength' => 140, 'help' => $hl]),
                    $area('lead', 'Intro paragraph', 'Browse past issues of our newsletter.', 3),
                    $text('read_label', 'Link text on each issue card', 'Read this issue'),
                    $text('page_label', 'Page counter ("Page 2 of 5")', 'Page {page} of {pages}', ['help' => '{page} and {pages} are filled in automatically.']),
                    $text('seo_title', 'Search-engine title', 'Newsletter archive'),
                    $area('seo_description', 'Search-engine description', 'Browse past issues of the Lumina Africa Foundation newsletter.', 2),
                ],
            ],
            [
                'key' => 'email', 'label' => 'E-mail edition: fixed words',
                'help' => 'The words around the issue in the e-mail itself. The organisation address and the extra footer text come from Site settings > Newsletter.',
                'fields' => [
                    $text('view_in_browser', 'Link: view in browser', 'View in browser', ['required' => true]),
                    $text('read_online', 'Button: read the issue online', 'Read this issue online', ['required' => true]),
                    $area('reason', 'Why they get the e-mail', 'You are receiving this e-mail because you subscribed to updates from {site}.', 3, ['required' => true]),
                    $text('unsubscribe', 'Link: unsubscribe', 'Unsubscribe', ['required' => true]),
                    $text('download_pdf', 'Button: download the PDF (PDF issues)', 'Download PDF'),
                    $text('test_notice', 'Notice shown only in test e-mails', 'This is a test e-mail. It was not sent to the subscribers.'),
                ],
            ],
        ],
    ];
})();
