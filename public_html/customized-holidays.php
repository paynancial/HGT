<?php
// Customized holiday request. Posts to the existing Contact Us workflow (mail.php)
// with enquiry type "Customized Holiday"; prefilled from the search context.
require __DIR__ . '/include/ui/core.php';

$S = hg_search_state();
$package = isset($_GET['package']) && is_string($_GET['package']) ? mb_substr(trim($_GET['package']), 0, 150) : '';
$dests = array_map(function ($g) { return $g['name']; }, array_values(array_filter(hg_groups(), function ($g) { return $g['count'] > 0; })));
$sel = function ($a, $b) { return strcasecmp((string) $a, (string) $b) === 0 ? ' selected' : ''; };

$faqs = array(
    array('How does a customized holiday work?', '<p>Tell us where and when you want to travel, who is travelling and your budget. A travel expert calls or WhatsApps you, suggests a day-by-day itinerary and hotels, and revises it until it suits you. Our booking terms ask for a 35% advance to confirm, with the balance before departure; we then issue a booking voucher.</p>'),
    array('Can you add flights or trains?', '<p>Yes. Our standard package cost excludes airfare, train fare and bus fare, but we can quote tickets with your trip. Air and train tickets need full payment at booking.</p>'),
    array('Can I start from one of your packages?', '<p>Yes — open any package and choose “Customize this package”, or name it in the form below.</p>'),
);

hg_layout_start(array(
    'title' => 'Customized Holiday Packages — Plan Your Own Trip | Holiday Guru Travel',
    'description' => 'Plan a customized holiday in India or abroad. Share your destination, dates, travellers and budget, and a Holiday Guru Travel expert builds your itinerary and quote.',
    'path' => '/customized-holidays',
    'index' => empty($_GET),
    'breadcrumbs' => array(array('Home', '/'), array('Customized Holidays', null)),
    'schema' => array(hg_faq_schema($faqs)),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow">Customized holidays</p>
        <h1 class="hg-h1" id="page-title">Plan a holiday your way</h1>
        <p class="hg-lead">Tell us where, when and who is travelling. A travel expert designs the itinerary, hotels and transfers around you.</p>
    </div>
</section>

<section class="hg-section hg-section--tight">
    <div class="hg-container hg-layout">
        <div>
            <form class="hg-form hg-form--card" id="custom-form" data-hg-enquiry novalidate>
                <h2 class="hg-form__title">Your trip</h2>
                <input type="hidden" name="enquiry_type" value="Customized Holiday">
                <?php if ($package !== '') { ?><input type="hidden" name="package" value="<?= hg_e($package) ?>"><p class="hg-notice">Starting from: <strong><?= hg_e($package) ?></strong></p><?php } ?>
                <fieldset class="hg-fieldset"><legend>Trip details</legend>
                <div class="hg-form__grid">
                    <div class="hg-field"><label for="c-dest">Destination</label>
                        <input id="c-dest" name="destination" list="c-dest-list" maxlength="100" value="<?= hg_e($S['destination']) ?>" placeholder="e.g. Kashmir, or several places">
                        <datalist id="c-dest-list"><?php foreach ($dests as $d) { ?><option value="<?= hg_e($d) ?>"><?php } ?></datalist></div>
                    <div class="hg-field"><label for="c-date">Travel date <span class="hg-optional">(optional)</span></label><input id="c-date" name="travel_date" type="date" min="<?= date('Y-m-d') ?>" value="<?= hg_e($S['date']) ?>"></div>
                    <div class="hg-field"><label for="c-duration">Trip length</label><select id="c-duration" name="duration"><option value="">Not sure</option><?php foreach (array('3–4 days', '5–6 days', '7–8 days', '9–12 days', '13+ days') as $d) { ?><option><?= $d ?></option><?php } ?></select></div>
                    <div class="hg-field"><label for="c-dep">Departure city</label><select id="c-dep" name="departure_city"><option value="">Not decided</option><option<?= $sel('delhi', $S['departure']) ?>>Delhi</option><option<?= $sel('haridwar', $S['departure']) ?>>Haridwar</option><option>Other city (tell us below)</option><option>I will reach the destination myself</option></select></div>
                    <div class="hg-field"><label for="c-adults">Adults</label><select id="c-adults" name="adults"><?php for ($i = 1; $i <= 20; $i++) { ?><option<?= $i === $S['adults'] ? ' selected' : '' ?>><?= $i ?></option><?php } ?></select></div>
                    <div class="hg-field"><label for="c-children">Children</label><select id="c-children" name="children"><?php for ($i = 0; $i <= 10; $i++) { ?><option<?= $i === $S['children'] ? ' selected' : '' ?>><?= $i ?></option><?php } ?></select></div>
                </div>
                </fieldset>
                <fieldset class="hg-fieldset"><legend>Preferences</legend>
                <div class="hg-form__grid">
                    <div class="hg-field"><label for="c-type">Holiday type</label><select id="c-type" name="holiday_type"><option value="">Any</option><?php foreach (array('Family holiday', 'Honeymoon', 'Pilgrimage', 'Adventure', 'Leisure / sightseeing', 'Group tour', 'Corporate') as $t) { ?><option><?= $t ?></option><?php } ?></select></div>
                    <div class="hg-field"><label for="c-hotel">Hotel category</label><select id="c-hotel" name="hotel_category"><option value="">No preference</option><?php foreach (array('Standard', 'Deluxe', 'Premium / 4-star', 'Luxury / 5-star') as $t) { ?><option><?= $t ?></option><?php } ?></select></div>
                    <div class="hg-field hg-field--full"><label for="c-budget">Budget per person <span class="hg-optional">(optional)</span></label><select id="c-budget" name="budget"><option value="">Prefer to discuss</option><?php foreach (array('Under ₹15,000', '₹15,000 – ₹30,000', '₹30,000 – ₹60,000', '₹60,000 – ₹1,00,000', 'Above ₹1,00,000') as $t) { ?><option><?= $t ?></option><?php } ?></select></div>
                </div>
                </fieldset>
                <fieldset class="hg-fieldset"><legend>Your details</legend>
                <div class="hg-form__grid">
                    <div class="hg-field hg-field--full"><label for="c-name">Full name</label><input id="c-name" name="name" autocomplete="name" required maxlength="100"></div>
                    <div class="hg-field"><label for="c-phone">Mobile number</label><input id="c-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="20" pattern="[0-9+ ]{8,20}"></div>
                    <div class="hg-field"><label for="c-email">Email</label><input id="c-email" name="email" type="email" autocomplete="email" required maxlength="150"></div>
                    <div class="hg-field hg-field--full"><label for="c-msg">Anything else? <span class="hg-optional">(optional)</span></label><textarea id="c-msg" name="message" rows="4" maxlength="2000" placeholder="Places you want to include, special occasions, mobility needs…"></textarea></div>
                </div>
                </fieldset>
                <p class="hg-form__status" role="status" aria-live="polite"></p>
                <button class="hg-btn hg-btn--primary hg-btn--block" type="submit">Send my trip request</button>
                <p class="hg-form__note">A travel expert replies by phone or WhatsApp. We do not share your details.</p>
            </form>
        </div>
        <aside class="hg-layout__side">
            <div class="hg-sidecard">
                <h2 class="hg-h3">How it works</h2>
                <ol class="hg-steps hg-steps--list">
                    <li><strong>Tell us your trip</strong><span>Destination, dates, travellers and budget.</span></li>
                    <li><strong>Get your plan</strong><span>A day-by-day itinerary, hotels and a quote.</span></li>
                    <li><strong>Fine-tune it</strong><span>Change anything until it suits you.</span></li>
                    <li><strong>Confirm</strong><span>35% advance to confirm; booking voucher issued once payment is received.</span></li>
                </ol>
            </div>
            <div class="hg-sidecard">
                <h2 class="hg-h3">Prefer to talk?</h2>
                <p><a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_icon('phone') ?> <?= hg_e(HG_PHONE_DISPLAY) ?></a></p>
                <a class="hg-btn hg-btn--wa hg-btn--block" href="<?= hg_e(hg_whatsapp_href("Hi Holiday Guru Travel,\nI would like to plan a customized holiday" . ($S['destination'] ? ' to ' . $S['destination'] : '') . '.')) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp') ?>Chat on WhatsApp</a>
            </div>
        </aside>
    </div>
</section>

<section class="hg-section hg-section--tint" aria-labelledby="faq-title">
    <div class="hg-container hg-narrow">
        <h2 class="hg-h2" id="faq-title">Customized holiday questions</h2>
        <?= hg_faq($faqs) ?>
    </div>
</section>
<?php hg_layout_end(); ?>
