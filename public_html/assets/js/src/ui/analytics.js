    /* ---------------- Analytics (consent-gated, see hg-site.js) ---------------- */
    function track(name, params) { if (window.hgTrack) window.hgTrack(name, params); }
    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-hg-track]');
        if (t && !t.closest('.hg-support')) track(t.getAttribute('data-hg-track'), { link_url: t.getAttribute('href') || '' });
    });

