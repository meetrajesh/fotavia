<?php

require '../config.php';

$page = new page;
$page->title(_('Site Temporarily Down'));

if (!IS_SITE_DOWN) {
    $page->smart_redirect();
}

$page->quit(_('Sorry, Fotavia is temporarily down for maintenance and updates. Please check back in a few hours.<br/><br/>
 We should be up and running in no time. And who knows, we might even have some snazzy new features when you\'re back!'));

$page->header();

?>
