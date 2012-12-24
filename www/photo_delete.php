<?php

require '../config.php';

$page = new page;
$page->ensure_login();

// 1. get the currently logged in user
$user = user::active();

// 2. check if the photo exists
if (!photo::exists($_GET['pid'])) {
    redirect('/dash');
}

// 3. get a photo object
$photo = photo::get($_GET['pid']);

// 4. check if logged in user has permissions to delete this photo
if ($user->get_id() != $photo->get_owner_id()) {
    redirect('/dash');
}

// set the page title
$page->title(_('Delete Photo'));

if ($page->is_post()) {
    if (isset($_POST['btn_cancel'])) {
        redirect_ref($_POST['ref']);
    } elseif (isset($_POST['btn_delete'])) {
        $title = $photo->get_title();
        $photo->delete();
        if (empty($title)) {
            $page->msg = _('Your photo has been successfully deleted from our system.');
        } else {
            $page->msg = spf(_('Your photo "%s" has been successfully deleted from our system.'), hsc($title));
        }
        $page->quit();
    }

}

$page->header();
$page->feedback();

?>

<h2><?=hsc($photo->get_title())?></h2>

<form id="delete" action="<?=$page->get_form_action()?>" method="post">
  <img src="<?=$photo->url('rss')?>" class="silver_frame">
  <h3>Are you sure you want to delete this photo? This action cannot be undone!</h3>
  <input type="submit" name="btn_delete" value="Delete" />
  <input type="submit" name="btn_cancel" value="Cancel" />
  <input type="hidden" name="ref" value="<?=$_SERVER['HTTP_REFERER']?>" />
</form>

<? $page->footer(); ?>
