<?php
/** Tab 4 — Pricing & Availability (Screen 7). Manual rates; every change is a new price version. */
$rate = pkg_current_rate($p);
$latest = $p['rates'] ? $p['rates'][0] : null;
$canApprove = hg_may(role(), 'approve_rate');
$lvl = hg_level(role(), 'pricing');
list($payOk, $payWhy) = pkg_paynow($p);
$expired = !$rate && array_filter($p['rates'], function ($r) { return $r['rate_status'] === 'approved' && $r['valid_until'] < today(); });
ob_start(); ?>
<div class="cms-ratebox">
    <div>
        <p class="cms-eyebrow">What the website shows today</p>
        <p class="cms-ratebox__v"><?= e(rate_label($rate)) ?></p>
        <?php if ($rate) { ?><p class="cms-muted">Rate valid <strong><?= e(rate_validity($rate)) ?></strong> · Price version <?= (int) $rate['version'] ?> · updated <?= e(dmy($rate['approved_at'] ?: $rate['created_at'])) ?></p>
        <?php } elseif ($expired) { ?><p class="cms-warntext">The last approved rate has expired, so the website shows “Price on request” / “Get current price” until a new rate is approved.</p>
        <?php } else { ?><p class="cms-muted">No approved rate is valid today. The website shows “Price on request” with “Get current price”.</p><?php } ?>
    </div>
    <div class="cms-ratebox__pay">
        <p class="cms-eyebrow">Pay Now</p>
        <p><?= $payOk ? '<strong class="cms-good">Active</strong>' : '<strong>Disabled (honest staging state)</strong>' ?></p>
        <?php if (!$payOk) { ?><ul class="cms-small"><?php foreach ($payWhy as $w) { ?><li><?= e($w) ?></li><?php } ?></ul><?php } ?>
    </div>
</div>
<p class="cms-travelnote"><?= icon('info') ?><span><strong>Standard rule:</strong> <?= e(HG_STANDARD_STATEMENT) ?> Local transfers are separate from external travel tickets.</span></p>
<?php echo card('Current rate', ob_get_clean()); ?>

<?php if ($lvl >= HG_LEVELS['request']) { ob_start(); ?>
<p class="cms-hint">Saving creates <strong>price version <?= $latest ? (int) $latest['version'] + 1 : 1 ?></strong>. Earlier versions are never edited, so quotations and payments stay linked to the rate they used.</p>
<div class="cms-form-grid cms-form-grid--4">
    <?= field_text('base_price', 'Base price (₹)', $val('base_price', ''), array('inputmode' => 'numeric', 'required' => true, 'placeholder' => 'e.g. 24999')) ?>
    <?= field_select('currency', 'Currency', 'INR', array('INR' => 'INR (₹)'), array('hint' => 'Rupee pricing only.')) ?>
    <?= field_select('price_unit', 'Price unit', $val('price_unit', $latest ? $latest['price_unit'] : 'per person'), HG_PRICE_UNITS, array('required' => true)) ?>
    <div class="cms-field"><span class="cms-label">Rate status</span><p class="cms-readonly"><?= $canApprove ? 'Draft, or approved if ticked below' : 'Draft — needs approval by Pricing' ?></p></div>
    <?= field_text('valid_from', 'Rate valid from', $val('valid_from', ''), array('type' => 'date', 'required' => true, 'min' => today())) ?>
    <?= field_text('valid_until', 'Rate valid until', $val('valid_until', ''), array('type' => 'date', 'required' => true, 'min' => today())) ?>
    <?= field_text('reason', 'Reason for change', $val('reason', ''), array('class' => 'cms-span2', 'required' => (bool) $latest, 'placeholder' => $latest ? 'e.g. Hotel contract rates for Oct 2026' : 'e.g. First published rate')) ?>
    <?= field_text('price_notes', 'Price notes (shown to customers)', $val('price_notes', ''), array('class' => 'cms-span4', 'placeholder' => 'e.g. Based on 2 adults sharing a standard room')) ?>
</div>
<details class="cms-disclose"><summary>Optional price components</summary>
<div class="cms-form-grid cms-form-grid--4">
    <?= field_text('adult_price', 'Adult (₹)', $val('adult_price', ''), array('inputmode' => 'numeric')) ?>
    <?= field_text('child_price', 'Child (₹)', $val('child_price', ''), array('inputmode' => 'numeric')) ?>
    <?= field_text('single_supplement', 'Single supplement (₹)', $val('single_supplement', ''), array('inputmode' => 'numeric')) ?>
    <?= field_text('extra_bed', 'Extra bed (₹)', $val('extra_bed', ''), array('inputmode' => 'numeric')) ?>
    <?= field_text('tax_percent', 'Tax (%)', $val('tax_percent', ''), array('inputmode' => 'decimal', 'placeholder' => 'e.g. 5')) ?>
    <?= field_text('discount', 'Discount (₹)', $val('discount', ''), array('inputmode' => 'numeric')) ?>
    <?= field_text('seasonal_note', 'Seasonal price note', $val('seasonal_note', ''), array('class' => 'cms-span2', 'placeholder' => 'e.g. Peak season surcharge applies 20 Dec – 5 Jan')) ?>
</div>
<?php $addTotal = 0; foreach ($p['addons'] as $a) if ($a['status'] === 'active' && $a['required'] && $a['price']) $addTotal += (int) $a['price']; ?>
<p class="cms-hint">Required add-on total: <?= $addTotal ? e(inr($addTotal)) : '—' ?> (from the Add-ons tab).</p>
</details>
<?php if ($canApprove) echo field_check('approve_now', 'Approve this price version now (Pricing approval)', false); ?>
<?php echo card('New price version', ob_get_clean(), array('sub' => 'Manual CMS pricing. No hard-coded prices.')); } ?>

<?php ob_start(); ?>
<?php if (!$p['rates']) { echo '<p class="cms-muted">No price versions yet.</p>'; } else { ?>
<div class="cms-tablewrap" role="region" aria-label="Price versions" tabindex="0">
<table class="cms-table">
    <thead><tr><th scope="col">Version</th><th scope="col">Rate</th><th scope="col">Valid</th><th scope="col">Status</th><th scope="col">Old rate → change</th><th scope="col">Changed by / date</th><th scope="col">Reason</th><?= $canApprove ? '<th scope="col"><span class="cms-sr">Actions</span></th>' : '' ?></tr></thead>
    <tbody>
    <?php foreach ($p['rates'] as $i => $r) {
        $prev = isset($p['rates'][$i + 1]) ? $p['rates'][$i + 1] : null;
        $who = $r['created_by'] ? qv('SELECT name FROM users WHERE user_id = ?', array($r['created_by'])) : 'import';
        $isCur = $rate && $rate['rate_pk'] === $r['rate_pk'];
        $st = $r['rate_status'] === 'approved' && $r['valid_until'] < today() ? 'expired' : $r['rate_status']; ?>
        <tr<?= $isCur ? ' class="is-current"' : '' ?>>
            <td data-label="Version"><b>v<?= (int) $r['version'] ?></b><?= $isCur ? ' <span class="cms-pill cms-pill--published">Current</span>' : '' ?></td>
            <td data-label="Rate"><?= e(rate_label($r)) ?></td>
            <td data-label="Valid"><?= e(rate_validity($r)) ?></td>
            <td data-label="Status"><span class="cms-pill cms-pill--<?= e($st) ?>"><?= e(ucfirst($st)) ?></span></td>
            <td data-label="Old → new"><?= $prev ? e(inr($prev['base_price'])) . ' → ' . e(inr($r['base_price'])) : '<span class="cms-muted">first version</span>' ?></td>
            <td data-label="Changed"><?= e($who) ?><small class="cms-block cms-muted"><?= e(dmy($r['created_at'])) ?></small></td>
            <td data-label="Reason"><?= e($r['reason'] ?: '—') ?></td>
            <?php if ($canApprove) { ?><td class="cms-table__act">
                <?php if ($r['rate_status'] === 'draft') { ?><button class="cms-btn cms-btn--navy cms-btn--sm" type="submit" name="op" value="approve:<?= (int) $r['rate_pk'] ?>">Approve</button><?php } ?>
                <?php if ($r['rate_status'] !== 'withdrawn') { ?><button class="cms-btn cms-btn--ghost cms-btn--sm" type="submit" name="op" value="withdraw:<?= (int) $r['rate_pk'] ?>" data-cms-confirm="Withdraw price version <?= (int) $r['version'] ?>?">Withdraw</button><?php } ?>
            </td><?php } ?>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>
<?php } ?>
<?php echo card('Price version history', ob_get_clean(), array('sub' => 'Append-only. Expired rates are never shown as current.'));
