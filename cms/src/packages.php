<?php
/**
 * Tour package domain: taxonomy, loading, Package ID state, rates, publication checklist,
 * versions, search. Pure data logic — no HTML here.
 */

const HG_PACKAGE_TYPES = array('Leisure', 'Family', 'Honeymoon', 'Adventure', 'Luxury', 'Pilgrimage', 'Group', 'Weekend', 'Senior Citizen', 'Women Special', 'Inbound', 'Custom');
const HG_SUITABLE_FOR = array('Families', 'Couples', 'Groups', 'Solo Travellers', 'Senior Citizens', 'Children', 'Honeymoon', 'Corporate');
const HG_SPECIALITY = array('', 'Pilgrimage', 'Honeymoon', 'Family', 'Adventure', 'Wildlife', 'Hill Station', 'Beach', 'Houseboat', 'Helicopter', 'Luxury');
const HG_PRICE_UNITS = array('per person', 'per couple', 'per room', 'per vehicle', 'per day', 'flat fee');
const HG_SCOPE_CATEGORIES = array('Accommodation', 'Meals', 'Transfers', 'Sightseeing', 'Driver', 'Parking', 'Activities', 'Tickets', 'Guide', 'Taxes', 'Travel', 'Personal', 'Other', 'Imported');
const HG_STATUSES = array('draft' => 'Draft', 'in_review' => 'In review', 'approved' => 'Approved', 'published' => 'Published', 'paused' => 'Paused', 'archived' => 'Archived');
const HG_STANDARD_EXCLUSIONS = array('Airfare', 'Train fare', 'Bus fare');
const HG_STANDARD_STATEMENT = 'Standard package cost excludes airfare, train fare and bus fare unless specifically mentioned in the package inclusions.';

// Country for each destination group of the website (destinations.json has no country field).
const HG_GROUP_COUNTRY = array('dubai' => 'United Arab Emirates', 'maldives' => 'Maldives', 'singapore-malaysia' => 'Singapore & Malaysia');

function site_path($rel) { return rtrim(cms_config('site_root'), '/') . '/' . ltrim($rel, '/'); }

/** Destination taxonomy from the website's destinations.json: [key => [name, country, region]]. */
function hg_destinations()
{
    static $d = null;
    if ($d === null) {
        $d = array();
        $raw = json_decode((string) @file_get_contents(site_path('include/data/destinations.json')), true);
        foreach (isset($raw['groups']) ? $raw['groups'] : array() as $g) {
            $d[$g['key']] = array(
                'name' => $g['name'],
                'country' => isset(HG_GROUP_COUNTRY[$g['key']]) ? HG_GROUP_COUNTRY[$g['key']] : ($g['region'] === 'india' ? 'India' : ''),
                'region' => isset($g['area']) ? $g['area'] : '',
            );
        }
    }
    return $d;
}

function pkg_row($pk) { return q1('SELECT * FROM packages WHERE package_pk = ?', array((int) $pk)); }

/** Everything about one package (the aggregate the editor, preview, checklist and snapshots use). */
function pkg_load($pk)
{
    $p = pkg_row($pk);
    if (!$p) return null;
    $pk = (int) $p['package_pk'];
    $p['suitable_for'] = jd($p['suitable_for']);
    $p['highlights'] = jd($p['highlights']);
    $p['days_list'] = q('SELECT * FROM itinerary_days WHERE package_pk = ? ORDER BY day_number', array($pk))->fetchAll();
    $p['media'] = q('SELECT pm.pm_id, pm.role, pm.day_number, pm.sort_order, m.* FROM package_media pm JOIN media m ON m.media_id = pm.media_id WHERE pm.package_pk = ? ORDER BY pm.role, pm.sort_order, pm.pm_id', array($pk))->fetchAll();
    $p['rates'] = q('SELECT * FROM rate_versions WHERE package_pk = ? ORDER BY version DESC', array($pk))->fetchAll();
    $p['scope'] = q('SELECT * FROM scope_items WHERE package_pk = ? ORDER BY kind, sort_order, item_pk', array($pk))->fetchAll();
    $p['addons'] = q('SELECT * FROM addons WHERE package_pk = ? ORDER BY sort_order, addon_pk', array($pk))->fetchAll();
    $p['offers'] = q('SELECT o.* FROM offers o JOIN offer_packages op ON op.offer_pk = o.offer_pk WHERE op.package_pk = ? ORDER BY o.offer_code', array($pk))->fetchAll();
    $seo = q1('SELECT * FROM package_seo WHERE package_pk = ?', array($pk));
    if (!$seo) $seo = array('meta_title' => '', 'meta_description' => '', 'canonical' => '', 'og_title' => '', 'og_description' => '', 'og_media_id' => null, 'aeo_question' => '', 'aeo_answer' => '', 'key_facts' => '[]', 'supporting_questions' => '[]');
    $seo['key_facts'] = jd($seo['key_facts']);
    $seo['supporting_questions'] = jd($seo['supporting_questions']);
    $p['seo'] = $seo;
    $p['faqs'] = q('SELECT * FROM faqs WHERE package_pk = ? ORDER BY sort_order, faq_pk', array($pk))->fetchAll();
    $p['curation'] = q1('SELECT * FROM curation WHERE package_pk = ?', array($pk)) ?: array('priority_rank' => null, 'featured' => 0, 'homepage_featured' => 0, 'search_featured' => 0, 'seasonal_featured' => 0, 'speciality_featured' => 0);
    $p['reviews'] = q('SELECT r.*, u.name AS user_name FROM reviews r LEFT JOIN users u ON u.user_id = r.user_id WHERE r.package_pk = ? ORDER BY r.review_pk DESC', array($pk))->fetchAll();
    return $p;
}

/* ---------- Package ID ---------- */

/** How the Package ID reads inside the CMS: ['0001', 'approved'] | ['0001', 'proposed'] | ['', 'pending']. */
function pkg_id_state(array $p)
{
    if ($p['package_id_status'] === 'approved' && $p['package_id']) return array($p['package_id'], 'approved');
    if ($p['proposed_package_id']) return array($p['proposed_package_id'], 'proposed');
    return array('', 'pending');
}

/** The Package ID that may leave the CMS (public page, enquiry, quotation): approved only. */
function pkg_public_id(array $p)
{
    return $p['package_id_status'] === 'approved' ? $p['package_id'] : null;
}

/**
 * Assign the permanent Package ID. Disabled until the owner approves the mapping (config 'package_id_assignment').
 * Imported packages take their approved proposed number; new packages take the next number in the sequence,
 * which continues after the highest mapped number (never reuses one).
 */
function pkg_assign_id($pk)
{
    if (!cms_config('package_id_assignment')) throw new RuntimeException('Package ID assignment is switched off until the owner approves the Package ID mapping.');
    $db = cms_db();
    $db->beginTransaction();
    try {
        $p = pkg_row($pk);
        if (!$p) throw new RuntimeException('Package not found.');
        if ($p['package_id_status'] === 'approved') throw new RuntimeException('This package already has its permanent Package ID.');
        if ($p['proposed_package_id']) {
            $id = $p['proposed_package_id'];
        } else {
            $n = (int) qv('SELECT last_value FROM sequences WHERE name = ?', array('package_id')) + 1;
            if ($n > 9999) throw new RuntimeException('Package ID sequence exhausted.');
            q('UPDATE sequences SET last_value = ? WHERE name = ?', array($n, 'package_id'));
            $id = sprintf('%04d', $n);
        }
        q("UPDATE packages SET package_id = ?, package_id_status = 'approved' WHERE package_pk = ?", array($id, $pk));
        $db->commit();
    } catch (Throwable $t) {
        $db->rollBack();
        throw $t;
    }
    cms_log('Package ID assigned', $pk, 'package_id', '', $id);
    return $id;
}

/* ---------- Rates ---------- */

/** The approved rate version valid today, or null ("Price on request"). */
function pkg_current_rate(array $p, $day = null)
{
    $day = $day ?: today();
    foreach ($p['rates'] as $r) {  // newest version first
        if ($r['rate_status'] === 'approved' && $r['valid_from'] <= $day && $r['valid_until'] >= $day) return $r;
    }
    return null;
}

function rate_label($r) { return $r ? inr($r['base_price']) . ' / ' . preg_replace('/^per /', '', $r['price_unit']) : 'Price on request'; }
function rate_validity($r) { return $r ? dmy($r['valid_from']) . ' – ' . dmy($r['valid_until']) : ''; }

/** Pay Now can be active only when every condition holds. Returns [bool, reasons[]]. */
function pkg_paynow(array $p)
{
    $why = array();
    if (!pkg_current_rate($p)) $why[] = 'No current approved rate';
    if (!cms_config('payment_gateway')) $why[] = 'No payment gateway connected';
    if (!$p['payable']) $why[] = 'Package is not marked payable';
    if ($p['status'] !== 'published') $why[] = 'Package is not published';
    return array(!$why, $why);
}

/* ---------- Validation ---------- */

function pkg_duration_issue(array $p)
{
    $n = count($p['days_list']);
    if (!$p['days']) return null;
    if ($p['nights'] !== null && (int) $p['nights'] !== (int) $p['days'] - 1 && (int) $p['nights'] !== (int) $p['days']) {
        return sprintf('Duration reads %d days / %d nights. Check the nights.', $p['days'], $p['nights']);
    }
    if ($n && $n !== (int) $p['days']) {
        return sprintf('Itinerary contains %d day%s but package duration is %d days.', $n, $n === 1 ? '' : 's', $p['days']);
    }
    return null;
}

function words($html) { return str_word_count(trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)))); }

/**
 * Publication checklist (brief §15, §34, §50). Each item: [key, label, ok, severity, tab].
 * severity 'error' blocks Publish; 'warning' is shown but does not block.
 */
function pkg_checklist(array $p)
{
    $featured = array_values(array_filter($p['media'], function ($m) { return $m['role'] === 'featured'; }));
    $gallery = array_values(array_filter($p['media'], function ($m) { return $m['role'] === 'gallery'; }));
    $noAlt = array_filter($p['media'], function ($m) { return trim($m['alt_text']) === ''; });
    $inc = array_filter($p['scope'], function ($s) { return $s['kind'] === 'inclusion' && $s['status'] === 'active'; });
    $exc = array_filter($p['scope'], function ($s) { return $s['kind'] === 'exclusion' && $s['status'] === 'active'; });
    $std = array_filter($exc, function ($s) { return $s['is_standard']; });
    $rate = pkg_current_rate($p);
    $latest = $p['rates'] ? $p['rates'][0] : null;
    $expired = !$rate && array_filter($p['rates'], function ($r) { return $r['rate_status'] === 'approved' && $r['valid_until'] < today(); });
    $dur = pkg_duration_issue($p);
    $emptyDays = array_filter($p['days_list'], function ($d) { return trim($d['description']) === ''; });
    $seo = $p['seo'];
    $mt = mb_strlen($seo['meta_title']);
    $md = mb_strlen($seo['meta_description']);
    $offersBad = array_filter($p['offers'], function ($o) { return $o['status'] === 'published' && $o['valid_until'] < today(); });

    $c = array();
    $c['Content'] = array(
        array('description', 'Unique package description (80+ words)', words($p['description_html']) >= 80, 'error', 'basic'),
        array('short', 'Short description (120–300 characters)', mb_strlen($p['short_description']) >= 120 && mb_strlen($p['short_description']) <= 300, 'warning', 'basic'),
        array('highlights', 'Highlights (at least 3)', count($p['highlights']) >= 3, 'error', 'basic'),
        array('itinerary', 'Day-wise itinerary', count($p['days_list']) > 0 && !$emptyDays, 'error', 'itinerary'),
        array('duration', $dur ? 'Duration matches itinerary — ' . $dur : 'Duration matches itinerary', !$dur || $p['duration_override'] !== '', 'error', 'itinerary'),
        array('inclusions', 'Inclusions', count($inc) > 0, 'error', 'inclusions'),
        array('exclusions', 'Exclusions incl. airfare / train fare / bus fare rule', count($exc) > 0 && count($std) === 3, 'error', 'inclusions'),
        array('destination', 'Destination relationship', $p['destination'] !== '' && isset(hg_destinations()[$p['destination']]), 'error', 'basic'),
    );
    $c['Commercial'] = array(
        array('pricing', $rate ? 'Current approved rate' : ($expired ? 'Rate expired — shows "Price on request"' : 'No current rate — shows "Price on request"'), (bool) $rate, 'warning', 'pricing'),
        array('validity', 'Rate validity dates', !$latest || ($latest['valid_from'] && $latest['valid_until']), 'error', 'pricing'),
        array('enquiry', 'Enquire Now enabled', (bool) $p['enquiry_enabled'], 'error', 'advanced'),
        array('paynow', pkg_paynow($p)[0] ? 'Pay Now active (current rate + gateway)' : 'Pay Now shown disabled: ' . strtolower(implode('; ', pkg_paynow($p)[1])), true, 'error', 'advanced'),
        array('offers', 'No expired offer still published', !$offersBad, 'error', 'offers'),
    );
    $c['SEO & AEO'] = array(
        array('meta_title', 'Meta title (30–65 characters)', $mt >= 30 && $mt <= 65, 'error', 'seo'),
        array('meta_description', 'Meta description (120–160 characters)', $md >= 120 && $md <= 160, 'error', 'seo'),
        array('canonical', 'Canonical URL (absolute, no .php)', (bool) preg_match('~^https://[^\s]+$~', $seo['canonical']) && !preg_match('~\.php(\?|$)~', $seo['canonical']), 'error', 'seo'),
        array('aeo', 'AEO question and direct answer', trim($seo['aeo_question']) !== '' && words($seo['aeo_answer']) >= 15, 'error', 'seo'),
        array('faq', 'Package FAQs (at least 2)', count($p['faqs']) >= 2, 'error', 'seo'),
    );
    $c['Media'] = array(
        array('featured', 'Featured image', count($featured) === 1, 'error', 'media'),
        array('gallery', 'Gallery (at least 3 images)', count($gallery) >= 3, 'warning', 'media'),
        array('alt', 'Alt text on every image', count($p['media']) > 0 && !$noAlt, 'error', 'media'),
    );
    foreach ($c as &$items) {
        foreach ($items as &$i) $i = array('key' => $i[0], 'label' => $i[1], 'ok' => (bool) $i[2], 'severity' => $i[3], 'tab' => $i[4]);
    }
    return $c;
}

/** [blocking errors, warnings] from the checklist. */
function pkg_blockers(array $p)
{
    $err = array(); $warn = array();
    foreach (pkg_checklist($p) as $items) foreach ($items as $i) {
        if ($i['ok']) continue;
        if ($i['severity'] === 'error') $err[] = $i; else $warn[] = $i;
    }
    return array($err, $warn);
}

/** Per-tab completion for the tab bar: 'done' | 'todo' | 'warn' | ''. */
function pkg_tab_states(array $p)
{
    $s = array();
    foreach (pkg_checklist($p) as $items) foreach ($items as $i) {
        $cur = isset($s[$i['tab']]) ? $s[$i['tab']] : 'done';
        if (!$i['ok'] && $i['severity'] === 'error') $cur = 'todo';
        elseif (!$i['ok'] && $cur !== 'todo') $cur = 'warn';
        $s[$i['tab']] = $cur;
    }
    return $s;
}

/* ---------- Workflow ---------- */

/** Review sign-offs for the current package version: stage => row|null. */
function pkg_signoffs(array $p)
{
    $out = array('content' => null, 'seo' => null, 'pricing' => null);
    foreach ($p['reviews'] as $r) {  // newest first
        if ((int) $r['package_version'] === (int) $p['version'] && $out[$r['stage']] === null) $out[$r['stage']] = $r;
    }
    return $out;
}

/* ---------- Versions ---------- */

/** Bump the package version and store a full snapshot. Call after every save. */
function pkg_snapshot($pk, $sections, $note = '')
{
    q('UPDATE packages SET version = version + 1, updated_at = ?, updated_by = ? WHERE package_pk = ?', array(now(), uid(), $pk));
    $p = pkg_load($pk);
    unset($p['reviews']);
    q('INSERT INTO package_versions(package_pk, package_id, version, sections, note, snapshot, changed_by, changed_at) VALUES (?,?,?,?,?,?,?,?)', array(
        $pk, pkg_public_id($p), $p['version'], is_array($sections) ? implode(',', $sections) : $sections, $note, je($p), uid(), now(),
    ));
    return (int) $p['version'];
}

/* ---------- Search ---------- */

/**
 * CMS search: Package ID (approved or proposed), package name, destination, Offer Code, status.
 * Returns ['offer' => row|null, 'packages' => rows].
 */
function cms_search($term, $status = '', $dest = '', $limit = 200)
{
    $term = trim($term);
    $out = array('offer' => null, 'packages' => array());
    if (preg_match('/^OF-?(\d{1,4})$/i', $term, $m)) {
        $code = 'OF-' . str_pad($m[1], 4, '0', STR_PAD_LEFT);
        $out['offer'] = q1('SELECT * FROM offers WHERE offer_code = ?', array($code));
        if ($out['offer']) {
            $out['packages'] = q('SELECT p.* FROM packages p JOIN offer_packages op ON op.package_pk = p.package_pk WHERE op.offer_pk = ? ORDER BY p.name', array($out['offer']['offer_pk']))->fetchAll();
        }
        return $out;
    }
    $where = array('1=1'); $args = array();
    if ($term !== '') {
        if (preg_match('/^(?:package\s*id\s*)?(\d{1,4})$/i', $term, $m)) {
            $id = str_pad($m[1], 4, '0', STR_PAD_LEFT);
            $where[] = '(package_id = ? OR proposed_package_id = ?)';
            $args[] = $id; $args[] = $id;
        } else {
            $where[] = '(name LIKE ? OR slug LIKE ? OR destination LIKE ? OR city_route LIKE ?)';
            $like = '%' . str_replace(array('%', '_'), array('\%', '\_'), $term) . '%';
            array_push($args, $like, $like, $like, $like);
        }
    }
    if ($status !== '' && isset(HG_STATUSES[$status])) { $where[] = 'status = ?'; $args[] = $status; }
    elseif ($status === '') { $where[] = "status <> 'archived'"; }
    if ($dest !== '') { $where[] = 'destination = ?'; $args[] = $dest; }
    $out['packages'] = q('SELECT * FROM packages WHERE ' . implode(' AND ', $where) . ' ORDER BY COALESCE(package_id, proposed_package_id, \'9999\'), name LIMIT ' . (int) $limit, $args)->fetchAll();
    return $out;
}

/* ---------- Offers ---------- */

function next_offer_code()
{
    $db = cms_db();
    $n = (int) qv('SELECT last_value FROM sequences WHERE name = ?', array('offer_code')) + 1;
    if ($n > 9999) throw new RuntimeException('Offer Code sequence exhausted.');
    q('UPDATE sequences SET last_value = ? WHERE name = ?', array($n, 'offer_code'));
    return sprintf('OF-%04d', $n);
}

/* ---------- Rich text ---------- */

/** Allow-list sanitiser for the description editor. */
function clean_html($html)
{
    $html = trim((string) $html);
    if ($html === '') return '';
    $allowed = array('h2' => array(), 'h3' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(),
        'a' => array('href'), 'table' => array(), 'thead' => array(), 'tbody' => array(), 'tr' => array(), 'th' => array(), 'td' => array(), 'br' => array(),
        'aside' => array('class'), 'figure' => array(), 'img' => array('src', 'alt'), 'figcaption' => array());
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('root');
    $walk = function (DOMNode $n) use (&$walk, $allowed) {
        foreach (iterator_to_array($n->childNodes) as $c) {
            if ($c instanceof DOMElement) {
                $tag = strtolower($c->tagName);
                if (in_array($tag, array('script', 'style', 'iframe', 'object', 'embed', 'form'), true)) { $n->removeChild($c); continue; }
                if ($tag === 'div' || $tag === 'span' || !isset($allowed[$tag])) {  // unwrap
                    $walk($c);
                    while ($c->firstChild) $n->insertBefore($c->firstChild, $c);
                    $n->removeChild($c);
                    continue;
                }
                foreach (iterator_to_array($c->attributes) as $a) {
                    $name = strtolower($a->name);
                    $ok = in_array($name, $allowed[$tag], true);
                    if ($ok && in_array($name, array('href', 'src'), true) && !preg_match('~^(https?://|/|#|mailto:)~i', trim($a->value))) $ok = false;
                    if ($ok && $name === 'class' && !in_array($a->value, array('hg-tip', 'hg-note', 'hg-callout'), true)) $ok = false;
                    if (!$ok) $c->removeAttribute($a->name);
                }
                if ($tag === 'a' && $c->hasAttribute('href') && preg_match('~^https?://~i', $c->getAttribute('href'))) $c->setAttribute('rel', 'noopener');
                $walk($c);
            } elseif ($c instanceof DOMComment) {
                $n->removeChild($c);
            }
        }
    };
    $walk($root);
    $out = '';
    foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    return trim($out);
}

/** Split a textarea into non-empty lines. */
function lines($s)
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $s)), 'strlen'));
}
