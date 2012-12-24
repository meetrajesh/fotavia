<?php

require '../config.php';

$page = new page;
$page->ensure_login();

if (empty($_GET['pid']) || !is_id($_GET['pid']) || !photo::exists($_GET['pid'])) {
    redirect('/dash');
}

$photo = photo::get($_GET['pid']);
$body = trim($_POST['comment_body']);

if (strlen($body) > 600) {
    $page->quit(spf(_('Your photo comment is too long! Please keep it to %d characters.'), 600));
}

$cid = 0;
if (!empty($body)) {
    $cid = $photo->add_comment(user::active()->get_id(), $body);
}

redirect($photo->get_page_url() . '#comment_' . $cid);

?>
