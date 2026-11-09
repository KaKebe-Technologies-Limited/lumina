<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\AuditLog;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Services\Academy\GoogleSignIn;

/**
 * ADMIN > E-MAIL AND SIGN-IN (ability settings.manage): is the website's e-mail connected (SMTP, e.g. Gmail), a "send a test message" button, the DNS records that keep the mail out of
 * spam (SPF, DMARC), and the state of "Continue with Google" for the Academy (with the exact address to paste into the Google console). The passwords and the secret live ONLY in the
 * server's .env file: this page never shows them and never stores them. Routes: GET admin/connections, POST admin/connections/test-mail.
 */
final class ConnectionsController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $driver = Mailer::driver();
        $smtp = (array) Config::get('mail.smtp', []);
        $from = trim((string) Config::get('mail.from', ''));
        $domain = str_contains($from, '@') ? strtolower(substr($from, strrpos($from, '@') + 1)) : '';
        $user = trim((string) ($smtp['username'] ?? ''));
        $google = GoogleSignIn::cfg();
        $host = (string) parse_url((string) Config::get('app.url', ''), PHP_URL_HOST);
        return $this->render('admin/connections/index', [
            'driver'   => $driver,
            'smtpHost' => trim((string) ($smtp['host'] ?? '')),
            'smtpPort' => (int) ($smtp['port'] ?? 0),
            'smtpEnc'  => (string) ($smtp['encryption'] ?? ''),
            'smtpUser' => self::mask($user),
            'smtpSet'  => $driver === 'smtp' && trim((string) ($smtp['host'] ?? '')) !== '' && $user !== '',
            'gmail'    => str_contains(strtolower((string) ($smtp['host'] ?? '')), 'gmail.com') || str_contains(strtolower((string) ($smtp['host'] ?? '')), 'google'),
            'from'     => $from,
            'fromName' => (string) Config::get('mail.from_name', ''),
            'notify'   => trim((string) Settings::get('notify_email', '')) ?: trim((string) Settings::get('email_primary', '')),
            'dns'      => $domain !== '' ? self::dns($domain) : null,
            'domain'   => $domain,
            'googleOn' => GoogleSignIn::enabled(),
            'googleId' => self::mask($google['id'], 6),
            'redirect' => GoogleSignIn::redirectUri(),
            'origin'   => $host !== '' ? rtrim((string) Config::get('app.url', ''), '/') : '',
            'academyOn' => (string) Settings::get('academy_enabled', '1') === '1',
        ], ['title' => 'E-mail and sign-in', 'nav' => 'connections', 'breadcrumbs' => [['E-mail and sign-in', null]]]);
    }

    /** POST: send a short test message to the address typed in (the answer says exactly what the mail server said, never the password). */
    public function testMail(Request $request): Response
    {
        $this->authorize('settings.manage');
        $to = trim($request->string('to'));
        if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            Flash::error('Type a valid e-mail address to send the test to.');
            return $this->redirect('admin/connections');
        }
        if (RateLimit::tooMany('conn-test:' . $request->ip(), 6)) {
            Flash::error('Please wait a few minutes before sending more test messages.');
            return $this->redirect('admin/connections');
        }
        RateLimit::hit('conn-test:' . $request->ip(), 600);
        $site = (string) Settings::get('site_name', 'Lumina Africa Foundation');
        $r = Mailer::attempt($to, 'Test message from ' . $site, "This is a test message from the " . $site . " website.\n\nIf you can read this, the website can send e-mail: sign-up codes, contact-form replies, newsletters and notices will reach people.\n\nSent " . date('j M Y H:i') . ' (' . Mailer::driver() . ").", [
            'html' => '<p>This is a test message from the <strong>' . htmlspecialchars($site, ENT_QUOTES) . '</strong> website.</p><p>If you can read this, the website can send e-mail: sign-up codes, contact-form replies, newsletters and notices will reach people.</p><p style="color:#667">Sent ' . date('j M Y H:i') . ' (' . htmlspecialchars(Mailer::driver(), ENT_QUOTES) . ').</p>',
        ]);
        AuditLog::record('settings.manage', 'mail', null, 'Sent an e-mail test message (' . ($r['ok'] ? 'sent' : 'failed') . ')');
        if ($r['ok']) {
            Flash::success(Mailer::driver() === 'file'
                ? 'Test message written to a file (e-mail is in "file" mode on this computer: nothing is really sent).'
                : 'Test message sent to ' . $to . '. Check the inbox, and the spam folder too.');
        } else {
            Flash::error('The test message could not be sent. ' . (string) ($r['error'] ?? 'The mail server did not accept it.'));
        }
        return $this->redirect('admin/connections');
    }

    /** "name@gmail.com" -> "na...@gmail.com"; a client id keeps its first characters. */
    private static function mask(string $v, int $keep = 2): string
    {
        $v = trim($v);
        if ($v === '') {
            return '';
        }
        if (str_contains($v, '@')) {
            [$l, $d] = explode('@', $v, 2);
            return substr($l, 0, min($keep, strlen($l))) . '...@' . $d;
        }
        return substr($v, 0, $keep) . '...';
    }

    /** SPF and DMARC records of the sending domain (read from DNS); DKIM depends on the provider, so only a hint is given. @return array{spf:?string,dmarc:?string} */
    private static function dns(string $domain): array
    {
        $txt = static function (string $name): array {
            try {
                $rows = @dns_get_record($name, DNS_TXT) ?: [];
            } catch (\Throwable) {
                $rows = [];
            }
            return array_values(array_filter(array_map(static fn(array $r): string => (string) ($r['txt'] ?? ''), $rows)));
        };
        $spf = null;
        foreach ($txt($domain) as $t) {
            if (stripos($t, 'v=spf1') === 0) {
                $spf = $t;
            }
        }
        $dmarc = null;
        foreach ($txt('_dmarc.' . $domain) as $t) {
            if (stripos($t, 'v=DMARC1') === 0) {
                $dmarc = $t;
            }
        }
        return ['spf' => $spf, 'dmarc' => $dmarc];
    }
}
