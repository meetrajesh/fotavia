<?php

require '../config.php';

// this page does not require login
$page = new page();

// but it does if the user is accessing /profile
if (in_str($_SERVER['REQUEST_URI'], '/profile')) {
    $page->ensure_login();
}

// bound the specified user, otherwise, bind the currently logged-in user
if (empty($_GET['user'])) {
    if (user::has_active()) {
        $user = user::active();
    }
} else {
    $user = user::get_from_username($_GET['user']);
}

// if no user could be bound, error out
if (!isset($user)) {
    $page->quit(_('Sorry we could not find the profile of the user specified.'));
}

$username = hsc($user->get_username());
$is_own_profile = user::has_active() && user::active()->get_id() == $user->get_id();
$total_photos = $user->total_photos();

// set the page title
$page->title(spf(_("%s's Profile"), $user->get_name()));

// add the rss feed for this user
$feed['url'] = spf('%s/%s/%s/', BASE_URL, $user->get_username(), 'feed');
$feed['title'] = spf(_("%s's Photos on Fotavia"), $user->get_name());
$page->set_rss_feed($feed['url'], $feed['title']);

$page->header();
$page->feedback();

?>

<h2><?=hsc($user->get_name())?> (<?=$username?>)</h2>

<? $bio = $user->get_bio();
   $loc = $user->get_location();
   $web = $user->get_website(); ?>

<?=$user->headshot_pic_link('profile_headshot_pic');?>

<? if ($total_photos > 0) { ?>
  <p><a href="<?=spf('/%s/photos/', hsc($user->get_username()))?>">View all photos by <?=hsc($user->get_username())?></a></p>
<? } ?>

<? if ($is_own_profile || !empty($bio)) { ?>
  <div class="user_info"><strong>Bio:</strong> <?=hsc($bio)?><? if ($is_own_profile) { ?> <a class="edit_link" href="/settings#profile">edit</a><? } ?></div>
<? } ?>
<? if ($is_own_profile || !empty($loc)) { ?>
  <div class="user_info"><strong>Location:</strong> <?=hsc($loc)?><? if ($is_own_profile) { ?> <a class="edit_link" href="/settings#profile">edit</a><? } ?></div>
<? } ?>
<? if ($is_own_profile || !empty($web)) { ?>
  <div class="user_info"><strong>Web:</strong> <a href="<?=hsc($web)?>"><?=hsc($web)?></a><? if ($is_own_profile) { ?> <a class="edit_link" href="/settings#profile">edit</a><? } ?></div>
<? } ?>
<div class="user_info"><strong>Total Photos:</strong> <?=$user->total_photos()?></div>

<div class="user_info">
  <img class="rss" src="/img/icons/rss.png" />
  <? if (!$is_own_profile) { ?>
    Subscribe to <?=spf(_("%s's"), $username)?> <a href="/<?=$username?>/feed">RSS feed</a>
  <? } else { ?>
    View my public <a href="/<?=$username?>/feed">RSS feed</a> (as others see it)<br/>
  <? } ?>
</div>

<? if (!$is_own_profile) { ?>

  <div class="line"></div>
  
  <h3>Follow Status</h3>
  <? if (!user::has_active() || (!$is_own_profile && !user::active()->is_following($user->get_id()))) { ?>
    <p>You are not yet following <?=$username?>.</p>
    <form action="/<?=$username?>/follow" method="post">
      <button>Follow <?=$username?></button>
    </form>
  <? } else { ?>
    <p>You are following <?=$username?>.</p>
    <form action="/<?=$username?>/unfollow" method="post">
      <button>Unfollow <?=$username?></button>
    </form>
  <? } ?>

<? } ?>

<? //========================================================================================================== ?>

<? if ($is_own_profile) { ?>
  <h2>Your Recent Photos</h2>
<? } else { ?>
  <h2><?=spf(_("%s's Recent Photos"), $username)?></h2>
<? } ?>

<?

$num_recent_photos = 5;

if ($is_own_profile) {
    $empty_msg = spf(_('You do not have any photos uploaded yet! <a href="%s">Upload one</a> here now.'), '/photo/add');
} else {
    $empty_msg = spf(_('%s does not have any photos yet.'), hsc($username));
}

$latest_photos = photo::latest_from($user->get_id(), $num_recent_photos);
if ($total_photos > $num_recent_photos) { ?>
   <p><a href="<?=spf('/%s/photos/', hsc($user->get_username()))?>">View all <?=$total_photos?> photos by <?=hsc($user->get_username())?></a></p>
<?
}
photo::print_photo_entries($latest_photos, $empty_msg);
if ($total_photos > $num_recent_photos) { ?>
   <p><a href="<?=spf('/%s/photos/', hsc($user->get_username()))?>">View all <?=$total_photos?> photos by <?=hsc($user->get_username())?></a></p>
<?
} ?>

<? //========================================================================================================== ?>

<? if ($is_own_profile) { ?>
  <h2>Your Monthly Mosaic</h2>
<? } else { ?>
  <h2><?=spf(_("%s's Monthly Mosaic"), $username)?></h2>
<? } ?>

<? require '../tmpl/monthly.php'; ?>

<? $page->footer(); ?>
