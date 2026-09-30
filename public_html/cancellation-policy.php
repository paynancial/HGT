<?php
require __DIR__ . '/include/templates/policy.php';
ob_start(); ?>
<h2 class="hg-h3">How cancellation charges are calculated</h2>
<ul class="hg-checks hg-checks--info">
    <li>Cancellation charges are calculated on the gross tour cost and depend on the date of departure and the date of cancellation.</li>
    <li>Cancellation charges for any type of transport ticket (air, train or bus) are applicable as per the rules of the concerned airline, railway or operator.</li>
</ul>
<h2 class="hg-h3">Cancellation charges schedule</h2>
<p>Where a package lists a cancellation schedule, the charges before the departure / check-in date are:</p>
<div class="hg-tablewrap" tabindex="0" role="region" aria-label="Cancellation charges"><table class="hg-table">
    <thead><tr><th scope="col">When you cancel</th><th scope="col">Cancellation charge</th></tr></thead>
    <tbody>
        <tr><td>At least 21 days before departure</td><td>25% of booking value</td></tr>
        <tr><td>Between 8 and 20 days before departure</td><td>50% of booking value</td></tr>
        <tr><td>7 days or less before departure</td><td>100% of booking value</td></tr>
    </tbody>
</table></div>
<p class="hg-muted">This schedule appears on many of our packages — all Leh Ladakh and Singapore packages, most Dubai and Goa packages, and several Himachal packages. Other packages state that charges depend on the dates of departure and cancellation; we confirm the exact charge before you book.</p>
<p><strong>Example:</strong> on a package with this schedule, cancelling 15 days before departure falls in the 8–20 day band, so the charge is 50% of the booking value.</p>
<h2 class="hg-h3">How to cancel</h2>
<p>Send your cancellation request by email to <a href="mailto:info@holidaygurutravel.in">Info@holidaygurutravel.in</a> or on WhatsApp to <?= hg_e(HG_PHONE_DISPLAY) ?>, with your name and booking voucher details. We confirm the charges that apply and any refund due.</p>
<h2 class="hg-h3">Changes during the trip</h2>
<ul class="hg-checks hg-checks--info">
    <li>Transport is provided as per the itinerary and sightseeing depends on the time available.</li>
    <li>Weather, road blocks, flight cancellations or ill health can change the plan on the day. Costs caused by such changes are listed as excluded on most packages.</li>
    <li>If a listed hotel is unavailable, our package terms provide for a hotel of similar standard.</li>
</ul>
<h2 class="hg-h3">Refunds</h2>
<p>Any refund due after cancellation follows our <a href="/refund-policy">refund policy</a>.</p>
<?php
hg_render_policy(array(
    'path' => '/cancellation-policy', 'h1' => 'Cancellation policy',
    'title' => 'Cancellation Policy | Holiday Guru Travel',
    'description' => 'How cancellation charges are calculated for Holiday Guru Travel holiday packages, including the cancellation schedule and transport ticket rules.',
    'lead' => 'How cancellation charges apply to holiday packages booked with Holiday Guru Travel.',
), ob_get_clean());
