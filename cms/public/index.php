<?php
/**
 * Holiday Guru Travel — Tour Package CMS front controller.
 * A separate application from the public website: it has its own shell and never includes the
 * website's header, footer, utility bar, support widget or cookie consent.
 */
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/packages.php';
require __DIR__ . '/../src/media.php';
require __DIR__ . '/../src/ui.php';
require __DIR__ . '/../src/routes.php';

header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

cms_session();
cms_dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
