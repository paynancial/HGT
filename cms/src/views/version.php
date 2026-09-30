<?php
/** One saved version: what changed against the previous version, and restore. */
$fields = array('name' => 'Package name', 'destination' => 'Destination', 'city_route' => 'City / Route', 'package_type' => 'Package type', 'days' => 'Days', 'nights' => 'Nights',
    'short_description' => 'Short description', 'description_html' => 'Detailed description', 'highlights' => 'Highlights');
$str = function ($v) { return is_array($v) ? implode(' · ', array_map(function ($x) { return is_array($x) ? ($x['title'] ?? $x['name'] ?? $x['question'] ?? je($x)) : $x; }, $v)) : strip_tags((string) $v); };
$cmp = array();
foreach ($fields as $k => $l) $cmp[$l] = array($prev ? $str($prev[$k] ?? '') : '', $str($snap[$k] ?? ''));
$cmp['Itinerary'] = array($prev ? $str($prev['days_list'] ?? array()) : '', $str($snap['days_list'] ?? array()));
$cmp['Inclusions/exclusions'] = array($prev ? $str($prev['scope'] ?? array()) : '', $str($snap['scope'] ?? array()));
$cmp['Add-ons'] = array($prev ? $str($prev['addons'] ?? array()) : '', $str($snap['addons'] ?? array()));
$cmp['Meta title'] = array($prev ? $prev['seo']['meta_title'] ?? '' : '', $snap['seo']['meta_title'] ?? '');
$cmp['Meta description'] = array($prev ? $prev['seo']['meta_description'] ?? '' : '', $snap['seo']['meta_description'] ?? '');
$cmp['FAQs'] = array($prev ? $str($prev['faqs'] ?? array()) : '', $str($snap['faqs'] ?? array()));
$cmp['Images'] = array($prev ? count($prev['media'] ?? array()) . ' images' : '', count($snap['media'] ?? array()) . ' images');
ob_start(); ?>
<div class="cms-tablewrap" role="region" aria-label="Changes" tabindex="0">
<table class="cms-table cms-table--diff">
    <thead><tr><th scope="col">Field</th><th scope="col"><?= $prev ? 'Previous version' : '—' ?></th><th scope="col">This version (v<?= (int) $row['version'] ?>)</th></tr></thead>
    <tbody>
    <?php foreach ($cmp as $l => $v) { $chg = $prev && $v[0] !== $v[1]; ?>
        <tr<?= $chg ? ' class="is-changed"' : '' ?>><th scope="row"><?= e($l) ?><?= $chg ? ' <span class="cms-pill cms-pill--draft">Changed</span>' : '' ?></th><td data-label="Previous"><?= e(mb_strimwidth($v[0], 0, 400, '…')) ?></td><td data-label="This version"><?= e(mb_strimwidth($v[1], 0, 400, '…')) ?></td></tr>
    <?php } ?>
    </tbody>
</table>
</div>
<?php $body = ob_get_clean();
$restore = '';
if ((int) $row['version'] !== (int) $p['version'] && hg_can(role(), 'packages', 'edit') && $p['status'] !== 'archived') {
    $restore = '<form method="post" action="/packages/' . (int) $p['package_pk'] . '/versions/' . (int) $row['version'] . '/restore" class="cms-inline">' . csrf_field()
        . '<button class="cms-btn cms-btn--navy" type="submit" data-cms-confirm="Restore the content of version ' . (int) $row['version'] . '? It is saved as a new version; nothing is deleted.">' . icon('restore') . 'Restore this version</button></form>';
}
$content = card('Changes in this version', $body, array('sub' => e($row['sections']) . ($row['note'] ? ' · ' . e($row['note']) : '') . ' · ' . e($row['uname'] ?: 'system') . ' · ' . e(dmy($row['changed_at'])))) .
    '<p class="cms-hint">Restoring brings back the content (details, itinerary, inclusions, add-ons, SEO/AEO, FAQs, images). The Package ID, price versions and offers are never rolled back.</p>';
cms_render('layout', array('title' => 'Version ' . $row['version'] . ' · ' . $p['name'], 'active' => 'versions', 'crumbs' => array(array('Dashboard', '/'), array($p['name'], '/packages/' . (int) $p['package_pk'] . '?tab=history'), array('Version ' . $row['version'], null)),
    'head' => page_head('Version ' . (int) $row['version'], $p['name'], $restore . ' <a class="cms-btn cms-btn--ghost" href="/packages/' . (int) $p['package_pk'] . '?tab=history">Back to history</a>'), 'content' => $content));
