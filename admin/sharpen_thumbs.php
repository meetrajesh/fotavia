<?php

require '../config.php';

$page = new page;
$page->title('Sharpen Thumbs');
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');

if ($page->is_post() && !empty($_POST['pid'])) {

    if (!is_id($_POST['pid'])) {
        $page->quit(_('Provided photo id invalid.'));
    }

    set_time_limit(0);    

    $min_pid = $_POST['pid'];
    $max_pid = photo::get_max_photo_id();

    foreach (range($min_pid, $max_pid) as $pid) {
        if (photo::exists($pid)) {
            photo::get($pid)->gen_thumbs(true, true);
        }
    }

    $page->msg = _('Sharpening successful!');
    
}

$page->header();
$page->feedback();

?>

<h2>Sharpen Latest Thumbs</h2>

<form id="sharpen_thumbs" method="post" action="<?=$page->get_form_action()?>">
  <label for="pid">Photo id to start sharpening from (max is <?=photo::get_max_photo_id()?>):</label>
  <input type="text" id="pid" name="pid" value="" />
  <input type="submit" name="btn_submit" value="Go!" />
</form>

<? $page->footer(); ?>
