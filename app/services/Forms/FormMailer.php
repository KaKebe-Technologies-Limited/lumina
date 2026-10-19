<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;

/**
 * E-MAIL of the forms module: the notification to staff and the confirmation to the submitter. Contract: docs/FORMS.md section 10.
 * Every message goes through App\Core\Mailer (header-injection safe, addresses validated, never throws). Nothing here throws either: a mail problem
 * never loses a submission. Log lines carry ids and counts only, never addresses or answers.
 *
 * STAFF NOTIFICATION: default = a LINK to the submission (the answers stay behind the admin login); the form can opt in to include the answers.
 * CONFIRMATION: only to the address the submitter typed in the form's chosen e-mail question, rate-limited per address and per form (config
 * form_builder.confirm_per_address / confirm_per_form), so a form can never be used to spam third parties. Text comes from the form's own subject /
 * body (placeholders) or the editable defaults (Admin > Pages > Forms > E-mails).
 *
 * PLACEHOLDERS (single pass, `{name}`): {form_title} {site} {date} {reference} {submission_id} and one per question key, e.g. {full_name}. Question values are
 * inserted as ONE line of at most 100 characters with web links removed; {link} (staff mail only) is the admin URL. Unknown placeholders stay as typed.
 */
final class FormMailer
{
    /** @return array{sent:int,skipped:?string} */
    public static function notifyStaff(array $form, int $submissionId, array $snapshot, ?string $replyTo = null): array
    {
        $cfg = (array) $form['cfg'];
        if (empty($cfg['notify']['enabled'])) {
            return ['sent' => 0, 'skipped' => 'disabled'];
        }
        $to = FormSettings::staffRecipients($cfg);
        if ($to === []) {
            return ['sent' => 0, 'skipped' => 'no_recipient'];
        }
        [$max, $window] = array_pad((array) config('form_builder.notify_per_form', [60, 3600]), 2, 3600);
        if (!RateLimit::attempt('forms:notify:' . (int) $form['id'], (int) $max, (int) $window)) {
            return ['sent' => 0, 'skipped' => 'rate_limited'];
        }
        $vars = self::placeholders($form, $snapshot, $submissionId) + ['link' => abs_url('admin/forms/' . (int) $form['id'] . '/submissions/' . $submissionId)];
        $subject = self::oneLine(FormText::fill(FormText::get('email.staff_subject'), $vars), 190);
        $lines = [FormText::fill(FormText::get('email.staff_intro'), $vars), '', FormText::get('email.staff_link_line'), $vars['link'], ''];
        if (!empty($cfg['notify']['include_answers'])) {
            $lines[] = FormText::get('email.staff_answers_heading');
            foreach ($snapshot as $e) {
                $lines[] = '- ' . $e['label'] . ': ' . mb_substr(str_replace("\r", '', (string) $e['text']), 0, 1500);
            }
        } else {
            $lines[] = FormText::get('email.staff_privacy_note');
        }
        $text = implode("\n", $lines);
        $sent = 0;
        foreach ($to as $addr) {
            $opts = ['html' => self::html($text)];
            if ($replyTo !== null && Mailer::validEmail($replyTo)) {
                $opts['reply_to'] = $replyTo;
            }
            if (Mailer::send($addr, $subject, $text, $opts)) {
                $sent++;
            }
        }
        if ($sent === 0) {
            Log::warning('Forms: staff notification not sent', ['form' => (int) $form['id'], 'submission' => $submissionId]);
        }
        return ['sent' => $sent, 'skipped' => $sent === 0 ? 'failed' : null];
    }

    /** @return array{sent:bool,skipped:?string} */
    public static function confirmSubmitter(array $form, int $submissionId, array $snapshot, ?string $email): array
    {
        $cfg = (array) $form['cfg'];
        if (empty($cfg['confirmation']['enabled'])) {
            return ['sent' => false, 'skipped' => 'disabled'];
        }
        $email = $email !== null ? strtolower(trim($email)) : '';
        if ($email === '' || !Mailer::validEmail($email)) {
            return ['sent' => false, 'skipped' => 'no_address'];
        }
        [$perAddr, $winA] = array_pad((array) config('form_builder.confirm_per_address', [3, 3600]), 2, 3600);
        [$perForm, $winF] = array_pad((array) config('form_builder.confirm_per_form', [120, 3600]), 2, 3600);
        // the limits use the CANONICAL address (no +tag, no Gmail dots), so name+1@, name+2@ and n.ame@ are one mailbox
        $canon = self::canonicalAddress($email);
        if (!RateLimit::attempt('forms:confirm:addr:' . hash('sha256', $canon), (int) $perAddr, (int) $winA)
            || !RateLimit::attempt('forms:confirm:form:' . (int) $form['id'], (int) $perForm, (int) $winF)) {
            return ['sent' => false, 'skipped' => 'rate_limited'];
        }
        $vars = self::placeholders($form, $snapshot, $submissionId) + ['link' => ''];
        $subjectT = trim((string) ($cfg['confirmation']['subject'] ?? '')) !== '' ? (string) $cfg['confirmation']['subject'] : FormText::get('email.confirm_subject');
        $bodyT = trim((string) ($cfg['confirmation']['body'] ?? '')) !== '' ? (string) $cfg['confirmation']['body'] : FormText::get('email.confirm_body');
        $subject = self::oneLine(FormText::fill($subjectT, $vars), 190);
        $text = FormText::fill($bodyT, $vars);
        $opts = ['html' => self::html($text)];
        foreach (['email_primary', 'email_info'] as $k) {
            $reply = '';
            try {
                $reply = (string) setting($k, '');
            } catch (\Throwable) {
            }
            if ($reply !== '' && Mailer::validEmail($reply)) {
                $opts['reply_to'] = $reply;
                break;
            }
        }
        $ok = Mailer::send($email, $subject, $text, $opts);
        if (!$ok) {
            Log::warning('Forms: confirmation not sent', ['form' => (int) $form['id'], 'submission' => $submissionId]);
        }
        return ['sent' => $ok, 'skipped' => $ok ? null : 'failed'];
    }

    /**
     * Placeholder map of one submission (values are made safe for a mail body: one line, <= 100 characters, no web links).
     * @param list<array<string,mixed>> $snapshot
     * @return array<string,string>
     */
    public static function placeholders(array $form, array $snapshot, int $submissionId): array
    {
        $vars = [];
        foreach ($snapshot as $e) {
            $key = (string) ($e['key'] ?? '');
            if ($key !== '' && !isset($vars[$key])) {
                $vars[$key] = self::safeValue((string) ($e['text'] ?? ''));
            }
        }
        $site = '';
        try {
            $site = (string) setting('site_name', '');
        } catch (\Throwable) {
        }
        // the fixed names win over a question key (FormDefinition::RESERVED_KEYS keeps keys from colliding anyway)
        $fixed = [
            'form_title'    => self::oneLine((string) $form['title'], 190),
            'site'          => $site,
            'date'          => FormService::now()->format('j F Y'),
            'reference'     => SubmissionService::reference((int) $form['id'], $submissionId),
            'submission_id' => (string) $submissionId,
        ];
        return $fixed + $vars;
    }

    /**
     * The mailbox an address really delivers to, for rate limits: lower case, "+tag" removed from the local part, and for Gmail the dots removed (googlemail.com = gmail.com).
     */
    public static function canonicalAddress(string $email): string
    {
        $email = strtolower(trim($email));
        $at = strrpos($email, '@');
        if ($at === false) {
            return $email;
        }
        $local = substr($email, 0, $at);
        $domain = substr($email, $at + 1);
        $plus = strpos($local, '+');
        if ($plus !== false && $plus > 0) {
            $local = substr($local, 0, $plus);
        }
        if ($domain === 'googlemail.com') {
            $domain = 'gmail.com';
        }
        if ($domain === 'gmail.com') {
            $local = str_replace('.', '', $local);
        }
        return $local . '@' . $domain;
    }

    /** One line, no control characters, no web links, at most 100 characters. */
    public static function safeValue(string $v): string
    {
        $v = (string) preg_replace('~(?:https?://|www\.)\S+~i', '', $v);
        return self::oneLine($v, 100);
    }

    public static function oneLine(string $s, int $max): string
    {
        $s = (string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $s);
        $s = trim((string) preg_replace('/\s+/u', ' ', $s));
        return mb_substr($s, 0, $max);
    }

    private static function html(string $text): string
    {
        $paras = array_filter(array_map('trim', preg_split("/\n{2,}/", str_replace("\r", '', $text)) ?: []), static fn(string $p): bool => $p !== '');
        $out = '';
        foreach ($paras as $p) {
            $e = htmlspecialchars($p, ENT_QUOTES, 'UTF-8');
            $e = (string) preg_replace('~(https?://[^\s<]+)~', '<a href="$1">$1</a>', $e);
            $out .= '<p>' . nl2br($e, false) . '</p>';
        }
        return $out;
    }
}
