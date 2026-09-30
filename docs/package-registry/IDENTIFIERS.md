# Package ID and Offer Code: identifier model

**Owner rule (30 Sep 2026): only the Package ID identifies a package.** "Tour No." is not used anywhere. Offers have their own, separate Offer Code.

| Identifier | Purpose | Scope | Mutable? | Example | Stored as |
|---|---|---|---|---|---|
| **Package ID** | The one package identifier. The itinerary is keyed by it; CRM search, quotations, payments, bookings and reporting use it | Package and its itinerary | No (never changed or reused) | `0001` | `packages.package_id` CHAR(4), issued by `create_package()` from `package_id_sequence` |
| **Offer Code** | Promotion identifier | Offer | No | `OF-0001` | `offers.offer_code` CHAR(7), from its own `offer_code_sequence` |
| *(internal key)* | Database relational key only; never shown to customers or staff | Database | No | `package_pk` = 847 | `packages.package_pk` BIGINT |

## Relationships

```
Package ID (0001) ──► itinerary ──► day 1, day 2, day 3 …
Offer (OF-0001) ◄──► packages      (a package can have several offers; an offer can cover several packages)
enquiry / lead / quotation / payment / booking ──► Package ID + Offer Code (when used) + rate version
```

## Rules

- **One Package ID sequence** (0001–9999) is shared by domestic, international and speciality packages.
- **Offers never take a Package ID number.** Offer Codes come from their own `OF-####` sequence.
- The two formats cannot collide: a Package ID is 4 digits, an Offer Code starts with `OF-`. This is enforced by CHECK constraints and tested.
- Changing or creating an offer never changes a Package ID or consumes one (tested).
- **Package IDs are proposed (0001–0107) and not assigned.** See `PACKAGE-ID-MAPPING.md` and the reconciliation review in `docs/top-tours/`.
- Until the database exists, the internal key is the TEMPORARY reference `slug:<page URL>`. It is sent in enquiries as "Internal ref" and is **not** a CRM ID.

## Where each appears

| | Package ID | Offer Code |
|---|---|---|
| Itinerary header on the package page | ✓ (once approved) | — |
| Cards, title area, breadcrumb, search suggestions | — | — |
| WhatsApp message | ✓ (once approved) | — |
| Enquiry email | ✓ ("Not assigned yet" until approved) | ✓ (only a published offer valid for that package) |
| Quotation / payment / booking records | ✓ | ✓ (when an offer applied) |
| Search | ✓ ("0001" opens the package, approved IDs only) | — |

## Implementation

- **Website:** `public_html/include/package_registry.php`, with this data:
  - `include/data/package-registry.json`;
  - `offers.json` (empty until offers are published);
  - `rates.json`.
- **Database design:** `schema-draft.sql`:
  - `packages`, `package_id_sequence`, `package_id_registry`;
  - `offers`, `offer_code_sequence`, `offer_packages`, `create_offer()`;
  - `offer_code` on the enquiry, quotation, payment and booking tables.
- **Tests:**
  - `tools/tests/package_registry_test.php` (40 checks);
  - `docs/package-registry/tests/registry_test.php` (37 checks on a throwaway database).
