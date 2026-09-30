// End-to-end test of the staging Tour Package CMS (brief §54). Runs against a FRESH staging database.
//   php cms/bin/setup.php --reset && php -S 127.0.0.1:8099 -t cms/public cms/public/router.php &
//   node cms/tests/browser/cms-e2e.js
// Env: HG_CMS_URL (default http://127.0.0.1:8099), PLAYWRIGHT_MODULE.
// Everything it creates is labelled "Staging test" (package, images, rate, offer, enquiry). Never run against production.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('fs'), path = require('path'), crypto = require('crypto'), { execFileSync } = require('child_process');
const B = process.env.HG_CMS_URL || 'http://127.0.0.1:8099';
if (/holidaygurutravel\.in/.test(B)) { console.error('Refusing to run against the live site.'); process.exit(2); }
const ROOT = path.resolve(__dirname, '../..');
const creds = Object.fromEntries(fs.readFileSync(ROOT + '/storage/.staging-credentials', 'utf8').split('\n').filter(l => l.includes('@')).map(l => { const p = l.trim().split(/\s+/); return [p[p.length - 2], p[p.length - 1]]; }));
const cfg = JSON.parse(execFileSync('php', ['-r', 'echo json_encode(require "' + ROOT + '/' + (fs.existsSync(ROOT + '/config.php') ? 'config.php' : 'config.example.php') + '");']).toString());
const TMP = fs.mkdtempSync('/tmp/hgcms-e2e-');
// Clearly marked development placeholder images (solid colour + "STAGING TEST IMAGE" text).
execFileSync('php', ['-r', `foreach (array('featured' => array(10,22,61), 'g1' => array(22,137,216), 'g2' => array(254,127,22), 'g3' => array(255,194,10)) as $n => $c) { $i = imagecreatetruecolor(1920, 1080); imagefill($i, 0, 0, imagecolorallocate($i, $c[0], $c[1], $c[2])); $w = imagecolorallocate($i, 255, 255, 255); for ($k = 0; $k < 5; $k++) imagestring($i, 5, 760, 480 + $k * 20, 'STAGING TEST IMAGE - ' . strtoupper($n), $w); imagejpeg($i, '${TMP}/' . $n . '.jpg', 85); }`]);

let pass = 0, fail = 0;
const t = (ok, msg) => { ok ? pass++ : fail++; console.log((ok ? 'PASS  ' : 'FAIL  ') + msg); };

(async () => {
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  p.on('dialog', d => d.accept());
  const errors = []; p.on('pageerror', e => errors.push(e.message)); p.on('console', m => { if (m.type() === 'error') errors.push(m.text()); });
  const go = u => p.goto(B + u, { waitUntil: 'networkidle' });
  const login = async who => {
    await ctx.clearCookies(); await go('/login');
    await p.fill('#f-email', who); await p.fill('#f-password', creds[who]);
    await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  };
  const flash = () => p.locator('.cms-flash').allInnerTexts().then(a => a.join(' | '));
  const save = async (btn = '.cms-formbar button[type=submit]:not([name])') => { await Promise.all([p.waitForNavigation(), p.click(btn)]); };
  const text = sel => p.locator(sel).innerText();

  try {
    /* ---- Access control ---- */
    await go('/'); t(p.url().includes('/login'), 'CMS requires login');
    await login('super.admin@staging.invalid');
    t(await p.locator('h1').innerText() === 'Dashboard', 'Super Admin signs in to the dashboard');

    /* ---- Create package → Package ID pending (assignment off until owner approval) ---- */
    await go('/packages/new');
    await p.fill('#f-name', 'Staging Test Package Kashmir');
    await p.selectOption('#f-destination', 'kashmir');
    await p.fill('#f-city_route', 'Srinagar – Gulmarg – Pahalgam');
    await p.selectOption('#f-package_type', 'Family');
    await p.fill('#f-days', '4'); await p.fill('#f-nights', '3');
    await Promise.all([p.waitForNavigation(), p.click('button:has-text("Create draft")')]);
    const pk = +p.url().match(/packages\/(\d+)/)[1];
    t(/Pending approval/.test(await text('.cms-pagehead')), 'new package: Package ID shows "Pending approval"');
    t(/Draft/.test(await text('.cms-pagehead')), 'new package starts as Draft');
    await go(`/packages/${pk}?tab=advanced`);
    t(await p.locator('button:has-text("Assign Package ID")').isDisabled(), 'Assign Package ID is disabled until the owner approves the mapping');

    /* ---- Basic information ---- */
    await go(`/packages/${pk}?tab=basic`);
    await p.fill('#f-short_description', 'Staging test package used to verify the CMS workflow. Four days around Srinagar, Gulmarg and Pahalgam with a houseboat night. Not a real product; not for sale.');
    await p.click('#desc-ed');
    await p.keyboard.type('This is a staging test description written to exercise the rich text editor and the publication checklist of the Holiday Guru Travel CMS. It is not a real tour and must never be published to the live website. ' +
      'The package covers Srinagar, Gulmarg and Pahalgam over four days and three nights. The text is deliberately plain so that reviewers can see how the editor stores headings, paragraphs and lists, and how the checklist counts words before a package may be published on staging.');
    await p.fill('#f-highlights', 'Staging test highlight: Dal Lake shikara ride\nStaging test highlight: Gulmarg gondola\nStaging test highlight: Betaab Valley');
    await p.check('input[value="Families"]'); await p.check('input[value="Couples"]');
    await save();
    t(/Basic information saved · version 2/.test(await flash()), 'basic information saved as version 2');

    /* ---- Itinerary: add days, mismatch warning, delete, reorder; Package ID unchanged ---- */
    await go(`/packages/${pk}?tab=itinerary`);
    for (let i = 0; i < 5; i++) await save('button[name=op][value=add]');
    t(await p.locator('[data-cms-day]').count() === 5, 'five days added with + Add Day');
    t(/Itinerary contains 5 days but package duration is 4 days/.test(await text('.cms-editor__main')), 'duration mismatch warning shown (5 days vs 4)');
    t(/Duration matches itinerary/.test(await p.locator('.cms-checklist .is-bad').allInnerTexts().then(a => a.join())), 'mismatch listed as a publishing blocker');
    await save('button[name=op][value="del:4"]');
    t(await p.locator('[data-cms-day]').count() === 4 && !/WARNING/.test(await text('.cms-editor__main')), 'deleting the extra day clears the warning');
    const days = [['Arrival in Srinagar', 'Staging test: arrive at Srinagar, transfer to the houseboat, evening shikara ride.'], ['Srinagar to Gulmarg', 'Staging test: day trip to Gulmarg with optional gondola ride.'], ['Srinagar to Pahalgam', 'Staging test: drive to Pahalgam, visit Betaab Valley.'], ['Departure', 'Staging test: transfer to Srinagar airport.']];
    for (let i = 0; i < 4; i++) { await p.locator('[data-cms-day] details').nth(i).evaluate(d => d.open = true); await p.fill(`#d${i}-title`, days[i][0]); await p.fill(`#d${i}-desc`, days[i][1]); await p.fill(`#d${i}-meals`, 'Breakfast, Dinner'); }
    await save();
    // Reorder: move day 3 up, save, then move it back.
    await p.locator('[data-cms-day] details').nth(2).evaluate(d => d.open = true);
    await p.locator('[data-cms-day]').nth(2).locator('[data-cms-move="-1"]').click();
    await save();
    t((await p.locator('.cms-day__t').allInnerTexts())[1] === 'Srinagar to Pahalgam', 'reorder moves a day and renumbers');
    await p.locator('[data-cms-day] details').nth(1).evaluate(d => d.open = true);
    await p.locator('[data-cms-day]').nth(1).locator('[data-cms-move="1"]').click();
    await save();
    t((await p.locator('.cms-day__t').allInnerTexts()).join('|') === days.map(d => d[0]).join('|'), 'itinerary saved in the right order');
    await save('button[name=op][value="dup:0"]');
    t(await p.locator('[data-cms-day]').count() === 5, 'Duplicate Day copies a day');
    await save('button[name=op][value="del:1"]');
    t(/Pending approval/.test(await text('.cms-ithead')), 'itinerary header shows the (pending) Package ID; itinerary is linked to the package');

    /* ---- Images: upload, featured, alt text, crop, reorder ---- */
    await go(`/packages/${pk}?tab=media`);
    await p.setInputFiles('#m-files', `${TMP}/featured.jpg`);
    await p.selectOption('#m-role', 'featured');
    await p.fill('#m-alt', 'Staging test image placeholder (navy)');
    await save('button[name=op][value=upload]');
    t(/1 image uploaded/.test(await flash()), 'featured image uploaded');
    await p.setInputFiles('#m-files', [`${TMP}/g1.jpg`, `${TMP}/g2.jpg`, `${TMP}/g3.jpg`]);
    await p.selectOption('#m-role', 'gallery');
    await p.fill('#m-alt', '');
    await save('button[name=op][value=upload]');
    t(/3 images uploaded.*Add alt text/.test(await flash()), 'three gallery images uploaded; alt text reminder shown');
    t(/Alt text on every image/.test(await p.locator('.cms-checklist .is-bad').allInnerTexts().then(a => a.join())), 'missing alt text blocks publishing');
    const alts = await p.locator('input[name$="[alt_text]"]').all();
    for (let i = 0; i < alts.length; i++) await alts[i].fill('Staging test image placeholder ' + (i + 1));
    await save();
    t(!/Alt text on every image/.test(await p.locator('.cms-checklist .is-bad').allInnerTexts().then(a => a.join())), 'alt text saved on every image');
    await p.setInputFiles('#m-files', `${TMP}/g1.jpg`); await p.fill('#m-alt', 'x');
    const bad = `${TMP}/not-an-image.jpg`; fs.writeFileSync(bad, 'not an image');
    await p.setInputFiles('#m-files', bad);
    await save('button[name=op][value=upload]');
    t(/Only JPG, JPEG, PNG, WEBP and AVIF/.test(await flash()), 'a non-image file is rejected');
    const firstGallery = await p.locator('.cms-card:has(h2:text-is("Gallery")) button[value^="crop:"]').first().getAttribute('value');
    await save(`button[value="${firstGallery}"]`);
    t(/Cropped to 16:9/.test(await flash()), 'crop creates a cropped copy');
    const img = await p.locator('.cms-mini__img img').getAttribute('src');
    const r = await p.request.get(B + img);
    t(r.ok() && (r.headers()['content-type'] || '').startsWith('image/webp'), 'live preview uses an optimised WebP variant');

    /* ---- Pricing: version 1 then version 2 ---- */
    await go(`/packages/${pk}?tab=pricing`);
    t(/Price on request/.test(await text('.cms-ratebox')), 'no rate → Price on request');
    const d = n => { const x = new Date(); x.setDate(x.getDate() + n); return x.toISOString().slice(0, 10); };
    await p.fill('#f-base_price', '10000'); await p.fill('#f-valid_from', d(0)); await p.fill('#f-valid_until', d(30));
    await p.fill('#f-price_notes', 'Staging test rate — not a real price');
    await p.check('#f-approve_now-1');
    await save();
    t(/Price version 1 approved/.test(await flash()) && /₹10,000 \/ person/.test(await text('.cms-ratebox')), 'price version 1 approved and current');
    await p.fill('#f-base_price', '12000'); await p.fill('#f-valid_from', d(0)); await p.fill('#f-valid_until', d(45)); await p.fill('#f-reason', 'Staging test: rate change');
    await p.fill('#f-price_notes', 'Staging test rate — not a real price');
    await save();
    t(/Price version 2 saved as draft/.test(await flash()) && /₹10,000/.test(await text('.cms-ratebox')), 'price version 2 saved as a draft; v1 stays current until approved');
    await save('button[name=op][value^="approve:"]');
    t(/₹12,000 \/ person/.test(await text('.cms-ratebox')) && /Price version 2/.test(await text('.cms-ratebox')), 'approved price version 2 becomes current');
    t(/₹10,000 → ₹12,000/.test(await text('table')), 'history shows old rate → new rate');
    await p.fill('#f-base_price', '9000'); await p.fill('#f-valid_from', d(-20)); await p.fill('#f-valid_until', d(-10)); await p.fill('#f-reason', 'test');
    await save();
    t(/already ended/.test(await flash()), 'an expired validity cannot be entered as a new rate');

    /* ---- Inclusions & exclusions ---- */
    await go(`/packages/${pk}?tab=inclusions`);
    await save('button[name=op][value="add:inclusion"]');
    await p.locator('.cms-scopelist').first().locator('input[name$="[name]"]').last().fill('Staging test inclusion: houseboat night');
    await save();
    await save('button[name=op][value="add:exclusion"]');
    await p.locator('.cms-scopelist').nth(1).locator('input[name$="[name]"]').last().fill('Staging test exclusion: pony rides');
    await save();
    t(/Inclusions & exclusions saved/.test(await flash()), 'inclusion and exclusion added');
    await p.selectOption('.cms-scope.is-standard select >> nth=0', 'hidden');
    await save();
    t(/can be hidden only when an inclusion states it is included/.test(await flash()), 'standard airfare exclusion cannot be hidden without a matching inclusion');
    t(await p.locator('.cms-scope.is-standard').count() === 3 && await p.locator('.cms-scope.is-standard button[aria-label^="Remove"]').count() === 0, 'standard exclusions are fixed and not deletable');

    /* ---- Add-ons ---- */
    await go(`/packages/${pk}?tab=addons`);
    await save('button[name=op][value=add]');
    await p.fill('#a0-name', 'Staging test add-on: private guide'); await p.fill('#a0-price', '1500'); await p.selectOption('#a0-unit', 'per day');
    await p.selectOption('#a0-av', 'available'); await p.selectOption('#a0-st', 'active');
    await save();
    t(/Add-ons saved/.test(await flash()) && /₹1,500 per day/.test(await text('.cms-quote')), 'add-on saved and shown in the customer view');

    /* ---- Offer Zone: separate Offer Code ---- */
    await go('/offers/new?package=' + pk);
    await p.fill('#f-name', 'Staging test offer'); await p.selectOption('#f-offer_type', 'percentage'); await p.fill('#f-discount_value', '5');
    await p.fill('#f-valid_from', d(0)); await p.fill('#f-valid_until', d(20));
    await p.fill('#f-terms', 'Staging test offer. Not a real promotion; used only to verify the Offer Zone.');
    await p.selectOption('#f-status', 'published');
    await Promise.all([p.waitForNavigation(), p.click('button:has-text("Save offer")')]);
    t(/Offer created: OF-0001/.test(await flash()), 'offer created with Offer Code OF-0001 (own sequence)');
    await go(`/packages/${pk}?tab=offers`);
    t(/OF-0001/.test(await text('.cms-idsplit')) && /Pending/.test(await text('.cms-idsplit')), 'Offer Code shown separately from the Package ID');

    /* ---- SEO & AEO ---- */
    await go(`/packages/${pk}?tab=seo`);
    await p.fill('#f-meta_title', 'Staging Test Package Kashmir 4 Days | Holiday Guru');
    await p.fill('#f-meta_description', 'Staging test package for the Holiday Guru Travel CMS: four days in Srinagar, Gulmarg and Pahalgam. Used only to verify the workflow.');
    await p.fill('#f-aeo_question', 'How many days does this staging test package take?');
    await p.fill('#f-aeo_answer', 'This staging test package takes four days and three nights, covering Srinagar, Gulmarg and Pahalgam. It exists only to test the CMS.');
    await save('button[name=op][value=add_faq]'); await save('button[name=op][value=add_faq]');
    await p.fill('#fq0', 'Is this a real package?'); await p.fill('#fa0', 'No. It is a staging test record.');
    await p.fill('#fq1', 'Can it be booked?'); await p.fill('#fa1', 'No. It is never published to the live website.');
    await save();
    t(/SEO & AEO saved/.test(await flash()), 'SEO, AEO and FAQs saved');

    /* ---- Checklist complete; publish needs review + approval ---- */
    await go(`/packages/${pk}`);
    t(/All mandatory items complete/.test(await text('#checklist')), 'publication checklist complete');
    t(await p.locator('.cms-pagehead button:has-text("Publish Package")').isDisabled(), 'Publish is disabled before review and approval');
    await Promise.all([p.waitForNavigation(), p.click('.cms-editor__rail button:has-text("Submit for review")')]);
    t(/In review/.test(await text('.cms-pagehead')), 'submitted for review');

    // Role checks + sign-offs by the right roles
    await login('content.manager@staging.invalid');
    await go(`/packages/${pk}`);
    t(await p.locator('button:has-text("Publish")').count() === 0, 'Content Manager has no Publish button');
    await Promise.all([p.waitForNavigation(), p.click('form.cms-signoff:has(input[value=content]) button[value=approved]')]);
    await login('seo.manager@staging.invalid');
    await go(`/packages/${pk}`);
    await Promise.all([p.waitForNavigation(), p.click('form.cms-signoff:has(input[value=seo]) button[value=approved]')]);
    await login('pricing.manager@staging.invalid');
    await go(`/packages/${pk}`);
    await Promise.all([p.waitForNavigation(), p.click('form.cms-signoff:has(input[value=pricing]) button[value=approved]')]);
    await login('reviewer@staging.invalid');
    const r403 = await p.request.get(B + '/enquiries');
    t(r403.status() === 403, 'Reviewer cannot open CRM enquiries (403)');
    await login('admin@staging.invalid');
    await go(`/packages/${pk}`);
    await Promise.all([p.waitForNavigation(), p.click('.cms-flow button:has-text("Approve")')]);
    t(/Approved/.test(await text('.cms-pagehead')), 'Admin approves after three sign-offs');
    await Promise.all([p.waitForNavigation(), p.click('.cms-pagehead button:has-text("Publish Package")')]);
    t(/Published/.test(await text('.cms-pagehead')) && /Published \(staging\)/.test(await flash()), 'package published (staging)');

    /* ---- Preview reflects current data ---- */
    await go(`/packages/${pk}/preview`);
    const pv = await text('main');
    t(/₹12,000 \/ person/.test(pv) && /Price version 2/.test(pv), 'preview shows the current rate and price version');
    t(/Staging test inclusion: houseboat night/.test(pv) && /Staging test exclusion: pony rides/.test(pv) && /Airfare/.test(pv), 'preview shows inclusions and exclusions (incl. standard)');
    t(/Staging test add-on: private guide/.test(pv), 'preview shows add-ons');
    t(/Staging test offer/.test(pv), 'preview shows the live offer');
    t(/Standard package cost excludes airfare, train fare and bus fare/.test(pv), 'preview shows the standard cost statement');
    t(await p.locator('button:has-text("Pay Now")').isDisabled(), 'Pay Now disabled (no gateway) — no simulated payment');
    t(!/Package ID\s+\d{4}/.test(await text('.hg-ithead')), 'no unapproved Package ID is shown in the preview');
    t(await p.locator('.hg-header, .hg-footer, .hg-utility').count() === 0, 'preview contains the package module only (no website header/footer)');
    // Edit after publish → unpublished changes indicator
    await go(`/packages/${pk}?tab=inclusions`);
    await p.locator('.cms-scopelist').first().locator('input[name$="[name]"]').first().fill('Staging test inclusion: houseboat night (updated)');
    await save();
    t(/unpublished changes/.test(await text('.cms-pagehead')), 'editing a published package shows unpublished changes');
    await go(`/packages/${pk}/preview`);
    t(/\(updated\)/.test(await text('main')), 'preview is never stale (reflects the latest save)');

    /* ---- Search ---- */
    await go('/packages?q=OF-0001');
    t(/Offer Code match/i.test(await text('main')) && /Staging test offer/.test(await text('main')), 'search by Offer Code returns the offer');
    await go('/packages?q=0001');
    t(await p.locator('tbody tr').count() === 1 && /Srinagar Gulmarg Tour/.test(await text('tbody')), 'search by Package ID 0001 returns the (proposed) package');
    await go('/packages?q=Staging%20Test');
    t(await p.locator('tbody tr').count() === 1, 'search by package name');

    /* ---- Enquiry: Tour Package Enquiry with package context ---- */
    await go('/enquiries/new?package=' + pk);
    await p.fill('#f-name', 'Staging Test Traveller'); await p.fill('#f-phone', '0000000000');
    await p.fill('#f-offer_code', 'OF-0001'); await p.fill('#f-adults', '2'); await p.fill('#f-departure_city', 'Delhi');
    await p.check('#f-is_test-1');
    await Promise.all([p.waitForNavigation(), p.click('button:has-text("Log enquiry")')]);
    const eq = await text('main');
    t(/TOUR PACKAGE ENQUIRY/.test(eq) && /Not assigned yet/.test(eq), 'enquiry type Tour Package Enquiry; Package ID "Not assigned yet"');
    t(/₹12,000 \/ person/.test(eq) && /v2/.test(eq) && /OF-0001/.test(eq), 'enquiry carries displayed rate, price version 2 and Offer Code');
    t(/Test record/.test(eq), 'staging test enquiry is labelled as a test record');
    const intake = await p.request.post(B + '/api/enquiries', { headers: { 'X-HG-Intake-Token': 'wrong' }, data: { name: 'x', phone: '1' } });
    t(intake.status() === (cfg.intake_token ? 401 : 503), 'website intake API rejects a wrong token');
    if (cfg.intake_token) {
      const ok = await p.request.post(B + '/api/enquiries', { headers: { 'X-HG-Intake-Token': cfg.intake_token }, data: { name: 'Staging Intake Test', phone: '0000000000', package_slug: 'staging-test-package-kashmir-3n-4d', offer_code: 'OF-0001', displayed_rate: '₹1', package_id: '9999', is_test: true } });
      const j = await ok.json();
      t(ok.status() === 201 && j.price_version === 2 && j.offer_code === 'OF-0001' && j.package_id === null, 'intake derives rate/offer on the server and ignores spoofed Package ID and rate');
    }
    const api = await (await p.request.get(B + `/api/packages/pk-${pk}`)).json();
    t(api.package_id === null && api.rate.price_version === 2 && api.offers[0].offer_code === 'OF-0001' && api.cta.pay_now === false, 'package API: Package ID null until approved, current price version, separate offer');

    /* ---- Version restore, archive → recycle bin → restore ---- */
    await login('super.admin@staging.invalid');
    await go(`/packages/${pk}?tab=history`);
    const nVers = await p.locator('tbody tr').count();
    await go(`/packages/${pk}/versions/2`);
    await Promise.all([p.waitForNavigation(), p.click('button:has-text("Restore this version")')]);
    t(/Content of version 2 restored as version/.test(await flash()), 'an earlier version is restored as a new version');
    t(await p.locator('.cms-table tbody tr').count() === nVers + 1, 'restore adds a version; history is kept');
    await go(`/packages/${pk}?tab=pricing`);
    t(/₹12,000/.test(await text('.cms-ratebox')), 'restore does not roll back price versions');
    await go(`/packages/${pk}`);
    await p.click('.cms-more summary');
    await Promise.all([p.waitForNavigation(), p.click('.cms-more__menu button:has-text("Archive")')]);
    await go('/recycle-bin');
    t(/Staging Test Package Kashmir/.test(await text('main')) && /Kept for audit/.test(await text('main')), 'archived package in the Recycle Bin; published package cannot be permanently deleted');
    await Promise.all([p.waitForNavigation(), p.click('button:has-text("Restore")')]);
    t(/Restored as a draft/.test(await flash()), 'Recycle Bin restore returns the package to draft');

    /* ---- Activity log ---- */
    await go('/activity');
    const act = await text('main');
    t(['Price changed', 'Itinerary updated', 'Image uploaded', 'Offer added', 'Package published', 'Version restored', 'Package archived'].every(a => act.includes(a)), 'activity log records price, itinerary, image, offer, publish, restore and archive');

    /* ---- Header/footer isolation: changing the website shell does not change the package editor or preview ---- */
    const site = cfg.site_root;
    const files = ['include/global/header.php', 'include/global/footer.php', 'include/global/utility-bar.php', 'assets/css/hg-site.css'];
    const snap = async () => {
      const out = {};
      for (const u of [`/packages/${pk}`, `/packages/${pk}?tab=itinerary`, `/packages/${pk}?tab=pricing`, `/packages/${pk}/preview`]) {
        await go(u);
        out[u] = crypto.createHash('sha256').update(await p.evaluate(() => { const m = document.querySelector('main'); return m.innerHTML + [...m.querySelectorAll('*')].map(e => { const c = getComputedStyle(e); return c.color + c.backgroundColor + c.fontSize + c.width; }).join(); })).digest('hex');
      }
      return out;
    };
    const before = await snap();
    const orig = files.map(f => fs.readFileSync(path.join(site, f), 'utf8'));
    try {
      fs.appendFileSync(path.join(site, files[0]), '\n<!-- isolation test marker -->\n');
      fs.appendFileSync(path.join(site, files[1]), '\n<!-- isolation test marker -->\n');
      fs.appendFileSync(path.join(site, files[2]), '\n<!-- isolation test marker -->\n');
      fs.appendFileSync(path.join(site, files[3]), '\n.hg-footer{background-color:rgb(1,2,3)!important}.hg-header{background-color:rgb(1,2,3)!important}.hg-utility{color:rgb(1,2,3)!important}\n');
      const after = await snap();
      t(JSON.stringify(before) === JSON.stringify(after), 'header/footer/utility-bar change on the website leaves the package editor and preview unchanged');
    } finally { files.forEach((f, i) => fs.writeFileSync(path.join(site, f), orig[i])); }

    t(errors.length === 0, 'no browser console errors' + (errors.length ? ': ' + errors.slice(0, 3).join(' / ') : ''));
  } catch (e) { fail++; console.log('FAIL  exception: ' + e.message.split('\n')[0]); await p.screenshot({ path: TMP + '/failure.png' }); console.log('      screenshot ' + TMP + '/failure.png at ' + p.url()); }
  await b.close();
  console.log(`\n${pass} passed, ${fail} failed`); process.exit(fail ? 1 : 0);
})();
