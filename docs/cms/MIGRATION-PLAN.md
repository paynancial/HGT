# Migration plan (staging → production)

Nothing below has been done. Each step needs owner approval first.

## 0. Today (staging)

- The CMS runs from `cms/` on its own port with SQLite.
- It **reads** the website's data files and stylesheets and never writes to the website.
- Staging "Publish" changes only the CMS status.

## 1. Owner decisions needed first

1. **Package ID mapping:** approve `docs/top-tours/OWNER-PACKAGE-ID-DECISIONS.csv`. After that, set `package_id_assignment = true`, and a Super Admin assigns the IDs.
2. **Rates:** the real rates for each package, with validity dates.
3. **Images:** real photos, their rights and credits. The website's image files are not in the repository.
4. **Payment gateway:** which provider. Pay Now stays disabled until one is connected.
5. **Hosting:** a subdomain such as `cms.holidaygurutravel.in`, IP-restricted, with HTTPS.
6. **Staff accounts:** names, emails and roles. Replace the staging users.

## 2. Production database

- Create a **new** MySQL/MariaDB database, `hgt_cms`. Do not touch the existing website or admin tables.
- Apply `cms/schema/mysql.sql`. It was verified on MariaDB in staging: all 20 tables, the Package ID and Offer Code checks, the one-featured-image rule and the insert-only activity-log triggers.
- Give the application user SELECT, INSERT, UPDATE and DELETE on its tables, except INSERT and SELECT only on `activity_log`.
- **SQL portability:** the code uses `INSERT OR REPLACE` / `INSERT OR IGNORE`, which are SQLite syntax. Before production, switch these to `REPLACE INTO` / `INSERT IGNORE` (MySQL). They appear in `save_seo`, `save_advanced`, `save_offers`, `version_restore` and `cms_migrate` (production uses `schema/mysql.sql` instead of `cms_migrate`).

## 3. Import

1. `bin/setup.php`'s import is the tested template. It reads `packages.json`, `package-registry.json`, `rates.json`, `offers.json` and `curation.json`.
2. Run it once against production data, then set `package_id_status = 'approved'` for the approved rows.
3. Recategorise the "Imported" inclusions and exclusions. Some are payment terms.

## 4. Connect the website (a separate, reviewed change)

**Option A: build-time export (recommended first).**

- A CMS command writes the *published versions* (`published_version`) to the website's data files: `packages.json`, `rates.json`, `offers.json` and `curation.json`.
- The website keeps its current templates, so the header, footer and page modules are unaffected. This follows the isolation rule in `docs/architecture/GLOBAL-COMPONENT-ISOLATION.md`.
- Run the website suites before upload.

**Option B: live read through the API.** Needs caching and an outage fallback. Do this later.

**Enquiries:** point `mail.php` at `POST /api/enquiries`, keeping the email as a fallback. Share the intake token through server configuration, never in the repository.

## 5. Rollback

- The CMS is a separate application and database. Removing its vhost removes it.
- Website data exports are committed files, so a revert restores the previous data.

## 6. Security checklist before production

- [ ] HTTPS only, secure cookies, IP allow-list or VPN.
- [ ] Real `config.php` outside version control. No default or staging users.
- [ ] Uploads stored outside the web root (already the case); size and type checks stay on.
- [ ] Backups: database daily, uploads daily. Test a restore.
- [ ] Rotate the SMTP password that was exposed earlier (owner action, still open).
