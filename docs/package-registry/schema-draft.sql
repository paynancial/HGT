-- Holiday Guru Travel — package registry, pricing and enquiry schema (DRAFT)
-- STATUS: proposal for owner review. NOT executed anywhere. Do not run on
-- production. Target: MySQL 8.0.16+ / MariaDB 10.4+ (InnoDB, utf8mb4).
-- The production database version and existing tables are not yet known
-- (admin/db.php and a schema dump were not provided).
--
-- Identifiers (owner decision, 30 Sep 2026):
--   package_id  CHAR(4)  the "Package ID" (0001…): the ONLY package identifier. Permanent. The
--                        itinerary is keyed by it; CRM search, quotations, payments and bookings use it.
--                        Domestic, international and speciality packages share this one sequence.
--   offer_code  CHAR(7)  Offer Code (OF-0001…): a SEPARATE sequence (section 7), never a Package ID.
--                        A package may have several offers.
--   package_pk  BIGINT   internal relational key only; never shown to customers or staff.

-- ------------------------------------------------------------------
-- 1. Package ID counter (single row). Package IDs come ONLY from here.
-- ------------------------------------------------------------------
CREATE TABLE package_id_sequence (
    id          TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    next_value  SMALLINT UNSIGNED NOT NULL,          -- next number to issue
    max_value   SMALLINT UNSIGNED NOT NULL DEFAULT 9999,
    CONSTRAINT chk_seq_single CHECK (id = 1)
) ENGINE=InnoDB;
INSERT INTO package_id_sequence (id, next_value) VALUES (1, 1);

-- ------------------------------------------------------------------
-- 2. Packages. package_pk = internal key; package_id = business key.
-- ------------------------------------------------------------------
CREATE TABLE packages (
    package_pk        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    package_id        CHAR(4) NOT NULL,
    slug              VARCHAR(160) NOT NULL,
    name              VARCHAR(200) NOT NULL,
    destination_key   VARCHAR(40)  NOT NULL,
    package_type      VARCHAR(40)  NULL,
    duration_nights   TINYINT UNSIGNED NULL,
    duration_days     TINYINT UNSIGNED NULL,
    status            ENUM('draft','active','archived','retired') NOT NULL DEFAULT 'draft',
    enquiry_enabled   BOOLEAN NOT NULL DEFAULT TRUE,
    pay_now_enabled   BOOLEAN NOT NULL DEFAULT FALSE,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by        VARCHAR(80) NOT NULL,
    updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_packages_number UNIQUE (package_id),
    CONSTRAINT uq_packages_slug   UNIQUE (slug),
    CONSTRAINT chk_packages_number CHECK (package_id REGEXP '^[0-9]{4}$' AND package_id <> '0000')
) ENGINE=InnoDB;

-- Permanent ledger: a number stays here forever, even if the package row is
-- ever removed, so it can never be issued again.
CREATE TABLE package_id_registry (
    package_id      CHAR(4) NOT NULL PRIMARY KEY,
    package_pk      BIGINT UNSIGNED NOT NULL,
    first_name      VARCHAR(200) NOT NULL,       -- name when the number was issued
    assigned_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    assigned_by     VARCHAR(80) NOT NULL,
    source          ENUM('migration','cms') NOT NULL
) ENGINE=InnoDB;

-- Old slugs keep working (301) after a rename; the number never changes.
CREATE TABLE package_slug_history (
    old_slug    VARCHAR(160) NOT NULL PRIMARY KEY,
    package_pk  BIGINT UNSIGNED NOT NULL,
    changed_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT
) ENGINE=InnoDB;

DELIMITER //
-- Number can never change after creation.
CREATE TRIGGER trg_packages_number_immutable BEFORE UPDATE ON packages FOR EACH ROW
BEGIN
    IF NEW.package_id <> OLD.package_id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'package_id is permanent';
    END IF;
END//
-- Packages are archived/retired, never deleted.
CREATE TRIGGER trg_packages_no_delete BEFORE DELETE ON packages FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'packages are archived, not deleted';
END//
CREATE TRIGGER trg_registry_no_delete BEFORE DELETE ON package_id_registry FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'package numbers are never released';
END//
CREATE TRIGGER trg_registry_no_update BEFORE UPDATE ON package_id_registry FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'package number registry is append-only';
END//

-- The only way to create a package. Concurrency-safe: the counter row is
-- locked (FOR UPDATE) for the whole transaction, so two admins can never get
-- the same number; the UNIQUE/PK constraints are a second guard.
CREATE PROCEDURE create_package(
    IN p_slug VARCHAR(160), IN p_name VARCHAR(200), IN p_destination VARCHAR(40),
    IN p_user VARCHAR(80), IN p_source VARCHAR(10),
    OUT o_package_pk BIGINT UNSIGNED, OUT o_package_id CHAR(4))
BEGIN
    DECLARE v_next SMALLINT UNSIGNED;
    DECLARE v_max SMALLINT UNSIGNED;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    SELECT next_value, max_value INTO v_next, v_max
      FROM package_id_sequence WHERE id = 1 FOR UPDATE;
    IF v_next > v_max THEN
        -- No silent roll-over to 0001: an owner-approved identifier strategy is required.
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Package IDs exhausted (9999): owner decision required';
    END IF;
    SET o_package_id = LPAD(v_next, 4, '0');
    INSERT INTO packages (package_id, slug, name, destination_key, created_by)
         VALUES (o_package_id, p_slug, p_name, p_destination, p_user);
    SET o_package_pk = LAST_INSERT_ID();
    INSERT INTO package_id_registry (package_id, package_pk, first_name, assigned_by, source)
         VALUES (o_package_id, o_package_pk, p_name, p_user, p_source);
    UPDATE package_id_sequence SET next_value = v_next + 1 WHERE id = 1;
    COMMIT;
END//
DELIMITER ;

-- ------------------------------------------------------------------
-- 3. Rates: one row per approved price version (immutable once approved).
-- ------------------------------------------------------------------
CREATE TABLE package_rates (
    rate_id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    package_pk         BIGINT UNSIGNED NOT NULL,
    version            SMALLINT UNSIGNED NOT NULL,              -- 1, 2, 3… per package
    status             ENUM('draft','approved','superseded','withdrawn') NOT NULL DEFAULT 'draft',
    currency           CHAR(3) NOT NULL DEFAULT 'INR',
    price_unit         ENUM('per_person_twin_sharing','per_person','per_couple','per_group') NOT NULL,
    base_price         DECIMAL(12,2) NOT NULL,                  -- standard package cost
    adult_price        DECIMAL(12,2) NULL,
    child_price        DECIMAL(12,2) NULL,
    single_supplement  DECIMAL(12,2) NULL,
    extra_bed          DECIMAL(12,2) NULL,
    tax_mode           ENUM('included','extra','not_applicable') NOT NULL,
    tax_rate_percent   DECIMAL(5,2) NULL,                       -- e.g. 5.00 GST where "extra"
    offer_price        DECIMAL(12,2) NULL,                      -- only with a real, approved offer
    offer_reason       VARCHAR(200) NULL,
    includes_airfare   BOOLEAN NOT NULL DEFAULT FALSE,          -- standard rule: external travel excluded
    includes_train     BOOLEAN NOT NULL DEFAULT FALSE,
    includes_bus       BOOLEAN NOT NULL DEFAULT FALSE,
    rate_valid_from    DATE NOT NULL,
    rate_valid_until   DATE NULL,                               -- NULL = "subject to confirmation"
    price_notes        TEXT NULL,
    payment_mode       ENUM('none','advance','full') NOT NULL DEFAULT 'none',
    advance_percent    DECIMAL(5,2) NULL,
    created_by         VARCHAR(80) NOT NULL,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved_by        VARCHAR(80) NULL,
    approved_at        DATETIME NULL,
    change_reason      VARCHAR(500) NULL,
    CONSTRAINT uq_rate_version UNIQUE (package_pk, version),
    CONSTRAINT chk_rate_validity CHECK (rate_valid_until IS NULL OR rate_valid_until >= rate_valid_from),
    CONSTRAINT chk_offer CHECK (offer_price IS NULL OR (offer_price < base_price AND offer_reason IS NOT NULL)),
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Inclusions / exclusions belong to the SAME rate version, so a new price can
-- never be shown next to old inclusions.
CREATE TABLE package_rate_items (
    item_id    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    rate_id    BIGINT UNSIGNED NOT NULL,
    kind       ENUM('inclusion','exclusion') NOT NULL,
    category   ENUM('accommodation','meals','local_transfer','sightseeing','vehicle','driver',
                    'activity','entry_fee','external_air','external_train','external_bus',
                    'insurance','visa','tax','personal','other') NOT NULL,
    text       VARCHAR(500) NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (rate_id) REFERENCES package_rates (rate_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Price-bearing fields of an approved rate cannot be edited: change = new version.
-- Its inclusions/exclusions are locked the same way (triggers below).
DELIMITER //
CREATE TRIGGER trg_items_lock_ins BEFORE INSERT ON package_rate_items FOR EACH ROW
BEGIN
    IF (SELECT status FROM package_rates WHERE rate_id = NEW.rate_id) <> 'draft' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'inclusions/exclusions of an approved rate are locked: create a new version';
    END IF;
END//
CREATE TRIGGER trg_items_lock_upd BEFORE UPDATE ON package_rate_items FOR EACH ROW
BEGIN
    IF (SELECT status FROM package_rates WHERE rate_id = OLD.rate_id) <> 'draft' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'inclusions/exclusions of an approved rate are locked: create a new version';
    END IF;
END//
CREATE TRIGGER trg_items_lock_del BEFORE DELETE ON package_rate_items FOR EACH ROW
BEGIN
    IF (SELECT status FROM package_rates WHERE rate_id = OLD.rate_id) <> 'draft' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'inclusions/exclusions of an approved rate are locked: create a new version';
    END IF;
END//
CREATE TRIGGER trg_rate_immutable BEFORE UPDATE ON package_rates FOR EACH ROW
BEGIN
    IF OLD.status <> 'draft' AND (
        NEW.base_price <> OLD.base_price OR NOT (NEW.offer_price <=> OLD.offer_price)
        OR NEW.currency <> OLD.currency OR NEW.price_unit <> OLD.price_unit
        OR NEW.rate_valid_from <> OLD.rate_valid_from OR NOT (NEW.rate_valid_until <=> OLD.rate_valid_until)
        OR NEW.includes_airfare <> OLD.includes_airfare OR NEW.includes_train <> OLD.includes_train
        OR NEW.includes_bus <> OLD.includes_bus OR NEW.version <> OLD.version) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'approved rates are immutable: create a new version';
    END IF;
END//
DELIMITER ;

-- The one rate the website may show: approved, started, not expired, latest version.
CREATE VIEW package_active_rate AS
SELECT r.* FROM package_rates r
WHERE r.status = 'approved'
  AND r.rate_valid_from <= CURRENT_DATE
  AND (r.rate_valid_until IS NULL OR r.rate_valid_until >= CURRENT_DATE)
  AND r.version = (SELECT MAX(r2.version) FROM package_rates r2
                   WHERE r2.package_pk = r.package_pk AND r2.status = 'approved'
                     AND r2.rate_valid_from <= CURRENT_DATE
                     AND (r2.rate_valid_until IS NULL OR r2.rate_valid_until >= CURRENT_DATE));

-- ------------------------------------------------------------------
-- 4. Audit trail (append-only) for packages, rates and items.
-- ------------------------------------------------------------------
CREATE TABLE package_audit (
    audit_id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    package_pk      BIGINT UNSIGNED NOT NULL,
    package_id  CHAR(4) NOT NULL,
    entity          ENUM('package','rate','rate_item','content') NOT NULL,
    entity_id       BIGINT UNSIGNED NULL,
    action          ENUM('create','update','approve','supersede','withdraw','archive','retire') NOT NULL,
    old_values      JSON NULL,
    new_values      JSON NULL,
    reason          VARCHAR(500) NULL,
    changed_by      VARCHAR(80) NOT NULL,
    changed_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------------
-- 5. CRM-ready records (enquiry → quotation → payment → booking).
--    Each keeps package_pk (relational key) + package_id + name
--    snapshot + rate version seen, so later edits never blur history.
-- ------------------------------------------------------------------
CREATE TABLE enquiries (
    enquiry_id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    enquiry_ref       CHAR(12) NOT NULL UNIQUE,              -- shown to customer, e.g. ENQ-7K3P9Q
    enquiry_type      ENUM('tour_package','customized_holiday','general','newsletter') NOT NULL,
    package_pk        BIGINT UNSIGNED NULL,
    package_id        CHAR(4) NULL,
    package_name      VARCHAR(200) NULL,                     -- snapshot
    package_url       VARCHAR(300) NULL,
    rate_id           BIGINT UNSIGNED NULL,
    rate_version      SMALLINT UNSIGNED NULL,
    displayed_price   DECIMAL(12,2) NULL,                     -- NULL when "Price on request"
    displayed_currency CHAR(3) NULL,
    displayed_valid_until DATE NULL,
    destination       VARCHAR(80) NULL,
    travel_date       DATE NULL,
    adults            TINYINT UNSIGNED NULL,
    children          TINYINT UNSIGNED NULL,
    departure_city    VARCHAR(80) NULL,
    customer_name     VARCHAR(100) NOT NULL,
    customer_phone    VARCHAR(20) NOT NULL,
    customer_email    VARCHAR(150) NOT NULL,
    message           TEXT NULL,
    utm_source        VARCHAR(100) NULL, utm_medium VARCHAR(100) NULL, utm_campaign VARCHAR(100) NULL,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT,
    FOREIGN KEY (rate_id) REFERENCES package_rates (rate_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE quotations (
    quotation_id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    quotation_ref     CHAR(12) NOT NULL UNIQUE,
    enquiry_id        BIGINT UNSIGNED NULL,
    package_pk        BIGINT UNSIGNED NULL,
    package_id        CHAR(4) NULL,
    package_name      VARCHAR(200) NULL,
    rate_id           BIGINT UNSIGNED NULL,
    rate_version      SMALLINT UNSIGNED NULL,
    rate_updated_at   DATETIME NULL,
    travel_date       DATE NULL,
    adults            TINYINT UNSIGNED NULL,
    children          TINYINT UNSIGNED NULL,
    amount            DECIMAL(12,2) NOT NULL,
    currency          CHAR(3) NOT NULL,
    breakdown         JSON NOT NULL,                          -- standard cost, taxes, add-ons, external travel
    valid_until       DATE NULL,
    status            ENUM('draft','sent','accepted','expired','cancelled') NOT NULL DEFAULT 'draft',
    created_by        VARCHAR(80) NOT NULL,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enquiry_id) REFERENCES enquiries (enquiry_id),
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT,
    FOREIGN KEY (rate_id) REFERENCES package_rates (rate_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE payments (
    payment_id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    payment_ref       CHAR(14) NOT NULL UNIQUE,
    package_pk        BIGINT UNSIGNED NOT NULL,
    package_id        CHAR(4) NOT NULL,
    package_name      VARCHAR(200) NOT NULL,
    rate_id           BIGINT UNSIGNED NOT NULL,
    rate_version      SMALLINT UNSIGNED NOT NULL,
    enquiry_id        BIGINT UNSIGNED NULL,
    quotation_id      BIGINT UNSIGNED NULL,
    amount            DECIMAL(12,2) NOT NULL,                 -- computed server-side, never from the browser
    currency          CHAR(3) NOT NULL,
    payment_kind      ENUM('advance','full','balance') NOT NULL,
    travel_date       DATE NOT NULL,
    adults            TINYINT UNSIGNED NOT NULL,
    children          TINYINT UNSIGNED NOT NULL,
    customer_name     VARCHAR(100) NOT NULL,
    customer_phone    VARCHAR(20) NOT NULL,
    customer_email    VARCHAR(150) NOT NULL,
    gateway           VARCHAR(30) NULL,
    gateway_order_id  VARCHAR(100) NULL UNIQUE,
    gateway_payment_id VARCHAR(100) NULL UNIQUE,
    status            ENUM('created','pending','paid','failed','refunded','cancelled') NOT NULL DEFAULT 'created',
    paid_at           DATETIME NULL,                          -- set only from a verified gateway webhook
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT,
    FOREIGN KEY (rate_id) REFERENCES package_rates (rate_id) ON DELETE RESTRICT,
    FOREIGN KEY (enquiry_id) REFERENCES enquiries (enquiry_id),
    FOREIGN KEY (quotation_id) REFERENCES quotations (quotation_id)
) ENGINE=InnoDB;

-- Leads and bookings complete the CRM chain: Package ID → enquiry → lead → quotation → payment → booking.
-- Every step keeps package_pk + package_id; history is never identified by package name alone.
CREATE TABLE leads (
    lead_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    enquiry_id        BIGINT UNSIGNED NOT NULL,
    package_pk        BIGINT UNSIGNED NULL,
    package_id        CHAR(4) NULL,
    owner             VARCHAR(80) NULL,                       -- travel expert handling the lead
    stage             ENUM('new','contacted','quoted','won','lost') NOT NULL DEFAULT 'new',
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enquiry_id) REFERENCES enquiries (enquiry_id),
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE bookings (
    booking_id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    booking_ref       CHAR(12) NOT NULL UNIQUE,
    package_pk        BIGINT UNSIGNED NOT NULL,
    package_id        CHAR(4) NOT NULL,
    package_name      VARCHAR(200) NOT NULL,                  -- snapshot
    rate_id           BIGINT UNSIGNED NULL,
    rate_version      SMALLINT UNSIGNED NULL,
    quotation_id      BIGINT UNSIGNED NULL,
    payment_id        BIGINT UNSIGNED NULL,
    travel_date       DATE NOT NULL,
    adults            TINYINT UNSIGNED NOT NULL,
    children          TINYINT UNSIGNED NOT NULL,
    status            ENUM('confirmed','completed','cancelled') NOT NULL DEFAULT 'confirmed',
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT,
    FOREIGN KEY (rate_id) REFERENCES package_rates (rate_id) ON DELETE RESTRICT,
    FOREIGN KEY (quotation_id) REFERENCES quotations (quotation_id),
    FOREIGN KEY (payment_id) REFERENCES payments (payment_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------------
-- 6. Internal curation ("Top 500"). Merchandising data only — never shown publicly.
-- ------------------------------------------------------------------
CREATE TABLE package_curation (
    package_pk          BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    priority_rank       SMALLINT UNSIGNED NULL,               -- 1–500, unique; NULL = not in the priority collection
    is_featured         BOOLEAN NOT NULL DEFAULT FALSE,
    is_top_priority     BOOLEAN NOT NULL DEFAULT FALSE,
    homepage_featured   BOOLEAN NOT NULL DEFAULT FALSE,
    search_featured     BOOLEAN NOT NULL DEFAULT FALSE,
    seasonal_featured   BOOLEAN NOT NULL DEFAULT FALSE,
    speciality_featured BOOLEAN NOT NULL DEFAULT FALSE,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by          VARCHAR(80) NOT NULL,
    CONSTRAINT uq_curation_rank UNIQUE (priority_rank),
    CONSTRAINT chk_curation_rank CHECK (priority_rank IS NULL OR priority_rank BETWEEN 1 AND 500),
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------------
-- 7. Offers: OF-0001... from their OWN sequence (never the Package ID sequence).
-- ------------------------------------------------------------------
CREATE TABLE offer_code_sequence (
    id          TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    next_value  SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT chk_offer_seq_single CHECK (id = 1)
) ENGINE=InnoDB;
INSERT INTO offer_code_sequence (id, next_value) VALUES (1, 1);

CREATE TABLE offers (
    offer_id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    offer_code    CHAR(7) NOT NULL,
    title         VARCHAR(200) NOT NULL,
    status        ENUM('draft','published','expired','withdrawn') NOT NULL DEFAULT 'draft',
    valid_from    DATE NULL,
    valid_until   DATE NULL,
    terms         TEXT NULL,
    created_by    VARCHAR(80) NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_offer_code UNIQUE (offer_code),
    CONSTRAINT chk_offer_code CHECK (offer_code REGEXP '^OF-[0-9]{4}$' AND offer_code <> 'OF-0000')
) ENGINE=InnoDB;

-- One offer can apply to several packages; one package can have several offers.
CREATE TABLE offer_packages (
    offer_id    BIGINT UNSIGNED NOT NULL,
    package_pk  BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (offer_id, package_pk),
    FOREIGN KEY (offer_id) REFERENCES offers (offer_id) ON DELETE RESTRICT,
    FOREIGN KEY (package_pk) REFERENCES packages (package_pk) ON DELETE RESTRICT
) ENGINE=InnoDB;

DELIMITER //
CREATE PROCEDURE create_offer(IN p_title VARCHAR(200), IN p_user VARCHAR(80), OUT o_offer_id BIGINT UNSIGNED, OUT o_offer_code CHAR(7))
BEGIN
    DECLARE v_next SMALLINT UNSIGNED;
    START TRANSACTION;
    SELECT next_value INTO v_next FROM offer_code_sequence WHERE id = 1 FOR UPDATE;
    IF v_next > 9999 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'offer codes exhausted (OF-9999): owner decision required';
    END IF;
    SET o_offer_code = CONCAT('OF-', LPAD(v_next, 4, '0'));
    INSERT INTO offers (offer_code, title, created_by) VALUES (o_offer_code, p_title, p_user);
    SET o_offer_id = LAST_INSERT_ID();
    UPDATE offer_code_sequence SET next_value = v_next + 1 WHERE id = 1;
    COMMIT;
END//
DELIMITER ;

-- Historical records keep the offer that applied.
ALTER TABLE enquiries  ADD COLUMN offer_code CHAR(7) NULL AFTER rate_version;
ALTER TABLE quotations ADD COLUMN offer_code CHAR(7) NULL AFTER rate_version;
ALTER TABLE payments   ADD COLUMN offer_code CHAR(7) NULL AFTER rate_version;
ALTER TABLE bookings   ADD COLUMN offer_code CHAR(7) NULL AFTER rate_version;
