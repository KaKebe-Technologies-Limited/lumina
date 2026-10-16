<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Event;

/**
 * GET /events (upcoming + past), GET /events/{slug}, POST /events/{slug}/register, GET /events/{slug}/calendar.ics.
 * OWNED BY B7 "org-public". Copy comes from the registry page "events" (app/config/pages/events.php).
 *
 * Registration writes to `event_registrations` (UNIQUE event_id+email) inside a transaction that locks the event row, so the capacity
 * check cannot be raced. The POST route carries the honeypot + throttle middleware (see routes/web_org.php).
 */
final class EventsController extends OrgPublicController
{
    private const PAST_PER_PAGE = 9;

    public function index(Request $request): Response
    {
        $upcoming = Event::upcoming(60);
        $past = Event::paginate(
            $request->page(),
            self::PAST_PER_PAGE,
            ['is_active' => 1, '@raw' => ['COALESCE(end_at, start_at) < NOW()', []]],
            'start_at DESC'
        );

        return $this->renderPage('events', 'web/events/index', [
            'upcoming' => $upcoming,
            'past'     => $past->items,
            'pagination' => $past,
            'any'      => $upcoming !== [] || $past->total > 0,
        ], [
            'breadcrumbs' => $this->crumbs('events'),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $event = $this->findActive($slug);
        $state = $this->state($event);

        $registered = Session::getFlash('event_registered');
        if (!is_array($registered) || (int) ($registered['event'] ?? 0) !== (int) $event['id']) {
            $registered = null;
        }

        $others = [];
        foreach (Event::upcoming(5) as $other) {
            if ((int) $other['id'] !== (int) $event['id'] && count($others) < 3) {
                $others[] = $other;
            }
        }

        $summary = trim((string) ($event['summary'] ?? ''));
        return $this->renderPage('events', 'web/events/show', $state + [
            'event'      => $event,
            'externalUrl' => $this->safeUrl($event['registration_url'] ?? ''),
            'registered' => $registered,
            'others'     => $others,
        ], [
            'meta' => [
                'title'       => trim((string) ($event['seo_title'] ?? '')) !== '' ? (string) $event['seo_title'] : (string) $event['title'],
                'description' => trim((string) ($event['seo_description'] ?? '')) !== '' ? (string) $event['seo_description'] : ($summary !== '' ? $summary : excerpt((string) ($event['body'] ?? ''), 175)),
                'og_image'    => (string) ($event['image'] ?? ''),
            ],
            'breadcrumbs' => $this->crumbs('events', [[(string) $event['title'], null]]),
            'bodyClass'   => 'is-detail',
        ]);
    }

    /** A visitor who opens the form action by hand (GET) is sent to the form on the event page. */
    public function registerForm(Request $request, string $slug): Response
    {
        $event = $this->findActive($slug);
        return $this->redirect('events/' . $event['slug'] . '#register');
    }

    public function register(Request $request, string $slug): Response
    {
        $event = $this->findActive($slug);
        $state = $this->state($event);
        if (empty($event['registration_enabled'])) {
            throw new HttpException(404);
        }
        $back = 'events/' . $event['slug'] . '#register';

        if ($state['over']) {
            // shown as a normal notice: the sign-up form (and its inline "form" alert) is not on the page for a finished event
            Flash::error((string) content('events', 'messages.closed'));
            return $this->redirect($back);
        }

        $data = $this->validate([
            'name'         => 'required|max:150',
            'email'        => 'required|email|max:190',
            'phone'        => 'nullable|phone|max:40',
            'organisation' => 'nullable|max:190',
            'notes'        => 'nullable|max:1000',
        ], [
            'name.required'  => (string) content('events', 'messages.name_required'),
            'name.max'       => (string) content('events', 'messages.too_long'),
            'email.required' => (string) content('events', 'messages.email_required'),
            'email.email'    => (string) content('events', 'messages.email_invalid'),
            'email.max'      => (string) content('events', 'messages.too_long'),
            'phone.phone'    => (string) content('events', 'messages.phone_invalid'),
            'phone.max'      => (string) content('events', 'messages.too_long'),
            'organisation.max' => (string) content('events', 'messages.too_long'),
            'notes.max'      => (string) content('events', 'messages.too_long'),
        ], [], $back);

        $email = mb_strtolower((string) $data['email']);
        $eventId = (int) $event['id'];
        $capacity = (int) ($event['capacity'] ?? 0);

        $outcome = Database::transaction(function () use ($eventId, $email, $capacity, $data, $request): string {
            Database::one('SELECT id FROM events WHERE id = ? FOR UPDATE', [$eventId]);       // serialises concurrent sign-ups per event
            $existing = Database::one('SELECT id, status FROM event_registrations WHERE event_id = ? AND email = ?', [$eventId, $email]);
            if ($existing !== null && (string) $existing['status'] !== 'cancelled') {
                return 'duplicate';
            }
            $taken = (int) Database::value("SELECT COUNT(*) FROM event_registrations WHERE event_id = ? AND status <> 'cancelled'", [$eventId], 0);
            if ($capacity > 0 && $taken >= $capacity) {
                return 'full';
            }
            $row = [
                'name'         => (string) $data['name'],
                'phone'        => (string) ($data['phone'] ?? '') !== '' ? (string) $data['phone'] : null,
                'organisation' => (string) ($data['organisation'] ?? '') !== '' ? (string) $data['organisation'] : null,
                'notes'        => (string) ($data['notes'] ?? '') !== '' ? (string) $data['notes'] : null,
                'status'       => 'registered',
                'ip'           => mb_substr($request->ip(), 0, 45),
            ];
            if ($existing !== null) {
                Database::update('event_registrations', $row, 'id = ?', [(int) $existing['id']]);   // re-registering after a cancellation
            } else {
                Database::insert('event_registrations', $row + ['event_id' => $eventId, 'email' => $email]);
            }
            return 'ok';
        });

        if ($outcome === 'full') {
            Flash::error((string) content('events', 'messages.full'));
            return $this->redirect($back);
        }

        // An address that is already registered gets the SAME success screen (and the confirmation e-mail again, within the
        // per-address limit) as a new one, so this form cannot be used to find out who is registered. Staff are only notified once.
        $mailed = $this->sendMails($event, $state, (string) $data['name'], $email, $data, $outcome === 'ok');
        Session::flash('event_registered', ['event' => $eventId, 'name' => (string) $data['name'], 'email' => $email, 'mailed' => $mailed]);
        return $this->redirect($back);
    }

    /** Add-to-calendar download (RFC 5545). Times are written in UTC so every calendar app converts them to the viewer's zone. */
    public function calendar(Request $request, string $slug): Response
    {
        $event = $this->findActive($slug);
        $start = to_datetime($event['start_at']);
        if ($start === null) {
            throw new HttpException(404);
        }
        $end = to_datetime($event['end_at']) ?? $start->modify('+1 hour');   // calendars need an end: default length 1 hour when none is set
        if ($end <= $start) {
            $end = $start->modify('+1 hour');
        }
        $utc = new \DateTimeZone('UTC');
        $fmt = static fn(\DateTimeImmutable $d): string => $d->setTimezone($utc)->format('Ymd\THis\Z');
        $eventUrl = abs_url('events/' . $event['slug']);
        $summary = trim((string) ($event['summary'] ?? ''));
        if ($summary === '') {
            $summary = excerpt((string) ($event['body'] ?? ''), 300);
        }
        $site = (string) preg_replace('/[^A-Za-z0-9 ._-]+/', '', (string) setting('site_name', 'Events'));
        $host = (string) preg_replace('/[^A-Za-z0-9.-]+/', '', explode(':', $request->host())[0]) ?: 'localhost';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//' . ($site !== '' ? $site : 'Events') . '//Events//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:event-' . (int) $event['id'] . '@' . $host,
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART:' . $fmt($start),
            'DTEND:' . $fmt($end),
            'SUMMARY:' . $this->icsText((string) $event['title']),
        ];
        $desc = trim($summary . "\n" . $eventUrl);
        $lines[] = 'DESCRIPTION:' . $this->icsText($desc);
        if (trim((string) ($event['location'] ?? '')) !== '') {
            $lines[] = 'LOCATION:' . $this->icsText((string) $event['location']);
        }
        $lines[] = 'URL:' . $eventUrl;
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        $body = implode("\r\n", array_map([$this, 'icsFold'], $lines)) . "\r\n";
        $file = (string) preg_replace('/[^a-z0-9-]+/', '', (string) $event['slug']) ?: 'event';
        return Response::text($body, 200, 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $file . '.ics"')
            ->header('Cache-Control', 'no-cache');
    }

    // ------------------------------------------------------------------ helpers

    /** @return array<string,mixed> */
    private function findActive(string $slug): array
    {
        $event = Event::first(['slug' => $slug, 'is_active' => 1]);
        if ($event === null) {
            throw new HttpException(404);
        }
        return $event;
    }

    /**
     * Time and capacity state of an event.
     * @return array{start:?\DateTimeImmutable,end:?\DateTimeImmutable,over:bool,capacity:int,taken:int,left:?int,full:bool,canRegister:bool}
     */
    private function state(array $event): array
    {
        $start = to_datetime($event['start_at']);
        $end = to_datetime($event['end_at']);
        $last = $end ?? $start;
        $over = $last !== null && $last < new \DateTimeImmutable('now');
        $capacity = (int) ($event['capacity'] ?? 0);
        $taken = (int) Database::value("SELECT COUNT(*) FROM event_registrations WHERE event_id = ? AND status <> 'cancelled'", [(int) $event['id']], 0);
        $left = $capacity > 0 ? max(0, $capacity - $taken) : null;
        $full = $capacity > 0 && $taken >= $capacity;
        return [
            'start'       => $start,
            'end'         => $end,
            'over'        => $over,
            'capacity'    => $capacity,
            'taken'       => $taken,
            'left'        => $left,
            'full'        => $full,
            'canRegister' => !empty($event['registration_enabled']) && !$over && !$full,
        ];
    }

    /** Notify the organisation and confirm to the visitor. Returns true when the visitor's confirmation was accepted for delivery. */
    private function sendMails(array $event, array $state, string $name, string $email, array $data, bool $notifyStaff = true): bool
    {
        $when = $this->whenText($state['start'], $state['end']);
        $where = trim((string) ($event['location'] ?? ''));
        $url = abs_url('events/' . $event['slug']);
        $site = (string) setting('site_name', '');

        try {
            if (!$notifyStaff) {
                throw new \RuntimeException('skip');
            }
            $lines = [
                'Event: ' . $event['title'],
                'When: ' . $when,
                'Name: ' . $name,
                'Email: ' . $email,
            ];
            foreach (['phone' => 'Phone', 'organisation' => 'Organisation', 'notes' => 'Notes'] as $key => $label) {
                if (trim((string) ($data[$key] ?? '')) !== '') {
                    $lines[] = $label . ': ' . $data[$key];
                }
            }
            if ($state['capacity'] > 0) {
                $lines[] = 'Places taken: ' . ($state['taken'] + 1) . ' of ' . $state['capacity'];
            }
            Mailer::notify('New event registration: ' . $event['title'], implode("\n", $lines), $email);
        } catch (\Throwable $e) {
            if ($e->getMessage() !== 'skip') {
                Log::error('Event registration notification failed: ' . $e->getMessage());
            }
        }

        // The confirmation goes to an address nobody has proven to own: cap what can be sent to one address per day and
        // what the whole site sends per hour (the visitor still sees the normal success screen when a cap is hit).
        if (!RateLimit::attempt('evreg-mail:' . sha1($email), 2, 86400) || !RateLimit::attempt('evreg-mail-all', 120, 3600)) {
            Log::warning('Event confirmation e-mail skipped (send cap reached)', ['event' => $event['id']]);
            return true;
        }

        try {
            $details = [];
            if ($when !== '') {
                $details[] = content('events', 'detail.when') . ': ' . $when;
            }
            if ($where !== '') {
                $details[] = content('events', 'detail.where') . ': ' . $where;
            }
            $vars = [
                '{name}' => $this->mailName($name), '{event}' => (string) $event['title'], '{email}' => $email, '{url}' => $url,
                '{site}' => $site, '{details}' => implode("\n", $details), '{date}' => $when, '{location}' => $where,
            ];
            $subject = strtr((string) content('events', 'email.subject'), $vars);
            $body = strtr((string) content('events', 'email.body'), $vars);
            return Mailer::send($email, $subject, $body);
        } catch (\Throwable $e) {
            Log::error('Event registration confirmation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * The visitor's name as it may appear in a message sent to a third party's inbox: letters, digits and a few punctuation marks
     * only (no links, no line breaks, no instructions), shortened. Empty names fall back to the editable "email.name_fallback" word.
     */
    private function mailName(string $name): string
    {
        $n = (string) preg_replace('~(?:https?://|www\.)\S*~iu', ' ', $name);
        $n = (string) preg_replace('/[^\p{L}\p{M}\p{N} \'.-]+/u', ' ', $n);
        $n = trim((string) preg_replace('/\s+/u', ' ', $n));
        $n = mb_substr($n, 0, 40);
        if (preg_match('/[\p{L}]/u', $n) !== 1) {
            $fallback = trim((string) content('events', 'email.name_fallback'));
            return $fallback !== '' ? $fallback : 'there';
        }
        return $n;
    }

    private function whenText(?\DateTimeImmutable $start, ?\DateTimeImmutable $end): string
    {
        if ($start === null) {
            return '';
        }
        $text = $start->format('l, j F Y, H:i');
        if ($end !== null) {
            $text .= $end->format('Y-m-d') === $start->format('Y-m-d') ? ' - ' . $end->format('H:i') : ' - ' . $end->format('l, j F Y, H:i');
        }
        return $text;
    }

    private function icsText(string $s): string
    {
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\;', '\,', '\n'], $s);
    }

    /** Fold a content line at 75 octets without splitting a multi-byte character (continuation lines start with one space). */
    private function icsFold(string $line): string
    {
        $out = '';
        $limit = 75;
        while (strlen($line) > $limit) {
            $cut = $limit;
            while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) {
                $cut--;
            }
            $out .= substr($line, 0, $cut) . "\r\n ";
            $line = substr($line, $cut);
            $limit = 74;
        }
        return $out . $line;
    }
}
