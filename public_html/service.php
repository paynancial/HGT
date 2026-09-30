<?php
// Car rental service page (Phase 1). Vehicle list from the previous page; the old
// per-km rates had no date or validity, so rates are quoted on request until the
// owner publishes current rates (pricing rule: never show unconfirmed prices).
require __DIR__ . '/include/ui/core.php';

$fleet = array(
    'Mini & sedan cars' => array('AC Swift Dzire (4+1 driver)', 'AC Hyundai Xcent (4+1 driver)', 'AC Chevrolet Sail (4+1 driver)', 'AC Honda Amaze (4+1 driver)', 'AC Toyota Etios (4+1 driver)', 'AC Maruti Suzuki Ciaz (4+1 driver)', 'AC Toyota Corolla (4+1 driver)', 'AC Honda City (4+1 driver)', 'AC Hyundai Verna (4+1 driver)'),
    'SUVs' => array('AC Chevrolet Enjoy (7+1 driver)', 'AC Chevrolet Tavera (9+1 driver)', 'AC Toyota Innova (7+1 driver)', 'AC Toyota Innova Crysta (7+1 driver)'),
    'AC Winger / Force Traveller' => array('AC 9-seater Tata Winger (9+1 driver)', 'AC 13-seater Tata Winger (13+1 driver)', '13-seater AC Force Traveller (13+1 driver)', '17-seater AC Force Traveller (17+1 driver)', '26-seater AC Force Traveller (26+1 driver)'),
    'AC luxury tempo traveller' => array('15-seater AC luxury Force Traveller (15+1 driver)', '18-seater AC luxury Force Traveller (18+1 driver)'),
    'Non-AC bus / tourist coach' => array('21-seater mini bus (21+1 driver)', '35-seater coach (35+1 driver)', '42-seater bus (42+1 driver)'),
    'AC luxury bus / tourist coach' => array('AC 41-seater + 14 sleeper', '45-seater AC Volvo bus (45+1 driver)'),
);
$faqs = array(
    array('Where do you provide cabs from?', '<p>From Delhi and Haridwar, including airport and railway station pick-ups, for local use and outstation trips such as Himachal and the Char Dham Yatra.</p>'),
    array('How is the rental charged?', '<p>Rates depend on the vehicle, the route, the number of days and the season. Local use is typically quoted per 80 km / 8 hours and outstation trips per km with a daily minimum. Ask us for the current rate for your trip.</p>'),
    array('Can I book a tempo traveller or bus for a group?', '<p>Yes — from 9-seater Tata Wingers and Force Travellers to 45-seater AC Volvo coaches.</p>'),
    array('Is the driver included?', '<p>Yes. Every vehicle comes with a driver; seating shown is passengers plus the driver.</p>'),
);

hg_layout_start(array(
    'title' => 'Car Rental Services from Delhi | Holiday Guru Travel',
    'description' => 'Hire a car, SUV, tempo traveller or bus with driver from Delhi and Haridwar for local use, airport transfers, Himachal trips and the Char Dham Yatra. Ask for the current rate.',
    'path' => '/service',
    'breadcrumbs' => array(array('Home', '/'), array('Car rental', null)),
    'schema' => array(hg_faq_schema($faqs)),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow">Services</p>
        <h1 class="hg-h1" id="page-title">Car rental services from Delhi</h1>
        <p class="hg-lead">Sedans, SUVs, tempo travellers and buses with driver — for airport and station pick-ups, local use, and outstation trips from Delhi and Haridwar to Himachal and the Char Dham Yatra.</p>
        <div class="hg-pagehead__actions">
            <a class="hg-btn hg-btn--primary" href="#rental-enquiry">Get a rental quote</a>
            <a class="hg-btn hg-btn--outline" href="<?= hg_e(hg_whatsapp_href("Hi Holiday Guru Travel,\nI would like a quote for a car rental.")) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp') ?>WhatsApp us</a>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tight" aria-labelledby="fleet-title">
    <div class="hg-container hg-layout">
        <div>
            <h2 class="hg-h2" id="fleet-title">Our fleet</h2>
            <p class="hg-muted">All vehicles are air-conditioned unless marked non-AC, and come with a driver. Rates depend on the vehicle, route, days and season, and are quoted for your trip.</p>
            <div class="hg-grid hg-grid--2">
                <?php foreach ($fleet as $cat => $list) { ?>
                <div class="hg-card">
                    <h3><?= hg_icon('car') ?> <?= hg_e($cat) ?></h3>
                    <ul class="hg-checks"><?php foreach ($list as $v) { ?><li><?= hg_e($v) ?></li><?php } ?></ul>
                    <p class="hg-muted" style="margin-top:12px;font-size:14px"><strong>Rate:</strong> on request</p>
                </div>
                <?php } ?>
            </div>
            <h2 class="hg-h2" style="margin-top:40px">Questions</h2>
            <?= hg_faq($faqs) ?>
        </div>
        <aside class="hg-layout__side" id="rental-enquiry">
            <div class="hg-sidecard"><?= hg_enquiry_form('rental-form', 'Get a rental quote', array('enquiry_type' => 'Car rental'), true) ?></div>
        </aside>
    </div>
</section>
<?php hg_layout_end(); ?>
