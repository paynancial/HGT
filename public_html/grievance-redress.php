<?php
// Grievance redress. Uses the company's published contact channels only.
// The Grievance Officer's name is to be supplied by the owner (left blank).
// Publishing status: include/data/page-status.php.
require __DIR__ . '/include/ui/core.php';

$officer = array('name' => 'Mrs. Laxmi Saxena', 'designation' => 'Grievance Officer', 'email' => 'Laxmi@holidaygurutravel.in');

hg_layout_start(array(
    'title' => 'Grievance Redress | Holiday Guru Travel',
    'description' => 'How to raise a complaint or grievance about a Holiday Guru Travel booking, and how we respond.',
    'path' => '/grievance-redress', 'index' => hg_page_status('/grievance-redress') === 'approved',
    'breadcrumbs' => array(array('Home', '/'), array('Grievance Redress', null)),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container hg-narrow">
        <p class="hg-eyebrow">Legal &amp; support</p>
        <h1 class="hg-h1" id="page-title">Grievance redress</h1>
        <p class="hg-lead">If something about your booking or trip has not gone as expected, tell us. Here is how to raise a complaint and what happens next.</p>
    </div>
</section>
<section class="hg-section hg-section--tight">
    <div class="hg-container hg-narrow hg-prose hg-policy">
        <h2 class="hg-h3">How to raise a grievance</h2>
        <ol class="hg-steps hg-steps--list">
            <li><strong>Contact us</strong><span>Email our Grievance Officer at <a href="mailto:<?= hg_e($officer['email']) ?>"><?= hg_e($officer['email']) ?></a>, call or WhatsApp <a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_e(HG_PHONE_DISPLAY) ?></a>, or write to our office.</span></li>
            <li><strong>Share the details</strong><span>Your name, phone number, booking voucher or enquiry details, travel dates, and a short description of the issue, with any photos or documents.</span></li>
            <li><strong>We acknowledge and review</strong><span>Our team acknowledges your complaint, reviews it with the hotel, transport or other supplier involved, and contacts you with an update and resolution.</span></li>
        </ol>

        <h2 class="hg-h3">Grievance Officer</h2>
        <dl class="hg-qf">
            <div><dt>Name</dt><dd><?= $officer['name'] !== '' ? hg_e($officer['name']) : '<span class="hg-muted">To be added</span>' ?></dd></div>
            <div><dt>Designation</dt><dd><?= hg_e($officer['designation']) ?></dd></div>
            <div><dt>Email</dt><dd><a href="mailto:<?= hg_e($officer['email']) ?>"><?= hg_e($officer['email']) ?></a></dd></div>
            <div><dt>Phone / WhatsApp</dt><dd><a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_e(HG_PHONE_DISPLAY) ?></a></dd></div>
            <div><dt>Address</dt><dd><?= hg_e(HG_ADDRESS_LINE1) ?>, <?= hg_e(HG_ADDRESS_LINE2) ?></dd></div>
            <div><dt>Company</dt><dd><?= hg_e(HG_LEGAL_NAME) ?></dd></div>
        </dl>

        <h2 class="hg-h3">Cancellations and refunds</h2>
        <p>Requests to cancel or for a refund follow our <a href="/cancellation-policy">cancellation policy</a> and <a href="/refund-policy">refund policy</a>.</p>
    </div>
</section>
<?php hg_layout_end(); ?>
