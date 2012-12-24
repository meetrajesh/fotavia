<?php

chdir(dirname(__FILE__));
require '../config.php';
set_time_limit(0);

if (empty($argv[1])) {
    die(spf('Usage: php %s <photo_id>' . "\n", basename(__FILE__)));
}

$pids = get_range($argv[1]);

foreach ($pids as $pid) {
    if (!is_id($pid)) {
        die('Invalid photo id: ' . $pid . "\n");
    }
    if (!photo::exists($pid)) {
        printf("Could not find photo with id %d\n", $pid);
        continue;
    }
    $photo = photo::get($pid);
    printf("Generating thumbs for photo id %d ..\n", $pid);
    $photo->gen_thumbs(false);
}

?>
