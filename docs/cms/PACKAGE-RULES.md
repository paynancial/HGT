# Package rules

These are the business rules the CMS enforces.

- Where a rule lives in code, the file is named.
- **VERIFIED** means an automated test checks it (`cms/tests/unit.php`, `cms/tests/browser/cms-e2e.js`).
- **INFERRED** means it is enforced in code but has no dedicated test.

## 1. Package ID (the only package identifier)

**Format**

- Four digits, `0001`–`9999`. `0000` is invalid.
- The database refuses anything else: `CHECK` in `schema/*.sql`. VERIFIED on SQLite and MariaDB.
- There is no Tour No. and no second business number.

**States** (`packages.package_id_status`)

| State | Shown in the CMS | Shown publicly |
|---|---|---|
| `pending` | "Pending approval" | Nothing |
| `proposed` | The mapping number, dashed, labelled "Proposed · pending owner approval" | Nothing (VERIFIED) |
| `approved` | The number, read-only | In the itinerary header only, and in enquiry/CRM payloads |

**Assignment**

- Only Super Admin can assign. It stays **switched off** (`config package_id_assignment = false`) until the owner approves `docs/top-tours/OWNER-PACKAGE-ID-DECISIONS.csv`. VERIFIED: the button is disabled.
- Imported website packages take their approved mapping number.
- New packages take the next number in the `package_id` sequence. The sequence starts after the highest mapped number (0107), so new packages would start at 0108. VERIFIED.

**Permanence**

- Once assigned, a Package ID never changes, and it cannot be assigned twice.
- Deleting a package never frees its number: the sequence only moves forward. VERIFIED.
- Changing the name, URL, itinerary, price or offers never changes the Package ID. VERIFIED.

## 2. Itinerary

- The itinerary belongs to the package (`itinerary_days.package_pk`). Every saved version carries the Package ID (`package_versions.package_id`).
- The editor header shows Package ID, package name, duration and day count.
- Each day has these fields: day number, title, destination, route, description, sightseeing, activities, meals, hotel, transport, optional activities, important notes and image.
- Actions: Add Day, Duplicate Day, Delete Day, Reorder (drag, or the up/down buttons). All VERIFIED.

**Duration validation**

- The day count is compared with the package duration. A mismatch shows "WARNING — Itinerary contains 5 days but package duration is 4 days." VERIFIED.
- A mismatch blocks Publish until it is corrected, or until an explicit override reason is recorded. Only Super Admin, Admin or Package Manager can record an override. VERIFIED.
- The nights must equal days − 1 or days.

## 3. Pricing

- Rates are manual CMS entries. There are no hard-coded prices.
- Every change creates a **new price version** (`rate_versions`, unique per package). Approved rows are never edited, only withdrawn. VERIFIED.
- Each version records: base price, currency (INR), unit, valid from/until, status (draft/approved/withdrawn), optional components (adult, child, single supplement, extra bed, seasonal note, tax %, discount), notes, reason, who and when, and who approved it.
- **Who can do what:**
  - Pricing Manager, Finance, Admin and Super Admin approve rates.
  - A Travel Consultant can only save a draft (request).
  - A change after version 1 needs a reason.
- **Current rate:** the newest approved version whose validity includes today. VERIFIED.
- **Expired rate:** never shown as current. The website shows "Price on request" / "Get current price". A validity that has already ended cannot be entered. VERIFIED.
- **Quotations and payments** keep the `price_version` they used (CRM-MAPPING.md).

**Pay Now** is active only when all of these hold (`pkg_paynow()`):

- a current approved rate exists;
- a gateway is connected (`payment_gateway`, currently false);
- the package is marked payable;
- the package is published.

Otherwise it shows an honest disabled state with the reason. Payment success is never simulated. VERIFIED.

## 4. Inclusions and exclusions

- Each item has: category, name, description, icon, sort order and status. Every item can be edited, reordered and removed.
- **Standard rule:** every package has three fixed exclusions, Airfare, Train fare and Bus fare (`is_standard = 1`).
  - They cannot be deleted or renamed. VERIFIED.
  - One can be hidden only when an active inclusion names it, e.g. "Airfare from Delhi". VERIFIED.
- **Standard statement**, shown on the pricing tab and in the preview: "Standard package cost excludes airfare, train fare and bus fare unless specifically mentioned in the package inclusions." Local transfers are a separate inclusion category.
- **Imported website items** are marked "Imported". Some of them are really payment terms, so the editor asks for them to be recategorised.

## 5. Add-ons

- Each add-on has: ID (internal `AO-nnnn`), name, description, price (empty = on request), currency, unit, tax %, required/optional, availability, sort order and status.
- **Units:** per person, per couple, per room, per vehicle, per day, flat fee.
- An add-on that is available **and** active must have a price. VERIFIED (validation message).
- Unavailable add-ons cannot be selected. The preview disables them (INFERRED from code). The intake API ignores them. VERIFIED in code path.
- A Travel Consultant's add-ons are saved hidden (suggested). Pricing Manager, Admin or Super Admin activates them.
- **Customer view:** base price + selected add-ons = quote total. A total appears only when a current rate exists.

## 6. Offers (Offer Zone)

- Codes run `OF-0001`, `OF-0002`… from their **own** sequence (`sequences.offer_code`). Creating offers never moves the Package ID sequence. VERIFIED.
- The database rejects an offer code shaped like a Package ID. VERIFIED.
- **Fields:** code, name, type (percentage / flat / value-add), discount, validity, minimum value, usage limit, eligible packages, eligible destinations, eligible package types, customer eligibility, terms, status.
- One package can have many offers, and one offer can cover many packages. VERIFIED.
- **Publishing an offer:**
  - Only Super Admin, Admin, Pricing Manager or Marketing can publish.
  - It needs dates that have not ended and at least one eligible package, destination or type.
  - At most `offer_zone_limit` (100) offers can be live at once. This limit is internal and never advertised.
- An expired offer is never shown. A package with an expired offer still marked published fails the checklist.
- Offers and Top Tour rank are different objects. The rank lives in the Advanced tab.

## 7. SEO and AEO

- **SEO fields:** meta title (unique across packages, 30–65 characters), meta description (120–160), canonical (absolute https, no `.php`), OG title, OG description, OG image, SEO slug.
- The slug is locked once published. A URL change needs a 301 redirect, which is an Admin task.
- **AEO fields:** primary question, direct answer (15+ words), key facts, supporting questions, FAQs (at least 2, no duplicate questions).
- **Content rules** (editor hints and checklist): no keyword stuffing, copied text, city-name substitutions, AI filler, fake reviews or fake statistics. Lorem ipsum is never used.
- **Rich text** is limited to an allow-list: headings, paragraphs, lists, links, tables, callout/tip/note asides and images. Scripts, event handlers and `javascript:` links are stripped. VERIFIED.

## 8. No thin pages: the publication checklist

`pkg_checklist()`: a package cannot be published merely because it has a title, an image and a price.

| Group | Mandatory (blocks Publish) | Warning only |
|---|---|---|
| Content | Description of 80+ words · 3+ highlights · itinerary with every day described · duration matches or has an override · inclusions · exclusions including the 3 standard rules · valid destination | Short description 120–300 characters |
| Commercial | Rate validity dates · Enquire Now enabled · no expired offer still published | No current rate ("Price on request" is allowed) |
| SEO & AEO | Meta title · meta description · canonical · AEO question and answer · 2+ FAQs | |
| Media | Exactly one featured image · alt text on every image | Gallery of 3+ images |

- With a mandatory item missing, **Publish is disabled** and Save Draft still works. VERIFIED.
- Tabs show ✓ (complete), a red ! (mandatory missing) or a yellow ! (warning).

## 9. Publishing workflow

```
Draft → Submit for review → Content review → SEO/AEO review → Pricing review → Approval → Publish
                                   (sign-off or "request changes" → back to Draft)
Published ⇄ Paused        any → Archived (Recycle Bin) → Restore → Draft
```

- Sign-offs apply to one package version. Any content change after sign-off sends the package back to "In review" for fresh sign-offs.
- Approve needs all three sign-offs. Publish needs approval plus a clean checklist. VERIFIED.
- Editing a published package keeps the live version (`published_version`) and shows "unpublished changes" until someone publishes again. VERIFIED.
- Staging "Publish" only changes the CMS status. It never writes to the website (MIGRATION-PLAN.md).

## 10. Roles

The matrix is in `cms/src/permissions.php` and on the Users screen. It follows the blueprint's "Roles & Permissions" sheet and adds Package Manager and Reviewer from the brief.

| Action | Roles |
|---|---|
| Publish, approve, archive, restore | Super Admin, Admin |
| Pause | Super Admin, Admin, Package Manager |
| Delete permanently | Super Admin only |
| Assign Package ID | Super Admin only |
| Approve rates | Super Admin, Admin, Pricing Manager, Finance |
| Content / SEO / pricing sign-off | The matching specialist roles, plus Reviewer, Admin and Super Admin |

Examples:

- Content Manager has no Publish button. VERIFIED.
- Reviewer cannot open CRM enquiries. VERIFIED.

## 11. Deletion, the Recycle Bin and restore

- **Archive is preferred.** Archived packages leave the website, become read-only and appear in the **Recycle Bin**, where they can be restored to draft. VERIFIED.
- **Permanent delete** is Super Admin only. It is allowed only for drafts that were never published, have no approved Package ID and have no enquiries. Everything else is "kept for audit". VERIFIED.
- **Version history:** every save stores a full snapshot, and nothing is overwritten.
  - **Restore this version** brings back the content (details, itinerary, inclusions, add-ons, SEO/AEO, FAQs, images) as a *new* version. VERIFIED.
  - The Package ID, price versions and offers are never rolled back. VERIFIED.
- **Activity log:** insert-only. A database trigger blocks UPDATE and DELETE. VERIFIED on SQLite and MariaDB.

## 12. Data integrity

- No fabricated prices, ratings, reviews, hotels, availability, traveller counts, package counts, Package IDs, offers or statistics.
- **Imported data** is shown exactly as it exists on the website today:
  - no website package has a rate, so all show "Price on request";
  - none has a usable image file in the repository, so all show "No featured image".
- **Test data** created by the tests is labelled "Staging test" or "Test record".
- Internal counts appear only inside the CMS. The public website never shows package totals.
