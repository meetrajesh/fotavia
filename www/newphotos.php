<?php

require '../config.php';

$page = new page;
$page->title('Recent Photos');

$page_num = isset($_GET['page']) && is_id($_GET['page']) ? (int)$_GET['page'] : 1;
$total_photos = photo::total_good_photos();
$photos_per_page = 15;
$total_pages = ceil($total_photos / $photos_per_page);

$recent_photos = photo::latest_from(null, $photos_per_page, $photos_per_page * ($page_num - 1));

$page->header();

?>

<h2>Recently Uploaded Photos</h2>

<div id="newphotos">

<? if (count($recent_photos) == 0) { ?>
  <em>There are no photos yet. Please check back tomorrow.</em>
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
      <a class="recent_upload" href="<?=$p->get_page_url()?>"><img class="silver_frame" src="<?=$p->url('thumb')?>" title="<?=$p->get_tooltip()?>" /></a><br/>
      by <?=$p->owner()->get_full_profile_link()?>
    </td>
<?
} ?>

</table>
</div>

<? if ($total_photos > $photos_per_page) { ?>
  <div id="lower">
    &laquo;&nbsp;
    <? echo $page_num > 1 ? '<a id="prevlink" href="' . spf('/newphotos/page/%d', $page_num - 1) . '">prev</a>' : 'prev'; ?> | 
    <?=spf(_('page %d of %d'), $page_num, $total_pages)?> | 
    <? echo ($page_num * $photos_per_page) < $total_photos ? '<a id="nextlink" href="' . spf('/newphotos/page/%d', $page_num + 1) . '">next</a>' : 'next'; ?>
    &nbsp;&raquo;
  </div>
<? } ?>

<? $page->footer(); ?>
