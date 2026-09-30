<?php
/** Tab 3 — Images & Media (Screen 6). Package images reference central Media Library assets. */
$pk = (int) $p['package_pk'];
$roles = array('featured' => 'Featured image', 'gallery' => 'Gallery', 'itinerary' => 'Itinerary images', 'hotel' => 'Hotel images', 'activity' => 'Activity images');
$by = array_fill_keys(array_keys($roles), array());
foreach ($p['media'] as $m) $by[$m['role']][] = $m;
$canMedia = hg_can(role(), 'media', 'manage') && $p['status'] !== 'archived';
$lib = $canMedia ? q("SELECT media_id, file_path, alt_text FROM media WHERE status = 'active' AND media_id NOT IN (SELECT media_id FROM package_media WHERE package_pk = ?) ORDER BY media_id DESC LIMIT 200", array($pk))->fetchAll() : array();
$F = ' form="media-actions"';
ob_start(); ?>
<?php if ($canMedia) { ?>
<div class="cms-drop" data-cms-drop>
    <?= icon('upload') ?>
    <p><strong>Drag &amp; drop images here</strong> or <label class="cms-link" for="m-files">choose files</label></p>
    <input id="m-files" type="file" name="files[]" multiple accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif"<?= $F ?> class="cms-drop__input" aria-describedby="m-files-h">
    <p class="cms-hint" id="m-files-h">JPG, JPEG, PNG, WEBP or AVIF · up to <?= (int) (cms_config('upload_max_bytes') / 1048576) ?> MB · at least <?= HG_MEDIA_MIN[0] ?>×<?= HG_MEDIA_MIN[1] ?> px · hero images 1920×1080 or larger. Optimised WebP sizes (1600, 800, 400 px) are generated automatically.</p>
    <div class="cms-drop__row">
        <label for="m-role">Add as</label>
        <select id="m-role" name="upload_role"<?= $F ?>><?php foreach ($roles as $k => $l) { ?><option value="<?= $k ?>"<?= $k === 'gallery' ? ' selected' : '' ?>><?= e($l) ?></option><?php } ?></select>
        <label for="m-alt">Alt text</label>
        <input id="m-alt" name="upload_alt"<?= $F ?> placeholder="Describe what the image shows" maxlength="160">
        <button class="cms-btn cms-btn--navy" type="submit" name="op" value="upload"<?= $F ?>><?= icon('upload') ?>Upload</button>
    </div>
    <p class="cms-drop__files" data-cms-drop-files aria-live="polite"></p>
</div>
<?php if ($lib) { ?>
<div class="cms-inline-form">
    <label for="m-lib">Use an image already in the Media Library</label>
    <select id="m-lib" name="attach_media"<?= $F ?>><?php foreach ($lib as $m) { ?><option value="<?= (int) $m['media_id'] ?>">#<?= (int) $m['media_id'] ?> · <?= e($m['alt_text'] ?: basename(substr($m['file_path'], strpos($m['file_path'], ':') + 1))) ?></option><?php } ?></select>
    <select name="attach_role" aria-label="Use as"<?= $F ?>><?php foreach ($roles as $k => $l) { ?><option value="<?= $k ?>"<?= $k === 'gallery' ? ' selected' : '' ?>><?= e($l) ?></option><?php } ?></select>
    <button class="cms-btn cms-btn--ghost" type="submit" name="op" value="attach"<?= $F ?>>Add</button>
</div>
<?php } } ?>
<?php echo card('Upload', ob_get_clean(), array('sub' => 'Images are stored once in the Media Library and referenced by packages — no duplicate files.'));

foreach ($roles as $role => $label) {
    ob_start();
    if (!$by[$role]) { echo '<p class="cms-muted">' . ($role === 'featured' ? 'No featured image yet — required for publishing (card, hero and OG image).' : 'None yet.') . '</p>'; }
    else { ?>
    <ul class="cms-media" data-cms-sortable>
    <?php foreach ($by[$role] as $m) { $name = basename(substr($m['file_path'], strpos($m['file_path'], ':') + 1)); $mid = (int) $m['media_id']; ?>
        <li class="cms-media__item" data-cms-sort-item>
            <input type="hidden" name="order[]" value="<?= (int) $m['pm_id'] ?>">
            <div class="cms-media__thumb">
                <img src="<?= e(media_url($m, 400)) ?>" alt="" loading="lazy" width="200" height="130">
                <?php if ($role === 'featured') { ?><span class="cms-media__badge"><?= icon('star') ?>Featured</span><?php } ?>
                <?= trim($m['alt_text']) === '' ? '<span class="cms-media__warn">Alt text missing</span>' : '' ?>
            </div>
            <p class="cms-media__file"><span title="<?= e($name) ?>"><?= e($name) ?></span><small><?= (int) $m['width'] ?>×<?= (int) $m['height'] ?> · <?= e(strtoupper(HG_MEDIA_TYPES[$m['mime']] ?? pathinfo($name, PATHINFO_EXTENSION))) ?> · <?= $m['bytes'] ? number_format($m['bytes'] / 1024) . ' KB' : '' ?><?= strpos($m['file_path'], 'site:') === 0 ? ' · website file' : '' ?><?= $m['width'] && $m['width'] < 1920 && $role === 'featured' ? ' · <span class="cms-warntext">below 1920 px hero guidance</span>' : '' ?></small></p>
            <div class="cms-form-grid cms-form-grid--tight">
                <?= field_text("media[$mid][alt_text]", 'Alt text', $m['alt_text'], array('id' => "m$mid-{$m['pm_id']}-alt", 'maxlength' => 160, 'class' => 'cms-span2', 'hint' => 'Describe the scene naturally. No keyword lists.')) ?>
                <?= field_text("media[$mid][caption]", 'Caption', $m['caption'], array('id' => "m$mid-{$m['pm_id']}-cap")) ?>
                <?= field_text("media[$mid][title]", 'Title', $m['title'], array('id' => "m$mid-{$m['pm_id']}-title")) ?>
                <?= field_text("media[$mid][credit]", 'Image credit', $m['credit'], array('id' => "m$mid-{$m['pm_id']}-cred", 'class' => 'cms-span2', 'placeholder' => 'Photographer / source')) ?>
            </div>
            <?php if ($canMedia) { ?>
            <div class="cms-media__actions">
                <?php if ($role !== 'featured') { ?><button class="cms-btn cms-btn--ghost cms-btn--sm" type="submit" name="op" value="featured:<?= (int) $m['pm_id'] ?>"<?= $F ?>><?= icon('star') ?>Set featured</button><?php } ?>
                <a class="cms-btn cms-btn--ghost cms-btn--sm" href="<?= e(media_url($m)) ?>" target="_blank" rel="noopener"><?= icon('eye') ?>Preview</a>
                <span class="cms-media__crop"><label class="cms-sr" for="crop-<?= (int) $m['pm_id'] ?>">Crop ratio</label><select id="crop-<?= (int) $m['pm_id'] ?>" name="crop_ratio[<?= (int) $m['pm_id'] ?>]"<?= $F ?>><option>16:9</option><option>4:3</option><option>1:1</option><option>3:4</option></select><button class="cms-btn cms-btn--ghost cms-btn--sm" type="submit" name="op" value="crop:<?= (int) $m['pm_id'] ?>"<?= $F ?>><?= icon('crop') ?>Crop</button></span>
                <span class="cms-media__replace"><label class="cms-btn cms-btn--ghost cms-btn--sm" for="rep-<?= (int) $m['pm_id'] ?>"><?= icon('upload') ?>Replace</label><input class="cms-sr" id="rep-<?= (int) $m['pm_id'] ?>" type="file" name="replace[<?= (int) $m['pm_id'] ?>]" accept=".jpg,.jpeg,.png,.webp,.avif"<?= $F ?> data-cms-autosubmit="replace:<?= (int) $m['pm_id'] ?>"></span>
                <button class="cms-btn cms-btn--ghost cms-btn--sm" type="button" data-cms-sort="-1" aria-label="Move earlier"><?= icon('up') ?></button>
                <button class="cms-btn cms-btn--ghost cms-btn--sm" type="button" data-cms-sort="1" aria-label="Move later"><?= icon('down') ?></button>
                <button class="cms-btn cms-btn--ghost cms-btn--sm cms-btn--danger" type="submit" name="op" value="remove:<?= (int) $m['pm_id'] ?>"<?= $F ?> data-cms-confirm="Remove this image from the package? It stays in the Media Library."><?= icon('trash') ?>Remove</button>
            </div>
            <?php } ?>
        </li>
    <?php } ?>
    </ul>
    <?php }
    echo card($label, ob_get_clean(), array('sub' => $role === 'gallery' ? 'At least 3 recommended. Drag or use the arrows to reorder, then Save Draft.' : ''));
}
$afterForm = '<form id="media-actions" method="post" action="/packages/' . $pk . '/media" enctype="multipart/form-data">' . csrf_field() . '</form>';
