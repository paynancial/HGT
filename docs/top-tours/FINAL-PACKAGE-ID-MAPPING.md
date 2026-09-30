# Package ID: final mapping for owner approval

**Status: PROPOSED. No number is assigned.**
- Nothing is migrated, deployed or published.
- The live website shows no Package ID until you approve.
- Built at commit `ce5339f` or later.

**Numbers proposed:** 107 (0001 to 0107), one per current package.

**Needs your decision:**
- 45 rows marked "possible overlap", in 41 pairs.
- 17 rows with page-data problems.
- 14 rows whose title is the same as, or nearly the same as, another tour.

**Files:**
- Machine-readable sheet: [`PACKAGE-ID-MAPPING-FINAL.csv`](PACKAGE-ID-MAPPING-FINAL.csv). Every row has Approval Status **PENDING**.
- Generator: `tools/package_id_review.py`. It re-runs from the package data, so every figure below comes from the pages themselves.

## How to decide

Write one of these in the **Owner Decision** column of the decision table (section 3). You can also edit the CSV's Approval Status.

| Decision | Meaning |
|---|---|
| **APPROVED** | The tour gets its proposed Package ID permanently. |
| **MERGED** | The tour is combined into another tour. It gets no active number. Its URL redirects (301) to the kept tour, and the merge is recorded. |
| **RETIRED** | The tour is withdrawn. Its URL redirects to its destination page. Its proposed number is reserved and never given to another tour without your approval. |
| **PENDING** | Not decided yet. It gets no number. |

**Suggested actions** (a suggestion only; you decide):
- **KEEP**: materially different.
- **KEEP (correct page data)**: keep it, but the page has a wrong duration or URL label; fix the label, the number stays the same.
- **KEEP (rename to tell apart)**: identical titles confuse customers and staff.
- **REVIEW**: the evidence is not conclusive.
- **MERGE or RETIRE one**: the two pages appear to be the same trip.

**Evidence used:**
- **Itinerary similarity:** word-level text similarity of the day-by-day plans (100% = identical).
- **Inclusion similarity:** the same measure for the inclusion lists.
- Also: trip length, start city, hotel category and meal plan.

## 1. Complete mapping

- **Internal ref:** the website has no database key for these pages; the old database was not provided. The interim internal key is `slug:<page URL>`. It becomes the database internal key (`package_pk`) when the CMS database is created. The Package ID stays the same.
- **Current Status:** every tour is a published static page with no approved rate (Price on request).

| Proposed Package ID | Internal ref | Package Name | Destination | Duration | Current URL | Current Status | Overlap Flag | Recommended Review Action |
|---|---|---|---|---|---|---|---|---|
| 0001 | `slug:srinagar-gulmarg-tour-03nt04dy` | Srinagar Gulmarg Tour Package | Kashmir | 3 Nights / 4 Days | /srinagar-gulmarg-tour-03nt04dy | Published | — | KEEP |
| 0002 | `slug:srinagar-gulmarg-pahalgam-tour-package-5-days` | Srinagar Gulmarg Pahalgam Tour Package | Kashmir | 4 Nights / 5 Days | /srinagar-gulmarg-pahalgam-tour-package-5-days | Published | — | KEEP |
| 0003 | `slug:srinagar-gulmarg-sonmarg-day-trip-tour-package-5-days` | Srinagar Gulmarg Sonmarg Day Trip Tour | Kashmir | 4 Nights / 5 Days | /srinagar-gulmarg-sonmarg-day-trip-tour-package-5-days | Published | — | KEEP |
| 0004 | `slug:srinagar-pahalgam-gulmarg-sonmarg-package-6-days` | Srinagar Pahalgam Gulmarg Sonmarg Tour | Kashmir | 4 Nights / 5 Days | /srinagar-pahalgam-gulmarg-sonmarg-package-6-days | Published | — | KEEP (correct page data) |
| 0005 | `slug:srinagar-gulmarg-pahalgam-tour-package-7-days` | Srinagar Gulmarg Pahalgam Tour | Kashmir | 6 Nights / 7 Days | /srinagar-gulmarg-pahalgam-tour-package-7-days | Published | — | KEEP (correct page data) |
| 0006 | `slug:srinagar-gulmarg-sonmarg-pahalgam-katra-tour-package-8-days` | Srinagar Gulmarg Sonmarg Pahalgam Katra Tour | Kashmir | 7 Nights / 8 Days | /srinagar-gulmarg-sonmarg-pahalgam-katra-tour-package-8-days | Published | — | KEEP |
| 0007 | `slug:breathtaking-leh-ladakh-tour-3n-4d` | Breathtaking Leh Ladakh Tour | Leh Ladakh | 3 Nights / 4 Days | /breathtaking-leh-ladakh-tour-3n-4d | Published | — | KEEP |
| 0008 | `slug:special-leh-ladakh-tour-4n-5d` | Special Leh Ladakh Tour | Leh Ladakh | 4 Nights / 5 Days | /special-leh-ladakh-tour-4n-5d | Published | — | KEEP |
| 0009 | `slug:jewels-of-leh-ladakh-package-5n6d` | Jewels of Leh Ladakh Tour | Leh Ladakh | 5 Nights / 6 Days | /jewels-of-leh-ladakh-package-5n6d | Published | Yes: 0010 | KEEP (correct page data) |
| 0010 | `slug:panorama-ladakh-tour-6n7d` | Panorama Ladakh tour | Leh Ladakh | 5 Nights / 6 Days | /panorama-ladakh-tour-6n7d | Published | Yes: 0009 | KEEP (correct page data) |
| 0011 | `slug:serene-leh-ladakh-tour-7n-8d` | Serene Leh Ladakh Tour | Leh Ladakh | 7 Nights / 8 Days | /serene-leh-ladakh-tour-7n-8d | Published | Yes: 0012 | KEEP (correct page data) |
| 0012 | `slug:discover-leh-ladakh-tour-8n-9d` | Discover Leh Ladakh Tour | Leh Ladakh | 8 Nights / 9 Days | /discover-leh-ladakh-tour-8n-9d | Published | Yes: 0011 | KEEP (correct page data) |
| 0013 | `slug:best-of-shimla-vacation` | Best of Shimla Vacation | Himachal Pradesh | 2 Nights / 3 Days | /best-of-shimla-vacation | Published | Yes: 0014 | REVIEW |
| 0014 | `slug:best-of-shimla` | Best of Shimla | Himachal Pradesh | 3 Nights / 4 Days | /best-of-shimla | Published | Yes: 0013 | REVIEW |
| 0015 | `slug:amritsar-with-dalhousie-dharamshala-05-days` | Amritsar with Dalhousie Dharamshala | Himachal Pradesh | 4 Nights / 5 Days | /amritsar-with-dalhousie-dharamshala-05-days | Published | Yes: 0019 | KEEP |
| 0016 | `slug:blissful-tour-to-himachal-pradesh-4n-5d` | Blissful Tour to Himachal Pradesh | Himachal Pradesh | 4 Nights / 5 Days | /blissful-tour-to-himachal-pradesh-4n-5d | Published | — | KEEP |
| 0017 | `slug:manali-volvo-vrip-weekend` | Manali Volvo Trip weekend | Himachal Pradesh | 4 Nights / 5 Days | /manali-volvo-vrip-weekend | Published | Yes: 0020 | KEEP |
| 0018 | `slug:manali-with-manikaran` | Manali with Manikaran | Himachal Pradesh | 4 Nights / 5 Days | /manali-with-manikaran | Published | — | KEEP |
| 0019 | `slug:amrirsar-with-dalhousie-and-dharamshala-06-days` | Amrirsar with Dalhousie and Dharamshala | Himachal Pradesh | 5 Nights / 6 Days | /amrirsar-with-dalhousie-and-dharamshala-06-days | Published | Yes: 0015 | KEEP |
| 0020 | `slug:best-of-manali-with-delhi-by-volvo` | Best of Manali with Delhi By Volvo | Himachal Pradesh | 5 Nights / 6 Days | /best-of-manali-with-delhi-by-volvo | Published | Yes: 0017 | KEEP |
| 0021 | `slug:manali-with-dharamshala-and-amritsar-06-days` | Manali with Dharamshala and amritsar | Himachal Pradesh | 5 Nights / 6 Days | /manali-with-dharamshala-and-amritsar-06-days | Published | — | KEEP |
| 0022 | `slug:shimla-manali-tour-06-days` | Shimla Manali Tour | Himachal Pradesh | 5 Nights / 6 Days | /shimla-manali-tour-06-days | Published | Yes: 0023 | KEEP |
| 0023 | `slug:shimla-manali-with-chandigarh-06-days` | Shimla Manali with Chandigarh | Himachal Pradesh | 5 Nights / 6 Days | /shimla-manali-with-chandigarh-06-days | Published | Yes: 0022 | KEEP |
| 0024 | `slug:amritsar-with-dalhousie-dharamshala-and-chandigarh-07-days` | Amritsar with Dalhousie Dharamshala and Chandigarh | Himachal Pradesh | 6 Nights / 7 Days | /amritsar-with-dalhousie-dharamshala-and-chandigarh-07-days | Published | — | KEEP |
| 0025 | `slug:best-of-shimla-and-manali` | Best of Shimla and Manali | Himachal Pradesh | 6 Nights / 7 Days | /best-of-shimla-and-manali | Published | — | KEEP |
| 0026 | `slug:shimla-manali-with-dalhousie-amritsar-08-days` | Shimla Manali with Dalhousie Amritsar | Himachal Pradesh | 7 Nights / 8 Days | /shimla-manali-with-dalhousie-amritsar-08-days | Published | — | KEEP |
| 0027 | `slug:shimla-manali-with-dharmshala-dalhousie-and-amritsar-09-days` | Shimla Manali with Dharmshala Dalhousie and Amritsar | Himachal Pradesh | 8 Nights / 9 Days | /shimla-manali-with-dharmshala-dalhousie-and-amritsar-09-days | Published | — | KEEP |
| 0028 | `slug:grand-himachal-with-mata-vaishno-devi--tour-13-days` | Grand Himachal with Mata vaishno Devi Tour | Himachal Pradesh | 12 Nights / 13 Days | /grand-himachal-with-mata-vaishno-devi--tour-13-days | Published | — | KEEP |
| 0029 | `slug:haridwar-rishikesh-tour-03-days` | Haridwar Rishikesh Tour | Uttarakhand | 2 Nights / 3 Days | /haridwar-rishikesh-tour-03-days | Published | — | KEEP |
| 0030 | `slug:mussoorie-tour-03-days` | Mussoorie Tour | Uttarakhand | 2 Nights / 3 Days | /mussoorie-tour-03-days | Published | — | KEEP |
| 0031 | `slug:nainital-tour-03-days` | Nainital Tour | Uttarakhand | 2 Nights / 3 Days | /nainital-tour-03-days | Published | — | KEEP |
| 0032 | `slug:uttarakhand-mussoorie-with-rishikesh-flight-inclusive` | Uttarakhand Mussoorie With Rishikesh Flight Inclusive | Uttarakhand | 2 Nights / 3 Days | /uttarakhand-mussoorie-with-rishikesh-flight-inclusive | Published | — | KEEP |
| 0033 | `slug:corbett-with-nainital-04-days` | Corbett with Nainital | Uttarakhand | 3 Nights / 4 Days | /corbett-with-nainital-04-days | Published | — | KEEP |
| 0034 | `slug:haridwar-rishikesh-mussoorie-04-days` | Haridwar Rishikesh Mussoorie | Uttarakhand | 3 Nights / 4 Days | /haridwar-rishikesh-mussoorie-04-days | Published | — | KEEP |
| 0035 | `slug:mussoorie-with-jim-corbett-04-days` | Mussoorie with Jim Corbett | Uttarakhand | 3 Nights / 4 Days | /mussoorie-with-jim-corbett-04-days | Published | — | KEEP |
| 0036 | `slug:haridwar-with-mussoorie-and-jim-corbett-05-days` | Haridwar with Mussoorie and Jim Corbett | Uttarakhand | 4 Nights / 5 Days | /haridwar-with-mussoorie-and-jim-corbett-05-days | Published | Yes: 0040 | KEEP |
| 0037 | `slug:nainital-with-almora-and-jim-corbett-05-days` | Nainital with Almora and jim corbett | Uttarakhand | 4 Nights / 5 Days | /nainital-with-almora-and-jim-corbett-05-days | Published | — | KEEP (correct page data) |
| 0038 | `slug:nainital-with-kausani-and-jim-corbett-05-days` | Nainital with Kausani and Jim Corbett | Uttarakhand | 4 Nights / 5 Days | /nainital-with-kausani-and-jim-corbett-05-days | Published | Yes: 0042 | KEEP |
| 0039 | `slug:nainital-with-ranikhet--jim-corbett` | Nainital with Ranikhet Jim Corbett | Uttarakhand | 4 Nights / 5 Days | /nainital-with-ranikhet--jim-corbett | Published | Yes: 0043 | KEEP |
| 0040 | `slug:haridwar--with-mussoorie-and-corbett-06-days` | Haridwar with Mussoorie and Corbett | Uttarakhand | 5 Nights / 6 Days | /haridwar--with-mussoorie-and-corbett-06-days | Published | Yes: 0036 | KEEP |
| 0041 | `slug:mussoorie-with-nainital-and-jim-corbett-06-days` | Mussoorie with Nainital and Jim Corbett | Uttarakhand | 5 Nights / 6 Days | /mussoorie-with-nainital-and-jim-corbett-06-days | Published | Yes: 0044 | KEEP |
| 0042 | `slug:nainital-with-kausani-and-jim-corbett-06-days` | Nainital with Kausani and Jim Corbett | Uttarakhand | 5 Nights / 6 Days | /nainital-with-kausani-and-jim-corbett-06-days | Published | Yes: 0038 | KEEP |
| 0043 | `slug:nainital-with-ranikhet-and-jim-corbett-06-days` | Nainital with Ranikhet and jim corbett | Uttarakhand | 5 Nights / 6 Days | /nainital-with-ranikhet-and-jim-corbett-06-days | Published | Yes: 0039 | KEEP |
| 0044 | `slug:corbett-with-nainital-and-mussoorie-07-days` | Corbett With Nainital and mussoorie | Uttarakhand | 6 Nights / 7 Days | /corbett-with-nainital-and-mussoorie-07-days | Published | Yes: 0041 | KEEP |
| 0045 | `slug:haridwar-with-rishikesh-mussoorie-nainital-and-jim-corbett-07-days` | Haridwar With Rishikesh Mussoorie Nainital and Jim Corbett | Uttarakhand | 6 Nights / 7 Days | /haridwar-with-rishikesh-mussoorie-nainital-and-jim-corbett-07-days | Published | — | KEEP |
| 0046 | `slug:mussoorie-with-nainital-almora-and-jim-corbett--07-days` | Mussoorie with Nainital Almora and Jim Corbett | Uttarakhand | 6 Nights / 7 Days | /mussoorie-with-nainital-almora-and-jim-corbett--07-days | Published | — | KEEP |
| 0047 | `slug:nainital-with-ranikhet-kausani-and-jim-corbett-07-days` | Nainital with Ranikhet kausani and Jim corbett | Uttarakhand | 6 Nights / 7 Days | /nainital-with-ranikhet-kausani-and-jim-corbett-07-days | Published | — | KEEP |
| 0048 | `slug:haridwar-with-mussoorie-corbett-kausani-and-nainital-08-days` | Haridwar with Mussoorie corbett Kausani and Nainital Tour | Uttarakhand | 7 Nights / 8 Days | /haridwar-with-mussoorie-corbett-kausani-and-nainital-08-days | Published | — | KEEP |
| 0049 | `slug:mussoorie-with-corbett-ranikhet-kausani-and-nainital-08-days` | Mussoorie with corbett Ranikhet Kausani and Nainital | Uttarakhand | 7 Nights / 8 Days | /mussoorie-with-corbett-ranikhet-kausani-and-nainital-08-days | Published | — | KEEP |
| 0050 | `slug:nainital-with-ranikhet-kausani-jim-corbett-and-haridwar-08-days` | Nainital with Ranikhet Kausani jim Corbett and Haridwar | Uttarakhand | 7 Nights / 8 Days | /nainital-with-ranikhet-kausani-jim-corbett-and-haridwar-08-days | Published | — | KEEP |
| 0051 | `slug:do-dham-kedar–badri-from-haridwar-4n-5d` | Do Dham Yatra (Kedarnath - Badrinath) Tour from Haridwar 5 Days Package | Char Dham Yatra | 04 Nights / 05 Days | /do-dham-kedar–badri-from-haridwar-4n-5d | Published | Yes: 0052 | KEEP |
| 0052 | `slug:do-dham-kedar–badri-from-haridwar-5n-6d` | 6-Day Do Dham Yatra (Kedarnath & Badrinath) Tour from Haridwar | Char Dham Yatra | 5 Nights / 6 Days | /do-dham-kedar–badri-from-haridwar-5n-6d | Published | Yes: 0051 | KEEP |
| 0053 | `slug:chardham-yatra-by-helicopter` | Chardham Yatra By Helicopter Tour | Char Dham Yatra | 5 Nights / 6 Days | /chardham-yatra-by-helicopter | Published | — | KEEP |
| 0054 | `slug:char-dham-yatra-from-delhi-11n-12d` | Char Dham yatra Package from Haridwar for 9 Days | Char Dham Yatra | 08 Nights / 09 Days | /char-dham-yatra-from-delhi-11n-12d | Published | Yes: 0055, 0056 | REVIEW (URL/content mismatch) |
| 0055 | `slug:chardham-yatra-from-haridwar-8n-9d` | Char Dham yatra Package from Haridwar for 9 Days | Char Dham Yatra | 08 Nights / 09 Days | /chardham-yatra-from-haridwar-8n-9d | Published | Yes: 0054, 0056 | REVIEW (see 0054) |
| 0056 | `slug:char-dham-yatra-from-haridwar-9n-10d` | Char Dham yatra Package from Haridwar | Char Dham Yatra | 9 Nights / 10 Days | /char-dham-yatra-from-haridwar-9n-10d | Published | Yes: 0054, 0055 | KEEP (correct page data) |
| 0057 | `slug:yamunotri-gangotri-do-dham-from-delhi-6n-7d` | Yamunotri Gangotri Do Dham Yatra from Delhi | Char Dham Yatra | 9 Nights / 10 Days | /yamunotri-gangotri-do-dham-from-delhi-6n-7d | Published | — | KEEP (correct page data) |
| 0058 | `slug:char-dham-yatra-from-delhi-10n-11d` | Char Dham yatra Package from delhi for 11 Days | Char Dham Yatra | 10 Nights / 11 Days | /char-dham-yatra-from-delhi-10n-11d | Published | — | KEEP |
| 0059 | `slug:chardham-package` | Chardham Package 12 Days Ex Delhi Tour | Char Dham Yatra | 11 Nights / 12 Days | /chardham-package | Published | — | KEEP |
| 0060 | `slug:amarnath-ji-yatra-by-helicopter-2n-3d` | Amarnath Ji Tour Package by Helicopter for 2 Nights 3 Days | Amarnath Yatra | 2 Nights / 3 Days | /amarnath-ji-yatra-by-helicopter-2n-3d | Published | Yes: 0061 | KEEP |
| 0061 | `slug:amarnath-ji-yatra-by-helicopter-3n-4d` | Amarnath Ji Yatra by Helicopter – 3 Nights 4 Days with Srinagar Tour | Amarnath Yatra | 3 Nights / 4 Days | /amarnath-ji-yatra-by-helicopter-3n-4d | Published | Yes: 0060 | KEEP |
| 0062 | `slug:amarnath-ji-yatra-with-srinagar-4n-5d` | Amarnath Ji Yatra with Srinagar – 4 Nights 5 Days Package | Amarnath Yatra | 4 Nights / 5 Days | /amarnath-ji-yatra-with-srinagar-4n-5d | Published | — | KEEP |
| 0063 | `slug:amarnath-ji-yatra-via-pahalgam-5n-6d` | Amarnath Ji Yatra via Pahalgam 5 Nights 6 Days Spiritual Journey | Amarnath Yatra | 5 Nights / 6 Days | /amarnath-ji-yatra-via-pahalgam-5n-6d | Published | — | KEEP |
| 0064 | `slug:darjeeling-gangtok-04-days` | Darjeeling Gangtok Tour | Darjeeling & Sikkim | 3 Nights / 4 Days | /darjeeling-gangtok-04-days | Published | Yes: 0066, 0067 | KEEP (correct page data) |
| 0065 | `slug:darjeeling-kalimpong-04-days` | Darjeeling Kalimpong Tour | Darjeeling & Sikkim | 3 Nights / 4 Days | /darjeeling-kalimpong-04-days | Published | — | KEEP |
| 0066 | `slug:darjeeling-and-gangtok-06-days` | Darjeeling and Gangtok Tour | Darjeeling & Sikkim | 4 Nights / 5 Days | /darjeeling-and-gangtok-06-days | Published | Yes: 0064, 0067 | REVIEW |
| 0067 | `slug:darjeeling-gangtok-05-days` | Darjeeling Gangtok Tour | Darjeeling & Sikkim | 4 Nights / 5 Days | /darjeeling-gangtok-05-days | Published | Yes: 0064, 0066 | REVIEW |
| 0068 | `slug:gangtok-pelling-tour-05-days` | Gangtok Pelling Tour | Darjeeling & Sikkim | 4 Nights / 5 Days | /gangtok-pelling-tour-05-days | Published | — | KEEP |
| 0069 | `slug:darjeeling-with-kalimpong-and-gangtok-06-days` | Darjeeling with Kalimpong and Gangtok Tour | Darjeeling & Sikkim | 5 Nights / 6 Days | /darjeeling-with-kalimpong-and-gangtok-06-days | Published | — | KEEP |
| 0070 | `slug:darjeeling-with-pelling-and-gangtok-06-days` | Darjeeling with Pelling and Gangtok Tour | Darjeeling & Sikkim | 5 Nights / 6 Days | /darjeeling-with-pelling-and-gangtok-06-days | Published | — | KEEP |
| 0071 | `slug:gangtok-with-lachen-and-lachung-07-days` | Gangtok with lachen and Lachung Tour | Darjeeling & Sikkim | 5 Nights / 6 Days | /gangtok-with-lachen-and-lachung-07-days | Published | — | KEEP (correct page data) |
| 0072 | `slug:gangtok-with-pelling-and-kalimpong-tour-07-days` | Gangtok with Pelling and Kalimpong Tour | Darjeeling & Sikkim | 6 Nights / 7 Days | /gangtok-with-pelling-and-kalimpong-tour-07-days | Published | — | KEEP |
| 0073 | `slug:darjeeling-with-lachung-and-gangtok-08-days` | Darjeeling with Lachung and gangtok Tour | Darjeeling & Sikkim | 7 Nights / 8 Days | /darjeeling-with-lachung-and-gangtok-08-days | Published | — | KEEP |
| 0074 | `slug:gangtok-with-lachung-pelling-and-darjeeling-08-days` | Gangtok with Lachung Pelling and Darjeeling Tour | Darjeeling & Sikkim | 7 Nights / 8 Days | /gangtok-with-lachung-pelling-and-darjeeling-08-days | Published | — | KEEP |
| 0075 | `slug:kalimpong-with-gangtok-pelling-and-darjeeling-08-days` | Kalimpong With Gangtok pelling and darjeeling Tour | Darjeeling & Sikkim | 7 Nights / 8 Days | /kalimpong-with-gangtok-pelling-and-darjeeling-08-days | Published | — | KEEP |
| 0076 | `slug:darjeeling-with-kalimpong-pelling-lachung-and-gangtok-10-days` | Darjeeling with Kalimpong pelling Lachung and gangtok Tour | Darjeeling & Sikkim | 9 Nights / 10 Days | /darjeeling-with-kalimpong-pelling-lachung-and-gangtok-10-days | Published | — | KEEP |
| 0077 | `slug:kovalam-kanyakumari-tour-4-days` | Kovalam Kanyakumari Tour | Kerala | 3 Nights / 4 Days | /kovalam-kanyakumari-tour-4-days | Published | — | KEEP (correct page data) |
| 0078 | `slug:munnar-alleppey-tour-package-04-days` | Munnar Alleppey Tour Package | Kerala | 3 Nights / 4 Days | /munnar-alleppey-tour-package-04-days | Published | — | KEEP (rename to tell apart) |
| 0079 | `slug:munnar-thekkady-tour-package-4-days` | Munnar Thekkady Tour Package | Kerala | 3 Nights / 4 Days | /munnar-thekkady-tour-package-4-days | Published | — | KEEP (correct page data) |
| 0080 | `slug:munnar-alleppey-kovalam-05-days` | Munnar Alleppey Kovalam | Kerala | 4 Nights / 5 Days | /munnar-alleppey-kovalam-05-days | Published | — | KEEP (correct page data) |
| 0081 | `slug:munnar-thekkady-alleppey-05-days` | Munnar Thekkady Alleppey | Kerala | 4 Nights / 5 Days | /munnar-thekkady-alleppey-05-days | Published | — | KEEP |
| 0082 | `slug:cochin-munnar-thekkedy-alleppey-06-days` | Munnar Alleppey Kovalam | Kerala | 5 Nights / 6 Days | /cochin-munnar-thekkedy-alleppey-06-days | Published | — | KEEP (correct page data) |
| 0083 | `slug:munnar-alleppey-kovalam-06-days` | Munnar Alleppey Tour Package | Kerala | 5 Nights / 6 Days | /munnar-alleppey-kovalam-06-days | Published | — | KEEP (correct page data) |
| 0084 | `slug:munnar-thekkady-alleppey-kovalam-trivandurum-07-days` | Munnar Alleppey Tour Package | Kerala | 6 Nights / 7 Days | /munnar-thekkady-alleppey-kovalam-trivandurum-07-days | Published | — | KEEP (rename to tell apart) |
| 0085 | `slug:munnar-thekkady-alleppey--kovalam-kanyakumari-07-days` | Munnar Thekkady Alleppey Kovalam Kanyakumari | Kerala | 6 Nights / 7 Days | /munnar-thekkady-alleppey--kovalam-kanyakumari-07-days | Published | — | KEEP |
| 0086 | `slug:mysore-coorg-04-days` | Mysore Coorg tour | Ooty, Mysore & Coorg | 3 Nights / 4 Days | /mysore-coorg-04-days | Published | Yes: 0089 | KEEP |
| 0087 | `slug:mysore-wayand-04-days` | Mysore wayand tour | Ooty, Mysore & Coorg | 3 Nights / 4 Days | /mysore-wayand-04-days | Published | — | KEEP |
| 0088 | `slug:bangalore-mysore-ooty-tour-05-days` | Bangalore Mysore Ooty tour | Ooty, Mysore & Coorg | 4 Nights / 5 Days | /bangalore-mysore-ooty-tour-05-days | Published | Yes: 0091 | KEEP (correct page data) |
| 0089 | `slug:banglore-mysore-coorg--tour-05-days` | Banglore Mysore Coorg Tour | Ooty, Mysore & Coorg | 4 Nights / 5 Days | /banglore-mysore-coorg--tour-05-days | Published | Yes: 0086 | KEEP |
| 0090 | `slug:mysore-ooty-kodaikanal-06-days` | Mysore Ooty Kodaikanal Tour | Ooty, Mysore & Coorg | 5 Nights / 6 Days | /mysore-ooty-kodaikanal-06-days | Published | — | KEEP |
| 0091 | `slug:ooty-mysore-04-days` | Ooty Mysore tour | Ooty, Mysore & Coorg | 5 Nights / 6 Days | /ooty-mysore-04-days | Published | Yes: 0088 | KEEP (correct page data) |
| 0092 | `slug:delightful-goa-tour-3n-4d` | Delightful Goa Tour | Goa | 3 Nights / 4 Days | /delightful-goa-tour-3n-4d | Published | Yes: 0093, 0094, 0095, 0096 | KEEP |
| 0093 | `slug:enticing-tour-to-goa-3n-4d` | Enticing Tour to Goa | Goa | 3 Nights / 4 Days | /enticing-tour-to-goa-3n-4d | Published | Yes: 0092, 0094, 0095, 0096 | KEEP |
| 0094 | `slug:exclusive-goa-tour-3n-4d` | Exclusive Goa Tour | Goa | 3 Nights / 4 Days | /exclusive-goa-tour-3n-4d | Published | Yes: 0092, 0093, 0095, 0096 | KEEP |
| 0095 | `slug:fascinating-tour-to-goa-3n-4d` | Fascinating Tour to Goa | Goa | 3 Nights / 4 Days | /fascinating-tour-to-goa-3n-4d | Published | Yes: 0092, 0093, 0094, 0096 | KEEP |
| 0096 | `slug:sun-kissed-goa-escape` | Sun Kissed Goa Escape Tour Package | Goa | 3 Nights / 4 Days | /sun-kissed-goa-escape | Published | Yes: 0092, 0093, 0094, 0095 | KEEP |
| 0097 | `slug:best-of-dubai-tour` | Best of Dubai tour | Dubai | 2 Nights / 3 Days | /best-of-dubai-tour | Published | Yes: 0098, 0099, 0100 | KEEP |
| 0098 | `slug:dubai-family-trip-with-free-burj-khalifa-tickets` | Dubai Family Trip with FREE Burj Khalifa Tickets | Dubai | 2 Nights / 3 Days | /dubai-family-trip-with-free-burj-khalifa-tickets | Published | Yes: 0097, 0099, 0100 | KEEP |
| 0099 | `slug:best-dubai-tour` | Best Dubai Tour | Dubai | 3 Nights / 4 Days | /best-dubai-tour | Published | Yes: 0097, 0098, 0100, 0101, 0102 | KEEP |
| 0100 | `slug:dubai-travel-packages` | Dubai Travel Packages | Dubai | 3 Nights / 4 Days | /dubai-travel-packages | Published | Yes: 0097, 0098, 0099, 0101, 0102 | KEEP |
| 0101 | `slug:deluxe-tour-to-dubai-4n5d` | Deluxe Tour to Dubai | Dubai | 4 Nights / 5 Days | /deluxe-tour-to-dubai-4n5d | Published | Yes: 0099, 0100, 0102 | REVIEW |
| 0102 | `slug:standard-tour-to-dubai-4n5d` | Standard Tour to Dubai | Dubai | 4 Nights / 5 Days | /standard-tour-to-dubai-4n5d | Published | Yes: 0099, 0100, 0101 | REVIEW |
| 0103 | `slug:singapore-and-kuala-lumpur-tour-4n-5d` | Singapore and Kuala Lumpur Tour | Singapore & Malaysia | 4 Nights / 5 Days | /singapore-and-kuala-lumpur-tour-4n-5d | Published | — | KEEP |
| 0104 | `slug:the-magical-tour-to-singapore` | The Magical Tour to Singapore tour | Singapore & Malaysia | 4 Nights / 5 Days | /the-magical-tour-to-singapore | Published | — | KEEP |
| 0105 | `slug:serene-tour-to-singapore-with-thailand--8n-9d` | Serene Tour to Singapore with Thailand | Singapore & Malaysia | 8 Nights / 9 Days | /serene-tour-to-singapore-with-thailand--8n-9d | Published | — | KEEP |
| 0106 | `slug:the-best-of-singapore-and-kuala-lumpur-with-pattaya-tour-8n-9d` | The Best of Singapore and Kuala Lumpur with Pattaya tour | Singapore & Malaysia | 8 Nights / 9 Days | /the-best-of-singapore-and-kuala-lumpur-with-pattaya-tour-8n-9d | Published | — | KEEP |
| 0107 | `slug:maldives-05-days` | Maldives | Maldives | 4 Nights / 5 Days | /maldives-05-days | Published | — | KEEP |

## 2. Overlap review, pair by pair

**Why rows are flagged "possible overlap":** the two tours are in the same destination, list the same places, and differ in length by one day or less.

**For each pair:**
- **A:** why it may overlap
- **B:** which tour it overlaps with
- **C:** whether the two appear identical
- **D:** what materially differs
- **E:** suggested action

### 2.1 0009 Jewels of Leh Ladakh Tour ↔ 0010 Panorama Ladakh tour

- **A. Why flagged:** both are Leh Ladakh tours covering Leh, Nubra, Pangong.
  - Lengths: 5 Nights / 6 Days and 5 Nights / 6 Days.
  - Itinerary similarity 43%; inclusion similarity 100%.
- **B. Overlaps with:** 0009 `/jewels-of-leh-ladakh-package-5n6d` ↔ 0010 `/panorama-ladakh-tour-6n7d`
- **C. Appear identical?** No.
- **D. Material differences:** different day-by-day plans.
  - Data note, 0010: URL says 7 days but the page says 5 Nights / 6 Days.
  - Data note, 0010: itinerary lists 7 days but the page says 6 days.
- **E. Suggested:** **KEEP (correct page data)**. Same length but different day-by-day plans.

### 2.2 0011 Serene Leh Ladakh Tour ↔ 0012 Discover Leh Ladakh Tour

- **A. Why flagged:** both are Leh Ladakh tours covering Leh, Nubra, Pangong.
  - Lengths: 7 Nights / 8 Days and 8 Nights / 9 Days.
  - Itinerary similarity 23%; inclusion similarity 100%.
- **B. Overlaps with:** 0011 `/serene-leh-ladakh-tour-7n-8d` ↔ 0012 `/discover-leh-ladakh-tour-8n-9d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 7 Nights / 8 Days vs 8 Nights / 9 Days; different day-by-day plans.
  - Data note, 0012: itinerary lists 8 days but the page says 9 days.
- **E. Suggested:** **KEEP (correct page data)**. Different trip length; each can carry its own Package ID

### 2.3 0013 Best of Shimla Vacation ↔ 0014 Best of Shimla

- **A. Why flagged:** both are Himachal Pradesh tours covering Shimla.
  - Lengths: 2 Nights / 3 Days and 3 Nights / 4 Days.
  - Itinerary similarity —; inclusion similarity 2%.
- **B. Overlaps with:** 0013 `/best-of-shimla-vacation` ↔ 0014 `/best-of-shimla`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 2 Nights / 3 Days vs 3 Nights / 4 Days; different inclusions.
  - Data note, 0013: no day-by-day itinerary on the page.
- **E. Suggested:** **REVIEW**. One of the two has no day-by-day itinerary, so they cannot be compared.

### 2.4 0015 Amritsar with Dalhousie Dharamshala ↔ 0019 Amrirsar with Dalhousie and Dharamshala

- **A. Why flagged:** both are Himachal Pradesh tours covering Dalhousie, Dharamshala, Amritsar.
  - Lengths: 4 Nights / 5 Days and 5 Nights / 6 Days.
  - Itinerary similarity 95%; inclusion similarity 0%.
- **B. Overlaps with:** 0015 `/amritsar-with-dalhousie-dharamshala-05-days` ↔ 0019 `/amrirsar-with-dalhousie-and-dharamshala-06-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 4 Nights / 5 Days vs 5 Nights / 6 Days; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.5 0017 Manali Volvo Trip weekend ↔ 0020 Best of Manali with Delhi By Volvo

- **A. Why flagged:** both are Himachal Pradesh tours covering Manali, Kullu.
  - Lengths: 4 Nights / 5 Days and 5 Nights / 6 Days.
  - Itinerary similarity 39%; inclusion similarity 66%.
- **B. Overlaps with:** 0017 `/manali-volvo-vrip-weekend` ↔ 0020 `/best-of-manali-with-delhi-by-volvo`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 4 Nights / 5 Days vs 5 Nights / 6 Days; different day-by-day plans.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.6 0022 Shimla Manali Tour ↔ 0023 Shimla Manali with Chandigarh

- **A. Why flagged:** both are Himachal Pradesh tours covering Shimla, Manali, Kullu, Chandigarh.
  - Lengths: 5 Nights / 6 Days and 5 Nights / 6 Days.
  - Itinerary similarity 68%; inclusion similarity 4%.
- **B. Overlaps with:** 0022 `/shimla-manali-tour-06-days` ↔ 0023 `/shimla-manali-with-chandigarh-06-days`
- **C. Appear identical?** No.
- **D. Material differences:** different inclusions.
- **E. Suggested:** **KEEP**. Same length but different inclusions.

### 2.7 0036 Haridwar with Mussoorie and Jim Corbett ↔ 0040 Haridwar with Mussoorie and Corbett

- **A. Why flagged:** both are Uttarakhand tours covering Mussoorie, Jim Corbett, Haridwar.
  - Lengths: 4 Nights / 5 Days and 5 Nights / 6 Days.
  - Itinerary similarity 84%; inclusion similarity 0%.
- **B. Overlaps with:** 0036 `/haridwar-with-mussoorie-and-jim-corbett-05-days` ↔ 0040 `/haridwar--with-mussoorie-and-corbett-06-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 4 Nights / 5 Days vs 5 Nights / 6 Days; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.8 0038 Nainital with Kausani and Jim Corbett ↔ 0042 Nainital with Kausani and Jim Corbett

- **A. Why flagged:** both are Uttarakhand tours covering Nainital, Jim Corbett, Kausani.
  - Lengths: 4 Nights / 5 Days and 5 Nights / 6 Days.
  - Itinerary similarity 83%; inclusion similarity 0%.
- **B. Overlaps with:** 0038 `/nainital-with-kausani-and-jim-corbett-05-days` ↔ 0042 `/nainital-with-kausani-and-jim-corbett-06-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 4 Nights / 5 Days vs 5 Nights / 6 Days; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.9 0039 Nainital with Ranikhet Jim Corbett ↔ 0043 Nainital with Ranikhet and jim corbett

- **A. Why flagged:** both are Uttarakhand tours covering Nainital, Jim Corbett, Ranikhet.
  - Lengths: 4 Nights / 5 Days and 5 Nights / 6 Days.
  - Itinerary similarity 62%; inclusion similarity 0%.
- **B. Overlaps with:** 0039 `/nainital-with-ranikhet--jim-corbett` ↔ 0043 `/nainital-with-ranikhet-and-jim-corbett-06-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 4 Nights / 5 Days vs 5 Nights / 6 Days; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.10 0041 Mussoorie with Nainital and Jim Corbett ↔ 0044 Corbett With Nainital and mussoorie

- **A. Why flagged:** both are Uttarakhand tours covering Nainital, Mussoorie, Jim Corbett.
  - Lengths: 5 Nights / 6 Days and 6 Nights / 7 Days.
  - Itinerary similarity 2%; inclusion similarity 0%.
- **B. Overlaps with:** 0041 `/mussoorie-with-nainital-and-jim-corbett-06-days` ↔ 0044 `/corbett-with-nainital-and-mussoorie-07-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 5 Nights / 6 Days vs 6 Nights / 7 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.11 0051 Do Dham Yatra (Kedarnath - Badrinath) Tour from Haridwar 5 Days Package ↔ 0052 6-Day Do Dham Yatra (Kedarnath & Badrinath) Tour from Haridwar

- **A. Why flagged:** both are Char Dham Yatra tours covering Haridwar, Guptkashi, Kedarnath, Badrinath.
  - Lengths: 04 Nights / 05 Days and 5 Nights / 6 Days.
  - Itinerary similarity 87%; inclusion similarity 100%.
- **B. Overlaps with:** 0051 `/do-dham-kedar–badri-from-haridwar-4n-5d` ↔ 0052 `/do-dham-kedar–badri-from-haridwar-5n-6d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 04 Nights / 05 Days vs 5 Nights / 6 Days.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.12 0054 Char Dham yatra Package from Haridwar for 9 Days ↔ 0055 Char Dham yatra Package from Haridwar for 9 Days

- **A. Why flagged:** both are Char Dham Yatra tours covering Rishikesh, Haridwar, Barkot, Uttarkashi, Guptkashi, Yamunotri, Gangotri, Kedarnath, Badrinath.
  - Lengths: 08 Nights / 09 Days and 08 Nights / 09 Days.
  - Itinerary similarity 100%; inclusion similarity 100%.
- **B. Overlaps with:** 0054 `/char-dham-yatra-from-delhi-11n-12d` ↔ 0055 `/chardham-yatra-from-haridwar-8n-9d`
- **C. Appear identical?** **Yes.** Same day plans, inclusions and length.
- **D. Material differences:** none found in the page data.
  - Data note, 0054: URL says 12 days but the page says 08 Nights / 09 Days.
  - Data note, 0054: itinerary lists 10 days but the page says 9 days.
  - Data note, 0054: URL says "from Delhi" but the page content is "Char Dham yatra Package from Haridwar for 9 Days".
  - Data note, 0055: itinerary lists 10 days but the page says 9 days.
- **E. Suggested:** **REVIEW (URL/content mismatch)**. The page at the "from Delhi, 11N/12D" URL carries the Haridwar 8N/9D content word for word. Either write the real Delhi 11N/12D itinerary for it (then KEEP), or RETIRE it and redirect to the Haridwar tour.

### 2.13 0054 Char Dham yatra Package from Haridwar for 9 Days ↔ 0056 Char Dham yatra Package from Haridwar

- **A. Why flagged:** both are Char Dham Yatra tours covering Rishikesh, Haridwar, Barkot, Uttarkashi, Guptkashi, Yamunotri, Gangotri, Kedarnath, Badrinath.
  - Lengths: 08 Nights / 09 Days and 9 Nights / 10 Days.
  - Itinerary similarity 74%; inclusion similarity 100%.
- **B. Overlaps with:** 0054 `/char-dham-yatra-from-delhi-11n-12d` ↔ 0056 `/char-dham-yatra-from-haridwar-9n-10d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 08 Nights / 09 Days vs 9 Nights / 10 Days.
  - Data note, 0054: URL says 12 days but the page says 08 Nights / 09 Days.
  - Data note, 0054: itinerary lists 10 days but the page says 9 days.
  - Data note, 0054: URL says "from Delhi" but the page content is "Char Dham yatra Package from Haridwar for 9 Days".
- **E. Suggested:** **KEEP (correct page data)**. Different trip length; each can carry its own Package ID

### 2.14 0055 Char Dham yatra Package from Haridwar for 9 Days ↔ 0056 Char Dham yatra Package from Haridwar

- **A. Why flagged:** both are Char Dham Yatra tours covering Rishikesh, Haridwar, Barkot, Uttarkashi, Guptkashi, Yamunotri, Gangotri, Kedarnath, Badrinath.
  - Lengths: 08 Nights / 09 Days and 9 Nights / 10 Days.
  - Itinerary similarity 74%; inclusion similarity 100%.
- **B. Overlaps with:** 0055 `/chardham-yatra-from-haridwar-8n-9d` ↔ 0056 `/char-dham-yatra-from-haridwar-9n-10d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 08 Nights / 09 Days vs 9 Nights / 10 Days.
  - Data note, 0055: itinerary lists 10 days but the page says 9 days.
- **E. Suggested:** **KEEP (correct page data)**. Different trip length; each can carry its own Package ID

### 2.15 0060 Amarnath Ji Tour Package by Helicopter for 2 Nights 3 Days ↔ 0061 Amarnath Ji Yatra by Helicopter – 3 Nights 4 Days with Srinagar Tour

- **A. Why flagged:** both are Amarnath Yatra tours covering Srinagar, Sonmarg.
  - Lengths: 2 Nights / 3 Days and 3 Nights / 4 Days.
  - Itinerary similarity 79%; inclusion similarity 94%.
- **B. Overlaps with:** 0060 `/amarnath-ji-yatra-by-helicopter-2n-3d` ↔ 0061 `/amarnath-ji-yatra-by-helicopter-3n-4d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 2 Nights / 3 Days vs 3 Nights / 4 Days.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.16 0064 Darjeeling Gangtok Tour ↔ 0066 Darjeeling and Gangtok Tour

- **A. Why flagged:** both are Darjeeling & Sikkim tours covering Gangtok, Darjeeling.
  - Lengths: 3 Nights / 4 Days and 4 Nights / 5 Days.
  - Itinerary similarity 43%; inclusion similarity 0%.
- **B. Overlaps with:** 0064 `/darjeeling-gangtok-04-days` ↔ 0066 `/darjeeling-and-gangtok-06-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 3 Nights / 4 Days vs 4 Nights / 5 Days; different day-by-day plans; different inclusions.
  - Data note, 0066: URL says 6 days but the page says 4 Nights / 5 Days.
  - Data note, 0066: itinerary lists 6 days but the page says 5 days.
- **E. Suggested:** **KEEP (correct page data)**. Different trip length; each can carry its own Package ID

### 2.17 0064 Darjeeling Gangtok Tour ↔ 0067 Darjeeling Gangtok Tour

- **A. Why flagged:** both are Darjeeling & Sikkim tours covering Gangtok, Darjeeling.
  - Lengths: 3 Nights / 4 Days and 4 Nights / 5 Days.
  - Itinerary similarity 49%; inclusion similarity 0%.
- **B. Overlaps with:** 0064 `/darjeeling-gangtok-04-days` ↔ 0067 `/darjeeling-gangtok-05-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 3 Nights / 4 Days vs 4 Nights / 5 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.18 0066 Darjeeling and Gangtok Tour ↔ 0067 Darjeeling Gangtok Tour

- **A. Why flagged:** both are Darjeeling & Sikkim tours covering Gangtok, Darjeeling.
  - Lengths: 4 Nights / 5 Days and 4 Nights / 5 Days.
  - Itinerary similarity 72%; inclusion similarity 100%.
- **B. Overlaps with:** 0066 `/darjeeling-and-gangtok-06-days` ↔ 0067 `/darjeeling-gangtok-05-days`
- **C. Appear identical?** No.
- **D. Material differences:** none found in the page data.
  - Data note, 0066: URL says 6 days but the page says 4 Nights / 5 Days.
  - Data note, 0066: itinerary lists 6 days but the page says 5 days.
- **E. Suggested:** **REVIEW**. Same length and inclusions, with very similar day plans; any difference (e.g. hotel class) is not stated on the pages.

### 2.19 0086 Mysore Coorg tour ↔ 0089 Banglore Mysore Coorg Tour

- **A. Why flagged:** both are Ooty, Mysore & Coorg tours covering Mysore, Coorg, Bangalore.
  - Lengths: 3 Nights / 4 Days and 4 Nights / 5 Days.
  - Itinerary similarity 66%; inclusion similarity 0%.
- **B. Overlaps with:** 0086 `/mysore-coorg-04-days` ↔ 0089 `/banglore-mysore-coorg--tour-05-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 3 Nights / 4 Days vs 4 Nights / 5 Days; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.20 0088 Bangalore Mysore Ooty tour ↔ 0091 Ooty Mysore tour

- **A. Why flagged:** both are Ooty, Mysore & Coorg tours covering Ooty, Mysore, Bangalore.
  - Lengths: 4 Nights / 5 Days and 5 Nights / 6 Days.
  - Itinerary similarity 65%; inclusion similarity 100%.
- **B. Overlaps with:** 0088 `/bangalore-mysore-ooty-tour-05-days` ↔ 0091 `/ooty-mysore-04-days`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 4 Nights / 5 Days vs 5 Nights / 6 Days.
  - Data note, 0091: URL says 4 days but the page says 5 Nights / 6 Days.
  - Data note, 0091: itinerary lists 4 days but the page says 6 days.
- **E. Suggested:** **KEEP (correct page data)**. Different trip length; each can carry its own Package ID

### 2.21 0092 Delightful Goa Tour ↔ 0093 Enticing Tour to Goa

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 2%; inclusion similarity 6%.
- **B. Overlaps with:** 0092 `/delightful-goa-tour-3n-4d` ↔ 0093 `/enticing-tour-to-goa-3n-4d`
- **C. Appear identical?** No.
- **D. Material differences:** meals Breakfast vs Breakfast & dinner; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but meals Breakfast vs Breakfast & dinner, different day-by-day plans, different inclusions.

### 2.22 0092 Delightful Goa Tour ↔ 0094 Exclusive Goa Tour

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 3%; inclusion similarity 99%.
- **B. Overlaps with:** 0092 `/delightful-goa-tour-3n-4d` ↔ 0094 `/exclusive-goa-tour-3n-4d`
- **C. Appear identical?** No.
- **D. Material differences:** different day-by-day plans.
- **E. Suggested:** **KEEP**. Same length but different day-by-day plans.

### 2.23 0092 Delightful Goa Tour ↔ 0095 Fascinating Tour to Goa

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 3%; inclusion similarity 75%.
- **B. Overlaps with:** 0092 `/delightful-goa-tour-3n-4d` ↔ 0095 `/fascinating-tour-to-goa-3n-4d`
- **C. Appear identical?** No.
- **D. Material differences:** meals Breakfast vs Breakfast & dinner; different day-by-day plans.
- **E. Suggested:** **KEEP**. Same length but meals Breakfast vs Breakfast & dinner, different day-by-day plans.

### 2.24 0092 Delightful Goa Tour ↔ 0096 Sun Kissed Goa Escape Tour Package

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 2%; inclusion similarity 21%.
- **B. Overlaps with:** 0092 `/delightful-goa-tour-3n-4d` ↔ 0096 `/sun-kissed-goa-escape`
- **C. Appear identical?** No.
- **D. Material differences:** different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but different day-by-day plans, different inclusions.

### 2.25 0093 Enticing Tour to Goa ↔ 0094 Exclusive Goa Tour

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 6%; inclusion similarity 4%.
- **B. Overlaps with:** 0093 `/enticing-tour-to-goa-3n-4d` ↔ 0094 `/exclusive-goa-tour-3n-4d`
- **C. Appear identical?** No.
- **D. Material differences:** meals Breakfast & dinner vs Breakfast; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but meals Breakfast & dinner vs Breakfast, different day-by-day plans, different inclusions.

### 2.26 0093 Enticing Tour to Goa ↔ 0095 Fascinating Tour to Goa

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 8%; inclusion similarity 1%.
- **B. Overlaps with:** 0093 `/enticing-tour-to-goa-3n-4d` ↔ 0095 `/fascinating-tour-to-goa-3n-4d`
- **C. Appear identical?** No.
- **D. Material differences:** different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but different day-by-day plans, different inclusions.

### 2.27 0093 Enticing Tour to Goa ↔ 0096 Sun Kissed Goa Escape Tour Package

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 4%; inclusion similarity 10%.
- **B. Overlaps with:** 0093 `/enticing-tour-to-goa-3n-4d` ↔ 0096 `/sun-kissed-goa-escape`
- **C. Appear identical?** No.
- **D. Material differences:** meals Breakfast & dinner vs Breakfast; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but meals Breakfast & dinner vs Breakfast, different day-by-day plans, different inclusions.

### 2.28 0094 Exclusive Goa Tour ↔ 0095 Fascinating Tour to Goa

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 63%; inclusion similarity 76%.
- **B. Overlaps with:** 0094 `/exclusive-goa-tour-3n-4d` ↔ 0095 `/fascinating-tour-to-goa-3n-4d`
- **C. Appear identical?** No.
- **D. Material differences:** meals Breakfast vs Breakfast & dinner.
- **E. Suggested:** **KEEP**. Same length but meals Breakfast vs Breakfast & dinner.

### 2.29 0094 Exclusive Goa Tour ↔ 0096 Sun Kissed Goa Escape Tour Package

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 3%; inclusion similarity 22%.
- **B. Overlaps with:** 0094 `/exclusive-goa-tour-3n-4d` ↔ 0096 `/sun-kissed-goa-escape`
- **C. Appear identical?** No.
- **D. Material differences:** different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but different day-by-day plans, different inclusions.

### 2.30 0095 Fascinating Tour to Goa ↔ 0096 Sun Kissed Goa Escape Tour Package

- **A. Why flagged:** both are Goa tours covering Goa.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 3%; inclusion similarity 19%.
- **B. Overlaps with:** 0095 `/fascinating-tour-to-goa-3n-4d` ↔ 0096 `/sun-kissed-goa-escape`
- **C. Appear identical?** No.
- **D. Material differences:** meals Breakfast & dinner vs Breakfast; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but meals Breakfast & dinner vs Breakfast, different day-by-day plans, different inclusions.

### 2.31 0097 Best of Dubai tour ↔ 0098 Dubai Family Trip with FREE Burj Khalifa Tickets

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 2 Nights / 3 Days and 2 Nights / 3 Days.
  - Itinerary similarity 3%; inclusion similarity 10%.
- **B. Overlaps with:** 0097 `/best-of-dubai-tour` ↔ 0098 `/dubai-family-trip-with-free-burj-khalifa-tickets`
- **C. Appear identical?** No.
- **D. Material differences:** different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but different day-by-day plans, different inclusions.

### 2.32 0097 Best of Dubai tour ↔ 0099 Best Dubai Tour

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 2 Nights / 3 Days and 3 Nights / 4 Days.
  - Itinerary similarity 76%; inclusion similarity 92%.
- **B. Overlaps with:** 0097 `/best-of-dubai-tour` ↔ 0099 `/best-dubai-tour`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 2 Nights / 3 Days vs 3 Nights / 4 Days.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.33 0097 Best of Dubai tour ↔ 0100 Dubai Travel Packages

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 2 Nights / 3 Days and 3 Nights / 4 Days.
  - Itinerary similarity 5%; inclusion similarity 19%.
- **B. Overlaps with:** 0097 `/best-of-dubai-tour` ↔ 0100 `/dubai-travel-packages`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 2 Nights / 3 Days vs 3 Nights / 4 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.34 0098 Dubai Family Trip with FREE Burj Khalifa Tickets ↔ 0099 Best Dubai Tour

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 2 Nights / 3 Days and 3 Nights / 4 Days.
  - Itinerary similarity 2%; inclusion similarity 7%.
- **B. Overlaps with:** 0098 `/dubai-family-trip-with-free-burj-khalifa-tickets` ↔ 0099 `/best-dubai-tour`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 2 Nights / 3 Days vs 3 Nights / 4 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.35 0098 Dubai Family Trip with FREE Burj Khalifa Tickets ↔ 0100 Dubai Travel Packages

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 2 Nights / 3 Days and 3 Nights / 4 Days.
  - Itinerary similarity 2%; inclusion similarity 41%.
- **B. Overlaps with:** 0098 `/dubai-family-trip-with-free-burj-khalifa-tickets` ↔ 0100 `/dubai-travel-packages`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 2 Nights / 3 Days vs 3 Nights / 4 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.36 0099 Best Dubai Tour ↔ 0100 Dubai Travel Packages

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 3 Nights / 4 Days and 3 Nights / 4 Days.
  - Itinerary similarity 4%; inclusion similarity 17%.
- **B. Overlaps with:** 0099 `/best-dubai-tour` ↔ 0100 `/dubai-travel-packages`
- **C. Appear identical?** No.
- **D. Material differences:** different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Same length but different day-by-day plans, different inclusions.

### 2.37 0099 Best Dubai Tour ↔ 0101 Deluxe Tour to Dubai

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 3 Nights / 4 Days and 4 Nights / 5 Days.
  - Itinerary similarity 3%; inclusion similarity 25%.
- **B. Overlaps with:** 0099 `/best-dubai-tour` ↔ 0101 `/deluxe-tour-to-dubai-4n5d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 3 Nights / 4 Days vs 4 Nights / 5 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.38 0099 Best Dubai Tour ↔ 0102 Standard Tour to Dubai

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 3 Nights / 4 Days and 4 Nights / 5 Days.
  - Itinerary similarity 3%; inclusion similarity 25%.
- **B. Overlaps with:** 0099 `/best-dubai-tour` ↔ 0102 `/standard-tour-to-dubai-4n5d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 3 Nights / 4 Days vs 4 Nights / 5 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.39 0100 Dubai Travel Packages ↔ 0101 Deluxe Tour to Dubai

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 3 Nights / 4 Days and 4 Nights / 5 Days.
  - Itinerary similarity 3%; inclusion similarity 5%.
- **B. Overlaps with:** 0100 `/dubai-travel-packages` ↔ 0101 `/deluxe-tour-to-dubai-4n5d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 3 Nights / 4 Days vs 4 Nights / 5 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.40 0100 Dubai Travel Packages ↔ 0102 Standard Tour to Dubai

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 3 Nights / 4 Days and 4 Nights / 5 Days.
  - Itinerary similarity 3%; inclusion similarity 5%.
- **B. Overlaps with:** 0100 `/dubai-travel-packages` ↔ 0102 `/standard-tour-to-dubai-4n5d`
- **C. Appear identical?** No.
- **D. Material differences:** trip length 3 Nights / 4 Days vs 4 Nights / 5 Days; different day-by-day plans; different inclusions.
- **E. Suggested:** **KEEP**. Different trip length; each can carry its own Package ID

### 2.41 0101 Deluxe Tour to Dubai ↔ 0102 Standard Tour to Dubai

- **A. Why flagged:** both are Dubai tours covering Dubai.
  - Lengths: 4 Nights / 5 Days and 4 Nights / 5 Days.
  - Itinerary similarity 99%; inclusion similarity 100%.
- **B. Overlaps with:** 0101 `/deluxe-tour-to-dubai-4n5d` ↔ 0102 `/standard-tour-to-dubai-4n5d`
- **C. Appear identical?** **Yes.** Same day plans, inclusions and length.
- **D. Material differences:** none found in the page data.
- **E. Suggested:** **REVIEW**. Day plans, inclusions and length are the same, but the names suggest a difference (e.g. hotel class) that the pages do not state. Either add the real differences to the pages (then KEEP both) or MERGE them.

## 2b. Other records to check (not flagged as overlaps)

**Page-data problems.**
- The Package ID does not depend on these fields, so numbering can go ahead.
- Correcting them later does not change the number.
- Where a problem changes *which trip the page is*, it is marked REVIEW.

| Proposed No. | Package | Problem | Suggested |
|---|---|---|---|
| 0004 | Srinagar Pahalgam Gulmarg Sonmarg Tour (`/srinagar-pahalgam-gulmarg-sonmarg-package-6-days`) | URL says 6 days but the page says 4 Nights / 5 Days; itinerary lists 6 days but the page says 5 days | KEEP (correct page data) |
| 0005 | Srinagar Gulmarg Pahalgam Tour (`/srinagar-gulmarg-pahalgam-tour-package-7-days`) | no day-by-day itinerary on the page | KEEP (correct page data) |
| 0010 | Panorama Ladakh tour (`/panorama-ladakh-tour-6n7d`) | URL says 7 days but the page says 5 Nights / 6 Days; itinerary lists 7 days but the page says 6 days | KEEP (correct page data) |
| 0012 | Discover Leh Ladakh Tour (`/discover-leh-ladakh-tour-8n-9d`) | itinerary lists 8 days but the page says 9 days | KEEP (correct page data) |
| 0013 | Best of Shimla Vacation (`/best-of-shimla-vacation`) | no day-by-day itinerary on the page | REVIEW |
| 0037 | Nainital with Almora and jim corbett (`/nainital-with-almora-and-jim-corbett-05-days`) | itinerary lists 3 days but the page says 5 days | KEEP (correct page data) |
| 0054 | Char Dham yatra Package from Haridwar for 9 Days (`/char-dham-yatra-from-delhi-11n-12d`) | URL says 12 days but the page says 08 Nights / 09 Days; itinerary lists 10 days but the page says 9 days; URL says "from Delhi" but the page content is "Char Dham yatra Package from Haridwar for 9 Days" | REVIEW (URL/content mismatch) |
| 0055 | Char Dham yatra Package from Haridwar for 9 Days (`/chardham-yatra-from-haridwar-8n-9d`) | itinerary lists 10 days but the page says 9 days | REVIEW (see 0054) |
| 0057 | Yamunotri Gangotri Do Dham Yatra from Delhi (`/yamunotri-gangotri-do-dham-from-delhi-6n-7d`) | URL says 7 days but the page says 9 Nights / 10 Days; itinerary lists 7 days but the page says 10 days | KEEP (correct page data) |
| 0066 | Darjeeling and Gangtok Tour (`/darjeeling-and-gangtok-06-days`) | URL says 6 days but the page says 4 Nights / 5 Days; itinerary lists 6 days but the page says 5 days | REVIEW |
| 0071 | Gangtok with lachen and Lachung Tour (`/gangtok-with-lachen-and-lachung-07-days`) | URL says 7 days but the page says 5 Nights / 6 Days; itinerary lists 7 days but the page says 6 days | KEEP (correct page data) |
| 0077 | Kovalam Kanyakumari Tour (`/kovalam-kanyakumari-tour-4-days`) | itinerary lists 3 days but the page says 4 days | KEEP (correct page data) |
| 0079 | Munnar Thekkady Tour Package (`/munnar-thekkady-tour-package-4-days`) | itinerary lists 3 days but the page says 4 days | KEEP (correct page data) |
| 0080 | Munnar Alleppey Kovalam (`/munnar-alleppey-kovalam-05-days`) | itinerary lists 3 days but the page says 5 days | KEEP (correct page data) |
| 0082 | Munnar Alleppey Kovalam (`/cochin-munnar-thekkedy-alleppey-06-days`) | itinerary lists 5 days but the page says 6 days | KEEP (correct page data) |
| 0083 | Munnar Alleppey Tour Package (`/munnar-alleppey-kovalam-06-days`) | no day-by-day itinerary on the page | KEEP (correct page data) |
| 0091 | Ooty Mysore tour (`/ooty-mysore-04-days`) | URL says 4 days but the page says 5 Nights / 6 Days; itinerary lists 4 days but the page says 6 days | KEEP (correct page data) |

**Same or near-same titles.** These are different trips (different lengths or places) with names that are hard to tell apart. Consider adding the length or route to the name. Renaming never changes the Package ID

| Titles | Tours |
|---|---|
| Nainital with Kausani and Jim Corbett | 0038 (4 Nights / 5 Days); 0042 (5 Nights / 6 Days) |
| Nainital with Ranikhet Jim Corbett | 0039 (4 Nights / 5 Days); 0043 (5 Nights / 6 Days) |
| Char Dham yatra Package from Haridwar for 9 Days | 0054 (08 Nights / 09 Days); 0055 (08 Nights / 09 Days) |
| Darjeeling Gangtok Tour | 0064 (3 Nights / 4 Days); 0066 (4 Nights / 5 Days); 0067 (4 Nights / 5 Days) |
| Munnar Alleppey Tour Package | 0078 (3 Nights / 4 Days); 0083 (5 Nights / 6 Days); 0084 (6 Nights / 7 Days) |
| Munnar Alleppey Kovalam | 0080 (4 Nights / 5 Days); 0082 (5 Nights / 6 Days) |

## 3. Owner decision table

**Rows listed:** every flagged or ambiguous row.
**Owner Decision:** left blank. Write APPROVED, MERGED (into …), RETIRED or PENDING.

**Every other row** is suggested **KEEP**. If you approve without changes, it gets its proposed number.

| Proposed No. | Package | Possible Overlap | Suggested Action | Owner Decision |
|---|---|---|---|---|
| 0004 | Srinagar Pahalgam Gulmarg Sonmarg Tour (4 Nights / 5 Days) | — | KEEP (correct page data) | |
| 0005 | Srinagar Gulmarg Pahalgam Tour (6 Nights / 7 Days) | — | KEEP (correct page data) | |
| 0009 | Jewels of Leh Ladakh Tour (5 Nights / 6 Days) | 0010 | KEEP (correct page data) | |
| 0010 | Panorama Ladakh tour (5 Nights / 6 Days) | 0009 | KEEP (correct page data) | |
| 0011 | Serene Leh Ladakh Tour (7 Nights / 8 Days) | 0012 | KEEP (correct page data) | |
| 0012 | Discover Leh Ladakh Tour (8 Nights / 9 Days) | 0011 | KEEP (correct page data) | |
| 0013 | Best of Shimla Vacation (2 Nights / 3 Days) | 0014 | REVIEW | |
| 0014 | Best of Shimla (3 Nights / 4 Days) | 0013 | REVIEW | |
| 0015 | Amritsar with Dalhousie Dharamshala (4 Nights / 5 Days) | 0019 | KEEP | |
| 0017 | Manali Volvo Trip weekend (4 Nights / 5 Days) | 0020 | KEEP | |
| 0019 | Amrirsar with Dalhousie and Dharamshala (5 Nights / 6 Days) | 0015 | KEEP | |
| 0020 | Best of Manali with Delhi By Volvo (5 Nights / 6 Days) | 0017 | KEEP | |
| 0022 | Shimla Manali Tour (5 Nights / 6 Days) | 0023 | KEEP | |
| 0023 | Shimla Manali with Chandigarh (5 Nights / 6 Days) | 0022 | KEEP | |
| 0036 | Haridwar with Mussoorie and Jim Corbett (4 Nights / 5 Days) | 0040 | KEEP | |
| 0037 | Nainital with Almora and jim corbett (4 Nights / 5 Days) | — | KEEP (correct page data) | |
| 0038 | Nainital with Kausani and Jim Corbett (4 Nights / 5 Days) | 0042 | KEEP | |
| 0039 | Nainital with Ranikhet Jim Corbett (4 Nights / 5 Days) | 0043 | KEEP | |
| 0040 | Haridwar with Mussoorie and Corbett (5 Nights / 6 Days) | 0036 | KEEP | |
| 0041 | Mussoorie with Nainital and Jim Corbett (5 Nights / 6 Days) | 0044 | KEEP | |
| 0042 | Nainital with Kausani and Jim Corbett (5 Nights / 6 Days) | 0038 | KEEP | |
| 0043 | Nainital with Ranikhet and jim corbett (5 Nights / 6 Days) | 0039 | KEEP | |
| 0044 | Corbett With Nainital and mussoorie (6 Nights / 7 Days) | 0041 | KEEP | |
| 0051 | Do Dham Yatra (Kedarnath - Badrinath) Tour from Haridwar 5 Days Package (04 Nights / 05 Days) | 0052 | KEEP | |
| 0052 | 6-Day Do Dham Yatra (Kedarnath & Badrinath) Tour from Haridwar (5 Nights / 6 Days) | 0051 | KEEP | |
| 0054 | Char Dham yatra Package from Haridwar for 9 Days (08 Nights / 09 Days) | 0055, 0056 | REVIEW (URL/content mismatch) | |
| 0055 | Char Dham yatra Package from Haridwar for 9 Days (08 Nights / 09 Days) | 0054, 0056 | REVIEW (see 0054) | |
| 0056 | Char Dham yatra Package from Haridwar (9 Nights / 10 Days) | 0054, 0055 | KEEP (correct page data) | |
| 0057 | Yamunotri Gangotri Do Dham Yatra from Delhi (9 Nights / 10 Days) | — | KEEP (correct page data) | |
| 0060 | Amarnath Ji Tour Package by Helicopter for 2 Nights 3 Days (2 Nights / 3 Days) | 0061 | KEEP | |
| 0061 | Amarnath Ji Yatra by Helicopter – 3 Nights 4 Days with Srinagar Tour (3 Nights / 4 Days) | 0060 | KEEP | |
| 0064 | Darjeeling Gangtok Tour (3 Nights / 4 Days) | 0066, 0067 | KEEP (correct page data) | |
| 0066 | Darjeeling and Gangtok Tour (4 Nights / 5 Days) | 0064, 0067 | REVIEW | |
| 0067 | Darjeeling Gangtok Tour (4 Nights / 5 Days) | 0064, 0066 | REVIEW | |
| 0071 | Gangtok with lachen and Lachung Tour (5 Nights / 6 Days) | — | KEEP (correct page data) | |
| 0077 | Kovalam Kanyakumari Tour (3 Nights / 4 Days) | — | KEEP (correct page data) | |
| 0078 | Munnar Alleppey Tour Package (3 Nights / 4 Days) | — | KEEP (rename to tell apart) | |
| 0079 | Munnar Thekkady Tour Package (3 Nights / 4 Days) | — | KEEP (correct page data) | |
| 0080 | Munnar Alleppey Kovalam (4 Nights / 5 Days) | — | KEEP (correct page data) | |
| 0082 | Munnar Alleppey Kovalam (5 Nights / 6 Days) | — | KEEP (correct page data) | |
| 0083 | Munnar Alleppey Tour Package (5 Nights / 6 Days) | — | KEEP (correct page data) | |
| 0084 | Munnar Alleppey Tour Package (6 Nights / 7 Days) | — | KEEP (rename to tell apart) | |
| 0086 | Mysore Coorg tour (3 Nights / 4 Days) | 0089 | KEEP | |
| 0088 | Bangalore Mysore Ooty tour (4 Nights / 5 Days) | 0091 | KEEP (correct page data) | |
| 0089 | Banglore Mysore Coorg Tour (4 Nights / 5 Days) | 0086 | KEEP | |
| 0091 | Ooty Mysore tour (5 Nights / 6 Days) | 0088 | KEEP (correct page data) | |
| 0092 | Delightful Goa Tour (3 Nights / 4 Days) | 0093, 0094, 0095, 0096 | KEEP | |
| 0093 | Enticing Tour to Goa (3 Nights / 4 Days) | 0092, 0094, 0095, 0096 | KEEP | |
| 0094 | Exclusive Goa Tour (3 Nights / 4 Days) | 0092, 0093, 0095, 0096 | KEEP | |
| 0095 | Fascinating Tour to Goa (3 Nights / 4 Days) | 0092, 0093, 0094, 0096 | KEEP | |
| 0096 | Sun Kissed Goa Escape Tour Package (3 Nights / 4 Days) | 0092, 0093, 0094, 0095 | KEEP | |
| 0097 | Best of Dubai tour (2 Nights / 3 Days) | 0098, 0099, 0100 | KEEP | |
| 0098 | Dubai Family Trip with FREE Burj Khalifa Tickets (2 Nights / 3 Days) | 0097, 0099, 0100 | KEEP | |
| 0099 | Best Dubai Tour (3 Nights / 4 Days) | 0097, 0098, 0100, 0101, 0102 | KEEP | |
| 0100 | Dubai Travel Packages (3 Nights / 4 Days) | 0097, 0098, 0099, 0101, 0102 | KEEP | |
| 0101 | Deluxe Tour to Dubai (4 Nights / 5 Days) | 0099, 0100, 0102 | REVIEW | |
| 0102 | Standard Tour to Dubai (4 Nights / 5 Days) | 0099, 0100, 0101 | REVIEW | |

## 4. Rules for merged and retired tours

Every merge or retirement is recorded with these fields:

| Old package | Old Package ID (if assigned) | Retained package | Final Package ID | Redirect / archive |
|---|---|---|---|---|
| `slug:…` | — (none assigned yet) | `slug:…` | the retained tour's number | 301 from the old URL to the retained tour; old record kept as archived |

- **Historical numbers:** a merge never overwrites or reuses a number.
- **Proposed numbers of merged or retired rows:**
  - Before approval, you choose one of two options.
    - (a) Keep the gap: the number stays reserved and unused.
    - (b) Close the gap by renumbering the *proposal* before anything is approved.
  - After approval, gaps are never closed.
- **Retired numbers:** a number is never given to another tour without your approval.

## 5. After approval: migration plan (not run)

1. **Freeze.** Your decisions are copied into the CSV (APPROVED / MERGED / RETIRED / PENDING) and committed. That commit is the approval record.
2. **Interim registry.** In `package-registry.json`:
   - APPROVED rows change from `proposed` to `approved`;
   - MERGED and RETIRED rows are recorded with their disposition;
   - PENDING rows stay `proposed`, so they get no public number.
   - A test fails if any approved number later changes, disappears or is reused.
3. **Redirects.** 301 redirects for merged and retired URLs are added to `.htaccess`.
4. **Database** (when the CMS is built). A single transaction calls `create_package(…, 'migration')` in approved order.
   - The counter is locked with `SELECT … FOR UPDATE`; it never uses `MAX()+1`.
   - Safeguards:
     - unique and four-digit constraints;
     - an append-only `package_id_registry` as the audit trail;
     - `package_audit` rows for each change.
   - **Rollback:** run on a copy first. If any check fails, the transaction is rolled back and nothing is kept. After a successful run, the pre-migration database backup is kept until you sign off.
5. **Retest** (section 6). Then Package ID goes live with the next approved deployment.

## 6. Tests to run after assignment

| Check | How it is tested |
|---|---|
| All active tours have a unique Package ID; no duplicates | tools/tests/package_registry_test.php (registry integrity); DB unique constraint |
| Retired numbers stay reserved | registry_test.php: retire 0001, next package gets 0003 |
| Name, URL, price and itinerary changes do not change the Package ID | registry_test.php (rename, slug change, new rate version); an extra check will compare the approved snapshot |
| Search by Package ID works | tour.js: "0002" and "Package ID 0002" open the tour |
| Enquiry, WhatsApp and itinerary show the Package ID | tour.js (live mode once approved) |
| CRM and payment payloads carry the Package ID | registry_test.php: enquiry, payment and booking rows |
| Historical records stay traceable | registry_test.php: payment keeps name, rate and number after a rename |
| No public inventory counts | counts.py page scan |

## 7. Gate items still open (need your go-ahead; not built at this stage)

**Offer packages and offer codes (gate items 11 and 16)**
- **Offer packages** (a package sold as an offer) get a Package ID from the **same single sequence** as domestic, international and special packages, so no two packages ever share an ID.
- **Offer codes** (a discount or promotion applied to a package) are a different thing. If you use them, they need their own format so they are never confused with a Package ID. Proposed:
  - an `offers` table with codes `OF-0001` onwards;
  - an `offer_packages` link table, so one package can have several offers;
  - `offer_code` stored on enquiry, quotation, payment and booking records, so history keeps the offer that applied.
- There are no offers yet, so enquiries do not carry an offer code. Not built until you confirm you want promo codes.

**Selected add-ons (gate item 11)**
- No add-ons are defined yet.
- Proposed: optional add-ons attached to a rate version (`package_rate_items`, kind `addon`), with the selected ones sent in the enquiry.

**Where the Package ID shows (owner decision, 30 Sep):** only in the itinerary header on the package page. It is not shown on cards, the title area, breadcrumbs or search suggestions. It is carried in enquiry emails, WhatsApp messages and CRM, quotation and payment records.

**Search by Package ID (gate item 10):** verified. The search redirects (302) to the tour's own canonical URL, and `/tours?…` search URLs are noindex. No duplicate indexable URLs are created.

