<?php
/**
 * Public site settings shared by every page (header, footer, contact page,
 * floating support widget, cookie consent).
 *
 * Only PUBLIC values belong here. SMTP / database credentials live in
 * hgt-config.php outside public_html and must never be added to this file.
 */

if (!defined('HG_SITE_CONFIG')) {
    define('HG_SITE_CONFIG', true);

    define('HG_SITE_URL', 'https://holidaygurutravel.in');

    // Official contact details (confirmed by the owner, 2026-09-29).
    define('HG_PHONE_DISPLAY', '+91 99717 54265');
    define('HG_PHONE_TEL', '+919971754265');
    define('HG_WHATSAPP_NUMBER', '919971754265');
    define('HG_EMAIL_DISPLAY', 'Info@holidaygurutravel.in');
    define('HG_EMAIL', 'info@holidaygurutravel.in');

    // Official office address (confirmed by the owner, 2026-09-29).
    define('HG_ADDRESS_LINE1', 'Holiday Guru Travel 2nd floor, B6 Dharampali Palace');
    define('HG_ADDRESS_LINE2', 'Bhoja Market, Sector 27 Noida');

    // GA4 measurement ID. Loaded only after the visitor accepts analytics
    // cookies (see assets/js/hg-site.js). Empty string disables GA.
    define('HG_GA4_ID', 'G-5QV5YEX7XG');

    // Privacy policy page. Empty until the owner publishes one; the cookie
    // banner hides the link while this is empty.
    define('HG_PRIVACY_URL', '');

    function hg_e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    function hg_tel_href()
    {
        return 'tel:' . HG_PHONE_TEL;
    }

    function hg_mailto_href()
    {
        return 'mailto:' . HG_EMAIL;
    }

    /** wa.me link with an optional pre-filled message. */
    function hg_whatsapp_href($message = '')
    {
        if ($message === '') {
            $message = "Hi Holiday Guru Travel,\nI would like help planning my holiday.";
        }
        return 'https://wa.me/' . HG_WHATSAPP_NUMBER . '?text=' . rawurlencode($message);
    }
}
