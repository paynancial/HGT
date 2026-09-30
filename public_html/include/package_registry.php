<?php
/**
 * Package registry: permanent Package ID, versioned rates and internal curation.
 *
 * Interim, file-based stand-in for the CMS tables in docs/package-registry/schema-draft.sql.
 * Same fields and rules, so the data moves into the database later unchanged.
 *
 *   include/data/package-registry.json  Package ID ↔ package (append-only; status proposed|approved|retired).
 *                                       The Package ID (0001…) is the ONLY package identifier: the itinerary
 *                                       is keyed by it and CRM/quotations use it. Domestic, international and
 *                                       speciality packages share this one sequence — no duplicates.
 *   include/data/offers.json            Offer Codes (OF-0001…): a SEPARATE sequence, never a Package ID.
 *                                       A package may have several offers.
 *   include/data/rates.json             one row per price version (manually maintained)
 *   include/data/curation.json          internal merchandising (priority_rank 1–500 etc.) — never shown publicly
 *
 * Owner rule: the Package ID is shown on the website only in the itinerary header, and is carried
 * in enquiries, WhatsApp messages and CRM/quotation records. Only 'approved' (or 'retired', for
 * history) IDs are shown; 'proposed' IDs appear only on local test servers with the preview switch on.
 */
require_once __DIR__ . '/site_config.php';

if (!defined('HG_PACKAGE_REGISTRY')) {
    define('HG_PACKAGE_REGISTRY', true);

    if (!defined('HG_PAYMENT_ENABLED')) {
        // Pay Now is live only when a payment gateway is connected AND the package has a current rate.
        define('HG_PAYMENT_ENABLED', false);
    }

    /** Data loader. $set replaces a file's data (used by tools/tests only). */
    function hg_tr_json($file, $set = null)
    {
        static $cache = array();
        if ($set !== null) { $cache[$file] = $set; return $set; }
        if (!array_key_exists($file, $cache)) {
            $path = __DIR__ . '/data/' . $file;
            $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
            $cache[$file] = is_array($data) ? $data : array();
        }
        return $cache[$file];
    }

    /** True on local test servers with the preview switch on; never on the live site. */
    function hg_package_id_preview()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
        $local = (bool) preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host);
        return $local && is_file(__DIR__ . '/data/.preview-package-ids');
    }

    /** Registry entry for a package slug, or null. */
    function hg_package_entry($slug)
    {
        static $map = null, $src = null;
        $reg = hg_tr_json('package-registry.json');
        if ($src !== $reg) {
            $src = $reg;
            $map = array();
            foreach (isset($reg['entries']) ? $reg['entries'] : array() as $e) {
                if (isset($e['slug'], $e['package_id'], $e['status'])) $map[$e['slug']] = $e;
            }
        }
        return isset($map[$slug]) ? $map[$slug] : null;
    }

    /** Public Package ID ("0001") for a package slug, or '' when none is approved. */
    function hg_package_id($slug)
    {
        $e = hg_package_entry($slug);
        if (!$e || !preg_match('/^(?!0000)[0-9]{4}$/', (string) $e['package_id'])) return '';
        if (in_array($e['status'], array('approved', 'retired'), true)) return $e['package_id'];
        if ($e['status'] === 'proposed' && hg_package_id_preview()) return $e['package_id'];
        return '';
    }

    /** Whether the shown Package ID is only a proposal (preview on a test server). */
    function hg_package_id_is_proposed($slug)
    {
        $e = hg_package_entry($slug);
        return $e && $e['status'] === 'proposed' && hg_package_id($slug) !== '';
    }

    /** Slug for a Package ID (search / CRM lookup by ID), or ''. */
    function hg_slug_by_package_id($id)
    {
        $id = str_pad(preg_replace('/\D/', '', (string) $id), 4, '0', STR_PAD_LEFT);
        $reg = hg_tr_json('package-registry.json');
        foreach (isset($reg['entries']) ? $reg['entries'] : array() as $e) {
            if ($e['package_id'] === $id && hg_package_id($e['slug']) === $id) return $e['slug'];
        }
        return '';
    }

    /** Internal database key (never shown publicly). Until the database exists it is the permanent slug key. */
    function hg_package_key($slug)
    {
        $e = hg_package_entry($slug);
        return $e && !empty($e['internal_key']) ? (string) $e['internal_key'] : 'slug:' . $slug;
    }

    /** Offer codes are OF-0001…: a separate format and sequence, so they can never collide with a Package ID. */
    function hg_is_offer_code($code)
    {
        return (bool) preg_match('/^OF-(?!0000)[0-9]{4}$/', (string) $code);
    }

    /** Published offer by code, or null. */
    function hg_offer($code)
    {
        if (!hg_is_offer_code($code)) return null;
        $o = hg_tr_json('offers.json');
        foreach (isset($o['offers']) ? $o['offers'] : array() as $row) {
            if (isset($row['offer_code']) && $row['offer_code'] === $code && (!isset($row['status']) || $row['status'] === 'published')) return $row;
        }
        return null;
    }

    /** Published offers that apply to a package (a package may have several). */
    function hg_offers_for($slug)
    {
        $o = hg_tr_json('offers.json');
        return array_values(array_filter(isset($o['offers']) ? $o['offers'] : array(), function ($row) use ($slug) {
            return isset($row['offer_code'], $row['packages']) && hg_is_offer_code($row['offer_code'])
                && (!isset($row['status']) || $row['status'] === 'published') && in_array($slug, (array) $row['packages'], true);
        }));
    }

    /**
     * The rate version to show publicly, or null ("Price on request").
     * A version counts only if approved, priced, and valid today; expired rates are never shown.
     */
    function hg_current_rate($slug, $rates = null, $today = null)
    {
        $today = $today ?: date('Y-m-d');
        if ($rates === null) {
            $all = hg_tr_json('rates.json');
            $rates = isset($all['versions']) ? $all['versions'] : array();
        }
        $best = null;
        foreach ($rates as $r) {
            if (!isset($r['slug']) || $r['slug'] !== $slug) continue;
            if (!isset($r['rate_status']) || $r['rate_status'] !== 'approved') continue;
            if (!isset($r['base_price']) || !is_numeric($r['base_price']) || $r['base_price'] <= 0) continue;
            if (empty($r['rate_valid_from']) || empty($r['rate_valid_until'])) continue;
            if ($today < $r['rate_valid_from'] || $today > $r['rate_valid_until']) continue;
            if (!$best || (int) $r['version'] > (int) $best['version']) $best = $r;
        }
        return $best;
    }

    function hg_rate_label(array $r)
    {
        $sym = (isset($r['currency']) ? $r['currency'] : 'INR') === 'INR' ? '₹' : $r['currency'] . ' ';
        return $sym . number_format((float) $r['base_price'], 0, '.', ',') . (!empty($r['price_unit']) ? ' / ' . $r['price_unit'] : '');
    }

    function hg_rate_validity(array $r)
    {
        $f = function ($d) { $t = strtotime($d); return $t ? date('j M Y', $t) : $d; };
        return $f($r['rate_valid_from']) . ' – ' . $f($r['rate_valid_until']);
    }

    /** Pay Now is active only with a current rate AND a connected payment gateway. */
    function hg_paynow_enabled($slug)
    {
        return HG_PAYMENT_ENABLED && hg_current_rate($slug) !== null;
    }

    /** Internal curation for a slug (never rendered publicly). */
    function hg_curation($slug)
    {
        $c = hg_tr_json('curation.json');
        $row = isset($c['tours'][$slug]) ? $c['tours'][$slug] : array();
        $rank = isset($row['priority_rank']) ? (int) $row['priority_rank'] : 0;
        return array(
            'priority_rank' => ($rank >= 1 && $rank <= 500) ? $rank : 0,
            'is_featured' => !empty($row['is_featured']),
            'is_top_priority' => !empty($row['is_top_priority']),
            'homepage_featured' => !empty($row['homepage_featured']),
            'search_featured' => !empty($row['search_featured']),
            'seasonal_featured' => !empty($row['seasonal_featured']),
            'speciality_featured' => !empty($row['speciality_featured']),
        );
    }

    /**
     * Server-side package context for an enquiry, built only from our own data
     * (never from submitted text): Package ID, internal key, offer code (only a published offer valid
     * for this package), rate shown and its version/validity.
     */
    function hg_package_enquiry_context($slug, $offerCode = '')
    {
        $rate = hg_current_rate($slug);
        $offer = '';
        foreach (hg_offers_for($slug) as $o) { if ($o['offer_code'] === $offerCode) $offer = $offerCode; }
        return array(
            'Package ID' => hg_package_id($slug),
            'Internal ref' => hg_package_key($slug),
            'Offer code' => $offer,
            'Displayed rate' => $rate ? hg_rate_label($rate) : 'Price on request',
            'Rate version' => $rate ? (string) $rate['version'] : '',
            'Rate validity' => $rate ? hg_rate_validity($rate) : '',
        );
    }
}
