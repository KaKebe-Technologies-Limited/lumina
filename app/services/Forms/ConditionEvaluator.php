<?php
declare(strict_types=1);

namespace App\Services\Forms;

/**
 * CONDITIONAL LOGIC. The server (this class) and the browser (assets/js/forms.js, `evaluateLogic()`) implement the SAME rules; the spec is
 * docs/FORMS.md section 5 and bin/selftest-forms.php holds the truth table both must match.
 *
 * logic = {"match":"all"|"any","rules":[{"field":"<field id>","op":"equals|not_equals|contains|filled|empty|gt|lt","value":"text"}]}   (1 or 2 rules)
 * A field or a page with no logic (null) is always visible.
 *
 * ANSWER VALUES used for comparisons ("normalised values"):
 *   - text-like fields (text, textarea, email, phone, number, date, time, select, radio, yes_no, rating): a string ("" = nothing entered). yes_no is "yes"/"no",
 *     rating "1".."5", select/radio the option VALUE (or "__other"), number the typed number, date "Y-m-d", time "HH:MM".
 *   - checkboxes: a list of option values (empty list = nothing ticked); consent: "yes" when ticked, "" otherwise; file: a list of file names.
 *   - null = unknown / the field does not exist / the field is hidden -> treated exactly like an empty answer.
 *
 * OPERATORS (compare = trim + case-insensitive, Unicode aware):
 *   filled      : the value is not empty (string with a non-space character, or a list with at least one item). "no" of a yes/no IS filled.
 *   empty       : not filled.
 *   equals      : string: equal after compare (numbers are compared as numbers when both sides are numeric: "5" equals "5.0"); list: exactly one item and it equals.
 *                 An empty value equals the empty string.
 *   not_equals  : not equals.
 *   contains    : string: the rule value (non-empty) occurs inside the text; list: some item equals the rule value.
 *   gt / lt     : strings only: both numeric -> numeric comparison; else both valid dates (YYYY-MM-DD) or both times (HH:MM) -> string comparison; anything else is false.
 *
 * HIDDEN-FIELD SEMANTICS (resolve()): a rule evaluates the EFFECTIVE value of the field it refers to. A field that is hidden (its own logic fails, or
 * its page is hidden) has no value, so rules that refer to it see an empty answer. A hidden field is never required, never validated and its answer
 * is discarded. A page is visible when its own logic passes; the fields of a hidden page are hidden. A dependency cycle cannot be published (the checklist
 * refuses it); if one exists anyway, the fields are evaluated in document order, results are memoised, and the reference that closes the circle sees a
 * hidden (empty) field. The browser uses the identical algorithm.
 */
final class ConditionEvaluator
{
    public const OPS = FormDefinition::OPS;

    /** Does one rule hold for this normalised value? */
    public static function ruleMatches(array $rule, string|array|null $value): bool
    {
        $op = (string) ($rule['op'] ?? 'equals');
        $expected = (string) ($rule['value'] ?? '');
        switch ($op) {
            case 'filled':
                return self::isFilled($value);
            case 'empty':
                return !self::isFilled($value);
            case 'equals':
                return self::equals($value, $expected);
            case 'not_equals':
                return !self::equals($value, $expected);
            case 'contains':
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if (self::fold((string) $item) === self::fold($expected)) {
                            return true;
                        }
                    }
                    return false;
                }
                return $value !== null && trim($expected) !== '' && mb_stripos((string) $value, trim($expected)) !== false;
            case 'gt':
            case 'lt':
                if ($value === null || is_array($value)) {
                    return false;
                }
                $a = trim($value);
                $b = trim($expected);
                if (self::isNumber($a) && self::isNumber($b)) {
                    return $op === 'gt' ? (float) $a > (float) $b : (float) $a < (float) $b;
                }
                if ((self::isDate($a) && self::isDate($b)) || (self::isTime($a) && self::isTime($b))) {
                    return $op === 'gt' ? strcmp($a, $b) > 0 : strcmp($a, $b) < 0;
                }
                return false;
        }
        return false;
    }

    /**
     * Does a logic object pass? $valueOf(fieldId) returns the EFFECTIVE normalised value (null for unknown / hidden fields).
     * @param callable(string):(string|array|null) $valueOf
     */
    public static function passes(?array $logic, callable $valueOf): bool
    {
        if ($logic === null || (array) ($logic['rules'] ?? []) === []) {
            return true;
        }
        $any = (($logic['match'] ?? 'all') === 'any');
        foreach ((array) $logic['rules'] as $rule) {
            $hit = self::ruleMatches((array) $rule, $valueOf((string) ($rule['field'] ?? '')));
            if ($any && $hit) {
                return true;
            }
            if (!$any && !$hit) {
                return false;
            }
        }
        return !$any;
    }

    /**
     * Visibility of every page and field for a set of RAW answers.
     *
     * @param array<string,string|array|null> $raw field id => normalised value as entered (hidden fields may carry values; they are ignored)
     * @return array{fields:array<string,bool>,pages:array<string,bool>,values:array<string,string|array|null>}
     *   fields / pages : id => visible; values : id => effective value (null when hidden or unanswered input of a layout type)
     */
    public static function resolve(array $definition, array $raw): array
    {
        $fields = [];
        $pageOf = [];
        $pages = [];
        foreach ((array) ($definition['pages'] ?? []) as $page) {
            $pages[(string) $page['id']] = $page;
            foreach ((array) $page['fields'] as $f) {
                $fields[(string) $f['id']] = $f;
                $pageOf[(string) $f['id']] = (string) $page['id'];
            }
        }
        $fieldVisible = [];
        $pageVisible = [];
        $busy = [];

        $valueOf = null;
        $isFieldVisible = null;
        $isPageVisible = null;

        $valueOf = static function (string $id) use (&$fields, &$raw, &$isFieldVisible): string|array|null {
            if (!isset($fields[$id]) || !FormDefinition::isInput((string) $fields[$id]['type'])) {
                return null;
            }
            return $isFieldVisible($id) ? ($raw[$id] ?? null) : null;
        };
        $isPageVisible = static function (string $pid) use (&$pages, &$pageVisible, &$valueOf, &$busy): bool {
            if (isset($pageVisible[$pid])) {
                return $pageVisible[$pid];
            }
            $key = 'p:' . $pid;
            if (isset($busy[$key])) {
                return false;                                   // cycle
            }
            $busy[$key] = true;
            $ok = self::passes($pages[$pid]['logic'] ?? null, $valueOf);
            unset($busy[$key]);
            return $pageVisible[$pid] = $ok;
        };
        $isFieldVisible = static function (string $id) use (&$fields, &$pageOf, &$fieldVisible, &$valueOf, &$isPageVisible, &$busy): bool {
            if (isset($fieldVisible[$id])) {
                return $fieldVisible[$id];
            }
            $key = 'f:' . $id;
            if (isset($busy[$key])) {
                return false;                                   // cycle
            }
            $busy[$key] = true;
            $ok = $isPageVisible($pageOf[$id]) && self::passes($fields[$id]['logic'] ?? null, $valueOf);
            unset($busy[$key]);
            return $fieldVisible[$id] = $ok;
        };

        $outFields = [];
        $outValues = [];
        foreach (array_keys($fields) as $id) {
            $outFields[$id] = $isFieldVisible($id);
            $outValues[$id] = $outFields[$id] && FormDefinition::isInput((string) $fields[$id]['type']) ? ($raw[$id] ?? null) : null;
        }
        $outPages = [];
        foreach (array_keys($pages) as $pid) {
            $outPages[$pid] = $isPageVisible($pid);
        }
        return ['fields' => $outFields, 'pages' => $outPages, 'values' => $outValues];
    }

    // ------------------------------------------------------------------ helpers (mirrored 1:1 in forms.js)

    public static function isFilled(string|array|null $value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                if (trim((string) $item) !== '') {
                    return true;
                }
            }
            return false;
        }
        return trim($value) !== '';
    }

    private static function equals(string|array|null $value, string $expected): bool
    {
        if (is_array($value)) {
            $items = array_values(array_filter(array_map('strval', $value), static fn(string $x): bool => trim($x) !== ''));
            if ($items === []) {
                return trim($expected) === '';
            }
            return count($items) === 1 && self::fold($items[0]) === self::fold($expected);
        }
        $v = $value === null ? '' : trim($value);
        $e = trim($expected);
        if (self::isNumber($v) && self::isNumber($e)) {
            return (float) $v === (float) $e;
        }
        return self::fold($v) === self::fold($e);
    }

    private static function fold(string $s): string
    {
        return mb_strtolower(trim($s));
    }

    private static function isNumber(string $s): bool
    {
        return preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $s) === 1;
    }

    private static function isDate(string $s): bool
    {
        return preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $s) === 1;
    }

    private static function isTime(string $s): bool
    {
        return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $s) === 1;
    }
}
