<?php

require '../config.php';
$page = new page;
$page->title(_('Account Settings'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('settings.js');
$page->add_script('facebook.js');
$page->add_script('http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php');

$page->ensure_login();
$user = user::active();

// list of profile fields
$profile_fields = array('name'     => array('Name', 100),
                        'bio'      => array('One-line Bio', 250),
                        'website'  => array('Website', 150),
                        'location' => array('Location', 100));

if ($page->is_post()) {
    if (isset($_POST['email'])) {
        $_POST['email'] = trim($_POST['email']);
    }
    // check if password verification is needed
    $email_changed = isset($_POST['email']) && $_POST['email'] != $user->get_email();
    $password_changed = !empty($_POST['password']);

    // check old password for verification
    if ($email_changed || $password_changed) {
        if (empty($_POST['oldpassword'])) {
            $page->err = _('Please type in your current password for verification.');
        } elseif (!$user->is_right_password($_POST['oldpassword'])) {
            $page->err = _('Your current password is incorrect.');
        }
    } else {
        $page->msg = _('No settings were updated.');
    }

    if (empty($page->err) && empty($page->msg)) {
        // check email field
        if ($email_changed) {
            if (user::email_exists($_POST['email'])) {
                $page->err = _('Sorry another user already exists with that email address.');
            } elseif (!user::valid_email($_POST['email'])) {
                $page->err = _('Sorry that email address does not look valid. Please try again.');
            }
        }
        // check password field
        if (empty($page->err)) {
            if ($password_changed) {
                if (strlen($_POST['password']) < PASSWORD_MIN_LENGTH) {
                    $page->err = _('Your new password is too short. Please choose a longer one.');
                }
            }
        }
        // save accounts settings if no errors
        if (empty($page->err)) {
            // save the email
            if ($email_changed) {
                $user->set_email($_POST['email']);
            }
            // save password
            if ($password_changed) {
                $user->update_password($_POST['password']);
            }
            $page->msg = _('Account settings successfully updated.');
        }
    }

    // profile settings
    if (isset($_POST['btn_profile_save'])) {
        $_POST = array_map('trim', $_POST);
        // check fields
        foreach($profile_fields as $field => $field_props) {
            list($field_desc, $maxlength) = $field_props; 
            if (strlen($_POST[$field]) > $maxlength) {
                $page->err = _('Sorry your %s is too long. Please keep it to %d characters.', $field, $maxlength);
            }
        }
        // save fields if no errors
        if (empty($page->err)) {
            $user->save_profile_details($_POST['name'], $_POST['bio'], $_POST['location'], $_POST['website']);
            $page->msg = _('Profile settings successfully updated.');
        }
        // if a pic has been uploaded, save it
        if (!empty($_FILES['headshot']['tmp_name'])) {
            $err_msg = $user->save_headshot($_FILES['headshot']);
            if (empty($page->err) && strlen($err_msg)) {
                $page->err = $err_msg;
            }
        }
    }

    // site prefs
    if (isset($_POST['btn_siteprefs_save'])) {
        // clicknext pref
        $clicknext_pref = isset($_POST['clicknext']) && $_POST['clicknext'] == 'on';
        $user->set_pref('clicknext', (int)$clicknext_pref);
        $page->msg = _('Site preferences successfully saved.');
    }

    // site prefs
    if (isset($_POST['btn_connect_save'])) {
        // fb connect
        $fbconnect_priv = isset($_POST['fbconnect_priv']) && $_POST['fbconnect_priv'] == 'on';
        // has the connect pref changed?
        if ($fbconnect_priv != is_id($user->get_pref('fbid'))) {
            if (!$fbconnect_priv) {
                $user->unset_pref('fbid');
                $page->msg = _('Photo upload stories will <strong>not</strong> be published to Facebook.');
            } else {
                if ($user->can_fb_publish()) {
                    $page->msg = _('Photo upload stories will now be automatically published to your Facebook news feed.');
                } else {
                    $page->err = _('Sorry, we could not verify your publish privileges with Facebook. Please try again.');
                }
            }
        }

        // tw connection
        $twconnect_priv = isset($_POST['twconnect_priv']) && $_POST['twconnect_priv'] == 'on';
        // has the connect pref changed?
        if ($twconnect_priv != $user->can_tw_publish()) {
            if (!$twconnect_priv) {
                $user->unset_tw_auth();
                $page->msg = _('Your Twitter status will <strong>no longer</strong> be updated on photo uploads.');
            } else {
                if ($user->can_tw_publish()) {
                    $page->msg = _('Your Twitter status will be automatically updated on photo uploads.');
                } else {
                    $page->err = _('Sorry, we could not verify your Twitter read/write privileges. Please try again.');
                }
            }
        }
    }

    // email opt out
    if (isset($_POST['btn_optout_save'])) {
        $optouts = array();
        foreach (array_keys(email::$types) as $type) {
            if (!isset($_POST['check'][$type]) || $_POST['check'][$type] != 'on') {
                $optouts[] = $type;
            }
        }
        $user->set_email_optouts($optouts);
        $page->msg = _('Email settings successfully saved.');
    }
    if (empty($page->msg) && empty($page->err)) {
        $page->msg = _('No settings were updated.');
    }

}

$page->header();
$page->feedback();

?>

<? /* ===============ACCOUNT-SETTINGS======================================================= */ ?>

<a name="account"></a>
<h2>Change Email/Password</h2>

<form id="account" method="post" action="<?=$page->get_form_action()?>">

  <p>
    <label for="oldpassword">Current Password: (for verifcation) <span class="req">*</span></label>
    <input type="password" name="oldpassword" id="oldpassword" value="" />
  </p>
  <p>
    <label for="email">Email:</label>
    <input type="text" name="email" id="email" value="<?=$user->get_email()?>" maxlength="100" />
  </p>
  <p>
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" value="" />
  </p> 
 
  <input type="submit" name="btn_account_save" value="Save" />

</form>

<? /* ===============PROFILE-SETTINGS======================================================= */ ?>

<a name="profile"></a>
<h2>Personal Details</h2>

<form id="profile" method="post" action="<?=$page->get_form_action()?>" enctype="multipart/form-data">

<? foreach ($profile_fields as $field => $field_props) { 
     list($field_desc, $maxlength) = $field_props; 
     $func = 'get_' . $field; ?>

  <p>
    <label for="<?=$field?>"><?=hsc($field_desc)?></label>
    <input type="text" name="<?=$field?>" id="<?=$field?>" value="<?=hsc($user->$func())?>" maxlength="<?=$maxlength?>" />
  </p>

<? } ?>

<? /* user pics are 1mb max */ ?>
  <input type="hidden" name="MAX_FILE_SIZE" value="<?=1024*1024?>" />
  <p>
  <? if ($user->has_headshot()) { ?>
    <label>Your Headshot:</label>
    <?=$user->headshot_pic_link('settings_headshot_pic')?>
    <label for="headshot">Choose another:</label>
    <input id="headshot" type="file" name="headshot" size="36" />
  <? } else { ?>
    <label for="headshot">Your Headshot:</label>
    <input id="headshot" type="file" name="headshot" size="36" />
  <? } ?>
  </p>
  <input type="submit" name="btn_profile_save" value="Save" />

</form>

<? /* =====================EMAIL-SETTINGS-AND-OPTOUTS================================================= */ ?>

<a name="email"></a>
<? require '../tmpl/email_optouts.php'; ?>

<? /* ============PEOPLE-I-FOLLOW========================================================== */ ?>

<a name="follow"></a>
<h2>People I Follow</h2>

<?
$leaders = $user->get_leaders();

if (count($leaders) == 0) { ?>

  <em>You do not follow anyone yet. Find some <a href="/search/users">new people</a> to follow.</em>

<? } else { ?>

  <ul>
  <? foreach ($leaders as $leader_id) { 
      $leader = user::get($leader_id); ?>
      <li><?=spf('%s (%s) - <a href="/%s/unfollow">Unfollow</a>', $leader->get_full_profile_link(), hsc($leader->get_username()), hsc($leader->get_username()))?></li>
  <? } ?>
  </ul>

  <p>Find some <a href="/search/users">new people</a> to follow.</p>

<? } ?>

<? // Site preferences ?>

<a name="siteprefs"></a>
<h2>Site Preferences</h2>

<form id="siteprefs" method="post" action="<?=$page->get_form_action()?>">
  <p>
    <input type="checkbox" id="clicknext" name="clicknext" <?=$user->get_pref('clicknext') ? 'checked="checked"' : ''?> />
    <label for="clicknext">Take me to the <em>next</em> photo instead of the previous when I click on a photo in view mode</label>
  </p>
  <p><input type="submit" name="btn_siteprefs_save" value="Save" /></p>
</form>

<? /* ================================SITE-PREFS====================================== */ ?>

<a name="connect"></a>
<h2>Connect to Other Sites</h2>

<form id="connect" method="post" action="<?=$page->get_form_action()?>">
  <p>
    <a name="fbconnect"></a>
    <h3>Facebook Connect</h3>
    <input type="checkbox" id="fbconnect_priv" name="fbconnect_priv" <?=is_id($user->get_pref('fbid')) ? 'checked="checked"' : ''?> />
    <label for="fbconnect_priv">Automatically publish my photo upload stories to my <strong>Facebook</strong> news feed.<br/>
    (This will require you to login to Facebook to provide our <a target="_blank" href="http://www.facebook.com/apps/application.php?id=123632969326">Fotavia app</a> with publish privileges.)</label>
  </p>
  <p>
    <a name="twconnect"></a>
    <h3>Twitter Connect</h3>
    <input type="checkbox" id="twconnect_priv" name="twconnect_priv" <?=is_id($user->get_pref('twpub')) ? 'checked="checked"' : ''?> />
    <label for="twconnect_priv">Automatically update my <strong>Twitter</strong> status when I upload a photo.<br/>
    (This will require you to login to Twitter to provide our Twitter app with read/write privileges.)</label>
  </p>
  <p><input type="submit" name="btn_connect_save" value="Save" /></p>
</form>

<? $page->footer(); ?>
