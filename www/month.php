<?php

require '../config.php';
$page = new page;

if (empty($_GET['user']) || empty($_GET['date'])) {
    redirect('/dash');
}

$_GET = array_map('trim', $_GET);

list($y, $m) = explode('-', $_GET['date']);
if (empty($y) || empty($m) || !ctype_digit($y) || !ctype_digit($m)) {
    redirect('/dash');
}

if (!user::username_exists($_GET['user'])) {
    $page->quit(_('Sorry that user does not exist.'));
}

$user = user::get_from_username($_GET['user']);

$month = new photo_month($user, $y, $m);
$page->title($user->get_name(), date('F Y', $month->stamp));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('month.js');
$page->header();

?>

<? /* ================================================================= */ ?>

<div id="calendar_month">
  <div id="text">

    <h2><?=date('F Y', $month->stamp)?></h2>

    <? /* displaying table here */ ?>

      <table id="calendar">

        <tr>

          <? $weekdays = array(0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat');
             $weekday = calendar_month::first_day_of_week;
  
             for ($i=0; $i < 7; $i++) {
               echo '<th>' . $weekdays[$weekday] . '</th>';
               $weekday = ($weekday + 1) % 7;
             }

          ?>

        </tr>
  
        <? while ($day = $month->cal->fetch()) { ?>
  
          <? if ($day->isFirst()) { ?>
            <tr>
          <? } ?>
  
          <? if ($day->thisMonth() != $month->month) { ?>
            <td class="empty">&nbsp;</td>
          <? } elseif (!$day->isTagged()) { ?>
            <td class="blank">&nbsp;</td>
          <? } else { ?>
  
            <? $p = photo::get($day->getTag()); ?>
            <td>
                <a href="<?=$p->get_page_url()?>">
                  <img src="<?=$p->url('square')?>" width="65" height="65"
                       title="<?=date('F jS Y', $day->getStamp())?> (<?=hsc($p->get_title())?>)"
                       alt="<?=date('j-M-y', $day->getStamp())?>" />
                </a>
            </td>
  
          <? } ?>
  
          <? if ($day->isLast()) { ?>
            </tr>
          <? } ?>
  
        <? } ?>

      </table>

    <? /* end display table */ ?>

  </div>

  <div id="lower">
    &laquo;&nbsp; 
    <? if ($month->has_prev()) { ?>
      <a id="prevlink" href="<?=$month->url($month->cal->prevMonth())?>">prev</a>
    <? } else { ?>
      prev
    <? } ?> 
    &nbsp;|&nbsp; <?=date('F Y', $month->stamp)?> &nbsp;|&nbsp; 
    <? if ($month->has_next()) { ?>
      <a id="nextlink" href="<?=$month->url($month->cal->nextMonth())?>">next</a>
    <? } else { ?>
      next 
    <? } ?> 
    &nbsp;&raquo;
  </div>

</div>

<? $page->footer(); ?>
