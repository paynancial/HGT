# Tour No. mapping: proposed, awaiting owner approval

**Status: PROPOSED.** No Tour No. is permanent yet. The live website shows **no** Tour No. until you approve this mapping.

- Review sheet: [`TOUR-NUMBER-MAPPING.csv`](TOUR-NUMBER-MAPPING.csv) has 107 rows, one per current package.
- Registry file: `public_html/include/data/tour-registry.json` (every entry has `"status": "proposed"`).
- Generator: `tools/propose_tour_numbers.py`. It is append-only, so approved numbers are never renumbered or reused.
- **Owner review pack** (overlap analysis and decision table): [`docs/top-tours/FINAL-TOUR-NUMBER-MAPPING.md`](../top-tours/FINAL-TOUR-NUMBER-MAPPING.md)

## What was inspected

- **All 107 package pages** are in `include/data/packages.json`, built from the page sources. They supplied each package's name, URL, destination, duration, itinerary and data warnings.
- **Prices:** no package has an approved, current rate. Every row therefore reads *Price on request*.
- **Status:** every package is a published static page.
- **The old database was not inspected.** It holds the `yatra_package` and `theme_package` tables and the `admin/` code, and neither was provided. If it holds packages that are not on the website, they need numbers too. Send the database backup before approval so those packages can be added to the review pack.

## Proposed numbering

- **Order:**
  1. Destinations in the website's display order: Kashmir, Leh Ladakh, Himachal Pradesh, Uttarakhand, Char Dham, Amarnath, Darjeeling & Sikkim, Kerala, South India, Goa, Dubai, Singapore & Malaysia, Maldives.
  2. Within each destination, by trip length.
  3. Then by name.
- **Result:** 0001 (Srinagar Gulmarg Tour Package) through 0107.
- **Format:** four digits, zero-padded (`0001`), never `0000`. Numbers are stored as text, so the leading zeros are kept.
- **Public label:** **Tour No. 0001**. It is used the same way on every page.

## Review notes in the CSV

- **"possible overlap"**: two packages cover the same places and differ in length by at most one day. For each pair, decide: keep both (each gets its own number), merge, or retire one. Retired packages keep their number forever.
- **"no day-by-day itinerary"**: the page has no itinerary yet. It can still be numbered; the itinerary is added later under the same number.

## Rules once approved (enforced in code and in the database design)

| Rule | Where it is enforced |
|---|---|
| Unique, four digits, not 0000 | `tools/tests/tour_registry_test.php` and a database CHECK constraint |
| Never changed, reused or recycled; retired numbers stay reserved | `tour_number_registry` ledger and triggers in `schema-draft.sql`; the append-only generator |
| Unaffected by name, URL, itinerary or price changes | The number is kept apart from the slug and the rate versions; `package_slug_history` holds old URLs for 301 redirects |
| New numbers never use `MAX()+1` | `create_package()` locks a one-row counter (`SELECT … FOR UPDATE`); tested with 8 parallel processes |
| Kept in enquiry, quotation, payment and booking records | `package_id` and `tour_number` columns on every CRM table |

## To approve

1. Mark any changes in the CSV: merge or retire pairs, and a different order if you prefer one.
2. Reply "Approve Tour No. mapping" (with your changes, if any).
3. I set the approved entries to `"status": "approved"` and rebuild the search index.
   - Tour No. then appears on cards, tour pages, itinerary headers, breadcrumbs, WhatsApp messages and enquiry emails.
   - Search by number, such as "0001", starts working.
4. After approval, the mapping is permanent.
