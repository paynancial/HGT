<?php
/** Screens 2 (All Packages), 3 (Add Package), 14 (Preview) and version snapshots. */

function packages_list()
{
    need('packages');
    $q = get('q'); $status = get('status'); $dest = get('dest');
    $res = cms_search($q, $status, $dest, 500);
    $rows = array();
    $check = get('check');
    foreach ($res['packages'] as $r) {
        $p = pkg_load($r['package_pk']);
        list($err, $warn) = pkg_blockers($p);
        if ($check === 'fail' && !$err) continue;
        if ($check === 'ready' && $err) continue;
        $total = 0;
        foreach (pkg_checklist($p) as $items) $total += count($items);
        $p['_err'] = $err; $p['_ready'] = $total - count($err) - count($warn); $p['_total'] = $total;
        $p['_rate'] = pkg_current_rate($p);
        $rows[] = $p;
    }
    // Pagination (internal list; 25 per page).
    $per = 25; $page = max(1, (int) get('page', 1)); $pages = max(1, (int) ceil(count($rows) / $per));
    $page = min($page, $pages);
    $shown = array_slice($rows, ($page - 1) * $per, $per);
    cms_render('packages', array('rows' => $shown, 'all' => count($rows), 'page' => $page, 'pages' => $pages, 'offer' => $res['offer'], 'q' => $q, 'status' => $status, 'dest' => $dest, 'check' => $check, 'focus' => get('focus')));
}

function package_new_get($errors = array(), $v = array())
{
    need('packages', 'edit');
    cms_render('package-new', array('errors' => $errors, 'v' => $v + array('name' => '', 'destination' => '', 'package_type' => '', 'days' => '', 'nights' => '', 'city_route' => '')));
}

function slugify($s)
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s)), '-'));
    return substr($s, 0, 90);
}

function package_new_post()
{
    need('packages', 'edit');
    $v = array('name' => post('name'), 'destination' => post('destination'), 'package_type' => post('package_type'), 'days' => post('days'), 'nights' => post('nights'), 'city_route' => post('city_route'));
    $err = array();
    if (mb_strlen($v['name']) < 5) $err['name'] = 'Enter the package name (at least 5 characters).';
    $dests = hg_destinations();
    if (!isset($dests[$v['destination']])) $err['destination'] = 'Choose a destination.';
    if (!in_array($v['package_type'], HG_PACKAGE_TYPES, true)) $err['package_type'] = 'Choose a package type.';
    if (!ctype_digit((string) $v['days']) || (int) $v['days'] < 1 || (int) $v['days'] > 60) $err['days'] = 'Days must be 1–60.';
    if (!ctype_digit((string) $v['nights']) || (int) $v['nights'] > 60) $err['nights'] = 'Nights must be 0–60.';
    if (!$err && (int) $v['nights'] !== (int) $v['days'] - 1 && (int) $v['nights'] !== (int) $v['days']) $err['nights'] = 'Nights are usually one less than days (e.g. 4 Days / 3 Nights).';
    $slug = slugify($v['name'] . ' ' . $v['nights'] . 'n ' . $v['days'] . 'd');
    if (!$err && qv('SELECT 1 FROM packages WHERE slug = ?', array($slug))) $err['name'] = 'A package with this name and duration already exists.';
    if ($err) return package_new_get($err, $v);
    $g = $dests[$v['destination']];
    $db = cms_db();
    $db->beginTransaction();
    q('INSERT INTO packages(slug, name, country, region, destination, city_route, package_type, days, nights, status, public_url, source, created_at, created_by, updated_at, updated_by, version)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)', array($slug, $v['name'], $g['country'], $g['region'], $v['destination'], $v['city_route'], $v['package_type'], (int) $v['days'], (int) $v['nights'], 'draft', '/' . $slug, 'cms', now(), uid(), now(), uid()));
    $pk = (int) $db->lastInsertId();
    foreach (HG_STANDARD_EXCLUSIONS as $i => $t) q("INSERT INTO scope_items(package_pk, kind, category, name, sort_order, is_standard, icon) VALUES (?, 'exclusion', 'Travel', ?, ?, 1, 'ticket')", array($pk, $t, $i));
    q('INSERT INTO package_seo(package_pk, canonical) VALUES (?, ?)', array($pk, rtrim(cms_config('site_url'), '/') . '/' . $slug));
    $db->commit();
    cms_log('Package created', $pk, 'name', '', $v['name']);
    pkg_snapshot($pk, 'basic', 'Created');
    flash('Package created as a draft. Package ID: pending approval. Add the itinerary next.');
    redirect('/packages/' . $pk . '?tab=basic');
}

function package_preview($pk)
{
    need('packages');
    $p = pkg_load($pk);
    if (!$p) { http_response_code(404); return cms_render('error', array('title' => 'Not found', 'message' => 'Package not found.')); }
    cms_render('preview', array('p' => $p));
}

function version_view($pk, $v)
{
    need('packages');
    $row = q1('SELECT pv.*, u.name uname FROM package_versions pv LEFT JOIN users u ON u.user_id = pv.changed_by WHERE package_pk = ? AND version = ?', array($pk, $v));
    if (!$row) { http_response_code(404); return cms_render('error', array('title' => 'Not found', 'message' => 'Version not found.')); }
    $snap = jd($row['snapshot']);
    $prev = q1('SELECT snapshot FROM package_versions WHERE package_pk = ? AND version < ? ORDER BY version DESC LIMIT 1', array($pk, $v));
    cms_render('version', array('row' => $row, 'snap' => $snap, 'prev' => $prev ? jd($prev['snapshot']) : null, 'p' => pkg_load($pk)));
}
