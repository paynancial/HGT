<?php
// Copy to the directory ABOVE public_html as hgt-config.php (never inside the web root)
// and fill in the password. This file is not served and must not be committed or shared.
// Mail server: Hostinger (owner, 2026-09-30) — SMTP with authentication, STARTTLS on port 587.
return array(
    'smtp_host'     => 'smtp.hostinger.com',
    'smtp_port'     => 587,
    'smtp_secure'   => 'tls',                         // TLS / STARTTLS
    'smtp_username' => 'info@holidaygurutravel.in',
    'smtp_password' => 'CHANGE_ME',                   // the mailbox password — only in the real hgt-config.php
    'mail_from'     => 'info@holidaygurutravel.in',   // must be the authenticated mailbox on Hostinger
    'mail_to'       => 'info@holidaygurutravel.in',   // where website enquiries are delivered
);
