<?php
/** Tab 1 — Basic Information (Screen 4). */
$dests = hg_destinations();
$countries = array(); $regions = array();
foreach ($dests as $k => $d) { $countries[$d['country']] = $d['country']; $regions[$d['country'] . '|' . $d['region']] = $d['region']; }
list($pid, $pidState) = pkg_id_state($p);
$dur = pkg_duration_issue($p);
ob_start(); ?>
<div class="cms-form-grid">
    <div class="cms-field">
        <span class="cms-label">Package ID</span>
        <p class="cms-readonly"><?= icon('lock') ?><?php if ($pidState === 'approved') { ?><b class="cms-code"><?= e($pid) ?></b> Permanent · read-only<?php } elseif ($pidState === 'proposed') { ?><b class="cms-code cms-code--proposed"><?= e($pid) ?></b> Proposed · pending owner approval<?php } else { ?>Pending approval<?php } ?></p>
        <small class="cms-hint">Auto-assigned and read-only. The only package identifier: itinerary, CRM, quotations and payments.</small>
    </div>
    <div class="cms-field">
        <span class="cms-label">Status</span>
        <p class="cms-readonly"><?= status_pill($p['status']) ?> <span class="cms-muted">Changed through the workflow</span></p>
    </div>
    <?= field_text('name', 'Package name', $val('name', $p['name']), array('required' => true, 'maxlength' => 120, 'class' => 'cms-span2', 'hint' => 'Changing the name never changes the Package ID.')) ?>
</div>
<?php echo card('Package identity', ob_get_clean()); ?>

<?php ob_start(); ?>
<div class="cms-form-grid cms-form-grid--3" data-cms-dest>
    <?= field_select('country', 'Country', $p['country'], array_values($countries), array('data' => 'data-cms-dest-country', 'id' => 'f-country')) ?>
    <div class="cms-field"><label for="f-region">Region</label><select id="f-region" data-cms-dest-region>
        <?php foreach ($regions as $key => $r) { list($c) = explode('|', $key); ?><option value="<?= e($r) ?>" data-country="<?= e($c) ?>"<?= $r === $p['region'] && $c === $p['country'] ? ' selected' : '' ?>><?= e($r) ?></option><?php } ?>
    </select></div>
    <div class="cms-field"><label for="f-destination">Destination <span class="cms-req" aria-hidden="true">*</span></label><select id="f-destination" name="destination" required data-cms-dest-dest>
        <?php foreach ($dests as $k => $d) { ?><option value="<?= e($k) ?>" data-country="<?= e($d['country']) ?>" data-region="<?= e($d['region']) ?>"<?= $k === $p['destination'] ? ' selected' : '' ?>><?= e($d['name']) ?></option><?php } ?>
    </select></div>
    <?= field_text('city_route', 'City / Route', $val('city_route', $p['city_route']), array('class' => 'cms-span3', 'placeholder' => 'e.g. Srinagar – Gulmarg – Pahalgam')) ?>
</div>
<details class="cms-disclose"><summary><?= icon('plus') ?>Add New Destination</summary><p>New destinations follow the controlled workflow: an Admin creates the destination in <strong>Destinations</strong> (country, region, content, SEO); it becomes selectable here once approved.</p></details>
<?php echo card('Destination', ob_get_clean(), array('sub' => 'Country → Region → Destination → City / Route')); ?>

<?php ob_start(); ?>
<div class="cms-form-grid cms-form-grid--4">
    <?= field_select('package_type', 'Package type', $p['package_type'], array_merge(array(''), HG_PACKAGE_TYPES)) ?>
    <?= field_select('speciality_type', 'Speciality type', $p['speciality_type'], HG_SPECIALITY) ?>
    <?= field_text('days', 'Days', $val('days', $p['days']), array('type' => 'number', 'min' => 1, 'max' => 60, 'required' => true, 'inputmode' => 'numeric')) ?>
    <?= field_text('nights', 'Nights', $val('nights', $p['nights']), array('type' => 'number', 'min' => 0, 'max' => 60, 'required' => true, 'inputmode' => 'numeric')) ?>
</div>
<p class="cms-durline">Duration: <strong data-cms-duration><?= (int) $p['days'] ?> Days / <?= (int) $p['nights'] ?> Nights</strong> · itinerary has <?= count($p['days_list']) ?> day<?= count($p['days_list']) === 1 ? '' : 's' ?></p>
<?php if ($dur) { ?><p class="cms-flash cms-flash--<?= $p['duration_override'] ? 'info' : 'warn' ?>" role="status"><?= icon('alert') ?><strong>WARNING</strong> — <?= e($dur) ?><?= $p['duration_override'] ? ' Override recorded: ' . e($p['duration_override']) : ' <a href="/packages/' . (int) $p['package_pk'] . '?tab=itinerary">Fix the itinerary</a>' ?></p><?php } ?>
<fieldset class="cms-chips">
    <legend>Suitable for</legend>
    <?php foreach (HG_SUITABLE_FOR as $s) echo field_check('suitable_for[]', $s, in_array($s, $p['suitable_for'], true), $s); ?>
</fieldset>
<?php echo card('Type, duration & audience', ob_get_clean()); ?>

<?php ob_start(); ?>
<?= field_text('short_description', 'Short description', $val('short_description', $p['short_description']), array('type' => 'textarea', 'rows' => 3, 'maxlength' => 300, 'counter' => '120-300', 'hint' => 'Aim for 250–300 characters: what the trip is, where it goes, who it suits. Used on cards and search snippets.')) ?>
<div class="cms-field">
    <span class="cms-label" id="desc-l">Detailed description</span>
    <div class="cms-rte" data-cms-rte>
        <div class="cms-rte__bar" role="toolbar" aria-label="Formatting" aria-controls="desc-ed">
            <button type="button" data-cmd="formatBlock" data-val="h2">Heading</button>
            <button type="button" data-cmd="formatBlock" data-val="h3">Subheading</button>
            <button type="button" data-cmd="formatBlock" data-val="p">Paragraph</button>
            <button type="button" data-cmd="insertUnorderedList">• Bullets</button>
            <button type="button" data-cmd="insertOrderedList">1. Numbered</button>
            <button type="button" data-cmd="bold"><b>B</b></button>
            <button type="button" data-cmd="createLink">Link</button>
            <button type="button" data-cmd="table">Table</button>
            <button type="button" data-cmd="callout" data-val="hg-callout">Callout</button>
            <button type="button" data-cmd="callout" data-val="hg-tip">Travel tip</button>
            <button type="button" data-cmd="callout" data-val="hg-note">Important note</button>
            <button type="button" data-cmd="image">Image</button>
        </div>
        <div class="cms-rte__area" id="desc-ed" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="desc-l" aria-describedby="desc-h"><?= $p['description_html'] ?></div>
        <textarea name="description_html" hidden><?= e($p['description_html']) ?></textarea>
    </div>
    <small class="cms-hint" id="desc-h"><span data-cms-words><?= words($p['description_html']) ?></span> words · at least 80 for publishing. Write original traveller value — no filler, no copied text, no keyword stuffing.</small>
</div>
<?= field_text('highlights', 'Highlights (one per line)', $val('highlights', implode("\n", $p['highlights'])), array('type' => 'textarea', 'rows' => 5, 'hint' => 'At least 3 specific highlights, e.g. “Shikara ride on Dal Lake”.')) ?>
<?php echo card('Description & highlights', ob_get_clean(), array('sub' => 'Rich content — the part travellers and search engines read.'));
