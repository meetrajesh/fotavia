<?php

define('IS_DEV', true);
define('IS_SITE_DOWN', false);
ini_set('display_errors', true);

define('DBHOST', 'localhost');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBNAME', 'fotavia');

// secret keys
define('FORGOT_PASS_SECRET', 'Mj+yNDE=NT:xMzk;NDIwL');
define('FEED_SECRET', '*bQ#+,/f&@- cU:|');
define('COOKIE_SECRET', 'Njc1MDAwNDkzMS4wNg');

define('EMAIL_OPTOUT_SECRET', '9204af3470c2b7a25');
define('PWD_SALT', 'OTEwNjQ2Ljg4Mzg5NDIwMzktYX');

// facebook config
define('FACEBOOK_API_KEY', '');
define('FACEBOOK_API_SECRET', '');

// twitter config
define('TWITTER_KEY', '');
define('TWITTER_SECRET', '');

// bit.ly config
define('BITLY_USERNAME', '');
define('BITLY_API_KEY', '');

// photo dir
define('USER_PHOTO_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'photos');

// temp photo dir
define('TEMP_PHOTO_DIR', USER_PHOTO_DIR . DIRECTORY_SEPARATOR . 'temp');

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'init.php';

?>
