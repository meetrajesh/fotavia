#!/usr/bin/php
<?php

chdir(dirname(__FILE__));
require '../config.php';

$user_ids = db::col_query('SELECT user_id FROM users WHERE sent_first_reminder=0 AND status != %d', user::REJECTED);

foreach ($user_ids as $user_id) {
    $user = user::get($user_id);
    $user->send_first_reminder();
}

// log
printf("\n%s: first_reminder.php cron job complete\n", date('c'));
printf("attempted to send email to user ids: %s\n", implode(', ', $user_ids));
printf("%s\n\n", str_repeat('=', 72));
exit;

?>
