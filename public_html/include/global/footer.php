<?php require_once __DIR__ . '/../site_config.php'; ?>
<?php
/**
 * GLOBAL COMPONENT: site footer (redesign 2026-09-30, owner reference).
 * Brand column · link columns from include/footer_nav.php (Tours, Destinations, Company, Support) · Contact Us ·
 * bottom bar (© line + policy links + cookie settings). Mobile: link columns are accordions (assets/js/src/site/footer.js).
 */
$hgFooterNav = hg_footer_nav();
?>
<footer class="hg-footer">
    <div class="hg-footer__main">
        <div class="container hg-footer__grid">
            <div class="hg-footer__top">
                <a href="/" class="hg-footer__brand" aria-label="Holiday Guru Travel home">
                    <picture><source type="image/webp" srcset="/assets/brand/holiday-guru-travel-badge-160.webp 1x, /assets/brand/holiday-guru-travel-badge-320.webp 2x"><img src="/assets/brand/holiday-guru-travel-badge-160.webp" alt="" width="64" height="64" loading="lazy"></picture>
                    <span class="hg-footer__wordmark"><span class="hg-footer__name">Holiday <b>Guru</b> Travel</span><span class="hg-footer__tag">Explore &bull; Experience &bull; Create Memories</span></span>
                </a>
                <p class="hg-footer__intro">Your trusted travel partner for holiday packages across India and abroad — day-by-day itineraries, customised trips and pilgrimage tours, planned by our team in Noida.</p>
                <div class="hg-footer__social">
                    <a class="hg-footer__soc hg-footer__soc--fb" href="<?= hg_e(HG_FACEBOOK_URL) ?>" target="_blank" rel="noopener" aria-label="Holiday Guru Travel on Facebook"><?= hg_icon('facebook') ?></a>
                    <a class="hg-footer__soc hg-footer__soc--ig" href="<?= hg_e(HG_INSTAGRAM_URL) ?>" target="_blank" rel="noopener" aria-label="Holiday Guru Travel on Instagram"><?= hg_icon('instagram') ?></a>
                    <a class="hg-footer__soc hg-footer__soc--wa" href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener" aria-label="Chat with Holiday Guru Travel on WhatsApp"><?= hg_icon('whatsapp') ?></a>
                </div>
                <form id="newsletter" class="hg-footer__news" aria-label="Newsletter">
                    <label class="hg-sr" for="hg-news-email">Email for our newsletter</label>
                    <input id="hg-news-email" name="email" type="email" placeholder="Your email for offers" autocomplete="email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>

            <nav class="hg-footer__nav" aria-label="Footer">
                <?php foreach ($hgFooterNav as $hgKey => $hgSection) { ?>
                <details class="hg-fnav" id="footer-<?= hg_e($hgKey) ?>" open data-hg-fnav>
                    <summary class="hg-fnav__summary"><h2 class="hg-fnav__title"><?= hg_e($hgSection['title']) ?></h2><span class="hg-fnav__icon" aria-hidden="true"></span></summary>
                    <ul class="hg-fnav__list">
                        <?php foreach ($hgSection['items'] as $hgItem) { ?>
                        <?php if ($hgItem['state'] === 'active') { ?>
                        <li><a class="hg-fnav__link" href="<?= hg_e($hgItem['url']) ?>"<?= !empty($hgItem['target']) ? ' target="' . hg_e($hgItem['target']) . '" rel="noopener"' : '' ?>><?= hg_e($hgItem['label']) ?><?php if (!empty($hgItem['badge'])) { ?> <span class="hg-fnav__badge hg-fnav__badge--info"><?= hg_e($hgItem['badge']) ?></span><?php } ?></a></li>
                        <?php } else { ?>
                        <li><span class="hg-fnav__soon"><?= hg_e($hgItem['label']) ?></span></li>
                        <?php } ?>
                        <?php } ?>
                    </ul>
                </details>
                <?php } ?>
            </nav>

            <div class="hg-footer__contact">
                <h2 class="hg-fnav__title">Contact Us</h2>
                <ul>
                    <li><?= hg_icon('pin') ?><a href="/contact"><?= hg_e(HG_ADDRESS_LINE1) ?>, <?= hg_e(HG_ADDRESS_LINE2) ?></a></li>
                    <li><?= hg_icon('phone') ?><a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_e(HG_PHONE_DISPLAY) ?></a></li>
                    <li><?= hg_icon('mail') ?><a href="<?= hg_e(hg_mailto_href()) ?>"><?= hg_e(HG_EMAIL_DISPLAY) ?></a></li>
                    <li><?= hg_icon('clock') ?><span>24×7 Support</span></li>
                    <li class="hg-footer__wa"><?= hg_icon('whatsapp') ?><a href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener">Chat on WhatsApp</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="hg-footer__bottom">
        <div class="container">
            <p class="hg-footer__copyline">&copy; <?= (int) HG_COPYRIGHT_SINCE ?>&ndash;<?= date('Y') ?> <?= hg_e(HG_LEGAL_NAME) ?>. All Rights Reserved.</p>
            <ul class="hg-footer__bottomlinks">
                <li><a href="/cancellation-policy">Cancellation Policy</a></li>
                <li><a href="/refund-policy">Refund Policy</a></li>
                <li><a href="/grievance-redress">Grievance Redress</a></li>
                <li><button type="button" class="hg-footer-link" data-hg-consent-open>Cookie settings</button></li>
            </ul>
        </div>
    </div>
</footer>
