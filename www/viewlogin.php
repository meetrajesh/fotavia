<?php

require '../config.php';

// the page you're redirected to before redirecting to the login page
$_SESSION['r'] = $_SERVER['HTTP_REFERER'] . '#comments';

redirect('/login');

?>
