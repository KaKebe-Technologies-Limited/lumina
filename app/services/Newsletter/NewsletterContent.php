<?php
declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Core\Sanitizer;

/**
 * The structured content of a newsletter issue (JSON, version 1) - schema, validation, normalisation and text helpers.
 * Contract and examples: docs/NEWSLETTER.md section 2.
 *
 *   {"version":1,
 *    "featured":{"heading":"","body_html":"","image":{"path":"","alt":"","caption":""},"link":{"label":"","url":""}},
 *    "blocks":[{"id":"b3f9a1c2","type":"article","heading":"","body_html":"","image":{...},"link":{...}},
 *              {"id":"..","type":"image","image":{...}},
 *              {"id":"..","type":"quote","body_html":"","cite":""},
 *              {"id":"..","type":"button","button":{"label":"","url":"","style":"primary|outline"}},
 *              {"id":"..","type":"divider"}]}
 *
 * Every save goes through normalise(): strict schema check, HTML through Sanitizer::html(), plain text fields stripped of tags, URLs limited to
 * http / https / mailto / tel and site paths ("/about"), image paths limited to uploads/ ("YYYY/MM/file.ext") and the bundled assets/img/ photos
 * AND required to exist, size caps, unknown keys dropped, block ids regenerated when missing or duplicated. Reading (decode()) is tolerant:
 * it never throws and always returns a complete, safe structure, so a damaged row can never break a public page.
 *
 * Strict mode (manual save, publish): the first problem of a field is an ERROR keyed by its path ("blocks.2.button.url"); the content is not saved.
 * Lenient mode (autosave, reading): the offending value is cleared / the block skipped and reported as a WARNING, so a half-typed URL never
 * blocks an autosave.
 */
final class NewsletterContent
{
    public const VERSION = 1;
    public const BLOCK_TYPES = ['article', 'image', 'quote', 'button', 'divider'];
    public const BUTTON_STYLES = ['primary', 'outline'];

    public const MAX_BLOCKS = 60;
    public const MAX_HEADING = 200;
    public const MAX_BODY = 20000;       // characters of sanitised HTML per body
    public const MAX_ALT = 300;
    public const MAX_CAPTION = 300;
    public const MAX_LABEL = 80;
    public const MAX_CITE = 160;
    public const MAX_URL = 2000;
    public const MAX_BYTES = 600000;     // encoded JSON of the whole issue

    /** @return array<string,mixed> an empty issue: no featured text, no blocks */
    public static function emptyContent(): array
    {
        return [
            'version'  => self::VERSION,
            'featured' => ['heading' => '', 'body_html' => '', 'image' => self::emptyImage(), 'link' => self::emptyLink()],
            'blocks'   => [],
        ];
    }

    /** @return array{path:string,alt:string,caption:string} */
    public static function emptyImage(): array
    {
        return ['path' => '', 'alt' => '', 'caption' => ''];
    }

    /** @return array{label:string,url:string} */
    public static function emptyLink(): array
    {
        return ['label' => '', 'url' => ''];
    }

    /** A new, empty block of a type (the editor's "Add block"). */
    public static function newBlock(string $type): array
    {
        $id = self::newId();
        return match ($type) {
            'article' => ['id' => $id, 'type' => 'article', 'heading' => '', 'body_html' => '', 'image' => self::emptyImage(), 'link' => self::emptyLink()],
            'image'   => ['id' => $id, 'type' => 'image', 'image' => self::emptyImage()],
            'quote'   => ['id' => $id, 'type' => 'quote', 'body_html' => '', 'cite' => ''],
            'button'  => ['id' => $id, 'type' => 'button', 'button' => ['label' => '', 'url' => '', 'style' => 'primary']],
            'divider' => ['id' => $id, 'type' => 'divider'],
            default   => throw new \InvalidArgumentException('Unknown block type.'),
        };
    }

    public static function newId(): string
    {
        return 'b' . bin2hex(random_bytes(4));
    }

    /** Tolerant read of a stored value (JSON string or array): never throws, always a complete safe structure, no file checks. */
    public static function decode(string|array|null $stored): array
    {
        return self::normalise($stored, ['strict' => false, 'check_files' => false])['content'];
    }

    /** Compact UTF-8 JSON of a (normalised) content array, ready for the `content` column. */
    public static function encode(array $content): string
    {
        return (string) json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * Validate + clean a submitted or stored content value.
     *
     * @param mixed $input JSON text or decoded array (null = empty issue; any other type, e.g. a JSON number, is reported as an error, never thrown)
     * @param array{strict?:bool,check_files?:bool} $opts strict (default true): problems are errors; false: cleared + warnings.
     *        check_files (default true): image paths must exist on disk.
     * @return array{ok:bool,content:array<string,mixed>,errors:array<string,string>,warnings:array<string,string>}
     */
    public static function normalise(mixed $input, array $opts = []): array
    {
        $strict = (bool) ($opts['strict'] ?? true);
        $files = (bool) ($opts['check_files'] ?? true);
        $ctx = new class ($strict, $files) {
            /** @var array<string,string> */
            public array $errors = [];
            /** @var array<string,string> */
            public array $warnings = [];
            /** @var array<string,bool> */
            public array $ids = [];

            public function __construct(public bool $strict, public bool $files)
            {
            }

            public function problem(string $path, string $message): void
            {
                if ($this->strict) {
                    $this->errors[$path] ??= $message;
                } else {
                    $this->warnings[$path] ??= $message;
                }
            }
        };

        $data = $input;
        if (is_string($data)) {
            $data = trim($data) === '' ? [] : json_decode($data, true, 64);
        }
        if ($input === null) {
            $data = [];
        } elseif (!is_string($input) && !is_array($input)) {
            $data = null;                       // a JSON number / boolean: reported below like any other malformed shape
        }
        if (!is_array($data) || ($data !== [] && array_is_list($data))) {
            $ctx->problem('content', 'The content must be an object.');
            $data = [];
        }

        $version = $data['version'] ?? self::VERSION;
        if (!is_int($version) && !(is_string($version) && ctype_digit($version))) {
            $ctx->problem('version', 'The content version is not valid.');
        } elseif ((int) $version !== self::VERSION) {
            $ctx->problem('version', 'This content version is not supported.');
        }

        $out = self::emptyContent();
        $out['featured'] = self::featured($data['featured'] ?? null, $ctx);

        $blocksIn = $data['blocks'] ?? [];
        if (!is_array($blocksIn) || ($blocksIn !== [] && !array_is_list($blocksIn))) {
            $ctx->problem('blocks', 'The blocks must be a list.');
            $blocksIn = [];
        }
        if (count($blocksIn) > self::MAX_BLOCKS) {
            $ctx->problem('blocks', 'An issue can have at most ' . self::MAX_BLOCKS . ' blocks.');
            $blocksIn = array_slice($blocksIn, 0, self::MAX_BLOCKS);
        }
        foreach ($blocksIn as $i => $block) {
            $b = self::block($block, 'blocks.' . $i, $ctx);
            if ($b !== null) {
                $out['blocks'][] = $b;
            }
        }

        if (strlen(self::encode($out)) > self::MAX_BYTES) {
            $ctx->problem('content', 'This issue is too large. Remove some text or blocks.');
        }

        return ['ok' => $ctx->errors === [], 'content' => $out, 'errors' => $ctx->errors, 'warnings' => $ctx->warnings];
    }

    // ------------------------------------------------------------------------------------------------ parts

    private static function featured(mixed $in, object $ctx): array
    {
        if ($in === null || $in === '' || $in === []) {
            return self::emptyContent()['featured'];
        }
        if (!is_array($in) || array_is_list($in)) {
            $ctx->problem('featured', 'The featured article must be an object.');
            return self::emptyContent()['featured'];
        }
        return [
            'heading'   => self::text($in['heading'] ?? '', self::MAX_HEADING, 'featured.heading', 'The heading', $ctx),
            'body_html' => self::html($in['body_html'] ?? '', 'featured.body_html', $ctx),
            'image'     => self::image($in['image'] ?? null, 'featured.image', $ctx),
            'link'      => self::link($in['link'] ?? null, 'featured.link', $ctx),
        ];
    }

    private static function block(mixed $in, string $path, object $ctx): ?array
    {
        if (!is_array($in) || array_is_list($in)) {
            $ctx->problem($path, 'A block must be an object.');
            return null;
        }
        $type = $in['type'] ?? '';
        if (!is_string($type) || !in_array($type, self::BLOCK_TYPES, true)) {
            $ctx->problem($path . '.type', 'Unknown block type.');
            return null;
        }
        $id = $in['id'] ?? '';
        if (!is_string($id) || preg_match('/^[a-z][a-z0-9_-]{1,31}$/', $id) !== 1 || isset($ctx->ids[$id])) {
            do {
                $id = self::newId();
            } while (isset($ctx->ids[$id]));
        }
        $ctx->ids[$id] = true;

        switch ($type) {
            case 'article':
                return [
                    'id' => $id, 'type' => 'article',
                    'heading'   => self::text($in['heading'] ?? '', self::MAX_HEADING, $path . '.heading', 'The heading', $ctx),
                    'body_html' => self::html($in['body_html'] ?? '', $path . '.body_html', $ctx),
                    'image'     => self::image($in['image'] ?? null, $path . '.image', $ctx),
                    'link'      => self::link($in['link'] ?? null, $path . '.link', $ctx),
                ];
            case 'image':
                return ['id' => $id, 'type' => 'image', 'image' => self::image($in['image'] ?? null, $path . '.image', $ctx)];
            case 'quote':
                return [
                    'id' => $id, 'type' => 'quote',
                    'body_html' => self::html($in['body_html'] ?? '', $path . '.body_html', $ctx),
                    'cite'      => self::text($in['cite'] ?? '', self::MAX_CITE, $path . '.cite', 'The name', $ctx),
                ];
            case 'button':
                return ['id' => $id, 'type' => 'button', 'button' => self::button($in['button'] ?? null, $path . '.button', $ctx)];
            default:
                return ['id' => $id, 'type' => 'divider'];
        }
    }

    /** @return array{path:string,alt:string,caption:string} */
    private static function image(mixed $in, string $path, object $ctx): array
    {
        $empty = self::emptyImage();
        if ($in === null || $in === '' || $in === []) {
            return $empty;
        }
        if (!is_array($in) || array_is_list($in)) {
            $ctx->problem($path, 'The image must be an object.');
            return $empty;
        }
        $p = $in['path'] ?? '';
        if (!is_string($p) && $p !== null) {
            $ctx->problem($path . '.path', 'The image path is not valid.');
            $p = '';
        }
        $p = trim((string) $p);
        $alt = self::text($in['alt'] ?? '', self::MAX_ALT, $path . '.alt', 'The description', $ctx);
        $caption = self::text($in['caption'] ?? '', self::MAX_CAPTION, $path . '.caption', 'The caption', $ctx);
        if ($p === '') {
            return $empty;
        }
        if (!self::validImagePath($p)) {
            $ctx->problem($path . '.path', 'That image is not one of this site\'s uploads. Upload it again.');
            return $empty;
        }
        if ($ctx->files && !self::imageExists($p)) {
            $ctx->problem($path . '.path', 'That image could not be found. Upload it again.');
            return $empty;
        }
        return ['path' => $p, 'alt' => $alt, 'caption' => $caption];
    }

    /** @return array{label:string,url:string} */
    private static function link(mixed $in, string $path, object $ctx): array
    {
        $empty = self::emptyLink();
        if ($in === null || $in === '' || $in === []) {
            return $empty;
        }
        if (!is_array($in) || array_is_list($in)) {
            $ctx->problem($path, 'The link must be an object.');
            return $empty;
        }
        return [
            'label' => self::text($in['label'] ?? '', self::MAX_LABEL, $path . '.label', 'The link text', $ctx),
            'url'   => self::url($in['url'] ?? '', $path . '.url', $ctx),
        ];
    }

    /** @return array{label:string,url:string,style:string} */
    private static function button(mixed $in, string $path, object $ctx): array
    {
        $empty = ['label' => '', 'url' => '', 'style' => 'primary'];
        if ($in === null || $in === '' || $in === []) {
            return $empty;
        }
        if (!is_array($in) || array_is_list($in)) {
            $ctx->problem($path, 'The button must be an object.');
            return $empty;
        }
        $style = $in['style'] ?? 'primary';
        if (!is_string($style) || !in_array($style, self::BUTTON_STYLES, true)) {
            $ctx->problem($path . '.style', 'The button style must be primary or outline.');
            $style = 'primary';
        }
        return [
            'label' => self::text($in['label'] ?? '', self::MAX_LABEL, $path . '.label', 'The button text', $ctx),
            'url'   => self::url($in['url'] ?? '', $path . '.url', $ctx),
            'style' => $style,
        ];
    }

    // ------------------------------------------------------------------------------------------------ scalars

    /** Plain text field: tags stripped, whitespace collapsed, length-capped. */
    private static function text(mixed $v, int $max, string $path, string $what, object $ctx): string
    {
        if ($v === null) {
            return '';
        }
        if (!is_string($v) && !is_int($v) && !is_float($v)) {
            $ctx->problem($path, $what . ' must be text.');
            return '';
        }
        $s = Sanitizer::text((string) $v);
        if (mb_strlen($s) > $max) {
            $ctx->problem($path, $what . ' is too long (at most ' . $max . ' characters).');
            $s = mb_substr($s, 0, $max);
        }
        return $s;
    }

    /** Rich text: Sanitizer::html(), length-capped. */
    private static function html(mixed $v, string $path, object $ctx): string
    {
        if ($v === null) {
            return '';
        }
        if (!is_string($v)) {
            $ctx->problem($path, 'The text must be a string.');
            return '';
        }
        $s = Sanitizer::html($v);
        if (mb_strlen($s) > self::MAX_BODY) {
            $ctx->problem($path, 'This text is too long. Split it into two blocks.');
            return '';
        }
        return $s;
    }

    private static function url(mixed $v, string $path, object $ctx): string
    {
        if ($v === null || $v === '') {
            return '';
        }
        if (!is_string($v)) {
            $ctx->problem($path, 'The link address must be text.');
            return '';
        }
        $clean = self::cleanUrl($v);
        if ($clean === '') {
            $ctx->problem($path, 'Use a full web address (https://...), an e-mail link (mailto:...), a phone link (tel:...) or a page of this site (/about).');
        }
        return $clean;
    }

    // ------------------------------------------------------------------------------------------------ public helpers

    /**
     * A link target for a newsletter: http(s), mailto, tel or a site path ("/about"; stored root-relative). '' when not allowed
     * (javascript:, data:, protocol-relative //host, anchors, ...).
     */
    public static function cleanUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || strlen($url) > self::MAX_URL) {
            return '';
        }
        $clean = Sanitizer::url($url);
        if ($clean === '' || $clean[0] === '#') {
            return '';
        }
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $clean, $m)) {
            return in_array(strtolower($m[1]), ['http', 'https', 'mailto', 'tel'], true) ? $clean : '';
        }
        return $clean[0] === '/' && !str_starts_with($clean, '//') ? $clean : '';
    }

    /** Image paths an issue may use: uploads ("2026/10/abc.jpg") or a bundled photo ("assets/img/site/photo-04.jpg"). */
    public static function validImagePath(string $path): bool
    {
        if (str_contains($path, '..') || str_contains($path, '\\')) {
            return false;
        }
        return preg_match('~^\d{4}/\d{2}/[A-Za-z0-9_-]+\.(jpe?g|png|gif|webp)$~i', $path) === 1
            || preg_match('~^assets/img/[A-Za-z0-9_./-]+\.(jpe?g|png|gif|webp)$~i', $path) === 1;
    }

    public static function imageExists(string $path): bool
    {
        if (!self::validImagePath($path)) {
            return false;
        }
        $file = str_starts_with($path, 'assets/') ? BASE_PATH . '/' . $path : UPLOAD_PATH . '/' . $path;
        return is_file($file);
    }

    /** First usable image of an issue (featured first, then blocks in order) as ['path','alt','caption'], or null. */
    public static function firstImage(array $content): ?array
    {
        $c = self::decode($content);
        if ($c['featured']['image']['path'] !== '') {
            return $c['featured']['image'];
        }
        foreach ($c['blocks'] as $b) {
            if (($b['image']['path'] ?? '') !== '') {
                return $b['image'];
            }
        }
        return null;
    }

    /** Readable plain text of the whole issue (featured, then blocks), paragraphs separated by blank lines. $max = 0 means no limit. */
    public static function plainText(array $content, int $max = 0): string
    {
        $c = self::decode($content);
        $parts = [];
        $f = $c['featured'];
        foreach ([$f['heading'], self::htmlToText($f['body_html'], false), $f['link']['label']] as $p) {
            if ($p !== '') {
                $parts[] = $p;
            }
        }
        foreach ($c['blocks'] as $b) {
            foreach ([$b['heading'] ?? '', self::htmlToText((string) ($b['body_html'] ?? ''), false), $b['cite'] ?? '', $b['button']['label'] ?? '', $b['link']['label'] ?? ''] as $p) {
                if ($p !== '') {
                    $parts[] = $p;
                }
            }
        }
        $text = implode("\n\n", $parts);
        return $max > 0 ? self::clip($text, $max) : $text;
    }

    /** Short single-line excerpt (for SEO descriptions): whitespace collapsed, cut at a word boundary with an ellipsis. */
    public static function clip(string $text, int $max): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > $max * 0.6) {
            $cut = mb_substr($cut, 0, $space);
        }
        return rtrim($cut, " ,.;:-") . "\u{2026}";
    }

    /**
     * Sanitised rich text to readable plain text (the text/plain part of the e-mail, SEO fallbacks). Paragraphs and list items become lines;
     * with $links, "<a href=x>label</a>" becomes "label (x)" (root-relative links are made absolute).
     */
    public static function htmlToText(string $html, bool $links = true): string
    {
        $html = Sanitizer::html($html);
        if ($html === '') {
            return '';
        }
        if ($links) {
            $html = (string) preg_replace_callback('~<a\b[^>]*\bhref="([^"]*)"[^>]*>(.*?)</a>~is', static function (array $m): string {
                $href = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($href !== '' && $href[0] === '/' && !str_starts_with($href, '//')) {
                    $href = abs_url(ltrim($href, '/'));
                }
                $label = trim(strip_tags($m[2]));
                if (str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    return $label !== '' ? $label : substr($href, strpos($href, ':') + 1);
                }
                return $label === '' || $label === $href ? $href : $label . ' (' . $href . ')';
            }, $html);
        }
        $html = (string) preg_replace('~<li\b[^>]*>~i', "\n- ", $html);
        $html = (string) preg_replace('~<br\s*/?>~i', "\n", $html);
        $html = (string) preg_replace('~</(p|h2|h3|h4|blockquote|ul|ol|pre|table|figure)>~i', "\n\n", $html);
        $html = (string) preg_replace('~</(li|tr|caption|figcaption)>~i', "\n", $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = (string) preg_replace("/[ \t\x{00A0}]+/u", ' ', $text);
        $text = (string) preg_replace("/ *\n */", "\n", $text);
        return trim((string) preg_replace("/\n{3,}/", "\n\n", $text));
    }

    /**
     * What is missing before an issue can go live. Blocking problems stop "Publish" / "Schedule"; warnings are shown but do not.
     *
     * @param array $content normalised content
     * @param array $issue   the newsletters row (or the details being saved): title, summary, cover_image, cover_alt
     * @return array{blocking:array<string,string>,warnings:array<string,string>}
     */
    public static function readiness(array $content, array $issue = []): array
    {
        $c = self::decode($content);
        $blocking = [];
        $warn = [];
        if (trim((string) ($issue['title'] ?? '')) === '') {
            $blocking['title'] = 'Give the issue a title.';
        }
        $f = $c['featured'];
        $featuredFilled = $f['heading'] !== '' || $f['body_html'] !== '';
        if (!$featuredFilled && $c['blocks'] === []) {
            $blocking['content'] = 'Write the featured article or add at least one block.';
        }
        if ($f['image']['path'] !== '' && $f['image']['alt'] === '') {
            $warn['featured.image.alt'] = 'Describe the featured image for people who cannot see it.';
        }
        self::linkProblems($f['link'], 'featured.link', $blocking);
        foreach ($c['blocks'] as $i => $b) {
            $p = 'blocks.' . $i;
            switch ($b['type']) {
                case 'article':
                    if ($b['heading'] === '' && $b['body_html'] === '' && $b['image']['path'] === '') {
                        $blocking[$p] = 'This article block is empty. Fill it in or remove it.';
                    }
                    self::linkProblems($b['link'], $p . '.link', $blocking);
                    if ($b['image']['path'] !== '' && $b['image']['alt'] === '') {
                        $warn[$p . '.image.alt'] = 'Describe this image for people who cannot see it.';
                    }
                    break;
                case 'image':
                    if ($b['image']['path'] === '') {
                        $blocking[$p] = 'This image block has no picture. Upload one or remove the block.';
                    } elseif ($b['image']['alt'] === '') {
                        $warn[$p . '.image.alt'] = 'Describe this image for people who cannot see it.';
                    }
                    break;
                case 'quote':
                    if ($b['body_html'] === '') {
                        $blocking[$p] = 'This quote is empty. Type the quote or remove the block.';
                    }
                    break;
                case 'button':
                    if ($b['button']['label'] === '' || $b['button']['url'] === '') {
                        $blocking[$p] = 'A button needs both its text and its link.';
                    }
                    break;
            }
        }
        if (trim((string) ($issue['summary'] ?? '')) === '') {
            $warn['summary'] = 'A short summary helps in the archive, in search results and when the issue is shared.';
        }
        if (($issue['cover_image'] ?? '') !== '' && trim((string) ($issue['cover_alt'] ?? '')) === '') {
            $warn['cover_alt'] = 'Describe the cover image for people who cannot see it.';
        }
        return ['blocking' => $blocking, 'warnings' => $warn];
    }

    /** @param array{label:string,url:string} $link */
    private static function linkProblems(array $link, string $path, array &$blocking): void
    {
        if (($link['label'] === '') !== ($link['url'] === '')) {
            $blocking[$path] = 'A link needs both its text and its address.';
        }
    }
}
