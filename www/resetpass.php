<?php

require '../config.php';

$page = new page;
$page->title(_('Reset Password'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');
user::logout();

if (!empty($_GET['secret'])) {
    list($uid, $timestamp) = explode('-', str_decrypt(FORGOT_PASS_SECRET, trim($_GET['secret'])));
    // limit forgot pass email link to 8 hours
    if (!is_id($uid) || $timestamp + 8*3600 < time()) {
        $page->quit(_('Invalid request.'));
    }
    $user = user::get($uid);
    if ($user->is_rejected()) {
        $page->quit(_('Invalid request.'));
    }
}

if ($page->is_post() && isset($user)) {
    $_POST = array_map('trim', $_POST);
    if (strlen($_POST['password']) < PASSWORD_MIN_LENGTH) {
        $page->err = spf(_('Sorry your new password is too short. Please make sure it is at least %d characters.'), PASSWORD_MIN_LENGTH);
    } else {
        $user->update_password($_POST['password']);
        $page->msg = _('Your password has been successfully updated. You may now login by clicking <a href="/login">here</a>.');
        $page->quit();
    }
}

$page->header();
$page->feedback();

?>

<h2>Reset Password</h2>

<p>Please choose a new password.</p>

<form method="post" action="<?=$page->get_form_action()?>">
  <p>
    <label for="password">New Password: <span class="help_text">(<?=spf(_('min %d chars. long'), PASSWORD_MIN_LENGTH)?>)</span></label>
    <input type="password" id="password" name="password" value="" />
  </p>
  <p>
    <input type="submit" name="btn_submit" value="Submit" />
  </p>
</form>

<? $page->footer() ?>


