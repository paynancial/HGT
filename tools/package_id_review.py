#!/usr/bin/env python3
"""Owner review pack for the proposed Package IDs (no assignment is made).

Reads   public_html/include/data/packages.json, package-registry.json, destinations.json
Writes  docs/top-tours/FINAL-PACKAGE-ID-MAPPING.md
        docs/top-tours/PACKAGE-ID-MAPPING-FINAL.csv   (Approval Status = PENDING for every row)

Evidence per pair: itinerary text similarity, inclusion similarity, trip length, start city,
hotel category, meal plan. "Suggested action" is a suggestion only; the owner decides.
"""
import csv, difflib, itertools, json, os, re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA = os.path.join(ROOT, 'public_html', 'include', 'data')
OUT_MD = os.path.join(ROOT, 'docs', 'top-tours', 'FINAL-PACKAGE-ID-MAPPING.md')
OUT_CSV = os.path.join(ROOT, 'docs', 'top-tours', 'PACKAGE-ID-MAPPING-FINAL.csv')

pk = {p['slug']: p for p in json.load(open(os.path.join(DATA, 'packages.json'), encoding='utf-8'))}
reg = json.load(open(os.path.join(DATA, 'package-registry.json'), encoding='utf-8'))['entries']
gname = {g['key']: g['name'] for g in json.load(open(os.path.join(DATA, 'destinations.json'), encoding='utf-8'))['groups']}
num = {e['slug']: e['package_id'] for e in reg}
assert all(e['status'] == 'proposed' for e in reg), 'registry already has approved numbers; this pack is for the first approval only'


def days_of(p):
    m = re.findall(r'\d+', p['duration'])
    return int(m[1]) if len(m) >= 2 else None


def itxt(p):
    return ' '.join(d['title'] + ' ' + d['text'] for d in p['itinerary']).lower()


def sim(a, b):
    return difflib.SequenceMatcher(None, a, b).ratio()


def name(p):
    return (p['title'] or p['name']).strip()


# ---------- Data inconsistencies per package ----------
def data_issues(p):
    s, out = p['slug'], []
    d = days_of(p)
    m = re.search(r'(\d+)n-?(\d+)d', s) or re.search(r'(\d+)nt(\d+)dy', s)
    sd = int(m.group(2)) if m else (int(re.search(r'(\d+)-days?', s).group(1)) if re.search(r'(\d+)-days?', s) else None)
    if sd and d and sd != d:
        out.append(f'URL says {sd} days but the page says {p["duration"]}')
    if p['itinerary'] and d and len(p['itinerary']) != d:
        out.append(f'itinerary lists {len(p["itinerary"])} days but the page says {d} days')
    if 'delhi' in s and p['departure'] != 'Delhi':
        out.append(f'URL says "from Delhi" but the page content is "{name(p)}"')
    if not p['itinerary']:
        out.append('no day-by-day itinerary on the page')
    return out


issues = {s: data_issues(p) for s, p in pk.items()}

# ---------- Overlap pairs: same destination and same places, trip length within 1 day ----------
sig = {}
for p in pk.values():
    sig.setdefault((p['group'], tuple(sorted(p['places']))), []).append(p['slug'])
pairs = set()
for group in sig.values():
    for a, b in itertools.combinations(group, 2):
        if abs((days_of(pk[a]) or 0) - (days_of(pk[b]) or 0)) <= 1:
            pairs.add(tuple(sorted((a, b), key=lambda x: num[x])))
flagged = {s for pr in pairs for s in pr}

# Same or near-same public titles (confusing for customers and staff even when trips differ)
title_groups = {}
for p in pk.values():
    key = re.sub(r'[^a-z]', '', name(p).lower().replace('and', ''))
    title_groups.setdefault(key, []).append(p['slug'])
same_title = {s: [t for t in g if t != s] for g in title_groups.values() if len(g) > 1 for s in g}


def assess(a, b):
    A, B = pk[a], pk[b]
    da, db = days_of(A), days_of(B)
    it = sim(itxt(A), itxt(B)) if A['itinerary'] and B['itinerary'] else None
    inc = sim(' '.join(A['inclusions']).lower(), ' '.join(B['inclusions']).lower())
    diffs = []
    if da != db:
        diffs.append(f'trip length {A["duration"]} vs {B["duration"]}')
    if A['departure'] and B['departure'] and A['departure'] != B['departure']:
        diffs.append(f'start city {A["departure"]} vs {B["departure"]}')
    if A['hotel'] != B['hotel'] and A['hotel'] and B['hotel']:
        diffs.append(f'hotels {A["hotel"]} vs {B["hotel"]}')
    if A['meals'] != B['meals'] and A['meals'] and B['meals']:
        diffs.append(f'meals {A["meals"]} vs {B["meals"]}')
    if it is not None and it < 0.6:
        diffs.append('different day-by-day plans')
    if inc < 0.6:
        diffs.append('different inclusions')
    identical = it is not None and it >= 0.95 and inc >= 0.95 and da == db and len(A['itinerary']) == len(B['itinerary'])
    extra = issues[a] + issues[b]
    if identical and sim(name(A).lower(), name(B).lower()) < 0.8:
        action, why = 'REVIEW', ('Day plans, inclusions and length are the same, but the names suggest a difference (e.g. hotel class) that the pages do not state. '
                                 'Either add the real differences to the pages (then KEEP both) or MERGE them.')
    elif identical:
        action, why = 'MERGE or RETIRE one (REVIEW)', 'Day plans, inclusions and length are the same; two numbers would identify one trip.'
    elif it is None:
        action, why = 'REVIEW', 'One of the two has no day-by-day itinerary, so they cannot be compared.'
    elif da == db and it >= 0.7 and inc >= 0.95:
        action, why = 'REVIEW', 'Same length and inclusions, with very similar day plans; any difference (e.g. hotel class) is not stated on the pages.'
    elif da != db:
        action, why = 'KEEP', 'Different trip length; each can carry its own Package ID'
    elif diffs:
        action, why = 'KEEP', 'Same length but ' + ', '.join(diffs) + '.'
    else:
        action, why = 'REVIEW', 'Same length with similar content.'
    if extra and action == 'KEEP':
        action = 'KEEP (correct page data)'
    return dict(a=a, b=b, it=it, inc=inc, diffs=diffs, identical=identical, action=action, why=why)


assessed = [assess(a, b) for a, b in sorted(pairs, key=lambda x: (num[x[0]], num[x[1]]))]
# Special case found in review: 0054's URL/title contradict its content (copy of the Haridwar 8N/9D page).
for x in assessed:
    if 'char-dham-yatra-from-delhi-11n-12d' in (x['a'], x['b']) and x['identical']:
        x['action'] = 'REVIEW (URL/content mismatch)'
        x['why'] = ('The page at the "from Delhi, 11N/12D" URL carries the Haridwar 8N/9D content word for word. '
                    'Either write the real Delhi 11N/12D itinerary for it (then KEEP), or RETIRE it and redirect to the Haridwar tour.')

RANK = ['MERGE', 'REVIEW', 'KEEP (correct', 'KEEP']


def worst(actions):
    for r in RANK:
        for a in actions:
            if a.startswith(r):
                return a
    return 'KEEP'


row_action, row_overlaps = {}, {}
for x in assessed:
    for s, o in ((x['a'], x['b']), (x['b'], x['a'])):
        row_overlaps.setdefault(s, []).append(o)
        act = x['action']
        if act.startswith('REVIEW (URL') and s != 'char-dham-yatra-from-delhi-11n-12d':
            act = 'REVIEW (see ' + num['char-dham-yatra-from-delhi-11n-12d'] + ')'
        row_action.setdefault(s, []).append(act)


def suggested(s):
    acts = row_action.get(s, [])
    if issues[s] and not acts:
        acts = ['REVIEW (URL/content mismatch)'] if any('from Delhi' in i for i in issues[s]) else ['KEEP (correct page data)']
    if s in same_title and not acts:
        acts = ['KEEP (rename to tell apart)']
    return worst(acts) if acts else 'KEEP'


order = sorted(pk, key=lambda s: num[s])

# ---------- CSV ----------
with open(OUT_CSV, 'w', newline='', encoding='utf-8') as fh:
    w = csv.writer(fh)
    w.writerow(['Package ID', 'Internal ref', 'Package Name', 'Destination', 'Duration', 'URL', 'Status', 'Approval Status', 'Notes'])
    for s in order:
        p = pk[s]
        notes = []
        if s in row_overlaps:
            notes.append('possible overlap with ' + ', '.join(num[o] for o in sorted(row_overlaps[s], key=lambda o: num[o])))
        if s in same_title:
            notes.append('same title as ' + ', '.join(num[o] for o in same_title[s]))
        notes += issues[s]
        notes.append('suggested: ' + suggested(s))
        w.writerow([num[s], 'slug:' + s, name(p), gname.get(p['group'], p['group']), p['duration'], p['url'],
                     'Published (static page)', 'PENDING', ' | '.join(notes)])

# ---------- Markdown ----------
pct = lambda v: '—' if v is None else f'{round(v * 100)}%'
L = []
L.append('# Package ID: final mapping for owner approval')
L.append('')
L.append('**Status: PROPOSED. No number is assigned.**')
L.append('- Nothing is migrated, deployed or published.')
L.append('- The live website shows no Package ID until you approve.')
L.append('- Built at commit `ce5339f` or later.')
L.append('')
L.append(f'**Numbers proposed:** {len(order)} ({num[order[0]]} to {num[order[-1]]}), one per current package.')
L.append('')
L.append('**Needs your decision:**')
L.append(f'- {len(flagged)} rows marked "possible overlap", in {len(assessed)} pairs.')
L.append(f'- {sum(1 for s in pk if issues[s])} rows with page-data problems.')
L.append(f'- {len(same_title)} rows whose title is the same as, or nearly the same as, another tour.')
L.append('')
L.append('**Files:**')
L.append('- Machine-readable sheet: [`PACKAGE-ID-MAPPING-FINAL.csv`](PACKAGE-ID-MAPPING-FINAL.csv). Every row has Approval Status **PENDING**.')
L.append('- Generator: `tools/package_id_review.py`. It re-runs from the package data, so every figure below comes from the pages themselves.')
L.append('')
L.append('## How to decide')
L.append('')
L.append('Write one of these in the **Owner Decision** column of the decision table (section 3). You can also edit the CSV\'s Approval Status.')
L.append('')
L.append('| Decision | Meaning |')
L.append('|---|---|')
L.append('| **APPROVED** | The tour gets its proposed Package ID permanently. |')
L.append('| **MERGED** | The tour is combined into another tour. It gets no active number. Its URL redirects (301) to the kept tour, and the merge is recorded. |')
L.append('| **RETIRED** | The tour is withdrawn. Its URL redirects to its destination page. Its proposed number is reserved and never given to another tour without your approval. |')
L.append('| **PENDING** | Not decided yet. It gets no number. |')
L.append('')
L.append('**Suggested actions** (a suggestion only; you decide):')
L.append('- **KEEP**: materially different.')
L.append('- **KEEP (correct page data)**: keep it, but the page has a wrong duration or URL label; fix the label, the number stays the same.')
L.append('- **KEEP (rename to tell apart)**: identical titles confuse customers and staff.')
L.append('- **REVIEW**: the evidence is not conclusive.')
L.append('- **MERGE or RETIRE one**: the two pages appear to be the same trip.')
L.append('')
L.append('**Evidence used:**')
L.append('- **Itinerary similarity:** word-level text similarity of the day-by-day plans (100% = identical).')
L.append('- **Inclusion similarity:** the same measure for the inclusion lists.')
L.append('- Also: trip length, start city, hotel category and meal plan.')
L.append('')

# Section 1: full table
L.append('## 1. Complete mapping')
L.append('')
L.append('- **Internal ref:** the website has no database key for these pages; the old database was not provided. The interim internal key is `slug:<page URL>`. It becomes the database internal key (`package_pk`) when the CMS database is created. The Package ID stays the same.')
L.append('- **Current Status:** every tour is a published static page with no approved rate (Price on request).')
L.append('')
L.append('| Proposed Package ID | Internal ref | Package Name | Destination | Duration | Current URL | Current Status | Overlap Flag | Recommended Review Action |')
L.append('|---|---|---|---|---|---|---|---|---|')
for s in order:
    p = pk[s]
    flag = ('Yes: ' + ', '.join(num[o] for o in sorted(row_overlaps[s], key=lambda o: num[o]))) if s in row_overlaps else '—'
    L.append(f'| {num[s]} | `slug:{s}` | {name(p)} | {gname.get(p["group"], p["group"])} | {p["duration"]} | {p["url"]} | Published | {flag} | {suggested(s)} |')
L.append('')

# Section 2: overlap review per pair
L.append('## 2. Overlap review, pair by pair')
L.append('')
L.append('**Why rows are flagged "possible overlap":** the two tours are in the same destination, list the same places, and differ in length by one day or less.')
L.append('')
L.append('**For each pair:**')
L.append('- **A:** why it may overlap')
L.append('- **B:** which tour it overlaps with')
L.append('- **C:** whether the two appear identical')
L.append('- **D:** what materially differs')
L.append('- **E:** suggested action')
L.append('')
for i, x in enumerate(assessed, 1):
    A, B = pk[x['a']], pk[x['b']]
    L.append(f'### 2.{i} {num[x["a"]]} {name(A)} ↔ {num[x["b"]]} {name(B)}')
    L.append('')
    L.append(f'- **A. Why flagged:** both are {gname.get(A["group"])} tours covering {", ".join(A["places"]) or "the same places"}.')
    L.append(f'  - Lengths: {A["duration"]} and {B["duration"]}.')
    L.append(f'  - Itinerary similarity {pct(x["it"])}; inclusion similarity {pct(x["inc"])}.')
    L.append(f'- **B. Overlaps with:** {num[x["a"]]} `{A["url"]}` ↔ {num[x["b"]]} `{B["url"]}`')
    L.append(f'- **C. Appear identical?** {"**Yes.** Same day plans, inclusions and length." if x["identical"] else "No."}')
    L.append(f'- **D. Material differences:** {"; ".join(x["diffs"]) if x["diffs"] else "none found in the page data"}.')
    for s in (x['a'], x['b']):
        for iss in issues[s]:
            L.append(f'  - Data note, {num[s]}: {iss}.')
    L.append(f'- **E. Suggested:** **{x["action"]}**. {x["why"]}')
    L.append('')

# Section 2b: other ambiguous records
L.append('## 2b. Other records to check (not flagged as overlaps)')
L.append('')
L.append('**Page-data problems.**')
L.append('- The Package ID does not depend on these fields, so numbering can go ahead.')
L.append('- Correcting them later does not change the number.')
L.append('- Where a problem changes *which trip the page is*, it is marked REVIEW.')
L.append('')
L.append('| Proposed No. | Package | Problem | Suggested |')
L.append('|---|---|---|---|')
for s in order:
    if issues[s]:
        L.append(f'| {num[s]} | {name(pk[s])} (`{pk[s]["url"]}`) | {"; ".join(issues[s])} | {suggested(s)} |')
L.append('')
L.append('**Same or near-same titles.** These are different trips (different lengths or places) with names that are hard to tell apart. Consider adding the length or route to the name. Renaming never changes the Package ID')
L.append('')
L.append('| Titles | Tours |')
L.append('|---|---|')
seen = set()
for s in order:
    if s in same_title and s not in seen:
        grp = [s] + same_title[s]
        seen.update(grp)
        L.append(f'| {name(pk[s])} | ' + '; '.join(f'{num[t]} ({pk[t]["duration"]})' for t in sorted(grp, key=lambda t: num[t])) + ' |')
L.append('')

# Section 3: decision table
L.append('## 3. Owner decision table')
L.append('')
L.append('**Rows listed:** every flagged or ambiguous row.')
L.append('**Owner Decision:** left blank. Write APPROVED, MERGED (into …), RETIRED or PENDING.')
L.append('')
L.append('**Every other row** is suggested **KEEP**. If you approve without changes, it gets its proposed number.')
L.append('')
L.append('| Proposed No. | Package | Possible Overlap | Suggested Action | Owner Decision |')
L.append('|---|---|---|---|---|')
for s in order:
    if s in row_overlaps or issues[s] or s in same_title:
        ov = ', '.join(num[o] for o in sorted(row_overlaps.get(s, []), key=lambda o: num[o])) or '—'
        L.append(f'| {num[s]} | {name(pk[s])} ({pk[s]["duration"]}) | {ov} | {suggested(s)} | |')
L.append('')

# Section 4: rules
L.append('## 4. Rules for merged and retired tours')
L.append('')
L.append('Every merge or retirement is recorded with these fields:')
L.append('')
L.append('| Old package | Old Package ID (if assigned) | Retained package | Final Package ID | Redirect / archive |')
L.append('|---|---|---|---|---|')
L.append('| `slug:…` | — (none assigned yet) | `slug:…` | the retained tour\'s number | 301 from the old URL to the retained tour; old record kept as archived |')
L.append('')
L.append('- **Historical numbers:** a merge never overwrites or reuses a number.')
L.append('- **Proposed numbers of merged or retired rows:**')
L.append('  - Before approval, you choose one of two options.')
L.append('    - (a) Keep the gap: the number stays reserved and unused.')
L.append('    - (b) Close the gap by renumbering the *proposal* before anything is approved.')
L.append('  - After approval, gaps are never closed.')
L.append('- **Retired numbers:** a number is never given to another tour without your approval.')
L.append('')

# Section 5: after approval
L.append('## 5. After approval: migration plan (not run)')
L.append('')
L.append('1. **Freeze.** Your decisions are copied into the CSV (APPROVED / MERGED / RETIRED / PENDING) and committed. That commit is the approval record.')
L.append('2. **Interim registry.** In `package-registry.json`:')
L.append('   - APPROVED rows change from `proposed` to `approved`;')
L.append('   - MERGED and RETIRED rows are recorded with their disposition;')
L.append('   - PENDING rows stay `proposed`, so they get no public number.')
L.append('   - A test fails if any approved number later changes, disappears or is reused.')
L.append('3. **Redirects.** 301 redirects for merged and retired URLs are added to `.htaccess`.')
L.append('4. **Database** (when the CMS is built). A single transaction calls `create_package(…, \'migration\')` in approved order.')
L.append('   - The counter is locked with `SELECT … FOR UPDATE`; it never uses `MAX()+1`.')
L.append('   - Safeguards:')
L.append('     - unique and four-digit constraints;')
L.append('     - an append-only `package_id_registry` as the audit trail;')
L.append('     - `package_audit` rows for each change.')
L.append('   - **Rollback:** run on a copy first. If any check fails, the transaction is rolled back and nothing is kept. After a successful run, the pre-migration database backup is kept until you sign off.')
L.append('5. **Retest** (section 6). Then Package ID goes live with the next approved deployment.')
L.append('')
L.append('## 6. Tests to run after assignment')
L.append('')
L.append('| Check | How it is tested |')
L.append('|---|---|')
for c, t in [
    ('All active tours have a unique Package ID; no duplicates', 'tools/tests/package_registry_test.php (registry integrity); DB unique constraint'),
    ('Retired numbers stay reserved', 'registry_test.php: retire 0001, next package gets 0003'),
    ('Name, URL, price and itinerary changes do not change the Package ID', 'registry_test.php (rename, slug change, new rate version); an extra check will compare the approved snapshot'),
    ('Search by Package ID works', 'tour.js: "0002" and "Package ID 0002" open the tour'),
    ('Enquiry, WhatsApp and itinerary show the Package ID', 'tour.js (live mode once approved)'),
    ('CRM and payment payloads carry the Package ID', 'registry_test.php: enquiry, payment and booking rows'),
    ('Historical records stay traceable', 'registry_test.php: payment keeps name, rate and number after a rename'),
    ('No public inventory counts', 'counts.py page scan'),
]:
    L.append(f'| {c} | {t} |')
L.append('')

# Section 7: gate items not yet built
L.append('## 7. Gate items still open (need your go-ahead; not built at this stage)')
L.append('')
L.append('**Offer packages and offer codes (gate items 11 and 16)**')
L.append('- **Offer packages** (a package sold as an offer) get a Package ID from the **same single sequence** as domestic, international and special packages, so no two packages ever share an ID.')
L.append('- **Offer codes** (a discount or promotion applied to a package) are a different thing. If you use them, they need their own format so they are never confused with a Package ID. Proposed:')
L.append('  - an `offers` table with codes `OF-0001` onwards;')
L.append('  - an `offer_packages` link table, so one package can have several offers;')
L.append('  - `offer_code` stored on enquiry, quotation, payment and booking records, so history keeps the offer that applied.')
L.append('- There are no offers yet, so enquiries do not carry an offer code. Not built until you confirm you want promo codes.')
L.append('')
L.append('**Selected add-ons (gate item 11)**')
L.append('- No add-ons are defined yet.')
L.append('- Proposed: optional add-ons attached to a rate version (`package_rate_items`, kind `addon`), with the selected ones sent in the enquiry.')
L.append('')
L.append('**Where the Package ID shows (owner decision, 30 Sep):** only in the itinerary header on the package page. It is not shown on cards, the title area, breadcrumbs or search suggestions. It is carried in enquiry emails, WhatsApp messages and CRM, quotation and payment records.')
L.append('')
L.append('**Search by Package ID (gate item 10):** verified. The search redirects (302) to the tour\'s own canonical URL, and `/tours?…` search URLs are noindex. No duplicate indexable URLs are created.')
L.append('')

os.makedirs(os.path.dirname(OUT_MD), exist_ok=True)
open(OUT_MD, 'w', encoding='utf-8').write('\n'.join(L) + '\n')
print(f'{len(order)} rows; {len(assessed)} overlap pairs ({len(flagged)} rows); '
      f'{sum(1 for x in assessed if x["identical"])} identical pairs; {sum(1 for s in pk if issues[s])} data-problem rows; '
      f'{len(same_title)} same-title rows -> {os.path.relpath(OUT_MD, ROOT)}, {os.path.relpath(OUT_CSV, ROOT)}')
