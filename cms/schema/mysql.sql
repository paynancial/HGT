-- Holiday Guru Travel — Tour Package CMS: MySQL 8 / MariaDB 10.6+ schema (PROPOSED for production; not applied anywhere).
-- Same tables and columns as schema/sqlite.sql (the staging database). Apply only after owner approval, on a new
-- database, never over the existing production tables. See docs/cms/MIGRATION-PLAN.md.
--
-- Identifiers: Package ID (CHAR(4), 0001…) is the only package identifier. No Tour No. exists.
-- Offer Codes (OF-0001…) have their own sequence. package_pk is an internal row key, never shown publicly.

SET NAMES utf8mb4;

CREATE TABLE users (
    user_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email          VARCHAR(190) NOT NULL UNIQUE,
    name           VARCHAR(120) NOT NULL,
    role           VARCHAR(40) NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    active         TINYINT(1) NOT NULL DEFAULT 1,
    created_at     DATETIME NOT NULL,
    last_login_at  DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sequences (
    name        VARCHAR(40) PRIMARY KEY,
    last_value  INT UNSIGNED NOT NULL
) ENGINE=InnoDB;
INSERT INTO sequences(name, last_value) VALUES ('package_id', 0), ('offer_code', 0);

CREATE TABLE packages (
    package_pk            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_id            CHAR(4) NULL UNIQUE,
    proposed_package_id   CHAR(4) NULL,
    package_id_status     ENUM('pending','proposed','approved') NOT NULL DEFAULT 'pending',
    slug                  VARCHAR(160) NOT NULL UNIQUE,
    name                  VARCHAR(160) NOT NULL,
    country               VARCHAR(80) NOT NULL DEFAULT '',
    region                VARCHAR(80) NOT NULL DEFAULT '',
    destination           VARCHAR(80) NOT NULL DEFAULT '',
    city_route            VARCHAR(255) NOT NULL DEFAULT '',
    package_type          VARCHAR(40) NOT NULL DEFAULT '',
    speciality_type       VARCHAR(40) NOT NULL DEFAULT '',
    days                  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    nights                SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    suitable_for          JSON NOT NULL,
    short_description     VARCHAR(300) NOT NULL DEFAULT '',
    description_html      MEDIUMTEXT NOT NULL,
    highlights            JSON NOT NULL,
    status                ENUM('draft','in_review','approved','published','paused','archived') NOT NULL DEFAULT 'draft',
    duration_override     VARCHAR(255) NOT NULL DEFAULT '',
    payable               TINYINT(1) NOT NULL DEFAULT 0,
    enquiry_enabled       TINYINT(1) NOT NULL DEFAULT 1,
    noindex               TINYINT(1) NOT NULL DEFAULT 0,
    public_url            VARCHAR(200) NOT NULL DEFAULT '',
    source                ENUM('cms','site-import') NOT NULL DEFAULT 'cms',
    version               INT UNSIGNED NOT NULL DEFAULT 1,
    created_at            DATETIME NOT NULL,
    created_by            INT UNSIGNED NULL,
    updated_at            DATETIME NOT NULL,
    updated_by            INT UNSIGNED NULL,
    published_at          DATETIME NULL,
    published_version     INT UNSIGNED NULL,
    archived_at           DATETIME NULL,
    CONSTRAINT ck_package_id CHECK (package_id IS NULL OR (package_id REGEXP '^[0-9]{4}$' AND package_id <> '0000')),
    KEY ix_status (status), KEY ix_dest (destination),
    CONSTRAINT fk_pkg_cb FOREIGN KEY (created_by) REFERENCES users(user_id),
    CONSTRAINT fk_pkg_ub FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
    media_id     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_path    VARCHAR(255) NOT NULL UNIQUE,
    media_type   VARCHAR(20) NOT NULL DEFAULT 'image',
    mime         VARCHAR(40) NOT NULL DEFAULT '',
    width        INT UNSIGNED NOT NULL DEFAULT 0,
    height       INT UNSIGNED NOT NULL DEFAULT 0,
    bytes        INT UNSIGNED NOT NULL DEFAULT 0,
    variants     JSON NOT NULL,
    alt_text     VARCHAR(160) NOT NULL DEFAULT '',
    caption      VARCHAR(255) NOT NULL DEFAULT '',
    title        VARCHAR(160) NOT NULL DEFAULT '',
    credit       VARCHAR(160) NOT NULL DEFAULT '',
    destination  VARCHAR(80) NOT NULL DEFAULT '',
    status       ENUM('active','archived') NOT NULL DEFAULT 'active',
    created_at   DATETIME NOT NULL,
    created_by   INT UNSIGNED NULL,
    CONSTRAINT fk_media_cb FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE itinerary_days (
    day_pk              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_pk          BIGINT UNSIGNED NOT NULL,
    day_number          SMALLINT UNSIGNED NOT NULL,
    title               VARCHAR(255) NOT NULL DEFAULT '',
    destination         VARCHAR(120) NOT NULL DEFAULT '',
    route               VARCHAR(255) NOT NULL DEFAULT '',
    description         TEXT NOT NULL,
    sightseeing         TEXT NOT NULL, activities TEXT NOT NULL,
    meals               VARCHAR(120) NOT NULL DEFAULT '',
    hotel               VARCHAR(160) NOT NULL DEFAULT '',
    transport           VARCHAR(160) NOT NULL DEFAULT '',
    optional_activities TEXT NOT NULL, notes TEXT NOT NULL,
    media_id            BIGINT UNSIGNED NULL,
    UNIQUE KEY ux_day (package_pk, day_number),
    CONSTRAINT fk_day_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE,
    CONSTRAINT fk_day_media FOREIGN KEY (media_id) REFERENCES media(media_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE package_media (
    pm_id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_pk   BIGINT UNSIGNED NOT NULL,
    media_id     BIGINT UNSIGNED NOT NULL,
    role         ENUM('featured','gallery','itinerary','hotel','activity') NOT NULL,
    day_number   SMALLINT UNSIGNED NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    featured_guard BIGINT UNSIGNED AS (IF(role = 'featured', package_pk, NULL)) STORED,
    UNIQUE KEY ux_one_featured (featured_guard),       -- one featured image per package
    CONSTRAINT fk_pm_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE,
    CONSTRAINT fk_pm_media FOREIGN KEY (media_id) REFERENCES media(media_id)
) ENGINE=InnoDB;

CREATE TABLE rate_versions (
    rate_pk            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_pk         BIGINT UNSIGNED NOT NULL,
    version            INT UNSIGNED NOT NULL,
    base_price         INT UNSIGNED NOT NULL,
    currency           CHAR(3) NOT NULL DEFAULT 'INR',
    price_unit         VARCHAR(20) NOT NULL DEFAULT 'per person',
    valid_from         DATE NOT NULL,
    valid_until        DATE NOT NULL,
    rate_status        ENUM('draft','approved','withdrawn') NOT NULL DEFAULT 'draft',
    adult_price INT UNSIGNED NULL, child_price INT UNSIGNED NULL, single_supplement INT UNSIGNED NULL, extra_bed INT UNSIGNED NULL,
    seasonal_note      VARCHAR(255) NOT NULL DEFAULT '',
    tax_percent        DECIMAL(5,2) NULL, discount INT UNSIGNED NULL,
    price_notes        VARCHAR(255) NOT NULL DEFAULT '',
    reason             VARCHAR(255) NOT NULL DEFAULT '',
    created_at         DATETIME NOT NULL, created_by INT UNSIGNED NULL,
    approved_at        DATETIME NULL, approved_by INT UNSIGNED NULL,
    UNIQUE KEY ux_rate_version (package_pk, version),
    CONSTRAINT ck_rate_dates CHECK (valid_until >= valid_from),
    CONSTRAINT ck_rate_price CHECK (base_price > 0),
    CONSTRAINT fk_rate_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE scope_items (
    item_pk      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_pk   BIGINT UNSIGNED NOT NULL,
    kind         ENUM('inclusion','exclusion') NOT NULL,
    category     VARCHAR(40) NOT NULL DEFAULT '',
    name         VARCHAR(255) NOT NULL,
    description  VARCHAR(500) NOT NULL DEFAULT '',
    icon         VARCHAR(20) NOT NULL DEFAULT '',
    sort_order   INT NOT NULL DEFAULT 0,
    status       ENUM('active','hidden') NOT NULL DEFAULT 'active',
    is_standard  TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_scope_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE addons (
    addon_pk      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_pk    BIGINT UNSIGNED NOT NULL,
    name          VARCHAR(160) NOT NULL,
    description   VARCHAR(500) NOT NULL DEFAULT '',
    price         INT UNSIGNED NULL,
    currency      CHAR(3) NOT NULL DEFAULT 'INR',
    price_unit    ENUM('per person','per couple','per room','per vehicle','per day','flat fee') NOT NULL DEFAULT 'per person',
    tax_percent   DECIMAL(5,2) NULL,
    required      TINYINT(1) NOT NULL DEFAULT 0,
    availability  ENUM('available','on request','unavailable') NOT NULL DEFAULT 'available',
    sort_order    INT NOT NULL DEFAULT 0,
    status        ENUM('active','hidden') NOT NULL DEFAULT 'active',
    CONSTRAINT fk_addon_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE offers (
    offer_pk               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_code             CHAR(7) NOT NULL UNIQUE,
    name                   VARCHAR(160) NOT NULL,
    offer_type             ENUM('percentage','flat','value-add') NOT NULL DEFAULT 'percentage',
    discount_value         INT UNSIGNED NULL,
    valid_from             DATE NOT NULL,
    valid_until            DATE NOT NULL,
    min_value              INT UNSIGNED NULL,
    usage_limit            INT UNSIGNED NULL,
    eligible_destinations  JSON NOT NULL,
    eligible_package_types JSON NOT NULL,
    customer_eligibility   VARCHAR(255) NOT NULL DEFAULT '',
    terms                  TEXT NOT NULL,
    status                 ENUM('draft','published','paused','expired','archived') NOT NULL DEFAULT 'draft',
    created_at             DATETIME NOT NULL, created_by INT UNSIGNED NULL,
    CONSTRAINT ck_offer_code CHECK (offer_code REGEXP '^OF-[0-9]{4}$' AND offer_code <> 'OF-0000'),
    CONSTRAINT ck_offer_dates CHECK (valid_until >= valid_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE offer_packages (
    offer_pk    BIGINT UNSIGNED NOT NULL,
    package_pk  BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (offer_pk, package_pk),
    CONSTRAINT fk_op_offer FOREIGN KEY (offer_pk) REFERENCES offers(offer_pk) ON DELETE CASCADE,
    CONSTRAINT fk_op_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE package_seo (
    package_pk           BIGINT UNSIGNED PRIMARY KEY,
    meta_title           VARCHAR(70) NOT NULL DEFAULT '',
    meta_description     VARCHAR(200) NOT NULL DEFAULT '',
    canonical            VARCHAR(255) NOT NULL DEFAULT '',
    og_title             VARCHAR(160) NOT NULL DEFAULT '',
    og_description       VARCHAR(300) NOT NULL DEFAULT '',
    og_media_id          BIGINT UNSIGNED NULL,
    aeo_question         VARCHAR(255) NOT NULL DEFAULT '',
    aeo_answer           TEXT NOT NULL,
    key_facts            JSON NOT NULL,
    supporting_questions JSON NOT NULL,
    CONSTRAINT fk_seo_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE,
    CONSTRAINT fk_seo_media FOREIGN KEY (og_media_id) REFERENCES media(media_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faqs (
    faq_pk      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_pk  BIGINT UNSIGNED NOT NULL,
    question    VARCHAR(255) NOT NULL,
    answer      TEXT NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_faq_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE curation (
    package_pk           BIGINT UNSIGNED PRIMARY KEY,
    priority_rank        SMALLINT UNSIGNED NULL UNIQUE,
    featured TINYINT(1) NOT NULL DEFAULT 0, homepage_featured TINYINT(1) NOT NULL DEFAULT 0, search_featured TINYINT(1) NOT NULL DEFAULT 0,
    seasonal_featured TINYINT(1) NOT NULL DEFAULT 0, speciality_featured TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT ck_rank CHECK (priority_rank IS NULL OR priority_rank BETWEEN 1 AND 500),
    CONSTRAINT fk_cur_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    review_pk   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_pk  BIGINT UNSIGNED NOT NULL,
    stage       ENUM('content','seo','pricing') NOT NULL,
    decision    ENUM('approved','changes') NOT NULL,
    note        VARCHAR(500) NOT NULL DEFAULT '',
    user_id     INT UNSIGNED NULL,
    package_version INT UNSIGNED NOT NULL,
    at          DATETIME NOT NULL,
    CONSTRAINT fk_rev_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE package_versions (
    pv_pk        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_pk   BIGINT UNSIGNED NOT NULL,
    package_id   CHAR(4) NULL,
    version      INT UNSIGNED NOT NULL,
    sections     VARCHAR(120) NOT NULL DEFAULT '',
    note         VARCHAR(255) NOT NULL DEFAULT '',
    snapshot     JSON NOT NULL,
    changed_by   INT UNSIGNED NULL,
    changed_at   DATETIME NOT NULL,
    UNIQUE KEY ux_pv (package_pk, version),
    CONSTRAINT fk_pv_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_log (
    log_pk      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    at          DATETIME NOT NULL,
    user_id     INT UNSIGNED NULL,
    action      VARCHAR(80) NOT NULL,
    package_pk  BIGINT UNSIGNED NULL,
    package_id  CHAR(4) NULL,
    field       VARCHAR(80) NOT NULL DEFAULT '',
    old_value   TEXT NOT NULL,
    new_value   TEXT NOT NULL,
    ip          VARCHAR(45) NOT NULL DEFAULT '',
    KEY ix_log_pkg (package_pk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Insert-only: grant the application user INSERT and SELECT only on activity_log (no UPDATE/DELETE), and add:
DELIMITER //
CREATE TRIGGER activity_log_no_update BEFORE UPDATE ON activity_log FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'activity_log is insert-only'//
CREATE TRIGGER activity_log_no_delete BEFORE DELETE ON activity_log FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'activity_log is insert-only'//
DELIMITER ;

CREATE TABLE enquiries (
    enquiry_pk      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at      DATETIME NOT NULL,
    enquiry_type    VARCHAR(40) NOT NULL DEFAULT 'TOUR PACKAGE ENQUIRY',
    source          VARCHAR(20) NOT NULL DEFAULT 'website',
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(190) NOT NULL DEFAULT '',
    phone           VARCHAR(30) NOT NULL DEFAULT '',
    package_pk      BIGINT UNSIGNED NULL,
    package_id      CHAR(4) NULL,
    package_name    VARCHAR(160) NOT NULL DEFAULT '',
    internal_ref    VARCHAR(200) NOT NULL DEFAULT '',
    destination     VARCHAR(120) NOT NULL DEFAULT '',
    travel_date     VARCHAR(10) NOT NULL DEFAULT '',
    adults SMALLINT UNSIGNED NULL, children SMALLINT UNSIGNED NULL,
    departure_city  VARCHAR(80) NOT NULL DEFAULT '',
    displayed_rate  VARCHAR(60) NOT NULL DEFAULT '',
    rate_version    INT UNSIGNED NULL,
    rate_validity   VARCHAR(60) NOT NULL DEFAULT '',
    addons          JSON NOT NULL,
    offer_code      CHAR(7) NULL,
    package_url     VARCHAR(255) NOT NULL DEFAULT '',
    utm             JSON NOT NULL,
    message         TEXT NOT NULL,
    stage           ENUM('new','contacted','qualified','quoted','won','lost') NOT NULL DEFAULT 'new',
    assigned_to     INT UNSIGNED NULL,
    is_test         TINYINT(1) NOT NULL DEFAULT 0,
    raw             JSON NOT NULL,
    KEY ix_enq_pid (package_id), KEY ix_enq_offer (offer_code),
    CONSTRAINT fk_enq_pkg FOREIGN KEY (package_pk) REFERENCES packages(package_pk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enquiry_notes (
    note_pk     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enquiry_pk  BIGINT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NULL,
    at          DATETIME NOT NULL,
    note        TEXT NOT NULL,
    CONSTRAINT fk_note_enq FOREIGN KEY (enquiry_pk) REFERENCES enquiries(enquiry_pk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
    email  VARCHAR(190) NOT NULL,
    at     DATETIME NOT NULL,
    ok     TINYINT(1) NOT NULL,
    KEY ix_login (email, at)
) ENGINE=InnoDB;
