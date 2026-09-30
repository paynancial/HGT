# CRM mapping

```
Package ID ─► Enquiry ─► Lead ─► Quotation ─► Payment ─► Booking
   0001         #123     (stage)   Q-…          PAY-…      BK-…
```

Every downstream record keeps what the customer was shown at that moment:

| Key | Enquiry | Quotation (planned) | Payment (planned) | Booking (planned) |
|---|---|---|---|---|
| package_id | ✓ (NULL = "Not assigned yet") | ✓ | ✓ | ✓ |
| price_version (`rate_version`) | ✓ | ✓ must match the rate used | ✓ must be the *active* version at charge time | ✓ |
| offer_code | ✓ (only if valid for the package that day) | ✓ | ✓ | ✓ |
| selected add-ons | ✓ | ✓ line items | ✓ | ✓ |
| internal_ref (`slug:…`) | ✓ | ✓ | | |

There is no Tour No. field anywhere in this chain.

## Enquire Now (Contact Us → "Tour Package Enquiry")

**Website today:** `public_html/mail.php` emails the enquiry. The server fills in these fields from the package (`hg_package_enquiry_context()`):

- Package ID
- Internal ref
- Offer code
- Displayed rate
- Rate version
- Rate validity

Values sent by the browser for these fields are ignored.

**CMS:** the same context is built by `enquiry_package_context()` for enquiries logged by staff, and by the intake API.

- **Carried fields:**
  - package: Package ID, name, destination, package URL;
  - rate: displayed rate, price version, rate validity;
  - selection: selected add-ons, Offer Code;
  - traveller: travel date, travellers, departure city;
  - source: UTM tags.
- **Intake (planned website connection):** `POST /api/enquiries` with the shared intake token.
  - The website sends the package slug and optional Offer Code and add-on IDs.
  - The CMS derives everything else.
  - A spoofed `package_id` or `displayed_rate` is ignored. VERIFIED.
- **Stale rate:** the enquiry detail warns when the package's current price version differs from the one the customer saw.

## Pay Now

- Pay Now must use the active price version and detect a stale amount before charging.
- No gateway is connected, so Pay Now stays disabled everywhere and payment success is never simulated.
- The payments module is planned. Its table must store `package_id`, `price_version`, `offer_code`, `amount`, `gateway_ref` and `status`.

## Search keys

CRM search (`/enquiries`) accepts:

- a Package ID (`0001`);
- an Offer Code (`OF-0001`);
- a name, phone, email or package name.
