<?php
// India tours for foreign travellers (inbound landing, Phase 1).
// STATUS: draft — noindex until the owner approves the content.
require __DIR__ . '/include/ui/core.php';

$status = hg_page_status('/india-tours');
$firstTrips = array_values(array_filter(array_map('hg_package', array('srinagar-gulmarg-pahalgam-tour-package-5-days', 'munnar-thekkady-alleppey-05-days', 'shimla-manali-tour-06-days', 'jewels-of-leh-ladakh-package-5n6d', 'haridwar-rishikesh-tour-03-days', 'delightful-goa-tour-3n-4d'))));
$picks = array('kashmir', 'kerala', 'himachal', 'uttarakhand', 'ladakh', 'goa', 'south-india', 'sikkim-darjeeling');
$groups = array_values(array_filter(array_map('hg_group', $picks), function ($g) { return $g && $g['count'] > 0; }));

$faqs = array(
    array('Do I need a visa to visit India?', '<p>Most foreign nationals need a valid passport and an Indian visa. Many nationalities can apply online for an e-Visa through the Government of India’s official portal, <a href="https://indianvisaonline.gov.in/evisa/" target="_blank" rel="noopener">indianvisaonline.gov.in</a>. Check the current rules for your nationality before you book flights.</p>'),
    array('Can you arrange everything once I land in India?', '<p>Yes. Our packages start on arrival — for example, pick-up at Srinagar or Cochin airport — and include hotels, a private cab for transfers and sightseeing, and the meals listed on each package. International flights are not included; we can help with domestic flights and trains within India.</p>'),
    array('Can I combine several regions in one trip?', '<p>Yes. Tell us your arrival and departure cities and how long you have, and we plan a route across regions — for example the Kashmir valley with Himachal, or Kerala with Ooty and Mysore.</p>'),
    array('How do I pay from abroad?', '<p>Our standard terms list net banking, IMPS, NEFT, cheque and UPI, which are Indian payment methods. If you are paying from outside India, ask us for the options available for your booking.</p>'),
    array('Which airports do your tours start from?', '<p>Each itinerary starts where the tour begins — for example Srinagar for Kashmir, Leh for Ladakh, Cochin for Kerala, Bagdogra or New Jalpaiguri for Darjeeling and Sikkim, and Goa. Most international visitors fly into Delhi or Mumbai and connect by a domestic flight; we can quote domestic flights and trains.</p>'),
    array('What documents should I carry?', '<p>Your passport and valid Indian visa for every traveller — hotels in India register foreign guests using them.</p>'),
);
$country = '<div class="hg-field"><label for="enq-country">Country of residence</label><input id="enq-country" name="country" autocomplete="country-name" maxlength="80"></div>';

hg_layout_start(array(
    'title' => 'India Tour Packages for Foreign Travellers | Holiday Guru Travel',
    'description' => 'Private India tours for international visitors: Kashmir, Kerala, the Himalayas, Goa and more, with hotels, private transfers and sightseeing arranged by our team in Noida.',
    'path' => '/india-tours', 'index' => $status === 'approved',
    'breadcrumbs' => array(array('Home', '/'), array('India Tours for Foreign Travellers', null)),
    'schema' => array(hg_faq_schema($faqs)),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow">Inbound · For international visitors</p>
        <h1 class="hg-h1" id="page-title">India tours for foreign travellers</h1>
        <p class="hg-lead">Private tours of India planned around your arrival: hotels, a private cab with driver, sightseeing and a day-by-day itinerary — arranged by our team in Noida, Delhi NCR, and supported on WhatsApp.</p>
        <div class="hg-pagehead__actions">
            <a class="hg-btn hg-btn--primary" href="#plan">Plan my India trip</a>
            <a class="hg-btn hg-btn--outline" href="<?= hg_e(hg_whatsapp_href("Hello Holiday Guru Travel,\nI am planning a trip to India from abroad.")) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp') ?>WhatsApp us</a>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tight" aria-labelledby="how-title">
    <div class="hg-container">
        <?= hg_section_head('How it works', 'What we arrange for you', '', null, 'how-title') ?>
        <div class="hg-grid hg-grid--4">
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('plane') ?></span><h3>From your arrival</h3><p>Packages start with pick-up at the arrival airport or station and end with drop-off for your onward journey.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('car') ?></span><h3>Private cab</h3><p>Transfers and sightseeing by private cab, with tolls, parking and driver allowance where the package lists them.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('bed') ?></span><h3>Hotels you choose</h3><p>Hotel category agreed with you; houseboat nights in Kashmir and Kerala on selected itineraries.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('whatsapp') ?></span><h3>Support on WhatsApp</h3><p>Reach our team on <?= hg_e(HG_PHONE_DISPLAY) ?> before and during your trip.</p></div>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tint" aria-labelledby="dest-title">
    <div class="hg-container">
        <?= hg_section_head('Where to go', 'Popular regions for a first visit', 'Start from one of our itineraries and we adapt it to your arrival city and dates.', array('All India tours', '/domestic-holidays'), 'dest-title') ?>
        <div class="hg-grid hg-grid--dest"><?php foreach ($groups as $g) echo hg_destination_card($g); ?></div>
    </div>
</section>

<section class="hg-section" aria-labelledby="first-title">
    <div class="hg-container">
        <?= hg_section_head('Ideas for a first visit', 'Suggested itineraries', 'Real itineraries we run, each adaptable to your arrival city, dates and pace.', array('Search all packages', '/tours'), 'first-title') ?>
        <?= hg_package_grid($firstTrips) ?>
    </div>
</section>

<section class="hg-section hg-section--tint" aria-labelledby="season-title">
    <div class="hg-container hg-narrow">
        <h2 class="hg-h2" id="season-title">Where to go, month by month</h2>
        <p class="hg-summary"><strong>In short:</strong> head south to Kerala and Goa from October to March, north to the Himalayan hill stations and Kashmir from March to June, and to Ladakh from May to September.</p>
        <div class="hg-tablewrap" tabindex="0" role="region" aria-label="Where to go by season"><table class="hg-table">
            <thead><tr><th scope="col">Months</th><th scope="col">Good choices</th><th scope="col">Why</th></tr></thead>
            <tbody>
                <tr><td>October – March</td><td><a href="/tours/kerala">Kerala</a>, <a href="/tours/goa">Goa</a>, <a href="/tours/south-india">Mysore &amp; Coorg</a></td><td>The cool, dry season in the south; December–January is peak.</td></tr>
                <tr><td>December – February</td><td><a href="/tours/kashmir">Kashmir</a>, <a href="/tours/himachal">Himachal</a></td><td>Snow in Gulmarg, Manali and Shimla; very cold, some roads close.</td></tr>
                <tr><td>March – June</td><td><a href="/tours/kashmir">Kashmir</a>, <a href="/tours/himachal">Himachal</a>, <a href="/tours/uttarakhand">Uttarakhand</a>, <a href="/tours/sikkim-darjeeling">Darjeeling &amp; Sikkim</a></td><td>Pleasant hill-station weather while the plains get hot.</td></tr>
                <tr><td>May – September</td><td><a href="/tours/ladakh">Leh Ladakh</a></td><td>The high roads to Nubra and Pangong are open.</td></tr>
                <tr><td>June – September</td><td><a href="/tours/kerala">Kerala</a> (monsoon)</td><td>Green, quieter and good value; heavy rain in the Himalaya can close roads.</td></tr>
            </tbody>
        </table></div>
    </div>
</section>

<section class="hg-section" aria-labelledby="prac-title">
    <div class="hg-container">
        <?= hg_section_head('Before you travel', 'Practical information', '', null, 'prac-title') ?>
        <div class="hg-grid hg-grid--3">
            <div class="hg-card"><h3>Visa and documents</h3><p>Carry your passport and valid Indian visa; hotels register foreign guests with them. Many nationalities can apply for an e-Visa on the official portal, <a href="https://indianvisaonline.gov.in/evisa/" target="_blank" rel="noopener">indianvisaonline.gov.in</a>. Foreign nationals need a special permit for Sikkim, which we help arrange.</p></div>
            <div class="hg-card"><h3>Money</h3><p>The currency is the Indian rupee (INR). Cards are widely accepted in cities and hotels; carry some cash for small shops, ponies and tips, especially in the mountains where ATMs are fewer.</p></div>
            <div class="hg-card"><h3>Phones and connectivity</h3><p>Mobile data works in most tourist areas, with weaker coverage in high valleys. Prepaid SIMs issued outside Jammu &amp; Kashmir generally do not work there.</p></div>
            <div class="hg-card"><h3>Health and altitude</h3><p>Ladakh, Kedarnath and North Sikkim are at high altitude — rest on arrival and consult a doctor before travel if you have heart, lung or blood-pressure conditions. Travel insurance is not included in our packages; we recommend it.</p></div>
            <div class="hg-card"><h3>Temples and customs</h3><p>Dress modestly at temples, mosques and gurdwaras, remove shoes where asked, and cover your head at gurdwaras such as Amritsar’s Golden Temple.</p></div>
            <div class="hg-card"><h3>Booking and payment</h3><p>Our booking terms ask for a 35% advance to confirm, with the balance before departure; we issue a booking voucher once payment is received. If you are paying from outside India, ask us for the options available.</p></div>
        </div>
    </div>
</section>

<section class="hg-section" id="plan" aria-labelledby="plan-title" style="scroll-margin-top:calc(var(--hg-sticky) + 16px)">
    <div class="hg-container hg-layout">
        <div>
            <h2 class="hg-h2" id="plan-title">Questions from international visitors</h2>
            <?= hg_faq($faqs) ?>
        </div>
        <aside class="hg-layout__side">
            <div class="hg-sidecard"><?= hg_enquiry_form('inbound-form', 'Plan my India trip', array('enquiry_type' => 'India tour (foreign traveller)'), true, $country) ?></div>
        </aside>
    </div>
</section>
<?php hg_layout_end(); ?>
