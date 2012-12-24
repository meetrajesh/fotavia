<?php

require '../config.php';

$page = new page;

if (isset($_GET['user']) && user::username_exists($_GET['user'])) {
    $user = user::get_from_username($_GET['user']);
}

if (!$user) {
    $page->quit(_('Invalid user specified.'));
}

$page_num = isset($_GET['page']) && is_id($_GET['page']) ? (int)$_GET['page'] : 1;
$total_photos = $user->total_photos();
$photos_per_page = 15;
$total_pages = ceil($total_photos / $photos_per_page);

$page->title(spf(_("%s's Photos"), $user->get_name()));
$recent_photos = photo::latest_from($user->get_id(), $photos_per_page, $photos_per_page * ($page_num - 1));

$page->header();

?>

<h2><?=spf(_("%s's Photos"), hsc($user->get_name()))?></h2>

<div id="newphotos">

<? if (count($recent_photos) == 0) { ?>
  <em><?=hsc($user->get_name())?> has no photos yet.</em>
<?
} ?>

<table>

<? $photos_per_row = 3;
foreach ($recent_photos as $i => $photo_id) {
    $p = photo::get($photo_id);
    if ($i % $photos_per_row+1 == 0) {
        echo '<tr>';
    }
    if ($i != 0 && $i % $photos_per_row == 0) {
        echo '</tr>';
    } ?>
    <td>
      <a class="recent_upload" href="<?=$p->get_page_url()?>"><img src="<?=$p->url('thumb')?>" title="<?=hsc($p->get_title())?>" /></a>
    </td>
<?
} ?>

</table>
</div>

<? if ($total_photos > $photos_per_page) { ?>
  <div id="lower">
    &laquo;&nbsp;
    <? echo $page_num > 1 ? '<a id="prevlink" href="' . spf('/%s/photos/page/%d', hsc($user->get_username()), $page_num - 1) . '">prev</a>' : 'prev'; ?> | 
    <?=spf(_('page %d of %d'), $page_num, $total_pages)?> | 
    <? echo ($page_num * $photos_per_page) < $total_photos ? '<a id="nextlink" href="' . spf('/%s/photos/page/%d', hsc($user->get_username()), $page_num + 1) . '">next</a>' : 'next'; ?>
    &nbsp;&raquo;
  </div>
<? } ?>

<? $page->footer(); ?>
