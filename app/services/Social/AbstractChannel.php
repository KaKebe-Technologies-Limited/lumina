<?php
declare(strict_types=1);

namespace App\Services\Social;

/**
 * Base of the six channel adapters: holds the static description (META of the subclass merged over DEFAULT_META), the format validation of the admin's input
 * (driven by the field specs) and small helpers. A concrete channel only adds post() and test() (and, where useful, normaliseConfig()).
 *
 * META keys (docs/SOCIAL.md section 4):
 *   id, label, icon (name from Icons::names()), short (one plain sentence: what it does), limit (HARD caption limit, enforced when saving and sending), soft
 *   (recommended length, shown as a hint), count ('plain' | 'x': X counts every URL as 23 and weights characters), links ('clickable' | 'none' | 'attached':
 *   whether a link in the caption is clickable, not clickable (Instagram: write "link in bio"), or attached as a link card), uses_image (the post carries the share
 *   image), image_variant (square | portrait | landscape | null), ambiguous ('review' | 'retry': what to do when it is unknown whether a request was executed),
 *   needs (one line: what the owner must have), setup_url (where the keys are created), steps (plain-language list), permissions (what the token needs),
 *   notes (honest limits), fields (list of field specs), test_post ('real' | 'test_chat' | 'webhook_flag'), token_expiry (field key of an expiry date or null),
 *   built (false while the adapter is a stub).
 * FIELD spec: key, label, type (text | secret | select | date | url | multi), required (bool), when (['field' => 'value']: only needed when another field has
 *   this value), secret (bool, derived from type), env (name of the .env override or ''), pattern (regex of a valid value), options (value => label), default,
 *   placeholder, help, maxlength.
 */
abstract class AbstractChannel implements ChannelInterface
{
    protected const META = [];

    private const DEFAULT_META = [
        'id' => '', 'label' => '', 'icon' => 'globe', 'short' => '', 'limit' => 2000, 'soft' => 0, 'count' => 'plain', 'links' => 'clickable', 'uses_image' => false,
        'image_variant' => null, 'ambiguous' => 'review', 'needs' => '', 'setup_url' => '', 'steps' => [], 'permissions' => '', 'notes' => [], 'fields' => [],
        'test_post' => 'real', 'token_expiry' => null, 'built' => true,
    ];

    public function id(): string
    {
        return (string) $this->meta()['id'];
    }

    /** @return array<string,mixed> */
    public function meta(): array
    {
        $m = static::META + self::DEFAULT_META;
        $fields = [];
        foreach ($m['fields'] as $f) {
            $f += ['label' => $f['key'], 'type' => 'text', 'required' => false, 'when' => null, 'env' => '', 'pattern' => '', 'options' => [], 'default' => '', 'placeholder' => '', 'help' => '', 'maxlength' => 300];
            $f['secret'] = $f['type'] === 'secret';
            $fields[] = $f;
        }
        $m['fields'] = $fields;
        return $m;
    }

    /** @return array<string,string> */
    public function validate(array $input): array
    {
        $errors = [];
        foreach ($this->meta()['fields'] as $f) {
            $k = (string) $f['key'];
            if (!array_key_exists($k, $input)) {
                continue;
            }
            $v = $input[$k];
            if (!is_string($v) || trim($v) === '') {
                continue;
            }
            $v = trim($v);
            $label = (string) $f['label'];
            if (preg_match('/[\x00-\x1f\x7f]/', $v) === 1) {
                $errors[$k] = $label . ' contains characters that are not allowed.';
                continue;
            }
            if (mb_strlen($v) > (int) $f['maxlength']) {
                $errors[$k] = $label . ' is too long.';
                continue;
            }
            switch ($f['type']) {
                case 'select':
                    if (!array_key_exists($v, (array) $f['options'])) {
                        $errors[$k] = 'Choose one of the listed options for ' . $label . '.';
                    }
                    break;
                case 'date':
                    $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $v);
                    if ($d === false || $d->format('Y-m-d') !== $v || (int) $d->format('Y') < 2020 || (int) $d->format('Y') > 2100) {
                        $errors[$k] = $label . ' must be a date like 2026-12-31.';
                    }
                    break;
                case 'multi':
                    foreach (array_filter(array_map('trim', explode(',', $v))) as $one) {
                        if (!array_key_exists($one, (array) $f['options'])) {
                            $errors[$k] = 'Unknown choice in ' . $label . '.';
                            break;
                        }
                    }
                    break;
                default:
                    if ($f['pattern'] !== '' && preg_match((string) $f['pattern'], $v) !== 1) {
                        $errors[$k] = $label . ' does not look right' . ($f['type'] === 'secret' ? '.' : ($f['placeholder'] !== '' ? ' (for example ' . $f['placeholder'] . ').' : '.'));
                    }
            }
        }
        return $errors;
    }

    /**
     * Last touch on the stored values before an adapter sees them (for example LinkedIn: a bare number becomes "urn:li:organization:123").
     * @param array<string,string> $config
     * @return array<string,string>
     */
    public function normaliseConfig(array $config): array
    {
        return $config;
    }

    /** The field spec of one key, or null. @return ?array<string,mixed> */
    public function field(string $key): ?array
    {
        foreach ($this->meta()['fields'] as $f) {
            if ($f['key'] === $key) {
                return $f;
            }
        }
        return null;
    }

    // ------------------------------------------------------------------------------------------------ helpers for adapters

    /** A stub result for adapters that are not built yet. */
    protected function notBuilt(): SocialResult
    {
        return SocialResult::failed('The ' . $this->meta()['label'] . ' adapter is not built yet.', 'not_built');
    }

    /** @return array{ok:bool,state:string,message:string,detail?:string} */
    protected function ok(string $message, string $detail = ''): array
    {
        return ['ok' => true, 'state' => 'ok', 'message' => $message] + ($detail !== '' ? ['detail' => $detail] : []);
    }

    /** @return array{ok:bool,state:string,message:string} */
    protected function bad(string $message, string $state = 'error'): array
    {
        return ['ok' => false, 'state' => $state, 'message' => SocialHttp::redact($message)];
    }

    /** @param array<string,string> $config */
    protected function missing(array $config, string ...$keys): ?string
    {
        foreach ($keys as $k) {
            if (trim((string) ($config[$k] ?? '')) === '') {
                $f = $this->field($k);
                return (string) ($f['label'] ?? $k);
            }
        }
        return null;
    }
}
