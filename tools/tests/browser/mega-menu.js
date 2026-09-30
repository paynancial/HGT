// Mega menu test (staging): six menus open/close (click, hover, Escape), region tabs (hover + keyboard),
// every menu link resolves, no counts / invented dates, mobile drawer, axe WCAG 2.2 A/AA with each menu open.
// Env: HG_BASE_URL, PLAYWRIGHT_MODULE, AXE_PATH (axe.min.js).
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('fs');
const B = process.env.HG_BASE_URL || 'http://localhost:8098';
const AXE = process.env.AXE_PATH ? fs.readFileSync(process.env.AXE_PATH, 'utf8') : null;
const IDS = ['india', 'intl', 'inbound', 'spec', 'fixed', 'about'];
let fail = 0; const rec = (ok, m) => { if (!ok) fail++; console.log((ok ? 'PASS  ' : 'FAIL  ') + m); };
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1440, height: 900 } });
  const errs = []; p.on('pageerror', e => errs.push(e.message));
  await p.goto(B + '/about', { waitUntil: 'networkidle' });
  const links = new Set();
  for (const id of IDS) {
    const trig = p.locator(`[aria-controls="mega-${id}"]`);
    await trig.click();
    const open = await p.locator(`#mega-${id}`).isVisible();
    rec(open && await trig.getAttribute('aria-expanded') === 'true', `${id}: opens on click (aria-expanded)`);
    const txt = await p.locator(`#mega-${id}`).innerText();
    rec(!/\b\d+\s+(tours?|packages?|trips?)\b/i.test(txt), `${id}: no package/tour counts`);
    if (id === 'fixed') rec(!/\b\d{1,2}\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\b|\b20\d\d-\d\d-\d\d\b/.test(txt), 'fixed: no invented departure dates');
    for (const h of await p.$$eval(`#mega-${id} a[href]`, a => a.map(x => x.getAttribute('href')))) links.add(h);
    if (AXE) {
      await p.addScriptTag({ content: AXE });
      const v = await p.evaluate(async id => (await axe.run(document.getElementById('mega-' + id), { runOnly: ['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa'] })).violations.map(x => x.id + '(' + x.nodes.length + ')'), id);
      rec(!v.length, `${id}: axe clean with menu open ${v.join(' ')}`);
    }
    await p.keyboard.press('Escape');
    rec(!(await p.locator(`#mega-${id}`).isVisible()) && await p.evaluate(id => document.activeElement.getAttribute('aria-controls') === 'mega-' + id, id), `${id}: Escape closes and returns focus`);
  }
  // Hover opens; region tabs switch on hover and with arrow keys.
  await p.hover('[aria-controls="mega-india"]'); await p.waitForTimeout(300);
  rec(await p.locator('#mega-india').isVisible(), 'india: opens on hover');
  const tabs = p.locator('#mega-india [data-hg-mega-tab]');
  await tabs.nth(1).hover(); await p.waitForTimeout(250);
  const act = await p.$eval('#mega-india .hg-mega__pane.is-active', e => e.id);
  rec(act === (await tabs.nth(1).getAttribute('aria-controls')) && await tabs.nth(1).getAttribute('aria-selected') === 'true', `india: hovering a region shows it (${act})`);
  await tabs.nth(1).focus(); await p.keyboard.press('ArrowDown');
  rec(await p.evaluate(() => document.activeElement.getAttribute('aria-selected')) === 'true' && await tabs.nth(2).getAttribute('aria-selected') === 'true', 'india: ArrowDown moves to the next region');
  const paneLinks = await p.$$eval('#mega-india .hg-mega__pane.is-active a', a => a.length);
  rec(paneLinks > 3, `india: region pane lists destinations and places (${paneLinks} links)`);
  // Every link resolves (200 after redirects).
  const bad = [];
  for (const h of links) {
    if (!h.startsWith('/') || h.startsWith('//')) continue;   // tel:, mailto:, WhatsApp
    const r = await p.request.get(B + h, { maxRedirects: 5 });
    if (r.status() !== 200) bad.push(h + ' ' + r.status());
  }
  rec(!bad.length, `all ${links.size} menu links resolve ${bad.slice(0, 5).join(', ')}`);
  // Place links land on a filtered destination page with results.
  const place = [...links].find(h => h.includes('place%5B%5D='));
  const pr = await p.request.get(B + place); const html = await pr.text();
  rec(/class="hg-rcard"/.test(html), `place link shows matching tours (${place})`);
  // Mobile drawer.
  const m = await b.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await m.goto(B + '/about', { waitUntil: 'networkidle' });
  await m.click('[data-hg-menu-open], .hg-header [aria-controls="hg-nav"]').catch(() => {});
  await m.waitForTimeout(400);
  await m.click('[aria-controls="mega-india"]'); await m.waitForTimeout(300);
  const heads = await m.$$eval('#mega-india .hg-mega__panehead', e => e.filter(x => x.offsetParent).map(x => x.textContent));
  rec(heads.length >= 3, `mobile: Domestic shows region headings (${heads.join(', ')})`);
  const small = await m.$$eval('#mega-india a', a => a.filter(x => x.offsetParent && x.getBoundingClientRect().height < 44).length);
  rec(small === 0, 'mobile: every visible menu link is at least 44 px tall');
  rec(await m.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), 'mobile: no horizontal overflow');
  rec(errs.length === 0, 'no JS errors');
  await b.close(); console.log(fail ? `\n${fail} FAILED` : '\nall passed'); process.exit(fail ? 1 : 0);
})();
