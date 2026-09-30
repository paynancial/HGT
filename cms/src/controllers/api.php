<?php
/**
 * JSON API (contract: docs/cms/API-CONTRACT.md, docs/cms/openapi.yaml).
 * Read endpoints need a CMS session. The enquiry intake needs the shared intake token (config 'intake_token').
 */

function api_json($data, $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function api_find_package($ref)
{
    if (strpos($ref, 'pk-') === 0) return pkg_load((int) substr($ref, 3));
    $pk = qv("SELECT package_pk FROM packages WHERE package_id = ? AND package_id_status = 'approved'", array($ref));
    return $pk ? pkg_load($pk) : null;
}

/** Public-safe representation (what the website/CRM may use). Proposed Package IDs are not exposed. */
function api_package_out(array $p)
{
    $r = pkg_current_rate($p);
    list($pay) = pkg_paynow($p);
    return array(
        'package_id' => pkg_public_id($p),
        'package_id_status' => $p['package_id_status'] === 'approved' ? 'approved' : 'pending',
        'ref' => 'pk-' . (int) $p['package_pk'],
        'slug' => $p['slug'], 'name' => $p['name'], 'status' => $p['status'], 'version' => (int) $p['version'], 'published_version' => $p['published_version'] ? (int) $p['published_version'] : null,
        'destination' => $p['destination'], 'country' => $p['country'], 'region' => $p['region'], 'route' => $p['city_route'],
        'package_type' => $p['package_type'], 'days' => (int) $p['days'], 'nights' => (int) $p['nights'], 'suitable_for' => $p['suitable_for'],
        'short_description' => $p['short_description'], 'description_html' => $p['description_html'], 'highlights' => $p['highlights'],
        'itinerary' => array_map(function ($d) { return array_intersect_key($d, array_flip(array('day_number', 'title', 'destination', 'route', 'description', 'sightseeing', 'activities', 'meals', 'hotel', 'transport', 'optional_activities', 'notes'))); }, $p['days_list']),
        'rate' => $r ? array('price_version' => (int) $r['version'], 'base_price' => (int) $r['base_price'], 'currency' => $r['currency'], 'price_unit' => $r['price_unit'], 'valid_from' => $r['valid_from'], 'valid_until' => $r['valid_until'], 'label' => rate_label($r)) : null,
        'price_label' => rate_label($r),
        'standard_statement' => HG_STANDARD_STATEMENT,
        'inclusions' => array_values(array_map(function ($s) { return array('category' => $s['category'], 'name' => $s['name'], 'description' => $s['description']); }, array_filter($p['scope'], function ($s) { return $s['kind'] === 'inclusion' && $s['status'] === 'active'; }))),
        'exclusions' => array_values(array_map(function ($s) { return array('category' => $s['category'], 'name' => $s['name'], 'description' => $s['description'], 'standard' => (bool) $s['is_standard']); }, array_filter($p['scope'], function ($s) { return $s['kind'] === 'exclusion' && $s['status'] === 'active'; }))),
        'addons' => array_values(array_map(function ($a) { return array('addon_id' => (int) $a['addon_pk'], 'name' => $a['name'], 'description' => $a['description'], 'price' => $a['price'] === null ? null : (int) $a['price'], 'price_unit' => $a['price_unit'], 'required' => (bool) $a['required'], 'availability' => $a['availability']); }, array_filter($p['addons'], function ($a) { return $a['status'] === 'active'; }))),
        'offers' => array_values(array_map(function ($o) { return array('offer_code' => $o['offer_code'], 'name' => $o['name'], 'valid_from' => $o['valid_from'], 'valid_until' => $o['valid_until']); }, array_filter($p['offers'], function ($o) { return $o['status'] === 'published' && $o['valid_from'] <= today() && $o['valid_until'] >= today(); }))),
        'seo' => array('meta_title' => $p['seo']['meta_title'], 'meta_description' => $p['seo']['meta_description'], 'canonical' => $p['seo']['canonical'], 'noindex' => (bool) $p['noindex']),
        'aeo' => array('question' => $p['seo']['aeo_question'], 'answer' => $p['seo']['aeo_answer'], 'key_facts' => $p['seo']['key_facts']),
        'faqs' => array_map(function ($f) { return array('question' => $f['question'], 'answer' => $f['answer']); }, $p['faqs']),
        'cta' => array('enquire_now' => (bool) $p['enquiry_enabled'], 'pay_now' => $pay),
    );
}

function api_packages()
{
    need('packages');
    $res = cms_search(get('q'), get('status'), get('dest'), 100);
    $out = array();
    foreach ($res['packages'] as $r) {
        $out[] = array('package_id' => $r['package_id_status'] === 'approved' ? $r['package_id'] : null, 'ref' => 'pk-' . (int) $r['package_pk'], 'name' => $r['name'], 'destination' => $r['destination'], 'status' => $r['status'], 'days' => (int) $r['days'], 'nights' => (int) $r['nights']);
    }
    api_json(array('offer' => $res['offer'] ? array('offer_code' => $res['offer']['offer_code'], 'name' => $res['offer']['name'], 'status' => $res['offer']['status']) : null, 'packages' => $out));
}

function api_package($ref)
{
    need('packages');
    $p = api_find_package($ref);
    if (!$p) return api_json(array('error' => 'not found'), 404);
    api_json(api_package_out($p));
}

function api_checklist($ref)
{
    need('packages');
    $p = api_find_package($ref);
    if (!$p) return api_json(array('error' => 'not found'), 404);
    list($err, $warn) = pkg_blockers($p);
    api_json(array('publishable' => !$err, 'errors' => array_column($err, 'label'), 'warnings' => array_column($warn, 'label'), 'checklist' => pkg_checklist($p)));
}

function api_offer($code)
{
    need('offers');
    $o = q1('SELECT * FROM offers WHERE offer_code = ?', array($code));
    if (!$o) return api_json(array('error' => 'not found'), 404);
    $pk = q('SELECT p.package_id, p.package_id_status, p.package_pk, p.name FROM offer_packages op JOIN packages p ON p.package_pk = op.package_pk WHERE op.offer_pk = ?', array($o['offer_pk']))->fetchAll();
    api_json(array('offer_code' => $o['offer_code'], 'name' => $o['name'], 'type' => $o['offer_type'], 'discount_value' => $o['discount_value'] === null ? null : (int) $o['discount_value'],
        'valid_from' => $o['valid_from'], 'valid_until' => $o['valid_until'], 'status' => $o['status'], 'terms' => $o['terms'],
        'eligible_destinations' => jd($o['eligible_destinations']), 'eligible_package_types' => jd($o['eligible_package_types']),
        'packages' => array_map(function ($r) { return array('package_id' => $r['package_id_status'] === 'approved' ? $r['package_id'] : null, 'ref' => 'pk-' . (int) $r['package_pk'], 'name' => $r['name']); }, $pk)));
}

/**
 * Website → CRM intake for "Tour Package Enquiry". The website sends the package slug (and optional offer code);
 * the CMS derives Package ID, rate, price version and validity itself — client-supplied values are ignored.
 */
function api_enquiry_intake()
{
    $token = (string) cms_config('intake_token');
    $given = isset($_SERVER['HTTP_X_HG_INTAKE_TOKEN']) ? $_SERVER['HTTP_X_HG_INTAKE_TOKEN'] : '';
    if ($token === '') return api_json(array('error' => 'intake disabled'), 503);
    if (!hash_equals($token, $given)) return api_json(array('error' => 'unauthorised'), 401);
    $in = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($in)) return api_json(array('error' => 'invalid JSON'), 400);
    $name = trim((string) ($in['name'] ?? ''));
    $email = trim((string) ($in['email'] ?? ''));
    $phone = trim((string) ($in['phone'] ?? ''));
    if ($name === '' || ($email === '' && $phone === '')) return api_json(array('error' => 'name and phone or email are required'), 422);
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return api_json(array('error' => 'invalid email'), 422);
    $pk = isset($in['package_slug']) ? qv('SELECT package_pk FROM packages WHERE slug = ?', array((string) $in['package_slug'])) : null;
    $ctx = enquiry_package_context($pk, strtoupper((string) ($in['offer_code'] ?? '')));
    $addons = array();
    if ($ctx && !empty($in['addon_ids']) && is_array($in['addon_ids'])) {
        $ids = array_map('intval', $in['addon_ids']);
        foreach (q("SELECT addon_pk, name, price, price_unit FROM addons WHERE package_pk = ? AND status = 'active' AND availability <> 'unavailable'", array($ctx['package_pk']))->fetchAll() as $a) if (in_array((int) $a['addon_pk'], $ids, true)) $addons[] = $a;
    }
    $utm = array();
    foreach (array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content') as $k) if (!empty($in[$k]) && is_string($in[$k])) $utm[$k] = mb_substr($in[$k], 0, 100);
    $id = enquiry_insert($ctx + array('source' => 'website', 'name' => mb_substr($name, 0, 120), 'email' => $email, 'phone' => mb_substr($phone, 0, 30),
        'travel_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($in['travel_date'] ?? '')) ? $in['travel_date'] : '',
        'adults' => isset($in['adults']) ? max(0, (int) $in['adults']) : null, 'children' => isset($in['children']) ? max(0, (int) $in['children']) : null,
        'departure_city' => mb_substr((string) ($in['departure_city'] ?? ''), 0, 80), 'message' => mb_substr((string) ($in['message'] ?? ''), 0, 4000),
        'addons' => je($addons), 'utm' => je($utm), 'is_test' => !empty($in['is_test']) ? 1 : 0, 'raw' => je($in)));
    cms_log('Website enquiry received', $ctx ? $ctx['package_pk'] : null, 'enquiry', '', '#' . $id);
    api_json(array('enquiry_id' => $id, 'package_id' => $ctx ? $ctx['package_id'] : null, 'price_version' => $ctx ? $ctx['rate_version'] : null, 'offer_code' => $ctx ? $ctx['offer_code'] : null), 201);
}
