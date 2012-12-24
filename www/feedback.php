<?php

require '../config.php';

$page = new page(); 
$page->title(_('Feedback'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('autofocus.js');

$default_name = '';
$default_email = '';
if (user::has_active()) {
    $default_name = user::active()->get_name();
    $default_email = user::active()->get_email();
}

if ($page->is_post()) {

    // trim all input
    $_POST = array_map('trim', $_POST);

    if (empty($_POST['name'])) {
        $page->err = _('Please specify your name.');
    } elseif (empty($_POST['email'])) {
        $page->err = _('Please specify your email so we may get in touch with you.');
    } elseif (empty($_POST['msg'])) {
        $page->err = _("Surely you don't want to contact us with an empty message!");
    } elseif (!user::valid_email($_POST['email'])) {
        $page->err = _("That doesn't quite look like a valid email. Make sure to give us your real email so we can get in touch with you about your query.");
    }

    if (empty($page->err)) {
        feedback::add_to_db($_POST['name'], $_POST['email'], $_POST['msg']);
        $page->msg = _('Thank you for contacting us! We will be sure to get in touch with you shortly.');
        $page->quit();
    }

}

$page->header();
$page->feedback();

?>

<h2>Contact Us!</h2>

<p>Feel free to get in touch with us for comments, appreciation or suggestions. We promise to get back to you as soon as we can!</p>

<p>Alternatively, please leave us a message <a href="http://twitter.com/?status=@fotavia&in_reply_to=fotavia">on twitter</a>.</p>

<form id="feedback" action="<?=$page->get_form_action()?>" method="post">
  <p>
    <label for="name">Your Name: <span class="req">*</span></label>
    <input type="text" id="name" name="name" size="23" maxlength="100" value="<?php pts('name', $default_name)?>" />
  </p>
  <p>
    <label for="email">Your Email: <span class="req">*</span></label>
    <input type="text" id="email" name="email" size="23" maxlength="100" value="<?php pts('email', $default_email)?>" />
  </p>
  <p>
    <label for="msg">Message:</label>
    <textarea id="msg" name="msg" rows="8" cols="45"><?php pts('msg'); ?></textarea>
  </p>
  <p>
    <input type="submit" name="btn_submit" value="Submit" />
  </p>
</form>

<?php $page->footer(); ?>
