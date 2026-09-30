<?php
// Pilgrimage tours (Phase 1 speciality page). Facts below are taken from our own
// Char Dham and Amarnath itineraries (include/data/packages.json).
require __DIR__ . '/include/ui/core.php';

$pilgrim = hg_packages_where(function ($p) { return $p['pilgrimage']; });
$charDham = hg_packages_in('char-dham');
$amarnath = hg_packages_in('amarnath');
$other = array_values(array_filter($pilgrim, function ($p) { return !in_array($p['group'], array('char-dham', 'amarnath'), true); }));
foreach (array(&$charDham, &$amarnath) as &$list) { usort($list, function ($a, $b) { return $a['days'] <=> $b['days']; }); }
unset($list);

$faqs = array(
    array('Which pilgrimage tours do you offer?', '<p>Char Dham Yatra (Yamunotri, Gangotri, Kedarnath and Badrinath), Do Dham (Kedarnath and Badrinath), Yamunotri–Gangotri Do Dham, and Amarnath Yatra — by road, or with helicopter options. Two of our holiday packages also include Mata Vaishno Devi from Katra.</p>'),
    array('Where do Char Dham packages start?', '<p>Our Char Dham and Do Dham packages start from Haridwar or Delhi, depending on the package. The departure city is shown on each package.</p>'),
    array('How long are the treks?', '<p>Our itineraries list the trek to Yamunotri as about 6 km from Jankichatti/Phoolchatti (on foot, by horse or by doli at your own cost). For Amarnath via Pahalgam, the trek runs from Chandanwari to Sheshnag (about 13 km) and on to Panchtarni and the holy cave. The Mata Vaishno Devi trek from Katra is about 13 km each way, with horse or palki available.</p>'),
    array('Is the helicopter included?', '<p>Only where the package inclusions say so. In our road Char Dham and Do Dham packages, the Kedarnath helicopter is optional and the ticket is <strong>not included</strong>; the Char Dham by helicopter package is built around helicopter travel. For Amarnath, our itinerary notes that the helicopter stops short of the holy cave (at Panchtarni), so a final walk or pony ride remains. Check each package’s inclusions.</p>'),
    array('Can senior citizens do the yatra?', '<p>Many do, using ponies, palki or doli, or a helicopter package. Tell us the travellers’ ages and health so we can suggest the right package and pace. Consult a doctor before high-altitude travel.</p>'),
);

hg_layout_start(array(
    'title' => 'Religious Tour Packages in India',
    'description' => "Holiday Guru Travel offers customized religious tour packages to sacred destinations. Whether it's a pilgrimage or spiritual retreat, let us guide you on a journey of faith.",
    'path' => '/religious-tour',
    'breadcrumbs' => array(array('Home', '/'), array('Speciality Tours', null), array('Pilgrimage Tours', null)),
    'schema' => array(hg_faq_schema($faqs)),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container hg-pagehead__grid">
        <div>
            <p class="hg-eyebrow">Speciality tours</p>
            <h1 class="hg-h1" id="page-title">Pilgrimage tour packages</h1>
            <p class="hg-lead">Char Dham, Do Dham and Amarnath Yatra by road or with helicopter options — every package lists road distances, trek lengths and what is included, so your family knows what to expect.</p>
            <div class="hg-pagehead__actions">
                <a class="hg-btn hg-btn--primary" href="#char-dham">Char Dham packages</a>
                <a class="hg-btn hg-btn--outline" href="#amarnath">Amarnath Yatra packages</a>
            </div>
        </div>
        <div class="hg-pagehead__media"><?= hg_img('assets/img/destination/chardham1.jpg', 'Kedarnath temple in the Garhwal Himalaya', 640, 440, '', true) ?></div>
    </div>
</section>

<?= hg_section_nav(array(array('char-dham', 'Char Dham'), array('amarnath', 'Amarnath'), array('more', 'More yatras'), array('plan', 'Plan your yatra'), array('faq', 'FAQs'))) ?>

<section class="hg-section hg-section--tint" id="char-dham" aria-labelledby="cd-title" style="scroll-margin-top:calc(var(--hg-sticky) + 56px)">
    <div class="hg-container">
        <?= hg_section_head('Uttarakhand', 'Char Dham &amp; Do Dham Yatra', hg_e(hg_duration_range($charDham)) . ' trips' . ' · from Haridwar or Delhi', array('Compare with filters', '/tours/char-dham'), 'cd-title') ?>
        <?= hg_package_grid($charDham) ?>
    </div>
</section>

<section class="hg-section" id="amarnath" aria-labelledby="am-title" style="scroll-margin-top:calc(var(--hg-sticky) + 56px)">
    <div class="hg-container">
        <?= hg_section_head('Jammu &amp; Kashmir', 'Amarnath Yatra', hg_e(hg_duration_range($amarnath)) . ' trips' . ' · via Pahalgam or by helicopter from Baltal', array('Compare with filters', '/tours/amarnath'), 'am-title') ?>
        <?= hg_package_grid($amarnath) ?>
    </div>
</section>

<section class="hg-section hg-section--tint" id="more" aria-labelledby="more-title" style="scroll-margin-top:calc(var(--hg-sticky) + 56px)">
    <div class="hg-container">
        <?= hg_section_head('Also includes a pilgrimage', 'Holidays with Mata Vaishno Devi', '', null, 'more-title') ?>
        <?= hg_package_grid($other) ?>
        <div class="hg-notice" role="note" style="margin-top:28px">
            <p><strong>More yatras on yatrapackages.com</strong> (external site): <a href="https://yatrapackages.com/ayodha-kashi-darshan.php" rel="noopener" target="_blank">Ayodhya Kashi Darshan</a>, <a href="https://yatrapackages.com/vaishno-devi-yatra.php" rel="noopener" target="_blank">Vaishno Devi Yatra</a>, <a href="https://yatrapackages.com/jagannath-ji-darshan.php" rel="noopener" target="_blank">Jagannath Ji Darshan</a>. These links open in a new tab.</p>
        </div>
    </div>
</section>

<section class="hg-section" id="plan" aria-labelledby="plan-title" style="scroll-margin-top:calc(var(--hg-sticky) + 56px)">
    <div class="hg-container hg-narrow">
        <h2 class="hg-h2" id="plan-title">Plan your yatra</h2>
        <p class="hg-summary"><strong>In short:</strong> choose Char Dham (9–11 days from Haridwar or Delhi) for all four shrines, Do Dham (5–6 days from Haridwar) for Kedarnath and Badrinath, or a helicopter package if walking long distances is difficult.</p>
        <div class="hg-tablewrap" tabindex="0" role="region" aria-label="Table"><table class="hg-table">
            <thead><tr><th scope="col">Yatra</th><th scope="col">Our packages</th><th scope="col">Starts from</th><th scope="col">Walking / trek (from our itineraries)</th></tr></thead>
            <tbody>
                <tr><td>Char Dham</td><td><?= hg_e(hg_duration_range(array_filter($charDham, function ($p) { return stripos($p['slug'], 'char') === 0 || stripos($p['slug'], 'chardham') === 0; }))) ?></td><td>Haridwar or Delhi; helicopter package separately</td><td>Yamunotri about 6 km from Jankichatti/Phoolchatti (walk, horse or doli). Kedarnath: local jeep from Sonprayag to Gaurikund, then trek (ponies available) — or helicopter, ticket not included in road packages</td></tr>
                <tr><td>Do Dham (Kedarnath, Badrinath)</td><td><?= hg_e(hg_duration_range(array_filter($charDham, function ($p) { return stripos($p['slug'], 'do-dham') === 0; }))) ?></td><td>Haridwar</td><td>Kedarnath: jeep to Gaurikund, then trek or pony — or helicopter (ticket not included)</td></tr>
                <tr><td>Amarnath</td><td><?= hg_e(hg_duration_range($amarnath)) ?></td><td>Srinagar</td><td>Via Pahalgam: Chandanwari–Sheshnag about 13 km, then Panchtarni and the holy cave. By helicopter from Baltal: to Panchtarni, then the final stretch on foot or pony</td></tr>
            </tbody>
        </table></div>
        <?= hg_tip('Before you go', '<ul><li>Registration and medical requirements for Char Dham and Amarnath are set by the authorities each season — we confirm current rules with your booking.</li><li>Carry photo ID for every traveller, warm layers and rain protection.</li><li>Ponies, palki and doli are paid locally unless the package says otherwise.</li><li>Weather and road closures can change the day plan; the driver follows what is open.</li></ul>') ?>
    </div>
</section>

<section class="hg-section hg-section--tint" id="faq" aria-labelledby="faq-title" style="scroll-margin-top:calc(var(--hg-sticky) + 56px)">
    <div class="hg-container hg-narrow">
        <h2 class="hg-h2" id="faq-title">Pilgrimage tour questions</h2>
        <?= hg_faq($faqs) ?>
    </div>
</section>

<?= hg_cta_band('Planning a yatra for your family?', 'Tell us who is travelling, their ages and your dates — we suggest the right route, pace and helicopter options.') ?>
<?php hg_layout_end(); ?>
