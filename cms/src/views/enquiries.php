<?php
ob_start(); ?>
<form class="cms-filters" method="get" action="/enquiries" role="search" aria-label="Filter enquiries">
    <div class="cms-field cms-field--grow"><label for="eq-q">Search</label><input id="eq-q" name="q" type="search" value="<?= e($q) ?>" placeholder="Name, phone, email, Package ID or OF-code"></div>
    <?= field_select('stage', 'Stage', $stage, array('' => 'All stages') + HG_STAGES, array('id' => 'eq-stage')) ?>
    <button class="cms-btn cms-btn--navy" type="submit"><?= icon('search') ?>Apply</button>
</form>
<?php if (!$rows) { echo empty_state('No enquiries', 'Website enquiries currently arrive by email (mail.php). They appear here once the website → CRM intake is connected. Staff can log phone and WhatsApp enquiries now.', hg_can(role(), 'enquiries', 'manage') ? '<a class="cms-btn cms-btn--navy" href="/enquiries/new">' . icon('plus') . 'Log enquiry</a>' : ''); } else { ?>
<div class="cms-tablewrap" role="region" aria-label="Enquiries" tabindex="0">
<table class="cms-table">
    <thead><tr><th scope="col">#</th><th scope="col">Received</th><th scope="col">Traveller</th><th scope="col">Package</th><th scope="col">Source</th><th scope="col">Stage</th><th scope="col">Owner</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $x) { ?>
        <tr>
            <td data-label="#"><a href="/enquiries/<?= (int) $x['enquiry_pk'] ?>">#<?= (int) $x['enquiry_pk'] ?></a><?= $x['is_test'] ? ' <span class="cms-pill cms-pill--test">Test</span>' : '' ?></td>
            <td data-label="Received"><?= e(dmy($x['created_at'])) ?></td>
            <td data-label="Traveller"><a class="cms-strong" href="/enquiries/<?= (int) $x['enquiry_pk'] ?>"><?= e($x['name']) ?></a></td>
            <td data-label="Package"><?= $x['package_id'] ? '<span class="cms-code">' . e($x['package_id']) . '</span> ' : '' ?><?= e($x['package_name'] ?: '—') ?></td>
            <td data-label="Source"><?= e(ucfirst($x['source'])) ?></td>
            <td data-label="Stage"><span class="cms-pill cms-pill--stage-<?= e($x['stage']) ?>"><?= e(HG_STAGES[$x['stage']]) ?></span></td>
            <td data-label="Owner"><?= e($x['owner'] ?: 'Unassigned') ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>
<?php } ?>
<?php
$content = ob_get_clean();
$right = hg_can(role(), 'enquiries', 'manage') ? '<a class="cms-btn cms-btn--primary" href="/enquiries/new">' . icon('plus') . 'Log enquiry</a>' : '';
cms_render('layout', array('title' => 'Enquiries', 'active' => 'enquiries', 'crumbs' => array(array('Dashboard', '/'), array('CRM', null), array('Enquiries', null)),
    'head' => page_head('Enquiries', 'Tour Package Enquiries — Package ID → Enquiry → Lead → Quotation → Payment → Booking.', $right), 'content' => $content));
