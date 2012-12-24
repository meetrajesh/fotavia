#!/usr/bin/php
<?php
$blist = array(
    '.hg/',
    '.hgignore',
    'www/.htaccess',
    'config.php',
    'dbsql/',
    'admin/pushtoprod.php',
);

error_reporting(E_ALL);
ini_set('display_errors', true);
chdir(dirname(__FILE__));

define('DIR_SEP', DIRECTORY_SEPARATOR);
define('SOURCE_DIR', dirname(dirname(__FILE__)) . DIR_SEP);
define('TARGET_DIR', dirname(dirname(dirname(__FILE__))) . DIR_SEP . 'prod' . DIR_SEP);
define('YUI_COMPRESSOR_LOC', '~/bin/yuicompressor-2.4.2.jar');
#define('YUI_COMPRESSOR_LOC', 'c:\unix\yuicompressor-2.4.2.jar');
define('YUI_COMPRESS_CMD', 'java -jar ' . YUI_COMPRESSOR_LOC . ' "%s" -o "%s"');

// create the target dir if one doesn't exist
if (!is_dir(TARGET_DIR)) {
    mkdir(TARGET_DIR, 0755);
}

copy_recursive(SOURCE_DIR);
function copy_recursive($dir) {

    $d = opendir($dir);
    while ($file = readdir($d)) {

        // 1. if the file starts with . or hash, or ends with ~ or has .orig extension, ignore it
        if (in_ignore_list($file)) {
            continue;
        }

        // 2. build the absolute source and target paths
        $file = gtrim($dir) . DIRECTORY_SEPARATOR . $file;
        $file = str_replace(SOURCE_DIR, '', $file);
        $source = SOURCE_DIR . str_replace('/', DIR_SEP, $file);
        $target = TARGET_DIR . str_replace('/', DIR_SEP, $file);

        #printf('source %s, dest %s' . "\n", $source, $target);

        // 3. if this file is in the blacklist, ignore it
        if (in_blist($source)) {
            continue;
        }

        // 4. if its a directory recurse into it
        if (is_dir($source)) {
            echo 'Creating folder: ' . $target . "\n";
            mkdir_if_not_exists($target);
            copy_recursive($source);
            continue;
        }

        // 5. compress files if necessary
        if (should_compress($source)) {
            echo 'Compressing ' . $file . "...\n";
            shell_exec(sprintf(YUI_COMPRESS_CMD, $source, $target));
       } else {
            // 6. otherwise, just copy the file over
            copy($source, $target);
        }
        echo 'Copying ' . $file . "...\n";
        chmod($target, 0644);

    }

}

function gtrim($path) {
    return rtrim($path, '\\/');
}

function mkdir_if_not_exists($dir) {
    // recursively create a dir and set its permission if it doesn't exist
    if (!is_dir($dir)) {
        return mkdir($dir, 0755, true);
    }
}

function in_ignore_list($file) {
    // starts with . or hash, or ends in ~, or has .orig extension
    return preg_match('/^[\.#]/', $file) || preg_match('/(~|\.orig)$/', $file);
}

function in_blist($file) {
    global $blist;
    foreach ($blist as $blist_file) {
        if (false !== strpos(str_replace(DIR_SEP, '/', $file), '/' . gtrim($blist_file))) {
            return true;
        }
    }
    return false;
}

function should_compress($file) {
    // compress .js files but only if they aren't already compressed (ends with
    // .pack.js) and arent' jquery files (to preserve copyright)
    return substr($file, -3) == '.js' &&
        false === strpos($file, '.pack.js') &&
        false === strpos($file, 'jquery');
}

?>
