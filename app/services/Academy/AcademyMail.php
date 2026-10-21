<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Log;
use App\Core\Mailer;

/**
 * E-mails of the LAF Academy: confirm your address, welcome, password reset, "you already have an account", certificate issued, class invitation / reminder.
 * Plain text plus a simple HTML version with one button. Names are reduced to safe text; headers cannot be injected (Mailer strips CR/LF).
 * Nothing here logs an address or a link (ids only).
 */
final class AcademyMail
{
    private static function site(): string
    {
        return trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
    }

    public static function supportEmail(): string
    {
        $e = trim((string) setting('academy_support_email', ''));
        return $e !== '' ? $e : trim((string) setting('email_info', ''));
    }

    private static function first(string $name): string
    {
        $n = trim((string) preg_replace('/[^\p{L}\p{M}\' \-]/u', '', $name));
        $n = explode(' ', $n)[0] ?? '';
        return $n !== '' ? mb_substr($n, 0, 40) : 'there';
    }

    /** @param list<string> $paragraphs plain text paragraphs */
    private static function send(string $to, string $subject, array $paragraphs, ?array $button = null, ?string $replyTo = null): bool
    {
        $site = self::site();
        $support = self::supportEmail();
        $text = implode("\n\n", $paragraphs);
        if ($button !== null) {
            $text .= "\n\n" . $button['label'] . ":\n" . $button['url'];
        }
        $text .= "\n\n" . $site . ' - LAF Academy' . ($support !== '' ? "\nNeed help? Write to " . $support : '');
        $html = '<!doctype html><html><body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#1d1e20">'
            . '<div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:14px;padding:28px">'
            . '<p style="margin:0 0 18px;font-size:13px;letter-spacing:.12em;text-transform:uppercase;color:#8a5a06;font-weight:700">' . htmlspecialchars($site . ' - LAF Academy', ENT_QUOTES) . '</p>';
        foreach ($paragraphs as $p) {
            $html .= '<p style="margin:0 0 16px;line-height:1.6;font-size:16px">' . nl2br(htmlspecialchars($p, ENT_QUOTES), false) . '</p>';
        }
        if ($button !== null) {
            $html .= '<p style="margin:22px 0"><a href="' . htmlspecialchars($button['url'], ENT_QUOTES) . '" style="background:#c8641b;color:#ffffff;text-decoration:none;font-weight:700;padding:13px 24px;border-radius:999px;display:inline-block">'
                . htmlspecialchars($button['label'], ENT_QUOTES) . '</a></p>'
                . '<p style="margin:0 0 16px;font-size:13px;color:#5a6470;word-break:break-all">If the button does not work, copy this address into your browser:<br>' . htmlspecialchars($button['url'], ENT_QUOTES) . '</p>';
        }
        $html .= '<p style="margin:18px 0 0;font-size:13px;color:#5a6470">' . htmlspecialchars($site, ENT_QUOTES) . ($support !== '' ? ' &middot; Need help? ' . htmlspecialchars($support, ENT_QUOTES) : '') . '</p></div></body></html>';
        $opts = ['html' => $html];
        if ($replyTo !== null && $replyTo !== '') {
            $opts['reply_to'] = $replyTo;
        } elseif ($support !== '' && Mailer::validEmail($support)) {
            $opts['reply_to'] = $support;
        }
        try {
            return Mailer::send($to, $subject, $text, $opts);
        } catch (\Throwable $e) {
            Log::error('Academy e-mail failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function verification(array $learner, string $url): bool
    {
        return self::send((string) $learner['email'], 'Confirm your e-mail address for the LAF Academy', [
            'Hello ' . self::first((string) $learner['name']) . ',',
            'Thank you for joining the LAF Academy. Please confirm that this e-mail address is yours so you can sign in and start learning.',
            'The link works for ' . LearnerAuth::VERIFY_HOURS . ' hours. If you did not create an account, you can ignore this message.',
        ], ['label' => 'Confirm my e-mail address', 'url' => $url]);
    }

    public static function welcome(array $learner): bool
    {
        return self::send((string) $learner['email'], 'Welcome to the LAF Academy', [
            'Hello ' . self::first((string) $learner['name']) . ',',
            'Your LAF Academy account is ready. Choose a course, learn at your own pace, take the quizzes and earn a certificate when you pass the final assessment.',
        ], ['label' => 'Browse the courses', 'url' => abs_url('academy#courses')]);
    }

    public static function alreadyRegistered(string $email, string $name): bool
    {
        if (!\App\Core\RateLimit::attempt('academy-exists-mail:' . sha1($email), 2, 86400)) {
            return false;
        }
        return self::send($email, 'You already have a LAF Academy account', [
            'Hello ' . self::first($name) . ',',
            'Someone (hopefully you) tried to create a LAF Academy account with this e-mail address, but an account already exists. You can sign in, or reset your password if you have forgotten it.',
            'If this was not you, you can ignore this message: nothing has changed.',
        ], ['label' => 'Sign in', 'url' => abs_url('academy/login')]);
    }

    public static function passwordReset(array $learner, string $url): bool
    {
        return self::send((string) $learner['email'], 'Reset your LAF Academy password', [
            'Hello ' . self::first((string) $learner['name']) . ',',
            'We received a request to reset the password of your LAF Academy account. The link below works for ' . LearnerAuth::RESET_MINUTES . ' minutes and only once.',
            'If you did not ask for this, ignore this message: your password stays as it is.',
        ], ['label' => 'Choose a new password', 'url' => $url]);
    }

    public static function certificate(array $cert, array $learner): bool
    {
        return self::send((string) $learner['email'], 'Your LAF Academy certificate: ' . mb_substr((string) $cert['course_title'], 0, 80), [
            'Congratulations ' . self::first((string) $learner['name']) . '!',
            'You have completed "' . $cert['course_title'] . '" and passed the final assessment. Your certificate is ready: you can view it, download it as a PDF and share the verification link with employers.',
            'Certificate number: ' . $cert['code'],
        ], ['label' => 'Open my certificate', 'url' => abs_url('academy/certificate/' . $cert['code'])]);
    }

    /** @param array<string,mixed> $class a row of academy_classes, @param array<string,mixed> $learner */
    public static function classNotice(array $class, array $learner, bool $reminder): bool
    {
        $when = format_datetime((string) $class['start_at'], 'l j F Y, H:i') . ' (East Africa Time)';
        return self::send((string) $learner['email'], ($reminder ? 'Reminder: ' : 'Virtual class: ') . mb_substr((string) $class['title'], 0, 100), [
            'Hello ' . self::first((string) $learner['name']) . ',',
            ($reminder ? 'A reminder that the virtual class' : 'You are invited to the virtual class') . ' "' . $class['title'] . '" takes place on ' . $when . '.',
            'Sign in to the LAF Academy to open the join link on the class page.',
        ], ['label' => 'Open the class page', 'url' => abs_url('academy/classes')]);
    }
}
