<?php
/** Login / logout. Passwords are hashed (password_hash); failed attempts are rate-limited per email. */

function login_get($error = '')
{
    if (cms_user()) redirect('/');
    cms_render('login', array('error' => $error, 'email' => post('email')));
}

function login_post()
{
    csrf_check();
    $email = strtolower(post('email'));
    $since = gmdate('Y-m-d H:i:s', time() - 900);
    $fails = (int) qv('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ok = 0 AND at > ?', array($email, $since));
    if ($fails >= 5) return login_get('Too many attempts. Wait 15 minutes and try again.');
    $u = q1('SELECT * FROM users WHERE email = ? AND active = 1', array($email));
    $ok = $u && password_verify((string) post('password'), $u['password_hash']);
    q('INSERT INTO login_attempts(email, at, ok) VALUES (?,?,?)', array($email, now(), (int) $ok));
    if (!$ok) return login_get('The email or password is not correct.');
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $u['user_id'];
    unset($_SESSION['csrf']);
    q('UPDATE users SET last_login_at = ? WHERE user_id = ?', array(now(), $u['user_id']));
    $next = get('next');
    redirect(preg_match('~^/[^/\\\\]~', $next) || $next === '/' ? $next : '/');
}

function logout_post()
{
    $_SESSION = array();
    session_destroy();
    redirect('/login');
}
