<?php
/**
 * CMS page shell. Vars: $title, $crumbs (array of [label, href|null]), $content (HTML), $active (sidebar key),
 * optional $head (page header HTML), $bodyClass, $bottomBar (mobile sticky actions HTML).
 */
$u = cms_user();
$r = role();
$nav = array(
    array('Dashboard', null, array(array('dashboard', 'Dashboard', '/', 'packages'))),
    array('Tour packages', 'packages', array(
        array('packages', 'All Packages', '/packages', 'packages'),
        array('new', 'Add New Package', '/packages/new', 'packages', 'edit'),
        array('itinerary', 'Manage Itinerary', '/packages?focus=itinerary', 'itinerary'),
        array('pricing', 'Price & Availability', '/pricing', 'pricing'),
        array('images', 'Package Images', '/media', 'media'),
        array('versions', 'Package Version History', '/versions', 'packages'),
        array('recycle', 'Recycle Bin', '/recycle-bin', 'packages'),
    )),
    array('Merchandising', 'curation', array(
        array('featured', 'Featured Tours', '/curation?view=featured', 'curation'),
        array('curation', 'Top Tour Collection', '/curation', 'curation'),
        array('offers', 'Offer Zone', '/offers', 'offers'),
    )),
    array('Destinations', null, array(
        array('destinations', 'Destinations', '/destinations', 'packages'),
        array(null, 'Countries', null), array(null, 'Regions', null), array(null, 'Cities', null), array(null, 'Speciality Tours', null),
    )),
    array('CRM', 'enquiries', array(
        array('enquiries', 'Enquiries', '/enquiries', 'enquiries'),
        array(null, 'Customers', null), array(null, 'Quotations', null), array(null, 'Bookings', null), array(null, 'Payments', null), array(null, 'Reviews', null),
    )),
    array('Content', null, array(
        array(null, 'CMS Pages', null), array(null, 'Travel Guides', null), array(null, 'FAQs', null),
        array('media', 'Media Library', '/media', 'media'),
    )),
    array('SEO / AEO', 'seo', array(
        array('seo', 'SEO Dashboard', '/seo', 'seo'),
        array(null, 'Metadata', null), array(null, 'Schema', null), array(null, 'Internal Links', null),
    )),
    array('Admin', null, array(
        array('users', 'Users', '/users', 'users'),
        array(null, 'Settings', null),
        array('activity', 'Activity Log', '/activity', 'activity'),
    )),
);
$active = isset($active) ? $active : '';
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Holiday Guru CMS</title>
<link rel="icon" href="/assets/brand/holiday-guru-travel-logo-240.png">
<link rel="stylesheet" href="/cms-assets/cms.css?v=<?= filemtime(CMS_ROOT . '/public/cms-assets/cms.css') ?>">
</head>
<body class="cms <?= isset($bodyClass) ? e($bodyClass) : '' ?>">
<a class="cms-skip" href="#cms-main">Skip to content</a>
<?php if (cms_is_staging()) { ?><div class="cms-ribbon" role="note">Staging CMS — changes here do not reach the live website</div><?php } ?>
<div class="cms-shell">
    <aside class="cms-side" id="cms-side" aria-label="CMS navigation">
        <div class="cms-side__brand">
            <span class="cms-side__logo"><img src="/assets/brand/holiday-guru-travel-logo-240.png" alt="Holiday Guru Travel" width="120" height="40"></span>
            <span class="cms-side__app">CMS · CRM</span>
            <button type="button" class="cms-side__close" data-cms-nav-close aria-label="Close menu"><?= icon('x') ?></button>
        </div>
        <nav class="cms-side__nav">
            <?php foreach ($nav as $g) {
                $items = array_filter($g[2], function ($i) use ($r) { return $i[0] === null || hg_can($r, $i[3], isset($i[4]) ? $i[4] : 'view'); });
                if (!$items) continue; ?>
                <?php if ($g[0] !== 'Dashboard') { ?><p class="cms-side__group"><?= e($g[0]) ?></p><?php } ?>
                <ul>
                    <?php foreach ($items as $i) { if ($i[0] === null) { ?>
                        <li><span class="cms-side__link is-planned" title="Planned module — not part of this release"><?= e($i[1]) ?></span></li>
                    <?php } else { ?>
                        <li><a class="cms-side__link<?= $active === $i[0] ? ' is-active' : '' ?>" href="<?= e($i[2]) ?>"<?= $active === $i[0] ? ' aria-current="page"' : '' ?>><?= e($i[1]) ?></a></li>
                    <?php } } ?>
                </ul>
            <?php } ?>
        </nav>
        <p class="cms-side__legend">Greyed items are planned modules.</p>
    </aside>
    <div class="cms-side__scrim" data-cms-nav-close hidden></div>
    <div class="cms-body">
        <header class="cms-top">
            <button type="button" class="cms-top__menu" data-cms-nav-open aria-controls="cms-side" aria-expanded="false" aria-label="Open menu"><?= icon('menu') ?></button>
            <nav class="cms-crumbs" aria-label="Breadcrumb"><ol>
                <?php foreach ($crumbs as $i => $c) { ?><li><?php if ($c[1] && $i < count($crumbs) - 1) { ?><a href="<?= e($c[1]) ?>"><?= e($c[0]) ?></a><?php } else { ?><span aria-current="page"><?= e($c[0]) ?></span><?php } ?></li><?php } ?>
            </ol></nav>
            <form class="cms-top__search" action="/packages" method="get" role="search">
                <label class="cms-sr" for="cms-q">Search packages</label>
                <?= icon('search') ?><input id="cms-q" name="q" type="search" placeholder="Package ID, name, destination or OF-code" value="<?= e(get('q')) ?>">
            </form>
            <?php if ($u) { ?>
            <div class="cms-top__user">
                <span class="cms-avatar" aria-hidden="true"><?= e(mb_substr(preg_replace('/^Staging /', '', $u['name']), 0, 1)) ?></span>
                <span class="cms-top__who"><strong><?= e($u['name']) ?></strong><small><?= e(HG_ROLES[$u['role']]) ?></small></span>
                <form method="post" action="/logout"><?= csrf_field() ?><button class="cms-btn cms-btn--ghost cms-btn--sm" type="submit">Log out</button></form>
            </div>
            <?php } ?>
        </header>
        <main class="cms-main" id="cms-main" tabindex="-1">
            <?php foreach (flash() as $f) { ?><div class="cms-flash cms-flash--<?= e($f[0]) ?>" role="<?= $f[0] === 'err' ? 'alert' : 'status' ?>"><?= e($f[1]) ?></div><?php } ?>
            <?= isset($head) ? $head : '' ?>
            <?= $content ?>
        </main>
    </div>
</div>
<?= isset($bottomBar) ? $bottomBar : '' ?>
<script src="/cms-assets/cms.js?v=<?= filemtime(CMS_ROOT . '/public/cms-assets/cms.js') ?>" defer></script>
</body>
</html>
