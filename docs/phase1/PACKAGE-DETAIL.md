# Package detail page: implementation notes

- **Template:** `public_html/include/templates/package-detail.php`.
- **Applied to:** 93 of 105 package pages. URLs, titles and descriptions are unchanged, except that double spaces in 13 old titles were normalised. Each converted page is a small wrapper; its original is kept byte-for-byte in `tools/package-sources/`, which is not web-served, so the data build still reads it. The rebuilt data was confirmed byte-identical.
- **Held on their legacy pages (12)**, because their content has problems to fix first:

| Package | Problem |
|---|---|
| `best-of-shimla-vacation`, `munnar-alleppey-kovalam-06-days`, `srinagar-gulmarg-pahalgam-tour-package-7-days` | No itinerary on the page |
| `munnar-alleppey-kovalam-05-days`, `nainital-with-almora-and-jim-corbett-05-days`, `ooty-mysore-04-days`, `yamunotri-gangotri-do-dham-from-delhi-6n-7d` | Stated duration doesn't match the itinerary days |
| `char-dham-yatra-from-delhi-11n-12d` | URL says Delhi, but day 1 starts elsewhere |
| `amritsar-with-dalhousie-dharamshala-05-days`, `manali-with-manikaran`, `munnar-thekkady-alleppey--kovalam-kanyakumari-07-days` | Exclusion or terms text listed under Inclusions |
| `uttarakhand-mussoorie-with-rishikesh-flight-inclusive` | Named "flight-inclusive" but no flight listed |

  To convert one after fixing it: copy the page to `tools/package-sources/`, replace it with the wrapper, and rebuild the data.
- **Canonical fix:** the legacy `chardham-yatra-from-haridwar-8n-9d` page declared its canonical as a *different* package (`char-dham-yatra-from-haridwar-9n-10d`). The template gives every page a self-canonical. This is INFERRED to be a copy-paste error; the owner should confirm.

## Sections

All sections are built from the package's own data.

1. **Summary**
   - image, feature badges, title, duration and route
   - hotel, meals and transfers
   - description
   - "Your trip" context (date · travellers · departure)
   - Save and Share (native share, copy link, WhatsApp, email)
2. **Section tabs** (sticky), with an Enquire now button on desktop.
3. **Highlights:** shown only where the itinerary or inclusions mention them.
4. **Quick facts.**
5. **Day-wise itinerary**
   - Headings come from the source.
   - "Overnight" comes from the day's place.
   - A day with no text says so; nothing is invented.
   - A route link opens Google Maps directions (no embedded map, no API key), but only when the itinerary names the overnight places in order. Otherwise the page lists the places covered with no directions link. A single-destination package gets a map search link instead. International places are not suffixed with ", India".
6. **Inclusions and exclusions**
   - Both lists are shown verbatim.
   - The travel-ticket note appears here: see "Travel-ticket rule" below.
   - Important information and booking terms follow.
7. **Where you stay**
   - Nights per place, taken from the itinerary.
   - The houseboat night count comes from the inclusions.
   - "Similar standard" appears only when the package's terms say so.
8. **Price**
   - "Price on request", plus pricing facts quoted from this package's terms: GST, extra adult and child rates, 35% advance, payment methods.
9. **Customize, package FAQs** (with FAQPage schema), **destination planning notes, similar packages.**
10. **Booking sidebar**
    - price state, and whether air, train or bus fare is included
    - **[Pay Now] (disabled) and [Enquire Now]** side by side, with the explanation "Online payment isn't available yet…"
    - the enquiry form
    - the contextual WhatsApp button
    - trust points (only verifiable ones)
11. **Mobile:** sticky Call / WhatsApp / Enquire bar.

## Travel-ticket rule

- **Standard:** "Standard package cost excludes airfare, train fare and bus fare unless specifically mentioned in the package inclusions."
- **Exception:** a package whose inclusions list tickets names them instead. Today that is 4 Volvo packages and 3 Dubai packages.

## Enquiry payload

The form posts to `/mail.php` (the existing Contact Us workflow).

| Field | Value |
|---|---|
| `enquiry_type` | `Tour Package Enquiry` |
| `package`, `package_url`, `destination` | This package |
| `travel_date`, `adults`, `children`, `departure_city` | Prefilled from the search context |
| `displayed_rate` | `Price on request` |
| `utm_source`, `utm_medium`, `utm_campaign` | When present |

`mail.php` takes the package name from site data using the URL, so a tampered `package` field is ignored.

## Pending the owner's decision (docs/package-registry/PROPOSAL.md)

- **Package No.:** shown only after the registry is approved. Dev mode shows a placeholder note.
- **Rates:** real rates, validity, versions and price breakdown, from CMS rates.
- **Pay Now:** activated only once a gateway exists and approved rates can be paid.

## Schema

- TouristTrip, with the itinerary as an ItemList.
- FAQPage and BreadcrumbList.
- No Offer and no AggregateRating, since there are no prices and no reviews.
