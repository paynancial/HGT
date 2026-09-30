<?php
/** Package images (upload, attach, featured, reorder, crop, replace, remove), Media Library, file serving. */

function package_media_post($pk)
{
    need('media', 'manage');
    $p = pkg_row($pk);
    if (!$p || $p['status'] === 'archived') deny('Package not found or archived.');
    $op = post('op');
    $back = '/packages/' . (int) $pk . '?tab=media';
    $roles = array('featured', 'gallery', 'itinerary', 'hotel', 'activity');
    $attach = function ($mid, $role) use ($pk) {
        if ($role === 'featured') q("UPDATE package_media SET role = 'gallery' WHERE package_pk = ? AND role = 'featured'", array($pk));
        $ord = (int) qv('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM package_media WHERE package_pk = ? AND role = ?', array($pk, $role));
        q('INSERT INTO package_media(package_pk, media_id, role, sort_order) VALUES (?,?,?,?)', array($pk, $mid, $role, $ord));
    };
    $pm = function ($id) use ($pk) { return q1('SELECT * FROM package_media WHERE pm_id = ? AND package_pk = ?', array((int) $id, $pk)); };
    try {
        if ($op === 'upload') {
            $role = in_array(post('upload_role'), $roles, true) ? post('upload_role') : 'gallery';
            $f = isset($_FILES['files']) ? $_FILES['files'] : null;
            if (!$f || !is_array($f['name']) || !array_filter($f['name'], 'strlen')) throw new RuntimeException('Choose at least one image to upload.');
            $done = 0; $errs = array();
            foreach ($f['name'] as $i => $name) {
                if ($name === '') continue;
                if ($f['error'][$i] !== UPLOAD_ERR_OK) { $errs[] = $name . ': ' . ($f['error'][$i] === UPLOAD_ERR_INI_SIZE || $f['error'][$i] === UPLOAD_ERR_FORM_SIZE ? 'file too large' : 'upload failed'); continue; }
                try {
                    $mid = media_store($f['tmp_name'][$i], $name, (int) $f['size'][$i], array('alt_text' => mb_substr(post('upload_alt'), 0, 160), 'destination' => $p['destination']));
                    $attach($mid, $done === 0 ? $role : ($role === 'featured' ? 'gallery' : $role));
                    cms_log('Image uploaded', $pk, $role, '', $name);
                    $done++;
                } catch (RuntimeException $x) { $errs[] = $name . ': ' . $x->getMessage(); }
            }
            if ($done) flash($done . ' image' . ($done === 1 ? '' : 's') . ' uploaded.' . (post('upload_alt') === '' ? ' Add alt text before publishing.' : ''));
            foreach ($errs as $x) flash($x, 'err');
        } elseif ($op === 'attach') {
            $mid = (int) post('attach_media');
            if (!qv("SELECT 1 FROM media WHERE media_id = ? AND status = 'active'", array($mid))) throw new RuntimeException('Image not found.');
            $role = in_array(post('attach_role'), $roles, true) ? post('attach_role') : 'gallery';
            $attach($mid, $role);
            cms_log('Image added from library', $pk, $role, '', 'media ' . $mid);
            flash('Image added from the Media Library.');
        } elseif (preg_match('/^featured:(\d+)$/', $op, $m) && ($row = $pm($m[1]))) {
            q("UPDATE package_media SET role = 'gallery' WHERE package_pk = ? AND role = 'featured'", array($pk));
            q("UPDATE package_media SET role = 'featured', sort_order = 0 WHERE pm_id = ?", array($row['pm_id']));
            cms_log('Featured image set', $pk, 'featured', '', 'media ' . $row['media_id']);
            flash('Featured image updated.');
        } elseif (preg_match('/^remove:(\d+)$/', $op, $m) && ($row = $pm($m[1]))) {
            q('DELETE FROM package_media WHERE pm_id = ?', array($row['pm_id']));
            q('UPDATE itinerary_days SET media_id = NULL WHERE package_pk = ? AND media_id = ?', array($pk, $row['media_id']));
            cms_log('Image removed', $pk, $row['role'], 'media ' . $row['media_id'], '');
            flash('Image removed from this package (kept in the Media Library).');
        } elseif (preg_match('/^crop:(\d+)$/', $op, $m) && ($row = $pm($m[1]))) {
            $ratio = isset($_POST['crop_ratio'][$row['pm_id']]) ? (string) $_POST['crop_ratio'][$row['pm_id']] : '16:9';
            $new = media_crop($row['media_id'], $ratio);
            q('UPDATE package_media SET media_id = ? WHERE pm_id = ?', array($new, $row['pm_id']));
            cms_log('Image cropped', $pk, $row['role'], 'media ' . $row['media_id'], 'media ' . $new . ' (' . $ratio . ')');
            flash('Cropped to ' . $ratio . '. The original stays in the Media Library.');
        } elseif (preg_match('/^replace:(\d+)$/', $op, $m) && ($row = $pm($m[1]))) {
            $f = isset($_FILES['replace']) ? $_FILES['replace'] : null;
            $k = $row['pm_id'];
            if (!$f || empty($f['name'][$k]) || $f['error'][$k] !== UPLOAD_ERR_OK) throw new RuntimeException('Choose the replacement image.');
            $old = q1('SELECT * FROM media WHERE media_id = ?', array($row['media_id']));
            $new = media_store($f['tmp_name'][$k], $f['name'][$k], (int) $f['size'][$k], array('alt_text' => $old['alt_text'], 'caption' => $old['caption'], 'title' => $old['title'], 'credit' => $old['credit'], 'destination' => $old['destination']));
            q('UPDATE package_media SET media_id = ? WHERE pm_id = ?', array($new, $row['pm_id']));
            q('UPDATE itinerary_days SET media_id = ? WHERE package_pk = ? AND media_id = ?', array($new, $pk, $row['media_id']));
            cms_log('Image replaced', $pk, $row['role'], 'media ' . $row['media_id'], 'media ' . $new);
            flash('Image replaced. Alt text and caption were carried over — check they still describe the new image.');
        } else {
            throw new RuntimeException('Unknown action.');
        }
    } catch (RuntimeException $x) {
        flash($x->getMessage(), 'err');
        redirect($back);
    }
    pkg_snapshot($pk, 'media', 'Images changed');
    redirect($back);
}

/** Media Library (central assets). */
function media_library()
{
    need('media');
    $q = get('q');
    $args = array(); $where = "m.status = 'active'";
    if ($q !== '') { $where .= ' AND (m.alt_text LIKE ? OR m.file_path LIKE ? OR m.destination LIKE ?)'; $l = '%' . $q . '%'; $args = array($l, $l, $l); }
    if (get('missing') === 'alt') $where .= " AND TRIM(m.alt_text) = ''";
    $rows = q("SELECT m.*, (SELECT COUNT(*) FROM package_media pm WHERE pm.media_id = m.media_id) AS uses,
        (SELECT GROUP_CONCAT(p.name, ' | ') FROM package_media pm JOIN packages p ON p.package_pk = pm.package_pk WHERE pm.media_id = m.media_id) AS used_by
        FROM media m WHERE $where ORDER BY m.media_id DESC LIMIT 200", $args)->fetchAll();
    cms_render('media-library', array('rows' => $rows, 'q' => $q));
}

function media_update($mid)
{
    need('media', 'manage');
    $m = q1('SELECT * FROM media WHERE media_id = ?', array($mid));
    if (!$m) deny('Image not found.');
    $alt = mb_substr(post('alt_text'), 0, 160);
    q('UPDATE media SET alt_text = ?, caption = ?, credit = ? WHERE media_id = ?', array($alt, post('caption'), post('credit'), $mid));
    cms_log('Image details updated', null, 'media ' . $mid, $m['alt_text'], $alt);
    flash('Image details saved.');
    redirect('/media' . (get('q') ? '?q=' . rawurlencode(get('q')) : ''));
}

/** Serve a library file (uploads live outside the web root). */
function serve_file()
{
    $p = get('p');
    if (!preg_match('~^(site|upload):[A-Za-z0-9/_\-.]+$~', $p) || strpos($p, '..') !== false) { http_response_code(404); return; }
    $abs = media_abs($p);
    $base = realpath(strpos($p, 'site:') === 0 ? cms_config('site_root') . '/assets' : cms_config('upload_dir'));
    $real = $abs ? realpath($abs) : false;
    if (!$real || !$base || strpos($real, $base . '/') !== 0 || !is_file($real)) { http_response_code(404); return; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($real);
    if (!in_array($mime, array('image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'), true)) { http_response_code(404); return; }
    header('Content-Type: ' . $mime);
    header('Cache-Control: private, max-age=600');
    header('Content-Length: ' . filesize($real));
    readfile($real);
}
