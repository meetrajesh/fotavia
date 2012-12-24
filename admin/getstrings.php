<?php

chdir(dirname(dirname(__FILE__)));
find_strings('.');

function find_strings($dir) {

    $d = opendir($dir);
    while ($file = readdir($d)) {

        if (preg_match('/^[\.#]/', $file)) {
            continue;
        }

        if (preg_match('/(~|\.orig)$/', $file)) {
            continue;
        }

        $file = $dir . DIRECTORY_SEPARATOR . $file;

        if (realpath($file) == __FILE__) {
            continue;
        }

        if (is_dir($file)) {
            $func = __FUNCTION__;
            $func($file);
        } else {
            $contents = file_get_contents($file);
            preg_match_all("/_\([\"'](.+?)[\"']\)/m", $contents, $matches);
            if (isset($matches[1])) {
                foreach($matches[1] as $val) {
                    echo $val . PHP_EOL;
                }
            }
        }

    }

}

?>
