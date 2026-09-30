# Tour Package CMS (staging)

**Status: staging design and implementation, awaiting owner approval.** It is not deployed, it has no production database, and no Package ID has been assigned.

This is the admin application for Holiday Guru Travel tour packages. It covers:

- package create → edit → review → approve → publish → pause → archive → restore;
- the Offer Zone;
- CRM enquiries.

Labels: **VERIFIED** (tested), **INFERRED** (read from code or design, not tested).

## Identifier rule (owner, final)

> "No Tour Code, Only Package ID."

| Identifier | Format | What it is |
|---|---|---|
| **Package ID** | `0001`–`9999` | The **only** package identifier. The itinerary, CRM, quotations and payments all carry it. It is permanent once assigned, read-only, and never reused. |
| **Offer Code** | `OF-0001`… | A separate promotion object with its own sequence. One package can have several offers, and one offer can cover several packages. |
| `package_pk` | integer | An internal database row key. It is never shown publicly. It exists so a draft can be written before its Package ID is approved. |

There is **no Tour No.** anywhere in this CMS. Every "Tour No." in the brief and the blueprint is implemented as the Package ID.

Top Tour ranking (priority rank 1–500) is internal merchandising. It is not an identifier and is never shown publicly.

## Where things are

```
cms/                         the application — NOT inside public_html, so a website upload never includes it
  public/                    web root of the CMS only (front controller, router, CSS/JS)
  src/                       bootstrap, permissions, domain (packages.php, media.php), controllers, views
  schema/sqlite.sql          staging database
  schema/mysql.sql           proposed production schema (MySQL 8 / MariaDB 10.6+)
  bin/setup.php              creates the staging DB, imports the website's package data (read-only), staging users
  config.example.php         configuration template (no secrets); real config.php is gitignored
  tests/unit.php             domain tests (throwaway DB)
  tests/browser/             end-to-end and accessibility tests
  storage/                   staging DB, uploads, staging logins (gitignored)
docs/cms/                    this documentation
```

## Run on staging

```bash
cp cms/config.example.php cms/config.php      # set site_root to the staging website copy
php cms/bin/setup.php --reset                 # fresh staging DB + 107 website packages + one user per role
php -S 127.0.0.1:8099 -t cms/public cms/public/router.php
# logins: cms/storage/.staging-credentials (random passwords, staging only)
```

On Apache, the document root must be `cms/public` (it has an `.htaccess`). Do not use the repository root. `setup.php` refuses to run when `env` is `production`.

## Screens (brief §53)

| # | Screen | URL |
|---|---|---|
| 1 | Dashboard | `/` |
| 2 | All Packages (search: Package ID, name, destination, status, Offer Code) | `/packages` |
| 3 | Add Package | `/packages/new` |
| 4 | Edit — Basic Information | `/packages/{pk}?tab=basic` |
| 5 | Itinerary Editor | `?tab=itinerary` |
| 6 | Images & Media | `?tab=media` |
| 7 | Pricing & Availability | `?tab=pricing` |
| 8 | Inclusions & Exclusions | `?tab=inclusions` |
| 9 | Add-ons | `?tab=addons` |
| 10 | Offer Zone (per package) + `/offers` | `?tab=offers` |
| 11 | SEO & AEO | `?tab=seo` |
| 12 | Advanced | `?tab=advanced` |
| 13 | Version History (+ restore) | `?tab=history`, `/packages/{pk}/versions/{v}` |
| 14 | Package Preview | `/packages/{pk}/preview` |
| 15 | CRM Enquiry Detail | `/enquiries/{id}` |
| 16 | Mobile package editor | same URLs at ≤ 640 px (sticky Preview / Save / Publish bar) |

Also built:

- Media Library
- Top Tour Collection
- Featured Tours
- Price overview
- Destinations
- SEO dashboard
- Users (with the role matrix)
- Activity Log
- **Recycle Bin**

Sidebar items that are not built yet are greyed out and labelled "planned": Customers, Quotations, Bookings, Payments, Reviews, CMS Pages, Travel Guides, FAQs, Metadata, Schema, Internal Links and Settings.

## Documents

- [DATA-DICTIONARY.md](DATA-DICTIONARY.md): every table and field
- [PACKAGE-RULES.md](PACKAGE-RULES.md): Package ID, itinerary, pricing, inclusions/exclusions, add-ons, offers, SEO/AEO, publishing, roles, deletion
- [CRM-MAPPING.md](CRM-MAPPING.md): Package ID → enquiry → lead → quotation → payment → booking
- [API-CONTRACT.md](API-CONTRACT.md) and [openapi.yaml](openapi.yaml)
- [MIGRATION-PLAN.md](MIGRATION-PLAN.md): staging → production, and connecting the website
- [TESTING.md](TESTING.md): how to run every test, with the latest results
- The identifier background lives in [../package-registry/IDENTIFIERS.md](../package-registry/IDENTIFIERS.md), and the proposed mapping in `../top-tours/`.
