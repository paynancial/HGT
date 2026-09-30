<?php
ob_start(); ?>
<div class="cms-kpis cms-kpis--2">
    <div class="cms-kpi"><span class="cms-kpi__label">Live offers</span><strong><?= $live ?> <small>/ <?= (int) cms_config('offer_zone_limit') ?></small></strong><small>internal limit — not advertised</small></div>
    <div class="cms-kpi"><span class="cms-kpi__label">Next Offer Code</span><strong class="cms-code cms-code--offer">OF-<?= sprintf('%04d', (int) qv("SELECT last_value FROM sequences WHERE name = 'offer_code'") + 1) ?></strong><small>own sequence, never a Package ID</small></div>
</div>
<?php if (!$rows) { echo empty_state('No offers yet', 'The website shows “No offers are published right now” until an offer is published here.', hg_can(role(), 'offers', 'manage') ? '<a class="cms-btn cms-btn--primary" href="/offers/new">' . icon('plus') . 'Create offer</a>' : ''); } else { ?>
<div class="cms-tablewrap" role="region" aria-label="Offers" tabindex="0">
<table class="cms-table">
    <thead><tr><th scope="col">Offer Code</th><th scope="col">Offer</th><th scope="col">Type</th><th scope="col">Validity</th><th scope="col">Packages</th><th scope="col">Status</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $o) { $exp = $o['valid_until'] < today(); ?>
        <tr>
            <td data-label="Code"><a class="cms-code cms-code--offer" href="/offers/<?= e($o['offer_code']) ?>"><?= e($o['offer_code']) ?></a></td>
            <td data-label="Offer"><?= e($o['name']) ?></td>
            <td data-label="Type"><?= $o['offer_type'] === 'percentage' ? (int) $o['discount_value'] . '% off' : ($o['offer_type'] === 'flat' ? e(inr($o['discount_value'])) . ' off' : 'Value-add') ?></td>
            <td data-label="Validity"><?= e(dmy($o['valid_from'])) ?> – <?= e(dmy($o['valid_until'])) ?><?= $exp ? ' <span class="cms-warntext">expired</span>' : '' ?></td>
            <td data-label="Packages"><?= (int) $o['n'] ?></td>
            <td data-label="Status"><?= status_pill($exp && $o['status'] === 'published' ? 'expired' : $o['status']) ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>
<?php } ?>
<?php
$content = ob_get_clean();
$right = hg_can(role(), 'offers', 'manage') ? '<a class="cms-btn cms-btn--primary" href="/offers/new">' . icon('plus') . 'Create offer</a>' : '';
cms_render('layout', array('title' => 'Offer Zone', 'active' => 'offers', 'crumbs' => array(array('Dashboard', '/'), array('Merchandising', null), array('Offer Zone', null)),
    'head' => page_head('Offer Zone', 'Offer Codes (OF-0001…) are separate from Package IDs. One package may have several offers.', $right), 'content' => $content));
