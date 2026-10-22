<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CivilDate;
use App\Services\Calendar\IcsBuilder;
use App\Services\Calendar\RecurrenceRules;

/**
 * PUBLIC calendar: /calendar (page), /calendar/feed (JSON), /calendar.ics and /calendar/{id}.ics (iCal), /calendar/{slug} (detail).
 * Contract: docs/CALENDAR.md. OWNER of the page behaviour: WEB agent (views/web/calendar/*, partials/calendar-*, scss/pages/_calendar.scss,
 * assets/js/calendar.js, the contact embed, the sitemap block). The FEED and ICAL methods are complete and tested: keep their shapes.
 *
 * Rules: only public, active entries of visible types are reachable (CalendarService enforces it); nothing here stores or logs anything
 * about the visitor; every parameter is validated (month, dates, type slugs); the JSON range is capped at 400 days.
 *
 * Type filter in the URL (page): no `types` = the default selection (setting calendar_default_types, else every type); `types=a,b` (or `types[]=`)
 * = exactly those; `types=all` = every type even when a default selection is set; `types=none` = nothing ticked (an empty, explained page).
 */
final class CalendarController extends Controller
{
    private const VIEWS = ['month', 'list', 'upcoming', 'podcast'];
    /** months beyond this distance from today that hold only the recurring system days are thin pages: noindex */
    private const THIN_MONTHS = 24;
    /** the jump form and ?month= accept this span (the grid of 1900 / 2200 would reach outside the supported years) */
    private const MIN_MONTH = '1901-01';
    private const MAX_MONTH = '2199-12';

    /** GET /calendar */
    public function index(Request $request): Response
    {
        if (!CalendarService::available()) {
            throw new HttpException(404, 'Not found');
        }
        $todayMonth = substr(CivilDate::today(), 0, 7);
        $month = $this->str($request, 'month');
        $y = $this->str($request, 'y');
        $m = $this->str($request, 'm');
        if ($month === '' && ctype_digit($y) && ctype_digit($m) && strlen($y) <= 4) {      // the "jump to" form works without JavaScript
            $month = sprintf('%04d-%02d', (int) $y, (int) $m);
        }
        $monthGiven = CivilDate::validMonth($month) && $month >= self::MIN_MONTH && $month <= self::MAX_MONTH;
        if (!$monthGiven) {
            $month = $todayMonth;
        }
        $view = $this->str($request, 'view');
        $view = in_array($view, self::VIEWS, true) ? $view : 'month';
        $sel = $this->selection($request);
        $filters = ['types' => $sel['filter']];

        $grid = CalendarService::monthGrid($month, $filters);
        $upcoming = [];
        $agenda = [];
        $flat = in_array($view, ['upcoming', 'podcast'], true);          // list views that do not depend on the month
        if ($view === 'podcast') {
            // the independent Podcasts and live tab: only the "Podcast / live broadcast" type, whatever the colour-key filter says
            $upcoming = CalendarService::upcoming(50, ['types' => ['podcast']]);
        } elseif ($view === 'upcoming') {
            $upcoming = CalendarService::upcoming(max(3, min(50, (int) content('calendar', 'upcoming.count'))), $filters);
        } elseif ($view === 'list') {
            $agenda = CalendarService::groupByDay($grid['items'], CivilDate::monthStart($month), CivilDate::monthEnd($month));
        }

        $isDefault = !$monthGiven || $grid['is_current'];
        $monthCanonical = $isDefault ? '/calendar' : '/calendar?month=' . $month;
        $meta = ['canonical' => $flat ? '/calendar?view=' . $view : $monthCanonical];
        if ($view === 'podcast') {
            $meta['title'] = (string) content('calendar', 'podcast.seo_title');
            $meta['description'] = (string) content('calendar', 'podcast.seo_description');
        }
        if (!$flat && !$isDefault) {
            $meta['title'] = $this->tpl((string) content('calendar', 'seo.month_title'), ['month' => $grid['label'], 'count' => $grid['total']]);
            $meta['description'] = $this->tpl((string) content('calendar', 'seo.month_description'), ['month' => $grid['label'], 'count' => $grid['total']]);
        }
        // thin or parameter-made pages stay out of search results: a month far from today holding only the recurring system days, and filtered views
        if ($sel['param'] !== null || (!$flat && $this->monthsFrom($todayMonth, $month) > self::THIN_MONTHS && $grid['custom_total'] === 0)) {
            $meta['robots'] = 'noindex,follow';
        }

        $jsonLd = $flat ? $this->eventLd($upcoming) : ($view === 'month' || $view === 'list' ? $this->eventLd($this->monthItems($grid)) : []);

        return $this->renderPage('calendar', 'web/calendar/index', [
            'grid' => $grid, 'view' => $view, 'upcoming' => $upcoming, 'agenda' => $agenda, 'jsonLd' => $jsonLd,
            'types' => CalendarService::publicTypes(), 'sel' => $sel, 'month' => $month, 'todayMonth' => $todayMonth,
        ], [
            'meta' => $meta,
            'breadcrumbs' => [[HomeSupport::navLabel('contact', 'Contact'), url('contact')], [HomeSupport::navLabel('calendar', 'Calendar'), null]],
            'bodyClass' => 'page-calendar',
        ]);
    }

    /**
     * GET /calendar/feed?start=YYYY-MM-DD&end=YYYY-MM-DD (or ?month=YYYY-MM = the whole visible grid) [&types=a,b]
     * Public entries only, no personal data. Range capped at 400 days (`capped: true` when the end was cut). 400 + {"error"} for bad input.
     */
    public function feed(Request $request): Response
    {
        if (!CalendarService::available()) {
            return $this->feedError('unavailable', 'The calendar is not available.', 404);
        }
        $start = $this->str($request, 'start');
        $end = $this->str($request, 'end');
        $month = $this->str($request, 'month');
        foreach (['start', 'end', 'month'] as $k) {         // ?start[]= and friends are a bad request, not a server error
            if (is_array($request->query($k, ''))) {
                return $this->feedError('bad_request', 'start, end and month must be single values.', 400);
            }
        }
        if ($start === '' && $end === '' && $month !== '') {
            if (!CivilDate::validMonth($month) || $month < CalendarService::MIN_MONTH || $month > CalendarService::MAX_MONTH) {
                return $this->feedError('bad_request', 'month must look like 2026-03.', 400);
            }
            $ws = CalendarService::weekStart();
            $start = CivilDate::weekStartOf(CivilDate::monthStart($month), $ws);
            $end = CivilDate::addDays(CivilDate::weekStartOf(CivilDate::monthEnd($month), $ws), 6);
        }
        if (!CivilDate::valid($start) || !CivilDate::valid($end)) {
            return $this->feedError('bad_request', 'start and end must be dates like 2026-03-01.', 400);
        }
        if ($end < $start) {
            return $this->feedError('bad_request', 'end cannot be before start.', 400);
        }
        $data = CalendarService::feed($start, $end, ['types' => $this->typeFilter($request, false)]);
        $body = (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
        $etag = '"' . md5($body) . '"';
        $headers = ['ETag' => $etag, 'Cache-Control' => 'public, max-age=300', 'Content-Type' => 'application/json; charset=utf-8', 'Vary' => 'Accept-Encoding'];
        if (trim((string) $request->header('If-None-Match', '')) === $etag) {
            return new Response('', 304, $headers);
        }
        return new Response($body, 200, $headers);
    }

    /** GET /calendar.ics[?type=slug[,slug]] - every public entry for about four years (UTF-8, CRLF, RFC 5545). */
    public function icsAll(Request $request): Response
    {
        if (!CalendarService::available()) {
            throw new HttpException(404, 'Not found');
        }
        $types = $this->typeFilter($request, false, 'type');
        $body = CalendarService::icsFeed(['types' => $types]);
        $name = $types === [] ? 'lumina-calendar' : 'lumina-calendar-' . implode('-', array_slice($types, 0, 3));
        return $this->ics($body, substr((string) preg_replace('/[^a-z0-9-]+/', '-', $name), 0, 80));
    }

    /** GET /calendar/{id}.ics[?date=YYYY-MM-DD] - one occurrence (or the next one) of a public entry. */
    public function icsEntry(Request $request, int $id): Response
    {
        if (!CalendarService::available() || !str_ends_with($request->path(), '.ics')) {
            throw new HttpException(404, 'Not found');
        }
        $entry = CalendarService::publicEntryById($id);
        if ($entry === null) {
            throw new HttpException(404, 'Not found');
        }
        $date = $this->str($request, 'date');
        $body = CalendarService::icsEntry($entry, CivilDate::valid($date) ? $date : null);
        if ($body === '') {
            throw new HttpException(404, 'Not found');
        }
        return $this->ics($body, (string) $entry['slug']);
    }

    /**
     * GET /calendar/{slug}[?date=YYYY-MM-DD] - detail page of an entry (the next occurrence unless ?date= names one).
     * An unknown, hidden or private slug answers 404 with a friendly page (registry calendar > day.not_found_*), never a hint that it exists.
     */
    public function show(Request $request, string $slug): Response
    {
        $entry = CalendarService::publicEntryBySlug($slug);
        if ($entry === null) {
            return $this->notFound();
        }
        $date = $this->str($request, 'date');
        $occ = null;
        if (CivilDate::valid($date)) {
            $occ = CalendarService::occurrenceOn(CalendarService::occurrencesOfEntry($entry, CivilDate::addDays($date, -RecurrenceRules::MAX_DURATION_DAYS), CivilDate::addDays($date, 1), true), $date);
        }
        // the coming dates in ONE range query (about five years): a yearly holiday gives five or six, a weekly session seven
        // (CalendarService::nextOccurrences would walk the same span in 400-day steps, one overrides query each)
        $today = CivilDate::today();
        $next = array_slice(CalendarService::occurrencesOfEntry($entry, $today, CivilDate::addDays($today, CalendarService::MAX_INTERNAL_DAYS)), 0, 7);
        $occ ??= $next[0] ?? (CalendarService::occurrencesOfEntry($entry, CivilDate::addDays(CivilDate::today(), -4000), CivilDate::today())[0] ?? null);
        if ($occ === null) {
            return $this->notFound();
        }
        $desc = IcsBuilder::plainText((string) $entry['description']);
        $description = $desc !== '' ? $desc : CalendarService::dateLabel($occ) . ($occ['source_label'] ? ' (' . $occ['source_label'] . ')' : '');
        $type = CalendarService::typeById((int) $entry['type_id']);
        $meta = [
            'title' => (string) $entry['title'],
            'description' => $description,
            // a recurring day has one canonical page; the occurrence is only a ?date= view of it
            'canonical' => '/calendar/' . $entry['slug'],
        ];
        // a public MEETING (docs/MEETINGS.md) is a one-off listing that may carry a join link: visitors can read it, search engines do not index it
        if (\App\Services\Calendar\CalendarWriter::ownedByMeeting((int) $entry['id'])) {
            $meta['robots'] = 'noindex,follow';
        }
        return $this->renderPage('calendar', 'web/calendar/show', [
            'entry' => $entry, 'occ' => $occ, 'next' => $next, 'type' => $type,
            'jsonLd' => $this->eventLd([$occ]),
        ], [
            'meta' => $meta,
            'breadcrumbs' => [
                [HomeSupport::navLabel('contact', 'Contact'), url('contact')],
                [HomeSupport::navLabel('calendar', 'Calendar'), url('calendar')],
                [(string) $entry['title'], null],
            ],
            'bodyClass' => 'page-calendar page-calendar-detail',
        ]);
    }

    // ------------------------------------------------------------------ helpers

    private function notFound(): Response
    {
        return $this->renderPage('calendar', 'web/calendar/not-found', [], [
            'status' => 404,
            'meta' => ['title' => (string) content('calendar', 'day.not_found_title'), 'description' => (string) content('calendar', 'day.not_found_text'), 'robots' => 'noindex,follow'],
            'breadcrumbs' => [[HomeSupport::navLabel('contact', 'Contact'), url('contact')], [HomeSupport::navLabel('calendar', 'Calendar'), url('calendar')], [(string) content('calendar', 'day.not_found_title'), null]],
            'bodyClass' => 'page-calendar page-calendar-detail',
        ]);
    }

    /** A query value as a trimmed string ('' when missing or an array). */
    private function str(Request $request, string $key): string
    {
        $v = $request->query($key, '');
        return is_string($v) ? trim($v) : '';
    }

    /** Number of calendar months between two 'YYYY-MM' values (absolute). */
    private function monthsFrom(string $a, string $b): int
    {
        return abs(((int) substr($a, 0, 4) * 12 + (int) substr($a, 5, 2)) - ((int) substr($b, 0, 4) * 12 + (int) substr($b, 5, 2)));
    }

    /**
     * The visitor's type selection (see the class comment for the URL forms).
     * @return array{checked:list<string>,filter:list<string>,param:?string,all:list<string>,defaults:list<string>}
     *   checked = slugs ticked in the chips; filter = argument for CalendarService ([] = every type); param = the `types` value that reproduces
     *   this selection in a URL (null = the default selection, no parameter needed); all = every public type; defaults = the default selection
     */
    private function selection(Request $request): array
    {
        $public = array_values(array_map(static fn(array $t): string => (string) $t['slug'], CalendarService::publicTypes()));
        $raw = $request->query('types', null);
        $list = is_array($raw) ? $raw : (is_string($raw) ? explode(',', $raw) : []);
        $asked = [];
        foreach ($list as $s) {
            $s = is_string($s) ? strtolower(trim($s)) : '';
            if ($s !== '' && preg_match('/^[a-z0-9-]{1,60}$/', $s)) {
                $asked[$s] = $s;
            }
        }
        $asked = array_slice(array_values($asked), 0, 40);
        $defaults = array_values(array_intersect($public, CalendarService::defaultTypeSlugs()));
        if ($defaults === []) {
            $defaults = $public;
        }
        if (in_array('all', $asked, true)) {
            $checked = $public;
        } elseif ($asked === ['none']) {
            $checked = [];
        } else {
            $valid = array_values(array_intersect($public, $asked));
            $checked = $valid !== [] ? $valid : $defaults;
        }
        $isAll = count($checked) === count($public);
        $same = count($checked) === count($defaults) && array_diff($checked, $defaults) === [];
        $param = $same ? null : ($checked === [] ? 'none' : ($isAll ? 'all' : implode(',', $checked)));
        return [
            'checked' => $checked,
            'filter' => $isAll ? [] : ($checked === [] ? ['-none-'] : $checked),
            'param' => $param, 'all' => $public, 'defaults' => $defaults,
        ];
    }

    /** Occurrences of the month grid that really fall inside the month (the grid also shows days of the neighbours). */
    private function monthItems(array $grid): array
    {
        $first = $grid['month'] . '-01';
        $last = CivilDate::monthEnd($grid['month']);
        return array_values(array_filter($grid['items'], static fn(array $o): bool => $o['start_date'] <= $last && $o['end_date'] >= $first));
    }

    /**
     * schema.org Event data for programme sessions and Lumina events ONLY (never holidays or international days). At most 20.
     * @param list<array<string,mixed>> $items
     * @return list<array<string,mixed>>
     */
    private function eventLd(array $items): array
    {
        $out = [];
        foreach ($items as $o) {
            if (!empty($o['cancelled']) || ($o['type_slug'] !== 'programme' && empty($o['is_event']))) {
                continue;
            }
            $start = $o['start_date'] . (!empty($o['start_time']) && empty($o['all_day']) ? 'T' . $o['start_time'] . ':00+03:00' : '');
            $timed = !empty($o['start_time']) && empty($o['all_day']);
            $ld = [
                '@context' => 'https://schema.org', '@type' => 'Event', 'name' => (string) $o['title'],
                'startDate' => $start,
                'eventStatus' => 'https://schema.org/EventScheduled',
                'url' => origin() . (string) $o['url'],
                'organizer' => array_filter(['@type' => 'Organization', 'name' => (string) setting('site_name', ''), 'url' => abs_url('')]),
            ];
            // endDate in the same format as startDate; a timed session without an end time has none (a bare date would read as midnight, before the start)
            if (!$timed) {
                $ld['endDate'] = $o['end_date'];
            } elseif (!empty($o['end_time'])) {
                $ld['endDate'] = $o['end_date'] . 'T' . $o['end_time'] . ':00+03:00';
            }
            $summary = IcsBuilder::plainText((string) ($o['description'] ?? ''));
            if ($summary !== '') {
                $ld['description'] = mb_strlen($summary) > 300 ? rtrim(mb_substr($summary, 0, 299)) . "\u{2026}" : $summary;
            }
            if ((string) $o['location'] !== '') {
                $ld['location'] = ['@type' => 'Place', 'name' => (string) $o['location'], 'address' => (string) $o['location']];
                $ld['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
            }
            $out[] = $ld;
            if (count($out) >= 20) {
                break;
            }
        }
        return $out;
    }

    /**
     * Type slugs from ?types=a,b / ?types[]=a (or the given parameter). Unknown slugs are dropped by the service.
     * With $useDefaults the setting calendar_default_types applies when the visitor chose nothing.
     * @return list<string>
     */
    private function typeFilter(Request $request, bool $useDefaults = true, string $param = 'types'): array
    {
        $raw = $request->query($param, null);
        $list = is_array($raw) ? $raw : (is_string($raw) ? explode(',', $raw) : []);
        $out = [];
        foreach ($list as $s) {
            $s = is_string($s) ? strtolower(trim($s)) : '';
            if ($s !== '' && preg_match('/^[a-z0-9-]{1,60}$/', $s)) {
                $out[$s] = $s;
            }
        }
        $out = array_slice(array_values($out), 0, 20);
        return $out === [] && $useDefaults ? CalendarService::defaultTypeSlugs() : $out;
    }

    private function tpl(string $text, array $vars): string
    {
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{' . $k . '}'] = (string) $v;
        }
        return strtr($text, $map);
    }

    private function feedError(string $code, string $message, int $status): Response
    {
        return new Response((string) json_encode(['error' => $code, 'message' => $message]), $status, ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'no-store']);
    }

    private function ics(string $body, string $file): Response
    {
        return new Response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $file . '.ics"',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }
}
