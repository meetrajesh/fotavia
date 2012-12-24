#!/usr/bin/php
<?php

chdir(dirname(__FILE__));
require '../config.php';

$photos_pending = db::result_query('SELECT COUNT(*) FROM photos WHERE status=%d', photo::PENDING);
$feedbacks_pending = db::result_query('SELECT COUNT(*) FROM feedback_form WHERE is_reviewed=0');

if ($photos_pending + $feedbacks_pending != 0) {
    $email = new email('reviewreminder');
    $email->assign('NUM_PHOTOS_PENDING', $photos_pending);
    $email->assign('NUM_FEEDBACKS_PENDING', $feedbacks_pending);
    $email->assign('PHOTO_REVIEW_URL', BASE_URL . '/admin/review_photos.php');
    $email->assign('PHOTO_FEEDBACK_REVIEW_URL', BASE_URL . '/admin/review_feedback.php');
    $email->send(ADMIN_EMAIL);
}

// log
printf("\n%s: review_reminder.php cron job complete\n", date('c'));
printf("photos pending: %d\n", $photos_pending);
printf("feedbacks pending: %d\n", $feedbacks_pending);
printf("%s\n", str_repeat('=', 72));
exit;

?>
