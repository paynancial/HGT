# Testing guide

Every test runs on staging only. The browser tests refuse to run against `holidaygurutravel.in`.

| Test | Command | What it covers | Latest result |
|---|---|---|---|
| Domain unit tests | `php cms/tests/unit.php` | Package ID generation and permanence; proposed IDs kept internal; itinerary → Package ID; duration mismatch and override; price versions, expiry → Price on request; standard exclusions; thin-page blockers; Offer Code sequence and separation; search by Package ID and Offer Code; rich-text sanitiser; insert-only activity log; image validation, WebP variants, crop; role rules. Uses a throwaway database. | **47/47** |
| End-to-end | `php cms/bin/setup.php --reset`, start the server, then `node cms/tests/browser/cms-e2e.js` | The whole brief §54 list in a real browser (see below) | **68/68** |
| Accessibility + layout | `node cms/tests/browser/cms-a11y.js` (after e2e) | axe-core WCAG 2.2 A/AA, horizontal overflow and JS errors on 31 screens × 1440/768/390 px, plus login | **94/94** |
| MySQL schema | `mariadb test_db < cms/schema/mysql.sql` | Tables, checks, unique featured image, insert-only triggers | Loads cleanly; constraint checks rejected bad data as expected |

## Brief §54 → e2e checks

| Brief test | Result |
|---|---|
| Create package → Package ID | Created as a draft. The Package ID shows "Pending approval" because assignment stays off until owner approval; generation is proven in the unit tests. |
| Itinerary → linked to Package ID; change itinerary → Package ID unchanged | ✓ (unit + e2e) |
| Price update → new price version | ✓ v1 → v2, history shows old → new |
| Price expiry → Price on request | ✓ (unit test; e2e checks an ended validity cannot be entered) |
| Add inclusion / exclusion / add-on → preview updates | ✓ |
| Assign offer → Offer Code separate | ✓ OF-0001, shown separately from the Package ID |
| Enquire Now → Tour Package Enquiry | ✓ type, Package ID "Not assigned yet", rate, price version, Offer Code |
| Pay Now → current price version | ✓ disabled (no gateway); the API reports the current price version |
| Search by Package ID / by Offer Code | ✓ (the Tour No. search does not exist, by owner rule) |
| Header/footer update → package editor/content unaffected | ✓ header, footer, utility bar and site CSS are changed in the staging website copy; editor and preview hashes (HTML + computed styles) are identical; files restored |

Also covered:

- review sign-offs by the right roles;
- role limits: no Publish for Content Manager, 403 on enquiries for Reviewer;
- publish blocked until approval;
- unpublished-changes indicator;
- version restore;
- Recycle Bin archive and restore;
- activity log entries;
- no console errors.

## Test data

The e2e test creates a package, placeholder images (solid colour with "STAGING TEST IMAGE"), a rate, an offer and an enquiry. Each is labelled "Staging test" or "Test record". Nothing is real, and nothing reaches the website.
