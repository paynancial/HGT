<?php
require __DIR__ . '/include/ui/core.php';

$groups = hg_groups();
$india = array_filter($groups, function ($g) { return $g['region'] === 'india' && $g['count'] > 0; });
$world = array_filter($groups, function ($g) { return $g['region'] === 'international' && $g['count'] > 0; });
$allPackages = hg_packages();

// Editor-selected packages (real itineraries with complete data). Rename the
// section "Most Loved" only once enquiry/booking data supports that claim.
$featured = array();
foreach (array('srinagar-gulmarg-pahalgam-tour-package-5-days', 'char-dham-yatra-from-haridwar-9n-10d', 'shimla-manali-tour-06-days',
    'haridwar-with-mussoorie-and-jim-corbett-05-days', 'munnar-thekkady-alleppey-05-days', 'darjeeling-and-gangtok-06-days',
    'serene-leh-ladakh-tour-7n-8d', 'deluxe-tour-to-dubai-4n5d') as $slug) {
    if ($p = hg_package($slug)) $featured[] = $p;
}
// Internal curation (include/data/curation.json): tours marked homepage_featured replace the
// editor selection, ordered by priority_rank. Ranks and totals are never shown publicly.
$curated = array_values(array_filter($allPackages, function ($p) { return hg_curation($p['slug'])['homepage_featured']; }));
if ($curated) {
    usort($curated, function ($a, $b) { return (hg_curation($a['slug'])['priority_rank'] ?: 1000) <=> (hg_curation($b['slug'])['priority_rank'] ?: 1000); });
    $featured = array_slice($curated, 0, 8);
}

$faqs = hg_booking_faqs();

hg_layout_start(array(
    'title' => 'Holiday Guru Travel — India & International Holiday Packages',
    'description' => 'Holiday packages for Kashmir, Char Dham, Himachal, Uttarakhand, Kerala, Ladakh, Dubai and more, with day-by-day itineraries. Plan and book with our Noida-based travel team.',
    'path' => '/',
    'image' => '/assets/brand/holiday-guru-travel-logo-720.webp',
    'schema' => array(hg_faq_schema($faqs)),
));
?>

<?php
// Homepage hero slider: owner-supplied photos (assets/img/hero, metadata stripped). Captions link only where
// packages exist; the first slide loads with priority, the rest with low priority.
$heroSlides = array(
    array('assets/img/hero/taj-mahal-agra.jpg', 'The Taj Mahal in Agra seen across its long water channel and gardens', 'Taj Mahal, Agra', '', '50% 60%'),
    array('assets/img/hero/kerala-backwaters.jpg', 'A houseboat among palm trees and pink water lilies on the Kerala backwaters', 'Kerala backwaters', '/tours/kerala', '50% 55%'),
    array('assets/img/hero/varanasi-ganga-aarti.jpg', 'Priests performing the evening Ganga Aarti with brass lamps on the ghats of Varanasi', 'Ganga Aarti, Varanasi', '', '60% 40%'),
    array('assets/img/hero/golden-temple-amritsar.jpg', 'The Golden Temple in Amritsar reflected in the sacred pool under a clear blue sky', 'Golden Temple, Amritsar', '/tours?destination=amritsar', '50% 50%'),
);
?>
<section class="hg-hero hg-hero--slider" aria-labelledby="hero-title">
    <div class="hg-hero__slides" data-hg-slider aria-roledescription="carousel" aria-label="Destination photos">
        <?php foreach ($heroSlides as $i => $sl) { ?>
        <figure class="hg-hero__slide hg-frame hg-frame--hero hg-frame--dark<?= $i === 0 ? ' is-active' : '' ?>" data-hg-slide role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= count($heroSlides) ?>: <?= hg_e($sl[2]) ?>"<?= $i === 0 ? '' : ' aria-hidden="true"' ?> style="--hg-focus: <?= hg_e($sl[4]) ?>">
            <?= hg_img($sl[0], $sl[1], 2400, 1350, 'hg-hero__img', $i === 0 ? true : 'low', '100vw') ?>
            <figcaption class="hg-hero__place"><?= hg_icon('pin') ?><?php if ($sl[3]) { ?><a href="<?= hg_e($sl[3]) ?>"<?= $i === 0 ? '' : ' tabindex="-1"' ?>><?= hg_e($sl[2]) ?></a><?php } else { ?><span><?= hg_e($sl[2]) ?></span><?php } ?></figcaption>
        </figure>
        <?php } ?>
    </div>
    <div class="hg-container hg-hero__inner">
        <p class="hg-eyebrow">Holiday packages across India &amp; abroad</p>
        <h1 class="hg-h1" id="hero-title">Your journey. <span>Your way.</span></h1>
        <p class="hg-hero__sub">Day-by-day itineraries for Kashmir, Char Dham, Himachal, Uttarakhand, Kerala, Ladakh, Dubai and more — planned by the Holiday Guru Travel team from our office in Noida.</p>
        <div class="hg-hero__ctas">
            <a class="hg-btn hg-btn--primary" href="/tours">Explore holiday packages</a>
            <a class="hg-btn hg-btn--light" href="/customized-holidays">Plan my trip</a>
        </div>
        <ul class="hg-hero__benefits">
            <li><?= hg_icon('route') ?>Day-by-day itineraries</li>
            <li><?= hg_icon('check') ?>Customize any package</li>
            <li><?= hg_icon('whatsapp') ?>24×7 support on WhatsApp</li>
            <li><?= hg_icon('pin') ?>Office in Noida</li>
        </ul>
    </div>
    <div class="hg-hero__controls" data-hg-slider-controls hidden>
        <button type="button" class="hg-hero__btn" data-hg-slide-prev aria-label="Previous photo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg></button>
        <span class="hg-hero__dots"><?php foreach ($heroSlides as $i => $sl) { ?><button type="button" class="hg-hero__dot" data-hg-slide-to="<?= $i ?>" aria-label="Show photo <?= $i + 1 ?>: <?= hg_e($sl[2]) ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>></button><?php } ?></span>
        <button type="button" class="hg-hero__btn" data-hg-slide-next aria-label="Next photo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg></button>
        <button type="button" class="hg-hero__btn" data-hg-slide-pause aria-label="Pause slideshow" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path class="hg-hero__pause" d="M9 5v14M15 5v14"/><path class="hg-hero__play" d="M8 5l11 7-11 7z"/></svg></button>
    </div>
</section>

<section class="hg-searchwidget" aria-labelledby="search-title">
    <div class="hg-container">
        <form class="hg-searchwidget__card" action="/tours" method="get" role="search">
            <h2 class="hg-searchwidget__title" id="search-title"><?= hg_icon('search') ?>Search holiday packages</h2>
            <div class="hg-searchwidget__grid">
                <div class="hg-field">
                    <label for="sw-q">Where do you want to go?</label>
                    <input id="sw-q" name="destination" type="search" placeholder="Destination, city or package" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="sw-q-list" data-hg-autocomplete data-hg-ac-fill>
                    <ul class="hg-ac" id="sw-q-list" role="listbox" aria-label="Suggestions" hidden></ul>
                </div>
                <div class="hg-field">
                    <label for="sw-date">Travel date</label>
                    <input id="sw-date" name="date" type="date" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="hg-field">
                    <label for="sw-adults">Adults</label>
                    <select id="sw-adults" name="adults"><?php for ($i = 1; $i <= 9; $i++) { echo '<option value="' . $i . '"' . ($i === 2 ? ' selected' : '') . '>' . $i . ' Adult' . ($i > 1 ? 's' : '') . '</option>'; } ?></select>
                </div>
                <div class="hg-field">
                    <label for="sw-children">Children</label>
                    <select id="sw-children" name="children"><?php for ($i = 0; $i <= 6; $i++) { echo '<option value="' . $i . '">' . $i . ($i === 1 ? ' Child' : ' Children') . '</option>'; } ?></select>
                </div>
                <div class="hg-field">
                    <label for="sw-from">Departure city</label>
                    <select id="sw-from" name="departure"><option value="">Any / at destination</option><option value="delhi">Delhi</option><option value="haridwar">Haridwar</option></select>
                </div>
                <button class="hg-btn hg-btn--primary" type="submit">Explore holiday packages <?= hg_icon('arrow') ?></button>
            </div>
            <p class="hg-searchwidget__hint">Try “Kashmir”, “Char Dham”, “Munnar” or “Dubai”. Departure-city packages currently start from Delhi or Haridwar.</p>
        </form>
    </div>
</section>

<section class="hg-section" aria-labelledby="india-title">
    <div class="hg-container">
        <?= hg_section_head('Explore India', 'Holidays across India', 'From the Kashmir valley and the Char Dham to Kerala’s backwaters — explore handpicked holidays across India.', array('View all India tours', '/domestic-holidays'), 'india-title') ?>
        <div class="hg-grid hg-grid--dest">
            <?php foreach ($india as $g) echo hg_destination_card($g); ?>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tint" aria-labelledby="world-title">
    <div class="hg-container">
        <?= hg_section_head('Explore the world', 'International holidays', 'Dubai, Singapore &amp; Malaysia and the Maldives, with hotels, transfers and sightseeing planned together.', array('View all international tours', '/international-holidays'), 'world-title') ?>
        <div class="hg-grid hg-grid--dest">
            <?php foreach ($world as $g) echo hg_destination_card($g); ?>
        </div>
    </div>
</section>

<section class="hg-section" aria-labelledby="spec-title">
    <div class="hg-container">
        <?= hg_section_head('Speciality holidays', 'Find your kind of holiday', 'Themes appear here once each has its own, genuinely different content and packages.', null, 'spec-title') ?>
        <div class="hg-speciality">
            <article class="hg-feature">
                <div class="hg-feature__body">
                    <p class="hg-eyebrow">Available now</p>
                    <h3>Pilgrimage tours</h3>
                    <p>Char Dham, Do Dham (Kedarnath &amp; Badrinath), Yamunotri–Gangotri and Amarnath Yatra — by road or with helicopter options, from Haridwar, Delhi or Dehradun.</p>
                    <ul><li>Road distances and trek lengths in every itinerary</li><li>Pony, palki and doli options explained</li><li>Helicopter packages for Char Dham and Amarnath</li></ul>
                    <a class="hg-btn hg-btn--primary" href="/religious-tour">Explore pilgrimage tours</a>
                </div>
                <?= hg_img('assets/img/destination/chardham1.jpg', 'Char Dham pilgrimage in the Garhwal Himalaya', 600, 600) ?>
            </article>
            <div class="hg-card">
                <h3>Plan on request</h3>
                <p class="hg-muted">We plan these holidays around your dates, group and budget:</p>
                <ul class="hg-tags">
                    <?php foreach (array('Family', 'Honeymoon', 'Adventure', 'Luxury', 'Beach', 'Senior citizen', 'Women special', 'Group tours', 'Weekend getaways') as $t) { ?>
                    <li><?= hg_e($t) ?></li>
                    <?php } ?>
                </ul>
                <p class="hg-muted" style="margin-top:16px"><a href="/customized-holidays">Tell us what you need</a> and a travel expert builds the itinerary with you.</p>
            </div>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tint" aria-labelledby="featured-title">
    <div class="hg-container">
        <?= hg_section_head('Featured tours', 'Handpicked holidays to start from', 'Each tour shows the full day-by-day plan, inclusions and exclusions. Prices are quoted for your dates and group size.', array('Explore tours', '/tours'), 'featured-title') ?>
        <?= hg_package_grid($featured) ?>
    </div>
</section>

<section class="hg-section hg-band" aria-labelledby="custom-title">
    <div class="hg-container">
        <?= hg_section_head('Customized holidays', 'Your trip, planned around you', 'Choose a package and change it, or start from a blank page. You talk to a person, not a booking engine.', null, 'custom-title') ?>
        <ol class="hg-steps">
            <li><strong>Share your plan</strong>Destination, dates, travellers, hotel preference and budget — by form, phone or WhatsApp.</li>
            <li><strong>Talk to an expert</strong>We suggest routes, durations and hotels that fit, and answer your questions.</li>
            <li><strong>Get your itinerary &amp; quote</strong>A day-by-day plan with clear inclusions and exclusions.</li>
            <li><strong>Confirm &amp; travel</strong>35% advance to confirm, balance before departure; booking voucher on payment.</li>
        </ol>
        <p style="margin-top:28px"><a class="hg-btn hg-btn--primary" href="/customized-holidays">Plan my trip</a></p>
    </div>
</section>

<section class="hg-section" aria-labelledby="why-title">
    <div class="hg-container">
        <?= hg_section_head('Why Holiday Guru Travel', 'What you can expect from us', '', null, 'why-title') ?>
        <div class="hg-grid hg-grid--3">
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('route') ?></span><h3>Read the plan first</h3><p>Every package page shows the route, day by day, before you share any details.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('temple') ?></span><h3>Pilgrimage know-how</h3><p>Char Dham, Do Dham and Amarnath itineraries with trek distances, altitudes and helicopter options.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('bed') ?></span><h3>One plan, end to end</h3><p>Hotels, private cab transfers and sightseeing in one itinerary, with inclusions listed line by line.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('phone') ?></span><h3>Talk to a person</h3><p>Call or WhatsApp <?= hg_e(HG_PHONE_DISPLAY) ?> to speak with the team that plans your trip.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('check') ?></span><h3>Clear booking terms</h3><p>35% advance to confirm, balance before departure, voucher on payment. No cash payments.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('shield') ?></span><h3>Who we are</h3><p>Holiday Guru Travel is operated by <?= hg_e(HG_LEGAL_NAME) ?> from our office in Sector 27, Noida.</p></div>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tight hg-section--tint" aria-labelledby="proof-title">
    <div class="hg-container">
        <h2 class="hg-sr" id="proof-title">Holiday Guru Travel at a glance</h2>
        <div class="hg-stats">
            <div class="hg-stat"><span class="hg-stat__value">Day by day</span><span class="hg-stat__label">Every itinerary</span><span class="hg-stat__src">Inclusions &amp; exclusions listed</span></div>
            <div class="hg-stat"><span class="hg-stat__value">Your way</span><span class="hg-stat__label">Customize any tour</span><span class="hg-stat__src">Hotels, nights, sightseeing</span></div>
            <div class="hg-stat"><span class="hg-stat__value">24×7</span><span class="hg-stat__label">WhatsApp support</span><span class="hg-stat__src"><?= hg_e(HG_PHONE_DISPLAY) ?></span></div>
            <div class="hg-stat"><span class="hg-stat__value">Noida</span><span class="hg-stat__label">Office in Sector 27</span><span class="hg-stat__src"><a href="/contact">Address &amp; map</a></span></div>
        </div>
    </div>
</section>

<?php if (HG_DEV_MODE) { ?>
<section class="hg-section" aria-labelledby="reviews-title">
    <div class="hg-container">
        <?= hg_section_head('Traveller reviews', 'What our travellers say', '', null, 'reviews-title') ?>
        <?= hg_dev_note('Only genuine, verifiable reviews will appear here (e.g. from the Google Business Profile, with permission). No review data is available yet, so this section is hidden in production.') ?>
    </div>
</section>
<?php } ?>

<section class="hg-section" aria-labelledby="inspo-title">
    <div class="hg-container">
        <?= hg_section_head('Travel inspiration', 'Plan with confidence', '', null, 'inspo-title') ?>
        <div class="hg-grid hg-grid--3">
            <a class="hg-guide" href="/travel-guide/kashmir"><?= hg_img('assets/img/destination/SrinagarGulmargPahalgamTour2.jpg', '', 640, 360) ?><div class="hg-guide__body"><p class="hg-guide__kicker">Travel guide</p><h3>Kashmir travel guide</h3><p>Seasons, how to reach, getting around, where to stay and practical tips.</p></div></a>
            <a class="hg-guide" href="/religious-tour"><?= hg_img('assets/img/destination/chardham1.jpg', '', 640, 360) ?><div class="hg-guide__body"><p class="hg-guide__kicker">Pilgrimage</p><h3>Planning Char Dham &amp; Amarnath</h3><p>Registration, routes, trek lengths and helicopter options explained.</p></div></a>
            <a class="hg-guide" href="/india-tours"><?= hg_img('', '', 640, 360) ?><div class="hg-guide__body"><p class="hg-guide__kicker">Visiting India</p><h3>India for international travellers</h3><p>Arrival, e-Visa, seasons and private tours for first-time visitors.</p></div></a>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tint" aria-labelledby="faq-title">
    <div class="hg-container hg-layout">
        <div>
            <?= hg_section_head('FAQs', 'Questions travellers ask us', '', array('All FAQs', '/faqs'), 'faq-title') ?>
            <?= hg_faq($faqs) ?>
        </div>
        <div class="hg-sidecard" id="enquire">
            <?= hg_enquiry_form('home-enquiry', 'Get a free quote', array('enquiry_type' => 'Homepage enquiry'), true) ?>
        </div>
    </div>
</section>

<?php hg_layout_end(); ?>
