<?php require_once __DIR__ . '/site_config.php'; ?>
<?php include __DIR__ . "/partials/site-footer.php"; ?>
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