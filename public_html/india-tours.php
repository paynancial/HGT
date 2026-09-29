<?php
// India tours for foreign travellers (inbound landing, Phase 1).
// STATUS: draft — noindex until the owner approves the content.
require __DIR__ . '/include/ui/core.php';

$status = 'draft';
$picks = array('kashmir', 'kerala', 'himachal', 'uttarakhand', 'ladakh', 'goa', 'south-india', 'sikkim-darjeeling');
$groups = array_values(array_filter(array_map('hg_group', $picks), function ($g) { return $g && $g['count'] > 0; }));

$faqs = array(
    array('Do I need a visa to visit India?', '<p>Most foreign nationals need a valid passport and an Indian visa. Many nationalities can apply online for an e-Visa through the Government of India’s official portal, <a href="https://indianvisaonline.gov.in/evisa/" target="_blank" rel="noopener">indianvisaonline.gov.in</a>. Check the current rules for your nationality before you book flights.</p>'),
    array('Can you arrange everything once I land in India?', '<p>Yes. Our packages start on arrival — for example, pick-up at Srinagar or Cochin airport — and include hotels, a private cab for transfers and sightseeing, and the meals listed on each package. International flights are not included; we can help with domestic flights and trains within India.</p>'),
    array('Can I combine several regions in one trip?', '<p>Yes. Tell us your arrival and departure cities and how long you have, and we plan a route across regions — for example the Kashmir valley with Himachal, or Kerala with Ooty and Mysore.</p>'),
    array('How do I pay from abroad?', '<p>Our standard terms list net banking, IMPS, NEFT, cheque and UPI, which are Indian payment methods. If you are paying from outside India, ask us for the options available for your booking.</p>'),
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
