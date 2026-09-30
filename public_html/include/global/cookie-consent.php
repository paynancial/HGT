<?php require_once __DIR__ . '/../site_config.php'; ?>
<!-- Cookie consent. Choice stored in the first-party cookie "hg_consent". Behaviour: assets/js/hg-site.js -->
<div class="hg-consent" id="hg-consent" role="region" aria-label="Cookie consent" hidden>
    <p class="hg-consent__text">
        We use cookies to improve your experience, analyse website usage, and support essential website functionality.
        <?php if (HG_PRIVACY_URL !== '') { ?><a href="<?= hg_e(HG_PRIVACY_URL) ?>">Privacy Policy</a><?php } ?>
    </p>
    <div class="hg-consent__buttons">
        <button type="button" class="hg-consent__btn hg-consent__btn--primary" data-hg-consent="accept">Accept All</button>
        <button type="button" class="hg-consent__btn" data-hg-consent="reject">Reject Non-Essential</button>
        <button type="button" class="hg-consent__btn hg-consent__btn--link" data-hg-consent="settings">Cookie Settings</button>
    </div>
</div>

<div class="hg-consent-modal" id="hg-consent-settings" hidden>
    <div class="hg-consent-modal__box" role="dialog" aria-modal="true" aria-labelledby="hg-consent-title">
        <h2 class="hg-consent-modal__title" id="hg-consent-title">Cookie settings</h2>
        <div class="hg-consent-modal__row">
            <input type="checkbox" id="hg-consent-essential" checked disabled>
            <label for="hg-consent-essential"><strong>Essential</strong><span>Needed for the website and enquiry forms to work, and to remember this choice. Always on.</span></label>
        </div>
        <div class="hg-consent-modal__row">
            <input type="checkbox" id="hg-consent-analytics">
            <label for="hg-consent-analytics"><strong>Analytics</strong><span>Google Analytics, to understand how visitors use the website. Off unless you allow it.</span></label>
        </div>
        <?php if (HG_PRIVACY_URL !== '') { ?><p class="hg-consent-modal__link"><a href="<?= hg_e(HG_PRIVACY_URL) ?>">Privacy Policy</a></p><?php } ?>
        <div class="hg-consent__buttons">
            <button type="button" class="hg-consent__btn hg-consent__btn--primary" data-hg-consent="save">Save choices</button>
            <button type="button" class="hg-consent__btn" data-hg-consent="accept">Accept All</button>
        </div>
    </div>
</div>
<script>window.HG_SITE = <?= json_encode(array('ga4Id' => HG_GA4_ID), JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
