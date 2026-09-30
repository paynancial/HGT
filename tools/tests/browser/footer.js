// Footer test (staging): 4 link sections, desktop columns, mobile accordions (tap + keyboard), 44 px targets,
// axe WCAG 2.2 A/AA, no JS errors. Env: HG_BASE_URL, PLAYWRIGHT_MODULE, AXE_PATH, HG_SHOT_DIR.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const AXE = require('fs').readFileSync(process.env.AXE_PATH, 'utf8');
const R = []; const rec = (n, p, d = '') => { R.push(p); console.log(`${p ? 'PASS' : 'FAIL'}  ${n}${d ? '  — ' + d : ''}`); };
const VPS = { 'desktop-1366': { viewport: { width: 1366, height: 900 } }, 'laptop-1024': { viewport: { width: 1024, height: 800 } }, 'tablet-768': { viewport: { width: 768, height: 1000 }, hasTouch: true }, 'mobile-375': { viewport: { width: 375, height: 812 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 } };
(async () => {
  const b = await chromium.launch();
  for (const [vp, opt] of Object.entries(VPS)) {
    const ctx = await b.newContext(opt); await ctx.addCookies([{ name: 'hg_consent', value: '1.a0', url: '' + (process.env.HG_BASE_URL || 'http://127.0.0.1:8098') + '' }]);
    const p = await ctx.newPage(); const errs = []; p.on('pageerror', e => { if (/hg-site/.test(e.stack || '')) errs.push(String(e)); });
    await p.goto('' + (process.env.HG_BASE_URL || 'http://127.0.0.1:8098') + '/about', { waitUntil: 'domcontentloaded' }); await p.waitForTimeout(600);
    const mobile = opt.viewport.width < 992;
    const st = await p.$$eval('[data-hg-fnav]', ds => ds.map(d => d.open));
    rec(`[${vp}] 4 footer sections, initially ${mobile ? 'collapsed' : 'open'}`, st.length === 4 && st.every(o => o === !mobile), JSON.stringify(st));
    const ov = await p.evaluate(() => document.documentElement.scrollWidth - innerWidth);
    rec(`[${vp}] no horizontal overflow`, ov <= 0, `${ov}px`);
    if (!mobile) {
      const tops = await p.$$eval('.hg-fnav', ds => ds.map(d => Math.round(d.getBoundingClientRect().top)));
      const rows = new Set(tops).size;
      rec(`[${vp}] columns laid out in 1 row`, rows === 1, `tops ${tops.join(',')}`);
      await p.click('#footer-tours summary', { force: true }); await p.waitForTimeout(150);
      rec(`[${vp}] clicking a heading on desktop does not collapse it`, await p.$eval('#footer-tours', d => d.open));
    } else {
      await p.click('#footer-support summary'); await p.waitForTimeout(200);
      rec(`[${vp}] tap opens Support`, await p.$eval('#footer-support', d => d.open));
      await p.focus('#footer-destinations summary'); await p.keyboard.press('Enter'); await p.waitForTimeout(150);
      rec(`[${vp}] keyboard Enter opens Destinations`, await p.$eval('#footer-destinations', d => d.open));
      const sh = await p.$eval('#footer-support summary', s => s.getBoundingClientRect().height);
      const rowH = await p.$$eval('#footer-support li > *', els => Math.min(...els.map(e => e.getBoundingClientRect().height)));
      rec(`[${vp}] tap targets: header ${Math.round(sh)}px, rows ${Math.round(rowH)}px (>=44)`, sh >= 44 && rowH >= 44);
      const soonClickable = await p.$$eval('#footer-support .hg-fnav__soon', els => els.some(e => e.closest('a') || e.getAttribute('href')));
      rec(`[${vp}] Coming soon items not clickable`, !soonClickable);
      for (const id of ['tours', 'company']) await p.click(`#footer-${id} summary`);
    }
    await p.$eval('.hg-footer', f => f.scrollIntoView()); await p.waitForTimeout(300);
    await p.addScriptTag({ content: AXE });
    const v = await p.evaluate(async () => (await axe.run({ include: ['.hg-footer .hg-footer__top', '.hg-footer__nav', '.hg-footer__bottom'] }, { runOnly: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice'] })).violations.map(x => `${x.id}(${x.nodes.length})`));
    rec(`[${vp}] axe WCAG A/AA + best practice on new footer`, v.length === 0, v.join(', ') || '0 violations');
    rec(`[${vp}] no JS errors from hg-site.js`, errs.length === 0, errs.join('|'));
    const el = await p.$('.hg-footer .hg-footer__top'); const bb = await p.$('.hg-footer__bottom');
    const top = (await el.boundingBox()).y + await p.evaluate(() => scrollY);
    const bottom = (await bb.boundingBox()).y + (await bb.boundingBox()).height + await p.evaluate(() => scrollY);
    await p.evaluate(() => { document.querySelectorAll('.sticky-wrapper, .th-header, .hg-header').forEach(h => h.style.visibility = 'hidden'); document.getElementById('hg-support').style.display = 'none'; document.querySelector('.scroll-top') && (document.querySelector('.scroll-top').style.display = 'none'); });
    await p.screenshot({ path: `${process.env.HG_SHOT_DIR || '/tmp'}/footer2-${vp}.png`, fullPage: true, clip: { x: 0, y: top - 20, width: opt.viewport.width, height: bottom - top + 20 } });
    await ctx.close();
  }
  await b.close(); console.log(`\n${R.filter(Boolean).length}/${R.length} passed`);
})();
