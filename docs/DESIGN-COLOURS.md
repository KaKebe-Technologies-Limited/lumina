# Colour system

Source of truth: `scss/abstracts/_tokens.scss` (shared by the public and admin bundles). Rebuild with `bin/build-scss.py`.

## Roles

| Role | Token | Hex | Use |
|---|---|---|---|
| Primary | `$navy` / `$navy-dark` / `$navy-light` | `#2C3950` / `#172036` / `#3C4E6E` | headings, header, footer, deep bands, secondary buttons, icon tiles |
| Accent + action | `$gold` (`$cta`) | `#D6A151` | the ONE call-to-action fill (text `$on-cta` = navy-dark), underlines, rules, top bar, icons on navy |
| Action hover | `$cta-hover` | `#E3B666` | hover of the gold button |
| Gold as text on light | `$gold-text` (aliases `$orange-text`, `$orange-dark`) | `#85560A` | eyebrows, text links, hover colour of card titles, icons on light, focus rings |
| Gold decorative on white | `$gold-deep` | `#C47E0D` | large numerals, borders, double rules (never small text) |
| Neutrals | `$ink`, `$ink-soft`, `$surface` (cream), `$surface-alt`, `$tint-cool`, `$line`, `$border` | `#1D1E20`, `#5A6470`, `#F7F3EC`, `#F1EEE8`, `#EEF1F6`, `#E4E1DA`, `#E3DDD2` | body text, section backgrounds (white / cream / pale navy alternate), hairlines |
| Status only | `$success`, `$warning`, `$danger`, `$info` (+ `-bg`) | `#1E7B45`, `#85560A`, `#B42318`, `#1F5F8B` | alerts, required marks, validation. Never used as decoration |
| Optional | `$teal-dark` | `#3D6870` | "teal" form theme, a few Academy category tiles, calendar type |

The rust orange of the old design (`#B85A14`, `#CC651A`, `#E07341`, `#A64B0C`) is retired. The legacy names `$orange`, `$orange-dark`, `$orange-accent`, `$orange-text` remain as aliases of gold / bronze so older partials keep compiling; use the new names in new code.

Rules of thumb: one primary (navy), one accent (gold), one action (gold button, navy text). Secondary buttons are navy (`btn--primary`) or outlined (`btn--outline`, `btn--outline-light` on dark). Information badges are the navy tint, not a separate blue.

## Contrast (WCAG 2.1, computed from the hex values)

| Pair | Ratio | Needs |
|---|---|---|
| navy on white / cream | 11.6 / 10.5 | 4.5 |
| navy-dark on gold (button) | 7.0 | 4.5 |
| navy-dark on `$cta-hover` | 8.6 | 4.5 |
| gold on navy / navy-dark | 5.0 / 7.0 | 4.5 |
| gold-text on white / cream / pale navy | 6.3 / 5.7 / 5.6 | 4.5 |
| ink on white | 16.7 | 4.5 |
| ink-soft on white / cream | 6.0 / 5.4 | 4.5 |
| gold-deep on white | 3.3 | 3 (large text, UI only) |
| danger / success on white | 6.6 / 5.3 | 4.5 |

Calendar types (white text on the solid colour): programme `#2C3950` 11.6, event `#2F6F7A` 5.7, Uganda holiday `#85560A` 6.3, international day `#1F5F8B` 6.8, meeting `#5E6B3A` 5.8, deadline `#B42318` 6.6, other `#5A6470` 6.0.

An in-browser audit (`tools/contrast-audit.js`: loads each page in an iframe at 1200 px and 390 px and lists text below AA against its nearest solid background) found no failing text on 35 public pages at either width and no horizontal scrolling. Text laid over photographs is not measured by that script: hero and band photos keep a navy overlay of at least 0.55 to 0.9 opacity where the copy sits (see `_hero.scss`, `_hero-photo.scss`).

## Not changed (settings stored in the database)

- Calendar type colours are edited in Admin > Calendar > Types. The seed (`database/seed.sql`) now ships the palette above and only applies to types that were never edited. On an existing site, set them by hand to the hex values listed above if you want them to match.
- Membership-card accent (`membership_card_accent`, `#E0642C`), share-image accent 2 (`social_card_accent2`, `#CC651A`) and the card templates "Reference / Royal / Forest" are admin-editable design settings for printed cards and social images, not website UI.
