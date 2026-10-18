<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Models\ContactMessage;
use App\Services\Calendar\CalendarService;
use App\Services\Web\FormMessages;

/**
 * GET /contact   office details (from Settings), map (Settings map_embed_url, graceful fallback), contact form.
 * POST /contact  validated server-side -> contact_messages (admin: Inbox > Messages) + email to Settings notify_email.
 * Route middleware: page.published:contact, honeypot, throttle:5,600 (+ automatic CSRF).
 */
final class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $cal = $request->query('cal', '');                  // an array (?cal[]=) is ignored, never cast
        return $this->renderPage('contact', 'web/contact/index', [
            'mapUrl' => $this->embeddableMapUrl((string) setting('map_embed_url', '')),
            // compact calendar under the form (null when switched off or while the calendar tables are missing); ?cal=YYYY-MM changes its month without JavaScript
            'calendar' => CalendarService::embedData(is_string($cal) ? trim($cal) : ''),
        ], [
            'bodyClass'   => 'is-contact',
            'breadcrumbs' => [[(string) (SitePages::get('contact')['nav_label'] ?? 'Contact'), null]],
        ]);
    }

    public function send(Request $request): Response
    {
        $rules = [
            'name'    => 'required|max:150',
            'email'   => 'required|email|max:190',
            'phone'   => 'nullable|phone|max:40',
            'subject' => 'nullable|max:220',
            'message' => 'required|max:5000',
        ];
        $data = $this->validate($rules, FormMessages::for('contact', array_keys($rules), $rules), [], 'contact#contact-form');
        foreach (array_keys($rules) as $k) {
            $data[$k] = trim((string) ($data[$k] ?? ''));
        }

        $id = ContactMessage::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] !== '' ? $data['phone'] : null,
            'subject'    => $data['subject'] !== '' ? $data['subject'] : null,
            'message'    => $data['message'],
            'status'     => 'new',
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $subject = trim((string) content('contact', 'form.notify_subject'));
        $subject = ($subject !== '' ? $subject : 'Contact message') . ($data['subject'] !== '' ? ': ' . $data['subject'] : '');
        Mailer::notify($subject, implode("\n", [
            'Name: ' . $data['name'],
            'Email: ' . $data['email'],
            'Phone: ' . ($data['phone'] !== '' ? $data['phone'] : '-'),
            'Subject: ' . ($data['subject'] !== '' ? $data['subject'] : '-'),
            '',
            $data['message'],
            '',
            'Open in the admin: ' . abs_url('admin/messages/' . $id),
        ]), $data['email']);

        Flash::success((string) content('contact', 'form.success'));
        return $this->redirect('contact');
    }

    /** Only Google Maps embed URLs may be framed (the CSP also allows nothing else). Returns '' to trigger the fallback card. */
    private function embeddableMapUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || !preg_match('~^https://((www\.)?google\.[a-z.]+/maps/embed|maps\.google\.[a-z.]+/maps)~i', $url)) {
            return '';
        }
        return $url;
    }
}
