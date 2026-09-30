<?php
/** Route table and dispatcher. */

require_once __DIR__ . '/controllers/auth.php';
require_once __DIR__ . '/controllers/dashboard.php';
require_once __DIR__ . '/controllers/packages.php';
require_once __DIR__ . '/controllers/editor.php';
require_once __DIR__ . '/controllers/media.php';
require_once __DIR__ . '/controllers/offers.php';
require_once __DIR__ . '/controllers/enquiries.php';
require_once __DIR__ . '/controllers/admin.php';
require_once __DIR__ . '/controllers/api.php';

function cms_dispatch($method, $path)
{
    $path = rtrim($path, '/') ?: '/';

    // Public (no login): login form, the website's stylesheets/fonts/images for the preview, enquiry intake API.
    if (strpos($path, '/assets/') === 0) return serve_site_asset($path);
    if ($path === '/login') return $method === 'POST' ? login_post() : login_get();
    if ($path === '/api/enquiries' && $method === 'POST') return api_enquiry_intake();

    if (!cms_user()) {
        if (strpos($path, '/api/') === 0) { http_response_code(401); header('Content-Type: application/json'); echo je(array('error' => 'login required')); return; }
        redirect('/login?next=' . rawurlencode($_SERVER['REQUEST_URI']));
    }
    if ($method === 'POST' && strpos($path, '/api/') !== 0) csrf_check();

    $routes = array(
        array('GET', '#^/$#', 'dashboard'),
        array('POST', '#^/logout$#', 'logout_post'),
        array('GET', '#^/packages$#', 'packages_list'),
        array('GET', '#^/packages/new$#', 'package_new_get'),
        array('POST', '#^/packages/new$#', 'package_new_post'),
        array('GET', '#^/packages/(\d+)$#', 'editor_get'),
        array('POST', '#^/packages/(\d+)/save$#', 'editor_save'),
        array('POST', '#^/packages/(\d+)/workflow$#', 'workflow_post'),
        array('GET', '#^/packages/(\d+)/preview$#', 'package_preview'),
        array('GET', '#^/packages/(\d+)/versions/(\d+)$#', 'version_view'),
        array('POST', '#^/packages/(\d+)/media$#', 'package_media_post'),
        array('GET', '#^/media$#', 'media_library'),
        array('POST', '#^/media/(\d+)$#', 'media_update'),
        array('GET', '#^/file$#', 'serve_file'),
        array('GET', '#^/offers$#', 'offers_list'),
        array('GET', '#^/offers/new$#', 'offer_form'),
        array('POST', '#^/offers/new$#', 'offer_save'),
        array('GET', '#^/offers/(OF-\d{4})$#', 'offer_form'),
        array('POST', '#^/offers/(OF-\d{4})$#', 'offer_save'),
        array('GET', '#^/enquiries$#', 'enquiries_list'),
        array('GET', '#^/enquiries/new$#', 'enquiry_new_get'),
        array('POST', '#^/enquiries/new$#', 'enquiry_new_post'),
        array('GET', '#^/enquiries/(\d+)$#', 'enquiry_view'),
        array('POST', '#^/enquiries/(\d+)$#', 'enquiry_update'),
        array('GET', '#^/curation$#', 'curation_list'),
        array('GET', '#^/pricing$#', 'pricing_overview'),
        array('GET', '#^/versions$#', 'versions_list'),
        array('GET', '#^/destinations$#', 'destinations_list'),
        array('GET', '#^/seo$#', 'seo_dashboard'),
        array('GET', '#^/users$#', 'users_list'),
        array('GET', '#^/activity$#', 'activity_list'),
        array('GET', '#^/recycle-bin$#', 'recycle_bin'),
        array('POST', '#^/packages/(\d+)/versions/(\d+)/restore$#', 'version_restore'),
        array('GET', '#^/api/packages$#', 'api_packages'),
        array('GET', '#^/api/packages/(\d{4}|pk-\d+)$#', 'api_package'),
        array('GET', '#^/api/packages/(\d{4}|pk-\d+)/checklist$#', 'api_checklist'),
        array('GET', '#^/api/offers/(OF-\d{4})$#', 'api_offer'),
    );
    foreach ($routes as $r) {
        if ($r[0] === $method && preg_match($r[1], $path, $m)) {
            array_shift($m);
            return call_user_func_array($r[2], $m);
        }
    }
    http_response_code(404);
    cms_render('error', array('title' => 'Not found', 'message' => 'This CMS page does not exist.'));
}

/** Read-only pass-through of the website's stylesheets, fonts and images (used by the package preview). */
function serve_site_asset($path)
{
    $types = array('css' => 'text/css', 'woff2' => 'font/woff2', 'woff' => 'font/woff', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'avif' => 'image/avif', 'svg' => 'image/svg+xml', 'js' => 'text/javascript');
    $rel = ltrim($path, '/');
    $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
    $root = realpath(cms_config('site_root') . '/assets');
    $abs = realpath(cms_config('site_root') . '/' . $rel);
    if (!$root || !$abs || strpos($abs, $root . '/') !== 0 || !isset($types[$ext]) || !is_file($abs)) { http_response_code(404); return; }
    header('Content-Type: ' . $types[$ext]);
    header('Cache-Control: max-age=300');
    readfile($abs);
}
