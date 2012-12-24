<?php

require '../config.php';
$page = new page;
$page->ensure_login();

if (empty($_GET['cid']) || !is_id($_GET['cid'])) {
    redirect('/dash');
}

if (!photo_comment::exists($_GET['cid'])) {
    redirect('/dash');
}

list($pid, $comment_owner_id) = photo_comment::get_photo_and_owner_ids($_GET['cid']);

if (!photo::exists($pid)) {
    $page->quit(_('The comment you are trying to delete does not belong to a valid photo.'));
}

$photo = photo::get($pid);

if (!$photo->can_delete_comment(user::active()->get_id(), $comment_owner_id)) {
    $page->quit(_('You do not have permissions to delete this comment.'));
}

photo_comment::delete($_GET['cid']);

redirect($photo->get_page_url() . '#comments');

?>
