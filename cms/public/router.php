<?php
// Router for PHP's built-in server (staging):  php -S 127.0.0.1:8099 -t cms/public cms/public/router.php
// Static CMS assets are served directly; everything else goes through index.php.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, '/cms-assets/') === 0 && is_file(__DIR__ . $path)) return false;
require __DIR__ . '/index.php';
