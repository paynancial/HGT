<?php
/** Screen 1 — Dashboard. Internal counts are allowed here (the CMS is not public). */

function dashboard()
{
    need('packages');
    $rows = q("SELECT package_pk FROM packages WHERE status <> 'archived'")->fetchAll();
    $ready = 0; $gaps = array(); $gapCount = array();
    foreach ($rows as $r) {
        $p = pkg_load($r['package_pk']);
        list($err) = pkg_blockers($p);
        if (!$err) { $ready++; continue; }
        foreach ($err as $i) $gapCount[$i['key']] = array($i['label'], (isset($gapCount[$i['key']]) ? $gapCount[$i['key']][1] : 0) + 1, $i['tab']);
    }
    uasort($gapCount, function ($a, $b) { return $b[1] - $a[1]; });
    $by = array();
    foreach (q('SELECT status, COUNT(*) n FROM packages GROUP BY status')->fetchAll() as $r) $by[$r['status']] = (int) $r['n'];
    $ids = q('SELECT package_id_status s, COUNT(*) n FROM packages GROUP BY package_id_status')->fetchAll();
    $idBy = array(); foreach ($ids as $r) $idBy[$r['s']] = (int) $r['n'];
    $soon = gmdate('Y-m-d', strtotime(today() . ' +14 days'));
    $expiring = q("SELECT r.*, p.name, p.package_pk, p.package_id, p.proposed_package_id, p.package_id_status FROM rate_versions r JOIN packages p ON p.package_pk = r.package_pk
        WHERE r.rate_status = 'approved' AND r.valid_until BETWEEN ? AND ? ORDER BY r.valid_until", array(today(), $soon))->fetchAll();
    $review = q("SELECT * FROM packages WHERE status IN ('in_review','approved') ORDER BY updated_at DESC LIMIT 8")->fetchAll();
    $activity = q('SELECT a.*, u.name uname FROM activity_log a LEFT JOIN users u ON u.user_id = a.user_id ORDER BY log_pk DESC LIMIT 8')->fetchAll();
    $enq = hg_can(role(), 'enquiries') ? q('SELECT * FROM enquiries ORDER BY enquiry_pk DESC LIMIT 5')->fetchAll() : array();
    $offersLive = (int) qv("SELECT COUNT(*) FROM offers WHERE status = 'published' AND valid_until >= ?", array(today()));
    $rated = (int) qv("SELECT COUNT(DISTINCT package_pk) FROM rate_versions WHERE rate_status = 'approved' AND ? BETWEEN valid_from AND valid_until", array(today()));
    cms_render('dashboard', compact('rows', 'ready', 'gapCount', 'by', 'idBy', 'expiring', 'review', 'activity', 'enq', 'offersLive', 'rated'));
}
