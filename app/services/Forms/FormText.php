<?php
declare(strict_types=1);

namespace App\Services\Forms;

/**
 * Visitor-facing wording of the forms module. ALL of it lives in the page registry entry `forms` (app/config/pages/forms.php), so the owner edits it
 * in Admin > Pages > Forms; this class only reads it and fills {placeholders}.
 *
 *   FormText::get('errors.required', ['label' => 'Email'])     // "Email is required."
 *   FormText::fill('Step {current} of {total}', ['current' => 2, 'total' => 3])
 *
 * Values are substituted exactly once (no recursion), so a visitor's text containing "{label}" is never expanded again.
 */
final class FormText
{
    /** Registry page that holds the wording. */
    public const PAGE = 'forms';

    /** @param array<string,scalar|null> $vars */
    public static function get(string $key, array $vars = []): string
    {
        $text = '';
        try {
            $v = content(self::PAGE, $key);
            $text = is_string($v) ? $v : '';
        } catch (\Throwable) {
            $text = '';
        }
        if ($text === '') {
            $text = $key;
        }
        return self::fill($text, $vars);
    }

    /** @param array<string,scalar|null> $vars */
    public static function fill(string $text, array $vars): string
    {
        if ($vars === [] || !str_contains($text, '{')) {
            return $text;
        }
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{' . $k . '}'] = (string) $v;
        }
        return strtr($text, $map);          // strtr() replaces in one pass and never rescans replaced text
    }
}
