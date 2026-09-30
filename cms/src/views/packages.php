<?php
$dests = hg_destinations();
$tab = $focus === 'itinerary' ? '?tab=itinerary' : '';
$qs = function ($over) use ($q, $status, $dest, $check) { return '/packages?' . http_build_query(array_filter(array_merge(array('q' => $q, 'status' => $status, 'dest' => $dest, 'check' => $check), $over), 'strlen')); };
ob_start(); ?>
<form class="cms-filters" method="get" action="/packages" role="search" aria-label="Filter packages">
    <div class="cms-field cms-field--grow"><label for="pl-q">Search</label><input id="pl-q" name="q" type="search" value="<?= e($q) ?>" placeholder="0001, Package ID 0001, Kashmir, OF-0001…"></div>
    <?= field_select('status', 'Status', $status, array('' => 'All except archived') + HG_STATUSES, array('id' => 'pl-status')) ?>
    <?php $dopts = array('' => 'All destinations'); foreach ($dests as $k => $d) $dopts[$k] = $d['name']; ?>
    <?= field_select('dest', 'Destination', $dest, $dopts, array('id' => 'pl-dest')) ?>
    <?= field_select('check', 'Readiness', $check, array('' => 'Any', 'fail' => 'Missing mandatory items', 'ready' => 'Ready to publish'), array('id' => 'pl-check')) ?>
    <?php if ($focus) { ?><input type="hidden" name="focus" value="<?= e($focus) ?>"><?php } ?>
    <button class="cms-btn cms-btn--navy" type="submit"><?= icon('search') ?>Apply</button>
</form>

<?php if ($offer) { ?>
    <section class="cms-card cms-offerhit" aria-labelledby="oh">
        <p class="cms-eyebrow">Offer Code match</p>
        <h2 id="oh"><span class="cms-code"><?= e($offer['offer_code']) ?></span> <?= e($offer['name']) ?> <?= status_pill($offer['status']) ?></h2>
        <p class="cms-muted"><?= e(dmy($offer['valid_from'])) ?> – <?= e(dmy($offer['valid_until'])) ?> · Offer Codes are separate from Package IDs.</p>
        <a class="cms-btn cms-btn--ghost cms-btn--sm" href="/offers/<?= e($offer['offer_code']) ?>">Open offer</a>
    </section>
<?php } elseif (preg_match('/^OF-?\d/i', $q)) { ?>
    <p class="cms-flash cms-flash--warn" role="status">No offer has the code <?= e(strtoupper($q)) ?>.</p>
<?php } ?>

<p class="cms-resultline" role="status"><?= (int) $all ?> package<?= $all === 1 ? '' : 's' ?> <?= $q !== '' || $status || $dest || $check ? 'match' : '' ?> <span class="cms-muted">(internal count)</span></p>

<?php if (!$rows) { echo empty_state('No packages found', 'Try a Package ID such as 0001, part of a name, or clear the filters.', '<a class="cms-btn cms-btn--ghost" href="/packages">Clear filters</a>'); } else { ?>
<div class="cms-tablewrap" role="region" aria-label="Packages" tabindex="0">
<table class="cms-table cms-table--packages">
    <thead><tr><th scope="col">Package ID</th><th scope="col">Package</th><th scope="col">Destination</th><th scope="col">Duration</th><th scope="col">Rate</th><th scope="col">Status</th><th scope="col">Readiness</th><th scope="col"><span class="cms-sr">Actions</span></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $p) { list($id, $st) = pkg_id_state($p); ?>
        <tr>
            <td data-label="Package ID"><?php if ($st === 'approved') { ?><b class="cms-code"><?= e($id) ?></b><?php } elseif ($st === 'proposed') { ?><span class="cms-code cms-code--proposed"><?= e($id) ?></span> <small class="cms-muted">proposed</small><?php } else { ?><small class="cms-muted">pending</small><?php } ?></td>
            <td data-label="Package"><a class="cms-strong" href="/packages/<?= (int) $p['package_pk'] ?><?= $tab ?>"><?= e($p['name']) ?></a><small class="cms-block cms-muted"><?= e($p['city_route']) ?></small></td>
            <td data-label="Destination"><?= e(isset($dests[$p['destination']]) ? $dests[$p['destination']]['name'] : $p['destination']) ?></td>
            <td data-label="Duration"><?= (int) $p['days'] ?>D / <?= (int) $p['nights'] ?>N</td>
            <td data-label="Rate"><?= $p['_rate'] ? e(rate_label($p['_rate'])) . '<small class="cms-block cms-muted">v' . (int) $p['_rate']['version'] . ' · to ' . e(dmy($p['_rate']['valid_until'])) . '</small>' : '<span class="cms-muted">Price on request</span>' ?></td>
            <td data-label="Status"><?= status_pill($p['status']) ?></td>
            <td data-label="Readiness"><span class="cms-meter" title="<?= (int) $p['_ready'] ?> of <?= (int) $p['_total'] ?> checks pass"><span style="width:<?= round(100 * $p['_ready'] / max(1, $p['_total'])) ?>%"></span></span><small><?= $p['_err'] ? count($p['_err']) . ' missing' : 'Ready' ?></small></td>
            <td class="cms-table__act"><a class="cms-btn cms-btn--ghost cms-btn--sm" href="/packages/<?= (int) $p['package_pk'] ?><?= $tab ?>">Edit</a> <a class="cms-btn cms-btn--ghost cms-btn--sm" href="/packages/<?= (int) $p['package_pk'] ?>/preview"><?= icon('eye') ?><span class="cms-sr">Preview <?= e($p['name']) ?></span></a></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>
<?php if ($pages > 1) { ?>
<nav class="cms-pager" aria-label="Pages">
    <?php if ($page > 1) { ?><a class="cms-btn cms-btn--ghost cms-btn--sm" href="<?= e($qs(array('page' => $page - 1))) ?>">Previous</a><?php } ?>
    <span>Page <?= $page ?> of <?= $pages ?></span>
    <?php if ($page < $pages) { ?><a class="cms-btn cms-btn--ghost cms-btn--sm" href="<?= e($qs(array('page' => $page + 1))) ?>">Next</a><?php } ?>
</nav>
<?php } } ?>
<?php
$content = ob_get_clean();
$right = hg_can(role(), 'packages', 'edit') ? '<a class="cms-btn cms-btn--primary" href="/packages/new">' . icon('plus') . 'Add New Package</a>' : '';
$t = $focus === 'itinerary' ? 'Manage Itinerary' : 'All Packages';
cms_render('layout', array('title' => $t, 'active' => $focus === 'itinerary' ? 'itinerary' : 'packages', 'crumbs' => array(array('Dashboard', '/'), array('Tour Packages', '/packages'), array($t, null)),
    'head' => page_head($t, $focus === 'itinerary' ? 'Choose a package to edit its day-wise itinerary.' : 'Search by Package ID, name, destination, status or Offer Code.', $right), 'content' => $content));
