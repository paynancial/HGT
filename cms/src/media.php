<?php
/**
 * Media library: upload validation, optimised variants, crop, URLs.
 * Files live outside the web root (storage/uploads) and are served by the CMS after login.
 * Existing website images are referenced in place ('site:' paths), never copied.
 */

const HG_MEDIA_TYPES = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/avif' => 'avif');
const HG_MEDIA_MIN = array(800, 450);     // smallest accepted dimensions
const HG_VARIANT_WIDTHS = array(1600, 800, 400);

function media_abs($rel)
{
    if (strpos($rel, 'site:') === 0) return site_path(substr($rel, 5));
    if (strpos($rel, 'upload:') === 0) return rtrim(cms_config('upload_dir'), '/') . '/' . substr($rel, 7);
    return null;
}

/** URL of a media file (or a variant width) through the CMS file route. */
function media_url(array $m, $width = null)
{
    $path = $m['file_path'];
    if ($width) {
        $v = jd($m['variants']);
        if (isset($v[$width])) $path = $v[$width];
    }
    return '/file?p=' . rawurlencode($path);
}

/** Validate an uploaded file. Returns [mime, width, height] or throws with a message for the user. */
function media_validate($tmp, $name, $bytes, $isUpload = true)
{
    if ($isUpload && !is_uploaded_file($tmp) && PHP_SAPI !== 'cli') throw new RuntimeException('Upload failed. Try again.');
    if ($bytes <= 0) throw new RuntimeException('The file is empty.');
    $max = (int) cms_config('upload_max_bytes');
    if ($bytes > $max) throw new RuntimeException(sprintf('The file is %.1f MB. The limit is %d MB.', $bytes / 1048576, $max / 1048576));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset(HG_MEDIA_TYPES[$mime])) throw new RuntimeException('Only JPG, JPEG, PNG, WEBP and AVIF images are accepted.');
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'avif'), true)) throw new RuntimeException('The file name must end in .jpg, .jpeg, .png, .webp or .avif.');
    $info = @getimagesize($tmp);
    if (!$info) throw new RuntimeException('The file is not a readable image.');
    list($w, $h) = $info;
    if ($w < HG_MEDIA_MIN[0] || $h < HG_MEDIA_MIN[1]) throw new RuntimeException(sprintf('The image is %d×%d px. Use at least %d×%d px (hero images: 1920×1080 or larger).', $w, $h, HG_MEDIA_MIN[0], HG_MEDIA_MIN[1]));
    return array($mime, $w, $h);
}

function gd_open($abs, $mime)
{
    switch ($mime) {
        case 'image/jpeg': return @imagecreatefromjpeg($abs);
        case 'image/png': return @imagecreatefrompng($abs);
        case 'image/webp': return @imagecreatefromwebp($abs);
        case 'image/avif': return function_exists('imagecreatefromavif') ? @imagecreatefromavif($abs) : false;
    }
    return false;
}

/** Create WebP variants at the standard widths (only downscales). Returns ['1600' => 'upload:…', …]. */
function media_variants($rel, $mime)
{
    $abs = media_abs($rel);
    $img = gd_open($abs, $mime);
    if (!$img || !function_exists('imagewebp')) return array();
    $w = imagesx($img); $h = imagesy($img);
    $out = array();
    $base = preg_replace('/\.[a-z0-9]+$/i', '', $rel);
    foreach (HG_VARIANT_WIDTHS as $vw) {
        if ($vw >= $w) continue;
        $vh = (int) round($h * $vw / $w);
        $dst = imagecreatetruecolor($vw, $vh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $vw, $vh, $w, $h);
        $vrel = $base . '-' . $vw . '.webp';
        imagewebp($dst, media_abs($vrel), 82);
        imagedestroy($dst);
        $out[(string) $vw] = $vrel;
    }
    imagedestroy($img);
    return $out;
}

/** Store an upload in the library. Returns media_id. */
function media_store($tmp, $name, $bytes, array $meta, $isUpload = true)
{
    list($mime, $w, $h) = media_validate($tmp, $name, $bytes, $isUpload);
    $dir = gmdate('Y/m');
    $root = rtrim(cms_config('upload_dir'), '/');
    if (!is_dir("$root/$dir") && !mkdir("$root/$dir", 0775, true)) throw new RuntimeException('The upload folder is not writable.');
    $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(pathinfo($name, PATHINFO_FILENAME))), '-') ?: 'image';
    $rel = 'upload:' . $dir . '/' . substr($slug, 0, 60) . '-' . bin2hex(random_bytes(4)) . '.' . HG_MEDIA_TYPES[$mime];
    $ok = ($isUpload && PHP_SAPI !== 'cli') ? move_uploaded_file($tmp, media_abs($rel)) : copy($tmp, media_abs($rel));
    if (!$ok) throw new RuntimeException('Could not save the file.');
    $variants = media_variants($rel, $mime);
    q('INSERT INTO media(file_path, mime, width, height, bytes, variants, alt_text, caption, title, credit, destination, created_at, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', array(
        $rel, $mime, $w, $h, $bytes, je($variants), $meta['alt_text'] ?? '', $meta['caption'] ?? '', $meta['title'] ?? '', $meta['credit'] ?? '', $meta['destination'] ?? '', now(), uid(),
    ));
    return (int) cms_db()->lastInsertId();
}

/** Crop to a preset aspect ratio (centre crop). Creates a NEW media item; the original is kept. */
function media_crop($mediaId, $ratio)
{
    $ratios = array('16:9' => 16 / 9, '4:3' => 4 / 3, '1:1' => 1.0, '3:4' => 3 / 4);
    if (!isset($ratios[$ratio])) throw new RuntimeException('Unknown crop ratio.');
    $m = q1('SELECT * FROM media WHERE media_id = ?', array($mediaId));
    if (!$m) throw new RuntimeException('Image not found.');
    $img = gd_open(media_abs($m['file_path']), $m['mime']);
    if (!$img) throw new RuntimeException('This image format cannot be cropped on this server.');
    $w = imagesx($img); $h = imagesy($img); $r = $ratios[$ratio];
    if ($w / $h > $r) { $cw = (int) round($h * $r); $ch = $h; } else { $cw = $w; $ch = (int) round($w / $r); }
    $dst = imagecreatetruecolor($cw, $ch);
    imagecopy($dst, $img, 0, 0, (int) (($w - $cw) / 2), (int) (($h - $ch) / 2), $cw, $ch);
    $tmp = tempnam(sys_get_temp_dir(), 'crop');
    imagewebp($dst, $tmp, 86);
    imagedestroy($dst); imagedestroy($img);
    $name = preg_replace('/\.[a-z0-9]+$/i', '', basename(substr($m['file_path'], strpos($m['file_path'], ':') + 1))) . '-' . str_replace(':', 'x', $ratio) . '.webp';
    $meta = array('alt_text' => $m['alt_text'], 'caption' => $m['caption'], 'title' => $m['title'], 'credit' => $m['credit'], 'destination' => $m['destination']);
    if ($cw < HG_MEDIA_MIN[0] || $ch < HG_MEDIA_MIN[1]) { @unlink($tmp); throw new RuntimeException('The cropped image would be smaller than the minimum size.'); }
    $id = media_store($tmp, $name, filesize($tmp), $meta, false);
    @unlink($tmp);
    return $id;
}
