<?php

require '../../config.php';
$page = new page;
$page->ensure_login();

if (isset($_POST['fbid']) && is_id($_POST['fbid'])) {
    user::active()->set_pref('fbid', $_POST['fbid']);
}

?>
