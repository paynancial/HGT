<?php
// Contact page (Phase 1). Official contact values come from include/site_config.php.
require __DIR__ . '/include/ui/core.php';

$mapsDir = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode('Dharampali Palace, Bhoja Market, Sector 27, Noida');

hg_layout_start(array(
    'title' => 'Contact Holiday Guru Travel | Travel Agency in Noida, Delhi NCR',
    'description' => 'Contact Holiday Guru Travel for holiday packages and customized tours. Call or WhatsApp +91 80066 92040, email Info@holidaygurutravel.in, or visit our office at Bhoja Market, Sector 27, Noida.',
    'path' => '/contact',
    'breadcrumbs' => array(array('Home', '/'), array('Contact Us', null)),
    'schema' => array(array('@type' => 'ContactPage', 'name' => 'Contact Holiday Guru Travel', 'url' => hg_abs('/contact'), 'about' => array('@id' => HG_SITE_URL . '/#organization'))),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow">Contact us</p>
        <h1 class="hg-h1" id="page-title">Talk to a travel expert</h1>
        <p class="hg-lead">Call, WhatsApp or email us, or send an enquiry below. Our team plans holidays across India and abroad from our office in Noida.</p>
    </div>
</section>

<section class="hg-section hg-section--tight">
    <div class="hg-container">
        <ul class="hg-contactcards">
            <li><?= hg_icon('phone') ?><div><h2>Call</h2><a href="<?= hg_e(hg_tel_href()) ?>" data-hg-track="call_click"><?= hg_e(HG_PHONE_DISPLAY) ?></a></div></li>
            <li><?= hg_icon('whatsapp') ?><div><h2>WhatsApp · 24×7</h2><a href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener" data-hg-track="whatsapp_click"><?= hg_e(HG_PHONE_DISPLAY) ?></a></div></li>
            <li><?= hg_icon('mail') ?><div><h2>Email</h2><a href="<?= hg_e(hg_mailto_href()) ?>" data-hg-track="email_click"><?= hg_e(HG_EMAIL_DISPLAY) ?></a></div></li>
            <li><?= hg_icon('pin') ?><div><h2>Office</h2><address><?= hg_e(HG_ADDRESS_LINE1) ?>,<br><?= hg_e(HG_ADDRESS_LINE2) ?></address><a href="<?= hg_e($mapsDir) ?>" target="_blank" rel="noopener">Get directions</a></div></li>
        </ul>
    </div>
</section>

<section class="hg-section hg-section--tint hg-section--tight" aria-label="Enquiry and map">
    <div class="hg-container hg-contactgrid">
        <div class="hg-sidecard">
            <?= hg_enquiry_form('contact-form', 'Send us an enquiry', array('enquiry_type' => 'General enquiry')) ?>
        </div>
        <div class="hg-contactmap">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2910.825304970541!2d77.327913!3d28.571558999999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce44c1267dce5%3A0x1001590744d7cc9a!2sDharampali%20Place(bhoja%20market)!5e1!3m2!1sen!2sin!4v1790705513426!5m2!1sen!2sin"
                    title="Map: Holiday Guru Travel office, Bhoja Market, Sector 27 Noida" width="600" height="520" style="border:0" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
            <p class="hg-muted"><?= hg_e(HG_ADDRESS_LINE1) ?>, <?= hg_e(HG_ADDRESS_LINE2) ?>. <a href="<?= hg_e($mapsDir) ?>" target="_blank" rel="noopener">Open in Google Maps</a></p>
        </div>
    </div>
</section>

<section class="hg-section hg-section--tight" aria-labelledby="co-title">
    <div class="hg-container hg-narrow">
        <h2 class="hg-h3" id="co-title">Company details</h2>
        <dl class="hg-qf">
            <div><dt>Legal name</dt><dd><?= hg_e(HG_LEGAL_NAME) ?></dd></div>
            <div><dt>Brand</dt><dd>Holiday Guru Travel</dd></div>
            <div><dt>Follow us</dt><dd><a href="<?= hg_e(HG_FACEBOOK_URL) ?>" target="_blank" rel="noopener">Facebook</a> · <a href="<?= hg_e(HG_INSTAGRAM_URL) ?>" target="_blank" rel="noopener">Instagram</a></dd></div>
        </dl>
    </div>
</section>
<?php hg_layout_end(); ?>
