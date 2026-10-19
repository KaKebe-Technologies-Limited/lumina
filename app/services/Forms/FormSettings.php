<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\Mailer;
use App\Core\Sanitizer;

/**
 * THE FORM SETTINGS (forms.settings, JSON version 1): everything about a form that is not its questions. Contract: docs/FORMS.md section 4.
 * normalise() is the only gate (like FormDefinition::normalise): unknown keys are dropped, texts are plain or sanitised, lists are capped.
 * The columns opens_at / closes_at / max_submissions / listed / slug / title / description live on the `forms` row, not here.
 *
 * Empty text settings mean "use the default wording from Admin > Pages > Forms" (the registry page `forms`), so the owner can change the
 * defaults for every form at once and still override a single form.
 */
final class FormSettings
{
    public const VERSION = 1;
    public const KINDS = ['general', 'job', 'intake', 'survey'];
    public const ACCENTS = ['orange', 'navy', 'teal', 'gold'];
    public const SPAM_LEVELS = ['light', 'standard', 'strict'];
    public const LAYOUTS = ['single', 'stepped'];
    public const STATUSES = ['new', 'read', 'starred', 'shortlisted', 'rejected', 'accepted', 'archived', 'spam'];

    /** @return array<string,mixed> */
    public static function defaults(string $kind = 'general'): array
    {
        return [
            'version'        => self::VERSION,
            'kind'           => in_array($kind, self::KINDS, true) ? $kind : 'general',
            'intro'          => '',                                   // sanitised rich text above the questions
            'submit_label'   => '',                                   // '' = registry default
            'success'        => ['mode' => 'message', 'message_html' => '', 'redirect_path' => ''],
            'closed_message' => '',                                   // '' = registry default wording (plain text)
            'full_message'   => '',
            'not_open_message' => '',
            'unavailable_message' => '',
            'consent'        => ['required' => false, 'text' => ''],  // a form-level consent tick box (text '' = registry default)
            'privacy_note'   => '',                                   // '' = registry default (links to /privacy)
            'one_per_email'  => false,
            'spam'           => 'standard',
            'throttle_form'  => 0,                                    // extra: max submissions per form per 10 minutes (0 = off)
            'confirmation'   => ['enabled' => false, 'email_field' => '', 'subject' => '', 'body' => ''],
            'notify'         => ['enabled' => true, 'emails' => [], 'include_answers' => false],
            'theme'          => ['accent' => 'orange', 'layout' => 'stepped', 'progress' => true],
            'retention_days' => 0,                                    // 0 = keep until deleted
            'seo'            => ['title' => '', 'description' => '', 'noindex' => in_array($kind, ['job', 'intake'], true)],
            'status_labels'  => [],                                   // only the labels that differ from the defaults
            'allow_print'    => true,
        ];
    }

    /** Default status labels (admin wording). Job forms say "Hired" instead of "Accepted". @return array<string,string> */
    public static function defaultStatusLabels(string $kind = 'general'): array
    {
        $labels = ['new' => 'New', 'read' => 'Read', 'starred' => 'Starred', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'accepted' => 'Accepted', 'archived' => 'Archived', 'spam' => 'Spam'];
        if ($kind === 'job') {
            $labels['accepted'] = 'Hired';
        }
        return $labels;
    }

    /** Status labels of one form: defaults for its kind with the form's own labels on top. @return array<string,string> */
    public static function statusLabels(array $settings): array
    {
        $labels = self::defaultStatusLabels((string) ($settings['kind'] ?? 'general'));
        foreach ((array) ($settings['status_labels'] ?? []) as $k => $v) {
            if (isset($labels[$k]) && is_string($v) && trim($v) !== '') {
                $labels[$k] = $v;
            }
        }
        return $labels;
    }

    public static function encode(array $settings): string
    {
        return (string) json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /** Decode + normalise stored JSON (never throws; invalid JSON gives the defaults). */
    public static function decode(?string $json, string $kind = 'general'): array
    {
        if ($json === null || trim($json) === '') {
            return self::defaults($kind);
        }
        return self::normalise($json, $kind)['settings'];
    }

    /**
     * Validate + normalise settings (array or JSON string). Missing keys take their defaults, so a partial object is fine.
     * errors: path => message (an invalid redirect path, a bad e-mail address, too many addresses). Invalid parts are reset to the default and
     * reported, so `settings` is always usable.
     *
     * @return array{ok:bool,settings:array<string,mixed>,errors:array<string,string>,warnings:list<string>}
     */
    public static function normalise(mixed $raw, string $kindFallback = 'general'): array
    {
        $errors = [];
        $warnings = [];
        if (is_string($raw)) {
            if (strlen($raw) > (int) config('form_builder.max_settings_kb', 64) * 1024) {
                return ['ok' => false, 'settings' => self::defaults($kindFallback), 'errors' => ['settings' => 'The settings are too large.'], 'warnings' => []];
            }
            $raw = json_decode($raw, true, 12);
        }
        if (!is_array($raw)) {
            $raw = [];
        }
        $kind = in_array($raw['kind'] ?? null, self::KINDS, true) ? (string) $raw['kind'] : $kindFallback;
        $d = self::defaults($kind);
        $plain = static fn(mixed $v, int $max, bool $multi = false): string => FormDefinition::plain($v, $max, $multi);
        $bool = static fn(mixed $v, bool $default): bool => $v === null ? $default : ($v === true || $v === 1 || $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes');

        $s = $d;
        $s['kind'] = $kind;
        $s['intro'] = trim(Sanitizer::html(is_string($raw['intro'] ?? null) ? mb_substr($raw['intro'], 0, 8000) : ''));
        $s['submit_label'] = $plain($raw['submit_label'] ?? '', 60);
        $s['closed_message'] = $plain($raw['closed_message'] ?? '', 600, true);
        $s['full_message'] = $plain($raw['full_message'] ?? '', 600, true);
        $s['not_open_message'] = $plain($raw['not_open_message'] ?? '', 600, true);
        $s['unavailable_message'] = $plain($raw['unavailable_message'] ?? '', 600, true);
        $s['privacy_note'] = $plain($raw['privacy_note'] ?? '', 600, true);
        $s['one_per_email'] = $bool($raw['one_per_email'] ?? null, false);
        $s['spam'] = in_array($raw['spam'] ?? null, self::SPAM_LEVELS, true) ? (string) $raw['spam'] : 'standard';
        $s['throttle_form'] = max(0, min(1000, (int) ($raw['throttle_form'] ?? 0)));
        $s['allow_print'] = $bool($raw['allow_print'] ?? null, true);

        // ---- success
        $su = is_array($raw['success'] ?? null) ? $raw['success'] : [];
        $mode = ($su['mode'] ?? 'message') === 'redirect' ? 'redirect' : 'message';
        $redirect = '';
        if ($mode === 'redirect' || trim((string) ($su['redirect_path'] ?? '')) !== '') {
            $candidate = Sanitizer::localPath(is_string($su['redirect_path'] ?? null) ? $su['redirect_path'] : '');
            if ($candidate !== null && $candidate !== '' && strlen($candidate) <= 300 && $candidate[0] === '/'
                && !preg_match('~^/(admin|assets|uploads|storage)(/|$)~i', $candidate) && !str_contains($candidate, '//')) {
                $redirect = $candidate;
            } elseif (trim((string) ($su['redirect_path'] ?? '')) !== '') {
                $errors['success.redirect_path'] = 'Enter a page on this website, for example /thank-you.';
            }
        }
        $s['success'] = [
            'mode'          => $mode,
            'message_html'  => trim(Sanitizer::html(is_string($su['message_html'] ?? null) ? mb_substr($su['message_html'], 0, 6000) : '')),
            'redirect_path' => $redirect,
        ];

        // ---- consent
        $co = is_array($raw['consent'] ?? null) ? $raw['consent'] : [];
        $s['consent'] = ['required' => $bool($co['required'] ?? null, false), 'text' => $plain($co['text'] ?? '', 500, true)];

        // ---- confirmation e-mail to the submitter
        $cf = is_array($raw['confirmation'] ?? null) ? $raw['confirmation'] : [];
        $efield = is_string($cf['email_field'] ?? null) && preg_match(FormDefinition::ID_PATTERN, $cf['email_field']) === 1 ? $cf['email_field'] : '';
        $s['confirmation'] = [
            'enabled'     => $bool($cf['enabled'] ?? null, false),
            'email_field' => $efield,
            'subject'     => $plain($cf['subject'] ?? '', 150),
            'body'        => $plain($cf['body'] ?? '', 4000, true),
        ];

        // ---- notification e-mails to staff
        $nf = is_array($raw['notify'] ?? null) ? $raw['notify'] : [];
        $emails = [];
        $list = $nf['emails'] ?? [];
        if (is_string($list)) {
            $list = preg_split('/[\s,;]+/', $list) ?: [];
        }
        $max = (int) config('form_builder.staff_emails_max', 10);
        foreach ((array) $list as $i => $addr) {
            $addr = strtolower(trim((string) $addr));
            if ($addr === '') {
                continue;
            }
            if (!Mailer::validEmail($addr) || strlen($addr) > 190) {
                $errors['notify.emails.' . $i] = '"' . mb_substr($plain($addr, 60), 0, 60) . '" is not a valid e-mail address.';
                continue;
            }
            if (!in_array($addr, $emails, true)) {
                $emails[] = $addr;
            }
        }
        if (count($emails) > $max) {
            $errors['notify.emails'] = 'Use at most ' . $max . ' notification addresses.';
            $emails = array_slice($emails, 0, $max);
        }
        $s['notify'] = ['enabled' => $bool($nf['enabled'] ?? null, true), 'emails' => $emails, 'include_answers' => $bool($nf['include_answers'] ?? null, false)];

        // ---- theme
        $th = is_array($raw['theme'] ?? null) ? $raw['theme'] : [];
        $s['theme'] = [
            'accent'   => in_array($th['accent'] ?? null, self::ACCENTS, true) ? (string) $th['accent'] : 'orange',
            'layout'   => in_array($th['layout'] ?? null, self::LAYOUTS, true) ? (string) $th['layout'] : 'stepped',
            'progress' => $bool($th['progress'] ?? null, true),
        ];

        $s['retention_days'] = max(0, min((int) config('form_builder.retention_max_days', 3650), (int) ($raw['retention_days'] ?? 0)));

        // ---- SEO
        $se = is_array($raw['seo'] ?? null) ? $raw['seo'] : [];
        $s['seo'] = [
            'title'       => $plain($se['title'] ?? '', 120),
            'description' => $plain($se['description'] ?? '', 300),
            'noindex'     => $bool($se['noindex'] ?? null, in_array($kind, ['job', 'intake'], true)),
        ];

        // ---- status labels (only known statuses, only labels that differ from the default)
        $labels = [];
        $def = self::defaultStatusLabels($kind);
        foreach ((array) ($raw['status_labels'] ?? []) as $k => $v) {
            if (is_string($k) && isset($def[$k])) {
                $label = $plain($v, 30);
                if ($label !== '' && $label !== $def[$k]) {
                    $labels[$k] = $label;
                }
            }
        }
        $s['status_labels'] = $labels;

        return ['ok' => $errors === [], 'settings' => $s, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Who is e-mailed about a new submission: the form's own list, else Site settings > Forms, else the site notification address.
     * @return list<string>
     */
    public static function staffRecipients(array $settings): array
    {
        $own = array_values(array_filter((array) ($settings['notify']['emails'] ?? []), 'is_string'));
        if ($own !== []) {
            return $own;
        }
        $out = [];
        foreach (['forms_default_notify', 'notify_email'] as $key) {
            $raw = '';
            try {
                $raw = (string) setting($key, '');
            } catch (\Throwable) {
            }
            foreach (preg_split('/[\s,;]+/', $raw) ?: [] as $addr) {
                $addr = strtolower(trim($addr));
                if ($addr !== '' && Mailer::validEmail($addr) && !in_array($addr, $out, true)) {
                    $out[] = $addr;
                }
            }
            if ($out !== []) {
                return $out;
            }
        }
        return [];
    }
}
