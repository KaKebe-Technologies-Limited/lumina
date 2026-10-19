<?php
declare(strict_types=1);

namespace App\Services\Forms;

/**
 * The [form slug="my-form"] SHORTCODE for custom page bodies. Contract: docs/FORMS.md section 14.
 *
 *   echo FormShortcode::expand(rich($page['body']));      // after sanitising: the shortcode survives Sanitizer::html as plain text
 *
 * A shortcode that stands alone in a paragraph replaces the whole <p>. An unknown, archived or draft form renders NOTHING; a form that is closed (by status
 * or by date), full or not open yet shows the editable notice (decided by `web/forms/_embed`). At most 5 per page. The markup comes
 * from the partial `web/forms/_embed` (PUBLIC agent): variables `form` (hydrated form) and `availability`. The shortcode never accepts other attributes.
 */
final class FormShortcode
{
    public const MAX_PER_PAGE = 5;

    public static function expand(string $html): string
    {
        if (!str_contains($html, '[form')) {
            return $html;
        }
        $quote = '(?:"|&quot;|&#0?34;|&#x22;|\'|&#0?39;|&#x27;|&ldquo;|&rdquo;|&#8220;|&#8221;|\x{201C}|\x{201D})';
        $re = '~(<p>\s*)?\[form\s+slug\s*=\s*' . $quote . '([a-z0-9]+(?:-[a-z0-9]+)*)' . $quote . '\s*\](\s*</p>)?~iu';
        $n = 0;
        $out = preg_replace_callback($re, static function (array $m) use (&$n): string {
            $hasP = ($m[1] ?? '') !== '' && ($m[3] ?? '') !== '';
            $keepOpen = ($m[1] ?? '') !== '' && !$hasP ? $m[1] : '';
            $keepClose = ($m[3] ?? '') !== '' && !$hasP ? $m[3] : '';
            if (++$n > self::MAX_PER_PAGE) {
                return $keepOpen . $keepClose;
            }
            $html = self::render((string) $m[2]);
            return $keepOpen . $html . $keepClose;
        }, $html);
        return $out ?? $html;
    }

    /** The embed HTML for a slug, or '' when the form may not be shown. */
    public static function render(string $slug): string
    {
        try {
            $form = FormService::findBySlug($slug);
            if ($form === null) {
                return '';
            }
            $avail = FormService::availability($form);
            if (!$avail['visible']) {                 // draft and archived forms are invisible; closed (by status or date), full and not-yet-open ones show their notice
                return '';
            }
            return partial('web/forms/_embed', ['form' => $form, 'availability' => $avail]);
        } catch (\Throwable) {
            return '';
        }
    }
}
