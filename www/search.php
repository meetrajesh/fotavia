<?php

require '../config.php';
$page = new page;
$page->ensure_login();

$page->title(_('Search'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');
$page->header(); ?>

<h2>Search</h2>

<form id="search" method="post" action="/search/users">
  <label for="term">Search all users for:</label>
  <input type="text" id="term" name="term" maxlength="50" value="<?=pts('term')?>" />
  <input type="submit" name="btn_submit" value="Go!" />
</form>

<br>

<form id="search" method="post" action="/search/photos">
  <label for="term">Search your photos for:</label>
  <input type="text" id="term" name="term" maxlength="50" value="" />
  <input type="submit" name="btn_submit" value="Go!" />
</form>

<? $page->footer(); ?>
