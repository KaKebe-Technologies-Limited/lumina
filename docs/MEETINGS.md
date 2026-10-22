# Meeting scheduler - CONTRACT

> Read `docs/ARCHITECTURE.md` and `docs/DESIGN.md` first; `docs/CALENDAR.md`, `docs/NEWSLETTER.md` (SMTP driver, mock server, batch queue pattern) and `docs/MEMBERSHIP.md` are worked examples of the same conventions.
> Three agents build on this in parallel: the **ADMIN agent** (schedule form, meeting page, request inbox), the **MAIL agent** (sending, queue, cron, reminders) and the **WEB agent**
> (invitee page, Contact "Request a meeting"). The **architect** files (schema, seed, models, `app/services/Meetings/*`, Mailer, registry, settings, abilities, routes, nav fragment, pickers controller, selftest, this file)
> are **frozen**: call them, do not copy or edit them. Missing something? Add your own file and list the gap in your report.

Owner request: *a meeting scheduler where we can include a meeting link and e-mail to create meetings; add members' e-mails to invite them; put it under Contact or Scheduler (the Calendar group) and make it easy.*

Everything is **additive and idempotent**: three new tables, no change to existing ones (the guards below touch existing PHP only). Re-importing `database/lumina-live-import.sql`
(`php bin/build-import-sql.php`) upgrades a live database without data loss and without overwriting owner edits (tested: previous committed live import, then the new file twice).

**Nothing is invented**: no sample meeting, attendee or request is seeded. The site **cannot create Zoom / Google Meet / Teams rooms** (no vendor accounts or APIs): the organiser pastes a link. The one built-in
exception is the optional button "Generate a free private video room", which makes an unguessable `https://meet.jit.si/LuminaMeeting-<32 hex>` link (Jitsi Meet is an external free service; nothing is created
until somebody opens the link). The UI must say so (the endpoint returns the sentence in `notice`).

---------------------------------------------------------------------------------------------------------------------

## 1. What exists (architect, done and tested)

| Area | Files |
|---|---|
| Schema / seed | `database/schema.sql` (end): `meetings`, `meeting_attendees`, `meeting_requests`. `database/seed.sql` (end): 5 `meeting_*` settings, pages row `site_meetings` (virtual). **No sample data.** The calendar type "Meeting / outreach" (slug `meeting`) already exists in the calendar seed; `MeetingService::calendarTypeId()` recreates it at run time if the owner deleted it |
| Models | `app/models/Meeting.php`, `MeetingAttendee.php`, `MeetingRequest.php` (generic `Model` API + constants) |
| Services | `app/services/Meetings/`: `MeetingTime`, `JitsiLink`, `InviteeParser`, `MeetingIcs`, `MeetingService` (all rules), `MeetingRenderer` (all wording), `MeetingRequestService` |
| E-mail layout | `app/views/meetings/email/layout.php` (one table-based layout for every meeting e-mail), shared partial `app/views/partials/meeting-details.php` (detail rows of the invitee page AND the admin meeting page) |
| Mailer (core, additive) | `app/core/Mailer.php`: opts `calendar`, `attachments`, `to_name`, `reply_to_name` (section 9). Every existing caller is byte-compatible |
| Registry | `app/config/pages/site_meetings.php` (virtual page "Meetings: invitee page & e-mail wording": invitee page, answer buttons, every e-mail subject and body); `app/config/pages/contact.php` new section `meeting_request` (toggle, labels, errors) |
| Settings / abilities | `settings_schema.php` group **Meetings**; `config/app.php`: `meetings.manage` (admin, editor), `meetings.bulk` (admin), `meetings.delete` (admin); reserved page slugs `meeting`, `meetings` |
| Routes | `app/routes/admin_meetings.php`, `app/routes/web_meetings.php`; sidebar `app/config/admin_nav/meetings.php` (merged into the **Calendar** group through the new `merge_into` key of `AdminNav`; badge = new requests) |
| Controllers | `Admin/MeetingPickersController` **complete** (parser, member / staff / earlier-attendee pickers, Jitsi). STUBS: `Admin/MeetingsController` (index works), `Admin/MeetingRequestsController` (index works), `Admin/MeetingMailController` (501), `Web/MeetingController` (501), `Web/MeetingRequestController` (501) |
| Views / styles / JS stubs | `views/admin/meetings/{index,requests}.php` (baselines), `views/web/meetings/show.php` (empty), `scss/admin/_meetings.scss`, `scss/pages/_meetings.scss` (imported), `assets/js/admin-meetings.js`, `admin-meetings-send.js`, `meetings.js` (headers only) |
| Shared-file guards | `CalendarWriter` refuses to edit / duplicate / delete / override / bulk-change a calendar entry that a meeting owns (`CalendarWriter::ownedByMeeting()`, message `CalendarWriter::MEETING_OWNED`); `MembershipApplication::anonymise()` calls `MeetingService::erase()`; `SeoController` robots disallows `/meeting/`; `DashboardController` shows the two tiles and the quick action "Schedule a meeting" |
| Tests | `bin/selftest-meetings.php` (about 190 checks, run by `bin/selftest.php`; starts its own mock SMTP server; removes its own data) |

## 2. Decisions and additions to the owner's brief (read these)

| Topic | Decision |
|---|---|
| Schedule storage | The meeting is the **source of truth**: `meetings.start_at` / `end_at` (EAT `DATETIME`, same calendar day, 5 minutes to 12 hours). The calendar entry is **derived** (section 6). These two columns are an addition to the brief |
| Extra columns (additive) | `meetings.cancelled_at`, `send_paused`, `send_halt_reason`; `meeting_attendees.queue_kind` (`invite` / `update` / `cancel`: what a queued row will send), `reminder_attempts`; `meeting_requests.admin_notes` |
| `description` / `agenda` | `description` = the sanitised HTML typed in the rich-text editor (e-mail, invitee page); `agenda` = its plain-text twin, derived on every save (ICS, text part). Never write `agenda` yourself |
| Request topics | Live in the **setting** `meeting_request_topics` (one per line, editable by administrators in Settings > Meetings), not in the registry, so there is ONE list. The Contact registry section holds the form wording and the on/off switch |
| Request on/off | The form is shown only when ALL are true: setting `meeting_request_enabled = 1`, registry switch `contact` > `meeting_request` is on, the `contact` page is published, `MeetingService::available()` |
| Registry page slug | `site_meetings` (virtual, no URL). It needs a `pages` row (foreign key of `content_blocks`): seeded |
| Sidebar | Items live in the **Calendar** group (Meetings, Schedule a meeting, Meeting requests). `AdminNav` fragments may now carry `'merge_into' => '<group key>'` |
| Calendar entry guard | The calendar manager can no longer change a meeting's entry. The ADMIN agent must still make the calendar screens friendly (section 12) |
| Reminders | Both off in the database by default; the **form defaults** tick both. A reminder is sent only to invited people who have not declined and were invited before the reminder moment |
| Answers reset | A new **start** (date or start time) resets every answer to "no answer yet" (declines too) and the sent-reminder marks (the reminders restart for the new start). A change of the **end** only (a longer meeting) asks accepted / maybe answers again but **keeps declines and keeps the reminder marks** (reminders are scheduled from the start, so nobody gets a second "starts in 24 hours" for the same start). Other changes keep answers |
| Test invitation | Goes to the signed-in user's own address only, uses the real UID / SEQUENCE and `[TEST]` in the subject; its buttons do not record an answer |
| Free-text "Send an update" | Not queued: the browser sends it in chunks of at most 30 attendee ids (`POST .../mail/message`). It needs the page open, has no retry state and is not stored |
| Declined people | Do not get invitations, updates or reminders by default (queue scopes skip them); they DO get a cancellation (they may still hold the event). The admin can resend to one person whatever their answer (`scope = one`) |

---------------------------------------------------------------------------------------------------------------------

## 3. Data model

### `meetings`
`id, calendar_entry_id (UNIQUE FK calendar_entries ON DELETE CASCADE), uid (UNIQUE; lumina-meet-<24 hex>@<host>, stable for life), title (cache of the entry title), platform ENUM(zoom, google_meet, teams, jitsi, whatsapp, phone, in_person, other), join_url (https; tel: allowed for phone / whatsapp), passcode, dial_in, location, description (HTML), agenda (plain), start_at, end_at, organiser_name, organiser_email, status ENUM(draft, scheduled, cancelled, completed), sequence INT (iCalendar SEQUENCE), visibility ENUM(private, public), show_join_link_public, reminder_24h, reminder_1h, allow_rsvp, cancelled_reason, cancelled_at, invitations_sent_at, send_paused, send_halt_reason, created_by, updated_by, created_at, updated_at`.
Indexes: `(status, start_at)`, `start_at`, unique `calendar_entry_id`, unique `uid`.
`join_url`, `passcode` and `dial_in` are **secrets**: never in a public endpoint, sitemap, feed or JSON unless `visibility = public AND show_join_link_public = 1` (and then only the join link, on the calendar entry).

### `meeting_attendees`
`id, meeting_id (FK CASCADE), name, email (lower-case), source ENUM(manual, member, user, previous, request), member_id, user_id, token (UNIQUE, 48 hex = 192 bits), rsvp ENUM(pending, accepted, declined, tentative), rsvp_at, invite_status ENUM(not_sent, queued, sent, failed), queue_kind ENUM(invite, update, cancel) NULL, invite_sequence (SEQUENCE of the last message handed to the mail server; NULL = never), attempts (for the CURRENT queued message, max 3), last_error (255, no credentials, no addresses), invite_sent_at (first time an invitation reached the mail server; NULL = never invited), reminder_24h_sent_at, reminder_1h_sent_at, reminder_attempts, removed_at, created_at, updated_at`.
**UNIQUE (meeting_id, email)**: nobody is invited twice. Removed attendees keep their row (`removed_at`): their token stops working (`attendeeByToken` returns null) and adding them again issues a **new token** and a clean state.
**PERSONAL DATA**: the address never leaves the admin area and never reaches a public endpoint, a log or an audit entry (audit details carry ids and counts only).

### `meeting_requests`
`id, name, email, phone, organisation, topic (the topic label), message, preferred_date, preferred_time (any | morning | afternoon | evening, EAT), duration_minutes (15/30/45/60/90), platform_pref ENUM(video, phone, in_person), status ENUM(new, approved, declined, closed), meeting_id (FK SET NULL), handled_by, admin_notes, consent_at, ip_hash (keyed hash, 40 hex), created_at, updated_at`.

## 4. State machines

**Meeting** (`MeetingService::TRANSITIONS`)
```
draft --schedule()--> scheduled --completeDue() (end time passed)--> completed
draft | scheduled --cancel()--> cancelled --reopen()--> draft          (scheduled --> draft only while invitations_sent_at IS NULL)
```
Only `draft` and `scheduled` can be edited (`update`) and receive attendees. `completed` is final (duplicate it). `delete()` works for a **never-sent draft** only (deletes the calendar entry; attendees cascade); everything else is cancelled.
`completeDue()` is lazy: called by `listMeetings`, and must be called by the invitee page and the cron job.

**SEQUENCE**: starts at 0. `update()` adds 1 when a field in `MeetingService::INVITE_FIELDS` (title, start_at, end_at, platform, join_url, passcode, dial_in, location, description, organiser_name, organiser_email) changed **and** `invitations_sent_at` is not null. `cancel()` adds 1 when anyone was invited (a METHOD:CANCEL must outrank the last REQUEST). `reopen()` of a cancelled meeting that was invited adds 1 again. An option-only change (reminders, RSVP switch, visibility) does not bump it. **Removing a person with a cancellation e-mail adds 1** (the METHOD:CANCEL they get must outrank the last REQUEST they got) and **adding back someone who had been invited adds 1 again** (the new REQUEST must outrank that CANCEL). These two bumps change no content, so `bumpSequence()` moves every attendee who was up to date (`invite_sequence` = the old value) up with it: nobody turns "stale" and nobody earns an update e-mail. The SEQUENCE of one UID therefore only ever goes up, for every mailbox. `cancel()` leaves a removed person's waiting "no longer invited" notice queued (it is not about the old plan) so it is still sent.

**Attendee invite state**
```
not_sent --queue()--> queued --claim()+markSent()--> sent
                        |  \--markFailed(permanent or 3rd attempt)--> failed --queue('retry_failed')--> queued
                        \--markFailed(transient, attempts < 3)--> queued (next batch)
sent --queue('update_all') when invite_sequence < sequence--> queued (kind update)
meeting cancelled --queue('cancel_all')--> queued (kind cancel);  removeAttendee(.., true) --> queued (kind cancel)
```
`queue_kind` says what the queued row sends. `cancel()` drops anything still waiting about the old plan (queued rows go back to their previous state) before `cancel_all` is queued.

**RSVP** (`MeetingService::setRsvp`): `pending` -> `accepted | tentative | declined`, changeable while the meeting is `scheduled`, `allow_rsvp = 1` and not ended. Codes: `ok`, `not_found`, `rsvp_off`, `cancelled`, `ended`, `invalid`.

**Request**: `new -> approved | declined | closed`, any status can be set again by staff (`MeetingRequestService::setStatus`); `declined` with `notify` e-mails the visitor once. "Schedule this meeting" ends with `attachMeeting()` (status `approved`).

## 5. Service API (`App\Services\Meetings\`, all static)

### `MeetingTime` (pure)
`TZ`, `LABEL` (EAT), `OFFSET` (10800), `DURATIONS` [15,30,45,60,90], `MAX_MINUTES` (720) - `now()`, `today()`, `freeze(?int $utcEpoch)` (tests), `normalise(mixed): ?string` ('Y-m-d H:i:s'), `combine(date, time)`, `toTimestamp(local)`, `fromTimestamp(epoch)`, `utcStamp(local)` (`20261024T070000Z`), `utcIso(local)` (`2026-10-24T07:00:00Z`), `date()`, `time()`, `addMinutes()`, `minutesBetween()`, `rangeLabel(start, end)` ("Sat 24 Oct 2026, 10:00–11:00 EAT", en dash), `timeRange()`, `dateLabel()` ("Saturday 24 October 2026"), `durationLabel(min)`, `hasEnded(meeting)`, `hasStarted()`, `reminderAt(start, hours)`, `fromForm(date, start_time, duration, end_time): {ok, errors{date|start_time|end_time|duration}, start_at, end_at, duration}` (an end time wins over a duration).

### `JitsiLink`
`generate(): string` (`https://meet.jit.si/LuminaMeeting-<32 hex>`), `isJitsi(url)`.

### `InviteeParser`
`parse(string $raw, array $skip = []): {valid:[{name,email}], invalid:[{input,reason}], duplicates:int, skipped:int, truncated:bool}` (comma / semicolon / space / new line, `Name <addr>`, `"Last, First" <addr>`, `mailto:`, brackets; lower-cased, validated, de-duplicated; at most 1000 tokens), `cleanAddress()`, `cleanName()`. Fuzz-tested: every accepted address is a plain valid address, every name is header-safe.

### `MeetingIcs`
`build(array $meeting, ?array $attendee, string $method = 'REQUEST', array $opts = []): string` (section 8), `newUid()`, `host()`, `location($m)`, `description($m, $opts)`, `param($v)`, `mailto($e)`.

### `MeetingService`
| Signature | Purpose |
|---|---|
| `available()`, `flush()`, `validToken()`, `newToken()`, `platformLabel()`, `inviteeUrl($attendee)`, `icsUrl($attendee)`, `adminUrl($id, $absolute)` | basics; `inviteeUrl` is an absolute URL built from `APP_URL` |
| `defaults(?array $user, array $prefill): array` | schedule-form defaults: organiser = signed-in user, duration / platform from settings, both reminders ticked, RSVP on, private |
| `find($id)`, `findByUid()`, `formData($meeting)`, `listMeetings(array $filters, int $page, int $perPage)`, `statusCounts()`, `attendees($id, $includeRemoved)`, `summary($id)`, `attendeeByToken($token)`, `entryMeetingMap(array $entryIds)` | reads. `listMeetings` filters: `status`, `q`, `when` (`upcoming | past | all`); items carry `n_attendees n_accepted n_declined n_tentative n_failed n_queued`. `attendeeByToken` returns `{attendee, meeting}` or null (malformed, unknown or removed) |
| `validate(array $in, ?array $existing): {ok, errors, row}` | the whole form, see section 11 for the input names |
| `readiness(array $meeting): array<field,message>` | what is missing before scheduling (join link for online platforms, address for in person, number for phone, future start, organiser e-mail) |
| `create(array $in, ?int $userId): {ok, errors, id, meeting}` | a DRAFT + its calendar entry |
| `update(int $id, array $in, ?int $userId): {ok, errors, meeting, changed[], sequence_bumped, update_suggested}` | `update_suggested` = offer "Send updated invitation to everyone" |
| `schedule($id, $userId)`, `cancel($id, $reason, $userId): {.., notify}`, `reopen()`, `completeDue()`, `duplicate($id, $userId, $copyAttendees)`, `delete($id)`, `canTransition()` | status changes (section 4) |
| `addAttendees($meetingId, list<item>, ?$userId, ['allow_bulk' => bool]): {ok, errors, added, reactivated, existing, ids[]}` | item = `{email*, name, source, member_id, user_id}`. For `source = member|user` the name and address are read from the database by id (unknown ids dropped; never trust a posted address). Max 200 per meeting; more than 50 new at once needs `allow_bulk` (pass `can('meetings.bulk')`). Nothing is sent |
| `bulkItems('members_all' | 'members_type:ID'): ?{items, total, capped}` | server-side expansion of "add all approved members / members of type X" (capped at 500) |
| `removeAttendee($meetingId, $attendeeId, bool $sendCancellation, $userId): {ok, error, queued_cancel}` | IDOR-guarded |
| `setRsvp($token, $answer): {ok, error, code}` | section 4 |
| `queue($meetingId, $scope, array $opts): {ok, queued, error}` | scopes `invite_new`, `invite_pending`, `update_all`, `cancel_all`, `one` (opts.attendee_id), `retry_failed`. Idempotent |
| `claim($attendeeId, ?int $seenAttempts): bool`, `markSent($attendeeId, int $sequence)`, `markFailed($attendeeId, $error, bool $permanent): string`, `release($attendeeId)`, `pause($id, $reason)`, `resume($id)`, `meetingsWithQueue()`, `queuedRows($meetingId, $limit)` | what the batch sender uses (section 12.3) |
| `dueReminders(?$now, $limit): list<{meeting, attendee, which}>`, `claimReminder($attendeeId, '24h'|'1h'): bool`, `reminderFailed($attendeeId, $which, bool $transient)` | reminder selection and marks |
| `searchMembers($q)`, `memberTypeCounts()`, `membersForBulk($typeId)`, `staff()`, `previousAttendees($q)` | pickers |
| `csvRows($meetingId)`, `CSV_HEADER` | attendee export (hand to `CsvExport::response`: formula-safe) |
| `dashboardTiles()`, `newRequestCount()`, `erase($email, ?$memberId)` | dashboard, sidebar badge, privacy |
| `entryInput($meeting)`, `syncCalendar($meeting, $userId)`, `calendarTypeId()` | the derived calendar entry (section 6) |
| constants | `MAX_ATTENDEES 200`, `MAX_BULK_MEMBERS 500`, `BULK_THRESHOLD 50`, `INVITE_FIELDS`, `ONLINE_PLATFORMS`, `TRANSITIONS`, `STATUS_LABELS`, `STATUS_TONES`, `RSVP_LABELS`, `INVITE_LABELS`, `SOURCE_LABELS` |

### Additions after the review (also read the code comments)
`MeetingTime::untilLabel($start, $now)` ("now", "in 25 minutes", "in 2 hours", "in 3 days"); `MeetingRenderer::tf($key)` / `useVars()` (registry text with the placeholders of the current message filled), `agendaPlain($meeting)` (description as text with `label (url)` links, made absolute at send time); `MeetingRequestService::ipKey($ip)` (IPv6 -> /64) and `multiline($text, $max)` (free text that keeps its line breaks); `MeetingService::JOIN_LABEL_TOKEN`, `calendarJoinLabel()`; private `bumpSequence()`; global helper `analytics_id()`.

### `MeetingRenderer`
`message(array $meeting, array $attendee, string $kind, array $opts = []): {to, subject, text, html, method, ics, kind, opts}` with kind `invite | update | cancel | removed | reminder_24h | reminder_1h | message`
(opts: `test`, `message` for kind `message`, `reason`, `now`, `page_url`). **`opts` is the ready Mailer option array**: `html`, `reply_to` + `reply_to_name` (organiser), `from_name` ("Jane Doe via Lumina Africa Foundation"), `from` (the configured sender, never the organiser), `to_name`, and for invite / update / cancel / removed also `calendar` (inline `text/calendar; method=...`) and `attachments` (`invite.ics` / `cancel.ics`). Send with `Mailer::attempt($m['to'], $m['subject'], $m['text'], $m['opts'])`.
`requestMessage('ack' | 'notify' | 'decline', array $request, ['note' => ...])`, `pageData($meeting, $attendee)` (everything the invitee page shows), `details($meeting, 'email' | 'page', $withJoin)`, `joinUrl()`, `vars()`, `fill()`, `subject()`, `t($registryKey)`, `fromName()`, `PLACEHOLDERS`, `KINDS`.

### `MeetingRequestService`
`enabled()`, `topics()`, `windows()`, `platformPrefs()`, `create(array $in, array $ctx): {ok, errors, id, duplicate?, throttled?}` (validates with the editable registry error texts, stores, sends BOTH e-mails; `ctx.mail = false` for tests), `listRequests($filters, $page, $perPage)`, `statusCounts()`, `setStatus($id, $status, $userId, ['notify', 'note', 'notes'])`, `attachMeeting($requestId, $meetingId, $userId)`, `scheduleDefaults($requestId, ?$user): {form, attendees, request_id}`, `csvRows($filters)`, `delete($id)`.

## 6. The calendar entry a meeting owns

Created, kept in step and hidden / shown **only** by `MeetingService` (through `CalendarWriter::saveEntry(..., ['meeting' => true])`). Type `meeting` ("Meeting / outreach"), timed (`all_day = 0`), EAT date and times from `start_at` / `end_at`, `recurrence = none`, **no description, no location, no attendee data**.

| Meeting | `is_public` | `is_active` | `link_url` |
|---|---|---|---|
| draft (any visibility) | 0 | 1 | - |
| scheduled / completed, private | 0 | 1 | - |
| scheduled, public | 1 | 1 | the join link only when `show_join_link_public = 1`. The entry stores the token `{meeting_join}` as its link text; `OccurrenceExpander` swaps it for the registry text `site_meetings` > `page.calendar_join` (default "Join the meeting") each time it is shown, so the wording is editable and never stale |
| completed, public | 1 | 1 | **no link** (the entry stays listed as a past event; a finished meeting must not keep advertising a possibly reusable room in the calendar, the feed and the .ics) |
| cancelled | 0 | 0 | - |

So a private meeting is invisible to every public query, `/calendar/feed`, `/calendar.ics`, the Contact block and the sitemap (tested), and a public one shows type "Meeting / outreach", title, date and time. The admin calendar (`public = false`) lists drafts and private meetings; cancelled ones are inactive.
`CalendarWriter` refuses `saveEntry`, `duplicate`, `delete`, `saveOverride` and the bulk actions on such an entry (bulk counts them as skipped). **The ADMIN agent must therefore**: (1) in the calendar manager, month view, list and JSON (`CalendarApiController::month`) replace the edit link of an owned entry by the meeting page (`MeetingService::entryMeetingMap($entryIds)` gives entry id => meeting id) and show a "Meeting" cue; (2) make `CalendarController::edit` redirect an owned entry to `/admin/meetings/{id}`; (3) hide toggle / duplicate / delete for owned entries; (4) add a prominent "Schedule a meeting" button to the Calendar manager header (`admin/calendar/index.php`) and to the Contact-side admin screen (Inbox > Messages list header). All additive.

## 7. E-mails: messages, wording, placeholders

One message **per recipient** (To: only that person, with their name). Nobody sees another invitee's address (tested). From = the configured sender (`newsletter_from_email` else `MAIL_FROM`) with the display name "Organiser via Site"; **Reply-To = organiser e-mail + name**. **The organiser's address is never the From address** (SPF / DMARC). No `List-Unsubscribe` (transactional).

| Kind | Subject (registry `site_meetings` > `email.*`, default) | iCalendar | Buttons |
|---|---|---|---|
| `invite` | `Invitation: {title} — {when}` | inline `method=REQUEST` + `invite.ics` | Join meeting, Accept / Maybe / Decline (links to `/meeting/{token}?r=accept|maybe|decline#answer`), Open the meeting page, Add to calendar |
| `update` | `Updated: {title} — {when}` | same UID, higher SEQUENCE | same as invite |
| `cancel` | `Cancelled: {title} — {when}` | inline `method=CANCEL` + `cancel.ics` | none (shows the reason) |
| `removed` | `You are no longer invited: {title}` | `method=CANCEL` | none |
| `reminder_24h` / `reminder_1h` | `Reminder: {title} starts {in} — {when}` / `Starting soon: {title} starts {in} — {when}` (`{in}` = how far away the start REALLY is when the message is built: "in 23 hours", "in 2 hours", "in 25 minutes", so a late reminder never claims "24 hours"; the 24-hour reminder is sent while more than 2 hours remain, the 1-hour reminder while more than 10 minutes remain) | none | Join meeting |
| `message` | `About {title}` | none | - (organiser text) |
| request `ack` / `notify` / `decline` | `We received your meeting request` / `New meeting request: {topic}` / `About your meeting request` | none | - |

Placeholders: `{title} {when} {date} {time} {organiser} {organiser_name} {organiser_email} {platform} {site} {name} {in}` work in **every** field of the invitee page and of the e-mails (`MeetingRenderer::tf()` fills them from the message / page being built; subjects keep `t()` + `subject()` so the title is still cut). The e-mails about a meeting REQUEST know only `{topic} {site} {name} {note}` (there is no meeting yet, so `{title} {when} {organiser}` are empty; the acknowledgement never fills `{name}` / `{message}`, see section 13). Unknown placeholders stay as typed. A blank override falls back to the registry default. The title is cut to 100 characters in subjects so the date survives (subjects are one line, at most 200 characters).
The HTML is a 600 px table layout with inline CSS, serif fallbacks, absolute URLs from `APP_URL`, a hidden preheader, escaped text and sanitised agenda (`NewsletterRenderer::richHtml`), plus the plain-text twin. A phone / in-person meeting (no https link) turns the main button into "Open the meeting page". The "your local time" hint is the note "Your calendar app shows the meeting in your own time zone" plus, on the invitee page, `assets/js/meetings.js` reading `data-meeting-start`.

## 8. iCalendar rules (`MeetingIcs`)

`VERSION:2.0`, `PRODID:-//Lumina Africa Foundation//Meetings//EN`, `CALSCALE:GREGORIAN`, `METHOD:REQUEST | CANCEL | PUBLISH`, CRLF, lines folded at 75 octets (never inside a UTF-8 character), TEXT escaped (`\\ \; \, \n`), `VTIMEZONE Africa/Kampala` (+03:00, no DST), `UID` stable per meeting, `DTSTAMP` UTC, `SEQUENCE`, `DTSTART/DTEND;TZID=Africa/Kampala`, `SUMMARY` (cancel: "Cancelled: ..."), all calendar-file words (the cancelled prefix and note, the alert text, the labels Join / Passcode / Dial-in / Location / Answer and details) are editable registry text `site_meetings` > `ics.*`, `DESCRIPTION` (agenda + join details + page link), `LOCATION` (join link, or the address for in-person), `URL` (join link, else the page), `ORGANIZER;CN="Name":mailto:...`, **exactly one `ATTENDEE`** (the recipient; `PARTSTAT=NEEDS-ACTION;RSVP=FALSE` always: a native Accept / Decline in a mail app is an iTIP REPLY mailed to the ORGANIZER's own mailbox and never reaches this site, so the answer would not be recorded, declined people would still be reminded and the organiser's inbox would fill with replies the admin does not show. **The only answer path is the site's own Accept / Maybe / Decline buttons** (in the e-mail, and on the invitee page that the DESCRIPTION links to). Inbound iTIP parsing is deliberately not built), `STATUS:CONFIRMED | CANCELLED`, `TRANSP`, optional 15-minute `VALARM` (REQUEST only). Parameter values are always quoted without inner quotes; CR / LF / NUL in any field cannot create a property (tested). The invitee page's "Add to calendar" is `METHOD:PUBLISH` with the invitee's own answer as `PARTSTAT`.

## 9. Mailer extension (core, additive)

`App\Core\Mailer::attempt($to, $subject, $text, $opts)` gained, besides `reply_to`, `from`, `from_name`, `html`, `headers`, `return_path`:

| Option | Meaning |
|---|---|
| `to_name`, `reply_to_name` | display names (only with one recipient for `to_name`); RFC 2047 / quoted, CR / LF safe |
| `calendar` | `['method' => 'REQUEST', 'content' => 'BEGIN:VCALENDAR...']`: an inline `text/calendar; charset=UTF-8; method=REQUEST` part inside `multipart/alternative` (Gmail, Outlook and Apple Mail show the event with an Add-to-calendar action; the attendee line carries `RSVP=FALSE` so they do **not** show their own Accept / Decline bar, see section 8). Invalid method / content is dropped |
| `attachments` | `[['filename' => 'invite.ics', 'content' => bytes, 'type' => 'application/ics']]` (max 5 files, 5 MB): wraps the alternative in `multipart/mixed`; the file name loses paths, quotes and control characters, a non-ASCII name also gets `filename*=UTF-8''...`, a bad type becomes `application/octet-stream` |

Structure of an invitation: `multipart/mixed { multipart/alternative { text/plain, text/html, text/calendar;method=REQUEST }, application/ics; name="invite.ics" }`, every part base64 (so no content can contain a boundary), unique boundaries, 76-column bodies. Works for the `file`, `mail()` and `smtp` drivers (one `compose()`); tested with the file driver and the mock SMTP server. Without `html`, `calendar` and `attachments` the output is exactly what it was before (newsletter, membership and contact mail are unchanged).

## 10. Routes

CSRF is automatic on POST; JSON clients send `X-CSRF-Token` and `Accept: application/json`. **Role** column: M = `meetings.manage` (admin + editor, checked by the route group AND inside every method); A = also `meetings.bulk` / `meetings.delete` (admin).

### Public (`web_meetings.php`, WEB agent)
| Method | Path | Handler | Notes |
|---|---|---|---|
| GET | `/meeting/{token}` | `Web\MeetingController::show` | `{token}` = `[a-f0-9]{48}`; throttle 90/min per IP. Unknown, malformed or removed = plain 404. The token lookup comes first and **a valid token always works**; only FAILED lookups are counted per visitor address (an IPv6 address counts as its /64), and after 30 of them in 10 minutes further failed lookups answer 429. (A shared office, campus or mobile-carrier address can therefore never lock invitees out of their join link.) Behind a reverse proxy `TRUSTED_PROXIES` must be set, otherwise every visitor shares the proxy's address. `noindex`, `Cache-Control: private, no-store`, `Referrer-Policy: no-referrer` recommended. Never lists other attendees. `?r=accept|maybe|decline` only **preselects** the answer (no side effect) |
| POST | `/meeting/{token}` | `rsvp` | field `rsvp` = `accepted | tentative | declined`; CSRF; throttle 30/min; calls `MeetingService::setRsvp`; redirect back with a flash from `rsvp.saved_*` or the error text |
| GET | `/meeting/{token}.ics` | `ics` | `METHOD:PUBLISH`, `Content-Disposition: attachment; filename="meeting.ics"`, no-store; 404 for a cancelled meeting is NOT wanted: it returns the CANCEL file (calendars remove the event) |
| POST | `/contact/meeting-request` | `Web\MeetingRequestController::submit` | honeypot + `throttle:5,600` + `page.published:contact`; calls `MeetingRequestService::create`; success: flash `success_title` / `success_text` and redirect to `contact#meeting-request`; failure: `$this->fail($errors, 'contact#meeting-request')` |

### Admin pages (`admin_meetings.php`)
| Method | Path | Role | Handler / state |
|---|---|---|---|
| GET | `/admin/meetings` | M | `MeetingsController::index` (`?status= &when= &q= &page=`) - baseline works |
| GET | `/admin/meetings/create` | M | `create` (`?request=ID` prefill via `MeetingRequestService::scheduleDefaults`, `?date=`) |
| POST | `/admin/meetings` | M | `store` (`intent = draft | send`) |
| GET | `/admin/meetings/{id}` | M | `show` (status, details, attendee table, sending panel, actions) |
| GET | `/admin/meetings/{id}/edit` | M | `edit` |
| POST | `/admin/meetings/{id}` | M | `update` (`intent = save | send`; `send` also queues `update_all` after the save) |
| POST | `/admin/meetings/{id}/cancel` | M | `cancel` (`reason`, `send_cancellation`: 1 = queue `cancel_all`) |
| POST | `/admin/meetings/{id}/reopen`, `/duplicate`, `/delete` | M | `reopen`, `duplicate` (`copy_attendees`), `destroy` (drafts only) |
| GET | `/admin/meetings/{id}/attendees.csv` | M | `exportAttendees` (`CsvExport::response`, `MeetingService::CSV_HEADER`, `csvRows`) |
| POST | `/admin/meetings/{id}/attendees` | M (+A over 50) | `addAttendees`: `emails` (raw text), `items` (JSON list), `bulk[]` (`members_all`, `members_type:ID`), `invite_now` (1 = queue `invite_new` afterwards). JSON answer `{ok, added, reactivated, existing, invalid[], total, queued}` |
| POST | `/admin/meetings/{id}/attendees/{aid}/remove` | M | `removeAttendee` (`send_cancellation`) |
| GET | `/admin/meeting-requests` | M | `MeetingRequestsController::index` (`?status= &q= &page=`) - baseline works |
| GET | `/admin/meeting-requests/export.csv` | M | `export` |
| GET | `/admin/meeting-requests/{id}` | M | `show` (detail; "Schedule this meeting" = link to `/admin/meetings/create?request=ID`) |
| POST | `/admin/meeting-requests/{id}/status` | M | `status` (`status`, `notes`, `notify` + `note` for decline) |
| POST | `/admin/meeting-requests/{id}/delete` | A (`meetings.delete`) | `destroy` |

### Admin JSON helpers (`MeetingPickersController`, ARCHITECT, complete)
| Method | Path | Response |
|---|---|---|
| POST | `/admin/api/meetings/parse` | body `{text, exclude?[]}` -> `{valid:[{name,email}], invalid:[{input,reason}], duplicates, skipped, truncated, count}` |
| POST | `/admin/api/meetings/jitsi` | `{url, service, notice}` |
| GET | `/admin/api/meetings/pickers/members?q=` | `{items:[{id,name,email,number,type}]}` (approved members, 2+ characters) |
| GET | `/admin/api/meetings/pickers/member-types` | `{items:[{id,name,count}], total, can_bulk}` |
| GET | `/admin/api/meetings/pickers/members-bulk?type=ID` | `{spec, count, total, capped, max, allowed, needs_admin}` (the confirmation dialog shows `count`; over 50 needs an administrator) |
| GET | `/admin/api/meetings/pickers/staff` | `{items:[{id,name,email,role}]}` |
| GET | `/admin/api/meetings/pickers/previous?q=` | `{items:[{name,email}]}` |

Errors: `{"error":"<code>","message":"..."}` (codes `bad_request` 400, `validation` 422 with `errors`, `forbidden` 403, `not_found` 404, `conflict` 409). Every response is `private, no-store`.

### Admin JSON sending (`MeetingMailController`, MAIL agent)
| Method | Path | Request -> Response |
|---|---|---|
| GET | `/admin/api/meetings/{id}/mail` | `{meeting:{id,status,sequence,send_paused,halt_reason}, counts:{total,not_sent,queued,sent,failed,accepted,declined,tentative,pending,removed}, transport:{driver,level,message,blocking}, sender:{from,from_name,reply_to}, available:{invite_new:N,invite_pending:N,update_all:N,cancel_all:N,retry_failed:N}, reminders:{h24:N,h1:N}}` (the `available` counts are what each scope WOULD queue now: they feed the confirm dialog) |
| POST | `.../mail/send` | `{scope, confirm_count*, attendee_id?}` -> status + `queued`. `confirm_count` must equal the number the scope would queue now (409 `count_changed` with `expected`); a **draft** with scope `invite_new` is scheduled first (422 `not_ready` + `errors` from `readiness`); 409 wrong status |
| POST | `.../mail/batch` | -> `{processed, sent, failed, remaining, paused, busy?, halted?:{reason}}` |
| POST | `.../mail/pause`, `/resume`, `/retry` | -> status |
| POST | `.../mail/test` | `{kind?:invite}` -> `{results:[{ok,error}]}`; sent to the **signed-in user's own address only** |
| POST | `.../mail/message` | `{subject?, message*, attendee_ids*[<=30]}` -> `{results:[{id,ok,error}]}` (ids must belong to this meeting and not be removed) |
| POST | `.../mail/reminders` | `{which:'24h'|'1h'}` -> `{sent, failed, skipped}` ("Send reminder now": everyone eligible who has not had it) |

## 11. Forms (names the ADMIN agent posts)

Schedule form (`store` / `update`), all strings: `title*`, `date*` (Y-m-d), `start_time*` (HH:MM), `duration` (15/30/45/60/90 or any number of minutes) and/or `end_time` (HH:MM, wins), `platform`, `join_url`, `passcode`, `dial_in`, `location`, `description` (HTML from the rich-text editor), `organiser_name*`, `organiser_email*`, `visibility` (`private | public`), `show_join_link_public`, `reminder_24h`, `reminder_1h`, `allow_rsvp`, plus the invitees as `emails` (raw text), `items` (JSON list from the chips: `{source, member_id, user_id, name, email}`) and `bulk[]`. Times are East Africa Time and the form says so next to the time fields. `intent = send` means "Save and send invitations": after the save the controller calls `schedule()`, requires `confirm_count` (the number of people who will be invited) to match, queues `invite_new` and redirects to the meeting page with `?send=1`, where `admin-meetings-send.js` starts the batch loop.
Help text per platform (link field placeholder + one line): Zoom "Paste the Zoom meeting link (Meetings > Schedule in Zoom, then Copy invitation link)"; Google Meet "In Google Meet choose New meeting > Create a meeting for later and paste the link"; Teams "In Teams open Calendar > New meeting, then Copy link"; Jitsi "Press Generate a free private video room"; WhatsApp "Paste the group call link or a https://wa.me/ link, or give the number in Dial-in"; Phone "Enter the number people must call in Dial-in"; In person "Enter the address"; Other "Paste any https link, or a phone number or address".

## 12. Checklists

### 12.1 ADMIN agent
Controllers `Admin/MeetingsController`, `Admin/MeetingRequestsController`; views `views/admin/meetings/*`; `scss/admin/_meetings.scss`; `assets/js/admin-meetings.js`.
* **One-page schedule form** for non-technical users: all fields of section 11, East Africa Time shown clearly, duration presets + custom end time, platform select with changing help, **Generate a free private video room** button (say it is an external free service: show `notice`), optional passcode / dial-in / address, rich-text description (`partials/admin/richtext-field`), organiser name + e-mail (default = signed-in user), visibility (Private default / Public "Meeting" with the Zoom-bombing warning next to "also show the join link publicly", default off), options (reminders, RSVP).
* **Invitees "smart box"**: type or paste many addresses (calls `/parse`, shows per-address errors), MEMBER search (calls `pickers/members`, chips with name + number), "Add all approved members" and "Add members of type X" with a confirmation showing the count (`members-bulk`; over 50 only for administrators: show the `needs_admin` message), STAFF picker, earlier-meeting attendees; every person is a removable chip; the privacy note "Members' e-mail addresses are used only to send this invitation".
* Buttons: Save draft, **Save and send invitations** (confirm dialog with the exact recipient count, date and time, link and organiser), **Send me a test invitation** (calls `mail/test`, needs a saved meeting).
* **Meeting page** (`show`): status badge, details via `partials/meeting-details`, edit / reschedule (when `update_suggested`, offer "Send updated invitation to everyone": `mail/send` scope `update_all`), cancel (reason, "Send cancellation to everyone"), reopen, duplicate, delete (drafts only), **attendee table** (name, email, source, invite status sent / failed / queued + error, RSVP status + time, reminders sent), add people later ("invite only the new ones"), remove (optionally send the cancellation), resend to one / all pending, copy the join link, "Send an update" (free-text message), export CSV, summary counts, the sending panel hooks for the MAIL agent (`data-meeting-send` root with `data-api` and `data-id`).
* **Request inbox**: list + status tabs + search, detail, "Schedule this meeting" (prefilled form), decline with an optional message, CSV export, delete (administrators), sidebar badge already works.
* Calendar integration (section 6), dashboard cards are already in `DashboardController`; add "Schedule a meeting" buttons to the Calendar manager and the Contact-side admin screen.
* Roles: admin + editor; `meetings.bulk` / `meetings.delete` for administrators only. Never write the tables or the calendar entry yourself.

### 12.2 WEB agent
Controllers `Web/MeetingController`, `Web/MeetingRequestController`; views `views/web/meetings/*`; `scss/pages/_meetings.scss`; `assets/js/meetings.js`; the Contact block `views/web/contact/index.php` (a partial `partials/meeting-request-form.php` rendered only when `MeetingRequestService::enabled()` AND `section_on('contact','meeting_request')`, anchor `id="meeting-request"`).
* **Invitee page**: all copy from `MeetingRenderer::pageData` + registry `site_meetings` (`MeetingRenderer::t('page.*')`, `('rsvp.*')`); title, organiser, date and time in EAT (+ "In your time zone" filled by `meetings.js` from the `<time data-meeting-start>` the shared partial prints), description, big **Join meeting** button (visible to that invitee even if the meeting is private), Add to calendar, Accept / Maybe / Decline as a POST form with CSRF and a status message (`aria-live`), preselect from `?r=`; cancelled and ended states (no join link); a bad token shows `page.not_found_*` with status **404**; `noindex`, no-store; never show other attendees; call `MeetingService::completeDue()` first.
* **Request form** fields: name, email, phone (optional), organisation (optional), topic (`MeetingRequestService::topics()`), message, preferred date (min tomorrow), time window (`windows()`), duration (15/30/45/60/90), meeting type (`platformPrefs()`), consent tick box; honeypot; labels and errors from `content('contact','meeting_request.*')`; the privacy note; success state with `success_title` / `success_text`. Works without JavaScript.
* Public calendar: public meetings already arrive through the calendar (type "Meeting / outreach", title, date, time, link only if ticked). Change calendar partials only additively and only if the contract allows (it does not require any change).
* Sitemap: nothing to add (`/meeting/*` is never listed; robots disallows it).

### 12.3 MAIL agent
Controller `Admin/MeetingMailController`; new `app/services/Meetings/MeetingMailer.php`; `bin/meetings-send.php`; `assets/js/admin-meetings-send.js`; tests against the mock SMTP server (`docs/NEWSLETTER.md` section 9).
* **Batch algorithm** (same pattern as `NewsletterMailer`): take the DB named lock `GET_LOCK('lumina_meeting_sender', 0)` (answer `busy` when held; release with `RELEASE_LOCK`); re-read the meeting; stop when `send_paused`; take up to `newsletter_batch_size` rows with `MeetingService::queuedRows($id, $n)`; for each: `MeetingService::claim($row['id'], $row['attempts'])` (skip when false); **re-read the row state that matters** (removed with kind other than `cancel` -> `markFailed` permanent "Removed"; meeting cancelled + kind invite / update -> drop it); build `MeetingRenderer::message($meeting, $row, $kind)` where kind = `queue_kind` (cancel on a removed attendee = `removed`); `Mailer::attempt(...)`; success -> `markSent($id, (int) $meeting['sequence'])`; failure -> `markFailed($id, $error, !$transient)`; a configuration-level failure (`$result['config']`) -> `release($id)`, `MeetingService::pause($meetingId, $reason)` and stop the batch (nobody is marked failed). `Mailer::closeConnection()` at the end; stop after about 20 seconds.
* The **sequence sent** is the meeting's current SEQUENCE; because `queue('update_all')` only picks rows with `invite_sequence < sequence`, a change made while a batch is running is never sent twice.
* **Reminders**: `MeetingService::dueReminders()` -> for each: `claimReminder` (skip if false) -> send `reminder_24h` / `reminder_1h` -> on failure `reminderFailed($id, $which, $transient)`. Use the same lock. `mail/reminders` ("Send reminder now") sends the reminder to every eligible invited person who has not had it, regardless of the clock.
* **Cron** `bin/meetings-send.php` (hPanel, every 5 minutes: `0-59/5 * * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/meetings-send.php`): CLI only, no output unless `--verbose` (counts only), time budget `--max-seconds`; calls `MeetingService::completeDue()`, finishes queues of `meetingsWithQueue()` and sends due reminders; needs `APP_URL` and `MAIL_DRIVER=smtp` in `.env`. Add its line to the README next to the newsletter cron.
* **JS**: confirm dialog with the exact count from `GET .../mail` (`available`), progress bar, pause / resume / retry, per-recipient results, test, message in chunks of 30, "Send reminder now"; addresses only via `textContent`.
* Logs and audit carry ids and counts only. Never log or return the SMTP password or an address in an error (the Mailer already scrubs its messages; `markFailed` masks anything address-shaped).

## 13. Privacy, security and compliance

* Attendee and request e-mails are personal data: admin + editor only; never in a public endpoint, a log, an audit entry, a URL parameter or the sitemap. Join links are secrets (section 3). Invitee tokens are 192-bit `random_bytes`, shape-checked before any database lookup, rate limited, and die with the attendee. Failed lookups answer 404 for every reason.
* **Header-injection safe**: names, titles, organiser e-mail, subjects and attachment names go through the Mailer's CR / LF / NUL stripping and RFC 2047; ICS fields are escaped and folded; tested with CRLF payloads.
* Limits: 200 attendees per meeting, 500 members per bulk add, more than 50 recipients at once for administrators only, 1000 pasted tokens, 200 KB of pasted text, request message 3000 characters, 5 requests per hour per visitor (keyed hash of the IP, IPv6 as its /64) plus the route throttle (also per /64), honeypot. **The acknowledgement e-mail goes to an unverified address from the foundation's own sender**, so it is a fixed text (nothing the visitor typed: no name, no message, no `To:` display name, whatever the owner's wording says), at most **2 per address per 24 hours** and 120 per hour for the whole site (`mreq-ack:*`, `mreq-ack-all`); the visitor still sees the normal success screen when a cap is hit, and staff still get every notification. The decline e-mail (staff-initiated) shows the visitor's name reduced to plain letters, never the message.
* Sanitised HTML (`Sanitizer::html`), escaped output (`e()`), no inline JavaScript (CSP), POST + CSRF for every state change (an answer is never a GET side effect), role checks on every method, IDOR guards (`removeAttendee` checks the attendee belongs to the meeting; CSRF is checked by the router).
* **Erasure**: erasing a member's personal data (`MembershipApplication::anonymise`) calls `MeetingService::erase()`: attendee rows keep their place but lose name, address and token and are marked removed; request rows lose name, address, phone, organisation, message and notes.
* **Uganda Data Protection and Privacy Act, 2019**: using members' e-mail addresses to invite them to a meeting is processing of personal data for a purpose that must fit what members consented to and what the privacy notice says. The foundation may need to register with the Personal Data Protection Office and keep records of consent. **The owner must confirm the lawful basis and the wording of the privacy notice (public privacy page, membership terms, the request-form consent line) with counsel**; the site makes no legal claim and adds no legal text to the public pages. The schedule form shows the note that members' addresses are used only to send the invitation.
* The visibility "Public" warns about **Zoom-bombing**: a public listing with the join link lets strangers join; the default is private and the link is hidden unless explicitly ticked.

* **Analytics never sees a personal link.** The helper `analytics_id()` (used by `layouts/main.php` for `data-ga-id` and by `Response` for the CSP `script-src` / `connect-src`) returns '' for every path under `/meeting/`, whatever the page is (the invitee page, the 404, and the 419 / 429 / 500 pages of such an address), so the address that holds the token is never handed to Google. `meetings.js` also removes the attribute (second layer).
* **E-mail layout on phones**: the Accept / Maybe / Decline buttons are `inline-block` links (`white-space:nowrap`, at least 44 px high) in a cell that does not break words, so on a narrow phone they wrap onto a second line instead of being split in the middle of a word.
* **Admin screen behaviour**: the schedule form's invitee box has one status line (`[data-inv-msg]`, `role=status`) that is visible whichever tab is open; a confirm dialog keeps its title and buttons in view (only the middle scrolls); after a sending the page is **never reloaded by itself** (the note offers "Update the list"); a long people list shows its first 25 rows with a "Show all" button (a filter or pill shows every match) and announces how many people match; the help line and steps of the chosen meeting type are shown even for Phone and In person (they live outside the hidden link field); a sticky action bar no longer hides the focused control (`scroll-padding-bottom`).

## 14. Commands

| Task | Command |
|---|---|
| Apply schema + seed (idempotent) | `/c/xampp/php/php.exe bin/setup.php` |
| Rebuild the one-file live import | `/c/xampp/php/php.exe bin/build-import-sql.php` |
| Meeting tests alone / everything | `/c/xampp/php/php.exe bin/selftest-meetings.php` / `bin/selftest.php` (`ALL CHECKS PASSED`) |
| Mock SMTP server | `/c/xampp/php/php.exe bin/dev/mock-smtp.php` (see `docs/NEWSLETTER.md`); set `MAIL_DRIVER=smtp MAIL_HOST=127.0.0.1 MAIL_PORT=2525 MAIL_ENCRYPTION=none` in the local `.env` and **restore `MAIL_DRIVER=file`** afterwards |
| SCSS | `PYTHONUTF8=1 "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" bin/build-scss.py` |
| Deploy order | import `database/lumina-live-import.sql` first (or with the code upload); until then `MeetingService::available()` is false (the Contact form hides itself) and the admin Meetings pages need the tables |

## 15. Ownership

| Owner | Owns exactly |
|---|---|
| **ADMIN agent** | `app/controllers/Admin/MeetingsController.php`, `Admin/MeetingRequestsController.php`, `app/views/admin/meetings/**`, `scss/admin/_meetings.scss`, `assets/js/admin-meetings.js`, the "Schedule a meeting" buttons and the meeting cues in the calendar admin views (additive) |
| **MAIL agent** | `app/controllers/Admin/MeetingMailController.php`, new `app/services/Meetings/MeetingMailer.php`, `bin/meetings-send.php`, `assets/js/admin-meetings-send.js`, mail-side tests against the mock SMTP server, the README cron line |
| **WEB agent** | `app/controllers/Web/MeetingController.php`, `Web/MeetingRequestController.php`, `app/views/web/meetings/**`, the Contact block + a request-form partial, `scss/pages/_meetings.scss`, `assets/js/meetings.js` |
| **ARCHITECT (frozen)** | schema, seed, models, `app/services/Meetings/{MeetingTime,JitsiLink,InviteeParser,MeetingIcs,MeetingService,MeetingRenderer,MeetingRequestService}.php`, `app/views/meetings/email/layout.php`, `app/views/partials/meeting-details.php`, `app/core/{Mailer,AdminNav}.php`, `Admin/MeetingPickersController.php`, registry files `pages/site_meetings.php` + the `meeting_request` section of `pages/contact.php`, `settings_schema.php`, `config/app.php`, routes, `admin_nav/meetings.php`, `scss/main.scss` + `scss/admin.scss`, `bin/selftest*.php`, this file |

If two agents need the same new helper each writes its own inside its own files; the architect merges afterwards.

## 16. Owner to-do before the first meeting

1. Make sure a mailbox is configured (`MAIL_DRIVER=smtp`, `.env`, see `docs/NEWSLETTER.md`) and send yourself a test invitation first.
2. Check the wording: Admin > Pages > Meetings (e-mails and invitee page) and Pages > Contact > Request a meeting; topics in Settings > Meetings.
3. Add the cron line for `bin/meetings-send.php` (reminders and finishing interrupted sends).
4. Confirm the legal position for using members' e-mail addresses (section 13) and update the privacy notice if needed.


## Podcast / live broadcast meetings

Platforms `podcast` (any live or podcast link), `youtube_live`, `facebook_live`, `tiktok_live`, `instagram_live` (`Meeting::BROADCAST`). Choose one in the schedule form and paste the link (a Zoom or Google Meet link may be used with `podcast` too). A broadcast meeting is **always public with its link shown** (the form's Zoom-bombing choice does not apply: the link is for viewers). Its calendar entry is filed under the calendar type **Podcast / live broadcast** (slug `podcast`, created on first use, editable under Calendar types) and the entry page has a **Watch live** button. When the meeting is completed (or cancelled) the button disappears, as for any meeting. The database change (new `platform` values) is in `database/schema.sql`, additive and idempotent. Test: `bin/selftest-podcast.php`.
