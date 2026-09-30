    /* ---------------- Cookie consent ---------------- */

    function readConsent() {
        var match = document.cookie.match(/(?:^|;\s*)hg_consent=([^;]*)/);
        if (!match) return null;
        var parts = decodeURIComponent(match[1]).split('.');
        if (parts[0] !== CONSENT_VERSION) return null; // policy changed: ask again
        return { analytics: parts[1] === 'a1' };
    }

    function writeConsent(analytics) {
        var value = CONSENT_VERSION + '.' + (analytics ? 'a1' : 'a0');
        var cookie = CONSENT_COOKIE + '=' + encodeURIComponent(value) +
            '; Max-Age=' + (CONSENT_DAYS * 86400) + '; Path=/; SameSite=Lax';
        if (location.protocol === 'https:') cookie += '; Secure';
        document.cookie = cookie;
    }

    var gaLoaded = false;
    function loadAnalytics() {
        var id = window.HG_SITE && window.HG_SITE.ga4Id;
        if (gaLoaded || !id) return;
        gaLoaded = true;
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', id);
        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
        document.head.appendChild(s);
    }

    function removeAnalyticsCookies() {
        var host = location.hostname.replace(/^www\./, '');
        document.cookie.split(';').forEach(function (c) {
            var name = c.split('=')[0].trim();
            if (name === '_ga' || name.indexOf('_ga_') === 0 || name === '_gid' || name === '_gat') {
                ['', '; Domain=' + host, '; Domain=.' + host].forEach(function (d) {
                    document.cookie = name + '=; Max-Age=0; Path=/' + d;
                });
            }
        });
        if (gaLoaded && window.gtag) window.gtag('consent', 'update', { analytics_storage: 'denied' });
    }

    // Analytics events for the whole site: sent only when analytics consent is
    // current and GA has loaded; otherwise silently dropped (never queued).
    window.hgTrack = function (name, params) {
        var c = readConsent();
        if (!gaLoaded || !window.gtag || !c || !c.analytics) return;
        window.gtag('event', name, params || {});
    };

    function applyConsent(consent) {
        if (consent && consent.analytics) loadAnalytics();
        else removeAnalyticsCookies();
    }

    var banner = document.getElementById('hg-consent');
    var modal = document.getElementById('hg-consent-settings');
    var analyticsBox = document.getElementById('hg-consent-analytics');
    var lastFocus = null;

    function setBannerOffset() {
        var h = banner && !banner.hidden ? banner.getBoundingClientRect().height : 0;
        // On wide screens the banner is inset 16px from the bottom; add a 12px gap.
        root.style.setProperty('--hg-consent-h', h ? Math.ceil(h + (window.innerWidth > 575 ? 16 : 0) + 12) + 'px' : '0px');
    }

    function showBanner() {
        if (!banner) return;
        // Move the banner to the start of <body> so keyboard users reach it first.
        if (document.body.firstElementChild !== banner) document.body.insertBefore(banner, document.body.firstChild);
        banner.hidden = false;
        setBannerOffset();
    }

    function hideBanner() {
        if (!banner) return;
        banner.hidden = true;
        setBannerOffset();
    }

    function openSettings() {
        if (!modal) return;
        var current = readConsent();
        if (analyticsBox) analyticsBox.checked = !!(current && current.analytics);
        lastFocus = document.activeElement;
        modal.hidden = false;
        var first = modal.querySelector('input:not([disabled]), button');
        if (first) first.focus();
    }

    function closeSettings() {
        if (!modal || modal.hidden) return;
        modal.hidden = true;
        if (lastFocus && document.body.contains(lastFocus)) lastFocus.focus();
    }

    function decide(analytics) {
        writeConsent(analytics);
        applyConsent({ analytics: analytics });
        closeSettings();
        hideBanner();
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-hg-consent]');
        if (btn) {
            var action = btn.getAttribute('data-hg-consent');
            if (action === 'accept') decide(true);
            else if (action === 'reject') decide(false);
            else if (action === 'save') decide(!!(analyticsBox && analyticsBox.checked));
            else if (action === 'settings') openSettings();
            return;
        }
        if (e.target.closest('[data-hg-consent-open]')) {
            e.preventDefault();
            openSettings();
            return;
        }
        if (modal && e.target === modal) closeSettings();
    });

    if (modal) {
        document.addEventListener('keydown', function (e) {
            if (modal.hidden) return;
            if (e.key === 'Escape') {
                closeSettings();
                return;
            }
            if (e.key !== 'Tab') return;
            if (!modal.contains(document.activeElement)) {
                e.preventDefault();
                modal.querySelector('input:not([disabled]), button').focus();
                return;
            }
            var items = modal.querySelectorAll('input:not([disabled]), button, a[href]');
            if (!items.length) return;
            var first = items[0], last = items[items.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        });
    }

    var consent = readConsent();
    if (consent) applyConsent(consent);
    else showBanner();
    window.addEventListener('resize', setBannerOffset);

