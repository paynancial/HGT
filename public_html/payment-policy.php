<?php
require __DIR__ . '/include/templates/policy.php';
ob_start(); ?>
<h2 class="hg-h3">Booking and payment</h2>
<ul class="hg-checks hg-checks--info">
    <li>To book and confirm your tour, we request a deposit of 35% of the total package amount as an advance; the balance is paid before departure.</li>
    <li>Air and train tickets require full payment at the time of booking.</li>
    <li>Once we receive the payment, we issue a booking voucher.</li>
    <li>Some packages note that prices are dynamic and subject to change until the booking is put on hold or vouchered.</li>
</ul>
<h2 class="hg-h3">Taxes and extra travellers</h2>
<ul class="hg-checks hg-checks--info">
    <li>Many packages list GST at 5% as extra, on a GST bill; each package page shows whether GST is included or extra.</li>
    <li>Many packages charge an extra adult at 35% and an extra child at 25% of the package cost, with children below 5 complimentary; some packages use different rates, shown in their terms.</li>
</ul>
<h2 class="hg-h3">Payment methods</h2>
<p>Net banking, IMPS, NEFT, cheque and UPI (including Google Pay, PhonePe, Paytm and scan-to-pay QR code). <strong>We do not accept cash.</strong></p>
<h2 class="hg-h3">Online payment</h2>
<p>Online card payment on this website is not available yet. A travel expert shares the payment details with your quote. Keep your payment receipt until your booking voucher is issued.</p>
<?php
hg_render_policy(array(
    'path' => '/payment-policy', 'h1' => 'Payment policy',
    'title' => 'Payment Policy | Holiday Guru Travel',
    'description' => 'Advance, balance and accepted payment methods for Holiday Guru Travel holiday packages.',
    'lead' => 'Advance, balance and accepted payment methods for our holiday packages.',
), ob_get_clean());
