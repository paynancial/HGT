<?php
$manage = hg_can(role(), 'media', 'manage');
ob_start(); ?>
<form class="cms-filters" method="get" action="/media" role="search" aria-label="Filter media">
    <div class="cms-field cms-field--grow"><label for="ml-q">Search</label><input id="ml-q" name="q" type="search" value="<?= e($q) ?>" placeholder="Alt text, file name or destination"></div>
    <?= field_select('missing', 'Show', get('missing'), array('' => 'All images', 'alt' => 'Missing alt text'), array('id' => 'ml-missing')) ?>
    <button class="cms-btn cms-btn--navy" type="submit"><?= icon('search') ?>Apply</button>
</form>
<?php if (!$rows) echo empty_state('No images', 'Upload images from a package’s Images & Media tab.'); ?>
<ul class="cms-media cms-media--lib">
<?php foreach ($rows as $m) { $name = basename(substr($m['file_path'], strpos($m['file_path'], ':') + 1)); ?>
    <li class="cms-media__item">
        <div class="cms-media__thumb"><img src="<?= e(media_url($m, 400)) ?>" alt="" loading="lazy" width="200" height="130"><?= trim($m['alt_text']) === '' ? '<span class="cms-media__warn">Alt text missing</span>' : '' ?></div>
        <p class="cms-media__file"><span title="<?= e($name) ?>">#<?= (int) $m['media_id'] ?> · <?= e($name) ?></span><small><?= (int) $m['width'] ?>×<?= (int) $m['height'] ?> · <?= $m['bytes'] ? number_format($m['bytes'] / 1024) . ' KB' : '' ?> · used by <?= (int) $m['uses'] ?> package<?= (int) $m['uses'] === 1 ? '' : 's' ?></small></p>
        <?php if ($m['used_by']) { ?><p class="cms-small cms-muted cms-clamp"><?= e($m['used_by']) ?></p><?php } ?>
        <form method="post" action="/media/<?= (int) $m['media_id'] ?><?= $q !== '' ? '?q=' . e(rawurlencode($q)) : '' ?>">
            <?= csrf_field() ?>
            <fieldset class="cms-fieldset"<?= $manage ? '' : ' disabled' ?>>
                <?= field_text('alt_text', 'Alt text', $m['alt_text'], array('id' => 'ml' . $m['media_id'] . '-alt', 'maxlength' => 160)) ?>
                <?= field_text('caption', 'Caption', $m['caption'], array('id' => 'ml' . $m['media_id'] . '-cap')) ?>
                <?= field_text('credit', 'Credit', $m['credit'], array('id' => 'ml' . $m['media_id'] . '-cr')) ?>
                <button class="cms-btn cms-btn--ghost cms-btn--sm" type="submit">Save</button>
            </fieldset>
        </form>
    </li>
<?php } ?>
</ul>
<?php
$content = ob_get_clean();
cms_render('layout', array('title' => 'Media Library', 'active' => 'media', 'crumbs' => array(array('Dashboard', '/'), array('Content', null), array('Media Library', null)),
    'head' => page_head('Media Library', 'Central image store. Packages reference these files — one physical file, many uses. Website images are referenced in place.'), 'content' => $content));
