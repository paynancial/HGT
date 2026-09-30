<?php
require __DIR__ . '/include/templates/policy.php';
ob_start(); ?>
<h2 class="hg-h3">When refunds apply</h2>
<ul class="hg-checks hg-checks--info">
    <li>Refunds are made strictly as per the <a href="/cancellation-policy">cancellation policy</a>.</li>
    <li>Any refund payable to the guest is paid after the company receives the refund from the respective suppliers and authorities (hotels, transport operators, airlines or railways).</li>
    <li>Holiday Guru Travel deducts processing charges from the refund paid to the guest.</li>
    <li>Refunds for transport tickets follow the rules of the concerned airline, railway or operator.</li>
</ul>
<h2 class="hg-h3">What is not refunded</h2>
<ul class="hg-checks hg-checks--no">
    <li>Cancellation charges under the cancellation policy.</li>
    <li>Processing charges deducted from the refund.</li>
    <li>Ticket charges retained by airlines, railways or operators under their own rules.</li>
</ul>
<h2 class="hg-h3">How to request a refund</h2>
<p>Contact us with your booking voucher details by phone, WhatsApp or email. We confirm the refundable amount after applying the cancellation charges.</p>
<?php
hg_render_policy(array(
    'path' => '/refund-policy', 'h1' => 'Refund policy',
    'title' => 'Refund Policy | Holiday Guru Travel',
    'description' => 'When and how refunds are paid for cancelled Holiday Guru Travel bookings.',
    'lead' => 'When and how refunds are paid for cancelled bookings.',
), ob_get_clean());
