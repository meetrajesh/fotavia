<?php

require '../../config.php';

if (empty($_POST['pid']) || !is_id($_POST['pid']) || !photo::exists($_POST['pid'])) {
    return;
}

$photo = photo::get($_POST['pid']); 
$active_user_id = user::has_active() ? user::active()->get_id() : 0;

?>

<h3>Comments</h3>

<? 
foreach ($photo->get_comments() as $c) {
    $u = $c->owner(); ?>
    <a name="comment_<?=$c->id?>"></a>
    <div class="comment_entry">
      <?=$u->headshot_pic_link()?>
      <div class="comment_details">
        <span class="user_name"><?=hsc($u->get_name())?></span> (<?=$u->get_profile_link()?>)<br/>
        <div class="comment_stamp">
          Posted <?=fuzzydate($c->stamp)?> (<a href="<?=$c->get_permalink()?>">link</a>)
          <? if ($photo->can_delete_comment($active_user_id, $c->owner_id)) { ?>
            <span class="comment_delete">(<a href="/comment/delete/<?=$c->id?>">delete</a>)</span>
          <? } ?>
        </div>
        <div class="comment_body"><?=$c->get_formatted_body()?></div>
      </div>
    </div>
<?
}
?>

<? if (user::has_active()) { ?>

  <form id="comment_form" action="/comment/add/<?=$photo->get_id()?>" method="post">
    <p>
      <label for="comment_body">Add your comment:</label>
      <textarea id="comment_body" name="comment_body" rows="5"><?=pts('comment_body')?></textarea>
    </p>
    <p>
      <input type="submit" name="btn_submit" value="Post Comment" />
  </form>

<? } else { ?>
     <a href="/viewlogin.php">Login</a> to add your comment..
<? } ?>
