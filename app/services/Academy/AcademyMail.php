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
    private static ?string $lastError = null;

    /** Why the last e-mail of this class could not be sent (null after a success). Shown to administrators only. */
    public static function lastError(): ?string
    {
        return self::$lastError;
    }

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
            $html .= '<p style="margin:22px 0"><a href="' . htmlspecialchars($button['url'], ENT_QUOTES) . '" style="background:#d6a151;color:#172036;text-decoration:none;font-weight:700;padding:13px 24px;border-radius:999px;display:inline-block">'
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
            $ok = Mailer::send($to, $subject, $text, $opts);
            self::$lastError = $ok ? null : (Mailer::lastError() ?: 'The mail server did not accept the message.');
            return $ok;
        } catch (\Throwable $e) {
            Log::error('Academy e-mail failed: ' . $e->getMessage());
            self::$lastError = 'The e-mail could not be sent.';
            return false;
        }
    }

    public static function verification(array $learner, string $url, ?int $hours = null, ?string $code = null): bool
    {
        $paragraphs = [
            'Hello ' . self::first((string) $learner['name']) . ',',
            'Thank you for joining the LAF Academy. Please confirm that this e-mail address is yours so you can sign in and start learning.',
        ];
        if ($code !== null) {
            $paragraphs[] = 'Your confirmation code: ' . $code . '. If the button does not work, open ' . abs_url('academy/check-email') . ' and type this code. It works for ' . VerificationCode::HOURS . ' hours.';
        }
        $paragraphs[] = 'The link works for ' . ($hours ?? LearnerAuth::VERIFY_HOURS) . ' hours. If you did not create an account, you can ignore this message.';
        return self::send((string) $learner['email'], 'Confirm your e-mail address for the LAF Academy' . ($code !== null ? ' (code ' . $code . ')' : ''), $paragraphs, ['label' => 'Confirm my e-mail address', 'url' => $url]);
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

    /** The same words as a message the team wrote in the portal, as an e-mail (only when the administrator ticks "also send by e-mail"). */
    public static function teamMessage(array $learner, string $text): bool
    {
        return self::send((string) $learner['email'], 'A message from the LAF Academy team', [
            'Hello ' . self::first((string) $learner['name']) . ',',
            $text,
            'You can read this message and answer it in your learner portal.',
        ], ['label' => 'Open my messages', 'url' => abs_url('academy/portal/messages')]);
    }

    /** The application was rejected (only when the administrator ticks "also e-mail"; best effort). */
    public static function rejected(array $learner, string $reason): bool
    {
        return self::send((string) $learner['email'], 'About your LAF Academy application', [
            'Hello ' . self::first((string) $learner['name']) . ',',
            'Thank you for applying to the LAF Academy. We could not approve your application' . ($reason !== '' ? ': ' . $reason : '.'),
            'If you think this is a mistake, please write to the Academy team and we will look at it again.',
        ], ['label' => 'Message the Academy team', 'url' => abs_url('academy/contact')]);
    }

    /** An administrator approved the account (best effort: approval never depends on this e-mail). */
    public static function approved(array $learner): bool
    {
        return self::send((string) $learner['email'], 'Your LAF Academy account is approved', [
            'Hello ' . self::first((string) $learner['name']) . ',',
            'Your LAF Academy account is approved' . ((string) ($learner['reg_no'] ?? '') !== '' ? ' (registration number ' . $learner['reg_no'] . ')' : '') . '. You can sign in now with your e-mail address and password.',
        ], ['label' => 'Sign in', 'url' => abs_url('academy/login')]);
    }

    /** The learner passed and the certificate waits for the administrator's approval and signature. */
    public static function certificateWaiting(array $learner, array $course): bool
    {
        return self::send((string) $learner['email'], 'Congratulations: you completed ' . mb_substr((string) $course['title'], 0, 80), [
            'Congratulations ' . self::first((string) $learner['name']) . '!',
            'You passed the final exam and completed "' . $course['title'] . '".',
            'Your certificate is ready to be prepared. It is now waiting for approval and signing by the Academy administrator, and it is shared with you only after that.',
            'Please check your dashboard again after ' . CertificateService::waitHours() . ' hours: the certificate will be there to open and download.',
        ], ['label' => 'Open my dashboard', 'url' => abs_url('academy/dashboard')]);
    }

    public static function certificateDeclined(array $learner, array $cert, string $reason): bool
    {
        return self::send((string) $learner['email'], 'About your LAF Academy certificate request', [
            'Hello ' . self::first((string) $learner['name']) . ',',
            'Thank you for asking for your certificate for "' . $cert['course_title'] . '". The Academy team could not approve it yet.',
            $reason !== '' ? 'Message from the team: ' . $reason : 'Please check that you finished every lesson and passed the final exam, then request it again, or write to us.',
        ], ['label' => 'Open the Academy', 'url' => abs_url('academy/dashboard')]);
    }

    public static function certificate(array $cert, array $learner): bool
    {
        return self::send((string) $learner['email'], 'Your LAF Academy certificate: ' . mb_substr((string) $cert['course_title'], 0, 80), [
            'Congratulations ' . self::first((string) $learner['name']) . '!',
            'You have completed "' . $cert['course_title'] . '" and passed the final assessment. Your certificate is ready: you can view it, download it as a PDF and share the verification link with employers.',
            'Certificate number: ' . $cert['code'],
        ], ['label' => 'Open my certificate', 'url' => abs_url('academy/certificate/' . $cert['code'])]);
    }

    /** Receipt of a certificate payment (Pesapal). Amount and reference only: no card or phone number is ever stored or e-mailed. */
    public static function paymentReceipt(array $payment, array $learner, bool $certificateWaits): bool
    {
        $amount = number_format((float) $payment['amount'], 0) . ' ' . $payment['currency'];
        $next = $certificateWaits
            ? 'Your certificate request is now with the Academy team, who approve and sign it. You will get an e-mail as soon as it is ready.'
            : 'Your certificate is on its way: open your Academy portal to see it.';
        return self::send((string) $learner['email'], 'Payment received: ' . mb_substr((string) $payment['course_title'], 0, 80), [
            'Thank you ' . self::first((string) $learner['name']) . '!',
            'We received your payment of ' . $amount . ' for the certificate of "' . $payment['course_title'] . '".',
            'Reference: ' . $payment['merchant_ref'] . (!empty($payment['confirmation_code']) ? "\nPesapal confirmation: " . $payment['confirmation_code'] : ''),
            $next,
        ], ['label' => 'Open my certificates', 'url' => abs_url('academy/portal/certificates')]);
    }

    // ------------------------------------------------------------------------------------------------------------- volunteer facilitators

    public static function facilitatorReceived(array $app): bool
    {
        return self::send((string) $app['email'], 'We received your application to facilitate at the LAF Academy', [
            'Hello ' . self::first((string) $app['name']) . ',',
            'Thank you for offering your time as a volunteer facilitator for "' . $app['topic_detail'] . '". We have received your application, your CV and your papers.',
            'An administrator will review them and write to you. This is a voluntary role: facilitators are not paid, and every approved volunteer receives a certificate of appreciation on request.',
        ]);
    }

    /** @param array{name:string,email:string,password:string}|null $creds the temporary sign-in details to e-mail (approval only) */
    public static function facilitatorDecision(array $app, string $status, string $note, ?array $creds = null): bool
    {
        $first = 'Hello ' . self::first((string) $app['name']) . ',';
        $noteLine = trim($note) !== '' ? 'Message from the Academy team: ' . trim($note) : null;
        if ($status === 'approved') {
            $p = [$first, 'Good news: your application to facilitate "' . $app['topic_detail'] . '" at the LAF Academy has been approved. Thank you for volunteering your time.'];
            if ($creds !== null) {
                $p[] = "Your facilitator account is ready.\nSign in address: " . abs_url('admin/login') . "\nE-mail: " . $creds['email'] . "\nTemporary password: " . $creds['password'] . "\nYou will be asked to choose your own password the first time you sign in. Please delete this e-mail afterwards.";
            } else {
                $p[] = 'You can sign in with your existing account: your Academy menu is now available.';
            }
            if ($noteLine !== null) {
                $p[] = $noteLine;
            }
            return self::send((string) $app['email'], 'Your LAF Academy facilitator application was approved', $p);
        }
        if ($status === 'more_info') {
            $p = [$first, 'We are reviewing your application to facilitate "' . $app['topic_detail'] . '" and need a little more information before we can decide.'];
            $p[] = $noteLine ?? 'Please reply to this e-mail with the missing details.';
            return self::send((string) $app['email'], 'LAF Academy: we need more information', $p, null, self::supportEmail());
        }
        $p = [$first, 'Thank you for applying to facilitate "' . $app['topic_detail'] . '" at the LAF Academy. After careful review we are not able to approve the application at this time.'];
        if ($noteLine !== null) {
            $p[] = $noteLine;
        }
        $p[] = 'You are welcome to apply again later, and you can keep learning for free in the Academy.';
        return self::send((string) $app['email'], 'Your LAF Academy facilitator application', $p);
    }

    /** A short notice to the Academy team address. */
    public static function staffNotice(string $subject, array $paragraphs, string $path): bool
    {
        $to = self::supportEmail();
        if ($to === '' || !Mailer::validEmail($to)) {
            return false;
        }
        return self::send($to, $subject, $paragraphs, ['label' => 'Open in the admin', 'url' => abs_url($path)]);
    }

    // ------------------------------------------------------------------------------------------------------------- challenges

    public static function challengeEntry(array $challenge, array $learner, string $status, string $note = ''): bool
    {
        $first = 'Hello ' . self::first((string) $learner['name']) . ',';
        $url = abs_url('academy/challenges/' . $challenge['slug']);
        if ($status === 'approved') {
            return self::send((string) $learner['email'], 'You are in: ' . mb_substr((string) $challenge['title'], 0, 90), [
                $first, 'Your entry to "' . $challenge['title'] . '" has been approved. Open the challenge page when it starts to take part.',
            ], ['label' => 'Open the challenge', 'url' => $url]);
        }
        $p = [$first, 'Thank you for applying to "' . $challenge['title'] . '". We are not able to accept your entry this time.'];
        if (trim($note) !== '') {
            $p[] = 'Message from the Academy team: ' . trim($note);
        }
        return self::send((string) $learner['email'], 'About your entry to ' . mb_substr((string) $challenge['title'], 0, 90), $p, ['label' => 'See other challenges', 'url' => abs_url('academy/challenges')]);
    }

    /** @param array<string,mixed> $cert the certificate row */
    public static function challengeResult(array $challenge, array $entry, array $learner, ?array $cert): bool
    {
        $first = 'Hello ' . self::first((string) $learner['name']) . ',';
        if (!empty($entry['is_winner'])) {
            $p = [$first . ' Congratulations!', 'You won ' . ($entry['award'] ?: 'a place') . ' in "' . $challenge['title'] . '". You will be featured as a winner of the month on the LAF Academy.'];
        } else {
            $p = [$first, 'Thank you for taking part in "' . $challenge['title'] . '". The results are final: you can see the ranking on the challenge page.'];
        }
        if ($cert !== null) {
            $p[] = 'Your certificate number is ' . $cert['code'] . '. You can view it, download the PDF and share the verification link.';
        }
        return self::send((string) $learner['email'], (!empty($entry['is_winner']) ? 'You won: ' : 'Results: ') . mb_substr((string) $challenge['title'], 0, 90), $p,
            ['label' => $cert !== null ? 'Open my certificate' : 'See the results', 'url' => $cert !== null ? abs_url('academy/certificate/' . $cert['code']) : abs_url('academy/challenges/' . $challenge['slug'])]);
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
