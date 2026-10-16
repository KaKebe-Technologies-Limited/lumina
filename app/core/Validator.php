<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Input validation.
 *
 *   $v = Validator::make($request->all(), [
 *       'name'    => 'required|max:120',
 *       'email'   => 'required|email|max:190',
 *       'phone'   => 'nullable|phone',
 *       'type'    => 'required|in:volunteer,partner,mentor',
 *       'website' => 'nullable|url',
 *       'slug'    => 'required|slug|unique:posts,slug,' . $id,       // table,column[,ignoreId]
 *       'start'   => 'required|datetime',
 *       'agree'   => 'accepted',
 *   ], ['email.required' => 'Please enter your email.'], ['email' => 'Email address']);
 *   if ($v->fails()) { $v->errors(); }     // ['email' => 'Please enter your email.']  (first message per field)
 *   $data = $v->validated();               // only the keys that have rules, trimmed strings (arrays untouched)
 *
 * Rules (pipe string or array): required, nullable, string, email, min:n, max:n (chars, or value for numeric/integer),
 * between:a,b, in:a,b,c, not_in:..., url (http/https), phone, date (Y-m-d), datetime (Y-m-d H:i[:s] or Y-m-d\TH:i),
 * numeric, integer, boolean, accepted, confirmed, same:field, regex:/pattern/, slug, alpha_num, unique:table,col[,ignoreId],
 * exists:table,col, array.
 * In forms use Controller::validate() which throws ValidationException (auto redirect back with errors + old input).
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];
    /** @var array<string,mixed> */
    private array $data = [];

    private function __construct(private array $input, private array $rules, private array $messages, private array $labels)
    {
        $this->run();
    }

    public static function make(array $input, array $rules, array $messages = [], array $labels = []): self
    {
        return new self($input, $rules, $messages, $labels);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string,string> first error message per field */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string,mixed> */
    public function validated(): array
    {
        return $this->data;
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $spec) {
            $rules = is_array($spec) ? $spec : explode('|', (string) $spec);
            $raw = $this->input[$field] ?? null;
            $value = is_string($raw) ? trim($raw) : $raw;
            $this->data[$field] = $value;
            // a list/array posted for a scalar field (name[]=x) is never valid: reject it instead of casting it to "Array"
            if (is_array($value) && $value !== [] && !in_array('array', $rules, true)) {
                $this->fail($field, 'invalid', '', $value);
                continue;
            }
            $empty = $value === null || $value === '' || $value === [];
            $nullable = in_array('nullable', $rules, true) || (!in_array('required', $rules, true) && !in_array('accepted', $rules, true));

            foreach ($rules as $rule) {
                if ($rule === 'nullable' || $rule === '') {
                    continue;
                }
                [$name, $arg] = array_pad(explode(':', (string) $rule, 2), 2, '');
                if ($name === 'required') {
                    if ($empty) {
                        $this->fail($field, $name, $arg, $value);
                        break;
                    }
                    continue;
                }
                if ($empty) {
                    if ($nullable) {
                        break;
                    }
                    continue;
                }
                if (!$this->check($name, $arg, $value, $field)) {
                    $this->fail($field, $name, $arg, $value);
                    break;
                }
            }
        }
    }

    private function check(string $rule, string $arg, mixed $value, string $field): bool
    {
        $isNum = static fn($v): bool => is_numeric($v);
        $numeric = in_array('numeric', (array) $this->ruleList($field), true) || in_array('integer', (array) $this->ruleList($field), true);
        switch ($rule) {
            case 'string':
                return is_string($value);
            case 'array':
                return is_array($value);
            case 'email':
                return is_string($value) && strlen($value) <= 254 && filter_var($value, FILTER_VALIDATE_EMAIL) !== false
                    && !preg_match('/[\r\n]/', $value);
            case 'min':
                return $numeric && $isNum($value) ? (float) $value >= (float) $arg : mb_strlen((string) $value) >= (int) $arg;
            case 'max':
                return $numeric && $isNum($value) ? (float) $value <= (float) $arg : mb_strlen((string) $value) <= (int) $arg;
            case 'between':
                [$a, $b] = array_map('floatval', array_pad(explode(',', $arg), 2, '0'));
                return $isNum($value) && (float) $value >= $a && (float) $value <= $b;
            case 'in':
                return in_array((string) $value, explode(',', $arg), true);
            case 'not_in':
                return !in_array((string) $value, explode(',', $arg), true);
            case 'url':
                return is_string($value) && preg_match('~^https?://~i', $value) === 1 && filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'phone':
                return is_string($value) && preg_match('/^\+?[0-9][0-9\s().\-]{5,24}$/', $value) === 1;
            case 'date':
                return $this->validDate((string) $value, 'Y-m-d');
            case 'datetime':
                $v = str_replace('T', ' ', (string) $value);
                return $this->validDate($v, 'Y-m-d H:i') || $this->validDate($v, 'Y-m-d H:i:s');
            case 'numeric':
                return $isNum($value);
            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT) !== false;
            case 'boolean':
                return in_array(is_string($value) ? strtolower($value) : $value, [true, false, 0, 1, '0', '1', 'on', 'off', 'true', 'false', 'yes', 'no'], true);
            case 'accepted':
                return in_array(is_string($value) ? strtolower($value) : $value, [true, 1, '1', 'on', 'yes', 'true'], true);
            case 'confirmed':
                return isset($this->input[$field . '_confirmation']) && hash_equals((string) $value, (string) $this->input[$field . '_confirmation']);
            case 'same':
                return isset($this->input[$arg]) && (string) $this->input[$arg] === (string) $value;
            case 'regex':
                return is_string($value) && @preg_match($arg, $value) === 1;
            case 'slug':
                return is_string($value) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1;
            case 'alpha_num':
                return is_string($value) && preg_match('/^[A-Za-z0-9_\-]+$/', $value) === 1;
            case 'unique':
                [$table, $col, $ignore] = array_pad(explode(',', $arg), 3, '');
                $sql = 'SELECT COUNT(*) FROM ' . Database::ident($table) . ' WHERE ' . Database::ident($col ?: $field) . ' = ?';
                $params = [(string) $value];
                if ($ignore !== '' && ctype_digit($ignore)) {
                    $sql .= ' AND id <> ?';
                    $params[] = (int) $ignore;
                }
                return (int) Database::value($sql, $params, 0) === 0;
            case 'exists':
                [$table, $col] = array_pad(explode(',', $arg), 2, '');
                return (int) Database::value('SELECT COUNT(*) FROM ' . Database::ident($table) . ' WHERE ' . Database::ident($col ?: 'id') . ' = ?', [(string) $value], 0) > 0;
        }
        throw new \InvalidArgumentException("Unknown validation rule: {$rule}");
    }

    private function ruleList(string $field): array
    {
        $spec = $this->rules[$field] ?? [];
        return is_array($spec) ? $spec : explode('|', (string) $spec);
    }

    private function validDate(string $value, string $format): bool
    {
        $d = \DateTime::createFromFormat('!' . $format, $value);
        return $d !== false && $d->format($format) === $value;
    }

    private function fail(string $field, string $rule, string $arg, mixed $value): void
    {
        if (isset($this->errors[$field])) {
            return;
        }
        $custom = $this->messages["{$field}.{$rule}"] ?? $this->messages[$field] ?? null;
        $label = $this->labels[$field] ?? ucfirst(str_replace(['_', '-'], ' ', $field));
        $this->errors[$field] = $custom ?? match ($rule) {
            'required'  => "{$label} is required.",
            'email'     => 'Please enter a valid email address.',
            'min'       => "{$label} must be at least {$arg}" . (in_array('numeric', $this->ruleList($field), true) ? '.' : ' characters.'),
            'max'       => "{$label} must be at most {$arg}" . (in_array('numeric', $this->ruleList($field), true) ? '.' : ' characters.'),
            'between'   => "{$label} must be between " . str_replace(',', ' and ', $arg) . '.',
            'in', 'not_in' => "{$label} has an invalid value.",
            'url'       => 'Please enter a full web address starting with http:// or https://.',
            'phone'     => 'Please enter a valid phone number.',
            'date'      => "{$label} must be a valid date (YYYY-MM-DD).",
            'datetime'  => "{$label} must be a valid date and time.",
            'numeric', 'integer' => "{$label} must be a number.",
            'unique'    => "That {$label} is already in use.",
            'exists'    => "{$label} is not valid.",
            'confirmed', 'same' => "{$label} does not match.",
            'accepted'  => "Please accept {$label}.",
            'slug'      => "{$label} may only contain lowercase letters, numbers and hyphens.",
            default     => "{$label} is invalid.",
        };
    }
}
