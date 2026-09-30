#!/usr/bin/env python3
"""ONE-OFF migration (already run): split hg-site.css + hg-ui.css into component/page source files.

Each top-level rule (and each rule inside @media) is classified by the component classes in its
selector and written, in original order, to assets/css/src/<layer>/<file>.css. The bundles are then
rebuilt by tools/build_assets.py. Kept in the repo as a record of how the split was made.
Usage: python3 tools/css_split_migration.py   (reads the current bundles; refuses if src/ exists)
"""
import os, re, sys, collections

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
CSS = os.path.join(ROOT, 'public_html', 'assets', 'css')
SRC = os.path.join(CSS, 'src')

# (file, [selector patterns]) — first match wins; order matters (specific before generic)
MAP = [
    ('tokens', [r'^:root\b']),
    ('components/utility-bar', [r'\.hg-utility']),
    ('components/holiday-search', [r'\.hg-hsearch', r'\.hg-ac\b', r'\.hg-ac__', r'#hg-header-search']),
    ('components/mega-menu', [r'\.hg-mega']),
    ('components/navigation', [r'\.hg-nav\b', r'\.hg-nav_', r'\.hg-nav__', r'hg-menu-open', r'\.hg-soon']),
    ('components/header', [r'\.hg-header', r'\.hg-hcontact', r'\.hg-skip', r'\.hg-iconbtn', r'hg-sheet-open']),
    ('components/footer', [r'\.hg-footer', r'\.hg-fnav', r'\.hg-qlink', r'^\.hg-body \.container']),
    ('components/support-widget', [r'\.hg-support', r'\.scroll-top']),
    ('components/cookie-consent', [r'\.hg-consent']),
    ('components/login-dialog', [r'\.hg-login', r'\.hg-dialog']),
    ('pages/search-results', [r'\.hg-results', r'\.hg-rcard', r'\.hg-rlist', r'\.hg-filter', r'\.hg-facet', r'\.hg-activechips', r'\.hg-mbar',
                              r'\.hg-pager', r'\.hg-dhero', r'\.hg-rail', r'\.hg-sortform', r'\.hg-routelist', r'\.hg-whylist', r'\.hg-linklist',
                              r'\.hg-save', r'\.hg-sheet', r'\.hg-bottombar', r'\.hg-count', r'\.hg-places?\b', r'\.hg-answer']),
    ('pages/itinerary', [r'\.hg-timeline', r'\.hg-ithead', r'\.hg-routebox']),
    ('pages/tour-detail', [r'\.hg-pkg', r'\.hg-bookcard', r'\.hg-pricebox', r'\.hg-incl', r'\.hg-travelnote', r'\.hg-important', r'\.hg-custom\b',
                           r'\.hg-trustlist', r'\.hg-hl\b', r'\.hg-secnav', r'\.hg-qf']),
    ('pages/homepage', [r'\.hg-hero', r'\.hg-searchwidget', r'\.hg-stat', r'\.hg-speciality', r'\.hg-feature', r'\.hg-tags']),
    ('pages/contact', [r'\.hg-contact', r'\.hg-map']),
    ('pages/travel-guide', [r'\.hg-article', r'\.hg-guide', r'\.hg-toc']),
    ('pages/people', [r'\.hg-people', r'\.hg-person']),
    ('pages/policy', [r'\.hg-policy', r'\.hg-sitemap']),
    ('pages/customized-holidays', [r'\.hg-form--card', r'\.hg-custom-']),
    ('components/content', [r'\.hg-(card|pcard|dcard|grid|section|faq|cta|ctaband|steps|summary|prose|tip|notice|devnote|empty|layout|sidecard|form|field|'
                            r'placeholder|img|media|narrow|eyebrow|lead|h[1-4]|muted|link-arrow|checks|pagehead|crumbs|table|tablewrap|optional|review|'
                            r'ph|band|block|facts|price-note|chips|region|helpcard|daylist|btn--wa|btn\[disabled\])\b']),
    ('global', [r'.']),
]
BUNDLE_OF = lambda f: 'hg-site' if f in SITE_FILES else 'hg-ui'
SITE_FILES = set()  # filled from the file each rule came from (support, consent and site footer rules stay in hg-site.css)


def parse(css):
    """Top-level items: ('comment', text) | ('rule', selector, body) | ('at', prelude, [items]) | ('atraw', text)."""
    i, n, out = 0, len(css), []
    while i < n:
        if css[i].isspace():
            i += 1; continue
        if css.startswith('/*', i):
            j = css.index('*/', i) + 2; out.append(('comment', css[i:j])); i = j; continue
        j = i; depth = 0
        while j < n and css[j] not in '{;':
            if css.startswith('/*', j): j = css.index('*/', j) + 2; continue
            j += 1
        if j < n and css[j] == ';':
            out.append(('atraw', css[i:j + 1].strip())); i = j + 1; continue
        prelude = css[i:j].strip(); k = j; depth = 0
        while True:
            if css.startswith('/*', k): k = css.index('*/', k) + 2; continue
            c = css[k]
            if c == '{': depth += 1
            elif c == '}':
                depth -= 1
                if depth == 0: break
            k += 1
        body = css[j + 1:k]
        if prelude.startswith('@media') or prelude.startswith('@supports'):
            out.append(('at', prelude, parse(body)))
        else:
            out.append(('rule', prelude, body))
        i = k + 1
    return out


def classify(sel):
    s = re.sub(r'/\*.*?\*/', '', sel, flags=re.S)
    for f, pats in MAP:
        if any(re.search(p, s) for p in pats):
            return f
    return 'global'


def split_selectors(sel):
    """Split a selector list at top-level commas (not inside :is()/:not() parentheses)."""
    parts, depth, cur = [], 0, ''
    for ch in re.sub(r'/\*.*?\*/', '', sel, flags=re.S):
        if ch in '([': depth += 1
        elif ch in ')]': depth -= 1
        if ch == ',' and depth == 0:
            parts.append(cur.strip()); cur = ''
        else:
            cur += ch
    parts.append(cur.strip())
    return [x for x in parts if x]


def by_component(sel):
    """Group the selectors of one rule by component, keeping their order: [(file, 'sel, sel'), ...]."""
    groups = collections.OrderedDict()
    for part in split_selectors(sel):
        groups.setdefault(classify(part), []).append(part)
    return [(f, ', '.join(v)) for f, v in groups.items()]


def fmt_rule(sel, body, indent=''):
    body = body.strip('\n')
    if '\n' not in body:
        return f'{indent}{sel} {{{body}}}\n'
    return f'{indent}{sel} {{\n{body}\n{indent}}}\n'


def main():
    if os.path.exists(SRC):
        sys.exit('src/ already exists: migration was already run')
    files = collections.OrderedDict()
    order = []
    for bundle in ('hg-site', 'hg-ui'):
        css = open(os.path.join(CSS, bundle + '.css'), encoding='utf-8').read()
        pending_comment = []
        for item in parse(css):
            if item[0] == 'comment':
                pending_comment.append(item[1]); continue
            if item[0] == 'rule':
                for f, sel in (by_component(item[1]) if not item[1].startswith('@') else [('global', item[1])]):
                    key = (bundle, f)
                    files.setdefault(key, [])
                    files[key].extend(c + '\n' for c in pending_comment); pending_comment = []
                    files[key].append(fmt_rule(sel, item[2]))
            elif item[0] == 'at':
                groups = collections.OrderedDict()
                for sub in item[2]:
                    if sub[0] == 'rule':
                        for f, sel in by_component(sub[1]):
                            groups.setdefault(f, []).append(fmt_rule(sel, sub[2], '    '))
                    elif sub[0] == 'comment':
                        groups.setdefault('__c', []).append('    ' + sub[1] + '\n')
                comments = groups.pop('__c', [])
                for f, rules in groups.items():
                    key = (bundle, f); files.setdefault(key, [])
                    files[key].extend(c + '\n' for c in pending_comment); pending_comment = []
                    files[key].append(item[1] + ' {\n' + ''.join(comments if f == next(iter(groups)) else []) + ''.join(rules) + '}\n')
            else:  # @charset, @import …
                key = (bundle, 'global'); files.setdefault(key, []); files[key].append(item[1] + '\n')
    os.makedirs(SRC)
    manifest = {'hg-site': [], 'hg-ui': []}
    for (bundle, f), chunks in files.items():
        name = f if bundle == 'hg-ui' else f + ('' if f.startswith('components/') else '-site')
        path = os.path.join(SRC, name + '.css')
        os.makedirs(os.path.dirname(path), exist_ok=True)
        mode = 'a' if os.path.exists(path) else 'w'
        with open(path, mode, encoding='utf-8') as fh:
            fh.write('\n'.join(c.rstrip('\n') for c in chunks) + '\n')
        manifest[bundle].append(name)
    # Post-steps: one tokens file (site + ui :root blocks define disjoint variables), and the shared
    # .hg-i icon rule (originally first in hg-site.css) in its own component file.
    site_tok, ui_tok = os.path.join(SRC, 'tokens-site.css'), os.path.join(SRC, 'tokens.css')
    merged = open(site_tok, encoding='utf-8').read() + open(ui_tok, encoding='utf-8').read()
    open(ui_tok, 'w', encoding='utf-8').write(merged); os.remove(site_tok)
    gs = os.path.join(SRC, 'global-site.css')
    icon_rules = [l for l in open(gs, encoding='utf-8').read().split('\n') if l.startswith('.hg-i ')]
    open(os.path.join(SRC, 'components', 'icons.css'), 'w', encoding='utf-8').write(
        '/* Shared inline SVG icon sizing (.hg-i). Colours measured from the logo (Logo_HGT.png, 2026-09-29): navy #0A163D, orange #FE7F16. */\n'
        + '\n'.join(icon_rules) + '\n')
    os.remove(gs)
    for b, names in manifest.items():
        print(b, names)


if __name__ == '__main__':
    main()
