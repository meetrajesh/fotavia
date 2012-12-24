<?php

require '../config.php';

$page = new page;
$page->title('Admin Panel');
$page->header();

?>

<h2>Admin Panel</h2>

<ul>
  <li><a href="/admin/review_photos.php">Review Photos</a>
  <li><a href="/admin/review_feedback.php">Review Feedback</a>
  <li><a href="/admin/login_as.php">Login As</a>
  <li><a href="/admin/phpinfo.php">PHP Info</a>
  <li><a href="/admin/test.php">Test.php</a>
  <li><a href="/admin/sharpen_thumbs.php">Sharpen Latest Thumb</a>
</ul>

<? $page->footer(); ?>
