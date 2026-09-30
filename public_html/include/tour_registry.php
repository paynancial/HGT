<?php
/**
 * Tour registry: permanent Tour No., versioned rates and internal curation.
 *
 * Interim, file-based stand-in for the CMS tables in docs/package-registry/schema-draft.sql.
 * Same fields and rules, so the data moves into the database later unchanged.
 *
 *   include/data/tour-registry.json   Tour No. ↔ package (append-only; status proposed|approved|retired)
 *   include/data/rates.json           one row per price version (manually maintained)
 *   include/data/curation.json        internal merchandising (priority_rank 1–500 etc.) — never shown publicly
 *
 * Public pages show a Tour No. only once its mapping is 'approved' (or 'retired', for history).
 * 'proposed' numbers are visible only on local test servers when preview is switched on
 * (HG_TOUR_NO_PREVIEW), so screenshots can show the layout before approval.
 */
require_once __DIR__ . '/site_config.php';

if (!defined('HG_TOUR_REGISTRY')) {
    define('HG_TOUR_REGISTRY', true);

    if (!defined('HG_PAYMENT_ENABLED')) {
        // Pay Now is live only when a payment gateway is connected AND the tour has a current rate.
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
    function hg_tour_no_preview()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
        $local = (bool) preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host);
        return $local && is_file(__DIR__ . '/data/.preview-tour-numbers');
    }

    /** Registry entry for a package slug, or null. */
    function hg_tour_entry($slug)
    {
        static $map = null, $src = null;
        $reg = hg_tr_json('tour-registry.json');
        if ($src !== $reg) {
            $src = $reg;
            $map = array();
            foreach (isset($reg['entries']) ? $reg['entries'] : array() as $e) {
                if (isset($e['slug'], $e['tour_number'], $e['status'])) $map[$e['slug']] = $e;
            }
        }
        return isset($map[$slug]) ? $map[$slug] : null;
    }

    /** Public Tour No. ("0001") for a package slug, or '' when none is approved. */
    function hg_tour_number($slug)
    {
        $e = hg_tour_entry($slug);
        if (!$e || !preg_match('/^(?!0000)[0-9]{4}$/', (string) $e['tour_number'])) return '';
        if (in_array($e['status'], array('approved', 'retired'), true)) return $e['tour_number'];
        if ($e['status'] === 'proposed' && hg_tour_no_preview()) return $e['tour_number'];
        return '';
    }

    /** Whether the shown number is only a proposal (preview on a test server). */
    function hg_tour_number_is_proposed($slug)
    {
        $e = hg_tour_entry($slug);
        return $e && $e['status'] === 'proposed' && hg_tour_number($slug) !== '';
    }

    /** Slug for a public Tour No. (search by number), or ''. */
    function hg_tour_slug_by_number($number)
    {
        $number = str_pad(preg_replace('/\D/', '', (string) $number), 4, '0', STR_PAD_LEFT);
        $reg = hg_tr_json('tour-registry.json');
        foreach (isset($reg['entries']) ? $reg['entries'] : array() as $e) {
            if ($e['tour_number'] === $number && hg_tour_number($e['slug']) === $number) return $e['slug'];
        }
        return '';
    }

    /** Internal package_id. Until the database exists this is the package's permanent slug key. */
    function hg_package_id($slug)
    {
        $e = hg_tour_entry($slug);
        return $e && !empty($e['package_id']) ? (string) $e['package_id'] : 'slug:' . $slug;
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
     * Server-side tour context for an enquiry, built only from our own data
     * (never from submitted text): Tour No., package ID, rate shown and its version/validity.
     */
    function hg_tour_enquiry_context($slug)
    {
        $rate = hg_current_rate($slug);
        return array(
            'Tour No.' => hg_tour_number($slug),
            'Package ID' => hg_package_id($slug),
            'Displayed rate' => $rate ? hg_rate_label($rate) : 'Price on request',
            'Rate version' => $rate ? (string) $rate['version'] : '',
            'Rate validity' => $rate ? hg_rate_validity($rate) : '',
        );
    }
}
