<?php
declare(strict_types=1);

namespace App\Services\Opportunities;

/**
 * Reads an RSS 2.0 or Atom feed into a plain list. No network, no database: easy to test.
 * Each item: title, link (http/https only), guid, date (unix time or null), categories (list<string>), text (HTML as sent), content (HTML as sent, may be '').
 * ENTITY declarations are refused (no entity tricks) and external resources are never loaded.
 */
final class FeedParser
{
    /** @return array{ok:bool,items:list<array<string,mixed>>,title:string,error:?string} */
    public static function parse(string $xml): array
    {
        $xml = trim($xml);
        if (str_starts_with($xml, "\xEF\xBB\xBF")) {
            $xml = substr($xml, 3);
        }
        $bad = ['ok' => false, 'items' => [], 'title' => '', 'error' => 'That address did not return a readable RSS or Atom news feed.'];
        if ($xml === '' || $xml[0] !== '<' || stripos($xml, '<!ENTITY') !== false || preg_match('/^<(!doctype\s+html|html)\b/i', $xml) === 1) {
            return $bad;
        }
        $prev = libxml_use_internal_errors(true);
        try {
            $doc = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }
        if (!$doc instanceof \SimpleXMLElement) {
            return $bad;
        }
        $items = [];
        $title = '';
        $root = strtolower($doc->getName());
        if ($root === 'rss' || $root === 'rdf') {
            $title = trim((string) ($doc->channel->title ?? ''));
            foreach ($doc->xpath('//item') ?: [] as $it) {
                $items[] = self::rssItem($it);
            }
        } elseif ($root === 'feed') {
            $title = trim((string) $doc->title);
            foreach ($doc->entry ?? [] as $en) {
                $items[] = self::atomEntry($en);
            }
        } else {
            return $bad;
        }
        $items = array_values(array_filter($items, static fn(array $i): bool => $i['title'] !== '' && $i['link'] !== ''));
        return ['ok' => true, 'items' => $items, 'title' => $title, 'error' => null];
    }

    private static function rssItem(\SimpleXMLElement $it): array
    {
        $content = $it->children('http://purl.org/rss/1.0/modules/content/');
        $dc = $it->children('http://purl.org/dc/elements/1.1/');
        $cats = [];
        foreach ($it->category ?? [] as $c) {
            $c = trim((string) $c);
            if ($c !== '') {
                $cats[] = $c;
            }
        }
        $date = trim((string) ($it->pubDate ?? '')) ?: trim((string) ($dc->date ?? ''));
        $link = trim((string) ($it->link ?? ''));
        return [
            'title' => self::plain((string) ($it->title ?? '')),
            'link' => self::safeUrl($link),
            'guid' => trim((string) ($it->guid ?? '')) ?: $link,
            'date' => $date !== '' ? (strtotime($date) ?: null) : null,
            'categories' => $cats,
            'text' => (string) ($it->description ?? ''),
            'content' => (string) ($content->encoded ?? ''),
        ];
    }

    private static function atomEntry(\SimpleXMLElement $en): array
    {
        $link = '';
        foreach ($en->link ?? [] as $l) {
            $rel = (string) ($l['rel'] ?? 'alternate');
            if ($rel === 'alternate' || $link === '') {
                $link = trim((string) ($l['href'] ?? ''));
            }
        }
        $cats = [];
        foreach ($en->category ?? [] as $c) {
            $t = trim((string) ($c['term'] ?? $c));
            if ($t !== '') {
                $cats[] = $t;
            }
        }
        $date = trim((string) ($en->published ?? '')) ?: trim((string) ($en->updated ?? ''));
        return [
            'title' => self::plain((string) ($en->title ?? '')),
            'link' => self::safeUrl($link),
            'guid' => trim((string) ($en->id ?? '')) ?: $link,
            'date' => $date !== '' ? (strtotime($date) ?: null) : null,
            'categories' => $cats,
            'text' => (string) ($en->summary ?? ''),
            'content' => (string) ($en->content ?? ''),
        ];
    }

    private static function plain(string $s): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($s), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function safeUrl(string $u): string
    {
        $u = trim($u);
        return preg_match('~^https?://[^\s<>"\']+$~i', $u) === 1 && strlen($u) <= 500 ? $u : '';
    }
}
