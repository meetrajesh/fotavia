<?php

require '../config.php';

$page = new page;
$page->title('Login As Another User');
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');

if ($page->is_post()) {
    $_POST = array_map('trim', $_POST);
    if (user::login($_POST['email'], false)) {
        redirect('/dash');
    } else {
        $page->err = _('Invalid username/email. Please try again.');
    }
}

$page->header();
$page->feedback();

?>

<form method="post" action="<?=$page->get_form_action()?>">
  <p>
    <label for="email">Username / User Id / Email:</label>
    <input type="text" id="email" name="email" value="<?=pts('email')?>" />
  </p>
  <p>
    <input type="submit" name="btn_submit" value="Login" />
  </p>
</form>

<? $page->footer(); ?>
