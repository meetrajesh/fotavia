#!/usr/bin/php
<?php

chdir(dirname(__FILE__));
require '../config.php';

$user_ids = user::get_inactive_users();

foreach ($user_ids as $user_id) {
    $user = user::get($user_id);
    $user->send_inactive_reminder();
}

// log
printf("\n%s: inactivity_reminder.php cron job complete\n", date('c'));
printf("sent email to user ids %s\n", implode(', ', $user_ids));
printf("%s\n\n", str_repeat('=', 72));
exit;

?>
