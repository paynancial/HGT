# Data dictionary

The source of truth is `cms/schema/sqlite.sql` (staging) and `cms/schema/mysql.sql` (proposed for production). Both files have the same tables and columns.

"Public" means the field may appear on the website or leave the CMS in an enquiry, quotation or payment.

## packages

| Field | Type | Public | Rule |
|---|---|---|---|
| package_pk | int | No | Internal row key. Never shown publicly. |
| package_id | char(4) | Yes, once approved | The only package identifier. 0001–9999, unique, never reused. NULL until approved. |
| proposed_package_id | char(4) | No | Number from the migration mapping. Internal until the owner approves it. |
| package_id_status | pending / proposed / approved | No | |
| slug | text | Yes (URL) | Unique. Locked after first publish. |
| name | text | Yes | Changing it never changes the Package ID. |
| country, region, destination | text | Yes | `destination` is a key from `public_html/include/data/destinations.json`. Country and region are derived from it. |
| city_route | text | Yes | Cities in travel order. |
| package_type | enum list | Yes | Leisure, Family, Honeymoon, Adventure, Luxury, Pilgrimage, Group, Weekend, Senior Citizen, Women Special, Inbound, Custom |
| speciality_type | enum list | Yes | |
| days, nights | int | Yes | Checked against the itinerary length. |
| suitable_for | JSON array | Yes | Families, Couples, Groups, Solo Travellers, Senior Citizens, Children, Honeymoon, Corporate |
| short_description | ≤ 300 characters | Yes | 120–300 recommended. |
| description_html | sanitised HTML | Yes | 80+ words to publish. |
| highlights | JSON array | Yes | 3+ to publish. |
| status | draft / in_review / approved / published / paused / archived | No | |
| duration_override | text | No | Reason recorded when the itinerary length intentionally differs from the duration. |
| payable, enquiry_enabled, noindex | 0/1 | Behaviour | |
| version / published_version | int | No | Current content version / the version that is live. |
| source | cms / site-import | No | |
| created/updated/published/archived _at, _by | | No | |

## itinerary_days

`package_pk`, `day_number` (unique per package), `title`, `destination`, `route`, `description`, `sightseeing`, `activities`, `meals`, `hotel`, `transport`, `optional_activities`, `notes`, `media_id`.

All fields are public except the keys.

## media, package_media

- **media:** one row per physical file.
  - `file_path` is either `site:assets/img/…` (an existing website file, used in place) or `upload:YYYY/MM/…` (uploaded).
  - Other fields: `mime`, `width`, `height`, `bytes`, `variants` (WebP at 1600/800/400 px), `alt_text` (required), `caption`, `title`, `credit`, `destination`, `status`.
- **package_media:** links a package to media.
  - Fields: `role` (featured / gallery / itinerary / hotel / activity), `day_number`, `sort_order`.
  - At most one featured image per package, enforced by the database.

## rate_versions (append-only)

| Field | Rule |
|---|---|
| package_pk, version | Unique together. Numbered 1, 2, 3… per package. |
| base_price | Whole rupees, greater than 0. |
| currency | INR |
| price_unit | per person / per couple / per room / per vehicle / per day / flat fee |
| valid_from, valid_until | `valid_until` ≥ `valid_from` |
| rate_status | draft / approved / withdrawn |
| adult_price, child_price, single_supplement, extra_bed, seasonal_note, tax_percent, discount | Optional |
| price_notes | Shown to customers. |
| reason | Required from version 2 onwards. |
| created_at/by, approved_at/by | |

## scope_items (inclusions and exclusions)

`kind` (inclusion / exclusion), `category`, `name`, `description`, `icon`, `sort_order`, `status` (active / hidden), `is_standard`.

`is_standard = 1` marks the fixed Airfare, Train fare and Bus fare exclusions.

## addons

`addon_pk` (shown as AO-nnnn), `name`, `description`, `price` (NULL = on request), `currency`, `price_unit`, `tax_percent`, `required`, `availability` (available / on request / unavailable), `sort_order`, `status`.

## offers, offer_packages

- **offers:** `offer_code` (OF-0001…, unique, own sequence), `name`, `offer_type` (percentage / flat / value-add), `discount_value`, `valid_from/until`, `min_value`, `usage_limit`, `eligible_destinations`, `eligible_package_types`, `customer_eligibility`, `terms`, `status` (draft / published / paused / expired / archived).
- **offer_packages:** a many-to-many link between offers and packages.

## package_seo, faqs

- **package_seo:** `meta_title`, `meta_description`, `canonical`, `og_title`, `og_description`, `og_media_id`, `aeo_question`, `aeo_answer`, `key_facts`, `supporting_questions`.
- **faqs:** `question`, `answer`, `sort_order`.

## curation (internal "Top 500")

`priority_rank` (1–500, unique), `featured`, `homepage_featured`, `search_featured`, `seasonal_featured`, `speciality_featured`.

Never rendered publicly.

## Workflow and audit

| Table | Fields | Rule |
|---|---|---|
| reviews | `stage` (content / seo / pricing), `decision` (approved / changes), `note`, `user_id`, `package_version`, `at` | A sign-off counts only for the version it was given on. |
| package_versions | `package_pk`, `package_id` (at the time), `version`, `sections`, `note`, `snapshot` (full JSON), `changed_by`, `changed_at` | |
| activity_log | `at`, `user_id`, `action`, `package_pk`, `package_id`, `field`, `old_value`, `new_value`, `ip` | Insert-only. |
| login_attempts | `email`, `at`, `ok` | Rate limit: 5 failures per 15 minutes per email. |

## sequences

| name | last_value |
|---|---|
| package_id | Highest assigned or reserved Package ID. Staging reserves 0107 (the mapping). |
| offer_code | Highest Offer Code. |

## CRM: enquiries, enquiry_notes

- **enquiries** fields:
  - `enquiry_type` (TOUR PACKAGE ENQUIRY), `source` (website / phone / whatsapp / email / walk-in);
  - `name`, `email`, `phone`;
  - `package_pk`, `package_id` (as carried at enquiry time; NULL means "not assigned yet"), `package_name`, `internal_ref` (`slug:…`), `destination`;
  - `travel_date`, `adults`, `children`, `departure_city`;
  - `displayed_rate`, `rate_version`, `rate_validity`, `addons` (JSON), `offer_code`, `package_url`, `utm` (JSON), `message`;
  - `stage` (new / contacted / qualified / quoted / won / lost), `assigned_to`, `is_test`, `raw`.
- **enquiry_notes:** follow-up notes.
