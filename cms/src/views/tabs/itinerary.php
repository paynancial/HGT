<?php
/** Tab 2 — Itinerary (Screen 5). The itinerary belongs to the package and carries its Package ID. */
$dur = pkg_duration_issue($p);
list($pid, $pidState) = pkg_id_state($p);
$images = array_filter($p['media'], function ($m) { return in_array($m['role'], array('itinerary', 'gallery', 'featured'), true); });
ob_start(); ?>
<dl class="cms-ithead">
    <div><dt>Package ID</dt><dd><?= $pidState === 'approved' ? '<b class="cms-code">' . e($pid) . '</b>' : ($pidState === 'proposed' ? '<span class="cms-code cms-code--proposed">' . e($pid) . '</span> <small>proposed</small>' : '<small>Pending approval</small>') ?></dd></div>
    <div><dt>Package</dt><dd><?= e($p['name']) ?></dd></div>
    <div><dt>Duration</dt><dd><?= (int) $p['days'] ?> Days / <?= (int) $p['nights'] ?> Nights</dd></div>
    <div><dt>Itinerary</dt><dd data-cms-daycount><?= count($p['days_list']) ?> days</dd></div>
</dl>
<?php if ($dur) { ?>
<div class="cms-flash cms-flash--<?= $p['duration_override'] ? 'info' : 'warn' ?>" role="alert"><?= icon('alert') ?><strong>WARNING — <?= e($dur) ?></strong> Correct the days or record an explicit override.</div>
<?php } ?>
<?php if ($dur || $p['duration_override']) {
    echo field_text('duration_override', 'Duration override reason', $p['duration_override'], array('disabled' => !hg_may(role(), 'override_duration'), 'placeholder' => 'e.g. Day 6 is the departure morning; shown for clarity', 'hint' => 'Only Super Admin, Admin and Package Manager can override. Leave empty to require a correction.'));
} else { ?><input type="hidden" name="duration_override" value=""><?php } ?>

<ol class="cms-days" data-cms-days>
<?php foreach ($p['days_list'] as $i => $d) { $n = $i + 1; ?>
    <li class="cms-day" data-cms-day draggable="false">
        <details<?= $i < 2 || $d['description'] === '' ? ' open' : '' ?>>
            <summary class="cms-day__head">
                <span class="cms-day__grip" data-cms-grip title="Drag to reorder" aria-hidden="true"><?= icon('grip') ?></span>
                <span class="cms-day__n" data-cms-dayn>Day <?= $n ?></span>
                <span class="cms-day__t"><?= e($d['title'] ?: 'Untitled day') ?></span>
                <?= $d['description'] === '' ? '<span class="cms-pill cms-pill--draft">Needs details</span>' : '' ?>
            </summary>
            <div class="cms-day__body">
                <div class="cms-form-grid">
                    <?= field_text("days[$i][title]", 'Day title', $d['title'], array('class' => 'cms-span2', 'required' => true, 'id' => "d$i-title")) ?>
                    <?= field_text("days[$i][destination]", 'Destination', $d['destination'], array('id' => "d$i-dest")) ?>
                    <?= field_text("days[$i][route]", 'Route', $d['route'], array('id' => "d$i-route", 'placeholder' => 'e.g. Srinagar → Gulmarg (56 km)')) ?>
                    <?= field_text("days[$i][description]", 'Description', $d['description'], array('type' => 'textarea', 'rows' => 4, 'class' => 'cms-span2', 'id' => "d$i-desc")) ?>
                    <?= field_text("days[$i][sightseeing]", 'Sightseeing', $d['sightseeing'], array('id' => "d$i-sight")) ?>
                    <?= field_text("days[$i][activities]", 'Activities', $d['activities'], array('id' => "d$i-act")) ?>
                    <?= field_text("days[$i][meals]", 'Meals', $d['meals'], array('id' => "d$i-meals", 'placeholder' => 'e.g. Breakfast, Dinner')) ?>
                    <?= field_text("days[$i][hotel]", 'Hotel', $d['hotel'], array('id' => "d$i-hotel", 'placeholder' => 'Only a confirmed hotel or category')) ?>
                    <?= field_text("days[$i][transport]", 'Transport', $d['transport'], array('id' => "d$i-tr")) ?>
                    <?= field_text("days[$i][optional_activities]", 'Optional activities', $d['optional_activities'], array('id' => "d$i-opt")) ?>
                    <?= field_text("days[$i][notes]", 'Important notes', $d['notes'], array('type' => 'textarea', 'rows' => 2, 'class' => 'cms-span2', 'id' => "d$i-notes")) ?>
                    <?php $opts = array('' => 'No image'); foreach ($images as $m) $opts[$m['media_id']] = ($m['alt_text'] ?: basename($m['file_path'])); ?>
                    <?= field_select("days[$i][media_id]", 'Image', (string) $d['media_id'], $opts, array('id' => "d$i-img", 'hint' => 'From this package’s images (upload in Images & Media).')) ?>
                </div>
                <div class="cms-day__actions">
                    <button type="button" class="cms-btn cms-btn--ghost cms-btn--sm" data-cms-move="-1"><?= icon('up') ?>Move up</button>
                    <button type="button" class="cms-btn cms-btn--ghost cms-btn--sm" data-cms-move="1"><?= icon('down') ?>Move down</button>
                    <button type="submit" class="cms-btn cms-btn--ghost cms-btn--sm" name="op" value="dup:<?= $i ?>"><?= icon('copy') ?>Duplicate day</button>
                    <button type="submit" class="cms-btn cms-btn--ghost cms-btn--sm cms-btn--danger" name="op" value="del:<?= $i ?>" data-cms-confirm="Delete Day <?= $n ?>?"><?= icon('trash') ?>Delete day</button>
                </div>
            </div>
        </details>
    </li>
<?php } ?>
</ol>
<?php if (!$p['days_list']) echo empty_state('No itinerary yet', 'Add Day 1 to start the day-wise plan.'); ?>
<button type="submit" class="cms-btn cms-btn--outline" name="op" value="add"><?= icon('plus') ?>Add Day</button>
<?php echo card('Day-wise itinerary', ob_get_clean(), array('sub' => 'Linked to the package (Package ID). Changing the itinerary never changes the Package ID. Every save is a new version.'));
