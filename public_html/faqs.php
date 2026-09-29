<?php
// FAQs (Phase 1). Answers come from the company's package booking terms and
// the package pages. STATUS: draft — noindex until the owner approves.
require __DIR__ . '/include/ui/core.php';

$status = 'draft';
$sections = array(
    'booking' => array('Booking and payment', hg_booking_faqs()),
    'packages' => array('Packages and pricing', array(
        array('Why do packages show “Price on request”?', '<p>Prices depend on your dates, hotel category, number of travellers and season, so we quote each trip for you. Published rates will appear on package pages once they are approved.</p>'),
        array('Is GST included?', '<p>Many of our packages list GST at 5% as extra, on a GST bill. Each package page shows whether GST is included or extra.</p>'),
        array('How are extra adults and children charged?', '<p>Many packages charge an extra adult at 35% and an extra child at 25% of the package cost, with children below 5 complimentary; some use different rates. Each package’s terms show its rates.</p>'),
        array('What if a listed hotel is unavailable?', '<p>Our package terms provide for an alternative hotel of similar standard. Check the terms on your package page.</p>'),
        array('Is the vehicle at our disposal all day?', '<p>No. Transport is provided as per the itinerary, and sightseeing depends on time available.</p>'),
    )),
    'cancel' => array('Changes and cancellation', array(
        array('How are cancellation charges calculated?', '<p>Cancellation charges are calculated on the gross tour cost and depend on the date of departure and the date of cancellation. Charges for transport tickets follow the rules of the airline, railway or operator.</p>'),
        array('When is a refund paid?', '<p>Any refund due under the cancellation policy is paid after we receive the refund from the respective suppliers; processing charges are deducted.</p>'),
    )),
    'travel' => array('Travel', array(
        array('Do packages include flights or trains?', '<p>The standard package cost excludes airfare, train fare and bus fare unless the package inclusions say otherwise (a few Dubai and Volvo packages include tickets). We can quote tickets with your trip.</p>'),
        array('Which destinations do you cover?', '<p>India — Kashmir, Leh Ladakh, Himachal, Uttarakhand, Char Dham, Amarnath, Darjeeling &amp; Sikkim, Kerala, Ooty–Mysore–Coorg and Goa — and abroad: Dubai, Singapore &amp; Malaysia and the Maldives. See <a href="/domestic-holidays">India</a> and <a href="/international-holidays">international</a> holidays.</p>'),
    )),
);
$all = array();
foreach ($sections as $s) $all = array_merge($all, $s[1]);

hg_layout_start(array(
    'title' => 'FAQs — Booking, Payment and Cancellation | Holiday Guru Travel',
    'description' => 'Answers about booking a holiday package with Holiday Guru Travel: advance payment, payment methods, GST, extra travellers, flights, changes and cancellation.',
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
        <p class="hg-lead">Booking, payment, pricing and cancellation — answered from our package terms. Can’t find your answer? <a href="/contact">Contact us</a>.</p>
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
<?= hg_cta_band('Still have a question?', 'Call or WhatsApp +91 99717 54265 — a travel expert will help.') ?>
<?php hg_layout_end(); ?>
