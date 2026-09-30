<?php
/** Tab 9 — Advanced (Screen 12): commercial switches, internal curation, Package ID, danger zone. */
list($payOk, $payWhy) = pkg_paynow($p);
list($pid, $st) = pkg_id_state($p);
$c = $p['curation'];
$canCur = hg_can(role(), 'curation', 'manage');
ob_start(); ?>
<div class="cms-switches">
    <?= field_check('enquiry_enabled', 'Enquire Now enabled — opens the Contact Us form as a “Tour Package Enquiry” with this package’s details', (bool) $p['enquiry_enabled']) ?>
    <?= field_check('payable', 'Package is payable online (Pay Now also needs a current rate and a connected gateway)', (bool) $p['payable']) ?>
    <?= field_check('noindex', 'Hide from search engines (noindex)', (bool) $p['noindex']) ?>
</div>
<p class="cms-hint">Pay Now today: <?= $payOk ? '<strong class="cms-good">active</strong>' : '<strong>disabled</strong> — ' . e(implode('; ', $payWhy)) ?>. Payment success is never simulated.</p>
<?php echo card('Commercial', ob_get_clean()); ?>

<?php ob_start(); ?>
<p class="cms-hint">Internal merchandising only (Top Tour collection). Never shown publicly — the website says “Featured”, “Popular” or “Recommended”, never a count.</p>
<div class="cms-form-grid cms-form-grid--3">
    <?= field_text('priority_rank', 'Priority rank (1–500)', (string) $c['priority_rank'], array('type' => 'number', 'min' => 1, 'max' => 500, 'disabled' => !$canCur, 'hint' => 'Unique. Empty = not in the collection.')) ?>
</div>
<div class="cms-switches cms-switches--grid">
    <?php foreach (array('featured' => 'Featured', 'homepage_featured' => 'Homepage featured', 'search_featured' => 'Search featured', 'seasonal_featured' => 'Seasonal featured', 'speciality_featured' => 'Speciality featured') as $k => $l) echo field_check($k, $l, (bool) $c[$k], '1', array('disabled' => !$canCur)); ?>
</div>
<?php echo card('Top Tour collection', ob_get_clean(), array('sub' => 'Separate from Offer Codes.')); ?>

<?php ob_start(); ?>
<dl class="cms-dl">
    <div><dt>Package ID</dt><dd><?= $pid !== '' ? '<b class="cms-code' . ($st === 'proposed' ? ' cms-code--proposed' : '') . '">' . e($pid) . '</b> ' : '' ?><?= $st === 'approved' ? 'Permanent' : ($st === 'proposed' ? 'Proposed in the migration mapping — pending owner approval' : 'Not numbered yet') ?></dd></div>
    <div><dt>Internal reference</dt><dd><code>slug:<?= e($p['slug']) ?></code></dd></div>
    <div><dt>Source</dt><dd><?= $p['source'] === 'site-import' ? 'Imported from the current website' : 'Created in the CMS' ?> · created <?= e(dmy($p['created_at'])) ?></dd></div>
</dl>
<?php if ($st !== 'approved') { ?>
<p class="cms-hint">Assignment is <?= cms_config('package_id_assignment') ? 'on' : '<strong>off until the owner approves the Package ID mapping</strong>' ?>. Once assigned, the Package ID is permanent, read-only and never reused.</p>
<?php } ?>
<?php echo card('Identity', ob_get_clean());
// The assign button lives outside the tab form (its own form).
if ($st !== 'approved' && hg_may(role(), 'assign_package_id')) {
    $afterForm = '<form method="post" action="/packages/' . (int) $p['package_pk'] . '/workflow" class="cms-card cms-card--pad">' . csrf_field()
        . '<input type="hidden" name="action" value="assign_id"><input type="hidden" name="tab" value="advanced">'
        . '<p><strong>Assign permanent Package ID</strong></p><button class="cms-btn cms-btn--navy" type="submit"' . (cms_config('package_id_assignment') ? ' data-cms-confirm="Assign the permanent Package ID? This cannot be undone."' : ' disabled') . '>' . icon('lock') . 'Assign Package ID</button>'
        . (cms_config('package_id_assignment') ? '' : ' <span class="cms-hint">Waiting for owner approval of the mapping.</span>') . '</form>';
}
