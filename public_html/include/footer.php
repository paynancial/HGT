<?php require_once __DIR__ . '/site_config.php'; ?>
<style>
    @media only screen and (max-width: 600px) {
 .footer-card{
    text-align:center;
  }
}
</style>
<footer class="footer-wrapper footer-layout1">
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
                <div class="row justify-content-between">
                    <div class="col-md-6 col-xl-3">
                        <div class="widget footer-widget">
                            <div class="th-widget-about">
                                
                                <div class="about-logo"><a href="/"><img src="assets/img/logopng.png"
                                            alt="logopng.png" width="150px"></a></div>
                                <div class="holiday package">
                                <i class="icd-ico ich ich_globe pull-left"></i>
                                <p style="text-align: justify;">Born from a love for travel and a desire to share the joy of exploration, Holiday Guru Travel was founded with a vision to redefine the way you experience the world. We understand that travel is not just about reaching a destination; it's about the moments, the connections, and the memories that last a lifetime.</p>
                                
                                </div>
                                </div>
                                </div>
                                </div>
                                <!--<p class="about-text">Rapidiously myocardinate cross-platform intellectual capital-->
                                <!--    model. Appropriately create interactive infrastructures</p>-->
                                
                   <div class="col-md-6 col-xl-auto">
                        <div class="widget widget_nav_menu footer-widget">
                            <h3 class="widget_title">Themes</h3>
                            <div class="menu-all-pages-container">
                                <ul class="menu">
                                            <li><a href="/family-holiday">Family Holiday</a></li>
                                            <li><a href="/beach-holiday">Beach Holiday</a></li>
                                            <li><a href="/hill-station-holidays">Hill Station Holidays</a></li>
                                            <li><a href="/honeymoon-holiday">Honeymoon Holiday</a></li>
                                            <li><a href="/pilgrim-holidays">Pilgrim Holidays</a></li>
                                            <li><a href="/adventure-holiday">Adventure Holiday</a></li>
                               </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-auto">
                        <div class="widget widget_nav_menu footer-widget">
                            <h3 class="widget_title">Related Links</h3>
                            <div class="menu-all-pages-container">
                                <ul class="menu">
                                    <li><a href="/">Home</a></li>
                                    <li><a href="/about">About us</a></li>
                                    <li><a href="/contact">Contact Us</a></li>
                                    <li><a href="/service">Our Services</a></li>
                                    
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-auto">
                        <div class="widget footer-widget">
                            <h3 class="widget_title">Contact Us</h3>
                            <div class="th-widget-contact">
                                <div class="info-box_text">
                                    <div class="icon"><img src="assets/img/icon/add.png" alt="add.png"></div>
                                    <div class="details">
                                        <p>India</p>
                                        
                                    </div>
                                </div>
                                <div class="info-box_text">
                                    <div class="icon"><img src="assets/img/icon/phone1.png" width="18px" alt="Travel Agency India"></div>
                                    <div class="details">
                                    <p><a href="<?= hg_e(hg_tel_href()) ?>" class="info-box_link" aria-label="Call Holiday Guru Travel on <?= hg_e(HG_PHONE_DISPLAY) ?>"><?= hg_e(HG_PHONE_DISPLAY) ?></a></p>
                                        
                                    </div>
                                </div>
                                <div class="info-box_text">
                                    <div class="icon"><img src="assets/img/icon/email.png" alt="Mail to Travel Agency in Delhi"></div>
                                    <div class="details">
                                        <p><a href="<?= hg_e(hg_mailto_href()) ?>" class="info-box_link"><?= hg_e(HG_EMAIL_DISPLAY) ?></a></p>
                                        
                                    </div>
                                </div>
                                
                                
                            </div>
                            <h3 class="widget_title mt-4">Connect With Us</h3>
                                <div class="th-social">
                                    <a href="https://www.facebook.com/share/dPbbE3G2et8VmGMu/?mibextid=qi2Omg"><i
                                            class="fab fa-facebook-f"></i>
                                    </a>
                                    <!--<a href="https://www.twitter.com/"><i-->
                                    <!--        class="fab fa-twitter"></i>-->
                                    <!--</a>-->
                                    <!--<a href="https://www.linkedin.com/"><i-->
                                    <!--        class="fab fa-linkedin-in"></i>-->
                                    <!--</a>-->
                                    <a href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener" aria-label="WhatsApp Holiday Guru Travel"><i
                                        class="fab fa-whatsapp"></i>
                                    </a>
                                    <a href="https://www.instagram.com/holidaygurutravel?igsh=MXFwOHJ5bTFjeGxsZw=="><i
                                            class="fab fa-instagram"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    
                    
                </div>
            </div>
        </div>
        <div class="copyright-wrap" data-bg-src="assets/img/copyright_bg_1.jpg">
            <div class="container">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-6">
                        <p class="copyright-text">Copyright 2014 <a href="/">Holiday Guru Travels</a>. All Rights
                            Reserved.</p>
                        <p class="copyright-text"><button type="button" class="hg-footer-link" data-hg-consent-open>Cookie settings</button></p>
                    </div>
                    <div class="col-md-6 text-end d-md-block">
                        <div class="footer-card"><span class="title">Designed By: <a href="https://sbbjitsolutions.com/" target="blank">SBBJ IT SOLUTIONS</a></span>
                    </div>
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