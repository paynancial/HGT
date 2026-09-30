# Global component isolation: architecture audit (30 Sep 2026)

**Owner rule (permanent):** "Changing header or footer must not impact other page content."

This audit checks whether the current code can honour that rule, and lists the gaps that need a refactor. **No refactor has been done.** It needs your approval first.

**Labels:** VERIFIED (tested), INFERRED (read from the code, not tested), GAP (needs work).

## 1. Current structure

```
hg_layout_start()  include/ui/core.php
├── <head>                     hg_head($meta): page-specific title, meta, canonical, schema (from the page)
├── include/partials/site-header.php
│     ├── Utility bar          .hg-utility
│     ├── Header + search      .hg-header, #hg-header-search
│     ├── Navigation + mega    .hg-nav, #mega-india / #mega-intl / #mega-spec
│     └── Login dialog         include/partials/login-dialog.php
├── <main id="main" class="hg-main">   PAGE CONTENT ONLY (breadcrumbs + page module)
hg_layout_end()
├── include/support-widget.php .hg-support
├── include/cookie-consent.php #hg-consent
└── include/partials/site-footer.php   .hg-footer (links from include/footer_nav.php)
```

## 2. Compliance by rule area

| Area | Status | Evidence |
|---|---|---|
| Page content in its own container | **VERIFIED** | Every page renders inside `<main id="main">`; header and footer partials are outside it. |
| Header / footer independently changeable | **VERIFIED** | Footer (`42b468e`) and header (`6db047f`) were changed today. Page bodies stayed identical on 8 page types × 2 widths: DOM, geometry, SEO head, forms, links and pixels. |
| SEO isolation | **VERIFIED** | Title, description, canonical, robots, OG and JSON-LD come only from the page's `$meta`. They were identical before and after both global changes. Organization schema is the only global schema. |
| Duplicate IDs | **VERIFIED** | None found across all 141 pages. Global IDs are namespaced (`hg-header-search`, `mega-*`, `hg-consent`, `footer-*`). |
| JavaScript isolation | **INFERRED (good)** | Behaviour is bound through `data-hg-*` attributes or IDs (e.g. `[data-hg-fnav]` for the footer, `[data-hg-mega]` for the menu). The few element selectors in `hg-ui.js` are scoped inside a given form. No page-wide `.button` or `form` selectors exist. |
| Data isolation | **VERIFIED** | Contact details, social links and footer links live in `site_config.php` and `footer_nav.php`. Package data, rates and the Package ID registry are in `include/data/`. They don't reference each other. |
| CSS namespacing | **VERIFIED** | Components use `hg-` prefixed class names (`.hg-header`, `.hg-footer`, `.hg-rcard`, `.hg-pkghead`, `.hg-ithead`, …). All footer rules are scoped to `.hg-footer`. |
| Base (design-system) CSS | **INFERRED (intended)** | `hg-ui.css` sets baseline rules for `.hg-body` `a`, `h1–h4`, `p`, `ul/ol`, `img` and form controls. These are the global typography baseline, which the rule allows. Components override them with their own scoped selectors. The footer needed `.hg-footer …` specificity for this. |
| **Separate include per shell component** | **GAP** | Utility bar, header, search, navigation and mega menu are all in one file, `site-header.php`. They work independently but are edited in one file. |
| **Page-specific CSS ownership** | **GAP** | `hg-ui.css` (~1,000 lines) holds the design tokens, base, header, and the page modules (results, tour detail, itinerary, contact, …) in one file. The class names are namespaced, so there are no collisions today. But a change to one page's styles is committed in the same file as global styles, which makes rollback per component coarser. |
| Legacy `.container` class | **INFERRED (low risk)** | `.hg-body .container` is used only by the footer. It's a generic name, but no page module uses it (checked by search). |
| Automated isolation test | **VERIFIED** | `tools/tests/browser/isolation.js`: before/after capture of page bodies. Run it for every header or footer change. |

## 3. Proposed refactor (not started, needs approval)

Each step is its own commit and can be reverted on its own. Each is verified with `isolation.js` (no page body change).

1. **Split `site-header.php`** into:
   - `include/partials/utility-bar.php`
   - `header.php` (logo, search, contact)
   - `navigation.php` (tabs and mega menus)
   - `site-header.php` becomes a three-line wrapper that includes them. The HTML output is unchanged.
2. **Split `hg-ui.css`** into:
   - `assets/css/global/` (tokens and base)
   - `components/` (header, footer, cards, forms)
   - `pages/` (home, results, tour-detail, itinerary, contact, guide)
   - They are concatenated into the same single stylesheet at build time, so page weight and HTTP requests stay the same, and the output is identical.
3. **Move `hg-site.css`'s** support-widget, consent and footer rules into `components/`.
4. **Add a CSS lint** that fails on new unscoped selectors outside `global/`.

**Risk:** low, provided the rendered HTML and CSS stay byte-identical. The isolation test proves it.

**Effort:** one working session.

## 4. Checklist for every future header/footer change

- State the change type (GLOBAL HEADER / GLOBAL FOOTER / PAGE-SPECIFIC), the expected impact, the files, the affected page types, the tests and the rollback path. Do this before editing.
- Capture the baseline (`isolation.js before`), make the change, compare (`isolation.js after`).
- Run the footer, regression, accessibility and link suites.
- Make one commit per component: "Update Holiday Guru header" or "Update Holiday Guru footer". Never mix page changes into it.
