# Content notes - seed data and live-site audit

Owner of this file and of `database/seed.sql`: B6 "content-seed".
Source: the live site https://www.luminaafricafoundation.com/ crawled on 2026-10-15 (every page in the menu, the site's own
`sitemap.xml`, the two blog posts and the orphan pages it exposes). The text snapshots in `docs/live-snapshot/` were re-checked
against a fresh crawl: they match.

This file is the answer to four questions: what was put into the database and where it came from, which parts of the live site
were deliberately left out, which claims cannot be verified, and which real data only the foundation can supply.

---------------------------------------------------------------------------------------------------------------------

## 1. How `database/seed.sql` behaves

| Command | Effect |
|---|---|
| `php bin/setup.php` | creates the schema, imports the seed, then adds any registry page that has no `pages` row |
| `mysql -u root lumina < database/seed.sql` | re-import: safe, never duplicates, never deletes, never truncates |
| `mysql -u root lumina --init-command="SET @reseed=1" < database/seed.sql` | development only: forces every seeded row back to the values in the file (overwrites admin edits) |

* **Non-destructive by design.** A row that already exists is refreshed from the file only while it is still *pristine*
  (`updated_at = created_at`, i.e. nobody edited it in the admin). Anything the owner changed in the admin survives a re-import.
  Pristine rows that `bin/setup.php` created from the registry before the seed ran (for example `home`) are refreshed, so the
  menu order and flags below always apply on a fresh install.
* Tables with a unique natural key (`settings`, `pages`, `programs`, `projects`, `activities`, `posts`, `content_blocks`) use
  `INSERT ... ON DUPLICATE KEY UPDATE`. Tables without one (`stats`, `faqs`, `gallery_images`, `team_members`) are filled once
  (`INSERT ... SELECT ... WHERE NOT EXISTS`; stats and FAQs: only while their group is empty, gallery: per image path, team: per name).
* Tested: imported repeatedly through the mysql client and through the PDO path used by `bin/setup.php`; row counts stay
  identical; admin edits to a program, setting, page label, stat, FAQ, team bio, gallery caption and post status survived a
  re-import; `@reseed=1` restored all of them. Every seeded HTML body is stable under `Sanitizer::html()`.
* Page COPY (headings, paragraphs, buttons, images) is **not** seeded: the registry defaults in `app/config/pages/*.php` already
  carry the live wording. A fuzzy comparison of every registry default against the live text found no wording that differs from
  the live site apart from the intended placeholders (`{reg_number}`, `{year}`), so **no `content_blocks` rows are seeded**.
  (If one is ever needed, the generator slot exists: add rows to the `content_blocks` section of the seed.)

---------------------------------------------------------------------------------------------------------------------

## 2. What is seeded, and where it came from

| Table | Rows | Source on the live site | Notes |
|---|---|---|---|
| `settings` | all 30 keys of `settings_schema.php` | footer of every page, `/contact`, `/donate`, home hero | Phones, emails, socials (tracking parameters removed), WhatsApp, GoFundMe link, city/address. Bank, mobile-money, PayPal, map URL, analytics id, YouTube and donate intro are saved as empty strings (nothing real to show). `footer_credit` = "Website managed by Lumina Africa Foundation." (the live footer credit to the previous web agency is not carried over). `og_image` is the logo, not the schema default photo (see section 5). |
| `pages` | 22 (18 public + 4 virtual site-wide) | live menu `/` | Titles = registry titles. Header/footer flags and order in section 4. |
| `stats` | 2 (group `home`) | `/` "Vision & Mission" block | 150+ Youth Trained, 5 Active Programs. Only these two exist on the live site. |
| `programs` | 3 | `docs/content-source/planned-programs.md` (owner copy, verbatim) | The three PLANNED pillars: Health & Community Wellbeing, Education, Skills & Youth Development, Technology, Digital Inclusion & Innovation. Column `status` = `planned` (public label "Planned program area"). `seo_description` is set for the Education and Technology pillars so the meta description is a complete sentence. The four digital programs of the old site were REMOVED (section 3a). |
| `projects` | 27 | same owner copy | The 27 "proposed areas of intervention" (7 Health, 9 Education, 11 Technology), status `planned`, no photo, no location, each linked to its pillar by `program_id`. The four old projects were REMOVED (section 3a). |
| `activities` | 1 | `/activities` ("VISIT TO WII QUARRY SITE") | Full live text; the summary is a condensed sentence taken from it. No date and no image (see section 3). |
| `posts` | 2 | `/empowering-voices-planting-hope-lumina-africa-foundations-weekend-at-wii-got-quarry`, `/blog-post` | Post 1 published (22 Aug 2025, category Community Engagement); post 2 imported as a **draft** (section 3). |
| `faqs` | 6 (group `donate`) | `/donate` FAQ | Three are inactive until confirmed (section 3). |
| `gallery_images` | 6 | the photos bundled in `assets/img/site/` | Four active, two inactive (section 5). Honest alt text and captions; albums are neutral labels. |
| `team_members` | 1 | `/about` (board) and `/` (founder story) | Moses Komakech, Founder, group `board`. No photo, no email (section 5). |
| `jobs` | 0 | `/careers` | The live site says "Currently, there are no openings." The volunteer pathway is page copy (Careers > Volunteer opportunities), not a vacancy. |
| `partners`, `documents`, `events`, `event_registrations`, `stories` | 0 | none | Nothing real exists on the live site. Pages and admin screens are fully built and show empty states. |

### Source URL for each live page (reference for editors)

| Page | Live URL | Registry slug |
|---|---|---|
| Home | https://www.luminaafricafoundation.com/ | `home` |
| About | https://www.luminaafricafoundation.com/about | `about` |
| Programs | https://www.luminaafricafoundation.com/programs | `programs` |
| Projects | https://www.luminaafricafoundation.com/projects | `projects` |
| Involvement | https://www.luminaafricafoundation.com/involvement | `involvement` |
| Careers | https://www.luminaafricafoundation.com/careers | `careers` |
| Donate | https://www.luminaafricafoundation.com/donate | `donate` |
| Blog | https://www.luminaafricafoundation.com/blog | `blog` |
| Contact | https://www.luminaafricafoundation.com/contact | `contact` |
| Activities | https://www.luminaafricafoundation.com/activities | `activities` |
| Blog post (real) | https://www.luminaafricafoundation.com/empowering-voices-planting-hope-lumina-africa-foundations-weekend-at-wii-got-quarry | post 1 |
| Blog post (template-like) | https://www.luminaafricafoundation.com/blog-post | post 2 (draft) |
| "Impact" (not in the menu) | https://www.luminaafricafoundation.com/impact1 | not used |

Where every block lives in the CMS: public copy = Admin > Pages > (page) ; org-wide facts = Admin > Site settings ;
programs/projects/activities/posts/gallery/jobs = Admin > Content group ; stats = Admin > Impact figures ; FAQs = Admin > FAQs ;
team/partners/documents/events = Admin > Organisation group (Impact stories = Admin > Impact stories).

---------------------------------------------------------------------------------------------------------------------

## 3. Flags and discrepancies the owner must resolve

1. **Registration number.** The live home page says `Reg. No: GSD/R 1545`, the live About page says `GSD/R 1234`.
   The seed uses **GSD/R 1545** (setting `reg_number`, also used by the About page lead through `{reg_number}`).
   Confirm the correct number with the registrar and edit it in Admin > Site settings.
2. **Broken stat labels on the live site.** The live DOM lists the values `150+`, `5` and then the labels `Active Programs`,
   `Youth Trained` (the labels are in the opposite order). The seed pairs them as intended: **150+ Youth Trained** and
   **5 Active Programs**. Both are numbers the foundation states without any breakdown, period or evidence: confirm them.
3. **"5 Active Programs" counter.** The Home stat still says 5 Active Programs, but the owner copy now describes THREE PLANNED program pillars and says nothing is confirmed as operational. Change or remove the counter in Admin > Impact figures (listed in section 3a, not changed here).
4. **(Resolved) Program "Impact Indicators".** The old indicator lists ("120 vulnerable youth enrolled annually" and similar) were part of the four removed programs and no longer exist anywhere in the site.
5. **Donate FAQ, three answers hidden.** The live answers below are seeded but `is_active = 0` (public Donate page does not show them
   until you switch them on in Admin > FAQs), because they promise things the site does not offer yet or contradict the programs:
   * "How can I donate?" lists mobile money, bank transfer and "PayPal/Stripe". Only the GoFundMe link is real today; no bank,
     mobile-money or PayPal details have been published. Enable it after filling Site settings > Donations.
   * "Can I donate monthly?" promises recurring donations. Nothing in the site or the GoFundMe link confirms this.
   * "What programs do donations support?" says school supplies and community health outreach for children and families. The
     foundation's programs are now described as three PLANNED pillars (Health, Education, Technology): the answer must not suggest
     that funded activities are running. Review it before switching it on.
   The other three (tax deductibility, who to contact, impact of a donation) are active. Note: while the table has active rows for
   group `donate`, the page ignores the six fallback questions in the Donate registry.
6. **Template boilerplate on the live site that contradicts the digital focus.** The meta descriptions of Home, About, Programs,
   Projects, Involvement, Donate and the "Impact" page speak of "healthcare, education, and community engagement"; the Involvement
   page says volunteers help "healthcare access, educational empowerment, and digital inclusion"; the Programs FAQ says "programs in
   education, health, technology, and entrepreneurship"; the Careers page says "health, education, digital innovation, leadership".
   The registry copy of Careers, Involvement, the Programs FAQ and Donate keeps these live sentences verbatim (the Careers volunteer
   role "Program support (health, education, ICT, environment)" is also seeded wording). Review them: they describe delivery and a
   scope that the owner's planned-programs copy deliberately does not claim.
7. **Second blog post imported as a draft.** "Breaking the Stigma: Discussing Mental Health in Northern Uganda" (25 Aug 2025,
   author shown as "Moses Koma", stock photo, no category, slug `blog-post`) is generic article text; its summary claims the
   foundation runs "community dialogues, wellness programs, and advocacy" on mental health, which appears nowhere else. It is seeded
   as `draft` with a proper slug (`breaking-the-stigma-discussing-mental-health-in-northern-uganda`). Publish it only if the
   foundation stands behind it.
8. **Founder titles.** Moses Komakech appears as "Founder" (home, About quote), "CHAIRPERSON" (About board list) and "our Director"
   (blog post and activity text). The seed uses "Founder" (group `board`) and says he is listed as Chairperson. Confirm the official title.
9. **Board of directors.** The live About page names five more people: Oyoo Francis Tom (Board Member), Okello Emmy (Board Member),
   Harriet Jason (Board Member), Alwoch Merol (Vice Board Chair), Anenocan Providence Okoda (Board Secretary). They are **not seeded**
   (the brief allows only the founder). Add them in Admin > Team once each person has confirmed they want to be listed and has
   supplied a photo. The About page shows the board from the Team table.
10. **Two Visions and three Missions.** Home says "An inclusive digital future for Northern Uganda where all young people and women
    are equipped, connected, and empowered..." while the lower About block says "A Northern Uganda where vulnerable youth thrive
    through digital skills, decent work, and equal opportunity." The mission appears in three wordings (home, About "Who We Are",
    About "Our Mission"). The registry keeps each where the live site had it; choose one official statement of each.
11. **Spelling on the live Activities page.** The caption reads "VISIT TO WII QUARRY SITE"; the text and the blog post say "Wii Got
    Quarry". The seed uses **Wii Got Quarry**. The activity has no date on the live site (the photo file names suggest 16 Aug 2025,
    the blog post is dated 22 Aug 2025): add the real date in Admin > Activities.
12. **Privacy notice.** The live site has no privacy page. `/privacy` is a notice written by B2 from what this website actually does
    (forms, cookies, Uganda's Data Protection and Privacy Act, 2019). It is **not** foundation wording and it has not been reviewed by
    a lawyer. Have it reviewed, or unpublish it in Admin > Pages (footer entry and page then disappear).
13. **Calls to external forms.** The live site sends "Partner With Us" and "Volunteer With Us" to Google Forms
    (`https://forms.gle/w5Tv3FALETyDYtXL9`, `https://forms.gle/f4tnML4FCCcLZdmZA`) and "Apply here" to another Google Form. The rebuilt
    site uses its own forms; the old links are only mentioned in help text.
14. **Extra phone number and opening hours.** `/contact` also lists `+256 741 819 287` and "Mon-Fri, 9am-5pm". Settings has only two
    phone keys, so both live in the Contact page copy (Admin > Pages > Contact), not in Site settings.

---------------------------------------------------------------------------------------------------------------------

## 3a. Planned Programs update (owner copy: `docs/content-source/planned-programs.md`)

**What changed.** The Programs and Projects pages, the program/project cards on Home, the footer program list, the sitemap and the
JSON-LD now carry the owner's wording, verbatim, as PLANNED work. The page copy is in the registry (`app/config/pages/programs.php`,
`projects.php`, Home `help` and `projects` sections); the three pillars are `programs` rows and the 27 proposed areas of
intervention are `projects` rows (all `status = planned`). Nothing on these pages says or implies that an activity is funded,
operational or delivering results; the "commitment" and "disclaimer" sections say so explicitly. See `docs/PROGRAMS_CONTENT_MAP.md`.

**Status field.** `programs.status` (`planned`, `ongoing`, `completed`; new column, migrated by `database/schema.sql`) is set
in Admin > Programs ("Program status", default "Planned program area"). `projects.status` already existed (new projects now default
to "planned"). Public labels are editable in Admin > Pages > Programs / Projects (labels, list).

**Legacy programs and projects REMOVED (owner instruction "remove the old ones").** The four programs and four projects imported from the
previous live site (slugs `digital-pathways-for-youth`, `digital-employability-internship-linkages`,
`inclusive-digital-access-for-out-of-school-youth`, `youth-digital-entrepreneurship-micro-income-development`) were deleted from the
development database (projects first, then programs; no `content_blocks`, gallery, FAQ or post rows referenced them), their INSERTs were
removed from `seed.sql`, and two slug-scoped `DELETE` statements near the programs seed clean any database seeded earlier. Their
detail URLs now answer 404. The registry sections of the old site that described delivery (Programs: overview, accountability, impact,
how we work, FAQ; Projects: approach) were deleted as well, so Admin > Pages no longer offers dead "Legacy" sections.
`docs/live-snapshot/*.txt` is kept as an archive of the old live site only; it is not used by the website.

**Not touched, owner to review (they make claims that the planned-programs copy does not support).**
* Home stats "150+ Youth Trained" and "5 Active Programs" (Admin > Impact figures) and the Home stat fallback in `home.php`.
* Home hero / "Who we are" / mission blocks and the "measurable change" paragraph, About history, `about.php` ("When we equip youth...").
* Setting `meta_description` and the Donate FAQ rows that mention digital literacy training or health outreach.

---------------------------------------------------------------------------------------------------------------------

## 4. Menu and page flags (the `pages` rows)

The seeded `nav_order` mirrors the live menu order: Home, About, Programs, Projects, Involvement, Donate, Blog, Contact,
Activities (+ Gallery under Activities). This deliberately differs from the architect's registry defaults (which put Donate last at
95 and Contact at 90): the database value wins and the owner can reorder everything in Admin > Menus.

| Slug | Label | Order | Header | Footer | Parent |
|---|---|---|---|---|---|
| home | Home | 10 | yes | no | - |
| about | About | 20 | yes | yes | - |
| team | Our Team | 21 | **no** | **no** | about |
| partners | Partners | 22 | yes* | yes* | about |
| reports | Reports & Downloads | 23 | yes* | yes* | about |
| programs | Programs | 30 | yes | yes | - |
| projects | Projects | 40 | yes | yes | - |
| involvement | Involvement | 50 | yes | yes | - |
| careers | Careers | 55 | yes | yes | involvement |
| donate | Donate (header button) | 60 | yes | yes | - |
| blog | Blog | 70 | yes | yes | - |
| news | News | 71 | yes* | yes* | blog |
| stories | Impact Stories | 72 | yes* | yes* | blog |
| contact | Contact | 80 | yes | yes | - |
| activities | Activities | 90 | yes | yes | - |
| events | Events | 91 | yes* | yes* | activities |
| gallery | Gallery | 92 | yes | yes | activities |
| privacy | Privacy Policy | 200 | no | yes | - |
| global, site_errors, site_newsletter, site_custom | site-wide copy (no URL, never in menus) | 9000-9030 | no | no | - |

\* **Shown only once real content exists.** The registry rule `nav.requires` hides these entries while the table is empty
(no active partner / public document / active event / published news post / consented story), so they appear by themselves as soon as
the owner adds the first record. They are therefore seeded "on" rather than "off". **Team** is seeded off because it contains only the
founder: switch it on in Admin > Menus when the team list is ready. The pages themselves stay reachable and editable at all times
(`/team`, `/partners`, `/reports`, `/events`, `/stories`, `/news`) and show friendly empty states.

---------------------------------------------------------------------------------------------------------------------

## 5. Media and rights

All six bundled photos are generic classroom/audience images: none is confirmed to be the foundation's own work and none is
labelled with a photographer or licence. Alt text describes only what is visible.

| File | Used for | Note |
|---|---|---|
| `photo-03.jpg` | program 4, gallery | young people with headphones at computers, a man pointing at a screen |
| `photo-04.jpg` | program 3, gallery | young people in red theatre-style seats at an event |
| `photo-05.jpg` | program 1, gallery | young people with laptops in a bright room |
| `photo-06.jpg` | program 2, gallery | young people at desks in a lecture room |
| `photo-02.jpg` | gallery, **inactive** | black-and-white photograph of a crowd of children. Needs proof of consent and rights |
| `photo-08.jpg` | gallery, **inactive** | black-and-white portrait of a child holding a sign that reads HOPE. Needs proof of consent and rights |
| `photo-hero-portrait.jpg` | not seeded | children at laptops in blue uniforms; carries a **third-party watermark strip** (global-impact-innovators.com). Replace it in Admin > Pages > Home before launch. Because of the watermark the seeded default social-sharing image (`og_image`) is the logo instead of this photo |
| `photo-hero-wide.jpg` | Home hero (seeded) | a plain composition crop of the portrait photo taken above the third-party strip, 1600 px wide, no retouching. The source is portrait, so on very wide screens it is soft: replace it with a landscape photograph the foundation owns (Admin > Pages > Home > Hero) |
| `photo-founder-tall.jpg` | not seeded as a team photo | portrait described on the live site as the founder; the brief asks for no photo claim, so Moses Komakech's profile has no photo yet |

**More photographs needed.** Only four photographs are cleared for use (`photo-03` to `photo-06`) and they are also the four program photos, so on Home the same pictures reappear in the stacks and the carousel. The gallery mosaic hides itself for that reason (it only shows gallery photos the carousel does not). Owner: please supply additional photographs (with consent and rights) and add them in Admin > Gallery and Admin > Pages > Home.

Images that exist on the live site but are **not bundled** (not downloaded): the Wii Got Quarry photos for the activity and the blog
post, the board portraits, program card photos, gallery photos and Unsplash/Pexels stock images. The seeded activity and posts
therefore have no cover image; upload real ones in the admin.

---------------------------------------------------------------------------------------------------------------------

## 6. Left out on purpose

* **"Add your promotional text..."** - a site-builder banner placeholder that appears at the top of every live page. Not copied.
* **Designed and maintained by NovaSoft Company Limited** - previous agency credit, replaced by the foundation's own `footer_credit`.
* **Template shop pages** in the live `sitemap.xml` (`classic-cap`, `face-serum`, `handmade-vase`, `hand-soap`, `set-of-plates`,
  `sunglasses`, `wooden-chair`, `wool-sweater`): leftover e-commerce template products with placeholder copy. Not real content.
* **`/impact1` ("Impact", not linked from the menu):** says "We are supporting over 20 vulnerable children" and "500 youth will gain
  crucial skills" for education support and ICT training. No evidence, contradicts the rest of the site, not seeded. If the
  foundation wants these figures it must supply and confirm them.
* **Live meta descriptions** (see flag 6) and keywords: not copied; the registry has accurate descriptions.
* **Subscribe/contact forms on Blog and Activities** (the same three-field enquiry form repeated on four pages): replaced by the
  one Contact form and the newsletter box.
* **A video thumbnail on the live home page** (YouTube `DpptGd72fwc`): handled by the home registry, not by the seed.
* **No invented data**: no statistics beyond the two live counters, no testimonials, partners, donors, reports, events, dates,
  addresses or job openings were created.

---------------------------------------------------------------------------------------------------------------------

## 7. Registry copy audit (information for the integrator)

Every default in `app/config/pages/*.php` was compared with the live text. All substantive wording matches the live site except:
interface/validation/empty-state messages (expected, structural), `home why_digital.video_title` (a YouTube video title), and the whole
`privacy` page (flag 12). The `global cta.title` ("Help us bridge the digital divide for youth in Northern Uganda") and `global
cta.text` are new sentences that paraphrase the live mission; keep or edit them in Admin > Pages > Site-wide content.

---------------------------------------------------------------------------------------------------------------------

## 8. Real data the owner must supply

| Item | Where to add it |
|---|---|
| Confirmed **registration number** | Site settings > Organisation |
| The remaining **board members**, staff, advisors, volunteers (names, roles, photos, consent) | Admin > Team |
| **Partners**, funders and collaborators (name, logo, website). The live site mentions partnerships with government, NGOs, schools and the private sector and a target of 10 partnerships, but names none | Admin > Partners |
| **Reports and documents** (annual report, financial statements, policies, newsletters) as PDF/Office files | Admin > Documents |
| **Events** (date, place, registration rules) | Admin > Events |
| **Impact stories** with written consent of the people featured | Admin > Impact stories |
| **News** posts and **press** coverage | Admin > Posts (type News / Press) |
| **Bank transfer details** (account name, number, bank, branch, SWIFT/IBAN) | Site settings > Donations |
| **Mobile-money details** (network, number, registered name) | Site settings > Donations |
| **PayPal** address, if used; confirmation of recurring donations (flag 5) | Site settings > Donations, Admin > FAQs |
| **Google Maps embed URL** for Laroo-Pece Division, Gulu City | Site settings > Contact details |
| **Real photographs** with rights and consent (activity photos, programs, founder, team) and replacement of the watermarked hero | Admin > Pages (images), Gallery, Programs, Projects |
| Date and photos for the Wii Got Quarry **activity**; cover image for its blog post | Admin > Activities, Posts |
| Decision on the draft "Breaking the Stigma" post | Admin > Posts |
| One official **vision** and **mission** wording; official title of the founder | Admin > Pages > About / Home |
| What the **"5 Active Programs"** and **"150+ Youth Trained"** counters on Home should say now that the programs are described as planned (see section 3a), and evidence for them | Admin > Impact figures |
| **Google Analytics** id and **YouTube** channel, if wanted | Site settings |
| Legal review of the **privacy notice**, terms and any donation/tax statement | Admin > Pages > Privacy |
| Official **notification email** if different from the Gmail address | Site settings > Contact details |
