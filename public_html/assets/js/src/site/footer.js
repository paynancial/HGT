    /* ---------------- Footer navigation accordion ---------------- */
    // <details> sections are open in the HTML (works without JS). On screens
    // up to 991px they start closed and toggle; on desktop they stay open.
    var fnavs = document.querySelectorAll('[data-hg-fnav]');
    if (fnavs.length && window.matchMedia) {
        var desktop = window.matchMedia('(min-width: 992px)');
        var syncFnav = function () {
            for (var i = 0; i < fnavs.length; i++) {
                var summary = fnavs[i].querySelector('summary');
                if (desktop.matches) {
                    fnavs[i].open = true;
                    summary.setAttribute('tabindex', '-1');
                } else {
                    summary.removeAttribute('tabindex');
                    if (!fnavs[i].hasAttribute('data-hg-touched')) fnavs[i].open = false;
                }
            }
        };
        for (var k = 0; k < fnavs.length; k++) {
            fnavs[k].querySelector('summary').addEventListener('click', function (e) {
                if (desktop.matches) { e.preventDefault(); return; }
                this.parentNode.setAttribute('data-hg-touched', '');
            });
        }
        if (desktop.addEventListener) desktop.addEventListener('change', syncFnav);
        else if (desktop.addListener) desktop.addListener(syncFnav);
        syncFnav();
    }

