<?php

require '../config.php';

$page = new page();
$page->ensure_login();

// get the currently logged in user
$user = user::active();

$page->title(_('Add a New Photo'));

if ($page->is_post() && isset($_FILES['photo'])) { // Handle temp photo upload

    // check if the user can upload
    if (!$user->can_upload_photo()) {
        $page->quit(_('You have already uploaded a photo for today. Please come back tomorrow to upload another pic!'));
    }

    try {
        $date_stamp = photo::add_temp($user->get_id(), $_FILES['photo']);
    } catch(Exception $e) {
        $page->quit($e->getMessage());
    }

    $preview_url = spf('%s/photos/temp/%s/%d/preview.jpg', BASE_URL, $date_stamp, $user->get_id());

} elseif ($page->is_post() && isset($_POST['date'])) { // Handle adding photo to site
    if (empty($_POST['title'])) {
        $page->err = _('Please enter a caption for your photo.');
    } else {
        $photo_id = photo::add($user->get_id(), $_POST['date'], $_POST['title'], $_POST['text']);
        $photo = photo::get($photo_id);
        redirect($photo->get_page_url());
    }
} else {
    $page->quit(spf(_('There was a problem uploading your photo. <a href="%s">Please try again</a>.'), '/dash'));
}

$page->add_script('jquery-1.3.2.min.js');
$page->add_script('editphoto.js');
$page->add_script('facebook.js');
$page->add_script('http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php');

$page->header();
$page->feedback();

?>

<form class="photo_modify" action="<?=$page->get_form_action()?>" method="post">
  <input type="hidden" name="date" value="<?=$date_stamp?>" />
  <h2>Add New Photo</h2>

  <div class="photo_preview">
    <img class="silver_frame" src="<?=$preview_url?>" />
    <p>Wrong photo? <a href="/dash/">No problem</a></p>
  </div>

  <p>
    <label for="photo_title_field">Caption: <span class="help_text">(a short, one-line title to describe your photo)</span></label>
    <input id="photo_title_field" name="title" type="text" size="60" maxlength="100" />
  </p>
  <p>
    <label for="photo_text_field">Description: <span class="help_text">(where did you take this photo? what were you thinking? what did you learn?)</span></label>
    <textarea id="photo_text_field" name="text" rows="8" cols="45"></textarea>
  </p>
  <p>
    <input type="submit" name="btn_submit" value="Submit" />
  </p>
  <p id="license">You own and keep full rights to the photos you
   upload assuming you took them. You can specify otherwise in your
   photo description. Fotavia reserves the right to take down any photo
   or change your caption and description, but we rarely do that.</p>
</form>

<? $page->footer(); ?>
