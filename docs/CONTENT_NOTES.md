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
| membership | Membership | 52 | yes | yes | - |
| membership-terms | Membership terms (linked from the form only) | 53 | no | no | - |
| careers | Careers | 55 | yes | yes | involvement |
| donate | Donate (header button) | 60 | yes | yes | - |
| blog | Blog | 70 | yes | yes | - |
| news | News | 71 | yes* | yes* | blog |
| stories | Impact Stories | 72 | yes* | yes* | blog |
| contact | Contact | 80 | yes | yes | - |
| calendar | Calendar (under Contact) | 91 | yes | yes | contact |
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
| **Membership** (section 9): the fee of every type, the benefits, eligibility and terms wording, payment instructions, data-retention period, legal review | Admin > Membership types & fees, Pages > Membership / Membership terms, Site settings > Membership |
| Official **notification email** if different from the Gmail address | Site settings > Contact details |


---------------------------------------------------------------------------------------------------------------------

## 9. Membership registration: what is placeholder and what the owner must do

Nothing on the membership pages is invented: no fee, member count, benefit, testimonial or legal claim. Everything below is a **neutral, editable
default** until the owner confirms it. Technical contract: `docs/MEMBERSHIP.md`.

| Item | State in the starter | Owner action | Where |
|---|---|---|---|
| **Fees** | The three membership types ("Individual Member", "Youth & Student Member", "Organisation / Partner Member") are PLACEHOLDERS with no fee. The website, the form and the e-mails say "Fee to be announced". | Rename or delete the placeholders, set the fee (a set amount, free, or still to be announced), an optional joining fee, the billing period and an optional member limit for each type. A fee change affects new applications only. | Admin > Membership types & fees (admin role) |
| **Benefits** ("Why become a member") | Reuses the owner's own wording about volunteering, mentorship and partnership (from `planned-programs.md`). No other benefit is promised. | Confirm or replace with the foundation's real benefits. | Admin > Pages > Membership |
| **Eligibility** | A neutral placeholder sentence. | Write the real eligibility rules (age, residence, organisations). | Admin > Pages > Membership, and per type in Membership types & fees |
| **Membership terms** (`/membership/terms`) | A short placeholder, not a legal document. | Replace with the foundation's terms. **Have counsel review them.** | Admin > Pages > Membership terms |
| **Payment instructions** | Empty. The page then falls back to the bank / mobile-money details of the Donations tab, and if those are empty too the confirmation says the payment details will be shared after review. | Enter how members pay (mobile-money number and name, bank account, cash at the office). | Site settings > Membership (or Donations) |
| **Privacy notice**, section "Membership registration" | A DRAFT, flagged for review. The data-retention period is a placeholder. | Fill in the retention period; confirm with counsel whether Uganda's Data Protection and Privacy Act, 2019 requires the foundation to register with the Personal Data Protection Office or give further notices. The site makes no legal claim. | Admin > Pages > Privacy |
| **National ID / passport number** | Not collected (off, for data minimisation). | Decide whether it is really needed before switching it on. | Site settings > Membership |
| **Minors** | Applicants under 18 must give a parent or guardian's details and consent. | Confirm this matches the foundation's safeguarding policy. | Admin > Pages > Membership |
| **Newsletter box** | Ticking it subscribes the address (source `membership`). Newsletter double opt-in is not built (see section 3). | Decide whether that is acceptable. | - |
| **E-mails** | Sent through the configured mailer (`file` in development, PHP `mail()` or, since the newsletter feature, the optional `smtp` driver in production; see section 10). The approval, rejection and receipt e-mails are optional per action. | Test delivery on the live host; edit the wording. | Admin > Pages > Membership > E-mails |
| **Receipts** | Printable page with the foundation's details and registration number (see the flag on the registration number). | Confirm the registration number and the receipt wording. | Site settings > Organisation |
| **Opening** | Registration ships **closed** (`membership_open` = off): the page shows a "closed" notice and no personal data is collected. | Switch it on only after the fees are set, the terms replaced and the privacy section reviewed. | Site settings > Membership > Accept applications |

Real applications contain personal data (date of birth, address, phone numbers, guardian details). They are visible only to the admin and editor roles, never
public, never in logs or in the activity log (ids and references only), and the CSV export carries a warning. Deleting a member is admin-only and refused for
members who have a membership number or payments.

## 10. Newsletter: what is sample, what is placeholder, and what the owner must do

Nothing on the newsletter pages is invented: no issue count, subscriber number, statistic or testimonial. Technical contract: `docs/NEWSLETTER.md`.

| Item | State in the starter | Owner action | Where |
|---|---|---|---|
| **Sample issue** | ONE issue, titled "Sample issue - edit or delete me", saved as a **DRAFT** (so `/newsletter`, the archive, the API and the sitemap show nothing until a real issue is published). It is built only from wording that already exists: the planned-programs introduction (`docs/content-source/planned-programs.md`), the Wii Got Quarry activity text, the founder quote from the About page and two bundled photos with their existing captions. It has no issue number. | Edit it into the first real issue, or delete it. Do not publish it as it is. | Admin > Newsletter |
| **Public page** | Until the first issue is published `/newsletter` shows a friendly "no issue yet" page (not indexed). The Newsletter page is in the footer and, since the PDF/partners update, in the header menu under Blog > Newsletter (Admin > Menus can hide it again). | Publish the first issue; decide whether the footer link should stay. | Admin > Pages > Newsletter |
| **Page wording** (hero, subscribe box, privacy note, share row, e-mail fixed words) | Neutral editable copy. The privacy note under the subscribe box says the address is used only for news and updates and that every e-mail has a one-click unsubscribe link. | Review and edit the wording. | Admin > Pages > Newsletter |
| **E-mailing** | Off. The mail driver is `file` locally (messages are written to `storage/mail`) and PHP `mail()` in production, which is NOT suitable for sending to a list. An issue is e-mailed only from its E-mail page (admin role), only once it is published and "E-mail this issue to subscribers" is switched on, and only after the admin confirms the exact recipient count. | Create a mailbox at the host, put its SMTP login in the server `.env` (README, "Newsletter e-mail"), set the sender name, address, reply-to and footer text, send a test to yourself first. Optionally add the cron job. | Server `.env`; Site settings > Newsletter |
| **Sender identity and footer** | Sender name defaults to the site name, reply-to to the primary e-mail; the footer shows the organisation address from Site settings. | Check they are the foundation's real details. | Site settings > Newsletter and Organisation |
| **Consent and double opt-in** | Anyone who ticks the subscribe box (or the membership newsletter tick box) is subscribed at once; there is no confirmation e-mail (see section 3). Only `subscribed` addresses are mailed and the status is re-checked right before each message. | Decide whether single opt-in is acceptable; keep a record of how consent is given. | - |
| **Unsubscribing** | Every e-mail has a visible unsubscribe link and the `List-Unsubscribe` headers (including one-click, as Gmail and Outlook expect). A `mailto:` unsubscribe request lands in the foundation's inbox and must be handled by hand (Admin > Subscribers); there is no inbound mail processing. | Check that inbox regularly. | Admin > Subscribers |
| **Personal data** | A sending campaign records each address (admin only) so an issue is never sent twice to the same person. Addresses never appear in logs, the activity log or the public API. | Delete old issues you no longer need; mention newsletters in the privacy notice (retention period). | Admin > Pages > Privacy |
| **Legal** | The site makes no legal claim. Uganda's Data Protection and Privacy Act, 2019 may require registration with the Personal Data Protection Office, consent records and specific notices. | **Confirm with counsel** before the first mailing. | - |
| **Pictures** | Newsletter pictures are uploaded by the admin or editor into `/uploads`; the editor asks for a description (alt text) of every picture. The sample issue uses only pictures the site already shows. | Make sure you have the right to publish every photo you add (see section 5). | Admin > Newsletter |

## 11. Calendar and programme scheduler: what is seeded, what is not, and what the owner must do

Technical contract and sources: `docs/CALENDAR.md`. Nothing here is invented: **no Lumina programme session, event, meeting or deadline is seeded**; the owner adds those. Only public, verifiable dates are pre-filled, each with its source name (and a link where the page was verified).

| Topic | What ships | What the owner must do | Where |
|---|---|---|---|
| **Uganda public holidays** (14) | New Year's Day, NRM Liberation Day (26 Jan), Archbishop Janani Luwum Day (16 Feb), International Women's Day (8 Mar), Good Friday, Easter Monday, Labour Day (1 May), Uganda Martyrs' Day (3 Jun), National Heroes' Day (9 Jun), Eid al-Fitr, Eid al-Adha, Independence Day (9 Oct), Christmas Day, Boxing Day. Fixed days repeat every year; Good Friday and Easter Monday are calculated from Western Easter. The list was cross-checked against several sources; the text of the Public Holidays Act itself could not be opened (see `docs/CALENDAR.md` section 9). | Check the list against the current official notice for each year. | Admin > Calendar > Holidays & days |
| **Eid al-Fitr and Eid al-Adha** | Calculated with a tabular (arithmetical) calendar, which is only a prediction: the real day follows the moon sighting and the announcement, and can differ by one day (for example 2025: 31 and 30 March). Every Eid date therefore shows **"Date to be confirmed"** on the website and in the downloadable calendar file. | When the day is announced, press **Confirm** (or enter the announced date) on Holidays & days. The label disappears for that year only. The sidebar badge and the dashboard card list the dates that still need confirming. | Admin > Calendar > Holidays & days |
| **Extra public holidays** | Not seeded. The President can declare additional holidays at short notice (for example for an election or a swearing-in). | Add each one as a normal date of the type "Uganda public holiday". | Admin > Calendar > Add a date |
| **Holidays that fall on a Sunday** | The calendar shows the **statutory date only**. No source confirmed an automatic move to the Monday, so none is shown. | If the government announces a substitute day, add it (or move that year's date). | Admin > Calendar |
| **International and UN days** (46) | Days from the UN list of observances (for example Refugee Day, Humanitarian Day, Human Rights Day, Day of the Girl Child), WHO health days, UNESCO and ITU days, plus Africa Day, Day of the African Child and the Red Cross and Red Crescent day. Safer Internet Day (2nd Tuesday of February) and Girls in ICT Day (4th Thursday of April) move with the weekday. Each entry carries its source name; links that could not be verified are left empty. | Hide the days that do not suit the foundation (they are hidden, never deleted), add links, and add days that matter to your programs. | Admin > Calendar > Holidays & days, or edit the entry |
| **Programme sessions, events, meetings, deadlines** | None. Events entered under Admin > Events appear in the calendar automatically (the Events screen stays the place where they are edited). | Add the real schedule: one date, a range, or a repeating date (daily, weekly, monthly, yearly) with an "until" date; link it to a program or project if you like. A CSV file can add up to 500 dates at once (template in Import / export). | Admin > Calendar > Add a date |
| **Wording** | Page title, introduction, filters, the "dates may change" disclaimer and every label are editable. | Review the disclaimer and the Contact block wording. | Admin > Pages > Calendar and Admin > Pages > Contact |
| **Where it appears** | `/calendar`, a compact month and the next dates on the Contact page under the form, the main menu under Contact, the sitemap (one page per public date) and a file to add any date or the whole calendar to a phone or computer calendar. | Switch the Contact block off or change the week start (Monday, Sunday or Saturday) under Calendar settings if you prefer. | Admin > Calendar > Settings (admin role) |
| **Time zone** | All times are East Africa Time (Kampala, UTC+3, no daylight saving). | Nothing. | - |
| **Personal data** | None is collected from visitors. Staff names are never shown on the public calendar. | Nothing. | - |

## 12. Meeting scheduler: what is seeded, what is not, and what the owner must do

Technical contract: `docs/MEETINGS.md`. Nothing here is invented: **no meeting, attendee or meeting request is seeded**; the owner creates them. Only the empty tables, five settings and the wording of the invitee page and e-mails ship.

| Topic | What ships | What the owner must do | Where |
|---|---|---|---|
| **Meetings, invitees, requests** | Nothing. No sample meeting, no invented person. | Create the first meeting, add the real invitees, send yourself a test invitation first. | Admin > Calendar & schedule > Meetings |
| **Video links** | The site cannot create Zoom, Google Meet or Microsoft Teams rooms (no vendor accounts or APIs are connected). The organiser pastes the link. The one built-in exception is the button "Generate a free private video room": a link on **Jitsi Meet (meet.jit.si)**, a free service run by someone else (8x8); nothing is created until somebody opens the link, and anyone with the link can join. | Create the Zoom / Meet / Teams link in that service and paste it, or decide whether the Jitsi option is acceptable for the foundation. | Schedule form |
| **E-mail sending** | Invitations, updates, cancellations and reminders reuse the newsletter mail login and the sender set under Site settings > Newsletter. The organiser's address is only the Reply-To (never the From address). Each person gets a separate e-mail; nobody sees who else is invited. | **Set `MAIL_DRIVER=smtp` and the `MAIL_*` login in the live `.env`** (invitations with a calendar file are unreliable through PHP `mail()`), confirm the sender address belongs to your domain (SPF / DKIM), then send yourself a test. | `.env`, Site settings > Newsletter |
| **Reminders (24 h and 1 h before)** | Off in the database by default; the schedule form ticks both. They are sent by a cron job. | Add the cron job `0-59/5 * * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/meetings-send.php` (every 5 minutes, also needs `APP_URL` in `.env`). Without it, reminders go out only when somebody presses "Send reminder now". | hPanel > Advanced > Cron jobs |
| **Public calendar** | A meeting is private by default. A **public** meeting shows only the type "Meeting / outreach", the title, date and time on `/calendar`, the feed and the .ics files; the join link is hidden unless "also show the join link publicly" is ticked (Zoom-bombing risk: strangers can join). Drafts, private and cancelled meetings never appear on a public page, in the sitemap or in a feed. | Keep meetings private unless they are open to everybody. | Schedule form > Who can see it |
| **Personal data and privacy** | Invitee and request e-mail addresses are visible to administrators and editors only, never in a public page, log or audit entry. Each invitee has a private 48-character link that stops working if they are removed. Erasing a member's data also clears their meeting attendee and request rows. | **Confirm the lawful basis** for using members' e-mail addresses to invite them (Uganda Data Protection and Privacy Act, 2019: possible registration with the Personal Data Protection Office, consent records) and **update the privacy notice and the request-form consent line with counsel**. The site adds no legal text and makes no legal claim. | Admin > Pages > Privacy, Admin > Pages > Contact > Request a meeting |
| **Contact "Request a meeting"** | A form on the Contact page (name, e-mail, topic, message, preferred day and time, consent tick box, anti-spam honeypot, 5 requests per hour per visitor). It sends an acknowledgement to the visitor and a notification to the organisation address. Nothing is booked automatically. | Check the wording and the topic list; answer requests from the inbox ("Schedule this meeting" pre-fills the form; a decline can carry a short message). The form can be switched off. | Admin > Pages > Contact, Site settings > Meetings |
| **Wording** | Invitee page and all e-mails (subject and body, with `{title}`, `{when}`, `{organiser}` placeholders) are editable. | Read them once and adjust the tone. | Admin > Pages > Meetings |
| **Time zone** | All times are East Africa Time (Kampala, UTC+3). Calendar apps convert to each person's own time zone. | Nothing. | - |

## 13. Partners section and PDF newsletters: what is placeholder and what the owner must do

Nothing is invented: no partner and no issue is seeded (the only issue is still the draft sample of section 10). Technical contract: `docs/PARTNERS_NEWSLETTER.md`.

| Item | What ships | What the owner does | Where |
|---|---|---|---|
| **Partners list** | Empty. The About "Our partners" section prints nothing at all until a partner is active; the Partners page and the footer strip show their empty states. | Add each partner: name, logo, website, type (Partner, Funder, Donor, Collaborator). Do not list an organisation without its permission to use its name and logo. | Admin > Partners |
| **Logo files** | Trimmed, resized to fit a 480 x 240 box, stored in PNG (JPEG for a heavy opaque logo) plus WebP, with a 2x copy only when the original has the pixels. Metadata is removed. | Upload the largest clean logo you have (a PNG with a transparent background is best, at least 400 px wide). Logos under about 320 x 160 px of content look soft: the admin warns. A **white-on-transparent logo is invisible on the light boxes**: use its colour or dark version. | Admin > Partners |
| **Image library (GD)** | The code uses PHP `gd` when it is present and falls back to storing the file as uploaded when it is not (the admin shows a notice). It could not be confirmed here whether the live host has GD. | Check Admin > Partners after the first upload: if it says GD is not installed, ask the host to enable the `gd` extension, then press "Optimise existing logos". | Hosting panel |
| **Section wording** | "Our Partners" title, intro sentence from the live site, and a "See all partners" link; each can be edited or the section switched off. | Review the wording. | Admin > Pages > About > Our partners |
| **PDF issues** | PDFs are stored privately (`storage/private/newsletters`), never reachable by a direct file URL, and served only while the issue is published. The visitor page offers an embedded viewer (not on phones), Download PDF, Read in a new tab, size and an optional download counter. The e-mail for a PDF issue carries a link, never an attachment. | Keep PDFs reasonably small (use "Compress" when exporting). Check the upload limit shown on the PDF box; raise `upload_max_filesize` / `post_max_size` at the host if you need larger files. | Admin > Newsletter, Site settings > Newsletter |
| **Where visitors find it** | Header menu Blog > Newsletter, footer link, Home "Latest newsletter" card, Blog page call-to-action; the cards hide themselves while no issue is public or the Newsletter page is off. | Edit or hide the wording of the Home and Blog cards if you prefer. | Admin > Pages > Home / Blog / Newsletter |
| **"I published it but cannot see it"** | The "Live on the website" box in every issue says exactly why: still a draft, scheduled for later, archived, missing a required field, or the Newsletter page switched off. | Publish the issue (the Save button does not publish by itself) and make sure Admin > Pages > Newsletter is published. | Admin > Newsletter |
| **Download counter** | Counts real downloads; crawlers, HEAD requests and resumed downloads are not counted. | Hide the counter on the page if you do not want it. | Admin > Pages > Newsletter |

## 14. Forms (form builder, public forms, answers): what ships, what is placeholder and what the owner must do

Nothing is invented and nothing is published: no form exists until the owner creates one. Technical contract: `docs/FORMS.md`.

| Item | What ships | What the owner does | Where |
|---|---|---|---|
| **Templates** | Nine neutral starting points (job application, volunteer registration, event RSVP, contact, partnership enquiry, feedback survey, scholarship application, beneficiary intake, training registration). They are generic wording written for this build, not foundation policy. | Create a form from the closest template and edit every question, help text and message. Delete questions you do not need: ask only what you will really use (data minimisation). | Admin > Forms > New form |
| **Public wording** | All fixed words (buttons, errors, closed / full / not-open notices, thank-you page, e-mails, job wording) are in one editable page. | Review the wording once, especially the closed / full notices and the e-mails. | Admin > Pages > Forms |
| **Consent and privacy text** | A consent tick box with a default sentence and a link to the privacy page; each form can carry its own text and a short privacy note. The sentences are **placeholders, not legal text**. | Have the consent wording, the privacy note and the retention period of every form confirmed by the foundation's legal adviser against **Uganda's Data Protection and Privacy Act, 2019** (lawful basis, purpose, who may see the answers, how long they are kept, rights to correction and deletion). Intake forms may hold sensitive data about children and vulnerable people: be stricter. | Form settings > Consent and spam protection, Admin > Pages > Forms |
| **Retention** | Default is "keep until deleted" (the builder warns while a form that collects personal data has no period). Site settings > Forms has a default for NEW forms. Deletion is permanent. | Set a retention period on each form (for example 180 days for job applications) and add the daily cron job from the README. | Form settings > Privacy and search, Site settings > Forms |
| **Who sees the answers** | Administrators and Editors can build forms and read answers; only Administrators export CSV or delete. Custom roles get four separate abilities (build, read answers, export, delete) to tick. | Give "read answers" only to people who need it; keep export and delete for few people. | Admin > Roles |
| **E-mails** | Staff get a link only (no answers) by default; the confirmation goes only to the address typed in the form and only when switched on per form. Both use the mail login in `.env`. | Enter the staff addresses on each form (or once in Site settings > Forms). Check SMTP works before relying on notifications. Nothing is queued: a submission is always saved even if the mail fails. | Form settings > E-mails |
| **Job vacancies** | A job can name a form as its application form; the job page then shows an Apply button to that form. Jobs without one keep the built-in application form. Applications show "Applying for: ..." and the Hired label. | Attach the published job application form to each vacancy that should use it. | Admin > Jobs > Application form |
| **Files** | PDF, Word, Excel, JPG and PNG up to the lowest of the form limit, Site settings > Forms and the server limit (default 5 MB). Stored privately, content-checked, pictures stripped of location data, downloaded only by signed-in staff. | Check the effective upload limit with the host; tell applicants which files to send. | Site settings > Forms |
| **Public list and embedding** | `/forms` (a public list) is OFF. A form is reached by its own link, a button, or `[form slug="..."]` inside a page. Job and intake forms are not indexed by search engines by default. | Switch the list on only if you want it, and tick "Show in the public list" per form. | Site settings > Forms |
| **Spam protection** | No CAPTCHA: hidden trap field, minimum fill time, flood limits, one-per-address option, link limit in strict mode. | Raise the level to Strict on a form that gets junk; leave Standard otherwise. | Form settings > Consent and spam protection |

## 15. Shop (jumpers, caps, T-shirts, stickers, payments): what ships, what is placeholder and what the owner must do

Technical contract: `docs/SHOP.md`. The owner asked for a shop selling branded items to raise funds, with payment options (mobile money, Visa, or booking) configured in the admin, and chose **Flutterwave** as the online gateway. Nothing about money is invented and nothing is live.

| Item | What ships | What the owner does | Where |
|---|---|---|---|
| **Shop status** | The shop is **closed** (visitors see the products but cannot order) and Flutterwave is **off**. | Review everything below, then switch "The shop takes orders" on. | Admin > Shop > Shop settings |
| **Sample products** | Six products (Mission Hoodie 85,000, Lumina Crew Jumper 70,000, Lumina Cap 25,000, two T-shirts 35,000, Sticker Pack 10,000) in five categories, flagged "Sample product - edit or delete". **Names, prices (UGX), descriptions and stock numbers are made up** to show how the shop works; the pictures are flat illustrations drawn for this site (not photographs) using the logo patch. They are inserted once: a deleted sample never comes back. Sample pages are kept out of the sitemap and search results and carry no price data for search engines. | Replace every name, price, text and photo with the real item (upload photos; tick "This is now my real product"), delete the rest. | Admin > Shop > Products |
| **Prices and currency** | Placeholders in UGX. No price appears anywhere else as a fact (the wording pages contain none). | Set real prices; change the currency only with care (prices are not converted). | Products, Payments |
| **Collection, delivery, fees** | Collection from the organisation address (Site settings); delivery switched OFF; fee 0. | Confirm the collection address and hours; decide whether to deliver, the fee, the free-delivery level and the areas. | Shop settings |
| **Ways to pay** | Five methods: Book now pay later (ON), Pay on pickup or delivery (ON), Mobile money (OFF, no number), Bank transfer (OFF, no account), Pay online with Flutterwave (OFF, no keys). A method is only offered when it is on AND complete. | Enter the real MTN / Airtel numbers and account names, the bank details, and decide which methods to offer. **No number, account or key is invented.** | Admin > Shop > Payments |
| **Flutterwave** | Written from Flutterwave's v3 documentation (hosted payment page, verification by reference, webhook with `verif-hash`) and tested **only against a local practice server** (`bin/dev/mock-flutterwave.php`). Two details are assumptions to confirm: the "Test connection" check treats a normal "no transaction found" answer as "key accepted", and webhooks are authenticated by the `verif-hash` header only. | Create the account, use **TEST** keys and Flutterwave's test card / test mobile-money numbers, place test orders, check they turn Paid, then go LIVE. Keep `APP_KEY` stable. | Payments, `docs/SHOP.md` section 6 |
| **Manual payments** | Mobile-money and bank orders stay "waiting" until a person checks the statement and marks the order paid (the order screen lists the checks). | Decide who checks, how often, and which e-mails get the staff notice. | Orders |
| **Refunds** | The shop never moves money. When an order that holds money is cancelled (or a payment arrives after the cancel) its page shows "Record the refund": return the money yourself (Flutterwave dashboard, mobile money or bank), then record it so the books and the sales numbers match. Revenue on the overview never counts cancelled or refunded orders. | Decide who returns money and how fast; the cancelled-order e-mail promises that you will contact the customer. | Orders |
| **Online order e-mail** | An online (Flutterwave) order sends the customer a short "complete your payment" e-mail with the private order link, so someone who closes the payment page can come back and press Pay again; the receipt and the staff notice follow when the payment is confirmed. At most 3 "order received" e-mails a day go to one address. | Read the wording in Admin > Pages > Shop > E-mail wording. | Admin > Pages > Shop |
| **Policy texts** | Neutral **drafts**: terms of sale, delivery and collection, returns and exchanges (page `/shop/terms`), plus a "Shop orders" paragraph in the privacy notice. Not legal advice. | Have them read, corrected and approved (ideally by a legal adviser) before opening; type your own text in Shop settings. | Shop settings, Admin > Pages > Shop and Privacy |
| **Wording** | All headings, buttons, messages and e-mails are in the page "Shop" (hundreds of editable fields). The FAQ and the "why we sell" strip say only that purchases support the programmes: no amount, percentage or promise about the use of the money. | Add figures only when confirmed. | Admin > Pages > Shop |
| **E-mails** | Order received, payment received, status updates (customer); new order, payment arrived, customer typed a payment reference (staff). Never any card or account detail. They need the `.env` mail login. | Check the sender and the recipients. | Shop settings, `.env` |
| **Personal data** | An order holds the buyer's name, e-mail, phone and delivery address, reachable only through a private 40-character link and by staff with "See and handle shop orders". Order pages are not indexed and never cached. There is **no automatic retention period** and no customer account. | Agree a retention period with a legal adviser and delete old orders by hand (Admin > Shop > Orders, administrators only). | `docs/SHOP.md` section 15 |
| **Not built** | Stock per colour-and-size COMBINATION, discount codes, tax lines, partial refunds, several currencies, customer accounts. | Ask if needed. | |

