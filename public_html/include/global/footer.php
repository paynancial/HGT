<?php require_once __DIR__ . '/../site_config.php'; ?>
<?php $hgFooterNav = hg_footer_nav(); ?>
<footer class="hg-footer">
    <div class="hg-footer__main">
        <div class="container hg-footer__grid">
            <div class="hg-footer__top">
                <a href="/" class="hg-footer__logo"><picture><source type="image/webp" srcset="/assets/brand/holiday-guru-travel-logo-240.webp 1x, /assets/brand/holiday-guru-travel-logo-480.webp 2x"><img src="/assets/brand/holiday-guru-travel-logo-240.png" alt="Holiday Guru Travel" width="122" height="60" loading="lazy"></picture></a>
                <p class="hg-footer__tagline">Your journey. Your way.</p>
                <p class="hg-footer__intro">Holiday packages across India and abroad, planned by our team in Noida.</p>
                <form id="newsletter" class="hg-footer__news" aria-label="Newsletter">
                    <label class="hg-sr" for="hg-news-email">Email for our newsletter</label>
                    <input id="hg-news-email" name="email" type="email" placeholder="Your email for offers" autocomplete="email" required>
                    <button type="submit">Subscribe</button>
                </form>
                <div class="hg-footer__social">
                    <a href="<?= hg_e(HG_FACEBOOK_URL) ?>" target="_blank" rel="noopener" aria-label="Holiday Guru Travel on Facebook"><?= hg_icon('facebook') ?></a>
                    <a href="<?= hg_e(HG_INSTAGRAM_URL) ?>" target="_blank" rel="noopener" aria-label="Holiday Guru Travel on Instagram"><?= hg_icon('instagram') ?></a>
                </div>
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
                    <li class="hg-footer__wa"><?= hg_icon('whatsapp') ?><a href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener">Chat on WhatsApp <span>24×7</span></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="hg-footer__bottom">
        <div class="container">
            <p class="hg-footer__copyline">&copy; 2021&ndash;<?= date('Y') ?> <?= hg_e(HG_LEGAL_NAME) ?> All Rights Reserved.<span class="hg-footer__cin">CIN <?= hg_e(HG_CIN) ?></span></p>
            <ul class="hg-footer__bottomlinks">
                <li><button type="button" class="hg-footer-link" data-hg-consent-open>Cookie settings</button></li>
            </ul>
        </div>
    </div>
</footer>
