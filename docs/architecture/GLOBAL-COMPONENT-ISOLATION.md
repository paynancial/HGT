# Global component isolation: architecture (updated 30 Sep 2026)

**Owner rule (permanent):** "Changing header or footer must not impact other page content."

This is the structure after the approved split, and the tests that enforce the rule.

**Labels:** VERIFIED (tested) or INFERRED (read from code).

## 1. Before → after

| | Before | After |
|---|---|---|
| Shell markup | One file, `include/partials/site-header.php` (utility bar + header + search + navigation + mega menus). Footer, support, consent and login lived in three different folders. | `include/global/` has one file per component (section 2). |
| CSS | Two hand-edited stylesheets: `hg-ui.css` (~1,000 lines, mixing base, header, cards and every page) and `hg-site.css`. | Source files per token set, base, component and page in `assets/css/src/`. `tools/build_assets.py` builds the same two stylesheets. |
| JS | Two files, `hg-ui.js` and `hg-site.js`, with all behaviours mixed. | Source chunks per component in `assets/js/src/`, concatenated into the same two files (byte-identical). |
| Tests | Before/after page-body capture (`isolation.js`). | Plus `component-change.js`: changes each shell component in staging and proves page bodies don't move. |

The pages still load exactly the same four assets. The rendered HTML of 150 checked URLs is byte-identical to before the split (VERIFIED).

## 2. Structure

```
include/ui/core.php            hg_layout_start() / hg_layout_end(): the layout. Page-specific <head> (SEO) comes from the page's $meta.
include/global/                GLOBAL SHELL, one component per file
  page-loader.php              first-visit loader (inline critical CSS/JS in <head>, overlay first in <body>); sets html.hg-js
  header.php                   skip link + <header> frame (logo, contact actions, login icon); includes the three below
  utility-bar.php              .hg-utility (tagline, Login)
  holiday-search.php           #hg-header-search: sends its state to /tours as query parameters
  navigation.php               #hg-nav: primary tabs, mobile drawer; includes mega-menu.php
  mega-menu.php                #mega-india / #mega-intl / #mega-spec (data from destinations.json)
  footer.php                   .hg-footer (links from include/footer_nav.php)
  support-widget.php           #hg-support
  cookie-consent.php           #hg-consent
  login-dialog.php             login dialog
<main id="main">               PAGE CONTENT ONLY: page modules (index.php, tours.php, templates/*.php …)
```

## 3. Ownership

| Layer | Owns | Files | Must never contain |
|---|---|---|---|
| **Global shell** | Page loader, utility bar, header, search, navigation and mega menus, footer, support widget, cookie consent, login | `include/global/*` · `css/src/components/{utility-bar,header,holiday-search,navigation,mega-menu,footer,support-widget,cookie-consent,login-dialog,icons}.css` · `js/src/ui/{mega-menu,navigation,holiday-search*,login-dialog,analytics}.js`, `js/src/site/*` | Package data, itinerary, prices, page SEO |
| **Design system** | Tokens (colours, type, spacing, radii, shadows, breakpoints), base typography, buttons | `css/src/tokens.css`, `css/src/global.css` | Page layout values |
| **Shared content components** | Cards, grids, sections, FAQ, forms, notices (used inside pages) | `css/src/components/content.css`, `js/src/ui/forms.js`, `section-nav.js` | Header/footer rules |
| **Page modules** | Their own layout and content | `css/src/pages/{homepage,search-results,tour-detail,itinerary,contact,travel-guide,people,policy,customized-holidays}.css`, `js/src/ui/search-results.js`, page PHP files and templates | Shell rules |
| **Business data** | Packages, itineraries, rates, offers, curation, Package IDs | `include/data/*.json`, `include/package_registry.php` | Presentation |
| **Global config** | Brand, phone, WhatsApp, email, address, social links, footer navigation | `include/site_config.php`, `include/footer_nav.php` | Tour-specific itinerary, pricing, inclusions, add-ons, reviews |
| **SEO** | Title, description, canonical, robots, OG, page schema: from each page's `$meta` | `hg_head()` in `core.php` | Nothing in the shell writes them. Organization schema is the only global schema. |
| **CRM / enquiry** | Package ID, offer code, rate version, enquiry payload: built on the server from business data | `mail.php`, `package_registry.php` | Nothing in the shell touches them |

## 4. CSS rules

- **Bundle order** is set in `tools/build_assets.py`. The cascade was preserved exactly: computed styles of every element are identical on 14 page types × 3 widths, including open menus, sheets, accordions and dialogs (VERIFIED).
- **Scoping.** Component rules are scoped to the component's root class: `.hg-footer …`, `.hg-utility …`, `#hg-nav …`.
- **Selector lists** that spanned components were split per component during the migration. Each component's responsive overrides live in its own file, after its base rules.
- **Global base rules** (`.hg-body a`, `h1–h4`, `p`, `ul`, form controls) are the design-system baseline in `global.css`. Components override them with their own scoped selectors.
- **To change CSS:** edit `assets/css/src/…`, run `python3 tools/build_assets.py`, commit the sources and the rebuilt bundles. `--check` fails if a bundle is out of date.

## 5. JS rules

- Behaviour is bound through `data-hg-*` attributes and component IDs. There are no page-wide generic selectors like `.button`, `form` or `.card` (INFERRED from review; the chunks are small enough to read).
- Chunks share one closure (`_start.js` / `_end.js`) for the small helpers (`$`, `$$`, analytics `track`).
- Edit the chunk, then rebuild.

## 6. Tests (run for every global change)

| Test | What it proves |
|---|---|
| `tools/tests/browser/component-change.js` | For each of utility bar, header, navigation, holiday search and footer, a markup marker and a style rule are added in the staging copy. The change appears in that component on 8 page types. Page bodies are unchanged: DOM, text, geometry and computed styles. Files are restored. **17/17 VERIFIED.** |
| `tools/tests/browser/isolation.js` | Before/after capture of page bodies (DOM, geometry, SEO head, forms, links, pixels with a tolerance for anti-aliasing) on 8 page types × desktop and mobile. |
| `tools/tests/browser/page-loader.js` | Loader: first page of a visit only, real progress 0→100 %, closes within 2.9 s (slow network), reduced motion, JavaScript off, mobile, cached, CLS. |
| `tools/build_assets.py --check` | The served bundles match their sources. |
| Regression, accessibility and link suites | Functional behaviour, WCAG A/AA (axe), no broken links. |

## 7. Rollback

Each step is its own commit and can be reverted on its own:

| Commit | What | Revert effect |
|---|---|---|
| `531dea2` | Layout component split | Restores the single `site-header.php`. Output identical either way. |
| `62b7a26` | CSS source split | Restores the hand-edited stylesheets. Output identical either way. |
| `fc68f41` | JS source split | Restores the hand-edited scripts. Output identical either way. |
| `42b468e`, `6db047f` | Footer / header design changes | Independent of package pages. |

## 8. Checklist for every header/footer change

1. **State the scope first:** change type, expected impact, files, affected page types, tests, rollback.
2. **Edit only the component's files:** `include/global/<component>.php`, `css/src/components/<component>.css`, `js/src/…/<component>.js`.
3. **Rebuild:** run `build_assets.py`.
4. **Test:** `component-change.js`, `isolation.js` before/after, and the suites.
5. **Commit:** one commit per component ("Update Holiday Guru header" / "Update Holiday Guru footer"). Never mix page changes in.
