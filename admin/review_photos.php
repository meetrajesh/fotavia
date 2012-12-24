<?php
include '../config.php';

$page = new page;
$page->title(_('Review New Photos'));

if ($page->is_post()) {
    $p = photo::get($_POST['photo_id']);
    if (isset($_POST['btn_approve'])) {
        $p->approve();
    } elseif (isset($_POST['btn_is_good'])) {
        // automatically approves the photo as well
        $p->mark_as_good();
    } elseif (isset($_POST['btn_reject'])) {
        $p->reject();
    }
    redirect($_SERVER['REQUEST_URI']);
}

$photo_id = photo::get_next_photo_for_review();

if (!$photo_id) {
    $page->msg = spf(_('No new photos to review! Review some <a href="%s">feedback</a> instead?'), '/admin/review_feedback.php');
    $page->quit();
} else {
    $p = photo::get($photo_id);
}

$page->header();

?>

<form id="photo_review" class="review" action="<?=$page->get_form_action()?>" method="post">
  <div id="photo_info">
    <p>
      <label>Photo Id:</label> <?=$p->get_id()?><br/>
      <label>User:</label> <?=spf('%d (%s)', $p->get_owner_id(), $p->owner()->get_profile_link())?><br/>
      <label>Caption:</label> <?=hsc($p->get_title())?><br/>
      <label>Uploaded On:</label> <?=std_datetime($p->get_stamp())?><br/>
      <label>Page Url:</label><br/><a href="<?=$p->get_page_url()?>"><?=$p->get_page_url()?></a><br/>
    </p>
    <p>
      <label>Description:</label><br/>
      <?=$p->get_formatted_text()?>
    </p>
  </div>

  <center>
    <p><a href="<?=$p->get_page_url()?>"><?=$p->img_tag('rss')?></a></p>
    <br class="clear">
    <p>
      <input type="hidden" name="photo_id" value="<?=$p->get_id()?>" />
      <input type="submit" name="btn_approve" value="Approve" />
      <input type="submit" name="btn_is_good" value="Is Good" />
      <input type="submit" name="btn_reject" value="Reject" />
    </p>
  </center>

</form>

<? $page->footer() ?>
