<?php
include '../config.php';

$page = new page;
$page->title(_('Review User Feedback'));

if ($page->is_post()) {

    foreach (array_keys($_POST['msg']) as $id) {

        $reviewed = isset($_POST['reviewed'][$id]) && $_POST['reviewed'][$id] == 'on' ? 1 : 0;
        $sql = 'UPDATE feedback_form SET admin_comment="%s", is_reviewed=%d WHERE id=%d';
        db::query($sql, $_POST['comment'][$id], $reviewed, $id);

    }

    redirect($_SERVER['REQUEST_URI']);
}

$feedback_msgs = feedback::get_top_n(30, isset($_GET['include_reviewed']));

if ($feedback_msgs->num_rows == 0) {
    $page->msg = spf(_('No new comments to review! Show <a href="%s">reviewed comments</a>?'), $page->get_form_action() . '?include_reviewed=1');
    $page->quit();
}

$page->header();

?>

<form id="feedback_review" class="review" action="<?=$page->get_form_action()?>" method="post">

<input type="submit" name="btn_save" value="Save" />
<div class="line"></div>

<? while ($row = $feedback_msgs->fetch_assoc()) { ?>

  <p>
    <label>Name:</label> <?=hsc($row['name'])?><br/>
    <label>Logged in as:</label> <?=hsc($row['logged_in_userid'])?> <?=!empty($row['logged_in_userid']) ? spf('(%s)', user::get($row['logged_in_userid'])->get_profile_link()) : ''?><br/>
    <label>Email:</label> <a href="mailto:<?=hsc($row['email'])?>"><?=hsc($row['email'])?></a><br/>
    <label>Posted:</label> <?=std_datetime($row['stamp'])?> UTC<br/>
  </p>

  <p>
    <textarea rows="5" cols="40" name="msg[<?=$row['id']?>]" readonly="readonly"><?=hsc($row['msg'])?></textarea>
    <textarea rows="5" cols="40" name="comment[<?=$row['id']?>]"><?=hsc($row['admin_comment'])?></textarea>
  </p>

  <p>
    <input id="reviewed_<?=$row['id']?>" name="reviewed[<?=$row['id']?>]" type="checkbox"<?=$row['is_reviewed'] ? ' checked="checked"' : ''?> />
    <label for="reviewed_<?=$row['id']?>">Reviewed?</label>
  </p>

  <div class="line"></div>

<? }
?>

<input type="submit" name="btn_save" value="Save" />

</form>

<? $page->footer() ?>
