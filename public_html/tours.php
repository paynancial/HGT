<?php
/**
 * Holiday package search results.
 *   /tours                   all packages (search results, never indexed)
 *   /tours/{destination}     destination results; with rich content
 *                            (include/content/{key}.php) it is the destination page
 * Filters, sorting, paging and search context use query parameters; any
 * parameterised URL is noindex with a canonical to the clean URL.
 */
require __DIR__ . '/include/ui/core.php';

$S = hg_search_state();
$destKey = isset($_GET['dest']) && is_string($_GET['dest']) ? strtolower($_GET['dest']) : '';

/* ---------- /tours?destination=... → resolve to /tours/{key} ---------- */
if ($destKey === '' && $S['destination'] !== '') {
    list($k, $place) = hg_resolve_destination($S['destination']);
    if ($k) {
        $extra = $place ? array('place' => array(strtolower($place))) : array();
        $q = hg_context_query($extra);
        header('Location: /tours/' . $k . $q, true, 302);
        exit;
    }
}

/* ---------- Search by Package ID ("0001", "Package ID 0001") → that package (redirect, never a new URL) ---------- */
if ($destKey === '' && preg_match('/^\s*(?:(?:package|pkg|tour)\s*(?:id|no\.?|number)?\s*[:#]?\s*)?(\d{1,4})\s*$/i', $S['destination'], $tm)) {
    $tslug = hg_slug_by_package_id($tm[1]);
    if ($tslug && ($tp = hg_package($tslug))) {
        header('Location: ' . $tp['url'] . hg_context_query(), true, 302);
        exit;
    }
}

$group = $destKey !== '' ? hg_group($destKey) : null;
if ($destKey !== '' && !$group) {
    http_response_code(404);
}

/* ---------- Load packages safely ---------- */
$loadError = false;
try {
    $all = hg_packages();
    if (!$all) {
        $loadError = true;
    }
} catch (Throwable $e) {
    $all = array();
    $loadError = true;
    error_log('tours: package data unavailable: ' . $e->getMessage());
}

$content = null;
if ($group && !empty($group['content'])) {
    $content = include __DIR__ . '/include/content/' . basename($group['content']) . '.php';
    if (empty($content['image'])) $content['image'] = $group['image'];
}

/* ---------- Scope and filters ---------- */
$textQuery = ($destKey === '' && $S['destination'] !== '') ? $S['destination'] : '';
$scope = array_values(array_filter($all, function ($p) use ($destKey, $textQuery) {
    if ($destKey !== '') {
        return $p['group'] === $destKey;
    }
    if ($textQuery !== '') {
        $hay = strtolower($p['title'] . ' ' . $p['name'] . ' ' . implode(' ', $p['places']) . ' ' . $p['cities']);
        return strpos($hay, strtolower($textQuery)) !== false;
    }
    return true;
}));

$arr = function ($k) {
    $v = isset($_GET[$k]) ? $_GET[$k] : array();
    $v = is_array($v) ? $v : array($v);
    return array_values(array_filter(array_map(function ($x) { return is_string($x) ? strtolower(trim($x)) : ''; }, $v)));
};
$durBuckets = array('3-4' => array(1, 4, '3–4 days'), '5-6' => array(5, 6, '5–6 days'), '7-8' => array(7, 8, '7–8 days'), '9-plus' => array(9, 99, '9+ days'));
$sel = array(
    'place' => $arr('place'),
    'dur' => array_values(array_intersect($arr('dur'), array_keys($durBuckets))),
    'feat' => $arr('feat'),
    'hotel' => $arr('hotel'),
    'meal' => $arr('meal'),
    'budget' => $arr('budget'),
    'departure' => $S['departure'] ? array($S['departure']) : array(),
);
$slug = function ($s) { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-'); };
// Budget buckets (per person, INR) apply only to tours with a current approved rate.
$budgetBuckets = array('under-20000' => array(0, 19999, 'Under ₹20,000'), '20000-40000' => array(20000, 40000, '₹20,000 – ₹40,000'), 'over-40000' => array(40001, PHP_INT_MAX, 'Over ₹40,000'));
$rateOf = function ($p) { static $c = array(); if (!array_key_exists($p['slug'], $c)) $c[$p['slug']] = hg_current_rate($p['slug']); return $c[$p['slug']]; };

$matches = function ($p, $except = null) use ($sel, $durBuckets, $slug, $budgetBuckets, $rateOf) {
    if ($except !== 'hotel' && $sel['hotel'] && !in_array($slug($p['hotel']), $sel['hotel'], true)) return false;
    if ($except !== 'meal' && $sel['meal'] && !in_array($slug($p['meals']), $sel['meal'], true)) return false;
    if ($except !== 'budget' && $sel['budget']) {
        $r = $rateOf($p);
        if (!$r) return false;
        $ok = false;
        foreach ($sel['budget'] as $b) {
            if (isset($budgetBuckets[$b]) && $r['base_price'] >= $budgetBuckets[$b][0] && $r['base_price'] <= $budgetBuckets[$b][1]) $ok = true;
        }
        if (!$ok) return false;
    }
    if ($except !== 'place' && $sel['place']) {
        $pp = array_map($slug, $p['places']);
        if (!array_intersect($sel['place'], $pp)) return false;
    }
    if ($except !== 'dur' && $sel['dur']) {
        $ok = false;
        foreach ($sel['dur'] as $b) {
            if ($p['days'] >= $durBuckets[$b][0] && $p['days'] <= $durBuckets[$b][1]) $ok = true;
        }
        if (!$ok) return false;
    }
    if ($except !== 'feat' && $sel['feat']) {
        $pf = array_map($slug, $p['features']);
        if (array_diff($sel['feat'], $pf)) return false;
    }
    if ($except !== 'departure' && $sel['departure']) {
        // Only packages with a different fixed start city are excluded; packages that
        // start at the destination stay (travel to the start point is quoted separately).
        if ($p['departure'] && strtolower($p['departure']) !== $sel['departure'][0]) return false;
    }
    return true;
};

// Facet availability (each facet evaluated with the other filters applied). Used only to
// enable/disable options — counts are never shown publicly.
$facet = array('place' => array(), 'dur' => array(), 'feat' => array(), 'departure' => array(), 'fixed_departure' => false, 'hotel' => array(), 'meal' => array(), 'budget' => array());
foreach ($scope as $p) {
    if ($matches($p, 'place')) foreach ($p['places'] as $pl) { $facet['place'][$pl] = (isset($facet['place'][$pl]) ? $facet['place'][$pl] : 0) + 1; }
    if ($matches($p, 'dur')) foreach ($durBuckets as $b => $d) { if ($p['days'] >= $d[0] && $p['days'] <= $d[1]) $facet['dur'][$b] = (isset($facet['dur'][$b]) ? $facet['dur'][$b] : 0) + 1; }
    if ($matches($p, 'feat')) foreach ($p['features'] as $f) { $facet['feat'][$f] = (isset($facet['feat'][$f]) ? $facet['feat'][$f] : 0) + 1; }
    if ($p['hotel'] && $matches($p, 'hotel')) $facet['hotel'][$p['hotel']] = (isset($facet['hotel'][$p['hotel']]) ? $facet['hotel'][$p['hotel']] : 0) + 1;
    if ($p['meals'] && $matches($p, 'meal')) $facet['meal'][$p['meals']] = (isset($facet['meal'][$p['meals']]) ? $facet['meal'][$p['meals']] : 0) + 1;
    if (($r = $rateOf($p)) && $matches($p, 'budget')) foreach ($budgetBuckets as $b => $d) { if ($r['base_price'] >= $d[0] && $r['base_price'] <= $d[1]) $facet['budget'][$b] = (isset($facet['budget'][$b]) ? $facet['budget'][$b] : 0) + 1; }
    if ($matches($p, 'departure')) {
        foreach (array('Delhi', 'Haridwar') as $dc) {
            if (!$p['departure'] || $p['departure'] === $dc) $facet['departure'][$dc] = (isset($facet['departure'][$dc]) ? $facet['departure'][$dc] : 0) + 1;
        }
        if ($p['departure']) $facet['fixed_departure'] = true;
    }
}
arsort($facet['place']);

$results = array_values(array_filter($scope, $matches));

/* ---------- Sorting ---------- */
// "Recommended" is our merchandising order (internal priority ranking, then complete itineraries);
// it is not a claim that a tour is objectively best. Price sorts appear only when rates are published.
$sorts = array('recommended' => 'Recommended');
$priced = count(array_filter($scope, function ($p) use ($rateOf) { return (bool) $rateOf($p); }));
if ($priced >= 2) { $sorts['price-asc'] = 'Price: low to high'; $sorts['price-desc'] = 'Price: high to low'; }
$sorts += array('duration-asc' => 'Duration: shortest first', 'duration-desc' => 'Duration: longest first', 'name' => 'Name A–Z');
$sort = isset($_GET['sort']) && isset($sorts[$_GET['sort']]) ? $_GET['sort'] : 'recommended';
usort($results, function ($a, $b) use ($sort, $rateOf) {
    switch ($sort) {
        case 'price-asc':
        case 'price-desc':
            // Tours without a current rate go last in both directions.
            $pa = $rateOf($a) ? (float) $rateOf($a)['base_price'] : null; $pb = $rateOf($b) ? (float) $rateOf($b)['base_price'] : null;
            if ($pa === null || $pb === null) return ($pa === null) <=> ($pb === null);
            return $sort === 'price-asc' ? $pa <=> $pb : $pb <=> $pa;
        case 'duration-asc': return array($a['days'], $a['title']) <=> array($b['days'], $b['title']);
        case 'duration-desc': return array($b['days'], $a['title']) <=> array($a['days'], $b['title']);
        case 'name': return strcasecmp($a['title'], $b['title']);
        default:
            // Recommended: internal priority rank (1–500) first, search-featured next,
            // then complete data (itinerary, inclusions, no data warnings), then shorter trips.
            $ca = hg_curation($a['slug']); $cb = hg_curation($b['slug']);
            $rk = function ($c) { return $c['priority_rank'] ?: 1000; };
            $q = function ($p) { return (empty($p['warnings']) ? 0 : 2) + ($p['itinerary'] ? 0 : 1) + ($p['inclusions'] ? 0 : 1); };
            return array($rk($ca), $ca['search_featured'] ? 0 : 1, $q($a), $a['days']) <=> array($rk($cb), $cb['search_featured'] ? 0 : 1, $q($b), $b['days']);
    }
});

/* ---------- Paging ---------- */
$perPage = 8;
$total = count($results);
$pages = max(1, (int) ceil($total / $perPage));
$page = isset($_GET['page']) ? max(1, min($pages, (int) $_GET['page'])) : 1;
$shown = array_slice($results, ($page - 1) * $perPage, $perPage);

/* ---------- URLs ---------- */
$base = $destKey !== '' ? '/tours/' . $destKey : '/tours';
$current = array();
foreach (array('destination', 'date', 'adults', 'children', 'departure', 'sort') as $k) {
    if (isset($_GET[$k]) && is_string($_GET[$k]) && $_GET[$k] !== '') $current[$k] = $_GET[$k];
}
foreach (array('place', 'dur', 'feat', 'hotel', 'meal', 'budget') as $k) {
    if ($sel[$k]) $current[$k] = $sel[$k];
}
if ($destKey !== '') unset($current['destination']);
$url = function (array $change = array(), array $remove = array()) use ($current, $base) {
    $q = array_merge($current, $change);
    foreach ($remove as $r) {
        if (is_array($r)) {
            list($k, $v) = $r;
            if (isset($q[$k])) { $q[$k] = array_values(array_diff((array) $q[$k], array($v))); if (!$q[$k]) unset($q[$k]); }
        } else {
            unset($q[$r]);
        }
    }
    unset($q['page']);
    $qs = hg_qs($q);
    return $base . ($qs ? '?' . $qs : '');
};
$pageUrl = function ($n) use ($url) { $u = $url(); return $n > 1 ? $u . (strpos($u, '?') ? '&' : '?') . 'page=' . $n : $u; };
$context = hg_context_query();
$hasParams = !empty($_GET) && array_diff(array_keys($_GET), array('dest'));

/* ---------- Page meta ---------- */
$name = $group ? $group['name'] : '';
if ($group) {
    $h1 = $name . ' Tour Packages';
    $title = $name . ' Tour Packages | Holiday Guru Travel';
    $desc = $content ? mb_substr($content['intro'], 0, 155) : 'Compare ' . $name . ' holiday packages with day-by-day itineraries, inclusions and exclusions. Enquire for the latest price.';
} elseif ($destKey !== '') {
    $h1 = 'Destination not found';
    $title = 'Destination not found | Holiday Guru Travel';
    $desc = 'The destination you searched for is not available.';
} else {
    $h1 = $textQuery !== '' ? 'Holiday packages matching “' . $textQuery . '”' : 'Explore holiday packages';
    $title = 'Search holiday packages | Holiday Guru Travel';
    $desc = 'Search holiday packages across India and abroad by destination, duration and departure city.';
}
$indexable = $group && $content && in_array($content['status'], array('review', 'approved'), true) && !$hasParams && !$loadError;
$crumbs = array(array('Home', '/'));
if ($group) {
    $crumbs[] = $group['region'] === 'india' ? array('Domestic', '/domestic-holidays') : array('International', '/international-holidays');
}
$crumbs[] = array($group ? $h1 : ($destKey !== '' ? 'Not found' : 'Holiday packages'), null);

$schema = array();
if ($group && $shown) {
    $items = array();
    foreach ($shown as $i => $p) {
        $items[] = array('@type' => 'ListItem', 'position' => $i + 1, 'url' => hg_abs($p['url']), 'name' => $p['title']);
    }
    $schema[] = array('@type' => 'ItemList', 'name' => $h1, 'itemListElement' => $items);
}
if ($content) {
    $schema[] = array('@type' => 'TouristDestination', 'name' => $name, 'description' => $content['intro'], 'url' => hg_abs($base));
    $schema[] = hg_faq_schema($content['faqs']);
}

hg_layout_start(array(
    'title' => $title, 'description' => $desc, 'path' => $base, 'index' => $indexable,
    'image' => $content ? $content['image'] : '', 'breadcrumbs' => $crumbs, 'schema' => $schema,
    'search_destination' => $group ? $group['name'] : '',
));

/* ---------- Render helpers ---------- */
$resultCard = function ($p) use ($context) {
    $href = $p['url'] . $context;
    $places = $p['places'] ? implode(' • ', $p['places']) : hg_cities($p);
    $badge = in_array('Helicopter option', $p['features'], true) ? 'Helicopter option' : (in_array('Houseboat stay', $p['features'], true) ? 'Houseboat stay' : ($p['departure'] ? 'From ' . $p['departure'] : ''));
    $facts = array();
    if ($p['hotel']) $facts[] = array('bed', $p['hotel'] . ' hotels');
    if (in_array('Houseboat stay', $p['features'], true)) $facts[] = array('bed', 'Houseboat night');
    if ($p['meals']) $facts[] = array('check', $p['meals']);
    if ($p['transfers']) $facts[] = array('car', $p['transfers'] . ' transfers');
    if (in_array('Helicopter option', $p['features'], true)) $facts[] = array('plane', 'Helicopter option');
    $facts[] = array('route', 'Customizable itinerary');
    ob_start(); ?>
<article class="hg-rcard">
    <div class="hg-rcard__media">
        <a class="hg-frame" href="<?= hg_e($href) ?>" tabindex="-1" aria-hidden="true"><?= hg_img($p['image'], '', 480, 340, 'hg-rcard__img', false, '(max-width: 767px) 100vw, 360px') ?></a>
        <?php if ($badge) { ?><span class="hg-rcard__badge"><?= hg_e($badge) ?></span><?php } ?>
        <button type="button" class="hg-save" data-hg-save="<?= hg_e($p['slug']) ?>" aria-pressed="false" aria-label="Save <?= hg_e($p['title']) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.3C1 8.3 3.2 4.5 7 4.5c2 0 3.4 1.1 5 3 1.6-1.9 3-3 5-3 3.8 0 6 3.8 4.6 7.2C19.5 16.4 12 21 12 21z"/></svg></button>
    </div>
    <div class="hg-rcard__body">
        <h3 class="hg-rcard__title"><a href="<?= hg_e($href) ?>"><?= hg_e($p['title']) ?></a></h3>
        <p class="hg-rcard__meta"><strong><?= hg_e($p['duration']) ?></strong><?php if ($places) { ?><span aria-hidden="true"> · </span><?= hg_e($places) ?><?php } ?></p>
        <ul class="hg-rcard__facts">
            <?php foreach (array_slice($facts, 0, 4) as $f) { ?><li><?= hg_icon($f[0]) ?><?= hg_e($f[1]) ?></li><?php } ?>
        </ul>
    </div>
    <div class="hg-rcard__aside">
        <?php $rate = hg_current_rate($p['slug']); if ($rate) { ?>
        <p class="hg-rcard__price"><span>Starting from</span><strong><?= hg_e(hg_rate_label($rate)) ?></strong><small>Rate valid until <?= hg_e(date('j M Y', strtotime($rate['rate_valid_until']))) ?></small></p>
        <?php } else { ?>
        <p class="hg-rcard__price"><span>Price</span><strong>On request</strong><small>Quoted for your dates &amp; group</small></p>
        <?php } ?>
        <a class="hg-btn hg-btn--primary hg-btn--sm" href="<?= hg_e($p['url'] . hg_context_query() . '#enquire') ?>" data-hg-track="enquiry_start">Enquire now</a>
        <a class="hg-btn hg-btn--outline hg-btn--sm" href="<?= hg_e($href) ?>">View details</a>
    </div>
</article>
<?php
    return ob_get_clean();
};
$check = function ($name, $value, $label, $count, $checked) {
    $id = 'f-' . $name . '-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($value));
    return '<li><input type="checkbox" id="' . hg_e($id) . '" name="' . hg_e($name) . '[]" value="' . hg_e($value) . '"' . ($checked ? ' checked' : '') . ($count ? '' : ' disabled') . '>'
        . '<label for="' . hg_e($id) . '">' . hg_e($label) . '</label></li>';
};
?>

<section class="hg-dhero<?= $content ? '' : ' hg-dhero--plain' ?>" aria-labelledby="page-title">
    <?php if ($content) { ?><div class="hg-dhero__bg hg-frame hg-frame--hero hg-frame--dark"><?= hg_img($content['image'], '', 1600, 500, '', true, '100vw') ?></div><?php } ?>
    <div class="hg-container hg-dhero__inner">
        <div>
            <h1 class="hg-h1" id="page-title"><?= hg_e($h1) ?></h1>
            <?php if ($content) { ?>
            <p class="hg-dhero__intro"><?= hg_e($content['intro']) ?></p>
            <?php } elseif ($group) { ?>
            <p class="hg-dhero__intro">Handpicked <?= hg_e($name) ?> itineraries with day-by-day plans. For destination advice, see <a href="<?= hg_e($group['hub_url']) ?>"><?= hg_e($name) ?> holidays</a> or talk to our team.</p>
            <?php } elseif ($destKey === '') { ?>
            <p class="hg-dhero__intro">Explore handpicked holidays across India and abroad. Every tour shows its full day-by-day plan.</p>
            <?php } ?>
        </div>
        <?php if ($group) { ?>
        <ul class="hg-dhero__values" aria-label="What you get">
            <li><?= hg_icon('route') ?><span>Day-by-day itineraries</span></li>
            <li><?= hg_icon('check') ?><span>Customizable plans</span></li>
            <li><?= hg_icon('whatsapp') ?><span>24×7 WhatsApp support</span></li>
            <li><?= hg_icon('pin') ?><span>Office in Noida</span></li>
        </ul>
        <?php } ?>
    </div>
</section>

<?php if ($destKey !== '' && !$group) { ?>
<section class="hg-section">
    <div class="hg-container">
        <div class="hg-empty">
            <h2 class="hg-h2">We don’t have packages for that destination yet</h2>
            <p>Try one of our destinations below, or tell us where you want to go and we will plan it for you.</p>
            <p><a class="hg-btn hg-btn--primary" href="/customized-holidays">Plan a customized holiday</a></p>
        </div>
        <div class="hg-grid hg-grid--dest" style="margin-top:32px"><?php foreach (hg_groups() as $g) { if ($g['count']) echo hg_destination_card($g); } ?></div>
    </div>
</section>
<?php hg_layout_end(); return; } ?>

<div class="hg-mbar" data-hg-mbar>
    <div class="hg-container hg-mbar__inner">
        <button type="button" class="hg-mbar__search" data-hg-search-open aria-controls="hg-header-search">
            <?= hg_icon('search') ?>
            <span><strong><?= hg_e($group ? $name : ($textQuery ?: 'All destinations')) ?></strong>
            <small><?= hg_e(($S['date'] ? hg_date_label($S['date']) : 'Any date') . ' · ' . $S['adults'] . ' Adult' . ($S['adults'] > 1 ? 's' : '') . ($S['children'] ? ', ' . $S['children'] . ' Child' . ($S['children'] > 1 ? 'ren' : '') : '') . ($S['departure'] ? ' · From ' . ucfirst($S['departure']) : '')) ?></small></span>
            <span class="hg-mbar__edit">Edit</span>
        </button>
    </div>
</div>

<section class="hg-results" data-hg-results-root>
    <div class="hg-container hg-results__grid">
        <aside class="hg-filters-panel" id="hg-filters" aria-labelledby="filters-title" data-hg-sheet>
            <form class="hg-filterform" action="<?= hg_e($base) ?>" method="get" data-hg-filterform>
                <div class="hg-filterform__head">
                    <h2 id="filters-title">Filter your package</h2>
                    <button type="button" class="hg-iconbtn hg-sheet-close" data-hg-sheet-close><?= hg_icon('close') ?><span class="hg-sr">Close filters</span></button>
                </div>
                <?php foreach (array('destination', 'date', 'adults', 'children', 'sort') as $k) { if (isset($current[$k]) && !is_array($current[$k])) { ?><input type="hidden" name="<?= $k ?>" value="<?= hg_e($current[$k]) ?>"><?php } } ?>

                <?php if (count($facet['place']) > 1) { ?>
                <fieldset class="hg-facet"><legend>Places covered</legend><ul>
                    <?php foreach ($facet['place'] as $pl => $c) echo $check('place', $slug($pl), $pl, $c, in_array($slug($pl), $sel['place'], true)); ?>
                </ul></fieldset>
                <?php } ?>

                <fieldset class="hg-facet"><legend>Duration</legend><ul>
                    <?php foreach ($durBuckets as $b => $d) echo $check('dur', $b, $d[2], isset($facet['dur'][$b]) ? $facet['dur'][$b] : 0, in_array($b, $sel['dur'], true)); ?>
                </ul></fieldset>

                <?php if ($facet['feat']) { ?>
                <fieldset class="hg-facet"><legend>Package features</legend><ul>
                    <?php foreach ($facet['feat'] as $f => $c) echo $check('feat', $slug($f), $f, $c, in_array($slug($f), $sel['feat'], true)); ?>
                </ul></fieldset>
                <?php } ?>

                <?php if (count($facet['hotel']) + count($sel['hotel']) > 1) { ?>
                <fieldset class="hg-facet"><legend>Hotel category</legend><ul>
                    <?php foreach ($facet['hotel'] as $h => $c) echo $check('hotel', $slug($h), $h, $c, in_array($slug($h), $sel['hotel'], true)); ?>
                </ul></fieldset>
                <?php } ?>

                <?php if (count($facet['meal']) + count($sel['meal']) > 1) { ?>
                <fieldset class="hg-facet"><legend>Meal plan</legend><ul>
                    <?php foreach ($facet['meal'] as $m => $c) echo $check('meal', $slug($m), $m, $c, in_array($slug($m), $sel['meal'], true)); ?>
                </ul></fieldset>
                <?php } ?>

                <?php if ($facet['budget'] || $sel['budget']) { ?>
                <fieldset class="hg-facet"><legend>Budget per person</legend><ul>
                    <?php foreach ($budgetBuckets as $b => $d) echo $check('budget', $b, $d[2], isset($facet['budget'][$b]) ? $facet['budget'][$b] : 0, in_array($b, $sel['budget'], true)); ?>
                </ul></fieldset>
                <?php } ?>

                <fieldset class="hg-facet"><legend>Departure city</legend>
                <?php if (!$facet['fixed_departure']) { ?><p class="hg-facet__note" style="margin:0 0 8px !important">These packages start at the destination; we quote travel from your city separately.</p><?php } ?>
                <ul class="hg-facet__radios">
                    <li><input type="radio" id="f-dep-any" name="departure" value=""<?= $S['departure'] === '' ? ' checked' : '' ?>><label for="f-dep-any">Any / starts at destination</label></li>
                    <?php foreach (array('delhi' => 'Delhi', 'haridwar' => 'Haridwar') as $v => $l) { $c = isset($facet['departure'][$l]) ? $facet['departure'][$l] : 0; ?>
                    <li><input type="radio" id="f-dep-<?= $v ?>" name="departure" value="<?= $v ?>"<?= $S['departure'] === $v ? ' checked' : '' ?><?= $c || $S['departure'] === $v ? '' : ' disabled' ?>><label for="f-dep-<?= $v ?>"><?= $l ?></label></li>
                    <?php } ?>
                </ul></fieldset>

                <?php if (!$facet['budget']) { ?><p class="hg-facet__note">Budget filters and price sorting appear once package rates are published. Prices today are quoted per enquiry.</p><?php } ?>
                <div class="hg-filterform__actions">
                    <button class="hg-btn hg-btn--primary" type="submit">Apply filters</button>
                    <a class="hg-btn hg-btn--ghost" href="<?= hg_e($url(array(), array('place', 'dur', 'feat', 'hotel', 'meal', 'budget', 'departure', 'sort'))) ?>">Clear all</a>
                </div>
            </form>
            <div class="hg-helpcard">
                <p><strong>Need help choosing?</strong> Talk to our travel expert</p>
                <a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_icon('phone') ?> <?= hg_e(HG_PHONE_DISPLAY) ?></a>
            </div>
        </aside>

        <div class="hg-results__main">
            <div class="hg-results__bar">
                <p class="hg-results__count" role="status" aria-live="polite"><strong><?= hg_e($group ? 'Recommended ' . $name . ' tours' : ($textQuery !== '' ? 'Search results' : 'Explore tours')) ?></strong><?php $note = array_filter(array($sort !== 'recommended' ? 'Sorted by ' . lcfirst($sorts[$sort]) : '', $page > 1 ? 'page ' . (int) $page : '')); if ($shown && $note) { ?> <span class="hg-results__sortnote">· <?= hg_e(implode(' · ', $note)) ?></span><?php } ?></p>
                <div class="hg-results__tools">
                    <button type="button" class="hg-btn hg-btn--outline hg-btn--sm hg-results__filterbtn" data-hg-sheet-open aria-controls="hg-filters"><?= hg_icon('menu') ?> Filter &amp; sort</button>
                    <form class="hg-sortform" action="<?= hg_e($base) ?>" method="get" data-hg-sortform>
                        <?php foreach ($current as $k => $v) { if ($k === 'sort') continue; foreach ((array) $v as $vv) { ?><input type="hidden" name="<?= hg_e($k) ?><?= is_array($v) ? '[]' : '' ?>" value="<?= hg_e($vv) ?>"><?php } } ?>
                        <label for="sort">Sort by</label>
                        <select id="sort" name="sort"><?php foreach ($sorts as $k => $l) { ?><option value="<?= $k ?>"<?= $k === $sort ? ' selected' : '' ?>><?= hg_e($l) ?></option><?php } ?></select>
                        <noscript><button class="hg-btn hg-btn--sm hg-btn--outline" type="submit">Sort</button></noscript>
                    </form>
                </div>
            </div>

            <?php
            $chips = array();
            foreach ($sel['place'] as $v) $chips[] = array(ucwords(str_replace('-', ' ', $v)), $url(array(), array(array('place', $v))));
            foreach ($sel['dur'] as $v) $chips[] = array($durBuckets[$v][2], $url(array(), array(array('dur', $v))));
            foreach ($sel['feat'] as $v) $chips[] = array(ucwords(str_replace('-', ' ', $v)), $url(array(), array(array('feat', $v))));
            foreach ($sel['hotel'] as $v) $chips[] = array(ucwords(str_replace('-', ' ', $v)) . ' hotels', $url(array(), array(array('hotel', $v))));
            foreach ($sel['meal'] as $v) $chips[] = array(ucfirst(str_replace('-', ' ', $v)), $url(array(), array(array('meal', $v))));
            foreach ($sel['budget'] as $v) if (isset($budgetBuckets[$v])) $chips[] = array($budgetBuckets[$v][2], $url(array(), array(array('budget', $v))));
            if ($S['departure']) $chips[] = array('From ' . ucfirst($S['departure']), $url(array(), array('departure')));
            if ($S['date']) $chips[] = array('Date: ' . hg_date_label($S['date']), $url(array(), array('date')));
            if ($chips) { ?>
            <ul class="hg-activechips" aria-label="Active filters">
                <?php foreach ($chips as $c) { ?><li><a href="<?= hg_e($c[1]) ?>" aria-label="Remove filter: <?= hg_e($c[0]) ?>"><?= hg_e($c[0]) ?> <span aria-hidden="true">×</span></a></li><?php } ?>
                <li><a class="hg-activechips__clear" href="<?= hg_e($url(array(), array('place', 'dur', 'feat', 'hotel', 'meal', 'budget', 'departure', 'date', 'sort'))) ?>">Clear all</a></li>
            </ul>
            <?php } ?>

            <?php if ($loadError) { ?>
            <div class="hg-empty" role="alert">
                <h2 class="hg-h3">We couldn’t load packages right now</h2>
                <p>Please try again in a moment, or talk to a travel expert.</p>
                <p><a class="hg-btn hg-btn--primary" href="<?= hg_e($url()) ?>">Try again</a> <a class="hg-btn hg-btn--outline" href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener">WhatsApp an expert</a></p>
            </div>
            <?php } elseif (!$shown) { ?>
            <div class="hg-empty">
                <h2 class="hg-h3">No holiday packages match your current filters</h2>
                <p>Remove a filter, or let us plan a trip around your dates.</p>
                <p><a class="hg-btn hg-btn--outline" href="<?= hg_e($url(array(), array('place', 'dur', 'feat', 'hotel', 'meal', 'budget', 'departure', 'sort'))) ?>">Clear filters</a> <a class="hg-btn hg-btn--primary" href="/customized-holidays<?= hg_e(hg_context_query($name ? array('destination' => $name) : array())) ?>">Plan a customized holiday</a></p>
            </div>
            <?php if ($scope) { ?>
            <h2 class="hg-h3" style="margin-top:32px">Recommended <?= hg_e($name) ?> packages</h2>
            <div class="hg-rlist"><?php foreach (array_slice($scope, 0, 3) as $p) echo $resultCard($p); ?></div>
            <?php } ?>
            <?php } else { ?>
            <div class="hg-rlist">
                <?php foreach ($shown as $p) echo $resultCard($p); ?>
            </div>
            <?php if ($pages > 1) { ?>
            <nav class="hg-pager" aria-label="Results pages">
                <?php if ($page > 1) { ?><a href="<?= hg_e($pageUrl($page - 1)) ?>" rel="prev">&larr; Previous</a><?php } ?>
                <span class="hg-pager__current" aria-current="page">Page <?= (int) $page ?></span>
                <?php if ($page < $pages) { ?><a href="<?= hg_e($pageUrl($page + 1)) ?>" rel="next">More tours &rarr;</a><?php } ?>
            </nav>
            <?php } ?>
            <?php } ?>
        </div>

        <aside class="hg-results__rail" aria-label="<?= hg_e($name ? 'About ' . $name : 'Help') ?>">
            <?php if ($content) { ?>
            <div class="hg-railcard">
                <h2 class="hg-railcard__title">Explore <?= hg_e($name) ?> on the map</h2>
                <ul class="hg-routelist"><?php foreach (array_slice($content['places'], 0, 5) as $pl) { ?><li><?= hg_icon('pin') ?><?= hg_e($pl[0]) ?></li><?php } ?></ul>
                <a class="hg-btn hg-btn--navy hg-btn--sm hg-btn--block" href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode($content['map_query']) ?>" target="_blank" rel="noopener">Open in Google Maps <?= hg_icon('arrow') ?></a>
            </div>
            <div class="hg-railcard">
                <h2 class="hg-railcard__title">Why choose <?= hg_e($name) ?>?</h2>
                <ul class="hg-whylist"><?php foreach ($content['why'] as $w) { ?><li><strong><?= hg_e($w[0]) ?></strong><span><?= hg_e($w[1]) ?></span></li><?php } ?></ul>
            </div>
            <?php } ?>
            <div class="hg-railcard hg-railcard--navy">
                <h2 class="hg-railcard__title">Customize your <?= hg_e($name ?: '') ?> tour</h2>
                <p>Change hotels, add nights or combine destinations — a travel expert builds the itinerary with you.</p>
                <a class="hg-btn hg-btn--primary hg-btn--sm" href="/customized-holidays<?= hg_e(hg_context_query($name ? array('destination' => $name) : array())) ?>">Plan my trip <?= hg_icon('arrow') ?></a>
            </div>
            <?php if ($content) { ?>
            <div class="hg-railcard">
                <h2 class="hg-railcard__title">Frequently searched</h2>
                <ul class="hg-linklist">
                    <li><a href="#how-many-days">How many days for <?= hg_e($name) ?></a></li>
                    <li><a href="#best-time">Best time to visit <?= hg_e($name) ?></a></li>
                    <li><a href="#cost"><?= hg_e($name) ?> tour cost factors</a></li>
                    <?php foreach ($content['frequent'] as $fl) { if (hg_page_exists($fl[1])) { ?><li><a href="<?= hg_e($fl[1]) ?>"><?= hg_e($fl[0]) ?></a></li><?php } } ?>
                </ul>
            </div>
            <?php } ?>
        </aside>
    </div>
</section>

<?php if ($content) { $c = $content; ?>
<section class="hg-section hg-section--tint" aria-labelledby="plan-title">
    <div class="hg-container hg-narrow">
        <p class="hg-eyebrow">Plan your trip</p>
        <h2 class="hg-h2" id="plan-title">Plan your <?= hg_e($name) ?> trip</h2>
        <p class="hg-summary"><strong>In short:</strong> <?= hg_e($c['days_answer']) ?> <?= hg_e($c['best_time_answer']) ?></p>

        <section class="hg-answer" id="how-many-days" aria-labelledby="q-days">
            <h3 class="hg-h3" id="q-days">How many days are enough for <?= hg_e($name) ?>?</h3>
            <p class="hg-answer__direct"><?= hg_e($c['days_answer']) ?></p>
            <div class="hg-tablewrap" tabindex="0" role="region" aria-label="Table"><table class="hg-table"><thead><tr><th scope="col">Trip length</th><th scope="col">What it covers</th><th scope="col">Itinerary</th></tr></thead><tbody>
            <?php foreach ($c['days_rows'] as $r) { $pp = hg_package($r[2]); ?><tr><td><?= hg_e($r[0]) ?></td><td><?= hg_e($r[1]) ?></td><td><?php if ($pp) { ?><a href="<?= hg_e($pp['url'] . $context) ?>"><?= hg_e($pp['title']) ?></a><?php } ?></td></tr><?php } ?>
            </tbody></table></div>
        </section>

        <section class="hg-answer" id="best-time" aria-labelledby="q-best">
            <h3 class="hg-h3" id="q-best">What is the best time to visit <?= hg_e($name) ?>?</h3>
            <p class="hg-answer__direct"><?= hg_e($c['best_time_answer']) ?></p>
            <div class="hg-tablewrap" tabindex="0" role="region" aria-label="Table"><table class="hg-table"><thead><tr><th scope="col">Months</th><th scope="col">Season</th><th scope="col">What to expect</th></tr></thead><tbody>
            <?php foreach ($c['best_time'] as $r) { ?><tr><td><?= hg_e($r[0]) ?></td><td><?= hg_e($r[1]) ?></td><td><?= hg_e($r[2]) ?></td></tr><?php } ?>
            </tbody></table></div>
        </section>

        <section class="hg-answer" id="cost" aria-labelledby="q-cost">
            <h3 class="hg-h3" id="q-cost">How much does a <?= hg_e($name) ?> tour cost?</h3>
            <p class="hg-answer__direct"><?= hg_e($c['cost_answer']) ?></p>
            <ul class="hg-checks hg-checks--info"><?php foreach ($c['cost_factors'] as $f) { ?><li><?= hg_e($f) ?></li><?php } ?></ul>
            <p style="margin-top:16px"><a class="hg-btn hg-btn--primary hg-btn--sm" href="/customized-holidays<?= hg_e(hg_context_query(array('destination' => $name))) ?>">Get a quote for your dates</a></p>
        </section>

        <section class="hg-answer" id="places" aria-labelledby="q-places">
            <h3 class="hg-h3" id="q-places">Which places do <?= hg_e($name) ?> packages cover?</h3>
            <div class="hg-places"><?php foreach ($c['places'] as $pl) { ?><div class="hg-place"><h4 class="hg-h3" style="font-size:18px"><?= hg_e($pl[0]) ?></h4><p><?= hg_e($pl[1]) ?></p></div><?php } ?></div>
        </section>

        <section class="hg-answer" aria-labelledby="q-things">
            <h3 class="hg-h3" id="q-things">Things to do in <?= hg_e($name) ?></h3>
            <ul class="hg-checks"><?php foreach ($c['things'] as $t) { ?><li><?= hg_e($t) ?></li><?php } ?></ul>
        </section>

        <section class="hg-answer" aria-labelledby="q-stay">
            <h3 class="hg-h3" id="q-stay">Hotels, houseboats and transport</h3>
            <div class="hg-prose"><p><?= hg_e($c['stay']) ?></p><p><?= hg_e($c['transport']) ?></p></div>
        </section>

        <?php $who = !empty($c['who']) ? $c['who'] : array(array('Families', $c['family']), array('Couples', $c['honeymoon']), array('Adventure', $c['adventure'])); ?>
        <section class="hg-answer" aria-labelledby="q-who">
            <h3 class="hg-h3" id="q-who"><?= hg_e(!empty($c['who_title']) ? $c['who_title'] : 'Family, honeymoon and adventure trips') ?></h3>
            <div class="hg-grid hg-grid--3">
                <?php foreach ($who as $wv) { ?><div class="hg-card"><h4 class="hg-h3" style="font-size:17px"><?= hg_e($wv[0]) ?></h4><p><?= hg_e($wv[1]) ?></p></div><?php } ?>
            </div>
        </section>

        <?= hg_tip('Travel tips from our team', '<ul>' . implode('', array_map(function ($t) { return '<li>' . hg_e($t) . '</li>'; }, $c['tips'])) . '</ul>') ?>

        <?php if (!empty($c['more_html'])) { ?><p><?= $c['more_html'] ?></p><?php } ?>
    </div>
</section>

<section class="hg-section" aria-labelledby="faq-title">
    <div class="hg-container hg-narrow">
        <h2 class="hg-h2" id="faq-title">Frequently asked questions</h2>
        <?= hg_faq($c['faqs']) ?>
        <p class="hg-muted" style="margin-top:16px;font-size:14px">Content reviewed <?= hg_e(hg_date_label($c['reviewed'])) ?> by Holiday Guru Travel.</p>
    </div>
</section>

<section class="hg-section hg-section--tint" aria-labelledby="related-title">
    <div class="hg-container">
        <?= hg_section_head('Related destinations', 'You may also like', '', null, 'related-title') ?>
        <div class="hg-grid hg-grid--dest"><?php foreach ($c['related'] as $k) { $g = hg_group($k); if ($g && $g['count']) echo hg_destination_card($g); } ?></div>
    </div>
</section>
<?php } ?>

<div class="hg-bottombar" aria-label="Quick contact">
    <a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_icon('phone') ?>Call</a>
    <a href="<?= hg_e(hg_whatsapp_href($name ? "Hi Holiday Guru Travel,\nI am looking for a " . $name . " holiday package." : '')) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp') ?>WhatsApp</a>
    <a class="is-primary" href="/customized-holidays<?= hg_e(hg_context_query($name ? array('destination' => $name) : array())) ?>"><?= hg_icon('mail') ?>Enquire</a>
</div>

<?php hg_layout_end(); ?>
