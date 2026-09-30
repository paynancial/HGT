<?php
/** Screen 15 — CRM Enquiry Detail. */
$addons = jd($e['addons']);
$utm = jd($e['utm']);
$manage = hg_can(role(), 'enquiries', 'manage');
$stale = $e['rate_version'] && (!$rateNow || (int) $rateNow['version'] !== (int) $e['rate_version']);
ob_start(); ?>
<?php if ($e['is_test']) { ?><p class="cms-flash cms-flash--info" role="note"><?= icon('info') ?>Test record created on staging — not a real customer.</p><?php } ?>
<div class="cms-editor">
    <div class="cms-editor__main">
        <?php ob_start(); ?>
        <dl class="cms-dl cms-dl--2">
            <div><dt>Name</dt><dd><?= e($e['name']) ?></dd></div>
            <div><dt>Phone</dt><dd><?= $e['phone'] ? '<a href="tel:' . e(preg_replace('/[^\d+]/', '', $e['phone'])) . '">' . e($e['phone']) . '</a>' : '—' ?></dd></div>
            <div><dt>Email</dt><dd><?= $e['email'] ? '<a href="mailto:' . e($e['email']) . '">' . e($e['email']) . '</a>' : '—' ?></dd></div>
            <div><dt>Travel date</dt><dd><?= e($e['travel_date'] ? dmy($e['travel_date']) : '—') ?></dd></div>
            <div><dt>Travellers</dt><dd><?= $e['adults'] !== null ? (int) $e['adults'] . ' adult' . ((int) $e['adults'] === 1 ? '' : 's') : '—' ?><?= $e['children'] ? ', ' . (int) $e['children'] . ' child' . ((int) $e['children'] === 1 ? '' : 'ren') : '' ?></dd></div>
            <div><dt>Departure city</dt><dd><?= e($e['departure_city'] ?: '—') ?></dd></div>
        </dl>
        <?php if ($e['message'] !== '') { ?><p class="cms-quoteblock"><?= nl2br(e($e['message'])) ?></p><?php } ?>
        <?php echo card('Traveller', ob_get_clean(), array('actions' => $e['phone'] ? '<a class="cms-btn cms-btn--ghost cms-btn--sm" href="https://wa.me/' . e(preg_replace('/\D/', '', $e['phone'])) . '" target="_blank" rel="noopener">WhatsApp</a>' : '')); ?>

        <?php ob_start(); ?>
        <dl class="cms-dl cms-dl--2">
            <div><dt>Enquiry type</dt><dd><?= e($e['enquiry_type']) ?></dd></div>
            <div><dt>Package ID</dt><dd><?= $e['package_id'] ? '<b class="cms-code">' . e($e['package_id']) . '</b>' : '<span class="cms-muted">Not assigned yet</span>' ?></dd></div>
            <div><dt>Package</dt><dd><?= $pkg ? '<a href="/packages/' . (int) $pkg['package_pk'] . '">' . e($e['package_name']) . '</a>' : e($e['package_name'] ?: '—') ?></dd></div>
            <div><dt>Destination</dt><dd><?= e($e['destination'] ?: '—') ?></dd></div>
            <div><dt>Displayed rate</dt><dd><?= e($e['displayed_rate'] ?: '—') ?></dd></div>
            <div><dt>Price version</dt><dd><?= $e['rate_version'] ? 'v' . (int) $e['rate_version'] : '—' ?><?= $stale ? ' <span class="cms-warntext">rate has changed since — quote the current version</span>' : '' ?></dd></div>
            <div><dt>Rate validity</dt><dd><?= e($e['rate_validity'] ?: '—') ?></dd></div>
            <div><dt>Offer Code</dt><dd><?= $e['offer_code'] ? '<a class="cms-code cms-code--offer" href="/offers/' . e($e['offer_code']) . '">' . e($e['offer_code']) . '</a>' : '—' ?></dd></div>
            <div><dt>Selected add-ons</dt><dd><?= $addons ? e(implode(', ', array_map(function ($a) { return $a['name']; }, $addons))) : '—' ?></dd></div>
            <div><dt>Internal ref</dt><dd><code><?= e($e['internal_ref'] ?: '—') ?></code></dd></div>
            <div class="cms-span2"><dt>Package URL</dt><dd><?= $e['package_url'] ? '<span class="cms-break">' . e($e['package_url']) . '</span>' : '—' ?></dd></div>
            <div class="cms-span2"><dt>Source / UTM</dt><dd><?= e(ucfirst($e['source'])) ?><?= $utm ? ' · ' . e(implode(' · ', array_map(function ($k, $v) { return $k . '=' . $v; }, array_keys($utm), $utm))) : '' ?></dd></div>
        </dl>
        <?php echo card('Package context', ob_get_clean(), array('sub' => 'Captured from the package at enquiry time. Kept unchanged for quotation and payment traceability.')); ?>

        <?php ob_start(); ?>
        <ol class="cms-pipeline">
            <?php foreach (array('Package ID', 'Enquiry', 'Lead', 'Quotation', 'Payment', 'Booking') as $i => $s) { ?>
                <li class="<?= $i < 2 ? 'is-done' : ($i === 2 && in_array($e['stage'], array('qualified', 'quoted', 'won'), true) ? 'is-done' : ($i === 3 && in_array($e['stage'], array('quoted', 'won'), true) ? 'is-done' : '')) ?>"><?= e($s) ?><?= $i >= 3 ? '<small>Planned module</small>' : '' ?></li>
            <?php } ?>
        </ol>
        <p class="cms-hint">Quotations, payments and bookings will carry package_id, price_version and offer_code from this enquiry.</p>
        <?php echo card('CRM journey', ob_get_clean()); ?>
    </div>
    <aside class="cms-editor__rail">
        <?php ob_start(); ?>
        <form method="post" action="/enquiries/<?= (int) $e['enquiry_pk'] ?>">
            <?= csrf_field() ?>
            <fieldset class="cms-fieldset"<?= $manage ? '' : ' disabled' ?>>
                <?= field_select('stage', 'Stage', $e['stage'], HG_STAGES) ?>
                <?php $u = array('' => 'Unassigned'); foreach ($users as $x) $u[$x['user_id']] = $x['name']; ?>
                <?= field_select('assigned_to', 'Owner', (string) $e['assigned_to'], $u) ?>
                <?= field_text('note', 'Add a note', '', array('type' => 'textarea', 'rows' => 3, 'placeholder' => 'Call outcome, follow-up date…')) ?>
                <button class="cms-btn cms-btn--navy cms-btn--block" type="submit"><?= icon('save') ?>Update enquiry</button>
            </fieldset>
        </form>
        <?php echo card('Follow-up', ob_get_clean()); ?>
        <?php ob_start(); ?>
        <?php if (!$notes) echo '<p class="cms-muted">No notes yet.</p>'; ?>
        <ul class="cms-feed"><?php foreach ($notes as $n) { ?><li><span class="cms-feed__t"><?= e(dmy($n['at'])) ?></span><span><?= nl2br(e($n['note'])) ?> <span class="cms-muted">— <?= e($n['uname']) ?></span></span></li><?php } ?></ul>
        <?php echo card('Notes', ob_get_clean()); ?>
    </aside>
</div>
<?php
$content = ob_get_clean();
cms_render('layout', array('title' => 'Enquiry #' . $e['enquiry_pk'], 'active' => 'enquiries', 'crumbs' => array(array('Dashboard', '/'), array('Enquiries', '/enquiries'), array('#' . $e['enquiry_pk'], null)),
    'head' => page_head('Enquiry #' . $e['enquiry_pk'], 'Received ' . dmy($e['created_at']) . ' · ' . ucfirst($e['source']), '', '<span class="cms-pill cms-pill--stage-' . e($e['stage']) . '">' . e(HG_STAGES[$e['stage']]) . '</span>' . ($e['owner'] ? ' <span class="cms-muted cms-small">Owner: ' . e($e['owner']) . '</span>' : '')), 'content' => $content));
