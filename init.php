<?php

// record the start time for diagnostic info in the footer
define('START_TIME', microtime(true));

error_reporting(E_ALL | E_STRICT | E_NOTICE | E_WARNING);
ini_set('date.timezone', 'GMT');
date_default_timezone_set('GMT');
session_start();

define('DIR_SEP', DIRECTORY_SEPARATOR);
define('BASE_DIR', dirname(__FILE__) . DIR_SEP);
define('BASE_URL', 'http://www.fotavia.com');

// standardize $_SERVER['REQUEST_URI']
if (isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = str_replace(BASE_URL, '', $_SERVER['REQUEST_URI']);
}

require BASE_DIR . 'functions.php';

function __autoload($class) {
    require BASE_DIR . 'lib' . DIR_SEP . strtolower($class) . '.php';
}

// define include path
set_include_path(dirname(__FILE__) . DIR_SEP . 'lib');

// user config
define('USERNAME_MIN_LENGTH', 5);
define('USERNAME_MAX_LENGTH', 25);
define('PASSWORD_MIN_LENGTH', 4);

// general site config
define('SITE_EMAIL', 'noreply@fotavia.com');
define('ADMIN_EMAIL', 'raj@fotavia.com');
define('ADMIN_UID', 1);
define('NEWSFEED_NUM_ENTRIES', 10);

// photo config
define('MAX_UPLOAD_SIZE', 1024*1024*20); // 20mb, also change in apache config
// quality ranges from 0 (worst quality, smaller file) to 100 (best quality,
// biggest file). the default is about 75
define('DEFAULT_PHOTO_QUALITY', 85);
// lower-limit on area of the smallest image that can be uploaded to the site
define('MIN_UPLOAD_AREA', 1.5*1e6);
// filename prefix to give to deleted photos
define('REJECTED_PHOTO_PREFIX', 'dltd_b7ad_');
// minimum time user's have to wait before they can upload another picture (4 hours)
define('MIN_SECS_BETWEEN_UPLOADS', 4*3600);

// feed config
define('PRIVATE_FEED_LENGTH', 30);
define('PUBLIC_FEED_LENGTH', 10);

// cookie config
define('COOKIE_NAME', 'fotavia');

// site down check
if (IS_SITE_DOWN && isset($_SERVER['REQUEST_URI']) && !is_sitedown_page()) {
    $_SESSION['r'] = $_SERVER['REQUEST_URI'];
    redirect('/sitedown');
    exit;
}

// ip ban check
if (!empty($_SERVER['REMOTE_ADDR']) && db::has_row('SELECT null FROM blocked_ips WHERE ip="%s"', $_SERVER['REMOTE_ADDR'])) {
    $page = new page;
    $page->title(_('Page Unavailable'));
    $page->quit(_('Sorry, Fotavia is currently unavailable. Please try again tomorrow.'));
    exit;
}

// attemp auto-login
user::attempt_auto_login();

?>
