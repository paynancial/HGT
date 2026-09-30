    /* ---------------- Login dialog ---------------- */
    var login = document.getElementById('hg-login');
    $$('[data-hg-login-open]').forEach(function (b) {
        b.addEventListener('click', function () {
            if (!login) return;
            if (nav && nav.classList.contains('is-open')) setDrawer(false);
            if (typeof login.showModal === 'function') login.showModal(); else login.setAttribute('open', '');
        });
    });
    if (login) login.addEventListener('click', function (e) { if (e.target === login) login.close(); });

