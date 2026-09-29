<?php require_once __DIR__ . '/site_config.php'; ?>
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
                                        class="th-btn style3">Subscribe Now <img src="assets/img/icon/plane.svg"
                                            alt=""></button></form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hg-footer__top">
                    <a href="/" class="hg-footer__logo"><img src="assets/img/logopng.png" alt="Holiday Guru Travel" width="180"></a>
                    <p class="hg-footer__tagline">Your journey. Your way.</p>
                    <p class="hg-footer__intro">Holiday packages across India and abroad, planned by the Holiday Guru Travel team from our office in Noida.</p>
                    <ul class="hg-footer__quick">
                        <li><a class="hg-qlink hg-qlink--primary" href="<?= hg_e(hg_tel_href()) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i><span class="hg-qlink__label">Call Now</span><span class="hg-qlink__value"><?= hg_e(HG_PHONE_DISPLAY) ?></span></a></li>
                        <li><a class="hg-qlink" href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp hg-qlink__wa" aria-hidden="true"></i><span class="hg-qlink__label">WhatsApp Expert</span><span class="hg-qlink__value"><?= hg_e(HG_PHONE_DISPLAY) ?></span></a></li>
                        <li><a class="hg-qlink" href="<?= hg_e(hg_mailto_href()) ?>"><i class="fa-regular fa-envelope" aria-hidden="true"></i><span class="hg-qlink__label">Email Us</span><span class="hg-qlink__value"><?= hg_e(HG_EMAIL_DISPLAY) ?></span></a></li>
                    </ul>
                    <div class="hg-footer__social">
                        <a href="https://www.facebook.com/share/dPbbE3G2et8VmGMu/?mibextid=qi2Omg" target="_blank" rel="noopener" aria-label="Holiday Guru Travel on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                        <a href="https://www.instagram.com/holidaygurutravel?igsh=MXFwOHJ5bTFjeGxsZw==" target="_blank" rel="noopener" aria-label="Holiday Guru Travel on Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
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
    <div class="scroll-top"><svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"
                style="transition: stroke-dashoffset 10ms linear 0s; stroke-dasharray: 307.919, 307.919; stroke-dashoffset: 307.919;">
            </path>
        </svg></div>
    <?php include __DIR__ . "/support-widget.php"; ?>
    <?php include __DIR__ . "/cookie-consent.php"; ?>
    <script src="assets/js/vendor/jquery-3.6.0.min.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/jquery-ui.min.js"></script>
    <script src="assets/js/imagesloaded.pkgd.min.js"></script>
    <script src="assets/js/isotope.pkgd.min.js"></script>
    <script src="assets/js/gsap.min.js"></script>
    <script src="assets/js/circle-progress.js"></script>
    <script src="assets/js/matter.min.js"></script>
    <script src="assets/js/matterjs-custom.js"></script>
    <script src="assets/js/nice-select.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/hg-site.js" defer></script>
    <script>
    $(function () {
      var loadedAt = Date.now();
      var forms = {
        '#contactForm1': 'mail.php',
        '#contactForm2': 'mail.php',
        '#contactForm3': 'mail.php',
        '#newsletter': 'mail1.php'
      };
      $.each(forms, function (selector, url) {
        var $form = $(selector);
        if (!$form.length) return;
        // Honeypot (hidden from people, filled by bots) + page load time + source page.
        $form.append(
          '<input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" ' +
          'style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">'
        );
        $form.on('submit', function (event) {
          event.preventDefault();
          var data = $form.serialize() +
            '&_ts=' + loadedAt +
            '&page=' + encodeURIComponent(window.location.href);
          $.ajax({ url: url, type: 'POST', data: data })
            .done(function (response) {
              if ($.trim(response) === '1') {
                alert('form submitted');
                $form[0].reset();
              } else {
                alert('form not submitted');
              }
            })
            .fail(function () {
              alert('form not submitted');
            });
        });
      });
    });
    </script>
    <script>
                            function validateUsername(input) {
                                // Regular expression to match only letters and spaces, excluding numeric characters
                                var regex = /^[a-zA-Z ]*$/;
                                if (!regex.test(input.value)) {
                                    // Remove last character if it does not match the regex
                                    input.value = input.value.slice(0, -1);
                                }
                            }
                            function validateNumeric(input) {
                                // Regular expression to match only numeric characters
                                var regex = /^[0-9]*$/;
                                if (!regex.test(input.value)) {
                                    // Remove last character if it does not match the regex
                                    input.value = input.value.slice(0, -1);
                                }
                            }
</script>


</body>

</html>