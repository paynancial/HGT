-- Holiday Guru Travel — Tour Package CMS (staging schema, SQLite).
-- The MySQL/MariaDB version for production is schema/mysql.sql (same tables and columns).
--
-- Identifiers (owner rule): the Package ID (4 digits, 0001…) is the ONLY package identifier.
-- There is no Tour No. Offer Codes (OF-0001…) are a separate business object with their own sequence.
-- package_pk is the internal row key. It never appears publicly and exists so that a package can be
-- drafted before its Package ID is approved; every package row carries its Package ID once assigned.

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    user_id        INTEGER PRIMARY KEY,
    email          TEXT NOT NULL UNIQUE,
    name           TEXT NOT NULL,
    role           TEXT NOT NULL,              -- see src/permissions.php
    password_hash  TEXT NOT NULL,
    active         INTEGER NOT NULL DEFAULT 1,
    created_at     TEXT NOT NULL,
    last_login_at  TEXT
);

-- Sequences. 'package_id' continues after the owner-approved mapping; 'offer_code' is independent.
CREATE TABLE IF NOT EXISTS sequences (
    name        TEXT PRIMARY KEY,
    last_value  INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS packages (
    package_pk            INTEGER PRIMARY KEY,
    package_id            TEXT UNIQUE CHECK (package_id IS NULL OR (length(package_id) = 4 AND package_id GLOB '[0-9][0-9][0-9][0-9]' AND package_id <> '0000')),
    proposed_package_id   TEXT,                -- from the migration mapping; internal only until approved
    package_id_status     TEXT NOT NULL DEFAULT 'pending' CHECK (package_id_status IN ('pending','proposed','approved')),
    slug                  TEXT NOT NULL UNIQUE,
    name                  TEXT NOT NULL,
    country               TEXT NOT NULL DEFAULT '',
    region                TEXT NOT NULL DEFAULT '',
    destination           TEXT NOT NULL DEFAULT '',   -- destination key (destinations.json)
    city_route            TEXT NOT NULL DEFAULT '',
    package_type          TEXT NOT NULL DEFAULT '',
    speciality_type       TEXT NOT NULL DEFAULT '',
    days                  INTEGER NOT NULL DEFAULT 0 CHECK (days >= 0),
    nights                INTEGER NOT NULL DEFAULT 0 CHECK (nights >= 0),
    suitable_for          TEXT NOT NULL DEFAULT '[]', -- JSON array of controlled tags
    short_description     TEXT NOT NULL DEFAULT '',
    description_html      TEXT NOT NULL DEFAULT '',   -- sanitised rich text
    highlights            TEXT NOT NULL DEFAULT '[]', -- JSON array
    status                TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','in_review','approved','published','paused','archived')),
    duration_override     TEXT NOT NULL DEFAULT '',   -- reason when itinerary length intentionally differs
    payable               INTEGER NOT NULL DEFAULT 0, -- package may be paid online (still needs rate + gateway)
    enquiry_enabled       INTEGER NOT NULL DEFAULT 1,
    noindex               INTEGER NOT NULL DEFAULT 0,
    public_url            TEXT NOT NULL DEFAULT '',
    source                TEXT NOT NULL DEFAULT 'cms' CHECK (source IN ('cms','site-import')),
    version               INTEGER NOT NULL DEFAULT 1,
    created_at            TEXT NOT NULL,
    created_by            INTEGER REFERENCES users(user_id),
    updated_at            TEXT NOT NULL,
    updated_by            INTEGER REFERENCES users(user_id),
    published_at          TEXT,
    published_version     INTEGER,             -- the version that is live; later versions are unpublished changes
    archived_at           TEXT
);
CREATE INDEX IF NOT EXISTS ix_packages_status ON packages(status);
CREATE INDEX IF NOT EXISTS ix_packages_dest ON packages(destination);

CREATE TABLE IF NOT EXISTS itinerary_days (
    day_pk              INTEGER PRIMARY KEY,
    package_pk          INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    day_number          INTEGER NOT NULL,
    title               TEXT NOT NULL DEFAULT '',
    destination         TEXT NOT NULL DEFAULT '',
    route               TEXT NOT NULL DEFAULT '',
    description         TEXT NOT NULL DEFAULT '',
    sightseeing         TEXT NOT NULL DEFAULT '',
    activities          TEXT NOT NULL DEFAULT '',
    meals               TEXT NOT NULL DEFAULT '',
    hotel               TEXT NOT NULL DEFAULT '',
    transport           TEXT NOT NULL DEFAULT '',
    optional_activities TEXT NOT NULL DEFAULT '',
    notes               TEXT NOT NULL DEFAULT '',
    media_id            INTEGER REFERENCES media(media_id),
    UNIQUE (package_pk, day_number)
);

CREATE TABLE IF NOT EXISTS media (
    media_id     INTEGER PRIMARY KEY,
    file_path    TEXT NOT NULL,           -- relative: 'site:assets/img/…' (existing site file) or 'upload:2026/09/…'
    media_type   TEXT NOT NULL DEFAULT 'image',
    mime         TEXT NOT NULL DEFAULT '',
    width        INTEGER NOT NULL DEFAULT 0,
    height       INTEGER NOT NULL DEFAULT 0,
    bytes        INTEGER NOT NULL DEFAULT 0,
    variants     TEXT NOT NULL DEFAULT '{}', -- JSON {"1600": "upload:…", "800": …, "400": …}
    alt_text     TEXT NOT NULL DEFAULT '',
    caption      TEXT NOT NULL DEFAULT '',
    title        TEXT NOT NULL DEFAULT '',
    credit       TEXT NOT NULL DEFAULT '',
    destination  TEXT NOT NULL DEFAULT '',
    status       TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','archived')),
    created_at   TEXT NOT NULL,
    created_by   INTEGER REFERENCES users(user_id),
    UNIQUE (file_path)
);

-- Packages reference central media; the same asset can be used by several packages (no duplicate files).
CREATE TABLE IF NOT EXISTS package_media (
    pm_id        INTEGER PRIMARY KEY,
    package_pk   INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    media_id     INTEGER NOT NULL REFERENCES media(media_id),
    role         TEXT NOT NULL CHECK (role IN ('featured','gallery','itinerary','hotel','activity')),
    day_number   INTEGER,
    sort_order   INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX IF NOT EXISTS ux_pm_featured ON package_media(package_pk) WHERE role = 'featured';

-- Price versions: append-only. A change creates a new version; approved rows are never edited.
CREATE TABLE IF NOT EXISTS rate_versions (
    rate_pk            INTEGER PRIMARY KEY,
    package_pk         INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    version            INTEGER NOT NULL,
    base_price         INTEGER NOT NULL CHECK (base_price > 0),   -- whole rupees
    currency           TEXT NOT NULL DEFAULT 'INR',
    price_unit         TEXT NOT NULL DEFAULT 'per person',
    valid_from         TEXT NOT NULL,
    valid_until        TEXT NOT NULL,
    rate_status        TEXT NOT NULL DEFAULT 'draft' CHECK (rate_status IN ('draft','approved','withdrawn')),
    adult_price        INTEGER, child_price INTEGER, single_supplement INTEGER, extra_bed INTEGER,
    seasonal_note      TEXT NOT NULL DEFAULT '',
    tax_percent        REAL, discount INTEGER,
    price_notes        TEXT NOT NULL DEFAULT '',
    reason             TEXT NOT NULL DEFAULT '',
    created_at         TEXT NOT NULL,
    created_by         INTEGER REFERENCES users(user_id),
    approved_at        TEXT,
    approved_by        INTEGER REFERENCES users(user_id),
    UNIQUE (package_pk, version),
    CHECK (valid_until >= valid_from)
);

CREATE TABLE IF NOT EXISTS scope_items (
    item_pk      INTEGER PRIMARY KEY,
    package_pk   INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    kind         TEXT NOT NULL CHECK (kind IN ('inclusion','exclusion')),
    category     TEXT NOT NULL DEFAULT '',
    name         TEXT NOT NULL,
    description  TEXT NOT NULL DEFAULT '',
    icon         TEXT NOT NULL DEFAULT '',
    sort_order   INTEGER NOT NULL DEFAULT 0,
    status       TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','hidden')),
    is_standard  INTEGER NOT NULL DEFAULT 0   -- the standard airfare / train fare / bus fare exclusions
);

CREATE TABLE IF NOT EXISTS addons (
    addon_pk      INTEGER PRIMARY KEY,
    package_pk    INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    name          TEXT NOT NULL,
    description   TEXT NOT NULL DEFAULT '',
    price         INTEGER CHECK (price IS NULL OR price >= 0),
    currency      TEXT NOT NULL DEFAULT 'INR',
    price_unit    TEXT NOT NULL DEFAULT 'per person' CHECK (price_unit IN ('per person','per couple','per room','per vehicle','per day','flat fee')),
    tax_percent   REAL,
    required      INTEGER NOT NULL DEFAULT 0,
    availability  TEXT NOT NULL DEFAULT 'available' CHECK (availability IN ('available','on request','unavailable')),
    sort_order    INTEGER NOT NULL DEFAULT 0,
    status        TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','hidden'))
);

CREATE TABLE IF NOT EXISTS offers (
    offer_pk               INTEGER PRIMARY KEY,
    offer_code             TEXT NOT NULL UNIQUE CHECK (length(offer_code) = 7 AND offer_code GLOB 'OF-[0-9][0-9][0-9][0-9]' AND offer_code <> 'OF-0000'),
    name                   TEXT NOT NULL,
    offer_type             TEXT NOT NULL DEFAULT 'percentage' CHECK (offer_type IN ('percentage','flat','value-add')),
    discount_value         INTEGER,
    valid_from             TEXT NOT NULL,
    valid_until            TEXT NOT NULL,
    min_value              INTEGER,
    usage_limit            INTEGER,
    eligible_destinations  TEXT NOT NULL DEFAULT '[]',
    eligible_package_types TEXT NOT NULL DEFAULT '[]',
    customer_eligibility   TEXT NOT NULL DEFAULT '',
    terms                  TEXT NOT NULL DEFAULT '',
    status                 TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','paused','expired','archived')),
    created_at             TEXT NOT NULL,
    created_by             INTEGER REFERENCES users(user_id),
    CHECK (valid_until >= valid_from)
);

CREATE TABLE IF NOT EXISTS offer_packages (
    offer_pk    INTEGER NOT NULL REFERENCES offers(offer_pk) ON DELETE CASCADE,
    package_pk  INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    PRIMARY KEY (offer_pk, package_pk)
);

CREATE TABLE IF NOT EXISTS package_seo (
    package_pk           INTEGER PRIMARY KEY REFERENCES packages(package_pk) ON DELETE CASCADE,
    meta_title           TEXT NOT NULL DEFAULT '',
    meta_description     TEXT NOT NULL DEFAULT '',
    canonical            TEXT NOT NULL DEFAULT '',
    og_title             TEXT NOT NULL DEFAULT '',
    og_description       TEXT NOT NULL DEFAULT '',
    og_media_id          INTEGER REFERENCES media(media_id),
    aeo_question         TEXT NOT NULL DEFAULT '',
    aeo_answer           TEXT NOT NULL DEFAULT '',
    key_facts            TEXT NOT NULL DEFAULT '[]',
    supporting_questions TEXT NOT NULL DEFAULT '[]'
);

CREATE TABLE IF NOT EXISTS faqs (
    faq_pk      INTEGER PRIMARY KEY,
    package_pk  INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    question    TEXT NOT NULL,
    answer      TEXT NOT NULL,
    sort_order  INTEGER NOT NULL DEFAULT 0
);

-- Internal merchandising ("Top 500"). Never rendered publicly; separate from offers.
CREATE TABLE IF NOT EXISTS curation (
    package_pk           INTEGER PRIMARY KEY REFERENCES packages(package_pk) ON DELETE CASCADE,
    priority_rank        INTEGER UNIQUE CHECK (priority_rank IS NULL OR priority_rank BETWEEN 1 AND 500),
    featured             INTEGER NOT NULL DEFAULT 0,
    homepage_featured    INTEGER NOT NULL DEFAULT 0,
    search_featured      INTEGER NOT NULL DEFAULT 0,
    seasonal_featured    INTEGER NOT NULL DEFAULT 0,
    speciality_featured  INTEGER NOT NULL DEFAULT 0
);

-- Workflow sign-offs (content / SEO-AEO / pricing review, then approval).
CREATE TABLE IF NOT EXISTS reviews (
    review_pk   INTEGER PRIMARY KEY,
    package_pk  INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    stage       TEXT NOT NULL CHECK (stage IN ('content','seo','pricing')),
    decision    TEXT NOT NULL CHECK (decision IN ('approved','changes')),
    note        TEXT NOT NULL DEFAULT '',
    user_id     INTEGER REFERENCES users(user_id),
    package_version INTEGER NOT NULL,
    at          TEXT NOT NULL
);

-- Package versions: a full snapshot per save. Nothing is overwritten.
CREATE TABLE IF NOT EXISTS package_versions (
    pv_pk        INTEGER PRIMARY KEY,
    package_pk   INTEGER NOT NULL REFERENCES packages(package_pk) ON DELETE CASCADE,
    package_id   TEXT,
    version      INTEGER NOT NULL,
    sections     TEXT NOT NULL DEFAULT '',     -- comma list: basic,itinerary,media,pricing,scope,addons,offers,seo,advanced,status
    note         TEXT NOT NULL DEFAULT '',
    snapshot     TEXT NOT NULL,                -- JSON of the whole package after the change
    changed_by   INTEGER REFERENCES users(user_id),
    changed_at   TEXT NOT NULL,
    UNIQUE (package_pk, version)
);

-- Activity log: insert-only (the app never updates or deletes rows).
CREATE TABLE IF NOT EXISTS activity_log (
    log_pk      INTEGER PRIMARY KEY,
    at          TEXT NOT NULL,
    user_id     INTEGER REFERENCES users(user_id),
    action      TEXT NOT NULL,
    package_pk  INTEGER,
    package_id  TEXT,
    field       TEXT NOT NULL DEFAULT '',
    old_value   TEXT NOT NULL DEFAULT '',
    new_value   TEXT NOT NULL DEFAULT '',
    ip          TEXT NOT NULL DEFAULT ''
);
CREATE TRIGGER IF NOT EXISTS activity_log_no_update BEFORE UPDATE ON activity_log BEGIN SELECT RAISE(ABORT, 'activity_log is insert-only'); END;
CREATE TRIGGER IF NOT EXISTS activity_log_no_delete BEFORE DELETE ON activity_log BEGIN SELECT RAISE(ABORT, 'activity_log is insert-only'); END;

-- CRM: website "Tour Package Enquiry" (Contact Us enquiry type) and enquiries logged by staff.
CREATE TABLE IF NOT EXISTS enquiries (
    enquiry_pk      INTEGER PRIMARY KEY,
    created_at      TEXT NOT NULL,
    enquiry_type    TEXT NOT NULL DEFAULT 'TOUR PACKAGE ENQUIRY',
    source          TEXT NOT NULL DEFAULT 'website',   -- website | phone | whatsapp | email | walk-in
    name            TEXT NOT NULL,
    email           TEXT NOT NULL DEFAULT '',
    phone           TEXT NOT NULL DEFAULT '',
    package_pk      INTEGER REFERENCES packages(package_pk),
    package_id      TEXT,                              -- as carried at enquiry time (NULL = not assigned yet)
    package_name    TEXT NOT NULL DEFAULT '',
    internal_ref    TEXT NOT NULL DEFAULT '',
    destination     TEXT NOT NULL DEFAULT '',
    travel_date     TEXT NOT NULL DEFAULT '',
    adults          INTEGER, children INTEGER,
    departure_city  TEXT NOT NULL DEFAULT '',
    displayed_rate  TEXT NOT NULL DEFAULT '',
    rate_version    INTEGER,
    rate_validity   TEXT NOT NULL DEFAULT '',
    addons          TEXT NOT NULL DEFAULT '[]',
    offer_code      TEXT,
    package_url     TEXT NOT NULL DEFAULT '',
    utm             TEXT NOT NULL DEFAULT '{}',
    message         TEXT NOT NULL DEFAULT '',
    stage           TEXT NOT NULL DEFAULT 'new' CHECK (stage IN ('new','contacted','qualified','quoted','won','lost')),
    assigned_to     INTEGER REFERENCES users(user_id),
    is_test         INTEGER NOT NULL DEFAULT 0,
    raw             TEXT NOT NULL DEFAULT '{}'
);

CREATE TABLE IF NOT EXISTS enquiry_notes (
    note_pk     INTEGER PRIMARY KEY,
    enquiry_pk  INTEGER NOT NULL REFERENCES enquiries(enquiry_pk) ON DELETE CASCADE,
    user_id     INTEGER REFERENCES users(user_id),
    at          TEXT NOT NULL,
    note        TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS login_attempts (
    email  TEXT NOT NULL,
    at     TEXT NOT NULL,
    ok     INTEGER NOT NULL
);
