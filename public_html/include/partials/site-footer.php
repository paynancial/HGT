<?php require_once __DIR__ . '/../site_config.php'; ?>
<?php $hgFooterNav = hg_footer_nav(); ?>
<footer class="footer-wrapper footer-layout1 hg-footer">
        <div class="widget-area">
            <div class="container">
                <div class="newsletter-area">
                    <div class="newsletter-top">
                        <div class="row gy-4 align-items-center">
                            <div class="col-lg-5">
                                <h2 class="newsletter-title text-capitalize mb-0">get updated the latest newsletter</h2>
                            </div>
                            <div class="col-lg-7">
                                <form id="newsletter" class="newsletter-form"><input class="form-control" name="email" type="email"
                                        placeholder="Enter Email" required> <button id="submit" type="submit"
                                        class="th-btn style3">Subscribe Now <img src="/assets/img/icon/plane.svg"
                                            alt=""></button></form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hg-footer__top">
                    <a href="/" class="hg-footer__logo"><picture><source type="image/webp" srcset="/assets/brand/holiday-guru-travel-logo-240.webp 1x, /assets/brand/holiday-guru-travel-logo-480.webp 2x"><img src="/assets/brand/holiday-guru-travel-logo-240.png" alt="Holiday Guru Travel" width="180" height="89" loading="lazy"></picture></a>
                    <p class="hg-footer__tagline">Your journey. Your way.</p>
                    <p class="hg-footer__intro">Holiday packages across India and abroad, planned by the Holiday Guru Travel team from our office in Noida.</p>
                    <ul class="hg-footer__quick">
                        <li><a class="hg-qlink hg-qlink--primary" href="<?= hg_e(hg_tel_href()) ?>"><?= hg_icon('phone') ?><span class="hg-qlink__label">Call Now</span><span class="hg-qlink__value"><?= hg_e(HG_PHONE_DISPLAY) ?></span></a></li>
                        <li><a class="hg-qlink" href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener"><?= hg_icon('whatsapp', 'hg-qlink__wa') ?><span class="hg-qlink__label">WhatsApp Expert</span><span class="hg-qlink__value"><?= hg_e(HG_PHONE_DISPLAY) ?></span></a></li>
                        <li><a class="hg-qlink" href="<?= hg_e(hg_mailto_href()) ?>"><?= hg_icon('mail') ?><span class="hg-qlink__label">Email Us</span><span class="hg-qlink__value"><?= hg_e(HG_EMAIL_DISPLAY) ?></span></a></li>
                    </ul>
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
                            <li><a class="hg-fnav__link" href="<?= hg_e($hgItem['url']) ?>"<?= !empty($hgItem['target']) ? ' target="' . hg_e($hgItem['target']) . '" rel="noopener"' : '' ?>><?= hg_e($hgItem['label']) ?><?php if (!empty($hgItem['badge'])) { ?> <span class="hg-fnav__badge hg-fnav__badge--info"><?= hg_e($hgItem['badge']) ?></span><?php } ?><span class="hg-fnav__arrow" aria-hidden="true">&rarr;</span></a></li>
                            <?php } else { ?>
                            <li><span class="hg-fnav__soon"><?= hg_e($hgItem['label']) ?> <span class="hg-fnav__badge">Coming soon</span></span></li>
                            <?php } ?>
                            <?php } ?>
                        </ul>
                    </details>
                    <?php } ?>
                </nav>
            </div>
        </div>
        <div class="hg-footer__bottom">
            <div class="container">
                <div class="hg-footer__legal">
                    <p class="hg-footer__entity"><?= hg_e(HG_LEGAL_NAME) ?><span class="hg-footer__sep" aria-hidden="true">|</span>CIN <?= hg_e(HG_CIN) ?></p>
                    <p class="hg-footer__copy">&copy; Holiday Guru Travel<span class="hg-footer__sep" aria-hidden="true">|</span>All Rights Reserved</p>
                </div>
                <div class="hg-footer__meta">
                    <button type="button" class="hg-footer-link" data-hg-consent-open>Cookie settings</button>
                    <span class="hg-footer__credit">Designed by <a href="https://sbbjitsolutions.com/" target="_blank" rel="noopener">SBBJ IT SOLUTIONS</a></span>
                </div>
            </div>
        </div>
    </footer>
