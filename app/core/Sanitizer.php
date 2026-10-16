<?php
declare(strict_types=1);

namespace App\Core;

/**
 * The ONLY way raw HTML may reach the database or the page. Allow-list based (DOMDocument).
 *
 *   $clean = Sanitizer::html($_POST['body']);   // on SAVE (editors, custom pages, posts, richtext content blocks)
 *   echo rich($row['body']);                    // on OUTPUT: helper = Sanitizer::rich() = html() + base-path expansion
 *   Sanitizer::text($s)                         // strip tags, collapse whitespace (plain text fields)
 *   Sanitizer::url($s)                          // validate a link for url fields; '' when unsafe
 *   Sanitizer::localPath($s)                    // site-local path ("/uploads/x.jpg") or null
 *
 * Allowed: p br strong em u ul ol li a[href title rel target] h2 h3 h4 blockquote img[src alt title width height loading]
 *          figure figcaption hr code pre table thead tbody tfoot tr th td caption[colspan rowspan scope].
 * b->strong, i->em, h1->h2, h5/h6->h4, div->p. Everything else is unwrapped; script/style/iframe/object/form/svg... are
 * removed with their content. Event handlers (on*), style, javascript:/data:/vbscript: URLs are dropped.
 *
 * Internal URLs are STORED root-relative (e.g. "/uploads/2026/10/x.jpg", "/about") so the site can move between
 * http://localhost/lumina/, a domain root or a sub-folder; expand()/rich() prefix the current base path on output.
 */
final class Sanitizer
{
    private const DROP = ['script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'form', 'input', 'button',
        'textarea', 'select', 'option', 'svg', 'math', 'link', 'meta', 'base', 'noscript', 'template', 'head', 'title', 'video',
        'audio', 'canvas', 'source', 'track', 'map', 'area', 'dialog', 'portal'];

    private const RENAME = ['b' => 'strong', 'i' => 'em', 'h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4', 'div' => 'p', 'strike' => 'u', 's' => 'u',
        'ins' => 'u', 'dl' => 'ul', 'dt' => 'li', 'dd' => 'li', 'center' => 'p', 'section' => 'p', 'article' => 'p', 'header' => 'p', 'footer' => 'p', 'main' => 'p', 'aside' => 'p'];

    private const ALLOWED = [
        'p' => ['class'], 'br' => [], 'strong' => [], 'em' => [], 'u' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'title', 'target', 'rel', 'class'], 'h2' => [], 'h3' => [], 'h4' => [], 'blockquote' => [],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading', 'class'], 'figure' => ['class'], 'figcaption' => [],
        'hr' => [], 'code' => [], 'pre' => [], 'table' => ['class'], 'thead' => [], 'tbody' => [], 'tfoot' => [], 'tr' => [],
        'th' => ['colspan', 'rowspan', 'scope'], 'td' => ['colspan', 'rowspan'], 'caption' => [],
    ];

    private const CLASS_ALLOW = ['lead', 'text-center', 'text-right', 'text-left', 'align-left', 'align-center', 'align-right', 'btn', 'btn--primary', 'btn--outline',
        'img-left', 'img-right', 'img-full', 'table', 'table--striped'];

    /** Sanitise untrusted HTML. Idempotent. Returns '' for empty input. */
    public static function html(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        if (!extension_loaded('dom')) {
            return nl2br(htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8'));
        }
        $html = str_replace("\0", '', $html);
        $prev = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="lumina-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $dom->getElementById('lumina-root');
        if ($root === null) {
            foreach ($dom->getElementsByTagName('div') as $d) {
                $root = $d;
                break;
            }
        }
        if ($root === null) {
            return '';
        }
        self::clean($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }
        $out = preg_replace('~<p>(\s|&nbsp;|<br\s*/?>)*</p>~i', '', $out) ?? $out;   // drop empty paragraphs
        return trim($out);
    }

    private static function clean(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }
            if (!($child instanceof \DOMElement)) {
                $node->removeChild($child);          // comments, PIs, CDATA
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }
            self::clean($child);                     // children first

            if (isset(self::RENAME[$tag])) {
                $new = $child->ownerDocument->createElement(self::RENAME[$tag]);
                while ($child->firstChild) {
                    $new->appendChild($child->firstChild);
                }
                $node->replaceChild($new, $child);
                $child = $new;
                $tag = self::RENAME[$tag] ?? $tag;
            }
            if (!isset(self::ALLOWED[$tag])) {       // unknown/unsupported wrapper: keep its children, drop the element
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            self::cleanAttributes($child, $tag);
            if ($tag === 'img' && !$child->hasAttribute('src')) {
                $node->removeChild($child);
            } elseif ($tag === 'a' && !$child->hasAttribute('href') && trim($child->textContent) === '') {
                $node->removeChild($child);
            }
        }
    }

    private static function cleanAttributes(\DOMElement $el, string $tag): void
    {
        $allowed = self::ALLOWED[$tag];
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);
            $value = $attr->nodeValue ?? '';
            if (!in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }
            switch ($name) {
                case 'href':
                    $clean = self::url($value);
                    $clean === '' ? $el->removeAttribute('href') : $el->setAttribute('href', $clean);
                    break;
                case 'src':
                    $local = self::localPath($value);
                    // an <img> may never point into the admin area: a logged-in admin's browser would fetch it with their cookie
                    // (exports, CV downloads, "mark as read" views) just by opening the public page
                    if ($local !== null && preg_match('~^/(admin|storage|app|bin|database)(/|$|\?|#)~i', $local) === 1) {
                        $local = null;
                    }
                    $local === null ? $el->removeAttribute('src') : $el->setAttribute('src', $local);
                    break;
                case 'target':
                    $value === '_blank' ? $el->setAttribute('target', '_blank') : $el->removeAttribute('target');
                    break;
                case 'rel':
                    $el->removeAttribute('rel');      // recomputed below
                    break;
                case 'width':
                case 'height':
                case 'colspan':
                case 'rowspan':
                    ctype_digit($value) && (int) $value > 0 && (int) $value < 5000 ? null : $el->removeAttribute($name);
                    break;
                case 'loading':
                    $el->setAttribute('loading', 'lazy');
                    break;
                case 'scope':
                    in_array($value, ['row', 'col', 'rowgroup', 'colgroup'], true) ? null : $el->removeAttribute('scope');
                    break;
                case 'class':
                    $keep = array_values(array_intersect(preg_split('/\s+/', trim($value)) ?: [], self::CLASS_ALLOW));
                    $keep === [] ? $el->removeAttribute('class') : $el->setAttribute('class', implode(' ', $keep));
                    break;
                case 'alt':
                case 'title':
                    $el->setAttribute($name, self::text($value));
                    break;
            }
        }
        if ($tag === 'img') {
            if (!$el->hasAttribute('alt')) {
                $el->setAttribute('alt', '');
            }
            $el->setAttribute('loading', 'lazy');
        }
        if ($tag === 'a' && $el->hasAttribute('href')) {
            $href = $el->getAttribute('href');
            if (preg_match('~^https?://~i', $href)) {
                $el->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    /** Plain text: tags stripped, entities decoded, whitespace collapsed. */
    public static function text(?string $s): string
    {
        $s = html_entity_decode(strip_tags((string) $s), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string) preg_replace('/\s+/u', ' ', $s));
    }

    /**
     * Validate a link for url fields / hrefs. Returns the cleaned URL or '' when unsafe.
     * Accepts http(s)://, mailto:, tel:, "#anchor", site paths ("/about", "about", "/lumina/about" -> "/about").
     */
    public static function url(?string $url): string
    {
        $url = trim((string) $url);
        $url = (string) preg_replace('/[\x00-\x1F\x7F\s]+/u', '', $url);
        if ($url === '' || strlen($url) > 2000) {
            return '';
        }
        if ($url[0] === '#') {
            return $url;
        }
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $url, $m)) {
            $scheme = strtolower($m[1]);
            if (in_array($scheme, ['mailto', 'tel'], true)) {
                return $url;
            }
            if (in_array($scheme, ['http', 'https'], true)) {
                $local = self::localPath($url);          // same host -> root-relative
                return $local ?? (filter_var($url, FILTER_VALIDATE_URL) ? $url : '');
            }
            return '';                                  // javascript:, data:, vbscript:, file: ...
        }
        if (str_starts_with($url, '//')) {
            return '';
        }
        return self::localPath($url) ?? '';
    }

    /**
     * Convert a site-local URL to its stored root-relative form ("/uploads/x.jpg"), or null if it is not local/safe.
     */
    public static function localPath(?string $url): ?string
    {
        $url = trim((string) $url);
        $url = (string) preg_replace('/[\x00-\x1F\x7F\s]+/u', '', $url);
        if ($url === '' || str_contains($url, '\\') || str_starts_with($url, '//')) {
            return null;
        }
        $req = Request::current();
        $base = $req ? $req->basePath() : '';
        if (preg_match('~^https?://([^/?#]+)(.*)$~i', $url, $m)) {
            $host = strtolower($m[1]);
            $mine = $req ? $req->host() : '';
            if ($mine === '' || $host !== $mine) {
                return null;
            }
            $url = $m[2] === '' ? '/' : $m[2];
        } elseif (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url)) {
            return null;
        }
        if ($url[0] !== '/' && $url[0] !== '?' && $url[0] !== '#') {
            $url = '/' . $url;
        }
        if ($base !== '' && ($url === $base || str_starts_with($url, $base . '/') || str_starts_with($url, $base . '?'))) {
            $url = substr($url, strlen($base)) ?: '/';
        }
        if (preg_match('~(^|/)\.\.(/|$|\?|#)~', $url)) {
            return null;
        }
        return $url;
    }

    /** Prefix the current base path onto root-relative href/src attributes ("/about" -> "/lumina/about"). */
    public static function expand(string $html): string
    {
        $base = Request::current()?->basePath() ?? '';
        if ($base === '' || $html === '') {
            return $html;
        }
        return (string) preg_replace('~\b(href|src)="/(?!/)~i', '$1="' . $base . '/', $html);
    }

    /** html() + expand(): safe to call on already-stored content, and on defaults from the page registry. */
    public static function rich(?string $html): string
    {
        return self::expand(self::html($html));
    }
}
