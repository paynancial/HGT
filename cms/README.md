# Tour Package CMS (staging)

A separate admin application for Holiday Guru Travel tour packages, offers and CRM enquiries.
It lives outside `public_html/`, so uploading the website never includes it.

**Package ID is the only package identifier. There is no Tour No. Offer Codes (OF-0001…) are separate.**

```bash
cp cms/config.example.php cms/config.php   # no secrets in the example
php cms/bin/setup.php --reset              # staging DB, website package import, one user per role
php -S 127.0.0.1:8099 -t cms/public cms/public/router.php
php cms/tests/unit.php                     # domain tests
```

Full documentation: [docs/cms/README.md](../docs/cms/README.md).
Do not deploy without owner approval. See [docs/cms/MIGRATION-PLAN.md](../docs/cms/MIGRATION-PLAN.md).
