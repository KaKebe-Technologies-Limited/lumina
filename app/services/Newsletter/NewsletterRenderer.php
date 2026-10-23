<?php
declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Core\Sanitizer;
use App\Core\View;

/**
 * The ONE renderer of a newsletter issue. The public page, the admin live preview / preview page and the e-mail all call renderIssue(), so they
 * read the same normalised content through the same block dispatch and the same helpers and cannot drift apart.
 *
 *   NewsletterRenderer::renderIssue($row, 'public')    -> HTML of the issue (head, featured article, blocks) for the public page
 *   NewsletterRenderer::renderIssue($row, 'preview')   -> the same HTML + data-nl-block="<id>" hooks (click a block to focus it in the editor)
 *   NewsletterRenderer::renderIssue($row, 'email', ['subject'=>..,'preheader'=>..,'unsubscribe_url'=>..,'browser_url'=>..,'is_test'=>bool])
 *                                                       -> a COMPLETE e-mail HTML document (table layout, inline CSS, absolute URLs)
 *   NewsletterRenderer::emailText($row, $opts)         -> the text/plain alternative of the same e-mail
 *   NewsletterRenderer::previewDocument($row, $opts)   -> a standalone HTML page (site CSS + the preview HTML) for the editor's iframe / srcdoc
 *
 * $row is a `newsletters` row or an equivalent array (an unsaved editor state works): `content` may be the JSON text or the decoded array.
 * Views (one file per block type and context family; see docs/NEWSLETTER.md section 8):
 *   web/newsletter/_issue.php, web/newsletter/_featured.php, web/newsletter/blocks/{article,image,quote,button,divider}.php    (WEB agent)
 *   newsletter/email/layout.php, newsletter/email/blocks/{featured,article,image,quote,button,divider}.php                       (MAILING agent)
 * Every block view receives: $block (normalised), $issue (row), $context, and the helpers below stay the single source for URLs and labels.
 */
final class NewsletterRenderer
{
    public const CONTEXTS = ['public', 'preview', 'email'];

    /** @param array<string,mixed> $opts email only: subject, preheader, unsubscribe_url, browser_url, is_test */
    public static function renderIssue(array $newsletter, string $context = 'public', array $opts = []): string
    {
        $context = in_array($context, self::CONTEXTS, true) ? $context : 'public';
        $issue = self::prepare($newsletter);
        if ($context === 'email') {
            return self::emailHtml($issue, $opts);
        }
        $blocks = '';
        foreach ($issue['content_data']['blocks'] as $block) {
            $blocks .= self::renderBlock($block, $issue, $context);
        }
        return View::partial('web/newsletter/_issue', [
            'issue'      => $issue,
            'content'    => $issue['content_data'],
            'context'    => $context,
            'featured'   => self::renderFeatured($issue, $context),
            'blocksHtml' => $blocks,
            'label'      => self::issueLabel($issue),
        ]);
    }

    /** Render one block of a given type with the view of its context family. Unknown types render nothing. */
    public static function renderBlock(array $block, array $issue, string $context): string
    {
        $type = (string) ($block['type'] ?? '');
        if (!in_array($type, NewsletterContent::BLOCK_TYPES, true)) {
            return '';
        }
        $view = ($context === 'email' ? 'newsletter/email/blocks/' : 'web/newsletter/blocks/') . $type;
        return View::exists($view) ? View::partial($view, ['block' => $block, 'issue' => $issue, 'context' => $context]) : '';
    }

    public static function renderFeatured(array $issue, string $context): string
    {
        $f = $issue['content_data']['featured'];
        if ($f['heading'] === '' && $f['body_html'] === '' && $f['image']['path'] === '') {
            return '';
        }
        $view = $context === 'email' ? 'newsletter/email/blocks/featured' : 'web/newsletter/_featured';
        return View::exists($view) ? View::partial($view, ['featured' => $f, 'issue' => $issue, 'context' => $context]) : '';
    }

    // ------------------------------------------------------------------------------------------------ e-mail

    /**
     * The complete e-mail HTML. $opts: subject (default NewsletterService::defaultSubject), preheader (default the issue's e-mail preheader,
     * else its summary), unsubscribe_url (per recipient; '' for the preview), browser_url (default the public issue URL), is_test.
     */
    public static function emailHtml(array $issue, array $opts = []): string
    {
        $issue = self::prepare($issue);
        $blocks = '';
        foreach ($issue['content_data']['blocks'] as $block) {
            $blocks .= self::renderBlock($block, $issue, 'email');
        }
        return View::render('newsletter/email/layout', self::emailVars($issue, $opts) + [
            'featuredHtml' => self::renderFeatured($issue, 'email'),
            'blocksHtml'   => $blocks,
        ], null);
    }

    /** The text/plain part of the e-mail: same order as the HTML, links written out, footer with the unsubscribe link. */
    public static function emailText(array $issue, array $opts = []): string
    {
        $issue = self::prepare($issue);
        $v = self::emailVars($issue, $opts);
        $c = $issue['content_data'];
        $out = [];
        if ($v['is_test']) {
            $out[] = '[' . $v['labels']['test_notice'] . ']';
        }
        $out[] = (string) $issue['title'];
        $out[] = self::issueLabel($issue);
        if (trim((string) ($issue['summary'] ?? '')) !== '') {
            $out[] = trim((string) $issue['summary']);
        }
        $out[] = $v['labels']['view_in_browser'] . ': ' . $v['browser_url'];
        $out[] = str_repeat('-', 40);

        $f = $c['featured'];
        $parts = array_filter([
            $f['heading'], NewsletterContent::htmlToText($f['body_html'], true),
            $f['link']['label'] !== '' && $f['link']['url'] !== '' ? $f['link']['label'] . ': ' . self::href($f['link']['url'], 'email') : '',
        ], static fn(string $s): bool => $s !== '');
        if ($parts !== []) {
            $out[] = implode("\n\n", $parts);
        }
        foreach ($c['blocks'] as $b) {
            $piece = match ($b['type']) {
                'article' => implode("\n\n", array_filter([
                    $b['heading'], NewsletterContent::htmlToText($b['body_html'], true),
                    $b['link']['label'] !== '' && $b['link']['url'] !== '' ? $b['link']['label'] . ': ' . self::href($b['link']['url'], 'email') : '',
                ], static fn(string $s): bool => $s !== '')),
                'image'   => $b['image']['caption'] !== '' ? '[' . $b['image']['caption'] . ']' : ($b['image']['alt'] !== '' ? '[' . $b['image']['alt'] . ']' : ''),
                'quote'   => '"' . NewsletterContent::htmlToText($b['body_html'], false) . '"' . ($b['cite'] !== '' ? "\n- " . $b['cite'] : ''),
                'button'  => $b['button']['label'] !== '' && $b['button']['url'] !== '' ? $b['button']['label'] . ': ' . self::href($b['button']['url'], 'email') : '',
                default   => '----------',
            };
            if (trim($piece) !== '') {
                $out[] = $piece;
            }
        }
        if ($v['pdf'] !== null) {
            // a PDF issue is not attached to the e-mail (size, deliverability): two links instead
            $out[] = $v['pdf']['label'] . ': ' . $v['pdf']['download_url'] . ($v['pdf']['size_text'] !== '' ? ' (' . $v['pdf']['size_text'] . ')' : '');
            $out[] = $v['labels']['read_online'] . ': ' . $v['browser_url'];
        }
        $out[] = str_repeat('-', 40);
        $follow = function_exists('social_links') ? social_links('newsletter') : [];      // the admin's profile links (Admin > Social media), same order as the HTML e-mail
        if ($follow !== []) {
            $out[] = ($v['labels']['follow'] !== '' ? $v['labels']['follow'] . "\n" : '') . implode("\n", array_map(static fn(array $s): string => $s['label'] . ': ' . $s['url'], $follow));
        }
        if ($v['footer_text'] !== '') {
            $out[] = $v['footer_text'];
        }
        $out[] = $v['reason'];
        if ($v['org_address'] !== '') {
            $out[] = $v['org_name'] . ', ' . $v['org_address'];
        }
        if ($v['unsubscribe_url'] !== '') {
            $out[] = $v['labels']['unsubscribe'] . ': ' . $v['unsubscribe_url'];
        }
        return trim(implode("\n\n", $out)) . "\n";
    }

    /** Subject for an issue: its own e-mail subject, else prefix + title. */
    public static function subject(array $issue): string
    {
        $s = trim((string) ($issue['email_subject'] ?? ''));
        return $s !== '' ? $s : NewsletterService::defaultSubject($issue);
    }

    /** Preheader (the grey preview line in inboxes): the issue's own, else its summary, else a clip of the text. */
    public static function preheader(array $issue): string
    {
        $p = trim((string) ($issue['email_preheader'] ?? ''));
        if ($p === '') {
            $p = trim((string) ($issue['summary'] ?? ''));
        }
        if ($p === '') {
            $p = NewsletterContent::plainText(self::prepare($issue)['content_data'], 120);
        }
        return NewsletterContent::clip($p, 150);
    }

    /** @return array<string,mixed> variables of newsletter/email/layout.php */
    private static function emailVars(array $issue, array $opts): array
    {
        $site = (string) setting('site_name', 'Lumina Africa Foundation');
        // a blank fixed word (the owner cleared the field) falls back to the built-in default: the unsubscribe link must never become invisible
        $label = static function (string $k): string {
            $v = trim((string) content('newsletter', 'email.' . $k));
            return $v !== '' ? $v : (string) (\App\Core\PageRegistry::field('newsletter', 'email.' . $k)['default'] ?? '');
        };
        $address = trim(implode(', ', array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))], static fn(string $s): bool => $s !== '')));
        $logo = logo_url(64);
        // PDF issue (kind = pdf): the message carries the summary and two buttons (Read online / Download PDF) instead of the file itself. Why no
        // attachment: 5-25 MB attachments push the message over most providers' size limits and into spam, multiply the sending time by the
        // number of subscribers, and could not be swapped after sending; the link always serves the current file and is counted.
        $pdf = null;
        if ((string) ($issue['kind'] ?? 'online') === 'pdf' && NewsletterPdf::has($issue)) {
            $info = NewsletterPdf::info($issue, true);
            $pdf = [
                'download_url' => $info['download_url'],
                'view_url'     => $info['view_url'],
                'size_human'   => $info['size_human'],
                'size_text'    => trim(strtr((string) content('newsletter', 'pdf.size_label'), ['{size}' => $info['size_human']])),
                'label'        => $label('download_pdf'),
            ];
        }
        return [
            'issue'           => $issue,
            'content'         => $issue['content_data'],
            'subject'         => (string) ($opts['subject'] ?? self::subject($issue)),
            'preheader'       => (string) ($opts['preheader'] ?? self::preheader($issue)),
            'browser_url'     => (string) ($opts['browser_url'] ?? NewsletterService::url($issue, true)),
            'unsubscribe_url' => (string) ($opts['unsubscribe_url'] ?? ''),
            'is_test'         => (bool) ($opts['is_test'] ?? false),
            'label'           => self::issueLabel($issue),
            'org_name'        => $site,
            'org_address'     => str_replace(["\r", "\n"], ' ', $address),
            'footer_text'     => trim((string) setting('newsletter_footer_text', '')),
            'reason'          => str_replace('{site}', $site, $label('reason')),
            'logo_url'        => self::absoluteUrl($logo),
            'pdf'             => $pdf,
            'labels'          => [
                'follow'          => trim((string) content('newsletter', 'email.follow', 'Follow us:')),       // may be empty on purpose: the links are then shown without a heading
                'view_in_browser' => $label('view_in_browser'),
                'read_online'     => $label('read_online'),
                'unsubscribe'     => $label('unsubscribe'),
                'test_notice'     => $label('test_notice'),
            ],
        ];
    }

    // ------------------------------------------------------------------------------------------------ preview document

    /**
     * A standalone HTML page for the editor's live preview (iframe srcdoc or a new tab): fonts + the site stylesheet + the issue rendered in
     * the 'preview' context (data-nl-block hooks). noindex. $opts: banner (string shown on top, e.g. "Preview - not published"), width_class.
     */
    public static function previewDocument(array $newsletter, array $opts = []): string
    {
        $issue = self::prepare($newsletter);
        $banner = trim((string) ($opts['banner'] ?? ''));
        $site = (string) setting('site_name', '');
        $body = self::renderIssue($issue, 'preview');
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<meta name="robots" content="noindex,nofollow"><title>' . e(trim((string) $issue['title'] . ' - ' . $site, ' -')) . '</title>'
            . '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
            . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&amp;family=Fraunces:wght@500;600;700&amp;family=Poppins:wght@500;600;700&amp;display=swap">'
            . '<link rel="stylesheet" href="' . e(asset('css/main.min.css')) . '"></head>'
            . '<body class="page-newsletter page-newsletter--preview">'
            . ($banner !== '' ? '<p class="nl-preview-banner" role="note">' . e($banner) . '</p>' : '')
            . '<main class="site-main"><div class="container container--narrow nl-page">' . $body . '</div></main></body></html>';
    }

    // ------------------------------------------------------------------------------------------------ shared helpers

    /**
     * Ensure the issue array has `content_data` (normalised, tolerant) and string keys the views rely on. Idempotent.
     * @return array<string,mixed>
     */
    public static function prepare(array $issue): array
    {
        if (!isset($issue['content_data']) || !is_array($issue['content_data'])) {
            $raw = $issue['content'] ?? null;
            $issue['content_data'] = NewsletterContent::decode(is_string($raw) || is_array($raw) ? $raw : null);
        }
        $issue['title'] = (string) ($issue['title'] ?? '');
        $issue['issue_date'] = (string) ($issue['issue_date'] ?? date('Y-m-d'));
        return $issue;
    }

    /** "Issue No. 3 · 17 October 2026" (editable: registry newsletter issue.label); just the date when the issue has no number. */
    public static function issueLabel(array $issue): string
    {
        $date = format_date($issue['issue_date'] ?? '');
        $number = (int) ($issue['issue_number'] ?? 0);
        if ($number < 1) {
            return $date;
        }
        $tpl = (string) content('newsletter', 'issue.label');
        return trim(strtr($tpl !== '' ? $tpl : 'Issue No. {number} · {date}', ['{number}' => (string) $number, '{date}' => $date]));
    }

    /** href for a stored link: site paths get the base path (absolute for e-mail), http(s)/mailto/tel pass through. '' for empty/unsafe. */
    public static function href(string $stored, string $context): string
    {
        $stored = NewsletterContent::cleanUrl($stored);
        if ($stored === '') {
            return '';
        }
        return $context === 'email' ? self::absoluteUrl(link_url($stored)) : link_url($stored);
    }

    /** True when the link leaves the site (open in a new tab with rel noopener). */
    public static function isExternal(string $stored): bool
    {
        return is_external($stored);
    }

    /** Public URL of a stored image path (absolute for e-mail). */
    public static function imageUrl(array $image, string $context): string
    {
        $path = (string) ($image['path'] ?? '');
        if ($path === '') {
            return '';
        }
        $u = img_url($path);
        return $context === 'email' ? self::absoluteUrl($u) : $u;
    }

    /** [width, height] of a stored image (cached per request) or null; lets <img> carry its size so the page does not jump while loading. */
    public static function imageSize(string $path): ?array
    {
        static $cache = [];
        if ($path === '' || !NewsletterContent::validImagePath($path)) {
            return null;
        }
        if (!array_key_exists($path, $cache)) {
            $file = str_starts_with($path, 'assets/') ? BASE_PATH . '/' . $path : UPLOAD_PATH . '/' . $path;
            $info = is_file($file) ? @getimagesize($file) : false;
            $cache[$path] = $info !== false && ($info[0] ?? 0) > 0 ? [(int) $info[0], (int) $info[1]] : null;
        }
        return $cache[$path];
    }

    /** Sanitised rich text ready for output. Public/preview: rich() (base path expanded). E-mail: the same with absolute URLs and inline styles. */
    public static function richHtml(string $html, string $context): string
    {
        if ($context === 'email') {
            return self::emailStyles(self::absolute(Sanitizer::html($html)));
        }
        return Sanitizer::rich($html);
    }

    /** Root-relative href/src ("/lumina/about") -> absolute URLs, for e-mail. */
    public static function absolute(string $html): string
    {
        $origin = origin();
        $html = Sanitizer::expand($html);
        return (string) preg_replace('~\b(href|src)="/(?!/)~i', '$1="' . $origin . '/', $html);
    }

    /** "/lumina/assets/x.png" -> "https://host/lumina/assets/x.png"; absolute URLs unchanged. */
    public static function absoluteUrl(string $u): string
    {
        if ($u === '' || preg_match('~^(https?:|mailto:|tel:)~i', $u)) {
            return $u;
        }
        return origin() . (str_starts_with($u, '/') ? '' : '/') . $u;
    }

    /** Inline styles for the few tags rich text can contain, because mail clients ignore (or strip) style sheets. */
    public static function emailStyles(string $html): string
    {
        $styles = [
            'p'          => 'margin:0 0 16px 0;',
            'h2'         => "margin:24px 0 12px 0;font-family:Georgia,'Times New Roman',serif;font-size:19px;line-height:1.3;color:#2C3950;",
            'h3'         => "margin:20px 0 10px 0;font-family:Georgia,'Times New Roman',serif;font-size:17px;line-height:1.3;color:#2C3950;",
            'h4'         => 'margin:18px 0 8px 0;font-size:16px;line-height:1.4;color:#2C3950;',
            'ul'         => 'margin:0 0 16px 0;padding:0 0 0 22px;',
            'ol'         => 'margin:0 0 16px 0;padding:0 0 0 22px;',
            'li'         => 'margin:0 0 6px 0;',
            'blockquote' => 'margin:0 0 16px 0;padding:0 0 0 16px;border-left:3px solid #D6A151;color:#5A6470;',
            'a'          => 'color:#85560A;text-decoration:underline;',
            'img'        => 'max-width:100%;height:auto;border:0;display:block;',
            'hr'         => 'border:0;border-top:1px solid #E4E1DA;margin:20px 0;',
            'table'      => 'border-collapse:collapse;margin:0 0 16px 0;max-width:100%;',
            'td'         => 'border:1px solid #E4E1DA;padding:6px 8px;',
            'th'         => 'border:1px solid #E4E1DA;padding:6px 8px;text-align:left;',
        ];
        // a pasted URL or one very long word must wrap inside the 600px card instead of stretching it
        $wrap = 'word-break:break-word;overflow-wrap:anywhere;word-wrap:break-word;';
        $wrapTags = ['p', 'h2', 'h3', 'h4', 'li', 'blockquote', 'a', 'td', 'th'];
        return (string) preg_replace_callback('~<(p|h2|h3|h4|ul|ol|li|blockquote|a|img|hr|table|td|th)\b([^>]*)>~i', static function (array $m) use ($styles, $wrap, $wrapTags): string {
            $tag = strtolower($m[1]);
            $attrs = rtrim($m[2], '/ ');
            $self = str_ends_with(rtrim($m[2]), '/') ? ' /' : '';
            return '<' . $tag . $attrs . ' style="' . $styles[$tag] . (in_array($tag, $wrapTags, true) ? $wrap : '') . '"' . $self . '>';
        }, $html);
    }

    /**
     * An editor state (details + content from the browser, NOT saved) as a displayable issue row: lenient content normalisation, plain-text
     * details, no uniqueness or file checks. Used by the preview endpoint so a preview needs no database write.
     *
     * @param array|null $row the saved row to start from (null = a new issue)
     */
    public static function previewRow(?array $row, array $input): array
    {
        $base = $row ?? [
            'id' => 0, 'slug' => 'preview', 'issue_number' => null, 'title' => '', 'summary' => null, 'issue_date' => date('Y-m-d'),
            'cover_image' => null, 'cover_alt' => null, 'status' => 'draft', 'content' => NewsletterContent::encode(NewsletterContent::emptyContent()),
            'seo_title' => null, 'seo_description' => null, 'published_at' => null, 'publish_at' => null, 'updated_at' => date('Y-m-d H:i:s'),
        ];
        foreach (['title', 'summary', 'cover_alt', 'seo_title', 'seo_description'] as $k) {
            if (array_key_exists($k, $input) && is_scalar($input[$k])) {
                $base[$k] = Sanitizer::text((string) $input[$k]);
            }
        }
        if (array_key_exists('issue_number', $input)) {
            $n = is_scalar($input['issue_number']) ? trim((string) $input['issue_number']) : '';
            $base['issue_number'] = ctype_digit($n) && (int) $n > 0 ? (int) $n : null;
        }
        if (array_key_exists('issue_date', $input) && is_scalar($input['issue_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $input['issue_date']) === 1) {
            $base['issue_date'] = (string) $input['issue_date'];
        }
        if (array_key_exists('cover_image', $input) && is_scalar($input['cover_image'])) {
            $p = trim((string) $input['cover_image']);
            $base['cover_image'] = NewsletterContent::validImagePath($p) ? $p : null;
        }
        if (array_key_exists('content', $input)) {
            $base['content'] = NewsletterContent::encode(NewsletterContent::normalise($input['content'], ['strict' => false, 'check_files' => false])['content']);
        }
        unset($base['content_data']);
        return self::prepare($base);
    }
}
