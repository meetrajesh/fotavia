<?php

require '../config.php';

$page = new page();
$page->title(_('Confirm Signup'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('register.js');
$page->add_script('autofocus.js');

if (user::has_active()) {
    user::logout();
}

$gets = get_params();
if (empty($gets[1])) {
    $page->quit(_('No confirm code specified'));
}

$confirm_code = $gets[1];
$name = user::get_name_from_confirm_code($confirm_code);

if (false === $name) {
    $page->quit(_('Sorry, you specified an invalid confirmation code.'));
}

if ($page->is_post()) {

    // trim all input
    $_POST = array_map('trim', $_POST);

    if ($page->any_empty_var()) {
        $page->err = _('Please fill in all fields.');
    } else {
        if (!user::valid_username($_POST['username'])) {
            $page->err = _('Sorry, but your username can only be plain alphanumeric characters.');
        } elseif (!user::valid_username_length($_POST['username'])) {
            $page->err = spf(_('Sorry, but your username has to be a minimum of %d characters.'), USERNAME_MIN_LENGTH);
        } elseif (strlen($_POST['username']) > USERNAME_MAX_LENGTH) {
            $page->err = spf(_('Sorry, but your username can be at most %d characters.'), USERNAME_MAX_LENGTH);
        } elseif (user::is_reserved_username($_POST['username'])) {
            $page->err = _('Sorry, your username is a keyword reserved for internal system use. Please choose another one.');
        } elseif (user::is_numbers_only($_POST['username'])) {
            $page->err = _('Sorry, your username cannot be composed only of numbers. Please include some letters too.');
        } elseif (user::username_exists($_POST['username'])) {
            $page->err = _('Sorry, that username has already been taken. Please try another one.');
        } elseif (strlen($_POST['password']) < PASSWORD_MIN_LENGTH) {
            $page->err = spf(_('Sorry your password is too short. Please choose one that is at least %d characters.'), PASSWORD_MIN_LENGTH);
        } elseif ($_POST['password'] != $_POST['confirm_password']) {
            $page->err = _('Sorry your passwords don\'t match up. Please try again.');
        } else {
            if (user::create($confirm_code, $_POST['username'], $_POST['password'])) {
                // log the user in
                user::login($_POST['username']);
                $user = user::active();
                $user->set_tz_offset($_POST['tz_offset']);
                $user->set_client_dimensions($_POST['client_width'], $_POST['client_height']);
                // send confirmation email
                $email = new email('confirm');
                $email->assign('name', $user->get_name());
                $email->assign('username', $user->get_username());
                $email->assign('add_photo_link', BASE_URL . '/photo/add');
                $email->assign('dashboard_link', BASE_URL . '/dash');
                $email->send($user);
                // get me to auto follow the user, myspace style
                user::get(ADMIN_UID)->follow($user->get_id());
                // display confirmation message
                $page->msg = spf(_('Congrats, your signup is now successful. Why not start by <a href="%s">uploading a photo</a>? <br/><br/>You can also start by <a href="%s">visiting your dashboard</a> or tweaking your <a href="%s">account settings</a>.'), '/dash', '/dash', '/settings');
                $page->quit();
            } else { 
                $page->err = _('Oops, something went wrong while trying to confirm your signup. Please leave us a message on our <a href="/feedback">feedback page</a>.');
            }
        }
    }
}

$page->header();
$page->feedback();

?>

<h2>Signup Confirmation Step 2</h2>

<p>Hi <?=hsc($name)?>!</p>

<p>Please choose a username and password for your site:</p>
<form id="confirm" action="<?=$page->get_form_action()?>" method="post">
  <input type="hidden" id="tz_offset" name="tz_offset" value="0" />
  <input type="hidden" id="client_width" name="client_width" value="0" />
  <input type="hidden" id="client_height" name="client_height" value="0" />
  <p>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" maxlength="25" value="<?=pts('username', user::suggest_username($name))?>" />
    <span class="helpline">At least 5 characters long
  </p>
  <p class="field_desc_text">Your profile will be at fotavia.com/username</p>
  <p>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" maxlength="100" value="" />
  </p>
  <p>
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" id="confirm_password" name="confirm_password" maxlength="100" value="" />
  </p>
  <p>
    <input type="submit" name="btn_confirm" value="Confirm" />
  </p>
</form>

<? $page->footer(); ?>
