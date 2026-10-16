# Planned Programs - content map (for the view / SCSS agents)

Source of truth: `docs/content-source/planned-programs.md` (owner copy, verbatim). The registries `app/config/pages/programs.php` and
`app/config/pages/projects.php` and the seed (`database/seed.sql`) were generated from it, so every default below IS the owner wording.
These are PLANNED programs: never add words such as "ongoing", "we have trained", "impact achieved". Status wording is "Planned".

Read copy only through `content()`, `content_html()`, `content_list()`, `content_btn()`, `section_on()`/`section_begin()` (see ARCHITECTURE section 8.3).
Highlight convention: text fields may contain `[[phrase]]`; print headings with `highlight()`.

## 1. Data (database)

### `programs` = the 3 pillars (sort_order 1,2,3; all `is_active=1`, `is_featured=1`, `status='planned'`)
| slug | title | icon | photo (`image`) |
|---|---|---|---|
| `health-community-wellbeing` | Health & Community Wellbeing | `heart` | `assets/img/site/photo-04.jpg` |
| `education-skills-youth-development` | Education, Skills & Youth Development | `graduation-cap` | `assets/img/site/photo-06.jpg` |
| `technology-digital-inclusion-innovation` | Technology, Digital Inclusion & Innovation | `laptop` | `assets/img/site/photo-03.jpg` |

Columns used: `slug title tagline summary body image image_alt icon status is_featured is_active sort_order`.
* `title` has no "01." number: display it yourself from the loop index / `sort_order` (01, 02, 03).
* `tagline` = the "Planned focus:" sentence WITHOUT the label (render the label from `labels.planned_focus`).
* `summary` = ONE existing sentence of the intro (card text). `body` = sanitiser-safe HTML: the intro paragraph(s) verbatim, then
  `<h3>Our intended contribution</h3>` + the contribution paragraph(s) verbatim (Health includes the clinical-services caveat). Print with `HomeSupport::answerHtml()` / `rich()` as `web/programs/show.php` does.
* `image_alt` is honest and generic (no identities claimed). `seo_title` / `seo_description` are NULL (the controller falls back to title / excerpt of the summary).
* **NEW COLUMN `programs.status ENUM('planned','ongoing','completed') NOT NULL DEFAULT 'planned'`.**
  Public labels (editable): `content('programs','labels.status_planned')` = "Planned program area", `labels.status_ongoing` = "Ongoing", `labels.status_completed` = "Completed".
  Helper: `\App\Models\Program::statusLabels()` returns `['planned'=>..,'ongoing'=>..,'completed'=>..]`; `Program::STATUSES` lists the values.
  `partials/program-card` already shows the badge (photo corner, or in the meta line without photo) and accepts an optional `$statusLabels` map;
  `web/programs/show.php` shows a badge above the body. Keep them when you rewrite the views (badge classes: `badge badge--info` planned, `--success` ongoing, `--neutral` completed; on a photo add `card__badge`).

### `projects` = the 27 "Proposed areas of intervention" (7 Health, 9 Education, 11 Technology; `status='planned'`, `is_active=1`, `is_featured=0`)
Columns used: `program_id slug title summary body status sort_order` (`tagline`, `image`, `image_alt`, `location`, `start_date`, `end_date`, `seo_*` are NULL).
* `program_id` -> the pillar (join `programs` for its title/slug). `sort_order` 1..27 = the order of the owner copy (Health 1-7, Education 8-16, Technology 17-27).
* `title` = text before the colon, `summary` = the sentence after it, `body` = `<p>` + the same sentence. Slugs e.g. `community-health-education`, `digital-literacy`,
  `girls-education-young-womens-empowerment`, `ict-skills-computer-literacy`, `robotics-automation`.
* Note: Digital Literacy (Education) and ICT Skills & Computer Literacy (Technology) are two separate owner entries; keep both.
* The projects have no photo (cards must work without `image`; `partials/project-card` already does). Do not invent project photos.
* `ProjectsController::index` lists every active project grouped by pillar (never paginated) and passes `program_id` for the filter.

### Removed
The 4 old programs and 4 old projects (slugs `digital-pathways-for-youth`, `digital-employability-internship-linkages`, `inclusive-digital-access-for-out-of-school-youth`,
`youth-digital-entrepreneurship-micro-income-development`) are deleted (dev DB + seed). `/programs/<old slug>` now answers 404. No link to them remains in code.

### Admin
Admin > Programs: new "Program status" select (required; default "Planned program area") and a "Stage" column in the list. Admin > Projects: new projects now default to "planned".

## 2. Registry `programs` (`content('programs', '<section>.<field>')`)
The default HTML id written by `section_begin()` is the section key with `_` turned into `-` (`pillars`, `cross-cutting`, `approach`, `audience`, `partnerships`, `commitment`); pass an explicit `'id'` for another anchor. The hero button 2 default link is `#pillars`, so the pillars section MUST keep `id="pillars"` (it does by default).

| section (visibility) | fields (type) |
|---|---|
| `hero` (always) | `eyebrow` "Our planned programs"; `title` "Building [[Pathways to Opportunity]]." ; `title2` "Preparing Communities for Change." (h1 = title + title2, two display lines); `lead` paragraph 1; `lead2` paragraph 2; `lead3` paragraph 3; `vision` sentence; buttons `cta1_label/cta1_url` ("Partner With Us" -> `/involvement`), `cta2_label/cta2_url` ("Explore Our Planned Programs" -> `#pillars`); `image` (photo-03, has alt) |
| `pillars` (always) | `eyebrow` (''), `title` "Our three planned program [[pillars]]", `intro` ('' = optional, do not print when empty), `card_cta` "View pillar", `empty_title`, `empty_text`, `empty_cta_label`, `empty_cta_url` |
| `cross_cutting` (toggle, on) | `eyebrow`, `title` "Our cross-cutting [[priorities]]", `intro`, `items` repeater **{title, text}** x8 |
| `approach` (toggle, on) | `eyebrow`, `title` "Our proposed implementation [[approach]]", `intro`, `items` repeater **{number, title, text}** x6 (`number` = "01".."06", text) |
| `audience` (toggle, on) | `eyebrow`, `title` "Who we intend to [[serve]]", `intro`, `items` repeater **{text}** x8, `geographic` (textarea, two sentences: primary focus Northern Uganda + locations determined by needs...) |
| `partnerships` (toggle, on) | `eyebrow`, `title` "Partnerships that can turn plans into [[action]]", `intro` (sentence 1), `list_intro` (sentence introducing the list), `items` repeater **{text}** x9, `closing` + `closing2` (the two closing sentences, print as two paragraphs), buttons `cta1_*` ("Discuss a Partnership" -> `/involvement`), `cta2_*` ("Contact Lumina Africa Foundation" -> `/contact`) |
| `commitment` (always, no toggle on purpose) | `eyebrow`, `title` "Our commitment to [[responsible development]]", `body` **richtext** (3 paragraphs; the 2nd is the planned-programs disclaimer: print with `content_html()`), `signoff_name` "Lumina Africa Foundation", `signoff_tagline` "Igniting Light. Inspiring Change." |
| `labels` (always) | `status_label` "Status", `status_planned` "Planned program area", `status_ongoing` "Ongoing", `status_completed` "Completed", `planned_focus` "Planned focus", `interventions_heading` "Proposed areas of intervention", `view_pillar` "View pillar", `view_interventions` "View interventions", `interventions_empty` (message for a pillar without projects) |
| `detail` (always) | `back_label` "Our planned programs", `related_title` "Proposed areas of intervention", `related_cta` "View project", `more_title` "Our three planned program pillars", `join_label` "Partner With Us", `join_url` `/involvement` |
| SEO | title "Our Planned Programs"; description = the first intro sentence (registry `seo`, printed by the layout: do not print meta tags) |

The sections of the previous site that described delivery (`overview`, `accountability`, `impact`, `how_we_work`, `faq`) and `labels.contribution_heading` were REMOVED from the registry: nothing rendered them and Admin > Pages would have offered dead controls. The "Our intended contribution" box on a pillar page is the FIRST `<h3>` of the program body (Admin > Programs > Full description); `ProgramsController::splitBody()` turns it into the highlighted box.
`detail.*` keys that existed before are unchanged in name (values updated).

## 3. Registry `projects` (`content('projects', '<section>.<field>')`)
| section (visibility) | fields |
|---|---|
| `hero` (always) | `eyebrow` "Our planned projects", `title` "Our planned [[interventions]]", `lead` "Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation.", `cta_label` "Partner With Us" / `cta_url` `/involvement` (empty url = donation link from Settings), `image` (photo-06) |
| `list` (always) | `eyebrow` (''), `lead` "Proposed areas of [[intervention]]", `card_cta` "Read more", **`status_planned` "Planned"**, `status_ongoing` "Ongoing", `status_completed` "Completed" (the project status labels; unchanged keys, used by `ProjectsController::statusLabels()`), `empty_title`, `empty_text`, `empty_cta_label`, `empty_cta_url`, `filter_empty` |
| `filters` (toggle, on) | `aria_label` "Filter by program pillar" (not shown), `all` "All", `pillars` repeater **{slug, label}** x3: slug = the program slug above, label = the pillar title (use `slug` to match `programs.slug`; fall back to the DB title) |
| `disclaimer` (always) | `eyebrow`, `title` "Our commitment to [[responsible development]]", `text` = "The initiatives described on this page represent our planned program areas and proposed interventions. They should not be interpreted as confirmation that every activity is currently funded, operational or already delivering results." Always render it near the list. |
| `cta` (toggle, on) | `eyebrow`, `title` "Partnerships that can turn plans into [[action]]", `text`, buttons `cta1_*` ("Discuss a Partnership" -> `/involvement`), `cta2_*` ("Contact Lumina Africa Foundation" -> `/contact`) |
| `detail` (always) | `back_label` "Our planned projects", `program_label` "Program", `status_label` "Status", `location_label`, `dates_label`, `more_title` "Proposed areas of intervention", `join_label` "Partner With Us", `join_url` `/involvement` (hide location/dates rows when empty: the 27 projects have none) |
| SEO | title "Our Planned Projects"; description "Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation." |

The `approach` section of the previous site (delivery claims) was REMOVED.

## 4. Other pages changed (registry only)
* `home.php` > `help`: eyebrow "Our planned programs", title "Building [[Pathways to Opportunity]]", lead = the interventions sentence, `items` = the 3 pillars (icon, title, text = planned-focus sentence, url `/programs/<slug>`).
* `home.php` > `projects` (carousel): eyebrow/title/intro now the pillars wording, `limit` default 3 (so the 27 projects do not flood the carousel after the 3 program cards), CTA "Explore Our Planned Programs" -> `/programs`, fallback cards = the 3 pillars.
* Footer "programs" list, sitemap and JSON-LD read the DB: they now list the 3 pillars (sitemap also the 27 projects).

## 5. Idempotent seed behaviour
`database/seed.sql` deletes the 8 old rows by slug (projects first), then upserts the 3 programs and 27 projects with `ON DUPLICATE KEY UPDATE` (refreshed only while the row is pristine, i.e. `updated_at = created_at`, or with `SET @reseed = 1`). Resolve `program_id` by slug sub-select. Migration of `programs.status` lives in `database/schema.sql` (information_schema guard + `PREPARE`, works on MySQL and MariaDB, re-runnable).

## 6. Things I did NOT change (owner to review; all describe delivery or figures from the old site)
* DB `stats` + `home.php` stat fallback: "Youth Trained 150+", "Active Programs 5".
* Setting `meta_description` (seed) and Home hero / "Who we are" / mission blocks, "measurable change" paragraph (Home, around the impact/about block), About history, `about.php` "When we equip youth..." copy.
* FAQ rows in the `faqs` table (group `donate`, `general`) that mention donations supporting digital literacy training / health outreach.
