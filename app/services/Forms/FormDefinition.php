<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\Config;

/**
 * THE FORM DEFINITION (forms.definition, JSON version 1). Contract and examples: docs/FORMS.md section 3.
 *
 *   {"version":1,"pages":[{"id":"pg3k9x2","title":"About you","description":"","logic":null,
 *      "fields":[{"id":"f8k2m9xa","key":"full_name","type":"text","label":"Full name","help":"","placeholder":"","required":true,"default":"",
 *                 "width":"full","validation":{"min_length":2,"max_length":120,"pattern":"letters"},"logic":null}, ...]}]}
 *
 * Everything that reaches the database or a visitor goes through normalise(): it is the ONLY gate (the builder, the template picker, duplicate
 * and the import of old data all use it). It drops unknown keys, strips tags from every text (labels are plain text), enforces the caps of
 * config('form_builder.*'), repairs missing or duplicated ids / keys / option values and never throws. Hard failures (too many fields, an
 * unknown field type, an unknown condition operator, a document that is not an object) come back in `errors`; everything else is repaired and
 * reported in `warnings`. Whether a form is READY TO GO LIVE is a separate question answered by publishChecklist().
 *
 * Ids are stable: a field keeps its id for life (answers are stored by id, with a snapshot of label + type), so editing a published form
 * never breaks old answers. A new id is `f` + 8 random [a-z0-9]; the builder creates ids in the browser with the same pattern.
 *
 * ReDoS: the definition cannot carry a regular expression. `validation.pattern` is the NAME of one of the presets below; the expressions are fixed,
 * anchored and linear. The same expressions are used by assets/js/forms.js (see clientPatterns()).
 */
final class FormDefinition
{
    public const VERSION = 1;

    /** Value stored when a visitor picks the "Other: please specify" choice of a select / radio / checkbox field. */
    public const OTHER = '__other';

    public const WIDTHS = ['full', 'half', 'third'];

    /** Condition operators (docs/FORMS.md section 5). */
    public const OPS = ['equals', 'not_equals', 'contains', 'filled', 'empty', 'gt', 'lt'];

    /** Hard limits of strings that are not configurable (the configurable ones are config form_builder.*). */
    public const MAX_KEY = 40;
    public const MAX_OPTION_VALUE = 60;
    public const MAX_DEFAULT = 500;
    public const MAX_PLACEHOLDER = 160;
    public const MAX_RULE_VALUE = 200;
    public const MAX_OTHER_TEXT = 200;

    /** Id pattern shared with the browser code: first char a letter, then lower-case letters and digits (2..24 chars). */
    public const ID_PATTERN = '/^[a-z][a-z0-9]{1,23}$/';
    /** Names of Object.prototype members: never an id or an option value (plain JavaScript objects keyed by them would answer for every form). Lower-cased compare. */
    private const RESERVED_NAMES = ['constructor', 'prototype', '__proto__', 'tostring', 'valueof', 'hasownproperty', 'isprototypeof', 'propertyisenumerable', 'tolocalestring', '__definegetter__', '__definesetter__', '__lookupgetter__', '__lookupsetter__'];

    public static function isReservedName(string $s): bool
    {
        return in_array(strtolower($s), self::RESERVED_NAMES, true);
    }
    public const KEY_PATTERN = '/^[a-z][a-z0-9_]{0,39}$/';

    /** Keys that would collide with the e-mail placeholders {form_title} {site} {date} {reference} {link} {submission_id}. */
    public const RESERVED_KEYS = ['form_title', 'site', 'date', 'reference', 'link', 'submission_id'];

    /**
     * Field types. input = collects an answer; layout types never do.
     * flags: placeholder, default, length (validation.min_length / max_length), pattern, range (validation.min / max as numbers),
     *        date_range (strings Y-m-d or "today"), time_range (HH:MM), options, other (allow_other), count (validation.min / max = number of picks),
     *        file (file rules), text (the `text` property: paragraph).
     * @var array<string,array<string,mixed>>
     */
    public const TYPES = [
        'text'       => ['label' => 'Short text',      'icon' => 'edit',         'input' => true,  'group' => 'text',    'flags' => ['placeholder', 'default', 'length', 'pattern']],
        'textarea'   => ['label' => 'Long text',       'icon' => 'file-text',    'input' => true,  'group' => 'text',    'flags' => ['placeholder', 'default', 'length', 'pattern']],
        'email'      => ['label' => 'E-mail address',  'icon' => 'mail',         'input' => true,  'group' => 'text',    'flags' => ['placeholder', 'default']],
        'phone'      => ['label' => 'Phone number',    'icon' => 'phone',        'input' => true,  'group' => 'text',    'flags' => ['placeholder', 'default']],
        'number'     => ['label' => 'Number',          'icon' => 'bar-chart',    'input' => true,  'group' => 'text',    'flags' => ['placeholder', 'default', 'range']],
        'date'       => ['label' => 'Date',            'icon' => 'calendar',     'input' => true,  'group' => 'text',    'flags' => ['default', 'date_range']],
        'time'       => ['label' => 'Time',            'icon' => 'clock',        'input' => true,  'group' => 'text',    'flags' => ['default', 'time_range']],
        'select'     => ['label' => 'Dropdown',        'icon' => 'chevron-down', 'input' => true,  'group' => 'choice',  'flags' => ['placeholder', 'default', 'options', 'other']],
        'radio'      => ['label' => 'Single choice',   'icon' => 'circle',       'input' => true,  'group' => 'choice',  'flags' => ['default', 'options', 'other']],
        'checkboxes' => ['label' => 'Multiple choice', 'icon' => 'check-circle', 'input' => true,  'group' => 'choice',  'flags' => ['default', 'options', 'other', 'count']],
        'yes_no'     => ['label' => 'Yes / No',        'icon' => 'check',        'input' => true,  'group' => 'choice',  'flags' => ['default']],
        'rating'     => ['label' => 'Rating (1-5)',    'icon' => 'star',         'input' => true,  'group' => 'choice',  'flags' => []],
        'file'       => ['label' => 'File upload',     'icon' => 'upload',       'input' => true,  'group' => 'special', 'flags' => ['file']],
        'consent'    => ['label' => 'Consent box',     'icon' => 'shield',       'input' => true,  'group' => 'special', 'flags' => []],
        'heading'    => ['label' => 'Heading',         'icon' => 'menu',         'input' => false, 'group' => 'layout',  'flags' => []],
        'paragraph'  => ['label' => 'Help text',       'icon' => 'info',         'input' => false, 'group' => 'layout',  'flags' => ['text']],
        'divider'    => ['label' => 'Divider',         'icon' => 'minus',        'input' => false, 'group' => 'layout',  'flags' => []],
    ];

    /**
     * Fixed validation presets (name => definition). regex is applied (PCRE /u in PHP, the u flag in JavaScript) to the value after `prepare`:
     *   none = as typed (trimmed) | strip_sep = spaces, hyphens, dots and brackets removed | upper = spaces removed and upper-cased.
     * negate = the value must NOT match. These expressions are anchored and contain no nested quantifiers (linear time).
     * @var array<string,array{label:string,hint:string,regex:string,prepare:string,negate:bool,flags?:string}>  (flags: 'i' = case-insensitive, so "Https://" / "HTTP://" behave like "https://")
     */
    public const PATTERNS = [
        'letters'        => ['label' => 'Letters only (names)',                   'hint' => 'Use letters, spaces, hyphens and apostrophes only.', 'regex' => "^[\\p{L}\\p{M}][\\p{L}\\p{M} '.\\-]*$",                   'prepare' => 'none',      'negate' => false],
        'digits'         => ['label' => 'Digits only',                            'hint' => 'Use digits only.',                                  'regex' => '^[0-9]+$',                                                   'prepare' => 'none',      'negate' => false],
        'letters_digits' => ['label' => 'Letters and digits',                     'hint' => 'Use letters, digits, spaces and hyphens only.',     'regex' => '^[\\p{L}\\p{N}][\\p{L}\\p{N} \\-]*$',                          'prepare' => 'none',      'negate' => false],
        'ug_phone'       => ['label' => 'Ugandan phone number',                   'hint' => 'Enter a Ugandan number such as 0700 123456 or +256 700 123456.', 'regex' => '^(?:\\+?256|0)[347][0-9]{8}$',                       'prepare' => 'strip_sep', 'negate' => false],
        'url'            => ['label' => 'Web address (https://...)',              'hint' => 'Enter a web address starting with http:// or https://.', 'regex' => '^https?://[^\\s/?#]+\\.[^\\s/?#]{2,}(?:[/?#]\\S*)?$',   'prepare' => 'none',      'negate' => false, 'flags' => 'i'],
        'nin'            => ['label' => 'National ID number (NIN-like format)',   'hint' => 'Enter 14 characters: CM or CF, 8 digits, then 4 letters.', 'regex' => '^C[MF][0-9]{8}[A-Z]{4}$',                                  'prepare' => 'upper',     'negate' => false],
        'year'           => ['label' => 'Year (for example 2024)',                'hint' => 'Enter a 4-digit year.',                              'regex' => '^(?:19|20)[0-9]{2}$',                                       'prepare' => 'none',      'negate' => false],
        'no_links'       => ['label' => 'No web links (stops spam)',              'hint' => 'Please do not include web links.',                   'regex' => '(?:https?://|www\\.)',                                       'prepare' => 'none',      'negate' => true,  'flags' => 'i'],
    ];

    /** Allowed upload types: extension => label. The subset a form may accept is config form_builder.file_types. */
    public const FILE_TYPES = [
        'pdf'  => 'PDF',
        'doc'  => 'Word (DOC)',
        'docx' => 'Word (DOCX)',
        'xls'  => 'Excel (XLS)',
        'xlsx' => 'Excel (XLSX)',
        'jpg'  => 'Photo (JPG)',
        'png'  => 'Image (PNG)',
    ];

    // ------------------------------------------------------------------ construction

    /** A blank definition: one page, no fields. */
    public static function blank(): array
    {
        return ['version' => self::VERSION, 'pages' => [['id' => self::newId('p'), 'title' => '', 'description' => '', 'logic' => null, 'fields' => []]]];
    }

    /** New random id: prefix + 8 characters [a-z0-9] (pages use prefix `p` and 6 characters). */
    public static function newId(string $prefix = 'f'): string
    {
        $len = $prefix === 'p' ? 6 : 8;
        $alphabet = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $id = $prefix;
        for ($i = 0; $i < $len; $i++) {
            $id .= $alphabet[random_int(0, 35)];
        }
        return $id;
    }

    public static function encode(array $definition): string
    {
        return (string) json_encode($definition, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /** Decode + normalise stored JSON. Invalid or empty JSON gives a blank definition (never throws). */
    public static function decode(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return self::blank();
        }
        $res = self::normalise($json);
        return $res['definition'] ?? self::blank();
    }

    // ------------------------------------------------------------------ introspection helpers

    /** Does this type collect an answer? */
    public static function isInput(string $type): bool
    {
        return (bool) (self::TYPES[$type]['input'] ?? false);
    }

    /** @return bool does the type have the given flag (see TYPES) */
    public static function hasFlag(string $type, string $flag): bool
    {
        return in_array($flag, (array) (self::TYPES[$type]['flags'] ?? []), true);
    }

    /** Is a choice type (select / radio / checkboxes)? */
    public static function isChoice(string $type): bool
    {
        return in_array($type, ['select', 'radio', 'checkboxes'], true);
    }

    /**
     * Every field in document order with its position: [['page' => index, 'page_id', 'index' => index in page, 'field' => array], ...].
     * @return list<array{page:int,page_id:string,index:int,field:array<string,mixed>}>
     */
    public static function flat(array $definition): array
    {
        $out = [];
        foreach ((array) ($definition['pages'] ?? []) as $pi => $page) {
            foreach ((array) ($page['fields'] ?? []) as $fi => $field) {
                $out[] = ['page' => (int) $pi, 'page_id' => (string) ($page['id'] ?? ''), 'index' => (int) $fi, 'field' => $field];
            }
        }
        return $out;
    }

    /** @return list<array<string,mixed>> every field (flat, document order) */
    public static function fields(array $definition): array
    {
        return array_column(self::flat($definition), 'field');
    }

    /** @return array<string,array<string,mixed>> field id => field */
    public static function fieldMap(array $definition): array
    {
        $map = [];
        foreach (self::fields($definition) as $f) {
            $map[(string) $f['id']] = $f;
        }
        return $map;
    }

    /** @return list<array<string,mixed>> only the fields that collect an answer */
    public static function inputFields(array $definition): array
    {
        return array_values(array_filter(self::fields($definition), static fn(array $f): bool => self::isInput((string) $f['type'])));
    }

    /** Page index of every field id: id => ['page' => int, 'index' => int (position in the whole form)]. */
    public static function positions(array $definition): array
    {
        $out = [];
        $n = 0;
        foreach (self::flat($definition) as $row) {
            $out[(string) $row['field']['id']] = ['page' => $row['page'], 'index' => $n++];
        }
        return $out;
    }

    /** Effective maximum length of a text answer. */
    public static function maxLength(array $field): int
    {
        $cap = (int) config('form_builder.max_answer_chars', 8000);
        $default = match ((string) ($field['type'] ?? 'text')) {
            'textarea' => 5000,
            'email'    => 190,
            default    => 500,
        };
        $max = $field['validation']['max_length'] ?? null;
        return $max === null ? min($default, $cap) : max(1, min((int) $max, $cap));
    }

    // ------------------------------------------------------------------ the catalogue the builder loads

    /** Everything the builder needs to render its palette and settings panel (GET /admin/api/forms/catalog). */
    public static function catalog(): array
    {
        $types = [];
        foreach (self::TYPES as $key => $t) {
            $types[] = ['type' => $key, 'label' => $t['label'], 'icon' => $t['icon'], 'input' => $t['input'], 'group' => $t['group'], 'flags' => $t['flags']];
        }
        $patterns = [];
        foreach (self::PATTERNS as $key => $p) {
            $patterns[] = ['name' => $key, 'label' => $p['label'], 'hint' => self::patternHint($key)];
        }
        $fileTypes = [];
        foreach (self::allowedFileTypes() as $ext) {
            $fileTypes[] = ['ext' => $ext, 'label' => self::FILE_TYPES[$ext] ?? strtoupper($ext)];
        }
        return [
            'version'   => self::VERSION,
            'types'     => $types,
            'widths'    => self::WIDTHS,
            'ops'       => [
                ['op' => 'equals', 'label' => 'is'], ['op' => 'not_equals', 'label' => 'is not'], ['op' => 'contains', 'label' => 'contains'],
                ['op' => 'filled', 'label' => 'is filled in'], ['op' => 'empty', 'label' => 'is empty'],
                ['op' => 'gt', 'label' => 'is greater than'], ['op' => 'lt', 'label' => 'is less than'],
            ],
            'patterns'  => $patterns,
            'file_types' => $fileTypes,
            'other'     => self::OTHER,
            'limits'    => [
                'pages' => (int) config('form_builder.max_pages'), 'fields' => (int) config('form_builder.max_fields'), 'options' => (int) config('form_builder.max_options'),
                'rules' => (int) config('form_builder.max_rules'), 'label' => (int) config('form_builder.max_label'), 'help' => (int) config('form_builder.max_help'),
                'text' => (int) config('form_builder.max_text'), 'option_label' => (int) config('form_builder.max_option_label'), 'title' => (int) config('form_builder.max_title'),
                'file_max_mb' => FormFileStore::maxMb(), 'file_max_files' => (int) config('form_builder.file_max_files'),
                'key' => self::MAX_KEY, 'default' => self::MAX_DEFAULT, 'placeholder' => self::MAX_PLACEHOLDER, 'rule_value' => self::MAX_RULE_VALUE,
            ],
            'id_pattern' => trim(self::ID_PATTERN, '/'),
            'key_pattern' => trim(self::KEY_PATTERN, '/'),
        ];
    }

    /** Presets for the browser renderer: name => {regex, prepare, negate, hint}. The regex source is for `new RegExp(source, 'u')`. */
    public static function clientPatterns(): array
    {
        $out = [];
        foreach (self::PATTERNS as $key => $p) {
            $out[$key] = ['regex' => $p['regex'], 'prepare' => $p['prepare'], 'negate' => $p['negate'], 'flags' => (string) ($p['flags'] ?? ''), 'hint' => self::patternHint($key)];
        }
        return $out;
    }

    /** The explanation of a format preset, editable in Admin > Pages > Forms (pattern_hints.<name>); the built-in sentence is the default. */
    public static function patternHint(string $name): string
    {
        $default = self::PATTERNS[$name]['hint'] ?? '';
        try {
            $v = content('forms', 'pattern_hints.' . $name);
            if (is_string($v) && trim($v) !== '') {
                return $v;
            }
        } catch (\Throwable) {
        }
        return $default;
    }

    /** @return list<string> the file extensions a form may accept on this site */
    public static function allowedFileTypes(): array
    {
        $cfg = (array) config('form_builder.file_types', array_keys(self::FILE_TYPES));
        return array_values(array_filter(array_keys(self::FILE_TYPES), static fn(string $e): bool => in_array($e, $cfg, true)));
    }

    // ------------------------------------------------------------------ patterns and phone numbers

    /** Does $value satisfy the preset? Unknown preset names pass (they are dropped by normalise()). */
    public static function matchPattern(string $name, string $value): bool
    {
        $p = self::PATTERNS[$name] ?? null;
        if ($p === null) {
            return true;
        }
        $v = match ($p['prepare']) {
            'strip_sep' => (string) preg_replace('/[\s().\-]+/', '', $value),
            'upper'     => strtoupper((string) preg_replace('/\s+/', '', $value)),
            default     => trim($value),
        };
        $hit = @preg_match('~' . str_replace('~', '\\~', $p['regex']) . '~u' . (string) ($p['flags'] ?? ''), $v) === 1;
        return $p['negate'] ? !$hit : $hit;
    }

    /**
     * Normalise a phone number to +E.164 style, Uganda-friendly. Accepts "0700 123456", "0700-123-456", "+256 700 123 456", "256700123456",
     * "00256700123456", "(0414) 123456" and other countries' numbers that start with "+" or "00". Returns null when it cannot be a number.
     */
    public static function normalisePhone(string $raw): ?string
    {
        $s = trim($raw);
        if ($s === '' || strlen($s) > 40 || preg_match('/^[+0-9\s().\-]+$/', $s) !== 1) {
            return null;
        }
        $plus = str_starts_with($s, '+');
        $digits = (string) preg_replace('/\D+/', '', $s);
        if ($digits === '') {
            return null;
        }
        if (!$plus && str_starts_with($digits, '00')) {
            $plus = true;
            $digits = substr($digits, 2);
        }
        if ($plus) {
            return preg_match('/^[1-9][0-9]{6,14}$/', $digits) === 1 ? '+' . $digits : null;
        }
        if (str_starts_with($digits, '256') && strlen($digits) === 12) {
            return '+' . $digits;                                    // 256700123456
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '+256' . substr($digits, 1);                      // 0700123456
        }
        return null;                                                  // ambiguous: ask for the country code
    }

    // ------------------------------------------------------------------ normalisation

    /**
     * Validate + normalise a definition (array or JSON string).
     *
     * @return array{ok:bool,definition:?array,errors:array<string,string>,warnings:list<string>}
     *   errors   : path => message, only for what cannot be repaired. ok = (errors === []) and definition is the cleaned document (null when not ok).
     *   warnings : human sentences about repairs ("2 duplicate field ids were replaced").
     */
    public static function normalise(mixed $raw): array
    {
        $errors = [];
        $warnings = [];
        $fail = static fn(string $path, string $msg): array => ['ok' => false, 'definition' => null, 'errors' => [$path => $msg], 'warnings' => []];

        if (is_string($raw)) {
            if (strlen($raw) > (int) config('form_builder.max_definition_kb', 512) * 1024) {
                return $fail('definition', 'This form is too large.');
            }
            $decoded = json_decode($raw, true, 24);
            if (!is_array($decoded)) {
                return $fail('definition', 'The form definition is not valid JSON.');
            }
            $raw = $decoded;
        }
        if (!is_array($raw)) {
            return $fail('definition', 'The form definition must be an object.');
        }
        $pagesRaw = $raw['pages'] ?? null;
        if (!is_array($pagesRaw)) {
            return $fail('pages', 'The form has no pages.');
        }
        $pagesRaw = array_values($pagesRaw);
        if ($pagesRaw === []) {
            $pagesRaw = [[]];
        }
        $maxPages = (int) config('form_builder.max_pages', 12);
        $maxFields = (int) config('form_builder.max_fields', 150);
        if (count($pagesRaw) > $maxPages) {
            return $fail('pages', 'A form can have at most ' . $maxPages . ' pages.');
        }
        $total = 0;
        foreach ($pagesRaw as $p) {
            $total += is_array($p) && is_array($p['fields'] ?? null) ? count($p['fields']) : 0;
        }
        if ($total > $maxFields) {
            return $fail('pages', 'A form can have at most ' . $maxFields . ' fields (this one has ' . $total . ').');
        }

        $usedIds = [];
        $usedKeys = [];
        $repairedIds = 0;
        $repairedKeys = 0;
        $repairedValues = 0;
        $pages = [];

        foreach ($pagesRaw as $pi => $p) {
            $p = is_array($p) ? $p : [];
            $pid = self::cleanId($p['id'] ?? null, $usedIds, 'p', $repairedIds);
            $page = [
                'id'          => $pid,
                'title'       => self::plain($p['title'] ?? '', (int) config('form_builder.max_label', 200)),
                'description' => self::plain($p['description'] ?? '', (int) config('form_builder.max_help', 600)),
                'logic'       => null,
                'fields'      => [],
            ];
            $logic = self::normaliseLogic($p['logic'] ?? null, "pages.{$pi}.logic", $errors);
            $page['logic'] = $logic;
            $fieldsRaw = is_array($p['fields'] ?? null) ? array_values($p['fields']) : [];
            foreach ($fieldsRaw as $fi => $f) {
                if (!is_array($f)) {
                    continue;
                }
                $path = "pages.{$pi}.fields.{$fi}";
                $type = (string) ($f['type'] ?? '');
                if (!isset(self::TYPES[$type])) {
                    $errors[$path . '.type'] = 'Unknown field type.';
                    continue;
                }
                $field = self::normaliseField($f, $type, $usedIds, $usedKeys, $path, $errors, $repairedIds, $repairedKeys, $repairedValues);
                $page['fields'][] = $field;
            }
            $pages[] = $page;
        }

        if ($repairedIds > 0) {
            $warnings[] = $repairedIds . ' missing or duplicate id' . ($repairedIds === 1 ? ' was' : 's were') . ' replaced.';
        }
        if ($repairedKeys > 0) {
            $warnings[] = $repairedKeys . ' missing or duplicate field key' . ($repairedKeys === 1 ? ' was' : 's were') . ' replaced.';
        }
        if ($repairedValues > 0) {
            $warnings[] = $repairedValues . ' option value' . ($repairedValues === 1 ? ' was' : 's were') . ' repaired.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'definition' => null, 'errors' => $errors, 'warnings' => $warnings];
        }
        $definition = ['version' => self::VERSION, 'pages' => $pages];
        if (strlen(self::encode($definition)) > (int) config('form_builder.max_definition_kb', 512) * 1024) {
            return $fail('definition', 'This form is too large.');
        }
        return ['ok' => true, 'definition' => $definition, 'errors' => [], 'warnings' => $warnings];
    }

    /**
     * Give every id of a definition a fresh value and remap the conditions (template instantiate).
     * Keys are kept. Input must already be normalised. $idMap (by reference) receives old field id => new field id so the caller can remap
     * anything else that points at a field (the settings' confirmation e-mail question).
     */
    public static function reidentify(array $definition, ?array &$idMap = null): array
    {
        $map = [];
        foreach ((array) $definition['pages'] as $page) {
            foreach ((array) $page['fields'] as $f) {
                $map[(string) $f['id']] = self::newId('f');
            }
        }
        $remap = static function (?array $logic) use ($map): ?array {
            if ($logic === null) {
                return null;
            }
            foreach ($logic['rules'] as $i => $r) {
                $logic['rules'][$i]['field'] = $map[$r['field']] ?? $r['field'];
            }
            return $logic;
        };
        $idMap = $map;
        foreach ($definition['pages'] as $pi => $page) {
            $definition['pages'][$pi]['id'] = self::newId('p');
            $definition['pages'][$pi]['logic'] = $remap($page['logic'] ?? null);
            foreach ($page['fields'] as $fi => $f) {
                $definition['pages'][$pi]['fields'][$fi]['id'] = $map[(string) $f['id']];
                $definition['pages'][$pi]['fields'][$fi]['logic'] = $remap($f['logic'] ?? null);
            }
        }
        return $definition;
    }

    /** Plain text: tags stripped, < and > removed, control characters removed, whitespace collapsed, cut to $max characters. */
    public static function plain(mixed $v, int $max, bool $multiline = false): string
    {
        if (!is_string($v) && !is_int($v) && !is_float($v)) {
            return '';
        }
        $s = (string) $v;
        $s = strip_tags($s);
        $s = str_replace(['<', '>'], '', $s);
        $s = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s);
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = (string) mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        }
        if ($multiline) {
            $s = str_replace(["\r\n", "\r"], "\n", $s);
            $s = (string) preg_replace('/[ \t]+/u', ' ', $s);
            $s = (string) preg_replace("/\n{3,}/", "\n\n", $s);
        } else {
            $s = (string) preg_replace('/\s+/u', ' ', $s);
        }
        return mb_substr(trim($s), 0, $max);
    }

    // ------------------------------------------------------------------ internals

    /** @param array<string,bool> $used */
    private static function cleanId(mixed $id, array &$used, string $prefix, int &$repaired): string
    {
        $id = is_string($id) ? $id : '';
        if (preg_match(self::ID_PATTERN, $id) !== 1 || isset($used[$id]) || self::isReservedName($id)) {
            if ($id !== '') {
                $repaired++;
            }
            do {
                $id = self::newId($prefix);
            } while (isset($used[$id]));
        }
        $used[$id] = true;
        return $id;
    }

    /** @param array<string,string> $errors */
    private static function normaliseLogic(mixed $raw, string $path, array &$errors): ?array
    {
        if (!is_array($raw)) {
            return null;
        }
        $rulesRaw = is_array($raw['rules'] ?? null) ? array_values($raw['rules']) : [];
        if ($rulesRaw === []) {
            return null;
        }
        $maxRules = (int) config('form_builder.max_rules', 2);
        if (count($rulesRaw) > $maxRules) {
            $errors[$path] = 'Use at most ' . $maxRules . ' conditions.';
            return null;
        }
        $rules = [];
        foreach ($rulesRaw as $i => $r) {
            if (!is_array($r)) {
                continue;
            }
            $op = (string) ($r['op'] ?? 'equals');
            if (!in_array($op, self::OPS, true)) {
                $errors["{$path}.rules.{$i}.op"] = 'Unknown condition.';
                continue;
            }
            $field = is_string($r['field'] ?? null) && preg_match(self::ID_PATTERN, $r['field']) === 1 ? $r['field'] : '';
            $value = in_array($op, ['filled', 'empty'], true) ? '' : self::plain($r['value'] ?? '', self::MAX_RULE_VALUE);
            $rules[] = ['field' => $field, 'op' => $op, 'value' => $value];
        }
        if ($rules === []) {
            return null;
        }
        return ['match' => (($raw['match'] ?? 'all') === 'any') ? 'any' : 'all', 'rules' => $rules];
    }

    /**
     * @param array<string,bool> $usedIds
     * @param array<string,bool> $usedKeys
     * @param array<string,string> $errors
     */
    private static function normaliseField(array $f, string $type, array &$usedIds, array &$usedKeys, string $path, array &$errors, int &$repairedIds, int &$repairedKeys, int &$repairedValues): array
    {
        $spec = self::TYPES[$type];
        $isInput = (bool) $spec['input'];
        $label = self::plain($f['label'] ?? '', (int) config('form_builder.max_label', 200));
        $field = [
            'id'    => self::cleanId($f['id'] ?? null, $usedIds, 'f', $repairedIds),
            'key'   => '',
            'type'  => $type,
            'label' => $label,
            'help'  => self::plain($f['help'] ?? '', (int) config('form_builder.max_help', 600)),
            'width' => in_array($f['width'] ?? 'full', self::WIDTHS, true) ? (string) ($f['width'] ?? 'full') : 'full',
            'logic' => self::normaliseLogic($f['logic'] ?? null, $path . '.logic', $errors),
        ];
        if ($isInput) {
            $field['key'] = self::cleanKey($f['key'] ?? null, $label, $usedKeys, $repairedKeys);
            $field['required'] = !empty($f['required']) && $f['required'] !== 'false' && $f['required'] !== '0';
        } else {
            $field['required'] = false;
            $field['width'] = 'full';
        }
        if (self::hasFlag($type, 'text')) {
            $field['text'] = self::plain($f['text'] ?? '', (int) config('form_builder.max_text', 2000), true);
        }
        if (self::hasFlag($type, 'placeholder')) {
            $field['placeholder'] = self::plain($f['placeholder'] ?? '', self::MAX_PLACEHOLDER);
        }

        // ---- options
        if (self::hasFlag($type, 'options')) {
            $maxOptions = (int) config('form_builder.max_options', 200);
            $optsRaw = is_array($f['options'] ?? null) ? array_values($f['options']) : [];
            if (count($optsRaw) > $maxOptions) {
                $errors[$path . '.options'] = 'A choice field can have at most ' . $maxOptions . ' options.';
                $optsRaw = array_slice($optsRaw, 0, $maxOptions);
            }
            $options = [];
            $usedVals = [];
            foreach ($optsRaw as $o) {
                $olabel = '';
                $ovalue = '';
                if (is_string($o) || is_int($o) || is_float($o)) {
                    $olabel = self::plain($o, (int) config('form_builder.max_option_label', 120));
                } elseif (is_array($o)) {
                    $olabel = self::plain($o['label'] ?? ($o['value'] ?? ''), (int) config('form_builder.max_option_label', 120));
                    $ovalue = is_string($o['value'] ?? null) || is_int($o['value'] ?? null) ? (string) $o['value'] : '';
                }
                if ($olabel === '') {
                    continue;
                }
                $valid = $ovalue !== '' && $ovalue !== self::OTHER && preg_match('/^[A-Za-z0-9_.\-]{1,' . self::MAX_OPTION_VALUE . '}$/', $ovalue) === 1 && !isset($usedVals[$ovalue]) && !self::isReservedName($ovalue);
                if (!$valid) {
                    if ($ovalue !== '') {
                        $repairedValues++;
                    }
                    $base = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower((string) (function_exists('iconv') ? @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $olabel) : $olabel))), '-');
                    $base = substr($base !== '' ? $base : 'option', 0, 40);
                    $ovalue = self::isReservedName($base) ? $base . '-1' : $base;
                    for ($n = 2; isset($usedVals[$ovalue]); $n++) {
                        $ovalue = substr($base, 0, 36) . '-' . $n;
                    }
                }
                $usedVals[$ovalue] = true;
                $options[] = ['value' => $ovalue, 'label' => $olabel];
            }
            $field['options'] = $options;
            if (self::hasFlag($type, 'other')) {
                $field['allow_other'] = !empty($f['allow_other']) && $f['allow_other'] !== 'false' && $f['allow_other'] !== '0';
                $field['other_label'] = self::plain($f['other_label'] ?? '', (int) config('form_builder.max_option_label', 120));
            }
        }

        // ---- validation
        $v = is_array($f['validation'] ?? null) ? $f['validation'] : [];
        $val = [];
        if (self::hasFlag($type, 'length')) {
            $cap = (int) config('form_builder.max_answer_chars', 8000);
            $val['min_length'] = self::intOrNull($v['min_length'] ?? null, 0, $cap);
            $val['max_length'] = self::intOrNull($v['max_length'] ?? null, 1, $cap);
        } elseif ($type === 'email') {
            $val['max_length'] = self::intOrNull($v['max_length'] ?? null, 1, 190);
        }
        if (self::hasFlag($type, 'pattern')) {
            $pat = is_string($v['pattern'] ?? null) ? $v['pattern'] : '';
            $val['pattern'] = isset(self::PATTERNS[$pat]) ? $pat : '';
        }
        if (self::hasFlag($type, 'range')) {
            $val['min'] = self::numOrNull($v['min'] ?? null);
            $val['max'] = self::numOrNull($v['max'] ?? null);
        }
        if (self::hasFlag($type, 'count')) {
            $val['min'] = self::intOrNull($v['min'] ?? null, 0, 200);
            $val['max'] = self::intOrNull($v['max'] ?? null, 1, 200);
        }
        if (self::hasFlag($type, 'date_range')) {
            $val['min'] = self::dateBound($v['min'] ?? null);
            $val['max'] = self::dateBound($v['max'] ?? null);
        }
        if (self::hasFlag($type, 'time_range')) {
            $val['min'] = self::timeOrNull($v['min'] ?? null);
            $val['max'] = self::timeOrNull($v['max'] ?? null);
        }
        if ($val !== []) {
            $field['validation'] = $val;
        }

        // ---- file rules
        if (self::hasFlag($type, 'file')) {
            $fr = is_array($f['file'] ?? null) ? $f['file'] : [];
            $allowed = self::allowedFileTypes();
            $types = [];
            foreach ((array) ($fr['types'] ?? []) as $t) {
                $t = strtolower(trim((string) $t));
                $t = $t === 'jpeg' ? 'jpg' : $t;
                if (in_array($t, $allowed, true) && !in_array($t, $types, true)) {
                    $types[] = $t;
                }
            }
            if ($types === []) {
                $types = in_array('pdf', $allowed, true) ? ['pdf'] : array_slice($allowed, 0, 1);
            }
            $maxMb = FormFileStore::maxMb();
            $field['file'] = [
                'types'     => $types,
                'max_mb'    => max(1, min($maxMb, (int) ($fr['max_mb'] ?? 5) ?: 5)),
                'max_files' => max(1, min((int) config('form_builder.file_max_files', 5), (int) ($fr['max_files'] ?? 1) ?: 1)),
            ];
        }

        // ---- default value (last: it depends on the options)
        if (self::hasFlag($type, 'default')) {
            $field['default'] = self::cleanDefault($f['default'] ?? '', $type, $field['options'] ?? []);
        }
        return $field;
    }

    /** @param array<string,bool> $used */
    private static function cleanKey(mixed $key, string $label, array &$used, int &$repaired): string
    {
        $key = is_string($key) ? strtolower(trim($key)) : '';
        $ok = preg_match(self::KEY_PATTERN, $key) === 1 && !in_array($key, self::RESERVED_KEYS, true) && !isset($used[$key]);
        if (!$ok) {
            if ($key !== '') {
                $repaired++;
            }
            $base = trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower((string) (function_exists('iconv') ? @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $label) : $label))), '_');
            if ($base === '' || !ctype_alpha($base[0])) {
                $base = 'field' . ($base !== '' ? '_' . $base : '');
            }
            $base = substr($base, 0, self::MAX_KEY - 4);
            $base = rtrim($base, '_');
            if (in_array($base, self::RESERVED_KEYS, true)) {
                $base .= '_field';
            }
            $key = $base;
            for ($n = 2; isset($used[$key]); $n++) {
                $key = substr($base, 0, self::MAX_KEY - 4) . '_' . $n;
            }
        }
        $used[$key] = true;
        return $key;
    }

    private static function intOrNull(mixed $v, int $min, int $max): ?int
    {
        if ($v === null || $v === '' || !is_numeric($v)) {
            return null;
        }
        return max($min, min($max, (int) $v));
    }

    private static function numOrNull(mixed $v): int|float|null
    {
        if ($v === null || $v === '' || !is_numeric($v)) {
            return null;
        }
        $n = $v + 0;
        if (is_float($n) && (is_nan($n) || is_infinite($n))) {
            return null;
        }
        return max(-1.0e12, min(1.0e12, $n));
    }

    private static function dateBound(mixed $v): ?string
    {
        if (!is_string($v)) {
            return null;
        }
        $v = trim($v);
        if ($v === 'today') {
            return 'today';
        }
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $v);
        return $d !== false && $d->format('Y-m-d') === $v ? $v : null;
    }

    private static function timeOrNull(mixed $v): ?string
    {
        return is_string($v) && preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', trim($v)) === 1 ? trim($v) : null;
    }

    /** @param list<array{value:string,label:string}> $options */
    private static function cleanDefault(mixed $v, string $type, array $options): string|array
    {
        $values = array_column($options, 'value');
        switch ($type) {
            case 'checkboxes':
                $list = is_array($v) ? $v : (is_string($v) && $v !== '' ? explode(',', $v) : []);
                $out = [];
                foreach ($list as $x) {
                    $x = trim((string) $x);
                    if (in_array($x, $values, true) && !in_array($x, $out, true)) {
                        $out[] = $x;
                    }
                }
                return $out;
            case 'select':
            case 'radio':
                $x = is_string($v) ? trim($v) : '';
                return in_array($x, $values, true) ? $x : '';
            case 'yes_no':
                return in_array($v, ['yes', 'no'], true) ? (string) $v : '';
            case 'number':
                return is_numeric($v) ? (string) $v : '';
            case 'date':
                return self::dateBound($v) ?? '';
            case 'time':
                return self::timeOrNull($v) ?? '';
            case 'textarea':
                return self::plain($v, self::MAX_DEFAULT, true);
            default:
                return self::plain($v, self::MAX_DEFAULT);
        }
    }

    // ------------------------------------------------------------------ publish checklist

    /**
     * Is this form ready to go live? A friendly list the builder shows before publishing.
     * level `error` blocks publishing (and saving a form that is already live); `warning` is advice.
     *
     * @param array<string,mixed> $definition normalised definition
     * @param array<string,mixed> $settings   normalised settings (FormSettings::normalise); [] skips the settings checks
     * @param array<string,mixed> $meta       form columns: slug, opens_at, closes_at, max_submissions
     * @return list<array{level:string,code:string,message:string,page:?string,field:?string}>
     */
    public static function publishChecklist(array $definition, array $settings = [], array $meta = []): array
    {
        $out = [];
        $add = static function (string $level, string $code, string $message, ?string $page = null, ?string $field = null) use (&$out): void {
            $out[] = ['level' => $level, 'code' => $code, 'message' => $message, 'page' => $page, 'field' => $field];
        };
        $map = self::fieldMap($definition);
        $pos = self::positions($definition);
        $inputs = self::inputFields($definition);
        if ($inputs === []) {
            $add('error', 'no_fields', 'Add at least one question to the form.');
        }
        $labels = [];
        foreach (self::flat($definition) as $row) {
            $f = $row['field'];
            $id = (string) $f['id'];
            $name = (string) ($f['label'] ?? '') !== '' ? '"' . $f['label'] . '"' : 'A ' . strtolower((string) self::TYPES[$f['type']]['label']);
            $isInput = self::isInput((string) $f['type']);
            if (($isInput || $f['type'] === 'heading') && trim((string) $f['label']) === '') {
                $add('error', 'empty_label', 'The ' . strtolower((string) self::TYPES[$f['type']]['label']) . ' on page ' . ($row['page'] + 1) . ' has no label.', $row['page_id'], $id);
            }
            if ($f['type'] === 'paragraph' && trim((string) ($f['text'] ?? '')) === '') {
                $add('error', 'empty_text', 'A help text on page ' . ($row['page'] + 1) . ' is empty.', $row['page_id'], $id);
            }
            if ($isInput && trim((string) $f['label']) !== '') {
                $k = mb_strtolower((string) $f['label']);
                if (isset($labels[$k])) {
                    $add('warning', 'duplicate_label', 'Two questions are both called "' . $f['label'] . '". Their answers are hard to tell apart in the inbox and in the export.', $row['page_id'], $id);
                }
                $labels[$k] = true;
            }
            if (self::isChoice((string) $f['type'])) {
                $n = count((array) ($f['options'] ?? []));
                $other = !empty($f['allow_other']);
                if ($n === 0 && !$other) {
                    $add('error', 'empty_options', $name . ' has no options.', $row['page_id'], $id);
                } elseif ($n < 2 && in_array($f['type'], ['select', 'radio'], true) && !$other) {
                    $add('warning', 'one_option', $name . ' has only one option.', $row['page_id'], $id);
                }
            }
            $v = (array) ($f['validation'] ?? []);
            if (isset($v['min_length'], $v['max_length']) && $v['min_length'] > $v['max_length']) {
                $add('error', 'range', $name . ': the minimum length is larger than the maximum length.', $row['page_id'], $id);
            }
            if (isset($v['min'], $v['max']) && $v['min'] !== 'today' && $v['max'] !== 'today') {
                $bad = is_numeric($v['min']) && is_numeric($v['max']) ? (float) $v['min'] > (float) $v['max'] : (string) $v['min'] > (string) $v['max'];
                if ($bad) {
                    $add('error', 'range', $name . ': the minimum is larger than the maximum.', $row['page_id'], $id);
                }
            }
            // conditions of the field
            self::checkLogic($f['logic'] ?? null, $id, $row['page_id'], $name, $map, $pos, $add, false);
        }
        foreach ((array) ($definition['pages'] ?? []) as $pi => $page) {
            $title = trim((string) $page['title']) !== '' ? '"' . $page['title'] . '"' : 'page ' . ($pi + 1);
            if ((array) $page['fields'] === []) {
                $add('warning', 'empty_page', ucfirst($title) . ' has no fields.', (string) $page['id']);
            }
            self::checkLogic($page['logic'] ?? null, null, (string) $page['id'], ucfirst($title), $map, $pos, $add, true, $pi);
        }
        // a condition cycle (A shows only if B ..., B only if A ...)
        foreach (self::cycles($definition) as $id) {
            $add('error', 'condition_cycle', 'The conditions of "' . ($map[$id]['label'] ?? $id) . '" depend on each other in a circle.', null, $id);
        }
        // personal data advice
        $hasFile = false;
        $hasConsent = false;
        foreach ($inputs as $f) {
            $hasFile = $hasFile || $f['type'] === 'file';
            $hasConsent = $hasConsent || $f['type'] === 'consent';
        }
        if ($settings !== []) {
            $consentOn = !empty($settings['consent']['required']);
            if (($hasFile) && !$hasConsent && !$consentOn) {
                $add('warning', 'no_consent', 'This form collects files but has no consent box. Add a "Consent box" question or switch on "Require consent" in the settings.');
            }
            if (!empty($settings['confirmation']['enabled'])) {
                $ef = (string) ($settings['confirmation']['email_field'] ?? '');
                if ($ef === '' || ($map[$ef]['type'] ?? '') !== 'email') {
                    $add('error', 'confirmation_field', 'The confirmation e-mail needs an "E-mail address" question to send it to. Choose one in the settings, or switch the confirmation off.');
                }
            }
            if (!empty($settings['one_per_email'])) {
                $hasEmail = false;
                foreach ($inputs as $f) {
                    $hasEmail = $hasEmail || $f['type'] === 'email';
                }
                if (!$hasEmail) {
                    $add('error', 'one_per_email', '"One submission per e-mail address" needs an "E-mail address" question.');
                }
            }
            if (!empty($settings['notify']['enabled']) && FormSettings::staffRecipients($settings) === []) {
                $add('warning', 'no_recipient', 'Nobody is set to be e-mailed about new submissions. Add an address in the settings (or in Site settings > Forms).');
            }
            if ((int) ($settings['retention_days'] ?? 0) === 0 && ($hasFile || self::hasPersonalFields($inputs))) {
                $add('warning', 'no_retention', 'No retention period is set, so personal data is kept until you delete it. Consider "Delete submissions after ... days" in the settings.');
            }
            if (($settings['success']['mode'] ?? 'message') === 'redirect' && ($settings['success']['redirect_path'] ?? '') === '') {
                $add('error', 'redirect', 'Choose the page visitors go to after sending, or switch to a thank-you message.');
            }
        }
        if (!empty($meta['opens_at']) && !empty($meta['closes_at']) && (string) $meta['closes_at'] <= (string) $meta['opens_at']) {
            $add('error', 'dates', 'The closing date is before the opening date.');
        }
        if (isset($meta['max_submissions']) && $meta['max_submissions'] !== null && (int) $meta['max_submissions'] < 1) {
            $add('error', 'max', 'The maximum number of submissions must be at least 1.');
        }
        return $out;
    }

    /** Does the form ask for e-mail, phone or other identifying fields? @param list<array<string,mixed>> $inputs */
    private static function hasPersonalFields(array $inputs): bool
    {
        foreach ($inputs as $f) {
            if (in_array($f['type'], ['email', 'phone'], true) || (($f['validation']['pattern'] ?? '') === 'nin')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<string,array<string,mixed>> $map
     * @param array<string,array{page:int,index:int}> $pos
     */
    private static function checkLogic(?array $logic, ?string $ownerId, string $pageId, string $name, array $map, array $pos, callable $add, bool $pageLevel, int $ownerPage = 0): void
    {
        if ($logic === null) {
            return;
        }
        foreach ($logic['rules'] as $r) {
            $rid = (string) $r['field'];
            if ($rid === '') {
                $add('error', 'dangling_condition', $name . ': a condition has no question selected.', $pageId, $ownerId);
                continue;
            }
            if (!isset($map[$rid])) {
                $add('error', 'dangling_condition', $name . ': a condition refers to a question that no longer exists.', $pageId, $ownerId);
                continue;
            }
            if ($rid === $ownerId) {
                $add('error', 'dangling_condition', $name . ' cannot depend on itself.', $pageId, $ownerId);
                continue;
            }
            if (!self::isInput((string) $map[$rid]['type'])) {
                $add('error', 'dangling_condition', $name . ': a condition refers to something that is not a question.', $pageId, $ownerId);
                continue;
            }
            if (!in_array($r['op'], ['filled', 'empty'], true) && trim((string) $r['value']) === '' && in_array($r['op'], ['contains', 'gt', 'lt'], true)) {
                $add('error', 'incomplete_condition', $name . ': a condition has no value to compare with.', $pageId, $ownerId);
            }
            $rp = $pos[$rid] ?? null;
            if ($rp !== null) {
                if ($pageLevel && $rp['page'] >= $ownerPage) {
                    $add('error', 'condition_order', $name . ' can only depend on questions on EARLIER pages (visitors have not answered later ones yet).', $pageId, $ownerId);
                } elseif (!$pageLevel && $ownerId !== null && isset($pos[$ownerId]) && $rp['index'] > $pos[$ownerId]['index']) {
                    $add('warning', 'condition_order', $name . ' depends on a question that comes after it.', $pageId, $ownerId);
                }
            }
        }
    }

    /**
     * Fields that are part of a dependency cycle (A needs B, B needs A; also via page logic).
     * @return list<string>
     */
    private static function cycles(array $definition): array
    {
        $deps = [];
        foreach ((array) ($definition['pages'] ?? []) as $page) {
            $pageDeps = array_column((array) ($page['logic']['rules'] ?? []), 'field');
            foreach ((array) $page['fields'] as $f) {
                $deps[(string) $f['id']] = array_values(array_unique(array_merge($pageDeps, array_column((array) ($f['logic']['rules'] ?? []), 'field'))));
            }
        }
        $bad = [];
        $state = [];
        $path = [];
        $visit = static function (string $id) use (&$visit, &$state, &$bad, &$path, $deps): void {
            $state[$id] = 1;
            $path[] = $id;
            foreach ($deps[$id] ?? [] as $d) {
                if (!isset($deps[$d])) {
                    continue;                                   // dangling: reported elsewhere
                }
                if (($state[$d] ?? 0) === 1) {                  // back edge: everything on the path from $d is in the cycle
                    $from = array_search($d, $path, true);
                    foreach (array_slice($path, (int) $from) as $x) {
                        $bad[$x] = true;
                    }
                } elseif (($state[$d] ?? 0) === 0) {
                    $visit($d);
                }
            }
            array_pop($path);
            $state[$id] = 2;
        };
        foreach (array_keys($deps) as $id) {
            if (($state[(string) $id] ?? 0) === 0) {
                $visit((string) $id);
            }
        }
        return array_keys($bad);
    }
}
