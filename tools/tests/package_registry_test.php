<?php
/**
 * Unit tests for include/package_registry.php and the committed registry data.
 * Run: php tools/tests/package_registry_test.php   (exit code 0 = all passed)
 */
$_SERVER['HTTP_HOST'] = 'holidaygurutravel.in';   // behave like the live site (no preview)
require __DIR__ . '/../../public_html/include/package_registry.php';

$pass = 0; $fail = 0;
function t($name, $ok, $detail = '')
{
    global $pass, $fail;
    if ($ok) { $pass++; echo "PASS  $name\n"; } else { $fail++; echo "FAIL  $name  $detail\n"; }
}

/* ---------- Committed registry integrity ---------- */
$root = dirname(__DIR__, 2);
$reg = json_decode(file_get_contents($root . '/public_html/include/data/package-registry.json'), true);
$pk = json_decode(file_get_contents($root . '/public_html/include/data/packages.json'), true);
$nums = array_column($reg['entries'], 'package_id');
$slugs = array_column($reg['entries'], 'slug');
t('registry: every number is 4 digits, not 0000', count(array_filter($nums, function ($n) { return preg_match('/^(?!0000)\d{4}$/', $n); })) === count($nums));
t('registry: numbers unique', count($nums) === count(array_unique($nums)));
t('registry: each package listed once', count($slugs) === count(array_unique($slugs)));
t('registry: every current package has an entry', !array_diff(array_column($pk, 'slug'), $slugs));
t('registry: status is proposed|approved|retired', !array_diff(array_unique(array_column($reg['entries'], 'status')), array('proposed', 'approved', 'retired')));
$approved = array_filter($reg['entries'], function ($e) { return $e['status'] !== 'proposed'; });
t('registry: nothing approved without owner sign-off (all proposed today)', count($approved) === 0, count($approved) . ' approved');

/* ---------- Public display rules ---------- */
hg_tr_json('package-registry.json', array('entries' => array(
    array('package_id' => '0001', 'slug' => 'a', 'internal_key' => '847', 'status' => 'approved'),
    array('package_id' => '0002', 'slug' => 'b', 'internal_key' => '', 'status' => 'proposed'),
    array('package_id' => '0003', 'slug' => 'c', 'internal_key' => '', 'status' => 'retired'),
    array('package_id' => '12', 'slug' => 'd', 'internal_key' => '', 'status' => 'approved'),
)));
t('approved Package ID shown', hg_package_id('a') === '0001');
t('proposed Package ID hidden on the live site', hg_package_id('b') === '');
t('retired Package ID still resolves (history)', hg_package_id('c') === '0003');
t('malformed Package ID never shown', hg_package_id('d') === '');
t('unknown package: no Package ID', hg_package_id('zzz') === '');
t('lookup by Package ID (padded)', hg_slug_by_package_id('1') === 'a' && hg_slug_by_package_id('0001') === 'a');
t('lookup by proposed Package ID finds nothing publicly', hg_slug_by_package_id('0002') === '');
t('internal key from registry', hg_package_key('a') === '847');
t('internal key fallback is the permanent slug key', hg_package_key('b') === 'slug:b');
$_SERVER['HTTP_HOST'] = 'localhost:8098';
t('proposed Package ID hidden on a local server without the preview switch', hg_package_id('b') === '' || is_file($root . '/public_html/include/data/.preview-package-ids'));
$_SERVER['HTTP_HOST'] = 'holidaygurutravel.in';

/* ---------- Offer Codes: separate format and sequence ---------- */
t('offer code format OF-0001', hg_is_offer_code('OF-0001') && !hg_is_offer_code('0001') && !hg_is_offer_code('OF-0000') && !hg_is_offer_code('OF-12'));
t('a Package ID can never be read as an offer code (and vice versa)', !hg_is_offer_code(hg_package_id('a')) && !preg_match('/^(?!0000)\d{4}$/', 'OF-0001'));
hg_tr_json('offers.json', array('offers' => array(
    array('offer_code' => 'OF-0001', 'packages' => array('a'), 'status' => 'published'),
    array('offer_code' => 'OF-0002', 'packages' => array('a', 'c'), 'status' => 'published'),
    array('offer_code' => 'OF-0003', 'packages' => array('a'), 'status' => 'draft'),
)));
t('a package may have several published offers', count(hg_offers_for('a')) === 2);
t('draft offers are not live', hg_offer('OF-0003') === null && hg_offer('OF-0001') !== null);
t('offers do not change the Package ID', hg_package_id('a') === '0001');
$ctx = hg_package_enquiry_context('a', 'OF-0002');
t('enquiry carries a valid offer code for this package', $ctx['Offer code'] === 'OF-0002');
$ctx = hg_package_enquiry_context('b', 'OF-0001');
t('offer code for another package is rejected', $ctx['Offer code'] === '');
hg_tr_json('offers.json', array('offers' => array()));
$committed = json_decode(file_get_contents($root . '/public_html/include/data/offers.json'), true);
t('committed offers: codes valid and unique (none published yet)', count(array_filter(array_column($committed['offers'], 'offer_code'), 'hg_is_offer_code')) === count($committed['offers']) && count(array_unique(array_column($committed['offers'], 'offer_code'))) === count($committed['offers']));

/* ---------- Rates: versioning and validity ---------- */
$rates = array(
    array('slug' => 'a', 'version' => 1, 'base_price' => 21999, 'currency' => 'INR', 'price_unit' => 'person', 'rate_status' => 'approved', 'rate_valid_from' => '2026-09-01', 'rate_valid_until' => '2026-09-30'),
    array('slug' => 'a', 'version' => 2, 'base_price' => 23999, 'currency' => 'INR', 'price_unit' => 'person', 'rate_status' => 'approved', 'rate_valid_from' => '2026-10-01', 'rate_valid_until' => '2026-10-31'),
    array('slug' => 'a', 'version' => 3, 'base_price' => 24999, 'currency' => 'INR', 'price_unit' => 'person', 'rate_status' => 'draft', 'rate_valid_from' => '2026-10-01', 'rate_valid_until' => '2026-10-31'),
    array('slug' => 'b', 'version' => 1, 'base_price' => 0, 'currency' => 'INR', 'price_unit' => 'person', 'rate_status' => 'approved', 'rate_valid_from' => '2026-10-01', 'rate_valid_until' => '2026-10-31'),
    array('slug' => 'c', 'version' => 4, 'base_price' => 30000, 'currency' => 'INR', 'price_unit' => 'person', 'rate_status' => 'approved', 'rate_valid_from' => '2026-10-01', 'rate_valid_until' => '2026-10-31'),
    array('slug' => 'c', 'version' => 5, 'base_price' => 31000, 'currency' => 'INR', 'price_unit' => 'person', 'rate_status' => 'approved', 'rate_valid_from' => '2026-10-10', 'rate_valid_until' => '2026-11-30'),
);
$r = hg_current_rate('a', $rates, '2026-10-15');
t('current approved version shown (v2, draft v3 ignored)', $r && $r['version'] === 2);
t('rate label', $r && hg_rate_label($r) === '₹23,999 / person', $r ? hg_rate_label($r) : '');
t('rate validity label', $r && hg_rate_validity($r) === '1 Oct 2026 – 31 Oct 2026', $r ? hg_rate_validity($r) : '');
t('expired rate is never shown as current', hg_current_rate('a', $rates, '2026-11-01') === null);
t('future rate not shown before its start date', hg_current_rate('a', $rates, '2026-08-31') === null);
t('validity is inclusive of first and last day', hg_current_rate('a', $rates, '2026-10-01') && hg_current_rate('a', $rates, '2026-10-31'));
t('zero / missing price never shown', hg_current_rate('b', $rates, '2026-10-15') === null);
t('overlapping versions: highest version wins', hg_current_rate('c', $rates, '2026-10-15')['version'] === 5);
t('before newer version starts, older still valid', hg_current_rate('c', $rates, '2026-10-05')['version'] === 4);
t('no rate at all → Price on request', hg_current_rate('zzz', $rates, '2026-10-15') === null);

/* ---------- Pay Now ---------- */
t('Pay Now disabled while no gateway is connected', HG_PAYMENT_ENABLED === false && hg_paynow_enabled('a') === false);

/* ---------- Enquiry context ---------- */
hg_tr_json('rates.json', array('versions' => array_map(function ($x) { $x['rate_valid_from'] = date('Y-m-d', strtotime('-1 day')); $x['rate_valid_until'] = date('Y-m-d', strtotime('+30 days')); return $x; }, array_slice($rates, 1, 1))));
$ctx = hg_package_enquiry_context('a');
t('enquiry context: Package ID + internal ref + rate + version + validity', $ctx['Package ID'] === '0001' && $ctx['Internal ref'] === '847' && $ctx['Displayed rate'] === '₹23,999 / person' && $ctx['Rate version'] === '2' && $ctx['Rate validity'] !== '');
$ctx = hg_package_enquiry_context('b');
t('enquiry context without rate: Price on request, no version', $ctx['Displayed rate'] === 'Price on request' && $ctx['Rate version'] === '' && $ctx['Package ID'] === '');

/* ---------- Curation (internal) ---------- */
hg_tr_json('curation.json', array('tours' => array('a' => array('priority_rank' => 1, 'homepage_featured' => true), 'b' => array('priority_rank' => 501), 'c' => array('priority_rank' => 0))));
t('priority_rank 1–500 accepted', hg_curation('a')['priority_rank'] === 1 && hg_curation('a')['homepage_featured'] === true);
t('priority_rank outside 1–500 ignored', hg_curation('b')['priority_rank'] === 0 && hg_curation('c')['priority_rank'] === 0);
$cur = json_decode(file_get_contents($root . '/public_html/include/data/curation.json'), true);
$ranks = array_filter(array_map(function ($x) { return isset($x['priority_rank']) ? (int) $x['priority_rank'] : 0; }, $cur['tours']));
t('committed curation: ranks unique, within 1–500, at most 500 ranked', count($ranks) === count(array_unique($ranks)) && count($ranks) <= 500 && (!$ranks || (min($ranks) >= 1 && max($ranks) <= 500)));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
