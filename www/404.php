<?php

require '../config.php';

$page = new page;
$page->title(_('Page Not Found'));

$page->quit(_('Sorry the page/photo you were trying to access was not found.'));

$page->header();

?>
