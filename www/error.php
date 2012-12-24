<?php

require '../config.php';

$page = new page;
$page->title(_('Error'));

$page->quit(_('Sorry, we had an unexpected error occur. If you can manage to make it happen consistently, please do <a href="/feedback">let us know</a>!'));

$page->header();

?>
