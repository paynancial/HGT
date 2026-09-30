<?php
/**
 * CMS bootstrap: configuration, database, session, CSRF, small helpers.
 * The CMS is a separate application: it does not include the website's header, footer or layout.
 */
declare(strict_types=1);

define('CMS_ROOT', dirname(__DIR__));

require_once __DIR__ . '/permissions.php';

function cms_config($key = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $file = getenv('HG_CMS_CONFIG') ?: CMS_ROOT . '/config.php';
        if (!is_file($file)) $file = CMS_ROOT . '/config.example.php';
        $cfg = require $file;
    }
    return $key === null ? $cfg : (isset($cfg[$key]) ? $cfg[$key] : null);
}

function cms_db(): PDO
{
    static $db = null;
    if ($db === null) {
        $db = new PDO(cms_config('dsn'), cms_config('db_user'), cms_config('db_pass'), array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ));
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $db->exec('PRAGMA foreign_keys = ON');
            $db->exec('PRAGMA journal_mode = WAL');
            $db->exec('PRAGMA busy_timeout = 3000');
        }
    }
    return $db;
}

function cms_migrate(PDO $db)
{
    $db->exec(file_get_contents(CMS_ROOT . '/schema/sqlite.sql'));
    foreach (array('package_id', 'offer_code') as $s) {
        $db->prepare('INSERT OR IGNORE INTO sequences(name, last_value) VALUES (?, 0)')->execute(array($s));
    }
}

function q($sql, array $args = array())
{
    $st = cms_db()->prepare($sql);
    $st->execute($args);
    return $st;
}
function q1($sql, array $args = array())
{
    $r = q($sql, $args)->fetch();
    return $r === false ? null : $r;
}
function qv($sql, array $args = array())
{
    $v = q($sql, $args)->fetchColumn();
    return $v === false ? null : $v;
}

function now() { return gmdate('Y-m-d H:i:s'); }
function today() { return getenv('HG_CMS_TODAY') ?: date('Y-m-d'); }

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function jd($s) { $v = json_decode((string) $s, true); return is_array($v) ? $v : array(); }
function je($v) { return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }

function inr($n)
{
    // Indian digit grouping: 1,23,456
    $n = (string) (int) $n;
    if (strlen($n) <= 3) return '₹' . $n;
    $last3 = substr($n, -3);
    $rest = substr($n, 0, -3);
    $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
    return '₹' . $rest . ',' . $last3;
}
function dmy($d) { return $d ? date('d M Y', strtotime($d)) : ''; }

/* ---------- session, auth, CSRF ---------- */

function cms_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('hgcms');
    session_set_cookie_params(array(
        'lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ));
    session_start();
}

function cms_user()
{
    static $u = false;
    if ($u === false) {
        $u = null;
        if (!empty($_SESSION['uid'])) {
            $u = q1('SELECT user_id, email, name, role FROM users WHERE user_id = ? AND active = 1', array($_SESSION['uid']));
        }
    }
    return $u;
}
function uid() { $u = cms_user(); return $u ? (int) $u['user_id'] : null; }
function role() { $u = cms_user(); return $u ? $u['role'] : ''; }

function csrf_token()
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check()
{
    $t = isset($_POST['_csrf']) ? $_POST['_csrf'] : (isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '');
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(400);
        exit('Your session expired. Go back, reload the page and try again.');
    }
}

function flash($msg = null, $kind = 'ok')
{
    if ($msg !== null) { $_SESSION['flash'][] = array($kind, $msg); return null; }
    $f = isset($_SESSION['flash']) ? $_SESSION['flash'] : array();
    unset($_SESSION['flash']);
    return $f;
}

function redirect($to)
{
    header('Location: ' . $to, true, 303);
    exit;
}

function deny($msg = 'Your role does not allow this action.')
{
    http_response_code(403);
    cms_render('error', array('title' => 'Not allowed', 'message' => $msg));
    exit;
}

function need($module, $level = 'view')
{
    if (!hg_can(role(), $module, $level)) deny();
}
function need_action($action)
{
    if (!hg_may(role(), $action)) deny();
}

function post($k, $default = '')
{
    $v = isset($_POST[$k]) ? $_POST[$k] : $default;
    return is_string($v) ? trim($v) : $v;
}
function get($k, $default = '')
{
    $v = isset($_GET[$k]) ? $_GET[$k] : $default;
    return is_string($v) ? trim($v) : $v;
}

/** Record an activity. Insert-only. */
function cms_log($action, $pk = null, $field = '', $old = '', $new = '')
{
    $pid = $pk ? qv('SELECT package_id FROM packages WHERE package_pk = ?', array($pk)) : null;
    q('INSERT INTO activity_log(at, user_id, action, package_pk, package_id, field, old_value, new_value, ip) VALUES (?,?,?,?,?,?,?,?,?)', array(
        now(), uid(), $action, $pk, $pid, $field, mb_substr((string) $old, 0, 2000), mb_substr((string) $new, 0, 2000),
        isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli',
    ));
}

function cms_render($view, array $vars = array())
{
    extract($vars, EXTR_SKIP);
    require CMS_ROOT . '/src/views/' . $view . '.php';
}

function cms_is_staging() { return cms_config('env') !== 'production'; }
