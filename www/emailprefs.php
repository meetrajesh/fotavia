<?php

require '../config.php';
$page = new page;
$page->title(_('Email Preferences'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('settings.js');

// if the user got here by clicking on an email
if (!empty($_GET['secret'])) {
    $secret = str_decrypt(EMAIL_OPTOUT_SECRET, trim($_GET['secret']));
    if (in_str($secret, '-')) {
        list($uid, $timestamp) = explode('-', $secret, 2);
        $timestamp = (int)$timestamp;
        // limit email optout link to 7 days
        if (is_id($uid) && $timestamp !== 0 && $timestamp + 7*86400 >= time()) {
            $user = user::get($uid);
            $optouts = $user->get_email_optouts();
        }
    }
}

if (!isset($user)) {
    redirect('/settings');
}

if ($page->is_post()) {
    // email opt out
    $optouts = array();
    foreach (array_keys(email::$types) as $type) {
        if (!isset($_POST['check'][$type]) || $_POST['check'][$type] != 'on') {
            $optouts[] = $type;
        }
    }
    $user->set_email_optouts($optouts);
    $page->msg = _('Email settings successfully saved. Go back <a href="/">home</a>.');

    if (empty($page->msg) && empty($page->err)) {
        $page->msg = _('No settings were updated.');
    }
}

$page->header();
$page->feedback();

require '../tmpl/email_optouts.php';

$page->footer();

?>
