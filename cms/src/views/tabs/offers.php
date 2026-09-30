<?php
/** Tab 7 — Offer Zone (Screen 10). Offer Codes are separate from the Package ID; a package may have several offers. */
$all = q("SELECT * FROM offers WHERE status <> 'archived' ORDER BY offer_code")->fetchAll();
$linked = array_map(function ($o) { return (int) $o['offer_pk']; }, $p['offers']);
$manage = hg_can(role(), 'offers', 'manage');
list($pid, $st) = pkg_id_state($p);
ob_start(); ?>
<div class="cms-idsplit">
    <div><span class="cms-eyebrow">Package ID</span><b class="cms-code<?= $st === 'proposed' ? ' cms-code--proposed' : '' ?>"><?= $pid !== '' ? e($pid) : 'Pending' ?></b></div>
    <div class="cms-idsplit__arrow" aria-hidden="true">→</div>
    <div><span class="cms-eyebrow">Offer Codes</span><?= $p['offers'] ? implode(' ', array_map(function ($o) { return '<b class="cms-code cms-code--offer">' . e($o['offer_code']) . '</b>'; }, $p['offers'])) : '<span class="cms-muted">None</span>' ?></div>
</div>
<p class="cms-hint">Different business objects: the Package ID identifies the package; an Offer Code (OF-0001…) identifies a promotion with its own sequence. Top Tour rank (Advanced tab) is internal merchandising, not an offer.</p>
<?php if (!$all) { echo empty_state('No offers exist yet', 'Create an offer in the Offer Zone, then link it to this package.', $manage ? '<a class="cms-btn cms-btn--navy" href="/offers/new?package=' . (int) $p['package_pk'] . '">' . icon('plus') . 'Create offer</a>' : ''); } else { ?>
<div class="cms-tablewrap" role="region" aria-label="Offers" tabindex="0">
<table class="cms-table">
    <thead><tr><th scope="col">Linked</th><th scope="col">Offer Code</th><th scope="col">Offer</th><th scope="col">Discount</th><th scope="col">Validity</th><th scope="col">Status</th></tr></thead>
    <tbody>
    <?php foreach ($all as $o) { $exp = $o['valid_until'] < today(); ?>
        <tr>
            <td data-label="Linked"><?= field_check('offer_pk[]', 'Link ' . $o['offer_code'], in_array((int) $o['offer_pk'], $linked, true), (string) $o['offer_pk'], array('disabled' => !$manage)) ?></td>
            <td data-label="Code"><a class="cms-code cms-code--offer" href="/offers/<?= e($o['offer_code']) ?>"><?= e($o['offer_code']) ?></a></td>
            <td data-label="Offer"><?= e($o['name']) ?></td>
            <td data-label="Discount"><?= $o['offer_type'] === 'percentage' ? (int) $o['discount_value'] . '%' : ($o['offer_type'] === 'flat' ? e(inr($o['discount_value'])) : 'Value-add') ?></td>
            <td data-label="Validity"><?= e(dmy($o['valid_from'])) ?> – <?= e(dmy($o['valid_until'])) ?><?= $exp ? ' <span class="cms-warntext">expired</span>' : '' ?></td>
            <td data-label="Status"><?= status_pill($o['status']) ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>
<?php if ($manage) { ?><p><a class="cms-btn cms-btn--ghost cms-btn--sm" href="/offers/new?package=<?= (int) $p['package_pk'] ?>"><?= icon('plus') ?>Create a new offer</a></p><?php } } ?>
<?php echo card('Offers for this package', ob_get_clean(), array('sub' => 'Expired offers are never shown on the website.'));
