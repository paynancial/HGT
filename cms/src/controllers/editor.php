<?php
/**
 * Screens 4–13: the package editor (one URL, ten tabs) and the publishing workflow.
 * Every save writes a package version (full snapshot) and activity-log rows; nothing is overwritten silently.
 */

const HG_TABS = array(
    'basic' => array('Basic Information', 'packages'),
    'itinerary' => array('Itinerary', 'itinerary'),
    'media' => array('Images & Media', 'media'),
    'pricing' => array('Pricing & Availability', 'pricing'),
    'inclusions' => array('Inclusions & Exclusions', 'scope'),
    'addons' => array('Add-ons', 'addons'),
    'offers' => array('Offer Zone', 'offers'),
    'seo' => array('SEO & AEO', 'seo'),
    'advanced' => array('Advanced', 'packages'),
    'history' => array('Version History', 'packages'),
);

function editor_get($pk)
{
    need('packages');
    $p = pkg_load($pk);
    if (!$p) { http_response_code(404); return cms_render('error', array('title' => 'Not found', 'message' => 'Package not found.')); }
    $tab = get('tab', 'basic');
    if (!isset(HG_TABS[$tab])) $tab = 'basic';
    cms_render('editor', array('p' => $p, 'tab' => $tab));
}

/** Can the current user edit this tab? */
function tab_editable($tab, array $p)
{
    if ($p['status'] === 'archived') return false;
    $mod = HG_TABS[$tab][1];
    $need = in_array($tab, array('pricing', 'addons'), true) ? 'request' : 'edit';
    return hg_can(role(), $mod, $need);
}

function arr($v) { return is_array($v) ? $v : array(); }

function editor_save($pk)
{
    $p = pkg_load($pk);
    if (!$p) deny('Package not found.');
    $tab = post('tab');
    if (!isset(HG_TABS[$tab]) || !tab_editable($tab, $p)) deny();
    $fn = 'save_' . $tab;
    try {
        $note = $fn($p);
    } catch (InvalidArgumentException $x) {
        flash($x->getMessage(), 'err');
        $_SESSION['old'] = $_POST;
        redirect('/packages/' . (int) $pk . '?tab=' . $tab);
    }
    if ($note !== false) {
        $v = pkg_snapshot($pk, $tab, is_string($note) ? $note : '');
        // A change after sign-off needs a fresh review.
        if (in_array($p['status'], array('in_review', 'approved'), true) && $tab !== 'history') {
            q("UPDATE packages SET status = 'in_review' WHERE package_pk = ?", array($pk));
        }
        flash((is_string($note) && $note !== '' ? $note . ' · ' : 'Saved · ') . 'version ' . $v);
    }
    $back = post('next_tab');
    redirect('/packages/' . (int) $pk . '?tab=' . (isset(HG_TABS[$back]) ? $back : $tab) . (post('anchor') ? '#' . preg_replace('/[^a-z0-9-]/', '', post('anchor')) : ''));
}

function log_changes($pk, array $old, array $new)
{
    foreach ($new as $k => $v) {
        $o = isset($old[$k]) ? $old[$k] : '';
        if (is_array($o)) $o = je($o);
        if (is_array($v)) $v = je($v);
        if ((string) $o !== (string) $v) cms_log('Package updated', $pk, $k, $o, $v);
    }
}

/* ---------- Basic information ---------- */
function save_basic(array $p)
{
    $dests = hg_destinations();
    $d = post('destination');
    if (!isset($dests[$d])) throw new InvalidArgumentException('Choose a destination.');
    $name = post('name');
    if (mb_strlen($name) < 5) throw new InvalidArgumentException('Enter the package name (at least 5 characters).');
    $days = (int) post('days'); $nights = (int) post('nights');
    if ($days < 1 || $days > 60 || $nights < 0 || $nights > 60) throw new InvalidArgumentException('Days must be 1–60 and nights 0–60.');
    $type = post('package_type');
    if ($type !== '' && !in_array($type, HG_PACKAGE_TYPES, true)) throw new InvalidArgumentException('Choose a package type from the list.');
    $suit = array_values(array_intersect(arr(isset($_POST['suitable_for']) ? $_POST['suitable_for'] : array()), HG_SUITABLE_FOR));
    $short = post('short_description');
    if (mb_strlen($short) > 300) throw new InvalidArgumentException('The short description is longer than 300 characters.');
    $new = array(
        'name' => $name, 'country' => $dests[$d]['country'], 'region' => $dests[$d]['region'], 'destination' => $d,
        'city_route' => post('city_route'), 'package_type' => $type, 'speciality_type' => in_array(post('speciality_type'), HG_SPECIALITY, true) ? post('speciality_type') : '',
        'days' => $days, 'nights' => $nights, 'suitable_for' => $suit, 'short_description' => $short,
        'description_html' => clean_html(post('description_html')), 'highlights' => lines(post('highlights')),
    );
    log_changes($p['package_pk'], $p, $new);
    q('UPDATE packages SET name=?, country=?, region=?, destination=?, city_route=?, package_type=?, speciality_type=?, days=?, nights=?, suitable_for=?, short_description=?, description_html=?, highlights=? WHERE package_pk=?', array(
        $new['name'], $new['country'], $new['region'], $new['destination'], $new['city_route'], $new['package_type'], $new['speciality_type'], $new['days'], $new['nights'],
        je($new['suitable_for']), $new['short_description'], $new['description_html'], je($new['highlights']), $p['package_pk']));
    return 'Basic information saved';
}

/* ---------- Itinerary (linked to the package; carries its Package ID) ---------- */
const HG_DAY_FIELDS = array('title', 'destination', 'route', 'description', 'sightseeing', 'activities', 'meals', 'hotel', 'transport', 'optional_activities', 'notes', 'media_id');

function save_itinerary(array $p)
{
    $days = array();
    foreach (arr(isset($_POST['days']) ? $_POST['days'] : array()) as $d) {
        $row = array();
        foreach (HG_DAY_FIELDS as $f) $row[$f] = isset($d[$f]) && is_string($d[$f]) ? trim($d[$f]) : '';
        $row['media_id'] = ctype_digit($row['media_id']) ? (int) $row['media_id'] : null;
        $days[] = $row;
    }
    $op = post('op');
    $note = 'Itinerary saved';
    if ($op === 'add') { $blank = array_fill_keys(HG_DAY_FIELDS, ''); $blank['media_id'] = null; $days[] = $blank; $note = 'Day ' . count($days) . ' added'; }
    elseif (preg_match('/^dup:(\d+)$/', $op, $m) && isset($days[$m[1]])) { array_splice($days, $m[1] + 1, 0, array($days[$m[1]])); $note = 'Day ' . ($m[1] + 1) . ' duplicated'; }
    elseif (preg_match('/^del:(\d+)$/', $op, $m) && isset($days[$m[1]])) { array_splice($days, $m[1], 1); $note = 'Day ' . ($m[1] + 1) . ' deleted'; }
    foreach ($days as $i => &$d) if ($d['title'] === '') $d['title'] = 'Day ' . ($i + 1);   // untitled days get a neutral label
    unset($d);
    $override = post('duration_override');
    if ($override !== $p['duration_override']) {
        if (!hg_may(role(), 'override_duration')) throw new InvalidArgumentException('Your role cannot override the duration check.');
        cms_log('Duration override', $p['package_pk'], 'duration_override', $p['duration_override'], $override);
        q('UPDATE packages SET duration_override = ? WHERE package_pk = ?', array($override, $p['package_pk']));
    }
    $old = array_map(function ($d) { return $d['title']; }, $p['days_list']);
    $db = cms_db();
    $db->beginTransaction();
    q('DELETE FROM itinerary_days WHERE package_pk = ?', array($p['package_pk']));
    foreach ($days as $i => $d) {
        q('INSERT INTO itinerary_days(package_pk, day_number, title, destination, route, description, sightseeing, activities, meals, hotel, transport, optional_activities, notes, media_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array(
            $p['package_pk'], $i + 1, $d['title'], $d['destination'], $d['route'], $d['description'], $d['sightseeing'], $d['activities'], $d['meals'], $d['hotel'], $d['transport'], $d['optional_activities'], $d['notes'], $d['media_id']));
    }
    $db->commit();
    cms_log('Itinerary updated', $p['package_pk'], 'itinerary', count($old) . ' days', count($days) . ' days');
    return $note;
}

/* ---------- Media (metadata + order; uploads go through package_media_post) ---------- */
function save_media(array $p)
{
    $ids = array_map(function ($m) { return (int) $m['media_id']; }, $p['media']);
    foreach (arr(isset($_POST['media']) ? $_POST['media'] : array()) as $mid => $m) {
        if (!in_array((int) $mid, $ids, true)) continue;
        $alt = trim((string) ($m['alt_text'] ?? ''));
        if (mb_strlen($alt) > 160) throw new InvalidArgumentException('Alt text should describe the image in under 160 characters.');
        $old = q1('SELECT alt_text, caption, title, credit FROM media WHERE media_id = ?', array($mid));
        $new = array('alt_text' => $alt, 'caption' => trim((string) ($m['caption'] ?? '')), 'title' => trim((string) ($m['title'] ?? '')), 'credit' => trim((string) ($m['credit'] ?? '')));
        if ($old != $new) {
            q('UPDATE media SET alt_text=?, caption=?, title=?, credit=? WHERE media_id=?', array($new['alt_text'], $new['caption'], $new['title'], $new['credit'], $mid));
            cms_log('Image details updated', $p['package_pk'], 'media ' . $mid, je($old), je($new));
        }
    }
    foreach (arr(isset($_POST['order']) ? $_POST['order'] : array()) as $i => $pmId) {
        q('UPDATE package_media SET sort_order = ? WHERE pm_id = ? AND package_pk = ?', array((int) $i, (int) $pmId, $p['package_pk']));
    }
    return 'Image details saved';
}

/* ---------- Pricing: every change is a new price version ---------- */
function save_pricing(array $p)
{
    $op = post('op');
    if (preg_match('/^(approve|withdraw):(\d+)$/', $op, $m)) {
        if (!hg_may(role(), 'approve_rate')) throw new InvalidArgumentException('Your role cannot approve or withdraw rates.');
        $r = q1('SELECT * FROM rate_versions WHERE rate_pk = ? AND package_pk = ?', array($m[2], $p['package_pk']));
        if (!$r) throw new InvalidArgumentException('Price version not found.');
        if ($m[1] === 'approve') {
            if ($r['rate_status'] !== 'draft') throw new InvalidArgumentException('Only a draft price version can be approved.');
            q("UPDATE rate_versions SET rate_status = 'approved', approved_at = ?, approved_by = ? WHERE rate_pk = ?", array(now(), uid(), $r['rate_pk']));
            cms_log('Price approved', $p['package_pk'], 'price version ' . $r['version'], 'draft', 'approved');
            return 'Price version ' . $r['version'] . ' approved';
        }
        if ($r['rate_status'] === 'withdrawn') throw new InvalidArgumentException('This price version is already withdrawn.');
        q("UPDATE rate_versions SET rate_status = 'withdrawn' WHERE rate_pk = ?", array($r['rate_pk']));
        cms_log('Price withdrawn', $p['package_pk'], 'price version ' . $r['version'], $r['rate_status'], 'withdrawn');
        return 'Price version ' . $r['version'] . ' withdrawn';
    }
    $price = (int) preg_replace('/[^\d]/', '', post('base_price'));
    $from = post('valid_from'); $until = post('valid_until');
    $unit = post('price_unit');
    if ($price <= 0) throw new InvalidArgumentException('Enter the base price in rupees.');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $until)) throw new InvalidArgumentException('Enter both rate validity dates.');
    if ($until < $from) throw new InvalidArgumentException('“Valid until” must be on or after “Valid from”.');
    if ($until < today()) throw new InvalidArgumentException('This validity has already ended. Enter current dates.');
    if (!in_array($unit, HG_PRICE_UNITS, true)) throw new InvalidArgumentException('Choose a price unit.');
    $reason = post('reason');
    if ($p['rates'] && mb_strlen($reason) < 5) throw new InvalidArgumentException('Give the reason for the rate change.');
    $opt = function ($k) { $v = preg_replace('/[^\d]/', '', (string) post($k)); return $v === '' ? null : (int) $v; };
    $tax = post('tax_percent') === '' ? null : (float) post('tax_percent');
    $approve = post('approve_now') === '1' && hg_may(role(), 'approve_rate');
    $v = (int) qv('SELECT COALESCE(MAX(version), 0) FROM rate_versions WHERE package_pk = ?', array($p['package_pk'])) + 1;
    q('INSERT INTO rate_versions(package_pk, version, base_price, currency, price_unit, valid_from, valid_until, rate_status, adult_price, child_price, single_supplement, extra_bed, seasonal_note, tax_percent, discount, price_notes, reason, created_at, created_by, approved_at, approved_by)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array(
        $p['package_pk'], $v, $price, 'INR', $unit, $from, $until, $approve ? 'approved' : 'draft', $opt('adult_price'), $opt('child_price'), $opt('single_supplement'), $opt('extra_bed'),
        post('seasonal_note'), $tax, $opt('discount'), post('price_notes'), $reason, now(), uid(), $approve ? now() : null, $approve ? uid() : null));
    $prev = $p['rates'] ? $p['rates'][0] : null;
    cms_log('Price changed', $p['package_pk'], 'price version ' . $v, $prev ? rate_label($prev) . ' (v' . $prev['version'] . ', ' . rate_validity($prev) . ')' : '', inr($price) . ' / ' . $unit . ' (' . dmy($from) . ' – ' . dmy($until) . ', ' . ($approve ? 'approved' : 'draft') . ')');
    return 'Price version ' . $v . ($approve ? ' approved' : ' saved as draft (needs approval)');
}

/* ---------- Inclusions & exclusions ---------- */
function save_inclusions(array $p)
{
    $items = array();
    foreach (arr(isset($_POST['items']) ? $_POST['items'] : array()) as $it) {
        $kind = ($it['kind'] ?? '') === 'exclusion' ? 'exclusion' : 'inclusion';
        $items[] = array('item_pk' => (int) ($it['item_pk'] ?? 0), 'kind' => $kind, 'category' => in_array($it['category'] ?? '', HG_SCOPE_CATEGORIES, true) ? $it['category'] : 'Other',
            'name' => trim((string) ($it['name'] ?? '')), 'description' => trim((string) ($it['description'] ?? '')), 'icon' => preg_replace('/[^a-z-]/', '', (string) ($it['icon'] ?? '')),
            'status' => ($it['status'] ?? 'active') === 'hidden' ? 'hidden' : 'active', 'is_standard' => 0);
    }
    $op = post('op');
    if ($op === 'add:inclusion' || $op === 'add:exclusion') $items[] = array('item_pk' => 0, 'kind' => substr($op, 4), 'category' => 'Other', 'name' => '', 'description' => '', 'icon' => '', 'status' => 'active', 'is_standard' => 0, '_new' => 1);
    if (preg_match('/^del:(\d+)$/', $op, $m) && isset($items[$m[1]])) array_splice($items, $m[1], 1);
    // Standard exclusions are fixed rows: they keep their name; they may be hidden only when an active inclusion names them.
    $std = array();
    foreach ($p['scope'] as $s) if ($s['is_standard']) $std[(int) $s['item_pk']] = $s;
    foreach ($items as &$it) if (isset($std[$it['item_pk']])) { $it['is_standard'] = 1; $it['name'] = $std[$it['item_pk']]['name']; $it['kind'] = 'exclusion'; $it['category'] = 'Travel'; }
    unset($it);
    $posted = array_map(function ($i) { return $i['item_pk']; }, $items);
    foreach ($std as $id => $s) if (!in_array($id, $posted, true)) throw new InvalidArgumentException('The standard exclusion “' . $s['name'] . '” cannot be deleted.');
    foreach ($items as $it) {
        if ($it['is_standard'] && $it['status'] === 'hidden') {
            $word = strtolower(explode(' ', $it['name'])[0]);  // airfare / train / bus
            $named = array_filter($items, function ($i) use ($word) { return $i['kind'] === 'inclusion' && $i['status'] === 'active' && stripos($i['name'] . ' ' . $i['description'], $word) !== false; });
            if (!$named) throw new InvalidArgumentException('“' . $it['name'] . '” can be hidden only when an inclusion states it is included.');
        }
        if ($it['name'] === '' && empty($it['_new'])) throw new InvalidArgumentException('Every inclusion and exclusion needs a name (or delete the empty row).');
    }
    $db = cms_db();
    $db->beginTransaction();
    q('DELETE FROM scope_items WHERE package_pk = ?', array($p['package_pk']));
    $ord = array('inclusion' => 0, 'exclusion' => 0);
    foreach ($items as $it) {
        q('INSERT INTO scope_items(' . ($it['item_pk'] ? 'item_pk, ' : '') . 'package_pk, kind, category, name, description, icon, sort_order, status, is_standard) VALUES (' . ($it['item_pk'] ? '?,' : '') . '?,?,?,?,?,?,?,?,?)',
            array_merge($it['item_pk'] ? array($it['item_pk']) : array(), array($p['package_pk'], $it['kind'], $it['category'], $it['name'] === '' ? 'New item' : $it['name'], $it['description'], $it['icon'], $ord[$it['kind']]++, $it['status'], $it['is_standard'])));
    }
    $db->commit();
    $before = count($p['scope']); $after = count($items);
    cms_log($after > $before ? 'Inclusion/exclusion added' : ($after < $before ? 'Inclusion/exclusion removed' : 'Inclusions/exclusions updated'), $p['package_pk'], 'scope', $before . ' items', $after . ' items');
    return 'Inclusions & exclusions saved';
}

/* ---------- Add-ons ---------- */
function save_addons(array $p)
{
    $manage = hg_can(role(), 'addons', 'manage');
    $items = array();
    foreach (arr(isset($_POST['addons']) ? $_POST['addons'] : array()) as $a) {
        $price = preg_replace('/[^\d]/', '', (string) ($a['price'] ?? ''));
        $items[] = array('addon_pk' => (int) ($a['addon_pk'] ?? 0), 'name' => trim((string) ($a['name'] ?? '')), 'description' => trim((string) ($a['description'] ?? '')),
            'price' => $price === '' ? null : (int) $price, 'price_unit' => in_array($a['price_unit'] ?? '', HG_PRICE_UNITS, true) ? $a['price_unit'] : 'per person',
            'tax_percent' => ($a['tax_percent'] ?? '') === '' ? null : (float) $a['tax_percent'], 'required' => !empty($a['required']) ? 1 : 0,
            'availability' => in_array($a['availability'] ?? '', array('available', 'on request', 'unavailable'), true) ? $a['availability'] : 'on request',
            'status' => ($a['status'] ?? '') === 'active' && $manage ? 'active' : (($a['status'] ?? '') === 'active' && (int) ($a['addon_pk'] ?? 0) ? 'active' : 'hidden'));
    }
    $op = post('op');
    if ($op === 'add') $items[] = array('addon_pk' => 0, 'name' => 'New add-on', 'description' => '', 'price' => null, 'price_unit' => 'per person', 'tax_percent' => null, 'required' => 0, 'availability' => 'on request', 'status' => 'hidden');
    if (preg_match('/^del:(\d+)$/', $op, $m) && isset($items[$m[1]])) {
        if (!$manage) throw new InvalidArgumentException('Your role can suggest add-ons but not delete them.');
        array_splice($items, $m[1], 1);
    }
    foreach ($items as $a) {
        if ($a['name'] === '') throw new InvalidArgumentException('Every add-on needs a name.');
        if ($a['status'] === 'active' && $a['availability'] === 'available' && $a['price'] === null) throw new InvalidArgumentException('“' . $a['name'] . '” is available to book, so it needs a price (or set it to “on request”).');
    }
    $db = cms_db();
    $db->beginTransaction();
    q('DELETE FROM addons WHERE package_pk = ?', array($p['package_pk']));
    foreach ($items as $i => $a) {
        q('INSERT INTO addons(' . ($a['addon_pk'] ? 'addon_pk, ' : '') . 'package_pk, name, description, price, price_unit, tax_percent, required, availability, sort_order, status) VALUES (' . ($a['addon_pk'] ? '?,' : '') . '?,?,?,?,?,?,?,?,?,?)',
            array_merge($a['addon_pk'] ? array($a['addon_pk']) : array(), array($p['package_pk'], $a['name'], $a['description'], $a['price'], $a['price_unit'], $a['tax_percent'], $a['required'], $a['availability'], $i, $a['status'])));
    }
    $db->commit();
    cms_log(count($items) > count($p['addons']) ? 'Add-on added' : 'Add-ons updated', $p['package_pk'], 'addons', count($p['addons']) . ' add-ons', count($items) . ' add-ons');
    return 'Add-ons saved';
}

/* ---------- Offer Zone: link existing offers (Offer Codes stay separate from the Package ID) ---------- */
function save_offers(array $p)
{
    if (!hg_can(role(), 'offers', 'manage')) throw new InvalidArgumentException('Your role cannot change offers.');
    $want = array_map('intval', arr(isset($_POST['offer_pk']) ? $_POST['offer_pk'] : array()));
    $have = array_map(function ($o) { return (int) $o['offer_pk']; }, $p['offers']);
    foreach (array_diff($want, $have) as $o) {
        if (!qv('SELECT 1 FROM offers WHERE offer_pk = ?', array($o))) continue;
        q('INSERT OR IGNORE INTO offer_packages(offer_pk, package_pk) VALUES (?,?)', array($o, $p['package_pk']));
        cms_log('Offer added', $p['package_pk'], 'offer', '', qv('SELECT offer_code FROM offers WHERE offer_pk = ?', array($o)));
    }
    foreach (array_diff($have, $want) as $o) {
        q('DELETE FROM offer_packages WHERE offer_pk = ? AND package_pk = ?', array($o, $p['package_pk']));
        cms_log('Offer removed', $p['package_pk'], 'offer', qv('SELECT offer_code FROM offers WHERE offer_pk = ?', array($o)), '');
    }
    return 'Offers saved';
}

/* ---------- SEO & AEO ---------- */
function save_seo(array $p)
{
    $canon = post('canonical');
    if ($canon !== '' && (!preg_match('~^https://[^\s]+$~', $canon) || preg_match('~\.php(\?|$)~', $canon))) throw new InvalidArgumentException('The canonical must be an absolute https:// URL without .php.');
    $slug = slugify(post('slug'));
    if ($slug === '') throw new InvalidArgumentException('Enter the SEO slug.');
    if ($slug !== $p['slug']) {
        if ($p['published_at']) throw new InvalidArgumentException('This package has been published: its URL can only change with a 301 redirect from the old URL. Ask an Admin.');
        if (qv('SELECT 1 FROM packages WHERE slug = ? AND package_pk <> ?', array($slug, $p['package_pk']))) throw new InvalidArgumentException('Another package already uses this slug.');
        q('UPDATE packages SET slug = ?, public_url = ? WHERE package_pk = ?', array($slug, '/' . $slug, $p['package_pk']));
        cms_log('Package updated', $p['package_pk'], 'slug', $p['slug'], $slug);
    }
    $new = array('meta_title' => post('meta_title'), 'meta_description' => post('meta_description'), 'canonical' => $canon, 'og_title' => post('og_title'), 'og_description' => post('og_description'),
        'aeo_question' => post('aeo_question'), 'aeo_answer' => post('aeo_answer'), 'key_facts' => lines(post('key_facts')), 'supporting_questions' => lines(post('supporting_questions')));
    $dup = qv('SELECT p.name FROM package_seo s JOIN packages p ON p.package_pk = s.package_pk WHERE s.meta_title = ? AND s.package_pk <> ? AND s.meta_title <> ""', array($new['meta_title'], $p['package_pk']));
    if ($dup) throw new InvalidArgumentException('Another package (“' . $dup . '”) already uses this meta title. Titles must be unique.');
    $old = $p['seo'];
    log_changes($p['package_pk'], $old, $new);
    q('INSERT OR REPLACE INTO package_seo(package_pk, meta_title, meta_description, canonical, og_title, og_description, og_media_id, aeo_question, aeo_answer, key_facts, supporting_questions) VALUES (?,?,?,?,?,?,?,?,?,?,?)', array(
        $p['package_pk'], $new['meta_title'], $new['meta_description'], $new['canonical'], $new['og_title'], $new['og_description'], ctype_digit((string) post('og_media_id')) ? (int) post('og_media_id') : null,
        $new['aeo_question'], $new['aeo_answer'], je($new['key_facts']), je($new['supporting_questions'])));
    $faqs = array();
    foreach (arr(isset($_POST['faqs']) ? $_POST['faqs'] : array()) as $f) {
        $qq = trim((string) ($f['question'] ?? '')); $aa = trim((string) ($f['answer'] ?? ''));
        if ($qq === '' && $aa === '') continue;
        if ($qq === '' || $aa === '') throw new InvalidArgumentException('Each FAQ needs both a question and an answer.');
        $faqs[] = array($qq, $aa);
    }
    $op = post('op');
    if ($op === 'add_faq') $faqs[] = array('', '');
    if (preg_match('/^del_faq:(\d+)$/', $op, $m) && isset($faqs[$m[1]])) array_splice($faqs, $m[1], 1);
    $qs = array_map(function ($f) { return mb_strtolower($f[0]); }, array_filter($faqs, function ($f) { return $f[0] !== ''; }));
    if (count($qs) !== count(array_unique($qs))) throw new InvalidArgumentException('The same FAQ question appears twice.');
    q('DELETE FROM faqs WHERE package_pk = ?', array($p['package_pk']));
    foreach ($faqs as $i => $f) q('INSERT INTO faqs(package_pk, question, answer, sort_order) VALUES (?,?,?,?)', array($p['package_pk'], $f[0] === '' ? 'New question' : $f[0], $f[1] === '' ? '—' : $f[1], $i));
    return 'SEO & AEO saved';
}

/* ---------- Advanced ---------- */
function save_advanced(array $p)
{
    $new = array('payable' => post('payable') === '1' ? 1 : 0, 'enquiry_enabled' => post('enquiry_enabled') === '1' ? 1 : 0, 'noindex' => post('noindex') === '1' ? 1 : 0);
    log_changes($p['package_pk'], $p, $new);
    q('UPDATE packages SET payable=?, enquiry_enabled=?, noindex=? WHERE package_pk=?', array($new['payable'], $new['enquiry_enabled'], $new['noindex'], $p['package_pk']));
    if (hg_can(role(), 'curation', 'manage')) {
        $rank = post('priority_rank');
        if ($rank !== '' && (!ctype_digit($rank) || (int) $rank < 1 || (int) $rank > 500)) throw new InvalidArgumentException('Priority rank must be 1–500 (or empty).');
        $rank = $rank === '' ? null : (int) $rank;
        $taken = $rank ? qv('SELECT p.name FROM curation c JOIN packages p ON p.package_pk = c.package_pk WHERE c.priority_rank = ? AND c.package_pk <> ?', array($rank, $p['package_pk'])) : null;
        if ($taken) throw new InvalidArgumentException('Priority rank ' . $rank . ' is already used by “' . $taken . '”.');
        $c = array('priority_rank' => $rank);
        foreach (array('featured', 'homepage_featured', 'search_featured', 'seasonal_featured', 'speciality_featured') as $f) $c[$f] = post($f) === '1' ? 1 : 0;
        log_changes($p['package_pk'], $p['curation'], $c);
        q('INSERT OR REPLACE INTO curation(package_pk, priority_rank, featured, homepage_featured, search_featured, seasonal_featured, speciality_featured) VALUES (?,?,?,?,?,?,?)', array(
            $p['package_pk'], $c['priority_rank'], $c['featured'], $c['homepage_featured'], $c['search_featured'], $c['seasonal_featured'], $c['speciality_featured']));
    }
    return 'Advanced settings saved';
}

function save_history(array $p) { return false; }

/* ---------- Workflow ---------- */
function workflow_post($pk)
{
    $p = pkg_load($pk);
    if (!$p) deny('Package not found.');
    $a = post('action');
    $to = '/packages/' . (int) $pk . '?tab=' . (isset(HG_TABS[post('tab')]) ? post('tab') : 'advanced');
    $set = function ($status, $label, $extra = '') use ($p) {
        q('UPDATE packages SET status = ?' . $extra . ' WHERE package_pk = ?', array($status, $p['package_pk']));
        cms_log($label, $p['package_pk'], 'status', $p['status'], $status);
    };
    switch ($a) {
        case 'submit':
            need_action('submit_review');
            if (!in_array($p['status'], array('draft', 'paused'), true)) break;
            $set('in_review', 'Submitted for review');
            flash('Submitted for review: content, SEO/AEO and pricing sign-offs are next.');
            break;
        case 'review':
            $stage = post('stage');
            if (!in_array($stage, array('content', 'seo', 'pricing'), true)) break;
            need_action('review_' . $stage);
            if ($p['status'] !== 'in_review') { flash('Only a package in review can be signed off.', 'err'); break; }
            $dec = post('decision') === 'changes' ? 'changes' : 'approved';
            q('INSERT INTO reviews(package_pk, stage, decision, note, user_id, package_version, at) VALUES (?,?,?,?,?,?,?)', array($pk, $stage, $dec, post('note'), uid(), $p['version'], now()));
            cms_log($dec === 'approved' ? 'Review signed off' : 'Changes requested', $pk, $stage . ' review', '', post('note'));
            if ($dec === 'changes') $set('draft', 'Returned to draft');
            flash($dec === 'approved' ? ucfirst($stage === 'seo' ? 'SEO/AEO' : $stage) . ' review signed off.' : 'Changes requested; the package is back in draft.');
            break;
        case 'approve':
            need_action('approve');
            $so = pkg_signoffs($p);
            $missing = array_keys(array_filter($so, function ($r) { return !$r || $r['decision'] !== 'approved'; }));
            if ($p['status'] !== 'in_review' || $missing) { flash('Approval needs content, SEO/AEO and pricing sign-offs on the current version' . ($missing ? ' (missing: ' . implode(', ', $missing) . ')' : '') . '.', 'err'); break; }
            $set('approved', 'Package approved');
            flash('Approved. It can now be published.');
            break;
        case 'publish':
            need_action('publish');
            list($err) = pkg_blockers($p);
            if ($err) { flash('Publish is blocked: ' . count($err) . ' mandatory item' . (count($err) === 1 ? '' : 's') . ' missing. Save as draft and complete the checklist.', 'err'); break; }
            if (!in_array($p['status'], array('approved', 'published', 'paused'), true)) { flash('Only an approved package can be published.', 'err'); break; }
            $set('published', 'Package published', ', published_at = COALESCE(published_at, \'' . now() . '\'), published_version = version');
            flash('Published (staging). Version ' . $p['version'] . ' is the live version.');
            break;
        case 'pause':
            need_action('pause');
            if ($p['status'] !== 'published') break;
            $set('paused', 'Package paused');
            flash('Paused: the package is hidden from the website until it is published again.');
            break;
        case 'archive':
            need_action('archive');
            if ($p['status'] === 'archived') break;
            $set('archived', 'Package archived', ', archived_at = \'' . now() . '\'');
            flash('Archived. Its Package ID stays reserved and is never reused.');
            break;
        case 'restore':
            need_action('restore');
            if ($p['status'] !== 'archived') break;
            $set('draft', 'Package restored', ', archived_at = NULL');
            flash('Restored as a draft.');
            break;
        case 'assign_id':
            need_action('assign_package_id');
            try { $id = pkg_assign_id($pk); flash('Package ID ' . $id . ' assigned. It is permanent.'); }
            catch (RuntimeException $x) { flash($x->getMessage(), 'err'); }
            break;
        case 'delete':
            need_action('delete');
            if ($p['published_at'] || $p['package_id_status'] === 'approved' || qv('SELECT 1 FROM enquiries WHERE package_pk = ?', array($pk))) {
                flash('This package cannot be deleted (it was published, has a Package ID or has enquiries). Archive it instead.', 'err'); break;
            }
            cms_log('Package deleted', $pk, 'name', $p['name'], '');
            q('DELETE FROM packages WHERE package_pk = ?', array($pk));
            flash('Draft deleted.');
            redirect('/packages');
    }
    // Status changes are recorded in the activity log; they do not create a content version
    // (a new version would invalidate the review sign-offs of the version being reviewed).
    redirect($to);
}
