<?php
$total = count($rows);
ob_start(); ?>
<div class="cms-kpis">
    <div class="cms-kpi"><span class="cms-kpi__label">Active packages</span><strong><?= $total ?></strong><small><?= (int) ($by['published'] ?? 0) ?> published · <?= (int) ($by['draft'] ?? 0) ?> draft · <?= (int) ($by['in_review'] ?? 0) ?> in review</small></div>
    <div class="cms-kpi"><span class="cms-kpi__label">Ready to publish</span><strong><?= $ready ?></strong><small>pass every mandatory check</small></div>
    <div class="cms-kpi"><span class="cms-kpi__label">With a current rate</span><strong><?= $rated ?></strong><small>others show “Price on request”</small></div>
    <div class="cms-kpi"><span class="cms-kpi__label">Offer Zone</span><strong><?= $offersLive ?> <small>/ <?= (int) cms_config('offer_zone_limit') ?></small></strong><small>live offers (internal limit)</small></div>
</div>
<p class="cms-note"><?= icon('info') ?>Counts on this dashboard are internal. The public website never shows package totals.</p>

<div class="cms-grid2">
<?php
$b = '';
if ($gapCount) {
    $b .= '<ul class="cms-bars">';
    $max = max(array_map(function ($g) { return $g[1]; }, $gapCount));
    foreach (array_slice($gapCount, 0, 8, true) as $k => $g) {
        $b .= '<li><span class="cms-bars__l">' . e(preg_replace('/ — .*/', '', $g[0])) . '</span><span class="cms-bars__bar"><span style="width:' . round(100 * $g[1] / $max) . '%"></span></span><span class="cms-bars__n">' . $g[1] . '</span></li>';
    }
    $b .= '</ul>';
} else $b = '<p class="cms-muted">Every active package passes the mandatory checks.</p>';
echo card('Publication gaps', $b, array('sub' => 'Packages missing a mandatory item. Fix these before the next publish.', 'actions' => '<a class="cms-btn cms-btn--ghost cms-btn--sm" href="/packages?check=fail">View packages</a>'));

$b = '<dl class="cms-dl">'
   . '<div><dt>Proposed (pending owner approval)</dt><dd>' . (int) ($idBy['proposed'] ?? 0) . '</dd></div>'
   . '<div><dt>Approved</dt><dd>' . (int) ($idBy['approved'] ?? 0) . '</dd></div>'
   . '<div><dt>Not yet numbered</dt><dd>' . (int) ($idBy['pending'] ?? 0) . '</dd></div></dl>'
   . '<p class="cms-hint">Assignment is ' . (cms_config('package_id_assignment') ? '<strong>on</strong>' : '<strong>off</strong> until the owner approves the Package ID mapping') . '. Offer Codes (OF-…) use their own sequence.</p>';
echo card('Package ID', $b, array('sub' => 'The only package identifier — itinerary, CRM, quotations and payments.'));
?>
</div>

<div class="cms-grid2">
<?php
$b = '';
if ($review) {
    $b .= '<ul class="cms-list">';
    foreach ($review as $p) $b .= '<li><a href="/packages/' . (int) $p['package_pk'] . '?tab=advanced">' . e($p['name']) . '</a> ' . status_pill($p['status']) . '</li>';
    $b .= '</ul>';
} else $b = '<p class="cms-muted">Nothing is waiting for review.</p>';
echo card('Review queue', $b, array('sub' => 'Content → SEO/AEO → Pricing → Approval → Publish'));

$b = '';
if ($expiring) {
    $b .= '<ul class="cms-list">';
    foreach ($expiring as $r) $b .= '<li><a href="/packages/' . (int) $r['package_pk'] . '?tab=pricing">' . e($r['name']) . '</a> <span class="cms-muted">v' . (int) $r['version'] . ' · ends ' . e(dmy($r['valid_until'])) . '</span></li>';
    $b .= '</ul>';
} else $b = '<p class="cms-muted">No approved rate ends in the next 14 days.</p>';
echo card('Rates ending soon', $b, array('sub' => 'After the end date the website shows “Price on request”.'));
?>
</div>

<div class="cms-grid2">
<?php
if (hg_can(role(), 'enquiries')) {
    $b = '';
    if ($enq) {
        $b .= '<ul class="cms-list">';
        foreach ($enq as $x) $b .= '<li><a href="/enquiries/' . (int) $x['enquiry_pk'] . '">' . e($x['name']) . '</a> <span class="cms-muted">' . e($x['package_name'] ?: $x['enquiry_type']) . '</span>' . ($x['is_test'] ? ' <span class="cms-pill cms-pill--test">Test</span>' : '') . '</li>';
        $b .= '</ul>';
    } else $b = '<p class="cms-muted">No enquiries recorded in the CMS yet. Website enquiries still arrive by email until the intake is connected.</p>';
    echo card('Latest enquiries', $b, array('actions' => '<a class="cms-btn cms-btn--ghost cms-btn--sm" href="/enquiries">All enquiries</a>'));
}
$b = '<ul class="cms-feed">';
foreach ($activity as $a) $b .= '<li><span class="cms-feed__t">' . e(dmy($a['at'])) . ' ' . e(substr($a['at'], 11, 5)) . '</span><span><strong>' . e($a['action']) . '</strong>' . ($a['package_pk'] ? ' · <a href="/packages/' . (int) $a['package_pk'] . '">package ' . ($a['package_id'] ? e($a['package_id']) : '#' . (int) $a['package_pk']) . '</a>' : '') . ($a['field'] ? ' · ' . e($a['field']) : '') . ' <span class="cms-muted">by ' . e($a['uname'] ?: 'system') . '</span></span></li>';
$b .= '</ul>';
echo card('Recent activity', $b, array('actions' => hg_can(role(), 'activity') ? '<a class="cms-btn cms-btn--ghost cms-btn--sm" href="/activity">Activity log</a>' : ''));
?>
</div>
<?php
$content = ob_get_clean();
$right = hg_can(role(), 'packages', 'edit') ? '<a class="cms-btn cms-btn--primary" href="/packages/new">' . icon('plus') . 'Add New Package</a>' : '';
cms_render('layout', array('title' => 'Dashboard', 'active' => 'dashboard', 'crumbs' => array(array('Dashboard', null)),
    'head' => page_head('Dashboard', 'Tour packages, pricing, offers and enquiries at a glance.', $right), 'content' => $content));
