<?php
declare(strict_types=1);

namespace App\Services\Meetings;

use App\Core\PageRegistry;
use App\Core\View;
use App\Models\Meeting;
use App\Services\Calendar\IcsBuilder;
use App\Services\Newsletter\NewsletterMailer;
use App\Services\Newsletter\NewsletterRenderer;

/**
 * The ONE place that turns a meeting into words: the invitation, update, cancellation, removal, reminder and organiser-message e-mails, the
 * meeting-request e-mails, and the data of the public invitee page. The wording comes from the registry page `site_meetings`
 * (Admin > Pages > Meetings), so e-mail and page cannot drift. Docs: docs/MEETINGS.md sections 7 and 8.
 *
 *   $msg = MeetingRenderer::message($meeting, $attendee, 'invite');                  // one message for ONE recipient
 *   Mailer::attempt($msg['to'], $msg['subject'], $msg['text'], $msg['opts']);        // html, reply_to, calendar (inline text/calendar), invite.ics attachment ...
 */
final class MeetingRenderer
{
    /** Message kinds of message(). */
    public const KINDS = ['invite', 'update', 'cancel', 'removed', 'reminder_24h', 'reminder_1h', 'message'];
    /** Placeholders understood in the registry texts. */
    public const PLACEHOLDERS = ['title', 'when', 'date', 'time', 'organiser', 'organiser_name', 'organiser_email', 'platform', 'site', 'name', 'in', 'topic', 'message', 'note'];

    /** Placeholder values of the message / page being built (set by message(), pageData(), requestMessage()); null = none. @var array<string,string>|null */
    private static ?array $ctx = null;

    // ------------------------------------------------------------------------------------------------ wording

    /** Make tf() fill placeholders from these values (null = stop). */
    public static function useVars(?array $vars): void
    {
        self::$ctx = $vars;
    }

    /**
     * t() with the {placeholders} of the current message / page filled in. EVERY registry text that reaches a reader goes through this, so the placeholders
     * promised in the admin help work in every field (subjects use t() + subject(), which also cuts the title).
     */
    public static function tf(string $key): string
    {
        $v = self::t($key);
        return self::$ctx === null ? $v : self::fill($v, self::$ctx);
    }

    /** Registry text 'email.invite_subject' / 'page.intro' ... (a blank override falls back to the default). */
    public static function t(string $key): string
    {
        $v = trim((string) content('site_meetings', $key));
        if ($v === '') {
            $v = trim((string) (PageRegistry::field('site_meetings', $key)['default'] ?? ''));
        }
        return $v;
    }

    /** Replace {placeholders}; unknown ones stay as typed. @param array<string,string> $vars */
    public static function fill(string $tpl, array $vars): string
    {
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{' . $k . '}'] = (string) $v;
        }
        return strtr($tpl, $map);
    }

    /** Values of the placeholders for a meeting (+ attendee). @return array<string,string> */
    public static function vars(array $m, ?array $attendee = null, array $extra = []): array
    {
        $start = (string) ($m['start_at'] ?? '');
        $end = (string) ($m['end_at'] ?? '');
        $has = MeetingTime::normalise($start) !== null && MeetingTime::normalise($end) !== null;
        return $extra + [
            'title' => (string) ($m['title'] ?? ''),
            'when' => $has ? MeetingTime::rangeLabel($start, $end) : '',
            // "in 2 hours" / "in 25 minutes": how far away the start REALLY is when the message is built (reminders use it, so a late one never says "24 hours")
            'in' => $has ? MeetingTime::untilLabel($start) : '',
            'date' => $has ? MeetingTime::dateLabel($start) : '',
            'time' => $has ? MeetingTime::timeRange($start, $end) : '',
            'organiser' => (string) ($m['organiser_name'] ?? ''),
            'organiser_name' => (string) ($m['organiser_name'] ?? ''),
            'organiser_email' => (string) ($m['organiser_email'] ?? ''),
            'platform' => MeetingService::platformLabel((string) ($m['platform'] ?? 'other')),
            'site' => trim((string) setting('site_name', 'Lumina Africa Foundation')),
            'name' => trim((string) ($attendee['name'] ?? '')),
            'topic' => '', 'message' => '', 'note' => '',
        ];
    }

    /** One-line, header-safe, at most 200 characters (the title is cut to 100 first so the date survives). */
    public static function subject(string $tpl, array $vars): string
    {
        $vars['title'] = mb_strlen($vars['title']) > 100 ? mb_substr($vars['title'], 0, 99) . "\u{2026}" : $vars['title'];
        $s = trim((string) preg_replace('/\s+/u', ' ', self::fill($tpl, $vars)));
        return mb_strlen($s) > 200 ? mb_substr($s, 0, 199) . "\u{2026}" : $s;
    }

    // ------------------------------------------------------------------------------------------------ details (shared by e-mail and page)

    /**
     * The detail rows: when, organiser, how to join, passcode, dial-in, address. `ctx` picks the label set ('email' | 'page'); `join` = may the join
     * link be shown (the invitee page and invitations: yes; cancellations: no). Each row: key, label, text, url (null = plain text).
     * @return list<array{key:string,label:string,text:string,url:?string}>
     */
    public static function details(array $m, string $ctx = 'email', bool $join = true): array
    {
        $p = $ctx === 'page' ? 'page.' : 'email.';
        $rows = [];
        $rows[] = ['key' => 'when', 'label' => self::tf($p . 'when_label'), 'text' => MeetingTime::dateLabel((string) $m['start_at']) . ', ' . MeetingTime::timeRange((string) $m['start_at'], (string) $m['end_at']), 'url' => null];
        $mail = MeetingIcs::mailto((string) ($m['organiser_email'] ?? ''));
        $rows[] = ['key' => 'organiser', 'label' => self::tf($p . 'organiser_label'), 'text' => (string) $m['organiser_name'], 'url' => $mail !== '' ? 'mailto:' . $mail : null];
        if ($join) {
            $url = (string) ($m['join_url'] ?? '');
            $rows[] = ['key' => 'how', 'label' => self::tf($p . 'how_label'), 'text' => MeetingService::platformLabel((string) $m['platform']) . (stripos($url, 'tel:') === 0 ? ': ' . trim(rawurldecode(substr($url, 4))) : ''), 'url' => preg_match('~^(https://|tel:)~i', $url) === 1 ? $url : null];
            if (($m['passcode'] ?? '') !== '') {
                $rows[] = ['key' => 'passcode', 'label' => self::tf($p . 'passcode_label'), 'text' => (string) $m['passcode'], 'url' => null];
            }
            if (($m['dial_in'] ?? '') !== '') {
                $tel = preg_replace('/[^0-9+]/', '', (string) $m['dial_in']);
                $rows[] = ['key' => 'dial_in', 'label' => self::tf($p . 'dial_label'), 'text' => (string) $m['dial_in'], 'url' => ($tel !== '' && strlen((string) $tel) >= 5) ? 'tel:' . $tel : null];
            }
            if (($m['location'] ?? '') !== '') {
                $rows[] = ['key' => 'location', 'label' => self::tf($p . 'location_label'), 'text' => (string) $m['location'], 'url' => null];
            }
        }
        return $rows;
    }

    /** The https join link when there is one (tel: and others are not a "button" target). */
    public static function joinUrl(array $m): ?string
    {
        $u = (string) ($m['join_url'] ?? '');
        return preg_match('~^https://~i', $u) === 1 ? $u : null;
    }

    // ------------------------------------------------------------------------------------------------ the messages

    /**
     * Build ONE message for ONE recipient.
     *
     * kind: invite | update | cancel | removed | reminder_24h | reminder_1h | message
     * opts: test (bool: "[TEST] " subject, notice, answer links point at the admin page), message (kind message: free text), reason (cancel: overrides the stored
     *       reason), now (ICS DTSTAMP, tests), page_url (override of the invitee page URL)
     *
     * @param array<string,mixed> $meeting
     * @param array<string,mixed> $attendee row (token, name, email, rsvp)
     * @return array{to:string,subject:string,text:string,html:string,method:?string,ics:?string,kind:string,opts:array<string,mixed>}
     */
    public static function message(array $meeting, array $attendee, string $kind, array $opts = []): array
    {
        if (!in_array($kind, self::KINDS, true)) {
            throw new \InvalidArgumentException('Unknown message kind.');
        }
        $test = !empty($opts['test']);
        $vars = self::vars($meeting, $attendee);
        self::$ctx = $vars;
        $pageUrl = (string) ($opts['page_url'] ?? ($test ? MeetingService::adminUrl((int) ($meeting['id'] ?? 0), true) : MeetingService::inviteeUrl($attendee)));
        $icsUrl = $test ? '' : MeetingService::icsUrl($attendee);
        $cancelled = in_array($kind, ['cancel', 'removed'], true);
        $key = match ($kind) { 'reminder_24h' => 'reminder24', 'reminder_1h' => 'reminder1', default => $kind };
        $subject = self::subject(self::t('email.' . $key . '_subject'), $vars);
        if ($test) {
            $subject = trim(self::tf('email.test_prefix')) . ' ' . $subject;
        }
        $heading = self::fill(self::tf('email.' . $key . '_heading'), $vars);
        $intro = self::fill(self::tf(str_starts_with($kind, 'reminder') ? 'email.reminder_intro' : 'email.' . $kind . '_intro'), $vars);
        if ($kind === 'message') {
            $intro = '';
        }
        $join = (!$cancelled && $kind !== 'message') ? self::joinUrl($meeting) : null;
        $rows = self::details($meeting, 'email', !$cancelled && $kind !== 'message');
        if ($kind === 'cancel' || $kind === 'removed') {
            $reason = trim((string) ($opts['reason'] ?? ($meeting['cancelled_reason'] ?? '')));
            if ($kind === 'cancel' && $reason !== '') {
                $rows[] = ['key' => 'reason', 'label' => self::tf('email.cancel_reason_label'), 'text' => $reason, 'url' => null];
            }
        }
        $bodyText = '';
        if ($kind === 'message') {
            $bodyText = trim(str_replace(["\r\n", "\r"], "\n", (string) ($opts['message'] ?? '')));
        }
        $agendaText = (!$cancelled && $kind !== 'message' && !str_starts_with($kind, 'reminder')) ? self::agendaPlain($meeting) : '';
        $agendaHtml = ($agendaText !== '' && ($meeting['description'] ?? '') !== '') ? NewsletterRenderer::richHtml((string) $meeting['description'], 'email') : '';

        $rsvp = null;
        $rsvpLinks = [];
        if (in_array($kind, ['invite', 'update'], true) && !empty($meeting['allow_rsvp'])) {
            foreach ([['accept', 'accepted', 'yes', 'rsvp_accept'], ['maybe', 'tentative', 'maybe', 'rsvp_maybe'], ['decline', 'declined', 'no', 'rsvp_decline']] as [$q, , $tone, $lk]) {
                $rsvpLinks[] = [self::tf('email.' . $lk), $pageUrl . ($test ? '' : '?r=' . $q . '#answer'), $tone];
            }
            $rsvp = ['title' => self::tf('email.rsvp_title'), 'buttons' => $rsvpLinks];
        }
        $calendar = ($icsUrl !== '' && !$cancelled && $kind !== 'message') ? [self::tf('email.calendar_label'), $icsUrl] : null;
        $secondary = null;
        if (!$cancelled && $kind !== 'message' && $pageUrl !== '' && !$test) {
            $secondary = [self::tf('email.details_button'), $pageUrl];
            if ($join === null) {
                $join = $pageUrl;                                  // phone / in-person meetings: the main button opens the meeting page
            }
        }
        $joinPair = $join !== null ? [self::tf('email.join_button'), $join] : null;
        if ($joinPair !== null && $join === $pageUrl && $secondary !== null) {
            $joinPair[0] = self::tf('email.details_button');
            $secondary = null;
        }

        $notes = [];
        if (in_array($kind, ['invite', 'update', 'reminder_24h', 'reminder_1h'], true)) {
            $notes[] = self::tf('email.time_note');
        }
        $notes[] = self::fill(self::tf('email.reply_note'), $vars);
        $reasonLine = self::fill(self::tf('email.reason_line'), $vars);
        $extraNote = trim((string) setting('meeting_from_note', ''));
        $org = $vars['site'];
        $addr = trim(str_replace(["\r", "\n"], ' ', (string) setting('address', '')));
        $city = trim((string) setting('city_country', ''));
        $addrLine = trim($addr . ($addr !== '' && $city !== '' ? ', ' : '') . $city);
        $footer = array_values(array_filter([$extraNote, $reasonLine, $addrLine !== '' ? $org . ', ' . $addrLine : $org]));
        $notice = $test ? self::tf('email.test_notice') : '';

        $introHtml = '';
        foreach (preg_split('/\n{2,}/', trim($intro)) ?: [] as $para) {
            if (trim($para) !== '') {
                $introHtml .= '<p style="margin:0 0 12px 0;">' . nl2br(e(trim($para))) . '</p>';
            }
        }
        $bodyHtml = '';
        foreach (preg_split('/\n{2,}/', $bodyText) ?: [] as $para) {
            if (trim($para) !== '') {
                $bodyHtml .= '<p style="margin:0 0 14px 0;">' . nl2br(e(trim($para))) . '</p>';
            }
        }

        $html = View::render('meetings/email/layout', [
            'subject' => $subject, 'preheader' => $vars['when'] . ($vars['title'] !== '' ? ' · ' . $vars['title'] : ''), 'heading' => $heading,
            'kicker' => '', 'introHtml' => $introHtml, 'rows' => array_map(static fn(array $r): array => [$r['label'], $r['text'], $r['url']], $rows),
            'join' => $joinPair, 'secondary' => $secondary, 'agendaLabel' => self::tf('email.agenda_label'), 'agendaHtml' => $agendaHtml, 'bodyHtml' => $bodyHtml,
            'rsvp' => $rsvp, 'calendar' => $calendar, 'notes' => $notes, 'footerLines' => $footer, 'notice' => $notice,
            'logoUrl' => NewsletterRenderer::absoluteUrl(logo_url(64)), 'orgName' => $org, 'cancelled' => $cancelled,
        ]);

        // plain text twin
        $t = [$heading];
        if ($intro !== '') {
            $t[] = '';
            $t[] = $intro;
        }
        $t[] = '';
        foreach ($rows as $r) {
            $t[] = $r['label'] . ': ' . $r['text'] . ($r['key'] === 'how' && $join !== null && $join !== $pageUrl ? "\n" . self::tf('email.join_button') . ': ' . $join : '');
        }
        if ($bodyText !== '') {
            $t[] = '';
            $t[] = $bodyText;
        }
        if ($agendaText !== '') {
            $t[] = '';
            $t[] = self::tf('email.agenda_label') . ":\n" . $agendaText;
        }
        if ($rsvp !== null) {
            $t[] = '';
            $t[] = $rsvp['title'];
            foreach ($rsvpLinks as [$label, $url]) {
                $t[] = $label . ': ' . $url;
            }
        }
        if ($secondary !== null) {
            $t[] = '';
            $t[] = $secondary[0] . ': ' . $secondary[1];
        } elseif ($joinPair !== null && $join === $pageUrl) {
            $t[] = '';
            $t[] = $joinPair[0] . ': ' . $joinPair[1];
        }
        if ($calendar !== null) {
            $t[] = $calendar[0] . ': ' . $calendar[1];
        }
        $t[] = '';
        foreach ($notes as $n) {
            $t[] = $n;
        }
        $t[] = '';
        foreach ($footer as $f) {
            $t[] = $f;
        }
        if ($notice !== '') {
            array_unshift($t, $notice, '');
        }
        $text = implode("\n", $t) . "\n";

        // iCalendar
        $method = match ($kind) { 'invite', 'update' => 'REQUEST', 'cancel', 'removed' => 'CANCEL', default => null };
        $ics = null;
        $mail = [
            'html' => $html, 'reply_to' => (string) $meeting['organiser_email'], 'reply_to_name' => (string) $meeting['organiser_name'],
            'from_name' => self::fromName((string) $meeting['organiser_name'], $org), 'to_name' => (string) ($attendee['name'] ?? ''),
        ];
        $from = NewsletterMailer::sender()['from'];
        if (\App\Core\Mailer::validEmail($from)) {
            $mail['from'] = $from;
        }
        if ($method !== null) {
            $ics = MeetingIcs::build($meeting, $attendee, $method, array_filter([
                'page_url' => $test ? '' : $pageUrl, 'now' => $opts['now'] ?? null, 'alarm_minutes' => $method === 'REQUEST' ? 15 : null,
                'cancel_reason' => $kind === 'cancel' ? (string) ($opts['reason'] ?? ($meeting['cancelled_reason'] ?? '')) : '',
            ], static fn(mixed $v): bool => $v !== null && $v !== ''));
            $mail['calendar'] = ['method' => $method, 'content' => $ics];
            $mail['attachments'] = [['filename' => $method === 'CANCEL' ? 'cancel.ics' : 'invite.ics', 'content' => $ics, 'type' => 'application/ics']];
        }
        return ['to' => (string) $attendee['email'], 'subject' => $subject, 'text' => $text, 'html' => $html, 'method' => $method, 'ics' => $ics, 'kind' => $kind, 'opts' => $mail];
    }

    /** Display name of the sender: "Jane Doe via Lumina Africa Foundation" (the address stays the configured sender). */
    public static function fromName(string $organiser, string $site): string
    {
        $o = InviteeParser::cleanName($organiser);
        if ($o === '') {
            return $site;
        }
        $pattern = self::t('email.from_name');
        $name = InviteeParser::cleanName(self::fill($pattern, ['organiser' => $o, 'organiser_name' => $o, 'site' => $site]));
        return mb_substr($name !== '' ? $name : $o . ' via ' . $site, 0, 120);
    }

    // ------------------------------------------------------------------------------------------------ meeting-request e-mails

    /**
     * E-mails about a meeting REQUEST (Contact page form). kind: ack (to the visitor) | notify (to the organisation) | decline (to the visitor, with an optional note).
     * @param array<string,mixed> $req meeting_requests row (+ optional 'note' for decline)
     * @return array{to:string,subject:string,text:string,html:string,kind:string,opts:array<string,mixed>}
     */
    public static function requestMessage(string $kind, array $req, array $opts = []): array
    {
        $site = trim((string) setting('site_name', 'Lumina Africa Foundation'));
        $vars = ['site' => $site, 'name' => (string) $req['name'], 'topic' => (string) $req['topic'], 'message' => (string) $req['message'], 'note' => trim((string) ($opts['note'] ?? ''))]
            + self::vars([], null);
        self::$ctx = $vars;
        if ($kind === 'ack') {
            // The acknowledgement goes to an address nobody has proven to own: nothing the visitor typed (name, message) may appear in it, whatever the owner's
            // wording says. {topic} is one of the owner's own list entries, so it is safe.
            $vars['name'] = '';
            $vars['message'] = '';
        } elseif ($kind === 'decline') {
            // staff chose to answer this address, but the name is still the visitor's typing: letters, digits and a few marks only, no links
            $n = (string) preg_replace('~(?:https?://|www\.)\S*~iu', ' ', (string) $req['name']);
            $n = trim((string) preg_replace('/\s+/u', ' ', (string) preg_replace('/[^\p{L}\p{M}\p{N} \'.-]+/u', ' ', $n)));
            $vars['name'] = mb_substr($n, 0, 40);
            $vars['message'] = '';
        }
        self::$ctx = $vars;
        $contact = trim((string) setting('notify_email', '')) ?: trim((string) setting('email_primary', ''));
        if ($kind === 'notify') {
            $subject = self::subject(self::t('email.request_notify_subject'), $vars);
            $lines = ['A visitor asked to meet the team.', '', 'Name: ' . $req['name'], 'Email: ' . $req['email'], 'Phone: ' . ($req['phone'] ?: '-'), 'Organisation: ' . ($req['organisation'] ?: '-'),
                'Topic: ' . $req['topic'], 'Preferred date: ' . ($req['preferred_date'] ?: '-') . ' (' . ($req['preferred_time'] ?: 'any time') . ')',
                'Length: ' . ($req['duration_minutes'] ? $req['duration_minutes'] . ' minutes' : '-'), 'Type: ' . ($req['platform_pref'] ?: '-'), '', 'Message:', $req['message'], '',
                'Answer it in the admin: ' . abs_url('admin/meeting-requests/' . (int) ($req['id'] ?? 0))];
            $text = implode("\n", $lines) . "\n";
            $to = $contact;
            $mail = ['reply_to' => (string) $req['email'], 'reply_to_name' => (string) $req['name']];
            $paras = [$text];
        } else {
            $key = $kind === 'decline' ? 'request_decline' : 'ack';
            $subject = self::subject(self::t('email.' . $key . '_subject'), $vars);
            $text = trim(self::fill(self::t('email.' . $key . '_body'), $vars)) . "\n";
            if ($kind === 'ack') {
                $text = (string) preg_replace('/[ \t]+([,.;:!])/u', '$1', $text);                      // "Hello {name}," with no name -> "Hello,"
                $text = (string) preg_replace('/\n+Your message:\s*$/iu', '', rtrim($text)) . "\n";
            }
            $text = (string) preg_replace("/\n{3,}/", "\n\n", $text);
            $to = (string) $req['email'];
            // a display name from the visitor's own typing would be shown in the recipient's inbox: only the staff-initiated decline may carry it
            $mail = $kind === 'decline' && $vars['name'] !== '' ? ['to_name' => $vars['name']] : [];
            if (\App\Core\Mailer::validEmail($contact)) {
                $mail['reply_to'] = $contact;
            }
            $paras = preg_split('/\n{2,}/', trim($text)) ?: [];
        }
        $introHtml = '';
        foreach ($paras as $p) {
            if (trim($p) !== '') {
                $introHtml .= '<p style="margin:0 0 12px 0;">' . nl2br(e(trim($p))) . '</p>';
            }
        }
        $html = View::render('meetings/email/layout', [
            'subject' => $subject, 'preheader' => $subject, 'heading' => $subject, 'kicker' => '', 'introHtml' => $introHtml, 'rows' => [], 'join' => null, 'secondary' => null,
            'agendaLabel' => '', 'agendaHtml' => '', 'bodyHtml' => '', 'rsvp' => null, 'calendar' => null, 'notes' => [], 'footerLines' => [$site],
            'notice' => '', 'logoUrl' => NewsletterRenderer::absoluteUrl(logo_url(64)), 'orgName' => $site, 'cancelled' => false,
        ]);
        $mail['html'] = $html;
        return ['to' => $to, 'subject' => $subject, 'text' => $text, 'html' => $html, 'kind' => $kind, 'opts' => $mail];
    }

    // ------------------------------------------------------------------------------------------------ the invitee page

    /**
     * Everything the invitee page /meeting/{token} shows, from the same sources as the e-mail.
     * state: active | cancelled | ended. `join_url` is set only while the meeting is active and has an https link.
     * @return array<string,mixed>
     */
    public static function pageData(array $meeting, array $attendee): array
    {
        $ended = $meeting['status'] === 'completed' || MeetingTime::hasEnded($meeting);
        $state = $meeting['status'] === 'cancelled' ? 'cancelled' : ($ended ? 'ended' : 'active');
        $active = $state === 'active';
        $canRsvp = $active && $meeting['status'] === 'scheduled' && (int) $meeting['allow_rsvp'] === 1;
        self::$ctx = self::vars($meeting, $attendee);        // the view reads its words through tf(), so {title} {when} ... work in every field
        return [
            'state' => $state,
            'title' => (string) $meeting['title'],
            'vars' => self::vars($meeting, $attendee),
            'rows' => self::details($meeting, 'page', $active),
            'agenda_html' => ($active && ($meeting['description'] ?? '') !== '') ? \App\Core\Sanitizer::rich((string) $meeting['description']) : '',
            'join_url' => $active ? self::joinUrl($meeting) : null,
            'ics_url' => $active ? url('meeting/' . $attendee['token'] . '.ics') : null,
            'start_iso' => MeetingTime::utcIso((string) $meeting['start_at']),
            'end_iso' => MeetingTime::utcIso((string) $meeting['end_at']),
            'cancel_reason' => $state === 'cancelled' ? trim((string) ($meeting['cancelled_reason'] ?? '')) : '',
            'rsvp' => [
                'allowed' => $canRsvp,
                'current' => (string) $attendee['rsvp'],
                'at' => $attendee['rsvp_at'],
                'options' => ['accepted' => self::tf('rsvp.accept'), 'tentative' => self::tf('rsvp.maybe'), 'declined' => self::tf('rsvp.decline')],
            ],
        ];
    }

    /**
     * The meeting description as plain text for the text part of the e-mail and the calendar file: paragraphs and list items become lines, and a link keeps its
     * target ("label (https://...)"; a site-relative link is made absolute here, at send time, so the stored text stays independent of the domain).
     * Falls back to the stored plain-text twin (`agenda`).
     */
    public static function agendaPlain(array $m): string
    {
        $html = trim((string) ($m['description'] ?? ''));
        if ($html !== '') {
            $t = \App\Services\Newsletter\NewsletterContent::htmlToText($html, true);
            if ($t !== '') {
                return $t;
            }
        }
        return trim((string) ($m['agenda'] ?? ''));
    }

    /** Plain-text agenda for the admin preview (HTML stripped). */
    public static function agendaText(array $m): string
    {
        return IcsBuilder::plainText((string) ($m['description'] ?? ''));
    }

    /** Label of a meeting's platform for lists (kept here so admin and mail agree). */
    public static function platformLabel(array $m): string
    {
        return Meeting::PLATFORMS[(string) ($m['platform'] ?? 'other')] ?? 'Other';
    }
}
