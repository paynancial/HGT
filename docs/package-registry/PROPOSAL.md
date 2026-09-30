# Package number, pricing and payment: architecture proposal (A–J)

Status: **proposal for owner approval.** Nothing here has been run against production. No package numbers have been assigned. No prices exist in the code.

Every claim below is labelled with one of these statuses:

| Label | Meaning |
|---|---|
| VERIFIED | Checked in this repository or tested locally. |
| INFERRED | Reasoned from code, but not confirmed against the real database. |
| BLOCKED | Needs something we do not have yet. |

Files in this folder:

- `schema-draft.sql` is the proposed MySQL/MariaDB schema. It has only been run on a throwaway local database.
- `tests/registry_test.php` holds 28 tests covering the section 44 rules. All 28 pass on local MariaDB 10.11 (VERIFIED).
- `package-inventory-for-review.csv` lists every package we can see today. The number column is intentionally blank.

---

## A. Current package-data structure

**1. Public package pages: 105 static PHP files (VERIFIED)**

- Each package is one hand-written page, for example `/srinagar-gulmarg-pahalgam-tour-package-5-days`. The URL slug is the only identifier.
- These pages are what the public site links to and what the sitemap lists.
- Phase 1 extracts them into `include/data/packages.json` using `tools/build_package_data.py`. That file is read-only and has no ID or price field.

**2. Legacy database templates (VERIFIED from code; database contents BLOCKED)**

These templates query a MySQL database through `admin/db.php`:

- `packages.php`, `package-details.php`
- `destinations.php`, `destination_detail.php`
- `themes-packages.php`, `theme-package-details.php`

Tables referenced in the code:

| Table | Columns / purpose |
|---|---|
| `yatra_package` | id, title, days, description, banner, thumb_img, meta_tags, category |
| `theme_package` | Same shape as `yatra_package` |
| `category`, `theme_category` | Categories |
| `tour_table`, `theme_tour_table` | Itinerary days: main_id, heading, description |
| `other_info`, `theme_other_info` | Tabs: main_id, tab_head, tab_details (inclusions, exclusions, terms) |
| `destination`, `destination_package` | Destination ↔ package link |

We do not have `admin/db.php`, the admin code or a database dump. So we cannot tell:

- how many database packages exist;
- whether they duplicate the 105 static pages;
- whether any live page still uses these tables.

(BLOCKED)

**3. Prices: none (VERIFIED)**

- No legacy query or page references a package price.
- None of the 105 package pages shows a package price. The only rupee amount is "Travel/health Insurance (INR 5000* EXTRA)" on the Maldives page.
- The terms tabs carry percentages only: GST 5% extra; extra adult 35% and extra child 25% in 44 packages (40% and 35% in 3); and a 35% advance to confirm a booking in 45 packages.
- The site therefore shows "Price on request" everywhere, which is correct under rule 36.

**4. Enquiries (VERIFIED)**

- All forms post to `mail.php`, the Contact Us workflow. It sends an email only; nothing is stored and there is no CRM.
- It already accepts these optional fields: `enquiry_type`, `package`, `destination`, `travel_date`, `adults`, `children`, `departure_city`.

**5. Payments (VERIFIED)**

- No payment gateway, code or credentials exist anywhere in the site.

**6. CMS/admin (BLOCKED)**

- The `admin/` folder was not in the supplied files.

**7. Data problems found (VERIFIED; see the CSV)**

- 12 packages have problems:
  - 4 have fewer itinerary days than the stated duration.
  - 3 have no itinerary.
  - 1 says "from Delhi" but day 1 starts elsewhere.
  - 2 have exclusion or terms text listed under Inclusions.
  - `uttarakhand-mussoorie-with-rishikesh-flight-inclusive` is named "flight inclusive" but lists no flight.
- **7 packages explicitly include external travel.** This matters for rule 18: they must be configured as the documented exception, not labelled "excluded".
  - Volvo bus seats: `best-of-manali-with-delhi-by-volvo`, `best-of-shimla`, `best-of-shimla-and-manali`, `manali-volvo-vrip-weekend`.
  - Economy airfare: `standard-tour-to-dubai-4n5d`, `deluxe-tour-to-dubai-4n5d`, `dubai-family-trip-with-free-burj-khalifa-tickets`.
- Many packages list "Pick up & drop from railway station / bus stand / airport". These are **local transfers** (rule 20), not tickets. The CSV keeps them separate.

## B. Proposed package-number architecture

**Keys (rules 3 and 7)**

- `packages.package_pk` is a BIGINT auto-increment and serves as the internal relational key. All foreign keys use it.
- `packages.package_id` is `CHAR(4)`, NOT NULL and UNIQUE, with a CHECK of `^[0-9]{4}$` that excludes `0000`.
  - It is stored as a string so the leading zeros are kept. "0001" is never the integer 1.

**Issuing numbers**

- Numbers are issued only by the `create_package()` procedure. It works as follows:
  1. Lock a single-row counter with `SELECT … FOR UPDATE`.
  2. Issue `LPAD(n, 4, '0')`.
  3. Write the package and the registry row.
  4. Increment the counter in the same transaction.
- This never uses `MAX()+1`, so it is safe under concurrency.
  - Tested: 8 processes each created 12 packages at the same time. Result: 96 unique numbers with no gaps (VERIFIED locally).

**Permanence**

`package_id_registry` is an append-only ledger. Triggers enforce these rules:

| Action | Result |
|---|---|
| Changing a package's number | Blocked |
| Deleting a package | Blocked; packages are `archived` or `retired` instead |
| Deleting or updating a registry row | Blocked |

A number is therefore never reused, even after retirement. This is tested: after 0001 was retired, the next package received 0003.

**Running out of numbers**

- After 9999, creation stops with the error "owner decision required". There is no roll-over (tested).
- The CMS should warn from about 9000 onwards.

**URLs (rule 32)**

- The slug stays the SEO URL.
- `package_slug_history` records old slugs so renames can 301-redirect while the number stays the same.
- The number is not placed in URLs.

**Search (rule 33)**

- The CMS and CRM can search by `package_id` (unique index), name, destination and type.

**Before the database exists (interim)**

- After you approve the mapping in C, the numbers can live in a committed, append-only `include/data/package-registry.json`.
- A build check would fail on duplicates, non-4-digit values, or any number that has been removed or changed.
- This is migrated into the table later, with the same numbers.

## C. Migration plan: existing package → permanent number

No numbers will be assigned until you approve the reviewed list.

1. **Get the sources.** Provide a database dump (structure and data, from a backup, not live access) and the `admin/` code. (BLOCKED)
2. **Inventory everything.**
   - Merge the 105 static pages (already in the CSV) with the `yatra_package` and `theme_package` rows.
   - Mark duplicates. For example, the same trip may exist both as a database row and as a static page.
   - Mark near-duplicates for your decision, such as the pairs of 5-day and 6-day Munnar variants.
3. **You decide, per row:**
   - Keep (number it), merge into another row, or retire.
   - Retired packages that were published should still receive a number, so historical enquiries can reference them. Please confirm.
4. **You decide the order.** My recommendation: destination display order, then duration, then name. The alternative is the order in which packages were first published, if known.
   - Your examples (0001 New Delhi Tour, 0002 Kashmir Special, 0003 Dubai Explorer) are illustrative. None of those names exist in the current data.
5. **Dry run.** Generate `migration-preview.csv` (number → slug → title) on a copy of the database, and you review it.
6. **One-time run.** A single transaction calls `create_package(…, 'migration')` in the approved order. The CSV becomes the permanent mapping and is archived with the release.
7. **Afterwards.** New packages get numbers only from the CMS.

## D. Price model (rules 14–16, 18–23, 27, 36)

`package_rates` has one row per price version. It holds:

- `base_price` (the standard package cost), `currency` (INR), and `price_unit` (per person twin sharing, per person, per couple, or per group);
- optional `adult_price`, `child_price`, `single_supplement` and `extra_bed`;
- `tax_mode` (included / extra) and `tax_rate_percent`;
- `offer_price`, which needs a reason and must be lower. The database rejects fake discounts (tested).

**External travel (rule 18)**

- The flags `includes_airfare`, `includes_train` and `includes_bus` default to **false**.
- Only an authorised user can switch one on. This is how the 7 exceptions from A.7 are handled.

**Validity**

- `rate_valid_from` is required. `rate_valid_until` may be NULL; in that case the site shows "Current rate subject to confirmation". No date is ever invented.

**Inclusions and exclusions (rules 21–23)**

- They live in `package_rate_items`, tied to the same rate version, with categories:
  - local transfers (`local_transfer`, `vehicle`, `driver`);
  - tickets (`external_air`, `external_train`, `external_bus`).
- Keeping them in separate categories stops local transfers being confused with tickets (rule 20).

**Active rate**

- The website shows only the `package_active_rate` view: approved, already started, not expired, latest version.
- Draft, expired and withdrawn rates are never shown. Instead the site shows **"Price on request"** or "Get current price" (tested).
- Prices are never hard-coded in templates.

**Breakdown (rule 27)**

"View price breakdown" is computed from the active rate:

- standard package cost
- taxes (if extra)
- selected add-ons
- total
- the external-travel line (excluded, or included for the exceptions)

## E. Price-version model (rules 17, 24)

**Versioning**

- Once a rate is approved, its prices, validity, travel flags **and its inclusions/exclusions are locked** by triggers (tested).
- A change means a new version, v+1. The previous version becomes `superseded`. History is never overwritten.
- Workflow: draft → approved (by an authorised user, recorded in `approved_by` / `approved_at`) → superseded, or withdrawn.

**Audit**

`package_audit` is append-only and records:

- package number and entity;
- action;
- old and new values (JSON: price, validity, inclusions, exclusions);
- reason;
- changed_by / changed_at.

## F. Pay Now architecture (rules 10–12, 37)

**Placement**

- Pay Now and Enquire Now sit next to each other on every package page.
- Pay Now is primary (orange). Enquire Now is a strong secondary button (navy).

**When Pay Now is live**

It is live only when all of these hold:

- `pay_now_enabled` is set;
- there is an active approved rate;
- the rate has a `payment_mode` of advance or full;
- a gateway is configured;
- a travel date and travellers are selected.

**When any condition fails**

- The button is visibly disabled and labelled "Online payment not available yet — enquire to book".
- There is no fake flow and no fake success.
- **This is the only state that can ship now**, because no gateway exists.

**Flow once a gateway is chosen**

1. The browser sends `package_pk`, `rate_id`, `rate_version`, date and travellers.
2. The server reloads the active rate. If the version differs from what the customer saw, it shows "The price has changed from ₹X to ₹Y — please confirm" (rule 37).
3. The server computes the amount itself and never trusts an amount from the browser.
4. It collects the customer's name, phone and email, and shows a summary of what is being paid for.
5. It creates a `payments` row, then creates a gateway order.
6. The payment is marked `paid` **only from a signature-verified gateway webhook**, never from the browser redirect.

A `payments` row stores:

- package id, number and name snapshot;
- rate id and version;
- amount and currency;
- payment kind;
- travel date and travellers;
- customer;
- enquiry or quotation reference;
- gateway order and payment IDs.

## G. Enquire Now → Contact Us mapping (rules 6, 13, 34, 38)

Enquire Now uses the existing `mail.php` workflow. There is no separate system.

| Field sent | Value |
|---|---|
| `enquiry_type` | `Tour Package Enquiry` (automatic) |
| `package_id` | e.g. `0025`, only once approved and in the registry |
| `package` | Package name (automatic) |
| `package_url` | Canonical package URL |
| `destination`, `travel_date`, `adults`, `children`, `departure_city` | From the search context (already carried today) |
| `displayed_rate`, `rate_version`, `rate_valid_until` | The rate shown at the moment of enquiry, or "Price on request" |
| `utm_source`, `utm_medium`, `utm_campaign` | From the landing URL, when present |

**Integrity**

- `mail.php` should re-derive the package number, name and current rate **on the server from the package URL**, instead of trusting the hidden fields. The customer cannot type or alter them.
- The email subject becomes: `Tour Package Enquiry — Package No. 0025 — Kashmir Special — {name}`.

**WhatsApp**

The pre-filled message becomes:

> "Hi Holiday Guru Travel, I am interested in Package No. 0025 – Kashmir Special. Please share the current price, inclusions, exclusions and availability."

Until numbers are approved, the message names the package without a number.

**Analytics**

- Events: `package_view`, `enquiry_start`, `enquiry_submit` and `pay_now_start`, with `package_id` and `package_slug` only.
- No names, phone numbers or emails are sent.
- Events are sent only after cookie consent. This already exists in `hgTrack`.

## H. CRM data mapping (rules 4, 7–9, 28, 39)

Each stage keeps `package_pk` (the key), `package_id` and a `package_name` snapshot. Later renames and price changes therefore never alter history (tested).

1. **Enquiry.** `enquiries` stores the reference, type, package, the rate version and price displayed, trip context, customer and UTM.
2. **Quotation.** `quotations` stores the reference, enquiry, package, rate id/version and `rate_updated_at`, date, travellers, amount, breakdown (JSON), validity and status.
3. **Payment.** `payments` stores the fields listed in F.
4. **Booking.** `bookings` is a later phase and links to quotation and payment the same way.

Reporting by package number is a GROUP BY `package_id` across these tables, giving enquiries, quotes, bookings, revenue and source.

Example enquiry payload:

```json
{"enquiry_type":"tour_package","package_pk":847,"package_id":"0025",
 "package_name":"Srinagar Gulmarg Pahalgam Tour Package","package_url":"https://www.holidaygurutravel.in/srinagar-gulmarg-pahalgam-tour-package-5-days",
 "rate_version":null,"displayed_price":null,"displayed_price_label":"Price on request",
 "destination":"Kashmir","travel_date":"2026-10-15","adults":2,"children":0,"departure_city":"Delhi",
 "utm_source":null}
```

This is an illustration only. 847 and 0025 are placeholders, not assigned values.

## I. Public package-page display (rules 5, 25, 26, 40, 41, 45)

**Package card**

1. A small muted "PACKAGE NO. 0025" label above the title (12px, never larger than the title).
2. Name, duration and places.
3. The price block from the active rate: "Starting from ₹X / person", then "Valid until …" or "Current rate subject to confirmation". Otherwise it shows "Price on request".
4. Up to 3 key inclusions, taken from the rate items only.
5. A line "Air / train / bus: not included", or the included item for the 7 exceptions.
6. View details and Enquire Now.

**Package detail page, top section, in this order (rule 40)**

1. Package No.
2. Name
3. Destination and duration
4. Rating, shown only when genuine reviews exist
5. Price and validity
6. Key inclusions
7. Exclusions
8. **[Pay Now] [Enquire Now]**

The itinerary and detailed content follow below.

**Travel disclaimer (rule 41)**

- The standard wording is shown in the price box and in the Inclusions/Exclusions section, not in the footer:
  > "Standard package cost excludes airfare, train fare and bus fare unless specifically mentioned in the package inclusions."

**What can ship before your approval**

- No package number is shown, because none has been assigned.
- The price shows "Price on request", because no approved rates exist.
- Pay Now is shown disabled with the explanation from F.
- The package's real inclusions and exclusions are shown, plus the disclaimer, which the 7 exception packages would not get.

## J. Test plan

**Database tests (`tests/registry_test.php`), 28/28 passing locally (VERIFIED)**

- create → 0001, second → 0002
- edit or rename keeps the number; direct changes to a number are blocked
- archived packages keep their number; deleting a package or releasing a number is blocked; retired numbers are not reused
- duplicate and non-4-digit numbers are rejected
- 8 concurrent writers produce 96 unique numbers
- no roll-over after 9999
- expired, draft and superseded rates are not shown; a new approved version becomes active
- approved rates and their inclusions/exclusions are immutable; history is kept
- fake discounts are rejected; external travel is excluded by default
- enquiries and payments keep the number, rate version and name snapshot

**Still to run once approved and built**

- CMS form tests: the number is system-generated and read-only.
- Site rendering tests (Playwright):
  - active, expired and on-request price states;
  - validity text;
  - inclusions and exclusions per version;
  - the disclaimer and the 7 exceptions;
  - the Pay Now disabled and enabled states;
  - the price-changed reconfirmation.
- `mail.php` payload tests:
  - enquiry type;
  - number and name re-derived on the server from the URL;
  - a tampered package field is ignored.
- Analytics tests: no personal data in events, and nothing is sent before consent.
- Payment tests with the gateway's sandbox: webhook signature, idempotency, amount computed on the server, mismatch on a version change.

## Update (30 Sep 2026): Top Tour Packages brief

- **Terminology decided (owner, 30 Sep 2026):** the business identifier is the **Package ID** (e.g. "Package ID 0001"). It replaces "Tour No." / "package number".
  - The schema column is `package_id` CHAR(4) (tables `package_id_sequence` and `package_id_registry`). The internal relational key is renamed `package_pk`.
  - Domestic, international and speciality packages share one Package ID sequence. **Offers are separate:** Offer Codes `OF-0001`… have their own sequence (see [IDENTIFIERS.md](IDENTIFIERS.md)).
  - On the website the Package ID is shown **only in the itinerary header**. It is carried in enquiries, WhatsApp messages, CRM search, quotations, payments and bookings.
- **Numbering starts at 0001.** The proposed mapping for the 107 current packages is in [PACKAGE-ID-MAPPING.md](PACKAGE-ID-MAPPING.md). It awaits approval.
- **Built on staging:**
  - an interim file-based registry, rates and curation (`public_html/include/package_registry.php`);
  - approved-only Package ID display;
  - rate validity and versions;
  - the Pay Now gate;
  - enquiry context derived on the server;
  - the curation (`package_curation`), `leads` and `bookings` tables.
- **Full report:** [docs/top-tours/TOP-TOUR-PACKAGES.md](../top-tours/TOP-TOUR-PACKAGES.md).

## Decisions needed from the owner

1. Approve this architecture (B, D, E), or send changes.
2. Provide a database dump and the `admin/` code so the inventory (C.1–C.2) can be completed.
3. Choose a numbering order (C.4) and confirm that retired but published packages get numbers.
4. Confirm the 7 external-travel exceptions, and correct the "flight-inclusive" Uttarakhand package.
5. Pay Now: charge the 35% advance (the booking terms of 45 packages) or the full amount? Which gateway (Razorpay, PayU, CCAvenue, …)?
6. Who may approve rates, which CMS users can do so, and where the CMS will live (extend the existing `admin/`, or build new)?
