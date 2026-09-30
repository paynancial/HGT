<?php
/**
 * GLOBAL COMPONENT: mega-menu panels.
 *   #mega-india, #mega-intl  region tabs (left) → destinations with their places (right), top-destination strip
 *   #mega-inbound, #mega-spec, #mega-fixed, #mega-about  column panels with a feature card
 * All destinations, places and packages come from include/data (hg_groups(), hg_packages()); nothing is typed
 * here that the data does not contain. No counts are shown (owner rule). Place links open the destination page
 * filtered to that place (/tours/{key}?place[]={place}).
 * Included by include/global/navigation.php with $hgMegaPanel = 'india' | 'intl' | 'inbound' | 'spec' | 'fixed' | 'about'.
 * Behaviour: assets/js/src/ui/mega-menu.js (open/close, region tabs).
 */
if (!isset($hgMega)) {
    $hgMega = array();
    $hgSlug = function ($s) { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-'); };
    $hgGroupsAll = array_filter(hg_groups(), function ($g) { return $g['count'] > 0; });
    // Places per destination, most frequent first (from the packages' own "places").
    $hgPlaces = array();
    foreach (hg_packages() as $p) {
        foreach ($p['places'] as $pl) {
            if (!isset($hgPlaces[$p['group']][$pl])) $hgPlaces[$p['group']][$pl] = 0;
            $hgPlaces[$p['group']][$pl]++;
        }
    }
    foreach ($hgPlaces as &$hgPl) arsort($hgPl);
    unset($hgPl);
    // India in four regions (owner request); pilgrimage destinations sit with the north.
    $hgRegionOf = array('Pilgrimage' => 'North India', 'East & North East' => 'East & North East India');
    $hgRegions = array('india' => array(), 'international' => array());
    foreach ($hgGroupsAll as $g) {
        $r = $g['region'] === 'india' ? (isset($hgRegionOf[$g['area']]) ? $hgRegionOf[$g['area']] : $g['area']) : $g['area'];
        $hgRegions[$g['region']][$r][] = $g;
    }
    $hgOrder = array('North India', 'South India', 'East & North East India', 'West India');
    uksort($hgRegions['india'], function ($a, $b) use ($hgOrder) {
        $x = array_search($a, $hgOrder); $y = array_search($b, $hgOrder);
        return ($x === false ? 99 : $x) - ($y === false ? 99 : $y);
    });

    /** One destination block: name (to its page) + its places (to the filtered page). */
    $hgMega['group'] = function (array $g, $max = 7) use ($hgPlaces, $hgSlug) {
        $out = '<div class="hg-mega__group"><a class="hg-mega__gname" href="' . hg_e($g['hub_url']) . '">' . hg_e($g['name']) . '</a>';
        $pl = isset($hgPlaces[$g['key']]) ? array_slice(array_keys($hgPlaces[$g['key']]), 0, $max) : array();
        $pl = array_values(array_filter($pl, function ($p) use ($g) { return strcasecmp($p, $g['name']) !== 0; }));
        if ($pl) {
            $out .= '<ul class="hg-mega__places">';
            foreach ($pl as $p) $out .= '<li><a href="' . hg_e($g['hub_url'] . '?place%5B%5D=' . rawurlencode($hgSlug($p))) . '">' . hg_e($p) . '</a></li>';
            $out .= '</ul>';
        }
        return $out . '</div>';
    };

    /** Region-tab panel (Domestic, International). $extra = array(region label => html) for panes without data. */
    $hgMega['tabs'] = function ($id, array $regions, $allLabel, $allUrl, array $top, array $extra = array()) use (&$hgMega) {
        $out = '<div class="hg-mega hg-mega--tabs" id="mega-' . $id . '">';
        if ($top) {
            $out .= '<div class="hg-mega__top"><div class="hg-mega__inner"><span class="hg-mega__toplabel">Top recommended destinations</span><ul>';
            foreach ($top as $g) $out .= '<li><a href="' . hg_e($g['hub_url']) . '">' . hg_e($g['name']) . '</a></li>';
            $out .= '</ul></div></div>';
        }
        $out .= '<div class="hg-mega__inner hg-mega__body"><div class="hg-mega__rail" role="tablist" aria-orientation="vertical" aria-label="Regions">';
        $i = 0;
        $labels = array_merge(array_keys($regions), array_keys($extra));
        foreach ($labels as $label) {
            $k = $id . '-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($label)), '-');
            $out .= '<button type="button" role="tab" class="hg-mega__tab" id="mt-' . $k . '" aria-controls="mp-' . $k . '" aria-selected="' . ($i ? 'false' : 'true') . '"' . ($i ? ' tabindex="-1"' : '') . ' data-hg-mega-tab>' . hg_e($label) . hg_icon('chevron') . '</button>';
            $i++;
        }
        $out .= '</div><div class="hg-mega__panes">';
        $i = 0;
        foreach ($labels as $label) {
            $k = $id . '-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($label)), '-');
            $out .= '<div class="hg-mega__pane' . ($i ? '' : ' is-active') . '" role="tabpanel" id="mp-' . $k . '" aria-labelledby="mt-' . $k . '">';
            $out .= '<p class="hg-mega__panehead">' . hg_e($label) . '</p>';
            if (isset($regions[$label])) {
                $out .= '<a class="hg-mega__all" href="' . hg_e($allUrl) . '">' . hg_e($allLabel) . ' <span aria-hidden="true">&rsaquo;</span></a><div class="hg-mega__groups">';
                foreach ($regions[$label] as $g) $out .= $hgMega['group']($g);
                $out .= '</div>';
            } else {
                $out .= $extra[$label];
            }
            $out .= '</div>';
            $i++;
        }
        return $out . '</div></div>' . $hgMega['foot']($id) . '</div>';
    };

    $hgMega['foot'] = function ($id) {
        return '<div class="hg-mega__foot"><div class="hg-mega__inner"><span>Can’t find your trip? We plan tailor-made holidays around your dates and budget.</span>'
            . '<a class="hg-btn hg-btn--primary hg-btn--sm" href="/customized-holidays">Plan a customized holiday</a>'
            . '<a class="hg-btn hg-btn--light hg-btn--sm" href="' . hg_e(hg_whatsapp_href()) . '" target="_blank" rel="noopener">' . hg_icon('whatsapp') . 'WhatsApp us</a></div></div>';
    };

    /** Column panel with a feature card on the right. */
    $hgMega['cols'] = function ($id, array $cols, $feature) {
        $out = '<div class="hg-mega hg-mega--cols" id="mega-' . $id . '"><div class="hg-mega__inner hg-mega__grid"><div class="hg-mega__cols">';
        foreach ($cols as $head => $items) {
            $out .= '<div class="hg-mega__col"><p class="hg-mega__head">' . hg_e($head) . '</p><ul>';
            foreach ($items as $it) {
                $out .= '<li><a href="' . hg_e($it[1]) . '"' . (!empty($it[3]) ? ' target="_blank" rel="noopener"' : '') . '><span>' . hg_e($it[0]) . (isset($it[2]) && $it[2] !== '' ? '<small>' . hg_e($it[2]) . '</small>' : '') . '</span></a></li>';
            }
            $out .= '</ul></div>';
        }
        return $out . '</div>' . $feature . '</div></div>';
    };
    $hgMega['feature'] = function ($title, $text, array $links, $extraHtml = '') {
        $out = '<div class="hg-mega__feature"><p class="hg-mega__ftitle">' . hg_e($title) . '</p><p>' . hg_e($text) . '</p>' . $extraHtml . '<div class="hg-mega__factions">';
        foreach ($links as $l) {
            $out .= '<a class="hg-btn hg-btn--sm ' . ($l[2] ? 'hg-btn--primary' : 'hg-btn--light') . '" href="' . hg_e($l[1]) . '"' . (!empty($l[3]) ? ' target="_blank" rel="noopener"' : '') . '>' . $l[0] . '</a>';
        }
        return $out . '</div></div>';
    };
    $hgMega['g'] = function ($keys) use ($hgGroupsAll) {
        $out = array();
        foreach ($keys as $k) if (isset($hgGroupsAll[$k])) $out[] = array($hgGroupsAll[$k]['name'], $hgGroupsAll[$k]['hub_url']);
        return $out;
    };
    $hgMega['top'] = function ($region, $n) use ($hgGroupsAll) {
        $g = array_values(array_filter($hgGroupsAll, function ($x) use ($region) { return $x['region'] === $region; }));
        usort($g, function ($a, $b) { return $b['count'] - $a['count']; });   // internal ordering only; counts are never shown
        return array_slice($g, 0, $n);
    };
    $hgMega['regions'] = $hgRegions;
}

if ($hgMegaPanel === 'india') {
    echo $hgMega['tabs']('india', $hgMega['regions']['india'], 'All India tours', '/domestic-holidays', $hgMega['top']('india', 6));

} elseif ($hgMegaPanel === 'intl') {
    $req = '<p class="hg-mega__note">No ready packages yet — we plan these trips around your dates.</p><ul class="hg-mega__places hg-mega__places--wide">';
    foreach (array('Thailand', 'Bali', 'Europe', 'Sri Lanka', 'Nepal', 'Bhutan') as $d) $req .= '<li><a href="/customized-holidays?destination=' . rawurlencode($d) . '">' . hg_e($d) . '</a></li>';
    $req .= '</ul>';
    echo $hgMega['tabs']('intl', $hgMega['regions']['international'], 'All international tours', '/international-holidays', array(), array('Plan on request' => $req));

} elseif ($hgMegaPanel === 'inbound') {
    echo $hgMega['cols']('inbound', array(
        'Himalayas & hills' => $hgMega['g'](array('kashmir', 'ladakh', 'himachal', 'uttarakhand', 'sikkim-darjeeling')),
        'Spiritual India' => array_merge($hgMega['g'](array('char-dham', 'amarnath')), array(array('All pilgrimage tours', '/religious-tour'))),
        'Beaches & backwaters' => $hgMega['g'](array('kerala', 'goa', 'south-india')),
    ), $hgMega['feature']('Visiting India from abroad?', 'Day-by-day India itineraries with hotels, private transfers and sightseeing — planned with you on WhatsApp before you fly.',
        array(array('India tours for visitors', '/india-tours', true), array('Plan my India trip', '/customized-holidays', false))));

} elseif ($hgMegaPanel === 'spec') {
    echo $hgMega['cols']('spec', array(
        'Pilgrimage tours' => array(array('Char Dham & Do Dham Yatra', '/tours/char-dham', 'Yamunotri, Gangotri, Kedarnath, Badrinath'), array('Amarnath Yatra', '/tours/amarnath', 'By helicopter or on foot via Pahalgam'), array('All pilgrimage tours', '/religious-tour')),
        'Holiday styles' => array(array('Family holidays', '/customized-holidays?destination=Family%20holiday'), array('Honeymoon holidays', '/customized-holidays?destination=Honeymoon'), array('Adventure tours', '/customized-holidays?destination=Adventure'), array('Luxury holidays', '/customized-holidays?destination=Luxury'), array('Senior citizen tours', '/customized-holidays?destination=Senior%20citizen%20tour'), array('Group tours', '/customized-holidays?destination=Group%20tour')),
    ), $hgMega['feature']('Have something special in mind?', 'Honeymoon, family reunion or a group trip — we plan it around your dates and budget.',
        array(array('Plan a customized holiday', '/customized-holidays', true))));

} elseif ($hgMegaPanel === 'fixed') {
    // Packages that start from a set city (data field "departure"). Departure DATES are not in the data yet,
    // so the panel asks visitors to request the next dates instead of showing any.
    $fx = array_values(array_filter(hg_packages(), function ($p) { return !empty($p['departure']); }));
    $items = array();
    foreach ($fx as $p) $items[] = array($p['name'], $p['url'], $p['duration'] . ' · from ' . $p['departure']);
    $wa = 'https://wa.me/' . HG_WHATSAPP_NUMBER . '?text=' . rawurlencode('Hello Holiday Guru Travel, please share the next fixed departure dates.');
    echo $hgMega['cols']('fixed', array('Set start city tours' => array_slice($items, 0, 7)),
        $hgMega['feature']('Fixed departures', 'Group departures leave on set dates from Delhi and Haridwar. Dates and seats change by season — ask us for the next departures.',
            array(array(hg_icon('whatsapp') . 'Ask for the next dates', $wa, true, true), array('Send an enquiry', '/customized-holidays?destination=Fixed%20departure', false))));

} elseif ($hgMegaPanel === 'about') {
    $contact = '<ul class="hg-mega__contact"><li>' . hg_icon('phone') . '<a href="' . hg_e(hg_tel_href()) . '">' . hg_e(HG_PHONE_DISPLAY) . '</a></li>'
        . '<li>' . hg_icon('mail') . '<a href="' . hg_e(hg_mailto_href()) . '">' . hg_e(HG_EMAIL_DISPLAY) . '</a></li>'
        . '<li>' . hg_icon('pin') . '<span>' . hg_e(HG_ADDRESS_LINE1) . ', ' . hg_e(HG_ADDRESS_LINE2) . '</span></li></ul>';
    echo $hgMega['cols']('about', array(
        'Company' => array(array('About Holiday Guru Travel', '/about'), array('Leadership', '/leadership'), array('Our team', '/our-team'), array('Contact us', '/contact')),
        'Help & policies' => array(array('FAQs', '/faqs'), array('Cancellation policy', '/cancellation-policy'), array('Refund policy', '/refund-policy'), array('Payment policy', '/payment-policy'), array('Grievance redress', '/grievance-redress')),
    ), $hgMega['feature']('Talk to a travel expert', 'Call or WhatsApp — the team that plans your trip answers.', array(array(hg_icon('whatsapp') . 'WhatsApp us', hg_whatsapp_href(), true, true)), $contact));
}
