# Calendar & programme scheduler - CONTRACT

> Read `docs/ARCHITECTURE.md` and `docs/DESIGN.md` first; `docs/NEWSLETTER.md` and `docs/MEMBERSHIP.md` are worked examples of the same conventions.
> Two agents build on this in parallel: the **WEB agent** (public pages) and the **ADMIN agent** (admin screens, CSV import). The **architect** files
> (schema, seed, models, `app/services/Calendar/*`, registry, settings, abilities, routes, nav fragment, selftest, this file) are **frozen**: call them, do not copy or edit them.
> Missing something? Add your own file and list the gap in your report.

Owner request: *a calendar and programme scheduler under the Contact page, featuring all Uganda public holidays and the important international humanitarian days,
and easy for a non-technical admin to add schedules.*

Everything is **additive and idempotent**: three new tables, no change to existing ones. Re-importing `database/lumina-live-import.sql`
(`php bin/build-import-sql.php`) upgrades a live database without data loss and without overwriting owner edits (tested: previous live import, then the new file twice).

---------------------------------------------------------------------------------------------------------------------

## 1. What exists (architect, done and tested)

| Area | Files |
|---|---|
| Schema / seed | `database/schema.sql` (end): `calendar_types`, `calendar_entries`, `calendar_overrides`. `database/seed.sql` (end): 3 settings, pages row `calendar` (child of `contact`), 7 types, **14 Uganda public holidays + 46 international days** as rules |
| Models | `app/models/CalendarType.php`, `CalendarEntry.php`, `CalendarOverride.php` |
| Date engine | `app/services/Calendar/`: `CivilDate`, `Easter`, `IslamicCalendar`, `RecurrenceRules`, `OccurrenceExpander`, `ColorContrast`, `IcsBuilder` (pure PHP, no DB) |
| Services | `CalendarService` (read side: queries, month grid, agenda, upcoming, JSON feed, iCal, dates to confirm, preview, embed data), `CalendarWriter` (write side: validate, save, quick add, duplicate, delete, bulk, overrides, types, CSV mapping) |
| Registry | `app/config/pages/calendar.php` (all copy), `app/config/pages/contact.php` (new section `calendar`) |
| Settings / abilities | `settings_schema.php` group **Calendar**; `config/app.php`: `calendar.manage` (admin, editor), `calendar.advanced` (admin), `calendar.delete` (admin); reserved page slug `calendar` |
| Routes | `app/routes/web_calendar.php`, `app/routes/admin_calendar.php`; sidebar `app/config/admin_nav/calendar.php` (group "Calendar", order 36, badge = dates to confirm) |
| Controllers | `Web/CalendarController` (page, detail, feed, iCal), `Admin/CalendarController` (manager, editor, holidays, occurrences, settings), `Admin/CalendarTypesController`, `Admin/CalendarTransferController` (CSV export, template, import with dry run), `Admin/CalendarApiController` (month + preview). All complete and tested end to end |
| Views / partials | `views/web/calendar/*`, `views/partials/calendar-*.php`, `views/admin/calendar/*` (index, form, holidays, types, import, settings and their partials); the Contact view renders the embed |
| Styles / JS | `scss/pages/_calendar.scss` (public, print styles), `scss/admin/_calendar.scss`, `assets/js/calendar.js` (in-place month change, dialog, filters, keyboard, swipe, print), `assets/js/admin-calendar.js` (quick add, live preview, contrast check) |
| Core hook | `DashboardController` adds the tile "Dates to confirm" through `CalendarService::dashboardTile()` |
| Tests | `bin/selftest-calendar.php` (about 190 checks, run by `bin/selftest.php`, removes its own data) |

## 2. Data model

### `calendar_types`
`id, slug (unique), name, color (#rrggbb), icon (name from App\Core\Icons), sort_order, is_active, show_on_public, is_system, created_at, updated_at`.
Seeded: `programme` "Programme session", `event` "Event" (system), `uganda-holiday` "Uganda public holiday" (system), `international-day` "International / UN day", `meeting` "Meeting / outreach", `deadline` "Deadline", `other` "Other".
`show_on_public = 0` makes a team-only type (its entries never reach visitors, feeds or filters). The `event` type has **no stored entries**: its occurrences are read from the existing `events` table.
Every colour yields an accessible set through `ColorContrast::pair()` (section 5.6). Colour is never the only cue: each type also has an icon and a text label.

### `calendar_entries`
`id, type_id (FK RESTRICT), title, slug (unique), description (small sanitised HTML), location, all_day, start_date, end_date (NULL = one day), start_time, end_time (TIME, Africa/Kampala), recurrence (ENUM none|daily|weekly|monthly|yearly|nth_weekday|easter|islamic), recurrence_data (JSON text, CHECK JSON_VALID), repeat_until, program_id / project_id / event_id (FK SET NULL), link_url, link_label, color (per-entry override, NULL = type colour), source_label, source_url, is_system, is_public, is_active, needs_confirmation, created_by, updated_by, created_at, updated_at`.
Indexes: `(is_active, is_public, start_date)`, `type_id`, program / project / event, UNIQUE `slug`.
`is_system = 1` = seeded holidays and international days: editable and hideable (`is_active = 0`), **never hard-deleted** (`CalendarWriter::delete` refuses; bulk delete skips them).

### `calendar_overrides`
`id, entry_id (FK CASCADE), occurrence_date, new_date, new_start_time, new_end_time, cancelled, confirmed, note, updated_by, created_at, updated_at`, UNIQUE `(entry_id, occurrence_date)`, index on `new_date`.
Changes ONE occurrence (move, cancel, confirm) without touching the series. `occurrence_date` is the day the RULE produces, not the moved day, so an override keeps its identity when it is moved.
**Overrides never touch the entry row** (its `updated_at` stays equal to `created_at`), so confirming an Eid date does not make the entry "edited" for the seed refresh.

### Seed behaviour (idempotent, non-destructive)
Like the rest of `seed.sql`: a row is refreshed from the file **only while pristine** (`updated_at = created_at`); an entry or type an admin edited, hid or renamed is never overwritten; overrides are never touched. `SET @reseed = 1` forces a refresh (development only). The seed adds no Lumina programme session. The page row `calendar` uses a looked-up `parent_slug` (NULL when the `contact` row is missing).

## 3. Rules: `recurrence` + `recurrence_data`

Weekdays are ISO 1..7 (Monday..Sunday); `nth` is 1..5 or -1 (last); the series never starts before `start_date`; `repeat_until` (column) is the last day an occurrence may START.
A multi-day entry (`end_date`) keeps its length in every occurrence. `until` may also appear inside the JSON; `RecurrenceRules::normalise()` moves it to `repeat_until` (the earlier of both wins).
Unknown keys, wrong types, out-of-range values, a JSON list, malformed JSON or more than 1000 bytes are **rejected** with a message (fuzz-tested).
**No overlapping copies**: for a repeating entry with an end date, `normalise()` refuses a length (end date minus start date) that reaches the smallest gap between two starts (`RecurrenceRules::minGapDays`: daily = the interval, weekly = the closest two chosen weekdays, monthly 28 x interval, yearly 365 x interval, nth weekday 357, Easter 300, Islamic 340). A daily entry lasting a year, or a weekly summer camp of two months, is rejected on the `end_date` field instead of producing hundreds of overlapping copies.

| `recurrence` | `recurrence_data` | Meaning |
|---|---|---|
| `none` | `{}` (nothing else allowed) | only `start_date` (..`end_date`) |
| `daily` | `{"interval":3}` (1..99, default 1) | every N days from `start_date` |
| `weekly` | `{"interval":2,"weekdays":[1,3]}` (default: the weekday of `start_date`) | N-weekly on the listed weekdays (weeks counted from the Monday of the start week) |
| `monthly` | `{"interval":1,"mode":"day"}` or `{"mode":"nth_weekday","nth":2,"weekday":2}` | same day of the month (**clamped to the last day** of short months: the 31st becomes 28/29/30) or the nth weekday of the month |
| `yearly` | `{"interval":1}` | same month and day (**29 February falls on 28 February** in common years) |
| `nth_weekday` | `{"month":2,"weekday":2,"nth":2,"interval":1}`; optional `"from_day":5` (only with `nth` 1, value 2..22) | e.g. 2nd Tuesday of February, 4th Thursday of April (a missing 5th weekday skips that year); with `from_day` the first weekday on or after that day of the month (`from_day` 5 = the Tuesday falling on day 5 to 11, how Safer Internet Day is set); `start_date` is a lower bound only |
| `easter` | `{"easter_offset":-2}` (-366..366) | days from Western Easter Sunday (Meeus/Jones/Butcher): Good Friday -2, Easter Monday +1; lower bound only |
| `islamic` | `{"islamic":"eid_al_fitr"}` | `eid_al_fitr` (1 Shawwal), `eid_al_adha` (10 Dhu al-Hijjah), `islamic_new_year`, `mawlid`; **tabular (arithmetical) calendar = a prediction** (section 9) |

Validation/normalisation: `RecurrenceRules::normalise(['recurrence'=>..,'recurrence_data'=>array|json,'start_date'=>..,'end_date'=>..,'repeat_until'=>..])` returns `{ok, errors:{field:msg}, recurrence, data, json, repeat_until, start_date, end_date}`; it fills defaults, so `{}` for `weekly` becomes `{"interval":1,"weekdays":[<start weekday>]}`.

## 4. Engine and service API (`App\Services\Calendar\`, all static)

### Date helpers (pure)
| Class | Signatures |
|---|---|
| `CivilDate` | `valid(mixed): bool`, `parse(mixed): ?array`, `validMonth(mixed): bool`, `days(string): int`, `ymd(int): string`, `addDays(string,int)`, `diffDays(a,b)`, `weekday(string): int`, `nthWeekday(int $y,int $m,int $weekday,int $nth): ?string`, `monthStart(string)`, `monthEnd(string)`, `addMonths('YYYY-MM', int)`, `weekStartOf(string,int $weekStart=1)`, `fmt(string,'l j F Y')`, `time(mixed): ?string`, `today()` (Africa/Kampala), `setToday(?string)` (tests), `isLeap`, `daysInMonth`, constants `MONTHS`, `WEEKDAYS`, `TZ`. Years 1900..2200. No DateTime, no intl, no calendar extension |
| `Easter` | `sunday(int $year): string`, `offset(int $year, int $days): string` |
| `IslamicCalendar` | `toGregorian(hy,hm,hd)`, `fromGregorian(string): [hy,hm,hd]`, `datesInRange(string $key, string $from, string $to): list<string>`, `knownKey()`, `HOLIDAYS` |
| `ColorContrast` | `normalise`, `ratio(a,b)`, `luminance`, `passesAA`, `textOn(bg)`, `tint(hex,0.88)`, `darkenToContrast(hex,bg,4.5)`, `pair(?string): {solid,on_solid,tint,text,ratio_solid,ratio_text,aa_solid,aa_text}` |
| `RecurrenceRules` | `normalise()`, `starts(array $entry, string $from, string $to): list<string>` (rule start days, before overrides), `describe(array $entry): string` ("Every year on 8 March"), `duration()`, `effectiveUntil()`, `decode()`, `encode()` |
| `OccurrenceExpander` | `expandEntries(list $entries, array $overridesByEntry, string $from, string $to, array $opts): list<occurrence>`, `build()`, `fromEvent(array $eventRow): ?array`, `touches()`, `sort()` |
| `IcsBuilder` | `calendar(list $occurrences, array $opts): string`, `eventLines()`, `vtimezone()`, `uid()`, `escapeText()`, `fold()`, `plainText(string $html)` |

### The OCCURRENCE (one shape for every consumer)
`key` (`e{entry_id}:{occurrence_date}` | `v{event_id}:{start_date}`), `source` (`entry|event`), `entry_id`, `event_id`, `type_id`, `title`, `slug`, `description` (sanitised HTML; events: plain summary), `location`, `all_day`, `start_date`, `end_date` (INCLUSIVE), `start_time`, `end_time` (`HH:MM|null`), `occurrence_date`, `recurring`, `moved`, `cancelled`, `confirmed`, `needs_confirmation`, `note`, `override_id`, `color`, `program_id`, `project_id`, `link_url`, `link_label`, `source_label`, `source_url`, `is_public`, `is_active`, `is_system`.
`CalendarService::decorate()` adds `type_slug`, `type_name`, `type_icon`, `type_sort`, `pair` (colour set), `is_holiday`, `is_event`, `url` (`/calendar/{slug}?date=` or `/events/{slug}`), `ics_url`.

### `CalendarService` (read side)
| Signature | Purpose |
|---|---|
| `available(): bool`, `flush()` | tables exist (a live DB imported late must not 500 `/contact`), clear per-request caches |
| `weekStart(): int`, `defaultTypeSlugs(): array`, `showOnContact(): bool` | settings |
| `types()`, `publicTypes()`, `typeById(int)`, `typeBySlug(string)` | types with `pair` (cached per request) |
| `occurrences(string $from, string $to, array $filters = []): list` | range query. Filters: `types[]` (slugs), `public` (default true), `include_cancelled`, `include_inactive`, `q`, `entry_id`, `only_unconfirmed`, `source`. **Constant number of SQL statements** (types cached, then entries, overrides, events); range capped at 1900 days |
| `monthGrid(string $month, array $filters = [], array $opts = []): array` | `{month,year,month_number,label,prev,next,is_current,is_far_future,week_start,weekdays[{iso,short,long}],from,to,today,weeks[[day]],total,custom_total,items}`; day = `{date,day,in_month,is_today,is_weekend,weekday,label,items,count,has_holiday}`; Monday first by default |
| `agenda(from,to,filters)`, `groupByDay(items,from,to)`, `upcoming(int $n, filters, ?from)` | grouped list, next N (running multi-day ones included) |
| `feed(from,to,filters)`, `publicItem(occ)` | JSON feed body (section 6), visitor-safe item |
| `publicEntryBySlug`, `publicEntryById`, `occurrencesOfEntry`, `nextOccurrences` | detail pages |
| `previewOccurrences(array $input, int $n = 5, ?int $entryId = null)` | next dates of an UNSAVED schedule + a sentence; same engine as the public calendar |
| `datesToConfirm(int $days = 60)`, `pendingConfirmationCount()`, `dashboardTile()`, `systemEntries(string $typeSlug)` | holidays tab: entry + next occurrence + `unconfirmed` + `days_until` |
| `embedData(?string $month)` | data of the Contact block or null |
| `icsFeed(array $filters)`, `icsEntry(array $entry, ?string $date)`, `icsHost()`, `icsCalendarName()` | iCal |
| `dateLabel($occ)`, `timeLabel($occ)` | text labels |

### `CalendarWriter` (write side; every change goes through it)
`validateEntry(array $in, ?array $existing, array $opts): {ok,errors,row}`; `saveEntry(?int $id, array $in, ?int $userId, array $opts): {ok,errors,id,entry}` (`opts.allow_advanced` = the user holds `calendar.advanced`); `quickAdd(array $in, ?int $userId)` (title, date, [date_end, all_day, start_time, end_time], type, location, repeat none|daily|weekly|monthly|yearly, until, public); `duplicate(int,?int)`; `delete(int)` (system entries refused); `bulk('activate'|'deactivate'|'delete', ids, userId)`; `saveOverride(int $entryId, string $occurrenceDate, array $in, ?int $userId)` with `action` = `move` (new_date and/or new_start_time, new_end_time), `cancel`, `confirm` (new_date optional; sets `confirmed`), `restore`, `reset`; the occurrence date must be produced by the rule; `deleteOverride(entryId, overrideId)` (IDOR-guarded); `saveType`, `deleteType` (system and used types refused); `CSV_COLUMNS`, `csvRecord()`, `inputFromCsv()`.
Validation covers: title, type (events cannot be created as entries), dates and times, end after start, rule JSON, link / source URLs (**http and https only**), colour, linked programme / project / event, sanitised description (`Sanitizer::html`), reserved slugs (`feed`, `event`, ...), unique slug. Audit entries (`calendar.create|update|duplicate|delete|bulk_*|override_*|type_*|export`) carry ids and counts only.

## 5. Behaviour rules the engine guarantees
1. **Time zone**: all times are Africa/Kampala (EAT, UTC+3, no daylight saving), so dates are plain civil dates; iCal timed events carry `TZID=Africa/Kampala` + `VTIMEZONE`.
2. **Override precedence**: an override replaces the rule's occurrence (date, times, cancelled, confirmed, note). An override moved from outside the queried window into it is found; one moved out is dropped; one whose date the rule no longer produces is ignored.
3. **Needs confirmation**: `entry.needs_confirmation = 1` and no `confirmed = 1` override for that occurrence gives `needs_confirmation = true` ("Date to be confirmed").
4. **Visibility** (public queries): `entry.is_active = 1 AND entry.is_public = 1` and the type `is_active = 1 AND show_on_public = 1`; events: `is_active = 1`. The admin passes `public => false`.
5. **Events** are merged, never copied: midnight start without end = all-day; `/events/{slug}` is the link; they use the `event` type's colour and icon.
6. **Colour**: stored colour = `solid`; `on_solid` white / near-black / black (first reaching 4.5:1); `tint` = 88% wash; `text` = the colour darkened until it reaches 4.5:1 on the tint. Pills and chips use `text` on `tint` with a `solid` border; every default type passes (selftest).

## 6. Routes, JSON and iCal shapes

### Public (all GET, read-only)
| Path | Handler | Notes |
|---|---|---|
| `/calendar` | `index` | `?month=YYYY-MM` (or `?m=&y=` from the jump form), `?view=month|list|upcoming`, `?types=a,b` (or `types[]`), defaults from setting `calendar_default_types`. Canonical `/calendar` or `/calendar?month=`, month title/description from registry `seo.*`, **noindex** for far-future (> 24 months) months that hold only the recurring holidays and days. Page unpublished = 404 (the page, the detail pages, `/calendar/feed`, `/calendar.ics` and `/calendar/{id}.ics` all carry `page.published:calendar`; the Contact block and the sitemap entry go too) |
| `/calendar/feed` | `feed` | `?start=&end=` or `?month=` (1901-01 to 2199-12; the whole visible grid), `?types=`. Range capped at **400 days** and **1500 items** (`capped:true` when either cut it). `400 {error,message}` for bad input, an array value (`?start[]=`) included. `Cache-Control: public, max-age=300`, `ETag` + 304, throttle 120/min (section 11 says what the throttle stores) |
| `/calendar.ics` | `icsAll` | public entries from 1 Jan last year to 31 Dec next year + 1 (about four years, max 3000 events: when more are due, **every holiday and international day is kept** and the earliest other occurrences fill the rest; a log warning is written), `?type=slug[,slug]`; `text/calendar`, `Content-Disposition: attachment` |
| `/calendar/{id}.ics` | `icsEntry` | `?date=YYYY-MM-DD` for one occurrence, else the next one; 404 if not a public entry / not an occurrence. Events keep `/events/{slug}/calendar.ics` |
| `/calendar/{slug}` | `show` | detail of a public entry; `?date=` picks the occurrence (else the next); JSON-LD `Event` for **programme sessions only** |

Feed body: `{"range":{"start","end"},"capped":false,"timezone":"Africa/Kampala","week_start":1,"types":[{slug,name,icon,color,on_color,tint,text_color}],"count":N,"items":[{key,kind,entry_id,event_id,title,type,type_name,icon,color,on_color,tint,text_color,start_date,end_date,all_day,start_time,end_time,location,summary,description_html,is_holiday,needs_confirmation,confirmed,moved,note,source_label,source_url,link_url,link_label,url,ics_url}]}`. Public items only, no staff ids, no personal data.
iCal: `VERSION:2.0`, `PRODID:-//Lumina Africa Foundation//Calendar//EN`, `METHOD:PUBLISH`, CRLF, lines folded at 75 octets (never inside a UTF-8 character), TEXT escaped, `UID:lumina-cal-e{entry}-{YYYY-MM-DD of the ORIGINAL occurrence}@{host}` (stable when an occurrence moves), `DTSTAMP` UTC, all-day = `DTSTART;VALUE=DATE` + exclusive `DTEND;VALUE=DATE`, timed = `TZID=Africa/Kampala` (no end time = no DTEND; multi-day without end time ends 23:59), `STATUS:TENTATIVE` + "Date to be confirmed." for unconfirmed dates, `URL`, `CATEGORIES`.

### Admin (inside `auth`; `calendar.manage` = admin + editor)
| Path | Handler | State |
|---|---|---|
| `GET /admin/calendar` (`?month=`, `?view=list`) | `CalendarController::index` | month view of everything incl. hidden / cancelled, click-a-day quick add, list tab with filters and bulk actions |
| `GET /admin/calendar/holidays` | `holidays` | holidays with next date + **Confirm date**, change one year, extra holidays, international days |
| `GET /admin/calendar/create` (`?date=`), `GET /admin/calendar/{id}/edit` | `create`, `edit` | the full editor with live next-dates preview and per-occurrence changes |
| `POST /admin/calendar/quick-add` | `quickAdd` | complete |
| `POST /admin/calendar`, `POST /admin/calendar/{id}` | `store`, `update` | complete (advanced repeats need `calendar.advanced`) |
| `POST /admin/calendar/{id}/delete`, `/duplicate`, `/toggle` | | complete (toggle = hide / show, how a system holiday is hidden) |
| `POST /admin/calendar/bulk` (`action`, `ids[]`) | `bulk` | complete (`delete` needs `calendar.delete`, 403 otherwise) |
| `POST /admin/calendar/{id}/occurrence` (`occurrence_date`, `action`, `new_date`, ...; `return=holidays`) | `occurrence` | complete |
| `POST /admin/calendar/{id}/occurrence/{oid}/delete` | `occurrenceDelete` | complete |
| `GET/POST /admin/calendar/types`, `POST types/{id}`, `POST types/{id}/delete` | `CalendarTypesController` | complete |
| `GET /admin/calendar/export.csv`, `GET import-template.csv` | `CalendarTransferController` | complete |
| `GET /admin/calendar/import`, `POST /admin/calendar/import` | | complete (`dry_run=1` checks, `commit=1` saves; the commit re-validates the file, nothing in the posted payload is trusted) |
| `GET /admin/calendar/settings`, `POST` | `settings`, `saveSettings` | complete (`settings.manage`) |
| `GET /admin/api/calendar/month?month=` | `CalendarApiController::month` | complete: `{month,label,prev,next,from,to,week_start,days:{date:[item+flags+edit_url]}}` |
| `POST /admin/api/calendar/preview` | `preview` | complete: `{ok,errors,description,dates:[{date,end_date,label,weekday}]}` (header `X-CSRF-Token`) |

CSRF is automatic on POST. JSON callers send `Accept: application/json`; the controllers then answer `{ok, errors}` with 422.

## 7. Registry keys and settings
**`content('calendar', ...)`** sections: `hero` (eyebrow title lead image), `views` (month_tab agenda_tab upcoming_tab today_label prev_label next_label jump_label month_label year_label go_label more_label `{n}` all_day print_label time_note), `filters` (title group_label all_label holiday_badge tbc_badge moved_badge), `upcoming` (title count empty_title empty_text), `day` (panel_title `{date}` panel_empty close_label when_label where_label source_label about_label link_default program_label project_label event_label next_dates back_label cancelled_note not_found_title not_found_text), `share` (toggle; add_label share_label copy_label copied subscribe_title subscribe_text subscribe_label), `empty` (month_title `{month}` month_text filtered_text), `notes` (**disclaimer** sources_label sources_text), `ics` (calendar_name `{site}` tbc_note source_prefix), `aria` (nav_label grid_label `{month}` month_announce `{month}` `{count}` + month_announce_one, day_with_items `{date}` `{count}` + day_with_one, loading load_error filter_hint keyboard_hint), `seo` (month_title month_description). **`content('contact', 'calendar.*')`**: section switch `calendar._visible`, eyebrow title intro upcoming_title upcoming_count empty_text legend_title cta_label cta_url.
**Settings** (Admin > Settings > Calendar): `calendar_week_start` (`monday|sunday|saturday`), `calendar_show_on_contact` (`1|0`), `calendar_default_types` (comma list of type slugs, empty = all). The Contact block shows only when the setting, the registry switch `contact` > `calendar` **and** the published `calendar` page all allow it. The disclaimer is a registry field (`notes.disclaimer`), editable in Admin > Pages > Calendar.

## 8. Seeded dates and SOURCES

Rules are anchored at 2020 (`start_date`), so years before 2020 show nothing. Verification (searches and fetches done while building; the primary statute text could not be opened, see section 9):
* **Uganda public holidays (14)**: Public Holidays Act (Uganda), `https://ulii.org/akn/ug/act/1965/23` (the Act declares the days of its Schedule to be public holidays and lets the President add others); list cross-checked against the Uganda Ministry of Foreign Affairs holiday list (`luanda.mofa.go.ug/basic-page/public-holidays`: New Year's Day, Liberation Day, Archbishop Janani Luwum Memorial, International Women's Day, Eid al-Fitr, Good Friday, Easter Monday, Labour Day, Martyrs' Day, Eid al-Adha, Heroes Day, Independence Day, Christmas, Boxing Day), Wikipedia "Public holidays in Uganda" (same 14 days) and the 2026 lists of timeanddate / officeholidays-type sites (Good Friday 3 Apr, Easter Monday 6 Apr 2026).
  1 Jan New Year's Day, 26 Jan NRM Liberation Day, 16 Feb Archbishop Janani Luwum Day, 8 Mar International Women's Day (also the UN day; one entry, typed as the holiday), Good Friday (Easter -2), Easter Monday (Easter +1), 1 May Labour Day, 3 Jun Uganda Martyrs' Day, 9 Jun National Heroes' Day, **Eid al-Fitr, Eid al-Adha (Islamic rules, flagged "date to be confirmed")**, 9 Oct Independence Day, 25 Dec Christmas Day, 26 Dec Boxing Day.
  No other statutory day was found. **Ad hoc days** (the President can declare extra holidays, e.g. 15-16 January 2026 for the general election and 12 May 2026 for the swearing-in, as reported by news sites) are NOT seeded: an admin adds them as normal entries of the "Uganda public holiday" type.
  **Weekend rule**: no source confirmed that the Act moves a holiday that falls on a Sunday to the Monday (one payroll site claims so; Zambian law has such a sentence; the 2026 lists show Women's Day on Sunday 8 March with no Monday). The calendar therefore shows the **statutory date only**; if the government announces a substitute day, an admin adds or moves it (override). Nothing in the entry texts claims otherwise.
* **International / UN days (46)** (type "International / UN day"; each carries its own source label and, where verified, link). UN dates verified against `https://www.un.org/en/observances/list-days-weeks` (and its per-day pages as linked there): Education 24 Jan; Zero Tolerance FGM 6 Feb; Women and Girls in Science 11 Feb; Social Justice 20 Feb; Mother Language 21 Feb (UNESCO); Water 22 Mar; Mother Earth 22 Apr; Families 15 May; Telecommunication & Information Society 17 May (ITU); Environment 5 Jun; Child Labour 12 Jun; Refugee 20 Jun; Youth Skills 15 Jul; Mandela Day 18 Jul; Youth 12 Aug; Humanitarian 19 Aug; Charity 5 Sep; Literacy 8 Sep (UNESCO); Peace 21 Sep; Teachers 5 Oct (UNESCO); Girl Child 11 Oct; Eradication of Poverty 17 Oct; United Nations Day 24 Oct; Tolerance 16 Nov (UNESCO); Universal / World Children's Day 20 Nov; Elimination of Violence against Women 25 Nov; AIDS 1 Dec; Persons with Disabilities 3 Dec; Volunteers 5 Dec; Human Rights 10 Dec; Migrants 18 Dec. WHO days: World Health Day 7 Apr, Malaria 25 Apr, Mental Health 10 Oct (UN list), Blood Donor 14 Jun, No Tobacco 31 May, Hand Hygiene 5 May, Tuberculosis 24 Mar (WHO pages found by search). Others: **Safer Internet Day = the Tuesday falling on 5 to 11 February** (rule `nth_weekday` with `nth` 1 and `from_day` 5; **flagged "date to be confirmed"**, see below), **Girls in ICT Day = 4th Thursday of April** (ITU; 23 Apr 2026 matches the UN list), World Red Cross and Red Crescent Day 8 May (IFRC, Henry Dunant's birthday), **Africa Day 25 May** (African Union, OAU founded 25 May 1963), Day of the African Child 16 Jun (African Union), World Cancer Day 4 Feb (UICC), Global Handwashing Day 15 Oct (Global Handwashing Partnership), World Food Day 16 Oct (FAO; UN list).
* No Lumina programme session, event or meeting is seeded.

## 9. What could NOT be verified (do not present as certain)
1. **Primary statute text**: ulii.org returned 403 and the PDF 404, so the Schedule was cross-checked through the Ministry of Foreign Affairs list, Wikipedia and secondary sites, not read from the Act. Chapter numbers differ between sources (Cap. 255 / 174), so none is printed.
2. **Islamic dates**: the real day follows moon sighting and the announcement of the Uganda Muslim Supreme Council (UMSC) / the government. The engine is the tabular (arithmetical) calendar, a **prediction**: against the days actually observed in Uganda it was exact for 2024 Eid al-Fitr (Wed 10 Apr) and 2026 (Fitr Fri 20 Mar, Adha Wed 27 May) but **one day late** for 2024 Eid al-Adha (engine Mon 17 Jun, UMSC announced Sun 16 Jun: Daily Monitor "Muslims to celebrate Idd ul Adhuha on June 16"), 2025 Eid al-Fitr (engine Mon 31 Mar, UMSC announced Sun 30 Mar: Daily Monitor, `monitor.co.ug/uganda/news/national/muslims-celebrate-idd-ul-fitr--4983576`) and 2025 Eid al-Adha (engine Sat 7 Jun, the Ministry of Public Service declared **Fri 6 Jun 2025** a public holiday: Nile Post, `nilepost.co.ug/faith/263038/govt-declares-friday-a-public-holiday-for-eid-al-adhuha`). The engine was never early in these checks, so a predicted Eid day is usually **the day shown or one day earlier** (the disclaimer says so). The reference dates used by the selftest for 2027-2030 (Fitr 9 Mar 2027, 26 Feb 2028, 13 Feb 2029, 4 Feb 2030; Adha 16 May 2027, 5 May 2028, 24 Apr 2029, 13 Apr 2030) come from astronomical estimates on secondary sites; the tabular dates are **up to two days later** than those (2029 Eid al-Fitr: engine 15 Feb against the reference 13 Feb), which is why the selftest accepts "within 2 days".
   **The five days above and 2026 are seeded as confirmed overrides** (`database/seed.sql`, end: `INSERT IGNORE INTO calendar_overrides`, with the source in the note), so those years show the real day and no "Date to be confirmed": 2024 Fitr 10 Apr, 2024 Adha 16 Jun, 2025 Fitr 30 Mar, 2025 Adha 6 Jun, 2026 Fitr 20 Mar, 2026 Adha 27 May. An override an admin saved for the same occurrence always wins (unique key), and an admin who resets one of these will see it return at the next import of the seed. **Every other Eid occurrence stays "Date to be confirmed" until an admin confirms it** (Admin > Calendar > Holidays & days).
3. **Safer Internet Day**: the organisers announce each edition. Verified: Tue 7 Feb 2023 and Tue 6 Feb 2024 (European Commission / Better Internet for Kids: `better-internet-for-kids.europa.eu/en/news/safer-internet-day-2024-approaches-only-two-weeks-left`; School Education Platform "Save the date Safer Internet Day 2024"), Tue 5 Feb 2019 (UK Safer Internet Centre), and the editions 2020-2022 and 2025-2027 all fall on the Tuesday of day 5 to 11 (11 Feb 2020, 9 Feb 2021, 8 Feb 2022, 11 Feb 2025, 10 Feb 2026, 9 Feb 2027). Wikipedia words the rule as "second day of the second week of February"; Insafe's current page says "second Tuesday", which contradicts its own 2023 and 2024 dates (a second-Tuesday rule would give 14 Feb 2023 and 13 Feb 2024, both wrong). The two readings agree in 2025-2028 and 2031-2033 and **differ by a week in 2029, 2030, 2034 and 2035** (6/5/7/6 Feb against 13/12/14/13 Feb), where secondary sites disagree. The entry therefore uses the "Tuesday falling on day 5 to 11" rule (matches every verified edition) **and is flagged "date to be confirmed"**; re-check 2029 onwards on `saferinternetday.org` once Insafe publishes it and confirm the date under Holidays & days.
4. Deep links: the per-day pages of Day of the African Child, Hand Hygiene, Tuberculosis, Mental Health Day, World Food Day, World Cancer Day and Global Handwashing Day were not verified, so those entries show the source name without a link (an admin can add one). The Africa Day link is the African Union's 2018 page.
5. Whether the 2020 anchor matches the year each holiday became law was not checked (it only means earlier years are not shown).

## 10. Ownership
| Owner | Owns exactly |
|---|---|
| **WEB agent** | `app/controllers/Web/CalendarController.php` (page, detail; keep `feed` and `icsAll` / `icsEntry` shapes), `app/views/web/calendar/*`, `app/views/partials/calendar-*.php`, the Contact embed (`partials/calendar-embed.php` and the block in `views/web/contact/index.php`), `scss/pages/_calendar.scss`, `assets/js/calendar.js`, the calendar block of `SitemapBuilder` (the page `/calendar` is already listed; entry detail pages are optional) |
| **ADMIN agent** | `app/controllers/Admin/Calendar{,Types,Transfer,Api}Controller.php`, `app/views/admin/calendar/*`, `scss/admin/_calendar.scss`, `assets/js/admin-calendar.js`, CSV import, the dashboard card styling |
| **ARCHITECT (frozen)** | schema, seed, models, `app/services/Calendar/*`, registry files `pages/calendar.php` and the Contact `calendar` section, `settings_schema.php`, `config/app.php`, routes, `admin_nav/calendar.php`, `scss/main.scss` + `scss/admin.scss`, `bin/selftest*.php`, `DashboardController` hook, this file |

**Status**: the public side is complete (in-place month change from `/calendar/feed` with an aria-live announcement, day panel and entry dialog, swipe, keyboard grid, mobile agenda below 768 px, no-JS paths `?month=`, GET filter form and `?cal=` on `/contact`, print styles, Event JSON-LD, sitemap entries).
The admin side is complete (editor with live preview and per-occurrence move / cancel / confirm, manager month and list views with bulk actions, holidays tab, types manager with live contrast check, CSV import with dry run, settings panel). Roles: admin and editor edit the calendar; advanced repeats, bulk delete and settings are admin only.

## 11. Privacy and security
Public endpoints expose only active + public entries; no personal data; the calendar stores nothing about visitors **except the rate limiter**: the three public GET endpoints are throttled per IP, which stores a keyed hash (HMAC-SHA256 with the application key, cut to 40 hex characters; `app/core/RateLimit.php`) of the key `throttle:{ip}:{method}:{path}` with a counter until the window ends (60 s) and until a later request sweeps expired rows (1 request in 20). The hash cannot be reversed by trying every IPv4 address without the application key, but it is still derived from the address, so the privacy page says that a hashed IP address is kept for a short time. Every parameter is read through a string-only guard: an array value (`?cal[]=`, `?start[]=`) is ignored or answered with 400, never an error page. All output is escaped (`e()`, `rich()` for sanitised descriptions); iCal and JSON are escaped / encoded; admin inputs are validated (dates, times, ranges, rule schema, http/https URLs, sizes); every mutation is POST + CSRF + role check + audit; the occurrence a change applies to must be produced by the rule, and an override can only be removed through its own entry (IDOR guard); CSV export goes through `CsvExport` (formula-safe). Entry text may be plain-text only in the CSV import.

## 12. Commands
| Task | Command |
|---|---|
| Apply schema + seed (idempotent) | `/c/xampp/php/php.exe bin/setup.php` |
| Rebuild the one-file live import | `/c/xampp/php/php.exe bin/build-import-sql.php` |
| Calendar tests alone / everything | `/c/xampp/php/php.exe bin/selftest-calendar.php` / `bin/selftest.php` (`ALL CHECKS PASSED`) |
| SCSS | `PYTHONUTF8=1 "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" bin/build-scss.py` |
| Deploy order | import `database/lumina-live-import.sql` first (or with the code upload); until then the calendar pages answer 404 and `/contact` simply omits the block |

## 13. Integration checks (final pass)
Verified on the local database: `bin/setup.php --fresh` gives 7 types, 14 Uganda holidays and 46 international days (60 entries, none for Lumina programmes), a second run changes nothing; the previous committed live import followed by the new one (twice) adds only the three calendar tables, 3 settings and the `calendar` page row, keeps owner edits (renamed / hidden entry, renamed type, confirmed Eid date, page nav label, edited setting) and creates no duplicate. Engine dates 2025-2035: Western Easter and Good Friday match the published tables; the Eid dates are tabular predictions and stay flagged. End-to-end tests (admin, editor, anonymous) covered quick add (single, range, weekly with until), edit, HTML sanitising, move / cancel / confirm of one occurrence, hide a system holiday, system delete refused, CSV dry run + commit + export (formula-safe), events merged, type deletion rules, bulk delete admin-only, settings admin-only, CSRF on every POST, override IDOR guard, `.ics` validation, and the public pages around year boundaries.

## 14. Review fixes (fixer pass)
Every finding of the review was fixed and re-tested (`bin/selftest-calendar.php`, section "review fixes", plus the HTTP checks): Safer Internet Day rule and flag; the known Eid days seeded as confirmed overrides; array query values (`/contact`, `/calendar/feed`, `/calendar/{id}.ics`, admin API and forms) never give a 500; overlapping repeats refused, feed capped at 1500 items, the expansion has a byte budget (`OccurrenceExpander::MAX_BYTES`) and a `truncated` flag that is logged; unpublishing the Calendar page now also closes `/calendar/feed`, `/calendar.ics` and `/calendar/{id}.ics`; `.ics` cut at 3000 keeps all holidays and international days (system entries are also read first, so no limit drops them); duplicate-key races on override and slug saves retry or update instead of answering 500; a multi-day series with "repeat until" stays visible while its last occurrence runs; `?date=` picks the occurrence whose rule date it is; quick add with an invalid repeat saves nothing; a moved occurrence may not end before it starts (one-day occurrences); events that end at 00:00 stay on their own day; JSON-LD `endDate` only when it matches the `startDate` format; the Contact block clamps `?cal=` to 1901-01 .. 2199-12 like the page; CSV import counts the seeded holidays as duplicates; the public month grid starts at 1024 px (below that the agenda lists the month, names in full) and holiday names break only inside a word that is wider than the cell; admin month pills put the flag under the title; forced-colours (Windows high contrast) rules for today, the selected tab, holiday pills, the dialog and the admin cells; screen-reader labels have singular forms (`1 entry`); the tab title follows an in-place month change; the mobile agenda marks today with `aria-current` and the word "Today"; admin selects keep room for the chevron; the dead registry field `filters.legend_title` is gone; the selftest filters to its own entries so owner programme data cannot fail it.