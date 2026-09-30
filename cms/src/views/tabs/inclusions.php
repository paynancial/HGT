<?php
/** Tab 5 — Inclusions & Exclusions (Screen 8). */
$icons = array('' => 'None', 'bed' => 'Bed', 'meal' => 'Meal', 'car' => 'Car', 'map' => 'Map pin', 'ticket' => 'Ticket', 'user' => 'Person', 'check' => 'Check');
$idx = 0;
$row = function ($s) use (&$idx, $icons) {
    $i = $idx++;
    $std = (int) $s['is_standard'];
    ob_start(); ?>
    <li class="cms-scope<?= $std ? ' is-standard' : '' ?><?= $s['status'] === 'hidden' ? ' is-hidden' : '' ?>" data-cms-sort-item>
        <input type="hidden" name="items[<?= $i ?>][item_pk]" value="<?= (int) $s['item_pk'] ?>">
        <input type="hidden" name="items[<?= $i ?>][kind]" value="<?= e($s['kind']) ?>">
        <div class="cms-scope__grid">
            <?php if ($std) { ?>
                <div class="cms-field"><span class="cms-label">Item</span><p class="cms-readonly"><?= icon('lock') ?><?= e($s['name']) ?> <span class="cms-pill cms-pill--std">Standard rule</span></p></div>
                <?= field_select("items[$i][status]", 'Show', $s['status'], array('active' => 'Shown (excluded)', 'hidden' => 'Hidden — package includes it'), array('id' => "it$i-st", 'hint' => 'Hide only when an inclusion states this ticket is included.')) ?>
                <input type="hidden" name="items[<?= $i ?>][category]" value="Travel">
            <?php } else { ?>
                <?= field_select("items[$i][category]", 'Category', $s['category'], HG_SCOPE_CATEGORIES, array('id' => "it$i-cat")) ?>
                <?= field_text("items[$i][name]", 'Name', $s['name'], array('id' => "it$i-name", 'required' => true, 'class' => 'cms-span2')) ?>
                <?= field_text("items[$i][description]", 'Description', $s['description'], array('id' => "it$i-desc", 'class' => 'cms-span2')) ?>
                <?= field_select("items[$i][icon]", 'Icon', $s['icon'], $icons, array('id' => "it$i-icon")) ?>
                <?= field_select("items[$i][status]", 'Status', $s['status'], array('active' => 'Active', 'hidden' => 'Hidden'), array('id' => "it$i-st")) ?>
            <?php } ?>
        </div>
        <div class="cms-scope__act">
            <button class="cms-btn cms-btn--ghost cms-btn--sm" type="button" data-cms-sort="-1" aria-label="Move up"><?= icon('up') ?></button>
            <button class="cms-btn cms-btn--ghost cms-btn--sm" type="button" data-cms-sort="1" aria-label="Move down"><?= icon('down') ?></button>
            <?php if (!$std) { ?><button class="cms-btn cms-btn--ghost cms-btn--sm cms-btn--danger" type="submit" name="op" value="del:<?= $i ?>" aria-label="Remove <?= e($s['name']) ?>"><?= icon('trash') ?></button><?php } ?>
        </div>
    </li>
    <?php return ob_get_clean();
};
$inc = array_filter($p['scope'], function ($s) { return $s['kind'] === 'inclusion'; });
$exc = array_filter($p['scope'], function ($s) { return $s['kind'] === 'exclusion'; });
usort($exc, function ($a, $b) { return $b['is_standard'] - $a['is_standard'] ?: $a['sort_order'] - $b['sort_order']; });
$imported = array_filter($p['scope'], function ($s) { return $s['category'] === 'Imported'; });

ob_start(); ?>
<?php if ($imported) { ?><p class="cms-flash cms-flash--info" role="status"><?= icon('info') ?>Items marked “Imported” were copied as-is from the current website page. Some are payment terms rather than inclusions — recategorise, reword or remove them.</p><?php } ?>
<ul class="cms-scopelist" data-cms-sortable><?php foreach ($inc as $s) echo $row($s); ?></ul>
<?php if (!$inc) echo '<p class="cms-muted">No inclusions yet — required for publishing (e.g. accommodation, breakfast, local transfers, sightseeing, driver, parking).</p>'; ?>
<button class="cms-btn cms-btn--outline" type="submit" name="op" value="add:inclusion"><?= icon('plus') ?>Add Inclusion</button>
<?php echo card('What’s included', ob_get_clean()); ?>

<?php ob_start(); ?>
<p class="cms-travelnote"><?= icon('ticket') ?><span><?= e(HG_STANDARD_STATEMENT) ?></span></p>
<ul class="cms-scopelist" data-cms-sortable><?php foreach ($exc as $s) echo $row($s); ?></ul>
<button class="cms-btn cms-btn--outline" type="submit" name="op" value="add:exclusion"><?= icon('plus') ?>Add Exclusion</button>
<?php echo card('What’s excluded', ob_get_clean(), array('sub' => 'Airfare, train fare and bus fare are standard exclusions unless the package explicitly includes them.'));
