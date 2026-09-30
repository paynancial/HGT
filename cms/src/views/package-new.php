<?php
$dests = hg_destinations();
$countries = array(); $regions = array();
foreach ($dests as $k => $d) { $countries[$d['country']] = $d['country']; $regions[$d['country'] . '|' . $d['region']] = $d['region']; }
$sel = $v['destination'] && isset($dests[$v['destination']]) ? $dests[$v['destination']] : array('country' => 'India', 'region' => '');
$err = function ($k) use ($errors) { return isset($errors[$k]) ? '<p class="cms-err" id="err-' . $k . '">' . e($errors[$k]) . '</p>' : ''; };
ob_start(); ?>
<form method="post" action="/packages/new" class="cms-newpkg" novalidate>
    <?= csrf_field() ?>
    <?php if ($errors) { ?><div class="cms-flash cms-flash--err" role="alert">Please correct the highlighted fields.</div><?php } ?>
    <div class="cms-editor">
        <div class="cms-editor__main">
            <?php ob_start(); ?>
            <div class="cms-form-grid">
                <div class="cms-field cms-span2"><label>Package ID</label><p class="cms-readonly"><?= icon('lock') ?>Pending approval — assigned after owner approval, then permanent and read-only.</p></div>
                <?= field_text('name', 'Package name', $v['name'], array('required' => true, 'maxlength' => 120, 'class' => 'cms-span2' . (isset($errors['name']) ? ' has-err' : ''), 'placeholder' => 'e.g. Srinagar Gulmarg Pahalgam Tour', 'hint' => 'The customer-facing name. Changing it later never changes the Package ID.')) ?>
                <?= $err('name') ?>
            </div>
            <?php $b1 = ob_get_clean(); echo card('Package identity', $b1); ?>

            <?php ob_start(); ?>
            <div class="cms-form-grid cms-form-grid--3" data-cms-dest>
                <?= field_select('country', 'Country', $sel['country'], array_values($countries), array('data' => 'data-cms-dest-country', 'id' => 'f-country')) ?>
                <div class="cms-field"><label for="f-region">Region</label><select id="f-region" data-cms-dest-region>
                    <?php foreach ($regions as $key => $r) { list($c) = explode('|', $key); ?><option value="<?= e($r) ?>" data-country="<?= e($c) ?>"<?= $r === $sel['region'] && $c === $sel['country'] ? ' selected' : '' ?>><?= e($r) ?></option><?php } ?>
                </select></div>
                <div class="cms-field<?= isset($errors['destination']) ? ' has-err' : '' ?>"><label for="f-destination">Destination <span class="cms-req" aria-hidden="true">*</span></label><select id="f-destination" name="destination" required data-cms-dest-dest>
                    <option value="">— Select —</option>
                    <?php foreach ($dests as $k => $d) { ?><option value="<?= e($k) ?>" data-country="<?= e($d['country']) ?>" data-region="<?= e($d['region']) ?>"<?= $k === $v['destination'] ? ' selected' : '' ?>><?= e($d['name']) ?></option><?php } ?>
                </select></div>
                <?= $err('destination') ?>
                <?= field_text('city_route', 'City / Route', $v['city_route'], array('class' => 'cms-span3', 'placeholder' => 'e.g. Srinagar – Gulmarg – Pahalgam', 'hint' => 'Cities in travel order, separated by “–”.')) ?>
            </div>
            <details class="cms-disclose"><summary><?= icon('plus') ?>Add New Destination</summary><p>New destinations follow the controlled CMS workflow: an Admin creates the destination (country, region, content and SEO) in <strong>Destinations</strong>, and it becomes selectable here once approved. This prevents thin or duplicate destination pages.</p></details>
            <?php echo card('Destination', ob_get_clean(), array('sub' => 'Country → Region → Destination → City / Route')); ?>

            <?php ob_start(); ?>
            <div class="cms-form-grid cms-form-grid--3">
                <?= field_select('package_type', 'Package type', $v['package_type'], array_merge(array(''), HG_PACKAGE_TYPES), array('required' => true, 'class' => isset($errors['package_type']) ? 'has-err' : '')) ?>
                <?= field_text('days', 'Days', $v['days'], array('type' => 'number', 'min' => 1, 'max' => 60, 'required' => true, 'inputmode' => 'numeric', 'class' => isset($errors['days']) ? 'has-err' : '')) ?>
                <?= field_text('nights', 'Nights', $v['nights'], array('type' => 'number', 'min' => 0, 'max' => 60, 'required' => true, 'inputmode' => 'numeric', 'class' => isset($errors['nights']) ? 'has-err' : '')) ?>
                <?= $err('package_type') . $err('days') . $err('nights') ?>
            </div>
            <?php echo card('Type & duration', ob_get_clean(), array('sub' => 'The itinerary is checked against this duration before publishing.')); ?>
        </div>
        <aside class="cms-editor__rail">
            <?php ob_start(); ?>
            <ol class="cms-steps">
                <li class="is-current">Create the draft</li>
                <li>Itinerary, images, pricing</li>
                <li>Inclusions, add-ons, SEO &amp; AEO</li>
                <li>Review → approval → publish</li>
            </ol>
            <p class="cms-hint">Airfare, train fare and bus fare are added as standard exclusions automatically.</p>
            <button class="cms-btn cms-btn--primary cms-btn--block" type="submit"><?= icon('save') ?>Create draft</button>
            <?php echo card('Next steps', ob_get_clean()); ?>
        </aside>
    </div>
</form>
<?php
$content = ob_get_clean();
cms_render('layout', array('title' => 'Add New Package', 'active' => 'new', 'crumbs' => array(array('Dashboard', '/'), array('Tour Packages', '/packages'), array('Add New Tour', null)),
    'head' => page_head('Add / Edit Tour Package', 'Create and manage complete tour package information.', '', status_pill('draft') . ' <span class="cms-pid cms-pid--pending"><span class="cms-pid__k">Package ID</span> <small>Pending approval</small></span>'), 'content' => $content));
