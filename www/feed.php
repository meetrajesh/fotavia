<?php

// don't need login to access this page
require '../config.php';

// bind the specified user, otherwise, bind the currently logged-in user
$is_own_feed = false;
if (!empty($_GET['key']) && !empty($_GET['user'])) {
    $cryptstring = str_decrypt(FEED_SECRET, $_GET['key']);
    list($uid) = explode('-', $cryptstring, 2);
    if (is_id($uid)) {
        $tmp_user = user::get($uid);
        if ($tmp_user->feed_secret_key() == $_GET['key']) {
            $user = $tmp_user;
            $is_own_feed = true;
        }
    }
} elseif (!empty($_GET['user'])) {
    $user = user::get_from_username($_GET['user']);
} elseif (user::has_active()) {
    // bind the currently logged-in user
    $user = user::active();
    $is_own_feed = true;
    redirect(spf('/feed/%s/%s', $user->get_username(), $user->feed_secret_key()));
} else {
    // not logged in
    $page = new page;
    $page->ensure_login();
    if (user::has_active()) {
        $user = user::active();
        redirect(spf('/feed/%s/%s', $user->get_username(), $user->feed_secret_key()));
    }
}

// if no user could be bound, error out
if (!isset($user)) {
    die('Sorry that user does not exist within our site.');
}

if ($is_own_feed) {
    $feed_source = $user->get_private_feed(PRIVATE_FEED_LENGTH);
} else {
    $feed_source = $user->get_public_feed(PUBLIC_FEED_LENGTH);
}

// the rss writer obj
$rss = new rss;

foreach ($feed_source as $photo_id) {

    $props = array();
    $photo = photo::get($photo_id);

    $props['title']       = $photo->get_title();
    $props['link']        = $photo->get_page_url();
    $props['guid']        = $photo->get_page_url();
    $props['pubDate']     = date(DATE_RSS, $photo->get_stamp());
    $props['description'] = $photo->get_rss_body();

    $rss->add_item($props);

}

// ... RSS specifications

$rss->specification = '2.0';
$rss->stylesheet = BASE_URL . '/rss.xsl';
$rss->about = $_SERVER['REQUEST_URI'];
$rss->guid_ispermalink = true;

// define the properties of the channel

$props = array();

$props['title'] = $is_own_feed ? _('My Fotavia Private Feed') : spf(_("%s's Photos on Fotavia"), $user->get_name());
$props['link'] = spf('%s/%s', BASE_URL, ltrim($user->get_profile_url(), '/'));
//$props['description'] = 'Fotavia';
$props['language'] = 'en-us';
$props['lastBuildDate'] = date(DATE_RSS);
$rss->add_channel($props);

// channel logo
$props = array();
$props['url'] = BASE_URL . '/img/favicon.jpg';
$props['link'] = BASE_URL;
$props['title'] = $is_own_feed ? _('My Fotavia Private Feed') : spf(_("%s's Photos on Fotavia"), $user->get_name());
$rss->add_image($props);

header('Content-type: text/xml');
echo $rss->get_output();

?>
