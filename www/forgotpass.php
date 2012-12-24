<?php

require '../config.php';

$page = new page;
$page->title(_('Forgot Password'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');

if ($page->is_post()) {
    $_POST = array_map('trim', $_POST);
    if (empty($_POST['email'])) {
        $page->err = _('Please enter your username or email to get instructions on how to reset your password.');
    } elseif (user::valid_email($_POST['email'])) {
        if (user::email_exists($_POST['email'])) {
            $user = user::get_from_email($_POST['email']);
        } else {
            $page->err = _('Sorry, that email does not exist in our system.');
        }
    } else {
        // username entered
        if (user::username_exists($_POST['email'])) {
            $user = user::get_from_username($_POST['email']);
        } else {
            $page->err = _('Sorry, that username does not exist in our system.');
        }
    }
    if (isset($user)) {
        // check if the user isn't rejected
        if ($user->is_rejected()) {
            $page->err = _('Sorry we could not find you in our system.');
        } else {
            $user->send_forgot_pass_email();
            $page->msg = spf(_('An email containing instructions to reset your password has been emailed to %s.'), hsc($user->get_email()));
            $page->quit();
        }
    }
}

$page->header();
$page->feedback();

?>

<h2>Forgot your password?</h2>

<p>No problem! We can have it reset it no time. Just type in your username or
email address, and we will email you containing instructions to reset your
password.</p>

<form method="post" action="<?=$page->get_form_action()?>">
  <p>
    <label for="email">Username or Email:</label>
    <input type="text" id="email" name="email" value="<?=pts('email')?>" />
  </p>
  <p>
    <input type="submit" name="btn_submit" value="Submit" />
  </p>
</form>

<? $page->footer() ?>


