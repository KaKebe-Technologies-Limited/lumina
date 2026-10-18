<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Services\Meetings\MeetingIcs;
use App\Services\Meetings\MeetingRenderer;
use App\Services\Meetings\MeetingRequestService;
use App\Services\Meetings\MeetingService;

/**
 * The invitee page /meeting/{token}: details, join button, add to calendar (.ics) and the answer (Accept / Maybe / Decline).
 * OWNER: WEB agent. Contract: docs/MEETINGS.md sections 6, 7 and 10.
 *
 * The token (192 bits) is the only credential: it is shape-checked by the route, looked up with MeetingService::attendeeByToken() (null for an unknown or
 * REMOVED attendee) and every failure is the same plain 404 page, so nothing tells a guesser what exists. The page never lists other people, never goes into
 * a cache, a search index, a referrer or the analytics script (analytics_id() switches it off by PATH for this page and for every error page of this address). An answer is a POST (CSRF is automatic); the GET only PRESELECTS what the e-mail button said.
 * All copy comes from the registry page "site_meetings" through MeetingRenderer (the same words the e-mails use).
 */
final class MeetingController extends Controller
{
    /** ?r= of the e-mail buttons => the stored answer */
    private const SUGGEST = ['accept' => 'accepted', 'maybe' => 'tentative', 'decline' => 'declined'];
    private const ANSWERS = ['accepted', 'tentative', 'declined'];
    /** failed token lookups one visitor address may make in MISS_WINDOW seconds before further failed lookups answer 429 (a valid link always works) */
    private const MISS_MAX = 30;
    private const MISS_WINDOW = 600;

    /** GET /meeting/{token} */
    public function show(Request $request, string $token): Response
    {
        $found = $this->find($token);
        if ($found === null) {
            return $this->notFound();
        }
        ['attendee' => $attendee, 'meeting' => $meeting] = $found;
        $data = MeetingRenderer::pageData($meeting, $attendee);

        $r = $request->query('r', '');
        $suggest = is_string($r) ? (self::SUGGEST[$r] ?? '') : '';
        // the confirmation is only shown for the answer that is REALLY stored (a made-up ?saved= can never show a false "thank you")
        $saved = $request->query('saved', '');
        $saved = (is_string($saved) && in_array($saved, self::ANSWERS, true) && $saved === (string) $attendee['rsvp']) ? $saved : '';
        $err = $request->query('error', '');
        $error = is_string($err) && $err === 'invalid';

        $site = trim((string) setting('site_name', 'Lumina Africa Foundation'));
        $res = $this->render('web/meetings/show', [
            'data' => $data, 'token' => $token, 'suggest' => $suggest, 'saved' => $saved, 'error' => $error,
        ], [
            'page' => 'meeting',
            'bodyClass' => 'is-meeting',
            // a generic title: the meeting's own title stays out of the tab title, link previews and the page's meta tags
            'meta' => ['title' => MeetingRenderer::tf('page.eyebrow') !== '' ? MeetingRenderer::tf('page.eyebrow') : $site, 'description' => '', 'robots' => 'noindex,nofollow', 'canonical' => abs_url('')],
        ]);
        return $this->personal($res);
    }

    /** POST /meeting/{token}: field `rsvp` = accepted | tentative | declined. Redirects back to the page (never a GET side effect). */
    public function rsvp(Request $request, string $token): Response
    {
        $found = $this->find($token);
        if ($found === null) {
            return $this->notFound();
        }
        $answer = $request->post('rsvp', '');
        $answer = is_string($answer) ? $answer : '';
        $res = MeetingService::setRsvp($token, $answer);
        $base = url('meeting/' . $token);
        if ($res['ok']) {
            return $this->personal(Response::redirect($base . '?saved=' . rawurlencode($answer) . '#answer'));
        }
        // cancelled / ended / answers off: the page itself explains it; only a wrong value gets the "could not be saved" line
        return $this->personal(Response::redirect($base . ($res['code'] === 'invalid' ? '?error=invalid' : '') . '#answer'));
    }

    /**
     * GET /meeting/{token}.ics: the "Add to calendar" file (METHOD:PUBLISH). A CANCELLED meeting answers with the CANCEL file (calendars remove the event),
     * never a 404. The invitee's own answer is the PARTSTAT.
     */
    public function ics(Request $request, string $token): Response
    {
        $found = $this->find($token);
        if ($found === null) {
            return $this->notFound();
        }
        ['attendee' => $attendee, 'meeting' => $meeting] = $found;
        $cancelled = $meeting['status'] === 'cancelled';
        $method = $cancelled ? 'CANCEL' : 'PUBLISH';
        $body = MeetingIcs::build($meeting, $attendee, $method, array_filter([
            'page_url' => MeetingService::inviteeUrl($attendee),
            'cancel_reason' => $cancelled ? trim((string) ($meeting['cancelled_reason'] ?? '')) : '',
        ], static fn(string $v): bool => $v !== ''));
        $res = new Response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8; method=' . $method,
            'Content-Disposition' => 'attachment; filename="meeting.ics"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        return $this->personal($res);
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /**
     * The invitee and their meeting, or null. A draft is not visible to anybody yet (nobody was invited), so it counts as "not found" as well.
     * @return array{attendee:array<string,mixed>,meeting:array<string,mixed>}|null
     */
    private function find(string $token): ?array
    {
        if (!MeetingService::available()) {
            return null;
        }
        // The route throttle counts per ADDRESS AND PATH, and the token is part of the path, so it cannot stop somebody trying many different tokens.
        // This second counter is per visitor address only (an IPv6 address counts as its /64) and counts the lookups that FAILED (a well-formed token nobody
        // holds). 192-bit tokens cannot be guessed anyway; the limit just makes any try-and-see attempt (or a crawler working through leaked old links) stop early.
        // The lookup comes FIRST and a valid token always works: many people share one address (an office, a campus, a mobile carrier), and a few bad links from
        // one of them must never lock the others out of their join link. Fails open without a database.
        $miss = 'meeting-miss:' . MeetingRequestService::ipKey($this->request->ip());
        MeetingService::completeDue();                         // lazy scheduled -> completed, so "ended" is true even without the cron job
        $found = MeetingService::attendeeByToken($token);
        if ($found !== null && $found['meeting']['status'] !== 'draft') {
            return $found;
        }
        if (RateLimit::tooMany($miss, self::MISS_MAX)) {
            throw new HttpException(429, 'Too many requests', ['Retry-After' => (string) RateLimit::retryAfter($miss)]);
        }
        RateLimit::attempt($miss, self::MISS_MAX, self::MISS_WINDOW);
        return null;
    }

    /** The same plain 404 for a wrong, removed or unknown link, with the editable wording of the registry. */
    private function notFound(): Response
    {
        MeetingRenderer::useVars(MeetingRenderer::vars([], null));      // {site} works in the wording of this page
        $res = $this->render('web/meetings/not-found', [], [
            'page' => 'meeting', 'status' => 404, 'bodyClass' => 'is-meeting',
            'meta' => ['title' => MeetingRenderer::tf('page.not_found_title'), 'description' => '', 'robots' => 'noindex,nofollow', 'canonical' => abs_url('')],
        ]);
        return $this->personal($res);
    }

    /** Headers of every response of this controller: personal link = no caches, no indexing, no referrer. */
    private function personal(Response $res): Response
    {
        return $res->withHeaders([
            'Cache-Control' => 'private, no-store',
            'Pragma' => 'no-cache',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
