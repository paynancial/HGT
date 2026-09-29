<?php require_once __DIR__ . '/site_config.php'; ?>
<!-- Floating support widget (Chat / Call / WhatsApp / Email). Behaviour: assets/js/hg-site.js -->
<div class="hg-support" id="hg-support">
    <div class="hg-support__panel" id="hg-support-panel" role="dialog" aria-labelledby="hg-support-title" hidden>
        <div class="hg-support__head">
            <div>
                <p class="hg-support__title" id="hg-support-title">How can we help?</p>
                <p class="hg-support__sub">Talk to a Holiday Guru travel expert</p>
            </div>
            <button type="button" class="hg-support__close" data-hg-support-close aria-label="Close contact options">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <div class="hg-support__view" data-hg-view="actions">
            <button type="button" class="hg-support__action" data-hg-chat-open>
                <span class="hg-support__icon" aria-hidden="true"><i class="fa-regular fa-comments"></i></span>
                <span class="hg-support__label">Chat with us<small>Ask a quick question</small></span>
            </button>
            <a class="hg-support__action" href="<?= hg_e(hg_tel_href()) ?>" data-hg-track="call">
                <span class="hg-support__icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                <span class="hg-support__label">Call Now<small><?= hg_e(HG_PHONE_DISPLAY) ?></small></span>
            </a>
            <a class="hg-support__action" href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener" data-hg-whatsapp data-hg-track="whatsapp">
                <span class="hg-support__icon hg-support__icon--wa" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></span>
                <span class="hg-support__label">WhatsApp<small><?= hg_e(HG_PHONE_DISPLAY) ?></small></span>
            </a>
            <a class="hg-support__action" href="<?= hg_e(hg_mailto_href()) ?>" data-hg-track="email">
                <span class="hg-support__icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                <span class="hg-support__label">Email Us<small><?= hg_e(HG_EMAIL_DISPLAY) ?></small></span>
            </a>
        </div>

        <div class="hg-support__view" data-hg-view="chat" hidden>
            <!--
                CHATBOT INTEGRATION POINT
                No chatbot or live-chat backend is connected yet. When one is approved,
                mount its client here (or replace this block) and remove the notice below.
                Do not add scripted/fake replies.
            -->
            <p class="hg-support__notice"><strong>Live chat is not available yet.</strong> For the fastest reply, message a travel expert on WhatsApp or call us.</p>
            <a class="hg-support__btn hg-support__btn--primary" href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener" data-hg-whatsapp data-hg-track="whatsapp">
                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Chat on WhatsApp
            </a>
            <a class="hg-support__btn" href="<?= hg_e(hg_tel_href()) ?>" data-hg-track="call">
                <i class="fa-solid fa-phone" aria-hidden="true"></i> Call Holiday Guru
            </a>
            <button type="button" class="hg-support__back" data-hg-chat-back>&larr; All contact options</button>
        </div>
    </div>

    <button type="button" class="hg-support__toggle" aria-expanded="false" aria-controls="hg-support-panel" aria-label="Contact Holiday Guru Travel">
        <i class="fa-regular fa-comments hg-support__toggle-open" aria-hidden="true"></i>
        <i class="fa-solid fa-xmark hg-support__toggle-close" aria-hidden="true"></i>
        <span class="hg-support__toggle-text">Need help?</span>
    </button>
</div>
