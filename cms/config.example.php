<?php
/**
 * Tour Package CMS configuration — EXAMPLE (no secrets).
 * Copy to cms/config.php (gitignored) and adjust. Never commit config.php.
 */
return array(
    // 'staging' shows the staging ribbon and allows the setup script. Use 'production' only after owner approval.
    'env' => 'staging',

    // SQLite database for staging. Production uses MySQL/MariaDB (schema/mysql.sql):
    //   'dsn' => 'mysql:host=localhost;dbname=hgt_cms;charset=utf8mb4', 'db_user' => '…', 'db_pass' => '…'
    'dsn' => 'sqlite:' . __DIR__ . '/storage/cms.sqlite',
    'db_user' => null,
    'db_pass' => null,

    // The public website's folder. The CMS reads its data files (import) and stylesheets (preview).
    // The CMS never writes into it.
    'site_root' => __DIR__ . '/../public_html',
    'site_url' => 'https://holidaygurutravel.in',

    // Uploaded media (outside the web root; served through the CMS after login).
    'upload_dir' => __DIR__ . '/storage/uploads',
    'upload_max_bytes' => 8 * 1024 * 1024,

    // Package ID assignment stays OFF until the owner approves the Package ID mapping
    // (docs/top-tours/OWNER-PACKAGE-ID-DECISIONS.csv). While off, packages show "Pending" / "Proposed".
    'package_id_assignment' => false,

    // Online payment: no gateway is connected. Pay Now stays disabled everywhere while this is false.
    'payment_gateway' => false,

    // Website → CRM enquiry intake (POST /api/enquiries). Empty = intake disabled.
    // Set a long random value in config.php and the same value on the website side.
    'intake_token' => '',

    // Offer Zone limit (internal): at most this many offers can be published at once.
    'offer_zone_limit' => 100,
);
