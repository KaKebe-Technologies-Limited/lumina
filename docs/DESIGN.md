# Lumina Africa Foundation - DESIGN SYSTEM (public site)

> **Page agents: read THIS file instead of the reference image.** It contains the tokens, the exact markup of every component,
> the JS hooks, and a section-by-section recipe for the 12 sections of the reference layout. Everything below already exists in
> `scss/`, `app/views/partials/`, `app/helpers_design.php` and `assets/js/main.js` - build pages by composing it. Do not edit the
> shared files (see "Who owns what"); list gaps in your report.

The layout language is taken from a commercial charity theme (layout and visual language only - none of its photos, copy, figures or
branding are used). Lumina keeps its own brand: navy, gold and orange from the logo patch, the foundation's own photographs and wording.

---------------------------------------------------------------------------------------------------------------------

## 1. Principles

1. **Photography-led, lots of whitespace.** Big rounded photos, generous section padding (4 rem phone / 6 rem desktop).
2. **Very large corners.** Cards 24 px, photos 28 px, deep panels 40 px, the "sheet" that overlaps a photo 56 px (32 px on phones). Buttons are pills.
3. **Serif display headings + clean sans body.** Fraunces for h1-h3, big numerals and quotes; DM Sans for body; Poppins for UI labels, buttons, h4-h6.
4. **Alternate pale tints with deep navy bands.** White -> cream (`section--tint`) -> pale navy (`section--cool`) -> navy photo band. One orange call-to-action colour.
5. **Thin 1 px borders, subtle shadows, small-caps eyebrows with a short gold underline.**
6. **Rounded-top "sheet" overlaps** between hero and the first section (and in every inner page hero).
7. **Nothing invented.** Components that *can* show figures, ratings, partners, stories or progress are driven ONLY by real data (tables / registry) and **render nothing when empty**. There are no star ratings, no progress rings, no raised/goal numbers, no fake counters.
8. **Everything editable.** Every heading, paragraph, button, image (+ alt), list and optional section is declared in `app/config/pages/<slug>.php`.
9. **Accessible by default.** AA contrast (section 5), visible focus, one `<h1>`, reduced motion respected, keyboard-operable carousel/drawer/dialog.

### Reference -> Lumina brand mapping (decided)

| Reference | Lumina |
|---|---|
| deep green (bands, stats card, donate panel, footer) | **navy** `$navy #2C3950` (stats card, donate panel, bands) and `$navy-dark #172036` (footer, photo overlays) |
| yellow (top bar, underline stroke, numerals, subscribe) | **gold** `$gold #D6A151` |
| orange CTA | **`$cta #B85A14`** (white text 4.66:1; hover `$cta-hover #9A470C`) |
| pale green tints | cream `$surface #F7F3EC` and pale navy `$tint-cool #EEF1F6` |
| display serif | **Fraunces** (Google Fonts 500/600/700, no optical-size axis) |
| "Campaign" cards with progress ring | **Programs & projects** cards with a round icon chip (no figures) |
| "Feedback & reviews" with stars | **Impact stories** (consent-confirmed rows only, no stars) |
| donation panel | pledge / redirect panel (not a payment form) |

---------------------------------------------------------------------------------------------------------------------

## 2. Tokens

Sass variables live in `scss/abstracts/_tokens.scss` (also available to every partial); CSS custom properties are exported in `scss/main.scss`.

### Colour
| Sass | CSS var | Value | Use |
|---|---|---|---|
| `$navy` | `--navy` | `#2C3950` | headings, deep bands, stat card, donate panel, primary button |
| `$navy-dark` | `--navy-dark` | `#172036` | footer, photo overlays, text on gold |
| `$navy-light` | `--navy-light` | `#3C4E6E` | soft glows on navy |
| `$gold` | `--gold` | `#D6A151` | top bar, underline stroke, stat numerals, eyebrow bar, subscribe button. Text on navy only |
| `$gold-text` | - | `#8A5A06` | gold-looking TEXT on white/cream |
| `$cta` / `$cta-hover` | `--cta` / `--cta-hover` | `#B85A14` / `#9A470C` | the orange pill (white text) |
| `$orange-text` | `--orange-text` | `#A64B0C` | orange TEXT / links on white, cream, pale navy |
| `$orange-accent` | `--orange-accent` | `#E07341` | decorative / navy-dark text on it (legacy `.btn--accent` now renders as `$cta`) |
| `$surface` | `--tint` | `#F7F3EC` | cream section background (`.section--tint`) |
| `$tint-cool` | `--tint-cool` | `#EEF1F6` | pale navy tint: quote cards, mission/vision boxes, icon discs (`.section--cool`) |
| `$ink` / `$ink-soft` | `--ink` / `--ink-soft` | `#1D1E20` / `#5A6470` | body / secondary text |
| `$line` | `--line` | `#E4E1DA` | 1 px borders on white |
| `$border`, `$border-strong` | `--border` | `#E3DDD2`, `#CFC7B8` | form borders |

### Type
| Token | Value |
|---|---|
| `$font-display` / `--font-display` | `'Fraunces', 'Iowan Old Style', Palatino, Georgia, serif` (h1-h3, `.stat-band__value`, card titles, quotes) |
| `$font-heading` / `--font-heading` | Poppins (buttons, labels, nav, h4-h6) |
| `$font-body` | DM Sans (requested as `ital,wght@0,400;0,500;0,600;0,700;1,400`: no `opsz` axis, about 40 KB lighter) |
| Fallback faces | `scss/base/_fonts.scss` defines metric-matched `DM Sans Fallback`, `Poppins Fallback`, `Poppins Caps Fallback` (local Arial) and `Fraunces Fallback` (local Georgia) with `size-adjust` / `ascent-override` / `descent-override`. They sit second in `$font-body`, `$font-heading`, `$font-display`; `$font-heading-caps` (eyebrows, `.btn--caps`) uses the capitals variant. The web-font swap no longer shifts the layout (measured: above-the-fold boxes identical with and without the web fonts). If you change a Google Fonts weight/axis, re-measure and update the numbers in that file. |
| h1 | `clamp(2.4rem, 1.5rem + 3.2vw, 4rem)`; hero-photo title up to 4.5 rem; h2 `1.9 -> 3 rem`; h3 `1.3 -> 1.7 rem` |
| `$fs-xs .. $fs-4xl` | 0.8125, 0.9375, 1.0625 (body), 1.1875, 1.375, 1.75, 2.25, 3, 3.75 rem |

### Shape, shadow, motion, layout
| Token | Value | Use |
|---|---|---|
| `$radius-card` / `--radius-card` | 24 px | cards, tint boxes, accordion |
| `$radius-photo` / `--radius-photo` | 28 px | photographs |
| `$radius-sheet` / `--radius-sheet` | 56 px (CSS var is 32 px below `md`) | sheet / page-hero cut-in / footer top |
| `$radius-panel` / `--radius-panel` | 40 px | stat card, donate panel, cta band |
| `$radius-pill` | 999 px | buttons, chips, inputs |
| `$shadow-card` | `0 10px 30px rgba(navy-dark,.08)...` | card hover, photos |
| `$shadow`, `$shadow-lg` | existing | popovers, panels |
| `$container` 1180 px, `$container-narrow` 760, `$container-wide` 1360 | | `.container`, `--narrow`, `--wide` |
| `$gutter` | 1.25 rem phones, 2 rem from md | container side padding |
| `$header-h` 72 / `$header-h-lg` 88 / `$header-h-shrunk` 64 px, `$topbar-h` 38 px | | header |
| z-index | `$z-header 100`, `$z-overlay 200`, `$z-modal 300`, `$z-toast 400` | the drawer lives inside the header context |
| Breakpoints (mobile-first) | `sm 576, md 768, lg 992, xl 1200, nav 1280, 2xl 1400` | `@include respond(md) { ... }` |

Spacing scale `$space-1..10` = 0.25, 0.5, 0.75, 1, 1.5, 2, 3, 4, 6, 8 rem. Section padding: `$space-8` (4 rem) phone, `$space-9` (6 rem) from `lg`; `.section--spacious` adds more; `.section--sm` less.

---------------------------------------------------------------------------------------------------------------------

## 3. Contrast (WCAG 2.1 AA) - every pairing used by the system

Computed from the actual hex values (`overlay` rows = worst case: the photo behind the veil is pure white).

| Pairing | Ratio | Needs | Result |
|---|---|---|---|
| ink `#1D1E20` on white | 16.68 | 4.5 | pass |
| navy on white (headings) | 11.61 | 4.5 | pass |
| ink-soft `#5A6470` on white | 6.01 | 4.5 | pass |
| orange-text `#A64B0C` on white (links) | 5.78 | 4.5 | pass |
| gold-text `#8A5A06` on white | 5.92 | 4.5 | pass |
| navy on cream `#F7F3EC` | 10.50 | 4.5 | pass |
| ink-soft on cream | 5.44 | 4.5 | pass |
| orange-text on cream | 5.23 | 4.5 | pass |
| gold-text on cream | 5.35 | 4.5 | pass |
| ink on pale navy `#EEF1F6` | 14.73 | 4.5 | pass |
| navy on pale navy | 10.26 | 4.5 | pass |
| ink-soft on pale navy | 5.31 | 4.5 | pass |
| orange-text on pale navy | 5.11 | 4.5 | pass |
| white on navy | 11.61 | 4.5 | pass |
| white 92 % on navy (lead text) | 10.09 | 4.5 | pass |
| gold on navy (eyebrow, numerals) | 5.02 | 4.5 | pass |
| white on navy-dark | 16.20 | 4.5 | pass |
| white 84 % on navy-dark (footer text) | 11.71 | 4.5 | pass |
| gold on navy-dark | 7.00 | 4.5 | pass |
| navy-dark on gold (top bar, gold button) | 7.00 | 4.5 | pass |
| navy on gold (open accordion icon) | 5.02 | 3 (graphic) | pass |
| white on `$cta` (orange pill) | 4.66 | 4.5 | pass |
| white on `$cta-hover` | 6.40 | 4.5 | pass |
| orange-text on gold 14 % tint (active drawer link) | 5.20 | 4.5 | pass |
| gold chip icon on navy | 5.02 | 3 (graphic) | pass |
| white on 62 % navy-dark veil over white | 4.62 | 4.5 | pass |
| white on 66 % veil (hero-photo desktop, where the copy is) | 5.26 | 4.5 | pass |
| white on 70 % veil (hero-photo phone/tablet, band) | 6.01 | 4.5 | pass |
| quote-mark `$cta` on pale navy (graphic) | 4.11 | 3 | pass |

Rules: never put `$gold` text on white; never use `$orange` / `$gold-deep` for small text on white (use `$orange-text` / `$gold-text`); white text on a photo needs the veil
(hero-photo, band and page-hero add it); link colour is `$orange-text` everywhere.

**Keyboard focus ring (WCAG 1.4.11 / 2.4.7 / 2.4.11):** the global ring is `$orange-dark` (4.5-5.0:1 on white and cream). On navy surfaces it is only 1.8-2.4:1, so `scss/base/_a11y.scss` switches it to gold (`$gold` 5.0:1 on navy, 7.0:1 on navy-dark) inside `.section--navy/--dark`, `.sheet--navy`, `.hero-photo`, `.page-hero`, `.band`, `.stat-band__card`, `.donate-panel`, `.cta`, `.quote-card--dark`, `.event-facts`, `.subscribe-box` and `.site-footer`. A NEW navy component must be added to that list. Components that clip overflow (the accordion item) draw the ring inside (`outline-offset: -4px`). `html` has `scroll-padding-top` (header + 1 rem) so the sticky header never covers a focused element.

**White text on photos:** below `lg` every `.page-hero--image` uses an even navy-dark veil (.82 -> .76) because the copy spans the full width; the left-to-right fade starts at `lg`. Gallery captions fade to a .82 veil by 38 % of their height (white 13 px text >= 4.5:1 over bright patches).

---------------------------------------------------------------------------------------------------------------------

## 4. Responsive rules

* Mobile-first. Content is one column below `md` (768). Splits go two columns at `md`; 3-column grids at `lg`.
* The header menu is inline from `nav` (1280 px). Below it: logo + (Donate pill from `md`) + hamburger -> off-canvas drawer. Keep `main.js` (`min-width: 1280px`) in step with `$breakpoints.nav`.
* Sheet radius: 32 px on phones, 56 px from `md` (CSS var `--radius-sheet`).
* `.site-main` has `overflow-x: clip`: rotated photo stacks and carousels can never create a horizontal scrollbar. There must be **no horizontal scroll at 375 px** - test every page.
* Stat card: 1 / 3 items stack on phones, 2 / 4 items sit in a 2-column grid; one row with thin vertical dividers from `md`.
* Carousel slides: 86 % (max 22 rem) on phones, 2 per view from `md`, 3 per view from `lg`.
* Anchors (`#pledge`) land below the sticky header (`.section[id]` has a scroll margin).
* Images: always `alt` (empty `alt=""` only for purely decorative), `loading="lazy"` except the hero (`fetchpriority="high"`). Never crop faces: use `object-position` via the `focus` variable (`'50% 20%'`) on hero/band/page-hero.

---------------------------------------------------------------------------------------------------------------------

## 5. Helpers (`app/helpers_design.php`, auto-loaded at the end of `app/helpers.php`)

| Function | Purpose |
|---|---|
| `highlight(?string $text, string $class = 'hl'): string` | **Accent-stroke convention.** Wrap ONE phrase in `[[double brackets]]` (preferred) or `*stars*` in any CMS *text* field: `Together, we [[change lives]]`. The text is HTML-escaped first, only then are the markers turned into `<span class="hl">`, so an editor can never inject markup. `[[ ]]` wins over `*`; unbalanced markers stay as typed. Returns HTML: `<h1><?= highlight(content('home','hero.title')) ?></h1>`. All heading partials (`hero-photo`, `page-hero`, `section-head`, `band`, `donate-panel`, `carousel`) already call it. |
| `strip_highlight(?string $text): string` | Same text without markers (plain, NOT escaped): `e(strip_highlight($t))` for `aria-label`, alt text, `<title>`. |
| `youtube_id(?string $url): string` | 11-character id from any youtube.com / youtu.be / nocookie URL or a bare id; `''` otherwise. |
| `photo_tag(array $img, string $class = '', array $attrs = []): string` | `<img>` from a `content_img()` result (`''` when there is no image). Defaults `loading=lazy decoding=async`. |
| `icon_options(): array` | `[icon name => label]` curated list for registry `select` fields (`'options' => icon_options()`). |
| `safe_icon(?string $name, string $fallback = 'check-circle')` | known icon name or fallback (the partials call it). |
| `donate_prefill(): array` | `['amount' => '', 'currency' => '']` validated from `?amount=&currency=`: the donate page must use it to prefill its pledge form (see section 12, recipe 9). |

**Registry hint for highlight:** say it in the field help: `'help' => 'Put [[double brackets]] around the words that get the hand-drawn underline.'`

---------------------------------------------------------------------------------------------------------------------

## 6. Global chrome (already built - do not rebuild, do not edit)

| Piece | File | Editable in Admin > Pages > "Site-wide content" (registry `global`) |
|---|---|---|
| Top bar | `partials/topbar.php` | section **Top bar** (on/off, show address / e-mail / phone, quick link = WhatsApp / Donate page / none + its text). Data: Settings > Contact. Mobile shows phone + quick link only (the clock takes its own row above them when they do not fit side by side). Also holds the live **clock** (section **Date & time clock**: `clock.enabled / mode / timezone / hour12 / show_seconds / show_date / label / aria_label`; `App\Core\SiteClock` renders the first paint, `[data-clock]` in `main.js` ticks it; see "Top-bar clock" below). |
| Header | `partials/site-header.php` | menu = Admin > Menus (`Nav::header()`), Donate pill = the page flagged `button` (`Nav::buttons()`); ARIA labels in **Common labels** |
| Mobile drawer | in the header partial | focus trap, Esc, backdrop, close button, background `inert`, returns focus to the hamburger |
| Footer | `partials/site-footer.php` | **Footer** section: description, partners heading, quick-links / programs headings, "show programs" switch, WhatsApp text, newsletter texts, copyright. Quick links = `Nav::footer()`; programs = active `programs`; contact + socials + reg. number = Settings; partners = `partners` table (strip hidden when empty). |
| Newsletter row | `partials/newsletter-row.php` | posts to `POST /subscribe` (csrf + honeypot, `source=footer`) |
| Donation panel | `partials/donate-panel.php` | section **Donation panel** (switch, texts, currency, amount buttons, 3 collage photos + alt) |

Layout `layouts/main.php` prints: skip link, top bar, header, flash, `<main id="main">`, footer, back-to-top, `main.js`. Page views never include chrome.

---------------------------------------------------------------------------------------------------------------------

## 7. Components - catalogue

Every snippet below is real and tested (class names exact). **Copy always comes from `content()` / DB rows**; the examples use `content('home', ...)` as the page.
Partials print nothing when they have nothing to show; wrap sections with `section_begin()/section_end()` so owners can switch them off.

### 7.1 Eyebrow (small caps + short gold underline)
```html
<span class="eyebrow">How we help</span>            <!-- left aligned -->
<span class="eyebrow eyebrow--center">Impact</span>  <!-- centred (automatic inside .section-head--center and .text-center) -->
```
White text on dark: automatic inside `.section--navy`, `.section--dark`, `.hero-photo`, `.page-hero`, `.band`, `.donate-panel`; otherwise add `eyebrow--light`.

### 7.2 Section heading - `partials/section-head`
Params: `title`* (supports `[[highlight]]`), `eyebrow`, `lead`, `align` (`'left'` | `'center'`), `tag` (`h2` default, `h1`, `h3`), `id` (heading id for `aria-labelledby`), `class` (e.g. `mb-0`), `actions` (SAFE html on the right: carousel arrows, "view all" button).
```php
<?= partial('partials/section-head', ['eyebrow' => content('home','help.eyebrow'), 'title' => content('home','help.title'), 'lead' => content('home','help.lead'), 'id' => 'help-title']) ?>
```
Markup produced: `.section-head > .section-head__text > .eyebrow + .section-head__title + .section-head__lead`. Modifiers: `.section-head--center`, `--split`. (`.section__header` is the older alias.)

### 7.3 Highlight word
`<h2><?= highlight(content('home','gift.title')) ?></h2>` with the field value `All children deserve an [[equal opportunity]]`. Plain HTML: `<span class="hl">word</span>`; navy-on-... variant `hl hl--navy` (orange stroke, for light photos).

### 7.4 Buttons (pill)
| Class | Look | Use |
|---|---|---|
| `btn btn--cta` | solid orange, white text | THE call to action (Donate, Pledge) |
| `btn btn--primary` | solid navy | header Donate pill, secondary solid |
| `btn btn--outline` | navy outline | secondary on light |
| `btn btn--outline-cta` | orange outline | tertiary on light |
| `btn btn--gold` | gold, navy-dark text | Subscribe on white; on navy |
| `btn btn--photo` | translucent white outline, blurred | **on photos** (hero video button) |
| `btn btn--white` | solid white | on dark photo bands |
| `btn btn--outline-light` | white outline | on navy panels |
| `btn btn--ghost`, `btn--link`, `.link-arrow` | quiet | inline |
| modifiers | `btn--caps` (small-caps label), `--sm`, `--lg`, `--block` | |
| play badge | `<span class="btn__play"><?= icon('play') ?></span>` | inside `btn--photo` / `btn--white` |
```html
<a class="btn btn--cta btn--caps" href="<?= e($b['url']) ?>"><?= e($b['label']) ?></a>
```
`btn--accent` / `btn--orange` (older views) render exactly like `btn--cta`.

### 7.5 Sheet (rounded-top overlap)
```html
<section class="section sheet" id="help"> ... </section>              <!-- white -->
<section class="section sheet sheet--tint"> ... </section>            <!-- cream; also --cool, --navy -->
```
Place it **right after** `hero-photo` (or any full-bleed photo block). It pulls itself up by `--radius-sheet` and rounds its top corners. With `section_begin('home','help',['class' => 'sheet'])`.

### 7.6 Hero (home) - `partials/hero-photo`
Params: `title`* (`[[highlight]]` marks ONE phrase), `eyebrow`, `lead`, `image`* (`content_img()` result), `buttons` (list `['label','url','class'?]`, first defaults to the orange CTA, others to `btn--photo`), `video` (`['url','label','title']`; the button is **hidden when the URL is empty or not YouTube**), `focus` (object-position, default `'60% 30%'`).
```php
<?= partial('partials/hero-photo', [
    'eyebrow' => content('home','hero.eyebrow'), 'title' => content('home','hero.title'), 'lead' => content('home','hero.lead'),
    'image'   => content_img('home','hero.image'),
    'buttons' => array_filter([ content_btn('home','hero.cta1')['has'] ? content_btn('home','hero.cta1') : null ]),
    'video'   => ['url' => content('home','hero.video_url'), 'label' => content('home','hero.video_label'), 'title' => content('home','hero.video_title')],
    'focus'   => '60% 30%',
]) ?>
```
(`content_btn()` returns `label`/`url`/`has`: pass only entries with `has`; the partial also skips empty ones.) Default video: `https://www.youtube.com/watch?v=DpptGd72fwc` (the live site's own video).

### 7.7 Inner-page hero - `partials/page-hero` (existing signature, new look)
Params (unchanged): `title`*, `eyebrow`, `lead`, `image` (`['src','alt']`, optional extra `'focus' => '50% 20%'`), `actions`, `breadcrumbs`. **Additive:** `sheet` = `'white'` (default) | `'tint'` | `'cool'` - set it to the background of the first section below the hero. `title` supports `[[highlight]]`.
Photo or navy gradient, breadcrumbs, eyebrow, serif title, lead, optional buttons, rounded white sheet cut-in. The first section after it needs no negative margin of its own.

### 7.8 Feature card - `partials/feature-card`
Params: `title`*, `text`, `icon` (see `icon_options()`), `href` (whole card becomes a link), `tag` (`h3`), `class` (`feature-card--row` icon-left, `feature-card--tint`).
```php
<div class="feature-row">                         <!-- 3 columns from md; --2 / --4 variants -->
  <?php foreach (content_list('home','help.items') as $f): ?>
    <?= partial('partials/feature-card', ['icon' => $f['icon'], 'title' => $f['title'], 'text' => $f['text']]) ?>
  <?php endforeach; ?>
</div>
```
Classes: `.feature-card`, `__icon`, `__body`, `__title`, `__text`; `.feature` is an alias with the same look.

### 7.9 Split layout (text + media) - `.split`
```html
<div class="split">                       <!-- 2 columns from md. DOM order: text first, media second -> media on the RIGHT -->
  <div> ...text... </div>
  <figure class="split__media"><img ...></figure>
</div>
<div class="split split--reverse"> ... </div>   <!-- the FIRST child moves to the right, i.e. media on the LEFT if media is first in the DOM -->
```
Modifiers: `split--start` (top aligned), `split--wide-text` (1.15 / 0.85), `split--wide-media`; media: `split__media--arch`, `split__media--portrait` (tall, 56 px corners), `split__media--frame` (pale offset block behind).
**Media left:** put the media *first* in the DOM; on phones it then also comes first. Use `split--reverse` only to flip a text-first block.

### 7.10 Photo stack - `partials/photo-stack`
Param `images` = up to 3 `content_img()` results; **first = front photo** (tilted -6deg), the others peek out behind. Missing images are skipped.
```php
<?= partial('partials/photo-stack', ['images' => [content_img('home','gift.image'), content_img('home','gift.image2'), content_img('home','gift.image3')]]) ?>
```
Classes: `.photo-stack`, `.photo-stack__item`. Needs about 30 rem of width to look right; it is `max-width: 34rem` and centred.

### 7.11 Photo band - `partials/band` (+ stat card)
Params: `image`*, `title`* (`[[highlight]]`), `eyebrow`, `text`, `actions` (`['label','url','class'?]`), `video` (`['url','label','title']` -> small white pill with play icon, hidden without a valid URL), `overlap` (bool), `after` (SAFE html placed in a `.container` under the band, normally the stat card), `focus`.
```php
<?php section_begin('home','volunteer',['bleed' => true, 'class' => 'band-section']); ?>
<?= partial('partials/band', [
    'image' => content_img('home','volunteer.image'), 'title' => content('home','volunteer.title'),
    'video' => ['url' => content('home','volunteer.video_url'), 'label' => content('home','volunteer.video_label'), 'title' => content('home','volunteer.video_title')],
    'after' => partial('partials/stat-band', ['stats' => $stats, 'overlap' => true]),
]) ?>
<?php section_end(); ?>
```
`band-section` removes the section padding (only room under the card remains). Without `after`, use `class="section band-section"` too and add your own spacing.

### 7.12 Stat card - `partials/stat-band`
Params: `stats` (rows of the `stats` table: `label`, `value_number` or `value`, `prefix`, `suffix`), `overlap` (pull up over the band above), `class`. Renders **1-4 columns** (first 4 rows) and **nothing when empty**. Numerals count up on scroll (`data-count-to`); the final value is in the HTML.
```php
<?= partial('partials/stat-band', ['stats' => \App\Models\Stat::forGroup('home')]) ?>
```
Classes: `.stat-band stat-band--N [stat-band--overlap]`, `__card`, `__item`, `__value`, `__label`. Real data today: "150+/300+ Youth Trained", "5 Active Programs" - render whatever the owner enters, never extra figures.

### 7.13 Card carousel - `partials/carousel`
Params: `items`* (list of SAFE html strings, one card each), `label`* (accessible name of the scroll region), `eyebrow`, `title`, `lead`, `id`, `foot` (SAFE html under the track). Renders nothing without items; with JS off it is a swipeable row.
```php
<?php $cards = [];
foreach ($programs as $p) { $cards[] = partial('partials/program-card', ['program' => $p]); }
foreach ($projects as $p) { $cards[] = partial('partials/project-card', ['project' => $p, 'statusLabels' => $labels]); } ?>
<?= partial('partials/carousel', ['items' => $cards, 'label' => content('home','featured.title'), 'eyebrow' => content('home','featured.eyebrow'), 'title' => content('home','featured.title')]) ?>
```
Markup: `.carousel[data-carousel] > (.section-head with .carousel__nav buttons) + .carousel__track[data-carousel-track tabindex=0 role=region] > .carousel__slide*`. Arrow keys scroll when the track has focus; buttons disable at the ends; hidden when everything fits; reduced-motion aware.

### 7.14 Programs / projects cards (campaign look)
`partials/program-card` and `partials/project-card` keep their signatures. Photo (4:3), a **round icon chip** on the photo edge (`.card__chip`, program icon column), small-caps meta, serif title, short summary, small orange pill (`btn btn--cta btn--caps btn--sm`). To build your own card with the chip:
```html
<article class="card"><a class="card__media" href="..."><img ...></a>
  <div class="card__body card__body--chip"><span class="card__chip"><?= icon('book-open','icon--lg') ?></span> ... </div></article>
```
`post-card`, `event-card`, `story-card`, `team-card`, `document-row`, `partner-grid` kept their signatures and got the new radius / serif titles.

### 7.15 Quote / testimonial card - `partials/quote-card`
EITHER `story` (a `stories` row - renders **nothing unless `consent_confirmed = 1`**; use `Story::published()`), OR `quote`*, `name`, `role`, `image` (`['src','alt']`), `href`. `variant`: `''` (pale navy) | `'dark'` (deep navy) | `'large'` (serif, not clamped). No stars, no ratings.
```php
<?php $stories = \App\Models\Story::published(3); ?>
<?php if ($stories): ?><div class="grid grid--3"><?php foreach ($stories as $s): ?><?= partial('partials/quote-card', ['story' => $s]) ?><?php endforeach; ?></div><?php endif; ?>
```
Classes: `.quote-card`, `__mark`, `__text`, `__person`, `__avatar`, `__name`, `__role`; `--dark`, `--large`.

### 7.16 Donation panel - `partials/donate-panel`
No params needed (copy: registry `global.donate_panel`; optional overrides `eyebrow`, `title`, `text`, `class`). Hidden when the registry switch is off. Chips = editable amounts, currency editable, **GET to `/donate?amount=&currency=#pledge`** (pledge intent, never a payment) + "Donate online" button to `setting('donate_url')` (hidden when empty). Wrap: `<section class="section"><div class="container"><?= partial('partials/donate-panel') ?></div></section>`.
JS: `form[data-donate-panel]`, `[data-donate-input]`, `[data-donate-chip][data-amount]`.

### 7.17 Accordion / FAQ with photo - `partials/faq-split`
Params: `faqs`* (rows of `faqs`), `image` (`content_img()`), `eyebrow`, `title`, `lead`, `prefix` (unique id prefix if two on a page), `first_open` (default true), `reverse`. First item open; plain-text or rich answers both safe. Without an image it becomes a centred single column.
The plain accordion (`.accordion[data-accordion="single"]`) is unchanged and still works anywhere.

### 7.18 Media mosaic - `partials/media-mosaic`
Param `images` (3-5 `content_img()` results). 2 columns on phones, a row of 3 / 4 / 5 with a relaxed stagger from `md`. Classes `.mosaic`, `.mosaic__item`, `.mosaic--3`, `--5`.

### 7.19 Video button + lightbox - `partials/video-button` (used inside hero-photo and band)
Params: `url`*, `label`, `title`, `class` (default `btn btn--photo btn--caps`). **No valid YouTube URL -> nothing is rendered.** Without JS it is a normal link to YouTube. With JS (`main.js`) a click opens an accessible dialog (`role=dialog aria-modal`, focus on Close, Tab trap, Esc / backdrop / Close dismiss, background `inert`, focus returns to the button) and injects `https://www.youtube-nocookie.com/embed/<id>?autoplay=1...` **only then**. A "Watch on YouTube" link inside the dialog is the fallback. CSP: `frame-src` already allows `youtube-nocookie.com` (no header change was needed).
Hooks: `a[data-video-lightbox][data-video-id][data-video-title][data-close-label][data-fallback-label]`. Any page may add the attributes to its own link.

### 7.20 Partner strip - `partials/partner-strip`
Params `partners` (rows of `partners`), `title`. Renders nothing when empty. Greyscale logos that colourise on hover. (The footer already shows it - pages normally do NOT.)

### 7.21 Newsletter row - `partials/newsletter-row`
Optional `title`, `text`, `placeholder`, `button`, `source` (stored on the subscriber), `idp` (id prefix, required if used twice). The footer already contains one.

### 7.22 Reveal-on-scroll
`data-reveal` on any block (partials already add it), including tall CMS text: the observer uses `threshold: 0`, so a block taller than the viewport still reveals as soon as it enters (a ratio threshold is unreachable for tall blocks and left long text invisible). Fades/slides in once; no-JS and `prefers-reduced-motion` show everything immediately.

### 7.23 Misc
`.section--tint | --cool | --navy | --dark | --spacious | --sm`, `.grid grid--2|3|4`, `.stack`, `.cluster`, `.prose`, `.figure-round`, `.badge`, `.tag`, `.empty-state` (via `partials/empty-state`), `.pagination`. Spacing helpers `.mt-3..7`, `.mb-0..6`.

---------------------------------------------------------------------------------------------------------------------

## 8. JS hooks (`assets/js/main.js`; no inline JS, CSP forbids it)

| Hook | Behaviour |
|---|---|
| `[data-site-header]` | `.is-scrolled` after 8 px -> bar shrinks, shadow |
| `[data-nav-toggle]`, `[data-site-nav]`, `[data-nav-close]`, `[data-nav-backdrop]` | mobile drawer (focus trap, Esc, inert background) |
| `[data-submenu-toggle]` | dropdown button (also opens on hover / focus-within at desktop) |
| `[data-accordion]` (`="single"`), `[data-accordion-trigger]`, `[data-accordion-panel]` | accordion |
| `[data-carousel]` > `[data-carousel-track]`, `[data-carousel-prev]`, `[data-carousel-next]` | carousel |
| `a[data-video-lightbox]` + `data-video-id` `data-video-title` `data-close-label` `data-fallback-label` | YouTube dialog |
| `form[data-donate-panel]`, `[data-donate-input]`, `[data-donate-chip][data-amount]` | donation chips |
| `[data-reveal]`, `[data-count-to]` (+ `data-count-prefix` / `data-count-suffix`) | reveal / counters |
| `a[data-lightbox="group"]` (+ `data-caption`) | image lightbox (gallery) |
| `[data-back-to-top]`, `select[data-autosubmit]`, `[data-dismiss]`, `body[data-ga-id]` | unchanged |

Page-specific JS goes in `assets/js/pages/<name>.js` and is included with `$this->start('scripts')` (as before).

---------------------------------------------------------------------------------------------------------------------

## 9. Icons

Inline SVG, 24x24, stroke `currentColor`, rendered by `icon('name', 'icon--lg', 'optional label')` (`App\Core\Icons`, `app/core/Icons.php`). 100+ icons, including the 23 added for the redesign:
`laptop monitor smartphone wifi code graduation-cap lightbulb flame sprout handshake hand-heart target rocket trending-up compass mic play book message-circle arrow-up-right send sun video`
plus existing `heart phone mail map-pin arrow-left/right/up chevron-* quote check check-circle calendar users user briefcase book-open globe award star shield home banknote clock external-link x ...`. Social glyphs (`facebook instagram linkedin twitter(X) tiktok youtube whatsapp`) are inline SVG too. No icon fonts, no downloads.
**Add an icon:** open `app/core/Icons.php`, add `'name' => '<path .../>'` (inner SVG markup of a 24x24 stroke icon; use `fill="currentColor"` inside the markup for a solid glyph like `play`) to `Icons::SET`, optionally add it to the curated list in `icon_options()` (`app/helpers_design.php`) with a human label. Sizes: `.icon` 1.25em, `--sm` 1em, `--lg` 1.6em, `--xl` 2.25em.
**Editor-selectable icon in a registry repeater:** `['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => icon_options()]` and render with `icon(safe_icon($row['icon']))`.

---------------------------------------------------------------------------------------------------------------------

## 10. Registry conventions for page agents

* One section per visual block; key it with the recipe name below (`hero`, `help`, `gift`, `volunteer`, `about_us` ...). Optional blocks: `'toggle' => true, 'default_visible' => true`.
* Field set per heading block: `eyebrow` (text), `title` (text, `help` mentions `[[...]]`), `lead` (textarea). Button = `<name>_label` + `<name>_url`. Image = `type image` with an honest default `alt`.
* Lists = `repeater` (`item_label`, `add_label`, `max_rows`). Card lists: `icon` select (`icon_options()`), `title`, `text`.
* **Never hardcode copy in a view.** Empty states and button labels come from the registry too.
* **Data-driven blocks** (stats, programs, projects, stories, faqs, partners) take their *rows* from tables; the registry only holds the copy around them. Hide the whole section when there are no rows (`if ($rows) { section_begin... }` or `section_on()` + empty check).
* Photos: use only `assets/img/site/*`. `photo-hero-portrait.jpg` carries a third-party watermark along its bottom edge (crop it with `focus` or replace it); `photo-02.jpg` / `photo-08.jpg` are consent-flagged (do not use without owner confirmation). Suggested crops: training room `photo-03` (trainer + students, centre-left), audience `photo-04`, classroom `photo-05`, hall `photo-06`, founder portrait `photo-founder-tall` (face top-centre: use `object-position: center 15%` for avatars; never crop the head).

---------------------------------------------------------------------------------------------------------------------

## 11. Page skeletons

**Home** (`web/home/index.php`): `hero-photo` -> `section.sheet` (1) -> sections (2)-(11) -> (footer is in the layout).
**Inner page:**
```php
<?= partial('partials/page-hero', ['title' => content('team','hero.title'), 'eyebrow' => content('team','hero.eyebrow'), 'lead' => content('team','hero.lead'), 'image' => /* optional content_img array */ null]) ?>
<?php section_begin('team','board'); ?>
    <?= partial('partials/section-head', ['title' => content('team','board.title'), 'lead' => content('team','board.lead')]) ?>
    <div class="grid grid--4"> ...cards... </div>
<?php section_end(); ?>
<?= partial('partials/cta-donate') ?>
```
Inner pages should alternate backgrounds: white / `section--tint` / white ..., finish with `cta-donate` (restyled navy panel) or `donate-panel`. Use `sheet` => `'tint'` on page-hero when the first section is cream.

---------------------------------------------------------------------------------------------------------------------

## 12. Section-by-section recipe: the 12 sections of the reference

The reference order (top to bottom). Items 1, 2 and 12 are global chrome (done). Items 3-11 are the home page; the recipes also tell inner pages what to reuse.

### 1. Top bar - DONE (`partials/topbar`)
Address left; e-mail, phone and a WhatsApp/Donate quick link right. Editable in Site-wide content > Top bar. Nothing to build.

#### Top-bar clock (`.clock`, `scss/layout/_topbar.scss`)
Live date + time in the left group, e.g. `Uganda time: Fri, 16 Oct 2026 · 14:35:09 EAT`. Registry page `global`, section **Date & time clock** (organisation time zone or the visitor's device time, 12/24 hour, seconds, date, label). Rules:
* PHP prints the first paint (`<time datetime>`, `data-epoch` = the server's Unix time), `main.js` (`[data-clock]`) updates it in place once per second (per minute when seconds are hidden), aligned to the boundary, paused while the tab is hidden, one timer per page. CSP-safe, no requests. Zone and DST maths use `Intl.DateTimeFormat` with `timeZone` (zone rules come from the visitor's browser, so a very unusual zone can differ from PHP's by an hour until the script runs). Organisation time follows the server's instant (`data-epoch`, skew measured once at load, ignored when the page came from the back/forward cache), so a wrong device clock does not change it; Visitor mode uses the device clock. Zone names come from `en-US` (EST/EDT/BST), PHP's abbreviation is kept while the UTC offset is unchanged.
* `role="timer" aria-live="off"`, named by `clock.aria_label`; the icon is decorative. Navy-dark on gold = 7.0:1.
* No layout shift: the clock uses the device UI font stack with tabular digits (DM Sans digits are proportional) and the time has a fixed min-width; the date, year, time and zone are each unbreakable. The contact items keep their normal breakpoints (phone + quick link on phones, e-mail from 576px, address from 992px); when the left group (address + clock) and the right group do not fit on one row, the right group drops to a second row instead of overlapping, and at 200% text size the clock wraps between its pieces. A long label is ellipsised (`max-width: 11em`, label is max 20 characters). Phones drop the label and the year. Nothing is cut off at 320px with 200% text. The clock is hidden for print.
* It lives inside the top bar, so switching the top bar off hides the clock too.

### 2. Header - DONE (`partials/site-header`)
White, sticky, logo left, centred menu (dropdowns), dark "Donate" pill right, drawer on mobile. Nothing to build.

### 3. Hero - `partials/hero-photo`
* Registry `hero`: `eyebrow`, `title` (e.g. `Bridging the Digital Divide for [[Vulnerable Youth]] in Northern Uganda`), `lead`, `cta1_label/url` (orange pill, e.g. Donate / Explore our Programs), `image`, `video_url` (default `https://www.youtube.com/watch?v=DpptGd72fwc`), `video_label`, `video_title`. A second text button is optional (`cta2_*`).
* Markup: see 7.6. The next block MUST be `<section class="section sheet">`.
* Empty: no image -> navy-dark background still readable; no video URL -> no video button.

### 4. "How we help" - feature cards + tall portrait
```php
<?php section_begin('home','help',['class' => 'sheet', 'labelledby' => 'help-title']); ?>
  <div class="split split--wide-text split--start">
    <div>
      <?= partial('partials/section-head', ['eyebrow' => content('home','help.eyebrow'), 'title' => content('home','help.title'), 'lead' => content('home','help.lead'), 'id' => 'help-title']) ?>
      <div class="feature-row"> <?php foreach (content_list('home','help.items') as $f): ?><?= partial('partials/feature-card', ['icon' => $f['icon'], 'title' => $f['title'], 'text' => $f['text']]) ?><?php endforeach; ?> </div>
    </div>
    <?php $img = content_img('home','help.image'); if ($img['has']): ?><figure class="split__media split__media--portrait"><?= photo_tag($img) ?></figure><?php endif; ?>
  </div>
<?php section_end(); ?>
```
Registry `help`: `eyebrow`, `title`, `lead`, `items` repeater (`icon` select `icon_options()`, `title`, `text`; 3 rows), `image`. Use the founder portrait or a Lumina photo with faces intact. Source of the cards' copy: the real focus areas (digital skills, employability, entrepreneurship...). Inner pages: `feature-row` for any 3 benefits/values.

### 5. Split: stacked tilted photos + text + orange button
```php
<?php section_begin('home','approach',['class' => 'section--tint']); ?>
  <div class="split split--wide-media">
    <?= partial('partials/photo-stack', ['images' => [content_img('home','approach.image1'), content_img('home','approach.image2'), content_img('home','approach.image3')]]) ?>
    <div data-reveal>
      <span class="eyebrow"><?= e(content('home','approach.eyebrow')) ?></span>
      <h2><?= highlight(content('home','approach.title')) ?></h2>
      <div class="prose mt-4"><?= content_html('home','approach.body') ?></div>
      <?php $b = content_btn('home','approach.cta'); if ($b['has']): ?><p class="mt-5"><a class="btn btn--cta btn--caps" href="<?= e($b['url']) ?>"><?= e($b['label']) ?></a></p><?php endif; ?>
    </div>
  </div>
<?php section_end(); ?>
```
Photo stack first in the DOM = media on the left. Eyebrow text must be real copy (the reference's "gift of $36" is NOT used - no invented gift amounts). One image is enough (`photo-stack` degrades to a single tilted photo).

### 6. Volunteer band + stats card
See 7.11 + 7.12. Registry `volunteer`: `title` (e.g. a real invitation: "Volunteer opportunities now open for you" only if the live site says so; otherwise use the Involvement page wording), `image`, `video_url/label/title` (optional), optional `cta_label/url`. Stats come from `Stat::forGroup('home')` (hide the card when empty; the band alone is fine).
```php
<?php $stats = \App\Models\Stat::forGroup('home'); ?>
<?php section_begin('home','volunteer',['bleed' => true,'class' => 'band-section']); ?>
  <?= partial('partials/band', ['image' => content_img('home','volunteer.image'), 'title' => content('home','volunteer.title'), 'actions' => array_filter([content_btn('home','volunteer.cta')]), 'video' => [...], 'after' => partial('partials/stat-band', ['stats' => $stats, 'overlap' => true])]) ?>
<?php section_end(); ?>
```
(If you pass `actions`, only entries with both label and url render.)

### 7. Mission / vision + collage + feature rows + quote card + mosaic
```php
<?php section_begin('home','mission',['labelledby' => 'mission-title']); ?>
  <div class="split split--start">
    <?= partial('partials/photo-stack', ['images' => [content_img('home','mission.image1'), content_img('home','mission.image2')]]) ?>
    <div>
      <?= partial('partials/section-head', ['eyebrow' => ..., 'title' => ..., 'lead' => ..., 'id' => 'mission-title']) ?>
      <div class="grid grid--2">                                   <!-- pale tint boxes -->
        <div class="feature-card feature-card--tint"><h3 class="feature-card__title"><?= e(content('home','mission.mission_title')) ?></h3><p class="feature-card__text"><?= e(content('home','mission.mission_text')) ?></p></div>
        <div class="feature-card feature-card--tint"> ...vision... </div>
      </div>
      <div class="stack mt-5">
        <?php foreach (content_list('home','mission.rows') as $r): ?><?= partial('partials/feature-card', ['class' => 'feature-card--row', 'icon' => $r['icon'], 'title' => $r['title'], 'text' => $r['text']]) ?><?php endforeach; ?>
        <?= partial('partials/quote-card', ['variant' => 'dark', 'quote' => content('home','mission.quote'), 'name' => content('home','mission.quote_name'), 'role' => content('home','mission.quote_role'), 'image' => content_img('home','mission.quote_image')]) ?>
      </div>
    </div>
  </div>
  <div class="mt-7"><?= partial('partials/media-mosaic', ['images' => [content_img('home','mission.m1'), content_img('home','mission.m2'), content_img('home','mission.m3'), content_img('home','mission.m4')]]) ?></div>
<?php section_end(); ?>
```
The quote must be a real quotation (e.g. the founder's, from the live site). Mission/vision wording = the existing home registry `vision_mission` texts. The mosaic can reuse the 4-5 Lumina photos (no repeats of the same photo in the same view if avoidable).

### 8. Featured programs & projects carousel
See 7.13/7.14: cards from `Program::active()` (+ `Project::where(['is_active' => 1], 'sort_order ASC, id ASC', 3)`); heading with arrows; each slide = `program-card` / `project-card`. **No progress rings, raised/goal numbers or percentages** (none exist as data). The round chip carries the program icon. Registry `featured`: `eyebrow`, `title`, `lead`, optional `cta_label/url` ("View all programs" in `foot`). Hide the section when there are no cards.

### 9. Donation panel
`<section class="section"><div class="container"><?= partial('partials/donate-panel') ?></div></section>` (inside `section_begin('home','donate_panel', ...)` only if the page wants its own toggle; the panel itself already honours the global switch). **Donate page agent:** read `donate_prefill()` and use it as the default of the pledge form fields: `old('amount', $pre['amount'])` and the currency select (`$pre['currency']`), and keep `id="pledge"` on the pledge section (anchor target).
Label it as what it is: *pledge* or *donate online*; never "pay", "checkout" or "secure payment".

### 10. Impact stories (the "feedback & reviews" slot)
Three `quote-card`s on a `section--cool` background, **from `Story::published(3)` only** (consent-confirmed). **Hide the whole section when there are none** (no placeholders, no star ratings). Add carousel arrows with `partials/carousel` when there are more than 3 (`items` = rendered `quote-card`s). Registry `stories_teaser`: `eyebrow`, `title`, `lead`, `cta_label/url` ("Read more stories").
```php
<?php $stories = \App\Models\Story::published(6); if ($stories): section_begin('home','stories',['class' => 'section--cool']); ?>
  <?= partial('partials/section-head', ['eyebrow' => ..., 'title' => ..., 'align' => 'center']) ?>
  <div class="grid grid--3"><?php foreach (array_slice($stories, 0, 3) as $s): ?><?= partial('partials/quote-card', ['story' => $s]) ?><?php endforeach; ?></div>
<?php section_end(); endif; ?>
```

### 11. FAQ with photo
`<?= partial('partials/faq-split', ['faqs' => \App\Models\Faq::forGroup('general'), 'image' => content_img('home','faq.image'), 'eyebrow' => content('home','faq.eyebrow'), 'title' => content('home','faq.title')]) ?>` inside a normal section. Rows come from the `faqs` table (hide the section when empty). Use `prefix` when two accordions share a page (`faq-home`, `faq-donate`).

### 12. Footer - DONE (`partials/site-footer`)
Partners strip (hidden when no partners) -> newsletter row -> deep navy footer (logo + description + socials | quick links | programs | contact) -> copyright / registration / credit row. Nothing to build.

---------------------------------------------------------------------------------------------------------------------

## 13. Do / don't

* **Do** compose partials; **don't** write a new card/heading/button style on a page. Needing a brand-new reusable component? Put it in your own page partial `scss/pages/_<page>.scss`, scoped under `.page-<slug>`, and report it.
* **Do** wrap every optional block in `section_begin()` / empty-data guards. **Don't** show headings above empty lists.
* **Don't** use `btn--accent` for new work (use `btn--cta`); don't use gold text on white; don't add `backdrop-filter` to `.site-header`.
* **Don't** add inline `<script>`, external scripts, iframes (except via `video-button`) or new external fonts/CDNs.
* **Don't** invent numbers: stats, amounts, partner names, testimonials, ratings, goals, progress. The amount chips are *suggestions* in the registry, not figures about the foundation.
* **Do** test at 375 / 768 / 1440 with no horizontal scroll, keyboard only, and `prefers-reduced-motion`.

---------------------------------------------------------------------------------------------------------------------

## 14. Who owns what / what changed in shared files (architect log)

Created: `app/helpers_design.php`; partials `topbar site-header site-footer newsletter-row partner-strip section-head feature-card photo-stack stat-band band hero-photo video-button carousel quote-card donate-panel faq-split media-mosaic`;
`scss/layout/_topbar.scss`; `scss/components/_buttons-pill _section-head _sheet _hero-photo _feature-card _media _stat-band _band _carousel _quote-card _donate-panel _faq-split _partner-strip _video-modal.scss`; this file.
Rewritten: `scss/layout/_header.scss`, `scss/layout/_footer.scss`, `scss/components/_hero.scss` (page-hero; legacy `.hero` kept), `scss/base/_typography.scss` (serif headings, new eyebrow, `.hl`).
Edited (additive): `scss/abstracts/_tokens.scss`, `scss/main.scss`, `scss/components/{_cards,_cta,_accordion,_event-card,_story-card,_team-card,_document-row,_partner-grid}.scss`, `scss/layout/{_grid,_container,_section}.scss`,
`app/views/layouts/main.php` (partials + Fraunces link), `app/views/partials/{page-hero,cta-donate,program-card,project-card}.php`, `app/core/Icons.php` (+23 icons), `app/helpers.php` (one `require_once` at the end), `app/config/pages/global.php` (new sections/fields, all existing keys unchanged), `assets/js/main.js`.
Unchanged: admin SCSS and PHP, every `.htaccess`, CSP headers (`frame-src` already allows `youtube-nocookie.com`; Fraunces comes from the already-allowed Google Fonts hosts).
Legacy removed by the integration pass: the old `.hero` home hero, `.stats` / `.stat` counters, `.newsletter` footer form, the `timeline` component and a few dead home blocks (`.home-photo`, `.icon-tile`, `.principle-grid`, `.form-split`). `.feature` is still an alias of `.feature-card`.

Build note: `bin/build-scss.py` now strips the UTF-8 BOM that libsass prepends to compressed output whenever the CSS contains a non-ASCII character (for example `content: '...'`). After the banner comment that BOM became part of the first selector and silently dropped the `:root` rule (all `--custom-properties`, including `--radius-sheet`). Write CSS escapes as `\201C`, never as literal non-ASCII characters.


## 15. Integration pass (what changed after the page agents finished)

* **Stat card source of truth:** the navy stat card on Home shows the rows of **Admin > Stats** (group `home`, first four). The repeater in Admin > Pages > Home > "4b. Statistics card" is only a fallback while that table is empty, so editing it has no visible effect once stats exist. This is deliberate (one place for real figures).
* **Donate hand-off:** the home donation panel is a GET form to `/donate?amount=&currency=#pledge`; the Donate page reads it through `donate_prefill()` (digits only, 1 to 999,999,999.99; anything else is ignored) and fills its pledge form. The pledge chips need JS and are hidden without it.
* **Heading order:** `partials/empty-state` now prints an `h2` by default (optional `tag` = `h2|h3|h4`), and the Contact info cards are `h2`, so no public page jumps from `h1` to `h3`.
* **Events:** the facts card shows `HH:MM - HH:MM` only when the end time is later than the start.
* **Checking a mobile layout:** headless Edge cannot go below a 500 px window, so use the built-in browser (emulated 375 x 812) for phone checks. Scroll-reveal (`[data-reveal]`) elements are faded until they enter the viewport, so full-page headless screenshots show them half transparent: that is expected.
* **CSS size:** `assets/css/main.min.css` is about 169 KB (about 199 KB unminified); the pages load only the minified file.
* **Tablet "How we help":** the text + cards block stacks (cards 2-up, then the photo as a 4:3 crop, max 34 rem tall) until `lg`; only from `lg` does the photo sit beside the cards and stretch to their height. (At `md` the narrow text column used to force a 270 px wide, 1180 px tall sliver.)
* **No repeated photos on Home:** the gallery mosaic leaves out every photo the carousel cards already show and disappears when fewer than three remain (today all four cleared photos are program photos, so it is hidden until more gallery photos are added). The Home donation panel has its own collage fields (Admin > Pages > Home > "7. Donation panel": default the hero photo; clear them to fall back to the site-wide collage); `partials/donate-panel` accepts an optional `images` override for this.
* **Footer quick links:** a list of more than 7 links flows into two CSS columns from 360 px (a wrapped label no longer stretches a grid row); the quick-links track is wider (`1.15fr 1.85fr 1fr 1.35fr` from `lg`).

## 16. Fixer pass (visual redesign review)

* **Hero crops:** Programs, Donate and Team (photo-03) use `focus` `50% 12%` / `50% 12%` / `40% 12%` so the trainer's head is inside the 1440 px strip; Events (photo-04) `50% 45%`. Only four photographs are cleared (CONTENT_NOTES), so three pages still share photo-03 by default; editors can swap the hero image in Admin > Pages.
* **Events detail:** the facts `<aside>` is now FIRST in the DOM (tab order = visual order on phones); from `lg` the grid places `__main` in column 1 and `__aside` in column 2 without `order`.
* **Back to top:** `visibility: hidden` until shown (not a tab stop while invisible); the footer bottom bar has extra bottom padding below `xl` so the button never covers the credit line.
* **Projects list:** hover zoom and the reverse-row number chip were dead selectors (`&:hover &__frame` inside a nested block repeats the parent chain). Inside nested page blocks write the block name out in full.
* **Home mosaic:** the controller passes up to 40 active gallery photos; the view removes the ones the carousel shows and THEN keeps five, so photos added in Admin > Gallery (which are appended at the end) switch the mosaic on.
* **Admin > FAQs:** hints describe the real Home fallback (home, then general, then donate); a `home` tab is always listed.
* **Admin > Pages repeaters:** a sub-field missing from a row saved before it existed shows the registry default in the editor (an unchanged Save no longer rewrites the icon).
* **Lightbox / video labels:** `global > labels` has `image_viewer`, `prev_image`, `next_image`, `video_fallback_title` (and `close`); `_tile.php` and `video-button.php` pass them as data attributes; the redundant `allowFullscreen` line is gone.
* **Image weight:** `photo-founder-tall.jpg` is now 832 x 1248 (100 KB); the 1440 x 2160 master is `docs/brand/photo-founder-tall-master.jpg` (not web served).
