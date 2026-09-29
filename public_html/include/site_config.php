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

    // Legal entity (confirmed by the owner, 2026-09-29).
    define('HG_LEGAL_NAME', 'M/S Swaasthik Vocation Pvt. Ltd.');
    define('HG_CIN', 'U74999UP2021PTC154544');

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

    /**
     * True when an internal URL points at a page that exists in this site.
     * '/'        -> index.php
     * '/about'   -> about.php (extensionless public URL)
     * '/a/b/'    -> a/b/index.php or a/b.php (future folder URLs)
     * External https:// URLs are trusted as configured.
     */
    function hg_page_exists($url)
    {
        if (preg_match('#^https?://#i', $url)) {
            return true;
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || $path[0] !== '/' || strpos($path, '..') !== false) {
            return false;
        }
        $root = dirname(__DIR__);
        $trimmed = trim($path, '/');
        if ($trimmed === '') {
            return is_file($root . '/index.php');
        }
        return is_file($root . '/' . $trimmed . '.php')
            || is_file($root . '/' . $trimmed . '/index.php')
            || (strpos(basename($trimmed), '.') !== false && is_file($root . '/' . $trimmed));
    }

    /**
     * Footer navigation from include/footer_nav.php with each item's state
     * resolved: 'active' (real page, rendered as a link) or 'coming_soon'
     * (plain text). Hidden items are removed.
     */
    function hg_footer_nav()
    {
        $sections = include __DIR__ . '/footer_nav.php';
        foreach ($sections as $key => $section) {
            // Optional 'order' per item; items without one keep their file order.
            $position = 0;
            foreach ($section['items'] as $i => $item) {
                $section['items'][$i]['_sort'] = array(isset($item['order']) ? (int) $item['order'] : PHP_INT_MAX, $position++);
            }
            usort($section['items'], function ($a, $b) {
                return $a['_sort'] < $b['_sort'] ? -1 : ($a['_sort'] > $b['_sort'] ? 1 : 0);
            });
            $items = array();
            foreach ($section['items'] as $item) {
                $status = isset($item['status']) ? $item['status'] : 'auto';
                if ($status === 'hidden') {
                    continue;
                }
                $url = isset($item['url']) ? trim($item['url']) : '';
                $item['state'] = ($status !== 'coming_soon' && $url !== '' && hg_page_exists($url))
                    ? 'active' : 'coming_soon';
                $items[] = $item;
            }
            $sections[$key]['items'] = $items;
        }
        return $sections;
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
