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

    // Official contact details (mobile and WhatsApp updated by the owner, 2026-09-30).
    define('HG_PHONE_DISPLAY', '+91 80066 92040');
    define('HG_PHONE_TEL', '+918006692040');
    define('HG_WHATSAPP_NUMBER', '918006692040');
    define('HG_EMAIL_DISPLAY', 'Info@holidaygurutravel.in');
    define('HG_EMAIL', 'info@holidaygurutravel.in');

    // Official office address (confirmed by the owner, 2026-09-29).
    define('HG_ADDRESS_LINE1', '2nd floor, B6 Dharampali Palace');
    define('HG_ADDRESS_LINE2', 'Bhoja Market, Sector 27, Noida-201301 UP(IN)');

    // Official social profiles (both confirmed by the owner, 2026-09-29).
    define('HG_INSTAGRAM_URL', 'https://www.instagram.com/holidaygurutravel/');
    define('HG_FACEBOOK_URL', 'https://www.facebook.com/holidaygurutravel5/');

    // Legal entity and copyright start year (owner, 2026-09-30: "© 2014 M/S Holiday Guru Travel").
    // The previous entity name and its CIN were removed site-wide at the owner's request.
    define('HG_LEGAL_NAME', 'M/S Holiday Guru Travel');
    define('HG_COPYRIGHT_SINCE', 2014);

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
        // Destination pages /tours/{key} are routed to tours.php (no file per destination).
        if (preg_match('#^tours/([a-z0-9-]+)$#', $trimmed, $m)) {
            $dest = json_decode((string) @file_get_contents($root . '/include/data/destinations.json'), true);
            foreach ((isset($dest['groups']) ? $dest['groups'] : array()) as $g) {
                if ($g['key'] === $m[1]) {
                    return true;
                }
            }
            return false;
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
                // Pages still awaiting owner approval (include/data/page-status.php) show as Coming soon.
                $approved = !function_exists('hg_page_status') || hg_page_status((string) parse_url($url, PHP_URL_PATH)) === 'approved';
                $item['state'] = ($status !== 'coming_soon' && $url !== '' && $approved && hg_page_exists($url))
                    ? 'active' : 'coming_soon';
                $items[] = $item;
            }
            $sections[$key]['items'] = $items;
        }
        return $sections;
    }

    /**
     * Inline SVG icon (no icon font needed). Stroke icons follow the Lucide
     * style (ISC licence); the WhatsApp glyph is from Simple Icons (CC0).
     */
    function hg_icon($name, $class = '')
    {
        $stroke = array(
            'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
            'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
            'chat' => '<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/>',
            'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
            'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
            'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.4A4 4 0 1 1 12.6 8 4 4 0 0 1 16 11.4zM17.5 6.5h.01"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
            'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
            'chevron' => '<path d="m6 9 6 6 6-6"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
            'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
            'plane' => '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>',
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
            'bed' => '<path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"/>',
            'car' => '<path d="M5 17h14M3 13l2-6h14l2 6v4H3zM7 17v2M17 17v2"/><circle cx="7.5" cy="13.5" r=".5"/><circle cx="16.5" cy="13.5" r=".5"/>',
            'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
            'arrow' => '<path d="M5 12h14M12 5l7 7-7 7"/>',
            'route' => '<circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/>',
            'temple' => '<path d="M12 2 9 7h6zM5 10h14M6 10v10M18 10v10M10 10v10M14 10v10M3 22h18M4 7h16v3H4z"/>',
            'star' => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
            'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
            'heart' => '<path d="M12 21s-7.5-4.6-9.6-9.3C1 8.3 3.2 4.5 7 4.5c2 0 3.4 1.1 5 3 1.6-1.9 3-3 5-3 3.8 0 6 3.8 4.6 7.2C19.5 16.4 12 21 12 21z"/>',
            'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
            'link' => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>',
            'meal' => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v20M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>',
            'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'x' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>',
            'ticket' => '<path d="M3 9a3 3 0 0 0 0 6v3a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-3a3 3 0 0 0 0-6V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1z"/><path d="M13 5v2M13 17v2M13 11v2"/>',
        );
        $cls = hg_e(trim('hg-i ' . $class));
        if ($name === 'whatsapp') {
            return '<svg class="' . $cls . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.2-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2-.2-.3 0-.5.1-.6l.4-.5c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3m-5.4 7.4a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.7-.2-.4a9.9 9.9 0 0 1-1.5-5.3c0-5.4 4.4-9.9 9.9-9.9 2.6 0 5.1 1 7 2.9a9.8 9.8 0 0 1 2.9 7c0 5.5-4.4 9.9-9.9 9.9m8.4-18.3A11.8 11.8 0 0 0 12 0C5.5 0 .2 5.3.2 11.9c0 2.1.5 4.1 1.6 5.9L0 24l6.4-1.7a11.9 11.9 0 0 0 5.7 1.4c6.6 0 11.9-5.3 11.9-11.9 0-3.2-1.2-6.2-3.5-8.4z"/></svg>';
        }
        $body = isset($stroke[$name]) ? $stroke[$name] : $stroke['info'];
        return '<svg class="' . $cls . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $body . '</svg>';
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
