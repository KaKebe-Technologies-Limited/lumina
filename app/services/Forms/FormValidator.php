<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\Mailer;
use App\Core\Request;
use App\Core\UploadException;

/**
 * SERVER-SIDE VALIDATION OF A SUBMISSION against a form definition. Contract: docs/FORMS.md section 6. The browser validates too (inline, on blur), but
 * this class is the authority: it re-evaluates the conditional logic, ignores everything posted for hidden fields, checks EVERY visible field by type,
 * and returns cleaned values. It never stores anything and never echoes hostile input back inside an error message.
 *
 * Request field names (HTML `name` attributes):
 *     f_<fieldId>          the answer  (checkboxes: f_<fieldId>[] ; file fields: f_<fieldId> or f_<fieldId>[] with multiple)
 *     f_<fieldId>_other    free text of the "Other: please specify" choice
 *     form_consent         the form-level consent box (settings.consent.required)
 *
 * Normalised values (the shape stored in the snapshot, see SubmissionService::buildSnapshot):
 *     text/textarea/email/phone/number/date/time : string  (email lower-case, phone +E.164, number as typed with "." decimal, date Y-m-d, time HH:MM)
 *     select/radio : option value or "__other" | checkboxes : list of option values | yes_no : "yes"/"no" | rating : "1".."5" | consent : "yes" or ""
 *     file : list of the uploaded file arrays are returned separately in `files`; `values[id]` is the list of cleaned file names.
 */
final class FormValidator
{
    /**
     * @param array<string,mixed>            $definition normalised definition
     * @param array<string,mixed>            $input      all request parameters (Request::all()), flat
     * @param array<string,list<array>>      $files      fieldId => list of upload arrays (see collectFiles())
     * @param array{settings?:array,messages?:array<string,string>,inspect_files?:bool,now?:\DateTimeImmutable} $opts
     *
     * @return array{
     *   ok:bool,
     *   errors:array<string,string>,
     *   values:array<string,string|list<string>>,
     *   others:array<string,string>,
     *   files:array<string,list<array>>,
     *   visible:array<string,bool>,
     *   pages_visible:array<string,bool>,
     *   error_pages:list<string>,
     *   consent_at:?string
     * }
     *   errors      : fieldId => first message, plus "_form" (whole-form problems: too much data, total file size) and "_consent" (form-level consent box)
     *   values      : fieldId => cleaned value for every VISIBLE input field ('' / [] when optional and empty)
     *   others      : fieldId => the free text of "Other" for visible choice fields whose value is "__other" (or contains it)
     *   files       : fieldId => accepted upload arrays of visible file fields, ready for FormFileStore::storeAll()
     *   error_pages : ids of the pages that contain an error (the stepped form jumps to the first)
     */
    public static function validate(array $definition, array $input, array $files = [], array $opts = []): array
    {
        $now = $opts['now'] ?? FormService::now();
        $inspect = (bool) ($opts['inspect_files'] ?? true);
        $settings = (array) ($opts['settings'] ?? []);
        $msg = static function (string $code, array $vars = []) use ($opts): string {
            $custom = $opts['messages'][$code] ?? null;
            return is_string($custom) && $custom !== '' ? FormText::fill($custom, $vars) : FormText::get('errors.' . $code, $vars);
        };

        // ---- 1. raw values as entered (for the conditions)
        $raw = [];
        foreach (FormDefinition::fields($definition) as $f) {
            if (FormDefinition::isInput((string) $f['type'])) {
                $raw[(string) $f['id']] = self::readRaw($f, $input, $files);
            }
        }
        $res = ConditionEvaluator::resolve($definition, $raw);

        // ---- 2. validate every VISIBLE input field
        $errors = [];
        $values = [];
        $others = [];
        $outFiles = [];
        $consentAt = null;
        $total = 0;
        $filesTotal = 0;
        $pageOf = [];
        foreach ($definition['pages'] as $page) {
            foreach ($page['fields'] as $f) {
                $pageOf[(string) $f['id']] = (string) $page['id'];
            }
        }

        foreach (FormDefinition::fields($definition) as $f) {
            $id = (string) $f['id'];
            $type = (string) $f['type'];
            if (!FormDefinition::isInput($type) || empty($res['fields'][$id])) {
                continue;
            }
            $label = (string) $f['label'];
            $required = !empty($f['required']);
            $val = (array) ($f['validation'] ?? []);
            $in = $input['f_' . $id] ?? null;
            $err = null;
            $out = '';

            switch ($type) {
                case 'text':
                case 'textarea':
                    $s = self::cleanText($in, $type === 'textarea');
                    $out = $s;
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                        break;
                    }
                    $len = mb_strlen($s);
                    $max = FormDefinition::maxLength($f);
                    $min = (int) ($val['min_length'] ?? 0);
                    if ($min > 0 && $len < $min) {
                        $err = $msg('min_length', ['label' => $label, 'min' => $min]);
                    } elseif ($len > $max) {
                        $err = $msg('max_length', ['label' => $label, 'max' => $max]);
                    } elseif (($val['pattern'] ?? '') !== '' && !FormDefinition::matchPattern((string) $val['pattern'], $s)) {
                        $err = $msg('pattern', ['label' => $label, 'hint' => FormDefinition::patternHint((string) $val['pattern'])]);
                    }
                    break;

                case 'email':
                    $s = strtolower(self::cleanText($in, false));
                    $out = $s;
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                    } elseif (strlen($s) > min(190, FormDefinition::maxLength($f)) || !self::plainEmail($s)) {
                        $err = $msg('email', ['label' => $label]);
                    }
                    break;

                case 'phone':
                    $s = self::cleanText($in, false);
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                        break;
                    }
                    $p = FormDefinition::normalisePhone($s);
                    if ($p === null) {
                        $out = $s;
                        $err = $msg('phone', ['label' => $label]);
                    } else {
                        $out = $p;
                    }
                    break;

                case 'number':
                    $s = self::cleanText($in, false);
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                        break;
                    }
                    if (preg_match('/^-?[0-9]+,[0-9]+$/', $s) === 1) {
                        $s = str_replace(',', '.', $s);
                    }
                    $out = $s;
                    if (preg_match('/^-?[0-9]{1,15}(\.[0-9]{1,6})?$/', $s) !== 1) {
                        $err = $msg('number', ['label' => $label]);
                    } elseif (isset($val['min']) && $val['min'] !== null && (float) $s < (float) $val['min']) {
                        $err = $msg('min_number', ['label' => $label, 'min' => $val['min']]);
                    } elseif (isset($val['max']) && $val['max'] !== null && (float) $s > (float) $val['max']) {
                        $err = $msg('max_number', ['label' => $label, 'max' => $val['max']]);
                    }
                    break;

                case 'date':
                    $s = self::cleanText($in, false);
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                        break;
                    }
                    $out = $s;
                    $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $s);
                    if ($d === false || $d->format('Y-m-d') !== $s || (int) $d->format('Y') < 1900 || (int) $d->format('Y') > 2200) {
                        $err = $msg('date', ['label' => $label]);
                        break;
                    }
                    $min = self::bound($val['min'] ?? null, $now);
                    $max = self::bound($val['max'] ?? null, $now);
                    if ($min !== null && $s < $min) {
                        $err = $msg('min_date', ['label' => $label, 'min' => format_date($min)]);
                    } elseif ($max !== null && $s > $max) {
                        $err = $msg('max_date', ['label' => $label, 'max' => format_date($max)]);
                    }
                    break;

                case 'time':
                    $s = self::cleanText($in, false);
                    if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $s) === 1) {
                        $s = substr($s, 0, 5);
                    }
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                        break;
                    }
                    $out = $s;
                    if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $s) !== 1) {
                        $err = $msg('time', ['label' => $label]);
                    } elseif (isset($val['min']) && $val['min'] !== null && $s < $val['min']) {
                        $err = $msg('min_time', ['label' => $label, 'min' => $val['min']]);
                    } elseif (isset($val['max']) && $val['max'] !== null && $s > $val['max']) {
                        $err = $msg('max_time', ['label' => $label, 'max' => $val['max']]);
                    }
                    break;

                case 'select':
                case 'radio':
                    $s = is_string($in) ? trim($in) : '';
                    $out = $s;
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                        break;
                    }
                    if (!self::validOption($f, $s)) {
                        $out = '';
                        $err = $msg('option', ['label' => $label]);
                        break;
                    }
                    if ($s === FormDefinition::OTHER) {
                        $o = self::cleanText($input['f_' . $id . '_other'] ?? '', false);
                        if ($o === '') {
                            $err = $msg('other_required', ['label' => $label]);
                        } else {
                            $others[$id] = mb_substr($o, 0, FormDefinition::MAX_OTHER_TEXT);
                        }
                    }
                    break;

                case 'checkboxes':
                    $list = is_array($in) ? $in : (is_string($in) && $in !== '' ? [$in] : []);
                    $picked = [];
                    $bad = false;
                    foreach ($list as $x) {
                        $x = is_string($x) ? trim($x) : '';
                        if ($x === '') {
                            continue;
                        }
                        if (!self::validOption($f, $x)) {
                            $bad = true;
                            continue;
                        }
                        if (!in_array($x, $picked, true)) {
                            $picked[] = $x;
                        }
                    }
                    $out = $picked;
                    if ($bad) {
                        $err = $msg('option', ['label' => $label]);
                    } elseif ($picked === []) {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                    } else {
                        $min = (int) ($val['min'] ?? 0);
                        $max = (int) ($val['max'] ?? 0);
                        if ($min > 0 && count($picked) < $min) {
                            $err = $msg('min_select', ['label' => $label, 'min' => $min]);
                        } elseif ($max > 0 && count($picked) > $max) {
                            $err = $msg('max_select', ['label' => $label, 'max' => $max]);
                        } elseif (in_array(FormDefinition::OTHER, $picked, true)) {
                            $o = self::cleanText($input['f_' . $id . '_other'] ?? '', false);
                            if ($o === '') {
                                $err = $msg('other_required', ['label' => $label]);
                            } else {
                                $others[$id] = mb_substr($o, 0, FormDefinition::MAX_OTHER_TEXT);
                            }
                        }
                    }
                    break;

                case 'yes_no':
                    $s = is_string($in) ? strtolower(trim($in)) : '';
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                    } elseif ($s !== 'yes' && $s !== 'no') {
                        $err = $msg('yes_no', ['label' => $label]);
                    } else {
                        $out = $s;
                    }
                    break;

                case 'rating':
                    $s = is_string($in) || is_int($in) ? trim((string) $in) : '';
                    if ($s === '') {
                        $err = $required ? $msg('required', ['label' => $label]) : null;
                    } elseif (preg_match('/^[1-5]$/', $s) !== 1) {
                        $err = $msg('rating', ['label' => $label]);
                    } else {
                        $out = $s;
                    }
                    break;

                case 'consent':
                    if (self::truthy($in)) {
                        $out = 'yes';
                        $consentAt = $consentAt ?? $now->format('Y-m-d H:i:s');
                    } elseif ($required) {
                        $err = $msg('consent', ['label' => $label]);
                    }
                    break;

                case 'file':
                    $rules = (array) ($f['file'] ?? []);
                    $list = array_values((array) ($files[$id] ?? []));
                    $names = [];
                    if ($list === []) {
                        $out = [];
                        $err = $required ? $msg('file_required', ['label' => $label]) : null;
                        break;
                    }
                    $maxFiles = max(1, (int) ($rules['max_files'] ?? 1));
                    if (count($list) > $maxFiles) {
                        $out = [];
                        $err = $msg('file_too_many', ['label' => $label, 'count' => $maxFiles]);
                        break;
                    }
                    $okFiles = [];
                    foreach ($list as $file) {
                        try {
                            if ($inspect) {
                                $meta = FormFileStore::inspect($file, $rules);
                            } else {
                                $meta = self::inspectNames($file, $rules);
                            }
                        } catch (UploadException $e) {
                            $err = $e->getMessage();
                            break;
                        }
                        $filesTotal += (int) $meta['size'];
                        $names[] = $meta['name'];
                        $okFiles[] = $file;
                    }
                    $out = $names;
                    if ($err === null) {
                        $outFiles[$id] = $okFiles;
                    }
                    break;
            }

            $values[$id] = $out;
            if ($err !== null) {
                $errors[$id] = $err;
            }
            $total += is_array($out) ? strlen(implode('', $out)) : strlen((string) $out);
        }

        // ---- 3. whole-form rules
        $consentCfg = (array) ($settings['consent'] ?? []);
        if (!empty($consentCfg['required'])) {
            if (self::truthy($input['form_consent'] ?? null)) {
                $consentAt = $consentAt ?? $now->format('Y-m-d H:i:s');
            } else {
                $errors['_consent'] = $msg('consent', ['label' => '']);
            }
        }
        if ($total > (int) config('form_builder.max_total_answer_kb', 256) * 1024) {
            $errors['_form'] = $msg('form_too_big');
        }
        if ($filesTotal > (int) config('form_builder.file_total_mb', 40) * 1048576) {
            $errors['_form'] = $msg('files_too_big', ['max' => (int) config('form_builder.file_total_mb', 40)]);
        }
        $errorPages = [];
        foreach (array_keys($errors) as $fid) {
            if (isset($pageOf[$fid]) && !in_array($pageOf[$fid], $errorPages, true)) {
                $errorPages[] = $pageOf[$fid];
            }
        }
        // document order for the pages
        $ordered = [];
        foreach ($definition['pages'] as $page) {
            if (in_array((string) $page['id'], $errorPages, true)) {
                $ordered[] = (string) $page['id'];
            }
        }
        return [
            'ok'            => $errors === [],
            'errors'        => $errors,
            'values'        => $values,
            'others'        => $others,
            'files'         => $errors === [] ? $outFiles : [],
            'visible'       => $res['fields'],
            'pages_visible' => $res['pages'],
            'error_pages'   => $ordered,
            'consent_at'    => $consentAt,
        ];
    }

    /**
     * Collect the uploaded files of every file field from a request: fieldId => list of upload arrays (empty fields omitted).
     * @return array<string,list<array>>
     */
    /**
     * An address a form accepts: valid for Mailer AND plain (letters, digits and . _ % + ' - before the @, a dotted host name after it). PHP's own filter also
     * accepts "x?bcc=a@b.example" and "x@[127.0.0.1]", which would turn an admin "mailto:" link into a Bcc / Subject injection or point nowhere useful.
     */
    public static function plainEmail(string $s): bool
    {
        return Mailer::validEmail($s) && preg_match('/^[A-Za-z0-9._%+\'\-]{1,64}@(?:[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,24}$/', $s) === 1;
    }

    public static function collectFiles(array $definition, Request $request): array
    {
        $out = [];
        foreach (FormDefinition::inputFields($definition) as $f) {
            if ($f['type'] === 'file') {
                $list = $request->files('f_' . $f['id']);
                if ($list !== []) {
                    $out[(string) $f['id']] = $list;
                }
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------ internals

    /** The value of a field AS ENTERED, in the shape the conditions compare (docs/FORMS.md section 5). */
    private static function readRaw(array $f, array $input, array $files): string|array|null
    {
        $id = (string) $f['id'];
        $in = $input['f_' . $id] ?? null;
        switch ((string) $f['type']) {
            case 'checkboxes':
                $list = is_array($in) ? $in : (is_string($in) && $in !== '' ? [$in] : []);
                return array_values(array_filter(array_map(static fn($x): string => is_string($x) ? trim($x) : '', $list), static fn(string $x): bool => $x !== ''));
            case 'file':
                return array_map(static fn(array $file): string => (string) ($file['name'] ?? ''), array_values((array) ($files[$id] ?? [])));
            case 'consent':
                return self::truthy($in) ? 'yes' : '';
            default:
                return is_string($in) || is_int($in) || is_float($in) ? trim((string) $in) : '';
        }
    }

    private static function truthy(mixed $v): bool
    {
        return in_array(is_string($v) ? strtolower(trim($v)) : $v, ['1', 'on', 'yes', 'true', true, 1], true);
    }

    /** Trim, remove control characters; single-line text also turns line breaks into spaces. */
    private static function cleanText(mixed $v, bool $multiline): string
    {
        if (!is_string($v)) {
            return '';
        }
        if (!mb_check_encoding($v, 'UTF-8')) {
            $v = (string) mb_convert_encoding($v, 'UTF-8', 'UTF-8');
        }
        $v = str_replace(["\r\n", "\r"], "\n", $v);
        $v = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);
        if ($multiline) {
            $v = (string) preg_replace("/\n{4,}/", "\n\n\n", $v);
        } else {
            $v = (string) preg_replace('/\s+/u', ' ', $v);
        }
        return trim($v);
    }

    /** @param array<string,mixed> $f */
    private static function validOption(array $f, string $value): bool
    {
        if ($value === FormDefinition::OTHER) {
            return !empty($f['allow_other']);
        }
        foreach ((array) ($f['options'] ?? []) as $o) {
            if ((string) $o['value'] === $value) {
                return true;
            }
        }
        return false;
    }

    /** Resolve "today" / a fixed date to Y-m-d (EAT). */
    private static function bound(mixed $v, \DateTimeImmutable $now): ?string
    {
        if ($v === 'today') {
            return $now->format('Y-m-d');
        }
        return is_string($v) && $v !== '' ? $v : null;
    }

    /** Cheap file check without reading content (used by the builder's test submit and the unit tests). @return array{size:int,name:string} */
    private static function inspectNames(array $file, array $rules): array
    {
        $name = (string) ($file['name'] ?? '');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        $types = (array) ($rules['types'] ?? ['pdf']);
        if (!in_array($ext, $types, true)) {
            throw new UploadException(FormText::get('errors.file_type', ['types' => strtoupper(implode(', ', $types))]));
        }
        $size = (int) ($file['size'] ?? 0);
        $maxMb = max(1, (int) ($rules['max_mb'] ?? 5));
        if ($size > $maxMb * 1048576) {
            throw new UploadException(FormText::get('errors.file_size', ['max' => $maxMb]));
        }
        return ['size' => $size, 'name' => FormFileStore::cleanName($name, $ext)];
    }
}
