    /* ---------------- Floating support widget ---------------- */

    var widget = document.getElementById('hg-support');
    if (!widget) return;
    var toggle = widget.querySelector('.hg-support__toggle');
    var panel = document.getElementById('hg-support-panel');
    var views = widget.querySelectorAll('[data-hg-view]');

    function showView(name) {
        for (var i = 0; i < views.length; i++) {
            views[i].hidden = views[i].getAttribute('data-hg-view') !== name;
        }
    }

    function openPanel() {
        showView('actions');
        panel.hidden = false;
        widget.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Close contact options');
        var first = panel.querySelector('.hg-support__action');
        if (first) first.focus();
    }

    function closePanel(returnFocus) {
        panel.hidden = true;
        widget.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Contact Holiday Guru Travel');
        if (returnFocus) toggle.focus();
    }

    toggle.addEventListener('click', function () {
        if (panel.hidden) openPanel(); else closePanel(false);
    });
    widget.querySelector('[data-hg-support-close]').addEventListener('click', function () { closePanel(true); });
    widget.querySelector('[data-hg-chat-open]').addEventListener('click', function () {
        showView('chat');
        var first = widget.querySelector('[data-hg-view="chat"] a');
        if (first) first.focus();
    });
    widget.querySelector('[data-hg-chat-back]').addEventListener('click', function () {
        showView('actions');
        var first = panel.querySelector('.hg-support__action');
        if (first) first.focus();
    });
    widget.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) closePanel(true);
    });
    document.addEventListener('click', function (e) {
        if (!panel.hidden && !widget.contains(e.target)) closePanel(false);
    });

    // Package pages carry the enquiry sidebar (#contactForm3) and the package
    // name in the page H1: pre-fill WhatsApp with that package.
    // Phase 1 package pages provide the full contextual message (package, date,
    // travellers, departure) on their own WhatsApp button: reuse it.
    var ctxWa = document.querySelector('.hg-bookcard .hg-btn--wa');
    var heading = document.querySelector('h1.breadcumb-title');
    var packageName = document.getElementById('contactForm3') && heading ? heading.textContent.replace(/\s+/g, ' ').trim() : '';
    if (ctxWa) {
        var wl = widget.querySelectorAll('[data-hg-whatsapp]');
        for (var k = 0; k < wl.length; k++) wl[k].setAttribute('href', ctxWa.getAttribute('href'));
    } else if (packageName) {
        var message = 'Hi Holiday Guru Travel,\nI am interested in the ' + packageName +
            ' package.\nPlease share the details and best available quote.';
        var links = widget.querySelectorAll('[data-hg-whatsapp]');
        for (var j = 0; j < links.length; j++) {
            var base = links[j].getAttribute('href').split('?')[0];
            links[j].setAttribute('href', base + '?text=' + encodeURIComponent(message));
        }
    }

    // GA4 event for contact actions (sent only if analytics was accepted and GA loaded).
    widget.addEventListener('click', function (e) {
        var a = e.target.closest('[data-hg-track]');
        if (a && gaLoaded && window.gtag) {
            window.gtag('event', 'contact_click', { method: a.getAttribute('data-hg-track') });
        }
    });
