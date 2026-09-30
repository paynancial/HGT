<?php
$content = '<div class="cms-card cms-errorbox"><h1>' . e($title) . '</h1><p>' . e($message) . '</p><p><a class="cms-btn cms-btn--ghost" href="/">Back to the dashboard</a></p></div>';
if (cms_user()) { cms_render('layout', array('title' => $title, 'crumbs' => array(array('Dashboard', '/'), array($title, null)), 'content' => $content)); }
else { echo '<!doctype html><meta charset="utf-8"><title>' . e($title) . '</title>' . $content; }
