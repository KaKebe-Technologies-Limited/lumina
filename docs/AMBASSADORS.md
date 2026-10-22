# Ambassadors

A call for people anywhere in the world to become **Lumina Africa Foundation ambassadors**: they raise funds, lobby for the foundation, and pledge a yearly amount of
money and / or in-kind support (computers, materials ...). The page is a child of Involvement: `/involvement/ambassadors` (menu: Involvement > Ambassadors).

## For visitors

The page is built from editable blocks: banner, highlights strip, what an ambassador is, what ambassadors do (four cards), **the yearly pledge** (money, in-kind, something else, with a
list of in-kind items), how it works (steps), what ambassadors receive, "anywhere in the world" (the eight regions), the list of approved ambassadors who agreed to be shown, the
**application form**, questions and answers, and the donate band. The Involvement page carries a banner that leads to it.

The application form asks for:

1. **About you**: name, e-mail, phone or WhatsApp, occupation, organisation, profile link.
2. **Where in the world**: a **region** (Africa, Asia, Middle East, Europe, North America, Central America and the Caribbean, South America, Oceania) and then a **country** (about 240
   countries and territories; the list follows the chosen region, and the server checks the country belongs to the region), city.
3. **How you could help**: raise funds, lobby companies and donors, collect computers and materials, introduce partners, spread the word, speak at events (at least one), and who they could reach.
4. **The yearly pledge**: tick **Money**, **In-kind items**, **Something else**, or a mix.
   * Money: the amount they could raise or give in a year and its **currency** (25 common currencies).
   * In-kind: tick the kinds of items (computers and laptops, tablets, projectors and routers, solar and power equipment, books, stationery, software licences, furniture) and describe quantity, condition and where they are.
   * Something else: free text for anything else they can lobby for or give (services, scholarships, venues, flights ...).
5. **A little more**: time they could give, and why they want to be an ambassador. Two tick boxes: consent to store the details (required) and permission to show their name and country once approved (optional).

The form is protected by CSRF, the honeypot and a throttle (15 submissions per 10 minutes per visitor). One open application per e-mail address (60 days). The applicant gets a confirmation e-mail with the
pledge as described; the notification address (Settings) gets the full application. A pledge is described as a good-faith intention, not a contract.

## For staff (Admin > Inbox > Ambassadors)

Needs the inbox ability (`inbox.manage`); deleting needs `inbox.delete`, the CSV export `export.csv`.

* List with status tabs (New, Under review, Approved, On hold, Declined), search, filters by region and pledge kind, order by date or by the largest amount; the menu shows a badge with the new ones.
* The detail page shows the whole pledge and the answers. **Decision and notes**: set the status, write private notes, and optionally e-mail the applicant when approving, declining or putting on hold (with an optional
  team message). Who decided and when is recorded; every change is in the audit trail.
* **Approved applicants who ticked "show my name and country"** appear under "Our ambassadors" on the public page; the number of countries represented is shown once there are approved ambassadors.
* CSV export of the filtered list (pledge amount and currency, items, notes, other, region, country ...).

## Editing the wording

Admin > Pages > Ambassadors holds every heading, card, step, pledge description, list of examples, form note, success message and FAQ. The default "what ambassadors receive" cards are proposals
(a letter of appointment, a briefing pack, a community of ambassadors, updates): edit them to match what the foundation really offers. Admin > Pages > Involvement has the banner that links to the page.

## Technical

Table `ambassador_applications` (additive, `database/schema.sql`); model `App\Models\AmbassadorApplication` (statuses, ways to help, in-kind kinds, pledge lines); data `App\Services\Web\Countries` (regions,
countries, currencies); public `Web\AmbassadorsController`, view `app/views/web/ambassadors/index.php`, `assets/js/ambassadors.js` (country list follows the region; pledge parts show when ticked; without JavaScript
everything is shown), styles `scss/pages/_ambassadors.scss`; admin `Admin\AmbassadorsController` with views in `app/views/admin/ambassadors/`; registry `app/config/pages/ambassadors.php` (page row in `database/seed.sql`).
While the table is missing (database update not imported) the page answers 404, the admin redirects with an explanation, and the menu entry stays hidden. Test: `bin/selftest-ambassadors.php`.
