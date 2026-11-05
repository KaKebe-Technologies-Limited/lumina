<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * Approved learners who have NOT STARTED A COURSE: the account is approved and active, but there is no completed lesson and no quiz attempt (a learner who only enrolled but never
 * opened a lesson counts as not started). The Academy team reminds them by portal message + e-mail, and on WhatsApp (sent from the site when the WhatsApp Business API and the template
 * WHATSAPP_NOT_STARTED_TEMPLATE are set up, otherwise one click-to-chat link per learner with the message typed).
 */
final class NotStarted
{
    public const TEMPLATE_ENV = 'WHATSAPP_NOT_STARTED_TEMPLATE';

    /** SQL condition (alias l) for "approved, active, nothing started". */
    public const WHERE = "l.is_active = 1 AND l.email_verified_at IS NOT NULL AND NOT EXISTS (SELECT 1 FROM academy_progress gp WHERE gp.learner_id = l.id) AND NOT EXISTS (SELECT 1 FROM academy_attempts ga WHERE ga.learner_id = l.id)";

    /** @return list<array<string,mixed>> */
    public static function learners(): array
    {
        try {
            return Database::all('SELECT l.id, l.name, l.email, l.phone, l.country, ' . (VerificationCode::ready() ? 'l.whatsapp' : 'NULL AS whatsapp') . ', ' . (LearnerRegistry::ready() ? 'l.reg_no' : 'NULL AS reg_no') . ' FROM academy_learners l WHERE ' . self::WHERE . ' ORDER BY l.id');
        } catch (\Throwable) {
            return [];
        }
    }

    public static function defaultMessage(): string
    {
        return "Hello {name}, this is the LAF Academy team. Your account is approved, but you have not started a course yet. Start today: sign in, open Computer Basics and Digital Literacy (or any course you like) and do your first lesson. "
            . "Each lesson takes only a few minutes, and you earn a free certificate when you pass the final exam. Sign in here: " . abs_url('academy/login');
    }

    /** The short text used on WhatsApp. */
    public static function whatsAppText(array $learner): string
    {
        $first = trim((string) strtok(trim((string) $learner['name']), ' ')) ?: 'there';
        return 'Hello ' . $first . ', this is the LAF Academy team. Your account is approved but you have not started a course yet. Sign in and start your first lesson today: ' . abs_url('academy/login');
    }

    /** Digits of the learner's WhatsApp number (or phone), or ''. */
    public static function number(array $learner): string
    {
        $n = (string) preg_replace('/\D+/', '', (string) ($learner['whatsapp'] ?? ''));
        if ($n !== '') {
            return (string) WhatsAppSender::normalise('+' . $n);
        }
        return (string) WhatsAppSender::normalise((string) ($learner['phone'] ?? ''));
    }

    /** Click-to-chat link with the message typed, or null when the learner has no usable number. */
    public static function whatsAppUrl(array $learner): ?string
    {
        $n = self::number($learner);
        return strlen($n) >= 8 ? 'https://wa.me/' . $n . '?text=' . rawurlencode(self::whatsAppText($learner)) : null;
    }

    /** Portal message (and e-mail when asked) to every learner who has not started. @return int how many learners were written to */
    public static function remind(?int $adminId, string $text, bool $email): int
    {
        $sent = 0;
        foreach (self::learners() as $l) {
            $first = trim((string) strtok(trim((string) $l['name']), ' ')) ?: 'there';
            $body = str_replace('{name}', $first, $text);
            if (!LearnerMessages::send((int) $l['id'], 'admin', $body, $adminId)['ok']) {
                continue;
            }
            if ($email) {
                AcademyMail::teamMessage($l, $body);
            }
            $sent++;
        }
        return $sent;
    }

    /** Is the WhatsApp template reminder set up (API + template)? */
    public static function whatsAppApiReady(): bool
    {
        return WhatsAppSender::templateEnabled(self::TEMPLATE_ENV);
    }

    /**
     * Send the approved template to every learner who has not started ({{1}} first name, {{2}} sign-in link).
     * @return array{sent:int,failed:int,nonumber:int,error:?string}
     */
    public static function whatsAppAll(): array
    {
        $out = ['sent' => 0, 'failed' => 0, 'nonumber' => 0, 'error' => null];
        foreach (self::learners() as $l) {
            $n = self::number($l);
            if ($n === '') {
                $out['nonumber']++;
                continue;
            }
            $first = trim((string) strtok(trim((string) $l['name']), ' ')) ?: 'there';
            $r = WhatsAppSender::sendReminder($n, [$first, abs_url('academy/login')], self::TEMPLATE_ENV);
            if ($r['ok']) {
                $out['sent']++;
            } else {
                $out['failed']++;
                $out['error'] ??= $r['error'];
            }
        }
        return $out;
    }
}
