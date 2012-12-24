<?php

chdir(dirname(__FILE__));
require '../config.php';

if (empty($argv[1])) {
    die(spf('Usage: php %s <uid>' . "\n", basename(__FILE__)));
}

$uid = trim($argv[1]);

if (!is_id($uid)) {
    die('Invalid uid: ' . $uid . "\n");
}

user::get($uid)->reject();

?>
