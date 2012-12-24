<?php

require '../../config.php';
require 'Epi/EpiTwitter.php';

$page = new page;
$page->ensure_login();

if (!empty($_GET['oauth_token'])) {

    $twitterObj = new EpiTwitter(TWITTER_KEY, TWITTER_SECRET);
    $twitterObj->setToken($_GET['oauth_token']);
    $token = $twitterObj->getAccessToken();
    user::active()->save_tw_auth($token->oauth_token, $token->oauth_token_secret);
    $_SESSION['feedback_msg'] = _('Your Twitter status will be automatically updated on photo uploads.');

} else {
    $_SESSION['feedback_err'] = _('Oops something went wrong while granting us Twitter read/write privileges. Please try again.');
}

redirect('/settings');

?>
