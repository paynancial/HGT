    /* ---------------- Enquiry + newsletter forms ---------------- */
    var loadedAt = Date.now();
    // Campaign source (utm_*) is kept for this visit and sent with enquiries.
    var UTM = ['utm_source', 'utm_medium', 'utm_campaign'];
    (function () {
        var q = new URLSearchParams(window.location.search);
        UTM.forEach(function (k) { var v = q.get(k); if (v) { try { sessionStorage.setItem('hg_' + k, v.slice(0, 100)); } catch (e) {} } });
    })();
    function addUtm(form) {
        UTM.forEach(function (k) {
            var v = null;
            try { v = sessionStorage.getItem('hg_' + k); } catch (e) {}
            if (!v || form.querySelector('input[name="' + k + '"]')) return;
            var h = document.createElement('input'); h.type = 'hidden'; h.name = k; h.value = v; form.appendChild(h);
        });
    }
    function guardFields(form) {
        addUtm(form);
        if (form.querySelector('input[name="website"]')) return;
        var hp = document.createElement('input');
        hp.type = 'text'; hp.name = 'website'; hp.tabIndex = -1; hp.autocomplete = 'off';
        hp.setAttribute('aria-hidden', 'true');
        hp.style.cssText = 'position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden';
        form.appendChild(hp);
    }
    function status(form, msg, ok) {
        var s = form.querySelector('.hg-form__status');
        if (!s) { alert(msg); return; }
        s.textContent = msg;
        s.className = 'hg-form__status ' + (ok ? 'is-ok' : 'is-error');
    }
    function send(form, url) {
        var data = new FormData(form);
        data.append('_ts', String(loadedAt));
        data.append('page', window.location.href);
        var btn = form.querySelector('[type="submit"]');
        if (btn) btn.disabled = true;
        return fetch(url, { method: 'POST', body: new URLSearchParams(data), credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (t) { return t.trim() === '1'; })
            .catch(function () { return false; })
            .then(function (ok) { if (btn) btn.disabled = false; return ok; });
    }
    $$('form[data-hg-enquiry]').forEach(function (form) {
        guardFields(form);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var bad = null;
            $$('input[required]', form).forEach(function (el) {
                var valid = el.value.trim() !== '' && el.checkValidity();
                el.setAttribute('aria-invalid', valid ? 'false' : 'true');
                if (!valid && !bad) bad = el;
            });
            if (bad) { status(form, 'Please check the highlighted fields.', false); bad.focus(); return; }
            status(form, 'Sending…', true);
            send(form, '/mail.php').then(function (ok) {
                if (ok) {
                    status(form, 'Thank you. A travel expert will contact you shortly on phone or WhatsApp.', true);
                    form.reset();
                    var pk = form.querySelector('[name="package_url"]');
                    track('enquiry_submit', { form_id: form.id, package_url: pk ? pk.value : '' });
                } else {
                    status(form, 'Sorry, we could not send this. Please call or WhatsApp +91 80066 92040.', false);
                }
            });
        });
    });
    var news = document.getElementById('newsletter');
    if (news && !window.jQuery) {
        guardFields(news);
        news.addEventListener('submit', function (e) {
            e.preventDefault();
            send(news, '/mail1.php').then(function (ok) {
                alert(ok ? 'Thank you for subscribing.' : 'Sorry, that did not work. Please try again later.');
                if (ok) news.reset();
            });
        });
    }

    /* Prefill enquiry forms from the search context (date, travellers, departure) */
    var params = new URLSearchParams(window.location.search);
    $$('form[data-hg-enquiry]').forEach(function (form) {
        var map = { travel_date: 'date', adults: 'adults', children: 'children', departure_city: 'departure' };
        Object.keys(map).forEach(function (k) {
            var v = params.get(map[k]);
            var el = form.querySelector('[name="' + k + '"]');
            if (!v || !el || el.value && el.type === 'hidden') return;
            if (el.tagName === 'SELECT' && !$('option[value="' + v.replace(/"/g, '') + '"]', el)) return;
            el.value = v;
        });
        var d = params.get('date'), m = form.querySelector('[name="travel_month"]');
        if (d && m && /^\d{4}-\d{2}/.test(d) && $('option[value="' + d.slice(0, 7) + '"]', m)) m.value = d.slice(0, 7);
        form.addEventListener('focusin', function once() { track('enquiry_start', { form_id: form.id }); form.removeEventListener('focusin', once); });
    });

    /* Package page view (slug only; package number once the registry exists) */
    var pv = $('[data-hg-package-view]');
    if (pv) track('package_view', { package_slug: pv.getAttribute('data-hg-package-view'), package_number: pv.getAttribute('data-package-number') || '' });

