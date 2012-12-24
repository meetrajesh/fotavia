<?php

require '../config.php';

$page = new page();
$page->ensure_login();

// get the currently logged in user
$user = user::active();

// Check page
if (empty($_GET['pid']) || !is_id($_GET['pid'])) {
    $page->quit(_('There is a problem trying to edit the photo'));
}

$photo = photo::get($_GET['pid']);

// check if logged in user has permissions to edit this photo
if ($user->get_id() != $photo->get_owner_id()) {
    $page->quit(_('Sorry you do not have permissions to edit this photo.'));
}

$page->title(_('Edit Photo'), $photo->get_title());

if ($page->is_post()) {
    $_POST = array_map('trim', $_POST);
    if (empty($_POST['title'])) {
        $page->err = _('Please enter a caption for your photo.');
    } else {
        $photo->save_details($_POST['title'], $_POST['text']);
        redirect($photo->get_page_url());
    }
}

$page->add_script('jquery-1.3.2.min.js');
$page->add_script('editphoto.js');
$page->add_script('facebook.js');
$page->add_script('http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php');

$page->header();
$page->feedback();

?>

<form class="photo_modify" action="<?=$page->get_form_action()?>" method="post" enctype="multipart/form-data">
  <h2><?=hsc($photo->get_title())?></h2>

  <div class="photo_preview">
    <a href="<?=$photo->get_page_url()?>">
      <img class="silver_frame" alt="<?=hsc($photo->get_title())?>" src="<?=$photo->url('rss')?>" />
    </a>
    <p><a href="/photo/delete/<?=$photo->get_id()?>">Delete this photo?</a></p>
  </div>
  <p>
    <label for="photo_title_field">Caption: <span class="help_text">(a short, one-line title to describe your photo)</span></label>
    <input id="photo_title_field" name="title" type="text" size="60" maxlength="100" value="<?=pts('title', $photo->get_title() )?>" />
  </p>
  <p>
    <label for="photo_text_field">Description: <span class="help_text">(where did you take this photo? what were you thinking? what did you learn?)</span></label>
    <textarea id="photo_text_field" name="text" rows="8" cols="45"><?=pts('text', $photo->get_text() )?></textarea>
  </p>
  <p>
    <input type="submit" name="btn_submit" value="Submit" />
  </p>
</form>

<? $page->footer(); ?>
