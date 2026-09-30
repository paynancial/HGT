# Phase 1: implementation notes

Branch `claude/wonderful-meitner-k9xu1y`. Nothing has been deployed. No production database or production site was touched.

Status labels used in this document:

| Label | Meaning |
|---|---|
| VERIFIED | Checked on the local test server. |
| INFERRED | Reasoned, not checked. |
| BLOCKED | Needs something we do not have yet. |

## Architecture

- **No framework.** New pages use `include/ui/core.php`:
  - data access: `hg_packages()`, `hg_package()`, `hg_groups()`, `hg_group()`
  - search state: `hg_search_state()`, `hg_context_query()`, `hg_resolve_destination()`
  - `hg_head()` handles canonical, robots and JSON-LD
  - `hg_layout_start()` / `hg_layout_end()` handle the header, footer, support widget, cookie consent and login dialog
  - UI components: cards, FAQ, enquiry form, and others
- **Templates:**
  - `include/templates/package-detail.php` renders any package from its data.
  - `include/templates/region.php` renders the India and International listings.
- **Data:** `tools/build_package_data.py` extracts `include/data/packages.json` (105 packages) and `assets/data/search-index.json` from the real package pages.
  - Pages converted to the template keep their original source in `tools/package-sources/`, so the build still reads them. The rebuilt output was confirmed byte-identical (VERIFIED).
- **Destination content:** `include/content/{key}.php`, currently only `kashmir.php`. Each file has a `status`, and a page is indexable only when that status is `approved`.
- **Styles and scripts:** `assets/css/hg-ui.css` holds the design system and `assets/js/hg-ui.js` the behaviour. Neither uses a framework, jQuery or Font Awesome.
- **Fonts:** self-hosted (Plus Jakarta Sans and Inter) with `font-display: optional`, which prevents layout shift. On a first visit over a slow connection, pages may show the fallback font.
- **Security:** `/include/` is now denied to browsers (403), in addition to `/PHPMailer/`.

## Pages

| URL | What it is | Index |
|---|---|---|
| `/` | Homepage; search posts destination, date, adults, children and departure to `/tours` | index |
| `/tours`, `/tours/{destination}` | Search results with facets, sort, paging, chips, empty and error states | noindex |
| `/tours/kashmir` | Kashmir destination and results page; `/kashmir-tour` 301-redirects here | noindex (content is draft) |
| 93 package pages, e.g. `/srinagar-gulmarg-pahalgam-tour-package-5-days` | Package detail template; 12 packages are held on legacy pages until their data is fixed (see PACKAGE-DETAIL.md) | index; noindex when opened with search parameters |
| `/domestic-holidays`, `/international-holidays` | Region listings built from real packages | index |
| `/religious-tour` | Pilgrimage speciality page | index |
| `/customized-holidays` | Trip request form, enquiry type "Customized Holiday" | index; noindex with parameters |
| `/india-tours` | Inbound page for foreign travellers | noindex (draft) |
| `/faqs` | FAQs taken from the package terms | noindex (draft) |
| `/travel-guide/kashmir` | Article | noindex (draft) |
| `/contact`, `/about` | Rebuilt; address, map embed, legal name and CIN kept | index |

Draft pages become indexable when their `status` is changed to `approved` after owner review.

## Data honesty (no fabrication)

- **Prices:** none exist, so every price shows "Price on request". There is no budget filter and no price sort.
- **Removed fabricated ratings:** the legacy listing pages showed "Rated 5.00 / 4.8" on every package. The rebuilt pages do not.
- **Reviews:** there are none, so none are shown and there is no AggregateRating. In dev mode a note marks the gap.
- **Package numbers:** none have been assigned. This waits on the owner's decision on `docs/package-registry/PROPOSAL.md`.
- **Pay Now:** shown but disabled, because no payment gateway exists.
- **Claims tied to each package's own data:** the following appear only when that package's own terms or inclusions say so:
  - "hotel of similar standard"
  - the number of houseboat nights
  - tickets included
  - GST and extra-person rates
- **Pilgrimage facts** come from our itineraries. For example, the road packages state that the Kedarnath helicopter ticket is not included.
- **External links:** `/religious-tour` keeps the legacy links to yatrapackages.com, marked as external. The owner should confirm that relationship.

## Removed or changed legacy content (owner to confirm)

- **About:** kept the owner's story, mission, expertise, personalized-service and customer-centric wording. Removed claims we cannot evidence: "extensive network of partners worldwide", "sustainability" and "cutting-edge technology". The old text is in git history.
- **Contact title:** "Leading Travel Agency in Delhi & DMC India" became "Travel Agency in Noida, Delhi NCR". The office is in Noida, and "leading" is unverifiable.

## Tests (local server, VERIFIED)

| Suite | Result |
|---|---|
| `test.js`: Phase 0 regression plus the new package page context | 109 / 109 |
| `footer2.js`: footer, including axe | 28 / 28 |
| `flow.js`: home → results → filters, sort, back/forward/refresh → package → enquiry; mobile sheets | 30 / 30 |
| `a11y.js`: axe WCAG 2.2 A/AA, overflow and JS errors, 16 pages × 4 widths (1440, 1280, 820, 375) | 64 / 64 |
| `pkgs-a11y.js`: the same checks on all 93 template package pages at 1440 and 375 | 186 / 186 |
| Routing: 127 sitemap URLs return 200; 126 `.php` URLs 301 to the clean URL; 404s; PHPMailer and `/include/` return 403; security headers present; `GET /mail.php` returns 405 | pass |
| Registry schema (`docs/package-registry/tests`) on local MariaDB | 28 / 28 |

**Lighthouse (mobile, local server, not production):**

| Page | Performance | Accessibility | Best practices | SEO | Notes |
|---|---|---|---|---|---|
| `/` | 99 | 100 | 96 | 100 | CLS 0.013, LCP 1.8 s |
| `/tours/kashmir` | 99 | 99 | 96 | 69 | SEO is 69 only because the page is intentionally noindex (draft) |
| Package page | 99 | 100 | 96 | 100 | |

Best-practices is 96 because `/assets/img/favicon.png` is missing: the image library was not supplied.

## BLOCKED

- Image library (`assets/img/`); pages show labelled placeholders in dev mode.
- Database dump and `admin/` code.
- A payment gateway.
- Real SMTP send; the password must still be rotated.
- Google Search Console, GA4 verification and Google Business Profile.
- Privacy policy text.
- Production Core Web Vitals.
- Owner review of draft content.
