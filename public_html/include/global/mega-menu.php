<?php
/**
 * GLOBAL COMPONENT: mega-menu panels (#mega-india, #mega-intl, #mega-spec).
 * Menu data comes from include/data/destinations.json (via hg_groups()), never from page templates.
 * Included by include/global/navigation.php with $hgMegaPanel = 'india' | 'intl' | 'spec'.
 */
if (!isset($hgMegaCol)) {
    $hgGroups = hg_groups();
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
    $hgMegaCol = function ($area, array $items) {
        $out = '<div class="hg-mega__col"><p class="hg-mega__head">' . hg_e($area) . '</p><ul>';
        foreach ($items as $g) {
            $out .= '<li><a href="' . hg_e($g['hub_url']) . '"><span>' . hg_e($g['name']) . '</span></a></li>';
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
}
if ($hgMegaPanel === 'india') { ?>
                <div class="hg-mega" id="mega-india">
                    <div class="hg-mega__grid">
                        <div class="hg-mega__cols hg-mega__cols--4">
                            <?php foreach ($hgIndia as $area => $items) echo $hgMegaCol($area, $items); ?>
                        </div>
                        <?= $hgMegaFeature('Plan your India trip', 'Every package has a day-by-day itinerary, inclusions and exclusions you can read before you enquire.', array(array('All India tours', '/domestic-holidays', true), array('Search all packages', '/tours', false))) ?>
                    </div>
                </div>
<?php } elseif ($hgMegaPanel === 'intl') { ?>
                <div class="hg-mega" id="mega-intl">
                    <div class="hg-mega__grid">
                        <div class="hg-mega__cols">
                            <?php foreach ($hgIntl as $area => $items) echo $hgMegaCol($area, $items); ?>
                            <div class="hg-mega__col">
                                <p class="hg-mega__head">Plan on request</p>
                                <ul><?php foreach (array('Thailand', 'Europe', 'Bali') as $hgReq) { ?><li><a href="/customized-holidays?destination=<?= rawurlencode($hgReq) ?>"><span><?= hg_e($hgReq) ?></span></a></li><?php } ?></ul>
                            </div>
                        </div>
                        <?= $hgMegaFeature('Travelling abroad?', 'Hotels, transfers and sightseeing planned together — tell us your dates and we build the trip.', array(array('All international tours', '/international-holidays', true), array('Plan a custom trip', '/customized-holidays', false))) ?>
                    </div>
                </div>
<?php } elseif ($hgMegaPanel === 'spec') { ?>
                <div class="hg-mega" id="mega-spec">
                    <div class="hg-mega__grid">
                        <div class="hg-mega__cols">
                            <div class="hg-mega__col">
                                <p class="hg-mega__head">Speciality tours</p>
                                <ul>
                                    <li><a href="/religious-tour"><span>Pilgrimage Tours<small>Char Dham, Amarnath, Vaishno Devi</small></span></a></li>
                                </ul>
                            </div>
                            <div class="hg-mega__col">
                                <p class="hg-mega__head">Plan on request</p>
                                <ul class="hg-mega__soonlist">
                                    <?php foreach (array('Family Holidays', 'Honeymoon Holidays', 'Adventure Tours', 'Luxury Holidays', 'Senior Citizen Tours', 'Group Tours') as $sp) { ?><li><a href="/customized-holidays"><span><?= hg_e($sp) ?></span></a></li><?php } ?>
                                </ul>
                            </div>
                        </div>
                        <?= $hgMegaFeature('Have something special in mind?', 'Honeymoon, family reunion or a group trip — we plan it around your dates and budget.', array(array('Plan a customized holiday', '/customized-holidays', true))) ?>
                    </div>
                </div>
<?php }
