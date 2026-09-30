<?php
/**
 * Package detail page.
 *
 * Usage (from a package page, e.g. /srinagar-gulmarg-pahalgam-tour-package-5-days):
 *     require __DIR__ . '/include/templates/package-detail.php';
 *     hg_render_package('srinagar-gulmarg-pahalgam-tour-package-5-days');
 *
 * Everything shown comes from the package's own page data (include/data/packages.json)
 * and, for destination context, include/content/{destination}.php.
 * Package ID, rate and Pay Now come from include/package_registry.php:
 *   - Package ID shows only in the itinerary header, once approved (docs/package-registry/PACKAGE-ID-MAPPING.md)
 *   - the rate shows only while an approved rate version is valid; otherwise "Price on request"
 *   - Pay Now is active only with a current rate AND a connected payment gateway
 */
require_once __DIR__ . '/../ui/core.php';

if (!function_exists('hg_render_package')) {

    /** Split "Day 3 : (Gulmarg)Srinagar to Gulmarg 56 Kms" into day, overnight place and heading. */
    function hg_itinerary_day(array $d, $i, array $places)
    {
        $title = trim($d['title']);
        $stay = '';
        $heading = trim(preg_replace('/^Day\s*\d+\s*[:|\-–]?\s*/iu', '', $title));
        // "(Srinagar)Arrival…": a leading bracket without digits names the overnight place.
        // Brackets with digits ("(3hrs/4-5kms)", "( 56 Kms )") are details and stay in the heading.
        if (preg_match('/^\(([^)0-9]+)\)\s*/u', $heading, $m)) {
            $stay = trim($m[1]);
            $heading = trim(substr($heading, strlen($m[0])));
            foreach ($places as $pl) {   // "Pahalga" (typo in source) → "Pahalgam"
                if (stripos($pl, $stay) === 0) { $stay = $pl; break; }
            }
        }
        $heading = preg_replace('/(\d)\s*Kms\b/i', '$1 km', $heading);
        return array('n' => $i + 1, 'heading' => $heading !== '' ? $heading : 'Day ' . ($i + 1), 'stay' => $stay, 'text' => trim($d['text']));
    }

    /** External travel (tickets) included in this package, from its own inclusions. */
    function hg_external_travel(array $p)
    {
        $found = array();
        foreach ($p['inclusions'] as $line) {
            $l = strtolower($line);
            if (preg_match('/pick ?up|drop|transfer|stand|except|we required|\btax|cancell?ation|incidental/', $l)) continue;
            // First matching line per kind wins (e.g. "Economy Class Airfare (Delhi - Dubai - Delhi)").
            if (!isset($found['Airfare']) && preg_match('/air ?fare|flights?\b|economy class/', $l)) $found['Airfare'] = $line;
            if (!isset($found['Train fare']) && preg_match('/train (ticket|fare)/', $l)) $found['Train fare'] = $line;
            if (!isset($found['Bus fare']) && preg_match('/volvo (ticket|seat)|volvo seats|bus ticket/', $l)) $found['Bus fare'] = $line;
        }
        return $found;
    }

    /** Highlights only where the itinerary or inclusions actually mention them. */
    function hg_package_highlights(array $p)
    {
        $hay = strtolower(implode(' ', array_map(function ($d) { return $d['title'] . ' ' . $d['text']; }, $p['itinerary'])) . ' ' . implode(' ', $p['inclusions']));
        $map = array(
            array('/shikara/', 'Shikara ride on Dal Lake', 'route'),
            array('/houseboat/', 'Night in a traditional houseboat', 'bed'),
            array('/mughal garden|nishat|shalimar/', 'Mughal gardens — Nishat Bagh and Shalimar Bagh', 'pin'),
            array('/gulmarg/', 'Gulmarg meadows (Gondola optional, own cost)', 'pin'),
            array('/pahalgam/', 'Pahalgam and the Lidder valley', 'pin'),
            array('/saffron|avantipur/', 'Saffron fields and Avantipur ruins en route', 'pin'),
            array('/sonmarg/', 'Sonmarg and the Thajiwas glacier', 'pin'),
            array('/vaishno|katra/', 'Mata Vaishno Devi from Katra', 'temple'),
            array('/helicopter/', 'Helicopter option', 'plane'),
            array('/pvt cab|private cab|pvt\. cab/', 'Private cab for transfers and sightseeing', 'car'),
        );
        $out = array();
        foreach ($map as $m) {
            if (preg_match($m[0], $hay)) $out[] = array($m[1], $m[2]);
        }
        return $out;
    }

    function hg_render_package($slug)
    {
        $p = hg_package($slug);
        if (!$p) {
            http_response_code(404);
            include dirname(__DIR__, 2) . '/404.php';
            return;
        }
        $S = hg_search_state();
        $g = hg_group($p['group']);
        $content = ($g && !empty($g['content'])) ? include dirname(__DIR__) . '/content/' . basename($g['content']) . '.php' : null;
        $days = array();
        foreach ($p['itinerary'] as $i => $d) $days[] = hg_itinerary_day($d, $i, $p['places']);
        $highlights = hg_package_highlights($p);
        $external = hg_external_travel($p);
        $hasContext = !empty($_GET);
        $url = $p['url'];
        $name = $p['title'] ?: $p['name'];
        $route = array();
        foreach ($days as $d) { if ($d['stay'] && end($route) !== $d['stay']) $route[] = $d['stay']; }
        // Ordered route only when the itinerary names the overnight places; otherwise
        // just list the places covered (their order is not known).
        $routeKnown = count($route) > 1;
        if (!$routeKnown) $route = $p['places'];
        $mapSuffix = ($g && $g['region'] === 'india') ? ', India' : '';

        // Where you stay: consecutive nights per place, from the itinerary's overnight places.
        $stays = array();
        foreach (array_slice($days, 0, max(0, (int) $p['nights'])) as $d) {
            if (!$d['stay']) continue;
            $last = count($stays) - 1;
            if ($last >= 0 && $stays[$last]['place'] === $d['stay']) $stays[$last]['nights']++;
            else { $stays[] = array('place' => $d['stay'], 'nights' => 1, 'from' => $d['n'], 'hb' => false); $last++; }
            if (stripos($d['text'], 'houseboat') !== false) $stays[$last]['hb'] = true;
        }
        $houseboat = in_array('Houseboat stay', $p['features'], true);
        $hbNights = 0;
        foreach ($p['inclusions'] as $i) { if (preg_match('/(\d+)\s*nights?\s*house ?boat/i', $i, $m)) $hbNights = (int) $m[1]; }
        $hbText = $hbNights ? $hbNights . ' houseboat night' . ($hbNights > 1 ? 's' : '') : 'houseboat stay';
        $similarHotel = (bool) preg_grep('/similar standard/i', $p['terms']);
        $hotelNote = 'Hotel names are confirmed with your booking.' . ($similarHotel ? ' If a listed hotel is unavailable, a hotel of similar standard is arranged.' : '');

        // Pricing facts come only from this package's own terms.
        $priceFacts = array();
        foreach (array_merge($p['exclusions'], $p['terms'], $p['booking']) as $t) {
            $t = trim(ltrim($t, '# '));
            if (preg_match('/gst|extra adult|advance|mode of payment|air\/ ?train tickets/i', $t)) $priceFacts[] = $t;
        }

        $pkgId = hg_package_id($p['slug']);
        $rate = hg_current_rate($p['slug']);
        $payNow = hg_paynow_enabled($p['slug']);
        $rateLabel = $rate ? hg_rate_label($rate) : 'Price on request';
        $rateValid = $rate ? hg_rate_validity($rate) : '';

        // Context line + contextual WhatsApp message
        $ctx = array();
        if ($S['date']) $ctx['Travel date'] = hg_date_label($S['date']);
        if ($S['has_travellers']) $ctx['Travellers'] = $S['adults'] . ' adult' . ($S['adults'] > 1 ? 's' : '') . ($S['children'] ? ', ' . $S['children'] . ' child' . ($S['children'] > 1 ? 'ren' : '') : '');
        if ($S['departure']) $ctx['Departure city'] = ucfirst($S['departure']);
        $wa = "Hi Holiday Guru Travel,\n\nI am interested in " . ($pkgId ? 'Package ID ' . $pkgId . ' – ' . $name : 'the ' . $name) . ' (' . $p['duration'] . ").\n";
        foreach ($ctx as $k => $v) $wa .= "\n" . $k . ":\n" . $v . "\n";
        $wa .= "\nPlease share the current rate, inclusions, exclusions and availability.";

        $faqs = array(
            array('What is included in the ' . $name . '?', '<ul>' . implode('', array_map(function ($i) { return '<li>' . hg_e($i) . '</li>'; }, $p['inclusions'])) . '</ul>'),
            array('Are flights or train tickets included?', $external
                ? '<p>This package includes: ' . hg_e(implode('; ', $external)) . '. Anything not listed in the inclusions is not included.</p>'
                : '<p>No. The package starts ' . ($p['departure'] ? 'from ' . hg_e($p['departure']) : ($days && $days[0]['stay'] ? 'on arrival in ' . hg_e($days[0]['stay']) : 'at the destination')) . ' and the standard package cost excludes airfare, train fare and bus fare. We can quote tickets separately.</p>'),
        );
        if ($p['hotel']) $faqs[] = array('Which hotels are used?', '<p>' . hg_e($p['hotel']) . ' category hotels' . ($houseboat ? ', with ' . $hbText : '') . '. ' . hg_e($hotelNote) . '</p>');
        foreach ($p['booking'] as $t) {
            if (stripos($t, 'advance') !== false) { $faqs[] = array('How do I book this package?', '<p>' . hg_e(ltrim($t, '# ')) . ' A booking voucher is issued once payment is received.</p>'); break; }
        }
        foreach ($p['terms'] as $t) {
            if (stripos($t, 'extra adult') !== false) { $faqs[] = array('How are extra adults and children charged?', '<p>' . hg_e($t) . '</p>'); break; }
        }
        $faqs[] = array('Can this itinerary be customized?', '<p>Yes. You can change hotels, add nights or sightseeing, or combine it with another destination. Tell us in the enquiry form or on WhatsApp.</p>');

        $similar = array_slice(array_values(array_filter(hg_packages_in($p['group']), function ($x) use ($p) { return $x['slug'] !== $p['slug']; })), 0, 3);

        $crumbs = array(array('Home', '/'));
        if ($g) {
            $crumbs[] = $g['region'] === 'india' ? array('Domestic', '/domestic-holidays') : array('International', '/international-holidays');
            $crumbs[] = array($g['name'] . ' Tour Packages', $g['hub_url']);
        }
        $crumbs[] = array($name, null);

        $trip = array(
            '@type' => 'TouristTrip', 'name' => $name, 'description' => $p['description'], 'url' => hg_abs($url),
            'image' => hg_abs($p['image']), 'touristType' => array('Leisure'),
            'provider' => array('@id' => HG_SITE_URL . '/#organization'),
        );
        // Only visible, true facts: the Package ID (shown in the itinerary) once approved, and an Offer only for a current approved rate.
        if ($pkgId && !hg_package_id_is_proposed($p['slug'])) $trip['identifier'] = array('@type' => 'PropertyValue', 'name' => 'Package ID', 'value' => $pkgId);
        if ($rate) {
            $trip['offers'] = array('@type' => 'Offer', 'price' => (string) $rate['base_price'], 'priceCurrency' => $rate['currency'],
                'validFrom' => $rate['rate_valid_from'], 'priceValidUntil' => $rate['rate_valid_until'], 'url' => hg_abs($url));
        }
        $trip += array(
            'itinerary' => array('@type' => 'ItemList', 'numberOfItems' => count($days), 'itemListElement' => array_map(function ($d) {
                return array('@type' => 'ListItem', 'position' => $d['n'], 'name' => 'Day ' . $d['n'] . ': ' . $d['heading']);
            }, $days)),
        );

        hg_layout_start(array(
            'title' => $p['page_title'] ?: $name, 'description' => $p['description'], 'path' => $url,
            'index' => !$hasContext, 'image' => $p['image'], 'breadcrumbs' => $crumbs,
            'search_destination' => $g ? $g['name'] : '',
            'schema' => array($trip, hg_faq_schema($faqs)),
        ));
        $nav = array(array('overview', 'Overview'), array('itinerary', 'Itinerary'), array('inclusions', 'Inclusions'), array('hotels', 'Hotels'), array('price', 'Price'), array('faq', 'FAQ'));
        if ($similar) $nav[] = array('similar', 'Similar packages');
        ?>
<div data-hg-package-view="<?= hg_e($p['slug']) ?>" hidden></div>

<section class="hg-pkghead" id="overview" aria-labelledby="pkg-title">
    <div class="hg-container hg-pkghead__grid">
        <div class="hg-pkghead__media hg-frame hg-frame--hero">
            <?= hg_img($p['image'], $name, 760, 480, 'hg-pkghead__img', true, '(max-width: 1023px) 100vw, 640px') ?>
        </div>
        <div class="hg-pkghead__summary">
            <?php if ($p['features']) { ?><p class="hg-pkghead__badges"><?php foreach (array_slice($p['features'], 0, 2) as $f) { ?><span><?= hg_e($f) ?></span><?php } ?></p><?php } ?>
            <h1 class="hg-pkghead__title" id="pkg-title"><?= hg_e($name) ?></h1>
            <p class="hg-pkghead__meta"><strong><?= hg_e($p['duration']) ?></strong><?php if ($route) { ?> <span aria-hidden="true">·</span> <?= hg_e(implode($routeKnown ? ' – ' : ', ', $route)) ?><?php } ?></p>
            <ul class="hg-pkghead__facts">
                <?php if ($p['hotel']) { ?><li><?= hg_icon('bed') ?><?= hg_e($p['hotel']) ?> hotels<?= $houseboat ? ' + ' . hg_e($hbText) : '' ?></li><?php } ?>
                <?php if ($p['meals']) { ?><li><?= hg_icon('meal') ?><?= hg_e($p['meals']) ?></li><?php } ?>
                <?php if ($p['transfers']) { ?><li><?= hg_icon('car') ?><?= hg_e($p['transfers']) ?> transfers &amp; sightseeing</li><?php } ?>
                <li><?= hg_icon('route') ?>Customizable itinerary</li>
            </ul>
            <p class="hg-pkghead__desc"><?= hg_e($p['description']) ?></p>
            <div class="hg-pkghead__buy">
                <p class="hg-pkghead__rate"><span><?= $rate ? 'Current rate' : 'Package price' ?></span><strong><?= hg_e($rateLabel) ?></strong><?php if ($rate) { ?><small>Rate valid: <?= hg_e($rateValid) ?></small><?php } else { ?><small>Quoted for your dates &amp; group</small><?php } ?></p>
                <div class="hg-pkghead__cta">
                    <?php if ($payNow) { ?><a class="hg-btn hg-btn--primary" href="/pay?tour=<?= hg_e($p['slug']) ?>" data-hg-track="paynow_start"><?= hg_icon('lock') ?>Pay Now</a><?php } else { ?><button type="button" class="hg-btn hg-btn--primary" disabled aria-describedby="paynow-note-top"><?= hg_icon('lock') ?>Pay Now</button><?php } ?>
                    <a class="hg-btn hg-btn--navy" href="#enquiry-form" data-hg-track="enquiry_start">Enquire Now</a>
                </div>
                <?php if (!$payNow) { ?><p class="hg-pkghead__paynote" id="paynow-note-top"><?= $rate ? 'Online payment is not available yet — enquire to book at this rate.' : 'Pay Now opens once a current rate is published and online payment is connected. Enquire for today’s price.' ?></p><?php } ?>
            </div>
            <?php if ($ctx) { ?>
            <p class="hg-pkghead__ctx"><span>Your trip:</span> <?= hg_e(implode(' · ', $ctx)) ?> <a href="#hg-header-search" data-hg-search-open>Change</a></p>
            <?php } ?>
            <div class="hg-pkghead__actions">
                <button type="button" class="hg-btn hg-btn--outline hg-btn--sm hg-save hg-save--inline" data-hg-save="<?= hg_e($p['slug']) ?>" aria-pressed="false"><?= hg_icon('heart') ?><span data-hg-save-label>Save</span></button>
                <button type="button" class="hg-btn hg-btn--outline hg-btn--sm" data-hg-share="native" data-url="<?= hg_e(hg_abs($url)) ?>" data-title="<?= hg_e($name) ?>" data-id="<?= hg_e($p['slug']) ?>"><?= hg_icon('share') ?><span data-hg-share-label>Share</span></button>
                <button type="button" class="hg-btn hg-btn--ghost hg-btn--sm" data-hg-share="copy" data-url="<?= hg_e(hg_abs($url)) ?>" data-id="<?= hg_e($p['slug']) ?>"><?= hg_icon('link') ?><span data-hg-share-label>Copy link</span></button>
                <a class="hg-btn hg-btn--ghost hg-btn--sm" href="https://wa.me/?text=<?= rawurlencode($name . ' — ' . hg_abs($url)) ?>" target="_blank" rel="noopener" data-hg-track="share"><?= hg_icon('whatsapp') ?>Send on WhatsApp</a>
                <a class="hg-btn hg-btn--ghost hg-btn--sm" href="mailto:?subject=<?= rawurlencode($name) ?>&amp;body=<?= rawurlencode(hg_abs($url)) ?>" data-hg-track="share"><?= hg_icon('mail') ?>Email</a>
            </div>
        </div>
    </div>
</section>

<?= hg_section_nav($nav, 'Package sections', '<a class="hg-btn hg-btn--primary hg-btn--sm hg-secnav__cta" href="#enquiry-form" data-hg-track="enquiry_start">Enquire now</a>') ?>

<div class="hg-container hg-pkg">
    <div class="hg-pkg__main">
        <?php if ($highlights) { ?>
        <section class="hg-pkgsec" aria-labelledby="hl-title">
            <h2 class="hg-h2" id="hl-title">Package highlights</h2>
            <ul class="hg-hl"><?php foreach ($highlights as $h) { ?><li><?= hg_icon($h[1]) ?><?= hg_e($h[0]) ?></li><?php } ?></ul>
        </section>
        <?php } ?>

        <section class="hg-pkgsec" aria-labelledby="qf-title">
            <h2 class="hg-h2" id="qf-title">Quick facts</h2>
            <dl class="hg-qf">
                <div><dt>Duration</dt><dd><?= hg_e($p['duration']) ?></dd></div>
                <div><dt>Places</dt><dd><?= hg_e(implode(', ', $p['places'])) ?></dd></div>
                <?php if ($days && $days[0]['stay']) { ?><div><dt>Starts / ends</dt><dd><?= hg_e($days[0]['stay']) ?> / <?= hg_e(end($days)['stay'] ?: $days[0]['stay']) ?></dd></div><?php } ?>
                <?php if ($p['hotel']) { ?><div><dt>Stay</dt><dd><?= hg_e($p['hotel']) ?> category, twin sharing<?= $houseboat ? '; ' . hg_e($hbText) : '' ?></dd></div><?php } ?>
                <?php if ($p['meals']) { ?><div><dt>Meals</dt><dd><?= hg_e($p['meals']) ?></dd></div><?php } ?>
                <?php if ($p['transfers']) { ?><div><dt>Transport</dt><dd><?= hg_e($p['transfers']) ?></dd></div><?php } ?>
                <?php if ($content) { ?><div><dt>Best time</dt><dd><?= hg_e($content['best_time_answer']) ?></dd></div><?php } ?>
                <div><dt>Customizable</dt><dd>Yes</dd></div>
            </dl>
        </section>

        <section class="hg-pkgsec" id="itinerary" aria-labelledby="it-title">
            <h2 class="hg-h2" id="it-title">Day-wise itinerary</h2>
            <dl class="hg-ithead" aria-label="Tour reference">
                <?php if ($pkgId) { ?><div class="hg-ithead__id"><dt>Package ID</dt><dd><?= hg_e($pkgId) ?><?= hg_package_id_is_proposed($p['slug']) ? ' <span class="hg-pkgid__flag">Proposed · preview only</span>' : '' ?></dd></div><?php } ?>
                <div class="hg-ithead__tour"><dt>Tour</dt><dd><?= hg_e($name) ?></dd></div>
                <div><dt>Duration</dt><dd><?= hg_e($p['duration']) ?></dd></div>
                <div><dt>Destination</dt><dd><?= hg_e($g ? $g['name'] : implode(', ', $p['places'])) ?></dd></div>
                <div><dt><?= $rate ? 'Current rate' : 'Price' ?></dt><dd><?= hg_e($rateLabel) ?></dd></div>
                <?php if ($rate) { ?><div><dt>Rate valid</dt><dd><?= hg_e($rateValid) ?></dd></div><?php } ?>
            </dl>
            <?php if ($days) { ?>
            <ol class="hg-timeline hg-timeline--pkg">
                <?php foreach ($days as $d) { ?>
                <li>
                    <span class="hg-timeline__day" aria-hidden="true"><?= (int) $d['n'] ?></span>
                    <h3><span class="hg-sr">Day <?= (int) $d['n'] ?>: </span><?= hg_e($d['heading']) ?></h3>
                    <?php if ($d['text'] !== '') { ?><p><?= hg_e($d['text']) ?></p><?php } else { ?><p class="hg-muted"><em>Day details are confirmed with your itinerary — ask our team for the plan for this day.</em></p><?php } ?>
                    <?php if ($d['stay'] && $d['n'] <= $p['nights']) { ?><p class="hg-timeline__stay"><?= hg_icon('bed') ?>Overnight: <?= hg_e($d['stay']) ?><?= stripos($d['text'], 'overnight at houseboat') !== false ? ' (houseboat)' : '' ?></p><?php } ?>
                </li>
                <?php } ?>
            </ol>
            <?php if ($p['days'] && count($days) < $p['days']) { ?>
            <p class="hg-notice" role="note">The published plan covers <?= count($days) ?> of the <?= (int) $p['days'] ?> days. Ask us for the complete day-by-day itinerary for your dates.</p>
            <?php } ?>
            <?php } else { ?>
            <div class="hg-empty"><p>The day-by-day plan for this package is shared on request. <a href="#enquire">Ask for the itinerary</a>.</p></div>
            <?php } ?>
            <?php if ($route) { ?>
            <div class="hg-routebox">
                <?php if ($routeKnown) { $origin = $route[0]; $dest = end($route); $wp = array_slice($route, 1, -1); ?>
                <p><strong>Route:</strong> <?= hg_e(implode(' → ', $route)) ?></p>
                <a class="hg-btn hg-btn--navy hg-btn--sm" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&amp;origin=<?= rawurlencode($origin . $mapSuffix) ?>&amp;destination=<?= rawurlencode($dest . $mapSuffix) ?><?= $wp ? '&amp;waypoints=' . rawurlencode(implode('|', array_map(function ($w) use ($mapSuffix) { return $w . $mapSuffix; }, $wp))) : '' ?>">View route on Google Maps <?= hg_icon('arrow') ?></a>
                <?php } else { ?>
                <p><strong><?= count($route) > 1 ? 'Places covered' : 'Destination' ?>:</strong> <?= hg_e(implode(', ', $route)) ?></p>
                <?php if (count($route) === 1) { ?><a class="hg-btn hg-btn--navy hg-btn--sm" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode($route[0] . $mapSuffix) ?>">View on Google Maps <?= hg_icon('arrow') ?></a><?php } ?>
                <?php } ?>
            </div>
            <?php } ?>
        </section>

        <section class="hg-pkgsec" id="inclusions" aria-labelledby="inc-title">
            <h2 class="hg-h2" id="inc-title">Inclusions &amp; exclusions</h2>
            <div class="hg-incl">
                <div><h3><?= hg_icon('check') ?>Included</h3><?php if ($p['inclusions']) { ?><ul class="hg-checks"><?php foreach ($p['inclusions'] as $i) { ?><li><?= hg_e($i) ?></li><?php } ?></ul><?php } else { ?><p class="hg-muted">Shared with your quote.</p><?php } ?></div>
                <div><h3><?= hg_icon('x') ?>Not included</h3><?php if ($p['exclusions']) { ?><ul class="hg-checks hg-checks--no"><?php foreach ($p['exclusions'] as $i) { ?><li><?= hg_e($i) ?></li><?php } ?></ul><?php } else { ?><p class="hg-muted">Shared with your quote.</p><?php } ?></div>
            </div>
            <div class="hg-travelnote" role="note">
                <?= hg_icon('ticket') ?>
                <?php if (!$external && preg_match('/flight[\s-]*inclusive|with[\s-]*flights?/i', $p['slug'] . ' ' . $p['title'])) { ?>
                <p><strong>Flights:</strong> this package’s name mentions flights, but its inclusions do not list them. Flight details and fares are confirmed with your quote. Otherwise the standard package cost excludes airfare, train fare and bus fare.</p>
                <?php } elseif ($external) { ?>
                <p><strong>Travel tickets in this package:</strong> <?= hg_e(implode('; ', $external)) ?>. Any other air, train or bus travel is excluded unless listed in the inclusions.</p>
                <?php } else { ?>
                <p><strong>Standard package cost excludes airfare, train fare and bus fare</strong> unless specifically mentioned in the package inclusions. Local transfers and sightseeing listed above are included.</p>
                <?php } ?>
            </div>
            <?php if ($p['terms'] || $p['booking']) { ?>
            <details class="hg-important">
                <summary><?= hg_icon('info') ?>Important information &amp; booking terms</summary>
                <ul><?php foreach (array_merge($p['booking'], $p['terms']) as $t) { ?><li><?= hg_e(ltrim($t, '# ')) ?></li><?php } ?></ul>
            </details>
            <?php } ?>
        </section>

        <section class="hg-pkgsec" id="hotels" aria-labelledby="ho-title">
            <h2 class="hg-h2" id="ho-title">Where you stay</h2>
            <?php if ($stays) { ?>
            <div class="hg-tablewrap" tabindex="0" role="region" aria-label="Table"><table class="hg-table"><thead><tr><th scope="col">Nights</th><th scope="col">Place</th><th scope="col">Stay</th></tr></thead><tbody>
            <?php foreach ($stays as $st) { ?><tr><td><?= $st['nights'] > 1 ? 'Nights ' . $st['from'] . '–' . ($st['from'] + $st['nights'] - 1) : 'Night ' . $st['from'] ?></td><td><?= hg_e($st['place']) ?></td><td><?= $p['hotel'] ? hg_e($p['hotel']) . ' category' : 'Hotel as per your quote' ?><?= ($houseboat && $st['hb']) ? ' — includes ' . hg_e($hbText) : '' ?></td></tr><?php } ?>
            </tbody></table></div>
            <?php } ?>
            <p class="hg-muted"><?= hg_e($hotelNote) ?> Ask us to upgrade any night.</p>
        </section>

        <section class="hg-pkgsec" id="price" aria-labelledby="pr-title">
            <h2 class="hg-h2" id="pr-title">Price</h2>
            <div class="hg-pricebox">
                <div>
                    <p class="hg-pricebox__label"><?= $rate ? 'Current rate' : 'Package price' ?></p>
                    <p class="hg-pricebox__value"><?= hg_e($rateLabel) ?></p>
                    <?php if ($rate) { ?>
                    <p class="hg-muted">Rate valid: <strong><?= hg_e($rateValid) ?></strong> · Price version <?= (int) $rate['version'] ?></p>
                    <?php if (!empty($rate['price_notes'])) { ?><p class="hg-muted"><?= hg_e($rate['price_notes']) ?></p><?php } ?>
                    <?php } else { ?>
                    <p class="hg-muted">We quote this package for your dates, travellers and hotel choice. No current rate is published for this tour.</p>
                    <?php } ?>
                </div>
                <a class="hg-btn hg-btn--primary" href="#enquire" data-hg-track="enquiry_start"><?= $rate ? 'Enquire to book' : 'Get current price' ?></a>
            </div>
            <p class="hg-travelnote hg-travelnote--inline" role="note"><?= hg_icon('ticket') ?><span><strong>Standard package cost excludes airfare, train fare and bus fare</strong> unless specifically mentioned in the package inclusions.</span></p>
            <?php if ($priceFacts) { ?>
            <h3 class="hg-h3">How this package is priced</h3>
            <ul class="hg-checks hg-checks--info"><?php foreach ($priceFacts as $f) { ?><li><?= hg_e($f) ?></li><?php } ?></ul>
            <?php } ?>
        </section>

        <?php if (HG_DEV_MODE) { ?>
        <section class="hg-pkgsec" aria-label="Reviews"><?= hg_dev_note('Reviews: no verified reviews exist for this package, so none are shown and no rating appears in structured data. Connect Google reviews or a verified review source before showing ratings.') ?></section>
        <?php } ?>

        <section class="hg-pkgsec" aria-labelledby="cu-title">
            <div class="hg-custom">
                <div>
                    <h2 class="hg-h3" id="cu-title">Want this trip your way?</h2>
                    <p>Change hotels, add nights or sightseeing, or combine it with another destination.</p>
                </div>
                <a class="hg-btn hg-btn--navy" href="/customized-holidays<?= hg_e(hg_context_query(array('destination' => $g ? $g['name'] : '', 'package' => $name))) ?>">Customize this package</a>
            </div>
        </section>

        <section class="hg-pkgsec" id="faq" aria-labelledby="faq-title">
            <h2 class="hg-h2" id="faq-title">Questions about this package</h2>
            <?= hg_faq($faqs) ?>
        </section>

        <?php if ($content) { ?>
        <section class="hg-pkgsec" aria-labelledby="about-title">
            <h2 class="hg-h2" id="about-title">Planning your <?= hg_e($g['name']) ?> trip</h2>
            <p class="hg-summary"><strong>Best time:</strong> <?= hg_e($content['best_time_answer']) ?></p>
            <div class="hg-prose"><p><?= hg_e($content['transport']) ?></p></div>
            <p><a class="hg-link-arrow" href="<?= hg_e($g['hub_url']) ?>">All <?= hg_e($g['name']) ?> tour packages and travel advice <span aria-hidden="true">&rarr;</span></a></p>
        </section>
        <?php } ?>
    </div>

    <aside class="hg-pkg__side" id="enquire" aria-label="Book or enquire">
        <div class="hg-bookcard">
            <p class="hg-bookcard__label"><?= $rate ? 'Current rate' : 'Package price' ?></p>
            <p class="hg-bookcard__price"><?= hg_e($rateLabel) ?></p>
            <p class="hg-bookcard__sub"><?= $rate ? 'Rate valid: ' . hg_e($rateValid) . '.' : 'Quoted for your dates &amp; group.' ?> <a href="#price">How it’s priced</a></p>
            <p class="hg-bookcard__excl"><?= hg_icon('ticket') ?><?= $external ? 'Includes ' . hg_e(strtolower(implode(', ', array_keys($external)))) . ' as listed' : (preg_match('/flight[\s-]*inclusive|with[\s-]*flights?/i', $p['slug'] . ' ' . $p['title']) ? 'Flights: confirmed with your quote' : 'Air / train / bus fare not included') ?></p>
            <div class="hg-bookcard__actions">
                <?php if ($payNow) { ?><a class="hg-btn hg-btn--primary hg-btn--block" href="/pay?tour=<?= hg_e($p['slug']) ?>" data-hg-track="paynow_start"><?= hg_icon('lock') ?>Pay Now</a><?php } else { ?><button type="button" class="hg-btn hg-btn--primary hg-btn--block" disabled aria-describedby="paynow-note" data-hg-paynow><?= hg_icon('lock') ?>Pay Now</button><?php } ?>
                <a class="hg-btn hg-btn--navy hg-btn--block" href="#enquiry-form" data-hg-track="enquiry_start">Enquire Now</a>
            </div>
            <?php if (!$payNow) { ?><p class="hg-bookcard__note" id="paynow-note">Online payment isn’t available yet. Enquire and a travel expert will confirm the price, availability and payment options.</p><?php } ?>
            <?= hg_enquiry_form('enquiry-form', 'Plan your trip', array(
                'enquiry_type' => 'TOUR PACKAGE ENQUIRY',
                'package_id' => $pkgId,
                'internal_ref' => hg_package_key($p['slug']),
                'package' => $name,
                'package_url' => hg_abs($url),
                'destination' => $g ? $g['name'] : '',
                'displayed_rate' => $rateLabel,
                'rate_version' => $rate ? (string) $rate['version'] : '',
                'rate_validity' => $rateValid,
            ), true) ?>
            <a class="hg-btn hg-btn--wa hg-btn--block" href="<?= hg_e(hg_whatsapp_href($wa)) ?>" target="_blank" rel="noopener" data-hg-track="whatsapp_click"><?= hg_icon('whatsapp') ?>Chat on WhatsApp</a>
        </div>
        <ul class="hg-trustlist">
            <li><?= hg_icon('check') ?><span><strong>Inclusions listed</strong> Every inclusion and exclusion on this page</span></li>
            <li><?= hg_icon('route') ?><span><strong>Customizable</strong> Hotels, nights and sightseeing</span></li>
            <li><?= hg_icon('whatsapp') ?><span><strong>24×7 support</strong> Phone and WhatsApp</span></li>
            <li><?= hg_icon('shield') ?><span><strong>Operated by</strong> <?= hg_e(HG_LEGAL_NAME) ?></span></li>
        </ul>
    </aside>
</div>

<?php if ($similar) { ?>
<section class="hg-section hg-section--tint" id="similar" aria-labelledby="sim-title">
    <div class="hg-container">
        <?= hg_section_head('You may also like', 'Similar ' . ($g ? $g['name'] . ' ' : '') . 'packages', '', $g ? array('View all', $g['hub_url']) : null, 'sim-title') ?>
        <?= hg_package_grid($similar) ?>
    </div>
</section>
<?php } ?>

<div class="hg-bottombar" aria-label="Quick contact">
    <a href="<?= hg_e(hg_tel_href()) ?>" data-hg-track="call_click"><?= hg_icon('phone') ?>Call</a>
    <a href="<?= hg_e(hg_whatsapp_href($wa)) ?>" target="_blank" rel="noopener" data-hg-track="whatsapp_click"><?= hg_icon('whatsapp') ?>WhatsApp</a>
    <a class="is-primary" href="#enquiry-form" data-hg-track="enquiry_start"><?= hg_icon('mail') ?>Enquire</a>
</div>
<?php
        hg_layout_end();
    }
}
