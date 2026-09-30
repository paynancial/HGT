<?php
/** Tab 6 — Add-ons (Screen 9). Every add-on is separately editable and priced. */
$manage = hg_can(role(), 'addons', 'manage');
$rate = pkg_current_rate($p);
ob_start(); ?>
<?php if (!$manage) { ?><p class="cms-flash cms-flash--info" role="status">Your role can suggest add-ons (saved hidden). A Pricing Manager activates them.</p><?php } ?>
<?php if (!$p['addons']) echo empty_state('No add-ons yet', 'Optional upsells such as a hotel upgrade, extra night, private guide or special dinner.'); ?>
<ol class="cms-addons">
<?php foreach ($p['addons'] as $i => $a) { ?>
    <li class="cms-addon<?= $a['status'] === 'hidden' ? ' is-hidden' : '' ?>">
        <input type="hidden" name="addons[<?= $i ?>][addon_pk]" value="<?= (int) $a['addon_pk'] ?>">
        <div class="cms-addon__head"><span class="cms-code">AO-<?= sprintf('%04d', (int) $a['addon_pk']) ?></span><span class="cms-muted cms-small">Add-on ID (internal)</span>
            <span class="cms-pill cms-pill--<?= $a['status'] === 'active' ? 'published' : 'draft' ?>"><?= $a['status'] === 'active' ? 'Active' : 'Hidden' ?></span></div>
        <div class="cms-form-grid cms-form-grid--4">
            <?= field_text("addons[$i][name]", 'Name', $a['name'], array('id' => "a$i-name", 'required' => true, 'class' => 'cms-span2')) ?>
            <?= field_text("addons[$i][price]", 'Price (₹)', $a['price'] === null ? '' : (string) $a['price'], array('id' => "a$i-price", 'inputmode' => 'numeric', 'hint' => 'Empty = price on request')) ?>
            <?= field_select("addons[$i][price_unit]", 'Price unit', $a['price_unit'], array_combine(HG_PRICE_UNITS, array_map('ucfirst', HG_PRICE_UNITS)), array('id' => "a$i-unit")) ?>
            <?= field_text("addons[$i][description]", 'Description', $a['description'], array('id' => "a$i-desc", 'class' => 'cms-span2')) ?>
            <?= field_text("addons[$i][tax_percent]", 'Tax (%)', $a['tax_percent'] === null ? '' : (string) $a['tax_percent'], array('id' => "a$i-tax", 'inputmode' => 'decimal')) ?>
            <?= field_select("addons[$i][availability]", 'Availability', $a['availability'], array('available' => 'Available', 'on request' => 'On request', 'unavailable' => 'Unavailable'), array('id' => "a$i-av", 'hint' => 'Unavailable add-ons can’t be selected by customers.')) ?>
            <?= field_select("addons[$i][status]", 'Status', $a['status'], $manage ? array('active' => 'Active', 'hidden' => 'Hidden') : array('hidden' => 'Hidden (suggested)'), array('id' => "a$i-st")) ?>
            <div class="cms-field cms-field--check"><?= field_check("addons[$i][required]", 'Required (included in every booking)', (bool) $a['required'], '1', array('id' => "a$i-req")) ?></div>
        </div>
        <?php if ($manage) { ?><div class="cms-addon__act"><button class="cms-btn cms-btn--ghost cms-btn--sm cms-btn--danger" type="submit" name="op" value="del:<?= $i ?>" data-cms-confirm="Delete this add-on?"><?= icon('trash') ?>Delete</button></div><?php } ?>
    </li>
<?php } ?>
</ol>
<button class="cms-btn cms-btn--outline" type="submit" name="op" value="add"><?= icon('plus') ?>Add Add-on</button>
<?php echo card('Optional add-ons', ob_get_clean(), array('sub' => 'Per person · per couple · per room · per vehicle · per day · flat fee')); ?>

<?php ob_start();
$avail = array_filter($p['addons'], function ($a) { return $a['status'] === 'active' && $a['availability'] === 'available' && $a['price'] !== null; }); ?>
<p class="cms-hint">How the package page shows it: base price + selected optional add-ons = quote total. Totals are shown only when a current rate exists; otherwise add-ons are listed for the quote.</p>
<div class="cms-quote">
    <p><span>Base price</span><strong><?= e(rate_label($rate)) ?></strong></p>
    <?php foreach ($avail as $a) { ?><p><span>+ <?= e($a['name']) ?></span><strong><?= e(inr($a['price'])) ?> <?= e($a['price_unit']) ?></strong></p><?php } ?>
    <p class="cms-quote__total"><span>Quote total</span><strong><?= $rate ? 'Calculated for the selected add-ons and travellers' : 'Quoted by our team (no current rate)' ?></strong></p>
</div>
<?php echo card('Customer view', ob_get_clean());
