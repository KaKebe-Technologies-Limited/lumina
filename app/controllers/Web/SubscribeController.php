<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Honeypot;
use App\Core\HttpException;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Models\Subscriber;

/**
 * Newsletter.
 *   POST /subscribe                 (footer box, Blog, Activities, Careers...) -> subscribers; same friendly message whether the address was
 *                                   new, already subscribed or had opted out (so the form cannot be used to discover who is on the list).
 *                                   An opt-out is never reversed by this form; resubscribing needs the token link or staff.
 *   GET  /subscribe                 sends visitors to the home page (the sign-up boxes post here).
 *   GET  /unsubscribe/{token}       asks for confirmation (so mail scanners that open links cannot unsubscribe people).
 *   POST /unsubscribe/{token}       action=unsubscribe | resubscribe (page form, CSRF checked here) OR the RFC 8058 one-click POST "List-Unsubscribe=One-Click"
 *                                   sent by mail clients for the List-Unsubscribe-Post header of newsletter e-mails (no CSRF token possible; the token in the URL is the credential)
 * Messages come from the "site_newsletter" registry page. Tokens are 40 hex characters (subscribers.token).
 */
final class SubscribeController extends Controller
{
    public function landing(Request $request): Response
    {
        return $this->redirect('');
    }

    public function store(Request $request): Response
    {
        $back = Honeypot::safeBackUrl($request);
        $email = strtolower(trim($request->string('email')));
        if ($email === '' || mb_strlen($email) > 190 || !Mailer::validEmail($email)) {
            Flash::error((string) content('site_newsletter', 'messages.invalid'));
            return Response::redirect($back);
        }
        $source = substr((string) preg_replace('/[^a-z0-9_-]/', '', strtolower($request->string('source'))), 0, 60);

        // An address that opted out is NEVER switched back on by an anonymous form post (anyone could re-enrol a stranger that way);
        // the owner of the mailbox can resubscribe from the button on the unsubscribe page, and staff from Admin > Subscribers.
        // Everybody sees the same message, so the form cannot be used to find out who is on the list.
        $existing = Subscriber::findBy('email', $email);
        if ($existing === null) {
            try {
                Subscriber::create([
                    'email'         => $email,
                    'status'        => 'subscribed',
                    'token'         => bin2hex(random_bytes(20)),
                    'source'        => $source !== '' ? $source : 'website',
                    'ip'            => $request->ip(),
                    'subscribed_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\PDOException $e) {
                // two sign-ups for the same new address at the same moment: the other request won, which is just as good
                if ((string) $e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }

        Flash::success((string) content('site_newsletter', 'messages.success'));
        return Response::redirect($back);
    }

    public function showUnsubscribe(Request $request, string $token): Response
    {
        $sub = $this->subscriberByToken($token);
        return $this->screen($sub['status'] === 'unsubscribed' ? 'done' : 'ask', $token);
    }

    public function confirmUnsubscribe(Request $request, string $token): Response
    {
        // RFC 8058 one-click unsubscribe: a mail client POSTs "List-Unsubscribe=One-Click" to the URL of the List-Unsubscribe header, without a page
        // and therefore without a CSRF token (the route is csrf.exempt). The unguessable 40-character token is the credential; the effect is
        // immediate and idempotent, and the reply is a plain 200 with no redirect and no cookie.
        if ($request->post('List-Unsubscribe') === 'One-Click') {
            $sub = $this->subscriberByToken($token);
            if ($sub['status'] !== 'unsubscribed') {
                Database::update('subscribers', ['status' => 'unsubscribed', 'unsubscribed_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $sub['id']]);
            }
            return Response::text("OK
", 200)->header('Cache-Control', 'no-store');
        }
        // the page form: the route does not verify CSRF for us any more, so do it here
        if (!Csrf::verify($request)) {
            throw new HttpException(419, 'Page expired');
        }
        $sub = $this->subscriberByToken($token);
        $action = $request->string('action', 'unsubscribe');

        if ($action === 'resubscribe') {
            Database::update('subscribers', ['status' => 'subscribed', 'subscribed_at' => date('Y-m-d H:i:s'), 'unsubscribed_at' => null], 'id = ?', [(int) $sub['id']]);
            Flash::success(trim((string) content('site_newsletter', 'unsubscribe.back_title') . '. ' . (string) content('site_newsletter', 'unsubscribe.back_text')));
            return $this->redirect('');
        }
        if ($sub['status'] !== 'unsubscribed') {
            Database::update('subscribers', ['status' => 'unsubscribed', 'unsubscribed_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $sub['id']]);
        }
        return $this->redirect('unsubscribe/' . $token);
    }

    // -------------------------------------------------------------------------------------------------------------

    private function subscriberByToken(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $token)) {
            throw new HttpException(404, 'Not found');
        }
        return Subscriber::findByToken($token) ?? throw new HttpException(404, 'Not found');
    }

    private function screen(string $state, string $token): Response
    {
        $title = (string) content('site_newsletter', $state === 'ask' ? 'unsubscribe.title' : 'unsubscribe.done_title');
        return $this->render('web/legal/unsubscribe', ['state' => $state, 'token' => $token], [
            'bodyClass' => 'is-unsubscribe',
            'meta'      => ['title' => $title, 'robots' => 'noindex,nofollow', 'canonical' => '/unsubscribe'],
        ]);
    }
}
