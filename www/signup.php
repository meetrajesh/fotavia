<?php

require '../config.php';

$page = new page;
$page->title(_('Signup'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');

if (user::has_active()) {
    redirect('/dash');
}

if ($page->is_post()) {

    // trim all input
    $_POST = array_map('trim', $_POST);

    if ($page->any_empty_var()) {
        $page->err = _('Please fill in all fields.');
    } else {
        // check email uniqueness
        if (user::email_exists($_POST['email'])) {
            $page->err = _('Sorry, that email address is already in use. Plase try another one.');
        } elseif (!user::valid_email($_POST['email'])) {
            $page->err = _('Sorry that email address does not look valid.');
        } else {
            $confirm_code = user::create_temp($_POST['email'], $_POST['name']);
            // send email
            $email = new email('signup');
            $email->assign('name', $_POST['name']);
            $email->assign('confirm_url', BASE_URL . '/confirm/' . $confirm_code);
            $email->send($_POST['email']);
            // show success message
            $page->msg = _('Congrats, you are now signed up! Please check your inbox for a confirmation email.');
            $page->quit();
        }
    }
}

$page->header();
$page->feedback();

?>

<h2>Become a proud member of Fotavia today!</h2>
<p>Welcome to Fotavia! Enter your email and <strong>full name</strong> to get started. We&#39;ll have you set up in less than a minute!</p>

<form id="signup" action="<?=$page->get_form_action()?>" method="post">
  <p>
    <label for="email">Email:</label>
    <input type="text" id="email" name="email" maxlength="100" value="<?=pts('email')?>" />
  </p>
  <p>
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" maxlength="100" value="<?=pts('name')?>" />
  </p>
  <p class="signup_btn_para">
    <input type="submit" name="btn_submit" id="btn_submit" value="Signup!" />
  </p>
</form>


<? $page->footer(); ?>
