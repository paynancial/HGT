<?php
/**
 * CRM enquiries (Screen 15). Website "Tour Package Enquiry" submissions (Contact Us enquiry type) arrive through
 * POST /api/enquiries once connected; staff can also log phone / WhatsApp / walk-in enquiries.
 */

const HG_STAGES = array('new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost');

function enquiries_list()
{
    need('enquiries');
    $stage = get('stage');
    $where = '1=1'; $args = array();
    if (isset(HG_STAGES[$stage])) { $where .= ' AND stage = ?'; $args[] = $stage; }
    $q = get('q');
    if ($q !== '') {
        if (preg_match('/^\d{1,4}$/', $q)) { $where .= ' AND package_id = ?'; $args[] = str_pad($q, 4, '0', STR_PAD_LEFT); }
        elseif (preg_match('/^OF-\d{4}$/i', $q)) { $where .= ' AND offer_code = ?'; $args[] = strtoupper($q); }
        else { $where .= ' AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR package_name LIKE ?)'; $l = '%' . $q . '%'; array_push($args, $l, $l, $l, $l); }
    }
    $rows = q("SELECT e.*, u.name owner FROM enquiries e LEFT JOIN users u ON u.user_id = e.assigned_to WHERE $where ORDER BY enquiry_pk DESC LIMIT 200", $args)->fetchAll();
    cms_render('enquiries', array('rows' => $rows, 'stage' => $stage, 'q' => $q));
}

function enquiry_new_get()
{
    need('enquiries', 'manage');
    $pkgs = q("SELECT package_pk, name, package_id, proposed_package_id, package_id_status FROM packages WHERE status IN ('published','paused','approved','in_review','draft') ORDER BY name")->fetchAll();
    cms_render('enquiry-new', array('pkgs' => $pkgs, 'pre' => (int) get('package')));
}

/** Build the package context exactly as the website does: only data the server knows, never client-supplied. */
function enquiry_package_context($pk, $offerCode = '')
{
    $p = $pk ? pkg_load($pk) : null;
    if (!$p) return array();
    $r = pkg_current_rate($p);
    $offer = null;
    if ($offerCode !== '') {
        foreach ($p['offers'] as $o) if ($o['offer_code'] === $offerCode && $o['status'] === 'published' && $o['valid_from'] <= today() && $o['valid_until'] >= today()) $offer = $o['offer_code'];
    }
    $d = hg_destinations();
    return array('package_pk' => $p['package_pk'], 'package_id' => pkg_public_id($p), 'package_name' => $p['name'], 'internal_ref' => 'slug:' . $p['slug'],
        'destination' => isset($d[$p['destination']]) ? $d[$p['destination']]['name'] : $p['destination'], 'displayed_rate' => rate_label($r), 'rate_version' => $r ? (int) $r['version'] : null,
        'rate_validity' => rate_validity($r), 'offer_code' => $offer, 'package_url' => rtrim(cms_config('site_url'), '/') . $p['public_url']);
}

function enquiry_insert(array $e)
{
    $cols = array('created_at', 'enquiry_type', 'source', 'name', 'email', 'phone', 'package_pk', 'package_id', 'package_name', 'internal_ref', 'destination', 'travel_date', 'adults', 'children',
        'departure_city', 'displayed_rate', 'rate_version', 'rate_validity', 'addons', 'offer_code', 'package_url', 'utm', 'message', 'is_test', 'raw', 'assigned_to');
    $e += array('created_at' => now(), 'enquiry_type' => 'TOUR PACKAGE ENQUIRY', 'source' => 'website', 'addons' => '[]', 'utm' => '{}', 'raw' => '{}', 'is_test' => 0);
    $vals = array();
    foreach ($cols as $c) $vals[] = array_key_exists($c, $e) ? $e[$c] : ($c === 'package_pk' || $c === 'package_id' || $c === 'rate_version' || $c === 'offer_code' || $c === 'adults' || $c === 'children' || $c === 'assigned_to' ? null : '');
    q('INSERT INTO enquiries(' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')', $vals);
    return (int) cms_db()->lastInsertId();
}

function enquiry_new_post()
{
    need('enquiries', 'manage');
    $name = post('name'); $phone = post('phone'); $email = post('email');
    if (mb_strlen($name) < 2 || ($phone === '' && $email === '')) { flash('Enter the traveller’s name and a phone number or email.', 'err'); redirect('/enquiries/new'); }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('The email address is not valid.', 'err'); redirect('/enquiries/new'); }
    $src = in_array(post('source'), array('phone', 'whatsapp', 'email', 'walk-in', 'website'), true) ? post('source') : 'phone';
    $addonIds = array_map('intval', arr($_POST['addons'] ?? array()));
    $ctx = enquiry_package_context((int) post('package_pk'), strtoupper(post('offer_code')));
    $addons = array();
    if ($ctx && $addonIds) foreach (q("SELECT addon_pk, name, price, price_unit FROM addons WHERE package_pk = ? AND status = 'active' AND availability <> 'unavailable'", array($ctx['package_pk']))->fetchAll() as $a) if (in_array((int) $a['addon_pk'], $addonIds, true)) $addons[] = $a;
    $id = enquiry_insert($ctx + array('source' => $src, 'name' => $name, 'email' => $email, 'phone' => $phone, 'travel_date' => post('travel_date'),
        'adults' => post('adults') === '' ? null : (int) post('adults'), 'children' => post('children') === '' ? null : (int) post('children'), 'departure_city' => post('departure_city'),
        'message' => post('message'), 'addons' => je($addons), 'assigned_to' => uid(), 'is_test' => post('is_test') === '1' ? 1 : 0));
    cms_log('Enquiry logged', $ctx ? $ctx['package_pk'] : null, 'enquiry', '', '#' . $id . ' (' . $src . ')');
    flash('Enquiry #' . $id . ' logged.');
    redirect('/enquiries/' . $id);
}

function enquiry_view($id)
{
    need('enquiries');
    $e = q1('SELECT e.*, u.name owner FROM enquiries e LEFT JOIN users u ON u.user_id = e.assigned_to WHERE enquiry_pk = ?', array($id));
    if (!$e) { http_response_code(404); return cms_render('error', array('title' => 'Not found', 'message' => 'Enquiry not found.')); }
    $notes = q('SELECT n.*, u.name uname FROM enquiry_notes n LEFT JOIN users u ON u.user_id = n.user_id WHERE enquiry_pk = ? ORDER BY note_pk DESC', array($id))->fetchAll();
    $pkg = $e['package_pk'] ? pkg_row($e['package_pk']) : null;
    $users = q("SELECT user_id, name, role FROM users WHERE active = 1 AND role IN ('super_admin','admin','sales_manager','travel_consultant') ORDER BY name")->fetchAll();
    $rateNow = $pkg ? pkg_current_rate(pkg_load($pkg['package_pk'])) : null;
    cms_render('enquiry', compact('e', 'notes', 'pkg', 'users', 'rateNow'));
}

function enquiry_update($id)
{
    need('enquiries', 'manage');
    $e = q1('SELECT * FROM enquiries WHERE enquiry_pk = ?', array($id));
    if (!$e) deny('Enquiry not found.');
    $stage = post('stage');
    if (isset(HG_STAGES[$stage]) && $stage !== $e['stage']) {
        q('UPDATE enquiries SET stage = ? WHERE enquiry_pk = ?', array($stage, $id));
        cms_log('Enquiry stage changed', $e['package_pk'], 'enquiry #' . $id, $e['stage'], $stage);
    }
    $to = post('assigned_to');
    if ($to !== '' && (int) $to !== (int) $e['assigned_to'] && qv('SELECT 1 FROM users WHERE user_id = ?', array((int) $to))) {
        q('UPDATE enquiries SET assigned_to = ? WHERE enquiry_pk = ?', array((int) $to, $id));
        cms_log('Enquiry assigned', $e['package_pk'], 'enquiry #' . $id, (string) $e['assigned_to'], $to);
    }
    if (post('note') !== '') q('INSERT INTO enquiry_notes(enquiry_pk, user_id, at, note) VALUES (?,?,?,?)', array($id, uid(), now(), post('note')));
    flash('Enquiry updated.');
    redirect('/enquiries/' . (int) $id);
}
