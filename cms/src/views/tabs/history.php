<?php
/** Tab 10 — Version History (Screen 13). Content versions (snapshots) and status events. */
$vers = q('SELECT pv.pv_pk, pv.version, pv.package_id, pv.sections, pv.note, pv.changed_at, u.name uname FROM package_versions pv LEFT JOIN users u ON u.user_id = pv.changed_by WHERE package_pk = ? ORDER BY version DESC', array($p['package_pk']))->fetchAll();
$events = q("SELECT a.*, u.name uname FROM activity_log a LEFT JOIN users u ON u.user_id = a.user_id WHERE package_pk = ? ORDER BY log_pk DESC LIMIT 60", array($p['package_pk']))->fetchAll();
$sec = array('basic' => 'Basic', 'itinerary' => 'Itinerary', 'media' => 'Images', 'pricing' => 'Pricing', 'inclusions' => 'Inclusions', 'addons' => 'Add-ons', 'offers' => 'Offers', 'seo' => 'SEO/AEO', 'advanced' => 'Advanced', 'import' => 'Import', 'restore' => 'Restore');
ob_start(); ?>
<div class="cms-tablewrap" role="region" aria-label="Versions" tabindex="0">
<table class="cms-table">
    <thead><tr><th scope="col">Version</th><th scope="col">Package ID</th><th scope="col">Changed sections</th><th scope="col">Note</th><th scope="col">Changed by</th><th scope="col">Date</th><th scope="col"><span class="cms-sr">Open</span></th></tr></thead>
    <tbody>
    <?php foreach ($vers as $v) { ?>
        <tr<?= (int) $v['version'] === (int) $p['published_version'] ? ' class="is-current"' : '' ?>>
            <td data-label="Version"><b>v<?= (int) $v['version'] ?></b><?= (int) $v['version'] === (int) $p['published_version'] ? ' <span class="cms-pill cms-pill--published">Live</span>' : '' ?><?= (int) $v['version'] === (int) $p['version'] ? ' <span class="cms-pill cms-pill--draft">Latest</span>' : '' ?></td>
            <td data-label="Package ID"><?= $v['package_id'] ? '<span class="cms-code">' . e($v['package_id']) . '</span>' : '<span class="cms-muted">not assigned</span>' ?></td>
            <td data-label="Sections"><?= e(implode(', ', array_map(function ($x) use ($sec) { return isset($sec[$x]) ? $sec[$x] : $x; }, array_filter(explode(',', $v['sections']))))) ?></td>
            <td data-label="Note"><?= e($v['note']) ?></td>
            <td data-label="By"><?= e($v['uname'] ?: 'system') ?></td>
            <td data-label="Date"><?= e(dmy($v['changed_at'])) ?> <small class="cms-muted"><?= e(substr($v['changed_at'], 11, 5)) ?> UTC</small></td>
            <td class="cms-table__act"><a class="cms-btn cms-btn--ghost cms-btn--sm" href="/packages/<?= (int) $p['package_pk'] ?>/versions/<?= (int) $v['version'] ?>">View</a></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>
<?php echo card('Package versions', ob_get_clean(), array('sub' => 'A full copy is kept for every save. Old versions are never deleted; restoring one saves it as a new version.')); ?>

<?php ob_start(); ?>
<ul class="cms-feed">
<?php foreach ($events as $a) { ?>
    <li><span class="cms-feed__t"><?= e(dmy($a['at'])) ?> <?= e(substr($a['at'], 11, 5)) ?></span><span><strong><?= e($a['action']) ?></strong><?= $a['field'] ? ' · ' . e($a['field']) : '' ?><?php if ($a['old_value'] !== '' || $a['new_value'] !== '') { ?> <span class="cms-muted"><?= e(mb_strimwidth($a['old_value'], 0, 60, '…')) ?> → <?= e(mb_strimwidth($a['new_value'], 0, 60, '…')) ?></span><?php } ?> <span class="cms-muted">by <?= e($a['uname'] ?: 'system') ?></span></span></li>
<?php } ?>
</ul>
<?php echo card('Activity for this package', ob_get_clean());
