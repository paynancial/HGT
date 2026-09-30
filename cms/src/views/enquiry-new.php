<?php
ob_start(); ?>
<form method="post" action="/enquiries/new" novalidate>
<?= csrf_field() ?>
<div class="cms-editor">
    <div class="cms-editor__main">
        <?php ob_start(); ?>
        <div class="cms-form-grid cms-form-grid--3">
            <?= field_text('name', 'Traveller name', '', array('required' => true, 'autocomplete' => 'off')) ?>
            <?= field_text('phone', 'Phone', '', array('type' => 'tel', 'inputmode' => 'tel')) ?>
            <?= field_text('email', 'Email', '', array('type' => 'email')) ?>
            <?= field_select('source', 'Source', 'phone', array('phone' => 'Phone', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'walk-in' => 'Walk-in', 'website' => 'Website')) ?>
            <?= field_text('travel_date', 'Travel date', '', array('type' => 'date')) ?>
            <?= field_text('departure_city', 'Departure city', '') ?>
            <?= field_text('adults', 'Adults', '', array('type' => 'number', 'min' => 0)) ?>
            <?= field_text('children', 'Children', '', array('type' => 'number', 'min' => 0)) ?>
        </div>
        <?php echo card('Traveller', ob_get_clean()); ?>
        <?php ob_start(); ?>
        <div class="cms-form-grid">
            <div class="cms-field cms-span2"><label for="f-pkg">Package</label><select id="f-pkg" name="package_pk"><option value="">No specific package</option>
                <?php foreach ($pkgs as $p) { $pid = pkg_public_id($p); ?><option value="<?= (int) $p['package_pk'] ?>"<?= $pre === (int) $p['package_pk'] ? ' selected' : '' ?>><?= $pid ? e($pid) . ' · ' : '' ?><?= e($p['name']) ?></option><?php } ?>
            </select><small class="cms-hint">Package ID, displayed rate, price version and validity are filled in from the package — not typed.</small></div>
            <?= field_text('offer_code', 'Offer Code (optional)', '', array('placeholder' => 'OF-0001', 'hint' => 'Kept only if it is a live offer for this package.')) ?>
            <?= field_text('message', 'Notes from the traveller', '', array('type' => 'textarea', 'rows' => 3, 'class' => 'cms-span2')) ?>
        </div>
        <?= field_check('is_test', 'This is a test record (staging)', false) ?>
        <?php echo card('Enquiry', ob_get_clean()); ?>
    </div>
    <aside class="cms-editor__rail"><?php echo card('Save', '<button class="cms-btn cms-btn--primary cms-btn--block" type="submit">' . icon('save') . 'Log enquiry</button>'); ?></aside>
</div>
</form>
<?php
$content = ob_get_clean();
cms_render('layout', array('title' => 'Log enquiry', 'active' => 'enquiries', 'crumbs' => array(array('Dashboard', '/'), array('Enquiries', '/enquiries'), array('Log enquiry', null)),
    'head' => page_head('Log enquiry', 'Record a phone, WhatsApp, email or walk-in Tour Package Enquiry.'), 'content' => $content));
