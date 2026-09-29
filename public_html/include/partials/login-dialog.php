<?php require_once __DIR__ . '/../site_config.php'; ?>
<!--
    ACCOUNT / LOGIN INTEGRATION POINT
    There is no customer-account backend yet. This dialog shows the approved
    Login / Sign up entry state and says plainly that accounts are not live.
    When authentication is built (mobile/email OTP), enable the form below.
-->
<dialog class="hg-dialog" id="hg-login" aria-labelledby="hg-login-title">
    <form method="dialog" class="hg-dialog__close-form">
        <button class="hg-iconbtn hg-dialog__close" value="close"><?= hg_icon('close') ?><span class="hg-sr">Close</span></button>
    </form>
    <p class="hg-eyebrow">My Holiday Guru</p>
    <h2 class="hg-dialog__title" id="hg-login-title">Login or create an account</h2>
    <div class="hg-notice" role="note">
        <?= hg_icon('info') ?>
        <p><strong>Online accounts are coming soon.</strong> Until then, a travel expert shares your itinerary, quote and booking voucher by WhatsApp or email.</p>
    </div>
    <form class="hg-form hg-form--login" aria-describedby="hg-login-soon" onsubmit="return false">
        <fieldset disabled>
            <legend class="hg-sr">Login with mobile or email</legend>
            <div class="hg-field"><label for="hg-login-id">Mobile number or email</label><input id="hg-login-id" autocomplete="username"></div>
            <button class="hg-btn hg-btn--primary hg-btn--block" type="submit">Send one-time password</button>
        </fieldset>
        <p class="hg-form__note" id="hg-login-soon">What you will be able to do: see your enquiries and bookings, save packages, and update your profile.</p>
    </form>
    <div class="hg-dialog__alt">
        <a class="hg-btn hg-btn--outline hg-btn--block" href="<?= hg_e(hg_whatsapp_href("Hi Holiday Guru Travel,\nI would like an update on my enquiry/booking.")) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp') ?> Check my booking on WhatsApp</a>
        <a class="hg-btn hg-btn--ghost hg-btn--block" href="<?= hg_e(hg_mailto_href()) ?>"><?= hg_icon('mail') ?> Email <?= hg_e(HG_EMAIL_DISPLAY) ?></a>
    </div>
</dialog>
