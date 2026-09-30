<?php
// FAQs. Answers come from the company's package terms, package pages and destination
// pages. Publishing status: include/data/page-status.php (noindex until approved).
require __DIR__ . '/include/ui/core.php';

$status = hg_page_status('/faqs');
$dest = function ($key) {
    $g = hg_group($key);
    $c = $g && !empty($g['content']) ? include __DIR__ . '/include/content/' . basename($g['content']) . '.php' : null;
    return array($g, $c);
};
$bestTime = array();
foreach (array('kashmir', 'himachal', 'uttarakhand', 'ladakh', 'kerala', 'goa', 'char-dham', 'dubai') as $k) {
    list($g, $c) = $dest($k);
    if ($g && $c) $bestTime[] = '<li><a href="' . hg_e($g['hub_url']) . '">' . hg_e($g['name']) . '</a>: ' . hg_e($c['best_time_answer']) . '</li>';
}

$sections = array(
    'booking' => array('Booking and payment', array_merge(hg_booking_faqs(), array(
        array('Can I pay online on the website?', '<p>Not yet. A travel expert confirms your itinerary and price, then shares the payment details. See our <a href="/payment-policy">payment policy</a>.</p>'),
        array('When will I receive my booking confirmation?', '<p>We issue a booking voucher once we receive your payment.</p>'),
        array('Can you hold a price for me?', '<p>Some packages note that prices are dynamic and can change until the booking is put on hold or vouchered. Ask your travel expert how long a quote is valid.</p>'),
    ))),
    'packages' => array('Packages and pricing', array(
        array('Why do packages show “Price on request”?', '<p>Prices depend on your dates, hotel category, number of travellers and season, so we quote each trip for you. Published rates will appear on package pages once they are approved.</p>'),
        array('What do packages usually include?', '<p>Hotel stays, a private cab (or the listed transport) for transfers and sightseeing, and the meals shown on each package. Many packages also include tolls, parking, driver allowance and applicable taxes. Every package page lists its exact inclusions.</p>'),
        array('What is usually not included?', '<p>Items many packages list as excluded: air or train fare, mini-bar use, optional trips outside the itinerary, personal expenses (shopping, medical kit, adventure activities), travel insurance, and costs caused by changes due to bad weather, road blocks or flight cancellations. Check the exclusions on your package page.</p>'),
        array('Is GST included?', '<p>Many of our packages list GST at 5% as extra, on a GST bill. Each package page shows whether GST is included or extra.</p>'),
        array('How are extra adults and children charged?', '<p>Many packages charge an extra adult at 35% and an extra child at 25% of the package cost, with children below 5 complimentary; some use different rates. Each package’s terms show its rates.</p>'),
        array('What if a listed hotel is unavailable?', '<p>Our package terms provide for an alternative hotel of similar standard. Check the terms on your package page.</p>'),
        array('Is the vehicle at our disposal all day?', '<p>No. Transport is provided as per the itinerary, and sightseeing depends on the time available.</p>'),
        array('Can I change a package?', '<p>Yes — hotels, nights, sightseeing or destinations. Use the enquiry form on any package, or plan a <a href="/customized-holidays">customised tour</a>.</p>'),
    )),
    'cancel' => array('Changes, cancellation and refunds', array(
        array('How are cancellation charges calculated?', '<p>On the gross tour cost, depending on the date of departure and the date of cancellation. Where a package lists a schedule, it is 25% of booking value at least 21 days before departure, 50% at 8–20 days, and 100% at 7 days or less. See the <a href="/cancellation-policy">cancellation policy</a>.</p>'),
        array('What about air, train or bus tickets?', '<p>Cancellation charges for tickets follow the rules of the airline, railway or operator.</p>'),
        array('When is a refund paid?', '<p>Any refund due is paid after we receive the refund from the respective suppliers, and processing charges are deducted. See the <a href="/refund-policy">refund policy</a>.</p>'),
        array('What if weather changes the plan?', '<p>Snow, rain and road closures can change sightseeing on the day; the driver follows what is open. Costs caused by such changes are listed as excluded on most packages.</p>'),
    )),
    'travel' => array('Travel and documents', array(
        array('Do packages include flights or trains?', '<p>The standard package cost excludes airfare, train fare and bus fare unless the package inclusions say otherwise (a few Dubai and Volvo packages include tickets). We can quote tickets with your trip.</p>'),
        array('What documents should I carry?', '<p>A government photo ID for every traveller for hotel check-ins. Foreign nationals need a passport and valid Indian visa. For trips abroad, carry your passport and the visa for each country.</p>'),
        array('Do I need permits?', '<p>Some areas need permits — for example Nubra and Pangong in Ladakh, and North Sikkim and Tsomgo Lake. Our Ladakh packages include the permit charges; for Sikkim, permits are arranged locally.</p>'),
        array('Is registration needed for a yatra?', '<p>Yes. The Char Dham Yatra and the Amarnath Yatra require registration, and the Amarnath Yatra also needs a compulsory health certificate. We confirm the current process with your booking. See <a href="/religious-tour">pilgrimage tours</a>.</p>'),
        array('Is high altitude a concern?', '<p>Ladakh, Kedarnath, Amarnath and North Sikkim are at high altitude. Our Ladakh itineraries start with a rest day in Leh. Consult a doctor before travelling if you have heart, lung or blood-pressure conditions.</p>'),
        array('Is travel insurance included?', '<p>No — most packages list travel insurance as excluded. We recommend buying cover for medical, evacuation and cancellation.</p>'),
    )),
    'destinations' => array('Destinations and seasons', array(
        array('Which destinations do you cover?', '<p>India — Kashmir, Leh Ladakh, Himachal Pradesh, Uttarakhand, Char Dham, Amarnath, Darjeeling &amp; Sikkim, Kerala, Ooty–Mysore–Coorg and Goa — and abroad: Dubai, Singapore &amp; Malaysia (with Thailand extensions) and the Maldives. See <a href="/domestic-holidays">domestic</a> and <a href="/international-holidays">international</a> holidays.</p>'),
        array('When is the best time to visit?', '<ul>' . implode('', $bestTime) . '</ul>'),
        array('Do you plan trips for foreign visitors to India?', '<p>Yes — see <a href="/india-tours">India tours for foreign travellers</a>.</p>'),
    )),
    'support' => array('Support', array(
        array('How do I reach you?', '<p>Call or WhatsApp <a href="' . hg_e(hg_tel_href()) . '">' . hg_e(HG_PHONE_DISPLAY) . '</a> (WhatsApp 24×7), email <a href="' . hg_e(hg_mailto_href()) . '">' . hg_e(HG_EMAIL_DISPLAY) . '</a>, or visit our office at ' . hg_e(HG_ADDRESS_LINE1) . ', ' . hg_e(HG_ADDRESS_LINE2) . '.</p>'),
        array('Who operates Holiday Guru Travel?', '<p>Holiday Guru Travel is operated by ' . hg_e(HG_LEGAL_NAME) . ', from our office in Sector 27, Noida.</p>'),
    )),
);
$all = array();
foreach ($sections as $s) $all = array_merge($all, $s[1]);

hg_layout_start(array(
    'title' => 'FAQs — Booking, Payment, Cancellation and Travel | Holiday Guru Travel',
    'description' => 'Answers about booking a holiday with Holiday Guru Travel: advance and payment methods, GST, inclusions, cancellation and refunds, documents, permits and the best time to travel.',
    'path' => '/faqs', 'index' => $status === 'approved',
    'breadcrumbs' => array(array('Home', '/'), array('FAQs', null)),
    'schema' => array(hg_faq_schema($all)),
));
$nav = array();
foreach ($sections as $k => $s) $nav[] = array($k, $s[0]);
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow">Help</p>
        <h1 class="hg-h1" id="page-title">Frequently asked questions</h1>
        <p class="hg-lead">Booking, payment, what is included, cancellation, documents and the best time to travel — answered from our package terms. Can’t find your answer? <a href="/contact">Contact us</a>.</p>
    </div>
</section>
<?= hg_section_nav($nav, 'FAQ topics') ?>
<?php foreach ($sections as $k => $s) { ?>
<section class="hg-section hg-section--tight" id="<?= $k ?>" aria-labelledby="h-<?= $k ?>" style="scroll-margin-top:calc(var(--hg-sticky) + 56px)">
    <div class="hg-container hg-narrow">
        <h2 class="hg-h2" id="h-<?= $k ?>"><?= hg_e($s[0]) ?></h2>
        <?= hg_faq($s[1], $k) ?>
    </div>
</section>
<?php } ?>
<?= hg_cta_band('Still have a question?', 'Call or WhatsApp ' . HG_PHONE_DISPLAY . ' — a travel expert will help.') ?>
<?php hg_layout_end(); ?>
