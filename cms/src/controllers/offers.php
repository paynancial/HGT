<?php
/** Offer Zone: Offer Codes (OF-0001…) with their own sequence, separate from Package IDs. */

function offers_list()
{
    need('offers');
    $rows = q("SELECT o.*, (SELECT COUNT(*) FROM offer_packages op WHERE op.offer_pk = o.offer_pk) AS n FROM offers o ORDER BY o.offer_code")->fetchAll();
    $live = (int) qv("SELECT COUNT(*) FROM offers WHERE status = 'published' AND valid_until >= ?", array(today()));
    cms_render('offers', array('rows' => $rows, 'live' => $live));
}

function offer_form($code = null, $errors = array())
{
    need('offers');
    $o = $code ? q1('SELECT * FROM offers WHERE offer_code = ?', array($code)) : null;
    if ($code && !$o) { http_response_code(404); return cms_render('error', array('title' => 'Not found', 'message' => 'Offer not found.')); }
    $linked = $o ? array_map('intval', q('SELECT package_pk FROM offer_packages WHERE offer_pk = ?', array($o['offer_pk']))->fetchAll(PDO::FETCH_COLUMN)) : (get('package') ? array((int) get('package')) : array());
    $pkgs = q("SELECT package_pk, name, package_id, proposed_package_id, package_id_status, destination, package_type FROM packages WHERE status <> 'archived' ORDER BY name")->fetchAll();
    cms_render('offer-form', array('o' => $o, 'linked' => $linked, 'pkgs' => $pkgs, 'errors' => $errors));
}

function offer_save($code = null)
{
    need('offers', 'manage');
    $o = $code ? q1('SELECT * FROM offers WHERE offer_code = ?', array($code)) : null;
    if ($code && !$o) deny('Offer not found.');
    $v = array(
        'name' => post('name'), 'offer_type' => in_array(post('offer_type'), array('percentage', 'flat', 'value-add'), true) ? post('offer_type') : 'percentage',
        'discount_value' => post('discount_value') === '' ? null : (int) preg_replace('/[^\d]/', '', post('discount_value')),
        'valid_from' => post('valid_from'), 'valid_until' => post('valid_until'),
        'min_value' => post('min_value') === '' ? null : (int) preg_replace('/[^\d]/', '', post('min_value')),
        'usage_limit' => post('usage_limit') === '' ? null : (int) post('usage_limit'),
        'eligible_destinations' => je(array_values(array_intersect(arr($_POST['eligible_destinations'] ?? array()), array_keys(hg_destinations())))),
        'eligible_package_types' => je(array_values(array_intersect(arr($_POST['eligible_package_types'] ?? array()), HG_PACKAGE_TYPES))),
        'customer_eligibility' => post('customer_eligibility'), 'terms' => post('terms'),
        'status' => in_array(post('status'), array('draft', 'published', 'paused', 'archived'), true) ? post('status') : 'draft',
    );
    $err = array();
    if (mb_strlen($v['name']) < 4) $err[] = 'Enter the offer name.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v['valid_from']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v['valid_until']) || $v['valid_until'] < $v['valid_from']) $err[] = 'Enter a valid date range.';
    if ($v['offer_type'] === 'percentage' && ($v['discount_value'] === null || $v['discount_value'] < 1 || $v['discount_value'] > 90)) $err[] = 'A percentage discount must be 1–90.';
    if ($v['offer_type'] === 'flat' && !$v['discount_value']) $err[] = 'Enter the flat discount in rupees.';
    if (mb_strlen($v['terms']) < 20) $err[] = 'Write the offer terms (at least 20 characters).';
    if ($v['status'] === 'published') {
        if (!hg_may(role(), 'publish_offer')) $err[] = 'Your role cannot publish offers.';
        if ($v['valid_until'] < today()) $err[] = 'An expired offer cannot be published.';
        $live = (int) qv("SELECT COUNT(*) FROM offers WHERE status = 'published' AND valid_until >= ? AND offer_pk <> ?", array(today(), $o ? $o['offer_pk'] : 0));
        if ($live >= (int) cms_config('offer_zone_limit')) $err[] = 'The Offer Zone already has the maximum number of live offers.';
    }
    $pk = array_map('intval', arr($_POST['packages'] ?? array()));
    if ($v['status'] === 'published' && !$pk && !jd($v['eligible_destinations']) && !jd($v['eligible_package_types'])) $err[] = 'Link at least one package, destination or package type before publishing.';
    if ($err) { flash(implode(' ', $err), 'err'); $_SESSION['old'] = $_POST; redirect($code ? '/offers/' . $code : '/offers/new'); }
    $db = cms_db();
    $db->beginTransaction();
    if (!$o) {
        $code = next_offer_code();
        q('INSERT INTO offers(offer_code, name, offer_type, discount_value, valid_from, valid_until, min_value, usage_limit, eligible_destinations, eligible_package_types, customer_eligibility, terms, status, created_at, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            array($code, $v['name'], $v['offer_type'], $v['discount_value'], $v['valid_from'], $v['valid_until'], $v['min_value'], $v['usage_limit'], $v['eligible_destinations'], $v['eligible_package_types'], $v['customer_eligibility'], $v['terms'], $v['status'], now(), uid()));
        $opk = (int) $db->lastInsertId();
    } else {
        $opk = (int) $o['offer_pk'];
        q('UPDATE offers SET name=?, offer_type=?, discount_value=?, valid_from=?, valid_until=?, min_value=?, usage_limit=?, eligible_destinations=?, eligible_package_types=?, customer_eligibility=?, terms=?, status=? WHERE offer_pk=?',
            array($v['name'], $v['offer_type'], $v['discount_value'], $v['valid_from'], $v['valid_until'], $v['min_value'], $v['usage_limit'], $v['eligible_destinations'], $v['eligible_package_types'], $v['customer_eligibility'], $v['terms'], $v['status'], $opk));
    }
    $had = array_map('intval', q('SELECT package_pk FROM offer_packages WHERE offer_pk = ?', array($opk))->fetchAll(PDO::FETCH_COLUMN));
    q('DELETE FROM offer_packages WHERE offer_pk = ?', array($opk));
    foreach (array_unique($pk) as $p) if (qv('SELECT 1 FROM packages WHERE package_pk = ?', array($p))) q('INSERT INTO offer_packages(offer_pk, package_pk) VALUES (?,?)', array($opk, $p));
    $db->commit();
    cms_log($o ? 'Offer updated' : 'Offer created', null, 'offer', $o ? $o['status'] : '', $code . ' (' . $v['status'] . ')');
    foreach (array_diff($pk, $had) as $p) cms_log('Offer added', $p, 'offer', '', $code);
    foreach (array_diff($had, $pk) as $p) cms_log('Offer removed', $p, 'offer', $code, '');
    flash(($o ? 'Offer saved: ' : 'Offer created: ') . $code . '. Offer Codes never share the Package ID sequence.');
    redirect('/offers/' . $code);
}
