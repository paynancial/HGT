<?php
/**
 * Tests for the DRAFT package registry / pricing schema (schema-draft.sql).
 * Runs against a throwaway local database ONLY — never production.
 *
 *   mysql -e "CREATE DATABASE hg_registry_test"
 *   mysql hg_registry_test < docs/package-registry/schema-draft.sql
 *   HG_TEST_DSN="mysql:unix_socket=/run/mysqld/mysqld.sock;dbname=hg_registry_test" \
 *   HG_TEST_USER=root php docs/package-registry/tests/registry_test.php
 */
$dsn = getenv('HG_TEST_DSN');
if (!$dsn || strpos($dsn, '_test') === false) {
    fwrite(STDERR, "Refusing to run: HG_TEST_DSN must point at a *_test database.\n");
    exit(2);
}
function db()
{
    $pdo = new PDO(getenv('HG_TEST_DSN'), getenv('HG_TEST_USER') ?: 'root', getenv('HG_TEST_PASS') ?: '',
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    return $pdo;
}
function create(PDO $db, $slug, $name, $source = 'cms')
{
    $st = $db->prepare('CALL create_package(?, ?, ?, ?, ?, @id, @num)');
    $st->execute(array($slug, $name, 'kashmir', 'tester', $source));
    $st->closeCursor();
    return $db->query('SELECT @id AS id, @num AS num')->fetch(PDO::FETCH_ASSOC);
}
function fails(callable $fn, $expect)
{
    try { $fn(); return 'no error'; } catch (PDOException $e) { return strpos($e->getMessage(), $expect) !== false ? true : $e->getMessage(); }
}

// Concurrency worker mode: create N packages and print their numbers.
if (isset($argv[1]) && $argv[1] === 'worker') {
    $db = db();
    for ($i = 0; $i < (int) $argv[3]; $i++) {
        for ($try = 0; $try < 5; $try++) {
            try { $r = create($db, 'w' . $argv[2] . '-' . $i, 'Worker ' . $argv[2] . ' #' . $i); echo $r['num'], "\n"; break; }
            catch (PDOException $e) { if (strpos($e->getMessage(), 'Deadlock') === false) throw $e; usleep(20000); }
        }
    }
    exit(0);
}

$pass = 0; $fail = 0;
function check($name, $ok, $detail = '')
{
    global $pass, $fail;
    $ok === true ? $pass++ : $fail++;
    echo ($ok === true ? 'PASS' : 'FAIL') . "  $name" . ($ok === true ? ($detail ? "  — $detail" : '') : '  — ' . var_export($ok, true) . ' ' . $detail) . "\n";
}

$db = db();
foreach (array('payments', 'quotations', 'enquiries', 'package_audit', 'package_slug_history') as $t) $db->exec("DELETE FROM $t");
$db->exec('DROP TRIGGER IF EXISTS trg_packages_no_delete'); $db->exec('DROP TRIGGER IF EXISTS trg_registry_no_delete');
$db->exec('DELETE FROM tour_number_registry'); $db->exec('DELETE FROM packages');
$db->exec("CREATE TRIGGER trg_packages_no_delete BEFORE DELETE ON packages FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'packages are archived, not deleted'");
$db->exec("CREATE TRIGGER trg_registry_no_delete BEFORE DELETE ON tour_number_registry FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'package numbers are never released'");
$db->exec('UPDATE tour_number_sequence SET next_value = 1, max_value = 9999');

// 1–2. Sequential numbers
$a = create($db, 'new-delhi-tour', 'New Delhi Tour');
$b = create($db, 'kashmir-special', 'Kashmir Special');
check('create package → 0001', $a['num'] === '0001', $a['num']);
check('second package → 0002', $b['num'] === '0002', $b['num']);

// 3–4. Edit / rename keep the number
$db->prepare('UPDATE packages SET name = ?, slug = ?, duration_days = 5 WHERE package_id = ?')->execute(array('Kashmir Special Deluxe', 'kashmir-special-deluxe', $b['id']));
$num = $db->query('SELECT tour_number FROM packages WHERE package_id = ' . (int) $b['id'])->fetchColumn();
check('edit + rename → number unchanged', $num === '0002', $num);
check('direct change of tour_number is blocked', fails(function () use ($db, $b) { $db->exec("UPDATE packages SET tour_number = '0009' WHERE package_id = " . (int) $b['id']); }, 'permanent'));

// 5–6. Archive / delete never free the number
$db->exec("UPDATE packages SET status = 'retired' WHERE package_id = " . (int) $a['id']);
check('archived/retired package keeps its number', $db->query('SELECT tour_number FROM packages WHERE package_id = ' . (int) $a['id'])->fetchColumn() === '0001');
check('deleting a package is blocked', fails(function () use ($db, $a) { $db->exec('DELETE FROM packages WHERE package_id = ' . (int) $a['id']); }, 'archived, not deleted'));
check('releasing a registry number is blocked', fails(function () use ($db) { $db->exec("DELETE FROM tour_number_registry WHERE tour_number = '0001'"); }, 'never released'));
$c = create($db, 'dubai-explorer', 'Dubai Explorer');
check('next package after retirement → 0003 (0001 not reused)', $c['num'] === '0003', $c['num']);
check('manual duplicate number rejected by UNIQUE', fails(function () use ($db) { $db->exec("INSERT INTO packages (tour_number, slug, name, destination_key, created_by) VALUES ('0002', 'dup', 'Dup', 'x', 't')"); }, 'Duplicate'));
check('non 4-digit number rejected by CHECK', fails(function () use ($db) { $db->exec("INSERT INTO packages (tour_number, slug, name, destination_key, created_by) VALUES ('12', 'bad', 'Bad', 'x', 't')"); }, 'chk_packages_number'));

// 7. Concurrency: 8 processes × 12 packages at the same time
$procs = array(); $out = array();
for ($w = 0; $w < 8; $w++) {
    $procs[$w] = proc_open(array(PHP_BINARY, __FILE__, 'worker', (string) $w, '12'), array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes[$w]);
}
for ($w = 0; $w < 8; $w++) { $out = array_merge($out, array_filter(explode("\n", stream_get_contents($pipes[$w][1])))); $err = stream_get_contents($pipes[$w][2]); proc_close($procs[$w]); if ($err) echo "worker $w: $err\n"; }
$uniq = array_unique($out);
check('concurrent creation: 96 packages, 96 unique numbers', count($out) === 96 && count($uniq) === 96, count($out) . ' created, ' . count($uniq) . ' unique');
$seqOk = $db->query("SELECT COUNT(*) = 99 AND MIN(tour_number) = '0001' AND MAX(tour_number) = '0099' FROM tour_number_registry")->fetchColumn();
check('no gaps or duplicates in registry (0001–0099)', (bool) $seqOk);

// 8. Exhaustion: no silent roll-over
$db->exec('UPDATE tour_number_sequence SET next_value = 9999');
$last = create($db, 'last-one', 'Last One');
check('9999 is issued', $last['num'] === '9999');
check('after 9999 creation stops (owner decision required)', fails(function () use ($db) { create($db, 'overflow', 'Overflow'); }, 'exhausted'));
check('no roll-over row was created', (int) $db->query("SELECT COUNT(*) FROM packages WHERE slug = 'overflow'")->fetchColumn() === 0);

// 9. Rates, versions, validity, expiry
$pid = (int) $b['id'];
$ins = $db->prepare("INSERT INTO package_rates (package_id, version, status, price_unit, base_price, tax_mode, tax_rate_percent, rate_valid_from, rate_valid_until, created_by, approved_by, approved_at)
                     VALUES (?, ?, ?, 'per_person_twin_sharing', ?, 'extra', 5, ?, ?, 't', 'owner', NOW())");
$ins->execute(array($pid, 1, 'approved', 24999, date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-1 day'))));
check('expired rate is not shown as current', $db->query("SELECT COUNT(*) FROM package_active_rate WHERE package_id = $pid")->fetchColumn() == 0);
$ins->execute(array($pid, 2, 'draft', 26999, date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))));
$v2 = (int) $db->lastInsertId();
// Inclusions / exclusions are written while the version is a draft, then approved together.
$item = $db->prepare('INSERT INTO package_rate_items (rate_id, kind, category, text) VALUES (?, ?, ?, ?)');
$item->execute(array($v2, 'inclusion', 'accommodation', '4 nights deluxe hotel, twin sharing'));
$item->execute(array($v2, 'exclusion', 'external_air', 'Airfare'));
$db->exec("UPDATE package_rates SET status = 'approved' WHERE rate_id = $v2");
$act = $db->query("SELECT version, base_price, rate_valid_until FROM package_active_rate WHERE package_id = $pid")->fetch(PDO::FETCH_ASSOC);
check('price update → new active rate displayed', $act && $act['version'] == 2 && $act['base_price'] == 26999, json_encode($act));
check('validity comes from the rate record', $act && $act['rate_valid_until'] === date('Y-m-d', strtotime('+30 days')));
$ins->execute(array($pid, 3, 'draft', 19999, date('Y-m-d'), null));
check('unapproved (draft) rate is never active', $db->query("SELECT version FROM package_active_rate WHERE package_id = $pid")->fetchColumn() == 2);
check('approved rate price cannot be edited in place', fails(function () use ($db, $v2) { $db->exec("UPDATE package_rates SET base_price = 1 WHERE rate_id = $v2"); }, 'immutable'));
check('history kept: versions 1–3 all retained', (int) $db->query("SELECT COUNT(*) FROM package_rates WHERE package_id = $pid")->fetchColumn() === 3);
check('fake discount rejected (offer without reason / not lower)', fails(function () use ($db, $pid) { $db->exec("INSERT INTO package_rates (package_id, version, price_unit, base_price, offer_price, tax_mode, rate_valid_from, created_by) VALUES ($pid, 9, 'per_person', 1000, 1200, 'included', CURDATE(), 't')"); }, 'chk_offer'));
check('standard rule: external travel excluded by default', $db->query("SELECT includes_airfare = 0 AND includes_train = 0 AND includes_bus = 0 FROM package_rates WHERE rate_id = $v2")->fetchColumn() == 1);

// 10. Inclusions / exclusions tied to the rate version
check('inclusions of an approved rate cannot be added', fails(function () use ($item, $v2) { $item->execute(array($v2, 'inclusion', 'meals', 'All meals')); }, 'locked'));
check('exclusions of an approved rate cannot be deleted', fails(function () use ($db, $v2) { $db->exec("DELETE FROM package_rate_items WHERE rate_id = $v2"); }, 'locked'));
$rows = $db->query("SELECT i.kind, i.text FROM package_active_rate r JOIN package_rate_items i ON i.rate_id = r.rate_id WHERE r.package_id = $pid")->fetchAll(PDO::FETCH_ASSOC);
check('current inclusions/exclusions come from the active rate version', count($rows) === 2, json_encode($rows));

// 11. Enquiry and payment keep package_id + number + name + rate version
$db->prepare("INSERT INTO enquiries (enquiry_ref, enquiry_type, package_id, tour_number, package_name, rate_id, rate_version, displayed_price, displayed_currency, travel_date, adults, children, customer_name, customer_phone, customer_email)
              VALUES ('ENQ-TEST0001', 'tour_package', ?, '0002', 'Kashmir Special Deluxe', ?, 2, 26999, 'INR', '2026-10-15', 2, 0, 'Test', '9999999999', 't@example.com')")->execute(array($pid, $v2));
$e = $db->query("SELECT enquiry_type, tour_number, rate_version, displayed_price FROM enquiries WHERE enquiry_ref = 'ENQ-TEST0001'")->fetch(PDO::FETCH_ASSOC);
check('enquiry stores type, package number and displayed rate version', $e['enquiry_type'] === 'tour_package' && $e['tour_number'] === '0002' && $e['rate_version'] == 2, json_encode($e));
$db->prepare("INSERT INTO payments (payment_ref, package_id, tour_number, package_name, rate_id, rate_version, amount, currency, payment_kind, travel_date, adults, children, customer_name, customer_phone, customer_email)
              VALUES ('PAY-TEST000001', ?, '0002', 'Kashmir Special Deluxe', ?, 2, 53998, 'INR', 'full', '2026-10-15', 2, 0, 'Test', '9999999999', 't@example.com')")->execute(array($pid, $v2));
$db->exec("UPDATE packages SET name = 'Renamed Again' WHERE package_id = $pid");
check('payment keeps the name/rate it was made against after a rename', $db->query("SELECT package_name FROM payments WHERE payment_ref = 'PAY-TEST000001'")->fetchColumn() === 'Kashmir Special Deluxe');

// 12. Internal curation ("Top 500")
$cur = $db->prepare('INSERT INTO package_curation (package_id, priority_rank, homepage_featured, updated_by) VALUES (?, ?, 1, ?)');
$cur->execute(array($pid, 1, 'tester'));
check('curation: rank 1 stored for a package', (int) $db->query("SELECT priority_rank FROM package_curation WHERE package_id = $pid")->fetchColumn() === 1);
$other = array('id' => $db->query("SELECT package_id FROM packages WHERE package_id <> $pid ORDER BY package_id LIMIT 1")->fetchColumn());
check('curation: duplicate rank rejected', fails(function () use ($cur, $other) { $cur->execute(array($other['id'], 1, 'tester')); }, 'uq_curation_rank'));
check('curation: rank 501 rejected', fails(function () use ($cur, $other) { $cur->execute(array($other['id'], 501, 'tester')); }, 'chk_curation_rank'));

// 13. Booking keeps package_id + Tour No.
check('booking requires a Tour No.', fails(function () use ($db, $pid) { $db->exec("INSERT INTO bookings (booking_ref, package_id, tour_number, package_name, travel_date, adults, children) VALUES ('BKG-TEST0001', $pid, NULL, 'X', '2026-10-15', 2, 0)"); }, 'tour_number'));

echo "\n$pass/" . ($pass + $fail) . " passed\n";
exit($fail ? 1 : 0);
