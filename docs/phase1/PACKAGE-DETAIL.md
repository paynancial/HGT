# Package detail page: implementation notes

- **Template:** `public_html/include/templates/package-detail.php`.
- **Applied to:** `/srinagar-gulmarg-pahalgam-tour-package-5-days`. The URL, title and description are unchanged.
- **Other packages:** they stay on their legacy pages until approved. Converting one takes a three-line wrapper page, after copying its original source into `tools/package-sources/`.

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
   - A route link opens Google Maps directions, with no embedded map and no API key.
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
