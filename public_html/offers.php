<?php
// Offers page (Phase 1). No offers are published yet: offer prices, discounts and validity
// must come from the owner (no fabricated deals). Status: include/data/page-status.php.
require __DIR__ . '/include/ui/core.php';

$status = hg_page_status('/offers');
$picks = array_values(array_filter(array_map('hg_group', array('kashmir', 'himachal', 'kerala', 'goa', 'ladakh', 'dubai')), function ($g) { return $g && $g['count'] > 0; }));

hg_layout_start(array(
    'title' => 'Holiday Offers | Holiday Guru Travel',
    'description' => 'Current holiday offers from Holiday Guru Travel. Ask a travel expert for the best available price for your dates, hotel category and group size.',
    'path' => '/offers', 'index' => $status === 'approved',
    'breadcrumbs' => array(array('Home', '/'), array('Offers', null)),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow">Offers</p>
        <h1 class="hg-h1" id="page-title">Holiday offers</h1>
        <p class="hg-lead">No offers are published right now. Prices depend on your dates, hotel category and group size, so ask a travel expert for the best available price for your trip.</p>
        <div class="hg-pagehead__actions">
            <a class="hg-btn hg-btn--primary" href="#offer-enquiry">Ask for the best price</a>
            <a class="hg-btn hg-btn--outline" href="<?= hg_e(hg_whatsapp_href("Hi Holiday Guru Travel,\nDo you have any current holiday offers?")) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp') ?>WhatsApp us</a>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tight" aria-labelledby="dest-title">
    <div class="hg-container hg-layout">
        <div>
            <h2 class="hg-h2" id="dest-title">Popular destinations</h2>
            <p class="hg-muted">Browse our itineraries and ask for a quote on any of them.</p>
            <div class="hg-grid hg-grid--dest"><?php foreach ($picks as $g) echo hg_destination_card($g); ?></div>
        </div>
        <aside class="hg-layout__side" id="offer-enquiry">
            <div class="hg-sidecard"><?= hg_enquiry_form('offer-form', 'Ask for the best price', array('enquiry_type' => 'Offer enquiry'), true) ?></div>
        </aside>
    </div>
</section>
<?php hg_layout_end(); ?>
