#!/usr/bin/env python3
"""Propose permanent Tour Numbers for the existing packages (owner review before approval).

Reads   public_html/include/data/packages.json, destinations.json, rates.json
Writes  public_html/include/data/tour-registry.json   (entries with status "proposed")
        docs/package-registry/TOUR-NUMBER-MAPPING.csv  (review sheet)

Rules
- Proposed order: destination display order, then trip length, then name
  (the recommendation in docs/package-registry/PROPOSAL.md, section C.4).
- Append-only: an entry that is already 'approved' or 'retired' is never renumbered,
  moved or removed, and its number is never issued again. Re-running only (re)proposes
  numbers for packages that do not have an approved number yet.
- Never MAX()+1 on the live database: this script is for the one-time migration preview.
  In the CMS, numbers come from create_package() in schema-draft.sql (locked counter).
"""
import csv, json, os, re, sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA = os.path.join(ROOT, 'public_html', 'include', 'data')
REG = os.path.join(DATA, 'tour-registry.json')
CSV_OUT = os.path.join(ROOT, 'docs', 'package-registry', 'TOUR-NUMBER-MAPPING.csv')


def load(name, default):
    p = os.path.join(DATA, name)
    return json.load(open(p, encoding='utf-8')) if os.path.exists(p) else default


def main():
    pkgs = load('packages.json', [])
    groups = load('destinations.json', {'groups': []})['groups']
    order = {g['key']: i for i, g in enumerate(groups)}
    gname = {g['key']: g['name'] for g in groups}
    reg = load('tour-registry.json', {'entries': []})
    fixed = [e for e in reg.get('entries', []) if e.get('status') in ('approved', 'retired')]
    used = {e['tour_number'] for e in fixed}
    fixed_slugs = {e['slug'] for e in fixed}

    pending = [p for p in pkgs if p['slug'] not in fixed_slugs]
    pending.sort(key=lambda p: (order.get(p['group'], 999), p['days'] or 99, (p['title'] or p['name']).lower()))

    entries = list(fixed)
    n = 0
    for p in pending:
        n += 1
        while f'{n:04d}' in used:
            n += 1
        if n > 9999:
            sys.exit('Tour numbers exhausted: owner decision required')
        num = f'{n:04d}'
        used.add(num)
        entries.append({'tour_number': num, 'slug': p['slug'], 'package_id': '', 'status': 'proposed'})

    # Integrity checks (also run by tests)
    nums = [e['tour_number'] for e in entries]
    assert len(nums) == len(set(nums)), 'duplicate tour numbers'
    assert all(re.fullmatch(r'(?!0000)\d{4}', x) for x in nums), 'bad tour number'
    assert len({e['slug'] for e in entries}) == len(entries), 'package listed twice'

    entries.sort(key=lambda e: e['tour_number'])
    out = {
        '_about': 'Tour No. registry. Append-only once approved: never renumber, reuse or delete. '
                  'Public pages show only approved/retired numbers. See docs/package-registry/TOUR-NUMBER-MAPPING.md.',
        'numbering_order': 'destination display order, then trip length, then name',
        'entries': entries,
    }
    with open(REG, 'w', encoding='utf-8') as fh:
        json.dump(out, fh, ensure_ascii=False, indent=1)

    # Near-duplicate hints: same destination and same place set, different length.
    by_slug = {p['slug']: p for p in pkgs}
    sig = {}
    for p in pkgs:
        sig.setdefault((p['group'], tuple(sorted(p['places']))), []).append(p['slug'])

    rates = load('rates.json', {'versions': []}).get('versions', [])
    with open(CSV_OUT, 'w', newline='', encoding='utf-8') as fh:
        w = csv.writer(fh)
        w.writerow(['Tour No. (proposed)', 'Package name', 'Existing URL', 'Destination', 'Duration',
                    'Current price', 'Current status', 'Mapping status', 'Review note'])
        for e in entries:
            p = by_slug.get(e['slug'])
            if not p:
                w.writerow([e['tour_number'], '', '/' + e['slug'], '', '', '', 'not in current data', e['status'], 'retired / removed package'])
                continue
            has_rate = any(r.get('slug') == p['slug'] and r.get('rate_status') == 'approved' for r in rates)
            twins = [s for s in sig[(p['group'], tuple(sorted(p['places'])))]
                     if s != p['slug'] and abs((by_slug[s]['days'] or 0) - (p['days'] or 0)) <= 1]
            note = []
            if twins:
                note.append('possible overlap with ' + ', '.join('/' + t for t in twins) + ' (same places, similar length: keep both, merge or retire?)')
            if not p['itinerary']:
                note.append('no day-by-day itinerary on page')
            if p['warnings']:
                note.append('data warnings: ' + '; '.join(p['warnings'])[:160])
            w.writerow([e['tour_number'], p['title'] or p['name'], p['url'], gname.get(p['group'], p['group']),
                        p['duration'], 'approved rate on file' if has_rate else 'Price on request (no approved rate)',
                        'Published (static page)', e['status'], ' | '.join(note)])
    print(f'{len(entries)} entries ({len(fixed)} fixed, {len(entries) - len(fixed)} proposed) -> {os.path.relpath(REG, ROOT)}, {os.path.relpath(CSV_OUT, ROOT)}')


if __name__ == '__main__':
    main()
