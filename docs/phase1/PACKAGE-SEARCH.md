# Package search results: implementation notes

File: `public_html/tours.php`. Behaviour lives in `assets/js/hg-ui.js` and styles in `assets/css/hg-ui.css`.

## Search context

- The search carries five parameters: `destination`, `date` (Y-m-d), `adults`, `children` and `departure` (delhi or haridwar).
- They are validated in `hg_search_state()`.
- They flow from the homepage hero or header search → `/tours?destination=…` → a 302 redirect to `/tours/{key}`. A place such as Gulmarg also adds a `place[]` filter.
- From the results they continue to the package page, then into its enquiry form and WhatsApp message.
- **Departure city is context, not a hard filter.** A package that starts at the destination (Kashmir starts in Srinagar) stays listed for "Delhi". Only a package with a *different* fixed start city is excluded.

## Filters and sort

**Facets.** Each facet's counts are computed with the other active filters applied. A facet option with zero matches is disabled.

- places covered
- duration (3–4, 5–6, 7–8 and 9+ days)
- features (houseboat, helicopter option, Volvo, pilgrimage)
- departure city

**Sort:**

- recommended (complete data first)
- duration, ascending or descending
- name

There is no price sort, because no prices exist yet.

**Paging:** 8 results per page.

**Chips:**

- one chip per active filter, with Clear all
- a date chip
- "From Delhi"

## Without a page reload

- Filters, sort, chips and paging update through fetch plus `history.pushState` on real URLs.
- Back, forward and refresh reproduce the same state, because the server renders every URL.
- If the fetch fails, the browser falls back to normal navigation.
- An `aria-live` region announces the new result count.

## Mobile

- A compact search summary opens the header form as a full-screen search editor.
- Filters open as a bottom sheet (a modal dialog with a focus trap and Escape to close) that applies live.
- A sticky Call / WhatsApp / Enquire bar sits at the bottom, and the floating widget is lifted above it.

## SEO

- Any URL with parameters is `noindex,follow`, with a canonical to the clean URL.
- `/tours/{key}` is indexable only when that destination's content is `approved`.
- Schema: ItemList, plus TouristDestination and FAQPage when destination content exists.

## Other features

- **Wishlist:** kept in `localStorage` on the device only.
- **Analytics:** events go through `hgTrack` and are sent only after analytics consent. None contain personal data. Events:
  - `search_submit`, `search_suggestion_select`
  - `filter_apply`, `sort_change`, `results_page`
  - `wishlist_add`, `wishlist_remove`
  - `enquiry_start`
- **Empty and error states:** both offer recovery actions (clear filters, plan a customized holiday, WhatsApp).
- **Autocomplete:** tolerates a single-letter typo. Choosing a destination fills the field; choosing a package opens it with the search context. Escape closes the suggestions without clearing the typed text.
