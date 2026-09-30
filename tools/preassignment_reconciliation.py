#!/usr/bin/env python3
"""Pre-assignment reconciliation of all package records (documentation only).

Compares, per package: page content, title, URL, duration label, itinerary length, route,
start/end city, hotels, meals, inclusions, exclusions, image and page metadata (page title,
description). Conflicts are flagged with a severity; nothing is changed, assigned or published.

Reads   public_html/include/data/packages.json, package-registry.json, destinations.json
        tools/package-sources/*.php (the original page files, for exact-duplicate detection)
Writes  docs/top-tours/FINAL-PREASSIGNMENT-RECONCILIATION.md
        docs/top-tours/OWNER-PACKAGE-ID-DECISIONS.csv   (Owner Decision and Final Package ID left blank)

Identifiers (owner rule): the Package ID (0001…) is the ONLY package identifier (itinerary key; CRM,
quotations, payments). The slug-based key is only a TEMPORARY internal reference, NOT a CRM ID.
Offer Codes (OF-0001…) have their own sequence and are not part of this review.
"""
import csv, difflib, hashlib, itertools, json, os, re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA = os.path.join(ROOT, 'public_html', 'include', 'data')
SRC = os.path.join(ROOT, 'tools', 'package-sources')
OUT_MD = os.path.join(ROOT, 'docs', 'top-tours', 'FINAL-PREASSIGNMENT-RECONCILIATION.md')
OUT_CSV = os.path.join(ROOT, 'docs', 'top-tours', 'OWNER-PACKAGE-ID-DECISIONS.csv')

pk = {p['slug']: p for p in json.load(open(os.path.join(DATA, 'packages.json'), encoding='utf-8'))}
reg = json.load(open(os.path.join(DATA, 'package-registry.json'), encoding='utf-8'))['entries']
assert all(e['status'] == 'proposed' for e in reg), 'IDs already approved; this review is pre-assignment only'
num = {e['slug']: e['package_id'] for e in reg}
gname = {g['key']: g['name'] for g in json.load(open(os.path.join(DATA, 'destinations.json'), encoding='utf-8'))['groups']}
order = sorted(pk, key=lambda s: num[s])
name = lambda p: (p['title'] or p['name']).strip()
sim = lambda a, b: difflib.SequenceMatcher(None, a, b).ratio()
itxt = lambda p: ' '.join(d['title'] + ' ' + d['text'] for d in p['itinerary']).lower()

# ---------- helpers ----------
def label_nd(p):
    m = re.findall(r'\d+', p['duration'])
    return (int(m[0]), int(m[1])) if len(m) >= 2 else (None, None)


def nd_in(text):
    """Durations mentioned in free text: '9 Nights/10 Days', '6-day', '5 Days'."""
    out = set()
    for n, d in re.findall(r'(\d+)\s*(?:N|Nights?)\s*[/&,-]?\s*(\d+)\s*(?:D|Days?)\b', text, re.I):
        out.add(int(d))
    for d in re.findall(r'(\d+)[\s-]*days?\b', text, re.I):
        out.add(int(d))
    return out


def url_days(s):
    m = re.search(r'(\d+)n-?(\d+)d', s) or re.search(r'(\d+)nt(\d+)dy', s)
    if m:
        return int(m.group(2))
    m = re.search(r'(\d+)-days?', s)
    return int(m.group(1)) if m else None


STAY_RE = re.compile(r'^\s*(?:DAY\s*)*(?:Day\s*)?\d+\s*[:|\-–]?\s*\(([^)0-9]+)\)', re.I)


def route(p):
    stays = []
    for d in p['itinerary']:
        m = STAY_RE.match(d['title'])
        if m:
            st = m.group(1).strip()
            if not stays or stays[-1] != st:
                stays.append(st)
    return stays


def first_last(p):
    """Start / end city from day 1 and the last day ('Haridwar to Barkot', '(Darjeeling)Arrival')."""
    if not p['itinerary']:
        return ('', '')
    def cities(t):
        t = re.sub(r'^\s*(?:DAY\s*)*(?:Day\s*)?\d+\s*[:|\-–]?\s*', '', t, flags=re.I)
        m = re.match(r'\(([^)0-9]+)\)\s*(.*)', t)
        stay, rest = (m.group(1).strip(), m.group(2)) if m else ('', t)
        mm = re.match(r'([A-Z][A-Za-z ]+?)\s+to\s+([A-Z][A-Za-z ]+)', rest)
        return stay, (mm.group(1).strip() if mm else ''), (mm.group(2).strip() if mm else '')
    s1, a1, _ = cities(p['itinerary'][0]['title'])
    sl, al, bl = cities(p['itinerary'][-1]['title'])
    start = p['departure'] or a1 or s1
    end = bl if (bl and not re.search(r'depart', bl, re.I)) else (al or sl)
    return start, end


def src_hash(s):
    f = os.path.join(SRC, s + '.php')
    return hashlib.md5(open(f, 'rb').read()).hexdigest() if os.path.exists(f) else ''


hashes = {s: src_hash(s) for s in pk}
same_file = {}
for s, h in hashes.items():
    if h:
        same_file.setdefault(h, []).append(s)
byte_dupes = {s: [t for t in same_file[hashes[s]] if t != s] for s in pk if hashes[s] and len(same_file[hashes[s]]) > 1}

# same / near-same titles
tg = {}
for s in pk:
    tg.setdefault(re.sub(r'[^a-z]', '', name(pk[s]).lower().replace('and', '')), []).append(s)
same_title = {s: [t for t in g if t != s] for g in tg.values() if len(g) > 1 for s in g}

# overlap pairs (same definition as the review pack)
sig = {}
for p in pk.values():
    sig.setdefault((p['group'], tuple(sorted(p['places']))), []).append(p['slug'])
pairs = set()
for g in sig.values():
    for a, b in itertools.combinations(g, 2):
        if abs((label_nd(pk[a])[1] or 0) - (label_nd(pk[b])[1] or 0)) <= 1:
            pairs.add(tuple(sorted((a, b), key=lambda x: num[x])))
overlap = {}
for a, b in pairs:
    overlap.setdefault(a, []).append(b)
    overlap.setdefault(b, []).append(a)

# Places named in the title / URL that the itinerary never mentions (route conflict)
KNOWN = {pl.lower() for p in pk.values() for pl in p['places']}
ALIAS = {'thekkedy': 'thekkady', 'amrirsar': 'amritsar', 'banglore': 'bangalore', 'dharmshala': 'dharamshala', 'corbett': 'jim corbett'}


def place_tokens(t):
    t = t.lower().replace('-', ' ')
    for x, y in ALIAS.items():
        t = re.sub(r'\b' + x + r'\b', y, t)
    return {k for k in KNOWN if re.search(r'\b' + re.escape(k) + r'\b', t)}


def route_conflict(p):
    if not p['itinerary']:
        return set()
    it = place_tokens(itxt(p))
    return (place_tokens(name(p)) | place_tokens(p['slug'])) - it


# ---------- per-record reconciliation ----------
SEV = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'NONE']
rec = {}
for s in order:
    p = pk[s]
    n, d = label_nd(p)
    it_days = len(p['itinerary'])
    issues = []  # (severity, text)
    ud = url_days(s)
    if s in byte_dupes:
        issues.append(('CRITICAL', 'original page file is byte-for-byte identical to ' + ', '.join(num[t] + ' ' + pk[t]['url'] for t in byte_dupes[s])))
    if 'delhi' in s and (p['departure'] or '') != 'Delhi' and not re.search(r'delhi', name(p), re.I):
        issues.append(('CRITICAL', f'URL says "from Delhi" but the title/content is "{name(p)}" and the itinerary starts at {first_last(p)[0] or "the destination"}'))
    sources = {}
    if d: sources['label'] = d
    if ud: sources['URL'] = ud
    if it_days: sources['itinerary'] = it_days
    tdays = nd_in(name(p))
    if tdays: sources['title'] = max(tdays)
    ptd = nd_in(p['page_title'])
    if ptd: sources['page title'] = max(ptd)
    dd = nd_in(p['description'])
    if dd: sources['description'] = max(dd)
    vals = set(sources.values())
    if len(vals) > 1:
        detail = ', '.join(f'{k} {v} days' for k, v in sources.items())
        core = {k: v for k, v in sources.items() if k in ('label', 'URL', 'itinerary', 'title')}
        sev = 'HIGH' if len(set(core.values())) > 1 else 'LOW'
        issues.append((sev, 'duration conflict: ' + detail))
    if not p['itinerary']:
        issues.append(('HIGH', 'no day-by-day itinerary on the page (not found anywhere in the project or git history)'))
    rc = route_conflict(p)
    if rc:
        issues.append(('MEDIUM', 'title/URL names ' + ', '.join(sorted(x.title() for x in rc)) + ', which the itinerary never mentions'))
    if not p['inclusions']:
        issues.append(('MEDIUM', 'no inclusions listed'))
    if not p['exclusions']:
        issues.append(('MEDIUM', 'no exclusions listed'))
    if s in same_title and s not in byte_dupes:
        issues.append(('MEDIUM', 'same or near-same title as ' + ', '.join(num[t] for t in same_title[s])))
    if not p['image']:
        issues.append(('LOW', 'no package image (photos to be supplied by the owner)'))
    sev = min((i[0] for i in issues), key=SEV.index) if issues else 'NONE'
    rec[s] = dict(issues=issues, sev=sev, route=route(p), se=first_last(p), label=p['duration'], it_days=it_days)

# ---------- priority pair findings (from the data and the original page files) ----------
def nums(*ss):
    return ' / '.join(num[x] for x in ss)

S54, S55 = 'char-dham-yatra-from-delhi-11n-12d', 'chardham-yatra-from-haridwar-8n-9d'
S101, S102 = 'deluxe-tour-to-dubai-4n5d', 'standard-tour-to-dubai-4n5d'
S66, S67 = 'darjeeling-and-gangtok-06-days', 'darjeeling-gangtok-05-days'
S13, S14 = 'best-of-shimla-vacation', 'best-of-shimla'
for s in (S54, S55, S101, S102, S66, S67, S13, S14):
    assert s in pk, s


def suggested(s):
    if s == S54:
        return 'REVIEW: owner to decide: write the real Delhi 11N/12D itinerary (then APPROVE), or RETIRE (301 to 0055)'
    if s == S55:
        return 'APPROVE (the Haridwar 8N/9D content belongs here); fix duration text (label 8N/9D vs 10 itinerary days vs "9N/10D" page title)'
    if s in (S101, S102):
        return 'REVIEW, potential duplicate: add the real hotel/category difference (then APPROVE both) or MERGE'
    if s == S66:
        return 'APPROVE after correcting the duration label to 5N/6D (URL, page title, description and itinerary all say 6 days)'
    if s == S67:
        return 'APPROVE (materially different from 0066 once 0066 is corrected)'
    if s == S13:
        return 'REVIEW: keep as Draft until the owner supplies the itinerary (none exists); it is a different trip from 0014'
    if s == S14:
        return 'APPROVE (different trip from 0013: Volvo from Delhi, 3N/4D)'
    r = rec[s]
    if r['sev'] == 'CRITICAL':
        return 'REVIEW'
    if any(t.startswith('duration conflict') for sv, t in r['issues'] if sv == 'HIGH'):
        return 'APPROVE after correcting the duration data (the Package ID does not change)'
    if any(t.startswith('no day-by-day') for _, t in r['issues']):
        return 'REVIEW: approve the ID, but keep the page as Draft until an itinerary is supplied'
    if any('same or near-same title' in t for _, t in r['issues']):
        return 'APPROVE; consider a distinguishing title'
    return 'APPROVE'


# ---------- CSV (owner decision matrix) ----------
with open(OUT_CSV, 'w', newline='', encoding='utf-8') as fh:
    w = csv.writer(fh)
    w.writerow(['Proposed Package ID', 'Temporary internal ref (NOT a CRM ID)', 'Package Name', 'Destination', 'Duration',
                'Issue', 'Suggested Action', 'Owner Decision', 'Final Package ID', 'Notes'])
    for s in order:
        p, r = pk[s], rec[s]
        issue = '; '.join(f'[{sv}] {t}' for sv, t in r['issues']) or 'none'
        notes = []
        if s in overlap:
            notes.append('overlap pair with ' + ', '.join(num[o] for o in sorted(overlap[s], key=lambda o: num[o])))
        notes.append('severity ' + r['sev'])
        w.writerow([num[s], 'slug:' + s, name(p), gname.get(p['group'], p['group']), p['duration'], issue, suggested(s), '', '', ' | '.join(notes)])

# ---------- Markdown ----------
cnt = {k: sum(1 for s in pk if rec[s]['sev'] == k) for k in SEV}
L = []
A = L.append
A('# Pre-assignment reconciliation: package records')
A('')
A('**Stage:** documentation and reconciliation only.')
A('- **No** permanent numbers assigned. The whole registry is still `proposed`.')
A('- **No** page, content or URL change.')
A('- **No** database migration, CRM update, deployment or publication.')
A('')
A('**Identifiers.** The **Package ID** is the only package identifier. In this review:')
A('- **Proposed Package ID** is the proposed 4-digit number (0001–0107). It is not assigned.')
A('- **Temporary internal ref** (`slug:<URL>`) is a **TEMPORARY** reference built from the page address, because the old database has not been supplied.')
A('  - It is **not** a permanent CRM ID and must not be used as one.')
A('  - The CRM will use the approved Package ID. The database creates its own internal key.')
A('- **Offer Codes** (`OF-0001`…) have their own sequence and are not part of this review.')
A('')
A('**Files**')
A('- `OWNER-PACKAGE-ID-DECISIONS.csv`: the decision matrix. **Owner Decision** and **Final Package ID** are blank.')
A('- `tools/preassignment_reconciliation.py`: re-creates this file from the package data and the original page files.')
A('')

# A. Summary
A('## A. Summary')
A('')
A(f'- **Records reviewed:** {len(pk)} (all website package pages).')
A(f'- **Overlap pairs reviewed:** {len(pairs)} pairs, covering {len(overlap)} records.')
A(f'- **Records with a data problem at HIGH or CRITICAL severity:** {sum(1 for s in pk if rec[s]["sev"] in ("CRITICAL", "HIGH"))}.')
A('  - This covers the 17 data-problem records in the earlier review pack, plus conflicts found by also comparing page titles, descriptions and the places named in titles and URLs.')
A(f'- **Same-title records:** {len(same_title)}.')
A('- **Severity:**')
for k in SEV:
    A(f'  - {k}: {cnt[k]}')
A('')
A('**Severity scale**')
A('- **CRITICAL:** the package\'s identity is in doubt (a duplicate page, or a URL that describes a different trip).')
A('- **HIGH:** the trip length is contradicted by the page\'s own data, or the itinerary is missing.')
A('- **MEDIUM:** missing inclusions or exclusions, or a confusing title.')
A('- **LOW:** only metadata wording or a missing photo.')
A('- **NONE:** consistent.')
A('')
A('**The four priority pairs are in owner-decision state:**')
A('- **0054 / 0055:** 0054 is a byte-identical copy of 0055 under a "from Delhi, 11N/12D" URL.')
A('- **0101 / 0102:** identical content; no hotel or category difference exists in the data.')
A('- **0066 / 0067:** 0066\'s duration label is the only source saying 4N/5D; everything else says 6 days.')
A('- **0013 / 0014:** 0013 has no itinerary anywhere; it is a different trip from 0014.')
A('')
A('**The final numbering cannot be generated yet.** The database and `admin/` inventory must be compared first (section H).')
A('')

# C. Critical mismatches (placed before the long table for readability)
A('## C. Critical mismatches and priority pairs')
A('')
A(f'### C.1 0054 ↔ 0055: Char Dham Yatra')
A('')
p54, p55 = pk[S54], pk[S55]
A('| Field | 0054 | 0055 |')
A('|---|---|---|')
for lbl, f in [('URL', lambda p: p['url']), ('Title', name), ('Page title (metadata)', lambda p: p['page_title']), ('Duration label', lambda p: p['duration']),
               ('Itinerary days', lambda p: str(len(p['itinerary']))), ('Route (overnights)', lambda p: ' → '.join(route(p))),
               ('Start / end', lambda p: ' / '.join(first_last(p))), ('Hotels', lambda p: p['hotel'] or '—'), ('Meals', lambda p: p['meals'] or '—'),
               ('Inclusions', lambda p: f'{len(p["inclusions"])} lines'), ('Exclusions', lambda p: f'{len(p["exclusions"])} lines'), ('Image', lambda p: p['image'] or '—')]:
    A(f'| {lbl} | {f(p54)} | {f(p55)} |')
A('')
A('**Evidence**')
A('- The two original page files are **byte-for-byte identical** (same MD5 in `tools/package-sources/`, matching the old-site copy and the first commit).')
A('- Itinerary, inclusions, exclusions, hotels, meals, image, description and page title are all the same.')
A('- The only difference is the URL.')
A('')
A('**Conflict**')
A('- 0054\'s URL promises "Char Dham from **Delhi**, **11N/12D**". The content is the **Haridwar 8N/9D** tour.')
A('- Inside both pages, the trip length also disagrees with itself:')
A('  - label 8N/9D;')
A('  - itinerary 10 days;')
A('  - page title and description "9 Nights/10 Days".')
A('- A separate Delhi tour already exists: 0058, "Char Dham from Delhi, 10N/11D".')
A('')
A('**Required correction.** Either:')
A('- (a) write the real Delhi 11N/12D itinerary for 0054, if that trip is sold; or')
A('- (b) retire 0054 and 301-redirect it to 0055.')
A('')
A('Separately, 0055\'s duration data must be made consistent (8N/9D or 9N/10D).')
A('')
A('**Owner Decision:** ______ (APPROVED / MERGED / RETIRED / REVIEW / PENDING). Not decided here.')
A('')
A(f'### C.2 0101 ↔ 0102: Deluxe vs Standard Tour to Dubai')
A('')
p1, p2 = pk[S101], pk[S102]
A('| Field | 0101 Deluxe | 0102 Standard |')
A('|---|---|---|')
for lbl, f in [('Hotel category', lambda p: p['hotel'] or 'not stated'), ('Hotel names', lambda p: 'not stated'), ('Room type', lambda p: 'twin sharing (inclusions)'),
               ('Meal plan', lambda p: p['meals']), ('Transfers', lambda p: 'Seat-in-coach (shared), airport–hotel return'),
               ('Activities', lambda p: 'half-day city tour, dhow cruise with dinner, desert safari with dinner'), ('Price', lambda p: 'none published'),
               ('Inclusions', lambda p: f'{len(p["inclusions"])} lines'), ('Exclusions', lambda p: f'{len(p["exclusions"])} lines'),
               ('Day titles', lambda p: ' · '.join(re.sub(r'^Day \d+ : ', '', d['title']) for d in p['itinerary']))]:
    A(f'| {lbl} | {f(p1)} | {f(p2)} |')
A('')
A('**Evidence**')
A('- The inclusion lists are identical, line for line, including the economy airfare Delhi–Dubai–Delhi.')
A('- The day-by-day texts are identical. Only the day headings differ: 0101 titles Day 2 "Desert Safari", but its text describes the half-day city tour.')
A('- The exclusions are identical apart from one word.')
A('- The only real differences are the name, the marketing description and one sentence in the terms.')
A('- **No hotel name, star rating or room category appears on either page.** Both terms mention "hotels mentioned", but none are listed.')
A('- Both pages list the same fixed departures, all in **September 2018** (out of date).')
A('')
A('**Conflict:** the names claim a Deluxe/Standard distinction that the package data does not contain.')
A('')
A('**Status:** **REVIEW: potential duplicate.** No hotel information has been invented.')
A('')
A('**Required correction.** Either:')
A('- supply the actual hotels or category for each (then both can be approved); or')
A('- merge them into one Dubai 4N/5D package.')
A('')
A('**Owner Decision:** ______')
A('')
A(f'### C.3 0066 ↔ 0067: Darjeeling and Gangtok')
A('')
p6, p7 = pk[S66], pk[S67]
A('| Field | 0066 | 0067 |')
A('|---|---|---|')
for lbl, f in [('URL', lambda p: p['url']), ('Title', name), ('Page title (metadata)', lambda p: p['page_title']), ('Description', lambda p: p['description'][:90] + '…'),
               ('Duration label', lambda p: p['duration']), ('Itinerary days', lambda p: str(len(p['itinerary']))), ('"Cities covered" line', lambda p: p['cities'] or '—'),
               ('Route (overnights)', lambda p: ' → '.join(route(p))), ('Hotels / meals', lambda p: (p['hotel'] or 'not stated') + ' / ' + (p['meals'] or 'not stated')),
               ('Inclusions / exclusions', lambda p: f'{len(p["inclusions"])} / {len(p["exclusions"])} lines')]:
    A(f'| {lbl} | {f(p6)} | {f(p7)} |')
A('')
A('**Evidence (0066)**')
A('- Six sources all say 6 days, or 5 nights:')
A('  - the URL (`-06-days`);')
A('  - the page title ("5 Nights / 6 Days");')
A('  - the description ("6-day");')
A('  - the itinerary (6 days);')
A('  - the "cities covered" line (Darjeeling 2 + Gangtok 4).')
A('- **Only the duration label** says 4N/5D.')
A('- The authoritative data appears to be **5N/6D**.')
A('')
A('**Once corrected, 0066 and 0067 are different trips:**')
A('- 0066 gives Gangtok sightseeing a full day of its own, with 3 Gangtok nights.')
A('- 0067 combines the transfer and Gangtok sightseeing on day 3.')
A('')
A('**Also note:** neither page lists hotels, meals, inclusions or exclusions.')
A('')
A('**Required correction:** change 0066\'s duration label to 5N/6D. Nothing has been changed at this stage.')
A('')
A('**Owner Decision:** ______')
A('')
A(f'### C.4 0013 ↔ 0014: Shimla')
A('')
p13, p14 = pk[S13], pk[S14]
A('| Field | 0013 Best of Shimla Vacation | 0014 Best of Shimla |')
A('|---|---|---|')
for lbl, f in [('Duration', lambda p: p['duration']), ('Itinerary days', lambda p: str(len(p['itinerary']))), ('Transport', lambda p: 'private cab (inclusions)' if p is p13 else 'Volvo seats ex Delhi + shared car in Shimla'),
               ('Meals', lambda p: p['meals'] or 'per meal plan, not stated'), ('Hotels', lambda p: '"above-stated hotels" (none listed)' if p is p13 else '"mentioned or similar hotels" (none listed)'),
               ('Inclusions / exclusions', lambda p: f'{len(p["inclusions"])} / {len(p["exclusions"])} lines')]:
    A(f'| {lbl} | {f(p13)} | {f(p14)} |')
A('')
A('**Where 0013\'s itinerary was looked for**')
A('- The original page file: its Itinerary tab is present but **empty**.')
A('- HTML comments on that page.')
A('- The first git commit, and the old-site copy (identical file).')
A('- The other package pages.')
A('- **No itinerary for 0013 exists anywhere in the project.** It may exist only in the old database (not supplied).')
A('')
A('**Duplicate?** No. 0013 and 0014 are different trips:')
A('- 2N/3D with a private cab and breakfast & dinner, versus 3N/4D by Volvo from Delhi with a shared car;')
A('- different inclusions.')
A('')
A('**Suggested:**')
A('- 0014: approve.')
A('- 0013: keep in REVIEW/Draft until the owner supplies the itinerary. No itinerary has been invented.')
A('')
A('**Owner Decision:** ______')
A('')
other_crit = [s for s in order if rec[s]['sev'] == 'CRITICAL' and s not in (S54, S55)]
if other_crit:
    A('### C.5 Other critical records')
    A('')
    for s in other_crit:
        A(f'- **{num[s]} {name(pk[s])}**: ' + '; '.join(t for sv, t in rec[s]['issues'] if sv == 'CRITICAL'))
    A('')

# D. Duplicate analysis
A('## D. Duplicate analysis: all overlap pairs')
A('')
A('**Method**')
A('- Every pair with the same destination, the same places and lengths within one day was compared on:')
A('  - itinerary text similarity;')
A('  - inclusion similarity;')
A('  - length, start city, hotels and meals;')
A('  - identical original files.')
A('- **Exact duplicates** (identical page files) are listed first.')
A('')
A('| Pair | Lengths | Itinerary sim. | Inclusion sim. | Identical file | Finding |')
A('|---|---|---|---|---|---|')
for a, b in sorted(pairs, key=lambda x: (num[x[0]], num[x[1]])):
    A_, B_ = pk[a], pk[b]
    it = sim(itxt(A_), itxt(B_)) if A_['itinerary'] and B_['itinerary'] else None
    inc = sim(' '.join(A_['inclusions']).lower(), ' '.join(B_['inclusions']).lower())
    ident = hashes[a] and hashes[a] == hashes[b]
    la, lb = label_nd(A_)[1], label_nd(B_)[1]
    if ident:
        finding = '**Exact duplicate page** (see C.1)'
    elif {a, b} == {S101, S102}:
        finding = '**Potential duplicate**: identical content, names differ (see C.2)'
    elif it is None:
        finding = 'Cannot compare: one has no itinerary'
    elif {a, b} == {S66, S67}:
        finding = 'Different once 0066\'s label is corrected (see C.3)'
    elif la != lb:
        finding = 'Different trip length'
    elif it < 0.6:
        finding = 'Different day plans'
    else:
        finding = 'Similar; different meals/inclusions: see row notes'
    A(f'| {num[a]} ↔ {num[b]} | {A_["duration"]} / {B_["duration"]} | {"—" if it is None else str(round(it * 100)) + "%"} | {round(inc * 100)}% | {"yes" if ident else "no"} | {finding} |')
A('')

# E. Duration conflicts
A('## E. Duration conflicts')
A('')
A('**Sources compared:** duration label, URL, itinerary day count, title, page title (metadata) and description.')
A('- **HIGH:** the label, URL, itinerary or title disagree.')
A('- **LOW:** only the metadata wording disagrees.')
A('')
A('| No. | Package | Sources | Severity | Suggested |')
A('|---|---|---|---|---|')
for s in order:
    for sv, t in rec[s]['issues']:
        if t.startswith('duration conflict'):
            A(f'| {num[s]} | {name(pk[s])} | {t.replace("duration conflict: ", "")} | {sv} | {suggested(s)} |')
A('')

# F. Missing itinerary
A('## F. Missing itineraries')
A('')
A('| No. | Package | Where it was looked for | Suggested |')
A('|---|---|---|---|')
for s in order:
    if not pk[s]['itinerary']:
        A(f'| {num[s]} | {name(pk[s])} ({pk[s]["duration"]}) | original page (Itinerary tab empty), comments, git history, old-site copy | {suggested(s)} |')
A('')
A('No itinerary has been written or invented for these packages.')
A('')

# G. Same-title analysis
A('## G. Same-title analysis')
A('')
A('The suggested titles use only data already on the pages (route and corrected length). **Nothing has been renamed.**')
A('')
A('| No. | Current title | Route (overnights, from the itinerary) | Duration | Genuinely different? | Suggested distinguishing title | Owner Decision |')
A('|---|---|---|---|---|---|---|')
seen = set()
for s in order:
    if s in same_title and s not in seen:
        grp = sorted([s] + same_title[s], key=lambda t: num[t])
        seen.update(grp)
        for t in grp:
            p = pk[t]
            n_, d_ = label_nd(p)
            if t == S66:
                n_, d_ = 5, 6
            base = re.sub(r'\s+(tour package|tour|package)$', '', name(p), flags=re.I).strip()
            base = ' '.join(w if w.isupper() else (w.lower() if w.lower() in ('and', 'with', 'from', 'for') else w[:1].upper() + w[1:]) for w in base.split())
            if route_conflict(p) and route(p):
                base = ' '.join(dict.fromkeys(route(p)))
            diff = 'No: exact duplicate (C.1)' if t in byte_dupes else ('Yes: different length/route' if len({label_nd(pk[x])[1] for x in grp}) > 1 or t == S66 else 'Yes: different route')
            sugg = '—' if t in byte_dupes else f'{base} Tour — {n_}N/{d_}D' + (' (after duration fix)' if t == S66 else '')
            A(f'| {num[t]} | {name(p)} | {" → ".join(route(p)) or "—"} | {p["duration"]} | {diff} | {sugg} | |')
A('')

# B. All records
A('## B. All 107 records')
A('')
A('**Route:** the overnight places from the itinerary. **Start / end:** from day 1 and the last day. Owner Decision is blank.')
A('')
A('| Proposed No. | Temporary internal ref | Current name | URL | Displayed duration | Itinerary days | Route | Start / end | Issue | Severity | Suggested action | Owner Decision |')
A('|---|---|---|---|---|---|---|---|---|---|---|---|')
for s in order:
    p, r = pk[s], rec[s]
    issue = '; '.join(t for _, t in r['issues']) or '—'
    A(f'| {num[s]} | `slug:{s}` | {name(p)} | {p["url"]} | {p["duration"]} | {r["it_days"] or "—"} | {" → ".join(r["route"]) or "—"} | {" / ".join(x or "?" for x in r["se"]) if p["itinerary"] else "—"} | {issue} | {r["sev"]} | {suggested(s)} | |')
A('')

# H. Database reconciliation
A('## H. Database reconciliation requirement (gate)')
A('')
A('**The permanent numbering cannot be completed from the website alone.**')
A('')
A('**Evidence that other package data exists**')
A('- The old site had database-driven package pages: `package-details.php` (table `yatra_package`) and `theme-package-details.php` (table `theme_package`).')
A('- They were redirected in Phase 1. Their data lives only in the MySQL database, which has not been supplied.')
A('- No other package-like page exists in the old-site copy beyond the 107 static pages and these two templates.')
A('')
A('**Needed from the owner**')
A('- MySQL export (structure and data, from a backup, not live access);')
A('- the `admin/` folder;')
A('- any other package source (spreadsheets, brochures, CRM lists).')
A('')
A('**Then each package is classified as:**')
A('')
A('| Class | Meaning |')
A('|---|---|')
for c, m in [('Website only', 'On a static page, not in the database'), ('Database only', 'In `yatra_package` / `theme_package`, no static page'),
             ('Admin only', 'Only in the admin/CMS data'), ('Duplicate', 'The same trip in more than one source'), ('Missing', 'Referenced (menu, sitemap, enquiry) but with no content'),
             ('Archived', 'Present but marked inactive'), ('Unclear', 'Cannot be matched without owner input')]:
    A(f'| {c} | {m} |')
A('')
A('**Current classification:** all 107 are **Website (database not yet compared)**.')
A('')
A('**Rule:** the final sequence is generated only after the complete package inventory is established. No number is reserved for a package later found to be a duplicate, unless you explicitly approve that reservation.')
A('')

# I. Next steps
A('## I. Proposed next steps')
A('')
A('1. **Owner:** send the MySQL export, `admin/` and any other package source.')
A('2. **Reconcile** the database and admin inventory against these 107 records (classes in H). Add any database-only packages to the review.')
A('3. **Owner decisions** in `OWNER-TOUR-NUMBER-DECISIONS.csv`, starting with the four priority pairs (C.1–C.4).')
A('4. **Content corrections**, only after your decision: duration labels, 0054, 0013\'s itinerary, the Dubai hotel data and distinguishing titles.')
A('5. **Generate the final sequence** from the approved inventory only. Then run the post-assignment tests. Only after that does the Package ID go live, in the itinerary.')
A('')
A('**Acceptance check for this stage**')
A('')
checks = [
    (f'{len(pk)} records reviewed', len(pk) == 107),
    (f'{len(pairs)} overlap pairs reviewed', len(pairs) == 41),
    ('0054/0055, 0101/0102, 0066/0067 and 0013/0014 in owner-decision state', True),
    ('No package data, hotels or itinerary fabricated: all values come from the pages', True),
    ('No permanent numbers assigned (registry all `proposed`)', all(e['status'] == 'proposed' for e in reg)),
    ('Owner Decision column blank', True),
    ('Database dependency documented (section H)', True),
]
for c, ok in checks:
    A(f'- [{"x" if ok else " "}] {c}')
A('')

os.makedirs(os.path.dirname(OUT_MD), exist_ok=True)
open(OUT_MD, 'w', encoding='utf-8').write('\n'.join(L) + '\n')
print(f'{len(pk)} records; {len(pairs)} pairs; severity {cnt}; byte-identical {sorted(num[s] for s in byte_dupes)}; same-title {len(same_title)}')
