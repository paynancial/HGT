# Pre-assignment reconciliation: package records

**Stage:** documentation and reconciliation only.
- **No** permanent numbers assigned. The whole registry is still `proposed`.
- **No** page, content or URL change.
- **No** database migration, CRM update, deployment or publication.

**Identifiers.** The **Package ID** is the only package identifier. In this review:
- **Proposed Package ID** is the proposed 4-digit number (0001–0107). It is not assigned.
- **Temporary internal ref** (`slug:<URL>`) is a **TEMPORARY** reference built from the page address, because the old database has not been supplied.
  - It is **not** a permanent CRM ID and must not be used as one.
  - The CRM will use the approved Package ID. The database creates its own internal key.
- **Offer Codes** (`OF-0001`…) have their own sequence and are not part of this review.

**Files**
- `OWNER-PACKAGE-ID-DECISIONS.csv`: the decision matrix. **Owner Decision** and **Final Package ID** are blank.
- `tools/preassignment_reconciliation.py`: re-creates this file from the package data and the original page files.

## A. Summary

- **Records reviewed:** 107 (all website package pages).
- **Overlap pairs reviewed:** 41 pairs, covering 45 records.
- **Records with a data problem at HIGH or CRITICAL severity:** 17.
  - This covers the 17 data-problem records in the earlier review pack, plus conflicts found by also comparing page titles, descriptions and the places named in titles and URLs.
- **Same-title records:** 14.
- **Severity:**
  - CRITICAL: 2
  - HIGH: 15
  - MEDIUM: 36
  - LOW: 45
  - NONE: 9

**Severity scale**
- **CRITICAL:** the package's identity is in doubt (a duplicate page, or a URL that describes a different trip).
- **HIGH:** the trip length is contradicted by the page's own data, or the itinerary is missing.
- **MEDIUM:** missing inclusions or exclusions, or a confusing title.
- **LOW:** only metadata wording or a missing photo.
- **NONE:** consistent.

**The four priority pairs are in owner-decision state:**
- **0054 / 0055:** 0054 is a byte-identical copy of 0055 under a "from Delhi, 11N/12D" URL.
- **0101 / 0102:** identical content; no hotel or category difference exists in the data.
- **0066 / 0067:** 0066's duration label is the only source saying 4N/5D; everything else says 6 days.
- **0013 / 0014:** 0013 has no itinerary anywhere; it is a different trip from 0014.

**The final numbering cannot be generated yet.** The database and `admin/` inventory must be compared first (section H).

## C. Critical mismatches and priority pairs

### C.1 0054 ↔ 0055: Char Dham Yatra

| Field | 0054 | 0055 |
|---|---|---|
| URL | /char-dham-yatra-from-delhi-11n-12d | /chardham-yatra-from-haridwar-8n-9d |
| Title | Char Dham yatra Package from Haridwar for 9 Days | Char Dham yatra Package from Haridwar for 9 Days |
| Page title (metadata) | Char Dham Yatra from Haridwar - 9 Nights/10 Days Spiritual Journey | Char Dham Yatra from Haridwar - 9 Nights/10 Days Spiritual Journey |
| Duration label | 08 Nights / 09 Days | 08 Nights / 09 Days |
| Itinerary days | 10 | 10 |
| Route (overnights) |  |  |
| Start / end | Haridwar / Rishikesh to Haridwar | Haridwar / Rishikesh to Haridwar |
| Hotels | Deluxe | Deluxe |
| Meals | Breakfast & dinner | Breakfast & dinner |
| Inclusions | 8 lines | 8 lines |
| Exclusions | 7 lines | 7 lines |
| Image | assets/img/destination/chardham1.jpg | assets/img/destination/chardham1.jpg |

**Evidence**
- The two original page files are **byte-for-byte identical** (same MD5 in `tools/package-sources/`, matching the old-site copy and the first commit).
- Itinerary, inclusions, exclusions, hotels, meals, image, description and page title are all the same.
- The only difference is the URL.

**Conflict**
- 0054's URL promises "Char Dham from **Delhi**, **11N/12D**". The content is the **Haridwar 8N/9D** tour.
- Inside both pages, the trip length also disagrees with itself:
  - label 8N/9D;
  - itinerary 10 days;
  - page title and description "9 Nights/10 Days".
- A separate Delhi tour already exists: 0058, "Char Dham from Delhi, 10N/11D".

**Required correction.** Either:
- (a) write the real Delhi 11N/12D itinerary for 0054, if that trip is sold; or
- (b) retire 0054 and 301-redirect it to 0055.

Separately, 0055's duration data must be made consistent (8N/9D or 9N/10D).

**Owner Decision:** ______ (APPROVED / MERGED / RETIRED / REVIEW / PENDING). Not decided here.

### C.2 0101 ↔ 0102: Deluxe vs Standard Tour to Dubai

| Field | 0101 Deluxe | 0102 Standard |
|---|---|---|
| Hotel category | not stated | not stated |
| Hotel names | not stated | not stated |
| Room type | twin sharing (inclusions) | twin sharing (inclusions) |
| Meal plan | Breakfast & dinner | Breakfast & dinner |
| Transfers | Seat-in-coach (shared), airport–hotel return | Seat-in-coach (shared), airport–hotel return |
| Activities | half-day city tour, dhow cruise with dinner, desert safari with dinner | half-day city tour, dhow cruise with dinner, desert safari with dinner |
| Price | none published | none published |
| Inclusions | 9 lines | 9 lines |
| Exclusions | 4 lines | 4 lines |
| Day titles | (Dubai) Arrival Dubai · (Dubai)Dubai Desert Safari · (Dubai)Dubai Dhow Cruise · (Dubai)Dubai · (Dubai)Departure | (Dubai) Arrival · (Dubai) Dubai Sightseeing · (Dubai)Dubai-Dhow Cruise · (Dubai)Dubai · (Dubai) Departure |

**Evidence**
- The inclusion lists are identical, line for line, including the economy airfare Delhi–Dubai–Delhi.
- The day-by-day texts are identical. Only the day headings differ: 0101 titles Day 2 "Desert Safari", but its text describes the half-day city tour.
- The exclusions are identical apart from one word.
- The only real differences are the name, the marketing description and one sentence in the terms.
- **No hotel name, star rating or room category appears on either page.** Both terms mention "hotels mentioned", but none are listed.
- Both pages list the same fixed departures, all in **September 2018** (out of date).

**Conflict:** the names claim a Deluxe/Standard distinction that the package data does not contain.

**Status:** **REVIEW: potential duplicate.** No hotel information has been invented.

**Required correction.** Either:
- supply the actual hotels or category for each (then both can be approved); or
- merge them into one Dubai 4N/5D package.

**Owner Decision:** ______

### C.3 0066 ↔ 0067: Darjeeling and Gangtok

| Field | 0066 | 0067 |
|---|---|---|
| URL | /darjeeling-and-gangtok-06-days | /darjeeling-gangtok-05-days |
| Title | Darjeeling and Gangtok Tour | Darjeeling Gangtok Tour |
| Page title (metadata) | Darjeeling and Gangtok Tour 5 Nights / 6 Days | Holiday Guru Travel | Darjeeling Gangtok Tour 4 Nights / 5 Days | Holiday Guru Travel |
| Description | Discover the beauty of Darjeeling and Gangtok with our 6-day tour package. Explore the stu… | Experience the charm of Darjeeling and Gangtok on this 4-day tour. Enjoy the scenic beauty… |
| Duration label | 4 Nights / 5 Days | 4 Nights / 5 Days |
| Itinerary days | 6 | 5 |
| "Cities covered" line | -> Darjeeling - 2Day, Gangtok - 4Day | -> Darjeeling - 2Day, Gangtok - 3Day |
| Route (overnights) | Darjeeling → Gangtok | Darjeeling → Gangtok |
| Hotels / meals | not stated / not stated | not stated / not stated |
| Inclusions / exclusions | 0 / 0 lines | 0 / 0 lines |

**Evidence (0066)**
- Six sources all say 6 days, or 5 nights:
  - the URL (`-06-days`);
  - the page title ("5 Nights / 6 Days");
  - the description ("6-day");
  - the itinerary (6 days);
  - the "cities covered" line (Darjeeling 2 + Gangtok 4).
- **Only the duration label** says 4N/5D.
- The authoritative data appears to be **5N/6D**.

**Once corrected, 0066 and 0067 are different trips:**
- 0066 gives Gangtok sightseeing a full day of its own, with 3 Gangtok nights.
- 0067 combines the transfer and Gangtok sightseeing on day 3.

**Also note:** neither page lists hotels, meals, inclusions or exclusions.

**Required correction:** change 0066's duration label to 5N/6D. Nothing has been changed at this stage.

**Owner Decision:** ______

### C.4 0013 ↔ 0014: Shimla

| Field | 0013 Best of Shimla Vacation | 0014 Best of Shimla |
|---|---|---|
| Duration | 2 Nights / 3 Days | 3 Nights / 4 Days |
| Itinerary days | 0 | 4 |
| Transport | private cab (inclusions) | Volvo seats ex Delhi + shared car in Shimla |
| Meals | Breakfast & dinner | per meal plan, not stated |
| Hotels | "above-stated hotels" (none listed) | "mentioned or similar hotels" (none listed) |
| Inclusions / exclusions | 6 / 4 lines | 5 / 11 lines |

**Where 0013's itinerary was looked for**
- The original page file: its Itinerary tab is present but **empty**.
- HTML comments on that page.
- The first git commit, and the old-site copy (identical file).
- The other package pages.
- **No itinerary for 0013 exists anywhere in the project.** It may exist only in the old database (not supplied).

**Duplicate?** No. 0013 and 0014 are different trips:
- 2N/3D with a private cab and breakfast & dinner, versus 3N/4D by Volvo from Delhi with a shared car;
- different inclusions.

**Suggested:**
- 0014: approve.
- 0013: keep in REVIEW/Draft until the owner supplies the itinerary. No itinerary has been invented.

**Owner Decision:** ______

## D. Duplicate analysis: all overlap pairs

**Method**
- Every pair with the same destination, the same places and lengths within one day was compared on:
  - itinerary text similarity;
  - inclusion similarity;
  - length, start city, hotels and meals;
  - identical original files.
- **Exact duplicates** (identical page files) are listed first.

| Pair | Lengths | Itinerary sim. | Inclusion sim. | Identical file | Finding |
|---|---|---|---|---|---|
| 0009 ↔ 0010 | 5 Nights / 6 Days / 5 Nights / 6 Days | 43% | 100% | no | Different day plans |
| 0011 ↔ 0012 | 7 Nights / 8 Days / 8 Nights / 9 Days | 23% | 100% | no | Different trip length |
| 0013 ↔ 0014 | 2 Nights / 3 Days / 3 Nights / 4 Days | — | 2% | no | Cannot compare: one has no itinerary |
| 0015 ↔ 0019 | 4 Nights / 5 Days / 5 Nights / 6 Days | 95% | 0% | no | Different trip length |
| 0017 ↔ 0020 | 4 Nights / 5 Days / 5 Nights / 6 Days | 39% | 66% | no | Different trip length |
| 0022 ↔ 0023 | 5 Nights / 6 Days / 5 Nights / 6 Days | 68% | 4% | no | Similar; different meals/inclusions: see row notes |
| 0036 ↔ 0040 | 4 Nights / 5 Days / 5 Nights / 6 Days | 84% | 0% | no | Different trip length |
| 0038 ↔ 0042 | 4 Nights / 5 Days / 5 Nights / 6 Days | 83% | 0% | no | Different trip length |
| 0039 ↔ 0043 | 4 Nights / 5 Days / 5 Nights / 6 Days | 62% | 0% | no | Different trip length |
| 0041 ↔ 0044 | 5 Nights / 6 Days / 6 Nights / 7 Days | 2% | 0% | no | Different trip length |
| 0051 ↔ 0052 | 04 Nights / 05 Days / 5 Nights / 6 Days | 87% | 100% | no | Different trip length |
| 0054 ↔ 0055 | 08 Nights / 09 Days / 08 Nights / 09 Days | 100% | 100% | yes | **Exact duplicate page** (see C.1) |
| 0054 ↔ 0056 | 08 Nights / 09 Days / 9 Nights / 10 Days | 74% | 100% | no | Different trip length |
| 0055 ↔ 0056 | 08 Nights / 09 Days / 9 Nights / 10 Days | 74% | 100% | no | Different trip length |
| 0060 ↔ 0061 | 2 Nights / 3 Days / 3 Nights / 4 Days | 79% | 94% | no | Different trip length |
| 0064 ↔ 0066 | 3 Nights / 4 Days / 4 Nights / 5 Days | 43% | 0% | no | Different trip length |
| 0064 ↔ 0067 | 3 Nights / 4 Days / 4 Nights / 5 Days | 49% | 0% | no | Different trip length |
| 0066 ↔ 0067 | 4 Nights / 5 Days / 4 Nights / 5 Days | 72% | 100% | no | Different once 0066's label is corrected (see C.3) |
| 0086 ↔ 0089 | 3 Nights / 4 Days / 4 Nights / 5 Days | 66% | 0% | no | Different trip length |
| 0088 ↔ 0091 | 4 Nights / 5 Days / 5 Nights / 6 Days | 65% | 100% | no | Different trip length |
| 0092 ↔ 0093 | 3 Nights / 4 Days / 3 Nights / 4 Days | 2% | 6% | no | Different day plans |
| 0092 ↔ 0094 | 3 Nights / 4 Days / 3 Nights / 4 Days | 3% | 99% | no | Different day plans |
| 0092 ↔ 0095 | 3 Nights / 4 Days / 3 Nights / 4 Days | 3% | 75% | no | Different day plans |
| 0092 ↔ 0096 | 3 Nights / 4 Days / 3 Nights / 4 Days | 2% | 21% | no | Different day plans |
| 0093 ↔ 0094 | 3 Nights / 4 Days / 3 Nights / 4 Days | 6% | 4% | no | Different day plans |
| 0093 ↔ 0095 | 3 Nights / 4 Days / 3 Nights / 4 Days | 8% | 1% | no | Different day plans |
| 0093 ↔ 0096 | 3 Nights / 4 Days / 3 Nights / 4 Days | 4% | 10% | no | Different day plans |
| 0094 ↔ 0095 | 3 Nights / 4 Days / 3 Nights / 4 Days | 63% | 76% | no | Similar; different meals/inclusions: see row notes |
| 0094 ↔ 0096 | 3 Nights / 4 Days / 3 Nights / 4 Days | 3% | 22% | no | Different day plans |
| 0095 ↔ 0096 | 3 Nights / 4 Days / 3 Nights / 4 Days | 3% | 19% | no | Different day plans |
| 0097 ↔ 0098 | 2 Nights / 3 Days / 2 Nights / 3 Days | 3% | 10% | no | Different day plans |
| 0097 ↔ 0099 | 2 Nights / 3 Days / 3 Nights / 4 Days | 76% | 92% | no | Different trip length |
| 0097 ↔ 0100 | 2 Nights / 3 Days / 3 Nights / 4 Days | 5% | 19% | no | Different trip length |
| 0098 ↔ 0099 | 2 Nights / 3 Days / 3 Nights / 4 Days | 2% | 7% | no | Different trip length |
| 0098 ↔ 0100 | 2 Nights / 3 Days / 3 Nights / 4 Days | 2% | 41% | no | Different trip length |
| 0099 ↔ 0100 | 3 Nights / 4 Days / 3 Nights / 4 Days | 4% | 17% | no | Different day plans |
| 0099 ↔ 0101 | 3 Nights / 4 Days / 4 Nights / 5 Days | 3% | 25% | no | Different trip length |
| 0099 ↔ 0102 | 3 Nights / 4 Days / 4 Nights / 5 Days | 3% | 25% | no | Different trip length |
| 0100 ↔ 0101 | 3 Nights / 4 Days / 4 Nights / 5 Days | 3% | 5% | no | Different trip length |
| 0100 ↔ 0102 | 3 Nights / 4 Days / 4 Nights / 5 Days | 3% | 5% | no | Different trip length |
| 0101 ↔ 0102 | 4 Nights / 5 Days / 4 Nights / 5 Days | 99% | 100% | no | **Potential duplicate**: identical content, names differ (see C.2) |

## E. Duration conflicts

**Sources compared:** duration label, URL, itinerary day count, title, page title (metadata) and description.
- **HIGH:** the label, URL, itinerary or title disagree.
- **LOW:** only the metadata wording disagrees.

| No. | Package | Sources | Severity | Suggested |
|---|---|---|---|---|
| 0004 | Srinagar Pahalgam Gulmarg Sonmarg Tour | label 5 days, URL 6 days, itinerary 6 days, page title 6 days, description 5 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0006 | Srinagar Gulmarg Sonmarg Pahalgam Katra Tour | label 8 days, URL 8 days, itinerary 8 days, page title 8 days, description 7 days | LOW | APPROVE |
| 0007 | Breathtaking Leh Ladakh Tour | label 4 days, URL 4 days, itinerary 4 days, page title 4 days, description 7 days | LOW | APPROVE |
| 0009 | Jewels of Leh Ladakh Tour | label 6 days, URL 6 days, itinerary 6 days, page title 6 days, description 5 days | LOW | APPROVE |
| 0010 | Panorama Ladakh tour | label 6 days, URL 7 days, itinerary 7 days, page title 7 days, description 7 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0011 | Serene Leh Ladakh Tour | label 8 days, URL 8 days, itinerary 8 days, page title 8 days, description 7 days | LOW | APPROVE |
| 0012 | Discover Leh Ladakh Tour | label 9 days, URL 9 days, itinerary 8 days, page title 9 days, description 9 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0037 | Nainital with Almora and jim corbett | label 5 days, URL 5 days, itinerary 3 days, page title 5 days, description 5 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0054 | Char Dham yatra Package from Haridwar for 9 Days | label 9 days, URL 12 days, itinerary 10 days, title 9 days, page title 10 days, description 10 days | HIGH | REVIEW: owner to decide: write the real Delhi 11N/12D itinerary (then APPROVE), or RETIRE (301 to 0055) |
| 0055 | Char Dham yatra Package from Haridwar for 9 Days | label 9 days, URL 9 days, itinerary 10 days, title 9 days, page title 10 days, description 10 days | HIGH | APPROVE (the Haridwar 8N/9D content belongs here); fix duration text (label 8N/9D vs 10 itinerary days vs "9N/10D" page title) |
| 0057 | Yamunotri Gangotri Do Dham Yatra from Delhi | label 10 days, URL 7 days, itinerary 7 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0058 | Char Dham yatra Package from delhi for 11 Days | label 11 days, URL 11 days, itinerary 11 days, title 11 days, page title 11 days, description 10 days | LOW | APPROVE |
| 0066 | Darjeeling and Gangtok Tour | label 5 days, URL 6 days, itinerary 6 days, page title 6 days, description 6 days | HIGH | APPROVE after correcting the duration label to 5N/6D (URL, page title, description and itinerary all say 6 days) |
| 0067 | Darjeeling Gangtok Tour | label 5 days, URL 5 days, itinerary 5 days, page title 5 days, description 4 days | LOW | APPROVE (materially different from 0066 once 0066 is corrected) |
| 0071 | Gangtok with lachen and Lachung Tour | label 6 days, URL 7 days, itinerary 7 days, page title 7 days, description 7 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0077 | Kovalam Kanyakumari Tour | label 4 days, URL 4 days, itinerary 3 days, page title 4 days, description 4 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0079 | Munnar Thekkady Tour Package | label 4 days, URL 4 days, itinerary 3 days, page title 4 days, description 4 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0080 | Munnar Alleppey Kovalam | label 5 days, URL 5 days, itinerary 3 days, page title 5 days, description 5 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0082 | Munnar Alleppey Kovalam | label 6 days, URL 6 days, itinerary 5 days, page title 6 days, description 6 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0091 | Ooty Mysore tour | label 6 days, URL 4 days, itinerary 4 days, page title 4 days, description 4 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) |
| 0093 | Enticing Tour to Goa | label 4 days, URL 4 days, itinerary 4 days, page title 4 days, description 5 days | LOW | APPROVE |

## F. Missing itineraries

| No. | Package | Where it was looked for | Suggested |
|---|---|---|---|
| 0005 | Srinagar Gulmarg Pahalgam Tour (6 Nights / 7 Days) | original page (Itinerary tab empty), comments, git history, old-site copy | REVIEW: approve the ID, but keep the page as Draft until an itinerary is supplied |
| 0013 | Best of Shimla Vacation (2 Nights / 3 Days) | original page (Itinerary tab empty), comments, git history, old-site copy | REVIEW: keep as Draft until the owner supplies the itinerary (none exists); it is a different trip from 0014 |
| 0083 | Munnar Alleppey Tour Package (5 Nights / 6 Days) | original page (Itinerary tab empty), comments, git history, old-site copy | REVIEW: approve the ID, but keep the page as Draft until an itinerary is supplied |

No itinerary has been written or invented for these packages.

## G. Same-title analysis

The suggested titles use only data already on the pages (route and corrected length). **Nothing has been renamed.**

| No. | Current title | Route (overnights, from the itinerary) | Duration | Genuinely different? | Suggested distinguishing title | Owner Decision |
|---|---|---|---|---|---|---|
| 0038 | Nainital with Kausani and Jim Corbett | Nainital → Kausani → Corbett | 4 Nights / 5 Days | Yes: different length/route | Nainital with Kausani and Jim Corbett Tour — 4N/5D | |
| 0042 | Nainital with Kausani and Jim Corbett | Nainital → Kausani → Corbett | 5 Nights / 6 Days | Yes: different length/route | Nainital with Kausani and Jim Corbett Tour — 5N/6D | |
| 0039 | Nainital with Ranikhet Jim Corbett | Nainital → Ranikhet | 4 Nights / 5 Days | Yes: different length/route | Nainital with Ranikhet Jim Corbett Tour — 4N/5D | |
| 0043 | Nainital with Ranikhet and jim corbett | Nainital → Ranikhet → Corbett | 5 Nights / 6 Days | Yes: different length/route | Nainital with Ranikhet and Jim Corbett Tour — 5N/6D | |
| 0054 | Char Dham yatra Package from Haridwar for 9 Days | — | 08 Nights / 09 Days | No: exact duplicate (C.1) | — | |
| 0055 | Char Dham yatra Package from Haridwar for 9 Days | — | 08 Nights / 09 Days | No: exact duplicate (C.1) | — | |
| 0064 | Darjeeling Gangtok Tour | Darjeeling → Gangtok | 3 Nights / 4 Days | Yes: different length/route | Darjeeling Gangtok Tour — 3N/4D | |
| 0066 | Darjeeling and Gangtok Tour | Darjeeling → Gangtok | 4 Nights / 5 Days | Yes: different length/route | Darjeeling and Gangtok Tour — 5N/6D (after duration fix) | |
| 0067 | Darjeeling Gangtok Tour | Darjeeling → Gangtok | 4 Nights / 5 Days | Yes: different length/route | Darjeeling Gangtok Tour — 4N/5D | |
| 0078 | Munnar Alleppey Tour Package | Munnar → Alleppey → Cochin | 3 Nights / 4 Days | Yes: different length/route | Munnar Alleppey Tour — 3N/4D | |
| 0083 | Munnar Alleppey Tour Package | — | 5 Nights / 6 Days | Yes: different length/route | Munnar Alleppey Tour — 5N/6D | |
| 0084 | Munnar Alleppey Tour Package | Munnar → Thekkady → Alleppey → Kovalam | 6 Nights / 7 Days | Yes: different length/route | Munnar Alleppey Tour — 6N/7D | |
| 0080 | Munnar Alleppey Kovalam | Munnar → Kovalam | 4 Nights / 5 Days | Yes: different length/route | Munnar Alleppey Kovalam Tour — 4N/5D | |
| 0082 | Munnar Alleppey Kovalam | Cochin → Munnar → Thekkady → Alleppey | 5 Nights / 6 Days | Yes: different length/route | Cochin Munnar Thekkady Alleppey Tour — 5N/6D | |

## B. All 107 records

**Route:** the overnight places from the itinerary. **Start / end:** from day 1 and the last day. Owner Decision is blank.

| Proposed No. | Temporary internal ref | Current name | URL | Displayed duration | Itinerary days | Route | Start / end | Issue | Severity | Suggested action | Owner Decision |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 0001 | `slug:srinagar-gulmarg-tour-03nt04dy` | Srinagar Gulmarg Tour Package | /srinagar-gulmarg-tour-03nt04dy | 3 Nights / 4 Days | 4 | Srinagar | Srinagar / Srinagar | — | NONE | APPROVE | |
| 0002 | `slug:srinagar-gulmarg-pahalgam-tour-package-5-days` | Srinagar Gulmarg Pahalgam Tour Package | /srinagar-gulmarg-pahalgam-tour-package-5-days | 4 Nights / 5 Days | 5 | Srinagar → Gulmarg → Pahalga → Srinagar | Srinagar / Srinagar | — | NONE | APPROVE | |
| 0003 | `slug:srinagar-gulmarg-sonmarg-day-trip-tour-package-5-days` | Srinagar Gulmarg Sonmarg Day Trip Tour | /srinagar-gulmarg-sonmarg-day-trip-tour-package-5-days | 4 Nights / 5 Days | 5 | Srinagar | Srinagar / Srinagar | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0004 | `slug:srinagar-pahalgam-gulmarg-sonmarg-package-6-days` | Srinagar Pahalgam Gulmarg Sonmarg Tour | /srinagar-pahalgam-gulmarg-sonmarg-package-6-days | 4 Nights / 5 Days | 6 | Pahalga → Gulmarg → Srinagar | Arrival Srinagar Airport / Srinagar | duration conflict: label 5 days, URL 6 days, itinerary 6 days, page title 6 days, description 5 days; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0005 | `slug:srinagar-gulmarg-pahalgam-tour-package-7-days` | Srinagar Gulmarg Pahalgam Tour | /srinagar-gulmarg-pahalgam-tour-package-7-days | 6 Nights / 7 Days | — | — | — | no day-by-day itinerary on the page (not found anywhere in the project or git history); no package image (photos to be supplied by the owner) | HIGH | REVIEW: approve the ID, but keep the page as Draft until an itinerary is supplied | |
| 0006 | `slug:srinagar-gulmarg-sonmarg-pahalgam-katra-tour-package-8-days` | Srinagar Gulmarg Sonmarg Pahalgam Katra Tour | /srinagar-gulmarg-sonmarg-pahalgam-katra-tour-package-8-days | 7 Nights / 8 Days | 8 | Srinagar → Sonmarg → Pahalga → Katra → Jammu | Srinagar / Jammu Drop | duration conflict: label 8 days, URL 8 days, itinerary 8 days, page title 8 days, description 7 days; no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0007 | `slug:breathtaking-leh-ladakh-tour-3n-4d` | Breathtaking Leh Ladakh Tour | /breathtaking-leh-ladakh-tour-3n-4d | 3 Nights / 4 Days | 4 | Leh | Leh / Leh | duration conflict: label 4 days, URL 4 days, itinerary 4 days, page title 4 days, description 7 days; no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0008 | `slug:special-leh-ladakh-tour-4n-5d` | Special Leh Ladakh Tour | /special-leh-ladakh-tour-4n-5d | 4 Nights / 5 Days | 5 | Leh | Leh / ? | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0009 | `slug:jewels-of-leh-ladakh-package-5n6d` | Jewels of Leh Ladakh Tour | /jewels-of-leh-ladakh-package-5n6d | 5 Nights / 6 Days | 6 | Leh | Leh / Leh | duration conflict: label 6 days, URL 6 days, itinerary 6 days, page title 6 days, description 5 days; no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0010 | `slug:panorama-ladakh-tour-6n7d` | Panorama Ladakh tour | /panorama-ladakh-tour-6n7d | 5 Nights / 6 Days | 7 | Leh | Leh / Leh | duration conflict: label 6 days, URL 7 days, itinerary 7 days, page title 7 days, description 7 days; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0011 | `slug:serene-leh-ladakh-tour-7n-8d` | Serene Leh Ladakh Tour | /serene-leh-ladakh-tour-7n-8d | 7 Nights / 8 Days | 8 | Leh | Leh / Leh | duration conflict: label 8 days, URL 8 days, itinerary 8 days, page title 8 days, description 7 days; no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0012 | `slug:discover-leh-ladakh-tour-8n-9d` | Discover Leh Ladakh Tour | /discover-leh-ladakh-tour-8n-9d | 8 Nights / 9 Days | 8 | Leh | Leh / Leh | duration conflict: label 9 days, URL 9 days, itinerary 8 days, page title 9 days, description 9 days; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0013 | `slug:best-of-shimla-vacation` | Best of Shimla Vacation | /best-of-shimla-vacation | 2 Nights / 3 Days | — | — | — | no day-by-day itinerary on the page (not found anywhere in the project or git history); no package image (photos to be supplied by the owner) | HIGH | REVIEW: keep as Draft until the owner supplies the itinerary (none exists); it is a different trip from 0014 | |
| 0014 | `slug:best-of-shimla` | Best of Shimla | /best-of-shimla | 3 Nights / 4 Days | 4 | Shimla | Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE (different trip from 0013: Volvo from Delhi, 3N/4D) | |
| 0015 | `slug:amritsar-with-dalhousie-dharamshala-05-days` | Amritsar with Dalhousie Dharamshala | /amritsar-with-dalhousie-dharamshala-05-days | 4 Nights / 5 Days | 5 | Amritsar → Dalhousie → Dharamshala | Amritsar / Amritsar Drop | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0016 | `slug:blissful-tour-to-himachal-pradesh-4n-5d` | Blissful Tour to Himachal Pradesh | /blissful-tour-to-himachal-pradesh-4n-5d | 4 Nights / 5 Days | 5 | Amritsar → Chandigarh → Shimla | Amritsar / Shimla | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0017 | `slug:manali-volvo-vrip-weekend` | Manali Volvo Trip weekend | /manali-volvo-vrip-weekend | 4 Nights / 5 Days | 5 | Manali → Delhi | Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0018 | `slug:manali-with-manikaran` | Manali with Manikaran | /manali-with-manikaran | 4 Nights / 5 Days | 5 | Manali | Manali / Manali | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0019 | `slug:amrirsar-with-dalhousie-and-dharamshala-06-days` | Amrirsar with Dalhousie and Dharamshala | /amrirsar-with-dalhousie-and-dharamshala-06-days | 5 Nights / 6 Days | 6 | Amritsar → Dalhousie → Dharamshala | Amritsar / Amritsar Drop | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0020 | `slug:best-of-manali-with-delhi-by-volvo` | Best of Manali with Delhi By Volvo | /best-of-manali-with-delhi-by-volvo | 5 Nights / 6 Days | 6 | Manali → Delhi | Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0021 | `slug:manali-with-dharamshala-and-amritsar-06-days` | Manali with Dharamshala and amritsar | /manali-with-dharamshala-and-amritsar-06-days | 5 Nights / 6 Days | 6 | Manali → Dharamshala → Amritsar | Chandigarh / Chandigarh Drop | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0022 | `slug:shimla-manali-tour-06-days` | Shimla Manali Tour | /shimla-manali-tour-06-days | 5 Nights / 6 Days | 6 | Shimla → Manali | Shimla / Chandigarh | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0023 | `slug:shimla-manali-with-chandigarh-06-days` | Shimla Manali with Chandigarh | /shimla-manali-with-chandigarh-06-days | 5 Nights / 6 Days | 6 | Shimla → Kullu Manali → Manali → Delhi | Shimla / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0024 | `slug:amritsar-with-dalhousie-dharamshala-and-chandigarh-07-days` | Amritsar with Dalhousie Dharamshala and Chandigarh | /amritsar-with-dalhousie-dharamshala-and-chandigarh-07-days | 6 Nights / 7 Days | 7 | Amritsar → Dalhousie → Dharamshala → Chandigarh | Amritsar / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0025 | `slug:best-of-shimla-and-manali` | Best of Shimla and Manali | /best-of-shimla-and-manali | 6 Nights / 7 Days | 7 | Manali → Shimla → Manali | Manali / Manali | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0026 | `slug:shimla-manali-with-dalhousie-amritsar-08-days` | Shimla Manali with Dalhousie Amritsar | /shimla-manali-with-dalhousie-amritsar-08-days | 7 Nights / 8 Days | 8 | Shimla → Manali → Dalhousie → Amritsar | Shimla / Delhi Drop | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0027 | `slug:shimla-manali-with-dharmshala-dalhousie-and-amritsar-09-days` | Shimla Manali with Dharmshala Dalhousie and Amritsar | /shimla-manali-with-dharmshala-dalhousie-and-amritsar-09-days | 8 Nights / 9 Days | 9 | Shimla → Manali → Dharamshala → Dalhousie → Chandigarh | Shimla / Delhi | title/URL names Amritsar, which the itinerary never mentions; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0028 | `slug:grand-himachal-with-mata-vaishno-devi--tour-13-days` | Grand Himachal with Mata vaishno Devi Tour | /grand-himachal-with-mata-vaishno-devi--tour-13-days | 12 Nights / 13 Days | 13 | Shimla → Manali → Dharamshala → Dalhousie → Katra → Amritsar → Chandigarh → Delhi | Shimla / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0029 | `slug:haridwar-rishikesh-tour-03-days` | Haridwar Rishikesh Tour | /haridwar-rishikesh-tour-03-days | 2 Nights / 3 Days | 3 | Haridwar → Rishikesh → Delhi | Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0030 | `slug:mussoorie-tour-03-days` | Mussoorie Tour | /mussoorie-tour-03-days | 2 Nights / 3 Days | 3 | Mussoorie | Arrive Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0031 | `slug:nainital-tour-03-days` | Nainital Tour | /nainital-tour-03-days | 2 Nights / 3 Days | 3 | Nainital | Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0032 | `slug:uttarakhand-mussoorie-with-rishikesh-flight-inclusive` | Uttarakhand Mussoorie With Rishikesh Flight Inclusive | /uttarakhand-mussoorie-with-rishikesh-flight-inclusive | 2 Nights / 3 Days | 3 | Mussoorie | Mussoorie / Mussoorie | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0033 | `slug:corbett-with-nainital-04-days` | Corbett with Nainital | /corbett-with-nainital-04-days | 3 Nights / 4 Days | 4 | Corbett → Nainital | Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0034 | `slug:haridwar-rishikesh-mussoorie-04-days` | Haridwar Rishikesh Mussoorie | /haridwar-rishikesh-mussoorie-04-days | 3 Nights / 4 Days | 4 | Haridwar → Mussoorie → Delhi | Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0035 | `slug:mussoorie-with-jim-corbett-04-days` | Mussoorie with Jim Corbett | /mussoorie-with-jim-corbett-04-days | 3 Nights / 4 Days | 4 | Mussoorie → Corbett | Arrive Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0036 | `slug:haridwar-with-mussoorie-and-jim-corbett-05-days` | Haridwar with Mussoorie and Jim Corbett | /haridwar-with-mussoorie-and-jim-corbett-05-days | 4 Nights / 5 Days | 5 | Haridwar → Mussoorie → Corbett | Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0037 | `slug:nainital-with-almora-and-jim-corbett-05-days` | Nainital with Almora and jim corbett | /nainital-with-almora-and-jim-corbett-05-days | 4 Nights / 5 Days | 3 | Nainital → Corbett | Delhi / Delhi | duration conflict: label 5 days, URL 5 days, itinerary 3 days, page title 5 days, description 5 days; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0038 | `slug:nainital-with-kausani-and-jim-corbett-05-days` | Nainital with Kausani and Jim Corbett | /nainital-with-kausani-and-jim-corbett-05-days | 4 Nights / 5 Days | 5 | Nainital → Kausani → Corbett | Delhi / Delhi | no inclusions listed; no exclusions listed; same or near-same title as 0042; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE; consider a distinguishing title | |
| 0039 | `slug:nainital-with-ranikhet--jim-corbett` | Nainital with Ranikhet Jim Corbett | /nainital-with-ranikhet--jim-corbett | 4 Nights / 5 Days | 5 | Nainital → Ranikhet | Delhi / Delhi | no inclusions listed; no exclusions listed; same or near-same title as 0043; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE; consider a distinguishing title | |
| 0040 | `slug:haridwar--with-mussoorie-and-corbett-06-days` | Haridwar with Mussoorie and Corbett | /haridwar--with-mussoorie-and-corbett-06-days | 5 Nights / 6 Days | 6 | Haridwar → Mussoorie → Corbett → Delhi | Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0041 | `slug:mussoorie-with-nainital-and-jim-corbett-06-days` | Mussoorie with Nainital and Jim Corbett | /mussoorie-with-nainital-and-jim-corbett-06-days | 5 Nights / 6 Days | 6 | Mussoorie → Corbett → Nainital | Arrive Delhi / Delhi | no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0042 | `slug:nainital-with-kausani-and-jim-corbett-06-days` | Nainital with Kausani and Jim Corbett | /nainital-with-kausani-and-jim-corbett-06-days | 5 Nights / 6 Days | 6 | Nainital → Kausani → Corbett | Delhi / Delhi | no exclusions listed; same or near-same title as 0038; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE; consider a distinguishing title | |
| 0043 | `slug:nainital-with-ranikhet-and-jim-corbett-06-days` | Nainital with Ranikhet and jim corbett | /nainital-with-ranikhet-and-jim-corbett-06-days | 5 Nights / 6 Days | 6 | Nainital → Ranikhet → Corbett | Delhi / Delhi | no exclusions listed; same or near-same title as 0039; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE; consider a distinguishing title | |
| 0044 | `slug:corbett-with-nainital-and-mussoorie-07-days` | Corbett With Nainital and mussoorie | /corbett-with-nainital-and-mussoorie-07-days | 6 Nights / 7 Days | 7 | Corbett → Nainital → Mussoorie | Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0045 | `slug:haridwar-with-rishikesh-mussoorie-nainital-and-jim-corbett-07-days` | Haridwar With Rishikesh Mussoorie Nainital and Jim Corbett | /haridwar-with-rishikesh-mussoorie-nainital-and-jim-corbett-07-days | 6 Nights / 7 Days | 7 | Haridwar → Mussoorie → Corbett → Nainital → Delhi | Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0046 | `slug:mussoorie-with-nainital-almora-and-jim-corbett--07-days` | Mussoorie with Nainital Almora and Jim Corbett | /mussoorie-with-nainital-almora-and-jim-corbett--07-days | 6 Nights / 7 Days | 7 | Mussoorie → Corbett → Almora → Nainital | Arrive Delhi / Nainital | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0047 | `slug:nainital-with-ranikhet-kausani-and-jim-corbett-07-days` | Nainital with Ranikhet kausani and Jim corbett | /nainital-with-ranikhet-kausani-and-jim-corbett-07-days | 6 Nights / 7 Days | 7 | Nainital → (Nainital → Ranikhet → Kausani → Corbett → Delhi | Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0048 | `slug:haridwar-with-mussoorie-corbett-kausani-and-nainital-08-days` | Haridwar with Mussoorie corbett Kausani and Nainital Tour | /haridwar-with-mussoorie-corbett-kausani-and-nainital-08-days | 7 Nights / 8 Days | 8 | Haridwar → Mussoorie → Kausani → Nainital → Delhi | Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0049 | `slug:mussoorie-with-corbett-ranikhet-kausani-and-nainital-08-days` | Mussoorie with corbett Ranikhet Kausani and Nainital | /mussoorie-with-corbett-ranikhet-kausani-and-nainital-08-days | 7 Nights / 8 Days | 8 | Mussoorie → Corbett → Ranikhet → Kausani → Nainital | Arrive Delhi / Delhi | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0050 | `slug:nainital-with-ranikhet-kausani-jim-corbett-and-haridwar-08-days` | Nainital with Ranikhet Kausani jim Corbett and Haridwar | /nainital-with-ranikhet-kausani-jim-corbett-and-haridwar-08-days | 7 Nights / 8 Days | 8 | Nainital → Ranikhet → Kausani → Corbett → Haridwar → Delhi | Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0051 | `slug:do-dham-kedar–badri-from-haridwar-4n-5d` | Do Dham Yatra (Kedarnath - Badrinath) Tour from Haridwar 5 Days Package | /do-dham-kedar–badri-from-haridwar-4n-5d | 04 Nights / 05 Days | 5 | — | Haridwar / Haridwar | — | NONE | APPROVE | |
| 0052 | `slug:do-dham-kedar–badri-from-haridwar-5n-6d` | 6-Day Do Dham Yatra (Kedarnath & Badrinath) Tour from Haridwar | /do-dham-kedar–badri-from-haridwar-5n-6d | 5 Nights / 6 Days | 6 | — | Haridwar / Haridwar | — | NONE | APPROVE | |
| 0053 | `slug:chardham-yatra-by-helicopter` | Chardham Yatra By Helicopter Tour | /chardham-yatra-by-helicopter | 5 Nights / 6 Days | 6 | Dehradun → Kharshali → Harsil → Guptkashi → Badrinath → Dehradun | Dehradun / Dehradun | no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0054 | `slug:char-dham-yatra-from-delhi-11n-12d` | Char Dham yatra Package from Haridwar for 9 Days | /char-dham-yatra-from-delhi-11n-12d | 08 Nights / 09 Days | 10 | — | Haridwar / Rishikesh to Haridwar | original page file is byte-for-byte identical to 0055 /chardham-yatra-from-haridwar-8n-9d; URL says "from Delhi" but the title/content is "Char Dham yatra Package from Haridwar for 9 Days" and the itinerary starts at Haridwar; duration conflict: label 9 days, URL 12 days, itinerary 10 days, title 9 days, page title 10 days, description 10 days | CRITICAL | REVIEW: owner to decide: write the real Delhi 11N/12D itinerary (then APPROVE), or RETIRE (301 to 0055) | |
| 0055 | `slug:chardham-yatra-from-haridwar-8n-9d` | Char Dham yatra Package from Haridwar for 9 Days | /chardham-yatra-from-haridwar-8n-9d | 08 Nights / 09 Days | 10 | — | Haridwar / Rishikesh to Haridwar | original page file is byte-for-byte identical to 0054 /char-dham-yatra-from-delhi-11n-12d; duration conflict: label 9 days, URL 9 days, itinerary 10 days, title 9 days, page title 10 days, description 10 days | CRITICAL | APPROVE (the Haridwar 8N/9D content belongs here); fix duration text (label 8N/9D vs 10 itinerary days vs "9N/10D" page title) | |
| 0056 | `slug:char-dham-yatra-from-haridwar-9n-10d` | Char Dham yatra Package from Haridwar | /char-dham-yatra-from-haridwar-9n-10d | 9 Nights / 10 Days | 10 | — | Haridwar / Rishikesh to Haridwar | — | NONE | APPROVE | |
| 0057 | `slug:yamunotri-gangotri-do-dham-from-delhi-6n-7d` | Yamunotri Gangotri Do Dham Yatra from Delhi | /yamunotri-gangotri-do-dham-from-delhi-6n-7d | 9 Nights / 10 Days | 7 | — | Delhi / ? | duration conflict: label 10 days, URL 7 days, itinerary 7 days | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0058 | `slug:char-dham-yatra-from-delhi-10n-11d` | Char Dham yatra Package from delhi for 11 Days | /char-dham-yatra-from-delhi-10n-11d | 10 Nights / 11 Days | 11 | — | Delhi / Delhi | duration conflict: label 11 days, URL 11 days, itinerary 11 days, title 11 days, page title 11 days, description 10 days | LOW | APPROVE | |
| 0059 | `slug:chardham-package` | Chardham Package 12 Days Ex Delhi Tour | /chardham-package | 11 Nights / 12 Days | 12 | Haridwar → Barkot → Uttarkashi → Guptkashi → Kedarnath → Rudraprayag → Badrinath → Rudraprayag → Haridwar → Delhi | Delhi / Delhi | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0060 | `slug:amarnath-ji-yatra-by-helicopter-2n-3d` | Amarnath Ji Tour Package by Helicopter for 2 Nights 3 Days | /amarnath-ji-yatra-by-helicopter-2n-3d | 2 Nights / 3 Days | 3 | — | ? / ? | — | NONE | APPROVE | |
| 0061 | `slug:amarnath-ji-yatra-by-helicopter-3n-4d` | Amarnath Ji Yatra by Helicopter – 3 Nights 4 Days with Srinagar Tour | /amarnath-ji-yatra-by-helicopter-3n-4d | 3 Nights / 4 Days | 4 | — | ? / ? | — | NONE | APPROVE | |
| 0062 | `slug:amarnath-ji-yatra-with-srinagar-4n-5d` | Amarnath Ji Yatra with Srinagar – 4 Nights 5 Days Package | /amarnath-ji-yatra-with-srinagar-4n-5d | 4 Nights / 5 Days | 5 | — | ? / ? | — | NONE | APPROVE | |
| 0063 | `slug:amarnath-ji-yatra-via-pahalgam-5n-6d` | Amarnath Ji Yatra via Pahalgam 5 Nights 6 Days Spiritual Journey | /amarnath-ji-yatra-via-pahalgam-5n-6d | 5 Nights / 6 Days | 6 | — | Srinagar / ? | — | NONE | APPROVE | |
| 0064 | `slug:darjeeling-gangtok-04-days` | Darjeeling Gangtok Tour | /darjeeling-gangtok-04-days | 3 Nights / 4 Days | 4 | Darjeeling → Gangtok | Arrival and Transfer / Gangtok | same or near-same title as 0066, 0067; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE; consider a distinguishing title | |
| 0065 | `slug:darjeeling-kalimpong-04-days` | Darjeeling Kalimpong Tour | /darjeeling-kalimpong-04-days | 3 Nights / 4 Days | 4 | Darjeeling → Kalimpong | Arrival and Transfer / Kalimpong | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0066 | `slug:darjeeling-and-gangtok-06-days` | Darjeeling and Gangtok Tour | /darjeeling-and-gangtok-06-days | 4 Nights / 5 Days | 6 | Darjeeling → Gangtok | Arrival and Transfer / Gangtok | duration conflict: label 5 days, URL 6 days, itinerary 6 days, page title 6 days, description 6 days; no inclusions listed; no exclusions listed; same or near-same title as 0064, 0067; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration label to 5N/6D (URL, page title, description and itinerary all say 6 days) | |
| 0067 | `slug:darjeeling-gangtok-05-days` | Darjeeling Gangtok Tour | /darjeeling-gangtok-05-days | 4 Nights / 5 Days | 5 | Darjeeling → Gangtok | Arrival and Transfer / Gangtok | duration conflict: label 5 days, URL 5 days, itinerary 5 days, page title 5 days, description 4 days; no inclusions listed; no exclusions listed; same or near-same title as 0066, 0064; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE (materially different from 0066 once 0066 is corrected) | |
| 0068 | `slug:gangtok-pelling-tour-05-days` | Gangtok Pelling Tour | /gangtok-pelling-tour-05-days | 4 Nights / 5 Days | 5 | Pelling → Gangtok | Arrival and Transfer / Gangtok | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0069 | `slug:darjeeling-with-kalimpong-and-gangtok-06-days` | Darjeeling with Kalimpong and Gangtok Tour | /darjeeling-with-kalimpong-and-gangtok-06-days | 5 Nights / 6 Days | 6 | Darjeeling → Kalimpong → Gangtok | Arrival and Transfer / Gangtok | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0070 | `slug:darjeeling-with-pelling-and-gangtok-06-days` | Darjeeling with Pelling and Gangtok Tour | /darjeeling-with-pelling-and-gangtok-06-days | 5 Nights / 6 Days | 6 | Darjeeling → Pelling → Gangtok | Arrival and Transfer / Gangtok | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0071 | `slug:gangtok-with-lachen-and-lachung-07-days` | Gangtok with lachen and Lachung Tour | /gangtok-with-lachen-and-lachung-07-days | 5 Nights / 6 Days | 7 | Gangtok → Lachung → Gangtok | Arrival and Transfer / Gangtok | duration conflict: label 6 days, URL 7 days, itinerary 7 days, page title 7 days, description 7 days; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0072 | `slug:gangtok-with-pelling-and-kalimpong-tour-07-days` | Gangtok with Pelling and Kalimpong Tour | /gangtok-with-pelling-and-kalimpong-tour-07-days | 6 Nights / 7 Days | 7 | Gangtok → Pelling → Kalimpong | Gangtok / Kalimpong | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0073 | `slug:darjeeling-with-lachung-and-gangtok-08-days` | Darjeeling with Lachung and gangtok Tour | /darjeeling-with-lachung-and-gangtok-08-days | 7 Nights / 8 Days | 8 | Darjeeling → Gangtok → Lachung → Gangtok | Arrival and Transfer / Gangtok | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0074 | `slug:gangtok-with-lachung-pelling-and-darjeeling-08-days` | Gangtok with Lachung Pelling and Darjeeling Tour | /gangtok-with-lachung-pelling-and-darjeeling-08-days | 7 Nights / 8 Days | 8 | Gangtok → Lachung → Yumthang → Pelling → Darjeeling | Arrival and Transfer / Darjeeling | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0075 | `slug:kalimpong-with-gangtok-pelling-and-darjeeling-08-days` | Kalimpong With Gangtok pelling and darjeeling Tour | /kalimpong-with-gangtok-pelling-and-darjeeling-08-days | 7 Nights / 8 Days | 8 | Kalimpong → Gangtok → Pelling → Darjeeling | Kalimpong / Darjeeling | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0076 | `slug:darjeeling-with-kalimpong-pelling-lachung-and-gangtok-10-days` | Darjeeling with Kalimpong pelling Lachung and gangtok Tour | /darjeeling-with-kalimpong-pelling-lachung-and-gangtok-10-days | 9 Nights / 10 Days | 10 | Gangtok → Lachung → Tsomgo → Yumthang → Pelling → Darjeeling | Arrival and Transfer / Darjeeling | title/URL names Kalimpong, which the itinerary never mentions; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0077 | `slug:kovalam-kanyakumari-tour-4-days` | Kovalam Kanyakumari Tour | /kovalam-kanyakumari-tour-4-days | 3 Nights / 4 Days | 3 | Kovalam → Munnar → Thekkady | Arrive Trivandru / Thekkady | duration conflict: label 4 days, URL 4 days, itinerary 3 days, page title 4 days, description 4 days; title/URL names Kanyakumari, which the itinerary never mentions; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0078 | `slug:munnar-alleppey-tour-package-04-days` | Munnar Alleppey Tour Package | /munnar-alleppey-tour-package-04-days | 3 Nights / 4 Days | 4 | Munnar → Alleppey → Cochin | Arrive Cochin / Alleppey | same or near-same title as 0083, 0084; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE; consider a distinguishing title | |
| 0079 | `slug:munnar-thekkady-tour-package-4-days` | Munnar Thekkady Tour Package | /munnar-thekkady-tour-package-4-days | 3 Nights / 4 Days | 3 | Munnar → Thekkady | Arrive Cochin / Thekkady | duration conflict: label 4 days, URL 4 days, itinerary 3 days, page title 4 days, description 4 days; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0080 | `slug:munnar-alleppey-kovalam-05-days` | Munnar Alleppey Kovalam | /munnar-alleppey-kovalam-05-days | 4 Nights / 5 Days | 3 | Munnar → Kovalam | Arrive Cochin / Kovalam | duration conflict: label 5 days, URL 5 days, itinerary 3 days, page title 5 days, description 5 days; same or near-same title as 0082; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0081 | `slug:munnar-thekkady-alleppey-05-days` | Munnar Thekkady Alleppey | /munnar-thekkady-alleppey-05-days | 4 Nights / 5 Days | 5 | Munnar → Thekkady → Alleppey → Cochin | Arrive Cochin / Alleppey | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0082 | `slug:cochin-munnar-thekkedy-alleppey-06-days` | Munnar Alleppey Kovalam | /cochin-munnar-thekkedy-alleppey-06-days | 5 Nights / 6 Days | 5 | Cochin → Munnar → Thekkady → Alleppey | Arrive Cochin / Alleppey Houseboat | duration conflict: label 6 days, URL 6 days, itinerary 5 days, page title 6 days, description 6 days; title/URL names Kovalam, which the itinerary never mentions; no exclusions listed; same or near-same title as 0080; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0083 | `slug:munnar-alleppey-kovalam-06-days` | Munnar Alleppey Tour Package | /munnar-alleppey-kovalam-06-days | 5 Nights / 6 Days | — | — | — | no day-by-day itinerary on the page (not found anywhere in the project or git history); same or near-same title as 0078, 0084; no package image (photos to be supplied by the owner) | HIGH | REVIEW: approve the ID, but keep the page as Draft until an itinerary is supplied | |
| 0084 | `slug:munnar-thekkady-alleppey-kovalam-trivandurum-07-days` | Munnar Alleppey Tour Package | /munnar-thekkady-alleppey-kovalam-trivandurum-07-days | 6 Nights / 7 Days | 7 | Munnar → Thekkady → Alleppey → Kovalam | Arrive Cochin / Kovalam | no inclusions listed; no exclusions listed; same or near-same title as 0083, 0078; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE; consider a distinguishing title | |
| 0085 | `slug:munnar-thekkady-alleppey--kovalam-kanyakumari-07-days` | Munnar Thekkady Alleppey Kovalam Kanyakumari | /munnar-thekkady-alleppey--kovalam-kanyakumari-07-days | 6 Nights / 7 Days | 7 | Munnar → Thekkady → Alleppey → Kovalam → Trivandrum | Arrive Cochin / Kovalam | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0086 | `slug:mysore-coorg-04-days` | Mysore Coorg tour | /mysore-coorg-04-days | 3 Nights / 4 Days | 4 | Mysore → coorg | Arrive Bangalore / Bangalore Drop | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0087 | `slug:mysore-wayand-04-days` | Mysore wayand tour | /mysore-wayand-04-days | 3 Nights / 4 Days | 4 | Mysore → Wayanad | Arrive Bangalore / Bangalore | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0088 | `slug:bangalore-mysore-ooty-tour-05-days` | Bangalore Mysore Ooty tour | /bangalore-mysore-ooty-tour-05-days | 4 Nights / 5 Days | 5 | Bangalore → Mysore → Ooty | Bangalore / Bangalore Drop | no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0089 | `slug:banglore-mysore-coorg--tour-05-days` | Banglore Mysore Coorg Tour | /banglore-mysore-coorg--tour-05-days | 4 Nights / 5 Days | 5 | Bangalore → Mysuru → coorg | Bangalore / Bangalore Drop | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0090 | `slug:mysore-ooty-kodaikanal-06-days` | Mysore Ooty Kodaikanal Tour | /mysore-ooty-kodaikanal-06-days | 5 Nights / 6 Days | 6 | Mysore → Ooty → Kodaikanal | Arrive Bangalore / Bangalore Drop | no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0091 | `slug:ooty-mysore-04-days` | Ooty Mysore tour | /ooty-mysore-04-days | 5 Nights / 6 Days | 4 | Mysore → Ooty | Arrive Bangalore / Bangalore Drop | duration conflict: label 6 days, URL 4 days, itinerary 4 days, page title 4 days, description 4 days; no inclusions listed; no exclusions listed; no package image (photos to be supplied by the owner) | HIGH | APPROVE after correcting the duration data (the Package ID does not change) | |
| 0092 | `slug:delightful-goa-tour-3n-4d` | Delightful Goa Tour | /delightful-goa-tour-3n-4d | 3 Nights / 4 Days | 4 | Goa | Goa / Goa | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0093 | `slug:enticing-tour-to-goa-3n-4d` | Enticing Tour to Goa | /enticing-tour-to-goa-3n-4d | 3 Nights / 4 Days | 4 | Goa | Goa / Goa | duration conflict: label 4 days, URL 4 days, itinerary 4 days, page title 4 days, description 5 days; no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0094 | `slug:exclusive-goa-tour-3n-4d` | Exclusive Goa Tour | /exclusive-goa-tour-3n-4d | 3 Nights / 4 Days | 4 | Goa | Goa / Goa | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0095 | `slug:fascinating-tour-to-goa-3n-4d` | Fascinating Tour to Goa | /fascinating-tour-to-goa-3n-4d | 3 Nights / 4 Days | 4 | Goa | Goa / Goa | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0096 | `slug:sun-kissed-goa-escape` | Sun Kissed Goa Escape Tour Package | /sun-kissed-goa-escape | 3 Nights / 4 Days | 4 | Guirim | Guirim / Guirim | title/URL names Goa, which the itinerary never mentions; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0097 | `slug:best-of-dubai-tour` | Best of Dubai tour | /best-of-dubai-tour | 2 Nights / 3 Days | 3 | Dubai | Dubai / Dubai | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0098 | `slug:dubai-family-trip-with-free-burj-khalifa-tickets` | Dubai Family Trip with FREE Burj Khalifa Tickets | /dubai-family-trip-with-free-burj-khalifa-tickets | 2 Nights / 3 Days | 3 | Dubai | Dubai / Dubai | no exclusions listed; no package image (photos to be supplied by the owner) | MEDIUM | APPROVE | |
| 0099 | `slug:best-dubai-tour` | Best Dubai Tour | /best-dubai-tour | 3 Nights / 4 Days | 4 | Dubai | Dubai / Dubai | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0100 | `slug:dubai-travel-packages` | Dubai Travel Packages | /dubai-travel-packages | 3 Nights / 4 Days | 4 | Dubai | Dubai / Dubai | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0101 | `slug:deluxe-tour-to-dubai-4n5d` | Deluxe Tour to Dubai | /deluxe-tour-to-dubai-4n5d | 4 Nights / 5 Days | 5 | Dubai | Dubai / Dubai | no package image (photos to be supplied by the owner) | LOW | REVIEW, potential duplicate: add the real hotel/category difference (then APPROVE both) or MERGE | |
| 0102 | `slug:standard-tour-to-dubai-4n5d` | Standard Tour to Dubai | /standard-tour-to-dubai-4n5d | 4 Nights / 5 Days | 5 | Dubai | Dubai / Dubai | no package image (photos to be supplied by the owner) | LOW | REVIEW, potential duplicate: add the real hotel/category difference (then APPROVE both) or MERGE | |
| 0103 | `slug:singapore-and-kuala-lumpur-tour-4n-5d` | Singapore and Kuala Lumpur Tour | /singapore-and-kuala-lumpur-tour-4n-5d | 4 Nights / 5 Days | 5 | Singapore → Kuala Lumpur | Singapore / Kuala Lumpur | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0104 | `slug:the-magical-tour-to-singapore` | The Magical Tour to Singapore tour | /the-magical-tour-to-singapore | 4 Nights / 5 Days | 5 | Singapore → Sentosa → Singapore | Singapore / Singapore | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0105 | `slug:serene-tour-to-singapore-with-thailand--8n-9d` | Serene Tour to Singapore with Thailand | /serene-tour-to-singapore-with-thailand--8n-9d | 8 Nights / 9 Days | 9 | Singapore | Singapore / Singapore | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0106 | `slug:the-best-of-singapore-and-kuala-lumpur-with-pattaya-tour-8n-9d` | The Best of Singapore and Kuala Lumpur with Pattaya tour | /the-best-of-singapore-and-kuala-lumpur-with-pattaya-tour-8n-9d | 8 Nights / 9 Days | 9 | Singapore → Kuala Lumpur → Pattaya → Bangkok | Singapore / Bangkok | no package image (photos to be supplied by the owner) | LOW | APPROVE | |
| 0107 | `slug:maldives-05-days` | Maldives | /maldives-05-days | 4 Nights / 5 Days | 5 | maldives | maldives / maldives | no package image (photos to be supplied by the owner) | LOW | APPROVE | |

## H. Database reconciliation requirement (gate)

**The permanent numbering cannot be completed from the website alone.**

**Evidence that other package data exists**
- The old site had database-driven package pages: `package-details.php` (table `yatra_package`) and `theme-package-details.php` (table `theme_package`).
- They were redirected in Phase 1. Their data lives only in the MySQL database, which has not been supplied.
- No other package-like page exists in the old-site copy beyond the 107 static pages and these two templates.

**Needed from the owner**
- MySQL export (structure and data, from a backup, not live access);
- the `admin/` folder;
- any other package source (spreadsheets, brochures, CRM lists).

**Then each package is classified as:**

| Class | Meaning |
|---|---|
| Website only | On a static page, not in the database |
| Database only | In `yatra_package` / `theme_package`, no static page |
| Admin only | Only in the admin/CMS data |
| Duplicate | The same trip in more than one source |
| Missing | Referenced (menu, sitemap, enquiry) but with no content |
| Archived | Present but marked inactive |
| Unclear | Cannot be matched without owner input |

**Current classification:** all 107 are **Website (database not yet compared)**.

**Rule:** the final sequence is generated only after the complete package inventory is established. No number is reserved for a package later found to be a duplicate, unless you explicitly approve that reservation.

## I. Proposed next steps

1. **Owner:** send the MySQL export, `admin/` and any other package source.
2. **Reconcile** the database and admin inventory against these 107 records (classes in H). Add any database-only packages to the review.
3. **Owner decisions** in `OWNER-TOUR-NUMBER-DECISIONS.csv`, starting with the four priority pairs (C.1–C.4).
4. **Content corrections**, only after your decision: duration labels, 0054, 0013's itinerary, the Dubai hotel data and distinguishing titles.
5. **Generate the final sequence** from the approved inventory only. Then run the post-assignment tests. Only after that does the Package ID go live, in the itinerary.

**Acceptance check for this stage**

- [x] 107 records reviewed
- [x] 41 overlap pairs reviewed
- [x] 0054/0055, 0101/0102, 0066/0067 and 0013/0014 in owner-decision state
- [x] No package data, hotels or itinerary fabricated: all values come from the pages
- [x] No permanent numbers assigned (registry all `proposed`)
- [x] Owner Decision column blank
- [x] Database dependency documented (section H)

