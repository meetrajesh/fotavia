<?php

require '../config.php';
$page = new page;
$page->ensure_login();

// bind the specified user
if (!empty($_GET['user'])) {
    $leader = user::get_from_username($_GET['user']);
}

if (!isset($leader)) {
    $page->quit(_('Sorry that user does not exist within our site.'));
}

user::active()->follow($leader->get_id());
$_SESSION['feedback_msg'] = spf(_('You are now following %s.'), $leader->get_full_profile_link());
redirect_ref($leader->get_profile_url());

$page->title(spf(_('Follow %s'), $leader->get_username()));

?>
