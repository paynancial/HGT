<?php
// About page (Phase 1). Story and mission text are the company's own wording
// from the previous About page; facts are from site configuration and data.
require __DIR__ . '/include/ui/core.php';


hg_layout_start(array(
    'title' => 'About us | Holiday Guru Travel',
    'description' => 'Holiday Guru Travel plans holidays across India and abroad from Noida. Learn about our story, how we work and the company behind the brand.',
    'path' => '/about',
    'breadcrumbs' => array(array('Home', '/'), array('About Us', null)),
    'schema' => array(array('@type' => 'AboutPage', 'name' => 'About Holiday Guru Travel', 'url' => hg_abs('/about'), 'about' => array('@id' => HG_SITE_URL . '/#organization'))),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow">About us</p>
        <h1 class="hg-h1" id="page-title">Your passport to extraordinary journeys</h1>
        <p class="hg-lead">At Holiday Guru Travel we believe that every journey is a story waiting to be told. Whether you’re a seasoned globetrotter or a first-time adventurer, we invite you to embark on a voyage of discovery with us.</p>
    </div>
</section>

<section class="hg-section hg-section--tight">
    <div class="hg-container hg-grid hg-grid--2">
        <div class="hg-card">
            <h2 class="hg-h3">Our story</h2>
            <p>Born from a love for travel and a desire to share the joy of exploration, Holiday Guru Travel was founded with a vision to redefine the way you experience the world. We understand that travel is not just about reaching a destination; it’s about the moments, the connections and the memories that last a lifetime.</p>
        </div>
        <div class="hg-card">
            <h2 class="hg-h3">Our mission</h2>
            <p>At the heart of our mission is a commitment to curating journeys that go beyond the ordinary — experiences that immerse you in the local culture and leave you with a deep appreciation for the diverse tapestry of our planet.</p>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tint" aria-labelledby="facts-title">
    <div class="hg-container">
        <?= hg_section_head('At a glance', 'The company behind the brand', '', null, 'facts-title') ?>
        <dl class="hg-qf hg-qf--3">
            <div><dt>Legal name</dt><dd><?= hg_e(HG_LEGAL_NAME) ?></dd></div>
            <div><dt>Brand</dt><dd>Holiday Guru Travel</dd></div>
            <div><dt>Office</dt><dd><?= hg_e(HG_ADDRESS_LINE1) ?>, <?= hg_e(HG_ADDRESS_LINE2) ?></dd></div>
            <div><dt>Holidays</dt><dd>India, Dubai, Singapore &amp; Malaysia and the Maldives — each tour with a day-by-day plan</dd></div>
            <div><dt>Support</dt><dd>Phone and WhatsApp <?= hg_e(HG_PHONE_DISPLAY) ?> (WhatsApp 24×7)</dd></div>
            <div><dt>Email</dt><dd><a href="<?= hg_e(hg_mailto_href()) ?>"><?= hg_e(HG_EMAIL_DISPLAY) ?></a></dd></div>
        </dl>
    </div>
</section>

<section class="hg-section" id="why-us" aria-labelledby="why-title" style="scroll-margin-top:calc(var(--hg-sticky) + 16px)">
    <div class="hg-container">
        <?= hg_section_head('Why choose us', 'How we plan your trip', '', null, 'why-title') ?>
        <div class="hg-grid hg-grid--3">
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('route') ?></span><h3>Expertise</h3><p>Our team crafts itineraries that reflect our passion for discovery — from hidden gems to iconic landmarks. Every package on this site shows its full day-by-day plan.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('users') ?></span><h3>Personalized service</h3><p>Each traveller is unique. Any package can be changed — hotels, nights, sightseeing — or we plan a <a href="/customized-holidays">customized holiday</a> from scratch.</p></div>
            <div class="hg-card"><span class="hg-card__icon"><?= hg_icon('check') ?></span><h3>Customer-centric</h3><p>Your satisfaction is our priority, from your first message to your return. Inclusions, exclusions and booking terms are written on every package so there are no surprises.</p></div>
        </div>
    </div>
</section>

<?= hg_cta_band('Join us on the adventure of a lifetime', 'Whether you dream of ancient wonders, pristine beaches or vibrant city streets, tell us where you want to go.') ?>
<?php hg_layout_end(); ?>
