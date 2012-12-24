<?php

require '../config.php';
$page = new page;
$page->ensure_login();

if (!empty($_POST['term'])) {
    $term = trim($_POST['term']);
}

$page->title(_('Find User'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');
$page->header();

if (empty($term)) { ?>
  <h2>Find User</h2>
<? } else { ?>
  <h2>Find User <?=hsc($term)?></h2>
<? } ?>

<form id="search" method="post" action="<?=$page->get_form_action()?>">
  <label for="term">Search all users for:</label>
  <input type="text" id="term" name="term" maxlength="50" value="<?=pts('term')?>" />
  <input type="submit" name="btn_submit" value="Go!" />
</form>

<? if (!empty($term)) { ?>

  <h2>Search Results</h2>
  <? $user_ids = user::search($term);
    if (count($user_ids) == 0) { ?>
      <p><em>No users match your seach terms.</em></p>
 <? } else {
        foreach ($user_ids as $user_id) {
            $u = user::get($user_id); 
            $desc = '';
            if (strlen($u->get_location())) {
                $desc = $u->get_location();
            } elseif (strlen($u->get_website())) {
                $desc = $u->get_website();
            } elseif (strlen($u->get_bio())) {
                $desc = $u->get_bui();
            } ?>
            <div class="user_entry">
              <?=$u->headshot_pic_link()?>
              <div class="user_details">
                <span class="user_name"><?=hsc($u->get_name())?></span> (<?=$u->get_profile_link()?>)<br/>
                <?=!empty($desc) ? hsc($desc) . '<br/>' : ''?>
                <a href="<?=$u->get_profile_url()?>">View Profile</a><br/>
                <? if (!in_array($u->get_id(), user::active()->get_leaders())) { ?>
                  <a href="/<?=hsc($u->get_username())?>/follow/">Follow <?=hsc($u->get_name())?></a>
                <? } else { ?>
                  You are following <?=hsc($u->get_name())?>
                <? } ?>
              </div>
            </div>
        <?
       }
   }
}

$page->footer(); ?>
