# Top Tour Packages: design, SEO, AEO and CMS/CRM architecture (Phase 1, staging)

**Date:** 30 Sep 2026
**Branch:** `claude/wonderful-meitner-k9xu1y`
**Status:** built and tested on the staging server. Nothing is deployed.

**Labels:** every result is marked VERIFIED (tested here), FAILED, BLOCKED (needs something from the owner or a third party) or INFERRED (reasoned, not tested).

**Owner decisions applied:**
- Header tabs stay as set on 30 Sep: Domestic · International · Inbound Tours · Special Tours · Customised Tours · Offers · About Us · Contact Us.
- The utility bar stays as set on 30 Sep: "Premium Holiday Planner" on the left, Login on the right.
- Screenshots are delivered separately from the code ZIP.
- **Package ID replaces "Tour No."** (owner, 30 Sep). It is shown only in the itinerary header, uses one sequence for every package type (domestic, international, special, offers), and is carried in enquiries, WhatsApp, CRM search, quotations, payments and bookings.
- The brief's nav list (Flights, Visa, Corporate, Forex) and the utility-bar list were **not** applied, per the owner's answer.

---

## A. Package ID architecture — VERIFIED (code), BLOCKED (assignment awaits owner approval)

**Identifiers**
- `package_pk` is the internal relational key: a BIGINT auto-increment in `schema-draft.sql`.
- `package_id` is the business identifier: `CHAR(4)`, NOT NULL, UNIQUE, CHECK `^[0-9]{4}$` and not `0000`.
- Until the database exists, the internal key is the permanent slug key (`slug:<slug>`), sent in enquiries as "Internal ref".
- One sequence for every package type (domestic, international, special, offer packages), so IDs never repeat.

**Label:** **Package ID 0001**.

**Display rules** (`include/package_registry.php`)
- A number is shown only when its registry entry is `approved`, or `retired` for history.
- `proposed` numbers show only on a local test server with the preview switch on. The live site can never show them (host check).

**Where it appears once approved (owner decision: itinerary only):**
- on the website, **only** in the itinerary header on the package page. It is not shown on cards, the title area, breadcrumbs or search suggestions;
- in the WhatsApp message, the enquiry email (field and subject) and CRM, quotation, payment and booking records;
- in structured data (`identifier`, approved IDs only), matching the visible itinerary header.

**Permanence**
- `package_id_registry` is an append-only ledger with triggers: no change, no delete, no reuse. Retired numbers stay reserved.
- New numbers come from `create_package()`, which locks a one-row counter; it never uses `MAX()+1`.
- Tested: 8 processes × 12 packages gave 96 unique numbers. After 9999, creation stops.

**Migration mapping**
- The proposed mapping for all 107 existing packages is `docs/package-registry/PACKAGE-ID-MAPPING.csv` (0001–0107), explained in `PACKAGE-ID-MAPPING.md`.
- **Nothing is assigned** until the owner approves it.

## B. Top 500 internal curation — VERIFIED

**Data**
- `include/data/curation.json` is the interim store. The CMS table is `package_curation` in the schema.
- Fields: `priority_rank` (1–500, unique), `is_featured`, `is_top_priority`, `homepage_featured`, `search_featured`, `seasonal_featured`, `speciality_featured`.
- Validation:
  - ranks outside 1–500 are ignored (PHP);
  - duplicate ranks and rank 501 are rejected (database tests).

**Where it is used**
- **Recommended sort:** priority rank first, then search-featured, then complete itineraries, then shorter trips.
- **Homepage "Featured tours":** uses `homepage_featured` tours ordered by rank when any exist. Otherwise it falls back to the editor selection.

**Never shown publicly:** ranks, "Top 500" and totals. The data file sits in `include/`, which the web server denies (403).
- Today `curation.json` is empty. The owner or CMS fills it.

## C. Public count suppression — VERIFIED

**Removed:**
- the results count ("N packages found") and filter option counts "(N)";
- page numbers that implied totals (now Previous · Page N · More tours);
- mega-menu "N tours" and destination-card "N packages" (cards now show trip lengths, e.g. "4–8 day trips · North India");
- homepage hero and stats counts (replaced with non-count facts);
- counts on the About, region, pilgrimage, HTML sitemap and search-suggestion pages;
- counts in meta descriptions;
- `numberOfItems` in list structured data;
- `count` fields in the public search index.

**Content text rephrased:** "Our 13/22 … itineraries", "22 of our packages", "Two packages include airfare".

**Scan:** `counts.py` checked **147 pages** (all sitemap URLs plus results, filter, paging and search variants) for number + packages/tours/itineraries/results, "found", "available", "Top 500" and "(N)". **0 hits.**

## D. Search Hero → Results → Tour Detail flow — VERIFIED

**Homepage search**
- "SEARCH HOLIDAY PACKAGES": Destination, Travel date, Adults, Children, Departure city.
- Button: "Explore holiday packages →".
- It goes to `/tours/{destination}` with the date, travellers and departure in the URL.
- The header search is prefilled from that state.

**State carried forward:** results filters and sort (AJAX, with back/forward and refresh) → tour page ("Your trip: …") → enquiry form fields → WhatsApp text → enquiry email.

**Search by Package ID:** "0002" or "Package ID 0002" opens that tour, keeping the context. Approved numbers only.

**Tests:** `flow.js` (30/30) and `tour.js` (32/32).

## E. Itinerary architecture — VERIFIED

- The itinerary is part of its tour page (`#itinerary`), never a separate unrelated page.
- **Itinerary header:** Package ID · Tour · Duration · Destination · Price (and Rate valid when a rate exists).
- **Each day shows:**
  - title and route or distance;
  - the day's details from the package's own page;
  - the overnight place (and houseboat).
- **Also shown:** a "Where you stay" table, and a route with a Google Maps link.
- Days with no published text say so honestly ("confirmed with your itinerary") rather than inventing activity.

## F. Pricing / version architecture — VERIFIED (logic and tests), BLOCKED (no rates published)

**Data:** `include/data/rates.json` has one row per price version, maintained by hand and never hard-coded. Fields:
- `slug`, `package_id`, `version`
- `base_price`, `currency`, `price_unit`
- `rate_status` (draft | approved | withdrawn)
- `rate_valid_from` / `rate_valid_until`, `rate_updated_at`, `approved_by`, `price_notes`
- optional adult, child, single-supplement, extra-bed, tax, discount and add-ons

**Public display** (`hg_current_rate`): shows only the highest *approved* version whose validity includes today.
- Expired, future, draft or zero-price versions are never shown. The page falls back to **Price on request / Get current price**.

**When a rate exists, it shows:**
- Current rate with "Rate valid: 1 Oct 2026 – 31 Oct 2026" and "Price version N";
- "Starting from" on cards, with "Rate valid until";
- price sorts and a budget filter (both appear only when rates exist);
- an `Offer` in structured data with price and validity.

**Standard package cost statement:** "excludes airfare, train fare and bus fare unless specifically mentioned in the package inclusions". It sits in the Inclusions section and the Price section, at full text size.

**Tests:** 10 PHP unit tests on versions and validity, and 11 browser tests with staging-only test rates. All passed.

**Today:** `rates.json` is empty, so every tour shows Price on request (BLOCKED: owner to supply approved rates).

## G. Pay Now architecture — VERIFIED (honest disabled state), BLOCKED (no gateway)

**Layout:** [ Pay Now ] [ Enquire Now ] at the top of every tour, and again in the book card.

**When Pay Now is active:** only when `HG_PAYMENT_ENABLED` is true **and** the tour has a current approved rate.
- Today it is a disabled button with an explanation. No payment is simulated.

**Payment records** (`payments` table): Package ID, package_pk, tour name snapshot, rate_id and rate version, amount computed on the server, currency, travel date, travellers, customer, and the enquiry/quotation reference.
- `paid_at` is set only from a verified gateway webhook.

**Pending:** the gateway choice and the `/pay` page. BLOCKED: owner decision on the gateway and on advance vs. full payment.

## H. Enquire Now / Contact Us integration — VERIFIED

**Form:** the existing Contact Us enquiry system (`mail.php`), Enquiry type **TOUR PACKAGE ENQUIRY**.

**Sent fields:**
- Package ID, Package name, Internal ref, Destination
- Travel date, Adults, Children, Departure city
- Displayed rate, Rate version, Rate validity
- Package URL, Page, UTM source/medium/campaign

**Anti-spoofing:** the server builds the Package ID, package name, internal ref and rate details from its own data, using the package URL. Submitted values are ignored.
- Tested by tampering with the hidden fields: the email still carried the true values.

**Email subject:** "Website enquiry: {name} – Package ID 0002 {tour name}".

## I. CRM-ready mapping — VERIFIED (schema tests), INFERRED (future CRM)

**Chain:** Package ID → enquiry → lead → quotation → payment → booking.
- Every table keeps `package_pk` + `package_id` + a name snapshot + the rate version.
- Payments and bookings **require** a Package ID.

**Search:** the CMS and CRM can search by Package ID (unique index), name, destination and type.

**Tests:** `docs/package-registry/tests/registry_test.php`: **32/32** on a throwaway MariaDB test database.

## J. SEO architecture — VERIFIED

**URLs and indexing**
- Clean URLs with no `.php`. The Package ID is not in URLs or titles.
- Unique titles and descriptions.
- Canonicals on every indexable page. Any filtered, sorted, paged or searched URL is `noindex` with a canonical to the clean URL, so filter combinations never become indexable pages.
- `/tours` (all results) is noindex.

**sitemap.xml:** 139 URLs. The two URLs containing an en dash are now percent-encoded (they were raw UTF-8).

## K. AEO architecture — VERIFIED

**Destination pages answer, answer-first:**
- how many days;
- best time;
- cost factors (no invented prices);
- places, things to do, stays and transport, who the trip suits;
- tips and FAQs.

**Tour pages answer:**
- what is included and excluded;
- whether flights, trains or buses are included (from the package's own inclusions);
- hotels, how to book, extra adults and children;
- whether the tour can be customised.

## L. Rich-content architecture — VERIFIED

**A tour page includes:**
- summary, highlights (only those the itinerary mentions) and quick facts;
- day-wise itinerary with header, route and map;
- hotels table, meals, transport;
- inclusions and exclusions, with the standard exclusion note;
- price, validity and version when available, and how the package is priced (from its own terms);
- customisation, FAQs;
- destination guide (best time, transport) and similar tours.

**Reviews:** a placeholder shows only on staging. No verified reviews exist, so none are shown and no rating markup exists. BLOCKED: a verified review source.

## M. Internal linking — VERIFIED

**Path:** Home → Domestic / International / Inbound → destination (`/tours/{key}`) → tour → itinerary section.

**Also linked:**
- tour ↔ destination guide ("Planning your trip");
- similar tours;
- Customised Tours ("Customize this package");
- speciality (Pilgrimage) pages.

**Crawl** (`crawl.py`): 151 pages and assets, **0 broken links**, 0 `.php` links.

## N. Local SEO readiness — VERIFIED (existing office only)

- There is one genuine location, the Noida office. It is described in Organization/TravelAgency structured data with address and phone, on the Contact page (with map) and in the footer.
- No city or office landing pages were created, and no invented locations or reviews.

## O. Structured data — VERIFIED

**Validation** (`schema_check.py`) ran on **139 pages**, with 0 problems. It checked:
- valid JSON-LD with `@context`;
- no Rating or Review markup;
- any Offer price matches the visible price;
- BreadcrumbList matches the visible breadcrumb exactly;
- every FAQ question is visible on the page;
- a Package ID in markup must be visible.

**Types in use:** Organization/TravelAgency, WebSite, BreadcrumbList, FAQPage, TouristTrip (107), TouristDestination (13), ItemList, AboutPage, ContactPage, CollectionPage, Article.

**Offer** is added only for a current approved rate. It was tested with staging-only rates.

## P. Mobile — VERIFIED

**Page order:** header → compact search bar ("Kashmir · 15 Oct 2026 · 2 Adults · From Delhi · Edit") → "Recommended Kashmir tours" → Filter & sort (bottom sheet) → tour cards.

**Tour card:** image, title, duration, facts, price, CTAs (no Package ID on cards, per owner decision).

**Sticky bar:** Call · WhatsApp · Enquire.

**Tour page:** title, price, [Pay Now] [Enquire Now] above the fold; Package ID in the itinerary header.

**Checked widths:** 375, 390, 768, 1024, 1280 and 1440, with no horizontal scroll.

## Q. Accessibility — VERIFIED (automated)

**axe-core checks:**
- 24 key pages × 4 widths: 96/96 clean;
- 10 approved pages × 4 widths: 40/40;
- all 107 tour pages × 2 widths: 214/214 clean, repeated with Package ID preview on.

**Built in:**
- semantic landmarks, labelled filters and sorting, keyboard sheets and Escape;
- visible focus, 44px+ targets;
- contrast-checked orange text (#A84B00).

**Not done:** a manual screen-reader pass is recommended before launch (INFERRED).

## R. Performance — VERIFIED (architecture), INFERRED (at 500 tours)

**Results page**
- Paginated at 8 per page, with lazy-loaded, sized images.
- No count queries. Rates are cached once per request.
- Minimal JS (`hg-ui.js`); filters use AJAX fragments.
- The search index is a small static JSON loaded on focus.

**At 500 tours:** file-based data is fine as a stand-in. At that size the CMS database (indexed `package_id` and slug) should serve pages, with page caching. INFERRED; not load-tested.

## S. Files changed

**New**
- `public_html/include/package_registry.php`
- `public_html/include/data/package-registry.json`, `rates.json`, `curation.json`
- `tools/propose_package_ids.py`
- `tools/tests/package_registry_test.php`
- `docs/package-registry/PACKAGE-ID-MAPPING.md` and `.csv`
- `docs/top-tours/TOP-TOUR-PACKAGES.md`
- `docs/package-registry/schema-draft.sql`: now committed. It was previously excluded by `.gitignore` `*.sql`; an exception was added.

**Changed**
- **Pages:** `public_html/tours.php`, `include/templates/package-detail.php`, `include/templates/region.php`, `index.php`, `about.php`, `religious-tour.php`, `sitemap.php`, `cancellation-policy.php`
- **Shared code:** `include/ui/core.php`, `include/partials/site-header.php`
- **Enquiry mail:** `mail.php`, `include/mail_helper.php` (test-only `HGT_MAIL_DUMP`, set only in the staging server environment)
- **Assets:** `assets/js/hg-ui.js`, `assets/css/hg-ui.css`, `assets/data/search-index.json`
- **Data and content:** `sitemap.xml`, `include/content/{dubai,sikkim-darjeeling,uttarakhand}.php`
- **Tools, tests and config:** `tools/build_package_data.py`, `docs/package-registry/tests/registry_test.php`, `.gitignore`

**Bug fixed along the way:** duration ranges ("4–8 days") printed only "–8 days". PHP read `$min–` as a variable name. It now prints correctly.

## T. Tests performed (all on the staging server)

| Suite | Result |
|---|---|
| `tools/tests/package_registry_test.php` (Package ID rules, rates, validity, Pay Now, enquiry context, curation, committed registry integrity) | 32/32 VERIFIED |
| `docs/package-registry/tests/registry_test.php` (MariaDB: sequence, concurrency, permanence, rates, CRM, curation) | 32/32 VERIFIED |
| `tour.js`: browser test of Package ID live/preview, rates and validity, Pay Now, search by number, email content, spoofing | 32/32 VERIFIED |
| `test.js`: site regression | 105/105 VERIFIED |
| `flow.js`: search → results → filters → tour | 30/30 VERIFIED |
| `footer2.js`: footer | 28/28 VERIFIED |
| `a11y.js` / `a11y-drafts.js` / `pkgs-a11y.js` (axe) | 96/96, 40/40, 214/214 VERIFIED |
| `counts.py`: public count scan | 147 pages, 0 hits VERIFIED |
| `schema_check.py`: structured data | 139 pages, 0 problems VERIFIED |
| `crawl.py`: links | 151 checked, 0 problems VERIFIED |
| PHP error log during all runs | 0 warnings VERIFIED |

## U. Screenshots (real browser renders of staging; delivered separately, not in the ZIP)

- **Settings:** desktop 1440 px, mobile 390 px at 2×.
- **Package ID preview is on,** so the proposed IDs are shown in the itinerary header and labelled "Proposed · preview only".
- **Prices** are the real state (Price on request). Images are blank placeholders until the owner supplies photos.

**Desktop**
- `01-homepage-hero-search.png`
- `02-tour-search-results.png`
- `03-tour-detail-top.png`
- `04-tour-detail-itinerary.png`
- `05-tour-detail-pricing.png`
- `06-tour-detail-inclusions.png`
- `07-tour-detail-reviews-faq.png`
- `08-full-tour-detail.png`

**Mobile**
- `09-mobile-home-search.png`
- `10-mobile-results.png`
- `11-mobile-tour-detail.png`
- `12-mobile-itinerary.png`
- `13-mobile-enquiry.png`
- `14-mobile-footer.png`

## V. ZIP

- **Code:** `HolidayGuruTravel_TopTourPackages_Phase1.zip`, containing `/code`, `/css`, `/js`, `/components`, `/config-examples` and `/docs`.
  - No screenshots (owner instruction).
  - No passwords, secrets or real config.
- **Screenshots:** sent as a separate file, `HolidayGuruTravel_TopTourPackages_Phase1_Screenshots.zip`.

## W. Git commit

See the commit on `claude/wonderful-meitner-k9xu1y`; the hash is reported in the delivery message.

## X. Remaining blockers (owner)

1. **Approve the Package ID mapping.** Merge or retire the overlap pairs and confirm the order. Package ID stays hidden until then.
2. **Old database dump and `admin/` code,** to include any packages that exist only in the database.
3. **Approved rates with validity** for each tour. Until then the site shows Price on request, and price sort and the budget filter stay hidden.
4. **Payment gateway and advance/full decision,** for Pay Now.
5. **"Popular" and "Newest" sorts:** they need real popularity data (enquiries or bookings) and publish dates. They were not added rather than faked.
6. **Verified review source** (e.g. Google Business Profile, with permission) for ratings and reviews.
7. **Tour and destination photos.** Placeholders are blank.
8. **Departure-city pages:** none were created (no inventory, no demand data).
9. **Deployment** only on the owner's go-ahead.
