# Holiday Guru Travel — holidaygurutravel.in

`public_html/` is the live site as of 2026-09-29 (plain PHP + Bootstrap template, MySQL via `admin/db.php`).

Not in this repository (server only):
- `public_html/admin/` — admin panel and `db.php` database credentials
- `public_html/assets/img/` — image library
- Database dump

Secrets are never committed. SMTP credentials live in a config file outside the web root (copy `config/hgt-config.example.php` to `hgt-config.php` one level above `public_html` on the server).
