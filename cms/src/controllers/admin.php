<?php
/** Overview lists (curation, pricing, versions, destinations, SEO, users, activity), Recycle Bin, version restore. */

/** Render a simple list page. $rows are arrays of cell HTML (already escaped). */
function list_page($title, $active, $sub, array $headers, array $rows, $before = '', $right = '', $emptyText = 'Nothing to show.')
{
    ob_start();
    echo $before;
    if (!$rows) echo empty_state('Nothing here', $emptyText);
    else {
        echo '<div class="cms-tablewrap" role="region" aria-label="' . e($title) . '" tabindex="0"><table class="cms-table"><thead><tr>';
        foreach ($headers as $h) echo '<th scope="col">' . e($h) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr>';
            foreach (array_values($r) as $i => $c) echo '<td data-label="' . e($headers[$i]) . '">' . $c . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
    cms_render('layout', array('title' => $title, 'active' => $active, 'crumbs' => array(array('Dashboard', '/'), array($title, null)), 'head' => page_head($title, $sub, $right), 'content' => ob_get_clean()));
}

function pkg_link(array $p, $tab = '')
{
    list($id, $st) = pkg_id_state($p);
    return ($id !== '' ? '<span class="cms-code' . ($st === 'proposed' ? ' cms-code--proposed' : '') . '">' . e($id) . '</span> ' : '') . '<a href="/packages/' . (int) $p['package_pk'] . ($tab ? '?tab=' . $tab : '') . '">' . e($p['name']) . '</a>';
}

function curation_list()
{
    need('curation');
    $featured = get('view') === 'featured';
    $rows = q('SELECT p.*, c.priority_rank, c.featured, c.homepage_featured, c.search_featured, c.seasonal_featured, c.speciality_featured FROM curation c JOIN packages p ON p.package_pk = c.package_pk WHERE ' . ($featured ? 'c.featured = 1 OR c.homepage_featured = 1 OR c.search_featured = 1' : 'c.priority_rank IS NOT NULL') . ' ORDER BY c.priority_rank IS NULL, c.priority_rank')->fetchAll();
    $out = array();
    foreach ($rows as $r) {
        $flags = array();
        foreach (array('featured' => 'Featured', 'homepage_featured' => 'Homepage', 'search_featured' => 'Search', 'seasonal_featured' => 'Seasonal', 'speciality_featured' => 'Speciality') as $k => $l) if ($r[$k]) $flags[] = $l;
        $out[] = array($r['priority_rank'] ? '<b>' . (int) $r['priority_rank'] . '</b>' : '—', pkg_link($r, 'advanced'), e(implode(', ', $flags) ?: '—'), status_pill($r['status']));
    }
    list_page($featured ? 'Featured Tours' : 'Top Tour Collection', $featured ? 'featured' : 'curation', 'Internal merchandising (priority rank 1–500). Never shown publicly and separate from Offer Codes. Set ranks in each package’s Advanced tab.',
        array('Rank', 'Package', 'Placement', 'Status'), $out, '', '', 'No package is ranked yet. The owner decides the Top Tour ranking; set it per package in the Advanced tab.');
}

function pricing_overview()
{
    need('pricing');
    $rows = q("SELECT * FROM packages WHERE status <> 'archived' ORDER BY name")->fetchAll();
    $out = array(); $none = 0;
    foreach ($rows as $r) {
        $p = pkg_load($r['package_pk']);
        $cur = pkg_current_rate($p);
        $draft = array_filter($p['rates'], function ($x) { return $x['rate_status'] === 'draft'; });
        if (!$cur && !$p['rates']) { $none++; if (get('all') !== '1') continue; }
        $out[] = array(pkg_link($p, 'pricing'), e(rate_label($cur)), e($cur ? rate_validity($cur) : '—'), $cur ? 'v' . (int) $cur['version'] : '—', $draft ? '<span class="cms-pill cms-pill--draft">' . count($draft) . ' awaiting approval</span>' : '—');
    }
    $before = '<p class="cms-note">' . icon('info') . $none . ' package' . ($none === 1 ? ' has' : 's have') . ' no price version and show “Price on request”. ' . (get('all') === '1' ? '<a href="/pricing">Hide them</a>' : '<a href="/pricing?all=1">Show them</a>') . '</p>';
    list_page('Price & Availability', 'pricing', 'Current approved rate, validity and pending price versions for every package.', array('Package', 'Current rate', 'Valid', 'Version', 'Pending'), $out, $before, '', 'No rates have been entered yet. Rates are added per package in the Pricing tab.');
}

function versions_list()
{
    need('packages');
    $rows = q('SELECT pv.*, p.name, p.package_pk, p.package_id AS pid, p.proposed_package_id, p.package_id_status, u.name uname FROM package_versions pv JOIN packages p ON p.package_pk = pv.package_pk LEFT JOIN users u ON u.user_id = pv.changed_by WHERE pv.sections <> "import" ORDER BY pv.pv_pk DESC LIMIT 100')->fetchAll();
    $out = array();
    foreach ($rows as $r) $out[] = array(e(dmy($r['changed_at'])) . ' <small class="cms-muted">' . e(substr($r['changed_at'], 11, 5)) . '</small>', pkg_link(array('package_pk' => $r['package_pk'], 'name' => $r['name'], 'package_id' => $r['pid'], 'proposed_package_id' => $r['proposed_package_id'], 'package_id_status' => $r['package_id_status']), 'history'),
        '<a href="/packages/' . (int) $r['package_pk'] . '/versions/' . (int) $r['version'] . '">v' . (int) $r['version'] . '</a>', e($r['sections']), e($r['note']), e($r['uname'] ?: 'system'));
    list_page('Package Version History', 'versions', 'The latest 100 saved versions across all packages (website imports are version 1 of each package).', array('Date', 'Package', 'Version', 'Sections', 'Note', 'By'), $out, '', '', 'No edits yet — every package still has its imported version 1.');
}

function destinations_list()
{
    need('packages');
    $out = array();
    foreach (hg_destinations() as $k => $d) {
        $n = (int) qv("SELECT COUNT(*) FROM packages WHERE destination = ? AND status <> 'archived'", array($k));
        $out[] = array('<a href="/packages?dest=' . e($k) . '">' . e($d['name']) . '</a>', e($d['country']), e($d['region']), $n . ' <small class="cms-muted">internal</small>');
    }
    list_page('Destinations', 'destinations', 'Read from the website’s destination list (include/data/destinations.json). Adding destinations is a controlled Admin workflow (planned).', array('Destination', 'Country', 'Region', 'Packages'), $out);
}

function seo_dashboard()
{
    need('seo');
    $rows = q("SELECT package_pk FROM packages WHERE status <> 'archived' ORDER BY name")->fetchAll();
    $out = array(); $gaps = array();
    foreach ($rows as $r) {
        $p = pkg_load($r['package_pk']);
        $c = pkg_checklist($p);
        $miss = array_filter($c['SEO & AEO'], function ($i) { return !$i['ok']; });
        foreach ($miss as $m) $gaps[$m['key']] = ($gaps[$m['key']] ?? 0) + 1;
        if (!$miss) continue;
        $out[] = array(pkg_link($p, 'seo'), e(implode(', ', array_map(function ($i) { return preg_replace('/ \(.*/', '', $i['label']); }, $miss))), e(mb_strlen($p['seo']['meta_title'])), e(mb_strlen($p['seo']['meta_description'])));
    }
    $before = '<p class="cms-note">' . icon('info') . 'Internal checks only — this dashboard does not claim rankings. Search Console data is not connected.</p>';
    list_page('SEO Dashboard', 'seo', 'Packages missing SEO or AEO essentials.', array('Package', 'Missing', 'Title length', 'Description length'), $out, $before, '', 'Every package has its SEO and AEO essentials.');
}

function users_list()
{
    need('users');
    $out = array();
    foreach (q('SELECT * FROM users ORDER BY role, name')->fetchAll() as $u) $out[] = array(e($u['name']), e($u['email']), e(HG_ROLES[$u['role']] ?? $u['role']), $u['active'] ? 'Active' : 'Disabled', e($u['last_login_at'] ? dmy($u['last_login_at']) : 'Never'));
    $m = '<details class="cms-disclose"><summary>Role permissions</summary><div class="cms-tablewrap" tabindex="0" role="region" aria-label="Permission matrix"><table class="cms-table cms-table--matrix"><thead><tr><th scope="col">Module</th>';
    foreach (HG_ROLES as $r) $m .= '<th scope="col">' . e($r) . '</th>';
    $m .= '</tr></thead><tbody>';
    foreach (HG_MATRIX as $mod => $row) { $m .= '<tr><th scope="row">' . e(ucfirst($mod)) . '</th>'; foreach (HG_ROLES as $k => $r) $m .= '<td>' . e(ucfirst($row[$k] ?? 'none')) . '</td>'; $m .= '</tr>'; }
    $m .= '</tbody></table></div></details>';
    list_page('Users', 'users', 'Staging users (one per role). Passwords are hashed; invite and disable flows are planned.', array('Name', 'Email', 'Role', 'Status', 'Last login'), $out, $m);
}

function activity_list()
{
    need('activity');
    $args = array(); $where = '1=1';
    if (!hg_can(role(), 'activity', 'full') && role() !== 'admin') { $where = 'a.user_id = ?'; $args[] = uid(); }
    $rows = q("SELECT a.*, u.name uname FROM activity_log a LEFT JOIN users u ON u.user_id = a.user_id WHERE $where ORDER BY log_pk DESC LIMIT 300", $args)->fetchAll();
    $out = array();
    foreach ($rows as $a) $out[] = array(e(dmy($a['at'])) . ' <small class="cms-muted">' . e(substr($a['at'], 11, 8)) . ' UTC</small>', e($a['uname'] ?: 'system'), '<strong>' . e($a['action']) . '</strong>',
        $a['package_pk'] ? '<a href="/packages/' . (int) $a['package_pk'] . '?tab=history">' . ($a['package_id'] ? e($a['package_id']) : '#' . (int) $a['package_pk']) . '</a>' : '—',
        e($a['field']), '<span class="cms-break">' . e(mb_strimwidth($a['old_value'], 0, 80, '…')) . '</span>', '<span class="cms-break">' . e(mb_strimwidth($a['new_value'], 0, 80, '…')) . '</span>');
    list_page('Activity Log', 'activity', 'Insert-only audit trail: who changed what and when. Rows cannot be edited or deleted.' . ($where !== '1=1' ? ' Showing your own actions.' : ''),
        array('Date/time', 'User', 'Action', 'Package', 'Field', 'Old value', 'New value'), $out);
}

/* ---------- Recycle Bin ---------- */

function recycle_bin()
{
    need('packages');
    $rows = q("SELECT * FROM packages WHERE status = 'archived' ORDER BY archived_at DESC")->fetchAll();
    $out = array();
    foreach ($rows as $p) {
        $act = '';
        if (hg_may(role(), 'restore')) $act .= '<form method="post" action="/packages/' . (int) $p['package_pk'] . '/workflow" class="cms-inline">' . csrf_field() . '<input type="hidden" name="action" value="restore"><input type="hidden" name="tab" value="basic"><button class="cms-btn cms-btn--navy cms-btn--sm" type="submit">' . icon('restore') . 'Restore</button></form> ';
        $purgeable = !$p['published_at'] && $p['package_id_status'] !== 'approved' && !qv('SELECT 1 FROM enquiries WHERE package_pk = ?', array($p['package_pk']));
        if (hg_may(role(), 'delete')) $act .= $purgeable
            ? '<form method="post" action="/packages/' . (int) $p['package_pk'] . '/workflow" class="cms-inline">' . csrf_field() . '<input type="hidden" name="action" value="delete"><button class="cms-btn cms-btn--ghost cms-btn--sm cms-btn--danger" type="submit" data-cms-confirm="Delete this package permanently? This cannot be undone.">' . icon('trash') . 'Delete permanently</button></form>'
            : '<span class="cms-muted cms-small" title="Published packages, packages with a Package ID and packages with enquiries are kept for traceability.">Kept for audit</span>';
        $out[] = array(pkg_link($p), e(dmy($p['archived_at'])), $p['published_at'] ? 'Yes' : 'No', $act);
    }
    list_page('Recycle Bin', 'recycle', 'Archived packages. They are off the website and read-only. Restore returns a package to draft. Permanent delete is Super Admin only and only for drafts that were never published, never numbered and have no enquiries — Package IDs are never reused.',
        array('Package', 'Archived', 'Was published', 'Actions'), $out, '', '', 'The Recycle Bin is empty.');
}

/* ---------- Restore an earlier version (roll back content) ---------- */

function version_restore($pk, $v)
{
    need('packages', 'edit');
    $p = pkg_row($pk);
    if (!$p || $p['status'] === 'archived') deny('Restore the package from the Recycle Bin first.');
    $row = q1('SELECT * FROM package_versions WHERE package_pk = ? AND version = ?', array($pk, $v));
    if (!$row) deny('Version not found.');
    $s = jd($row['snapshot']);
    $db = cms_db();
    $db->beginTransaction();
    // Content only. Identity (Package ID), status, price versions (append-only) and offers are not rolled back.
    q('UPDATE packages SET name=?, country=?, region=?, destination=?, city_route=?, package_type=?, speciality_type=?, days=?, nights=?, suitable_for=?, short_description=?, description_html=?, highlights=?, duration_override=?, payable=?, enquiry_enabled=?, noindex=? WHERE package_pk=?', array(
        $s['name'], $s['country'], $s['region'], $s['destination'], $s['city_route'], $s['package_type'], $s['speciality_type'], $s['days'], $s['nights'], je($s['suitable_for']), $s['short_description'],
        $s['description_html'], je($s['highlights']), $s['duration_override'] ?? '', $s['payable'], $s['enquiry_enabled'], $s['noindex'], $pk));
    q('DELETE FROM itinerary_days WHERE package_pk = ?', array($pk));
    foreach ($s['days_list'] as $d) q('INSERT INTO itinerary_days(package_pk, day_number, title, destination, route, description, sightseeing, activities, meals, hotel, transport, optional_activities, notes, media_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array(
        $pk, $d['day_number'], $d['title'], $d['destination'], $d['route'], $d['description'], $d['sightseeing'], $d['activities'], $d['meals'], $d['hotel'], $d['transport'], $d['optional_activities'], $d['notes'],
        $d['media_id'] && qv('SELECT 1 FROM media WHERE media_id = ?', array($d['media_id'])) ? $d['media_id'] : null));
    q('DELETE FROM scope_items WHERE package_pk = ?', array($pk));
    foreach ($s['scope'] as $i) q('INSERT INTO scope_items(package_pk, kind, category, name, description, icon, sort_order, status, is_standard) VALUES (?,?,?,?,?,?,?,?,?)', array($pk, $i['kind'], $i['category'], $i['name'], $i['description'], $i['icon'], $i['sort_order'], $i['status'], $i['is_standard']));
    q('DELETE FROM addons WHERE package_pk = ?', array($pk));
    foreach ($s['addons'] as $a) q('INSERT INTO addons(package_pk, name, description, price, currency, price_unit, tax_percent, required, availability, sort_order, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)', array($pk, $a['name'], $a['description'], $a['price'], $a['currency'], $a['price_unit'], $a['tax_percent'], $a['required'], $a['availability'], $a['sort_order'], $a['status']));
    $seo = $s['seo'];
    q('INSERT OR REPLACE INTO package_seo(package_pk, meta_title, meta_description, canonical, og_title, og_description, og_media_id, aeo_question, aeo_answer, key_facts, supporting_questions) VALUES (?,?,?,?,?,?,?,?,?,?,?)', array(
        $pk, $seo['meta_title'], $seo['meta_description'], $seo['canonical'], $seo['og_title'], $seo['og_description'], null, $seo['aeo_question'], $seo['aeo_answer'], je($seo['key_facts']), je($seo['supporting_questions'])));
    q('DELETE FROM faqs WHERE package_pk = ?', array($pk));
    foreach ($s['faqs'] as $f) q('INSERT INTO faqs(package_pk, question, answer, sort_order) VALUES (?,?,?,?)', array($pk, $f['question'], $f['answer'], $f['sort_order']));
    q('DELETE FROM package_media WHERE package_pk = ?', array($pk));
    foreach ($s['media'] as $m) if (qv('SELECT 1 FROM media WHERE media_id = ?', array($m['media_id']))) q('INSERT INTO package_media(package_pk, media_id, role, day_number, sort_order) VALUES (?,?,?,?,?)', array($pk, $m['media_id'], $m['role'], $m['day_number'], $m['sort_order']));
    $db->commit();
    cms_log('Version restored', $pk, 'version', 'v' . $p['version'], 'content of v' . (int) $v);
    $nv = pkg_snapshot($pk, 'restore', 'Restored content of v' . (int) $v);
    if (in_array($p['status'], array('in_review', 'approved'), true)) q("UPDATE packages SET status = 'in_review' WHERE package_pk = ?", array($pk));
    flash('Content of version ' . (int) $v . ' restored as version ' . $nv . '. Package ID, prices and offers were not changed.');
    redirect('/packages/' . (int) $pk . '?tab=history');
}
