<?php
/**
 * Make responsive WebP (and optionally AVIF) variants of the site's photos, for hg_img()'s <picture>.
 *
 *   php tools/build_images.php [public_html/assets/img] [--avif] [--dry-run]
 *
 * For every .jpg/.jpeg/.png it writes {name}-480.webp, {name}-960.webp, {name}-1600.webp next to the
 * original (only widths smaller than the original; never upscales). Existing variants newer than the
 * original are skipped, so it is safe to re-run. The original file is never changed or removed.
 * hg_img() picks the variants up automatically; pages without variants keep the original <img>.
 * Needs PHP GD with WebP (and AVIF for --avif).
 */
$args = array_slice($argv, 1);
$dir = realpath(isset($args[0]) && $args[0][0] !== '-' ? $args[0] : __DIR__ . '/../public_html/assets/img');
$avif = in_array('--avif', $args, true);
$dry = in_array('--dry-run', $args, true);
if (!$dir || !is_dir($dir)) { fwrite(STDERR, "Image folder not found.\n"); exit(1); }
if (!function_exists('imagewebp')) { fwrite(STDERR, "PHP GD without WebP support.\n"); exit(1); }
if ($avif && !function_exists('imageavif')) { fwrite(STDERR, "PHP GD without AVIF support; run without --avif.\n"); exit(1); }

$widths = array(480, 960, 1600);
$made = 0; $skipped = 0; $failed = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    $path = $f->getPathname();
    if (!preg_match('/\.(jpe?g|png)$/i', $path) || preg_match('/-(480|960|1600)\.[a-z]+$/i', $path)) continue;
    $info = @getimagesize($path);
    if (!$info) { $failed++; echo "skip (unreadable): $path\n"; continue; }
    $src = null;
    foreach ($widths as $w) {
        if ($w >= $info[0]) continue;
        foreach (array_merge(array('webp'), $avif ? array('avif') : array()) as $fmt) {
            $out = preg_replace('/\.[a-z]+$/i', '', $path) . '-' . $w . '.' . $fmt;
            if (is_file($out) && filemtime($out) >= filemtime($path)) { $skipped++; continue; }
            if ($dry) { echo "would write $out\n"; $made++; continue; }
            if (!$src) $src = $info[2] === IMAGETYPE_PNG ? imagecreatefrompng($path) : imagecreatefromjpeg($path);
            if (!$src) { $failed++; continue 3; }
            $h = (int) round($info[1] * $w / $info[0]);
            $dst = imagecreatetruecolor($w, $h);
            imagealphablending($dst, false); imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);
            $ok = $fmt === 'webp' ? imagewebp($dst, $out, 80) : imageavif($dst, $out, 55, 6);
            imagedestroy($dst);
            $ok ? $made++ : $failed++;
        }
    }
    if ($src) imagedestroy($src);
}
echo "variants written: $made, up to date: $skipped, failed: $failed\n";
exit($failed ? 1 : 0);
