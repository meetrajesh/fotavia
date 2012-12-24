<?php

if (empty($argv[1])) {
    die(sprintf('Usage: php %s <dir>' . "\n", basename(__FILE__)));
}

check_lint($argv[1]);

function check_lint($dir = '.') {
    
    $d = opendir($dir);
    while ($file = readdir($d)) {
    
        // ignore this dir?
        if (preg_match('/^\./', $file)) {
            continue;
        }

        $file = $dir . DIRECTORY_SEPARATOR . $file;

        // if its a directory recurse into it
        if (is_dir($file)) {
            check_lint($file);
            continue;
        }

        // if it ends in .php, run it through php lint
        if (preg_match('/.php$/i', $file)) {
            echo shell_exec('php -l ' . $file);
        }

    }

}

?>
