<?php
/**
 * Holiday Guru Travel — Phase 1 UI core.
 *
 * Data access (real package data from include/data/*.json), SEO head,
 * structured data and reusable components. Page files call
 * hg_layout_start($meta) ... hg_layout_end().
 */

require_once __DIR__ . '/../site_config.php';
require_once __DIR__ . '/../package_registry.php';

if (!defined('HG_UI_CORE')) {
    define('HG_UI_CORE', true);

    // Development mode: local test servers only. Shows clearly marked
    // placeholders (missing images, reviews block) that production never shows.
    $hgHost = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
    define('HG_DEV_MODE', (bool) preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $hgHost));

    /* ------------------------------------------------------------------ */
    /* Data                                                                */
    /* ------------------------------------------------------------------ */

    function hg_packages()
    {
        static $packages = null;
        if ($packages === null) {
            $packages = json_decode((string) file_get_contents(__DIR__ . '/../data/packages.json'), true);
            $packages = is_array($packages) ? $packages : array();
        }
        return $packages;
    }

    function hg_package($slug)
    {
        foreach (hg_packages() as $p) {
            if ($p['slug'] === $slug) {
                return $p;
            }
        }
        return null;
    }

    /** Destination groups with live package counts, keyed by group key. */
    function hg_groups()
    {
        static $groups = null;
        if ($groups === null) {
            $data = json_decode((string) file_get_contents(__DIR__ . '/../data/destinations.json'), true);
            $groups = array();
            foreach ($data['groups'] as $g) {
                $g['count'] = 0;
                $g['image'] = '';
                $groups[$g['key']] = $g;
            }
            foreach (hg_packages() as $p) {
                if (isset($groups[$p['group']])) {
                    $groups[$p['group']]['count']++;
                    if ($groups[$p['group']]['image'] === '' && $p['image'] !== '') {
                        $groups[$p['group']]['image'] = $p['image'];
                    }
                }
            }
        }
        return $groups;
    }

    function hg_group($key)
    {
        $g = hg_groups();
        return isset($g[$key]) ? $g[$key] : null;
    }

    function hg_packages_where($fn)
    {
        return array_values(array_filter(hg_packages(), $fn));
    }

    function hg_packages_in($groupKey)
    {
        return hg_packages_where(function ($p) use ($groupKey) {
            return $p['group'] === $groupKey;
        });
    }

    /** Duration range text from real packages, e.g. "4–8 days". */
    function hg_duration_range(array $packages)
    {
        $days = array_filter(array_map(function ($p) { return $p['days']; }, $packages));
        if (!$days) {
            return '';
        }
        $min = min($days);
        $max = max($days);
        return $min === $max ? "{$min} days" : "{$min}–{$max} days";
    }

    /** Short feature flags derived from a package's real inclusion text. */
    function hg_inclusion_flags(array $p)
    {
        $text = strtolower(implode(' ', $p['inclusions']));
        $flags = array();
        if (preg_match('/hotel|stay|accommodation|houseboat|room/', $text)) $flags[] = 'Hotels';
        if (preg_match('/breakfast|dinner|meal|map |cp |ap /', $text)) $flags[] = 'Meals';
        if (preg_match('/cab|transfer|pick ?up|car|innova|vehicle/', $text)) $flags[] = 'Transfers';
        if (preg_match('/sightseeing/', $text)) $flags[] = 'Sightseeing';
        if (preg_match('/helicopter|chopper/', $text)) $flags[] = 'Helicopter';
        return $flags;
    }

    /** Cities covered as a clean comma list (from the page's "Cities Covered" line). */
    function hg_cities(array $p)
    {
        if (trim($p['cities']) === '') {
            return '';
        }
        $parts = preg_split('/\s*[-,>]+\s*\d*\s*Days?\s*|\s*->\s*|,\s*/i', $p['cities']);
        $seen = array();
        foreach ($parts as $c) {
            $c = trim(preg_replace('/\s*-?\s*\d+\s*Days?$/i', '', $c), " -\t");
            $c = str_ireplace('Pahalga ', 'Pahalgam ', $c . ' ');
            $c = trim($c);
            if ($c !== '' && !in_array(strtolower($c), array_map('strtolower', $seen), true)) {
                $seen[] = $c;
            }
        }
        return implode(' · ', $seen);
    }

    /* ------------------------------------------------------------------ */
    /* Search state (shared by hero, header, results, package, enquiry)    */
    /* ------------------------------------------------------------------ */

    /** Validated search context from the query string. */
    function hg_search_state()
    {
        static $s = null;
        if ($s !== null) {
            return $s;
        }
        $get = function ($k) { return isset($_GET[$k]) && is_string($_GET[$k]) ? trim($_GET[$k]) : ''; };
        $date = $get('date');
        $d = DateTime::createFromFormat('!Y-m-d', $date);
        $date = ($d && $d->format('Y-m-d') === $date) ? $date : '';
        $adults = (int) $get('adults');
        $children = (int) $get('children');
        $departure = strtolower($get('departure'));
        $s = array(
            'destination' => mb_substr(preg_replace('/[^\p{L}\p{N} &,\'-]/u', '', $get('destination')), 0, 60),
            'date' => $date,
            'adults' => ($adults >= 1 && $adults <= 9) ? $adults : 2,
            'children' => ($children >= 0 && $children <= 6) ? $children : 0,
            'departure' => in_array($departure, array('delhi', 'haridwar'), true) ? $departure : '',
            'has_travellers' => $get('adults') !== '',
        );
        return $s;
    }

    /** http_build_query with list params written as name[]=v (as HTML forms submit them). */
    function hg_qs(array $q)
    {
        return preg_replace('/%5B\d+%5D=/', '%5B%5D=', http_build_query($q));
    }

    /** Query string carrying the search context (date, travellers, departure) to the next page. */
    function hg_context_query(array $extra = array())
    {
        $s = hg_search_state();
        $q = array();
        if ($s['date']) $q['date'] = $s['date'];
        if ($s['has_travellers']) { $q['adults'] = $s['adults']; $q['children'] = $s['children']; }
        if ($s['departure']) $q['departure'] = $s['departure'];
        $q = array_merge($q, $extra);
        return $q ? '?' . hg_qs($q) : '';
    }

    /** Resolve free-text destination to [groupKey, place] using names, keywords and places. */
    function hg_resolve_destination($text)
    {
        $t = strtolower(trim($text));
        if ($t === '') {
            return array(null, null);
        }
        foreach (hg_groups() as $key => $g) {
            if ($t === $key || $t === strtolower($g['name']) || strpos(strtolower($g['name']), $t) === 0) {
                return array($key, null);
            }
        }
        // A specific place ("gulmarg") maps to the group where it appears most, plus a place filter.
        $hits = array();
        $placeName = null;
        foreach (hg_packages() as $p) {
            foreach ($p['places'] as $pl) {
                if (strtolower($pl) === $t) {
                    $hits[$p['group']] = (isset($hits[$p['group']]) ? $hits[$p['group']] : 0) + 1;
                    $placeName = $pl;
                }
            }
        }
        if ($hits) {
            arsort($hits);
            return array(key($hits), $placeName);
        }
        foreach (hg_groups() as $key => $g) {
            if (preg_match('/\b' . preg_quote($t, '/') . '/', strtolower($g['keywords']))) {
                return array($key, null);
            }
        }
        return array(null, null);
    }

    function hg_date_label($ymd)
    {
        $d = DateTime::createFromFormat('!Y-m-d', $ymd);
        return $d ? $d->format('j M Y') : '';
    }

    /* ------------------------------------------------------------------ */
    /* Images                                                              */
    /* ------------------------------------------------------------------ */

    /**
     * <img> for a site image. If the file is missing (the image library is not
     * in this repository), render a neutral block instead of a broken image;
     * in dev mode it is labelled so reviewers know an image belongs there.
     *
     * Loading (components/media-reveal.css + include/global/page-loader.php):
     * - below the fold: loading="lazy"; $eager = true for the primary/hero image (fetchpriority="high");
     *   $eager = 'low' for images in view but not urgent (later hero slides: fetchpriority="low").
     * - responsive: when tools/build_images.php has made {name}-{480|960|1600}.{avif|webp} next to the
     *   original, a <picture> offers them with srcset/sizes (the original stays the fallback).
     * - reveal: inside an .hg-frame the image fades in from a slight blur once loaded (onload marks it);
     *   onerror swaps in the branded fallback, so no broken-image icon is ever shown.
     */
    function hg_img($path, $alt, $width, $height, $class = '', $eager = false, $sizes = '')
    {
        $path = ltrim((string) $path, '/');
        $file = dirname(__DIR__, 2) . '/' . $path;
        $cls = trim('hg-media ' . $class);
        if ($path !== '' && is_file($file)) {
            $img = '<img class="' . hg_e($cls . ' hg-reveal') . '" src="/' . hg_e($path) . '" alt="' . hg_e($alt) . '" width="' . (int) $width
                . '" height="' . (int) $height . '"' . ($eager === 'low' ? ' fetchpriority="low"' : ($eager ? ' fetchpriority="high"' : ' loading="lazy"')) . ' decoding="async"'
                . ' onload="this.classList.add(\'is-loaded\')" onerror="window.hgImgFail&amp;&amp;hgImgFail(this)">';
            $base = preg_replace('/\.[a-z0-9]+$/i', '', $path);
            $sources = '';
            foreach (array('avif', 'webp') as $fmt) {
                $set = array();
                foreach (array(480, 960, 1600) as $w) {
                    if (is_file(dirname(__DIR__, 2) . '/' . $base . '-' . $w . '.' . $fmt)) $set[] = '/' . $base . '-' . $w . '.' . $fmt . ' ' . $w . 'w';
                }
                if ($set) {
                    $sources .= '<source type="image/' . $fmt . '" srcset="' . hg_e(implode(', ', $set)) . '" sizes="'
                        . hg_e($sizes !== '' ? $sizes : '(max-width: 640px) 100vw, ' . (int) $width . 'px') . '">';
                }
            }
            return $sources ? '<picture class="hg-pic">' . $sources . $img . '</picture>' : $img;
        }
        // Blank until the owner adds the photo (the expected file is kept in data-image for the team).
        $label = '';
        $a11y = $alt === '' ? 'aria-hidden="true"' : 'role="img" aria-label="' . hg_e($alt) . '"';
        return '<div class="' . hg_e($cls) . ' hg-ph" data-image="' . hg_e($path) . '" ' . $a11y . ' style="aspect-ratio:' . (int) $width . '/' . (int) $height . '">'
            . '<svg class="hg-ph__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M6 38l11-14 8 9 6-7 11 12z" fill="currentColor" opacity=".55"/><circle cx="34" cy="14" r="5" fill="currentColor" opacity=".55"/></svg>'
            . $label . '</div>';
    }

    /* ------------------------------------------------------------------ */
    /* SEO head + structured data                                          */
    /* ------------------------------------------------------------------ */

    function hg_abs($path)
    {
        return preg_match('#^https?://#', $path) ? $path : HG_SITE_URL . '/' . ltrim($path, '/');
    }

    function hg_org_schema()
    {
        return array(
            '@type' => array('Organization', 'TravelAgency'),
            '@id' => HG_SITE_URL . '/#organization',
            'name' => 'Holiday Guru Travel',
            'legalName' => HG_LEGAL_NAME,
            'url' => HG_SITE_URL . '/',
            'logo' => HG_SITE_URL . '/assets/brand/holiday-guru-travel-logo-240.png',
            'slogan' => 'Your journey. Your way.',
            'telephone' => HG_PHONE_DISPLAY,
            'email' => HG_EMAIL,
            'address' => array(
                '@type' => 'PostalAddress',
                'streetAddress' => '2nd floor, B6 Dharampali Palace, Bhoja Market, Sector 27',
                'addressLocality' => 'Noida',
                'addressRegion' => 'Uttar Pradesh',
                'postalCode' => '201301',
                'addressCountry' => 'IN',
            ),
            'sameAs' => array(HG_INSTAGRAM_URL, HG_FACEBOOK_URL),
        );
    }

    /**
     * $meta keys: title, description, path (canonical path), image, type,
     * index (bool), breadcrumbs [[label, path|null], ...], schema [ ... ]
     */
    /**
     * Versioned URL for a site asset: /assets/css/hg-ui.css?v=<file time>. After an upload the URL changes,
     * so browsers fetch the new stylesheet/script instead of a cached old copy.
     */
    function hg_asset($path)
    {
        $f = dirname(__DIR__, 2) . $path;
        return is_file($f) ? $path . '?v=' . filemtime($f) : $path;
    }

    function hg_head(array $meta)
    {
        $title = $meta['title'];
        $desc = $meta['description'];
        $canonical = hg_abs(isset($meta['path']) ? $meta['path'] : '/');
        $image = hg_abs(isset($meta['image']) && $meta['image'] ? $meta['image'] : '/assets/brand/holiday-guru-travel-logo-720.webp');
        $index = !isset($meta['index']) || $meta['index'];

        $graph = array(hg_org_schema(), array(
            '@type' => 'WebSite', '@id' => HG_SITE_URL . '/#website', 'url' => HG_SITE_URL . '/',
            'name' => 'Holiday Guru Travel', 'publisher' => array('@id' => HG_SITE_URL . '/#organization'),
        ));
        if (!empty($meta['breadcrumbs'])) {
            $items = array();
            foreach ($meta['breadcrumbs'] as $i => $b) {
                $item = array('@type' => 'ListItem', 'position' => $i + 1, 'name' => $b[0]);
                if (!empty($b[1])) {
                    $item['item'] = hg_abs($b[1]);
                }
                $items[] = $item;
            }
            $graph[] = array('@type' => 'BreadcrumbList', 'itemListElement' => $items);
        }
        if (!empty($meta['schema'])) {
            foreach ($meta['schema'] as $s) {
                $graph[] = $s;
            }
        }
        $json = json_encode(array('@context' => 'https://schema.org', '@graph' => $graph),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

        ob_start(); ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $hgLoaderPart = 'head'; include __DIR__ . '/../global/page-loader.php'; ?>
    <title><?= hg_e($title) ?></title>
    <meta name="description" content="<?= hg_e($desc) ?>">
    <meta name="robots" content="<?= $index ? 'index,follow,max-image-preview:large' : 'noindex,follow' ?>">
    <link rel="canonical" href="<?= hg_e($canonical) ?>">
    <meta property="og:site_name" content="Holiday Guru Travel">
    <meta property="og:type" content="<?= hg_e(isset($meta['type']) ? $meta['type'] : 'website') ?>">
    <meta property="og:title" content="<?= hg_e($title) ?>">
    <meta property="og:description" content="<?= hg_e($desc) ?>">
    <meta property="og:url" content="<?= hg_e($canonical) ?>">
    <meta property="og:image" content="<?= hg_e($image) ?>">
    <meta property="og:locale" content="en_IN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0A163D">
    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/brand/favicon-32.png">
    <link rel="apple-touch-icon" href="/assets/brand/apple-touch-icon.png">
    <link rel="manifest" href="/assets/brand/site.webmanifest">
    <link rel="preload" href="/assets/fonts/hg/plus-jakarta-sans-latin-800-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/hg/plus-jakarta-sans-latin-700-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/hg/inter-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= hg_e(hg_asset('/assets/css/hg-site.css')) ?>">
    <link rel="stylesheet" href="<?= hg_e(hg_asset('/assets/css/hg-ui.css')) ?>">
    <script type="application/ld+json"><?= $json ?></script>
<?php
        return ob_get_clean();
    }

    function hg_layout_start(array $meta)
    {
        $GLOBALS['hgMeta'] = $meta;
        echo "<!doctype html>\n<html lang=\"en-IN\">\n<head>\n" . hg_head($meta) . "</head>\n<body class=\"hg-body\">\n";
        $hgLoaderPart = 'body';
        include __DIR__ . '/../global/page-loader.php';
        include __DIR__ . '/../global/header.php';
        echo '<main id="main" class="hg-main" tabindex="-1">' . "\n";
        if (!empty($meta['breadcrumbs']) && empty($meta['hide_breadcrumbs'])) {
            echo hg_breadcrumbs($meta['breadcrumbs']);
        }
    }

    function hg_layout_end()
    {
        echo "</main>\n";
        include __DIR__ . '/../global/footer.php';
        include __DIR__ . '/../global/support-widget.php';
        include __DIR__ . '/../global/cookie-consent.php';
        include __DIR__ . '/../global/login-dialog.php';
        echo '<script src="' . hg_e(hg_asset('/assets/js/hg-site.js')) . '" defer></script>' . "\n";
        echo '<script src="' . hg_e(hg_asset('/assets/js/hg-ui.js')) . '" defer></script>' . "\n";
        echo "</body>\n</html>\n";
    }

    /* ------------------------------------------------------------------ */
    /* Components                                                          */
    /* ------------------------------------------------------------------ */

    function hg_breadcrumbs(array $crumbs)
    {
        $out = '<nav class="hg-crumbs" aria-label="Breadcrumb"><div class="hg-container"><ol>';
        $last = count($crumbs) - 1;
        foreach ($crumbs as $i => $c) {
            if ($i === $last || empty($c[1])) {
                $out .= '<li><span aria-current="page">' . hg_e($c[0]) . '</span></li>';
            } else {
                $out .= '<li><a href="' . hg_e($c[1]) . '">' . hg_e($c[0]) . '</a></li>';
            }
        }
        return $out . '</ol></div></nav>';
    }

    function hg_section_head($eyebrow, $title, $lead = '', $link = null, $id = '')
    {
        $out = '<div class="hg-section__head">';
        $out .= '<div>' . ($eyebrow ? '<p class="hg-eyebrow">' . hg_e($eyebrow) . '</p>' : '');
        $out .= '<h2 class="hg-h2"' . ($id ? ' id="' . hg_e($id) . '"' : '') . '>' . hg_e($title) . '</h2>';
        $out .= $lead ? '<p class="hg-lead">' . $lead . '</p>' : '';
        $out .= '</div>';
        if ($link) {
            $out .= '<a class="hg-link-arrow" href="' . hg_e($link[1]) . '">' . hg_e($link[0]) . '<span aria-hidden="true"> &rarr;</span></a>';
        }
        return $out . '</div>';
    }

    /** Destination card meta without inventory totals: trip lengths and area, e.g. "4–8 day trips · North India". */
    function hg_group_meta(array $g)
    {
        $range = hg_duration_range(hg_packages_in($g['key']));
        return ($range ? str_replace(' days', '', $range) . ' day trips · ' : '') . $g['area'];
    }

    /** Price block for cards: current approved rate with validity, or "Price on request". */
    function hg_card_price_html($slug)
    {
        $r = hg_current_rate($slug);
        if (!$r) return '<span class="hg-pcard__price">Price on request</span>';
        return '<span class="hg-pcard__price"><strong>' . hg_e(hg_rate_label($r)) . '</strong><small>Valid until ' . hg_e(date('j M Y', strtotime($r['rate_valid_until']))) . '</small></span>';
    }

    function hg_destination_card(array $g, $headingLevel = 3)
    {
        $h = 'h' . (int) $headingLevel;
        return '<a class="hg-dcard" href="' . hg_e($g['hub_url']) . '">'
            . '<span class="hg-dcard__media hg-frame">' . hg_img($g['image'], $g['name'] . ' holiday destination', 480, 360, 'hg-dcard__img') . '</span>'
            . '<span class="hg-dcard__body"><' . $h . ' class="hg-dcard__title">' . hg_e($g['name']) . '</' . $h . '>'
            . '<span class="hg-dcard__meta">' . hg_e(hg_group_meta($g)) . '</span>'
            . '<span class="hg-dcard__cta">View tours <span aria-hidden="true">&rarr;</span></span></span></a>';
    }

    function hg_package_card(array $p, $headingLevel = 3)
    {
        $g = hg_group($p['group']);
        $h = 'h' . (int) $headingLevel;
        $flags = hg_inclusion_flags($p);
        $cities = hg_cities($p);
        $out = '<article class="hg-pcard">';
        $out .= '<a class="hg-pcard__media hg-frame" href="' . hg_e($p['url']) . '" tabindex="-1" aria-hidden="true">'
            . hg_img($p['image'], '', 480, 320, 'hg-pcard__img') . '</a>';
        $out .= '<div class="hg-pcard__body">';
        $out .= '<p class="hg-pcard__kicker">' . hg_e($g ? $g['name'] : '') . ($p['departure'] ? ' · From ' . hg_e($p['departure']) : '') . '</p>';
        $out .= '<' . $h . ' class="hg-pcard__title"><a href="' . hg_e($p['url']) . '">' . hg_e($p['title'] ?: $p['name']) . '</a></' . $h . '>';
        $out .= '<p class="hg-pcard__duration"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>' . hg_e($p['duration']) . '</p>';
        if ($cities) {
            $out .= '<p class="hg-pcard__cities">' . hg_e($cities) . '</p>';
        }
        if ($flags) {
            $out .= '<ul class="hg-pcard__flags" aria-label="Included">';
            foreach ($flags as $f) {
                $out .= '<li>' . hg_e($f) . '</li>';
            }
            $out .= '</ul>';
        }
        $out .= '<div class="hg-pcard__foot">' . hg_card_price_html($p['slug'])
            . '<a class="hg-btn hg-btn--sm hg-btn--outline" href="' . hg_e($p['url']) . '" aria-label="View package: ' . hg_e($p['title'] ?: $p['name']) . '">View package</a></div>';
        $out .= '</div></article>';
        return $out;
    }

    function hg_package_grid(array $packages, $headingLevel = 3)
    {
        $out = '<div class="hg-grid hg-grid--cards">';
        foreach ($packages as $p) {
            $out .= hg_package_card($p, $headingLevel);
        }
        return $out . '</div>';
    }

    /** FAQ list: [[question, answerHtml], ...]. $schema=true returns [html, FAQPage schema]. */
    function hg_faq(array $faqs, $idPrefix = 'faq')
    {
        $out = '<div class="hg-faq">';
        foreach ($faqs as $i => $f) {
            $out .= '<details class="hg-faq__item"' . ($i === 0 ? ' open' : '') . '><summary class="hg-faq__q"><h3>' . hg_e($f[0]) . '</h3>'
                . '<span class="hg-faq__icon" aria-hidden="true"></span></summary><div class="hg-faq__a">' . $f[1] . '</div></details>';
        }
        return $out . '</div>';
    }

    function hg_faq_schema(array $faqs)
    {
        $items = array();
        foreach ($faqs as $f) {
            $items[] = array('@type' => 'Question', 'name' => $f[0],
                'acceptedAnswer' => array('@type' => 'Answer', 'text' => trim(strip_tags($f[1]))));
        }
        return array('@type' => 'FAQPage', 'mainEntity' => $items);
    }

    /** Answer-first block: question heading, direct answer, then detail. */
    function hg_answer($id, $question, $answer, $detailHtml = '')
    {
        return '<section class="hg-answer" aria-labelledby="' . hg_e($id) . '"><h2 class="hg-h2" id="' . hg_e($id) . '">' . hg_e($question) . '</h2>'
            . '<p class="hg-answer__direct">' . $answer . '</p>' . ($detailHtml ? '<div class="hg-prose">' . $detailHtml . '</div>' : '') . '</section>';
    }

    function hg_facts(array $facts, $title = 'Quick facts')
    {
        $out = '<aside class="hg-facts" aria-label="' . hg_e($title) . '"><h2 class="hg-facts__title">' . hg_e($title) . '</h2><dl>';
        foreach ($facts as $k => $v) {
            $out .= '<div><dt>' . hg_e($k) . '</dt><dd>' . $v . '</dd></div>';
        }
        return $out . '</dl></aside>';
    }

    function hg_tip($title, $html)
    {
        return '<div class="hg-tip"><p class="hg-tip__title"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5c.8.8 1 1.5 1 2.5h6c0-1 .2-1.7 1-2.5A6 6 0 0 0 12 3z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
            . hg_e($title) . '</p><div class="hg-tip__body">' . $html . '</div></div>';
    }

    /** Page-local section navigation (sticky). $items = [[id, label], ...] */
    function hg_section_nav(array $items, $label = 'On this page', $ctaHtml = '')
    {
        $out = '<nav class="hg-secnav" aria-label="' . hg_e($label) . '"><div class="hg-container hg-secnav__inner"><ul>';
        foreach ($items as $it) {
            $out .= '<li><a href="#' . hg_e($it[0]) . '">' . hg_e($it[1]) . '</a></li>';
        }
        return $out . '</ul>' . $ctaHtml . '</div></nav>';
    }

    /**
     * Enquiry form (posts to mail.php with the Phase 0 spam guards added by
     * hg-ui.js). $context adds hidden fields such as package or destination.
     */
    function hg_enquiry_form($id, $title, array $context = array(), $compact = false, $extraFields = '')
    {
        static $n = 0;
        $n++;
        $p = 'enq' . $n . '-';
        $S = hg_search_state();
        $deps = array('' => 'Not decided', 'delhi' => 'Delhi', 'haridwar' => 'Haridwar', 'own' => 'I will reach the destination myself');
        $hidden = '';
        foreach ($context as $k => $v) {
            $hidden .= '<input type="hidden" name="' . hg_e($k) . '" value="' . hg_e($v) . '">';
        }
        ob_start(); ?>
<form class="hg-form<?= $compact ? ' hg-form--compact' : '' ?>" id="<?= hg_e($id) ?>" data-hg-enquiry novalidate>
    <?php if ($title) { ?><h2 class="hg-form__title"><?= hg_e($title) ?></h2><?php } ?>
    <?= $hidden ?>
    <div class="hg-form__grid">
        <div class="hg-field"><label for="<?= $p ?>name">Full name</label><input id="<?= $p ?>name" name="name" autocomplete="name" required maxlength="100"></div>
        <div class="hg-field"><label for="<?= $p ?>phone">Mobile number</label><input id="<?= $p ?>phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="20" pattern="[0-9+ ]{8,20}"></div>
        <div class="hg-field"><label for="<?= $p ?>email">Email</label><input id="<?= $p ?>email" name="email" type="email" autocomplete="email" required maxlength="150"></div>
        <div class="hg-field"><label for="<?= $p ?>date">Travel date <span class="hg-optional">(optional)</span></label><input id="<?= $p ?>date" name="travel_date" type="date" min="<?= date('Y-m-d') ?>" value="<?= hg_e($S['date']) ?>"></div>
        <div class="hg-field"><label for="<?= $p ?>adults">Adults</label><select id="<?= $p ?>adults" name="adults"><?php for ($i = 1; $i <= 9; $i++) { ?><option<?= $i === $S['adults'] ? ' selected' : '' ?>><?= $i ?></option><?php } ?></select></div>
        <div class="hg-field"><label for="<?= $p ?>children">Children</label><select id="<?= $p ?>children" name="children"><?php for ($i = 0; $i <= 6; $i++) { ?><option<?= $i === $S['children'] ? ' selected' : '' ?>><?= $i ?></option><?php } ?></select></div>
        <div class="hg-field"><label for="<?= $p ?>dep">Departure city</label><select id="<?= $p ?>dep" name="departure_city"><?php foreach ($deps as $v => $l) { ?><option value="<?= hg_e($v === '' ? '' : $l) ?>"<?= $v !== '' && $v === $S['departure'] ? ' selected' : '' ?>><?= hg_e($l) ?></option><?php } ?></select></div>
        <?= $extraFields ?>
        <div class="hg-field hg-field--full"><label for="<?= $p ?>message">Anything we should know? <span class="hg-optional">(optional)</span></label><textarea id="<?= $p ?>message" name="message" rows="<?= $compact ? 2 : 3 ?>" maxlength="2000"></textarea></div>
    </div>
    <p class="hg-form__status" role="status" aria-live="polite"></p>
    <button class="hg-btn hg-btn--primary hg-btn--block" type="submit">Get my free quote</button>
    <p class="hg-form__note">A travel expert replies by phone or WhatsApp. We do not share your details.</p>
</form>
<?php
        return ob_get_clean();
    }

    /** Owner-approval status of a page (include/data/page-status.php); pages not listed are 'approved'. */
    function hg_page_status($path)
    {
        static $st = null;
        if ($st === null) {
            $f = dirname(__DIR__) . '/data/page-status.php';
            $st = is_file($f) ? (array) include $f : array();
        }
        return isset($st[$path]) ? $st[$path] : 'approved';
    }

    /** Booking FAQs built from the company's real package booking terms. */
    function hg_booking_faqs()
    {
        return array(
            array('How do I book a holiday package with Holiday Guru Travel?',
                '<p>Send an enquiry (or call or WhatsApp ' . hg_e(HG_PHONE_DISPLAY) . '). A travel expert confirms the itinerary, hotels and price with you. Our package booking terms ask for a <strong>35% advance</strong> to confirm, with the balance paid before departure. We issue a booking voucher once the payment is received.</p>'),
            array('Which payment methods do you accept?',
                '<p>Net banking, IMPS, NEFT, cheque and UPI (including Google Pay, PhonePe, Paytm and scan-to-pay QR). We do not accept cash. Air and train tickets need full payment at the time of booking.</p>'),
            array('Can I change the itinerary or hotels in a package?',
                '<p>Yes. Every package can be used as a starting point: tell us your dates, travellers, hotel preference and anything you want to add or remove, and we send a revised itinerary and quote. Use <a href="/customized-holidays">Customised Tours</a> for a trip planned from scratch.</p>'),
            array('Are flights or train tickets included?',
                '<p>Most packages start and end at the destination (for example, pick-up and drop at Srinagar airport) and list air and train fares under exclusions. Each package page shows exactly what is included and excluded. We can add flights or trains to your quote on request.</p>'),
            array('Where is your office?',
                '<p>' . hg_e(HG_ADDRESS_LINE1) . ', ' . hg_e(HG_ADDRESS_LINE2) . '. Holiday Guru Travel is operated by ' . hg_e(HG_LEGAL_NAME) . '.</p>'),
        );
    }

    function hg_cta_band($title, $text)
    {
        return '<section class="hg-ctaband"><div class="hg-container hg-ctaband__inner"><div><h2 class="hg-h2 hg-h2--light">' . hg_e($title) . '</h2><p>' . $text . '</p></div>'
            . '<div class="hg-ctaband__actions"><a class="hg-btn hg-btn--primary" href="/customized-holidays">Plan my trip</a>'
            . '<a class="hg-btn hg-btn--light" href="' . hg_e(hg_whatsapp_href()) . '" target="_blank" rel="noopener">WhatsApp an expert</a></div></div></section>';
    }

    function hg_dev_note($text)
    {
        return HG_DEV_MODE ? '<div class="hg-devnote" role="note"><strong>Development placeholder:</strong> ' . $text . '</div>' : '';
    }
}
