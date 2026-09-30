<?php
require_once __DIR__ . '/../ui/core.php';
$hgGroups = hg_groups();
$hgS = hg_search_state();
if ($hgS['destination'] === '' && !empty($GLOBALS['hgMeta']['search_destination'])) {
    $hgS['destination'] = $GLOBALS['hgMeta']['search_destination']; // e.g. Kashmir on a Kashmir package page
}
$hgArea = function ($region) use ($hgGroups) {
    $areas = array();
    foreach ($hgGroups as $g) {
        if ($g['region'] === $region && $g['count'] > 0) {
            $areas[$g['area']][] = $g;
        }
    }
    return $areas;
};
$hgIndia = array('North India' => array(), 'South India' => array(), 'East India' => array(), 'West India' => array());
// India mega menu in four regions (owner request). Pilgrimage destinations are in the north;
// "East & North East" shows as East India. Destination data (area) is unchanged.
$hgRegionOf = array('Pilgrimage' => 'North India', 'East & North East' => 'East India');
foreach ($hgArea('india') as $area => $items) {
    $col = isset($hgRegionOf[$area]) ? $hgRegionOf[$area] : $area;
    $hgIndia[isset($hgIndia[$col]) ? $col : 'North India'] = array_merge(isset($hgIndia[$col]) ? $hgIndia[$col] : array(), $items);
}
$hgIndia = array_filter($hgIndia);
$hgIntl = $hgArea('international');
$hgPilgrimCount = count(hg_packages_where(function ($p) { return $p['pilgrimage']; }));
$hgSoon = function ($label) {
    return '<span class="hg-soon">' . hg_e($label) . ' <span class="hg-soon__tag">Soon</span></span>';
};
$hgMegaCol = function ($area, array $items) {
    $out = '<div class="hg-mega__col"><p class="hg-mega__head">' . hg_e($area) . '</p><ul>';
    foreach ($items as $g) {
        $out .= '<li><a href="' . hg_e($g['hub_url']) . '"><span>' . hg_e($g['name']) . '</span> <span class="hg-mega__count">'
            . (int) $g['count'] . ' tour' . ($g['count'] === 1 ? '' : 's') . '</span></a></li>';
    }
    return $out . '</ul></div>';
};
$hgMegaFeature = function ($title, $text, array $links) {
    $out = '<div class="hg-mega__feature"><p class="hg-mega__ftitle">' . hg_e($title) . '</p><p>' . hg_e($text) . '</p><div class="hg-mega__factions">';
    foreach ($links as $l) {
        $out .= '<a class="hg-btn hg-btn--sm ' . ($l[2] ? 'hg-btn--primary' : 'hg-btn--light') . '" href="' . hg_e($l[1]) . '">' . hg_e($l[0]) . '</a>';
    }
    return $out . '</div></div>';
};
?>
<a class="hg-skip" href="#main">Skip to content</a>
<div class="hg-utility">
    <div class="hg-container hg-utility__inner">
        <ul class="hg-utility__left" aria-label="Contact">
            <li><a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_icon('phone') ?><span><?= hg_e(HG_PHONE_DISPLAY) ?></span></a></li>
            <li><a href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp') ?><span>24×7 Support</span></a></li>
        </ul>
        <ul class="hg-utility__right">
            <li><?= $hgSoon('Offers') ?></li>
            <li><?= $hgSoon('Blogs') ?></li>
            <li><a href="/about">About Us</a></li>
            <li><a href="/contact">Contact Us</a></li>
            <li><button type="button" class="hg-login-btn" data-hg-login-open><?= hg_icon('user') ?><span>Login</span></button></li>
        </ul>
    </div>
</div>

<header class="hg-header" data-hg-header>
    <div class="hg-container hg-header__inner">
        <button type="button" class="hg-iconbtn hg-header__menu" aria-controls="hg-nav" aria-expanded="false" data-hg-menu-toggle>
            <?= hg_icon('menu') ?><span class="hg-sr">Open menu</span>
        </button>
        <a class="hg-header__logo" href="/" aria-label="Holiday Guru Travel home">
            <picture>
                <source type="image/webp" srcset="/assets/brand/holiday-guru-travel-logo-240.webp 1x, /assets/brand/holiday-guru-travel-logo-480.webp 2x">
                <img src="/assets/brand/holiday-guru-travel-logo-240.png" alt="Holiday Guru Travel" width="122" height="60">
            </picture>
        </a>
        <form class="hg-hsearch" role="search" action="/tours" method="get" data-hg-search-form id="hg-header-search" aria-label="Search holiday packages">
            <div class="hg-hsearch__sheethead">
                <span class="hg-hsearch__sheettitle">Search holiday packages</span>
                <button type="button" class="hg-iconbtn" data-hg-search-close><?= hg_icon('close') ?><span class="hg-sr">Close search</span></button>
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--dest">
                <?= hg_icon('search') ?>
                <label for="hg-q">Destination</label>
                <input id="hg-q" name="destination" type="search" value="<?= hg_e($hgS['destination']) ?>" placeholder="Search &quot;Gulmarg&quot;" autocomplete="off"
                       data-hg-placeholder-cycle="Gulmarg|Kashmir|Char Dham|Kerala|Dubai|Leh Ladakh|Goa"
                       role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="hg-q-list" data-hg-autocomplete data-hg-ac-fill>
                <ul class="hg-ac" id="hg-q-list" role="listbox" aria-label="Suggestions" hidden></ul>
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--date hg-hsearch__field--extra">
                <?= hg_icon('calendar') ?>
                <label for="hg-date">Travel date</label>
                <input id="hg-date" name="date" type="date" value="<?= hg_e($hgS['date']) ?>" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--extra">
                <?= hg_icon('users') ?>
                <label for="hg-adults">Adults</label>
                <select id="hg-adults" name="adults"<?= $hgS['has_travellers'] ? '' : ' data-hg-optional' ?>><?php for ($i = 1; $i <= 9; $i++) { ?><option value="<?= $i ?>"<?= $i === $hgS['adults'] ? ' selected' : '' ?>><?= $i ?> Adult<?= $i > 1 ? 's' : '' ?></option><?php } ?></select>
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--extra">
                <?= hg_icon('users') ?>
                <label for="hg-children">Children</label>
                <select id="hg-children" name="children"<?= $hgS['has_travellers'] ? '' : ' data-hg-optional' ?>><?php for ($i = 0; $i <= 6; $i++) { ?><option value="<?= $i ?>"<?= $i === $hgS['children'] ? ' selected' : '' ?>><?= $i ?></option><?php } ?></select>
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--extra">
                <?= hg_icon('route') ?>
                <label for="hg-departure">Departure city</label>
                <select id="hg-departure" name="departure"><option value="">Any</option><option value="delhi"<?= $hgS['departure'] === 'delhi' ? ' selected' : '' ?>>Delhi</option><option value="haridwar"<?= $hgS['departure'] === 'haridwar' ? ' selected' : '' ?>>Haridwar</option></select>
            </div>
            <button class="hg-btn hg-btn--primary hg-hsearch__submit" type="submit"><?= hg_icon('search') ?><span>Search</span><span class="hg-sr"> holiday packages</span></button>
        </form>
        <div class="hg-header__actions">
            <button type="button" class="hg-iconbtn hg-header__searchbtn" aria-controls="hg-header-search" aria-expanded="false" data-hg-search-toggle><?= hg_icon('search') ?><span class="hg-sr">Search holiday packages</span></button>
            <a class="hg-hcontact" href="<?= hg_e(hg_tel_href()) ?>" aria-label="Call <?= hg_e(HG_PHONE_DISPLAY) ?>"><?= hg_icon('phone') ?><span><strong>Call Now</strong><?= hg_e(HG_PHONE_DISPLAY) ?></span></a>
            <a class="hg-hcontact hg-hcontact--wa" href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener" aria-label="WhatsApp <?= hg_e(HG_PHONE_DISPLAY) ?>"><?= hg_icon('whatsapp') ?><span><strong>WhatsApp</strong><?= hg_e(HG_PHONE_DISPLAY) ?></span></a>
            <button type="button" class="hg-iconbtn hg-header__login" data-hg-login-open><?= hg_icon('user') ?><span class="hg-sr">Login</span></button>
        </div>
    </div>

    <nav class="hg-nav" id="hg-nav" aria-label="Primary" data-hg-nav>
        <div class="hg-nav__drawerhead">
            <span class="hg-nav__drawertitle">Menu</span>
            <button type="button" class="hg-iconbtn" data-hg-menu-close><?= hg_icon('close') ?><span class="hg-sr">Close menu</span></button>
        </div>
        <ul class="hg-container hg-nav__list">
            <li class="hg-nav__item hg-nav__item--mega">
                <button type="button" class="hg-nav__trigger" aria-expanded="false" aria-controls="mega-india" data-hg-mega>India<?= hg_icon('chevron') ?></button>
                <div class="hg-mega" id="mega-india">
                    <div class="hg-mega__grid">
                        <div class="hg-mega__cols hg-mega__cols--4">
                            <?php foreach ($hgIndia as $area => $items) echo $hgMegaCol($area, $items); ?>
                        </div>
                        <?= $hgMegaFeature('Plan your India trip', 'Every package has a day-by-day itinerary, inclusions and exclusions you can read before you enquire.', array(array('All India tours', '/domestic-holidays', true), array('Search all packages', '/tours', false))) ?>
                    </div>
                </div>
            </li>
            <li class="hg-nav__item hg-nav__item--mega">
                <button type="button" class="hg-nav__trigger" aria-expanded="false" aria-controls="mega-intl" data-hg-mega>International<?= hg_icon('chevron') ?></button>
                <div class="hg-mega" id="mega-intl">
                    <div class="hg-mega__grid">
                        <div class="hg-mega__cols">
                            <?php foreach ($hgIntl as $area => $items) echo $hgMegaCol($area, $items); ?>
                            <div class="hg-mega__col">
                                <p class="hg-mega__head">Coming soon</p>
                                <ul><li><?= $hgSoon('Thailand') ?></li><li><?= $hgSoon('Europe') ?></li><li><?= $hgSoon('Bali') ?></li></ul>
                            </div>
                        </div>
                        <?= $hgMegaFeature('Travelling abroad?', 'Hotels, transfers and sightseeing planned together — tell us your dates and we build the trip.', array(array('All international tours', '/international-holidays', true), array('Plan a custom trip', '/customized-holidays', false))) ?>
                    </div>
                </div>
            </li>
            <li class="hg-nav__item hg-nav__item--mega">
                <button type="button" class="hg-nav__trigger" aria-expanded="false" aria-controls="mega-spec" data-hg-mega>Speciality Tours<?= hg_icon('chevron') ?></button>
                <div class="hg-mega" id="mega-spec">
                    <div class="hg-mega__grid">
                        <div class="hg-mega__cols">
                            <div class="hg-mega__col">
                                <p class="hg-mega__head">Available now</p>
                                <ul>
                                    <li><a href="/religious-tour"><span>Pilgrimage Tours<small>Char Dham, Amarnath, Vaishno Devi</small></span> <span class="hg-mega__count"><?= (int) $hgPilgrimCount ?> tours</span></a></li>
                                    <li><a href="/india-tours"><span>India Tours for Foreign Travellers<small>Private tours from your arrival</small></span></a></li>
                                </ul>
                            </div>
                            <div class="hg-mega__col">
                                <p class="hg-mega__head">Coming soon</p>
                                <ul class="hg-mega__soonlist">
                                    <?php foreach (array('Family Holidays', 'Honeymoon Holidays', 'Adventure Tours', 'Luxury Holidays', 'Senior Citizen Tours', 'Group Tours') as $sp) { ?><li><?= $hgSoon($sp) ?></li><?php } ?>
                                </ul>
                            </div>
                        </div>
                        <?= $hgMegaFeature('Have something special in mind?', 'Honeymoon, family reunion or a group trip — we plan it around your dates and budget.', array(array('Plan a customized holiday', '/customized-holidays', true))) ?>
                    </div>
                </div>
            </li>
            <li class="hg-nav__item"><a class="hg-nav__link" href="/customized-holidays">Customized Holidays</a></li>
            <li class="hg-nav__item"><?= $hgSoon('Flights') ?></li>
            <li class="hg-nav__item"><?= $hgSoon('Visa') ?></li>
            <li class="hg-nav__item"><?= $hgSoon('Corporate Travel') ?></li>
            <li class="hg-nav__item"><?= $hgSoon('Forex') ?></li>
            <li class="hg-nav__item"><a class="hg-nav__link" href="/contact">Contact</a></li>
        </ul>
        <div class="hg-nav__drawerfoot">
            <a class="hg-btn hg-btn--primary hg-btn--block" href="/customized-holidays">Enquire now</a>
            <div class="hg-nav__quick">
                <a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_icon('phone') ?> Call</a>
                <a href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp') ?> WhatsApp</a>
                <a href="<?= hg_e(hg_mailto_href()) ?>"><?= hg_icon('mail') ?> Email</a>
            </div>
            <ul class="hg-nav__secondary"><li><a href="/about">About Us</a></li><li><a href="/faqs">FAQs</a></li></ul>
        </div>
    </nav>
    <div class="hg-nav__scrim" data-hg-menu-close hidden></div>
</header>
