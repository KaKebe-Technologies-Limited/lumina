<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Database;
use App\Core\SitePages;

/**
 * Small shared helpers for the B1 "web-core" pages (home, about, programs, projects, involvement, donate).
 * Not a controller: static, side-effect free, safe to call from views and controllers.
 */
final class HomeSupport
{
    /**
     * Replace the org-wide tokens in editable copy: {reg_number} {site_name} {founder_name} {city}.
     * When the registration number setting is empty, the whole "Reg. No: {reg_number}" fragment (and its comma) disappears.
     */
    public static function fill(?string $text): string
    {
        $text = (string) $text;
        if ($text === '' || !str_contains($text, '{')) {
            return $text;
        }
        $reg = trim((string) setting('reg_number', ''));
        if ($reg === '') {
            $text = (string) preg_replace('/,?\s*Reg\.?\s*No\.?:?\s*\{reg_number\}/u', '', $text);
        }
        return strtr($text, [
            '{reg_number}'   => $reg,
            '{site_name}'    => (string) setting('site_name', ''),
            '{founder_name}' => (string) setting('founder_name', ''),
            '{city}'         => (string) setting('city_country', ''),
        ]);
    }

    /**
     * Questions for an FAQ accordion: active rows of the `faqs` table for $group (managed in Admin > FAQs) when $group is given, else
     * (or while that group is empty) the editable repeater of the page itself ($page, $registryKey with sub-fields question + answer).
     * @return list<array{question:string,answer:string}>
     */
    public static function faqs(string $page, ?string $group, string $registryKey): array
    {
        $rows = [];
        try {
            foreach ($group === null ? [] : Database::all('SELECT question, answer FROM faqs WHERE group_key = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC', [$group]) as $r) {
                $rows[] = ['question' => (string) $r['question'], 'answer' => (string) $r['answer']];
            }
        } catch (\Throwable) {
            $rows = [];
        }
        if ($rows !== []) {
            return $rows;
        }
        foreach (content_list($page, $registryKey) as $r) {
            if (trim((string) ($r['question'] ?? '')) !== '') {
                $rows[] = ['question' => (string) $r['question'], 'answer' => (string) ($r['answer'] ?? '')];
            }
        }
        return $rows;
    }

    /** Validation messages taken from the page's editable form section ($section = "form" or "pledge"): keys err_required, err_email, err_phone, err_max, err_choice, err_number. */
    public static function formMessages(string $page, array $fields, string $section = "form"): array
    {
        $msg = [];
        $required = (string) content($page, $section . '.err_required');
        $email = (string) content($page, $section . '.err_email');
        $phone = (string) content($page, $section . '.err_phone');
        $max = (string) content($page, $section . '.err_max');
        $choice = (string) content($page, $section . '.err_choice');
        $number = (string) content($page, $section . '.err_number');
        foreach ($fields as $f) {
            $msg[$f . '.required'] = $required;
            $msg[$f . '.email'] = $email;
            $msg[$f . '.phone'] = $phone;
            $msg[$f . '.max'] = $max;
            $msg[$f . '.in'] = $choice;
            $msg[$f . '.numeric'] = $number;
            $msg[$f . '.between'] = $number;
            $msg[$f . '.min'] = $number;
        }
        return array_filter($msg, static fn(string $m): bool => trim($m) !== '');
    }

    /** Render a stored answer: HTML from the rich-text editor, or plain text (newlines kept) otherwise. */
    public static function answerHtml(string $answer): string
    {
        if (preg_match('/<\/?[a-z][^>]*>/i', $answer)) {
            return rich($answer);
        }
        return nl2p($answer);
    }

    /** The (editable) menu label of a page, used for breadcrumbs: the owner can rename "Programs" in Admin > Menus. */
    public static function navLabel(string $slug, string $fallback = ''): string
    {
        $label = trim((string) (SitePages::get($slug)['nav_label'] ?? ''));
        return $label !== '' ? $label : ($fallback !== '' ? $fallback : $slug);
    }

    /** Non-empty trimmed lines of a multi-line textarea value. @return list<string> */
    public static function lines(?string $text): array
    {
        $out = [];
        foreach (preg_split('/\R/u', (string) $text) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return $out;
    }

    /** First URL of a button pair: the registry/edited link, or $fallback (a stored link such as the donate_url setting). */
    public static function href(string $page, string $key, string $fallback = ''): string
    {
        $stored = trim((string) content($page, $key));
        return $stored !== '' ? link_url($stored) : link_url($fallback);
    }

    /** YouTube video id from a watch/short/embed URL, or '' (only YouTube is supported for the video facade). */
    public static function youtubeId(?string $url): string
    {
        $u = trim((string) $url);
        if ($u === '') {
            return '';
        }
        if (preg_match('~^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $u, $m)) {
            return $m[1];
        }
        return '';
    }
}
