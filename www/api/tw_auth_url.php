<?php

require '../../config.php';
require 'Epi/EpiTwitter.php';

$twitterObj = new EpiTwitter(TWITTER_KEY, TWITTER_SECRET);
echo trim($twitterObj->getAuthorizationUrl());
exit;

?>
