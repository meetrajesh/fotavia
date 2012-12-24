<?php

if (empty($argv[2])) {
    die('Usage: snr.php <search> <replace>');
}

modify_files_in_dir('.', $argv[1], $argv[2]);

function modify_files_in_dir($dir, $s, $r) {

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
            $func($file, $s, $r);
        } else {
            $contents = file_get_contents($file);
            if (false !== strpos($contents, $s)) {
                file_put_contents($file, str_replace($s, $r, $contents));
            }
        }

    }

}

?>
