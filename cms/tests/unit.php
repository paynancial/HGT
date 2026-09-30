<?php
/**
 * CMS domain tests (no browser). Uses a throwaway SQLite database and a temporary config, so it never touches
 * the staging database, the website data or any production system.
 *   php cms/tests/unit.php
 */
$tmp = sys_get_temp_dir() . '/hgcms-test-' . getmypid();
@mkdir($tmp . '/uploads', 0777, true);
file_put_contents("$tmp/config.php", '<?php return ' . var_export(array(
    'env' => 'staging', 'dsn' => "sqlite:$tmp/cms.sqlite", 'db_user' => null, 'db_pass' => null,
    'site_root' => realpath(__DIR__ . '/../../public_html'), 'site_url' => 'https://holidaygurutravel.in',
    'upload_dir' => "$tmp/uploads", 'upload_max_bytes' => 8388608, 'package_id_assignment' => true,
    'payment_gateway' => false, 'intake_token' => '', 'offer_zone_limit' => 100,
), true) . ';');
putenv("HG_CMS_CONFIG=$tmp/config.php");
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/packages.php';
require __DIR__ . '/../src/media.php';

$pass = 0; $fail = 0;
function t($ok, $msg) { global $pass, $fail; if ($ok) { $pass++; echo "PASS  $msg\n"; } else { $fail++; echo "FAIL  $msg\n"; } }
function throws($fn) { try { $fn(); return false; } catch (Throwable $e) { return $e->getMessage(); } }

$db = cms_db();
cms_migrate($db);
q("INSERT INTO users(email, name, role, password_hash, created_at) VALUES ('t@staging.invalid', 'Tester', 'super_admin', 'x', ?)", array(now()));
$_SESSION['uid'] = 1;

function mkpkg($name, $days = 4, $nights = 3)
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . mt_rand(1000, 9999);
    q("INSERT INTO packages(slug, name, destination, country, region, days, nights, status, public_url, created_at, updated_at, version) VALUES (?,?, 'kashmir', 'India', 'North India', ?, ?, 'draft', ?, ?, ?, 0)", array($slug, $name, $days, $nights, '/' . $slug, now(), now()));
    $pk = (int) cms_db()->lastInsertId();
    foreach (HG_STANDARD_EXCLUSIONS as $i => $t) q("INSERT INTO scope_items(package_pk, kind, category, name, sort_order, is_standard) VALUES (?, 'exclusion', 'Travel', ?, ?, 1)", array($pk, $t, $i));
    pkg_snapshot($pk, 'basic', 'Created');
    return $pk;
}

/* ---------- Package ID ---------- */
q("UPDATE sequences SET last_value = 107 WHERE name = 'package_id'");   // mapping reserves 0001–0107
$a = mkpkg('New Delhi Tour');
$p = pkg_load($a);
t(pkg_id_state($p)[1] === 'pending' && pkg_public_id($p) === null, 'new package: Package ID pending, nothing public');
$id = pkg_assign_id($a);
t($id === '0108', 'Package ID generated after the reserved mapping range (0108)');
t(pkg_public_id(pkg_load($a)) === '0108', 'approved Package ID is the public identifier');
t((bool) throws(function () use ($a) { pkg_assign_id($a); }), 'Package ID cannot be assigned twice (read-only once permanent)');
t((bool) throws(function () use ($a) { q("UPDATE packages SET package_id = '0000' WHERE package_pk = ?", array($a)); }), 'database rejects Package ID 0000');
$b = mkpkg('Second Tour');
t((bool) throws(function () use ($b) { q("UPDATE packages SET package_id = '0108' WHERE package_pk = ?", array($b)); }), 'database rejects a duplicate Package ID');
q("UPDATE packages SET proposed_package_id = '0042', package_id_status = 'proposed' WHERE package_pk = ?", array($b));
t(pkg_id_state(pkg_load($b)) === array('0042', 'proposed') && pkg_public_id(pkg_load($b)) === null, 'proposed Package ID is internal only');
t(pkg_assign_id($b) === '0042', 'imported package takes its approved proposed number');
t(qv("SELECT last_value FROM sequences WHERE name = 'package_id'") == 108, 'sequence never moves backwards');
q('DELETE FROM packages WHERE package_pk = ?', array($a));
$c = mkpkg('Third Tour');
t(pkg_assign_id($c) === '0109', 'a deleted package’s Package ID is never reused');

/* ---------- Itinerary linked to package; Package ID unchanged by edits ---------- */
q("INSERT INTO itinerary_days(package_pk, day_number, title, description) VALUES (?, 1, 'Arrival', 'Arrive and check in.')", array($c));
pkg_snapshot($c, 'itinerary');
t(pkg_load($c)['package_id'] === '0109', 'changing the itinerary keeps the Package ID');
$v = q1('SELECT * FROM package_versions WHERE package_pk = ? ORDER BY version DESC', array($c));
t($v['package_id'] === '0109' && strpos($v['sections'], 'itinerary') !== false, 'itinerary version records Package ID and changed section');
t(pkg_duration_issue(pkg_load($c)) === 'Itinerary contains 1 day but package duration is 4 days.', 'duration mismatch warning text');
foreach (array(2, 3, 4, 5) as $n) q("INSERT INTO itinerary_days(package_pk, day_number, title, description) VALUES (?, ?, 'Day', 'Text')", array($c, $n));
t(strpos(pkg_duration_issue(pkg_load($c)), 'contains 5 days but package duration is 4 days') !== false, 'too many itinerary days is flagged');
$chk = pkg_checklist(pkg_load($c));
$dur = array_values(array_filter($chk['Content'], function ($i) { return $i['key'] === 'duration'; }))[0];
t(!$dur['ok'], 'duration mismatch blocks publishing');
q("UPDATE packages SET duration_override = 'Day 5 is departure morning' WHERE package_pk = ?", array($c));
$chk = pkg_checklist(pkg_load($c));
$dur = array_values(array_filter($chk['Content'], function ($i) { return $i['key'] === 'duration'; }))[0];
t($dur['ok'], 'explicit override clears the duration block');

/* ---------- Pricing: versions, expiry ---------- */
putenv('HG_CMS_TODAY=2026-10-15');
q("INSERT INTO rate_versions(package_pk, version, base_price, price_unit, valid_from, valid_until, rate_status, created_at) VALUES (?, 1, 24999, 'per person', '2026-10-01', '2026-10-31', 'approved', ?)", array($c, now()));
$r = pkg_current_rate(pkg_load($c));
t($r && rate_label($r) === '₹24,999 / person', 'approved rate within validity is current (Indian grouping)');
q("INSERT INTO rate_versions(package_pk, version, base_price, price_unit, valid_from, valid_until, rate_status, created_at) VALUES (?, 2, 26999, 'per person', '2026-10-10', '2026-11-30', 'draft', ?)", array($c, now()));
t((int) pkg_current_rate(pkg_load($c))['version'] === 1, 'a draft price version is not shown');
q("UPDATE rate_versions SET rate_status = 'approved' WHERE package_pk = ? AND version = 2", array($c));
t((int) pkg_current_rate(pkg_load($c))['version'] === 2, 'new approved price version becomes current');
t(qv('SELECT base_price FROM rate_versions WHERE package_pk = ? AND version = 1', array($c)) == 24999, 'old price version kept unchanged');
putenv('HG_CMS_TODAY=2026-12-05');
t(pkg_current_rate(pkg_load($c)) === null && rate_label(null) === 'Price on request', 'expired rate → Price on request');
t((bool) throws(function () use ($c) { q("INSERT INTO rate_versions(package_pk, version, base_price, valid_from, valid_until, created_at) VALUES (?, 3, 1000, '2026-12-10', '2026-12-01', ?)", array($c, now())); }), 'database rejects validity ending before it starts');
t((bool) throws(function () use ($c) { q("INSERT INTO rate_versions(package_pk, version, base_price, valid_from, valid_until, created_at) VALUES (?, 2, 1000, '2026-12-10', '2026-12-20', ?)", array($c, now())); }), 'price version numbers are unique per package');
putenv('HG_CMS_TODAY=2026-10-15');
list($pay, $why) = pkg_paynow(pkg_load($c));
t(!$pay && in_array('No payment gateway connected', $why, true), 'Pay Now disabled without a gateway, with the reason');

/* ---------- Standard exclusions / checklist ---------- */
$p = pkg_load($c);
$std = array_filter($p['scope'], function ($s) { return $s['is_standard']; });
t(count($std) === 3 && array_values(array_map(function ($s) { return $s['name']; }, $std)) === array('Airfare', 'Train fare', 'Bus fare'), 'standard exclusions: airfare, train fare, bus fare');
list($err) = pkg_blockers($p);
$keys = array_column($err, 'key');
t(in_array('description', $keys, true) && in_array('meta_title', $keys, true) && in_array('featured', $keys, true) && in_array('inclusions', $keys, true), 'thin package: publish blocked (description, meta title, image, inclusions)');
t(!in_array('pricing', $keys, true), 'Price on request does not block publishing (allowed state)');

/* ---------- Offers: separate sequence ---------- */
$o1 = next_offer_code(); $o2 = next_offer_code();
t($o1 === 'OF-0001' && $o2 === 'OF-0002', 'Offer Codes use their own OF- sequence');
t(qv("SELECT last_value FROM sequences WHERE name = 'package_id'") == 109, 'creating offers does not move the Package ID sequence');
q("INSERT INTO offers(offer_code, name, valid_from, valid_until, status, created_at) VALUES (?, 'Autumn', '2026-10-01', '2026-10-31', 'published', ?)", array($o1, now()));
q("INSERT INTO offers(offer_code, name, valid_from, valid_until, status, created_at) VALUES (?, 'Family', '2026-10-01', '2026-11-30', 'draft', ?)", array($o2, now()));
foreach (q('SELECT offer_pk FROM offers')->fetchAll() as $o) q('INSERT INTO offer_packages VALUES (?, ?)', array($o['offer_pk'], $c));
$p = pkg_load($c);
t(count($p['offers']) === 2 && $p['package_id'] === '0109', 'one package, many offers; Package ID unchanged');
t((bool) throws(function () { q("INSERT INTO offers(offer_code, name, valid_from, valid_until, created_at) VALUES ('0110', 'x', '2026-01-01', '2026-01-02', ?)", array(now())); }), 'database rejects an offer code shaped like a Package ID');

/* ---------- Search ---------- */
$s = cms_search('0109');
t(count($s['packages']) === 1 && (int) $s['packages'][0]['package_pk'] === $c, 'search by Package ID "0109"');
$s = cms_search('Package ID 109');
t(count($s['packages']) === 1, 'search "Package ID 109" (short form)');
$s = cms_search('0042');
t(count($s['packages']) === 1 && (int) $s['packages'][0]['package_pk'] === $b, 'search by another Package ID finds the right package');
$s = cms_search('of-1');
t($s['offer'] && $s['offer']['offer_code'] === 'OF-0001' && count($s['packages']) === 1, 'search by Offer Code returns the offer and its packages');
$s = cms_search('Third');
t(count($s['packages']) === 1, 'search by package name');

/* ---------- Rich text sanitiser ---------- */
$h = clean_html('<h2>Plan</h2><p onclick="x()">Hi <a href="javascript:alert(1)">x</a> <a href="https://a.b">y</a></p><script>alert(1)</script><aside class="hg-tip">Tip</aside><aside class="evil">z</aside><div><span>kept</span></div>');
t(strpos($h, 'script') === false && strpos($h, 'onclick') === false && strpos($h, 'javascript') === false, 'sanitiser strips scripts, handlers and javascript: links');
t(strpos($h, '<h2>Plan</h2>') !== false && strpos($h, 'rel="noopener"') !== false && strpos($h, 'class="hg-tip"') !== false && strpos($h, 'class="evil"') === false && strpos($h, 'kept') !== false, 'sanitiser keeps allowed structure and callout classes');

/* ---------- Activity log immutability ---------- */
cms_log('Test action', $c, 'f', 'a', 'b');
t((bool) throws(function () { q("UPDATE activity_log SET action = 'x'"); }), 'activity log rows cannot be updated');
t((bool) throws(function () { q('DELETE FROM activity_log'); }), 'activity log rows cannot be deleted');

/* ---------- Media validation ---------- */
$img = imagecreatetruecolor(1920, 1080);
imagefill($img, 0, 0, imagecolorallocate($img, 10, 22, 61));
imagejpeg($img, "$tmp/ok.jpg"); imagedestroy($img);
$small = imagecreatetruecolor(300, 200); imagepng($small, "$tmp/small.png"); imagedestroy($small);
file_put_contents("$tmp/fake.jpg", '<?php echo 1;');
$mid = media_store("$tmp/ok.jpg", 'Dal Lake.jpg', filesize("$tmp/ok.jpg"), array('alt_text' => 'Shikaras on Dal Lake at sunrise'), false);
$m = q1('SELECT * FROM media WHERE media_id = ?', array($mid));
t($m['width'] == 1920 && count(jd($m['variants'])) === 3 && is_file(media_abs(jd($m['variants'])['400'])), 'upload stores image and creates 1600/800/400 WebP variants');
t(strpos((string) throws(function () use ($tmp) { media_validate("$tmp/small.png", 'small.png', filesize("$tmp/small.png"), false); }), 'at least') !== false, 'too-small image rejected with a clear message');
t(strpos((string) throws(function () use ($tmp) { media_validate("$tmp/fake.jpg", 'fake.jpg', filesize("$tmp/fake.jpg"), false); }), 'Only JPG') !== false, 'non-image disguised as .jpg rejected (content check)');
$crop = media_crop($mid, '1:1');
$cm = q1('SELECT * FROM media WHERE media_id = ?', array($crop));
t($cm['width'] == 1080 && $cm['height'] == 1080 && is_file(media_abs($m['file_path'])), 'crop creates a new 1:1 image and keeps the original');

/* ---------- Permissions ---------- */
t(hg_may('super_admin', 'assign_package_id') && !hg_may('admin', 'assign_package_id') && !hg_may('content_manager', 'publish'), 'only Super Admin assigns Package IDs; Content Manager cannot publish');
t(hg_can('travel_consultant', 'pricing', 'request') && !hg_may('travel_consultant', 'approve_rate'), 'Travel Consultant can request, not approve, a rate');
t(!hg_can('reviewer', 'enquiries') && hg_can('sales_manager', 'enquiries', 'manage'), 'enquiry access follows the role matrix');

exec('rm -rf ' . escapeshellarg($tmp));
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
