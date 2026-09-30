# API contract (v1, staging)

**Base:** the CMS host, for example `http://127.0.0.1:8099` on staging. The machine-readable version is [openapi.yaml](openapi.yaml).

**Auth**

- Read endpoints need a CMS session cookie: sign in at `/login`.
- The enquiry intake needs the header `X-HG-Intake-Token`, which must equal the `intake_token` in `cms/config.php`. Keep that value secret; it is never committed.

**References**

- A package is addressed by its **Package ID** (`0001`) once approved.
- Before approval, use the internal reference `pk-{n}`.
- Proposed Package IDs are never returned. `package_id` is `null` until approved.

## GET /api/packages?q=&status=&dest=

Search by Package ID, name, destination or status. An Offer Code query returns the offer plus its packages.

```json
{ "offer": null,
  "packages": [ { "package_id": null, "ref": "pk-1", "name": "Srinagar Gulmarg Tour", "destination": "kashmir", "status": "published", "days": 4, "nights": 3 } ] }
```

## GET /api/packages/{package_id | pk-n}

The full package as the website or CRM may use it. Key fields:

- `package_id`, `package_id_status`
- `rate`: `price_version`, `base_price`, `currency`, `price_unit`, `valid_from`, `valid_until`, `label`. It is `null` when there is no current rate, and then `price_label` is "Price on request".
- `standard_statement`
- `inclusions[]`, `exclusions[]` (with a `standard` flag)
- `addons[]`, with `availability`
- `offers[]`: only offers that are live today
- `seo`, `aeo`, `faqs[]`
- `cta`: `{ "enquire_now": true, "pay_now": false }`

Errors: `404 {"error":"not found"}`, and `401` without a session.

## GET /api/packages/{ref}/checklist

```json
{ "publishable": false, "errors": ["Featured image", "…"], "warnings": ["Gallery (at least 3 images)"], "checklist": { "Content": [ … ] } }
```

## GET /api/offers/{OF-nnnn}

Returns:

- `offer_code`, `name`, `type`, `discount_value`
- `valid_from`, `valid_until`, `status`, `terms`
- `eligible_destinations`, `eligible_package_types`
- `packages[]`: `{ package_id | null, ref, name }`

## POST /api/enquiries (website → CRM intake)

Request (JSON):

```json
{ "name": "…", "phone": "…", "email": "…",
  "package_slug": "srinagar-gulmarg-tour-3n-4d", "offer_code": "OF-0001", "addon_ids": [3],
  "travel_date": "2026-11-12", "adults": 2, "children": 0, "departure_city": "Delhi", "message": "…",
  "utm_source": "google", "utm_medium": "cpc", "utm_campaign": "…" }
```

**Rules**

- `name` is required, plus a `phone` or an `email`.
- The Package ID, displayed rate, price version, rate validity and destination are **derived by the CMS** from `package_slug`. Any such values in the request are ignored.
- `offer_code` is kept only if it is a published offer valid today for that package.
- Add-ons are kept only if they are active and not unavailable.

**Responses**

| Status | Body |
|---|---|
| 201 | `{ "enquiry_id": 12, "package_id": null, "price_version": 2, "offer_code": "OF-0001" }` |
| 401 | Wrong token |
| 422 | Validation error |
| 503 | Intake disabled (no token configured) |
