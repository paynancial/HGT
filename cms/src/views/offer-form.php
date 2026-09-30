<?php
$old = isset($_SESSION['old']) ? $_SESSION['old'] : array();
unset($_SESSION['old']);
$v = function ($k, $d = '') use ($o, $old) { return isset($old[$k]) && is_string($old[$k]) ? $old[$k] : ($o && isset($o[$k]) ? (string) $o[$k] : $d); };
$manage = hg_can(role(), 'offers', 'manage');
$eDest = $o ? jd($o['eligible_destinations']) : array();
$eType = $o ? jd($o['eligible_package_types']) : array();
$code = $o ? $o['offer_code'] : null;
ob_start(); ?>
<form method="post" action="<?= $code ? '/offers/' . e($code) : '/offers/new' ?>" novalidate>
<?= csrf_field() ?>
<fieldset class="cms-fieldset"<?= $manage ? '' : ' disabled' ?>>
<div class="cms-editor">
    <div class="cms-editor__main">
        <?php ob_start(); ?>
        <div class="cms-form-grid cms-form-grid--3">
            <div class="cms-field"><span class="cms-label">Offer Code</span><p class="cms-readonly"><?= icon('lock') ?><?= $code ? '<b class="cms-code cms-code--offer">' . e($code) . '</b>' : 'Assigned on save (next in the Offer Code sequence)' ?></p></div>
            <?= field_text('name', 'Offer name', $v('name'), array('required' => true, 'class' => 'cms-span2')) ?>
            <?= field_select('offer_type', 'Offer type', $v('offer_type', 'percentage'), array('percentage' => 'Percentage discount', 'flat' => 'Flat discount (₹)', 'value-add' => 'Value-add (no discount)')) ?>
            <?= field_text('discount_value', 'Discount', $v('discount_value'), array('inputmode' => 'numeric', 'hint' => '% for percentage, ₹ for flat')) ?>
            <?= field_select('status', 'Status', $v('status', 'draft'), array('draft' => 'Draft', 'published' => 'Published', 'paused' => 'Paused', 'archived' => 'Archived')) ?>
            <?= field_text('valid_from', 'Valid from', $v('valid_from'), array('type' => 'date', 'required' => true)) ?>
            <?= field_text('valid_until', 'Valid until', $v('valid_until'), array('type' => 'date', 'required' => true)) ?>
            <?= field_text('min_value', 'Minimum booking value (₹)', $v('min_value'), array('inputmode' => 'numeric')) ?>
            <?= field_text('usage_limit', 'Usage limit', $v('usage_limit'), array('type' => 'number', 'min' => 1)) ?>
            <?= field_text('customer_eligibility', 'Customer eligibility', $v('customer_eligibility'), array('class' => 'cms-span2', 'placeholder' => 'e.g. New customers; bookings of 2+ adults')) ?>
            <?= field_text('terms', 'Terms', $v('terms'), array('type' => 'textarea', 'rows' => 4, 'class' => 'cms-span3', 'required' => true)) ?>
        </div>
        <?php echo card('Offer', ob_get_clean()); ?>

        <?php ob_start(); ?>
        <fieldset class="cms-chips"><legend>Eligible destinations</legend>
            <?php foreach (hg_destinations() as $k => $d) echo field_check('eligible_destinations[]', $d['name'], in_array($k, $eDest, true), $k); ?>
        </fieldset>
        <fieldset class="cms-chips"><legend>Eligible package types</legend>
            <?php foreach (HG_PACKAGE_TYPES as $t) echo field_check('eligible_package_types[]', $t, in_array($t, $eType, true), $t); ?>
        </fieldset>
        <div class="cms-field"><label for="f-pk">Eligible packages</label>
            <select id="f-pk" name="packages[]" multiple size="10" class="cms-multi">
            <?php foreach ($pkgs as $p) { list($id) = pkg_id_state($p); ?><option value="<?= (int) $p['package_pk'] ?>"<?= in_array((int) $p['package_pk'], $linked, true) ? ' selected' : '' ?>><?= $id !== '' ? e($id) . ' · ' : '' ?><?= e($p['name']) ?></option><?php } ?>
            </select>
            <small class="cms-hint">Ctrl/⌘-click to select several. Numbers shown are Package IDs (proposed until approved).</small>
        </div>
        <?php echo card('Eligibility', ob_get_clean()); ?>
    </div>
    <aside class="cms-editor__rail">
        <?php ob_start(); ?>
        <p class="cms-hint">Published offers show on the website only between their dates; expired offers are hidden automatically.</p>
        <?php if ($manage) { ?><button class="cms-btn cms-btn--primary cms-btn--block" type="submit"><?= icon('save') ?>Save offer</button><?php } ?>
        <?php echo card('Save', ob_get_clean()); ?>
    </aside>
</div>
</fieldset>
</form>
<?php
$content = ob_get_clean();
$t = $code ? $code . ' · ' . $o['name'] : 'Create offer';
cms_render('layout', array('title' => $t, 'active' => 'offers', 'crumbs' => array(array('Dashboard', '/'), array('Offer Zone', '/offers'), array($code ?: 'Create offer', null)),
    'head' => page_head($code ? 'Edit offer' : 'Create offer', 'Offer Codes are promotional identifiers — never a Package ID, never the Top Tour rank.', '', $o ? status_pill($o['status']) : ''), 'content' => $content));
