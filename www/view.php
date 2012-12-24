<?php

require '../config.php';
$page = new page();

// figure out the photo from the username and user date
$gets = get_params();
if (count($gets) < 4) {
    redirect('/dash');
}
$photo = photo::get_from_user_date($gets[0], spf('%d-%d-%d', $gets[1], $gets[2], $gets[3]));
if (!$photo) {
    $page->quit(_('Invalid photo id specified'));
}
if ($photo->is_rejected()) {
    $page->quit(_('Sorry this photo has been deleted.'));
}
$page->title($photo->owner()->get_name(), $photo->get_title());

// nav
user::has_active() ? $page->add_nav_link('dashboard', '/dash') : $page->add_nav_link('login', '/login');
// add an "edit" link for this photo if the user is logged in and viewing his own photo
if (user::has_active() && $photo->get_owner_id() == user::active()->get_id()) {
    $page->add_nav_link('edit', '/photo/edit/' . $photo->get_id());
    $page->add_nav_link('delete', '/photo/delete/' . $photo->get_id());
} else {
    $page->add_nav_link(hsc($photo->owner()->get_username()) . "'s profile", $photo->owner()->get_profile_url());
}
$photo->has_prev() ? $page->add_nav_link('prev', $photo->prev()->get_page_url()) : $page->add_nav_link('prev');
$photo->has_next() ? $page->add_nav_link('next', $photo->next()->get_page_url()) : $page->add_nav_link('next');

// increment the view count on the photo
// include all views that don't belong to the owner
if (!user::has_active() || user::active()->get_id() != $photo->owner()->get_id()) {
    $photo->increment_num_views();
}

// add js files
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('view.js');

// add an rss feed
$feed['url'] = spf('%s/%s/%s/', BASE_URL, $photo->owner()->get_username(), 'feed');
$feed['title'] = spf(_("%s's Photos on Fotavia"), $photo->owner()->get_name());
$page->set_rss_feed($feed['url'], $feed['title']);

// print out the header
$page->header();

?>

<input type="hidden" id="photo_id" value="<?=$photo->get_id()?>" />

<div class="viewbody">

  <? if (!user::has_active() || !user::active()->get_pref('clicknext')) {
        if ($photo->has_prev()) {
            $show_link = true; ?>
            <a href="<?=$photo->prev()->get_page_url()?>">
     <? }
     } else {
         if ($photo->has_next()) {
            $show_link = true; ?>
            <a href="<?=$photo->next()->get_page_url()?>">
        <?
         }
     }
  ?>

  <img class="viewphoto" src="<?=$photo->url(user::default_thumb_size())?>" alt="<?=hsc($photo->get_title())?>" title="<?=$photo->get_tooltip()?>" />
  
  <? if (isset($show_link) && $show_link) { ?>
    </a>
  <? } ?>
  
  <? $formatted_text = trim($photo->get_formatted_text()); ?>

  <div id="text">
    <h2<?=empty($formatted_text) ? ' class="empty"' : ''?>><?=hsc($photo->get_title())?></h2>
    <?=!empty($formatted_text) ? $formatted_text : ''?>
  </div>
  
  <div id="toggle_comments" class="hide">
    <a id="show_comments" href="#">view comments</a> (<?=$photo->num_comments()?>)
  </div>

  <a name="comments"></a>
  <div id="comments" class="hide loading"></div>

  <div id="lower">
    &laquo;&nbsp; 
    <? echo $photo->has_prev() ? '<a id="prevlink" href="' . $photo->prev()->get_page_url() . '">prev</a>' : 'prev'; ?>
    &nbsp;|&nbsp; by <?=$photo->owner()->get_profile_link()?>
    &nbsp;|&nbsp; <?=std_date(strtotime($photo->get_user_date()))?> &nbsp;|&nbsp;
    <? if (user::has_active() && $photo->get_owner_id() == user::active()->get_id()) { ?>
      <a href="/photo/edit/<?=$photo->get_id()?>">edit</a> &nbsp;|&nbsp;
      <a href="/photo/delete/<?=$photo->get_id()?>">delete</a> &nbsp;|&nbsp;
    <? } ?>
    <a id="exif_toggle" href="#">exif</a> &nbsp;|&nbsp;
    <? echo $photo->has_next() ? '<a id="nextlink" href="' . $photo->next()->get_page_url() . '">next</a>' : 'next'; ?>
    &nbsp;&raquo;
  </div>
  <div id="exif" class="hide loading"></div>

</div>

<?php $page->footer(); ?>
