# Package ID mapping: proposed, awaiting owner approval

**Status: PROPOSED.** No Package ID is permanent yet. The live website shows **no** Package ID until you approve this mapping.

- Review sheet: [`PACKAGE-ID-MAPPING.csv`](PACKAGE-ID-MAPPING.csv) has 107 rows, one per current package.
- Registry file: `public_html/include/data/package-registry.json` (every entry has `"status": "proposed"`).
- Generator: `tools/propose_package_ids.py`. It is append-only, so approved IDs are never renumbered or reused.
- **Owner review pack** (overlap analysis and decision table): [`docs/top-tours/FINAL-PACKAGE-ID-MAPPING.md`](../top-tours/FINAL-PACKAGE-ID-MAPPING.md)

## What the Package ID is (owner decision, 30 Sep 2026)

- **Package ID** (e.g. `0001`) is the permanent business identifier of a package. It replaces "Tour No.".
- **One sequence covers every package type**: domestic, international, special (pilgrimage, honeymoon, …) and offer packages. No two packages can ever share an ID.
- **On the website** it appears **only in the itinerary header** of the package page. It is not shown on cards, the title area, breadcrumbs or search suggestions.
- **Behind the scenes** it is carried in:
  - enquiry emails (field and subject) and WhatsApp messages;
  - CRM search, quotations, payments and bookings.
- **The internal database key** is separate (`package_pk`) and is never shown. Until the database exists, the internal key is `slug:<page URL>`, sent in enquiries as "Internal ref".

## What was inspected

- **All 107 package pages** are in `include/data/packages.json`, built from the page sources.
- **Prices:** no package has an approved, current rate, so every row reads *Price on request*.
- **Status:** every package is a published static page.
- **The old database was not inspected.** It holds the `yatra_package` and `theme_package` tables and the `admin/` code, and neither was provided. If it holds packages that are not on the website, they need IDs too. Send the database backup before approval so those packages can be added to the review pack.

## Proposed numbering

- **Order:**
  1. Destinations in the website's display order.
  2. Within each destination, by trip length.
  3. Then by name.
- **Result:** 0001 (Srinagar Gulmarg Tour Package) through 0107.
- **Format:** four digits, zero-padded, never `0000`. IDs are stored as text, so the leading zeros are kept.

## Rules once approved (enforced in code and in the database design)

| Rule | Where it is enforced |
|---|---|
| Unique, four digits, not 0000 | `tools/tests/package_registry_test.php` and a database CHECK constraint |
| Never changed, reused or recycled; retired IDs stay reserved | `package_id_registry` ledger and triggers in `schema-draft.sql`; the append-only generator |
| Unaffected by name, URL, itinerary, price, image or offer changes | The ID is kept apart from the slug, content and rate versions; `package_slug_history` holds old URLs for 301 redirects |
| New IDs never use `MAX()+1` | `create_package()` locks a one-row counter (`SELECT … FOR UPDATE`); tested with 8 parallel processes |
| Kept in enquiry, lead, quotation, payment and booking records | `package_pk` + `package_id` columns on every CRM table |

## To approve

1. Fill in the Owner Decision column in the review pack (APPROVED / MERGED into … / RETIRED / PENDING), or reply with only the rows you want changed.
2. The approved entries are set to `"status": "approved"` and the search index is rebuilt.
   - The Package ID then appears in the itinerary header, WhatsApp messages and enquiry emails.
   - Staff can search "0001" to find the package.
3. After approval, the mapping is permanent.
