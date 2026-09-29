#!/usr/bin/env python3
"""
Extract the real package content from the existing static package pages
(public_html/*.php) into public_html/include/data/packages.json.

Only text that already exists on the site is used: titles, durations, cities,
day-by-day itinerary, inclusions, exclusions, terms and booking notes.
Nothing is invented. Re-run whenever a package page changes:

    python3 tools/build_package_data.py
"""
import html
import json
import os
import re
import sys

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'public_html')
OUT = os.path.join(ROOT, 'include', 'data', 'packages.json')

DEST = os.path.join(ROOT, 'include', 'data', 'destinations.json')
SEARCH_OUT = os.path.join(ROOT, 'assets', 'data', 'search-index.json')

# Destination groups come from include/data/destinations.json. Pilgrimage groups
# are matched first because e.g. "amarnath-ji-yatra-with-srinagar" also matches Kashmir.
SOURCES = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'package-sources')
DEST_GROUPS = json.load(open(DEST, encoding='utf-8'))['groups']
GROUPS = [(g['key'], g['match']) for g in sorted(DEST_GROUPS, key=lambda g: g['area'] != 'Pilgrimage')]

# Hub / listing pages, not packages
NOT_PACKAGES = {
    'index', 'index2', 'about', 'contact', 'service', 'destinations', 'packages', 'package-details',
    'theme-package-details', 'themes-packages', 'destination_detail', 'mail', 'mail1', '404',
    'pages-1', 'pages-2', 'pages-3', 'domestic-holidays', 'international-holidays', 'religious-tour',
    'devotional-tours-domestic', 'family-holiday', 'beach-holiday', 'hill-station-holidays',
    'honeymoon-holiday', 'pilgrim-holidays', 'adventure-holiday', 'kashmir-tour', 'leh-ladakh',
    'uttarakhand-tour', 'chardham-yatra', 'amarnath-ji-yatra', 'darjeeling-sikkim', 'delight-full-himachal',
    'exotic-kerala', 'royal-rajasthan', 'amazing-goa', 'dream-dubai', 'sizzling-singapore',
    'thriller-thailand', 'dubai-travel-packages', 'chardham-package', 'ooty-mysore-coorg',
    'customized-holidays', 'faqs', 'india-tours', 'tours',
}


def clean(text):
    text = re.sub(r'<br\s*/?>', '\n', text, flags=re.I)
    text = re.sub(r'<[^>]+>', ' ', text)
    text = html.unescape(text)
    text = re.sub(r'[ \t\r\f\v]+', ' ', text)
    text = re.sub(r'\s*\n\s*', '\n', text)
    return text.strip()


def first(pattern, src, flags=re.S | re.I):
    m = re.search(pattern, src, flags)
    return clean(m.group(1)) if m else ''


def tab_items(src, tab_id):
    m = re.search(r'id="v-pills-%s"[^>]*>(.*?)</ul>' % tab_id, src, re.S | re.I)
    if not m:
        return []
    return [clean(li) for li in re.findall(r'<li[^>]*>(.*?)</li>', m.group(1), re.S) if clean(li)]


def parse(path):
    slug = os.path.basename(path)[:-4]
    src = open(path, encoding='utf-8', errors='replace').read()
    if 'include/footer.php' not in src:
        return None
    title = first(r'breadcumb-title">(.*?)</h1>', src)
    name = first(r'<h2 class="box-title">(.*?)</h2>', src) or title
    duration = first(r'fa-clock"></i>\s*(.*?)</p>', src)
    dm = re.search(r'(\d+)\s*Nights?\s*/\s*(\d+)\s*Days?', duration + ' ' + title + ' ' + slug.replace('-', ' '), re.I)
    nights, days = (int(dm.group(1)), int(dm.group(2))) if dm else (None, None)
    if days is None:
        m = re.search(r'(\d+)[- ]?days?', slug)
        days = int(m.group(1)) if m else None
        nights = days - 1 if days else None
    cities = first(r'Cities Covered\s*(.*?)</p>', src)
    itinerary = []
    for head, body in re.findall(
            r'class="accordion-button[^"]*"[^>]*>(.*?)</button>.*?class="accordion-body[^"]*">(.*?)</div>', src, re.S):
        h, b = clean(head), clean(body)
        if h:
            itinerary.append({'title': h, 'text': b})
    group = next((k for k, rx in GROUPS if re.search(rx, slug)), None)
    # Departure city: only when the slug says so AND day 1 of the itinerary confirms it.
    dep = re.search(r'from-(delhi|haridwar)|with-delhi', slug)
    departure = None
    warnings = []
    if dep:
        claimed = 'Delhi' if 'delhi' in dep.group(0) else 'Haridwar'
        day1 = itinerary[0]['title'] + ' ' + itinerary[0]['text'][:200] if itinerary else ''
        if re.search(claimed, day1, re.I):
            departure = claimed
        else:
            warnings.append(f'URL says "from {claimed}" but day 1 does not start there')
    if days and itinerary and abs(len(itinerary) - days) > 1:
        warnings.append(f'duration says {days} days but the itinerary has {len(itinerary)} days')
    if not itinerary:
        warnings.append('no day-by-day itinerary on the page')
    image = first(r'property="og:image" content="https?://[^/]+/([^"]+)"', src)
    return {
        'slug': slug,
        'url': '/' + slug,
        'title': title,
        'name': name,
        'page_title': first(r'<title>(.*?)</title>', src),
        'description': first(r'<meta name="description" content="([^"]*)"', src),
        'duration': duration or (f'{nights} Nights / {days} Days' if days else ''),
        'nights': nights,
        'days': days,
        'cities': cities,
        'image': image,
        'group': group,
        'departure': departure,
        'pilgrimage': bool(re.search(r'dham|yatra|amarnath|vaishno|katra', slug)),
        'itinerary': itinerary,
        'inclusions': tab_items(src, 'Inclusion'),
        'exclusions': tab_items(src, 'Exclusion'),
        'terms': tab_items(src, 'Cancellation'),
        'booking': tab_items(src, 'Important'),
        'warnings': warnings,
    }


# Places detected in a package's title, "cities covered" line and day titles.
PLACES = ['Srinagar', 'Gulmarg', 'Pahalgam', 'Sonmarg', 'Doodhpathri', 'Yusmarg', 'Katra', 'Jammu', 'Leh', 'Nubra',
          'Pangong', 'Shimla', 'Manali', 'Kullu', 'Manikaran', 'Dalhousie', 'Dharamshala', 'Amritsar', 'Chandigarh',
          'Nainital', 'Mussoorie', 'Jim Corbett', 'Rishikesh', 'Haridwar', 'Ranikhet', 'Kausani', 'Almora', 'Barkot',
          'Uttarkashi', 'Guptkashi', 'Yamunotri', 'Gangotri', 'Kedarnath', 'Badrinath', 'Gangtok', 'Darjeeling', 'Pelling',
          'Kalimpong', 'Lachung', 'Lachen', 'Munnar', 'Thekkady', 'Alleppey', 'Kovalam', 'Kanyakumari', 'Cochin',
          'Trivandrum', 'Ooty', 'Mysore', 'Coorg', 'Kodaikanal', 'Wayanad', 'Bangalore', 'Goa', 'Dubai', 'Abu Dhabi',
          'Singapore', 'Kuala Lumpur', 'Pattaya', 'Bangkok', 'Maldives']
ALIASES = {'Pahalgam': r'pahal?gam|phalgam', 'Sonmarg': r'sona?marg', 'Jim Corbett': r'corbett',
           'Alleppey': r'alleppey|alappuzha', 'Cochin': r'cochin|kochi', 'Trivandrum': r'trivand', 'Wayanad': r'wayan?a?d',
           'Bangalore': r'bang?alore', 'Dharamshala': r'dharam?shala|dharmshala', 'Thekkady': r'thekk?e?a?dy',
           'Amritsar': r'amr?i?r?t?sar'}


def enrich(p):
    head = ' '.join([p['title'], p['name'], p['slug'].replace('-', ' '), p['cities']] + [d['title'] for d in p['itinerary']])
    places = []
    for pl in PLACES:
        rx = ALIASES.get(pl, re.escape(pl))
        if re.search(r'\b(' + rx + r')', head, re.I):
            places.append(pl)
    body = ' '.join([d['title'] + ' ' + d['text'] for d in p['itinerary']] + p['inclusions'])
    incl = ' '.join(p['inclusions'])
    features = []
    if re.search(r'houseboat', body, re.I):
        features.append('Houseboat stay')
    if re.search(r'helicopter|chopper|helipad', body + ' ' + p['slug'], re.I):
        features.append('Helicopter option')
    if re.search(r'volvo', body + ' ' + p['slug'], re.I):
        features.append('Volvo bus travel')
    if p['pilgrimage']:
        features.append('Pilgrimage')
    m = re.search(r'\b(deluxe|standard|luxury|premium|budget|[345][ -]?star)\b[^.]{0,30}(room|hotel|categ)', incl, re.I)
    hotel = m.group(1).title().replace('Star', 'star') if m else ''
    if re.search(r'breakfast', incl, re.I) and re.search(r'dinner', incl, re.I):
        meals = 'Breakfast & dinner'
    elif re.search(r'breakfast', incl, re.I):
        meals = 'Breakfast'
    else:
        meals = ''
    transfers = 'Private cab' if re.search(r'pvt\.? ?cab|private (cab|vehicle|car)|innova|cab', incl, re.I) else ''
    p.update({'places': places, 'features': features, 'hotel': hotel, 'meals': meals, 'transfers': transfers})
    return p


def main():
    pkgs = []
    for f in sorted(os.listdir(ROOT)):
        if not f.endswith('.php') or f[:-4] in NOT_PACKAGES:
            continue
        path = os.path.join(ROOT, f)
        # Pages converted to the Phase 1 template keep their original content in
        # tools/package-sources/ (not web-served); extract from that copy.
        if 'hg_render_package(' in open(path, encoding='utf-8', errors='replace').read():
            path = os.path.join(SOURCES, f)
            if not os.path.exists(path):
                sys.exit('missing source for templated page: ' + f)
        p = parse(path)
        if p:
            pkgs.append(enrich(p))
    os.makedirs(os.path.dirname(OUT), exist_ok=True)
    with open(OUT, 'w', encoding='utf-8') as fh:
        json.dump(pkgs, fh, ensure_ascii=False, indent=1)
    # Lightweight index for the site search / autocomplete (public data only)
    counts = {}
    for p in pkgs:
        counts[p['group']] = counts.get(p['group'], 0) + 1
    index = {
        'groups': [{'key': g['key'], 'name': g['name'], 'region': g['region'], 'area': g['area'],
                    'url': g['hub_url'], 'keywords': g['keywords'], 'count': counts.get(g['key'], 0)}
                   for g in DEST_GROUPS if counts.get(g['key'])],
        'themes': [{'name': 'Pilgrimage Tours', 'url': '/religious-tour',
                    'keywords': 'pilgrimage yatra religious char dham amarnath kedarnath badrinath vaishno devi',
                    'count': sum(1 for p in pkgs if p['pilgrimage'])}],
        'packages': [{'t': p['title'] or p['name'], 'u': p['url'], 'd': p['duration'], 'g': p['group']} for p in pkgs],
        'places': sorted({pl for p in pkgs for pl in p['places']}),
    }
    os.makedirs(os.path.dirname(SEARCH_OUT), exist_ok=True)
    with open(SEARCH_OUT, 'w', encoding='utf-8') as fh:
        json.dump(index, fh, ensure_ascii=False, separators=(',', ':'))
    print(f'search index -> {os.path.relpath(SEARCH_OUT)} ({os.path.getsize(SEARCH_OUT)} bytes)')
    for p in pkgs:
        for w in p['warnings']:
            print(f'WARNING {p["slug"]}: {w}')
    missing = [p['slug'] for p in pkgs if not p['itinerary']]
    ungrouped = [p['slug'] for p in pkgs if not p['group']]
    print(f'{len(pkgs)} packages -> {os.path.relpath(OUT)}')
    print('no itinerary:', missing)
    print('no group:', ungrouped)
    return 0


if __name__ == '__main__':
    sys.exit(main())
