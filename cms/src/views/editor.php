<?php
/** Package editor shell: header actions, tab bar, current tab, right rail. Vars: $p, $tab */
$pk = (int) $p['package_pk'];
$states = pkg_tab_states($p);
list($errors, $warnings) = pkg_blockers($p);
$editable = tab_editable($tab, $p);
$old = isset($_SESSION['old']) ? $_SESSION['old'] : null;
unset($_SESSION['old']);
$val = function ($k, $default) use ($old) { return $old !== null && isset($old[$k]) && is_string($old[$k]) ? $old[$k] : $default; };
$canPublish = hg_may(role(), 'publish');
$publishable = !$errors && in_array($p['status'], array('approved', 'published', 'paused'), true);
$unpub = $p['published_version'] && (int) $p['version'] > (int) $p['published_version'];
$rate = pkg_current_rate($p);
$featured = null;
foreach ($p['media'] as $m) if ($m['role'] === 'featured') $featured = $m;

// Header
$meta = '<strong class="cms-pagehead__pkg">' . e($p['name']) . '</strong> ' . status_pill($p['status']) . ' ' . pkg_id_badge($p, true) . ' <span class="cms-muted cms-small">Version ' . (int) $p['version'] . ($unpub ? ' · <span class="cms-unpub">unpublished changes since v' . (int) $p['published_version'] . '</span>' : '') . '</span>';
$wf = function ($action, $label, $cls = 'cms-btn--ghost', $iconName = '', $attrs = '') use ($pk, $tab) {
    return '<form method="post" action="/packages/' . $pk . '/workflow" class="cms-inline">' . csrf_field() . '<input type="hidden" name="action" value="' . e($action) . '"><input type="hidden" name="tab" value="' . e($tab) . '"><button type="submit" class="cms-btn ' . $cls . '"' . $attrs . '>' . ($iconName ? icon($iconName) : '') . e($label) . '</button></form>';
};
$more = '';
if (hg_may(role(), 'submit_review') && in_array($p['status'], array('draft', 'paused'), true)) $more .= $wf('submit', 'Submit for review', 'cms-btn--menu', 'send');
if (hg_may(role(), 'pause') && $p['status'] === 'published') $more .= $wf('pause', 'Pause', 'cms-btn--menu', 'pause');
if (hg_may(role(), 'archive') && $p['status'] !== 'archived') $more .= $wf('archive', 'Archive', 'cms-btn--menu', 'archive', ' data-cms-confirm="Archive this package? It leaves the website and its Package ID stays reserved."');
if (hg_may(role(), 'restore') && $p['status'] === 'archived') $more .= $wf('restore', 'Restore as draft', 'cms-btn--menu', 'restore');
if (hg_may(role(), 'delete') && !$p['published_at'] && $p['package_id_status'] !== 'approved') $more .= $wf('delete', 'Delete draft', 'cms-btn--menu cms-btn--danger', 'trash', ' data-cms-confirm="Delete this draft permanently? This cannot be undone."');
$actions = '<a class="cms-btn cms-btn--ghost" href="/packages/' . $pk . '/preview" target="_blank" rel="noopener">' . icon('eye') . 'Preview Package</a>';
if ($editable) $actions .= '<button class="cms-btn cms-btn--navy" type="submit" form="tab-form">' . icon('save') . 'Save Draft</button>';
if ($canPublish) {
    $why = $errors ? count($errors) . ' mandatory item' . (count($errors) === 1 ? '' : 's') . ' missing' : ($publishable ? '' : 'Needs review sign-offs and approval first');
    $actions .= $publishable ? $wf('publish', $p['status'] === 'published' ? ($unpub ? 'Publish changes' : 'Published') : 'Publish Package', 'cms-btn--primary', 'send', $p['status'] === 'published' && !$unpub ? ' disabled' : '')
        : '<span class="cms-tip"><button type="button" class="cms-btn cms-btn--primary" disabled aria-describedby="pub-why">' . icon('lock') . 'Publish Package</button><span class="cms-tip__text" id="pub-why" role="tooltip">' . e($why) . '</span></span>';
}
if ($more) $actions .= '<details class="cms-more"><summary class="cms-btn cms-btn--ghost" aria-label="More actions">' . icon('more') . '<span class="cms-hide-sm">More</span></summary><div class="cms-more__menu">' . $more . '</div></details>';

ob_start(); ?>
<nav class="cms-tabs" aria-label="Package editor sections">
    <ol>
    <?php $n = 0; foreach (HG_TABS as $k => $t) { $n++; $s = isset($states[$k]) ? $states[$k] : '';
        $mark = $s === 'done' ? icon('check', 'cms-tabs__ok') : ($s === 'todo' ? '<span class="cms-tabs__bad" aria-hidden="true">!</span>' : ($s === 'warn' ? '<span class="cms-tabs__warn" aria-hidden="true">!</span>' : '<span class="cms-tabs__n" aria-hidden="true">' . $n . '</span>'));
        $sr = $s === 'done' ? ' (complete)' : ($s === 'todo' ? ' (needs attention)' : ($s === 'warn' ? ' (check)' : '')); ?>
        <li><a href="/packages/<?= $pk ?>?tab=<?= $k ?>" class="cms-tabs__a<?= $k === $tab ? ' is-active' : '' ?> is-<?= $s ?: 'none' ?>"<?= $k === $tab ? ' aria-current="page"' : '' ?>><?= $mark ?><span><?= e($t[0]) ?></span><span class="cms-sr"><?= $sr ?></span></a></li>
    <?php } ?>
    </ol>
</nav>

<div class="cms-editor">
    <div class="cms-editor__main">
        <?php if ($p['status'] === 'archived') { ?><p class="cms-flash cms-flash--warn" role="status">This package is archived and read-only. Restore it to edit.</p><?php }
        elseif (!$editable && $tab !== 'history') { ?><p class="cms-flash cms-flash--info" role="status">View only: your role (<?= e(HG_ROLES[role()]) ?>) cannot change this section.</p><?php } ?>
        <form id="tab-form" method="post" action="/packages/<?= $pk ?>/save" enctype="application/x-www-form-urlencoded" novalidate<?= $editable ? '' : ' data-readonly' ?>>
            <?= csrf_field() ?>
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <fieldset class="cms-fieldset"<?= $editable ? '' : ' disabled' ?>>
                <legend class="cms-sr"><?= e(HG_TABS[$tab][0]) ?></legend>
                <?php require __DIR__ . '/tabs/' . $tab . '.php'; ?>
            </fieldset>
            <?php if ($editable && $tab !== 'history') {
                $keys = array_keys(HG_TABS); $i = array_search($tab, $keys, true); $next = isset($keys[$i + 1]) ? $keys[$i + 1] : null; ?>
            <div class="cms-formbar">
                <button class="cms-btn cms-btn--navy" type="submit"><?= icon('save') ?>Save Draft</button>
                <?php if ($next && $next !== 'history') { ?><button class="cms-btn cms-btn--ghost" type="submit" name="next_tab" value="<?= e($next) ?>">Save &amp; continue to <?= e(HG_TABS[$next][0]) ?></button><?php } ?>
            </div>
            <?php } ?>
        </form>
        <?= isset($afterForm) ? $afterForm : '' ?>
    </div>

    <aside class="cms-editor__rail" aria-label="Package status and preview">
        <?php // Live preview card — built from the saved package data (reloads after every save).
        ob_start(); ?>
        <div class="cms-mini">
            <div class="cms-mini__img"><?php if ($featured) { ?><img src="<?= e(media_url($featured, 400)) ?>" alt="<?= e($featured['alt_text']) ?>" loading="lazy"><?php } else { ?><span><?= icon('image') ?>No featured image</span><?php } ?></div>
            <p class="cms-mini__name"><?= e($p['name']) ?></p>
            <p class="cms-mini__meta"><?= (int) $p['days'] ?> Days / <?= (int) $p['nights'] ?> Nights<?= $p['city_route'] ? ' · ' . e($p['city_route']) : '' ?></p>
            <p class="cms-mini__rate"><strong><?= e(rate_label($rate)) ?></strong><?php if ($rate) { ?><small>Valid <?= e(rate_validity($rate)) ?> · v<?= (int) $rate['version'] ?></small><?php } ?></p>
            <ul class="cms-mini__counts">
                <li><?= count($p['days_list']) ?> itinerary day<?= count($p['days_list']) === 1 ? '' : 's' ?></li>
                <?php $pl = function ($n, $w) { return $n . ' ' . $w . ($n === 1 ? '' : 's'); }; ?>
                <li><?= $pl(count(array_filter($p['scope'], function ($s) { return $s['kind'] === 'inclusion' && $s['status'] === 'active'; })), 'inclusion') ?> · <?= $pl(count(array_filter($p['scope'], function ($s) { return $s['kind'] === 'exclusion' && $s['status'] === 'active'; })), 'exclusion') ?></li>
                <li><?= $pl(count(array_filter($p['addons'], function ($a) { return $a['status'] === 'active'; })), 'add-on') ?></li>
                <li><?= $p['offers'] ? e(implode(', ', array_map(function ($o) { return $o['offer_code']; }, $p['offers']))) : 'No offer' ?></li>
            </ul>
            <p class="cms-mini__seo"><span class="cms-muted">Search title</span> <?= e($p['seo']['meta_title'] ?: '—') ?></p>
            <?php list($payOk) = pkg_paynow($p); ?>
            <div class="cms-mini__cta"><span class="cms-btn cms-btn--primary cms-btn--sm<?= $payOk ? '' : ' is-disabled' ?>"><?= icon('lock') ?>Pay Now</span><span class="cms-btn cms-btn--navy cms-btn--sm<?= $p['enquiry_enabled'] ? '' : ' is-disabled' ?>">Enquire Now</span></div>
        </div>
        <?php echo card('Live preview', ob_get_clean(), array('actions' => '<a class="cms-btn cms-btn--ghost cms-btn--sm" href="/packages/' . $pk . '/preview" target="_blank" rel="noopener">' . icon('external') . '<span class="cms-sr">Open full preview</span></a>', 'class' => 'cms-card--preview')); ?>

        <?php ob_start(); ?>
        <p class="cms-checksum"><?= $errors ? '<strong class="cms-bad">' . count($errors) . ' mandatory item' . (count($errors) === 1 ? '' : 's') . ' missing</strong> — Publish is blocked; Save Draft still works.' : '<strong class="cms-good">All mandatory items complete.</strong>' ?><?= $warnings ? ' ' . count($warnings) . ' warning' . (count($warnings) === 1 ? '' : 's') . '.' : '' ?></p>
        <?php foreach (pkg_checklist($p) as $group => $items) { ?>
            <p class="cms-checklist__h"><?= e($group) ?></p>
            <ul class="cms-checklist">
            <?php foreach ($items as $i) { $cls = $i['ok'] ? 'ok' : ($i['severity'] === 'error' ? 'bad' : 'warn'); ?>
                <li class="is-<?= $cls ?>"><?= $cls === 'ok' ? icon('check') : icon('alert') ?><a href="/packages/<?= $pk ?>?tab=<?= e($i['tab']) ?>"><?= e($i['label']) ?></a><span class="cms-sr"><?= $cls === 'ok' ? 'complete' : ($cls === 'bad' ? 'missing, blocks publishing' : 'warning') ?></span></li>
            <?php } ?>
            </ul>
        <?php } ?>
        <?php echo card('Publication checklist', ob_get_clean(), array('id' => 'checklist')); ?>

        <?php ob_start(); $so = pkg_signoffs($p); ?>
        <ol class="cms-flow">
            <li class="<?= $p['status'] !== 'draft' ? 'is-done' : 'is-current' ?>">Draft</li>
            <?php foreach (array('content' => 'Content review', 'seo' => 'SEO/AEO review', 'pricing' => 'Pricing review') as $st => $lbl) { $r = $so[$st]; ?>
                <li class="<?= $r && $r['decision'] === 'approved' ? 'is-done' : ($p['status'] === 'in_review' ? 'is-current' : '') ?>"><?= e($lbl) ?><?php if ($r) { ?><small><?= $r['decision'] === 'approved' ? 'Signed off' : 'Changes requested' ?> by <?= e($r['user_name']) ?></small><?php } ?>
                <?php if ($p['status'] === 'in_review' && hg_may(role(), 'review_' . $st) && !($r && $r['decision'] === 'approved')) { ?>
                    <form method="post" action="/packages/<?= $pk ?>/workflow" class="cms-signoff"><?= csrf_field() ?><input type="hidden" name="action" value="review"><input type="hidden" name="stage" value="<?= $st ?>"><input type="hidden" name="tab" value="<?= e($tab) ?>">
                        <label class="cms-sr" for="note-<?= $st ?>">Note</label><input id="note-<?= $st ?>" name="note" placeholder="Note (optional)">
                        <button class="cms-btn cms-btn--sm cms-btn--navy" name="decision" value="approved">Sign off</button>
                        <button class="cms-btn cms-btn--sm cms-btn--ghost" name="decision" value="changes">Request changes</button>
                    </form>
                <?php } ?></li>
            <?php } ?>
            <li class="<?= in_array($p['status'], array('approved', 'published', 'paused'), true) ? 'is-done' : '' ?>">Approval
                <?php if ($p['status'] === 'in_review' && hg_may(role(), 'approve')) echo $wf('approve', 'Approve', 'cms-btn--sm cms-btn--navy'); ?></li>
            <li class="<?= $p['status'] === 'published' ? 'is-done' : '' ?>">Publish<?= $p['published_at'] ? '<small>First published ' . e(dmy($p['published_at'])) . '</small>' : '' ?></li>
        </ol>
        <?php if ($p['status'] === 'draft' && hg_may(role(), 'submit_review')) echo $wf('submit', 'Submit for review', 'cms-btn--navy cms-btn--block', 'send'); ?>
        <?php echo card('Workflow', ob_get_clean(), array('sub' => 'Any change after sign-off needs a fresh review.')); ?>
    </aside>
</div>
<?php
$content = ob_get_clean();
$bottom = '<div class="cms-bottombar" role="toolbar" aria-label="Package actions">'
    . '<a class="cms-btn cms-btn--ghost" href="/packages/' . $pk . '/preview" target="_blank" rel="noopener">' . icon('eye') . 'Preview</a>'
    . ($editable ? '<button class="cms-btn cms-btn--navy" type="submit" form="tab-form">' . icon('save') . 'Save</button>' : '')
    . ($canPublish ? ($publishable && !($p['status'] === 'published' && !$unpub) ? $wf('publish', 'Publish', 'cms-btn--primary', 'send') : '<button class="cms-btn cms-btn--primary" disabled>' . icon('lock') . 'Publish</button>') : '')
    . '<a class="cms-btn cms-btn--ghost" href="#checklist" aria-label="Checklist: ' . count($errors) . ' missing">' . ($errors ? '<span class="cms-badge">' . count($errors) . '</span>' : icon('check')) . '</a>'
    . '</div>';
cms_render('layout', array('title' => $p['name'] . ' — ' . HG_TABS[$tab][0], 'active' => $tab === 'itinerary' ? 'itinerary' : 'packages', 'bodyClass' => 'cms-has-bottombar',
    'crumbs' => array(array('Dashboard', '/'), array('Tour Packages', '/packages'), array($p['name'], null)),
    'head' => page_head('Add / Edit Tour Package', 'Create and manage complete tour package information.', $actions, $meta),
    'content' => $content, 'bottomBar' => $bottom));
