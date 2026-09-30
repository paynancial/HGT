<?php
/**
 * Staging setup: create the CMS database, import the website's package data (read-only), create one
 * staging user per role with a random password.
 *
 *   php cms/bin/setup.php            create / update (keeps existing data)
 *   php cms/bin/setup.php --reset    delete the staging database and uploads first
 *
 * Refuses to run when config env is 'production'. Never writes into the website folder.
 * Staging logins are written to cms/storage/.staging-credentials (gitignored, mode 0600).
 */
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/packages.php';

if (PHP_SAPI !== 'cli') exit(1);
if (!cms_is_staging()) { fwrite(STDERR, "Refusing: config env is production.\n"); exit(2); }

$reset = in_array('--reset', $argv, true);
$dsn = cms_config('dsn');
if ($reset && strpos($dsn, 'sqlite:') === 0) {
    $file = substr($dsn, 7);
    foreach (array($file, "$file-wal", "$file-shm") as $f) if (is_file($f)) unlink($f);
    $up = cms_config('upload_dir');
    if (is_dir($up)) exec('rm -rf ' . escapeshellarg($up));
    @unlink(CMS_ROOT . '/storage/.staging-credentials');
}
@mkdir(cms_config('upload_dir'), 0775, true);

$db = cms_db();
cms_migrate($db);

/* ---------- users ---------- */
$creds = array();
foreach (HG_ROLES as $role => $label) {
    $email = str_replace('_', '.', $role) . '@staging.invalid';
    if (qv('SELECT 1 FROM users WHERE email = ?', array($email))) continue;
    $pw = rtrim(strtr(base64_encode(random_bytes(12)), '+/', 'Ab'), '=');
    q('INSERT INTO users(email, name, role, password_hash, created_at) VALUES (?,?,?,?,?)', array($email, 'Staging ' . $label, $role, password_hash($pw, PASSWORD_DEFAULT), now()));
    $creds[] = sprintf('%-20s %-34s %s', $label, $email, $pw);
}
if ($creds) {
    $cf = CMS_ROOT . '/storage/.staging-credentials';
    file_put_contents($cf, "# Staging CMS logins (random, staging only; never commit)\n" . implode("\n", $creds) . "\n", FILE_APPEND);
    chmod($cf, 0600);
    echo "Created " . count($creds) . " staging users. Logins: $cf\n";
}

/* ---------- import website packages (only when the database has none) ---------- */
if ((int) qv('SELECT COUNT(*) FROM packages') === 0) {
    $read = function ($f) { return json_decode((string) file_get_contents(site_path('include/data/' . $f)), true); };
    $pkgs = $read('packages.json');
    $reg = $read('package-registry.json');
    $rates = $read('rates.json');
    $offers = $read('offers.json');
    $cur = $read('curation.json');
    $proposed = array();
    foreach ($reg['entries'] as $r) $proposed[$r['slug']] = $r;
    $dest = hg_destinations();
    $site = rtrim(cms_config('site_url'), '/');
    $db->beginTransaction();
    $pkByslug = array();
    foreach ($pkgs as $p) {
        $g = isset($dest[$p['group']]) ? $dest[$p['group']] : array('country' => '', 'region' => '');
        $r = isset($proposed[$p['slug']]) ? $proposed[$p['slug']] : null;
        $idStatus = $r ? ($r['status'] === 'approved' ? 'approved' : 'proposed') : 'pending';
        q('INSERT INTO packages(package_id, proposed_package_id, package_id_status, slug, name, country, region, destination, city_route, package_type, speciality_type, days, nights,
            suitable_for, short_description, description_html, highlights, status, public_url, source, created_at, updated_at, published_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array(
            $idStatus === 'approved' ? $r['package_id'] : null, $r ? $r['package_id'] : null, $idStatus,
            $p['slug'], $p['name'], $g['country'], $g['region'], $p['group'], $p['places'] ? implode(' – ', $p['places']) : $p['cities'],
            $p['pilgrimage'] ? 'Pilgrimage' : '', $p['pilgrimage'] ? 'Pilgrimage' : '', (int) $p['days'], (int) $p['nights'],
            '[]', mb_strlen($p['description']) <= 300 ? $p['description'] : '', '<p>' . e($p['description']) . '</p>', je(array_values($p['features'])),
            'published', $p['url'], 'site-import', now(), now(), now(),
        ));
        $pk = (int) $db->lastInsertId();
        $pkByslug[$p['slug']] = $pk;
        foreach ($p['itinerary'] as $i => $d) {
            q('INSERT INTO itinerary_days(package_pk, day_number, title, description) VALUES (?,?,?,?)', array($pk, $i + 1, trim(preg_replace('/\s+/', ' ', $d['title'])), trim($d['text'])));
        }
        $n = 0;
        foreach ($p['inclusions'] as $t) q("INSERT INTO scope_items(package_pk, kind, category, name, sort_order) VALUES (?, 'inclusion', 'Imported', ?, ?)", array($pk, $t, $n++));
        $n = 0;
        foreach (HG_STANDARD_EXCLUSIONS as $t) q("INSERT INTO scope_items(package_pk, kind, category, name, sort_order, is_standard, icon) VALUES (?, 'exclusion', 'Travel', ?, ?, 1, 'ticket')", array($pk, $t, $n++));
        foreach ($p['exclusions'] as $t) q("INSERT INTO scope_items(package_pk, kind, category, name, sort_order) VALUES (?, 'exclusion', 'Imported', ?, ?)", array($pk, $t, $n++));
        q('INSERT INTO package_seo(package_pk, meta_title, meta_description, canonical) VALUES (?,?,?,?)', array($pk, $p['page_title'], $p['description'], $site . $p['url']));
        if ($p['image'] && is_file(site_path($p['image']))) {
            $rel = 'site:' . $p['image'];
            $mid = qv('SELECT media_id FROM media WHERE file_path = ?', array($rel));
            if (!$mid) {
                $info = @getimagesize(site_path($p['image']));
                q('INSERT INTO media(file_path, mime, width, height, bytes, destination, created_at) VALUES (?,?,?,?,?,?,?)', array(
                    $rel, $info ? $info['mime'] : '', $info ? $info[0] : 0, $info ? $info[1] : 0, filesize(site_path($p['image'])), $p['group'], now()));
                $mid = $db->lastInsertId();
            }
            q("INSERT INTO package_media(package_pk, media_id, role) VALUES (?, ?, 'featured')", array($pk, $mid));
        }
    }
    foreach ($rates['versions'] as $v) {
        if (!isset($pkByslug[$v['slug']])) continue;
        q('INSERT INTO rate_versions(package_pk, version, base_price, currency, price_unit, valid_from, valid_until, rate_status, price_notes, created_at, approved_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)', array(
            $pkByslug[$v['slug']], $v['version'], $v['base_price'], $v['currency'], $v['price_unit'], $v['rate_valid_from'], $v['rate_valid_until'], $v['rate_status'], $v['price_notes'] ?? '', $v['rate_updated_at'] ?? now(), $v['rate_status'] === 'approved' ? now() : null));
    }
    foreach ($offers['offers'] as $o) {
        q('INSERT INTO offers(offer_code, name, valid_from, valid_until, terms, status, created_at) VALUES (?,?,?,?,?,?,?)', array($o['offer_code'], $o['title'], $o['valid_from'], $o['valid_until'], $o['terms'] ?? '', $o['status'], now()));
        $op = $db->lastInsertId();
        foreach ($o['packages'] as $s) if (isset($pkByslug[$s])) q('INSERT INTO offer_packages VALUES (?,?)', array($op, $pkByslug[$s]));
    }
    foreach ($cur['tours'] as $slug => $c) {
        if (!isset($pkByslug[$slug])) continue;
        q('INSERT INTO curation(package_pk, priority_rank, featured, homepage_featured, search_featured, seasonal_featured, speciality_featured) VALUES (?,?,?,?,?,?,?)', array(
            $pkByslug[$slug], $c['priority_rank'] ?? null, (int) !empty($c['is_featured']), (int) !empty($c['homepage_featured']), (int) !empty($c['search_featured']), (int) !empty($c['seasonal_featured']), (int) !empty($c['speciality_featured'])));
    }
    // Sequences: new packages continue after the highest number in the Package ID mapping; offers after the highest code.
    $maxId = 0;
    foreach ($reg['entries'] as $r) $maxId = max($maxId, (int) $r['package_id']);
    q("UPDATE sequences SET last_value = MAX(last_value, ?) WHERE name = 'package_id'", array($maxId));
    $maxOf = 0;
    foreach ($offers['offers'] as $o) $maxOf = max($maxOf, (int) substr($o['offer_code'], 3));
    q("UPDATE sequences SET last_value = MAX(last_value, ?) WHERE name = 'offer_code'", array($maxOf));
    foreach ($pkByslug as $pk) {
        $p = pkg_load($pk); unset($p['reviews']);
        q('INSERT INTO package_versions(package_pk, package_id, version, sections, note, snapshot, changed_at) VALUES (?,?,?,?,?,?,?)', array($pk, pkg_public_id($p), 1, 'import', 'Imported from the website (include/data/packages.json)', je($p), now()));
    }
    q('UPDATE packages SET published_version = 1');
    q("INSERT INTO activity_log(at, action, new_value, ip) VALUES (?, 'Website packages imported', ?, 'cli')", array(now(), count($pkByslug) . ' packages'));
    $db->commit();
    echo 'Imported ' . count($pkByslug) . " website packages (Package IDs stay proposed until the owner approves the mapping).\n";
}
echo "Done. Start the staging CMS:  php -S 127.0.0.1:8099 -t cms/public cms/public/router.php\n";
