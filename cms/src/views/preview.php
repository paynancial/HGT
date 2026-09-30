<?php
/**
 * Screen 14 — Package Preview. Reproduces the public package page body using the website's own stylesheets
 * (served read-only from the site folder) and the CURRENT saved package data. The website header and footer are
 * deliberately not included: the package module and the global shell are separate.
 */
$rate = pkg_current_rate($p);
list($payOk, $payWhy) = pkg_paynow($p);
$pid = pkg_public_id($p);
list($idShown, $idState) = pkg_id_state($p);
$dests = hg_destinations();
$dest = isset($dests[$p['destination']]) ? $dests[$p['destination']]['name'] : $p['destination'];
$featured = null; $gallery = array();
foreach ($p['media'] as $m) { if ($m['role'] === 'featured') $featured = $m; elseif ($m['role'] === 'gallery') $gallery[] = $m; }
$inc = array_filter($p['scope'], function ($s) { return $s['kind'] === 'inclusion' && $s['status'] === 'active'; });
$exc = array_filter($p['scope'], function ($s) { return $s['kind'] === 'exclusion' && $s['status'] === 'active'; });
$addons = array_filter($p['addons'], function ($a) { return $a['status'] === 'active'; });
$offers = array_filter($p['offers'], function ($o) { return $o['status'] === 'published' && $o['valid_from'] <= today() && $o['valid_until'] >= today(); });
$dayImg = array();
foreach ($p['media'] as $m) $dayImg[(int) $m['media_id']] = $m;
list($errors) = pkg_blockers($p);
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Preview · <?= e($p['seo']['meta_title'] ?: $p['name']) ?></title>
<link rel="stylesheet" href="/assets/css/hg-site.css">
<link rel="stylesheet" href="/assets/css/hg-ui.css">
<link rel="stylesheet" href="/cms-assets/cms.css?v=<?= filemtime(CMS_ROOT . '/public/cms-assets/cms.css') ?>">
</head>
<body class="hg-body cms-previewpage">
<div class="cms-previewbar" role="note">
    <strong>Package preview</strong>
    <span><?= status_pill($p['status']) ?> Version <?= (int) $p['version'] ?> · built from the saved data just now</span>
    <span class="cms-previewbar__warn"><?= $errors ? count($errors) . ' mandatory item' . (count($errors) === 1 ? '' : 's') . ' missing — not publishable yet' : 'Publishable' ?></span>
    <span>Search title: <em><?= e($p['seo']['meta_title'] ?: '—') ?></em></span>
    <a href="/packages/<?= (int) $p['package_pk'] ?>">Back to editor</a>
</div>
<p class="cms-previewbar__shell">Website header, navigation and footer are shared components and are not part of this preview.</p>
<main id="main" class="hg-main">
<section class="hg-pkghead" id="overview" aria-labelledby="pkg-title">
    <div class="hg-container hg-pkghead__grid">
        <div class="hg-pkghead__media">
            <?php if ($featured) { ?><img src="<?= e(media_url($featured, 1600)) ?>" alt="<?= e($featured['alt_text']) ?>" width="<?= (int) $featured['width'] ?>" height="<?= (int) $featured['height'] ?>"><?php } else { ?><div class="cms-noimg">No featured image</div><?php } ?>
        </div>
        <div class="hg-pkghead__summary">
            <?php if ($p['package_type'] || $p['speciality_type']) { ?><p class="hg-pkghead__badges"><?php foreach (array_unique(array_filter(array($p['package_type'], $p['speciality_type']))) as $b) { ?><span><?= e($b) ?></span><?php } ?></p><?php } ?>
            <h1 class="hg-pkghead__title" id="pkg-title"><?= e($p['name']) ?></h1>
            <p class="hg-pkghead__meta"><strong><?= (int) $p['nights'] ?> Nights / <?= (int) $p['days'] ?> Days</strong><?= $p['city_route'] ? ' <span aria-hidden="true">·</span> ' . e($p['city_route']) : '' ?></p>
            <ul class="hg-pkghead__facts"><li><?= e($dest) ?></li><?php if ($p['suitable_for']) { ?><li>Suits <?= e(implode(', ', $p['suitable_for'])) ?></li><?php } ?></ul>
            <p class="hg-pkghead__desc"><?= e($p['short_description']) ?></p>
            <div class="hg-pkghead__buy">
                <p class="hg-pkghead__rate"><span><?= $rate ? 'Current rate' : 'Package price' ?></span><strong><?= e(rate_label($rate)) ?></strong><?php if ($rate) { ?><small>Rate valid: <?= e(rate_validity($rate)) ?></small><?php } else { ?><small>Quoted for your dates &amp; group</small><?php } ?></p>
                <div class="hg-pkghead__cta">
                    <button type="button" class="hg-btn hg-btn--primary" <?= $payOk ? '' : 'disabled' ?> aria-describedby="paynote">Pay Now</button>
                    <?php if ($p['enquiry_enabled']) { ?><a class="hg-btn hg-btn--navy" href="#enquire">Enquire Now</a><?php } ?>
                </div>
                <?php if (!$payOk) { ?><p class="hg-pkghead__paynote" id="paynote"><?= $rate ? 'Online payment is not available yet — enquire to book at this rate.' : 'Pay Now opens once a current rate is published and online payment is connected. Enquire for today’s price.' ?></p><?php } ?>
            </div>
            <?php if ($offers) { ?><p class="cms-offerline"><?php foreach ($offers as $o) { ?><span><?= e($o['name']) ?> · valid to <?= e(dmy($o['valid_until'])) ?></span><?php } ?></p><?php } ?>
        </div>
    </div>
</section>

<div class="hg-container hg-pkg">
    <div class="hg-pkg__main">
        <?php if ($gallery) { ?>
        <section class="hg-pkgsec" aria-labelledby="gal-title"><h2 class="hg-h2" id="gal-title">Gallery</h2>
            <div class="cms-pgallery"><?php foreach ($gallery as $g) { ?><figure><img src="<?= e(media_url($g, 800)) ?>" alt="<?= e($g['alt_text']) ?>" loading="lazy"><?php if ($g['caption']) { ?><figcaption><?= e($g['caption']) ?><?= $g['credit'] ? ' · ' . e($g['credit']) : '' ?></figcaption><?php } ?></figure><?php } ?></div>
        </section>
        <?php } ?>

        <?php if ($p['highlights']) { ?>
        <section class="hg-pkgsec" aria-labelledby="hl-title"><h2 class="hg-h2" id="hl-title">Package highlights</h2>
            <ul class="hg-hl"><?php foreach ($p['highlights'] as $h) { ?><li><?= e($h) ?></li><?php } ?></ul>
        </section>
        <?php } ?>

        <?php if ($p['seo']['aeo_question'] && $p['seo']['aeo_answer']) { ?>
        <section class="hg-pkgsec" aria-labelledby="aeo-title"><h2 class="hg-h2" id="aeo-title"><?= e($p['seo']['aeo_question']) ?></h2>
            <p class="hg-summary"><?= e($p['seo']['aeo_answer']) ?></p>
            <?php if ($p['seo']['key_facts']) { ?><ul class="hg-checks hg-checks--info"><?php foreach ($p['seo']['key_facts'] as $f) { ?><li><?= e($f) ?></li><?php } ?></ul><?php } ?>
        </section>
        <?php } ?>

        <?php if ($p['description_html']) { ?><section class="hg-pkgsec" aria-labelledby="ov-title"><h2 class="hg-h2" id="ov-title">About this tour</h2><div class="hg-prose"><?= $p['description_html'] ?></div></section><?php } ?>

        <section class="hg-pkgsec" id="itinerary" aria-labelledby="it-title">
            <h2 class="hg-h2" id="it-title">Day-wise itinerary</h2>
            <dl class="hg-ithead" aria-label="Tour reference">
                <?php if ($pid) { ?><div class="hg-ithead__id"><dt>Package ID</dt><dd><?= e($pid) ?></dd></div><?php } elseif ($idState === 'proposed') { ?><div class="hg-ithead__id"><dt>Package ID</dt><dd><?= e($idShown) ?> <span class="hg-pkgid__flag">Proposed · preview only — not shown publicly until approved</span></dd></div><?php } ?>
                <div class="hg-ithead__tour"><dt>Tour</dt><dd><?= e($p['name']) ?></dd></div>
            </dl>
            <?php if ($p['days_list']) { ?>
            <ol class="hg-timeline hg-timeline--pkg">
                <?php foreach ($p['days_list'] as $d) { ?>
                <li>
                    <span class="hg-timeline__day" aria-hidden="true"><?= (int) $d['day_number'] ?></span>
                    <h3><span class="hg-sr">Day <?= (int) $d['day_number'] ?>: </span><?= e(preg_replace('/^Day\s*\d+\s*[:|\-–]?\s*/iu', '', $d['title'])) ?></h3>
                    <?php if ($d['route']) { ?><p class="hg-muted"><?= e($d['route']) ?></p><?php } ?>
                    <?php if ($d['description'] !== '') { ?><p><?= e($d['description']) ?></p><?php } ?>
                    <?php $facts = array_filter(array('Sightseeing' => $d['sightseeing'], 'Activities' => $d['activities'], 'Meals' => $d['meals'], 'Hotel' => $d['hotel'], 'Transport' => $d['transport'], 'Optional' => $d['optional_activities']));
                    if ($facts) { ?><ul class="cms-dayfacts"><?php foreach ($facts as $k => $v) { ?><li><strong><?= e($k) ?>:</strong> <?= e($v) ?></li><?php } ?></ul><?php } ?>
                    <?php if ($d['notes']) { ?><p class="hg-notice" role="note"><?= e($d['notes']) ?></p><?php } ?>
                    <?php if ($d['media_id'] && isset($dayImg[(int) $d['media_id']])) { $im = $dayImg[(int) $d['media_id']]; ?><img class="cms-dayimg" src="<?= e(media_url($im, 800)) ?>" alt="<?= e($im['alt_text']) ?>" loading="lazy"><?php } ?>
                </li>
                <?php } ?>
            </ol>
            <?php } else { ?><div class="hg-empty"><p>No itinerary yet.</p></div><?php } ?>
        </section>

        <section class="hg-pkgsec" id="inclusions" aria-labelledby="inc-title">
            <h2 class="hg-h2" id="inc-title">Inclusions &amp; exclusions</h2>
            <div class="hg-incl">
                <div><h3>Included</h3><?php if ($inc) { ?><ul class="hg-checks"><?php foreach ($inc as $i) { ?><li><?= e($i['name']) ?><?= $i['description'] ? ' — ' . e($i['description']) : '' ?></li><?php } ?></ul><?php } else { ?><p class="hg-muted">Shared with your quote.</p><?php } ?></div>
                <div><h3>Not included</h3><?php if ($exc) { ?><ul class="hg-checks hg-checks--no"><?php foreach ($exc as $i) { ?><li><?= e($i['name']) ?><?= $i['description'] ? ' — ' . e($i['description']) : '' ?></li><?php } ?></ul><?php } else { ?><p class="hg-muted">Shared with your quote.</p><?php } ?></div>
            </div>
        </section>

        <section class="hg-pkgsec" id="price" aria-labelledby="pr-title">
            <h2 class="hg-h2" id="pr-title">Price</h2>
            <div class="hg-pricebox">
                <div>
                    <p class="hg-pricebox__label"><?= $rate ? 'Current rate' : 'Package price' ?></p>
                    <p class="hg-pricebox__value"><?= e(rate_label($rate)) ?></p>
                    <?php if ($rate) { ?><p class="hg-muted">Rate valid: <strong><?= e(rate_validity($rate)) ?></strong> · Price version <?= (int) $rate['version'] ?></p><?php if ($rate['price_notes']) { ?><p class="hg-muted"><?= e($rate['price_notes']) ?></p><?php } ?>
                    <?php } else { ?><p class="hg-muted">We quote this package for your dates, travellers and hotel choice. No current rate is published for this tour.</p><?php } ?>
                </div>
                <a class="hg-btn hg-btn--primary" href="#enquire"><?= $rate ? 'Enquire to book' : 'Get current price' ?></a>
            </div>
            <p class="hg-travelnote hg-travelnote--inline" role="note"><span><strong>Standard package cost excludes airfare, train fare and bus fare</strong> unless specifically mentioned in the package inclusions.</span></p>
        </section>

        <?php if ($addons) { ?>
        <section class="hg-pkgsec" id="addons" aria-labelledby="ad-title">
            <h2 class="hg-h2" id="ad-title">Optional add-ons</h2>
            <ul class="cms-paddons">
            <?php foreach ($addons as $a) { $un = $a['availability'] === 'unavailable'; ?>
                <li<?= $un ? ' class="is-off"' : '' ?>><label><input type="checkbox" <?= $un || $a['required'] ? 'disabled' : '' ?> <?= $a['required'] ? 'checked' : '' ?>> <strong><?= e($a['name']) ?></strong><?= $a['description'] ? ' — ' . e($a['description']) : '' ?></label>
                    <span><?= $un ? 'Unavailable' : ($a['price'] !== null ? e(inr($a['price'])) . ' ' . e($a['price_unit']) : 'Price on request') ?><?= $a['required'] ? ' · included' : '' ?></span></li>
            <?php } ?>
            </ul>
            <p class="hg-muted"><?= $rate ? 'Base price + selected add-ons = your quote total.' : 'Selected add-ons are added to your quote.' ?></p>
        </section>
        <?php } ?>

        <section class="hg-pkgsec" aria-label="Reviews"><p class="hg-muted">Reviews: no verified reviews are connected, so none are shown and no rating is published.</p></section>

        <?php if ($p['faqs']) { ?>
        <section class="hg-pkgsec" id="faq" aria-labelledby="faq-title">
            <h2 class="hg-h2" id="faq-title">Questions about this package</h2>
            <div class="hg-faq"><?php foreach ($p['faqs'] as $f) { ?><details><summary><?= e($f['question']) ?></summary><p><?= e($f['answer']) ?></p></details><?php } ?></div>
        </section>
        <?php } ?>

        <section class="hg-pkgsec" id="enquire" aria-labelledby="enq-title">
            <h2 class="hg-h2" id="enq-title">Enquire about this tour</h2>
            <p class="hg-muted">On the website this opens the Contact Us form as a <strong>Tour Package Enquiry</strong>, carrying: Package ID <?= $pid ? e($pid) : '(not assigned yet)' ?> · <?= e($p['name']) ?> · <?= e($dest) ?> · <?= e(rate_label($rate)) ?><?= $rate ? ' · price version ' . (int) $rate['version'] . ' · valid ' . e(rate_validity($rate)) : '' ?><?= $offers ? ' · offer ' . e(implode(', ', array_map(function ($o) { return $o['offer_code']; }, $offers))) : '' ?> · selected add-ons · travel date · travellers · departure city · page URL and UTM.</p>
        </section>
    </div>
</div>
</main>
</body>
</html>
