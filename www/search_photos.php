<?php

require '../config.php';
$page = new page;
$page->ensure_login();

if (!empty($_POST['term'])) {
    $term = trim($_POST['term']);
}

$page->title(_('Search Photos'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');
$page->header();

if (empty($term)) { ?>
  <h2>Search Your Photos</h2>
<? } else { ?>
  <h2>Search Your Photos for <?=hsc($term)?></h2>
<? } ?>

<form id="search" method="post" action="<?=$page->get_form_action()?>">
  <label for="term">Search my photos for:</label>
  <input type="text" id="term" name="term" maxlength="50" value="<?=pts('term')?>" />
  <input type="submit" name="btn_submit" value="Go!" />
</form>

<? if (!empty($term)) { ?>

  <h2>Search Results</h2>
  <? $photo_ids = photo::search(user::active()->get_id(), $term);
  photo::print_photo_entries($photo_ids, _('No photos match your search terms.'));

}

$page->footer(); ?>
