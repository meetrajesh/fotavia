<?php

require '../config.php';

$page = new page();
$page->ensure_login();
$page->title(_('Dashboard'));

$page->add_script('jquery-1.3.2.min.js');
$page->add_script('dash.js');

$user = user::active();

$feed_url = spf('%s/feed/%s/%s', BASE_URL, $user->get_username(), $user->feed_secret_key());
$feed_title = _('My Fotavia Private Feed');
$page->set_rss_feed($feed_url, $feed_title);

$page->header();

?>

<h2>Your Personal Dashboard</h2>

<? // photo upload
if ($user->can_upload_photo()) { ?>

  <h3>Upload a Photo for Today</h3>
  <form action="/photo/add" enctype="multipart/form-data" method="post" id="photo_add">
    <input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key" value="<?= md5($user->get_id() . microtime()) ?>" />
    <input type="hidden" name="MAX_FILE_SIZE" value="<?=MAX_UPLOAD_SIZE?>" />
    <label for="image_select">Your Photo:</label>
    <p><input id="image_select" type="file" size="44" name="photo"/></p>
    <input type="submit" value="Upload" id="photo_add_submit" name="photo_add_submit"/>
  </form>
  
  <div class="line"></div>
  
<? } ?>

<? /* ==========================NEWS-FEED====================================================== */ ?>

<h3>Your Newsfeed</h3>

<? $feed = new newsfeed($user->get_id()); ?>
<? $feed_entries = $feed->get_entries(); ?>
<? if(!empty($feed_entries)) { ?>
<ul id="newsfeed">
<?
foreach ($feed->get_entries() as $entry) {
    require '../tmpl/newsfeed_entry.php';
}
?>
</ul>
<? } else { ?>
<p><em>There are no items in your news feed</em></p>
<? } ?>

<img class="rss" src="/img/icons/rss.png" /> View my private <a href="/feed">news feed</a> (feed containing yours and your friends&#39; photos)

<div class="line"></div>

<? /* =======================LATEST-PHOTOS=============================================== */ ?>

<h3 id="recent_photos_heading">Your Friends&#39; Recent Photos</h3>

<div id="recent_photos">

  <? $latest_photos = photo::latest_from($user->get_leaders(), 10 + 1);
     ($has_next = (sizeof($latest_photos) > 10)) ? array_pop($latest_photos) : null;
  ?>

  <input type="hidden" id="photo_has_next" value="<?= $has_next ? 'true' : 'false' ?>">

  <? if (count($latest_photos) == 0) { ?>
    <p><em>No photos have been uploaded by your friends!</em></p>
  <?
  } else {
      foreach($latest_photos as $i => $photo_id) {
        $p = photo::get($photo_id);
        if ($i != 0 && $i % 5 == 0) {
            echo '<br/><br/>';
        } ?>
        <a href="<?=$p->get_page_url()?>"><img class="silver_frame" src="<?=$p->url('square')?>" title="<?=$p->get_tooltip()?>" /></a>
    <?
     }
  } ?>

</div>

<div class="line"></div>

<? /* ================================================================================ */ ?>

<h3>Your Most Recent Photos</h3>

<? $latest_photos = photo::latest_from($user->get_id(), 3);

$empty_msg = spf(_('You do not have any photos yet!'));
photo::print_photo_entries($latest_photos, $empty_msg) ?>

<p><a href="<?=spf('/%s/photos/', hsc($user->get_username()))?>">View all my Photos</a> &raquo;</p>

<? $page->footer(); ?>
