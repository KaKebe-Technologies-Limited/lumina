<?php
declare(strict_types=1);

namespace App\Services\Social;

/**
 * Turns an issue into the PLAIN-TEXT caption of a channel (docs/SOCIAL.md section 7). Static, no database access except the settings it reads (hashtags, UTM option,
 * custom templates). Used by the share panel (pre-filled captions, live counter), the dispatcher, the Share kit, the webhook payload and the tests.
 *
 *   CaptionBuilder::build('x', ['title'=>..., 'summary'=>..., 'url'=>'https://site/newsletter/slug', 'issue_number'=>5, 'issue_date'=>'2026-10-20'])
 *       -> ['text','length','limit','over','trimmed','unknown','url']
 *
 * PLACEHOLDERS (one pass, so a title that contains "{url}" stays literal): {title} {summary} {url} {issue_number} {date} {hashtags} {site}
 *   {url} carries utm_source=<channel>&utm_medium=social&utm_campaign=newsletter when the option is on (setting social_utm). {issue_number} is just the digits ("5",
 *   empty when the issue has no number). {date} reads "20 October 2026". {hashtags} are the default hashtags (setting social_hashtags), cleaned.
 * LENGTH RULES (hard limits, Channels::get(id)->meta()['limit']; the share panel enforces them while typing, save and send enforce them again):
 *   facebook 5000, instagram 2200, x 280 (WEIGHTED: every URL counts 23, characters above U+10FF other than the usual punctuation ranges count 2, so emoji and CJK
 *   count 2), linkedin 3000, telegram 1024, webhook 5000, tiktok (share kit) 2200.
 * When a template result is too long it is SHRUNK, never cut blindly: the summary is shortened first (word boundary, "..."), then the hashtags are dropped, then the
 * title is shortened. A caption an admin typed by hand is never shortened silently: validate() reports it.
 * PLAIN TEXT: tags stripped, entities decoded, control characters, zero-width and bidirectional-override characters removed, blank lines collapsed.
 */
final class CaptionBuilder
{
    public const PLACEHOLDERS = ['title', 'summary', 'url', 'issue_number', 'date', 'hashtags', 'site'];
    public const X_URL_LENGTH = 23;
    public const DEFAULT_TEMPLATES = [
        'facebook'  => "{title}\n\n{summary}\n\n{hashtags}",
        'instagram' => "{title}\n\n{summary}\n\nRead the full newsletter: link in bio.\n\n{hashtags}",
        'x'         => "{title}\n{summary}\n{url}\n{hashtags}",
        'linkedin'  => "{title}\n\n{summary}\n\nRead the full issue: {url}\n\n{hashtags}",
        'telegram'  => "{title}\n\n{summary}\n\nRead more: {url}\n\n{hashtags}",
        'webhook'   => "{title}\n\n{summary}\n\n{url}\n\n{hashtags}",
        'tiktok'    => "{title}\n\n{summary}\n\nLink in bio.\n\n{hashtags}",
    ];
    /** at most this many hashtags per channel */
    private const HASHTAG_MAX = ['instagram' => 30, 'tiktok' => 10, 'x' => 3, 'linkedin' => 5, 'facebook' => 5, 'telegram' => 8, 'webhook' => 10];
    private const LIMITS = ['tiktok' => 2200];

    // ------------------------------------------------------------------------------------------------ templates and limits

    public static function defaultTemplate(string $channel): string
    {
        return self::DEFAULT_TEMPLATES[$channel] ?? self::DEFAULT_TEMPLATES['webhook'];
    }

    /** The template in force: the admin's own (Admin > Social media > Post templates) or the built-in one. */
    public static function template(string $channel): string
    {
        return SocialConfig::template($channel);
    }

    /** Hard caption limit of a channel (the share kit's TikTok included). */
    public static function limit(string $channel): int
    {
        if (Channels::has($channel)) {
            return (int) Channels::get($channel)->meta()['limit'];
        }
        return self::LIMITS[$channel] ?? 2200;
    }

    public static function counting(string $channel): string
    {
        return Channels::has($channel) ? (string) Channels::get($channel)->meta()['count'] : 'plain';
    }

    /**
     * How many characters a template needs WITHOUT any title, summary or hashtag: its fixed words plus a typical issue address (with the UTM tags when that option is on).
     * A template whose fixed part alone leaves no room is refused when it is saved.
     */
    public static function fixedLength(string $channel, string $template): int
    {
        $typical = 'https://www.example.org/newsletter/a-typical-issue-address-of-about-this-length';
        $b = self::build($channel, ['title' => '', 'summary' => '', 'url' => $typical, 'issue_number' => null, 'issue_date' => '', 'site' => ''], $template, ['hashtags' => '', 'fit' => false]);
        return $b['length'];
    }

    /** A made-up issue to preview a template with (the templates screen and the template checks). */
    public static function sampleIssue(): array
    {
        return [
            'id' => 0, 'title' => 'Building Pathways to Opportunity',
            'summary' => 'Our youth training programme welcomed its newest group of learners this month, and a quarry site visit showed what hands-on skills can build for a whole community.',
            'url' => abs_url('newsletter/sample-issue'), 'issue_number' => 12, 'issue_date' => date('Y-m-d'), 'site' => (string) setting('site_name', ''),
        ];
    }

    // ------------------------------------------------------------------------------------------------ building

    /**
     * @param array{title?:mixed,summary?:mixed,url?:mixed,issue_number?:mixed,issue_date?:mixed,site?:mixed} $issue url = the PLAIN public address (the UTM tags are added here)
     * @param array{hashtags?:string,utm?:bool,fit?:bool} $opts
     * @return array{text:string,length:int,limit:int,over:bool,trimmed:bool,unknown:list<string>,url:string}
     */
    public static function build(string $channel, array $issue, ?string $template = null, array $opts = []): array
    {
        $tpl = $template ?? self::template($channel);
        $url = self::utmUrl((string) ($issue['url'] ?? ''), $channel, $opts['utm'] ?? null);
        $number = (int) ($issue['issue_number'] ?? 0);
        $vars = [
            'title'        => self::sanitize((string) ($issue['title'] ?? ''), false),
            'summary'      => self::sanitize((string) ($issue['summary'] ?? ''), false),
            'url'          => $url,
            'issue_number' => $number > 0 ? (string) $number : '',
            'date'         => self::formatDate((string) ($issue['issue_date'] ?? '')),
            'hashtags'     => self::hashtags((string) ($opts['hashtags'] ?? SocialConfig::str('social_hashtags')), self::HASHTAG_MAX[$channel] ?? 10),
            'site'         => self::sanitize((string) ($issue['site'] ?? setting('site_name', '')), false),
        ];
        $unknown = [];
        $render = static function (array $v) use ($tpl, &$unknown): string {
            $unknown = [];
            $out = (string) preg_replace_callback('/\{([A-Za-z_]+)\}/', static function (array $m) use ($v, &$unknown): string {
                $k = strtolower($m[1]);
                if (array_key_exists($k, $v)) {
                    return (string) $v[$k];
                }
                $unknown[] = $m[0];
                return $m[0];
            }, $tpl);
            return self::tidy($out);
        };
        $text = $render($vars);
        $limit = self::limit($channel);
        $trimmed = false;
        if (($opts['fit'] ?? true) && self::length($channel, $text) > $limit) {
            $trimmed = true;
            $text = self::fit($channel, $vars, $render, $limit);
        }
        $len = self::length($channel, $text);
        return ['text' => $text, 'length' => $len, 'limit' => $limit, 'over' => $len > $limit, 'trimmed' => $trimmed, 'unknown' => array_values(array_unique($unknown)), 'url' => $url];
    }

    /**
     * Check a caption somebody typed: sanitise it and compare with the channel's limit. Nothing is shortened.
     * @return array{ok:bool,text:string,length:int,limit:int,over:bool,error:string}
     */
    public static function validate(string $channel, string $text): array
    {
        $clean = self::tidy(self::sanitize($text, true));
        $len = self::length($channel, $clean);
        $limit = self::limit($channel);
        $error = $clean === '' ? 'The caption is empty.' : ($len > $limit ? 'The caption is ' . ($len - $limit) . ' character' . ($len - $limit === 1 ? '' : 's') . ' too long for ' . Channels::label($channel) . ' (limit ' . $limit . ').' : '');
        return ['ok' => $error === '', 'text' => $clean, 'length' => $len, 'limit' => $limit, 'over' => $len > $limit, 'error' => $error];
    }

    /**
     * Shrink to fit: summary first, then drop the hashtags, then the title; a last resort cuts the finished text.
     * @param array<string,string> $vars
     */
    private static function fit(string $channel, array $vars, \Closure $render, int $limit): string
    {
        $fits = static fn(string $t): bool => self::length($channel, $t) <= $limit;
        foreach (['summary', 'hashtags', 'title'] as $step) {
            $full = $vars[$step];
            if ($full === '') {
                continue;
            }
            if ($step === 'hashtags') {
                $vars['hashtags'] = '';
                if ($fits($render($vars))) {
                    return $render($vars);
                }
                continue;
            }
            $lo = 0;
            $hi = mb_strlen($full);
            while ($lo < $hi) {                                           // largest clip length that fits (binary search)
                $mid = intdiv($lo + $hi + 1, 2);
                $vars[$step] = self::clip($full, $mid);
                if ($fits($render($vars))) {
                    $lo = $mid;
                } else {
                    $hi = $mid - 1;
                }
            }
            $vars[$step] = $lo > 0 ? self::clip($full, $lo) : '';
            $try = $render($vars);
            if ($fits($try)) {
                return $try;
            }
        }
        $text = $render($vars);
        return self::cutToLimit($channel, $text, $limit);
    }

    private static function cutToLimit(string $channel, string $text, int $limit): string
    {
        $chars = mb_str_split($text);
        while ($chars !== [] && self::length($channel, implode('', $chars)) > $limit - 1) {
            array_pop($chars);
        }
        return rtrim(implode('', $chars)) . '…';
    }

    /** Shorten at a word boundary and add an ellipsis; the result is at most $max characters long. */
    public static function clip(string $text, int $max): string
    {
        $text = trim($text);
        if ($max <= 0) {
            return '';
        }
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        if ($max === 1) {
            return '…';
        }
        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > (int) ($max * 0.5)) {
            $cut = mb_substr($cut, 0, $space);
        }
        return rtrim($cut, " \t\n.,;:-") . '…';
    }

    // ------------------------------------------------------------------------------------------------ counting

    /**
     * Length of a caption the way the platform counts it. 'plain' = characters; 'x' = weighted (URLs 23, wide characters 2).
     */
    public static function length(string $channel, string $text): int
    {
        if (self::counting($channel) !== 'x') {
            return mb_strlen($text);
        }
        $weight = 0;
        $rest = (string) preg_replace_callback('~https?://[^\s]+~iu', static function () use (&$weight): string {
            $weight += self::X_URL_LENGTH;
            return '';
        }, $text);
        foreach (mb_str_split($rest) as $ch) {
            $cp = mb_ord($ch, 'UTF-8');
            $weight += ($cp <= 4351 || ($cp >= 8192 && $cp <= 8205) || ($cp >= 8208 && $cp <= 8223) || ($cp >= 8242 && $cp <= 8247)) ? 1 : 2;
        }
        return $weight;
    }

    // ------------------------------------------------------------------------------------------------ text hygiene

    /**
     * Plain text from anything: tags stripped, entities decoded, control / zero-width / bidirectional characters removed, spaces collapsed.
     * $multiline false folds every line break into a space (titles, summaries).
     */
    public static function sanitize(string $text, bool $multiline = true): string
    {
        if ($text === '') {
            return '';
        }
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = (string) mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        $text = self::stripMarkup($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = self::stripMarkup($text);                                   // "&lt;b&gt;" decoded into a tag-looking text: strip once more
        $text = str_replace(["\r\n", "\r", "\x{2028}", "\x{2029}", "\x{0085}"], "\n", $text);
        $text = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        $text = (string) preg_replace('/[\x{00AD}\x{061C}\x{180E}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{206F}\x{FEFF}\x{FFF9}-\x{FFFB}\x{E0000}-\x{E007F}]/u', '', $text);
        if (!$multiline) {
            $text = (string) preg_replace('/\s+/u', ' ', $text);
        }
        return trim($text);
    }

    /** HTML element names whose tags are removed (everything else that merely contains a "<" is plain text: "<5%", "I <3 Uganda", "x<y", "<Youth> Fund"). */
    private const HTML_ELEMENTS = 'a|abbr|address|article|aside|audio|b|base|bdi|bdo|blockquote|body|br|button|canvas|caption|center|cite|code|col|colgroup|data|dd|del|details|dfn|dialog|div|dl|dt|em|embed|fieldset|figcaption|figure|font|footer|form|frame|frameset|h[1-6]|head|header|hr|html|i|iframe|img|input|ins|kbd|label|legend|li|link|main|map|mark|menu|meta|meter|nav|noscript|object|ol|optgroup|option|output|p|param|picture|pre|progress|q|rp|rt|ruby|s|samp|script|section|select|small|source|span|strike|strong|style|sub|summary|sup|svg|table|tbody|td|template|textarea|tfoot|th|thead|time|title|tr|track|tt|u|ul|var|video|wbr|xmp';

    /**
     * Remove real HTML / XML markup from text that is meant to be plain: comments, processing instructions and complete tags of known HTML elements (the text between them
     * stays). Unlike strip_tags() a "<" that does not start such a tag is kept, so a title like "Dropout rate <5% after our programme" is never cut off.
     */
    private static function stripMarkup(string $text): string
    {
        if (!str_contains($text, '<')) {
            return $text;
        }
        $text = (string) preg_replace(['/<!--.*?-->/su', '/<\?.*?\?>/su', '/<!\[CDATA\[.*?\]\]>/su', '/<![a-z][^<>]*>/iu'], '', $text);
        return (string) preg_replace('~</?(?:' . self::HTML_ELEMENTS . ')(?=[\s/>])(?:[^<>"\']|"[^"]*"|\'[^\']*\')*>~iu', '', $text);
    }

    /** Layout clean-up of a finished caption: no trailing spaces, at most one blank line in a row. */
    private static function tidy(string $text): string
    {
        $text = (string) preg_replace('/[ \t\x{00A0}]+/u', ' ', $text);
        $text = (string) preg_replace('/ *\n */', "\n", $text);
        $text = (string) preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    /** Hashtags from a free list ("#a b, c"): letters, digits and underscores only, no pure numbers, no duplicates, at most $max. Returns "#a #b". */
    public static function hashtags(string $list, int $max = 10): string
    {
        return implode(' ', self::hashtagList($list, $max));
    }

    /** @return list<string> */
    public static function hashtagList(string $list, int $max = 10): array
    {
        $out = [];
        $seen = [];
        foreach (preg_split('/[\s,;]+/u', self::sanitize($list, false), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $tok) {
            $tag = (string) preg_replace('/[^\p{L}\p{N}_]/u', '', ltrim($tok, '#'));
            if ($tag === '' || ctype_digit($tag) || mb_strlen($tag) > 60 || isset($seen[mb_strtolower($tag)])) {
                continue;
            }
            $seen[mb_strtolower($tag)] = true;
            $out[] = '#' . $tag;
            if (count($out) >= $max) {
                break;
            }
        }
        return $out;
    }

    /** Add utm_source=<channel>&utm_medium=social&utm_campaign=newsletter to an address (existing query kept, existing utm_ values not overridden). */
    public static function utmUrl(string $url, string $channel, ?bool $on = null): string
    {
        $on ??= SocialConfig::bool('social_utm');
        if (!$on || $url === '' || preg_match('~^https?://~i', $url) !== 1) {
            return $url;
        }
        $fragment = '';
        if (($h = strpos($url, '#')) !== false) {
            $fragment = substr($url, $h);
            $url = substr($url, 0, $h);
        }
        $tags = ['utm_source' => preg_replace('/[^a-z0-9_]/', '', strtolower($channel)) ?: 'social', 'utm_medium' => 'social', 'utm_campaign' => 'newsletter'];
        foreach ($tags as $k => $v) {
            if (preg_match('/[?&]' . $k . '=/i', $url) === 1) {
                unset($tags[$k]);
            }
        }
        if ($tags === []) {
            return $url . $fragment;
        }
        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($tags, '', '&', PHP_QUERY_RFC3986) . $fragment;
    }

    /** "2026-10-20" -> "20 October 2026" (empty for anything else). */
    public static function formatDate(string $date): string
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', substr($date, 0, 10));
        return $d !== false && $d->format('Y-m-d') === substr($date, 0, 10) ? $d->format('j F Y') : '';
    }

    /** HTML-escape a plain caption for Telegram's HTML parse mode (the built-in Telegram channel sends plain text and does not need this). */
    public static function telegramHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
